<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-city me-2"></i>Cities</h2>
        <a href="<?php echo base_url('admin/city_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New City
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
                            <th>Name</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($cities)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No cities found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($cities as $city): ?>
                                <tr>
                                    <td><?php echo $city->id; ?></td>
                                    <td>
                                        <?php if(!empty($city->image)): ?>
                                            <img src="<?php echo base_url($city->image); ?>" alt="<?php echo $city->name; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $city->name; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $city->status == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($city->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($city->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo base_url('admin/city_edit/'.$city->id); ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/city_delete/'.$city->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? This will also delete all locations in this city.')">
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

