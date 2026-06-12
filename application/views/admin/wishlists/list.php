<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-heart me-2"></i>Wishlists Management</h2>
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
                <div class="col-md-4">
                    <form method="get" class="d-flex">
                        <input type="text" name="search" class="form-control" placeholder="Search property or user..."
                               value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>">
                        <button type="submit" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-4">
                    <form method="get" class="d-flex">
                        <select name="user_id" class="form-select" onchange="this.form.submit();">
                            <option value="">All Users</option>
                            <?php foreach($users as $user): ?>
                                <option value="<?php echo $user->id; ?>" <?php echo isset($user_id) && $user_id === $user->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user->fullname ?? 'Unknown'); ?>
                                </option>
                            <?php endforeach; ?>
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
                            <th>User</th>
                            <th>Property</th>
                            <th>Price</th>
                            <th>Location</th>
                            <th>Image</th>
                            <th>Added Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($wishlists)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No wishlist items found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($wishlists as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item->user_name ?? 'N/A'); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($item->property_name ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php if($item->property_price): ?>
                                            ₹<?php echo htmlspecialchars(number_format($item->property_price, 0)); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($item->property_location ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($item->property_image)): ?>
                                            <img src="<?php echo base_url($item->property_image); ?>" alt="Property"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($item->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo base_url('admin/wishlist_view/'.$item->id); ?>" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/wishlist_delete/'.$item->id); ?>" class="btn btn-sm btn-danger"
                                           onclick="return confirm('Remove from wishlist?')" title="Delete">
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
        <?php if(!empty($wishlists)): ?>
            <div class="card-footer text-muted">
                Total: <?php echo count($wishlists); ?> wishlist item(s)
            </div>
        <?php endif; ?>
    </div>
</div>
