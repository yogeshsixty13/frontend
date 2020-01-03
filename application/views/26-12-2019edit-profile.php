<?php
    $data['menu'] = "profile-edit";
    $this->load->view('elements/header-menu', $data );
?>


<form method="post" action="<?php echo base_url('update-profile'); ?>"  enctype="multipart/form-data">
<div class="container mT150 mT130-xs">
	<div class="col-md-3 col-sm-4">
		<div class="profile-img edit-profile">
			<img class="img-responsive" src="<?php echo $listArr->data->image_url;?>" alt="profile" />
			
			<div class="form-group edit-profile-btn ">			
			<div class="file btn btn-lg btn-primary update">
			  Upload
			  <input type="file" class="form-control" name="file" accept="image/x-png,image/gif,image/jpeg"/>
			</div>
 		</div>
		</div>
		
<!--		verification Details-->
		<div class="verification">
<!--			<h4><i class="fa fa-male mr-3 color-theam"></i> Verification</h4>-->
		<div class="star-rate text-center">
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star-half-o"></i>
				<i class="fa fa-star-o"></i>
			</div>	
			
			<h5 class="mt-5 profile-subHead"><i class="mr-2 fa fa-envelope"></i> <strong>Email :</strong></h5>
			
			<div class="form-group mb-5">
				<p class="mb-5"><?php echo $listArr->data->email;?></p>
			</div>
			
			<h5 class="profile-subHead"><i class="mr-2 fa fa-phone"></i> <strong>Phone :</strong></h5>			
			<div class="form-group mb-5">
				<input type="text" class="form-control" value="<?php echo $listArr->data->mobile_no;?>">
			</div>
			
			<h5 class="profile-subHead"><i class="mr-2 fa fa-calendar"></i> <strong>Date Of Birth :</strong></h5>			
			<div class="form-group mb-5">
				<input type="text" class="form-control" value="<?php echo $listArr->data->date_of_birth;?>">
			</div>
			
			<h5 class="profile-subHead"><i class="mr-2 fa fa-globe"></i> <strong>Address :</strong></h5>
			<p class="mb-5"></p>
			
			<h6 class="profile-subHead"><strong>House No, Street Name* :</strong></h6>			
				<div class="form-group mb-3">
				<input type="text" class="form-control" value="<?php echo $listArr->data->address;?>">
			</div>
			
			<h6 class="profile-subHead"><strong>City :</strong></h6>
			<div class="form-group mb-3">
				<input type="text" class="form-control" value="<?php echo $listArr->data->city_name;?>">
			</div>
			
			<h6 class="profile-subHead"><strong>Zip Code :</strong></h6>
			<div class="form-group mb-5">
				<input type="text" class="form-control" value="<?php echo $listArr->data->pincode;?>">
			</div>
				
			
			
		</div>
	</div>
	
	<div class="col-md-9 col-sm-8 border-left-profile">
		
		<div class="mb-5">
			<div class="clearfix">
				<h1 class="mt-0 pull-left"><?php echo $listArr->data->first_name." ".$listArr->data->last_name;?></h1>	
				<a href="#_" class="profile-subHead pull-right" id="editName"><i class="fa fa-pencil"></i></a>
			</div>
			
			<div class="clearfix">
				<div class="col-xs-6"> 
					<input type="text" class="form-control userName" value="<?php echo $listArr->data->first_name;?>">	
				</div>
				<div class="col-xs-6"> 
					<input type="text" class="form-control userName" value="<?php echo $listArr->data->last_name;?>">	
				</div>
				
				
			</div>
			
		</div>	
		
		<div class="form-group mb-5">
			<div class="clearfix">
				<h4 class="pull-left"><strong class="profile-heading">About Me :-</strong></h4>
				<a href="#_" class="profile-subHead pull-right " id="editSummary"><i class="fa fa-pencil"></i></a>
			</div>
			<div class="clearfix">
				<p class="summaryDisplay"><?php echo $listArr->data->aboutme;?></p>
			</div>
				<textarea class="form-control profileSummary" readonly rows="3"><?php echo $listArr->data->aboutme;?></textarea>
		</div>
<!--
		<div class="form-group mb-5">
			<div class="clearfix">
				<h4 class="pull-left"><strong class="profile-heading">Profile summary :-</strong></h4>
				<a href="#_" class="profile-subHead pull-right" id="editSummary2"><i class="fa fa-pencil"></i></a>
			</div>
			<p class="summaryDisplay2">Here a 2 lines brief about skills or cover letter of the user will appear having some word limits</p>
			<textarea class="form-control profileSummary2" rows="2">Here a 2 lines brief about skills or cover letter of the user will appear having some word limits</textarea>
		</div>
