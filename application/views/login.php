
<div class="login clearfix">
	<div class="container">
		<div class="col-md-6 login-left">
			<img class="img-responsive" src="<?php echo asset_url('images/login.png') ?>" alt="login" />
		</div>
		<div class="col-md-6 login-right py-5">
			<div class="text-center py-5 mb-5 mt-4 ">
				<img class="img-responsive mx-auto mt-5" src="<?php echo asset_url('images/logo.png'); ?>" alt="logo" />
			</div>
			<div class="text-center pb-5">
				<h4 class="mb-5"> <strong>Login To WOW Tasks</strong></h4>
			 <form method="post" action="<?php echo base_url('sign-in')?>">
				<div class="form-group mx-sm-5 px-md-5">
					<input class="form-control login-txt" name="username" type="text" placeholder="username">
					<span class="error_msg"><?php echo (@$error)?form_error('username'):''; ?> </span> 
						
				</div>
				
				<div class="form-group mx-sm-5 px-md-5">
					<input class="form-control login-txt" name="password" type="Password" placeholder="Password">
					<span class="error_msg"><?php echo (@$error)?form_error('password'):''; ?> </span>  
				</div>
				
				<div class="clearfix mx-sm-5 px-md-5">
					<h5 class="pull-right"><a href="<?php echo base_url('forgot-password')?>" class="text-primary">Forgot Password ?</a></h5>
				</div>
				
				<div class="clearfix mx-sm-5 px-md-5 ">					
					<h5 class="pull-left">Don't have an account yet? <a href="<?php echo base_url('register')?>" class="text-primary">Register here.</a></h5><br>
				</div>
				<div class="clearfix mx-sm-5 px-md-5 ">					
					<span class="error_msg"><?php echo (@$response)? $response :''; ?> </span>
				</div>
				<div class="clearfix mx-sm-5 px-md-5 ">					
					<input class="btn btn-upload px-5 pull-right mb-5 mt-4" type="submit" value="LOGIN"> 
				</div>
			</form>

			</div>
		</div>
	</div>
</div>  