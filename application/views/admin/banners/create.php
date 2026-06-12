<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-plus me-2"></i>Create Banner</h2>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Banner Image *</label>
                    <input type="file" class="form-control" name="image" accept="image/*" id="bannerImageInput" required>
                    <small class="text-muted">Recommended size: 1920x600px</small>
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
                        <option value="inactive">Inactive</option>
                        <option value="active">Active</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Banner
                    </button>
                    <a href="<?php echo base_url('admin/banners'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

