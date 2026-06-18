<!-- Register Page -->
<section class="register-page" style="min-height: 100vh; background: #f8f9fa; display: flex; align-items: center; position: relative;">
    <!-- Left Background -->
    <div class="register-bg-left" style="position: absolute; left: 0; top: 0; width: 40%; height: 100%; background: #e9ecef; z-index: 1;"></div>
    
    <!-- Right Content -->
    <div class="register-content" style="position: relative; z-index: 2; width: 100%; display: flex; justify-content: flex-end; padding: 0 50px;">
        <div class="register-form-container" style="width: 60%; max-width: 500px; background: white; border-radius: 20px; padding: 60px 50px; box-shadow: 0 20px 60px rgba(0,0,0,0.1); position: relative;">
            <!-- Close Button -->
            <button class="close-btn" onclick="history.back()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 24px; color: #6c757d; cursor: pointer;">&times;</button>
            
            <!-- Register Form -->
            <div class="register-form">
                <h1 class="register-title" style="font-size: 2.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 40px;">Register</h1>
                
                <form id="registerForm" method="post">
                    <!-- User Name Field -->
                    <div class="form-group mb-4">
                        <label for="username" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">User name</label>
                        <div class="input-group">
                            <span class="input-icon" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 3;">
                                <i class="fas fa-user" style="font-size: 16px;"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="User name" required style="padding-left: 45px; border: 2px solid #e9ecef; border-radius: 12px; height: 50px; font-size: 16px; transition: all 0.3s ease;">
                        </div>
                    </div>
                    
                    <!-- Email Field -->
                    <div class="form-group mb-4">
                        <label for="email" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">Email address</label>
                        <div class="input-group">
                            <span class="input-icon" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 3;">
                                <i class="fas fa-envelope" style="font-size: 16px;"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email address" required style="padding-left: 45px; border: 2px solid #e9ecef; border-radius: 12px; height: 50px; font-size: 16px; transition: all 0.3s ease;">
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-group mb-4">
                        <label for="password" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">Password</label>
                        <div class="input-group">
                            <span class="input-icon" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 3;">
                                <i class="fas fa-lock" style="font-size: 16px;"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Your password" required style="padding-left: 45px; border: 2px solid #e9ecef; border-radius: 12px; height: 50px; font-size: 16px; transition: all 0.3s ease;">
                        </div>
                    </div>
                    
                    <!-- Confirm Password Field -->
                    <div class="form-group mb-4">
                        <label for="confirmPassword" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">Confirm password</label>
                        <div class="input-group">
                            <span class="input-icon" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 3;">
                                <i class="fas fa-lock" style="font-size: 16px;"></i>
                            </span>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required style="padding-left: 45px; border: 2px solid #e9ecef; border-radius: 12px; height: 50px; font-size: 16px; transition: all 0.3s ease;">
                        </div>
                    </div>
                    
                    <!-- Sign Up Button -->
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-register" style="background: #3498db; border: none; border-radius: 12px; height: 50px; font-size: 16px; font-weight: 600; transition: all 0.3s ease;">
                            Sign Up
                        </button>
                    </div>
                    
                    <!-- Sign In Link -->
                    <div class="text-center mb-4">
                        <span style="color: #6c757d;">Don't you have an account? </span>
                        <a href="<?php echo base_url('login'); ?>" class="signin-link" style="color: #3498db; text-decoration: none; font-weight: 600;">Sign In</a>
                    </div>
                    
                    <!-- Divider -->
                    <div class="divider" style="position: relative; text-align: center; margin: 30px 0;">
                        <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                        <span style="background: white; padding: 0 20px; color: #6c757d; font-size: 14px;">or login with</span>
                    </div>
                    
                    <!-- Social Login Buttons -->
                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary btn-social w-100" style="border: 2px solid #e9ecef; border-radius: 12px; height: 50px; font-weight: 600; color: #2c3e50; transition: all 0.3s ease;">
                                <i class="fab fa-google me-2" style="color: #4285f4;"></i>Google
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary btn-social w-100" style="border: 2px solid #e9ecef; border-radius: 12px; height: 50px; font-weight: 600; color: #2c3e50; transition: all 0.3s ease;">
                                <i class="fab fa-facebook-f me-2" style="color: #1877f2;"></i>Facebook
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.register-page {
    font-family: 'Inter', sans-serif;
}

.form-control:focus {
    border-color: #3498db !important;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25) !important;
}

.btn-register:hover {
    background: #2980b9 !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
}

.btn-social:hover {
    border-color: #3498db !important;
    color: #3498db !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.signin-link:hover {
    text-decoration: underline !important;
}

.close-btn:hover {
    color: #2c3e50 !important;
}

@media (max-width: 768px) {
    .register-content {
        padding: 0 20px !important;
    }
    
    .register-form-container {
        width: 100% !important;
        padding: 40px 30px !important;
    }
    
    .register-bg-left {
        display: none !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (!username || !email || !password || !confirmPassword) {
            alert('Please fill in all fields');
            return;
        }
        
        if (password !== confirmPassword) {
            alert('Passwords do not match');
            return;
        }
        
        if (password.length < 6) {
            alert('Password must be at least 6 characters long');
            return;
        }
        
        // Simulate registration
        alert('Registration successful!');
        window.location.href = '<?php echo base_url('login'); ?>';
    });
    
    // Social login buttons
    document.querySelectorAll('.btn-social').forEach(button => {
        button.addEventListener('click', function() {
            const platform = this.textContent.trim();
            alert('Redirecting to ' + platform + ' registration...');
        });
    });
});
</script>
