<?php
    $data['menu'] = "profile-edit";
    $this->load->view('elements/header-menu', $data );
?>

<style>
<!--
.custom-combobox {
    position: relative;
    display: inline-block;
  }
  .custom-combobox-toggle {
    position: absolute;
    top: 0;
    bottom: 0;
    margin-left: -1px;
    padding: 0;
  }
  .custom-combobox-input {
    margin: 0;
    padding-top: 2px;
    padding-bottom: 5px;
    padding-right: 5px;
  }
-->
</style>
<form id="updateform">
<div class="container mT150 mT130-xs">
	<div class="col-md-3 col-sm-4">
		<div class="profile-img edit-profile">
			<img class="img-responsive" src="<?php echo load_image($listArr->data->image_url);?>" alt="profile" id="profile_img_00"/>
			<div class="form-group edit-profile-btn ">			
			<div class="file btn btn-lg btn-primary update"> Upload
				<input type="file" class="form-control" onchange="readURL(this);" id="img_profile_00" name="profile_image" accept="image/x-png,image/gif,image/jpeg"/>
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
				<input type="text" name="mobile_no"class="form-control" value="<?php echo $listArr->data->mobile_no;?>">
			</div>
			
			<h5 class="profile-subHead"><i class="mr-2 fa fa-calendar"></i> <strong>Date Of Birth :</strong></h5>			
			<div class="form-group mb-5">
				<input type="text" name="date_of_birth" class="form-control" value="<?php echo $listArr->data->date_of_birth;?>">
			</div>
			
			<h5 class="profile-subHead"><i class="mr-2 fa fa-globe"></i> <strong>Address :</strong></h5>
			<p class="mb-5"></p>
			
			<h6 class="profile-subHead"><strong>House No, Street Name* :</strong></h6>			
				<div class="form-group mb-3">
				<input type="text" name="address_one" class="form-control" value="<?php echo $listArr->data->address;?>">
			</div>
			
			<h6 class="profile-subHead"><strong>City :</strong></h6>
			<div class="form-group mb-3">
				<select name="city_id" class="form-control">
            				<?php 
            				
            					$cityArr = getCityList();
            					if( $cityArr->status == true )
            					{
            					    if( !isEmptyArr( $cityArr->data ) )
            					    {
            					        foreach ( $cityArr->data as $city )
            					        {?>
            					           <option value="<?php echo $city->city_id;  ?>" <?php echo ($listArr->data->city_id == $city->city_id) ? "selected" : "";?> ><?php echo $city->city.', '.$city->state_name.', '.$city->country_name ?></option>
    					     <?php       } 
            					    }
            					    else ?>
            					        <option value="">No City Available</option>
            					        
            					<?php } 
            					else ?>
            					    <option value="">Invalid token header.</option>
            					
				</select>
            				
			</div>
			
			<h6 class="profile-subHead"><strong>Zip Code :</strong></h6>
			<div class="form-group mb-5">
				<input type="number" name="pincode" class="form-control" value="<?php echo $listArr->data->pincode;?>">
			</div>
				
			
			
		</div>
	</div>
	
	<div class="col-md-9 col-sm-8 border-left-profile">
		
		<div class="mb-5">
			<div class="clearfix">
				
				<h1 class="mt-0 pull-left"><?php echo $listArr->data->first_name." ".$listArr->data->last_name;?></h1>	
				<a href="#_" class="profile-subHead pull-right" id="editName"><i class="fas fa-pencil-alt"></i></a>
			</div>
			
			<div class="clearfix">
				<div class="col-xs-6"> 
					<input type="text" name="first_name" class="form-control userName" value="<?php echo $listArr->data->first_name;?>">	
				</div>
				<div class="col-xs-6"> 
					<input type="text" name="last_name" class="form-control userName" value="<?php echo $listArr->data->last_name;?>">	
				</div>
				
				
			</div>
			
		</div>	
		
		<div class="form-group mb-5">
			<div class="clearfix">
				<h4 class="pull-left"><strong class="profile-heading">About Me :-</strong></h4>
				<a href="#_" class="profile-subHead pull-right " id="editSummary"><i class="fas fa-pencil-alt"></i></a>
			</div>
			<div class="clearfix">
				<p class="summaryDisplay"><?php echo $listArr->data->aboutme;?></p>
			</div>
				<textarea class="form-control profileSummary" name="aboutme"  rows="3"><?php echo $listArr->data->aboutme;?></textarea>
		</div>
