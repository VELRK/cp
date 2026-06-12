<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus-circle me-2"></i>Create New Referral</h2>
                <a href="<?php echo base_url('admin/referrals'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
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
                    <form method="post">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="referrer_id" class="form-label">Referrer <span class="text-danger">*</span></label>
                                <select class="form-select" id="referrer_id" name="referrer_id" required>
                                    <option value="">Select Referrer</option>
                                    <?php foreach($users as $user): ?>
                                        <option value="<?php echo $user->id; ?>">
                                            <?php echo htmlspecialchars($user->fullname ?? 'Unknown') . ' (' . htmlspecialchars($user->email ?? '') . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="referred_id" class="form-label">Referred User <span class="text-danger">*</span></label>
                                <select class="form-select" id="referred_id" name="referred_id" required>
                                    <option value="">Select User</option>
                                    <?php foreach($users as $user): ?>
                                        <option value="<?php echo $user->id; ?>">
                                            <?php echo htmlspecialchars($user->fullname ?? 'Unknown') . ' (' . htmlspecialchars($user->email ?? '') . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="reward_points" class="form-label">Reward Points</label>
                                <input type="number" class="form-control" id="reward_points" name="reward_points" value="0" min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="reward_amount" class="form-label">Reward Amount (₹)</label>
                                <input type="number" class="form-control" id="reward_amount" name="reward_amount" value="0" min="0" step="0.01">
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Referral
                            </button>
                            <a href="<?php echo base_url('admin/referrals'); ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle me-2"></i>About Referrals
                </div>
                <div class="card-body small">
                    <h6>Referral Process:</h6>
                    <ol>
                        <li>Select the referrer (person who refers)</li>
                        <li>Select the referred user (new user)</li>
                        <li>Set status and rewards</li>
                        <li>Auto-generated referral code</li>
                    </ol>

                    <hr>

                    <h6>Statuses:</h6>
                    <ul>
                        <li><strong>Pending:</strong> Not yet completed</li>
                        <li><strong>Completed:</strong> Referral successful</li>
                        <li><strong>Cancelled:</strong> Invalid referral</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
