<!-- Blog Create Header -->
<section class="flat-title-page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="page-title-heading">
                    <h1 class="heading">Create Blog Post</h1>
                    <p class="sub-heading">Share your insights with our community</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Create Form -->
<section class="flat-blog-create">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="blog-form-wrapper">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($upload_error)): ?>
                        <div class="alert alert-danger">
                            <strong>Upload Error:</strong> <?php echo $upload_error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success">
                            <?php echo $this->session->flashdata('success'); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo base_url('blog/create'); ?>" method="post" enctype="multipart/form-data" id="blogForm">
                        <div class="form-group">
                            <label for="title">Post Title *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   value="<?php echo set_value('title'); ?>" 
                                   placeholder="Enter blog post title"
                                   required>
                            <?php echo form_error('title', '<div class="text-danger">', '</div>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Brief description of your blog post"
                                      required><?php echo set_value('description'); ?></textarea>
                            <?php echo form_error('description', '<div class="text-danger">', '</div>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="content">Content *</label>
                            <textarea class="form-control" 
                                      id="content" 
                                      name="content" 
                                      rows="15" 
                                      placeholder="Write your blog post content here..."
                                      required><?php echo set_value('content'); ?></textarea>
                            <?php echo form_error('content', '<div class="text-danger">', '</div>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="featured_image">Featured Image</label>
                            <input type="file" 
                                   class="form-control-file" 
                                   id="featured_image" 
                                   name="featured_image" 
                                   accept="image/*">
                            <small class="form-text text-muted">
                                Supported formats: JPG, PNG, GIF, WebP. Max size: 2MB. Recommended: 800x600px
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="draft" <?php echo set_select('status', 'draft'); ?>>Draft</option>
                                <option value="published" <?php echo set_select('status', 'published'); ?>>Published</option>
                                <option value="archived" <?php echo set_select('status', 'archived'); ?>>Archived</option>
                            </select>
                            <?php echo form_error('status', '<div class="text-danger">', '</div>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="meta_title">SEO Title</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="meta_title" 
                                   name="meta_title" 
                                   value="<?php echo set_value('meta_title'); ?>" 
                                   placeholder="SEO optimized title (optional)">
                            <small class="form-text text-muted">
                                If left empty, the post title will be used
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="meta_description">SEO Description</label>
                            <textarea class="form-control" 
                                      id="meta_description" 
                                      name="meta_description" 
                                      rows="3" 
                                      placeholder="SEO description for search engines (optional)"><?php echo set_value('meta_description'); ?></textarea>
                            <small class="form-text text-muted">
                                Recommended length: 150-160 characters
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="meta_keywords">SEO Keywords</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="meta_keywords" 
                                   name="meta_keywords" 
                                   value="<?php echo set_value('meta_keywords'); ?>" 
                                   placeholder="Keywords separated by commas (optional)">
                            <small class="form-text text-muted">
                                Example: real estate, home buying, market trends
                            </small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="icon icon-save"></i> Create Post
                            </button>
                            <a href="<?php echo base_url('blog/manage'); ?>" class="btn btn-secondary">
                                <i class="icon icon-arrow-left"></i> Back to Manage
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="blog-help-sidebar">
                    <div class="help-widget">
                        <h4>Writing Tips</h4>
                        <ul>
                            <li>Use a compelling title that grabs attention</li>
                            <li>Write a clear, engaging description</li>
                            <li>Structure your content with headings</li>
                            <li>Use bullet points for easy reading</li>
                            <li>Include relevant images</li>
                            <li>Proofread before publishing</li>
                        </ul>
                    </div>

                    <div class="help-widget">
                        <h4>SEO Guidelines</h4>
                        <ul>
                            <li>Include target keywords naturally</li>
                            <li>Write meta descriptions 150-160 chars</li>
                            <li>Use descriptive image alt text</li>
                            <li>Create internal links to other posts</li>
                            <li>Use heading tags (H1, H2, H3)</li>
                        </ul>
                    </div>

                    <div class="help-widget">
                        <h4>Image Guidelines</h4>
                        <ul>
                            <li>Use high-quality images</li>
                            <li>Recommended size: 800x600px</li>
                            <li>Supported formats: JPG, PNG, GIF, WebP</li>
                            <li>Max file size: 2MB</li>
                            <li>Use descriptive filenames</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Blog Create Styles */
.blog-form-wrapper {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
}

.form-control {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 12px 15px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.form-control-file {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 8px;
}

.form-text {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.text-danger {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 5px;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
    color: white;
}

/* Help Sidebar */
.blog-help-sidebar {
    padding-left: 30px;
}

.help-widget {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 25px;
}

.help-widget h4 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.help-widget ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.help-widget li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
    color: #666;
}

.help-widget li:last-child {
    border-bottom: none;
}

.help-widget li:before {
    content: "✓";
    color: #007bff;
    font-weight: bold;
    margin-right: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .blog-help-sidebar {
        padding-left: 0;
        margin-top: 30px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    const title = this.value;
    const slug = title.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
    
    // You can display the generated slug to the user if needed
    console.log('Generated slug:', slug);
});

// Character counter for meta description
document.getElementById('meta_description').addEventListener('input', function() {
    const length = this.value.length;
    const maxLength = 160;
    
    // Add visual feedback for optimal length
    if (length > maxLength) {
        this.style.borderColor = '#dc3545';
    } else if (length >= 150) {
        this.style.borderColor = '#28a745';
    } else {
        this.style.borderColor = '#ddd';
    }
});

// Form validation
document.getElementById('blogForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const description = document.getElementById('description').value.trim();
    const content = document.getElementById('content').value.trim();
    
    if (title.length < 5) {
        alert('Title must be at least 5 characters long.');
        e.preventDefault();
        return;
    }
    
    if (description.length < 10) {
        alert('Description must be at least 10 characters long.');
        e.preventDefault();
        return;
    }
    
    if (content.length < 50) {
        alert('Content must be at least 50 characters long.');
        e.preventDefault();
        return;
    }
});
</script>
