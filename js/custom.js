(function() {
	// trim polyfill : https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/Trim
	if (!String.prototype.trim) 
	{
		(function() {
			// Make sure we trim BOM and NBSP
			var rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
			String.prototype.trim = function() {
				return this.replace(rtrim, '');
			};
		})();
	}

	[].slice.call( document.querySelectorAll( 'input.input__field' ) ).forEach( function( inputEl ) 
	{
		// in case the input is already filled..
		if( inputEl.value.trim() !== '' ) {
			classie.add( inputEl.parentNode, 'input--filled' );
		}

		// events:
		inputEl.addEventListener( 'focus', onInputFocus );
		inputEl.addEventListener( 'blur', onInputBlur );
	} );

	function onInputFocus( ev ) {
		classie.add( ev.target.parentNode, 'input--filled' );
	}

	function onInputBlur( ev ) 
	{
		if( ev.target.value.trim() === '' ) {
			classie.remove( ev.target.parentNode, 'input--filled' );
		}
	}
	
	if( $(".map-distance").length >0 )
	{
		var slider = new Slider('.map-distance', {});
		$('#OpenImgUpload').click(function(){ $('#imgupload').trigger('click'); });
		var slider = new Slider('#map-distanceMob', {});
	}
	
	if( $("#find_job_list").length >0 )
	{
		setAllTaskList( "", "getTaskList" );
		setAllTaskListGoogleMap( "", "getMyTaskListGoogleMap" );
//		setMyOfferWishlist( "", "getMyBidsList", 0 );
	}
	
	if( $("#my_post_list").length >0 )
	{
		setMyTaskList( "", "getTaskList" );
//		setMyTaskListGoogleMap( "", "getMyTaskListGoogleMap" );
		$("#my_post_list li").first().find('a').click();
	}

	if (isAutoLoadPageDetails )
		setInterval(auto_reload_page_data, 500);

	if ( isAutoLoadPagination )
		setInterval(auto_increment_pagination, 1000);
	
})();

/**
 * 
 */
$(".my-offer-wishlist").click( function(){
	setMyOfferWishlist( "", "getMyBidsList", true );
	$("#my_offer_wish_list li").first().find('a').click();
});

/**
 * 
 */
$(".my-post-task-list").click( function(){
	setAllTaskList( "", "getTaskList" );
	$("#my_post_list li").first().find('a').click();
});

/**
 *
 */
function auto_increment_pagination(){
	var paging = $('#auto_increment_pagination').text();
	var formData = "";
	
	if( paging != 0 )
	{
		var formID = $('#selectFilterFormID').text();
		if( formID != "" )	
			formData = $(formID).serialize();
		
		$.ajax({
			type: 'POST',
			url: base_url+'find_task/getTaskList/'+paging,
			data: { data:formData },
			beforeSend: function(){
			},
			success: function( data  )
			{
				paging = parseInt(paging) + 1;
				$('#auto_increment_pagination').text(paging);
				
				var id = "#find_job_list";
				var select = "";
				if( $("#posted_task").length > 0 )
					select = $("#posted_task").find("li.active").attr('id');
				
				if(select == 'my_post_task_information')
					id = 'my_post_list';
				else if(select == 'my_offer_whish_list')
					id = 'my_offer_wish_list';
				
				displayLeftSideCardListing( id, $.parseJSON( data ), true, false );
			}
		});
	}
}

/**
 * 
 */
function setAllTaskList( id, functn )
{
	var formData = "";
	if( id != "" )	
		formData = $(id).serialize();
	
	$.ajax({
		type: 'POST',
		url: base_url+'find_task/'+functn,
		data: { data:formData },
//		contentType: false,
//		cache: false,
//		processData:false,
//		dataType : "json",
//		enctype: 'multipart/form-data',
		beforeSend: function(){
			$("#find_job_list").html('');
			$(".preloader").removeClass('hide');
		},
		success: function( data  ){
			displayLeftSideCardListing( '#find_job_list', $.parseJSON( data ), true, false );
			$(".preloader").addClass('hide');
		} 
	});
}

$(".details-show").click(function(){
	$("jobd active").addClass("dpb");
});

$(".details-show").click(function(){
	$("jobd active").addClass("dpb");
});

