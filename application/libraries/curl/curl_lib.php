<?php

//INCLUDE Libs
require_once 'curl.php';

//created to get abstraction layer for fetch system(Fetching content from web)



/**

* @author Cloudwebs

* @abstract function get header html from url

* @return Ambigous <multitype:, multitype:NULL string Ambigous <The, mixed> >

*/

function fetchHead($target, $port, $fsocket_timeout, $IsSslB = false)
{

	$OptionsA = array( CURLOPT_URL => $target, CURLOPT_PORT => $port, CURLOPT_TIMEOUT => $fsocket_timeout );

	if( $IsSslB )

	{ $OptionsA[CURLOPT_SSL_VERIFYPEER]=false; $OptionsA[CURLOPT_SSL_VERIFYHOST]=false; }



	return curlGet( $OptionsA, 1);

}



/**

* @author Cloudwebs

* @abstract function get header html from url

* @return Ambigous <multitype:, multitype:NULL string Ambigous <The, mixed> >

*/

function fetchUrlHtml($url)

{

	//will no more used: 3/1/2014

	return curlGet( array( CURLOPT_URL=>$url, CURLOPT_PORT => null,

			CURLOPT_HEADER => null, CURLOPT_NOBODY => null, 
			
			CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 2 ), 1);

}

/**
 * @author Cloudwebs
 * @abstract Get file content with curl
 * @param $RequestI 1 for full string, 2 for direct output to screen, 3 for content type, 4 for CURLINFO_HTTP_CODE
 */
function curlGet($ConfigA, $RequestI)
{	
	$ch = CURL::get_CURLinstance();	$resArr = array();


	//set all passed configuration option
	$ch->setOptionArray($ConfigA);

	$response = $ch->exec();

	//return as per request
	if($RequestI == 1)
	{
		$resArr['answer'] = $response;
	}
	else if($RequestI == 3)
	{
		$resArr['answer'] = $ch->get_info( CURLINFO_CONTENT_TYPE );
	}
	else if($RequestI == 4)
	{
		$resArr['answer'] = $ch->get_info( CURLINFO_HTTP_CODE );
	}

	if( !$resArr['answer'] )
	{
		$resArr = array_merge($resArr, $ch->get_error_info());
	}

	return $resArr;
}

// function saveImageFromUrl( $url, $folder="", $subdomain="" )
// {
// 	$dir_path = 'assets/'.$folder; 
	
// 	if(!is_dir('./'.$dir_path))
// 		mkdir('./assets/'.$folder);
		
// 	$subdomain = ($subdomain) ? $subdomain."/" : '';
	
// 	$allowed_ext = array( 'jpg', 'jpeg', 'png' );
// 	$pos = strrpos( $url, "." );
// 	if( $pos !== FALSE )
// 	{
// 		$ext = substr( $url, $pos + 1 );
// 		if( in_array( $ext, $allowed_ext ) )
// 		{
// 			$path = $dir_path.'/'.time().'_'.rand( 1000, 100000 ).'.'.$ext;
// 			$img = dirname( BASE_DIR ). "/".$subdomain . $path;	//upload to main domain from basedomain product
// 			file_put_contents( $img, file_get_contents( $url ) );
// 		}
// 	}
// 	return $path;
// }



?>