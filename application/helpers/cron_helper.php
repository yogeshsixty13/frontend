<?php

/**
 * @abstract get html contents from url for scrapping
 */
function getContents( $url ) 
{
	$url = cleanUrl( $url );
	// Create a DOM object from a URL: simple html dom
	for( $i=0; $i<5; $i++ )
	{
		//$via_proxy= array( "http" => array( "proxy" =>getProxyIp(), "request_fulluri" => true ) );
		//$stream_context_create = stream_context_create( $via_proxy );

		$html = file_get_html( $url );
		if( !empty( $html ) )
		{
			return $html;	
		}
	}
	
	return false;
}	

/**
 * @abstract cleanUrl
 */
function cleanUrl( $url ) 
{
	return str_replace( array( "amp;", "&&&", "&&" ), array( "&", "&", "&" ), $url );
}	

/**
 * @abstract getProxyIp
 */
function getProxyIp() 
{
	$proxyIpPortArr = array( 0=>"181.48.190.162:8080", 1=>"112.142.128.172:80", 2=>"162.243.207.193:80", 3=>"182.74.60.26:3128", 
							 4=>"162.243.64.60:80", 5=>"185.25.209.38:8080", 6=>"169.199.19.136:8080", 7=>"67.210.125.135:80", 8=>"129.21.39.87:80", 
							 9=>"162.243.207.219:80", 10=>"118.175.93.160:80" );
	return $proxyIpPortArr[ rand( 0, 10 ) ]; 
}	

function is_url_exist( $url ) 
{
	$status = false; 
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	echo $code; die; 
	if( $code == 200 ) 
	{
		$status = true;
	}
	else
	{
		$status = false;
	}
	
	curl_close($ch);
	return $status;
}

/**
 * @abstract not a standard implementation: but help in detecting franchise name out of dealerhip name
 */
function getFranchiseName( $dealerhip_name )
{
	$res = executeQuery( " SELECT brand_id, brand_name FROM search_tbl_brands WHERE 1 " );
	foreach( $res as $k=>$ar )
	{
		if( stripos( $dealerhip_name, $ar["brand_name"] ) !== FALSE )
		{
			return $ar;	
		}
	}

	return array( "brand_id"=> 0, "brand_name"=>"Independent Dealer" );	
}

/**
 * @abstract not a standard implementation: but help in detecting franchise name out of dealerhip name
 */
function getModelId( $ad_title )
{
	$res = executeQuery( " SELECT model_id, model_name FROM search_tbl_car_models WHERE 1 " );
	foreach( $res as $k=>$ar )
	{
		if( stripos( $ad_title, $ar["model_name"] ) !== FALSE )
		{
			return $ar["model_id"];	
		}
	}

	return 0;	
}

/**
 * @abstract not a standard implementation: but help in detecting car year out of car title
 */
function filterTitle( $ad_title )
{
	return str_ireplace( array( "AutoTrader.com", "AutoTrader" ), "", $ad_title );
}

/**
 * @abstract not a standard implementation: but help in detecting car year out of car title
 */
function getYearFromTitle( $ad_title )
{
	if( !empty( $ad_title ) )
	{
		$arr = explode( " ", trim( $ad_title ) );
		if( !empty( $arr[0] ) && is_numeric( trim( $arr[0] ) ) )
		{
			return trim( $arr[0] );	
		}
		else if( !empty( $arr[1] ) && is_numeric( trim( $arr[1] ) ) )
		{
			return trim( $arr[1] );	
		}
		else
		{
			return 0;
		}
	}
	else
	{
		return 0;
	}
}

/**
 * @abstract not a standard implementation: but help in detecting car year out of car title
 */
