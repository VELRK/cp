<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Forces Coimbatore Properties owner/tenant panel routes to resolve before the global routes table.
 * Fixes 404s when other regex routes (e.g. legacy property URLs) match first or when routing order differs by environment.
 */
class MY_Router extends CI_Router
{

    /**
     * Rebuild URI from REQUEST_URI when CI segments are wrong (RewriteBase / mod_rewrite quirks).
     */
    private function _nb_uri_from_request()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return '';
        }
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = is_string($path) ? $path : '';
        $sn = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '';
        if ($path === '' || $sn === '') {
            return '';
        }
        if (strpos($path, $sn) === 0) {
            $path = (string) substr($path, strlen($sn));
        } elseif (strpos($path, dirname($sn)) === 0) {
            $path = (string) substr($path, strlen(dirname($sn)));
        }
        return trim($path, '/');
    }

    /**
     * URI from REQUEST_URI path only (no SCRIPT_NAME). Strips leading subfolder (property_list/ or property/)
     * so routes match (owner/..., property/{slug}, etc.).
     * Fixes 404 when SCRIPT_NAME is wrong and CI collapses the path to a single segment (e.g. "dashboard" → root Dashboard has no index).
     */
    private function _nb_uri_from_path_only()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return '';
        }
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = is_string($path) ? trim($path, '/') : '';
        if ($path === '') {
            return '';
        }
        // Strip public subfolder first (production: /property_list/..., local dev often: /property/...).
        if (strpos($path, 'property_list/') === 0) {
            $path = (string) substr($path, strlen('property_list/'));
        } elseif (strpos($path, 'property/') === 0) {
            $path = (string) substr($path, strlen('property/'));
        }
        return trim($path, '/');
    }

    /** @return string[] */
    private function _nb_panel_uri_candidates()
    {
        $out = array();
        $primary = strtolower(implode('/', $this->uri->segments));
        $from_ci = strtolower($this->_nb_uri_from_request());
        $from_path = strtolower($this->_nb_uri_from_path_only());
        foreach (array($primary, $from_ci, $from_path) as $s) {
            if ($s !== '') {
                $out[] = $s;
            }
        }
        return array_values(array_unique($out));
    }

    protected function _parse_routes()
    {
        static $nb_panel = null;
        if ($nb_panel === null) {
            $nb_panel = array(
                'property/owner' => 'owner/dashboard/index',
                'property/owner/dashboard' => 'owner/dashboard/index',
                'property/owner/listings' => 'owner/listings/index',
                'property/owner/enquiries' => 'owner/enquiries/index',
                'property/owner/site-visits' => 'owner/site_visits/index',
                'property/owner/property/add' => 'owner/property/add',
                'property/tenant' => 'tenant/dashboard/index',
                'property/tenant/dashboard' => 'tenant/dashboard/index',
                'property/tenant/enquiries' => 'tenant/enquiries/index',
                'owner/dashboard' => 'owner/dashboard/index',
                'owner/listings' => 'owner/listings/index',
                'owner/enquiries' => 'owner/enquiries/index',
                'owner/property/add' => 'owner/property/add',
                'tenant/dashboard' => 'tenant/dashboard/index',
                'tenant/enquiries' => 'tenant/enquiries/index',
            );
        }

        $candidates = array_values(array_unique($this->_nb_panel_uri_candidates()));

        foreach ($candidates as $try) {
            $tl = strtolower($try);
            if (preg_match('#^property/owner/property/edit/(\d+)$#', $tl, $m)) {
                $this->_set_request(array('owner', 'property', 'edit', $m[1]));
                return;
            }
            if (preg_match('#^owner/property/edit/(\d+)$#', $tl, $m)) {
                $this->_set_request(array('owner', 'property', 'edit', $m[1]));
                return;
            }
        }

        foreach ($candidates as $try) {
            if (isset($nb_panel[$try])) {
                $this->_set_request(explode('/', $nb_panel[$try]));
                return;
            }
        }

        parent::_parse_routes();
    }
}
