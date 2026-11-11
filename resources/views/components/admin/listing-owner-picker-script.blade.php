<script>
(function () {
    function roleLabel(role) {
        var map = {
            provider: @json(__('admin.customers.role_provider')),
            guide: @json(__('admin.customers.role_guide')),
            customer: @json(__('admin.customers.role_customer'))
        };
        return map[role] || role;
    }

    function bindPicker(root) {
        var searchUrl = root.getAttribute('data-search-url');
        var syncProvider = root.getAttribute('data-sync-provider-name') === '1';
        var providerNameInputId = root.getAttribute('data-provider-name-input') || 'provider_name';
        var idInput = root.querySelector('[data-owner-id]');
        var searchInput = root.querySelector('[data-owner-search]');
        var results = root.querySelector('[data-owner-results]');
        var meta = root.querySelector('[data-owner-meta]');
        var clearBtn = root.querySelector('[data-owner-clear]');
        var timer = null;
        var reqId = 0;

        function setMeta(item) {
            if (!meta) return;
            if (!item) {
                meta.innerHTML = '<span class="text-muted small">' + @json(__('admin.listing_owner.none')) + '</span>';
                return;
            }
            var badgeClass = item.role === 'guide' ? 'success' : (item.role === 'provider' ? 'primary' : 'secondary');
            var html = '<span class="badge bg-' + badgeClass + '-light">' + roleLabel(item.role) + '</span>';
            if (item.city) {
                html += '<span class="text-muted small ms-1">' + item.city + '</span>';
            }
            meta.innerHTML = html;
        }

        function select(item) {
            idInput.value = item ? String(item.id) : '';
            searchInput.value = item ? item.label : '';
            results.hidden = true;
            results.innerHTML = '';
            setMeta(item);
            if (syncProvider && item) {
                var nameInput = document.getElementById(providerNameInputId);
                if (nameInput && !String(nameInput.value || '').trim()) {
                    var label = String(item.label || '');
                    nameInput.value = label.split(' · ')[0] || label;
                }
            }
        }

        function render(items) {
            results.innerHTML = '';
            if (!items.length) {
                results.hidden = true;
                return;
            }
            items.forEach(function (item) {
                var li = document.createElement('button');
                li.type = 'button';
                li.className = 'list-group-item list-group-item-action';
                li.textContent = item.label + (item.role ? ' (' + roleLabel(item.role) + ')' : '');
                li.addEventListener('click', function () { select(item); });
                results.appendChild(li);
            });
            results.hidden = false;
        }

        function search(term) {
            var myReq = ++reqId;
            fetch(searchUrl + '?q=' + encodeURIComponent(term || ''), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (myReq !== reqId) return;
                    render((data && data.results) || []);
                })
                .catch(function () {
                    if (myReq !== reqId) return;
                    results.hidden = true;
                });
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            if (!searchInput.value.trim()) {
                select(null);
                return;
            }
            timer = setTimeout(function () { search(searchInput.value.trim()); }, 250);
        });

        searchInput.addEventListener('focus', function () {
            if (!idInput.value) {
                search(searchInput.value.trim());
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () { select(null); searchInput.focus(); });
        }

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                results.hidden = true;
            }
        });
    }

    document.querySelectorAll('[data-owner-picker]').forEach(bindPicker);
})();
</script>
