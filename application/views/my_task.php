<?php
$data['menu'] = "my-task";
$this->load->view('elements/header-menu', $data );
?>

<div class="container mT150 mT130-xs">
	<div class="tab-content mt-4">   
    	<div id="post-job" class="tab-pane fade active in">
			<div class="row mt-md-4">
				<!--  Left Panel-->
				<div class="col-md-5 forscrl find-job">
					<div class="col-md-12 text-center preloader hide">
						<img class="img-fluid GIF" src="<?php echo asset_url('images/preloader.gif?v='.time())?>">
					</div>
					<div class="row findjob  mtx-0 ">    
                    	<div class="col-md-12 details-show">
                    		<ul class="nav nav-pills findjob-pills" id="my_post_list" role="tablist"></ul>
                		</div>
            		</div>
				</div>
    
				<!-- handel post-task-offer-wishlist -->
				<div class="col-md-7 mob-sidepanel position-relative forscrl m-job-height post-task-offer-wishlist">
					<div class="col-md-12 text-center task-post-details-preloader hide">
						<img class="img-fluid GIF" src="<?php echo asset_url('images/preloader.gif?v='.time())?>">
					</div>
					<div class="tab-content task-full-details" id="v_pills_my_post_task_information"> </div>
				</div>
			</div>
		</div>
		
		<div id="wishlist" class="tab-pane fade in">
			<div class="row mt-md-4">
                <div class="col-md-5 forscrl m-job-height">	
                	<div class="col-md-12 text-center preloader hide">
						<img class="img-fluid GIF" src="<?php echo asset_url('images/preloader.gif?v='.time())?>">
					</div>
                    <div class="row findjob">    
                		<div class="col-md-12 details-show">
                			<ul class="nav nav-pills findjob-pills" id="my_offer_wish_list" role="tablist"> </ul>
                		</div>     
                     </div> 
                </div>
    
                <div class="col-md-7 mob-sidepanel position-relative forscrl m-job-height">
                	<div class="col-md-12 text-center task-post-details-preloader hide">
						<img class="img-fluid GIF" src="<?php echo asset_url('images/preloader.gif?v='.time())?>">
					</div>
                	<div class="tab-content task-full-details" id="v_pills_my_offer_whish_list"> </div>
					<div class="my-3 py-3 px-2  bake-div">
                		<a href="#" class="back my-3 btn btn-upload w-100"><i class="fa fa-arrow-circle-left"></i> Back</a>
					</div>
                </div>
    		</div>
    	</div>
	</div>
</div>