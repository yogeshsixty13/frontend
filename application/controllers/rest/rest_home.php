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

class rest_home extends REST_Controller
{
//     var $is_ajax = false;

    //parent constructor will load model inside it
    function rest_home()
    {
    	parent::__construct();
    	$this->load->model('mdl_home','hom');
//     	$this->is_ajax = $this->input->is_ajax_request();
    }
    
    /**
     * 
     */
    function index()
    {
    	$this->home();
    }

    /**
     * function will sets language session currently under use
     */
    function setLangSession_get()
    {
    	/**
    	 * if admin session then nothing to do, it's done :) <br>
    	 * but if it is front end client session then redirect to that particular subdomain.
    	 */
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = array("type"=>"success", "msg"=>"");
    	 
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     *
     */
    function menu_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_mainMenu();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     *
     */
    function home_get()
    {
    	$data = array(); 
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_home();
    	 
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    function setCurrencySession_get()
    {
    	/**
    	 * if admin session then nothing to do, it's done :) <br>
    	 * but if it is front end client session then redirect to that particular subdomain.
    	 */
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = changeDefaultCurrency( $this->input->get('currency_id') );
    	
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
//     	echo $this->hom->getCurrencyData();
    }
    
    /**
     * Review Form
     */
    public function review_post()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_review(); 
    	
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * Contact Form
     */
    public function contact_post()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_contact();
    	 
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    /**
     * create date 11-05-2015 used to restAPI
     * Invitefriends Form
     */
    public function invitefriend_post()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_invitefriend();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    public function about_us_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_about_us();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    public function term_condition_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_term_condition();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }

    public function return_policy_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_return_policy();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * Create on 19-03-2016
     * common function call from API for drower link
     * About US, Return Policy, Term & Condition, Export.
     * @return unknown
     */
    public function common_page_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_common_page( $this->input->get("article_key") );
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
}
