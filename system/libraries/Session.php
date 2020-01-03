<?php
if( ! defined ( 'BASEPATH' ) )
	exit ( 'No direct script access allowed' );
/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package CodeIgniter
 * @author Dariusz Debowczyk
 * @copyright Copyright (c) 2006, D.Debowczyk
 * @license http://www.codeignitor.com/user_guide/license.html
 * @link http://www.codeigniter.com
 * @since Version 1.0
 * @filesource
 *
 */
	
// ------------------------------------------------------------------------

/**
 * Session class using native PHP session features and hardened against session fixation.
 *
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Sessions
 * @author Dariusz Debowczyk
 * @link http://www.codeigniter.com/user_guide/libraries/sessions.html
 */
class CI_Session
{
	var $session_id_ttl = 0; // session id time to live (TTL) in seconds : currently for 24 hours to timeout ==> 0 means infinite time for session to stay live
	var $flash_key = 'flash'; // prefix for "flash" variables (eg. flash:new:message)
	function __construct()
	{
		log_message ( 'debug', "Native_session Class Initialized" );
		$this->_sess_run ();
	}

	/**
	 * Regenerates session id
	 */
	function regenerate_id()
	{
		// copy old session data, including its id
		$old_session_id = session_id ();
		$old_session_data = $_SESSION;
		
		// regenerate session id and store it
		session_regenerate_id ();
		$new_session_id = session_id ();
		
		// switch to the old session and destroy its storage
		session_id ( $old_session_id );
		@session_destroy ();
		
		// switch back to the new session id and send the cookie
		session_id ( $new_session_id );
		session_start ();
		
		// restore the old session data into the new session
		$_SESSION = $old_session_data;
		
		// update the session creation time
		$_SESSION ['regenerated'] = time ();
		
		// session_write_close() patch based on this thread
		// http://www.codeigniter.com/forums/viewthread/1624/
		// there is a question mark ?? as to side affects
		
		// end the current session and store session data.
		session_write_close ();
	}

	/**
	 * Destroys the session and erases session storage
	 */
	function sess_destroy()
	{
		$this->destroy();
    }

	/**
	 * Fetch all session data
	 *
	 */
	function all_userdata()
	{
		return ( ! isset($_SESSION)) ? FALSE : $_SESSION;
	}

    /**
    * Destroys the session and erases session storage
    */
    function destroy()
    {
        unset($_SESSION);
        if ( isset( $_COOKIE[session_name()] ) )
        {
              setcookie(session_name(), '', time()-42000, '/');
        }
        session_destroy();
    }

    /**
    * Reads given session attribute value
    */
    function userdata($item)
    {
        if($item == 'session_id'){ //added for backward-compatibility
            return session_id();
        }else{
            return ( ! isset($_SESSION[$item])) ? false : $_SESSION[$item];
        }
    }

    /**
    * Sets session attributes to the given values
    */
    function set_userdata($newdata = array(), $newval = '')
    {
        if (is_string($newdata))
        {
            $newdata = array($newdata => $newval);
        }

        if (count($newdata) > 0)
        {
            foreach ($newdata as $key => $val)
            {
                $_SESSION[$key] = $val;
            }
        }
    }

    /**
    * Erases given session attributes
    */
    function unset_userdata($newdata = array())
    {
        if (is_string($newdata))
        {
            $newdata = array($newdata => '');
        }

        if (count($newdata) > 0)
        {
            foreach ($newdata as $key => $val)
            {
                unset($_SESSION[$key]);
            }
        }
    }

