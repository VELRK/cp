<?php $notification_base = isset($notification_base) ? trim((string) $notification_base) : 'admin'; ?>
<?php $is_panel_notifications = ($notification_base === 'panel'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-bell me-2"></i>Notifications</h2>
        <a href="<?php echo $is_panel_notifications ? site_url('panel/notification/create') : site_url('admin/notification_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Notification
        </a>
    </div>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Video</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($notifications)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No notifications found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($notifications as $notification): ?>
                                <tr>
                                    <td><?php echo $notification->id; ?></td>
                                    <td>
                                        <?php if($notification->image): ?>
                                            <img src="<?php echo base_url($notification->image); ?>" style="max-width: 100px; height: 60px; object-fit: cover;" class="img-thumbnail">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small">
                                        <?php if (!empty($notification->video)): ?>
                                            <a href="<?php echo base_url($notification->video); ?>" target="_blank" rel="noopener">View</a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($notification->title); ?></td>
                                    <td>
                                        <?php 
                                            $description = htmlspecialchars($notification->description);
                                            echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $notification->status == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($notification->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($notification->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo $is_panel_notifications ? site_url('panel/notification/toggle/'.$notification->id) : site_url('admin/notification_toggle/'.$notification->id); ?>" class="btn btn-sm btn-<?php echo $notification->status == 'active' ? 'warning' : 'success'; ?>" title="Toggle Status">
                                            <i class="fas fa-<?php echo $notification->status == 'active' ? 'eye-slash' : 'eye'; ?>"></i>
                                        </a>
                                        <a href="<?php echo $is_panel_notifications ? site_url('panel/notification/edit/'.$notification->id) : site_url('admin/notification_edit/'.$notification->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo $is_panel_notifications ? site_url('panel/notification/delete/'.$notification->id) : site_url('admin/notification_delete/'.$notification->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this notification?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
