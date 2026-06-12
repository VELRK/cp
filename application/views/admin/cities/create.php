<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-plus me-2"></i>Create City</h2>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">City Name *</label>
                    <input type="text" class="form-control" name="name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">City Image</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WEBP. Max size: 2MB</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create City
                    </button>
                    <a href="<?php echo base_url('admin/cities'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

