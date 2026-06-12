<?php defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
$posted = !empty($p->created_at) ? date('d/m/Y', strtotime($p->created_at)) : '';
$avail = !empty($p->available_from) ? date('d/m/Y', strtotime($p->available_from)) : '';
$price_line = nb_format_listing_price($p->price, $p->listing_type);
$plot_l = isset($p->plot_length_ft) ? (float) $p->plot_length_ft : 0;
$plot_w = isset($p->plot_width_ft) ? (float) $p->plot_width_ft : 0;
$show_plot = ($p->property_type === 'plot' || $p->property_type === 'others' || $plot_l > 0 || $plot_w > 0);
$nb_uid = ($nb_user && isset($nb_user['id'])) ? (string) $nb_user['id'] : '';

$stat_count = 0;
if (!empty($p->rate_per_sqft)) {
    $stat_count++;
}
if (!empty($p->area_sqft)) {
    $stat_count++;
}
if ($p->bedrooms !== null && $p->bedrooms !== '') {
    $stat_count++;
}
$stat_grid_class = 'nb-detail-stat-grid' . ($stat_count >= 3 ? ' nb-detail-stat-grid-3' : '');
?>
<div class="nb-property-detail nb-property-detail--styled">
  <div class="nb-detail-ambient" aria-hidden="true"></div>
  <div class="container py-4 py-lg-5 nb-detail-container">
    <nav class="nb-detail-crumb mb-2" aria-label="breadcrumb">
      <a class="nb-detail-crumb__link" href="<?php echo site_url(''); ?>"><i class="bi bi-house-door"></i> Home</a>
      <span class="nb-detail-crumb__sep" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
      <a class="nb-detail-crumb__link" href="<?php echo site_url('search'); ?>">Search results</a>
      <span class="nb-detail-crumb__sep" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
      <span class="nb-detail-crumb__current text-truncate d-inline-block align-bottom" style="max-width:min(100%, 16rem)" title="<?php echo html_escape($p->title); ?>"><?php echo html_escape($p->title); ?></span>
    </nav>
    <p class="small mb-3 mb-lg-4"><a href="<?php echo site_url('search'); ?>" class="nb-detail-back-link"><i class="bi bi-arrow-left me-1"></i>Back to search results</a></p>

    <div class="nb-detail-shell">
    <div class="row g-4 g-xl-5">
      <div class="col-lg-8">
        <div class="nb-detail-gallery-wrap mb-4">
          <?php if (!empty($p->is_featured)) : ?>
            <span class="nb-detail-featured-badge"><i class="bi bi-star-fill me-1"></i>Featured</span>
          <?php endif; ?>
          <div id="propCarousel" class="carousel slide carousel-fade nb-detail-carousel"<?php echo count($images) > 1 ? ' data-bs-ride="carousel"' : ''; ?>>
            <div class="carousel-inner">
              <?php if (empty($images)) : ?>
                <div class="carousel-item active">
                  <div class="nb-detail-img-placeholder d-flex flex-column align-items-center justify-content-center text-center">
                    <i class="bi bi-image"></i>
                    <span class="small text-muted mt-2">Photos from owner</span>
                  </div>
                </div>
              <?php else : ?>
                <?php $i = 0; foreach ($images as $im) : ?>
                  <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                    <img src="<?php echo base_url($im); ?>" class="d-block w-100" alt="<?php echo html_escape($p->title); ?> — photo <?php echo (int) ($i + 1); ?>">
                  </div>
                <?php $i++; endforeach; ?>
              <?php endif; ?>
            </div>
            <?php if (count($images) > 1) : ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#propCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#propCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
            <?php endif; ?>
            <?php if (!empty($images)) : ?>
            <div class="position-absolute bottom-0 end-0 m-3 nb-detail-photo-badge bg-dark text-white bg-opacity-90">
              <i class="bi bi-images me-1"></i><?php echo count($images); ?> photos
            </div>
            <?php endif; ?>
          </div>
        </div>

        <?php
        $vid_html = '';
        if (isset($p->video_url) && $p->video_url !== '') {
            $vid_html = nb_video_embed_html($p->video_url);
        }
        ?>
        <?php if ($vid_html !== '') : ?>
        <div class="nb-detail-section nb-detail-section-card mb-4">
          <h2 class="nb-detail-section-title"><i class="bi bi-play-btn"></i>Video tour</h2>
          <div class="nb-video-wrap"><?php echo $vid_html; ?></div>
        </div>
        <?php endif; ?>

        <div class="nb-detail-summary-panel">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
          <h1 class="nb-detail-title mb-0 flex-grow-1"><?php echo html_escape($p->title); ?></h1>
          <div class="d-flex align-items-center gap-2">
            <button
              type="button"
              class="btn btn-outline-danger btn-sm nb-wishlist-toggle"
              data-property-id="<?php echo (int) $p->id; ?>"
              data-user-id="<?php echo html_escape($nb_uid); ?>"
              data-wishlisted="0"
              aria-label="Add to wishlist"
              title="Add to wishlist">
              <i class="bi bi-heart me-1" aria-hidden="true"></i><span>Wishlist</span>
            </button>
            <?php if (isset($p->views) && (int) $p->views > 0) : ?>
              <span class="nb-detail-views-pill"><i class="bi bi-eye me-1"></i><?php echo number_format((int) $p->views); ?> views</span>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($p->address)) : ?>
          <p class="nb-detail-meta-line nb-detail-address-line mb-3"><i class="bi bi-geo-alt-fill me-1"></i><?php echo html_escape($p->address); ?></p>
        <?php endif; ?>

        <div class="nb-detail-price-row mb-4">
          <span class="nb-detail-price"><?php echo html_escape($price_line); ?></span>
          <?php if (!empty($p->is_price_negotiable)) : ?>
            <span class="badge rounded-pill nb-detail-badge-negotiable">Negotiable</span>
          <?php endif; ?>
          <span class="badge rounded-pill <?php echo $p->listing_type === 'rent' ? 'badge-rent' : 'badge-sale'; ?>"><?php echo $p->listing_type === 'rent' ? 'For rent' : 'For sale'; ?></span>
          <span class="badge rounded-pill nb-detail-badge-type"><?php echo html_escape(nb_property_type_label($p->property_type)); ?></span>
        </div>

        <?php if ($stat_count > 0) : ?>
        <div class="<?php echo $stat_grid_class; ?> mb-4 pb-1">
          <?php if (!empty($p->rate_per_sqft)) : ?>
          <div class="nb-detail-stat">
            <div class="nb-detail-stat-label">Rate / sq.ft</div>
            <div class="nb-detail-stat-value">₹<?php echo number_format((float) $p->rate_per_sqft, 0, '.', ','); ?></div>
          </div>
          <?php endif; ?>
          <?php if (!empty($p->area_sqft)) : ?>
          <div class="nb-detail-stat">
            <div class="nb-detail-stat-label"><?php echo $p->property_type === 'plot' ? 'Plot area' : 'Built-up area'; ?></div>
            <div class="nb-detail-stat-value"><?php echo number_format((int) $p->area_sqft); ?> sqft</div>
          </div>
          <?php endif; ?>
          <?php if ($p->bedrooms !== null && $p->bedrooms !== '') : ?>
          <div class="nb-detail-stat">
            <div class="nb-detail-stat-label">Layout</div>
            <div class="nb-detail-stat-value"><?php echo (int) $p->bedrooms; ?> BHK<?php echo ($p->bathrooms !== null && $p->bathrooms !== '') ? ' · ' . (int) $p->bathrooms . ' bath' : ''; ?></div>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($avail !== '' || $posted !== '' || ($show_plot && ($plot_l > 0 || $plot_w > 0)) || (isset($p->has_boundary_wall) && $p->has_boundary_wall !== null && $p->has_boundary_wall !== '')) : ?>
        <div class="nb-detail-info-strip mb-4">
          <div class="row g-3">
            <?php if ($avail !== '') : ?>
            <div class="col-sm-6">
              <div class="nb-detail-info-row">
                <i class="bi bi-key"></i>
                <div>
                  <span class="nb-detail-info-label">Available from</span>
                  <strong class="d-block text-dark"><?php echo html_escape($avail); ?></strong>
                </div>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($posted !== '') : ?>
            <div class="col-sm-6">
              <div class="nb-detail-info-row">
                <i class="bi bi-calendar3"></i>
                <div>
                  <span class="nb-detail-info-label">Listed on</span>
                  <strong class="d-block text-dark"><?php echo html_escape($posted); ?></strong>
                </div>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($show_plot && ($plot_l > 0 || $plot_w > 0)) : ?>
            <div class="col-sm-6">
              <div class="nb-detail-info-row">
                <i class="bi bi-bounding-box"></i>
                <div>
                  <span class="nb-detail-info-label">Plot size (L × W)</span>
                  <strong class="d-block text-dark"><?php echo html_escape(rtrim(rtrim(number_format($plot_l, 2, '.', ''), '0'), '.')); ?> × <?php echo html_escape(rtrim(rtrim(number_format($plot_w, 2, '.', ''), '0'), '.')); ?> ft</strong>
                </div>
              </div>
            </div>
            <?php endif; ?>
            <?php if (isset($p->has_boundary_wall) && $p->has_boundary_wall !== null && $p->has_boundary_wall !== '') : ?>
            <div class="col-sm-6">
              <div class="nb-detail-info-row">
                <i class="bi bi-border-outer"></i>
                <div>
                  <span class="nb-detail-info-label">Boundary wall</span>
                  <strong class="d-block text-dark"><?php echo ((string) $p->has_boundary_wall === '1') ? 'Yes' : 'No'; ?></strong>
                </div>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

        <p class="nb-detail-meta-line nb-detail-locality-line mb-0"><i class="bi bi-pin-map me-1"></i><?php echo html_escape($p->locality); ?> · <?php echo html_escape($p->city_name); ?></p>
        </div>

        <div class="nb-detail-about">
          <div class="nb-detail-about__accent" aria-hidden="true"></div>
          <div class="nb-detail-about__inner">
            <h2 class="nb-detail-section-title mb-3"><i class="bi bi-text-left"></i>About this property</h2>
            <div class="nb-detail-description mb-0"><?php echo nl2br(html_escape($p->description)); ?></div>
          </div>
        </div>

        <?php if (!empty($amenities)) : ?>
        <div class="nb-detail-section nb-detail-amenities-block nb-detail-section-card">
          <h2 class="nb-detail-section-title"><i class="bi bi-stars"></i>Amenities</h2>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($amenities as $a) : ?>
              <span class="nb-amenity-pill"><?php echo html_escape($a); ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($p->location) || !empty($p->location_image)) : ?>
        <div class="nb-detail-section nb-detail-section-card">
          <h2 class="nb-detail-section-title"><i class="bi bi-pin-map-fill"></i>Detailed Location</h2>
          <?php if (!empty($p->location_image)) : ?>
            <div class="mb-3">
              <img src="<?php echo base_url($p->location_image); ?>" alt="Location Map" class="img-fluid rounded-3 shadow-sm border">
            </div>
          <?php endif; ?>
          <?php if (!empty($p->location)) : ?>
            <div class="nb-detail-locality-desc">
              <?php if (filter_var($p->location, FILTER_VALIDATE_URL)) : ?>
                <a href="<?php echo html_escape($p->location); ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill"><i class="bi bi-box-arrow-up-right me-1"></i>View on External Map</a>
              <?php else : ?>
                <p class="mb-0 text-secondary small"><?php echo nl2br(html_escape($p->location)); ?></p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="col-lg-4 d-flex">
        <div class="card nb-detail-cta border-0 sticky-lg-top nb-detail-cta-sticky w-100 align-self-start">
          <div class="nb-detail-cta-head">
            <h2>Get owner details</h2>
            <p>Send a secure enquiry. We route it to the owner — you’ll be contacted on your phone or email.</p>
          </div>
          <div class="card-body p-4">
            <?php if (!$nb_user) : ?>
              <a href="<?php echo base_url(); ?>?modal=login" class="btn btn-danger w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#nbModalLogin">Get owner details</a>
              <p class="small text-muted mt-3 mb-0 text-center">Sign in with an approved account to submit an enquiry. New users <a href="<?php echo base_url(); ?>?modal=register" data-bs-toggle="modal" data-bs-target="#nbModalRegister">register as property owners</a>.</p>
            <?php elseif (!empty($can_enquire)) : ?>
              <form id="enq-form">
                <input type="hidden" name="property_id" value="<?php echo (int) $p->id; ?>">
                <div class="mb-2"><label class="form-label small text-muted mb-1">Name</label><input type="text" class="form-control form-control-sm" name="name" value="<?php echo html_escape($nb_user['name']); ?>" readonly></div>
                <div class="mb-2"><label class="form-label small text-muted mb-1">Email</label><input type="email" class="form-control form-control-sm" name="email" required placeholder="Email" value="<?php echo html_escape($nb_user['email']); ?>"></div>
                <div class="mb-2"><label class="form-label small text-muted mb-1">Phone</label><input type="text" class="form-control form-control-sm" name="phone" required placeholder="Phone" value="<?php echo isset($nb_user['phone']) ? html_escape($nb_user['phone']) : ''; ?>"></div>
                <div class="mb-3"><label class="form-label small text-muted mb-1">Message</label><textarea name="message" class="form-control form-control-sm" rows="3" placeholder="Message" required>I’m interested in this listing. Please share owner contact details.</textarea></div>
                <button type="submit" class="btn btn-danger w-100 fw-semibold" id="enq-submit">Send enquiry</button>
              </form>
              <div class="modal fade" id="nbEnquiryResultModal" tabindex="-1" aria-labelledby="nbEnquiryResultModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0" id="nbEnquiryResultModalHeader">
                      <h2 class="modal-title h5 fw-semibold d-flex align-items-center gap-2 mb-0" id="nbEnquiryResultModalLabel">
                        <i class="bi fs-4" id="nbEnquiryResultIcon" aria-hidden="true"></i>
                        <span id="nbEnquiryResultTitleText">Enquiry</span>
                      </h2>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2 text-secondary" id="nbEnquiryResultBody"></div>
                    <div class="modal-footer border-0 pt-0">
                      <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">OK</button>
                    </div>
                  </div>
                </div>
              </div>
              <script>
              (function () {
                var form = document.getElementById('enq-form');
                var btn = document.getElementById('enq-submit');
                var modalEl = document.getElementById('nbEnquiryResultModal');
                var iconEl = document.getElementById('nbEnquiryResultIcon');
                var titleEl = document.getElementById('nbEnquiryResultTitleText');
                var bodyEl = document.getElementById('nbEnquiryResultBody');
                var btnLabel = btn.textContent.trim();
                function showEnquiryModal(ok, msg) {
                  msg = msg || (ok ? 'Your enquiry was sent successfully.' : 'Something went wrong. Please try again.');
                  titleEl.textContent = ok ? 'Enquiry sent' : 'Could not send';
                  bodyEl.textContent = msg;
                  iconEl.className = 'bi fs-4 ' + (ok ? 'bi-check-circle-fill text-success' : 'bi-exclamation-circle-fill text-danger');
                  if (typeof bootstrap !== 'undefined' && modalEl) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                  } else {
                    alert(msg);
                  }
                }
                form.addEventListener('submit', function (e) {
                  if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                  }
                  e.preventDefault();
                  var fd = new FormData(form);
                  btn.disabled = true;
                  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Sending…';
                  fetch('<?php echo site_url('api/enquiry/send'); ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                      return r.text().then(function (t) {
                        var j = {};
                        try { j = t ? JSON.parse(t) : {}; } catch (err) {}
                        return { ok: r.ok, status: r.status, json: j };
                      });
                    })
                    .then(function (res) {
                      var j = res.json || {};
                      var success = res.ok && j.success === true;
                      var msg = j.message || (success ? 'Your enquiry was sent. We\'ll route it to the owner.' : (res.status === 401 ? 'Please sign in to send an enquiry.' : (res.status === 403 ? (j.message || 'You cannot send this enquiry.') : 'Request failed. Please try again.')));
                      showEnquiryModal(success, msg);
                    })
                    .catch(function () {
                      showEnquiryModal(false, 'Network error. Check your connection and try again.');
                    })
                    .finally(function () {
                      btn.disabled = false;
                      btn.textContent = btnLabel;
                    });
                });
              })();
              </script>
            <?php elseif ($nb_user['status'] !== 'approved') : ?>
              <p class="small text-muted mb-0">Your account must be approved before you can send enquiries.</p>
            <?php elseif ((int) $nb_user['id'] === (int) $p->owner_id) : ?>
              <p class="small text-muted mb-0">This is your listing — enquiries from buyers appear under Owner → Enquiries.</p>
            <?php else : ?>
              <p class="small text-muted mb-0">Property enquiries are not available for your account type.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    </div>

    <?php if (!empty($similar)) : ?>
    <section class="mt-4 mt-lg-5 pt-2 pt-lg-4 nb-detail-similar-section nb-detail-similar-shell">
      <div class="nb-detail-similar-head-wrap">
        <h2 class="nb-detail-similar-head"><i class="bi bi-layers"></i>Similar properties</h2>
        <p class="nb-detail-similar-sub mb-0">More homes like this in the same city &amp; type</p>
      </div>
      <div class="row g-4 nb-property-grid">
        <?php foreach ($similar as $sp) : ?>
          <?php $CI->load->view('nobroker/_property_card', array('p' => $sp)); ?>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </div>
</div>
