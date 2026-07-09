@php
    $isDraftListing = ($formData['is_update'] ?? false)
        && (($formData['status'] ?? '') === 'draft');
    $activateViaSubmitId = $activateSubmitButtonId ?? null;
@endphp

@if($isDraftListing)
    <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3" role="alert">
        <div>
            <strong>Draft</strong> — this listing is not published yet.
            Use <em>Activate</em> below, or Submit &amp; Publish on the last step.
        </div>
        <button type="button"
                class="btn btn-sm btn-success js-activate-listing"
                data-form-id="{{ $formId }}"
                @if($activateViaSubmitId) data-submit-id="{{ $activateViaSubmitId }}" @endif>
            <i class="fas fa-check"></i> Activate / Publish
        </button>
    </div>
    <script>
        (function () {
            document.querySelectorAll('.js-activate-listing').forEach(function (btn) {
                if (btn.dataset.bound === '1') return;
                btn.dataset.bound = '1';
                btn.addEventListener('click', function () {
                    var draft = document.getElementById('is_draft');
                    var status = document.getElementById('status');
                    if (draft) draft.value = '0';
                    if (status) status.value = 'active';

                    var submitId = btn.getAttribute('data-submit-id');
                    if (submitId) {
                        var submitBtn = document.getElementById(submitId);
                        if (submitBtn) {
                            submitBtn.click();
                            return;
                        }
                    }

                    var form = document.getElementById(btn.getAttribute('data-form-id'));
                    if (form && typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else if (form) {
                        form.submit();
                    }
                });
            });
        })();
    </script>
@endif