<!--
		<div class="form-group mb-5">
			<div class="clearfix">
				<h4 class="pull-left"><strong class="profile-heading">Profile summary :-</strong></h4>
				<a href="#_" class="profile-subHead pull-right" id="editSummary2"><i class="fas fa-pencil-alt"></i></a>
			</div>
			<p class="summaryDisplay2">Here a 2 lines brief about skills or cover letter of the user will appear having some word limits</p>
			<textarea class="form-control profileSummary2" rows="2">Here a 2 lines brief about skills or cover letter of the user will appear having some word limits</textarea>
		</div>
-->
	<form id="portfolioform" method="post" enctype="multipart/form-data">
	 	<div class="clearfix">
		 <!-- begin:: Transportation Info -->
			<div class="col-sm-12 col-md-12 mt-5">
				<h4 class="profile-heading">Portfolio Or Work Images ( Max 5 )</h4>		
				<div class="attach-imgs edit-attach  imageContainer portfolio"> 	
    				<?php
    				$countportfolio = 0;
    				if(!isEmptyArr($listArr->data->portfolioData))
    				{
    				    foreach ($listArr->data->portfolioData as $k=>$imgarr) 
    				    {
        				    $countportfolio++;
        				    $k += 1;
        				    ?>	
        				    <div class="position-relative" id="po_<?php echo $imgarr->_id;?>">				    		
            					<a href="<?php echo $imgarr->portfolio_image?>" class="imageLink "><img alt="mg-responsive image" src="<?php echo $imgarr->portfolio_image?>"/></a>
            					<a onclick="remove_portfolio('<?php echo $imgarr->_id?>')" class="del-img"></a>
        					</div>
    					<?php } 
    				}?>		
					
					<div class="image">
						<div class="position-relative hide">
        					<img src="<?php echo load_image('');?>" width="100" height="100" id="port_Image_00" class="image" style="margin-bottom:0px;padding:3px;" alt="logo"><br>
        					<a onclick="javascript:clear_image('port_Image_00')" class="del-img"></a>
    					</div>
						<input type="file" id="imge_00" onchange="add_portfolio(this,'00');" style="display: none;" accept="image/jpg,image/png">
						<input type="hidden" value="portfolioimg"  id="hiddenImgLogo">
						<div class="image-upload d-block d-none">
							<a onclick="$('#imge_00').trigger('click');"><label for="Portfolio">
								<img src="<?php echo load_image('images/add_image.png')?>" class="img-responsive w-100" alt="Attachment"/>
							</label></a>
						</div>
					</div>
				</div>
			</div>
			<!-- end:: Transportation Info -->
			</div>
		</form>	
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
					foreach ($listArr->data->education as $k=>$eduarr) {
					    $totaledu++;
					    $k+= 1; 
				    ?>
				    <li id="<?php echo "edu_".$k;?>"><?php echo $eduarr->education_title; ?> <a  onclick="remove_education('<?php echo $eduarr->_id; ?>')" class='del-info'><i class='far fa-minus-square'></i></a></li>
					<?php }
					}?>	
					
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
				    <input type="hidden" name="city[]" value="<?php echo $cityarr;?>"/>
				    <?php
            	     if(!isEmptyArr($listArr->data->work_city)){
            	     foreach ($listArr->data->work_city as $work_city) {
            			     $cityArr = getCityList();
            			     foreach ($cityArr->data as $arr){
            			       
            			         if($arr->city_id == $work_city)
			         { ?><li id="<?php echo "city_".$k;?>"><?php echo $arr->city; ?> <a  onclick="remove_city('<?php echo $k; ?>')" class='del-info'><i class='far fa-minus-square'></i></a></li></li>
					<?php }}}}?>
					
					<?php }}?>	
				</ul>
				<div class="text-center">
