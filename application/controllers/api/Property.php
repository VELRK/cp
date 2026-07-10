<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * POST api/property/save — add/edit property (any approved non-admin user; admin may set owner_id;
 * valid owner_id may also be supplied without an NB session).
 */
class Property extends CI_Controller
{

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

        // Multipart uploads — use $_POST only; do not read php://input (breaks $_FILES)
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

        $input = $this->_input_json_or_post();
        $content_type = (string) $this->input->server('CONTENT_TYPE');
        $is_multipart_form = stripos($content_type, 'multipart/form-data') !== false;
        $admin_save_request = $this->_is_panel_property_save_request($input);
        $owner_save_request = $this->_is_owner_panel_save_request($input);

        $respond_json = $this->input->is_ajax_request()
            || stripos((string) $this->input->server('HTTP_ACCEPT'), 'application/json') !== false;
        if ($admin_save_request || $owner_save_request) {
            // Browser panel forms redirect with flash; mobile/Next.js use JSON.
            $respond_json = false;
        } elseif ($this->nb_api_token->read_token_from_request() !== '') {
            $respond_json = true;
        }

        $is_admin = $this->_resolve_is_admin_user();
        if (!$is_admin && (int) $this->session->userdata('nb_admin_property_save_verified') === 1) {
            $this->session->unset_userdata('nb_admin_property_save_verified');
            $is_admin = $this->_resolve_is_admin_user();
        }
        if (!$is_admin && $admin_save_request) {
            $is_admin = $this->_verify_admin_property_token($input);
            if ($is_admin) {
                $this->session->set_userdata('nb_admin_property_save_verified', 1);
            }
        }
        $session_uid = (int) $this->session->userdata('nb_user_id');
        if ($admin_save_request && !$is_admin) {
            if ($respond_json) {
                return $this->_json(array(
                    'success' => false,
                    'message' => 'Admin login required to save listing flags.',
                ), 403);
            }
            $this->session->set_flashdata('nb_err', 'Admin login required. Open the panel from Admin Panel link after signing in.');
            $fail_id = (int) ($input['property_id'] ?? 0);
            nb_redirect_path($fail_id > 0 ? ('panel/property/edit/' . $fail_id) : 'panel/property/add');
            return;
        }

