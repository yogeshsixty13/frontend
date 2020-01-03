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
				<div class="tab-content m-0">
					<div id="receivePayment" class="tab-pane fade in active position-relative">
						<p class="mt-5 px-4 text-justify">Payments for Completed tasks shall be transferred via Interac e-Transfers to your Registerd Email id : 
						<span class="color-theam"><?php echo $this->session->userdata('email');?></span>. to the Security Question shall be e-mailed to you and will also be avilable in your Payment Records Window.</p>
					</div>
					<div id="makePayment" class="tab-pane fade position-relative mt-4">
						<form onsubmit="return false;" action="<?php echo base_url('stripe');?>" role="form" method="post" class="require-validation" data-cc-on-file="false" data-stripe-publishable-key="<?php echo $this->config->item('stripe_key') ?>" id="payment-form">
                            <div class='form-row row'>
                                <div class='col-xs-12 form-group required'>
                                    <label class='control-label'>Name on Card</label> 
                                    <input class='form-control' size='4' type='text'>
                                </div>
                            </div>
         
                            <div class='form-row row'>
                                <div class='col-xs-12 form-group card required'>
                                    <label class='control-label'>Card Number</label> <input autocomplete='off' class='form-control card-number' size='20' type='text'>
                                </div>
                            </div>
          
                            <div class='form-row row'>
                                <div class='col-xs-12 col-md-4 form-group cvc required'>
                                    <label class='control-label'>CVC</label> <input autocomplete='off' class='form-control card-cvc' placeholder='ex. 311' size='4' type='text'>
                                </div>
                                <div class='col-xs-12 col-md-4 form-group expiration required'>
                                    <label class='control-label'>Exp. Month</label> <input class='form-control card-expiry-month' placeholder='MM' size='2' type='text'>
                                </div>
                                <div class='col-xs-12 col-md-4 form-group expiration required'>
                                    <label class='control-label'>Exp. Year</label> <input class='form-control card-expiry-year' placeholder='YYYY' size='4' type='text'>
                                </div>
                            </div>
          
                            <div class='form-row row'>
                                <div class='col-md-12 error form-group hide'>
                                    <div class='alert-danger alert'>Please correct the errors and try again.</div>
                                </div>
                            </div>
          
                            <div class="row">
                                <div class="col-xs-12 text-center">
                                    <button class="btn btn-primary btn-lg btn-block" type="submit" id="add_card">Add Card</button>
                                </div>
                            </div>
                        </form>
						<p class="mt-5 px-4 text-center">By Providing You Credit card details, you agree to Stripe T&amp;C.</p>
						<p class="mt-3 px-4 text-center">Be Safe Your credit card is not charged untill You authorize.</p>
					</div>
				</div>
			</div>
			<div class="modal-footer clearfix">
<!--          <a href="#" type="button" class="btn btn-upload pull-right" data-dismiss="modal">Choose</a>          -->
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
     
<script type="text/javascript">
$(function() 
{
	var $form = $(".require-validation");
	$('form.require-validation').bind('submit', function(e) 
	{
    	var $form         = $(".require-validation"),
        inputSelector = ['input[type=email]', 'input[type=password]',
                         'input[type=text]', 'input[type=file]',
                         'textarea'].join(', '),
        $inputs       = $form.find('.required').find(inputSelector),
        $errorMessage = $form.find('div.error'),
        valid         = true;
        $errorMessage.addClass('hide');
 
        $('.has-error').removeClass('has-error');
		$inputs.each(function(i, el) 
		{
			var $input = $(el);
      		if ($input.val() === '') 
      		{
            	$input.parent().addClass('has-error');
            	$errorMessage.removeClass('hide');
        		e.preventDefault();
      		}
    	});
     
        if (!$form.data('cc-on-file')) 
        {
			e.preventDefault();
			Stripe.setPublishableKey($form.data('stripe-publishable-key'));
			Stripe.createToken({
                number: $('.card-number').val(),
                cvc: $('.card-cvc').val(),
                exp_month: $('.card-expiry-month').val(),
                exp_year: $('.card-expiry-year').val()
			}, stripeResponseHandler);
		}
    
	});
  
	function stripeResponseHandler(status, response) 
	{
    	if (response.error) 
        $('.error').removeClass('hide').find('.alert').text(response.error.message);
		else 
		{
            var token = response['id'];
            $form.find('input[type=text]').empty();
            $form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
            $form.get(0).submit();
    	}
	}
});
</script>