<!-- 						<input class="form-control" id="cityname" list="citylist" type="text" placeholder="Add Another"> -->
						<div class="ui-widget">	
							
          					  <select id="combobox" class="form-control" >
            					<?php 
            					$cityArr = getCityList();
            					if( $cityArr->status == true )
            					{
            					    if( !isEmptyArr( $cityArr->data ) )
            					    {
            					        foreach ( $cityArr->data as $city )
            					        {
            					            echo '<option value="'.$city->city_id.'">'.$city->city.', '.$city->state_name.', '.$city->country_name.'</option>';
            					        }
            					    }
            					    else 
            					        echo '<option value="">No City Available</option>';
            					}
            					else
            					    echo '<option value="">Invalid token header.</option>';
            					?>
            				</select>
     				   </div>
						<a class="btn btn-cancel py-1 mt-3"   onclick="add_city()" href="#">Add</a>
				</div>
			</div>
			<!-- end:: Cities to receive Task -->
		</div>	
		
		<div class="clearfix">
			<!-- begin:: licenses or Trade certificates -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">Licenses or Trade Certificates ( Max 5 )</h4>	
				<ul class="profile-detail-list editiInfo licences">
				
					<?php 
					$countlicences = 0;
					if(!isEmptyArr($listArr->data->all_licence_data)){
					  
					foreach ($listArr->data->all_licence_data as $k=>$licenceArr) {
					    $countlicences++;
					    $k += 1;
				    ?>
				    <li id="li_<?php echo $k;?>"><?php echo $licenceArr->licenceType;?> <a onclick="remove_licence('<?php echo $k; ?>')" class='del-info'><i class='far fa-minus-square'></i></a></li>
					<?php } }?>
				</ul>	
				<div class="text-center">
