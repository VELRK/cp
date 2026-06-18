<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Feedback</h1>
    <p class="nb-admin-page-desc mb-0">User feedback from <code>/api/mobile/feedback</code>.</p>
  </div>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header d-flex flex-wrap justify-content-between gap-2">
    <h2 class="nb-admin-panel-title mb-0">All feedback</h2>
    <span class="badge bg-light text-dark border"><?php echo count($rows); ?> total</span>
  </div>
  <div class="nb-admin-panel-body p-3">
    <form method="get" class="row g-2 align-items-end mb-3">
      <div class="col-sm-8 col-md-4">
        <label class="form-label">Filter by userId</label>
        <input type="text" class="form-control" name="userId" value="<?php echo html_escape((string) $filter_userId); ?>" placeholder="Enter userId">
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary rounded-pill px-3">Apply</button>
      </div>
      <div class="col-auto">
        <a class="btn btn-outline-secondary rounded-pill px-3" href="<?php echo site_url('panel/feedbacks'); ?>">Reset</a>
      </div>
    </form>
    <div class="nb-admin-table-wrap">
      <table class="table nb-admin-table mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Title</th>
            <th>Description</th>
            <th>Image</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)) : ?>
          <tr><td colspan="6" class="text-center text-muted py-5">No feedback found.</td></tr>
        <?php else : foreach ($rows as $r) : ?>
          <?php
            $uid = trim((string) $r->userId);
            $u = (isset($feedback_users[$uid]) && is_array($feedback_users[$uid])) ? $feedback_users[$uid] : null;
          ?>
          <tr>
            <td class="text-muted font-monospace small">#<?php echo (int) $r->id; ?></td>
            <td style="min-width:220px;">
              <?php if ($u) : ?>
                <div class="fw-semibold"><?php echo html_escape($u['name'] !== '' ? $u['name'] : '-'); ?></div>
                <div class="small text-muted"><?php echo html_escape($u['phone'] !== '' ? $u['phone'] : '-'); ?></div>
                <div class="small text-muted"><?php echo html_escape($u['email'] !== '' ? $u['email'] : '-'); ?></div>
              <?php else : ?>
                <div class="small text-muted">User not found</div>
                <div class="small font-monospace"><?php echo html_escape($uid); ?></div>
              <?php endif; ?>
            </td>
            <td><?php echo html_escape((string) $r->title); ?></td>
            <td style="max-width:380px;"><?php echo html_escape((string) $r->description); ?></td>
            <td>
              <?php if (!empty($r->image)) : ?>
                <a href="<?php echo base_url($r->image); ?>" target="_blank" rel="noopener">
                  <img src="<?php echo base_url($r->image); ?>" alt="Feedback image" style="width:64px;height:64px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                </a>
              <?php else : ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td><?php echo html_escape((string) $r->createdAt); ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
