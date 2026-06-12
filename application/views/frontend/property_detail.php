
<style>
/* Main slider - full width */
.sw-single .image-sw-single {
    width: 100% !important;
    max-width: 100% !important;
}
.sw-single .image-sw-single img {
    width: 100% !important;
    height: auto !important;
    object-fit: cover;
}

/* Thumbnail slider images - make them smaller */
.thumbs-sw-pagi .image-sw-single {
    width: 150px !important;
    height: 100px !important;
    object-fit: cover;
}
.thumbs-sw-pagi .swiper-slide {
    width: auto !important;
}
.thumbs-sw-pagi .image-sw-single img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
}

.info-box {
    background: transparent !important;
    border-radius: 12px;
    margin-bottom: 15px;
    display: block;
    width: 100%;
}

/* Ensure Features and Location are on separate lines */
.content-bottom .box-left {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.content-bottom .box-left .info-box {
    display: block;
    width: 100%;
    margin-bottom: 15px;
}
</style>


<?php if (!isset($property) || empty($property)): ?>
<div class="container" style="padding: 50px 0; text-align: center;">
    <h2>Property Not Found</h2>
    <p>The property you are looking for does not exist.</p>
    <a href="<?php echo base_url(); ?>" class="btn btn-primary">Go to Home</a>
</div>
<?php else: ?>
<div class="flat-section-v4">
                <div class="container">
                    <div class="header-property-detail">
                        <div class="content-top d-flex justify-content-between align-items-center">
                            <h3 class="title link fw-8"><?php echo isset($property['propertyName']) ? htmlspecialchars($property['propertyName']) : 'Property Name'; ?></h3>
                            <div class="box-price d-flex align-items-end">
                                <h3 class="fw-8"><?php
                                    $pd = dvm_property_price_display($property);
                                    echo $pd !== '' ? $pd : 'Price on request';
                                ?></h3>
                                <?php if (isset($property['lease']) && !empty($property['lease'])): ?>
                                <span class="body-1 text-variant-1">/<?php echo htmlspecialchars($property['lease']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="content-bottom">
                            <div class="box-left">
                                <?php 
                                // Parse features from JSON if it's a string
                                $features = array();
                                if (isset($property['features'])) {
                                    if (is_string($property['features'])) {
                                        $features = json_decode($property['features'], true);
                                        if (!is_array($features)) {
                                            $features = array();
                                        }
                                    } elseif (is_array($property['features'])) {
                                        $features = $property['features'];
                                    }
                                }
                                ?>
                                <div class="info-box">
                                    <div class="label">Location</div>
                                    <p class="meta-item"><span class="icon icon-mapPin"></span><span
                                            class="text-variant-1"><?php 
                                            $locationName = '';
                                            $cityName = '';
                                            if (isset($property['locationInfo']) && is_array($property['locationInfo']) && isset($property['locationInfo']['locationName']) && !empty($property['locationInfo']['locationName'])) {
                                                $locationName = htmlspecialchars($property['locationInfo']['locationName']);
                                            }
                                            if (isset($property['cityInfo']) && is_array($property['cityInfo']) && isset($property['cityInfo']['cityName']) && !empty($property['cityInfo']['cityName'])) {
                                                $cityName = htmlspecialchars($property['cityInfo']['cityName']);
                                            }
                                            echo $locationName . (!empty($locationName) && !empty($cityName) ? ', ' : '') . $cityName;
                                            ?></span></p>
                                </div>
                            </div>

                            <div class="enquiry-button-container">
                                <button class="form-wg tf-btn primary enquiry-btn" 
                                        name="submit" 
                                        type="button"
                                        data-property-id="<?php echo isset($property['id']) ? htmlspecialchars($property['id']) : ''; ?>"
                                        data-property-name="<?php echo isset($property['propertyName']) ? htmlspecialchars($property['propertyName']) : ''; ?>"
                                        data-property-price="<?php echo htmlspecialchars(dvm_property_price_data_attr($property)); ?>"
                                        data-cover-image="<?php echo isset($property['propertiesMainImage']) ? htmlspecialchars($property['propertiesMainImage']) : (isset($property['projectThumbnailImage']) ? htmlspecialchars($property['projectThumbnailImage']) : ''); ?>">
                                    <span>Enquiry</span>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="container">

                            <?php 
                            // Only show video section if projectVideoUrl exists and is not empty
                            // All videos are YouTube URLs now
                            if (isset($property['video']) && !empty($property['video'])):
                                $videoUrl = $property['video'];
                                
                                // Check if it's a YouTube URL
                                $isYouTube = (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false);
                                
                                // Generate embed URL if YouTube
                                $embedUrl = '';
                                if ($isYouTube && !empty($videoUrl)) {
                                    $patterns = array(
                                        '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/',
                                        '/youtube\.com\/.*[?&]v=([^&\n?#]+)/'
                                    );
                                    foreach ($patterns as $pattern) {
                                        if (preg_match($pattern, $videoUrl, $matches)) {
                                            $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                                            break;
                                        }
                                    }
                                }
                                
                                // If video URL doesn't start with http:// or https://, treat as site-relative and prepend base_url
                                if (!$isYouTube && !empty($videoUrl) && !preg_match('/^https?:\/\//', $videoUrl)) {
                                    $videoUrl = base_url($videoUrl);
                                }
                            ?>
                            <div class="single-property-element single-property-video" style="margin-bottom: 30px;">
                                <h5 class="title fw-6">Video</h5>
                                <div class="img-video">
                                    <?php 
                                    $thumbnail = '';
                                    if (isset($property['main_image']) && !empty($property['main_image'])) {
                                        $thumbnail = $property['main_image'];
                                    } elseif (isset($property['main_image']) && !empty($property['main_image'])) {
                                        $thumbnail = $property['main_image'];
                                    } elseif (isset($property['main_image']) && !empty($property['main_image'])) {
                                        $thumbnail = $property['main_image'];
                                    }
                                    if (!empty($thumbnail)):
                                        // If image path doesn't start with http:// or https://, add base_url
                                        if (!preg_match('/^https?:\/\//', $thumbnail)) {
                                            $thumbnail = base_url($thumbnail);
                                        }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="img-video" style="width: 100%; height: auto; border-radius: 12px;">
                                    <?php endif; ?>
                                    <button type="button" class="btn-video" data-bs-toggle="modal" data-bs-target="#propertyVideoModal" data-video-url="<?php echo htmlspecialchars($videoUrl); ?>" data-is-youtube="<?php echo $isYouTube ? 'true' : 'false'; ?>" data-embed-url="<?php echo htmlspecialchars($embedUrl); ?>">
                                        <span class="icon icon-play"></span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Video Modal -->
                            <div class="modal fade" id="propertyVideoModal" tabindex="-1" aria-labelledby="propertyVideoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content" style="background: #000; border: none; border-radius: 12px;">
                                        <div class="modal-header" style="border-bottom: 1px solid #333; padding: 15px 20px;">
                                            <h5 class="modal-title text-white" id="propertyVideoModalLabel">Property Video</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body" style="padding: 0; position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
                                            <!-- YouTube iframe for YouTube videos -->
                                            <iframe id="propertyVideoIframe" 
                                                    src="" 
                                                    frameborder="0" 
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                    allowfullscreen
                                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #000; display: none;">
                                            </iframe>
                                            <!-- Video player for server-uploaded videos (fallback) -->
                                            <video id="propertyVideoPlayer" controls autoplay playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #000; display: none;">
                                                <source src="" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <script>
                            // Handle video modal - supports both YouTube and server-uploaded videos
                            document.addEventListener('DOMContentLoaded', function() {
                                var videoModal = document.getElementById('propertyVideoModal');
                                var videoPlayer = document.getElementById('propertyVideoPlayer');
                                var videoIframe = document.getElementById('propertyVideoIframe');
                                
                                if (videoModal) {
                                    // When modal is shown, load and play video
                                    videoModal.addEventListener('shown.bs.modal', function (e) {
                                        // Get the button that triggered the modal
                                        var triggerButton = e.relatedTarget;
                                        if (!triggerButton) {
                                            // Try to find the button by selector as fallback
                                            triggerButton = document.querySelector('.btn-video[data-bs-target="#propertyVideoModal"]');
                                        }
                                        
                                        if (!triggerButton) {
                                            console.error('Video button not found');
                                            return;
                                        }
                                        
                                        var videoUrl = triggerButton.getAttribute('data-video-url') || '';
                                        var isYouTubeAttr = triggerButton.getAttribute('data-is-youtube');
                                        var embedUrlAttr = triggerButton.getAttribute('data-embed-url') || '';
                                        
                                        // Determine if YouTube
                                        var isYouTube = false;
                                        if (isYouTubeAttr === 'true' || isYouTubeAttr === '1') {
                                            isYouTube = true;
                                        } else if (videoUrl) {
                                            isYouTube = videoUrl.indexOf('youtube.com') !== -1 || videoUrl.indexOf('youtu.be') !== -1;
                                        }
                                        
                                        // Hide both initially
                                        if (videoIframe) videoIframe.style.display = 'none';
                                        if (videoPlayer) videoPlayer.style.display = 'none';
                                        
                                        if (isYouTube && videoIframe) {
                                            // Use embed URL from data attribute if available, otherwise generate it
                                            var embedUrl = embedUrlAttr;
                                            if (!embedUrl && videoUrl) {
                                                // Extract YouTube video ID and create embed URL
                                                var videoId = '';
                                                var patterns = [
                                                    /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
                                                    /youtube\.com\/.*[?&]v=([^&\n?#]+)/
                                                ];
                                                
                                                for (var i = 0; i < patterns.length; i++) {
                                                    var match = videoUrl.match(patterns[i]);
                                                    if (match && match[1]) {
                                                        videoId = match[1];
                                                        break;
                                                    }
                                                }
                                                
                                                if (videoId) {
                                                    embedUrl = 'https://www.youtube.com/embed/' + videoId;
                                                }
                                            }
                                            
                                            if (embedUrl) {
                                                // Add autoplay parameter
                                                embedUrl = embedUrl + (embedUrl.indexOf('?') > -1 ? '&' : '?') + 'autoplay=1&rel=0';
                                                videoIframe.src = embedUrl;
                                                videoIframe.style.display = 'block';
                                                console.log('YouTube video loaded:', embedUrl);
                                            } else {
                                                console.error('Could not generate YouTube embed URL from:', videoUrl);
                                            }
                                        } else if (videoPlayer && videoUrl) {
                                            // Regular video file
                                            videoPlayer.querySelector('source').src = videoUrl;
                                            videoPlayer.load();
                                            videoPlayer.style.display = 'block';
                                            var playPromise = videoPlayer.play();
                                            if (playPromise !== undefined) {
                                                playPromise.then(function() {
                                                    console.log('Video playing');
                                                }).catch(function(error) {
                                                    console.log('Autoplay prevented:', error);
                                                });
                                            }
                                        } else {
                                            console.error('No video URL provided or video elements not found');
                                        }
                                    });
                                    
                                    // When modal is hidden, pause and reset video
                                    videoModal.addEventListener('hidden.bs.modal', function () {
                                        // Stop YouTube iframe
                                        if (videoIframe) {
                                            videoIframe.src = '';
                                            videoIframe.style.display = 'none';
                                        }
                                        // Stop regular video
                                        if (videoPlayer) {
                                            videoPlayer.pause();
                                            videoPlayer.currentTime = 0;
                                            videoPlayer.querySelector('source').src = '';
                                            videoPlayer.load();
                                            videoPlayer.style.display = 'none';
                                        }
                                    });
                                }
                            });
                            </script>
                            <?php endif; ?>

                    <div class="single-property-gallery">
                        <div class="position-relative">
                            <div dir="ltr" class="swiper sw-single">
                                <div class="swiper-wrapper">

                                    <?php if (isset($property['projectThumbnailImage']) && !empty($property['projectThumbnailImage'])): ?>
                                    <?php 
                                    $thumbImage = $property['projectThumbnailImage'];
                                    // If image path doesn't start with http:// or https://, add base_url
                                    if (!preg_match('/^https?:\/\//', $thumbImage)) {
                                        $thumbImage = base_url($thumbImage);
                                    }
                                    ?>
                                    <div class="swiper-slide">
                                        <div class="image-sw-single">
                                            <img src="<?php echo htmlspecialchars($thumbImage); ?>" alt="images">
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($property['propertySliderImages']) && is_array($property['propertySliderImages']) && !empty($property['propertySliderImages'])): ?>
                                    <?php foreach ($property['propertySliderImages'] as $image): ?>
                                        <?php if (!empty($image)): ?>
                                        <?php 
                                        // If image path doesn't start with http:// or https://, add base_url
                                        if (!preg_match('/^https?:\/\//', $image)) {
                                            $image = base_url($image);
                                        }
                                        ?>
                                        <div class="swiper-slide">
                                            <div class="image-sw-single">
                                                <img src="<?php echo htmlspecialchars($image); ?>" alt="images">
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                
                                </div>

                            </div>
                            <div class="box-navigation">
                                <div class="navigation swiper-nav-next nav-next-single"><span
                                        class="icon icon-arr-l"></span></div>
                                <div class="navigation swiper-nav-prev nav-prev-single"><span
                                        class="icon icon-arr-r"></span></div>
                            </div>
                        </div>
                        <div dir="ltr" class="swiper thumbs-sw-pagi">
                            <div class="swiper-wrapper">
                            <?php if (isset($property['propertySliderImages']) && is_array($property['propertySliderImages']) && !empty($property['propertySliderImages'])): ?>
                            <?php foreach ($property['propertySliderImages'] as $image): ?>
                                        <?php if (!empty($image)): ?>
                                        <?php 
                                        // If image path doesn't start with http:// or https://, add base_url
                                        if (!preg_match('/^https?:\/\//', $image)) {
                                            $image = base_url($image);
                                        }
                                        ?>
                                        <div class="swiper-slide">
                                            <div class="image-sw-single">
                                                <img src="<?php echo htmlspecialchars($image); ?>" alt="images">
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



<section class="flat-section-v3 flat-property-detail">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-8 col-lg-7">
                            <?php if (isset($property['desc']) && !empty($property['desc'])): ?>
                            <div class="single-property-element single-property-desc">
                                <h5 class="fw-6 title">Description</h5>
                                <p class="text-variant-1"><?php echo htmlspecialchars($property['desc']); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($features)): ?>
                            <div class="single-property-element">
                                <h5 class="fw-6 title">Features</h5>
                                <div class="features-chips" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px;">
                                    <?php foreach ($features as $feature): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 7px; background: linear-gradient(135deg, #f0f3ff 0%, #e6ebff 100%); border: 1px solid #c5ceff; color: #3a56e4; border-radius: 20px; padding: 7px 16px; font-size: 13px; font-weight: 500; box-shadow: 0 1px 4px rgba(58,86,228,0.10);">
                                        <i class="icon icon-check" style="font-size: 11px;"></i>
                                        <?php echo htmlspecialchars($feature); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                         



                            <?php 
                            // Parse nearby places from JSON if it's a string
                            $nearby_places = array();
                            if (isset($property['nearby'])) {
                                $nearbyRaw = $property['nearby'];
                                
                                // Handle different formats
                                if (is_string($nearbyRaw)) {
                                    // Try to decode JSON
                                    $decoded = json_decode($nearbyRaw, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $nearby_places = $decoded;
                                    } else {
                                        // If not valid JSON, try to parse as empty array
                                        $nearby_places = array();
                                    }
                                } elseif (is_array($nearbyRaw)) {
                                    $nearby_places = $nearbyRaw;
                                }
                            }
                            
                            // Filter out empty entries
                            $nearby_places = array_filter($nearby_places, function($place) {
                                return !empty($place) && (isset($place['title']) || isset($place['name']));
                            });
                            ?>
                            <?php if (!empty($nearby_places)): ?>
                            <div class="single-property-element single-property-nearby">
                                <h5 class="title fw-6">Nearby Places</h5>
                                <?php
                                $grouped = array();
                                foreach (array_values($nearby_places) as $place) {
                                    $cat = !empty($place['category']) ? $place['category'] : (!empty($place['title']) ? $place['title'] : 'Other');
                                    $grouped[$cat][] = $place;
                                }
                                ?>
                                <div class="nearby-groups">
                                    <?php foreach ($grouped as $category => $places): ?>
                                    <div class="nearby-group">
                                        <div class="nearby-group-heading">
                                            <span class="nearby-group-icon">
                                                <?php
                                                $icons = [
                                                    'School'          => 'icon-listing',
                                                    'College'         => 'icon-listing',
                                                    'University'      => 'icon-listing',
                                                    'Hospital'        => 'icon-hospital',
                                                    'Clinic'          => 'icon-hospital',
                                                    'Pharmacy'        => 'icon-package',
                                                    'Bank'            => 'icon-lockbox',
                                                    'ATM'             => 'icon-lockbox',
                                                    'Supermarket'     => 'icon-package',
                                                    'Shopping Mall'   => 'icon-package',
                                                    'Restaurant'      => 'icon-coffee',
                                                    'Hotel'           => 'icon-home1',
                                                    'Park'            => 'icon-carbon',
                                                    'Gym'             => 'icon-ruler',
                                                    'Temple'          => 'icon-home',
                                                    'Church'          => 'icon-home',
                                                    'Mosque'          => 'icon-home',
                                                    'Bus Stop'        => 'icon-location',
                                                    'Metro Station'   => 'icon-location',
                                                    'Railway Station' => 'icon-location',
                                                    'Airport'         => 'icon-airplane-landing',
                                                    'Police Station'  => 'icon-security',
                                                    'Post Office'     => 'icon-mail',
                                                    'Petrol Pump'     => 'icon-location',
                                                ];
                                                $icon = isset($icons[$category]) ? $icons[$category] : 'icon-mapPin';
                                                ?>
                                                <span class="<?php echo $icon; ?>"></span>
                                            </span>
                                            <span><?php echo htmlspecialchars($category); ?></span>
                                        </div>
                                        <ul class="nearby-place-list">
                                            <?php foreach ($places as $place):
                                                $name     = !empty($place['name'])     ? $place['name']     : '';
                                                $distance = !empty($place['distance']) ? $place['distance'] : '';
                                                if (empty($name)) continue;
                                            ?>
                                            <li class="nearby-place-item-row">
                                                <span class="nearby-place-dot"></span>
                                                <span class="nearby-place-name"><?php echo htmlspecialchars($name); ?></span>
                                                <?php if (!empty($distance)): ?>
                                                <span class="nearby-place-dist"><?php echo htmlspecialchars($distance); ?> km</span>
                                                <?php endif; ?>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php 
                            // Also show propertiesDetails/amenities if available (for backward compatibility)
                            if (isset($property['propertiesDetails']) && !empty($property['propertiesDetails']) && is_array($property['propertiesDetails'])): ?>
                            <?php foreach ($property['propertiesDetails'] as $detailItem): ?>
                            <?php if (isset($detailItem['header']) && isset($detailItem['properties']) && !empty($detailItem['properties'])): ?>
                            <div class="single-property-element single-property-nearby">
                                <h5 class="title fw-6"><?php echo htmlspecialchars($detailItem['header']); ?></h5>
                                <p>Explore nearby amenities to precisely locate your property and identify surrounding
                                    conveniences, providing a comprehensive overview of the living environment and the
                                    property's convenience.</p>
                                <div class="row box-nearby">
                                    <?php 
                                    // Split properties into 2 columns
                                    $properties = $detailItem['properties'];
                                    $totalItems = count($properties);
                                    $halfCount = ceil($totalItems / 2);
                                    $leftColumn = array_slice($properties, 0, $halfCount);
                                    $rightColumn = array_slice($properties, $halfCount);
                                    ?>
                                    <div class="col-md-5">
                                        <ul class="box-left">
                                            <?php foreach ($leftColumn as $prop): ?>
                                            <?php if (isset($prop['key']) && isset($prop['value'])): ?>
                                            <li class="item-nearby">
                                                <span class="label"><?php echo htmlspecialchars($prop['key']); ?>:</span>
                                                <span class="fw-7"><?php echo htmlspecialchars($prop['value']); ?></span>
                                            </li>
                                            <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="col-md-5">
                                        <ul class="box-right">
                                            <?php foreach ($rightColumn as $prop): ?>
                                            <?php if (isset($prop['key']) && isset($prop['value'])): ?>
                                            <li class="item-nearby">
                                                <span class="label"><?php echo htmlspecialchars($prop['key']); ?>:</span>
                                                <span class="fw-7"><?php echo htmlspecialchars($prop['value']); ?></span>
                                            </li>
                                            <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <?php endif; ?>                      
                            <div class="single-property-element single-property-map">
                                <h5 class="title fw-6">Map location</h5>
                                <div class="map-container-fullwidth">
                                    <?php 
                                    // Priority 1: Use locationUrl if available (direct iframe URL)
                                    // Only use if it's already an embed URL to avoid X-Frame-Options errors
                                    $useLocationUrl = false;
                                    
                                    if (isset($property['locationUrl']) && !empty($property['locationUrl'])):
                                        $locationUrl = trim($property['locationUrl']);
                                        
                                        // Only use locationUrl if it's already an embed URL (contains /embed)
                                        // This ensures it can be embedded in an iframe
                                        if (strpos($locationUrl, '/embed') !== false || strpos($locationUrl, 'maps/embed') !== false || strpos($locationUrl, 'embed') !== false):
                                            $useLocationUrl = true;
                                    ?>
                                    <iframe 
                                        width="100%" 
                                        height="478" 
                                        style="border:0" 
                                        loading="lazy" 
                                        allowfullscreen
                                        src="<?php echo htmlspecialchars($locationUrl); ?>">
                                    </iframe>
                                    <?php 
                                        endif;
                                    endif;
                                    
                                    // Only show fallback options if locationUrl wasn't used
                                    if (!$useLocationUrl):
                                        // Priority 2: Check for latitude and longitude for Google Maps embed
                                        $latitude = '';
                                        $longitude = '';
                                        $address = '';
                                        
                                        if (isset($property['latitude']) && !empty($property['latitude'])) {
                                            $latitude = $property['latitude'];
                                        } elseif (isset($property['lat']) && !empty($property['lat'])) {
                                            $latitude = $property['lat'];
                                        }
                                        
                                        if (isset($property['longitude']) && !empty($property['longitude'])) {
                                            $longitude = $property['longitude'];
                                        } elseif (isset($property['lng']) && !empty($property['lng'])) {
                                            $longitude = $property['lng'];
                                        } elseif (isset($property['lon']) && !empty($property['lon'])) {
                                            $longitude = $property['lon'];
                                        }
                                        
                                        // Build address string for map
                                        $addressParts = array();
                                        if (isset($property['locationInfo']) && is_array($property['locationInfo']) && isset($property['locationInfo']['locationName']) && !empty($property['locationInfo']['locationName'])) {
                                            $addressParts[] = $property['locationInfo']['locationName'];
                                        }
                                        if (isset($property['cityInfo']) && is_array($property['cityInfo']) && isset($property['cityInfo']['cityName']) && !empty($property['cityInfo']['cityName'])) {
                                            $addressParts[] = $property['cityInfo']['cityName'];
                                        }
                                        if (isset($property['state']) && !empty($property['state'])) {
                                            $addressParts[] = $property['state'];
                                        }
                                        $address = implode(', ', $addressParts);
                                        
                                        // If we have coordinates, use Google Maps embed (without API key)
                                        if (!empty($latitude) && !empty($longitude)):
                                    ?>
                                    <iframe 
                                        width="100%" 
                                        height="478" 
                                        style="border:0" 
                                        loading="lazy" 
                                        allowfullscreen
                                        src="https://www.google.com/maps?q=<?php echo urlencode($latitude . ',' . $longitude); ?>&output=embed">
                                    </iframe>
                                    <?php 
                                    // If we have address but no coordinates, use address search (without API key)
                                    elseif (!empty($address)):
                                    ?>
                                    <iframe 
                                        width="100%" 
                                        height="478" 
                                        style="border:0" 
                                        loading="lazy" 
                                        allowfullscreen
                                        src="https://www.google.com/maps?q=<?php echo urlencode($address); ?>&output=embed">
                                    </iframe>
                                    <?php 
                                    // Use static map image if available
                                    elseif (isset($property['mapImageUrl']) && !empty($property['mapImageUrl'])):
                                        $mapImage = $property['mapImageUrl'];
                                        // If image path doesn't start with http:// or https://, add base_url
                                        if (!preg_match('/^https?:\/\//', $mapImage)) {
                                            $mapImage = base_url($mapImage);
                                        }
                                    ?>
                                    <img class="map" 
                                        src="<?php echo htmlspecialchars($mapImage); ?>" 
                                        alt="Map location"
                                        style="width: 100%; height: 478px; object-fit: cover; border: 0;">
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="info-map">
                                    <ul class="box-left">
                                        <?php if (isset($property['address']) && !empty($property['address'])): ?>
                                        <li>
                                            <span class="label fw-6">Address</span>
                                            <div class="text text-variant-1"><?php echo htmlspecialchars($property['address']); ?></div>
                                        </li>
                                        <?php elseif (isset($property['locationInfo']) && is_array($property['locationInfo']) && isset($property['locationInfo']['locationName']) && !empty($property['locationInfo']['locationName'])): ?>
                                        <li>
                                            <span class="label fw-6">Address</span>
                                            <div class="text text-variant-1"><?php echo htmlspecialchars($property['locationInfo']['locationName']); ?></div>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (isset($property['cityInfo']) && is_array($property['cityInfo']) && isset($property['cityInfo']['cityName']) && !empty($property['cityInfo']['cityName'])): ?>
                                        <li>
                                            <span class="label fw-6">City</span>
                                            <div class="text text-variant-1"><?php echo htmlspecialchars($property['cityInfo']['cityName']); ?></div>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (isset($property['state']) && !empty($property['state'])): ?>
                                        <li>
                                            <span class="label fw-6">State/county</span>
                                            <div class="text text-variant-1"><?php echo htmlspecialchars($property['state']); ?></div>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                    <ul class="box-right">
                                        <?php if (isset($property['pinCode']) && !empty($property['pinCode'])): ?>
                                        <li>
                                            <span class="label fw-6">Postal code</span>
                                            <div class="text text-variant-1"><?php echo htmlspecialchars($property['pinCode']); ?></div>
                                        </li>
                                        <?php elseif (isset($property['postalCode']) && !empty($property['postalCode'])): ?>
                                        <li>
                                            <span class="label fw-6">Postal code</span>
                                            <div class="text text-variant-1"><?php echo htmlspecialchars($property['postalCode']); ?></div>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (isset($property['sqft']) && !empty($property['sqft'])): ?>
                                        <li>
                                            <span class="label fw-6">Area</span>
                                            <div class="text text-variant-1"><?php echo htmlspecialchars($property['sqft']); ?> sqft</div>
                                        </li>
                                        <?php elseif (isset($property['size']) && !empty($property['size'])): ?>
                                        <li>
                                            <span class="label fw-6">Area</span>
                                            <div class="text text-variant-1"><?php echo htmlspecialchars($property['size']); ?> sqft</div>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (isset($property['country']) && !empty($property['country'])): ?>
                                        <li>
                                            <span class="label fw-6">Country</span>
                                            <div class="text text-variant-1"><?php echo htmlspecialchars($property['country']); ?></div>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            <?php if (isset($property['layoutImage']) && !empty($property['layoutImage'])): ?>
                            <?php 
                            $layoutImage = $property['layoutImage'];
                            // If image path doesn't start with http:// or https://, add base_url
                            if (!preg_match('/^https?:\/\//', $layoutImage)) {
                                $layoutImage = base_url($layoutImage);
                            }
                            ?>
                            <div class="single-property-element single-property-floor">
                                <h5 class="title fw-6">Layout</h5>
                                <ul class="box-floor" id="parent-floor">
                                    <li class="floor-item1">
                                        <div class="floor-header" data-bs-target="#floor-one" data-bs-toggle="collapse"
                                            aria-expanded="false" aria-controls="floor-one" role="button">
                                         
                                        </div>
                                        <div id="floor-one" class="collapse show" data-bs-parent="#parent-floor">
                                            <div class="faq-body">
                                                <div class="box-img">
                                                    <img src="<?php echo htmlspecialchars($layoutImage); ?>" alt="img-floor">
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        
                    </div>

                </div>

            </section>
<?php endif; ?>