<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">My live updates</h1>
    <a href="<?php echo site_url('user/live-update/add'); ?>" class="btn btn-danger btn-sm">Add update</a>
  </div>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Title</th>
          <th>Status</th>
          <th>Platform</th>
          <th>Live time</th>
          <th>Created</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($rows)) : ?>
        <tr><td colspan="6" class="text-muted text-center">No live updates yet.</td></tr>
      <?php else : foreach ($rows as $r) : ?>
        <tr>
          <td><?php echo html_escape((string) $r->title); ?></td>
          <td><?php echo html_escape((string) (isset($r->status) ? $r->status : 'upcoming')); ?></td>
          <td><?php echo html_escape((string) $r->platform); ?></td>
          <td><?php echo html_escape((string) $r->liveTime); ?></td>
          <td><?php echo html_escape((string) $r->createdAt); ?></td>
          <td class="text-nowrap">
            <a href="<?php echo site_url('user/live-update/edit/' . (int) $r->id); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
            <form action="<?php echo site_url('user/live-update/delete/' . (int) $r->id); ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this live update?');">
              <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

