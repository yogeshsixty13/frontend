<div class="bg-success pB5 filter px-lg-0 px-md-5 "><!-- find-jobs-search-filter-menu -->
	<div class="container1 px-lg-5 px-3">
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<div class="row">
					<a class="btn btn-primary visible-xs visible-sm formob" role="button" id="opnFilter" href="#_"> Filters <i class="fas fa-chevron-down"></i> </a> 
					<a href="#" id="clsMobFilter"><i class="fa fa-times fa-2x"></i></a>
					
					<form id="task_filter_form">
				    	<div class="collapse fordesk" id="mobile">
				    		<div class="col-md-2 col-xs-12 pL0-xs col-sm-2 mt-3 pt-1">
				    			<div class="dropdown">
				    				<button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"> Sort By <span class="caret"></span> </button>
				    				<br>
				    				<p class="small text-white sort-by badge"></p>
				    				<ul class="dropdown-menu drp-filter sortBy" aria-labelledby="dropdownMenu1">
				    					<span class="pR20 d-block mb-3"><strong>Filter By Sort:</strong> </span>
				    					<div class="radio">
				    						<input type="radio" id="allType" class="cancel-resone" name="sort_by" value="All"> <label for="allType">All</label>
				    					</div>
				    					<div class="radio">
				    						<input type="radio" id="selected" class="cancel-resone" name="sort_by" value="Selected"> <label for="selected">Selected</label>
				    					</div>
				    					<div class="radio">
				    						<input type="radio" id="pending" class="cancel-resone" name="sort_by" value="Pending"> <label for="pending">Pending</label>
				    					</div>
				    				</ul>
				    			</div>
				    		</div>
				    
				    		<div class="col-md-7 col-xs-12 col-sm-9 mT10-xs mt-3 pt-1">
				    			<div class="col-md-3 col-xs-6 pL0-xs pL0 col-sm-2 forcity">
				    				<div class="dropdown">
				    					<button class="btn btn-success dropdown-toggle" type="button" id="dropdownCity" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"> Location <span class="caret"></span> </button>
				    					<p class="small pl-4 text-white city_name badge"></p>
				    					<ul class="dropdown-menu drp-filter" aria-labelledby="dropdownCity">
				    						<li>
				    							<div class="clearfix">
				    								<div class="radio col-xs-6">
				    									<input type="radio" id="locationOnline" class="cancel-resone locatio-rdo" value="ONLINE" name="location">
				    									<label for="locationOnline">Online</label>
				    								</div>
				    								<div class="radio col-xs-6 mt-3">
				    									<input type="radio" id="locationCity" class="cancel-resone locatio-rdo" value="City" name="location"> 
				    									<label for="locationCity">City</label>
				    								</div>
				    							</div>
				    							<div class="cityDisplay">
				    								<input type="text" list="locationTextBox" class="form-control searchCity mb-3" name="search_city" placeholder="Search City">
				    								<datalist id="locationTextBox">
                                    					<?php 
                                    					$cityArr = getCityList();
                                    					if( $cityArr->status == true )
                                    					{
                                    					    if( !isEmptyArr( $cityArr->data ) )
                                    					    {
                                    					        foreach ( $cityArr->data as $city )
                                    					        {
                                    					            echo '<option value="'.$city->city.'">'.$city->city.', '.$city->state_name.', '.$city->country_name.'</option>';
                                    					        }
                                    					    }
                                    					    else 
                                    					        echo '<option value="">No City Available</option>';
                                    					}
                                    					else
                                    					    echo '<option value="">Invalid token header.</option>';
                                    					?>
                                    				</datalist>
				    								<span class="pR20 hide-range">Distance range : </span> 
				    								<input id="map-distance" type="text" class="span2 map-distance py-3" name="map_distance" value="0" data-slider-min="10" data-slider-max="1000" data-slider-step="2" data-slider-value="[250,750]" />
				    							</div>
				    							<div class="clearfix mt-4">
				    								<a href="#" class="btn btn-upload pull-right mr-2 fcity-btn">Apply</a>
				    							</div>
				    						</li>
				    					</ul>
				    				</div>
				    			</div>
				    
				    			<div class="col-md-3 col-xs-6 pL0-xs pL0 col-sm-2">
				    				<div class="dropdown forbudget ">
				    					<button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"> Budget <span class="caret"></span> </button>
				    					<p class="small pl-4 text-white fbudget badge"></p>
				    					<div class="dropdown-menu drp-filter" aria-labelledby="dropdownMenu1">
				    						<div class="row">
				    							<div class="col-md-5 col-xs-5">
				    								<span>minimum</span> 
				    								<input type="text" name="min_budget" class="budget bmin" value="10">
				    							</div>
				    							<div class="col-md-2 col-xs-2 pT20">---</div>
				    							<div class="col-md-5 col-xs-5">
				    								<span>maximum</span> 
				    								<input type="text" name="max_budget" class="budget bmax" value="100">
				    							</div>
				    						</div>
				    						<div class="clearfix mt-4">
				    							<a href="#" class="btn btn-upload pull-right mr-2 budget-btn">Apply</a>
				    						</div>
				    					</div>
				    				</div>
				    			</div>
				    			<div class="col-md-3 col-xs-6 pL0-xs pL0 col-sm-2 mT5-xs">
				    				<div class="dropdown">
				    					<button class="btn btn-success dropdown-toggle" type="button" id="dropdowntaskType" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"> Budget Type <span class="caret"></span> </button>
				    					<p class="small pl-4 text-white job_type badge"></p>
				    					<input type="hidden" name="job_budget_type" value="" id="job_budget_type">
				    					<ul class="dropdown-menu drp-filter job-type" aria-labelledby="dropdownMenu1">
				    						<li><a href="#" class="px-0 job-budget-type" data-val="HOURLY">Hourly</a></li>
				    						<li><a href="#" class="px-0 job-budget-type" data-val="FIXED">Fixed</a></li>
				    						<li><a href="#" class="px-0 job-budget-type" data-val="ALL">All</a></li>
				    					</ul>
				    				</div>
				    			</div>
				    			<div class="col-md-3 col-xs-6 pL0-xs pL0 col-sm-2 clear ">
				    				<a href="#" class="mt-2 clearFilter"><i class="far fa-times-circle "></i> clear all filters</a>
				    			</div>
				    		</div>
				    	</div>
					</form>
					<div class="col-md-3 col-xs-7 col-sm-5 pull-right mT10 search searchmob">
						<form id="search_task_form">
				    		<input type="text" name="q" id="search_task" placeholder="Search a kind of task..."> 
				    		<a href="#"><i class="fas fa-search"></i></a>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="clearfix find-jobs-search-filter-menu">
   <div id="mobFilter">
		<div class="mobile-filter">
			<div class="clearfix">				
				<h4 class="pull-left my-0"><strong>Filter</strong></h4>
				<img class="img-responsive pull-right" src="images/close.png" alt="close" id="closeFilter" />
			</div>
			
			<div class="mt-5 mob-sortby">
				<span class="pR20 d-block Fheading"><strong>By sort :</strong><span class="small pl-4 filter task-price"></span> </span><br>			
				<div class="radio">		  
				   <input type="radio" id="allType1" class="cancel-resone" value="all" name="selectType" >
				   <label for="allType1">All</label>
				</div>

				<div class="radio">		  
				   <input type="radio" id="selected1" class="cancel-resone" value="selected" name="selectType" >
				   <label for="selected1">Selected</label>
				</div>

				<div class="radio">		  
				   <input type="radio" id="pending1" class="cancel-resone" value="pending" name="selectType" >
				   <label for="pending1">Pending</label>
				</div>		
			</div>
			
			<div class="mt-5 mob-sortby">
				
				<span class="pR20 d-block mb-3 Fheading"><strong>By Location:</strong>
