@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="login-container">
    <div class="container">
        <div class="row justify-content-center align-items-center ">
            <div class="col-lg-10">
                <div class="card login-card shadow-lg border-0 overflow-hidden">
                    <div class="row g-0">
                        <!-- Left Side - Branding & Graphics -->
                        <div class="col-lg-6 d-none d-lg-block">
                            <div class="login-branding">
                                <div class="branding-overlay"></div>
                                <div class="branding-content">
                                    <!-- Logo & Title -->
                                    <div class="text-center mb-4">
                                        <div class="login-logo mb-3">
                                            <div class="logo-circle">
                                                <i class="fas fa-network-wired"></i>
                                            </div>
                                        </div>
                                        <h2 class="text-white fw-bold mb-2">NetMonitor</h2>
                                        <p class="text-white-50">Network Infrastructure Monitoring & Asset Management System</p>
                                    </div>

                                    <!-- Features List -->
                                    <div class="features-list">
                                        <div class="feature-item">
                                            <i class="fas fa-server"></i>
                                            <span>Real-time Device Monitoring</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-plug"></i>
                                            <span>Port Management & Tracking</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-chart-line"></i>
                                            <span>Advanced Analytics & Reports</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-bell"></i>
                                            <span>Instant Alert Notifications</span>
                                        </div>
                                    </div>

                                    <!-- Network Animation -->
                                    <div class="network-animation">
                                        <div class="node node-1"></div>
                                        <div class="node node-2"></div>
                                        <div class="node node-3"></div>
                                        <div class="node node-4"></div>
                                        <div class="node node-5"></div>
                                        <div class="line line-1"></div>
                                        <div class="line line-2"></div>
                                        <div class="line line-3"></div>
                                        <div class="line line-4"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side - Login Form -->
                        <div class="col-lg-6">
                            <div class="login-form-wrapper">
                                <!--<div class="login-header text-center mb-4">-->
                                <!--    <div class="d-lg-none mb-3">-->
                                <!--        <div class="logo-circle-small">-->
                                <!--            <i class="fas fa-network-wired"></i>-->
                                <!--        </div>-->
                                <!--    </div>-->
                                <!--    <h3 class="fw-bold text-gray-900 mb-2">Welcome Back!</h3>-->
                                <!--    <p class="text-muted">Sign in to your account to continue</p>-->
                                <!--</div>-->
                                
                                <div class="login-header text-center mb-4">
    <!-- Updated logo section -->
    <div class="mb-3">
        <div class="logo-container">
            <!-- Replace 'your-logo-url.png' with your actual image path -->
            <img src="{{ asset('logo.png')}}" alt="NMS Logo" class="img-fluid" style="max-height: 60px;">
        </div>
    </div>
    
    <h3 class="fw-bold text-gray-900 mb-2">Welcome Back!</h3>
    <p class="text-muted">Sign in to your account to continue</p>
