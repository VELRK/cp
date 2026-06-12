                <div class="overlay-dashboard"></div>
            </div>
        </div>
        <!-- /#page -->
    </div>

    <!-- Javascript -->
    <script type="text/javascript" src="<?php echo base_url('assets/js/bootstrap.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/ectrajs/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/plugin.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/chart.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/chart-init.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/jquery.nice-select.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/countto.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/shortcodes.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/jqueryui.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/ectrajs/main.js'); ?>"></script>

    <!-- Delete Account Confirmation Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid #e9ecef; padding: 20px;">
                    <h5 class="modal-title fw-6" id="deleteAccountModalLabel">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #ff6b6b; margin-bottom: 16px;">
                            <path d="M3 6H5H21M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6H19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 11V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 11V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <p class="text-center" style="font-size: 16px; margin-bottom: 8px; font-weight: 600;">Are you sure you want to delete your account?</p>
                    <p class="text-center text-muted" style="font-size: 14px; margin-bottom: 0;">This action cannot be undone. All your data will be permanently deleted.</p>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 10px 20px;">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteAccountBtn" style="border-radius: 8px; padding: 10px 20px; background: #ff6b6b; border: none;">Yes, Delete Account</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dashboard Logout Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('dashboardLogoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Confirm logout
                    if (confirm('Are you sure you want to logout?')) {
                        // Show loading state
                        const originalHTML = logoutBtn.innerHTML;
                        logoutBtn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid white; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 8px;"></span> Logging out...';
                        logoutBtn.style.pointerEvents = 'none';
                        
                        // Clear localStorage/sessionStorage
                        localStorage.removeItem('userData');
                        sessionStorage.removeItem('userData');
                        
                        // Call logout API
                        fetch('<?php echo base_url('auth/logout'); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Logout successful:', data);
                            // Redirect to home page
                            window.location.href = '<?php echo base_url(); ?>';
                        })
                        .catch(error => {
                            console.error('Logout error:', error);
                            // Still redirect even if API call fails
                            window.location.href = '<?php echo base_url(); ?>';
                        });
                    }
                });
            }

            // Delete Account Functionality
            const deleteAccountBtn = document.getElementById('deleteAccountBtn');
            const confirmDeleteBtn = document.getElementById('confirmDeleteAccountBtn');
            const deleteAccountModal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));

            if (deleteAccountBtn) {
                deleteAccountBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    deleteAccountModal.show();
                });
            }

            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    const originalText = confirmDeleteBtn.innerHTML;
                    confirmDeleteBtn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid white; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 8px;"></span> Deleting...';
                    confirmDeleteBtn.disabled = true;

                    // Call delete account API
                    fetch('<?php echo base_url('dashboard/delete_account'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Clear all local storage
                            localStorage.clear();
                            sessionStorage.clear();
                            
                            // Show success message
                            alert('Your account has been deleted successfully.');
                            
                            // Redirect to home page
                            window.location.href = '<?php echo base_url(); ?>';
                        } else {
                            alert(data.message || 'Failed to delete account. Please try again.');
                            confirmDeleteBtn.innerHTML = originalText;
                            confirmDeleteBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Delete account error:', error);
                        alert('An error occurred. Please try again.');
                        confirmDeleteBtn.innerHTML = originalText;
                        confirmDeleteBtn.disabled = false;
                    });
                });
            }
        });
    </script>

</body>
</html>


