<?php
    $data['menu'] = "notification";
    $this->load->view('elements/header-menu', $data );
?>
<div class="container mT150 mT130-xs">
	<div class=" mx-lg-5 px-md-5">
		<?php 
		if( $listArr->status == true )
		{
		    if( !isEmptyArr( $listArr->data ) )
		    {
		        foreach ( array_reverse( $listArr->data ) as $k=>$ar )
		        {
		            ?>
		            <a class="noti-block" href="#">
            			<div class="notifi-img">
            				<div class="user-img"> <img src="<?php echo $ar->image_url;?>" class="img-responsive" alt=""> </div>
            			</div>
            			<div class="noti-detail ml-md-3">
            				<p class="mb-2"><?php echo $ar->notification_text;?></p>
            				<small class="text-muted"><?php echo get_time_ago( $ar->timestamp );?></small>
            				<?php 
            				if( $ar->read_status == false )
            				    echo '<i class="fa fa-circle unread-noti"></i>';
            				?>
            			</div>
            		</a>
		            <?php 
		        }
		    }
		}
		?>
	</div>
</div>