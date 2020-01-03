<?php
    $data['menu'] = "payment-record-header";
    $this->load->view('elements/header-menu', $data );
?>
<!--	begin:: tab content-->
<div class="container mt-100 mt-65 mt-md-5 pt-md-5 mT130-xs">
	<!-- main tabs -->
	<?php if($this->session->flashdata('success')){ ?>
        <div class="alert alert-success text-center">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
            <p><?php echo $this->session->flashdata('success'); ?></p>
        </div>
    <?php } ?>
	<div class="clearfix pay-record mt-5">		
		<ul class="nav nav-pills job-bid__tab text-center col-md-5 pull-none mx-auto text-center mt-lg-0 mb-lg-5 pb-3">
			<li class="active"><a data-toggle="pill" href="#post-job">Received Payments</a></li>
			<li><a data-toggle="pill" href="#wishlist">Made Payments</a></li>		
		</ul>	
	</div>
    <div class="tab-content">   
        <div id="post-job" class="tab-pane fade active in">
			<div class="row">
            	<div class="col-md-5 forscrl pl-0 pr-xl-2 pr-lg-0 pull-none mx-auto">		
            		<div class="row findjob">    
            			<div class="col-md-12 details-show">
            				<ul class="nav nav-pills findjob-pills" id="my-job-list" role="tablist">
            					<?php 
            					if( !isEmptyArr( $listArr->data ) )
            					{
                                    foreach( $listArr->data as $ar ) 
                                    { ?>
                    					<li class="active">
                    						<a  class="mob_opn-sidepanel nav-link" data-toggle="pill" href="#rcived-payment1">
                    							<div class="forhr my-job">
                    								<div class="row">
                    									<div class="col-md-2 pR0 text-center col-xs-2 mB5">
                    										<div class="defaultJob  user-img"><img src="<?php echo load_image($ar->poster_img) ?>"></div>
                    									</div>
                    									<div class="col-md-7 col-xs-7 pL15-xs">
                    									   <h4 class="border-bottom pb-2"><?php echo $ar->job_title;?></h4>
                    										<p class="mB5">
                    											<span><i class="fas fa-user mr-1"></i> <?php echo $ar->payment_by_first_name." ".$ar->payment_by_last_name;?></span>	           
                    										</p>
                    										<p class="mB5"><span><i class="far fa-calendar mr-1"></i> <span>Payment Date : </span><?php $date = strtotime($ar->created_date); echo date('Y-m-d', $date);  ?> </span></p>
                    										<p class="mB5"><span class=""> <i class="fas fa-envelope mr-1"></i><?php echo $this->session->userdata('email');?></span></p>
                    										<p class="mB5"><span class=""><span> Intrac e-Transfer Security Answer :</span><?php echo $ar->reference_answer; ?></span></p>
                    									</div>
                    									<div class="col-md-3 text-center col-xs-3 pL0-xs pl-lg-0 pl-xl-3">
                    										<p class="price">$<?php echo $ar->bidder_will_get;?></p> 
                    										<span class="label label-danger">recived</span> 
                    									</div>    
                    								</div>	
                    
                    							</div>
                    						</a>
                    					</li>
            					<?php }
            					}
            				    else
            				    {?>
                    				<li class="">
                    					<div class="forhr my-job">
                    						<div class="row">
                    							<p style="font-size: 15px; text-align: center;">No record Found</p> 
                    						</div>	
                    					</div>
                    				</li>	
            					<?php } ?>
            				</ul>
            			</div>     
            		 </div> 
            	</div>
			</div>
        </div>
		<div id="wishlist" class="tab-pane fade">
			<div class="row">
				<div class="col-md-5 forscrl pl-0 pr-xl-2 pr-lg-0  pull-none mx-auto">	
					<div class="row findjob">    
						<div class="col-md-12 details-show">
    						<ul class="nav nav-pills findjob-pills" id="my-job-list1" role="tablist">
								<?php 
                                if( !isEmptyArr( $madePaymentArr->data ) )
                                {
    				                foreach( $madePaymentArr->data as $ar )
    				                { ?>
    				    				<li class="active">
    										<a  class="mob_opn-sidepanel nav-link" data-toggle="pill" href="#my-job1">
        										<div class="forhr my-job">
        											<div class="row">
        												<div class="col-md-2 pR0 text-center col-xs-2 mB5">
        													<div class="defaultJob user-img"><img src="<?php echo asset_url("images/job-dummy.png")?>"></div>
        												</div>
        												<div class="col-md-7 pL0 col-xs-7 pL15-xs">
        								   					<h4 class="border-bottom pb-2"><?php echo $ar->job_title?></h4>
        													<p class="mB5">
        														<span><i class="fas fa-user mt-1"></i> To :<?php echo $ar->bidder_details->first_name." ".$ar->bidder_details->last_name?></span>	            
        													</p>
        													<p class="mB5"><span><i class="far fa-calendar mr-1"></i> <span>Payment Date : </span> <?php $date = strtotime($ar->date_time); echo date('Y-m-d', $date)?></span></p>									
        												</div>
        												<div class="col-md-3 text-center col-xs-3 pL0-xs pl-lg-0 pl-xl-3">
        													<p class="price">$<?php echo $ar->total_payed_amount;?></p> 
        													<span class="label label-danger">Secured</span> 
        												</div>    
        											</div>			
        										</div>
    										</a>
    									</li>
    								<?php }
                                }
                                else
                                {?>
                    				<li class="">
                    					<div class="forhr my-job">
                    						<div class="row">
                    							<p style="font-size: 15px; text-align: center;">No record Found</p> 
                    						</div>	
                    					</div>
                    				</li>	
    							<?php } ?>
    						</ul>
    					</div>     
         			</div> 
    			</div>
        	</div>
        </div>   
	</div>
</div> 