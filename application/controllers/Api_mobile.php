<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_mobile extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form', 'nb'));
        $this->load->database();
        $this->load->library('form_validation');
        $this->load->model(array(
            'Banner_model', 'Housing_news_model', 'Feedback_model', 'Live_update_model', 'Contact_model',
            'Enquiry_model', 'Nb_property_model', 'Nb_user_model', 'Nb_amenity_model', 'Nb_city_model',
            'Blog_model', 'User_model', 'Location_model', 'Category_model', 'Offer_banner_model',
            'Reelsvideo_model', 'Video_model',
        ));
        $this->output->set_content_type('application/json');
        $this->_cors();
    }

    private function _cors()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        if (strtoupper((string) $this->input->server('REQUEST_METHOD')) === 'OPTIONS') {
            $this->output->set_output('');
            exit;
        }
    }

    private function _json($payload, $code = 200)
    {
        $this->output->set_status_header($code);
        $this->output->set_output(json_encode($payload));
    }

    private function _asset_url_or_null($path)
    {
        return nb_public_asset_url($path);
    }

    private function _input_json_or_post()
    {
        $contentType = (string) $this->input->server('CONTENT_TYPE');

        if (stripos($contentType, 'multipart/form-data') !== false) {
            return array_merge($this->input->post(), $this->input->get());
        }

        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            return is_array($json) ? $json : array();
        }
        return array_merge($this->input->post(), $this->input->get());
    }

    private function _normalize_multi_images($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }
        if (!is_array($value)) {
            return null;
        }
        $out = array();
        foreach ($value as $item) {
            $item = trim((string) $item);
            if ($item !== '') {
                $out[] = $item;
            }
        }
        return !empty($out) ? array_values(array_unique($out)) : null;
    }

    private function _format_housing_news($row)
    {
        $formatted = nb_housing_news_to_blog($row);
        return is_array($formatted) ? $formatted : array();
    }

    public function banners()
    {
        $params = $this->_input_json_or_post();
        $status = isset($params['status']) ? trim((string) $params['status']) : null;
        $limit = isset($params['limit']) && trim((string) $params['limit']) !== '' ? (int) $params['limit'] : null;
        $offset = isset($params['offset']) && trim((string) $params['offset']) !== '' ? (int) $params['offset'] : 0;
        $rows = $this->Banner_model->get_all($status, $limit, $offset);
        
        $out = array();
        foreach ($rows as $row) {
            $formatted = (array) $row;
            if (isset($formatted['image'])) {
                $formatted['image'] = $this->_asset_url_or_null($formatted['image']);
            }
            $out[] = $formatted;
        }

        if (method_exists($this->Banner_model, 'count_all')) {
            $count = $this->Banner_model->count_all($status);
        } else {
            $count = count($rows);
        }
        $this->_json(array('success' => true, 'data' => $out, 'count' => $count));
    }

    public function banners_create()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(array('success' => false, 'message' => 'POST only'), 405);
            return;
        }

        $input = $this->_input_json_or_post();
        $image = trim((string) ($input['image'] ?? ''));
        $status = trim((string) ($input['status'] ?? 'inactive'));

        if ($image === '') {
            $this->_json(array('success' => false, 'message' => 'image is required'), 422);
            return;
        }

        $data = array(
            'image' => $this->security->xss_clean($image),
            'status' => $status !== '' ? $status : 'inactive',
        );

        $id = $this->Banner_model->create($data);
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Could not create banner'), 500);
            return;
        }

        $this->_json(array('success' => true, 'message' => 'Banner created', 'id' => $id));
    }

    public function banners_update($id = 0)
    {
        $id = (int) $id;
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Invalid banner ID'), 400);
            return;
        }
        if ($this->input->method() !== 'post') {
            $this->_json(array('success' => false, 'message' => 'POST only'), 405);
            return;
        }

        $banner = $this->Banner_model->get_by_id($id);
        if (!$banner) {
            $this->_json(array('success' => false, 'message' => 'Banner not found'), 404);
            return;
        }

        $input = $this->_input_json_or_post();
        $update_data = array();
        if (isset($input['image'])) {
            $update_data['image'] = $this->security->xss_clean(trim((string) $input['image']));
        }
        if (isset($input['status'])) {
            $update_data['status'] = trim((string) $input['status']) ?: 'inactive';
        }

        if (empty($update_data)) {
            $this->_json(array('success' => false, 'message' => 'Nothing to update'), 422);
            return;
        }

        $this->Banner_model->update($id, $update_data);
        $this->_json(array('success' => true, 'message' => 'Banner updated', 'id' => $id));
    }

    public function feedback()
    {
        if ($this->input->method() === 'get') {
            $params = array_merge($this->input->get(), $this->input->post());
            $userId = trim((string) ($params['userId'] ?? $params['user_id'] ?? ''));
            if ($userId === '') {
                $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
                return;
            }
            $limit = isset($params['limit']) && trim((string) $params['limit']) !== '' ? (int) $params['limit'] : null;
            $offset = isset($params['offset']) && trim((string) $params['offset']) !== '' ? (int) $params['offset'] : 0;
            $rows = $this->Feedback_model->get_all($userId, $limit, $offset);
            $total = $this->Feedback_model->count_all($userId);
            $out = array();
            foreach ($rows as $row) {
                $out[] = $this->_format_feedback($row);
            }
            $this->_json(array(
                'success' => true,
                'data' => array(
                    'feedbacks' => $out,
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                ),
            ));
            return;
        }

        if ($this->input->method() !== 'post') {
            $this->_json(array('success' => false, 'message' => 'GET or POST only'), 405);
            return;
        }

        $input = $this->_input_json_or_post();
        $userId = trim((string) ($input['userId'] ?? $input['user_id'] ?? ''));
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? $input['message'] ?? ''));
        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $phone = trim((string) ($input['phone'] ?? ''));

        if ($userId === '') {
            $this->_json(array('success' => false, 'message' => 'userId is required'), 422);
            return;
        }

        if ($title === '' && $name === '' && $description === '') {
            $this->_json(array('success' => false, 'message' => 'title or message is required'), 422);
            return;
        }

        if ($title === '') {
            if ($name !== '' && $email !== '') {
                $title = $name . ' (' . $email . ')';
            } else {
                $title = 'Mobile feedback';
            }
        }

        $data = array(
            'userId' => $userId,
            'title' => $this->security->xss_clean($title),
            'description' => $this->security->xss_clean($description . ($phone !== '' ? "\nPhone: " . $phone : '')),
            'createdAt' => date('Y-m-d H:i:s'),
        );
        if ($name !== '') {
            $data['name'] = $this->security->xss_clean($name);
        }

        $id = $this->Feedback_model->create($data);
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Could not save feedback'), 500);
            return;
        }

        $row = $this->Feedback_model->get_by_id($id);
        $this->_json(array(
            'success' => true,
            'message' => 'Feedback submitted',
            'id' => $id,
            'data' => $row ? $this->_format_feedback($row) : null,
        ));
    }

    private function _format_feedback($row)
    {
        $item = is_array($row) ? $row : (array) $row;
        $out = array(
            'id' => isset($item['id']) ? (string) $item['id'] : null,
            'userId' => isset($item['userId']) ? (string) $item['userId'] : null,
            'title' => isset($item['title']) ? (string) $item['title'] : '',
            'description' => isset($item['description']) ? (string) $item['description'] : '',
            'createdAt' => isset($item['createdAt']) ? (string) $item['createdAt'] : null,
        );
        if (isset($item['name']) && $item['name'] !== '') {
            $out['name'] = (string) $item['name'];
        }
        if (isset($item['image']) && $item['image'] !== '') {
            $out['image'] = $this->_asset_url_or_null($item['image']);
        }
        return $out;
    }

    public function contact()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(array('success' => false, 'message' => 'POST only'), 405);
            return;
        }

        $input = $this->_input_json_or_post();
        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $phone = trim((string) ($input['phone'] ?? ''));
        $subject = trim((string) ($input['subject'] ?? ''));
        $message = trim((string) ($input['message'] ?? ''));

        if ($name === '' || $email === '' || $message === '') {
            $this->_json(array('success' => false, 'message' => 'name, email and message are required'), 422);
            return;
        }

        $data = array(
            'title' => $this->security->xss_clean('Contact: ' . $subject . ' - ' . $name . ' (' . $email . ')'),
            'description' => $this->security->xss_clean($message . ( $phone ? "\nPhone: " . $phone : '' )),
            'createdAt' => date('Y-m-d H:i:s'),
        );

        $id = $this->Feedback_model->create($data);
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Could not save contact request'), 500);
            return;
        }

        $this->_json(array('success' => true, 'message' => 'Contact request submitted', 'id' => $id));
    }

    public function enquiry()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(array('success' => false, 'message' => 'POST only'), 405);
            return;
        }

        $input      = $this->_input_json_or_post();
        $email      = trim((string) ($input['email']      ?? ''));
        $phone      = trim((string) ($input['phone']      ?? ''));
        $propertyId = (int) ($input['propertyId'] ?? $input['property_id'] ?? 0);
        $userId     = (int) ($input['userId']     ?? $input['user_id']     ?? 0);
        $message    = trim((string) ($input['message']    ?? ''));
        $name       = trim((string) ($input['name']       ?? ''));

        $using_nb = $this->Enquiry_model->using_nb_enquiries();

        if ($using_nb) {
            // nb_enquiries: userId + propertyId + message + email required; no name column
            if ($userId <= 0) {
                $this->_json(array('success' => false, 'message' => 'userId is required'), 422);
                return;
            }
            if ($propertyId <= 0 || $message === '' || $email === '') {
                $this->_json(array('success' => false, 'message' => 'propertyId, email and message are required'), 422);
                return;
            }
        } else {
            if ($name === '' || $email === '' || $propertyId <= 0) {
                $this->_json(array('success' => false, 'message' => 'name, email and propertyId are required'), 422);
                return;
            }
        }

        $data = array(
            'name'        => $this->security->xss_clean($name),
            'email'       => $this->security->xss_clean($email),
            'phone'       => $this->security->xss_clean($phone),
            'property_id' => $propertyId,
            'user_id'     => $userId > 0 ? $userId : null,
            'message'     => $this->security->xss_clean($message),
            'status'      => 'new',
            'created_at'  => date('Y-m-d H:i:s'),
        );

        $id = $this->Enquiry_model->create($data);
        if (!$id) {
            $this->_json(array('success' => false, 'message' => 'Could not save enquiry'), 500);
            return;
        }

        $this->_json(array('success' => true, 'message' => 'Enquiry submitted', 'id' => $id));
    }

    public function enquiries_by_user($userId = null)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            $this->_json(array('success' => false, 'message' => 'Invalid user ID'), 400);
            return;
        }

        $params = array_merge($this->input->get(), $this->input->post());
        $status = isset($params['status']) && trim((string) $params['status']) !== ''
            ? trim((string) $params['status'])
            : null;
        $limit = isset($params['limit']) && trim((string) $params['limit']) !== '' ? (int) $params['limit'] : null;
        $offset = isset($params['offset']) && trim((string) $params['offset']) !== '' ? (int) $params['offset'] : 0;

        $rows = $this->Enquiry_model->get_by_userid($userId, $status, $limit, $offset);
        $total = $this->Enquiry_model->count_by_userid($userId, $status);

        $out = array();
        foreach ($rows as $row) {
            $item = (array) $row;
            // Format property thumbnail from images JSON
            if (!empty($item['property_images'])) {
                $imgs = json_decode($item['property_images'], true);
                $item['property_thumbnail'] = (!empty($imgs) && is_array($imgs))
                    ? $this->_asset_url_or_null($imgs[0])
                    : null;
            } else {
                $item['property_thumbnail'] = null;
            }
            unset($item['property_images']);
            $out[] = $item;
        }

        $this->_json(array(
            'success' => true,
            'data' => $out,
            'total' => (int) $total,
            'limit' => $limit,
            'offset' => $offset,
        ));
    }

    public function enquiries_by_customer($customerId = null)
    {
        $customerId = (int) $customerId;
        if ($customerId <= 0) {
            $this->_json(array('success' => false, 'message' => 'Invalid customer ID'), 400);
            return;
        }
        $rows = $this->Enquiry_model->get_by_customer_id($customerId);
        $this->_json(array('success' => true, 'data' => $rows));
    }

    public function housing_news()
    {
        $params = $this->_input_json_or_post();
        $category = isset($params['category']) ? trim((string) $params['category']) : null;
        if ($category === '') { $category = null; }
        $limit = isset($params['limit']) && trim((string) $params['limit']) !== '' ? (int) $params['limit'] : null;
        $offset = isset($params['offset']) && trim((string) $params['offset']) !== '' ? (int) $params['offset'] : 0;
        
        $rows = $this->Housing_news_model->get_all($category, $limit, $offset);
        $count = $this->Housing_news_model->count_all($category);
        
        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->_format_housing_news($row);
        }
        
        $this->_json(array(
            'success' => true, 
            'housingNews' => $out, 
            'total' => (int) $count,
            'limit' => $limit,
            'offset' => $offset
        ));
    }

    public function housing_news_create()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(array('success' => false, 'message' => 'POST only'), 405);
            return;
        }

        $input = $this->_input_json_or_post();
        $title = trim((string) ($input['title'] ?? ''));
        $subtitle = trim((string) ($input['subtitle'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $authorName = trim((string) ($input['authorName'] ?? ''));
        $category = trim((string) ($input['category'] ?? 'market'));
        $allowed = array('market', 'tips', 'legal');
        if ($title === '') {
            $this->_json(array('success' => false, 'message' => 'title is required'), 422);
            return;
        }
        if (!in_array($category, $allowed, true)) {
            $category = 'market';
        }

        $data = array(
            'title' => $title,
            'subtitle' => $subtitle !== '' ? $subtitle : null,
            'description' => $description !== '' ? $description : null,
            'authorName' => $authorName !== '' ? $authorName : null,
            'category' => $category,
            'multiImages' => $this->_normalize_multi_images($input['multiImages']),
        );

        if ($data['multiImages'] !== null) {
            $data['multiImages'] = json_encode($data['multiImages']);
        }

        $id = $this->Housing_news_model->create($data);
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Could not create housing news'), 500);
            return;
        }

        $this->_json(array('success' => true, 'message' => 'Housing news created', 'id' => $id));
    }

    public function housing_news_update($id = 0)
    {
        $id = (int) $id;
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Invalid housing news ID'), 400);
            return;
        }
        if ($this->input->method() !== 'post') {
            $this->_json(array('success' => false, 'message' => 'POST only'), 405);
            return;
        }

        $item = $this->Housing_news_model->get_by_id($id);
        if (!$item) {
            $this->_json(array('success' => false, 'message' => 'Housing news not found'), 404);
            return;
        }

        $input = $this->_input_json_or_post();
        $update_data = array();
        if (isset($input['title'])) {
            $title = trim((string) $input['title']);
            if ($title === '') {
                $this->_json(array('success' => false, 'message' => 'title is required'), 422);
                return;
            }
            $update_data['title'] = $title;
        }
        if (isset($input['subtitle'])) {
            $update_data['subtitle'] = trim((string) $input['subtitle']) ?: null;
        }
        if (isset($input['description'])) {
            $update_data['description'] = trim((string) $input['description']) ?: null;
        }
        if (isset($input['authorName'])) {
            $update_data['authorName'] = trim((string) $input['authorName']) ?: null;
        }
        if (isset($input['category'])) {
            $category = trim((string) $input['category']);
            $allowed = array('market', 'tips', 'legal');
            $update_data['category'] = in_array($category, $allowed, true) ? $category : 'market';
        }
        if (array_key_exists('multiImages', $input)) {
            $normalized = $this->_normalize_multi_images($input['multiImages']);
            $update_data['multiImages'] = $normalized !== null ? json_encode($normalized) : null;
        }

        if (empty($update_data)) {
            $this->_json(array('success' => false, 'message' => 'Nothing to update'), 422);
            return;
        }

        $this->Housing_news_model->update($id, $update_data);
        $this->_json(array('success' => true, 'message' => 'Housing news updated', 'id' => $id));
    }

    public function housing_news_item($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Invalid housing news ID'), 400);
            return;
        }

        $row = $this->Housing_news_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('success' => false, 'message' => 'Housing news not found'), 404);
            return;
        }

        $this->_json(array('success' => true, 'data' => $this->_format_housing_news($row)));
    }

    // ============================================
    // Live Updates API
    // ============================================

    private function _format_live_update($row)
    {
        $out = (array) $row;
        if (isset($out['image'])) {
            $out['image'] = $this->_asset_url_or_null($out['image']);
        }
        return $out;
    }

    public function live_updates()
    {
        $params = $this->_input_json_or_post();
        $limit = isset($params['limit']) ? (int) $params['limit'] : null;
        $offset = isset($params['offset']) ? (int) $params['offset'] : 0;
        $upcoming = !empty($params['upcoming']);
        $status = isset($params['status']) ? trim((string) $params['status']) : null;
        
        $rows = $this->Live_update_model->get_list(null, $limit, $offset, $upcoming, $status);
        $total = $this->Live_update_model->count_for_list(null, $upcoming, $status);
        
        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->_format_live_update($row);
        }

        $this->_json(array(
            'success' => true,
            'data' => $out,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ));
    }

    public function live_update($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Invalid ID'), 400);
            return;
        }
        $row = $this->Live_update_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('success' => false, 'message' => 'Live update not found'), 404);
            return;
        }
        $this->_json(array('success' => true, 'data' => $this->_format_live_update($row)));
    }

    public function live_updates_for_user($userId = null)
    {
        if ($userId === null || $userId === '') {
            $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
            return;
        }
        // Support both GET query params and POST/JSON body
        $get    = $this->input->get();
        $params = $this->_input_json_or_post();
        $merged = array_merge((array) $get, (array) $params);
        $limit  = isset($merged['limit'])  && trim((string) $merged['limit'])  !== '' ? (int) $merged['limit']  : null;
        $offset = isset($merged['offset']) && trim((string) $merged['offset']) !== '' ? (int) $merged['offset'] : 0;

        $rows  = $this->Live_update_model->get_all_by_user($userId, $limit, $offset);
        $total = $this->Live_update_model->count_all_for_user($userId);

        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->_format_live_update($row);
        }

        $this->_json(array(
            'success' => true,
            'data'    => $out,
            'total'   => $total,
            'limit'   => $limit,
            'offset'  => $offset,
        ));
    }

    public function live_update_remove($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Invalid ID'), 400);
            return;
        }
        if ($this->input->method() !== 'post' && $this->input->method() !== 'delete') {
            $this->_json(array('success' => false, 'message' => 'POST or DELETE only'), 405);
            return;
        }
        $row = $this->Live_update_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('success' => false, 'message' => 'Live update not found'), 404);
            return;
        }
        $this->Live_update_model->delete($id);
        $this->_json(array('success' => true, 'message' => 'Live update deleted'));
    }

    public function live_update_create()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(array('success' => false, 'message' => 'POST only'), 405);
            return;
        }
        $input = $this->_input_json_or_post();
        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            $this->_json(array('success' => false, 'message' => 'title is required'), 422);
            return;
        }
        
        $data = array(
            'title' => $this->security->xss_clean($title),
            'platform' => trim((string) ($input['platform'] ?? 'app')),
            'url' => trim((string) ($input['url'] ?? '')),
            'description' => $this->security->xss_clean(trim((string) ($input['description'] ?? ''))),
            'image' => trim((string) ($input['image'] ?? '')),
            'liveTime' => trim((string) ($input['liveTime'] ?? date('Y-m-d H:i:s'))),
            'status' => trim((string) ($input['status'] ?? 'upcoming')),
            'createdAt' => date('Y-m-d H:i:s'),
        );

        if ($this->Live_update_model->has_user_column()) {
            $uid = trim((string) ($input['userId'] ?? $input['user_id'] ?? ''));
            if ($uid === '') {
                $uid = (string) (int) $this->session->userdata('nb_user_id');
            }
            if ($uid !== '') {
                $data['userId'] = $uid;
            }
        }

        if (!empty($_FILES['image_file']['name'])) {
            $upload_dir = FCPATH . 'assets/uploads/live_updates/';
            if (!is_dir($upload_dir) && !@mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
                $this->_json(array('success' => false, 'message' => 'Could not prepare image upload directory'), 500);
                return;
            }
            if (!empty($_FILES['image_file']['error']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
                $this->_json(array('success' => false, 'message' => 'Image upload failed: file could not be received'), 400);
                return;
            }
            $this->load->library('upload');
            $cfg = array(
                'upload_path' => $upload_dir,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size' => 5120,
                'encrypt_name' => true,
            );
            $this->upload->initialize($cfg);
            if (!$this->upload->do_upload('image_file')) {
                $this->_json(array(
                    'success' => false,
                    'message' => 'Image upload failed: ' . strip_tags($this->upload->display_errors('', '')),
                ), 400);
                return;
            }
            $u = $this->upload->data();
            $data['image'] = 'assets/uploads/live_updates/' . $u['file_name'];
        }
        
        $id = $this->Live_update_model->create($data);
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Could not create live update'), 500);
            return;
        }
        $this->_json(array('success' => true, 'message' => 'Live update created', 'id' => $id));
    }

    public function live_update_save($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Invalid ID'), 400);
            return;
        }
        if ($this->input->method() !== 'post' && $this->input->method() !== 'put') {
            $this->_json(array('success' => false, 'message' => 'POST or PUT only'), 405);
            return;
        }
        
        $row = $this->Live_update_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('success' => false, 'message' => 'Live update not found'), 404);
            return;
        }
        
        $input = $this->_input_json_or_post();
        $update = array();
        foreach (array('title', 'platform', 'url', 'description', 'image', 'liveTime', 'status', 'userId') as $f) {
            if (isset($input[$f])) {
                if ($f === 'title' || $f === 'description') {
                    $update[$f] = $this->security->xss_clean(trim((string) $input[$f]));
                } elseif ($f === 'userId') {
                    if ($this->Live_update_model->has_user_column()) {
                        $update[$f] = trim((string) $input[$f]);
                    }
                } else {
                    $update[$f] = trim((string) $input[$f]);
                }
            }
        }

        if (!empty($_FILES['image_file']['name'])) {
            $upload_dir = FCPATH . 'assets/uploads/live_updates/';
            if (!is_dir($upload_dir) && !@mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
                $this->_json(array('success' => false, 'message' => 'Could not prepare image upload directory'), 500);
                return;
            }
            if (!empty($_FILES['image_file']['error']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
                $this->_json(array('success' => false, 'message' => 'Image upload failed: file could not be received'), 400);
                return;
            }
            $this->load->library('upload');
            $cfg = array(
                'upload_path' => $upload_dir,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size' => 5120,
                'encrypt_name' => true,
            );
            $this->upload->initialize($cfg);
            if (!$this->upload->do_upload('image_file')) {
                $this->_json(array(
                    'success' => false,
                    'message' => 'Image upload failed: ' . strip_tags($this->upload->display_errors('', '')),
                ), 400);
                return;
            }
            if (!empty($row->image) && strpos((string) $row->image, 'assets/uploads/live_updates/') === 0) {
                $old = FCPATH . $row->image;
                if (is_file($old)) {
                    @unlink($old);
                }
            }
            $u = $this->upload->data();
            $update['image'] = 'assets/uploads/live_updates/' . $u['file_name'];
        }
        
        if (empty($update)) {
            $this->_json(array('success' => false, 'message' => 'Nothing to update'), 422);
            return;
        }
        
        $this->Live_update_model->update($id, $update);
        $this->_json(array('success' => true, 'message' => 'Live update updated', 'id' => $id));
    }

    // ============================================
    // Properties Core API (nb_properties table)
    // ============================================

    private function _format_property_core($row)
    {
        $out = (array) $row;
        foreach (array('images', 'amenities', 'nearby') as $f) {
            if (isset($out[$f]) && is_string($out[$f]) && $out[$f] !== '') {
                $decoded = json_decode($out[$f], true);
                $out[$f] = is_array($decoded) ? $decoded : array();
            } elseif (!isset($out[$f])) {
                $out[$f] = array();
            }
        }
        if (!empty($out['images']) && is_array($out['images'])) {
            $urls = array();
            foreach ($out['images'] as $img) {
                $url = $this->_asset_url_or_null($img);
                if ($url !== null) { $urls[] = $url; }
            }
            $out['images'] = $urls;
        }
        if (!empty($out['location_image'])) {
            $out['location_image_url'] = $this->_asset_url_or_null($out['location_image']);
        }
        if (!empty($out['home_banner_image'])) {
            $out['home_banner_image_url'] = $this->_asset_url_or_null($out['home_banner_image']);
        }
        if (isset($out['map_url']) && $out['map_url'] === '') {
            $out['map_url'] = null;
        }
        foreach (array('brochure_url', 'audio_notes_url') as $mediaField) {
            if (!empty($out[$mediaField])) {
                $out[$mediaField] = $this->_asset_url_or_null($out[$mediaField]);
            }
        }
        if (isset($out['is_active'])) {
            $out['publication_status'] = !empty($out['is_active']) ? 'published' : 'pending';
        }
        return $out;
    }

    public function properties_core()
    {
        $params = $this->_input_json_or_post();

        $filters = array();
        foreach (array('listing_type', 'property_type', 'sort') as $f) {
            if (isset($params[$f]) && trim((string) $params[$f]) !== '') {
                $filters[$f] = trim((string) $params[$f]);
            }
        }
        foreach (array('city_id', 'bedrooms') as $f) {
            if (isset($params[$f]) && trim((string) $params[$f]) !== '') {
                $filters[$f] = (int) $params[$f];
            }
        }
        foreach (array('min_price', 'max_price') as $f) {
            if (isset($params[$f]) && trim((string) $params[$f]) !== '') {
                $filters[$f] = (float) $params[$f];
            }
        }
        foreach (array('locality_q', 'lat', 'lng', 'radius_km') as $f) {
            if (isset($params[$f]) && trim((string) $params[$f]) !== '') {
                $filters[$f] = trim((string) $params[$f]);
            }
        }
        // userId / user_id / owner_id all map to owner_id
        $uid = $params['userId'] ?? $params['user_id'] ?? $params['owner_id'] ?? null;
        if ($uid !== null && trim((string) $uid) !== '') {
            $filters['owner_id'] = (int) $uid;
        }
        if (!empty($params['is_featured'])) {
            $filters['is_featured'] = 1;
        }

        $limit  = isset($params['limit'])  && trim((string) $params['limit'])  !== '' ? (int) $params['limit']  : 20;
        $offset = isset($params['offset']) && trim((string) $params['offset']) !== '' ? (int) $params['offset'] : 0;

        $rows  = $this->Nb_property_model->search($filters, $limit, $offset);
        $total = $this->Nb_property_model->count_search($filters);

        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->_format_property_core($row);
        }

        $this->_json(array(
            'success' => true,
            'data'    => $out,
            'total'   => (int) $total,
            'limit'   => $limit,
            'offset'  => $offset,
        ));
    }

    public function property_core($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            $this->_json(array('success' => false, 'message' => 'Invalid property ID'), 400);
            return;
        }
        $row = $this->Nb_property_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('success' => false, 'message' => 'Property not found'), 404);
            return;
        }
        if (!empty($row->is_active)) {
            $this->Nb_property_model->increment_views($id);
            $row = $this->Nb_property_model->get_by_id($id);
        }
        $this->_json(array('success' => true, 'data' => $this->_format_property_core($row)));
    }

    public function properties_create()
    {
        return $this->_property_save_internal();
    }

    public function property_update($id = 0)
    {
        return $this->_property_save_internal((int) $id);
    }

    public function property_delete($id = 0)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid ID'), 400);
        }
        if ($this->input->method() !== 'post' && $this->input->method() !== 'delete') {
            return $this->_json(array('success' => false, 'message' => 'POST or DELETE only'), 405);
        }

        $row = $this->Nb_property_model->get_by_id($id);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'Property not found'), 404);
        }

        // Check ownership if not admin
        $u = $this->session->userdata('nb_user');
        $session_uid = (int) $this->session->userdata('nb_user_id');
        $is_admin = $u && isset($u['role']) && $u['role'] === 'admin' && isset($u['status']) && $u['status'] === 'approved';

        if (!$is_admin && (int) $row->owner_id !== $session_uid) {
             return $this->_json(array('success' => false, 'message' => 'Forbidden'), 403);
        }

        $this->Nb_property_model->delete($id);
        $this->_json(array('success' => true, 'message' => 'Property deleted'));
    }

    private function _property_save_internal($id = 0)
    {
        $this->load->library('Nb_api_token');
        $this->nb_api_token->try_attach_session();

        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }

        $input = $this->_input_json_or_post();
        $u = $this->session->userdata('nb_user');
        $session_uid = (int) $this->session->userdata('nb_user_id');
        $is_admin = $u && isset($u['role']) && $u['role'] === 'admin' && isset($u['status']) && $u['status'] === 'approved';

        $owner_id = null;
        $post_owner = (int) ($input['owner_id'] ?? $input['userId'] ?? $input['user_id'] ?? 0);

        if ($session_uid > 0) {
            $owner_id = $session_uid;
        } elseif ($post_owner > 0) {
            if (!$this->_valid_owner_for_property($post_owner)) {
                return $this->_json(array('success' => false, 'message' => 'Invalid owner account'), 400);
            }
            $owner_id = $post_owner;
        } else {
            return $this->_json(array('success' => false, 'message' => 'Login required or owner_id is required'), 401);
        }

        $existing = null;
        if ($id > 0) {
            $existing = $this->Nb_property_model->get_by_id($id);
            if (!$existing) {
                return $this->_json(array('success' => false, 'message' => 'Property not found'), 404);
            }
            if (!$is_admin && (int) $existing->owner_id !== $owner_id) {
                return $this->_json(array('success' => false, 'message' => 'Forbidden'), 403);
            }
        }

        // Populate $_POST for form_validation
        if (stripos((string) $this->input->server('CONTENT_TYPE'), 'application/json') !== false) {
            $_POST = array_merge($_POST, $input);
        }

        $this->form_validation->set_rules('title', 'Title', 'required|trim|max_length[300]');
        $this->form_validation->set_rules('property_type', 'Type', 'required|trim');
        $this->form_validation->set_rules('listing_type', 'Listing', 'required|in_list[rent,sale]');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');
        $this->form_validation->set_rules('address', 'Address', 'required|trim');
        $this->form_validation->set_rules('locality', 'Locality', 'trim|max_length[200]');
        $this->form_validation->set_rules('city_id', 'City', 'required|integer');

        if (!$this->form_validation->run()) {
            return $this->_json(array('success' => false, 'message' => validation_errors(' ', " \n")));
        }

        $amenities = isset($input['amenities']) ? $input['amenities'] : array();
        if (!is_array($amenities)) { $amenities = array(); }
        
        $row = array(
            'title'            => $this->security->xss_clean($input['title'] ?? ''),
            'description'      => $input['description'] ?? '',
            'property_type'    => $input['property_type'] ?? '',
            'listing_type'     => $input['listing_type'] ?? '',
            'price'            => (float) ($input['price'] ?? 0),
            'bedrooms'         => isset($input['bedrooms']) && $input['bedrooms'] !== '' ? (int) $input['bedrooms'] : null,
            'bathrooms'        => isset($input['bathrooms']) && $input['bathrooms'] !== '' ? (int) $input['bathrooms'] : null,
            'area_sqft'        => isset($input['area_sqft']) && $input['area_sqft'] !== '' ? (int) $input['area_sqft'] : null,
            'address'          => $input['address'] ?? '',
            'locality'         => $this->security->xss_clean($input['locality'] ?? ''),
            'city_id'          => (int) ($input['city_id'] ?? 0),
            'location'         => $this->security->xss_clean($input['location'] ?? ''),
            'is_price_negotiable' => !empty($input['is_price_negotiable']) ? 1 : 0,
            'rate_per_sqft'       => $this->_parse_optional_decimal($input['rate_per_sqft'] ?? null),
            'available_from'      => $this->_parse_date_field($input['available_from'] ?? null),
            'plot_length_ft'      => $this->_parse_optional_decimal($input['plot_length_ft'] ?? null),
            'plot_width_ft'       => $this->_parse_optional_decimal($input['plot_width_ft'] ?? null),
            'has_boundary_wall'   => $this->_parse_boundary_wall($input['has_boundary_wall'] ?? null),
            'amenities'           => json_encode($amenities),
        );

        if ($this->db->field_exists('nearby', 'nb_properties')) {
            $row['nearby'] = $this->_nearby_json_from_input($input);
        }
        if ($this->db->field_exists('video_url', 'nb_properties')) {
            $v = nb_sanitize_video_url($input['video_url'] ?? $input['video'] ?? '');
            $row['video_url'] = $v;
        }

        if (!empty($_FILES['location_image']['name'])) {
            $config = array(
                'upload_path'   => FCPATH . 'assets/uploads/nb_properties/',
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size'      => 5120,
                'encrypt_name'  => true,
            );
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            if ($this->upload->do_upload('location_image')) {
                $upd_data = $this->upload->data();
                $row['location_image'] = 'assets/uploads/nb_properties/' . $upd_data['file_name'];
                if ($existing && !empty($existing->location_image) && file_exists(FCPATH . $existing->location_image)) {
                    @unlink(FCPATH . $existing->location_image);
                }
            }
        }

        nb_ensure_property_map_columns();
        if ($this->db->field_exists('latitude', 'nb_properties')) {
            $row['latitude'] = $this->_parse_latitude($input['latitude'] ?? $input['lat'] ?? null);
        }
        if ($this->db->field_exists('longitude', 'nb_properties')) {
            $row['longitude'] = $this->_parse_longitude($input['longitude'] ?? $input['lng'] ?? null);
        }
        if ($this->db->field_exists('google_place_id', 'nb_properties')) {
            $row['google_place_id'] = $this->_parse_google_place_id($input['google_place_id'] ?? $input['place_id'] ?? null);
        }
        if ($this->db->field_exists('map_url', 'nb_properties')) {
            $map_url = trim((string) ($input['map_url'] ?? $input['mapUrl'] ?? ''));
            $row['map_url'] = $map_url !== '' ? substr($map_url, 0, 500) : null;
        }

        $media_errors = $this->_apply_listing_media_uploads($row, $existing);
        if (!empty($media_errors)) {
            return $this->_json(array('success' => false, 'message' => implode(' ', $media_errors)), 400);
        }

        if ($is_admin) {
            $row['owner_id'] = $owner_id;
            if ($this->db->field_exists('is_active', 'nb_properties')) {
                $row['is_active'] = !empty($input['is_active']) ? 1 : 0;
            }
            if ($this->db->field_exists('is_featured', 'nb_properties')) {
                $row['is_featured'] = !empty($input['is_featured']) ? 1 : 0;
            }
            if ($this->db->field_exists('is_latest', 'nb_properties')) {
                $row['is_latest'] = !empty($input['is_latest']) ? 1 : 0;
            }
        } else {
            $row['owner_id'] = $owner_id;
            if ($this->db->field_exists('is_active', 'nb_properties')) {
                $row['is_active'] = 0;
            }
        }

        if ($this->db->field_exists('slug', 'nb_properties')) {
            if ($id > 0) {
                if ($this->Nb_property_model->slug_is_empty($existing->slug) || strcmp($existing->title, $row['title']) !== 0) {
                    $row['slug'] = $this->Nb_property_model->unique_slug($row['title'], $id);
                }
            } else {
                $row['slug'] = $this->Nb_property_model->unique_slug($row['title'], null);
            }
        }

        $new_paths = $this->_upload_property_images($id > 0 ? $id : null);
        $existing_paths = $input['existing_paths'] ?? array();
        if (!is_array($existing_paths)) { $existing_paths = array(); }
        $remove = $input['remove_existing'] ?? array();
        if (!is_array($remove)) { $remove = array(); }
        
        $kept = array();
        foreach ($existing_paths as $ep) {
            $ep = trim((string) $ep);
            if ($ep !== '' && !in_array($ep, $remove, true)) { $kept[] = $ep; }
        }
        $all_images = array_merge($kept, $new_paths);
        if (count($all_images) > 10) { $all_images = array_slice($all_images, 0, 10); }
        $cover_index = (int) ($input['cover_index'] ?? 0);
        $all_images = nb_reorder_cover($all_images, $cover_index);

        if ($id < 1 || ($input['image_action'] ?? '') === 'replace' || !empty($new_paths) || !empty($remove)) {
            $row['images'] = json_encode($all_images);
        }

        if ($id > 0) {
            $this->Nb_property_model->update($id, $row);
            $new_id = $id;
        } else {
            $new_id = $this->Nb_property_model->create($row);
        }

        $saved = $this->Nb_property_model->get_by_id((int) $new_id);
        $payload = array('success' => true, 'property_id' => (int) $new_id);
        if ($saved) {
            $payload['property'] = $this->_property_response_payload($saved);
            if (!$is_admin && empty($saved->is_active)) {
                $payload['pending_review'] = true;
                $payload['message'] = $id > 0
                    ? 'Changes saved. Listing is pending admin approval.'
                    : 'Listing submitted for admin approval.';
            }
        }
        return $this->_json($payload);
    }

    private function _valid_owner_for_property($user_id)
    {
        $row = $this->Nb_user_model->get_by_id($user_id);
        return (bool) $row;
    }

    private function _upload_property_images($existing_id)
    {
        if (empty($_FILES['images']['name']) || !is_array($_FILES['images']['name'])) {
            return array();
        }
        $paths = array();
        $count = min(count($_FILES['images']['name']), 10);
        $upload_path = FCPATH . 'assets/uploads/nb_properties/';
        if (!is_dir($upload_path)) { mkdir($upload_path, 0755, true); }
        $this->load->library('upload');
        for ($i = 0; $i < $count; $i++) {
            if (empty($_FILES['images']['name'][$i])) { continue; }
            $_FILES['userfile'] = array(
                'name'     => $_FILES['images']['name'][$i],
                'type'     => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error'    => $_FILES['images']['error'][$i],
                'size'     => $_FILES['images']['size'][$i],
            );
            $config = array(
                'upload_path'   => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size'      => 5120,
                'encrypt_name'  => true,
            );
            $this->upload->initialize($config);
            if ($this->upload->do_upload('userfile')) {
                $data = $this->upload->data();
                $paths[] = 'assets/uploads/nb_properties/' . $data['file_name'];
            }
        }
        return $paths;
    }

    private function _property_response_payload($row)
    {
        $out = (array) $row;
        foreach (array('images', 'amenities', 'nearby') as $f) {
            if (isset($out[$f]) && is_string($out[$f]) && $out[$f] !== '') {
                $decoded = json_decode($out[$f], true);
                if (is_array($decoded)) { $out[$f] = $decoded; }
            }
        }
        if (isset($out['location_image']) && !empty($out['location_image'])) {
            $out['location_image_url'] = $this->_asset_url_or_null($out['location_image']);
        }
        if (isset($out['map_url']) && $out['map_url'] === '') {
            $out['map_url'] = null;
        }
        foreach (array('brochure_url', 'audio_notes_url') as $mediaField) {
            if (!empty($out[$mediaField])) {
                $out[$mediaField] = $this->_asset_url_or_null($out[$mediaField]);
            }
        }
        if (isset($out['images']) && is_array($out['images'])) {
            $out['image_urls'] = array();
            foreach ($out['images'] as $img) {
                $out['image_urls'][] = preg_match('#^https?://#i', $img) ? $img : base_url($img);
            }
        }
        // Owner contact fields (joined from nb_users)
        $out['owner_name']  = isset($out['owner_name'])  ? (string) $out['owner_name']  : '';
        $out['owner_phone'] = isset($out['owner_phone']) ? (string) $out['owner_phone'] : '';
        // Tag flags
        $out['tags_best_rate_localities']  = !empty($out['tags_best_rate_localities'])  ? 1 : 0;
        $out['tags_high_growth_localities'] = !empty($out['tags_high_growth_localities']) ? 1 : 0;
        return $out;
    }

    private function _parse_latitude($v) {
        if (!is_numeric($v)) return null;
        $f = (float) $v;
        return ($f < -90 || $f > 90) ? null : round($f, 8);
    }

    private function _parse_longitude($v) {
        if (!is_numeric($v)) return null;
        $f = (float) $v;
        return ($f < -180 || $f > 180) ? null : round($f, 8);
    }

    private function _parse_google_place_id($v) {
        $v = trim((string) $v);
        return ($v === '' || strlen($v) > 255) ? null : $v;
    }

    private function _parse_optional_decimal($v) {
        return is_numeric($v) ? round((float) $v, 2) : null;
    }

    private function _parse_date_field($v) {
        $t = strtotime((string) $v);
        return $t ? date('Y-m-d', $t) : null;
    }

    private function _parse_boundary_wall($v) {
        return ($v === '0' || $v === '1') ? (int) $v : null;
    }

    private function _nearby_json_from_input($input)
    {
        $nearby = $input['nearby'] ?? null;
        if (is_array($nearby)) { return json_encode($nearby); }
        return '[]';
    }

    // ============================================
    // Account
    // ============================================

    public function delete_account()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $input = $this->_input_json_or_post();
        $uid = $input['userId'] ?? $input['user_id'] ?? null;
        if (!$uid || (int) $uid <= 0) {
            return $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
        }
        $id = (int) $uid;
        $this->db->where('id', $id)->delete('nb_users');
        $this->_json(array('success' => true, 'message' => 'Account deleted successfully.'));
    }

    // ============================================
    // Mobile catalog routes (home, search, OTP, etc.)
    // ============================================

    private function _pagination_from_request($default_limit = 20, $max_limit = 50)
    {
        $page = max(1, (int) ($this->input->get('page') ?: 1));
        $limit = min($max_limit, max(1, (int) ($this->input->get('limit') ?: $default_limit)));
        $offset = max(0, ($page - 1) * $limit);
        if ($this->input->get('offset') !== null && $this->input->get('offset') !== '') {
            $offset = max(0, (int) $this->input->get('offset'));
        }
        return array($page, $limit, $offset);
    }

    private function _property_filters_from_query()
    {
        $params = array_merge($this->input->get(), $this->input->post());
        $filters = array();
        foreach (array('listing_type', 'property_type', 'sort') as $f) {
            if (isset($params[$f]) && trim((string) $params[$f]) !== '') {
                $filters[$f] = trim((string) $params[$f]);
            }
        }
        foreach (array('city_id', 'bedrooms') as $f) {
            if (isset($params[$f]) && trim((string) $params[$f]) !== '') {
                $filters[$f] = (int) $params[$f];
            }
        }
        foreach (array('min_price', 'max_price') as $f) {
            if (isset($params[$f]) && trim((string) $params[$f]) !== '') {
                $filters[$f] = (float) $params[$f];
            }
        }
        $keyword = isset($params['keyword']) ? trim((string) $params['keyword']) : '';
        if ($keyword === '' && isset($params['q'])) {
            $keyword = trim((string) $params['q']);
        }
        if ($keyword !== '') {
            $filters['locality_q'] = $keyword;
        }
        foreach (array('locality_q', 'lat', 'lng', 'radius_km') as $f) {
            if (isset($params[$f]) && trim((string) $params[$f]) !== '') {
                $filters[$f] = trim((string) $params[$f]);
            }
        }
        if (!empty($params['is_featured'])) {
            $filters['is_featured'] = 1;
        }
        if (!empty($params['include_pending']) || !empty($params['include_inactive'])) {
            $filters['include_pending'] = 1;
        }
        $uid = $params['userId'] ?? $params['user_id'] ?? $params['owner_id'] ?? null;
        if ($uid !== null && trim((string) $uid) !== '') {
            $filters['owner_id'] = (int) $uid;
        }
        return $filters;
    }

    private function _property_list_response(array $filters, $limit, $offset, $page = null)
    {
        $rows = $this->Nb_property_model->search($filters, $limit, $offset);
        $total = $this->Nb_property_model->count_search($filters);
        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->_format_property_core($row);
        }
        $payload = array(
            'success' => true,
            'data' => $out,
            'total' => (int) $total,
            'limit' => (int) $limit,
            'offset' => (int) $offset,
        );
        if ($page !== null) {
            $payload['page'] = (int) $page;
        }
        return $this->_json($payload);
    }

    public function home()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }

        $banners = array();
        foreach ($this->Banner_model->get_active() as $row) {
            $path = $this->Banner_model->row_image_path($row);
            if ($path === '') {
                continue;
            }
            $banners[] = array(
                'id' => (int) $row->id,
                'image' => $this->_asset_url_or_null($path),
            );
        }

        $featured = array();
        foreach ($this->Nb_property_model->search(array('is_featured' => 1), 10, 0) as $row) {
            $featured[] = $this->_format_property_core($row);
        }

        $latest = array();
        foreach ($this->Nb_property_model->search(array('sort' => 'new'), 10, 0) as $row) {
            $latest[] = $this->_format_property_core($row);
        }

        $this->_json(array(
            'success' => true,
            'banners' => $banners,
            'featured' => $featured,
            'latest' => $latest,
        ));
    }

    public function properties()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        list($page, $limit, $offset) = $this->_pagination_from_request();
        return $this->_property_list_response($this->_property_filters_from_query(), $limit, $offset, $page);
    }

    /** GET /api/mobile/properties/user/{userId} — listings posted by that owner. */
    public function properties_by_user($userId = null)
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $userId = (int) $userId;
        if ($userId <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid user ID'), 400);
        }
        list($page, $limit, $offset) = $this->_pagination_from_request();
        $filters = $this->_property_filters_from_query();
        $filters['owner_id'] = $userId;
        if (empty($filters['include_pending']) && ($this->input->get('is_active') === null || $this->input->get('is_active') === '')) {
            $filters['include_pending'] = 1;
        }
        return $this->_property_list_response($filters, $limit, $offset, $page);
    }

    public function search_properties()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        list($page, $limit, $offset) = $this->_pagination_from_request();
        return $this->_property_list_response($this->_property_filters_from_query(), $limit, $offset, $page);
    }

    public function featured_properties()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        list($page, $limit, $offset) = $this->_pagination_from_request(10, 20);
        $filters = $this->_property_filters_from_query();
        $filters['is_featured'] = 1;
        return $this->_property_list_response($filters, $limit, $offset, $page);
    }

    public function latest_properties()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        list($page, $limit, $offset) = $this->_pagination_from_request();
        $filters = $this->_property_filters_from_query();
        $filters['sort'] = 'new';
        return $this->_property_list_response($filters, $limit, $offset, $page);
    }

    private function _section_properties(array $sectionFilters)
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        list($page, $limit, $offset) = $this->_pagination_from_request();
        $filters = array_merge($this->_property_filters_from_query(), $sectionFilters);
        return $this->_property_list_response($filters, $limit, $offset, $page);
    }

    /** GET /api/mobile/properties/published — active listings, newest first. */
    public function published_properties()
    {
        return $this->_section_properties(array('sort' => 'new'));
    }

    /** GET /api/mobile/properties/best-rate */
    public function best_rate_properties()
    {
        return $this->_section_properties(array('tags_best_rate_localities' => 1));
    }

    /** GET /api/mobile/properties/high-growth */
    public function high_growth_properties()
    {
        return $this->_section_properties(array('tags_high_growth_localities' => 1));
    }

    /** GET /api/mobile/properties/recommended */
    public function recommended_properties()
    {
        return $this->_section_properties(array('is_recommended' => 1));
    }

    /** GET /api/mobile/properties/newly-launched */
    public function newly_launched_properties()
    {
        return $this->_section_properties(array('is_newly_launched' => 1));
    }

    /** GET /api/mobile/properties/verified */
    public function verified_properties()
    {
        return $this->_section_properties(array('is_verified_property' => 1));
    }

    /** GET /api/mobile/properties/premium */
    public function premium_properties()
    {
        return $this->_section_properties(array('is_premium' => 1));
    }

    /** GET /api/mobile/properties/home-banner */
    public function home_banner_properties()
    {
        return $this->_section_properties(array(
            'is_home_banner' => 1,
            'sort' => 'home_banner',
        ));
    }

    public function property($id = null)
    {
        return $this->property_core($id);
    }

    public function cities()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $rows = $this->Nb_city_model->all_active();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'id' => (int) $r->id,
                'name' => (string) $r->name,
                'state' => isset($r->state) ? (string) $r->state : '',
                'is_active' => 1,
                'image' => $this->_asset_url_or_null(isset($r->image) ? $r->image : null),
            );
        }
        $this->_json(array('success' => true, 'data' => $out));
    }

    public function city($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid city ID'), 400);
        }
        $row = $this->Nb_city_model->get_by_id($id);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'City not found'), 404);
        }
        $this->_json(array(
            'success' => true,
            'data' => array(
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'state' => isset($row->state) ? (string) $row->state : '',
                'image' => $this->_asset_url_or_null(isset($row->image) ? $row->image : null),
            ),
        ));
    }

    public function blogs()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }

        // Same rows as /api/mobile/housing-news (panel → Blogs / housing_news table).
        $params = $this->_input_json_or_post();
        $category = isset($params['category']) ? trim((string) $params['category']) : trim((string) $this->input->get('category'));
        if ($category === '') {
            $category = null;
        }
        $limitRaw = isset($params['limit']) ? $params['limit'] : $this->input->get('limit');
        $limit = ($limitRaw !== null && trim((string) $limitRaw) !== '') ? (int) $limitRaw : null;
        $offsetRaw = isset($params['offset']) ? $params['offset'] : $this->input->get('offset');
        $offset = ($offsetRaw !== null && trim((string) $offsetRaw) !== '') ? (int) $offsetRaw : 0;

        $rows = $this->Housing_news_model->get_all($category, $limit, $offset);
        $count = $this->Housing_news_model->count_all($category);
        $out = array();
        foreach ($rows as $r) {
            $item = nb_housing_news_to_blog($r);
            if ($item !== null) {
                $out[] = $item;
            }
        }
        $this->_json(array(
            'success' => true,
            'data' => $out,
            'count' => count($out),
            'total' => (int) $count,
            'limit' => $limit,
            'offset' => $offset,
        ));
    }

    /** GET /api/mobile/blogs/{id} — detail from housing_news (same id as housing-news/{id}). */
    public function blog($id = null)
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $id = (int) ($id ?: $this->input->get('id'));
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid blog ID'), 400);
        }
        $r = $this->Housing_news_model->get_by_id($id);
        if (!$r) {
            return $this->_json(array('success' => false, 'message' => 'Blog not found'), 404);
        }
        $this->_json(array('success' => true, 'data' => nb_housing_news_to_blog($r)));
    }

    public function categories()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $rows = $this->Category_model->get_all('active');
        $out = array();
        foreach ($rows as $r) {
            $out[] = (array) $r;
        }
        $this->_json(array('success' => true, 'data' => $out));
    }

    public function category($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid category ID'), 400);
        }
        $row = $this->Category_model->get_by_id($id);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'Category not found'), 404);
        }
        $this->_json(array('success' => true, 'data' => (array) $row));
    }

    public function locations()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $city_id = $this->input->get('city_id');
        $rows = $this->Location_model->get_all('active', $city_id ? (int) $city_id : null);
        $out = array();
        foreach ($rows as $r) {
            $out[] = (array) $r;
        }
        $this->_json(array('success' => true, 'data' => $out));
    }

    public function location($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid location ID'), 400);
        }
        $row = $this->Location_model->get_by_id($id);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'Location not found'), 404);
        }
        $this->_json(array('success' => true, 'data' => (array) $row));
    }

    public function locations_by_city($city_id = null)
    {
        $city_id = (int) $city_id;
        if ($city_id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid city ID'), 400);
        }
        $rows = $this->Location_model->get_by_city($city_id, 'active');
        $out = array();
        foreach ($rows as $r) {
            $out[] = (array) $r;
        }
        $this->_json(array('success' => true, 'data' => $out));
    }

    public function offer_banner()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $row = $this->Offer_banner_model->get_active();
        if (!$row) {
            return $this->_json(array('success' => true, 'data' => null));
        }
        $item = (array) $row;
        if (!empty($item['image'])) {
            $item['image_url'] = $this->_asset_url_or_null($item['image']);
        }
        $this->_json(array('success' => true, 'data' => $item));
    }

    public function offer_banners()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $rows = $this->Offer_banner_model->get_all('active');
        $out = array();
        foreach ($rows as $r) {
            $item = (array) $r;
            if (!empty($item['image'])) {
                $item['image_url'] = $this->_asset_url_or_null($item['image']);
            }
            $out[] = $item;
        }
        $this->_json(array('success' => true, 'data' => $out));
    }

    public function send_otp()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $this->load->library('session');
        $input = $this->_input_json_or_post();
        $phone = trim((string) ($input['phone'] ?? ''));
        $country_code = trim((string) ($input['country_code'] ?? '+91'));
        if ($phone === '') {
            return $this->_json(array('success' => false, 'message' => 'Phone number is required'), 400);
        }

        $test_phone = '9876543210';
        $test_otp = '123456';
        $otp = ($phone === $test_phone && $country_code === '+91')
            ? $test_otp
            : str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $otp_expires_at = date('Y-m-d H:i:s', time() + 300);

        $this->session->set_userdata($this->_mobile_otp_session_key($phone), array(
            'otp' => $otp,
            'expires_at' => $otp_expires_at,
            'phone' => $phone,
            'country_code' => $country_code,
        ));

        if ($this->db->table_exists('users')) {
            $user = $this->User_model->get_by_phone($phone, $country_code);
            if ($user) {
                $this->User_model->update_otp($phone, $country_code, $otp, $otp_expires_at);
            } else {
                @$this->User_model->create(array(
                    'phonenumber' => $phone,
                    'countrycode' => $country_code,
                    'otp' => $otp,
                    'otp_expires_at' => $otp_expires_at,
                    'is_verified' => 0,
                    'isactive' => 'active',
                ));
            }
        }

        $nb_user = $this->Nb_user_model->get_by_phone($phone);
        $this->_json(array(
            'success' => true,
            'message' => 'OTP sent',
            'otp' => $otp,
            'is_new_user' => !$nb_user,
        ));
    }

    public function verify_otp()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $this->load->library('session');
        $input = $this->_input_json_or_post();
        $phone = trim((string) ($input['phone'] ?? ''));
        $country_code = trim((string) ($input['country_code'] ?? '+91'));
        $otp = trim((string) ($input['otp'] ?? ''));
        if ($phone === '' || $otp === '') {
            return $this->_json(array('success' => false, 'message' => 'Phone number and OTP are required'), 400);
        }

        $valid = false;
        $stored = $this->session->userdata($this->_mobile_otp_session_key($phone));
        if (is_array($stored) && isset($stored['otp']) && (string) $stored['otp'] === $otp) {
            if (!empty($stored['expires_at']) && strtotime($stored['expires_at']) > time()) {
                $valid = true;
            }
        }
        if (!$valid && $this->db->table_exists('users')) {
            $result = $this->User_model->verify_otp($phone, $country_code, $otp);
            $valid = is_array($result) && !empty($result['success']);
        }
        if (!$valid && $phone === '9876543210' && $otp === '123456') {
            $valid = true;
        }
        if (!$valid) {
            return $this->_json(array('success' => false, 'message' => 'Invalid or expired OTP'), 400);
        }
        $this->session->unset_userdata($this->_mobile_otp_session_key($phone));

        $token = null;
        $nb_user = $this->Nb_user_model->get_by_phone($phone);
        $user_payload = array(
            'phone' => $phone,
            'country_code' => $country_code,
        );
        if ($nb_user) {
            if ($this->db->field_exists('api_token', 'nb_users')) {
                $token = bin2hex(random_bytes(32));
                $this->Nb_user_model->set_api_token((int) $nb_user->id, $token);
            }
            $this->session->set_userdata('nb_user_id', (int) $nb_user->id);
            $this->session->set_userdata('nb_user', array(
                'id' => (int) $nb_user->id,
                'name' => $nb_user->name,
                'email' => $nb_user->email,
                'phone' => isset($nb_user->phone) ? (string) $nb_user->phone : '',
                'role' => $nb_user->role,
                'status' => $nb_user->status,
            ));
            if ($token !== null) {
                nb_set_api_token_cookie($token);
            }
            $user_payload = array(
                'id' => (int) $nb_user->id,
                'name' => $nb_user->name,
                'email' => $nb_user->email,
                'phone' => isset($nb_user->phone) ? (string) $nb_user->phone : '',
                'country_code' => $country_code,
                'role' => $nb_user->role,
                'status' => $nb_user->status,
            );
        }

        $this->_json(array(
            'success' => true,
            'message' => 'OTP verified successfully',
            'token' => $token,
            'user' => $user_payload,
        ));
    }

    private function _mobile_otp_session_key($phone)
    {
        return 'mobile_otp_' . preg_replace('/\D/', '', (string) $phone);
    }

    public function resend_otp()
    {
        return $this->send_otp();
    }

    public function check()
    {
        $this->load->library('Nb_api_token');
        $token = $this->nb_api_token->read_token_from_request();
        if ($token !== '') {
            $nb_user = $this->Nb_user_model->get_by_api_token($token);
            if ($nb_user) {
                return $this->_json(array(
                    'success' => true,
                    'logged_in' => true,
                    'user_id' => (int) $nb_user->id,
                ));
            }
        }
        if ($this->session->userdata('user_logged_in')) {
            return $this->_json(array(
                'success' => true,
                'logged_in' => true,
                'user' => array(
                    'id' => $this->session->userdata('user_id'),
                    'name' => $this->session->userdata('user_name'),
                    'phone' => $this->session->userdata('user_phone'),
                ),
            ));
        }
        $this->_json(array('success' => true, 'logged_in' => false));
    }

    public function refresh_session()
    {
        return $this->check();
    }

    public function logout()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $this->load->library('Nb_api_token');
        $token = $this->nb_api_token->read_token_from_request();
        if ($token !== '') {
            $user = $this->Nb_user_model->get_by_api_token($token);
            if ($user) {
                $this->Nb_user_model->clear_api_token((int) $user->id);
            }
        }
        $this->session->unset_userdata(array(
            'user_logged_in', 'user_id', 'user_name', 'user_email', 'user_phone',
            'user_country_code', 'user_address', 'nb_user_id', 'nb_user',
        ));
        nb_clear_api_token_cookie();
        $this->_json(array('success' => true, 'message' => 'Logged out successfully'));
    }

    public function check_phone_exists()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $input = $this->_input_json_or_post();
        $phone = trim((string) ($input['phone'] ?? ''));
        $country_code = trim((string) ($input['country_code'] ?? '+91'));
        if ($phone === '') {
            return $this->_json(array('success' => false, 'message' => 'Phone number is required'), 400);
        }
        $exists = false;
        if ($this->db->table_exists('users')) {
            $exists = (bool) $this->User_model->get_by_phone($phone, $country_code);
        } else {
            $exists = (bool) $this->Nb_user_model->get_by_phone($phone);
        }
        $this->_json(array('success' => true, 'exists' => $exists));
    }

    public function save_profile()
    {
        return $this->_mobile_profile_save(false);
    }

    public function update_profile()
    {
        return $this->_mobile_profile_save(true);
    }

    private function _mobile_profile_save($is_update)
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $input = $this->_input_json_or_post();
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            return $this->_json(array('success' => false, 'message' => 'Name is required'), 400);
        }
        $user_id = (int) ($this->session->userdata('user_id') ?: ($input['userId'] ?? $input['user_id'] ?? 0));
        if ($user_id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Login required'), 401);
        }
        $data = array('name' => $name);
        if (isset($input['email'])) {
            $data['email'] = trim((string) $input['email']);
        }
        if (isset($input['address'])) {
            $data['address'] = trim((string) $input['address']);
        }
        $this->User_model->update($user_id, $data);
        $this->_json(array(
            'success' => true,
            'message' => $is_update ? 'Profile updated' : 'Profile saved',
        ));
    }

    public function change_phone()
    {
        return $this->send_otp();
    }

    public function verify_phone_change()
    {
        return $this->verify_otp();
    }

    public function referral_apply()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $this->_json(array('success' => true, 'message' => 'Referral applied'));
    }

    public function referral_list()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $this->_json(array('success' => true, 'data' => array()));
    }

    public function referral_stats()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $this->_json(array('success' => true, 'stats' => array('total' => 0, 'pending' => 0, 'approved' => 0)));
    }

    // ============================================
    // Reels & videos (YouTube URL)
    // ============================================

    private function _extract_youtube_video_id($url)
    {
        $parts = nb_video_embed_parts($url);
        return ($parts && isset($parts['type'], $parts['id']) && $parts['type'] === 'youtube') ? $parts['id'] : null;
    }

    private function _youtube_thumbnail_url($videoId)
    {
        return 'https://img.youtube.com/vi/' . $videoId . '/maxresdefault.jpg';
    }

    private function _format_youtube_media_row($row)
    {
        $item = (array) $row;
        $videoUrl = isset($item['videoUrl']) ? trim((string) $item['videoUrl']) : '';
        $videoId = $videoUrl !== '' ? $this->_extract_youtube_video_id($videoUrl) : null;
        $item['youtube_id'] = $videoId;
        $item['embed_url'] = $videoId ? 'https://www.youtube.com/embed/' . $videoId : null;
        if (!empty($item['thumbnail']) && !filter_var($item['thumbnail'], FILTER_VALIDATE_URL)) {
            $item['thumbnail'] = $this->_asset_url_or_null($item['thumbnail']);
        }
        $item['createdAt'] = isset($item['createdAt']) && $item['createdAt'] !== null && $item['createdAt'] !== ''
            ? (string) $item['createdAt']
            : null;
        return $item;
    }

    private function _youtube_media_payload_from_input(array $input, $requireTitle = false)
    {
        $videoUrl = trim((string) ($input['videoUrl'] ?? $input['video_url'] ?? ''));
        if ($videoUrl === '') {
            return array('error' => 'videoUrl is required', 'code' => 422);
        }

        $videoUrl = nb_sanitize_video_url($videoUrl);
        $videoId = $this->_extract_youtube_video_id($videoUrl);
        if (!$videoId) {
            return array('error' => 'Please provide a valid YouTube video URL', 'code' => 422);
        }

        $title = trim((string) ($input['title'] ?? ''));
        if ($requireTitle && $title === '') {
            return array('error' => 'title is required', 'code' => 422);
        }

        $data = array(
            'videoUrl' => $videoUrl,
            'thumbnail' => $this->_youtube_thumbnail_url($videoId),
        );
        if ($title !== '') {
            $data['title'] = $this->security->xss_clean($title);
        }

        $status = trim((string) ($input['status'] ?? ''));
        if ($status !== '') {
            $data['status'] = $status;
        } elseif ($requireTitle) {
            $data['status'] = 'active';
        }

        if (isset($input['index_no']) && trim((string) $input['index_no']) !== '') {
            $data['index_no'] = (int) $input['index_no'];
        }

        $thumb = trim((string) ($input['thumbnail'] ?? ''));
        if ($thumb !== '') {
            $data['thumbnail'] = $thumb;
        }

        return array('data' => $data);
    }

    public function reels()
    {
        $params = $this->_input_json_or_post();
        $status = isset($params['status']) ? trim((string) $params['status']) : null;
        if ($status === '') {
            $status = null;
        }
        $rows = $this->Reelsvideo_model->get_all($status);
        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->_format_youtube_media_row($row);
        }
        $this->_json(array('success' => true, 'data' => $out, 'count' => count($out)));
    }

    public function reel($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid reel ID'), 400);
        }
        $row = $this->Reelsvideo_model->get_by_id($id);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'Reel not found'), 404);
        }
        $this->_json(array('success' => true, 'data' => $this->_format_youtube_media_row($row)));
    }

    public function reels_create()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $input = $this->_input_json_or_post();
        $payload = $this->_youtube_media_payload_from_input($input, true);
        if (isset($payload['error'])) {
            return $this->_json(array('success' => false, 'message' => $payload['error']), $payload['code']);
        }
        $id = $this->Reelsvideo_model->create($payload['data']);
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Could not create reel'), 500);
        }
        $row = $this->Reelsvideo_model->get_by_id($id);
        $this->_json(array(
            'success' => true,
            'message' => 'Reel created',
            'id' => $id,
            'data' => $row ? $this->_format_youtube_media_row($row) : null,
        ));
    }

    public function reels_update($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid reel ID'), 400);
        }
        if ($this->input->method() !== 'post' && $this->input->method() !== 'put') {
            return $this->_json(array('success' => false, 'message' => 'POST or PUT only'), 405);
        }
        $row = $this->Reelsvideo_model->get_by_id($id);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'Reel not found'), 404);
        }

        $input = $this->_input_json_or_post();
        $update = array();
        if (isset($input['title'])) {
            $update['title'] = $this->security->xss_clean(trim((string) $input['title']));
        }
        if (isset($input['status'])) {
            $update['status'] = trim((string) $input['status']);
        }
        if (isset($input['index_no']) && trim((string) $input['index_no']) !== '') {
            $update['index_no'] = (int) $input['index_no'];
        }
        if (isset($input['videoUrl']) || isset($input['video_url'])) {
            $payload = $this->_youtube_media_payload_from_input($input, false);
            if (isset($payload['error'])) {
                return $this->_json(array('success' => false, 'message' => $payload['error']), $payload['code']);
            }
            $update = array_merge($update, $payload['data']);
        } elseif (isset($input['thumbnail'])) {
            $update['thumbnail'] = trim((string) $input['thumbnail']);
        }

        if (empty($update)) {
            return $this->_json(array('success' => false, 'message' => 'No fields to update'), 422);
        }

        $this->Reelsvideo_model->update($id, $update);
        $row = $this->Reelsvideo_model->get_by_id($id);
        $this->_json(array(
            'success' => true,
            'message' => 'Reel updated',
            'data' => $row ? $this->_format_youtube_media_row($row) : null,
        ));
    }

    public function reels_delete($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid reel ID'), 400);
        }
        if ($this->input->method() !== 'post' && $this->input->method() !== 'delete') {
            return $this->_json(array('success' => false, 'message' => 'POST or DELETE only'), 405);
        }
        $row = $this->Reelsvideo_model->get_by_id($id);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'Reel not found'), 404);
        }
        $this->Reelsvideo_model->delete($id);
        $this->_json(array('success' => true, 'message' => 'Reel deleted'));
    }

    public function reels_reorder()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $input = $this->_input_json_or_post();
        $orders = isset($input['orders']) && is_array($input['orders']) ? $input['orders'] : null;
        if ($orders === null && isset($input['order']) && is_array($input['order'])) {
            $orders = $input['order'];
        }
        if (empty($orders) || !is_array($orders)) {
            return $this->_json(array('success' => false, 'message' => 'orders object is required (id => index_no)'), 422);
        }
        if (!$this->Reelsvideo_model->update_orders($orders)) {
            return $this->_json(array('success' => false, 'message' => 'Could not reorder reels'), 500);
        }
        $this->_json(array('success' => true, 'message' => 'Reels reordered'));
    }

    public function videos()
    {
        $params = $this->_input_json_or_post();
        $status = isset($params['status']) ? trim((string) $params['status']) : null;
        if ($status === '') {
            $status = null;
        }
        $rows = $this->Video_model->get_all($status);
        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->_format_youtube_media_row($row);
        }
        $this->_json(array('success' => true, 'data' => $out, 'count' => count($out)));
    }

    public function video($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid video ID'), 400);
        }
        $row = $this->Video_model->get_by_id($id);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'Video not found'), 404);
        }
        $this->_json(array('success' => true, 'data' => $this->_format_youtube_media_row($row)));
    }

    public function videos_create()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $input = $this->_input_json_or_post();
        $payload = $this->_youtube_media_payload_from_input($input, true);
        if (isset($payload['error'])) {
            return $this->_json(array('success' => false, 'message' => $payload['error']), $payload['code']);
        }
        $id = $this->Video_model->create($payload['data']);
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Could not create video'), 500);
        }
        $row = $this->Video_model->get_by_id($id);
        $this->_json(array(
            'success' => true,
            'message' => 'Video created',
            'id' => $id,
            'data' => $row ? $this->_format_youtube_media_row($row) : null,
        ));
    }

    public function videos_update($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid video ID'), 400);
        }
        if ($this->input->method() !== 'post' && $this->input->method() !== 'put') {
            return $this->_json(array('success' => false, 'message' => 'POST or PUT only'), 405);
        }
        $row = $this->Video_model->get_by_id($id);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'Video not found'), 404);
        }

        $input = $this->_input_json_or_post();
        $update = array();
        if (isset($input['title'])) {
            $update['title'] = $this->security->xss_clean(trim((string) $input['title']));
        }
        if (isset($input['status'])) {
            $update['status'] = trim((string) $input['status']);
        }
        if (isset($input['index_no']) && trim((string) $input['index_no']) !== '') {
            $update['index_no'] = (int) $input['index_no'];
        }
        if (isset($input['videoUrl']) || isset($input['video_url'])) {
            $payload = $this->_youtube_media_payload_from_input($input, false);
            if (isset($payload['error'])) {
                return $this->_json(array('success' => false, 'message' => $payload['error']), $payload['code']);
            }
            $update = array_merge($update, $payload['data']);
        } elseif (isset($input['thumbnail'])) {
            $update['thumbnail'] = trim((string) $input['thumbnail']);
        }

        if (empty($update)) {
            return $this->_json(array('success' => false, 'message' => 'No fields to update'), 422);
        }

        $this->Video_model->update($id, $update);
        $row = $this->Video_model->get_by_id($id);
        $this->_json(array(
            'success' => true,
            'message' => 'Video updated',
            'data' => $row ? $this->_format_youtube_media_row($row) : null,
        ));
    }

    public function videos_delete($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Invalid video ID'), 400);
        }
        if ($this->input->method() !== 'post' && $this->input->method() !== 'delete') {
            return $this->_json(array('success' => false, 'message' => 'POST or DELETE only'), 405);
        }
        $row = $this->Video_model->get_by_id($id);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'Video not found'), 404);
        }
        $this->Video_model->delete($id);
        $this->_json(array('success' => true, 'message' => 'Video deleted'));
    }

    public function videos_reorder()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $input = $this->_input_json_or_post();
        $orders = isset($input['orders']) && is_array($input['orders']) ? $input['orders'] : null;
        if ($orders === null && isset($input['order']) && is_array($input['order'])) {
            $orders = $input['order'];
        }
        if (empty($orders) || !is_array($orders)) {
            return $this->_json(array('success' => false, 'message' => 'orders object is required (id => index_no)'), 422);
        }
        if (!$this->Video_model->update_orders($orders)) {
            return $this->_json(array('success' => false, 'message' => 'Could not reorder videos'), 500);
        }
        $this->_json(array('success' => true, 'message' => 'Videos reordered'));
    }

    // ============================================
    // Legal pages (rendered HTML for mobile WebView)
    // ============================================

    public function privacy_policy()
    {
        $this->output->set_content_type('text/html');
        $data = array('page_title' => 'Privacy Policy', 'legal_view' => 'privacy');
        $this->load->view('legal/mobile_layout', $data);
    }

    public function terms()
    {
        $this->output->set_content_type('text/html');
        $data = array('page_title' => 'Terms & Conditions', 'legal_view' => 'terms');
        $this->load->view('legal/mobile_layout', $data);
    }

    /** Upload brochure / audio notes on property create-update. @return string[] */
    private function _apply_listing_media_uploads(array &$row, $existing)
    {
        $errors = array();

        if ($this->db->field_exists('brochure_url', 'nb_properties')) {
            if (!empty($this->input->post('remove_brochure'))) {
                if ($existing && !empty($existing->brochure_url)) {
                    $this->_unlink_uploaded_file($existing->brochure_url);
                }
                $row['brochure_url'] = null;
            }
            $brochure = $this->_upload_single_document('brochure', 'brochures', 'pdf|doc|docx|jpg|jpeg|png|webp', 10240);
            if ($brochure['error'] !== null) {
                $errors[] = $brochure['error'];
            } elseif ($brochure['path'] !== null) {
                if ($existing && !empty($existing->brochure_url)) {
                    $this->_unlink_uploaded_file($existing->brochure_url);
                }
                $row['brochure_url'] = $brochure['path'];
            }
        }

        if ($this->db->field_exists('audio_notes_url', 'nb_properties')) {
            if (!empty($this->input->post('remove_audio_notes'))) {
                if ($existing && !empty($existing->audio_notes_url)) {
                    $this->_unlink_uploaded_file($existing->audio_notes_url);
                }
                $row['audio_notes_url'] = null;
            }
            $audio = $this->_upload_single_document('audio_notes', 'audio', 'mp3|wav|m4a|ogg|webm|aac', 15360);
            if ($audio['error'] !== null) {
                $errors[] = $audio['error'];
            } elseif ($audio['path'] !== null) {
                if ($existing && !empty($existing->audio_notes_url)) {
                    $this->_unlink_uploaded_file($existing->audio_notes_url);
                }
                $row['audio_notes_url'] = $audio['path'];
            }
        }

        return $errors;
    }

    /** @return array{path: string|null, error: string|null} */
    private function _upload_single_document($field, $subdir, $allowed_types, $max_kb)
    {
        if (empty($_FILES[$field]['name'])) {
            return array('path' => null, 'error' => null);
        }
        $label = ucfirst(str_replace('_', ' ', $field));
        if (!empty($_FILES[$field]['error']) && $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            return array(
                'path' => null,
                'error' => $label . ': upload failed (code ' . (int) $_FILES[$field]['error'] . ').',
            );
        }
        $upload_path = FCPATH . 'assets/uploads/nb_properties/' . trim($subdir, '/') . '/';
        if (!is_dir($upload_path) && !@mkdir($upload_path, 0755, true) && !is_dir($upload_path)) {
            return array('path' => null, 'error' => $label . ': could not create upload directory.');
        }
        $config = array(
            'upload_path' => $upload_path,
            'allowed_types' => $allowed_types,
            'max_size' => (int) $max_kb,
            'encrypt_name' => true,
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload($field)) {
            return array(
                'path' => null,
                'error' => $label . ': ' . strip_tags($this->upload->display_errors('', '')),
            );
        }
        $data = $this->upload->data();
        return array(
            'path' => 'assets/uploads/nb_properties/' . trim($subdir, '/') . '/' . $data['file_name'],
            'error' => null,
        );
    }

    private function _unlink_uploaded_file($relative_path)
    {
        $relative_path = trim((string) $relative_path);
        if ($relative_path === '' || preg_match('#^https?://#i', $relative_path)) {
            return;
        }
        $full = FCPATH . ltrim($relative_path, '/');
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
