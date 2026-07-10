<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head">
  <h1 class="nb-admin-page-title">Enquiries</h1>
  <p class="nb-admin-page-desc">Messages from interested tenants on your listings. Contact is coordinated by the platform admin.</p>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">Inbox</h2>
    <span class="badge bg-light text-dark border"><?php echo count($enquiries); ?> shown</span>
  </div>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>Property</th>
          <th>Tenant</th>
          <th>Message</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($enquiries)) : ?>
          <tr><td colspan="5" class="text-center text-muted py-5">No enquiries yet.</td></tr>
        <?php else : ?>
          <?php foreach ($enquiries as $e) : ?>
            <tr>
              <td class="fw-medium"><?php echo html_escape($e->property_title); ?></td>
              <td><?php echo html_escape($e->tenant_name); ?></td>
              <td class="small text-muted"><?php echo html_escape(mb_strlen($e->message) > 80 ? mb_substr($e->message, 0, 80) . '…' : $e->message); ?></td>
              <td><span class="badge bg-light text-dark border"><?php echo html_escape($e->status); ?></span></td>
              <td class="small text-nowrap"><?php echo html_escape($e->created_at); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
