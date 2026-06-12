<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public URL POST api/property/save — cannot live only in controllers/api/Property.php
 * because controllers/Api.php shadows the api/ directory for URIs starting with "api/".
 *
 * @see application/controllers/Api.php (cities() comment)
 */
require_once APPPATH . 'controllers/api/Property.php';

class Nb_property_form extends Property {

}
