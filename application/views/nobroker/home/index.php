<?php defined('BASEPATH') OR exit('No direct script access allowed');
$nb_type_icon_map = array(
  'apartment' => 'building',
  'studio' => 'bounding-box',
  'house' => 'house',
  'villa' => 'tree',
  'commercial' => 'shop',
  'plot' => 'grid-3x3',
  'office' => 'briefcase',
  'retail' => 'shop-window',
  'warehouse' => 'boxes',
  'farmhouse' => 'tree',
  'pg' => 'person-badge',
  'shared_flat' => 'people',
  'serviced_apartment' => 'buildings',
  'independent_floor' => 'building-up',
  'others' => 'grid',
);
$nb_type_tabs = array(array('', 'Any', 'grid'));
foreach (nb_property_types_map() as $slug => $label) {
  $icon = isset($nb_type_icon_map[$slug]) ? $nb_type_icon_map[$slug] : 'grid';
  $nb_type_tabs[] = array($slug, $label, $icon);
}
?>
<section class="nb-hero-wrap nb-hero-light">
  <div class="nb-hero-pattern nb-hero-pattern-light" aria-hidden="true"></div>
  <div class="container position-relative">
    <div class="row justify-content-center text-center mb-4 mb-lg-5">
      <div class="col-xl-10">
        <p class="nb-hero-badge mb-3"><i class="bi bi-shield-check me-1"></i> Verified listings · Zero brokerage</p>
        <h1 class="nb-hero-title mb-3">Find your perfect home — <span class="nb-hero-title-accent">without the noise.</span></h1>
        <p class="nb-hero-sub mb-0 mx-auto">Search owner-listed homes in your city. Clear prices, map-friendly search, and zero brokerage — <strong>no stock photos, no banner ads</strong>, just real listings.</p>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-xl-10 col-xxl-9">
        <div class="nb-search-card text-start text-dark">
          <form action="<?php echo site_url('search'); ?>" method="get" class="row g-3 g-md-4 align-items-end" id="nb-home-search-form">
            <div class="col-12">
              <div class="btn-group nb-tabs-buy-rent" role="group" aria-label="Buy or Rent">
                <input type="radio" class="btn-check" name="listing_type" id="lt-sale" value="sale" checked autocomplete="off">
                <label class="btn btn-outline-danger px-4" for="lt-sale">Buy</label>
                <input type="radio" class="btn-check" name="listing_type" id="lt-rent" value="rent" autocomplete="off">
                <label class="btn btn-outline-danger px-4" for="lt-rent">Rent</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label mb-2">Property type</label>
              <input type="hidden" name="property_type" id="property-type-hidden" value="">
              <ul class="nav nav-pills nb-property-type-tabs flex-wrap gap-2" role="tablist" id="property-type-tabs">
                <?php foreach ($nb_type_tabs as $i => $t) :
                  $active = $i === 0 ? ' active' : '';
                  $val = $t[0];
                ?>
                <li class="nav-item" role="presentation">
                  <button type="button" class="nav-link<?php echo $active; ?>" data-property-type="<?php echo html_escape($val); ?>" id="ptab-<?php echo $i; ?>" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>">
                    <i class="bi bi-<?php echo html_escape($t[2]); ?> me-1"></i><?php echo html_escape($t[1]); ?>
                  </button>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div class="col-md-6">
              <label class="form-label">City</label>
              <select class="form-select" name="city_id" id="city-select" required>
                <option value="">Select city</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Location</label>
              <input type="text" class="form-control" name="q" id="location-search" placeholder="Type locality or landmark" autocomplete="off">
            </div>
            <div class="col-12 text-center pt-1">
              <button type="submit" class="btn btn-danger btn-lg nb-btn-search px-5">Search properties</button>
            </div>
          </form>
        </div>

      
      </div>
    </div>
  </div>
</section>

<?php $cities_home = isset($cities_footer) ? $cities_footer : array(); ?>

