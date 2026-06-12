<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users me-2"></i>Users Management</h2>
        <a href="<?php echo base_url('admin/user_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New User
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
        <div class="card-header bg-light">
            <div class="row g-2">
                <div class="col-md-4">
                    <form method="get" class="d-flex">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..."
                               value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>">
                        <button type="submit" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form method="get" class="d-flex">
                        <select name="status" class="form-select" onchange="this.form.submit();">
                            <option value="">All Status</option>
                            <option value="active" <?php echo isset($status) && $status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo isset($status) && $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </form>
                </div>
                <div class="col-md-5 text-end">
                    <button type="button" class="btn btn-danger btn-sm" id="bulk-delete-btn" style="display:none;">
                        <i class="fas fa-trash me-2"></i>Delete Selected
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" id="bulk-status-btn" style="display:none;">
                        <i class="fas fa-edit me-2"></i>Change Status
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No users found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($users as $user): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input user-checkbox" value="<?php echo $user->id; ?>">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if(!empty($user->profilepic)): ?>
                                                <img src="<?php echo base_url($user->profilepic); ?>" alt="<?php echo htmlspecialchars($user->fullname); ?>"
                                                     style="width: 32px; height: 32px; object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                            <?php else: ?>
                                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($user->fullname ?? 'N/A'); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user->email ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(($user->countrycode ?? '+91') . ' ' . ($user->phonenumber ?? 'N/A')); ?></td>
                                    <td><?php echo htmlspecialchars($user->city ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user->isactive === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($user->isactive ?? 'unknown'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user->created_at ?? 'now')); ?></td>
                                    <td>
                                        <a href="<?php echo base_url('admin/user_edit/'.$user->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/user_delete/'.$user->id); ?>" class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this user?')" title="Delete">
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
        <?php if(!empty($users)): ?>
            <div class="card-footer text-muted">
                Showing <?php echo count($users); ?> user(s)
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const bulkStatusBtn = document.getElementById('bulk-status-btn');

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkButtons();
    });

    // Individual checkbox change
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkButtons();
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            }
        });
    });

    function updateBulkButtons() {
        const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
        if (checkedCount > 0) {
            bulkDeleteBtn.style.display = 'inline-block';
            bulkStatusBtn.style.display = 'inline-block';
        } else {
            bulkDeleteBtn.style.display = 'none';
            bulkStatusBtn.style.display = 'none';
        }
    }

    // Bulk delete
    bulkDeleteBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete the selected users?')) {
            const ids = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
            if (ids.length > 0) {
                fetch('<?php echo base_url('admin/bulk_delete_users'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids: ids })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => alert('Error: ' + error));
            }
        }
    });

    // Bulk status
    bulkStatusBtn.addEventListener('click', function() {
        const status = prompt('Enter new status (active/inactive):', 'active');
        if (status && (status === 'active' || status === 'inactive')) {
            const ids = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
            if (ids.length > 0) {
                fetch('<?php echo base_url('admin/bulk_update_status_users'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids: ids, status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => alert('Error: ' + error));
            }
        } else {
            alert('Invalid status. Please enter "active" or "inactive".');
        }
    });
});
</script>
