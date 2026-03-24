/**
 * Table/Card Utilities: Pagination, Refresh, Export CSV, Print
 * Usage:
 *   TableUtils.init({ tableId, rowsPerPage, exportName, exportColumns })
 *   TableUtils.initCards({ containerId, cardSelector, rowsPerPage, exportName, exportFn })
 */

const TableUtils = (() => {

    // ── Shared helpers ──────────────────────────────────────────────

    function buildToolbar(title) {
        const bar = document.createElement('div');
        bar.className = 'tu-toolbar';
        bar.innerHTML = `
            <span class="tu-info" id="tu-info"></span>
            <div class="tu-actions">
                <button class="tu-btn" id="tu-refresh" title="Refresh"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
                <button class="tu-btn" id="tu-export" title="Export CSV"><i class="bi bi-download"></i> Export</button>
                <button class="tu-btn" id="tu-print" title="Print"><i class="bi bi-printer"></i> Print</button>
            </div>`;
        return bar;
    }

    function buildPagination() {
        const wrap = document.createElement('div');
        wrap.className = 'tu-pagination';
        wrap.id = 'tu-pagination';
        return wrap;
    }

    function renderPagination(container, currentPage, totalPages, onPageChange) {
        container.innerHTML = '';
        if (totalPages <= 1) return;

        const prev = document.createElement('button');
        prev.className = 'tu-page-btn' + (currentPage === 1 ? ' disabled' : '');
        prev.innerHTML = '<i class="bi bi-chevron-left"></i>';
        prev.disabled = currentPage === 1;
        prev.onclick = () => onPageChange(currentPage - 1);
        container.appendChild(prev);

        // Page numbers with ellipsis
        const pages = getPageNumbers(currentPage, totalPages);
        pages.forEach(p => {
            if (p === '...') {
                const dots = document.createElement('span');
                dots.className = 'tu-page-dots';
                dots.textContent = '…';
                container.appendChild(dots);
            } else {
                const btn = document.createElement('button');
                btn.className = 'tu-page-btn' + (p === currentPage ? ' active' : '');
                btn.textContent = p;
                btn.onclick = () => onPageChange(p);
                container.appendChild(btn);
            }
        });

        const next = document.createElement('button');
        next.className = 'tu-page-btn' + (currentPage === totalPages ? ' disabled' : '');
        next.innerHTML = '<i class="bi bi-chevron-right"></i>';
        next.disabled = currentPage === totalPages;
        next.onclick = () => onPageChange(currentPage + 1);
        container.appendChild(next);
    }

    function getPageNumbers(current, total) {
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
        if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
        if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
        return [1, '...', current - 1, current, current + 1, '...', total];
    }

    function downloadCSV(filename, rows) {
        const csv = rows.map(r => r.map(c => `"${String(c ?? '').replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = filename + '_' + new Date().toISOString().slice(0, 10) + '.csv';
        a.click();
    }

    function printContent(title, html) {
        const win = window.open('', '_blank');
        win.document.write(`<!DOCTYPE html><html><head>
            <title>${title}</title>
            <style>
                body { font-family: Inter, sans-serif; padding: 24px; color: #1e293b; }
                h2 { margin-bottom: 16px; font-size: 20px; }
                table { width: 100%; border-collapse: collapse; font-size: 13px; }
                th { background: #f1f5f9; padding: 10px 12px; text-align: left; border-bottom: 2px solid #e2e8f0; }
                td { padding: 9px 12px; border-bottom: 1px solid #e2e8f0; }
                tr:last-child td { border-bottom: none; }
                .no-print { display: none !important; }
                @media print { body { padding: 0; } }
            </style>
        </head><body><h2>${title}</h2>${html}</body></html>`);
        win.document.close();
        win.focus();
        setTimeout(() => { win.print(); win.close(); }, 300);
    }

    // ── TABLE mode (for pages with <table>) ─────────────────────────

    function init({ tableId, rowsPerPage = 10, exportName = 'export', exportColumns = null }) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const container = table.closest('.content-table');
        if (!container) return;

        // Insert toolbar before table
        const toolbar = buildToolbar(exportName);
        table.parentNode.insertBefore(toolbar, table);

        // Insert pagination after table
        const pagination = buildPagination();
        table.parentNode.insertBefore(pagination, table.nextSibling);

        // Per-page selector
        const perPageSel = document.createElement('select');
        perPageSel.className = 'tu-per-page';
        perPageSel.innerHTML = [10, 25, 50, 100].map(n => `<option value="${n}"${n === rowsPerPage ? ' selected' : ''}>${n} per page</option>`).join('');
        toolbar.querySelector('.tu-actions').prepend(perPageSel);

        let currentPage = 1;
        let perPage = rowsPerPage;

        function getVisibleRows() {
            return Array.from(table.querySelectorAll('tbody tr')).filter(r => r.style.display !== 'none');
        }

        function render() {
            const rows = getVisibleRows();
            const total = rows.length;
            const totalPages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            rows.forEach((r, i) => {
                r.style.display = (i >= (currentPage - 1) * perPage && i < currentPage * perPage) ? '' : 'none';
            });

            // Re-hide rows that were hidden by search
            Array.from(table.querySelectorAll('tbody tr')).forEach(r => {
                if (r._searchHidden) r.style.display = 'none';
            });

            const start = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
            const end = Math.min(currentPage * perPage, total);
            document.getElementById('tu-info').textContent = total === 0 ? 'No records' : `Showing ${start}–${end} of ${total}`;

            renderPagination(pagination, currentPage, totalPages, (p) => { currentPage = p; render(); });
        }

        perPageSel.addEventListener('change', () => { perPage = +perPageSel.value; currentPage = 1; render(); });

        // Refresh
        document.getElementById('tu-refresh').addEventListener('click', () => {
            const btn = document.getElementById('tu-refresh');
            btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Refreshing...';
            btn.disabled = true;
            setTimeout(() => location.reload(), 400);
        });

        // Export CSV
        document.getElementById('tu-export').addEventListener('click', () => {
            const headers = Array.from(table.querySelectorAll('thead th'))
                .map(th => th.textContent.trim())
                .filter((_, i, arr) => i < arr.length - 1); // skip Actions col

            const rows = Array.from(table.querySelectorAll('tbody tr'))
                .filter(r => !r._searchHidden)
                .map(r => Array.from(r.querySelectorAll('td'))
                    .slice(0, headers.length)
                    .map(td => td.textContent.trim()));

            downloadCSV(exportName, [headers, ...rows]);
        });

        // Print
        document.getElementById('tu-print').addEventListener('click', () => {
            const headers = Array.from(table.querySelectorAll('thead th'))
                .map(th => th.textContent.trim())
                .filter((_, i, arr) => i < arr.length - 1);

            const bodyRows = Array.from(table.querySelectorAll('tbody tr'))
                .filter(r => !r._searchHidden)
                .map(r => `<tr>${Array.from(r.querySelectorAll('td')).slice(0, headers.length).map(td => `<td>${td.textContent.trim()}</td>`).join('')}</tr>`)
                .join('');

            const html = `<table><thead><tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr></thead><tbody>${bodyRows}</tbody></table>`;
            printContent(exportName, html);
        });

        // Hook into existing search to re-paginate
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            const origHandler = searchInput.oninput || searchInput.onkeyup;
            searchInput.addEventListener('input', () => {
                // Mark hidden rows
                setTimeout(() => {
                    Array.from(table.querySelectorAll('tbody tr')).forEach(r => {
                        r._searchHidden = r.style.display === 'none';
                    });
                    currentPage = 1;
                    render();
                }, 10);
            });
        }

        render();
    }

    // ── CARDS mode (for pages with card grids) ──────────────────────

    function initCards({ containerId, cardSelector, rowsPerPage = 9, exportName = 'export', exportFn = null }) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const outer = container.closest('.content-table');
        if (!outer) return;

        const toolbar = buildToolbar(exportName);
        outer.insertBefore(toolbar, container);

        const pagination = buildPagination();
        outer.insertBefore(pagination, container.nextSibling);

        const perPageSel = document.createElement('select');
        perPageSel.className = 'tu-per-page';
        perPageSel.innerHTML = [6, 9, 18, 36].map(n => `<option value="${n}"${n === rowsPerPage ? ' selected' : ''}>${n} per page</option>`).join('');
        toolbar.querySelector('.tu-actions').prepend(perPageSel);

        let currentPage = 1;
        let perPage = rowsPerPage;

        function getCards() {
            return Array.from(container.querySelectorAll(cardSelector));
        }

        function render() {
            const cards = getCards();
            const total = cards.length;
            const totalPages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            cards.forEach((c, i) => {
                c.style.display = (i >= (currentPage - 1) * perPage && i < currentPage * perPage) ? '' : 'none';
            });

            const start = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
            const end = Math.min(currentPage * perPage, total);
            document.getElementById('tu-info').textContent = total === 0 ? 'No records' : `Showing ${start}–${end} of ${total}`;

            renderPagination(pagination, currentPage, totalPages, (p) => { currentPage = p; render(); });
        }

        perPageSel.addEventListener('change', () => { perPage = +perPageSel.value; currentPage = 1; render(); });

        document.getElementById('tu-refresh').addEventListener('click', () => {
            const btn = document.getElementById('tu-refresh');
            btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Refreshing...';
            btn.disabled = true;
            setTimeout(() => location.reload(), 400);
        });

        document.getElementById('tu-export').addEventListener('click', () => {
            if (exportFn) { exportFn(); return; }
            // Generic: grab all text from each card
            const rows = getCards().map(c => [c.textContent.replace(/\s+/g, ' ').trim()]);
            downloadCSV(exportName, rows);
        });

        document.getElementById('tu-print').addEventListener('click', () => {
            const cards = getCards();
            const rows = cards.map(c => {
                const texts = Array.from(c.querySelectorAll('[class*="title"],[class*="text"],[class*="translation"],[class*="meaning"],[class*="content"]'))
                    .map(el => el.textContent.trim()).filter(Boolean);
                return `<tr>${texts.map(t => `<td>${t}</td>`).join('')}</tr>`;
            }).join('');
            printContent(exportName, `<table><tbody>${rows}</tbody></table>`);
        });

        render();
    }

    return { init, initCards };
})();
