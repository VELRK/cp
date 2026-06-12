<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-images me-2"></i>Banners</h2>
        <a href="<?php echo base_url('admin/banner_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Banner
        </a>
    </div>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $this->session->flashdata('success'); ?>
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
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($banners)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No banners found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($banners as $banner): ?>
                                <tr>
                                    <td><?php echo $banner->id; ?></td>
                                    <td>
                                        <?php if(isset($banner->image) && !empty($banner->image)): ?>
                                            <img src="<?php echo base_url($banner->image); ?>" style="max-width: 150px; height: 60px; object-fit: cover;" class="img-thumbnail">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $banner->status == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($banner->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($banner->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo base_url('admin/banner_toggle/'.$banner->id); ?>" class="btn btn-sm btn-<?php echo $banner->status == 'active' ? 'warning' : 'success'; ?>" title="Toggle Status">
                                            <i class="fas fa-<?php echo $banner->status == 'active' ? 'eye-slash' : 'eye'; ?>"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/banner_edit/'.$banner->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/banner_delete/'.$banner->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
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

