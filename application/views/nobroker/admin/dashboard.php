<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head">
  <h1 class="nb-admin-page-title">Dashboard</h1>
  <p class="nb-admin-page-desc">Overview of users, listings, and enquiries.</p>
</div>

<div class="row g-3 g-xl-4 mb-4">
  <div class="col-6 col-lg-3">
    <div class="nb-admin-stat-card">
      <div class="nb-admin-stat-icon users"><i class="bi bi-people-fill"></i></div>
      <div class="nb-admin-stat-value"><?php echo number_format((int) $stats['users']); ?></div>
      <div class="nb-admin-stat-label">Total users</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="nb-admin-stat-card">
      <div class="nb-admin-stat-icon pending"><i class="bi bi-hourglass-split"></i></div>
      <div class="nb-admin-stat-value"><?php echo number_format((int) $stats['pending']); ?></div>
      <div class="nb-admin-stat-label">Unverified users</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="nb-admin-stat-card">
      <div class="nb-admin-stat-icon props"><i class="bi bi-building-fill"></i></div>
      <div class="nb-admin-stat-value"><?php echo number_format((int) $stats['props']); ?></div>
      <div class="nb-admin-stat-label">Properties</div>
      <?php if (!empty($stats['props_pending'])) : ?>
      <a class="small d-block mt-2 text-warning text-decoration-none" href="<?php echo site_url('panel/properties/pending'); ?>"><?php echo (int) $stats['props_pending']; ?> awaiting publication →</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="nb-admin-stat-card">
      <div class="nb-admin-stat-icon enq"><i class="bi bi-envelope-open"></i></div>
      <div class="nb-admin-stat-value"><?php echo number_format((int) $stats['enq_today']); ?></div>
      <div class="nb-admin-stat-label">Enquiries today</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="nb-admin-panel">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title"><i class="bi bi-person-exclamation text-warning me-2"></i>Unverified users</h2>
        <a class="btn btn-sm btn-outline-secondary rounded-pill" href="<?php echo site_url('panel/users?verified=0'); ?>">View all</a>
      </div>
      <?php if (empty($pending_users)) : ?>
        <div class="nb-admin-empty">
          <i class="bi bi-check-circle d-block"></i>
          <p class="mb-0 small">No unverified users.</p>
        </div>
      <?php else : ?>
        <div class="nb-admin-table-wrap">
          <table class="table nb-admin-table mb-0">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pending_users as $u) : ?>
              <tr>
                <td class="fw-medium"><?php echo html_escape($u->name); ?></td>
                <td class="text-muted small"><?php echo html_escape($u->email); ?></td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-success rounded-pill px-3 nb-appr" data-id="<?php echo (int) $u->id; ?>">Verify</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="nb-admin-panel">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title"><i class="bi bi-chat-left-text text-primary me-2"></i>Recent enquiries</h2>
        <a class="btn btn-sm btn-outline-secondary rounded-pill" href="<?php echo site_url('panel/enquiries'); ?>">View all</a>
      </div>
      <?php if (empty($recent_enq)) : ?>
        <div class="nb-admin-empty">
          <i class="bi bi-inbox d-block"></i>
          <p class="mb-0 small">No enquiries yet.</p>
        </div>
      <?php else : ?>
        <div class="nb-admin-table-wrap">
          <table class="table nb-admin-table mb-0">
            <thead>
              <tr>
                <th>Tenant</th>
                <th>Property</th>
                <th class="text-end"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_enq as $e) : ?>
              <tr>
                <td class="fw-medium"><?php echo html_escape($e->tenant_name); ?></td>
                <td class="text-muted small text-truncate" style="max-width:12rem"><?php echo html_escape($e->property_title); ?></td>
                <td class="text-end"><a href="<?php echo site_url('panel/enquiry/' . (int) $e->id); ?>">Open</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
document.querySelectorAll('.nb-appr').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var id = this.getAttribute('data-id');
    var body = 'user_id=' + encodeURIComponent(id) + '&verified=1';
    fetch('<?php echo site_url('panel/approve-user'); ?>', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body, credentials: 'same-origin' })
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
      .then(function (x) {
        if (x.ok && x.j && x.j.success) {
          location.reload();
          return;
        }
        alert(x.j && x.j.message ? x.j.message : 'Could not verify user.');
      })
      .catch(function () { alert('Network error.'); });
  });
});
</script>
