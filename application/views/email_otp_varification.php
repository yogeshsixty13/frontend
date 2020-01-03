<div class="mb-0 safari_only">
    <div class="login clearfix">
    	<div class="container">
    		<div class="col-md-6 login-left">
    			<img class="img-responsive" src="<?php echo asset_url('images/login.png')?>" alt="login" />
    		</div>
    		<div class="col-md-6 login-right py-5">
    			<div class="text-center pb-5 pt-4 mt-1">
    				<img class="img-responsive mx-auto" src="<?php echo asset_url('images/logo.png')?>" alt="logo" />
    			</div>
    			<div class="text-center">
    				<h4 class="mb-3"> <strong>Verify Your Email</strong></h4>
					<form action="<?php echo base_url('otp-varification')?>" method="post">
        				<div class="clearfix py-5 text-center">
        					<div class="form-group mx-sm-5 px-md-5">
        						<input class="form-control login-txt" type="number" name="otp" placeholder="Verification Code">
        						<input class="form-control login-txt" type="hidden" name="registration_key" value="<?php echo $registration_key;?>"> 						
        					</div>
        					<span class="error_msg"><?php echo (@$error)?form_error('otp'):''; ?> </span>
        					<input class="btn btn-upload px-5 mt-4" type="submit" value="Proceed"><br> 
        					<!-- <input class="btn btn-apply text-primary px-5 mt-4 py-2" type="submit" value="Resend"> --> 
        				</div>
        				<div class="form-group mx-sm-5 px-md-5">
        					<span class="error_msg" id="common_message"><?php echo ( @$error['common_message'] ) ? @$error['common_message'] : ''; ?></span> 
        				</div>
    				</form>
    				<div class="clearfix mx-sm-5 px-md-5">					
    					<h5 class="text-muted">Information Submitted Success, <br> You have recieved an OTP proceed with verifcation code to complete registration</h5>
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
</div>    