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

class rest_account extends REST_Controller
{
    var $cTable = 'customer';
	var $cAutoId = 'customer_id';
	var $customerId=0;
	var $wishArr = array();
	var $addArr = array();
	var $is_ajax = false;
	
    function rest_account()
    {
		//parent constructor will load model inside it
		parent::__construct();
		
		$this->load->model('mdl_account','ma');
		$this->ma->cTable = $this->cTable;
		$this->ma->cAutoId = $this->cAutoId;
		
		$this->ma->customerId = $this->customerId = $this->session->userdata('customer_id');
		
		
		//check if wish session set
		if ($this->session->userdata('wishArr') !== FALSE)
		{
			$this->wishArr = $this->session->userdata('wishArr');
		}
		
		$this->ma->is_ajax = $this->is_ajax = $this->input->is_ajax_request();
    }

    /**
     * @since used to restAPI
     * customer forgot password
     */
    public function account_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_account( $this );
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * create date 11-05-2015 used to restAPI
     * forgot password Form
     */
    public function changePassword_post()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_changePassword();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * create date 11-05-2015 used to restAPI
     * Edit Account Form
     */
    public function editAccount_post()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_editAccount();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * @since
     * save / update customer Address
     */
    public function saveAddress_post()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_saveAddress( $this );
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * create date 16-05-2015 used to restAPI
     * newsletter Form
     */
    public function newsletter_post()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_newsletter();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * Create date 16-05-2015
     * get Order History data...
     */
    function order_history_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_order_history( $this->cTable, $this->cAutoId, $this->customerId );
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * Create date 16-05-2015
     * get OrderTracking data...
     */
    function order_tracking_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_order_tracking( $this );
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * Create date 16-05-2015
     * get Transaction data...
     */
    function transaction_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_transaction();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * create date 21-05-2015 used to restAPI
     * affiliate_compaign (Refferal Code) Form
     */
    public function affiliate_compaign_post()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_affiliate_compaign();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * create date 21-05-2015 used to restAPI
     * My Balance (Transaction History) Form
     */
    public function transaction_history_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_transaction_history();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * create date 25-05-2015 used to restAPI
     * Get User Address Information Form
     */
    public function address_book_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_address_book();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * create date 25-05-2015 used to restAPI
     * Get Account Information Form
     */
    public function account_detail_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_account_detail();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /*
     +-----------------------------------------+
    Callback function for check old password
    for current customer in database.
    +-----------------------------------------+
    */
    function checkForOldPassword($str)
    {
    	$ps = md5($str.$this->config->item('encryption_key'));
    	$d = $this->db->where('customer_password',$ps)->where($this->cAutoId,$this->customerId)->get($this->cTable)->row_array();
    
    	if(count($d) == 0)
    	{
    		$this->form_validation->set_message('checkForOldPassword','Please enter correct old password.');
    		return false;
    	}
    	else
    		return true;
    }
    
    /**
     * @since
     * delete old address book 
     */
    function del_address_row_post()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_del_address_row();
    		
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * @since
     * show address information
     */
    public function show_address_post()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_show_address();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }

    /**
     * @since used to restAPI
     * customer NewsLetter Subscribe or not
     */
    public function show_newsletter_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_show_newsletter();
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * @author Gautgam Kakadiya
     * @since 27-05-2015
     * scroll pagination on REST Apps liting page
     */
    function scrollPagination_order_history_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_scrollPagination_order_history($this);
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
    
    /**
     * @author Gautgam Kakadiya
     * @since 10-07-2015
     * socialmediashare_get on REST Apps liting page
     */
    function socialmediashare_get()
    {
    	$data = array();
    	$data[ getSysConfig( "rest_response_field_name" ) ] = cmn_vw_socialmediashare_get($this);
    
    	$data[ getSysConfig( "rest_status_field_name" ) ] = "success";
    	$data[ getSysConfig( "rest_message_field_name" ) ] = "";
    	$this->response( $data, 200 );
    }
}