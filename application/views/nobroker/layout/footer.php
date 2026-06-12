<?php defined('BASEPATH') OR exit('No direct script access allowed');
$is_landing = !empty($is_landing);
$nb_full_footer = !empty($nb_full_footer);
$cities_footer = isset($cities_footer) ? $cities_footer : array();
$CI =& get_instance();
$nb_footer_guest = !$CI->session->userdata('nb_user');
$nb_user_footer = $CI->session->userdata('nb_user');
$nb_gmaps_key = $CI->config->item('google_maps_api_key');
$nb_load_maps = !empty($load_maps);
$nb_admin_contact = $CI->config->item('nb_admin_email');
if (empty($cities_footer) && (!$is_landing && !$nb_full_footer)) {
    $CI->load->model('Nb_city_model');
    $cities_footer = $CI->Nb_city_model->all_active();
}
?>
</main>
<?php if ($is_landing || $nb_full_footer) : ?>
<footer class="nb-footer-site nb-footer-landing <?php echo $is_landing ? 'mt-0' : 'mt-5'; ?>">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5">
        <div class="nb-footer-brand mb-2"><i class="bi bi-house-heart-fill nb-text-brand me-1"></i>Dream Villa Makers</div>
        <p class="small text-white-50 mb-4">Find rental homes and properties for sale — verified listings, zero brokerage.</p>
        <div class="d-flex gap-3 mb-3">
          <a href="#" class="fs-5" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" class="fs-5" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
          <a href="#" class="fs-5" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" class="fs-5" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
        </div>
        <?php if (!empty($nb_admin_contact)) : ?>
        <p class="small text-white-50 mb-0"><i class="bi bi-envelope me-1"></i><a href="mailto:<?php echo html_escape($nb_admin_contact); ?>" class="text-white-50"><?php echo html_escape($nb_admin_contact); ?></a></p>
        <?php endif; ?>
      </div>
      <div class="col-6 col-lg-3">
        <div class="fw-semibold text-white mb-2 small text-uppercase">Explore</div>
        <ul class="list-unstyled small mb-0">
          <li class="mb-2"><a href="<?php echo site_url(''); ?>">Home</a></li>
          <li class="mb-2"><a href="<?php echo site_url('search'); ?>">Search properties</a></li>
          <?php if ($nb_footer_guest) : ?>
          <li class="mb-2"><a href="<?php echo base_url(); ?>?modal=login" data-bs-toggle="modal" data-bs-target="#nbModalLogin">Login</a></li>
          <li><a href="<?php echo base_url(); ?>?modal=register" data-bs-toggle="modal" data-bs-target="#nbModalRegister">Register</a></li>
          <?php else : ?>
          <?php if ($nb_user_footer && $nb_user_footer['role'] === 'admin') : ?>
          <li class="mb-2"><a href="<?php echo site_url('panel'); ?>">Admin panel</a></li>
          <?php endif; ?>
          <?php if ($nb_user_footer && $nb_user_footer['role'] === 'owner' && ($nb_user_footer['status'] ?? '') === 'approved') : ?>
          <li class="mb-2"><a href="<?php echo site_url('owner/dashboard'); ?>">Owner dashboard</a></li>
          <?php endif; ?>
          <?php if ($nb_user_footer && $nb_user_footer['role'] === 'tenant' && ($nb_user_footer['status'] ?? '') === 'approved') : ?>
          <li class="mb-2"><a href="<?php echo site_url('tenant/dashboard'); ?>">Tenant dashboard</a></li>
          <li class="mb-2"><a href="<?php echo site_url('user/wishlist'); ?>">My wishlist</a></li>
          <?php endif; ?>
          <?php if ($nb_user_footer && ($nb_user_footer['status'] ?? '') === 'approved' && in_array($nb_user_footer['role'], array('owner', 'tenant', 'admin'), true)) : ?>
          <li class="mb-2"><a href="<?php echo site_url('user/feedback'); ?>">Feedback</a></li>
          <?php endif; ?>
          <li><a href="<?php echo site_url('logout'); ?>">Logout</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="col-6 col-lg-4">
        <div class="fw-semibold text-white mb-2 small text-uppercase">Popular cities</div>
        <ul class="list-unstyled small mb-0">
          <?php
          $n = 0;
          foreach ($cities_footer as $c) :
            if ($n++ >= 8) break;
          ?>
            <li class="mb-2"><a href="<?php echo site_url('search?' . http_build_query(array('city_id' => $c->id))); ?>"><?php echo html_escape($c->name); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <hr class="border-secondary my-4 opacity-25">
    <div class="nb-footer-legal d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 gap-md-3 small text-white-50 w-100">
      <span class="text-center text-md-start mb-0">&copy; <?php echo date('Y'); ?> Dream Villa Makers. All rights reserved.</span>
      <span class="text-white-50 text-center text-md-end flex-shrink-0">
        <a href="#" class="text-white-50 text-decoration-none me-3">Privacy policy</a>
        <a href="#" class="text-white-50 text-decoration-none">Terms of use</a>
      </span>
    </div>
  </div>