<section class="nb-pill-strip py-4">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 gap-md-3">
      <span class="nb-pill-tag"><i class="bi bi-check2-circle"></i> Free to list &amp; search</span>
      <span class="nb-pill-tag"><i class="bi bi-person-check"></i> Owner-verified flow</span>
      <span class="nb-pill-tag"><i class="bi bi-chat-dots"></i> Enquiry inbox</span>
      <span class="nb-pill-tag"><i class="bi bi-pin-map"></i> Locality search</span>
      <span class="nb-pill-tag"><i class="bi bi-percent"></i> 0% brokerage</span>
    </div>
  </div>
</section>

<section class="nb-trust-strip py-5">
  <div class="container">
    <div class="row justify-content-center text-center mb-4">
      <div class="col-lg-8">
        <h2 class="nb-section-title mb-2">Why thousands choose us</h2>
        <p class="nb-section-muted mb-0">A full-service experience — from discovery to enquiry — built for clarity and trust. No clutter, no agent markups.</p>
      </div>
    </div>
    <div class="row g-4 g-lg-3">
      <div class="col-6 col-lg-3">
        <div class="nb-trust-card h-100 text-center">
          <div class="nb-trust-icon mx-auto"><i class="bi bi-patch-check-fill"></i></div>
          <h3 class="nb-trust-title">Verified listings</h3>
          <p class="nb-trust-text mb-0">Owner-submitted properties reviewed by our team before they go live.</p>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="nb-trust-card h-100 text-center">
          <div class="nb-trust-icon mx-auto"><i class="bi bi-people-fill"></i></div>
          <h3 class="nb-trust-title">Direct connection</h3>
          <p class="nb-trust-text mb-0">Talk to owners without brokers in the middle — fewer surprises.</p>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="nb-trust-card h-100 text-center">
          <div class="nb-trust-icon mx-auto"><i class="bi bi-currency-rupee"></i></div>
          <h3 class="nb-trust-title">Transparent pricing</h3>
          <p class="nb-trust-text mb-0">See rent or sale price upfront. We don’t hide fees behind “call for price.”</p>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="nb-trust-card h-100 text-center">
          <div class="nb-trust-icon mx-auto"><i class="bi bi-geo-alt-fill"></i></div>
          <h3 class="nb-trust-title">Locality search</h3>
          <p class="nb-trust-text mb-0">Find homes near work, schools, or transit with location-aware search.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 nb-section-compare">
  <div class="container">
    <div class="row justify-content-center mb-4">
      <div class="col-lg-8 text-center">
        <h2 class="nb-section-title mb-2">Traditional brokers vs this platform</h2>
        <p class="nb-section-muted mb-0">Same city, same listings potential — different experience and cost.</p>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="table-responsive nb-compare-table-wrap">
          <table class="table table-bordered mb-0 nb-compare-table">
            <thead>
              <tr>
                <th scope="col">What you care about</th>
                <th scope="col" class="text-center nb-compare-bad">Typical broker</th>
                <th scope="col" class="text-center nb-compare-good">Here</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Brokerage fee</td>
                <td class="text-center text-muted">Often 1–2 months rent or % of sale</td>
                <td class="text-center fw-semibold nb-text-brand">0% — direct listings</td>
              </tr>
              <tr>
                <td>Price transparency</td>
                <td class="text-center text-muted">Sometimes hidden behind “call”</td>
                <td class="text-center fw-semibold nb-text-brand">Shown on listing</td>
              </tr>
              <tr>
                <td>Who you talk to</td>
                <td class="text-center text-muted">Agent chain</td>
                <td class="text-center fw-semibold nb-text-brand">Owner-led + admin support</td>
              </tr>
              <tr>
                <td>Search &amp; filters</td>
                <td class="text-center text-muted">Varies by agent</td>
                <td class="text-center fw-semibold nb-text-brand">City, type &amp; locality</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white nb-section-edge">
  <div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
      <div>
        <h2 class="nb-section-title mb-1">Featured properties</h2>
        <p class="nb-section-muted mb-0">Hand-picked listings you can trust</p>
      </div>
      <a href="<?php echo site_url('search'); ?>" class="btn btn-outline-danger rounded-pill px-4">View all properties</a>
    </div>
    <div class="row g-4 nb-property-grid">
      <?php foreach ($featured as $p) : ?>
        <?php $this->load->view('nobroker/_property_card', array('p' => $p)); ?>
      <?php endforeach; ?>
      <?php if (empty($featured)) : ?>
        <div class="col-12">
          <div class="alert alert-light border text-center py-5 mb-0">
            <i class="bi bi-inboxes display-4 text-muted d-block mb-3"></i>
            <p class="text-muted mb-0">No featured listings yet. Import <code>application/sql/nobroker_schema.sql</code> and mark properties as featured in the database.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="py-5 nb-section-mint">
  <div class="container">
    <div class="row align-items-end mb-4">
      <div class="col-lg-8">
        <h2 class="nb-section-title mb-2">Popular cities</h2>
        <p class="nb-section-muted mb-0">Jump straight into listings in metros and growing neighbourhoods — same search filters, zero brokerage.</p>
      </div>
      <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a href="<?php echo site_url('search'); ?>" class="btn btn-outline-danger rounded-pill px-4">Explore all cities</a>
      </div>
    </div>
    <div class="row g-3">
      <?php
      $ci_n = 0;
      foreach ($cities_home as $c) :
        if ($ci_n++ >= 12) {
          break;
        }
        $city_url = site_url('search?' . http_build_query(array('city_id' => $c->id)));
      ?>
      <div class="col-6 col-sm-4 col-md-3 col-lg-2">
        <a href="<?php echo $city_url; ?>" class="nb-city-tile">
          <i class="bi bi-geo-alt"></i>
          <span><?php echo html_escape($c->name); ?></span>
        </a>
      </div>
      <?php endforeach; ?>
      <?php if (empty($cities_home)) : ?>
      <div class="col-12">
        <p class="text-muted mb-0 small">Cities will appear here once added in the admin or database.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="nb-stats-strip">
  <div class="container">
    <div class="row text-center g-4">
      <div class="col-6 col-md-3">
        <div class="nb-stat-num"><?php echo number_format((int) $stats['properties']); ?></div>
        <div class="small opacity-75">Properties listed</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="nb-stat-num"><?php echo number_format((int) $stats['cities']); ?></div>
        <div class="small opacity-75">Cities covered</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="nb-stat-num"><?php echo number_format((int) $stats['users']); ?></div>
        <div class="small opacity-75">Happy users</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="nb-stat-num nb-accent-stat">0%</div>
        <div class="small opacity-75">Brokerage — always</div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 nb-section-mint border-top border-bottom">
  <div class="container">
    <div class="row text-center mb-4">
      <div class="col-lg-8 mx-auto">
        <h2 class="nb-section-title mb-2">Start with how you’re moving</h2>
        <p class="nb-section-muted mb-0">Pick rent or buy — same search tools, tuned for your goal.</p>
      </div>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="nb-rent-sale-card h-100">
          <div class="nb-rent-sale-icon"><i class="bi bi-key"></i></div>
          <h3 class="h5 fw-bold mb-2">Rent a home</h3>
          <p class="small text-muted mb-3">Families, students, and professionals — filter by budget, BHK, and commute-friendly localities.</p>
          <a href="<?php echo site_url('search?' . http_build_query(array('listing_type' => 'rent'))); ?>" class="btn btn-danger rounded-pill px-4">Browse rentals</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-5">
        <div class="nb-rent-sale-card nb-rent-sale-card-alt h-100">
          <div class="nb-rent-sale-icon"><i class="bi bi-house-door"></i></div>
          <h3 class="h5 fw-bold mb-2">Buy a property</h3>
          <p class="small text-muted mb-3">Apartments, villas, plots, and commercial — compare sale listings with clear pricing.</p>
          <a href="<?php echo site_url('search?' . http_build_query(array('listing_type' => 'sale'))); ?>" class="btn btn-outline-danger rounded-pill px-4">Browse for sale</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white">
  <div class="container">
    <h2 class="nb-section-title text-center mb-2">How it works</h2>
    <p class="text-center nb-section-muted mb-5">Four steps from signup to enquiry — keep it simple</p>
    <div class="row g-4 text-center">
      <div class="col-6 col-lg-3">
        <div class="nb-step-num">1</div>
        <div class="nb-step-icon mx-auto"><i class="bi bi-person-plus"></i></div>
        <h3 class="h6 fw-bold">Register</h3>
        <p class="small text-muted mb-0">Create an account as owner or tenant. We review signups to keep spam low.</p>
      </div>
      <div class="col-6 col-lg-3">
        <div class="nb-step-num">2</div>
        <div class="nb-step-icon mx-auto"><i class="bi bi-house"></i></div>
        <h3 class="h6 fw-bold">List or search</h3>
        <p class="small text-muted mb-0">Owners add photos &amp; details. Everyone else searches by city, locality, and type.</p>
      </div>
      <div class="col-6 col-lg-3">
        <div class="nb-step-num">3</div>
        <div class="nb-step-icon mx-auto"><i class="bi bi-chat-left-text"></i></div>
        <h3 class="h6 fw-bold">Send enquiries</h3>
        <p class="small text-muted mb-0">Shortlist homes and message through our workflow — no random cold calls.</p>
      </div>
      <div class="col-6 col-lg-3">
        <div class="nb-step-num">4</div>
        <div class="nb-step-icon mx-auto"><i class="bi bi-handshake"></i></div>
        <h3 class="h6 fw-bold">Visit &amp; decide</h3>
        <p class="small text-muted mb-0">Schedule visits offline. We don’t charge brokerage on success.</p>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white border-top border-bottom nb-section-edge">
  <div class="container">
    <div class="row g-4 align-items-stretch">
      <div class="col-lg-6">
        <div class="nb-split-card nb-split-owners h-100">
          <span class="nb-split-badge"><i class="bi bi-key-fill me-1"></i> For owners</span>
          <h2 class="nb-split-title">List your property in minutes</h2>
          <p class="nb-split-text">Upload photos, set rent or sale price, and receive enquiries from serious tenants and buyers. Our team moderates listings so quality stays high.</p>
          <ul class="nb-split-list">
            <li><i class="bi bi-check-circle-fill"></i> Free to list — no commission on closure</li>
            <li><i class="bi bi-check-circle-fill"></i> Dashboard for edits &amp; enquiry tracking</li>
            <li><i class="bi bi-check-circle-fill"></i> Optional featured placement for visibility</li>
          </ul>
          <a href="<?php echo base_url(); ?>?modal=register" class="btn btn-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#nbModalRegister">Create owner account</a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="nb-split-card nb-split-tenants h-100">
          <span class="nb-split-badge nb-split-badge-alt"><i class="bi bi-search me-1"></i> For tenants &amp; buyers</span>
          <h2 class="nb-split-title">Search smarter, not harder</h2>
          <p class="nb-split-text">Filter by city, locality, budget, and property type. Save time with clear owner contact flow after you sign in.</p>
          <ul class="nb-split-list">
            <li><i class="bi bi-check-circle-fill"></i> Buy &amp; rent — one search experience</li>
            <li><i class="bi bi-check-circle-fill"></i> Enquiry workflow with admin routing</li>
            <li><i class="bi bi-check-circle-fill"></i> No paid “unlock contact” tricks</li>
          </ul>
          <a href="<?php echo site_url('search'); ?>" class="btn btn-outline-danger rounded-pill px-4 me-2">Browse listings</a>
          <a href="<?php echo base_url(); ?>?modal=register" class="btn btn-danger rounded-pill px-4 mt-2 mt-sm-0" data-bs-toggle="modal" data-bs-target="#nbModalRegister">Sign up free</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 nb-section-tools">
  <div class="container">
    <div class="row mb-4">
      <div class="col-lg-8">
        <h2 class="nb-section-title mb-2">Guides &amp; checklists</h2>
        <p class="nb-section-muted mb-0">Practical tips before you sign — no login required to read.</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <article class="nb-tool-card h-100">
          <div class="nb-tool-icon"><i class="bi bi-calculator"></i></div>
          <h3 class="h6 fw-bold mb-2">Plan your budget</h3>
          <p class="small text-muted mb-3">Factor in deposit, maintenance, and registration. Use search filters to stay inside your monthly range.</p>
          <span class="nb-tool-fake-link"><i class="bi bi-arrow-right-short"></i> Search by price on listings</span>
        </article>
      </div>
      <div class="col-md-4">
        <article class="nb-tool-card h-100">
          <div class="nb-tool-icon"><i class="bi bi-file-earmark-text"></i></div>
          <h3 class="h6 fw-bold mb-2">Documents to check</h3>
          <p class="small text-muted mb-3">Title, encumbrance, and owner ID — verify before you pay token or advance.</p>
          <span class="nb-tool-fake-link"><i class="bi bi-arrow-right-short"></i> Ask owner via enquiry</span>
        </article>
      </div>
      <div class="col-md-4">
        <article class="nb-tool-card h-100">
          <div class="nb-tool-icon"><i class="bi bi-truck"></i></div>
          <h3 class="h6 fw-bold mb-2">Moving day</h3>
          <p class="small text-muted mb-3">Society move-in rules, meter transfers, and packing — line these up early.</p>
          <span class="nb-tool-fake-link"><i class="bi bi-arrow-right-short"></i> Save localities you like</span>
        </article>
      </div>
    </div>
  </div>
