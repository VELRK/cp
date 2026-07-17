<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Owner dashboard</h1>
    <p class="nb-admin-page-desc mb-0">Overview of your listings, enquiries, and site visit requests.</p>
  </div>
  <a href="<?php echo site_url('owner/property/add'); ?>" class="btn btn-danger rounded-pill px-4">
    <i class="bi bi-plus-lg me-1"></i>Add property
  </a>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="nb-owner-stat-card d-flex align-items-center gap-3">
      <div class="nb-owner-stat-icon nb-owner-stat-icon--muted"><i class="bi bi-building"></i></div>
      <div>
        <div class="nb-owner-stat-value"><?php echo (int) $total_listings; ?></div>
        <div class="nb-owner-stat-label">Total listings</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="nb-owner-stat-card d-flex align-items-center gap-3">
      <div class="nb-owner-stat-icon nb-owner-stat-icon--success"><i class="bi bi-check2-circle"></i></div>
      <div>
        <div class="nb-owner-stat-value"><?php echo (int) $active_listings; ?></div>
        <div class="nb-owner-stat-label">Published</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="nb-owner-stat-card d-flex align-items-center gap-3">
      <div class="nb-owner-stat-icon nb-owner-stat-icon--info"><i class="bi bi-eye"></i></div>
      <div>
        <div class="nb-owner-stat-value"><?php echo (int) $total_views; ?></div>
        <div class="nb-owner-stat-label">Total views</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <a href="<?php echo site_url('owner/site-visits?status=pending'); ?>" class="text-decoration-none text-reset d-block h-100">
      <div class="nb-owner-stat-card d-flex align-items-center gap-3 h-100">
        <div class="nb-owner-stat-icon nb-owner-stat-icon--danger"><i class="bi bi-calendar-check"></i></div>
        <div>
          <div class="nb-owner-stat-value"><?php echo (int) $pending_visits; ?></div>
          <div class="nb-owner-stat-label">Pending site visits</div>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="nb-admin-panel h-100">
      <div class="nb-admin-panel-header d-flex justify-content-between align-items-center">
        <h2 class="nb-admin-panel-title mb-0">Recent site visits</h2>
        <a href="<?php echo site_url('owner/site-visits'); ?>" class="btn btn-sm btn-outline-secondary rounded-pill">View all</a>
      </div>
      <div class="nb-admin-table-wrap">
        <table class="table nb-admin-table mb-0">
          <thead>
            <tr>
              <th>Visitor</th>
              <th>Property</th>
              <th>Scheduled</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recent_visits)) : ?>
              <tr><td colspan="4" class="text-center text-muted py-4">No site visits yet.</td></tr>
            <?php else : ?>
              <?php foreach ($recent_visits as $v) : ?>
                <tr>
                  <td class="fw-medium"><?php echo html_escape($v->visitor_name ?: '—'); ?></td>
                  <td class="small"><?php echo html_escape($v->property_title ?: '—'); ?></td>
                  <td class="small text-nowrap"><?php echo html_escape($v->scheduled_at); ?></td>
                  <td><span class="badge bg-<?php echo $v->status === 'pending' ? 'warning text-dark' : ($v->status === 'confirmed' ? 'success' : 'secondary'); ?> text-capitalize"><?php echo html_escape($v->status); ?></span></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="nb-admin-panel h-100">
      <div class="nb-admin-panel-header d-flex justify-content-between align-items-center">
        <h2 class="nb-admin-panel-title mb-0">Recent enquiries</h2>
        <a href="<?php echo site_url('owner/enquiries'); ?>" class="btn btn-sm btn-outline-secondary rounded-pill">View all</a>
      </div>
      <div class="nb-admin-table-wrap">
        <table class="table nb-admin-table mb-0">
          <thead>
            <tr>
              <th>Property</th>
              <th>From</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recent_enquiries)) : ?>
              <tr><td colspan="3" class="text-center text-muted py-4">No enquiries yet.</td></tr>
            <?php else : ?>
              <?php foreach ($recent_enquiries as $e) : ?>
                <tr>
                  <td class="small"><?php echo html_escape($e->property_title); ?></td>
                  <td><?php echo html_escape($e->tenant_name); ?></td>
                  <td><span class="badge bg-light text-dark border"><?php echo html_escape($e->status); ?></span></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="mt-4 d-flex flex-wrap gap-2">
  <a href="<?php echo site_url('owner/listings'); ?>" class="btn btn-outline-secondary rounded-pill px-4">My listings</a>
  <a href="<?php echo site_url('owner/site-visits'); ?>" class="btn btn-outline-danger rounded-pill px-4">Manage site visits</a>
</div>
