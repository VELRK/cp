<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit me-2"></i>Edit Mobile Banner</h2>
        <a href="<?php echo base_url('admin/mobile_banners'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

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
                    <label class="form-label fw-semibold">Banner Image</label>
                    <?php if(!empty($mobile_banner->path)): ?>
                        <div class="mb-2">
                            <img src="<?php echo base_url($mobile_banner->path); ?>" class="img-thumbnail" style="max-width:320px;max-height:180px;object-fit:cover;" id="currentImage">
                            <div class="text-muted small mt-1">Current image</div>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="path" id="mbEditImageInput">
                    <small class="text-muted">Leave blank to keep current image. Any file type accepted.</small>
                    <div id="mbEditPreview" class="mt-2"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select class="form-select" name="status">
                        <option value="1" <?php echo $mobile_banner->status ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo !$mobile_banner->status ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Banner
                    </button>
                    <a href="<?php echo base_url('admin/mobile_banners'); ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('mbEditImageInput').addEventListener('change', function() {
    const preview = document.getElementById('mbEditPreview');
    preview.innerHTML = '';
    if (this.files && this.files[0]) {
        const file = this.files[0];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail mt-1" style="max-width:320px;max-height:180px;object-fit:cover;">';
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<span class="text-muted"><i class="fas fa-file me-1"></i>' + file.name + '</span>';
        }
    }
});
</script>
