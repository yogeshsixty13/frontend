<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
*/

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class rest_products extends REST_Controller
{

    function rest_products()
    {
    	parent::__construct();
    	$this->load->model('mdl_products','jew');
    }
    
    /**
     * will call product search module, and return products list to RESTApps
     */
    function productListing_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_productListing( true ); 
    	
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }

    /**
     * @author Cloudwebs
     * @since 26-05-2015
     * scroll pagination on REST Apps liting page
     */
    function scrollPagination_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_scrollPagination($this);
    	 
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * 
     */
    function productDetail_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_showProductsDetails(); 
    	 
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }

    /**
     *
     */
    function fetchProductDetailsAjax_post()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_fetchProductDetailsAjax();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
}