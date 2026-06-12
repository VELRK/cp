<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Location</h2>

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
                    <label class="form-label">City *</label>
                    <select class="form-control" name="city_id" required>
                        <option value="">Select City</option>
                        <?php foreach($cities as $city): ?>
                            <option value="<?php echo $city->id; ?>" <?php echo $location->city_id == $city->id ? 'selected' : ''; ?>>
                                <?php echo $city->name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">
                        <a href="<?php echo base_url('admin/cities'); ?>" target="_blank">Add New City</a>
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Location Name *</label>
                    <input type="text" class="form-control" name="name" value="<?php echo $location->name; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Location Image</label>
                    <?php if(!empty($location->image)): ?>
                        <div class="mb-2">
                            <img src="<?php echo base_url($location->image); ?>" alt="<?php echo htmlspecialchars($location->name); ?>" style="max-width: 200px; max-height: 150px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                            <br>
                            <small class="text-muted">Current image</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <small class="text-muted">Recommended size: 800x600px. Max size: 2MB. Formats: JPG, PNG, GIF, WEBP</small>
                    <?php if(!empty($location->image)): ?>
                        <input type="hidden" name="existing_image" value="<?php echo $location->image; ?>">
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="active" <?php echo $location->status == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $location->status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Location
                    </button>
                    <a href="<?php echo base_url('admin/locations'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

