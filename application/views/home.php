<?php
// Lazysizes expects a lightweight `src`; using full `data-src` URLs in `src` forces every image to download on load.
if (!isset($dvm_lazy_img_placeholder)) {
    $dvm_lazy_img_placeholder = 'data:image/svg+xml;charset=UTF-8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" width="400" height="300"><rect fill="#ececec" width="100%" height="100%"/></svg>');
}
?>
<!-- End Main Header -->
<!-- Slider -->
<style>
    /* Global Swiper Navigation Buttons - White Circle with Icons */
    .swiper-button-next,
    .swiper-button-prev {
        width: 45px !important;
        height: 45px !important;
        background: #fff !important;
        border-radius: 50% !important;
        color: #2c3e50 !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15) !important;
        transition: all 0.3s ease !important;
        z-index: 10 !important;
        margin-top: 0 !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: none !important;
    }

    .swiper-button-next::after,
    .swiper-button-prev::after {
        font-family: 'swiper-icons' !important;
        font-size: 20px !important;
        font-weight: bold !important;
    }

    .swiper-button-next:hover,
    .swiper-button-prev:hover {
        background: #2c3e50 !important;
        color: #fff !important;
        transform: translateY(-50%) scale(1.1) !important;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25) !important;
    }

    .swiper-button-next {
        right: 0 !important;
    }

    .swiper-button-prev {
        left: 0 !important;
    }

    /* Tab-pane sliders - ensure buttons are visible */
    .tab-pane .swiper {
        position: relative;
        padding: 0 50px;
    }

    .tab-pane .swiper-button-next,
    .tab-pane .swiper-button-prev {
        display: flex !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {

        .swiper-button-next,
        .swiper-button-prev {
            width: 40px !important;
            height: 40px !important;
        }

        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-size: 18px !important;
        }

        .tab-pane .swiper {
            padding: 0 45px;
        }
    }

    @media (max-width: 480px) {

        .swiper-button-next,
        .swiper-button-prev {
            width: 35px !important;
            height: 35px !important;
        }

        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-size: 16px !important;
        }

        .tab-pane .swiper {
            padding: 0 40px;
        }
    }

    .new-top {
        padding-top: 0px !important;
        padding-bottom: 0px !important;
    }

    .flat-location-v2 {
        padding-bottom: 58px !important;
    }

    .flat-recommended .tf-btn {
        margin-top: 17px !important;
    }

    .new-btn-view {
        margin-top: 64px !important;
    }

    /* Main Featured Properties Carousel */
    .main-featured-properties-carousel-wrapper {
        position: relative;
        width: 100%;
        padding: 0;
        margin: 0 auto;
    }

    #viewAll {
        position: relative;
    }

    .main-featured-properties-carousel-wrapper .swiper {
        width: 100%;
        overflow: hidden;
        position: relative;
        margin: 0 auto;
    }

    .main-featured-properties-carousel-wrapper .swiper-wrapper {
        display: flex;
        align-items: stretch;
    }

    .tf-sw-main-featured-properties .swiper-slide {
        height: auto;
        display: flex;
        box-sizing: border-box;
        flex-shrink: 0;
    }

    .tf-sw-main-featured-properties .homelengo-box {
        width: 100%;
        height: 96%;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
    }

    /* Mobile - Full Width Cards with Gap */
    @media (max-width: 991px) {

        .tf-sw-main-featured-properties .swiper-slide,
        .tf-sw-secondary-featured-properties .swiper-slide {
            width: 90% !important;
            max-width: 100% !important;
            flex-shrink: 0;
        }

        .tf-sw-main-featured-properties .homelengo-box,
        .tf-sw-secondary-featured-properties .homelengo-box {
            width: 100% !important;
            max-width: 100% !important;
        }

        .main-featured-properties-carousel-wrapper {
            padding: 0 15px !important;
        }
    }

    /* Mobile - 768px and below */
    @media (max-width: 768px) {

        .tf-sw-main-featured-properties .swiper-slide,
        .tf-sw-secondary-featured-properties .swiper-slide {
            width: 95% !important;
            margin-right: 12px !important;
        }

        .main-featured-properties-carousel-wrapper {
            padding: 0 10px !important;
        }

        .secondary-featured-properties-mobile {
            padding: 0 10px !important;
        }
    }

    /* Mobile - 480px and below */
    @media (max-width: 480px) {

        .tf-sw-main-featured-properties .swiper-slide,
        .tf-sw-secondary-featured-properties .swiper-slide {
            width: 100% !important;
            margin-right: 10px !important;
        }

        .main-featured-properties-carousel-wrapper {
            padding: 0 5px !important;
        }

        .secondary-featured-properties-mobile {
            padding: 0 5px !important;
        }
    }

    /* Adaptive Image Height */
    .adaptive-img-height {
        height: 310px;
    }

    @media (max-width: 768px) {
        .adaptive-img-height {
            height: 200px !important;
            /* Better height for tablet/mobile */
        }

        /* Compact Card Content on Mobile */
        .homelengo-box {
            height: auto !important;
            min-height: auto !important;
        }

        .homelengo-box .archive-bottom {
            padding: 10px 10px 8px 10px !important;
        }

        .homelengo-box .content-top h6 {
            font-size: 15px !important;
            margin-bottom: 4px !important;
            line-height: 1.3 !important;
        }

        .homelengo-box .meta-list {
            margin-bottom: 6px !important;
            display: flex !important;
            gap: 8px !important;
            flex-wrap: wrap !important;
        }

        .homelengo-box .meta-list .item {
            font-size: 11px !important;
            white-space: nowrap !important;
        }

        .homelengo-box p {
            font-size: 12px !important;
            line-height: 1.4 !important;
            margin-bottom: 8px !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
        }

        /* Side-by-side Layout: Button Left, Price Right */
        .homelengo-box .content-bottom {
            padding-top: 8px !important;
            margin-top: 6px !important;
            border-top: 1px solid #f0f0f0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-direction: row !important;
            gap: 10px !important;
            padding-bottom: 6px !important;
            margin-bottom: 0 !important;
        }

        /* Button container */
        .homelengo-box .content-bottom .d-flex {
            flex: 1 1 auto !important;
            width: auto !important;
            margin-right: 0 !important;
            margin-bottom: 0 !important;
            min-width: 0 !important;
        }

        .homelengo-box .price {
            font-size: 14px !important;
            margin: 0 !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
            font-weight: 700 !important;
            text-align: right !important;
        }

        .homelengo-box .tf-btn {
            padding: 6px 14px !important;
            height: auto !important;
            font-size: 12px !important;
            line-height: 1.4 !important;
            min-height: 32px !important;
            width: auto !important;
            margin-bottom: 0 !important;
            white-space: nowrap !important;
        }

        .homelengo-box .tf-btn span {
            display: inline-block !important;
            vertical-align: middle !important;
        }

        /* Mobile cursor and interaction styles */
        .secondary-featured-properties-new .homelengo-box {
            cursor: pointer !important;
        }

        .secondary-featured-properties-new .tf-btn {
            cursor: pointer !important;
        }
    }

    @media (max-width: 480px) {
        .adaptive-img-height {
            height: 160px !important;
            /* Smaller for extra small devices */
        }

        /* Extra small mobile optimizations */
        .homelengo-box .archive-bottom {
            padding: 8px 8px 6px 8px !important;
        }

        .homelengo-box .content-top h6 {
            font-size: 14px !important;
            margin-bottom: 3px !important;
        }

        .homelengo-box .meta-list {
            margin-bottom: 4px !important;
            gap: 6px !important;
        }

        .homelengo-box .meta-list .item {
            font-size: 10px !important;
        }

        .homelengo-box p {
            font-size: 11px !important;
            margin-bottom: 6px !important;
            -webkit-line-clamp: 2 !important;
        }

        .homelengo-box .content-bottom {
            padding-top: 6px !important;
            gap: 6px !important;
            padding-bottom: 4px !important;
            margin-top: 4px !important;
        }

        .homelengo-box .price {
            font-size: 13px !important;
        }

        .homelengo-box .tf-btn {
            padding: 6px 12px !important;
            font-size: 11px !important;
            min-height: 30px !important;
            height: auto !important;
        }
    }

    .secondary-featured-properties {
        padding-top: 0px !important;
    }

    /* Secondary Featured Properties New Section */
    .secondary-featured-properties-new {
        position: relative;
        padding-top: 40px !important;
        padding-bottom: 40px !important;
    }

    .secondary-featured-properties-new .homelengo-box {
        cursor: pointer !important;
        transition: all 0.3s ease !important;
    }

    .secondary-featured-properties-new .homelengo-box:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }

    .secondary-featured-properties-new .tf-btn {
        cursor: pointer !important;
    }

    .secondary-featured-properties-new .images-group {
        cursor: pointer !important;
    }

    .secondary-featured-properties-new .content-bottom {
        cursor: pointer !important;
    }

    .secondary-featured-properties-new a {
        cursor: pointer !important;
    }

    /* Show More Link Styling */
    .content-bottom .show-more-link {
        color: #007bff !important;
        text-decoration: none !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
        cursor: pointer !important;
    }

    .content-bottom .show-more-link:hover {
        color: #0056b3 !important;
        text-decoration: underline !important;
    }

    .content-bottom p {
        margin-bottom: 12px;
        line-height: 1.6;
        color: #333;
    }

    /* Secondary Featured Properties Mobile Slider */
    .secondary-featured-properties-mobile {
        position: relative;
        padding: 0 15px;
        width: 100%;
        margin: 0 auto;
        box-sizing: border-box;
    }

    .secondary-featured-properties-mobile .swiper {
        width: 100%;
        overflow: hidden;
        position: relative;
        margin: 0 auto;
        box-sizing: border-box;
    }

    .secondary-featured-properties-mobile .swiper-wrapper {
        display: flex;
        align-items: stretch;
    }

    .tf-sw-secondary-featured-properties .swiper-slide {
        height: auto;
        display: flex;
        box-sizing: border-box;
        width: 100% !important;
        max-width: 100% !important;
        flex-shrink: 0;
        padding: 0;
        margin: 0;
    }

    .tf-sw-secondary-featured-properties .homelengo-box {
        width: 100% !important;
        max-width: 100% !important;
        height: 100%;
        display: flex;
        flex-direction: column;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .tf-sw-secondary-featured-properties .homelengo-box .archive-top,
    .tf-sw-secondary-featured-properties .homelengo-box .archive-bottom {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
    }

    .tf-sw-secondary-featured-properties .images-style {
        width: 100% !important;
        max-width: 100% !important;
    }

    .tf-sw-secondary-featured-properties .images-style img {
        width: 100% !important;
        max-width: 100% !important;
        height: auto;
    }

    .tf-sw-secondary-featured-properties .images-group {
        width: 100% !important;
        display: block;
    }

    /* Responsive overlays for property cards */
    .archive-top {
        position: relative;
    }

    .archive-top .top {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 5;
    }

    .archive-top .top ul {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .archive-top .top .flag-tag {
        font-size: 12px;
        padding: 4px 8px;
        white-space: nowrap;
    }

    .archive-top .bottom {
        position: absolute;
        bottom: 10px;
        left: 10px;
        z-index: 5;
        color: white;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 4px;
    }

    @media (max-width: 768px) {
        .archive-top .top {
            top: 8px;
            left: 8px;
        }

        .archive-top .top .flag-tag {
            font-size: 11px;
            padding: 3px 6px;
        }

        .archive-top .bottom {
            bottom: 8px;
            left: 8px;
            font-size: 11px;
            padding: 3px 6px;
        }
    }

    @media (max-width: 480px) {
        .archive-top .top {
            top: 6px;
            left: 6px;
        }

        .archive-top .top .flag-tag {
            font-size: 10px;
            padding: 2px 5px;
        }

        .archive-top .bottom {
            bottom: 6px;
            left: 6px;
            font-size: 10px;
            padding: 2px 5px;
        }
    }

    /* Navigation Buttons for Secondary Featured Properties - Outside Container */
    #viewAllnew {
        position: relative;
    }

    /* Show buttons only on mobile */
    #viewAllnew .secondary-featured-next,
    #viewAllnew .secondary-featured-prev,
    #viewAllnew .secondary-featured-next-new,
    #viewAllnew .secondary-featured-prev-new {
        display: none !important;
        width: 45px !important;
        height: 45px !important;
        background: #fff !important;
        border-radius: 50% !important;
        color: #2c3e50 !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15) !important;
        transition: all 0.3s ease !important;
        z-index: 10 !important;
        margin-top: 0 !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        align-items: center !important;
        justify-content: center !important;
        position: absolute !important;
        cursor: pointer !important;
        pointer-events: auto !important;
        opacity: 1 !important;
    }

    @media (max-width: 767px) {

        #viewAllnew .secondary-featured-next,
        #viewAllnew .secondary-featured-prev,
        #viewAllnew .secondary-featured-next-new,
        #viewAllnew .secondary-featured-prev-new {
            display: flex !important;
        }
    }

    #viewAllnew .secondary-featured-next,
    #viewAllnew .secondary-featured-next-new {
        right: 15px !important;
    }

    #viewAllnew .secondary-featured-prev,
    #viewAllnew .secondary-featured-prev-new {
        left: 15px !important;
    }

    #viewAllnew .secondary-featured-next:hover,
    #viewAllnew .secondary-featured-prev:hover,
    #viewAllnew .secondary-featured-next-new:hover,
    #viewAllnew .secondary-featured-prev-new:hover {
        background: #2c3e50 !important;
        color: #fff !important;
        transform: translateY(-50%) scale(1.1) !important;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25) !important;
    }

    #viewAllnew .secondary-featured-next::after,
    #viewAllnew .secondary-featured-prev::after,
    #viewAllnew .secondary-featured-next-new::after,
    #viewAllnew .secondary-featured-prev-new::after {
        font-size: 20px !important;
        font-weight: bold !important;
        font-family: 'swiper-icons' !important;
    }

    @media (max-width: 768px) {

        #viewAllnew .secondary-featured-next,
        #viewAllnew .secondary-featured-next-new {
            right: 10px !important;
        }

        #viewAllnew .secondary-featured-prev,
        #viewAllnew .secondary-featured-prev-new {
            left: 10px !important;
        }

        #viewAllnew .secondary-featured-next,
        #viewAllnew .secondary-featured-prev,
        #viewAllnew .secondary-featured-next-new,
        #viewAllnew .secondary-featured-prev-new {
            width: 40px !important;
            height: 40px !important;
        }

        #viewAllnew .secondary-featured-next::after,
        #viewAllnew .secondary-featured-prev::after,
        #viewAllnew .secondary-featured-next-new::after,
        #viewAllnew .secondary-featured-prev-new::after {
            font-size: 18px !important;
        }
    }

    @media (max-width: 480px) {

        #viewAllnew .secondary-featured-next,
        #viewAllnew .secondary-featured-next-new {
            right: 5px !important;
        }

        #viewAllnew .secondary-featured-prev,
        #viewAllnew .secondary-featured-prev-new {
            left: 5px !important;
        }

        #viewAllnew .secondary-featured-next,
        #viewAllnew .secondary-featured-prev,
        #viewAllnew .secondary-featured-next-new,
        #viewAllnew .secondary-featured-prev-new {
            width: 35px !important;
            height: 35px !important;
        }

        #viewAllnew .secondary-featured-next::after,
        #viewAllnew .secondary-featured-prev::after,
        #viewAllnew .secondary-featured-next-new::after,
        #viewAllnew .secondary-featured-prev-new::after {
            font-size: 16px !important;
        }
    }

    /* ===== All Properties Navigation Buttons (viewAllnew-all) ===== */
    #viewAllnew-all {
        position: relative;
    }

    /* Show buttons only on mobile */
    #viewAllnew-all .secondary-featured-next-all,
    #viewAllnew-all .secondary-featured-prev-all,
    #viewAllnew-all .secondary-featured-next,
    #viewAllnew-all .secondary-featured-prev {
        display: none !important;
        width: 45px !important;
        height: 45px !important;
        background: #fff !important;
        border-radius: 50% !important;
        color: #2c3e50 !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15) !important;
        transition: all 0.3s ease !important;
        z-index: 10 !important;
        margin-top: 0 !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        align-items: center !important;
        justify-content: center !important;
        position: absolute !important;
        cursor: pointer !important;
        pointer-events: auto !important;
        opacity: 1 !important;
    }

    @media (max-width: 767px) {

        #viewAllnew-all .secondary-featured-next-all,
        #viewAllnew-all .secondary-featured-prev-all,
        #viewAllnew-all .secondary-featured-next,
        #viewAllnew-all .secondary-featured-prev {
            display: flex !important;
        }
    }

    #viewAllnew-all .secondary-featured-next-all,
    #viewAllnew-all .secondary-featured-next {
        right: 15px !important;
    }

    #viewAllnew-all .secondary-featured-prev-all,
    #viewAllnew-all .secondary-featured-prev {
        left: 15px !important;
    }

    #viewAllnew-all .secondary-featured-next-all:hover,
    #viewAllnew-all .secondary-featured-prev-all:hover,
    #viewAllnew-all .secondary-featured-next:hover,
    #viewAllnew-all .secondary-featured-prev:hover {
        background: #2c3e50 !important;
        color: #fff !important;
        transform: translateY(-50%) scale(1.1) !important;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25) !important;
    }

    #viewAllnew-all .secondary-featured-next-all::after,
    #viewAllnew-all .secondary-featured-prev-all::after,
    #viewAllnew-all .secondary-featured-next::after,
    #viewAllnew-all .secondary-featured-prev::after {
        font-size: 20px !important;
        font-weight: bold !important;
        font-family: 'swiper-icons' !important;
    }

    @media (max-width: 768px) {

        #viewAllnew-all .secondary-featured-next-all,
        #viewAllnew-all .secondary-featured-next {
            right: 10px !important;
        }

        #viewAllnew-all .secondary-featured-prev-all,
        #viewAllnew-all .secondary-featured-prev {
            left: 10px !important;
        }

        #viewAllnew-all .secondary-featured-next-all,
        #viewAllnew-all .secondary-featured-prev-all,
        #viewAllnew-all .secondary-featured-next,
        #viewAllnew-all .secondary-featured-prev {
            width: 40px !important;
            height: 40px !important;
        }

        #viewAllnew-all .secondary-featured-next-all::after,
        #viewAllnew-all .secondary-featured-prev-all::after,
        #viewAllnew-all .secondary-featured-next::after,
        #viewAllnew-all .secondary-featured-prev::after {
            font-size: 18px !important;
        }
    }

    @media (max-width: 480px) {

        #viewAllnew-all .secondary-featured-next-all,
        #viewAllnew-all .secondary-featured-next {
            right: 5px !important;
        }

        #viewAllnew-all .secondary-featured-prev-all,
        #viewAllnew-all .secondary-featured-prev {
            left: 5px !important;
        }

        #viewAllnew-all .secondary-featured-next-all,
        #viewAllnew-all .secondary-featured-prev-all,
        #viewAllnew-all .secondary-featured-next,
        #viewAllnew-all .secondary-featured-prev {
            width: 35px !important;
            height: 35px !important;
        }

        #viewAllnew-all .secondary-featured-next-all::after,
        #viewAllnew-all .secondary-featured-prev-all::after,
        #viewAllnew-all .secondary-featured-next::after,
        #viewAllnew-all .secondary-featured-prev::after {
            font-size: 16px !important;
        }
    }


    margin-top: 74px !important;
    }

    /* Main 
                
                Navigation Buttons - Outside Container, Inside viewAll */
    #viewAll {
        position: relative;
    }

    #viewAll .main-featured-next,
    #viewAll .main-featured-prev {
        width: 45px !important;
        height: 45px !important;
        background: #fff !important;
        border-radius: 50% !important;
        color: #2c3e50 !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15) !important;
        transition: all 0.3s ease !important;
        z-index: 10 !important;
        margin-top: 0 !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        position: absolute !important;
        cursor: pointer !important;
        pointer-events: auto !important;
        opacity: 1 !important;
    }

    #viewAll .main-featured-next {
        right: 15px !important;
    }

    #viewAll .main-featured-prev {
        left: 15px !important;
    }

    #viewAll .main-featured-next:hover,
    #viewAll .main-featured-prev:hover {
        background: #2c3e50 !important;
        color: #fff !important;
        transform: translateY(-50%) scale(1.1) !important;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25) !important;
    }

    #viewAll .main-featured-next::after,
    #viewAll .main-featured-prev::after {
        font-size: 20px !important;
        font-weight: bold !important;
        font-family: 'swiper-icons' !important;
    }

    @media (max-width: 768px) {

        #viewAll .main-featured-next,
        #viewAll .main-featured-prev {
            width: 40px !important;
            height: 40px !important;
        }

        #viewAll .main-featured-next::after,
        #viewAll .main-featured-prev::after {
            font-size: 18px !important;
        }

        #viewAll .main-featured-next {
            right: 10px !important;
        }

        #viewAll .main-featured-prev {
            left: 10px !important;
        }
    }

    @media (max-width: 480px) {

        #viewAll .main-featured-next,
        #viewAll .main-featured-prev {
            width: 35px !important;
            height: 35px !important;
        }

        #viewAll .main-featured-next::after,
        #viewAll .main-featured-prev::after {
            font-size: 16px !important;
        }

        #viewAll .main-featured-next {
            right: 5px !important;
        }

        #viewAll .main-featured-prev {
            left: 5px !important;
        }
    }

    /* Banner Image Loading Fixes */
    .flat-slider .box-img img,
    .flat-slider .image-detail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        min-height: 400px;
        background: #f0f0f0;
    }

    .flat-slider .box-img,
    .flat-slider .image-detail {
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    /* Ensure images are visible even during load */
    .flat-slider img[src] {
        opacity: 1 !important;
    }

    /* Fallback for broken images */
    .flat-slider img:not([src]),
    .flat-slider img[src=""] {
        display: none;
    }

    /* Property Slider Section */
    .flat-property-marquee {
        overflow: hidden;
        position: relative;
        margin-top: 50px;
    }

    .property-marquee-wrapper {
        width: 80%;
        margin: 0 auto;
        padding: 20px 0;
        overflow: hidden;
        position: relative;
    }

    .property-marquee-container {
        position: relative;
        overflow: hidden;
    }

    .property-marquee-swiper {
        width: 100%;
        overflow: hidden;
    }

    .property-marquee-swiper .swiper-slide {
        width: 200px !important;
        height: auto;
        flex-shrink: 0;
    }

    .property-marquee-card {
        width: 200px;
        display: block;
        height: 100%;
    }

    .property-marquee-next,
    .property-marquee-prev {
        width: 45px !important;
        height: 45px !important;
        background: #fff !important;
        border-radius: 50% !important;
        color: #2c3e50 !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15) !important;
        transition: all 0.3s ease !important;
        z-index: 10 !important;
        margin-top: 0 !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .property-marquee-next {
        right: 10px !important;
    }

    .property-marquee-prev {
        left: 10px !important;
    }

    .property-marquee-next:hover,
    .property-marquee-prev:hover {
        background: #2c3e50 !important;
        color: #fff !important;
        transform: translateY(-50%) scale(1.1) !important;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25) !important;
    }

    .property-marquee-next::after,
    .property-marquee-prev::after {
        font-size: 20px !important;
        font-weight: bold !important;
        font-family: 'swiper-icons' !important;
    }

    .property-marquee-card {
        width: 200px;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-decoration: none;
        display: block;
        height: 100%;
    }

    .property-marquee-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .property-card-image {
        width: 100%;
        height: 150px;
        overflow: hidden;
        position: relative;
    }

    .property-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .property-marquee-card:hover .property-card-image img {
        transform: scale(1.1);
    }

    .property-card-name {
        padding: 12px 15px;
        background: #fff;
        font-weight: bold !important;
        color: #000 !important;
    }

    .property-card-name h6 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
        line-height: 1.4;
        text-align: center;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    /* Property Marquee Navigation Buttons */
    .property-marquee-container {
        position: relative;
    }

    .property-marquee-nav {
        position: absolute !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 45px !important;
        height: 45px !important;
        background: #fff !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        z-index: 10 !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15) !important;
        transition: all 0.3s ease !important;
        border: none !important;
        margin-top: 0 !important;
    }

    .property-marquee-nav:hover {
        background: #2c3e50 !important;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25) !important;
        transform: translateY(-50%) scale(1.1) !important;
    }

    .property-marquee-nav::after {
        font-family: 'swiper-icons' !important;
        font-size: 20px !important;
        font-weight: bold !important;
        color: #2c3e50 !important;
        transition: color 0.3s ease !important;
    }

    .property-marquee-nav:hover::after {
        color: #fff !important;
    }

    .property-marquee-prev {
        left: 0 !important;
    }

    .property-marquee-prev::after {
        content: 'prev' !important;
    }

    .property-marquee-next {
        right: 0 !important;
    }

    .property-marquee-next::after {
        content: 'next' !important;
    }

    .main-featured-properties {
        padding-top: 0px !important;
    }

    /* Mobile View - Property Marquee */
    @media (max-width: 768px) {
        .property-marquee-wrapper {
            width: 90%;
        }

        .property-marquee-swiper .swiper-slide {
            width: 180px !important;
        }

        .property-marquee-card {
            width: 180px;
        }

        .property-card-image {
            height: 120px;
        }

        .property-card-name {
            padding: 10px 12px;

        }

        .property-card-name h6 {
            font-size: 15px;
            font-weight: bold !important;
        }

        .property-marquee-next,
        .property-marquee-prev {
            width: 40px !important;
            height: 40px !important;
        }

        .property-marquee-next::after,
        .property-marquee-prev::after {
            font-size: 18px !important;
        }
    }

    @media (max-width: 480px) {
        .property-marquee-wrapper {
            width: 95%;
        }

        .property-marquee-swiper .swiper-slide {
            width: 160px !important;
        }

        .property-marquee-card {
            width: 160px;
        }

        .property-card-image {
            height: 100px;
        }

        .property-card-name h6 {
            font-size: 12px;
        }

        .property-marquee-next,
        .property-marquee-prev {
            width: 35px !important;
            height: 35px !important;
        }

        .property-marquee-next::after,
        .property-marquee-prev::after {
            font-size: 16px !important;
        }
    }

    /* Partner Marquee Section */
    .partner-marquee-container {
        width: 100%;
        overflow: hidden;
        position: relative;
        padding: 20px 0;
    }

    .partner-marquee-track {
        display: flex;
        gap: 30px;
        animation: partnerMarquee 20s linear infinite;
        will-change: transform;
    }

    .partner-marquee-track:hover {
        animation-play-state: paused;
    }

    @keyframes partnerMarquee {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }

    .partner-marquee-item {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 15px;
    }

    .partner-marquee-item img {
        max-width: 100%;
        height: auto;
        filter: grayscale(100%);
        opacity: 0.7;
        transition: all 0.3s ease;
    }

    .partner-marquee-item:hover img {
        filter: grayscale(0%);
        opacity: 1;
        transform: scale(1.1);
    }

    @media (max-width: 768px) {
        .partner-marquee-track {
            gap: 20px;
            animation-duration: 15s;
        }

        .partner-marquee-item {
            padding: 0 10px;
        }

        .partner-marquee-item img {
            width: 120px !important;
            height: 60px !important;
        }
    }

    @media (max-width: 480px) {
        .partner-marquee-track {
            gap: 15px;
            animation-duration: 12s;
        }

        .partner-marquee-item img {
            width: 100px !important;
            height: 50px !important;
        }
    }

    /* Mobile View - Our Location For You Section */
    @media (max-width: 768px) {
        .grid-location {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 10px !important;
            padding: 0 10px !important;
        }

        .grid-location .box-location-v2 {
            width: 100% !important;
            margin: 0 !important;
        }

        .grid-location .box-location-v2 .box-img {
            height: auto !important;
        }

        .grid-location .box-location-v2 .box-img img {
            height: 120px !important;
            width: 100% !important;
            object-fit: cover !important;
        }

        .grid-location .box-location-v2 .content {
            padding: 8px 10px !important;
        }

        .grid-location .box-location-v2 .content h6 {
            font-size: 16px !important;
            font-weight: bold !important;
            margin-bottom: 4px !important;
        }

        .grid-location .box-location-v2 .content p {
            font-size: 12px !important;
            margin-top: 4px !important;
        }

        .flat-location-v2 {
            padding-bottom: 30px !important;
        }

        .flat-location-v2 .box-title {
            margin-bottom: 20px !important;
        }

        .flat-location-v2 .box-title h3 {
            font-size: 24px !important;
        }
    }

    /* Extra Small Mobile */
    @media (max-width: 480px) {
        .grid-location {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 8px !important;
            padding: 0 5px !important;
        }

        .grid-location .box-location-v2 .box-img img {
            height: 100px !important;
        }

        .grid-location .box-location-v2 .content {
            padding: 6px 8px !important;
        }

        .grid-location .box-location-v2 .content h6 {
            font-size: 13px !important;
        }

        .grid-location .box-location-v2 .content p {
            font-size: 11px !important;
        }
    }

    /* ==================== CURSOR & INTERACTIVE STYLES ==================== */
    /* General Cursor Styles for All Interactive Elements */
    a,
    button,
    [role="button"],
    .cursor-pointer {
        cursor: pointer !important;
    }

    /* Property Cards Cursor Styles */
    .homelengo-box,
    .homelengo-box a,
    .homelengo-box button {
        cursor: pointer !important;
    }

    .homelengo-box:hover {
        cursor: pointer !important;
    }

    /* Secondary Featured Properties New - Cursor Styles */
    .secondary-featured-properties-new .homelengo-box,
    .secondary-featured-properties-new .tf-btn,
    .secondary-featured-properties-new .images-group,
    .secondary-featured-properties-new .content-bottom,
    .secondary-featured-properties-new a {
        cursor: pointer !important;
    }

    /* Main Featured Properties - Cursor Styles */
    .main-featured-properties .homelengo-box,
    .main-featured-properties .tf-btn,
    .main-featured-properties .images-group,
    .main-featured-properties a {
        cursor: pointer !important;
    }

    /* Navigation Buttons Cursor */
    .swiper-button-next,
    .swiper-button-prev,
    .main-featured-next,
    .main-featured-prev,
    .secondary-featured-next,
    .secondary-featured-prev {
        cursor: pointer !important;
    }

    /* Location Cards Cursor */
    .box-location-v2 {
        cursor: pointer !important;
    }

    /* Enquiry Button Cursor */
    .enquiry-btn {
        cursor: pointer !important;
    }

    /* Show More Link Cursor */
    .show-more-link {
        cursor: pointer !important;
    }

    /* Blog/Video Card Cursor */
    .flat-blog-item,
    .property-marquee-card,
    .tf-sw-testimonial .swiper-slide {
        cursor: pointer !important;
    }

    /* Footer Links and Interactive Elements - When present */
    footer a,
    footer button,
    .footer-section a {
        cursor: pointer !important;
    }

    /* Hover state for better UX */
    .homelengo-box {
        transition: cursor 0.2s ease !important;
    }
</style>

<section class="flat-slider home-5">
    <div class="wrap-slider-swiper">
        <div dir="ltr" class="swiper-container thumbs-swiper-column">
            <div class="swiper-wrapper">
                <?php if (!empty($banners)): ?>
                    <?php foreach ($banners as $bannerIndex => $banner): ?>
                        <div class="swiper-slide">
                            <div class="box-img">
                                <?php
                                $bannerImage = isset($banner['imageUrl']) ? nb_upgrade_http_image_url($banner['imageUrl']) : '';
                                $fallbackImage = base_url('assets/images/home/house-18.jpg');
                                $isFirstBanner = ($bannerIndex === 0);
                                ?>
                                <img src="<?php echo htmlspecialchars($bannerImage); ?>" alt="Banner" width="1920" height="1080"
                                    loading="<?php echo $isFirstBanner ? 'eager' : 'lazy'; ?>" decoding="async" <?php if ($isFirstBanner): ?>fetchpriority="high" <?php else: ?>fetchpriority="low" <?php endif; ?>
                                    onerror="this.onerror=null; this.src='<?php echo $fallbackImage; ?>';"
                                    onload="this.style.opacity=1;" style="opacity:0; transition:opacity 0.3s;">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback banners if no active banners are available -->
                    <div class="swiper-slide">
                        <div class="box-img">
                            <img src="https://homelengo.vercel.app/images/slider/slider-5.jpg" alt="" width="1920"
                                height="1080" loading="eager" decoding="async" fetchpriority="high">
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="box-img">
                            <img src="https://homelengo.vercel.app/images/slider/slider-5-1.jpg" alt="" width="1920"
                                height="1080" loading="lazy" decoding="async" fetchpriority="low">
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="box-img">
                            <img src="https://homelengo.vercel.app/images/slider/slider-5-2.jpg" alt="" width="1920"
                                height="1080" loading="lazy" decoding="async" fetchpriority="low">
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="box-img">
                            <img src="https://homelengo.vercel.app/images/slider/slider-5-3.jpg" alt="" width="1920"
                                height="1080" loading="lazy" decoding="async" fetchpriority="low">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="box-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="slider-content">
                            <div class="heading">
                                <!-- <h1 class="title-large title text-white wow fadeIn animationtext clip"
                                                data-wow-delay=".2s" data-wow-duration="2000ms">
                                                Your Future Begins With the 
                                                <br>
                                                <span class="tf-text s1 cd-words-wrapper">
                                                    <span class="item-text is-visible">Right Property</span>
                                                </span>
                                            </h1> -->
                                <!-- <p class="subtitle text-white body-2 wow fadeInUp" data-wow-delay=".2s">
                                            Live the Life You Deserve
                                            Experience luxury living with thoughtfully designed homes, designed plots, modern amenities, and peaceful surroundings—all crafted for your comfort and lifestyle</p>
                                        -->
                            </div>
                            <!-- <div class="wrap-search-link">
                                            <div class="categories-list style-2">
                                                <a href="#"><i class="icon icon-house-fill"></i> Plots</a>
                                                <a href="#"><i class="icon icon-villa-fill"></i> Villas</a>
                                            </div>

                                        </div> -->
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="swiper-container thumbs-swiper-column1 swiper-pagination5">
                            <div class="swiper-wrapper">
                                <?php if (!empty($banners)): ?>
                                    <?php foreach ($banners as $thumbIndex => $banner): ?>
                                        <div class="swiper-slide">
                                            <div class="image-detail">
                                                <?php
                                                $thumbImage = isset($banner['imageUrl']) ? nb_upgrade_http_image_url($banner['imageUrl']) : '';
                                                $fallbackThumb = base_url('assets/images/home/house-18.jpg');
                                                ?>
                                                <img src="<?php echo htmlspecialchars($thumbImage); ?>" alt="" width="80"
                                                    height="80" loading="lazy" decoding="async"
                                                    fetchpriority="<?php echo ($thumbIndex === 0) ? 'high' : 'low'; ?>"
                                                    onerror="this.onerror=null; this.src='<?php echo $fallbackThumb; ?>';"
                                                    onload="this.style.opacity=1;" style="opacity:0; transition:opacity 0.3s;">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Fallback thumbnails if no active banners are available -->
                                    <div class="swiper-slide">
                                        <div class="image-detail">
                                            <img src="https://homelengo.vercel.app/images/slider/slider-pagi.jpg"
                                                alt="images">
                                        </div>
                                    </div>
                                    <div class="swiper-slide">
                                        <div class="image-detail">
                                            <img src="https://homelengo.vercel.app/images/slider/slider-pagi2.jpg"
                                                alt="images">
                                        </div>
                                    </div>
                                    <div class="swiper-slide">
                                        <div class="image-detail">
                                            <img src="https://homelengo.vercel.app/images/slider/slider-pagi3.jpg"
                                                alt="images">
                                        </div>
                                    </div>
                                    <div class="swiper-slide">
                                        <div class="image-detail">
                                            <img src="https://homelengo.vercel.app/images/slider/slider-pagi4.jpg"
                                                alt="images">
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="overlay"></div>
</section>
<!-- /Slider -->



<div class="flat-control-search abs">
    <div class="container">
        <div class="flat-tab flat-tab-form">
            <div class="tab-content">
                <div class="tab-pane fade active show" role="tabpanel">
                    <div class="form-sl">
                        <form method="post" id="homeSearchForm" action="<?php echo base_url('our-projects'); ?>">
                            <div class="wd-find-select shadow-3">
                                <div class="inner-group">

                                    <div class="form-group-2 form-style">
                                        <label>Location</label>
                                        <select class="nice-select" name="location" id="locationSelect">
                                            <option value="">Location</option>
                                            <?php foreach ($locations as $location) {
                                                ?>
                                                <option value="<?php echo $location['locationName']; ?>">
                                                    <?php echo $location['locationName']; ?></option>
                                                <?php
                                            } ?>
                                        </select>
                                    </div>

                                    <div class="box-btn-advanced">
                                        <button type="submit" class="tf-btn btn-search primary">Search <i
                                                class="icon icon-search"></i> </button>
                                    </div>

                                </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Filter -->

<!-- Property Marquee Section -->
<section class="flat-section flat-property-marquee pt-0 pb-0" style="background: #f8f9fa; padding: 30px 0;">
    <div class="container-fluid">
        <div class="property-marquee-wrapper">
            <div class="property-marquee-container" style="position: relative;">
                <div dir="ltr" class="swiper property-marquee-swiper">
                    <div class="swiper-wrapper" id="propertyMarqueeTrack">
                        <?php
                        // Get all properties and sort by index or orderValue if available
                        $marqueeProperties = array();
                        if (isset($allProperties['properties']) && !empty($allProperties['properties'])) {
                            $marqueeProperties = $allProperties['properties'];

                            // Sort by index or orderValue if available
                            usort($marqueeProperties, function ($a, $b) {
                                // Check for index first, then orderValue
                                $indexA = isset($a['index']) ? intval($a['index']) : (isset($a['orderValue']) ? intval($a['orderValue']) : 999);
                                $indexB = isset($b['index']) ? intval($b['index']) : (isset($b['orderValue']) ? intval($b['orderValue']) : 999);
                                return $indexA - $indexB;
                            });
                        }
                        ?>

                        <?php if (!empty($marqueeProperties)): ?>
                            <?php
                            // Duplicate slides to ensure seamless loop (need at least 8 slides for 4 per view)
                            $duplicateCount = count($marqueeProperties) < 8 ? 2 : 1;
                            for ($d = 0; $d < $duplicateCount; $d++):
                                foreach ($marqueeProperties as $prop):
                                    $propertyId = isset($prop['id']) ? $prop['id'] : '';
                                    $propertyImage = isset($prop['propertiesMainImage']) ? $prop['propertiesMainImage'] : '';
                                    $propertyName = isset($prop['propertyName']) ? $prop['propertyName'] : 'Property';
                                    ?>
                                    <div class="swiper-slide">
                                        <a href="<?php echo base_url('property-detail/' . (isset($prop['slug']) && !empty($prop['slug']) ? $prop['slug'] : $propertyId)); ?>"
                                            class="property-marquee-card">
                                            <div class="property-card-image">
                                                <?php if (!empty($propertyImage)): ?>
                                                    <img src="<?php echo htmlspecialchars($propertyImage); ?>"
                                                        alt="<?php echo htmlspecialchars($propertyName); ?>" loading="lazy"
                                                        onerror="this.src='<?php echo base_url('assets/images/home/house-18.jpg'); ?>'">
                                                <?php else: ?>
                                                    <img src="<?php echo base_url('assets/images/home/house-18.jpg'); ?>"
                                                        alt="<?php echo htmlspecialchars($propertyName); ?>">
                                                <?php endif; ?>
                                            </div>
                                            <div class="property-card-name">
                                                <h6><?php echo htmlspecialchars($propertyName); ?></h6>
                                            </div>
                                        </a>
                                    </div>
                                <?php
                                endforeach;
                            endfor;
                            ?>
                        <?php endif; ?>
                    </div>
                    <div class="swiper-button-next property-marquee-next"></div>
                    <div class="swiper-button-prev property-marquee-prev"></div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Property Marquee Section -->

<?php
$locations = array_keys($properties_by_location);
$firstArray = array_slice($properties_by_location, 0, 4, true);
$secondArray = array_slice($properties_by_location, 4, null, true);




?>

<!-- Location Marquee Section -->
<section class="flat-section flat-location-marquee pt-0 pb-0" style="background: #f8f9fa; padding: 60px 0;">
    <div class="container">
        <div class="box-title text-center wow fadeInUp">
            <div class="text-subtitle text-primary">Explore Locations</div>
            <h3 class="title mt-4">Our Locations</h3>
        </div>
        <div class="location-marquee-wrapper">
            <div class="location-marquee-container" style="position: relative;">
                <div dir="ltr" class="swiper location-marquee-swiper">
                    <div class="swiper-wrapper" id="locationMarqueeTrack">
                        <?php
                        // Initialize location_images if not set
                        if (!isset($location_images)) {
                            $location_images = array();
                        }

                        // Get all locations with their properties
                        $allLocationKeys = array_keys($properties_by_location);
                        $locationItems = array();

                        foreach ($allLocationKeys as $locationKey):
                            if (!isset($properties_by_location[$locationKey]) || empty($properties_by_location[$locationKey])) {
                                continue;
                            }
                            $locationProperties = $properties_by_location[$locationKey];
                            $firstProperty = $locationProperties[0];

                            // Get location image
                            $locationImage = '';
                            if (isset($location_images[$locationKey]) && !empty($location_images[$locationKey])) {
                                $locationImage = $location_images[$locationKey];
                            } else if (isset($firstProperty['propertiesMainImage']) && !empty($firstProperty['propertiesMainImage'])) {
                                $locationImage = $firstProperty['propertiesMainImage'];
                            } else if (isset($firstProperty['coverImageUrl']) && !empty($firstProperty['coverImageUrl'])) {
                                $locationImage = $firstProperty['coverImageUrl'];
                            }

                            // Add base_url if not already present
                            if (!empty($locationImage) && strpos($locationImage, 'http') !== 0 && strpos($locationImage, '/') !== 0) {
                                $locationImage = base_url($locationImage);
                            } else if (!empty($locationImage) && strpos($locationImage, '/') === 0) {
                                $locationImage = base_url(ltrim($locationImage, '/'));
                            }

                            $locationName = isset($firstProperty['locationInfo']['locationName']) ? $firstProperty['locationInfo']['locationName'] : $locationKey;
                            $propertyCount = count($locationProperties);

                            $locationItems[] = array(
                                'key' => $locationKey,
                                'name' => $locationName,
                                'image' => $locationImage,
                                'count' => $propertyCount
                            );
                        endforeach;

                        // Duplicate slides to ensure seamless loop
                        $duplicateCount = count($locationItems) < 8 ? 2 : 1;
                        for ($d = 0; $d < $duplicateCount; $d++):
                            foreach ($locationItems as $locationItem):
                                ?>
                                <div class="swiper-slide">
                                    <a href="<?php echo base_url('our-projects?location=' . urlencode($locationItem['key'])); ?>"
                                        class="location-marquee-card">
                                        <div class="location-card-image">
                                            <?php if (!empty($locationItem['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($locationItem['image']); ?>"
                                                    alt="<?php echo htmlspecialchars($locationItem['name']); ?>" loading="lazy"
                                                    onerror="this.src='<?php echo base_url('assets/images/home/house-18.jpg'); ?>'">
                                            <?php else: ?>
                                                <img src="<?php echo base_url('assets/images/home/house-18.jpg'); ?>"
                                                    alt="<?php echo htmlspecialchars($locationItem['name']); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="location-card-name">
                                            <h6><?php echo htmlspecialchars($locationItem['name']); ?></h6>
                                        </div>
                                    </a>
                                </div>
                            <?php
                            endforeach;
                        endfor;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Location Marquee Section -->

<style>
    /* Location Marquee Styles */
    .flat-location-marquee {
        overflow: hidden;
        position: relative;
    }

    .location-marquee-wrapper {
        width: 100%;
        margin: 0 auto;
        padding: 20px 0;
        overflow: hidden;
        position: relative;
    }

    .location-marquee-container {
        position: relative;
        overflow: hidden;
    }

    .location-marquee-swiper {
        width: 100%;
        overflow: hidden;
    }

    .location-marquee-swiper .swiper-slide {
        width: 250px !important;
        height: auto;
        flex-shrink: 0;
    }

    .location-marquee-card {
        width: 250px;
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-decoration: none;
        display: block;
        height: 100%;
    }

    .location-marquee-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .location-card-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
        position: relative;
        border-radius: 16px 16px 0 0;
        background: #f0f0f0;
    }

    .location-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
        display: block;
    }

    .location-marquee-card:hover .location-card-image img {
        transform: scale(1.1);
    }

    .location-card-name {
        padding: 20px;
        background: #fff;
        text-align: center;
        border-radius: 0 0 16px 16px;
    }

    .location-card-name h6 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #161e2d;
        line-height: 1.4;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .location-marquee-swiper .swiper-slide {
            width: 200px !important;
        }

        .location-marquee-card {
            width: 200px;
        }

        .location-card-image {
            height: 150px;
        }
    }
</style>

<section class="flat-section flat-location-v2">
    <div class="container">
        <div class="box-title text-center wow fadeInUp">
            <div class="text-subtitle text-primary">Explore Cities</div>
            <h3 class="title mt-4">Our Location For You</h3>
        </div>
        <div class="grid-location wow fadeInUp" data-wow-delay=".2s">
            <?php
            // Initialize location_images if not set
            if (!isset($location_images)) {
                $location_images = array();
            }
            ?>
            <?php foreach ($firstArray as $locationKey => $locationProperties): ?>
                <?php if (!empty($locationProperties) && isset($locationProperties[0])): ?>
                    <a href="<?php echo base_url('our-projects?location=' . urlencode($locationKey)); ?>"
                        class="item-<?php echo $locationKey; ?> box-location-v2 hover-img">
                        <div class="box-img img-style">
                            <?php
                            // Use location's own image first, then fall back to property image
                            $locationImage = '';
                            if (isset($location_images[$locationKey]) && !empty($location_images[$locationKey])) {
                                $locationImage = $location_images[$locationKey];
                            } else if (isset($locationProperties[0]['propertiesMainImage']) && !empty($locationProperties[0]['propertiesMainImage'])) {
                                $locationImage = $locationProperties[0]['propertiesMainImage'];
                            } else if (isset($locationProperties[0]['coverImageUrl']) && !empty($locationProperties[0]['coverImageUrl'])) {
                                $locationImage = $locationProperties[0]['coverImageUrl'];
                            }
                            // Add base_url if not already present
                            if (!empty($locationImage) && strpos($locationImage, 'http') !== 0 && strpos($locationImage, '/') !== 0) {
                                $locationImage = base_url($locationImage);
                            } else if (!empty($locationImage) && strpos($locationImage, '/') === 0) {
                                $locationImage = base_url(ltrim($locationImage, '/'));
                            }
                            $locationName = isset($locationProperties[0]['locationInfo']['locationName']) ? $locationProperties[0]['locationInfo']['locationName'] : $locationKey;
                            ?>
                            <img style="height: 300px; width: 100%; object-fit: cover;" class="lazyload"
                                data-src="<?php echo htmlspecialchars($locationImage); ?>"
                                src="<?php echo htmlspecialchars($dvm_lazy_img_placeholder); ?>" width="400" height="300"
                                loading="lazy" decoding="async"
                                alt="<?php echo htmlspecialchars($locationName, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="content">
                            <h6 class="link"><?php echo htmlspecialchars($locationName); ?></h6>
                            <p class="mt-4 text-variant-1"><?php echo count($locationProperties); ?>
                                <?php echo count($locationProperties) == 1 ? 'Property' : 'Properties'; ?></p>
                        </div>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php $secondIndex = 0; ?>
            <?php foreach ($secondArray as $locationKey => $locationProperties): ?>
                <?php if (!empty($locationProperties) && isset($locationProperties[0])): ?>
                    <?php $itemClass = $secondIndex === 0 ? 'item-5' : ('item-extra-' . $secondIndex); ?>
                    <a href="<?php echo base_url('our-projects?location=' . urlencode($locationKey)); ?>"
                        class="<?php echo $itemClass; ?> box-location-v2 hover-img">
                        <div class="box-img img-style">
                            <?php
                            // Use location's own image first, then fall back to property image
                            $locationImage = '';
                            if (isset($location_images[$locationKey]) && !empty($location_images[$locationKey])) {
                                $locationImage = $location_images[$locationKey];
                            } else if (isset($locationProperties[0]['propertiesMainImage']) && !empty($locationProperties[0]['propertiesMainImage'])) {
                                $locationImage = $locationProperties[0]['propertiesMainImage'];
                            } else if (isset($locationProperties[0]['coverImageUrl']) && !empty($locationProperties[0]['coverImageUrl'])) {
                                $locationImage = $locationProperties[0]['coverImageUrl'];
                            }
                            // Add base_url if not already present
                            if (!empty($locationImage) && strpos($locationImage, 'http') !== 0 && strpos($locationImage, '/') !== 0) {
                                $locationImage = base_url($locationImage);
                            } else if (!empty($locationImage) && strpos($locationImage, '/') === 0) {
                                $locationImage = base_url(ltrim($locationImage, '/'));
                            }
                            $locationName = isset($locationProperties[0]['locationInfo']['locationName']) ? $locationProperties[0]['locationInfo']['locationName'] : $locationKey;
                            ?>
                            <img style="height: 300px; width: 100%; object-fit: cover;" class="lazyload"
                                data-src="<?php echo htmlspecialchars($locationImage); ?>"
                                src="<?php echo htmlspecialchars($dvm_lazy_img_placeholder); ?>" width="400" height="300"
                                loading="lazy" decoding="async"
                                alt="<?php echo htmlspecialchars($locationName, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="content">
                            <h6 class="link"><?php echo htmlspecialchars($locationName); ?></h6>
                            <p class="mt-4 text-variant-1"><?php echo count($locationProperties); ?>
                                <?php echo count($locationProperties) == 1 ? 'Property' : 'Properties'; ?></p>
                        </div>
                    </a>
                <?php endif; ?>
                <?php $secondIndex++; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<!-- Service -->



<section class="flat-section flat-recommended secondary-featured-properties-new">
    <div class="container">
        <div class="box-title text-center wow fadeInUp">
            <div class="text-subtitle text-primary">Featured Properties</div>
            <h3 class="title mt-4">Discover Coimbatore Properties</h3>
        </div>

        <div class="flat-tab-recommended flat-animate-tab wow fadeInUp" data-wow-delay=".2s">
            <div class="tab-content">
                <div class="tab-pane active show" id="viewAllnew" role="tabpanel">
                    <!-- Desktop Grid View -->
                    <?php
                    // Filter properties to show only featured ones (is_featured = 1)
                    $featuredProperties = array();
                    foreach ($properties as $property) {
                        if (isset($property['is_featured']) && $property['is_featured'] == 1) {
                            $featuredProperties[] = $property;
                        } elseif (isset($property['isFeatured']) && $property['isFeatured'] == 1) {
                            $featuredProperties[] = $property;
                        }
                    }
                    ?>
                    <div class="row d-none d-md-flex">
                        <?php foreach ($featuredProperties as $property): ?>
                            <div class="col-xl-4 col-lg-6 col-md-6">
                                <div class="homelengo-box">

                                    <div class="archive-top">
                                        <a href="<?php echo base_url('property-detail/' . ($property['id'] ?? '')); ?>"
                                            class="images-group">
                                            <div class="images-style">
                                                <img class="lazyload adaptive-img-height"
                                                    data-src="<?php echo $property['propertiesMainImage'] ?? ''; ?>"
                                                    src="<?php echo htmlspecialchars($dvm_lazy_img_placeholder); ?>"
                                                    alt="<?php echo htmlspecialchars($property['propertyName'] ?? 'Property', ENT_QUOTES, 'UTF-8'); ?>"
                                                    width="400" height="300" loading="lazy" decoding="async"
                                                    style="width:100%; object-fit:cover;">
                                            </div>

                                            <div class="top">
                                                <ul class="d-flex gap-6">
                                                    <?php
                                                    $categoryName = '';
                                                    if (!empty($property['categoryInfo']['categoryName'])) {
                                                        $categoryName = $property['categoryInfo']['categoryName'];
                                                    } elseif (!empty($property['category'])) {
                                                        $categoryName = $property['category'];
                                                    } elseif (!empty($property['categoryName'])) {
                                                        $categoryName = $property['categoryName'];
                                                    }
                                                    if (!empty($categoryName)): ?>
                                                        <li class="flag-tag primary">
                                                            <?php echo ucfirst($categoryName); ?>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>

                                            <div class="bottom">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path
                                                        d="M10 7C10 7.53043 9.78929 8.03914 9.41421 8.41421C9.03914 8.78929 8.53043 9 8 9C7.46957 9 6.96086 8.78929 6.58579 8.41421C6.21071 8.03914 6 7.53043 6 7C6 6.46957 6.21071 5.96086 6.58579 5.58579C6.96086 5.21071 7.46957 5 8 5C8.53043 5 9.03914 5.21071 9.41421 5.58579C9.78929 5.96086 10 6.46957 10 7Z"
                                                        stroke="white" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M13 7C13 11.7613 8 14.5 8 14.5C8 14.5 3 11.7613 3 7C3 5.67392 3.52678 4.40215 4.46447 3.46447C5.40215 2.52678 6.67392 2 8 2C9.32608 2 10.5979 2.52678 11.5355 3.46447C12.4732 4.40215 13 5.67392 13 7Z"
                                                        stroke="white" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                <?php echo $property['location'] ?? ''; ?>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="archive-bottom">
                                        <div class="content-top">
                                            <h6 class="text-capitalize">
                                                <a href="<?php echo base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : ($property['id'] ?? ''))); ?>"
                                                    class="link">
                                                    <?php echo $property['propertyName'] ?? ''; ?>
                                                </a>
                                            </h6>

                                            <ul class="meta-list">
                                                <li class="item">
                                                    <i class="icon icon-mapPin"></i>
                                                    <span class="text-variant-1">
                                                        <?php echo $property['locationInfo']['locationName'] ?? ''; ?>
                                                    </span>
                                                </li>
                                                <li class="item">
                                                    <i class="icon icon-sqft"></i>
                                                    <span class="text-variant-1">
                                                        <?php echo $property['cityInfo']['cityName'] ?? ''; ?>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                        <?php
                                        $description = $property['desc'] ?? '';
                                        $descriptionLength = mb_strlen($description);
                                        $maxLength = 100;

                                        if ($descriptionLength > $maxLength) {
                                            $truncatedDesc = mb_substr($description, 0, $maxLength);
                                            $propertyDetailUrl = base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : ($property['id'] ?? '')));
                                            echo '<p>' . htmlspecialchars($truncatedDesc) . '... <a href="' . $propertyDetailUrl . '" class="show-more-link" style="color: #007bff; text-decoration: none; font-weight: 500;">show more</a></p>';
                                        } else {
                                            echo '<p>' . htmlspecialchars($description) . '</p>';
                                        }
                                        ?>
                                        <div class="content-bottom">

                                            <div class="d-flex gap-8 align-items-center">
                                                <button class="tf-btn primary w-100 enquiry-btn" type="button"
                                                    data-property-id="<?php echo $property['id'] ?? ''; ?>"
                                                    data-property-name="<?php echo htmlspecialchars($property['propertyName'] ?? ''); ?>"
                                                    data-property-price="<?php echo htmlspecialchars(dvm_property_price_data_attr($property)); ?>"
                                                    data-cover-image="<?php echo htmlspecialchars($property['propertiesMainImage'] ?? ''); ?>">
                                                    <span>Enquiry</span>
                                                </button>
                                            </div>

                                            <h6 class="price">
                                                <?php
                                                $pd = dvm_property_price_display($property);
                                                echo $pd !== '' ? $pd : 'Price on Request';
                                                ?>
                                            </h6>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- End Desktop Grid View -->

                    <!-- Mobile Slider View -->
                    <div class="secondary-featured-properties-mobile d-md-none">
                        <div dir="ltr" class="swiper tf-sw-secondary-featured-properties-new" data-preview="1.3"
                            data-centered-slides="true" data-space="15">
                            <div class="swiper-wrapper">
                                <?php foreach ($featuredProperties as $property): ?>
                                    <div class="swiper-slide">
                                        <div class="homelengo-box">

                                            <div class="archive-top">
                                                <a href="<?php echo base_url('property-detail/' . ($property['id'] ?? '')); ?>"
                                                    class="images-group">
                                                    <div class="images-style">
                                                        <img class="lazyload adaptive-img-height"
                                                            data-src="<?php echo $property['propertiesMainImage'] ?? ''; ?>"
                                                            src="<?php echo htmlspecialchars($dvm_lazy_img_placeholder); ?>"
                                                            alt="<?php echo htmlspecialchars($property['propertyName'] ?? 'Property', ENT_QUOTES, 'UTF-8'); ?>"
                                                            width="400" height="300" loading="lazy" decoding="async"
                                                            style="width:100%; object-fit:cover;">
                                                    </div>

                                                    <div class="top">
                                                        <ul class="d-flex gap-6">
                                                            <?php
                                                            $categoryName = '';
                                                            if (!empty($property['categoryInfo']['categoryName'])) {
                                                                $categoryName = $property['categoryInfo']['categoryName'];
                                                            } elseif (!empty($property['category'])) {
                                                                $categoryName = $property['category'];
                                                            } elseif (!empty($property['categoryName'])) {
                                                                $categoryName = $property['categoryName'];
                                                            }
                                                            if (!empty($categoryName)): ?>
                                                                <li class="flag-tag primary">
                                                                    <?php echo ucfirst($categoryName); ?>
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>

                                                    <div class="bottom">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                            <path
                                                                d="M10 7C10 7.53043 9.78929 8.03914 9.41421 8.41421C9.03914 8.78929 8.53043 9 8 9C7.46957 9 6.96086 8.78929 6.58579 8.41421C6.21071 8.03914 6 7.53043 6 7C6 6.46957 6.21071 5.96086 6.58579 5.58579C6.96086 5.21071 7.46957 5 8 5C8.53043 5 9.03914 5.21071 9.41421 5.58579C9.78929 5.96086 10 6.46957 10 7Z"
                                                                stroke="white" stroke-width="1.5" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                            <path
                                                                d="M13 7C13 11.7613 8 14.5 8 14.5C8 14.5 3 11.7613 3 7C3 5.67392 3.52678 4.40215 4.46447 3.46447C5.40215 2.52678 6.67392 2 8 2C9.32608 2 10.5979 2.52678 11.5355 3.46447C12.4732 4.40215 13 5.67392 13 7Z"
                                                                stroke="white" stroke-width="1.5" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                        <?php echo $property['location'] ?? ''; ?>
                                                    </div>
                                                </a>
                                            </div>

                                            <div class="archive-bottom">
                                                <div class="content-top">
                                                    <h6 class="text-capitalize">
                                                        <a href="<?php echo base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : ($property['id'] ?? ''))); ?>"
                                                            class="link">
                                                            <?php echo $property['propertyName'] ?? ''; ?>
                                                        </a>
                                                    </h6>

                                                    <ul class="meta-list">
                                                        <li class="item">
                                                            <i class="icon icon-mapPin"></i>
                                                            <span class="text-variant-1">
                                                                <?php echo $property['locationInfo']['locationName'] ?? ''; ?>
                                                            </span>
                                                        </li>
                                                        <li class="item">
                                                            <i class="icon icon-sqft"></i>
                                                            <span class="text-variant-1">
                                                                <?php echo $property['cityInfo']['cityName'] ?? ''; ?>
                                                            </span>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <?php
                                                $description = $property['desc'] ?? '';
                                                $descriptionLength = mb_strlen($description);
                                                $maxLength = 100;

                                                if ($descriptionLength > $maxLength) {
                                                    $truncatedDesc = mb_substr($description, 0, $maxLength);
                                                    $propertyDetailUrl = base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : ($property['id'] ?? '')));
                                                    echo '<p>' . htmlspecialchars($truncatedDesc) . '... <a href="' . $propertyDetailUrl . '" class="show-more-link" style="color: #007bff; text-decoration: none; font-weight: 500;">show more</a></p>';
                                                } else {
                                                    echo '<p>' . htmlspecialchars($description) . '</p>';
                                                }
                                                ?>
                                                <div class="content-bottom">
                                                    <div class="d-flex gap-8 align-items-center">
                                                        <button class="tf-btn primary w-100 enquiry-btn" type="button"
                                                            data-property-id="<?php echo $property['id'] ?? ''; ?>"
                                                            data-property-name="<?php echo htmlspecialchars($property['propertyName'] ?? ''); ?>"
                                                            data-property-price="<?php echo htmlspecialchars(dvm_property_price_data_attr($property)); ?>"
                                                            data-cover-image="<?php echo htmlspecialchars($property['propertiesMainImage'] ?? ''); ?>">
                                                            <span>Enquiry</span>
                                                        </button>
                                                    </div>

                                                    <h6 class="price">
                                                        <?php
                                                        $pd = dvm_property_price_display($property);
                                                        echo $pd !== '' ? $pd : 'Price on Request';
                                                        ?>
                                                    </h6>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <!-- End Mobile Slider View -->

                    <!-- Navigation Buttons - Outside Container, Inside viewAllnew -->
                    <div class="swiper-button-next secondary-featured-next-new"></div>
                    <div class="swiper-button-prev secondary-featured-prev-new"></div>

                    <div class="text-center">
                        <a href="<?php echo base_url('our-projects'); ?>"
                            class="tf-btn btn-view primary size-1 hover-btn-view">
                            View All Properties <span class="icon icon-arrow-right2"></span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>


