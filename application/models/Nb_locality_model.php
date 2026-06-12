<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_locality_model extends CI_Model {

    protected $table = 'nb_localities';

    public function by_city($city_id)
    {
        return $this->db->where('city_id', (int) $city_id)
            ->order_by('name', 'ASC')
            ->get($this->table)
            ->result();
    }
}
