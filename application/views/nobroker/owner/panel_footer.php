<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php
$nb_load_maps = !empty($load_maps);
$CI =& get_instance();
$nb_gmaps_key = $CI->config->item('google_maps_api_key');
if ($nb_load_maps && !empty($nb_gmaps_key)) :
?>
<script src="<?php echo base_url('assets/js/nb_maps.js'); ?>"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo rawurlencode($nb_gmaps_key); ?>&amp;libraries=places&amp;callback=initNbAutocomplete"></script>
<?php endif; ?>
</body>
</html>
