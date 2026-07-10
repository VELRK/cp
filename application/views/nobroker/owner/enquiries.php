<?php defined('BASEPATH') OR exit('No direct script access allowed');

$status_badge = function ($s) {
  $m = array(
    'new' => 'bg-info text-dark',
    'read' => 'bg-primary',
    'responded' => 'bg-success',
    'closed' => 'bg-secondary',
  );
  return isset($m[$s]) ? $m[$s] : 'bg-secondary';
};

$format_price = function ($price, $listing_type = '') {
  $n = (float) $price;
  if ($n <= 0) {
    return '—';
  }
  $out = '₹' . number_format($n, 0, '.', ',');
  if ($listing_type === 'rent') {
    $out .= ' / month';
  }
  return $out;
};

$format_label = function ($value) {
  $value = trim((string) $value);
  if ($value === '') {
    return '—';
  }
  return ucwords(str_replace('_', ' ', $value));
};

$format_datetime = function ($value) {
  $value = trim((string) $value);
  if ($value === '') {
    return '—';
  }
  $ts = strtotime($value);
  return $ts ? date('d M Y, h:i A', $ts) : $value;
};
?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Enquiries on your properties</h1>
    <p class="nb-admin-page-desc mb-0">Buyer and tenant enquiries with property details, contact information, and full messages.</p>
  </div>
  <span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><?php echo count($enquiries); ?> total</span>
</div>

<?php if (empty($enquiries)) : ?>
  <div class="nb-admin-panel">
    <div class="p-5 text-center text-muted">
      <i class="bi bi-chat-dots display-4 d-block mb-3 opacity-50"></i>
      <p class="mb-0 fw-medium">No enquiries received yet.</p>
      <p class="small mb-3">When someone enquires on your listing, the full details will appear here.</p>
      <a href="<?php echo site_url('owner/listings'); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-4">View your listings</a>
    </div>
  </div>
<?php else : ?>
  <div class="row g-3">
    <?php foreach ($enquiries as $e) : ?>
      <?php
      $property_url = !empty($e->property_slug) ? nb_property_url((object) array('slug' => $e->property_slug)) : '';
      $location = trim(implode(', ', array_filter(array(
        isset($e->locality) ? trim((string) $e->locality) : '',
        isset($e->city_name) ? trim((string) $e->city_name) : '',
      ))));
      ?>
      <div class="col-12">
        <div class="nb-admin-panel overflow-hidden">
          <div class="px-4 py-3 border-bottom bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="badge bg-light text-dark border font-monospace">#<?php echo (int) $e->id; ?></span>
              <span class="badge rounded-pill <?php echo $status_badge($e->status); ?> text-capitalize"><?php echo html_escape($e->status); ?></span>
            </div>
            <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?php echo html_escape($format_datetime($e->created_at)); ?></span>
          </div>
          <div class="p-4">
            <div class="row g-4">
              <div class="col-lg-6">
                <h2 class="h6 text-uppercase text-muted small fw-bold mb-3">Property</h2>
                <div class="mb-2">
                  <?php if ($property_url !== '') : ?>
                    <a href="<?php echo html_escape($property_url); ?>" class="fw-semibold text-decoration-none" target="_blank" rel="noopener">
                      <?php echo html_escape($e->property_title); ?>
                      <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                    </a>
                  <?php else : ?>
                    <span class="fw-semibold"><?php echo html_escape($e->property_title); ?></span>
                  <?php endif; ?>
                </div>
                <ul class="list-unstyled small text-secondary mb-0">
                  <li class="mb-2"><span class="text-muted">Listing ID:</span> <?php echo (int) $e->property_id; ?></li>
                  <?php if ($location !== '') : ?>
                    <li class="mb-2"><i class="bi bi-geo-alt me-1"></i><?php echo html_escape($location); ?></li>
                  <?php endif; ?>
                  <li class="mb-2">
                    <i class="bi bi-tag me-1"></i>
                    <?php echo html_escape($format_label($e->listing_type)); ?>
                    <?php if (!empty($e->property_type)) : ?>
                      · <?php echo html_escape($format_label($e->property_type)); ?>
                    <?php endif; ?>
                  </li>
                  <li class="fw-semibold text-dark"><?php echo html_escape($format_price($e->price, $e->listing_type)); ?></li>
                </ul>
              </div>
              <div class="col-lg-6">
                <h2 class="h6 text-uppercase text-muted small fw-bold mb-3">Enquirer</h2>
                <div class="fw-semibold mb-2">
                  <i class="bi bi-person me-1"></i>
                  <?php echo html_escape($e->tenant_name ?: 'Guest'); ?>
                  <span class="text-muted small fw-normal">(User #<?php echo (int) $e->tenant_id; ?>)</span>
                </div>
                <ul class="list-unstyled small mb-0">
                  <?php if (!empty($e->email)) : ?>
                    <li class="mb-2">
                      <i class="bi bi-envelope me-1 text-muted"></i>
                      <a href="mailto:<?php echo html_escape($e->email); ?>" class="text-decoration-none"><?php echo html_escape($e->email); ?></a>
                      <span class="text-muted">(on enquiry)</span>
                    </li>
                  <?php endif; ?>
                  <?php if (!empty($e->phone)) : ?>
                    <li class="mb-2">
                      <i class="bi bi-telephone me-1 text-muted"></i>
                      <a href="tel:<?php echo html_escape($e->phone); ?>" class="text-decoration-none"><?php echo html_escape($e->phone); ?></a>
                      <span class="text-muted">(on enquiry)</span>
                    </li>
                  <?php endif; ?>
                  <?php if (!empty($e->tenant_email) || !empty($e->tenant_phone)) : ?>
                    <li class="text-muted mt-2 pt-2 border-top">
                      Account:
                      <?php if (!empty($e->tenant_email)) : ?>
                        <a href="mailto:<?php echo html_escape($e->tenant_email); ?>" class="text-decoration-none ms-1"><?php echo html_escape($e->tenant_email); ?></a>
                      <?php endif; ?>
                      <?php if (!empty($e->tenant_email) && !empty($e->tenant_phone)) : ?> · <?php endif; ?>
                      <?php if (!empty($e->tenant_phone)) : ?>
                        <a href="tel:<?php echo html_escape($e->tenant_phone); ?>" class="text-decoration-none"><?php echo html_escape($e->tenant_phone); ?></a>
                      <?php endif; ?>
                    </li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
            <div class="mt-4 pt-3 border-top">
              <h2 class="h6 text-uppercase text-muted small fw-bold mb-2">Message</h2>
              <div class="small text-secondary p-3 bg-light rounded-3 mb-0" style="white-space:pre-line"><?php echo html_escape($e->message ?: '—'); ?></div>
            </div>
            <?php if (!empty($e->updated_at) && $e->updated_at !== $e->created_at) : ?>
              <p class="text-muted small mb-0 mt-3">Last updated: <?php echo html_escape($format_datetime($e->updated_at)); ?></p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
