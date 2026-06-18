<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blog extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('db_store');
    }

    /**
     * Blog listing page
     */
    public function index() {
        $data['title'] = 'Blog - Real Estate';
        $data['page'] = 'blog';

        // Get pagination parameters
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Get blogs from MySQL
        $blogsResult = $this->db_store->getBlogs($limit, $offset);
        
        $data['posts'] = array();
        $data['total_posts'] = 0;
        $data['current_page'] = $page;
        $data['total_pages'] = 1;
        
        if ($blogsResult['success']) {
            $data['posts'] = $blogsResult['blogs'];
            $data['total_posts'] = isset($blogsResult['total']) ? $blogsResult['total'] : count($blogsResult['blogs']);
            $data['total_pages'] = ceil($data['total_posts'] / $limit);
        } else {
            error_log('Failed to fetch blogs: ' . json_encode($blogsResult['error']));
        }


        $this->load->view('header', $data);
        $this->load->view('blog/index', $data);
        $this->load->view('footer', $data);
    }

    /**
     * Single blog post page
     */
    public function post($blogId = null) {
        if (!$blogId) {
            show_404();
        }

        // First try to get by document ID directly (most efficient)
        $blogResult = $this->db_store->getBlogs(1, 0, $blogId);
        $post = null;
        
        if ($blogResult['success'] && isset($blogResult['blog'])) {
            $post = $blogResult['blog'];
        } else {
            // If not found by ID, try to find by slug or ID in all blogs
            $blogsResult = $this->db_store->getBlogs(1000, 0);
            
            if ($blogsResult['success']) {
                foreach ($blogsResult['blogs'] as $blog) {
                    // Check by document ID first
                    if (isset($blog['id']) && $blog['id'] === $blogId) {
                        $post = $blog;
                        break;
                    }
                    // Also check by slug for backward compatibility
                    if (isset($blog['slug']) && $blog['slug'] === $blogId) {
                        $post = $blog;
                        break;
                    }
                }
            }
        }
        
        if (!$post) {
            show_404();
        }

        $data['title'] = isset($post['meta_title']) ? $post['meta_title'] : $post['title'];
        $data['page'] = 'blog';
        $data['post'] = $post;
        
        // Get recent posts (excluding current post)
        $recentBlogsResult = $this->db_store->getBlogs(6, 0);
        $data['recent_posts'] = array();
        if ($recentBlogsResult['success']) {
            foreach ($recentBlogsResult['blogs'] as $blog) {
                if (isset($post['id']) && isset($blog['id']) && $blog['id'] !== $post['id']) {
                    $data['recent_posts'][] = $blog;
                    if (count($data['recent_posts']) >= 5) {
                        break;
                    }
                }
            }
        }

        $this->load->view('header', $data);
        $this->load->view('blog/post', $data);
        $this->load->view('footer', $data);
    }


    /**
     * Search blog posts
     */
    public function search() {
        $keyword = $this->input->get('q');
        
        if (empty($keyword)) {
            redirect('blog');
        }

        $data['title'] = 'Search Results - Real Estate Blog';
        $data['page'] = 'blog';
        $data['keyword'] = $keyword;

        // Get pagination parameters
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Get all blogs and filter by keyword
        $blogsResult = $this->db_store->getBlogs(1000, 0);
        
        $data['posts'] = array();
        $data['total_posts'] = 0;
        $data['current_page'] = $page;
        $data['total_pages'] = 1;
        
        if ($blogsResult['success']) {
            $keywordLower = strtolower($keyword);
            $filteredBlogs = array();
            
            foreach ($blogsResult['blogs'] as $blog) {
                $title = strtolower($blog['title'] ?? '');
                $description = strtolower($blog['shortDescription'] ?? '');
                $content = strtolower($blog['content'] ?? '');
                $category = strtolower($blog['category'] ?? '');
                
                if (strpos($title, $keywordLower) !== false || 
                    strpos($description, $keywordLower) !== false || 
                    strpos($content, $keywordLower) !== false ||
                    strpos($category, $keywordLower) !== false) {
                    $filteredBlogs[] = $blog;
                }
            }
            
            $data['total_posts'] = count($filteredBlogs);
            $data['posts'] = array_slice($filteredBlogs, $offset, $limit);
            $data['total_pages'] = ceil($data['total_posts'] / $limit);
        }

        $this->load->view('header', $data);
        $this->load->view('blog/search', $data);
        $this->load->view('footer', $data);
    }
}