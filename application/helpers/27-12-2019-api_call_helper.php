<?php 
/**
 * check User Authentication
 */
function checkAuthentication()
{
    $url = "https://bend.wowtasks.com/login/";
    $username = "satkardeep@gmail.com";
    $password = "12345678";
    
    $data = array("username" => $username, "password" => $password);
    $data_string = json_encode($data);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, count($data));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'accept-encoding: gzip, deflate',
        'accept-language: en-US,en;q=0.8',
        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36',
        'accept: application/json',
        'server: nginx/1.16.1',
        'connection: keep-alive',
        'vary: Accept, Cookie',
        'allow: POST, OPTIONS',
        'Content-Length: ' . strlen($data_string))
        );
    
    $result = json_decode( curl_exec($ch) );
    curl_close($ch);
    
    $session = array();
    $session['first_name'] = $result->data->first_name;
    $session['last_name'] = $result->data->last_name;
    $session['email'] = $result->data->email;
    $session['token'] = $result->data->token;
    $session['id'] = $result->data->id;
    $session['address'] = $result->data->address;
    $session['city_name'] = $result->data->city_name;
    $session['city_latitude'] = $result->data->city_latitude;
    $session['city_longitude'] = $result->data->city_longitude;
    $session['country_name'] = $result->data->country_name;
    $session['mobile_no'] = $result->data->mobile_no;
    $session['image_url'] = $result->data->image_url;
    $session['gender'] = $result->data->gender;
    $session['regtype'] = $result->data->regtype;
    $session['date_of_birth'] = $result->data->date_of_birth;
    
    $CI =& get_instance();
    $CI->session->set_userdata( $session );
    
}

/**
 * @deprecated
 * @return mixed
 */
function getMyTaskList()
{}

/**
 * Post Data submited
 */
function postDataSubmit( $url, $data_string )
{
    $result = "";
    $CI =& get_instance();
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'accept-encoding: gzip, deflate',
        'accept-language: en-US,en;q=0.8',
        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36',
        'accept: application/json',
        'server: nginx/1.16.1',
        'connection: keep-alive',
        'vary: Accept, Cookie',
        'allow: POST, OPTIONS',
        'Authorization: token '.$CI->session->userdata('token'),
        'Content-Length: '.strlen($data_string) )
        );
    curl_setopt($ch, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch)
    
    $result = json_decode( curl_exec($ch) );
    
    if (curl_errno($ch))
        $result = curl_error($ch);
        
    curl_close($ch);
    
    return $result;
}

/**
 * 
 * @return mixed
 */
function getRequestedDataFromURL( $url )
{
    $url = "https://bend.wowtasks.com/".$url;
    
    $CI =& get_instance();
    $ch = curl_init();
    
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    
//     curl_setopt($ch,CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'accept-encoding: gzip, deflate',
            'accept-language: en-US,en;q=0.8',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36',
            'accept: application/json',
            'server: nginx/1.16.1',
            'connection: keep-alive',
            'vary: Accept, Cookie',
            'allow: POST, OPTIONS',
            'Authorization: token '.$CI->session->userdata('token')
        ) );
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $output=curl_exec($ch);
    
    curl_close($ch);
    return json_decode( $output );
}

/**
 * generator Html format 
 * @param $jobListArr
 */
function generateHTMLTaskList( $jobListArr, $search=array(), $isGoogleMap=false )
{
	$resultArr = array();
    if( $jobListArr->status == "true" )
    {
        foreach ( $jobListArr->data as $k=>$ar )
        { 
        	if($isGoogleMap)
        		generateFilterHTMLTaskListGoogleMap( $k, $ar, $search, $resultArr);
			else
        		generateFilterHTMLTaskList( $k, $ar, $search );
        }
	}
	else
	    noResultFound();
	
    if( $isGoogleMap )
    	echo json_encode( $resultArr, true );
}

/**
 * 
 */