$("input[name='sort_by']").click( function(){
	var selected = $("input[name='sort_by']:checked").val();
	$('#selectFilterFormID').text("#task_filter_form");
	if( selected == "Selected" ) 
		setAllTaskList( "#task_filter_form", "getSortByTaskSelectedList" );
	else if( selected == "Pending" ) 
		setAllTaskList( "#task_filter_form", "getSortByTaskPendingList" );
	else
	{
		$('#selectFilterFormID').text("");
		setAllTaskList( "", "getTaskList" );
		$('#auto_increment_pagination').text(1);
		auto_increment_pagination();
	}
});

$(".fcity-btn").click( function(){
	setAllTaskList( "#task_filter_form", "getTaskList" );
});

$(".budget-btn").click( function(){
	setAllTaskList( "#task_filter_form", "getTaskList" );
});

$(".job-budget-type").click( function(){
	var val = $(this).data('val');
	$("#job_budget_type").val(val);
	setAllTaskList( "#task_filter_form", "getTaskList" );
});

$(".clearFilter").click( function(){
	setAllTaskList( "", "getTaskList" );
});

/**
 * 
 */
function setMyOfferWishlist( id, functn, isOfferWishlist )
{
	var formData = append = "";
	if( id != "" )	
		formData = $(id).serialize();
	
	if( isOfferWishlist )
		append = "?isOfferWishlist=1";
		
	$.ajax({
		type: 'POST',
		url: base_url+'find_task/'+functn+append,
		data: { data:formData },
		beforeSend: function(){
			$("#find_job_list").html('');
			$(".preloader").removeClass('hide');
		},
		success: function( data  ){
			var id = "#find_job_list";
			if( $("#my_offer_wish_list").length > 0 )
				id = $('#my_offer_wish_list');
			
			displayLeftSideCardListing( id, $.parseJSON( data ), true, isOfferWishlist );
			$(".preloader").addClass('hide');
		}
	});
}

/**
 * 
 */
function setAllTaskListGoogleMap( id, functn )
{
	var formData = "";
	if( id != "" )	
		formData = $(id).serialize();
	
	$.ajax({
		type: 'POST',
		url: base_url+'find_task/'+functn,
		data: { data:formData },
		beforeSend: function(){
//			$("#find_job_list").html('');
//			$(".preloader").removeClass('hide');
		},
		success: function( data ){
			var locations = $.parseJSON(data );
		    var map = new google.maps.Map(document.getElementById('map'), { zoom: 8, center: new google.maps.LatLng( locations[0][1], locations[0][2]), mapTypeId: google.maps.MapTypeId.ROADMAP });
		    var infowindow = new google.maps.InfoWindow();
		    var marker, i;
		    for (i = 0; i < locations.length; i++) 
		    {  
	    		marker = new google.maps.Marker({
			        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
			        map: map
	    		});
	
	    		google.maps.event.addListener(marker, 'click', (function(marker, i) 
				{
	    			return function() { infowindow.setContent(locations[i][0]); infowindow.open(map, marker); }
				})(marker, i));
		    }
		} 
	});
}

// Without JQuery
//var slider = new Slider('#ex2', {});
//$('#OpenImgUpload').click(function(){ $('#imgupload').trigger('click'); });


/**
 * close any popup if open
 */
function closePopup()
{
	$(".btn-cancel").click();
}

/**
 * 
 * @param job_id
 * @returns
 */
function getJobDetails( job_id, active )
{
	$(".default-google-map").addClass('hide');
	$(".task-post-details").removeClass('hide');		
	$.ajax({
		type: 'POST',
		url: base_url+'find_task/getJobDetails',
		data: { job_id:job_id, active:active },
		beforeSend: function(){
			$(".task-full-details").html('');
			$(".task-post-details-preloader").removeClass('hide');
		},
		success: function(html){
			autoReloadJobDetails(html);	
			$('#auto_reaload_job_id').text(job_id);

			$('#auto_reaload_selected_tab').text(active);
			$('#is_auto_reaload_job_details').text(1);
			$(".task-post-details-preloader").addClass('hide');
		} 
	});
}

/**
 * 
 * @param sul
 * @returns
 */
