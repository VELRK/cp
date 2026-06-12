<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-plus me-2"></i>Create Video</h2>

    <div class="card">
        <div class="card-body">
            <form method="post" id="videoForm">
                <div class="mb-3">
                    <label class="form-label">YouTube Video URL *</label>
                    <input type="url" class="form-control" name="videoUrl" id="videoUrlInput" placeholder="https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID" required>
                    <small class="text-muted">Enter YouTube video URL. Thumbnail will be auto-generated.</small>
                    <div id="videoPreview" class="mt-3"></div>
                    <div id="thumbnailPreview" class="mt-3"></div>
                    <input type="hidden" name="thumbnail" id="thumbnailUrlInput">
                </div>

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" id="titleInput" placeholder="Video title">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Video
                    </button>
                    <a href="<?php echo base_url('admin/videos'); ?>" class="btn btn-secondary">
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
        // Try maxresdefault first, fallback to hqdefault
        return `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
    }
    
    document.getElementById('videoUrlInput')?.addEventListener('input', function(e) {
        const url = this.value.trim();
        const videoPreview = document.getElementById('videoPreview');
        const thumbnailPreview = document.getElementById('thumbnailPreview');
        const thumbnailUrlInput = document.getElementById('thumbnailUrlInput');
        const titleInput = document.getElementById('titleInput');
        
        videoPreview.innerHTML = '';
        thumbnailPreview.innerHTML = '';
        
        if (url) {
            const videoId = extractYouTubeVideoId(url);
            
            if (videoId) {
                // Show video embed preview
                const embedUrl = `https://www.youtube.com/embed/${videoId}`;
                const iframe = document.createElement('iframe');
                iframe.src = embedUrl;
                iframe.width = '100%';
                iframe.height = '400';
                iframe.frameBorder = '0';
                iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                iframe.allowFullscreen = true;
                iframe.style.borderRadius = '8px';
                videoPreview.appendChild(iframe);
                
                // Get and display thumbnail
                const thumbnailUrl = getYouTubeThumbnail(videoId);
                thumbnailUrlInput.value = thumbnailUrl;
                
                const img = document.createElement('img');
                img.src = thumbnailUrl;
                img.className = 'img-thumbnail mt-2';
                img.style.maxWidth = '300px';
                img.style.borderRadius = '8px';
                img.onerror = function() {
                    // Fallback to hqdefault if maxresdefault fails
                    this.src = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
                };
                
                const label = document.createElement('div');
                label.className = 'badge bg-success mt-2';
                label.innerHTML = '<i class="fas fa-check me-1"></i>Auto-generated thumbnail from YouTube';
                
                thumbnailPreview.appendChild(label);
                thumbnailPreview.appendChild(img);
                
                // Try to fetch video title from YouTube oEmbed (optional)
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
        }
    });
</script>
