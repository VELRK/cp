<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$nav = isset($admin_nav) ? $admin_nav : '';
$pt = isset($page_title) ? $page_title : 'Admin';
?><!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <script>window.NB_BASE_URL = <?php echo json_encode(rtrim(base_url(), '/') . '/'); ?>;</script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?php echo html_escape($pt); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?php echo base_url('assets/css/nb_admin.css'); ?>" rel="stylesheet">
  <script>
  (function () {
    try {
      var t = localStorage.getItem('nb_token');
      if (!t || document.cookie.indexOf('nb_token=') !== -1) {
        return;
      }
      if (sessionStorage.getItem('nb_panel_auth_sync') === '1') {
        return;
      }
      sessionStorage.setItem('nb_panel_auth_sync', '1');
      var authBase = (typeof window.NB_BASE_URL === 'string' && window.NB_BASE_URL) ? window.NB_BASE_URL : '/';
      window.location.replace(authBase + 'panel/auth?token=' + encodeURIComponent(t));
    } catch (e) {}
  })();
  </script>
</head>

<body>
  <div class="nb-admin-layout">
    <aside class="nb-admin-sidebar d-none d-lg-flex" aria-label="Admin navigation">
      <div class="nb-admin-brand">
        <div class="d-flex align-items-center gap-3">
          <div class="nb-admin-brand-mark"><i class="bi bi-grid-1x2-fill"></i></div>
          <div>
            <div class="nb-admin-brand-title">Coimbatore Properties Admin</div>
            <div class="nb-admin-brand-sub">Control panel</div>
          </div>
        </div>
      </div>
      <nav class="nb-admin-nav" aria-label="Primary">
        <div class="nb-admin-nav-label">Menu</div>
        <a class="nb-admin-nav-link<?php echo $nav === 'dashboard' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel'); ?>">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'users' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/users'); ?>">
          <i class="bi bi-people"></i> Users
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'properties' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/properties'); ?>">
          <i class="bi bi-building"></i> Properties
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'approvals' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/properties/pending'); ?>">
          <i class="bi bi-patch-check"></i> Approvals
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'enquiries' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/enquiries'); ?>">
          <i class="bi bi-chat-dots"></i> Enquiries
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'cities' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/cities'); ?>">
          <i class="bi bi-geo-alt"></i> Cities
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'amenities' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/amenities'); ?>">
          <i class="bi bi-check2-square"></i> Amenities
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'property_types' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/property-types'); ?>">
          <i class="bi bi-tags"></i> Property Types
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'banners' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/banners'); ?>">
          <i class="bi bi-images"></i> Banners
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'wishlists' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/wishlists'); ?>">
          <i class="bi bi-heart"></i> Wishlists
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'live_updates' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/live-updates'); ?>">
          <i class="bi bi-broadcast"></i> Live Updates
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'housing_news' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/housing-news'); ?>">
          <i class="bi bi-newspaper"></i> Housing News
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'feedbacks' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/feedbacks'); ?>">
          <i class="bi bi-chat-left-text"></i> Feedback
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'notifications' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/notifications'); ?>">
          <i class="bi bi-bell"></i> Notifications
        </a>
        <a class="nb-admin-nav-link<?php echo $nav === 'delete_requests' ? ' active' : ''; ?>"
          href="<?php echo site_url('panel/delete-requests'); ?>">
          <i class="bi bi-person-x"></i> Delete Requests
        </a>
      </nav>
      <div class="nb-admin-sidebar-foot">
        <a class="nb-admin-nav-link" href="<?php echo site_url(''); ?>">
          <i class="bi bi-box-arrow-up-right"></i> View public site
        </a>
        <a class="nb-admin-nav-link" href="<?php echo site_url('logout'); ?>">
          <i class="bi bi-box-arrow-right"></i> Log out
        </a>
      </div>
    </aside>

    <div class="offcanvas offcanvas-start nb-admin-offcanvas d-lg-none" tabindex="-1" id="nbAdminOffcanvas"
      aria-labelledby="nbAdminOffcanvasLabel">
      <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
        <h2 class="offcanvas-title h5 text-white mb-0" id="nbAdminOffcanvasLabel">Menu</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body p-0 d-flex flex-column">
        <nav class="nb-admin-nav flex-grow-1 pt-2">
          <a class="nb-admin-nav-link<?php echo $nav === 'dashboard' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'users' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/users'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-people"></i> Users
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'properties' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/properties'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-building"></i> Properties
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'approvals' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/properties/pending'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-patch-check"></i> Approvals
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'enquiries' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/enquiries'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-chat-dots"></i> Enquiries
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'cities' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/cities'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-geo-alt"></i> Cities
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'amenities' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/amenities'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-check2-square"></i> Amenities
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'property_types' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/property-types'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-tags"></i> Property Types
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'banners' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/banners'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-images"></i> Banners
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'wishlists' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/wishlists'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-heart"></i> Wishlists
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'live_updates' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/live-updates'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-broadcast"></i> Live Updates
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'housing_news' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/housing-news'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-newspaper"></i> Housing News
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'feedbacks' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/feedbacks'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-chat-left-text"></i> Feedback
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'notifications' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/notifications'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-bell"></i> Notifications
          </a>
          <a class="nb-admin-nav-link<?php echo $nav === 'delete_requests' ? ' active' : ''; ?>"
            href="<?php echo site_url('panel/delete-requests'); ?>" data-bs-dismiss="offcanvas">
            <i class="bi bi-person-x"></i> Delete Requests
          </a>
        </nav>
        <div class="nb-admin-sidebar-foot border-top border-secondary border-opacity-25">
          <a class="nb-admin-nav-link" href="<?php echo site_url(''); ?>"><i class="bi bi-box-arrow-up-right"></i>
            Public site</a>
          <a class="nb-admin-nav-link" href="<?php echo site_url('logout'); ?>"><i class="bi bi-box-arrow-right"></i>
            Log out</a>
        </div>
      </div>
    </div>

    <div class="nb-admin-main">
      <div class="nb-admin-mobile-bar d-lg-none">
        <button class="btn btn-light border btn-icon" type="button" data-bs-toggle="offcanvas"
          data-bs-target="#nbAdminOffcanvas" aria-controls="nbAdminOffcanvas" aria-label="Open menu">
          <i class="bi bi-list-lg"></i>
        </button>
        <span class="fw-semibold text-dark small text-truncate"><?php echo html_escape($pt); ?></span>
        <a class="btn btn-light border btn-icon" href="<?php echo site_url(''); ?>" title="Public site"
          aria-label="Public site"><i class="bi bi-house"></i></a>
      </div>
      <div class="nb-admin-content">
        <?php
        $nb_admin_ci =& get_instance();
        $nb_flash_ok = $nb_admin_ci->session->flashdata('nb_ok');
        $nb_flash_err = $nb_admin_ci->session->flashdata('nb_err');
        ?>
        <?php if (!empty($nb_flash_ok)): ?>
          <div class="alert alert-success border-0 shadow-sm mb-4 rounded-3 py-3 px-4">
            <?php echo html_escape($nb_flash_ok); ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($nb_flash_err)): ?>
          <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3 py-3 px-4">
            <?php echo html_escape($nb_flash_err); ?>
          </div>
        <?php endif; ?>