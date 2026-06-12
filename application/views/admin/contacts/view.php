<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-address-book me-2"></i>View Contact</h2>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2"><strong>Name:</strong></div>
                <div class="col-md-10"><?php echo $contact->name; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Email:</strong></div>
                <div class="col-md-10"><?php echo $contact->email; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Phone:</strong></div>
                <div class="col-md-10"><?php echo $contact->phone ?: '-'; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Subject:</strong></div>
                <div class="col-md-10"><?php echo $contact->subject ?: '-'; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Status:</strong></div>
                <div class="col-md-10">
                    <span class="badge bg-<?php 
                        echo $contact->status == 'new' ? 'danger' : 
                            ($contact->status == 'read' ? 'warning' : 'success'); 
                    ?>">
                        <?php echo ucfirst($contact->status); ?>
                    </span>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Date:</strong></div>
                <div class="col-md-10"><?php echo date('F d, Y h:i A', strtotime($contact->created_at)); ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong>Message:</strong></div>
                <div class="col-md-10"><?php echo nl2br($contact->message); ?></div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?php echo base_url('admin/contacts'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
                <a href="<?php echo base_url('admin/contact_delete/'.$contact->id); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                    <i class="fas fa-trash me-2"></i>Delete
                </a>
            </div>
        </div>
    </div>
</div>

