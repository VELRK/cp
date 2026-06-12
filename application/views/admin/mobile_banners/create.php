<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-plus me-2"></i>Add Mobile Banner</h2>
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
                    <label class="form-label fw-semibold">Banner Image <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" name="path" id="mbImageInput" required>
                    <small class="text-muted">Any file type accepted. Recommended: 1080×480px</small>
                    <div id="mbImagePreview" class="mt-2"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select class="form-select" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Banner
                    </button>
                    <a href="<?php echo base_url('admin/mobile_banners'); ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('mbImageInput').addEventListener('change', function() {
    const preview = document.getElementById('mbImagePreview');
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
