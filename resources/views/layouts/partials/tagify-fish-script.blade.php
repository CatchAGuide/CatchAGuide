{{-- Tagify target-fish search for guidings header forms. --}}
@once
@php
    $targetsForJs = collect(targets()::getAllTargets())->sortBy('name')->values()
        ->map(fn ($t) => ['value' => (string) $t['id'], 'label' => $t['name']]);
@endphp
<script>
(function () {
    if (typeof Tagify === 'undefined') {
        return;
    }

    var fishWhitelist = @json($targetsForJs);
    var selectedFish = @json(array_map('strval', (array) (request()->target_fish ?? [])));

    function syncHiddenInputs(tagify, form) {
        form.querySelectorAll('.tagify-fish-hidden').forEach(function (el) { el.remove(); });
        tagify.value.forEach(function (tag) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'target_fish[]';
            inp.value = tag.value;
            inp.className = 'tagify-fish-hidden';
            form.appendChild(inp);
        });
    }

    function enableTagStripDrag(tagify) {
        var strip = tagify && tagify.DOM && tagify.DOM.scope;
        if (!strip || strip._fishDragBound) {
            return;
        }
        strip._fishDragBound = true;

        var isDragging = false;
        var startX = 0;
        var startScroll = 0;
        var moved = false;

        strip.addEventListener('pointerdown', function (event) {
            // Keep remove/input interactions normal; drag empty strip / tags area.
            if (event.button !== 0) {
                return;
            }
            if (event.target.closest('.tagify__tag__removeBtn, .tagify__input')) {
                return;
            }
            isDragging = true;
            moved = false;
            startX = event.clientX;
            startScroll = strip.scrollLeft;
            strip.setPointerCapture(event.pointerId);
            strip.classList.add('is-dragging');
        });

        strip.addEventListener('pointermove', function (event) {
            if (!isDragging) {
                return;
            }
            var delta = event.clientX - startX;
            if (Math.abs(delta) > 3) {
                moved = true;
            }
            strip.scrollLeft = startScroll - delta;
        });

        function endDrag(event) {
            if (!isDragging) {
                return;
            }
            isDragging = false;
            strip.classList.remove('is-dragging');
            try {
                strip.releasePointerCapture(event.pointerId);
            } catch (e) {}
        }

        strip.addEventListener('pointerup', endDrag);
        strip.addEventListener('pointercancel', endDrag);
        strip.addEventListener('click', function (event) {
            // Prevent accidental tag remove/open when finishing a drag.
            if (moved) {
                event.preventDefault();
                event.stopPropagation();
                moved = false;
            }
        }, true);
    }

    function initFishTagify(inputEl) {
        if (!inputEl || inputEl._tagifyInited) return;
        inputEl._tagifyInited = true;

        var fishSegment = inputEl.closest('.guidings-page-header__segment--fish');
        var tagify = new Tagify(inputEl, {
            enforceWhitelist: true,
            whitelist: fishWhitelist,
            maxTags: 15,
            tagTextProp: 'label',
            dropdown: {
                maxItems: 50,
                enabled: 0,
                closeOnSelect: false,
                searchKeys: ['label'],
                classname: 'tagify__dropdown--fish',
                // Anchor under the TARGET FISH segment (avoids body-absolute drift)
                position: fishSegment ? 'text' : 'input',
                appendTarget: fishSegment || undefined,
                highlightFirst: true
            },
            templates: {
                dropdownItem: function (item) {
                    var cls = this.settings.classNames.dropdownItem;
                    var text = item.label || item.value;
                    return '<div ' + this.getAttributes(item) + ' class="' + cls + '" tabindex="0" role="option" aria-selected="false">' + text + '</div>';
                }
            }
        });

        if (fishSegment) {
            enableTagStripDrag(tagify);
            tagify.on('dropdown:show', function () {
                var dd = tagify.DOM && tagify.DOM.dropdown;
                if (!dd) {
                    return;
                }
                dd.style.left = '0';
                dd.style.right = 'auto';
                dd.style.top = 'calc(100% + 0.35rem)';
                dd.style.transform = 'none';
                dd.style.width = 'max(100%, 16rem)';
            });
        }

        if (selectedFish && selectedFish.length > 0) {
            var preSelected = fishWhitelist.filter(function (item) {
                return selectedFish.indexOf(item.value) !== -1;
            });
            if (preSelected.length > 0) {
                tagify.addTags(preSelected);
            }
        }

        var form = inputEl.closest('form');
        if (form) {
            tagify.on('add', function () { syncHiddenInputs(tagify, form); });
            tagify.on('remove', function () { syncHiddenInputs(tagify, form); });
            syncHiddenInputs(tagify, form);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.tagify-fish-input').forEach(initFishTagify);
    });

    if (document.readyState !== 'loading') {
        document.querySelectorAll('.tagify-fish-input').forEach(initFishTagify);
    }
})();
</script>
@endonce
