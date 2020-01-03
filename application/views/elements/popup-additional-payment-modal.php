<div class="modal fade timeline-modal" id="additional-payment" role="dialog">
	<div class="modal-dialog modal-sm">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title color-theam text-center">Increase Payment ?</h4>
			</div>
			<form id="additional_payment_form" onsubmit="return false;">
    			<input type="hidden" name="contract_id" value="" class="contract_id">
    			<div class="modal-body">
    				<p class="text-center">Task involves more stuff. Hope you have informed the Task Poster on message about this request.</p>
    				<div class="clearfix color-theam">
    					<p class="col-xs-8">Current Task</p>
    					<div class="col-xs-4"> <p class="text-right">$<span class="old_amount">1</span></p> </div>
    				</div>
    				<p class="mt-5">Enter Additional Payment</p>
    				<div class="form-group mt-3">
    					<input type="text" class="form-control" placeholder="Ex. 1">
    				</div>
    			</div>
    			<div class="modal-footer clearfix">
    				<a href="#" type="button" class="btn btn-cancel pull-left" data-dismiss="modal">Cancel</a> 
    				<input type="submit" class="btn btn-upload pull-right" id="submit_additional_payment" value="Procced">
    			</div>
			</form>
		</div>
	</div>
</div>