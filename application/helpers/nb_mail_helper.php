<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function nb_mail_env($key, $default = '')
{
    $v = getenv($key);
    if ($v === false || $v === '') {
        if (!empty($_SERVER[$key])) {
            $v = (string) $_SERVER[$key];
        } elseif (!empty($_ENV[$key])) {
            $v = (string) $_ENV[$key];
        } else {
            $v = $default;
        }
    }
    return is_string($v) ? trim($v) : $default;
}

function nb_mail_settings_all($flush = false)
{
    static $cache = null;
    if ($flush) {
        $cache = null;
    }
    if (is_array($cache)) {
        return $cache;
    }
    $CI =& get_instance();
    if (isset($CI->db) && $CI->db) {
        $CI->load->model('Nb_mail_model');
        $cache = $CI->Nb_mail_model->get_settings();
        return $cache;
    }
    $cache = array(
        'admin_email' => nb_mail_env('NB_ADMIN_EMAIL', 'admin@dreamvillamakers.com'),
        'from_email' => nb_mail_env('NB_MAIL_FROM', ''),
        'from_name' => 'Coimbatore Properties',
        'smtp_host' => nb_mail_env('SMTP_HOST', ''),
        'smtp_port' => nb_mail_env('SMTP_PORT', '465'),
        'smtp_crypto' => nb_mail_env('SMTP_CRYPTO', 'ssl'),
        'smtp_user' => nb_mail_env('SMTP_USER', ''),
        'smtp_pass' => nb_mail_env('SMTP_PASS', ''),
    );
    return $cache;
}

function nb_mail_admin_email()
{
    $s = nb_mail_settings_all();
    $admin = isset($s['admin_email']) ? trim((string) $s['admin_email']) : '';
    if ($admin === '') {
        $CI =& get_instance();
        $admin = trim((string) $CI->config->item('nb_admin_email'));
    }
    if ($admin === '') {
        $admin = nb_mail_env('NB_ADMIN_EMAIL', 'admin@dreamvillamakers.com');
    }
    return $admin;
}

function nb_mail_from_address()
{
    $s = nb_mail_settings_all();
    $from = isset($s['from_email']) ? trim((string) $s['from_email']) : '';
    if ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
        return $from;
    }
    $admin = nb_mail_admin_email();
    if ($admin !== '' && filter_var($admin, FILTER_VALIDATE_EMAIL)) {
        return $admin;
    }
    return 'noreply@superfinelabels.in';
}

function nb_mail_from_name()
{
    $s = nb_mail_settings_all();
    $name = isset($s['from_name']) ? trim((string) $s['from_name']) : '';
    return $name !== '' ? $name : 'Coimbatore Properties';
}

function nb_mail_site_url()
{
    $CI =& get_instance();
    $url = $CI->config->item('base_url');
    if (empty($url)) {
        $url = site_url();
    }
    return rtrim((string) $url, '/') . '/';
}

function nb_mail_user_name($user)
{
    if (!$user) {
        return 'there';
    }
    $row = is_array($user) ? $user : (array) $user;
    $name = isset($row['name']) ? trim((string) $row['name']) : '';
    return $name !== '' ? $name : 'there';
}

function nb_mail_wrap_html($title, $inner_html)
{
    $site = htmlspecialchars(nb_mail_site_url(), ENT_QUOTES, 'UTF-8');
    $title_esc = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $brand = htmlspecialchars(nb_mail_from_name(), ENT_QUOTES, 'UTF-8');
    return '<!DOCTYPE html><html><body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:24px 12px;">'
        . '<tr><td align="center">'
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">'
        . '<tr><td style="background:#0f766e;color:#ffffff;padding:18px 24px;font-size:18px;font-weight:bold;">' . $brand . '</td></tr>'
        . '<tr><td style="padding:24px;">'
        . '<h1 style="margin:0 0 16px;font-size:20px;color:#111827;">' . $title_esc . '</h1>'
        . $inner_html
        . '<p style="margin:24px 0 0;font-size:13px;color:#6b7280;">This is an automated message from ' . $brand . '.<br><a href="' . $site . '" style="color:#0f766e;">' . $site . '</a></p>'
        . '</td></tr></table>'
        . '</td></tr></table></body></html>';
}

