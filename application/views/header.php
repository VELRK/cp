<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <?php
    // Determine page key from URI for DB SEO lookup
    $CI =& get_instance();
    $uriStr = trim(uri_string(), '/');
    $pageKeyMap = [
        '' => 'home',
        'home' => 'home',
        'our-projects' => 'listing',
        'about' => 'about',
        'blog' => 'blog',
        'contact' => 'contact',
        'privacy-policy' => 'privacy-policy',
        'terms-conditions' => 'terms-conditions',
    ];
    $dbSeo = [];
    if (array_key_exists($uriStr, $pageKeyMap)) {
        $CI->load->model('Seo_settings_model');
        $dbSeo = $CI->Seo_settings_model->get_by_key($pageKeyMap[$uriStr]);
    }

    // DB SEO > controller-set > fallback
    // (DB admin settings always override controller defaults for static pages)
    $seoTitle = (!empty($dbSeo['meta_title']))
        ? $dbSeo['meta_title']
        : ((isset($title) && !empty($title)) ? $title : 'Coimbatore Properties');

    $seoDescription = (!empty($dbSeo['meta_description']))
        ? $dbSeo['meta_description']
        : ((isset($meta_description) && !empty($meta_description)) ? $meta_description : 'Coimbatore Properties — find premium villas, apartments, and plots in your city.');

    $seoKeywords = (!empty($dbSeo['meta_keywords']))
        ? $dbSeo['meta_keywords']
        : ((isset($meta_keywords) && !empty($meta_keywords)) ? $meta_keywords : 'real estate, villas, apartments, plots, property, DreamVillaMakers');

    // OG tags
    $ogTitle = (!empty($dbSeo['og_title'])) ? $dbSeo['og_title'] : $seoTitle;
    $ogDescription = (!empty($dbSeo['og_description'])) ? $dbSeo['og_description'] : $seoDescription;

    // Canonical URL
    $canonicalUrl = current_url();
    if (!empty($dbSeo['canonical_url'])) {
        $canonicalUrl = $dbSeo['canonical_url'];
    } elseif ((isset($page) && $page === 'home') || $uriStr === '' || $uriStr === 'home') {
        $canonicalUrl = rtrim(base_url(), '/');
    }
    if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
        $host = strtolower($_SERVER['HTTP_HOST']);
        if ($host === 'dreamvillamakers.com' || $host === 'www.dreamvillamakers.com') {
            $canonicalUrl = preg_replace('#^https?://#i', 'https://', $canonicalUrl);
            $canonicalUrl = str_replace('https://dreamvillamakers.com', 'https://www.dreamvillamakers.com', $canonicalUrl);
        }
    }
    ?>
    <title><?php echo htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($seoKeywords, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="author" content="DreamVillaMakers">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($ogDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="<?php echo base_url('assets/images/logo/favicon.ico'); ?>" type="image/ico">

    <!-- Google Fonts (non-blocking: avoids render-blocking request on critical path) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php $interFontUrl = 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap'; ?>
    <link href="<?php echo htmlspecialchars($interFontUrl, ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet" media="print"
        onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($interFontUrl, ENT_QUOTES, 'UTF-8'); ?>">
    </noscript>

    <!-- Self-hosted font faces + icon font (non-blocking — same pattern as Google Fonts) -->
    <?php
    $dvm_fonts_css = base_url('assets/fonts/fonts.css');
    $dvm_font_icons_css = base_url('assets/fonts/font-icons.css');
    ?>
    <link href="<?php echo htmlspecialchars($dvm_fonts_css, ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet" media="print"
        onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($dvm_fonts_css, ENT_QUOTES, 'UTF-8'); ?>">
    </noscript>
    <link href="<?php echo htmlspecialchars($dvm_font_icons_css, ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet"
        media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($dvm_font_icons_css, ENT_QUOTES, 'UTF-8'); ?>">
    </noscript>

    <!-- CSS Files (preload raises fetch priority for first-paint styles) -->
    <?php
    $dvm_bootstrap_css = base_url('assets/css/bootstrap.min.css');
    $dvm_main_stylesheet = (defined('ENVIRONMENT') && ENVIRONMENT === 'production') ? 'styles.min.css' : 'styles.css';
    $dvm_styles_href = base_url('assets/css/' . $dvm_main_stylesheet);
    ?>
    <link rel="preload" href="<?php echo htmlspecialchars($dvm_bootstrap_css, ENT_QUOTES, 'UTF-8'); ?>" as="style">
    <link rel="preload" href="<?php echo htmlspecialchars($dvm_styles_href, ENT_QUOTES, 'UTF-8'); ?>" as="style">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($dvm_bootstrap_css, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/swiper-bundle.min.css'); ?>">
    <?php $animateCssHref = base_url('assets/css/animate.css'); ?>
    <link href="<?php echo htmlspecialchars($animateCssHref, ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet" media="print"
        onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($animateCssHref, ENT_QUOTES, 'UTF-8'); ?>">
    </noscript>
    <!-- <link rel="stylesheet" href="<?php echo base_url('assets/css/jquery.fancybox.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/jqueryui.min.css'); ?>"> -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($dvm_styles_href, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/site-pages.min.css'); ?>">
    <!-- <link rel="stylesheet" href="<?php echo base_url('assets/css/prism.css'); ?>"> -->

    <!-- Preload Banner Images for Better Performance -->
    <?php if (isset($banners) && !empty($banners) && is_array($banners)): ?>
        <?php
        // Preload first banner image for faster display
        $firstBanner = $banners[0];
        if (isset($firstBanner['imageUrl']) && !empty($firstBanner['imageUrl'])) {
            $preloadImage = nb_upgrade_http_image_url($firstBanner['imageUrl']);
            echo '<link rel="preload" as="image" href="' . htmlspecialchars($preloadImage) . '" fetchpriority="high">';
        }
        ?>
    <?php endif; ?>

</head>

<body>
    <!-- preload -->
    <div id="preload">
        <div class="loader">
            <div class="loader-inner">
                <div class="loader-inner-2"></div>
            </div>
        </div>
    </div>
    <!-- /preload -->

    <div id="wrapper">
        <div id="pagee" class="clearfix">
            <!-- Main Header -->
            <header class="main-header fixed-header">
                <!-- Header Lower -->
                <div class="header-lower">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="inner-header">
                                <div class="inner-header-left">
                                    <div class="logo-box flex">
                                        <div class="logo"><a href="<?php echo base_url(); ?>"><img
                                                    src="<?php echo base_url('assets/images/logo/logo@2x.webp'); ?>"
                                                    alt="logo" width="166" height="48"></a></div>
                                    </div>
                                    <div class="nav-outer flex align-center">
                                        <!-- Main Menu -->
                                        <nav class="main-menu show navbar-expand-md">
                                            <div class="navbar-collapse collapse clearfix" id="navbarSupportedContent">
                                                <ul class="navigation clearfix">
                                                    <li class="<?php echo ($page == 'home') ? 'current' : ''; ?>"><a
                                                            href="<?php echo base_url(); ?>">Home</a></li>
                                                    <li class="<?php echo ($page == 'about') ? 'current' : ''; ?>"><a
                                                            href="<?php echo base_url('about'); ?>">About Us</a></li>
                                                    <li
                                                        class="<?php echo ($page == 'listing' || $page == 'property_detail') ? 'current' : ''; ?>">
                                                        <a href="<?php echo base_url('our-projects'); ?>">Our
                                                            Projects</a></li>

                                                    <li
                                                        class="<?php echo ($page == 'blog' || $page == 'blog_detail') ? 'current' : ''; ?>">
                                                        <a href="<?php echo base_url('blog'); ?>">Blog</a></li>
                                                    <li class="<?php echo ($page == 'contact') ? 'current' : ''; ?>"><a
                                                            href="<?php echo base_url('contact'); ?>">Contact</a></li>
                                                </ul>
                                            </div>
                                        </nav>
                                        <!-- Main Menu End-->
                                    </div>
                                </div>
                                <div class="inner-header-right header-account">
                                    <?php
                                    // Check if user is logged in via session
                                    $CI =& get_instance();
                                    $user = $CI->session->userdata('user');
                                    $isLoggedIn = !empty($user);
                                    ?>
                                    <!-- Login/Register Buttons (shown when not logged in) -->
                                    <!-- <div id="loginRegisterButtons1" style="<?php echo $isLoggedIn ? 'display: none;' : 'display: block;'; ?>">
                                        <a href="#modalLogin" data-bs-toggle="modal" class="tf-btn btn-line btn-login">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM10 3C11.66 3 13 4.34 13 6C13 7.66 11.66 9 10 9C8.34 9 7 7.66 7 6C7 4.34 8.34 3 10 3ZM10 17.2C7.5 17.2 5.29 15.92 4 13.98C4.03 11.99 8 10.9 10 10.9C11.99 10.9 15.97 11.99 16 13.98C14.71 15.92 12.5 17.2 10 17.2Z"
                                                    fill="currentColor" />
                                            </svg>
                                            <span>Login</span>
                                        </a>
                                      
                                    </div> -->

                                    <!-- Dashboard Dropdown (shown when logged in) -->
                                    <div id="userProfile"
                                        style="<?php echo $isLoggedIn ? 'display: block;' : 'display: none;'; ?>">
                                        <div class="dropdown">
                                            <a href="#" class="tf-btn primary" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <svg width="20" height="20" viewBox="0 0 22 22" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <g opacity="0.9">
                                                        <path d="M6.75682 9.35156V15.64" stroke="white"
                                                            stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path d="M11.0342 6.34375V15.6412" stroke="white"
                                                            stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path d="M15.2412 12.6758V15.6412" stroke="white"
                                                            stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M15.2939 1.83398H6.70346C3.70902 1.83398 1.83203 3.95339 1.83203 6.95371V15.0476C1.83203 18.0479 3.70029 20.1673 6.70346 20.1673H15.2939C18.2971 20.1673 20.1654 18.0479 20.1654 15.0476V6.95371C20.1654 3.95339 18.2971 1.83398 15.2939 1.83398Z"
                                                            stroke="white" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </g>
                                                </svg>
                                                <span id="userDisplayName" style="display: none;">Dashboard</span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 4.5L6 7.5L9 4.5" stroke="white" stroke-width="1.5"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15); padding: 8px; margin-top: 8px; min-width: 220px;">
                                                <li id="userEmailContainer">
                                                    <div class="dropdown-item-text"
                                                        style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                                        <div style="font-weight: 600; color: #333; font-size: 14px;"
                                                            id="userNameDisplay">
                                                            <?php
                                                            $CI =& get_instance();
                                                            $user = $CI->session->userdata('user');
                                                            $userName = isset($user['fullName']) ? $user['fullName'] : (isset($user['displayName']) ? $user['displayName'] : 'User');
                                                            echo htmlspecialchars($userName);
                                                            ?>
                                                        </div>
                                                        <div style="font-weight: 500; color: #666; font-size: 13px; margin-top: 4px;"
                                                            id="userEmail">
                                                            <?php
                                                            $userEmail = isset($user['email']) ? $user['email'] : (isset($user['phoneNumber']) ? $user['phoneNumber'] : '');
                                                            echo htmlspecialchars($userEmail);
                                                            ?>
                                                        </div>
                                                        <div style="color: #999; font-size: 11px; margin-top: 2px;">
                                                            Welcome back!</div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="<?php echo base_url('dashboard/enquiries'); ?>"
                                                        style="padding: 12px 16px; border-radius: 8px; color: #333; font-size: 14px; transition: all 0.2s ease; display: flex; align-items: center; gap: 10px;">
                                                        <svg width="18" height="18" viewBox="0 0 22 22" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <g opacity="0.7">
                                                                <path
                                                                    d="M16.4076 8.11328L12.3346 11.4252C11.5651 12.0357 10.4824 12.0357 9.71285 11.4252L5.60547 8.11328"
                                                                    stroke="currentColor" stroke-width="1.5"
                                                                    stroke-linecap="round" stroke-linejoin="round" />
                                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                                    d="M15.4985 19.25C18.2864 19.2577 20.1654 16.9671 20.1654 14.1518V7.85584C20.1654 5.04059 18.2864 2.75 15.4985 2.75H6.49891C3.711 2.75 1.83203 5.04059 1.83203 7.85584V14.1518C1.83203 16.9671 3.711 19.2577 6.49891 19.25H15.4985Z"
                                                                    stroke="currentColor" stroke-width="1.5"
                                                                    stroke-linecap="round" stroke-linejoin="round" />
                                                            </g>
                                                        </svg>
                                                        Enquiry Property
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider" style="margin: 8px 0;">
                                                </li>
                                                <li id="logoutBtnContainer">
                                                    <a class="dropdown-item" href="#" id="logoutBtn"
                                                        style="padding: 12px 16px; border-radius: 8px; color: #dc3545; font-size: 14px; transition: all 0.2s ease; display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                                        <svg width="18" height="18" viewBox="0 0 16 16" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M6 14H3C2.44772 14 2 13.5523 2 13V3C2 2.44772 2.44772 2 3 2H6"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                            <path d="M10 11L13 8L10 5" stroke="currentColor"
                                                                stroke-width="1.5" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                            <path d="M13 8H6" stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                        Logout
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="mobile-nav-toggler mobile-button"><span></span></div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Header Lower -->

                <!-- Mobile Menu  -->
                <div class="mobile-menu">
                    <div class="menu-backdrop"
                        style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998; display: none;">
                    </div>
                    <nav class="menu-box">
                        <div class="nav-logo"><a href="<?php echo base_url(); ?>"><img
                                    src="<?php echo base_url('assets/images/logo/logo@2x.webp'); ?>" alt="nav-logo"
                                    width="174" height="44"></a></div>
                        <div class="bottom-canvas">
                            <div class="menu-outer">
                                <ul class="navigation clearfix">
                                    <li class="<?php echo ($page == 'home') ? 'current' : ''; ?>"><a
                                            href="<?php echo base_url(); ?>">Home</a></li>
                                    <li
                                        class="<?php echo ($page == 'listing' || $page == 'property_detail') ? 'current' : ''; ?>">
                                        <a href="<?php echo base_url('our-projects'); ?>">Our Projects</a></li>
                                    <li class="<?php echo ($page == 'about') ? 'current' : ''; ?>"><a
                                            href="<?php echo base_url('about'); ?>">About Us</a></li>
                                    <li
                                        class="<?php echo ($page == 'blog' || $page == 'blog_detail') ? 'current' : ''; ?>">
                                        <a href="<?php echo base_url('blog'); ?>">Blog</a></li>
                                    <li class="<?php echo ($page == 'contact') ? 'current' : ''; ?>"><a
                                            href="<?php echo base_url('contact'); ?>">Contact</a></li>
                                </ul>
                            </div>
                        </div>
                    </nav>
                </div>
                <!-- End Mobile Menu -->

            </header>
            <!-- End Main Header -->

            <!-- Login Modal - Mobile App Style -->
            <div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="modalLoginLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" id="loginModalContent"
                        style="border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.15); overflow: hidden; background: #ffffff; max-width: 450px; margin: 20px auto;">
                        <div class="modal-body" id="loginModalBody"
                            style="padding: 30px 25px; position: relative; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; overflow: visible;">
                            <!-- Close Button -->
                            <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Close"
                                style="position: absolute; top: 20px; right: 20px; z-index: 10; background: rgba(0,0,0,0.08); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; transition: all 0.3s ease; padding: 0;"
                                onmouseover="this.style.background='rgba(0,0,0,0.15)'; this.style.transform='scale(1.1)'"
                                onmouseout="this.style.background='rgba(0,0,0,0.08)'; this.style.transform='scale(1)'">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" style="color: #2c3e50;">
                                    <path d="M13.5 4.5L4.5 13.5M4.5 4.5L13.5 13.5" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>

                            <!-- Logo centered at top -->
                            <div style="text-align: center; margin-bottom: 30px; margin-top: 10px;">
                                <div
                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff6b6b, #ff8e53); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);">
                                    <span style="color: white; font-weight: 700; font-size: 20px;">G</span>
                                </div>
                            </div>

                            <!-- Welcome Section -->
                            <div class="welcome-section" style="text-align: center; margin-bottom: 40px;">
                                <h4 class="modal-title" id="modalLoginLabel"
                                    style="font-size: 42px; font-weight: 700; color: #2c3e50; margin: 0 0 10px 0; font-family: 'Brush Script MT', cursive, serif; display: flex; align-items: center; justify-content: center; gap: 10px; line-height: 1.2;">
                                    <span style="font-size: 36px;">👋</span>
                                    <span>Welcome Back!</span>
                                </h4>
                                <p style="color: #6c757d; font-size: 16px; margin: 0; font-weight: 400;">Login to
                                    continue your journey.</p>
                            </div>

                            <!-- Login Form -->
                            <form id="loginFormModal" method="post">
                                <div id="loginModalAlert" class="alert d-none"
                                    style="margin-bottom: 20px; border-radius: 12px; font-size: 14px;"></div>
                                <!-- Phone Number Input -->
                                <!-- <div class="form-group mb-3" id="phoneFormGroup" style="margin-bottom: 25px; position: relative; z-index: 1; overflow: visible;">
                                    <div class="phone-input-container" id="phoneInputContainer" style="position: relative; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 15px; padding: 0; overflow: visible; transition: all 0.3s ease; display: flex; align-items: center; height: 60px;">
                                        -->
                                <!-- Country Code Input (Text Box Style) -->
                                <!-- <div style="position: relative; z-index: 1000; height: 100%; display: flex; align-items: center; min-width: 120px; border-right: 1px solid #e9ecef; background: white; border-radius: 15px 0 0 15px;">
                                            <select 
                                                id="countryCodeModal" 
                                                name="countryCode" 
                                                style="width: 100%; padding: 0 15px; border: none; color: #2c3e50; font-weight: 600; font-size: 16px; background: transparent; height: 60px; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; -moz-appearance: none; position: relative; z-index: 1000;"
                                            >
                                                <option value="+91" selected>IN +91</option>
                                                <option value="+1">US +1</option>
                                                <option value="+44">GB +44</option>
                                                <option value="+61">AU +61</option>
                                                <option value="+971">AE +971</option>
                                                <option value="+966">SA +966</option>
                                                <option value="+65">SG +65</option>
                                                <option value="+60">MY +60</option>
                                                <option value="+86">CN +86</option>
                                                <option value="+81">JP +81</option>
                                                <option value="+82">KR +82</option>
                                                <option value="+33">FR +33</option>
                                                <option value="+49">DE +49</option>
                                                <option value="+39">IT +39</option>
                                                <option value="+34">ES +34</option>
                                                <option value="+31">NL +31</option>
                                                <option value="+32">BE +32</option>
                                                <option value="+41">CH +41</option>
                                                <option value="+46">SE +46</option>
                                                <option value="+47">NO +47</option>
                                                <option value="+45">DK +45</option>
                                                <option value="+358">FI +358</option>
                                                <option value="+351">PT +351</option>
                                                <option value="+353">IE +353</option>
                                                <option value="+48">PL +48</option>
                                                <option value="+7">RU +7</option>
                                                <option value="+27">ZA +27</option>
                                                <option value="+55">BR +55</option>
                                                <option value="+52">MX +52</option>
                                                <option value="+54">AR +54</option>
                                                <option value="+64">NZ +64</option>
                                            </select>
                                        </div> -->
                                <!-- Phone Number Input -->
                                <!-- <input 
                                            type="tel" 
                                            class="form-control" 
                                            id="phoneNumberModal" 
                                            name="phoneNumber" 
                                            placeholder="Enter your Phone Number" 
                                            required 
                                            style="flex: 1; border: none; background: transparent; padding: 0 20px; height: 60px; font-size: 16px; color: #2c3e50; outline: none;"
                                            onfocus="this.parentElement.style.borderColor='#3498db'; this.parentElement.style.boxShadow='0 0 0 3px rgba(52, 152, 219, 0.1)'"
                                            onblur="this.parentElement.style.borderColor='#e9ecef'; this.parentElement.style.boxShadow='none'"
                                        > -->
                                <!-- </div>
                                </div> -->

                                <!-- Continue Button -->
                                <!-- <div class="d-grid mb-4" style="margin-bottom: 25px;">
                                    <button 
                                        type="submit" 
                                        class="btn btn-primary btn-continue-modal" 
                                        style="background: #1e3a8a; border: none; border-radius: 15px; height: 55px; font-size: 16px; font-weight: 700; color: white; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3); text-transform: none; letter-spacing: 0;"
                                        onmouseover="this.style.background='#1e40af'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(30, 58, 138, 0.4)'"
                                        onmouseout="this.style.background='#1e3a8a'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(30, 58, 138, 0.3)'"
                                    >
                                        Continue
                                    </button>
                                </div> -->

                                <!-- OR Separator -->
                                <!-- <div class="divider" style="position: relative; text-align: center; margin: 30px 0; display: flex; align-items: center; justify-content: center;">
                                    <div style="flex: 1; height: 1px; background: #e9ecef;"></div>
                                    <span style="padding: 0 15px; color: #6c757d; font-size: 14px; font-weight: 500; background: white;">OR</span>
                                    <div style="flex: 1; height: 1px; background: #e9ecef;"></div>
                                </div> -->

                                <!-- Google Login Button -->
                                <div class="d-grid mb-4" style="margin-bottom: 30px;">
                                    <button type="button" class="btn btn-google-modal"
                                        onclick="handleGoogleLoginModal()"
                                        style="background: white; border: 2px solid #e9ecef; border-radius: 15px; height: 55px; font-size: 16px; font-weight: 600; color: #2c3e50; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);"
                                        onmouseover="this.style.borderColor='#db4437'; this.style.boxShadow='0 4px 12px rgba(219, 68, 55, 0.15)'; this.style.transform='translateY(-2px)'"
                                        onmouseout="this.style.borderColor='#e9ecef'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)'; this.style.transform='translateY(0)'">
                                        <!-- Google Logo -->
                                        <svg width="20" height="20" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                                fill="#4285F4" />
                                            <path
                                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                                fill="#34A853" />
                                            <path
                                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                                fill="#FBBC05" />
                                            <path
                                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                                fill="#EA4335" />
                                        </svg>
                                        <span>Continue with Google</span>
                                    </button>
                                </div>

                                <!-- Skip for now -->
                                <div style="text-align: center; margin-bottom: 20px;">
                                    <a href="#" data-bs-dismiss="modal"
                                        style="color: #2c3e50; text-decoration: none; font-size: 16px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: color 0.3s ease;"
                                        onmouseover="this.style.color='#3498db'"
                                        onmouseout="this.style.color='#2c3e50'">
                                        Skip for now
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </a>
                                </div>

                                <!-- Terms & Conditions -->
                                <div style="text-align: center;">
                                    <a href="<?php echo base_url('legal/terms'); ?>" target="_blank"
                                        style="color: #6c757d; text-decoration: underline; font-size: 14px; transition: color 0.3s ease;"
                                        onmouseover="this.style.color='#3498db'"
                                        onmouseout="this.style.color='#6c757d'">
                                        Terms & Conditions
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Register Modal -->
            <div class="modal fade" id="modalRegister" tabindex="-1" aria-labelledby="modalRegisterLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content"
                        style="border-radius: 25px; border: none; box-shadow: 0 25px 80px rgba(0,0,0,0.15); overflow: hidden; background: #fff;">
                        <div class="modal-header"
                            style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; padding: 0; position: relative; min-height: 200px;">
                            <!-- Animated Background -->
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns=\"
                                http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\">
                                <defs>
                                    <pattern id=\"grain\" width=\"100\" height=\"100\" patternUnits=\"userSpaceOnUse\">
                                        <circle cx=\"50\" cy=\"50\" r=\"1\" fill=\"white\" opacity=\"0.1\" />
                                    </pattern>
                                </defs>
                                <rect width=\"100\" height=\"100\" fill=\"url(%23grain)\" /></svg>') repeat; opacity:
                                0.3;">
                            </div>

                            <!-- Floating Elements -->
                            <div
                                style="position: absolute; top: 20px; left: 20px; width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%; animation: float 6s ease-in-out infinite;">
                            </div>
                            <div
                                style="position: absolute; top: 40px; right: 30px; width: 40px; height: 40px; background: rgba(255,255,255,0.08); border-radius: 50%; animation: float 4s ease-in-out infinite reverse;">
                            </div>
                            <div
                                style="position: absolute; bottom: 30px; left: 50px; width: 30px; height: 30px; background: rgba(255,255,255,0.06); border-radius: 50%; animation: float 5s ease-in-out infinite;">
                            </div>

                            <!-- Content -->
                            <div
                                style="position: relative; z-index: 2; text-align: center; padding: 40px 30px; width: 100%;">
                                <div style="margin-bottom: 20px;">
                                    <div
                                        style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.3);">
                                        <i class="fas fa-user-plus" style="font-size: 40px; color: white;"></i>
                                    </div>
                                </div>
                                <h4 class="modal-title" id="modalRegisterLabel"
                                    style="color: white; font-weight: 700; margin: 0; font-size: 32px; text-shadow: 0 2px 8px rgba(0,0,0,0.3); letter-spacing: -0.5px;">
                                    Create Account
                                </h4>
                                <p
                                    style="color: rgba(255,255,255,0.9); margin: 12px 0 0 0; font-size: 18px; font-weight: 400; text-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                                    Join us and start your journey today</p>
                            </div>

                            <!-- Close Button -->
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"
                                style="position: absolute; top: 25px; right: 25px; z-index: 3; background: rgba(255,255,255,0.2); border-radius: 50%; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border: none; opacity: 0.9; transition: all 0.3s ease; backdrop-filter: blur(10px);"
                                onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.2)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='scale(1)'; this.style.boxShadow='none'"></button>
                        </div>
                        <div class="modal-body" style="padding: 40px 50px; background: #fafbfc;">
                            <form id="registerForm" method="post" enctype="multipart/form-data">
                                <!-- Full Name Field -->
                                <div class="form-group mb-4">
                                    <label for="fullName" class="form-label"
                                        style="display: block; margin-bottom: 12px; font-weight: 600; color: #2c3e50; font-size: 15px;">Full
                                        Name</label>
                                    <div class="input-group" style="position: relative;">
                                        <span class="input-icon"
                                            style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 3;">
                                            <i class="fas fa-user" style="font-size: 16px;"></i>
                                        </span>
                                        <input type="text" class="form-control" id="fullName" name="fullName"
                                            placeholder="Enter your full name" required
                                            style="padding-left: 50px; border: 2px solid #e9ecef; border-radius: 15px; height: 55px; font-size: 16px; transition: all 0.3s ease; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05);"
                                            onfocus="this.style.borderColor='#28a745'; this.style.boxShadow='0 0 0 0.2rem rgba(40, 167, 69, 0.25)'; this.style.transform='translateY(-2px)'"
                                            onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.05)'; this.style.transform='translateY(0)'">
                                    </div>
                                </div>

                                <!-- Email Field -->
                                <div class="form-group mb-4">
                                    <label for="email" class="form-label"
                                        style="display: block; margin-bottom: 12px; font-weight: 600; color: #2c3e50; font-size: 15px;">Email
                                        Address</label>
                                    <div class="input-group" style="position: relative;">
                                        <span class="input-icon"
                                            style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 3;">
                                            <i class="fas fa-envelope" style="font-size: 16px;"></i>
                                        </span>
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="Enter your email address" required
                                            style="padding-left: 50px; border: 2px solid #e9ecef; border-radius: 15px; height: 55px; font-size: 16px; transition: all 0.3s ease; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05);"
                                            onfocus="this.style.borderColor='#28a745'; this.style.boxShadow='0 0 0 0.2rem rgba(40, 167, 69, 0.25)'; this.style.transform='translateY(-2px)'"
                                            onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.05)'; this.style.transform='translateY(0)'">
                                    </div>
                                </div>

                                <!-- Password Field -->
                                <div class="form-group mb-4">
                                    <label for="passwordRegister" class="form-label"
                                        style="display: block; margin-bottom: 12px; font-weight: 600; color: #2c3e50; font-size: 15px;">Password</label>
                                    <div class="input-group" style="position: relative;">
                                        <span class="input-icon"
                                            style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 3;">
                                            <i class="fas fa-lock" style="font-size: 16px;"></i>
                                        </span>
                                        <input type="password" class="form-control" id="passwordRegister"
                                            name="password" placeholder="Enter your password" required
                                            style="padding-left: 50px; padding-right: 50px; border: 2px solid #e9ecef; border-radius: 15px; height: 55px; font-size: 16px; transition: all 0.3s ease; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05);"
                                            onfocus="this.style.borderColor='#28a745'; this.style.boxShadow='0 0 0 0.2rem rgba(40, 167, 69, 0.25)'; this.style.transform='translateY(-2px)'"
                                            onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.05)'; this.style.transform='translateY(0)'"
                                            oninput="checkPasswordStrength(this.value)"
                                            onfocus="this.style.borderColor='#28a745'; this.style.boxShadow='0 0 0 0.2rem rgba(40, 167, 69, 0.25)'; this.style.transform='translateY(-2px)'"
                                            onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.05)'; this.style.transform='translateY(0)'">
                                        <div class="position-absolute top-50 end-0 translate-middle-y pe-3"
                                            style="cursor: pointer; z-index: 3; color: #6c757d; transition: color 0.3s ease;"
                                            onclick="togglePassword('passwordRegister')"
                                            onmouseover="this.style.color='#28a745'"
                                            onmouseout="this.style.color='#6c757d'">
                                            <i class="fas fa-eye" id="passwordRegisterToggleIcon"></i>
                                        </div>
                                    </div>

                                    <!-- Password Strength Indicator -->
                                    <div class="password-strength" id="passwordStrengthRegister" style="display: none;">
                                        <div class="password-strength-bar" id="passwordStrengthBarRegister"></div>
                                    </div>
                                    <div class="password-strength-text" id="passwordStrengthTextRegister"
                                        style="display: none;"></div>

                                    <!-- Password Requirements -->
                                    <div class="password-requirements" id="passwordRequirementsRegister"
                                        style="display: none;">
                                        <div class="requirement" id="reqLengthRegister">
                                            <span class="requirement-icon">•</span>
                                            <span>At least 8 characters</span>
                                        </div>
                                        <div class="requirement" id="reqUppercaseRegister">
                                            <span class="requirement-icon">•</span>
                                            <span>One uppercase letter</span>
                                        </div>
                                        <div class="requirement" id="reqLowercaseRegister">
                                            <span class="requirement-icon">•</span>
                                            <span>One lowercase letter</span>
                                        </div>
                                        <div class="requirement" id="reqNumberRegister">
                                            <span class="requirement-icon">•</span>
                                            <span>One number</span>
                                        </div>
                                        <div class="requirement" id="reqSpecialRegister">
                                            <span class="requirement-icon">•</span>
                                            <span>One special character</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Phone Number Field -->
                                <div class="form-group mb-4">
                                    <label for="phoneNumber" class="form-label"
                                        style="display: block; margin-bottom: 12px; font-weight: 600; color: #2c3e50; font-size: 15px;">Phone
                                        Number</label>
                                    <div class="input-group" style="position: relative;">
                                        <span class="input-icon"
                                            style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 3;">
                                            <i class="fas fa-phone" style="font-size: 16px;"></i>
                                        </span>
                                        <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber"
                                            placeholder="Enter phone number" required
                                            style="padding-left: 50px; border: 2px solid #e9ecef; border-radius: 15px; height: 55px; font-size: 16px; transition: all 0.3s ease; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05);"
                                            onfocus="this.style.borderColor='#28a745'; this.style.boxShadow='0 0 0 0.2rem rgba(40, 167, 69, 0.25)'; this.style.transform='translateY(-2px)'"
                                            onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.05)'; this.style.transform='translateY(0)'">
                                    </div>
                                </div>

                                <!-- Hidden fields for backend compatibility -->
                                <input type="hidden" name="countryCode" value="+91">
                                <input type="hidden" name="state" value="">
                                <input type="hidden" name="city" value="">
                                <input type="hidden" name="pinCode" value="">
                                <input type="hidden" name="profilePic" value="">
                                <input type="hidden" name="referralCode" value="">

                                <!-- Terms & Conditions Checkbox -->
                                <div class="form-group mb-4" style="margin-bottom: 20px;">
                                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                                        <input type="checkbox" id="agreeTerms" name="agreeTerms" required
                                            style="margin-top: 4px; width: 18px; height: 18px; cursor: pointer; accent-color: #28a745;">
                                        <label for="agreeTerms"
                                            style="color: #6c757d; font-size: 14px; line-height: 1.5; cursor: pointer; margin: 0; flex: 1;">
                                            I agree to the <a href="<?php echo base_url('legal/terms'); ?>"
                                                target="_blank"
                                                style="color: #28a745; text-decoration: underline; font-weight: 600;">Terms
                                                & Conditions</a>
                                        </label>
                                    </div>
                                </div>

                                <!-- Sign Up Button -->
                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-primary btn-register"
                                        style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; border-radius: 15px; height: 55px; font-size: 16px; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3); text-transform: uppercase; letter-spacing: 0.5px;"
                                        onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(40, 167, 69, 0.4)'"
                                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 20px rgba(40, 167, 69, 0.3)'">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </button>
                                </div>

                                <!-- Sign In Link -->
                                <div class="text-center mb-4">
                                    <span style="color: #6c757d; font-size: 15px;">Already have an account? </span>
                                    <a href="#" class="text-decoration-none" data-bs-dismiss="modal"
                                        data-bs-toggle="modal" data-bs-target="#modalLogin"
                                        style="color: #28a745; font-weight: 600; font-size: 15px; transition: color 0.3s ease;"
                                        onmouseover="this.style.color='#20c997'"
                                        onmouseout="this.style.color='#28a745'">Sign In</a>
                                </div>

                                <!-- Divider -->
                                <div class="divider" style="position: relative; text-align: center; margin: 30px 0;">
                                    <div
                                        style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: linear-gradient(to right, transparent, #e9ecef, transparent);">
                                    </div>
                                    <span
                                        style="background: #fafbfc; padding: 0 20px; color: #6c757d; font-size: 14px; font-weight: 500;">OR</span>
                                </div>

                                <!-- OTP and Google Login Buttons -->
                                <div class="row g-3">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-outline-secondary btn-otp w-100"
                                            data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalOTP"
                                            style="border: 2px solid #e9ecef; border-radius: 15px; height: 55px; font-weight: 600; color: #2c3e50; transition: all 0.3s ease; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05);"
                                            onmouseover="this.style.borderColor='#28a745'; this.style.color='#28a745'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(40, 167, 69, 0.2)'; this.style.background='#f8fff9'"
                                            onmouseout="this.style.borderColor='#e9ecef'; this.style.color='#2c3e50'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.05)'; this.style.background='white'">
                                            <i class="fas fa-mobile-alt me-2" style="color: #28a745;"></i>OTP
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" class="btn btn-outline-secondary btn-social w-100"
                                            style="border: 2px solid #e9ecef; border-radius: 15px; height: 55px; font-weight: 600; color: #2c3e50; transition: all 0.3s ease; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05);"
                                            onmouseover="this.style.borderColor='#db4437'; this.style.color='#db4437'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(219, 68, 55, 0.2)'; this.style.background='#fff5f5'"
                                            onmouseout="this.style.borderColor='#e9ecef'; this.style.color='#2c3e50'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.05)'; this.style.background='white'">
                                            <i class="fab fa-google me-2" style="color: #db4437;"></i>Google
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OTP Login Modal -->
            <div class="modal fade" id="modalOTP" tabindex="-1" aria-labelledby="modalOTPLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content"
                        style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
                        <div class="modal-body p-5">
                            <div class="text-center mb-4">
                                <h4 class="modal-title" id="modalOTPLabel"
                                    style="color: #2c3e50; font-weight: 700; margin-bottom: 0;">OTP Login</h4>
                                <p style="color: #6c757d; margin-top: 8px; font-size: 14px;">Enter your phone number to
                                    receive OTP</p>
                            </div>

                            <form id="otpForm">
                                <!-- Phone Number Input with Country Code -->
                                <div class="mb-4">
                                    <div class="input-group" style="position: relative;">
                                        <span class="input-group-text"
                                            style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 3; background: none; border: none; color: #6c757d;">
                                            <i class="fas fa-mobile-alt"></i>
                                        </span>
                                        <select class="form-select" id="otpCountryCode"
                                            style="width: 100px; border: 2px solid #e9ecef; border-radius: 12px 0 0 12px; height: 50px; font-size: 16px; padding-left: 45px; background: white;">
                                            <option value="+91" selected>🇮🇳 +91</option>
                                            <option value="+1">🇺🇸 +1</option>
                                            <option value="+44">🇬🇧 +44</option>
                                            <option value="+61">🇦🇺 +61</option>
                                            <option value="+971">🇦🇪 +971</option>
                                            <option value="+966">🇸🇦 +966</option>
                                            <option value="+65">🇸🇬 +65</option>
                                            <option value="+60">🇲🇾 +60</option>
                                        </select>
                                        <input type="tel" class="form-control" id="otpPhone"
                                            placeholder="Enter your phone number" required
                                            style="border: 2px solid #e9ecef; border-left: none; border-radius: 0 12px 12px 0; height: 50px; font-size: 16px; transition: all 0.3s ease;">
                                    </div>
                                </div>

                                <!-- OTP Input (Initially Hidden) -->
                                <div class="mb-4" id="otpInputGroup" style="display: none;">
                                    <div class="input-group" style="position: relative;">
                                        <span class="input-group-text"
                                            style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 3; background: none; border: none; color: #6c757d;">
                                            <i class="fas fa-key"></i>
                                        </span>
                                        <input type="text" class="form-control" id="otpCode" placeholder="Enter OTP"
                                            style="padding-left: 45px; border: 2px solid #e9ecef; border-radius: 12px; height: 50px; font-size: 16px; transition: all 0.3s ease;">
                                    </div>
                                </div>

                                <!-- Send OTP / Verify Button -->
                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-primary" id="otpSubmitBtn"
                                        style="background: #3498db; border: none; border-radius: 12px; height: 50px; font-size: 16px; font-weight: 600; transition: all 0.3s ease;">
                                        Send OTP
                                    </button>
                                </div>

                                <!-- Resend OTP Link -->
                                <div class="text-center mb-4" id="resendOtpGroup" style="display: none;">
                                    <a href="#" class="text-decoration-none" id="resendOtp"
                                        style="color: #3498db; font-weight: 600;">Resend OTP</a>
                                </div>

                                <!-- Back to Login Link -->
                                <div class="text-center mb-4">
                                    <span style="color: #6c757d;">Back to </span>
                                    <a href="#" class="text-decoration-none" data-bs-dismiss="modal"
                                        data-bs-toggle="modal" data-bs-target="#modalLogin"
                                        style="color: #3498db; font-weight: 600;">Password Login</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Login Info Modal -->
            <div class="modal fade" id="socialLoginInfoModal" tabindex="-1" aria-labelledby="socialLoginInfoModalLabel"
                aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content"
                        style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
                        <div class="modal-body p-5">
                            <div class="text-center mb-4">
                                <h4 class="modal-title" id="socialLoginInfoModalLabel"
                                    style="color: #2c3e50; font-weight: 700; margin-bottom: 0;">Complete Your Profile
                                </h4>
                                <p style="color: #6c757d; margin-top: 8px; font-size: 14px;">Please provide your details
                                    to continue</p>
                            </div>

                            <form id="socialLoginInfoForm">
                                <!-- Full Name Input -->
                                <div class="mb-4">
                                    <div class="input-group" style="position: relative;">
                                        <span class="input-group-text"
                                            style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 3; background: none; border: none; color: #6c757d;">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control" id="socialFullName"
                                            placeholder="Enter your full name" required
                                            style="padding-left: 45px; border: 2px solid #e9ecef; border-radius: 12px; height: 50px; font-size: 16px; transition: all 0.3s ease;">
                                    </div>
                                </div>

                                <!-- Phone Number Input with Country Code -->
                                <div class="mb-4">
                                    <div class="input-group"
                                        style="position: relative; display: flex; align-items: stretch; border: 2px solid #e9ecef; border-radius: 12px; overflow: hidden; background: #f8f9fa;">
                                        <span class="input-group-text"
                                            style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 3; background: none; border: none; color: #6c757d; pointer-events: none;">
                                            <i class="fas fa-mobile-alt"></i>
                                        </span>
                                        <select class="form-select" id="socialCountryCode"
                                            style="width: 130px; border: none; border-right: 1px solid #e9ecef; border-radius: 0; height: 50px; font-size: 16px; padding-left: 45px; padding-right: 12px; background: white; appearance: none; -webkit-appearance: none; -moz-appearance: none; cursor: pointer; font-weight: 500; color: #2c3e50; outline: none;">
                                            <option value="+91" selected>🇮🇳 +91</option>
                                            <option value="+1">🇺🇸 +1</option>
                                            <option value="+44">🇬🇧 +44</option>
                                            <option value="+61">🇦🇺 +61</option>
                                            <option value="+971">🇦🇪 +971</option>
                                            <option value="+966">🇸🇦 +966</option>
                                            <option value="+65">🇸🇬 +65</option>
                                            <option value="+60">🇲🇾 +60</option>
                                        </select>
                                        <input type="tel" class="form-control" id="socialPhoneNumber"
                                            placeholder="Enter your phone number" required
                                            style="border: none; border-radius: 0; height: 50px; font-size: 16px; transition: all 0.3s ease; flex: 1; background: white; outline: none;">
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-primary" id="socialLoginSubmitBtn"
                                        style="background: #3498db; border: none; border-radius: 12px; height: 50px; font-size: 16px; font-weight: 600; transition: all 0.3s ease;">
                                        Continue
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Modal -->
            <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel"
                aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content"
                        style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1); background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="modal-body text-center p-5" style="color: white;">
                            <div class="welcome-animation mb-4">
                                <div class="welcome-icon" style="font-size: 4rem; margin-bottom: 1rem;">
                                    <i class="fas fa-check-circle"
                                        style="color: #4CAF50; animation: bounceIn 0.8s ease-in-out;"></i>
                                </div>
                                <h3 class="welcome-title"
                                    style="font-weight: 700; margin-bottom: 0.5rem; animation: slideInUp 0.6s ease-in-out 0.2s both;">
                                    Welcome Back!
                                </h3>
                                <p class="welcome-subtitle"
                                    style="font-size: 1.1rem; opacity: 0.9; animation: slideInUp 0.6s ease-in-out 0.4s both;">
                                    Hello, <span id="welcomeUserName"
                                        style="font-weight: 600; color: #FFD700;">User</span>
                                </p>
                                <div class="welcome-message"
                                    style="margin-top: 1.5rem; animation: slideInUp 0.6s ease-in-out 0.6s both;">
                                    <p style="margin-bottom: 0; font-size: 0.95rem; opacity: 0.8;">
                                        You have successfully logged in to your account.
                                    </p>
                                </div>
                            </div>

                            <!-- Loading Bar -->
                            <div class="welcome-progress"
                                style="margin-top: 2rem; animation: slideInUp 0.6s ease-in-out 0.8s both;">
                                <div class="progress"
                                    style="height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px;">
                                    <div class="progress-bar" id="welcomeProgressBar"
                                        style="background: linear-gradient(90deg, #4CAF50, #8BC34A); border-radius: 2px; transition: width 0.1s linear;">
                                    </div>
                                </div>
                                <p class="mt-2" style="font-size: 0.85rem; opacity: 0.7; margin-bottom: 0;">
                                    Redirecting...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CSS Animations and Styles -->
            <style>
                @keyframes float {

                    0%,
                    100% {
                        transform: translateY(0px);
                    }

                    50% {
                        transform: translateY(-20px);
                    }
                }

                @keyframes bounceIn {
                    0% {
                        transform: scale(0.3);
                        opacity: 0;
                    }

                    50% {
                        transform: scale(1.05);
                    }

                    70% {
                        transform: scale(0.9);
                    }

                    100% {
                        transform: scale(1);
                        opacity: 1;
                    }
                }

                @keyframes slideInUp {
                    0% {
                        transform: translateY(30px);
                        opacity: 0;
                    }

                    100% {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }

                .password-strength {
                    height: 4px;
                    background: #e9ecef;
                    border-radius: 2px;
                    margin-top: 8px;
                    overflow: hidden;
                }

                .password-strength-bar {
                    height: 100%;
                    width: 0%;
                    transition: width 0.3s ease;
                    border-radius: 2px;
                }

                .password-strength-text {
                    font-size: 12px;
                    margin-top: 4px;
                    font-weight: 600;
                }

                .password-requirements {
                    margin-top: 8px;
                    padding: 12px;
                    background: #f8f9fa;
                    border-radius: 8px;
                    border: 1px solid #e9ecef;
                }

                .requirement {
                    display: flex;
                    align-items: center;
                    margin-bottom: 4px;
                    font-size: 13px;
                    color: #6c757d;
                }

                .requirement:last-child {
                    margin-bottom: 0;
                }

                .requirement-icon {
                    margin-right: 8px;
                    font-weight: bold;
                }

                .requirement.valid {
                    color: #28a745;
                }

                .requirement.valid .requirement-icon {
                    color: #28a745;
                }
            </style>

            <!-- JavaScript Functions -->
            <script>
                // Password toggle function
                function togglePassword(fieldId) {
                    const field = document.getElementById(fieldId);
                    const icon = document.getElementById(fieldId + 'ToggleIcon');

                    if (field.type === 'password') {
                        field.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        field.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }

                // Password strength checker
                function checkPasswordStrength(password) {
                    const strengthBar = document.getElementById('passwordStrengthRegister');
                    const strengthText = document.getElementById('passwordStrengthTextRegister');
                    const requirements = document.getElementById('passwordRequirementsRegister');

                    if (password.length === 0) {
                        strengthBar.style.display = 'none';
                        strengthText.style.display = 'none';
                        requirements.style.display = 'none';
                        return;
                    }

                    strengthBar.style.display = 'block';
                    strengthText.style.display = 'block';
                    requirements.style.display = 'block';

                    let score = 0;
                    let strength = '';
                    let color = '';

                    // Check length
                    if (password.length >= 8) {
                        score += 20;
                        document.getElementById('reqLengthRegister').classList.add('valid');
                    } else {
                        document.getElementById('reqLengthRegister').classList.remove('valid');
                    }

                    // Check uppercase
                    if (/[A-Z]/.test(password)) {
                        score += 20;
                        document.getElementById('reqUppercaseRegister').classList.add('valid');
                    } else {
                        document.getElementById('reqUppercaseRegister').classList.remove('valid');
                    }

                    // Check lowercase
                    if (/[a-z]/.test(password)) {
                        score += 20;
                        document.getElementById('reqLowercaseRegister').classList.add('valid');
                    } else {
                        document.getElementById('reqLowercaseRegister').classList.remove('valid');
                    }

                    // Check number
                    if (/\d/.test(password)) {
                        score += 20;
                        document.getElementById('reqNumberRegister').classList.add('valid');
                    } else {
                        document.getElementById('reqNumberRegister').classList.remove('valid');
                    }

                    // Check special character
                    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                        score += 20;
                        document.getElementById('reqSpecialRegister').classList.add('valid');
                    } else {
                        document.getElementById('reqSpecialRegister').classList.remove('valid');
                    }

                    // Set strength level
                    if (score < 40) {
                        strength = 'Weak';
                        color = '#dc3545';
                    } else if (score < 80) {
                        strength = 'Medium';
                        color = '#ffc107';
                    } else {
                        strength = 'Strong';
                        color = '#28a745';
                    }

                    // Update UI
                    document.getElementById('passwordStrengthBarRegister').style.width = score + '%';
                    document.getElementById('passwordStrengthBarRegister').style.background = color;
                    strengthText.textContent = 'Password Strength: ' + strength;
                    strengthText.style.color = color;
                }

                // Form validation and submission
                document.addEventListener('DOMContentLoaded', function () {
                    const registerForm = document.getElementById('registerForm');

                    if (registerForm) {
                        registerForm.addEventListener('submit', function (e) {
                            e.preventDefault();

                            // Basic validation
                            const fullName = document.getElementById('fullName').value.trim();
                            const email = document.getElementById('email').value.trim();
                            const password = document.getElementById('passwordRegister').value;
                            const phoneNumber = document.getElementById('phoneNumber').value.trim();

                            if (!fullName || !email || !password || !phoneNumber) {
                                alert('Please fill in all required fields.');
                                return;
                            }

                            // Email validation
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(email)) {
                                alert('Please enter a valid email address.');
                                return;
                            }

                            // Password validation
                            if (password.length < 8) {
                                alert('Password must be at least 8 characters long.');
                                return;
                            }

                            // Phone validation
                            const phoneRegex = /^[\d\s\-\+\(\)]+$/;
                            if (!phoneRegex.test(phoneNumber)) {
                                alert('Please enter a valid phone number.');
                                return;
                            }

                            // Show loading state
                            const submitBtn = registerForm.querySelector('.btn-register');
                            const originalText = submitBtn.innerHTML;
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
                            submitBtn.disabled = true;

                            // Simulate form submission (replace with actual AJAX call)
                            setTimeout(() => {
                                alert('Account created successfully! Please check your email for verification.');
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;

                                // Close modal
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalRegister'));
                                modal.hide();

                                // Reset form
                                registerForm.reset();
                            }, 2000);
                        });
                    }
                });
            </script>

            <!-- Modal Login Form JavaScript -->
            <script>
                // Google Login handler for modal
                function handleGoogleLoginModal() {
                    if (typeof window.startGoogleOAuthSignIn === 'function') {
                        window.startGoogleOAuthSignIn();
                    } else {
                        alert('Google login is loading. Please try again.');
                    }
                }

                // Helper function to handle Google sign-in success
                function handleGoogleSignInSuccess(idToken, user) {
                    console.log('Google Sign-In Success:', user);

                    // Store Google user data
                    window.googleUserData = {
                        idToken: idToken,
                        email: user.email,
                        displayName: user.displayName || user.email,
                        photoURL: user.photoURL || ''
                    };

                    // Close login modal if open
                    const loginModal = bootstrap.Modal.getInstance(document.getElementById('modalLogin'));
                    if (loginModal) {
                        loginModal.hide();
                    }

                    // Check if user has phone number
                    checkSocialUserPhone(user.email, idToken, user);
                }

                // Check if user has phone number
                function checkSocialUserPhone(email, idToken, user) {
                    const formData = new FormData();
                    formData.append('email', email);

                    fetch('<?php echo base_url('auth/check-social-user-phone'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (data.data && data.data.hasAllFields) {
                                    // User has all required fields (phone, name) - login directly without asking anything, just update lastLoginAt
                                    loginSocialUserDirectly(idToken, user, data.data.phoneNumber, data.data.documentId);
                                } else if (data.data && data.data.missingFields && data.data.missingFields.length > 0) {
                                    // User exists but missing some fields - show profile modal with existing data pre-filled
                                    showSocialLoginProfileModal(data.data);
                                } else {
                                    // User doesn't exist or error - show profile modal
                                    showSocialLoginProfileModal();
                                }
                            } else {
                                // Error checking - show profile modal as fallback
                                showSocialLoginProfileModal();
                            }
                        })
                        .catch(error => {
                            console.error('Error checking user phone:', error);
                            // Error - show profile modal as fallback
                            showSocialLoginProfileModal();
                        });
                }

                // Show social login profile modal
                function showSocialLoginProfileModal(existingData = null) {
                    const socialLoginModal = document.getElementById('socialLoginInfoModal');
                    if (socialLoginModal) {
                        // Store existing data globally for form submission
                        window.existingUserData = existingData;

                        // Reset welcome modal flag when opening social login modal
                        window.socialLoginWelcomeShown = false;

                        const bsSocialModal = new bootstrap.Modal(socialLoginModal);
                        bsSocialModal.show();

                        // Get missing fields
                        const missingFields = existingData && existingData.missingFields ? existingData.missingFields : [];
                        const needsFullName = missingFields.includes('fullName');
                        const needsPhone = missingFields.includes('phoneNumber');

                        // Pre-fill full name from existing data or Google
                        const fullNameInput = document.getElementById('socialFullName');
                        const fullNameGroup = fullNameInput ? fullNameInput.closest('.mb-3') : null;

                        if (fullNameInput) {
                            // Clear previous value first
                            fullNameInput.value = '';

                            if (existingData && existingData.fullName) {
                                fullNameInput.value = existingData.fullName;
                            } else if (window.googleUserData && window.googleUserData.displayName) {
                                fullNameInput.value = window.googleUserData.displayName;
                            }

                            // Hide full name field if it's already filled and not needed
                            if (fullNameGroup) {
                                if (!needsFullName && fullNameInput.value) {
                                    fullNameGroup.style.display = 'none';
                                } else {
                                    fullNameGroup.style.display = 'block';
                                    fullNameInput.required = needsFullName;
                                }
                            }
                        }

                        // Pre-fill phone number if exists
                        const phoneInput = document.getElementById('socialPhoneNumber');
                        const phoneGroup = phoneInput ? phoneInput.closest('.mb-3') : null;
                        const countryCodeSelect = document.getElementById('socialCountryCode');

                        if (phoneInput) {
                            // Clear previous value first
                            phoneInput.value = '';

                            if (existingData && existingData.phoneNumber) {
                                // Extract country code and phone number
                                const phone = existingData.phoneNumber;
                                const countryCodeMatch = phone.match(/^(\+\d{1,3})/);
                                if (countryCodeMatch) {
                                    const countryCode = countryCodeMatch[1];
                                    const phoneNumber = phone.substring(countryCode.length);
                                    if (countryCodeSelect) {
                                        countryCodeSelect.value = countryCode;
                                    }
                                    phoneInput.value = phoneNumber;
                                } else {
                                    // If no country code, assume default +91
                                    if (countryCodeSelect) {
                                        countryCodeSelect.value = '+91';
                                    }
                                    phoneInput.value = phone.replace(/^\+91/, '').replace(/^\+/, '');
                                }
                            }

                            // Hide phone field if it's already filled and not needed
                            if (phoneGroup) {
                                if (!needsPhone && phoneInput.value) {
                                    phoneGroup.style.display = 'none';
                                } else {
                                    phoneGroup.style.display = 'block';
                                    phoneInput.required = needsPhone;
                                }
                            }
                        }

                        // Focus on first empty required field
                        setTimeout(() => {
                            if (needsFullName && fullNameInput && !fullNameInput.value) {
                                fullNameInput.focus();
                                fullNameInput.select();
                            } else if (needsPhone && phoneInput && !phoneInput.value) {
                                phoneInput.focus();
                            }
                        }, 300);
                    } else {
                        console.error('Social login modal not found');
                        alert('Error: Social login modal not available. Please refresh the page.');
                    }
                }

                // Login user directly if they have phone number
                function loginSocialUserDirectly(idToken, user, phoneNumber, documentId) {
                    const formData = new FormData();
                    formData.append('idToken', idToken);
                    formData.append('email', user.email);
                    formData.append('displayName', user.displayName || user.email);
                    formData.append('phoneNumber', phoneNumber);
                    formData.append('skipProfile', 'true'); // Flag to skip profile requirement

                    fetch('<?php echo base_url('auth/social-login-direct'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Store user data
                                if (data.data && data.data.documentId) {
                                    window.userDocumentId = data.data.documentId;
                                }

                                localStorage.setItem('userData', JSON.stringify(data.data));

                                // Update header
                                if (typeof updateHeaderForLogin === 'function') {
                                    updateHeaderForLogin(data.data);
                                }

                                // Check for pending enquiry first
                                if (window.pendingEnquiry && typeof showEnquiryMessageModal === 'function') {
                                    const propertyData = window.pendingEnquiry;
                                    window.pendingEnquiry = null;
                                    setTimeout(() => {
                                        showEnquiryMessageModal(propertyData);
                                    }, 500);
                                } else {
                                    // Show welcome modal only if no pending enquiry
                                    if (typeof showWelcomeModal === 'function') {
                                        showWelcomeModal(data.data.displayName || data.data.fullName || data.data.email || 'User');
                                    } else {
                                        // Reload page if welcome modal not available
                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 500);
                                    }
                                }
                            } else {
                                // If direct login fails, show profile modal
                                showSocialLoginProfileModal();
                            }
                        })
                        .catch(error => {
                            console.error('Error in direct login:', error);
                            // If error, show profile modal
                            showSocialLoginProfileModal();
                        });
                }

                // Handle social login info form submission
                document.addEventListener('DOMContentLoaded', function () {
                    const socialLoginInfoForm = document.getElementById('socialLoginInfoForm');
                    if (socialLoginInfoForm && !socialLoginInfoForm.hasAttribute('data-handler-attached')) {
                        socialLoginInfoForm.setAttribute('data-handler-attached', 'true');
                        socialLoginInfoForm.addEventListener('submit', function (e) {
                            e.preventDefault();

                            // Prevent duplicate submission
                            if (this.hasAttribute('data-submitting')) {
                                return;
                            }
                            this.setAttribute('data-submitting', 'true');

                            // Get input elements
                            const fullNameInput = document.getElementById('socialFullName');
                            const phoneInput = document.getElementById('socialPhoneNumber');
                            const countryCodeSelect = document.getElementById('socialCountryCode');
                            const fullNameGroup = fullNameInput ? fullNameInput.closest('.mb-3') : null;
                            const phoneGroup = phoneInput ? phoneInput.closest('.mb-3') : null;

                            // Check if fields are visible (required)
                            const isFullNameVisible = fullNameGroup && fullNameGroup.style.display !== 'none';
                            const isPhoneVisible = phoneGroup && phoneGroup.style.display !== 'none';

                            // Get values from input fields (always read from inputs, even if hidden)
                            let fullName = fullNameInput ? fullNameInput.value.trim() : '';
                            let phoneNumber = phoneInput ? phoneInput.value.trim() : '';
                            let fullPhoneNumber = '';

                            // If full name is visible and empty, validate it
                            if (isFullNameVisible && !fullName) {
                                alert('Please enter your full name');
                                this.removeAttribute('data-submitting');
                                return;
                            }

                            // If full name is not visible but empty, try to get from existing data
                            if (!isFullNameVisible && !fullName && window.existingUserData && window.existingUserData.fullName) {
                                fullName = window.existingUserData.fullName;
                            }

                            // If phone is visible, validate and format it
                            if (isPhoneVisible) {
                                if (!phoneNumber) {
                                    alert('Please enter your phone number');
                                    this.removeAttribute('data-submitting');
                                    return;
                                }

                                // Validate phone number
                                const phoneRegex = /^\d{6,15}$/;
                                if (!phoneRegex.test(phoneNumber)) {
                                    alert('Please enter a valid phone number (6-15 digits)');
                                    this.removeAttribute('data-submitting');
                                    return;
                                }

                                const countryCode = countryCodeSelect ? countryCodeSelect.value : '+91';
                                fullPhoneNumber = countryCode + phoneNumber;
                            } else if (phoneNumber) {
                                // Phone field is hidden but has value - use it with default country code
                                const countryCode = countryCodeSelect ? countryCodeSelect.value : '+91';
                                fullPhoneNumber = countryCode + phoneNumber;
                            } else if (window.existingUserData && window.existingUserData.phoneNumber) {
                                // Phone field is hidden and empty - use existing data
                                fullPhoneNumber = window.existingUserData.phoneNumber;
                            }

                            // Debug: Log what we're sending
                            console.log('Form submission values:', {
                                isFullNameVisible: isFullNameVisible,
                                isPhoneVisible: isPhoneVisible,
                                fullName: fullName || '(empty)',
                                phoneNumber: phoneNumber || '(empty)',
                                fullPhoneNumber: fullPhoneNumber || '(empty)',
                                existingUserData: window.existingUserData
                            });

                            const submitBtn = document.getElementById('socialLoginSubmitBtn');
                            const originalText = submitBtn.innerHTML;
                            submitBtn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid white; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span> Processing...';
                            submitBtn.disabled = true;

                            // Prepare form data
                            const formData = new FormData();
                            formData.append('idToken', window.googleUserData.idToken);
                            formData.append('email', window.googleUserData.email);

                            // Always send displayName (even if empty, so backend knows it was checked)
                            formData.append('displayName', fullName || '');

                            // Always send phoneNumber (even if empty, so backend knows it was checked)
                            formData.append('phoneNumber', fullPhoneNumber || '');

                            // Debug logging
                            console.log('Social Login Form Data:', {
                                email: window.googleUserData.email,
                                displayName: fullName || '(empty)',
                                phoneNumber: fullPhoneNumber || '(empty)'
                            });

                            // Submit to social login endpoint
                            fetch('<?php echo base_url('auth/social-login'); ?>', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Store user data
                                        if (data.data && data.data.documentId) {
                                            window.userDocumentId = data.data.documentId;
                                        }

                                        localStorage.setItem('userData', JSON.stringify(data.data));

                                        // Update header
                                        if (typeof updateHeaderForLogin === 'function') {
                                            updateHeaderForLogin(data.data);
                                        }

                                        // Close social login modal
                                        const socialModal = bootstrap.Modal.getInstance(document.getElementById('socialLoginInfoModal'));
                                        if (socialModal) {
                                            socialModal.hide();
                                        }

                                        // Check for pending enquiry first
                                        if (window.pendingEnquiry && typeof showEnquiryMessageModal === 'function') {
                                            const propertyData = window.pendingEnquiry;
                                            window.pendingEnquiry = null;
                                            setTimeout(() => {
                                                showEnquiryMessageModal(propertyData);
                                            }, 500);
                                        } else {
                                            // Show welcome modal only if no pending enquiry (only once)
                                            if (!window.socialLoginWelcomeShown) {
                                                window.socialLoginWelcomeShown = true;
                                                if (typeof showWelcomeModal === 'function') {
                                                    showWelcomeModal(data.data.displayName || data.data.fullName || data.data.email || 'User');
                                                } else {
                                                    // Reload page if welcome modal not available
                                                    setTimeout(() => {
                                                        window.location.reload();
                                                    }, 500);
                                                }
                                            }
                                        }
                                    } else {
                                        alert(data.message || 'Login failed. Please try again.');
                                        submitBtn.innerHTML = originalText;
                                        submitBtn.disabled = false;
                                        socialLoginInfoForm.removeAttribute('data-submitting');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('An error occurred during login. Please try again.');
                                    submitBtn.innerHTML = originalText;
                                    submitBtn.disabled = false;
                                    socialLoginInfoForm.removeAttribute('data-submitting');
                                });
                        });
                    }
                });

                // Reset login modal when opened
                const modalLogin = document.getElementById('modalLogin');
                if (modalLogin) {
                    modalLogin.addEventListener('show.bs.modal', function () {
                        // Reset social login flag
                        window.isSocialLogin = false;

                        // Reset phone form group
                        const phoneFormGroup = document.getElementById('phoneFormGroup');
                        if (phoneFormGroup) {
                            phoneFormGroup.style.display = 'block';
                        }

                        // Show Continue button
                        const continueBtn = document.querySelector('.btn-continue-modal');
                        if (continueBtn) {
                            continueBtn.style.display = 'block';
                        }

                        // Hide OTP section
                        const otpSection = document.getElementById('otpInputSection');
                        if (otpSection) {
                            otpSection.style.display = 'none';
                        }

                        // Reset phone input
                        const phoneInput = document.getElementById('phoneNumberModal');
                        if (phoneInput) {
                            phoneInput.value = '';
                        }
                    });
                }

                // Modal login form submission
                document.addEventListener('DOMContentLoaded', function () {
                    const loginFormModal = document.getElementById('loginFormModal');

                    if (loginFormModal) {
                        loginFormModal.addEventListener('submit', function (e) {
                            e.preventDefault();

                            const countryCode = document.getElementById('countryCodeModal').value;
                            const phoneNumber = document.getElementById('phoneNumberModal').value.trim();

                            if (!phoneNumber) {
                                showLoginModalError('Please enter your phone number');
                                return;
                            }

                            // Validate phone number (at least 6 digits, max 15 digits)
                            const phoneRegex = /^\d{6,15}$/;
                            if (!phoneRegex.test(phoneNumber)) {
                                showLoginModalError('Please enter a valid phone number (6-15 digits)');
                                return;
                            }

                            // Combine country code with phone number
                            const fullPhoneNumber = countryCode + phoneNumber;

                            // Show loading state
                            const submitBtn = loginFormModal.querySelector('.btn-continue-modal');
                            const originalText = submitBtn.innerHTML;
                            submitBtn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid white; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span> Processing...';
                            submitBtn.disabled = true;

                            // Send OTP to phone number
                            sendOTPToPhone(fullPhoneNumber, submitBtn, originalText);
                        });

                        function showLoginModalError(message) {
                            const alertDiv = document.getElementById('loginModalAlert');
                            if (alertDiv) {
                                alertDiv.textContent = message;
                                alertDiv.className = 'alert alert-danger';
                                alertDiv.classList.remove('d-none');
                            } else {
                                alert(message);
                            }
                        }
                        window.showLoginModalError = showLoginModalError;

                        function showLoginModalSuccess(message) {
                            const alertDiv = document.getElementById('loginModalAlert');
                            if (alertDiv) {
                                alertDiv.textContent = message;
                                alertDiv.className = 'alert alert-success';
                                alertDiv.classList.remove('d-none');
                            } else {
                                alert(message);
                            }
                        }
                        window.showLoginModalSuccess = showLoginModalSuccess;
                    }

                    // Function to send OTP (made global for Google sign-in)
                    window.sendOTPToPhone = function (phoneNumber, submitBtn, originalText) {
                        // Show loading state
                        submitBtn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid white; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span> Sending OTP...';
                        submitBtn.disabled = true;

                        // Function to send OTP request with FormData
                        function sendOTPRequest(recaptchaToken = null) {
                            // Create FormData fresh each time
                            const formData = new FormData();
                            formData.append('phoneNumber', phoneNumber);

                            // Add reCAPTCHA token if available
                            if (recaptchaToken) {
                                formData.append('recaptchaToken', recaptchaToken);
                                console.log('Sending OTP with reCAPTCHA token:', recaptchaToken.substring(0, 20) + '...');
                            } else {
                                console.log('Sending OTP without reCAPTCHA token (simulation mode)');
                            }

                            // Debug: Log FormData contents
                            console.log('FormData contents:');
                            for (let pair of formData.entries()) {
                                console.log(pair[0] + ': ' + (pair[0] === 'recaptchaToken' ? pair[1].substring(0, 20) + '...' : pair[1]));
                            }

                            return fetch('<?php echo base_url('auth/send_otp'); ?>', {
                                method: 'POST',
                                body: formData
                            });
                        }

                        // Helper function to handle OTP response
                        function handleOTPResponse(data, submitBtn, originalText) {
                            if (data.success) {
                                // OTP sent successfully
                                console.log('OTP sent successfully:', data);

                                // Store session info for verification
                                window.otpSessionInfo = (data.data && data.data.sessionInfo) ? data.data.sessionInfo : (data.sessionInfo || '');
                                window.otpPhoneNumber = phoneNumber;

                                // Show OTP input section
                                showOTPInputSection(phoneNumber);

                                // Try to use Web OTP API if available
                                if ('OTPCredential' in window) {
                                    requestWebOTP(phoneNumber);
                                }

                                // Reset button
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;
                            } else {
                                // Error sending OTP
                                let errorMsg = data.message || 'Failed to send OTP. Please try again.';

                                // Add helpful error message
                                if (data.data && data.data.error) {
                                    errorMsg += '\n\nError Details: ' + JSON.stringify(data.data.error);
                                }

                                errorMsg += '\n\nTo fix this:\n';
                                errorMsg += '1. Verify authentication backend configuration\n';
                                errorMsg += '2. Check server logs for details\n';
                                errorMsg += '3. Contact support if issue persists';

                                showLoginModalError(errorMsg);
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;
                            }
                        }

                        // Try to get reCAPTCHA token if available
                        // Use the same variables defined in footer.php if available, otherwise get from PHP config
                        <?php
                        $this->config->load("recaptcha");
                        $recaptcha_config = $this->config->item("recaptcha");
                        ?>
                        const recaptchaSiteKey = (typeof window.recaptchaSiteKey !== 'undefined') ? window.recaptchaSiteKey : '<?php echo isset($recaptcha_config["site_key"]) ? $recaptcha_config["site_key"] : ""; ?>';
                        const recaptchaEnabled = (typeof window.recaptchaEnabled !== 'undefined') ? window.recaptchaEnabled : <?php echo isset($recaptcha_config["enabled"]) && $recaptcha_config["enabled"] ? 'true' : 'false'; ?>;

                        console.log('reCAPTCHA Check:', {
                            grecaptchaDefined: typeof grecaptcha !== 'undefined',
                            recaptchaSiteKey: recaptchaSiteKey,
                            recaptchaEnabled: recaptchaEnabled,
                            siteKeyValid: recaptchaSiteKey && recaptchaSiteKey !== '' && recaptchaSiteKey !== '6LfYourSiteKeyHere'
                        });

                        // Check if reCAPTCHA is available and configured
                        if (typeof grecaptcha !== 'undefined' && recaptchaEnabled && recaptchaSiteKey && recaptchaSiteKey !== '' && recaptchaSiteKey !== '6LfYourSiteKeyHere') {
                            try {
                                // Use grecaptcha.ready() to ensure it's loaded, then execute
                                grecaptcha.ready(function () {
                                    console.log('reCAPTCHA ready, executing...');
                                    grecaptcha.execute(recaptchaSiteKey, { action: 'send_otp' })
                                        .then(function (recaptchaToken) {
                                            console.log('reCAPTCHA token received, length:', recaptchaToken ? recaptchaToken.length : 0);
                                            return sendOTPRequest(recaptchaToken);
                                        })
                                        .then(function (response) {
                                            return response.json();
                                        })
                                        .then(data => {
                                            handleOTPResponse(data, submitBtn, originalText);
                                        })
                                        .catch(function (error) {
                                            console.warn('reCAPTCHA failed, sending without token:', error);
                                            // Fallback: send without reCAPTCHA token
                                            sendOTPRequest()
                                                .then(function (response) {
                                                    return response.json();
                                                })
                                                .then(data => {
                                                    handleOTPResponse(data, submitBtn, originalText);
                                                })
                                                .catch(function (error) {
                                                    console.error('OTP request failed:', error);
                                                    showLoginModalError('Failed to send OTP. Please try again.');
                                                    submitBtn.innerHTML = originalText;
                                                    submitBtn.disabled = false;
                                                });
                                        });
                                });
                                return; // Exit early since we're handling the promise chain
                            } catch (e) {
                                console.error('reCAPTCHA error:', e);
                                // Fall through to send without token
                            }
                        } else {
                            if (typeof grecaptcha === 'undefined') {
                                console.warn('grecaptcha is not defined - reCAPTCHA script may not be loaded');
                            }
                            if (!recaptchaEnabled) {
                                console.warn('reCAPTCHA is disabled in config');
                            }
                            if (!recaptchaSiteKey || recaptchaSiteKey === '' || recaptchaSiteKey === '6LfYourSiteKeyHere') {
                                console.warn('reCAPTCHA site key not configured properly');
                            }
                        }

                        // If reCAPTCHA is not available, send without token
                        console.log('Sending OTP request without reCAPTCHA...');
                        sendOTPRequest()
                            .then(function (response) {
                                return response.json();
                            })
                            .then(data => {
                                handleOTPResponse(data, submitBtn, originalText);
                            })
                            .catch(function (error) {
                                console.error('OTP request failed:', error);
                                showLoginModalError('Failed to send OTP. Please try again.');
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;
                            });
                    }

                    // Function to show OTP input section
                    function showOTPInputSection(phoneNumber) {
                        // Hide phone input section
                        const phoneFormGroup = document.getElementById('phoneFormGroup');
                        if (phoneFormGroup) {
                            phoneFormGroup.style.display = 'none';
                        }

                        // Hide Continue button
                        const continueBtn = document.querySelector('.btn-continue-modal');
                        if (continueBtn) {
                            continueBtn.style.display = 'none';
                        }

                        // Create or show OTP input section
                        let otpSection = document.getElementById('otpInputSection');
                        if (!otpSection) {
                            otpSection = document.createElement('div');
                            otpSection.id = 'otpInputSection';
                            otpSection.innerHTML = `
                                <div class="form-group mb-3" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 12px; font-weight: 600; color: #2c3e50; font-size: 15px;">Enter OTP</label>
                                    <p style="color: #6c757d; font-size: 14px; margin-bottom: 15px;">OTP sent to ${phoneNumber}</p>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="otpCodeInput" 
                                        name="otpCode" 
                                        placeholder="Enter 6-digit OTP" 
                                        maxlength="6"
                                        required 
                                        style="border: 2px solid #e9ecef; border-radius: 15px; height: 55px; font-size: 18px; text-align: center; letter-spacing: 8px; font-weight: 600;"
                                        onfocus="this.style.borderColor='#3498db'; this.style.boxShadow='0 0 0 3px rgba(52, 152, 219, 0.1)'"
                                        onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'"
                                    >
                                </div>
                                <div class="d-grid mb-3">
                                    <button 
                                        type="button" 
                                        class="btn btn-primary btn-verify-otp" 
                                        style="background: #1e3a8a; border: none; border-radius: 15px; height: 55px; font-size: 16px; font-weight: 700; color: white; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3);"
                                    >
                                        Verify OTP
                                    </button>
                                </div>
                                <div class="text-center">
                                    <a href="#" id="resendOtpLink" style="color: #3498db; text-decoration: none; font-weight: 600;">Resend OTP</a>
                                    <span id="otpTimer" style="color: #6c757d; margin-left: 10px;"></span>
                                </div>
                            `;

                            // Insert after phone form group
                            if (phoneFormGroup && phoneFormGroup.parentNode) {
                                phoneFormGroup.parentNode.insertBefore(otpSection, phoneFormGroup.nextSibling);
                            }
                        } else {
                            otpSection.style.display = 'block';
                        }

                        // Focus on OTP input
                        setTimeout(() => {
                            const otpInput = document.getElementById('otpCodeInput');
                            if (otpInput) {
                                otpInput.focus();
                            }
                        }, 100);

                        // Add event listeners
                        setupOTPVerification();
                        setupResendOTP(phoneNumber);
                        startOTPTimer();
                    }

                    // Function to setup OTP verification
                    function setupOTPVerification() {
                        const verifyBtn = document.querySelector('.btn-verify-otp');
                        const otpInput = document.getElementById('otpCodeInput');

                        if (verifyBtn && otpInput) {
                            // Remove existing listeners
                            const newVerifyBtn = verifyBtn.cloneNode(true);
                            verifyBtn.parentNode.replaceChild(newVerifyBtn, verifyBtn);

                            newVerifyBtn.addEventListener('click', function () {
                                const otpCode = otpInput.value.trim();

                                if (!otpCode || otpCode.length !== 6) {
                                    showLoginModalError('Please enter a valid 6-digit OTP');
                                    return;
                                }

                                // Verify OTP
                                verifyOTP(otpCode, newVerifyBtn);
                            });

                            // Auto-submit on 6 digits
                            otpInput.addEventListener('input', function (e) {
                                const value = e.target.value.replace(/\D/g, '');
                                e.target.value = value;

                                if (value.length === 6) {
                                    // Auto-verify after a short delay
                                    setTimeout(() => {
                                        newVerifyBtn.click();
                                    }, 300);
                                }
                            });
                        }
                    }

                    // Function to verify OTP
                    function verifyOTP(otpCode, verifyBtn) {
                        const originalText = verifyBtn.innerHTML;
                        verifyBtn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid white; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span> Verifying...';
                        verifyBtn.disabled = true;

                        const formData = new FormData();
                        formData.append('sessionInfo', window.otpSessionInfo || '');
                        formData.append('code', otpCode);
                        formData.append('phoneNumber', window.otpPhoneNumber || '');

                        // Check if this is Google sign-in flow
                        const isGoogleSignIn = window.googleUserData && window.googleUserData.idToken;

                        fetch('<?php echo base_url('auth/verify_otp'); ?>', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // If Google sign-in, complete registration with Google data
                                    if (isGoogleSignIn && data.data && data.data.newUser) {
                                        // Complete registration with Google data
                                        completeGoogleOTPRegistration(data.data, verifyBtn, originalText);
                                        return;
                                    }

                                    // Check if new user needs to complete profile
                                    if (data.data && data.data.newUser) {
                                        // Show popup to collect full name and email
                                        showNewUserRegistrationPopup(data.data);
                                        verifyBtn.innerHTML = originalText;
                                        verifyBtn.disabled = false;
                                    } else {
                                        // Existing user - login successful
                                        // If Google sign-in, update with Google data
                                        if (isGoogleSignIn && window.googleUserData) {
                                            updateUserWithGoogleData(data.data || data);
                                        } else {
                                            // Close modal
                                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalLogin'));
                                            if (modal) {
                                                modal.hide();
                                            }

                                            // Update header immediately without reload
                                            if (typeof updateHeaderForLogin === 'function') {
                                                updateHeaderForLogin(data.data || data);
                                            }

                                            // Check for pending enquiry first
                                            if (window.pendingEnquiry && typeof showEnquiryMessageModal === 'function') {
                                                const propertyData = window.pendingEnquiry;
                                                window.pendingEnquiry = null;
                                                setTimeout(() => {
                                                    showEnquiryMessageModal(propertyData);
                                                }, 500);
                                            } else {
                                                // Reload page to update login state
                                                setTimeout(function () {
                                                    window.location.reload();
                                                }, 500);
                                            }
                                        }
                                    }
                                } else {
                                    showLoginModalError(data.message || 'Invalid OTP. Please try again.');
                                    verifyBtn.innerHTML = originalText;
                                    verifyBtn.disabled = false;

                                    // Clear OTP input
                                    const otpInput = document.getElementById('otpCodeInput');
                                    if (otpInput) {
                                        otpInput.value = '';
                                        otpInput.focus();
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Error verifying OTP:', error);
                                showLoginModalError('An error occurred while verifying OTP. Please try again.');
                                verifyBtn.innerHTML = originalText;
                                verifyBtn.disabled = false;
                            });
                    }

                    // Function to complete Google OTP registration
                    function completeGoogleOTPRegistration(otpData, verifyBtn, originalText) {
                        if (!window.googleUserData) {
                            showLoginModalError('Google sign-in data not found. Please try again.');
                            verifyBtn.innerHTML = originalText;
                            verifyBtn.disabled = false;
                            return;
                        }

                        const formData = new FormData();
                        formData.append('phoneNumber', window.otpPhoneNumber || window.googleUserData.phoneNumber || '');
                        formData.append('fullName', window.googleUserData.displayName || '');
                        formData.append('email', window.googleUserData.email || '');
                        formData.append('idToken', window.googleUserData.idToken || '');
                        formData.append('refreshToken', 'refresh_token');
                        formData.append('expiresIn', '3600');

                        fetch('<?php echo base_url('auth/complete_otp_registration'); ?>', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Close modals
                                    const loginModal = bootstrap.Modal.getInstance(document.getElementById('modalLogin'));
                                    if (loginModal) {
                                        loginModal.hide();
                                    }

                                    // Update header
                                    if (typeof updateHeaderForLogin === 'function') {
                                        updateHeaderForLogin(data.data || data);
                                    }

                                    // Check for pending enquiry first
                                    if (window.pendingEnquiry && typeof showEnquiryMessageModal === 'function') {
                                        const propertyData = window.pendingEnquiry;
                                        window.pendingEnquiry = null;
                                        setTimeout(() => {
                                            showEnquiryMessageModal(propertyData);
                                        }, 500);
                                    } else {
                                        alert('Registration completed successfully! Welcome!');
                                        setTimeout(function () {
                                            window.location.reload();
                                        }, 500);
                                    }
                                } else {
                                    showLoginModalError(data.message || 'Failed to complete registration. Please try again.');
                                    verifyBtn.innerHTML = originalText;
                                    verifyBtn.disabled = false;
                                }
                            })
                            .catch(error => {
                                console.error('Error completing registration:', error);
                                showLoginModalError('An error occurred while completing registration. Please try again.');
                                verifyBtn.innerHTML = originalText;
                                verifyBtn.disabled = false;
                            });
                    }

                    // Function to update existing user with Google data
                    function updateUserWithGoogleData(userData) {
                        // Check for pending enquiry first
                        if (window.pendingEnquiry && typeof showEnquiryMessageModal === 'function') {
                            const propertyData = window.pendingEnquiry;
                            window.pendingEnquiry = null;

                            // Close modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalLogin'));
                            if (modal) {
                                modal.hide();
                            }

                            // Update header
                            if (typeof updateHeaderForLogin === 'function') {
                                updateHeaderForLogin(userData);
                            }

                            setTimeout(() => {
                                showEnquiryMessageModal(propertyData);
                            }, 500);
                            return;
                        }
                        if (!window.googleUserData) {
                            return;
                        }

                        const formData = new FormData();
                        formData.append('idToken', window.googleUserData.idToken);
                        formData.append('email', window.googleUserData.email);
                        formData.append('displayName', window.googleUserData.displayName);
                        formData.append('phoneNumber', window.otpPhoneNumber || window.googleUserData.phoneNumber || '');

                        fetch('<?php echo base_url('auth/google-signin'); ?>', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                // Close modal
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalLogin'));
                                if (modal) {
                                    modal.hide();
                                }

                                // Update header
                                if (typeof updateHeaderForLogin === 'function') {
                                    updateHeaderForLogin(data.data || data || userData);
                                }

                                // Check for pending enquiry first
                                if (window.pendingEnquiry && typeof showEnquiryMessageModal === 'function') {
                                    const propertyData = window.pendingEnquiry;
                                    window.pendingEnquiry = null;
                                    setTimeout(() => {
                                        showEnquiryMessageModal(propertyData);
                                    }, 500);
                                } else {
                                    setTimeout(function () {
                                        window.location.reload();
                                    }, 500);
                                }
                            })
                            .catch(error => {
                                console.error('Error updating with Google data:', error);
                                // Still proceed with login
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalLogin'));
                                if (modal) {
                                    modal.hide();
                                }

                                if (typeof updateHeaderForLogin === 'function') {
                                    updateHeaderForLogin(userData);
                                }

                                // Check for pending enquiry
                                if (window.pendingEnquiry && typeof showEnquiryMessageModal === 'function') {
                                    const propertyData = window.pendingEnquiry;
                                    window.pendingEnquiry = null;
                                    setTimeout(() => {
                                        showEnquiryMessageModal(propertyData);
                                    }, 500);
                                } else {
                                    setTimeout(function () {
                                        window.location.reload();
                                    }, 500);
                                }
                            });
                    }

                    // Function to show new user registration popup
                    function showNewUserRegistrationPopup(userData) {
                        // Create modal if it doesn't exist
                        let registrationModal = document.getElementById('newUserRegistrationModal');
                        if (!registrationModal) {
                            registrationModal = document.createElement('div');
                            registrationModal.id = 'newUserRegistrationModal';
                            registrationModal.className = 'modal fade';
                            registrationModal.setAttribute('tabindex', '-1');
                            registrationModal.setAttribute('aria-labelledby', 'newUserRegistrationModalLabel');
                            registrationModal.setAttribute('aria-hidden', 'true');
                            registrationModal.innerHTML = `
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                                        <div class="modal-header" style="border-bottom: 1px solid #e9ecef; padding: 25px 30px;">
                                            <h5 class="modal-title" id="newUserRegistrationModalLabel" style="font-weight: 700; color: #2c3e50; font-size: 22px;">Complete Your Profile</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body" style="padding: 30px;">
                                            <p style="color: #6c757d; margin-bottom: 25px; font-size: 15px;">Please provide your details to complete registration</p>
                                            <form id="newUserRegistrationForm">
                                                <div class="form-group mb-4">
                                                    <label for="newUserFullName" style="display: block; margin-bottom: 10px; font-weight: 600; color: #2c3e50; font-size: 14px;">Full Name <span style="color: #dc3545;">*</span></label>
                                                    <input 
                                                        type="text" 
                                                        class="form-control" 
                                                        id="newUserFullName" 
                                                        name="fullName" 
                                                        placeholder="Enter your full name" 
                                                        required
                                                        style="border: 2px solid #e9ecef; border-radius: 12px; height: 50px; font-size: 16px; padding: 0 20px; transition: all 0.3s ease;"
                                                        onfocus="this.style.borderColor='#3498db'; this.style.boxShadow='0 0 0 3px rgba(52, 152, 219, 0.1)'"
                                                        onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'"
                                                    >
                                                </div>
                                                <div class="form-group mb-4">
                                                    <label for="newUserEmail" style="display: block; margin-bottom: 10px; font-weight: 600; color: #2c3e50; font-size: 14px;">Email Address</label>
                                                    <input 
                                                        type="email" 
                                                        class="form-control" 
                                                        id="newUserEmail" 
                                                        name="email" 
                                                        placeholder="Enter your email (optional)" 
                                                        style="border: 2px solid #e9ecef; border-radius: 12px; height: 50px; font-size: 16px; padding: 0 20px; transition: all 0.3s ease;"
                                                        onfocus="this.style.borderColor='#3498db'; this.style.boxShadow='0 0 0 3px rgba(52, 152, 219, 0.1)'"
                                                        onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'"
                                                    >
                                                </div>
                                                <div class="form-group mb-4">
                                                    <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #2c3e50; font-size: 14px;">Phone Number</label>
                                                    <input 
                                                        type="text" 
                                                        class="form-control" 
                                                        id="newUserPhone" 
                                                        name="phoneNumber" 
                                                        readonly
                                                        style="border: 2px solid #e9ecef; border-radius: 12px; height: 50px; font-size: 16px; padding: 0 20px; background-color: #f8f9fa; color: #6c757d;"
                                                    >
                                                </div>
                                                <div class="d-grid">
                                                    <button 
                                                        type="submit" 
                                                        class="btn btn-primary" 
                                                        id="completeRegistrationBtn"
                                                        style="background: #1e3a8a; border: none; border-radius: 12px; height: 50px; font-size: 16px; font-weight: 700; color: white; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3);"
                                                    >
                                                        Complete Registration
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            `;
                            document.body.appendChild(registrationModal);
                        }

                        // Set phone number (auto-filled)
                        const phoneInput = document.getElementById('newUserPhone');
                        if (phoneInput) {
                            phoneInput.value = userData.phoneNumber || window.otpPhoneNumber || '';
                        }

                        // Store user data for form submission
                        window.newUserData = userData;

                        // Show modal
                        const modal = new bootstrap.Modal(registrationModal);
                        modal.show();

                        // Focus on full name input
                        setTimeout(() => {
                            const fullNameInput = document.getElementById('newUserFullName');
                            if (fullNameInput) {
                                fullNameInput.focus();
                            }
                        }, 300);

                        // Handle form submission
                        const registrationForm = document.getElementById('newUserRegistrationForm');
                        if (registrationForm) {
                            // Remove existing listeners
                            const newForm = registrationForm.cloneNode(true);
                            registrationForm.parentNode.replaceChild(newForm, registrationForm);

                            newForm.addEventListener('submit', function (e) {
                                e.preventDefault();
                                completeNewUserRegistration(newForm, modal);
                            });
                        }
                    }

                    // Function to complete new user registration
                    function completeNewUserRegistration(form, modal) {
                        const submitBtn = document.getElementById('completeRegistrationBtn');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid white; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span> Saving...';
                        submitBtn.disabled = true;

                        const fullName = document.getElementById('newUserFullName').value.trim();
                        const email = document.getElementById('newUserEmail').value.trim();
                        const phoneNumber = document.getElementById('newUserPhone').value;

                        if (!fullName) {
                            showLoginModalError('Please enter your full name');
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                            return;
                        }

                        const formData = new FormData();
                        formData.append('phoneNumber', phoneNumber);
                        formData.append('fullName', fullName);
                        formData.append('email', email);
                        formData.append('idToken', window.newUserData.idToken || '');
                        formData.append('refreshToken', window.newUserData.refreshToken || '');
                        formData.append('expiresIn', window.newUserData.expiresIn || '3600');

                        fetch('<?php echo base_url('auth/complete_otp_registration'); ?>', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Close modals
                                    modal.hide();
                                    const loginModal = bootstrap.Modal.getInstance(document.getElementById('modalLogin'));
                                    if (loginModal) {
                                        loginModal.hide();
                                    }

                                    // Update header
                                    if (typeof updateHeaderForLogin === 'function') {
                                        updateHeaderForLogin(data.data || data);
                                    }

                                    // Check for pending enquiry first
                                    if (window.pendingEnquiry && typeof showEnquiryMessageModal === 'function') {
                                        const propertyData = window.pendingEnquiry;
                                        window.pendingEnquiry = null;
                                        setTimeout(() => {
                                            showEnquiryMessageModal(propertyData);
                                        }, 500);
                                    } else {
                                        alert('Registration completed successfully! Welcome!');
                                        setTimeout(function () {
                                            window.location.reload();
                                        }, 500);
                                    }

                                    // Hide username and logout option
                                    const userDisplayName = document.getElementById('userDisplayName');
                                    const logoutBtnContainer = document.getElementById('logoutBtnContainer');
                                    const logoutDivider = document.getElementById('logoutDivider');
                                    const userEmailContainer = document.getElementById('userEmailContainer');

                                    if (userDisplayName) {
                                        userDisplayName.style.display = 'none';
                                    }
                                    if (logoutBtnContainer) {
                                        logoutBtnContainer.style.display = 'none';
                                    }
                                    if (logoutDivider) {
                                        logoutDivider.style.display = 'none';
                                    }
                                    if (userEmailContainer) {
                                        userEmailContainer.style.display = 'none';
                                    }

                                    // Reload page to update login state
                                    window.location.reload();
                                } else {
                                    showLoginModalError(data.message || 'Failed to complete registration. Please try again.');
                                    submitBtn.innerHTML = originalText;
                                    submitBtn.disabled = false;
                                }
                            })
                            .catch(error => {
                                console.error('Error completing registration:', error);
                                showLoginModalError('An error occurred while completing registration. Please try again.');
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;
                            });
                    }

                    // Function to setup resend OTP
                    function setupResendOTP(phoneNumber) {
                        const resendLink = document.getElementById('resendOtpLink');
                        if (resendLink) {
                            resendLink.addEventListener('click', function (e) {
                                e.preventDefault();

                                const countryCode = document.getElementById('countryCodeModal').value;
                                const fullPhoneNumber = countryCode + phoneNumber;

                                const submitBtn = document.querySelector('.btn-continue-modal');
                                sendOTPToPhone(fullPhoneNumber, submitBtn, submitBtn.innerHTML);
                            });
                        }
                    }

                    // Function to start OTP timer
                    function startOTPTimer() {
                        let countdown = 60;
                        const timerElement = document.getElementById('otpTimer');
                        const resendLink = document.getElementById('resendOtpLink');

                        if (!timerElement) return;

                        const timerInterval = setInterval(function () {
                            if (countdown > 0) {
                                timerElement.textContent = `(${countdown}s)`;
                                if (resendLink) {
                                    resendLink.style.pointerEvents = 'none';
                                    resendLink.style.opacity = '0.5';
                                }
                                countdown--;
                            } else {
                                clearInterval(timerInterval);
                                timerElement.textContent = '';
                                if (resendLink) {
                                    resendLink.style.pointerEvents = 'auto';
                                    resendLink.style.opacity = '1';
                                }
                            }
                        }, 1000);
                    }

                    // Function to request Web OTP API
                    function requestWebOTP(phoneNumber) {
                        if ('OTPCredential' in window) {
                            const abortController = new AbortController();

                            navigator.credentials.get({
                                otp: { transport: ['sms'] },
                                signal: abortController.signal
                            })
                                .then(otp => {
                                    // Auto-fill OTP
                                    const otpInput = document.getElementById('otpCodeInput');
                                    if (otpInput && otp.code) {
                                        otpInput.value = otp.code;

                                        // Auto-verify
                                        setTimeout(() => {
                                            const verifyBtn = document.querySelector('.btn-verify-otp');
                                            if (verifyBtn) {
                                                verifyBtn.click();
                                            }
                                        }, 300);
                                    }
                                })
                                .catch(err => {
                                    console.log('Web OTP API not available or user cancelled:', err);
                                    // Fallback to manual entry
                                });

                            // Abort after 2 minutes
                            setTimeout(() => {
                                abortController.abort();
                            }, 120000);
                        }
                    }

                    // Phone number formatting for modal
                    const phoneInputModal = document.getElementById('phoneNumberModal');
                    if (phoneInputModal) {
                        phoneInputModal.addEventListener('input', function (e) {
                            // Remove all non-digit characters
                            let value = e.target.value.replace(/\D/g, '');

                            // Limit to 15 digits (international standard)
                            if (value.length > 15) {
                                value = value.substring(0, 15);
                            }

                            e.target.value = value;
                        });
                    }

                    // Ensure country code dropdown works properly
                    const countryCodeSelect = document.getElementById('countryCodeModal');
                    const phoneInputContainer = document.getElementById('phoneInputContainer');
                    const phoneFormGroup = document.getElementById('phoneFormGroup');
                    const loginModalBody = document.getElementById('loginModalBody');
                    const modalContent = document.getElementById('loginModalContent');

                    function makeDropdownVisible() {
                        if (phoneInputContainer) {
                            phoneInputContainer.style.overflow = 'visible';
                            phoneInputContainer.style.zIndex = '1000';
                            // Remove border-radius temporarily to allow dropdown to expand
                            phoneInputContainer.style.borderRadius = '0';
                        }
                        if (phoneFormGroup) {
                            phoneFormGroup.style.overflow = 'visible';
                            phoneFormGroup.style.zIndex = '1000';
                        }
                        if (loginModalBody) {
                            loginModalBody.style.overflow = 'visible';
                        }
                        if (modalContent) {
                            modalContent.style.overflow = 'visible';
                        }
                        if (countryCodeSelect) {
                            countryCodeSelect.style.zIndex = '1000';
                            // Ensure select can expand
                            countryCodeSelect.style.position = 'relative';
                        }
                    }

                    function resetDropdownVisibility() {
                        setTimeout(function () {
                            if (phoneInputContainer) {
                                phoneInputContainer.style.overflow = 'visible';
                                phoneInputContainer.style.zIndex = '1';
                                // Restore border-radius
                                phoneInputContainer.style.borderRadius = '15px';
                            }
                            if (phoneFormGroup) {
                                phoneFormGroup.style.overflow = 'visible';
                                phoneFormGroup.style.zIndex = '1';
                            }
                            if (loginModalBody) {
                                loginModalBody.style.overflow = 'visible';
                            }
                            if (modalContent) {
                                modalContent.style.overflow = 'hidden';
                            }
                        }, 300);
                    }

                    if (countryCodeSelect) {
                        // Make dropdown visible when clicked/focused
                        countryCodeSelect.addEventListener('mousedown', function (e) {
                            makeDropdownVisible();
                        });

                        countryCodeSelect.addEventListener('focus', function (e) {
                            makeDropdownVisible();
                        });

                        countryCodeSelect.addEventListener('change', function (e) {
                            console.log('Country code selected:', this.value);
                            resetDropdownVisibility();
                        });

                        countryCodeSelect.addEventListener('blur', function (e) {
                            resetDropdownVisibility();
                        });

                        // Ensure dropdown opens on click
                        countryCodeSelect.addEventListener('click', function (e) {
                            makeDropdownVisible();
                        });

                        // Also handle when dropdown opens (for better browser support)
                        countryCodeSelect.addEventListener('mouseenter', function (e) {
                            // Keep visible on hover
                        });
                    }
                });

                // Add spin animation for loading
                if (!document.getElementById('modalLoginStyles')) {
                    const style = document.createElement('style');
                    style.id = 'modalLoginStyles';
                    style.textContent = `
                        @keyframes spin {
                            to { transform: rotate(360deg); }
                        }
                        .phone-input-container:focus-within {
                            border-color: #3498db !important;
                            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1) !important;
                        }
                        .btn-continue-modal:active {
                            transform: translateY(0) !important;
                            box-shadow: 0 2px 10px rgba(30, 58, 138, 0.3) !important;
                        }
                        .btn-google-modal:active {
                            transform: translateY(0) !important;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
                        }
                        #countryCodeModal {
                            -webkit-appearance: none;
                            -moz-appearance: none;
                            appearance: none;
                            background-color: white !important;
                        }
                        #countryCodeModal::-ms-expand {
                            display: none;
                        }
                        #countryCodeModal:hover {
                            background-color: #f8f9fa !important;
                        }
                        #countryCodeModal:focus {
                            background-color: white !important;
                            z-index: 100 !important;
                        }
                        #countryCodeModal option {
                            padding: 10px;
                            background-color: white;
                            color: #2c3e50;
                        }
                        .phone-input-container {
                            overflow: visible !important;
                        }
                        /* Ensure select dropdown works properly and appears outside */
                        #countryCodeModal {
                            position: relative;
                            z-index: 1000;
                        }
                        /* Make sure select dropdown appears outside container */
                        #countryCodeModal:focus {
                            position: relative;
                            z-index: 1000;
                        }
                        /* Ensure the select wrapper allows dropdown to expand */
                        #phoneInputContainer > div:first-child {
                            overflow: visible !important;
                            position: relative;
                            z-index: 1000;
                        }
                        /* Make sure dropdown options are visible */
                        #countryCodeModal option {
                            padding: 10px 15px;
                            background-color: white;
                            color: #2c3e50;
                            display: block;
                            min-height: 40px;
                            line-height: 40px;
                        }
                        /* Ensure parent container allows dropdown to expand - only for phone input */
                        #phoneFormGroup {
                            overflow: visible !important;
                        }
                        /* Make sure modal body doesn't clip dropdown */
                        #loginModalBody {
                            overflow: visible !important;
                        }
                        /* Social Login Modal Country Code Styling */
                        #socialCountryCode {
                            -webkit-appearance: none !important;
                            -moz-appearance: none !important;
                            appearance: none !important;
                            background-color: white !important;
                            background-image: none !important;
                            background-repeat: no-repeat !important;
                            background-position: right center !important;
                        }
                        #socialCountryCode::-ms-expand {
                            display: none !important;
                        }
                        #socialCountryCode::-webkit-appearance {
                            -webkit-appearance: none !important;
                        }
                        #socialCountryCode:hover {
                            background-color: #f8f9fa !important;
                        }
                        #socialCountryCode:focus {
                            background-color: white !important;
                            outline: none !important;
                            box-shadow: none !important;
                        }
                        #socialCountryCode option {
                            padding: 10px 15px;
                            background-color: white;
                            color: #2c3e50;
                            font-weight: 500;
                        }
                        /* Focus state for the entire input group */
                        #socialLoginInfoModal .input-group:focus-within {
                            border-color: #3498db !important;
                            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1) !important;
                        }
                        /* Ensure social login modal input group allows dropdown */
                        #socialLoginInfoModal .input-group {
                            overflow: visible !important;
                        }
                        #socialLoginInfoModal .modal-body {
                            overflow: visible !important;
                        }
                        /* Remove any default select styling */
                        #socialLoginInfoModal select {
                            background-image: none !important;
                        }
                        #socialLoginInfoModal select:not([multiple]) {
                            background-image: none !important;
                        }
                        /* Ensure modal content allows overflow when dropdown is open - handled by JS */
                        /* Default: overflow hidden for border-radius */
                        /* When dropdown is focused, ensure all parents allow overflow */
                        #phoneInputContainer:has(#countryCodeModal:focus) {
                            overflow: visible !important;
                            z-index: 1000 !important;
                        }
                        /* For better dropdown visibility on mobile */
                        @media (max-width: 768px) {
                            #countryCodeModal {
                                font-size: 14px;
                                min-width: 100px;
                                padding-right: 30px;
                            }
                        }
                    `;
                    document.head.appendChild(style);
                }
            </script>