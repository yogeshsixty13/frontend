<header class="bg-white navbar-fixed-top container-header">
	<nav class="navbar navbar-default border-0 mb-0 mb-md-4">
		<div class="container1">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span> 
					<span class="icon-bar"></span> 
					<span class="icon-bar"></span> 
					<span class="icon-bar"></span>
				</button>
				<a href="<?php echo base_url('notification')?>" class="notification_icon">
					<i class="fa fa-bell fa-2x"></i> 
					<span>
						<?php $count = getRequestedDataFromURL( 'notification/unread/count/' );
						echo $count->data;?>
					</span>
				</a> 
				<a class="navbar-brand" href="#">
					<img src="<?php echo asset_url('images/logo.png');?>">
				</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<?php 
				$activeMenu = $this->uri->segment(1);
				?>
				<ul class="nav navbar-nav navbar-right text-center">
					<li>
						<a href="#" class="" data-toggle="modal" data-target="#myModal"> <i class="fas fa-briefcase  mB10 pR5"></i> Post Task </a>
					</li>
					<li>
						<a href="<?php echo asset_url('find-task');?>" class="<?php echo ( $activeMenu == "find-task" ) ? 'active' : ''; ?>"> <i class="fas fa-search mB10 pR5"></i>Find Tasks </a>
					</li>
					<li>
						<a href="<?php echo asset_url('my-task');?>" class="<?php echo ( $activeMenu == "my-task" ) ? 'active' : ''; ?>"><i class="fas fa-archive mB10 pR5"></i>My Tasks</a>
					</li>
					<li>
						<a class="dropdown-toggle <?php echo ( $activeMenu == "notification" ) ? 'active' : ''; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="far fa-bell mB10 pR5"></i>Notifications </a>
						<ul class="dropdown-menu">
							<?php 
							$notiArr = getAllNotification();
							
							if( $notiArr->status == true )
							{
							    if( !isEmptyArr( $notiArr->data ) )
							    {
							        foreach ( $notiArr->data as $k=>$ar )
							        {
// 							            if( $k < 10 )
							            {
							                ?>
							                <li>
                								<a href="#" class="noti-block py-2 px-3 mb-0">
                									<div class="user-img pull-left"> <img src="<?php echo $ar->image_url;?>" class="img-responsive" alt=""> </div>
                									<div class="noti-detail ">
                										<p class="mb-1"><?php echo $ar->notification_text;?></p>
                										<small class="text-muted"><?php echo get_time_ago( $ar->timestamp );?></small>
                									</div>
                								</a>
                							</li>
							                <?php 
							            }
							        }
							        
// 							        if( COUNT( $notiArr->data ) > 10 )
							        {
							            ?>
							            <li> <a href="<?php echo asset_url('notification');?>" class=" py-2 mx-5 btn btn-upload mt-3">Read More</a> </li>
							            <?php 
							        }
							    }
							}
							?>
						</ul>
					</li>
					<li>
						<a class="dropdown-toggle <?php echo ( $activeMenu == "my-account" ) ? 'active' : ''; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="far fa-user mB10 pR5"></i>My Account </a>
						<ul class="dropdown-menu">
							<li><a href="<?php echo base_url('profile')?>"><i class="fas fa-user-tie text-primary"></i> My Profile</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="<?php echo base_url('payments-record');?>"><i class="fas fa-credit-card text-primary"></i> Payment records</a></li>
							<li><a href="#" data-toggle="modal" data-target="#payment"><i class="fas fa-money-bill-wave text-primary"></i> Payments</a></li>
							<li><a href="<?php echo base_url('change-password');?>"><i class="fas fa-lock text-primary"></i> Change password</a></li>
							<li><a href="<?php echo base_url('help-contact-Us')?>"><i class="fas fa-info text-primary"></i> Help / Contact US</a></li>
							<li><a href="<?php echo base_url('terms-conditions');?>"><i class="fas fa-file text-primary"></i> Terms Of Service</a></li>
							<li><a href="<?php echo base_url('privacy-policy');?>"><i class="fas fa-shield-alt text-primary"></i> Privacy Policy</a></li>
							<li><a href="<?php echo base_url('insuarance');?>"><i class="fas fa-file text-primary"></i> Insurance</a></li>
							<li><a href="<?php echo base_url('software-licence');?>"><i class="fas fa-file text-primary"></i> Software Licence</a></li>
							<li><a href="<?php echo base_url('disclaimer');?>"><i class="fas fa-file text-primary"></i> Disclaimer</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="<?php echo base_url('logout')?>"><i class="fas fa-sign-out-alt text-primary"></i> Logout</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
		<!-- /.container-fluid -->
	</nav>
	<?php 
	if( !empty($menu) )
	   $this->load->view('elements/menu-'.$menu );
	?>
</header>