function nb_mail_p($text)
{
    return '<p style="margin:0 0 12px;line-height:1.55;font-size:15px;">' . nl2br(htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8')) . '</p>';
}

function nb_mail_apply_vars($text, array $vars)
{
    $repl = array();
    foreach ($vars as $k => $v) {
        $repl['{' . $k . '}'] = (string) $v;
    }
    return strtr((string) $text, $repl);
}

function nb_mail_init_library()
{
    $CI =& get_instance();
    $s = nb_mail_settings_all();
    $cfg = array(
        'useragent' => 'Coimbatore Properties',
        'mailtype' => 'html',
        'charset' => 'utf-8',
        'wordwrap' => true,
        'newline' => "\r\n",
        'crlf' => "\r\n",
        'validate' => false,
    );
    $host = isset($s['smtp_host']) ? trim((string) $s['smtp_host']) : '';
    if ($host !== '') {
        $cfg['protocol'] = 'smtp';
        $cfg['smtp_host'] = $host;
        $cfg['smtp_user'] = isset($s['smtp_user']) ? (string) $s['smtp_user'] : '';
        $cfg['smtp_pass'] = isset($s['smtp_pass']) ? (string) $s['smtp_pass'] : '';
        $cfg['smtp_port'] = isset($s['smtp_port']) && $s['smtp_port'] !== '' ? (int) $s['smtp_port'] : 465;
        $crypto = isset($s['smtp_crypto']) ? strtolower(trim((string) $s['smtp_crypto'])) : 'ssl';
        $cfg['smtp_crypto'] = in_array($crypto, array('ssl', 'tls'), true) ? $crypto : 'ssl';
    } else {
        $cfg['protocol'] = 'mail';
    }
    $CI->load->library('email');
    $CI->email->initialize($cfg);
}

/**
 * @param string|string[] $to
 * @param string $subject
 * @param string $html
 * @param array $opts extra_to, bcc, reply_to
 * @return bool
 */
function nb_send_mail($to, $subject, $html, $opts = array())
{
    $recipients = array();
    foreach ((array) $to as $addr) {
        $addr = strtolower(trim((string) $addr));
        if ($addr !== '' && filter_var($addr, FILTER_VALIDATE_EMAIL) && !preg_match('/@phone\.cp$/i', $addr)) {
            $recipients[] = $addr;
        }
    }
    $recipients = array_values(array_unique($recipients));
    if (empty($recipients)) {
        return false;
    }

    $CI =& get_instance();
    nb_mail_init_library();
    $CI->email->clear(true);

    $from = nb_mail_from_address();
    $CI->email->from($from, nb_mail_from_name());
    $CI->email->to($recipients);
    if (!empty($opts['reply_to']) && filter_var($opts['reply_to'], FILTER_VALIDATE_EMAIL)) {
        $CI->email->reply_to($opts['reply_to']);
    }
    if (!empty($opts['bcc'])) {
        $CI->email->bcc($opts['bcc']);
    }
    $CI->email->subject($subject);
    $CI->email->message($html);
    $CI->email->set_mailtype('html');
    return (bool) @$CI->email->send();
}

function nb_mail_send_template($key, $to, array $vars, $opts = array())
{
    $CI =& get_instance();
    $CI->load->model('Nb_mail_model');
    $tpl = $CI->Nb_mail_model->get_template($key);
    if (!$tpl || empty($tpl['is_enabled'])) {
        return false;
    }
    if (!isset($vars['site_url'])) {
        $vars['site_url'] = nb_mail_site_url();
    }
    $subject = nb_mail_apply_vars($tpl['subject'], $vars);
    $heading = nb_mail_apply_vars($tpl['heading'], $vars);
    $body = nb_mail_apply_vars($tpl['body'], $vars);
    $html = nb_mail_wrap_html($heading, nb_mail_p($body));
    return nb_send_mail($to, $subject, $html, $opts);
}

function nb_notify_enquiry($prop, $enquirer, $message, $phone, $email)
{
    $title = isset($prop->title) ? (string) $prop->title : 'Property';
    $pid = isset($prop->id) ? (int) $prop->id : 0;
    $enquirer_name = nb_mail_user_name($enquirer);
    $phone = trim((string) $phone);
    $email = strtolower(trim((string) $email));
    $listing_url = function_exists('nb_property_url') ? nb_property_url($prop) : nb_mail_site_url();
    $vars = array(
        'name' => $enquirer_name,
        'enquirer_name' => $enquirer_name,
        'owner_name' => '',
        'email' => $email,
        'phone' => $phone,
        'message' => $message,
        'title' => $title,
        'property_id' => (string) $pid,
        'listing_url' => $listing_url,
        'reason' => '',
        'site_url' => nb_mail_site_url(),
    );

    $admin_ok = nb_mail_send_template('enquiry_admin', nb_mail_admin_email(), $vars, array(
        'reply_to' => $email,
    ));

    $customer_ok = false;
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $customer_ok = nb_mail_send_template('enquiry_customer', $email, $vars);
    }

    $owner_ok = false;
    $owner_id = isset($prop->owner_id) ? (int) $prop->owner_id : 0;
    if ($owner_id > 0) {
        $CI =& get_instance();
        $CI->load->model('Nb_user_model');
        $owner = $CI->Nb_user_model->get_by_id($owner_id);
        $owner_email = $owner ? nb_user_deliverable_email($owner) : '';
        if ($owner_email !== '') {
            $vars['owner_name'] = nb_mail_user_name($owner);
            $vars['name'] = $vars['owner_name'];
            $owner_ok = nb_mail_send_template('enquiry_owner', $owner_email, $vars, array(
                'reply_to' => $email,
            ));
        }
    }

    return $admin_ok || $customer_ok || $owner_ok;
}