<section class="flat-section flat-recommended secondary-featured-properties">
    <div class="container">
        <div class="box-title text-center wow fadeInUp">
            <div class="text-subtitle text-primary">All Properties</div>
            <h3 class="title mt-4">Discover Coimbatore Properties Finest Properties for Your Dream Home</h3>
        </div>

        <div class="flat-tab-recommended flat-animate-tab wow fadeInUp" data-wow-delay=".2s">
            <div class="tab-content">
                <div class="tab-pane active show" id="viewAllnew-all" role="tabpanel">
                    <!-- Desktop Grid View -->
                    <div class="row d-none d-md-flex">
                        <?php foreach ($properties as $property): ?>
                            <div class="col-xl-4 col-lg-6 col-md-6">
                                <div class="homelengo-box">

                                    <div class="archive-top">
                                        <a href="<?php echo base_url('property-detail/' . ($property['id'] ?? '')); ?>"
                                            class="images-group">
                                            <div class="images-style">
                                                <img class="lazyload adaptive-img-height"
                                                    data-src="<?php echo $property['propertiesMainImage'] ?? ''; ?>"
                                                    src="<?php echo htmlspecialchars($dvm_lazy_img_placeholder); ?>"
                                                    alt="<?php echo htmlspecialchars($property['propertyName'] ?? 'Property', ENT_QUOTES, 'UTF-8'); ?>"
                                                    width="400" height="300" loading="lazy" decoding="async"
                                                    style="width:100%; object-fit:cover;">
                                            </div>

                                            <div class="top">
                                                <ul class="d-flex gap-6">
                                                    <?php
                                                    $categoryName = '';
                                                    if (!empty($property['categoryInfo']['categoryName'])) {
                                                        $categoryName = $property['categoryInfo']['categoryName'];
                                                    } elseif (!empty($property['category'])) {
                                                        $categoryName = $property['category'];
                                                    } elseif (!empty($property['categoryName'])) {
                                                        $categoryName = $property['categoryName'];
                                                    }
                                                    if (!empty($categoryName)): ?>
                                                        <li class="flag-tag primary">
                                                            <?php echo ucfirst($categoryName); ?>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>

                                            <div class="bottom">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path
                                                        d="M10 7C10 7.53043 9.78929 8.03914 9.41421 8.41421C9.03914 8.78929 8.53043 9 8 9C7.46957 9 6.96086 8.78929 6.58579 8.41421C6.21071 8.03914 6 7.53043 6 7C6 6.46957 6.21071 5.96086 6.58579 5.58579C6.96086 5.21071 7.46957 5 8 5C8.53043 5 9.03914 5.21071 9.41421 5.58579C9.78929 5.96086 10 6.46957 10 7Z"
                                                        stroke="white" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M13 7C13 11.7613 8 14.5 8 14.5C8 14.5 3 11.7613 3 7C3 5.67392 3.52678 4.40215 4.46447 3.46447C5.40215 2.52678 6.67392 2 8 2C9.32608 2 10.5979 2.52678 11.5355 3.46447C12.4732 4.40215 13 5.67392 13 7Z"
                                                        stroke="white" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                <?php echo $property['location'] ?? ''; ?>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="archive-bottom">
                                        <div class="content-top">
                                            <h6 class="text-capitalize">
                                                <a href="<?php echo base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : ($property['id'] ?? ''))); ?>"
                                                    class="link">
                                                    <?php echo $property['propertyName'] ?? ''; ?>
                                                </a>
                                            </h6>

                                            <ul class="meta-list">
                                                <li class="item">
                                                    <i class="icon icon-mapPin"></i>
                                                    <span class="text-variant-1">
                                                        <?php echo $property['locationInfo']['locationName'] ?? ''; ?>
                                                    </span>
                                                </li>
                                                <li class="item">
                                                    <i class="icon icon-sqft"></i>
                                                    <span class="text-variant-1">
                                                        <?php echo $property['cityInfo']['cityName'] ?? ''; ?>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                        <?php
                                        $description = $property['desc'] ?? '';
                                        $descriptionLength = mb_strlen($description);
                                        $maxLength = 100;

                                        if ($descriptionLength > $maxLength) {
                                            $truncatedDesc = mb_substr($description, 0, $maxLength);
                                            $propertyDetailUrl = base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : ($property['id'] ?? '')));
                                            echo '<p>' . htmlspecialchars($truncatedDesc) . '... <a href="' . $propertyDetailUrl . '" class="show-more-link" style="color: #007bff; text-decoration: none; font-weight: 500;">show more</a></p>';
                                        } else {
                                            echo '<p>' . htmlspecialchars($description) . '</p>';
                                        }
                                        ?>
                                        <div class="content-bottom">

                                            <div class="d-flex gap-8 align-items-center">
                                                <button class="tf-btn primary w-100 enquiry-btn" type="button"
                                                    data-property-id="<?php echo $property['id'] ?? ''; ?>"
                                                    data-property-name="<?php echo htmlspecialchars($property['propertyName'] ?? ''); ?>"
                                                    data-property-price="<?php echo htmlspecialchars(dvm_property_price_data_attr($property)); ?>"
                                                    data-cover-image="<?php echo htmlspecialchars($property['propertiesMainImage'] ?? ''); ?>">
                                                    <span>Enquiry</span>
                                                </button>
                                            </div>

                                            <h6 class="price">
                                                <?php
                                                $pd = dvm_property_price_display($property);
                                                echo $pd !== '' ? $pd : 'Price on Request';
                                                ?>
                                            </h6>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- End Desktop Grid View -->

                    <!-- Mobile Slider View -->
                    <div class="secondary-featured-properties-mobile d-md-none">
                        <div dir="ltr" class="swiper tf-sw-secondary-featured-properties" data-preview="1.3"
                            data-centered-slides="true" data-space="15">
                            <div class="swiper-wrapper">
                                <?php foreach ($properties as $property): ?>
                                    <div class="swiper-slide">
                                        <div class="homelengo-box">

                                            <div class="archive-top">
                                                <a href="<?php echo base_url('property-detail/' . ($property['id'] ?? '')); ?>"
                                                    class="images-group">
                                                    <div class="images-style">
                                                        <img class="lazyload adaptive-img-height"
                                                            data-src="<?php echo $property['propertiesMainImage'] ?? ''; ?>"
                                                            src="<?php echo htmlspecialchars($dvm_lazy_img_placeholder); ?>"
                                                            alt="<?php echo htmlspecialchars($property['propertyName'] ?? 'Property', ENT_QUOTES, 'UTF-8'); ?>"
                                                            width="400" height="300" loading="lazy" decoding="async"
                                                            style="width:100%; object-fit:cover;">
                                                    </div>

                                                    <div class="top">
                                                        <ul class="d-flex gap-6">
                                                            <?php
                                                            $categoryName = '';
                                                            if (!empty($property['categoryInfo']['categoryName'])) {
                                                                $categoryName = $property['categoryInfo']['categoryName'];
                                                            } elseif (!empty($property['category'])) {
                                                                $categoryName = $property['category'];
                                                            } elseif (!empty($property['categoryName'])) {
                                                                $categoryName = $property['categoryName'];
                                                            }
                                                            if (!empty($categoryName)): ?>
                                                                <li class="flag-tag primary">
                                                                    <?php echo ucfirst($categoryName); ?>
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>

                                                    <div class="bottom">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                            <path
                                                                d="M10 7C10 7.53043 9.78929 8.03914 9.41421 8.41421C9.03914 8.78929 8.53043 9 8 9C7.46957 9 6.96086 8.78929 6.58579 8.41421C6.21071 8.03914 6 7.53043 6 7C6 6.46957 6.21071 5.96086 6.58579 5.58579C6.96086 5.21071 7.46957 5 8 5C8.53043 5 9.03914 5.21071 9.41421 5.58579C9.78929 5.96086 10 6.46957 10 7Z"
                                                                stroke="white" stroke-width="1.5" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                            <path
                                                                d="M13 7C13 11.7613 8 14.5 8 14.5C8 14.5 3 11.7613 3 7C3 5.67392 3.52678 4.40215 4.46447 3.46447C5.40215 2.52678 6.67392 2 8 2C9.32608 2 10.5979 2.52678 11.5355 3.46447C12.4732 4.40215 13 5.67392 13 7Z"
                                                                stroke="white" stroke-width="1.5" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                        <?php echo $property['location'] ?? ''; ?>
                                                    </div>
                                                </a>
                                            </div>

                                            <div class="archive-bottom">
                                                <div class="content-top">
                                                    <h6 class="text-capitalize">
                                                        <a href="<?php echo base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : ($property['id'] ?? ''))); ?>"
                                                            class="link">
                                                            <?php echo $property['propertyName'] ?? ''; ?>
                                                        </a>
                                                    </h6>

                                                    <ul class="meta-list">
                                                        <li class="item">
                                                            <i class="icon icon-mapPin"></i>
                                                            <span class="text-variant-1">
                                                                <?php echo $property['locationInfo']['locationName'] ?? ''; ?>
                                                            </span>
                                                        </li>
                                                        <li class="item">
                                                            <i class="icon icon-sqft"></i>
                                                            <span class="text-variant-1">
                                                                <?php echo $property['cityInfo']['cityName'] ?? ''; ?>
                                                            </span>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <?php
                                                $description = $property['desc'] ?? '';
                                                $descriptionLength = mb_strlen($description);
                                                $maxLength = 100;

                                                if ($descriptionLength > $maxLength) {
                                                    $truncatedDesc = mb_substr($description, 0, $maxLength);
                                                    $propertyDetailUrl = base_url('property-detail/' . (isset($property['slug']) && !empty($property['slug']) ? $property['slug'] : ($property['id'] ?? '')));
                                                    echo '<p>' . htmlspecialchars($truncatedDesc) . '... <a href="' . $propertyDetailUrl . '" class="show-more-link" style="color: #007bff; text-decoration: none; font-weight: 500;">show more</a></p>';
                                                } else {
                                                    echo '<p>' . htmlspecialchars($description) . '</p>';
                                                }
                                                ?>
                                                <div class="content-bottom">
                                                    <div class="d-flex gap-8 align-items-center">
                                                        <button class="tf-btn primary w-100 enquiry-btn" type="button"
                                                            data-property-id="<?php echo $property['id'] ?? ''; ?>"
                                                            data-property-name="<?php echo htmlspecialchars($property['propertyName'] ?? ''); ?>"
                                                            data-property-price="<?php echo htmlspecialchars(dvm_property_price_data_attr($property)); ?>"
                                                            data-cover-image="<?php echo htmlspecialchars($property['propertiesMainImage'] ?? ''); ?>">
                                                            <span>Enquiry</span>
                                                        </button>
                                                    </div>

                                                    <h6 class="price">
                                                        <?php
                                                        $pd = dvm_property_price_display($property);
                                                        echo $pd !== '' ? $pd : 'Price on Request';
                                                        ?>
                                                    </h6>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <!-- End Mobile Slider View -->

                    <!-- Navigation Buttons - Outside Container, Inside viewAllnew-all -->
                    <div class="swiper-button-next secondary-featured-next"></div>
                    <div class="swiper-button-prev secondary-featured-prev"></div>

                    <div class="text-center">
                        <a href="<?php echo base_url('our-projects'); ?>"
                            class="tf-btn btn-view primary size-1 hover-btn-view">
                            View All Properties <span class="icon icon-arrow-right2"></span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>


