<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Additional routes
$route['home'] = 'Home/redirect_root';
$route['our-projects'] = 'Listing/index';
$route['listing'] = 'Listing/redirect_legacy';
$route['about'] = 'About/index';
$route['blog'] = 'Blog/index';
$route['blog/post/(:any)'] = 'Blog/post/$1';
$route['blog/create'] = 'Blog/create';
$route['blog/edit/(:num)'] = 'Blog/edit/$1';
$route['blog/delete/(:num)'] = 'Blog/delete/$1';
$route['blog/manage'] = 'Blog/manage';
$route['blog/search'] = 'Blog/search';
$route['contact'] = 'Contact/index';
$route['blog-detail'] = 'Blog_detail/index';
$route['property-detail'] = 'Property_detail/index';
$route['property-detail/(:any)'] = 'Property_detail/index/$1';
// Support for old HTML file names
$route['property-details-v1'] = 'Property_detail/index';
$route['property-details-v1/(:any)'] = 'Property_detail/index/$1';
$route['property-details-v2'] = 'Property_detail/index';
$route['property-details-v2/(:any)'] = 'Property_detail/index/$1';
$route['property-details-v3'] = 'Property_detail/index';
$route['property-details-v3/(:any)'] = 'Property_detail/index/$1';
$route['property-details-v4'] = 'Property_detail/index';
$route['property-details-v4/(:any)'] = 'Property_detail/index/$1';
$route['login'] = 'Login/index';
$route['register'] = 'Register/index';

// ============================================
// Authentication API Routes
// ============================================
// All routes support both underscore and hyphen formats
// Both /auth/ and /api/auth/ prefixes work the same way

// OTP Management
$route['auth/send_otp'] = 'Auth/send_otp';
$route['auth/send-otp'] = 'Auth/send_otp';
$route['auth/verify_otp'] = 'Auth/verify_otp';
$route['auth/verify-otp'] = 'Auth/verify_otp';
$route['auth/resend_otp'] = 'Auth/resend_otp';
$route['auth/resend-otp'] = 'Auth/resend_otp';

// Profile Management
$route['auth/save_profile'] = 'Auth/save_profile';
$route['auth/save-profile'] = 'Auth/save_profile';
$route['auth/update_profile'] = 'Auth/update_profile';
$route['auth/update-profile'] = 'Auth/update_profile';
$route['auth/profile'] = 'Auth/profile';

// Session Management
$route['auth/check'] = 'Auth/check';
$route['auth/check_auth'] = 'Auth/check';
$route['auth/check-auth'] = 'Auth/check';
$route['auth/refresh_session'] = 'Auth/refresh_session';
$route['auth/refresh-session'] = 'Auth/refresh_session';
$route['auth/logout'] = 'Auth/logout';

// Phone Management
$route['auth/check_phone_exists'] = 'Auth/check_phone_exists';
$route['auth/check-phone-exists'] = 'Auth/check_phone_exists';
$route['auth/check-phone'] = 'Auth/check_phone_exists';
$route['auth/change_phone'] = 'Auth/change_phone';
$route['auth/change-phone'] = 'Auth/change_phone';
$route['auth/verify_phone_change'] = 'Auth/verify_phone_change';
$route['auth/verify-phone-change'] = 'Auth/verify_phone_change';

// Account & Instruction Management
$route['deleteInstruction'] = 'Home/deleteInstruction';
$route['delete-instruction'] = 'Home/deleteInstruction';
$route['delete_account'] = 'Auth/delete_account';
$route['delete-account'] = 'Auth/delete_account';
$route['auth/delete_account'] = 'Auth/delete_account';
$route['auth/delete-account'] = 'Auth/delete_account';

// Developer API console (Postman-style tester for mobile/web devs)
$route['developer'] = 'api_developer/index';
$route['developer/catalog'] = 'api_developer/catalog';
$route['panel/api-collection'] = 'broker_admin/api_collection';
$route['panel/api-tester'] = 'broker_admin/api_collection';

// API routes (legacy contact form + JSON helpers used by PHP views / Next.js proxy)
$route['api/enquiry_store'] = 'Api/enquiry_store';
$route['api/enquiry/store'] = 'Api/enquiry_store';
$route['api/enquiry/send'] = 'Api/enquiry/send';

