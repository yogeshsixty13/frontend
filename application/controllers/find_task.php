<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class find_task extends CI_Controller 
{
    var $taskType = "findTask";
    
	//parent constructor will load model inside it
	function __construct()
	{
		ini_set('display_errors', 1);
		
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
	    $data['pageName'] = 'find_task';
		$this->load->view('site-layout',$data);
	}
	
	function getTaskList( $p=0 )
	{
	    $page = "";
	    if( $p!=0 )
	        $page = "?page=".$p;
	   
	    $searchData = $this->input->post();
	    $jobListArr = getRequestedDataFromURL( 'jobs/all/list/'.$page );
	    $searchData = convertStringToArray($searchData);
	    generateHTMLTaskList( $jobListArr, $searchData );
	}
	
	function getSortByTaskSelectedList( $p=0 )
	{
	    $page = "";
	    if( $p!=0 )
	        $page = "?page=".$p;
	    
	    $searchData = $this->input->post();
	    $jobListArr = getRequestedDataFromURL( 'jobs/mybids/selected/'.$page );
	    $searchData = convertStringToArray($searchData);
	    generateHTMLTaskList( $jobListArr, $searchData );
	}
	
	function getSortByTaskPendingList( $p=0 )
	{
	    $page = "";
	    if( $p!=0 )
	        $page = "?page=".$p;
	        
	    $searchData = $this->input->post();
	    $jobListArr = getRequestedDataFromURL( 'jobs/mybids/pending/'.$page );
	    $searchData = convertStringToArray($searchData);
	    generateHTMLTaskList( $jobListArr, $searchData );
	}
	
	function getMyTaskListGoogleMap()
	{
		$searchData = $this->input->post();
		$jobListArr = getRequestedDataFromURL( 'jobs/all/list/' );
		$searchData = convertStringToArray($searchData);
		generateHTMLTaskList( $jobListArr, $searchData, true );
	}
	
	/**
	 * https://bend.wowtasks.com/jobs/mybids/
	 */
	function getMyBidsList()
	{
	    $searchData = $this->input->post();
	    $isOfferWishlist = (int)$this->input->get('isOfferWishlist');
	    
	    if( $isOfferWishlist )
	        $this->taskType = "myOfferWishList";
	        
	    $bidsListArr = getRequestedDataFromURL('jobs/mybids/');
	    $searchData = convertStringToArray($searchData);
	    generateHTMLBidsList( $bidsListArr, $searchData );
	}
	
	/**
	 * https://bend.wowtasks.com/jobs/mybids/
	 */
	function getJobDetails()
	{
	    $data = $this->input->post();
	    $id = $this->session->userdata('id');
	    
	    $jobDetailArr = getRequestedDataFromURL('jobs/view/'.$data['job_id']."/");//get selected job details
	    
	    //get all my offer lists
	    $myOfferListArr = getRequestedDataFromURL('jobs/my/');
	    $myOfferArr = [];
	    if( $myOfferListArr->status == true )
	    {
	        if( !isEmptyArr( $myOfferListArr->data ) )
	        {
	            foreach ( $myOfferListArr->data as $ar )
	            {
	                $myOfferArr[] = $ar->_id;
	            }
	        }
	    }
	    
	    //get all my bids lists
	    $myBidsArr = getRequestedDataFromURL('jobs/mybids/');
	    $myBidsListArr = [];
	    if( $myBidsArr->status == true )
	    {
	        if( !isEmptyArr( $myBidsArr->data ) )
	        {
	            foreach ( $myBidsArr->data as $ar )
	            {
	                $myBidsListArr[] = $ar->job_id;
	            }
	        }
	    }
	    
	    //get all my bids lists
	    $myContractsArr = getRequestedDataFromURL('jobs/contracts/all/');
	    $myContractsListArr = [];
	    if( $myContractsArr->status == true )
	    {
	        if( !isEmptyArr( $myContractsArr->data ) )
	        {
	            foreach ( $myContractsArr->data as $ar )
	            {
	                $myContractsListArr[] = $ar->_id;
	            }
	        }
	    }
	    
	    generateHTMLJobDetails( $jobDetailArr, $data['job_id'], $myOfferArr, $myBidsListArr, $id, $myContractsListArr, $data['active'] );
	}
	
	/**
	 * https://bend.wowtasks.com/jobs/bid-on-job/
	 */
	function submitBidOnJob()
	{
	    $this->convertStringToPostData( getSysConfig('api_url')."jobs/bid-on-job/" );
	}
	
	/**
	 * https://bend.wowtasks.com/jobs/bid/update/
	 */
	function reSubmitBidOnJob()
	{
	    $this->convertStringToPostData( getSysConfig('api_url')."jobs/bid/update/" );
	 }
	
	/**
	 * https://bend.wowtasks.com/chat/send/messege/
	 */
	function submitChatMessage()
	{
	    $this->convertStringToPostData( getSysConfig('api_url')."chat/send/messege/" );
	 }
	
	/**
	 * https://bend.wowtasks.com/jobs/report-job/
	 */
	function submitReportIssue()
	{
	    $this->convertStringToPostData( getSysConfig('api_url')."jobs/report-job/" );
	}
	
	/**
	 * https://bend.wowtasks.com/jobs/poster/cancel/contract/
	 */
	function submitCancelTask()
	{
	    $this->convertStringToPostData( getSysConfig('api_url')."poster/cancel/contract/" );
	}
	
	/**
	 * https://bend.wowtasks.com/jobs/bidder/requested/to/increase/payments/
	 */
	function submitAdditionalPayment()
	{
	    $this->convertStringToPostData( getSysConfig('api_url')."jobs/bidder/requested/to/increase/payments/" );
	}
	
	/**
	 * convert string to array and submit post data function
	 */
	function convertStringToPostData( $url )
	{
	    $data = $this->input->post();
	    $dataArr = explode( "&", $data['data'] );
	    
	    $postData = array();
	    foreach( $dataArr as $ar)
	    {
	        $kvArr = explode("=",$ar);
	        $postData[$kvArr[0]] = $kvArr[1];
	    }
	    
	    $data_string = json_encode($postData);
	    $result = postDataSubmit( $url, $data_string );
	    
	    if( $result->status == true )
	        echo json_encode( array('status' => true, 'message'=> ( $result->response ) ? $result->response : $result->data ), true );
        else
            echo json_encode( array('status' => false, 'message'=> ( $result->response ) ? $result->response : $result->data ), true );
	}
	
	/**
	 * 
	 */
	function getAllJobDetails( $job_id )
	{
	    $id = $this->session->userdata('id');
	    $jobDetailArr = getRequestedDataFromURL('jobs/view/'.$job_id."/");
	    
	    $resultArr = [];
	    if( isset( $jobDetailArr->data->all_job_bids ) && !empty( $jobDetailArr->data->all_job_bids ) )
	    {
	        
	        foreach ( $jobDetailArr->data->all_job_bids as $tl )
	        {
	            if( $tl->bid_by->_id == $id )
	            {
	                $resultArr['id'] = $tl->_id;
	                $resultArr['amt'] = $tl->bid_amount;
	            }
	        }
	    }
	    
	    echo json_encode( $resultArr, true);
	}
	
	/**
	 * test function
	 */
	function display()
	{
// 	    $jobDetailsArr = getRequestedDataFromURL('jobs/all/list/?page=2');
// 	    $jobDetailsArr = getJobDetails('36dcc88b-a267-4688-baf3-dfdff8a06ad3/');
	    $jobDetailsArr = getRequestedDataFromURL( 'chat/view/2d1cef47-1744-4214-87a5-42343174c82d/?page=2' );
	    pr($jobDetailsArr);
	}
}