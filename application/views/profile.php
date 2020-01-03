<?php
    $data['menu'] = "profile";
    $this->load->view('elements/header-menu', $data );
?>
		
		 
<div class="container mT150 mT130-xs">
	<div class="col-md-3 col-sm-4">
		<div class="profile-img">
			<img class="img-responsive" src="<?php echo $listArr->data->image_url;?>" alt="profile" />
		</div>
		
<!--		verification Details-->
		<div class="verification">
<!--			<h4><i class="fa fa-male mr-3 color-theam"></i> Verification</h4>-->
		<h5 class="mt-5 color-orange text-center"><strong>As task owner: </strong></h5>
			<div class="star-rate text-center">
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star-half-o"></i>
				<i class="fa fa-star-o"></i>
			</div>	
			
		<h5 class="mt-5 color-orange text-center"><strong>As tasker: </strong></h5>
			<div class="star-rate text-center">
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star-half-o"></i>
				<i class="fa fa-star-o"></i>				
				<i class="fa fa-star-o"></i>
			</div>	
			
			<h5 class="mt-5 color-orange"><i class="mr-2 fa fa-envelope"></i> <strong>Email :</strong></h5>
			<p class="mb-5"><?php echo $listArr->data->email;?></p>
			
			<h5 class="color-orange"><i class="mr-2 fa fa-phone"></i> <strong>Phone :</strong></h5>
			<p class="mb-5"><?php echo $listArr->data->mobile_no;?></p>
			
			<h5 class="color-orange"><i class="mr-2 fa fa-calendar"></i> <strong>Date Of Birth :</strong></h5>
			<p class="mb-5"><?php echo $listArr->data->date_of_birth;?></p>
			
			<h5 class="color-orange"><i class="mr-2 fa fa-globe"></i> <strong>Address :</strong></h5>
			<p class="mb-5"><?php echo $listArr->data->address;?></p>
			
			
		</div>
	</div>
	
	<div class="col-md-9 col-sm-8 border-left-profile">
		<h1 class="mt-0"><?php echo $listArr->data->first_name." ".$listArr->data->last_name;?></h1>
		<p class="small text-muted">Police Verified | Licensed</p>
		<h4 class="profile-heading mb-0">About me:- </h4>
		<p><?php echo $listArr->data->aboutme;?></p>
		
		
		
		
		
		<div class="clearfix">
		 <!-- begin:: Transportation Info -->
			<div class="col-sm-12 col-md-12 mt-5">
				<h4 class="profile-heading">Portfolio Or Work Images</h4>		
				<div class="attach-imgs imageContainer portfolio"> 		
				<?php foreach ($listArr->data->portfolioData as $imgarr) {
				    ?>			
					<a href="<?php echo $imgarr->portfolio_image?>" class="imageLink"><img alt="mg-responsive image" src="<?php echo $imgarr->portfolio_image?>"/></a>
					<?php }?>
					
<!--
					<form>
					 	<div class="image-upload">
							<label for="Portfolio">
								<img src="images/add_image.png" class="img-responsive" alt="Attachment"/>
							</label>
							<input id="Portfolio" type="file"/>
						</div>
					</form>
