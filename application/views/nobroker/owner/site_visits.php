<?php defined('BASEPATH') OR exit('No direct script access allowed');
$status_filter = isset($status_filter) ? (string) $status_filter : '';
$stats = isset($stats) && is_array($stats) ? $stats : array();
$visits = isset($visits) && is_array($visits) ? $visits : array();

$status_badge = function ($s) {
  $m = array(
    'pending' => 'bg-warning text-dark',
    'confirmed' => 'bg-success',
    'cancelled' => 'bg-danger',
    'completed' => 'bg-secondary',
  );
  return isset($m[$s]) ? $m[$s] : 'bg-secondary';
};

$card_class = function ($s) {
  return 'nb-sv-card nb-sv-card--' . preg_replace('/[^a-z]/', '', (string) $s);
};

$initials = function ($name) {
  $name = trim((string) $name);
  if ($name === '') {
    return '?';
  }
  $parts = preg_split('/\s+/', $name);
  $out = strtoupper(substr($parts[0], 0, 1));
  if (count($parts) > 1) {
    $out .= strtoupper(substr($parts[count($parts) - 1], 0, 1));
  }
  return $out;
};
?>
<div class="nb-admin-page-head">
  <h1 class="nb-admin-page-title">Site visit schedule</h1>
  <p class="nb-admin-page-desc mb-0">Review visit requests from tenants on your listings. Approve or reject each request.</p>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="nb-owner-stat-card d-flex align-items-center gap-3">
      <div class="nb-owner-stat-icon nb-owner-stat-icon--muted"><i class="bi bi-calendar3"></i></div>
      <div>
        <div class="nb-owner-stat-value"><?php echo (int) ($stats['total'] ?? 0); ?></div>
        <div class="nb-owner-stat-label">Total requests</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="nb-owner-stat-card d-flex align-items-center gap-3">
      <div class="nb-owner-stat-icon nb-owner-stat-icon--danger"><i class="bi bi-hourglass-split"></i></div>
      <div>
        <div class="nb-owner-stat-value"><?php echo (int) ($stats['pending'] ?? 0); ?></div>
        <div class="nb-owner-stat-label">Pending approval</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="nb-owner-stat-card d-flex align-items-center gap-3">
      <div class="nb-owner-stat-icon nb-owner-stat-icon--success"><i class="bi bi-check-circle"></i></div>
      <div>
        <div class="nb-owner-stat-value"><?php echo (int) ($stats['confirmed'] ?? 0); ?></div>
        <div class="nb-owner-stat-label">Confirmed</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="nb-owner-stat-card d-flex align-items-center gap-3">
      <div class="nb-owner-stat-icon nb-owner-stat-icon--info"><i class="bi bi-flag"></i></div>
      <div>
        <div class="nb-owner-stat-value"><?php echo (int) ($stats['completed'] ?? 0); ?></div>
        <div class="nb-owner-stat-label">Completed</div>
      </div>
    </div>
  </div>
</div>

