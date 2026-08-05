<script>
(function () {
    window.dataLayer = window.dataLayer || [];

    document.querySelectorAll('[data-magazine-analytics]').forEach(function (el) {
        el.addEventListener('click', function () {
            var detail = {
                event: el.getAttribute('data-magazine-analytics')
            };
            var slug = el.getAttribute('data-magazine-slug');
            var category = el.getAttribute('data-magazine-category');
            var share = el.getAttribute('data-magazine-share');
            if (slug) detail.magazine_slug = slug;
            if (category) detail.magazine_category = category;
            if (share) detail.share_channel = share;
            window.dataLayer.push(detail);
        });
    });

    var searchForm = document.querySelector('[data-magazine-analytics-form="magazine_search_submit"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function () {
            var input = searchForm.querySelector('input[name="q"]');
            window.dataLayer.push({
                event: 'magazine_search_submit',
                search_term: input ? input.value : ''
            });
        });
    }

    document.querySelectorAll('[data-copy-link]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-copy-link');
            var label = btn.querySelector('[data-copy-label]');
            var original = label ? label.textContent : '';
            var done = function () {
                if (label) {
                    label.textContent = @json(__('magazine.link_copied'));
                    setTimeout(function () { label.textContent = original; }, 2000);
                }
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done).catch(function () {});
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

    var page = document.querySelector('[data-analytics-page="magazine-article"]');
    if (page) {
        window.dataLayer.push({
            event: 'magazine_article_view',
            magazine_slug: page.getAttribute('data-magazine-slug') || '',
            magazine_category: page.getAttribute('data-magazine-category') || ''
        });
    }
})();
</script>