function saveAdProperties( $ad_id, $uas_specificationArr, $dataAd ) 
{
	$CI =& get_instance(); 
	if( !empty( $ad_id ) ) 
	{
		$dataAdProperties = array();
		$dataAdProperties["ad_id"] = $ad_id;
		$dataAdProperties["ad_abs"] = "";
		$dataAdProperties["ad_brakes"] = "";
		$dataAdProperties["ad_drivetrain"] = $dataAd[ "ad_driver" ];
		$dataAdProperties["ad_engine_size"] = "";
		$dataAdProperties["ad_engine_type"] = isset( $uas_specificationArr["engine_type"] ) ? $uas_specificationArr["engine_type"] : "";
		$dataAdProperties["ad_engine_type_other"] = "";
		$dataAdProperties["ad_engine_properties"] = "";
		$dataAdProperties["ad_front_suspension"] = "";
		
		$tempArr = explode( "@", ( isset( $uas_specificationArr["horsepower"] ) ? $uas_specificationArr["horsepower"] : "" ) );
		$dataAdProperties["ad_horse_power"] = isset( $tempArr[0] ) ? $tempArr[0] : "";
		$dataAdProperties["ad_horse_power_rpm"] = isset( $tempArr[1] ) ? $tempArr[1] : "";
		
		$dataAdProperties["ad_fuel"] = ""; //$uas_specificationArr["fuel_capacity"];
		$dataAdProperties["ad_opt_engine_1"] = isset( $uas_specificationArr["engine_type"] ) ? $uas_specificationArr["engine_type"] : "";
		$dataAdProperties["ad_opt_engine_2"] = isset( $uas_specificationArr["horsepower"] ) ? $uas_specificationArr["horsepower"] : "";
		$dataAdProperties["ad_opt_engine_3"] = isset( $uas_specificationArr["torque_(lb_ft)"] ) ? $uas_specificationArr["torque_(lb_ft)"] : "";
		$dataAdProperties["ad_opt_transm_1"] = $dataAd[ "ad_transmission" ];
		$dataAdProperties["ad_opt_transm_2"] = "";
		$dataAdProperties["ad_opt_transm_3"] = "";
		$dataAdProperties["ad_trans_type"] = $dataAd[ "ad_transmission" ];

		$dataAdProperties["ad_trans_gear"] = "";
		$dataAdProperties["ad_trans_gear_other"] = "";
		$dataAdProperties["ad_power_steering"] = "";
		$dataAdProperties["ad_rear_suspension"] = "";
		$dataAdProperties["ad_torque"] = isset( $uas_specificationArr["torque_(lb_ft)"] ) ? $uas_specificationArr["torque_(lb_ft)"] : "";
		$dataAdProperties["ad_transmission"] =  $dataAd[ "ad_transmission" ];
		$dataAdProperties["ad_rear_tires"] = "";
		$dataAdProperties["ad_tires"] = isset( $uas_specificationArr["tires"] ) ? $uas_specificationArr["tires"] : "";
		
		$dataAdProperties["ad_wheel_type"] = "";
		$dataAdProperties["ad_alarm_system"] = isset( $uas_specificationArr["alarm_system"] ) ? $uas_specificationArr["alarm_system"] : "";
		$dataAdProperties["ad_driver_airbag"] = isset( $uas_specificationArr["driver_air_bag"] ) ? $uas_specificationArr["driver_air_bag"] : "";
		$dataAdProperties["ad_knee_airbags"] = "";
		$dataAdProperties["ad_passenger_airbag"] = isset( $uas_specificationArr["passenger_air_bag"] ) ? $uas_specificationArr["passenger_air_bag"] : "";
		$dataAdProperties["ad_rear_child_locks"] = isset( $uas_specificationArr["child_safety_locks"] ) ? $uas_specificationArr["child_safety_locks"] : "";
		$dataAdProperties["ad_roll_stability_control"] = "";
		$dataAdProperties["ad_side_airbags"] = isset( $uas_specificationArr["side_head_air_bag"] ) ? $uas_specificationArr["side_head_air_bag"] : "";
		
		
		$dataAdProperties["ad_side_curtains"] = isset( $uas_specificationArr["rear_side_curtain_airbags"] ) ? $uas_specificationArr["rear_side_curtain_airbags"] : "";
		$dataAdProperties["ad_stability_control"] = "";
		$dataAdProperties["ad_traction_control"] = isset( $uas_specificationArr["traction_control"] ) ? $uas_specificationArr["traction_control"] : "";
		$dataAdProperties["ad_driver_power_seat"] = "";
		$dataAdProperties["ad_folding_rear_seats"] = "";
		$dataAdProperties["ad_front_seat_type"] = "";
		$dataAdProperties["ad_heated_seats"] = isset( $uas_specificationArr["heated_driver_seat"] ) ? $uas_specificationArr["heated_driver_seat"] : "";
		$dataAdProperties["ad_trim"] = "";
		
		$dataAdProperties["ad_adjustable_steering"] = "";
		$dataAdProperties["ad_air_conditioning"] = isset( $uas_specificationArr["air_conditioning"] ) ? $uas_specificationArr["air_conditioning"] : "";
		$dataAdProperties["ad_auxiliary_rear_air_conditioning"] = "";
		$dataAdProperties["ad_cruise_control"] = "";
		$dataAdProperties["ad_power_locks"] = isset( $uas_specificationArr["power_door_locks"] ) ? $uas_specificationArr["power_door_locks"] : "";
		$dataAdProperties["ad_power_mirrors"] = isset( $uas_specificationArr["power_driver_mirror"] ) ? $uas_specificationArr["power_driver_mirror"] : "";
		$dataAdProperties["ad_power_windows"] = isset( $uas_specificationArr["power_windows"] ) ? $uas_specificationArr["power_windows"] : "";
		$dataAdProperties["ad_rear_window_defroster"] = "";
		
		$dataAdProperties["ad_remote_keyless_entry"] = "";
		$dataAdProperties["ad_sunroof"] = isset( $uas_specificationArr["sunroof"] ) ? $uas_specificationArr["sunroof"] : "";
		$dataAdProperties["ad_tachometer"] = "";
		$dataAdProperties["ad_am_fm_radio"] = "";
		$dataAdProperties["ad_with_cassette"] = isset( $uas_specificationArr["engine_type"] ) ? $uas_specificationArr["engine_type"] : "";
		$dataAdProperties["ad_with_cd"] = "";
		$dataAdProperties["ad_with_cd_charger"] = "";
		$dataAdProperties["ad_auto_transmission_city"] = "";
		
		
		$dataAdProperties["ad_manual_transmission_city"] = "";
		$dataAdProperties["ad_curbweight"] = isset( $uas_specificationArr["curb_weight"] ) ? $uas_specificationArr["curb_weight"] : "";
		$dataAdProperties["ad_fuel_tank_capacity"] = isset( $uas_specificationArr["fuel_capacity"] ) ? $uas_specificationArr["fuel_capacity"] : "";
		$dataAdProperties["ad_gross_vehicle_weight"] = "";
		$dataAdProperties["ad_height_mm"] = isset( $uas_specificationArr["height"] ) ? $uas_specificationArr["height"] : "";
		$dataAdProperties["ad_length_mm"] = "";
		$dataAdProperties["ad_max_trunk_size"] = "";
		$dataAdProperties["ad_towing_capacity"] = "";
		
		$dataAdProperties["ad_trunk_size"] = "";
		$dataAdProperties["ad_turning_circle"] = "";
		$dataAdProperties["ad_wheelbase"] = isset( $uas_specificationArr["wheelbase"] ) ? $uas_specificationArr["wheelbase"] : "";
		$dataAdProperties["ad_width_mm"] = "";
		$dataAdProperties["ad_general_years_km"] = "";
		$dataAdProperties["ad_perforation_corrosion"] = "";
		$dataAdProperties["ad_powertrain"] = "";
		$dataAdProperties["ad_roadside_assistance"] = "";
		
		$dataAdProperties["ad_acceleration"] = "";
		$dataAdProperties["ad_max_speed"] = "";
		$dataAdProperties["ad_auto_transmission"] = "";
		$dataAdProperties["ad_cvt_transmission"] = "";
		$dataAdProperties["ad_manual_transmission"] = "";
		$dataAdProperties["ad_sequential_transmission"] = "";
		$dataAdProperties["ad_rear_window"] = "";
		$dataAdProperties["ad_rollbar"] = "";
		
		$dataAdProperties["ad_roof_type"] = "";
		$dataAdProperties["ad_wind_deflector"] = "";

		//ad_property_id
		$ad_property_id = getField( "ad_property_id", "search_tbl_ad_properties", "ad_id", $ad_id );
		if( empty( $ad_property_id ) )
		{
			$CI->db->insert( "search_tbl_ad_properties", $dataAdProperties );
			$ad_property_id = $CI->db->insert_id();
		}
		else
		{
			$CI->db->set('ap_modified_date', 'NOW()', FALSE);
			$CI->db->where( "ad_property_id", $ad_property_id )->update( "search_tbl_ad_properties", $dataAdProperties );
		}

		return $ad_property_id;
	}
	else
	{
		return 0;
	}
}

