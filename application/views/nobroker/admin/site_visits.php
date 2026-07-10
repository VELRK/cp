<?php defined('BASEPATH') OR exit('No direct script access allowed');
$sv_badge = function ($s) {
  $m = array(
    'pending' => 'bg-warning text-dark',
    'confirmed' => 'bg-success',
    'cancelled' => 'bg-danger',
    'completed' => 'bg-secondary',
  );
  return isset($m[$s]) ? $m[$s] : 'bg-secondary';
};
$status_filter = isset($status_filter) ? (string) $status_filter : '';
?>
<div class="nb-admin-page-head">
  <h1 class="nb-admin-page-title">Site Visits</h1>
  <p class="nb-admin-page-desc">Property visit schedules requested by tenants/customers from the mobile app.</p>
</div>

<div class="nb-admin-panel mb-3">
  <div class="nb-admin-panel-body p-3">
    <form method="get" action="<?php echo site_url('panel/site-visits'); ?>" class="row g-2 align-items-end">
      <div class="col-auto">
        <label class="form-label fw-semibold small mb-1" for="svStatusFilter">Status</label>
        <select name="status" id="svStatusFilter" class="form-select form-select-sm">
          <option value="">All</option>
          <?php foreach (array('pending', 'confirmed', 'cancelled', 'completed') as $s) : ?>
            <option value="<?php echo $s; ?>" <?php echo $status_filter === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">Filter</button>
        <?php if ($status_filter !== '') : ?>
          <a href="<?php echo site_url('panel/site-visits'); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Clear</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">Scheduled visits</h2>
    <span class="badge bg-light text-dark border"><?php echo count($rows); ?> shown</span>
  </div>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Visitor</th>
          <th>Property</th>
          <th>City</th>
          <th>Scheduled</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)) : ?>
        <tr>
          <td colspan="7" class="text-center text-muted py-5">No site visits yet.</td>
        </tr>
        <?php else : ?>
          <?php foreach ($rows as $v) : ?>
          <tr id="nb-sv-row-<?php echo (int) $v->id; ?>">
            <td class="text-muted font-monospace small">#<?php echo (int) $v->id; ?></td>
            <td class="fw-medium"><?php echo html_escape($v->visitor_name ?: '—'); ?></td>
            <td class="small"><?php echo html_escape($v->property_title ?: '—'); ?></td>
            <td><?php echo html_escape($v->city_name ?: '—'); ?></td>
            <td class="small text-nowrap"><?php echo html_escape($v->scheduled_at); ?></td>
            <td><span class="badge <?php echo $sv_badge($v->status); ?>"><?php echo html_escape($v->status); ?></span></td>
            <td class="text-end text-nowrap">
              <a class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-1" href="<?php echo site_url('panel/site-visit/' . (int) $v->id); ?>">View</a>
              <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 nb-sv-del" data-id="<?php echo (int) $v->id; ?>" title="Delete">×</button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<p class="small text-muted" id="nb-sv-list-hint" aria-live="polite"></p>
<script>
(function () {
  var hint = document.getElementById('nb-sv-list-hint');
  document.querySelectorAll('.nb-sv-del').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-id');
      if (!confirm('Delete site visit #' + id + '?')) {
        return;
      }
      btn.disabled = true;
      hint.textContent = 'Deleting…';
      var body = new URLSearchParams();
      body.set('site_visit_id', id);
      fetch('<?php echo site_url('panel/delete-site-visit'); ?>', { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (!j || !j.success) {
            hint.textContent = 'Could not delete.';
            btn.disabled = false;
            return;
          }
          var row = document.getElementById('nb-sv-row-' + id);
          if (row) {
            row.remove();
          }
          hint.textContent = '';
        })
        .catch(function () {
          hint.textContent = 'Delete failed.';
          btn.disabled = false;
        });
    });
  });
})();
</script>
