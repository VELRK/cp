<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Db_store {
    private $CI;
    private $field_cache = array();
    private $column_meta_cache = array();

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->ensure_schema();
    }

    private function ensure_schema() {
        $queries = array(
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fullName VARCHAR(255) NOT NULL,
                email VARCHAR(255) NULL UNIQUE,
                password_hash VARCHAR(255) NULL,
                countryCode VARCHAR(20) DEFAULT '+91',
                phoneNumber VARCHAR(30) NULL UNIQUE,
                state VARCHAR(120) DEFAULT '',
                city VARCHAR(120) DEFAULT '',
                pinCode VARCHAR(20) DEFAULT '',
                referralCode VARCHAR(100) DEFAULT '',
                profilePic TEXT NULL,
                loginType VARCHAR(40) DEFAULT 'Web',
                fcmToken TEXT NULL,
                isActive TINYINT(1) DEFAULT 1,
                createdAt DATETIME NULL,
                updatedAt DATETIME NULL,
                lastLoginAt DATETIME NULL
            )",
            "CREATE TABLE IF NOT EXISTS cities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cityName VARCHAR(120) NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS locations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                city_id INT NULL,
                locationName VARCHAR(160) NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                categoryID VARCHAR(80) NULL,
                categoryName VARCHAR(160) NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS properties (
                id INT AUTO_INCREMENT PRIMARY KEY,
                propertyName VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NULL,
                propertyPriceRange DECIMAL(14,2) NULL,
                propertyPriceRangeText VARCHAR(120) NULL,
                propertyRange VARCHAR(120) NULL,
                `desc` TEXT NULL,
                propertiesMainImage TEXT NULL,
                projectThumbnailImage TEXT NULL,
                projectVideoUrl TEXT NULL,
                propertySliderImages LONGTEXT NULL,
                location_id INT NULL,
                city_id INT NULL,
                category_id INT NULL,
                beds INT NULL,
                baths INT NULL,
                sqft INT NULL,
                `index` INT NULL,
                orderValue INT NULL,
                is_recommended TINYINT(1) DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active',
                createdAt DATETIME NULL
            )",
            "CREATE TABLE IF NOT EXISTS banners (
                id INT AUTO_INCREMENT PRIMARY KEY,
                imageUrl TEXT NOT NULL,
                status VARCHAR(20) DEFAULT 'active',
                createdAt DATETIME NULL
            )",
            "CREATE TABLE IF NOT EXISTS videos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                videoUrl TEXT NOT NULL,
                title VARCHAR(255) NULL,
                index_no INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active',
                createdAt DATETIME NULL
            )",
            "CREATE TABLE IF NOT EXISTS reels_videos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                videoUrl TEXT NOT NULL,
                title VARCHAR(255) NULL,
                index_no INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active',
                createdAt DATETIME NULL
            )",
            "CREATE TABLE IF NOT EXISTS blogs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NULL,
                shortdescription TEXT NULL,
                description TEXT NULL,
                content LONGTEXT NULL,
                category VARCHAR(120) NULL,
                authorname VARCHAR(120) NULL,
                coverImageUrl TEXT NULL,
                imageUrls LONGTEXT NULL,
                meta_title VARCHAR(255) NULL,
                meta_description TEXT NULL,
                status VARCHAR(20) DEFAULT 'active',
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )",
            "CREATE TABLE IF NOT EXISTS enquiries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                propertyId VARCHAR(80) NULL,
                propertyName VARCHAR(255) NULL,
                propertyPrice VARCHAR(120) NULL,
                coverImageUrl TEXT NULL,
                userId VARCHAR(80) NULL,
                userName VARCHAR(255) NULL,
                userEmail VARCHAR(255) NULL,
                userPhone VARCHAR(40) NULL,
                city VARCHAR(120) NULL,
                enquiryType VARCHAR(80) DEFAULT 'property_enquiry',
                status VARCHAR(30) DEFAULT 'new',
                message TEXT NULL,
                ipAddress VARCHAR(60) NULL,
                userAgent TEXT NULL,
                userDetails LONGTEXT NULL,
                createdAt DATETIME NULL
            )",
            "CREATE TABLE IF NOT EXISTS contacts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(40) NOT NULL,
                subject VARCHAR(255) NULL,
                message TEXT NOT NULL,
                status VARCHAR(30) DEFAULT 'new',
                userDetails LONGTEXT NULL,
                ip_address VARCHAR(60) NULL,
                user_agent TEXT NULL,
                createdAt DATETIME NULL
            )",
            "CREATE TABLE IF NOT EXISTS customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NULL,
                email VARCHAR(255) NULL UNIQUE,
                phone VARCHAR(40) NULL,
                contactCount INT DEFAULT 0,
                status VARCHAR(30) DEFAULT 'active',
                source VARCHAR(80) NULL,
                lastContactDate DATETIME NULL,
                lastEnquiryProperty VARCHAR(255) NULL,
                lastEnquiryPropertyId VARCHAR(80) NULL,
                ip_address VARCHAR(60) NULL,
                user_agent TEXT NULL,
                createdAt DATETIME NULL,
                updatedAt DATETIME NULL
            )",
            "CREATE TABLE IF NOT EXISTS video_play_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                videoId VARCHAR(80) NOT NULL,
                videoUrl TEXT NULL,
                playTime DATETIME NULL,
                userId VARCHAR(80) NULL,
                ipAddress VARCHAR(60) NULL,
                userAgent TEXT NULL,
                createdAt DATETIME NULL,
                ts BIGINT NULL
            )"
        );

        foreach ($queries as $query) {
            $this->CI->db->query($query);
        }

        // Backward-compatible schema upgrades for existing databases.
        if ($this->CI->db->table_exists('properties') && !$this->CI->db->field_exists('slug', 'properties')) {
            $this->CI->db->query("ALTER TABLE properties ADD COLUMN slug VARCHAR(255) NULL");
        }
        if ($this->CI->db->table_exists('blogs') && !$this->CI->db->field_exists('slug', 'blogs')) {
            $this->CI->db->query("ALTER TABLE blogs ADD COLUMN slug VARCHAR(255) NULL");
        }
        if ($this->CI->db->table_exists('enquiries') && !$this->CI->db->field_exists('city', 'enquiries')) {
            $this->CI->db->query("ALTER TABLE enquiries ADD COLUMN city VARCHAR(120) NULL");
        }
    }

    public function formatPriceInRupees($price) {
        if ($price === null || $price === '') {
            return 'Price on Request';
        }
        return 'INR ' . number_format((float)$price, 0);
    }

    private function has_field($table, $field) {
        $key = $table . ':' . $field;
        if (!array_key_exists($key, $this->field_cache)) {
            $this->field_cache[$key] = $this->CI->db->field_exists($field, $table);
        }
        return $this->field_cache[$key];
    }

    private function first_field($table, $candidates, $fallback = null) {
        foreach ($candidates as $candidate) {
            if ($this->has_field($table, $candidate)) {
                return $candidate;
            }
        }
        return $fallback;
    }

    private function property_value($row, $candidates, $default = '') {
        foreach ($candidates as $candidate) {
            if (isset($row[$candidate]) && $row[$candidate] !== null) {
                return $row[$candidate];
            }
        }
        return $default;
    }

    private function slugify($value) {
        $value = strtolower(trim((string)$value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
        $value = trim((string)$value, '-');
        return $value !== '' ? $value : 'item';
    }

    private function unique_slug($table, $base, $id = null) {
        $slug = $this->slugify($base);
        $candidate = $slug;
        $counter = 1;

        while (true) {
            $this->CI->db->from($table)->where('slug', $candidate);
            if ($id !== null && $id !== '') {
                $this->CI->db->where('id !=', $id);
            }
            $exists = $this->CI->db->count_all_results() > 0;
            if (!$exists) {
                return $candidate;
            }
            $counter++;
            $candidate = $slug . '-' . $counter;
        }
    }

    private function get_location_table() {
        if ($this->CI->db->table_exists('location')) {
            return 'location';
        }
        if ($this->CI->db->table_exists('locations')) {
            return 'locations';
        }
        return null;
    }

    private function get_city_table() {
        // Check 'city' first since that's the actual table name
        if ($this->CI->db->table_exists('city')) {
            return 'city';
        }
        if ($this->CI->db->table_exists('cities')) {
            return 'cities';
        }
        return null;
    }

    private function get_video_table() {
        foreach (array('videos', 'video', 'vide0') as $candidateTable) {
            if ($this->CI->db->table_exists($candidateTable)) {
                return $candidateTable;
            }
        }
        return null;
    }

    private function get_reels_table() {
        foreach (array('reelsvideos', 'reels_videos') as $candidateTable) {
            if ($this->CI->db->table_exists($candidateTable)) {
                return $candidateTable;
            }
        }
        return null;
    }

    private function decode_json_assoc($value) {
        if (!is_string($value) || $value === '') {
            return array();
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : array();
    }

    private function decode_json_list($value) {
        if (is_array($value)) {
            return array_values(array_filter($value, function($item) {
                return is_string($item) && trim($item) !== '';
            }));
        }

        if (!is_string($value) || trim($value) === '') {
            return array();
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded, function($item) {
                return is_string($item) && trim($item) !== '';
            }));
        }

        // Fallback for comma/newline separated URLs stored as plain text.
        $parts = preg_split('/[\r\n,]+/', $value);
        if (!is_array($parts)) {
            return array();
        }

        $items = array();
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $items[] = $part;
            }
        }
        return $items;
    }

    private function get_column_meta($table, $field) {
        $key = $table . ':' . $field;
        if (array_key_exists($key, $this->column_meta_cache)) {
            return $this->column_meta_cache[$key];
        }

        $this->column_meta_cache[$key] = null;
        if (!$this->CI->db->table_exists($table)) {
            return null;
        }

        $fields = $this->CI->db->field_data($table);
        foreach ($fields as $meta) {
            if (isset($meta->name) && $meta->name === $field) {
                $this->column_meta_cache[$key] = $meta;
                break;
            }
        }
        return $this->column_meta_cache[$key];
    }

    private function generate_document_id($prefix = 'id_') {
        try {
            return $prefix . bin2hex(random_bytes(8));
        } catch (Exception $e) {
            return $prefix . md5(uniqid('', true));
        }
    }

    private function normalize_property_row($row) {
        $locationInfo = array();
        $cityInfo = array();
        $categoryInfo = array();

        $locationInfoRaw = $this->property_value($row, array('locationinfo', 'locationInfo'), '');
        $cityInfoRaw = $this->property_value($row, array('cityinfo', 'cityInfo'), '');
        $categoryInfoRaw = $this->property_value($row, array('categoryinfo', 'categoryInfo'), '');

        if (!empty($locationInfoRaw)) {
            $locationInfo = $this->decode_json_assoc($locationInfoRaw);
        }
        if (!empty($cityInfoRaw)) {
            $cityInfo = $this->decode_json_assoc($cityInfoRaw);
        }
        if (!empty($categoryInfoRaw)) {
            $categoryInfo = $this->decode_json_assoc($categoryInfoRaw);
        }

        // New schema: location is text, city is text, category is text
        $locationName = isset($locationInfo['locationName']) ? $locationInfo['locationName'] : $this->property_value($row, array('location', 'locationName'), '');
        
        // Get city name from joined table first, then from cityInfo, then from row
        $cityName = $this->property_value($row, array('city_name_joined'), '');
        if (empty($cityName)) {
            $cityName = isset($cityInfo['cityName']) ? $cityInfo['cityName'] : '';
        }
        if (empty($cityName)) {
            // New schema uses 'city' column (text)
            $cityName = $this->property_value($row, array('city', 'cityname', 'cityName'), '');
        }
        
        // New schema uses 'category' column (text)
        $categoryName = isset($categoryInfo['categoryName']) ? $categoryInfo['categoryName'] : $this->property_value($row, array('category', 'categoryname', 'categoryName', 'categoryid', 'categoryID'), '');

        // New schema uses 'gallery' column (JSON)
        $slider = $this->property_value($row, array('gallery', 'propertysliderimages', 'propertySliderImages'), array());
        if (is_string($slider)) {
            $decodedSlider = $this->decode_json_assoc($slider);
            // Handles both JSON arrays and wrapped JSON objects.
            $slider = array_values($decodedSlider === array_values($decodedSlider) ? $decodedSlider : array_filter($decodedSlider));
        }
        if (!is_array($slider)) {
            $slider = array();
        }

        // New schema uses 'is_featured' instead of 'is_recommended'
        $isFeatured = (int)$this->property_value($row, array('is_featured', 'isfeatured', 'isFeatured', 'is_recommended'), 0);
        $isLatest = (int)$this->property_value($row, array('is_latest', 'isLatest'), 0);

        // Decode nearby and features from JSON if they're strings
        $nearbyRaw = $this->property_value($row, array('nearby'), '');
        $nearby = array();
        if (!empty($nearbyRaw)) {
            if (is_string($nearbyRaw)) {
                $nearby = json_decode($nearbyRaw, true);
                if (!is_array($nearby)) {
                    $nearby = array();
                }
            } elseif (is_array($nearbyRaw)) {
                $nearby = $nearbyRaw;
            }
        }
        
        $featuresRaw = $this->property_value($row, array('features'), '');
        $features = array();
        if (!empty($featuresRaw)) {
            if (is_string($featuresRaw)) {
                $features = json_decode($featuresRaw, true);
                if (!is_array($features)) {
                    $features = array();
                }
            } elseif (is_array($featuresRaw)) {
                $features = $featuresRaw;
            }
        }

        // New schema column mappings
        $normalized = array(
            'id' => (string)$this->property_value($row, array('id'), ''),
            'propertyName' => $this->property_value($row, array('name', 'propertyname', 'propertyName'), ''),
            'name' => $this->property_value($row, array('name', 'propertyname', 'propertyName'), ''),
            'slug' => $this->property_value($row, array('slug'), ''),
            'propertyRange' => $this->property_value($row, array('propertyrange', 'propertyRange'), ''),
            'propertyPriceRange' => $this->property_value($row, array('price', 'propertypricerange', 'propertyPriceRange'), 0),
            'price' => $this->property_value($row, array('price', 'propertypricerange', 'propertyPriceRange'), 0),
            'propertyPriceRangeText' => $this->property_value($row, array('propertypricerangetext', 'propertyPriceRangeText'), ''),
            'propertiesMainImage' => $this->property_value($row, array('main_image', 'propertiesmainimage', 'propertiesMainImage'), ''),
            'main_image' => $this->property_value($row, array('main_image', 'propertiesmainimage', 'propertiesMainImage'), ''),
            'projectThumbnailImage' => $this->property_value($row, array('projectthumbnailimage', 'projectThumbnailImage'), ''),
            'projectVideoUrl' => $this->property_value($row, array('video', 'projectvideourl', 'projectVideoUrl'), ''),
            'video' => $this->property_value($row, array('video', 'projectvideourl', 'projectVideoUrl'), ''),
            'propertySliderImages' => $slider,
            'gallery' => $slider,
            'desc' => $this->property_value($row, array('description', 'desc'), ''),
            'description' => $this->property_value($row, array('description', 'desc'), ''),
            'location' => $this->property_value($row, array('location'), ''),
            'locationid' => '', // New schema stores location as text, not ID
            'city' => $cityName,
            'cityid' => '', // New schema stores city as text, not ID
            'category' => $categoryName,
            'categoryid' => '', // New schema stores category as text, not ID
            'type' => $this->property_value($row, array('type'), ''),
            'rating' => (int)$this->property_value($row, array('rating'), 0),
            'floorplan' => $this->property_value($row, array('floorplan'), ''),
            'locationimg' => $this->property_value($row, array('locationimg', 'locationImg'), ''),
            'nearby' => $nearby,
            'features' => $features,
            'index' => $this->property_value($row, array('index'), 999),
            'orderValue' => $this->property_value($row, array('ordervalue', 'orderValue'), 999),
            'isFeatured' => $isFeatured,
            'is_featured' => $isFeatured,
            'isfeatured' => $isFeatured,
            'is_latest' => $isLatest,
            'isLatest' => $isLatest,
            'createdAt' => $this->property_value($row, array('created_at', 'createdAt'), ''),
            'created_at' => $this->property_value($row, array('created_at', 'createdAt'), ''),
            'locationInfo' => array('locationName' => $locationName),
            'cityInfo' => array('cityName' => $cityName),
            'categoryInfo' => array('categoryName' => $categoryName)
        );
        
        // Handle amenities/propertiesDetails
        $amenitiesRaw = $this->property_value($row, array('amenities', 'amenity', 'amenitiesdetails', 'propertiesdetails', 'propertiesDetails'), '');
        if (!empty($amenitiesRaw)) {
            $amenities = $this->decode_json_assoc($amenitiesRaw);
            if (is_array($amenities) && !empty($amenities)) {
                $normalized['amenities'] = $amenities;
                $normalized['propertiesDetails'] = $amenities; // Also map to propertiesDetails for backward compatibility
            }
        }
        
        return $normalized;
    }

    private function normalize_enquiry_row($row) {
        $userDetailsRaw = $this->property_value($row, array('userDetails', 'userdetails', 'userdata'), '');
        $propertyDetailsRaw = $this->property_value($row, array('propertyData', 'propertydata', 'propertyDetails', 'propertydetails'), '');

        $userDetails = array();
        if (is_array($userDetailsRaw)) {
            $userDetails = $userDetailsRaw;
        } elseif (is_string($userDetailsRaw) && $userDetailsRaw !== '') {
            $userDetails = $this->decode_json_assoc($userDetailsRaw);
        }

        $propertyDetails = array();
        if (is_array($propertyDetailsRaw)) {
            $propertyDetails = $propertyDetailsRaw;
        } elseif (is_string($propertyDetailsRaw) && $propertyDetailsRaw !== '') {
            $propertyDetails = $this->decode_json_assoc($propertyDetailsRaw);
        }

        $createdAt = $this->property_value($row, array('createdAt', 'createdat', 'created_at', 'enquirytime'), '');
        $propertyId = $this->property_value($row, array('propertyId', 'propertyid'), '');

        return array(
            'id' => (string)$this->property_value($row, array('id'), ''),
            'propertyId' => $propertyId,
            'propertyName' => $this->property_value($row, array('propertyName', 'propertyname'), ''),
            'propertyPrice' => $this->property_value($row, array('propertyPrice', 'propertyprice'), ''),
            'coverImageUrl' => $this->property_value($row, array('coverImageUrl', 'coverimageurl'), ''),
            'userId' => $this->property_value($row, array('userId', 'userid'), ''),
            'userName' => $this->property_value($row, array('userName', 'username', 'name'), ''),
            'userEmail' => $this->property_value($row, array('userEmail', 'useremail', 'email'), ''),
            'userPhone' => $this->property_value($row, array('userPhone', 'userphone', 'phone'), ''),
            'enquiryType' => $this->property_value($row, array('enquiryType', 'enquirytype'), ''),
            'status' => $this->property_value($row, array('status'), 'new'),
            'message' => $this->property_value($row, array('message'), ''),
            'ipAddress' => $this->property_value($row, array('ipAddress', 'ipaddress'), ''),
            'userAgent' => $this->property_value($row, array('userAgent', 'useragent'), ''),
            'createdAt' => $createdAt,
            'updatedAt' => $this->property_value($row, array('updatedAt', 'updated_at'), ''),
            'enquiryState' => $this->property_value($row, array('enquiryState', 'enquirystate'), ''),
            'propertyDetails' => $propertyDetails,
            'userDetails' => $userDetails
        );
    }

    private function get_banner_table() {
        if ($this->CI->db->table_exists('mainbanner')) {
            return 'mainbanner';
        }
        if ($this->CI->db->table_exists('banners')) {
            return 'banners';
        }
        return null;
    }

    private function normalize_banner_row($row) {
        $imageUrl = $this->property_value($row, array('imageUrl', 'imageurl', 'image_path', 'imagepath', 'banner_image', 'bannerimage'), '');
        $createdAt = $this->property_value($row, array('createdAt', 'createdat', 'created_at'), '');
        $updatedAt = $this->property_value($row, array('updatedAt', 'updatedat', 'updated_at'), '');
        $status = strtolower((string)$this->property_value($row, array('status'), 'inactive'));
        $status = ($status === 'active') ? 'active' : 'inactive';

        $row['imageUrl'] = $imageUrl;
        $row['createdAt'] = $createdAt;
        $row['updatedAt'] = $updatedAt;
        $row['status'] = $status;
        return $row;
    }

    public function getCities() {
        $table = $this->get_city_table();
        if ($table === null) {
            return array('success' => true, 'cities' => array());
        }
        
        // Get all rows from city table - new schema uses 'name' column
        $rows = $this->CI->db->from($table)->where('status', 'active')->order_by('name', 'ASC')->get()->result_array();
        $cities = array();
        
        foreach ($rows as $row) {
            $city = array(
                'id' => $this->property_value($row, array('id'), ''),
                'name' => $this->property_value($row, array('name'), ''),
                'cityname' => $this->property_value($row, array('name', 'cityname', 'cityName'), ''), // For backward compatibility
                'cityid' => (string)$this->property_value($row, array('id'), ''), // cityid equals id
                'image' => $this->property_value($row, array('image'), ''),
                'status' => $this->property_value($row, array('status'), 'active')
            );
            
            $cities[] = $city;
        }
        
        return array('success' => true, 'cities' => $cities);
    }

    public function getLocationsList() {
        $locationTable = $this->get_location_table();
        if ($locationTable === null) {
            return array('success' => true, 'locations' => array());
        }

        // New schema: locations table has 'name' column, 'city_id' column
        $nameCol = $this->first_field($locationTable, array('name', 'locationname', 'locationName'), 'name');
        $cityCol = $this->first_field($locationTable, array('city_id', 'cityid'), 'city_id');
        $statusCol = $this->first_field($locationTable, array('status'), null);

        $orderCol = $this->first_field($locationTable, array('order', 'ordervalue', 'orderValue'), 'order');
        
        $this->CI->db->select('id, ' . $nameCol . ' AS locationName, ' . $cityCol . ' AS city_id');
        $this->CI->db->from($locationTable);
        if ($statusCol !== null) {
            $this->CI->db->where($statusCol, 'active');
        }
        // Order by order column first (ASC), then by name
        if ($orderCol !== null) {
            $this->CI->db->order_by('`' . $orderCol . '`', 'ASC');
        }
        $this->CI->db->order_by($nameCol, 'ASC');
        $rows = $this->CI->db->get()->result_array();

        $locations = array();
        foreach ($rows as $row) {
            $locations[] = array(
                'id' => isset($row['id']) ? $row['id'] : '',
                'city_id' => isset($row['city_id']) ? $row['city_id'] : '',
                'locationName' => isset($row['locationName']) ? $row['locationName'] : '',
                'name' => isset($row['locationName']) ? $row['locationName'] : ''
            );
        }

        return array('success' => true, 'locations' => $locations);
    }

    public function getCategories() {
        // New schema: categories table has 'category_name' column
        $nameCol = $this->first_field('categories', array('category_name', 'categoryName', 'categoryname'), 'category_name');
        $statusCol = $this->first_field('categories', array('status'), null);

        $this->CI->db->from('categories');
        if ($statusCol !== null) {
            $this->CI->db->where($statusCol, 'active');
        }
        $this->CI->db->order_by($nameCol, 'ASC');
        $rows = $this->CI->db->get()->result_array();
        
        $categories = array();
        foreach ($rows as $row) {
            $categories[] = array(
                'id' => isset($row['id']) ? $row['id'] : '',
                'categoryID' => isset($row['id']) ? (string)$row['id'] : '',
                'categoryName' => isset($row[$nameCol]) ? $row[$nameCol] : '',
                'category_name' => isset($row[$nameCol]) ? $row[$nameCol] : '',
                'image' => isset($row['image']) ? $row['image'] : ''
            );
        }
        return array('success' => true, 'categories' => $categories);
    }

    public function sortProperties($properties, $sort) {
        if (!is_array($properties)) {
            return array();
        }

        usort($properties, function($a, $b) use ($sort) {
            $aPrice = isset($a['propertyPriceRange']) ? (float)$a['propertyPriceRange'] : 0;
            $bPrice = isset($b['propertyPriceRange']) ? (float)$b['propertyPriceRange'] : 0;
            $aDate = isset($a['createdAt']) ? strtotime($a['createdAt']) : 0;
            $bDate = isset($b['createdAt']) ? strtotime($b['createdAt']) : 0;

            switch ($sort) {
                case 'oldest':
                    return $aDate <=> $bDate;
                case 'price_low':
                    return $aPrice <=> $bPrice;
                case 'price_high':
                    return $bPrice <=> $aPrice;
                case 'newest':
                default:
                    return $bDate <=> $aDate;
            }
        });

        return $properties;
    }

    public function getProperties($limit = 30, $offset = 0, $sort = 'newest', $price_min = null, $price_max = null, $city = null, $location = null, $category = null) {
        $db = $this->CI->db;
        // New schema: price, city (text), location (text), category (text)
        $priceCol = $this->first_field('properties', array('price', 'propertyPriceRange', 'propertypricerange'), 'price');
        $cityCol = $this->first_field('properties', array('city', 'city_id', 'cityid'), 'city');
        $locationCol = $this->first_field('properties', array('location', 'location_id', 'locationid'), 'location');
        $categoryCol = $this->first_field('properties', array('category', 'category_id', 'categoryid'), 'category');
        $createdCol = $this->first_field('properties', array('created_at', 'createdAt'), 'created_at');
        $statusCol = $this->first_field('properties', array('status'), null);
        $featuredCol = $this->first_field('properties', array('is_featured', 'is_recommended', 'isrecommended'), 'is_featured');

        // Join with city table - new schema: city is text, join on city name
        $cityTable = $this->get_city_table();
        $db->from('properties p');
        
        if ($cityTable !== null) {
            $cityNameCol = $this->first_field($cityTable, array('name', 'cityname', 'cityName'), 'name');
            
            // Left join with city table on city name (new schema stores city as text)
            $db->join($cityTable . ' c', 'p.' . $cityCol . ' = c.' . $cityNameCol, 'left');
            
            // Select city name from joined table
            if ($cityNameCol !== null) {
                $db->select('p.*, c.' . $cityNameCol . ' AS city_name_joined, c.id AS city_table_id');
            }
        }
        
        if ($statusCol !== null) {
            $db->where('p.' . $statusCol, 'active');
        }

        if ($price_min !== null && $price_min !== '') {
            $db->where('p.' . $priceCol . ' >=', (float)$price_min);
        }
        if ($price_max !== null && $price_max !== '') {
            $db->where('p.' . $priceCol . ' <=', (float)$price_max);
        }
        if (!empty($city)) {
            // New schema: city is text column, filter by city name
            $db->where('p.' . $cityCol, $city);
        }
        if (!empty($location)) {
            // New schema: location is text column
            $db->where('p.' . $locationCol, $location);
        }
        if (!empty($category)) {
            // New schema: category is text column
            $db->where('p.' . $categoryCol, $category);
        }

        switch ($sort) {
            case 'oldest':
                $db->order_by('p.' . $createdCol, 'ASC');
                break;
            case 'price_low':
                $db->order_by('p.' . $priceCol, 'ASC');
                break;
            case 'price_high':
                $db->order_by('p.' . $priceCol, 'DESC');
                break;
            case 'newest':
            default:
                $db->order_by('p.' . $createdCol, 'DESC');
                break;
        }

        if ((int)$limit > 0) {
            $db->limit((int)$limit, (int)$offset);
        }

        $rows = $db->get()->result_array();
        $properties = array();
        foreach ($rows as $row) {
            $normalized = $this->normalize_property_row($row);
            // New schema uses is_featured, but keep is_recommended for backward compatibility
            if ($featuredCol !== null) {
                $normalized['is_recommended'] = $this->property_value($row, array($featuredCol), 0);
            }
            $properties[] = $normalized;
        }
        return array('success' => true, 'properties' => $properties);
    }

    public function getPropertyById($propertyId) {
        $this->CI->db->from('properties p');
        $this->CI->db->where('p.id', $propertyId);
        $row = $this->CI->db->get()->row_array();

        if (!$row) {
            return array('success' => false, 'error' => 'Property not found');
        }

        return array('success' => true, 'property' => $this->normalize_property_row($row));
    }

    public function getPropertyBySlug($slug) {
        $row = $this->CI->db->from('properties')->where('slug', $slug)->get()->row_array();
        if (!$row) {
            return array('success' => false, 'error' => 'Property not found');
        }
        return array('success' => true, 'property' => $this->normalize_property_row($row));
    }

    public function getAllProductsAdmin() {
        $rows = $this->CI->db->from('properties')->order_by('id', 'DESC')->get()->result_array();
        $products = array();
        foreach ($rows as $row) {
            $products[] = $this->normalize_property_row($row);
        }
        return array('success' => true, 'products' => $products);
    }

    public function saveProduct($data, $id = null) {
        if (is_string($id)) {
            $id = trim($id);
        }
        if ($id === 'null' || $id === 'undefined') {
            $id = '';
        }

        $name = isset($data['propertyName']) ? trim((string)$data['propertyName']) : '';
        if ($name === '') {
            return array('success' => false, 'error' => 'Property name is required');
        }
        $payload = array();

        $nameCol = $this->first_field('properties', array('propertyName', 'propertyname', 'name', 'title'), null);
        if ($nameCol === null) {
            return array('success' => false, 'error' => 'No valid property name column found in properties table');
        }
        $payload[$nameCol] = $name;

        $fieldMap = array(
            'propertyPriceRange' => array('propertyPriceRange', 'propertypricerange', 'price', 'pricerange'),
            'propertyPriceRangeText' => array('propertyPriceRangeText', 'propertypricerangetext', 'pricetext'),
            'propertyRange' => array('propertyRange', 'propertyrange', 'range'),
            'desc' => array('desc', 'description'),
            'propertiesMainImage' => array('propertiesMainImage', 'propertiesmainimage', 'mainimage', 'image'),
            'projectThumbnailImage' => array('projectThumbnailImage', 'projectthumbnailimage', 'thumbnail', 'thumbnailimage'),
            'projectVideoUrl' => array('projectVideoUrl', 'projectvideourl', 'videourl', 'videoUrl'),
            'category_id' => array('category_id', 'categoryid', 'category'),
            'status' => array('status')
        );

        foreach ($fieldMap as $inputKey => $dbFields) {
            $dbCol = $this->first_field('properties', $dbFields, null);
            if ($dbCol === null || !isset($data[$inputKey])) {
                continue;
            }

            if ($inputKey === 'orderValue') {
                $payload[$dbCol] = (int)$data[$inputKey];
            } else {
                $payload[$dbCol] = $data[$inputKey];
            }
        }

        // Handle location_id - store location text (name) instead of ID
        if (isset($data['location_id']) && !empty($data['location_id'])) {
            $locationTable = $this->get_location_table();
            if ($locationTable !== null) {
                $nameCol = $this->first_field($locationTable, array('locationname', 'locationName'), 'locationname');
                if ($nameCol !== null) {
                    $locationRow = $this->CI->db->where('id', $data['location_id'])->get($locationTable)->row_array();
                    if ($locationRow && isset($locationRow[$nameCol])) {
                        $locationCol = $this->first_field('properties', array('location', 'locationName', 'locationname', 'location_id', 'locationid'), null);
                        if ($locationCol !== null) {
                            $payload[$locationCol] = $locationRow[$nameCol];
                        }
                    }
                }
            }
        }

        // Handle city_id - store cityid (which equals city id) in city column
        if (isset($data['city_id']) && !empty($data['city_id'])) {
            $cityTable = $this->get_city_table();
            if ($cityTable !== null) {
                $cityRow = $this->CI->db->where('id', $data['city_id'])->get($cityTable)->row_array();
                if ($cityRow) {
                    // Get cityid (which should be same as id)
                    $cityIdCol = $this->first_field($cityTable, array('cityid', 'cityId', 'city_id'), null);
                    $cityidValue = ($cityIdCol !== null && isset($cityRow[$cityIdCol])) ? $cityRow[$cityIdCol] : $data['city_id'];
                    
                    $cityCol = $this->first_field('properties', array('city_id', 'cityid', 'cityId'), null);
                    if ($cityCol !== null) {
                        $payload[$cityCol] = $cityidValue;
                    }
                }
            }
        }

        $sliderCol = $this->first_field('properties', array('propertySliderImages', 'propertysliderimages', 'sliderimages', 'images'), null);
        if ($sliderCol !== null && isset($data['propertySliderImages'])) {
            $payload[$sliderCol] = is_array($data['propertySliderImages'])
                ? json_encode($data['propertySliderImages'])
                : $data['propertySliderImages'];
        }

        // Save amenities to propertiesdetails column
        $amenitiesCol = $this->first_field('properties', array('propertiesdetails', 'propertiesDetails', 'amenities', 'amenity', 'amenitiesdetails'), null);
        if ($amenitiesCol !== null && isset($data['amenities'])) {
            $amenitiesValue = $data['amenities'];
            // If it's already a JSON string, validate it; if it's an array, encode it
            if (is_array($amenitiesValue)) {
                $payload[$amenitiesCol] = json_encode($amenitiesValue);
            } else {
                // Try to decode to validate JSON, then encode back
                $decoded = json_decode($amenitiesValue, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $payload[$amenitiesCol] = json_encode($decoded);
                } else {
                    $payload[$amenitiesCol] = $amenitiesValue; // Store as-is if invalid JSON
                }
            }
        }

        $recommendedCol = $this->first_field('properties', array('is_recommended', 'isrecommended'), null);
        if ($recommendedCol !== null && isset($data['is_recommended'])) {
            $payload[$recommendedCol] = (int)$data['is_recommended'];
        }

        // Auto-generate slug from property name
        $slugCol = $this->first_field('properties', array('slug'), null);
        if ($slugCol !== null) {
            $payload[$slugCol] = $this->unique_slug('properties', $name, $id);
        }

        if ($id === null || $id === '') {
            $createdCol = $this->first_field('properties', array('createdAt', 'created_at', 'createdat'), null);
            if ($createdCol !== null) {
                $payload[$createdCol] = date('Y-m-d H:i:s');
            }
            
            $updatedCol = $this->first_field('properties', array('updatedAt', 'updated_at', 'updatedat'), null);
            if ($updatedCol !== null) {
                $payload[$updatedCol] = date('Y-m-d H:i:s');
            }

            $idCol = $this->first_field('properties', array('id'), null);
            if ($idCol !== null && !isset($payload[$idCol])) {
                $idMeta = $this->get_column_meta('properties', $idCol);
                $isAutoIncrement = ($idMeta && isset($idMeta->extra) && stripos((string)$idMeta->extra, 'auto_increment') !== false);
                if (!$isAutoIncrement) {
                    $payload[$idCol] = $this->generate_document_id('prop_');
                }
            }

            $prevDebug = $this->CI->db->db_debug;
            $this->CI->db->db_debug = false;
            $ok = $this->CI->db->insert('properties', $payload);
            $dbError = $this->CI->db->error();
            $this->CI->db->db_debug = $prevDebug;

            if (!$ok) {
                return array('success' => false, 'error' => isset($dbError['message']) ? $dbError['message'] : 'Failed to save property');
            }

            return array(
                'success' => true,
                'id' => isset($payload['id']) ? $payload['id'] : $this->CI->db->insert_id(),
                'slug' => isset($payload[$slugCol]) ? $payload[$slugCol] : ''
            );
        } else {
            // Auto-update slug if property name changed
            if ($slugCol !== null) {
                $existing = $this->CI->db->where('id', $id)->get('properties')->row_array();
                if ($existing && isset($existing[$nameCol]) && $existing[$nameCol] !== $name) {
                    $payload[$slugCol] = $this->unique_slug('properties', $name, $id);
                }
            }

            $prevDebug = $this->CI->db->db_debug;
            $this->CI->db->db_debug = false;
            $ok = $this->CI->db->where('id', $id)->update('properties', $payload);
            $dbError = $this->CI->db->error();
            $this->CI->db->db_debug = $prevDebug;

            if (!$ok) {
                return array('success' => false, 'error' => isset($dbError['message']) ? $dbError['message'] : 'Failed to update property');
            }

            return array(
                'success' => true,
                'id' => $id,
                'slug' => ($slugCol !== null && isset($payload[$slugCol])) ? $payload[$slugCol] : ''
            );
        }
    }

    public function deleteProduct($id) {
        $ok = $this->CI->db->where('id', $id)->delete('properties');
        return array('success' => (bool)$ok);
    }

    public function getLocationsAdmin() {
        $table = $this->get_location_table();
        if ($table === null) {
            return array('success' => true, 'locations' => array());
        }
        return $this->getLocationsList();
    }

    public function saveLocation($data, $id = null) {
        $table = $this->get_location_table();
        if ($table === null) {
            return array('success' => false, 'error' => 'Location table not found');
        }
        $nameCol = $this->first_field($table, array('locationname', 'locationName'), 'locationname');
        $orderCol = $this->first_field($table, array('ordervalue', 'orderValue'), null);
        $cityCol = $this->first_field($table, array('city_id', 'cityid'), null);

        $name = isset($data['locationName']) ? trim((string)$data['locationName']) : '';
        if ($name === '') {
            return array('success' => false, 'error' => 'Location name is required');
        }

        $payload = array($nameCol => $name);

        if ($id === null || $id === '') {
            $ok = $this->CI->db->insert($table, $payload);
            return array('success' => (bool)$ok, 'id' => $ok ? $this->CI->db->insert_id() : null);
        }
        $ok = $this->CI->db->where('id', $id)->update($table, $payload);
        return array('success' => (bool)$ok, 'id' => $id);
    }

    public function deleteLocation($id) {
        $table = $this->get_location_table();
        if ($table === null) {
            return array('success' => false, 'error' => 'Location table not found');
        }
        $ok = $this->CI->db->where('id', $id)->delete($table);
        return array('success' => (bool)$ok);
    }

    public function getCitiesAdmin() {
        $table = $this->get_city_table();
        if ($table === null) {
            return array('success' => true, 'cities' => array());
        }
        $orderCol = $this->first_field($table, array('ordervalue', 'orderValue', 'addedtime', 'created_at', 'id'), 'id');
        $rows = $this->CI->db->from($table)->order_by($orderCol, 'DESC')->get()->result_array();
        $cities = array();
        foreach ($rows as $row) {
            $cities[] = array(
                'id' => $this->property_value($row, array('id'), ''),
                'cityname' => $this->property_value($row, array('cityname', 'cityName'), ''),
                'cityid' => $this->property_value($row, array('cityid', 'cityId', 'city_id'), ''),
                'cityimageurl' => $this->property_value($row, array('cityimageurl', 'cityImageUrl', 'cityimage'), ''),
                'ordervalue' => $this->property_value($row, array('ordervalue', 'orderValue'), 0),
                'created_at' => $this->property_value($row, array('created_at', 'createdAt'), ''),
                'updated_at' => $this->property_value($row, array('updated_at', 'updatedAt'), ''),
                'addedtime' => $this->property_value($row, array('addedtime', 'addedTime'), '')
            );
        }
        return array('success' => true, 'cities' => $cities);
    }

    public function saveCity($data, $id = null) {
        $table = $this->get_city_table();
        if ($table === null) {
            return array('success' => false, 'error' => 'City table not found');
        }
        
        if (is_string($id)) {
            $id = trim($id);
        }
        if ($id === 'null' || $id === 'undefined') {
            $id = '';
        }

        $nameCol = $this->first_field($table, array('cityname', 'cityName'), 'cityname');
        $cityIdCol = $this->first_field($table, array('cityid', 'cityId', 'city_id'), null);
        $imageCol = $this->first_field($table, array('cityimageurl', 'cityImageUrl', 'cityimage'), null);
        $orderCol = $this->first_field($table, array('ordervalue', 'orderValue'), null);
        $createdCol = $this->first_field($table, array('created_at', 'createdAt'), null);
        $updatedCol = $this->first_field($table, array('updated_at', 'updatedAt'), null);
        $addedTimeCol = $this->first_field($table, array('addedtime', 'addedTime'), null);

        $cityname = isset($data['cityname']) ? trim((string)$data['cityname']) : '';
        if ($cityname === '') {
            return array('success' => false, 'error' => 'City name is required');
        }

        $payload = array();
        if ($nameCol !== null) {
            $payload[$nameCol] = $cityname;
        }
        // cityid will be automatically set to same value as id, so we don't need to get it from user input
        if ($imageCol !== null && isset($data['cityimageurl'])) {
            $payload[$imageCol] = trim((string)$data['cityimageurl']);
        }
        if ($orderCol !== null && isset($data['ordervalue'])) {
            $orderVal = $data['ordervalue'];
            if ($orderVal === '' || $orderVal === null) {
                $orderVal = 0;
            }
            $payload[$orderCol] = (float)$orderVal;
        }

        $now = date('Y-m-d H:i:s');
        if ($id === null || $id === '') {
            // Insert
            if ($createdCol !== null) {
                $payload[$createdCol] = $now;
            }
            if ($updatedCol !== null) {
                $payload[$updatedCol] = $now;
            }
            if ($addedTimeCol !== null) {
                $payload[$addedTimeCol] = $now;
            }
            $ok = $this->CI->db->insert($table, $payload);
            $insertedId = $ok ? $this->CI->db->insert_id() : null;
            
            // Set cityid to same value as id after insert
            if ($ok && $insertedId !== null && $cityIdCol !== null) {
                $this->CI->db->where('id', $insertedId)->update($table, array($cityIdCol => (string)$insertedId));
            }
            
            return array('success' => (bool)$ok, 'id' => $insertedId);
        } else {
            // Update - set cityid to same value as id
            if ($cityIdCol !== null) {
                $payload[$cityIdCol] = (string)$id;
            }
            if ($updatedCol !== null) {
                $payload[$updatedCol] = $now;
            }
            $ok = $this->CI->db->where('id', $id)->update($table, $payload);
            return array('success' => (bool)$ok, 'id' => $id);
        }
    }

    public function deleteCity($id) {
        $table = $this->get_city_table();
        if ($table === null) {
            return array('success' => false, 'error' => 'City table not found');
        }
        $ok = $this->CI->db->where('id', $id)->delete($table);
        return array('success' => (bool)$ok);
    }

    public function getVideosAdmin() {
        $table = $this->get_video_table();
        if ($table === null) {
            return array('success' => true, 'videos' => array());
        }
        $orderCol = $this->first_field($table, array('index', 'index_no', 'orderValue', 'id'), 'id');
        $rows = $this->CI->db->from($table)->order_by('`' . $orderCol . '`', 'ASC', false)->get()->result_array();
        $mapped = array();
        foreach ($rows as $row) {
            $mapped[] = array(
                'id' => $this->property_value($row, array('id'), ''),
                'videoUrl' => $this->property_value($row, array('videoUrl', 'videourl', 'videolink'), ''),
                'videolink' => $this->property_value($row, array('videolink', 'videourl', 'videoUrl'), ''),
                'thumbnail' => $this->property_value($row, array('thumbnail', 'thumbnailurl'), ''),
                'title' => $this->property_value($row, array('title', 'caption', 'desc'), ''),
                'desc' => $this->property_value($row, array('desc', 'caption', 'title'), ''),
                'index_no' => $this->property_value($row, array('index_no', 'index', 'ordervalue', 'orderValue'), 0),
                'status' => $this->property_value($row, array('status'), 'active')
            );
        }
        return array('success' => true, 'videos' => $mapped);
    }

    public function saveVideo($data, $id = null) {
        $table = $this->get_video_table();
        if ($table === null) {
            return array('success' => false, 'error' => 'Video table not found');
        }
        if (is_string($id)) {
            $id = trim($id);
        }
        if ($id === 'null' || $id === 'undefined') {
            $id = '';
        }

        $urlRaw = isset($data['videoUrl']) ? $data['videoUrl'] : (isset($data['videolink']) ? $data['videolink'] : '');
        $url = trim((string)$urlRaw);
        if ($url === '') {
            return array('success' => false, 'error' => 'Video URL is required');
        }

        $payload = array();
        $urlCol = $this->first_field($table, array('videoUrl', 'videourl', 'videolink'), null);
        if ($urlCol === null) {
            return array('success' => false, 'error' => 'No valid video URL column found');
        }
        $payload[$urlCol] = $url;

        $titleCol = $this->first_field($table, array('title', 'caption', 'desc'), null);
        $titleValue = isset($data['title']) ? $data['title'] : (isset($data['desc']) ? $data['desc'] : null);
        if ($titleCol !== null && $titleValue !== null) {
            $payload[$titleCol] = $titleValue;
        }

        $thumbCol = $this->first_field($table, array('thumbnail', 'thumbnailurl'), null);
        if ($thumbCol !== null && isset($data['thumbnail']) && !empty($data['thumbnail'])) {
            $payload[$thumbCol] = $data['thumbnail'];
        }

        $orderCol = $this->first_field($table, array('index_no', 'index', 'ordervalue', 'orderValue'), null);
        if ($orderCol !== null) {
            $payload[$orderCol] = isset($data['index_no']) ? (int)$data['index_no'] : (isset($data['index']) ? (int)$data['index'] : 0);
        }

        $statusCol = $this->first_field($table, array('status'), null);
        if ($statusCol !== null) {
            $payload[$statusCol] = isset($data['status']) ? $data['status'] : 'active';
        }

        if ($id === null || $id === '') {
            $createdCol = $this->first_field($table, array('createdAt', 'created_at', 'createdat'), null);
            if ($createdCol !== null) {
                $payload[$createdCol] = date('Y-m-d H:i:s');
            }

            $idCol = $this->first_field($table, array('id'), null);
            if ($idCol !== null && !isset($payload[$idCol])) {
                $idMeta = $this->get_column_meta($table, $idCol);
                $isAutoIncrement = ($idMeta && isset($idMeta->extra) && stripos((string)$idMeta->extra, 'auto_increment') !== false);
                if (!$isAutoIncrement) {
                    $payload[$idCol] = $this->generate_document_id('video_');
                }
            }

            $prevDebug = $this->CI->db->db_debug;
            $this->CI->db->db_debug = false;
            $ok = $this->CI->db->insert($table, $payload);
            $dbError = $this->CI->db->error();
            $this->CI->db->db_debug = $prevDebug;
            if (!$ok) {
                return array('success' => false, 'error' => isset($dbError['message']) ? $dbError['message'] : 'Failed to save video');
            }
            return array('success' => true, 'id' => isset($payload['id']) ? $payload['id'] : $this->CI->db->insert_id());
        }

        $prevDebug = $this->CI->db->db_debug;
        $this->CI->db->db_debug = false;
        $ok = $this->CI->db->where('id', $id)->update($table, $payload);
        $dbError = $this->CI->db->error();
        $this->CI->db->db_debug = $prevDebug;
        if (!$ok) {
            return array('success' => false, 'error' => isset($dbError['message']) ? $dbError['message'] : 'Failed to update video');
        }
        return array('success' => true, 'id' => $id);
    }

    public function deleteVideo($id) {
        $table = $this->get_video_table();
        if ($table === null) {
            return array('success' => false, 'error' => 'Video table not found');
        }
        $ok = $this->CI->db->where('id', $id)->delete($table);
        return array('success' => (bool)$ok);
    }

    public function getReelsAdmin() {
        $table = $this->get_reels_table();
        if ($table === null) {
            return array('success' => true, 'reels' => array());
        }
        $orderCol = $this->first_field($table, array('index', 'index_no', 'orderValue', 'id'), 'id');
        $rows = $this->CI->db->from($table)->order_by('`' . $orderCol . '`', 'ASC', false)->get()->result_array();
        $mapped = array();
        foreach ($rows as $row) {
            $mapped[] = array(
                'id' => $this->property_value($row, array('id'), ''),
                'videoUrl' => $this->property_value($row, array('videoUrl', 'videourl', 'videolink'), ''),
                'thumbnail' => $this->property_value($row, array('thumbnail', 'thumbnailurl', 'thumbnailUrl'), ''),
                'title' => $this->property_value($row, array('title', 'caption', 'desc'), ''),
                'index_no' => $this->property_value($row, array('index_no', 'index', 'ordervalue', 'orderValue'), 0),
                'status' => $this->property_value($row, array('status'), 'active')
            );
        }
        return array('success' => true, 'reels' => $mapped);
    }

    public function saveReel($data, $id = null) {
        $table = $this->get_reels_table();
        if ($table === null) {
            return array('success' => false, 'error' => 'Reel table not found');
        }
        if (is_string($id)) {
            $id = trim($id);
        }
        if ($id === 'null' || $id === 'undefined') {
            $id = '';
        }

        $url = isset($data['videoUrl']) ? trim((string)$data['videoUrl']) : '';
        if ($url === '') {
            return array('success' => false, 'error' => 'Reel URL is required');
        }

        $payload = array();
        $urlCol = $this->first_field($table, array('videoUrl', 'videourl', 'videolink'), null);
        if ($urlCol === null) {
            return array('success' => false, 'error' => 'No valid reel URL column found');
        }
        $payload[$urlCol] = $url;

        $titleCol = $this->first_field($table, array('title', 'caption', 'desc'), null);
        if ($titleCol !== null && isset($data['title'])) {
            $payload[$titleCol] = $data['title'];
        }

        $thumbCol = $this->first_field($table, array('thumbnailurl', 'thumbnail', 'thumbnailUrl'), null);
        if ($thumbCol !== null && isset($data['thumbnail']) && !empty($data['thumbnail'])) {
            $payload[$thumbCol] = $data['thumbnail'];
        }

        $orderCol = $this->first_field($table, array('index_no', 'index', 'ordervalue', 'orderValue'), null);
        if ($orderCol !== null) {
            $payload[$orderCol] = isset($data['index_no']) ? (int)$data['index_no'] : 0;
        }

        $statusCol = $this->first_field($table, array('status'), null);
        if ($statusCol !== null) {
            $payload[$statusCol] = isset($data['status']) ? $data['status'] : 'active';
        }

        if ($id === null || $id === '') {
            $createdCol = $this->first_field($table, array('createdAt', 'created_at', 'createdat'), null);
            if ($createdCol !== null) {
                $payload[$createdCol] = date('Y-m-d H:i:s');
            }

            $idCol = $this->first_field($table, array('id'), null);
            if ($idCol !== null && !isset($payload[$idCol])) {
                $idMeta = $this->get_column_meta($table, $idCol);
                $isAutoIncrement = ($idMeta && isset($idMeta->extra) && stripos((string)$idMeta->extra, 'auto_increment') !== false);
                if (!$isAutoIncrement) {
                    $payload[$idCol] = $this->generate_document_id('reel_');
                }
            }

            $prevDebug = $this->CI->db->db_debug;
            $this->CI->db->db_debug = false;
            $ok = $this->CI->db->insert($table, $payload);
            $dbError = $this->CI->db->error();
            $this->CI->db->db_debug = $prevDebug;
            if (!$ok) {
                return array('success' => false, 'error' => isset($dbError['message']) ? $dbError['message'] : 'Failed to save reel');
            }
            return array('success' => true, 'id' => isset($payload['id']) ? $payload['id'] : $this->CI->db->insert_id());
        }

        $prevDebug = $this->CI->db->db_debug;
        $this->CI->db->db_debug = false;
        $ok = $this->CI->db->where('id', $id)->update($table, $payload);
        $dbError = $this->CI->db->error();
        $this->CI->db->db_debug = $prevDebug;
        if (!$ok) {
            return array('success' => false, 'error' => isset($dbError['message']) ? $dbError['message'] : 'Failed to update reel');
        }
        return array('success' => true, 'id' => $id);
    }

    public function deleteReel($id) {
        $table = $this->get_reels_table();
        if ($table === null) {
            return array('success' => false, 'error' => 'Reel table not found');
        }
        $ok = $this->CI->db->where('id', $id)->delete($table);
        return array('success' => (bool)$ok);
    }

    public function getBlogsAdmin() {
        // New schema: order by date if available, otherwise created_at
        if ($this->has_field('blogs', 'date')) {
            $this->CI->db->order_by('date', 'DESC');
        }
        $this->CI->db->order_by('created_at', 'DESC');
        $rows = $this->CI->db->from('blogs')->get()->result_array();
        $mapped = array();
        foreach ($rows as $row) {
            $mapped[] = $this->map_blog($row);
        }
        return array('success' => true, 'blogs' => $mapped);
    }

    public function saveBlog($data, $id = null) {
        $title = isset($data['title']) ? trim((string)$data['title']) : '';
        if ($title === '') {
            return array('success' => false, 'error' => 'Blog title is required');
        }

        $payload = array(
            'title' => $title,
            'shortdescription' => isset($data['shortdescription']) ? $data['shortdescription'] : '',
            'content' => isset($data['content']) ? $data['content'] : '',
            'category' => isset($data['category']) ? $data['category'] : '',
            'authorname' => isset($data['authorname']) ? $data['authorname'] : 'Admin',
            'coverImageUrl' => isset($data['coverImageUrl']) ? $data['coverImageUrl'] : '',
            'imageUrls' => isset($data['imageUrls']) ? (is_array($data['imageUrls']) ? json_encode($data['imageUrls']) : $data['imageUrls']) : '[]',
            'meta_title' => isset($data['meta_title']) ? $data['meta_title'] : $title,
            'meta_description' => isset($data['meta_description']) ? $data['meta_description'] : '',
            'status' => isset($data['status']) ? $data['status'] : 'active'
        );

        // Auto-generate slug from title
        $slugCol = $this->first_field('blogs', array('slug'), null);
        if ($slugCol !== null) {
            if ($id === null || $id === '') {
                // New blog - generate slug
                $payload[$slugCol] = $this->unique_slug('blogs', $title, $id);
            } else {
                // Update - check if title changed and update slug
                $existing = $this->CI->db->where('id', $id)->get('blogs')->row_array();
                $titleCol = $this->first_field('blogs', array('title'), 'title');
                if ($existing && isset($existing[$titleCol]) && $existing[$titleCol] !== $title) {
                    $payload[$slugCol] = $this->unique_slug('blogs', $title, $id);
                }
            }
        }

        if ($id === null || $id === '') {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $payload['updated_at'] = date('Y-m-d H:i:s');
            $ok = $this->CI->db->insert('blogs', $payload);
            return array('success' => (bool)$ok, 'id' => $ok ? $this->CI->db->insert_id() : null, 'slug' => isset($payload[$slugCol]) ? $payload[$slugCol] : '');
        }

        $payload['updated_at'] = date('Y-m-d H:i:s');
        $ok = $this->CI->db->where('id', $id)->update('blogs', $payload);
        return array('success' => (bool)$ok, 'id' => $id, 'slug' => isset($payload[$slugCol]) ? $payload[$slugCol] : '');
    }

    public function deleteBlog($id) {
        $ok = $this->CI->db->where('id', $id)->delete('blogs');
        return array('success' => (bool)$ok);
    }

    public function getBlogs($limit = 10, $offset = 0, $blogId = null) {
        if (!empty($blogId)) {
            // Try to find by slug first, then by ID
            $post = $this->CI->db->from('blogs')->where('slug', $blogId)->get()->row_array();
            if (!$post) {
                $post = $this->CI->db->from('blogs')->where('id', $blogId)->get()->row_array();
            }
            if (!$post) {
                return array('success' => false, 'error' => 'Post not found');
            }
            return array('success' => true, 'blog' => $this->map_blog($post));
        }

        if ($this->has_field('blogs', 'status')) {
            $this->CI->db->where('status', 'active');
        }
        $total = (int)$this->CI->db->count_all_results('blogs');
        if ($this->has_field('blogs', 'status')) {
            $this->CI->db->where('status', 'active');
        }
        // New schema: order by date if available, otherwise created_at
        if ($this->has_field('blogs', 'date')) {
            $this->CI->db->order_by('date', 'DESC');
        }
        $this->CI->db->order_by('created_at', 'DESC');
        $posts = $this->CI->db->from('blogs')->limit($limit, $offset)->get()->result_array();
        $mapped = array();
        foreach ($posts as $post) {
            $mapped[] = $this->map_blog($post);
        }

        return array('success' => true, 'blogs' => $mapped, 'total' => (int)$total);
    }

    private function map_blog($row) {
        // New schema: blogs table has name, description, short_notes, gallery, author, date, slug
        $name = $this->property_value($row, array('name', 'title'), '');
        $slug = $this->property_value($row, array('slug'), '');
        if (empty($slug) && !empty($name)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        }

        // New schema uses 'gallery' column (JSON array)
        $galleryRaw = $this->property_value($row, array('gallery', 'imageUrls', 'imageurls', 'image_urls'), '');
        $gallery = array();
        if (is_string($galleryRaw) && !empty($galleryRaw)) {
            $gallery = $this->decode_json_list($galleryRaw);
        } elseif (is_array($galleryRaw)) {
            $gallery = $galleryRaw;
        }

        // Get cover image from gallery or other sources
        $cover = '';
        if (!empty($gallery) && is_array($gallery)) {
            $cover = $gallery[0];
        } elseif (isset($row['coverImageUrl'])) {
            $cover = $row['coverImageUrl'];
        } elseif (isset($row['coverimageurl'])) {
            $cover = $row['coverimageurl'];
        }

        return array(
            'id' => isset($row['id']) ? (string)$row['id'] : '',
            'name' => $name,
            'title' => $name, // For backward compatibility
            'slug' => $slug,
            'description' => $this->property_value($row, array('description', 'content'), ''),
            'shortDescription' => $this->property_value($row, array('short_notes', 'shortdescription', 'shortDescription'), ''),
            'short_notes' => $this->property_value($row, array('short_notes', 'shortdescription'), ''),
            'content' => $this->property_value($row, array('description', 'content'), ''),
            'category' => $this->property_value($row, array('category'), ''),
            'author' => $this->property_value($row, array('author', 'authorname', 'authorName'), 'Admin'),
            'authorName' => $this->property_value($row, array('author', 'authorname', 'authorName'), 'Admin'),
            'date' => $this->property_value($row, array('date', 'publisheddate', 'publishedDate'), ''),
            'publishedDate' => $this->property_value($row, array('date', 'publisheddate', 'publishedDate'), isset($row['created_at']) ? $row['created_at'] : date('Y-m-d')),
            'meta_title' => $this->property_value($row, array('meta_title'), ''),
            'meta_description' => $this->property_value($row, array('meta_description'), ''),
            'coverImageUrl' => $cover,
            'gallery' => $gallery,
            'imageUrls' => $gallery, // For backward compatibility
            'status' => $this->property_value($row, array('status'), 'active'),
            'createdAt' => $this->property_value($row, array('created_at', 'createdAt'), ''),
            'created_at' => $this->property_value($row, array('created_at', 'createdAt'), ''),
            'updated_at' => $this->property_value($row, array('updated_at', 'updatedAt'), '')
        );
    }

    public function getBlogCategories() {
        $result = $this->getBlogs(6, 0);
        return array('success' => true, 'blogs' => isset($result['blogs']) ? $result['blogs'] : array());
    }

    public function getReelsVideos() {
        $reelsTable = $this->get_reels_table();
        if ($reelsTable === null) {
            return array('success' => true, 'reelsVideos' => array());
        }
        if ($this->has_field($reelsTable, 'status')) {
            $this->CI->db->where('status', 'active');
        }
        $orderCol = $this->first_field($reelsTable, array('index', 'index_no', 'orderValue', 'id'), 'id');
        $rows = $this->CI->db->from($reelsTable)->order_by('`' . $orderCol . '`', 'ASC', false)->get()->result_array();
        
        // Normalize reel data
        foreach ($rows as &$row) {
            // Map videoUrl
            $videoUrl = $this->property_value($row, array('videoUrl', 'videourl', 'videolink'), '');
            $row['videoUrl'] = $videoUrl;
            
            // Map thumbnail
            $thumbnail = $this->property_value($row, array('thumbnail', 'thumbnailurl', 'thumbnailUrl'), '');
            $row['thumbnail'] = $thumbnail;
            $row['thumbnailUrl'] = $thumbnail; // Also set thumbnailUrl for compatibility
            
            // Map title
            $title = $this->property_value($row, array('title', 'caption', 'desc'), '');
            $row['title'] = $title;
            $row['caption'] = $title; // Also set caption for compatibility
        }
        unset($row);

        return array('success' => true, 'reelsVideos' => $rows);
    }

    public function getVideos() {
        $videoTable = $this->get_video_table();
        if ($videoTable === null) {
            return array('success' => true, 'videos' => array());
        }

        $orderCol = $this->first_field($videoTable, array('index', 'index_no', 'orderValue', 'id'), 'id');
        if ($this->has_field($videoTable, 'status')) {
            $this->CI->db->where('status', 'active');
        }
        $rows = $this->CI->db->from($videoTable)->order_by('`' . $orderCol . '`', 'ASC', false)->get()->result_array();
        foreach ($rows as &$row) {
            $videoUrl = '';
            if (isset($row['videoUrl']) && $row['videoUrl'] !== '') {
                $videoUrl = $row['videoUrl'];
            } elseif (isset($row['videoLink']) && $row['videoLink'] !== '') {
                $videoUrl = $row['videoLink'];
            } elseif (isset($row['videolink']) && $row['videolink'] !== '') {
                $videoUrl = $row['videolink'];
            } elseif (isset($row['video_url']) && $row['video_url'] !== '') {
                $videoUrl = $row['video_url'];
            }

            // Keep both keys because different views/controllers read different names.
            $row['videoUrl'] = $videoUrl;
            $row['videoLink'] = $videoUrl;

            if (isset($row['desc']) && !isset($row['title'])) {
                $row['title'] = $row['desc'];
            }
        }
        unset($row);
        return array('success' => true, 'videos' => $rows);
    }

    public function getActiveBanners() {
        $bannerTable = $this->get_banner_table();
        if ($bannerTable === null) {
            return array('success' => true, 'banners' => array());
        }

        $orderCol = $this->first_field($bannerTable, array('created_at', 'createdAt', 'id'), 'id');
        $rows = $this->CI->db->from($bannerTable)->order_by($orderCol, 'DESC')->get()->result_array();
        foreach ($rows as &$row) {
            $row = $this->normalize_banner_row($row);
        }
        unset($row);
        return array('success' => true, 'banners' => $rows);
    }

    public function getRecommendedItems() {
        // New schema uses 'is_featured' instead of 'is_recommended'
        $featuredCol = $this->first_field('properties', array('is_featured', 'is_recommended', 'isrecommended'), 'is_featured');
        $statusCol = $this->first_field('properties', array('status'), null);
        
        $this->CI->db->from('properties');
        if ($featuredCol !== null) {
            $this->CI->db->where($featuredCol, 1);
        }
        if ($statusCol !== null) {
            $this->CI->db->where($statusCol, 'active');
        }
        $this->CI->db->limit(1);
        $rows = $this->CI->db->get()->result_array();
        
        $items = array();
        foreach ($rows as $row) {
            $items[] = $this->normalize_property_row($row);
        }
        return array('success' => true, 'items' => $items);
    }

    public function getAllEnquiries() {
        $orderCol = $this->first_field('enquiries', array('createdAt', 'createdat', 'created_at', 'enquirytime', 'id'), 'id');
        $rows = $this->CI->db->from('enquiries')->order_by($orderCol, 'DESC')->get()->result_array();
        foreach ($rows as &$row) {
            $row = $this->normalize_enquiry_row($row);
        }
        unset($row);
        return array('success' => true, 'enquiries' => $rows);
    }

    public function getEnquiries($userId) {
        $userIdCol = $this->first_field('enquiries', array('userId', 'userid'), 'userId');
        $orderCol = $this->first_field('enquiries', array('createdAt', 'createdat', 'created_at', 'enquirytime', 'id'), 'id');
        $rows = $this->CI->db->from('enquiries')->where($userIdCol, $userId)->order_by($orderCol, 'DESC')->get()->result_array();
        foreach ($rows as &$row) {
            $row = $this->normalize_enquiry_row($row);
        }
        unset($row);
        return array('success' => true, 'enquiries' => $rows);
    }

    public function addEnquiry($data) {
        if (isset($data['userDetails']) && is_array($data['userDetails'])) {
            $data['userDetails'] = json_encode($data['userDetails']);
        }
        $ok = $this->CI->db->insert('enquiries', $data);
        return array('success' => (bool)$ok, 'id' => $ok ? $this->CI->db->insert_id() : null);
    }

    public function getAllContacts() {
        $rows = $this->CI->db->from('contacts')->order_by('createdAt', 'DESC')->get()->result_array();
        foreach ($rows as &$row) {
            if (isset($row['userDetails']) && is_string($row['userDetails'])) {
                $decoded = json_decode($row['userDetails'], true);
                $row['userDetails'] = is_array($decoded) ? $decoded : array();
            }
        }
        unset($row);
        return array('success' => true, 'contacts' => $rows);
    }

    public function addContact($data) {
        // Get actual columns in contacts table
        $tableFields = $this->CI->db->list_fields('contacts');
        
        // Filter data to only include columns that exist in the table
        $filteredData = array();
        $allowedFields = array('id', 'name', 'email', 'phone', 'subject', 'message', 'status', 'created_at', 'createdAt', 'createdat');
        
        foreach ($data as $key => $value) {
            // Only include if field exists in table or is in allowed list
            if (in_array($key, $tableFields) || in_array($key, $allowedFields)) {
                $filteredData[$key] = $value;
            }
        }
        
        // Map column names to match database schema
        // Check which column exists: created_at or createdAt
        $createdCol = $this->first_field('contacts', array('created_at', 'createdAt', 'createdat'), 'created_at');
        if ($createdCol && isset($filteredData['createdAt']) && !isset($filteredData[$createdCol])) {
            $filteredData[$createdCol] = $filteredData['createdAt'];
            unset($filteredData['createdAt']);
        }
        
        // Remove any fields that don't exist in the table
        $finalData = array();
        foreach ($filteredData as $key => $value) {
            if (in_array($key, $tableFields)) {
                $finalData[$key] = $value;
            }
        }
        
        $data = $finalData;

        // Some legacy schemas use VARCHAR primary keys instead of AUTO_INCREMENT.
        if ((!isset($data['id']) || $data['id'] === '') && $this->has_field('contacts', 'id')) {
            $idMeta = $this->get_column_meta('contacts', 'id');
            $type = $idMeta && isset($idMeta->type) ? strtolower((string)$idMeta->type) : '';
            $isNumericId = in_array($type, array('int', 'integer', 'tinyint', 'smallint', 'mediumint', 'bigint', 'decimal', 'double', 'float'), true);
            if (!$isNumericId) {
                $data['id'] = $this->generate_document_id('contact_');
            }
        }

        $prevDebug = $this->CI->db->db_debug;
        $this->CI->db->db_debug = false;
        $ok = $this->CI->db->insert('contacts', $data);
        $dbError = $this->CI->db->error();
        $this->CI->db->db_debug = $prevDebug;

        if (!$ok) {
            return array('success' => false, 'error' => $dbError);
        }

        $id = isset($data['id']) && $data['id'] !== '' ? $data['id'] : $this->CI->db->insert_id();
        return array('success' => true, 'id' => $id);
    }

    public function addOrUpdateCustomer($data) {
        if (empty($data['email'])) {
            return array('success' => false, 'error' => 'Email is required');
        }

        // Get actual columns in customers table
        $tableFields = $this->CI->db->list_fields('customers');
        
        // Filter data to only include columns that exist in the table
        $filteredData = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $tableFields)) {
                $filteredData[$key] = $value;
            }
        }
        
        // If no valid fields, return error
        if (empty($filteredData) || !isset($filteredData['email'])) {
            return array('success' => false, 'error' => 'No valid fields to insert');
        }

        $contactCountCol = $this->first_field('customers', array('contactCount', 'contactcount'), 'contactCount');
        $existing = $this->CI->db->from('customers')->where('email', $filteredData['email'])->get()->row_array();
        
        if ($existing) {
            // Update existing customer
            if ($contactCountCol && in_array($contactCountCol, $tableFields)) {
                $currentCount = (int)$this->property_value($existing, array('contactCount', 'contactcount'), 0);
                $filteredData[$contactCountCol] = $currentCount + 1;
            }
            
            // Only update fields that exist in the table
            $updateData = array();
            foreach ($filteredData as $key => $value) {
                if (in_array($key, $tableFields) && $key !== 'id') {
                    $updateData[$key] = $value;
                }
            }
            
            if (!empty($updateData)) {
                $this->CI->db->where('id', $existing['id'])->update('customers', $updateData);
            }
            return array('success' => true, 'id' => isset($existing['id']) ? $existing['id'] : null);
        }

        // Insert new customer
        if ($contactCountCol && in_array($contactCountCol, $tableFields)) {
            if (isset($filteredData['contactCount'])) {
                $filteredData[$contactCountCol] = (int)$filteredData['contactCount'];
                unset($filteredData['contactCount']);
            } elseif (isset($filteredData['contactcount'])) {
                $filteredData[$contactCountCol] = (int)$filteredData['contactcount'];
                unset($filteredData['contactcount']);
            } else {
                $filteredData[$contactCountCol] = 1;
            }
        }

        // Final filter to ensure only existing columns are inserted
        $finalData = array();
        foreach ($filteredData as $key => $value) {
            if (in_array($key, $tableFields)) {
                $finalData[$key] = $value;
            }
        }

        if (empty($finalData)) {
            return array('success' => false, 'error' => 'No valid fields to insert');
        }

        $ok = $this->CI->db->insert('customers', $finalData);
        return array('success' => (bool)$ok, 'id' => $ok ? $this->CI->db->insert_id() : null);
    }

    public function addVideoPlayEvent($data) {
        if (isset($data['timestamp'])) {
            $data['ts'] = $data['timestamp'];
            unset($data['timestamp']);
        }
        $ok = $this->CI->db->insert('video_play_events', $data);
        return array('success' => (bool)$ok, 'id' => $ok ? $this->CI->db->insert_id() : null);
    }

    public function getBanners() {
        $bannerTable = $this->get_banner_table();
        if ($bannerTable === null) {
            return array('success' => true, 'banners' => array());
        }

        $orderCol = $this->first_field($bannerTable, array('created_at', 'createdAt', 'id'), 'id');
        $rows = $this->CI->db->from($bannerTable)->order_by($orderCol, 'DESC')->get()->result_array();
        foreach ($rows as &$row) {
            $row = $this->normalize_banner_row($row);
        }
        unset($row);
        return array('success' => true, 'banners' => $rows);
    }

    public function uploadBanner($imagePath, $imageName, $status = 'active') {
        $bannerTable = $this->get_banner_table();
        if ($bannerTable === null) {
            return array('success' => false, 'error' => 'Banner table not found');
        }

        $targetDir = FCPATH . 'uploads/banners/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }
        $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
        $fileName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $imageName) . '_' . time() . '.' . $ext;
        $targetPath = $targetDir . $fileName;
        if (!@copy($imagePath, $targetPath)) {
            return array('success' => false, 'error' => 'Unable to move uploaded image');
        }

        $now = date('Y-m-d H:i:s');
        $publicUrl = base_url('uploads/banners/' . $fileName);
        $insert = array();

        $idMeta = $this->get_column_meta($bannerTable, 'id');
        $idType = $idMeta && isset($idMeta->type) ? strtolower((string)$idMeta->type) : '';
        $isNumericId = in_array($idType, array('int', 'integer', 'tinyint', 'smallint', 'mediumint', 'bigint', 'decimal', 'double', 'float'), true);
        if ($this->has_field($bannerTable, 'id') && !$isNumericId) {
            $insert['id'] = $this->generate_document_id('banner_');
        }

        $imageCol = $this->first_field($bannerTable, array('imageurl', 'imageUrl', 'imagepath', 'image_path'), null);
        if ($imageCol !== null) {
            $insert[$imageCol] = $publicUrl;
        }

        if ($this->has_field($bannerTable, 'status')) {
            $insert['status'] = $status;
        }
        $createdCol = $this->first_field($bannerTable, array('created_at', 'createdAt', 'createdat'), null);
        if ($createdCol !== null) {
            $insert[$createdCol] = $now;
        }
        $updatedCol = $this->first_field($bannerTable, array('updated_at', 'updatedAt', 'updatedat'), null);
        if ($updatedCol !== null) {
            $insert[$updatedCol] = $now;
        }

        $ok = $this->CI->db->insert($bannerTable, $insert);
        $newId = null;
        if ($ok) {
            $newId = isset($insert['id']) ? $insert['id'] : $this->CI->db->insert_id();
        }
        return array('success' => (bool)$ok, 'id' => $newId, 'imageUrl' => $publicUrl);
    }

    public function updateBannerStatus($bannerId, $status) {
        $bannerTable = $this->get_banner_table();
        if ($bannerTable === null) {
            return array('success' => false, 'error' => 'Banner table not found');
        }

        $update = array();
        if ($this->has_field($bannerTable, 'status')) {
            $update['status'] = $status;
        }
        $updatedCol = $this->first_field($bannerTable, array('updated_at', 'updatedAt', 'updatedat'), null);
        if ($updatedCol !== null) {
            $update[$updatedCol] = date('Y-m-d H:i:s');
        }
        if (empty($update)) {
            return array('success' => false, 'error' => 'No updatable columns found');
        }

        $ok = $this->CI->db->where('id', $bannerId)->update($bannerTable, $update);
        return array('success' => (bool)$ok);
    }

    public function deleteBanner($bannerId) {
        $bannerTable = $this->get_banner_table();
        if ($bannerTable === null) {
            return array('success' => false, 'error' => 'Banner table not found');
        }

        $row = $this->CI->db->from($bannerTable)->where('id', $bannerId)->get()->row_array();
        if (!$row) {
            return array('success' => false, 'error' => 'Banner not found');
        }

        $row = $this->normalize_banner_row($row);
        if (!empty($row['imageUrl'])) {
            $relative = str_replace(base_url(), '', $row['imageUrl']);
            $local = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if (is_file($local)) {
                @unlink($local);
            }
        }

        $ok = $this->CI->db->where('id', $bannerId)->delete($bannerTable);
        return array('success' => (bool)$ok);
    }

    public function clearAllCache() {
        $cachePath = APPPATH . 'cache/';
        $count = 0;
        if (is_dir($cachePath)) {
            $files = scandir($cachePath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === 'index.html') {
                    continue;
                }
                $target = $cachePath . $file;
                if (is_file($target) && @unlink($target)) {
                    $count++;
                }
            }
        }

        return array('success' => true, 'message' => 'Cleared ' . $count . ' cache file(s).');
    }

    public function getUserByEmail($email) {
        $row = $this->CI->db->from('users')->where('email', $email)->get()->row_array();
        if (!$row) {
            return array('success' => false, 'error' => 'User not found');
        }
        return array('success' => true, 'documentId' => (string)$row['id'], 'user' => $row);
    }

    public function getUserByPhone($phoneNumber) {
        $row = $this->CI->db->from('users')->where('phoneNumber', $phoneNumber)->get()->row_array();
        if (!$row) {
            return array('success' => false, 'error' => 'User not found');
        }
        return array('success' => true, 'documentId' => (string)$row['id'], 'user' => $row);
    }

    public function getUserById($id) {
        $row = $this->CI->db->from('users')->where('id', $id)->get()->row_array();
        if (!$row) {
            return array('success' => false, 'error' => 'User not found');
        }
        return array('success' => true, 'data' => $row);
    }

    public function createUser($data) {
        $now = date('Y-m-d H:i:s');
        $data['createdAt'] = isset($data['createdAt']) ? $data['createdAt'] : $now;
        $data['updatedAt'] = $now;
        if (!empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        $ok = $this->CI->db->insert('users', $data);
        return array('success' => (bool)$ok, 'documentId' => $ok ? (string)$this->CI->db->insert_id() : null);
    }

    public function updateUser($id, $data) {
        $data['updatedAt'] = date('Y-m-d H:i:s');
        if (!empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        $ok = $this->CI->db->where('id', $id)->update('users', $data);
        return array('success' => (bool)$ok);
    }

    public function verifyUserCredentials($email, $password) {
        $row = $this->CI->db->from('users')->where('email', $email)->get()->row_array();
        if (!$row || empty($row['password_hash']) || !password_verify($password, $row['password_hash'])) {
            return array('success' => false, 'error' => 'Invalid credentials');
        }
        return array('success' => true, 'user' => $row);
    }

    public function deleteUser($id) {
        $ok = $this->CI->db->where('id', $id)->delete('users');
        return array('success' => (bool)$ok);
    }
}
