<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$active_group = 'default';
$active_record = TRUE;
$host = $_SERVER['HTTP_HOST'];

$db['default']['dbdriver'] = 'mysqli';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = TRUE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;
$db['default']['hostname'] = '';
$db['default']['username'] = '';
$db['default']['password'] = '';
$db['default']['database'] = '';
$db['default']['db_debug'] = TRUE;

// if( $host == LOCALHOST_IP )
// {
// 	$db['default']['hostname'] = 'localhost';
// 	$db['default']['username'] = 'root';
// 	$db['default']['password'] = '@dmin';
// 	$db['default']['database'] = 'codeigniter_stationery';
// 	$db['default']['db_debug'] = TRUE;
// }
// else 
// {
// 	$db['default']['hostname'] = 'localhost';
// 	$db['default']['username'] = 'kstreeth_Stationery';
// 	$db['default']['password'] = 'Bansi@123';
// 	$db['default']['database'] = 'kstreeth_Stationery';
// 	$db['default']['db_debug'] = ( ENVIRONMENT == "production" ) ? FALSE : TRUE;
// }

/* End of file database.php */
/* Location: ./application/config/database.php */