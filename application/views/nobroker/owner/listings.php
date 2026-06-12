<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">My listings</h1>
    <a href="<?php echo site_url('owner/property/add'); ?>" class="btn btn-danger btn-sm">Add</a>
  </div>
  <div class="table-responsive"><table class="table align-middle">
    <thead><tr><th>Title</th><th>Status</th><th>City</th><th>Price</th><th>Views</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($listings as $p) : ?>
        <?php $pub = !empty($p->is_active); ?>
        <tr>
          <td><?php echo html_escape($p->title); ?></td>
          <td>
            <?php if ($pub) : ?>
              <span class="badge text-bg-success rounded-pill">Published</span>
            <?php else : ?>
              <span class="badge text-bg-warning text-dark rounded-pill">Pending admin</span>
            <?php endif; ?>
          </td>
          <td><?php echo html_escape($p->city_name); ?></td>
          <td>₹<?php echo number_format((float) $p->price); ?></td>
          <td><?php echo (int) $p->views; ?></td>
          <td class="text-nowrap">
            <?php if ($pub) : ?>
              <a class="btn btn-sm btn-outline-primary" href="<?php echo html_escape(nb_property_url($p)); ?>">View</a>
            <?php else : ?>
              <span class="btn btn-sm btn-outline-secondary disabled" title="Not on the public site until an admin publishes it">Public view</span>
            <?php endif; ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('owner/property/edit/' . (int) $p->id); ?>">Edit</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
