<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class checkout extends CI_Controller
{
	var $customer_id =0;
	var $cartArr = array();
	
	/**
	 * @deprecated ummm... but still in doubt if used some where
	 */
	var $support_email = '';

	//parent constructor will load model inside it
	function checkout()
	{
		parent::__construct();
		$this->load->model('mdl_checkout','che');
		
		//check if customer id is set 
		if( $this->session->userdata('customer_id') !== FALSE )
		{
			$this->customer_id = $this->che->customer_id =  (int)$this->session->userdata('customer_id');
		}
		
		//cache driver
// 		$this->load->driver( 'cache', array( 'adapter' => 'apc', 'backup' => 'file' ) ); 
	}
	
	function index()
	{
		$data["chk"] = $this->getCheckOutData();
		$data["pageName"] = $data["chk"]["pageName"]; 
		$this->load->view('site-layout',$data);
	}
	
/*
+-----------------------------------------------+
	@author Cloudwebs
	@abstract function will return data for check put page
+-----------------------------------------------+
*/	
	function getCheckOutData()
	{
		return cart_hlp_getCheckOutData( true );
	}
	
	

	
//User related info: login-guest signup - logout 


/*
+-------------------------------------------------------+
	Callback function While customer registering to the site.
	check email duplication in database.
+-------------------------------------------------------+
*/	
	function checkMailDuplication($str)
	{
		if( isEmailSignedUp( $str ) )
		{	
			$this->form_validation->set_message('checkMailDuplication','This email address already registered with us. 
					<a data-target="#CloudwebsModal" data-toggle="modal" class="forgot_pass">
						<label class="cursor">Forgot Your Password?</label>
					</a>
					');
			return false;
		}		
		else
			return true;
	}

/*
 * @author   Cloudwebs
 * @abstract function will register user as guest
 */
	function guestSignup()
	{
		cmn_vw_guestSignup(); 
		
// 		$returnArr = array();
// 		$this->form_validation->set_rules('login_email','Email','trim|required|valid_email|callback_checkMailDuplication');
	
// 		if($this->form_validation->run() == FALSE)
// 		{
// 			$customer_emailid = $this->input->post('login_email');
			
// 			$returnArr['type'] = 'error';
// 			$returnArr['error'] = $this->form_validation->get_errors();
			
// 			//Chnnage: commented out since guest sign up given fulll access
// /*			$res = $this->db->query("SELECT customer_id, customer_emailid 
// 								FROM customer c INNER JOIN customer_group g 
// 								ON g.customer_group_id=c.customer_group_id
// 								WHERE customer_emailid='".$customer_emailid."'  AND customer_group_type='G'")->result_array();
								
// 			if(!empty($res))
// 			{
// 				$returnArr['type'] = 'success';
// 				$returnArr['msg'] = 'Guest login.';
				
// 				unset($returnArr['error']);
// 				$returnArr['customer_id'] = $res[0]['customer_id'];
// 				$returnArr['customer_group_type'] = 'G';
// 				$returnArr['customer_emailid'] = $res[0]['customer_emailid'];
// 			}
// */			
// 		}
// 		else 
// 		{
// 			$returnArr = $this->che->guestSignup(); 
// 		}

// 		if($returnArr['type']=='success')
// 		{
// 			//set all login sessions and upd cart/wish database
// 			setLoginSessions($returnArr['customer_id'], $returnArr['customer_group_type'], $returnArr['customer_emailid']);
			
// 			unset($returnArr['customer_id']);
// 			unset($returnArr['customer_group_type']);
// 			unset($returnArr['customer_emailid']);
// 		}
			
// 		echo json_encode($returnArr);	
	}
	
/*
 * @author   Cloudwebs
 * @abstract function will log out the user and display login form at browser side
 */
	function logOut()
	{
		$this->cartArr = $this->session->userdata('cartArr');
		$wishArr = $this->session->userdata('wishArr');
		echo json_encode(logout('', false, false, $this->customer_id, $this->cartArr, $wishArr));
	}
	

//User info end  *******************************************************************//




//Address info 

	
/*
 * @author   Cloudwebs
 * @abstract function will load city as per state selected
 */
	function loadCityAjax()
	{
		$state_id = $this->input->post('state_id');
		if(!empty($state_id))
		{
			echo loadCity($state_id);
		}
		else
		{
			echo '<option value="">- Select State First -</option>';	
		}
	}

/*
 * @author   Cloudwebs
 * @abstract function will load area as per city selected
 */
	function loadAreaAjax()
	{
		$city_name = $this->input->post('city_name');
		$state_id = $this->input->post('sta_id');
		if($city_name!='' && $state_id)
		{
			echo loadArea($city_name,$state_id);
		}
		else
		{
			echo '<option value="">- Select City First -</option>';	
		}
	}

/*
 * @author   Cloudwebs
 * @abstract function will load pincode as per area selected
 */
	function loadPincodeAjax()
	{
		$area_name = $this->input->post('area_name');
		$city_name = $this->input->post('city_name');
		$state_id = $this->input->post('sta_id');
		if($area_name!='')
		{
			echo json_encode(loadPincode($area_name,$city_name,$state_id));
		}
		else
		{
			return json_encode(array('pincode_id'=>'','pincode'=>''));	
		}
	}
	
/*
 * @author   Cloudwebs
 * @abstract function will save edited address of user
 */
	function editAddress()
	{
		$returnArr = array();
		
		if($this->customer_id==0)
		{
			redirect(site_url('checkout'));	
		}
		
		$this->form_validation->set_rules('customer_address_firstname_shipp','First Name','trim|required');
		$this->form_validation->set_rules('customer_address_address_shipp','Address','trim|required|min_length[10]');
		$this->form_validation->set_rules('country_shipp','country','trim|required');
		$this->form_validation->set_rules('state_id_shipp','State','trim|required');
		//$this->form_validation->set_rules('address_city_shipp','City','trim|required');
		$this->form_validation->set_rules('customer_address_landmark_area_shipp','Area','trim|required');
		$this->form_validation->set_rules('pincode_shipp','Pincode','trim|required');
		$this->form_validation->set_rules('customer_address_phone_no_shipp','Mobile No','trim|required');
	
		if($this->form_validation->run() == FALSE)
		{
			$returnArr['type'] = 'error';
			$returnArr['error'] = $this->form_validation->get_errors();
		}
		else 
			$returnArr = $this->che->editAddress();
			
		echo json_encode($returnArr);
	}


/*
 * @author   Cloudwebs
 * @abstract function will apply address at browser side in ajax call 
 */
	function applyAddress()
	{
		$data['customer_address_id'] = $this->input->post('id');
		$data['class'] = $this->input->post('type');
		$data['is_read_only'] = $this->input->post('read');
		echo $this->load->view('elements/customer_address',$data);
	}

/*
 * @author   Cloudwebs
 * @abstract function will save shipp/bill adresses if required and proceed to next payment info
 */
	function applyShipInfo()
	{
		$returnArr =array();
		
		if($this->customer_id==0)
		{
			redirect(site_url('checkout'));	
		}
		
		$is_validation_req = false;
		$customer_address_id_shipp = $this->input->post('customer_address_id_shipp');
		$edit_shipp = $this->input->post('edit_shipp');
		$customer_address_id_bill = $this->input->post('customer_address_id_bill');
		$edit_bill = $this->input->post('edit_bill');
		$same_as_billing_address = $this->input->post('same_as_billing_address');
		
		$order_is_gift_wrap = $this->input->post('order_is_gift_wrap');
		if((int)$order_is_gift_wrap == 1)
		{
			//set session for gift wrapping
			$this->session->set_userdata('order_is_gift_wrap',true);
		}
		else
		{
			//unset session for gift wrapping
			$this->session->unset_userdata('order_is_gift_wrap');
		}
	
		if($customer_address_id_shipp==0 || $edit_shipp==1) //validate if save mode or edit mode is on
		{
			$is_validation_req=true;
			$this->form_validation->set_rules('customer_address_firstname_shipp','First Name','trim|required');
			$this->form_validation->set_rules('customer_address_address_shipp','Address','trim|required|min_length[10]');
			$this->form_validation->set_rules('country_shipp','country','trim|required');
			//$this->form_validation->set_rules('state_id_shipp','State','trim|required');
			$this->form_validation->set_rules('address_city_shipp','City','trim|required');
			$this->form_validation->set_rules('customer_address_landmark_area_shipp','Area','trim|required');
			$this->form_validation->set_rules('pincode_shipp','Pincode','trim|required');
			$this->form_validation->set_rules('customer_address_phone_no_shipp','Mobile No','trim|required');
		}
	
		if((int)$same_as_billing_address!=1 && $customer_address_id_bill==0) //validate if diff address and save mode is on
		{
			$is_validation_req=true;
			$this->form_validation->set_rules('customer_address_firstname_bill','First Name','trim|required');
			$this->form_validation->set_rules('customer_address_address_bill','Address','trim|required|min_length[10]');
			$this->form_validation->set_rules('country_bill','country','trim|required');
			//$this->form_validation->set_rules('state_id_bill','State','trim|required');
			$this->form_validation->set_rules('address_city_bill','City','trim|required');
			$this->form_validation->set_rules('customer_address_landmark_area_bill','Area','trim|required');
			$this->form_validation->set_rules('pincode_bill','Pincode','trim|required');
			$this->form_validation->set_rules('customer_address_phone_no_bill','Mobile No','trim|required');
		}
	
		
		if($is_validation_req && $this->form_validation->run() == FALSE)
		{
			$returnArr['type'] = 'error';
			$returnArr['error'] = $this->form_validation->get_errors();
		}
		else 
		{
			$returnArr = $this->che->applyShipInfo(); 

			if($returnArr['type']=='success')
			{
				$returnArr = $this->checkShipAvail();
			}
				
			//set type to warning if it is error because error type used only for validation errors
			if($returnArr['type'] == 'error')
				$returnArr['type'] = 'warning';

			//set session that shipping is okay
			if($returnArr['type']=='success')
			{
				$this->session->set_userdata(array('is_shipping_valid'=>true)); 		
			}
			else
			{
				$this->session->set_userdata(array('is_shipping_valid'=>false)); 				
			}
		}
			
		echo json_encode($returnArr);
	}

/*
 * @author   Cloudwebs
 * @abstract functoin will check shipp availablity as per shipping code
 */
	function checkShipAvail()
	{
		return cart_hlp_checkShipAvail( true );
	}
	

//Address info end *******************************************************************//



//Order transactios:  insert order - payment processing 

	
/**
 * @deprecated
 * @author   Cloudwebs
 * @abstract function will display payment methods
 */
	function payMethods()
	{
		echo $this->load->view('elements/payment_method'); 
	}

/*
 * @author   Cloudwebs
 * @abstract functoin will complete all process related to making of payment and creating new order
 */
	function payment()
	{
		cart_hlp_payment( true ); 
	}

/**
 * added On 17-06-2015: explicit cancel order processing
 */
	function orderCanceled()
	{
		cart_hlp_orderCanceled( true );
	}
	
	
/**
+-----------------------------------------------+
	User will be redirect here after successfull payment
	another url called from payment gateway.
+-----------------------------------------------+
*/	
	function orderSuccess()
	{
		cart_hlp_orderSuccess( true ); 
	}
	
/*
+-----------------------------------------------+
	User will be redirect here after failed payment
	another url called from payment gateway.
+-----------------------------------------------+
*/	
	function orderFailed()
	{
		cart_hlp_orderFailed( true ); 
	}
		
/*
+-----------------------------------------------+
	@author Cloudwebs
	@abstract function will complete post payment order processing on session time out and once user login again
+-----------------------------------------------+
*/	
	function completeOrdOnTimeOut()
	{
		cart_hlp_completeOrdOnTimeOut( true ); 
	}
		
/**
 * @author   Hitesh Khunt
 * @abstract Thank you msg after checkout msg complete
 */
	function thankyou()
	{
		cart_hlp_thankyou( true ); 
	}
	
/**
 * @author   Hitesh Khunt
 * @abstract Failure msg in case checkout process has raised some errors
 */
	function failure()
	{
		cart_hlp_failure( true ); 
	}


	//PayPal Methods 

/**
 * @abstract paypal IPN: notify_url implementation
 * @author Cloudwebs
 */	
	function paypalNotify_sandbox
	()
	{
		$this->paypalNotify( true );
	}

/**
 * @abstract paypal IPN: notify_url implementation
 * @author Cloudwebs
 */	
	function paypalNotify( $is_sandbox=false )
	{
		// Set this to 0 once you go live or don't require logging.
		define("DEBUG", 1);
		
		// Set to 0 once you're ready to go live
		if( $is_sandbox )
			define( "USE_SANDBOX", 1);
		else
			define( "USE_SANDBOX", 0);
		
		//allow only calls from paypal: implement some mechanism
		

		if( DEBUG == 1 )
		{
			error_reporting(E_ALL);
			ini_set("display_errors", 1);
		}
		
		if( DEBUG == 1 )
		{
			errorLog( 'PAYPAL_IPN_DEBUG', ' PayPal IPN listener has detected a notification at: '.date("Y-m-d H:i:s") );
		}
		
		http_response_code(200);

		if( DEBUG == 1 )
		{
			errorLog( 'PAYPAL_IPN_DEBUG', ' PayPal IPN listener has sent 200 code back at: '.date("Y-m-d H:i:s") );
		}
		
		// Read POST data
		// reading posted data directly from $_POST causes serialization
		// issues with array data in POST. Reading raw POST data from input stream instead.
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = array();
		foreach ($raw_post_array as $keyval)
		{
			$keyval = explode ('=', $keyval);
			if (count($keyval) == 2)
			{
				$myPost[$keyval[0]] = urldecode($keyval[1]);
			}
		}
		
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		if(function_exists('get_magic_quotes_gpc'))
		{
			$get_magic_quotes_exists = true;
		}
		
		foreach ($myPost as $key => $value)
		{
			if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1)
			{
				$value = urlencode(stripslashes($value));
			}
			else
			{
				$value = urlencode($value);
			}
			$req .= "&$key=$value";
		}
		
		// Post IPN data back to PayPal to validate the IPN data is genuine
		// Without this step anyone can fake IPN data
		if( USE_SANDBOX == 1 || $myPost['payer_email'] == 'perriantech-facilitator@gmail.com' )
		{
			$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		}
		else
		{
			$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
		}
		
		
		$curl_error = array(); 	//catch curl error in this array is called by reference

		$headerA = array( CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_FORBID_REUSE => 1 );
		if( DEBUG == 1 ) 
		{
			$headerA[ CURLOPT_HEADER ] = 1;
			$headerA[ CURLINFO_HEADER_OUT ] = 1;
		}
		
		$headerA[ CURLOPT_HTTPHEADER ] = array('Connection: Close');
		

		$response = curl_post( $paypal_url, $req, $headerA, true, $curl_error, 30 );				
		
		if ( isset( $curl_error['error'] ) ) // cURL error
		{
			errorLog( 'PAYPAL_IPN_DEBUG', "Can't connect to PayPal to validate IPN message curl error: ".curl_error($ch).PHP_EOL." IPN msg was: ".$req.PHP_EOL." At:".date("Y-m-d H:i:s"));
		} 
		else
		{
			// Log the entire HTTP response if debug is switched on.
			if(DEBUG == true)
			{
				errorLog( 'PAYPAL_IPN_DEBUG', "HTTP request of validation request: " . $curl_error['info'] . PHP_EOL . " for IPN payload: ". $req .date("Y-m-d H:i:s") );
				errorLog( 'PAYPAL_IPN_DEBUG', "HTTP response of validation request: " . $response . PHP_EOL . "At:" .date("Y-m-d H:i:s") );
				
				// Split response headers and payload
				//list($headers, $response) = explode("\r\n\r\n", $response, 2);
			}
		}
		
		if( strcmp ($response, "VERIFIED") == 0 )
		{
			if( DEBUG == 1 )
			{
				//errorLog( 'PAYPAL_IPN_DEBUG', ' PayPal sends a single that IPN is VERIFIED at: '.date("Y-m-d H:i:s") );
			}

			//verify receiver_email
			if( $myPost['receiver_email'] == 'admin@'.baseDomain().'.com.au' )
			{
				$res_transaction = exeQuery( " SELECT * FROM order_transaction WHERE transaction_id='".$myPost['item_number']."' ORDER BY order_transaction_id DESC LIMIT 1" );
				
				if( ( !empty($res_transaction) && isset($myPost['payment_status']) && $res_transaction['payment_status'] != $myPost['payment_status'] ) || 
					( USE_SANDBOX == 1 || $myPost['payer_email'] == 'perriantech-facilitator@gmail.com' ) )
				{
					//for more detail visit: https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNandPDTVariables/
					$data_transaction['order_id'] = $res_transaction['order_id'];
					$data_transaction['transaction_id'] = $res_transaction['transaction_id'];
					$data_transaction['payment_method_id'] = $res_transaction['payment_method_id'];
					$data_transaction['currency_id'] = $res_transaction['currency_id'];
					$data_transaction['payment_status'] = @$myPost['payment_status'];
					$data_transaction['payment_gateway_transaction_id'] = @$myPost['txn_id'];
					$data_transaction['card_account_number'] = @$myPost['payer_id']; //payer_id is always as account_number in PAYPAL
					$data_transaction['payment_response_msg'] = serialize( $myPost );	//whole response serialized
					$data_transaction['pg_type'] = @$myPost['txn_type'];	//Payment received; source is any of the following:
																				 //A Direct Credit Card (Pro) transaction
																				 //A Buy Now, Donation or Smart Logo for eBay auctions button
		
					$data_transaction['bank_ref_num'] = @$myPost['verify_sign'];	//verify sign for PayPal
					$data_transaction['error_code'] = '';		//nothing stored for PayPal as worst case is not bound to failure
					$data_transaction['error_message'] = '';	 //nothing stored for PayPal as worst case is not bound to failure
				
					orderTransaction( $data_transaction );
					
					if( strtolower( $myPost['payment_status'] ) == 'completed' )
					{
						
						$order_total_amt = (int)getField( "order_total_amt", "orders", "order_id", $res_transaction['order_id'] );
						$myPost['mc_gross'] = (int)lp_rev( $myPost['mc_gross'], $res_transaction['currency_id'] );
						
						if( ( $order_total_amt == $myPost['mc_gross'] ) || ( ( $order_total_amt + 1 ) == $myPost['mc_gross'] ) || ( $order_total_amt == ( $myPost['mc_gross'] + 1 ) ) )
						{
							//order tracking entry
							$order_pend_status_id = getField( 'order_status_id', 'order_status', 'order_status_key', 'PAYMENT_APPROVED');
							$resOrdDet = $this->db->query('SELECT order_details_id FROM order_details WHERE order_id='.$res_transaction['order_id'].'')->result_array();
			
							$data_order_tracking['order_id'] = $resArr['order_id'];
							$data_order_tracking['order_status_id'] = $order_pend_status_id;
							foreach( $resOrdDet as $k=>$ar )
							{
								$data_order_tracking['order_details_id'] =  $ar['order_details_id'];
								$this->db->insert("order_tracking",$data_order_tracking);
							}
							
							//send confirmation mail
							orderEmail( $res_transaction['order_id'], 'PAYMENT_APPROVED', 'Payment Successfull.', 0, $res_transaction['currency_id']);

							errorLog( 'PAYPAL_IPN_DEBUG', 'IPN notification msg recieved, price match succed and mail sent. Original: '.$order_total_amt.' PayPal: '.$myPost['mc_gross'].' at: '.date("Y-m-d H:i:s") );
						}
						else
						{
							errorLog( 'PAYPAL_IPN_DEBUG', 'IPN notification msg recieved, price does not match. Original: '.$order_total_amt.' PayPal: '.$myPost['mc_gross'].' at: '.date("Y-m-d H:i:s") );
						}

					}

					if( DEBUG == 1 )
					{
						errorLog( 'PAYPAL_IPN_DEBUG', 'IPN notification msg recieved, payment_status:'.$myPost['payment_status'].' at: '.date("Y-m-d H:i:s") );
					}
					
				}
				else if( !empty($res_transaction['payment_status']) )
				{
					if( DEBUG == 1 )
					{
						errorLog( 'PAYPAL_IPN_DEBUG','IPN duplicate msg detected with payment_status:'.$myPost['payment_status'].' for txn_id:'.$myPost['txn_id'].' at: '.date("Y-m-d H:i:s") );
					}
				}
				else
				{
					if( DEBUG == 1 )
					{
						errorLog( 'PAYPAL_IPN_DEBUG', ' Unusual behaviour detected whicle checking IPN duplicate msg for txn_id:'.$myPost['txn_id'].' at: '.date("Y-m-d H:i:s") );
					}
				}
				
			}
			else
			{
				if( DEBUG == 1 )
				{
					errorLog( 'PAYPAL_IPN_DEBUG', ' IPN spoof detected with receiver_email='.$myPost['receiver_email'].' at: '.date("Y-m-d H:i:s") );
				}
			}
		}
		else
		{
			if( DEBUG == 1 )
			{
				errorLog( 'PAYPAL_IPN_DEBUG', ' PayPal sends a single that IPN is INVALID at: '.date("Y-m-d H:i:s") );
			}
		}

	}	


	//PayPal Methods end 
	
	
	

//Order transactios end *******************************************************************//
	
	
	
	
//Test functions 

	
	function paymentPayU()
	{
		$_POST['amount'] = 15;
		$_POST['email'] = 'hi0001234d@gmail.com';
		$_POST['firstname'] = 'Cloudwebs';
		$_POST['phone'] = '8866526465';
		$_POST['productinfo'] = 'Testing';
		$this->load->view('payuform');
	}
	
	function paymentPaypal()
	{
		$_POST['amount'] = 1;
		$_POST['email'] = 'perriantech@gmail.com';
		$_POST['firstname'] = 'Perrian';
		$_POST['phone'] = '9876543210';
		$_POST['productinfo'] = 'Testing pro';
		$this->load->view('paypal_form');
	}
	
	function test()
	{
		pr($_POST);	
	}
	

//Test functions end *************************************************************//	
	

}