-->
	 	<div class="clearfix">
		 <!-- begin:: Transportation Info -->
			<div class="col-sm-12 col-md-12 mt-5">
				<h4 class="profile-heading">Portfolio Or Work Images ( Max 5 )</h4>		
				<div class="attach-imgs edit-attach imageContainer portfolio"> 	
				<?php foreach ($listArr->data->portfolioData as $imgarr) {
				    ?>			
					<a href="<?php echo $imgarr->portfolio_image?>" class="imageLink"><img alt="mg-responsive image" src="<?php echo $imgarr->portfolio_image?>"/></a>
					<?php }?>				
					
					 	<div class="image-upload">
							<label for="Portfolio">
								<img src="images/add_image.png" class="img-responsive w-100" alt="Attachment"/>
							</label>
							<input id="Portfolio" type="file"/>
						</div>
				
				</div>
			</div>
			<!-- end:: Transportation Info -->
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
				<h4 class="profile-heading">Skills to recieve Task alerts (Max 10)</h4>	
				<ul class="profile-detail-list editiInfo skill">
					<?php
        				$skillArr = explode(',', $listArr->data->work_skills);
        				$totalRes = 0;
        				if(!isEmptyArr($skillArr)){
        				    $count = count($skillArr);
        				 foreach ($skillArr as $k=>$sArr) {
        				     $totalRes++;
        				     $k+= 1; 
        				   ?>
        				  <input type="hidden" name="skills[]" value="<?php echo $sArr; ?>" >
						<li id="<?php echo "sk_".$k?>"><?php echo $sArr; ?> <a  class='del-info'  onclick="remove_skill('<?php echo $k?>')"><i class='far fa-minus-square'></i></a></li>
					
					<?php }}?>					
				</ul>
				<div class="text-center">
					<input class="form-control" type="text" id="skill" autocomplete="off" placeholder="Add Another">
					<a class="btn btn-cancel py-1 mt-3"  onclick="add_skill()">Add</a>
				</div>	
			</div>
			<!-- end:: skill -->		

			<!-- begin:: Experience and Education -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">Experience Or Education</h4>		
				<ul class="profile-detail-list editiInfo education">
					<?php 
					$totaledu = 0;
					if(!isEmptyArr($listArr->data->education)){
					foreach ($listArr->data->education as $eduarr) {
					    $totaledu++;
					    $k+= 1; 
				    ?>
					<li id="<?php echo "edu_".$k;?>"><?php echo $eduarr->education_title; ?> <a  onclick="remove_education('<?php echo $eduarr->_id; ?>')" class='del-info'><i class='far fa-minus-square'></i></a></li>
					<?php }}?>	
					
				</ul>
				<div class="text-center">
					<input class="form-control" type="text" id="education"  placeholder="Add Another">
					<a class="btn btn-cancel py-1 mt-3"  onclick="add_education()" href="#">Add</a>
				</div>
			</div>
			<!-- end:: Experience and Education -->
			
			<!-- begin:: Cities to receive Task -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading mb-0">Cities to receive Task Alerts (Max 5)</h4>		
				<p class="color-theam"><strong>Choose city name without postel code for entire city</strong></p>
				<ul class="profile-detail-list editiInfo cityname">
					<?php 
					$countcity = 0;
					if(!isEmptyArr($listArr->data->work_city)){
					    foreach ($listArr->data->work_city as $k=>$cityarr) {
					    $countcity++;
					    $k+= 1; 
				    ?>
				    <input type="hidden" name="city[]" />
					<li id="<?php echo "city_".$k;?>"><?php echo $cityarr; ?> <a  onclick="remove_city('<?php echo $k; ?>')" class='del-info'><i class='far fa-minus-square'></i></a></li>
					<?php }}?>	
				</ul>
				<div class="text-center">
						<input class="form-control" id="cityname" list="citylist" type="text" placeholder="Add Another">
						<datalist id="citylist">
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
						<a class="btn btn-cancel py-1 mt-3"   onclick="add_city()" href="#">Add</a>
				</div>
			</div>
			<!-- end:: Cities to receive Task -->
		</div>	
		
		<div class="clearfix">
			<!-- begin:: licenses or Trade certificates -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">Licenses or Trade Certificates ( Max 5 )</h4>	
				<ul class="profile-detail-list editiInfo">
					<?php foreach ($listArr->data->all_licence_data as $licenceArr) {
				    ?>
					<li><?php echo $licenceArr->licenceType;?> <a href='#_' class='del-info'><i class='far fa-minus-square'></i></a></li>
					<?php }?>
				</ul>	
				<div class="text-center">
    				<input class="form-control" type="text" placeholder="Add Another">
    				<a class="btn btn-cancel py-1 mt-3" href="#">Add</a>
				</div>
			</div>
			<!-- end:: licenses or Trade certificates -->		

			<!-- begin:: Languages Spoken -->
			<div class="col-sm-12 col-md-4 mt-5 f">
				<h4 class="profile-heading">Languages Spoken or Understood</h4>		
				<ul class="profile-detail-list editiInfo">
					<?php foreach ($listArr->data->all_speak_lang_data as $langAr) {
				    ?>
					<li><?php echo $langAr->languges;?> <a href='#_' class='del-info'><i class='far fa-minus-square'></i></a></li>
					<?php }?>		
											
				</ul>
				<div class="text-center">
    				<input class="form-control" type="text" placeholder="Add Another">
    				<a class="btn btn-cancel py-1 mt-3" href="#">Add</a>
				</div>
			</div>
			<!-- end:: Languages Spoken -->
			
			<!-- begin:: Transportation Info -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">Transportation Info</h4>		
				<ul class="profile-detail-list editiInfo">
						<?php foreach ($listArr->data->all_transpotation_data as $transArr) {
				    ?>
					<li><?php echo $transArr->transportation_type;?> <a href='#_' class='del-info'><i class='far fa-minus-square'></i></a></li>
					<?php }?>			
										
				</ul>
				<div class="text-center">
						<input class="form-control" type="text" placeholder="Add Another">
						<a class="btn btn-cancel py-1 mt-3" href="#">Add</a>
				</div>
			</div>
			<!-- end:: Transportation Info -->
		</div>
			
		<div class="clearfix">	
			<!-- begin:: Transportation Info -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">GST/HST Information (For registerd entities only)</h4>		
				<ul class="profile-detail-list editiInfo">
					<?php foreach ($listArr->data->all_tax_info as $gstArr) {
				    ?>
					<li><?php echo $gstArr->tax_no."  ".$gstArr->tax_no_type?> <a href='#_' class='del-info'><i class='far fa-minus-square'></i></a></li>
					<?php }?>
									
				</ul>
				<div class="text-center">
    				<input class="form-control" type="text" placeholder="Add Another">
    				<a class="btn btn-cancel py-1 mt-3" href="#">Add</a>
				</div>
			</div>
			<!-- end:: Transportation Info -->
			
			<!-- begin:: Transportation Info -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">Background Check</h4>		
				<div class="attach-imgs edit-attach imageContainer">
					
				<?php foreach ($listArr->data->police_verifcation_data as $policeArr) {
				    ?>
					<a href="<?php echo $policeArr->policeVerificationDocument?>" class="imageLink"><img alt="image" src="<?php echo $policeArr->policeVerificationDocument?>"/></a>
				<?php }?>	
					<?php foreach ($listArr->data->all_licence_data as $liceArr) { ?>
					<a href="<?php echo $liceArr->licenceImage?>" class="imageLink"><img alt="image" src="<?php echo $liceArr->licenceImage?>"/></a>
					
				<?php }?>	
					
					 	<div class="image-upload">
							<label for="Check">
								<img src="images/add_image.png" class="img-responsive" alt="Attachment"/>
							</label>
							<input id="Check" type="file"/>
						</div>
					
				</div>
			</div>
			<!-- end:: Transportation Info -->	
		</div>
		
		</div>
	</div>