-->
				</div>
			</div>
			<!-- end:: portfolio images -->			
								
		</div>

	<div class="toggle-switch clearfix">
			<h5 class="mt-5"><strong>Can You Complete Your task Online</strong></h5>
						
			<div class="toggle">
			  <input type="checkbox" id="temp">
			  <label for="temp" class="lbl-yes"><span>Yes</span></label>
			  <label for="temp" class="lbl-no"><span>No</span></label>
			</div>
		</div>
		
	<div class="clearfix">
	  
		<div class="clearfix">
			<!-- begin:: skill -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">Skills to recieve Task alerts</h4>	
				
				<ul class="profile-detail-list">
					<?php
        				$skillArr = explode(',', $listArr->data->work_skills);
        				foreach ($skillArr as $sArr) {
        				    ?>
    				<li><?php echo $sArr; ?></li>
					<?php }?>
				</ul>	
			</div>
			<!-- end:: skill -->		

			<!-- begin:: Experience and Education -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">Experience and Education</h4>		
				<ul class="profile-detail-list">
				<?php foreach ($listArr->data->education as $eduarr) {
				    ?>
					<li><?php echo $eduarr->education_title; ?></li>
					<?php }?>
					
				</ul>
			</div>
			<!-- end:: Experience and Education -->
			
			<!-- begin:: Cities to receive Task -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading mb-0">Cities to receive Task Alerts</h4>		
				<p class="color-theam"><strong>Choose city name without postel code for entire city</strong></p>
				
				<ul class="profile-detail-list">
									
	     <?php
	     if(!isEmptyArr($listArr->data->work_city)){
	     foreach ($listArr->data->work_city as $work_city) {
			     $cityArr = getCityList();
			     foreach ($cityArr->data as $arr){
			       
			         if($arr->city_id == $work_city)
			         { ?><li><?php echo $arr->city ?></li>
					<?php }}}}?>
				</ul>
			</div>
			<!-- end:: Cities to receive Task -->
		</div>	
		
		<div class="clearfix">
			<!-- begin:: licenses or Trade certificates -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">licenses or Trade certificates</h4>	
				<ul class="profile-detail-list">
				<?php foreach ($listArr->data->all_licence_data as $licenceArr) {
				    ?>
					<li><?php echo $licenceArr->licenceType;?></li>
					<?php }?>
				</ul>	
			</div>
			<!-- end:: licenses or Trade certificates -->		

			<!-- begin:: Languages Spoken -->
			<div class="col-sm-12 col-md-4 mt-5 f">
				<h4 class="profile-heading">Languages Spoken or Understood</h4>		
				<ul class="profile-detail-list">
				<?php foreach ($listArr->data->all_speak_lang_data as $langAr) {
				    ?>
					<li><?php echo $langAr->languges;?></li>
					<?php }?>								
				</ul>
			</div>
			<!-- end:: Languages Spoken -->
			
			<!-- begin:: Transportation Info -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">Transportation Info</h4>		
				<ul class="profile-detail-list">
					<?php foreach ($listArr->data->all_transpotation_data as $transArr) {
				    ?>
					<li><?php echo $transArr->transportation_type;?></li>
					<?php }?>				
				</ul>
			</div>
			<!-- end:: Transportation Info -->
		</div>
			
		<div class="clearfix">	
			<!-- begin:: Transportation Info -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">GST/HST Information  (For registerd entities only)</h4>		
				<ul class="profile-detail-list">
					<?php foreach ($listArr->data->all_tax_info as $gstArr) {
				    ?>
					<li><?php echo $gstArr->tax_no."  ".$gstArr->tax_no_type?></li>
					<?php }?>				
				</ul>
			</div>
			<!-- end:: Transportation Info -->
			
			<!-- begin:: Transportation Info -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">Background Check</h4>		
				<div class="attach-imgs imageContainer">
				<?php foreach ($listArr->data->police_verifcation_data as $policeArr) {
				    ?>
					<a href="<?php echo $policeArr->policeVerificationDocument?>" class="imageLink"><img alt="image" src="<?php echo $policeArr->policeVerificationDocument?>"/></a>
				<?php }?>	
					
<!--
					<form class="">
					 	<div class="image-upload">
							<label for="Check">
								<img src="images/add_image.png" class="img-responsive" alt="Attachment"/>
							</label>
							<input id="Check" type="file"/>
						</div>
					</form>
-->
				</div>
			</div>
			<!-- end:: Transportation Info -->
		</div>
	</div>
</div>

    
    <div class="overlayContainer">
    
    <div class="imageBox">
      <div class="relativeContainer">
        <img class="largeImage" src="" alt="">
        <p class="imageCaption"></p>
      </div>  <!-- /relativeContainer -->
    </div>  <!-- /imageBox -->
    
  </div>  <!-- overlayContainer -->
   
<script>
	$(document).ready(function(){
		$(".profile-detail-list").each(function(){			
			if( $(this).children("li").length > 5) {
				$(this).parent("").append("<a href='#_' class='color-orange read-more'>Read More</a>");					
			}
			else{
				$(this).parent("").append("");
			}
		});
		
		// open read more
		$(".read-more").click(function(){			
			var numOfLi = $(this).siblings(".profile-detail-list").children("li").length;
			var liHeight = $(this).siblings(".profile-detail-list").children("li").height();
			var mb = $(this).siblings(".profile-detail-list").children("li").css("margin-bottom");
			var marginli = mb.replace("px", "");
			var totalLi = marginli * numOfLi; 
			var totalLiHeight = liHeight * numOfLi; 
			var ulHeight = totalLi + totalLiHeight ;			
			
			if($(this).text() === "Read More"){
				$(this).siblings(".profile-detail-list").animate({height: ulHeight });	
				$(this).text("Read Less")
			}
			else {
				$(this).siblings(".profile-detail-list").animate({height: "140px" });					
				$(this).text("Read More")
			}
			
		});
	});

		$(document).ready(function(){
			$(".profile-detail-list li").prepend( '<img src="<?php echo base_url('images/liststyle.png');?>" style="width:15px; height:15px;" />' );
		});
</script>
   