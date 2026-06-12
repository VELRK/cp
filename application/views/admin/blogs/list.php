<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-blog me-2"></i>Blogs</h2>
        <a href="<?php echo base_url('admin/blog_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Blog
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
                            <th>Author</th>
                            <th>Date</th>
                            <th>Gallery</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($blogs)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No blogs found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($blogs as $blog): ?>
                                <?php 
                                $gallery_count = 0;
                                if($blog->gallery) {
                                    $gallery_array = json_decode($blog->gallery, true);
                                    $gallery_count = is_array($gallery_array) ? count($gallery_array) : 0;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $blog->id; ?></td>
                                    <td>
                                        <strong><?php echo $blog->name; ?></strong>
                                        <?php if($blog->short_notes): ?>
                                            <br><small class="text-muted"><?php echo substr($blog->short_notes, 0, 50); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $blog->author ?: '-'; ?></td>
                                    <td><?php echo $blog->date ? date('M d, Y', strtotime($blog->date)) : '-'; ?></td>
                                    <td>
                                        <?php if($gallery_count > 0): ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-images me-1"></i><?php echo $gallery_count; ?> images
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">No images</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $blog->status == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($blog->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/blog/<?php echo $blog->id; ?>" class="btn btn-sm btn-info text-white" target="_blank" title="View Blog">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/blog_edit/'.$blog->id); ?>" class="btn btn-sm btn-warning" title="Edit Blog">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/blog_delete/'.$blog->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete Blog">
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

