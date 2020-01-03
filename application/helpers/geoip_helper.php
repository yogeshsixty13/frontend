<?php
/**
 * @package pr_: geoip_hlp 
 * @author Cloudwebs Tech Dev Team
 * @version 1.9
 * @abstract geoip IP detection helper: userful for ccTLD
 * @copyright Perrian Tech
 */


	/** 
	 * @abstract ccTLD: geoIP detect 
	 */
	function redirectGeoIP()
	{
		$CI =& get_instance();
		
		if( isset( $_GET['change_country'] ) )
		{
			$country_code = $CI->input->get('change_country');
			$country_code = strtoupper( $country_code );

			if( !empty($country_code) )
			{
				if( $country_code == 'PERRIAN' )
				{
					//set COUNTRY_CODE CONSTANT
					define( 'COUNTRY_CODE',  'IN' );		
				}
				else
				{
					//set COUNTRY_CODE CONSTANT
					define( 'COUNTRY_CODE', $country_code );		
				}
				return true;	
			}
		}
		else if( strpos( $_SERVER['REQUEST_URI'], 'checkout/paypalNotify') !== FALSE )
		{
			//allow direct access to IPN listner
			return true; 	
		}
		
		// include the php script
		include( BASE_DIR."application/libraries/geoip.inc");
		 
		// open the geoip database
		$gi = geoip_open( BASE_DIR."application/libraries/GeoIP/GeoIP.dat", GEOIP_STANDARD);
	
		// to get country code
		$country_code = strtoupper( geoip_country_code_by_addr($gi, $_SERVER['REMOTE_ADDR']) );

		// to get country name
		$country_name = geoip_country_name_by_addr($gi, $_SERVER['REMOTE_ADDR']);
	
		// close the database
		geoip_close($gi);

		//set COUNTRY_CODE CONSTANT
		define( 'COUNTRY_CODE', $country_code );		
			
		//echo $country_code."  ".$country_name."<br>";
					
		if( $country_code == 'AU' || $country_code == 'NZ' )
		{
			if( strpos( $_SERVER['HTTP_HOST'], ".com.au" ) === FALSE )
			{
				ip_redirect( $country_code );
			}
		}
		else
		{
			if( $_SERVER['HTTP_HOST'] != "www.perrian.com" )
			{
				ip_redirect( 'PERRIAN' );	
			}
		}

	}
	
	function ip_redirect( $country_code )
	{
		if( $country_code == 'PERRIAN' )
		{
			header( 'Location: http://www.perrian.com/' );
			exit;
		}
		else if( $country_code == 'AU' || $country_code == 'NZ' )
		{
			header( 'Location: http://www.perrian.com.au/' );
			exit;
		}
	}

?>