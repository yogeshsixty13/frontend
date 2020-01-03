<div class="modal fade timeline-modal" id="issue" role="dialog">
	<div class="modal-dialog modal-sm">
		<!-- Modal content-->
		<div class="modal-content">
			<form id="issue_form" onsubmit="return false;">
    			<input type="hidden" name="job_id" value="" class="job_id">
    			<div class="modal-header">
    				<button type="button" class="close" data-dismiss="modal">&times;</button>
    				<h4 class="modal-title color-theam text-center">Report An Issue</h4>
    			</div>
    			<div class="modal-body m-0">
    				<div class="other-resone-div1">
    					<p class="mt-3">Mention Your Issue</p>
    					<div class="form-group mt-1 mb-0">
    						<input type="text" class="form-control issue_comments" name="issue_comments" id="issue_comments" placeholder="Wrirte Issue Here...">
    					</div>
    				</div>
    			</div>
    			<div class="col-md-12 mb-3">
					<span class="error_msg error" id="issue_error"></span>
					<span class="success_msg error" id="issue_success"></span>
				</div>
    			<div class="modal-footer clearfix">
    				<a href="#" type="button" class="btn btn-cancel pull-left" data-dismiss="modal">cancel</a> 
    				<input type="submit" class="btn btn-upload pull-right" id="submit_issue" value="Submit Issue">
    			</div>
			</form>
		</div>
	</div>
</div>