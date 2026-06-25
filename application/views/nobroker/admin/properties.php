<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Properties</h1>
    <p class="nb-admin-page-desc mb-0">Listings are visible on the public site only when <strong>Published</strong> is on in the editor.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-dark rounded-pill px-3" href="<?php echo site_url('panel/properties/pending'); ?>">
      Approvals<?php if (!empty($pending_count)) : ?> <span class="badge text-bg-warning ms-1"><?php echo (int) $pending_count; ?></span><?php endif; ?>
    </a>
    <a class="btn btn-danger rounded-pill px-3" href="<?php echo site_url('panel/property/add'); ?>">Add property</a>
  </div>
</div>

<?php if (!empty($filter_owner)) : ?>
<div class="alert alert-light border d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <span>Showing listings for <strong><?php echo html_escape($filter_owner->name); ?></strong> (user #<?php echo (int) $filter_owner->id; ?>)</span>
  <a class="btn btn-sm btn-outline-secondary rounded-pill" href="<?php echo site_url('panel/properties'); ?>">Show all properties</a>
</div>
<?php endif; ?>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">All properties</h2>
    <span class="badge bg-light text-dark border"><?php echo count($rows); ?> shown</span>
  </div>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Public</th>
          <th>Owner</th>
          <th>City</th>
          <th>Type</th>
          <th class="text-end">Price</th>
          <th class="text-end">Views</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)) : ?>
        <tr>
          <td colspan="9" class="text-center text-muted py-5">No properties in the database yet.</td>
        </tr>
        <?php else : ?>
          <?php foreach ($rows as $p) : ?>
          <?php $pub = !empty($p->is_active); ?>
          <tr>
            <td class="text-muted font-monospace small"><?php echo (int) $p->id; ?></td>
            <td>
              <?php if ($pub) : ?>
              <a href="<?php echo html_escape(nb_property_url($p)); ?>" target="_blank" rel="noopener"><?php echo html_escape($p->title); ?></a>
              <?php else : ?>
              <span class="text-dark"><?php echo html_escape($p->title); ?></span>
              <span class="badge rounded-pill bg-secondary ms-1">Draft</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($pub) : ?>
              <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">Live</span>
              <?php else : ?>
              <span class="badge rounded-pill bg-light text-muted border">Hidden</span>
              <?php endif; ?>
            </td>
            <td><?php echo html_escape($p->owner_name); ?></td>
            <td><?php echo html_escape($p->city_name); ?></td>
            <td><span class="badge rounded-pill bg-light text-dark border"><?php echo html_escape($p->listing_type); ?></span></td>
            <td class="text-end fw-medium">₹<?php echo number_format((float) $p->price); ?></td>
            <td class="text-end text-muted"><?php echo number_format((int) $p->views); ?></td>
            <td class="text-end text-nowrap">
              <?php if ($pub) : ?>
              <a class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-1" href="<?php echo html_escape(nb_property_url($p)); ?>" target="_blank" rel="noopener">View</a>
              <?php else : ?>
              <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-1" disabled title="Publish the listing to view on the public site">View</button>
              <?php endif; ?>
              <a class="btn btn-sm btn-outline-dark rounded-pill px-3" href="<?php echo site_url('panel/property/edit/' . (int) $p->id); ?>">Edit</a>
              <?php echo form_open(site_url('panel/property/delete/' . (int) $p->id), array('class' => 'd-inline', 'onsubmit' => "return confirm('Delete this property permanently?');")); ?>
              <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Delete</button>
              <?php echo form_close(); ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