    /**
    * Starts up the session system for current request
    */
    function _sess_run()
    {
    	//$this->sess_destroy();
        session_start();
        
		// check if session id needs regeneration
        if ( $this->_session_id_expired() )
        {
            // regenerate session id (session data stays the
            // same, but old session storage is destroyed)
            $this->regenerate_id();
        }

        // delete old flashdata (from last request)
        $this->_flashdata_sweep();

        // mark all new flashdata as old (data will be deleted before next request)
        $this->_flashdata_mark();

        //echo "test1 ".$_GET["APP_KEY"]."<br>"; 
        //pr($_SESSION); 
        
        /**
         * Added on 13-06-2015 to re generate sessions, from session_id passed in URL in param APP_KEY.
         * Applicable to REST clients for only shared server installation.
         * 
         * On 22-06-2015 
         * This code block is moved outside of if statement to allow regenerate session in case when payment is performed using browsers, 
         * and in that case override existing cookie sessions if any there are. 
         */
        if( is_restClient() && getSysConfig( "IS_SHARED_SERVER" ) && !empty($_GET["APP_KEY"]) )
        {
        	/**
        	 * restore session as per session id passed in URL
        	 */
        	$this->reGenerateSessionWithID( $_GET["APP_KEY"] );
        }
        
        /**
         * do first time configuration if session is new
         */
		if( $this->userdata('sessions_id') === FALSE )
		{
				$sessA = array();
				
				/**
				 * session re instatiate to allow session re instatite in REST Apps
				 */
				if( is_restClient() && getSysConfig( "rest_allow_session_reinstantiate" ) && reInstantiateSession( $_REQUEST[ getSysConfig("PHPSESSID") ], $this ) )
				{
					//do nothing: session re instantiated
					

					/**
					 * added on 30-08-2015, since lType and so on is reuired session in system. 
					 */
					$sessA['sess_strt_time'] = time();
					$resDev = $this->getDeviceDesc( getUserAgent() );
					$sessA['lType'] = $resDev['lType'];
				}
				else
				{
					
						
					/**
					 * Cloudwebs
					 * added on 09-05-2015 for to allow REST APIs to depend on PHP session.
					 * It will tell REST client through REST response that session is change so that,
					 * they can update thier particular session key cookie and so on.
					*/
					$sessA["is_SID_c"] = 1;
						
				
					$sessA['sess_strt_time'] = time();
					$resDev = $this->getDeviceDesc( getUserAgent() );
						
					//sessions_id from perrian sessions table that point to current session_id hash and user_agent information
					$sessA['sessions_id'] = $this->saveSessionUserAgent( session_id(), getUserAgent(), $resDev['lType'].": ".$resDev['deviceBrowser']);
						
						
					/**
					 * Cloudwebs added ON 15-05-2015
					 * regenerate session id
					*/
					//regenerate session id same as sessions_id
					$session_id = generateSessionId( $sessA['sessions_id'] );
// 					set_session_id( $session_id );
// 					session_write_close();
// 					session_start();
					$this->reGenerateSessionWithID( $session_id );
						
					//set sessions_id: which is now same as PHP system session_id
// 					query( " UPDATE sessions SET session_id='".$session_id."' WHERE sessions_id=".$sessA['sessions_id']." " );
						
					/************************************* 	regenerate session id end ************************************/
						
						
					$sessA['lType'] = $resDev['lType'];
				}
				
				/**
				 * moved out of else on 30-08-2015
				 */
				/************************************* GeoIP ****************************************/
				// 				if( $_SERVER['HTTP_HOST'] != '192.168.1.14' )
				// 				{
				// 					$CI =& get_instance();
				// 					$CI->load->helper( 'geoip' );
				// 					redirectGeoIP();
				// 				}
				// 				else
				// 				{
				// 					//localhost: development
				// 					define( 'COUNTRY_CODE', 'IN' );
				// 				}
				
				// 				$sessA['country_code'] = COUNTRY_CODE;
				
				/**
				 * override for non country wise site
				 */
				define( 'COUNTRY_CODE', getDefaultCountryCode() );
				$sessA['country_code'] = COUNTRY_CODE;
				/************************************* GeoIP End ****************************************/
				
				
				$this->set_userdata( $sessA );
		}
		
		
		//flip layout rendering 
		if( isset($_GET['lType']) && ( $_GET['lType'] == 'PC' || $_GET['lType'] == 'M' ) )
		{
			$sessA['lType'] = $_GET['lType'];
			
			$this->set_userdata( $sessA );
		}
		
		/******************** Multi language support ********************/
		
		if( isset($_GET['set']) && $_GET['set'] == 'lang' )
		{
			$this->set_userdata( array( 'LANG'=>$_REQUEST['lang'] ) );

			//set language specific defaults
// 			if( $this->userdata('user_id') == userdata('root_id') )
// 			{
				
// 				if( $_REQUEST['lang'] == 'EN_US' )
// 				{
// 					//set_userdata( array( 'root_name'=>'My' ) );
// 				}
// 				else if( $_REQUEST['lang'] == 'GU' )
// 				{
// 					//set_userdata( array( 'root_name'=>'' ) );
// 				}
// 			}
		}
		else 
		{
			$this->set_userdata( array( 'LANG'=>'EN_US' ) );
		}
		
		/**
		 * added on 30-06-2015
		 */
		if( isset($_GET['si']) && $_GET['si'] == 'IT_KEY' )
		{
			//setInventorySession( $this->input->get("IT_KEY") );
			$this->set_userdata( array("IT_KEY"=>$_GET['IT_KEY']) );
		}
		
		
		/**
		 * @deprecated
		 * IS multiDOMAIN?
		 * applicable to multiple language solution only
		 */
// 		define('IS_ML', FALSE);
		
// 		/**
// 		 * @deprecated
// 		 * if multiple language is supported over multiple subdomain then use below code block, 
// 		 * if multiple sub domains are not required then just set default session to EN_US if 
// 		 * session is not set at all. 
// 		 */
// 		if( IS_ML )
// 		{
// 			/**
// 			 * MULTI DOMAIN
// 			 * multi language over it's own unique domain
// 			 */
// 			if( SUBDOMAIN === "WWW" || SUBDOMAIN === "EN" )
// 				$this->set_userdata( array( 'LANG'=>'EN_US' ) );
// 			else if( SUBDOMAIN === "HI" )
// 				$this->set_userdata( array( 'LANG'=>'HI' ) );
// 			else if( SUBDOMAIN === "GU" )
// 				$this->set_userdata( array( 'LANG'=>'GU' ) );
// 		}
// 		else 
// 		{
// 			/**
// 			 * SINGLE DOMAIN
// 			 * multi language over single domain
// 			 */
// 			if( $this->userdata('LANG') === FALSE )
// 			{
// 				$this->set_userdata( array( 'LANG'=>'EN_US' ) );
// 			}
// 		}
		

		if( $this->userdata('LANG') === FALSE )
		{
// 			$this->set_userdata( array( 'LANG'=>getLangCodeForCountryCode( COUNTRY_CODE ) ) );
			if( !defined("LANG") )
			{
				$this->set_userdata( array( 'LANG'=>getLangCodeForCountryCode( COUNTRY_CODE ) ) );
			}
			else
			{
				$this->set_userdata( array( 'LANG'=>LANG ) );
			}
		}

	    include_once BASE_DIR.'/application/language/'.$this->userdata('LANG').'/'.$this->userdata('LANG').'.php';
		
		/**
		 *---------------------------------------------------------------
		 * Department/Store/ccTLDs for more info see UML: ccTLDs
		 *---------------------------------------------------------------
		 *
		 * on 27-02-2015 initialization of MANUFACTURER_ID is moved to session library::_sess_run
		 * 
		 * below code could be made dynamic if more number of languages are supported 
		 */
		if( $this->userdata('LANG') === "EN_US" )
		{
			define( 'MANUFACTURER_ID', 7);
		}
		else if( $this->userdata('LANG') === "HI" )
		{
			define( 'MANUFACTURER_ID', 8);
		}
		else if( $this->userdata('LANG') === "GU" )
		{
			define( 'MANUFACTURER_ID', 9);
		}

		if( $this->userdata('IT_KEY') === FALSE )
		{
			$this->set_userdata( array( 'IT_KEY'=>getDefaultInventory() ) );
		}
		
		/******************** Multi language support end ****************/
		
    }

