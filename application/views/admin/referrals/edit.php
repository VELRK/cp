<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-edit me-2"></i>Edit Referral</h2>
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
                                <label for="referrer" class="form-label">Referrer</label>
                                <input type="text" class="form-control" disabled
                                       value="<?php echo htmlspecialchars($referral->referrer_name ?? 'Unknown'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="referred" class="form-label">Referred User</label>
                                <input type="text" class="form-control" disabled
                                       value="<?php echo htmlspecialchars($referral->referred_name ?? 'Unknown'); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="referral_code" class="form-label">Referral Code</label>
                                <input type="text" class="form-control" disabled
                                       value="<?php echo htmlspecialchars($referral->referral_code); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending" <?php echo $referral->status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="completed" <?php echo $referral->status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $referral->status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="reward_points" class="form-label">Reward Points</label>
                                <input type="number" class="form-control" id="reward_points" name="reward_points"
                                       value="<?php echo $referral->reward_points; ?>" min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="reward_amount" class="form-label">Reward Amount (₹)</label>
                                <input type="number" class="form-control" id="reward_amount" name="reward_amount"
                                       value="<?php echo $referral->reward_amount; ?>" min="0" step="0.01">
                            </div>
                        </div>

                        <div class="row mb-3 p-3 bg-light border rounded">
                            <div class="col-md-12">
                                <h6><i class="fas fa-clock me-2"></i>Timeline</h6>
                                <small class="text-muted">
                                    <div>Created: <?php echo date('M d, Y H:i', strtotime($referral->created_at)); ?></div>
                                    <div>Updated: <?php echo date('M d, Y H:i', strtotime($referral->updated_at)); ?></div>
                                </small>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Referral
                            </button>
                            <a href="<?php echo base_url('admin/referrals'); ?>" class="btn btn-secondary">Cancel</a>
                            <a href="<?php echo base_url('admin/referral_delete/'.$referral->id); ?>" class="btn btn-danger"
                               onclick="return confirm('Delete this referral?')">
                                <i class="fas fa-trash me-2"></i>Delete
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle me-2"></i>Referral Details
                </div>
                <div class="card-body small">
                    <div class="mb-3">
                        <strong>Referrer Email:</strong><br>
                        <code><?php echo htmlspecialchars($referral->referrer_email ?? 'N/A'); ?></code>
                    </div>

                    <div class="mb-3">
                        <strong>Referred Email:</strong><br>
                        <code><?php echo htmlspecialchars($referral->referred_email ?? 'N/A'); ?></code>
                    </div>

                    <hr>

                    <div>
                        <strong>Current Status:</strong><br>
                        <span class="badge bg-<?php echo $referral->status === 'completed' ? 'success' : ($referral->status === 'pending' ? 'warning' : 'danger'); ?>">
                            <?php echo ucfirst($referral->status); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
