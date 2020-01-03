<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Loader extends CI_Loader
{
	/*
	Place the class in application/libraries for your CodeIgniter application. This 
	plugin overrides the default $this->load->view() behaviour. Create a folder 
	called shared_sections in the views folder and create files called head.php,
	header.php, navigation.php and footer.php within it. Your views will only need to
	contain your content div and your controllers will not need to be modified since
	$this->load->view is now triggering the method on MY_Loader instead of CI_Loader.
	
	Set $this->load->no_header=TRUE before the $this->load->view() call in your 
	controller to suppress loading of the header div. Similarly, set 
	$this->load->no_navigation=TRUE to suppress loading of the navigation div.
	*/

	//Variable suppresses loading of the header div file.
	var $no_header = FALSE;

	//Variable suppresses loading of the navigation div file.
	var $no_navigation = FALSE;
    
    
    function My_Loader()
    {   
        parent::__construct(); 
    }
    
    function view($view, $vars = array(), $return = FALSE)
    {
		//head.php contains xml declaration, doctype and the opening HTML tag and head element.
		$output=parent::view('shared_sections/head.php', $vars, TRUE);

		//header.php contains a div for the page header/banner, this can be suppressed optionally.
		if (!$this->no_header) $output.=parent::view('shared_sections/header.php', $vars, TRUE);

		//Your navigation menu, optionally suppressed.
		if (!$this->no_navigation) $output.=parent::view('shared_sections/navigation.php', $vars, TRUE);

   		//this is the non-repeating part of the view loaded as dictated in your controller. 
		$output.=parent::view($view, $vars, TRUE);
		
		$output.=parent::view('shared_sections/footer.php', $vars, TRUE);
		
		if ($return == FALSE) echo $output;
		else return $output;
    }

	//This just calls the overridden load->view() method if there is a need for it.
	function basic_view($view, $vars = array(), $return = FALSE)
	{
	   parent::view($view, $vars, $return);
	}
    
}

?>
