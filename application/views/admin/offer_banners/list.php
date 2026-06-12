<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-gift me-2"></i>Offer Banners</h2>
        <a href="<?php echo base_url('admin/offer_banner_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Offer Banner
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
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Link</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($offer_banners)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No offer banners found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($offer_banners as $banner): ?>
                                <tr>
                                    <td><?php echo $banner->id; ?></td>
                                    <td>
                                        <?php if($banner->image): ?>
                                            <img src="<?php echo base_url($banner->image); ?>" style="max-width: 150px; height: 80px; object-fit: cover;" class="img-thumbnail">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($banner->title ?: '-'); ?></td>
                                    <td>
                                        <?php if($banner->link): ?>
                                            <a href="<?php echo htmlspecialchars($banner->link); ?>" target="_blank" class="text-primary">
                                                <i class="fas fa-external-link-alt"></i> View Link
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $banner->status == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($banner->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($banner->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo base_url('admin/offer_banner_edit/'.$banner->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/offer_banner_delete/'.$banner->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
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
    </div>
</div>

