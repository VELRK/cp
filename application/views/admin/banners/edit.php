<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Banner</h2>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Banner Image</label>
                    <?php if(isset($banner->image) && !empty($banner->image)): ?>
                        <div class="mb-2">
                            <img src="<?php echo base_url($banner->image); ?>" style="max-width: 500px;" class="img-thumbnail">
                            <p class="text-muted mt-1">Current banner image</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="image" accept="image/*" id="bannerImageInput">
                    <small class="text-muted">Upload new image to replace current banner</small>
                    <div id="bannerImagePreview" class="mt-2"></div>
                </div>
                <script>
                    document.getElementById('bannerImageInput').addEventListener('change', function(e) {
                        const preview = document.getElementById('bannerImagePreview');
                        preview.innerHTML = '';
                        if (this.files && this.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'img-thumbnail';
                                img.style.maxWidth = '500px';
                                preview.appendChild(img);
                            };
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                </script>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="inactive" <?php echo $banner->status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="active" <?php echo $banner->status == 'active' ? 'selected' : ''; ?>>Active</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Banner
                    </button>
                    <a href="<?php echo base_url('admin/banners'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

