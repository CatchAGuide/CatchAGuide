<script>
(function () {
    if (window.__categoryScopedEditorInit) {
        return;
    }
    window.__categoryScopedEditorInit = true;

    const languageDataUrl = @json($languageDataUrl ?? null);
    const autosaveUrl = @json($autosaveUrl ?? null);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const i18n = {
        saving: @json(__('admin.category_pages.editor.autosave_saving')),
        saved: @json(__('admin.category_pages.editor.autosave_saved')),
        error: @json(__('admin.category_pages.editor.autosave_error')),
    };
    const DEBOUNCE_MS = 1000;

    let faqIndex = 0;
    let debounceTimer = null;
    let saveChain = Promise.resolve();
    let dirty = false;
    let loadingTab = false;
    let switchingTab = false;
    let lastSnapshot = '';
    let statusTimer = null;

    const scopedFieldNames = new Set(['title', 'sub_title', 'introduction', 'content', 'faq_title']);

    window.addScopedFaqItem = function (question = '', answer = '') {
        const tbody = document.querySelector('#faq_table tbody');
        if (!tbody) return;
        const row = document.createElement('tr');
        const q = String(question).replace(/"/g, '&quot;');
        row.innerHTML = `
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="window.removeScopedFaqItem(this)"><i class="fa fa-trash"></i></button></td>
            <td><input type="text" class="form-control form-control-sm" name="faq[${faqIndex}][question]" value="${q}"></td>
            <td><textarea class="form-control form-control-sm" name="faq[${faqIndex}][answer]" rows="2">${answer}</textarea></td>
        `;
        tbody.appendChild(row);
        faqIndex++;
    };

    window.removeScopedFaqItem = function (button) {
        button.closest('tr')?.remove();
        scheduleAutosave();
    };

    window.syncCategoryEditors = function () {
        if (!window.CKEDITOR?.instances) return;
        Object.keys(CKEDITOR.instances).forEach((name) => {
            CKEDITOR.instances[name].updateElement();
        });
    };

    function currentScope() {
        return document.getElementById('content_scope')?.value || '';
    }

    function currentLocale() {
        return document.getElementById('languageSwitch')?.value || '';
    }

    function collectFaqs() {
        return Array.from(document.querySelectorAll('#faq_table tbody tr')).map((row) => ({
            question: row.querySelector('[name*="[question]"]')?.value || '',
            answer: row.querySelector('[name*="[answer]"]')?.value || '',
        }));
    }

    function collectPayload(scope, locale) {
        window.syncCategoryEditors();
        return {
            content_scope: scope ?? currentScope(),
            languageSwitch: locale ?? currentLocale(),
            title: document.getElementById('title')?.value || '',
            sub_title: document.getElementById('sub_title')?.value || '',
            introduction: document.getElementById('introduction')?.value || '',
            content: document.getElementById('content')?.value || '',
            faq_title: document.getElementById('faq_title')?.value || '',
            faq: collectFaqs(),
        };
    }

    function snapshotFor(payload) {
        return JSON.stringify(payload);
    }

    function markClean() {
        lastSnapshot = snapshotFor(collectPayload());
        dirty = false;
    }

    function setStatus(state) {
        const el = document.getElementById('category-autosave-status');
        if (!el) return;
        clearTimeout(statusTimer);
        el.hidden = false;
        el.classList.remove('is-saving', 'is-saved', 'is-error');
        if (state === 'saving') {
            el.textContent = i18n.saving;
            el.classList.add('is-saving');
            return;
        }
        if (state === 'saved') {
            el.textContent = i18n.saved;
            el.classList.add('is-saved');
            statusTimer = setTimeout(() => { el.hidden = true; }, 2500);
            return;
        }
        if (state === 'error') {
            el.textContent = i18n.error;
            el.classList.add('is-error');
        }
    }

    function applyCompleteness(completeness) {
        if (!completeness) return;
        document.querySelectorAll('.scope-tab').forEach((tab) => {
            const filled = !!(completeness[tab.dataset.scope]?.de || completeness[tab.dataset.scope]?.en);
            tab.classList.toggle('is-filled', filled);
            tab.classList.toggle('is-empty', !filled);
        });
    }

    function isScopedField(el) {
        if (!el || !el.name) return false;
        return scopedFieldNames.has(el.name) || /^faq\[\d+\]\[(question|answer)\]$/.test(el.name);
    }

    function postAutosave(payload, keepalive) {
        return fetch(autosaveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
            keepalive: !!keepalive,
        }).then((response) => {
            if (!response.ok) {
                throw new Error('autosave failed');
            }
            return response.json();
        });
    }

    function performAutosave(force) {
        if (!autosaveUrl || loadingTab) {
            return Promise.resolve(true);
        }

        const payload = collectPayload();
        const snap = snapshotFor(payload);

        if (!force && !dirty) {
            return Promise.resolve(true);
        }

        if (!force && snap === lastSnapshot) {
            dirty = false;
            return Promise.resolve(true);
        }

        setStatus('saving');

        return postAutosave(payload, false)
            .then((data) => {
                lastSnapshot = snap;
                dirty = false;
                applyCompleteness(data.completeness);
                setStatus('saved');
                return true;
            })
            .catch(() => {
                setStatus('error');
                return false;
            });
    }

    function flushAutosave(force) {
        clearTimeout(debounceTimer);
        debounceTimer = null;
        saveChain = saveChain.then(
            () => performAutosave(force),
            () => performAutosave(force),
        );
        return saveChain;
    }

    function scheduleAutosave() {
        if (!autosaveUrl || loadingTab || switchingTab) return;
        dirty = true;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            flushAutosave(false);
        }, DEBOUNCE_MS);
    }

    function applyLanguageData(data) {
        const title = document.getElementById('title');
        const subTitle = document.getElementById('sub_title');
        if (title) title.value = data.title || '';
        if (subTitle) subTitle.value = data.sub_title || '';
        if (window.CKEDITOR?.instances?.introduction) {
            CKEDITOR.instances.introduction.setData(data.introduction || '');
        }
        if (window.CKEDITOR?.instances?.content) {
            CKEDITOR.instances.content.setData(data.content || '');
        }
        const faqTitle = document.getElementById('faq_title');
        if (faqTitle) faqTitle.value = data.faq_title || '';
        const tbody = document.querySelector('#faq_table tbody');
        if (tbody) {
            tbody.innerHTML = '';
            faqIndex = 0;
            (data.faq || []).forEach((item) => window.addScopedFaqItem(item.question || '', item.answer || ''));
        }
    }

    window.loadScopedLanguageData = function () {
        if (!languageDataUrl) return Promise.resolve();
        const scope = currentScope();
        const locale = currentLocale();
        loadingTab = true;

        return fetch(`${languageDataUrl}?scope=${encodeURIComponent(scope)}&language=${encodeURIComponent(locale)}`)
            .then((response) => response.json())
            .then((data) => {
                applyLanguageData(data);
                return new Promise((resolve) => {
                    setTimeout(() => {
                        markClean();
                        loadingTab = false;
                        resolve();
                    }, 80);
                });
            })
            .catch(() => {
                loadingTab = false;
            });
    };

    window.loadLanguageData = window.loadScopedLanguageData;

    async function switchTab(kind, value, button) {
        if (switchingTab) return;
        const current = kind === 'scope' ? currentScope() : currentLocale();
        if (current === value) return;

        switchingTab = true;
        try {
            const saved = await flushAutosave(true);
            if (saved === false) {
                return;
            }

            const selector = kind === 'scope' ? '.scope-tab' : '.locale-tab';
            const inputId = kind === 'scope' ? 'content_scope' : 'languageSwitch';
            document.querySelectorAll(selector).forEach((el) => el.classList.remove('is-active'));
            button.classList.add('is-active');
            const input = document.getElementById(inputId);
            if (input) input.value = value;
            await window.loadScopedLanguageData();
        } finally {
            switchingTab = false;
        }
    }

    document.querySelectorAll('.scope-tab').forEach((button) => {
        button.addEventListener('click', () => switchTab('scope', button.dataset.scope, button));
    });

    document.querySelectorAll('.locale-tab').forEach((button) => {
        button.addEventListener('click', () => switchTab('locale', button.dataset.locale, button));
    });

    const form = document.getElementById('category-scoped-form') || document.querySelector('.category-editor form');
    if (form) {
        form.addEventListener('input', (event) => {
            if (isScopedField(event.target)) scheduleAutosave();
        });
        form.addEventListener('change', (event) => {
            if (isScopedField(event.target)) scheduleAutosave();
        });
        form.addEventListener('submit', () => {
            clearTimeout(debounceTimer);
            dirty = false;
            window.syncCategoryEditors();
        });
    }

    function bindCkeditorAutosave(editor) {
        if (!editor || editor.__categoryAutosaveBound) return;
        editor.__categoryAutosaveBound = true;
        ['change', 'key', 'paste', 'blur'].forEach((eventName) => {
            editor.on(eventName, () => scheduleAutosave());
        });
    }

    function bindAllCkeditorInstances() {
        if (!window.CKEDITOR?.instances) return;
        Object.keys(CKEDITOR.instances).forEach((name) => {
            bindCkeditorAutosave(CKEDITOR.instances[name]);
        });
    }

    if (window.CKEDITOR) {
        bindAllCkeditorInstances();
        CKEDITOR.on('instanceReady', (event) => bindCkeditorAutosave(event.editor));
    }

    window.addEventListener('pagehide', () => {
        if (!autosaveUrl || !dirty) return;
        const payload = collectPayload();
        if (snapshotFor(payload) === lastSnapshot) return;
        postAutosave(payload, true).catch(() => {});
    });

    @if(!empty($faq))
        @foreach($faq as $item)
            window.addScopedFaqItem(@json($item->question ?? $item['question'] ?? ''), @json($item->answer ?? $item['answer'] ?? ''));
        @endforeach
    @endif

    setTimeout(markClean, 400);
})();
</script>
