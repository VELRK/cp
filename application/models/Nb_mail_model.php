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
                    `subject` VARCHAR(255) NOT NULL DEFAULT '',
                    `heading` VARCHAR(255) NOT NULL DEFAULT '',
                    `body` TEXT NULL,
                    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`template_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
        }
        $this->seed_default_templates();
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

    public function get_templates()
    {
        $this->ensure_tables();
        $stored = array();
        if ($this->db->table_exists('nb_mail_templates')) {
            foreach ($this->db->get('nb_mail_templates')->result() as $row) {
                $stored[(string) $row->template_key] = array(
                    'subject' => (string) $row->subject,
                    'heading' => (string) $row->heading,
                    'body' => (string) $row->body,
                    'is_enabled' => !empty($row->is_enabled) ? 1 : 0,
                );
            }
        }
        $out = array();
        foreach ($this->catalog() as $group) {
            foreach ($group['templates'] as $key => $def) {
                $row = isset($stored[$key]) ? $stored[$key] : array();
                $out[$key] = array(
                    'subject' => isset($row['subject']) && $row['subject'] !== '' ? $row['subject'] : $def['subject'],
                    'heading' => isset($row['heading']) && $row['heading'] !== '' ? $row['heading'] : $def['heading'],
                    'body' => isset($row['body']) && $row['body'] !== '' ? $row['body'] : $def['body'],
                    'is_enabled' => array_key_exists('is_enabled', $row) ? (int) $row['is_enabled'] : 1,
                );
            }
        }
        return $out;
    }

    public function get_template($key)
    {
        $all = $this->get_templates();
        return isset($all[$key]) ? $all[$key] : null;
    }

    public function save_templates(array $posted)
    {
        $this->ensure_tables();
        $catalog = $this->catalog();
        foreach ($catalog as $group) {
            foreach ($group['templates'] as $key => $def) {
                $block = isset($posted[$key]) && is_array($posted[$key]) ? $posted[$key] : array();
                $row = array(
                    'template_key' => $key,
                    'subject' => isset($block['subject']) ? trim((string) $block['subject']) : $def['subject'],
                    'heading' => isset($block['heading']) ? trim((string) $block['heading']) : $def['heading'],
                    'body' => isset($block['body']) ? (string) $block['body'] : $def['body'],
                    'is_enabled' => !empty($block['is_enabled']) ? 1 : 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                );
                if ($row['subject'] === '') {
                    $row['subject'] = $def['subject'];
                }
                if ($row['heading'] === '') {
                    $row['heading'] = $def['heading'];
                }
                $exists = $this->db->get_where('nb_mail_templates', array('template_key' => $key))->row();
                if ($exists) {
                    $this->db->where('template_key', $key)->update('nb_mail_templates', $row);
                } else {
                    $this->db->insert('nb_mail_templates', $row);
                }
            }
        }
        return true;
    }

    private function seed_default_templates()
    {
        if (!$this->db->table_exists('nb_mail_templates')) {
            return;
        }
        $count = (int) $this->db->count_all('nb_mail_templates');
        if ($count > 0) {
            return;
        }
        foreach ($this->catalog() as $group) {
            foreach ($group['templates'] as $key => $def) {
                $this->db->insert('nb_mail_templates', array(
                    'template_key' => $key,
                    'subject' => $def['subject'],
                    'heading' => $def['heading'],
                    'body' => $def['body'],
                    'is_enabled' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
            }
        }
    }
}
