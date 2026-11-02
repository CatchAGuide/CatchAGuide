{{--
    Status UI shared by the admin request lists (camp/trip requests, contact requests, product reports):
    - the status history modal, opened by any .js-status-summary button with a data-url;
    - styles and the click handler for admin.partials.request-status-dropdown (incl. done reasons).
    Include once per page.
--}}
<style>
    .js-status-summary { display: block; margin-top: .25rem; font-size: .75rem; line-height: 1.2; text-align: left; white-space: normal; text-decoration: none; }
    .js-status-summary:hover { text-decoration: underline; }
    .status-history-list { list-style: none; margin: 0; padding: 0; }
    .status-history-list li { position: relative; padding: 0 0 1rem 1.5rem; border-left: 2px solid #dee2e6; margin-left: .4rem; }
    .status-history-list li:last-child { border-left-color: transparent; padding-bottom: 0; }
    .status-history-list li::before { content: ''; position: absolute; left: -.45rem; top: .2rem; width: .8rem; height: .8rem; border-radius: 50%; background: #adb5bd; }
    .status-history-list li.is-open::before { background: #0d6efd; }
    .status-history-list li.is-in_process::before { background: #ffc107; }
    .status-history-list li.is-done::before,
    .status-history-list li.is-done-booked::before { background: #198754; }
    .status-history-list li.is-done-no_reply::before { background: #64748b; }

    /* Status button: "Done · No reply" is a closed-but-lost request, so it is slate instead of green. */
    .js-status-dropdown-btn.btn-status-done.btn-done-no_reply { background-color: #64748b; border-color: #64748b; color: #fff; }
    .js-status-dropdown-btn.btn-status-done.btn-done-no_reply:hover { background-color: #475569; border-color: #475569; color: #fff; }
    .js-status-dropdown-btn[disabled] { opacity: .65; }

    .request-status-menu { min-width: 15rem; }
    .request-status-menu .dropdown-header { font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; }
    .request-status-option { display: flex; align-items: center; gap: .6rem; }
    .request-status-dot { width: .6rem; height: .6rem; border-radius: 50%; flex: 0 0 auto; background: #adb5bd; }
    .request-status-dot--open { background: #0d6efd; }
    .request-status-dot--in_process { background: #ffc107; }
    .request-status-dot--done { background: #198754; }
    .request-status-option__icon { width: 1rem; text-align: center; flex: 0 0 auto; }
    .request-status-option--booked .request-status-option__icon { color: #198754; }
    .request-status-option--no_reply .request-status-option__icon { color: #64748b; }
    .request-status-option__label { display: block; line-height: 1.2; }
    .request-status-option__hint { display: block; color: #6c757d; font-size: .72rem; line-height: 1.2; white-space: normal; }
    .request-status-option.active .request-status-option__icon,
    .request-status-option.active .request-status-option__hint { color: inherit; }

    /* Booked / no reply split next to the "Done" stat. */
    .done-split { display: inline-flex; gap: .5rem; font-size: .75rem; color: #6c757d; white-space: nowrap; }
    .done-split .fa-check-circle { color: #198754; }
    .done-split .fa-comment-slash { color: #64748b; }
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

        var text = {{ Js::from([
            'by' => __('admin.booking_status_history.by', ['name' => '__NAME__']),
            'failed' => __('admin.booking_status_history.failed'),
            'empty' => __('admin.booking_status_history.empty'),
            'update_failed' => __('admin.done_reason.update_failed'),
        ]) }};

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
                        var li = el('li', 'is-' + (item.variant || item.to));
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

        // Status menu (admin.partials.request-status-dropdown). Delegated, so rows on every
        // DataTables page work.
        var statusClasses = ['btn-status-open', 'btn-status-in_process', 'btn-status-done', 'btn-done-booked', 'btn-done-no_reply'];

        document.addEventListener('click', function (event) {
            var option = event.target.closest('.js-status-option');
            if (!option) return;
            event.preventDefault();

            var dropdown = option.closest('.dropdown');
            var btn = dropdown ? dropdown.querySelector('.js-status-dropdown-btn') : null;
            if (!btn || btn.disabled) return;

            var status = option.dataset.status;
            var doneReason = option.dataset.doneReason || '';
            var hide = function () {
                var instance = window.bootstrap && bootstrap.Dropdown ? bootstrap.Dropdown.getInstance(btn) : null;
                if (instance) instance.hide();
            };
            hide();
            if (status === btn.dataset.status && doneReason === (btn.dataset.doneReason || '')) return;

            var token = document.querySelector('meta[name="csrf-token"]');
            btn.disabled = true;

            fetch(btn.dataset.url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
                },
                body: JSON.stringify({ status: status, done_reason: doneReason || null })
            })
                .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
                .then(function (data) {
                    if (!data || !data.success) throw new Error('failed');
                    var savedReason = data.done_reason || '';

                    statusClasses.forEach(function (c) { btn.classList.remove(c); });
                    btn.classList.add('btn-status-' + data.status);
                    if (savedReason) btn.classList.add('btn-done-' + savedReason);
                    btn.dataset.status = data.status;
                    btn.dataset.doneReason = savedReason;

                    var label = btn.querySelector('.js-status-btn-text');
                    if (label) label.textContent = data.status_label || data.status;
                    var icon = btn.querySelector('.js-status-btn-icon');
                    if (icon) {
                        var reasonOption = savedReason ? dropdown.querySelector('.js-status-option[data-done-reason="' + savedReason + '"]') : null;
                        icon.className = 'fa js-status-btn-icon ' + (reasonOption ? reasonOption.dataset.icon + ' me-1' : 'd-none');
                    }

                    dropdown.querySelectorAll('.js-status-option').forEach(function (item) {
                        item.classList.toggle('active', item.dataset.status === data.status && (item.dataset.doneReason || '') === savedReason);
                    });

                    var cell = btn.closest('td');
                    var summary = cell ? cell.querySelector('.js-status-summary-text') : null;
                    if (summary && data.status_summary) summary.textContent = data.status_summary;

                    var row = btn.closest('tr');
                    if (row) {
                        row.setAttribute('data-status', data.status);
                        row.classList.add('table-success');
                        setTimeout(function () { row.classList.remove('table-success'); }, 600);
                    }
                })
                .catch(function () { alert(text.update_failed); })
                .finally(function () { btn.disabled = false; });
        });
    })();
</script>