<!--     				<input class="form-control" type="text" id="licences"  placeholder="Add Another"> -->
<!--     				<input class="form-control" id="licences"  list="" type="text" placeholder="Add Another"> -->
				<form id="licencedata" method="post" enctype="multipart/form-data">
					<select id="licences" class="form-control" name="licenceType">
						<option value="">Add Another</option>
        				<option value="GUN_LICENCE">GUN_LICENCE</option>
        				<option value="DRIVING_LICENCE">DRIVING_LICENCE</option>
        				<option value="INSURANCE_LICENCE">INSURANCE_LICENCE</option>
        			</select>
        			<div class="image-upload">
						<label for="licence">
							<img src="images/add_image.png" class="img-responsive w-100" alt="Attachment">
						</label>
						<input type="hidden" name="lic_img" id="lic_img">
						<input id="licence" type="file" name="licenceImage">
					</div>
					<a class="btn btn-cancel py-1 mt-3" id="licencesubmit" type="submit">Add</a>
				</form>
				</div>
			</div>
			<!-- end:: licenses or Trade certificates -->		

			<!-- begin:: Languages Spoken -->
			<div class="col-sm-12 col-md-4 mt-5 f">
				<h4 class="profile-heading">Languages Spoken or Understood</h4>		
				<ul class="profile-detail-list editiInfo language">
					<?php
					$countlang = 0;
					if(!isEmptyArr($listArr->data->all_speak_lang_data)){
					 
					foreach ($listArr->data->all_speak_lang_data as $k=>$langAr) {
					    $countlang++;
					    $k += 1;
				    ?>
					<li id="lang_<?php echo $langAr->_id;?>"><?php echo $langAr->languges;?> <a class='del-info' onclick="remove_lang('<?php echo $langAr->_id?>');"><i class='far fa-minus-square'></i></a></li>
					<?php } }?>		
											
				</ul>
				<div class="text-center">
    				<input class="form-control" id="languges" type="text" placeholder="Add Another">
    				<a class="btn btn-cancel py-1 mt-3" onclick="add_lang()">Add</a>
				</div>
			</div>
			<!-- end:: Languages Spoken -->
			
			<!-- begin:: Transportation Info -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">Transportation Info</h4>		
				<ul class="profile-detail-list editiInfo transpotation">
				<?php 
				$trans = 0;
				if(!isEmptyArr($listArr->data->all_transpotation_data)){
				foreach ($listArr->data->all_transpotation_data as $k=>$transArr) {
			       $trans++;
				    $k += 1;
				    ?>
					<li id="tr_<?php echo $transArr->_id;?>"><?php echo $transArr->transportation_type;?> <a onclick="remove_transpotation('<?php echo $transArr->_id?>')" class='del-info'><i class='far fa-minus-square'></i></a></li>
					<?php }}?>			
										
				</ul>
				<div class="text-center">
					<select id="transpotation" class="form-control" >
						<option value="">Add Another</option>
        				<option value="choose Transport">choose Transport</option>
        				<option value="CAR">CAR</option>
        				<option value="WALK">WALK</option>
        				<option value="CYCLE">CYCLE</option>
        				<option value="SUV_CAR">SUV_CAR</option>
        				<option value="MINIVAN">MINIVAN</option>
        				<option value="MOTORBIKE">MOTORBIKE</option>
        				<option value="HEAVY_LOAD_TRUCK">HEAVY_LOAD_TRUCK</option>
        				<option value="LIGHT_DUTY_TRUCK">LIGHT_DUTY_TRUCK</option>
        				<option value="HEAVY_DUTY_TRUCK">HEAVY_DUTY_TRUCK</option>
        				<option value="OTHERS">OTHERS</option>
        				
        			</select>
						<a class="btn btn-cancel py-1 mt-3" onclick="add_transpotation()">Add</a>
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
					<li><?php echo $gstArr->tax_no."  ".$gstArr->tax_no_type?> </li>
					<?php }?>
									
				</ul>
				
			</div>
			<!-- end:: Transportation Info -->
			
			<!-- begin:: Transportation Info -->
			<div class="col-sm-12 col-md-4 mt-5">
				<h4 class="profile-heading">Background Check</h4>		
				<div class="attach-imgs edit-attach  imageContainer backgroundimg">
			  
				<?php 
				if(!isEmptyArr($listArr->data->police_verifcation_data)){
				foreach ($listArr->data->police_verifcation_data as $policeArr) {
				    ?>
				     <div class="position-relative" id="bck_<?php echo $policeArr->_id?>">	
					 <a href="<?php echo $policeArr->policeVerificationDocument?>" class="imageLink"><img alt="image" src="<?php echo $policeArr->policeVerificationDocument?>"/></a>
					<!-- <a class="del-img" onclick="remove_backgroundimg('<?php //echo $policeArr->_id;?>')"></a> --> 
					</div>
				<?php } }?>	
					
				</div>
				<div class="image">
						<div class="position-relative">
        					<img src="http://192.168.29.134/codeigniter/gst_billing/images/no-image.jpg" width="100" height="100" id="back_Image_00" class="image" style="margin-bottom:0px;padding:3px;" alt="logo"><br>
        					<a onclick="javascript:clear_image('port_Image_00')" class="del-img"></a>
    					</div>
    				
							<input type="file" id="bk_img_00" onchange="add_background(this,'00');" style="display: none;" accept="image/jpg,image/png">
							<input type="hidden" value="back_ground_img"  id="hidden_bk_img_00">
						
						<div class="image-upload d-block d-none">
							<a onclick="$('#bk_img_00').trigger('click');"><label for="Portfolio">
								<img src="images/add_image.png" class="img-responsive w-100" alt="Attachment"/>
							</label></a>
						</div>
					</div>
					
			</div>
			<!-- end:: Transportation Info -->	
		</div>
		
		</div>
	</div>
