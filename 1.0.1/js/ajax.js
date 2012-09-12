function get_distance_and_rates(){

var data = jQuery('#rate_calculator').serialize();
	data += '&action=address-submit';
	jQuery.post(RateCalc.ajaxurl, data, function(response) {
			var obj = jQuery.parseJSON(response);
			jQuery('#rate_calc_container').remove();
			if(obj.calc_response_code==200 && obj.total_amount != null && obj.rate_per_km != null){
				jQuery('#rate_calc_container').remove();
	jQuery('#calc_container').append('<div id="rate_calc_container"><span>Distance Approx : '+ obj.total_distance +'</span><br><span> Rate/KM : $ '+ obj.rate_per_km  +'</span><br><span>Total Amount Approx : $ '+ obj.total_amount +'</span></div>')	;
			}			
	});
}