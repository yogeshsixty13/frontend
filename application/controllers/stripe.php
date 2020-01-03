<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class stripe extends CI_Controller {
    
    /**
     * Get All Data from this method.
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get All Data from this method.
     */
    public function index()
    {
//         if( isPost() )
//         {
            require_once('application/libraries/stripe-php/init.php');
            
            \Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));
            
            \Stripe\Charge::create ([
                "amount" => 100,
                "currency" => "INR",
                "source" => $this->input->post('stripeToken'),
                "description" => "Test payment from wowTask.com."
            ]);
            
            $this->session->set_flashdata('success', 'Payment made successfully.');
            
            redirect('payments-record', 'refresh');
//         }
        
//         $this->load->view('my_stripe');
    }
}