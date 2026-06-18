<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">My wishlist</h1>
    <a href="<?php echo site_url('search'); ?>" class="btn btn-outline-secondary btn-sm">Browse properties</a>
  </div>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Property</th>
          <th>Price</th>
          <th>Location</th>
          <th>Added</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($rows)) : ?>
        <tr><td colspan="5" class="text-muted text-center py-4">No wishlist items yet.</td></tr>
      <?php else : foreach ($rows as $r) : ?>
        <?php
          $imgRaw = isset($r->property_image) ? (string) $r->property_image : '';
          $defaultImg = base_url('assets/img/nb-placeholder-property.svg');
          $imgSrc = $defaultImg;
          if ($imgRaw !== '') {
            $imgSrc = preg_match('/^https?:\/\//i', $imgRaw) ? nb_upgrade_http_image_url($imgRaw) : base_url($imgRaw);
          }
          $name = isset($r->property_name_db) && $r->property_name_db !== null && $r->property_name_db !== ''
            ? (string) $r->property_name_db
            : (string) (isset($r->property_name) ? $r->property_name : ('Property #' . (int) $r->property_id));
          $linkObj = new stdClass();
          $linkObj->id = (int) $r->property_id;
          $linkObj->slug = isset($r->property_slug_db) ? (string) $r->property_slug_db : '';
          $detailUrl = nb_property_url($linkObj);
          $price = isset($r->property_price_db) && $r->property_price_db !== null && $r->property_price_db !== ''
            ? $r->property_price_db
            : (isset($r->property_price) ? $r->property_price : null);
          $locLocality = isset($r->property_locality_db) ? trim((string) $r->property_locality_db) : '';
          $locCity = isset($r->property_city_name_db) ? trim((string) $r->property_city_name_db) : '';
          $location = trim((string) (isset($r->property_location) ? $r->property_location : ''));
          if ($location === '') {
            if ($locLocality !== '' && $locCity !== '') {
              $location = $locLocality . ', ' . $locCity;
            } elseif ($locLocality !== '') {
              $location = $locLocality;
            } elseif ($locCity !== '') {
              $location = $locCity;
            } else {
              $location = '—';
            }
          }
        ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <a href="<?php echo html_escape($detailUrl); ?>" class="flex-shrink-0"><img src="<?php echo html_escape($imgSrc); ?>" alt="<?php echo html_escape($name); ?>" style="width:64px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;"></a>
              <div>
                <div class="fw-semibold"><a href="<?php echo html_escape($detailUrl); ?>" class="text-decoration-none text-dark"><?php echo html_escape($name); ?></a></div>
                <small class="text-muted">ID: <?php echo (int) $r->property_id; ?></small>
              </div>
            </div>
          </td>
          <td><?php echo $price !== null && $price !== '' ? '₹' . number_format((float) $price, 0, '.', ',') : '—'; ?></td>
          <td><?php echo html_escape($location); ?></td>
          <td><?php echo html_escape((string) (isset($r->created_at) ? $r->created_at : '')); ?></td>
          <td class="text-end">
            <form action="<?php echo site_url('user/wishlist/remove/' . (int) $r->property_id); ?>" method="post" class="d-inline" onsubmit="return confirm('Remove from wishlist?');">
              <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

