<?php
class Custom_Form_validation extends CI_Form_validation
{
     function __construct($config = array())
     {
          parent::__construct($config);
     }
 
    /**
     * Error Array
     *
     * Returns the error messages as an array
     *
     * @return  array
     */
    function get_errors()
    {
        if (count($this->_error_array) === 0)
        {
                return FALSE;
        }
        else
		{
			/*$err = array();
			$keys = array_keys($this->_error_array);
			$err['key'] = $keys[0];
            $err['error'] = $this->_error_array[$keys[0]];
			return $err;*/
			return $this->_error_array;
		}
 
    }
}