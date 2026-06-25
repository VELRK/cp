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
$user_type = isset($u->user_type) ? $u->user_type : (isset($u->role) ? $u->role : '');
?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <nav class="nb-admin-breadcrumb mb-2" aria-label="breadcrumb">
      <a href="<?php echo site_url('panel/users'); ?>"><i class="bi bi-arrow-left me-1"></i>Users</a>
      <span class="text-muted"> / </span>
      <span class="text-dark">#<?php echo (int) $u->id; ?></span>
    </nav>
    <h1 class="nb-admin-page-title"><?php echo html_escape($u->name); ?></h1>
    <p class="nb-admin-page-desc mb-0">Read-only profile details for this account.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-primary rounded-pill px-3" href="<?php echo site_url('panel/user/edit/' . (int) $u->id); ?>">Edit user</a>
    <?php if (!empty($property_count)) : ?>
    <a class="btn btn-outline-dark rounded-pill px-3" href="<?php echo site_url('panel/properties?owner_id=' . (int) $u->id); ?>">View listings (<?php echo (int) $property_count; ?>)</a>
    <?php endif; ?>
  </div>
</div>

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

      <?php if (!empty($u->aadhar_no)) : ?>
      <dt>Aadhar number</dt>
      <dd class="font-monospace"><?php echo html_escape($u->aadhar_no); ?></dd>
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
      <dd><?php echo html_escape($u->created_at); ?></dd>
      <?php endif; ?>
    </dl>
  </div>
</div>