function autoReloadJobDetails( html )
{
	var id = "find_task_information";		
	if( $("#posted_task").length > 0 )
		id = $("#posted_task").find("li.active").attr('id');
	
	$("#v_pills_"+id).html(html);
}

/**
 * 
 * @param active
 * @returns
 */
function auto_reload_page_data()
{
	var process = $("#is_auto_reaload_job_details").text();
	var job_id = $("#auto_reaload_job_id").text();
	
	if( process == 1 && job_id != 0 )
	{
		var active = $("#auto_reaload_selected_tab").text();
		$.ajax({
			type: 'POST',
			url: base_url+'find_task/getJobDetails',
			data: { job_id:job_id, active:active },
			beforeSend: function(){
			},
			success: function(html){
				autoReloadJobDetails(html);	
			}
		});
	}
}

/**
 * 
 */
function updateTabPane(active)
{
	$("#auto_reaload_selected_tab").text(active);
}

/**
 * 
 */
function openMakeOfferModal( job_id )
{
	$("#bid_amount").val('');
	$("#bid_messege").val('');
	$(".job_id").val(job_id);
	$(".error").text('');
}

/**
 * 
 * @returns
 */
$("#popup_make_an_offer_form").submit( function(){
	var formData = $("#popup_make_an_offer_form").serialize();
	var job_id = $(".job_id").val();
	
	$.ajax({
		type: 'POST',
		url: base_url+'find_task/submitBidOnJob',
		data: { data:formData },
		beforeSend: function(){
			$("#submit_bit").val( "wait..." ).attr( 'disabled' );
		},
		success: function( res ){
			var data = $.parseJSON( res );

			if( data.status == false )
				$(".error_msg").text( data.message );
			else
			{
				$(".success_msg").text( data.message );
				$(".btn-"+job_id).remove();
			}
			$("#submit_bit").val( "Submit Bid" ).removeAttr( 'disabled' );
			setInterval(function(){ closePopup }, 5000);
		}
	});
});

/**
 * 
 */
function openReSubmitOfferModal( bid_id, amt, isSearch )
{
	if( isSearch )
	{
		$.ajax({
			type: 'POST',
			url: base_url+'find_task/getAllJobDetails/'+bid_id,
			data: {},
			success: function( res ){
				var data = $.parseJSON( res );
				$("#r_bid_amount").attr("placeholder", data.amt );
				$("#bid_id").val(data.id);
			}
		});
	}
	else
	{
		$("#r_bid_amount").attr("placeholder", amt );
		$("#bid_id").val(bid_id);
	}
	
	$("#r_bid_messege").val('');
	$(".error").text('');
}

/**
 * 
 * @returns
 */
$("#re_submit_bid_offer").submit( function(){
	var formData = $("#re_submit_bid_offer").serialize();
	var bid_id = $("#bid_id").val();
	
	$.ajax({
		type: 'POST',
		url: base_url+'find_task/reSubmitBidOnJob',
		data: { data:formData },
		beforeSend: function(){
			$("#re_submit_bit").val( "wait..." ).attr( 'disabled' );
		},
		success: function( res ){
			var data = $.parseJSON( res );
			
			if( data.status == false )
				$(".error_msg").text( data.message );
			else
			{
				$(".success_msg").text( data.message );
				$(".btn-"+bid_id).remove();
			}
			
			$("#re_submit_bit").val( "Re Submit Offer" ).removeAttr( 'disabled' );
			setInterval(function(){ closePopup }, 5000);
		}
	});
});

/**
 * 
 */
function openIssueModal( job_id )
{
	$(".job_id").val(job_id);
	$(".error").text('');
}

/**
 * 
 * @returns
 */
$("#issue_form").submit( function(){
	var formData = $("#issue_form").serialize();
	var bid_id = $("#bid_id").val();
	
	$.ajax({
		type: 'POST',
		url: base_url+'find_task/submitReportIssue',
		data: { data:formData },
		beforeSend: function(){
			$("#submit_issue").val( "wait..." ).attr( 'disabled' );
		},
		success: function( res ){
			var data = $.parseJSON( res );
			console.log(data);
			if( data.status == false )
				$(".error_msg").text( data.message );
			else
			{
				$(".success_msg").text( data.message );
				$(".btn-"+bid_id).remove();
			}
			
			$("#submit_issue").val( "Submit Issue" ).removeAttr( 'disabled' );
			setInterval(function(){ closePopup }, 5000);
		}
	});
});

