<?php defined('BASEPATH') OR exit('No direct script access allowed');
$is_edit = !empty($edit_id);
$r = isset($row) ? $row : null;
$is_sub_type = !empty($is_sub_type);
$parent_id = isset($parent_id) ? (int) $parent_id : 0;
$parent_row = isset($parent_row) ? $parent_row : null;
$main_types = isset($main_types) && is_array($main_types) ? $main_types : array();
?>
<div class="nb-admin-page-head">
  <nav class="nb-admin-breadcrumb" aria-label="breadcrumb">
    <a href="<?php echo site_url('panel/property-types'); ?>"><i class="bi bi-arrow-left me-1"></i>Property types</a>
    <span class="text-muted"> / </span>
    <span class="text-dark"><?php echo $is_edit ? 'Edit' : 'Add'; ?></span>
  </nav>
  <h1 class="nb-admin-page-title">
    <?php
    if ($is_edit) {
        echo $is_sub_type ? 'Edit sub type' : 'Edit main type';
    } else {
        echo $is_sub_type ? 'Add sub type' : 'Add main type';
    }
    ?>
  </h1>
  <?php if ($is_sub_type && $parent_row) : ?>
    <p class="nb-admin-page-desc mb-0">Under main type: <strong><?php echo html_escape($parent_row->name); ?></strong></p>
  <?php elseif (!$is_sub_type && !$is_edit) : ?>
    <p class="nb-admin-page-desc mb-0">Main types appear as top-level categories. Add sub types from the list after saving.</p>
  <?php endif; ?>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-body p-4">
    <?php echo form_open_multipart(site_url($is_edit ? 'panel/property-type/edit/' . (int) $edit_id : 'panel/property-type/add')); ?>
      <input type="hidden" name="property_type_id" value="<?php echo (int) $edit_id; ?>">
      <input type="hidden" name="is_sub_type" value="<?php echo $is_sub_type ? '1' : '0'; ?>">

      <?php if ($is_sub_type) : ?>
        <?php if ($parent_row) : ?>
          <input type="hidden" name="parent_id" value="<?php echo (int) $parent_row->id; ?>">
        <?php else : ?>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="nbPtParent">Main type <span class="text-danger">*</span></label>
            <select name="parent_id" id="nbPtParent" class="form-select nb-admin-input" required>
              <option value="">— Select main type —</option>
              <?php foreach ($main_types as $mt) : ?>
                <option value="<?php echo (int) $mt->id; ?>" <?php echo $parent_id === (int) $mt->id ? 'selected' : ''; ?>>
                  <?php echo html_escape($mt->name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label fw-semibold" for="nbPtName">Display name <span class="text-danger">*</span></label>
        <input type="text" name="name" id="nbPtName" class="form-control nb-admin-input" required maxlength="120"
          value="<?php echo $r ? html_escape($r->name) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold" for="nbPtSlug">Slug (API / search key)</label>
        <input type="text" name="slug" id="nbPtSlug" class="form-control nb-admin-input" maxlength="140" placeholder="auto from name"
          value="<?php echo $r ? html_escape($r->slug) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold" for="nbPtSort">Sort order</label>
        <input type="number" name="sort_order" id="nbPtSort" class="form-control nb-admin-input" value="<?php echo $r ? (int) $r->sort_order : '0'; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold" for="nbPtImage">Category image</label>
        <?php if ($r && !empty($r->image)) : ?>
          <div class="mb-2">
            <img src="<?php echo html_escape(base_url($r->image)); ?>" alt="" class="rounded border" style="max-height:80px;max-width:120px;object-fit:cover;">
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="nbPtRemoveImage">
            <label class="form-check-label" for="nbPtRemoveImage">Remove current image</label>
          </div>
        <?php endif; ?>
        <input type="file" name="image" id="nbPtImage" class="form-control nb-admin-input" accept="image/jpeg,image/png,image/webp,image/gif">
        <div class="form-text">Shown in mobile Categories section (API: image_url).</div>
      </div>
      <div class="mb-4 form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="nbPtActive" <?php echo (!$r || !empty($r->is_active)) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="nbPtActive">Active (shown in forms, filters, and API)</label>
      </div>
      <button type="submit" class="btn btn-success rounded-pill px-4">Save</button>
      <a href="<?php echo site_url('panel/property-types'); ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
    <?php echo form_close(); ?>
  </div>
</div>
