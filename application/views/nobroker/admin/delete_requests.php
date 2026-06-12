<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head">
  <div>
    <h1 class="nb-admin-page-title">Delete Requests</h1>
    <p class="nb-admin-page-desc mb-0">Users who have requested their account to be deleted.</p>
  </div>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">All requests</h2>
    <span class="badge bg-light text-dark border"><?php echo count($requests); ?> total</span>
  </div>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Requested On</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-4">No delete requests found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $req): ?>
            <tr>
              <td><?php echo (int) $req->id; ?></td>
              <td><?php echo html_escape($req->user_name ?: '—'); ?></td>
              <td><?php echo html_escape($req->user_email ?: '—'); ?></td>
              <td><?php echo html_escape($req->user_phone ?: '—'); ?></td>
              <td style="max-width:260px;white-space:pre-wrap;"><?php echo html_escape($req->reason); ?></td>
              <td>
                <?php
                  $badge = $req->status === 'pending' ? 'danger' : ($req->status === 'reviewed' ? 'warning' : 'success');
                ?>
                <span class="badge text-bg-<?php echo $badge; ?>"><?php echo ucfirst($req->status); ?></span>
              </td>
              <td><?php echo date('d M Y, h:i A', strtotime($req->created_at)); ?></td>
              <td>
                <form method="post" action="<?php echo site_url('panel/delete-request/status/' . (int) $req->id); ?>" class="d-flex gap-1 align-items-center">
                  <select name="status" class="form-select form-select-sm" style="min-width:110px;">
                    <option value="pending"  <?php echo $req->status === 'pending'  ? 'selected' : ''; ?>>Pending</option>
                    <option value="reviewed" <?php echo $req->status === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                    <option value="done"     <?php echo $req->status === 'done'     ? 'selected' : ''; ?>>Done</option>
                  </select>
                  <button type="submit" class="btn btn-sm btn-primary">Update</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
