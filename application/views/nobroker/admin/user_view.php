<?php defined('BASEPATH') OR exit('No direct script access allowed');
$u = isset($user_row) ? $user_row : null;
if (!$u) { return; }
$role_badge = function ($r) {
  $m = array(
    'owner' => 'nb-admin-badge-role-owner',
    'tenant' => 'nb-admin-badge-role-tenant',
    'admin' => 'nb-admin-badge-role-admin',
    'customer' => 'nb-admin-badge-role-customer',
    'agent' => 'nb-admin-badge-role-agent',
  );
  return isset($m[$r]) ? $m[$r] : 'bg-secondary';
};
$status_badge = function ($s) {
  $m = array('approved' => 'nb-admin-badge-status-approved', 'rejected' => 'nb-admin-badge-status-rejected');
  return isset($m[$s]) ? $m[$s] : 'bg-secondary';
};
$kyc_badge = function ($s) {
  $m = array(
    'none' => 'bg-secondary',
    'pending' => 'nb-admin-badge-status-pending',
    'approved' => 'nb-admin-badge-status-approved',
    'rejected' => 'nb-admin-badge-status-rejected',
  );
  return isset($m[$s]) ? $m[$s] : 'bg-secondary';
};
$user_type = isset($u->user_type) ? $u->user_type : (isset($u->role) ? $u->role : '');
$is_agent = strtolower((string) $user_type) === 'agent';
$kyc_status = $is_agent ? nb_agent_kyc_status($u) : 'none';
$kyc_complete = $is_agent ? nb_agent_kyc_complete($u) : false;
$kyc_missing = $is_agent ? nb_agent_kyc_missing($u) : array();
$kyc_is_rejected = $is_agent && $kyc_status === 'rejected';
$kyc_rejection_reason = $kyc_is_rejected ? nb_agent_kyc_rejection_reason($u) : '';
$kyc_missing_labels = array(
  'business_name' => 'Business name',
  'aadhar_no' => 'Aadhaar number',
  'aadhar_file' => 'Aadhaar document',
);
?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <nav class="nb-admin-breadcrumb mb-2" aria-label="breadcrumb">
      <a href="<?php echo site_url('panel/users'); ?>"><i class="bi bi-arrow-left me-1"></i>Users</a>
      <span class="text-muted"> / </span>
      <span class="text-dark">#<?php echo (int) $u->id; ?></span>
    </nav>
    <h1 class="nb-admin-page-title"><?php echo html_escape($u->name); ?></h1>
    <p class="nb-admin-page-desc mb-0">Profile details<?php echo $is_agent ? ' and agent KYC review' : ''; ?>.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-primary rounded-pill px-3" href="<?php echo site_url('panel/user/edit/' . (int) $u->id); ?>">Edit user</a>
    <?php if (!empty($property_count)) : ?>
    <a class="btn btn-outline-dark rounded-pill px-3" href="<?php echo site_url('panel/properties?owner_id=' . (int) $u->id); ?>">View listings (<?php echo (int) $property_count; ?>)</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($is_agent) : ?>
