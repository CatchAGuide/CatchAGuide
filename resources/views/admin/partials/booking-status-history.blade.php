{{-- Status history modal for camp/vacation and trip requests. Opened by any .js-status-summary button with a data-url. --}}
<style>
    .js-status-summary { display: block; margin-top: .25rem; font-size: .75rem; line-height: 1.2; text-align: left; white-space: normal; text-decoration: none; }
    .js-status-summary:hover { text-decoration: underline; }
    .status-history-list { list-style: none; margin: 0; padding: 0; }
    .status-history-list li { position: relative; padding: 0 0 1rem 1.5rem; border-left: 2px solid #dee2e6; margin-left: .4rem; }
    .status-history-list li:last-child { border-left-color: transparent; padding-bottom: 0; }
    .status-history-list li::before { content: ''; position: absolute; left: -.45rem; top: .2rem; width: .8rem; height: .8rem; border-radius: 50%; background: #adb5bd; }
    .status-history-list li.is-open::before { background: #0d6efd; }
    .status-history-list li.is-in_process::before { background: #ffc107; }
    .status-history-list li.is-done::before { background: #198754; }
</style>

<div class="modal fade" id="statusHistoryModal" tabindex="-1" aria-labelledby="statusHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusHistoryModalLabel">{{ __('admin.booking_status_history.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="js-status-history-loading text-muted">{{ __('admin.booking_status_history.loading') }}</div>
                <div class="js-status-history-empty text-muted d-none">{{ __('admin.booking_status_history.empty') }}</div>
                <ul class="js-status-history-list status-history-list d-none"></ul>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('statusHistoryModal');
        if (!modal) return;

        var text = {{ Js::from(['by' => __('admin.booking_status_history.by', ['name' => '__NAME__']), 'failed' => __('admin.booking_status_history.failed'), 'empty' => __('admin.booking_status_history.empty')]) }};

        function el(tag, className, content) {
            var node = document.createElement(tag);
            if (className) node.className = className;
            if (content) node.textContent = content;
            return node;
        }

        modal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            var url = trigger ? trigger.getAttribute('data-url') : null;
            var loading = modal.querySelector('.js-status-history-loading');
            var empty = modal.querySelector('.js-status-history-empty');
            var list = modal.querySelector('.js-status-history-list');

            loading.classList.remove('d-none');
            empty.classList.add('d-none');
            empty.textContent = text.empty;
            list.classList.add('d-none');
            list.innerHTML = '';
            if (!url) return;

            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
                .then(function (data) {
                    loading.classList.add('d-none');
                    var items = (data && data.items) || [];
                    if (!items.length) {
                        empty.classList.remove('d-none');
                        return;
                    }
                    items.forEach(function (item) {
                        var li = el('li', 'is-' + item.to);
                        var title = el('div', 'fw-semibold');
                        title.textContent = item.from_label ? item.from_label + ' → ' + item.to_label : item.to_label;
                        li.appendChild(title);
                        var meta = text.by.replace('__NAME__', item.by || '') + (item.at ? ' · ' + item.at : '');
                        li.appendChild(el('div', 'text-muted small', meta));
                        if (item.reason) li.appendChild(el('span', 'badge bg-light text-dark mt-1', item.reason));
                        list.appendChild(li);
                    });
                    list.classList.remove('d-none');
                })
                .catch(function () {
                    loading.classList.add('d-none');
                    empty.textContent = text.failed;
                    empty.classList.remove('d-none');
                });
        });
    })();
</script>