function nb_notify_kyc_submitted($user)
{
    $name = nb_mail_user_name($user);
    $email = nb_user_deliverable_email($user);
    $vars = array(
        'name' => $name,
        'enquirer_name' => $name,
        'owner_name' => $name,
        'email' => $email !== '' ? $email : '(no email)',
        'phone' => isset($user->phone) ? (string) $user->phone : '',
        'message' => '',
        'title' => '',
        'property_id' => '',
        'listing_url' => nb_mail_site_url(),
        'reason' => '',
        'site_url' => nb_mail_site_url(),
    );
    $admin_ok = nb_mail_send_template('kyc_submitted_admin', nb_mail_admin_email(), $vars);
    $user_ok = $email !== '' ? nb_mail_send_template('kyc_submitted_customer', $email, $vars) : false;
    return $admin_ok || $user_ok;
}

function nb_notify_kyc_approved($user)
{
    $name = nb_mail_user_name($user);
    $email = nb_user_deliverable_email($user);
    $vars = array(
        'name' => $name,
        'enquirer_name' => $name,
        'owner_name' => $name,
        'email' => $email !== '' ? $email : '(no email)',
        'phone' => isset($user->phone) ? (string) $user->phone : '',
        'message' => '',
        'title' => '',
        'property_id' => '',
        'listing_url' => nb_mail_site_url(),
        'reason' => '',
        'site_url' => nb_mail_site_url(),
    );
    $admin_ok = nb_mail_send_template('kyc_approved_admin', nb_mail_admin_email(), $vars);
    $user_ok = $email !== '' ? nb_mail_send_template('kyc_approved_customer', $email, $vars) : false;
    return $admin_ok || $user_ok;
}

function nb_notify_kyc_rejected($user, $reason)
{
    $name = nb_mail_user_name($user);
    $email = nb_user_deliverable_email($user);
    $reason = trim((string) $reason);
    $vars = array(
        'name' => $name,
        'enquirer_name' => $name,
        'owner_name' => $name,
        'email' => $email !== '' ? $email : '(no email)',
        'phone' => isset($user->phone) ? (string) $user->phone : '',
        'message' => '',
        'title' => '',
        'property_id' => '',
        'listing_url' => nb_mail_site_url(),
        'reason' => $reason,
        'site_url' => nb_mail_site_url(),
    );
    $admin_ok = nb_mail_send_template('kyc_rejected_admin', nb_mail_admin_email(), $vars);
    $user_ok = $email !== '' ? nb_mail_send_template('kyc_rejected_customer', $email, $vars) : false;
    return $admin_ok || $user_ok;
}

