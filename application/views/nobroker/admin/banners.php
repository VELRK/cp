<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Site banners</h1>
    <p class="nb-admin-page-desc mb-0">
      Upload hero images for the homepage slideshow only (<code>/api/nb/site-banners</code>).
      Each row is one image — no property link required.
    </p>
  </div>
  <a class="btn btn-success rounded-pill px-3" href="<?php echo site_url('panel/banner/add'); ?>">
    <i class="bi bi-plus-lg me-1"></i> Add banner
  </a>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">Homepage banners</h2>
    <span class="badge bg-light text-dark border"><?php echo count($rows); ?> total</span>
  </div>
  <div class="nb-admin-panel-body">
    <div class="nb-admin-table-wrap">
      <table class="table nb-admin-table mb-0">
      <thead>
      <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Status</th>
        <th class="text-end">Actions</th>
      </tr>
      </thead>
      <tbody>
      <?php if (empty($rows)) : ?>
        <tr>
          <td colspan="4" class="text-center text-muted py-5">
            No site banners yet. Click <strong>Add banner</strong> and upload a wide hero image (JPEG/PNG/WebP, max 2 MB).
          </td>
        </tr>
      <?php else : foreach ($rows as $r) : ?>
        <?php
        $img = '';
        if (isset($r->imageUrl) && trim((string) $r->imageUrl) !== '') {
            $img = trim((string) $r->imageUrl);
        } elseif (isset($r->image) && trim((string) $r->image) !== '') {
            $img = trim((string) $r->image);
        }
        $imgUrl = $img !== '' ? (preg_match('/^https?:\/\//i', $img) ? $img : base_url($img)) : '';
        ?>
        <tr>
          <td class="text-muted font-monospace small">#<?php echo (int) $r->id; ?></td>
          <td>
            <?php if ($imgUrl !== '') : ?>
              <img src="<?php echo html_escape($imgUrl); ?>" alt="" style="width:220px;height:92px;object-fit:cover;border-radius:8px;border:1px solid #dbe1ea;">
            <?php else : ?>
              <span class="text-muted">No image</span>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge <?php echo ((string) $r->status === 'active') ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'; ?>">
              <?php echo html_escape((string) $r->status); ?>
            </span>
          </td>
          <td class="text-end">
            <div class="d-inline-flex align-items-center gap-2 flex-nowrap">
              <form action="<?php echo site_url('panel/banner/toggle/' . (int) $r->id); ?>" method="post" class="m-0">
                <button type="submit" class="btn btn-sm btn-outline-dark rounded-pill px-3">Toggle</button>
              </form>
              <a href="<?php echo site_url('panel/banner/edit/' . (int) $r->id); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
              <form action="<?php echo site_url('panel/banner/delete/' . (int) $r->id); ?>" method="post" class="m-0" onsubmit="return confirm('Delete this banner?');">
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
