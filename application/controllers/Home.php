<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('db_store');
        $this->load->model('Property_model');
        $this->load->model('City_model');
        $this->load->model('Location_model');
        $this->load->model('Category_model');
        $this->load->model('Blog_model');
        $this->load->model('Banner_model');
        $this->load->model('Reelsvideo_model');
        $this->load->model('Video_model');
    }

    /**
     * /home should not duplicate / — send users to the canonical base URL.
     */
    public function redirect_root()
    {
        redirect(base_url(), 'location', 301);
    }

    public function index()
    {
        $data['title'] = 'Home - Real Estate';
        $data['page']  = 'home';
        
        // Fetch cities using model
        $cities = $this->City_model->get_all('active');
        $data['cities'] = array();
        foreach ($cities as $city) {
            $data['cities'][] = array(
                'id' => $city->id,
                'name' => $city->name,
                'cityname' => $city->name, // For backward compatibility
                'cityid' => (string)$city->id, // cityid equals id
                'image' => isset($city->image) ? $city->image : ''
            );
        }
        
        // Fetch locations using model
        $locations = $this->Location_model->get_all('active');
        $data['locations'] = array();
        $location_images = array(); // Map location names to their images
        foreach ($locations as $location) {
            $locationName = $location->name;
            $locationImage = isset($location->image) && !empty($location->image) ? $location->image : '';
            $location_images[$locationName] = $locationImage;
            
            $data['locations'][] = array(
                'id' => $location->id,
                'locationName' => $locationName,
                'name' => $locationName,
                'city_id' => $location->city_id,
                'city_name' => isset($location->city_name) ? $location->city_name : '',
                'image' => $locationImage
            );
        }
        $data['location_images'] = $location_images; // Pass to view
        
        // Fetch properties using model
        $properties = $this->Property_model->get_all('active');
        $data['properties'] = array();
        $properties_by_location = array();
        $properties_by_city = array();
        
        foreach ($properties as $property) {
            $propArray = array(
                'id' => $property->id,
                'propertyName' => $property->name,
                'name' => $property->name,
                'slug' => isset($property->slug) ? $property->slug : '',
                'category' => $property->category,
                'location' => $property->location,
                'city' => $property->city,
                'price' => isset($property->price) ? $property->price : 0,
                'propertyPriceRange' => isset($property->price) ? $property->price : 0,
                'description' => isset($property->description) ? $property->description : '',
                'desc' => isset($property->description) ? $property->description : '',
                'video' => isset($property->video) ? $property->video : '',
                'projectVideoUrl' => isset($property->video) ? $property->video : '',
                'main_image' => isset($property->main_image) ? $property->main_image : '',
                'propertiesMainImage' => isset($property->main_image) ? $property->main_image : '',
                'gallery' => isset($property->gallery) ? (is_string($property->gallery) ? json_decode($property->gallery, true) : $property->gallery) : array(),
                'propertySliderImages' => isset($property->gallery) ? (is_string($property->gallery) ? json_decode($property->gallery, true) : $property->gallery) : array(),
                'locationimg' => isset($property->locationimg) ? $property->locationimg : '',
                'nearby' => isset($property->nearby) ? (is_string($property->nearby) ? json_decode($property->nearby, true) : $property->nearby) : array(),
                'features' => isset($property->features) ? (is_string($property->features) ? json_decode($property->features, true) : $property->features) : array(),
                'type' => isset($property->type) ? $property->type : '',
                'rating' => isset($property->rating) ? $property->rating : 0,
                'floorplan' => isset($property->floorplan) ? $property->floorplan : '',
                'is_featured' => isset($property->is_featured) ? $property->is_featured : 0,
                'isFeatured' => isset($property->is_featured) ? $property->is_featured : 0, // For backward compatibility
                'is_latest' => isset($property->is_latest) ? $property->is_latest : 0,
                'status' => $property->status,
                'created_at' => isset($property->created_at) ? $property->created_at : '',
                'createdAt' => isset($property->created_at) ? $property->created_at : '',
                'locationInfo' => array('locationName' => $property->location),
                'cityInfo' => array('cityName' => $property->city),
                'categoryInfo' => array('categoryName' => $property->category),
                'categoryName' => $property->category // For backward compatibility
            );
            
            $data['properties'][] = $propArray;
            
            // Group by location
            $locationName = $property->location ?: 'Other';
            if (!isset($properties_by_location[$locationName])) {
                $properties_by_location[$locationName] = array();
            }
            $properties_by_location[$locationName][] = $propArray;
            
            // Group by city
            $cityName = $property->city ?: 'Other';
            if (!isset($properties_by_city[$cityName])) {
                $properties_by_city[$cityName] = array();
            }
            $properties_by_city[$cityName][] = $propArray;
        }
        
        // Fetch blogs using model and map to expected format
        $blogs = $this->Blog_model->get_all('active');
        $data['blogs'] = array();
        foreach ($blogs as $blog) {
            // Get gallery images
            $gallery = array();
            if (isset($blog->gallery)) {
                if (is_string($blog->gallery)) {
                    $gallery = json_decode($blog->gallery, true);
                } elseif (is_array($blog->gallery)) {
                    $gallery = $blog->gallery;
                }
            }
            
            // Get cover image from gallery or use empty
            $coverImage = '';
            if (!empty($gallery) && is_array($gallery)) {
                $coverImage = $gallery[0];
            }
            
            // Format date
            $publishedDate = '';
            if (isset($blog->date) && !empty($blog->date)) {
                $publishedDate = $blog->date;
            } elseif (isset($blog->created_at) && !empty($blog->created_at)) {
                $publishedDate = $blog->created_at;
            } else {
                $publishedDate = date('Y-m-d');
            }
            
            $data['blogs'][] = array(
                'id' => $blog->id,
                'name' => isset($blog->name) ? $blog->name : '',
                'title' => isset($blog->name) ? $blog->name : '',
                'description' => isset($blog->description) ? $blog->description : '',
                'shortDescription' => isset($blog->short_notes) ? $blog->short_notes : '',
                'short_notes' => isset($blog->short_notes) ? $blog->short_notes : '',
                'content' => isset($blog->description) ? $blog->description : '',
                'gallery' => $gallery,
                'imageUrls' => $gallery, // For backward compatibility
                'coverImageUrl' => $coverImage,
                'author' => isset($blog->author) ? $blog->author : 'Admin',
                'authorName' => isset($blog->author) ? $blog->author : 'Admin',
                'date' => isset($blog->date) ? $blog->date : '',
                'publishedDate' => $publishedDate,
                'category' => '', // Blogs table doesn't have category in new schema
                'slug' => isset($blog->slug) ? $blog->slug : '',
                'status' => isset($blog->status) ? $blog->status : 'active',
                'createdAt' => isset($blog->created_at) ? $blog->created_at : '',
                'created_at' => isset($blog->created_at) ? $blog->created_at : ''
            );
        }
        
        // Helper function to extract YouTube video ID and generate embed URL
        $extractYouTubeEmbedUrl = function($url) {
            if (empty($url)) return '';
            
            $patterns = array(
                '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/',
                '/youtube\.com\/.*[?&]v=([^&\n?#]+)/'
            );
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $url, $matches)) {
                    return 'https://www.youtube.com/embed/' . $matches[1];
                }
            }
            return '';
        };
        
        // Fetch reels videos using model (from reels_videos table)
        $reels = $this->Reelsvideo_model->get_all('active');
        $data['reels_videos'] = array();
        foreach ($reels as $reel) {
            $videoUrl = $reel->videoUrl ?: '';
            $isYouTube = !empty($videoUrl) && (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false);
            $embedUrl = $isYouTube ? $extractYouTubeEmbedUrl($videoUrl) : '';
            
            $data['reels_videos'][] = array(
                'id' => $reel->id,
                'videoUrl' => $videoUrl,
                'videoLink' => $videoUrl,
                'embedUrl' => $embedUrl,
                'isYouTube' => $isYouTube,
                'thumbnailUrl' => $reel->thumbnail ?: '',
                'thumbnailurl' => $reel->thumbnail ?: '',
                'thumbnail' => $reel->thumbnail ?: '',
                'title' => $reel->title ?: '',
                'caption' => $reel->title ?: '', // Use title as caption for backward compatibility
                'desc' => $reel->title ?: '',
                'orderValue' => $reel->index_no ?: 999,
                'ordervalue' => $reel->index_no ?: 999,
                'index_no' => $reel->index_no ?: 999,
                'createdAt' => $reel->createdAt ?: '',
                'created_at' => $reel->createdAt ?: '',
                'reelId' => $reel->id,
                'reelid' => $reel->id,
                'status' => $reel->status ?: 'active'
            );
        }

        // Get videos using model
        $videos = $this->Video_model->get_all('active');
        $data['videos'] = array();
        foreach ($videos as $video) {
            $videoUrl = $video->videoUrl ?: '';
            $isYouTube = !empty($videoUrl) && (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false);
            $embedUrl = $isYouTube ? $extractYouTubeEmbedUrl($videoUrl) : '';
            
            $data['videos'][] = array(
                'id' => $video->id,
                'videoUrl' => $videoUrl,
                'videoLink' => $videoUrl,
                'embedUrl' => $embedUrl,
                'isYouTube' => $isYouTube,
                'title' => $video->title ?: '',
                'thumbnail' => $video->thumbnail ?: '',
                'index_no' => $video->index_no ?: 0,
                'status' => $video->status ?: 'active',
                'createdAt' => $video->createdAt ?: ''
            );
        }
        
        // Reorder properties_by_location to match the admin-defined location order
        $ordered_by_location = array();
        foreach ($data['locations'] as $loc) {
            $locName = $loc['name'];
            if (isset($properties_by_location[$locName])) {
                $ordered_by_location[$locName] = $properties_by_location[$locName];
            }
        }
        // Append any locations not in the ordered list (e.g. location name mismatch)
        foreach ($properties_by_location as $locName => $props) {
            if (!isset($ordered_by_location[$locName])) {
                $ordered_by_location[$locName] = $props;
            }
        }
        $data['properties_by_location'] = $ordered_by_location;
        $data['properties_by_city'] = $properties_by_city;
        
        // Fetch active banners
        $banners = $this->Banner_model->get_all('active');
        $data['banners'] = array();
        foreach ($banners as $banner) {
            $data['banners'][] = array(
                'id' => $banner->id,
                'imageUrl' => isset($banner->image) ? $banner->image : '',
                'image' => isset($banner->image) ? $banner->image : '',
                'status' => $banner->status
            );
        }
        
        // Fetch featured properties (is_featured = 1)
        $featuredProperties = $this->Property_model->get_featured_properties(1);
        $data['recommended_items'] = array();
        foreach ($featuredProperties as $property) {
            $data['recommended_items'][] = array(
                'id' => $property->id,
                'propertyName' => $property->name,
                'propertiesMainImage' => isset($property->main_image) ? $property->main_image : '',
                'price' => isset($property->price) ? $property->price : 0,
                'propertyPriceRange' => isset($property->price) ? $property->price : 0
            );
        }
       
        $this->load->view('header', $data);
        $this->load->view('home', $data);
        $this->load->view('footer');
    }

    public function mylistings()
    {
        $data['title'] = 'My Listings';
        $data['page'] = 'dashboard/listings';
        
        $this->load->view('header', $data);
        $this->load->view('mylistings', $data);
        $this->load->view('footer', $data);
    }  
    
    public function mywishlist()    
    {
        $data['title'] = 'My Wishlist';
        $data['page'] = 'dashboard/wishlist';
        
        $this->load->view('header', $data);
        $this->load->view('mywishlist', $data);
        $this->load->view('footer', $data);
    }

    public function deleteInstruction()
    {
        $this->load->view('delete_instruction');
    }
}
