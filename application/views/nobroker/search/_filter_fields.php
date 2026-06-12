<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Shared filter fields for search (desktop sidebar + mobile offcanvas).
 * Expects: $filters, $cities, $lat_get, $lng_get; optional $filter_suffix for label `for` / ids.
 */
$sfx = isset($filter_suffix) ? (string) $filter_suffix : 'main';
$has_maps_key = !empty($has_maps_key);
$rk = isset($filters['radius_km']) ? (float) $filters['radius_km'] : 15;
if ($rk < 1) {
    $rk = 15;
}
if ($rk > 100) {
    $rk = 100;
}
?>
<div class="nb-search-filters-panel">
<div class="mb-2">
  <label class="form-label nb-filter-label" for="city-select-<?php echo html_escape($sfx); ?>">City</label>
  <select name="city_id" id="city-select-<?php echo html_escape($sfx); ?>" class="form-select form-select-sm nb-filter-control nb-city-select-sync">
    <option value="">Any city</option>
    <?php foreach ($cities as $c) : ?>
      <option value="<?php echo (int) $c->id; ?>" <?php echo ((string)$filters['city_id'] === (string)$c->id) ? 'selected' : ''; ?>><?php echo html_escape($c->name); ?></option>
    <?php endforeach; ?>
  </select>
</div>
<div class="mb-2">
  <label class="form-label nb-filter-label" for="location-search-<?php echo html_escape($sfx); ?>">Locality</label>
  <input type="text" name="q" id="location-search-<?php echo html_escape($sfx); ?>" class="form-control form-control-sm nb-filter-control" value="<?php echo html_escape($filters['locality_q']); ?>" placeholder="Area, landmark…" autocomplete="off">
</div>
<div class="mb-2">
  <label class="form-label nb-filter-label">Listing</label>
  <select name="listing_type" class="form-select form-select-sm nb-filter-control">
    <option value="">Buy or rent</option>
    <option value="sale" <?php echo ($filters['listing_type'] === 'sale') ? 'selected' : ''; ?>>Buy</option>
    <option value="rent" <?php echo ($filters['listing_type'] === 'rent') ? 'selected' : ''; ?>>Rent</option>
  </select>
</div>
<div class="mb-2">
  <label class="form-label nb-filter-label">Property type</label>
  <select name="property_type" class="form-select form-select-sm nb-filter-control">
    <option value="">Any type</option>
    <?php foreach (nb_property_types_map() as $tval => $tlabel) : ?>
      <option value="<?php echo html_escape($tval); ?>" <?php echo ($filters['property_type'] === $tval) ? 'selected' : ''; ?>><?php echo html_escape($tlabel); ?></option>
    <?php endforeach; ?>
  </select>
</div>
<div class="mb-2">
  <label class="form-label nb-filter-label">Budget (₹)</label>
  <div class="row g-2">
    <div class="col-6">
      <input type="number" name="min_price" class="form-control form-control-sm nb-filter-control" placeholder="Min" value="<?php echo html_escape($filters['min_price']); ?>" min="0" step="1">
    </div>
    <div class="col-6">
      <input type="number" name="max_price" class="form-control form-control-sm nb-filter-control" placeholder="Max" value="<?php echo html_escape($filters['max_price']); ?>" min="0" step="1">
    </div>
  </div>
</div>
<div class="mb-2">
  <label class="form-label nb-filter-label">Bedrooms</label>
  <select name="bedrooms" class="form-select form-select-sm nb-filter-control">
    <option value="">Any</option>
    <?php for ($b = 1; $b <= 5; $b++) : ?>
      <option value="<?php echo $b; ?>" <?php echo ((string)$filters['bedrooms'] === (string)$b) ? 'selected' : ''; ?>><?php echo $b; ?> BHK</option>
    <?php endfor; ?>
  </select>
</div>
<div class="mb-3">
  <label class="form-label nb-filter-label">Sort by</label>
  <select name="sort" class="form-select form-select-sm nb-filter-control">
    <option value="new" <?php echo ($filters['sort'] === 'new') ? 'selected' : ''; ?>>Latest first</option>
    <option value="price_asc" <?php echo ($filters['sort'] === 'price_asc') ? 'selected' : ''; ?>>Price: low to high</option>
    <option value="price_desc" <?php echo ($filters['sort'] === 'price_desc') ? 'selected' : ''; ?>>Price: high to low</option>
  </select>
</div>
</div>
