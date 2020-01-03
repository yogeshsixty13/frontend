<div class="modal fade timeline-modal" id="cancel-task" role="dialog">
	<div class="modal-dialog modal-sm">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title color-theam text-center">Select reason for cancel this job</h4>
			</div>
			<form id="cancel_task_form" onsubmit="return false;">
				<input type="hidden" name="contract_id" value="" class="contract_id">
    			<div class="modal-body">
    				<div class="radio">
    					<input type="radio" id="cancel1" class="cancel-resone" name="reason" value="I do not want to get the task done anymore"> 
    					<label for="cancel1">I do not want to get the task done anymore</label>
    				</div>
    				<div class="radio">
    					<input type="radio" id="cancel2" class="cancel-resone" name="reason" value="Scope of task is now changed"> 
    					<label for="cancel2">Scope of task is now changed</label>
    				</div>
    				<div class="radio">
    					<input type="radio" id="cancel3" class="cancel-resone" name="reason" value="Bidder is not responding to my messages or didn't arrive at the given time"> 
    					<label for="cancel3">Bidder is not responding to my messages or didn't arrive at the given time</label>
    				</div>
    				<div class="radio">
    					<input type="radio" id="cancel4" class="cancel-resone" name="comments" value="Bidder doesn't want to proceed with the task"> 
    					<label for="cancel4">Bidder doesn't want to proceed with the task</label>
    				</div>
    				<div class="radio">
    					<input type="radio" id="other" class="cancel-resone" name="reason" value="Other"> 
    					<label for="other">Other</label>
    				</div>
    				<div class="other-resone-div">
    					<p class="mt-5">Enter Reason</p>
    					<div class="form-group mt-3">
    						<input type="text" class="form-control" placeholder="Ex. Rejected">
    					</div>
    				</div>
    			</div>
    			<div class="modal-footer clearfix">
    				<a href="#" type="button" class="btn btn-cancel pull-left" data-dismiss="modal">Cancel</a> 
    				<input type="submit" class="btn btn-upload pull-right" id="submit_cancel_task" value="Procced">
    			</div>
			</form>
		</div>
	</div>
</div>