<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-4">
  <h1 class="h3 mb-3">Enquiries on your listings</h1>
  <p class="small text-muted">Contact is handled by the platform admin only; you can see activity here.</p>
  <div class="table-responsive"><table class="table table-sm">
    <thead><tr><th>Property</th><th>Tenant</th><th>Message</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
      <?php foreach ($enquiries as $e) : ?>
        <tr>
          <td><?php echo html_escape($e->property_title); ?></td>
          <td><?php echo html_escape($e->tenant_name); ?></td>
          <td><?php echo html_escape(substr($e->message, 0, 80)); ?>…</td>
          <td><?php echo html_escape($e->status); ?></td>
          <td><?php echo html_escape($e->created_at); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
