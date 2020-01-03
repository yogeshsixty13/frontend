<div class="mb-0">
    <div class="login clearfix">
    	<div class="container">
    		<div class="col-md-6 login-left"> <img class="img-responsive" src="<?php echo asset_url('images/login.png');?>" alt="login" /> </div>
    		<div class="col-md-6 login-right py-5">
    			<div class="text-center  pb-4"> <img class="img-responsive mx-auto" src="<?php echo asset_url('images/logo.png')?>" alt="logo" /> </div>
    			<div class="text-center">
    				<h4 class="mb-3"> <strong>Register To WOW Tasks!</strong></h4>
    				<form action="<?php echo base_url('register')?>" method="post">
        				<div class="clearfix mx-sm-5 px-md-5">
        					<div class="form-group col-sm-6 pl-sm-0 px-0 pr-sm-2">
        						<input class="form-control login-txt" type="text" name="first_name" id="first_name" placeholder="First Name" value="<?php echo set_value('first_name')?>"> 
        					</div>
        					<div class="form-group col-sm-6 pr-sm-0 px-0 pl-sm-2">
        						<input class="form-control login-txt" type="text" name="last_name" id="last_name" placeholder="Last Name" value="<?php echo set_value('last_name')?>"> 
        					</div>
							<span class="error_msg"><?php echo (@$error)?form_error('first_name'):''; ?> </span>
							<span class="error_msg"><?php echo (@$error)?form_error('last_name'):''; ?> </span>
        				</div>
        				<div class="form-group mx-sm-5 px-md-5">
        					<input class="form-control login-txt" type="email" name="username" id="username" placeholder="Email" value="<?php echo set_value('username')?>">
        					<span class="error_msg"><?php echo (@$error)?form_error('username'):''; ?> </span> 
        				</div>
        				
        				<div class="form-group mx-sm-5 px-md-5">
        					<input class="form-control login-txt" type="Password" name="password" id="password" placeholder="Password" value="<?php echo set_value('password')?>">
        					<span class="error_msg"><?php echo (@$error)?form_error('password'):''; ?> </span> 
        				</div>
        				
        				<div class="form-group mx-sm-5 px-md-5">
        					<input class="form-control login-txt" type="Password" name="c_password" id="c_password" placeholder="Confirm Password" value="<?php echo set_value('c_password')?>">
        					<span class="error_msg"><?php echo (@$error)?form_error('c_password'):''; ?> </span> 
        				</div>
        				
        				<div class="form-group mx-sm-5 px-md-5">
        					<input type="text" list="city" class="form-control searchCity mb-3" name="search_city" placeholder="Search City">
            				<datalist id="city">
            					<?php 
            					$cityArr = getCityList();
            					if( $cityArr->status == true )
            					{
            					    if( !isEmptyArr( $cityArr->data ) )
            					    {
            					        foreach ( $cityArr->data as $city )
            					        {
            					            echo '<option value="'.$city->city_id.'">'.$city->city.', '.$city->state_name.', '.$city->country_name.'</option>';
            					        }
            					    }
            					    else 
            					        echo '<option value="">No City Available</option>';
            					}
            					else
            					    echo '<option value="">Invalid token header.</option>';
            					?>
            				</datalist>
            				
        					<span class="error_msg"><?php echo (@$error)?form_error('city_id'):''; ?></span> 
        				</div>
        				
        				<div class="form-group mx-sm-5 px-md-5">
        					<span class="error_msg" id="common_message"><?php echo ( @$error['common_message'] ) ? @$error['common_message'] : ''; ?></span> 
        				</div>
        				
        				<div class="clearfix mx-sm-5 px-md-5">					
        					<h5 class="pull-left">Already have an account? <a href="<?php echo base_url('login')?>" class="text-primary">Sign In</a></h5>
        					<input class="btn btn-upload px-5 pull-right" type="submit" value="Register"> 
        				</div>
    				</form>
    			</div>
    		</div>
    	</div>
    </div>
</div>    