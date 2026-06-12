<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * POST api/property/save — add/edit property (any approved non-admin user; admin may set owner_id;
 * valid owner_id may also be supplied without an NB session).
 */
class Property extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'nb'));
        $this->load->database();
        $this->load->model(array('Nb_property_model', 'Nb_user_model', 'Nb_amenity_model'));
        $this->load->library('session');
        $this->load->library('form_validation');
    }

    private function _input_json_or_post()
    {
        $contentType = (string) $this->input->server('CONTENT_TYPE');
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            return is_array($json) ? $json : array();
        }
        return array_merge($this->input->post(), $this->input->get());
    }

    public function save()
    {
        $this->load->library('Nb_api_token');
        $this->nb_api_token->try_attach_session();

        if ($this->input->method() !== 'post') {
            return $this->_json(array(
                'success' => false,
                'message' => 'This endpoint accepts POST only.',
            ), 405);
        }
        
        $respond_json = $this->input->is_ajax_request()
            || stripos((string) $this->input->server('HTTP_ACCEPT'), 'application/json') !== false
            || $this->nb_api_token->read_token_from_request() !== '';
        
        $input = $this->_input_json_or_post();
        
        $u = $this->session->userdata('nb_user');
        $session_uid = (int) $this->session->userdata('nb_user_id');
        $is_admin = $u && isset($u['role']) && $u['role'] === 'admin' && isset($u['status']) && $u['status'] === 'approved';

        // If not already JSON, force it for /api/ routes UNLESS it's an admin in the browser
        if (!$respond_json && stripos((string) $this->input->server('REQUEST_URI'), '/api/') !== false) {
            $is_browser_admin = $is_admin && !$this->input->is_ajax_request();
            $has_admin_flag = !empty($input['admin_save']) || !empty($input['nb_admin_save']);
            
            if (!$is_browser_admin && !$has_admin_flag) {
                $respond_json = true;
            }
        }

        $owner_id = null;
        $post_owner = (int) ($input['owner_id'] ?? 0);
        if ($post_owner <= 0) {
            $post_owner = (int) ($input['userId'] ?? 0);
        }
        if ($post_owner <= 0) {
            $post_owner = (int) ($input['user_id'] ?? 0);
        }

        if ($session_uid > 0) {
            $owner_id = $session_uid;
        } elseif ($post_owner > 0) {
            if (!$this->_valid_owner_for_property($post_owner)) {
                return $this->_json(array(
                    'success' => false,
                    'message' => 'Choose a valid approved owner account',
                ), 400);
            }
            $owner_id = $post_owner;
        } else {
            $fallback_owner = $this->_default_anonymous_owner_id();
            if ($fallback_owner > 0 && $this->_valid_owner_for_property($fallback_owner)) {
                $owner_id = $fallback_owner;
            } else {
                return $this->_json(array(
                    'success' => false,
                    'message' => 'Login required or owner_id is required',
                ), 401);
            }
        }

        if (!$is_admin) {
            if ($session_uid > 0 && (!$u || $u['status'] !== 'approved')) {
                return $this->_json(array(
                    'success' => false,
                    'message' => 'Your account must be approved to list properties.',
                ), 400);
            }
        }


        $id = (int) ($input['property_id'] ?? 0);
        $existing = null;
        if ($id > 0) {
            $existing = $this->Nb_property_model->get_by_id($id);
            if (!$existing) {
                return $this->_json(array('success' => false, 'message' => 'Property not found'), 404);
            }
            if (!$is_admin && (int) $existing->owner_id !== $owner_id) {
                return $this->_json(array(
                    'success' => false,
                    'message' => 'You can only edit your own listings.',
                ), 400);
            }
        }

        // We still need to populate $_POST for form_validation->run() to work correctly
        // OR we can pass the data to validation manually. 
        // For simplicity, let's merge into $_POST if it's JSON.
        if (stripos((string) $this->input->server('CONTENT_TYPE'), 'application/json') !== false) {
            $_POST = array_merge($_POST, $input);
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('title', 'Title', 'required|trim|max_length[300]');
        $pt_list = implode(',', array_keys(nb_property_types_map()));
        $this->form_validation->set_rules('property_type', 'Type', 'required|in_list[' . $pt_list . ']');
        $this->form_validation->set_rules('listing_type', 'Listing', 'required|in_list[rent,sale]');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');
        $this->form_validation->set_rules('address', 'Address', 'required|trim');
        $this->form_validation->set_rules('locality', 'Locality', 'trim|max_length[200]');
        $this->form_validation->set_rules('city_id', 'City', 'required|integer');
        $this->form_validation->set_rules('description', 'Description', 'trim');

        if (!$this->form_validation->run()) {
            $msg = validation_errors(' ', " \n");
            if ($respond_json) {
                return $this->_json(array('success' => false, 'message' => $msg));
            }
            $this->session->set_flashdata('nb_err', trim($msg));
            // Redirect back to either edit or add form
            if ($id > 0) {
                redirect($is_admin ? ('panel/property/edit/' . $id) : ('owner/listing/edit/' . $id));
            } else {
                redirect($is_admin ? 'panel/property/add' : 'owner/listing/add');
            }
        }

        $amenities = isset($input['amenities']) ? $input['amenities'] : array();
        if (!is_array($amenities)) {
            $amenities = array();
        }
        $amenities = array_map(array($this->security, 'xss_clean'), $amenities);
        if ($this->db->table_exists('nb_amenities')) {
            $allowed = $this->Nb_amenity_model->allowed_names_map();
            $amenities = array_values(array_filter($amenities, function ($n) use ($allowed) {
                $n = trim((string) $n);
                return $n !== '' && isset($allowed[$n]);
            }));
        } else {
            $amenities = array_values(array_filter(array_map('trim', $amenities)));
        }

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
            $row['video_url'] = $this->security->xss_clean($input['video_url'] ?? $input['video'] ?? '');
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

        if ($is_admin) {
            $post_owner_val = (int) ($input['owner_id'] ?? 0);
            if ($id > 0) {
                if ($post_owner_val > 0) {
                    $same_as_existing = ((int) $post_owner_val === (int) $existing->owner_id);
                    if (!$same_as_existing && !$this->_valid_owner_for_property($post_owner_val)) {
                        return $this->_json(array('success' => false, 'message' => 'Invalid owner account'), 400);
                    }
                    $row['owner_id'] = $post_owner_val;
                } else {
                    $row['owner_id'] = (int) $existing->owner_id;
                }
            } else {
                if ($post_owner_val < 1 || !$this->_valid_owner_for_property($post_owner_val)) {
                    return $this->_json(array('success' => false, 'message' => 'Choose a valid approved owner account'), 400);
                }
                $row['owner_id'] = $post_owner_val;
            }
            if ($this->db->field_exists('is_active', 'nb_properties')) {
                $row['is_active'] = !empty($input['is_active']) ? 1 : 0;
            }
            if ($this->db->field_exists('is_featured', 'nb_properties')) {
                $row['is_featured'] = !empty($input['is_featured']) ? 1 : 0;
            }
            if ($this->db->field_exists('is_latest', 'nb_properties')) {
                $row['is_latest'] = !empty($input['is_latest']) ? 1 : 0;
            }
            if ($this->db->field_exists('tags_best_rate_localities', 'nb_properties')) {
                $row['tags_best_rate_localities'] = !empty($input['tags_best_rate_localities']) ? 1 : 0;
            }
            if ($this->db->field_exists('tags_high_growth_localities', 'nb_properties')) {
                $row['tags_high_growth_localities'] = !empty($input['tags_high_growth_localities']) ? 1 : 0;
            }
        } else {
            $row['owner_id'] = $owner_id;
            if ($this->db->field_exists('is_active', 'nb_properties')) {
                if ($id < 1) {
                    $row['is_active'] = 1;
                }
            }
            if ($this->db->field_exists('is_latest', 'nb_properties')) {
                $row['is_latest'] = !empty($input['is_latest']) ? 1 : 0;
            }
            if ($this->db->field_exists('tags_best_rate_localities', 'nb_properties')) {
                $row['tags_best_rate_localities'] = !empty($input['tags_best_rate_localities']) ? 1 : 0;
            }
            if ($this->db->field_exists('tags_high_growth_localities', 'nb_properties')) {
                $row['tags_high_growth_localities'] = !empty($input['tags_high_growth_localities']) ? 1 : 0;
            }
        }

        if ($this->db->field_exists('slug', 'nb_properties')) {
            if ($id > 0) {
                $title_changed = strcmp(
                    trim((string) $existing->title),
                    trim((string) $row['title'])
                ) !== 0;
                if ($this->Nb_property_model->slug_is_empty(isset($existing->slug) ? $existing->slug : null) || $title_changed) {
                    $row['slug'] = $this->Nb_property_model->unique_slug($row['title'], $id);
                }
            } else {
                $row['slug'] = $this->Nb_property_model->unique_slug($row['title'], null);
            }
        }

        $new_paths = $this->_upload_images($id > 0 ? $id : null);
        if ($new_paths === false) {
            return $this->_json(array('success' => false, 'message' => 'Image upload failed'), 400);
        }
        if (!is_array($new_paths)) {
            $new_paths = array();
        }

        $existing_paths = $input['existing_paths'] ?? array();
        if (!is_array($existing_paths)) {
            $existing_paths = array();
        }
        $remove = $input['remove_existing'] ?? array();
        if (!is_array($remove)) {
            $remove = array();
        }
        $kept = array();
        foreach ($existing_paths as $ep) {
            $ep = trim((string) $ep);
            if ($ep === '') {
                continue;
            }
            if (in_array($ep, $remove, true)) {
                continue;
            }
            if (strpos($ep, 'assets/uploads/nb_properties/') !== 0) {
                continue;
            }
            $kept[] = $ep;
        }

        $all_images = array_merge($kept, $new_paths);
        if (count($all_images) > 10) {
            $all_images = array_slice($all_images, 0, 10);
        }
        $cover_index = (int) ($input['cover_index'] ?? 0);
        $all_images = nb_reorder_cover($all_images, $cover_index);

        if ($id > 0 && ($input['image_action'] ?? '') !== 'replace') {
            unset($row['images']);
        } else {
            $row['images'] = json_encode($all_images);
        }

        if ($this->db->field_exists('video_url', 'nb_properties')) {
            $v = nb_sanitize_video_url($input['video_url'] ?? '');
            $row['video_url'] = $v;
        }

        if ($id > 0) {
            if (!$is_admin || !isset($row['owner_id'])) {
                unset($row['owner_id']);
            }
            $this->Nb_property_model->update($id, $row);
            $new_id = $id;
        } else {
            $new_id = $this->Nb_property_model->create($row);
        }

        // $respond_json was calculated at the start of the method
            
        if ($respond_json) {
            $saved = $this->Nb_property_model->get_by_id((int) $new_id);
            $payload = array('success' => true, 'property_id' => (int) $new_id);
            if ($saved) {
                $payload['property'] = $this->_property_response_payload($saved);
                if (!empty($saved->is_active)) {
                    $payload['property_url'] = nb_property_url($saved);
                }
            }
            return $this->_json($payload);
        }
        $flash_ok = 'Property saved.';
        if ($is_admin && $id < 1) {
            $flash_ok = 'Listing created.';
        } elseif (!$is_admin && $id < 1) {
            $flash_ok = 'Listing saved and published successfully.';
        }
        $this->session->set_flashdata('nb_ok', $flash_ok);
        if (!empty($input['admin_save'])) {
            redirect('panel/properties');
            return;
        }
        redirect('owner/listings');
    }

    /** Owner role user suitable as listing owner */
    private function _valid_owner_for_property($user_id)
    {
        $user_id = (int) $user_id;
        if ($user_id < 1) {
            return false;
        }
        $row = $this->Nb_user_model->get_by_id($user_id);
        return (bool) $row;
    }

    /** @return int Default owner ID used when anonymous create is allowed */
    private function _default_anonymous_owner_id()
    {
        $default = getenv('NB_ANONYMOUS_OWNER_ID');
        if ($default === false || trim((string) $default) === '') {
            return 1;
        }
        return (int) $default;
    }

    private function _upload_images($existing_id)
    {
        if (empty($_FILES['images']['name']) || !is_array($_FILES['images']['name'])) {
            return array();
        }
        $paths = array();
        $count = count($_FILES['images']['name']);
        $count = min($count, 10);
        $upload_path = FCPATH . 'assets/uploads/nb_properties/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        $this->load->library('upload');
        for ($i = 0; $i < $count; $i++) {
            if (empty($_FILES['images']['name'][$i])) {
                continue;
            }
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
            if (!$this->upload->do_upload('userfile')) {
                continue;
            }
            $data = $this->upload->data();
            $full = $data['full_path'];
            if (function_exists('finfo_open')) {
                $f = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($f, $full);
                finfo_close($f);
                $ok = in_array($mime, array('image/jpeg', 'image/png', 'image/webp'), true);
                if (!$ok) {
                    @unlink($full);
                    continue;
                }
            }
            $paths[] = 'assets/uploads/nb_properties/' . $data['file_name'];
        }
        return $paths;
    }

    private function _json($data, $code = 200)
    {
        $this->output->set_status_header($code);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Return full saved property values for mobile clients.
     *
     * @param object $row
     * @return array
     */
    private function _property_response_payload($row)
    {
        $out = array();
        foreach ((array) $row as $k => $v) {
            if (!is_string($k) || $k === '') {
                continue;
            }
            $out[$k] = $v;
        }

        if (isset($out['location_image']) && !empty($out['location_image'])) {
            $li = $out['location_image'];
            $out['location_image_url'] = preg_match('#^https?://#i', $li) ? $li : base_url($li);
        }

        if (isset($out['images']) && is_string($out['images']) && $out['images'] !== '') {
            $decoded = json_decode($out['images'], true);
            if (is_array($decoded)) {
                $out['images'] = $decoded;
                $image_urls = array();
                foreach ($decoded as $img) {
                    if (!is_string($img) || trim($img) === '') {
                        continue;
                    }
                    $image_urls[] = preg_match('#^https?://#i', $img) ? $img : base_url($img);
                }
                $out['image_urls'] = $image_urls;
            }
        }

        if (isset($out['amenities']) && is_string($out['amenities']) && $out['amenities'] !== '') {
            $amenities = json_decode($out['amenities'], true);
            if (is_array($amenities)) {
                $out['amenities'] = $amenities;
            }
        }

        if (isset($out['nearby']) && is_string($out['nearby']) && $out['nearby'] !== '') {
            $nearby = json_decode($out['nearby'], true);
            if (is_array($nearby)) {
                $out['nearby'] = $nearby;
            }
        }

        return $out;
    }

    /** @return float|null WGS84 */
    private function _parse_latitude($v)
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (!is_numeric($v)) {
            return null;
        }
        $f = (float) $v;
        if ($f < -90.0 || $f > 90.0) {
            return null;
        }
        return round($f, 8);
    }

    /** @return float|null WGS84 */
    private function _parse_longitude($v)
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (!is_numeric($v)) {
            return null;
        }
        $f = (float) $v;
        if ($f < -180.0 || $f > 180.0) {
            return null;
        }
        return round($f, 8);
    }

    /** @return string|null */
    private function _parse_google_place_id($v)
    {
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        if (strlen($v) > 255) {
            return null;
        }
        return $v;
    }

    /** @return float|null */
    private function _parse_optional_decimal($v)
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (!is_numeric($v)) {
            return null;
        }
        return round((float) $v, 2);
    }

    /** @return string|null Y-m-d */
    private function _parse_date_field($v)
    {
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        $t = strtotime($v);
        if ($t === false) {
            return null;
        }
        return date('Y-m-d', $t);
    }

    /** @return int|null 0/1 */
    private function _parse_boundary_wall($v)
    {
        if ($v === null || $v === '') {
            return null;
        }
        if ($v === '0' || $v === '1') {
            return (int) $v;
        }
        return null;
    }

    private function _nearby_json_from_input($input)
    {
        $nearby = $input['nearby'] ?? null;
        if (is_array($nearby) && !empty($nearby) && isset($nearby[0]) && is_array($nearby[0])) {
            // Already an array of objects from mobile/JSON
            $clean = array();
            foreach ($nearby as $item) {
                $cat = trim((string) ($item['category'] ?? ''));
                $name = trim((string) ($item['name'] ?? $item['title'] ?? ''));
                $dist = trim((string) ($item['distance'] ?? ''));
                if ($cat !== '' || $name !== '' || $dist !== '') {
                    $clean[] = array('category' => $cat, 'title' => $cat, 'name' => $name, 'distance' => $dist);
                }
            }
            return json_encode($clean);
        }

        $categories = $input['nearby_category'] ?? array();
        $titles = $input['nearby_title'] ?? array();
        $distances = $input['nearby_distance'] ?? array();
        if (!is_array($categories)) $categories = array();
        if (!is_array($titles)) $titles = array();
        if (!is_array($distances)) $distances = array();

        $rows = array();
        foreach ($categories as $i => $category) {
            $cat = trim((string) $category);
            $name = isset($titles[$i]) ? trim((string) $titles[$i]) : '';
            $distance = isset($distances[$i]) ? trim((string) $distances[$i]) : '';
            if ($cat === '' && $name === '' && $distance === '') continue;
            $rows[] = array('category' => $cat, 'title' => $cat, 'name' => $name, 'distance' => $distance);
        }
        return json_encode($rows);
    }
}
