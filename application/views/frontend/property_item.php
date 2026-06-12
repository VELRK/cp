

<?php if (!empty($property)): ?>
<div class="col-md-6">
    <div class="homelengo-box">
        <div class="archive-top">
            <a href="<?php echo base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : $property['id'])); ?>" class="images-group">
                <div class="images-style">
                    <img class="lazyload" style="width:100%; height:310px; object-fit:cover;"
                         data-src="<?php echo !empty($property['propertiesMainImage']) ? $property['propertiesMainImage'] : 'assets/images/home/house-7.jpg'; ?>"
                         alt="<?php echo htmlspecialchars($property['propertyName'] ?? 'Property Image'); ?>">
                </div>
                        <div class="top">
                            <ul class="d-flex gap-6">
                                <?php 
                                $categoryName = '';
                                if (!empty($property['categoryInfo']['categoryName'])) {
                                    $categoryName = $property['categoryInfo']['categoryName'];
                                } elseif (!empty($property['category'])) {
                                    $categoryName = $property['category'];
                                } elseif (!empty($property['categoryName'])) {
                                    $categoryName = $property['categoryName'];
                                }
                                if (!empty($categoryName)): ?>
                                <li class="flag-tag primary"><?php echo ucfirst($categoryName); ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                <div class="bottom">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 7C10 7.53043 9.78929 8.03914 9.41421 8.41421C9.03914 8.78929 8.53043 9 8 9C7.46957 9 6.96086 8.78929 6.58579 8.41421C6.21071 8.03914 6 7.53043 6 7C6 6.46957 6.21071 5.96086 6.58579 5.58579C6.96086 5.21071 7.46957 5 8 5C8.53043 5 9.03914 5.21071 9.41421 5.58579C9.78929 5.96086 10 6.46957 10 7Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M13 7C13 11.7613 8 14.5 8 14.5C8 14.5 3 11.7613 3 7C3 5.67392 3.52678 4.40215 4.46447 3.46447C5.40215 2.52678 6.67392 2 8 2C9.32608 2 10.5979 2.52678 11.5355 3.46447C12.4732 4.40215 13 5.67392 13 7Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <?php echo htmlspecialchars($property['propertyRange'] ?? 'Location'); ?>
                </div>
            </a>
        </div>
        <div class="archive-bottom">
            <div class="content-top">
                <h6 class="text-capitalize">
                    <a href="<?php echo base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : $property['id'])); ?>" class="link">
                        <?php echo htmlspecialchars($property['propertyName'] ?? 'Property Name'); ?>
                    </a>
                </h6>
                <ul class="meta-list">
                                                        <li class="item">
                                                            <i class="icon icon-mapPin"></i>
                                                            <span class="text-variant-1"><?php $locationName = $property['locationInfo']['locationName']; echo $locationName; ?></span>                                                            
                                                        </li>
                                                        <li class="item">
                                                            <i class="icon icon-sqft"></i>
                                                            <span class="text-variant-1"><?php echo $property['cityInfo']['cityName']; ?></span>                                                            
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
                    <button class="form-wg tf-btn primary w-100 enquiry-btn" 
                            name="submit" 
                            type="button"
                            data-property-id="<?php echo isset($property['id']) ? $property['id'] : ''; ?>"
                            data-property-name="<?php echo htmlspecialchars($property['propertyName'] ?? ''); ?>"
                            data-property-price="<?php echo htmlspecialchars(dvm_property_price_data_attr($property)); ?>"
                            data-cover-image="<?php echo isset($property['propertiesMainImage']) ? htmlspecialchars($property['propertiesMainImage']) : ''; ?>">
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
