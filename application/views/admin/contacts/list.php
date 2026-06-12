<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-address-book me-2"></i>Contacts</h2>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $this->session->flashdata('success'); ?>
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($contacts)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No contacts found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($contacts as $contact): ?>
                                <tr>
                                    <td><?php echo $contact->id; ?></td>
                                    <td><?php echo $contact->name; ?></td>
                                    <td><?php echo $contact->email; ?></td>
                                    <td><?php echo $contact->phone ?: '-'; ?></td>
                                    <td><?php echo $contact->subject ?: '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $contact->status == 'new' ? 'danger' : 
                                                ($contact->status == 'read' ? 'warning' : 'success'); 
                                        ?>">
                                            <?php echo ucfirst($contact->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($contact->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo base_url('admin/contact_view/'.$contact->id); ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/contact_delete/'.$contact->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
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

