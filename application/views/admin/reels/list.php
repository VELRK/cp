<div class="container-fluid">
    <?php
    $mu = isset($media_urls) ? $media_urls : nb_media_admin_urls('reels', false);
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-video me-2"></i>Reels Videos</h2>
        <a href="<?php echo html_escape($mu['create']); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Reel
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

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>Drag and drop rows to reorder reels. Order will be saved automatically.
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reelsTableBody">
                        <?php if(empty($reels)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No reels found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($reels as $index => $reel): ?>
                                <tr data-reel-id="<?php echo $reel->id; ?>" data-index="<?php echo $reel->index_no ?: 0; ?>" style="cursor: move;">
                                    <td class="drag-handle" style="cursor: grab;">
                                        <i class="fas fa-grip-vertical text-muted"></i>
                                    </td>
                                    <td><?php echo $reel->id; ?></td>
                                    <td>
                                        <?php if($reel->thumbnail): ?>
                                            <img src="<?php echo html_escape(nb_media_external_url($reel->thumbnail)); ?>" style="max-width: 100px; height: 60px; object-fit: cover;" class="img-thumbnail">
                                        <?php else: ?>
                                            <span class="text-muted">No thumbnail</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($reel->title ?: 'Untitled'); ?></td>
                                    <td>
                                        <?php if($reel->videoUrl): ?>
                                            <a href="<?php echo html_escape(nb_media_external_url($reel->videoUrl)); ?>" target="_blank" class="text-primary">
                                                <i class="fas fa-video me-1"></i>View Video
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No video</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="index-value"><?php echo $reel->index_no ?: 0; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $reel->status == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($reel->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $reel->createdAt ? date('M d, Y', strtotime($reel->createdAt)) : 'N/A'; ?></td>
                                    <td>
                                        <a href="<?php echo html_escape($mu['edit'] . $reel->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo html_escape($mu['delete'] . $reel->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
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
    const tbody = document.getElementById('reelsTableBody');
    if (!tbody) return;
    
    let sortable = Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: function(evt) {
            // Update order values in the table
            const rows = tbody.querySelectorAll('tr[data-reel-id]');
            const orders = {};
            
            rows.forEach((row, index) => {
                const reelId = row.getAttribute('data-reel-id');
                if (reelId) {
                    // Order by DESC, so first row gets highest number
                    const orderValue = rows.length - index;
                    row.setAttribute('data-index', orderValue);
                    const indexCell = row.querySelector('.index-value');
                    if (indexCell) {
                        indexCell.textContent = orderValue;
                    }
                    // Store as string key (will be converted to int on server)
                    orders[String(reelId)] = orderValue;
                }
            });
            
            // Send update to server
            if (Object.keys(orders).length > 0) {
                updateReelOrder(orders);
            } else {
                console.error('No valid reel IDs found');
            }
        }
    });
    
    function updateReelOrder(orders) {
        console.log('Updating reel order:', orders);
        
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