// Dashboard routes
$route['dashboard/wishlist'] = 'Dashboard/wishlist';
$route['dashboard/enquiries'] = 'Dashboard/enquiries';

// Admin login → broker panel (/panel) after nb_users admin auth
$route['admin'] = 'broker_admin/admin_login';
$route['admin/login'] = 'broker_admin/admin_login';
$route['admin/dashboard'] = 'Admin/dashboard';
$route['admin/enquiries'] = 'Admin/enquiries';
$route['admin/contacts'] = 'Admin/contacts';
$route['admin/logout'] = 'Admin/logout';
$route['admin/clear-cache'] = 'Admin/clear_cache_public';
$route['clear-cache'] = 'Admin/clear_cache_public';


$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Additional routes
// Note: Routes for about, our-projects (listing), and blog are defined earlier in this file
// $route['about'] = 'Home/about'; // Removed - using About controller instead (uncomment line 59)
// $route['properties'] = 'Home/properties'; // Removed - not used
// $route['listing'] = 'Home/properties'; // Removed - use Listing controller via our-projects / listing redirect
// $route['blog'] = 'Home/blog'; // Removed - using Blog controller instead (defined on line 60)
// $route['blog/(:num)'] = 'Home/blog_detail/$1'; // Removed - using Blog controller instead
// $route['contact'] = 'Home/contact'; // Removed - using Contact controller instead (defined on line 67)
// Legacy Home property detail — disabled; main app uses $route['property/(:any)'] => Nb_property (see bottom of file).
// $route['property/(:num)'] = 'Home/property_detail/$1';
// $route['property-detail/(:num)'] = 'Home/property_detail/$1';
$route['privacy-policy'] = 'Home/privacy_policy';
$route['terms-conditions'] = 'Home/terms_conditions';
$route['testimonials'] = 'Home/testimonials';

// SEO Settings
$route['admin/seo_settings'] = 'Admin/seo_settings';
$route['admin/seo_settings_save'] = 'Admin/seo_settings_save';

