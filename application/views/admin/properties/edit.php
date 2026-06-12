<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Property</h2>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" value="<?php echo $property->name; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category *</label>
                        <select class="form-control" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category->category_name); ?>" 
                                        <?php echo $property->category == $category->category_name ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category->category_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            <a href="<?php echo base_url('admin/categories'); ?>" target="_blank">Manage Categories</a>
                        </small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">City *</label>
                        <select class="form-control" name="city" id="citySelect" required>
                            <option value="">Select City</option>
                            <?php foreach($cities as $city): ?>
                                <option value="<?php echo $city->name; ?>" <?php echo $property->city == $city->name ? 'selected' : ''; ?>>
                                    <?php echo $city->name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            <a href="<?php echo base_url('admin/cities'); ?>" target="_blank">Manage Cities</a>
                        </small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location *</label>
                        <select class="form-control" name="location" id="locationSelect" required>
                            <option value="">Select Location</option>
                            <?php foreach($all_locations as $loc): ?>
                                <option value="<?php echo $loc->name; ?>" 
                                        data-city-name="<?php echo htmlspecialchars($loc->city_name); ?>"
                                        <?php echo $property->location == $loc->name ? 'selected' : ''; ?>>
                                    <?php echo $loc->name; ?> (<?php echo $loc->city_name; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            <a href="<?php echo base_url('admin/locations'); ?>" target="_blank">Manage Locations</a>
                        </small>
                    </div>
                </div>
                <script>
                    // Filter locations based on selected city
                    document.getElementById('citySelect').addEventListener('change', function() {
                        const selectedCity = this.value;
                        const locationSelect = document.getElementById('locationSelect');
                        const options = locationSelect.querySelectorAll('option');
                        
                        // Show all options first
                        options.forEach(option => {
                            if (option.value !== '') {
                                option.style.display = 'block';
                            }
                        });
                        
                        // Hide options that don't match selected city
                        if (selectedCity) {
                            options.forEach(option => {
                                if (option.value !== '' && option.dataset.cityName) {
                                    if (option.dataset.cityName !== selectedCity) {
                                        option.style.display = 'none';
                                    }
                                }
                            });
                        }
                    });
                </script>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price *</label>
                        <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $property->price; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="type">
                            <option value="">Select Type</option>
                            <option value="house" <?php echo $property->type == 'house' ? 'selected' : ''; ?>>House</option>
                            <option value="apartment" <?php echo $property->type == 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                            <option value="villa" <?php echo $property->type == 'villa' ? 'selected' : ''; ?>>Villa</option>
                            <option value="condo" <?php echo $property->type == 'condo' ? 'selected' : ''; ?>>Condo</option>
                            <option value="land" <?php echo $property->type == 'land' ? 'selected' : ''; ?>>Land</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="5"><?php echo $property->description; ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Video URL</label>
                    <input type="url" class="form-control" name="video" value="<?php echo $property->video; ?>" placeholder="https://youtube.com/...">
                </div>

                <div class="mb-3">
                    <label class="form-label">Main Image</label>
                    <?php if($property->main_image): ?>
                        <div class="mb-2">
                            <img src="<?php echo base_url($property->main_image); ?>" class="img-thumbnail" style="max-width: 300px;">
                            <p class="text-muted mt-1">Current main image</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="main_image" accept="image/*" id="mainImageInput">
                    <small class="text-muted">Upload new image to replace current main image</small>
                    <div id="mainImagePreview" class="mt-2"></div>
                </div>
                <script>
                    document.getElementById('mainImageInput').addEventListener('change', function(e) {
                        const preview = document.getElementById('mainImagePreview');
                        preview.innerHTML = '';
                        if (this.files && this.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'img-thumbnail';
                                img.style.maxWidth = '300px';
                                preview.appendChild(img);
                            };
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                </script>

                <div class="mb-3">
                    <label class="form-label">Gallery Images</label>
                    <?php 
                    $existing_gallery = array();
                    if($property->gallery) {
                        $existing_gallery = json_decode($property->gallery, true);
                        if (!is_array($existing_gallery)) {
                            $existing_gallery = array();
                        }
                    }
                    ?>
                    <?php if(!empty($existing_gallery)): ?>
                        <div class="mb-3 p-3 border rounded bg-light" id="galleryContainer">
                            <h6 class="mb-3">Current Gallery Images (<span id="galleryCount"><?php echo count($existing_gallery); ?></span> images):</h6>
                            <div class="d-flex flex-wrap gap-3" id="existingGalleryContainer">
                                <?php foreach($existing_gallery as $index => $img): ?>
                                    <div class="position-relative gallery-item" data-image="<?php echo htmlspecialchars($img); ?>" style="width: 150px; height: 150px; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                        <img src="<?php echo base_url($img); ?>" class="w-100 h-100" style="object-fit: cover; display: block;">
                                        <input type="hidden" name="existing_gallery[]" value="<?php echo htmlspecialchars($img); ?>" class="gallery-input">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute remove-gallery-image" style="top: 5px; right: 5px; padding: 0; line-height: 1; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 2px 6px rgba(0,0,0,0.4); border: 2px solid #fff; background: #dc3545; cursor: pointer;" title="Remove image">
                                            <i class="fas fa-times" style="font-size: 14px; color: #fff;"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted d-block mt-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Click the <strong>X</strong> button to remove images. Uploading new images will add to existing gallery.
                            </small>
                        </div>
                    <?php else: ?>
                        <div class="mb-3 p-3 border rounded bg-light" id="galleryContainer" style="display: none;">
                            <h6 class="mb-3">Current Gallery Images (<span id="galleryCount">0</span> images):</h6>
                            <div class="d-flex flex-wrap gap-3" id="existingGalleryContainer"></div>
                            <small class="text-muted d-block mt-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Click the <strong>X</strong> button to remove images. Uploading new images will add to existing gallery.
                            </small>
                        </div>
                    <?php endif; ?>
                    <div class="mb-2">
                        <input type="file" class="form-control" name="gallery[]" multiple accept="image/*" id="galleryInput">
                        <small class="text-muted">Select new images to add to gallery (Hold Ctrl/Cmd to select multiple)</small>
                    </div>
                    <div id="galleryPreview" class="mt-3 d-flex flex-wrap gap-3"></div>
                </div>
                <script>
                    // Remove gallery image functionality
                    document.addEventListener('click', function(e) {
                        if (e.target.closest('.remove-gallery-image')) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            const galleryItem = e.target.closest('.gallery-item');
                            if (galleryItem) {
                                // Remove the hidden input
                                const hiddenInput = galleryItem.querySelector('.gallery-input');
                                if (hiddenInput) {
                                    hiddenInput.remove();
                                }
                                
                                // Add fade out effect
                                galleryItem.style.transition = 'opacity 0.3s, transform 0.3s';
                                galleryItem.style.opacity = '0';
                                galleryItem.style.transform = 'scale(0.8)';
                                
                                setTimeout(function() {
                                    // Remove the image container
                                    galleryItem.remove();
                                    
                                    // Update gallery count
                                    updateGalleryCount();
                                }, 300);
                            }
                        }
                    });

                    // Function to update gallery count
                    function updateGalleryCount() {
                        const container = document.getElementById('existingGalleryContainer');
                        const countElement = document.getElementById('galleryCount');
                        const galleryContainer = document.getElementById('galleryContainer');
                        
                        if (container && countElement) {
                            const remainingImages = container.querySelectorAll('.gallery-item').length;
                            countElement.textContent = remainingImages;
                            
                            // Show/hide container based on image count
                            if (galleryContainer) {
                                if (remainingImages === 0) {
                                    galleryContainer.style.display = 'none';
                                } else {
                                    galleryContainer.style.display = 'block';
                                }
                            }
                        }
                    }

                    // Preview new gallery images
                    document.getElementById('galleryInput').addEventListener('change', function(e) {
                        const preview = document.getElementById('galleryPreview');
                        preview.innerHTML = '';
                        if (this.files) {
                            Array.from(this.files).forEach(file => {
                                if (file.type.startsWith('image/')) {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        const div = document.createElement('div');
                                        div.className = 'position-relative';
                                        div.style.width = '150px';
                                        div.style.height = '150px';
                                        div.style.borderRadius = '8px';
                                        div.style.overflow = 'hidden';
                                        div.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                                        div.innerHTML = `
                                            <img src="${e.target.result}" class="w-100 h-100" style="object-fit: cover; display: block;">
                                            <span class="badge bg-success position-absolute top-0 start-0 m-2">New</span>
                                        `;
                                        preview.appendChild(div);
                                    };
                                    reader.readAsDataURL(file);
                                }
                            });
                        }
                    });
                </script>

                <div class="mb-3">
                    <label class="form-label">Location URL</label>
                    <input type="url" class="form-control" name="location_url" value="<?php echo isset($property->location_url) ? htmlspecialchars($property->location_url) : ''; ?>" placeholder="https://example.com/location">
                    <small class="text-muted">Enter the URL for the location (e.g., Google Maps link or location page)</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Floor Plan Image</label>
                    <?php if($property->floorplan): ?>
                        <div class="mb-2">
                            <img src="<?php echo base_url($property->floorplan); ?>" style="max-width: 200px;" class="img-thumbnail">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="floorplan" accept="image/*">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nearby Places <small class="text-muted fw-normal">(Optional)</small></label>
                    <div id="nearbyPlacesContainer">
                        <?php
                        $nearby_places = array();
                        if ($property->nearby) {
                            $nearby_places = json_decode($property->nearby, true);
                        }
                        $nearbyCategoryOptions = ['School','College','University','Hospital','Clinic','Pharmacy','Bank','ATM','Supermarket','Shopping Mall','Restaurant','Hotel','Park','Gym','Temple','Church','Mosque','Bus Stop','Metro Station','Railway Station','Airport','Police Station','Post Office','Petrol Pump'];
                        ?>
                        <?php if (!empty($nearby_places)): ?>
                            <?php foreach ($nearby_places as $place): ?>
                            <?php
                            $savedCat  = isset($place['category']) ? $place['category'] : (isset($place['title']) ? $place['title'] : '');
                            $savedName = isset($place['name']) ? $place['name'] : '';
                            $savedDist = isset($place['distance']) ? $place['distance'] : '';
                            ?>
                                <div class="nearby-place-item mb-2 row g-2 align-items-center">
                                    <div class="col-md-3">
                                        <select class="form-select" name="nearby_category[]">
                                            <option value="">-- Select Category --</option>
                                            <?php foreach ($nearbyCategoryOptions as $opt): ?>
                                            <option value="<?php echo $opt; ?>" <?php echo ($savedCat === $opt) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="nearby_title[]" value="<?php echo htmlspecialchars($savedName); ?>" placeholder="Name (e.g. Amit School)">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" step="0.1" class="form-control" name="nearby_distance[]" value="<?php echo htmlspecialchars($savedDist); ?>" placeholder="Distance (km)" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-nearby">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="addNearbyPlace">
                        <i class="fas fa-plus me-1"></i>Add Nearby Place
                    </button>
                    <small class="text-muted d-block mt-2">e.g. Category: School, Name: Amit School, Distance: 2</small>
                </div>
                <script>
                    const nearbyCategoryOptions = [
                        'School','College','University',
                        'Hospital','Clinic','Pharmacy',
                        'Bank','ATM',
                        'Supermarket','Shopping Mall',
                        'Restaurant','Hotel',
                        'Park','Gym','Temple','Church','Mosque',
                        'Bus Stop','Metro Station','Railway Station','Airport',
                        'Police Station','Post Office','Petrol Pump'
                    ];

                    function buildCategorySelect(selected) {
                        let opts = '<option value="">-- Select Category --</option>';
                        nearbyCategoryOptions.forEach(function(cat) {
                            const sel = (cat === selected) ? ' selected' : '';
                            opts += `<option value="${cat}"${sel}>${cat}</option>`;
                        });
                        return `<select class="form-select" name="nearby_category[]">${opts}</select>`;
                    }

                    document.getElementById('addNearbyPlace').addEventListener('click', function() {
                        const container = document.getElementById('nearbyPlacesContainer');
                        const newItem = document.createElement('div');
                        newItem.className = 'nearby-place-item mb-2 row g-2 align-items-center';
                        newItem.innerHTML = `
                            <div class="col-md-3">
                                ${buildCategorySelect('')}
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="nearby_title[]" placeholder="Name (e.g. Amit School)">
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="0.1" class="form-control" name="nearby_distance[]" placeholder="Distance (km)" min="0">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-sm remove-nearby">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                        container.appendChild(newItem);
                    });

                    document.addEventListener('click', function(e) {
                        if (e.target.closest('.remove-nearby')) {
                            e.target.closest('.nearby-place-item').remove();
                        }
                    });
                </script>

                <div class="mb-3">
                    <label class="form-label">Features <small class="text-muted">(Optional)</small></label>
                    <div id="featuresContainer">
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
                            <?php foreach($features as $index => $feature): ?>
                                <div class="feature-item mb-2 row">
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="features[]" value="<?php echo htmlspecialchars($feature); ?>" placeholder="Feature name (e.g., Swimming Pool)">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-feature">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="addFeature">
                        <i class="fas fa-plus me-1"></i>Add Feature
                    </button>
                    <small class="text-muted d-block mt-2">Example: Swimming Pool, Gym, Parking, Security, etc.</small>
                </div>
                <script>
                    document.getElementById('addFeature').addEventListener('click', function() {
                        const container = document.getElementById('featuresContainer');
                        const newItem = document.createElement('div');
                        newItem.className = 'feature-item mb-2 row';
                        newItem.innerHTML = `
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="features[]" placeholder="Feature name (e.g., Swimming Pool)">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-sm remove-feature">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                        container.appendChild(newItem);
                    });
                    
                    // Remove feature
                    document.addEventListener('click', function(e) {
                        if (e.target.closest('.remove-feature')) {
                            const item = e.target.closest('.feature-item');
                            item.remove();
                        }
                    });
                </script>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status">
                            <option value="active" <?php echo $property->status == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $property->status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_latest" value="1" id="isLatest" <?php echo (isset($property->is_latest) && $property->is_latest == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isLatest">
                                <strong>Latest Property</strong>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured" <?php echo (isset($property->is_featured) && $property->is_featured == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isFeatured">
                                <strong>Featured Property</strong>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="best_rated" value="1" id="bestRated" <?php echo (!empty($property->tags_best_rate_localities)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="bestRated">
                                <strong>Best Rated</strong>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="high_growth" value="1" id="highGrowth" <?php echo (!empty($property->tags_high_growth_localities)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="highGrowth">
                                <strong>High Growth</strong>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Property
                    </button>
                    <a href="<?php echo base_url('admin/properties'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

