<?php defined('BASEPATH') OR exit('No direct script access allowed');
$eq_badge = function ($s) {
  $m = array(
    'new' => 'nb-admin-badge-eq-new',
    'read' => 'nb-admin-badge-eq-read',
    'responded' => 'nb-admin-badge-eq-responded',
    'closed' => 'nb-admin-badge-eq-closed',
  );
  return isset($m[$s]) ? $m[$s] : 'bg-secondary';
};
?>
<div class="nb-admin-page-head">
  <h1 class="nb-admin-page-title">Enquiries</h1>
  <p class="nb-admin-page-desc">Messages from interested parties about listings — open a row to update status and notes.</p>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">Inbox</h2>
    <span class="badge bg-light text-dark border"><?php echo count($rows); ?> shown</span>
  </div>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>From</th>
          <th>Property</th>
          <th>City</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)) : ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-5">No enquiries yet.</td>
        </tr>
        <?php else : ?>
          <?php foreach ($rows as $e) : ?>
          <tr id="nb-eq-row-<?php echo (int) $e->id; ?>">
            <td class="text-muted font-monospace small">#<?php echo (int) $e->id; ?></td>
            <td class="fw-medium"><?php echo html_escape($e->tenant_name); ?></td>
            <td class="small"><?php echo html_escape($e->property_title); ?></td>
            <td><?php echo html_escape($e->city_name); ?></td>
            <td><span class="nb-admin-badge <?php echo $eq_badge($e->status); ?>"><?php echo html_escape($e->status); ?></span></td>
            <td class="text-end text-nowrap">
              <a class="btn btn-sm btn-outline-dark rounded-pill px-3" href="<?php echo site_url('panel/enquiry/' . (int) $e->id); ?>">Edit</a>
              <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 nb-eq-del" data-id="<?php echo (int) $e->id; ?>" title="Delete">×</button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<p class="small text-muted" id="nb-eq-list-hint" aria-live="polite"></p>
<script>
(function () {
  var hint = document.getElementById('nb-eq-list-hint');
  document.querySelectorAll('.nb-eq-del').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-id');
      if (!confirm('Delete enquiry #' + id + '?')) {
        return;
      }
      btn.disabled = true;
      hint.textContent = 'Deleting…';
      var body = new URLSearchParams();
      body.set('enquiry_id', id);
      fetch('<?php echo site_url('panel/delete-enquiry'); ?>', { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (!j || !j.success) {
            hint.textContent = 'Could not delete.';
            btn.disabled = false;
            return;
          }
          var row = document.getElementById('nb-eq-row-' + id);
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
