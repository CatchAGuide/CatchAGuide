<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-2">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">@lang('homepage.header-signup')</h3>
                </div>

                <form method="POST" action="{{ route('register') }}" class="mb-3">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" 
                                   class="form-control @error('firstname') is-invalid @enderror"
                                   name="firstname" 
                                   value="{{ old('firstname') }}"
                                   placeholder="@lang('forms.fname')"
                                   required 
                                   autocomplete="firstname" 
                                   autofocus>
                            @error('firstname')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" 
                                   class="form-control @error('lastname') is-invalid @enderror"
                                   name="lastname" 
                                   value="{{ old('lastname') }}"
                                   placeholder="@lang('forms.lname')"
                                   required 
                                   autocomplete="lastname">
                            @error('lastname')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror"
                               name="email" 
                               value="{{ old('email') }}"
                               placeholder="@lang('forms.email')"
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
                               autocomplete="new-password">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <input type="password" 
                               class="form-control"
                               name="password_confirmation" 
                               placeholder="@lang('forms.rpass')"
                               required 
                               autocomplete="new-password">
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input @error('agb') is-invalid @enderror" 
                                   id="agb" 
                                   name="agb" 
                                   required>
                            <label class="form-check-label" for="agb">
                                {{ translate('Ich akzeptiere die') }}
                                <a href="{{ route('law.agb') }}" target="_blank">{{ translate('AGB') }}</a>
                                {{ translate('und') }}
                                <a href="{{ route('law.data-protection') }}" target="_blank">{{ translate('Datenschutzbestimmungen') }}</a>
                            </label>
                            @error('agb')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    @production
                        {!! ReCaptcha::htmlScriptTagJsApi() !!}
                        {!! htmlFormSnippet() !!}
                    @endproduction

                    <div class="d-grid mt-3">
                        <button type="submit" class="btn theme-primary">
                            <span class="normal-state">@lang('forms.register')</span>
                            <span class="loading-state d-none">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Loading...
                            </span>
                        </button>
                    </div>
                </form>

                <div class="text-center">
                    <p>{{translate('Already have an account? ')}}
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                            @lang('homepage.header-login')
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Add these z-index rules */
#registerModal {
    z-index: 1060 !important;
}

#registerModal .modal-backdrop {
    z-index: 1059 !important;
}

#registerModal .modal-content {
    border-radius: 8px;
    border: none;
}

#registerModal .form-control {
    height: 48px;
    border: 1px solid #E8604C;
    border-radius: 4px;
}

#registerModal .form-control:focus {
    box-shadow: none;
    border-color: #E8604C;
}

#registerModal .btn-close {
    opacity: 0.5;
}

#registerModal .btn-close:hover {
    opacity: 0.75;
}

#registerModal .theme-primary {
    background-color: #E8604C;
    color: white;
    height: 48px;
    font-weight: 500;
}

#registerModal .theme-primary:hover {
    background-color: #313041;
}

#registerModal .theme-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

#registerModal a {
    color: #E8604C;
}

#registerModal a:hover {
    color: #313041;
}

#registerModal .form-check-input:checked {
    background-color: #E8604C;
    border-color: #E8604C;
}

#registerModal .form-check-input:focus {
    border-color: #E8604C;
    box-shadow: 0 0 0 0.25rem rgba(232, 96, 76, 0.25);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('#registerModal form');
    
    registerForm.addEventListener('submit', function(e) {
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
                window.location.reload();
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Keep loading state while page reloads
                window.location.reload();
            } else {
                // Reset loading state
                submitBtn.disabled = false;
                submitBtn.querySelector('.normal-state').classList.remove('d-none');
                submitBtn.querySelector('.loading-state').classList.add('d-none');
                
                // Display errors in the form
                Object.keys(data.errors).forEach(field => {
                    const input = registerForm.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = data.errors[field][0];
                        input.parentNode.appendChild(feedback);
                    } else {
                        // For errors not associated with a specific field
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-danger mb-3';
                        errorDiv.textContent = data.errors[field][0];
                        registerForm.insertBefore(errorDiv, registerForm.firstChild);
                    }
                });

                // Reset reCAPTCHA if present
                if (typeof grecaptcha !== 'undefined') {
                    grecaptcha.reset();
                }
            }
        })
        .catch(error => {
            // Reset loading state
            submitBtn.disabled = false;
            submitBtn.querySelector('.normal-state').classList.remove('d-none');
            submitBtn.querySelector('.loading-state').classList.add('d-none');
            
            // Add a general error message at the top of the form
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger mb-3';
            errorDiv.textContent = 'An error occurred. Please try again.';
            registerForm.insertBefore(errorDiv, registerForm.firstChild);
        });
    });

    // Clear errors when input changes
    registerForm.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const feedback = this.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
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