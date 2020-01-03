
<div class="login clearfix">
	<div class="container">
		<div class="col-md-6 login-left">
			<img class="img-responsive" src="images/login.png" alt="login" />
		</div>
		<div class="col-md-6 login-right py-5">
			<div class="text-center pb-5 pt-4 mt-1">
				<img class="img-responsive mx-auto" src="images/logo.png" alt="logo" />
			</div>
			<div class="text-center">
				<h4 class="mb-3"> <strong>Verify Your Email</strong></h4>
				
				<form method="post" action="forgot-password">
				
				<div class="clearfix py-5 text-center">
					<div class="form-group mx-sm-5 px-md-5">
						<input class="form-control login-txt" name="username" type="email" placeholder="Email">
						<span class="error_msg"><?php echo (@$error)?form_error('username'):''; ?> </span> 						
					</div>
					<div class="clearfix mx-sm-5 px-md-5 ">					
					<span class="error_msg"><?php echo (@$response)? $response :''; ?> </span> 
					</div>
					<input class="btn btn-apply text-primary px-5 mt-4 py-2" type="submit" value="Resend"> 
				</div>
				</form>
				<div class="clearfix mx-sm-5 px-md-5">					
					<h5 class="text-muted">Your OTP mail has been sent on your Email. Please Check Your email</h5>
				</div>
				
			</div>
		</div>
	</div>
</div>
