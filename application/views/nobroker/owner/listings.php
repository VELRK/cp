<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h1 class="nb-admin-page-title">My listings</h1>
    <p class="nb-admin-page-desc mb-0">Manage your published and pending properties.</p>
  </div>
  <a href="<?php echo site_url('owner/property/add'); ?>" class="btn btn-danger rounded-pill px-4"><i class="bi bi-plus-lg me-1"></i>Add property</a>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0 align-middle">
      <thead>
        <tr>
          <th>Title</th>
          <th>Status</th>
          <th>City</th>
          <th>Price</th>
          <th>Views</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($listings)) : ?>
          <tr><td colspan="6" class="text-center text-muted py-5">No listings yet. <a href="<?php echo site_url('owner/property/add'); ?>">Add your first property</a>.</td></tr>
        <?php else : ?>
          <?php foreach ($listings as $p) : ?>
            <?php $pub = !empty($p->is_active); ?>
            <tr>
              <td class="fw-medium"><?php echo html_escape($p->title); ?></td>
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
              <td class="text-end text-nowrap">
                <?php if ($pub) : ?>
                  <a class="btn btn-sm btn-outline-primary rounded-pill" href="<?php echo html_escape(nb_property_url($p)); ?>" target="_blank" rel="noopener">View</a>
                <?php endif; ?>
                <a class="btn btn-sm btn-outline-secondary rounded-pill" href="<?php echo site_url('owner/property/edit/' . (int) $p->id); ?>">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
