<?php


function atoll( $atts ) {
?>
<div class="calc_container" id="calc_container">
<form name="rate_calculator" id="rate_calculator" method="post" action="">
<table class="rate_calculator">
<tr><td>Source Address</td><td><input type="text" name="source_address" id="source_address" class="source_address"/></td></tr>
<tr><td>Destination Address</td><td><input type="text" name="destination_address" id="destination_address" class="destination_address"/></td></tr>
<tr><td colspan="2"><input type="button" name="calculate_rates" value="Calculate Pricing" onclick="get_distance_and_rates();"/></td></tr>
</table>
</form>
</div>
<?php
}

add_shortcode( 'ratecalculator', 'atoll' );


function get_latlong($args){
		$address = urlencode($args);
		$url = "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response);
		$arr = array();
		$arr['lat'] = $response_a->results[0]->geometry->location->lat;
		$arr['long'] = $response_a->results[0]->geometry->location->lng;
		return $arr;
}



function distance($lat1, $lon1, $lat2, $lon2, $unit) 
{ 
   $theta = $lon1 - $lon2; 
   $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
   $dist = acos($dist); 
   $dist = rad2deg($dist); 
   $miles = $dist * 60 * 1.1515;
   $unit = strtoupper($unit);

   if ($unit == "K") 
   {
      return ($miles * 1.609344); 
   } 
   else 
   {
      return $miles;
   }
}



function get_total_rates($distance){
global $wpdb;

$query = "select * from ".$wpdb->prefix . "geo_ip_rates where ".$distance." >= start_km and ".$distance." <= range_km limit 1";

$result = $wpdb->get_results($query,OBJECT);

return $result;



}



?>