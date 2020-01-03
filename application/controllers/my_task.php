<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class my_task extends CI_Controller 
{
    var $taskType = "myTask";
    
    //parent constructor will load model inside it
	function __construct()
	{
	    header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
		parent::__construct();
        
		$token = $this->session->userdata('token');
		if( empty( $token ) )
			redirect('login');
	}
	
	function index()
	{
	    $data = array();
	    $data['pageName'] = 'my_task';
		$this->load->view('site-layout',$data);
	}
	
	function jobPostData( )
    {
        $data = $this->input->post();
        $dataArr = explode("&",$data['data']);
        
        $postData = array();
        foreach( $dataArr as $ar)
        {
            $kvArr = explode("=",$ar);
            $postData[$kvArr[0]] = $kvArr[1];
        }
        
        $data_string = json_encode($postData);
        
     	$result = postDataSubmit( getSysConfig('api_url')."jobs/add/", $data_string );
	    
     	if( isset( $result->status ) && $result->status == "true" )
        {
    	    if( $result->status == true )
    	        echo json_encode( array('status' => true, 'message'=> $result->data), true );
    	    else
    	        echo json_encode( array('status' => false, 'message'=> $result->response), true );
        }
        else
            echo json_encode( array('status' => false, 'message'=> $result ), true );
    }
    
    /**
     * 
     */
    function getTaskList()
    {
        $searchData = $this->input->post();
        $jobListArr = getRequestedDataFromURL( 'jobs/my/' );
        $searchData = convertStringToArray($searchData);
        generateHTMLTaskList( $jobListArr, $searchData );
    }
    
    /**
     * https://bend.wowtasks.com/jobs/mybids/
     */
    function getMyBidsList()
    {
        $searchData = $this->input->post();
        $bidsListArr = getRequestedDataFromURL('jobs/mybids/');
        $searchData = convertStringToArray($searchData);
        generateHTMLBidsList( $bidsListArr, $searchData );
    }
    
    /**
     * 
     */
    function resetAllNotification()
    {
        postDataSubmit(  getSysConfig('api_url')."notification/reset-counter" , "" );
    }
}