function generateFilterHTMLTaskList( $k, $ar, $search=array() )
{
    $CI =& get_instance();
    
    $isContinue = false;
    if( !isEmptyArr( $search ) )//&& in_array( "sort_by" , $search ) !== false 
    {
        if( isset( $search['location'] ) && $ar->to_be_complete == $search['location'] )
        {
            $isContinue = true;
        }
        else if( isset( $search['min_badget'] ) && isset( $search['max_badget'] ) )
        {
            if( $ar->budget >= $search['min_budget'] || $search['max_budget'] <= $ar->budget)
                $isContinue = true;
        }
        if( isset( $search['job_budget_type'] ) && ( $ar->job_budget_type == $search['job_budget_type'] || $search['job_budget_type'] == "ALL" ) )
        {
            $isContinue = true;
        }
    }
    else
        $isContinue = true;
        
    if( $isContinue )
    {
        ?><li class="<?php echo ( $k == 0 ) ? 'active' : '';?>">
    		<a data-toggle="pill" href="#task-id-<?php echo $CI->taskType.'-'.$ar->_id;?>" class="mob_opn-sidepanel nav-link" onclick="getJobDetails('<?php echo $ar->_id;?>' );">
    			<div class="forhr">
    				<div class="row">
    					<div class="col-md-2 pR0 text-center col-xs-2 mB5">
    						<div class="defaultJob  user-img"><img src="<?php echo $ar->posted_by->profile_image ?>"></div>
    					</div>
    					<div class="col-md-7 pL0 col-xs-7 pL15-xs">
    					   <h4 class="border-bottom pb-2"><?php echo $ar->job_title;?></h4>
    						<p class="mB5">
    							<span><i class="fas fa-map-marker mR10"></i> <?php echo ( isset( $ar->location_data->city_name ) ) ? $ar->location_data->city_name : '';?> </span>	            
    						</p>
    						<p class="mB5"><span><i class="far fa-calendar mR10"></i> <span>Due Date : </span> <?php echo $ar->due_date_userfriendly;?> </span></p>
    						<p class="mB5 clearfix">
    							<span class="pull-left"> <i class="fas fa-bullhorn mR10"></i>Offers :  <?php echo $ar->job_bids;?></span>
    							<span class="pull-right"> <i class="fas fa-users mR10"></i>Team Of :  <?php echo $ar->no_person_required;?></span>
    						</p>
    					</div>
    					<div class="col-md-3 text-center col-xs-3 pL0-xs pl-lg-0 pl-xl-3">
    						<p class="price">$ <?php echo $ar->total_budget;?></p> 
    						<span class="label label-danger"><?php echo $ar->job_status;?></span> 
    					</div>    
    				</div>
    			</div>	
    		</a>
    	</li> <?php
    }
//     else
//         noResultFound();
}

/**
 * 
 */
function generateFilterHTMLTaskListGoogleMap( $k, $ar, $search=array(), &$resultArr = array())
{
// 	$isContinue = false;
// 	if( !isEmptyArr( $search ) )//&& in_array( "sort_by" , $search ) !== false
// 	{
// 		if( isset( $search['location'] ) && $ar->to_be_complete == $search['location'] )
// 		{
// 			$isContinue = true;
// 		}
// 		else if( isset( $search['min_badget'] ) && isset( $search['max_badget'] ) )
// 		{
// 			if( $ar->budget >= $search['min_budget'] || $search['max_budget'] <= $ar->budget)
// 				$isContinue = true;
// 		}
// 		if( isset( $search['job_budget_type'] ) && ( $ar->job_budget_type == $search['job_budget_type'] || $search['job_budget_type'] == "ALL" ) )
// 		{
// 			$isContinue = true;
// 		}
// 	}
// 	else
// 		$isContinue = true;
		
	if( isset( $ar->location_data->latitude) )
	{
		$result = array();
		$result[] = $ar->job_title;
		$result[] = $ar->location_data->latitude;
		$result[] = $ar->location_data->longitude;
		$result[] = $k;
		
		$resultArr[] = $result;
	}
}

