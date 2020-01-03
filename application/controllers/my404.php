<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class my404 extends CI_Controller {

	//parent constructor will load model inside it
	function my404()
	{
		parent::__construct();
		
		//cache driver
		$this->load->driver( 'cache', array( 'adapter' => 'apc', 'backup' => 'file'));
	}
	
	function index()
	{
		redirect();
		//$this->output->set_status_header('404');
		//$data['custom_page_title'] = '404 Error! Page Not Found';
		//redirect(site_url());
        //$data['pageName'] = 'error_404';
		//$this->load->view('site-layout',$data);
	}
	
}
