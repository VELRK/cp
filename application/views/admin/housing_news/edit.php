<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Housing News</h2>

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
                    <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($item->title); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subtitle</label>
                    <input type="text" class="form-control" name="subtitle" value="<?php echo htmlspecialchars((string) $item->subtitle); ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Author Name</label>
                        <input type="text" class="form-control" name="authorName" value="<?php echo htmlspecialchars((string) $item->authorName); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-control" name="category">
                            <option value="market" <?php echo $item->category === 'market' ? 'selected' : ''; ?>>Market</option>
                            <option value="tips" <?php echo $item->category === 'tips' ? 'selected' : ''; ?>>Tips</option>
                            <option value="legal" <?php echo $item->category === 'legal' ? 'selected' : ''; ?>>Legal</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="8"><?php echo htmlspecialchars((string) $item->description); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Housing News Images (Multiple)</label>
                    <input type="file" class="form-control" id="multi_images" name="multi_images[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
                    <small class="text-muted">New uploads will be added to existing images.</small>
                    <div id="new-image-preview" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>
                <?php
                    $existing_images = array();
                    if (!empty($item->multiImages)) {
                        $decoded = json_decode($item->multiImages, true);
                        if (is_array($decoded)) {
                            $existing_images = $decoded;
                        }
                    }
                ?>
                <?php if (!empty($existing_images)): ?>
                    <div class="mb-3">
                        <label class="form-label">Existing Images</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($existing_images as $img): ?>
                                <?php if (is_string($img) && trim($img) !== ''): ?>
                                    <div class="border rounded p-2">
                                        <a href="<?php echo base_url($img); ?>" target="_blank" rel="noopener" class="d-inline-block mb-2">
                                            <img src="<?php echo base_url($img); ?>" alt="Housing image" style="width:90px;height:90px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                        </a>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remove_existing_images[]" value="<?php echo htmlspecialchars($img); ?>" id="remove_existing_<?php echo md5($img); ?>">
                                            <label class="form-check-label text-danger small" for="remove_existing_<?php echo md5($img); ?>">Remove</label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update
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
