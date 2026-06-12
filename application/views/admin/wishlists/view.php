<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-heart me-2" style="color: #dc3545;"></i>Wishlist Item Details</h2>
        <a href="<?php echo base_url('admin/wishlists'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <?php if(!empty($wishlist->property_image)): ?>
                                <img src="<?php echo base_url($wishlist->property_image); ?>" alt="Property"
                                     class="img-fluid rounded" style="width: 100%; height: 300px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                    <i class="fas fa-image fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h4><?php echo htmlspecialchars($wishlist->property_name ?? 'Unknown Property'); ?></h4>

                            <div class="mb-3">
                                <span class="badge bg-primary">
                                    <i class="fas fa-tag me-2"></i>Property ID: <?php echo htmlspecialchars($wishlist->property_id); ?>
                                </span>
                            </div>

                            <div class="mb-3">
                                <h6>Location</h6>
                                <p><i class="fas fa-map-marker-alt me-2" style="color: #e74c3c;"></i><?php echo htmlspecialchars($wishlist->property_location ?? 'N/A'); ?></p>
                            </div>

                            <div class="mb-3">
                                <h6>Price</h6>
                                <p class="h5" style="color: #27ae60;">
                                    <?php if($wishlist->property_price): ?>
                                        ₹<?php echo htmlspecialchars(number_format($wishlist->property_price, 0)); ?>
                                    <?php else: ?>
                                        Price Not Available
                                    <?php endif; ?>
                                </p>
                            </div>

                            <div class="mb-3">
                                <h6>Added to Wishlist</h6>
                                <p><?php echo date('F d, Y at g:i A', strtotime($wishlist->created_at)); ?></p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <a href="<?php echo base_url('admin/wishlist_delete/'.$wishlist->id); ?>" class="btn btn-danger"
                           onclick="return confirm('Remove this item from wishlist?')">
                            <i class="fas fa-trash me-2"></i>Remove from Wishlist
                        </a>
                        <a href="<?php echo base_url('admin/wishlists'); ?>" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-user me-2"></i>User Information
                </div>
                <div class="card-body">
                    <h6><?php echo htmlspecialchars($wishlist->user_name ?? 'Unknown User'); ?></h6>
                    <p class="text-muted mb-0">
                        <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($wishlist->user_email ?? 'N/A'); ?>
                    </p>
                    <small class="text-muted">User ID: <?php echo htmlspecialchars($wishlist->user_id); ?></small>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-info-circle me-2"></i>Item Details
                </div>
                <div class="card-body small">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Wishlist ID:</strong></td>
                            <td><code><?php echo htmlspecialchars($wishlist->id); ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>Property ID:</strong></td>
                            <td><code><?php echo htmlspecialchars($wishlist->property_id); ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>User ID:</strong></td>
                            <td><code><?php echo htmlspecialchars($wishlist->user_id); ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>Added Date:</strong></td>
                            <td><?php echo date('M d, Y', strtotime($wishlist->created_at)); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Updated:</strong></td>
                            <td><?php echo date('M d, Y', strtotime($wishlist->updated_at)); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
