<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_mail_model extends CI_Model
{
    public function ensure_tables()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        if (!$this->db->table_exists('nb_mail_settings')) {
            $this->db->query(
                "CREATE TABLE `nb_mail_settings` (
                    `setting_key` VARCHAR(64) NOT NULL,
                    `setting_value` TEXT NULL DEFAULT NULL,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`setting_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
        }
        if (!$this->db->table_exists('nb_mail_templates')) {
            $this->db->query(
                "CREATE TABLE `nb_mail_templates` (
                    `template_key` VARCHAR(80) NOT NULL,
                    `label` VARCHAR(120) NOT NULL DEFAULT '',
                    `event_key` VARCHAR(64) NOT NULL DEFAULT '',
                    `audience` VARCHAR(32) NOT NULL DEFAULT '',
                    `to_email` VARCHAR(190) NULL DEFAULT NULL,
                    `subject` VARCHAR(255) NOT NULL DEFAULT '',
                    `heading` VARCHAR(255) NOT NULL DEFAULT '',
                    `body` TEXT NULL,
                    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
                    `is_system` TINYINT(1) NOT NULL DEFAULT 0,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`template_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
        }
        $this->ensure_template_columns();
        $this->seed_default_templates();
    }

    private function ensure_template_columns()
    {
        if (!$this->db->table_exists('nb_mail_templates')) {
            return;
        }
        $adds = array(
            'label' => "ALTER TABLE `nb_mail_templates` ADD `label` VARCHAR(120) NOT NULL DEFAULT '' AFTER `template_key`",
            'event_key' => "ALTER TABLE `nb_mail_templates` ADD `event_key` VARCHAR(64) NOT NULL DEFAULT '' AFTER `label`",
            'audience' => "ALTER TABLE `nb_mail_templates` ADD `audience` VARCHAR(32) NOT NULL DEFAULT '' AFTER `event_key`",
            'to_email' => "ALTER TABLE `nb_mail_templates` ADD `to_email` VARCHAR(190) NULL DEFAULT NULL AFTER `audience`",
            'is_system' => "ALTER TABLE `nb_mail_templates` ADD `is_system` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_enabled`",
        );
        foreach ($adds as $col => $sql) {
            if (!$this->db->field_exists($col, 'nb_mail_templates')) {
                $this->db->query($sql);
            }
        }
    }

    public function event_options()
    {
        $out = array();
        foreach ($this->catalog() as $event_key => $group) {
            $out[$event_key] = $group['label'];
        }
        return $out;
    }

    public function audience_options()
    {
        return array(
            'admin' => 'Admin inbox',
            'customer' => 'Customer / agent',
            'owner' => 'Listing owner',
        );
    }

    public function parse_system_key($key)
    {
        $key = trim((string) $key);
        foreach ($this->catalog() as $event_key => $group) {
            foreach ($group['templates'] as $tpl_key => $def) {
                if ($tpl_key === $key) {
                    $audience = 'admin';
                    if (substr($key, -9) === '_customer') {
                        $audience = 'customer';
                    } elseif (substr($key, -6) === '_owner') {
                        $audience = 'owner';
                    }
                    return array(
                        'event_key' => $event_key,
                        'audience' => $audience,
                        'label' => $def['label'],
                    );
                }
            }
        }
        return null;
    }

    public function setting_keys()
    {
        return array(
            'admin_email',
            'from_email',
            'from_name',
            'smtp_host',
            'smtp_port',
            'smtp_crypto',
            'smtp_user',
            'smtp_pass',
        );
    }

    public function get_settings()
    {
        $this->ensure_tables();
        $out = array(
            'admin_email' => '',
            'from_email' => '',
            'from_name' => 'Coimbatore Properties',
            'smtp_host' => '',
            'smtp_port' => '465',
            'smtp_crypto' => 'ssl',
            'smtp_user' => '',
            'smtp_pass' => '',
        );
        foreach ($out as $k => $v) {
            $env_map = array(
                'admin_email' => 'NB_ADMIN_EMAIL',
                'from_email' => 'NB_MAIL_FROM',
                'smtp_host' => 'SMTP_HOST',
                'smtp_port' => 'SMTP_PORT',
                'smtp_crypto' => 'SMTP_CRYPTO',
                'smtp_user' => 'SMTP_USER',
                'smtp_pass' => 'SMTP_PASS',
            );
            if (isset($env_map[$k]) && function_exists('nb_mail_env')) {
                $env = nb_mail_env($env_map[$k], '');
                if ($env !== '') {
                    $out[$k] = $env;
                }
            }
        }
        $rows = $this->db->get('nb_mail_settings')->result();
        foreach ($rows as $row) {
            $key = (string) $row->setting_key;
            if (!array_key_exists($key, $out)) {
                continue;
            }
            if ($key === 'smtp_pass' && ($row->setting_value === null || $row->setting_value === '')) {
                continue;
            }
            $out[$key] = (string) $row->setting_value;
        }
        if ($out['from_name'] === '') {
            $out['from_name'] = 'Coimbatore Properties';
        }
        if ($out['smtp_port'] === '') {
            $out['smtp_port'] = '465';
        }
        if ($out['smtp_crypto'] === '') {
            $out['smtp_crypto'] = 'ssl';
        }
        return $out;
    }

    public function save_settings(array $data)
    {
        $this->ensure_tables();
        $keys = $this->setting_keys();
        $current = $this->get_settings();
        foreach ($keys as $key) {
            if ($key === 'smtp_pass' && (!isset($data[$key]) || trim((string) $data[$key]) === '')) {
                continue;
            }
            $value = isset($data[$key]) ? trim((string) $data[$key]) : '';
            if ($key === 'smtp_port' && $value !== '' && !ctype_digit($value)) {
                $value = $current['smtp_port'];
            }
            $exists = $this->db->get_where('nb_mail_settings', array('setting_key' => $key))->row();
            $row = array(
                'setting_key' => $key,
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            if ($exists) {
                $this->db->where('setting_key', $key)->update('nb_mail_settings', $row);
            } else {
                $this->db->insert('nb_mail_settings', $row);
            }
        }
        return true;
    }

    public function catalog()
    {
        return array(
            'enquiry' => array(
                'label' => 'Property enquiry',
                'placeholders' => '{name}, {enquirer_name}, {email}, {phone}, {message}, {title}, {property_id}, {listing_url}, {site_url}',
                'templates' => array(
                    'enquiry_admin' => array(
                        'label' => 'Admin',
                        'subject' => 'New property enquiry: {title}',
                        'heading' => 'New property enquiry',
                        'body' => "A customer sent an enquiry on listing #{property_id}: {title}.\n\nFrom: {enquirer_name}\nEmail: {email}\nPhone: {phone}\n\nMessage:\n{message}\n\nListing: {listing_url}",
                    ),
                    'enquiry_customer' => array(
                        'label' => 'Customer',
                        'subject' => 'Enquiry received: {title}',
                        'heading' => 'We received your enquiry',
                        'body' => "Hello {enquirer_name},\n\nThanks for your interest in {title}. We have forwarded your enquiry to our team and the listing owner. They may contact you on {phone} or {email}.\n\nYour message:\n{message}",
                    ),
                    'enquiry_owner' => array(
                        'label' => 'Listing owner',
                        'subject' => 'New enquiry: {title}',
                        'heading' => 'New enquiry on your listing',
                        'body' => "Hello {owner_name},\n\nYou have a new enquiry on {title}.\n\nFrom: {enquirer_name}\nEmail: {email}\nPhone: {phone}\n\nMessage:\n{message}",
                    ),
                ),
            ),
            'kyc_submitted' => array(
                'label' => 'KYC submitted',
                'placeholders' => '{name}, {email}, {phone}, {site_url}',
                'templates' => array(
                    'kyc_submitted_admin' => array(
                        'label' => 'Admin',
                        'subject' => 'Agent KYC submitted: {name}',
                        'heading' => 'Agent KYC submitted',
                        'body' => "{name} submitted agent KYC for review.\n\nEmail: {email}\nPhone: {phone}\n\nOpen the admin panel to approve or reject this KYC.",
                    ),
                    'kyc_submitted_customer' => array(
                        'label' => 'Customer / agent',
                        'subject' => 'Your KYC has been submitted',
                        'heading' => 'KYC submitted for review',
                        'body' => "Hello {name},\n\nWe received your agent KYC documents. Our team will review them and email you when they are approved or if we need changes.",
                    ),
                ),
            ),
            'kyc_approved' => array(
                'label' => 'KYC approved',
                'placeholders' => '{name}, {email}, {phone}, {site_url}',
                'templates' => array(
                    'kyc_approved_admin' => array(
                        'label' => 'Admin',
                        'subject' => 'Agent KYC approved: {name}',
                        'heading' => 'Agent KYC approved',
                        'body' => "KYC for {name} is now approved.\n\nEmail: {email}",
                    ),
                    'kyc_approved_customer' => array(
                        'label' => 'Customer / agent',
                        'subject' => 'Your agent KYC is approved',
                        'heading' => 'Your KYC is approved',
                        'body' => "Hello {name},\n\nGood news — your agent KYC has been approved. You can continue listing and managing properties on Coimbatore Properties.",
                    ),
                ),
            ),
            'kyc_rejected' => array(
                'label' => 'KYC rejected',
                'placeholders' => '{name}, {email}, {phone}, {reason}, {site_url}',
                'templates' => array(
                    'kyc_rejected_admin' => array(
                        'label' => 'Admin',
                        'subject' => 'Agent KYC rejected: {name}',
                        'heading' => 'Agent KYC rejected',
                        'body' => "KYC for {name} was rejected.\n\nReason:\n{reason}\n\nEmail: {email}",
                    ),
                    'kyc_rejected_customer' => array(
                        'label' => 'Customer / agent',
                        'subject' => 'Agent KYC not approved — action required',
                        'heading' => 'KYC could not be approved',
                        'body' => "Hello {name},\n\nYour agent KYC submission was reviewed and could not be approved.\n\nReason:\n{reason}\n\nPlease sign in, update your KYC details if needed, and submit again.",
                    ),
                ),
            ),
            'property_submitted' => array(
                'label' => 'Property submitted',
                'placeholders' => '{name}, {owner_name}, {email}, {title}, {property_id}, {listing_url}, {site_url}',
                'templates' => array(
                    'property_submitted_admin' => array(
                        'label' => 'Admin',
                        'subject' => 'New listing submitted: {title}',
                        'heading' => 'New listing submitted',
                        'body' => "{owner_name} submitted a listing for review.\n\nListing #{property_id}: {title}\nOwner email: {email}\n\nPublish or reject it from Property approvals in the admin panel.",
                    ),
                    'property_submitted_owner' => array(
                        'label' => 'Owner / agent',
                        'subject' => 'Listing submitted: {title}',
                        'heading' => 'Listing submitted for review',
                        'body' => "Hello {owner_name},\n\nWe received your listing \"{title}\". It will go live after admin verification. We will email you when it is approved or if changes are needed.",
                    ),
                ),
            ),
            'property_approved' => array(
                'label' => 'Property approved',
                'placeholders' => '{name}, {owner_name}, {email}, {title}, {property_id}, {listing_url}, {site_url}',
                'templates' => array(
                    'property_approved_admin' => array(
                        'label' => 'Admin',
                        'subject' => 'Listing published: {title}',
                        'heading' => 'Listing published',
                        'body' => "Listing \"{title}\" is now live.\n\n{listing_url}",
                    ),
                    'property_approved_owner' => array(
                        'label' => 'Owner / agent',
                        'subject' => 'Your listing is approved: {title}',
                        'heading' => 'Your listing is live',
                        'body' => "Hello {owner_name},\n\nYour listing \"{title}\" has been approved and is now visible on Coimbatore Properties.\n\n{listing_url}",
                    ),
                ),
            ),
            'property_rejected' => array(
                'label' => 'Property rejected',
                'placeholders' => '{name}, {owner_name}, {email}, {title}, {property_id}, {reason}, {site_url}',
                'templates' => array(
                    'property_rejected_admin' => array(
                        'label' => 'Admin',
                        'subject' => 'Listing rejected: {title}',
                        'heading' => 'Listing rejected',
                        'body' => "Listing \"{title}\" was rejected.\n\nReason:\n{reason}\n\nOwner: {owner_name} {email}",
                    ),
                    'property_rejected_owner' => array(
                        'label' => 'Owner / agent',
                        'subject' => 'Your listing was not approved: {title}',
                        'heading' => 'Listing could not be published',
                        'body' => "Hello {owner_name},\n\nYour listing \"{title}\" was reviewed and could not be published.\n\nReason:\n{reason}\n\nPlease update the listing and submit it again, or contact support if you have questions.",
                    ),
                ),
            ),
        );
    }

    public function row_to_array($row)
    {
        if (!$row) {
            return null;
        }
        $key = (string) $row->template_key;
        $meta = $this->parse_system_key($key);
        $event = isset($row->event_key) ? trim((string) $row->event_key) : '';
        $audience = isset($row->audience) ? trim((string) $row->audience) : '';
        $label = isset($row->label) ? trim((string) $row->label) : '';
        if ($event === '' && $meta) {
            $event = $meta['event_key'];
        }
        if ($audience === '' && $meta) {
            $audience = $meta['audience'];
        }
        if ($label === '' && $meta) {
            $label = $meta['label'];
        }
        $events = $this->event_options();
        $audiences = $this->audience_options();
        return array(
            'template_key' => $key,
            'label' => $label !== '' ? $label : $key,
            'event_key' => $event,
            'event_label' => isset($events[$event]) ? $events[$event] : $event,
            'audience' => $audience,
            'audience_label' => isset($audiences[$audience]) ? $audiences[$audience] : $audience,
            'to_email' => isset($row->to_email) ? trim((string) $row->to_email) : '',
            'subject' => (string) $row->subject,
            'heading' => (string) $row->heading,
            'body' => (string) $row->body,
            'is_enabled' => !empty($row->is_enabled) ? 1 : 0,
            'is_system' => !empty($row->is_system) || $meta ? 1 : 0,
        );
    }

    public function get_templates()
    {
        $out = array();
        foreach ($this->list_templates() as $row) {
            $out[$row['template_key']] = array(
                'subject' => $row['subject'],
                'heading' => $row['heading'],
                'body' => $row['body'],
                'is_enabled' => $row['is_enabled'],
            );
        }
        return $out;
    }

    public function list_templates()
    {
        $this->ensure_tables();
        $stored = array();
        if ($this->db->table_exists('nb_mail_templates')) {
            foreach ($this->db->get('nb_mail_templates')->result() as $row) {
                $stored[(string) $row->template_key] = $this->row_to_array($row);
            }
        }
        $out = array();
        $audiences = $this->audience_options();
        foreach ($this->catalog() as $event_key => $group) {
            foreach ($group['templates'] as $key => $def) {
                $row = isset($stored[$key]) ? $stored[$key] : array();
                $audience = 'admin';
                if (substr($key, -9) === '_customer') {
                    $audience = 'customer';
                } elseif (substr($key, -6) === '_owner') {
                    $audience = 'owner';
                }
                $out[$key] = array(
                    'template_key' => $key,
                    'label' => !empty($row['label']) ? $row['label'] : $def['label'],
                    'event_key' => $event_key,
                    'event_label' => $group['label'],
                    'audience' => $audience,
                    'audience_label' => isset($audiences[$audience]) ? $audiences[$audience] : $audience,
                    'to_email' => isset($row['to_email']) ? $row['to_email'] : '',
                    'subject' => !empty($row['subject']) ? $row['subject'] : $def['subject'],
                    'heading' => !empty($row['heading']) ? $row['heading'] : $def['heading'],
                    'body' => isset($row['body']) && $row['body'] !== '' ? $row['body'] : $def['body'],
                    'is_enabled' => array_key_exists('is_enabled', $row) ? (int) $row['is_enabled'] : 1,
                    'is_system' => 1,
                );
                unset($stored[$key]);
            }
        }
        foreach ($stored as $key => $row) {
            $out[$key] = $row;
        }
        return array_values($out);
    }

    public function get_row($key)
    {
        $key = trim((string) $key);
        if ($key === '') {
            return null;
        }
        foreach ($this->list_templates() as $row) {
            if ($row['template_key'] === $key) {
                return $row;
            }
        }
        return null;
    }

    public function templates_to_send($key)
    {
        $primary = $this->get_row($key);
        $out = array();
        if ($primary && !empty($primary['is_enabled'])) {
            $out[] = $primary;
        }
        if (!$primary) {
            return $out;
        }
        foreach ($this->list_templates() as $row) {
            if ($row['template_key'] === $key) {
                continue;
            }
            if (empty($row['is_enabled'])) {
                continue;
            }
            if ($row['event_key'] === $primary['event_key'] && $row['audience'] === $primary['audience'] && empty($row['is_system'])) {
                $out[] = $row;
            }
        }
        return $out;
    }

    public function get_template($key)
    {
        $row = $this->get_row($key);
        if (!$row) {
            return null;
        }
        return array(
            'subject' => $row['subject'],
            'heading' => $row['heading'],
            'body' => $row['body'],
            'is_enabled' => $row['is_enabled'],
        );
    }

    public function save_templates(array $posted)
    {
        foreach ($this->list_templates() as $row) {
            if (empty($row['is_system'])) {
                continue;
            }
            $key = $row['template_key'];
            $block = isset($posted[$key]) && is_array($posted[$key]) ? $posted[$key] : array();
            $this->update_template($key, $block);
        }
        return true;
    }

    public function create_template(array $data)
    {
        $this->ensure_tables();
        $events = $this->event_options();
        $audiences = $this->audience_options();
        $event = isset($data['event_key']) ? trim((string) $data['event_key']) : '';
        $audience = isset($data['audience']) ? trim((string) $data['audience']) : '';
        if (!isset($events[$event]) || !isset($audiences[$audience])) {
            return false;
        }
        $key = 'custom_' . $event . '_' . $audience . '_' . substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
        $row = $this->normalize_save_row($key, $data, $event, $audience, 0);
        $this->db->insert('nb_mail_templates', $row);
        return $key;
    }

    public function update_template($key, array $data)
    {
        $this->ensure_tables();
        $existing = $this->get_row($key);
        if (!$existing) {
            return false;
        }
        $event = $existing['event_key'];
        $audience = $existing['audience'];
        if (empty($existing['is_system'])) {
            $events = $this->event_options();
            $audiences = $this->audience_options();
            $posted_event = isset($data['event_key']) ? trim((string) $data['event_key']) : $event;
            $posted_audience = isset($data['audience']) ? trim((string) $data['audience']) : $audience;
            if (isset($events[$posted_event])) {
                $event = $posted_event;
            }
            if (isset($audiences[$posted_audience])) {
                $audience = $posted_audience;
            }
        }
        $row = $this->normalize_save_row($key, $data, $event, $audience, !empty($existing['is_system']) ? 1 : 0);
        unset($row['template_key']);
        $found = $this->db->get_where('nb_mail_templates', array('template_key' => $key))->row();
        if ($found) {
            $this->db->where('template_key', $key)->update('nb_mail_templates', $row);
        } else {
            $row['template_key'] = $key;
            $this->db->insert('nb_mail_templates', $row);
        }
        return true;
    }

    public function delete_template($key)
    {
        $existing = $this->get_row($key);
        if (!$existing || !empty($existing['is_system'])) {
            return false;
        }
        $this->db->where('template_key', $key)->delete('nb_mail_templates');
        return true;
    }

    private function normalize_save_row($key, array $data, $event, $audience, $is_system)
    {
        $def = array('subject' => '', 'heading' => '', 'body' => '', 'label' => '');
        $catalog = $this->catalog();
        if (isset($catalog[$event]['templates'][$key])) {
            $def = $catalog[$event]['templates'][$key];
        }
        $label = isset($data['label']) ? trim((string) $data['label']) : '';
        if ($label === '') {
            $label = isset($def['label']) ? $def['label'] : $key;
        }
        $to_email = isset($data['to_email']) ? strtolower(trim((string) $data['to_email'])) : '';
        if ($to_email !== '' && !filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            $to_email = '';
        }
        $subject = isset($data['subject']) ? trim((string) $data['subject']) : '';
        $heading = isset($data['heading']) ? trim((string) $data['heading']) : '';
        $body = isset($data['body']) ? (string) $data['body'] : '';
        if ($subject === '' && !empty($def['subject'])) {
            $subject = $def['subject'];
        }
        if ($heading === '' && !empty($def['heading'])) {
            $heading = $def['heading'];
        }
        return array(
            'template_key' => $key,
            'label' => $label,
            'event_key' => $event,
            'audience' => $audience,
            'to_email' => $to_email !== '' ? $to_email : null,
            'subject' => $subject,
            'heading' => $heading,
            'body' => $body,
            'is_enabled' => !empty($data['is_enabled']) ? 1 : 0,
            'is_system' => $is_system ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        );
    }

    private function seed_default_templates()
    {
        if (!$this->db->table_exists('nb_mail_templates')) {
            return;
        }
        foreach ($this->catalog() as $event_key => $group) {
            foreach ($group['templates'] as $key => $def) {
                $audience = 'admin';
                if (substr($key, -9) === '_customer') {
                    $audience = 'customer';
                } elseif (substr($key, -6) === '_owner') {
                    $audience = 'owner';
                }
                $exists = $this->db->get_where('nb_mail_templates', array('template_key' => $key))->row();
                if ($exists) {
                    $patch = array();
                    if ($this->db->field_exists('label', 'nb_mail_templates') && trim((string) $exists->label) === '') {
                        $patch['label'] = $def['label'];
                    }
                    if ($this->db->field_exists('event_key', 'nb_mail_templates') && trim((string) $exists->event_key) === '') {
                        $patch['event_key'] = $event_key;
                    }
                    if ($this->db->field_exists('audience', 'nb_mail_templates') && trim((string) $exists->audience) === '') {
                        $patch['audience'] = $audience;
                    }
                    if ($this->db->field_exists('is_system', 'nb_mail_templates')) {
                        $patch['is_system'] = 1;
                    }
                    if (!empty($patch)) {
                        $this->db->where('template_key', $key)->update('nb_mail_templates', $patch);
                    }
                    continue;
                }
                $this->db->insert('nb_mail_templates', array(
                    'template_key' => $key,
                    'label' => $def['label'],
                    'event_key' => $event_key,
                    'audience' => $audience,
                    'subject' => $def['subject'],
                    'heading' => $def['heading'],
                    'body' => $def['body'],
                    'is_enabled' => 1,
                    'is_system' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
            }
        }
    }
}