</section>

<section class="py-5 nb-section-mint">
  <div class="container">
    <h2 class="nb-section-title text-center mb-2">What people say</h2>
    <p class="text-center nb-section-muted mb-5">Real feedback — profile photos optional, we use initials here.</p>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="nb-testimonial">
          <div class="nb-testimonial-stars mb-2" aria-hidden="true"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
          <p class="nb-quote mb-3">“Found a 2BHK in Velachery without paying brokerage. The process was smooth and transparent.”</p>
          <div class="d-flex align-items-center gap-3">
            <div class="nb-avatar-letter" aria-hidden="true">PS</div>
            <div>
              <div class="fw-semibold small">Priya S.</div>
              <div class="small text-muted">Tenant, Chennai</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="nb-testimonial">
          <div class="nb-testimonial-stars mb-2" aria-hidden="true"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
          <p class="nb-quote mb-3">“Listed my apartment and got genuine leads. No agent commission — exactly what I wanted.”</p>
          <div class="d-flex align-items-center gap-3">
            <div class="nb-avatar-letter nb-avatar-letter-b" aria-hidden="true">AM</div>
            <div>
              <div class="fw-semibold small">Arjun M.</div>
              <div class="small text-muted">Owner, Bangalore</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="nb-testimonial">
          <div class="nb-testimonial-stars mb-2" aria-hidden="true"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
          <p class="nb-quote mb-3">“Clean UI, map search works great. Saved a lot compared to traditional portals.”</p>
          <div class="d-flex align-items-center gap-3">
            <div class="nb-avatar-letter nb-avatar-letter-c" aria-hidden="true">NR</div>
            <div>
              <div class="fw-semibold small">Neha R.</div>
              <div class="small text-muted">Tenant, Mumbai</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white nb-section-edge">
  <div class="container">
    <div class="row align-items-center g-4 mb-4">
      <div class="col-lg-7">
        <h2 class="nb-section-title mb-2">Privacy &amp; safety first</h2>
        <p class="nb-section-muted mb-0">We don’t run random banner networks on core flows. Your enquiry goes through our system so owners aren’t spammed.</p>
      </div>
      <div class="col-lg-5">
        <div class="row g-3">
          <div class="col-12">
            <div class="d-flex gap-3 nb-security-row">
              <div class="nb-security-icon"><i class="bi bi-lock-fill"></i></div>
              <div>
                <div class="fw-semibold small">Secure sessions</div>
                <div class="small text-muted mb-0">Login protected areas for enquiries and dashboards.</div>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="d-flex gap-3 nb-security-row">
              <div class="nb-security-icon"><i class="bi bi-eye-slash-fill"></i></div>
              <div>
                <div class="fw-semibold small">No noisy ads on search</div>
                <div class="small text-muted mb-0">Focus on listings — not third-party promo banners.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 nb-section-mint">
  <div class="container">
    <h2 class="nb-section-title text-center mb-2">Search by lifestyle</h2>
    <p class="text-center nb-section-muted mb-4">Quick filters people often use — tap to open search.</p>
    <div class="d-flex flex-wrap justify-content-center gap-2">
      <a href="<?php echo site_url('search?' . http_build_query(array('q' => 'near IT park'))); ?>" class="nb-lifestyle-chip">Near IT parks</a>
      <a href="<?php echo site_url('search?' . http_build_query(array('q' => 'metro'))); ?>" class="nb-lifestyle-chip">Metro connectivity</a>
      <a href="<?php echo site_url('search?' . http_build_query(array('q' => 'school'))); ?>" class="nb-lifestyle-chip">Good schools</a>
      <a href="<?php echo site_url('search?' . http_build_query(array('property_type' => 'apartment'))); ?>" class="nb-lifestyle-chip">Gated apartments</a>
      <a href="<?php echo site_url('search?' . http_build_query(array('listing_type' => 'rent'))); ?>" class="nb-lifestyle-chip">Family rentals</a>
      <a href="<?php echo site_url('search?' . http_build_query(array('property_type' => 'villa'))); ?>" class="nb-lifestyle-chip">Villas &amp; duplex</a>
    </div>
  </div>
