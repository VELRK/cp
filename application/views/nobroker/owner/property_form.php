<?php defined('BASEPATH') OR exit('No direct script access allowed');
$r = $row;
$is_edit = !empty($edit_id);
$is_admin = !empty($is_admin);
$hide_page_title = !empty($hide_page_title);
$owners = isset($owners) && is_array($owners) ? $owners : array();
$maxw = $is_admin ? 'none' : '720px';
$existing_imgs = array();
if ($r && !empty($r->images)) {
  $d = json_decode($r->images, true);
  if (is_array($d)) {
    $existing_imgs = $d;
  }
}
$outer_class = $is_admin ? 'nb-admin-form-max nb-property-form--admin' : 'container py-4';
$outer_style = $is_admin ? '' : ' style="max-width:' . $maxw . '"';
$amenity_options = isset($amenity_options) && is_array($amenity_options) ? $amenity_options : array();
$nearby_places = array();
if ($r && !empty($r->nearby)) {
  $decoded_nearby = json_decode($r->nearby, true);
  if (is_array($decoded_nearby)) {
    $nearby_places = $decoded_nearby;
  }
}
$nearbyCategoryOptions = array(
  'School','College','University',
  'Hospital','Clinic','Pharmacy',
  'Bank','ATM',
  'Supermarket','Shopping Mall',
  'Restaurant','Hotel',
  'Park','Gym','Temple','Church','Mosque',
  'Bus Stop','Metro Station','Railway Station','Airport',
  'Police Station','Post Office','Petrol Pump'
);
?>
<div class="<?php echo $outer_class; ?>"<?php echo $outer_style; ?>>
  <?php if (!$hide_page_title) : ?>
  <h1 class="h3 mb-4"><?php
    if ($is_admin) {
      echo $is_edit ? 'Edit property (admin)' : 'Add property (admin)';
    } else {
      echo $is_edit ? 'Edit property' : 'Add property';
    }
  ?></h1>
  <?php endif; ?>
  <?php if (!$is_admin && !$is_edit) : ?>
  <div class="alert alert-info border-0 shadow-sm small mb-4">After you save, an admin must publish the listing before it appears in search and on the public site.</div>
  <?php endif; ?>
  <?php
  if ($is_admin) {
    // Same-origin relative action so session cookies are always sent (localhost:3000 or :8080).
    echo form_open_multipart('panel/property/save', array('action' => site_url('panel/property/save')));
  } else {
    echo form_open_multipart('api/property/save');
  }
  ?>
    <?php if ($is_admin) : ?>
      <input type="hidden" name="admin_save" value="1">
      <input type="hidden" name="nb_admin_save" value="1">
      <input type="hidden" name="admin_property_token" value="<?php echo html_escape(isset($admin_property_token) ? $admin_property_token : ''); ?>">
    <?php endif; ?>
    <input type="hidden" name="image_action" value="replace">
    <input type="hidden" name="cover_index" id="nbCoverIndex" value="0">
    <?php if ($is_edit) : ?>
      <input type="hidden" name="property_id" value="<?php echo (int) $edit_id; ?>">
    <?php endif; ?>
    <?php if ($is_admin) : ?>
    <?php if (empty($owners)) : ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4">There are no approved owner accounts yet. Approve at least one owner under Users before creating or assigning listings.</div>
    <?php endif; ?>
    <div class="nb-admin-panel mb-4 nb-admin-panel--accent">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title"><i class="bi bi-sliders me-2"></i>Listing control</h2>
        <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">Admin</span>
      </div>
      <div class="nb-admin-panel-body p-4">
        <div class="mb-3 mb-lg-0">
          <label class="form-label fw-semibold" for="nb-admin-owner-id">Owner account</label>
          <select name="owner_id" id="nb-admin-owner-id" class="form-select nb-admin-input" <?php echo !empty($owners) ? 'required' : ''; ?> <?php echo empty($owners) ? 'disabled' : ''; ?>>
            <?php if (!$is_edit) : ?>
            <option value="">— Select owner —</option>
            <?php endif; ?>
            <?php foreach ($owners as $ow) : ?>
              <option value="<?php echo (int) $ow->id; ?>" <?php echo ($r && (int) $r->owner_id === (int) $ow->id) ? 'selected' : ''; ?>>
                <?php echo html_escape($ow->name); ?> — <?php echo html_escape($ow->email); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <p class="small text-muted mb-0 mt-1"><?php echo $is_edit ? 'Reassign the listing to another approved owner account if needed.' : 'The listing will belong to this owner account.'; ?></p>
        </div>
        <?php
        $admin_listing_flags = array(
          array('name' => 'is_active', 'id' => 'nb_ad_active', 'label' => 'Published (visible in search)', 'default_on' => true),
          array('name' => 'is_featured', 'id' => 'nb_ad_feat', 'label' => 'Feature'),
          array('name' => 'tags_best_rate_localities', 'id' => 'nb_ad_best_rate', 'label' => 'Best Rate'),
          array('name' => 'tags_high_growth_localities', 'id' => 'nb_ad_high_growth', 'label' => 'High Growth'),
          array('name' => 'is_recommended', 'id' => 'nb_ad_recommended', 'label' => 'Recommended'),
          array('name' => 'is_newly_launched', 'id' => 'nb_ad_newly_launched', 'label' => 'Newly Launched Project'),
          array('name' => 'is_verified_property', 'id' => 'nb_ad_verified', 'label' => 'Verified Property'),
          array('name' => 'is_premium', 'id' => 'nb_ad_premium', 'label' => 'Premium Project'),
          array('name' => 'is_home_banner', 'id' => 'nb_ad_home_banner', 'label' => 'Home Banner (hero slideshow)'),
        );
        $home_banner_on = $r && !empty($r->is_home_banner);
        ?>
        <div class="row g-3 mt-1">
          <?php foreach ($admin_listing_flags as $flag) :
            $fname = $flag['name'];
            if ($r) {
              $is_on = !empty($r->{$fname});
            } else {
              $is_on = !empty($flag['default_on']);
            }
          ?>
          <div class="col-md-6">
            <div class="form-check form-switch">
              <input type="hidden" name="<?php echo html_escape($fname); ?>" value="0">
              <input class="form-check-input" type="checkbox" name="<?php echo html_escape($fname); ?>" value="1" id="<?php echo html_escape($flag['id']); ?>"<?php echo $is_on ? ' checked' : ''; ?>>
              <label class="form-check-label fw-medium" for="<?php echo html_escape($flag['id']); ?>"><?php echo html_escape($flag['label']); ?></label>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div id="nbHomeBannerUpload" class="mt-4 pt-3 border-top" style="<?php echo $home_banner_on ? '' : 'display:none;'; ?>">
          <label class="form-label fw-semibold" for="nbHomeBannerImage">Home banner image <span class="text-danger">*</span></label>
          <p class="small text-muted mb-2">Required when Home Banner is enabled. JPEG, PNG or WebP only — min 800×300 px, max 2 MB, max 4000×2000 px.</p>
          <?php if ($r && !empty($r->home_banner_image)) : ?>
          <div class="mb-2">
            <img src="<?php echo base_url($r->home_banner_image); ?>" alt="Home banner" class="img-thumbnail" style="max-height: 160px;">
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" name="remove_home_banner_image" value="1" id="nbRemoveHomeBanner">
              <label class="form-check-label text-danger" for="nbRemoveHomeBanner">Remove current banner image</label>
            </div>
          </div>
          <?php endif; ?>
          <input type="file" name="home_banner_image" id="nbHomeBannerImage" class="form-control nb-admin-input" accept="image/jpeg,image/png,image/webp">
        </div>
      </div>
    </div>

    <div class="nb-admin-panel mb-4">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title"><i class="bi bi-card-heading me-2"></i>Basics &amp; pricing</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
    <?php endif; ?>
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" required value="<?php echo $r ? html_escape($r->title) : ''; ?>">
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Property type</label>
        <select name="property_type" class="form-select<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" required>
          <?php foreach (nb_property_types_map() as $tval => $tlabel) : ?>
            <option value="<?php echo html_escape($tval); ?>" <?php echo ($r && $r->property_type === $tval) ? 'selected' : ''; ?>><?php echo html_escape($tlabel); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Listing</label>
        <select name="listing_type" class="form-select<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" required>
          <option value="rent" <?php echo ($r && $r->listing_type === 'rent') ? 'selected' : ''; ?>>Rent</option>
          <option value="sale" <?php echo ($r && $r->listing_type === 'sale') ? 'selected' : ''; ?>>Sale</option>
        </select>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Price (₹)</label>
      <input type="number" step="0.01" name="price" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" required value="<?php echo $r ? html_escape($r->price) : ''; ?>">
      <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name="is_price_negotiable" value="1" id="is_price_negotiable"
          <?php echo ($r && !empty($r->is_price_negotiable)) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="is_price_negotiable">Price is negotiable</label>
      </div>
    </div>
    <?php if ($is_admin) : ?>
      </div>
    </div>
    <div class="nb-admin-panel mb-4">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title"><i class="bi bi-rulers me-2"></i>Plot &amp; availability</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
    <?php endif; ?>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Rate per sq. ft. (₹, optional)</label>
        <input type="number" step="0.01" name="rate_per_sqft" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" value="<?php echo ($r && isset($r->rate_per_sqft) && $r->rate_per_sqft !== null) ? html_escape($r->rate_per_sqft) : ''; ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Available from (optional)</label>
        <input type="date" name="available_from" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" value="<?php
          if ($r && !empty($r->available_from)) {
            echo html_escape(substr($r->available_from, 0, 10));
          }
        ?>">
      </div>
    </div>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Plot length (ft)</label>
        <input type="number" step="0.01" name="plot_length_ft" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" value="<?php echo ($r && isset($r->plot_length_ft) && $r->plot_length_ft !== null) ? html_escape($r->plot_length_ft) : ''; ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Plot width (ft)</label>
        <input type="number" step="0.01" name="plot_width_ft" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" value="<?php echo ($r && isset($r->plot_width_ft) && $r->plot_width_ft !== null) ? html_escape($r->plot_width_ft) : ''; ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Boundary wall</label>
        <select name="has_boundary_wall" class="form-select<?php echo $is_admin ? ' nb-admin-input' : ''; ?>">
          <option value="">—</option>
          <option value="1" <?php echo ($r && isset($r->has_boundary_wall) && (string) $r->has_boundary_wall === '1') ? 'selected' : ''; ?>>Yes</option>
          <option value="0" <?php echo ($r && isset($r->has_boundary_wall) && (string) $r->has_boundary_wall === '0') ? 'selected' : ''; ?>>No</option>
        </select>
      </div>
    </div>
    <?php if ($is_admin) : ?>
      </div>
    </div>
    <div class="nb-admin-panel mb-4">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title"><i class="bi bi-text-paragraph me-2"></i>Description</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
    <?php endif; ?>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" rows="4"><?php echo $r ? html_escape($r->description) : ''; ?></textarea>
    </div>
    <?php if ($is_admin) : ?>
      </div>
    </div>
    <div class="nb-admin-panel mb-4">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title"><i class="bi bi-geo-alt me-2"></i>Location</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
    <?php endif; ?>
    <div class="mb-3">
      <label class="form-label">City</label>
      <select name="city_id" id="city-select" class="form-select<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" required>
        <?php foreach ($cities as $c) : ?>
          <option value="<?php echo (int) $c->id; ?>" <?php echo ($r && (int)$r->city_id === (int)$c->id) ? 'selected' : ''; ?>><?php echo html_escape($c->name); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Locality</label>
      <input type="text" name="locality" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" value="<?php echo $r ? html_escape($r->locality) : ''; ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Location (URL or details)</label>
      <textarea name="location" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" rows="2"><?php echo $r ? html_escape($r->location) : ''; ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Location Image</label>
      <?php if ($r && !empty($r->location_image)) : ?>
        <div class="mb-2">
          <img src="<?php echo base_url($r->location_image); ?>" alt="Location" class="img-thumbnail" style="max-height: 150px;">
        </div>
      <?php endif; ?>
      <input type="file" name="location_image" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" accept="image/jpeg,image/png,image/webp">
      <small class="text-muted">A static map image or location-specific photo.</small>
    </div>
    <div class="mb-3">
      <label class="form-label">Full address</label>
      <textarea name="address" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" required rows="2"><?php echo $r ? html_escape($r->address) : ''; ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label fw-semibold">Nearby places <small class="text-muted">(optional)</small></label>
      <div id="nearbyPlacesContainer">
        <?php if (!empty($nearby_places)) : ?>
          <?php foreach ($nearby_places as $place) : ?>
            <?php
            $savedCat  = isset($place['category']) ? $place['category'] : (isset($place['title']) ? $place['title'] : '');
            $savedName = isset($place['name']) ? $place['name'] : '';
            $savedDist = isset($place['distance']) ? $place['distance'] : '';
            ?>
            <div class="nearby-place-item mb-2 row g-2 align-items-center">
              <div class="col-md-3">
                <select class="form-select<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" name="nearby_category[]">
                  <option value="">-- Select Category --</option>
                  <?php foreach ($nearbyCategoryOptions as $opt) : ?>
                    <option value="<?php echo html_escape($opt); ?>" <?php echo ($savedCat === $opt) ? 'selected' : ''; ?>><?php echo html_escape($opt); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-5">
                <input type="text" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" name="nearby_title[]" value="<?php echo html_escape($savedName); ?>" placeholder="Name (e.g. City Hospital)">
              </div>
              <div class="col-md-3">
                <input type="number" step="0.1" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" name="nearby_distance[]" value="<?php echo html_escape($savedDist); ?>" placeholder="Distance (km)" min="0">
              </div>
              <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger btn-sm remove-nearby"><i class="bi bi-x"></i></button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="addNearbyPlace">
        <i class="bi bi-plus"></i> Add nearby place
      </button>
      <small class="text-muted d-block mt-2">Example: Category School, Name DPS School, Distance 1.8 km</small>
    </div>
    <?php if ($is_admin) : ?>
      </div>
    </div>
    <div class="nb-admin-panel mb-4">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title"><i class="bi bi-door-open me-2"></i>Layout &amp; area</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
    <?php endif; ?>
    <div class="row">
      <div class="col-4 mb-3"><label class="form-label">Bedrooms</label><input type="number" name="bedrooms" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" value="<?php echo $r ? (int)$r->bedrooms : ''; ?>"></div>
      <div class="col-4 mb-3"><label class="form-label">Bathrooms</label><input type="number" name="bathrooms" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" value="<?php echo $r ? (int)$r->bathrooms : ''; ?>"></div>
      <div class="col-4 mb-3"><label class="form-label">Area sqft</label><input type="number" name="area_sqft" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" value="<?php echo $r ? (int)$r->area_sqft : ''; ?>"></div>
    </div>
    <?php if ($is_admin) : ?>
      </div>
    </div>
    <div class="nb-admin-panel mb-4">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title"><i class="bi bi-check2-square me-2"></i>Amenities</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
    <?php endif; ?>
    <div class="mb-3">
      <label class="form-label">Amenities</label>
      <?php
      $sel = array();
      if ($r && $r->amenities) {
        $dec = json_decode($r->amenities, true);
        if (is_array($dec)) {
          $sel = $dec;
        }
      }
      ?>
      <?php if (empty($amenity_options)) : ?>
      <p class="text-muted small mb-0">No amenities are configured yet. An admin can add them under <strong>Panel → Amenities</strong>, or run the database migration <code>006_nb_amenities.sql</code>.</p>
      <?php else : ?>
      <div class="row">
        <?php foreach ($amenity_options as $am) :
          $o = isset($am->name) ? (string) $am->name : '';
          if ($o === '') {
            continue;
          }
          $fid = 'am-' . preg_replace('/[^a-zA-Z0-9_-]+/', '-', $o);
          ?>
          <div class="col-6 col-md-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="amenities[]" value="<?php echo html_escape($o); ?>" id="<?php echo html_escape($fid); ?>"
                <?php echo in_array($o, $sel, true) ? 'checked' : ''; ?>>
              <label class="form-check-label" for="<?php echo html_escape($fid); ?>"><?php echo html_escape($o); ?></label>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php if ($is_admin) : ?>
      </div>
    </div>
    <div class="nb-admin-panel mb-4">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title"><i class="bi bi-images me-2"></i>Photos &amp; video</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
    <?php endif; ?>
    <div class="mb-4">
      <label class="form-label fw-semibold">Photos</label>
      <p class="small text-muted">Up to 10 images (JPEG, PNG, WebP). Click a photo to set it as the <strong>cover image</strong> shown in search and at the top of your listing.</p>
      <?php if (!empty($existing_imgs)) : ?>
      <div class="mb-3">
        <span class="small text-muted d-block mb-2">Current photos — click one for cover, or mark remove</span>
        <div class="row g-2 nb-prop-photo-grid" id="nbPropExistingGrid">
          <?php $ei = 0; foreach ($existing_imgs as $ipath) : ?>
          <div class="col-12 nb-prop-img-row">
            <div class="d-flex align-items-start gap-2 p-2 border rounded-3<?php echo $is_admin ? ' bg-white' : ' bg-light'; ?>">
              <div class="nb-prop-existing-thumb nb-cover-choice flex-shrink-0 rounded-3 overflow-hidden border border-2 border-transparent" style="width:120px;height:90px;" data-coverIdx="<?php echo (int) $ei; ?>">
                <button type="button" class="btn p-0 nb-cover-existing-btn w-100 h-100" data-coverIdx="<?php echo (int) $ei; ?>" title="Set as cover">
                  <img src="<?php echo base_url($ipath); ?>" alt="" class="w-100 h-100" style="object-fit:cover;">
                </button>
              </div>
              <div class="flex-grow-1 small">
                <input type="hidden" name="existing_paths[]" value="<?php echo html_escape($ipath); ?>">
                <div class="form-check">
                  <input class="form-check-input nb-remove-existing" type="checkbox" name="remove_existing[]" value="<?php echo html_escape($ipath); ?>" id="rm-<?php echo (int) $ei; ?>">
                  <label class="form-check-label text-danger" for="rm-<?php echo (int) $ei; ?>">Remove this photo</label>
                </div>
              </div>
            </div>
          </div>
          <?php $ei++; endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <label class="form-label" for="nbPropImages"><?php echo empty($existing_imgs) ? 'Upload images' : 'Add more images'; ?></label>
      <input type="file" name="images[]" id="nbPropImages" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" multiple accept="image/jpeg,image/png,image/webp">
      <div class="row g-2 mt-1" id="nbPropNewPreview"></div>
    </div>
    <div class="mb-4">
      <label class="form-label fw-semibold" for="nbVideoUrl">Video tour (optional)</label>
      <?php if (isset($nb_has_video_url_column) && !$nb_has_video_url_column) : ?>
      <div class="alert alert-warning py-2 small mb-2" role="status">
        The database is missing the <code>video_url</code> column, so video links cannot be saved. Run the SQL migration
        <code>application/sql/migrations/008_nb_properties_video_url_if_missing.sql</code> (or <code>005_nb_properties_video_url.sql</code>) in phpMyAdmin, then reload this page.
      </div>
      <?php endif; ?>
      <p class="small text-muted mb-1">Paste a <strong>YouTube</strong> or <strong>Vimeo</strong> link (e.g. <code>https://www.youtube.com/watch?v=…</code>).</p>
      <input type="text" name="video_url" id="nbVideoUrl" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" inputmode="url" autocomplete="off" placeholder="https://www.youtube.com/watch?v=... or youtu.be/..." value="<?php echo ($r && isset($r->video_url) && $r->video_url !== '') ? html_escape($r->video_url) : ''; ?>" maxlength="512"<?php echo (isset($nb_has_video_url_column) && !$nb_has_video_url_column) ? ' disabled' : ''; ?>>
    </div>
    <div class="mb-4">
      <label class="form-label fw-semibold" for="nbBrochure">Brochure (optional)</label>
      <p class="small text-muted mb-1">PDF, Word, or image brochure for this listing.</p>
      <?php if ($r && !empty($r->brochure_url)) : ?>
      <div class="mb-2 small">
        <a href="<?php echo base_url($r->brochure_url); ?>" target="_blank" rel="noopener">View current brochure</a>
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" name="remove_brochure" value="1" id="nbRemoveBrochure">
          <label class="form-check-label text-danger" for="nbRemoveBrochure">Remove brochure</label>
        </div>
      </div>
      <?php endif; ?>
      <input type="file" name="brochure" id="nbBrochure" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" accept=".pdf,.doc,.docx,image/jpeg,image/png,image/webp">
    </div>
    <div class="mb-4">
      <label class="form-label fw-semibold" for="nbAudioNotes">Audio notes (optional)</label>
      <p class="small text-muted mb-1">Upload a voice note or audio walkthrough (MP3, WAV, M4A, OGG).</p>
      <?php if ($r && !empty($r->audio_notes_url)) : ?>
      <div class="mb-2 small">
        <audio controls preload="none" class="w-100" style="max-width:420px;">
          <source src="<?php echo base_url($r->audio_notes_url); ?>">
        </audio>
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" name="remove_audio_notes" value="1" id="nbRemoveAudio">
          <label class="form-check-label text-danger" for="nbRemoveAudio">Remove audio notes</label>
        </div>
      </div>
      <?php endif; ?>
      <input type="file" name="audio_notes" id="nbAudioNotes" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" accept="audio/*,.mp3,.wav,.m4a,.ogg,.webm,.aac">
    </div>
    <?php if ($is_admin) : ?>
      </div>
    </div>
    <?php endif; ?>
    <div class="<?php echo $is_admin ? 'nb-admin-form-actions' : ''; ?>">
      <button type="submit" class="btn <?php echo $is_admin ? 'btn-success' : 'btn-danger'; ?> px-4 rounded-pill fw-semibold"><?php echo $is_admin ? 'Save listing' : 'Save property'; ?></button>
      <a href="<?php echo $is_admin ? site_url('panel/properties') : site_url('owner/listings'); ?>" class="btn btn-outline-secondary rounded-pill px-4"><?php echo $is_admin ? 'Cancel' : 'Cancel'; ?></a>
    </div>
  <?php echo form_close(); ?>
</div>
<script src="<?php echo base_url('assets/js/nb_property_form.js'); ?>"></script>
<script>
(function () {
  var opts = <?php echo json_encode($nearbyCategoryOptions); ?>;
  var addBtn = document.getElementById('addNearbyPlace');
  var container = document.getElementById('nearbyPlacesContainer');
  if (!addBtn || !container) return;

  function buildSelect() {
    var html = '<option value="">-- Select Category --</option>';
    opts.forEach(function (cat) {
      html += '<option value="' + cat + '">' + cat + '</option>';
    });
    return html;
  }

  addBtn.addEventListener('click', function () {
    var item = document.createElement('div');
    item.className = 'nearby-place-item mb-2 row g-2 align-items-center';
    item.innerHTML =
      '<div class="col-md-3"><select class="form-select<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" name="nearby_category[]">' + buildSelect() + '</select></div>' +
      '<div class="col-md-5"><input type="text" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" name="nearby_title[]" placeholder="Name (e.g. City Hospital)"></div>' +
      '<div class="col-md-3"><input type="number" step="0.1" min="0" class="form-control<?php echo $is_admin ? ' nb-admin-input' : ''; ?>" name="nearby_distance[]" placeholder="Distance (km)"></div>' +
      '<div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm remove-nearby"><i class="bi bi-x"></i></button></div>';
    container.appendChild(item);
  });

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.remove-nearby');
    if (btn) {
      var row = btn.closest('.nearby-place-item');
      if (row) row.remove();
    }
  });
})();

(function () {
  var toggle = document.getElementById('nb_ad_home_banner');
  var panel = document.getElementById('nbHomeBannerUpload');
  var fileInput = document.getElementById('nbHomeBannerImage');
  var removeCb = document.getElementById('nbRemoveHomeBanner');
  var hasExisting = <?php echo ($r && !empty($r->home_banner_image)) ? 'true' : 'false'; ?>;
  if (!toggle || !panel) return;
  function syncHomeBannerPanel() {
    panel.style.display = toggle.checked ? '' : 'none';
    if (fileInput) {
      var removed = removeCb && removeCb.checked;
      fileInput.required = toggle.checked && (!hasExisting || removed);
    }
  }
  toggle.addEventListener('change', syncHomeBannerPanel);
  if (removeCb) {
    removeCb.addEventListener('change', syncHomeBannerPanel);
  }
  syncHomeBannerPanel();
})();
</script>
