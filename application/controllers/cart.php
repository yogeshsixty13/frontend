<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cart extends CI_Controller 
{

	var $is_ajax = false;
	var $customer_id =0;		//customer id if session set otherwise 0
	var $cartArr =array();     //cartArr array will be empty if not set 
	var $wishArr =array();     //wishArr array will be empty if not set 
	var $product_price_id =0;  //prod price id if session is set
	
	//parent constructor will load model inside it
	function cart()
	{
		parent::__construct();

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
	
	function index()
	{
		$data = $this->getCartData();
		
		$data['pageName'] = 'shopping_cart';
		$this->load->view('site-layout',$data);
	}

/*
 *	@abstract display wishlist of user
 */
	function wishlist()
	{
		$data = $this->getWishData();
		//pr($data); die;
		
		$data['pageName'] = 'wish_list';
		//$data['custom_page_title'] = "Wishlist";
		$this->load->view('site-layout',$data);
	}

/*
 * @abstract add product in cart: change25/13/2013* 25/12/2013 now solitaire(mount+diamond ) category and diamond can also be added oin cart
*/
	function add()
	{
		cmn_vw_cartAdd( $this->product_price_id, $this->is_ajax, $this->cartArr, $this->customer_id ); 
	}

/**
 * @abstract add product in cart
 */
	function add_wishlist()
	{
		cmn_vw_wishAdd($this->product_price_id, $this->is_ajax, $this->wishArr, $this->customer_id);
	}

/** 
 * @author Cloudwebs
 * @abstract get products from session cart and if it is not set then database cart
 */
	function getCartData()
	{
		/**
		 * From 09-04-2015 now it will always read from database if user is logged in 
		 */
		if( isLoggedIn() )
		{
			$data = getCartData( "", $this->customer_id, true, false, true, true);
			
		}
		else 
		{
			$data = getCartData( $this->cartArr, $this->customer_id, false, false, true, true);
		}
		return $data;		
	}

/**
 * @abstract get products from session wishArr and if it is not set then database wish
 */
	function getWishData()
	{
		/**
		 * From 09-04-2015 now it will always read from database if user is logged in
		 */
		if( isLoggedIn() )
		{
			return getWishData( "",$this->customer_id, true);
		}
		else
		{
			return getWishData($this->wishArr,$this->customer_id);
		}
		
				
	}

/**
 * @abstract update qty of product in cart
 */
	function updateQty()
	{
		cmn_vw_updateQty( $this ); 
	}
	
/*
 * @abstract function will apply coupon dixount to grand total only if coupon available and valid
*/
	function applyCoupon()
	{
		cmn_vw_applyCoupon();
	}

 /*	
 * return data refresh wishlist cart at front side
 */
	 function refreshWishCart()
	 {
		 echo json_encode(getCartWishCount());
	 }

/*
 * @author Cloudwebs
 * @abstract remove product from cart
*/
	function removeProduct()
	{
		cmn_vw_removeProduct( $this ); 
	}

/*
 * @abstract add product in cart
*/
	function removeWishlist()
	{
		cmn_vw_removeWishlist( $this ); 
	}

	function orderLatestStatusMsg()
	{
		$returnArr['success'] = cart_hlp_orderLatestStatusMsg( $_POST['order_details_id'] );
		
		echo json_encode($returnArr);
	}
	 
}