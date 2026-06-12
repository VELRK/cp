<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-map-marker-alt me-2"></i>Locations</h2>
        <a href="<?php echo base_url('admin/location_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Location
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
        <i class="fas fa-info-circle me-2"></i>Drag and drop rows to reorder locations. Order will be saved automatically.
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><i class="fas fa-grip-vertical text-muted"></i></th>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Location Name</th>
                            <th>City</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="locationsTableBody">
                        <?php if(empty($locations)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No locations found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($locations as $index => $location): ?>
                                <tr data-location-id="<?php echo $location->id; ?>" data-order="<?php echo isset($location->order) ? $location->order : $index; ?>" style="cursor: move;">
                                    <td class="drag-handle" style="cursor: grab;">
                                        <i class="fas fa-grip-vertical text-muted"></i>
                                    </td>
                                    <td><?php echo $location->id; ?></td>
                                    <td>
                                        <?php if(!empty($location->image)): ?>
                                            <img src="<?php echo base_url($location->image); ?>" alt="<?php echo htmlspecialchars($location->name); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $location->name; ?></td>
                                    <td><?php echo $location->city_name; ?></td>
                                    <td class="order-value"><?php echo isset($location->order) ? $location->order : $index; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $location->status == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($location->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($location->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo base_url('admin/location_edit/'.$location->id); ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/location_delete/'.$location->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
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
    const tbody = document.getElementById('locationsTableBody');
    if (!tbody) return;
    
    let sortable = Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: function(evt) {
            // Update order values in the table
            const rows = tbody.querySelectorAll('tr[data-location-id]');
            const orders = {};
            
            rows.forEach((row, index) => {
                const locationId = row.getAttribute('data-location-id');
                const orderValue = index + 1;
                row.setAttribute('data-order', orderValue);
                row.querySelector('.order-value').textContent = orderValue;
                orders[locationId] = orderValue;
            });
            
            // Send update to server
            updateLocationOrder(orders);
        }
    });
    
    function updateLocationOrder(orders) {
        fetch('<?php echo base_url("admin/location_update_order"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'orders=' + encodeURIComponent(JSON.stringify(orders))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:260px;';
                alertDiv.innerHTML = 'Order updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                document.body.appendChild(alertDiv);
                setTimeout(() => alertDiv.remove(), 3000);
            } else {
                alert('Failed to update order: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Order update error:', error);
            alert('An error occurred while updating order');
        });
    }
});
</script>