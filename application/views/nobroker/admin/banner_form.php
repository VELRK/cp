<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $isEdit = !empty($edit_id); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title"><?php echo $isEdit ? 'Edit banner' : 'Add banner'; ?></h1>
    <p class="nb-admin-page-desc mb-0">Image + status management for home banner feed.</p>
  </div>
  <a class="btn btn-outline-secondary rounded-pill px-3" href="<?php echo site_url('panel/banners'); ?>">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-body p-3">
    <form method="post" enctype="multipart/form-data" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Status</label>
        <?php $status = isset($row->status) ? strtolower((string) $row->status) : 'inactive'; ?>
        <select class="form-select" name="status">
          <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
          <option value="inactive" <?php echo $status !== 'active' ? 'selected' : ''; ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label"><?php echo $isEdit ? 'Replace image (optional)' : 'Image'; ?></label>
        <input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp,.gif" <?php echo $isEdit ? '' : 'required'; ?>>
      </div>
      <?php if (!empty($row->image)): ?>
        <?php $imgUrl = preg_match('/^https?:\/\//i', (string) $row->image) ? (string) $row->image : base_url((string) $row->image); ?>
        <div class="col-12">
          <label class="form-label">Current image</label><br>
          <img src="<?php echo html_escape($imgUrl); ?>" alt=""
            style="max-width:320px;max-height:160px;object-fit:cover;border-radius:8px;border:1px solid #dbe1ea;">
        </div>
      <?php endif; ?>
      <div class="col-12 d-flex gap-2">
        <button type="submit"
          class="btn btn-primary rounded-pill px-4"><?php echo $isEdit ? 'Update' : 'Create'; ?></button>
        <a class="btn btn-outline-secondary rounded-pill px-4"
          href="<?php echo site_url('panel/banners'); ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>