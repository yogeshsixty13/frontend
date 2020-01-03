<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/
$route['default_controller'] = "login";
/**
 * from 16-06-2015 all 404 are first redirected to product urlDecode and will check if url is related 
 * product category or product detail or search page if it is then taken to particular page other redirected to 
 * default "my404" controller page. 
 */
$route['404_override'] = 'site/index';//'my404';
$route['err'] = 'home/index';	//'my404';
$route['find-task'] = "find_task";
$route['my-task'] = "my_task";
$route['notification'] = "my_account/getAllNotification";
$route['profile'] = "my_account/profile";
$route['sign-in'] = "login/signIn";
$route['sign-in(:post)'] = "login/signIn";
$route['logout'] = "login/logout";
$route['register'] = "login/register";
$route['register(:post)'] = "login/register";
$route['otp-varification'] = "login/emailOTPVarification";
$route['otp-varification(:post)'] = "login/emailOTPVarification";
$route['edit-profile'] = "my_account/edit_profile";
$route['update-profile'] = "my_account/update_profile";
$route['change-password'] = "login/change_password";
$route['change-password(:post)'] = "login/change_password";
$route['forgot-password'] = "login/forgot_password";
$route['forgot-password(:post)'] = "login/forgot_password";
$route['payments-record'] = "my_account/payments_record";
$route['terms-conditions'] = "my_account/terms_conditions";
$route['help-contact-Us'] = "my_account/help_contactUs";
$route['privacy-policy'] ="my_account/privacy_policy";
$route['insuarance'] ="my_account/insuarance";
$route['software-licence'] = "my_account/software_licence";
$route['disclaimer'] = "my_account/disclaimer";
$route['google-login'] = "login/googleLogin";
$route['facebook-login'] = "login/facebookLogin";

/**
 * In this step, we will create two routes for get request and another for post request. 
 * So, let's add new route on that file.
 */
$route['stripe'] = "stripe";
$route['stripe(:post)'] = "stripe";
/* End of file routes.php */
/* Location: ./application/config/routes.php */