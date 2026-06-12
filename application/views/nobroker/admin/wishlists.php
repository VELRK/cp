<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Wishlists</h1>
    <p class="nb-admin-page-desc mb-0">Users who saved properties from mobile/web app.</p>
  </div>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">All wishlist items</h2>
    <span class="badge bg-light text-dark border"><?php echo count($rows); ?> total</span>
  </div>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Property ID</th>
          <th>Property</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($rows)) : ?>
        <tr><td colspan="5" class="text-center text-muted py-5">No wishlist records found.</td></tr>
      <?php else : foreach ($rows as $w) : ?>
        <tr>
          <td class="text-muted font-monospace small">#<?php echo (int) $w->id; ?></td>
          <td>
            <div class="fw-bold"><?php echo html_escape((string) ($w->user_name ?? 'Unknown User')); ?></div>
            <div class="small text-muted"><?php echo html_escape((string) ($w->user_email ?? '')); ?></div>
            <div class="small text-muted"><?php echo html_escape((string) ($w->user_phone ?? '')); ?></div>
            <div class="x-small text-muted font-monospace">ID: #<?php echo html_escape((string) $w->user_id); ?></div>
          </td>
          <td><?php echo (int) $w->property_id; ?></td>
          <td><?php echo html_escape((string) $w->property_name); ?></td>
          <td><?php echo html_escape((string) $w->created_at); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

