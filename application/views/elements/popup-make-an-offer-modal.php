<!-- Make an offer -->
<div class="modal fade timeline-modal" id="makeOffer" role="dialog">
	<div class="modal-dialog modal-sm">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title color-theam text-center">Make an Offer</h4>
			</div>
			<div class="modal-body">
				<div class="row clearfix px-4">
					<div class="text-center">
						<form id="popup_make_an_offer_form" onsubmit="return false;">
							<input type="hidden" name="job_id" value="" class="job_id">
    						<div class="form-group job_bid1 mt-3">
    							<h6 class="text-left">
    								<strong>Your Offer Value</strong>
    							</h6>
    							<input type="text" name="bid_amount" id="bid_amount" class="bid_amount	form-control" placeholder="Enter Offer Value" />
    							<h6 class="text-left">
    								<strong>Your Message</strong>
    							</h6>
    							<textarea rows="3" name="bid_messege" id="bid_messege" class="form-control" placeholder="Enter Your Message"></textarea>
    							
    							<div class="col-md-12 mt-3">
    								<span class="error_msg error" id="make_an_offer_error"></span>
    								<span class="success_msg error" id="make_an_offer_success"></span>
    							</div>
    							<input type="submit" class="btn btn-upload mt-3" id="submit_bit" value="Submit Bid">
    						</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>