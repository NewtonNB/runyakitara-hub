/**
 * Real-time content updater
 * Polls the API every 15s when tab is visible and updates the page if new content arrives.
 * Usage: add data-realtime="page-type" to the grid container.
 * Supported types: proverbs, grammar, lessons, dictionary, translations
 */
(function () {
    const POLL_INTERVAL = 15000; // 15 seconds
    let timers = [];

    // ── Renderers ────────────────────────────────────────────────────────────

    const renderers = {

        proverbs: (item) => `
            <div class="proverb-card" data-id="${item.id}">
                <div class="proverb-icon"><i class="bi bi-quote"></i></div>
                <div class="proverb-text">${esc(item.proverb)}</div>
                <div class="proverb-translation"><strong>Translation:</strong> ${esc(item.translation)}</div>
                <div class="proverb-meaning"><strong>Meaning:</strong> ${esc(item.meaning)}</div>
                <div data-engagement data-eng-type="proverb" data-eng-id="${item.id}"></div>
            </div>`,

        grammar: (item) => `
            <div class="grammar-card" data-id="${item.id}">
                <div class="grammar-icon"><i class="bi bi-book-half"></i></div>
                <h3>${esc(item.title)}</h3>
                <div class="grammar-content">${esc(item.content).replace(/\n/g,'<br>')}</div>
                ${item.examples ? `<div class="grammar-examples"><strong><i class="bi bi-lightbulb"></i> Examples:</strong><div>${esc(item.examples).replace(/\n/g,'<br>')}</div></div>` : ''}
                <div data-engagement data-eng-type="grammar" data-eng-id="${item.id}"></div>
            </div>`,

        lessons: (item) => `
            <div class="lesson-card" data-id="${item.id}">
                <div class="lesson-header">
                    <div class="lesson-number">Lesson ${item.lesson_order}</div>
                    <span class="level-badge level-${item.level.toLowerCase()}">${cap(item.level)}</span>
                </div>
                <h3>${esc(item.title)}</h3>
                <p class="lesson-description">${esc((item.description||'').substring(0,120))}</p>
                <div class="lesson-footer">
                    <span class="lesson-meta"><i class="bi bi-clock"></i> ${Math.max(1,Math.ceil(item.content.split(' ').length/150))} min</span>
                    <a href="lesson.php?id=${item.id}" class="lesson-start-btn">Start Lesson <i class="bi bi-arrow-right"></i></a>
                </div>
                <div data-engagement data-eng-type="lesson" data-eng-id="${item.id}"></div>
            </div>`,

        dictionary: (item) => `
            <div class="word-card" data-id="${item.id}">
                <div class="word-header">
                    <h3 class="word-runyakitara">${esc(item.word_runyakitara)}</h3>
                    ${item.category ? `<span class="word-category">${esc(item.category)}</span>` : ''}
                </div>
                <div class="word-translation">${esc(item.word_english)}</div>
                ${item.pronunciation ? `<div class="word-pronunciation"><i class="bi bi-volume-up"></i> ${esc(item.pronunciation)}</div>` : ''}
                ${item.example_sentence ? `<div class="word-example"><i class="bi bi-chat-text"></i> ${esc(item.example_sentence)}</div>` : ''}
            </div>`,

        translations: (item) => `
            <div class="translation-card" data-id="${item.id}">
                <div class="translation-header">
                    <h3>${esc(item.title)}</h3>
                    <span class="type-badge type-${item.type}">${cap(item.type)}</span>
                </div>
                <div class="translation-content">
                    <div class="translation-original">
                        <strong>Runyakitara:</strong>
                        <p>${esc(item.original_text)}</p>
                    </div>
                    <div class="translation-divider"><i class="bi bi-arrow-down-up"></i></div>
                    <div class="translation-translated">
                        <strong>English:</strong>
                        <p>${esc(item.translated_text)}</p>
                    </div>
                </div>
            </div>`,
    };

    // ── Helpers ───────────────────────────────────────────────────────────────

    function esc(str) {
        return String(str || '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function cap(str) { return str.charAt(0).toUpperCase() + str.slice(1); }

    function showToast(msg) {
        let t = document.getElementById('rt-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'rt-toast';
            t.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#667eea;color:white;padding:12px 20px;border-radius:50px;font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(102,126,234,0.4);z-index:9999;opacity:0;transition:opacity 0.3s;pointer-events:none;';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.style.opacity = '1';
        clearTimeout(t._timer);
        t._timer = setTimeout(() => t.style.opacity = '0', 3000);
    }

    // ── Poller ────────────────────────────────────────────────────────────────

    function startPolling(container, type) {
        const apiMap = {
            proverbs:     'api/proverbs.php',
            grammar:      'api/grammar.php',
            lessons:      'api/lessons.php',
            dictionary:   'api/dictionary.php',
            translations: 'api/translations.php',
        };

        const endpoint = apiMap[type];
        if (!endpoint || !renderers[type]) return;

        // Track current IDs
        let knownIds = new Set(
            [...container.querySelectorAll('[data-id]')].map(el => el.dataset.id)
        );

        const poll = () => {
            if (document.hidden) return; // skip if tab not visible

            fetch(endpoint)
                .then(r => r.json())
                .then(data => {
                    const items = data.data || data; // handle both {data:[]} and []
                    if (!Array.isArray(items)) return;

                    let added = 0;
                    items.forEach(item => {
                        const id = String(item.id);
                        if (!knownIds.has(id)) {
                            knownIds.add(id);
                            const html = renderers[type](item);
                            const temp = document.createElement('div');
                            temp.innerHTML = html;
                            const newEl = temp.firstElementChild;
                            newEl.style.animation = 'rtFadeIn 0.5s ease';
                            container.prepend(newEl);

                            // Init engagement widget if present
                            const engEl = newEl.querySelector('[data-engagement]');
                            if (engEl && window.EngagementWidget) {
                                EngagementWidget.init(engEl, type.replace(/s$/, ''), item.id);
                            }
                            added++;
                        }
                    });

                    if (added > 0) {
                        showToast(`${added} new ${type} added!`);
                    }
                })
                .catch(() => {}); // silent fail
        };

        const timer = setInterval(poll, POLL_INTERVAL);
        timers.push(timer);
    }

    // ── Init ──────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', () => {
        // Inject animation keyframe
        const style = document.createElement('style');
        style.textContent = '@keyframes rtFadeIn{from{opacity:0;transform:translateY(-12px)}to{opacity:1;transform:translateY(0)}}';
        document.head.appendChild(style);

        document.querySelectorAll('[data-realtime]').forEach(container => {
            startPolling(container, container.dataset.realtime);
        });
    });

})();
