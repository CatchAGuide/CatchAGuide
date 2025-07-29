@extends('pages.profile.layouts.profile')
@section('title', 'Password & Security')

@section('profile-content')
    <style>
        /* Header Section Styling */
        .security-header {
            background: linear-gradient(135deg, #313041, #252238);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .security-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.5;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translateX(-100px) translateY(-100px); }
            100% { transform: translateX(100px) translateY(100px); }
        }
        
        .security-header h1 {
            color: white !important;
            font-weight: 700;
            margin-bottom: 0;
            z-index: 1;
            position: relative;
        }
        
        .security-header p {
            color: white !important;
            opacity: 0.9;
            z-index: 1;
            position: relative;
        }

        .security-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid #dc3545;
        }
        
        .security-section.primary {
            border-left-color: #313041;
        }
        
        .section-title {
            color: #313041;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: #dc3545;
        }
        
        .primary .section-title i {
            color: #313041;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        
        .required {
            color: #dc3545;
        }
        
        .form-control {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #313041;
            box-shadow: 0 0 0 0.2rem rgba(49, 48, 65, 0.25);
        }
        
        .password-strength {
            margin-top: 8px;
        }
        
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background-color: #e9ecef;
            overflow: hidden;
            margin-bottom: 5px;
        }
        
        .strength-fill {
            height: 100%;
            transition: width 0.3s ease, background-color 0.3s ease;
            width: 0%;
            background-color: #dc3545;
        }
        
        .strength-text {
            font-size: 0.8rem;
            color: #666;
        }
        
        .btn-primary {
            background-color: #dc3545;
            border-color: #dc3545;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #c82333;
            border-color: #c82333;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }
        
        .alert {
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .security-tips {
            background-color: #e8f4fd;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .tips-list {
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        .tips-list li {
            margin-bottom: 5px;
        }
        
        .helper-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .security-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            background: white;
            margin-bottom: 15px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .security-card h5 {
            margin-bottom: 15px;
            color: #313041;
            font-weight: 600;
        }
        
        .security-card h5 i {
            margin-right: 8px;
            color: #6c757d;
        }
        
        .security-card p {
            flex-grow: 1;
            margin-bottom: 15px;
        }
        
        .security-card .btn {
            align-self: flex-start;
            margin-top: auto;
        }
        
        .coming-soon {
            position: relative;
            opacity: 0.7;
        }
        
        .coming-soon::before {
            content: "Coming Soon";
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(108, 117, 125, 0.9);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            z-index: 1;
        }
        
        .email-display {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            margin: 10px 0;
            font-family: monospace;
            word-break: break-all;
        }
        

    </style>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('message'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Header Section -->
    <div class="security-header">
        <h1 class="mb-0 text-white">
            <i class="fas fa-shield-alt"></i>
            Password & Security
        </h1>
        <p class="mb-0 mt-2 text-white">Secure your account with strong authentication settings</p>
    </div>

    <!-- Password Change Section -->
    <div class="security-section">
        <h3 class="section-title">
            <i class="fas fa-key"></i>
            Change Password
        </h3>
        
        <div class="alert security-tips">
            <strong><i class="fas fa-shield-alt"></i> Password Security Tips:</strong>
            <ul class="tips-list">
                <li>Use at least 8 characters with a mix of letters, numbers, and symbols</li>
                <li>Avoid using personal information like birthdays or names</li>
                <li>Don't reuse passwords from other accounts</li>
                <li>Consider using a password manager</li>
            </ul>
        </div>

        <form action="{{route('profile.password.update')}}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label" for="current_password">@lang('profile.currpassword')<span class="required">*</span></label>
                        <input id="current_password" type="password" class="form-control" name="current_password" required>
                        <small class="helper-text">Enter your current password to verify your identity</small>
                        @if ($errors->has('current_password'))
                            <div class="text-danger mt-1">
                                <small>{{ $errors->first('current_password') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="new_password">@lang('profile.newpass')<span class="required">*</span></label>
                        <input id="new_password" type="password" class="form-control" name="new_password" required>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strength-fill"></div>
                            </div>
                            <div class="strength-text" id="strength-text">Password strength</div>
                        </div>
                        @if ($errors->has('new_password'))
                            <div class="text-danger mt-1">
                                <small>{{ $errors->first('new_password') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="new_password_confirmation">@lang('profile.confpass')<span class="required">*</span></label>
                        <input id="new_password_confirmation" type="password" class="form-control" name="new_password_confirmation" required>
                        <small class="helper-text">Repeat your new password to confirm</small>
                        <div class="mt-1" id="password-match-status"></div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- Account Security Section -->
    <div class="security-section primary">
        <h3 class="section-title">
            <i class="fas fa-shield-alt"></i>
            Account Security
        </h3>
        
        <div class="row">
            <div class="col-md-6">
                <div class="security-card coming-soon">
                    <h5><i class="fas fa-mobile-alt"></i> Two-Factor Authentication</h5>
                    <p class="text-muted">Add an extra layer of security to your account with 2FA. Protect your account even if your password is compromised.</p>
                    <button class="btn btn-secondary" disabled>Enable 2FA</button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="security-card">
                    <h5><i class="fas fa-envelope"></i> Email Security</h5>
                    <p class="text-muted">Your account email address:</p>
                    <div class="email-display">
                        <strong>{{ Auth::user()->email }}</strong>
                    </div>
                    <small class="text-muted">Contact support to change your email address.</small>
                </div>
            </div>
        </div>
    </div>





@endsection

@section('js_after')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('new_password_confirmation');
            const strengthFill = document.getElementById('strength-fill');
            const strengthText = document.getElementById('strength-text');
            const matchStatus = document.getElementById('password-match-status');

            // Password strength checker
            newPasswordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = calculatePasswordStrength(password);
                
                strengthFill.style.width = strength.percentage + '%';
                strengthFill.style.backgroundColor = strength.color;
                strengthText.textContent = strength.text;
                strengthText.style.color = strength.color;
            });

            // Password match checker
            function checkPasswordMatch() {
                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword.length === 0) {
                    matchStatus.innerHTML = '';
                    return;
                }
                
                if (newPassword === confirmPassword) {
                    matchStatus.innerHTML = '<small class="text-success"><i class="fas fa-check"></i> Passwords match</small>';
                } else {
                    matchStatus.innerHTML = '<small class="text-danger"><i class="fas fa-times"></i> Passwords do not match</small>';
                }
            }

            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
            newPasswordInput.addEventListener('input', checkPasswordMatch);

            function calculatePasswordStrength(password) {
                let score = 0;
                
                if (password.length >= 8) score += 20;
                if (password.length >= 12) score += 10;
                if (/[a-z]/.test(password)) score += 20;
                if (/[A-Z]/.test(password)) score += 20;
                if (/[0-9]/.test(password)) score += 20;
                if (/[^A-Za-z0-9]/.test(password)) score += 10;
                
                if (score < 30) {
                    return { percentage: score, color: '#dc3545', text: 'Weak' };
                } else if (score < 60) {
                    return { percentage: score, color: '#fd7e14', text: 'Fair' };
                } else if (score < 80) {
                    return { percentage: score, color: '#ffc107', text: 'Good' };
                } else {
                    return { percentage: score, color: '#198754', text: 'Strong' };
                }
            }
        });
        

        
        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Insert alert at the top of the page
            const header = document.querySelector('.security-header');
            header.insertAdjacentHTML('afterend', alertHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
        
        // Add loading states to integration cards
        function setIntegrationLoading(integrationType, isLoading) {
            const card = document.querySelector(`[data-integration="${integrationType}"]`);
            if (card) {
                const actions = card.querySelector('.integration-actions');
                if (isLoading) {
                    actions.innerHTML = `
                        <button class="btn btn-secondary w-100" disabled>
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Processing...
                        </button>
                    `;
                }
            }
        }


    </script>
@endsection 