/**
 * @abstract will return state name as per in mynextauto from autotrader name
 */
function getStateNameFrAtName( $state_name, $country_id = 10 )
{
	$row = checkIfRowExist( " SELECT county_id, county_name FROM tbl_states WHERE county_key='US-".$state_name."' " );
	if( !empty( $row ) )
	{
		return $row;	
	}
	else
	{
		return array( "county_id" => 0, "county_name" => $state_name );	
	}
}
/*
* Function will update cron que status
*/
function updateCronQueStatus($cron_que_id)
{
	$CI =& get_instance();
	
	$CI->db->set('cq_modified_date', 'NOW()', FALSE);
	$CI->db->where( "cron_que_id", $cron_que_id )->update( "cron_que", array("cq_status"=>1) );
}

/**
 * @abstract returns PTA/PTO member listing table part from PTA document
 */
function getPTA_OListTable( $doc_text )
{
	$PTA_PTOIndentifier = getPTA_PTOIndentifier(); 
	foreach( $PTA_PTOIndentifier as $k=>$ar )
	{
		if( isContains( $doc_text, $ar ) )
		{
			return fetchLastSubStr( $doc_text, $ar, "" );
		}
	}

	return "";
}

function getPTA_PTOIndentifier()
{
	return array( 'Board Officers', 'Board Members', 'PTA Officers', 'PTA Members', 'PTO Officers', 'PTO Members' );
}

