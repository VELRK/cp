<?php
$mu = isset($media_urls) ? $media_urls : nb_media_admin_urls('videos', false);
$is_panel = isset($media_urls);
?>
<div class="<?php echo $is_panel ? '' : 'container-fluid'; ?>">
    <?php if ($is_panel) : ?>
    <div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="nb-admin-page-title">Videos</h1>
            <p class="nb-admin-page-desc mb-0">Manage YouTube video links shown on the homepage.</p>
        </div>
        <a href="<?php echo html_escape($mu['create']); ?>" class="btn btn-success rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add video
        </a>
    </div>
    <?php else : ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-film me-2"></i>Videos</h2>
        <a href="<?php echo html_escape($mu['create']); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Video
        </a>
    </div>
    <?php endif; ?>

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

    <div class="alert alert-info">
        <?php if ($is_panel) : ?><i class="bi bi-info-circle me-2"></i><?php else : ?><i class="fas fa-info-circle me-2"></i><?php endif; ?>
        Drag and drop rows to reorder videos. Order will be saved automatically.
    </div>

    <div class="<?php echo $is_panel ? 'nb-admin-panel' : 'card'; ?>">
        <?php if ($is_panel) : ?><div class="nb-admin-panel-body"><?php else : ?><div class="card-body"><?php endif; ?>
            <div class="<?php echo $is_panel ? 'nb-admin-table-wrap' : 'table-responsive'; ?>">
                <table class="table <?php echo $is_panel ? 'nb-admin-table mb-0' : 'table-striped table-hover'; ?>">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><i class="fas fa-grip-vertical text-muted"></i></th>
                            <th>ID</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Video URL</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="<?php echo $is_panel ? 'text-end' : ''; ?>">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="videosTableBody">
                        <?php if(empty($videos)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No videos found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($videos as $index => $video): ?>
                                <tr data-video-id="<?php echo $video->id; ?>" data-index="<?php echo $video->index_no ?: 0; ?>" style="cursor: move;">
                                    <td class="drag-handle" style="cursor: grab;">
                                        <i class="fas fa-grip-vertical text-muted"></i>
                                    </td>
                                    <td><?php echo $video->id; ?></td>
                                    <td>
                                        <?php if($video->thumbnail): ?>
                                            <img src="<?php echo html_escape(nb_media_external_url($video->thumbnail)); ?>" style="max-width: 100px; height: 60px; object-fit: cover;" class="img-thumbnail">
                                        <?php else: ?>
                                            <span class="text-muted">No thumbnail</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($video->title ?: 'Untitled'); ?></td>
                                    <td>
                                        <?php if($video->videoUrl): ?>
                                            <a href="<?php echo html_escape(nb_media_external_url($video->videoUrl)); ?>" target="_blank" class="text-primary">
                                                <i class="fas fa-video me-1"></i>View Video
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No video</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="index-value"><?php echo $video->index_no ?: 0; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $video->status == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($video->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $video->createdAt ? date('M d, Y', strtotime($video->createdAt)) : 'N/A'; ?></td>
                                    <td class="<?php echo $is_panel ? 'text-end' : ''; ?>">
                                        <?php if ($is_panel) : ?>
                                        <div class="d-inline-flex align-items-center gap-2 flex-nowrap">
                                            <a href="<?php echo html_escape($mu['edit'] . $video->id); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
                                            <a href="<?php echo html_escape($mu['delete'] . $video->id); ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Delete this video?');">Delete</a>
                                        </div>
                                        <?php else : ?>
                                        <a href="<?php echo html_escape($mu['edit'] . $video->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo html_escape($mu['delete'] . $video->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
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

<!-- SortableJS Library -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<style>
    .drag-handle {
        cursor: grab !important;
        user-select: none;
    }
    .drag-handle:active {
        cursor: grabbing !important;
    }
    tbody tr.sortable-ghost {
        opacity: 0.4;
        background: #f0f0f0;
    }
    tbody tr.sortable-drag {
        opacity: 0.8;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('videosTableBody');
    if (!tbody) return;
    
    let sortable = Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: function(evt) {
            // Update order values in the table
            const rows = tbody.querySelectorAll('tr[data-video-id]');
            const orders = {};
            
            rows.forEach((row, index) => {
                const videoId = row.getAttribute('data-video-id');
                if (videoId) {
                    // Order by DESC, so first row gets highest number
                    const orderValue = rows.length - index;
                    row.setAttribute('data-index', orderValue);
                    const indexCell = row.querySelector('.index-value');
                    if (indexCell) {
                        indexCell.textContent = orderValue;
                    }
                    // Store as string key (will be converted to int on server)
                    orders[String(videoId)] = orderValue;
                }
            });
            
            // Send update to server
            if (Object.keys(orders).length > 0) {
                updateVideoOrder(orders);
            } else {
                console.error('No valid video IDs found');
            }
        }
    });
    
    function updateVideoOrder(orders) {
        console.log('Updating video order:', orders);
        
        fetch('<?php echo html_escape($mu['update_order']); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'orders=' + encodeURIComponent(JSON.stringify(orders))
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Server response:', data);
            if (data.success) {
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Order updated successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                const container = document.querySelector('.container-fluid');
                const card = document.querySelector('.card');
                if (container && card) {
                    container.insertBefore(alertDiv, card);
                }
                
                // Auto-hide after 3 seconds
                setTimeout(() => {
                    alertDiv.remove();
                }, 3000);
            } else {
                const errorMsg = data.message || 'Unknown error';
                console.error('Update failed:', errorMsg);
                alert('Failed to update order: ' + errorMsg);
            }
        })
        .catch(error => {
            console.error('Error updating order:', error);
            alert('An error occurred while updating order: ' + error.message);
        });
    }
});
</script>
