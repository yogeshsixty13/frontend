<script src="https://maps.google.com/maps/api/js?sensor=false&key=AIzaSyBCnc-GcoMULbb3ECMT2SEWobbnFq5woYw" type="text/javascript"></script>
<script type="text/javascript">
	var base_url = "<?php echo base_url(); ?>";
	var asset_url = "<?php echo asset_url(); ?>";
	var controller = "<?php echo ucfirst($this->router->class); ?>";

	//see UML - An-JGV for more information on below variables
	var is_mobile = <?php echo ($this->session->userdata('lType') == 'PC' ? 'false' : 'true') ?>;
	var is_listing_page = false; 	//for scroll pagination
	var is_sol_listing = false;
	var p_id = 0; 
	var proConfig;
	var baseDomain = '<?php echo base_domain(); ?>';	//base domain for XMPP service
	var sessions_id = '<?php echo $this->session->userdata('token'); ?>';	//used in setting default XMPP connection for front user(Exper...)
	var appLaunch = '<?php echo getSysConfig('appLaunch'); ?>';
	var is_download_app = "<?php echo $this->session->userdata('is_SID_c'); ?>";	//When on mobile the webapp is first time launched show download app popup, when is app is launched. 
	var filter_page = '';
	var isAutoLoadPageDetails = false;
	var isAutoLoadPagination = true;
	/**
	 * notification variables
	 */
	var type = ""; 
	var message = ""; 
	var tot = 0;
	var post_step = 0 ;	
	/**
	 * lang
	 */	
</script>