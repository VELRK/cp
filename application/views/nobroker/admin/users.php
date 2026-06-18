<?php defined('BASEPATH') OR exit('No direct script access allowed');
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
?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Users</h1>
    <p class="nb-admin-page-desc mb-0">Search users and manage verification status.</p>
  </div>
  <a class="btn btn-danger rounded-pill px-3" href="<?php echo site_url('panel/user/add'); ?>">Add user</a>
</div>

<div class="nb-admin-panel mb-4">
  <form method="get" class="nb-admin-filters">
    <div class="flex-grow-1" style="min-width: 180px;">
      <label class="form-label small text-muted mb-1">Search</label>
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Name or email" value="<?php echo html_escape($filters['q']); ?>">
    </div>
    <div style="min-width: 130px;">
      <label class="form-label small text-muted mb-1">Role</label>
      <select name="role" class="form-select form-select-sm">
        <option value="">Any</option>
        <option value="owner" <?php echo ($filters['role'] === 'owner') ? 'selected' : ''; ?>>Owner</option>
        <option value="tenant" <?php echo ($filters['role'] === 'tenant') ? 'selected' : ''; ?>>Tenant</option>
        <option value="admin" <?php echo ($filters['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
      </select>
    </div>
    <div style="min-width: 130px;">
      <label class="form-label small text-muted mb-1">Status</label>
      <select name="status" class="form-select form-select-sm">
        <option value="">Any</option>
        <option value="approved" <?php echo ($filters['status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
        <option value="rejected" <?php echo ($filters['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
      </select>
    </div>

    <div class="d-flex gap-2 pt-4">
      <button class="btn btn-sm btn-dark px-4" type="submit">Apply</button>
      <a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('panel/users'); ?>">Reset</a>
    </div>
  </form>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Aadhar No</th>
          <th>Aadhar File</th>
          <th>Role</th>
          <th>Status</th>
          <th>Verified</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)) : ?>
        <tr>
          <td colspan="10" class="text-center text-muted py-5">No users match your filters.</td>
        </tr>
        <?php else : ?>
          <?php foreach ($users as $u) : ?>
          <tr>
            <td class="text-muted font-monospace small"><?php echo (int) $u->id; ?></td>
            <td class="fw-medium"><?php echo html_escape($u->name); ?></td>
            <td><?php echo html_escape($u->email); ?></td>
            <td><?php echo html_escape((string) (isset($u->phone) ? $u->phone : '')); ?></td>
            <td>
              <?php if (!empty($u->aadhar_no)) : ?>
                <span class="font-monospace small"><?php echo html_escape($u->aadhar_no); ?></span>
              <?php else : ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($u->aadhar_file)) : ?>
                <a class="btn btn-sm btn-outline-secondary rounded-pill" href="<?php echo base_url($u->aadhar_file); ?>" target="_blank" rel="noopener">View</a>
              <?php else : ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
            <td><span class="nb-admin-badge <?php echo $role_badge($u->user_type); ?>"><?php echo html_escape($u->user_type); ?></span></td>
            <td><span class="nb-admin-badge <?php echo $status_badge($u->status); ?>"><?php echo html_escape($u->status); ?></span></td>
            <td>
              <?php if (isset($u->is_verified) && (int) $u->is_verified === 1) : ?>
                <span class="nb-admin-badge nb-admin-badge-status-approved">Yes</span>
              <?php else : ?>
                <span class="nb-admin-badge nb-admin-badge-status-pending">No</span>
              <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
              <?php if (!isset($u->is_verified) || (int) $u->is_verified !== 1) : ?>
                <button type="button" class="btn btn-sm btn-success rounded-pill nb-verify me-1" data-id="<?php echo (int) $u->id; ?>">Set verified</button>
              <?php endif; ?>
              <a href="<?php echo site_url('panel/user/edit/' . (int) $u->id); ?>" class="btn btn-sm btn-outline-primary rounded-pill me-1">Edit</a>
              <button type="button" class="btn btn-sm btn-outline-danger rounded-pill nb-delete-user" data-id="<?php echo (int) $u->id; ?>" data-name="<?php echo html_escape($u->name); ?>">Delete</button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
function postStatus(id, status) {
  var body = 'user_id=' + encodeURIComponent(id) + '&status=' + encodeURIComponent(status);
  fetch('<?php echo site_url('panel/approve-user'); ?>', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body, credentials: 'same-origin' })
    .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
    .then(function (x) {
      if (x.ok && x.j && x.j.success) {
        location.reload();
        return;
      }
      alert(x.j && x.j.message ? x.j.message : 'Could not update user.');
    })
    .catch(function () { alert('Network error.'); });
}
document.querySelectorAll('.nb-verify').forEach(function (b) {
  b.addEventListener('click', function () {
    var body = 'user_id=' + encodeURIComponent(this.getAttribute('data-id')) + '&verified=1';
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
document.querySelectorAll('.nb-delete-user').forEach(function (b) {
  b.addEventListener('click', function () {
    var id = this.getAttribute('data-id');
    var name = this.getAttribute('data-name');
    if (!confirm('Delete user "' + name + '"? This cannot be undone.')) { return; }
    var f = document.createElement('form');
    f.method = 'POST';
    f.action = '<?php echo site_url('panel/user/delete/'); ?>' + id;
    document.body.appendChild(f);
    f.submit();
  });
});
</script>
