<?php defined('BASEPATH') OR exit('No direct script access allowed');
$is_edit = !empty($edit_id);
$r = $row;
?>
<div class="nb-admin-page-head">
  <nav class="nb-admin-breadcrumb" aria-label="breadcrumb">
    <a href="<?php echo site_url('panel/property-types'); ?>"><i class="bi bi-arrow-left me-1"></i>Property types</a>
    <span class="text-muted"> / </span>
    <span class="text-dark"><?php echo $is_edit ? 'Edit' : 'Add'; ?></span>
  </nav>
  <h1 class="nb-admin-page-title"><?php echo $is_edit ? 'Edit property type' : 'Add property type'; ?></h1>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-body p-4">
    <?php echo form_open(site_url($is_edit ? 'panel/property-type/edit/' . (int) $edit_id : 'panel/property-type/add')); ?>
      <input type="hidden" name="property_type_id" value="<?php echo (int) $edit_id; ?>">
      <div class="mb-3">
        <label class="form-label fw-semibold" for="nbPtName">Display name</label>
        <input type="text" name="name" id="nbPtName" class="form-control nb-admin-input" required maxlength="120"
          value="<?php echo $r ? html_escape($r->name) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold" for="nbPtSlug">Slug (used in API/search)</label>
        <input type="text" name="slug" id="nbPtSlug" class="form-control nb-admin-input" maxlength="140" placeholder="auto from name"
          value="<?php echo $r ? html_escape($r->slug) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold" for="nbPtSort">Sort order</label>
        <input type="number" name="sort_order" id="nbPtSort" class="form-control nb-admin-input" value="<?php echo $r ? (int) $r->sort_order : '0'; ?>">
      </div>
      <div class="mb-4 form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="nbPtActive" <?php echo (!$r || !empty($r->is_active)) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="nbPtActive">Active (available in listing forms and filters)</label>
      </div>
      <button type="submit" class="btn btn-success rounded-pill px-4">Save</button>
      <a href="<?php echo site_url('panel/property-types'); ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
    <?php echo form_close(); ?>
  </div>
</div>