<!--				 <span class="small pl-4 filter city_name">Chikago</span>-->
				 </span>
			<div class="clearfix">
				<div class="radio col-xs-6">
				  <input type="radio" id="locationOnlineMob" class="cancel-resone location-rdo" name="sortByCity" >
				  <label for="locationOnlineMob">Online</label>
				</div>

				<div class="radio col-xs-6 mt-3">
				   <input type="radio" id="locationCityMob" class="cancel-resone location-rdo" name="sortByCity" >
				   <label for="locationCityMob">City</label>
				</div>
			</div>
			<div class="cityDisplay">
				<input type="text"  id="locationTextBoxMob" class="form-control fcity mb-3" placeholder="Search City">
				<span class="pR20 hide-range">Distance range : </span>
				<input id="map-distanceMob" type="text" class="span2 map-distance py-3" data-slider-min="10" data-slider-max="1000" data-slider-step="2" data-slider-value="[250,750]"/> 
			</div>
					
		</div>
						
			<div class="mt-5">
				<span class="pR20 d-block mb-3 Fheading"><strong>By Budget:</strong>
<!--				<span class="small pl-4 filter">$135 - $145</span> -->
				</span>
				<div class="row">
					<div class="col-xs-6 ">
						<span>minimum</span>    
						<input type="text" class="budget form-control mobBmin" placeholder="min">    
					</div>					   
					<div class="col-xs-6">
						<span>maximum</span>    
						<input type="text" class="budget form-control mobBmax" placeholder="max">   
					</div>    
				</div>
			</div>
			
			<div class="mt-5">
				<span class="pR20 d-block mb-3 Fheading"><strong>By Budget Type:</strong> 
<!--				<span class="small pl-4 filter">Online Jobs</span>-->
				</span>
				<select class="form-control" id="ByTaskType">
					<option hidden="">Select Type</option>
					<option>Hourly</option>
					<option>Fixed</option>
				</select>
			</div>
			
			<div class="mt-5 pt-5">
				<a href="#" class="btn btn-upload w-100" id="applyFilter">Apply Filter</a>
			</div>
		</div>
	</div>
</div>