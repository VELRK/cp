<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Edit City</h2>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">City Name *</label>
                    <input type="text" class="form-control" name="name" value="<?php echo $city->name; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">City Image</label>
                    <?php if (!empty($city->image)): ?>
                        <div class="mb-2">
                            <img src="<?php echo base_url($city->image); ?>" alt="City Image" style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 4px;">
                            <br>
                            <small class="text-muted">Current image</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WEBP. Max size: 2MB. Leave empty to keep current image.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="active" <?php echo $city->status == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $city->status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update City
                    </button>
                    <a href="<?php echo base_url('admin/cities'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