/**
 * generator Html format
 * @param $jobListArr
 */
function generateHTMLBidsList( $jobListArr, $search=array() )
{
    if( $jobListArr->status == "true" )
    {
        foreach ( $jobListArr->data as $k=>$ar )
            generateFilterHTMLTaskList( $k, $ar->job_whole_data, $search );
    }
    else
        noResultFound();

}

/**
 *
 */
function generateHTMLJobDetails( $jobListArr, $job_id, $myOfferArr=array(), $myBidsListArr=array(), $id, $myContractsListArr=array() )
{
    $CI =& get_instance();
    $toggle_id = $CI->taskType.'-'.$job_id;
    if( $jobListArr->status == true )
    {
        $chatId = "";
        $jobDetails = $jobListArr->data;
        
        if( !in_array( $job_id , $myOfferArr ) )
        { ?>
        	<div class="dropdown job-option-drp">
        		<a class="text-dark" href="#" data-toggle="dropdown"><span class="fa fa-ellipsis-v"></span></a>
        		<ul class="dropdown-menu dropdown-menu-right ">
        			<?php if( !isEmptyArr( $jobDetails->contracts ) )
        			{ ?>
						<li><a href="#" class="text-dark" data-toggle="modal" data-target="#cancel-task" onclick="openCancelTaskModal('<?php echo $jobDetails->contracts[0]->_id;?>')"><i class="fa fa-trash color-theam"></i> Cancel this Task</a></li>
						<li><a href="#" class="text-dark" data-toggle="modal" data-target="#additional-payment" onclick="openAdditionalPaymentModal('<?php echo $jobDetails->contracts[0]->_id;?>', <?php echo $jobDetails->total_budget;?>)"><i class="fa fa-copy color-theam"></i> Request additional Payment</a></li>
					<?php }?>
					<li><a href="#" class="text-dark" data-toggle="modal" data-target="#issue" onclick="openIssueModal('<?php echo $job_id;?>')"><i class="fa fa-info color-theam"></i> Report Issue</a></li>
        	   </ul>
        	</div>
    	<?php }?>
    	
        <div id="task-id-<?php echo $toggle_id;?>" class="tab-pane fade in active">
			<ul class="nav nav-tabs my-job_tab mobile_fix-tab">
				<li class="nav-item active"><a class="nav-link" data-toggle="tab" href="#post-task-<?php echo $toggle_id;?>">Task Info</a></li>
				<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#post-timeline-<?php echo $toggle_id;?>">Timeline <i class="fa fa-circle unread-noti"></i></a></li>		  
				<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#post-offer-<?php echo $toggle_id;?>">Chat <i class="fa fa-circle unread-noti"></i></a></li>		  
			</ul>
			<div class="tab-content">
				<div id="post-task-<?php echo $toggle_id;?>" class="tab-pane fade position-relative active in">
					<div class="job-status text-capitalize mt-4">
						<span class="badge <?php echo ( $jobDetails->job_status == "OPEN" ) ? 'active' : '';?>">Open</span>
						<span class="badge <?php echo ( $jobDetails->job_status == "ASSIGNED" ) ? 'active' : '';?>">Assigned</span>
						<span class="badge <?php echo ( $jobDetails->job_status == "COMPLETED" ) ? 'active' : '';?>">Completed</span>
						<span class="badge <?php echo ( $jobDetails->job_status == "EXPIRED" ) ? 'active' : '';?>">Expired</span>
					</div>
					<div class="clearfix">
						<h3 class="job-title mt-5 col-xs-9 col-sm-12"> <?php echo $jobDetails->job_title;?> </h3>
						<div class="col-xs-3 pr-0 col-sm-12 position-static">
							<!--	price tag -->
							<div class="text-right w-100 clearfix">
								<div class="task-budget">
									<div class="price-tag">$<?php echo $jobDetails->total_budget;?></div>
									<?php if( !in_array( $job_id , $myOfferArr ) && $jobDetails->job_status == "OPEN" && !in_array( $job_id , $myBidsListArr ) )
									{ ?>
    									<a href="#" class="btn btn-block btn-upload mt-2 btn-<?php echo $job_id;?>" data-toggle="modal"  data-target="#makeOffer" onclick="openMakeOfferModal('<?php echo $job_id;?>');">Make an Offer</a>
    									<a href="#" class="btn btn-block btn-upload mt-2 btn-<?php echo $job_id;?>" data-toggle="modal" data-target="#verifyBidder">bidder</a>
									<?php }?>
								</div>	
							</div>
						</div>
					</div>
					<div class="mt-4">
						<h4 class="my-0"><strong>Detail</strong></h4>
						<p class="text-muted detail-txt"><?php echo $jobDetails->job_description;?> </p>												
					</div>
					<!--		attachment	 -->
					<?php 
					if( !isEmptyArr( $jobDetails->job_images ) )
					{
					   ?>  			
    					<div class="mt-5">
    						<h4 class="my-0"><strong>Attachment</strong></h4>
    						<div class="attach-imgs">
								<?php 
								foreach ( $jobDetails->job_images as $var )
							    {
							        $ext = explode( "." , $var );
							        $ext = end( $ext );
							        ?>
    							    <a href="<?php echo $var?>" target="_blank">
    							    	<img class="img-responsive" src="<?php echo base_url('images/'.$ext.'-64X64.png')?>" />
							    	</a>
    							    <?php
                                }?>
    						</div>
    					</div>
					<?php }?>
				</div>
				<div id="post-timeline-<?php echo $toggle_id;?>" class="tab-pane fade position-relative">
    				<?php 
    				if( in_array( $job_id , $myOfferArr ) )
    				{
				        echo '<p class="text-center mt-5">You have '.$jobDetails->job_bids.' bids avilable for this job</p>';
    				}
    				
				    if( isset( $jobDetails->all_job_bids ) && !empty( $jobDetails->all_job_bids ) )
				    {
				        
				        foreach ( $jobDetails->all_job_bids as $tl )
				        {
				            if( isset( $tl->chat_group_details->_id ) )
				            {
				                $chatId = $tl->chat_group_details->_id."/";
				            }
				            
				            if( in_array( $job_id , $myOfferArr ) )
				            {
				                ?>
				                <div class="offer-list offer-mob clearfix d-block">	
                					<div class="clearfix">
                    					<div class="col-sm-2 pr-2 col-xs-3">
                    						<div class="user-img">
                    							<img class="img-responsive" src="<?php echo $tl->bid_by->profile_image;?>" />								
                    						</div>
                    						<span class="profile-rating"><?php echo $tl->bid_by->user_rating;?><i class="fa fa-star pl-2"></i></span>
                    					</div>
                    					<div class=" col-sm-4 mb-4 mt-0 pr-2 col-xs-9">
                    						<h5 class="mb-0"><strong><?php echo $tl->bid_by->first_name." ".$tl->bid_by->last_name?></strong></h5>							
                    						<div class="small mt-2">
                    							<span><i class="fa fa-map-marker color-theam pr-2"></i> <?php echo $tl->bid_by->location->city_name.", ".$tl->bid_by->location->country_name;?> </span><br>
                    						</div>
                    					</div>
                    					<div class="pr-2 col-sm-3 col-xs-6">
                    						<h5 class="mb-0"><strong>CAD <?php echo $tl->bid_amount;?></strong></h5>							
                    						<div class="small mt-2">
                    							<span>Bid Price</span><br>								
                    						</div>
                    					</div>
                    					<?php 
                    					if( $tl->bid_status == "PENDING" )
                    					{
                    					    ?>
                    					    <div class="px-2 col-sm-3 col-xs-6 mt-3 text-right">
                        						<a class="btn btn-upload" data-toggle="modal" data-target="#offerAccept">Accept</a>
                        					</div>
                    					    <?php 
                    					}
                    					else
                    					{
                    					    ?>
                    					    <div class="px-2 col-sm-3 col-xs-6 mt-3 text-right">
                        						<a class="btn btn-upload">Assigned</a>
                        					</div>
                    					    <?php 
                    					}
                    					?>
                					</div>	
                					<div class="notification clearfix text-center noti-msg mb-0">
                						 <?php echo str_ireplace( "+" , " ", $tl->bid_messege );?>					
                					</div>
                				</div>
				                <?php 
				            }
				            else if( $tl->bid_by->_id == $id )
				            {
    				            ?>
        				        <div class="offer-list offer-mob clearfix d-block">	
        				        	<div class="notification clearfix">
                						<div class="pull-left">
                							<i class="fa fa-info-circle color-theam"></i> Your Offer Is Submitted
                						</div>	
                						<div class="pull-right"> <?php echo formatDate( 'd M, Y', $tl->posted_date_time );?> </div>
                					</div>
                					
                					<div class="col-md-6 pull-none mx-auto mt-5">
                						<table class="table">
                							<tbody>
                    							<tr> <td>Offer value</td> <td class="text-right">$<?php echo $tl->bid_amount ;?></td> </tr>
                    							<tr> <td>Service Fee</td> <td class="text-right">(-) $<?php echo $tl->job_work_fee ;?></td> </tr>
                    							<tr> <td>GST / HST</td> <td class="text-right">(-) $<?php echo $tl->gst_amount ;?></td> </tr>
                    							<tr> <th class="color-theam"><strong>You receive</strong></th> <th class="text-right color-theam">$<?php echo $tl->bidder_will_get ;?></th> </tr>						
                    						</tbody>
                						</table>						
                					</div>
                					<div class="notification clearfix text-center noti-msg mb-3"> <?php echo str_ireplace( "+" , " ", $tl->bid_messege );?> </div>
									<div class="text-right">
										<a href="#" class="btn btn-upload" data-toggle="modal" data-target="#resubmitOffer" onclick="openReSubmitOfferModal('<?php echo $tl->_id; ?>', <?php echo $tl->bid_amount;?>)">Re-submit Offer</a>
									</div>
									<hr>
                				</div>
                				<?php
				            }
				        }
				    }
				    
				    if( !isEmptyArr( $jobDetails->job_timeline  ) )
				    {
				        foreach ( $jobDetails->job_timeline as $tl )
				        {
				            if( isset( $tl->additional_content->comments ) && !empty($tl->additional_content->comments) ) { ?>
    							<div class="notification clearfix">
                					<div class="pull-left">
                						<i class="fa fa-info pr-2"></i> <?php echo $tl->timeline_text;?>
                					</div>	
                					<div class="pull-right"><?php echo $tl->submitted_date_time_user_friendly;?></div>
                				</div>			
                				<div class="notification clearfix text-center noti-msg">
                					<?php echo $tl->additional_content->comments;?>
            					</div>
            					<hr>
							<?php }
				        }
				    }	    
    				?>
    															
    				<div class="text-center position-relative hide">
    					<h4>Rate the Task Owner to Receive Funds</h4>
    
    					<div class="rating text-center mx-auto pull-none">				
    						<input type="radio" id="star5" name="rating" value="5" /><label class = "full" for="star5" title="Awesome - 5 stars"></label>
    						<input type="radio" id="star4half" name="rating" value="4 and a half" /><label class="half" for="star4half" title="Pretty good - 4.5 stars"></label>
    						<input type="radio" id="star4" name="rating" value="4" /><label class = "full" for="star4" title="Pretty good - 4 stars"></label>
    						<input type="radio" id="star3half" name="rating" value="3 and a half" /><label class="half" for="star3half" title="Meh - 3.5 stars"></label>
    						<input type="radio" id="star3" name="rating" value="3" /><label class = "full" for="star3" title="Meh - 3 stars"></label>
    						<input type="radio" id="star2half" name="rating" value="2 and a half" /><label class="half" for="star2half" title="Kinda bad - 2.5 stars"></label>
    						<input type="radio" id="star2" name="rating" value="2" /><label class = "full" for="star2" title="Kinda bad - 2 stars"></label>
    						<input type="radio" id="star1half" name="rating" value="1 and a half" /><label class="half" for="star1half" title="Meh - 1.5 stars"></label>
    						<input type="radio" id="star1" name="rating" value="1" /><label class = "full" for="star1" title="Sucks big time - 1 star"></label>
    						<input type="radio" id="starhalf" name="rating" value="half" /><label class="half" for="starhalf" title="Sucks big time - 0.5 stars"></label>
    					</div>	
    				</div>
    				<div class="notification clearfix mt-80 hide">
    					<div class="pull-left">
    						<i class="fa fa-info pr-2"></i> You have mark the contract as completed
    					</div>	
    					<div class="pull-right"> 
    						 23 Nov, 2019
    					</div>
    				</div>	
    				<div class="text-center mt-5 hide">
    					<a class="btn mb-5 btn-complete" href="#" data-toggle="modal" data-target="#task-complete">Completed </a>
    				</div>		
    				<div class="text-center mt-5 hide">
    					<a class="btn btn-upload mb-5" href="#" data-toggle="modal" data-target="#task-complete">Mark Task Completed </a>
    				</div>	
    			</div>
    			<div id="post-offer-<?php echo $toggle_id;?>" class="tab-pane fade in">
					<div class="chat-section mt-5 px-3">
						<?php
    					$chatArr = array();
    					if( !empty( $chatId ) )
    					{
    					    $chatArr = getTaskChatHistoryData( $chatId );
    					    if( $chatArr->status == "true" )
    					    {
    					        ?>
    					        <h4 class=""><strong>Question (<?php echo $chatArr->total_messege;?>)</strong></h4>
								<div class="chat-time"><span>Yesterday </span></div>
    					        <?php 
    					        foreach ( $chatArr->data as $var )
    					        {
    					            ?>
    					            <div class="chat-box clearfix">
    					            	<?php 
    					            	if( $var->messege_belong == "OTHER" )
    					            	{
        					            	?>
                							<div class="postByJob my-3 clearfix msg-box">
                								<div class="col-2">
                									<div class="user-img ">
                										<img class="img-responsive" src="<?php echo base_url('images/job-dummy.png')?>" />
                									</div>
                								</div>
                								<div class="mb-4 mt-0">
                									<h5 class="mb-0"><strong><?php echo $var->sender->first_name." ".$var->sender->last_name;?></strong> ( task poster )</h5>
                									<p class="small text-muted"><?php echo date( 'h:m A', $var->timestamp );?></p>
                									<p><?php echo $var->messege_text;?></p>
                									<a href="#" class="text-danger">reply</a>
                								</div>
                							</div>
            							<?php }
            							else 
            							{?>
                							<div class="postByJob my-3 ml-5 clearfix reply text-right msg-box">							
                								<div class="mb-4 mt-0 col-sm-81 cht-text mr-3">
                									<h5 class="mb-0"><strong><?php echo $var->sender->first_name." ".$var->sender->last_name;?></strong> </h5>
                									<p class="small text-muted"><?php echo date( 'h:m A', $var->timestamp );?></p>
                									<p class="text-left"><?php echo $var->messege_text;?></p>								
                								</div>
                								<div class="col-2 pull-right">
                									<div class="user-img">
                										<img class="img-responsive" src="<?php echo base_url('images/job-dummy.png')?>" />
                									</div>
                								</div>
                							</div>
            							<?php }?>
            						</div>
    					            <?php 
    					        }
    					    } 
    					}
    					?>
    					<form onsubmit="return false;" id="chat_send_message">
					    	<input type="text" name="group_id" value="<?php echo $chatId;?>" id="group_id">
    					    <div class="form-group px-3 mt-4">
        						<textarea placeholder="Ask A Question" name="message" id="message" class="message form-control" rows="3"></textarea>
        					</div>
        					<div class="clearfix">
        						<div class="position-relative col-xs-6">
        							<div class="upload-btn-wrapper ">
        								<a href="#" type="button" class="btn btn-link text-dark"><i class="fa fa-paperclip"></i> Add Attachment</a>
        							 	<input type="file" name="myfile1" />
        							</div>
        						</div>	
        						<div class="col-xs-6 text-right">
        							<a href="#" id="submit_chat" class="btn btn-upload">Submit</a>
        						</div>
        					</div>
    					</form>
					</div>
				</div>
    		</div>
		</div>
    <?php }
    else
    {
        ?>
        <div id="task-id-<?php echo $toggle_id;?>" class="tab-pane fade in">
    		<ul class="nav nav-tabs my-job_tab mobile_fix-tab">
    			<li class=" nav-item active"><a class="nav-link" data-toggle="tab" href="#post-task-0">Task Info</a></li>
    			<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#post-timeline-0">Timeline <i class="fa fa-circle unread-noti"></i></a></li>		  
    			<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#post-offer-0">Chat <i class="fa fa-circle unread-noti"></i></a></li>		  
    		</ul>
    		<div class="tab-content">
    			<div id="post-task-0" class="tab-pane fade position-relative active text-center">No Task Data Available</div>
    			<div id="post-timeline-0" class="tab-pane fade position-relative text-center">No Timeline Available</div>
    			<div id="post-offer-0" class="tab-pane fade in text-center">No Offer Available</div>
			</div>
		</div>
        <?php 
    }
}

