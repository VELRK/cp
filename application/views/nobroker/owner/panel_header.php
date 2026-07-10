<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$owner_nav = isset($owner_nav) ? $owner_nav : '';
$pt = isset($page_title) ? $page_title : 'Owner dashboard';
$nb = isset($nb_user) ? $nb_user : null;
if ($nb === null) {
    $CI =& get_instance();
    $nb = $CI->session->userdata('nb_user');
}
$pending_visits = isset($pending_visits) ? (int) $pending_visits : 0;
if ($pending_visits < 1) {
    $CI =& get_instance();
    $uid = (int) $CI->session->userdata('nb_user_id');
    if ($uid > 0) {
        $CI->load->model('Site_visit_model');
        $pending_visits = $CI->Site_visit_model->count_for_owner($uid, 'pending');
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <script>window.NB_BASE_URL = <?php echo json_encode(rtrim(base_url(), '/') . '/'); ?>;</script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?php echo html_escape($pt); ?> | Coimbatore Properties</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?php echo base_url('assets/css/nb_admin.css'); ?>" rel="stylesheet">
  <link href="<?php echo base_url('assets/css/nb_owner_panel.css'); ?>" rel="stylesheet">
</head>
<body>
<div class="nb-admin-layout nb-owner-panel">
  <aside class="nb-admin-sidebar d-none d-lg-flex" aria-label="Owner navigation">
    <div class="nb-admin-brand">
      <div class="d-flex align-items-center gap-3">
        <div class="nb-admin-brand-mark"><i class="bi bi-house-heart-fill"></i></div>
        <div>
          <div class="nb-admin-brand-title">Owner Panel</div>
          <div class="nb-admin-brand-sub"><?php echo $nb ? html_escape($nb['name']) : 'Dashboard'; ?></div>
        </div>
      </div>
    </div>
    <nav class="nb-admin-nav" aria-label="Primary">
      <div class="nb-admin-nav-label">Menu</div>
      <a class="nb-admin-nav-link<?php echo $owner_nav === 'dashboard' ? ' active' : ''; ?>" href="<?php echo site_url('owner/dashboard'); ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
      <a class="nb-admin-nav-link<?php echo $owner_nav === 'listings' ? ' active' : ''; ?>" href="<?php echo site_url('owner/listings'); ?>">
        <i class="bi bi-building"></i> My Listings
      </a>
      <a class="nb-admin-nav-link<?php echo $owner_nav === 'site_visits' ? ' active' : ''; ?>" href="<?php echo site_url('owner/site-visits'); ?>">
        <i class="bi bi-calendar-check"></i> Site Visits
        <?php if ($pending_visits > 0) : ?>
          <span class="badge bg-warning text-dark ms-1"><?php echo (int) $pending_visits; ?></span>
        <?php endif; ?>
      </a>
      <a class="nb-admin-nav-link<?php echo $owner_nav === 'enquiries' ? ' active' : ''; ?>" href="<?php echo site_url('owner/enquiries'); ?>">
        <i class="bi bi-chat-dots"></i> Enquiries
      </a>
      <div class="nb-admin-nav-label mt-3">Actions</div>
      <a class="nb-admin-nav-link" href="<?php echo site_url('owner/property/add'); ?>">
        <i class="bi bi-plus-circle"></i> Add Property
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

  <div class="offcanvas offcanvas-start nb-admin-offcanvas d-lg-none" tabindex="-1" id="nbOwnerOffcanvas" aria-labelledby="nbOwnerOffcanvasLabel">
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
      <h2 class="offcanvas-title h5 text-white mb-0" id="nbOwnerOffcanvasLabel">Owner menu</h2>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
      <nav class="nb-admin-nav flex-grow-1 pt-2">
        <a class="nb-admin-nav-link<?php echo $owner_nav === 'dashboard' ? ' active' : ''; ?>" href="<?php echo site_url('owner/dashboard'); ?>" data-bs-dismiss="offcanvas"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nb-admin-nav-link<?php echo $owner_nav === 'listings' ? ' active' : ''; ?>" href="<?php echo site_url('owner/listings'); ?>" data-bs-dismiss="offcanvas"><i class="bi bi-building"></i> My Listings</a>
        <a class="nb-admin-nav-link<?php echo $owner_nav === 'site_visits' ? ' active' : ''; ?>" href="<?php echo site_url('owner/site-visits'); ?>" data-bs-dismiss="offcanvas"><i class="bi bi-calendar-check"></i> Site Visits<?php if ($pending_visits > 0) : ?> <span class="badge bg-warning text-dark"><?php echo (int) $pending_visits; ?></span><?php endif; ?></a>
        <a class="nb-admin-nav-link<?php echo $owner_nav === 'enquiries' ? ' active' : ''; ?>" href="<?php echo site_url('owner/enquiries'); ?>" data-bs-dismiss="offcanvas"><i class="bi bi-chat-dots"></i> Enquiries</a>
        <a class="nb-admin-nav-link" href="<?php echo site_url('owner/property/add'); ?>" data-bs-dismiss="offcanvas"><i class="bi bi-plus-circle"></i> Add Property</a>
      </nav>
      <div class="nb-admin-sidebar-foot border-top border-secondary border-opacity-25">
        <a class="nb-admin-nav-link" href="<?php echo site_url(''); ?>"><i class="bi bi-box-arrow-up-right"></i> Public site</a>
        <a class="nb-admin-nav-link" href="<?php echo site_url('logout'); ?>"><i class="bi bi-box-arrow-right"></i> Log out</a>
      </div>
    </div>
  </div>

  <div class="nb-admin-main">
    <div class="nb-admin-mobile-bar d-lg-none">
      <button class="btn btn-light border btn-icon" type="button" data-bs-toggle="offcanvas" data-bs-target="#nbOwnerOffcanvas" aria-controls="nbOwnerOffcanvas" aria-label="Open menu">
        <i class="bi bi-list-lg"></i>
      </button>
      <span class="fw-semibold text-dark small text-truncate"><?php echo html_escape($pt); ?></span>
      <a class="btn btn-light border btn-icon" href="<?php echo site_url(''); ?>" title="Public site" aria-label="Public site"><i class="bi bi-house"></i></a>
    </div>
    <div class="nb-admin-content">
      <?php
      $nb_panel_ci =& get_instance();
      $nb_flash_ok = $nb_panel_ci->session->flashdata('nb_ok');
      $nb_flash_err = $nb_panel_ci->session->flashdata('nb_err');
      ?>
      <?php if (!empty($nb_flash_ok)) : ?>
        <div class="alert alert-success border-0 shadow-sm mb-4 rounded-3 py-3 px-4"><?php echo html_escape($nb_flash_ok); ?></div>
      <?php endif; ?>
      <?php if (!empty($nb_flash_err)) : ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3 py-3 px-4"><?php echo html_escape($nb_flash_err); ?></div>
      <?php endif; ?>
