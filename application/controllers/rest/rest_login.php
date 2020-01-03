<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Keys Controller
 *
 * This is a basic Key Management REST controller to make and delete keys.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
*/

// This can be removed if you use __autoload() in config.php
require(APPPATH.'/libraries/REST_Controller.php');

class rest_login extends REST_Controller
{
	//parent constructor will load model inside it
	function rest_login()
	{
		parent::__construct();
		
	}
	
	/**
	 * create date 08-05-2015 used to restAPI
	 * Login / Signin Form
	 */
	public function login_post()
	{
		$data = array();
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_login();
	
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}
	
	/**
	 * create date 09-05-2015 used to restAPI
	 * register / signup Form
	 */
	public function signup_post()
	{
		$data = array();
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_signup();
	
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}
	
	/**
	 * create date 11-05-2015 used to restAPI
	 * forgot password Form
	 */
	public function forgot_post()
	{
		$data = array();
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_forgot();
	
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}

	/**
	 * create date 11-05-2015 used to restAPI
	 * forgot password Form
	 */
	public function logout_get()
	{
		$data = array();
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_logout();
	
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}
	
}
