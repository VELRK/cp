<section class="flat-title-page flat-title-page--blog">
    <div class="container">
        <div class="breadcrumb-content">
            <ul class="breadcrumb">
                <li><a href="<?php echo base_url(); ?>" class="text-white">Home</a></li>
                <li class="text-white">/ Pages</li>
                <li class="text-white">/ Latest News</li>
            </ul>
            <h1 class="text-center text-white title">Latest News</h1>
        </div>
    </div>
</section>
<!-- End Page Title -->

<!-- Section Blog Grid -->
<section class="flat-section">
    <div class="container">
        <div class="row">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $index => $post): ?>
                    <div class="col-lg-4 col-md-6">
                        <a href="<?php echo base_url('blog/post/' . (isset($post['id']) && !empty($post['id']) ? $post['id'] : (isset($post['slug']) ? $post['slug'] : ''))); ?>" class="flat-blog-item hover-img">
                            <div class="img-style">
                                <?php if (!empty($post['coverImageUrl'])): ?>
                                    <img class="lazyload" style="width: 100%; height: 250px; object-fit: cover;" data-src="<?php echo $post['coverImageUrl']; ?>" 
                                         src="<?php echo $post['coverImageUrl']; ?>" 
                                         alt="<?php echo htmlspecialchars($post['title']); ?>">
                                <?php endif; ?>
                                <span class="date-post"><?php 
                                    $date = !empty($post['publishedDate']) ? $post['publishedDate'] : (isset($post['created_at']) ? $post['created_at'] : date('Y-m-d'));
                                    echo date('F d, Y', strtotime($date)); 
                                ?></span>
                            </div>
                            <div class="content-box">
                                <div class="post-author">
                                    <?php
                                    $rawAuthor = isset($post['authorName']) ? trim((string) $post['authorName']) : '';
                                    if ($rawAuthor === '' && isset($post['author'])) {
                                        $rawAuthor = trim((string) $post['author']);
                                    }
                                    $showAuthor = ($rawAuthor !== '' && strcasecmp($rawAuthor, 'Admin') !== 0);
                                    ?>
                                    <?php if ($showAuthor): ?>
                                    <span class="fw-6"><?php echo htmlspecialchars($rawAuthor); ?></span>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($post['category'] ?? 'Real Estate'); ?></span>
                                </div>
                                <h5 class="title link"><?php echo htmlspecialchars($post['title']); ?></h5>
                                <p class="description">
                                    <?php 
                                    // Limit content to 70 words
                                    $content = isset($post['content']) ? strip_tags($post['content']) : (isset($post['shortDescription']) ? $post['shortDescription'] : '');
                                    if (!empty($content)) {
                                        $words = explode(' ', $content);
                                        $wordLimit = 30;
                                        if (count($words) > $wordLimit) {
                                            $limitedWords = array_slice($words, 0, $wordLimit);
                                            $limitedContent = implode(' ', $limitedWords);
                                            echo htmlspecialchars($limitedContent) . ' ...';
                                        } else {
                                            echo htmlspecialchars($content);
                                        }
                                    }
                                    ?>
                                </p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<!-- End Section Blog Grid -->