// Admin login page (duplicate route block — keep in sync with top)
$route['admin'] = 'broker_admin/admin_login';
$route['admin/login'] = 'broker_admin/admin_login';
$route['admin/logout'] = 'Admin/logout';
$route['admin/dashboard'] = 'Admin/dashboard';
$route['admin/properties'] = 'Admin/properties';
$route['admin/property_create'] = 'Admin/property_create';
$route['admin/property_edit/(:num)'] = 'Admin/property_edit/$1';
$route['admin/property_delete/(:num)'] = 'Admin/property_delete/$1';
$route['admin/banners'] = 'Admin/banners';
$route['admin/offer_banners'] = 'Admin/offer_banners';
$route['admin/offer_banner_create'] = 'Admin/offer_banner_create';
$route['admin/offer_banner_edit/(:num)'] = 'Admin/offer_banner_edit/$1';
$route['admin/offer_banner_delete/(:num)'] = 'Admin/offer_banner_delete/$1';
$route['admin/banner_create'] = 'Admin/banner_create';
$route['admin/banner_edit/(:num)'] = 'Admin/banner_edit/$1';
$route['admin/banner_delete/(:num)'] = 'Admin/banner_delete/$1';
$route['admin/banner_toggle/(:num)'] = 'Admin/banner_toggle/$1';
$route['admin/mobile_banners'] = 'Admin/mobile_banners';
$route['admin/mobile_banner_create'] = 'Admin/mobile_banner_create';
$route['admin/mobile_banner_edit/(:num)'] = 'Admin/mobile_banner_edit/$1';
$route['admin/mobile_banner_delete/(:num)'] = 'Admin/mobile_banner_delete/$1';
$route['admin/mobile_banner_toggle/(:num)'] = 'Admin/mobile_banner_toggle/$1';
$route['admin/enquiries'] = 'Admin/enquiries';
$route['admin/enquiry_view/(:num)'] = 'Admin/enquiry_view/$1';
$route['admin/enquiry_delete/(:num)'] = 'Admin/enquiry_delete/$1';
$route['admin/contacts'] = 'Admin/contacts';
$route['admin/contact_view/(:num)'] = 'Admin/contact_view/$1';
$route['admin/contact_delete/(:num)'] = 'Admin/contact_delete/$1';
$route['admin/cities'] = 'Admin/cities';
$route['admin/city_create'] = 'Admin/city_create';
$route['admin/location_update_order'] = 'Admin/location_update_order';
$route['admin/city_edit/(:num)'] = 'Admin/city_edit/$1';
$route['admin/city_delete/(:num)'] = 'Admin/city_delete/$1';
$route['admin/locations'] = 'Admin/locations';
$route['admin/location_create'] = 'Admin/location_create';
$route['admin/location_edit/(:num)'] = 'Admin/location_edit/$1';
$route['admin/location_delete/(:num)'] = 'Admin/location_delete/$1';
$route['admin/blogs'] = 'Admin/blogs';
$route['admin/blog_create'] = 'Admin/blog_create';
$route['admin/blog_edit/(:num)'] = 'Admin/blog_edit/$1';
$route['admin/blog_delete/(:num)'] = 'Admin/blog_delete/$1';
$route['admin/housing_news'] = 'Admin/housing_news';
$route['admin/housing_news_create'] = 'Admin/housing_news_create';
$route['admin/housing_news_edit/(:num)'] = 'Admin/housing_news_edit/$1';
$route['admin/housing_news_delete/(:num)'] = 'Admin/housing_news_delete/$1';
$route['admin/notifications'] = 'Admin/notifications';
$route['admin/notification_create'] = 'Admin/notification_create';
$route['admin/notification_edit/(:num)'] = 'Admin/notification_edit/$1';
$route['admin/notification_delete/(:num)'] = 'Admin/notification_delete/$1';
$route['admin/notification_toggle/(:num)'] = 'Admin/notification_toggle/$1';
$route['admin/reels'] = 'Admin/reels';
$route['admin/reel_create'] = 'Admin/reel_create';
$route['admin/reel_edit/(:num)'] = 'Admin/reel_edit/$1';
$route['admin/reel_delete/(:num)'] = 'Admin/reel_delete/$1';
$route['admin/reel_update_order'] = 'Admin/reel_update_order';
$route['admin/videos'] = 'Admin/videos';
$route['admin/video_create'] = 'Admin/video_create';
$route['admin/video_edit/(:num)'] = 'Admin/video_edit/$1';
$route['admin/video_delete/(:num)'] = 'Admin/video_delete/$1';
$route['admin/video_update_order'] = 'Admin/video_update_order';
$route['admin/users'] = 'Admin/users';
$route['admin/user_create'] = 'Admin/user_create';
$route['admin/user_edit/(:any)'] = 'Admin/user_edit/$1';
$route['admin/user_delete/(:any)'] = 'Admin/user_delete/$1';
$route['admin/bulk_delete_users'] = 'Admin/bulk_delete_users';
$route['admin/bulk_update_status_users'] = 'Admin/bulk_update_status_users';
$route['admin/delete_requests'] = 'Admin/delete_requests';
$route['admin/delete_request_status/(:num)'] = 'Admin/delete_request_status/$1';
$route['admin/referrals'] = 'Admin/referrals';
$route['admin/referral_create'] = 'Admin/referral_create';
$route['admin/referral_edit/(:any)'] = 'Admin/referral_edit/$1';
$route['admin/referral_delete/(:any)'] = 'Admin/referral_delete/$1';
$route['admin/wishlists'] = 'Admin/wishlists';
$route['admin/wishlist_view/(:num)'] = 'Admin/wishlist_view/$1';
$route['admin/wishlist_delete/(:num)'] = 'Admin/wishlist_delete/$1';

// API routes
$route['property/store'] = 'Property/store';
$route['contact/save'] = 'Contact/save';
$route['enquiry/save'] = 'Enquiry/save';
$route['property_search/filter'] = 'Property_search/filter';

