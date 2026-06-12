<?php if (!empty($property)): ?>
<div class="col-md-12">
    <div class="homelengo-box list-style-1 list-style-2 line">
        <div class="archive-top">
            <a href="<?php echo base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : $property['id'])); ?>" class="images-group">
                <div class="images-style">
                    <img class="lazyload" 
                         data-src="<?php echo !empty($property['propertiesMainImage']) ? $property['propertiesMainImage'] : 'assets/images/home/house-sm-11.jpg'; ?>"
                         alt="<?php echo htmlspecialchars($property['propertyName'] ?? 'Property Image'); ?>">
                </div>
                <div class="top">
                    <ul class="d-flex gap-6 flex-wrap">
                        <?php if (!empty($property['categoryInfo']['categoryName']) || !empty($property['category'])): ?>
                        <li class="flag-tag primary">
                            <?php echo ucfirst($property['categoryInfo']['categoryName'] ?? $property['category'] ?? 'Property'); ?>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($property['isFeatured']) || !empty($property['is_featured'])): ?>
                        <li class="flag-tag style-1">Featured</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </a>
        </div>
        <div class="archive-bottom">
            <div class="content-top">
                <h6 class="text-capitalize">
                    <a href="<?php echo base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : $property['id'])); ?>" class="link text-line-clamp-1">
                        <?php echo htmlspecialchars($property['propertyName'] ?? 'Property Name'); ?>
                    </a>
                </h6>
                <ul class="meta-list">
                    <li class="item">
                        <i class="icon icon-bed"></i>
                        <span class="text-variant-1">Beds:</span>
                        <span class="fw-6"><?php echo $property['beds'] ?? '3'; ?></span>
                    </li>
                    <li class="item">
                        <i class="icon icon-bath"></i>
                        <span class="text-variant-1">Baths:</span>
                        <span class="fw-6"><?php echo $property['baths'] ?? '2'; ?></span>
                    </li>
                    <li class="item">
                        <i class="icon icon-sqft"></i>
                        <span class="text-variant-1">Sqft:</span>
                        <span class="fw-6"><?php echo $property['sqft'] ?? '1150'; ?></span>
                    </li>
                </ul>
            </div>
            <?php 
            $description = $property['desc'] ?? '';
            $descriptionLength = mb_strlen($description);
            $maxLength = 100;
            
            if ($descriptionLength > $maxLength) {
                $truncatedDesc = mb_substr($description, 0, $maxLength);
                $propertyDetailUrl = base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : ($property['id'] ?? '')));
                echo '<p>' . htmlspecialchars($truncatedDesc) . '... <a href="' . $propertyDetailUrl . '" class="show-more-link" style="color: #007bff; text-decoration: none; font-weight: 500;">show more</a></p>';
            } else {
                echo '<p>' . htmlspecialchars($description) . '</p>';
            }
            ?>
            <div class="content-bottom">
                <div class="d-flex gap-8 align-items-center">
                    <button class="form-wg tf-btn primary enquiry-btn" 
                            name="submit" 
                            type="button"
                            data-property-id="<?php echo isset($property['id']) ? $property['id'] : ''; ?>"
                            data-property-name="<?php echo htmlspecialchars($property['propertyName'] ?? ''); ?>"
                            data-property-price="<?php echo htmlspecialchars(dvm_property_price_data_attr($property)); ?>"
                            data-cover-image="<?php echo isset($property['propertiesMainImage']) ? htmlspecialchars($property['propertiesMainImage']) : ''; ?>"
                            style="min-width: 120px;">
                        <span>Enquiry</span>
                    </button>
                </div>
                <h6 class="price">
                    <?php
                    $pd = dvm_property_price_display($property);
                    echo $pd !== '' ? $pd : 'Price on Request';
                    ?>
                </h6>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
