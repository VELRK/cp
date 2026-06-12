<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
    
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card primary">
                <i class="fas fa-home text-primary"></i>
                <h3><?php echo $total_properties; ?></h3>
                <p class="text-muted mb-0">Total Properties</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card success">
                <i class="fas fa-check-circle text-success"></i>
                <h3><?php echo $active_properties; ?></h3>
                <p class="text-muted mb-0">Active Properties</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card info">
                <i class="fas fa-images text-info"></i>
                <h3><?php echo $total_banners; ?></h3>
                <p class="text-muted mb-0">Total Banners</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card warning">
                <i class="fas fa-envelope text-warning"></i>
                <h3><?php echo $new_enquiries + $new_contacts; ?></h3>
                <p class="text-muted mb-0">New Messages</p>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Recent Enquiries</h5>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted">View all enquiries from <a href="<?php echo base_url('admin/enquiries'); ?>">Enquiries Page</a></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-address-book me-2"></i>Recent Contacts</h5>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted">View all contacts from <a href="<?php echo base_url('admin/contacts'); ?>">Contacts Page</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

