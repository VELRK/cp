<?php defined('BASEPATH') OR exit('No direct script access allowed');
$p = isset($p) ? $p : null;
if (!$p) { return; }

$images = array();
if (!empty($p->images)) {
    $decoded = json_decode($p->images, true);
    if (is_array($decoded)) {
        $images = $decoded;
    }
}
$amenities = array();
if (!empty($p->amenities)) {
    $decoded = json_decode($p->amenities, true);
    if (is_array($decoded)) {
        $amenities = $decoded;
    }
}

$pub = !empty($p->is_active);
$price_txt = nb_format_listing_price($p->price, $p->listing_type);
$type_label = nb_property_type_label($p->property_type);
?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <nav class="nb-admin-breadcrumb mb-2" aria-label="breadcrumb">
      <a href="<?php echo site_url('panel/properties'); ?>"><i class="bi bi-arrow-left me-1"></i>Properties</a>
      <span class="text-muted"> / </span>
      <span class="text-dark">#<?php echo (int) $p->id; ?></span>
    </nav>
    <h1 class="nb-admin-page-title"><?php echo html_escape($p->title); ?></h1>
    <p class="nb-admin-page-desc mb-0">Admin read-only summary for this listing.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-primary rounded-pill px-3" href="<?php echo site_url('panel/property/edit/' . (int) $p->id); ?>">Edit property</a>
    <?php if ($pub && !empty($p->slug)) : ?>
    <a class="btn btn-outline-dark rounded-pill px-3" href="<?php echo html_escape(nb_property_url($p)); ?>" target="_blank" rel="noopener">
      View on site <i class="bi bi-box-arrow-up-right ms-1"></i>
    </a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-lg-8">
    <div class="nb-admin-panel h-100">
      <div class="nb-admin-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h2 class="nb-admin-panel-title mb-0">Listing details</h2>
        <?php if ($pub) : ?>
          <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">Published</span>
        <?php else : ?>
          <span class="badge rounded-pill bg-light text-muted border">Draft / hidden</span>
        <?php endif; ?>
      </div>
      <div class="p-4">
        <dl class="nb-admin-dl mb-0">
          <dt>Property ID</dt>
          <dd class="font-monospace"><?php echo (int) $p->id; ?></dd>

          <dt>URL slug</dt>
          <dd class="font-monospace"><?php echo html_escape($p->slug ?: '—'); ?></dd>

          <dt>Listing type</dt>
          <dd><?php echo html_escape(ucfirst((string) $p->listing_type)); ?></dd>

          <dt>Property type</dt>
          <dd><?php echo html_escape($type_label); ?></dd>

          <dt>Price</dt>
          <dd class="fw-semibold">
            <?php echo html_escape($price_txt); ?>
            <?php if (!empty($p->is_price_negotiable)) : ?>
              <span class="badge bg-light text-dark border ms-1">Negotiable</span>
            <?php endif; ?>
          </dd>

          <?php if (!empty($p->bedrooms) || !empty($p->bathrooms) || !empty($p->area_sqft)) : ?>
          <dt>Size</dt>
          <dd>
            <?php
            $parts = array();
            if (!empty($p->bedrooms)) { $parts[] = (int) $p->bedrooms . ' BHK'; }
            if (!empty($p->bathrooms)) { $parts[] = (int) $p->bathrooms . ' bath'; }
            if (!empty($p->area_sqft)) { $parts[] = number_format((int) $p->area_sqft) . ' sqft'; }
            echo html_escape(implode(' · ', $parts));
            ?>
          </dd>
          <?php endif; ?>

          <dt>Location</dt>
          <dd>
            <?php echo html_escape(trim($p->locality . ', ' . $p->city_name, ', ')); ?>
            <?php if (!empty($p->address)) : ?>
              <div class="text-muted small mt-1"><?php echo nl2br(html_escape($p->address)); ?></div>
            <?php endif; ?>
          </dd>

          <?php if (!empty($p->available_from)) : ?>
          <dt>Available from</dt>
          <dd><?php echo html_escape($p->available_from); ?></dd>
          <?php endif; ?>

          <dt>Views</dt>
          <dd><?php echo number_format((int) $p->views); ?></dd>

          <?php if (!empty($p->is_featured)) : ?>
          <dt>Featured</dt>
          <dd><span class="badge bg-warning text-dark">Yes</span></dd>
          <?php endif; ?>

          <?php if (!empty($p->created_at)) : ?>
          <dt>Created</dt>
          <dd><?php echo html_escape($p->created_at); ?></dd>
          <?php endif; ?>

          <?php if (!empty($p->updated_at)) : ?>
          <dt>Last updated</dt>
          <dd><?php echo html_escape($p->updated_at); ?></dd>
          <?php endif; ?>
        </dl>

        <?php if (!empty($p->description)) : ?>
        <div class="mt-4 pt-3 border-top">
          <h3 class="h6 fw-bold text-uppercase text-muted small mb-2">Description</h3>
          <div class="text-secondary small" style="white-space: pre-line;"><?php echo html_escape($p->description); ?></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($amenities)) : ?>
        <div class="mt-4 pt-3 border-top">
          <h3 class="h6 fw-bold text-uppercase text-muted small mb-2">Amenities</h3>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($amenities as $am) : ?>
              <span class="badge rounded-pill bg-light text-dark border"><?php echo html_escape(is_string($am) ? $am : json_encode($am)); ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="nb-admin-panel mb-4">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title mb-0">Owner</h2>
      </div>
      <div class="p-4">
        <dl class="nb-admin-dl mb-0">
          <dt>Name</dt>
          <dd class="mb-2">
            <a href="<?php echo site_url('panel/user/view/' . (int) $p->owner_id); ?>" class="text-decoration-none">
              <?php echo html_escape($p->owner_name ?: '—'); ?>
            </a>
          </dd>
          <dt>Owner ID</dt>
          <dd class="font-monospace mb-2"><?php echo (int) $p->owner_id; ?></dd>
          <?php if (!empty($p->owner_phone)) : ?>
          <dt>Phone</dt>
          <dd><a href="tel:<?php echo html_escape($p->owner_phone); ?>"><?php echo html_escape($p->owner_phone); ?></a></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <?php if (!empty($images)) : ?>
    <div class="nb-admin-panel">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title mb-0">Photos</h2>
        <span class="badge bg-light text-dark border"><?php echo count($images); ?></span>
      </div>
      <div class="p-3">
        <div class="row g-2">
          <?php foreach ($images as $img) : ?>
            <?php $img_url = nb_public_asset_url($img); ?>
            <?php if ($img_url) : ?>
            <div class="col-6">
              <a href="<?php echo html_escape($img_url); ?>" target="_blank" rel="noopener" class="d-block border rounded overflow-hidden">
                <img src="<?php echo html_escape($img_url); ?>" alt="" class="img-fluid w-100" style="aspect-ratio:4/3;object-fit:cover;">
              </a>
            </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($p->video_url)) : ?>
    <div class="nb-admin-panel mt-4">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title mb-0">Video</h2>
      </div>
      <div class="p-4">
        <a href="<?php echo html_escape($p->video_url); ?>" target="_blank" rel="noopener"><?php echo html_escape($p->video_url); ?></a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
