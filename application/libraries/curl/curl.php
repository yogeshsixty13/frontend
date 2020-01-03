<?php

/**
 * Basic cURL wrapper for PHP
 * Copyright © 2013 Gerben Oolbekkink
 */
class CURL {
	private $ch;
	private $response;
	private $OptionsSetA;
	private $gz = false;
	
	/**
	 * Call this method to get singleton
	 *
	 * @return UserFactory
	 */
	public static function get_CURLinstance()
	{
		static $inst = null;
		if ($inst === null) {
			$inst = new CURL ();
		}
		return $inst;
	}
	
	public function __construct() {
		$this->ch = curl_init ();
		
		// default used in most case
		
		$this->setOption ( CURLOPT_RETURNTRANSFER, true );
		
		// default used in most case
		
		$this->setOption ( CURLOPT_USERAGENT, WORK_USERAGENT );
		
		// default HTTP version
		
		$this->setOption ( CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
		
		$this->setOption ( CURLOPT_FOLLOWLOCATION, true );
		
		// set timeout server
		$this->setOption ( CURLOPT_CONNECTTIMEOUT, 10);
		
		$this->setOption ( CURLOPT_TIMEOUT, 60);
		
	}
	
	/**
	 *
	 *
	 * Set any option, a gateway for curl_setopt
	 *
	 * @param
	 *        	key
	 *        	
	 *        	The key for the option
	 *        	
	 * @param
	 *        	value
	 *        	
	 *        	The value for the option
	 *        	
	 */
	public function setOption($key, $value) 

	{
		if ($key == CURLOPT_HTTPHEADER) 

		{
			
			return setHeader ( $value );
		} 

		else 

		{
			
			$this->rememberOption ( array (
					$key => $value 
			) );
			
			return curl_setopt ( $this->ch, $key, $value );
		}
	}
	
	/**
	 *
	 *
	 * Set any option, a gateway for curl_setopt
	 *
	 * @param
	 *        	key
	 *        	
	 *        	The key for the option
	 *        	
	 * @param
	 *        	value
	 *        	
	 *        	The value for the option
	 *        	
	 */
	public function setOptionArray($OptionA) 

	{
		$this->rememberOption ( $OptionA );
		
		return curl_setopt_array ( $this->ch, $OptionA );
	}
	
	/**
	 *
	 *
	 * Sets the header of the request
	 *
	 * @param
	 *        	header
	 *        	
	 *        	The header as array
	 *        	
	 */
	public function setHeader($header) 

	{
		$this->gz = (is_array ( $header ) && in_array ( "Accept-Encoding: gzip", $header ));
		
		$this->rememberOption ( array (
				CURLOPT_HTTPHEADER => $header 
		) );
		
		return curl_setopt ( $this->ch, CURLOPT_HTTPHEADER, $header );
	}
	
	/**
	 *
	 *
	 * Set the postfields for the request
	 *
	 * @param
	 *        	fields
	 *        	
	 *        	The fields, either as formatted string (id=9&value=5) or as an associative array (array('id'=>'9','value'=>'5'))
	 *        	
	 */
	public function setPostfields($fields) 

	{
		if (is_array ( $fields )) 

		{
			
			$fields = http_build_query ( $fields, '', '&' );
		}
		
		$this->rememberOption ( array (
				CURLOPT_POSTFIELDS => $fields 
		) );
		
		return curl_setopt ( $this->ch, CURLOPT_POSTFIELDS, $fields );
	}
	
	/**
	 *
	 *
	 * Execute the curl action
	 *
	 * @return The curl response
	 *        
	 *        
	 *        
	 */
	public function exec() 

	{
		$this->response = curl_exec ( $this->ch );
		
		return $this->response;
	}
	
	/**
	 *
	 *
	 * Curl get_info
	 *
	 * @return The curl response
	 *        
	 *        
	 *        
	 */
	public function get_info($InfoI) 

	{
		return curl_getinfo ( $this->ch, $InfoI );
	}
	
	/**
	 *
	 *
	 * Curl get_info
	 *
	 * @return The curl response
	 *        
	 *        
	 *        
	 */
	public function get_error_info() 

	{
		return array (
				'error' => curl_error ( $this->ch ),
				'error_no' => curl_errno ( $this->ch ) 
		);
	}
	public function close() 

	{
		curl_close ( $this->ch );
	}
	public function getResponse() 

	{
		if ($this->gz)
			
			return gzdecode ( $this->response );
		
		else
			
			return $this->response;
	}
	
	/**
	 *
	 * @author Cloudwebs
	 *        
	 * @abstract remember whatever options is set
	 *          
	 *          
	 *          
	 */
	private function rememberOption($OptionA) 

	{
		foreach ( $OptionA as $Key => $Val ) 

		{
			
			$this->OptionsSetA [$Key] = $Val;
		}
	}
	
	/**
	 *
	 * @author Cloudwebs
	 *        
	 * @abstract remove options is set and remembered(*)
	 *          
	 *          
	 *          
	 */
	private function removeOption() 

	{
		foreach ( $OptionsA as $Key => $Val ) 

		{
			
			curl_setopt ( $this->ch, $key, null );
		}
	}
}