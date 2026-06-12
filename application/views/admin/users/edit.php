<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-edit me-2"></i>Edit User</h2>
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
                                       placeholder="Enter full name" value="<?php echo isset($user) ? htmlspecialchars($user->fullname ?? '') : ''; ?>">
                                <div class="invalid-feedback">Full name is required.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       placeholder="Enter email address" value="<?php echo isset($user) ? htmlspecialchars($user->email ?? '') : ''; ?>">
                                <div class="invalid-feedback">Please provide a valid email.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="countrycode" class="form-label">Country Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="countrycode" name="countrycode" required
                                       placeholder="+91" value="<?php echo isset($user) ? htmlspecialchars($user->countrycode ?? '+91') : '+91'; ?>">
                                <div class="invalid-feedback">Country code is required.</div>
                            </div>
                            <div class="col-md-8">
                                <label for="phonenumber" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phonenumber" name="phonenumber" required
                                       placeholder="10-digit phone number" value="<?php echo isset($user) ? htmlspecialchars($user->phonenumber ?? '') : ''; ?>"
                                       pattern="\d{10}" title="Phone must be 10 digits">
                                <small class="form-text text-muted">Indian phone numbers are 10 digits (without country code)</small>
                                <div class="invalid-feedback">Please provide a valid 10-digit phone number.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       placeholder="Enter city" value="<?php echo isset($user) ? htmlspecialchars($user->city ?? '') : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state"
                                       placeholder="Enter state" value="<?php echo isset($user) ? htmlspecialchars($user->state ?? '') : ''; ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="pincode" class="form-label">PIN Code</label>
                                <input type="text" class="form-control" id="pincode" name="pincode"
                                       placeholder="Enter PIN code" value="<?php echo isset($user) ? htmlspecialchars($user->pincode ?? '') : ''; ?>"
                                       pattern="\d{6}" title="PIN code should be 6 digits">
                            </div>
                            <div class="col-md-6">
                                <label for="isactive" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="isactive" name="isactive" required>
                                    <option value="active" <?php echo (isset($user) && $user->isactive === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($user) && $user->isactive === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="logintype" class="form-label">Login Type</label>
                                <select class="form-select" id="logintype" name="logintype">
                                    <option value="manual" <?php echo (isset($user) && $user->logintype === 'manual') ? 'selected' : 'selected'; ?>>Manual</option>
                                    <option value="google" <?php echo (isset($user) && $user->logintype === 'google') ? 'selected' : ''; ?>>Google</option>
                                    <option value="facebook" <?php echo (isset($user) && $user->logintype === 'facebook') ? 'selected' : ''; ?>>Facebook</option>
                                </select>
                            </div>
                        </div>

                        <?php if(isset($user)): ?>
                            <div class="row mb-3 p-3 bg-light border rounded">
                                <div class="col-md-12">
                                    <h6 class="mb-2"><i class="fas fa-history me-2"></i>User History</h6>
                                    <small class="text-muted">
                                        <div>Created: <?php echo date('M d, Y H:i', strtotime($user->created_at ?? 'now')); ?></div>
                                        <div>Last Updated: <?php echo date('M d, Y H:i', strtotime($user->updated_at ?? 'now')); ?></div>
                                        <div>User ID: <code><?php echo htmlspecialchars($user->id); ?></code></div>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update User
                            </button>
                            <a href="<?php echo base_url('admin/users'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <?php if(isset($user)): ?>
                                <a href="<?php echo base_url('admin/user_delete/'.$user->id); ?>" class="btn btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fas fa-trash me-2"></i>Delete
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle me-2"></i>Editing Tips
                </div>
                <div class="card-body small">
                    <h6>What You Can Edit:</h6>
                    <ul>
                        <li>Full name and contact info</li>
                        <li>Address (city, state, PIN)</li>
                        <li>Status (active/inactive)</li>
                        <li>Login type</li>
                    </ul>

                    <hr>

                    <h6>Important Notes:</h6>
                    <ul>
                        <li>Email and phone must be unique</li>
                        <li>Changes are saved immediately</li>
                        <li>Phone format: 10 digits</li>
                        <li>PIN Code format: 6 digits</li>
                    </ul>

                    <hr>

                    <h6>User Created:</h6>
                    <p class="text-muted mb-0">
                        <?php echo isset($user) ? date('M d, Y', strtotime($user->created_at ?? 'now')) : 'N/A'; ?>
                    </p>
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