        // api/property/save: JSON for mobile/Next.js; browser multipart without Accept JSON may still need JSON when X-Api-Token is sent.
        if (!$respond_json && stripos((string) $this->input->server('REQUEST_URI'), '/api/') !== false) {
            $is_browser_admin = $is_admin && !$this->input->is_ajax_request();
            $has_admin_flag = !empty($input['admin_save']) || !empty($input['nb_admin_save']);
            $wants_json_client = $this->input->is_ajax_request()
                || stripos((string) $this->input->server('HTTP_ACCEPT'), 'application/json') !== false
                || $this->nb_api_token->read_token_from_request() !== '';

            if ($wants_json_client && !$is_browser_admin && !$has_admin_flag && !$owner_save_request) {
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
            $owner_id = $post_owner;
        } else {
            $fallback_owner = $this->_default_anonymous_owner_id();
            $owner_id = $fallback_owner > 0 ? $fallback_owner : 0;
        }




        $id = (int) ($input['property_id'] ?? 0);
        $existing = null;
        if ($id > 0) {
            $existing = $this->Nb_property_model->get_by_id($id);
            if (!$existing) {
                if ($respond_json) {
                    return $this->_json(array('success' => false, 'message' => 'Property not found'), 404);
                }
                $this->session->set_flashdata('nb_err', 'Property not found.');
                nb_redirect_path($this->_owner_form_redirect_path($id, $is_admin, $owner_save_request));
                return;
            }
            if (!$is_admin && (int) $existing->owner_id !== $owner_id) {
                if ($respond_json) {
                    return $this->_json(array(
                        'success' => false,
                        'message' => 'You can only edit your own listings.',
                    ), 400);
                }
                $this->session->set_flashdata('nb_err', 'You can only edit your own listings.');
                nb_redirect_path($this->_owner_form_redirect_path($id, $is_admin, $owner_save_request));
                return;
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
                nb_redirect_path($this->_owner_form_redirect_path($id, $is_admin, $owner_save_request));
            } else {
                nb_redirect_path($is_admin ? 'panel/property/add' : 'owner/property/add');
            }
            return;
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
            'title' => $this->security->xss_clean($input['title'] ?? ''),
            'description' => $input['description'] ?? '',
            'property_type' => $input['property_type'] ?? '',
            'listing_type' => $input['listing_type'] ?? '',
            'price' => (float) ($input['price'] ?? 0),
            'bedrooms' => isset($input['bedrooms']) && $input['bedrooms'] !== '' ? (int) $input['bedrooms'] : null,
            'bathrooms' => isset($input['bathrooms']) && $input['bathrooms'] !== '' ? (int) $input['bathrooms'] : null,
            'area_sqft' => isset($input['area_sqft']) && $input['area_sqft'] !== '' ? (int) $input['area_sqft'] : null,
            'address' => $input['address'] ?? '',
            'locality' => $this->security->xss_clean($input['locality'] ?? ''),
            'city_id' => (int) ($input['city_id'] ?? 0),
            'location' => $this->security->xss_clean($input['location'] ?? ''),
            'is_price_negotiable' => !empty($input['is_price_negotiable']) ? 1 : 0,
            'rate_per_sqft' => $this->_parse_optional_decimal($input['rate_per_sqft'] ?? null),
            'available_from' => $this->_parse_date_field($input['available_from'] ?? null),
            'plot_length_ft' => $this->_parse_optional_decimal($input['plot_length_ft'] ?? null),
            'plot_width_ft' => $this->_parse_optional_decimal($input['plot_width_ft'] ?? null),
            'has_boundary_wall' => $this->_parse_boundary_wall($input['has_boundary_wall'] ?? null),
            'amenities' => json_encode($amenities),
        );
        if ($this->db->field_exists('nearby', 'nb_properties')) {
            $row['nearby'] = $this->_nearby_json_from_input($input);
        }
        if ($this->db->field_exists('video_url', 'nb_properties')) {
            $row['video_url'] = $this->security->xss_clean($input['video_url'] ?? $input['video'] ?? '');
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

        $upload_errors = array();

        if (!empty($_FILES['location_image']['name'])) {
            $upload_path = FCPATH . 'assets/uploads/nb_properties/';
            $dir_err = $this->_ensure_upload_dir($upload_path);
            if ($dir_err !== null) {
                $upload_errors[] = 'Location image: ' . $dir_err;
            } elseif (!empty($_FILES['location_image']['error']) && $_FILES['location_image']['error'] !== UPLOAD_ERR_OK) {
                $upload_errors[] = 'Location image: ' . $this->_php_upload_error_message($_FILES['location_image']['error']);
            } else {
                $config = array(
                    'upload_path' => $upload_path,
                    'allowed_types' => 'jpg|jpeg|png|webp',
                    'max_size' => 5120,
                    'encrypt_name' => true,
                );
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('location_image')) {
                    $upload_errors[] = 'Location image: ' . strip_tags($this->upload->display_errors('', ''));
                } else {
                    $upd_data = $this->upload->data();
                    $row['location_image'] = 'assets/uploads/nb_properties/' . $upd_data['file_name'];
                    if ($existing && !empty($existing->location_image) && file_exists(FCPATH . $existing->location_image)) {
                        @unlink(FCPATH . $existing->location_image);
                    }
                }
            }
        }

        $upload_errors = array_merge($upload_errors, $this->_apply_listing_media_uploads($row, $existing));

        if ($is_admin) {
            $post_owner_val = (int) ($input['owner_id'] ?? 0);
            if ($id > 0) {
                if ($post_owner_val > 0) {
                    $row['owner_id'] = $post_owner_val;
                } else {
                    $row['owner_id'] = (int) $existing->owner_id;
                }
            } else {
                $row['owner_id'] = $post_owner_val > 0 ? $post_owner_val : $owner_id;
            }
            $this->_apply_admin_listing_flags($row, $input);
            $hb_err = $this->_apply_home_banner($row, $input, $existing);
            if ($hb_err !== null) {
                if ($respond_json) {
                    return $this->_json(array('success' => false, 'message' => $hb_err), 400);
                }
                $this->session->set_flashdata('nb_err', $hb_err);
                nb_redirect_path($id > 0 ? ('panel/property/edit/' . $id) : 'panel/property/add');
                return;
            }
        } else {
            $row['owner_id'] = $owner_id;
            // Owner/agent listings require admin approval before public visibility.
            if ($this->db->field_exists('is_active', 'nb_properties')) {
                $row['is_active'] = 0;
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

        $image_result = $this->_upload_images($id > 0 ? $id : null);
        $new_paths = $image_result['paths'];
        $upload_errors = array_merge($upload_errors, $image_result['errors']);

        if (!empty($upload_errors)) {
            $this->_rollback_new_uploads($row, $existing, $new_paths);
            if ($respond_json) {
                return $this->_json(array(
                    'success' => false,
                    'message' => 'Upload failed. Please check the errors below.',
                    'upload_errors' => $upload_errors,
                ), 400);
            }
            $this->session->set_flashdata('nb_err', implode("\n", $upload_errors));
            if ($id > 0) {
                nb_redirect_path($this->_owner_form_redirect_path($id, $is_admin, $owner_save_request));
            } else {
                nb_redirect_path($is_admin ? 'panel/property/add' : 'owner/property/add');
            }
            return;
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
                $this->_unlink_uploaded_file($ep);
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
                } elseif (!$is_admin) {
                    $payload['pending_review'] = true;
                    $payload['message'] = $id > 0
                        ? 'Changes saved. Listing is pending admin approval before it appears on the site.'
                        : 'Listing submitted for admin verification. It will be published after approval.';
                }
            }
            return $this->_json($payload);
        }
        $flash_ok = 'Property saved.';
        if ($is_admin && $id < 1) {
            $flash_ok = 'Listing created.';
        } elseif (!$is_admin) {
            $flash_ok = $id > 0
                ? 'Changes saved. Admin must approve before the listing appears on the site.'
                : 'Listing submitted for admin verification. It will appear on the site after approval.';
        }
        $this->session->set_flashdata('nb_ok', $flash_ok);
        if ($admin_save_request) {
            if (!$is_admin) {
                $this->session->set_flashdata('nb_err', 'Admin login required. Open the panel from Admin after signing in.');
                nb_redirect_path($id > 0 ? ('panel/property/edit/' . (int) $new_id) : 'panel/property/add');
                return;
            }
            nb_redirect_path('panel/properties');
            return;
        }
        if ($owner_save_request) {
            nb_redirect_path('owner/listings');
            return;
        }
        nb_redirect_path('owner/listings');
    }

