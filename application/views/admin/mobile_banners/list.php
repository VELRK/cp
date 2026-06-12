<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-mobile-alt me-2"></i>Mobile Banners</h2>
        <a href="<?php echo base_url('admin/mobile_banner_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Banner
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
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($mobile_banners)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No mobile banners found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($mobile_banners as $banner): ?>
                                <tr>
                                    <td><?php echo $banner->id; ?></td>
                                    <td>
                                        <?php if(!empty($banner->path)): ?>
                                            <img src="<?php echo base_url($banner->path); ?>" style="width:120px;height:70px;object-fit:cover;border-radius:6px;" class="img-thumbnail">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo base_url('admin/mobile_banner_toggle/'.$banner->id); ?>"
                                           class="badge text-decoration-none fs-6 bg-<?php echo $banner->status ? 'success' : 'secondary'; ?>">
                                            <?php echo $banner->status ? 'Active' : 'Inactive'; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="<?php echo base_url('admin/mobile_banner_edit/'.$banner->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/mobile_banner_delete/'.$banner->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this banner?')" title="Delete">
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