// Mobile API routes
$route['api/mobile/home'] = 'Api_mobile/home';
$route['api/mobile/properties'] = 'Api_mobile/properties';
$route['api/mobile/properties-core'] = 'Api_mobile/properties_core';
$route['api/mobile/properties-core/(:num)'] = 'Api_mobile/property_core/$1';
$route['api/mobile/properties/featured'] = 'Api_mobile/featured_properties';
$route['api/mobile/properties/latest'] = 'Api_mobile/latest_properties';
$route['api/mobile/properties/search'] = 'Api_mobile/search_properties';
$route['api/mobile/properties/create'] = 'Api_mobile/properties_create';
$route['api/mobile/properties/update/(:num)'] = 'Api_mobile/property_update/$1';
$route['api/mobile/properties/delete/(:num)'] = 'Api_mobile/property_delete/$1';
$route['api/mobile/properties/(:num)'] = 'Api_mobile/property/$1';
$route['api/mobile/blogs'] = 'Api_mobile/blogs';
$route['api/mobile/blogs/(:num)'] = 'Api_mobile/blog/$1';
$route['api/mobile/notifications'] = 'api_nb_app/notifications';
$route['api/mobile/notifications/(:num)'] = 'api_nb_app/notification/$1';
$route['api/mobile/categories'] = 'Api_mobile/categories';
$route['api/mobile/categories/(:num)'] = 'Api_mobile/category/$1';
$route['api/mobile/cities'] = 'Api_mobile/cities';
$route['api/mobile/cities/(:num)'] = 'Api_mobile/city/$1';
$route['api/mobile/locations'] = 'Api_mobile/locations';
$route['api/mobile/locations/(:num)'] = 'Api_mobile/location/$1';
$route['api/mobile/locations/city/(:num)'] = 'Api_mobile/locations_by_city/$1';
$route['api/mobile/banners'] = 'Api_mobile/banners';
$route['api/mobile/banners/create'] = 'Api_mobile/banners_create';
$route['api/mobile/banners/update/(:num)'] = 'Api_mobile/banners_update/$1';
$route['api/mobile/offer_banner'] = 'Api_mobile/offer_banner';
$route['api/mobile/offer_banners'] = 'Api_mobile/offer_banners';
$route['api/mobile/live-updates'] = 'Api_mobile/live_updates';
$route['api/mobile/live-updates/create'] = 'Api_mobile/live_update_create';
$route['api/mobile/live-updates/update/(:num)'] = 'Api_mobile/live_update_save/$1';
$route['api/mobile/live-updates/delete/(:num)'] = 'Api_mobile/live_update_remove/$1';
$route['api/mobile/live-updates/user/(:any)'] = 'Api_mobile/live_updates_for_user/$1';
$route['api/mobile/live-updates/(:num)'] = 'Api_mobile/live_update/$1';
$route['api/mobile/housing-news'] = 'Api_mobile/housing_news';
$route['api/mobile/housing-news/create'] = 'Api_mobile/housing_news_create';
$route['api/mobile/housing-news/update/(:num)'] = 'Api_mobile/housing_news_update/$1';
$route['api/mobile/housing-news/(:num)'] = 'Api_mobile/housing_news_item/$1';
$route['api/mobile/feedback'] = 'Api_mobile/feedback';
$route['api/mobile/contact'] = 'Api_mobile/contact';
$route['api/mobile/enquiry'] = 'Api_mobile/enquiry';
$route['api/mobile/enquiries/user/(:any)'] = 'Api_mobile/enquiries_by_user/$1';
$route['api/mobile/enquiries/customer/(:num)'] = 'Api_mobile/enquiries_by_customer/$1';
$route['api/mobile/enquiries_by_customer/(:num)'] = 'Api_mobile/enquiries_by_customer/$1';