<!-- End Property  -->

<!-- Video Section -->
<section class="flat-section flat-video-reels pt-0">
    <div class="container">
        <div class="box-title text-center wow fadeInUp">
            <div class="text-subtitle text-primary">Explore Our Properties</div>
            <h3 class="title mt-4">Property Videos</h3>
        </div>

        <?php
        // Initialize videos array for this section
        $sectionVideos = array();

        // Get videos from videos collection - all videos are server-uploaded URLs
        if (isset($videos) && !empty($videos) && is_array($videos)) {
            foreach ($videos as $video) {

                // Prepare video data - all videos are YouTube URLs now
                $videoUrl = isset($video['videoUrl']) ? $video['videoUrl'] : '';
                $isYouTube = isset($video['isYouTube']) ? $video['isYouTube'] : (!empty($videoUrl) && (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false));

                // Generate embed URL if YouTube
                $embedUrl = '';
                if ($isYouTube && !empty($videoUrl)) {
                    $patterns = array(
                        '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/',
                        '/youtube\.com\/.*[?&]v=([^&\n?#]+)/'
                    );
                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern, $videoUrl, $matches)) {
                            $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                            break;
                        }
                    }
                }

                $videoData = array(
                    'id' => isset($video['id']) ? $video['id'] : '',
                    'videoUrl' => $videoUrl,
                    'videoLink' => $videoUrl, // Add videoLink for compatibility
                    'embedUrl' => !empty($video['embedUrl']) ? $video['embedUrl'] : $embedUrl,
                    'thumbnail' => isset($video['thumbnail']) ? $video['thumbnail'] : '',
                    'desc' => isset($video['title']) ? $video['title'] : (isset($video['desc']) ? $video['desc'] : ''),
                    'title' => isset($video['title']) ? $video['title'] : '',
                    'index' => isset($video['index_no']) ? intval($video['index_no']) : (isset($video['index']) ? intval($video['index']) : 999),
                    'isYouTube' => $isYouTube // Add isYouTube flag
                );

                // Only add if videoUrl exists
                if (!empty($videoData['videoUrl'])) {
                    $sectionVideos[] = $videoData;
                }
            }

            // Videos are already sorted by index in the data function
        }
        ?>

        <?php if (!empty($sectionVideos)): ?>
            <style>
                /* Video Reels Section Styles - Square Design */
                .flat-video-reels .video-item {
                    position: relative;
                    border-radius: 12px;
                    overflow: hidden;
                    background: #000;
                    cursor: pointer;
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                }

                .flat-video-reels .video-item:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                }

                .flat-video-reels .video-wrapper-reel {
                    position: relative;
                    padding-bottom: 56.25%;
                    /* 16:9 aspect ratio - YouTube style */
                    height: 0;
                    overflow: hidden;
                    background: #000;
                }

                .flat-video-reels .video-wrapper-reel img,
                .flat-video-reels .video-wrapper-reel video,
                .flat-video-reels .video-wrapper-reel iframe {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    border: none;
                    z-index: 1;
                }

                .flat-video-reels .video-wrapper-reel iframe {
                    z-index: 3;
                    background: #000;
                }

                .flat-video-reels .video-wrapper-reel video {
                    z-index: 2;
                    background: #000;
                }

                .flat-video-reels .video-wrapper-reel video,
                .flat-video-reels .video-wrapper-reel iframe {
                    display: none;
                }

                .flat-video-reels .video-wrapper-reel.playing video,
                .flat-video-reels .video-wrapper-reel.playing iframe {
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                    z-index: 10 !important;
                }

                .flat-video-reels .video-wrapper-reel.playing .video-info {
                    z-index: 1 !important;
                }

                .flat-video-reels .video-wrapper-reel.playing img {
                    display: none;
                }

                .flat-video-reels .play-btn {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 50px;
                    height: 50px;
                    background: rgba(255, 255, 255, 0.9);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 4;
                    transition: all 0.3s ease;
                    pointer-events: none;
                }

                .flat-video-reels .play-btn svg {
                    width: 20px;
                    height: 20px;
                    margin-left: 3px;
                }

                .flat-video-reels .video-wrapper-reel.playing .play-btn {
                    display: none;
                }

                /* Stop Button for Video Reels - Always Visible */
                .flat-video-reels .stop-button-video-reel {
                    display: flex !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                    position: absolute;
                    bottom: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    margin: 0;
                    padding: 10px 20px;
                    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                    color: #fff;
                    border: 2px solid #dc3545;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 13px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    width: auto;
                    min-width: 100px;
                    text-align: center;
                    z-index: 15;
                    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
                    align-items: center;
                    justify-content: center;
                    gap: 6px;
                }

                .flat-video-reels .stop-button-video-reel svg {
                    width: 14px;
                    height: 14px;
                    flex-shrink: 0;
                    display: block;
                    fill: currentColor;
                }

                .flat-video-reels .stop-button-video-reel span {
                    display: inline-block;
                    line-height: 1;
                }

                .flat-video-reels .stop-button-video-reel:hover {
                    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
                    transform: translateX(-50%) translateY(-2px);
                    box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
                    border-color: #c82333;
                }

                .flat-video-reels .stop-button-video-reel:active {
                    transform: translateX(-50%) translateY(0);
                    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
                }

                .flat-video-reels .video-wrapper-reel:not(.playing) .stop-button-video-reel {
                    display: none !important;
                }

                @media (max-width: 767px) {
                    .flat-video-reels .stop-button-video-reel {
                        bottom: 15px;
                        padding: 12px 18px;
                        font-size: 14px;
                        min-width: 110px;
                    }

                    .flat-video-reels .stop-button-video-reel svg {
                        width: 16px;
                        height: 16px;
                    }
                }

                .flat-video-reels .video-info {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
                    padding: 15px;
                    color: #fff;
                    z-index: 5;
                    pointer-events: none;
                }

                .flat-video-reels .video-wrapper-reel.playing .video-info {
                    opacity: 0.7;
                    transition: opacity 0.3s ease;
                }

                .flat-video-reels .video-info h6 {
                    color: #fff;
                    font-size: 14px;
                    margin-bottom: 5px;
                    font-weight: 600;
                    line-height: 1.3;
                }

                .flat-video-reels .video-info .meta {
                    font-size: 12px;
                    opacity: 0.9;
                }

                /* Desktop Grid - 4 columns (YouTube style) */
                .flat-video-reels .video-grid-desktop {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 20px;
                }

                /* Desktop Slider Container */
                .flat-video-reels .video-slider-desktop {
                    position: relative;
                    padding: 0 50px;
                }

                .flat-video-reels .video-slider-desktop .swiper-button-next,
                .flat-video-reels .video-slider-desktop .swiper-button-prev {
                    width: 45px;
                    height: 45px;
                    background: #fff;
                    border-radius: 50%;
                    color: #2c3e50;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
                    transition: all 0.3s ease;
                    z-index: 10;
                    margin-top: 0;
                    top: 50%;
                    transform: translateY(-50%);
                }

                .flat-video-reels .video-slider-desktop .swiper-button-next:hover,
                .flat-video-reels .video-slider-desktop .swiper-button-prev:hover {
                    background: #2c3e50;
                    color: #fff;
                    transform: translateY(-50%) scale(1.1);
                    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
                }

                .flat-video-reels .video-slider-desktop .swiper-button-next::after,
                .flat-video-reels .video-slider-desktop .swiper-button-prev::after {
                    font-size: 20px;
                    font-weight: bold;
                }

                .flat-video-reels .video-slider-desktop .swiper-button-next {
                    right: 0;
                }

                .flat-video-reels .video-slider-desktop .swiper-button-prev {
                    left: 0;
                }

                /* Mobile Slider Navigation Buttons */
                .flat-video-reels .video-slider-mobile {
                    position: relative;
                    padding: 0 0px;
                }

                .flat-video-reels .video-slider-mobile .swiper-button-next,
                .flat-video-reels .video-slider-mobile .swiper-button-prev {
                    width: 40px;
                    height: 40px;
                    background: #fff;
                    border-radius: 50%;
                    color: #2c3e50;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
                    transition: all 0.3s ease;
                    z-index: 10;
                    margin-top: 0;
                    top: 50%;
                    transform: translateY(-50%);
                }

                .flat-video-reels .video-slider-mobile .swiper-button-next:hover,
                .flat-video-reels .video-slider-mobile .swiper-button-prev:hover {
                    background: #2c3e50;
                    color: #fff;
                    transform: translateY(-50%) scale(1.1);
                    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
                }

                .flat-video-reels .video-slider-mobile .swiper-button-next::after,
                .flat-video-reels .video-slider-mobile .swiper-button-prev::after {
                    font-size: 18px;
                    font-weight: bold;
                }

                .flat-video-reels .video-slider-mobile .swiper-button-next {
                    right: 0;
                }

                .flat-video-reels .video-slider-mobile .swiper-button-prev {
                    left: 0;
                }

                .flat-video-reels .video-slider-desktop .swiper-slide {
                    height: auto;
                }

                /* Mobile Slider */
                @media (max-width: 767px) {

                    .flat-video-reels .video-grid-desktop,
                    .flat-video-reels .video-slider-desktop {
                        display: none !important;
                    }

                    .flat-video-reels .video-slider-mobile {
                        display: block !important;
                    }

                    .flat-video-reels .video-wrapper-reel {
                        padding-bottom: 56.25%;
                        /* 16:9 aspect ratio - YouTube style */
                    }

                    .flat-video-reels .video-slider-mobile {
                        padding: 0 0px;
                    }

                    .flat-video-reels .video-slider-mobile .swiper-button-next,
                    .flat-video-reels .video-slider-mobile .swiper-button-prev {
                        width: 35px;
                        height: 35px;
                    }

                    .flat-video-reels .video-slider-mobile .swiper-button-next::after,
                    .flat-video-reels .video-slider-mobile .swiper-button-prev::after {
                        font-size: 16px;
                    }
                }

                /* Desktop - Hide Mobile Slider */
                @media (min-width: 768px) {
                    .flat-video-reels .video-slider-mobile {
                        display: none !important;
                    }
                }
            </style>

            <?php
            $videoCount = count($sectionVideos);
            $useSlider = $videoCount > 4;
            ?>

            <?php if ($useSlider): ?>
                <!-- Desktop Slider View - More than 4 items -->
                <div class="video-slider-desktop wow fadeInUp" data-wow-delay=".2s">
                    <div dir="ltr" class="swiper tf-sw-video-reels-desktop" data-preview="4" data-tablet="3" data-mobile-sm="2"
                        data-mobile="1" data-space="20" data-space-md="20" data-space-lg="20" data-centered="false"
                        data-loop="true" data-autoplay="false" data-autoplay-delay="3000">
                        <div class="swiper-wrapper">
                            <?php foreach ($sectionVideos as $index => $video): ?>
                                <div class="swiper-slide">
                                    <div class="video-item" data-video-id="<?php echo htmlspecialchars($video['id']); ?>"
                                        data-video-url="<?php echo htmlspecialchars($video['videoLink']); ?>"
                                        data-is-youtube="<?php echo $video['isYouTube'] ? 'true' : 'false'; ?>">
                                        <div class="video-wrapper-reel" id="reelSliderWrapper-<?php echo $index; ?>">
                                            <?php if (!empty($video['thumbnail'])): ?>
                                                <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>"
                                                    alt="<?php echo htmlspecialchars($video['desc']); ?>" loading="lazy">
                                            <?php else: ?>
                                                <div
                                                    style="background: #1a1a1a; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #666;">
                                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M8 5v14l11-7z" />
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($video['isYouTube'] && !empty($video['embedUrl'])): ?>
                                                <iframe src="" data-embed-url="<?php echo htmlspecialchars($video['embedUrl']); ?>"
                                                    frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen
                                                    style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 3; background: #000;">
                                                </iframe>
                                            <?php else: ?>
                                                <video controls playsinline style="display: none;">
                                                    <source src="<?php echo htmlspecialchars($video['videoLink']); ?>" type="video/mp4">
                                                </video>
                                            <?php endif; ?>
                                            <div class="play-btn">
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M8 5v14l11-7z" />
                                                </svg>
                                            </div>
                                            <button type="button" class="stop-button-video-reel"
                                                data-video-wrapper="reelWrapper-<?php echo $index; ?>">
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M6 6h12v12H6z" />
                                                </svg>
                                                <span>Stop</span>
                                            </button>
                                            <div class="video-info">
                                                <h6><?php echo !empty($video['desc']) ? htmlspecialchars(mb_substr($video['desc'], 0, 50)) . (mb_strlen($video['desc']) > 50 ? '...' : '') : 'Property Video'; ?>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Desktop Grid View - 4 columns or less -->
                <div class="video-grid-desktop wow fadeInUp" data-wow-delay=".2s">
                    <?php foreach ($sectionVideos as $index => $video): ?>
                        <div class="video-item" data-video-id="<?php echo htmlspecialchars($video['id']); ?>"
                            data-video-url="<?php echo htmlspecialchars($video['videoLink']); ?>"
                            data-is-youtube="<?php echo $video['isYouTube'] ? 'true' : 'false'; ?>">
                            <div class="video-wrapper-reel" id="reelWrapper-<?php echo $index; ?>">
                                <?php if (!empty($video['thumbnail'])): ?>
                                    <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>"
                                        alt="<?php echo htmlspecialchars($video['desc']); ?>" loading="lazy">
                                <?php else: ?>
                                    <div
                                        style="background: #1a1a1a; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #666;">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <?php if ($video['isYouTube']): ?>
                                    <iframe src="" data-embed-url="<?php echo htmlspecialchars($video['embedUrl']); ?>" frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen
                                        style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 3; background: #000;">
                                    </iframe>
                                <?php else: ?>
                                    <video controls playsinline style="display: none;">
                                        <source src="<?php echo htmlspecialchars($video['videoLink']); ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                                <div class="play-btn">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                                <button type="button" class="stop-button-video-reel"
                                    data-video-wrapper="reelWrapper-<?php echo $index; ?>">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 6h12v12H6z" />
                                    </svg>
                                    <span>Stop</span>
                                </button>
                                <div class="video-info">
                                    <h6><?php echo !empty($video['desc']) ? htmlspecialchars(mb_substr($video['desc'], 0, 50)) . (mb_strlen($video['desc']) > 50 ? '...' : '') : 'Property Video'; ?>
                                    </h6>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Mobile Slider View -->
            <div class="video-slider-mobile" style="display: none;">
                <div dir="ltr" class="swiper tf-sw-video-reels" data-preview="1" data-tablet="2" data-mobile-sm="1"
                    data-mobile="1" data-space="15" data-space-md="20" data-space-lg="20" data-centered="false"
                    data-loop="false">
                    <div class="swiper-wrapper wow fadeInUp" data-wow-delay=".2s">
                        <?php foreach ($sectionVideos as $index => $video): ?>
                            <div class="swiper-slide">
                                <div class="video-item" data-video-id="<?php echo htmlspecialchars($video['id']); ?>"
                                    data-video-url="<?php echo htmlspecialchars($video['videoLink']); ?>"
                                    data-is-youtube="<?php echo $video['isYouTube'] ? 'true' : 'false'; ?>">
                                    <div class="video-wrapper-reel" id="reelMobileWrapper-<?php echo $index; ?>">
                                        <?php if (!empty($video['thumbnail'])): ?>
                                            <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>"
                                                alt="<?php echo htmlspecialchars($video['desc']); ?>" loading="lazy">
                                        <?php else: ?>
                                            <div
                                                style="background: #1a1a1a; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #666;">
                                                <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M8 5v14l11-7z" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($video['isYouTube'] && !empty($video['embedUrl'])): ?>
                                            <iframe src="" data-embed-url="<?php echo htmlspecialchars($video['embedUrl']); ?>"
                                                frameborder="0"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowfullscreen
                                                style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 3; background: #000;">
                                            </iframe>
                                        <?php else: ?>
                                            <video controls playsinline style="display: none;">
                                                <source src="<?php echo htmlspecialchars($video['videoLink']); ?>" type="video/mp4">
                                            </video>
                                        <?php endif; ?>
                                        <div class="play-btn">
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                        <button type="button" class="stop-button-video-reel"
                                            data-video-wrapper="reelMobileWrapper-<?php echo $index; ?>">
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M6 6h12v12H6z" />
                                            </svg>
                                            <span>Stop</span>
                                        </button>
                                        <div class="video-info">
                                            <h6><?php echo !empty($video['desc']) ? htmlspecialchars(mb_substr($video['desc'], 0, 40)) . (mb_strlen($video['desc']) > 40 ? '...' : '') : 'Property Video'; ?>
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="sw-pagination sw-pagination-video-reels text-center mt-20"></div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center wow fadeInUp" data-wow-delay=".2s">
                <p class="text-variant-1">No videos available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<!-- End Video Section -->


