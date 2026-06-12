<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tags me-2"></i>Categories</h2>
        <a href="<?php echo base_url('admin/category_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Category
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
                            <th>Category Name</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($categories)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No categories found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category->id; ?></td>
                                    <td>
                                        <?php if(!empty($category->image)): ?>
                                            <img src="<?php echo base_url($category->image); ?>" alt="<?php echo htmlspecialchars($category->category_name); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($category->category_name); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $category->status == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($category->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($category->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo base_url('admin/category_edit/'.$category->id); ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/category_delete/'.$category->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
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

