<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cities = isset($cities) && is_array($cities) ? $cities : array();
$is_edit = isset($edit_id) && (int) $edit_id > 0;
$u = isset($edit_row) ? $edit_row : null;

function _fv($field, $default = '') {
    $v = set_value($field);
    return $v !== '' ? $v : $default;
}
$city_val    = _fv('city_id',   $u && isset($u->city_id)   ? $u->city_id   : '');
$role_val    = _fv('role',      $u && isset($u->role)       ? $u->role      : 'owner');
$status_val  = _fv('status',    $u && isset($u->status)     ? $u->status    : 'approved');
$utype_val   = _fv('user_type', $u && isset($u->user_type)  ? $u->user_type : 'customer');
$verified_val = isset($_POST['is_verified']) ? (int) $_POST['is_verified']
              : ($u && isset($u->is_verified) ? (int) $u->is_verified : 0);
$has_user_type = isset($u->user_type) || ($is_edit === false);
$ci =& get_instance();
$has_verified = $ci->db->field_exists('is_verified', 'nb_users');
$has_utype    = $ci->db->field_exists('user_type', 'nb_users');
$is_agent_edit = $is_edit && $u && nb_user_is_agent($u);
$kyc_status = $is_agent_edit ? nb_agent_kyc_status($u) : 'none';
$kyc_complete = $is_agent_edit ? nb_agent_kyc_complete($u) : false;
$kyc_can_review = $is_agent_edit && $kyc_complete && $kyc_status === 'pending';
$kyc_is_rejected = $is_agent_edit && $kyc_status === 'rejected';
$kyc_rejection_reason = $kyc_is_rejected ? nb_agent_kyc_rejection_reason($u) : '';
$kyc_show_actions = $kyc_can_review || $kyc_is_rejected;
$kyc_badge = function ($s) {
  $m = array(
    'none' => 'bg-secondary',
    'pending' => 'nb-admin-badge-status-pending',
    'approved' => 'nb-admin-badge-status-approved',
    'rejected' => 'nb-admin-badge-status-rejected',
  );
  return isset($m[$s]) ? $m[$s] : 'bg-secondary';
};
?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <nav class="nb-admin-breadcrumb mb-2" aria-label="breadcrumb">
      <a href="<?php echo site_url('panel/users'); ?>"><i class="bi bi-arrow-left me-1"></i>Users</a>
      <span class="text-muted"> / </span>
      <span class="text-dark"><?php echo $is_edit ? 'Edit' : 'Add'; ?></span>
    </nav>
    <h1 class="nb-admin-page-title"><?php echo $is_edit ? 'Edit user #' . (int) $edit_id : 'Add user'; ?></h1>
    <p class="nb-admin-page-desc mb-0">
      <?php echo $is_edit ? 'Update name, contact, role, status, and password.' : 'Create an owner, tenant, or admin account with an initial password.'; ?>
      <?php if ($is_edit) : ?>
        <a href="<?php echo site_url('panel/user/view/' . (int) $edit_id); ?>" class="ms-2">View full profile</a>
      <?php endif; ?>
    </p>
  </div>
</div>

<?php if ($is_agent_edit) : ?>
<div class="nb-admin-panel mb-4">
  <div class="nb-admin-panel-body p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h6 mb-1">Agent KYC</h2>
        <p class="text-muted small mb-0">Account <strong>Status</strong> below is login access (approved/pending/rejected). KYC approval is separate.</p>
      </div>
      <span class="nb-admin-badge <?php echo $kyc_badge($kyc_status); ?>"><?php echo html_escape(ucfirst($kyc_status)); ?></span>
    </div>
    <dl class="row mb-3 small">
      <?php if (!empty($u->business_name)) : ?>
      <dt class="col-sm-3">Business</dt>
      <dd class="col-sm-9"><?php echo html_escape($u->business_name); ?></dd>
      <?php endif; ?>
      <?php if (!empty($u->aadhar_no)) : ?>
      <dt class="col-sm-3">Aadhaar</dt>
      <dd class="col-sm-9 font-monospace"><?php echo html_escape($u->aadhar_no); ?></dd>
      <?php endif; ?>
      <?php if (!empty($u->aadhar_file)) : ?>
      <dt class="col-sm-3">Document</dt>
      <dd class="col-sm-9"><a class="btn btn-sm btn-outline-secondary rounded-pill" href="<?php echo base_url($u->aadhar_file); ?>" target="_blank" rel="noopener">Open file</a></dd>
      <?php endif; ?>
      <?php if ($kyc_is_rejected && $kyc_rejection_reason !== '') : ?>
      <dt class="col-sm-3 text-danger">Rejection reason</dt>
      <dd class="col-sm-9 text-danger"><?php echo nl2br(html_escape($kyc_rejection_reason)); ?></dd>
      <?php endif; ?>
    </dl>
    <?php if (!$kyc_complete) : ?>
    <div class="alert alert-secondary border-0 rounded-3 mb-0 py-2 small">Agent has not submitted complete KYC yet (business name, Aadhaar number, and document).</div>
    <?php elseif ($kyc_can_review) : ?>
    <p class="text-muted small mb-3">Reject sends email to the agent (when a real email is on file). Use Approve KYC to verify the agent.</p>
    <div class="d-flex flex-wrap gap-2">
      <button type="button" class="btn btn-success rounded-pill px-4 nb-kyc-approve" data-id="<?php echo (int) $edit_id; ?>">Approve KYC</button>
      <button type="button" class="btn btn-outline-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#nbKycRejectModal">Reject KYC</button>
    </div>
    <?php elseif ($kyc_is_rejected) : ?>
    <p class="text-muted small mb-3">Edit the comment shown in the app without sending email again, or approve if corrected.</p>
    <div class="d-flex flex-wrap gap-2">
      <button type="button" class="btn btn-success rounded-pill px-4 nb-kyc-approve" data-id="<?php echo (int) $edit_id; ?>">Approve KYC</button>
      <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#nbKycEditRejectionModal">Edit rejection comment</button>
    </div>
    <?php elseif ($kyc_status === 'approved') : ?>
    <div class="alert alert-success border-0 rounded-3 mb-0 py-2 small">Agent KYC is approved.</div>
    <?php endif; ?>
  </div>
