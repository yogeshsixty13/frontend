<div class="row findjob  mtx-0 ">    
	<div class="col-md-12 details-show">
	   <!--		begin:: left side job list -->
		
		<ul class="nav nav-pills findjob-pills" id="my_job_list" role="tablist">
			<?php 
			if( false && $jobListArr->status == "true" )
			{
			    foreach ( $jobListArr->data as $k=>$ar )
			    {
			        ?>
			        <li class="<?php echo ( $k == 0 ) ? 'active' : '';?>">
        				<a data-toggle="pill" href="#my-offer3" class="mob_opn-sidepanel nav-link">
        					<div class="forhr my-job">
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
        			</li>
			        <?php 
			    }
			}
			?>
		</ul>
	   <!--		end:: left side job list -->
	</div>     
</div>