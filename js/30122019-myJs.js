// JavaScript Document
"use strict";
jQuery('.drp-filter').on('click', function (e) {
	
  e.stopPropagation();
});

$('.drp-filter .btn-upload').click(function() {
  $(this).parents('.dropdown').find('button.dropdown-toggle').dropdown('toggle');
});
// Fetch value form filters

// Task Price
$(document).ready(function(){

// city	
	$(".fcity-btn").click(function() {     	
		var cityName = $(".searchCity").val();				
		
		if ($("#locationOnline").is(':checked')) {
        	cityName = $("#locationOnline").val();		
		}		
		$(".city_name").text(cityName);
    }); 

// sort By
	$(".sortBy [type='radio']").click(function() {     	
		var sort_by = $(this).val();			
		$(".sort-by").text(sort_by);		
    }); 
	

// Job Type
	$(".job-type li").click(function() {     	
		var jType = $(this).children("a").text();			
		$(".job_type").text(jType);
    }); 
	
// Budget
	$(".budget-btn").click(function() {     	
		var bMin = $(".bmin").val();			
		var bMax = $(".bmax").val();			
		$(".fbudget").text("$" + bMin + " - $" + bMax );
    }); 
	
// Mobile Budget
	$(".budget-btn").click(function() {     	
		var bMin = $(".bmin").val();			
		var bMax = $(".bmax").val();			
		$(".fbudget").text("$" + bMin + " - $" + bMax );
    }); 
	
	//clear filter
	$(".clearFilter").click(function(){
		$(".sort-by").text("");
		$(".city_name").text("");
		$(".job_type").text("");
		$(".fbudget").text("");		
	});

	$(".post-job").on( "click", function()
	{
		var id = $(this).attr( "data-id" );		
		if($('#home_li').hasClass('active'))
		{
			var title = $('#input-4').val();
			var description = $('#input-45').val();
			
			if( title == "" )
			{
				$('#input-4').addClass("error_border");
				$('#detail').text("Please enter task title").fadeIn().fadeOut(6000);
				return false;
			}
			
			if(title.length < 10)
			{ 
				$('#task-title').removeClass("d-none");
				var len =title.length;
				var rem =  10 - parseInt(title.length);
			
				if (len >= 10)
					$('#task-title').text(""); 
				
				if( len > 50)
					$('#task-title').text("Title should be (10-50) words").fadeIn(1000);
				else 
					$('#task-title').text("add more "+ rem +" words").fadeIn(1000);
			}
			
			if( description == "" )
			{
				$('#input-45').addClass("error_border");
				$('#detail').text("Please enter description").fadeIn().fadeOut(6000);
				$("#input-45").on("keyup",function(){
					$('#input-45').removeClass("error-border");
				});
				return false;
			}
		}
		else
		{
			$("ul.nav-tabs li").removeClass( "active" );
			$("#"+id).addClass("active");	
		}
	});
	
	// Get tab pane with and applied to my-job_tab
	var width = $(".my-job_tab").siblings(".tab-content").width();	
	$(".my-job_tab").css("width", width);
	
	// set limit of task heading to 50
	$(".job-title").text(function(index, currentText) {
		return currentText.substr(0, 50);
	});
	
	// set limit of task heading to 50
	$(".findjob h4").text(function(index, currentText) {		
		return currentText.substr(0, 50);
	});
		
	// set limit of notification dropdown to 50
	$(".noti-detail > p").text(function(index, currentText) {
		return currentText.substr(0, 150) + " ...";
	});
// set limit of notification dropdown to 25
	$(".dropdown-menu .noti-detail > p").text(function(index, currentText) {
		return currentText.substr(0, 25) + " ";
	});

  	var showChar = 256;  // How many characters are shown by default
    var ellipsestext = "...";
    var moretext = "Read More";
    var lesstext = "Read Less";
    
    //Cut content based on showChar length
    if ($(".detail-txt").length) 
    {
        $(".detail-txt").each(function() 
		{
            var content = $(this).html();
            if(content.length > showChar) 
            {
                var contentExcert = content.substr(0, showChar);
                var contentRest = content.substr(showChar, content.length - showChar);
                var html = contentExcert + '<span class="toggle-text-ellipses">' + ellipsestext + ' </span> <span class="toggle-text-content"><span>' + contentRest + '</span><a id="test" href="javascript:;" class=" read-more moreless pl-3">' + moretext + '</a></span>';
                $(this).html(html);
            }
        });
    }
    
    //Toggle content when click on read more link
   	$('.moreless').click(function() {
	
		if ($('#test').text() ===  moretext)
	    	$(this).html(lesstext);
	  	else
	    	$(this).html(moretext);

		$(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });
   	
 // hide text box when fixedProce radio check in post task modal
	$("input[name='job_budget_type']").change(function() 
	{
   		$("#txtHour").slideDown();
		if ($("#fixedPrice").is(':checked'))
            $("#txtHour").slideUp();
	});
	
	if($('#in_online_rdo').is(':checked'))
		$('#in_person').css("display","none");
	
	// Open text box when In-prson radio check
	$(".task-to-be-done").change(function() 
	{
   		$("#in_person").slideUp();
		if ($("#in_person_rdo").is(':checked'))
            $("#in_person").slideDown();
	}); 
	
	// Open text box when "By a certain day" radio check
	$(".due-date").change(function() 
	{
		if($(this).val() == "Today" )
		{
			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth()+1; 
			var yyyy = today.getFullYear();
			today = yyyy+'-'+mm+'-'+dd;
			$("input[name='due_date']").val(today);
			$(".certain__day").slideUp();
		}
		else if($(this).val() == "Week")
		{
		 	var today = new Date();
    		var newdate = new Date(today);
			newdate.setDate(newdate.getDate() + 7);
    		var dd = newdate.getDate();
			var mm = newdate.getMonth()+1; 
			var yyyy = newdate.getFullYear();
		 	today = yyyy+'-'+mm+'-'+dd;
			console.log(today);	
			$("input[name='due_date']").val(today);
			$(".certain__day").slideUp();
		}
		else 
		{
			$(".certain__day").slideUp();
			if ($("#in_due_date_certainDay").is(':checked'))
				$(".certain__day").slideDown();
		}
	});
	
	//disabled location
	$(".cityDisplay input").attr("disabled","disabled");
	// Open text box when location type radio check
	$(".locatio-rdo").change(function() 
	{
   		$(".cityDisplay input").attr("disabled","disabled");
		$(".cityDisplay span").addClass("hide-range");
		$(".cityDisplay").css("opacity", "0.7");
		if ($("#locationCity").is(':checked')) 
		{
            $(".cityDisplay input").removeAttr("disabled");
            $(".cityDisplay").css("opacity", "1");
			$(".cityDisplay span").removeClass("hide-range");
		}	
	});
	
	// Open text box when location type radio check( Mobile filter)
	$(".location-rdo").change(function() 
	{ 
		$(".cityDisplay input").attr("disabled","disabled");
		$(".cityDisplay span").addClass("hide-range");
		if ($("#locationCityMob").is(':checked')) 
		{
			$(".cityDisplay input").removeAttr("disabled");
            $(".cityDisplay").css("opacity", "1");
			$(".cityDisplay span").removeClass("hide-range");
		}	
	});
	
	$("#applyFilter").click(function()
	{
		if ($(this).text() === "Clear Filter")
			clearSelectedfilter();
		
		// if sort by radio select
		if ($("#allType1").is(':checked') || $("#selected1").is(':checked') || $("#pending1").is(':checked') || $("#locationOnlineMob").is(':checked'))
            clearMobFilter();
		
		// if  city radio has value
		if ($("#locationTextBoxMob").val() !== null && $("#locationTextBoxMob").val() !== '' &&$("#locationCityMob").is(':checked'))  
            clearMobFilter();
		
		// Budget has min or max value
		if (($(".mobBmax").val() !== null && $(".mobBmax").val() !== '') || ($(".mobBmin").val() !== null && $(".mobBmin").val() !== ''))  
			clearMobFilter();
		
		if($("#ByTaskType").prop('selectedIndex') !== 0)
			clearMobFilter();
	});	
				
	$("#clsMobFilter").click(function(){
		clearSelectedfilter();
	});
	
	var wordLen = 3, len; // Maximum word length
	$('.taskDesc').keydown(function(event) {	
		len = $('.taskDesc').val().split(/[\s]+/);
		if (len.length > wordLen) {
			if ( event.keyCode === 46 || event.keyCode === 8 ) {// Allow backspace and delete buttons
		    } else if (event.keyCode < 48 || event.keyCode > 57 ) {//all other buttons
		    	event.preventDefault();
		    }
		}
		console.log(len.length + " words are typed out of an available " + wordLen);	
	});
	
	// progress bar
	$(".post-job").click(function(){		
		
		var id = $(this).attr( "data-id" );
		$("#"+id).children("i").css("background", "#000");
		
		if( $("#home_li").hasClass("active"))
			$("#progress-bar").css("width", "30%");
		else if( $("#profile_li").hasClass("active"))
			$("#progress-bar").css("width", "60%");
		else		
			$("#progress-bar").css("width", "100%");
	});	
});


$('#input-4').on("keyup",function(event) 
{
	var min = 10;
	var max = 50;
	var title =$('#input-4').val().length; 
	
    if(title.value.length >= max && title.value.length <= min )
	{
    	$('#input-4').blur();
    	return false;
	}
        
//	if (title > max) 
//	{ 			
//		if ( event.keyCode === 46 || event.keyCode === 8 ) 
//		{// Allow backspace and delete buttons
//		} 
//		else if (event.keyCode < 48 || event.keyCode > 57 ) 
//		{//all other buttons
//			//    	event.preventDefault();
//			$('#input-4').blur();
//			return false;
//		}
//	}
//	console.log(title);
});

// function for clear mobile applied filters
function clearMobFilter()
{
	$("#opnFilter").children("i").hide();
	$("#clsMobFilter").css("display", "block");
	$("#applyFilter").text("Clear Filter");
//	$("#applyFilter").attr("id", "clearFilter");
}

function clearSelectedfilter()
{
	$("input[name='selectType']"). prop("checked", false);
	$("input[name='sortByCity']"). prop("checked", false);
	$("#locationTextBoxMob").slideUp();
	$("#locationTextBoxMob").val("");
	$(".mobBmax").val("");
	$(".mobBmin").val("");
	$("#ByTaskType").prop("selectedIndex", 0);
	$("#clsMobFilter").css("display", "none");
	$("#opnFilter").children("i").show();
	$("#applyFilter").text("Apply Filter");
}

//location validation 
$('#message_li').click(function(){ });

//budget-validation

$("#no_person_required,#budget_per_person,#job_expected_hours").on("keyup keypress",function(){
	var person = $('#no_person_required').val();
	$('#count_person').text(person+" Person");
	valid();
});

$('.find_task_job').on("change",function(){
	valid();
	budget_calculate();
});

$("#find_task_job").click(function(){
	valid();
	budget_calculate();
});

function calculate()
{
	var budget = $('#budget_per_person').val();
	var hour = 1;
	var person = $('#no_person_required').val();

	if($('#txtHour').is(':visible'))
		hour = $('#job_expected_hours').val();

	tot = parseInt(budget) * parseInt(hour) * parseInt(person); 
	if($("#fixedPrice").is(":checked"))
		tot = parseInt(budget)  * parseInt(person);

	$('#count_person').text(person+" Person");
	$('#total_budget').val(tot);	
}

$('#no_person_required,#budget_per_person,#job_expected_hours').on("keyup",  function(){ budget_calculate(); });

function budget_calculate()
{
	if(tot < 10){
		$('#total_budget').addClass("error_border");
		$('#budget').text("Oops.Minimum Task Value is $10").fadeOut(6000);
		return false;
	}
	else{
		$('#total_budget').removeClass("error_border");
		$('.post-jobs-task').removeClass("error_border");
	}
}
function valid()
{
	var budget = $('#budget_per_person').val();
	var hour = $('#job_expected_hours').val();
	var person = $('#no_person_required').val();
	
	if(budget == "")
	{
		$('#budget_per_person').addClass("error_border");
		$('#budget').text("Please Enter Budget Per Person").fadeOut(6000);
		return false;
	}
	
	if(person == "")
	{
		$('#no_person_required').addClass("error_border");
		$('#budget').text("Please Enter Number of People Required").fadeOut(6000);
		return false;
	}
	
	if(hour == "" && $('#txtHour').is(':visible') && $('#perHour').is(":checked"))
	{
		$('#job_expected_hours').addClass("error_border");
		$('#budget').text("Please Enter Hours").fadeOut(6000);
		return false;
	}
	calculate();
}

function isNumber(evt) 
{
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode > 31 && (charCode < 48 || charCode > 57)) {
		return false;
	}
	return true;
}			