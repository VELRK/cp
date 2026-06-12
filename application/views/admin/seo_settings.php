<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-sliders me-2"></i>SEO Settings</h2>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo base_url('admin/seo_settings_save'); ?>">
        <?php foreach ($seo_pages as $page): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2 text-primary"></i>
                    <?php echo htmlspecialchars($page['page_name'], ENT_QUOTES, 'UTF-8'); ?>
                    <small class="text-muted ms-2">(<?php echo htmlspecialchars($page['page_key'], ENT_QUOTES, 'UTF-8'); ?>)</small>
                </h5>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="pages[<?php echo $page['id']; ?>][status]"
                        id="status_<?php echo $page['id']; ?>"
                        <?php echo $page['status'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="status_<?php echo $page['id']; ?>">Active</label>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Meta Title <small class="text-muted">(max 70 chars)</small></label>
                        <input type="text" class="form-control meta-title-input"
                            name="pages[<?php echo $page['id']; ?>][meta_title]"
                            value="<?php echo htmlspecialchars($page['meta_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="70"
                            placeholder="Page title for search engines">
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Recommended: 50–70 characters</small>
                            <small class="char-count text-muted"><span class="count">0</span>/70</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Canonical URL</label>
                        <input type="url" class="form-control"
                            name="pages[<?php echo $page['id']; ?>][canonical_url]"
                            value="<?php echo htmlspecialchars($page['canonical_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="https://yourdomain.com/page">
                        <small class="text-muted">Leave blank to use the current page URL</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Meta Description <small class="text-muted">(max 170 chars)</small></label>
                    <textarea class="form-control meta-desc-input"
                        name="pages[<?php echo $page['id']; ?>][meta_description]"
                        rows="2" maxlength="170"
                        placeholder="Brief description for search result snippets"><?php echo htmlspecialchars($page['meta_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">Recommended: 150–170 characters</small>
                        <small class="char-count text-muted"><span class="count">0</span>/170</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Meta Keywords</label>
                    <input type="text" class="form-control"
                        name="pages[<?php echo $page['id']; ?>][meta_keywords]"
                        value="<?php echo htmlspecialchars($page['meta_keywords'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="keyword1, keyword2, keyword3">
                    <small class="text-muted">Comma-separated keywords (optional)</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">OG Title <small class="text-muted">(Social Media)</small></label>
                        <input type="text" class="form-control"
                            name="pages[<?php echo $page['id']; ?>][og_title]"
                            value="<?php echo htmlspecialchars($page['og_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="70"
                            placeholder="Title shown when shared on social media">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">OG Description <small class="text-muted">(Social Media)</small></label>
                        <input type="text" class="form-control"
                            name="pages[<?php echo $page['id']; ?>][og_description]"
                            value="<?php echo htmlspecialchars($page['og_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="200"
                            placeholder="Description shown when shared on social media">
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="d-flex gap-2 mb-5">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>Save SEO Settings
            </button>
            <a href="<?php echo base_url('admin/dashboard'); ?>" class="btn btn-secondary px-4">Cancel</a>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.meta-title-input, .meta-desc-input').forEach(function(input) {
    var maxLen = parseInt(input.getAttribute('maxlength'));
    var counter = input.closest('.mb-3').querySelector('.count');
    function update() {
        var len = input.value.length;
        counter.textContent = len;
        counter.parentElement.className = 'char-count text-muted';
        if (len > maxLen * 0.9) counter.parentElement.className = 'char-count text-warning';
        if (len >= maxLen) counter.parentElement.className = 'char-count text-danger';
    }
    input.addEventListener('input', update);
    update();
});
</script>
