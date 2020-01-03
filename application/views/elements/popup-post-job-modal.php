<!-- Post job modal -->
<div class="modal fade postjob" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header text-center theam-linear-bg">
				<button type="button" class="close d-block" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title w-100 position-absolute" id="myModalLabel">Post Job</h4>
			</div>
			<div class="modal-body position-unset">
				<div>
					<ul class="nav nav-tabs text-center" role="tablist">
						<li role="presentation" class="active" id="home_li">
							<a href="#home1" aria-controls="home" role="tab"> <i class="fas fa-briefcase mB5"></i><br>Details </a>
						</li>
						<li role="presentation" id="profile_li">
							<a href="#profile1" aria-controls="profile" role="tab"> <i class="glyphicon glyphicon-map-marker mB5"></i><br>Location </a>
						</li>
						<li role="presentation" id="message_li">
							<a href="#messages1" aria-controls="messages" role="tab"> <i class="fas fa-dollar-sign mB5"></i><br>Budget </a>
						</li>
						<div class="progress theam-linear-bg">
							<div class="progress-bar" id="progress-bar" role="progressbar"
								aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"
								style="width: 30%;">
								<span class="sr-only">30% Complete</span>
							</div>
						</div>
					</ul>

					<form id="find-post-task" onSubmit="return false;">
						<input type="hidden" name="job_post_type" value="POST">
						<div class="tab-content pB15 mT-10">
							<div role="tabpanel" class="tab-pane fade in active" id="home">
								<div class="col-md-12">
									<span class="input input--hoshi"> 
										<label for="job title" class="form-label">Task title <small>(10-50 words)*</small></label>
										<input class="form-control" name="job_title" placeholder="e.g. I need a plumber to fix a Tap" type="text" id="input-4" maxlength="51"/> 
									</span>
									<span id="rchars_10" class="success_msg">10</span> <span class="success_msg">Character(s) Remaining</span>
									<div class="error_msg d-none" id="task-title">add more 10 words</div>
								</div>
								<div class="col-md-12 mt-3">
									<span class="input input--hoshi"> 
										<label for="job title" class="form-label">Describe your task <small>(20-300 words)*</small></label>
										<textarea class="form-control taskDesc" name="job_description" rows="5" maxlength="300" id="input-45" placeholder="e.g. Kitchen Tap needs to be replaced. I m attaching an image of the new tap to be fitted and old leaking tap. Need to get this done. At the earliest within next 2 hours."></textarea>
									</span>
    								<span id="rchars_20" class="success_msg">20</span> <span class="success_msg">Character(s) Remaining</span>
									<div class="error_msg d-none" id="detail"></div>
								</div>
								
								<div class="col-md-12 mT10	">
									<p class="mB10">Add images / documents / files (if any)</p>
									<div class="image-upload">
										<label for="PostTask"> 
											<img src="<?php echo asset_url('images/add_image.png')?>" class="img-responsive" alt="Attachment" />
										</label> 
										<input id="PostTask" type="file" name="file" />
									</div>
								</div>

								<div class="button-row w-100 modal-controls">
									<div class="row m-0 position-relative w-100">
										<div class="col-md-12 col-xs-12 p-0">
											<a href="#profile" class="text-dark custom-next details-post-job" aria-controls="profile" role="tab" data-toggle="tab" data-id="profile_li">
												<button type="button" class="nextbtn  position-unset p-0 radio-focus" data-id="profile_li">
													<i class="fas fa-arrow-right mB5"></i><br>
												</button>
											</a>
										</div>
									</div>
								</div>
							</div>

							<div role="tabpanel" class="tab-pane fade" id="profile">
								<div class="col-md-12">
									<label for="radio" class="form-label">Task to be done:</label>
								</div>
								<div class="row m-0">
									<div class="clearfix position-relative">
										<div class="col-xs-6">
											<div class="btn-group radio-focus" data-toggle="buttons">
												<label class="btn btn-50 radio-focus"> 
													<input type="radio" name="to_be_complete" class="task-to-be-done" id="in_person_rdo" data-id="1" value="IN_PERSON" checked><span> In Person</span>
													<i class="far fa-circle f-none"></i>
													<i class="far fa-dot-circle f-none"></i>
												</label>
											</div>
										</div>
										<div class="col-xs-6">
											<div class="btn-group radio-focus" data-toggle="buttons">
												<label class="btn btn-50 radio-focus"> 
													<input type="radio" name="to_be_complete" class="task-to-be-done" id="in_online_rdo" data-id="2" value="ONLINE"><span> Online</span>
													<i class="far fa-circle f-none"></i>
													<i class="far fa-dot-circle f-none"></i>
												</label>
											</div>
										</div>

										<div class="col-sm-12 p-0 mb-3 px-3 my-3">
											<input type="text" class="form-control" name="city_id" id="in_person" placeholder="Task City/ Suburb" value="1">
										</div>
										<div class="pl-4 mt-3 error_msg" id="city_error"></div>
									</div>
								</div>
								<div class="col-md-12">
									<label class="form-label">
										<strong class="form-label">Due Date</strong>
									</label>
									<div class="btn-group btn-group-vertical position-relative" data-toggle="buttons">
										<label class="btn active radio-focus"> 
											<input type="radio" name="due_date_userfriendly" class="due-date" id="in_due_date_today" data-id="1" value="Today">
											<span> Today</span>
											<i class="far fa-circle"></i>
											<i class="far fa-dot-circle"></i>
										</label> 
										<label class="btn radio-focus"> 
											<input type="radio" name="due_date_userfriendly" class="due-date" id="in_due_date_week" data-id="2" value="Week">
											<span> Within 1 week</span>
											<i class="far fa-circle"></i>
											<i class="far fa-dot-circle"></i>
										</label> 
										<label class="btn radio-focus"> 
											<input type="radio" name="due_date_userfriendly" class="due-date" id="in_due_date_certainDay" value="certain" data-id="3">
											<span> By a certain day</span>
											<i class="far fa-circle"></i>
											<i class="far fa-dot-circle"></i>
										</label>
									</div>
									<input type="text" name="due_date" id="datepicker"  class="form-control certain__day mt-3">
									<div class="pl-4 mt-3 error_msg" id="location"></div>
								</div>
								<div class="button-row w-100 modal-controls">
									<div class="row m-0 w-100">
										<div class="col-md-6 col-xs-6 p-0">
											<a href="#home" class="text-dark custom-back" aria-controls="profile" role="tab" data-toggle="tab">
												<button type="button" class="backbtn post-job position-unset p-0 radio-focus" data-id="home_li">
													<i class="fas fa-arrow-left mB5"></i><br>
												</button>
											</a>
											
										</div>
										
										<div class="col-md-6 col-xs-6 p-0">
											<a href="#messages" class="text-dark custom-next location-post-job" aria-controls="profile" role="tab" data-toggle="tab" data-id="message_li">
												<button type="button" class="nextbtn  position-unset p-0 radio-focus" data-id="message_li">
													<i class="fas fa-arrow-right mB5"></i><br>
												</button>
											</a>
										</div>
									</div>
								</div>
							</div>

							<div role="tabpanel" class="tab-pane fade" id="messages">
								<div class="col-md-12">
									<label for="" class="form-label">Estimated Budget</label>
									<p class="text-muted m-0" style="font-size: 10px;">This estimate serves as a guide for Taskbar to send offers</p>
								</div>
								<div class="col-md-12">
									<div class="btn-group btn-group-vertical d-flex" data-toggle="buttons">
										<label class="btn active btn-50 radio-focus h-unset"> 
											<input type="radio" class="find_task_job" name="job_budget_type" checked id="perHour" value="HOURLY">
											<span> Per hour</span>
											<i class="far fa-circle f-none"></i>
											<i class="far fa-dot-circle f-none"></i>
										</label> 
											<label class="btn btn-50 radio-focus h-unset"> 
											<input type="radio" name="job_budget_type" id="fixedPrice" value="FIXED" class="find_task_job">
											<span> Fixed price</span>
											<i class="far fa-circle f-none"></i>
											<i class="far fa-dot-circle f-none"></i>
										</label>
									</div>
								</div>
								<div class="col-md-12 mT15">
									<span class="input input--hoshi"> 
										<input class="form-control boldPlaceholder post-jobs-task" type="text" id="budget_per_person"  name="budget_per_person" placeholder="Estimated Price in $ per person" onkeypress="return isNumber(event)" />
									</span>
								</div>
								<div class="col-md-12 mT15" id="txtHour">
									<span class="input input--hoshi"> 
										<input class="form-control boldPlaceholder post-jobs-task" type="text" name="job_expected_hours" id="job_expected_hours" placeholder="Hours" onkeypress="return isNumber(event)" />
									</span>
								</div>
								<div class="col-md-12 mT15">
									<span class="input input--hoshi"> 
										<input class="form-control boldPlaceholder post-jobs-task" name="no_person_required" type="text" id="no_person_required" placeholder="Person required" value="1" onkeypress="return isNumber(event)"/>
									</span>
								</div>
								<div class="col-md-12 mT15">
									<span class="input input--hoshi"> 
										<input class="form-control post-jobs-task" readonly type="text" name="total_budget" id="total_budget" placeholder="Estimated Total Budget" onkeypress="return isNumber(event)" />
										<span class="count_person" id="count_person"> 1 person</span>
									</span>
									<div class="mt-3 error_msg" id="budget"></div>
									<div class="mt-3 error_msg" id="error"></div>
									<div class="mt-3 success_msg" id="success"></div>
								</div>
	
								<div class="button-row w-100 modal-controls">
									<div class="row m-0 w-100">
										<div class="col-md-6 col-xs-6 p-0">
											<a href="#profile" class="text-dark custom-back" aria-controls="profile" role="tab" data-toggle="tab">
												<button type="button" class="backbtn post-job position-unset p-0 radio-focus" data-id="profile_li">
													<i class="fas fa-arrow-left mB5"></i><br>
												</button>
											</a>
										</div>
										<div class="col-md-6 col-xs-6 p-0">
											<a href="#messages" class="text-dark custom-next" aria-controls="profile" role="tab" data-toggle="tab">
												<button type="submit" class="nextbtn post-job position-unset p-0 radio-focus find_task_job" data-id="message_li" id="find_task_job">
													<i class="fas fa-check mB5"></i><br>
												</button>
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$("#find_task_job").click(function(){
		
 	var formData = $('#find-post-task').serialize();
	
	$.ajax({
		type: 'POST',
		url: base_url+'my_task/jobPostData',
		data: { data:formData },
//		contentType: false,
//		cache: false,
//		processData:false,
//		dataType : "json",
//		enctype: 'multipart/form-data',
		beforeSend: function(){
			$(".error_msg").text( "" );
		},
		success: function( result ){
			console.log(result);
			var data = $.parseJSON( result );
			if( data.status == false )
				$("#error").text( data.message );
			else
			{
				$("#success").text( data.message );
//				$(".btn-"+job_id).remove();
			}
		} 
	});
		
}); 

</script>
