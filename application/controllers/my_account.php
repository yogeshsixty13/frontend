<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class my_account extends CI_Controller 
{
	//parent constructor will load model inside it
	function __construct()
	{
	    
	    header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
		parent::__construct();
       
		if( !$this->session->userdata('token'))
		    redirect('login');
	}
	
	/**
	 * 
	 */
	function index()
	{
	    $data = array();
	    $data['pageName'] = 'my_task';
		$this->load->view('site-layout',$data);
	}
	/**
	 *
	 */
	function getAllNotification()
	{
	    $data['listArr'] = getAllNotification();
	    $data['pageName'] = 'notification';
	    $this->load->view('site-layout',$data);
	}
	/**
	 *
	 */
	function profile()
	{
	    $data = array();
	    $data['listArr'] = getRequestedDataFromURL("profile-fetch/");
	   
	    $data['pageName'] = 'profile';
	    $this->load->view('site-layout',$data);
	    
	}
	/**
	 *
	 */
	function remove_education()
	{
	    $id = $this->input->post('id');
	    $data = array("education_id" => $id);
	    $data_string = json_encode($data);
	    $result = postDataSubmit( getSysConfig('api_url')."remove/education/", $data_string );
	    if($result->status)
	    {
	        echo json_encode($result);
	    }
	    else 
	    {
	        echo json_encode($result);
	    }

	}
	/**
	 *
	 */
	function add_backgroundimg()
	{
	    $data = $this->input->post();
	    pr($data);die;
	    $data = array("policeVerificationDocument" => $data);
	    $data_string = json_encode($data);
	    $result = postDataSubmit( getSysConfig('api_url')."submit/police/verification/document/", $data_string );
	    
	    if($result->status)
	    {
	        echo json_encode(array('type'=>'success','msg'=>$result->response) );
	    }
	    else
	    {
	        echo json_encode(array('type'=>'error','msg'=>$result->response) );
	    }
	    
	}
	/**
	 *
	 */
	function add_education()
	{
	    $education = $this->input->post('education_title');
        $data = array("education_title" => $education);
        $data_string = json_encode($data);
	    $result = postDataSubmit( getSysConfig('api_url')."add-education/", $data_string );
	    if($result->status)
	    {
	        echo json_encode(array('type'=>'success','msg'=>$result->response));
	    }
	    else
	    {
	        echo json_encode(array('type'=>'success','msg'=>$result->response));
	    }
	    
	    
	}
	/**
	 *
	 */
	function remove_transpotation()
	{
	    $id = $this->input->post('id');
	    $data = array("transpotation_id" => $id);
	    $data_string = json_encode($data);
	    $result = postDataSubmit( getSysConfig('api_url')."transpotation/remove/profile/", $data_string );
	    if($result->status)
	    {
	        echo json_encode($result);
	    }
	    else
	    {
	        echo json_encode($result);
	    }
	    
	}
	/**
	 *
	 */
	function add_transpotation()
	{
	    $transpotation = $this->input->post('transportation_type');
	  
	    $lowername = strtolower( $transpotation );
        $transdata = explode("_", $lowername);
        $trans_name = end($transdata);
        
        $data = array("transportation_type" => $transpotation ,"transportation_name" => $trans_name);
	    $data_string = json_encode($data);
	    
	    $result = postDataSubmit( getSysConfig('api_url')."transpotation/add/", $data_string );
	  
	    if($result->status)
	    {
	        echo json_encode($result);
	    }
	    else
	    {
	        echo json_encode($result );
	    }
	    
	    
	    
	}
	/**
	 *
	 */
	function getskill()
	{
	    $data = array();
	    $data= getRequestedDataFromURL("skills/");
	    echo json_encode($data);
    }
    /**
     *
     */
	function add_skill()
	{
	    
	    $skill = $this->input->post('skill_name');
	    $data = array("skill_name" => $skill);
	    $data_string = json_encode($data);

	    $result = postDataSubmit( getSysConfig('api_url')."add-skill/", $data_string );
// 	    pr($result);die;
	    if($result->status)
	    {
	        echo json_encode(array('type'=>'success'));
	    }
	    else
	    {
	        echo json_encode(array('type'=>'error'));
	    }
	   
	}
	/**
	 * @deprecated
	 */
	function change_password()
	{
        $data = array();
        $this->form_validation->set_rules('old_password','old Password','trim|required|min_legnth[6]|max_legnth[1000]');
	    $this->form_validation->set_rules('new_password','new Password','trim|required|min_legnth[6]|max_legnth[1000]');
	    $this->form_validation->set_rules('c_password','Confirm Password','trim|required|min_legnth[1]|max_legnth[1000]|matches[new_password]');
	    
	    $old_password = $this->input->post('old_password');
	    $new_password = $this->input->post('new_password');
	    $data = array("old_password" => $old_password, "new_password" => $new_password);
	    $data_string = json_encode($data);
	    $result = postDataSubmit( getSysConfig('api_url')."change/password/", $data_string );

	    if($this->form_validation->run() == FALSE || $result->status == "false" )
        {
            $data['error'] = $this->form_validation->get_errors();
	        if(isset($result->data) && isEmptyArr($data['error']))
	            $data['response'] = $result->data;
	            
            $data['pageName'] = 'change-pass';
            $this->load->view( 'site-layout', $data );
	    }
	    else  //redirect find job
	        redirect( 'find-task' );
	}
	
	/**
	 *
	 */
	function payments_record()
	{
	    $data = array();
	    $data['listArr'] = getRequestedDataFromURL("finances/received-payment-info/");
	    $data['madePaymentArr'] = getRequestedDataFromURL("finances/payed/reciepts/all");
	    $data['pageName'] = 'payment-records';
	    $this->load->view( 'site-layout', $data );
    }
    /**
     *
     */
	function terms_conditions()
	{
	    $data['pageName'] = 'Terms_conditions';
	    $this->load->view( 'site-layout', $data );
    }
    /**
     *
     */
    function insuarance()
    {
        $data['listArr'] = getRequestedDataFromURL("documents/insurance/");
        $data['pageName'] = 'insurance';
        $this->load->view( 'site-layout', $data );
     
    }
    /**
     *
     */
    function software_licence()
    {
        $data['listArr'] = getRequestedDataFromURL("documents/software/licence/");
        $data['pageName'] = 'software_licence';
        $this->load->view( 'site-layout', $data );
        
    }
    /**
     *
     */
    function help_contactUs()
    {
        $data['pageName'] = 'support';
        $this->load->view( 'site-layout', $data );
    }
    /**
     *
     */
    function privacy_policy()
    {
        $data['pageName'] = 'policy';
        $this->load->view( 'site-layout', $data );
    }
    /**
     *
     */
    function disclaimer()
    {
        $data['listArr'] = getRequestedDataFromURL("documents/disclaimer/");
        $data['pageName'] = 'disclaimer';
        $this->load->view( 'site-layout', $data );
        
    }
    /**
     *
     */
    function remove_portfolio()
    {
        $data = $this->input->post();
        $data_string = json_encode($data);
        $result = postDataSubmit( getSysConfig('api_url')."remove/portfolio/", $data_string );
        if($result->status)
            echo json_encode($result);
        else
            echo json_encode($result);
    }
    /**
     * @deprecated
     */
    function forgot_password(){
	    
	    $this->form_validation->set_rules('username','User Name','trim|required|valid_email');
	    $username = $this->input->post();
	    $data_string = json_encode($username);
	    $result = postDataSubmit( getSysConfig('api_url')."forgot-password/", $data_string );
	  
	    if($this->form_validation->run() == FALSE || $result->status == "false" )
	    {
	        $data['error'] = $this->form_validation->get_errors();
	        if(isEmptyArr($data['error']))
	            $data['response'] = $result->response->non_field_errors[0];
	        
	            $data['pageName'] = 'forgot-password';
	            $this->load->view( 'site-layout', $data );
	    }
	    else  //redirect find job
	    {
	        $data['response'] = $result->response;
	        
	        $data['pageName'] = 'forgot-password';
	        $this->load->view( 'site-layout', $data );
	    }
	    
	}
	/**
	 *
	 */
	function edit_profile()
	{
	    $data = array();
	    $data['listArr'] = getRequestedDataFromURL("profile-fetch/");
// 	    pr($data);die;
	    $data['pageName'] = 'edit-profile';
	    $this->load->view('site-layout',$data);
	    
	}
	/**
	 *
	 */
	function add_language()
	{
	    $languges= $this->input->post('languges');
	    $data = array("languges" => $languges);
	    $data_string = json_encode($data);
	    $result = postDataSubmit( getSysConfig('api_url')."language/add/", $data_string );
	    
	    if($result->status)
	    {
	        echo json_encode($result);
	    }
	    else
	    {
	        echo json_encode($result);
	    }
	}
	/**
	 *
	 */
	function remove_language()
	{
	    $id = $this->input->post('id');
	    $data = array("languge_id" => $id);
	    $data_string = json_encode($data);
	    $result = postDataSubmit( getSysConfig('api_url')."language/remove/profile/", $data_string );
	    
	    if($result->status)
	    {
	        echo json_encode($result);
	    }
	    else
	    {
	        echo json_encode($result);
	    }
    }
    /**
     *
     */
	function update_profile()
	{
	    $_FILES['portfolioimg'];
	    $data = $this->input->post();
	    $data['skills']  = implode(",", $data['skills']);
// 	    $data['work_city'] = "[".implode(",", $data['city'])."]";
	    $data['work_city'] = implode(",", $data['city']);
// 	    unset($data['city']);
// 	    pr($data);die;
	    $data_string = json_encode($data);
	    $result = postDataSubmit( getSysConfig('api_url')."profile-update/", $data_string );
	    
// 	    pr($result);die;
// 	    $data = array();
	    $data['listArr'] = getRequestedDataFromURL("profile-fetch/");
	    $data['pageName'] = 'profile';
	    $this->load->view('site-layout',$data);
	}
	/**
	 *
	 */
	function add_portfolio()
	{
	    $data = $this->input->post();
	    pr($data);die;
	    $data = array("portfolio_image" => $data);
	    $data_string = json_encode($data);
	    $result = postDataSubmit( getSysConfig('api_url')."add-portfolio/", $data_string );
	    
	    if($result->status)
	    {
	        echo json_encode(array('type'=>'success','msg'=>$result->response) );
	    }
	    else
	    {
	        echo json_encode(array('type'=>'error','msg'=>$result->response) );
	    }
	}
	/**
	 *
	 */
	function add_licences()
	{
	    $data = $this->input->post();
	    $files = $_FILES;
	    pr($files);die;
	    $licenceType = $this->input->post('licenceType');
	    $licenceImage = $files['licenceImage']['name'];
	    $data = array("licenceType" => $licenceType ,"licenceImage" => $licenceImage);
	    $data_string = json_encode($data);
	    $result = postDataSubmit( getSysConfig('api_url')."add/licence/request/", $data_string );
	   
	    if($result->status)
	    {
	        echo json_encode(array('type'=>'success','msg'=>$result->response) );
	    }
	    else
	    {
	        echo json_encode(array('type'=>'error','msg'=>$result->response) );
	    }
	 }
}