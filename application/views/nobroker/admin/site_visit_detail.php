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
?>
<div class="nb-admin-page-head">
  <nav class="nb-admin-breadcrumb" aria-label="breadcrumb">
    <a href="<?php echo site_url('panel/site-visits'); ?>"><i class="bi bi-arrow-left me-1"></i>Site Visits</a>
    <span class="text-muted"> / </span>
    <span class="text-dark">#<?php echo (int) $v->id; ?></span>
  </nav>
  <h1 class="nb-admin-page-title">Site Visit #<?php echo (int) $v->id; ?></h1>
  <p class="nb-admin-page-desc">Review visit request, confirm or cancel, and update notes.</p>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-6">
    <div class="nb-admin-panel h-100">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title mb-0"><i class="bi bi-building me-2 text-success"></i>Property</h2>
      </div>
      <div class="p-4">
        <?php if ($property) : ?>
          <dl class="nb-admin-dl mb-0">
            <dt>Listing</dt>
            <dd class="mb-3">
              <a href="<?php echo site_url('panel/property/view/' . (int) $property->id); ?>" class="text-decoration-none fw-medium">
                <?php echo html_escape($property->title); ?>
              </a>
              <?php if (!empty($property->is_active)) : ?>
              <a class="small ms-2" href="<?php echo html_escape(nb_property_url($property)); ?>" target="_blank" rel="noopener">View on site</a>
              <?php endif; ?>
            </dd>
            <dt>Locality / City</dt>
            <dd class="mb-3"><?php echo html_escape(trim(($v->locality ?? '') . ($v->city_name ? ', ' . $v->city_name : ''))); ?></dd>
            <dt>Owner</dt>
            <dd class="mb-0"><?php echo html_escape($v->owner_name ?: '—'); ?></dd>
          </dl>
        <?php else : ?>
          <p class="text-muted mb-0">Property not found.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="nb-admin-panel h-100">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title mb-0"><i class="bi bi-person me-2 text-primary"></i>Visitor</h2>
      </div>
      <div class="p-4">
        <dl class="nb-admin-dl mb-0">
          <dt>Name</dt>
          <dd class="mb-3"><?php echo html_escape($v->visitor_name ?: '—'); ?></dd>
          <dt>Phone</dt>
          <dd class="mb-3"><?php echo html_escape($v->visitor_phone ?: '—'); ?></dd>
          <dt>Email</dt>
          <dd class="mb-0"><?php echo $v->visitor_email ? '<a href="mailto:' . html_escape($v->visitor_email) . '">' . html_escape($v->visitor_email) . '</a>' : '—'; ?></dd>
        </dl>
      </div>
    </div>
  </div>
</div>

<div class="nb-admin-panel mb-4">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0"><i class="bi bi-calendar-check me-2"></i>Visit details</h2>
    <span class="badge <?php echo $sv_badge($v->status); ?>"><?php echo html_escape($v->status); ?></span>
  </div>
  <div class="p-4">
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold small text-muted">Scheduled date & time</label>
        <div class="fw-medium"><?php echo html_escape($v->scheduled_at); ?></div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold small text-muted">Requested on</label>
        <div><?php echo html_escape(isset($v->created_at) ? $v->created_at : '—'); ?></div>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label fw-semibold" for="sv-notes">Visitor notes / message</label>
      <textarea id="sv-notes" class="form-control" rows="4" placeholder="Notes from the visitor"><?php echo html_escape(isset($v->notes) ? (string) $v->notes : ''); ?></textarea>
    </div>
    <div class="col-md-4">
      <label class="form-label fw-semibold small" for="sv-status">Status</label>
      <select id="sv-status" class="form-select">
        <?php foreach (array('pending', 'confirmed', 'cancelled', 'completed') as $s) : ?>
          <option value="<?php echo $s; ?>" <?php echo ($v->status === $s) ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mt-4 d-flex flex-wrap gap-2 align-items-center">
      <button type="button" class="btn btn-success px-4 rounded-pill" id="sv-save">
        <i class="bi bi-check2 me-1"></i>Save changes
      </button>
      <button type="button" class="btn btn-outline-danger rounded-pill" id="sv-delete">
        <i class="bi bi-trash me-1"></i>Delete visit
      </button>
      <span class="small text-muted" id="sv-save-hint" aria-live="polite"></span>
    </div>
  </div>
</div>
<script>
(function () {
  var hint = document.getElementById('sv-save-hint');
  var saveBtn = document.getElementById('sv-save');
  var delBtn = document.getElementById('sv-delete');
  function postSave() {
    hint.textContent = 'Saving…';
    saveBtn.disabled = true;
    var body = new URLSearchParams();
    body.set('site_visit_id', '<?php echo (int) $v->id; ?>');
    body.set('status', document.getElementById('sv-status').value);
    body.set('notes', document.getElementById('sv-notes').value);
    fetch('<?php echo site_url('panel/update-site-visit'); ?>', { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        saveBtn.disabled = false;
        if (!j || !j.success) {
          hint.textContent = (j && j.message) ? j.message : 'Save failed.';
          return;
        }
        hint.textContent = 'Saved.';
        setTimeout(function () { hint.textContent = ''; }, 3000);
      })
      .catch(function () {
        saveBtn.disabled = false;
        hint.textContent = 'Save failed. Try again.';
      });
  }
  saveBtn.addEventListener('click', postSave);
  delBtn.addEventListener('click', function () {
    if (!confirm('Delete this site visit permanently?')) {
      return;
    }
    hint.textContent = 'Deleting…';
    delBtn.disabled = true;
    var body = new URLSearchParams();
    body.set('site_visit_id', '<?php echo (int) $v->id; ?>');
    fetch('<?php echo site_url('panel/delete-site-visit'); ?>', { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (!j || !j.success) {
          hint.textContent = 'Could not delete.';
          delBtn.disabled = false;
          return;
        }
        window.location.href = '<?php echo site_url('panel/site-visits'); ?>';
      })
      .catch(function () {
        hint.textContent = 'Delete failed.';
        delBtn.disabled = false;
      });
  });
})();
</script>