    /**
    * Checks if session has expired
    */
    function _session_id_expired()
    {
		if( $this->session_id_ttl == 0 )	//session is allowed for infinite time
		{
			return false;	
		}
		
        if ( !isset( $_SESSION['regenerated'] ) )
        {
            $_SESSION['regenerated'] = time();
            return false;
        }

        $expiry_time = time() - $this->session_id_ttl;

        if ( $_SESSION['regenerated'] <=  $expiry_time )         
		{             return true;         }         
		return false;     
	}          
		
/**     * Sets "flash" data which will be available only in next request (then it will     * be deleted from session). You can use it to implement "Save succeeded" messages     * after redirect.     */     
	function set_flashdata($key, $value)
	{
		$flash_key = $this->flash_key.':new:'.$key;
        $this->set_userdata($flash_key, $value);
    }

    /**
    * Keeps existing "flash" data available to next request.
    */
    function keep_flashdata($key)
    {
        $old_flash_key = $this->flash_key.':old:'.$key;
        $value = $this->userdata($old_flash_key);

        $new_flash_key = $this->flash_key.':new:'.$key;
        $this->set_userdata($new_flash_key, $value);
    }

    /**
    * Returns "flash" data for the given key.
    */
    function flashdata($key)
    {
        $flash_key = $this->flash_key.':old:'.$key;
        return $this->userdata($flash_key);
    }

