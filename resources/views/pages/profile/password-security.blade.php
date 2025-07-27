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
        
        /* Integration Cards Styling */
        .integration-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .integration-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .integration-card.coming-soon {
            opacity: 0.7;
            background: #f8f9fa;
        }
        
        .coming-soon-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            z-index: 1;
        }
        
        .integration-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            gap: 12px;
        }
        
        .integration-logo {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #f8f9fa;
        }
        
        .integration-info {
            flex: 1;
            min-width: 0;
        }
        
        .integration-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #313041;
            margin: 0 0 4px 0;
        }
        
        .integration-description {
            font-size: 0.85rem;
            color: #6c757d;
            margin: 0;
        }
        
        .integration-status {
            flex-shrink: 0;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-badge.connected {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.disconnected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge.disabled {
            background: #e2e3e5;
            color: #6c757d;
        }
        
        .integration-content {
            margin-bottom: 16px;
        }
        
        .integration-benefits {
            font-size: 0.85rem;
            color: #495057;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
        }
        
        .integration-benefits:last-child {
            margin-bottom: 0;
        }
        
        .integration-actions {
            margin-top: auto;
        }
        
        .integration-actions .btn {
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .integration-actions .btn-group {
            gap: 4px;
        }
        
        .integration-actions .btn-group .btn {
            flex: 1;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .integration-header {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
            
            .integration-status {
                align-self: center;
            }
            
            .integration-actions .btn-group {
                flex-direction: column;
            }
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

    <!-- Third-Party Integrations Section -->
    <div class="security-section">
        <h3 class="section-title">
            <i class="fas fa-plug"></i>
            @lang('profile.third_party_integrations')
        </h3>
        <p class="text-muted mb-4">@lang('profile.third_party_integrations_description')</p>
        
        <div class="row">
            <!-- Calendly Integration -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="integration-card" data-integration="calendly">
                    <div class="integration-header">
                        <div class="integration-logo">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="32" height="32" rx="6" fill="#006BFF"/>
                                <path d="M8 12H24V20H8V12ZM10 14V18H22V14H10Z" fill="white"/>
                                <path d="M12 16H20V17H12V16Z" fill="white"/>
                            </svg>
                        </div>
                        <div class="integration-info">
                            <h5 class="integration-title">@lang('profile.calendly_title')</h5>
                            <p class="integration-description">@lang('profile.calendly_description')</p>
                        </div>
                        <div class="integration-status">
                            @php
                                try {
                                    $calendlyService = app(\App\Services\CalendlyService::class);
                                    $hasCalendlyConnection = $calendlyService->hasActiveConnection(auth()->id());
                                } catch (\Exception $e) {
                                    $hasCalendlyConnection = false;
                                }
                            @endphp
                            
                            @if($hasCalendlyConnection)
                                <span class="status-badge connected">
                                    <i class="fas fa-check-circle"></i>
                                    @lang('profile.status_connected')
                                </span>
                            @else
                                <span class="status-badge disconnected">
                                    <i class="fas fa-circle"></i>
                                    @lang('profile.status_disconnected')
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="integration-content">
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            @lang('profile.calendly_benefit_1')
                        </p>
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            @lang('profile.calendly_benefit_2')
                        </p>
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            @lang('profile.calendly_benefit_3')
                        </p>
                    </div>
                    
                    <div class="integration-actions">
                        @if($hasCalendlyConnection)
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="syncCalendly()">
                                    <i class="fas fa-sync"></i>
                                    @lang('profile.sync_now')
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="disconnectCalendly()">
                                    <i class="fas fa-unlink"></i>
                                    @lang('profile.disconnect')
                                </button>
                            </div>
                        @else
                            <a href="{{ route('oauth.calendly') }}" class="btn btn-primary w-100">
                                <i class="fab fa-calendly me-2"></i>
                                @lang('profile.connect_calendly')
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Google Calendar Integration (Coming Soon) -->
            {{-- <div class="col-lg-6 col-md-12 mb-4">
                <div class="integration-card coming-soon">
                    <div class="coming-soon-badge">Coming Soon</div>
                    <div class="integration-header">
                        <div class="integration-logo">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="32" height="32" rx="6" fill="#4285F4"/>
                                <path d="M8 8H24V24H8V8ZM10 10V22H22V10H10Z" fill="white"/>
                                <path d="M12 12H20V14H12V12Z" fill="white"/>
                                <path d="M12 16H20V18H12V16Z" fill="white"/>
                                <path d="M12 20H16V22H12V20Z" fill="white"/>
                            </svg>
                        </div>
                        <div class="integration-info">
                            <h5 class="integration-title">Google Calendar</h5>
                            <p class="integration-description">Direct calendar integration</p>
                        </div>
                        <div class="integration-status">
                            <span class="status-badge disabled">
                                <i class="fas fa-clock"></i>
                                Soon
                            </span>
                        </div>
                    </div>
                    
                    <div class="integration-content">
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            Direct calendar sync
                        </p>
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            Two-way event management
                        </p>
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            Availability management
                        </p>
                    </div>
                    
                    <div class="integration-actions">
                        <button class="btn btn-secondary w-100" disabled>
                            <i class="fab fa-google me-2"></i>
                            Coming Soon
                        </button>
                    </div>
                </div>
            </div>
            
                        {{-- Future Integrations (Commented out for future use)
            
            <!-- Outlook Calendar Integration (Coming Soon) -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="integration-card coming-soon">
                    <div class="coming-soon-badge">Coming Soon</div>
                    <div class="integration-header">
                        <div class="integration-logo">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="32" height="32" rx="6" fill="#0078D4"/>
                                <path d="M8 8H24V24H8V8ZM10 10V22H22V10H10Z" fill="white"/>
                                <path d="M12 12H20V14H12V12Z" fill="white"/>
                                <path d="M12 16H20V18H12V16Z" fill="white"/>
                                <path d="M12 20H16V22H12V20Z" fill="white"/>
                            </svg>
                        </div>
                        <div class="integration-info">
                            <h5 class="integration-title">Outlook Calendar</h5>
                            <p class="integration-description">Microsoft calendar sync</p>
                        </div>
                        <div class="integration-status">
                            <span class="status-badge disabled">
                                <i class="fas fa-clock"></i>
                                Soon
                            </span>
                        </div>
                    </div>
                    
                    <div class="integration-content">
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            Outlook calendar sync
                        </p>
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            Exchange integration
                        </p>
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            Office 365 support
                        </p>
                    </div>
                    
                    <div class="integration-actions">
                        <button class="btn btn-secondary w-100" disabled>
                            <i class="fab fa-microsoft me-2"></i>
                            Coming Soon
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Apple Calendar Integration (Coming Soon) -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="integration-card coming-soon">
                    <div class="coming-soon-badge">Coming Soon</div>
                    <div class="integration-header">
                        <div class="integration-logo">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="32" height="32" rx="6" fill="#000000"/>
                                <path d="M8 8H24V24H8V8ZM10 10V22H22V10H10Z" fill="white"/>
                                <path d="M12 12H20V14H12V12Z" fill="white"/>
                                <path d="M12 16H20V18H12V16Z" fill="white"/>
                                <path d="M12 20H16V22H12V20Z" fill="white"/>
                            </svg>
                        </div>
                        <div class="integration-info">
                            <h5 class="integration-title">Apple Calendar</h5>
                            <p class="integration-description">iCloud calendar sync</p>
                        </div>
                        <div class="integration-status">
                            <span class="status-badge disabled">
                                <i class="fas fa-clock"></i>
                                Soon
                            </span>
                        </div>
                    </div>
                    
                    <div class="integration-content">
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            iCloud calendar sync
                        </p>
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            macOS & iOS integration
                        </p>
                        <p class="integration-benefits">
                            <i class="fas fa-check text-success me-2"></i>
                            Family sharing support
                        </p>
                    </div>
                    
                    <div class="integration-actions">
                        <button class="btn btn-secondary w-100" disabled>
                            <i class="fab fa-apple me-2"></i>
                            Coming Soon
                        </button>
                    </div>
                </div>
            </div>
            
            --}}--}}
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
        
        // Calendly Integration Functions
        function syncCalendly() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
            button.disabled = true;
            
            fetch('{{ route("oauth.calendly.sync") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message + ' (' + data.event_count + ' events synced)');
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to sync Calendly events. Please try again.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        function disconnectCalendly() {
            if (!confirm('Are you sure you want to disconnect your Calendly account? This will remove the integration.')) {
                return;
            }
            
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Disconnecting...';
            button.disabled = true;
            
            fetch('{{ route("oauth.calendly.disconnect") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to disconnect Calendly account. Please try again.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
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