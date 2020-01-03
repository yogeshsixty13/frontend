<?php

$nt_arr = array('danger','info', 'success', 'error', 'warning');

foreach($nt_arr as $nt)
{
	$flMessage = getFlashMessage($nt);
	if($flMessage != '')
	{
		$noti = $nt;
		break;
	}
}

if(isset($noti)):
	if($this->router->directory == 'admin/'):
?>
		<script type="text/javascript">
			/*
			+---------------------------------------------+
				AutoHide notification div. function call
				from timeout every 7 seconds.
			+---------------------------------------------+
			*/
			function hideNotification()
			{
				$('.notification').slideUp(); 
			}
			
			window.setTimeout(hideNotification, 5000);
		</script>
	
		<div class="notification <?php echo $noti; ?> png_bg">
			<a style="cursor:pointer" class="close">
		        <img src="<?php echo asset_url('images/admin/cross_grey_small.png'); ?>" title="Close this notification" alt="close">
	        </a>
			<div>
				<?php echo $flMessage; ?>
			</div>
		</div>
<?php
	else:
?>	
		<script type="text/javascript">
			type = '<?php echo $noti;?>'; 
			message = '<?php echo $flMessage?>'; 
		</script>
<?php	
	endif;
endif;
?>