<!-- /Latest new -->

<!-- Testimonial -->
<section class="flat-section flat-testimonial pt-0">
    <div class="container">
        <div class="box-title px-15">
            <div class="text-center wow fadeInUp">
                <div class="text-subtitle text-primary">Our Testimonials</div>
                <h3 class="title mt-4">What’s people say’s</h3>
                <p class="desc text-variant-1">Our seasoned team excels in real estate with years of
                    successful market navigation, offering informed decisions and optimal results.</p>
            </div>
        </div>
        <div dir="ltr" class="swiper tf-sw-testimonial" data-preview="3" data-tablet="2" data-mobile-sm="2"
            data-mobile="1" data-space="15" data-space-md="30" data-space-lg="30" data-centered="false"
            data-loop="false">
            <div class="swiper-wrapper wow fadeInUp" data-wow-delay=".2s">
                <div class="swiper-slide">
                    <div class="box-tes-item style-2">
                        <span class="icon icon-quote"></span>
                        <p class="note body-2">
                            "Working with Coimbatore Properties has been an exceptional experience. Their team guided me
                            through every step of the project with complete transparency. The construction quality,
                            timely updates, and professional approach made the entire journey stress-free. I’m truly
                            impressed by their commitment to delivering what they promise."
                        </p>
                        <div class="box-avt d-flex align-items-center gap-12">
                            <div class="avatar avt-60 round">
                                <img src="images/avatar/avt-png1.png" alt="avatar">
                            </div>
                            <div class="info">
                                <h6>R. Karthikeyan</h6>
                                <p class="caption-2 text-variant-1 mt-4">Villa Buyer</p>
                                <ul class="list-star">
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="box-tes-item style-2">
                        <span class="icon icon-quote"></span>
                        <p class="note body-2">
                            "Coimbatore Properties exceeded all my expectations. From planning to handover, their
                            attention to detail and dedication stood out. The craftsmanship is top-notch, and the
                            customer service team was always available to answer my questions. I couldn’t have chosen a
                            better builder for my dream home."
                        </p>
                        <div class="box-avt d-flex align-items-center gap-12">
                            <div class="avatar avt-60 round">
                                <img src="images/avatar/avt-png2.png" alt="avatar">
                            </div>
                            <div class="info">
                                <h6>S. Priyadharshini</h6>
                                <p class="caption-2 text-variant-1 mt-4">Property Investor </p>
                                <ul class="list-star">
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="box-tes-item style-2">
                        <span class="icon icon-quote"></span>
                        <p class="note body-2">
                            "Their project management is extremely efficient. Every milestone was completed on time, and
                            the material quality used across the property is outstanding. I always felt supported and
                            well-informed throughout the entire process. Highly recommended for anyone looking for a
                            trusted real-estate developer."
                        </p>
                        <div class="box-avt d-flex align-items-center gap-12">
                            <div class="avatar avt-60 round">
                                <img src="images/avatar/avt-png4.png" alt="avatar">
                            </div>
                            <div class="info">
                                <h6>V. Aravind Kumar</h6>
                                <p class="caption-2 text-variant-1 mt-4">Villa Owner</p>
                                <ul class="list-star">
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="box-tes-item style-2">
                        <span class="icon icon-quote"></span>
                        <p class="note body-2">
                            "Professional, reliable, and transparent—that’s how I would describe Coimbatore Properties.
                            They listened to our needs, customized the layout perfectly, and delivered a modern home far
                            better than what we imagined. Truly one of the best experiences we’ve had with a builder."
                        </p>
                        <div class="box-avt d-flex align-items-center gap-12">
                            <div class="avatar avt-60 round">
                                <img src="images/avatar/avt-png6.png" alt="avatar">
                            </div>
                            <div class="info">
                                <h6>Rajesh Kumar</h6>
                                <p class="caption-2 text-variant-1 mt-4">Villa Investor</p>
                                <ul class="list-star">
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="box-tes-item style-2">
                        <span class="icon icon-quote"></span>
                        <p class="note body-2">
                            "What I appreciate the most is their integrity and commitment to quality. The team ensured
                            every detail was taken care of, and the final result was exactly what they showcased. I
                            strongly recommend Coimbatore Properties for hassle-free and premium home construction."
                        </p>
                        <div class="box-avt d-flex align-items-center gap-12">
                            <div class="avatar avt-60 round">
                                <img src="images/avatar/avt-png6.png" alt="avatar">
                            </div>
                            <div class="info">
                                <h6>G. Anbarasu</h6>
                                <p class="caption-2 text-variant-1 mt-4">Property Buyer</p>
                                <ul class="list-star">
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                    <li class="icon icon-star"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="sw-pagination sw-pagination-testimonial text-center"></div>
        </div>
    </div>
