<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $isEdit = !empty($edit_id); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title"><?php echo $isEdit ? 'Edit blog' : 'Add blog'; ?></h1>
    <p class="nb-admin-page-desc mb-0">Published via <code>/api/blogs</code>, <code>/api/mobile/blogs</code>, and <code>/api/mobile/housing-news</code>.</p>
  </div>
  <a class="btn btn-outline-secondary rounded-pill px-3" href="<?php echo site_url('panel/housing-news'); ?>">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-body p-3">
  <form method="post" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-8">
      <label class="form-label">Title</label>
      <input type="text" class="form-control" name="title" required value="<?php echo html_escape(isset($row->title) ? $row->title : ''); ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Category</label>
      <?php $cat = isset($row->category) ? strtolower((string) $row->category) : 'market'; ?>
      <select class="form-select" name="category">
        <option value="market" <?php echo $cat === 'market' ? 'selected' : ''; ?>>Market</option>
        <option value="tips" <?php echo $cat === 'tips' ? 'selected' : ''; ?>>Tips</option>
        <option value="legal" <?php echo $cat === 'legal' ? 'selected' : ''; ?>>Legal</option>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Subtitle</label>
      <input type="text" class="form-control" name="subtitle" value="<?php echo html_escape(isset($row->subtitle) ? $row->subtitle : ''); ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Author name</label>
      <input type="text" class="form-control" name="authorName" value="<?php echo html_escape(isset($row->authorName) ? $row->authorName : ''); ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Description</label>
      <textarea class="form-control" name="description" rows="8"><?php echo html_escape(isset($row->description) ? $row->description : ''); ?></textarea>
    </div>
    <div class="col-12">
      <label class="form-label">Blog images (multiple)</label>
      <input type="file" class="form-control" id="multi_images" name="multi_images[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
      <div class="form-text">You can select multiple images. On edit, new uploads are added to existing images.</div>
      <div id="new-image-preview" class="d-flex flex-wrap gap-2 mt-3"></div>
      <?php if (!empty($multi_images) && is_array($multi_images)) : ?>
        <div class="d-flex flex-wrap gap-2 mt-3">
          <?php foreach ($multi_images as $img) :
            $imgUrl = nb_public_asset_url($img);
          ?>
            <div class="border rounded p-2">
              <a href="<?php echo html_escape($imgUrl); ?>" target="_blank" rel="noopener" class="text-decoration-none d-inline-block mb-2">
                <img src="<?php echo html_escape($imgUrl); ?>" alt="Blog image" style="width:84px;height:84px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;">
              </a>
              <?php if ($isEdit) : ?>
                <div class="form-check mt-1">
                  <input class="form-check-input" type="checkbox" name="remove_existing_images[]" value="<?php echo html_escape($img); ?>" id="remove_img_<?php echo md5($img); ?>">
                  <label class="form-check-label small text-danger" for="remove_img_<?php echo md5($img); ?>">Remove</label>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="col-12 d-flex gap-2">
      <button type="submit" class="btn btn-primary rounded-pill px-4"><?php echo $isEdit ? 'Update' : 'Create'; ?></button>
      <a class="btn btn-outline-secondary rounded-pill px-4" href="<?php echo site_url('panel/housing-news'); ?>">Cancel</a>
    </div>
  </form>
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
        box.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="width:84px;height:84px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;"><div class="small text-muted mt-1" style="max-width:84px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + file.name + '</div>';
        previewWrap.appendChild(box);
      };
      reader.readAsDataURL(file);
    });
  });
})();
</script>
