<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cag-auth-modal">
            <div class="cag-auth-modal__accent" aria-hidden="true"></div>
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="cag-auth-modal__intro">
                    <p class="cag-auth-modal__eyebrow">{{ __('homepage.header-login') }}</p>
                    <h3 id="loginModalLabel" class="cag-auth-modal__title">{{ __('forms.login_title') }}</h3>
                    <p class="cag-auth-modal__sub">{{ __('forms.login_sub') }}</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="mb-0">
                    @csrf
                    <div class="mb-3">
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="@lang('forms.user')"
                               required
                               autocomplete="email">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               name="password"
                               placeholder="@lang('forms.pass')"
                               required
                               autocomplete="current-password">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="cag-auth-modal__meta">
                        <div class="form-check mb-0">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="remember"
                                   id="remember"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                {{ __('forms.remember_me') }}
                            </label>
                        </div>
                        <a href="{{ route('password.request') }}" class="small">
                            @lang('forms.forgotPass')
                        </a>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn theme-primary">
                            <span class="normal-state">{{ __('forms.login') }}</span>
                            <span class="loading-state d-none">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ __('forms.loading') }}
                            </span>
                        </button>
                    </div>
                </form>

                <div class="cag-auth-modal__footer">
                    <p>
                        {{ __('forms.not_a_member') }}
                        <a href="#" id="signup-header" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">
                            @lang('homepage.header-signup')
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.__SHOW_LOGIN_MODAL__ = @json((bool) session('show_login_modal'));

window.openLoginModal = function openLoginModal() {
    const el = document.getElementById('loginModal');
    // Bootstrap 5.0 (site bundle) has getInstance / new Modal, not getOrCreateInstance.
    if (!el || !window.bootstrap || !bootstrap.Modal) {
        return false;
    }

    const instance = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
    instance.show();
    return true;
};

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('#loginModal form');
    if (!loginForm) {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const shouldOpenLogin = params.get('login') === '1' || window.__SHOW_LOGIN_MODAL__;

    if (shouldOpenLogin) {
        let attempts = 0;
        const openWhenBootstrapReady = function () {
            if (window.openLoginModal()) {
                if (params.has('login')) {
                    params.delete('login');
                    const query = params.toString();
                    const nextUrl = window.location.pathname + (query ? '?' + query : '') + window.location.hash;
                    window.history.replaceState({}, '', nextUrl);
                }
                return;
            }

            attempts += 1;
            if (attempts < 40) {
                // Header modal script can run before vendors/bootstrap is parsed.
                window.setTimeout(openWhenBootstrapReady, 50);
            }
        };

        openWhenBootstrapReady();
    }

    document.querySelectorAll('.logout-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                return;
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ _token: csrfToken })
            })
            .then(function (response) {
                return response.ok ? response.json() : null;
            })
            .then(function (data) {
                if (data?.success) {
                    window.location.href = data.redirect || (window.location.pathname + '?login=1');
                }
            })
            .catch(function () {});
        });
    });

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        clearErrors(this);

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.querySelector('.normal-state').classList.add('d-none');
        submitBtn.querySelector('.loading-state').classList.remove('d-none');

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.status === 419) {
                const url = new URL(window.location.href);
                url.searchParams.set('login', '1');
                window.location.href = url.toString();
                return null;
            }

            // The throttle layer can answer with an HTML error page, so parse
            // defensively instead of letting response.json() reject.
            return response.text().then(body => {
                let data = {};

                try {
                    data = body ? JSON.parse(body) : {};
                } catch (e) {
                    data = {};
                }

                if (response.status === 429 && !data.message) {
                    data.message = @json(__('auth.throttle_generic'));
                    data.retry_after = parseInt(response.headers.get('Retry-After'), 10) || 60;
                }

                return { status: response.status, data: data };
            });
        })
        .then(result => {
            if (!result) {
                return;
            }

            const data = result.data;

            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.querySelector('#loginModal'));
                if (modal) {
                    modal.hide();
                }

                // Always stay on the current page after login.
                window.location.reload();
                return;
            }

            resetSubmitState();

            if (data.message) {
                showAlert(data.message);
            } else if (!data.errors) {
                showAlert(@json(__('auth.failed')));
            }

            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = loginForm.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');

                        // The lockout text is already shown in the alert above.
                        if (data.message === data.errors[field][0]) {
                            return;
                        }

                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.style.display = 'block';
                        feedback.textContent = data.errors[field][0];
                        const wrapper = input.closest('.cag-password-toggle');
                        if (wrapper && wrapper.parentNode) {
                            wrapper.parentNode.insertBefore(feedback, wrapper.nextSibling);
                        } else {
                            input.parentNode.appendChild(feedback);
                        }
                    }
                });
            }

            if (result.status === 429) {
                startLockoutCountdown(parseInt(data.retry_after, 10) || 60);
            }
        })
        .catch(error => {
            resetSubmitState();
            showAlert(@json(__('auth.failed')));
        });

        function resetSubmitState() {
            submitBtn.disabled = false;
            submitBtn.querySelector('.normal-state').classList.remove('d-none');
            submitBtn.querySelector('.loading-state').classList.add('d-none');
        }

        function showAlert(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger mb-3';
            errorDiv.textContent = message;
            loginForm.insertBefore(errorDiv, loginForm.firstChild);
        }

        // Keep the button locked while the server side lockout is still running
        // so the user is not invited to burn further attempts.
        function startLockoutCountdown(seconds) {
            const label = submitBtn.querySelector('.normal-state');
            const originalLabel = label.textContent;
            let remaining = seconds;

            submitBtn.disabled = true;
            label.textContent = originalLabel + ' (' + remaining + ')';

            const timer = setInterval(function () {
                remaining -= 1;

                if (remaining <= 0) {
                    clearInterval(timer);
                    label.textContent = originalLabel;
                    submitBtn.disabled = false;
                    return;
                }

                label.textContent = originalLabel + ' (' + remaining + ')';
            }, 1000);
        }
    });

    // Clear errors when input changes
    loginForm.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const wrapper = this.closest('.cag-password-toggle');
            const group = wrapper ? wrapper.parentNode : this.parentNode;
            if (group) {
                group.querySelectorAll(':scope > .invalid-feedback').forEach(function (el) {
                    el.remove();
                });
            }
        });
    });

    function clearErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
        form.querySelectorAll('.alert').forEach(el => {
            el.remove();
        });
    }
});
</script>