</section>
<!-- End Testimonial -->
<!-- partner -->

<section class="flat-section pt-0" style="margin-top: 30px;">
    <div class="container">
        <div class="box-title text-center wow fadeInUp">
            <div class="text-subtitle text-primary">Latest New</div>
            <h3 class="title mt-4">Properties Reels</h3>
        </div>
        <?php
        // Initialize videos array
        $videos = array();

        // Debug: Check if reels_videos exists
        
        // var_dump(isset($reels_videos), !empty($reels_videos));
        
        // Get reels videos from database
        if (isset($reels_videos) && !empty($reels_videos) && is_array($reels_videos)) {
            foreach ($reels_videos as $reel) {
                // Skip if reel is empty or doesn't have required fields
                if (empty($reel) || (!isset($reel['videoUrl']) && !isset($reel['reelId']))) {
                    continue;
                }

                // Format date from createdAt
                $date = '';
                if (isset($reel['createdAt']) && !empty($reel['createdAt'])) {
                    try {
                        $dateObj = new DateTime($reel['createdAt']);
                        $date = $dateObj->format('F d, Y');
                    } catch (Exception $e) {
                        $date = '';
                    }
                }

                // Check if YouTube URL
                $videoUrl = isset($reel['videoUrl']) ? $reel['videoUrl'] : '';
                $isYouTube = !empty($videoUrl) && (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false);

                // Generate embed URL if YouTube
                $embedUrl = '';
                if ($isYouTube && !empty($videoUrl)) {
                    $patterns = array(
                        '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/',
                        '/youtube\.com\/.*[?&]v=([^&\n?#]+)/'
                    );
                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern, $videoUrl, $matches)) {
                            $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                            break;
                        }
                    }
                }

                // Prepare video data
                $videoData = array(
                    'id' => isset($reel['id']) ? $reel['id'] : (isset($reel['reelId']) ? $reel['reelId'] : ''),
                    'videoUrl' => $videoUrl,
                    'embedUrl' => !empty($reel['embedUrl']) ? $reel['embedUrl'] : $embedUrl,
                    'isYouTube' => $isYouTube,
                    'thumbnailUrl' => isset($reel['thumbnailUrl']) ? $reel['thumbnailUrl'] : (isset($reel['thumbnail']) ? $reel['thumbnail'] : ''),
                    'caption' => isset($reel['caption']) ? $reel['caption'] : '',
                    'date' => $date,
                    'views' => isset($reel['views']) ? intval($reel['views']) : 0,
                    'likes' => isset($reel['likes']) ? intval($reel['likes']) : 0,
                    'orderValue' => isset($reel['orderValue']) ? intval($reel['orderValue']) : 999
                );

                // Only add if videoUrl exists
                if (!empty($videoData['videoUrl'])) {
                    $videos[] = $videoData;
                }
            }

            // Sort by orderValue
            if (!empty($videos)) {
                usort($videos, function ($a, $b) {
                    return $a['orderValue'] - $b['orderValue'];
                });
            }
        }
        ?>
        <style>
            .video-wrapper {
                position: relative;
                padding-bottom: 177.78%;
                height: 0;
                overflow: hidden;
                background: #000;
                border-radius: 8px;
                cursor: pointer;
            }

            .video-wrapper video,
            .video-wrapper img {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .video-wrapper .play-button {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 60px;
                height: 60px;
                background: rgba(255, 255, 255, 0.9);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 2;
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .video-wrapper .play-button svg {
                width: 24px;
                height: 24px;
                margin-left: 4px;
            }

            .video-wrapper:hover .play-button {
                background: rgba(255, 255, 255, 1);
                transform: translate(-50%, -50%) scale(1.1);
            }

            .video-wrapper.playing .play-button {
                display: none;
            }

            .video-wrapper video {
                display: none;
            }

            .video-wrapper.playing video {
                display: block;
            }

            .video-wrapper.playing img {
                display: none;
            }

            /* Stop Button for Properties Reels - Always Visible (like Explore Our Properties) */
            .video-wrapper .stop-button-video-reel {
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
                position: absolute;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                margin: 0;
                padding: 10px 20px;
                background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                color: #fff;
                border: 2px solid #dc3545;
                border-radius: 8px;
                cursor: pointer;
                font-size: 13px;
                font-weight: 600;
                transition: all 0.3s ease;
                width: auto;
                min-width: 100px;
                text-align: center;
                z-index: 15;
                box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
                align-items: center;
                justify-content: center;
                gap: 6px;
            }

            .video-wrapper .stop-button-video-reel svg {
                width: 14px;
                height: 14px;
                flex-shrink: 0;
                display: block;
                fill: currentColor;
            }

            .video-wrapper .stop-button-video-reel span {
                display: inline-block;
                line-height: 1;
            }

            .video-wrapper .stop-button-video-reel:hover {
                background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
                transform: translateX(-50%) translateY(-2px);
                box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
                border-color: #c82333;
            }

            .video-wrapper .stop-button-video-reel:active {
                transform: translateX(-50%) translateY(0);
                box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
            }

            .video-wrapper:not(.playing) .stop-button-video-reel {
                display: none !important;
            }

            @media (max-width: 767px) {
                .video-wrapper .stop-button-video-reel {
                    bottom: 15px;
                    padding: 12px 18px;
                    font-size: 14px;
                    min-width: 110px;
                }

                .video-wrapper .stop-button-video-reel svg {
                    width: 16px;
                    height: 16px;
                }
            }

            .stop-button {
                display: none;
                margin: 15px auto 0;
                padding: 12px 24px;
                background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                color: #fff;
                border: 2px solid #dc3545;
                border-radius: 8px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 600;
                transition: all 0.3s ease;
                width: auto;
                min-width: 140px;
                text-align: center;
                position: relative;
                z-index: 10;
                box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            /* Override display none when button should be visible */
            .stop-button.show-stop-button,
            .stop-button[style*="display: flex"],
            .stop-button[style*="display:flex"],
            .stop-button[style*="display: block"],
            .stop-button[style*="display:block"],
            .stop-button[data-visible="true"],
            .flat-blog-item.playing .stop-button,
            .video-wrapper.playing+.stop-button {
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .stop-button svg {
                width: 16px;
                height: 16px;
                flex-shrink: 0;
                display: block;
                fill: currentColor;
            }

            .stop-button span {
                display: inline-block;
                line-height: 1;
            }

            /* Ensure button content is visible */
            .stop-button.show-stop-button svg,
            .stop-button[data-visible="true"] svg {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .stop-button.show-stop-button span,
            .stop-button[data-visible="true"] span {
                display: inline-block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .stop-button:hover {
                background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
                border-color: #c82333;
            }

            .stop-button:active {
                transform: translateY(0);
                box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
            }

            /* Responsive stop button */
            @media (max-width: 767px) {
                .stop-button {
                    width: 100%;
                    padding: 14px 20px;
                    font-size: 15px;
                }
            }

            /* Animation for stop button appearance */
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .stop-button.show-stop-button {
                animation: slideInUp 0.3s ease-out;
            }

            /* Show stop button when video is playing - multiple selectors for reliability */
            .video-wrapper.playing+.stop-button,
            .flat-blog-item.playing .stop-button,
            .flat-blog-item .video-wrapper.playing~.stop-button,
            .flat-blog-item.playing>.stop-button {
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            /* Show stop button when it has inline style display block or flex */
            .stop-button[style*="display: block"],
            .stop-button[style*="display:block"],
            .stop-button[style*="display: flex"],
            .stop-button[style*="display:flex"],
            .stop-button.show-stop-button {
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            /* Additional selector for stop button visibility - using class */
            .flat-blog-item.playing .stop-button,
            .video-wrapper.playing~.stop-button {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            /* Carousel slide width fix */
            .tf-sw-recent-estate .swiper-slide {
                width: 100%;
                height: auto;
            }

            .tf-sw-recent-estate .swiper-wrapper {
                display: flex;
                align-items: stretch;
            }

            @media (min-width: 575px) {
                .tf-sw-recent-estate .swiper-slide {
                    width: auto;
                }
            }

            /* Ensure desktop grid shows on medium screens and up */
            @media (min-width: 768px) {
                .tf-grid-layout.d-none.d-md-block {
                    display: grid !important;
                }
            }

            /* Hide desktop grid on mobile */
            @media (max-width: 767.98px) {
                .tf-grid-layout.d-none.d-md-block {
                    display: none !important;
                }
            }
        </style>
        <?php
        $reelsCount = count($videos);
        $useReelsSlider = $reelsCount > 4;
        ?>

        <?php if (!empty($videos)): ?>
            <?php if ($useReelsSlider): ?>
                <!-- Desktop Slider View - More than 4 items -->
                <div class="reels-slider-desktop wow fadeInUp d-none d-md-block" data-wow-delay=".2s"
                    style="position: relative; padding: 0 50px;">
                    <div dir="ltr" class="swiper tf-sw-reels-desktop" data-preview="4" data-tablet="3" data-mobile-sm="2"
                        data-mobile="1" data-space="20" data-space-md="20" data-space-lg="20" data-centered="false"
                        data-loop="true" data-autoplay="false" data-autoplay-delay="3000">
                        <div class="swiper-wrapper">
                            <?php foreach ($videos as $index => $video): ?>
                                <div class="swiper-slide">
                                    <div class="flat-blog-item hover-img style-1"
                                        data-video-id="<?php echo htmlspecialchars($video['id']); ?>"
                                        data-video-url="<?php echo htmlspecialchars($video['videoUrl']); ?>"
                                        data-is-youtube="<?php echo isset($video['isYouTube']) && $video['isYouTube'] ? 'true' : 'false'; ?>">
                                        <div class="img-style video-wrapper" id="videoWrapper-<?php echo $index; ?>">
                                            <?php if (!empty($video['thumbnailUrl'])): ?>
                                                <img src="<?php echo htmlspecialchars($video['thumbnailUrl']); ?>"
                                                    alt="<?php echo htmlspecialchars($video['caption']); ?>" loading="lazy">
                                            <?php else: ?>
                                                <div
                                                    style="background: #1a1a1a; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #666;">
                                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M8 5v14l11-7z" />
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (isset($video['isYouTube']) && $video['isYouTube'] && !empty($video['embedUrl'])): ?>
                                                <iframe src="" data-embed-url="<?php echo htmlspecialchars($video['embedUrl']); ?>"
                                                    frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen
                                                    style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 3; background: #000;">
                                                </iframe>
                                            <?php else: ?>
                                                <video controls playsinline style="display: none;"
                                                    poster="<?php echo !empty($video['thumbnailUrl']) ? htmlspecialchars($video['thumbnailUrl']) : ''; ?>">
                                                    <source src="<?php echo htmlspecialchars($video['videoUrl']); ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            <?php endif; ?>
                                            <div class="play-button">
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M8 5v14l11-7z" />
                                                </svg>
                                            </div>
                                            <button type="button" class="stop-button-video-reel"
                                                data-video-wrapper="videoWrapper-<?php echo $index; ?>">
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M6 6h12v12H6z" />
                                                </svg>
                                                <span>Stop</span>
                                            </button>
                                        </div>
                                        <?php if (!empty($video['date'])): ?>
                                            <span class="date-post"><?php echo htmlspecialchars($video['date']); ?></span>
                                        <?php endif; ?>
                                        <div class="content-box">
                                            <h6 class="title">
                                                <?php echo !empty($video['caption']) ? htmlspecialchars($video['caption']) : 'Video'; ?>
                                            </h6>
                                            <div class="post-author">
                                                <?php if ($video['views'] > 0): ?>
                                                    <span class="fw-6"><?php echo number_format($video['views']); ?> views</span>
                                                <?php endif; ?>
                                                <?php if ($video['likes'] > 0): ?>
                                                    <span><?php echo number_format($video['likes']); ?> likes</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
                <style>
                    .reels-slider-desktop {
                        position: relative;
                    }

                    .reels-slider-desktop .swiper-button-next,
                    .reels-slider-desktop .swiper-button-prev {
                        width: 50px;
                        height: 50px;
                        background: rgba(255, 255, 255, 0.95) !important;
                        backdrop-filter: blur(10px);
                        border-radius: 8px;
                        color: #2c3e50 !important;
                        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1), 0 2px 8px rgba(0, 0, 0, 0.08);
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        z-index: 10;
                        margin-top: 0;
                        top: 50%;
                        transform: translateY(-50%);
                        display: flex !important;
                        align-items: center;
                        justify-content: center;
                        border: 1px solid rgba(44, 62, 80, 0.1);
                    }

                    .reels-slider-desktop .swiper-button-next:hover,
                    .reels-slider-desktop .swiper-button-prev:hover {
                        background: #2c3e50 !important;
                        color: #fff !important;
                        transform: translateY(-50%) translateX(0) scale(1.05);
                        box-shadow: 0 6px 25px rgba(44, 62, 80, 0.3), 0 3px 12px rgba(0, 0, 0, 0.15);
                        border-color: #2c3e50;
                    }

                    .reels-slider-desktop .swiper-button-next::after,
                    .reels-slider-desktop .swiper-button-prev::after {
                        font-size: 22px;
                        font-weight: 600;
                        font-family: 'swiper-icons';
                    }

                    .reels-slider-desktop .swiper-button-next {
                        right: 10px !important;
                    }

                    .reels-slider-desktop .swiper-button-prev {
                        left: 10px !important;
                    }

                    .reels-slider-desktop .swiper-slide {
                        height: auto;
                    }

                    /* Ensure buttons are visible on all screen sizes */
                    @media (max-width: 991px) {
                        .reels-slider-desktop {
                            padding: 0 60px !important;
                        }

                        .reels-slider-desktop .swiper-button-next,
                        .reels-slider-desktop .swiper-button-prev {
                            width: 44px;
                            height: 44px;
                        }

                        .reels-slider-desktop .swiper-button-next::after,
                        .reels-slider-desktop .swiper-button-prev::after {
                            font-size: 20px;
                        }

                        .reels-slider-desktop .swiper-button-next {
                            right: 8px !important;
                        }

                        .reels-slider-desktop .swiper-button-prev {
                            left: 8px !important;
                        }
                    }

                    @media (max-width: 480px) {
                        .reels-slider-desktop {
                            padding: 0 50px !important;
                        }

                        .reels-slider-desktop .swiper-button-next,
                        .reels-slider-desktop .swiper-button-prev {
                            width: 38px;
                            height: 38px;
                        }

                        .reels-slider-desktop .swiper-button-next::after,
                        .reels-slider-desktop .swiper-button-prev::after {
                            font-size: 18px;
                        }

                        .reels-slider-desktop .swiper-button-next {
                            right: 5px !important;
                        }

                        .reels-slider-desktop .swiper-button-prev {
                            left: 5px !important;
                        }
                    }

                    /* Mobile Slider Navigation Buttons for Properties Reels */
                    .d-md-none .tf-sw-recent-estate {
                        position: relative;
                        padding: 0 50px;
                    }

                    .d-md-none .tf-sw-recent-estate .swiper-button-next,
                    .d-md-none .tf-sw-recent-estate .swiper-button-prev {
                        width: 40px;
                        height: 40px;
                        background: #fff !important;
                        border-radius: 50%;
                        color: #2c3e50 !important;
                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
                        transition: all 0.3s ease;
                        z-index: 10;
                        margin-top: 0;
                        top: 50%;
                        transform: translateY(-50%);
                        display: flex !important;
                        align-items: center;
                        justify-content: center;
                    }

                    .d-md-none .tf-sw-recent-estate .swiper-button-next:hover,
                    .d-md-none .tf-sw-recent-estate .swiper-button-prev:hover {
                        background: #2c3e50 !important;
                        color: #fff !important;
                        transform: translateY(-50%) scale(1.1);
                        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
                    }

                    .d-md-none .tf-sw-recent-estate .swiper-button-next::after,
                    .d-md-none .tf-sw-recent-estate .swiper-button-prev::after {
                        font-size: 18px;
                        font-weight: bold;
                        font-family: 'swiper-icons';
                    }

                    .d-md-none .tf-sw-recent-estate .swiper-button-next {
                        right: 0 !important;
                    }

                    .d-md-none .tf-sw-recent-estate .swiper-button-prev {
                        left: 0 !important;
                    }

                    @media (max-width: 480px) {
                        .d-md-none .tf-sw-recent-estate {
                            padding: 0 45px;
                        }

                        .d-md-none .tf-sw-recent-estate .swiper-button-next,
                        .d-md-none .tf-sw-recent-estate .swiper-button-prev {
                            width: 35px;
                            height: 35px;
                        }

                        .d-md-none .tf-sw-recent-estate .swiper-button-next::after,
                        .d-md-none .tf-sw-recent-estate .swiper-button-prev::after {
                            font-size: 16px;
                        }
                    }
                </style>
            <?php else: ?>
                <!-- Desktop Grid View - 4 columns or less -->
                <div class="tf-grid-layout xl-col-4 sm-col-2 wow fadeInUp d-none d-md-block" data-wow-delay=".2s">
                    <?php foreach ($videos as $index => $video): ?>
                        <div class="flat-blog-item hover-img style-1" data-video-id="<?php echo htmlspecialchars($video['id']); ?>"
                            data-video-url="<?php echo htmlspecialchars($video['videoUrl']); ?>">
                            <div class="img-style video-wrapper" id="videoWrapper-<?php echo $index; ?>">
                                <?php if (!empty($video['thumbnailUrl'])): ?>
                                    <img src="<?php echo htmlspecialchars($video['thumbnailUrl']); ?>"
                                        alt="<?php echo htmlspecialchars($video['caption']); ?>" loading="lazy">
                                <?php else: ?>
                                    <video preload="metadata" style="display: block; width: 100%; height: 100%; object-fit: cover;">
                                        <source src="<?php echo htmlspecialchars($video['videoUrl']); ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                                <video controls playsinline style="display: none;"
                                    poster="<?php echo !empty($video['thumbnailUrl']) ? htmlspecialchars($video['thumbnailUrl']) : ''; ?>">
                                    <source src="<?php echo htmlspecialchars($video['videoUrl']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <div class="play-button">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                                <button type="button" class="stop-button-video-reel"
                                    data-video-wrapper="videoWrapper-<?php echo $index; ?>">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 6h12v12H6z" />
                                    </svg>
                                    <span>Stop</span>
                                </button>
                            </div>
                            <?php if (!empty($video['date'])): ?>
                                <span class="date-post"><?php echo htmlspecialchars($video['date']); ?></span>
                            <?php endif; ?>
                            <div class="content-box">
                                <h6 class="title">
                                    <?php echo !empty($video['caption']) ? htmlspecialchars($video['caption']) : 'Video'; ?></h6>
                                <div class="post-author">
                                    <?php if ($video['views'] > 0): ?>
                                        <span class="fw-6"><?php echo number_format($video['views']); ?> views</span>
                                    <?php endif; ?>
                                    <?php if ($video['likes'] > 0): ?>
                                        <span><?php echo number_format($video['likes']); ?> likes</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Mobile Carousel View -->
        <?php if (!empty($videos)): ?>
            <div class="d-md-none">
                <div dir="ltr" class="swiper tf-sw-recent-estate wow fadeInUp" data-wow-delay=".2s" data-preview="4"
                    data-tablet="2" data-mobile-sm="1" data-mobile="1" data-space-lg="30" data-space-md="15"
                    data-space="15">
                    <div class="swiper-wrapper">
                        <?php foreach ($videos as $index => $video): ?>
                            <div class="swiper-slide">
                                <div class="flat-blog-item hover-img style-1"
                                    data-video-id="<?php echo htmlspecialchars($video['id']); ?>"
                                    data-video-url="<?php echo htmlspecialchars($video['videoUrl']); ?>">
                                    <div class="img-style video-wrapper" id="videoWrapper-mobile-<?php echo $index; ?>">
                                        <?php if (!empty($video['thumbnailUrl'])): ?>
                                            <img src="<?php echo htmlspecialchars($video['thumbnailUrl']); ?>"
                                                alt="<?php echo htmlspecialchars($video['caption']); ?>" loading="lazy">
                                        <?php else: ?>
                                            <video preload="metadata" muted
                                                style="display: block; width: 100%; height: 100%; object-fit: cover; pointer-events: none;">
                                                <source src="<?php echo htmlspecialchars($video['videoUrl']); ?>" type="video/mp4">
                                            </video>
                                        <?php endif; ?>
                                        <video controls playsinline style="display: none;"
                                            poster="<?php echo !empty($video['thumbnailUrl']) ? htmlspecialchars($video['thumbnailUrl']) : ''; ?>">
                                            <source src="<?php echo htmlspecialchars($video['videoUrl']); ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                        <div class="play-button">
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                        <button type="button" class="stop-button-video-reel"
                                            data-video-wrapper="videoWrapper-mobile-<?php echo $index; ?>">
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M6 6h12v12H6z" />
                                            </svg>
                                            <span>Stop</span>
                                        </button>
                                    </div>
                                    <?php if (!empty($video['date'])): ?>
                                        <span class="date-post"><?php echo htmlspecialchars($video['date']); ?></span>
                                    <?php endif; ?>
                                    <div class="content-box">
                                        <h6 class="title">
                                            <?php echo !empty($video['caption']) ? htmlspecialchars($video['caption']) : 'Video'; ?>
                                        </h6>
                                        <div class="post-author">
                                            <?php if ($video['views'] > 0): ?>
                                                <span class="fw-6"><?php echo number_format($video['views']); ?> views</span>
                                            <?php endif; ?>
                                            <?php if ($video['likes'] > 0): ?>
                                                <span><?php echo number_format($video['likes']); ?> likes</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="sw-pagination sw-pagination-recent-estate text-center"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>


<section class="flat-section pt-0">
    <div class="container2">
        <h6 class="mb-20 text-center text-capitalize text-black-4">Trusted by over 15+ major companies</h6>
        <div class="partner-marquee-container">
            <div class="partner-marquee-track">
                <div class="partner-marquee-item">
                    <img src="<?php echo base_url('images/logs/1.png'); ?>" alt="Partner Logo 1"
                        style="width: 160px; height: 80px; object-fit: contain;">
                </div>
                <div class="partner-marquee-item">
                    <img src="<?php echo base_url('images/logs/2.png'); ?>" alt="Partner Logo 2"
                        style="width: 160px; height: 80px; object-fit: contain;">
                </div>
                <div class="partner-marquee-item">
                    <img src="<?php echo base_url('images/logs/3.png'); ?>" alt="Partner Logo 3"
                        style="width: 160px; height: 80px; object-fit: contain;">
                </div>
                <div class="partner-marquee-item">
                    <img src="<?php echo base_url('images/logs/4.png'); ?>" alt="Partner Logo 4"
                        style="width: 160px; height: 80px; object-fit: contain;">
                </div>

                <div class="partner-marquee-item">
                    <img src="<?php echo base_url('images/logs/1.png'); ?>" alt="Partner Logo 1"
                        style="width: 160px; height: 80px; object-fit: contain;">
                </div>
                <div class="partner-marquee-item">
                    <img src="<?php echo base_url('images/logs/2.png'); ?>" alt="Partner Logo 2"
                        style="width: 160px; height: 80px; object-fit: contain;">
                </div>
                <div class="partner-marquee-item">
                    <img src="<?php echo base_url('images/logs/3.png'); ?>" alt="Partner Logo 3"
                        style="width: 160px; height: 80px; object-fit: contain;">
                </div>
                <div class="partner-marquee-item">
                    <img src="<?php echo base_url('images/logs/4.png'); ?>" alt="Partner Logo 4"
                        style="width: 160px; height: 80px; object-fit: contain;">
                </div>

            </div>
        </div>
    </div>
</section>
<!-- End partner -->

<!-- Benefit -->
<section class="mx-5 bg-primary-new radius-30">
    <div class="flat-img-with-text">
        <div class="content-left img-animation wow">
            <?php $benefitSectionImg = base_url('assets/images/home/house-18.jpg'); ?>
            <img class="lazyload" data-src="<?php echo htmlspecialchars($benefitSectionImg); ?>"
                src="<?php echo htmlspecialchars($dvm_lazy_img_placeholder); ?>" width="600" height="450" loading="lazy"
                decoding="async" alt="Coimbatore Properties benefits section image">
        </div>
        <div class="content-right">
            <div class="box-title wow fadeInUp">
                <div class="text-subtitle text-primary">Our Benifit</div>
                <h3 class="title mt-4">Why Choose Coimbatore Properties</h3>
                <p class="desc text-variant-1">At Coimbatore Properties, we combine expertise, personal attention, and
                    trust to deliver a seamless real estate experience. Whether you're buying a villa, investing in
                    farmland, or exploring premium plots, we guide you with clarity and care at every step.</p>
            </div>
            <div class="flat-service wow fadeInUp" data-wow-delay=".2s">
                <a href="#" class="box-benefit hover-btn-view">
                    <div class="icon-box">
                        <span class="icon icon-proven"></span>
                    </div>
                    <div class="content">
                        <h5 class="title">Expert Guidance</h5>
                        <p class="description">With years of real estate knowledge and a deep understanding of the local
                            market, our team ensures you make confident and informed decisions. From property selection
                            to documentation, we provide direction you can trust completely.
                        </p>
                    </div>
                </a>
                <a href="#" class="box-benefit hover-btn-view">
                    <div class="icon-box">
                        <span class="icon icon-customize"></span>
                    </div>
                    <div class="content">
                        <h5 class="title">Personalized Guidance</h5>
                        <p class="description">Your goals are unique—and so is our approach. We filter property options
                            that suit your lifestyle, budget, and long-term plans. Every recommendation is tailored to
                            match what you truly need.</p>
                    </div>
                </a>
                <a href="#" class="box-benefit hover-btn-view">
                    <div class="icon-box">
                        <span class="icon icon-partnership"></span>
                    </div>
                    <div class="content">
                        <h5 class="title">Trusted Client Partnerships</h5>
                        <p class="description">We build relationships based on transparency, honesty, and integrity.
                            With clear communication and ethical practices, we ensure your journey with us remains
                            smooth, secure, and trustworthy from start to finish.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>



<script>
    // Simple inline video playback - no modal, only one video plays at a time
    document.addEventListener('DOMContentLoaded', function () {
        // Ensure all videos are paused on page load
        document.querySelectorAll('.video-wrapper video').forEach(function (video) {
            video.pause();
            video.currentTime = 0;
            video.style.display = 'none';
        });

        // Function to stop a specific video
        function stopVideo(wrapper) {
            if (!wrapper) return;

            var video = wrapper.querySelector('video');
            var iframe = wrapper.querySelector('iframe');
            var img = wrapper.querySelector('img');
            var playBtn = wrapper.querySelector('.play-button');
            var flatBlogItem = wrapper.closest('.flat-blog-item');

            // Find stop button - try multiple selectors
            var stopBtn = document.querySelector('.stop-button-video-reel[data-video-wrapper="' + wrapper.id + '"]');
            if (!stopBtn && flatBlogItem) {
                stopBtn = flatBlogItem.querySelector('.stop-button-video-reel[data-video-wrapper="' + wrapper.id + '"]');
            }
            if (!stopBtn && flatBlogItem) {
                stopBtn = flatBlogItem.querySelector('.stop-button-video-reel');
            }

            // Pause and reset video
            if (video) {
                video.pause();
                video.currentTime = 0;
                video.style.display = 'none';
            }

            // Stop YouTube iframe
            if (iframe) {
                iframe.src = '';
                iframe.style.display = 'none';
                iframe.style.visibility = 'hidden';
                iframe.style.opacity = '0';
                iframe.style.zIndex = '-1';
            }

            // Show thumbnail and play button again
            if (img) img.style.display = 'block';
            if (playBtn) playBtn.style.display = 'flex';

            // Hide stop button
            if (stopBtn) {
                stopBtn.style.setProperty('display', 'none', 'important');
                stopBtn.style.setProperty('visibility', 'hidden', 'important');
                stopBtn.style.setProperty('opacity', '0', 'important');
                stopBtn.classList.remove('show-stop-button');
            }

            // Remove playing class
            wrapper.classList.remove('playing');

            // Remove playing class from parent flat-blog-item
            if (flatBlogItem) {
                flatBlogItem.classList.remove('playing');
            }
        }

        // Function to stop all playing videos except the current one
        function stopAllVideos(exceptWrapper) {
            document.querySelectorAll('.video-wrapper.playing').forEach(function (wrapper) {
                // Skip the video we want to play
                if (wrapper === exceptWrapper) {
                    return;
                }

                stopVideo(wrapper);
            });
        }

        // Function to play a video
        function playVideo(wrapper) {
            if (!wrapper) return;

            var video = wrapper.querySelector('video');
            var iframe = wrapper.querySelector('iframe');
            var img = wrapper.querySelector('img');
            var playBtn = wrapper.querySelector('.play-button');
            var flatBlogItem = wrapper.closest('.flat-blog-item');
            var isYouTube = flatBlogItem && flatBlogItem.getAttribute('data-is-youtube') === 'true';

            // Check if we have either video or iframe
            if (!video && !iframe) return;

            // Stop all other videos first
            stopAllVideos(wrapper);

            // Hide thumbnail and play button
            if (img) img.style.display = 'none';
            if (playBtn) playBtn.style.display = 'none';

            // Handle YouTube iframe
            if (isYouTube && iframe) {
                var embedUrl = iframe.getAttribute('data-embed-url');
                if (embedUrl) {
                    // Add autoplay parameter
                    var autoplayUrl = embedUrl + (embedUrl.indexOf('?') > -1 ? '&' : '?') + 'autoplay=1&rel=0';
                    iframe.src = autoplayUrl;
                    iframe.style.display = 'block';
                    iframe.style.visibility = 'visible';
                    iframe.style.opacity = '1';
                    iframe.style.zIndex = '3';
                }
            } else if (video) {
                // Show and play regular video
                video.style.display = 'block';
                video.play().catch(function (err) {
                    console.log('Video play error:', err);
                });
            }

            wrapper.classList.add('playing');

            // Add playing class to parent flat-blog-item for CSS selector
            if (flatBlogItem) {
                flatBlogItem.classList.add('playing');
            }

            // Show stop button - try multiple methods to find it
            var stopBtn = null;

            // Method 1: Find by data attribute
            stopBtn = document.querySelector('.stop-button[data-video-wrapper="' + wrapper.id + '"]');

            // Method 2: Find within flat-blog-item by data attribute
            if (!stopBtn && flatBlogItem) {
                stopBtn = flatBlogItem.querySelector('.stop-button[data-video-wrapper="' + wrapper.id + '"]');
            }

            // Method 3: Find as direct sibling of video-wrapper
            if (!stopBtn) {
                var nextSibling = wrapper.nextElementSibling;
                if (nextSibling && nextSibling.classList.contains('stop-button')) {
                    stopBtn = nextSibling;
                }
            }

            // Method 4: Find any stop button within flat-blog-item
            if (!stopBtn && flatBlogItem) {
                stopBtn = flatBlogItem.querySelector('.stop-button');
            }

            // Method 5: Find by searching all stop buttons and matching data attribute
            if (!stopBtn) {
                document.querySelectorAll('.stop-button').forEach(function (btn) {
                    if (btn.getAttribute('data-video-wrapper') === wrapper.id) {
                        stopBtn = btn;
                    }
                });
            }

            // Show the stop button immediately
            if (stopBtn) {
                // Remove any conflicting classes first
                stopBtn.classList.remove('hide-stop-button');

                // Add show class
                stopBtn.classList.add('show-stop-button');

                // Set inline styles with !important
                stopBtn.style.cssText = 'display: flex !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 10 !important;';

                // Force a reflow to ensure styles are applied
                void stopBtn.offsetHeight;

                // Verify it's visible
                var computedStyle = window.getComputedStyle(stopBtn);
                console.log('Stop button shown for:', wrapper.id);
                console.log('Button element:', stopBtn);
                console.log('Button HTML:', stopBtn.outerHTML);
                console.log('Computed display:', computedStyle.display);
                console.log('Computed visibility:', computedStyle.visibility);
                console.log('Computed opacity:', computedStyle.opacity);
                console.log('Button offsetHeight:', stopBtn.offsetHeight);
                console.log('Button offsetWidth:', stopBtn.offsetWidth);

                // Make absolutely sure it's visible - add a visible class
                stopBtn.setAttribute('data-visible', 'true');

                // Double-check after a brief delay to ensure it stays visible
                setTimeout(function () {
                    var checkStyle = window.getComputedStyle(stopBtn);
                    if (checkStyle.display === 'none') {
                        stopBtn.style.cssText = 'display: flex !important; visibility: visible !important; opacity: 1 !important;';
                        console.log('Stop button re-shown after check');
                    }
                }, 100);
            } else {
                console.error('Stop button NOT found for:', wrapper.id);
                // Try one more time after a delay
                setTimeout(function () {
                    var retryStopBtn = document.querySelector('.stop-button-video-reel[data-video-wrapper="' + wrapper.id + '"]') ||
                        document.querySelector('.stop-button[data-video-wrapper="' + wrapper.id + '"]');
                    if (retryStopBtn) {
                        retryStopBtn.style.setProperty('display', 'flex', 'important');
                        retryStopBtn.style.setProperty('visibility', 'visible', 'important');
                        retryStopBtn.style.setProperty('opacity', '1', 'important');
                        retryStopBtn.classList.add('show-stop-button');
                        console.log('Stop button found on retry for:', wrapper.id);
                    }
                }, 200);
            }

            // Only play regular video when explicitly called (not autoplay) - YouTube iframes are handled above
            if (video && !isYouTube) {
                video.play().catch(function (err) {
                    console.log('Video play error:', err);
                });
            }
        }

        // Handle play button click
        document.querySelectorAll('.play-button').forEach(function (playBtn) {
            playBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var wrapper = this.closest('.video-wrapper');
                playVideo(wrapper);
            });
        });

        // Also handle clicking on video wrapper (but not on video itself)
        document.querySelectorAll('.video-wrapper').forEach(function (wrapper) {
            wrapper.addEventListener('click', function (e) {
                // Don't trigger if clicking video controls
                if (e.target.tagName === 'VIDEO' || e.target.closest('video')) {
                    return;
                }

                // If already playing, do nothing
                if (this.classList.contains('playing')) {
                    return;
                }

                playVideo(this);
            });
        });

        // Handle stop button clicks
        document.querySelectorAll('.stop-button').forEach(function (stopBtn) {
            stopBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var wrapperId = this.getAttribute('data-video-wrapper');
                var wrapper = document.getElementById(wrapperId);
                if (wrapper) {
                    stopVideo(wrapper);
                }
            });
        });
    });

    // Initialize Property Marquee Slider - Auto-moving Carousel with Next/Prev Buttons
    // Wait for jQuery and Swiper to be loaded
    function initPropertyMarqueeSwiper() {
        if (typeof Swiper === 'undefined' || typeof jQuery === 'undefined') {
            // Retry after a short delay if libraries not loaded yet
            setTimeout(initPropertyMarqueeSwiper, 100);
            return;
        }

        var $ = jQuery;

        if ($('.property-marquee-swiper').length === 0) {
            return;
        }

        var $marqueeSwiper = $('.property-marquee-swiper');

        // Check if already initialized
        if ($marqueeSwiper.hasClass('swiper-initialized')) {
            return;
        }

        var slideCount = $marqueeSwiper.find('.swiper-slide').length;

        // Always enable loop since we duplicate slides in PHP to ensure enough slides
        var enableLoop = slideCount > 1;

        // Find navigation buttons
        var $nextBtn = $marqueeSwiper.find('.property-marquee-next');
        var $prevBtn = $marqueeSwiper.find('.property-marquee-prev');

        if ($nextBtn.length === 0 || $prevBtn.length === 0) {
            console.error('Navigation buttons not found for Property Marquee');
            return;
        }

        try {
            var swiperConfig = {
                slidesPerView: 4,
                spaceBetween: 20,
                loop: enableLoop,
                loopedSlides: enableLoop ? Math.ceil(slideCount / 2) : 0,
                loopAdditionalSlides: enableLoop ? 2 : 0,
                autoplay: {
                    delay: 2000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                speed: 800,
                watchOverflow: false,
                navigation: {
                    nextEl: $nextBtn[0],
                    prevEl: $prevBtn[0],
                },
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                        loopedSlides: enableLoop ? Math.ceil(slideCount / 2) : 0,
                        loopAdditionalSlides: enableLoop ? 1 : 0,
                    },
                    480: {
                        slidesPerView: 2,
                        spaceBetween: 15,
                        loopedSlides: enableLoop ? Math.ceil(slideCount / 2) : 0,
                        loopAdditionalSlides: enableLoop ? 1 : 0,
                    },
                    768: {
                        slidesPerView: 3,
                        spaceBetween: 20,
                        loopedSlides: enableLoop ? Math.ceil(slideCount / 2) : 0,
                        loopAdditionalSlides: enableLoop ? 2 : 0,
                    },
                    1024: {
                        slidesPerView: 4,
                        spaceBetween: 20,
                        loopedSlides: enableLoop ? Math.ceil(slideCount / 2) : 0,
                        loopAdditionalSlides: enableLoop ? 2 : 0,
                    },
                },
                on: {
                    init: function () {
                        console.log('Property Marquee Swiper initialized. Slides: ' + slideCount + ', Loop: ' + enableLoop);
                    },
                    loopFix: function () {
                        // Ensure loop works correctly
                        this.slideToLoop(0, 0);
                    }
                }
            };

            var propertyMarqueeSwiper = new Swiper('.property-marquee-swiper', swiperConfig);
        } catch (e) {
            console.error('Error initializing Property Marquee Swiper:', e);
        }
    }

    // Start initialization when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(initPropertyMarqueeSwiper, 500);
        });
    } else {
        setTimeout(initPropertyMarqueeSwiper, 500);
    }

    // Initialize Location Marquee Swiper
    function initLocationMarqueeSwiper() {
        if (typeof Swiper === 'undefined' || typeof jQuery === 'undefined') {
            setTimeout(initLocationMarqueeSwiper, 100);
            return;
        }

        var $ = jQuery;

        if ($('.location-marquee-swiper').length === 0) {
            return;
        }

        var $marqueeSwiper = $('.location-marquee-swiper');

        // Check if already initialized
        if ($marqueeSwiper.hasClass('swiper-initialized')) {
            return;
        }

        var slideCount = $marqueeSwiper.find('.swiper-slide').length;
        var enableLoop = slideCount > 1;

        try {
            var swiperConfig = {
                slidesPerView: 'auto',
                spaceBetween: 20,
                loop: enableLoop,
                loopedSlides: enableLoop ? Math.ceil(slideCount / 2) : 0,
                loopAdditionalSlides: enableLoop ? 2 : 0,
                autoplay: {
                    delay: 2000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                speed: 800,
                watchOverflow: false,
                breakpoints: {
                    320: {
                        slidesPerView: 'auto',
                        spaceBetween: 15,
                    },
                    768: {
                        slidesPerView: 'auto',
                        spaceBetween: 20,
                    },
                    1024: {
                        slidesPerView: 'auto',
                        spaceBetween: 20,
                    },
                },
                on: {
                    init: function () {
                        console.log('Location Marquee Swiper initialized. Slides: ' + slideCount + ', Loop: ' + enableLoop);
                    }
                }
            };

            var locationMarqueeSwiper = new Swiper('.location-marquee-swiper', swiperConfig);
        } catch (e) {
            console.error('Error initializing Location Marquee Swiper:', e);
        }
    }

    // Start initialization when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(initLocationMarqueeSwiper, 500);
        });
    } else {
        setTimeout(initLocationMarqueeSwiper, 500);
    }

    // Initialize Main Featured Properties Carousel - 3 Cards Per Row with Autoplay
    (function () {
        var initAttempts = 0;
        var maxAttempts = 50;

        function initMainFeaturedPropertiesCarousel() {
            initAttempts++;

            // Check if libraries are loaded
            if (typeof Swiper === 'undefined' || typeof jQuery === 'undefined') {
                if (initAttempts < maxAttempts) {
                    setTimeout(initMainFeaturedPropertiesCarousel, 100);
                } else {
                    console.error('Swiper or jQuery not loaded after maximum attempts');
                }
                return;
            }

            var $ = jQuery;
            var $swiperElement = $('.tf-sw-main-featured-properties');

            // Check if element exists
            if ($swiperElement.length === 0) {
                if (initAttempts < maxAttempts) {
                    setTimeout(initMainFeaturedPropertiesCarousel, 100);
                }
                return;
            }

            // Check if already initialized
            if ($swiperElement.hasClass('swiper-initialized')) {
                console.log('Main Featured Properties Carousel already initialized');
                return;
            }

            var slideCount = $swiperElement.find('.swiper-slide').length;
            if (slideCount === 0) {
                if (initAttempts < maxAttempts) {
                    setTimeout(initMainFeaturedPropertiesCarousel, 100);
                }
                return;
            }

            // Enable loop if we have more than 1 slide (allow navigation even with few slides)
            var enableLoop = slideCount > 1;

            // Find navigation buttons - check in #viewAll first (outside container)
            var $viewAll = $('#viewAll');
            var $nextBtn = $viewAll.find('.main-featured-next').first();
            var $prevBtn = $viewAll.find('.main-featured-prev').first();

            // If buttons not found, check inside swiper
            if ($nextBtn.length === 0 || $prevBtn.length === 0) {
                $nextBtn = $swiperElement.find('.swiper-button-next').first();
                $prevBtn = $swiperElement.find('.swiper-button-prev').first();
            }

            // If still not found, check in wrapper
            if ($nextBtn.length === 0 || $prevBtn.length === 0) {
                var $wrapper = $swiperElement.closest('.main-featured-properties-carousel-wrapper');
                if ($wrapper.length > 0) {
                    var wrapperNext = $wrapper.find('.swiper-button-next').first();
                    var wrapperPrev = $wrapper.find('.swiper-button-prev').first();
                    if (wrapperNext.length > 0) $nextBtn = wrapperNext;
                    if (wrapperPrev.length > 0) $prevBtn = wrapperPrev;
                }
            }

            // If still not found, create them in #viewAll
            if ($nextBtn.length === 0 || $prevBtn.length === 0) {
                if ($viewAll.length > 0) {
                    if ($nextBtn.length === 0) {
                        $nextBtn = $('<div class="swiper-button-next main-featured-next"></div>');
                        $viewAll.append($nextBtn);
                    }
                    if ($prevBtn.length === 0) {
                        $prevBtn = $('<div class="swiper-button-prev main-featured-prev"></div>');
                        $viewAll.append($prevBtn);
                    }
                }
            }

            // Ensure buttons are visible and enabled
            $nextBtn.css({
                'display': 'flex',
                'opacity': '1',
                'pointer-events': 'auto',
                'cursor': 'pointer'
            });
            $prevBtn.css({
                'display': 'flex',
                'opacity': '1',
                'pointer-events': 'auto',
                'cursor': 'pointer'
            });

            console.log('Navigation buttons found - Next:', $nextBtn.length, 'Prev:', $prevBtn.length);

            try {
                var swiperConfig = {
                    slidesPerView: 3,
                    spaceBetween: 30,
                    loop: enableLoop,
                    autoplay: {
                        delay: 3000,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    },
                    speed: 600,
                    watchOverflow: false,
                    centeredSlides: true,
                    navigation: {
                        nextEl: $nextBtn.length > 0 ? $nextBtn[0] : '.main-featured-next',
                        prevEl: $prevBtn.length > 0 ? $prevBtn[0] : '.main-featured-prev',
                        disabledClass: 'swiper-button-disabled',
                        hiddenClass: 'swiper-button-hidden',
                    },
                    breakpoints: {
                        320: {
                            slidesPerView: 1.2,
                            spaceBetween: 15,
                            centeredSlides: true,
                        },
                        480: {
                            slidesPerView: 1.3,
                            spaceBetween: 15,
                            centeredSlides: true,
                        },
                        768: {
                            slidesPerView: 2,
                            spaceBetween: 20,
                            centeredSlides: false,
                        },
                        992: {
                            slidesPerView: 2,
                            spaceBetween: 25,
                            centeredSlides: false,
                        },
                        1024: {
                            slidesPerView: 3,
                            spaceBetween: 30,
                            centeredSlides: false,
                        },
                    },
                    on: {
                        init: function () {
                            console.log('Main Featured Properties Carousel initialized. Slides: ' + slideCount + ', Loop: ' + enableLoop);
                            var self = this;
                            // Ensure autoplay starts
                            setTimeout(function () {
                                if (self.autoplay && self.autoplay.running === false) {
                                    self.autoplay.start();
                                }
                            }, 100);
                        },
                        autoplayStart: function () {
                            console.log('Autoplay started for Main Featured Properties');
                        },
                        autoplayStop: function () {
                            console.log('Autoplay stopped for Main Featured Properties');
                        }
                    }
                };

                var mainFeaturedSwiper = new Swiper('.tf-sw-main-featured-properties', swiperConfig);

                // Store reference globally for debugging
                window.mainFeaturedSwiper = mainFeaturedSwiper;

                // Verify navigation is working
                if (mainFeaturedSwiper.navigation) {
                    console.log('Navigation initialized successfully');
                    console.log('Next button element:', mainFeaturedSwiper.navigation.nextEl);
                    console.log('Prev button element:', mainFeaturedSwiper.navigation.prevEl);
                } else {
                    console.warn('Navigation not initialized');
                }

                // Add manual click handlers as fallback - use buttons from #viewAll
                var $finalNextBtn = $('#viewAll .main-featured-next');
                var $finalPrevBtn = $('#viewAll .main-featured-prev');

                if ($finalNextBtn.length > 0) {
                    $finalNextBtn.off('click').on('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (mainFeaturedSwiper) {
                            mainFeaturedSwiper.slideNext();
                        }
                    });
                }

                if ($finalPrevBtn.length > 0) {
                    $finalPrevBtn.off('click').on('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (mainFeaturedSwiper) {
                            mainFeaturedSwiper.slidePrev();
                        }
                    });
                }

                // Force start autoplay after a delay
                setTimeout(function () {
                    if (mainFeaturedSwiper && mainFeaturedSwiper.autoplay) {
                        if (mainFeaturedSwiper.autoplay.running === false) {
                            mainFeaturedSwiper.autoplay.start();
                        }
                    }
                }, 500);
            } catch (e) {
                console.error('Error initializing Main Featured Properties Carousel:', e);
                console.error('Error details:', e.message, e.stack);
            }
        }

        // Start initialization
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(initMainFeaturedPropertiesCarousel, 300);
            });
        } else {
            setTimeout(initMainFeaturedPropertiesCarousel, 300);
        }
    })();
