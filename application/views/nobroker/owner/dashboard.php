<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-4">
  <h1 class="h3 mb-4">Owner dashboard</h1>
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card p-3"><div class="h4 mb-0"><?php echo (int) $total_listings; ?></div><small class="text-muted">Listings</small></div></div>
    <div class="col-6 col-md-3"><div class="card p-3"><div class="h4 mb-0"><?php echo (int) $active_listings; ?></div><small class="text-muted">Active</small></div></div>
    <div class="col-6 col-md-3"><div class="card p-3"><div class="h4 mb-0"><?php echo (int) $total_views; ?></div><small class="text-muted">Views</small></div></div>
    <div class="col-6 col-md-3"><div class="card p-3"><div class="h4 mb-0"><?php echo (int) $enquiry_count; ?></div><small class="text-muted">Enquiries (all properties)</small></div></div>
  </div>
  <p><a href="<?php echo site_url('owner/property/add'); ?>" class="btn btn-danger">Add property</a>
     <a href="<?php echo site_url('owner/listings'); ?>" class="btn btn-outline-secondary">My listings</a></p>
  <h2 class="h6">Recent enquiries</h2>
  <div class="table-responsive"><table class="table table-sm">
    <thead><tr><th>Property</th><th>From</th><th>Date</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($recent_enquiries as $e) : ?>
        <tr>
          <td><?php echo html_escape($e->property_title); ?></td>
          <td><?php echo html_escape($e->tenant_name); ?></td>
          <td><?php echo html_escape($e->created_at); ?></td>
          <td><?php echo html_escape($e->status); ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($recent_enquiries)) : ?><tr><td colspan="4" class="text-muted">No enquiries yet.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
