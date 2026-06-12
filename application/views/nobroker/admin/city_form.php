<?php defined('BASEPATH') OR exit('No direct script access allowed');
$is_edit = !empty($edit_id);
$r = $row;
?>
<div class="nb-admin-page-head">
  <nav class="nb-admin-breadcrumb" aria-label="breadcrumb">
    <a href="<?php echo site_url('panel/cities'); ?>"><i class="bi bi-arrow-left me-1"></i>Cities</a>
    <span class="text-muted"> / </span>
    <span class="text-dark"><?php echo $is_edit ? 'Edit' : 'Add'; ?></span>
  </nav>
  <h1 class="nb-admin-page-title"><?php echo $is_edit ? 'Edit city' : 'Add city'; ?></h1>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-body p-4">
    <?php echo form_open_multipart(site_url($is_edit ? 'panel/city/edit/' . (int) $edit_id : 'panel/city/add')); ?>
      <input type="hidden" name="city_id" value="<?php echo (int) $edit_id; ?>">
      <div class="mb-3">
        <label class="form-label fw-semibold" for="nbCityName">City name</label>
        <input type="text" name="name" id="nbCityName" class="form-control nb-admin-input" required maxlength="100"
          value="<?php echo $r ? html_escape($r->name) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold" for="nbCityState">State</label>
        <input type="text" name="state" id="nbCityState" class="form-control nb-admin-input" required maxlength="100"
          value="<?php echo $r ? html_escape($r->state) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold" for="nbCitySort">Sort order</label>
        <input type="number" name="sort_order" id="nbCitySort" class="form-control nb-admin-input" value="<?php echo $r ? (int) $r->sort_order : '0'; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold" for="nbCityImage">City image</label>
        <?php if ($r && !empty($r->image)) : ?>
          <div class="mb-2">
            <img src="<?php echo base_url($r->image); ?>" alt="City image" class="img-thumbnail" style="max-width: 220px;">
          </div>
        <?php endif; ?>
        <input type="file" name="image" id="nbCityImage" class="form-control nb-admin-input" accept="image/*">
        <small class="text-muted">Upload JPG, PNG, WEBP or GIF (max 2MB)</small>
      </div>
      <div class="mb-4 form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="nbCityActive" <?php echo (!$r || !empty($r->is_active)) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="nbCityActive">Active (shown in dropdowns)</label>
      </div>
      <button type="submit" class="btn btn-success rounded-pill px-4">Save</button>
      <a href="<?php echo site_url('panel/cities'); ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
    <?php echo form_close(); ?>
  </div>
</div>
