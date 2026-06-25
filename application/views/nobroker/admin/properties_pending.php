<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head">
  <h1 class="nb-admin-page-title">Property approvals</h1>
  <p class="nb-admin-page-desc">Draft listings are hidden from search and public URLs until you publish them here or from the full editor.</p>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">Awaiting publication</h2>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <span class="badge bg-light text-dark border"><?php echo count($rows); ?> pending</span>
      <a class="btn btn-sm btn-outline-secondary rounded-pill" href="<?php echo site_url('panel/properties'); ?>">All properties</a>
      <a class="btn btn-sm btn-danger rounded-pill" href="<?php echo site_url('panel/property/add'); ?>">Add property</a>
    </div>
  </div>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Owner</th>
          <th>City</th>
          <th class="text-end">Price</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)) : ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-5">No listings waiting for approval.</td>
        </tr>
        <?php else : ?>
          <?php foreach ($rows as $p) : ?>
          <tr id="nb-pend-row-<?php echo (int) $p->id; ?>">
            <td class="text-muted font-monospace small"><?php echo (int) $p->id; ?></td>
            <td class="fw-medium"><?php echo html_escape($p->title); ?></td>
            <td class="small"><?php echo html_escape($p->owner_name); ?></td>
            <td><?php echo html_escape($p->city_name); ?></td>
            <td class="text-end">₹<?php echo number_format((float) $p->price); ?></td>
            <td class="text-end text-nowrap">
              <button type="button" class="btn btn-sm btn-success rounded-pill px-3 nb-pub-approve" data-id="<?php echo (int) $p->id; ?>">Publish</button>
              <a class="btn btn-sm btn-outline-dark rounded-pill px-3" href="<?php echo site_url('panel/property/edit/' . (int) $p->id); ?>">Edit</a>
              <?php echo form_open(site_url('panel/property/delete/' . (int) $p->id), array('class' => 'd-inline', 'onsubmit' => "return confirm('Delete this property permanently?');")); ?>
              <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Delete</button>
              <?php echo form_close(); ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<p class="small text-muted mt-3 mb-0" id="nb-pend-hint" aria-live="polite"></p>
<script>
(function () {
  var hint = document.getElementById('nb-pend-hint');
  document.querySelectorAll('.nb-pub-approve').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-id');
      btn.disabled = true;
      hint.textContent = 'Publishing…';
      var body = new URLSearchParams();
      body.set('property_id', id);
      fetch('<?php echo site_url('panel/approve-property'); ?>', { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (!j || !j.success) {
            hint.textContent = 'Could not publish. Try again.';
            btn.disabled = false;
            return;
          }
          var row = document.getElementById('nb-pend-row-' + id);
          if (row) {
            row.remove();
          }
          hint.textContent = 'Listing #' + id + ' is now live.';
          setTimeout(function () { hint.textContent = ''; }, 4000);
        })
        .catch(function () {
          hint.textContent = 'Network error.';
          btn.disabled = false;
        });
    });
  });
})();
</script>
