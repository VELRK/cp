<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Category</h2>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Category Name *</label>
                    <input type="text" class="form-control" name="category_name" value="<?php echo htmlspecialchars($category->category_name); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category Image</label>
                    <?php if (!empty($category->image)): ?>
                        <div class="mb-2">
                            <img src="<?php echo base_url($category->image); ?>" alt="Category Image" style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 4px;">
                            <br>
                            <small class="text-muted">Current image</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WEBP. Max size: 2MB. Leave empty to keep current image.</small>
                    <?php if (!empty($category->image)): ?>
                        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($category->image); ?>">
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="active" <?php echo $category->status == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $category->status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Category
                    </button>
                    <a href="<?php echo base_url('admin/categories'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

