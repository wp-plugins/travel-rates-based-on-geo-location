<?php   
/* 
Plugin Name: Travel Rates based on geo location
Plugin URI: http://www.indiainfotech.com 
Description: Rates calculator based on source and destination address
Author: C. Developer
Version: 1.0 
Author URI: http://www.indiainfotech.com 
*/  


include_once('common.php');
wp_enqueue_script( 'rates-distance-calculator', plugin_dir_url( __FILE__ ) . 'js/ajax.js', array( 'jquery' ) );
wp_localize_script( 'rates-distance-calculator', 'RateCalc', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );


add_action('admin_menu', 'address_lat_long_menu');



add_action( 'wp_ajax_nopriv_address-submit', 'get_distance_and_rates' );
add_action( 'wp_ajax_address-submit', 'get_distance_and_rates' );




function get_distance_and_rates() {    
	global $wpdb;	
	$source = $_POST['source_address'];		
	$destination = $_POST['destination_address'];

	// Getting the latitude an longitude of the source and the destination
	$s_latlong = get_latlong($source);
	$d_latlong = get_latlong($destination);
	
	$distance_in_km = ceil(distance($s_latlong['lat'],$s_latlong['long'],$d_latlong['lat'],$d_latlong['long'],'K'));
	
	if($distance_in_km > 0 and $distance_in_km < 5000){
		$rates_object = get_total_rates($distance_in_km);
		$rates_per_km = $rates_object[0]->rates;
		// Calculate the total amount which will be applied for the distance
		$total_amount = number_format(ceil($distance_in_km * $rates_per_km),2,'.',',');
		$response_array = array('calc_response_code'=>200,'total_distance'=>$distance_in_km,'rate_per_km'=>$rates_per_km,'total_amount'=>$total_amount);
	}else{
		$response_array = array('calc_response_code'=>400);
	}
		
		

    $response = json_encode($response_array);

//    header( "Content-Type: application/json" );
    print_r($response);
    exit;
}




define( 'DISTANCE_RATES_CALCULATOR_PATH', plugin_dir_path(__FILE__) );

function address_lat_long_menu() {

   add_menu_page('Set Rates', 'Set Rates', 'administrator', 'Set Rates', 'distance_rates','', 66);
}



function distance_rates(){
global $wpdb;


if(isset($_POST['add_km'])){
	$skm = $_POST['skm'];
	$ekm = $_POST['ekm'];
	$rates = $_POST['rate'];	
	$sql = "insert into ".$wpdb->prefix . "geo_ip_rates values('','$skm','$ekm','$rates')";
	$wpdb->query($sql);
}

if(isset($_POST['del_km'])){
	$id = $_POST['del_id'];
	$sql = "delete from ".$wpdb->prefix . "geo_ip_rates where id='$id'";
	$wpdb->query($sql);
}




	$form = '<h2>Set Rates for the distances</h2><h3>Just use [ratecalculator] shortcode in post , page or widget.</h3><br>e.g. 0km - 50km : $10 / km<br /><form name="distance_calculator" id="distance_calculator" action="" method="post">
	<table width="70%" border=1 cellspacing=0 cellpadding=15><tr><th>Serial No</th><th>Start KM</th><th>End KM</th><th>Rates/km</th><th>Action</th></tr>
	<tr><th>&nbsp;</th><th><input type="text" name="skm" size="20" ></th><th><input type="text" name="ekm" size="20" ></th><th><input type="text" name="rate" 
	size="20" ></th><th><input type="submit" name="add_km" value="Add"></th></tr>';


	$sql = "select * from ".$wpdb->prefix . "geo_ip_rates";
	$results = $wpdb->get_results($sql,OBJECT);
$ctr = 0;
foreach($results as $row){
		$ctr++;
		$form.='<tr><th>'.$ctr.'</th><th>'.$row->start_km.' km</th><th>'.$row->range_km.' km</th><th>$ '.$row->rates.'</th><th><input type="hidden" name="del_id" 
		value="'.$row->id.'"/><input type="submit" name="del_km" value="Delete"></th></tr>';
}

$form.='</table></form>';

echo $form;
}	

		
function geo_ip_rates(){
global $wpdb;
$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "geo_ip_rates (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start_km` smallint(6) NOT NULL,
  `range_km` smallint(6) NOT NULL,
  `rates` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$wpdb->query($sql);
}


register_activation_hook(__FILE__,'geo_ip_rates');

add_action('wp_ajax_my_unique_action','calc_rates',50,3);

add_filter('widget_text', 'do_shortcode');		
?>