<div class="nb-sv-filter-pills d-flex flex-wrap gap-2 mb-4">
  <?php
  $filters = array('' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Rejected', 'completed' => 'Completed');
  foreach ($filters as $val => $label) :
    $active = ($status_filter === $val) || ($val === '' && $status_filter === '');
    $href = site_url('owner/site-visits' . ($val !== '' ? '?status=' . urlencode($val) : ''));
  ?>
    <a href="<?php echo $href; ?>" class="btn btn-sm btn-outline-secondary<?php echo $active ? ' active' : ''; ?>"><?php echo html_escape($label); ?></a>
  <?php endforeach; ?>
</div>

<?php if (empty($visits)) : ?>
  <div class="nb-admin-panel">
    <div class="p-5 text-center text-muted">
      <i class="bi bi-calendar-x display-4 d-block mb-3 opacity-50"></i>
      <p class="mb-0 fw-medium">No site visit requests yet.</p>
      <p class="small mb-0">When tenants schedule a visit on your property from the mobile app, they will appear here.</p>
    </div>
  </div>
<?php else : ?>
  <div class="row g-3">
    <?php foreach ($visits as $v) : ?>
      <div class="col-12" id="nb-sv-card-<?php echo (int) $v->id; ?>">
        <div class="<?php echo $card_class($v->status); ?>">
          <div class="p-4">
            <div class="d-flex flex-wrap gap-3 align-items-start">
              <div class="nb-sv-visitor-avatar"><?php echo html_escape($initials($v->visitor_name ?? '')); ?></div>
              <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                  <div>
                    <h2 class="h6 mb-1 fw-semibold"><?php echo html_escape($v->visitor_name ?: 'Visitor'); ?></h2>
                    <div class="small text-muted">
                      <?php if (!empty($v->visitor_phone)) : ?>
                        <a href="tel:<?php echo html_escape($v->visitor_phone); ?>" class="text-decoration-none me-2"><i class="bi bi-telephone me-1"></i><?php echo html_escape($v->visitor_phone); ?></a>
                      <?php endif; ?>
                      <?php if (!empty($v->visitor_email)) : ?>
                        <a href="mailto:<?php echo html_escape($v->visitor_email); ?>" class="text-decoration-none"><i class="bi bi-envelope me-1"></i><?php echo html_escape($v->visitor_email); ?></a>
                      <?php endif; ?>
                    </div>
                  </div>
                  <span class="badge <?php echo $status_badge($v->status); ?> text-capitalize"><?php echo html_escape($v->status === 'cancelled' ? 'rejected' : $v->status); ?></span>
                </div>
                <div class="row g-2 small mb-3">
                  <div class="col-md-6">
                    <span class="text-muted">Property:</span>
                    <span class="fw-medium"><?php echo html_escape($v->property_title ?: '—'); ?></span>
                  </div>
                  <div class="col-md-6">
                    <span class="text-muted">Location:</span>
                    <?php echo html_escape(trim(($v->locality ?? '') . (($v->city_name ?? '') !== '' ? ', ' . $v->city_name : ''))); ?>
                  </div>
                  <div class="col-md-6">
                    <span class="text-muted">Scheduled:</span>
                    <span class="fw-medium text-dark"><i class="bi bi-clock me-1"></i><?php echo html_escape($v->scheduled_at); ?></span>
                  </div>
                  <div class="col-md-6">
                    <span class="text-muted">Requested:</span>
                    <?php echo html_escape(isset($v->created_at) ? $v->created_at : '—'); ?>
                  </div>
                </div>
                <?php if (!empty($v->notes)) : ?>
                  <div class="bg-light rounded-3 p-3 small mb-3">
                    <div class="text-muted mb-1"><i class="bi bi-chat-left-quote me-1"></i>Visitor message</div>
                    <?php echo nl2br(html_escape($v->notes)); ?>
                  </div>
                <?php endif; ?>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                  <?php if ($v->status === 'pending') : ?>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 nb-sv-action" data-id="<?php echo (int) $v->id; ?>" data-status="confirmed">
                      <i class="bi bi-check-lg me-1"></i>Approve
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 nb-sv-action" data-id="<?php echo (int) $v->id; ?>" data-status="cancelled">
                      <i class="bi bi-x-lg me-1"></i>Reject
                    </button>
                  <?php elseif ($v->status === 'confirmed') : ?>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 nb-sv-action" data-id="<?php echo (int) $v->id; ?>" data-status="completed">
                      <i class="bi bi-flag me-1"></i>Mark completed
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 nb-sv-action" data-id="<?php echo (int) $v->id; ?>" data-status="cancelled">
                      <i class="bi bi-x-lg me-1"></i>Cancel
                    </button>
                  <?php endif; ?>
                  <span class="small text-muted nb-sv-hint" id="nb-sv-hint-<?php echo (int) $v->id; ?>" aria-live="polite"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
(function () {
  document.querySelectorAll('.nb-sv-action').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-id');
      var status = btn.getAttribute('data-status');
      var label = status === 'confirmed' ? 'approve' : (status === 'cancelled' ? 'reject' : 'update');
      if (!confirm('Are you sure you want to ' + label + ' this site visit?')) {
        return;
      }
      var hint = document.getElementById('nb-sv-hint-' + id);
      if (hint) {
        hint.textContent = 'Updating…';
      }
      btn.disabled = true;
      var body = new URLSearchParams();
      body.set('site_visit_id', id);
      body.set('status', status);
      fetch('<?php echo site_url('owner/update-site-visit'); ?>', {
        method: 'POST',
        body: body,
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (!j || !j.success) {
            if (hint) {
              hint.textContent = (j && j.message) ? j.message : 'Update failed.';
            }
            btn.disabled = false;
            return;
          }
          window.location.reload();
        })
        .catch(function () {
          if (hint) {
            hint.textContent = 'Update failed.';
          }
          btn.disabled = false;
        });
    });
  });
})();
</script>
