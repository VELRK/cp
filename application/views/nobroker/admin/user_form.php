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
    </p>
  </div>
</div>

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
          <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" name="is_verified" id="nbUsVerified" value="1" <?php echo $verified_val ? 'checked' : ''; ?>>
            <label class="form-check-label fw-semibold" for="nbUsVerified">Verified</label>
          </div>
          <span class="text-muted small">Mark user as identity-verified</span>
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
