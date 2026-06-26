<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Cities</h1>
    <p class="nb-admin-page-desc mb-0">Manage cities used in listings, user profiles, and localities.</p>
  </div>
  <a class="btn btn-danger rounded-pill px-3" href="<?php echo site_url('panel/city/add'); ?>">Add city</a>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">All cities</h2>
    <span class="badge bg-light text-dark border"><?php echo count($rows); ?> total</span>
  </div>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>State</th>
          <th>Sort</th>
          <th>Active</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)) : ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-5">No cities yet.</td>
        </tr>
        <?php else : ?>
          <?php foreach ($rows as $c) : ?>
          <tr>
            <td class="text-muted font-monospace small">#<?php echo (int) $c->id; ?></td>
            <td class="fw-medium"><?php echo html_escape($c->name); ?></td>
            <td><?php echo html_escape($c->state); ?></td>
            <td><?php echo (int) $c->sort_order; ?></td>
            <td><?php echo !empty($c->is_active) ? '<span class="text-success">Yes</span>' : '<span class="text-muted">No</span>'; ?></td>
            <td class="text-end text-nowrap">
              <a class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-1" href="<?php echo site_url('panel/city/view/' . (int) $c->id); ?>">View</a>
              <a class="btn btn-sm btn-outline-dark rounded-pill px-3" href="<?php echo site_url('panel/city/edit/' . (int) $c->id); ?>">Edit</a>
              <?php echo form_open(site_url('panel/city/delete/' . (int) $c->id), array('class' => 'd-inline', 'onsubmit' => "return confirm('Delete this city? Only allowed if unused.');")); ?>
                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">Delete</button>
              <?php echo form_close(); ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
