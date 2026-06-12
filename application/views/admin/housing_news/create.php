<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-plus me-2"></i>Create Housing News</h2>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" class="form-control" name="title" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subtitle</label>
                    <input type="text" class="form-control" name="subtitle">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Author Name</label>
                        <input type="text" class="form-control" name="authorName">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-control" name="category">
                            <option value="market">Market</option>
                            <option value="tips">Tips</option>
                            <option value="legal">Legal</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="8"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Housing News Images (Multiple)</label>
                    <input type="file" class="form-control" id="multi_images" name="multi_images[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
                    <small class="text-muted">You can upload multiple images.</small>
                    <div id="new-image-preview" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create
                    </button>
                    <a href="<?php echo base_url('admin/housing_news'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var input = document.getElementById('multi_images');
    var previewWrap = document.getElementById('new-image-preview');
    if (!input || !previewWrap) {
        return;
    }
    input.addEventListener('change', function () {
        previewWrap.innerHTML = '';
        if (!input.files || !input.files.length) {
            return;
        }
        Array.prototype.forEach.call(input.files, function (file) {
            if (!file.type || file.type.indexOf('image/') !== 0) {
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                var box = document.createElement('div');
                box.className = 'border rounded p-2';
                box.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="width:90px;height:90px;object-fit:cover;border-radius:6px;border:1px solid #ddd;"><div class="small text-muted mt-1" style="max-width:90px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + file.name + '</div>';
                previewWrap.appendChild(box);
            };
            reader.readAsDataURL(file);
        });
    });
})();
</script>