</div>


                                <!-- Session Status -->
                                @if (session('status'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        {{ session('status') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <!-- Login Form -->
                                <form method="POST" action="{{ route('login') }}" class="login-form">
                                    @csrf

                                    <!-- Email Field -->
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label fw-semibold">
                                            <i class="fas fa-envelope me-2 text-primary"></i>Email Address
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-user text-muted"></i>
                                            </span>
                                            <input id="email" type="email" 
                                                   class="form-control border-start-0 @error('email') is-invalid @enderror" 
                                                   name="email" 
                                                   value="{{ old('email') }}" 
                                                   placeholder="Enter your email"
                                                   required 
                                                   autocomplete="email" 
                                                   autofocus>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Password Field -->
                                    <div class="form-group mb-3">
                                        <label for="password" class="form-label fw-semibold">
                                            <i class="fas fa-lock me-2 text-primary"></i>Password
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-key text-muted"></i>
                                            </span>
                                            <input id="password" type="password" 
                                                   class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror" 
                                                   name="password" 
                                                   placeholder="Enter your password"
                                                   required 
                                                   autocomplete="current-password">
                                            <span class="input-group-text bg-light border-start-0" 
                                                  onclick="togglePassword()" 
                                                  style="cursor: pointer;">
                                                <i class="fas fa-eye text-muted" id="togglePasswordIcon"></i>
                                            </span>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Remember Me & Forgot Password -->
                                        <!-- Remember Me & Forgot Password -->
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                                            <label class="form-check-label text-muted small" for="remember">
                                                Remember me
                                            </label>
                                        </div>
                                        @if (Route::has('password.request'))
                                            <a class="text-primary text-decoration-none small fw-semibold" 
                                               href="{{ route('password.request') }}">
                                                <i class="fas fa-question-circle me-1"></i>Forgot Password?
                                            </a>
                                        @endif
                                    </div>

                                    <!-- Login Button -->
                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-primary btn-lg login-btn">
                                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <footer class="text-center mt-auto py-3 mb-0">
                    <p class="text-muted small mb-0">
                        &copy; {{ date('Y') }} Network Infrastructure Monitor. All rights reserved.
                    </p>
                    <div class="mt-2">
                        <a href="https://github.com/amit2x/network-infra-monitor" 
                           target="_blank" 
                           class="text-decoration-none text-muted small">
                            <i class="fab fa-github"></i> View on GitHub
                        </a>
                    </div>
                </footer>

            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Login Container */
    .login-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
    }

    /* Login Card */
    .login-card {
        border-radius: 20px;
        background: #fff;
    }

    /* Branding Side */
    .login-branding {
        position: relative;
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        height: 100%;
        min-height: 600px;
        overflow: hidden;
    }

    .branding-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.4;
    }

    .branding-content {
        position: relative;
        z-index: 1;
        padding: 60px 40px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    /* Logo */
    .logo-circle {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        animation: pulse 2s infinite;
    }

    .logo-circle-small {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #4e73df, #224abe);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin: 0 auto;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
        }
        70% {
            box-shadow: 0 0 0 15px rgba(255, 255, 255, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
        }
    }

    /* Features List */
    .features-list {
        margin-top: 40px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        margin-bottom: 8px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        color: white;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    .feature-item:hover {
        background: rgba(255, 255, 255, 0.2);
        border-left-color: #ffd700;
        transform: translateX(5px);
    }

    .feature-item i {
        width: 30px;
        font-size: 1rem;
        color: #ffd700;
        margin-right: 10px;
    }

    /* Network Animation */
    .network-animation {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        width: 300px;
        height: 200px;
    }

    .node {
        position: absolute;
        width: 12px;
        height: 12px;
        background: #ffd700;
        border-radius: 50%;
        animation: blink 2s infinite;
    }

    .node-1 { top: 20px; left: 50px; animation-delay: 0s; }
    .node-2 { top: 80px; left: 150px; animation-delay: 0.5s; }
    .node-3 { top: 40px; left: 250px; animation-delay: 1s; }
    .node-4 { top: 150px; left: 100px; animation-delay: 1.5s; }
    .node-5 { top: 120px; left: 220px; animation-delay: 2s; }

    .line {
        position: absolute;
        background: rgba(255, 215, 0, 0.3);
        height: 2px;
        transform-origin: left center;
        animation: linePulse 2s infinite;
    }

    .line-1 { top: 25px; left: 60px; width: 100px; transform: rotate(30deg); animation-delay: 0s; }
    .line-2 { top: 85px; left: 160px; width: 100px; transform: rotate(-20deg); animation-delay: 0.5s; }
    .line-3 { top: 45px; left: 60px; width: 200px; transform: rotate(10deg); animation-delay: 1s; }
    .line-4 { top: 130px; left: 110px; width: 120px; transform: rotate(45deg); animation-delay: 1.5s; }

    @keyframes blink {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.3; transform: scale(1.5); }
    }

    @keyframes linePulse {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.8; }
    }

    /* Login Form */
    .login-form-wrapper {
        padding: 50px 40px;
    }

    .login-form .form-control {
        padding: 12px 15px;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .login-form .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
    }

    .login-form .input-group-text {
        border-radius: 8px 0 0 8px;
        padding: 12px 15px;
    }

    .login-form .input-group .form-control {
        border-radius: 0;
    }

    .login-form .input-group .form-control:last-child {
        border-radius: 0 8px 8px 0;
    }

    .login-form .input-group .input-group-text:last-child {
        border-radius: 0 8px 8px 0;
    }

    /* Login Button */
    .login-btn {
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        letter-spacing: 0.5px;
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        border: none;
        transition: all 0.3s ease;
    }

    .login-btn:hover {
        background: linear-gradient(135deg, #224abe 0%, #4e73df 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
    }

    /* Demo Credentials */
    .demo-credentials .alert {
        border-radius: 10px;
        border-left: 4px solid #36b9cc;
    }

    /* Form Check */
    .form-check-input:checked {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .login-branding {
            display: none;
        }
        
        .login-form-wrapper {
            padding: 40px 30px;
        }
    }

    @media (max-width: 576px) {
        .login-form-wrapper {
            padding: 30px 20px;
        }
        
        .login-card {
            border-radius: 15px;
        }
    }

    /* Error States */
    .was-validated .form-control:invalid,
    .form-control.is-invalid {
        border-color: #e74a3b;
        background-image: none;
    }

    /* Animation */
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .login-card {
        animation: slideUp 0.5s ease-out;
    }
</style>
@endpush

@push('scripts')
<script>
    // Toggle Password Visibility
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // Auto-focus email field
    $(document).ready(function() {
        $('#email').focus();
        
        // Add subtle animation to form elements
        $('.form-group').each(function(index) {
            $(this).css({
                'animation': `fadeInUp 0.5s ease-out ${index * 0.1}s both`
            });
        });
    });

    // Add custom animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
</script>
@endpush
@endsection