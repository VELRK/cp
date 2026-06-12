<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-plus me-2"></i>Add New User</h2>
                <a href="<?php echo base_url('admin/users'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
            </div>

            <?php if($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $this->session->flashdata('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="post" id="userForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required
                                       placeholder="Enter full name" value="<?php echo set_value('fullname'); ?>">
                                <div class="invalid-feedback">Full name is required.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       placeholder="Enter email address" value="<?php echo set_value('email'); ?>">
                                <div class="invalid-feedback">Please provide a valid email.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="countrycode" class="form-label">Country Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="countrycode" name="countrycode" required
                                       placeholder="+91" value="<?php echo set_value('countrycode', '+91'); ?>">
                                <div class="invalid-feedback">Country code is required.</div>
                            </div>
                            <div class="col-md-8">
                                <label for="phonenumber" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phonenumber" name="phonenumber" required
                                       placeholder="10-digit phone number" value="<?php echo set_value('phonenumber'); ?>"
                                       pattern="\d{10}" title="Phone must be 10 digits">
                                <small class="form-text text-muted">Indian phone numbers are 10 digits (without country code)</small>
                                <div class="invalid-feedback">Please provide a valid 10-digit phone number.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       placeholder="Enter city" value="<?php echo set_value('city'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state"
                                       placeholder="Enter state" value="<?php echo set_value('state'); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="pincode" class="form-label">PIN Code</label>
                                <input type="text" class="form-control" id="pincode" name="pincode"
                                       placeholder="Enter PIN code" value="<?php echo set_value('pincode'); ?>"
                                       pattern="\d{6}" title="PIN code should be 6 digits">
                            </div>
                            <div class="col-md-6">
                                <label for="isactive" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="isactive" name="isactive" required>
                                    <option value="active" <?php echo set_select('isactive', 'active', true); ?>>Active</option>
                                    <option value="inactive" <?php echo set_select('isactive', 'inactive'); ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password (Optional)</label>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Leave blank for no password">
                                <small class="form-text text-muted">Only set if user should login with password</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="logintype" class="form-label">Login Type</label>
                                <select class="form-select" id="logintype" name="logintype">
                                    <option value="manual" <?php echo set_select('logintype', 'manual', true); ?>>Manual</option>
                                    <option value="google">Google</option>
                                    <option value="facebook">Facebook</option>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create User
                            </button>
                            <a href="<?php echo base_url('admin/users'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle me-2"></i>User Information Guide
                </div>
                <div class="card-body">
                    <h6>Required Fields:</h6>
                    <ul class="small">
                        <li><strong>Full Name</strong> - User's complete name</li>
                        <li><strong>Email</strong> - Must be unique</li>
                        <li><strong>Phone Number</strong> - 10 digits, must be unique</li>
                        <li><strong>Status</strong> - Active or Inactive</li>
                    </ul>

                    <hr>

                    <h6>Optional Fields:</h6>
                    <ul class="small">
                        <li>City, State, PIN Code</li>
                        <li>Password (if user should login)</li>
                        <li>Login Type (for tracking)</li>
                    </ul>

                    <hr>

                    <h6>Validation Rules:</h6>
                    <ul class="small">
                        <li>Email must be valid format</li>
                        <li>Phone must be 10 digits</li>
                        <li>PIN Code should be 6 digits</li>
                        <li>No duplicate emails or phones</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>