// Mobile API Authentication Routes
$route['api/mobile/send_otp'] = 'Api_mobile/send_otp';
$route['api/mobile/send-otp'] = 'Api_mobile/send_otp';
$route['api/mobile/verify_otp'] = 'Api_mobile/verify_otp';
$route['api/mobile/verify-otp'] = 'Api_mobile/verify_otp';
$route['api/mobile/resend_otp'] = 'Api_mobile/resend_otp';
$route['api/mobile/resend-otp'] = 'Api_mobile/resend_otp';
$route['api/mobile/save_profile'] = 'Api_mobile/save_profile';
$route['api/mobile/save-profile'] = 'Api_mobile/save_profile';
$route['api/mobile/update_profile'] = 'Api_mobile/update_profile';
$route['api/mobile/update-profile'] = 'Api_mobile/update_profile';
$route['api/mobile/profile'] = 'api_nb_app/profile';
$route['api/mobile/check'] = 'Api_mobile/check';
$route['api/mobile/check_auth'] = 'Api_mobile/check';
$route['api/mobile/check-auth'] = 'Api_mobile/check';
$route['api/mobile/refresh_session'] = 'Api_mobile/refresh_session';
$route['api/mobile/refresh-session'] = 'Api_mobile/refresh_session';
$route['api/mobile/logout'] = 'Api_mobile/logout';
$route['api/mobile/check_phone_exists'] = 'Api_mobile/check_phone_exists';
$route['api/mobile/check-phone-exists'] = 'Api_mobile/check_phone_exists';
$route['api/mobile/check-phone'] = 'Api_mobile/check_phone_exists';
$route['api/mobile/change_phone'] = 'Api_mobile/change_phone';
$route['api/mobile/change-phone'] = 'Api_mobile/change_phone';
$route['api/mobile/verify_phone_change'] = 'Api_mobile/verify_phone_change';
$route['api/mobile/verify-phone-change'] = 'Api_mobile/verify_phone_change';
$route['api/mobile/delete_account'] = 'Api_mobile/delete_account';
$route['api/mobile/delete-account'] = 'Api_mobile/delete_account';
$route['api/mobile/privacy-policy'] = 'Api_mobile/privacy_policy';
$route['api/mobile/terms'] = 'Api_mobile/terms';

// Wishlist
$route['api/mobile/wishlist'] = 'api_nb_app/wishlist';
$route['api/mobile/wishlist/store'] = 'api_nb_app/wishlist';
$route['api/mobile/wishlist/check'] = 'api_nb_app/wishlist_check';
$route['api/mobile/wishlist/list'] = 'api_nb_app/wishlist';
$route['api/mobile/wishlist/remove'] = 'api_nb_app/wishlist_remove';
$route['api/mobile/wishlist/delete/(:num)'] = 'api_nb_app/wishlist_remove/$1';

// Referral
$route['api/mobile/referral/apply'] = 'Api_mobile/referral_apply';
$route['api/mobile/referral/list'] = 'Api_mobile/referral_list';
$route['api/mobile/referral/stats'] = 'Api_mobile/referral_stats';

// ============ Coimbatore Properties platform (overrides; keep last) ============
$route['default_controller'] = 'Nb_home';

$route['login'] = 'Nb_auth/login';
$route['register'] = 'Nb_auth/register';
$route['logout'] = 'Nb_auth/logout';
$route['pending'] = 'Nb_auth/pending';

$route['search'] = 'Nb_search/index';
$route['search/more'] = 'Nb_search/more';

// Public property by slug — do NOT use :any here: it matches property/owner and routes to Nb_property (404).
// Owner/tenant panel URLs use the same prefix; explicit routes below also map them to dashboards.
$route['property/(?!owner$|tenant$)([^/]+)'] = 'Nb_property/view/$1';

$route['panel/auth'] = 'broker_admin/auth';
$route['panel'] = 'broker_admin/index';
$route['panel/users'] = 'broker_admin/users';
$route['panel/user/add'] = 'broker_admin/user_add';
$route['panel/user/edit/(:num)'] = 'broker_admin/user_edit/$1';
$route['panel/user/delete/(:num)'] = 'broker_admin/user_delete/$1';
$route['panel/approve-user'] = 'broker_admin/approve_user';
$route['panel/approve-property'] = 'broker_admin/approve_property';
$route['panel/update-enquiry'] = 'broker_admin/update_enquiry';
$route['panel/delete-enquiry'] = 'broker_admin/delete_enquiry';
$route['panel/properties'] = 'broker_admin/properties';
$route['panel/properties/pending'] = 'broker_admin/properties_pending';
$route['panel/property/add'] = 'broker_admin/property_add';
$route['panel/property/edit/(:num)'] = 'broker_admin/property_edit/$1';
$route['panel/property/delete/(:num)'] = 'broker_admin/property_delete/$1';
$route['panel/property/save'] = 'nb_property_form/save';
$route['panel/enquiries'] = 'broker_admin/enquiries';
$route['panel/enquiry/(:num)'] = 'broker_admin/enquiry/$1';

