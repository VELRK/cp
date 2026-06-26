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

<?php
$cityImg = isset($city_image_url) ? $city_image_url : nb_city_image_url(isset($c->image) ? $c->image : null);
$hasDbImage = !empty($c->image);
?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="nb-admin-panel mb-4">
      <div class="nb-admin-panel-header d-flex justify-content-between align-items-center">
        <h2 class="nb-admin-panel-title mb-0">City image preview</h2>
        <?php if (!empty($cityImg)) : ?>
          <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#nbCityImageModal">
            View full size
          </button>
        <?php endif; ?>
      </div>
      <div class="nb-admin-panel-body p-4">
        <?php if (!empty($cityImg)) : ?>
          <div class="d-flex flex-wrap align-items-start gap-4">
            <button type="button" class="btn p-0 border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#nbCityImageModal" title="Click to enlarge">
              <img
                src="<?php echo html_escape($cityImg); ?>"
                alt="<?php echo html_escape($c->name); ?> city image"
                class="img-thumbnail shadow-sm"
                style="width: 220px; height: 220px; object-fit: cover; cursor: zoom-in;"
                id="nbCityViewPreview"
                onerror="this.closest('.d-flex').innerHTML='<div class=\'alert alert-warning mb-0 w-100\'>Image file missing on server. Re-upload from Edit city.</div>';"
              >
            </button>
            <div class="flex-grow-1 min-w-0">
              <p class="fw-semibold mb-1"><?php echo html_escape($c->name); ?></p>
              <p class="text-muted small mb-2">This is how the city image appears in listings and on the site.</p>
              <?php if ($hasDbImage) : ?>
                <dl class="mb-0 small">
                  <dt class="text-muted d-inline">Stored path:</dt>
                  <dd class="d-inline font-monospace ms-1 mb-0"><?php echo html_escape($c->image); ?></dd>
                </dl>
              <?php endif; ?>
              <a class="btn btn-sm btn-outline-primary rounded-pill mt-3" href="<?php echo site_url('panel/city/edit/' . (int) $c->id); ?>">Change image</a>
            </div>
          </div>
        <?php else : ?>
          <div class="text-center text-muted py-4">
            <div class="border rounded bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 220px; height: 220px;">
              <i class="bi bi-image display-4 opacity-50"></i>
            </div>
            <p class="mb-0">No city image uploaded yet.</p>
            <a class="btn btn-sm btn-outline-primary rounded-pill mt-3" href="<?php echo site_url('panel/city/edit/' . (int) $c->id); ?>">Upload image</a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($cityImg)) : ?>
    <div class="modal fade" id="nbCityImageModal" tabindex="-1" aria-labelledby="nbCityImageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="nbCityImageModalLabel"><?php echo html_escape($c->name); ?> — city image</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center p-0 bg-dark">
            <img src="<?php echo html_escape($cityImg); ?>" alt="<?php echo html_escape($c->name); ?>" class="img-fluid w-100" style="max-height: 75vh; object-fit: contain;">
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

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