</div>
</form>

    <!-- payment -->  
  <div class="modal fade timeline-modal" id="payment" role="dialog">
    <div class="modal-dialog modal-sm">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title color-theam text-center">Select Payment Method</h4>
        </div>
        <div class="modal-body">
        	
           <ul class="nav nav-tabs my-job_tab mobile_fix-tab">
		  		<li class=" nav-item active"><a class="nav-link" data-toggle="tab" href="#receivePayment">Receive Payment</a></li>
				<li class="nav-item "><a class="nav-link" data-toggle="tab" href="#makePayment">Make payment</a></li>		  		 		
			</ul>
           
           	<div class="tab-content">	  		
		  		<div id="receivePayment" class="tab-pane fade in active position-relative">
					<p class="mt-5 px-4">Payments for Completed tasks shall be transferrd via E-transfers to your Registerd Email id and detaila of Security Question and answer shall be emailed to you and also avilable on the payment Records Window at all times.</p>
		  		</div>
		  		
		  		<div id="makePayment" class="tab-pane fade position-relative">
					<h4 class="mt-5 px-md-5">Enter card info</h4>
					<div class="form-group px-md-5">
						<input type="text" placeholder="card number" class="form-control" />
					</div>
					<div class="text-center px-md-5">
						<a href="#" type="button" class="btn btn-upload" data-dismiss="modal">Add Card</a>
					</div>
					<p class="mt-5 px-4 text-center">By Providing You Credit card details, you agree to Stripe T&amp;C. </p>
					<p class="mt-3 px-4 text-center">Be Safe Your credit card is not charged untill You authorize. </p>
		  		</div>
			</div>
        </div>
        <div class="modal-footer clearfix">          