function nb_notify_property_submitted($prop, $owner = null)
{
    $CI =& get_instance();
    if (!$owner && !empty($prop->owner_id)) {
        $CI->load->model('Nb_user_model');
        $owner = $CI->Nb_user_model->get_by_id((int) $prop->owner_id);
    }
    $title = isset($prop->title) ? (string) $prop->title : 'Listing';
    $pid = isset($prop->id) ? (int) $prop->id : 0;
    $owner_name = nb_mail_user_name($owner);
    $owner_email = $owner ? nb_user_deliverable_email($owner) : '';
    $vars = array(
        'name' => $owner_name,
        'enquirer_name' => $owner_name,
        'owner_name' => $owner_name,
        'email' => $owner_email !== '' ? $owner_email : '(no email)',
        'phone' => $owner && isset($owner->phone) ? (string) $owner->phone : '',
        'message' => '',
        'title' => $title,
        'property_id' => (string) $pid,
        'listing_url' => function_exists('nb_property_url') ? nb_property_url($prop) : nb_mail_site_url(),
        'reason' => '',
        'site_url' => nb_mail_site_url(),
    );
    $admin_ok = nb_mail_send_template('property_submitted_admin', nb_mail_admin_email(), $vars);
    $owner_ok = $owner_email !== '' ? nb_mail_send_template('property_submitted_owner', $owner_email, $vars) : false;
    return $admin_ok || $owner_ok;
}

function nb_notify_property_approved($prop, $owner = null)
{
    $CI =& get_instance();
    if (!$owner && !empty($prop->owner_id)) {
        $CI->load->model('Nb_user_model');
        $owner = $CI->Nb_user_model->get_by_id((int) $prop->owner_id);
    }
    $title = isset($prop->title) ? (string) $prop->title : 'Listing';
    $owner_name = nb_mail_user_name($owner);
    $owner_email = $owner ? nb_user_deliverable_email($owner) : '';
    $url = function_exists('nb_property_url') ? nb_property_url($prop) : nb_mail_site_url();
    $vars = array(
        'name' => $owner_name,
        'enquirer_name' => $owner_name,
        'owner_name' => $owner_name,
        'email' => $owner_email !== '' ? $owner_email : '(no email)',
        'phone' => $owner && isset($owner->phone) ? (string) $owner->phone : '',
        'message' => '',
        'title' => $title,
        'property_id' => isset($prop->id) ? (string) (int) $prop->id : '',
        'listing_url' => $url,
        'reason' => '',
        'site_url' => nb_mail_site_url(),
    );
    $admin_ok = nb_mail_send_template('property_approved_admin', nb_mail_admin_email(), $vars);
    $owner_ok = $owner_email !== '' ? nb_mail_send_template('property_approved_owner', $owner_email, $vars) : false;
    return $admin_ok || $owner_ok;
}

function nb_notify_property_rejected($prop, $reason, $owner = null)
{
    $CI =& get_instance();
    if (!$owner && !empty($prop->owner_id)) {
        $CI->load->model('Nb_user_model');
        $owner = $CI->Nb_user_model->get_by_id((int) $prop->owner_id);
    }
    $title = isset($prop->title) ? (string) $prop->title : 'Listing';
    $owner_name = nb_mail_user_name($owner);
    $owner_email = $owner ? nb_user_deliverable_email($owner) : '';
    $reason = trim((string) $reason);
    $vars = array(
        'name' => $owner_name,
        'enquirer_name' => $owner_name,
        'owner_name' => $owner_name,
        'email' => $owner_email !== '' ? $owner_email : '(no email)',
        'phone' => $owner && isset($owner->phone) ? (string) $owner->phone : '',
        'message' => '',
        'title' => $title,
        'property_id' => isset($prop->id) ? (string) (int) $prop->id : '',
        'listing_url' => nb_mail_site_url(),
        'reason' => $reason,
        'site_url' => nb_mail_site_url(),
    );
    $admin_ok = nb_mail_send_template('property_rejected_admin', nb_mail_admin_email(), $vars);
    $owner_ok = $owner_email !== '' ? nb_mail_send_template('property_rejected_owner', $owner_email, $vars) : false;
    return $admin_ok || $owner_ok;
}
