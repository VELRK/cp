<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CLI-only maintenance (run from project root):
 *   php index.php cli_tool backfill_property_slugs
 */
class Cli_tool extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        if (!$this->input->is_cli_request()) {
            show_404();
        }
        $this->load->database();
    }

    /** Regenerate all nb_properties.slug from title (nb_slugify + unique). */
    public function backfill_property_slugs()
    {
        $this->load->model('Nb_property_model');
        $n = $this->Nb_property_model->backfill_all_slugs_from_titles();
        echo "Updated {$n} property slug(s) from titles.\n";
    }
}