</section>

<section class="py-5 bg-white nb-section-edge">
  <div class="container">
    <div class="row justify-content-center mb-4">
      <div class="col-lg-8 text-center">
        <h2 class="nb-section-title mb-2">Frequently asked questions</h2>
        <p class="nb-section-muted mb-0">Quick answers about how our platform works for owners and seekers.</p>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="accordion nb-faq" id="nbFaq">
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">Is this really zero brokerage?</button>
            </h3>
            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#nbFaq">
              <div class="accordion-body">We don’t charge tenants or buyers a brokerage fee for connecting through the platform. Owners can list for free; any optional paid features would be clearly labelled.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">How are listings verified?</button>
            </h3>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#nbFaq">
              <div class="accordion-body">Our team reviews new submissions for completeness and basic checks. Users should still inspect properties and documents before making decisions.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">Can I list both rent and sale?</button>
            </h3>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#nbFaq">
              <div class="accordion-body">Each listing is either rent or sale. You can create separate listings if you want to advertise both — use the owner dashboard after registration.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">How do enquiries work?</button>
            </h3>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#nbFaq">
              <div class="accordion-body">Signed-in users can send enquiries on a property. Messages are routed through our workflow so owners and admins can respond in one place.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5">Do I need an account to search?</button>
            </h3>
            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#nbFaq">
              <div class="accordion-body">You can browse and search without logging in. Registering unlocks enquiries, saved preferences, and owner tools.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6" aria-expanded="false" aria-controls="faq6">Are there banner ads on listing pages?</button>
            </h3>
            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#nbFaq">
              <div class="accordion-body">We keep the experience focused on properties — no third-party display banners on core search and detail flows.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7" aria-expanded="false" aria-controls="faq7">Can owners edit a listing after it goes live?</button>
            </h3>
            <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#nbFaq">
              <div class="accordion-body">Yes. Approved owners can update price, description, and photos from the owner dashboard; major changes may be rechecked by moderators.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8" aria-expanded="false" aria-controls="faq8">What if I don’t see my city?</button>
            </h3>
            <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#nbFaq">
              <div class="accordion-body">Cities are added as we expand. You can still search nearby areas or contact support — check back as new regions open.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 nb-newsletter-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center">
        <h2 class="nb-section-title mb-2">Stay in the loop</h2>
        <p class="nb-section-muted mb-4">Tips for renters, owners, and market updates — no spam, unsubscribe anytime (coming soon).</p>
        <form class="row g-2 justify-content-center" action="#" method="post" onsubmit="return false;">
          <div class="col-md-8">
            <label class="visually-hidden" for="nb-newsletter-email">Email</label>
            <input type="email" class="form-control form-control-lg nb-newsletter-input" id="nb-newsletter-email" placeholder="Your email address" autocomplete="email">
          </div>
          <div class="col-md-auto">
            <button type="button" class="btn btn-danger btn-lg px-4 rounded-pill" disabled>Notify me</button>
          </div>
        </form>
        <p class="small text-muted mt-3 mb-0">This is a UI placeholder — wire your email provider when ready.</p>
      </div>
    </div>
  </div>