<!--          <a href="#" type="button" class="btn btn-upload pull-right" data-dismiss="modal">Choose</a>          -->
        </div>
      </div>
      
    </div>
  </div>
    
    
<!-- lightbox image -->
<div class="overlayContainer">    
    <div class="imageBox">
      <div class="relativeContainer">
        <img class="largeImage" src="" alt="">
        <p class="imageCaption"></p>
      </div>  <!-- /relativeContainer -->
    </div>  <!-- /imageBox -->
    
  </div>  <!-- overlayContainer -->
     
   

<script type="text/javascript">
var count = <?php echo $totalRes;?>;
var countedu = <?php echo $totaledu; ?>;
var countcity = <?php echo $countcity;?>;
$(document).ready(function(){
	
	$(".profile-detail-list li").prepend( '<img src="<?php echo base_url('images/liststyle.png');?>" style="width:15px; height:15px;" />' );
	// Open text box when In-prson radio check
	$(".task-to-be-done").change(function() {
   		$("#in_person").slideUp();
		if ($("#in_person_rdo").is(':checked')) {
            $("#in_person").slideDown();
		}	
		
	});
	
	// Open text box when "By a certain day" radio check
	$(".due-date").change(function() {
   		$(".certain__day").slideUp();
		if ($("#in_due_date_certainDay").is(':checked')) {
            $(".certain__day").slideDown();
		}	
		
	});
});	

function remove_education(id)
{
	
    var loc = (base_url+'my_account/remove_education');		
    	
    	$.post(loc, { id:id }, function (json) {
    		var resp = ($.parseJSON(json));	
    		console.log( resp );
    		if( resp['type'] ==  "success" )
    		{
    			$("#edu_"+countedu).remove();
    			countedu--;
    		}
    });
}

function add_education()
{
    	var education_title = $("#education").val();
    	var loc = (base_url+'my_account/add_education');		
    	
    	$.post(loc, { education_title: education_title}, function (json) {
    		var resp = ($.parseJSON(json));	
    		console.log( resp );
    		if( resp['type'] ==  "success" )
    		{
    			countedu = countedu + 1;
    			$(".education").prepend( '<li id="edu_'+countedu+'"><img src="<?php echo base_url('images/liststyle.png');?>" style="width:15px; height:15px;" />'+education_title+'<a href="#_" onclick="remove_education('+countedu+')" class="del-info"><i class="far fa-minus-square"></i></a></li> ');
    		}
      	
    });
        
}
function add_skill()
{
	count = count + 1;
	var skill_name = $("#skill").val();
	$(".skill").prepend( '<li id="sk_'+count+'"><img src="<?php echo base_url('images/liststyle.png');?>" style="width:15px; height:15px;" />'+skill_name+'<a href="#_" onclick="remove_skill('+count+')" class="del-info"><i class="far fa-minus-square"></i></a></li> ');
	
}
function add_city()
{
	countcity = countcity + 1;
	var cityname = $("#cityname").val();
// 	var cityname = $('#citylist option:selected').text();
	$(".cityname").prepend( '<li id="city_'+countcity+'"><img src="<?php echo base_url('images/liststyle.png');?>" style="width:15px; height:15px;" />'+cityname+'<a href="#_" onclick="remove_city('+countcity+')" class="del-info"><i class="far fa-minus-square"></i></a></li> ');
}
function remove_city(id)
{
	$("#city_"+id).remove();
	countcity--;
}
	
function remove_skill(id)
{
	$("#sk_"+id).remove();
	count--;
}
	
$("#editName").click(function(){		
	$(".userName").slideToggle();
});

$("#editSummary").click(function(){		
	$(".profileSummary").slideToggle();
	$(".summaryDisplay").slideToggle();
});
$("#editSummary2").click(function(){		
	$(".profileSummary2").slideToggle();
	$(".summaryDisplay2").slideToggle();
});

</script>
    