$route['panel/cities'] = 'broker_admin/cities';
$route['panel/city/add'] = 'broker_admin/city_add';
$route['panel/city/edit/(:num)'] = 'broker_admin/city_edit/$1';
$route['panel/city/delete/(:num)'] = 'broker_admin/city_delete/$1';

$route['panel/amenities'] = 'broker_admin/amenities';
$route['panel/amenity/add'] = 'broker_admin/amenity_add';
$route['panel/amenity/edit/(:num)'] = 'broker_admin/amenity_edit/$1';
$route['panel/amenity/delete/(:num)'] = 'broker_admin/amenity_delete/$1';
$route['panel/property-types'] = 'broker_admin/property_types';
$route['panel/property-type/add'] = 'broker_admin/property_type_add';
$route['panel/property-type/add-sub/(:num)'] = 'broker_admin/property_type_add_sub/$1';
$route['panel/property-type/edit/(:num)'] = 'broker_admin/property_type_edit/$1';
$route['panel/property-type/delete/(:num)'] = 'broker_admin/property_type_delete/$1';
$route['panel/property-type/toggle/(:num)'] = 'broker_admin/property_type_toggle/$1';
$route['api/property-types'] = 'api_property_types/index';
$route['api/property-types/flat'] = 'api_property_types/flat';
$route['api/property-types/toggle'] = 'api_property_types/toggle';
$route['panel/banners'] = 'broker_admin/banners';
$route['panel/banner/add'] = 'broker_admin/banner_add';
$route['panel/banner/edit/(:num)'] = 'broker_admin/banner_edit/$1';
$route['panel/banner/delete/(:num)'] = 'broker_admin/banner_delete/$1';
$route['panel/banner/toggle/(:num)'] = 'broker_admin/banner_toggle/$1';
$route['panel/wishlists'] = 'broker_admin/wishlists';
$route['panel/live-updates'] = 'broker_admin/live_updates';
$route['panel/live-update/edit/(:num)'] = 'broker_admin/live_update_edit/$1';
$route['panel/live-update/delete/(:num)'] = 'broker_admin/live_update_delete/$1';
$route['panel/housing-news'] = 'broker_admin/housing_news';
$route['panel/housing-news/add'] = 'broker_admin/housing_news_add';
$route['panel/housing-news/edit/(:num)'] = 'broker_admin/housing_news_edit/$1';
$route['panel/housing-news/delete/(:num)'] = 'broker_admin/housing_news_delete/$1';
$route['panel/feedbacks'] = 'broker_admin/feedbacks';
$route['panel/api-collection'] = 'broker_admin/api_collection';
$route['panel/api-tester'] = 'broker_admin/api_collection';
$route['panel/delete-requests'] = 'broker_admin/delete_requests';
$route['panel/delete-request/status/(:num)'] = 'broker_admin/delete_request_update_status/$1';
$route['panel/notifications'] = 'broker_admin/notifications';
$route['panel/notification/create'] = 'broker_admin/notification_create';
$route['panel/notification/edit/(:num)'] = 'broker_admin/notification_edit/$1';
$route['panel/notification/delete/(:num)'] = 'broker_admin/notification_delete/$1';
$route['panel/notification/toggle/(:num)'] = 'broker_admin/notification_toggle/$1';

$route['user/feedback'] = 'feedback_user/index';
$route['user/live-updates'] = 'live_updates_user/index';
$route['user/live-update/add'] = 'live_updates_user/add';
$route['user/live-update/edit/(:num)'] = 'live_updates_user/edit/$1';
$route['user/live-update/delete/(:num)'] = 'live_updates_user/delete/$1';
$route['user/wishlist'] = 'wishlist_user/index';
$route['user/wishlist/remove/(:num)'] = 'wishlist_user/remove/$1';

// POST api/property/save — routed to Nb_property_form (Api.php blocks controllers/api/).
$route['api/property/save'] = 'nb_property_form/save';

