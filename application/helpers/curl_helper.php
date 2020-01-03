<?php
/**
 * @package pr_: curl_hlp 
 * @author Cloudwebs Tech Dev Team
 * @version 1.9
 * @abstract curl helper
 * @copyright Perrian Tech
 */


/**
 * @author Cloudwebs
 * @param $error is an array of error to be used in case of error and is called by reference
 */
	function curl_post( $url, $postfields, $headerA=array(), $is_return_info=false, &$error, $conn_timeout=60)
	{
		$post_string = '';
		if( is_array($postfields) )
		{
			//generate poststring for curl
			foreach( $postfields as $k=>$ar )	
			{
				$post_string .= $k."=".$ar."&";
			}
			
			$post_string = substr( $post_string, 0, -1);
		}
		else
		{
			$post_string = $postfields;	
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_URL,  $url);

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

		//set special headers if specified
		if( is_array( $headerA ) && sizeof($headerA) > 0 )
		{
			foreach( $headerA as $k=>$ar )	
			{
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
			}
		}
				
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$response = curl_exec($ch);
		
		//get info if specified
		if( $is_return_info )
		{
			$error['info'] = curl_getinfo($ch, CURLINFO_HEADER_OUT);	
		}
		
		//cURL error
		if (curl_errno($ch) != 0) // cURL error
		{
			$error['error'] = curl_error($ch);
		} 
		
		curl_close($ch);
		
		return  $response;
	}

/*
++++++++++++++++++++++++++++++++++++++++++++++
	This function will call any url using php
	curl and return result as string.
	@params : pass any url Ex. http://www.google.com
++++++++++++++++++++++++++++++++++++++++++++++
*/
	function call_url($url)
	{
		$ch = curl_init ($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
		$out = curl_exec($ch);
		curl_close($ch);
		
		return  $out;
	}
	


?>