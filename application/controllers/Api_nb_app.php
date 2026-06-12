<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dream Villa Makers mobile JSON API (nb_* tables).
 *
 * Auth: send Authorization: Bearer <token> or X-Api-Token (from login/register).
 * Property save & enquiry use the same tokens on existing endpoints (api/property/save, api/enquiry/send).
 */
class Api_nb_app extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'nb'));
        $this->load->database();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model(array('Nb_user_model', 'Nb_property_model', 'Nb_city_model', 'Nb_delete_request_model', 'User_model'));
        $this->output->set_content_type('application/json');
        $this->_cors();
    }

    private function _cors()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Api-Token, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        $rm = strtoupper((string) $this->input->server('REQUEST_METHOD'));
        if ($rm === 'OPTIONS') {
            $this->output->set_output('');
            exit;
        }
    }

    private function _json($data, $code = 200)
    {
        $this->output->set_status_header($code);
        $this->output->set_output(json_encode($data));
    }

    private function _asset_url_or_null($path)
    {
        if ($path === null) {
            return null;
        }
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        return base_url($path);
    }

    private function _user_public($user)
    {
        $apiRole = isset($user->user_type) ? trim(strtolower((string) $user->user_type)) : '';
        if ($apiRole !== 'agent' && $apiRole !== 'customer') {
            $dbRole = isset($user->role) ? trim(strtolower((string) $user->role)) : 'tenant';
            $apiRole = ($dbRole === 'owner') ? 'agent' : 'customer';
        }
        // Return full nb_users row (except password), plus API role mapping keys.
        $row = array();
        foreach ((array) $user as $k => $v) {
            if (!is_string($k) || $k === '') {
                continue;
            }
            if ($k === 'password') {
                continue;
            }
            $row[$k] = $v;
        }
        $row['api_role'] = $apiRole;
        $row['api_user_type'] = $apiRole;
        $row['is_verified'] = isset($row['is_verified']) ? (int) $row['is_verified'] : 0;
        
        if (isset($row['profile_pic'])) {
            $row['profile_pic'] = $this->_asset_url_or_null($row['profile_pic']);
        }
        if (isset($row['aadhar_file'])) {
            $row['aadhar_file'] = $this->_asset_url_or_null($row['aadhar_file']);
        }
        return $row;
    }





    private function _input_json_or_post()
    {
        $ct = (string) $this->input->server('CONTENT_TYPE');
        $raw = file_get_contents('php://input');

        // Try JSON if Content-Type says so, or if body looks like JSON
        if (stripos($ct, 'application/json') !== false || (isset($raw[0]) && $raw[0] === '{')) {
            if (trim($raw) !== '') {
                $j = json_decode($raw, true);
                if ($j === null && json_last_error() !== JSON_ERROR_NONE) {
                    // Invalid JSON — return sentinel so callers can detect it
                    return array('__json_parse_error__' => json_last_error_msg());
                }
                return is_array($j) ? $j : array();
            }
        }
        return array_merge($this->input->post(), $this->input->get());
    }

    private function _looks_like_base64_payload($value)
    {
        $v = trim((string) $value);
        if ($v === '') {
            return false;
        }
        if (stripos($v, 'data:') === 0) {
            return true;
        }
        // Heuristic for raw base64 payloads from mobile apps.
        if (strlen($v) < 128) {
            return false;
        }
        return (bool) preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $v);
    }

    /**
     * Save base64/data-uri payload to uploads and return relative path.
     *
     * @param string $payload base64 string (data URI or raw)
     * @param string $kind aadhar|profile
     * @return array{ok:bool,path:string|null,error:string|null}
     */
    private function _save_base64_upload($payload, $kind)
    {
        $payload = trim((string) $payload);
        $kind = ($kind === 'aadhar') ? 'aadhar' : 'profile';
        if ($payload === '') {
            return array('ok' => false, 'path' => null, 'error' => 'Empty payload');
        }

        $folder = ($kind === 'aadhar') ? 'kyc' : 'profiles';
        $targetDir = FCPATH . 'uploads/' . $folder . '/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }
        if (!is_dir($targetDir)) {
            return array('ok' => false, 'path' => null, 'error' => 'Could not prepare upload directory');
        }

        $rawBase64 = $payload;
        $ext = 'jpg';
        if (stripos($payload, 'data:') === 0) {
            if (preg_match('/^data:([a-zA-Z0-9\/\-\.+]+);base64,(.*)$/s', $payload, $m)) {
                $mime = strtolower(trim($m[1]));
                $rawBase64 = $m[2];
                $map = array(
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'application/pdf' => 'pdf',
                );
                if (isset($map[$mime])) {
                    $ext = $map[$mime];
                }
            } else {
                return array('ok' => false, 'path' => null, 'error' => 'Invalid data URI format');
            }
        }

        $bin = base64_decode($rawBase64, true);
        if ($bin === false) {
            return array('ok' => false, 'path' => null, 'error' => 'Invalid base64 data');
        }
        if (strlen($bin) > 5 * 1024 * 1024) {
            return array('ok' => false, 'path' => null, 'error' => 'File too large (max 5MB)');
        }
        if ($kind === 'profile' && $ext === 'pdf') {
            return array('ok' => false, 'path' => null, 'error' => 'profile image must be jpg, png, or webp');
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $fullPath = $targetDir . $fileName;
        if (@file_put_contents($fullPath, $bin) === false) {
            return array('ok' => false, 'path' => null, 'error' => 'Could not save uploaded file');
        }
        return array(
            'ok' => true,
            'path' => 'uploads/' . $folder . '/' . $fileName,
            'error' => null,
        );
    }

    /**
     * POST — upload registration image from gallery/camera.
     * multipart/form-data: file field `image`, optional `kind` = aadhar|profile.
     */
    public function upload_image()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        if (empty($_FILES['image']['name'])) {
            return $this->_json(array('success' => false, 'message' => 'image file is required'), 400);
        }

        $kind = trim(strtolower((string) $this->input->post('kind', true)));
        if (!in_array($kind, array('aadhar', 'profile'), true)) {
            $kind = 'profile';
        }

        $folder = ($kind === 'aadhar') ? 'kyc' : 'profiles';
        $targetDir = FCPATH . 'uploads/' . $folder . '/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }
        if (!is_dir($targetDir)) {
            return $this->_json(array('success' => false, 'message' => 'Could not prepare upload directory'), 500);
        }

        $this->load->library('upload');
        $config = array(
            'upload_path'   => $targetDir,
            'allowed_types' => 'jpg|jpeg|png|webp|pdf',
            'max_size'      => 5120, // 5 MB
            'file_ext_tolower' => true,
            'remove_spaces' => true,
            'encrypt_name'  => true,
        );
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('image')) {
            return $this->_json(array('success' => false, 'message' => strip_tags($this->upload->display_errors('', ''))), 400);
        }
        $u = $this->upload->data();
        $relPath = 'uploads/' . $folder . '/' . $u['file_name'];
        $this->_json(array(
            'success'   => true,
            'kind'      => $kind,
            'file_path' => $relPath,
            'file_url'  => base_url($relPath),
        ));
    }

    /** POST — registration (customer or agent). */
    public function register()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $input = $this->_input_json_or_post();
        $name = isset($input['name']) ? trim((string) $input['name']) : '';
        $email = isset($input['email']) ? trim(strtolower((string) $input['email'])) : '';
        $phone = isset($input['phone']) ? trim((string) $input['phone']) : '';
        $password = isset($input['password']) ? (string) $input['password'] : '';
        $password2 = isset($input['password_confirm']) ? (string) $input['password_confirm'] : $password;
        $accept = !empty($input['accept_terms']);
        // Accept user_type or role from app payload, but only customer/agent values.
        $roleInput = '';
        if (isset($input['user_type'])) {
            $roleInput = trim(strtolower((string) $input['user_type']));
        } elseif (isset($input['role'])) {
            $roleInput = trim(strtolower((string) $input['role']));
        }
        if ($roleInput === '') {
            $roleInput = 'customer';
        }
        if (!in_array($roleInput, array('customer', 'agent'), true)) {
            return $this->_json(array('success' => false, 'message' => 'role (or user_type) must be customer or agent'), 400);
        }
        $user_type = $roleInput;
        $role = ($user_type === 'agent') ? 'owner' : 'tenant';
        $aadhar_no = isset($input['aadhar_no']) ? preg_replace('/\D+/', '', (string) $input['aadhar_no']) : '';
        $experience_years = isset($input['experience_years']) && $input['experience_years'] !== '' ? (int) $input['experience_years'] : null;
        $aadhar_file = isset($input['aadhar_file']) ? trim((string) $input['aadhar_file']) : null;
        $profile_pic = '';
        if (isset($input['profile_pic'])) {
            $profile_pic = trim((string) $input['profile_pic']);
        } elseif (isset($input['profile_image'])) {
            $profile_pic = trim((string) $input['profile_image']);
        }
        if (!$accept) {
            return $this->_json(array('success' => false, 'message' => 'accept_terms must be true'), 400);
        }
        if (strlen($name) < 2 || strlen($email) < 3 || strlen($phone) < 10 || strlen($password) < 6) {
            return $this->_json(array('success' => false, 'message' => 'Invalid name, email, phone, or password (min 6 chars).'), 400);
        }
        if ($password !== $password2) {
            return $this->_json(array('success' => false, 'message' => 'Passwords do not match.'), 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->_json(array('success' => false, 'message' => 'Invalid email.'), 400);
        }
        // Handle direct file upload during register (multipart/form-data).
        $uploadedAadharPath = null;
        $uploadedProfilePath = null;
        if (!empty($_FILES['aadhar_file']['name'])) {
            $targetDir = FCPATH . 'uploads/kyc/';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }
            if (!is_dir($targetDir)) {
                return $this->_json(array('success' => false, 'message' => 'Could not prepare Aadhaar upload directory'), 500);
            }
            $this->load->library('upload');
            $cfgAad = array(
                'upload_path' => $targetDir,
                'allowed_types' => 'jpg|jpeg|png|webp|pdf',
                'max_size' => 5120,
                'file_ext_tolower' => true,
                'remove_spaces' => true,
                'encrypt_name' => true,
            );
            $this->upload->initialize($cfgAad);
            if (!$this->upload->do_upload('aadhar_file')) {
                return $this->_json(array('success' => false, 'message' => 'aadhar_file upload failed: ' . strip_tags($this->upload->display_errors('', ''))), 400);
            }
            $uAad = $this->upload->data();
            $uploadedAadharPath = 'uploads/kyc/' . $uAad['file_name'];
        }
        $profileInputField = '';
        if (!empty($_FILES['profile_image']['name'])) {
            $profileInputField = 'profile_image';
        } elseif (!empty($_FILES['profile_pic']['name'])) {
            $profileInputField = 'profile_pic';
        }
        if ($profileInputField !== '') {
            $targetDir = FCPATH . 'uploads/profiles/';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }
            if (!is_dir($targetDir)) {
                return $this->_json(array('success' => false, 'message' => 'Could not prepare profile upload directory'), 500);
            }
            $this->load->library('upload');
            $cfgProf = array(
                'upload_path' => $targetDir,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size' => 5120,
                'file_ext_tolower' => true,
                'remove_spaces' => true,
                'encrypt_name' => true,
            );
            $this->upload->initialize($cfgProf);
            if (!$this->upload->do_upload($profileInputField)) {
                return $this->_json(array('success' => false, 'message' => $profileInputField . ' upload failed: ' . strip_tags($this->upload->display_errors('', ''))), 400);
            }
            $uProf = $this->upload->data();
            $uploadedProfilePath = 'uploads/profiles/' . $uProf['file_name'];
            $profile_pic = $uploadedProfilePath;
        }
        if ($uploadedAadharPath !== null) {
            $aadhar_file = $uploadedAadharPath;
        }
        // JSON/base64 upload support in same register call.
        // Accepted payload keys:
        // - aadhar_file_base64 (preferred) or aadhar_file (data URI/base64)
        // - profile_image_base64 / profile_pic_base64 (preferred) or profile_image/profile_pic (data URI/base64)
        $aadharBase64 = '';
        if (isset($input['aadhar_file_base64'])) {
            $aadharBase64 = (string) $input['aadhar_file_base64'];
        } elseif ($this->_looks_like_base64_payload($aadhar_file)) {
            $aadharBase64 = (string) $aadhar_file;
        }
        if ($aadharBase64 !== '') {
            $savedAadhar = $this->_save_base64_upload($aadharBase64, 'aadhar');
            if (!$savedAadhar['ok']) {
                return $this->_json(array('success' => false, 'message' => 'aadhar_file base64 upload failed: ' . $savedAadhar['error']), 400);
            }
            $aadhar_file = $savedAadhar['path'];
        }

        $profileBase64 = '';
        if (isset($input['profile_image_base64'])) {
            $profileBase64 = (string) $input['profile_image_base64'];
        } elseif (isset($input['profile_pic_base64'])) {
            $profileBase64 = (string) $input['profile_pic_base64'];
        } elseif ($this->_looks_like_base64_payload($profile_pic)) {
            $profileBase64 = (string) $profile_pic;
        }
        if ($profileBase64 !== '') {
            $savedProfile = $this->_save_base64_upload($profileBase64, 'profile');
            if (!$savedProfile['ok']) {
                return $this->_json(array('success' => false, 'message' => 'profile image base64 upload failed: ' . $savedProfile['error']), 400);
            }
            $profile_pic = $savedProfile['path'];
        }
        // Optional agent KYC/profile fields: validate only when provided.
        if ($aadhar_no !== '' && !preg_match('/^\d{12}$/', $aadhar_no)) {
            return $this->_json(array('success' => false, 'message' => 'aadhar_no must be a valid 12-digit number when provided.'), 400);
        }
        if ($experience_years !== null && ($experience_years < 0 || $experience_years > 60)) {
            return $this->_json(array('success' => false, 'message' => 'experience_years must be between 0 and 60 when provided.'), 400);
        }
        if ($this->Nb_user_model->get_by_email($email)) {
            return $this->_json(array('success' => false, 'message' => 'Email already registered.'), 409);
        }
        $city_id = isset($input['city_id']) ? (int) $input['city_id'] : null;
        if ($city_id < 1) {
            $city_id = null;
        }
        $insert = array(
            'name'     => trim(strip_tags($name)),
            'email'    => $email,
            'phone'    => trim(preg_replace('/[^\d\+\-\s]/', '', $phone)),
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role'     => $role,
            'status'   => 'approved',
            'city_id'  => $city_id,
            'user_type' => $user_type,
        );
        if ($this->db->field_exists('aadhar_no', 'nb_users')) {
            $insert['aadhar_no'] = $aadhar_no !== '' ? $aadhar_no : null;
        }
        if ($this->db->field_exists('aadhar_file', 'nb_users')) {
            $insert['aadhar_file'] = $aadhar_file !== '' ? $aadhar_file : null;
        }
        if ($this->db->field_exists('experience_years', 'nb_users')) {
            $insert['experience_years'] = $experience_years;
        }
        if ($this->db->field_exists('profile_pic', 'nb_users')) {
            $insert['profile_pic'] = $profile_pic !== '' ? $profile_pic : null;
        }
        if ($this->db->field_exists('is_verified', 'nb_users')) {
            $insert['is_verified'] = 1;
        }
        $new_id = $this->Nb_user_model->create($insert);
        $user = $this->Nb_user_model->get_by_id($new_id);
        if (!$user) {
            return $this->_json(array('success' => false, 'message' => 'Registration failed.'), 500);
        }
        $this->_json(array(
            'success' => true,
            'user'    => $this->_user_public($user),
        ));
    }

    /** POST — login with email/phone + password. */
    public function login()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $input = $this->_input_json_or_post();
        $login = isset($input['login']) ? trim((string) $input['login']) : '';
        if ($login === '' && isset($input['email'])) {
            $login = trim((string) $input['email']);
        }
        $password = isset($input['password']) ? (string) $input['password'] : '';
        if ($login === '' || $password === '') {
            return $this->_json(array('success' => false, 'message' => 'login (email or phone) and password required.'), 400);
        }
        $user = $this->Nb_user_model->get_by_email_or_phone($login);
        if (!$user || !password_verify($password, $user->password)) {
            return $this->_json(array('success' => false, 'message' => 'Invalid credentials.'), 401);
        }
        if (!isset($user->status) || $user->status !== 'approved') {
            return $this->_json(array('success' => false, 'message' => 'Account is not active. Contact support.'), 403);
        }
        $token = bin2hex(random_bytes(32));
        if ($this->db->field_exists('api_token', 'nb_users')) {
            $this->Nb_user_model->update($user->id, array('api_token' => $token));
        }
        $this->_json(array(
            'success' => true,
            'token'   => $token,
            'user'    => $this->_user_public($user),
        ));
    }

    /** POST — invalidate token. */
    public function logout()
    {
        $this->_json(array('success' => true, 'message' => 'Logged out.'));
    }

    /** GET — current user (Bearer). */
    public function me()
    {
        $this->load->library('nb_api_token');
        $token = $this->nb_api_token->read_token_from_request();
        
        $u = null;
        if ($token !== '') {
            $u = $this->Nb_user_model->get_by_api_token($token);
        }

        // Fallback to query parameter if header token is missing or didn't yield a user
        if (!$u) {
            $uid = $this->input->get('userId') ?: $this->input->get('user_id');
            if ($uid) {
                $u = $this->Nb_user_model->get_by_id((int) $uid);
            }
        }

        if (!$u) {
            return $this->_json(array('success' => false, 'message' => 'User not found or token invalid'), 401);
        }

        $this->_json(array('success' => true, 'user' => $this->_user_public($u)));
    }

    /**
     * GET /api/mobile/profile
     * Supports either:
     * - query userId/user_id (legacy mobile usage), or
     * - Bearer token fallback when userId is absent.
     */
    public function profile()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $userId = $this->input->get('userId');
        if ($userId === null || $userId === '') {
            $userId = $this->input->get('user_id');
        }
        if ($userId !== null && $userId !== '') {
            $uid = (int) $userId;
            if ($uid < 1) {
                return $this->_json(array('success' => false, 'message' => 'Valid userId is required'), 400);
            }
            $user = $this->Nb_user_model->get_by_id($uid);
            if (!$user) {
                return $this->_json(array('success' => false, 'message' => 'User not found'), 404);
            }
            return $this->_json(array('success' => true, 'user' => $this->_user_public($user)));
        }

        return $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
    }

    /** POST — submit account deletion request. Body: userId (or user_id), reason. */
    public function delete_account()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }

        $input = $this->_input_json_or_post();
        $uid = isset($input['userId']) ? $input['userId'] : (isset($input['user_id']) ? $input['user_id'] : null);

        if (!$uid) {
            return $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
        }

        $id = (int) $uid;
        if ($id < 1) {
            return $this->_json(array('success' => false, 'message' => 'Valid userId is required'), 400);
        }

        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            return $this->_json(array('success' => false, 'message' => 'User not found'), 404);
        }

        $reason = isset($input['reason']) ? trim((string) $input['reason']) : '';
        if ($reason === '') {
            return $this->_json(array('success' => false, 'message' => 'reason is required'), 400);
        }

        if ($this->Nb_delete_request_model->already_requested($id)) {
            return $this->_json(array('success' => false, 'message' => 'A deletion request is already pending for this account'), 409);
        }

        $this->Nb_delete_request_model->create($id, $reason);

        return $this->_json(array('success' => true, 'message' => 'Your account deletion request has been submitted. Our team will process it shortly.'));
    }

    /** POST — update profile for customer or agent (same endpoint for both). */
    public function update_profile()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        $input = $this->_input_json_or_post();

        $uid = isset($input['userId']) ? $input['userId'] : (isset($input['user_id']) ? $input['user_id'] : null);
        if (!$uid) {
            return $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
        }
        $id = (int) $uid;
        if ($id < 1) {
            return $this->_json(array('success' => false, 'message' => 'Valid userId is required'), 400);
        }

        $user = $this->Nb_user_model->get_by_id($id);
        if (!$user) {
            return $this->_json(array('success' => false, 'message' => 'User not found'), 404);
        }

        $update = array();

        // --- name ---
        if (isset($input['name'])) {
            $name = trim(strip_tags((string) $input['name']));
            if (strlen($name) < 2) {
                return $this->_json(array('success' => false, 'message' => 'name must be at least 2 characters'), 400);
            }
            $update['name'] = $name;
        }

        // --- email ---
        if (isset($input['email'])) {
            $email = strtolower(trim((string) $input['email']));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->_json(array('success' => false, 'message' => 'Invalid email address'), 400);
            }
            $existing = $this->Nb_user_model->get_by_email($email);
            if ($existing && (int) $existing->id !== $id) {
                return $this->_json(array('success' => false, 'message' => 'Email already registered'), 409);
            }
            $update['email'] = $email;
        }

        // --- phone ---
        if (isset($input['phone'])) {
            $phone = trim(preg_replace('/[^\d\+\-\s]/', '', (string) $input['phone']));
            $digits = preg_replace('/\D+/', '', $phone);
            if (strlen($digits) < 10) {
                return $this->_json(array('success' => false, 'message' => 'phone must be at least 10 digits'), 400);
            }
            $existing = $this->Nb_user_model->get_by_phone($phone);
            if ($existing && (int) $existing->id !== $id) {
                return $this->_json(array('success' => false, 'message' => 'Phone number already registered'), 409);
            }
            $update['phone'] = $phone;
        }

        // --- role ---
        if (isset($input['role'])) {
            $role = trim(strtolower((string) $input['role']));
            if (!in_array($role, array('owner', 'tenant'), true)) {
                return $this->_json(array('success' => false, 'message' => 'role must be owner or tenant'), 400);
            }
            $update['role'] = $role;
        }

        // --- user_type ---
        if (isset($input['user_type'])) {
            $user_type = trim(strtolower((string) $input['user_type']));
            if (!in_array($user_type, array('agent', 'customer'), true)) {
                return $this->_json(array('success' => false, 'message' => 'user_type must be agent or customer'), 400);
            }
            $update['user_type'] = $user_type;
        }

        // --- city_id ---
        if (isset($input['city_id'])) {
            $city_id = (int) $input['city_id'];
            $update['city_id'] = $city_id > 0 ? $city_id : null;
        }

        // --- fcm_token ---
        if (isset($input['fcm_token'])) {
            $fcm = trim((string) $input['fcm_token']);
            if ($this->db->field_exists('fcm_token', 'nb_users')) {
                $update['fcm_token'] = $fcm !== '' ? $fcm : null;
            }
        }

        // --- profile_pic (multipart file upload takes priority over base64/path) ---
        $profileInputField = '';
        if (!empty($_FILES['profile_image']['name'])) {
            $profileInputField = 'profile_image';
        } elseif (!empty($_FILES['profile_pic']['name'])) {
            $profileInputField = 'profile_pic';
        }
        if ($profileInputField !== '') {
            $targetDir = FCPATH . 'uploads/profiles/';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }
            if (!is_dir($targetDir)) {
                return $this->_json(array('success' => false, 'message' => 'Could not prepare profile upload directory'), 500);
            }
            $this->load->library('upload');
            $this->upload->initialize(array(
                'upload_path'      => $targetDir,
                'allowed_types'    => 'jpg|jpeg|png|webp',
                'max_size'         => 5120,
                'file_ext_tolower' => true,
                'remove_spaces'    => true,
                'encrypt_name'     => true,
            ));
            if (!$this->upload->do_upload($profileInputField)) {
                return $this->_json(array('success' => false, 'message' => $profileInputField . ' upload failed: ' . strip_tags($this->upload->display_errors('', ''))), 400);
            }
            $uProf = $this->upload->data();
            $update['profile_pic'] = 'uploads/profiles/' . $uProf['file_name'];
        } else {
            $picRaw = '';
            if (isset($input['profile_pic']) && $this->_looks_like_base64_payload($input['profile_pic'])) {
                $picRaw = (string) $input['profile_pic'];
            } elseif (isset($input['profile_image_base64'])) {
                $picRaw = (string) $input['profile_image_base64'];
            } elseif (isset($input['profile_pic_base64'])) {
                $picRaw = (string) $input['profile_pic_base64'];
            } elseif (isset($input['profile_image']) && $this->_looks_like_base64_payload($input['profile_image'])) {
                $picRaw = (string) $input['profile_image'];
            }
            if ($picRaw !== '') {
                $saved = $this->_save_base64_upload($picRaw, 'profile');
                if (!$saved['ok']) {
                    return $this->_json(array('success' => false, 'message' => 'profile_pic upload failed: ' . $saved['error']), 400);
                }
                $update['profile_pic'] = $saved['path'];
            } elseif (isset($input['profile_pic']) && !$this->_looks_like_base64_payload($input['profile_pic'])) {
                $picPath = trim((string) $input['profile_pic']);
                if ($picPath !== '') {
                    $update['profile_pic'] = $picPath;
                }
            }
        }

        // --- agent-only fields (accepted for any user_type; validated when present) ---
        if (isset($input['aadhar_no'])) {
            $aadhar_no = preg_replace('/\D+/', '', (string) $input['aadhar_no']);
            if ($aadhar_no !== '' && !preg_match('/^\d{12}$/', $aadhar_no)) {
                return $this->_json(array('success' => false, 'message' => 'aadhar_no must be a valid 12-digit number'), 400);
            }
            if ($this->db->field_exists('aadhar_no', 'nb_users')) {
                $update['aadhar_no'] = $aadhar_no !== '' ? $aadhar_no : null;
            }
        }

        if (isset($input['experience_years'])) {
            $exp = $input['experience_years'] !== '' ? (int) $input['experience_years'] : null;
            if ($exp !== null && ($exp < 0 || $exp > 60)) {
                return $this->_json(array('success' => false, 'message' => 'experience_years must be between 0 and 60'), 400);
            }
            if ($this->db->field_exists('experience_years', 'nb_users')) {
                $update['experience_years'] = $exp;
            }
        }

        // --- aadhar_file upload ---
        if (!empty($_FILES['aadhar_file']['name'])) {
            $targetDir = FCPATH . 'uploads/kyc/';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }
            if (!is_dir($targetDir)) {
                return $this->_json(array('success' => false, 'message' => 'Could not prepare KYC upload directory'), 500);
            }
            $this->load->library('upload');
            $this->upload->initialize(array(
                'upload_path'      => $targetDir,
                'allowed_types'    => 'jpg|jpeg|png|webp|pdf',
                'max_size'         => 5120,
                'file_ext_tolower' => true,
                'remove_spaces'    => true,
                'encrypt_name'     => true,
            ));
            if (!$this->upload->do_upload('aadhar_file')) {
                return $this->_json(array('success' => false, 'message' => 'aadhar_file upload failed: ' . strip_tags($this->upload->display_errors('', ''))), 400);
            }
            $uAad = $this->upload->data();
            if ($this->db->field_exists('aadhar_file', 'nb_users')) {
                $update['aadhar_file'] = 'uploads/kyc/' . $uAad['file_name'];
            }
        } else {
            $aadharBase64 = '';
            if (isset($input['aadhar_file']) && $this->_looks_like_base64_payload($input['aadhar_file'])) {
                $aadharBase64 = (string) $input['aadhar_file'];
            } elseif (isset($input['aadhar_file_base64'])) {
                $aadharBase64 = (string) $input['aadhar_file_base64'];
            }
            if ($aadharBase64 !== '') {
                $saved = $this->_save_base64_upload($aadharBase64, 'aadhar');
                if (!$saved['ok']) {
                    return $this->_json(array('success' => false, 'message' => 'aadhar_file upload failed: ' . $saved['error']), 400);
                }
                if ($this->db->field_exists('aadhar_file', 'nb_users')) {
                    $update['aadhar_file'] = $saved['path'];
                }
            }
        }

        if (empty($update)) {
            return $this->_json(array('success' => false, 'message' => 'No fields provided to update'), 400);
        }

        $update['updated_at'] = date('Y-m-d H:i:s');
        $this->Nb_user_model->update($id, $update);

        $updated = $this->Nb_user_model->get_by_id($id);
        $this->_json(array('success' => true, 'message' => 'Profile updated successfully', 'user' => $this->_user_public($updated)));
    }

    /**
     * POST /api/nb/property/update
     * Partial update — send only fields you want to change.
     * Body: property_id (required), userId (required), + any updatable fields.
     */
    public function update_property()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }

        $input = $this->_input_json_or_post();

        if (isset($input['__json_parse_error__'])) {
            return $this->_json(array('success' => false, 'message' => 'Invalid JSON: ' . $input['__json_parse_error__'] . '. Check for trailing commas.'), 400);
        }

        // --- identify property ---
        $property_id = isset($input['property_id']) ? (int) $input['property_id'] : 0;
        if ($property_id < 1) {
            return $this->_json(array('success' => false, 'message' => 'property_id is required'), 400);
        }

        // --- identify owner ---
        $uid_raw = isset($input['userId']) ? $input['userId'] : (isset($input['user_id']) ? $input['user_id'] : null);
        if (!$uid_raw) {
            return $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
        }
        $user_id = (int) $uid_raw;
        if ($user_id < 1) {
            return $this->_json(array('success' => false, 'message' => 'Valid userId is required'), 400);
        }

        $property = $this->Nb_property_model->get_by_id($property_id);
        if (!$property) {
            return $this->_json(array('success' => false, 'message' => 'Property not found'), 404);
        }

        if ((int) $property->owner_id !== $user_id) {
            return $this->_json(array('success' => false, 'message' => 'You can only edit your own listings'), 403);
        }

        $update = array();

        // --- title ---
        if (isset($input['title'])) {
            $title = trim(strip_tags((string) $input['title']));
            if (strlen($title) < 3) {
                return $this->_json(array('success' => false, 'message' => 'title must be at least 3 characters'), 400);
            }
            if (strlen($title) > 300) {
                return $this->_json(array('success' => false, 'message' => 'title must be under 300 characters'), 400);
            }
            $update['title'] = $title;
            $update['slug']  = $this->Nb_property_model->unique_slug($title, $property_id);
        }

        // --- description ---
        if (isset($input['description'])) {
            $update['description'] = trim((string) $input['description']);
        }

        // --- property_type ---
        if (isset($input['property_type'])) {
            $pt = trim((string) $input['property_type']);
            $valid_types = array_keys(nb_property_types_map());
            if (!in_array($pt, $valid_types, true)) {
                return $this->_json(array('success' => false, 'message' => 'Invalid property_type. Valid: ' . implode(', ', $valid_types)), 400);
            }
            $update['property_type'] = $pt;
        }

        // --- listing_type ---
        if (isset($input['listing_type'])) {
            $lt = trim(strtolower((string) $input['listing_type']));
            if (!in_array($lt, array('rent', 'sale'), true)) {
                return $this->_json(array('success' => false, 'message' => 'listing_type must be rent or sale'), 400);
            }
            $update['listing_type'] = $lt;
        }

        // --- price ---
        if (isset($input['price'])) {
            if (!is_numeric($input['price']) || (float) $input['price'] < 0) {
                return $this->_json(array('success' => false, 'message' => 'price must be a positive number'), 400);
            }
            $update['price'] = (float) $input['price'];
        }

        // --- integer fields ---
        foreach (array('bedrooms', 'bathrooms', 'area_sqft') as $f) {
            if (isset($input[$f])) {
                $update[$f] = $input[$f] !== '' ? (int) $input[$f] : null;
            }
        }

        // --- text fields ---
        foreach (array('address', 'locality', 'location') as $f) {
            if (isset($input[$f])) {
                $update[$f] = trim(strip_tags((string) $input[$f]));
            }
        }

        // --- city_id ---
        if (isset($input['city_id'])) {
            $city_id = (int) $input['city_id'];
            if ($city_id < 1) {
                return $this->_json(array('success' => false, 'message' => 'Valid city_id is required'), 400);
            }
            $city = $this->db->get_where('nb_cities', array('id' => $city_id))->row();
            if (!$city) {
                return $this->_json(array('success' => false, 'message' => 'City not found'), 404);
            }
            $update['city_id'] = $city_id;
        }

        // --- lat/lng/place ---
        if (isset($input['latitude'])) {
            $lat = (float) $input['latitude'];
            $update['latitude'] = ($lat >= -90 && $lat <= 90) ? $lat : null;
        }
        if (isset($input['longitude'])) {
            $lng = (float) $input['longitude'];
            $update['longitude'] = ($lng >= -180 && $lng <= 180) ? $lng : null;
        }
        if (isset($input['google_place_id'])) {
            $update['google_place_id'] = trim((string) $input['google_place_id']) ?: null;
        }

        // --- decimal fields ---
        foreach (array('rate_per_sqft', 'plot_length_ft', 'plot_width_ft') as $f) {
            if (isset($input[$f])) {
                $update[$f] = $input[$f] !== '' && is_numeric($input[$f]) ? round((float) $input[$f], 2) : null;
            }
        }

        // --- boolean flags ---
        foreach (array('is_price_negotiable', 'tags_best_rate_localities', 'tags_high_growth_localities', 'is_active') as $f) {
            if (isset($input[$f])) {
                $update[$f] = !empty($input[$f]) ? 1 : 0;
            }
        }

        // --- has_boundary_wall ---
        if (isset($input['has_boundary_wall'])) {
            $hbw = $input['has_boundary_wall'];
            $update['has_boundary_wall'] = ($hbw === '' || $hbw === null) ? null : ($hbw ? 1 : 0);
        }

        // --- available_from ---
        if (isset($input['available_from'])) {
            $v = trim((string) $input['available_from']);
            if ($v !== '') {
                $t = strtotime($v);
                $update['available_from'] = $t !== false ? date('Y-m-d', $t) : null;
            } else {
                $update['available_from'] = null;
            }
        }

        // --- video_url ---
        if (isset($input['video_url'])) {
            $update['video_url'] = nb_sanitize_video_url((string) $input['video_url']);
        }

        // --- amenities ---
        if (isset($input['amenities'])) {
            $amenities = is_array($input['amenities']) ? $input['amenities'] : array();
            $amenities = array_values(array_filter(array_map('trim', $amenities)));
            $update['amenities'] = json_encode($amenities);
        }

        // --- nearby ---
        if (isset($input['nearby'])) {
            $nearby = is_array($input['nearby']) ? $input['nearby'] : array();
            $clean = array();
            foreach ($nearby as $item) {
                $cat  = trim((string) ($item['category'] ?? ''));
                $name = trim((string) ($item['name'] ?? $item['title'] ?? ''));
                $dist = trim((string) ($item['distance'] ?? ''));
                if ($cat !== '' || $name !== '' || $dist !== '') {
                    $clean[] = array('category' => $cat, 'title' => $cat, 'name' => $name, 'distance' => $dist);
                }
            }
            $update['nearby'] = json_encode($clean);
        }

        // --- location_image: multipart file OR base64 ---
        $uploadDir = FCPATH . 'assets/uploads/nb_properties/';
        if (!empty($_FILES['location_image']['name'])) {
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $this->load->library('upload');
            $this->upload->initialize(array(
                'upload_path'   => $uploadDir,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size'      => 5120,
                'encrypt_name'  => true,
            ));
            if (!$this->upload->do_upload('location_image')) {
                return $this->_json(array('success' => false, 'message' => 'location_image upload failed: ' . strip_tags($this->upload->display_errors('', ''))), 400);
            }
            $uData = $this->upload->data();
            if (!empty($property->location_image) && file_exists(FCPATH . $property->location_image)) {
                @unlink(FCPATH . $property->location_image);
            }
            $update['location_image'] = 'assets/uploads/nb_properties/' . $uData['file_name'];
        } elseif (isset($input['location_image']) && $this->_looks_like_base64_payload($input['location_image'])) {
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $saved = $this->_save_base64_upload_property($input['location_image']);
            if (!$saved['ok']) {
                return $this->_json(array('success' => false, 'message' => 'location_image upload failed: ' . $saved['error']), 400);
            }
            if (!empty($property->location_image) && file_exists(FCPATH . $property->location_image)) {
                @unlink(FCPATH . $property->location_image);
            }
            $update['location_image'] = $saved['path'];
        }

        // --- gallery images: multipart files OR base64 array ---
        $new_paths = array();
        if (!empty($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $this->load->library('upload');
            $count = min(count($_FILES['images']['name']), 10);
            for ($i = 0; $i < $count; $i++) {
                if (empty($_FILES['images']['name'][$i])) continue;
                $_FILES['userfile'] = array(
                    'name'     => $_FILES['images']['name'][$i],
                    'type'     => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i],
                );
                $this->upload->initialize(array(
                    'upload_path'   => $uploadDir,
                    'allowed_types' => 'jpg|jpeg|png|webp',
                    'max_size'      => 5120,
                    'encrypt_name'  => true,
                ));
                if ($this->upload->do_upload('userfile')) {
                    $new_paths[] = 'assets/uploads/nb_properties/' . $this->upload->data()['file_name'];
                }
            }
        } elseif (isset($input['images']) && is_array($input['images'])) {
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            foreach (array_slice($input['images'], 0, 10) as $imgItem) {
                if (!is_string($imgItem)) continue;
                if ($this->_looks_like_base64_payload($imgItem)) {
                    $saved = $this->_save_base64_upload_property($imgItem);
                    if ($saved['ok']) {
                        $new_paths[] = $saved['path'];
                    }
                } elseif (strpos(trim($imgItem), 'assets/uploads/nb_properties/') === 0) {
                    $new_paths[] = trim($imgItem);
                }
            }
        }

        if (!empty($new_paths) || isset($input['existing_paths']) || isset($input['remove_existing'])) {
            $existing_paths = isset($input['existing_paths']) && is_array($input['existing_paths'])
                ? $input['existing_paths'] : array();
            $remove = isset($input['remove_existing']) && is_array($input['remove_existing'])
                ? $input['remove_existing'] : array();

            $kept = array();
            foreach ($existing_paths as $ep) {
                $ep = trim((string) $ep);
                if ($ep !== '' && !in_array($ep, $remove, true) && strpos($ep, 'assets/uploads/nb_properties/') === 0) {
                    $kept[] = $ep;
                }
            }

            $all_images = array_slice(array_merge($kept, $new_paths), 0, 10);
            $cover_index = (int) ($input['cover_index'] ?? 0);
            $all_images  = nb_reorder_cover($all_images, $cover_index);
            $update['images'] = json_encode($all_images);

        }

        if (empty($update)) {
            return $this->_json(array('success' => false, 'message' => 'No fields provided to update'), 400);
        }

        $update['updated_at'] = date('Y-m-d H:i:s');
        $this->Nb_property_model->update($property_id, $update);

        $saved = $this->Nb_property_model->get_by_id($property_id);
        $out   = $this->_format_property_card($saved);

        $this->_json(array(
            'success'     => true,
            'message'     => 'Property updated successfully',
            'property_id' => $property_id,
            'property'    => $out,
        ));
    }

    /** Save base64 image to assets/uploads/nb_properties/ and return relative path. */
    private function _save_base64_upload_property($payload)
    {
        $payload = trim((string) $payload);
        if ($payload === '') {
            return array('ok' => false, 'path' => null, 'error' => 'Empty payload');
        }

        $targetDir = FCPATH . 'assets/uploads/nb_properties/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $rawBase64 = $payload;
        $ext = 'jpg';
        if (stripos($payload, 'data:') === 0) {
            if (preg_match('/^data:([a-zA-Z0-9\/\-\.+]+);base64,(.*)$/s', $payload, $m)) {
                $mime = strtolower(trim($m[1]));
                $rawBase64 = $m[2];
                $map = array(
                    'image/jpeg' => 'jpg',
                    'image/jpg'  => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                );
                if (isset($map[$mime])) {
                    $ext = $map[$mime];
                }
            } else {
                return array('ok' => false, 'path' => null, 'error' => 'Invalid data URI format');
            }
        }

        $bin = base64_decode($rawBase64, true);
        if ($bin === false) {
            return array('ok' => false, 'path' => null, 'error' => 'Invalid base64 data');
        }
        if (strlen($bin) > 5 * 1024 * 1024) {
            return array('ok' => false, 'path' => null, 'error' => 'File too large (max 5MB)');
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $fullPath = $targetDir . $fileName;
        if (@file_put_contents($fullPath, $bin) === false) {
            return array('ok' => false, 'path' => null, 'error' => 'Could not save file');
        }

        return array('ok' => true, 'path' => 'assets/uploads/nb_properties/' . $fileName, 'error' => null);
    }

    private function _search_filters_from_request()
    {
        $get = $this->input->get();
        $radius_km = isset($get['radius_km']) && $get['radius_km'] !== '' ? max(1, min(100, (float) $get['radius_km'])) : 15;
        return array(
            'city_id'        => isset($get['city_id']) ? $get['city_id'] : null,
            'property_type'  => isset($get['property_type']) ? $get['property_type'] : null,
            'listing_type'   => isset($get['listing_type']) ? $get['listing_type'] : null,
            'min_price'      => isset($get['min_price']) ? $get['min_price'] : null,
            'max_price'      => isset($get['max_price']) ? $get['max_price'] : null,
            'bedrooms'       => isset($get['bedrooms']) ? $get['bedrooms'] : null,
            'locality_q'     => isset($get['q']) ? trim((string) $get['q']) : null,
            'sort'           => isset($get['sort']) ? $get['sort'] : 'new',
            'lat'            => isset($get['lat']) ? $get['lat'] : null,
            'lng'            => isset($get['lng']) ? $get['lng'] : null,
            'radius_km'      => $radius_km,
        );
    }

    /**
     * GET — search active listings (public).
     * Query: q (area/title/address), city_id, property_type, listing_type, min_price, max_price, bedrooms, sort, lat, lng, radius_km, page, limit
     */
    public function search()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        return $this->_execute_search($this->_search_filters_from_request());
    }

    /** GET — same as search but requires city_id. */
    public function search_city()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $cid = $this->input->get('city_id');
        if ($cid === null || $cid === '') {
            return $this->_json(array('success' => false, 'message' => 'city_id is required'), 400);
        }
        return $this->_execute_search($this->_search_filters_from_request());
    }

    /** GET — same as search but requires property_type (slug). */
    public function search_type()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $t = $this->input->get('property_type');
        if ($t === null || $t === '') {
            return $this->_json(array('success' => false, 'message' => 'property_type is required'), 400);
        }
        return $this->_execute_search($this->_search_filters_from_request());
    }

    private function _execute_search(array $filters)
    {
        $page = max(1, (int) $this->input->get('page'));
        $limit = min(50, max(1, (int) $this->input->get('limit') ?: 12));
        $offset = ($page - 1) * $limit;
        $total = $this->Nb_property_model->count_search($filters);
        $rows = $this->Nb_property_model->search($filters, $limit, $offset);
        $items = array();
        foreach ($rows as $p) {
            $items[] = $this->_format_property_card($p);
        }
        $this->_json(array(
            'success' => true,
            'total'   => $total,
            'page'    => $page,
            'limit'   => $limit,
            'items'   => $items,
        ));
    }

    /** GET — active cities for filters. */
    public function cities()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $rows = $this->Nb_city_model->all_active();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'id'    => (int) $r->id,
                'name'  => $r->name,
                'state' => $r->state,
                'image' => $this->_asset_url_or_null(isset($r->image) ? $r->image : null),
            );
        }
        $this->_json(array('success' => true, 'cities' => $out));
    }

    /** GET — allowed property_type slugs + labels. */
    public function property_types()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $map = nb_property_types_map();
        $list = array();
        foreach ($map as $slug => $label) {
            $list[] = array('slug' => $slug, 'label' => $label);
        }
        $this->_json(array('success' => true, 'property_types' => $list));
    }

    /**
     * Build absolute image URLs for gallery JSON (same order as stored: cover first).
     *
     * @param object $p nb_properties row
     * @return array{0: string[], 1: string|null} [image_urls, thumbnail_url]
     */
    private function _property_image_urls($p)
    {
        $raw = isset($p->images) ? json_decode($p->images, true) : array();
        if (!is_array($raw)) {
            $raw = array();
        }
        $urls = array();
        foreach ($raw as $path) {
            if (!is_string($path)) {
                continue;
            }
            $path = trim($path);
            if ($path === '') {
                continue;
            }
            if (preg_match('#^https?://#i', $path)) {
                $urls[] = $path;
            } else {
                $urls[] = base_url($path);
            }
        }
        $thumb = !empty($urls) ? $urls[0] : null;
        return array($urls, $thumb);
    }

    /** @param object $p */
    private function _decode_images_array($p)
    {
        if (!isset($p->images) || $p->images === null || $p->images === '') {
            return array();
        }
        $d = is_string($p->images) ? json_decode($p->images, true) : $p->images;
        return is_array($d) ? array_values($d) : array();
    }

    /** @param object $p */
    private function _decode_amenities_array($p)
    {
        if (!isset($p->amenities) || $p->amenities === null || $p->amenities === '') {
            return null;
        }
        $d = is_string($p->amenities) ? json_decode($p->amenities, true) : $p->amenities;
        return is_array($d) ? $d : null;
    }

    /**
     * Full public listing row: all nb_properties columns (snake_case) + city_name, owner_name,
     * plus computed price_formatted, property_type_label, thumbnail_url, image_urls, url.
     *
     * @param object $p Row from Nb_property_model::search (p.*, city_name, owner_name)
     */
    private function _format_property_card($p)
    {
        list($image_urls, $thumb) = $this->_property_image_urls($p);
        $images_rel = $this->_decode_images_array($p);

        $video_url = null;
        if (isset($p->video_url) && $p->video_url !== null && trim((string) $p->video_url) !== '') {
            $video_url = nb_sanitize_video_url($p->video_url);
        }

        $row = array(
            'id'                   => (int) $p->id,
            'owner_id'             => isset($p->owner_id) ? (int) $p->owner_id : null,
            'title'                => isset($p->title) ? $p->title : '',
            'slug'                 => isset($p->slug) ? $p->slug : '',
            'description'          => isset($p->description) ? $p->description : null,
            'property_type'        => isset($p->property_type) ? $p->property_type : '',
            'listing_type'         => isset($p->listing_type) ? $p->listing_type : '',
            'price'                => isset($p->price) ? (float) $p->price : 0.0,
            'bedrooms'             => isset($p->bedrooms) && $p->bedrooms !== null && $p->bedrooms !== '' ? (int) $p->bedrooms : null,
            'bathrooms'            => isset($p->bathrooms) && $p->bathrooms !== null && $p->bathrooms !== '' ? (int) $p->bathrooms : null,
            'area_sqft'            => isset($p->area_sqft) && $p->area_sqft !== null && $p->area_sqft !== '' ? (int) $p->area_sqft : null,
            'address'              => isset($p->address) ? $p->address : '',
            'locality'             => isset($p->locality) ? $p->locality : '',
            'city_id'              => isset($p->city_id) ? (int) $p->city_id : null,
            'latitude'             => isset($p->latitude) && $p->latitude !== null && $p->latitude !== '' ? (float) $p->latitude : null,
            'longitude'            => isset($p->longitude) && $p->longitude !== null && $p->longitude !== '' ? (float) $p->longitude : null,
            'google_place_id'      => isset($p->google_place_id) && $p->google_place_id !== '' ? $p->google_place_id : null,
            'is_price_negotiable'  => isset($p->is_price_negotiable) ? (int) (bool) $p->is_price_negotiable : 0,
            'rate_per_sqft'        => isset($p->rate_per_sqft) && $p->rate_per_sqft !== null && $p->rate_per_sqft !== '' ? (float) $p->rate_per_sqft : null,
            'available_from'       => isset($p->available_from) && $p->available_from !== null && $p->available_from !== '' ? substr((string) $p->available_from, 0, 10) : null,
            'plot_length_ft'       => isset($p->plot_length_ft) && $p->plot_length_ft !== null && $p->plot_length_ft !== '' ? (float) $p->plot_length_ft : null,
            'plot_width_ft'        => isset($p->plot_width_ft) && $p->plot_width_ft !== null && $p->plot_width_ft !== '' ? (float) $p->plot_width_ft : null,
            'has_boundary_wall'    => isset($p->has_boundary_wall) && $p->has_boundary_wall !== null && $p->has_boundary_wall !== '' ? (int) $p->has_boundary_wall : null,
            'amenities'            => $this->_decode_amenities_array($p),
            'images'               => $images_rel,
            'video_url'            => $video_url,
            'is_active'            => isset($p->is_active) ? (int) (bool) $p->is_active : 0,
            'is_featured'          => isset($p->is_featured) ? (int) (bool) $p->is_featured : 0,
            'views'                => isset($p->views) ? (int) $p->views : 0,
            'created_at'           => isset($p->created_at) ? (string) $p->created_at : null,
            'updated_at'           => isset($p->updated_at) ? (string) $p->updated_at : null,
            'city_name'            => isset($p->city_name) ? $p->city_name : '',
            'owner_name'           => isset($p->owner_name) ? $p->owner_name : '',
            'owner_phone'          => isset($p->owner_phone) ? $p->owner_phone : null,
            'property_type_label'  => nb_property_type_label(isset($p->property_type) ? $p->property_type : ''),
            'price_formatted'      => nb_format_listing_price(isset($p->price) ? $p->price : 0, isset($p->listing_type) ? $p->listing_type : 'sale'),
            'thumbnail_url'        => $thumb,
            'image_urls'           => $image_urls,
            'url'                  => nb_property_url($p),
        );

        return $row;
    }

    /** @return string Wishlist `user_id` column value (VARCHAR). */
    private function _wishlist_user_key($user)
    {
        return (string) (int) $user->id;
    }

    /**
     * First image path/URL from nb_properties.images JSON array.
     *
     * @param object $p
     * @return string
     */
    private function _nb_property_first_image($p)
    {
        if (!isset($p->images) || $p->images === null || $p->images === '') {
            return '';
        }
        $raw = is_string($p->images) ? json_decode($p->images, true) : $p->images;
        if (!is_array($raw) || empty($raw)) {
            return '';
        }
        $first = $raw[0];
        return is_string($first) ? $first : '';
    }

    /**
     * @param object $p nb_properties row (with city_name from get_by_id)
     * @return array Data for wishlists insert
     */
    private function _wishlist_row_data_from_property($p)
    {
        $loc = array();
        if (!empty($p->locality)) {
            $loc[] = $p->locality;
        }
        if (!empty($p->city_name)) {
            $loc[] = $p->city_name;
        }
        $location = !empty($loc) ? implode(', ', $loc) : '';
        $img = $this->_nb_property_first_image($p);
        return array(
            'property_name'     => isset($p->title) ? $p->title : '',
            'property_image'    => $img !== '' ? $img : null,
            'property_price'    => isset($p->price) ? (float) $p->price : null,
            'property_location' => $location !== '' ? $location : null,
        );
    }

    private function _format_wishlist_item($w)
    {
        $property = null;
        $pid = isset($w->property_id) ? (int) $w->property_id : 0;
        if ($pid > 0) {
            $p = $this->Nb_property_model->get_by_id($pid);
            if ($p) {
                $property = $this->_format_property_card($p);
            }
        }
        return array(
            'id'          => isset($w->id) ? (string) $w->id : '',
            'userId'      => isset($w->user_id) ? (string) $w->user_id : '',
            'propertyId'  => isset($w->property_id) ? (string) $w->property_id : '',
            'createdAt'   => isset($w->created_at) ? (string) $w->created_at : '',
            'propertyName' => isset($w->property_name) ? $w->property_name : null,
            'property'    => $property,
        );
    }

    private function _notify_admin_enquiry($prop, $tenant, $message, $phone, $email)
    {
        $admin = $this->config->item('nb_admin_email');
        if (empty($admin)) {
            return;
        }
        $this->load->library('email');
        $tname = isset($tenant->name) ? $tenant->name : 'User';
        $this->email->from('noreply@localhost', 'Dream Villa Makers');
        $this->email->to($admin);
        $this->email->subject('New property enquiry: ' . $prop->title);
        $this->email->message(
            "From: {$tname} ({$email}) {$phone}\nProperty #{$prop->id}: {$prop->title}\n\n{$message}"
        );
        @$this->email->send();
    }

    /**
     * @param object $n notifications row
     * @return array
     */
    private function _format_notification_row($n)
    {
        $img = isset($n->image) ? trim((string) $n->image) : '';
        $video = isset($n->video) ? trim((string) $n->video) : '';
        return array(
            'id' => isset($n->id) ? (int) $n->id : 0,
            'title' => isset($n->title) ? (string) $n->title : '',
            'description' => isset($n->description) && $n->description !== null ? (string) $n->description : '',
            'status' => isset($n->status) ? (string) $n->status : 'active',
            'image' => $img !== '' ? $img : null,
            'image_url' => $img !== '' ? $this->_asset_url_or_null($img) : null,
            'video' => $video !== '' ? $video : null,
            'video_url' => $video !== '' ? $this->_asset_url_or_null($video) : null,
            'created_at' => isset($n->created_at) ? (string) $n->created_at : null,
        );
    }

    /**
     * GET — list notifications for mobile app.
     * Query: status (default active), limit, offset.
     */
    public function notifications()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $this->load->model('Notification_model');
        $status = $this->input->get('status');
        if ($status === null || $status === '') {
            $status = 'active';
        } else {
            $status = strtolower(trim((string) $status));
            if (!in_array($status, array('active', 'inactive', 'all'), true)) {
                return $this->_json(array('success' => false, 'message' => 'status must be active, inactive, or all'), 400);
            }
        }

        $limit = $this->input->get('limit') !== null && $this->input->get('limit') !== ''
            ? max(1, min(100, (int) $this->input->get('limit')))
            : null;
        $offset = $this->input->get('offset') !== null && $this->input->get('offset') !== ''
            ? max(0, (int) $this->input->get('offset'))
            : 0;

        $rows = $status === 'all'
            ? $this->Notification_model->get_all(null)
            : $this->Notification_model->get_all($status);
        $total = count($rows);
        if ($limit !== null) {
            $rows = array_slice($rows, $offset, $limit);
        } elseif ($offset > 0) {
            $rows = array_slice($rows, $offset);
        }

        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->_format_notification_row($row);
        }
        $this->_json(array(
            'success' => true,
            'notifications' => $out,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
        ));
    }

    /**
     * GET — notification details by id.
     */
    public function notification($id = null)
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $this->load->model('Notification_model');
        if ($id === null || $id === '') {
            $id = $this->uri->segment(4);
        }
        $id = (int) $id;
        if ($id < 1) {
            return $this->_json(array('success' => false, 'message' => 'notification id is required'), 400);
        }

        $row = $this->Notification_model->get_by_id($id);
        if (!$row || (!isset($row->status) || (string) $row->status !== 'active')) {
            return $this->_json(array('success' => false, 'message' => 'Notification not found'), 404);
        }
        $this->_json(array('success' => true, 'notification' => $this->_format_notification_row($row)));
    }

    private function _decode_housing_news_images($raw)
    {
        if (!is_string($raw) || trim($raw) === '') {
            return array();
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return array();
        }
        $out = array();
        foreach ($decoded as $img) {
            if (!is_string($img)) {
                continue;
            }
            $img = trim($img);
            if ($img === '') {
                continue;
            }
            $out[] = $img;
        }
        return array_values(array_unique($out));
    }

    private function _format_housing_news($row)
    {
        $images = $this->_decode_housing_news_images(isset($row->multiImages) ? (string) $row->multiImages : '');
        $imageUrls = array();
        foreach ($images as $img) {
            $url = $this->_asset_url_or_null($img);
            if ($url !== null) {
                $imageUrls[] = $url;
            }
        }
        $createdAt = isset($row->createdAt) ? (string) $row->createdAt : '';
        $publishedAt = null;
        if ($createdAt !== '') {
            $ts = strtotime($createdAt);
            $publishedAt = $ts ? date(DATE_ATOM, $ts) : $createdAt;
        }
        return array(
            'id' => isset($row->id) ? (int) $row->id : 0,
            'title' => isset($row->title) ? (string) $row->title : '',
            'subtitle' => isset($row->subtitle) && $row->subtitle !== null ? (string) $row->subtitle : '',
            'description' => isset($row->description) && $row->description !== null ? (string) $row->description : '',
            'authorName' => isset($row->authorName) && $row->authorName !== null ? (string) $row->authorName : '',
            'category' => isset($row->category) ? (string) $row->category : 'market',
            'slug' => isset($row->title) ? url_title((string) $row->title, '-', true) : '',
            'multiImages' => $imageUrls,
            'publishedAt' => $publishedAt,
            'createdAt' => $createdAt !== '' ? $createdAt : null,
        );
    }

    /**
     * GET /api/mobile/housing-news
     * Query: category, limit, offset
     */
    public function housing_news()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $this->load->model('Housing_news_model');
        $category = trim((string) $this->input->get('category'));
        if (!in_array($category, array('', 'market', 'tips', 'legal'), true)) {
            return $this->_json(array('success' => false, 'message' => 'category must be market, tips, or legal'), 400);
        }
        $limit = $this->input->get('limit') !== null && $this->input->get('limit') !== ''
            ? max(1, min(100, (int) $this->input->get('limit')))
            : null;
        $offset = $this->input->get('offset') !== null && $this->input->get('offset') !== ''
            ? max(0, (int) $this->input->get('offset'))
            : 0;

        $rows = $this->Housing_news_model->get_all($category !== '' ? $category : null, $limit, $offset);
        $total = $this->Housing_news_model->count_all($category !== '' ? $category : null);
        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->_format_housing_news($row);
        }
        $this->_json(array(
            'success' => true,
            'housingNews' => $out,
            'total' => (int) $total,
            'limit' => $limit,
            'offset' => $offset,
        ));
    }

    /**
     * GET /api/mobile/housing-news/{id}
     */
    public function housing_news_item($id = null)
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $this->load->model('Housing_news_model');
        if ($id === null || $id === '') {
            $id = $this->uri->segment(4);
        }
        $id = (int) $id;
        if ($id < 1) {
            return $this->_json(array('success' => false, 'message' => 'id is required'), 400);
        }
        $row = $this->Housing_news_model->get_by_id($id);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'Housing news not found'), 404);
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->_format_housing_news($row),
        ));
    }

    /**
     * GET — list wishlist for Bearer user.
     * POST — add property to wishlist (body: property_id or propertyId).
     */
    public function wishlist()
    {
        $method = strtoupper((string) $this->input->server('REQUEST_METHOD'));
        $uid = trim((string) $this->input->get('userId'));
        if ($uid === '') {
            $uid = trim((string) $this->input->get('user_id'));
        }
        $u = null;
        if ($uid === '') {
            $input = $this->_input_json_or_post();
            $uid = isset($input['userId']) ? trim((string) $input['userId']) : (isset($input['user_id']) ? trim((string) $input['user_id']) : '');
        }
        if ($uid === '') {
            return $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
        }

        if ($method === 'GET') {
            $this->load->model('Wishlist_model');
            $limit = $this->input->get('limit') ? (int) $this->input->get('limit') : null;
            $offset = $this->input->get('offset') ? (int) $this->input->get('offset') : 0;
            $items = $this->Wishlist_model->get_by_user($uid, $limit, $offset);
            $out = array();
            foreach ($items as $row) {
                $out[] = $this->_format_wishlist_item($row);
            }
            $this->_json(array(
                'success'   => true,
                'wishlist'  => $out,
                'total'     => $this->Wishlist_model->count_by_user($uid),
                'limit'     => $limit,
                'offset'    => $offset,
            ));
            return;
        }

        if ($method !== 'POST') {
            $this->_json(array('success' => false, 'message' => 'GET or POST only'), 405);
            return;
        }

        $input = $this->_input_json_or_post();
        $pid = isset($input['property_id']) ? (int) $input['property_id'] : 0;
        if ($pid < 1 && isset($input['propertyId'])) {
            $pid = (int) $input['propertyId'];
        }
        if ($pid < 1) {
            $this->_json(array('success' => false, 'message' => 'property_id is required'), 400);
            return;
        }

        $this->load->model('Wishlist_model');
        if ($this->Wishlist_model->is_wishlisted($uid, $pid)) {
            $this->_json(array('success' => true, 'message' => 'Already in wishlist', 'wishlisted' => true));
            return;
        }

        $p = $this->Nb_property_model->get_by_id($pid);
        if (!$p || empty($p->is_active)) {
            $this->_json(array('success' => false, 'message' => 'Property not found or inactive'), 404);
            return;
        }

        $snap = $this->_wishlist_row_data_from_property($p);
        $snap['user_id'] = $uid;
        $snap['property_id'] = $pid;
        $id = $this->Wishlist_model->create($snap);
        if (!$id) {
            $this->_json(array('success' => false, 'message' => 'Could not save wishlist'), 500);
            return;
        }
        $this->_json(array(
            'success'    => true,
            'wishlisted' => true,
            'id'         => (int) $id,
            'propertyId' => $pid,
        ));
    }

    /**
     * POST — toggle wishlist (add if missing, remove if present). Body: property_id / propertyId.
     */
    public function wishlist_toggle()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(array('success' => false, 'message' => 'POST only'), 405);
            return;
        }
        $input = $this->_input_json_or_post();
        $uid = isset($input['userId']) ? trim((string) $input['userId']) : (isset($input['user_id']) ? trim((string) $input['user_id']) : '');
        if ($uid === '') {
            return $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
        }
        $pid = isset($input['property_id']) ? (int) $input['property_id'] : 0;
        if ($pid < 1 && isset($input['propertyId'])) {
            $pid = (int) $input['propertyId'];
        }
        if ($pid < 1) {
            $this->_json(array('success' => false, 'message' => 'property_id is required'), 400);
            return;
        }

        $this->load->model('Wishlist_model');
        if ($this->Wishlist_model->is_wishlisted($uid, $pid)) {
            $this->Wishlist_model->remove_from_wishlist($uid, $pid);
            $this->_json(array('success' => true, 'wishlisted' => false, 'message' => 'Removed from wishlist'));
            return;
        }

        $p = $this->Nb_property_model->get_by_id($pid);
        if (!$p || empty($p->is_active)) {
            $this->_json(array('success' => false, 'message' => 'Property not found or inactive'), 404);
            return;
        }

        $snap = $this->_wishlist_row_data_from_property($p);
        $snap['user_id'] = $uid;
        $snap['property_id'] = $pid;
        $id = $this->Wishlist_model->create($snap);
        if (!$id) {
            $this->_json(array('success' => false, 'message' => 'Could not save wishlist'), 500);
            return;
        }
        $this->_json(array('success' => true, 'wishlisted' => true, 'id' => (int) $id));
    }

    /**
     * POST — remove from wishlist. Body: property_id / propertyId.
     */
    public function wishlist_remove()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(array('success' => false, 'message' => 'POST only'), 405);
            return;
        }
        $input = $this->_input_json_or_post();
        $uid = isset($input['userId']) ? trim((string) $input['userId']) : (isset($input['user_id']) ? trim((string) $input['user_id']) : '');
        if ($uid === '') {
            return $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
        }
        $pid = isset($input['property_id']) ? (int) $input['property_id'] : 0;
        if ($pid < 1 && isset($input['propertyId'])) {
            $pid = (int) $input['propertyId'];
        }
        if ($pid < 1) {
            $this->_json(array('success' => false, 'message' => 'property_id is required'), 400);
            return;
        }
        $this->load->model('Wishlist_model');
        $this->Wishlist_model->remove_from_wishlist($uid, $pid);
        $this->_json(array('success' => true, 'wishlisted' => false));
    }

    /**
     * GET — check if property is wishlisted. Query: property_id or propertyId.
     */
    public function wishlist_check()
    {
        if ($this->input->method() !== 'get') {
            $this->_json(array('success' => false, 'message' => 'GET only'), 405);
            return;
        }
        $uid = trim((string) ($this->input->get('userId') ?: $this->input->get('user_id')));
        if ($uid === '') {
            return $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
        }
        $pid = (int) ($this->input->get('property_id') ?: $this->input->get('propertyId'));
        if ($pid < 1) {
            $this->_json(array('success' => false, 'message' => 'property_id is required'), 400);
            return;
        }
        $this->load->model('Wishlist_model');
        $on = $this->Wishlist_model->is_wishlisted($uid, $pid);
        $this->_json(array('success' => true, 'wishlisted' => $on));
    }

    /**
     * POST — submit enquiry on a listing (Bearer). Body: property_id, message; optional phone, email.
     * Uses nb_enquiries when legacy enquiries table is absent (same as web api/enquiry/send).
     */
    public function enquiry()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(array('success' => false, 'message' => 'POST only'), 405);
            return;
        }
        $input = $this->_input_json_or_post();
        $uid = isset($input['userId']) ? (int) $input['userId'] : (isset($input['user_id']) ? (int) $input['user_id'] : 0);
        if ($uid < 1) {
            return $this->_json(array('success' => false, 'message' => 'userId is required'), 400);
        }
        $u = $this->Nb_user_model->get_by_id($uid);
        if (!$u) {
            return $this->_json(array('success' => false, 'message' => 'User not found'), 404);
        }
        if ($u->status !== 'approved') {
            $this->_json(array('success' => false, 'message' => 'Your account must be approved to send enquiries'), 403);
            return;
        }
        if (!in_array($u->role, array('owner', 'tenant'), true)) {
            $this->_json(array('success' => false, 'message' => 'This action is not available for your account type'), 403);
            return;
        }


        $pid = isset($input['property_id']) ? (int) $input['property_id'] : 0;
        if ($pid < 1 && isset($input['propertyId'])) {
            $pid = (int) $input['propertyId'];
        }
        $message = isset($input['message']) ? trim((string) $input['message']) : '';
        $phone = isset($input['phone']) ? trim((string) $input['phone']) : trim((string) $u->phone);
        $email = isset($input['email']) ? trim(strtolower((string) $input['email'])) : trim(strtolower((string) $u->email));

        if ($pid < 1 || $message === '' || $phone === '' || $email === '') {
            $this->_json(array('success' => false, 'message' => 'property_id, message, phone, and email are required (phone/email default from profile if omitted)'), 400);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->_json(array('success' => false, 'message' => 'Invalid email'), 400);
            return;
        }

        $prop = $this->Nb_property_model->get_by_id($pid);
        if (!$prop) {
            $this->_json(array('success' => false, 'message' => 'Property not found'), 404);
            return;
        }
        if (empty($prop->is_active)) {
            $this->_json(array('success' => false, 'message' => 'This listing is not available'), 404);
            return;
        }
        if ((int) $u->id === (int) $prop->owner_id) {
            $this->_json(array('success' => false, 'message' => 'You cannot send an enquiry on your own listing'), 403);
            return;
        }

        $this->load->model('Enquiry_model');
        $this->load->library('security');
        $id = $this->Enquiry_model->create(array(
            'user_id'     => (int) $u->id,
            'property_id' => $pid,
            'message'     => $this->security->xss_clean($message),
            'phone'       => $this->security->xss_clean($phone),
            'email'       => $this->security->xss_clean($email),
            'status'      => 'new',
        ));
        if (!$id) {
            $this->_json(array('success' => false, 'message' => 'Could not save enquiry'), 500);
            return;
        }

        $this->_notify_admin_enquiry($prop, $u, $message, $phone, $email);
        $this->_json(array(
            'success' => true,
            'message' => 'Enquiry sent. We\'ve routed it to the listing owner; they may contact you on your phone or email.',
            'id'      => (int) $id,
        ));
    }
}
