<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$db = getDBConnection();

// Content counts
$counts = [];
foreach (['lessons', 'dictionary', 'proverbs', 'articles', 'contact_messages', 'grammar_topics', 'translations', 'media'] as $t) {
    try {
        $counts[$t] = $db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    } catch (Exception $e) { $counts[$t] = 0; }
}

// Monthly growth for last 6 months
$months = [];
$monthLabels = [];
for ($i = 5; $i >= 0; $i--) {
    $label = date('M Y', strtotime("-$i months"));
    $monthLabels[] = $label;
    $start = date('Y-m-01', strtotime("-$i months"));
    $end   = date('Y-m-t', strtotime("-$i months"));
    $total = 0;
    foreach (['lessons', 'articles', 'dictionary', 'proverbs'] as $t) {
        try {
            $total += $db->query("SELECT COUNT(*) FROM $t WHERE created_at BETWEEN '$start' AND '$end 23:59:59'")->fetchColumn();
        } catch (Exception $e) {}
    }
    $months[] = $total;
}

// Messages by status
$msgStats = ['new' => 0, 'read' => 0, 'replied' => 0];
try {
    $rows = $db->query("SELECT status, COUNT(*) as c FROM contact_messages GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) { if (isset($msgStats[$r['status']])) $msgStats[$r['status']] = $r['c']; }
} catch (Exception $e) {}

// Top lessons by order
$topLessons = [];
try {
    $topLessons = $db->query("SELECT title, level, lesson_order FROM lessons ORDER BY lesson_order ASC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Runyakitara Hub Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/forms.css">
    <style>
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .metric-card { background: white; border-radius: 16px; padding: 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04); border: 1px solid rgba(226,232,240,0.8); }
        .metric-icon { font-size: 32px; margin-bottom: 10px; line-height: 1; }
        .metric-value { font-size: 32px; font-weight: 800; color: var(--dark); line-height: 1; }
        .metric-label { font-size: 12px; color: var(--text-light); margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .charts-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px; }
        @media (max-width: 900px) { .charts-2col { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="admin-body">
<div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>

    <div class="admin-content">
        <?php include 'includes/header.php'; ?>

        <main class="admin-main">

            <!-- Metric Cards -->
            <div class="analytics-grid">
                <div class="metric-card">
                    <div class="metric-icon" style="color:#667eea;"><i class="bi bi-book"></i></div>
                    <div class="metric-value"><?php echo $counts['lessons']; ?></div>
                    <div class="metric-label">Lessons</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon" style="color:#f093fb;"><i class="bi bi-journal-text"></i></div>
                    <div class="metric-value"><?php echo $counts['dictionary']; ?></div>
                    <div class="metric-label">Words</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon" style="color:#4facfe;"><i class="bi bi-chat-quote"></i></div>
                    <div class="metric-value"><?php echo $counts['proverbs']; ?></div>
                    <div class="metric-label">Proverbs</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon" style="color:#43e97b;"><i class="bi bi-newspaper"></i></div>
                    <div class="metric-value"><?php echo $counts['articles']; ?></div>
                    <div class="metric-label">Articles</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon" style="color:#fa709a;"><i class="bi bi-envelope"></i></div>
                    <div class="metric-value"><?php echo $counts['contact_messages']; ?></div>
                    <div class="metric-label">Messages</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon" style="color:#30cfd0;"><i class="bi bi-play-circle"></i></div>
                    <div class="metric-value"><?php echo $counts['media']; ?></div>
                    <div class="metric-label">Media</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon" style="color:#f59e0b;"><i class="bi bi-pencil-square"></i></div>
                    <div class="metric-value"><?php echo $counts['grammar_topics']; ?></div>
                    <div class="metric-label">Grammar</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon" style="color:#10b981;"><i class="bi bi-arrow-left-right"></i></div>
                    <div class="metric-value"><?php echo $counts['translations']; ?></div>
                    <div class="metric-label">Translations</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-2col">
                <div class="chart-card">
                    <div class="card-header">
                        <h3><i class="bi bi-bar-chart-line"></i> Content Added (Last 6 Months)</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="card-header">
                        <h3><i class="bi bi-envelope-check"></i> Messages by Status</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="msgChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Content Distribution -->
            <div class="chart-card" style="margin-bottom:28px;">
                <div class="card-header">
                    <h3><i class="bi bi-pie-chart"></i> Content Distribution</h3>
                </div>
                <div class="card-body" style="max-width:400px;margin:0 auto;">
                    <canvas id="distChart"></canvas>
                </div>
            </div>

            <!-- Top Lessons Table -->
            <?php if (!empty($topLessons)): ?>
            <div class="content-table">
                <div class="table-header">
                    <h2><i class="bi bi-list-ol"></i> Lessons Overview</h2>
                    <a href="lessons-manage.php" class="btn-add"><i class="bi bi-plus"></i> Add Lesson</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topLessons as $i => $lesson): ?>
                        <tr>
                            <td><?php echo $lesson['lesson_order'] ?? ($i + 1); ?></td>
                            <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                            <td><span class="level-badge level-<?php echo strtolower($lesson['level'] ?? 'beginner'); ?>"><?php echo ucfirst($lesson['level'] ?? 'Beginner'); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Bar: Content Added (Last 6 Months) ──
    const growthData = <?php echo json_encode($months); ?>;
    const growthCtx = document.getElementById('growthChart');
    if (growthCtx) {
        new Chart(growthCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($monthLabels); ?>,
                datasets: [{
                    label: 'Items Added',
                    data: growthData,
                    backgroundColor: 'rgba(102,126,234,0.75)',
                    borderColor: 'rgb(102,126,234)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed.y + ' items' } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ── Doughnut: Messages by Status ──
    const msgData = [<?php echo $msgStats['new']; ?>, <?php echo $msgStats['read']; ?>, <?php echo $msgStats['replied']; ?>];
    const msgTotal = msgData.reduce((a, b) => a + b, 0);
    const msgCtx = document.getElementById('msgChart');
    if (msgCtx) {
        if (msgTotal === 0) {
            msgCtx.parentElement.innerHTML = '<div style="text-align:center;padding:40px;color:#94a3b8;"><i class="bi bi-envelope" style="font-size:40px;display:block;margin-bottom:10px;"></i>No messages yet</div>';
        } else {
            new Chart(msgCtx, {
                type: 'doughnut',
                data: {
                    labels: ['New', 'Read', 'Replied'],
                    datasets: [{
                        data: msgData,
                        backgroundColor: ['rgba(59,130,246,0.85)', 'rgba(100,116,139,0.85)', 'rgba(16,185,129,0.85)'],
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } }
                    },
                    cutout: '60%'
                }
            });
        }
    }

    // ── Pie: Content Distribution ──
    const distData = [
        <?php echo (int)$counts['lessons']; ?>,
        <?php echo (int)$counts['dictionary']; ?>,
        <?php echo (int)$counts['proverbs']; ?>,
        <?php echo (int)$counts['articles']; ?>,
        <?php echo (int)$counts['grammar_topics']; ?>,
        <?php echo (int)$counts['translations']; ?>,
        <?php echo (int)$counts['media']; ?>
    ];
    const distTotal = distData.reduce((a, b) => a + b, 0);
    const distCtx = document.getElementById('distChart');
    if (distCtx) {
        if (distTotal === 0) {
            distCtx.parentElement.innerHTML = '<div style="text-align:center;padding:40px;color:#94a3b8;"><i class="bi bi-pie-chart" style="font-size:40px;display:block;margin-bottom:10px;"></i>No content yet</div>';
        } else {
            new Chart(distCtx, {
                type: 'pie',
                data: {
                    labels: ['Lessons', 'Dictionary', 'Proverbs', 'Articles', 'Grammar', 'Translations', 'Media'],
                    datasets: [{
                        data: distData,
                        backgroundColor: [
                            'rgba(102,126,234,0.85)', 'rgba(240,147,251,0.85)', 'rgba(79,172,254,0.85)',
                            'rgba(67,233,123,0.85)', 'rgba(245,158,11,0.85)', 'rgba(16,185,129,0.85)', 'rgba(250,112,154,0.85)'
                        ],
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true } },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const pct = ((ctx.parsed / distTotal) * 100).toFixed(1);
                                    return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

});
</script>
</body>
</html>
