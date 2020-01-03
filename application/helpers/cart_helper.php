<?php
/**
 * @package pr_: cart_hlp 
 * @author Cloudwebs Tech Dev Team 
 * @version 1.9 
 * @abstract cart features helper 
 * @copyright Cloudwebs Tech 
 */

//Shopping cart 

/** 
 * function will update cart as well as customer database for cart if user logged in
 * @param $prod_price_id as per unique product selection generated code 
 * $qty quantity of product 
 * $is_add if true then qty added to previous qty else qty directly updated as last value passed
 * @param $session_prefix paramter added to hold separate session for admin panel cart
 * Update/Change: Date->21/11/2013 from dated function will also be used for admin side as well to over come session conflict session_prefix parameter is added which will separate admin session from customer session
 */	
function updCartDatabase( $prod_price_id, $qty=1, $is_add=false, $is_login_mode=false, $cartArr='', $customer_id=0, $ring_size=0, $session_prefix='', $type='prod' ) 
{
	
	$CI =& get_instance();
	//Note*: When function called from admin side customer_id should always be provided so that session conflict never occur
	if($customer_id==0)
	{
		//check if customer session set then update database cart for customer
		if ($CI->session->userdata('customer_id') !== FALSE)
		{
			$customer_id = (int)$CI->session->userdata('customer_id');
		}
	}
	if( $cartArr=='' )
	{
		//check if cart session set
		if ($CI->session->userdata($session_prefix.'cartArr') !== FALSE)
		{
			$cartArr = $CI->session->userdata($session_prefix.'cartArr');
		}
	}
	
	// if login mode then apply if items in cart then to particular customer cart and unset general(with customer_id 0) cart
	if($is_login_mode && $customer_id!=0 && isset($cartArr[0]) && is_array($cartArr[0]))
	{
		$cartArr[$customer_id] = $cartArr[0];
		unset($cartArr[0]);
	}
	
	$diamond_price_id = '';
	if( $type == 'sol' )
	{
		$tempArr = explode('=', $prod_price_id);	
		$prod_price_id = (int)$tempArr[0];
		$diamond_price_id = $tempArr[1];
	}
	
	//update quantity		
	if($prod_price_id!=0)
	{
		if(isset($cartArr[$customer_id][$prod_price_id]))
		{
			if($is_add === false)
			{
				$cartArr[$customer_id][$prod_price_id]['qty'] = $qty;	
				$cartArr[$customer_id][$prod_price_id]['ring_size'] = $ring_size;	
				if( $type == 'sol' ) { $cartArr[$customer_id][$prod_price_id]['didArr'] = explode('|', $diamond_price_id); }
			}
			else 
			{
				if( $type != 'sol' && $type != 'dia' ) //don't increase sol or dia product
				{ $cartArr[$customer_id][$prod_price_id]['qty'] = $cartArr[$customer_id][$prod_price_id]['qty']+$qty; } 
				else 
				{ $cartArr[$customer_id][$prod_price_id]['qty'] = $qty; }	
						
				$cartArr[$customer_id][$prod_price_id]['ring_size'] = $ring_size;	
				if( $type == 'sol' ) { $cartArr[$customer_id][$prod_price_id]['didArr'] = explode('|', $diamond_price_id); }
			}
		}
		else
		{
			$cartArr[$customer_id][$prod_price_id]['id'] = $prod_price_id;	
			$cartArr[$customer_id][$prod_price_id]['qty'] = $qty;	
			$cartArr[$customer_id][$prod_price_id]['ring_size'] = $ring_size;	
			$cartArr[$customer_id][$prod_price_id]['type'] = $type;	
			if( $type == 'sol' ) { $cartArr[$customer_id][$prod_price_id]['didArr'] = explode('|', $diamond_price_id); }
		}
	}
	
	if((int)$customer_id != 0)
	{
		if(!$is_login_mode && $prod_price_id!=0)
		{
			if(isset($cartArr[$customer_id]) && is_array($cartArr[$customer_id]))
			{
				$diamond_price_idTemp = '';
				if( isset($cartArr[$customer_id][$prod_price_id]['didArr']) && is_array($cartArr[$customer_id][$prod_price_id]['didArr']) )
					$diamond_price_idTemp = implode("|", $cartArr[$customer_id][$prod_price_id]['didArr']);
				
				$data = array('customer_id'=>$customer_id, 'product_price_id'=>$prod_price_id, 'diamond_price_id'=> $diamond_price_idTemp,  
							'product_qty'=>$cartArr[$customer_id][$prod_price_id]['qty'],'customer_cartwish_type'=>'C', 
							'customer_cartwish_carttype'=>$cartArr[$customer_id][$prod_price_id]['type'], 'ring_size'=> $cartArr[$customer_id][$prod_price_id]['ring_size']);
				
				/**
				 * Change on 04-05-2015, keep default is not required CS 
				 */
				if( IS_CS ) 
				{
					$data["manufacturer_id"] = MANUFACTURER_ID;
				}
				else 
				{
					$data["manufacturer_id"] = 7;	//keep default
				}
				
							
				//$session_prefix is always blank for customer and if admin panel call then no need to maintain cart in databse
				if($session_prefix=='')
				{
					$sql = $CI->db->insert_string("customer_cartwish",$data)." ON DUPLICATE KEY UPDATE product_qty=".$cartArr[$customer_id][$prod_price_id]['qty'].", 
												   diamond_price_id='". $diamond_price_idTemp ."', ring_size='".$cartArr[$customer_id][$prod_price_id]['ring_size']."',customer_cartwish_modified_date=now()";

					$CI->db->query($sql);
				}
			}
		}
		else if($is_login_mode)	///login mode never user for admin panel and if used then place this condition "&& $session_prefix==''" with else if
		{
			if(isset($cartArr[$customer_id]) && is_array($cartArr[$customer_id]))
			{
				foreach($cartArr[$customer_id] as $k=>$ar)
				{
					$diamond_price_idTemp = '';
					if( isset($ar['didArr']) && is_array($ar['didArr']) )
						$diamond_price_idTemp = implode("|", $ar['didArr']);
					
					//here on update mode quantity is added to previous quantity in database implicitly
					$data = array('customer_id'=>$customer_id, 'product_price_id'=>$k,'diamond_price_id'=>$diamond_price_idTemp,'product_qty'=>$ar['qty'],'customer_cartwish_type'=>'C', 'customer_cartwish_carttype'=>$ar['type'],'ring_size'=> $ar['ring_size']);
					/**
					 * Change on 04-05-2015, keep default is not required CS
					 */
					if( IS_CS )
					{
						$data["manufacturer_id"] = MANUFACTURER_ID;
					}
					else
					{
						$data["manufacturer_id"] = 7;	//keep default
					}
						
					$sql = $CI->db->insert_string("customer_cartwish",$data)." ON DUPLICATE KEY UPDATE product_qty=product_qty+".$ar['qty'].", 
												   diamond_price_id='".$diamond_price_idTemp."', ring_size='".$ar['ring_size']."', customer_cartwish_modified_date=now()";
						
					
					$CI->db->query($sql);
				}
			}
			
			if( IS_CS )
			{
				$res = $CI->db->query("SELECT product_price_id,diamond_price_id,product_qty,ring_size,customer_cartwish_carttype FROM customer_cartwish 
								   	   WHERE manufacturer_id=".MANUFACTURER_ID." AND customer_id=".$customer_id." AND customer_cartwish_type='C'")->result_array();	
			}
			else 
			{
				$res = $CI->db->query("SELECT product_price_id,diamond_price_id,product_qty,ring_size,customer_cartwish_carttype FROM customer_cartwish
								   	   WHERE customer_id=".$customer_id." AND customer_cartwish_type='C'")->result_array();
			}
			if(!empty($res))
			{
				foreach($res as $k=>$ar)
				{
					$cartArr[$customer_id][$ar['product_price_id']]['id'] = (int)$ar['product_price_id'];	
					$cartArr[$customer_id][$ar['product_price_id']]['qty'] = $ar['product_qty'];	
					$cartArr[$customer_id][$ar['product_price_id']]['ring_size'] = $ar['ring_size'];
					$cartArr[$customer_id][$ar['product_price_id']]['type'] = $ar['customer_cartwish_carttype'];
					if( $cartArr[$customer_id][$ar['product_price_id']]['type'] == 'sol' ) { $cartArr[$customer_id][$ar['product_price_id']]['didArr'] = explode('|', $ar['diamond_price_id']); }
				}
			}
		}
	}

	
	//save session
	$session = array($session_prefix.'cartArr'=>$cartArr);
	$CI->session->set_userdata($session);
	
	unset($CI);
}
	
/** 
 * @abstract function will update wish as well as customer database for cart if user logged in
 * @param $prod_price_id id as per unique product selection generated code 
 * $qty quantity of product 
 * $is_add if true then qty added to previous qty else qty directly updated as last value passed
*/	
function updWishDatabase($prod_price_id,$is_login_mode=false,$wishArr='',$customer_id=0)
{
	$CI =& get_instance();
	
	if($customer_id==0)
	{
		//check if customer session set then update database cart for customer
		if ($CI->session->userdata('customer_id') !== FALSE)
		{
			$customer_id = (int)$CI->session->userdata('customer_id');
		}
	}
	if($wishArr=='')
	{
		//check if wish session set
		if ($CI->session->userdata('wishArr') !== FALSE)
		{
			$wishArr = $CI->session->userdata('wishArr');
		}
	}

	// if login mode then apply if items in cart then to particular customer cart and unset general(with customer_id 0) cart
	if($is_login_mode && $customer_id!=0 && isset($wishArr[0]) && is_array($wishArr[0]))
	{
		$wishArr[$customer_id] = $wishArr[0];
		unset($wishArr[0]);
	}
	
	if($prod_price_id!=0)
	{
		//insert in session if not exist
		if(!isset($wishArr[$customer_id][$prod_price_id]))
		{
			$wishArr[$customer_id][$prod_price_id]['id'] = $prod_price_id;	
		}		
		else
			return false;
	}

	if((int)$customer_id != 0)
	{
		if(!$is_login_mode && $prod_price_id!=0)
		{
			if(is_array($wishArr))
			{
				$data = array( 'customer_id'=>$customer_id, 'product_price_id'=>$prod_price_id, 'customer_cartwish_type'=>'W');
				$data["manufacturer_id"] = MANUFACTURER_ID;
				
				
				$sql = $CI->db->insert_string("customer_cartwish",$data)." ON DUPLICATE KEY UPDATE customer_cartwish_modified_date=now()";
				$CI->db->query($sql);
			}
		}
		else if($is_login_mode)
		{
			if(isset($wishArr[$customer_id]) && is_array($wishArr[$customer_id]))
			{
				foreach($wishArr[$customer_id] as $k=>$ar)
				{
					$data = array( 'customer_id'=>$customer_id, 'product_price_id'=>$k, 'customer_cartwish_type'=>'W');
					$data["manufacturer_id"] = MANUFACTURER_ID;
						
					$sql = $CI->db->insert_string("customer_cartwish",$data)." ON DUPLICATE KEY UPDATE customer_cartwish_modified_date=now()";
					$CI->db->query($sql);
				}
			}
				
			$res = null; 
			if( IS_CS )
			{
				$res = $CI->db->query("SELECT product_price_id FROM customer_cartwish 
								   	   WHERE manufacturer_id=".MANUFACTURER_ID." AND customer_id=".$customer_id." AND customer_cartwish_type='W'")->result_array();					
			}
			else 
			{
				$res = $CI->db->query("SELECT product_price_id FROM customer_cartwish
								   	   WHERE customer_id=".$customer_id." AND customer_cartwish_type='W'")->result_array();
			}				
			if(!empty($res))
			{
				foreach($res as $k=>$ar)
				{
					$wishArr[$customer_id][$ar['product_price_id']]['id'] = (int)$ar['product_price_id'];	
				}
			}
		}
	}

	//save session
	$CI->session->set_userdata( array( 'wishArr'=>$wishArr ) );
	unset($CI);
	return true;

}

/** 
 * @author Cloudwebs
 * @abstract get products from session cart and if it is not set then database cart
 */
function getCartData($cartArr='', $customer_id=0, $is_from_database=false, $is_ajax=false, $is_cart_or_checkout=false, $is_status_check=true, $is_post_order=false)
{
	$CI =& get_instance();
	
	if($cartArr=='' && !$is_from_database)
	{
		//check if cart session set
		if ($CI->session->userdata('cartArr') !== FALSE)
		{
			$cartArr = $CI->session->userdata('cartArr');
			
		}
	}

	if($customer_id==0)
	{
		/**
		 * check if customer session set
		 */
// 		if ($CI->session->userdata('customer_id') !== FALSE)
// 		{
// 			$customer_id = $CI->session->userdata('customer_id');
// 		}
		$customer_id = (int)$CI->session->userdata('customer_id');
		
	}
	
	$data = array();
	$data['order_total_qty'] = 0;
	$data['order_subtotal_amt'] = 0;
	$data['order_discount_amount'] = 0;
	$data['order_total_amt'] = 0;
	
	//get data from session here if customer is logged in then cust id is set else get data from session as per cust id 0 global guest session
	if(isset($cartArr[$customer_id]) && is_array($cartArr[$customer_id]) && sizeof($cartArr[$customer_id])>0)
	{
		$data['customer_id'] = $customer_id;	 
		$data['cartArr'][$customer_id] = $cartArr[$customer_id];	 
		
		foreach($cartArr[$customer_id] as $k=>$ar)
		{
			if( empty( $ar['type'] ) || $ar['type'] == 'prod' )	//changed on 09-04-015 $ar['type'] == ''
			{
				$data['cart_prod'][$k] = showProductsDetails($k, $is_ajax, $is_cart_or_checkout, $is_status_check, '', $ar['ring_size']);
				if( isIncludeChain( $ar["ring_size"] ) )
				{
					$data['cart_prod'][$k]['product_discounted_price'] += getChainPrice(false);
				}
				
				//if product is no more available then inform user that product is no more available
				if(!$data['cart_prod'][$k])	 
				{
					$data['cart_prod'][$k]['not_available'] = 'Sorry one of your cart product is not available.';
				}
				else
				{
					//total quantity
					$data['order_total_qty'] +=  $ar['qty'];
					$data['cart_prod'][$k]['qty'] = $ar['qty'];
					$data['cart_prod'][$k]['type'] = $ar['type'];
					
					$data['cart_prod'][$k]["is_out_stock"] = isProductOutOfStock( $data['cart_prod'][$k]["product_id"], $data['cart_prod'][$k]["inventory_type_id"] ); 

					/**
					 * out of stock conditionn added on 23-03-2015
					 */
					if( !$data['cart_prod'][$k]["is_out_stock"] )
					{
						//apply to order_subtotal_amt and order_total_amt : till coupon not applied both amt will be same
						$data['order_subtotal_amt'] += $data['cart_prod'][$k]['product_discounted_price'] * $ar['qty'];
						$data['order_total_amt'] += $data['cart_prod'][$k]['product_discounted_price'] * $ar['qty'];
					}
				}
			}
			else if( $ar['type'] == 'cz' )
			{
				$data['cart_prod'][$k] = showProductsDetails($k, $is_ajax, $is_cart_or_checkout, $is_status_check, '', $ar['ring_size'], "_cz");
				if( isIncludeChain( $ar["ring_size"] ) )
				{
					$data['cart_prod'][$k]['product_discounted_price'] += getChainPrice(false);
				}
				
				
				//if product is no more available then inform user that product is no more available
				if(!$data['cart_prod'][$k])	 
				{
					$data['cart_prod'][$k]['not_available'] = 'Sorry one of your cart product is not available.';
				}
				else
				{
					//total quantity
					$data['order_total_qty'] +=  $ar['qty'];
					$data['cart_prod'][$k]['qty'] = $ar['qty'];
					$data['cart_prod'][$k]['type'] = $ar['type'];
					
					//apply to order_subtotal_amt and order_total_amt : till coupon not applied both amt will be same
					$data['order_subtotal_amt'] += $data['cart_prod'][$k]['product_discounted_price'] * $ar['qty'];
					$data['order_total_amt'] += $data['cart_prod'][$k]['product_discounted_price'] * $ar['qty'];
				}
			}
			else if( $ar['type'] == 'sol' )
			{
				$data['cart_prod'][$k] = showProductsDetails($k, $is_ajax, $is_cart_or_checkout, $is_status_check, '', $ar['ring_size'], '_mount');
				if( isIncludeChain( $ar["ring_size"] ) )
				{
					$data['cart_prod'][$k]['product_discounted_price'] += getChainPrice(false);
				}
				

				//if product is no more available then inform user that product is no more available
				if(!$data['cart_prod'][$k])	 
				{
					$data['cart_prod'][$k]['not_available'] = 'Sorry one of your cart product is not available.';
				}
				else
				{
					//total quantity
					$data['order_total_qty'] +=  $ar['qty'];
					$data['cart_prod'][$k]['qty'] = $ar['qty'];
					$data['cart_prod'][$k]['type'] = $ar['type'];
					//fetch diamond detail for this mount						
					foreach( $ar['didArr'] as $no=>$did)
					{
						$data['cart_prod'][$k]['d_detail'][$did] = fetchDiamondDetail( $did );

						//apply to order_subtotal_amt and order_total_amt : till coupon not applied both amt will be same
						$data['order_subtotal_amt'] += @$data['cart_prod'][$k]['d_detail'][$did]['dp_price'];
						$data['order_total_amt'] += @$data['cart_prod'][$k]['d_detail'][$did]['dp_price'];
					}
					
					//apply to order_subtotal_amt and order_total_amt : till coupon not applied both amt will be same
					$data['order_subtotal_amt'] += $data['cart_prod'][$k]['product_discounted_price'] * $ar['qty'];
					$data['order_total_amt'] += $data['cart_prod'][$k]['product_discounted_price'] * $ar['qty'];
				}
			}
			else if( $ar['type'] == 'dia' )
			{
				//total quantity
				$data['order_total_qty'] +=  $ar['qty'];
				$data['cart_prod'][$k]['qty'] = $ar['qty'];
				$data['cart_prod'][$k]['type'] = $ar['type'];
				//fetch diamond detail for this mount						
				$data['cart_prod'][$k]['d_detail'][$k] = fetchDiamondDetail( $k );
				
				//apply to order_subtotal_amt and order_total_amt : till coupon not applied both amt will be same
				$data['order_subtotal_amt'] += $data['cart_prod'][$k]['d_detail'][$k]['dp_price'];
				$data['order_total_amt'] += $data['cart_prod'][$k]['d_detail'][$k]['dp_price'];
			}
		}
	}
	else if($is_from_database && $customer_id!=0)	//if session is not set then see if customer id is not 0 then fetch data from customer cart database
	{
		$data['customer_id'] = $customer_id;	 
		
		$res_cart = null;
		if( IS_CS )
		{
			$res_cart = $CI->db->query("SELECT product_price_id, diamond_price_id, product_qty, ring_size , customer_cartwish_carttype
									FROM customer_cartwish
									WHERE manufacturer_id=".MANUFACTURER_ID." AND customer_id=".$customer_id." AND customer_cartwish_type='C'")->result_array();
		}
		else 
		{
			$res_cart = $CI->db->query("SELECT product_price_id, diamond_price_id, product_qty, ring_size , customer_cartwish_carttype
									FROM customer_cartwish
									WHERE customer_id=".$customer_id." AND customer_cartwish_type='C'")->result_array();
		}
		
		if(is_array($res_cart) && sizeof($res_cart)>0)
		{
			foreach($res_cart as $k=>$ar)
			{
				if( empty( $ar['customer_cartwish_carttype'] ) || $ar['customer_cartwish_carttype'] == 'prod' )
				{
					$data['cart_prod'][$ar['product_price_id']] = showProductsDetails($ar['product_price_id'], $is_ajax, $is_cart_or_checkout, $is_status_check, '', $ar['ring_size']);
					if( isIncludeChain( $ar["ring_size"] ) )
					{
						$data['cart_prod'][$ar['product_price_id']]['product_discounted_price'] += getChainPrice(false);
					}
					
					//if product is no more available then inform user that product is no more available
					if(!$data['cart_prod'][$ar['product_price_id']])	 
					{
						$data['cart_prod'][$ar['product_price_id']]['not_available'] = 'Sorry one of your cart product is not available.';
					}
					else
					{
						$data['cart_prod'][$ar['product_price_id']]["is_out_stock"] = isProductOutOfStock( $data['cart_prod'][$ar['product_price_id']]["product_id"], $data['cart_prod'][$ar['product_price_id']]["inventory_type_id"] );
						
						/**
						 * out of stock conditionn added on 23-03-2015. 
						 * Condition added "$is_post_order" on 23-05-2015 for, post order processing of warehouse managed products without validation.   
						*/
						if( !$data['cart_prod'][$ar['product_price_id']]["is_out_stock"] || $is_post_order )
						{
							//prepare cart array from database
							$cartArr[$customer_id][$ar['product_price_id']]['id'] = $ar['product_price_id'];
							$cartArr[$customer_id][$ar['product_price_id']]['qty'] = $ar['product_qty'];
							$cartArr[$customer_id][$ar['product_price_id']]['ring_size'] = $ar['ring_size'];
							$cartArr[$customer_id][$ar['product_price_id']]['type'] = $ar['customer_cartwish_carttype'];
							
							//total quantity
							$data['order_total_qty'] +=  $ar['product_qty'];
							
							$data['cart_prod'][$ar['product_price_id']]['type'] = $ar['customer_cartwish_carttype'];
							
							//apply to order_subtotal_amt and order_total_amt : till coupon not applied both amt will be same
							$data['order_subtotal_amt'] += $data['cart_prod'][$ar['product_price_id']]['product_discounted_price'] * $ar['product_qty'];
							$data['order_total_amt'] += $data['cart_prod'][$ar['product_price_id']]['product_discounted_price'] * $ar['product_qty'];
						}
						else 
						{
							$data['cart_prod'][$ar['product_price_id']]['not_available'] = 'Sorry one of your cart product is out of stock.';
						}
					}
				}
				else if( $ar['customer_cartwish_carttype'] == 'cz' )
				{
					$data['cart_prod'][$ar['product_price_id']] = showProductsDetails($ar['product_price_id'], $is_ajax, $is_cart_or_checkout, $is_status_check, '', $ar['ring_size'], "_cz");
					if( isIncludeChain( $ar["ring_size"] ) )
					{
						$data['cart_prod'][$ar['product_price_id']]['product_discounted_price'] += getChainPrice(false);
					}
						
					
					//if product is no more available then inform user that product is no more available
					if(!$data['cart_prod'][$ar['product_price_id']])	 
					{
						$data['cart_prod'][$ar['product_price_id']]['not_available'] = 'Sorry one of your cart product is not available.';
					}
					else
					{
						//prepare cart array from database
						$cartArr[$customer_id][$ar['product_price_id']]['id'] = $ar['product_price_id'];
						$cartArr[$customer_id][$ar['product_price_id']]['qty'] = $ar['product_qty'];
						$cartArr[$customer_id][$ar['product_price_id']]['ring_size'] = $ar['ring_size'];
						$cartArr[$customer_id][$ar['product_price_id']]['type'] = $ar['customer_cartwish_carttype'];

						//total quantity
						$data['order_total_qty'] +=  $ar['product_qty'];
						
						$data['cart_prod'][$ar['product_price_id']]['type'] = $ar['customer_cartwish_carttype'];
						
						//apply to order_subtotal_amt and order_total_amt : till coupon not applied both amt will be same
						$data['order_subtotal_amt'] += $data['cart_prod'][$ar['product_price_id']]['product_discounted_price'] * $ar['product_qty'];
						$data['order_total_amt'] += $data['cart_prod'][$ar['product_price_id']]['product_discounted_price'] * $ar['product_qty'];
					}
				}
				else if( $ar['customer_cartwish_carttype'] == 'sol' )
				{
					$data['cart_prod'][$ar['product_price_id']] = showProductsDetails($ar['product_price_id'], $is_ajax, $is_cart_or_checkout, $is_status_check, '', $ar['ring_size'], '_mount');
					if( isIncludeChain( $ar["ring_size"] ) )
					{
						$data['cart_prod'][$ar['product_price_id']]['product_discounted_price'] += getChainPrice(false);
					}
						
					
					//if product is no more available then inform user that product is no more available
					if(!$data['cart_prod'][$ar['product_price_id']])	 
					{
						$data['cart_prod'][$ar['product_price_id']]['not_available'] = 'Sorry one of your cart product is not available.';
					}
					else
					{
						//prepare cart array from database
						$cartArr[$customer_id][$ar['product_price_id']]['id'] = $ar['product_price_id'];
						$cartArr[$customer_id][$ar['product_price_id']]['didArr'] = explode("|",$ar['diamond_price_id']);
						$cartArr[$customer_id][$ar['product_price_id']]['qty'] = $ar['product_qty'];
						$cartArr[$customer_id][$ar['product_price_id']]['ring_size'] = $ar['ring_size'];
						$cartArr[$customer_id][$ar['product_price_id']]['type'] = $ar['customer_cartwish_carttype'];

						//total quantity
						$data['order_total_qty'] +=  $ar['product_qty'];
						
						$data['cart_prod'][$ar['product_price_id']]['type'] = $ar['customer_cartwish_carttype'];
						//fetch diamond detail for this mount
						$didArr = explode('|', $ar['diamond_price_id']);						
						foreach( $cartArr[$customer_id][$ar['product_price_id']]['didArr'] as $no=>$did)
						{
							$data['cart_prod'][$ar['product_price_id']]['d_detail'][$did] = fetchDiamondDetail( $did );

							//apply to order_subtotal_amt and order_total_amt : till coupon not applied both amt will be same
							$data['order_subtotal_amt'] += $data['cart_prod'][$ar['product_price_id']]['d_detail'][$did]['dp_price'];
							$data['order_total_amt'] += $data['cart_prod'][$ar['product_price_id']]['d_detail'][$did]['dp_price'];
						}
						
						//apply to order_subtotal_amt and order_total_amt : till coupon not applied both amt will be same
						$data['order_subtotal_amt'] += $data['cart_prod'][$ar['product_price_id']]['product_discounted_price'] * $ar['product_qty'];
						$data['order_total_amt'] += $data['cart_prod'][$ar['product_price_id']]['product_discounted_price'] * $ar['product_qty'];
					}
				}
				else if( $ar['customer_cartwish_carttype'] == 'dia' )
				{
					//total quantity
					$data['order_total_qty'] +=  $ar['product_qty'];

					//prepare cart array from database
					$cartArr[$customer_id][$ar['product_price_id']]['id'] = $ar['product_price_id'];
					$cartArr[$customer_id][$ar['product_price_id']]['qty'] = $ar['product_qty'];
					$cartArr[$customer_id][$ar['product_price_id']]['type'] = $ar['customer_cartwish_carttype'];

					$data['cart_prod'][$ar['product_price_id']]['type'] = $ar['customer_cartwish_carttype'];
					//fetch diamond detail for this mount						
					$data['cart_prod'][$ar['product_price_id']]['d_detail'][$ar['product_price_id']] = fetchDiamondDetail( $ar['product_price_id'] );
					
					//apply to order_subtotal_amt and order_total_amt : till coupon not applied both amt will be same
					$data['order_subtotal_amt'] += $data['cart_prod'][$ar['product_price_id']]['d_detail'][$ar['product_price_id']]['dp_price'];
					$data['order_total_amt'] += $data['cart_prod'][$ar['product_price_id']]['d_detail'][$ar['product_price_id']]['dp_price'];
				}
			}
		}
		$data['cartArr'] = $cartArr;
	}
	else
	{
		if( is_restClient() )
		{
			return array('type'=>'error','msg'=>'Session expired. Your shopping cart is empty. Please login!','order_total_amt'=>0);
		}
		else 
		{
			$login = '<a href="'.site_url('login').'">login</a>';
			return array('type'=>'error','msg'=>'Session expired. Your shopping cart is empty. Please '.$login.'!','order_total_amt'=>0);
		}
	}

	$data['type'] = 'success';
	return $data;		
}

/**
 * @abstract get products from session wishArr and if it is not set then database wish
 */
function getWishData($wishArr,$customer_id, $is_from_db=false)
{
	
	$CI =& get_instance();
	$data = array();
	
	/**
	 * check if wish session set
	 * 
	 * !$is_from_db: condition added on 09-04-2015 to allow read from DB if user is logged in
	 */
	if ( !$is_from_db && $CI->session->userdata('wishArr') !== FALSE)
	{
		$wishArr = $CI->session->userdata('wishArr');
	}
	
	//get data from session here if customer is logged in then cust id is set else get data from session as per cust id 0 global guest session
	if(isset($wishArr[$customer_id]) && is_array($wishArr[$customer_id]) && sizeof($wishArr[$customer_id])>0)
	{
		$data['customer_id'] = $customer_id;	 
		$data['wishArr'][$customer_id] = $wishArr[$customer_id];	 
		
		foreach($wishArr[$customer_id] as $k=>$ar)
		{
			$data['wish_prod'][$k] = showProductsDetails($k,false,true,false, '', @$ar['ring_size']);	 
			
			//if product is no more available then inform user that product is no more available
			if(!$data['wish_prod'][$k])	 
			{
				$data['wish_prod'][$k]['not_available'] = 'Sorry one of your wish list product is not available.';
			}
		}
	}
	else if($customer_id!=0)	//if session is not set then see if customer id is not 0 then fetch data from customer cart database
	{
		$data['customer_id'] = $customer_id;	 
		
		$res_cart = null; 
		if( IS_CS )
		{
			$res_cart = $CI->db->query("SELECT product_price_id,product_qty, ring_size FROM customer_cartwish
										WHERE manufacturer_id=".MANUFACTURER_ID." AND customer_id=".$customer_id." AND customer_cartwish_type='W'")->result_array();
		}
		else 
		{
			$res_cart = $CI->db->query("SELECT product_price_id,product_qty, ring_size FROM customer_cartwish
										WHERE customer_id=".$customer_id." AND customer_cartwish_type='W'")->result_array();
		}
		
		if(is_array($res_cart) && sizeof($res_cart)>0)
		{
			foreach($res_cart as $k=>$ar)
			{
				$wishArr[$customer_id][$ar['product_price_id']]['id'] = $ar['product_price_id'];
				$data['wish_prod'][$ar['product_price_id']] = showProductsDetails($ar['product_price_id'],false,true,false, '', $ar['ring_size']);
				
				//if product is no more available then inform user that product is no more available
				if(!$data['wish_prod'][$ar['product_price_id']])	 
				{
					$data['wish_prod'][$ar['product_price_id']]['not_available'] = 'Sorry one of your wish list product is not available.';
				}
			}
		}
		
		//inititlize cart session from database for logged in user
		$CI->session->set_userdata(array('wishArr'=>$wishArr));
		
		$data['wishArr'] = $wishArr;
	}

	return $data;		
}

/**
 * @author Cloudwebs
 * @abstract function will return counting of cart an dwid=sh list for cust only if cust_id is set else return 0
 * $return return array of index cart and wish
*/
function getCartWishCount()
{
	
	$customer_id = 0;
	$resArr = array();
	$CI =& get_instance();
	//check if customer session set then update database cart for customer
	if ($CI->session->userdata('customer_id') !== FALSE)
	{
		$customer_id = (int)$CI->session->userdata('customer_id');
	}
	
	if($customer_id!=0)
	{
		$res_cart = null; 
		if( IS_CS )
		{
			$res_cart = executeQuery("SELECT COUNT(product_price_id) as 'Tot' FROM customer_cartwish WHERE manufacturer_id=".MANUFACTURER_ID." AND customer_id=".$customer_id." AND customer_cartwish_type='C'");
		}
		else 
		{
			$res_cart = executeQuery("SELECT COUNT(product_price_id) as 'Tot' FROM customer_cartwish WHERE customer_id=".$customer_id." AND customer_cartwish_type='C'");
		}
		if(!empty($res_cart))
			$resArr['cart'] = $res_cart[0]['Tot'];
		else
			$resArr['cart'] = 0;
			
		$res_wish = null; 
		if( IS_CS )
		{
			$res_wish = executeQuery("SELECT COUNT(product_price_id) as 'Tot' FROM customer_cartwish WHERE manufacturer_id=".MANUFACTURER_ID." AND customer_id=".$customer_id." AND customer_cartwish_type='W'");
		}
		else 
		{
			$res_wish = executeQuery("SELECT COUNT(product_price_id) as 'Tot' FROM customer_cartwish WHERE customer_id=".$customer_id." AND customer_cartwish_type='W'");
		}
		if(!empty($res_wish))
			$resArr['wish'] = $res_wish[0]['Tot'];
		else
			$resArr['wish'] = 0;
	}
	else if($CI->session->userdata('cartArr') !== FALSE || $CI->session->userdata('wishArr') !== FALSE)
	{
		$cartArr = array();
		$wishArr = array();
		if($CI->session->userdata('cartArr') !== FALSE)
			$cartArr = $CI->session->userdata('cartArr');
			
		if($CI->session->userdata('wishArr') !== FALSE)
			$wishArr = $CI->session->userdata('wishArr');
		
		if(isset($cartArr[0]) && is_array($cartArr[0]))
		{
			$resArr['cart'] = sizeof($cartArr[0]);
		}
		else
			$resArr['cart'] = 0;
			
		if(isset($wishArr[0]) && is_array($wishArr[0]))
		{
			$resArr['wish'] = sizeof($wishArr[0]);
		}
		else
			$resArr['wish'] = 0;
	}
	else
	{
		$resArr['cart'] = 0;
		$resArr['wish'] = 0;
	}

	unset($CI);	 
	return $resArr;
}

/**
 *	@author Cloudwebs
 *	@abstract function will return data for check put page
 */	
function cart_hlp_getCheckOutData( $is_front_end ) 
{
	
	$CI =& get_instance(); 
	$customer_id = cart_hlp_getCustOrdId( $is_front_end ); 
	
	if( $is_front_end ) 
	{
		$data = cart_hlp_getCustomerData( $customer_id ); 
		
		$data['customer_group_type'] = $CI->session->userdata('customer_group_type'); 

		/**
		 * shipp and bill address id
		 */
		//held for removal on 03-04-2015
// 		if ( $CI->session->userdata('customer_shipping_address_id') !== FALSE )
// 		{
// 			$data['customer_shipping_address_id'] = $CI->session->userdata('customer_shipping_address_id');
// 		}
// 		else
// 		{
// 			$data['customer_shipping_address_id'] = 0;
// 		}
		$data['customer_shipping_address_id'] = (int)$CI->session->userdata('customer_shipping_address_id');
		
		/**
		 * customer_billing_address_id
		 */
		//held for removal on 03-04-2015
// 		if ($CI->session->userdata('customer_billing_address_id') !== FALSE)
// 		{
// 			$data['customer_billing_address_id'] = $CI->session->userdata('customer_billing_address_id');
// 		}
// 		else
// 		{
// 			$data['customer_billing_address_id'] = 0;
// 		}
		$data['customer_billing_address_id'] = (int)$CI->session->userdata('customer_billing_address_id');
		
		
		/**
		 * is_shipping_valid
		 */
		//held for removal on 03-04-2015
// 		if( $CI->session->userdata('is_shipping_valid') !== FALSE ) 
// 		{
// 			$data['is_shipping_valid'] = $CI->session->userdata('is_shipping_valid'); 
// 		}
// 		else
// 		{
// 			$data['is_shipping_valid'] = false;
// 		}
		$data['is_shipping_valid'] = $CI->session->userdata('is_shipping_valid');

		$data['custom_page_title'] = 'Checkout';
		$data['pageName'] = 'checkout';
		
		//for security purpose calc grand_total each time from database
		$resArr = cart_hlp_getGrandTotal( true, false ); 
		if( $resArr['type'] != 'success' ) 
		{
			setFlashMessage( $resArr['type'], $resArr['msg']);
				
			if( is_restClient() )
			{
				rest_redirect("cart", ""); 
				
				$data["type"] = "_redirect";  
				return $data; 
			}
			else 
			{
				redirect('cart');
			}
		}
		
		$data['grand_total'] = $resArr['order_total_amt'];
		
		$data['customer_account_manage_balance'] = getCustBalance( $customer_id ); 

		//added on 25-07-2014
		$data["resArr"] = $resArr;
		
		return $data;
	}
	else 
	{
		$resArr = array(); $data = array(); 
		
		$adm_cartArr = $CI->session->userdata( "adm_cartArr" ); 

		//get product detail for cart data
		if( isset($adm_cartArr[$customer_id]) && is_array($adm_cartArr[$customer_id]) && sizeof($adm_cartArr[$customer_id]) > 0 )
		{
			
			$data = getCartData( $adm_cartArr, $customer_id, false, false, true, true ); 
			foreach( $data['cartArr'][$data['customer_id']] as $k=>$ar ) 
			{
				if( isset( $ar['ring_size_drop_down'] ) ) 
				{
					$ar['ring_size_drop_down'] = str_replace('onchange="ajaxCustomize(this)"', 'onchange="calcProdPrice($(\'#product_generated_code_\'+'.$k.'), \''.$k.'\')"', $ar['ring_size_drop_down']);
					
					$ar['ring_size_drop_down'] = str_replace('id="ring_size_id"', 'id="ring_size_id_'.$k.'"', $ar['ring_size_drop_down']);
					$ar['ring_size_drop_down'] = str_replace('name="ring_size_id"', 'name="ring_size_id_'.$k.'"', $ar['ring_size_drop_down']);
				}
			}
			
			$coupon_id = $CI->session->userdata( 'adm_coupon_id' ); 
			if( $coupon_id !== FALSE )
			{
				$resArr = applyCouponCode( $data['order_subtotal_amt'], $data['cartArr'], $customer_id, $coupon_id, '', 'adm_' );	
			}
			
			//billing address id
			$customer_billing_address_id = $CI->session->userdata('adm_customer_billing_address_id');
			if( $customer_billing_address_id !== FALSE )
			{
				$data['customer_billing_address_id'] = $customer_billing_address_id;	
			}
			
			//shipping address id
			$customer_shipping_address_id = $CI->session->userdata('adm_customer_shipping_address_id');
			if( $customer_shipping_address_id !== FALSE )
			{
				$data['customer_shipping_address_id'] = $customer_shipping_address_id;	
			}

			//shipping method id
			$shipping_method_id = $CI->session->userdata('adm_shipping_method_id');
			if( $shipping_method_id !== FALSE )
			{
				$data['shipping_method_id'] = $shipping_method_id;	
			}
			
			//shipping method id
			$is_shipping_valid = $CI->session->userdata('adm_is_shipping_valid');
			if( $is_shipping_valid !== FALSE )
			{
				$data['is_shipping_valid'] = $is_shipping_valid;	
			}

		}
		return array_merge($data, $resArr);
	}
	
}
	
/**
 *
 */
function isIncludeChain( $cart_attributes ) 
{
	if( !empty($cart_attributes) && $cart_attributes == "include_chain" ) 
		return true;
	else
		return false;
}

/**
 *
 */
function chainPriceMsg()
{
	return "Chain price included<br>(".getChainPrice().").";
}


//Shopping cart end  *******************************************************************//



//user info

/**
 * @author   Cloudwebs
 * @abstract function will get customer id: if it is admin process then return Customer ID if order is in insert mode otherwise returns order id
 */
function cart_hlp_getCustOrdId( $is_front_end ) 
{
	$CI =& get_instance(); 
	if( $is_front_end )
	{
		return (int)$CI->session->userdata('customer_id'); 
	}
	else
	{
		if( $CI->input->get('item_id') != '' || $CI->input->post('item_id') != '' ) 
			$id = (int)_de( $CI->security->xss_clean( $_REQUEST['item_id'] ) ); 
			
		if( !empty( $id ) )		//admin process: order edit mode
		{
			return $id;	
		}
		else					   //admin process: order insert mode
		{
			if( $CI->input->get('custid') != '' || $CI->input->post('custid') != '' ) 
				return (int)_de( $CI->security->xss_clean($_REQUEST['custid']) ); 
		}
	}
}

/**
 * @author   Cloudwebs
 * @abstract function will get customer id
 */
function cart_hlp_getCustomerId( $is_front_end ) 
{
	$CI =& get_instance(); 
	if( $is_front_end )
	{
		return (int)$CI->session->userdata('customer_id'); 
	}
	else 
	{
		if( $CI->input->get('custid') != '' || $CI->input->post('custid') != '' ) 
			return (int)_de( $CI->security->xss_clean($_REQUEST['custid']) ); 
	}
}

/**
 * @author   Cloudwebs
 * @abstract function will get customer data if customer logged in
 */
function cart_hlp_getCustomerData( $customer_id ) 
{
	
	$CI =& get_instance(); 
	if( (int)$customer_id != 0 ) 
	{
		return $CI->db->query('SELECT customer_id,customer_firstname,customer_lastname,customer_emailid FROM customer WHERE customer_id='.$customer_id.' ')->row_array(); 
	}
}


//user info: end ***********************************************************************






//Discount coupons


/**
 * @author Cloudwebs
 * @abstract function will apply coupon dixount to grand total only if coupon available and valid
 * @param $is_validate_coupon if false then coupon is not validated for expiry when order amt calculated after order is placed and coupon is used for particular order
 */
function applyCouponCode($order_subtotal_amt, $cartArr, $customer_id, $coupon_id=0, $couponCode='', $is_validate_coupon=true, $session_prefix='')
{
	$CI =& get_instance();
	
	
	$where = ""; 
	if( IS_CS )
	{
		$where = " AND manufacturer_id = ".MANUFACTURER_ID." ";
	}
	
	if((int)$coupon_id!=0) 
	{
		$where .= ' AND coupon_id='.$coupon_id.' ';	
	}
	else if($couponCode!='')
	{
		if( IS_CS )
		{
			$coupon_id = exeQuery( " SELECT coupon_id FROM coupon WHERE manufacturer_id = ".MANUFACTURER_ID." AND coupon_code='".$couponCode."' ", true, "coupon_id" );
		}
		else 
		{
			$coupon_id = exeQuery( " SELECT coupon_id FROM coupon WHERE coupon_code='".$couponCode."' ", true, "coupon_id" );
		}

		if((int)$coupon_id==0)
		{
			return array('type'=>'error','msg'=>'Coupon not available','order_total_amt'=>$order_subtotal_amt);
		}
		$where .= ' AND coupon_id='.$coupon_id.' ';	
	}
	else
	{
		/**
		 * unset coupon session first
		 * When multiple coupon support is added the old coupon session is not supposed to destroy directly, 
		 * instead "Remove" option is given to user to remove that particular coupon.  
		 */
		$CI->session->unset_userdata($session_prefix.'coupon_id');

		return array('type'=>'error','msg'=>'Invalid input.','order_total_amt'=>$order_subtotal_amt);
	}
	
	if( $is_validate_coupon ) 
	{
		$where .= " AND coupon_status=0 
					AND coupon_expiry_date>CURDATE() 
					AND coupon_maximum_use>(SELECT COUNT(coupon_id) as 'Count' FROM orders WHERE del_in=0 AND coupon_id=".$coupon_id.") ";	
	}

	/**
	 * 
	 */
	if( strlen($where) > 0 )
	{
		$where = substr($where, 4); 
	}
	
	$res_coupon = executeQuery( " SELECT coupon_id, coupon_code, coupon_above_amount, coupon_is_above_amount_currencywise, 
										 coupon_discount_amt, coupon_type FROM coupon WHERE ".$where." " ); 
	
	if(!empty($res_coupon)) 
	{
		/**
		 * Old: above static condition
		 */
// 		if( ( $res_coupon[0]["coupon_code"] == "ABOVE15" || $res_coupon[0]["coupon_code"] == "DISC15" ) && $order_subtotal_amt < 15000 ) 
// 		{
// 			return array( 'type' => 'error', 
// 						  'msg' => 'Coupon is not valid for this product, try again with "'.( $res_coupon[0]["coupon_code"] == "ABOVE15" ? "BELOW15" : "DISC10" ).'" Coupon Code.', 
// 						  'order_total_amt' => $order_subtotal_amt ); 
// 		} 

		/**
		 * above condition made dynamic on 03-04-2015
		 * only if above condition had been stated
		 */
		if( $res_coupon[0]["coupon_above_amount"] > 0 )
		{
			if( $res_coupon[0]["coupon_is_above_amount_currencywise"] == 1 )
			{
				if( lp_base( $order_subtotal_amt ) < $res_coupon[0]["coupon_above_amount"] )
				{
					return array( 'type' => 'error',
							'msg' => 'Coupon is not valid for this amount, amount should be at least '.lp( $res_coupon[0]["coupon_above_amount"] ),
							'order_total_amt' => $order_subtotal_amt );
				}
			}
			else 
			{
				$currency = null; 
				if( IS_CS )
				{
					$lang = getField("manufacturer_key", "manufacturer", "manufacturer_id", MANUFACTURER_ID);
					$currency = getCurrencyForCountryCode( getCountryCodeForLangCode( $lang ) );
				}
				else 
				{
					$currency = getDefaultCurrency();
				}
				
				if( lp_base( $order_subtotal_amt, 2, $currency["currency_id"] ) < $res_coupon[0]["coupon_above_amount"] )
				{
					return array( 'type' => 'error',
							'msg' => 'Coupon is not valid for this amount, amount should be at least '.lp( $res_coupon[0]["coupon_above_amount"], 2, $currency["currency_id"] ),
							'order_total_amt' => $order_subtotal_amt );
				}
			}
		}
		
		
		if( $res_coupon[0]['coupon_type'] == "Percent" ) 
		{
			$res_coupon[0]['order_discount_amount'] =  round($order_subtotal_amt * ($res_coupon[0]['coupon_discount_amt']/100) , 0);
			$res_coupon[0]['order_total_amt'] = $order_subtotal_amt - $res_coupon[0]['order_discount_amount'];
			
			//optimize to display on ajax call
			$res_coupon[0]['coupon_type'] = "(%)";
			$res_coupon[0]['coupon_discount_amt'] = $res_coupon[0]['coupon_discount_amt']." %";
		}
		else if( $res_coupon[0]['coupon_type'] == "Fix" ) 
		{
			$currency = null;
			if( IS_CS )
			{
				$lang = getField("manufacturer_key", "manufacturer", "manufacturer_id", MANUFACTURER_ID);
				$currency = getCurrencyForCountryCode( getCountryCodeForLangCode( $lang ) );
			}
			else
			{
				$currency = getDefaultCurrency();
			}
				
			$res_coupon[0]['coupon_discount_amt'] = lp_rev( $res_coupon[0]['coupon_discount_amt'], $currency["currency_id"] ); 
				
			$res_coupon[0]['order_discount_amount'] = $res_coupon[0]['coupon_discount_amt'];
			$res_coupon[0]['order_total_amt'] = round($order_subtotal_amt - $res_coupon[0]['coupon_discount_amt'],0);

			//optimize to display on ajax call
			$res_coupon[0]['coupon_discount_amt'] = lp( $res_coupon[0]['coupon_discount_amt'] );
		}
		else if( $res_coupon[0]['coupon_type'] == "FCW" )	//Fixed discount but currency wise, currency in session
		{
			$res_coupon[0]['coupon_discount_amt'] = lp_rev( $res_coupon[0]['coupon_discount_amt'], CURRENCY_ID );
		
			$res_coupon[0]['order_discount_amount'] = $res_coupon[0]['coupon_discount_amt'];
			$res_coupon[0]['order_total_amt'] = round($order_subtotal_amt - $res_coupon[0]['coupon_discount_amt'],0);
		
			//optimize to display on ajax call
			$res_coupon[0]['coupon_discount_amt'] = lp( $res_coupon[0]['coupon_discount_amt'] );
		}

		//save session coupon_id
		$CI->session->set_userdata(array($session_prefix.'coupon_id'=>$res_coupon[0]['coupon_id']));

		return array('type'=>'success','msg'=>'Coupon applied succcessfully!','coupon_type'=>$res_coupon[0]['coupon_type'],
					 'coupon_discount_amt'=>$res_coupon[0]['coupon_discount_amt'], 'coupon_id'=>$res_coupon[0]['coupon_id'], 
					 'order_total_amt'=>$res_coupon[0]['order_total_amt'], 'order_discount_amount'=>$res_coupon[0]['order_discount_amount']);
	}
	else
	{
		$res_coupon = null; 
		if( IS_CS )
		{
			$res_coupon = executeQuery("SELECT coupon_id FROM coupon WHERE manufacturer_id = ".MANUFACTURER_ID." AND coupon_id=".$coupon_id." ");
		}
		else 
		{
			$res_coupon = executeQuery("SELECT coupon_id FROM coupon WHERE coupon_id=".$coupon_id." ");
		}
		
		if(!empty($res_coupon))
		{
			//unset coupon session first
			$CI->session->unset_userdata($session_prefix.'coupon_id');

			return array('type'=>'error','msg'=>'Coupon expired','order_total_amt'=>$order_subtotal_amt);
		}
		else
		{
			//unset coupon session first
			$CI->session->unset_userdata($session_prefix.'coupon_id');

			return array('type'=>'error','msg'=>'Coupon not available','order_total_amt'=>$order_subtotal_amt);
		}
	}
}


//Discount coupons end  *******************************************************************//












//Address info 




/*
 * @author   Cloudwebs
 * @abstract functoin will check shipp availablity as per shipping code
 */
	function cart_hlp_checkShipAvail( $is_front_end )
	{
		$CI =& get_instance();

		$session_prefix = '';
		if( !$is_front_end )	//if admin
		{
			$session_prefix = 'adm_';
		}
		/**
		 * check and return true if client supports shipping in all locations 
		 */
		if( getField("config_value", "configuration", "config_key", "IS_SHIP_ALL") == 1 )
		{
			//save shippinng method session
			$CI->session->set_userdata( array( $session_prefix.'shipping_method_id'=>getField('shipping_method_id','shipping_method','shipping_method_key','SELF') ) );
			return array('type'=>'success','msg'=>'Shipping available in specified location.');
		}
		
		/**
		 * get grand total of the items in the cart and other charges applicable 
		 */		
		$resArr = cart_hlp_getGrandTotal( $is_front_end, true ); 
		
		if($resArr['type']!='success')
			return  $resArr;
		
		$customer_address_id_shipp = $CI->session->userdata( $session_prefix.'customer_shipping_address_id' ); 
		$res = checkShipAvailability( $resArr['order_total_amt'], $customer_address_id_shipp, 0, 1 );	//for now lat param for is_cod_prepaid is passed static 1 because both are same for now may be change in future
		
		if($res==0 || $res==-1)
		{
			if($res==-1) 
			{ 
				return array( 'type'=>'warning', 'msg'=>'Shipping not available in specified PINCODE please specify another Location.' ); 
			} 
			else if($res == 0) 
			{ 
				return array( 'type'=>'warning', 'msg'=>'Shipping not available in specified PINCODE please specify another PINCODE.' ); 
			} 
		}
		else
		{
			//save shippinng method session
			if( $res==1 )
			{
				$shipping_method_id = getField('shipping_method_id','shipping_method','shipping_method_key','SELF');
			}
			else 
			{
				$shipping_method_id = $res; 
			}
// 			else if( $res==2 || $res==7 )
// 			{
// 				$shipping_method_id = getField('shipping_method_id','shipping_method','shipping_method_key','SEQUEL');
// 			}
// 			else if( $res==1 )	//id deleted
// 			{
// 				$shipping_method_id = getField('shipping_method_id','shipping_method','shipping_method_key','BLUE_DART');
// 			}
// 			else if( $res==10 )
// 			{
// 				$shipping_method_id = getField('shipping_method_id','shipping_method','shipping_method_key','RUSH');
// 			}
			
			$CI->session->set_userdata(array( $session_prefix.'shipping_method_id'=>$shipping_method_id)); 
			return array('type'=>'success','msg'=>'Shipping available in specified location.');
		}
	}

/**
 * @author Cloudwebs
 * @abstract function will check if shipping available in particular shipping address
 * @param $customer_address_id_shipp
 * @param $$is_cod_prepaid 0 then false 1 then COD and 2 then PREPAID
 * @return 0 then  not available , 1 then blue dart available and 2 then sequel available : -1 then shipping in ODA location of Sequel , 7 then sequal in ODA in Gujrat Or Delhi
 */
function checkShipAvailability( $amt, $customer_address_id_shipp=0, $pincode_id=0, $is_cod_prepaid=0 ) 
{
	$CI =& get_instance(); 
	$sql = 'SELECT s.pincode_id,service_type_code, cod_limit, prepaid_limit, shipping_method_key, m.shipping_method_id FROM ';
	if($pincode_id!=0)
	{
		$pincode = getField( "pincode", "pincode", "pincode_id", $pincode_id ); 
	}
	else if($customer_address_id_shipp!=0)
	{
		$pincode = exeQuery( "SELECT p.pincode FROM customer_address c INNER JOIN pincode p 
							  ON p.pincode_id=c.customer_address_zipcode
							  WHERE c.customer_address_id=".$customer_address_id_shipp." ", true, "pincode" ); 
	}
	else
	{
		return 0;
	}	
	$shipping_method_status = (getSysConfig('IS_CHAS') ? ' AND m.shipping_method_status=0' : '');

	$sql .= "shipping_pincodes s 
			INNER JOIN shipping_method m ON m.shipping_method_id=s.shipping_method_id 
			INNER JOIN pincode p ON p.pincode_id=s.pincode_id 
			WHERE (s.shipping_pincodes_status=0 AND p.pincode='".$pincode."' ".$shipping_method_status.") OR (s.shipping_method_id=2)
			GROUP BY m.shipping_method_key ORDER BY shipping_method_sort_order";	
	
	$res = $CI->db->query($sql)->result_array();
	//pr($res);die;
	if(!empty($res))		
	{
		/**
		 * SELF shipping added on 28-04-2015
		 */
		if( associative_array_search($res, 'shipping_method_key', 'SELF') !== FALSE )
		{
			return 1;	//SELF shipping available
		}
		else 
		{
			/**
			 * no other shipping is allowed
			 */
			//commented on 23-01-2015
			
			if(getSysConfig('IS_CHAS')):
				return $res[0]['shipping_method_id'];
			else:
				return -1;
			endif;
			
		}
		
		
		/**
		 * no other shipping is allowed
		 */
		$is_sq_pincode_exist = false;
		$is_bd_pincode_exist = false;
		
		$sequelk = associative_array_search($res, 'shipping_method_key', 'SEQUEL');
		if($sequelk!==FALSE)
		{
			$is_sq_pincode_exist=true;
		}

		$blue_dartk = associative_array_search($res, 'shipping_method_key', 'BLUE_DART');
		if($blue_dartk!==FALSE)
			$is_bd_pincode_exist=true;
			
		//first check Sequel
		if($is_bd_pincode_exist)
		{
			//if not available in sequel check in blue dart if available with price more then 10000/-
			if($is_cod_prepaid==1)
			{
				if($amt < $res[$blue_dartk]['cod_limit'])	
				{
					return 1;	//blue dart available	
				}
			}
			else if($is_cod_prepaid==2)
			{
				if($amt < $res[$blue_dartk]['prepaid_limit'])	
				{
					return 1;	//blue dart available
				}
			}
		}
		
		if($is_sq_pincode_exist && $res[$sequelk]['service_type_code']!='SO')
		{
			return 2;	//sequel available
		}
		
		if($is_sq_pincode_exist && $res[$sequelk]['service_type_code']=='SO')
		{
			//check if sequel available with ODA in delhi or gujrat
			$resPin = $CI->db->query("SELECT state_key from pincode p INNER JOIN state s ON s.state_id=p.state_id WHERE pincode_id=". $res[$sequelk]['pincode_id']."")->row_array();
			if(!empty($resPin) && ($resPin['state_key']=='GJ' || $resPin['state_key']=='DL'))
			{
				return 7;
			}
		}

		if($is_sq_pincode_exist && $amt>50000)
		{
			//Optional allow if even SO amd price range is above 50000/-
			return 2;	//sequel available
		}

		return -1;

	}
	else
	{
		if( isPincodeNonInd( $customer_address_id_shipp ) )
		{
			//change: 24/2/2014 shipping is allowed from all location: outside india
			return 10;	//set default RUSH
		}
		return 0;		
	}
						
	unset($CI);
}

/**
 * is pincode non indian
 */
function isPincodeNonInd( $customer_address_id )
{
	
	$country_id = exeQuery( " SELECT s.country_id FROM customer_address ca 
							INNER JOIN pincode p 
							ON p.pincode_id=ca.customer_address_zipcode INNER JOIN state s 
							ON s.state_id=p.state_id 
							WHERE ca.customer_address_id=".$customer_address_id." ", true, "country_id");

	if( !empty($country_id) && $country_id != 105)						
	{
		return true;
	}
	else
	{
		return false;	
	}
}
	
	

//Address info end *******************************************************************//




//Order transactios:  insert order - payment processing 


/*
 * @author Cloudwebs
 * @abstract function will return next invoice num to be generated for order
 */
function getInvoiceNum()
{
	
	$res = executeQuery("SHOW TABLE STATUS LIKE 'orders'");
	return (int)$res[0]['Auto_increment'];
}

/**
 * @abstract Function generate random transaction id for order transaction
 */
function getTransactionID()
{
	return substr(hash('sha256', MANUFACTURER_ID . mt_rand() . microtime()), 0, 20);
}

/**
 * @author   Cloudwebs
 * @abstract function will fetch products metal and stone prices and also tax applied for productr to store in order_details table
 */
function getProdPricesAndTax($product_price_id, $type='', $prodDet = array()) 
{
	$CI =& get_instance();
	
	$product_generated_code_info = getField( "product_generated_code_info", "product_price", "product_price_id", $product_price_id);
	
	//echo"product_code";pr($product_generated_code_info);
	
	if( empty($product_generated_code_info) ) 
	{	
		return '';
	}

	$res[''] = '';
	$codeArr = parseProductcodeInfo( $product_generated_code_info ); 
	
	//echo"codeArr";pr($codeArr);
	
	$select = "SELECT pp.product_price_weight, product_cod_cost, product_shipping_cost ";
	$join = "FROM product_price pp INNER JOIN product p ON p.product_id=pp.product_id ";						
	$where = "WHERE pp.product_price_id=".$product_price_id." ";	
	
	/**
	 *
	 */
	foreach ($codeArr as $k=>$ar)
	{
		if( $k >= 2 )
		{
			$tempA = explode(":", $ar);
				
			/**
			 * here $k stands for product_stone_number,
			 * minus it by 2 to reflect stone number in sequence.
			*/
			$k -= 2;
			if( $tempA[1] === "JW_CS" || $tempA[1] === "JW_SS1" || $tempA[1] === "JW_SS2" || $tempA[1] === "JW_SSS" )
			{
				if( $k === 0 )
				{
					if( $type != 'cz' )
					{
						$select .= ", dpcs.dp_calculated_cost as dp_calculated_cost_cs ";
						$join .= "LEFT JOIN diamond_price dpcs ON dpcs.diamond_price_id=pp.cs_diamond_price_id ";	
					}
					else
					{
						if( $prodDet['diamond_type_key_cs'] ==  'DIAMOND' )	
						{
							$res['dp_calculated_cost_cs'] = 50;	//per stone
						}
						else if( $prodDet['diamond_type_key_cs'] ==  'GEMSTONE' )	
						{
							$res['dp_calculated_cost_cs'] = 500;	//per carat
						}
						else if( $prodDet['diamond_type_key_cs'] ==  'PEARL' )	
						{
							$res['dp_calculated_cost_cs'] = getField('dp_calculated_cost','diamond_price','diamond_price_id',$codeArr[3]);	//per carat
						}
					}
				}
				else if( $k <= 2 )
				{
					if( $type != 'cz' )
					{
						$select .= ", dpss".$k.".dp_calculated_cost as  dp_calculated_cost_ss".$k." ";
						$join .= "LEFT JOIN diamond_price dpss".$k." ON dpss".$k.".diamond_price_id=pp.ss".$k."_diamond_price_id ";	
					}
					else
					{
						if( $prodDet['diamond_type_key_ss'.$k] ==  'DIAMOND' )	
						{
							$res['dp_calculated_cost_ss'.$k] = 50;	//per stone
						}
						else if( $prodDet['diamond_type_key_ss'.$k] ==  'GEMSTONE' )	
						{
							$res['dp_calculated_cost_ss'.$k] = 500;	//per carat
						}
						else if( $prodDet['diamond_type_key_ss'.$k] ==  'PEARL' )	
						{
							$res['dp_calculated_cost_ss'.$k] = getField('dp_calculated_cost','diamond_price','diamond_price_id',$codeArr[4]);	//per carat
						}
					}
				}
				else
				{
					/**
					 * order details info of product price in detail is yet to made dynamic for product_side_stones table. 
					 */
				}
			}
			elseif( $tempA[1] === "SEL" || $tempA[1] === "CHK" || $tempA[1] === "RDO" )
			{
				if( $k === 0 )
				{
					$res['dp_calculated_cost_cs'] = 0; 
				}
				else if( $k <= 2 )
				{
					$res['dp_calculated_cost_ss'.$k] = 0;
				}
				else
				{
					/**
					 * order details info of product price in detail is yet to made dynamic for product_side_stones table. 
					 */
				}
			}
			elseif( $tempA[1] === "JW_MTL" )
			{
				$select .= ", mp_price_difference ";
				$join .= "LEFT JOIN metal_price mp 
						  ON mp.metal_price_id=pp.metal_price_id ";						
				
			}
			elseif( $tempA[1] === "TXT" )
			{
				if( $k === 0 )
				{
					$res['dp_calculated_cost_cs'] = 0; 
				}
				else if( $k <= 2 )
				{
					$res['dp_calculated_cost_ss'.$k] = 0;
				}
				else
				{
					/**
					 * order details info of product price in detail is yet to made dynamic for product_side_stones table. 
					 */
				}
			}
	
		}
	}
	
	$resNew = $CI->db->query($select.$join.$where." GROUP BY pp.product_price_id")->row_array();
	
// 	if( isIntranetIp() )
// 	{
// 		echo"resNew";pr($resNew); 
// 		echo $select.$join.$where." GROUP BY pp.product_price_id"; 
// 		die; 
// 	}
	return array_merge($res, $resNew);	
}

/**
 * @author   Cloudwebs
 * @abstract functoin will complete all process related to making of payment and creating new order
 * @param $is_front_end: if true then call is from front end and if false then call is from admin panel
 */
function cart_hlp_payment( $is_front_end ) 
{
	$CI =& get_instance(); 
	$customer_id = cart_hlp_getCustOrdId( $is_front_end ); 
	
	/**
	 * added On 20-06-2015 to let pass post parameter in GET using android browsers
	 */
	if( is_restClient() && $CI->input->get("pay_in") == "B" )
	{
		$_POST["agree"] = $CI->input->get("agree");
		$_POST["payment_method_id"] = $CI->input->get("payment_method_id");
	}
	
	$returnArr = array();
	$data = $CI->input->post();
	//echo"data:";pr($data); die; 
	
	if( $is_front_end )
	{
		$CI->form_validation->set_rules('agree','Terms & Condition','trim|required');
		$CI->form_validation->set_rules('payment_method_id','Payment Method','trim|required');
	}
	else
	{
		$CI->form_validation->set_rules( 'payment_method_id', 'Payment Method', 'trim|required' ); 
	}
			
	if( $CI->form_validation->run() == FALSE ) 
	{
		if( $is_front_end ) 
		{
			$returnArr['error'] = $CI->form_validation->get_errors(); 
			pr($returnArr['error']);
			die;
			if( is_restClient() )
			{
				$returnArr["type"] = "error";
    			$returnArr["msg"] = getErrorMessageFromCode('01005');
				return $returnArr;
			}
			else 
			{
				$data = cart_hlp_getCheckOutData( $is_front_end );
				
				$dt["chk"] = array_merge( $data, $returnArr );
				$dt["pageName"] = $dt["chk"]["pageName"];
					
				$data = $returnArr = null;
					
				$CI->load->view( 'site-layout', $dt );
			}
		}
		else
		{
			$data = cart_hlp_getCheckOutData( $is_front_end ); 
			$data['error'] = $CI->form_validation->get_errors(); 
			if( $data['error'] ) 
				setFlashMessage( 'error', getErrorMessageFromCode('01005') ); 
			
			$data['pageName'] = 'admin/'.$CI->controller.'/'.$CI->controller.'_form'; 
			$CI->load->view( 'admin/layout', $data ); 
		}
	}
	else 
	{
		//set session of payment method selected by user for placing order 
		$CI->session->set_userdata( array( 'payment_method_id'=>$data['payment_method_id'] ) ); 
		
		if( $is_front_end ) 
		{
			//validate preOrder reuired paranmeters
			$resValArr = cart_hlp_validatePreOrder( $is_front_end );
			if($resValArr['type'] != 'success')
			{
				setFlashMessage( $resValArr['type'], $resValArr['msg']);

				if( is_restClient() )
				{
					rest_redirect("checkout", "");
					$_rest_redirectData["type"] = "_redirect";
					return $_rest_redirectData;
				}
				else 
				{
					redirect('checkout');
				}
			}

			//echo "resValArr : ";pr($resValArr); die; 
				
			$resArr = cart_hlp_getGrandTotal( $is_front_end, true);
			if($resArr['type'] != 'success')
			{
				setFlashMessage( $resArr['type'], $resArr['msg']);

				if( is_restClient() )
				{
					rest_redirect("checkout", "");
					$_rest_redirectData["type"] = "_redirect";
					return $_rest_redirectData;
				}
				else
				{
					redirect('checkout');
				}
			}
			else if(!isset($resArr['cartArr']) || sizeof($resArr)==0)	//remove this unnecessery check
			{
				setFlashMessage( 'warning', 'Your shopping cart is empty!');

				if( is_restClient() )
				{
					rest_redirect("checkout", "");
					$_rest_redirectData["type"] = "_redirect";
					return $_rest_redirectData;
				}
				else
				{
					redirect('checkout');
				}
			}
			else
			{
				$resArr['customer_shipping_address_id'] = $resValArr['customer_shipping_address_id'];
				$resArr['customer_billing_address_id'] = $resValArr['customer_billing_address_id'];

				$resArr['shipping_method_id'] = $resValArr['shipping_method_id'];
		
				$resArr['customer_note'] = $resValArr["customer_note"]; 
		
				$resArr['order_is_gift_wrap'] = $resValArr['order_is_gift_wrap'];
				
				$resArr['customer_emailid'] = $resValArr['customer_emailid'];

				$resArr = cart_hlp_placeOrder( true, array_merge($resArr, $data) );
				
				if( is_restClient() )
				{
					return $resArr;
				}
			}
		}
		else
		{
			//validate preOrder reuired paranmeters
			$resValArr = cart_hlp_validatePreOrder( $is_front_end );
			if($resValArr['type'] != 'success')
			{
				setFlashMessage( 'error',$resValArr['msg']);
				redirect('admin/'.$CI->controller.'/salesOrderForm?custid='._en( cart_hlp_getCustomerId( $is_front_end ) ) ); 
			}
	
			$resArr = cart_hlp_getGrandTotal( $is_front_end, true ); 
			if($resArr['type'] != 'success') 
			{
				setFlashMessage( 'error',$resArr['msg']); 
				redirect('admin/'.$CI->controller.'/salesOrderForm?custid='._en( cart_hlp_getCustomerId( $is_front_end ) ) ); 
			}
			else
			{
				$resArr['customer_shipping_address_id'] = $resValArr['customer_shipping_address_id'];
				$resArr['customer_billing_address_id'] = $resValArr['customer_billing_address_id'];
	
				$resArr['shipping_method_id'] = $resValArr['shipping_method_id'];
				$resArr['customer_note'] = $resValArr['customer_note'];
				$resArr['order_is_gift_wrap'] = $resValArr['order_is_gift_wrap'];
		
				$resArr = array_merge( $resArr, $resValArr['custRes'] ); 
	
				$resArr['payment_method_id'] = $data['payment_method_id']; 
				$resArr['customer_note'] = $data['customer_note']; 
				$resArr['email_confirm'] = @$data['email_confirm']; 
	
				$resArr = cart_hlp_placeOrder( $is_front_end, $resArr ); 
	
				if( $resArr['type'] == 'success' ) 
				{ 
					setFlashMessage( $resArr['type'], $resArr['msg'] ); 
					redirect( 'admin/'.$CI->controller ); 
				} 
			}
			
		}
	}
	
}

/**
 * @author   Cloudwebs
 * @abstract functoin will called in payment call to vgalidate if all required addresses and shipp info and user is logged in
 * @return will return parameters like shipping and billing address id
 */
function cart_hlp_validatePreOrder( $is_front_end ) 
{
	$CI =& get_instance(); 
	$returnArr = array();
	$returnArr['customer_id'] = $customer_id = cart_hlp_getCustOrdId( $is_front_end ); 

	if( $is_front_end ) 
	{
		
		//check all required session is set else unset is_shipping_valid session and redirect to checkout
		if ( $customer_id == 0 ) 
		{
			$CI->session->set_userdata( array( 'is_shipping_valid' => false ) ); 
			
			$returnArr['type'] = 'warning'; 
			$login = '<a href="'.site_url( 'login' ).'" >Please login again</a>'; 
			$returnArr['msg'] = $login; 
			$returnArr['prompt'] = 'login'; 
			
			return $returnArr; 
		} 
		
		$returnArr['customer_shipping_address_id'] = $CI->session->userdata('customer_shipping_address_id'); 
		if ( empty( $returnArr['customer_shipping_address_id'] ) ) 
		{
			$CI->session->set_userdata( array( 'is_shipping_valid'=>false ) ); 		
			
			$returnArr['type'] = 'warning';
			$returnArr['msg'] = "Customer shipping address required.";
			$returnArr['prompt'] = 'customer_shipping_address_id';

			return $returnArr;
		}
	
		$returnArr['customer_billing_address_id'] = $CI->session->userdata('customer_billing_address_id'); 
		if ( empty($returnArr['customer_shipping_address_id']) )
		{
			$CI->session->set_userdata(array('is_shipping_valid'=>false)); 		
			
			$returnArr['type'] = 'warning';
			$returnArr['msg'] = "Customer billing address required.";
			$returnArr['prompt'] = 'customer_billing_address_id';
	
			return $returnArr;
		}
	
		$returnArr['is_shipping_valid'] = $CI->session->userdata('is_shipping_valid'); 
		if ( empty($returnArr['is_shipping_valid']) )
		{
			$returnArr['type'] = 'warning';
			$returnArr['msg'] = "There might be a problem with shipping location."; 
			$returnArr['prompt'] = 'is_shipping_valid';
	
			return $returnArr;
		}
		
		$returnArr['shipping_method_id'] = $CI->session->userdata('shipping_method_id'); 
		if ( empty($returnArr['shipping_method_id']) )
		{
			$CI->session->set_userdata(array('is_shipping_valid'=>false)); 		
			
			$returnArr['type'] = 'warning';
			$returnArr['msg'] = "There is a problem with shipping method.";
			$returnArr['prompt'] = 'is_shipping_valid';
	
			return $returnArr;
		}

		$returnArr['customer_emailid'] = $CI->session->userdata('customer_emailid');
		if ( empty($returnArr['customer_emailid']) )
		{
			$CI->session->set_userdata(array('is_shipping_valid'=>false)); 		
			
			$returnArr['type'] = 'warning';
			$login = '<a href="'.site_url('login').'" >Please login again</a>';
			$returnArr['msg'] = $login;
			$returnArr['prompt'] = 'login';
			
			return $returnArr;
		}

		$returnArr['payment_method_id'] = $CI->session->userdata('payment_method_id'); 

		$returnArr['customer_note'] = $CI->input->post("customer_note"); 

		$returnArr['order_is_gift_wrap'] = (int)$CI->session->userdata('order_is_gift_wrap');	//not applicable when fun called from completeOrder function
		
		
		$returnArr['type'] = 'success';
		$returnArr['msg'] = "";
		return $returnArr;
	} 
	else 
	{
		
		//check all required session is set else unset is_shipping_valid session and redirect to checkout
		if( (int)cart_hlp_getCustomerId( $is_front_end ) == 0 ) 
		{
			$CI->session->set_userdata( array( 'adm_is_shipping_valid' => false ) ); 
			
			$returnArr['type'] = 'warning'; 
			$returnArr['msg'] = 'Shipping information is required'; 
			return $returnArr; 
		}
		
		$returnArr['customer_shipping_address_id'] = $CI->session->userdata('adm_customer_shipping_address_id'); 
		if( empty( $returnArr['customer_shipping_address_id'] ) ) 
		{
			$CI->session->set_userdata(array('adm_is_shipping_valid'=>false)); 
			
			$returnArr['type'] = 'warning';
			$returnArr['msg'] = "Customer shipping address required.";
			$returnArr['prompt'] = 'customer_shipping_address_id';

			return $returnArr;
		}
	
		$returnArr['customer_billing_address_id'] = $CI->session->userdata('adm_customer_billing_address_id'); 
		if ( empty( $returnArr['customer_billing_address_id'] ) )
		{
			$CI->session->set_userdata(array('adm_is_shipping_valid'=>false)); 		
			
			$returnArr['type'] = 'warning';
			$returnArr['msg'] = "Customer billing address required.";
			$returnArr['prompt'] = 'customer_billing_address_id';
	
			return $returnArr;
		}
	
		$returnArr['is_shipping_valid'] = $CI->session->userdata('adm_is_shipping_valid'); 
		if ( empty( $returnArr['is_shipping_valid'] ) )
		{
			$returnArr['type'] = 'warning';
			$returnArr['msg'] = "Customer shipping address required.";
			$returnArr['prompt'] = 'is_shipping_valid';
	
			return $returnArr;
		}
		
		$returnArr['shipping_method_id'] = $CI->session->userdata('adm_shipping_method_id'); 
		if ( empty( $returnArr['shipping_method_id'] ) )
		{
			$CI->session->set_userdata(array('adm_is_shipping_valid'=>false)); 		
			
			$returnArr['type'] = 'warning';
			$returnArr['msg'] = "Customer shipping address required.";
			$returnArr['prompt'] = 'is_shipping_valid';
	
			return $returnArr;
		}

		$returnArr['payment_method_id'] = $CI->session->userdata('payment_method_id'); 

		$custRes = $CI->db->query('SELECT customer_firstname, customer_lastname, customer_emailid, customer_phoneno 
								   FROM customer 
								   WHERE customer_id='.cart_hlp_getCustomerId( $is_front_end ).' ')->row_array();

		$returnArr['custRes'] = $custRes; 

		$returnArr['customer_note'] = $CI->input->post( 'customer_note' ); 

		$returnArr['order_is_gift_wrap'] = '';	//currently not appplicable

		$returnArr['type'] = 'success';
		$returnArr['msg'] = "";
		return array_merge($returnArr, $custRes);
		
	} 
	
}

/**
 * @author   Cloudwebs
 * functoin will get grand total
 * @param $is_post_order added on 23-05-2015, to make sure that warehouse managed installation will not affect, 
 * post order product processing flow due to inStock condition check in getCartData function.   
 */
function cart_hlp_getGrandTotal( $is_front_end, $is_from_database, $is_validate_coupon=true, $is_post_order=false )
{
	$CI =& get_instance(); 
	$customer_id = cart_hlp_getCustOrdId( $is_front_end );
	
	//echo "Customer_id:";	pr($customer_id);die;
	if( $is_front_end )
	{
		$resCouponArr = array();
		
		$resArr = getCartData( '', $customer_id, $is_from_database, false, true, true, $is_post_order);
		
		//echo " resCoupenArr ";pr($resArr);
		
		if( $resArr['type'] == 'success' ) 
		{
			$coupon_id = $CI->session->userdata( 'coupon_id' );
			
			if( !empty( $coupon_id ) ) 
			{
				$resCouponArr = applyCouponCode( $resArr['order_subtotal_amt'], $resArr['cartArr'], $customer_id, $coupon_id, '', $is_validate_coupon ); 
				
				if( $resCouponArr['type'] != 'success' ) 
				{
					setFlashMessage( $resCouponArr['type'], $resCouponArr['msg'] );	//this msg is displayed only if shipping info completed		
					
					unset( $resCouponArr['msg'] ); 
					$resCouponArr['type'] = 'success';	//mimic type so that flash msg is set/display only when shipping info is complete else no need to display coupon msg. So allow to proceed it to applyShippInfo
				}
			}
		}
	}
	else
	{
		$resCouponArr = array();
		$resArr = getCartData( $CI->session->userdata('adm_cartArr'), $customer_id, $is_from_database, false, true, true ); 
		
		if( $resArr['type'] == 'success' ) 
		{
			$coupon_id = $CI->session->userdata('adm_coupon_id');
			if(!empty($coupon_id))
			{
				$resCouponArr = applyCouponCode( $resArr['order_subtotal_amt'], $resArr['cartArr'], $customer_id, $coupon_id, '', $is_validate_coupon, 
												 'adm_' );	
				if($resCouponArr['type']!='success')
				{
					setFlashMessage($resCouponArr['type'], $resCouponArr['msg']);	//this msg is displayed only if shipping info completed		
					
					unset($resCouponArr['msg']);
					$resCouponArr['type'] = 'success';	//mimic type so that flash msg is set/display only when shipping info is complete else no need to display coupon msg. So allow to proceed it to applyShippInfo
				}
			}
		}
	}
	
	/**
	 * Check if shipping charges needs to apply
	 * 
	 * enable below if non-indian ship charges is applicable, 
	 * 
	 * otherwise apply shipping method charges dynamically.
	 */
	if( FALSE )
	{
		if( MANUFACTURER_ID == 7 && $CI->session->userdata( "is_shipping_valid" ) === TRUE && isPincodeNonInd( $CI->session->userdata( "customer_shipping_address_id" ) ) )
		{
			$shipping_method_free_shipping = exeQuery( " SELECT shipping_method_free_shipping FROM shipping_method
													 WHERE shipping_method_id=".$CI->session->userdata( "shipping_method_id" )." ",
					true, "shipping_method_free_shipping" );
		
			if( !empty( $shipping_method_free_shipping ) )
			{
				$resArr["other_charges"]["shipping_charge"]["name"] = "shipping_charge";
				$resArr["other_charges"]["shipping_charge"]["value"] = $shipping_method_free_shipping;
			}
		}
	}
	if( TRUE )
	{
		if($CI->session->userdata( "is_shipping_valid" ) === TRUE)
		{
			$shipping_method_free_shipping = exeQuery( "SELECT shipping_method_free_shipping FROM shipping_method
														WHERE shipping_method_id=".$CI->session->userdata( "shipping_method_id" )." ",
														true, "shipping_method_free_shipping" );
	
			if( !empty( $shipping_method_free_shipping ) )
			{
				$resArr["other_charges"]["shipping_charge"]["name"] = "shipping_charge";
				$resArr["other_charges"]["shipping_charge"]["value"] = $shipping_method_free_shipping;
			}
		}
	}
	
	
	//pr($resArr);die;
	return array_merge($resArr, $resCouponArr);
}

/**
 * @author   Cloudwebs
 * save or update warehouse transaction only if inventory is warehouse managed
 */
function cart_hlp_warehouseTransaction( $is_front_end, $inventory_type_id, $warehouse_transactions_id, $product_id, $qty, $reflectiveRate )
{
	
	if( hewr_isWarehouseManagedCheckWithId($inventory_type_id) )
	{
		if( !empty( $warehouse_transactions_id ) )
		{
			$data = array();
			$data["wt_qty"] = $qty;
			hewr_editWarehouseTransactions($product_id, $warehouse_transactions_id, $qty, 0, 0, $data, true);		
		}
		else 
		{
			/**
			 * net rate
			 */
			$product = fetchRow("SELECT p.product_price,p.product_sku
							 	FROM product p
							 	WHERE p.product_id=".$product_id." ");
			$data = array();
			$data["product_id"] = $product_id; 
			$data["wt_qty"] = $qty;
			$data["wt_rate"] = $product["product_price"];
			$data["wt_rateReflective"] = $reflectiveRate;
			$data["wt_type"] = 2; //Sale (Online)
			
			return hewr_addWarehouseTransactions($product_id, $qty, $product["product_price"], $reflectiveRate, $data);
		}
	}
}

/**
 * @author   Cloudwebs
 * @abstract placeOrder common root to process order: for both front side and admin panel 
 */
function cart_hlp_placeOrder( $is_front_end, $resArr )
{
	$CI =& get_instance(); 
	$customer_id = cart_hlp_getCustOrdId( $is_front_end ); 

	/**
	 * added on 23-04-2015 to satisfy task 5 of BUG 403. 
	 * change with precaution applicable for both front end and admin panel. 
	 */
	if( isEmptyArr( $resArr['cartArr'][ $resArr['customer_id'] ] ) )
	{
		setFlashMessage('error',"Something wrong happen. It seems that your cart is empty or product is sold out.");
		if( is_restClient() )
		{
			rest_redirect("cart", "");
			$_rest_redirectData["type"] = "_redirect";
			return $_rest_redirectData;
		}
		else
		{
			redirect('cart');
		}
	}

	/**
	 * added on 22-05-2015 to satisfy task 6 of BUG 403.
	 * change with precaution applicable for both front end and admin panel.
	 */
	if( empty( $resArr['order_total_amt'] ) )
	{
		setFlashMessage('error',"Something wrong happen. It seems that your order payable amount is 0.");
		if( is_restClient() )
		{
			rest_redirect("cart", "");
			$_rest_redirectData["type"] = "_redirect";
			return $_rest_redirectData;
		}
		else
		{
			redirect('cart');
		}
	}
	
	
	if( $is_front_end )
	{
		$is_order_okay = true;
		$retResArr = array();
		
		$transaction_id = getTransactionID();
		
		//payment_method_id deprecated for orders table it was now stored and handled by order_transaction table
		$resPay = $CI->db->query( " SELECT payment_method_id, payment_method_name, payment_method_key 
									FROM payment_method 
									WHERE payment_method_id=".$resArr['payment_method_id']." " )->row_array(); 
		
		/**
		 * other_charges
		 */
		
		
		$shipping_charge = 0;
		if( isset($resArr["other_charges"]) )
		{
			if( $resArr["other_charges"] )
			{
				foreach( $resArr["other_charges"] as $k=>$ar )
				{
					$resArr['order_total_amt'] += $$ar["name"] = $ar["value"];
					
				}
			}
		}
		
		//in order order wise tax,shipping , handling , order_expected_delivery_date are currently not saved
		$resOrdArr = cart_hlp_mdl_placeOrder( $is_front_end, @$resArr['coupon_id'], $resArr['order_total_qty'], 
											  (float)$resArr['order_subtotal_amt'], (float)$resArr['order_discount_amount'], (float)$resArr['order_total_amt'], 
											  $resArr['customer_shipping_address_id'], $resArr['customer_billing_address_id'],
											  $resArr['shipping_method_id'], $transaction_id, $resPay['payment_method_id'], 
											  0, 0, $resArr['customer_note'], $resArr['order_is_gift_wrap'], $shipping_charge ); 

// 		$resDate = $CI->db->query("SELECT DATE_FORMAT(order_created_date, '%d-%m-%Y') as order_created_date FROM orders WHERE order_id=".$resOrdArr['order_id']." ")
// 						  ->row_array();
// 		$resOrdArr['order_created_date'] = $resDate['order_created_date'];
		$resDate = $CI->db->query("SELECT order_created_date FROM orders WHERE order_id=".$resOrdArr['order_id']." ")->row_array();
		$resOrdArr['order_created_date'] = formatDate( "d-m-Y <b>h:i:s A</b>", $resDate['order_created_date'] );
		
										 
		$CI->session->set_userdata( array( 'order_id' => $resOrdArr['order_id'] ) ); 
		if( $resOrdArr['type'] == 'success' ) 
		{
			//order tracking data
			$data_order_tracking['order_id'] = $resOrdArr['order_id']; 
			$order_plac_status_id = getField('order_status_id','order_status','order_status_key','ORD_PLC'); 
			$order_pending_status_id = getField('order_status_id','order_status','order_status_key','ORDER_PENDING');
			$cod_mode_status_id = getField('order_status_id','order_status','order_status_key','COD_MODE'); 
			
			$productinfo = '';	//$productinfo for payment gateway 
			foreach($resArr['cartArr'][ $resArr['customer_id'] ] as $k=>$ar) 
			{
				if( !isset($resArr['cart_prod'][ $k ]['not_available']) ) 
				{
					if( empty( $ar['type'] ) || $ar['type'] == 'prod' )
					{
						$productinfo .= $resArr['cart_prod'][ $k ]['product_name']." - ";
						
						$resProdPrice = getProdPricesAndTax($k);
						//in order details all parameters that are used in price calculation are stored , however weight param are not stored separatel for that order module still dependant on product database
						//product wise tax, labour_charge, company_charge currently not applied to product price so in order details it is not stored now.
						//however labour charge statically applied 
						//Note: store all above factors product wise if applied to product price 
						
						//echo " resProdPrice ";pr($resProdPrice);
						
						$resOrdDet = array();
						
						if( !empty( $resProdPrice ) ) 
						{
							/**
							 * warehouse transaction
							 */
							$warehouse_transactions_id = cart_hlp_warehouseTransaction($is_front_end, $resArr['cart_prod'][ $k ]["inventory_type_id"], 0, $resArr['cart_prod'][ $k ]["product_id"], -$ar['qty'], $resArr['cart_prod'][ $k ]['product_discounted_price']);
							
							$ring_size_name = $resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ar['ring_size'];
							if( hewr_isJewelryInventoryCheckWithId( $resArr['cart_prod'][ $k ]["inventory_type_id"] ) )
							{
								if($resArr['cart_prod'][ $k ]['ring_size_region'] == 'Y')
								{
									$ring_size_name = getRingSizeName( $ar['ring_size'] );
									//to be compatible with order invoive mail template: after order and shipping mail
									$resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ring_size_name;
								}
							}
							
							$resOrdDet[$k] = placeOrderDetails(0, $resOrdArr['order_id'], $resArr['cart_prod'][ $k ]['product_id'], $resArr['cart_prod'][ $k ]['product_generated_code'], 
												@$resProdPrice['mp_price_difference'], @$resProdPrice['dp_calculated_cost_cs'], 
												@$resProdPrice['dp_calculated_cost_ss1'], @$resProdPrice['dp_calculated_cost_ss2'], 
												0, $k, '', '', $ring_size_name, 
												0, round($resProdPrice['product_price_weight'] * LAB_STA_CHARGE, 2), (SHIPP_CHARGE + (int)$resProdPrice['product_shipping_cost']), 
												$resProdPrice['product_cod_cost'], PACK_CHARGE, 
												$ar['qty'], 
												0, 0, PAY_GATE_CHARGE, VAT_CHARGE, 
												$resArr['cart_prod'][ $k ]['product_price_calculated_price'], $resArr['cart_prod'][ $k ]['product_discount'], 
												$resArr['cart_prod'][ $k ]['product_discounted_price'], 
												0, 0, date("Y-m-d H:i:s", $resArr['cart_prod'][ $k ]['order_details_expected_delivery_date_org'] ), '', '', 'prod', $warehouse_transactions_id ); 
	
							$data_order_tracking['order_details_id'] = $resOrdDet[$k]['order_details_id'];
							if( $resArr['payment_method_id'] != 4 ) 
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];								
								cart_hlp_orderTracking($data_order_tracking); 
								
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_pending_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];								
								cart_hlp_orderTracking($data_order_tracking); 
							}
							else
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
								
								//COD mode tracking entry
								$data_order_tracking['order_status_id'] = $cod_mode_status_id;
								cart_hlp_orderTracking($data_order_tracking);
							}
						}
						else
						{
							$is_order_okay = false;
							//log
							
							//transaction re commit
								
							setFlashMessage('error',"Something wrong happen.Contact ". getField("config_value", "configuration", "config_key", "SUPPORT_EMAIL") .", Order ID: ".$resOrdArr['order_id']."");	
							if( is_restClient() )
							{
								rest_redirect("orderFailed", "");
								$_rest_redirectData["type"] = "_redirect";
								return $_rest_redirectData;
							}
							else
							{
								redirect('checkout/orderFailed');
							}
						}
					}
					else if( $ar['type'] ==  'cz' ) 
					{
						/**
						 * warehouse transaction
						 */
						$warehouse_transactions_id = cart_hlp_warehouseTransaction($is_front_end, $resArr['cart_prod'][ $k ]["inventory_type_id"], 0, $resArr['cart_prod'][ $k ]["product_id"], -$ar['qty'], $resArr['cart_prod'][ $k ]['product_discounted_price']);
					
						$productinfo .= $resArr['cart_prod'][ $k ]['product_name']." - ";
						
						$resProdPrice = getProdPricesAndTax($k, 'cz', $resArr['cart_prod'][ $k ]);
						//in order details all parameters that are used in price calculation are stored , however weight param are not stored separatel for that order module still dependant on product database
						//product wise tax, labour_charge, company_charge currently not applied to product price so in order details it is not stored now.
						//however labour charge statically applied 
						//Note: store all above factors product wise if applied to product price 
						
						$resOrdDet = array();
						if(!empty($resProdPrice))
						{
							$ring_size_name = $resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ar['ring_size'];
							if($resArr['cart_prod'][ $k ]['ring_size_region'] == 'Y')
							{
								$ring_size_name = getRingSizeName( $ar['ring_size'] );
								//to be compatible with order invoive mail template: after order and shipping mail
								$resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ring_size_name;
							}
							
							$resOrdDet[$k] = placeOrderDetails(0, $resOrdArr['order_id'], $resArr['cart_prod'][ $k ]['product_id'], $resArr['cart_prod'][ $k ]['product_generated_code'], 
												@$resProdPrice['mp_price_difference'], @$resProdPrice['dp_calculated_cost_cs'], 
												@$resProdPrice['dp_calculated_cost_ss1'], @$resProdPrice['dp_calculated_cost_ss2'], 
												0, $k, '', '', $ring_size_name, 
												0, round($resProdPrice['product_price_weight'] * LAB_STA_CHARGE, 2), (SHIPP_CHARGE + (int)$resProdPrice['product_shipping_cost']), 
												$resProdPrice['product_cod_cost'], PACK_CHARGE, 
												$ar['qty'], 
												0, 0, PAY_GATE_CHARGE, VAT_CHARGE, 
												$resArr['cart_prod'][ $k ]['product_price_calculated_price'], $resArr['cart_prod'][ $k ]['product_discount'], 
												$resArr['cart_prod'][ $k ]['product_discounted_price'], 
												0, 0, date("Y-m-d H:i:s", $resArr['cart_prod'][ $k ]['order_details_expected_delivery_date_org'] ), '', '', $ar['type'], $warehouse_transactions_id );
	
							$data_order_tracking['order_details_id'] = $resOrdDet[$k]['order_details_id'];
							if($resArr['payment_method_id']!=4)
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
								
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_pending_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
							}
							else
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
	
								//COD mode tracking entry
								$data_order_tracking['order_status_id'] = $cod_mode_status_id;
								cart_hlp_orderTracking($data_order_tracking);
								
							}
				
	
						}
						else
						{
							$is_order_okay = false;
							//log
							
							
							//transaction re commit
								
							setFlashMessage('error',"Something wrong happen.Contact ".getField("config_value", "configuration", "config_key", "SUPPORT_EMAIL").", Order ID: ".$resOrdArr['order_id']."");	
							if( is_restClient() )
							{
								rest_redirect("orderFailed", "");
								$_rest_redirectData["type"] = "_redirect";
								return $_rest_redirectData;
							}
							else
							{
								redirect('checkout/orderFailed');
							}
													
						}
					}
					else if( $ar['type'] == 'sol' )
					{
						/**
						 * warehouse transaction
						 */
						$warehouse_transactions_id = cart_hlp_warehouseTransaction($is_front_end, $resArr['cart_prod'][ $k ]["inventory_type_id"], 0, $resArr['cart_prod'][ $k ]["product_id"], -$ar['qty'], $resArr['cart_prod'][ $k ]['product_discounted_price']);
						
						$productinfo .= $resArr['cart_prod'][ $k ]['product_name']." - ";
						
						$resProdPrice = getProdPricesAndTax($k);
						//in order details all parameters that are used in price calculation are stored , however weight param are not stored separatel for that order module still dependant on product database
						//product wise tax, labour_charge, company_charge currently not applied to product price so in order details it is not stored now.
						//however labour charge statically applied 
						//Note: store all above factors product wise if applied to product price 
						
						$resOrdDet = array();
						if(!empty($resProdPrice))
						{
							$ring_size_name = $resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ar['ring_size'];
							if($resArr['cart_prod'][ $k ]['ring_size_region'] == 'Y')
							{
								$ring_size_name = getRingSizeName( $ar['ring_size'] );
								//to be compatible with order invoive mail template: after order and shipping mail
								$resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ring_size_name;
							}
							
							$diamond_price_idTemp = ''; $dp_priceTemp = ''; 
							if( isset( $resArr['cart_prod'][ $k ][ 'd_detail' ] ) && is_array( $resArr['cart_prod'][ $k ][ 'd_detail' ] ) ) 
							{
								foreach( $resArr['cart_prod'][ $k ][ 'd_detail' ] as $did=>$dArr )	
								{
									$diamond_price_idTemp .= $did.'|';	
									$dp_priceTemp .= $dArr['dp_price'].'|';									
								}
								$diamond_price_idTemp = substr($diamond_price_idTemp, 0, -1); $dp_priceTemp = substr($dp_priceTemp, 0, -1);
							}
							
							$resOrdDet[$k] = placeOrderDetails(0, $resOrdArr['order_id'], $resArr['cart_prod'][ $k ]['product_id'], $resArr['cart_prod'][ $k ]['product_generated_code'], 
												@$resProdPrice['mp_price_difference'], @$resProdPrice['dp_calculated_cost_cs'], 
												@$resProdPrice['dp_calculated_cost_ss1'], @$resProdPrice['dp_calculated_cost_ss2'], 
												0, $k, '', '', $ring_size_name, 
												0, round($resProdPrice['product_price_weight'] * LAB_STA_CHARGE, 2), (SHIPP_CHARGE + (int)$resProdPrice['product_shipping_cost']),
												$resProdPrice['product_cod_cost'], PACK_CHARGE, 
												$ar['qty'], 
												0, 0, PAY_GATE_CHARGE, VAT_CHARGE, 
												$resArr['cart_prod'][ $k ]['product_price_calculated_price'], $resArr['cart_prod'][ $k ]['product_discount'], 
												$resArr['cart_prod'][ $k ]['product_discounted_price'], 
												0, 0, date("Y-m-d H:i:s", $resArr['cart_prod'][ $k ]['order_details_expected_delivery_date_org'] ) , 
												$diamond_price_idTemp, $dp_priceTemp, $ar['type'], $warehouse_transactions_id );
	
							$data_order_tracking['order_details_id'] = $resOrdDet[$k]['order_details_id'];
							if($resArr['payment_method_id']!=4)
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
								
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_pending_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
							}
							else
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
	
								//COD mode tracking entry
								$data_order_tracking['order_status_id'] = $cod_mode_status_id;
								cart_hlp_orderTracking($data_order_tracking);
								
							}
						}
						else
						{
							$is_order_okay = false;
							//log
							
							
							//transaction re commit
								
							setFlashMessage('error',"Something wrong happen.Contact ".getField("config_value", "configuration", "config_key", "SUPPORT_EMAIL").", Order ID: ".$resOrdArr['order_id']."");	
							if( is_restClient() )
							{
								rest_redirect("orderFailed", "");
								$_rest_redirectData["type"] = "_redirect";
								return $_rest_redirectData;
							}
							else
							{
								redirect('checkout/orderFailed');
							}
													
						}
					}
					else if( $ar['type'] == 'dia' ) 
					{
						/**
						 * warehouse transaction
						 */
						$warehouse_transactions_id = cart_hlp_warehouseTransaction($is_front_end, $resArr['cart_prod'][ $k ]["inventory_type_id"], 0, $resArr['cart_prod'][ $k ]["product_id"], -$ar['qty'], $resArr['cart_prod'][ $k ]['product_discounted_price']);
						
						$productinfo .= $resArr['cart_prod'][ $k ]['d_detail'][ $k ][ 'diamond_shape_name' ]." - ";
						$resOrdDet = array();
						
						//for diamond order expected delivery date is +7 days to current date
						$resOrdDet[$k] = placeOrderDetails(0, $resOrdArr['order_id'], 0, '', 
											0, 0, 
											0, 0, 
											0, 0, '', '', '', 
											SOL_COMM, 0, 0,
											0, 0, 
											$ar['qty'], 
											0, 0, SOL_PAY_GATE_CHARGE, SOL_VAT_CHARGE, 
											0, 0, 
											0, 
											0, 0, date("Y-m-d H:i:s", strtotime("+7 day") ), $resArr['cart_prod'][ $k ]['d_detail'][ $k ][ 'diamond_price_id' ], 
											$resArr['cart_prod'][ $k ]['d_detail'][ $k ][ 'dp_price' ] , $ar['type'], $warehouse_transactions_id );

						$data_order_tracking['order_details_id'] = $resOrdDet[$k]['order_details_id'];
						if($resArr['payment_method_id']!=4)
						{
							//order placed tracking entry
							$data_order_tracking['order_status_id'] = $order_plac_status_id;
							$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
							cart_hlp_orderTracking($data_order_tracking);
							
							//order placed tracking entry
							$data_order_tracking['order_status_id'] = $order_pending_status_id;
							$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
							cart_hlp_orderTracking($data_order_tracking);
						}
						else
						{
							//order placed tracking entry
							$data_order_tracking['order_status_id'] = $order_plac_status_id;
							$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
							cart_hlp_orderTracking($data_order_tracking);

							//COD mode tracking entry
							$data_order_tracking['order_status_id'] = $cod_mode_status_id;
							cart_hlp_orderTracking($data_order_tracking);
							
						}
					}
				}
				else
				{
					$is_order_okay = false;
					//log
					errorLog( 'ORDER_FAIL_PROD_NOT_AVAILABLE', ' A product with product_price_id: '.$k.' is not available for which product type is: '.$ar['type'] );
					
					//transaction re commit
						
					setFlashMessage('error',"Something wrong happen.Contact ".getField("config_value", "configuration", "config_key", "SUPPORT_EMAIL").", Order ID: ".$resOrdArr['order_id']."");	
					if( is_restClient() )
					{
						rest_redirect("orderFailed", "");
						$_rest_redirectData["type"] = "_redirect";
						return $_rest_redirectData;
					}
					else
					{
						redirect('checkout/orderFailed');
					}
												
				}
			}
	
			//payment or otherwise COD
			if($is_order_okay)
			{
				if($resArr['payment_method_id']==4)	//COD
				{
					//insert transaction entry as COD	=>Note:All COD transaction are handled by Cloudwebs and only COD mode and status is stored for COD transactions for more detail refere to UML=> Order Transactions
					$data_transaction['order_id'] = $resOrdArr['order_id'];
					$data_transaction['transaction_id'] = $transaction_id; //getTransactionID();
					$data_transaction['currency_id'] = CURRENCY_ID;
					
					if(!empty($resPay))
					{
						$data_transaction['payment_method_id'] = $resPay['payment_method_id'];
						$resArr['payment_method_name'] = $resPay['payment_method_name'];
					}
					
					$data_transaction['payment_mode'] = 'COD';
					$data_transaction['payment_status'] = $resArr['payment_status'] = 'Cash On Delivery';
					orderTransaction($data_transaction);
				
					$retResArr = cart_hlp_orderConfirmMail( true, array_merge( $resArr, $resOrdArr ) ); 
					if($retResArr['type']=='success')
					{
						if( is_restClient() )
						{
							rest_redirect("thankyou", "oid=".$retResArr['order_id']);
							$_rest_redirectData["type"] = "_redirect"; 
							return $_rest_redirectData;
						}
						else
						{
							redirect('checkout/thankyou?oid='.$retResArr['order_id']);
						}
					}
				}
				else if($resArr['payment_method_id']==6)	//BUCKS
				{
					//check if product are under allowed BUCKs category
					$bucks_allowed_category_id = getField("category_id", "product_categories", "category_alias", "special-offers");
					foreach($resArr['cartArr'][ $resArr['customer_id'] ] as $k=>$ar)
					{
						if( !checkIfRowExist( "SELECT 1 FROM product_category_map WHERE product_id=".$resArr['cart_prod'][ $k ]['product_id']."
											  AND category_id=".$bucks_allowed_category_id." " ) )
						{
							//better if "COMMIT TRANSACTION" used so that admin don't need to cancel failed order
							
							setFlashMessage('error',"Only products which are under Special Offers section can be purchased using BUCK credit balance. Order ID: ".$resOrdArr['order_id'].""); 
							if( is_restClient() )
							{
								rest_redirect("orderFailed", "");
								$_rest_redirectData["type"] = "_redirect";
								return $_rest_redirectData;
							}
							else
							{
								redirect('checkout/orderFailed');
							}
					
						}
					}
					
					
					if( $resArr['order_total_amt'] <= getCustBalance( $resArr['customer_id'] ) )
					{
						//debit as per order details product
						hecam_bucksTransaction(true, 0, $resArr['customer_id'], $resOrdArr['order_id'], 0, 
											   0, $resArr['order_total_amt'], 2); 
						
							
						//insert transaction entry as COD	=>Note:All COD transaction are handled by Cloudwebs and only COD mode and status is stored for COD transactions for more detail refere to UML=> Order Transactions
						$data_transaction['order_id'] = $resOrdArr['order_id'];
						$data_transaction['transaction_id'] = $transaction_id; //getTransactionID();
						$data_transaction['currency_id'] = CURRENCY_ID;
							
						if(!empty($resPay))
						{
							$data_transaction['payment_method_id'] = $resPay['payment_method_id'];
							$resArr['payment_method_name'] = $resPay['payment_method_name'];
						}
							
						$data_transaction['payment_mode'] = 'BUCKS';
						$data_transaction['payment_status'] = $resArr['payment_status'] = 'Credit Bucks';
						orderTransaction($data_transaction);
						
						$retResArr = cart_hlp_orderConfirmMail( true, array_merge( $resArr, $resOrdArr ) );
						if($retResArr['type']=='success')
						{
							if( is_restClient() )
							{
								rest_redirect("thankyou", "oid=".$retResArr['order_id']);
								$_rest_redirectData["type"] = "_redirect";
								return $_rest_redirectData;
							}
							else
							{
								redirect('checkout/thankyou?oid='.$retResArr['order_id']);
							}
						}	
					}
					else 
					{
						//better if "COMMIT TRANSACTION" used so that admin don't need to cancel failed order
							
						
						setFlashMessage('error',"You have not enough BUCKs credit in your account. Order ID: ".$resOrdArr['order_id']."");
						if( is_restClient() )
						{
							rest_redirect("orderFailed", "");
							$_rest_redirectData["type"] = "_redirect";
							return $_rest_redirectData;
						}
						else
						{
							redirect('checkout/orderFailed');
						}
						
					}
					
				}
				else	//redirect to payment gateWays: payU
				{
					unset($_POST['payment_method_id']);unset($_POST['agree']);unset($_POST['proceed']);
					
					/**
					 * On 17-06-2015 set cancel url, for explicit cancel order processing
					 * Also set surl and furl for better control in library. 
					 */
					$restParamsTemp = ""; 
					if( is_restClient() )
					{
						$restParamsTemp = "&".
										  getSysConfig( "PHPSESSID" )."=".$CI->input->get(getSysConfig( "PHPSESSID" ))."&".
										  getSysConfig( "rest_version_index" )."=".$CI->input->get(getSysConfig( "rest_version_index" ))."&".
										  "format=".$CI->input->get( "format" ); 
					}
					$_POST['surl'] = site_url('checkout/orderSuccess?oid='.$resOrdArr['order_id'].$restParamsTemp);
					$_POST['furl'] = site_url('checkout/orderFailed?oid='.$resOrdArr['order_id'].$restParamsTemp);
					$_POST['curl'] = site_url('checkout/orderCanceled?oid='.$resOrdArr['order_id'].$restParamsTemp);
					$restParamsTemp = null;	//free memory
					
					
					$_POST['txnid'] = $transaction_id;
					$_POST['amount'] = lp_base( $resArr['order_total_amt'] );	//convert to base currency
					$_POST['email'] = $resArr['customer_emailid'];

					$resCust = $CI->db->query( " SELECT customer_firstname, customer_phoneno FROM customer WHERE customer_id=".$customer_id." " )->row_array(); 
					$_POST['firstname'] = $resCust['customer_firstname'];
					$_POST['phone'] = $resCust['customer_phoneno'];
					$_POST['productinfo'] = substr($productinfo, 0, -2);
				
					if( $resPay['payment_method_key'] == 'PAY_U' )
					{
						$_POST['amount'] = $resArr['order_total_amt'];	//for payU pay only in INR until multy currecny is not enabled

						$CI->load->view('payuform');
					}
					else if( $resPay['payment_method_key'] == 'PAYPAL' )
					{
						$_POST['quantity'] = $resArr['order_total_qty'];
						$_POST['currency_id'] = CURRENCY_ID;
						$_POST['shippAddr'] = getAddress( $resArr['customer_shipping_address_id'] );
						
						//store transaction entry that indicates PayPal transaction is initiated
						$data_transaction['order_id'] = $resOrdArr['order_id'];
						$data_transaction['payment_method_id'] = $resPay['payment_method_id'];
						$data_transaction['currency_id'] = CURRENCY_ID; 
						$data_transaction['transaction_id'] = $transaction_id;
						$data_transaction['payment_status'] = 'Transaction initiated';
						
						orderTransaction( $data_transaction );
						
						$CI->load->view('paypal_form');
					}
				}
			}
			
		}
	}
	else
	{
		$is_order_okay = true;
		$retResArr = array();
		
		$transaction_id = getTransactionID();
		
		$resPay['payment_method_id'] = $resArr['payment_method_id']; 
		
		//weird
		$resPay = $CI->db->query("SELECT payment_method_id, payment_method_name, payment_method_key FROM payment_method WHERE payment_method_id=".$resPay['payment_method_id']." ")
					 ->row_array();
		
		//other_charges
		$shipping_charge = 0;
		if( $resArr["other_charges"] )
		{
			foreach( $resArr["other_charges"] as $k=>$ar )
			{
				$resArr['order_total_amt'] += $$ar["name"] = $ar["value"];
			}
		}
				
		//in order: order wise tax,shipping , handling , order_expected_delivery_date are currently not saved
		$resOrdArr = cart_hlp_mdl_placeOrder( $is_front_end, @$resArr['coupon_id'], $resArr['order_total_qty'], 
											(float)$resArr['order_subtotal_amt'], (float)$resArr['order_discount_amount'], (float)$resArr['order_total_amt'], 
											 $resArr['customer_shipping_address_id'], $resArr['customer_billing_address_id'],
											 $resArr['shipping_method_id'], $transaction_id, $resPay['payment_method_id'], 
											 0, 0, $resArr['customer_note'], $resArr['order_is_gift_wrap'], $shipping_charge );
// 		$resDate = $CI->db->query("SELECT DATE_FORMAT(order_created_date, '%d-%m-%Y') as order_created_date FROM orders WHERE order_id=".$resOrdArr['order_id']." ")->row_array();
// 		$resOrdArr['order_created_date'] = $resDate['order_created_date'];
		$resDate = $CI->db->query("SELECT order_created_date FROM orders WHERE order_id=".$resOrdArr['order_id']." ")->row_array();
		$resOrdArr['order_created_date'] = formatDate( "d-m-Y <b>h:i:s A</b>", $resDate['order_created_date'] );
		
		
										 
		$CI->session->set_userdata( array('adm_order_id' => $resOrdArr['order_id']) );
										 
		if( $resOrdArr['type'] == 'success' )
		{
			//order tracking data
			$data_order_tracking['order_id'] = $resOrdArr['order_id'];
			$order_plac_status_id = getField('order_status_id','order_status','order_status_key','ORD_PLC');
			$cod_mode_status_id = getField('order_status_id','order_status','order_status_key','COD_MODE');
			
			//$productinfo for payment gateway
			$productinfo = '';
			foreach($resArr['cartArr'][ $resArr['customer_id'] ] as $k=>$ar)
			{
				if( !isset($resArr['cart_prod'][ $k ]['not_available']) )
				{
					if( empty( $ar['type'] ) || $ar['type'] == 'prod' )
					{
						$productinfo .= $resArr['cart_prod'][ $k ]['product_name']." - ";
						
						$resProdPrice = getProdPricesAndTax($k);
						//in order details all parameters that are used in price calculation are stored , however weight param are not stored separatel for that order module still dependant on product database
						//product wise tax, labour_charge, company_charge currently not applied to product price so in order details it is not stored now.
						//however labour charge statically applied 
						//Note: store all above factors product wise if applied to product price 
						
						$resOrdDet = array();
						if(!empty($resProdPrice))
						{
							/**
							 * warehouse transaction
							 */
							$warehouse_transactions_id = cart_hlp_warehouseTransaction($is_front_end, $resArr['cart_prod'][ $k ]["inventory_type_id"], 0, $resArr['cart_prod'][ $k ]["product_id"], -$ar['qty'], $resArr['cart_prod'][ $k ]['product_discounted_price']);
								
							$ring_size_name = $resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ar['ring_size'];
							if($resArr['cart_prod'][ $k ]['ring_size_region'] == 'Y')
							{
								$ring_size_name = getRingSizeName( $ar['ring_size'] );
								//to be compatible with order invoive mail template: after order and shipping mail
								$resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ring_size_name;
							}
							
							$resOrdDet[$k] = placeOrderDetails(0, $resOrdArr['order_id'], $resArr['cart_prod'][ $k ]['product_id'], $resArr['cart_prod'][ $k ]['product_generated_code'], 
												$resProdPrice['mp_price_difference'], @$resProdPrice['dp_calculated_cost_cs'], 
												@$resProdPrice['dp_calculated_cost_ss1'], @$resProdPrice['dp_calculated_cost_ss2'], 
												0, $k, '', '', $ring_size_name, 
												0, round($resProdPrice['product_price_weight'] * LAB_STA_CHARGE, 2), (SHIPP_CHARGE + (int)$resProdPrice['product_shipping_cost']),
												$resProdPrice['product_cod_cost'], PACK_CHARGE, 
												$ar['qty'], 
												0, 0, PAY_GATE_CHARGE, VAT_CHARGE, 
												$resArr['cart_prod'][ $k ]['product_price_calculated_price'], $resArr['cart_prod'][ $k ]['product_discount'], 
												$resArr['cart_prod'][ $k ]['product_discounted_price'], 
												0, 0, date("Y-m-d H:i:s", $resArr['cart_prod'][ $k ]['order_details_expected_delivery_date_org'] ), '', '', 'prod', $warehouse_transactions_id );
	
							$data_order_tracking['order_details_id'] = $resOrdDet[$k]['order_details_id'];
							if($resPay['payment_method_key']!='COD')
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
							}
							else
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
	
								//COD mode tracking entry
								$data_order_tracking['order_status_id'] = $cod_mode_status_id;
								cart_hlp_orderTracking($data_order_tracking);
							}
	
						}
						else
						{
							$is_order_okay = false;
							//log
							
							//transaction re commit
							setFlashMessage( 'error', "Something wrong happen.Contact support@Stationery.com, Order ID: ".$resOrdArr['order_id']."" ); 
							redirect( 'admin/'.$CI->controller ); 
						}
					}
					else if( $ar['type'] ==  'cz' )
					{
						$productinfo .= $resArr['cart_prod'][ $k ]['product_name']." - ";
						
						$resProdPrice = getProdPricesAndTax( $k, 'cz', $resArr['cart_prod'][ $k ] );
						//in order details all parameters that are used in price calculation are stored , however weight param are not stored separatel for that order module still dependant on product database
						//product wise tax, labour_charge, company_charge currently not applied to product price so in order details it is not stored now.
						//however labour charge statically applied 
						//Note: store all above factors product wise if applied to product price 
						
						$resOrdDet = array();
						if(!empty($resProdPrice))
						{
							/**
							 * warehouse transaction
							 */
							$warehouse_transactions_id = cart_hlp_warehouseTransaction($is_front_end, $resArr['cart_prod'][ $k ]["inventory_type_id"], 0, $resArr['cart_prod'][ $k ]["product_id"], -$ar['qty'], $resArr['cart_prod'][ $k ]['product_discounted_price']);
								
							$ring_size_name = $resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ar['ring_size'];
							if($resArr['cart_prod'][ $k ]['ring_size_region'] == 'Y')
							{
								$ring_size_name = getRingSizeName( $ar['ring_size'] );
								//to be compatible with order invoive mail template: after order and shipping mail
								$resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ring_size_name;
							}
							
							$resOrdDet[$k] = placeOrderDetails(0, $resOrdArr['order_id'], $resArr['cart_prod'][ $k ]['product_id'], $resArr['cart_prod'][ $k ]['product_generated_code'], 
												$resProdPrice['mp_price_difference'], @$resProdPrice['dp_calculated_cost_cs'], 
												@$resProdPrice['dp_calculated_cost_ss1'], @$resProdPrice['dp_calculated_cost_ss2'], 
												0, $k, '', '', $ring_size_name, 
												0, round($resProdPrice['product_price_weight'] * LAB_STA_CHARGE, 2), (SHIPP_CHARGE + (int)$resProdPrice['product_shipping_cost']), 
												$resProdPrice['product_cod_cost'], PACK_CHARGE, 
												$ar['qty'], 
												0, 0, PAY_GATE_CHARGE, VAT_CHARGE, 
												$resArr['cart_prod'][ $k ]['product_price_calculated_price'], $resArr['cart_prod'][ $k ]['product_discount'], 
												$resArr['cart_prod'][ $k ]['product_discounted_price'], 
												0, 0, date("Y-m-d H:i:s", $resArr['cart_prod'][ $k ]['order_details_expected_delivery_date_org'] ), '', '', $ar['type'], $warehouse_transactions_id );
	
							$data_order_tracking['order_details_id'] = $resOrdDet[$k]['order_details_id'];
							if($resPay['payment_method_key']!='COD')
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
							}
							else
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
	
								//COD mode tracking entry
								$data_order_tracking['order_status_id'] = $cod_mode_status_id;
								cart_hlp_orderTracking($data_order_tracking);
								
							}
				
	
						}
						else
						{
							$is_order_okay = false;
							//log
							
							
							//transaction re commit
								
							setFlashMessage('error',"Something wrong happen.Contact support@Stationery.com, Order ID: ".$resOrdArr['order_id']."");	
							redirect( 'admin/'.$CI->controller );						
						}
					}
					else if( $ar['type'] == 'sol' )
					{
						$productinfo .= $resArr['cart_prod'][ $k ]['product_name']." - ";
						
						$resProdPrice = getProdPricesAndTax($k);
						//in order details all parameters that are used in price calculation are stored , however weight param are not stored separatel for that order module still dependant on product database
						//product wise tax, labour_charge, company_charge currently not applied to product price so in order details it is not stored now.
						//however labour charge statically applied 
						//Note: store all above factors product wise if applied to product price 
						
						$resOrdDet = array();
						if(!empty($resProdPrice))
						{
							/**
							 * warehouse transaction
							 */
							$warehouse_transactions_id = cart_hlp_warehouseTransaction($is_front_end, $resArr['cart_prod'][ $k ]["inventory_type_id"], 0, $resArr['cart_prod'][ $k ]["product_id"], -$ar['qty'], $resArr['cart_prod'][ $k ]['product_discounted_price']);
								
							$ring_size_name = $resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ar['ring_size'];
							if($resArr['cart_prod'][ $k ]['ring_size_region'] == 'Y')
							{
								$ring_size_name = getRingSizeName( $ar['ring_size'] );
								//to be compatible with order invoive mail template: after order and shipping mail
								$resArr['cart_prod'][ $k ][ 'order_details_ring_size' ] = $ring_size_name;
							}
							
							$diamond_price_idTemp = ''; $dp_priceTemp = ''; 
							if( isset( $resArr['cart_prod'][ $k ][ 'd_detail' ] ) && is_array( $resArr['cart_prod'][ $k ][ 'd_detail' ] ) ) 
							{
								foreach( $resArr['cart_prod'][ $k ][ 'd_detail' ] as $did=>$dArr )	
								{
									$diamond_price_idTemp .= $did.'|';	
									$dp_priceTemp .= $dArr['dp_price'].'|';									
								}
								$diamond_price_idTemp = substr($diamond_price_idTemp, 0, -1); $dp_priceTemp = substr($dp_priceTemp, 0, -1);
							}
							
							$resOrdDet[$k] = placeOrderDetails(0, $resOrdArr['order_id'], $resArr['cart_prod'][ $k ]['product_id'], $resArr['cart_prod'][ $k ]['product_generated_code'], 
												$resProdPrice['mp_price_difference'], @$resProdPrice['dp_calculated_cost_cs'], 
												@$resProdPrice['dp_calculated_cost_ss1'], @$resProdPrice['dp_calculated_cost_ss2'], 
												0, $k, '', '', $ring_size_name, 
												0, round($resProdPrice['product_price_weight'] * LAB_STA_CHARGE, 2), (SHIPP_CHARGE + (int)$resProdPrice['product_shipping_cost']),
												$resProdPrice['product_cod_cost'], PACK_CHARGE, 
												$ar['qty'], 
												0, 0, PAY_GATE_CHARGE, VAT_CHARGE, 
												$resArr['cart_prod'][ $k ]['product_price_calculated_price'], $resArr['cart_prod'][ $k ]['product_discount'], 
												$resArr['cart_prod'][ $k ]['product_discounted_price'], 
												0, 0, date("Y-m-d H:i:s", $resArr['cart_prod'][ $k ]['order_details_expected_delivery_date_org'] ) , 
												$diamond_price_idTemp, $dp_priceTemp, $ar['type'], $warehouse_transactions_id );
	
							$data_order_tracking['order_details_id'] = $resOrdDet[$k]['order_details_id'];
							if($resPay['payment_method_key']!='COD')
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
							}
							else
							{
								//order placed tracking entry
								$data_order_tracking['order_status_id'] = $order_plac_status_id;
								$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
								cart_hlp_orderTracking($data_order_tracking);
	
								//COD mode tracking entry
								$data_order_tracking['order_status_id'] = $cod_mode_status_id;
								cart_hlp_orderTracking($data_order_tracking);
								
							}
						}
						else
						{
							$is_order_okay = false;
							//log
							
							
							//transaction re commit
								
							setFlashMessage('error',"Something wrong happen.Contact support@Stationery.com, Order ID: ".$resOrdArr['order_id']."");	
							redirect( 'admin/'.$CI->controller );						
						}
					}
					else if( $ar['type'] == 'dia' ) 
					{
						/**
						 * warehouse transaction
						 */
						$warehouse_transactions_id = cart_hlp_warehouseTransaction($is_front_end, $resArr['cart_prod'][ $k ]["inventory_type_id"], 0, $resArr['cart_prod'][ $k ]["product_id"], -$ar['qty'], $resArr['cart_prod'][ $k ]['product_discounted_price']);
						
						$productinfo .= $resArr['cart_prod'][ $k ]['d_detail'][ $k ][ 'diamond_shape_name' ]." - ";
						$resOrdDet = array();
						
						//for diamond order expected delivery date is +7 days to current date
						$resOrdDet[$k] = placeOrderDetails(0, $resOrdArr['order_id'], 0, '', 
											0, 0, 
											0, 0, 
											0, 0, '', '', '', 
											SOL_COMM, 0, 0,
											0, 0, 
											$ar['qty'], 
											0, 0, SOL_PAY_GATE_CHARGE, SOL_VAT_CHARGE, 
											0, 0, 
											0, 
											0, 0, date("Y-m-d H:i:s", strtotime("+7 day") ), $resArr['cart_prod'][ $k ]['d_detail'][ $k ][ 'diamond_price_id' ], 
											$resArr['cart_prod'][ $k ]['d_detail'][ $k ][ 'dp_price' ] , $ar['type'], $warehouse_transactions_id );

						$data_order_tracking['order_details_id'] = $resOrdDet[$k]['order_details_id'];
						if($resPay['payment_method_key']!='COD')
						{
							//order placed tracking entry
							$data_order_tracking['order_status_id'] = $order_plac_status_id;
							$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
							cart_hlp_orderTracking($data_order_tracking);
						}
						else
						{
							//order placed tracking entry
							$data_order_tracking['order_status_id'] = $order_plac_status_id;
							$data_order_tracking['order_tracking_comment'] =  $resArr['customer_note'];
							cart_hlp_orderTracking($data_order_tracking);

							//COD mode tracking entry
							$data_order_tracking['order_status_id'] = $cod_mode_status_id;
							cart_hlp_orderTracking($data_order_tracking);
							
						}
					}
				}
			}
	
			if( $is_order_okay ) 
			{
				if( $resPay['payment_method_key'] == 'COD' ) 
				{
					//insert transaction entry as COD	=>Note:All COD transaction are handled by Cloudwebs and only COD mode and status is stored for COD transactions for more detail refere to UML=> Order Transactions
					$data_transaction['order_id'] = $resOrdArr['order_id'];
					$data_transaction['transaction_id'] =  $transaction_id; //getTransactionID();
					
					$data_transaction['payment_method_id'] = $resPay['payment_method_id'];
					$resArr['payment_method_name'] = $resPay['payment_method_name'];
					
					$data_transaction['payment_mode'] = 'COD';
					$data_transaction['payment_status'] = $resArr['payment_status'] = 'Cash On Delivery';
					orderTransaction($data_transaction);
				
					if(isset($resArr['email_confirm']) && (int)$resArr['email_confirm']==1)
					{
						$retResArr = cart_hlp_orderConfirmMail( $is_front_end, array_merge( $resArr, $resOrdArr, $resPay ) ); 
					}
					
					$retResArr['type'] = 'success';
					$retResArr['msg'] = 'Order placed successfully.';
											
				}
				else	//redirect to payment gateWays: payU
				{
					
					unset($_POST);
					$_POST['surl'] = site_url('admin/sales_order/orderSuccess?custid='._en($resArr['customer_id']));	//success URL for payment gateway
					$_POST['furl'] = site_url('admin/sales_order/orderFailed?custid='._en($resArr['customer_id']));	//failure URL for payment gateway
					$_POST['curl'] = site_url('admin/sales_order/salesOrderForm?custid='._en($resArr['customer_id']));	//cancel URL for payment gateway
					
					$_POST['txnid'] = $transaction_id; 
					$_POST['amount'] = lp_base( $resArr['order_total_amt'], 2, 0, true );	//convert to base currency 
					$_POST['email'] = $resArr['customer_emailid']; 

					$resCust = $CI->db->query("SELECT customer_firstname, customer_phoneno FROM customer WHERE customer_id=".$resArr['customer_id']." ")->row_array(); 
					$_POST['firstname'] = $resCust['customer_firstname']; 
					$_POST['phone'] = $resCust['customer_phoneno']; 
					$_POST['productinfo'] = substr($productinfo, 0, -2); 
				
					if( $resPay['payment_method_key'] == 'PAY_U' ) 
					{
						$retResArr['type'] = 'payU'; 
						$CI->load->view( 'payuform' ); 
					}
					else if( $resPay['payment_method_key'] == 'PAYPAL' ) 
					{
						$_POST['quantity'] = $resArr['order_total_qty'];
						$_POST['currency_id'] = CURRENCY_ID;
						$_POST['shippAddr'] = getAddress( $resArr['customer_shipping_address_id'] );
						
						//store transaction entry that indicates PayPal transaction is initiated
						$data_transaction['order_id'] = $resOrdArr['order_id'];
						$data_transaction['payment_method_id'] = $resArr['payment_method_id'];
						$data_transaction['currency_id'] = CURRENCY_ID; 
						$data_transaction['transaction_id'] = $transaction_id; 
						$data_transaction['payment_status'] = 'Transaction initiated'; 
						
						orderTransaction( $data_transaction ); 
						
						$CI->load->view( 'paypal_form' ); 
					}
				}
			}
			
		}
		
		return $retResArr;
	} 
}

/**
 * function will add product tracking status for order details row
 */
function addOrderTrackingStatuses( $resArr, $resOrdArr, $resOrdDet, $order_status_key, $customer_note="" )
{
	$CI =& get_instance(); 
	$order_status_id = getField('order_status_id','order_status','order_status_key', $order_status_key);
	$data_order_tracking['order_id'] = $resOrdArr['order_id']; 
	
	foreach($resArr['cartArr'][ $resArr['customer_id'] ] as $k=>$ar)
	{
		//order tracking entry
		$data_order_tracking['order_details_id'] = $resOrdDet[$k]['order_details_id'];
		$data_order_tracking['order_status_id'] = $order_status_id;
		$data_order_tracking['order_tracking_comment'] =  $customer_note;
		$CI->db->insert("order_tracking",$data_order_tracking);
	}
}

/**
 * @author   Cloudwebs
 * @abstract placeOrder model root: for both front side and admin panel 
 */
function cart_hlp_mdl_placeOrder( $is_front_end, $coupon_id, 
					$order_total_qty,  
					$order_subtotal_amt, $order_discount_amount, $order_total_amt, 
					$customer_shipping_address_id, $customer_billing_address_id, 
					$shipping_method_id, $transaction_id, $payment_method_id, 
					$account_number, $payment_response, $customer_note, $order_is_gift_wrap, $shipping_method_shipping_charge=0)
{
	
	$CI =& get_instance(); 
	$invoice_number = getInvoiceNum();
	$ip_address = $CI->input->ip_address();

	return placeOrder( 0, cart_hlp_getCustomerId( $is_front_end ), $coupon_id, $invoice_number, $order_total_qty, $order_subtotal_amt, $order_discount_amount, 0, 0, 
				  $shipping_method_shipping_charge, 0, $order_total_amt, $customer_shipping_address_id, $customer_billing_address_id, $shipping_method_id, 
				 $transaction_id, $payment_method_id, $account_number, $payment_response, $customer_note, $order_is_gift_wrap, $ip_address, 0 ); 

}

/**
 * @author   Cloudwebs
 * @abstract functoin will insert or update order
 */
function placeOrder($order_id, $customer_id, $coupon_id, $invoice_number, 
					$order_total_qty, 
					$order_subtotal_amt, $order_discount_amount, 
					$order_tax_percent, $order_tax_amt, $shipping_method_shipping_charge, $shipping_method_handling_charge, 
					$order_total_amt, 
					$customer_shipping_address_id, $customer_billing_address_id, 
					$shipping_method_id, $transaction_id, $payment_method_id, 
					$account_number, $payment_response, 
					$customer_note, $order_is_gift_wrap, $ip_address, $del_in)
{
	
	$CI =& get_instance();
	$resArr = array();
	$data = array();	
	
	$data['manufacturer_id'] = MANUFACTURER_ID;
	
	if(!empty($customer_id))
		$data['customer_id'] = $customer_id;
		
	if(!empty($coupon_id))
		$data['coupon_id'] = $coupon_id;

	if(!empty($invoice_number))
		$data['invoice_number'] = $invoice_number;

	if(!empty($order_total_qty))
		$data['order_total_qty'] = $order_total_qty;

	if(!empty($order_subtotal_amt))
		$data['order_subtotal_amt'] = $order_subtotal_amt;

	if(!empty($order_discount_amount))
		$data['order_discount_amount'] = $order_discount_amount;
		
	if(!empty($order_tax_percent))
		$data['order_tax_percent'] = $order_tax_percent;

	if(!empty($order_tax_amt))
		$data['order_tax_amt'] = $order_tax_amt;

	if(!empty($shipping_method_shipping_charge))
		$data['shipping_method_shipping_charge'] = $shipping_method_shipping_charge;

	if(!empty($shipping_method_handling_charge))
		$data['shipping_method_handling_charge'] = $shipping_method_handling_charge;

	if(!empty($order_total_amt))
		$data['order_total_amt'] = $order_total_amt;
		
	if(!empty($customer_shipping_address_id))
		$data['customer_shipping_address_id'] = $customer_shipping_address_id;

	if(!empty($customer_billing_address_id))
		$data['customer_billing_address_id'] = $customer_billing_address_id;

	if(!empty($shipping_method_id))
		$data['shipping_method_id'] = $shipping_method_id;

	if(!empty($transaction_id))
		$data['transaction_id'] = $transaction_id;

	if(!empty($payment_method_id))
		$data['payment_method_id'] = $payment_method_id;
		
	if(!empty($account_number))
		$data['account_number'] = $account_number;

	if(!empty($payment_response))
		$data['payment_response'] = $payment_response;

	if(!empty($customer_note))
		$data['customer_note'] = $customer_note;

	if(!empty($order_is_gift_wrap))
		$data['order_is_gift_wrap'] = $order_is_gift_wrap;

	if(!empty($ip_address))
		$data['ip_address'] = $ip_address;
		
	if(!empty($del_in))
		$data['del_in'] = $del_in;
		
	if($order_id==0)
	{
		//$data["order_created_date"] = mysqlTimestamp();
		$CI->db->insert('orders',$data);

		//order id and success msg
		$resArr['order_id'] = $CI->db->insert_id();
		
		if( isExplicitCreatedDate() )
		{
			//update created date
			$CI->db->where('order_id', $resArr['order_id']);
			$CI->db->update('orders', array( "order_created_date" => mysqlTimestamp() ) );
		}
		
		
		$resArr['type'] = 'success';
		$resArr['msg'] = 'Order placed successfully.';
	}
	else
	{
		$CI->db->where('order_id',$order_id);
		$CI->db->set('order_modified_date', 'NOW()', FALSE);
		$CI->db->update('orders',$data);

		//order id and success msg
		$resArr['order_id'] = $order_id;
		$resArr['type'] = 'success';
		$resArr['msg'] = 'Order updated successfully.';
	}		
	
	return $resArr;
}
	
/*
 * @author   Cloudwebs
 * @abstract functoin will insert or update order details
 */
function placeOrderDetails($order_details_id, $order_id, $product_id, $product_generate_code, 
							$metal_price, $cs_price, $ss1_price, $ss2_price, 
							$gift_id, $product_price_id, $product_engraving_text, $product_engraving_font, $order_details_ring_size, 
							$labour_charge, $labour_charge_static, $order_details_product_shipping_cost, $order_details_product_cod_cost, $order_details_packaging_cost, 
							$order_details_product_qty, 
							$order_details_product_tax, $company_profit, $order_details_payment_gateway_charge, $order_details_vat, 
							$order_details_product_price, $order_details_product_discount, $order_details_amt, 
							$order_details_is_returned, $order_details_return_quantity, $order_details_expected_delivery_date, 
							$diamond_price_id='', $dp_price='', $product_type='', $warehouse_transactions_id=0 )
{
	
	$CI =& get_instance();
	$resArr = array();
	$data = array();	
	
	if(!empty($order_id))
		$data['order_id'] = $order_id;
		
	if(!empty($product_id))
		$data['product_id'] = $product_id;

	if(!empty($warehouse_transactions_id))
		$data['warehouse_transactions_id'] = $warehouse_transactions_id;
	
	if(!empty($product_generate_code))
		$data['product_generate_code'] = $product_generate_code;

	if(!empty($metal_price))
		$data['metal_price'] = $metal_price;
		
	if(!empty($cs_price))
		$data['cs_price'] = $cs_price;

	if(!empty($ss1_price))
		$data['ss1_price'] = $ss1_price;

	if(!empty($ss2_price))
		$data['ss2_price'] = $ss2_price;

	if(!empty($gift_id))
		$data['gift_id'] = $gift_id;

	if(!empty($product_price_id))
		$data['product_price_id'] = $product_price_id;

	if(!empty($product_engraving_text))
		$data['product_engraving_text'] = $product_engraving_text;
		
	if(!empty($product_engraving_font))
		$data['product_engraving_font'] = $product_engraving_font;

	if(!empty($order_details_ring_size))
		$data['order_details_ring_size'] = $order_details_ring_size;

	if(!empty($labour_charge))
		$data['labour_charge'] = $labour_charge;

	if(!empty($labour_charge_static))
		$data['labour_charge_static'] = $labour_charge_static;

	if(!empty($order_details_product_shipping_cost))
		$data['order_details_product_shipping_cost'] = $order_details_product_shipping_cost;

	if(!empty($order_details_product_cod_cost))
		$data['order_details_product_cod_cost'] = $order_details_product_cod_cost;

	if(!empty($order_details_packaging_cost))
		$data['order_details_packaging_cost'] = $order_details_packaging_cost;

	if(!empty($order_details_product_qty))
		$data['order_details_product_qty'] = $order_details_product_qty;

	if(!empty($order_details_product_tax))
		$data['order_details_product_tax'] = $order_details_product_tax;

	if(!empty($company_profit))
		$data['company_profit'] = $company_profit;

	if(!empty($order_details_payment_gateway_charge))
		$data['order_details_payment_gateway_charge'] = $order_details_payment_gateway_charge;
		
	if(!empty($order_details_vat))
		$data['order_details_vat'] = $order_details_vat;

	if(!empty($order_details_product_price))
		$data['order_details_product_price'] = $order_details_product_price;

	if(!empty($order_details_product_discount))
		$data['order_details_product_discount'] = $order_details_product_discount;

	if(!empty($order_details_amt))
		$data['order_details_amt'] = $order_details_amt;

	if(!empty($order_details_is_returned))
		$data['order_details_is_returned'] = $order_details_is_returned;

	if(!empty($order_details_return_quantity))
		$data['order_details_return_quantity'] = $order_details_return_quantity;
		
	if(!empty($order_details_expected_delivery_date))
		$data['order_details_expected_delivery_date'] = $order_details_expected_delivery_date;

	if(!empty($diamond_price_id))
		$data['diamond_price_id'] = $diamond_price_id;
		
	if(!empty($dp_price))
		$data['dp_price'] = $dp_price;

	if(!empty($product_type))
		$data['product_type'] = $product_type;
		
	if($order_details_id==0)
	{
		//$data["order_details_created_date"] = mysqlTimestamp();
		$CI->db->insert('order_details',$data);

		//order id and success msg
		$resArr['order_details_id'] = $CI->db->insert_id();

		if( isExplicitCreatedDate() )
		{
			//update created date
			$CI->db->where('order_details_id', $resArr['order_details_id']);
			$CI->db->update('order_details', array( "order_details_created_date" => mysqlTimestamp() ) );
		}
		
		
		$resArr['type'] = 'success';
		$resArr['msg'] = 'Order detail placed successfully.';
	}
	else
	{
		$CI->db->where('order_details_id',$order_details_id);
		$CI->db->set('order_details_modified_date', 'NOW()', FALSE);
		$CI->db->update('order_details',$data);

		/*never delete : commented because at at time inventory not mmaintained will be used if inventory going to be maintained
		$pre_quantity = getField("order_details_product_qty","order_details","order_details_id",$order_details_id);
		updateProductQuantity($product_id, $order_details_product_qty - $pre_quantity);	//formula: current_quantity - previous_quantity */

		//order id and success msg
		$resArr['order_details_id'] = $order_details_id;
		$resArr['type'] = 'success';
		$resArr['msg'] = 'Order detail updated successfully.';
	}
	
	
	return $resArr;
}


/**
 * @author   Cloudwebs
 * @abstract functoin will insert order tracking
 */
function cart_hlp_orderTracking( $data )
{
	$CI =& get_instance();

	//$data["order_tracking_created_date"] = mysqlTimestamp();
	$CI->db->insert("order_tracking",$data);
	
	$last_id = $CI->db->insert_id();

	if( isExplicitCreatedDate() )
	{
		//update created date
		$CI->db->where('order_tracking_id', $last_id);
		$CI->db->update('order_tracking', array( "order_tracking_created_date" => mysqlTimestamp() ) );
	}
	
	
	return $last_id;
}


/*
+-----------------------------------------------+
	@author Cloudwebs
	@abstract function will add update order transaction
+-----------------------------------------------+
*/	
function orderTransaction( $data, $currency_id=0 ) 
{
	$CI =& get_instance();
	if( !empty( $currency_id ) )
	{
		if( !defined( 'CURRENCY_VALUE_'.$currency_id ) ) 
		{
			currencyConstant( $currency_id );	
		}
		$data['currency_value'] = constant( 'CURRENCY_VALUE_'.$currency_id );
	}
	else
	{
		$data['currency_value'] = CURRENCY_VALUE;
	}
	
	//$data["order_transaction_created_date"] = mysqlTimestamp();
	$CI->db->insert( "order_transaction", $data );
	
	$last_id = $CI->db->insert_id();
	
	if( isExplicitCreatedDate() )
	{
		//update created date
		$CI->db->where('order_transaction_id', $last_id);
		$CI->db->update('order_transaction', array( "order_transaction_created_date" => mysqlTimestamp() ) );
	}
	
	
	return array( 'type'=>'success', 'msg'=>'Transaction inserted successfully.', 'order_transaction_id'=>$last_id); 
}	


/**
 * added On 17-06-2015: explicit cancel order processing
 * @param string $is_front_end
 */
function cart_hlp_orderCanceled( $is_front_end=true )
{
	/**	
	 * cancel order processing
	 */
	$CI =& get_instance();
	
	if( $CI->session->userdata( 'order_id' ) !== FALSE )
	{
		$data_pay_res = $CI->input->post();
		cart_hlp_completeOrder( $is_front_end, $data_pay_res, 'CANCEL');
	}
	else
	{
		if( $is_front_end )
		{
			cart_hlp_handleNonSessionOrderQuery( $is_front_end, 'index' );
		}
		else
		{
			cart_hlp_handleNonSessionOrderQuery( $is_front_end, 'sales_order' );
		}
	}
}


/**
+-----------------------------------------------+
	User will be redirect here after successfull payment
	another url called from payment gateway.
+-----------------------------------------------+
*/	
function cart_hlp_orderSuccess( $is_front_end=true )
{
	
	$CI =& get_instance(); 
	
	if( $CI->session->userdata( 'order_id' ) !== FALSE ) 
	{
		$data_pay_res = $CI->input->post();
		cart_hlp_completeOrder( $is_front_end, $data_pay_res, 'S');  
	} 
	else 
	{
		if( $is_front_end ) 
		{
			cart_hlp_handleNonSessionOrderQuery( $is_front_end, 'thankyou' ); 
		}
		else 
		{
			cart_hlp_handleNonSessionOrderQuery( $is_front_end, 'sales_order' ); 
		}
	}
}
	
/*
+-----------------------------------------------+
	User will be redirect here after failed payment
	another url called from payment gateway.
+-----------------------------------------------+
*/	
function cart_hlp_orderFailed( $is_front_end=true )
{
	
	$CI =& get_instance(); 

	if( $CI->session->userdata( 'order_id' ) !== FALSE )
	{
		$data_pay_res = $CI->input->post();
		cart_hlp_completeOrder( $is_front_end, $data_pay_res, 'F');
	}
	else 
	{
		if( $is_front_end ) 
		{
			cart_hlp_handleNonSessionOrderQuery( $is_front_end, 'failure' ); 
		}
		else 
		{
			cart_hlp_handleNonSessionOrderQuery( $is_front_end, 'sales_order' ); 
		}
	}
}	
	
/**
 * @abstract Function is abstracted out here so that can be called from both orderSuccess and orderFailed functions when the requested order id is not in session in give user best experience if whether they are authorized to access order information or suggest them right user account to access order information
 * @param $redirect_uri: will redirect based on whether function is called from orderSuccess or orderFailure
 */	
function cart_hlp_handleNonSessionOrderQuery( $is_front_end, $redirect_uri )
{
	
	$CI =& get_instance(); 

	if( $is_front_end )
	{
		$data['oid'] = $CI->input->get('oid');
		
		//remove # from order_id if it is there
		if( strpos( " ".$data['oid'], "#" ) !== FALSE )
		{
			$data['oid'] = (int)substr( $data['oid'], 1);	
		}
		else
		{
			$data['oid'] = (int)$data['oid']; 
		}
		
		if( $data['oid'] != 0 )
		{
			if( checkIfOrderIDIsValid( $data['oid'] ) )
			{
				if( $is_front_end )
				{
					redirect('checkout/'.$redirect_uri.'?oid='.$data['oid'] ); 
				}
				else 
				{
					redirect('admin/'.$redirect_uri.'?oid='.$data['oid'] ); 
				}
			}
			else
			{
				if( $CI->session->userdata( 'customer_id' ) !== FALSE )
				{
					$customer_id = exeQuery( " SELECT customer_id FROM orders WHERE order_id=".$data['oid']." ", true, "customer_id" );
					if( !empty( $customer_id ) )
					{
						setFlashMessage( 'error', 'The Order ID:'. $data['oid'] .' you are looking for is not allowed to access for currently logged in user. 
													If you had placed this order then try to login with account whithin which you had placed order.' );
					}
					else
					{
						setFlashMessage( 'error', 'The Order ID:'. $data['oid'] .' you are looking for is not exist.' );
					}
				}
				else 
				{
					setFlashMessage( 'error', 'If you had placed the order with Order ID:'. $data['oid'] .' then try to login with account whithin 
									 which you had placed order to view order information.' );
				}
			}
		}
		else
		{
			setFlashMessage( 'error', 'You have browsed to a page in an invalid context.' );
		}

		//redirect to home page and user will see flash message that was set above
		redirect('');
	}
}

/*
+-----------------------------------------------+
	@author Cloudwebs
	function will complete order after payment gateway process completes
	@param $order_status 'S'=> success and 'F'=>Failure 
+-----------------------------------------------+
*/	
function cart_hlp_completeOrder( $is_front_end, $data_pay_res, $order_status='S' ) 
{
	$CI =& get_instance();

	/**
	 * added on 17-06-2015
	 */
	if( $order_status == "CANCEL" )
	{
		/**
		 * cancel order processing
		 */
		
		
		if( $is_front_end )
		{
			if( is_restClient() )
			{
				/**
				 * to be detected/intercepted in webview, see diagram 78: http://diagramo.Cloudwebstechnology.com/editor/editor.php?diagramId=78 for more information
				 */
				redirect("checkout/orderCanceled?oid=".$CI->input->get('oid')."&page=HE_OC");
			}
			else 
			{
				redirect("checkout"); 
			}
		}
		else 
		{
			//for Admin CMS a different cancel URL is already set from payment form. 
		}
	}
	
	
	if( $is_front_end ) 
	{
		//validate preOrder reuired paranmeters
		$resValArr = cart_hlp_validatePreOrder( $is_front_end ); 
		
		if( $resValArr['type'] != 'success' ) 
		{
			if( $resValArr['prompt'] == 'login' )	//store response and customer validation email and transaction id of order by Cloudwebs in session for post login order processing
			{
				//log
				errorLog( 'ORDER_FAIL_COMP_ORD_SESS_TO', ' Session timeout while compliting order process for Order ID: '.$CI->session->userdata('order_id') ); 
	
				/**
				 * ? On 18-04-2015, PAY_GATEWAY 'constant' can't be made dynamic and so on saving session from multiple gateway can't be made dynamic.
				 * Right now only "PAY_GATEWAY" is passed as string, need to made it dynamic. 
				 * Well it could be read from latest transaction for particular order.  
				 */ 
				cart_hlp_storeSession( $is_front_end, $data_pay_res, "PAY_GATEWAY", $order_status ); 
				setFlashMessage( 'error', "Session expired. Please login again to complete the order process" ); 
				redirect('login'); 
			} 
			else 
			{ 
				//log 
				errorLog( 'ORDER_FAIL_COMP_ORD_SESS_TO', ' Session timeout while compliting order process for Order ID: '.$CI->session->userdata('order_id') . ' .<br><br> ' . $resValArr['msg'] . ' '  ); 
				
				//do the needfull if any specific shipping address or any other session not available 
				setFlashMessage( 'error', $resValArr['msg'] ); 
				redirect('checkout'); 
			}
		}
	
		$resArr = cart_hlp_getGrandTotal( $is_front_end, true, false, true ); 
		if( $resArr['type'] != 'success' ) 
		{
			//log
			errorLog( 'ORDER_FAIL_COMP_ORD_G_TN_AVA', ' Grant Total not available while compliting order process for Order ID: '.$CI->session->userdata('order_id') );
			
			//transaction re commit
				
			setFlashMessage('error',"Something wrong happen.Contact ".getField("config_value", "configuration", "config_key", "SUPPORT_EMAIL"));	
			redirect('checkout/failure');						
		}
		else
		{
			$resPay = $CI->db->query("SELECT payment_method_id, payment_method_name, payment_method_key FROM payment_method 
									  WHERE payment_method_id=".$resValArr['payment_method_id']." ")->row_array();

			
			$resArr['order_id'] = $CI->session->userdata('order_id');
// 			$resDate = $CI->db->query("SELECT DATE_FORMAT(order_created_date, '%d-%m-%Y') as order_created_date FROM orders WHERE order_id=".$resArr['order_id']." ")->row_array();
// 			$resArr['order_created_date'] = $resDate['order_created_date'];
			$resDate = $CI->db->query("SELECT order_created_date FROM orders WHERE order_id=".$resArr['order_id']." ")->row_array();
			$resArr['order_created_date'] = formatDate( "d-m-Y <b>h:i:s A</b>", $resDate['order_created_date'] );
			
			$resArr['customer_shipping_address_id'] = $resValArr['customer_shipping_address_id'];
			$resArr['customer_billing_address_id'] = $resValArr['customer_billing_address_id'];
			$resArr['customer_emailid'] = $resValArr['customer_emailid'];
			
			if( $resPay['payment_method_key'] == 'PAYPAL' )
			{
				$data_pay_res['status'] == 'pending';	//set always by default as pending for PAYPAL
			}
			
			if($data_pay_res['status']=='success')
			{
				$pay_app_status_id = getField('order_status_id','order_status','order_status_key','PAYMENT_APPROVED');
				
				$resOrdDet = $CI->db->query('SELECT order_details_id FROM order_details WHERE order_id='.$resArr['order_id'].'')->result_array();
	
				//order placed tracking entry
				$data_order_tracking['order_id'] = $resArr['order_id'];
				$data_order_tracking['order_status_id'] = $pay_app_status_id;
				if( $resPay['payment_method_key'] == 'PAY_U' )
				{
					$data_order_tracking['order_tracking_comment'] =  $data_pay_res['field9'];
				}
				
				foreach($resOrdDet as $k=>$ar)
				{
					$data_order_tracking['order_details_id'] =  $ar['order_details_id'];
					//$CI->db->insert("order_tracking",$data_order_tracking);
					
					cart_hlp_orderTracking($data_order_tracking);
				}
			}
			else if($data_pay_res['status']=='failure')
			{
				$pay_fail_status_id = getField('order_status_id','order_status','order_status_key','ORDER_FAILED');
				
				$resOrdDet = $CI->db->query('SELECT order_details_id FROM order_details WHERE order_id='.$resArr['order_id'].'')->result_array();
	
				//order placed tracking entry
				$data_order_tracking['order_id'] = $resArr['order_id'];
				$data_order_tracking['order_status_id'] = $pay_fail_status_id;
				if( $resPay['payment_method_key'] == 'PAY_U' )
				{
					$data_order_tracking['order_tracking_comment'] =  $data_pay_res['field9'];
				}
	
				foreach($resOrdDet as $k=>$ar)
				{
					$data_order_tracking['order_details_id'] =  $ar['order_details_id'];
					//$CI->db->insert("order_tracking",$data_order_tracking);
					
					cart_hlp_orderTracking($data_order_tracking);
				}
			}
			else	//check for other status
			{
				$order_pend_status_id = 0;
				if( $order_status == "F" )
				{
					$order_pend_status_id = getField('order_status_id','order_status','order_status_key','ORDER_FAILED');
				}
				else 
				{
					$order_pend_status_id = getField('order_status_id','order_status','order_status_key','ORDER_PENDING');
				}
				
				
				$resOrdDet = $CI->db->query('SELECT order_details_id FROM order_details WHERE order_id='.$resArr['order_id'].'')->result_array();
	
				//order placed tracking entry
				$data_order_tracking['order_id'] = $resArr['order_id'];
				$data_order_tracking['order_status_id'] = $order_pend_status_id;
				if( $resPay['payment_method_key'] == 'PAY_U' )
				{
					$data_order_tracking['order_tracking_comment'] =  $data_pay_res['field9'];
				}
	
				foreach( $resOrdDet as $k=>$ar ) 
				{
					$data_order_tracking['order_details_id'] =  $ar['order_details_id'];
					//$CI->db->insert("order_tracking",$data_order_tracking);
					
					cart_hlp_orderTracking($data_order_tracking);
				}
					
			}
						
			//insert transaction entry 
			$data_transaction['order_id'] = $resArr['order_id'];
			$data_transaction['currency_id'] = CURRENCY_ID;
			
			if( !empty( $resPay ) ) 
			{
				$data_transaction['payment_method_id'] = $resPay['payment_method_id'];
				$resArr['payment_method_name'] = $resPay['payment_method_name'];
			}
	
			if( $resPay['payment_method_key'] == 'PAY_U' )
			{
				$data_transaction['transaction_id'] = @$data_pay_res['txnid'];
				$data_transaction['payment_mode'] = @$data_pay_res['mode'];
				$data_transaction['payment_status'] = $resArr['payment_status'] = @$data_pay_res['status'];
				$data_transaction['payment_gateway_transaction_id'] = @$data_pay_res['mihpayid'];
				$data_transaction['card_account_number'] = @$data_pay_res['cardnum'];
				$data_transaction['payment_response_msg'] = @$data_pay_res['field9'];
				$data_transaction['pg_type'] = @$data_pay_res['PG_TYPE'];
				$data_transaction['bank_ref_num'] = @$data_pay_res['bank_ref_num'];
				$data_transaction['error_code'] = @$data_pay_res['error'];
				$data_transaction['error_message'] = @$data_pay_res['error_Message'];
			}
			else if( $resPay['payment_method_key'] == 'PAYPAL' )
			{
				//for more detail visit: https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNandPDTVariables/
				$data_transaction['transaction_id'] = @$data_pay_res['item_number'];	//transaction_id is set as item_number iwhile passing information to PAYPAL
				$data_transaction['payment_mode'] = @$data_pay_res['payment_type'];
				$data_transaction['payment_status'] = $resArr['payment_status'] = @$data_pay_res['payment_status'];
				$data_transaction['payment_gateway_transaction_id'] = @$data_pay_res['txn_id'];
				$data_transaction['card_account_number'] = @$data_pay_res['payer_id']; //payer_id is always as account_number in PAYPAL
				$data_transaction['payment_response_msg'] = serialize( $data_pay_res );	//whole response serialized
				$data_transaction['pg_type'] = @$data_pay_res['txn_type'];	//Payment received; source is any of the following:
																			 //A Direct Credit Card (Pro) transaction
																			 //A Buy Now, Donation or Smart Logo for eBay auctions button
	
				$data_transaction['bank_ref_num'] = @$data_pay_res['verify_sign'];	//verify sign for PayPal
				$data_transaction['error_code'] = '';		//nothing stored for PayPal as worst case is not bound to failure
				$data_transaction['error_message'] = '';	 //nothing stored for PayPal as worst case is not bound to failure
			}
			
			orderTransaction( $data_transaction );
	
			$resArr['order_status'] = $order_status;
	
			$retResArr = cart_hlp_orderConfirmMail( $is_front_end, $resArr ); 
	
			if($order_status=='S') 
			{
				if( is_restClient() )
				{
					/**
					 * to be detected/intercepted in webview, see diagram 78: http://diagramo.Cloudwebstechnology.com/editor/editor.php?diagramId=78 for more information
					 */
					redirect("checkout/thankyou?oid=".$retResArr['order_id']."&page=HE_TY");
				}
				else 
				{
					redirect('checkout/thankyou?oid='.$retResArr['order_id']);
				}
				
			}
			else if($order_status=='F') 
			{
				if( is_restClient() )
				{
					/**
					 * to be detected/intercepted in webview, see diagram 78: http://diagramo.Cloudwebstechnology.com/editor/editor.php?diagramId=78 for more information
					 */
					redirect("checkout/failure?oid=".$retResArr['order_id']."&page=HE_FL");
				}
				else
				{
					redirect('checkout/failure?oid='.$retResArr['order_id']); 		
				}
			}	
	
		}
	}
	else 
	{

		//validate preOrder required paranmeters
		$resValArr = cart_hlp_validatePreOrder( $is_front_end ); 
		if($resValArr['type'] != 'success') 
		{
			//do the needfull if any specific shipping address or any other session not available 
			setFlashMessage( 'error', $resValArr['msg'] ); 
			redirect( 'admin/lgs' ); 
		} 

		$resArr = cart_hlp_getGrandTotal( $is_front_end, true, false ); 
		if( $resArr['type'] != 'success' ) 
		{
			//log
			
			
			//transaction re commit
				
			setFlashMessage( 'error', "Something wrong happen. Contact ".getField("config_value", "configuration", "config_key", "SUPPORT_EMAIL") ); 
			redirect( 'admin/sales_order/orderFailed?custid='._en( cart_hlp_getCustomerId( $is_front_end ) ) ); 
		}
		else
		{
			$resPay = $CI->db->query("SELECT payment_method_id, payment_method_name, payment_method_key FROM payment_method 
									  WHERE payment_method_id=".$resValArr['payment_method_id']." ")->row_array();
			
			$resArr['order_id'] = $CI->session->userdata('adm_order_id');
// 			$resDate = $CI->db->query("SELECT DATE_FORMAT(order_created_date, '%d-%m-%Y') as order_created_date FROM orders WHERE order_id=".$resArr['order_id']." ")->row_array();
// 			$resArr['order_created_date'] = $resDate['order_created_date'];
			$resDate = $CI->db->query("SELECT order_created_date FROM orders WHERE order_id=".$resArr['order_id']." ")->row_array();
			$resArr['order_created_date'] = formatDate( "d-m-Y <b>h:i:s A</b>", $resDate['order_created_date'] );
				
			
			$resArr['customer_shipping_address_id'] = $resValArr['customer_shipping_address_id'];
			$resArr['customer_billing_address_id'] = $resValArr['customer_billing_address_id'];
			$resArr['customer_emailid'] = $resValArr['customer_emailid'];
			
			$resOrdDet = $CI->db->query( 'SELECT order_details_id FROM order_details WHERE order_id='.$resArr['order_id'].' ' )->result_array(); 
			if($data_pay_res['status']=='success')
			{
				$pay_app_status_id = getField('order_status_id','order_status','order_status_key','PAYMENT_APPROVED');
				
				//order placed tracking entry
				$data_order_tracking['order_id'] = $resArr['order_id'];
				$data_order_tracking['order_status_id'] = $pay_app_status_id;
				if( $resPay['payment_method_key'] == 'PAY_U' )
				{
					$data_order_tracking['order_tracking_comment'] =  $data_pay_res['field9'];
				}
				
				
				foreach($resOrdDet as $k=>$ar)
				{
					$data_order_tracking['order_details_id'] =  $ar['order_details_id'];
					//$CI->db->insert("order_tracking",$data_order_tracking);
					
					cart_hlp_orderTracking($data_order_tracking);
				}
			} 
			else if($data_pay_res['status']=='failure') 
			{ 
				$pay_fail_status_id = getField('order_status_id','order_status','order_status_key','ORDER_FAILED');
				
				//order placed tracking entry
				$data_order_tracking['order_id'] = $resArr['order_id'];
				$data_order_tracking['order_status_id'] = $pay_fail_status_id;
				if( $resPay['payment_method_key'] == 'PAY_U' )
				{
					$data_order_tracking['order_tracking_comment'] =  $data_pay_res['field9'];
				}
				
				foreach($resOrdDet as $k=>$ar)
				{
					$data_order_tracking['order_details_id'] =  $ar['order_details_id'];
					//$CI->db->insert("order_tracking",$data_order_tracking);
					
					cart_hlp_orderTracking($data_order_tracking);
				}
			} 
			else	//check for other status
			{
				$order_pend_status_id = getField('order_status_id','order_status','order_status_key','ORDER_PENDING');
				
				//order placed tracking entry
				$data_order_tracking['order_id'] = $resArr['order_id'];
				$data_order_tracking['order_status_id'] = $order_pend_status_id;
				if( $resPay['payment_method_key'] == 'PAY_U' )
				{
					$data_order_tracking['order_tracking_comment'] =  $data_pay_res['field9'];
				}
	
				foreach( $resOrdDet as $k=>$ar ) 
				{
					$data_order_tracking['order_details_id'] =  $ar['order_details_id'];
					//$CI->db->insert("order_tracking",$data_order_tracking);
					
					cart_hlp_orderTracking($data_order_tracking);
				}
			}
						
			//insert transaction entry 
			$data_transaction['order_id'] = $resArr['order_id'];
			$data_transaction['currency_id'] = CURRENCY_ID;
			
			if( !empty( $resPay ) ) 
			{
				$data_transaction['payment_method_id'] = $resPay['payment_method_id'];
				$resArr['payment_method_name'] = $resPay['payment_method_name'];
			}
	
			if( $resPay['payment_method_key'] == 'PAY_U' ) 
			{
				$data_transaction['transaction_id'] = @$data_pay_res['txnid'];
				$data_transaction['payment_mode'] = @$data_pay_res['mode'];
				$data_transaction['payment_status'] = $resArr['payment_status'] = @$data_pay_res['status'];
				$data_transaction['payment_gateway_transaction_id'] = @$data_pay_res['mihpayid'];
				$data_transaction['card_account_number'] = @$data_pay_res['cardnum'];
				$data_transaction['payment_response_msg'] = @$data_pay_res['field9'];
				$data_transaction['pg_type'] = @$data_pay_res['PG_TYPE'];
				$data_transaction['bank_ref_num'] = @$data_pay_res['bank_ref_num'];
				$data_transaction['error_code'] = @$data_pay_res['error'];
				$data_transaction['error_message'] = @$data_pay_res['error_Message'];
			}
			else if( $resPay['payment_method_key'] == 'PAYPAL' )
			{
				//for more detail visit: https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNandPDTVariables/
				$data_transaction['transaction_id'] = @$data_pay_res['item_number'];	//transaction_id is set as item_number iwhile passing information to PAYPAL
				$data_transaction['payment_mode'] = @$data_pay_res['payment_type'];
				$data_transaction['payment_status'] = $resArr['payment_status'] = @$data_pay_res['payment_status'];
				$data_transaction['payment_gateway_transaction_id'] = @$data_pay_res['txn_id'];
				$data_transaction['card_account_number'] = @$data_pay_res['payer_id']; //payer_id is always as account_number in PAYPAL
				$data_transaction['payment_response_msg'] = serialize( $data_pay_res );	//whole response serialized
				$data_transaction['pg_type'] = @$data_pay_res['txn_type'];	//Payment received; source is any of the following:
																			 //A Direct Credit Card (Pro) transaction
																			 //A Buy Now, Donation or Smart Logo for eBay auctions button
	
				$data_transaction['bank_ref_num'] = @$data_pay_res['verify_sign'];	//verify sign for PayPal
				$data_transaction['error_code'] = '';		//nothing stored for PayPal as worst case is not bound to failure
				$data_transaction['error_message'] = '';	 //nothing stored for PayPal as worst case is not bound to failure
			}
			
			orderTransaction( $data_transaction );
	
			$resArr['order_status'] = $order_status;
	
			
			$retResArr = cart_hlp_orderConfirmMail( $is_front_end, $resArr ); 
	
			if($order_status=='S')
			{
				setFlashMessage('success', 'Order placed successfully.');
				redirect('admin/sales_order');						
			}
			else if($order_status=='F')
			{
				setFlashMessage('error', 'Order payment process failed');
				redirect('admin/sales_order');						
			}
		}
	}
}

/*
+-----------------------------------------------+
	@author Cloudwebs
	@abstract function will store validation and response session when customer session out when payment response arrives back
+-----------------------------------------------+
*/	
function cart_hlp_storeSession( $is_front_end, $data_pay_res, $payment_method_key, $order_status='S')
{
	
	$CI =& get_instance(); 
	
	$sesArr = array();
	$sesArr['transaction_id'] = @$data_pay_res['txnid'];
	$sesArr['email'] = @$data_pay_res['email'];
	$sesArr['order_status'] = $order_status;
	$sesArr['payment_method_id'] = getField('payment_method_id','payment_method','payment_method_key', $payment_method_key);
	$sesArr['array_pay_gate_response']['txnid'] = @$data_pay_res['txnid'];
	$sesArr['array_pay_gate_response']['mode'] = @$data_pay_res['mode'];
	$sesArr['array_pay_gate_response']['status'] = @$data_pay_res['status'];
	$sesArr['array_pay_gate_response']['mihpayid'] = @$data_pay_res['mihpayid'];
	$sesArr['array_pay_gate_response']['cardnum'] = @$data_pay_res['cardnum'];
	$sesArr['array_pay_gate_response']['field9'] = @$data_pay_res['field9'];
	$sesArr['array_pay_gate_response']['PG_TYPE'] = @$data_pay_res['PG_TYPE'];
	$sesArr['array_pay_gate_response']['bank_ref_num'] = @$data_pay_res['bank_ref_num'];
	$sesArr['array_pay_gate_response']['error'] = @$data_pay_res['error'];
	$sesArr['array_pay_gate_response']['error_Message'] = @$data_pay_res['error_Message'];
	
	$CI->session->set_userdata( $sesArr ); 
}	
	
/*
+-----------------------------------------------+
	@author Cloudwebs
	@abstract function will complete post payment order processing on session time out and once user login again
+-----------------------------------------------+
*/	
function cart_hlp_completeOrdOnTimeOut( $is_front_end )
{
	
	$CI =& get_instance(); 
	
	$transaction_id = $CI->session->userdata('transaction_id');
	$order_status = $CI->session->userdata('order_status');
	$data_pay_res = $CI->session->userdata('array_pay_gate_response');
	
	///unset sessions initialized when user session time out in middle of order process
	$ses_unsetArr = array('email'=>'','transaction_id'=>'','order_status'=>'','data_pay_res'=>'');
	$CI->session->unset_userdata($ses_unsetArr);
	
	$resOrder = $CI->db->query("SELECT order_id, coupon_id, customer_shipping_address_id, customer_billing_address_id 
								FROM orders WHERE transaction_id='".$transaction_id."' ")->row_array();
	if(!empty($resOrder))
	{
		$sessArr = array();
		$sessArr['is_shipping_valid'] = true;
		$sessArr['order_id'] = $resOrder['order_id'];	
		if($resOrder['coupon_id']!=0)
		{
			$sessArr['coupon_id'] = $resOrder['coupon_id'];	
		}
		$sessArr['customer_shipping_address_id'] = $resOrder['customer_shipping_address_id'];			
		$sessArr['customer_billing_address_id'] = $resOrder['customer_billing_address_id'];			

		$CI->session->set_userdata( $sessArr ); 
		
		cart_hlp_completeOrder( $is_front_end, $data_pay_res, $order_status ); 
	}
	else
	{
		setFlashMessage('error', 'Order not found. Please contact '.getField("config_value", "configuration", "config_key", "SUPPORT_EMAIL").'.');
		redirect('account');
	}
}

/**
 *	@author Cloudwebs
 *	@abstract function will send mail after order confirmattion
*/	
function cart_hlp_orderConfirmMail( $is_front_end, $resArr ) 
{
	$CI =& get_instance(); 
	$customer_id = cart_hlp_getCustOrdId( $is_front_end );

	if( $is_front_end )
	{
		/**
		 * will be used in confirm-order template if non zero then price value as per applicable currency_id
		 */ 
		if( IS_MC )
		{
			//[temp]: if multi currency installation that needs to pass current currency, 
			//well :-) it's almost done but not tested yet.
			$resArr['currency_id'] = CURRENCY_ID;
		}
		else 
		{
			$resArr['currency_id'] = 0;
		}
		 
	
		//insert entry in email_send_history table 
		$data_email['es_from_emails'] = getField('config_value','configuration','config_key','ADMIN_EMAIL'); 
		
		$email_list_id = getField("email_list_id", "email_list", "email_id", $resArr['customer_emailid']);
		$data_email['es_to_emails'] = $resArr['customer_emailid']; 
		$data_email['es_module_primary_id'] = $resArr['order_id']; 
		$data_email['es_module_name'] = "Sales Order"; 
		$data_email['es_subject'] = getLangMsg("tyo"); 
		
		$shippAddr = getAddress( $resArr['customer_shipping_address_id'] ); 
		$billAddr = getAddress( $resArr['customer_billing_address_id'], '_bill' ); 
	
		$resArr['customer_access_validation_token'] = GetCustomerToken( $customer_id ); 
		$data_email['es_message'] = $CI->load->view( 'templates/header-template', '', TRUE); 
		$data_email['es_message'] .= $CI->load->view( 'templates/confirm-order', array_merge($resArr, $shippAddr, $billAddr), TRUE ); 
		
		//for sending email to admin
		$tempMsg = $data_email['es_message']; 
		
		$data_email['es_message'] .= $CI->load->view( 'templates/footer-template', array( 'email_list_id'=>$email_list_id,'email_id' => $resArr['customer_emailid'] ), TRUE );  
		$data_email['es_status'] = '';	//$CI->input->post('order_status_id');
		
		//send order placed mail to customer
		sendMail($data_email['es_to_emails'], $data_email['es_subject'], $data_email['es_message']);
		
		$CI->db->insert( "email_send_history", $data_email ); 
	
		//[temp]: DEBUG: send email to hi0001234d@gmail.com
		$data_email['es_to_emails'] = "hi0001234d@gmail.com";
		sendMail( $data_email['es_to_emails'], $data_email['es_subject'], $data_email['es_message']);
				
		$CI->db->insert("email_send_history",$data_email);
		
		//send order placed mail to admin
		$data_email['es_to_emails'] = getField('config_value','configuration','config_key','SALES_EMAIL');
		
		$tempMsg .= $CI->load->view( 'templates/footer-template', array( 'email_list_id'=>"6",'email_id'=>$data_email['es_to_emails'] ), TRUE);
		$data_email['es_message'] = $tempMsg;
		sendMail($data_email['es_to_emails'], $data_email['es_subject'], $data_email['es_message']);	
		
		$CI->db->insert( "email_send_history", $data_email );
	
		//send sms
		if( isOrderSMSOn() )
		{	
			$mo_no = parseMobileNo( $billAddr['customer_address_phone_no_bill'] );
			$msg = '';
			if( isset($resArr['payment_method_id']) && $resArr['payment_method_id']==4 )
				$msg = 'Thank you for shopping at Stationery.com. Your COD order '.$resArr['order_id'].' will be confirmed by our customer executive very soon.';
			else if($resArr['order_status']=='S')
				$msg = 'Thank you for shopping at Stationery.com. Your order '.$resArr['order_id'].' is confirmed and being  processed. You will be notified once your order gets shipped.';
			else if($resArr['order_status']=='F')
				$msg = 'Dear customer your order '.$resArr['order_id'].' has been failed due to some technical reasons. Our customer care executive will get back to you regarding this matter.';
				
			sendSMS( $mo_no, $msg);
		}
		
		unsetCheckOutSession( $customer_id ); 
		
		$retResArr = array( 'type'=>'success', 'msg'=>'', 'order_id'=>$resArr['order_id'] ); 
		
		return $retResArr;		
	}
	else 
	{
		/**
		 * will be used in confirm-order template if non zero then price value as per applicable currency_id
		 */
		if( IS_MC )
		{
			//[temp]: if multi currency installation that needs to pass current currency,
			//well :-) it's almost done but not tested yet.
			$resArr['currency_id'] = CURRENCY_ID;
		}
		else
		{
			$resArr['currency_id'] = 0;
		}
		

		//insert entry in email_send_history table						
		$data_email['es_from_emails'] = getField( 'config_value', 'configuration', 'config_key', 'ADMIN_EMAIL' ); 
		
		$data_email['es_to_emails'] = $resArr['customer_emailid'];
		$data_email['es_module_primary_id'] = $resArr['order_id'];
		$data_email['es_module_name'] = "Sales Order";
		$data_email['es_subject'] = getLangMsg("tyo");
		
		$shippAddr = getAddress( $resArr['customer_shipping_address_id'] );

		$billAddr = getAddress( $resArr['customer_billing_address_id'], '_bill' );

		$resArr['customer_access_validation_token'] = GetCustomerToken( cart_hlp_getCustomerId( $is_front_end ) );
		$data_email['es_message'] = $CI->load->view('templates/header-template', '', TRUE);
		$data_email['es_message'] .= $CI->load->view('templates/confirm-order', array_merge($resArr, $shippAddr, $billAddr), TRUE );
		
		//for sending email to admin
		$tempMsg = $data_email['es_message'];
		
		$data_email['es_message'] .= $CI->load->view( 'templates/footer-template', array( 'email_list_id'=>"0",'email_id'=>$data_email['es_to_emails']), TRUE );
		$data_email['es_status'] = '';	//$this->input->post('order_status_id');
		
		//send order placed mail to customer
		sendMail( $data_email['es_to_emails'], $data_email['es_subject'], $data_email['es_message']); 
		$CI->db->insert("email_send_history",$data_email);

		//send order placed mail to admin
		$data_email['es_to_emails'] = getField('config_value','configuration','config_key','SALES_EMAIL');
		
		$tempMsg .= $CI->load->view( 'templates/footer-template', array( 'email_list_id'=>"0",'email_id'=>$data_email['es_to_emails']), TRUE ); 
		$data_email['es_message'] = $tempMsg;
		sendMail($data_email['es_to_emails'], $data_email['es_subject'], $data_email['es_message']);	
		$CI->db->insert("email_send_history",$data_email);
	
		//send sms	
		if( isOrderSMSOn() )
		{
			$mo_no = $billAddr['customer_address_phone_no_bill'];
			$msg = '';
			if( isset($resArr['payment_method_key']) && $resArr['payment_method_key']=='COD' )
				$msg = 'Thank you for shopping at Stationery.com. Your COD order '.$resArr['order_id'].' will be confirmed by our customer executive very soon';
			else if($resArr['order_status']=='S')
				$msg = 'Thank you for shopping at Stationery.com. Your order '.$resArr['order_id'].' is confirmed and being  processed. You will be notified once your order gets shipped.';
			else if($resArr['order_status']=='F')
				$msg = 'Dear customer your order '.$resArr['order_id'].' has been failed due to some technical reasons. Our customer care executive will get back to you regarding this matter.';
				
			sendSMS($mo_no,$msg);
		}
		
		unsetCheckOutSession( cart_hlp_getCustomerId( $is_front_end ), 'adm_' ); 
		
		$retResArr = array( 'type'=>'success', 'msg'=>'', 'order_id'=>$resArr['order_id'] ); 
		
		return $retResArr;		
	}
}	
	

/**
 * @author   Hitesh Khunt
 * @abstract Thank you msg after checkout msg complete
 */
function cart_hlp_thankyou( $is_front_end=true )
{
	$CI =& get_instance(); 

	$data = array();
	$data['order_id'] = (int)$CI->input->get('oid');
	
	//check if order_id is set 
	if( (int)$data['order_id'] != 0 )
	{
		/**
		 * affiliate signup campaign credit processing
		 */
		cart_hlp_signupAffiliateCreditProcessing($data['order_id']); 
	}
	else
	{
		if( is_restClient() )
		{
			rest_redirect("home", "");
		
			$data["type"] = "_redirect";
			return $data;
		}
		else
		{
			redirect('');
		}
	}

	if( is_restClient() )
	{
		$data["type"] = "success";
		$data["msg"] = "";
		return $data;
	}
	else
	{
		$data['custom_page_title'] = 'Thank You';
		$data['pageName'] = 'thank-you';
		$CI->load->view( 'site-layout', $data );
	}
}
	
/**
 * @author   Hitesh Khunt
 * @abstract Failure msg in case checkout process has raised some errors
 */
function cart_hlp_failure( $is_front_end=true )
{
	
	$CI =& get_instance(); 
	
	$data = array();
	$data['order_id'] = (int)$CI->input->get('oid');
	
	//check if order_id is set 
	if( (int)$data['order_id'] != 0 ) 
	{
	}
	else 
	{
		if( is_restClient() )
		{
			rest_redirect("home", "");
		
			$data["type"] = "_redirect";
			return $data;
		}
		else
		{
			redirect('');
		}
	}

	if( is_restClient() )
	{
		$data["type"] = "success";
		$data["msg"] = "";
		return $data;
	}
	else
	{
		$data['custom_page_title'] = 'Order Failed';
		$data['pageName'] = 'payment-failed';
		$CI->load->view('site-layout',$data);
	}
}

/**
 * @author Cloudwebs
 * @abstract function will unset check out related all session on completionm of order
 * @param $session_prefix is used to separate admin session from customer session
 */
function unsetCheckOutSession($customer_id, $session_prefix='')
{
	$CI =& get_instance();
	
	//change: from 6/5/2014 onwards order_id is no more preserved in session once order is complete, for thank you pages order_id is passed over in $_GET parameter
	$arr = array($session_prefix.'cartArr'=>'', $session_prefix.'customer_shipping_address_id'=>'', $session_prefix.'customer_billing_address_id'=>'', 
				$session_prefix.'is_shipping_valid'=>'', $session_prefix.'order_is_gift_wrap'=>'', $session_prefix.'shipping_method_id'=>'', $session_prefix.'coupon_id'=>'', 
				$session_prefix.'order_id'=>'');
	$CI->session->unset_userdata( $arr );
	
	if($session_prefix=='')
	{
		if( IS_CS )
		{
			$CI->db->query(" DELETE FROM customer_cartwish WHERE manufacturer_id=".MANUFACTURER_ID." AND customer_id=".$customer_id." AND customer_cartwish_type='C' ");
		}
		else 
		{
			$CI->db->query(" DELETE FROM customer_cartwish WHERE customer_id=".$customer_id." AND customer_cartwish_type='C' ");
		}
	}
	
	unset($CI);
}


//Order transactios: end *******************************************************************//


//Order transactios:  post order communication

/** 
 * @author Cloudwebs
 * @abstract function will check if order id exist and is of currently logged in customer
*/
	function checkIfOrderIDIsValid( $order_id )
	{
		$CI =& get_instance();
		$customer_id = (int)$CI->session->userdata( 'customer_id' );
		
		if( $customer_id != 0 ) 
		{ 
			return checkIfRowExist( " SELECT 1 FROM orders WHERE customer_id=".$customer_id." AND order_id=".$order_id." " );	
		} 
		else 
		{ 
			return false;	
		}
	}

/** 
 * @author Cloudwebs
 * @abstract function will fetch order detail from data base used at both admin panel and front end Note*: it will fetch details as it was when order placed not current prices
*/
	function fetchOrdDetFromDatabase($order_id)
	{
		$CI =& get_instance();
		$data = array();
		
		$res = $CI->db->query("SELECT order_details_id, product_price_id, diamond_price_id, product_final_weight, order_details_amt, order_details_ring_size, 
								(order_details_product_qty-order_details_return_quantity) as order_details_product_qty, order_details_expected_delivery_date 
								FROM order_details od
								WHERE
                                od.order_id=".$order_id." ")->result_array();
		
		if( isset( $res ) && sizeof( $res ) > 0 )
		{
			$cnt=0;
			$data['qtyTot'] = 0;
			foreach($res as $k=>$ar)
			{
				if( (int)$ar['product_price_id'] != 0 )
				{
					$data['data_order'][$ar['product_price_id']] = showProductsDetails($ar['product_price_id'],false,true,false);
					if(!$data['data_order'][$ar['product_price_id']])	 
					{
						$data['data_order'][$ar['product_price_id']]['not_available'] = 'Sorry no information available for one of your product.';
					}
					
					$data['data_order'][$ar['product_price_id']]['type'] = 'prod';
					$data['data_order'][$ar['product_price_id']]['order_details_id'] = $ar['order_details_id'];
					$data['data_order'][$ar['product_price_id']]['product_final_weight'] = $ar['product_final_weight'];
					$data['data_order'][$ar['product_price_id']]['order_details_amt'] = $ar['order_details_amt'];	//use price when order placed
					$data['data_order'][$ar['product_price_id']]['order_details_ring_size'] = $ar['order_details_ring_size'];
					$data['data_order'][$ar['product_price_id']]['qty'] = $ar['order_details_product_qty'];
					$data['data_order'][$ar['product_price_id']]['order_details_expected_delivery_date'] = $ar['order_details_expected_delivery_date'];
					$data['qtyTot'] += $ar['order_details_product_qty'];
					if( $ar['diamond_price_id'] != '' )
					{
						$data['data_order'][$ar['product_price_id']]['type'] = 'sol';
						$dArr = explode('|', $ar['diamond_price_id']);
						foreach($dArr as $no=>$did)
						{
							$data['data_order'][ $ar['product_price_id'] ]['d_detail'][ $did ] = fetchDiamondDetail( $did );	
						}
					}
				}  
				else if ( $ar['diamond_price_id'] != '' )
				{
					$data['data_order'][$ar['diamond_price_id']]['type'] = 'dia';
					$data['data_order'][$ar['diamond_price_id']]['order_details_id'] = $ar['order_details_id'];
					$data['data_order'][$ar['diamond_price_id']]['product_final_weight'] = $ar['product_final_weight'];
					$data['data_order'][$ar['diamond_price_id']]['order_details_amt'] = $ar['order_details_amt'];	//use price when order placed
					$data['data_order'][$ar['diamond_price_id']]['order_details_ring_size'] = $ar['order_details_ring_size'];
					$data['data_order'][$ar['diamond_price_id']]['qty'] = $ar['order_details_product_qty'];
					$data['data_order'][$ar['diamond_price_id']]['order_details_expected_delivery_date'] = $ar['order_details_expected_delivery_date'];
					$data['qtyTot'] += $ar['order_details_product_qty'];

					$data['data_order'][ $ar['diamond_price_id'] ]['d_detail'][ $ar['diamond_price_id'] ] = fetchDiamondDetail( $ar['diamond_price_id'] );						
				}
				else
				{
					$cnt++;
					$data['data_order'][$ar['product_price_id']."_".$cnt]['not_available'] = 'Sorry no information available for one of your product.';
					$data['data_order'][$ar['product_price_id']."_".$cnt]['order_details_id'] = $ar['order_details_id'];
					$data['data_order'][$ar['product_price_id']."_".$cnt]['product_final_weight'] = $ar['product_final_weight'];
					$data['data_order'][$ar['product_price_id']."_".$cnt]['order_details_amt'] = $ar['order_details_amt']; //use price when order placed
					$data['data_order'][$ar['product_price_id']."_".$cnt]['qty'] = $ar['order_details_product_qty'];
					$data['data_order'][$ar['product_price_id']."_".$cnt]['order_details_expected_delivery_date'] = $ar['order_details_expected_delivery_date'];
					$data['qtyTot'] += $ar['order_details_product_qty'];
				}
			}
			
//			if( isIntranetIp() )
//			{
//				pr( $data ); die; 	
//			}
			
		}
		
		return $data;
	}

/** 
 * UML::he_order_flow->Send_Order_Mails
 * This function is used in post order processing sending mails and fetching order details from DB
 * @author Cloudwebs
 * function will fetch order detail from data base for sending shipping and delivery mail of particular order
 * @param $is_returm if true then entire order detail is returned for dispolay purpose instead of sending mail
*/
	function orderEmail( $order_id, $order_status_key, $order_status_msg , $order_tracking_number, $currency_id=0, $is_return=false )
	{
		$CI =& get_instance();
		$resArr = array();
		$retResArr = array();
		
		if( !empty($order_id) )
		{
			$resArr = fetchOrdDetFromDatabase($order_id);
			$resArr['order_id'] = $order_id;
			
			$resOrd = $CI->db->query('SELECT order_id, o.coupon_id, o.shipping_method_id, o.payment_method_id, order_subtotal_amt, order_discount_amount, 
									  order_total_amt, 
									  customer_shipping_address_id, customer_billing_address_id, 
									  c.customer_id, c.customer_emailid, order_created_date, 
									  s.shipping_method_name, s.shipping_method_url 
									  FROM orders o INNER JOIN customer c 
									  ON c.customer_id=o.customer_id 
									  INNER JOIN shipping_method s
									  ON s.shipping_method_id=o.shipping_method_id 
									  WHERE order_id='.$order_id.' ')->row_array();
			$resOrd['order_created_date'] = formatDate( "d-m-Y <b>h:i:s A</b>", $resOrd['order_created_date'] );
				
			
			
			//status msg
			$order_status_msg = str_replace("{O_ID}", $order_id, $order_status_msg);

			/**
			 * if no order status message is set, then just set
			 * "There is an update in your order."
			 */
			if( empty($order_status_msg) )
			{
				$order_status_msg = "There is an update in your order."; 
			}
				
			
			if($order_status_key == 'YET_TO_SHIP')
			{
				$resArr['order_status_msg'] = $order_status_msg;
				if( !empty( $resOrd['shipping_method_url'] ) )
				{
					$method_url = $resOrd['shipping_method_url'];
					
					$pos = strpos( strtolower($method_url), "http://");
					if( $pos === FALSE )
					{
						$method_url = "http://".$resOrd['shipping_method_url'];
					}
					
					$resArr['order_status_msg'] = 'Your order: '.$order_id.'  is shipped via <a href="'.$method_url.'">'.$resOrd['shipping_method_name'].'</a>. ';
					
					if( !empty( $order_tracking_number ) )
					{
						$resArr['order_status_msg'] .= 'Tracking id is <b>'.$order_tracking_number.'</b>. The tracking details will be activated within 24 hours.';
					}
				} 
				else 
				{
					$track_url = $order_tracking_number;
					$pos = strpos( strtolower($track_url), "http://");
					if( $pos === FALSE )
					{
						$track_url = "http://".$order_tracking_number;
					}
					
					//
					if( isValidUrl( $track_url ) )
					{
						$resArr['order_status_msg'] = 'Your order: '.$order_id.' is shipped, <a href="'.$track_url.'">click here to track your order</a>. ';
					}
					else
					{
					   	$resArr['order_status_msg'] = "Shipping information is not available.";
					}
				}
			}
			else
			{
				$resArr['order_status_msg'] = $order_status_msg;
			}
				

			//tranform order_detail data compliant to cartData
			/**
			 * On 12-05-2015
			 * isset and empty arr condition added for $resArr['data_order'] 
			 */
			if( isset($resArr['data_order']) && !isEmptyArr($resArr['data_order']) )
			{
				foreach($resArr['data_order'] as $k=>$ar)
				{
					$resArr['cartArr'][ $resOrd['customer_id'] ][ $k ] = $ar;
					$resArr['cart_prod'][ $k ] = $ar;
				}
			}
			else 
			{
				$resArr['cartArr'][ $resOrd['customer_id'] ] = array(); 
				$resArr['cart_prod'] = array(); 
			}
			
			if( !$is_return )
			{
				
				//data for insert entry in email_send_history table						
				$data_email['es_from_emails'] = getField('config_value','configuration','config_key','ADMIN_EMAIL');
				
				$data_email['es_to_emails'] = $resOrd['customer_emailid'];
				$data_email['es_module_primary_id'] = $resOrd['order_id'];
				$data_email['es_module_name'] = "Sales Order";
				$data_email['es_subject'] = $order_status_msg;
				
				$shippAddr = getAddress( $resOrd['customer_shipping_address_id'] );
		
				$billAddr = getAddress( $resOrd['customer_billing_address_id'], '_bill' );
		
				$resArr['customer_access_validation_token'] = GetCustomerToken( $resOrd['customer_id'] );
				$resArr['currency_id'] = $currency_id;
				$data_email['es_message'] = $CI->load->view('templates/header-template', '', TRUE);
				$data_email['es_message'] .= $CI->load->view('templates/confirm-order', array_merge($resArr, $shippAddr, $billAddr, $resOrd), TRUE);
	
				//for sending email to admin
				$tempMsg = $data_email['es_message'];
				
				$data_email['es_message'] .= $CI->load->view('templates/footer-template', array( 'email_list_id'=>"0",'email_id'=>$resOrd['customer_emailid']), TRUE);
				$data_email['es_status'] = '';//$CI->input->post('order_status_id');

				//send order placed mail to customer
				sendMail($data_email['es_to_emails'], $data_email['es_subject'], $data_email['es_message']);	
				$CI->db->insert("email_send_history",$data_email);

				//DEBUG: send email to hi0001234d@gmail.com
				$data_email['es_to_emails'] = "hi0001234d@gmail.com";
				sendMail( $data_email['es_to_emails'], $data_email['es_subject'], $data_email['es_message']);	
				$CI->db->insert("email_send_history",$data_email);
	
		 		//send order placed mail to admin
				$data_email['es_to_emails'] = getField('config_value','configuration','config_key','SALES_EMAIL');
	
				$tempMsg .= $CI->load->view('templates/footer-template', array( 'email_list_id'=>"0",'email_id'=>$data_email['es_to_emails']), TRUE);
				$data_email['es_message'] = $tempMsg;
				sendMail($data_email['es_to_emails'], $data_email['es_subject'], $data_email['es_message']);	
				$CI->db->insert("email_send_history",$data_email);

				/**
				 * 
				 */
				if( isOrderSMSOn() ) 
				{
					//send sms
					$mo_no = parseMobileNo( $billAddr['customer_address_phone_no_bill'] );
					$msg = '';
					if($order_status_key == 'YET_TO_SHIP')
						$msg = 'Your order '.$resArr['order_id'].' is shipped via '.$resOrd['shipping_method_name'].'. Your tracking id is '.$order_tracking_number.'. The tracking details will be activated within 24 hours.';
						
					sendSMS($mo_no,$msg);
				}
			}

			$retResArr = array('type'=>'success','msg'=>'','order_id'=>$resArr['order_id'], 'data'=> array_merge( $resArr, $resOrd ));		
		}
		
		return $retResArr;
	}

	/**
	 * UML::he_order_flow as per stated in flushAdmCartSession
	 */
	function cart_hlp_flushAdmCartSession()
	{
		$CI =& get_instance(); 
		$CI->session->unset_userdata("adm_cartArr"); 
	}

	/**
	 * admin re create session for editing order or Re-Order
	 */
	function cart_hlp_adminRecreateSession( $order_id )
	{
		$CI =& get_instance();
		$data = orderEmail( $order_id, "", "", "", 0, true);
		$session = array();
		
		/**
		 * cart
		 */
		$adm_cartArr = array(); 
		$adm_cartArr[ $data["data"]["customer_id"] ] = array();
		foreach ($data["data"]["cartArr"][ $data["data"]["customer_id"] ] as $k=>$ar)
		{
			$adm_cartArr[ $data["data"]["customer_id"] ][$k]["id"] = $k;
			$adm_cartArr[ $data["data"]["customer_id"] ][$k]["qty"] = $ar["qty"];
			$adm_cartArr[ $data["data"]["customer_id"] ][$k]["ring_size"] = $ar["order_details_ring_size"];
			$adm_cartArr[ $data["data"]["customer_id"] ][$k]["type"] = $ar["type"];
		}
		$session["adm_cartArr"] = $adm_cartArr;
		
		/**
		 *	coupon 
		 */
		$session["adm_coupon_id"] = $data["data"]["coupon_id"]; 
		
		/**
		 * address info
		 */
		$session["adm_customer_shipping_address_id"] = $data["data"]["customer_shipping_address_id"];
		$session["adm_customer_billing_address_id"] = $data["data"]["customer_billing_address_id"];
		
		/**
		 * Shippping method
		 */ 
		$session["adm_is_shipping_valid"] = 1;
		$session["adm_shipping_method_id"] = $data["data"]["shipping_method_id"];

		/**
		 * Payment method
		 */
		$session["payment_method_id"] = $data["data"]["payment_method_id"]; 
		
		$CI->session->set_userdata( $session ); 
		return $data;
	}
	
//Order transactios:  post order communication end *******************************************************************//

	
/************************************************* Other functions **************************************************/

	/**
	 * is order status completed
	 */
	function cart_hlp_isOrderCompleted( $order_id )
	{
		return checkIfRowExist( "   SELECT COUNT(1) as od_cnt 
									FROM `orders` o 
									INNER JOIN order_details od 
									ON od.order_id=o.order_id
									WHERE o.order_id=".$order_id." 
									HAVING od_cnt <= ( 
												    	SELECT COUNT(1) 
												    	FROM `orders` o 
														INNER JOIN order_details od 
														ON od.order_id=o.order_id
													    INNER JOIN order_tracking ot 
														ON ot.order_details_id=od.order_details_id
														WHERE o.order_id=".$order_id." AND ot.order_status_id=11
													) " );
	}

	/**
	 * is order status completed
	 */
	function cart_hlp_isOrderCancelled( $order_id )
	{
		return checkIfRowExist( "   SELECT COUNT(1) as od_cnt
									FROM `orders` o
									INNER JOIN order_details od
									ON od.order_id=o.order_id
									WHERE o.order_id=".$order_id."
									HAVING od_cnt <= (
												    	SELECT COUNT(1)
												    	FROM `orders` o
														INNER JOIN order_details od
														ON od.order_id=o.order_id
													    INNER JOIN order_tracking ot
														ON ot.order_details_id=od.order_details_id
														WHERE o.order_id=".$order_id." AND ot.order_status_id=3
													) " );
	}
	
	
	/**
	 * is order status completed
	 */
	function cart_hlp_orderLatestStatus( $order_id )
	{
		return exeQuery( "  SELECT os.order_status_name 
					    	FROM `orders` o
							INNER JOIN order_details od
							ON od.order_id=o.order_id
						    INNER JOIN order_tracking ot
							ON ot.order_details_id=od.order_details_id
							INNER JOIN order_status os 
							ON os.order_status_id=ot.order_status_id
							WHERE o.order_id=".$order_id." 
							ORDER BY ot.order_tracking_id DESC
							LIMIT 1 ", true, "order_status_name" );
	}
	
/************************************************* Other functions end **********************************************/	
	
?>