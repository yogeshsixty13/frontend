		<!-- Start Popup modal -->
		<?php 
		$this->load->view('elements/popup-post-job-modal');
		$this->load->view('elements/popup-make-an-offer-modal');
		$this->load->view('elements/popup-offer-accept-modal');
		$this->load->view('elements/popup-resubmit-offer-modal');
		$this->load->view('elements/popup-cancel-task-modal');
		$this->load->view('elements/popup-additional-payment-modal');
		$this->load->view('elements/popup-issue-modal');
		$this->load->view('elements/popup-payment-modal');
		?>
		<!-- End Popup modal -->
		
		
		  
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
      

<!--  <script src="js/material-bootstrap-wizard.js"></script>   -->
		
        <script src="<?php echo asset_url('js/bootstrap.js')?>"></script>
        <script src="<?php echo asset_url('js/bootstrap-select.js')?>" defer></script>
        <script src="<?php echo asset_url('js/classie.js')?>"></script>
      
		<script src="<?php echo asset_url('js/perfect-scrollbar.js')?>"></script>  
		<script src="<?php echo asset_url('js/bootstrap-slider.min.js')?>"></script>
		<script src="<?php echo asset_url('js/jquery.payment.js')?>"></script>
		<script src="<?php echo asset_url('js/inputmask.binding.js')?>"></script>
         
        <!--  plugin for date picker-->
        <script src="<?php echo asset_url('js/jquery-ui.js')?>"></script>
		<script src="<?php echo asset_url('js/autosuggestdropdown.js')?>"></script>
        <script>
        	  $( function() {
        		    $( "#datepicker" ).datepicker();
        	  } );
          </script>
        <!--  Plugin for the Wizard -->
        <script src="<?php //echo asset_url('js/material-bootstrap-wizard.js')?>"></script>
        <script src="<?php echo asset_url('js/custom.js?v='.time())?>"></script>
        <script src="<?php echo asset_url('js/myJs.js?v='.time())?>"></script>
        
  <!--    lightbox box-->    
   <script type="text/javascript" src="js/lightbox.js"></script>  
        <script>
        	$(document).ready(function(){
        		var width = window.innerWidth;
        		if(width <= 1023 ) {
        			$(".mob_opn-sidepanel").click(function(){
        				$(".mob-sidepanel").css({"width": "100%", "opacity": "1"});
        				$(".mob-sidepanel .tab-content").css("display", "block");
        				$("body").css("overflow", "hidden");
        				
        			});
        			$(".back").click(function(){
        				$(".mob-sidepanel").css({"width": "0", "opacity": "0"});
        				$(".mob-sidepanel .tab-content").css("display", "none");
        			});
        			
        			$("#opnFilter").click(function(){
        				$("#mobFilter").slideDown("");
        			});
        			
        			$("#closeFilter").click(function(){
        				$("#mobFilter").slideUp("");
        			});
        			$("#applyFilter").click(function(){
        				$("#mobFilter").slideUp("");
        			});
        		}
        	});	
        	
        </script>
    </body>
</html>
