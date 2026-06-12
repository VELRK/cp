<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-4">
  <h1 class="h3 mb-4">My enquiries</h1>
  <?php foreach ($enquiries as $e) : ?>
    <div class="card mb-3">
      <div class="card-body">
        <h2 class="h6"><?php echo html_escape($e->property_title); ?> <span class="badge bg-secondary"><?php echo html_escape($e->city_name); ?></span></h2>
        <p class="mb-1 small"><?php echo nl2br(html_escape($e->message)); ?></p>
        <small class="text-muted"><?php echo html_escape($e->created_at); ?> · Status: <strong><?php echo html_escape($e->status); ?></strong></small>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($enquiries)) : ?>
    <p class="text-muted">You have not sent any enquiries yet.</p>
  <?php endif; ?>
</div>
