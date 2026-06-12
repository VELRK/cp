<!-- Login Page - Mobile App Style -->
<section class="login-page" style="min-height: 100vh; background: #ffffff; display: flex; align-items: center; justify-content: center; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <div class="login-container" style="width: 100%; max-width: 450px; background: white; padding: 30px 20px; position: relative;">
        <!-- Logo at top left -->
        <div style="position: absolute; top: 20px; left: 20px;">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff6b6b, #ff8e53); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);">
                <span style="color: white; font-weight: 700; font-size: 20px;">G</span>
            </div>
        </div>

        <!-- Welcome Section -->
        <div class="welcome-section" style="text-align: center; margin-top: 80px; margin-bottom: 40px;">
            <h1 style="font-size: 42px; font-weight: 700; color: #2c3e50; margin: 0 0 10px 0; font-family: 'Brush Script MT', cursive, serif; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <span style="font-size: 36px;">👋</span>
                <span>Welcome Back!</span>
            </h1>
            <p style="color: #6c757d; font-size: 16px; margin: 0; font-weight: 400;">Login to continue your journey.</p>
        </div>

        <!-- Login Form -->
        <form id="loginForm" method="post">
            <!-- Phone Number Input -->
            <div class="form-group mb-3" style="margin-bottom: 20px;">
                <!-- <div class="phone-input-container" style="position: relative; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 15px; padding: 0; overflow: hidden; transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; height: 60px;">
                        <div style="padding: 0 20px; border-right: 1px solid #e9ecef; color: #2c3e50; font-weight: 600; font-size: 16px; white-space: nowrap; background: white;">
                            +91
                        </div>
                        <input 
                            type="tel" 
                            class="form-control" 
                            id="phoneNumber" 
                            name="phoneNumber" 
                            placeholder="Enter your Phone Number" 
                            required 
                            style="flex: 1; border: none; background: transparent; padding: 0 20px; height: 60px; font-size: 16px; color: #2c3e50; outline: none;"
                            onfocus="this.parentElement.parentElement.style.borderColor='#3498db'; this.parentElement.parentElement.style.boxShadow='0 0 0 3px rgba(52, 152, 219, 0.1)'"
                            onblur="this.parentElement.parentElement.style.borderColor='#e9ecef'; this.parentElement.parentElement.style.boxShadow='none'"
                        >
                    </div>
                </div>
                
                <!-- Auto-fill option -->
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 12px; margin-left: 5px;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #6c757d;">
                        <path d="M8 0L9.5 5.5L15 7L9.5 8.5L8 14L6.5 8.5L1 7L6.5 5.5L8 0Z" fill="currentColor" opacity="0.6"/>
                    </svg>
                    <span style="color: #6c757d; font-size: 14px; cursor: pointer;" onclick="autoFillPhone()">Auto-fill phone number</span>
                </div>
            </div>

            <!-- Continue Button -->
            <div class="d-grid mb-4" style="margin-bottom: 25px;">
                <button 
                    type="submit" 
                    class="btn btn-primary btn-continue" 
                    style="background: #1e3a8a; border: none; border-radius: 15px; height: 55px; font-size: 16px; font-weight: 700; color: white; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3); text-transform: none; letter-spacing: 0;"
                    onmouseover="this.style.background='#1e40af'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(30, 58, 138, 0.4)'"
                    onmouseout="this.style.background='#1e3a8a'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(30, 58, 138, 0.3)'"
                >
                    Continue
                </button>
            </div>

            <!-- OR Separator -->
            <div class="divider" style="position: relative; text-align: center; margin: 30px 0;">
                <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                <span style="background: white; padding: 0 20px; color: #6c757d; font-size: 14px; font-weight: 500;">OR</span>
            </div> -->

            <!-- Google Login Button -->
            <div class="d-grid mb-4" style="margin-bottom: 30px;">
                <button 
                    type="button" 
                    class="btn btn-google" 
                    onclick="handleGoogleLogin()"
                    style="background: white; border: 2px solid #e9ecef; border-radius: 15px; height: 55px; font-size: 16px; font-weight: 600; color: #2c3e50; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);"
                    onmouseover="this.style.borderColor='#db4437'; this.style.boxShadow='0 4px 12px rgba(219, 68, 55, 0.15)'; this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.borderColor='#e9ecef'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)'; this.style.transform='translateY(0)'"
                >
                    <!-- Google Logo -->
                    <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span>Continue with Google</span>
                </button>
            </div>

            <!-- Skip for now -->
            <div style="text-align: center; margin-bottom: 20px;">
                <a href="<?php echo base_url(); ?>" style="color: #2c3e50; text-decoration: none; font-size: 16px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: color 0.3s ease;" onmouseover="this.style.color='#3498db'" onmouseout="this.style.color='#2c3e50'">
                    Skip for now
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>

            <!-- Terms & Conditions -->
            <div style="text-align: center;">
                <a href="#" style="color: #6c757d; text-decoration: underline; font-size: 14px; transition: color 0.3s ease;" onmouseover="this.style.color='#3498db'" onmouseout="this.style.color='#6c757d'">
                    Terms & Conditions
                </a>
            </div>
        </form>
    </div>
</section>

<style>
.login-page {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.phone-input-container:focus-within {
    border-color: #3498db !important;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1) !important;
}

.btn-continue:active {
    transform: translateY(0) !important;
    box-shadow: 0 2px 10px rgba(30, 58, 138, 0.3) !important;
}

.btn-google:active {
    transform: translateY(0) !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .login-container {
        padding: 20px 15px !important;
    }
    
    .welcome-section h1 {
        font-size: 36px !important;
    }
    
    .welcome-section p {
        font-size: 14px !important;
    }
}

/* Tablet and Desktop */
@media (min-width: 769px) {
    .login-container {
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        border-radius: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const phoneNumber = document.getElementById('phoneNumber').value.trim();
            
            if (!phoneNumber) {
                alert('Please enter your phone number');
                return;
            }
            
            // Validate phone number (should be 10 digits after +91)
            const phoneRegex = /^\d{10}$/;
            if (!phoneRegex.test(phoneNumber)) {
                alert('Please enter a valid 10-digit phone number');
                return;
            }
            
            // Show loading state
            const submitBtn = loginForm.querySelector('.btn-continue');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid white; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span> Processing...';
            submitBtn.disabled = true;
            
            // Simulate login (replace with actual AJAX call)
            setTimeout(() => {
                // Here you would make an AJAX call to your backend
                // For now, we'll just redirect
                window.location.href = '<?php echo base_url(); ?>';
            }, 1500);
        });
    }
    
    // Phone number formatting
    const phoneInput = document.getElementById('phoneNumber');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Remove all non-digit characters
            let value = e.target.value.replace(/\D/g, '');
            
            // Limit to 10 digits
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            
            e.target.value = value;
        });
    }
});

// Auto-fill phone number function
function autoFillPhone() {
    // This would typically use browser APIs to auto-fill
    // For demo purposes, we'll just show an alert
    if (navigator.credentials && navigator.credentials.get) {
        // Try to use Web Credentials API
        alert('Auto-fill feature would use browser credentials API');
    } else {
        // Fallback: prompt user
        const phone = prompt('Enter your phone number to auto-fill:');
        if (phone) {
            document.getElementById('phoneNumber').value = phone.replace(/\D/g, '').substring(0, 10);
        }
    }
}

// Google Login handler
function handleGoogleLogin() {
    // This would integrate with Google OAuth
    alert('Redirecting to Google login...');
    // window.location.href = 'YOUR_GOOGLE_OAUTH_URL';
}

// Add spin animation for loading
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);
</script>