/**
 * 
 */
function openCancelTaskModal( contract_id )
{
	$(".contract_id").val(contract_id);
	$(".error").text('');
}

/**
 * 
 * @returns
 */
$("#cancel_task_form").submit( function(){
	var formData = $("#issue_form").serialize();
	
	$.ajax({
		type: 'POST',
		url: base_url+'find_task/submitCancelTask',
		data: { data:formData },
		beforeSend: function(){
			$("#submit_cancel_task").val( "wait..." ).attr( 'disabled' );
		},
		success: function( res ){
			var data = $.parseJSON( res );
			console.log(data);
			if( data.status == false )
				$(".error_msg").text( data.message );
			else
				$(".success_msg").text( data.message );
			
			$("#submit_cancel_task").val( "Procced" ).removeAttr( 'disabled' );
			setInterval(function(){ closePopup }, 5000);
		}
	});
});

/**
 * 
 */
function openAdditionalPaymentModal( job_id, amt )
{
	$(".old_amount").text(amt);
	$(".job_id").val(job_id);
	$(".error").text('');
}

/**
 * 
 * @returns
 */
$("#additional_payment_form").submit( function(){
	var formData = $("#issue_form").serialize();
	
	$.ajax({
		type: 'POST',
		url: base_url+'find_task/submitAdditionalPayment',
		data: { data:formData },
		beforeSend: function(){
			$("#submit_additional_payment").val( "wait..." ).attr( 'disabled' );
		},
		success: function( res ){
			var data = $.parseJSON( res );
			
			if( data.status == false )
				$(".error_msg").text( data.message );
			else
				$(".success_msg").text( data.message );
			
			$("#submit_additional_payment").val( "Procced" ).removeAttr( 'disabled' );
			
			setInterval(function(){ closePopup }, 5000);
		}
	});
});

/**
 * 
 */
function submitChat(){
//	var group_id = $("#group_id").val();
//	var message = $("#message").val();
	
	var formData = $("#chat_send_message").serialize();
	
	$.ajax({
		type: 'POST',
		url: base_url+'find_task/submitChatMessage',
		data: { data: formData },//message:message, group_id:group_id
		beforeSend: function(){
			$("#submit_chat").val( "wait..." ).attr( 'disabled' );
		},
		success: function( res ){
//			var data = $.parseJSON( res );
//			
//			if( data.status == false )
//				$(".error_msg").text( data.message );
//			else
//				$(".success_msg").text( data.message );
			
			$("#submit_chat").val( "Submit" ).removeAttr( 'disabled' );
			
			$("#auto_reaload_selected_tab").text("Chat");
			$("#auto_reaload_job_id").text( $("#chat_job_id").text() );
			auto_reload_page_data();
		}
	});
};

// My Task => Post/Offer/Wishlist
/**
 * 
 */
function setMyTaskList( id, functn )
{
	var formData = "";
	if( id != "" )	
		formData = $(id).serialize();
	
	$.ajax({
		type: 'POST',
		url: base_url+'my_task/'+functn,
		data: { data:formData },
		beforeSend: function(){
			$("#my_post_list").html('');
			$(".preloader").removeClass('hide');
		},
		success: function( data  ){
			$(".preloader").addClass('hide');
			displayLeftSideCardListing( '#my_post_list', $.parseJSON( data ), true, false );
		}
	});
}

/**
 * display left side card listing
 */
