<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-envelope me-2"></i>Enquiries</h2>

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
                            <th>Phone</th>
                            <th>City</th>
                            <th>Email</th>
                            <th>Property</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($enquiries)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No enquiries found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($enquiries as $enquiry): ?>
                                <tr>
                                    <td><?php echo $enquiry->id; ?></td>
                                    <td><?php echo $enquiry->name; ?></td>
                                    <td><?php echo $enquiry->phone ?: '-'; ?></td>
                                    <td><?php echo isset($enquiry->city) ? $enquiry->city : '-'; ?></td>
                                    <td><?php echo $enquiry->email; ?></td>
                                    <td><?php echo $enquiry->property_name ?: '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $enquiry->status == 'new' ? 'danger' : 
                                                ($enquiry->status == 'read' ? 'warning' : 'success'); 
                                        ?>">
                                            <?php echo ucfirst($enquiry->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($enquiry->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo base_url('admin/enquiry_view/'.$enquiry->id); ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/enquiry_delete/'.$enquiry->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
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

