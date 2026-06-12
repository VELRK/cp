<!-- Blog Manage Header -->
<section class="flat-title-page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="page-title-heading">
                    <h1 class="heading">Manage Blog Posts</h1>
                    <p class="sub-heading">Create, edit, and manage your blog content</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Manage Content -->
<section class="flat-blog-manage">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="blog-manage-wrapper">
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success">
                            <?php echo $this->session->flashdata('success'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?php echo $this->session->flashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="manage-header">
                        <div class="manage-actions">
                            <a href="<?php echo base_url('blog/create'); ?>" class="btn btn-primary">
                                <i class="icon icon-plus"></i> Create New Post
                            </a>
                            <a href="<?php echo base_url('blog'); ?>" class="btn btn-secondary">
                                <i class="icon icon-eye"></i> View Blog
                            </a>
                        </div>
                        <div class="manage-stats">
                            <span class="stat-item">
                                <strong><?php echo $total_posts; ?></strong> Total Posts
                            </span>
                            <span class="stat-item">
                                <strong><?php echo count(array_filter($posts, function($post) { return $post['status'] == 'published'; })); ?></strong> Published
                            </span>
                            <span class="stat-item">
                                <strong><?php echo count(array_filter($posts, function($post) { return $post['status'] == 'draft'; })); ?></strong> Drafts
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($posts)): ?>
                        <div class="posts-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td>
                                                <div class="post-title-cell">
                                                    <?php if (!empty($post['featured_image'])): ?>
                                                        <img src="<?php echo base_url('assets/images/' . $post['featured_image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                                             class="post-thumb">
                                                    <?php endif; ?>
                                                    <div class="post-info">
                                                        <h6><?php echo htmlspecialchars($post['title']); ?></h6>
                                                        <p><?php echo htmlspecialchars(substr($post['description'], 0, 100)); ?>
                                                        <?php if (strlen($post['description']) > 100): ?>...<?php endif; ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $post['status']; ?>">
                                                    <?php echo ucfirst($post['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="views-count">
                                                    <i class="icon icon-eye"></i>
                                                    <?php echo number_format($post['views']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="date-info">
                                                    <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="date-info">
                                                    <?php echo date('M d, Y', strtotime($post['updated_at'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?php echo base_url('blog/post/' . $post['slug']); ?>" 
                                                       class="btn btn-sm btn-info" 
                                                       target="_blank" 
                                                       title="View Post">
                                                        <i class="icon icon-eye"></i>
                                                    </a>
                                                    <a href="<?php echo base_url('blog/edit/' . $post['id']); ?>" 
                                                       class="btn btn-sm btn-warning" 
                                                       title="Edit Post">
                                                        <i class="icon icon-edit"></i>
                                                    </a>
                                                    <a href="<?php echo base_url('blog/delete/' . $post['id']); ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       title="Delete Post"
                                                       onclick="return confirm('Are you sure you want to delete this post?')">
                                                        <i class="icon icon-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-wrapper">
                                <nav aria-label="Blog management pagination">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($current_page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($current_page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-posts">
                            <div class="text-center py-5">
                                <i class="icon icon-document" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                                <h3>No blog posts found</h3>
                                <p>Create your first blog post to get started.</p>
                                <a href="<?php echo base_url('blog/create'); ?>" class="btn btn-primary">
                                    <i class="icon icon-plus"></i> Create First Post
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Blog Manage Styles */
.blog-manage-wrapper {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.manage-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 30px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
}

.manage-actions {
    display: flex;
    gap: 15px;
}

.manage-stats {
    display: flex;
    gap: 30px;
}

.stat-item {
    font-size: 14px;
    color: #666;
}

.stat-item strong {
    color: #333;
    font-weight: 600;
}

.alert {
    padding: 15px;
    margin: 20px 30px;
    border: 1px solid transparent;
    border-radius: 5px;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.posts-table {
    padding: 0 30px 30px 30px;
}

.table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: collapse;
}

.table thead th {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    padding: 15px 10px;
    font-weight: 600;
    color: #333;
    text-align: left;
}

.table tbody td {
    padding: 15px 10px;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}

.post-title-cell {
    display: flex;
    align-items: center;
    gap: 15px;
}

.post-thumb {
    width: 50px;
    height: 50px;
    border-radius: 5px;
    object-fit: cover;
    flex-shrink: 0;
}

.post-info h6 {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 5px 0;
    color: #333;
    line-height: 1.3;
}

.post-info p {
    font-size: 12px;
    color: #666;
    margin: 0;
    line-height: 1.4;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.status-published {
    background: #d4edda;
    color: #155724;
}

.status-draft {
    background: #fff3cd;
    color: #856404;
}

.status-archived {
    background: #f8d7da;
    color: #721c24;
}

.views-count {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    color: #666;
}

.date-info {
    font-size: 14px;
    color: #666;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn {
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 12px;
}

.btn-sm {
    padding: 6px 10px;
    font-size: 11px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
    color: white;
    text-decoration: none;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
    color: white;
    text-decoration: none;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-info:hover {
    background-color: #138496;
    color: white;
    text-decoration: none;
}

.btn-warning {
    background-color: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800;
    color: #212529;
    text-decoration: none;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
    color: white;
    text-decoration: none;
}

.pagination-wrapper {
    padding: 20px 30px;
    border-top: 1px solid #eee;
    background: #f8f9fa;
}

.pagination {
    margin: 0;
}

.page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
    padding: 8px 16px;
}

.page-link:hover {
    color: #0056b3;
    background-color: #e9ecef;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

.no-posts {
    padding: 60px 30px;
}

/* Responsive */
@media (max-width: 768px) {
    .manage-header {
        flex-direction: column;
        gap: 20px;
        align-items: stretch;
    }
    
    .manage-actions {
        justify-content: center;
    }
    
    .manage-stats {
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .posts-table {
        padding: 0 15px 30px 15px;
        overflow-x: auto;
    }
    
    .table {
        min-width: 600px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-sm {
        padding: 8px 12px;
        font-size: 12px;
    }
}
</style>
