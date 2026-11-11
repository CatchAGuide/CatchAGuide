@php
    $openCreateModal = $errors->any() || request()->boolean('create');
@endphp

<div
    class="modal fade"
    id="admin-customer-create-modal"
    tabindex="-1"
    aria-labelledby="admin-customer-create-modal-title"
    aria-hidden="true"
    data-open-on-load="{{ $openCreateModal ? '1' : '0' }}"
>
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content admin-customer-create-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="admin-customer-create-modal-title">{{ __('admin.customers.create_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.customers.cancel') }}"></button>
            </div>

            <form action="{{ route('admin.customers.store') }}" method="post" class="admin-customer-form admin-customer-form--modal" data-customer-create-form>
                @csrf
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @include('admin.pages.customers.partials.create-form-fields')
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin.customers.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('admin.customers.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js_after')
<script>
    (function () {
        var modalEl = document.getElementById('admin-customer-create-modal');
        if (!modalEl) return;

        var form = modalEl.querySelector('[data-customer-create-form]');

        function syncAccountType() {
            if (!form) return;
            var personFields = form.querySelector('[data-account-person]');
            var companyFields = form.querySelector('[data-account-company]');
            var type = form.querySelector('input[name="account_type"]:checked');
            var isCompany = type && type.value === 'company';
            if (personFields) personFields.hidden = !!isCompany;
            if (companyFields) companyFields.hidden = !isCompany;
        }

        if (form) {
            form.querySelectorAll('input[name="account_type"]').forEach(function (radio) {
                radio.addEventListener('change', syncAccountType);
            });
            syncAccountType();
        }

        if (modalEl.getAttribute('data-open-on-load') === '1' && window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    })();
</script>
@endpush
