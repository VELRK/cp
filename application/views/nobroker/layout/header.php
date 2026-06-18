<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
$nb = $CI->session->userdata('nb_user');
$gmaps = $CI->config->item('google_maps_api_key');
$pageTitle = isset($page_title) ? $page_title : 'Coimbatore Properties';
$is_landing = !empty($is_landing);
$home_hero_light = !empty($home_hero_light);
$nb_landing_light_nav = $is_landing && $home_hero_light;
$nb_page_search = !empty($nb_page_search);
$nb_page_property = !empty($nb_page_property);
$nb_seo = isset($nb_seo) && is_array($nb_seo) ? $nb_seo : array();
$nb_json_ld = isset($nb_json_ld) ? $nb_json_ld : '';
?><!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <script>window.NB_BASE_URL = <?php
  $nb_base_url = rtrim((string) $CI->config->item('base_url'), '/') . '/';
  echo json_encode($nb_base_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
  ?>;</script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo html_escape($pageTitle); ?></title>
  <?php if (!empty($nb_seo['description'])): ?>
    <meta name="description" content="<?php echo html_escape($nb_seo['description']); ?>">
  <?php endif; ?>
  <?php if (!empty($nb_seo['canonical'])): ?>
    <link rel="canonical" href="<?php echo html_escape($nb_seo['canonical']); ?>">
  <?php endif; ?>
  <meta property="og:type" content="website">
  <?php if (!empty($nb_seo['og_title'])): ?>
    <meta property="og:title" content="<?php echo html_escape($nb_seo['og_title']); ?>">
  <?php endif; ?>
  <?php if (!empty($nb_seo['og_description'])): ?>
    <meta property="og:description" content="<?php echo html_escape($nb_seo['og_description']); ?>">
  <?php endif; ?>
  <?php if (!empty($nb_seo['canonical'])): ?>
    <meta property="og:url" content="<?php echo html_escape($nb_seo['canonical']); ?>">
  <?php endif; ?>
  <?php if (!empty($nb_seo['og_image'])): ?>
    <meta property="og:image" content="<?php echo html_escape($nb_seo['og_image']); ?>">
  <?php endif; ?>
  <meta name="twitter:card" content="<?php echo !empty($nb_seo['og_image']) ? 'summary_large_image' : 'summary'; ?>">
  <?php if (!empty($nb_json_ld)): ?>
    <script type="application/ld+json"><?php echo $nb_json_ld; ?></script>
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?php echo base_url('assets/css/nobroker.css'); ?>" rel="stylesheet">
</head>

<body
  class="nb-body <?php echo $is_landing ? 'nb-page-home' . ($home_hero_light ? ' nb-home-light-hero' : '') : ''; ?><?php echo $nb_page_search ? ' nb-page-search' : ''; ?><?php echo $nb_page_property ? ' nb-page-property' : ''; ?>">
  <nav
    class="navbar navbar-expand-lg fixed-top <?php echo $is_landing ? 'navbar-nb-landing' : 'navbar-light bg-white border-bottom shadow-sm'; ?>"
    id="nbNavbar">
    <div class="container">
      <a class="navbar-brand fw-bold nb-brand" href="<?php echo site_url(''); ?>"><i
          class="bi bi-house-heart-fill me-1"></i>Coimbatore Properties</a>
      <button class="navbar-toggler <?php echo $is_landing ? 'navbar-toggler-nb' : ''; ?>" type="button"
        data-bs-toggle="collapse" data-bs-target="#nbNav" aria-controls="nbNav" aria-expanded="false"
        aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="nbNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link nb-nav-link" href="<?php echo site_url('search'); ?>">Search</a></li>
          <?php if ($nb && $nb['role'] === 'owner' && $nb['status'] === 'approved'): ?>
            <li class="nav-item"><a class="nav-link nb-nav-link"
                href="<?php echo site_url('owner/dashboard'); ?>">Owner</a></li>
            <li class="nav-item"><a class="nav-link nb-nav-link"
                href="<?php echo site_url('owner/enquiries'); ?>">Enquiries</a></li>
            <li class="nav-item"><a class="nav-link nb-nav-link" href="<?php echo site_url('tenant/enquiries'); ?>">Sent
                enquiries</a></li>
          <?php endif; ?>
          <?php if ($nb && $nb['role'] === 'tenant' && $nb['status'] === 'approved'): ?>
            <li class="nav-item"><a class="nav-link nb-nav-link"
                href="<?php echo site_url('tenant/dashboard'); ?>">Tenant</a></li>
            <li class="nav-item"><a class="nav-link nb-nav-link" href="<?php echo site_url('user/wishlist'); ?>">My
                Wishlist</a></li>
          <?php endif; ?>
          <?php if ($nb && $nb['status'] === 'approved' && in_array($nb['role'], array('owner', 'tenant', 'admin'), true)): ?>
            <li class="nav-item"><a class="nav-link nb-nav-link" href="<?php echo site_url('user/live-updates'); ?>">Live
                Updates</a></li>
            <li class="nav-item"><a class="nav-link nb-nav-link"
                href="<?php echo site_url('user/feedback'); ?>">Feedback</a></li>
          <?php endif; ?>
          <?php if ($nb && $nb['role'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link nb-nav-link" href="<?php echo site_url('panel'); ?>">Admin</a></li>
          <?php endif; ?>
        </ul>
        <div class="d-flex gap-2 align-items-center flex-wrap">
          <?php if ($nb): ?>
            <span class="small nb-nav-meta"><?php echo html_escape($nb['name']); ?>
              <span class="badge rounded-pill nb-role-pill"><?php echo html_escape($nb['role']); ?></span>
            </span>
            <a class="btn btn-sm <?php
            if ($nb_landing_light_nav) {
              echo 'btn-outline-secondary';
            } elseif ($is_landing) {
              echo 'btn-outline-light nb-btn-nav';
            } else {
              echo 'btn-outline-secondary';
            }
            ?>" href="<?php echo site_url('logout'); ?>">Logout</a>
          <?php else: ?>
            <a class="btn btn-sm <?php
            if ($nb_landing_light_nav) {
              echo 'btn-outline-danger';
            } elseif ($is_landing) {
              echo 'btn-outline-light nb-btn-nav';
            } else {
              echo 'btn-outline-primary';
            }
            ?>" href="<?php echo base_url(); ?>?modal=login" data-bs-toggle="modal" data-bs-target="#nbModalLogin"
              role="button">Login</a>
            <a class="btn btn-sm <?php
            if ($nb_landing_light_nav) {
              echo 'btn-danger fw-semibold';
            } elseif ($is_landing) {
              echo 'btn-light nb-text-brand fw-semibold';
            } else {
              echo 'btn-danger';
            }
            ?>" href="<?php echo base_url(); ?>?modal=register" data-bs-toggle="modal" data-bs-target="#nbModalRegister"
              role="button">Register Free</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
  <?php
  $nb_flash_ok = $CI->session->flashdata('nb_ok');
  $nb_flash_err_html = $CI->session->flashdata('nb_err_html');
  $nb_flash_err = $CI->session->flashdata('nb_err');
  ?>
  <?php if ($nb_flash_ok): ?>
    <div class="alert alert-success rounded-0 mb-0 border-0"><?php echo html_escape($nb_flash_ok); ?></div>
  <?php endif; ?>
  <?php if ($nb_flash_err_html): ?>
    <?php echo $nb_flash_err_html; ?>
  <?php elseif ($nb_flash_err): ?>
    <div class="alert alert-danger rounded-0 mb-0 border-0"><?php echo html_escape($nb_flash_err); ?></div>
  <?php endif; ?>
  <main class="nb-main" id="nbMain">