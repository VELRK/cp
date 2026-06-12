<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-plus me-2"></i>Create Offer Banner</h2>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" placeholder="Enter offer title (optional)">
                </div>

                <div class="mb-3">
                    <label class="form-label">Offer Banner Image *</label>
                    <input type="file" class="form-control" name="image" accept="image/*" id="offerBannerImageInput" required>
                    <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WEBP. Max size: 5MB</small>
                    <div id="offerBannerImagePreview" class="mt-2"></div>
                </div>
                <script>
                    document.getElementById('offerBannerImageInput').addEventListener('change', function(e) {
                        const preview = document.getElementById('offerBannerImagePreview');
                        preview.innerHTML = '';
                        if (this.files && this.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'img-thumbnail';
                                img.style.maxWidth = '500px';
                                img.style.maxHeight = '300px';
                                preview.appendChild(img);
                            };
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                </script>

                <div class="mb-3">
                    <label class="form-label">Link (Optional)</label>
                    <input type="url" class="form-control" name="link" placeholder="https://example.com">
                    <small class="form-text text-muted">URL to redirect when banner is clicked</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="inactive">Inactive</option>
                        <option value="active">Active</option>
                    </select>
                    <small class="form-text text-muted">Only active offer banners will be shown in the modal on home page</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Offer Banner
                    </button>
                    <a href="<?php echo base_url('admin/offer_banners'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

