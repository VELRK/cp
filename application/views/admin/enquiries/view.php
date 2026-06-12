<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-envelope me-2"></i>View Enquiry</h2>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2"><strong>Name:</strong></div>
                <div class="col-md-10"><?php echo $enquiry->name; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Email:</strong></div>
                <div class="col-md-10"><?php echo $enquiry->email; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Phone:</strong></div>
                <div class="col-md-10"><?php echo $enquiry->phone ?: '-'; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>City:</strong></div>
                <div class="col-md-10"><?php echo isset($enquiry->city) ? $enquiry->city : '-'; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Property:</strong></div>
                <div class="col-md-10"><?php echo $enquiry->property_name ?: '-'; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Status:</strong></div>
                <div class="col-md-10">
                    <span class="badge bg-<?php 
                        echo $enquiry->status == 'new' ? 'danger' : 
                            ($enquiry->status == 'read' ? 'warning' : 'success'); 
                    ?>">
                        <?php echo ucfirst($enquiry->status); ?>
                    </span>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Date:</strong></div>
                <div class="col-md-10"><?php echo date('F d, Y h:i A', strtotime($enquiry->created_at)); ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Message:</strong></div>
                <div class="col-md-10"><?php echo nl2br($enquiry->message); ?></div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?php echo base_url('admin/enquiries'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
                <a href="<?php echo base_url('admin/enquiry_delete/'.$enquiry->id); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                    <i class="fas fa-trash me-2"></i>Delete
                </a>
            </div>
        </div>
    </div>
</div>