</footer>
<?php else : ?>
<footer class="nb-footer-site nb-footer-landing mt-5">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-5">
        <div class="nb-footer-brand mb-2"><i class="bi bi-house-heart-fill nb-text-brand me-1"></i>Dream Villa Makers</div>
        <p class="small text-white-50 mb-3 mb-lg-4">Find rental homes and properties for sale — verified listings, zero brokerage.</p>
        <div class="d-flex gap-3 mb-3">
          <a href="#" class="fs-5" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" class="fs-5" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
          <a href="#" class="fs-5" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" class="fs-5" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
        </div>
        <?php if (!empty($nb_admin_contact)) : ?>
        <p class="small text-white-50 mb-0"><i class="bi bi-envelope me-1"></i><a href="mailto:<?php echo html_escape($nb_admin_contact); ?>" class="text-white-50"><?php echo html_escape($nb_admin_contact); ?></a></p>
        <?php endif; ?>
      </div>
      <div class="col-6 col-lg-3">
        <div class="fw-semibold text-white mb-2 small text-uppercase">Explore</div>
        <ul class="list-unstyled small mb-0">
          <li class="mb-2"><a href="<?php echo site_url(''); ?>">Home</a></li>
          <li class="mb-2"><a href="<?php echo site_url('search'); ?>">Search properties</a></li>
          <?php if ($nb_footer_guest) : ?>
          <li class="mb-2"><a href="<?php echo base_url(); ?>?modal=login" data-bs-toggle="modal" data-bs-target="#nbModalLogin">Login</a></li>
          <li><a href="<?php echo base_url(); ?>?modal=register" data-bs-toggle="modal" data-bs-target="#nbModalRegister">Register</a></li>
          <?php else : ?>
          <?php if ($nb_user_footer && $nb_user_footer['role'] === 'admin') : ?>
          <li class="mb-2"><a href="<?php echo site_url('panel'); ?>">Admin panel</a></li>
          <?php endif; ?>
          <?php if ($nb_user_footer && $nb_user_footer['role'] === 'owner' && ($nb_user_footer['status'] ?? '') === 'approved') : ?>
          <li class="mb-2"><a href="<?php echo site_url('owner/dashboard'); ?>">Owner dashboard</a></li>
          <?php endif; ?>
          <?php if ($nb_user_footer && $nb_user_footer['role'] === 'tenant' && ($nb_user_footer['status'] ?? '') === 'approved') : ?>
          <li class="mb-2"><a href="<?php echo site_url('tenant/dashboard'); ?>">Tenant dashboard</a></li>
          <li class="mb-2"><a href="<?php echo site_url('user/wishlist'); ?>">My wishlist</a></li>
          <?php endif; ?>
          <?php if ($nb_user_footer && ($nb_user_footer['status'] ?? '') === 'approved' && in_array($nb_user_footer['role'], array('owner', 'tenant', 'admin'), true)) : ?>
          <li class="mb-2"><a href="<?php echo site_url('user/feedback'); ?>">Feedback</a></li>
          <?php endif; ?>
          <li><a href="<?php echo site_url('logout'); ?>">Logout</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="fw-semibold text-white mb-2 small text-uppercase">Popular cities</div>
        <ul class="list-unstyled small mb-0 row row-cols-2 g-0">
          <?php
          $n = 0;
          foreach ($cities_footer as $c) :
            if ($n++ >= 8) {
                break;
            }
          ?>
            <li class="col mb-2"><a href="<?php echo site_url('search?' . http_build_query(array('city_id' => $c->id))); ?>"><?php echo html_escape($c->name); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <hr class="border-secondary my-4 opacity-25">
    <div class="nb-footer-legal d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 gap-md-3 small text-white-50 w-100">
      <span class="text-center text-md-start mb-0">&copy; <?php echo date('Y'); ?> Dream Villa Makers. All rights reserved.</span>
      <span class="text-white-50 text-center text-md-end flex-shrink-0">
        <a href="#" class="text-white-50 text-decoration-none me-3">Privacy policy</a>
        <a href="#" class="text-white-50 text-decoration-none">Terms of use</a>
      </span>
    </div>
  </div>
</footer>
<?php endif; ?>
<?php
if ($nb_footer_guest) {
    $CI->load->model('Nb_city_model');
    $CI->load->view('nobroker/layout/auth_modals', array('modal_cities' => $CI->Nb_city_model->all_active()));
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  function nbOpenAuthModal() {
    var q = new URLSearchParams(window.location.search).get('modal');
    if (q !== 'login' && q !== 'register') return;
    var id = q === 'login' ? 'nbModalLogin' : 'nbModalRegister';
    var el = document.getElementById(id);
    if (!el || typeof bootstrap === 'undefined') return;
    var m = bootstrap.Modal.getOrCreateInstance(el);
    m.show();
    if (window.history && window.history.replaceState) {
      var u = new URL(window.location.href);
      u.searchParams.delete('modal');
      window.history.replaceState({}, '', u.pathname + u.search + u.hash);
    }
  }
  document.addEventListener('DOMContentLoaded', nbOpenAuthModal);
})();
</script>
<script src="<?php echo base_url('assets/js/nb_wishlist_toggle.js'); ?>"></script>
<?php if ($is_landing) : ?>
<script src="<?php echo base_url('assets/js/nb_landing.js'); ?>"></script>
<?php endif; ?>
<?php if (!empty($nb_page_search)) : ?>
<script src="<?php echo base_url('assets/js/nb_search_infinite.js'); ?>"></script>
<?php endif; ?>
</body>
</html>
