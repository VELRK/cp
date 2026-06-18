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
  <nav class="nb-admin-breadcrumb" aria-label="breadcrumb">
    <a href="<?php echo site_url('panel/enquiries'); ?>"><i class="bi bi-arrow-left me-1"></i>Enquiries</a>
    <span class="text-muted"> / </span>
    <span class="text-dark">#<?php echo (int) $e->id; ?></span>
  </nav>
  <h1 class="nb-admin-page-title">Enquiry #<?php echo (int) $e->id; ?></h1>
  <p class="nb-admin-page-desc">Edit the message and contact details, update workflow status, or remove the record.</p>
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
              <?php if (!empty($property->is_active)) : ?>
              <a href="<?php echo html_escape(nb_property_url($property)); ?>" target="_blank" rel="noopener"><?php echo html_escape($property->title); ?></a>
              <?php else : ?>
              <span class="text-dark"><?php echo html_escape($property->title); ?></span>
              <span class="badge bg-secondary ms-1">Draft</span>
              <a class="small ms-2" href="<?php echo site_url('panel/property/edit/' . (int) $property->id); ?>">Edit listing</a>
              <?php endif; ?>
            </dd>
            <dt>Owner</dt>
            <dd class="mb-0"><?php echo html_escape($property->owner_name); ?></dd>
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
        <h2 class="nb-admin-panel-title mb-0"><i class="bi bi-person me-2 text-primary"></i>Enquirer</h2>
      </div>
      <div class="p-4">
        <?php if ($tenant) : ?>
          <dl class="nb-admin-dl mb-0">
            <dt>Name</dt>
            <dd class="mb-3"><?php echo html_escape($tenant->name); ?></dd>
            <dt>Account email</dt>
            <dd class="mb-0"><a href="mailto:<?php echo html_escape($tenant->email); ?>"><?php echo html_escape($tenant->email); ?></a></dd>
          </dl>
        <?php else : ?>
          <p class="text-muted mb-0">User record missing.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="nb-admin-panel mb-4">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0"><i class="bi bi-chat-quote me-2"></i>Enquiry content</h2>
    <span class="nb-admin-badge <?php echo $eq_badge($e->status); ?>"><?php echo html_escape($e->status); ?></span>
  </div>
  <div class="p-4">
    <div class="mb-3">
      <label class="form-label fw-semibold" for="enq-message">Message</label>
      <textarea id="enq-message" class="form-control" rows="5" required><?php echo html_escape($e->message); ?></textarea>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold" for="enq-phone">Phone (on enquiry)</label>
        <input type="text" id="enq-phone" class="form-control" maxlength="15" value="<?php echo html_escape($e->phone); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold" for="enq-email">Email (on enquiry)</label>
        <input type="email" id="enq-email" class="form-control" maxlength="200" value="<?php echo html_escape($e->email); ?>">
      </div>
    </div>
  </div>
</div>

<div class="nb-admin-panel mb-4">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0"><i class="bi bi-sliders me-2"></i>Admin</h2>
  </div>
  <div class="p-4">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label fw-semibold small" for="enq-status">Status</label>
        <select id="enq-status" class="form-select">
          <?php foreach (array('new','read','responded','closed') as $s) : ?>
            <option value="<?php echo $s; ?>" <?php echo ($e->status === $s) ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-8">
        <label class="form-label fw-semibold small" for="enq-notes">Internal notes</label>
        <textarea id="enq-notes" class="form-control" rows="3" placeholder="Visible only to admins"><?php echo html_escape($e->admin_notes); ?></textarea>
      </div>
    </div>
    <div class="mt-4 d-flex flex-wrap gap-2 align-items-center">
      <button type="button" class="btn btn-success px-4 rounded-pill" id="enq-save">
        <i class="bi bi-check2 me-1"></i>Save changes
      </button>
      <button type="button" class="btn btn-outline-danger rounded-pill" id="enq-delete">
        <i class="bi bi-trash me-1"></i>Delete enquiry
      </button>
      <span class="small text-muted" id="enq-save-hint" aria-live="polite"></span>
    </div>
  </div>
</div>
<script>
(function () {
  var hint = document.getElementById('enq-save-hint');
  var saveBtn = document.getElementById('enq-save');
  var delBtn = document.getElementById('enq-delete');
  function postSave() {
    hint.textContent = 'Saving…';
    saveBtn.disabled = true;
    var body = new URLSearchParams();
    body.set('enquiry_id', '<?php echo (int) $e->id; ?>');
    body.set('status', document.getElementById('enq-status').value);
    body.set('admin_notes', document.getElementById('enq-notes').value);
    body.set('message', document.getElementById('enq-message').value);
    body.set('phone', document.getElementById('enq-phone').value);
    body.set('email', document.getElementById('enq-email').value);
    fetch('<?php echo site_url('panel/update-enquiry'); ?>', { method: 'POST', body: body, credentials: 'same-origin' })
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
    if (!confirm('Delete this enquiry permanently?')) {
      return;
    }
    hint.textContent = 'Deleting…';
    delBtn.disabled = true;
    var body = new URLSearchParams();
    body.set('enquiry_id', '<?php echo (int) $e->id; ?>');
    fetch('<?php echo site_url('panel/delete-enquiry'); ?>', { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (!j || !j.success) {
          hint.textContent = 'Could not delete.';
          delBtn.disabled = false;
          return;
        }
        window.location.href = '<?php echo site_url('panel/enquiries'); ?>';
      })
      .catch(function () {
        hint.textContent = 'Delete failed.';
        delBtn.disabled = false;
      });
  });
})();
</script>
