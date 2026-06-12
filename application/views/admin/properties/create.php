<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-plus me-2"></i>Create Property</h2>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category *</label>
                        <select class="form-control" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category->category_name); ?>">
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
                                <option value="<?php echo $city->name; ?>"><?php echo $city->name; ?></option>
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
                            <?php foreach($all_locations as $location): ?>
                                <option value="<?php echo $location->name; ?>" data-city-name="<?php echo htmlspecialchars($location->city_name); ?>">
                                    <?php echo $location->name; ?> (<?php echo $location->city_name; ?>)
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
                        
                        // Reset location selection
                        locationSelect.value = '';
                    });
                </script>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price *</label>
                        <input type="number" step="0.01" class="form-control" name="price" required>
                    </div>                    
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="5"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Video URL</label>
                    <input type="url" class="form-control" name="video" placeholder="https://youtube.com/...">
                </div>

                <div class="mb-3">
                    <label class="form-label">Main Image *</label>
                    <input type="file" class="form-control" name="main_image" accept="image/*" id="mainImageInput" required>
                    <small class="text-muted">This will be the featured/main image for the property</small>
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
                    <input type="file" class="form-control" name="gallery[]" multiple accept="image/*" id="galleryInput">
                    <small class="text-muted">You can select multiple images (Hold Ctrl/Cmd to select multiple)</small>
                    <div id="galleryPreview" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>
                <script>
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
                                        div.innerHTML = `
                                            <img src="${e.target.result}" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
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
                    <input type="url" class="form-control" name="location_url" placeholder="https://example.com/location">
                    <small class="text-muted">Enter the URL for the location (e.g., Google Maps link or location page)</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Floor Plan Image</label>
                    <input type="file" class="form-control" name="floorplan" accept="image/*">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nearby Places <small class="text-muted fw-normal">(Optional)</small></label>
                    <div id="nearbyPlacesContainer">
                        <!-- Empty container - user can add places if needed -->
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
                        <!-- Empty container - user can add features if needed -->
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
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_latest" value="1" id="isLatest">
                            <label class="form-check-label" for="isLatest">
                                <strong>Latest Property</strong>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured">
                            <label class="form-check-label" for="isFeatured">
                                <strong>Featured Property</strong>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="best_rated" value="1" id="bestRated">
                            <label class="form-check-label" for="bestRated">
                                <strong>Best Rated</strong>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="high_growth" value="1" id="highGrowth">
                            <label class="form-check-label" for="highGrowth">
                                <strong>High Growth</strong>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Property
                    </button>
                    <a href="<?php echo base_url('admin/properties'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

