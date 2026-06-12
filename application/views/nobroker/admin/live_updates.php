<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Live updates</h1>
    <p class="nb-admin-page-desc mb-0">Updates powering <code>/api/mobile/live-updates</code>.</p>
  </div>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">All live updates</h2>
    <span class="badge bg-light text-dark border"><?php echo count($rows); ?> total</span>
  </div>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Status</th>
          <th>Platform</th>
          <th>Live time</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($rows)) : ?>
        <tr><td colspan="7" class="text-center text-muted py-5">No live updates found.</td></tr>
      <?php else : foreach ($rows as $r) : ?>
        <tr>
          <td class="text-muted font-monospace small">#<?php echo (int) $r->id; ?></td>
          <td><?php echo html_escape((string) $r->title); ?></td>
          <td><?php echo html_escape((string) (isset($r->status) ? $r->status : 'upcoming')); ?></td>
          <td><?php echo html_escape((string) $r->platform); ?></td>
          <td><?php echo html_escape((string) $r->liveTime); ?></td>
          <td><?php echo html_escape((string) $r->createdAt); ?></td>
          <td class="text-nowrap">
            <a href="<?php echo site_url('panel/live-update/edit/' . (int) $r->id); ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">Edit</a>
            <?php echo form_open(site_url('panel/live-update/delete/' . (int) $r->id), array('class' => 'd-inline', 'onsubmit' => "return confirm('Delete this live update?');")); ?>
              <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Delete</button>
            <?php echo form_close(); ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

