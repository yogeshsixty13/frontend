
<div class="login clearfix">
	<div class="container">
		<div class="col-md-6 login-left">
			<img class="img-responsive" src="images/login.png" alt="login" />
		</div>
		<div class="col-md-6 login-right py-5">
			<div class="pb-5 pt-4 mt-1">
				<img class="img-responsive mx-auto" src="images/logo.png" alt="logo" />
			</div>
			<div class="">
				<h4 class="mb-3"> <strong>Reset Your Password</strong></h4>
				<div class="clearfix py-5 text-center">
				<form action="<?php echo base_url('change-password')?>" method="post">
					<div class="form-group mx-sm-5 px-md-5">
						<input class="form-control login-txt" name="old_password" type="text" placeholder="Enter Old Password">
						<span class="error_msg text-left"><?php echo (@$error)?form_error('old_password'):''; ?> </span> 			
					</div>
					
					<div class="form-group mx-sm-5 px-md-5">
						<input class="form-control login-txt" name="new_password" type="text" placeholder="Enter New Password">
						<span class="error_msg text-left"><?php echo (@$error)?form_error('new_password'):''; ?> </span> 			
					</div>
					
					<div class="form-group mx-sm-5 px-md-5">
						<input class="form-control login-txt" name="c_password" type="text" placeholder="Confirm New Password">
						<span class="error_msg text-left"><?php echo (@$error)?form_error('c_password'):''; ?> </span> 			
					</div>
					<div class="clearfix mx-sm-5 px-md-5 ">					
						<span class="error_msg text-left"><?php echo (@$response)? $response :''; ?> </span>
					</div>
					<input class="btn btn-upload px-5 mt-4" type="submit" value="Chnage Password">
				</form>
				</div>
				
			
				
				<div class="mt-5 pt-5">
					
				</div>
			</div>
		</div>
	</div>
</div>

      