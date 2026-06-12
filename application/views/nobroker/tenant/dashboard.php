<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-4">
  <h1 class="h3 mb-4">Tenant dashboard</h1>
  <p><a href="<?php echo site_url('search'); ?>" class="btn btn-danger">Search properties</a>
     <a href="<?php echo site_url('tenant/enquiries'); ?>" class="btn btn-outline-secondary">My enquiries</a></p>
  <h2 class="h6">Recent enquiries</h2>
  <ul class="list-group">
    <?php foreach ($enquiries as $e) : ?>
      <li class="list-group-item d-flex justify-content-between">
        <span><?php echo html_escape($e->property_title); ?> — <?php echo html_escape($e->status); ?></span>
        <small class="text-muted"><?php echo html_escape($e->created_at); ?></small>
      </li>
    <?php endforeach; ?>
    <?php if (empty($enquiries)) : ?>
      <li class="list-group-item text-muted">No enquiries yet.</li>
    <?php endif; ?>
  </ul>
</div>
