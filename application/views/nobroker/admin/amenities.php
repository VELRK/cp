<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Amenities</h1>
    <p class="nb-admin-page-desc mb-0">Options appear as checkboxes when owners or admins create listings.</p>
  </div>
  <a class="btn btn-danger rounded-pill px-3" href="<?php echo site_url('panel/amenity/add'); ?>">Add amenity</a>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">All amenities</h2>
    <span class="badge bg-light text-dark border"><?php echo count($rows); ?> total</span>
  </div>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Slug</th>
          <th>Sort</th>
          <th>Active</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)) : ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-5">No amenities. Run <code>application/sql/migrations/006_nb_amenities.sql</code> or import the full schema.</td>
        </tr>
        <?php else : ?>
          <?php foreach ($rows as $a) : ?>
          <tr>
            <td class="text-muted font-monospace small">#<?php echo (int) $a->id; ?></td>
            <td class="fw-medium"><?php echo html_escape($a->name); ?></td>
            <td class="small text-muted font-monospace"><?php echo html_escape($a->slug); ?></td>
            <td><?php echo (int) $a->sort_order; ?></td>
            <td><?php echo !empty($a->is_active) ? '<span class="text-success">Yes</span>' : '<span class="text-muted">No</span>'; ?></td>
            <td class="text-end text-nowrap">
              <a class="btn btn-sm btn-outline-dark rounded-pill px-3" href="<?php echo site_url('panel/amenity/edit/' . (int) $a->id); ?>">Edit</a>
              <?php echo form_open(site_url('panel/amenity/delete/' . (int) $a->id), array('class' => 'd-inline', 'onsubmit' => "return confirm('Delete this amenity?');")); ?>
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
