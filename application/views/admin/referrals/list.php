<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-handshake me-2"></i>Referral Campaigns</h2>
        <a href="<?php echo base_url('admin/referral_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Referral
        </a>
    </div>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-light">
            <div class="row g-2">
                <div class="col-md-3">
                    <form method="get" class="d-flex">
                        <select name="status" class="form-select" onchange="this.form.submit();">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo isset($status) && $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo isset($status) && $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo isset($status) && $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Referral Code</th>
                            <th>Referrer Name</th>
                            <th>Referred Name</th>
                            <th>Status</th>
                            <th>Reward Points</th>
                            <th>Reward Amount</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($referrals)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No referrals found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($referrals as $referral): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($referral->referral_code); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($referral->referrer_name ?? 'N/A'); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($referral->referrer_email ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($referral->referred_name ?? 'N/A'); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($referral->referred_email ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $referral->status === 'completed' ? 'success' : ($referral->status === 'pending' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($referral->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($referral->reward_points); ?></td>
                                    <td>₹<?php echo htmlspecialchars(number_format($referral->reward_amount, 2)); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($referral->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo base_url('admin/referral_edit/'.$referral->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/referral_delete/'.$referral->id); ?>" class="btn btn-sm btn-danger"
                                           onclick="return confirm('Delete this referral?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if(!empty($referrals)): ?>
            <div class="card-footer text-muted">
                Total: <?php echo count($referrals); ?> referral(s)
            </div>
        <?php endif; ?>
    </div>
</div>
