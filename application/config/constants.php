<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


define('LOCALHOST_IP','192.168.29.134');

if(!isset($_SERVER['HTTP_HOST']))
{
	$_SERVER['HTTP_HOST'] = LOCALHOST_IP;
	$_SERVER['HTTP_USER_AGENT'] = 'BAT FILE';
	$_SERVER['REMOTE_ADDR'] = LOCALHOST_IP;
}
else if(!isset($_SERVER['HTTP_USER_AGENT']))
{
	$_SERVER['HTTP_USER_AGENT'] = 'Not Found';
}

/**
 * Local host path domain part, added on 14-09-2015
 *
 *
 */
define('LOCALHOST_PART', "codeigniter/wow-task/" );

/*
|--------------------------------------------------------------------------
| CORS dir
|--------------------------------------------------------------------------
*/
$_SERVER['HTTP_ORIGIN'] = "http://bug.Cloudwebstechnology.com/";
define('HTTP_ORIGIN', ( ($_SERVER['HTTP_HOST'] == LOCALHOST_IP) ? $_SERVER['HTTP_ORIGIN'] : " " ) );

/*
|--------------------------------------------------------------------------
| BAse dir
|--------------------------------------------------------------------------
*/
define('BASE_DIR', ( ($_SERVER['HTTP_HOST'] == LOCALHOST_IP) ? $_SERVER['DOCUMENT_ROOT']."/".LOCALHOST_PART: $_SERVER['DOCUMENT_ROOT']."/" ) );

/*
|--------------------------------------------------------------------------
| CDN url
|--------------------------------------------------------------------------
*/
define( 'ASSET_URL', ( ( $_SERVER['HTTP_HOST'] == LOCALHOST_IP ) ? 'http://'.LOCALHOST_IP.'/'.LOCALHOST_PART: 'http://test.wowtasks.com/' ) );//www.sixty13.com/wow-task-api

/**
 * @deprecated
 * image angle index to display at front side	
 */
define('ANGLE_IN', 1);	//Change from now individual index ae customized for each category

/*
|--------------------------------------------------------------------------
// Currency Constant
|--------------------------------------------------------------------------
*/
define('INR_ID', 1); //INR currency ID since data store always contains INR values so it will be used at many places to handle currency conversions

/**
 * @deprecated
 * @var unknown
 */
define('WORK_USERAGENT','SPHIDER');


/* End of file constants.php */
/* Location: ./application/config/constants.php */