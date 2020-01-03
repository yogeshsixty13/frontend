<?php 
class MY_Router extends CI_Router {
 
	var $error_controller = 'error';
	var $error_method_404 = 'error_404';
 
    function My_Router()
    {
       parent::__construct();
    }
 
	// this is just the same method as in Router.php, with show_404() replaced by $this->error_404();
	function _validate_request($segments)
	{	
		if(is_dir(APPPATH.'controllers/'.$segments[0]) == FALSE || 
			(count($segments) > 0 && !is_dir(APPPATH.'controllers/'.$segments[0]) && file_exists(APPPATH.'controllers/'.$segments[1].EXT) == FALSE) ||
			(!is_dir(APPPATH.'controllers/'.$segments[0]) && file_exists(APPPATH.'controllers/'.$segments[0].EXT) == FALSE)){
			
			//echo $this->default_controller; die;
			$this->set_class($this->default_controller);
			//$this->set_method('index');
			if(!isset($segments[1]))
				$segments[1] = $segments[0];
				
			return $segments;
		}
		else
		{
			if (count($segments) == 0)
			{
				return $segments;
			}
	
			// Does the requested controller exist in the root folder?
			if (file_exists(APPPATH.'controllers/'.$segments[0].EXT))
			{
				return $segments;
			}
	
			// Is the controller in a sub-folder?
			if (is_dir(APPPATH.'controllers/'.$segments[0]))
			{
				// Set the directory and remove it from the segment array
				$this->set_directory($segments[0]);
				$segments = array_slice($segments, 1);
	
				if (count($segments) > 0)
				{
					// Does the requested controller exist in the sub-folder?
					if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$segments[0].EXT))
					{
						show_404($this->fetch_directory().$segments[0]);
					}
					else
						$this->set_class($segments[0]);
				}
				else
				{
					// Is the method being specified in the route?
					if (strpos($this->default_controller, '/') !== FALSE)
					{
						$x = explode('/', $this->default_controller);
	
						$this->set_class($x[0]);
						$this->set_method($x[1]);
					}
					else
					{
						$this->set_class($this->default_controller);
						$this->set_method('index');
					}
	
					// Does the default controller exist in the sub-folder?
					if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.EXT))
					{
						$this->directory = '';
						return array();
					}
	
				}
				return $segments;
			}
		}
		
		// Can't find the requested controller...
		// If we've gotten this far it means that the URI does not correlate to a valid
		// controller class.  We will now see if there is an override
		if ( !empty($this->routes['404_override']) )
		{
			$x = explode('/', $this->routes['404_override']);
			$this->set_class($x[0]);
			$this->set_method(isset($x[1]) ? $x[1] : 'index');
			return $x;
		}
		// Nothing else to do at this point but show a 404
		show_404($segments[0]);
	}
 
	function error_404()
	{
		$this->directory = "";
		$segments = array();
		$segments[] = $this->error_controller;
		$segments[] = $this->error_method_404;
		return $segments;
	}
  	function _set_request($segments = array())
	{
		$segments = $this->_validate_request($segments);
		
		if (count($segments) == 0)
		{
			return $this->_set_default_controller();
		}	
		
		$this->set_class($this->class);
		
		//print_r($segments);
		if (isset($segments[1]))
		{
			// A standard method request
			$this->set_method($segments[1]);
		}
		else
		{
			// This lets the "routed" segment array identify that the default
			// index method is being used.
			$segments[1] = 'index';
		}
		//echo $this->method;
		
		// Update our "routed" segment array to contain the segments.
		// Note: If there is no custom routing, this array will be
		// identical to $this->uri->segments
		$this->uri->rsegments = $segments;
	}
	
	function set_class($class)
	{
		$this->class = str_replace(array('/', '.'), '', $class);
	}
	
	function set_method($method)
	{
		$this->method = $method;
	}
	function _set_default_controller()
	{
		if ($this->default_controller === FALSE)
		{
			show_error("Unable to determine what should be displayed. A default route has not been specified in the routing file.");
		}
		// Is the method being specified?
		if (strpos($this->default_controller, '/') !== FALSE)
		{
			$x = explode('/', $this->default_controller);
			
			$this->set_class($x[0]);
			$this->set_method($x[1]);
			$this->_set_request($x);
		}
		else
		{
			$this->set_class($this->default_controller);
			$this->set_method('index');
			$this->_set_request(array($this->default_controller, 'index'));
		}
		// re-index the routed segments array so it starts with 1 rather than 0
		$this->uri->_reindex_segments();
		log_message('debug', "No URI present. Default controller set.");
	}
	function fetch_class()
	{
		// if method doesn't exist in class, change
		// class to error and method to error_404
		$this->check_method();
 
		return $this->class;
	}
	function check_method()
	{
		$ignore_remap = true;
 		
		$class = $this->class;
		
		if (class_exists($class))
		{	
			// methods for this class
			$class_methods = array_map('strtolower', get_class_methods($class));
 
			// ignore controllers using _remap()
			if($ignore_remap && in_array('_remap', $class_methods))
			{
				return;
			}
 
			if (! in_array(strtolower($this->method), $class_methods))
			{
				$this->directory = "";
				$this->class = $this->error_controller;
				$this->method = $this->error_method_404;
				include(APPPATH.'controllers/'.$this->fetch_directory().$this->error_controller.EXT);
			}
		}
	}
	function show_404()
	{
		include(APPPATH.'controllers/'.$this->fetch_directory().$this->error_controller.EXT);
		call_user_func(array($this->error_controller, $this->error_method_404));
	}
 
}