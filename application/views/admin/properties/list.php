<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-home me-2"></i>Properties</h2>
        <a href="<?php echo base_url('admin/property_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Property
        </a>
    </div>

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
                            <th>Category</th>
                            <th>City</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Features</th>
                            <th>Gallery</th>
                            <th>Flags</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($properties)): ?>
                            <tr>
                                <td colspan="11" class="text-center">No properties found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($properties as $property): ?>
                                <?php 
                                $gallery_count = 0;
                                if($property->gallery) {
                                    $gallery_array = json_decode($property->gallery, true);
                                    $gallery_count = is_array($gallery_array) ? count($gallery_array) : 0;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $property->id; ?></td>
                                    <td>
                                        <?php if($property->main_image): ?>
                                            <img src="<?php echo base_url($property->main_image); ?>" style="width: 50px; height: 50px; object-fit: cover;" class="img-thumbnail me-2">
                                        <?php endif; ?>
                                        <?php echo $property->name; ?>
                                    </td>
                                    <td><?php echo $property->category; ?></td>
                                    <td><?php echo htmlspecialchars($property->city); ?></td>
                                    <td><?php echo !empty($property->location) ? htmlspecialchars($property->location) : '<span class="text-muted">-</span>'; ?></td>
                                    <td>₹<?php echo number_format($property->price, 2); ?></td>
                                    <td>
                                        <?php 
                                        $features = array();
                                        if(isset($property->features) && $property->features) {
                                            $features = json_decode($property->features, true);
                                            if (!is_array($features)) {
                                                $features = array();
                                            }
                                        }
                                        ?>
                                        <?php if(!empty($features)): ?>
                                            <div style="max-width: 200px;">
                                                <?php foreach($features as $feature): ?>
                                                    <span class="badge bg-info me-1 mb-1"><?php echo htmlspecialchars($feature); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($property->main_image): ?>
                                            <span class="badge bg-success me-1">
                                                <i class="fas fa-image"></i> Main
                                            </span>
                                        <?php endif; ?>
                                        <?php if($gallery_count > 0): ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-images me-1"></i><?php echo $gallery_count; ?> gallery
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">No gallery</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(isset($property->is_latest) && $property->is_latest == 1): ?>
                                            <span class="badge bg-primary me-1">
                                                <i class="fas fa-star"></i> Latest
                                            </span>
                                        <?php endif; ?>
                                        <?php if(isset($property->is_featured) && $property->is_featured == 1): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-star"></i> Featured
                                            </span>
                                        <?php endif; ?>
                                        <?php if((!isset($property->is_latest) || $property->is_latest == 0) && (!isset($property->is_featured) || $property->is_featured == 0)): ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $property->status == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($property->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo base_url('admin/property_edit/'.$property->id); ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/property_delete/'.$property->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
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

