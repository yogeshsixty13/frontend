<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class home extends CI_Controller 
{
	//parent constructor will load model inside it
	function __construct()
	{
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
		parent::__construct();
// 		require_once APPPATH.'libraries/simple_html_dom.php';
// 		require_once APPPATH.'libraries/curl/curl_lib.php';
// 		require_once APPPATH.'libraries/cacert.pem';

		$token = $this->session->userdata('token');
		if( $token == "" )
		{
		    checkAuthentication();
		}
	}
	
	function index()
	{
// 	    $url = "https://bend.wowtasks.com/jobs/all/list/";
// 	    $ch = curl_init($url);
// 	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
// 	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    
// 	    $result = curl_exec($ch);
// 	    if(curl_errno($ch)){
// 	        echo 'Request Error:' . curl_error($ch);
// 	    }
	    
	    $data = array();
	    $data['pageName'] = 'find_jobs';
	    $this->load->view('site-layout',$data);
	}
	
	
	function test()
	{
// 	    $data = getTaskChatHistoryData( "dc6921b4-4e1a-4b11-a654-f6841bcb33db/" );
// 	    pr( $data );die;
	    $url = "https://bend.wowtasks.com/jobs/mybids/";
	    
	    $ch = curl_init();
	    
	    curl_setopt($ch,CURLOPT_URL,$url);
	    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	    
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	        'Content-Type: application/json',
	        'accept-encoding: gzip, deflate',
	        'accept-language: en-US,en;q=0.8',
	        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36',
	        'accept: application/json',
	        'server: nginx/1.16.1',
	        'connection: keep-alive',
	        'vary: Accept, Cookie',
	        'allow: POST, OPTIONS',
	        'Authorization: token '.$this->session->userdata('token')
	    ) );
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    
	    $output=curl_exec($ch);
	    
	    curl_close($ch);
	    pr( json_decode( $output ) );	    
	}
	
}