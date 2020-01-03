<?php
    $data['menu'] = "find-task";
    $this->load->view('elements/header-menu', $data );
?>
<div class="container mT150 mT130-xs">
	<div class="tab-content  mt-4">   
    	<div id="post-job" class="tab-pane fade active in">
			<div class="row mt-md-4">
				<!--  Left Panel-->
				<div class="col-md-5 forscrl find-job">
					<div class="col-md-12 text-center preloader hide">
						<img class="img-fluid GIF" src="<?php echo asset_url('images/preloader.gif?v='.time())?>">
					</div>
					<div class="row findjob  mtx-0 ">    
                    	<div class="col-md-12 details-show">
                    		<ul class="nav nav-pills findjob-pills" id="find_job_list" role="tablist"> </ul>
                    	</div>     
                    </div>
				</div>
    
    			<!-- handel find-jobs-search-filter-menu -->
				<div class="col-md-7 mob-sidepanel position-relative forscrl find-job default-google-map">
					<div class="">
						<div class="tab-content" id="v_pills_myjob_map"><!-- v-pills-myjob -->
							<div id="map" class=""></div> 			
						</div>
						<div class="my-3 py-3 px-2  bake-div">
							<a href="#" class="back my-3 btn btn-upload w-100"><i class="fa fa-arrow-circle-left"></i> Back</a>
						</div>
					</div>
				</div>
				
				<!-- handel post-task-offer-wishlist -->
				<div class="col-md-7 mob-sidepanel position-relative forscrl m-job-height task-post-details hide">
					<div class="col-md-12 text-center task-post-details-preloader hide"	>
						<img class="img-fluid GIF" src="<?php echo asset_url('images/preloader.gif?v='.time())?>">
					</div>
					<div class="tab-content task-full-details" id="v_pills_find_task_information"> </div>
				    <div class="my-3 py-3 px-2  bake-div">
						<a href="#" class="back my-3 btn btn-upload w-100"><i class="fa fa-arrow-circle-left"></i> Back</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>