</section>

<section class="nb-cta-final py-5">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <h2 class="nb-cta-final-title mb-2">Ready to find your next home — or fill yours?</h2>
        <p class="nb-cta-final-text mb-0">Join owners and tenants who want a simpler, fairer way to deal in real estate. Start with a free account or search listings in your city today.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="<?php echo site_url('search'); ?>" class="btn btn-light btn-lg text-nowrap px-4 me-2 mb-2 mb-lg-0 nb-cta-btn-light">Search now</a>
        <a href="<?php echo base_url(); ?>?modal=register" class="btn btn-lg nb-cta-btn-outline text-white border-2 border-white text-nowrap px-4" data-bs-toggle="modal" data-bs-target="#nbModalRegister">Get started</a>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  fetch('<?php echo site_url('api/cities'); ?>', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
    .then(function (r) {
      if (!r.ok) {
        throw new Error('Cities request failed: ' + r.status);
      }
      return r.text();
    })
    .then(function (text) {
      var t = (text || '').trim();
      if (t.charAt(0) !== '[') {
        console.warn('api/cities did not return a JSON array (often HTML from a 404 or PHP error). First characters:', t.slice(0, 120));
        return null;
      }
      try {
        return JSON.parse(t);
      } catch (e) {
        console.warn('api/cities JSON parse failed', e);
        return null;
      }
    })
    .then(function (rows) {
      if (!rows) return;
      var sel = document.getElementById('city-select');
      if (!sel || !Array.isArray(rows)) return;
      rows.forEach(function (c) {
        var o = document.createElement('option');
        o.value = c.id;
        o.textContent = c.name + ', ' + c.state;
        sel.appendChild(o);
      });
      document.dispatchEvent(new CustomEvent('nb-cities-loaded'));
    })
    .catch(function (err) {
      if (err && err.message) console.warn('api/cities:', err.message);
    });

  var hiddenPt = document.getElementById('property-type-hidden');
  var tabWrap = document.getElementById('property-type-tabs');
  if (hiddenPt && tabWrap) {
    tabWrap.querySelectorAll('button[data-property-type]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        tabWrap.querySelectorAll('button[data-property-type]').forEach(function (b) {
          b.classList.remove('active');
          b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
        hiddenPt.value = btn.getAttribute('data-property-type') || '';
      });
    });
  }
});
</script>
