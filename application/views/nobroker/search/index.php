<?php defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
$lat_get = $CI->input->get('lat');
$lng_get = $CI->input->get('lng');

$get_params = array();
if (!empty($filters['city_id'])) {
    $get_params['city_id'] = $filters['city_id'];
}
if (isset($filters['locality_q']) && (string) $filters['locality_q'] !== '') {
    $get_params['q'] = $filters['locality_q'];
}
if (!empty($filters['lat'])) {
    $get_params['lat'] = $filters['lat'];
}
if (!empty($filters['lng'])) {
    $get_params['lng'] = $filters['lng'];
}
if (!empty($filters['lat']) && !empty($filters['lng']) && isset($filters['radius_km'])) {
    $get_params['radius_km'] = $filters['radius_km'];
}
if (!empty($filters['listing_type'])) {
    $get_params['listing_type'] = $filters['listing_type'];
}
if (!empty($filters['property_type'])) {
    $get_params['property_type'] = $filters['property_type'];
}
if (isset($filters['min_price']) && $filters['min_price'] !== '' && $filters['min_price'] !== null) {
    $get_params['min_price'] = $filters['min_price'];
}
if (isset($filters['max_price']) && $filters['max_price'] !== '' && $filters['max_price'] !== null) {
    $get_params['max_price'] = $filters['max_price'];
}
if (!empty($filters['bedrooms'])) {
    $get_params['bedrooms'] = $filters['bedrooms'];
}
if (!empty($filters['sort']) && $filters['sort'] !== 'new') {
    $get_params['sort'] = $filters['sort'];
}

$city_name_chip = '';
if (!empty($filters['city_id'])) {
    foreach ($cities as $c) {
        if ((string) $c->id === (string) $filters['city_id']) {
            $city_name_chip = $c->name;
            break;
        }
    }
}

$chips = array();
if ($city_name_chip !== '') {
    $chips[] = array('label' => $city_name_chip, 'title' => 'City');
}
if (!empty($filters['locality_q'])) {
    $chips[] = array('label' => '"' . $filters['locality_q'] . '"', 'title' => 'Locality');
}
if (!empty($filters['lat']) && !empty($filters['lng']) && isset($filters['radius_km'])) {
    $chips[] = array('label' => 'Within ' . (int) $filters['radius_km'] . ' km', 'title' => 'Map search');
}
if ($filters['listing_type'] === 'sale') {
    $chips[] = array('label' => 'Buy', 'title' => 'Listing');
} elseif ($filters['listing_type'] === 'rent') {
    $chips[] = array('label' => 'Rent', 'title' => 'Listing');
}
if (!empty($filters['property_type'])) {
    $chips[] = array('label' => nb_property_type_label($filters['property_type']), 'title' => 'Type');
}
if (isset($filters['min_price']) && $filters['min_price'] !== '' && $filters['min_price'] !== null) {
    $chips[] = array('label' => 'Min ₹' . number_format((float) $filters['min_price']), 'title' => 'Budget');
}
if (isset($filters['max_price']) && $filters['max_price'] !== '' && $filters['max_price'] !== null) {
    $chips[] = array('label' => 'Max ₹' . number_format((float) $filters['max_price']), 'title' => 'Budget');
}
if (!empty($filters['bedrooms'])) {
    $chips[] = array('label' => (int) $filters['bedrooms'] . ' BHK', 'title' => 'Bedrooms');
}
if (!empty($filters['sort']) && $filters['sort'] !== 'new') {
    $sort_l = ($filters['sort'] === 'price_asc') ? 'Price ↑' : (($filters['sort'] === 'price_desc') ? 'Price ↓' : '');
    if ($sort_l !== '') {
        $chips[] = array('label' => $sort_l, 'title' => 'Sort');
    }
}

