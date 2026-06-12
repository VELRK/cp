<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-plus me-2"></i>Create Blog</h2>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Blog Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Author</label>
                        <input type="text" class="form-control" name="author" placeholder="Author name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Short Notes</label>
                    <textarea class="form-control" name="short_notes" rows="3" placeholder="Brief summary/excerpt of the blog"></textarea>
                    <small class="text-muted">This will be displayed as a preview/summary</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea class="form-control" name="description" rows="10" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Gallery Images</label>
                    <input type="file" class="form-control" name="gallery[]" multiple accept="image/*" id="galleryInput">
                    <small class="text-muted">You can select multiple images (Hold Ctrl/Cmd to select multiple)</small>
                    <div id="galleryPreview" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>
                <script>
                    document.getElementById('galleryInput').addEventListener('change', function(e) {
                        const preview = document.getElementById('galleryPreview');
                        preview.innerHTML = '';
                        if (this.files) {
                            Array.from(this.files).forEach(file => {
                                if (file.type.startsWith('image/')) {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        const div = document.createElement('div');
                                        div.className = 'position-relative';
                                        div.innerHTML = `
                                            <img src="${e.target.result}" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                                        `;
                                        preview.appendChild(div);
                                    };
                                    reader.readAsDataURL(file);
                                }
                            });
                        }
                    });
                </script>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Blog
                    </button>
                    <a href="<?php echo base_url('admin/blogs'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

