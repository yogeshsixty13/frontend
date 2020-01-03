<?php
// referrece link : http://codeigniter.com/user_guide/general/hooks.html
class customHook extends CI_Hooks
{
	 
	 public $CI;
	 public $is_ajax = false;
	 
	 function __construct() 
	 {
		parent::__construct();
		$this->CI = get_instance();
		
		$this->is_ajax = $this->CI->input->is_ajax_request();
		
		//record access log
		if( !$this->is_ajax )
		{
			//recordPageAccess();
		}
	 }
	 
/*
+++++++++++++++++++++++++++++++++++++++++++++
	This function will detect admin url and 
	check for session, if session null then 
	check for cookie, if cookie exist then
	take username from it work ahead.
+++++++++++++++++++++++++++++++++++++++++++++
*/
	 function adminAutoLogin()
	 {
		$logins = array('activateAccount','createNewAccount','forgotPassword','resetPassword','resendActivationLink');
		 //check for admin url access or not
		if($this->CI->router->directory == 'admin/')
		{
			//checking for admin session.: cookie removed from 31/1/2014
			if(!$this->CI->session->userdata('admin_id')):
				
					//pr(getFlashMessage('admin_referrer'));die;
					if($this->CI->router->class != 'lgs')
						$this->_setReferrerAdmin();
						
					$auth = array('dashboard','accountSettings','checkAdminPassword','logout');
					if($this->CI->input->is_ajax_request())
					{
						echo '<script>location.reload();</script>';
						die;
					}
					
					if($this->CI->router->class == 'lgs' && in_array($this->CI->router->method,$auth))
						redirect('admin/lgs');
					else if($this->CI->router->class != 'lgs')
						redirect('admin/lgs');
			endif;
		}
		else if($this->CI->router->class == 'account' || $this->CI->router->class == 'rest_account')
		{
			
			//pr($_SESSION);die;
			//$this->CI->session->set_userdata(array('chat_id'=>FALSE,'customer_id'=>FALSE,'customer_group_type'=>FALSE));
			//checking for customer session.
			if( !$this->CI->session->userdata('customer_id') && !in_array($this->CI->router->method,$logins) )
			{
				
				if(isset($_GET['acc']))	//direct login from mail
				{
					letLoginUsingAccessToken();
				}
				else
				{
					$this->_setReferrerCustomer();
					if(!$this->CI->session->userdata('customer_id') && $this->CI->input->is_ajax_request() && $this->CI->router->class == 'account')
					{
						if( is_restClient() )
						{
							$this->_REST_hook_failed();
						}
						else 
						{
							echo json_encode(array('type'=>'error','msg'=>'Error: You are not logged in please <a href="'.site_url('login').'">login</a> first.'));
						}
						die;
					}
					
					//functions which are required authentication
					$auth = array('dashboard','logout');
					$accesDenied = array('account');
					
					if($this->CI->router->class == 'login' && in_array($this->CI->router->method,$auth))
					{
						if( is_restClient() )
						{
							$this->_REST_hook_failed();
						}
						else 
						{
							redirect('login');
						}
					}
					else if(in_array($this->CI->router->class,$accesDenied))
					{
						if( is_restClient() )
						{
							$this->_REST_hook_failed();
						}
						else 
						{
							redirect('login');
						}
					}
				}			
			}
			else if($this->CI->session->userdata('customer_group_type')=="C") //check chat user
			{
				if( is_restClient() )
				{
					$this->_REST_hook_failed();
				}
				else 
				{
					redirect('login');
				}
			}
		}
		
		 //echo 'Wow!! Great hook called';
	 }
	 
	
/*
+++++++++++++++++++++++++++++++++++++++++++
setting referrring url. admin will redirect 
this url after successfull login
+++++++++++++++++++++++++++++++++++++++++++
*/
	private function _setReferrerAdmin()
	{
		if(isset($_SERVER['REDIRECT_QUERY_STRING']))
		{
			if(($this->CI->uri->rsegment('1') == 'login' && $this->CI->uri->rsegment('2') != 'index') || $this->CI->uri->rsegment('1') != 'login')
				setFlashMessage('admin_referrer',$_SERVER['REDIRECT_QUERY_STRING']);
		}
	}
	
/*
+++++++++++++++++++++++++++++++++++++++++++
setting referrring url. Client will redirect 
this url after successfull login
+++++++++++++++++++++++++++++++++++++++++++
*/	
	private function _setReferrerCustomer()
	{
		if(isset($_SERVER['REDIRECT_QUERY_STRING']))
		{
			if(($this->CI->uri->rsegment('1') == 'login' && $this->CI->uri->rsegment('2') != 'index') || $this->CI->uri->rsegment('1') != 'login')
				setFlashMessage('customer_referrer',$_SERVER['REDIRECT_QUERY_STRING']);
		}
	}
	
/**
 * 
 */
	private function _REST_hook_failed()
	{
		$data = array();
		$data["_redirect"] = "login";
		$data["_rparam"] = "";
		$data["flash"]["error"] = "You are not logged in please login first";
		echo json_encode( $data );
		exit(1); 
	}
}