function displayLeftSideCardListing( id, data, isActive, isOfferWishlist )
{
	var chat = "Chat";
	var taskInfo = "Task Info";
	
	if( data.length == 0 )
	{
		var htmlLI = '';
		htmlLI += '<li class="active"><div class="extra-option">';
		htmlLI += '<a data-toggle="pill" href="#" class="mob_opn-sidepanel nav-link">';
		htmlLI += '<div class="forhr my-job">';
		htmlLI += '<div class="row">';
		htmlLI += '<div class="col-md-2 text-center col-xs-2 mB5">';
		htmlLI += '<div class="defaultJob  user-img"><img src="'+fileExists()+'"></div></div>';
		htmlLI += '<div class="col-md-7 col-xs-7">';
		htmlLI += '<h4 class="border-bottom pb-2">No Result Found.</h4>';
		htmlLI += '</div> </div> </div> </a> </div> </li>';
		$(id).append(htmlLI);
		$("#auto_increment_pagination").text(0);
	}
	else if( data.status != false )
	{
		$.each( data, function (k,v)
		{
			var active = "";
			if( k == 0 && isActive )
				active = "active";
			
			var cityname = "";
			if( v.location_data.city_name != "" )
				city_name = v.location_data.city_name;
			
			var htmlLI = '';
			htmlLI += '<li class="'+active+'"><div class="extra-option">';
			htmlLI += '<a data-toggle="pill" href="#task-id-'+v._id+'" class="mob_opn-sidepanel nav-link" onclick="getJobDetails(\''+v._id+'\', \''+taskInfo+'\' );">';
			htmlLI += '<div class="forhr my-job">';
			htmlLI += '<div class="row">';
			htmlLI += '<div class="col-md-2 pR0 text-center col-xs-2 mB5">';
			htmlLI += '<div class="defaultJob  user-img"><img src="'+fileExists( v.posted_by.profile_image )+'"></div></div>';
			htmlLI += '<div class="col-md-7 pL0 col-xs-7 pL15-xs">';
			htmlLI += '<h4 class="border-bottom pb-2">'+v.job_title+'</h4>';
			htmlLI += '<p class="mB5"> <span><i class="fas fa-map-marker mR10"></i>'+city_name+'</span> </p>';
			htmlLI += '<p class="mB5"><span><i class="far fa-calendar mR10"></i> <span>Due Date : </span> '+v.due_date_userfriendly+'</span></p>';
			htmlLI += '<p class="mB5 clearfix">';
			htmlLI += '<span class="pull-left"> <i class="fas fa-bullhorn mR10"></i>Offers :  '+v.job_bids+'</span>';
			htmlLI += '<span class="pull-right"> <i class="fas fa-users mR10"></i>Team Of :  '+v.no_person_required+'</span>';
			htmlLI += '</p> </div>';
			htmlLI += '<div class="col-md-3 text-center col-xs-3 pL0-xs pl-lg-0 pl-xl-3">';
			htmlLI += '<p class="price">$ '+v.total_budget+'</p>';
			htmlLI += '<span class="label label-danger">'+v.job_status+'</span>';
			htmlLI += '</div> </div> </div> </a>';
			
			if( isOfferWishlist )
			{
				htmlLI += '<div class="clearfix job-option mob-option">';
				htmlLI += '<div class="mr-4"><a href="#" class="text-dark" data-toggle="modal" data-target="#resubmitOffer" onclick="openReSubmitOfferModal( \''+v._id+'\', 0, 1 );">Re-submit Offer</a></div>';
				htmlLI += '<div class="mr-4"><a data-toggle="pill" href="#task-id-'+v._id+'" onclick="getJobDetails(\''+v._id+'\', \''+taskInfo+'\' );" class="text-dark view-task mob_opn-sidepanel">View Task Details</a></div>';
				htmlLI += '<div class="mr-4"><a href="#task-id-'+v._id+'" onclick="getJobDetails(\''+v._id+'\', \''+chat+'\'  );" class="text-dark">Chat Now</a></div></div>';
			}
							
			htmlLI +='</div></li>';
			$(id).append(htmlLI);
		});
	}
	else
		$("#auto_increment_pagination").text(0);
}

/**
 *  which besides getting the headers (all you need to check weather the file exists) gets the whole file. 
 *  If the file is big enough this method can take a while to complete.
 * @param url
 * @returns
 */
function fileExists(url) 
{
	var result = base_url+"images/favicon4.png";
    if(url)
    {
    	var img = new Image();
    	img.src = url;
    	   
        if( img.height != 0 )
        	result = url;
    } 
    
    return result;
}

function resetAllNotification()
{
	$.ajax({
		type: 'POST',
		url: base_url+'my_task/resetAllNotification',
		data: {},
		beforeSend: function(){},
		success: function( data  ){ window.location.reload(); }
	});
}