// NoBroker JSON API (Bearer token; base path api/nb/) — used by Next.js frontend
$route['api/nb/update-profile'] = 'api_nb_app/update_profile';
$route['api/nb/register'] = 'api_nb_app/register';
$route['api/nb/login'] = 'api_nb_app/login';
$route['api/nb/logout'] = 'api_nb_app/logout';
$route['api/nb/me'] = 'api_nb_app/me';
$route['api/nb/delete-account'] = 'api_nb_app/delete_account';
$route['api/nb/wishlist'] = 'api_nb_app/wishlist';
$route['api/nb/wishlist/toggle'] = 'api_nb_app/wishlist_toggle';
$route['api/nb/wishlist/check'] = 'api_nb_app/wishlist_check';
$route['api/nb/enquiry'] = 'api_nb_app/enquiry';
$route['api/nb/search'] = 'api_nb_app/search';
$route['api/nb/site-banners'] = 'api_nb_app/site_banners';
$route['api/nb/home-banners'] = 'api_nb_app/home_banners';
$route['api/nb/notifications'] = 'api_nb_app/notifications';
$route['api/nb/notifications/(:num)'] = 'api_nb_app/notification/$1';
$route['api/nb/cities'] = 'api_nb_app/cities';
$route['api/nb/explore-cities'] = 'api_nb_app/explore_cities';
$route['api/nb/property-type-counts'] = 'api_nb_app/property_type_counts';

// Next.js web API (production on PHP-only hosting — same paths as app/api/*)
$route['api/feedback'] = 'api_web/feedback';
$route['api/owner/dashboard'] = 'api_web/owner_dashboard';
$route['api/owner/listings'] = 'api_web/owner_listings';
$route['api/owner/enquiries'] = 'api_web/owner_enquiries';
$route['api/tenant/enquiries'] = 'api_web/tenant_enquiries';
$route['api/properties/(:any)'] = 'api_web/property/$1';

/* Legacy JSON helpers — explicit routes (avoid ambiguity with legacy api/* routes). */
$route['api/cities'] = 'Api/cities';
$route['api/blogs'] = 'Api/blogs';

// Prepend owner/tenant routes so CodeIgniter tries them before hundreds of legacy routes.
// Also supports an extra leading "property/" segment (e.g. /property/tenant/dashboard when base path is wrong).
$nb_uri_priority = array(
    'property/owner' => 'owner/dashboard/index',
    'property/owner/dashboard' => 'owner/dashboard/index',
    'property/owner/listings' => 'owner/listings/index',
    'property/owner/enquiries' => 'owner/enquiries/index',
    'property/owner/property/add' => 'owner/property/add',
    'property/owner/property/edit/(:num)' => 'owner/property/edit/$1',
    'property/tenant' => 'tenant/dashboard/index',
    'property/tenant/dashboard' => 'tenant/dashboard/index',
    'property/tenant/enquiries' => 'tenant/enquiries/index',
    'owner/dashboard' => 'owner/dashboard/index',
    'owner/listings' => 'owner/listings/index',
    'owner/enquiries' => 'owner/enquiries/index',
    'owner/property/add' => 'owner/property/add',
    'owner/property/edit/(:num)' => 'owner/property/edit/$1',
    'tenant/dashboard' => 'tenant/dashboard/index',
    'tenant/enquiries' => 'tenant/enquiries/index',
);
$route = $nb_uri_priority + $route;

/*
 * Re-assert owner/tenant routes last so nothing in $route overrides them. Optional: Dashboard::dashboard()
 * aliases exist on owner/tenant Dashboard controllers for odd URI mappings.
 */
$route['owner/dashboard'] = 'owner/dashboard/index';
$route['owner/listings'] = 'owner/listings/index';
$route['owner/enquiries'] = 'owner/enquiries/index';
$route['owner/property/add'] = 'owner/property/add';
$route['owner/property/edit/(:num)'] = 'owner/property/edit/$1';
$route['tenant/dashboard'] = 'tenant/dashboard/index';
$route['tenant/enquiries'] = 'tenant/enquiries/index';
$route['property/owner'] = 'owner/dashboard/index';
$route['property/owner/dashboard'] = 'owner/dashboard/index';
$route['property/owner/listings'] = 'owner/listings/index';
$route['property/owner/enquiries'] = 'owner/enquiries/index';
$route['property/owner/property/add'] = 'owner/property/add';
$route['property/owner/property/edit/(:num)'] = 'owner/property/edit/$1';
$route['property/tenant'] = 'tenant/dashboard/index';
$route['property/tenant/dashboard'] = 'tenant/dashboard/index';
$route['property/tenant/enquiries'] = 'tenant/enquiries/index';