</div>

<?php if ($kyc_can_review) : ?>
<div class="modal fade" id="nbKycRejectModal" tabindex="-1" aria-labelledby="nbKycRejectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="nbKycRejectModalLabel">Reject agent KYC</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label class="form-label" for="nb-kyc-reject-reason">Rejection reason (shown in app and emailed to agent)</label>
        <textarea id="nb-kyc-reject-reason" class="form-control" rows="4" minlength="5" placeholder="Explain what needs to be corrected…"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger rounded-pill nb-kyc-reject" data-id="<?php echo (int) $edit_id; ?>">Reject KYC</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($kyc_is_rejected) : ?>
<div class="modal fade" id="nbKycEditRejectionModal" tabindex="-1" aria-labelledby="nbKycEditRejectionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="nbKycEditRejectionModalLabel">Edit rejection comment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label class="form-label" for="nb-kyc-edit-rejection-reason">Rejection comment (app only — no email)</label>
        <textarea id="nb-kyc-edit-rejection-reason" class="form-control" rows="4" minlength="5"><?php echo html_escape($kyc_rejection_reason); ?></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dark rounded-pill nb-kyc-edit-rejection" data-id="<?php echo (int) $edit_id; ?>">Save comment</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-body p-4">
    <?php echo validation_errors('<div class="alert alert-danger border-0 rounded-3 mb-4">', '</div>'); ?>
    <?php
    $action = $is_edit ? site_url('panel/user/edit/' . (int) $edit_id) : site_url('panel/user/add');
    echo form_open($action);
    ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbUsName">Full name</label>
          <input type="text" name="name" id="nbUsName" class="form-control nb-admin-input" required maxlength="150"
            value="<?php echo html_escape(_fv('name', $u ? $u->name : '')); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbUsEmail">Email</label>
          <input type="email" name="email" id="nbUsEmail" class="form-control nb-admin-input" required maxlength="200"
            value="<?php echo html_escape(_fv('email', $u ? $u->email : '')); ?>" autocomplete="off">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbUsPhone">Phone</label>
          <input type="text" name="phone" id="nbUsPhone" class="form-control nb-admin-input" required maxlength="15"
            value="<?php echo html_escape(_fv('phone', $u && isset($u->phone) ? $u->phone : '')); ?>" autocomplete="off">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbUsCity">City (optional)</label>
          <select name="city_id" id="nbUsCity" class="form-select nb-admin-input">
            <option value="">—</option>
            <?php foreach ($cities as $c) : ?>
              <option value="<?php echo (int) $c->id; ?>" <?php echo ((string) $c->id === (string) $city_val) ? 'selected' : ''; ?>><?php echo html_escape($c->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbUsRole">Role</label>
          <select name="role" id="nbUsRole" class="form-select nb-admin-input" required>
            <?php foreach (array('owner', 'tenant', 'admin') as $r) : ?>
              <option value="<?php echo $r; ?>" <?php echo ($role_val === $r) ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if ($has_utype) : ?>
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbUsType">User type</label>
          <select name="user_type" id="nbUsType" class="form-select nb-admin-input">
            <option value="customer" <?php echo ($utype_val === 'customer') ? 'selected' : ''; ?>>Customer</option>
            <option value="agent" <?php echo ($utype_val === 'agent') ? 'selected' : ''; ?>>Agent</option>
          </select>
        </div>
        <?php endif; ?>
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbUsStatus">Status</label>
          <?php if ($is_edit) : ?>
            <select name="status" id="nbUsStatus" class="form-select nb-admin-input" required>
              <?php foreach (array('approved', 'pending', 'rejected') as $s) : ?>
                <option value="<?php echo $s; ?>" <?php echo ($status_val === $s) ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
              <?php endforeach; ?>
            </select>
          <?php else : ?>
            <input type="hidden" name="status" value="approved">
            <input type="text" class="form-control nb-admin-input" value="approved" readonly>
            <div class="form-text">New users are always created as approved.</div>
          <?php endif; ?>
        </div>
        <?php if ($is_edit && $has_verified) : ?>
        <div class="col-md-6 d-flex align-items-center gap-2 pt-4">
          <?php if ($is_agent_edit) : ?>
          <div class="form-text">
            <span class="nb-admin-badge <?php echo $kyc_badge($kyc_status); ?> me-1"><?php echo html_escape(ucfirst($kyc_status)); ?></span>
            Agent verification is controlled by <strong>Approve KYC</strong> above<?php echo (int) ($u->is_verified ?? 0) === 1 ? ' (currently verified)' : ''; ?>.
          </div>
          <?php else : ?>
          <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" name="is_verified" id="nbUsVerified" value="1" <?php echo $verified_val ? 'checked' : ''; ?>>
            <label class="form-check-label fw-semibold" for="nbUsVerified">Verified</label>
          </div>
          <span class="text-muted small">Mark user as identity-verified</span>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbUsPw">
            Password<?php echo $is_edit ? ' <span class="text-muted fw-normal small">(leave blank to keep current)</span>' : ''; ?>
          </label>
          <input type="password" name="password" id="nbUsPw" class="form-control nb-admin-input"
            <?php echo $is_edit ? '' : 'required'; ?> minlength="6" autocomplete="new-password">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbUsPw2">Confirm password</label>
          <input type="password" name="password2" id="nbUsPw2" class="form-control nb-admin-input"
            <?php echo $is_edit ? '' : 'required'; ?> minlength="6" autocomplete="new-password">
        </div>
      </div>
      <div class="mt-4">
        <button type="submit" class="btn btn-success rounded-pill px-4"><?php echo $is_edit ? 'Save changes' : 'Create user'; ?></button>
        <a href="<?php echo site_url('panel/users'); ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
      </div>
    <?php echo form_close(); ?>
  </div>
</div>
<?php if ($kyc_show_actions) : ?>
<script>
(function () {
  function postKyc(url, body, okMsg) {
    fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body,
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
      .then(function (x) {
        if (x.ok && x.j && x.j.success) {
          location.reload();
          return;
        }
        alert(x.j && x.j.message ? x.j.message : (okMsg || 'Could not update KYC.'));
      })
      .catch(function () { alert('Network error.'); });
  }
  document.querySelectorAll('.nb-kyc-approve').forEach(function (b) {
    b.addEventListener('click', function () {
      if (!confirm('Approve this agent\'s KYC?')) { return; }
      postKyc('<?php echo site_url('panel/user/kyc-approve'); ?>', 'user_id=' + encodeURIComponent(this.getAttribute('data-id')), 'KYC approved.');
    });
  });
  var rejectBtn = document.querySelector('.nb-kyc-reject');
  if (rejectBtn) {
    rejectBtn.addEventListener('click', function () {
      var reasonEl = document.getElementById('nb-kyc-reject-reason');
      var reason = reasonEl ? reasonEl.value.trim() : '';
      if (reason.length < 5) {
        alert('Please enter a rejection reason (at least 5 characters).');
        return;
      }
      postKyc(
        '<?php echo site_url('panel/user/kyc-reject'); ?>',
        'user_id=' + encodeURIComponent(this.getAttribute('data-id')) + '&reason=' + encodeURIComponent(reason),
        'KYC rejected.'
      );
    });
  }
  var editBtn = document.querySelector('.nb-kyc-edit-rejection');
  if (editBtn) {
    editBtn.addEventListener('click', function () {
      var reasonEl = document.getElementById('nb-kyc-edit-rejection-reason');
      var reason = reasonEl ? reasonEl.value.trim() : '';
      if (reason.length < 5) {
        alert('Please enter a rejection comment (at least 5 characters).');
        return;
      }
      postKyc(
        '<?php echo site_url('panel/user/kyc-rejection-edit'); ?>',
        'user_id=' + encodeURIComponent(this.getAttribute('data-id')) + '&reason=' + encodeURIComponent(reason),
        'Rejection comment updated.'
      );
    });
  }
})();
</script>
<?php endif; ?>
