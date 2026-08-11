@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    function parseData(root) {
        var node = root.querySelector('[data-offers-species-data]');
        if (!node) {
            return { options: [], selected: [], placeholder: '', removeLabel: 'Remove' };
        }
        try {
            return JSON.parse(node.textContent || '{}');
        } catch (e) {
            return { options: [], selected: [], placeholder: '', removeLabel: 'Remove' };
        }
    }

    function selectedSet(root) {
        var values = [];
        root.querySelectorAll('[data-offers-species-checkbox]:checked').forEach(function (checkbox) {
            values.push(String(checkbox.value));
        });
        return values;
    }

    function syncInputs(root, inputName) {
        var box = root.querySelector('[data-offers-species-inputs]');
        if (!box) {
            return;
        }
        box.innerHTML = '';
        selectedSet(root).forEach(function (value) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = inputName;
            input.value = value;
            box.appendChild(input);
        });
    }

    function renderTags(root, data) {
        var tags = root.querySelector('[data-offers-species-tags]');
        if (!tags) {
            return;
        }

        var selected = selectedSet(root);
        tags.innerHTML = '';

        if (selected.length === 0) {
            var placeholder = document.createElement('span');
            placeholder.className = 'offers-species-select__placeholder';
            placeholder.textContent = data.placeholder || '';
            tags.appendChild(placeholder);
            return;
        }

        var optionsByValue = {};
        (data.options || []).forEach(function (option) {
            optionsByValue[String(option.value)] = option.label || option.value;
        });

        selected.forEach(function (value) {
            var tag = document.createElement('span');
            tag.className = 'offers-species-select__tag';
            tag.setAttribute('data-value', value);

            var text = document.createElement('span');
            text.className = 'offers-species-select__tag-text';
            text.textContent = optionsByValue[value] || value;

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'offers-species-select__tag-remove';
            remove.setAttribute('data-offers-species-remove', value);
            remove.setAttribute('aria-label', data.removeLabel || 'Remove');
            remove.innerHTML = '&times;';

            tag.appendChild(text);
            tag.appendChild(remove);
            tags.appendChild(tag);
        });
    }

    function setOptionState(option, checked) {
        option.classList.toggle('is-checked', checked);
        var checkbox = option.querySelector('[data-offers-species-checkbox]');
        if (checkbox) {
            checkbox.checked = checked;
        }
    }

    function setOpen(root, open) {
        var dropdown = root.querySelector('[data-offers-species-dropdown]');
        var toggle = root.querySelector('[data-offers-species-toggle]');
        if (!dropdown || !toggle) {
            return;
        }
        dropdown.hidden = !open;
        root.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            var search = root.querySelector('[data-offers-species-search]');
            if (search) {
                window.setTimeout(function () { search.focus(); }, 0);
            }
        }
    }

    function filterOptions(root, query) {
        var needle = String(query || '').trim().toLowerCase();
        root.querySelectorAll('[data-offers-species-option]').forEach(function (option) {
            var label = String(option.getAttribute('data-label') || '').toLowerCase();
            option.hidden = needle !== '' && label.indexOf(needle) === -1;
        });
    }

    function initSpeciesSelect(root) {
        if (!root || root._offersSpeciesInited) {
            return;
        }
        root._offersSpeciesInited = true;

        var data = parseData(root);
        var inputName = root.getAttribute('data-input-name') || 'species[]';
        var toggle = root.querySelector('[data-offers-species-toggle]');
        var search = root.querySelector('[data-offers-species-search]');

        function refresh() {
            renderTags(root, data);
            syncInputs(root, inputName);
        }

        if (toggle) {
            toggle.addEventListener('click', function (event) {
                if (event.target.closest('[data-offers-species-remove]')) {
                    return;
                }
                setOpen(root, root.classList.contains('is-open') ? false : true);
            });
        }

        root.addEventListener('click', function (event) {
            var removeBtn = event.target.closest('[data-offers-species-remove]');
            if (removeBtn) {
                event.preventDefault();
                event.stopPropagation();
                var value = removeBtn.getAttribute('data-offers-species-remove');
                var option = root.querySelector('[data-offers-species-option][data-value="' + value + '"]');
                if (option) {
                    setOptionState(option, false);
                    refresh();
                }
                return;
            }

            var option = event.target.closest('[data-offers-species-option]');
            if (option && event.target.matches('[data-offers-species-checkbox]')) {
                setOptionState(option, event.target.checked);
                refresh();
            }
        });

        if (search) {
            search.addEventListener('input', function () {
                filterOptions(root, search.value);
            });
            search.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        }

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                setOpen(root, false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setOpen(root, false);
            }
        });

        refresh();
    }

    document.querySelectorAll('[data-offers-species-select]').forEach(initSpeciesSelect);
});
</script>
@endonce
