{{-- Shared styles + scripts for the Terms & Conditions (AGB) page --}}
@push('styles')
<style>
    .terms-page {
        background: #f7f6f4;
        padding: 10px 0 90px;
    }

    /* Reading progress bar */
    .terms-progress {
        position: fixed;
        top: 0;
        left: 0;
        height: 4px;
        width: 0;
        background: linear-gradient(90deg, #E8604C, #f0836f);
        z-index: 1060;
        transition: width .1s linear;
    }

    /* Hero */
    .terms-hero {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        background: linear-gradient(135deg, #313041 0%, #3e3d54 60%, #4a4265 100%);
        color: #fff;
        padding: 48px 44px;
        margin-bottom: 34px;
    }
    .terms-hero::after {
        content: "\f578"; /* fa-fish */
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        position: absolute;
        right: -30px;
        bottom: -55px;
        font-size: 240px;
        color: rgba(255, 255, 255, 0.05);
        transform: rotate(-20deg);
        pointer-events: none;
    }
    .terms-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(232, 96, 76, 0.18);
        color: #f5a394;
        border: 1px solid rgba(232, 96, 76, 0.4);
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 6px 14px;
        border-radius: 100px;
        margin-bottom: 18px;
    }
    .terms-hero h1 {
        color: #fff;
        font-size: 34px;
        font-weight: 800;
        margin-bottom: 10px;
    }
    .terms-hero__subtitle {
        color: rgba(255, 255, 255, 0.75);
        font-size: 16px;
        max-width: 720px;
        margin-bottom: 22px;
    }
    .terms-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }
    .terms-hero__badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: rgba(255, 255, 255, 0.85);
        font-size: 13.5px;
        padding: 7px 14px;
        border-radius: 100px;
    }
    .terms-hero__badge i { color: #E8604C; }
    .terms-print-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
        background: #E8604C;
        color: #fff;
        border: none;
        font-size: 14px;
        font-weight: 600;
        padding: 9px 20px;
        border-radius: 100px;
        transition: background .25s ease, transform .25s ease;
    }
    .terms-print-btn:hover {
        background: #d24b37;
        color: #fff;
        transform: translateY(-1px);
    }

    /* Sidebar */
    .terms-sidebar-wrap {
        position: sticky;
        top: 105px;
        z-index: 10;
    }
    .terms-toc-card {
        background: #fff;
        border: 1px solid #ebe9e4;
        border-radius: 16px;
        box-shadow: 0 6px 24px rgba(49, 48, 65, 0.06);
        overflow: hidden;
    }
    .terms-toc-card__header {
        padding: 18px 20px 0;
    }
    .terms-toc-card__title {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #8b8a96;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .terms-toc-toggle {
        display: none;
        background: none;
        border: none;
        color: #313041;
        font-size: 16px;
        padding: 0;
    }
    .terms-search {
        position: relative;
        margin-bottom: 6px;
    }
    .terms-search i.fa-search {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #b5b3ae;
        font-size: 14px;
        pointer-events: none;
    }
    .terms-search input {
        width: 100%;
        border: 1.5px solid #e7e5df;
        border-radius: 100px;
        background: #faf9f7;
        padding: 9px 38px 9px 38px;
        font-size: 14px;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .terms-search input:focus {
        border-color: #E8604C;
        box-shadow: 0 0 0 4px rgba(232, 96, 76, 0.12);
        background: #fff;
    }
    .terms-search__clear {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: none;
        color: #b5b3ae;
        font-size: 13px;
        padding: 4px;
        display: none;
    }
    .terms-search__clear:hover { color: #E8604C; }
    .terms-search-count {
        font-size: 12.5px;
        color: #8b8a96;
        padding: 0 20px 4px;
        min-height: 22px;
    }
    .terms-toc {
        list-style: none;
        margin: 0;
        padding: 8px 10px 14px;
        max-height: calc(100vh - 320px);
        overflow-y: auto;
    }
    .terms-toc::-webkit-scrollbar { width: 5px; }
    .terms-toc::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
    .terms-toc a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 9px 12px;
        border-radius: 10px;
        color: #55545e;
        font-size: 14px;
        line-height: 1.35;
        transition: background .2s ease, color .2s ease;
    }
    .terms-toc a:hover {
        background: #faf3f1;
        color: #E8604C;
    }
    .terms-toc a .terms-toc__num {
        flex-shrink: 0;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        background: #f2f1ee;
        color: #8b8a96;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background .2s ease, color .2s ease;
    }
    .terms-toc li.active a {
        background: #E8604C;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 6px 16px rgba(232, 96, 76, 0.3);
    }
    .terms-toc li.active a .terms-toc__num {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
    }
    .terms-toc li.terms-toc--hidden { display: none; }

    /* Section cards */
    .terms-section {
        background: #fff;
        border: 1px solid #ebe9e4;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(49, 48, 65, 0.04);
        padding: 30px 34px;
        margin-bottom: 22px;
        scroll-margin-top: 120px;
        transition: box-shadow .25s ease, border-color .25s ease;
    }
    .terms-section:hover {
        box-shadow: 0 10px 30px rgba(49, 48, 65, 0.08);
    }
    .terms-section.terms-section--hidden { display: none; }
    .terms-section--flash {
        border-color: #E8604C;
        box-shadow: 0 0 0 4px rgba(232, 96, 76, 0.15);
    }
    .terms-section__header {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 18px;
    }
    .terms-section__icon {
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        border-radius: 13px;
        background: linear-gradient(135deg, rgba(232, 96, 76, 0.12), rgba(232, 96, 76, 0.2));
        color: #E8604C;
        font-size: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .terms-section__heading { flex: 1 1 auto; min-width: 0; }
    .terms-section__kicker {
        display: block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #E8604C;
        margin-bottom: 2px;
    }
    .terms-section__title {
        font-size: 20px;
        font-weight: 700;
        color: #313041;
        margin: 0;
        line-height: 1.35;
    }
    .terms-copy-link {
        flex-shrink: 0;
        border: 1px solid #ebe9e4;
        background: #fff;
        color: #b5b3ae;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        font-size: 13px;
        transition: all .2s ease;
        position: relative;
    }
    .terms-copy-link:hover {
        color: #E8604C;
        border-color: #E8604C;
    }
    .terms-copy-link.copied {
        background: #E8604C;
        border-color: #E8604C;
        color: #fff;
    }
    .terms-section__body {
        color: #55545e;
        font-size: 15.5px;
        line-height: 1.75;
    }
    .terms-section__body p { margin-bottom: 12px; }
    .terms-section__body p:last-child { margin-bottom: 0; }
    .terms-section__body ol {
        list-style: none;
        counter-reset: terms-item;
        padding-left: 0;
        margin-bottom: 0;
    }
    .terms-section__body ol > li {
        counter-increment: terms-item;
        position: relative;
        padding-left: 44px;
        margin-bottom: 14px;
    }
    .terms-section__body ol > li:last-child { margin-bottom: 0; }
    .terms-section__body ol > li::before {
        content: counter(terms-item);
        position: absolute;
        left: 0;
        top: 2px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #f2f1ee;
        color: #8b8a96;
        font-size: 12.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .terms-section__body h2,
    .terms-section__body h3,
    .terms-section__body h4 {
        color: #313041;
        margin-top: 26px;
        margin-bottom: 10px;
        line-height: 1.35;
    }
    .terms-section__body h2 {
        font-size: 21px;
        font-weight: 700;
        padding-bottom: 8px;
        border-bottom: 2px solid rgba(232, 96, 76, 0.35);
    }
    .terms-section__body h3 { font-size: 17.5px; font-weight: 700; }
    .terms-section__body h4 { font-size: 16px; font-weight: 600; }
    .terms-section__body ul { padding-left: 22px; margin-bottom: 12px; }
    .terms-section__body ul > li { margin-bottom: 6px; }
    .terms-section__body a { color: #E8604C; word-break: break-word; }
    .terms-section__body a:hover { text-decoration: underline; }
    .terms-section__body mark {
        background: rgba(232, 96, 76, 0.25);
        color: inherit;
        padding: 0 2px;
        border-radius: 3px;
    }
    .terms-section__title mark {
        background: rgba(232, 96, 76, 0.25);
        color: inherit;
        padding: 0 2px;
        border-radius: 3px;
    }

    /* Empty search state */
    .terms-no-results {
        display: none;
        background: #fff;
        border: 1px dashed #ddd;
        border-radius: 16px;
        text-align: center;
        padding: 60px 30px;
        color: #8b8a96;
    }
    .terms-no-results i {
        font-size: 34px;
        color: #E8604C;
        opacity: .5;
        margin-bottom: 14px;
        display: block;
    }

    /* Prev / next pager */
    .terms-pager {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-top: 6px;
    }
    .terms-pager__link {
        flex: 0 1 48%;
        display: flex;
        flex-direction: column;
        gap: 4px;
        background: #fff;
        border: 1px solid #ebe9e4;
        border-radius: 14px;
        padding: 16px 20px;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .terms-pager__link:hover {
        border-color: #E8604C;
        box-shadow: 0 8px 22px rgba(232, 96, 76, 0.12);
        transform: translateY(-2px);
    }
    .terms-pager__link--next { text-align: right; margin-left: auto; }
    .terms-pager__label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #E8604C;
    }
    .terms-pager__title {
        font-size: 14.5px;
        font-weight: 600;
        color: #313041;
        line-height: 1.4;
    }
    .terms-pager__link:hover .terms-pager__title { color: #E8604C; }

    /* Back to top */
    .terms-back-top {
        position: fixed;
        right: 26px;
        bottom: 26px;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        border: none;
        background: #313041;
        color: #fff;
        font-size: 15px;
        box-shadow: 0 8px 24px rgba(49, 48, 65, 0.35);
        opacity: 0;
        visibility: hidden;
        transform: translateY(12px);
        transition: all .3s ease;
        z-index: 1040;
    }
    .terms-back-top.visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .terms-back-top:hover { background: #E8604C; }

    @media (max-width: 991.98px) {
        .terms-hero { padding: 34px 26px; }
        .terms-hero h1 { font-size: 26px; }
        .terms-sidebar-wrap {
            position: static;
            margin-bottom: 22px;
        }
        .terms-toc-toggle { display: inline-block; }
        .terms-toc { max-height: 0; padding-top: 0; padding-bottom: 0; overflow: hidden; transition: max-height .3s ease; }
        .terms-toc-card.is-open .terms-toc { max-height: 60vh; padding-bottom: 14px; overflow-y: auto; }
        .terms-toc-card.is-open .terms-toc-toggle i { transform: rotate(180deg); }
        .terms-toc-toggle i { transition: transform .3s ease; }
        .terms-section { padding: 24px 20px; }
        .terms-section__icon { width: 40px; height: 40px; font-size: 16px; border-radius: 11px; }
        .terms-print-btn { margin-left: 0; }
    }

    @media print {
        .terms-progress,
        .terms-sidebar-wrap,
        .terms-print-btn,
        .terms-copy-link,
        .terms-pager,
        .terms-back-top,
        .page-header,
        .navbar-custom,
        .site-footer,
        .mobile-nav__wrapper,
        #cookie-consent-banner { display: none !important; }
        .terms-page { background: #fff; padding: 0; }
        .terms-hero {
            background: none;
            color: #000;
            padding: 0 0 20px;
            border-radius: 0;
        }
        .terms-hero h1, .terms-hero__subtitle { color: #000; }
        .terms-hero::after { content: none; }
        .terms-hero__eyebrow, .terms-hero__badge { border-color: #999; color: #333; background: none; }
        .terms-section {
            box-shadow: none;
            border: none;
            padding: 0 0 24px;
            page-break-inside: avoid;
        }
    }
</style>
@endpush

@push('js_push')
<script>
(function () {
    var L = {
        copied: @json(__('terms.copied')),
        results: @json(__('terms.results_count')),
        minRead: @json(__('terms.min_read'))
    };

    var sections = Array.prototype.slice.call(document.querySelectorAll('.terms-section'));
    if (!sections.length) return;

    var tocList = document.getElementById('termsToc');
    var tocCard = document.getElementById('termsTocCard');
    var tocToggle = document.getElementById('termsTocToggle');
    var searchInput = document.getElementById('termsSearch');
    var searchClear = document.getElementById('termsSearchClear');
    var searchCount = document.getElementById('termsSearchCount');
    var noResults = document.getElementById('termsNoResults');
    var progressBar = document.getElementById('termsProgress');
    var backTop = document.getElementById('termsBackTop');
    var contentWrap = document.getElementById('termsContent');
    var readTimeEl = document.getElementById('termsReadTime');

    /* 'links' = sidebar items are real page links (one page per section);
       'anchors' = single page, TOC is built from in-page sections. */
    var mode = tocList && tocList.getAttribute('data-mode') === 'links' ? 'links' : 'anchors';

    /* Reading time estimate */
    if (readTimeEl && contentWrap) {
        var words = contentWrap.textContent.trim().split(/\s+/).length;
        readTimeEl.textContent = '~' + Math.max(1, Math.round(words / 200)) + ' ' + L.minRead;
    }

    /* Keep pristine copies of section bodies/titles for search highlighting */
    sections.forEach(function (section) {
        var body = section.querySelector('.terms-section__body');
        var title = section.querySelector('.terms-section__title');
        if (body) body.dataset.original = body.innerHTML;
        if (title) title.dataset.original = title.innerHTML;
    });

    var tocItems = [];
    if (tocList && mode === 'links') {
        tocItems = Array.prototype.slice.call(tocList.children);
    } else if (tocList) {
        /* Build anchor TOC from in-page section headings */
        sections.forEach(function (section, i) {
            var title = section.querySelector('.terms-section__title');
            var li = document.createElement('li');
            var a = document.createElement('a');
            a.href = '#' + section.id;
            a.innerHTML = '<span class="terms-toc__num">' + (i + 1) + '</span><span>' +
                (title ? title.textContent : section.id) + '</span>';
            a.addEventListener('click', function (e) {
                e.preventDefault();
                scrollToSection(section);
                if (window.innerWidth < 992 && tocCard) tocCard.classList.remove('is-open');
            });
            li.appendChild(a);
            tocList.appendChild(li);
            tocItems.push(li);
        });
    }

    function scrollToSection(section) {
        var top = section.getBoundingClientRect().top + window.pageYOffset - 110;
        window.scrollTo({ top: top, behavior: 'smooth' });
        history.replaceState(null, '', '#' + section.id);
        section.classList.add('terms-section--flash');
        setTimeout(function () { section.classList.remove('terms-section--flash'); }, 1500);
    }

    /* Scrollspy (anchors mode only) + progress bar + back-to-top */
    function onScroll() {
        if (mode === 'anchors' && tocItems.length) {
            var visible = sections.filter(function (s) { return !s.classList.contains('terms-section--hidden'); });
            if (visible.length) {
                var pos = window.pageYOffset + 140;
                var current = visible[0];
                visible.forEach(function (s) { if (s.offsetTop <= pos) current = s; });
                var idx = sections.indexOf(current);
                tocItems.forEach(function (li, i) { li.classList.toggle('active', i === idx); });
            }
        }
        if (progressBar && contentWrap) {
            var start = contentWrap.offsetTop - 110;
            var total = contentWrap.offsetHeight - window.innerHeight + 160;
            var pct = total > 0 ? Math.min(1, Math.max(0, (window.pageYOffset - start) / total)) : 0;
            progressBar.style.width = (pct * 100) + '%';
        }
        if (backTop) backTop.classList.toggle('visible', window.pageYOffset > 600);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    onScroll();

    if (backTop) {
        backTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* Mobile TOC toggle */
    if (tocToggle && tocCard) {
        tocToggle.addEventListener('click', function () {
            tocCard.classList.toggle('is-open');
        });
    }

    /* Copy link: in links mode each section is its own page, so copy the page URL */
    document.querySelectorAll('.terms-copy-link').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = location.origin + location.pathname;
            if (mode === 'anchors') {
                var section = btn.closest('.terms-section');
                url += '#' + section.id;
            }
            var done = function () {
                btn.classList.add('copied');
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.setAttribute('title', L.copied);
                setTimeout(function () {
                    btn.classList.remove('copied');
                    btn.innerHTML = '<i class="fas fa-link"></i>';
                    btn.setAttribute('title', '');
                }, 1600);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done);
            } else {
                var tmp = document.createElement('input');
                tmp.value = url;
                document.body.appendChild(tmp);
                tmp.select();
                document.execCommand('copy');
                document.body.removeChild(tmp);
                done();
            }
        });
    });

    /* Search with highlighting */
    function escapeRegExp(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function highlight(el, regex) {
        var walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null, false);
        var nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        nodes.forEach(function (node) {
            if (!regex.test(node.nodeValue)) return;
            var frag = document.createDocumentFragment();
            var last = 0;
            node.nodeValue.replace(new RegExp(regex.source, 'gi'), function (match, offset) {
                frag.appendChild(document.createTextNode(node.nodeValue.slice(last, offset)));
                var mark = document.createElement('mark');
                mark.textContent = match;
                frag.appendChild(mark);
                last = offset + match.length;
            });
            frag.appendChild(document.createTextNode(node.nodeValue.slice(last)));
            node.parentNode.replaceChild(frag, node);
        });
    }

    function resetHighlights() {
        sections.forEach(function (section) {
            var body = section.querySelector('.terms-section__body');
            var title = section.querySelector('.terms-section__title');
            if (body) body.innerHTML = body.dataset.original;
            if (title) title.innerHTML = title.dataset.original;
        });
    }

    function highlightSection(section, query) {
        var regex = new RegExp(escapeRegExp(query), 'i');
        var body = section.querySelector('.terms-section__body');
        var title = section.querySelector('.terms-section__title');
        if (title) highlight(title, regex);
        if (body) highlight(body, regex);
    }

    function sectionMatches(section, query) {
        var body = section.querySelector('.terms-section__body');
        var title = section.querySelector('.terms-section__title');
        var haystack = ((title ? title.textContent : '') + ' ' + (body ? body.textContent : '')).toLowerCase();
        return haystack.indexOf(query.toLowerCase()) !== -1;
    }

    function runSearch() {
        var query = (searchInput ? searchInput.value : '').trim();
        if (searchClear) searchClear.style.display = query ? 'block' : 'none';

        resetHighlights();

        if (mode === 'links') {
            /* Filter sidebar links by title; highlight matches in this page's content */
            var navMatches = 0;
            tocItems.forEach(function (li) {
                var hit = !query || li.textContent.toLowerCase().indexOf(query.toLowerCase()) !== -1;
                li.classList.toggle('terms-toc--hidden', !hit);
                if (query && hit) navMatches++;
            });

            var contentHit = false;
            if (query) {
                sections.forEach(function (section) {
                    if (sectionMatches(section, query)) {
                        contentHit = true;
                        highlightSection(section, query);
                    }
                });
            }

            if (searchCount) {
                searchCount.textContent = query
                    ? L.results.replace(':count', navMatches).replace(':total', tocItems.length)
                    : '';
            }
            if (noResults) noResults.style.display = (query && !navMatches && !contentHit) ? 'block' : 'none';
        } else {
            var matches = 0;
            sections.forEach(function (section, i) {
                if (!query) {
                    section.classList.remove('terms-section--hidden');
                    if (tocItems[i]) tocItems[i].classList.remove('terms-toc--hidden');
                    return;
                }
                var hit = sectionMatches(section, query);
                section.classList.toggle('terms-section--hidden', !hit);
                if (tocItems[i]) tocItems[i].classList.toggle('terms-toc--hidden', !hit);
                if (hit) {
                    matches++;
                    highlightSection(section, query);
                }
            });

            if (searchCount) {
                searchCount.textContent = query
                    ? L.results.replace(':count', matches).replace(':total', sections.length)
                    : '';
            }
            if (noResults) noResults.style.display = (query && matches === 0) ? 'block' : 'none';
        }

        onScroll();
    }

    if (searchInput) {
        var debounce;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounce);
            debounce = setTimeout(runSearch, 180);
        });
    }
    if (searchClear) {
        searchClear.addEventListener('click', function () {
            searchInput.value = '';
            runSearch();
            searchInput.focus();
        });
    }

    /* Deep link: scroll to hash target on load (anchors mode) */
    if (mode === 'anchors' && location.hash) {
        var target = document.getElementById(location.hash.slice(1));
        if (target && target.classList.contains('terms-section')) {
            setTimeout(function () { scrollToSection(target); }, 300);
        }
    }
})();
</script>
@endpush