<div class="nb-admin-panel mb-4">
  <div class="nb-admin-panel-body p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <h2 class="h6 mb-0">Agent KYC</h2>
      <span class="nb-admin-badge <?php echo $kyc_badge($kyc_status); ?>"><?php echo html_escape(ucfirst($kyc_status)); ?></span>
    </div>
    <?php if (!empty($kyc_missing)) : ?>
    <div class="alert alert-warning border-0 rounded-3 py-2 small mb-3">
      Missing KYC fields:
      <?php
        $missing_names = array();
        foreach ($kyc_missing as $field) {
          $missing_names[] = isset($kyc_missing_labels[$field]) ? $kyc_missing_labels[$field] : $field;
        }
        echo html_escape(implode(', ', $missing_names));
      ?>
    </div>
    <?php endif; ?>
    <?php if ($kyc_status === 'approved' && !$kyc_complete) : ?>
    <div class="alert alert-warning border-0 rounded-3 py-2 small mb-3">KYC is marked approved but required fields are still missing.</div>
    <?php endif; ?>
    <?php if ($kyc_is_rejected) : ?>
    <p class="text-muted small mb-3">Edit the rejection comment (no email) or approve if corrected.</p>
    <div class="d-flex flex-wrap gap-2">
      <button type="button" class="btn btn-success rounded-pill px-4 nb-kyc-approve" data-id="<?php echo (int) $u->id; ?>">Approve KYC</button>
      <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#nbKycEditRejectionModal">Edit rejection comment</button>
    </div>
    <?php else : ?>
    <p class="text-muted small mb-3"><strong>Reject KYC</strong> requires a reason (emailed when possible). <strong>Approve KYC</strong> verifies the agent.</p>
    <div class="d-flex flex-wrap gap-2">
      <button type="button" class="btn btn-success rounded-pill px-4 nb-kyc-approve" data-id="<?php echo (int) $u->id; ?>">Approve KYC</button>
      <button type="button" class="btn btn-outline-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#nbKycRejectModal">Reject KYC</button>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if (!$kyc_is_rejected) : ?>
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
        <button type="button" class="btn btn-danger rounded-pill nb-kyc-reject" data-id="<?php echo (int) $u->id; ?>">Reject KYC</button>
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
        <label class="form-label" for="nb-kyc-edit-rejection-reason">Rejection comment (shown in app only — no email sent)</label>
        <textarea id="nb-kyc-edit-rejection-reason" class="form-control" rows="4" minlength="5" placeholder="Update the reason shown to the agent…"><?php echo html_escape($kyc_rejection_reason); ?></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dark rounded-pill nb-kyc-edit-rejection" data-id="<?php echo (int) $u->id; ?>">Save comment</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-body p-4">
    <dl class="nb-admin-dl mb-0">
      <dt>User ID</dt>
      <dd class="font-monospace"><?php echo (int) $u->id; ?></dd>

      <dt>Full name</dt>
      <dd><?php echo html_escape($u->name); ?></dd>

      <dt>Email</dt>
      <dd><a href="mailto:<?php echo html_escape($u->email); ?>"><?php echo html_escape($u->email); ?></a></dd>

      <dt>Phone</dt>
      <dd><?php echo html_escape((string) (isset($u->phone) ? $u->phone : '—')); ?></dd>

      <dt>City</dt>
      <dd><?php echo $city_name !== '' ? html_escape($city_name) : '<span class="text-muted">—</span>'; ?></dd>

      <dt>Role / type</dt>
      <dd><span class="nb-admin-badge <?php echo $role_badge($user_type); ?>"><?php echo html_escape($user_type ?: '—'); ?></span></dd>

      <dt>Status</dt>
      <dd><span class="nb-admin-badge <?php echo $status_badge($u->status); ?>"><?php echo html_escape($u->status); ?></span></dd>

      <dt>Verified</dt>
      <dd>
        <?php if (isset($u->is_verified) && (int) $u->is_verified === 1) : ?>
          <span class="nb-admin-badge nb-admin-badge-status-approved">Yes</span>
        <?php else : ?>
          <span class="nb-admin-badge nb-admin-badge-status-pending">No</span>
        <?php endif; ?>
      </dd>

      <?php if ($is_agent) : ?>
      <dt>KYC status</dt>
      <dd><span class="nb-admin-badge <?php echo $kyc_badge($kyc_status); ?>"><?php echo html_escape(ucfirst($kyc_status)); ?></span></dd>

      <?php if (!empty($u->kyc_submitted_at)) : ?>
      <dt>KYC submitted</dt>
      <dd><?php echo html_escape(nb_format_datetime($u->kyc_submitted_at)); ?> <span class="text-muted small">(IST)</span></dd>
      <?php endif; ?>

      <?php if (!empty($u->kyc_reviewed_at)) : ?>
      <dt>KYC reviewed</dt>
      <dd><?php echo html_escape(nb_format_datetime($u->kyc_reviewed_at)); ?> <span class="text-muted small">(IST)</span></dd>
      <?php endif; ?>

      <?php if ($kyc_status === 'rejected' && $kyc_rejection_reason !== '') : ?>
      <dt>Rejection reason</dt>
      <dd class="text-danger" id="nb-kyc-rejection-display"><?php echo nl2br(html_escape($kyc_rejection_reason)); ?></dd>
      <?php endif; ?>
      <?php endif; ?>

      <?php if (!empty($u->aadhar_no)) : ?>
      <dt>Aadhar number</dt>
      <dd class="font-monospace"><?php echo html_escape($u->aadhar_no); ?></dd>
      <?php endif; ?>

      <?php if (!empty($u->business_name)) : ?>
      <dt>Business name</dt>
      <dd><?php echo html_escape($u->business_name); ?></dd>
      <?php endif; ?>

      <?php if (!empty($u->website)) : ?>
      <dt>Website</dt>
      <dd><a href="<?php echo html_escape($u->website); ?>" target="_blank" rel="noopener"><?php echo html_escape($u->website); ?></a></dd>
      <?php endif; ?>

      <?php if (!empty($u->aadhar_file)) : ?>
      <dt>Aadhar document</dt>
      <dd><a class="btn btn-sm btn-outline-secondary rounded-pill" href="<?php echo base_url($u->aadhar_file); ?>" target="_blank" rel="noopener">Open file</a></dd>
      <?php endif; ?>

      <?php if (isset($u->experience_years) && $u->experience_years !== '' && $u->experience_years !== null) : ?>
      <dt>Experience</dt>
      <dd><?php echo html_escape((string) $u->experience_years); ?> years</dd>
      <?php endif; ?>

      <?php if (!empty($u->created_at)) : ?>
      <dt>Registered</dt>
      <dd><?php echo html_escape(nb_format_datetime($u->created_at)); ?> <span class="text-muted small">(IST)</span></dd>
      <?php endif; ?>
    </dl>
  </div>
</div>
<?php if ($is_agent) : ?>
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