</script>
<!-- Latest New -->
<section class="flat-section bg-primary-new">
    <div class="container">
        <div class="box-title text-center wow fadeInUp">
            <div class="text-subtitle text-primary">Latest New</div>
            <h3 class="title mt-4">From Our Blog</h3>
        </div>
        <div dir="ltr" class="swiper tf-sw-latest" data-preview="3" data-tablet="2" data-mobile-sm="2" data-mobile="1"
            data-space-lg="30" data-space-md="15" data-space="15">
            <div class="swiper-wrapper wow fadeInUp" data-wow-delay=".2s">
                <?php foreach ($blogs as $blog_category):
                    // Safely handle date
                    $publishedDate = isset($blog_category['publishedDate']) ? $blog_category['publishedDate'] : (isset($blog_category['date']) ? $blog_category['date'] : (isset($blog_category['created_at']) ? $blog_category['created_at'] : date('Y-m-d')));
                    try {
                        $dt = new DateTime($publishedDate);
                        $formattedDate = $dt->format('F d, Y');
                    } catch (Exception $e) {
                        $formattedDate = date('F d, Y');
                    }

                    $coverImage = isset($blog_category['coverImageUrl']) ? $blog_category['coverImageUrl'] : '';
                    $authorName = isset($blog_category['authorName']) ? trim((string) $blog_category['authorName']) : '';
                    if ($authorName === '' && isset($blog_category['author'])) {
                        $authorName = trim((string) $blog_category['author']);
                    }
                    $showBlogAuthor = ($authorName !== '' && strcasecmp($authorName, 'Admin') !== 0);
                    $category = isset($blog_category['category']) ? $blog_category['category'] : '';
                    $title = isset($blog_category['title']) ? $blog_category['title'] : (isset($blog_category['name']) ? $blog_category['name'] : '');
                    $shortDescription = isset($blog_category['shortDescription']) ? $blog_category['shortDescription'] : (isset($blog_category['short_notes']) ? $blog_category['short_notes'] : '');
                    $slug = isset($blog_category['slug']) && !empty($blog_category['slug']) ? $blog_category['slug'] : (isset($blog_category['id']) ? $blog_category['id'] : '');
                    ?>
                    <div class="swiper-slide">
                        <a href="<?php echo base_url('blog/post/' . $slug); ?>" class="flat-blog-item hover-img">
                            <div class="img-style">
                                <img style="width:410px; height:310px; object-fit:cover;" class="lazyload"
                                    data-src="<?php echo htmlspecialchars($coverImage); ?>"
                                    src="<?php echo htmlspecialchars($dvm_lazy_img_placeholder); ?>" width="410"
                                    height="310" loading="lazy" decoding="async"
                                    alt="<?php echo htmlspecialchars($title ?: 'Blog', ENT_QUOTES, 'UTF-8'); ?>">
                                <span class="date-post"><?php echo $formattedDate; ?></span>
                            </div>
                            <div class="content-box">
                                <div class="post-author">
                                    <?php if ($showBlogAuthor): ?>
                                        <span class="fw-6"><?php echo htmlspecialchars($authorName); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($category)): ?>
                                        <span><?php echo htmlspecialchars($category); ?></span>
                                    <?php endif; ?>
                                </div>
                                <h5 class="title link"><?php echo htmlspecialchars($title); ?></h5>
                                <p class="description"><?php echo htmlspecialchars($shortDescription); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="sw-pagination sw-pagination-latest text-center"></div>
        </div>
    </div>
    </sectio