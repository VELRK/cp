<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Housing news</h1>
    <p class="nb-admin-page-desc mb-0">News powering <code>/api/mobile/housing-news</code>.</p>
  </div>
  <a class="btn btn-success rounded-pill px-3" href="<?php echo site_url('panel/housing-news/add'); ?>">
    <i class="bi bi-plus-lg me-1"></i> Add housing news
  </a>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">All housing news</h2>
    <span class="badge bg-light text-dark border"><?php echo count($rows); ?> total</span>
  </div>
  <div class="nb-admin-panel-body">
    <div class="nb-admin-table-wrap">
      <table class="table nb-admin-table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Category</th>
          <th>Author</th>
          <th>Created</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($rows)) : ?>
        <tr><td colspan="6" class="text-center text-muted py-5">No housing news found.</td></tr>
      <?php else : foreach ($rows as $r) : ?>
        <tr>
          <td class="text-muted font-monospace small">#<?php echo (int) $r->id; ?></td>
          <td><?php echo html_escape((string) $r->title); ?></td>
          <td><?php echo html_escape((string) $r->category); ?></td>
          <td><?php echo html_escape((string) $r->authorName); ?></td>
          <td><?php echo html_escape((string) $r->createdAt); ?></td>
          <td class="text-end">
            <div class="d-inline-flex align-items-center gap-2 flex-nowrap">
              <a href="<?php echo site_url('panel/housing-news/edit/' . (int) $r->id); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
              <form action="<?php echo site_url('panel/housing-news/delete/' . (int) $r->id); ?>" method="post" class="m-0" onsubmit="return confirm('Delete this housing news item?');">
                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Delete</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
      </table>
    </div>
  </div>
</div>
