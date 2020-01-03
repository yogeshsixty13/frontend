<?php
/**
 * @package pr_: login_signup_hlp
 * @author Cloudwebs Tech Dev Team
 * @version 1.9
 * @abstract login end signup helper except user  which features are separate in all helper
 * @copyright Perrian Tech
 */

/**
 * function will check IF EMAIL is signed up
 */
function isEmailSignedUp( $email_id )
{
	return checkIfRowExist( "SELECT 1 FROM customer WHERE customer_emailid='".$email_id."' AND customer_group_id 
							 IN ( SELECT customer_group_id FROM customer_group 
									  WHERE customer_group_type='U' ) " );
}


/**
 * function will check and update customer group to U from if G is current one
 */
function checkAndUpdateGuestCustomerGroup( $customer_id, $customer_group_type )
{
	if( $customer_group_type == "G" )
	{
		$CI =& get_instance(); 
		$data['customer_group_id'] = getField('customer_group_id','customer_group','customer_group_type','U');
		$CI->db->where("customer_id", $customer_id)->update("customer", $data);
	}
}

/**
 * @author Cloudwebs
 * @abstract function will generate secure token for customer direct access from mail links
 */
function GetCustomerToken($customer_id)
{
	$CI =& get_instance();
	$customer_access_validation_token = getField("customer_access_validation_token","customer","customer_id",$customer_id);

	if(empty($customer_access_validation_token))
	{
		$customer_access_validation_token = substr(hash('sha256', mt_rand() . microtime()), 0, 50);

		$CI->db->query("UPDATE customer SET customer_access_validation_token='".$customer_access_validation_token."' WHERE customer_id=".$customer_id."");
	}

	unset($CI);
	return $customer_access_validation_token;

}


/**
 * function will let user log in using access token 
 */
function letLoginUsingAccessToken()
{
	$CI =& get_instance(); 
	$customer_access_validation_token = $CI->input->get('acc');
		
	$resAcc = executeQuery("SELECT customer_id, customer_emailid, customer_group_type, customer_status
										FROM customer c INNER JOIN customer_group g
										ON g.customer_group_id=c.customer_group_id
										WHERE customer_access_validation_token='".$customer_access_validation_token."'");
	if(!empty($resAcc))
	{
		//allowed login to user who access toekn either G or U
		if( $resAcc[0]['customer_group_type'] == 'U' || $resAcc[0]['customer_group_type'] == 'G' )
		{
			if($resAcc[0]['customer_status']==0)
			{
				checkAndUpdateGuestCustomerGroup( $resAcc[0]['customer_id'], $resAcc[0]['customer_group_type']); 
				
				//set all login sessions and upd cart/wish database
				setLoginSessions($resAcc[0]['customer_id'], 'U', $resAcc[0]['customer_emailid']);
	
				redirect(customizeRedUrl($_SERVER['REDIRECT_QUERY_STRING']));
			}
			else
			{
				setFlashMessage('error',"Your access not allowed. please conact <a href='mailto:support@".baseDomain().">support@".baseDomain()."</a>");
				redirect('login');
			}
		}
		else
		{
			setFlashMessage('error',"Your access not allowed. please conact <a href='mailto:support@".baseDomain()."'>support@".baseDomain()."</a>");
			redirect('login');
		}
	}
	else
	{
		redirect('login');
	}
}


/*
 +------------------------------------------------------------------+
Function is save admin log.
@params : $email_id -> email address
$el_optout_level -> opt level as per web standard
$el_status -> status
$el_reference_source -> name of reference source
+------------------------------------------------------------------+
*/
function saveEmailList($email_id, $el_optout_level, $el_status, $el_reference_source, $el_priority_level="0" )
{
	$CI =& get_instance();
	$emailListArr = $CI->db->where('email_id',@$email_id)->get('email_list')->row_array();
	if(empty($emailListArr))
	{
		//$el_optout_level = (empty($emailListArr)) ? 0 : @$el_optout_level ;
		$data = array(
				'email_id' => $email_id,
				'el_optout_level' => @$el_optout_level,
				'el_status' => @$el_status,
				'el_reference_source' => @$el_reference_source,
				'el_priority_level' => @$el_priority_level
		);

		$CI->db->insert('email_list', $data);
		return $CI->db->insert_id();
	}
	else if($emailListArr['el_status'] != 'S')
	{
		$emailData = array(
				'el_optout_level' => @$el_optout_level,
				'el_status' => @$el_status,
				'el_reference_source' => @$el_reference_source,
				'el_priority_level' => @$el_priority_level
		);

		$CI->db->where('email_list_id',$emailListArr['email_list_id'])->update('email_list',$emailData);
		return $emailListArr['email_list_id'];
	}
	else{
		return $emailListArr['email_list_id'];
	}

}
/*
 * Function will saved customer data
* @params : $email_id -> email address
*           $customer_data -> customer data
*/
function saveCustomer($email_id='', $customer_data)
{
	$CI =& get_instance();
	$getCustomerArr = $CI->db->where('customer_emailid',@$email_id)
	->get('customer')
	->row_array();

	$customer_data['manufacturer_id'] = MANUFACTURER_ID;
	if(empty($getCustomerArr))
	{
			
		$CI->db->insert('customer',$customer_data);
		return $CI->db->insert_id();
	}
	else
	{
		$CI->db->where('customer_emailid',@$email_id)->update('customer',$customer_data);
		return $getCustomerArr['customer_id'];
	}
}

/**
 * @author Cloudwebs
 * @abstract function will set all sessions related to login and perform other login related activity
 */
function setLoginSessions($customer_id,$customer_group_type,$customer_emailid, $is_reInstantiatedSession=false, $session=null)
{
	$CI =& get_instance();
	if( $session == null )
	{
		$session = $CI->session;	
	}	
	
	$data['customer_id'] = $customer_id;
	$data['customer_group_type'] = $customer_group_type;
	$data['customer_emailid'] = $customer_emailid;

	$session->set_userdata($data); //set session store in user details

	if( $customer_group_type != 'C' )
	{
		if( !$is_reInstantiatedSession )
		{
			updCartDatabase(0,0,false,true,'',$customer_id);
			updWishDatabase(0,true,'',$customer_id);
				
			saveLogins( $customer_id , $customer_group_type, $session);
		}
	}
	
	/**
	 * 
	 */
	if( is_restClient() ) 
	{
		$data["customer_firstname"] = exeQuery("SELECT customer_firstname FROM customer WHERE customer_id=".$customer_id." ", 
							  					true, "customer_firstname" ); 
		return $data; 
	}
}

/**
 * @author Cloudwebs
 * function will check if user is logged in
 */
function isLoggedIn()
{
	$CI =& get_instance();

	if( $CI->session->userdata('customer_id') === FALSE || $CI->session->userdata('customer_group_type') == 'G' || $CI->session->userdata('customer_group_type') == 'C' )
	{
		return false;
	}
	else
	{
		return true;
	}
}


/*
 * @author Cloudwebs
* @abstract function will set all sessions related to login and perform other login related activity
*/
function saveLogins( $cust_admin_user_id , $l_user_type, $session=null)
{
	$CI =& get_instance();
	if( $session == null )
	{
		$session = $CI->session;
	}

	$data['cust_admin_user_id'] = $cust_admin_user_id;
	$data['sessions_id'] = $session->userdata('sessions_id');
	
	//$data['session_id'] = session_id();
	$data['l_user_type'] = $l_user_type;
	$agentArr = getUserAgentDevice();
	$data['l_user_agent'] = $agentArr['l_user_agent'];
	$data['l_user_device'] = $agentArr['l_user_device'];
	$CI->db->insert( "logins", $data);

	$dataIp['cust_admin_user_id'] = $cust_admin_user_id;
	$dataIp['sessions_id'] = $session->userdata('sessions_id');
	//$dataIp['session_id'] = $data['session_id'];
	$dataIp['li_user_type'] = $l_user_type;
	$dataIp['li_ip'] = $_SERVER['REMOTE_ADDR'];
	$dataIp['li_user_agent'] = $agentArr['l_user_agent'];
	$dataIp['li_user_device'] = $agentArr['l_user_device'];
	$CI->db->insert( "login_ip", $dataIp);
}

/**
 * @author Cloudwebs
 * @abstract function will unset all sessions related to login and perform other logout related activity
 */
function unsetLoginSessions($is_empty_cart=true, $customer_id=0, $cartArr='', $wishArr='')
{
	$CI =& get_instance();

	//unset customer_id,shipp add,bill add,Cart and wish sessions
	$arr = array('customer_id'=>'','customer_emailid'=>'','customer_group_type'=>'');
	if($is_empty_cart===true)
	{
		/**
		 * here it is better if cart_helper::unsetCheckOutSession function is called to flush all the cart session neatly.
		 */
		$arr['customer_shipping_address_id'] = '';
		$arr['customer_billing_address_id'] = '';
		$arr['cartArr'] = '';
		$arr['wishArr'] = '';
	}
	else
	{
		if(isset($cartArr[$customer_id]))
		{
			$cartArr[0] = $cartArr[$customer_id];
			unset($cartArr[$customer_id]);
		}

		if(isset($wishArr[$customer_id]))
		{
			$wishArr[0] = $wishArr[$customer_id];
			unset($wishArr[$customer_id]);
		}

		$sessArr = array('cartArr'=>$cartArr, 'wishArr'=>$wishArr);
		$CI->session->set_userdata($sessArr);
	}

	//update login table
	$CI->db->query("UPDATE logins SET l_session_status=0, l_reason_key=1, l_modified_time=NOW()
						WHERE (sessions_id=".$CI->session->userdata('sessions_id')." AND cust_admin_user_id=".(int)$CI->session->userdata('customer_id').")
						AND l_user_type='U' ");

	$CI->session->unset_userdata($arr);

	/**
	 * whole session destroy is turned off on 06-04-2015 once again, as it affects all other important session status.
	 * For actual checkout problem noted on 02-04-2015 has some other reason, so it does not make any sense to destroy whole session.
	*/
	//sessionDestroy();
}

/*
 * @author Cloudwebs
* @abstract function will unset all sessions related to login and perform other logout related activity
*/
function unsetLoginSessionsAdmin( $sessArr )
{
	$CI =& get_instance();
		
	//update login table
	$CI->db->query("UPDATE logins SET l_session_status=0, l_reason_key=1, l_modified_time=NOW()
						WHERE (sessions_id=".(int)$CI->session->userdata('sessions_id')." AND cust_admin_user_id=".(int)$CI->session->userdata('admin_id').")
						AND l_user_type='A' ");

	$CI->session->unset_userdata( $sessArr );
	sessionDestroy();
}

/**
 * session destroy
 */
function sessionDestroy()
{
	session_destroy();
	session_unset();
}

/**
 * @author Cloudwebs
 * @abstract function get admin session id: used for admin panel
 */
function getAdminSessionId( )
{
	return session_id();
}
function access_denied()
{
	echo "<h1>Access Denied</h1>
			<a href='".base_url()."home'>Go to Home</a>";
	die;
}

/**
 *	@author Cloudwebs
 *	@abstract function will logout the logged in user and redirects to url specified
 */
function logout($red_url='',$is_set_flash=false, $is_empty_cart=true, $customer_id=0, $cartArr='', $wishArr='')
{
	$CI =& get_instance();
	if($CI->session->userdata('customer_id'))
	{
		unsetLoginSessions($is_empty_cart, $customer_id, $cartArr, $wishArr);

		if($is_set_flash)
			setFlashMessage('success','You are successfully logged out.');
			
		if($red_url!='')
			redirect($red_url);
			
		return array('type'=>'success','msg'=>'You are successfully logged out.');
	}
	else
	{
		return array('type'=>'error','msg'=>'You are already logged out.');
	}
}

/**
 * function will re instantiate session when session timed OUT on server: only applicable to app's REST clients
 * However it does not seem standard approach better if session is in DB, so that they will stay alive.
 */
function reInstantiateSession( $session_id, $session=null )
{
	if( !empty( $session_id ) )
	{
		$row = exeQuery( "SELECT s.sessions_id, c.customer_id, cg.customer_group_type, c.customer_emailid 
						  FROM sessions s
						  INNER JOIN logins l
						  ON l.sessions_id=s.sessions_id
						  INNER JOIN customer c
						  ON c.customer_id=l.cust_admin_user_id
						  INNER JOIN customer_group cg
						  ON cg.customer_group_id=c.customer_group_id
						  WHERE s.session_id='".$session_id."' AND l.l_session_status=1 " );
		
		if( !isEmptyArr($row) )
		{
			$session->set_userdata( array( 'sessions_id'=> $row["sessions_id"] ) );
			setLoginSessions($row["customer_id"], $row["customer_group_type"], $row["customer_emailid"], true, $session); 
				
			setFlashMessage( "warning", "Your App session has been refreshed by server.", $session );
				
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

?>