    /** Redirect target after owner panel validation/upload errors or non-JSON saves. */
    private function _owner_form_redirect_path($property_id, $is_admin, $owner_save_request)
    {
        $property_id = (int) $property_id;
        if ($is_admin) {
            return $property_id > 0 ? ('panel/property/edit/' . $property_id) : 'panel/property/add';
        }
        if ($owner_save_request) {
            return $property_id > 0 ? ('owner/property/edit/' . $property_id) : 'owner/property/add';
        }
        return $property_id > 0 ? ('owner/property/edit/' . $property_id) : 'owner/property/add';
    }

    /** Owner PHP panel browser form POST (redirect + flash, not JSON). */
    private function _is_owner_panel_save_request($input)
    {
        if (!empty($input['owner_panel_save']) || !empty($input['nb_owner_panel_save'])) {
            return true;
        }
        $uri = (string) $this->uri->uri_string();
        if ($uri === 'owner/property/save' || strpos($uri, 'owner/property/save') === 0) {
            return true;
        }
        $requestUri = (string) $this->input->server('REQUEST_URI');
        return stripos($requestUri, '/owner/property/save') !== false;
    }

    /** Panel admin form POST (not owner/mobile JSON save). */
    private function _is_panel_property_save_request($input)
    {
        if (!empty($input['admin_save']) || !empty($input['nb_admin_save'])) {
            return true;
        }
        $uri = (string) $this->uri->uri_string();
        if ($uri === 'panel/property/save' || strpos($uri, 'panel/property/save') === 0) {
            return true;
        }
        $requestUri = (string) $this->input->server('REQUEST_URI');
        return stripos($requestUri, '/panel/property/save') !== false;
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
        $result = array('paths' => array(), 'errors' => array());
        if (empty($_FILES['images']['name']) || !is_array($_FILES['images']['name'])) {
            return $result;
        }
        $count = count($_FILES['images']['name']);
        $count = min($count, 10);
        $upload_path = FCPATH . 'assets/uploads/nb_properties/';
        $dir_err = $this->_ensure_upload_dir($upload_path);
        if ($dir_err !== null) {
            $result['errors'][] = 'Property images: ' . $dir_err;
            return $result;
        }
        $this->load->library('upload');
        for ($i = 0; $i < $count; $i++) {
            if (empty($_FILES['images']['name'][$i])) {
                continue;
            }
            $label = (string) $_FILES['images']['name'][$i];
            if (!empty($_FILES['images']['error'][$i]) && $_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                $result['errors'][] = $label . ': ' . $this->_php_upload_error_message($_FILES['images']['error'][$i]);
                continue;
            }
            $_FILES['userfile'] = array(
                'name' => $_FILES['images']['name'][$i],
                'type' => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error' => $_FILES['images']['error'][$i],
                'size' => $_FILES['images']['size'][$i],
            );
            $config = array(
                'upload_path' => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size' => 5120,
                'encrypt_name' => true,
            );
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('userfile')) {
                $result['errors'][] = $label . ': ' . strip_tags($this->upload->display_errors('', ''));
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
                    $result['errors'][] = $label . ': invalid image type';
                    continue;
                }
            }
            $result['paths'][] = 'assets/uploads/nb_properties/' . $data['file_name'];
        }
        return $result;
    }

    /** Upload brochure / audio notes and optionally remove existing files. @return string[] */
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
                'error' => $label . ': ' . $this->_php_upload_error_message($_FILES[$field]['error']),
            );
        }
        $upload_path = FCPATH . 'assets/uploads/nb_properties/' . trim($subdir, '/') . '/';
        $dir_err = $this->_ensure_upload_dir($upload_path);
        if ($dir_err !== null) {
            return array('path' => null, 'error' => $label . ': ' . $dir_err);
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

    private function _ensure_upload_dir($path)
    {
        if (is_dir($path)) {
            return null;
        }
        if (@mkdir($path, 0755, true) || is_dir($path)) {
            return null;
        }
        return 'Could not create upload directory.';
    }

    private function _php_upload_error_message($code)
    {
        switch ((int) $code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds maximum allowed size.';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded.';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Server missing temporary folder.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk.';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload blocked by server extension.';
            default:
                return 'Upload failed.';
        }
    }

    private function _rollback_new_uploads(array $row, $existing, array $new_paths)
    {
        foreach ($new_paths as $p) {
            $this->_unlink_uploaded_file($p);
        }
        if (!empty($row['location_image']) && (!$existing || ($existing->location_image ?? '') !== $row['location_image'])) {
            $this->_unlink_uploaded_file($row['location_image']);
        }
        if (!empty($row['brochure_url']) && (!$existing || ($existing->brochure_url ?? '') !== $row['brochure_url'])) {
            $this->_unlink_uploaded_file($row['brochure_url']);
        }
        if (!empty($row['audio_notes_url']) && (!$existing || ($existing->audio_notes_url ?? '') !== $row['audio_notes_url'])) {
            $this->_unlink_uploaded_file($row['audio_notes_url']);
        }
    }

    private function _unlink_uploaded_file($relative_path)
    {
        $relative_path = trim((string) $relative_path);
        if ($relative_path === '' || strpos($relative_path, 'assets/uploads/nb_properties/') !== 0) {
            return;
        }
        $full = FCPATH . $relative_path;
        if (is_file($full)) {
            @unlink($full);
        }
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

        foreach (array('brochure_url', 'audio_notes_url', 'home_banner_image') as $mediaCol) {
            if (isset($out[$mediaCol]) && !empty($out[$mediaCol])) {
                $path = $out[$mediaCol];
                $out[$mediaCol . '_url'] = preg_match('#^https?://#i', $path) ? $path : base_url($path);
            }
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
        if (!is_array($categories))
            $categories = array();
        if (!is_array($titles))
            $titles = array();
        if (!is_array($distances))
            $distances = array();

        $rows = array();
        foreach ($categories as $i => $category) {
            $cat = trim((string) $category);
            $name = isset($titles[$i]) ? trim((string) $titles[$i]) : '';
            $distance = isset($distances[$i]) ? trim((string) $distances[$i]) : '';
            if ($cat === '' && $name === '' && $distance === '')
                continue;
            $rows[] = array('category' => $cat, 'title' => $cat, 'name' => $name, 'distance' => $distance);
        }
        return json_encode($rows);
    }

    /** Whether the current request belongs to an approved admin (session or DB fallback). */
    private function _resolve_is_admin_user()
    {
        $u = $this->session->userdata('nb_user');
        if ($u && isset($u['role']) && $u['role'] === 'admin' && isset($u['status']) && $u['status'] === 'approved') {
            return true;
        }

        $session_uid = (int) $this->session->userdata('nb_user_id');
        if ($session_uid < 1) {
            return false;
        }

        $admin_row = $this->Nb_user_model->get_by_id($session_uid);
        if (!$admin_row || $admin_row->role !== 'admin' || $admin_row->status !== 'approved') {
            return false;
        }

        $this->session->set_userdata('nb_user_id', (int) $admin_row->id);
        $this->session->set_userdata('nb_user', array(
            'id'     => (int) $admin_row->id,
            'name'   => $admin_row->name,
            'email'  => $admin_row->email,
            'phone'  => isset($admin_row->phone) ? (string) $admin_row->phone : '',
            'role'   => $admin_row->role,
            'status' => $admin_row->status,
        ));

        return true;
    }

    /** Write all admin-only listing boolean flags from POST into $row. */
    private function _apply_admin_listing_flags(array &$row, array $input)
    {
        if ($this->db->field_exists('is_active', 'nb_properties')) {
            $row['is_active'] = $this->_post_flag($input, 'is_active');
        }
        if ($this->db->field_exists('is_featured', 'nb_properties')) {
            $row['is_featured'] = $this->_post_flag($input, 'is_featured');
        }
        if ($this->db->field_exists('is_recommended', 'nb_properties')) {
            $row['is_recommended'] = $this->_post_flag($input, 'is_recommended');
        }
        if ($this->db->field_exists('is_latest', 'nb_properties')) {
            $row['is_latest'] = $this->_post_flag($input, 'is_latest');
        }
        if ($this->db->field_exists('tags_best_rate_localities', 'nb_properties')) {
            $row['tags_best_rate_localities'] = $this->_post_flag($input, 'tags_best_rate_localities')
                || $this->_post_flag($input, 'best_rate');
        }
        if ($this->db->field_exists('tags_high_growth_localities', 'nb_properties')) {
            $row['tags_high_growth_localities'] = $this->_post_flag($input, 'tags_high_growth_localities');
        }
        if ($this->db->field_exists('is_newly_launched', 'nb_properties')) {
            $row['is_newly_launched'] = $this->_post_flag($input, 'is_newly_launched');
        }
        if ($this->db->field_exists('is_verified_property', 'nb_properties')) {
            $row['is_verified_property'] = $this->_post_flag($input, 'is_verified_property');
        }
        if ($this->db->field_exists('is_premium', 'nb_properties')) {
            $row['is_premium'] = $this->_post_flag($input, 'is_premium');
        }
        if ($this->db->field_exists('is_home_banner', 'nb_properties')) {
            $row['is_home_banner'] = $this->_post_flag($input, 'is_home_banner');
        }
    }

    /**
     * Admin home banner flag + image upload with strict validation.
     *
     * @return string|null Error message or null on success
     */
    private function _apply_home_banner(array &$row, array $input, $existing)
    {
        if (!$this->db->field_exists('is_home_banner', 'nb_properties')) {
            return null;
        }

        $enabled = $this->_post_flag($input, 'is_home_banner');
        $row['is_home_banner'] = $enabled;

        $removed = !empty($this->input->post('remove_home_banner_image'));
        if ($removed && $existing && !empty($existing->home_banner_image)) {
            $this->_unlink_uploaded_file($existing->home_banner_image);
            $row['home_banner_image'] = null;
        }

        $upload_err = null;
        $new_path = $this->_upload_home_banner_image($upload_err);
        if ($upload_err !== null) {
            return $upload_err;
        }
        if ($new_path !== null) {
            if ($existing && !empty($existing->home_banner_image)) {
                $this->_unlink_uploaded_file($existing->home_banner_image);
            }
            $row['home_banner_image'] = $new_path;
        }

        if (!$enabled) {
            return null;
        }

        $has_image = !empty($row['home_banner_image']);
        if (!$has_image && $existing && !empty($existing->home_banner_image) && !$removed) {
            $has_image = true;
        }
        if (!$has_image) {
            return 'Home banner image is required when Home Banner is enabled. Upload JPEG, PNG or WebP (min 800×300 px, max 2 MB).';
        }

        return null;
    }

    /**
     * Upload home banner hero image with strict MIME and dimension checks.
     *
     * @param string|null $error_msg Set on failure
     * @return string|null Relative path or null if no file uploaded
     */
    private function _upload_home_banner_image(&$error_msg)
    {
        $error_msg = null;
        if (empty($_FILES['home_banner_image']['name'])) {
            return null;
        }

        $upload_path = FCPATH . 'assets/uploads/nb_properties/home_banners/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config = array(
            'upload_path' => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size' => 2048,
            'encrypt_name' => true,
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('home_banner_image')) {
            $error_msg = trim(strip_tags($this->upload->display_errors('', '')));
            if ($error_msg === '') {
                $error_msg = 'Home banner image upload failed.';
            }
            return null;
        }

        $data = $this->upload->data();
        $full = $data['full_path'];

        if (function_exists('finfo_open')) {
            $f = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($f, $full);
            finfo_close($f);
            if (!in_array($mime, array('image/jpeg', 'image/png', 'image/webp'), true)) {
                @unlink($full);
                $error_msg = 'Home banner must be JPEG, PNG or WebP.';
                return null;
            }
        }

        $size = @getimagesize($full);
        if (!is_array($size) || empty($size[0]) || empty($size[1])) {
            @unlink($full);
            $error_msg = 'Home banner image is not a valid image file.';
            return null;
        }
        $w = (int) $size[0];
        $h = (int) $size[1];
        if ($w < 800 || $h < 300) {
            @unlink($full);
            $error_msg = 'Home banner image must be at least 800×300 pixels.';
            return null;
        }
        if ($w > 4000 || $h > 2000) {
            @unlink($full);
            $error_msg = 'Home banner image must not exceed 4000×2000 pixels.';
            return null;
        }

        return 'assets/uploads/nb_properties/home_banners/' . $data['file_name'];
    }

    /** Parse checkbox / switch from multipart POST (reads raw $_POST first). */
    private function _post_flag($input, $key)
    {
        $v = null;
        if (isset($_POST[$key])) {
            $v = $_POST[$key];
        } elseif (is_array($input) && array_key_exists($key, $input)) {
            $v = $input[$key];
        } else {
            return 0;
        }

        if (is_array($v)) {
            foreach ($v as $item) {
                if ((string) $item === '1') {
                    return 1;
                }
            }
            return 0;
        }

        $s = strtolower(trim((string) $v));
        return ($s === '1' || $s === 'on' || $s === 'true' || $s === 'yes') ? 1 : 0;
    }

    /** Validate admin property form token + approved admin session. */
    private function _verify_admin_property_token($input)
    {
        if ($this->_resolve_is_admin_user()) {
            return true;
        }

        $posted = trim((string) ($input['admin_property_token'] ?? ''));
        $expected = trim((string) $this->session->userdata('nb_admin_property_token'));
        if ($posted === '' || $expected === '' || !hash_equals($expected, $posted)) {
            return false;
        }

        return $this->_resolve_is_admin_user();
    }
}
