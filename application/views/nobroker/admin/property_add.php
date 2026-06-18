<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="nb-admin-page-head">
  <nav class="nb-admin-breadcrumb" aria-label="breadcrumb">
    <a href="<?php echo site_url('panel'); ?>">Dashboard</a>
    <span class="text-muted mx-1">/</span>
    <a href="<?php echo site_url('panel/properties'); ?>">Properties</a>
  </nav>
  <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
    <div>
      <h1 class="nb-admin-page-title d-flex align-items-center gap-2 flex-wrap">
        <span class="nb-admin-page-icon" aria-hidden="true"><i class="bi bi-building-add"></i></span>
        Add property
      </h1>
      <p class="nb-admin-page-desc mb-0">Create a listing for an approved owner. Set visibility and featured flags, then complete the details below.</p>
    </div>
    <a class="btn btn-outline-secondary rounded-pill px-3 align-self-center" href="<?php echo site_url('panel/properties'); ?>">
      <i class="bi bi-arrow-left me-1"></i> Back to list
    </a>
  </div>
</div>
<div class="nb-admin-property-edit-wrap">
  <?php $this->load->view('nobroker/owner/property_form'); ?>
</div>
