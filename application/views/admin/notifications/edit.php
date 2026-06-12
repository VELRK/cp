<div class="container-fluid">
    <?php $notification_base = isset($notification_base) ? trim((string) $notification_base) : 'admin'; ?>
    <?php $is_panel_notifications = ($notification_base === 'panel'); ?>
    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Notification</h2>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" class="form-control" name="title" required maxlength="255" value="<?php echo htmlspecialchars($notification->title); ?>" placeholder="Enter notification title">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="5" placeholder="Enter notification description"><?php echo htmlspecialchars($notification->description); ?></textarea>
                    <small class="text-muted">You can provide detailed information about the notification</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Image</label>
                    <?php if($notification->image): ?>
                        <div class="mb-2">
                            <img src="<?php echo base_url($notification->image); ?>" style="max-width: 500px; max-height: 300px;" class="img-thumbnail">
                            <p class="text-muted mt-1">Current notification image</p>
                            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($notification->image); ?>">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="image" accept="image/*" id="notificationImageInput">
                    <small class="text-muted">Upload new image to replace current image. JPG, PNG, GIF, WEBP — max 5MB.</small>
                    <div id="notificationImagePreview" class="mt-2"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Video</label>
                    <?php if (!empty($notification->video)) : ?>
                        <div class="mb-2">
                            <a href="<?php echo base_url($notification->video); ?>" target="_blank" rel="noopener" class="small">Current video file</a>
                            <input type="hidden" name="existing_video" value="<?php echo htmlspecialchars($notification->video); ?>">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="video" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov" id="notificationVideoInput">
                    <small class="text-muted">MP4, WebM, or MOV — max 30MB. Upload a new file to replace.</small>
                </div>
                <script>
                    document.getElementById('notificationImageInput').addEventListener('change', function(e) {
                        const preview = document.getElementById('notificationImagePreview');
                        preview.innerHTML = '';
                        if (this.files && this.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'img-thumbnail';
                                img.style.maxWidth = '500px';
                                img.style.maxHeight = '300px';
                                preview.appendChild(img);
                            };
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                </script>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="active" <?php echo $notification->status == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $notification->status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Notification
                    </button>
                    <a href="<?php echo $is_panel_notifications ? site_url('panel/notifications') : site_url('admin/notifications'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
