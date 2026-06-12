<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Offer Banner</h2>

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
                    <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($offer_banner->title ?: ''); ?>" placeholder="Enter offer title (optional)">
                </div>

                <div class="mb-3">
                    <label class="form-label">Offer Banner Image</label>
                    <?php if (!empty($offer_banner->image)): ?>
                        <div class="mb-2">
                            <img src="<?php echo base_url($offer_banner->image); ?>" alt="Current Image" style="max-width: 500px; max-height: 300px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px; padding: 10px;">
                            <br>
                            <small class="text-muted">Current image</small>
                        </div>
                        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($offer_banner->image); ?>">
                    <?php endif; ?>
                    <input type="file" class="form-control" name="image" accept="image/*" id="offerBannerImageInput">
                    <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WEBP. Max size: 5MB. Leave empty to keep current image.</small>
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
                    <input type="url" class="form-control" name="link" value="<?php echo htmlspecialchars($offer_banner->link ?: ''); ?>" placeholder="https://example.com">
                    <small class="form-text text-muted">URL to redirect when banner is clicked</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="inactive" <?php echo $offer_banner->status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="active" <?php echo $offer_banner->status == 'active' ? 'selected' : ''; ?>>Active</option>
                    </select>
                    <small class="form-text text-muted">Only active offer banners will be shown in the modal on home page</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Offer Banner
                    </button>
                    <a href="<?php echo base_url('admin/offer_banners'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

