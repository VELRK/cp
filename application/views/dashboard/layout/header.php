<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?> - Homelengo</title>
    <meta name="keywords" content="HTML, CSS, JavaScript, Bootstrap">
    <meta name="description" content="Real Estate HTML Template">
    <meta name="author" content="themesflat.com">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- font -->
    <link rel="stylesheet" href="<?php echo base_url('assets/fonts/fonts.css'); ?>">
    <!-- Icons -->
    <link rel="stylesheet" href="<?php echo base_url('assets/fonts/font-icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/jqueryui.min.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/styles.css'); ?>" />

    <!-- Favicon and Touch Icons  -->
    <link rel="shortcut icon" href="<?php echo base_url('assets/images/logo/favicon.png'); ?>">
    <link rel="apple-touch-icon-precomposed" href="<?php echo base_url('assets/images/logo/favicon.png'); ?>">

    <style>
        .dashboard-container {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .widget-box-2 {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .widget-box-2 .title {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 20px 15px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }
        .table tbody tr:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .table tbody td {
            padding: 20px 15px;
            vertical-align: middle;
            border: none;
        }
        .listing-box {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .listing-box .images {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .listing-box .images img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .listing-box .content .title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .listing-box .content .title a {
            color: #2c3e50;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .listing-box .content .title a:hover {
            color: #667eea;
        }
        .listing-box .content .text-date {
            color: #6c757d;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .listing-box .content .text-btn {
            font-size: 18px;
            font-weight: 700;
            color: #28a745;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state .icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        .empty-state h4 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .empty-state p {
            font-size: 16px;
            margin-bottom: 30px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        .d-flex.gap-4 {
            margin-bottom: 20px;
        }
        .text-primary.fw-7 {
            color: #667eea !important;
            font-weight: 700;
        }
        .fw-6 {
            font-weight: 600;
        }
    </style>
</head>
<body class="body bg-surface counter-scroll">
    <div id="wrapper">
        <div id="page" class="clearfix">
            <div class="layout-wrap">
                <!-- header -->
                <header class="main-header fixed-header header-dashboard">
                    <!-- Header Lower -->
                    <div class="header-lower">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="inner-header">
                                    <div class="inner-header-left">
                                        <div class="logo-box d-flex">
                                            <div class="logo"><a href="<?php echo base_url(); ?>"><img src="<?php echo base_url('assets/images/logo/logo@2x.webp'); ?>" alt="logo" width="174" height="44"></a></div>
                                           
                                        </div>
                                    </div>

                                    <div class="mobile-nav-toggler mobile-button"><span></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Header Lower -->
                </header>
                <!-- end header -->