</div>
</form>

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
var countlicence = <?php echo $countlicences;?>;
var countlang = <?php echo $countlang;?>;
var counttrans = <?php echo $trans; ?>;
var countportfolio = <?php echo $countportfolio;?>;

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
	
	$("#licencesubmit").click(function (event) {

	 var input = "licence";
	 if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
               $('#lic_img').attr('val',e.target.result);
            }
            
            reader.readAsDataURL(input.files[0]);
		
		var loc = (base_url+'my_account/add_licences');		
        //stop submit the form, we will post it manually.
        event.preventDefault();

        // Get form
        var form = $('#licencedata')[0];
	
		// Create an FormData object 
        var data = new FormData(form);
    
        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: loc,
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function (data) {}
        });
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
    		
    		if(resp['type'] ==  "success")
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
	$(".skill").prepend( '<input type=hidden name=skills[] value="'+skill_name+'" /><li id="sk_'+count+'"><img src="<?php echo base_url('images/liststyle.png');?>" style="width:15px; height:15px;" />'+skill_name+'<a  onclick="remove_skill('+count+')" class="del-info"><i class="far fa-minus-square"></i></a></li> ');
	
}
function add_city()
{
	countcity = countcity + 1;
// 	var cityname = $("#combobox").val();	
	var cityname = $("#combobox").children("option:selected").text();
	var cityid = $("#combobox").children("option:selected").val();
	$(".cityname").prepend( '<input type="hidden" name="city[]" value="'+cityid+'"/><li id="city_'+countcity+'"><img src="<?php echo base_url('images/liststyle.png');?>" style="width:15px; height:15px;" />'+cityname+'<a href="#_" onclick="remove_city('+countcity+')" class="del-info"><i class="far fa-minus-square"></i></a></li> ');
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
function savedata()
{
	var loc = (base_url+'my_account/update_profile');		
	var data = $("#updateform").serialize();
	$.post(loc, data, function (data) {
		var resp = ($.parseJSON(json));	
		console.log( resp );
		if( resp['type'] ==  "success" )
		{
			
		}
  	
	});
}
function add_lang()
{
	var languges = $("#languges").val();
	var loc = (base_url+'my_account/add_language');		
	
	$.post(loc, { languges: languges}, function (json) {
		var resp = ($.parseJSON(json));	
		console.log( resp );
		if(  resp['status'] ==  "true" )
		{
			var id = resp['data'][0]._id;
			countlang = countlang + 1;
			$(".language").prepend( '<li id="lang_'+id+'"><img src="<?php echo base_url('images/liststyle.png');?>" style="width:15px; height:15px;" />'+languges+'<a href="#_" onclick="remove_lang(\''+id+'\')" class="del-info"><i class="far fa-minus-square"></i></a></li> ');
		}
  	
});
}
function remove_lang(id)
{
	var loc = (base_url+'my_account/remove_language');		
    	
    	$.post(loc, { id:id }, function (json) {
    		var resp = ($.parseJSON(json));	
    		console.log( resp );
    		if( resp['status'] ==  "true" )
    		{
				$("#lang_"+id).remove();
    			countlang--;
    		}
    });
}
function add_transpotation()
{
	var transportation_type = $("#transpotation").children("option:selected").val();
	var loc = (base_url+'my_account/add_transpotation');		
	$.post(loc, { transportation_type: transportation_type}, function (json) {
		
		var resp = ($.parseJSON(json));
	
		if( resp['status'] ==  "true" )
		{
			var id = resp['data'][0]._id;
			counttrans = counttrans + 1;
			$(".transpotation").prepend( '<li id="tr_'+id+'"><img src="<?php echo base_url('images/liststyle.png');?>" style="width:15px; height:15px;" />'+transportation_type+'<a onclick="remove_transpotation(\''+id+'\')" class="del-info"><i class="far fa-minus-square"></i></a></li> ');
		}
  	
});
}
function remove_transpotation(id)
{
	var loc = (base_url+'my_account/remove_transpotation');		
    	
    	$.post(loc, { id:id }, function (json) {
    		var resp = ($.parseJSON(json));	
    		
    		if( resp['status'] ==  "true" )
    		{
    			$("#tr_"+id).remove();
    			counttrans--;
    		}
    });
}
function remove_licence(id)
{
	
    var loc = (base_url+'my_account/remove_licence');		
    	
    	$.post(loc, { id:id }, function (json) {
    		var resp = ($.parseJSON(json));	
    		console.log( resp );
    		if( resp['type'] ==  "success" )
    		{
    			$("#li_"+countedu).remove();
    			countlicence--;
    		}
    });
}

function add_portfolio(input,position) {
	var inputId = input.id;
	var prevImgId = $('#'+inputId).parent().find('img').attr('id'); //find parent img id
	strInput = inputId.substring(0,inputId.indexOf("_") + 1);
	strPrevImg = prevImgId.substring(0,prevImgId.indexOf("_") + 1);
	var imgName = $('#'+strInput+position).val();
	
	
	if (input.files && input.files[0]) 
		{
			var reader = new FileReader();
			reader.onload = function (e) 
			{
				$('#port_Image_00').attr('src', e.target.result);
				$('#'+inputId).next().val(imgName);
			}
			reader.readAsDataURL(input.files[0]);
		
        var loc = (base_url+'my_account/add_portfolio');	
		var data = $("#portfolioform").serialize();
		console.log(data);
    	$.post(loc, {data :data}, function (data) {
    		var resp = ($.parseJSON(json));	
    		console.log( resp );
    		if( resp['type'] ==  "success" )
    		{
    			
    		}
      	
    	});
	}
	
}
function add_background(input,position) {
	var inputId = input.id;
	var prevImgId = $('#'+inputId).parent().find('img').attr('id'); //find parent img id
	strInput = inputId.substring(0,inputId.indexOf("_") + 1);
	strPrevImg = prevImgId.substring(0,prevImgId.indexOf("_") + 1);
	var imgName = $('#'+strInput+position).val();
	
	
	if (input.files && input.files[0]) 
		{
			var reader = new FileReader();
			reader.onload = function (e) 
			{
				$('#back_Image_00').attr('src', e.target.result);
				$('#hidden_bk_img_00').next().val(imgName);
			}
			reader.readAsDataURL(input.files[0]);
		
        var loc = (base_url+'my_account/add_backgroundimg');	
		var data = $("#portfolioform").serialize();
		console.log(data);
    	$.post(loc, {data :data}, function (data) {
    		var resp = ($.parseJSON(json));	
    		console.log( resp );
    		if( resp['type'] ==  "success" )
    		{
    			
    		}
      	
    	});
	}
	
}
function clearHiddenImage(para1)
{
	var hideInput = $("#"+para1).nextAll('input:[type=hidden]')[0];//next('input:hidden').val(''); //empty hidden value
	$(hideInput).val('');
}

function remove_portfolio(id)
{
	var loc = (base_url+'my_account/remove_portfolio');		
	$.post(loc, { portfolio_id:id }, function (json) 
	{
		var resp = ($.parseJSON(json));	
		if(resp['status'] ==  "true")
		{
			$("#po_"+id).remove();
			countportfolio--;
		}
	});
}

function remove_backgroundimg()
{
	var loc = (base_url+'my_account/remove_backgroundchecks');		
	
	$.post(loc, { id:id }, function (json) {
		var resp = ($.parseJSON(json));	
		console.log( resp );
		if(resp['status'] ==  "true")
		{
			$("#po_"+id).remove();
			countportfolio--;
		}
});
	
	
}



function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#profile_img_00').attr('src', e.target.result);}
            reader.readAsDataURL(input.files[0]);
        }
    }
// function readURL(input,position) 
// {
// 	var inputId = input.id;
// 	var prevImgId = $('#'+inputId).parent().find('img').attr('id'); //find parent img id
// 	strInput = inputId.substring(0,inputId.indexOf("_") + 1);
// 	strPrevImg = prevImgId.substring(0,prevImgId.indexOf("_") + 1);
// 	var imgName = $('#'+strInput+position).val();
// 	var ext = imgName.split('.').pop().toLowerCase();
	
// 	if (input.files && input.files[0]) 
// 		{
// 			var reader = new FileReader();
// 			reader.onload = function (e) 
// 			{
// 				$('#'+strPrevImg+position).attr('src', e.target.result);
// 				$('#'+inputId).next().val(imgName);
// 			}
// 			reader.readAsDataURL(input.files[0]);
// 		 }
	
// }

/*
+---------------------------------------------+
	clear on set no image display
+---------------------------------------------+
*/
function clear_image(para1)
{
	$("#"+para1).attr('src', base_url+"images/no-image.jpg");
	clearHiddenImage(para1);
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
    