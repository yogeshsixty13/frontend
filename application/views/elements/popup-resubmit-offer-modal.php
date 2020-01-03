<div class="modal fade timeline-modal" id="resubmitOffer" role="dialog">
	<div class="modal-dialog modal-sm">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title color-theam text-center">Re-submit Offer</h4>
			</div>
			<form onsubmit="return false;" id="re_submit_bid_offer">
				<input type="hidden" value="" name="bid_id" id="bid_id" class="bid_id">
				<div class="modal-body">
    				<p class="mt-5">Your Offer Value</p>
    				<div class="input-group doller-addon">
    					<input type="text" name="bid_amount" id="r_bid_amount" class="r_bid_amount form-control" value="" placeholder="Update Amount">
    					<div class="input-group-btn">
    						<button class="btn btn-link">$</button>
    					</div>
    				</div>
    				<div class="other-resone-div1">
    					<p class="mt-5">Your Message</p>
    					<div class="form-group mt-3">
    						<input type="text" name="bid_messege" id="r_bid_messege" class="form-control bid_messege" placeholder="Write Here...">
    					</div>
    				</div>
    			</div>
    			<div class="modal-footer clearfix">
    				<a href="#" type="button" class="btn btn-cancel pull-left" data-dismiss="modal">Any Query ?</a>
    				<input type="submit" class="btn btn-upload pull-right" id="re_submit_bit" value="Re Submit Offer">
    			</div>
			</form>
		</div>
	</div>
</div>