$showing_from = $total > 0 ? 1 : 0;
$showing_to = $total > 0 ? min(count($results), $total) : 0;
$nb_search_has_more = $total > count($results);
$nb_search_more_url = site_url('search/more');
?>
<div class="nb-search-page">
  <header class="nb-search-hero">
    <div class="nb-search-hero__mesh" aria-hidden="true"></div>
    <div class="container position-relative py-4 py-lg-5">
      <div class="row align-items-end g-4">
        <div class="col-lg-8">
          <span class="nb-search-hero-badge"><i class="bi bi-compass me-1"></i>Discover listings</span>
          <h1 class="nb-search-hero-title mt-3 mb-2">Find your next home</h1>
          <p class="nb-search-hero-sub mb-0">Curated owner listings — filter by city, budget, BHK, and locality without brokerage noise.</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
          <div class="nb-search-count-pill ms-lg-auto">
            <span class="nb-search-count-num"><?php echo (int) $total; ?></span>
            <span class="nb-search-count-label"><?php echo (int) $total === 1 ? 'match' : 'matches'; ?></span>
          </div>
        </div>
      </div>
      <?php if (count($chips) > 0) : ?>
      <div class="nb-search-chips mt-4">
        <span class="nb-search-chips-label">Active filters</span>
        <?php foreach ($chips as $ch) : ?>
          <span class="nb-search-chip" title="<?php echo html_escape($ch['title']); ?>"><?php echo html_escape($ch['label']); ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </header>

  <div class="container py-4 py-lg-5 nb-search-body">
    <div class="d-flex d-lg-none justify-content-between align-items-center gap-2 mb-4 nb-search-mobile-bar">
      <button class="btn nb-search-fab" type="button" data-bs-toggle="offcanvas" data-bs-target="#nbSearchFiltersOffcanvas" aria-controls="nbSearchFiltersOffcanvas">
        <i class="bi bi-sliders"></i><span>Filters</span>
      </button>
      <a href="<?php echo site_url('search'); ?>" class="nb-search-clear-link"><i class="bi bi-x-lg me-1"></i>Clear all</a>
    </div>

    <div class="row g-4 g-xl-5">
      <aside class="col-lg-3 d-none d-lg-block">
        <div class="nb-search-filters-card card border-0 nb-search-filters-card--elevated">
          <div class="card-header nb-search-filters-head border-0 py-3">
            <h2 class="h6 mb-0 fw-semibold"><i class="bi bi-funnel me-2 nb-text-brand"></i>Filters</h2>
            <!-- <p class="small text-muted mb-0 mt-1"</p> -->
          </div>
          <div class="card-body pt-0">
            <?php echo form_open('search', array('method' => 'get', 'class' => 'nb-search-filter-form')); ?>
              <?php
              $CI->load->view('nobroker/search/_filter_fields', array(
                  'filters' => $filters,
                  'cities' => $cities,
                  'lat_get' => $lat_get,
                  'lng_get' => $lng_get,
                  'filter_suffix' => 'main',
                  'has_maps_key' => !empty($has_maps_key),
              ));
              ?>
              <button type="submit" class="btn btn-danger w-100 rounded-pill fw-semibold mb-2">Apply filters</button>
              <a href="<?php echo site_url('search'); ?>" class="btn btn-outline-secondary btn-sm w-100 rounded-pill">Reset</a>
            <?php echo form_close(); ?>
          </div>
        </div>
      </aside>

      <div class="col-lg-9" id="nb-search-results">
        <?php if (empty($results)) : ?>
          <div class="nb-search-empty">
            <div class="nb-search-empty__icon"><i class="bi bi-search"></i></div>
            <h2 class="nb-search-empty__title">No properties match</h2>
            <p class="nb-search-empty__text">Try widening your budget, picking another city, or removing a filter — new homes are listed regularly.</p>
            <a href="<?php echo site_url('search'); ?>" class="btn btn-danger rounded-pill px-4 fw-semibold">Browse all listings</a>
          </div>
        <?php else : ?>
          <div class="nb-search-toolbar">
            <p class="nb-search-toolbar__text mb-0" id="nb-search-toolbar-range">
              Showing <strong id="nb-search-showing-from"><?php echo (int) $showing_from; ?></strong>–<strong id="nb-search-showing-to"><?php echo (int) $showing_to; ?></strong>
              of <strong id="nb-search-total"><?php echo (int) $total; ?></strong> listings
            </p>
          </div>
          <div class="row g-4 nb-property-grid" id="nb-search-property-grid">
            <?php foreach ($results as $p) : ?>
              <?php $CI->load->view('nobroker/_property_card', array('p' => $p)); ?>
            <?php endforeach; ?>
          </div>
          <?php if ($nb_search_has_more) : ?>
          <div class="nb-search-infinite-sentinel text-center py-4" id="nb-search-infinite-sentinel" aria-hidden="true">
            <div class="spinner-border text-secondary nb-search-infinite-spinner" role="status" id="nb-search-infinite-spinner" style="display:none;">
              <span class="visually-hidden">Loading more listings…</span>
            </div>
            <p class="small text-muted mb-0 d-none" id="nb-search-infinite-end"></p>
            <p class="small text-danger mb-0 d-none" id="nb-search-infinite-err">Could not load more. Scroll to try again.</p>
          </div>
          <script type="application/json" id="nb-search-infinite-config"><?php
            echo json_encode(array(
                'moreUrl'   => $nb_search_more_url,
                'nextPage'  => 2,
                'hasMore'   => true,
                'params'    => $get_params,
            ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
          ?></script>
          <?php endif; ?>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<div class="offcanvas offcanvas-start nb-search-offcanvas" tabindex="-1" id="nbSearchFiltersOffcanvas" aria-labelledby="nbSearchFiltersLabel">
  <div class="offcanvas-header nb-search-offcanvas-head border-0">
    <h2 class="offcanvas-title h5 mb-0" id="nbSearchFiltersLabel"><i class="bi bi-sliders me-2"></i>Filters</h2>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <?php echo form_open('search', array('method' => 'get', 'class' => 'nb-search-filter-form', 'id' => 'nbSearchFilterFormMobile')); ?>
      <?php
      $CI->load->view('nobroker/search/_filter_fields', array(
          'filters' => $filters,
          'cities' => $cities,
          'lat_get' => $lat_get,
          'lng_get' => $lng_get,
          'filter_suffix' => 'm',
          'has_maps_key' => !empty($has_maps_key),
      ));
      ?>
      <button type="submit" class="btn btn-danger w-100 rounded-pill fw-semibold mb-2">Apply filters</button>
      <a href="<?php echo site_url('search'); ?>" class="btn btn-outline-secondary btn-sm w-100 rounded-pill">Reset</a>
    <?php echo form_close(); ?>
  </div>
</div>
