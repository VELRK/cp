<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Reel Video</h2>

    <div class="card">
        <div class="card-body">
            <form method="post" id="reelForm">
                <div class="mb-3">
                    <label class="form-label">Current Video</label>
                    <?php if($reel->videoUrl): ?>
                        <div class="mb-2">
                            <a href="<?php echo $reel->videoUrl; ?>" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-video me-1"></i>View Current Video
                            </a>
                        </div>
                    <?php endif; ?>
                    <label class="form-label">YouTube Video URL *</label>
                    <input type="url" class="form-control" name="videoUrl" id="videoUrlInput" value="<?php echo htmlspecialchars($reel->videoUrl); ?>" placeholder="https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID" required>
                    <small class="text-muted">Enter YouTube video URL. Thumbnail will be auto-generated.</small>
                    <div id="videoPreview" class="mt-3"></div>
                    <div id="thumbnailPreview" class="mt-3"></div>
                    <input type="hidden" name="thumbnail" id="thumbnailUrlInput" value="<?php echo htmlspecialchars($reel->thumbnail); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" id="titleInput" value="<?php echo htmlspecialchars($reel->title); ?>" placeholder="Video title">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="active" <?php echo $reel->status == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $reel->status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Reel
                    </button>
                    <a href="<?php echo base_url('admin/reels'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function extractYouTubeVideoId(url) {
        if (!url) return null;
        
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
            /youtube\.com\/.*[?&]v=([^&\n?#]+)/
        ];
        
        for (let pattern of patterns) {
            const match = url.match(pattern);
            if (match && match[1]) {
                return match[1];
            }
        }
        return null;
    }
    
    function getYouTubeThumbnail(videoId) {
        return `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
    }
    
    // Load preview on page load if URL exists
    document.addEventListener('DOMContentLoaded', function() {
        const videoUrlInput = document.getElementById('videoUrlInput');
        if (videoUrlInput && videoUrlInput.value) {
            videoUrlInput.dispatchEvent(new Event('input'));
        }
    });
    
    document.getElementById('videoUrlInput')?.addEventListener('input', function(e) {
        const url = this.value.trim();
        const videoPreview = document.getElementById('videoPreview');
        const thumbnailPreview = document.getElementById('thumbnailPreview');
        const thumbnailUrlInput = document.getElementById('thumbnailUrlInput');
        const titleInput = document.getElementById('titleInput');
        
        if (url) {
            const videoId = extractYouTubeVideoId(url);
            
            if (videoId) {
                const embedUrl = `https://www.youtube.com/embed/${videoId}`;
                const iframe = document.createElement('iframe');
                iframe.src = embedUrl;
                iframe.width = '100%';
                iframe.height = '400';
                iframe.frameBorder = '0';
                iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                iframe.allowFullscreen = true;
                iframe.style.borderRadius = '8px';
                videoPreview.innerHTML = '';
                videoPreview.appendChild(iframe);
                
                const thumbnailUrl = getYouTubeThumbnail(videoId);
                thumbnailUrlInput.value = thumbnailUrl;
                
                const img = document.createElement('img');
                img.src = thumbnailUrl;
                img.className = 'img-thumbnail mt-2';
                img.style.maxWidth = '300px';
                img.style.borderRadius = '8px';
                img.onerror = function() {
                    this.src = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
                };
                
                const label = document.createElement('div');
                label.className = 'badge bg-success mt-2';
                label.innerHTML = '<i class="fas fa-check me-1"></i>Auto-generated thumbnail from YouTube';
                
                thumbnailPreview.innerHTML = '';
                thumbnailPreview.appendChild(label);
                thumbnailPreview.appendChild(img);
                
                fetch(`https://www.youtube.com/oembed?url=${encodeURIComponent(url)}&format=json`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.title && !titleInput.value) {
                            titleInput.value = data.title;
                        }
                    })
                    .catch(err => console.log('Could not fetch video title'));
            } else {
                videoPreview.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Please enter a valid YouTube URL</div>';
            }
        } else {
            videoPreview.innerHTML = '';
            thumbnailPreview.innerHTML = '';
        }
    });
</script>
