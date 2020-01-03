
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once 'application/libraries/Google/vendor/autoload.php';

class login extends CI_Controller
{
    var $google_client = "";
    
    //parent constructor will load model inside it
    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        parent::__construct();    
		
        // Load fb config
        $this->load->config('facebook_config');
        $this->load->library('Facebook');

    }
    
    /**
     * 
     */
    function index()
    {
        $token = $this->session->userdata('token');
        if( $token )
            redirect('find-task');

        $google_client = new Google_Client();
        $google_client->setClientId('6339978627-ltljndphmjm6tfq63o1901ctol84441q.apps.googleusercontent.com');
        $google_client->setClientSecret('eooNQlbMosKvOg0Mm26h0Oo3');
        $google_client->setRedirectUri(base_url().'google-login');
        $google_client->addScope('email');
        
        $google_client->addScope('profile');
		
        $data = array();
        $data['facebook_login_url'] = $this->facebook->login_url();
        $data['google_login_url']= $google_client->createAuthUrl();//$this->google->get_login_url();//
        $data['pageName'] = 'wow-task';
        $this->load->view('site-layout',$data);
    }
    
    /**
     * 
     */
    function signIn()
    {
        $data = array();
        if( isPost() )
        {
            $this->form_validation->set_rules('username','User Name','trim|required|valid_email');
            $this->form_validation->set_rules('password','password','trim|required|min_legnth[6]|max_legnth[1000]');
            
            if( $this->form_validation->run() == FALSE )
            {
                $data['error'] = $this->form_validation->get_errors();
            }
            else  //redirect find job
            {
                $data = $this->input->post();
                $data_string = json_encode($data);
                $result = postDataSubmit( getSysConfig('api_url')."login/", $data_string );
                
                if( $result->status == "true" )
                {
                    $session = array();
                    $session['first_name'] = $result->data->first_name;
                    $session['last_name'] = $result->data->last_name;
                    $session['email'] = $result->data->email;
                    $session['token'] = $result->data->token;
                    $session['id'] = $result->data->id;
                    $session['address'] = $result->data->address;
                    $session['city_name'] = $result->data->city_name;
                    $session['city_latitude'] = $result->data->city_latitude;
                    $session['city_longitude'] = $result->data->city_longitude;
                    $session['country_name'] = $result->data->country_name;
                    $session['mobile_no'] = $result->data->mobile_no;
                    $session['image_url'] = $result->data->image_url;
                    $session['gender'] = $result->data->gender;
                    $session['regtype'] = $result->data->regtype;
                    $session['date_of_birth'] = $result->data->date_of_birth;
                    
                    $this->session->set_userdata( $session );
                    
                    redirect( 'find-task' );
                }
                else
                    $data['response'] = $result->response;
            }
        }
        
        $data['pageName'] = 'login';
        $this->load->view('site-layout',$data);       
    }
    
    /**
     * 
     */
    function logout()
    {
        session_destroy();
        session_unset();
        redirect( 'login' );
    }
    
    /**
     * 
     */
    function register()
    {
        $data = array();
        if( isPost() )
        {
            $this->form_validation->set_rules('first_name','First Name','trim|required');
            $this->form_validation->set_rules('last_name','Last Name','trim|required');
            $this->form_validation->set_rules('username','Email ID','trim|required|valid_email');
            $this->form_validation->set_rules('password','Password','trim|required|min_legnth[6]|max_legnth[1000]');
            $this->form_validation->set_rules('c_password','Confirm Password','trim|required|min_legnth[6]|max_legnth[1000]|matches[password]');
            $this->form_validation->set_rules('city_id','City','trim|required');
            
            if( $this->form_validation->run() == FALSE )
            {
                $data['error'] = $this->form_validation->get_errors();
            }
            else  //redirect find job
            {
                $data = $this->input->post();
                $data_string = json_encode($data);
                $result = postDataSubmit( getSysConfig('api_url')."register-one/", $data_string );
                
                if( $result->status == "true" )
                {
                    $data['pageName'] = 'email_otp_varification';
                    $data['result'] = $result;
                    $_SESSION['registration_key'] = $result->registration_key;
                    redirect( 'otp-varification' );
                }
                else
                    $data['error']['common_message'] = $result->description;
            }
        }
        
        $data['pageName'] = 'register';
        $this->load->view('site-layout',$data);
    }
    
    /**
     * 
     */
    function change_password()
    {
        $data = array();
        if( isPost() )
        {
            $this->form_validation->set_rules('old_password','old Password','trim|required|min_legnth[6]|max_legnth[1000]');
            $this->form_validation->set_rules('new_password','new Password','trim|required|min_legnth[6]|max_legnth[1000]');
            $this->form_validation->set_rules('c_password','Confirm Password','trim|required|min_legnth[1]|max_legnth[1000]|matches[new_password]');
            
            if( $this->form_validation->run() == FALSE )
                $data['error'] = $this->form_validation->get_errors();
            else
            {
                $data = $this->input->post();
                $data_string = json_encode($data);
                $result = postDataSubmit( getSysConfig('api_url')."change/password/", $data_string );
                
                if( $result->status == true )
                    redirect( 'find-task' );
                else
                    $data['response'] = $result->data;
            }
        }
        
        $data['pageName'] = 'change-pass';
        $this->load->view( 'site-layout', $data );
    }
    
    /**
     * 
     */
    function forgot_password()
    {
        $data = array();
        if( isPost() )
        {
            $this->form_validation->set_rules('username','User Name','trim|required|valid_email');
            
            if( $this->form_validation->run() == FALSE )
                $data['error'] = $this->form_validation->get_errors();
            else
            {
                $data = $this->input->post();
                $data_string = json_encode($data);
                $result = postDataSubmit( getSysConfig('api_url')."forgot-password/", $data_string );
                
                if( isset( $result->status ) )
                {
                    if( $result->status == true )
                        $data['success'] = $result->response;
                    else
                        $data['error'] = $result->response;
                }
                else 
                    $data['success'] = $result;
            }
        }
        
        $data['pageName'] = 'forgot-password';
        $this->load->view( 'site-layout', $data );
    }
    /**
     * 
     */
    function emailOTPVarification()
    {
        $data = array();
        if( isPost() )
        {
            $this->form_validation->set_rules('otp','OTP','trim|required');
            
            if( $this->form_validation->run() == FALSE )
            {
                $data['error'] = $this->form_validation->get_errors();
            }
            else  //redirect find job
            {
                $data = $this->input->post();
                $data_string = json_encode($data);
                $result = postDataSubmit( getSysConfig('api_url')."verify/otp/complete/registration/", $data_string );
                
                if( $result->status == true )
                {
                    $_SESSION['token'] = $result->token;
                    unset( $_SESSION['registration_key'] );
                    redirect('find-task');
                }
                else
                    $data['error']['common_message'] = $result->description;
            }
        }
        
        $data['pageName'] = 'email_otp_varification';
        $data['registration_key'] = $_SESSION['registration_key'];
        $this->load->view('site-layout',$data);
    }
    
    /**
     * 
     */
    function googleLogin()
    {
        $session_data = array();
        $google_client = new Google_Client();
        
        $google_client->setClientId('6339978627-ltljndphmjm6tfq63o1901ctol84441q.apps.googleusercontent.com');
        
        $google_client->setClientSecret('eooNQlbMosKvOg0Mm26h0Oo3');
        
        $google_client->setRedirectUri(base_url().'google-login');
        
        $google_client->addScope('email');
        
        $google_client->addScope('profile');
        $token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);
        if(!isset($token['error']))
        {
             $google_client->setAccessToken( $token['access_token'] );

//            $_SESSION['access_token'] = $token['access_token'];
            $google_service = new Google_Service_Oauth2($google_client);
            $data = $google_service->userinfo->get();
            
            $postData['secret_key'] = $this->config->item('google_app_id');
    		$postData['social_id'] = $token['access_token'];//$data['id'];
    		$postData['username'] = $data['email'];
    		$postData['register_via'] = "Google";//$data['email'];
    		$postData['first_name'] = $data['given_name'];//$data['first_name'];
    		$postData['last_name'] = $data['family_name'];//$data['last_name'];
    		$postData['gender'] = $data['gender'];//$data['gender'];
    		$postData['date_of_birth'] = "";//$data['id'];
    		$postData['profile_image'] =  $data['picture'];//$data['picture']['data']['url'];
    
    		$data_string = json_encode($postData);
    		$result = postDataSubmit( "register/social/accounts/", $data_string );
            pr($result);die;
    		if( $result->status == true )
    		{
    			$postData['token'] = $postData['social_id'];
    			$postData['id'] = $postData['social_id'];
    			$postData['image_url'] = $postData['profile_image'];
    
    			$this->session->set_userdata( $postData );
    
    			redirect( 'find-task' );
    		}
    		else
    			$data['response'] = $result->description;
    
        }
        $data['facebook_login_url'] = $this->facebook->login_url();
        $data['google_login_url']= $google_client->createAuthUrl();//$this->google->get_login_url();//
        $this->load->view('login',$data);
    }
      
    /**
     *
     */
    function facebookLogin()
    {
        $data = $postData = array();
        if($this->facebook->is_authenticated())
        {
            $data = $this->facebook->request('get', '/me?fields=id,first_name,last_name,email,gender,locale,picture');
            
            $postData['secret_key'] = $this->config->item('facebook_app_id');
            $postData['social_id'] = $data['id'];
            $postData['username'] = $data['email'];
            $postData['register_via'] = "Facebook";//$data['email'];
            $postData['first_name'] = $data['first_name'];
            $postData['last_name'] = $data['last_name'];
            $postData['gender'] = $data['gender'];
            $postData['date_of_birth'] = "";//$data['id'];
            $postData['profile_image'] = $data['picture']['data']['url'];
            
            $data_string = json_encode($postData);
            $result = postDataSubmit( "register/social/accounts/", $data_string );
            
            if( $result->status == true )
            {
                $postData['token'] = $postData['social_id'];
                $postData['id'] = $postData['social_id'];
                $postData['image_url'] = $postData['profile_image'];
                
                $this->session->set_userdata( $postData );
                
                redirect( 'find-task' );
            }
            else
                $data['response'] = $result->description;
            
        }
        
        $data['facebook_login_url'] = $this->facebook->login_url();
        $data['google_login_url']= $google_client->createAuthUrl();//$this->google->get_login_url();//
        $this->load->view('login',$data);
    }
}