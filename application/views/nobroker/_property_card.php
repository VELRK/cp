<?php defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
$nb_user = $CI->session->userdata('nb_user');
$nb_uid = ($nb_user && isset($nb_user['id'])) ? (string) $nb_user['id'] : '';
$imgs = array();
if (!empty($p->images)) {
  $imgs = json_decode($p->images, true);
  if (!is_array($imgs)) {
    $imgs = array();
  }
}
$img = !empty($imgs[0]) ? base_url($imgs[0]) : '';
$lt = $p->listing_type === 'rent' ? 'Rent' : 'Sale';
$badge = $p->listing_type === 'rent' ? 'nb-card-tag nb-card-tag--rent' : 'nb-card-tag nb-card-tag--sale';
$prop_url = nb_property_url($p);
$title_esc = html_escape($p->title);
?>
<div class="col-md-6 col-lg-4">
  <article class="nb-card nb-card-premium h-100">
    <div class="ratio ratio-4x3 nb-card-premium__ratio">
      <div class="nb-card-premium__frame">
        <a href="<?php echo html_escape($prop_url); ?>" class="nb-card-premium__visual" aria-label="View <?php echo $title_esc; ?>">
          <?php if ($img !== '') : ?>
          <img src="<?php echo html_escape($img); ?>" class="nb-card-premium__img" alt="<?php echo $title_esc; ?>" loading="lazy" decoding="async">
          <?php else : ?>
          <div class="nb-card-premium__placeholder d-flex flex-column align-items-center justify-content-center">
            <i class="bi bi-image"></i>
            <span class="small mt-1">Owner photo</span>
          </div>
          <?php endif; ?>
        </a>
        <div class="nb-card-premium__shade" aria-hidden="true"></div>
        <span class="<?php echo $badge; ?>"><?php echo $lt; ?></span>
        <?php if (!empty($p->is_featured)) : ?>
        <span class="nb-card-featured"><i class="bi bi-star-fill me-1"></i>Featured</span>
        <?php endif; ?>
        <button
          type="button"
          class="btn btn-light btn-sm nb-wishlist-toggle nb-wishlist-toggle--card"
          data-property-id="<?php echo (int) $p->id; ?>"
          data-user-id="<?php echo html_escape($nb_uid); ?>"
          data-wishlisted="0"
          aria-label="Add to wishlist"
          title="Add to wishlist">
          <i class="bi bi-heart" aria-hidden="true"></i>
        </button>
        <span class="nb-card-premium__price"><?php echo html_escape(nb_format_listing_price($p->price, $p->listing_type)); ?></span>
      </div>
    </div>
    <div class="nb-card-premium__body">
      <h3 class="nb-card-premium__title">
        <a href="<?php echo html_escape($prop_url); ?>"><?php echo $title_esc; ?></a>
      </h3>
      <p class="nb-card-premium__loc"><i class="bi bi-geo-alt-fill"></i><?php echo html_escape($p->locality); ?> · <?php echo html_escape(isset($p->city_name) ? $p->city_name : ''); ?></p>
      <div class="nb-card-premium__meta">
        <span><i class="bi bi-door-open"></i><?php echo (int) $p->bedrooms; ?> BHK</span>
        <span><i class="bi bi-droplet"></i><?php echo (int) $p->bathrooms; ?></span>
        <span><i class="bi bi-grid-1x2"></i><?php echo (int) $p->area_sqft; ?> sqft</span>
      </div>
      <div class="nb-card-premium__foot">
        <span class="nb-card-type-pill"><?php echo html_escape(nb_property_type_label($p->property_type)); ?></span>
        <a class="nb-card-premium__cta" href="<?php echo html_escape($prop_url); ?>">View <i class="bi bi-arrow-right-short"></i></a>
      </div>
    </div>
  </article>
</div>
