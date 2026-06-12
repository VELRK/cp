<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-newspaper me-2"></i>Housing News</h2>
        <a href="<?php echo base_url('admin/housing_news_create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add News
        </a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
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
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($housing_news)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No housing news found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($housing_news as $item): ?>
                            <tr>
                                <td><?php echo (int) $item->id; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item->title); ?></strong>
                                    <?php if (!empty($item->subtitle)): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($item->subtitle); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($item->category); ?></span></td>
                                <td><?php echo !empty($item->authorName) ? htmlspecialchars($item->authorName) : '-'; ?></td>
                                <td><?php echo !empty($item->createdAt) ? date('M d, Y H:i', strtotime($item->createdAt)) : '-'; ?></td>
                                <td>
                                    <a href="<?php echo base_url('admin/housing_news_edit/' . (int) $item->id); ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo base_url('admin/housing_news_delete/' . (int) $item->id); ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Delete this news item?');">
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
