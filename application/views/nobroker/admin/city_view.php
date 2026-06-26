<?php defined('BASEPATH') OR exit('No direct script access allowed');
$c = isset($row) ? $row : null;
if (!$c) { return; }
?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <nav class="nb-admin-breadcrumb mb-2" aria-label="breadcrumb">
      <a href="<?php echo site_url('panel/cities'); ?>"><i class="bi bi-arrow-left me-1"></i>Cities</a>
      <span class="text-muted"> / </span>
      <span class="text-dark">#<?php echo (int) $c->id; ?></span>
    </nav>
    <h1 class="nb-admin-page-title"><?php echo html_escape($c->name); ?></h1>
    <p class="nb-admin-page-desc mb-0">Read-only city details and usage on the site.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-dark rounded-pill px-3" href="<?php echo html_escape($search_url); ?>" target="_blank" rel="noopener">
      View on site <i class="bi bi-box-arrow-up-right ms-1"></i>
    </a>
    <a class="btn btn-outline-primary rounded-pill px-3" href="<?php echo site_url('panel/city/edit/' . (int) $c->id); ?>">Edit city</a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="nb-admin-panel">
      <div class="nb-admin-panel-body p-4">
        <dl class="nb-admin-dl mb-0">
          <dt>City ID</dt>
          <dd class="font-monospace">#<?php echo (int) $c->id; ?></dd>

          <dt>Name</dt>
          <dd><?php echo html_escape($c->name); ?></dd>

          <dt>State</dt>
          <dd><?php echo html_escape($c->state); ?></dd>

          <dt>Sort order</dt>
          <dd><?php echo (int) $c->sort_order; ?></dd>

          <dt>Active</dt>
          <dd>
            <?php if (!empty($c->is_active)) : ?>
              <span class="nb-admin-badge nb-admin-badge-status-approved">Yes</span>
              <span class="text-muted small ms-1">Shown in dropdowns</span>
            <?php else : ?>
              <span class="nb-admin-badge nb-admin-badge-status-pending">No</span>
            <?php endif; ?>
          </dd>

          <?php if (!empty($c->image)) : ?>
          <dt>City image</dt>
          <dd>
            <img src="<?php echo base_url($c->image); ?>" alt="<?php echo html_escape($c->name); ?>" class="img-thumbnail" style="max-width: 280px;">
          </dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="nb-admin-panel">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title mb-0">Usage</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
        <ul class="list-unstyled mb-0">
          <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span>Properties</span>
            <span class="fw-semibold"><?php echo (int) $property_count; ?></span>
          </li>
          <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span>Users</span>
            <span class="fw-semibold"><?php echo (int) $user_count; ?></span>
          </li>
          <li class="d-flex justify-content-between align-items-center py-2">
            <span>Localities</span>
            <span class="fw-semibold"><?php echo (int) $locality_count; ?></span>
          </li>
        </ul>
        <?php if ((int) $property_count === 0 && (int) $user_count === 0 && (int) $locality_count === 0) : ?>
          <p class="text-muted small mb-0 mt-3">This city is not linked to any listings or accounts yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