/**
 * Request URL
 * https://bend.wowtasks.com/chat/view/{GROUP_ID as _id}
 */
function getTaskChatHistoryData( $group_id='' )
{
    return getRequestedDataFromURL( 'chat/view/'.$group_id );
}

/**
 * get job details pass by job id 
 */
function getJobDetails( $job_id='/' )
{
    return getRequestedDataFromURL('jobs/view/'.$job_id );
}

/**
 * https://bend.wowtasks.com/notification/list/
 */
function getAllNotification()
{
    return getRequestedDataFromURL( 'notification/list/'  );
}

/**
 * 
 */
function getCityList()
{
    return getRequestedDataFromURL( 'citys/list/all' );    
}

/**
 *
 */
function getSkillList()
{
    return getRequestedDataFromURL( 'skills/' );
}

/**
 * 
 */
function noResultFound()
{
    ?>
    <div class="bg-white text-center position-relative py-5" >
		<div class="py-5 mt-5">
			<img class="img-responsive mb-4 mx-auto" src="<?php echo asset_url('images/no-resultfound.png');?>">
			<h3 class="mt-5"> No Result Fount </h3>
		</div>
	</div>
    <?php
}
/**
 * 
 */
function convertStringToArray( $strVar)
{
    $result = array();
    if( !isEmptyArr( $strVar) )
    {
        $expArr = explode('&', $strVar['data']);
        if( !isEmptyArr( $expArr ) )
        {
            foreach ( $expArr as $var )
            {
                $kv = explode( "=" , $var);
                $result[$kv[0]] = $kv[1];
            }
        }
    }
    
    return $result;
}

/**
 * PHP Time Ago functionality is used to display time in the different format.
 * It takes the timestamp as an input and subtracts it with the current timestamp. 
 * Then it compares the remaining date with a predefined set of rules which determine the month, day, minute and even year. 
 * Then it calculates time difference by using a predefined set of rules which determine the second, minute, hour, day, month and year.
 */
function get_time_ago( $time )
{
    $time_difference = time() - $time;
    
    if( $time_difference < 1 ) { return 'less than 1 second ago'; }
    $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
        30 * 24 * 60 * 60       =>  'month',
        24 * 60 * 60            =>  'day',
        60 * 60                 =>  'hour',
        60                      =>  'minute',
        1                       =>  'second'
    );
    
    foreach( $condition as $secs => $str )
    {
        $d = $time_difference / $secs;
        
        if( $d >= 1 )
        {
            $t = round( $d );
            return $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
        }
    }
}