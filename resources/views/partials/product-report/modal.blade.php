@php
    $defaultSourceType = $reportSourceType ?? '';
    $defaultSourceId = $reportSourceId ?? '';
    $defaultReportedUrl = $reportedUrl ?? url()->current();
@endphp
<div class="modal fade" id="productReportModal" tabindex="-1" aria-labelledby="productReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productReportModalLabel">{{ __('notice-takedown.modal_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="productReportFormContainer">
                    <form id="productReportModalForm">
                        @csrf
                        <input type="hidden" name="source_type" id="productReportSourceType" value="{{ $defaultSourceType }}">
                        <input type="hidden" name="source_id" id="productReportSourceId" value="{{ $defaultSourceId }}">
                        <input type="hidden" name="reported_url" id="productReportReportedUrl" value="{{ $defaultReportedUrl }}">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="name" placeholder="{{ __('notice-takedown.your_name') }}" required maxlength="255">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="email" class="form-control" name="email" placeholder="{{ __('notice-takedown.email') }}" required maxlength="255">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <input type="text" class="form-control" name="phone" placeholder="{{ __('notice-takedown.phone') }}" required maxlength="40">
                        </div>

                        <div class="form-group mb-3">
                            <select class="form-control" name="reason" required>
                                <option value="" disabled selected>{{ __('notice-takedown.reason_placeholder') }}</option>
                                @foreach(\App\Models\ProductReport::reasonOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <textarea name="description" class="form-control" rows="4" placeholder="{{ __('notice-takedown.description') }}" required minlength="20" maxlength="5000"></textarea>
                            <small class="text-muted">{{ __('notice-takedown.description_hint') }}</small>
                        </div>

                        <div class="d-flex flex-column flex-sm-row flex-wrap justify-content-between align-items-center gap-3">
                            <x-recaptcha />
                            <button type="submit" class="btn btn-orange">{{ __('notice-takedown.submit') }}</button>
                        </div>
                    </form>
                </div>
                <div id="productReportSuccessMessage" class="alert alert-success d-none mb-0"></div>
                <div id="productReportErrorMessage" class="alert alert-danger d-none mb-0"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function initProductReportModal() {
        var modalEl = document.getElementById('productReportModal');
        var form = document.getElementById('productReportModalForm');
        if (!modalEl || !form || form.dataset.bound === '1') {
            return;
        }
        form.dataset.bound = '1';

        var captcha = (typeof RecaptchaWidget !== 'undefined')
            ? new RecaptchaWidget(form)
            : null;

        document.querySelectorAll('[data-bs-target="#productReportModal"]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                var sourceType = trigger.getAttribute('data-source-type') || '';
                var sourceId = trigger.getAttribute('data-source-id') || '';
                var reportedUrl = trigger.getAttribute('data-reported-url') || window.location.href;
                document.getElementById('productReportSourceType').value = sourceType;
                document.getElementById('productReportSourceId').value = sourceId;
                document.getElementById('productReportReportedUrl').value = reportedUrl;
            });
        });

        modalEl.addEventListener('shown.bs.modal', function () {
            if (captcha && typeof captcha.render === 'function') {
                captcha.render();
            }
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var successEl = document.getElementById('productReportSuccessMessage');
            var errorEl = document.getElementById('productReportErrorMessage');
            var container = document.getElementById('productReportFormContainer');
            successEl.classList.add('d-none');
            errorEl.classList.add('d-none');

            var formData = new FormData(form);
            var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch('{{ route('product-reports.store') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token || ''
                },
                body: formData
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, status: response.status, data: data };
                }).catch(function () {
                    return { ok: response.ok, status: response.status, data: null };
                });
            })
            .then(function (result) {
                if (result.ok && result.data && result.data.success) {
                    container.classList.add('d-none');
                    successEl.textContent = result.data.message || '{{ __('notice-takedown.success') }}';
                    successEl.classList.remove('d-none');
                    form.reset();
                    document.getElementById('productReportSourceType').value = '{{ $defaultSourceType }}';
                    document.getElementById('productReportSourceId').value = '{{ $defaultSourceId }}';
                    document.getElementById('productReportReportedUrl').value = '{{ $defaultReportedUrl }}';
                    if (captcha && typeof captcha.reset === 'function') {
                        captcha.reset();
                    }
                    setTimeout(function () {
                        var instance = bootstrap.Modal.getInstance(modalEl);
                        if (instance) {
                            instance.hide();
                        }
                        container.classList.remove('d-none');
                        successEl.classList.add('d-none');
                    }, 2500);
                    return;
                }

                var message = '{{ __('notice-takedown.error') }}';
                if (result.data && result.data.message) {
                    message = result.data.message;
                } else if (result.data && result.data.errors) {
                    message = Object.values(result.data.errors).flat().join(' ');
                }
                errorEl.textContent = message;
                errorEl.classList.remove('d-none');
            })
            .catch(function () {
                errorEl.textContent = '{{ __('notice-takedown.error') }}';
                errorEl.classList.remove('d-none');
            });
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            document.getElementById('productReportFormContainer').classList.remove('d-none');
            document.getElementById('productReportSuccessMessage').classList.add('d-none');
            document.getElementById('productReportErrorMessage').classList.add('d-none');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductReportModal);
    } else {
        initProductReportModal();
    }
})();
</script>
