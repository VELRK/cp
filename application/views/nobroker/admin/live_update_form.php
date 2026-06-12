<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head">
  <nav class="nb-admin-breadcrumb" aria-label="breadcrumb">
    <a href="<?php echo site_url('panel/live-updates'); ?>"><i class="bi bi-arrow-left me-1"></i>Live updates</a>
    <span class="text-muted"> / </span>
    <span class="text-dark">Edit</span>
  </nav>
  <h1 class="nb-admin-page-title">Edit live update</h1>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-body p-4">
    <form method="post" enctype="multipart/form-data">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label fw-semibold">Title *</label>
          <input type="text" name="title" class="form-control nb-admin-input" maxlength="255" required value="<?php echo html_escape(set_value('title', isset($row->title) ? $row->title : '')); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Platform *</label>
          <?php $platform = set_value('platform', isset($row->platform) ? $row->platform : 'app'); ?>
          <select name="platform" class="form-select nb-admin-input" required>
            <option value="app" <?php echo $platform === 'app' ? 'selected' : ''; ?>>App</option>
            <option value="youtube" <?php echo $platform === 'youtube' ? 'selected' : ''; ?>>YouTube</option>
            <option value="instagram" <?php echo $platform === 'instagram' ? 'selected' : ''; ?>>Instagram</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Status *</label>
          <?php $status = set_value('status', isset($row->status) ? $row->status : 'upcoming'); ?>
          <select name="status" class="form-select nb-admin-input" required>
            <option value="upcoming" <?php echo $status === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
            <option value="live_started" <?php echo $status === 'live_started' ? 'selected' : ''; ?>>Live Started</option>
            <option value="reschedule" <?php echo $status === 'reschedule' ? 'selected' : ''; ?>>Reschedule</option>
            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Live time</label>
          <input type="datetime-local" name="liveTime" class="form-control nb-admin-input" value="<?php echo html_escape(set_value('liveTime', !empty($row->liveTime) ? date('Y-m-d\TH:i', strtotime($row->liveTime)) : '')); ?>">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Link URL</label>
          <input type="url" name="url" class="form-control nb-admin-input" maxlength="500" value="<?php echo html_escape(set_value('url', isset($row->url) ? $row->url : '')); ?>">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Image Upload</label>
          <input type="file" name="image_file" class="form-control nb-admin-input" accept=".jpg,.jpeg,.png,.webp">
          <div class="form-text">Allowed: JPG, JPEG, PNG, WEBP (max 5MB)</div>
          <?php $existingImage = set_value('image', isset($row->image) ? $row->image : ''); ?>
          <input type="hidden" name="image" value="<?php echo html_escape($existingImage); ?>">
          <?php if (!empty($existingImage)) : ?>
            <div class="mt-2">
              <span class="small text-muted d-block mb-1">Current image</span>
              <img src="<?php echo preg_match('/^https?:\/\//i', $existingImage) ? html_escape($existingImage) : base_url($existingImage); ?>" alt="Live update image" style="max-width:180px;max-height:120px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;">
            </div>
          <?php endif; ?>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" class="form-control nb-admin-input" rows="4"><?php echo html_escape(set_value('description', isset($row->description) ? $row->description : '')); ?></textarea>
        </div>
      </div>
      <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-success rounded-pill px-4">Update</button>
        <a href="<?php echo site_url('panel/live-updates'); ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
      </div>
    </form>
  </div>
</div>
