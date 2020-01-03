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

class rest_cart extends REST_Controller
{
	//HELD FOR REMOVAL
	var $is_ajax = false;
	
	var $customer_id =0;		//customer id if session set otherwise 0
	var $cartArr =array();     //cartArr array will be empty if not set
	var $wishArr =array();     //wishArr array will be empty if not set
	var $product_price_id =0;  //prod price id if session is set
	
    function rest_cart()
    {
		parent::__construct();

		//HELD FOR REMOVAL
		$this->is_ajax = $this->input->is_ajax_request();
		
		
		//check if customer session set then update database cart for customer
		if ($this->session->userdata('customer_id') !== FALSE)
		{
			$this->customer_id = (int)$this->session->userdata('customer_id');
		}
		
		//check if cart session set
		if ($this->session->userdata('cartArr') !== FALSE)
		{
			$this->cartArr = $this->session->userdata('cartArr');
		}

		//check if wish session set
		if ($this->session->userdata('wishArr') !== FALSE)
		{
			$this->wishArr = $this->session->userdata('wishArr');
		}

		//check if product_price_id session set
		if ($this->session->userdata('product_price_id') !== FALSE)
		{
			$this->product_price_id = (int)$this->session->userdata('product_price_id');		//partially used in combination with page token
		}
		
		//cache driver
// 		$this->load->driver( 'cache', array( 'adapter' => 'apc', 'backup' => 'file'));
	}
    

	function index_get()
	{
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_getCartData( $this->customer_id, $this->cartArr ); 
			
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}

/**
 *	@abstract display wishlist of user
 */
	function wishlist_get()
	{
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_getWishData( $this->customer_id, $this->wishArr ); 
			
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}

/*
 * @abstract add product in cart: change25/13/2013* 25/12/2013 now solitaire(mount+diamond ) category and diamond can also be added oin cart
*/
	function add_post()
	{
		$data = array();
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_cartAdd( $this->product_price_id, true, $this->cartArr, $this->customer_id );
		 
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}

/**
 * add product in wish list
 */
	function add_wishlist_post()
	{
		$data = array();
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_wishAdd( $this->product_price_id, true, $this->wishArr, $this->customer_id );
		 
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}
        
	/**
	 * Show List in Product page
	 */
	function add_cartlist_post()
	{}
	
	/**
	 * delete product in cart list
	 */
	function removeProduct_post()
	{
		$data = array();
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_removeProduct( $this ); //$this->product_price_id, true, $this->cartArr, $this->customer_id
			
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}
	
	/**
	 * delete product in wish list
	 */
	function removeWishlist_post()
	{
		$data = array();
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_removeWishlist( $this ); //$this->product_price_id, true, $this->cartArr, $this->customer_id
			
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}
	
	/**
	 * Apply Coupon Code in cart list
	 */
	function applyCoupon_post()
	{
		$data = array();
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_applyCoupon();
			
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}
	
	/**
	 * Apply Coupon Code in cart list
	 */
	function updateQty_post()
	{
		$data = array();
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_updateQty( $this );
			
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}
	
	/**
	 * get product in Wish Cart list count
	 */
	function refreshWishCart_get()
	{
		$data = array();
		$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_getrefreshWishCart( );
			
		$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
		$data[ getSysConfig( "rest_message_field_name" ) ] = "";
		$this->response( $data, 200 );
	}
	
}