    /**
    * PRIVATE: Internal method - marks "flash" session attributes as 'old'
    */
    function _flashdata_mark()
    {
        foreach ($_SESSION as $name => $value)
        {
            $parts = explode(':new:', $name);
            if (is_array($parts) && count($parts) == 2)
            {
                $new_name = $this->flash_key.':old:'.$parts[1];
                $this->set_userdata($new_name, $value);
                $this->unset_userdata($name);
            }
        }
    }

    /**
    * PRIVATE: Internal method - removes "flash" session marked as 'old'
    */
    function _flashdata_sweep()
    {
        foreach ($_SESSION as $name => $value)
        {
            $parts = explode(':old:', $name);
            if (is_array($parts) && count($parts) == 2 && $parts[0] == $this->flash_key)
            {
                $this->unset_userdata($name);
            }
        }
    }
	
	/**
 * @author Cloudwebs
 * @abstract function will get user agent description
 */
	function getDeviceDesc( $user_agent )
	{
		$CI =& get_instance();
		$CI->load->helper('mobile_detect');
		
		$detect = new Mobile_Detect();
		
		$resArr['lType'] = ($detect->isMobile($user_agent) ? ($detect->isTablet($user_agent) ? 'Tablet' : 'Mobile') : 'PC');
	    $layoutTypeArr = layoutTypes();
		
	    // Fallback. If everything fails choose classic layout.
	    if ( !in_array( $resArr['lType'], $layoutTypeArr) ) { $resArr['lType'] = 'PC'; }


		$resArr['deviceModel'] = '';
		$resArr['deviceBrowser'] = ( !$detect->match( 'Chrome', $user_agent) ? 
										( !$detect->match( 'Firefox', $user_agent) ? 
											( !$detect->match( 'Opera', $user_agent) ? 
												( !$detect->match( 'MSIE', $user_agent) ? 
													( !$detect->match( 'iPhone', $user_agent) ? 
														( !$detect->match( 'Android', $user_agent) ? 
															( !$detect->match( 'bot', $user_agent) ? '' 
																: 'Bot' 
															)
															: 'Android' 
														)
														: 'iPhone' 
													)
													: 'IE' 
												)
												: 'Opera' 
											)
											: 'Firefox'
										)	
									 : 'Chrome'
								   );

		if( $resArr['lType'] != 'PC' )
		{
			$resArr['deviceBrowser'] .= ": ". ( !$detect->isiOS() ? 
												( !$detect->isAndroidOS() ? 
													( !$detect->isBlackBerryOS() ? 
														( !$detect->isPalmOS() ? 
															( !$detect->isSymbianOS() ? 
																( !$detect->isWindowsMobileOS() ? '' 
																	: 'WindowsMobileOS' 
																)
																: 'SymbianOS' 
															)
															: 'PalmOS' 
														)
														: 'BlackBerryOS' 
													)
													: 'AndroidOS'
											)	
										 : 'iOS'
									   );
		}

	    return $resArr;		
	}
	

/**
 * @author Cloudwebs
 * @abstract function will save new user agent information when first time session_id generated
 */
	function saveSessionUserAgent( $session_id, $s_user_agent, $s_user_device)	
	{
// 		$CI =& get_instance();
// 		$CI->db->insert( "sessions", array('session_id'=>$session_id, 's_ip'=>$_SERVER['REMOTE_ADDR'], 's_user_agent'=>$s_user_agent, 's_user_device'=>$s_user_device));
// 		return $CI->db->insert_id();
	}	

/**
 * Added on 13-06-2015
 * @author Cloudwebs
 * function regenerate session with specified session id
 */
	function reGenerateSessionWithID( $session_id )
	{
		//echo "set_session_id: " . $session_id . " <br><br>";
		set_session_id( $session_id );
		session_write_close();
		session_start();
		//echo "set TO: " . session_id() . " <br><br>";
	}
	
}
?>