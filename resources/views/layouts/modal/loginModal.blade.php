<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-2">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Login</h3>
                </div>

                <form method="POST" action="{{ route('login') }}" class="mb-3">
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

                    <div class="mb-3 form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="remember" 
                               id="remember" 
                               {{ old('remember') ? 'checked' : '' }}> &nbsp;
                        <label class="form-check-label" for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn theme-primary">
                            <span class="normal-state">{{ __('Login') }}</span>
                            <span class="loading-state d-none">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Loading...
                            </span>
                        </button>
                    </div>
                </form>

                <div class="text-center mb-3">
                    <a href="{{ route('password.request') }}" class="text-decoration-none">
                        @lang('forms.forgotPass')
                    </a>
                </div>

                <div class="text-center">
                    <p>{{ translate('Not a member?')}}
                        <a href="#" id="signup-header" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">
                            @lang('homepage.header-signup')
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#loginModal {
    z-index: 1060 !important;
}

#loginModal .modal-backdrop {
    z-index: 1059 !important;
}

#loginModal .modal-content {
    border-radius: 8px;
    border: none;
}

#loginModal .form-control {
    height: 48px;
    border: 1px solid #E8604C;
    border-radius: 4px;
}

#loginModal .form-control:focus {
    box-shadow: none;
    border-color: #E8604C;
}

#loginModal .btn-close {
    opacity: 0.5;
}

#loginModal .btn-close:hover {
    opacity: 0.75;
}

#loginModal .theme-primary {
    background-color: #E8604C;
    color: white;
    height: 48px;
    font-weight: 500;
}

#loginModal .theme-primary:hover {
    background-color: #313041;
}

#loginModal .theme-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

#loginModal a {
    color: #E8604C;
}

#loginModal a:hover {
    color: #313041;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('#loginModal form');
    
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
                window.location.reload();
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.querySelector('#loginModal'));
                modal.hide();
                
                // Refresh the current page
                window.location.reload();
            } else {
                // Reset loading state
                submitBtn.disabled = false;
                submitBtn.querySelector('.normal-state').classList.remove('d-none');
                submitBtn.querySelector('.loading-state').classList.add('d-none');
                
                // Display errors in the form
                Object.keys(data.errors).forEach(field => {
                    const input = loginForm.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = data.errors[field][0];
                        input.parentNode.appendChild(feedback);
                    }
                });
            }
        })
        .catch(error => {
            // Reset loading state
            submitBtn.disabled = false;
            submitBtn.querySelector('.normal-state').classList.remove('d-none');
            submitBtn.querySelector('.loading-state').classList.add('d-none');
            
            // Add error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger mb-3';
            errorDiv.textContent = 'An error occurred. Please try again.';
            loginForm.insertBefore(errorDiv, loginForm.firstChild);
        });
    });

    // Clear errors when input changes
    loginForm.querySelectorAll('input').forEach(input => {
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