/**
 * on create 05-08-2016
 * Gautam Kakadiya
 * download image from other location like dropbox, etc....
 */
function getImageFromOtherLocation( $image_name, $image_url, $is_debug=false )
{
	require_once APPPATH.'libraries/simple_html_dom.php';
	
// 	usleep( rand( 100, 250 ));
	
	$image_url = trim($image_url);
	$cookie_string = "Cookie: locale=en;
							  gvc=MTk0MDcwNTc3ODc1MzExMzE3NzQ2ODA2MDkzNTQ0ODYwMDM5NTg2; 
							  seen-sl-signup-modal=VHJ1ZQ%3D%3D; 
							  t=d0u6rJgsncVkPit1VAyafqKY; 
							  __Host-js_csrf=d0u6rJgsncVkPit1VAyafqKY; 
							  seen-sl-download-modal=VHJ1ZQ%3D%3D; 
							 _ga=GA1.2.1585029359.".time()."; 
							 preauth=;
							 _dc_gtm_UA-279179-2=1";

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, ($image_url) );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 120 );
	curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)" );
	curl_setopt( $ch, CURLOPT_REFERER, "http://google.com" );
	curl_setopt( $ch, CURLOPT_CAINFO, APPPATH . 'libraries/fb_ca_chain_bundle.crt' );
	curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie_string );
	curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie_string );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_HEADER, 1 );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

	$result = curl_exec($ch);

	if ($result === false)
	{
		$e = new Exception(curl_error($ch), curl_errno($ch));
		curl_close($ch);
		//throw $e;
		
		if( $is_debug )
		{
			echo "curl error ".$e->getMessage()."<br>";
		}
		
		curl_close($ch);
		return false;
	}

	curl_close($ch);

// 	$dop_images = fetchSubStr( $result, "React.createElement(react_component, ", "), elt);");

	/**
	 * Gautam Kakadiya 29-11-2016
	 * Implement new concept
	 */
	$htmlDom = str_get_html( $result );
	$dop_images_tag = "";
	$drop_images;
	
	if( is_object($htmlDom) )
	{
		$dop_images_tag = $htmlDom->find('img[class=absolute-center]', 0);
		$drop_images = fetchSubStr( $dop_images_tag, 'src="', '"');
	}
	else 
	{
		if( $is_debug )
		{
			//echo "drop box invalid html result ".$result."<br>";
			echo "drop box invalid html result<br>";
		}
		
		if( strpos($image_url, "dl=0") !== FALSE )
		{
			$drop_images = str_replace("dl=0", "dl=1", $image_url); 
		}
	}
	
	if( $is_debug )
	{
		echo "dop_images_tag ".$dop_images_tag." 	drop_images ".$drop_images."<br>";
	}
	
	$save_url = '';
	
	if( !empty($drop_images) )		//if( !empty($dop_images_tag) )	//if( !isEmptyArr($dop_images) )
	{
		
		
// 		$htmlDom = json_decode( $dop_images );
		
		$folder = BASE_DIR . '/assets/importdata/product/'.$image_name;
		if( !file_exists($folder) )
		{
			mkdir($folder, 0777, true);
// 			echo "Success folder create: ".$image_name."<br>";
		}
		
		//get image from url
// 		$content = file_get_contents(str_replace("size=32x32", "size=1024x1024", $htmlDom->files[0]->preview_url));
		$content = file_get_contents($drop_images);
		
		
		//save image from given url
		$save_url = $folder."/".time().".jpg";
		file_put_contents( $save_url, $content);
		
	}
	
// 	usleep(rand(100, 200));
// 	return str_replace( BASE_DIR."/", "", $save_url );
	return true; 
}
?>