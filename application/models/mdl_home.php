<?php
class mdl_home extends CI_Model
{
	function getSliderData()
	{
		return $this->db->where('slider_status','0')
				->order_by('slider_sort_order')
				->get('slider')
				->result_array();
	}
/**
 * @abstract function will save customer feedback message
 */
	function feedback()
	{
		$data = $this->input->post();
		$cat_option = (!empty($data['cat_option'])) ? $data['cat_option'] : '';
		$country_option = (!empty($data['country_option'])) ? $data['country_option'] : '';
		unset($data['cat_option']);
		unset($data['country_option']);
		
		if($this->session->userdata('customer_id'))
		{
			$data['customer_id']= $this->session->userdata('customer_id');
		}
		else
		{
			$email_list_id = saveEmailList(@$data['pm_email'], 1, 'S', 'IN_FEEDBACK_CONTACT', 12); //save email_list table
			
			//saved to customer table
			$grpid = getField('customer_group_id','customer_group','customer_group_type','S');
			$customerArr = array(
				'customer_firstname' => (@$data['pm_name']) ? @$data['pm_name'] : 'Feedback User '.$cat_option,
				'email_list_id' => @$email_list_id,
				'customer_emailid' => @$data['pm_email'],
				'customer_phoneno' => (@$data['pm_phoneno']) ? @$data['pm_phoneno'] : '',
				'customer_group_id' => @$grpid,
				'customer_ip_address' => @$this->input->ip_address()
			);
			$data['customer_id'] = saveCustomer($data['pm_email'], $customerArr);			
		}
		
		$other_opt="";
		if(!empty($cat_option))
			$other_opt.= "Category: ".$cat_option."<br>";
		if(!empty($country_option))
			$other_opt.= "Country: ".$country_option."<br>";
		
		$data['pm_message'] = $other_opt.$data['pm_message'];
		$data['pm_ip_address'] = $_SERVER['REMOTE_ADDR'];
		$data['manufacturer_id'] = MANUFACTURER_ID;
		$this->db->insert('private_message',$data);
		
		$adminEmail = getField('config_value','configuration','config_key','CONTACT_EMAIL'); //get contact email
		$subject = baseDomain().' - Request for Feedback User '.$cat_option;
		$mail_body = "<table>
					  <tr><th>" .$other_opt. "Email: </th><td>".@$data['pm_email']."</td></tr>
					  <tr><th>Ip Address: </th><td>".@$this->input->ip_address()."</td></tr>
					 </table>";
		sendMail($adminEmail, $subject, $mail_body);            // send notification to admin email
		
	}

/**
 * HELD for removal
 * @deprecated
 * @abstract function will save newsletter subscriber
 */
// 	function newsletter()
// 	{
// 		$data = $this->input->post();
// 		$email_list_id = saveEmailList($data['newsletter_email'], 2, 'S', 'SUBSCRIBED',5); //save email_list table
		
// 		$grpid = getField('customer_group_id','customer_group','customer_group_type','S');
		
// 		$customerArr = array(
// 			'customer_firstname' => 'Subscribe User',
// 			'email_list_id' => @$email_list_id,
// 			'customer_emailid' => @$data['newsletter_email'],
// 			'customer_group_id' => @$grpid,
// 			'customer_ip_address' => @$this->input->ip_address()
// 		);
		
// 		if($this->session->userdata('customer_id'))
// 			$data['customer_id']= $this->session->userdata('customer_id');
// 		else
// 			$data['customer_id'] = saveCustomer($data['newsletter_email'], $customerArr); //save to customer		
		
// 		$data['email_list_id'] = $email_list_id;
// 		$data['newsletter_ip_address']= @$this->input->ip_address();
		
// 		$this->db->insert('newsletter_subscriber ',$data);
		
// 		$subject = 'Thank You for Subscribing at Stationery.com';
// 		$mail_body = $this->load->view('templates/newsletter-subscribe','',TRUE);
// 		$mail_body .= $this->load->view('templates/footer-template',array('email_id'=>$data['newsletter_email']),TRUE);
// 		sendMail($data['newsletter_email'], $subject, $mail_body);
// 	}
	
/**
 * @abstract function will get currency data
 */	
	function getCurrencyData()
	{
		return changeDefaultCurrency( $this->input->post('currency_id') );
	}
	
/**
 * @author Cloudwebs
 * @abstract function will show various nitifications to admin users
 *	
 */
	function updateNotifications()
	{
		$resArr = array();
		$CI =& get_instance();
		$res = $CI->db->query("SELECT * FROM configuration WHERE config_key IN ('ORDER_LAST_NOTIFIED_ID','CUSTOMER_LAST_NOTIFIED_ID','PRIVATE_MSG_LAST_NOTIFIED_ID')")->result_array();
		
		if(!empty($res))
		{
			$resArr['type'] = 'success';	
			foreach($res as $k=>$ar)
			{
				if($ar['config_key']=='ORDER_LAST_NOTIFIED_ID')
				{
					$resOrd = $CI->db->query("SELECT COUNT(order_id) as Count,max(order_id) as Max from orders WHERE order_id>".$ar['config_value']."")->row_array();
					if(!empty($resOrd))
					{
						$resArr['ord_cnt'] = $resOrd['Count'];
						$resArr['ord_last_id'] = $resOrd['Max'];
					}
				}
			}
		}
		else
		{
			$resArr['type'] = 'error';	
		}
		
		unset($CI);
	}
/**
 * @abstract function will save request ring sizer,customer,customer_address,send_email_history
 */
	function saveOrderRingSizer()
	{
		$data = $this->input->post();
		$emailArr = $this->db->where('customer_emailid',@$data['customer_emailid'])->get('customer')->row_array();
		
		if(!empty($emailArr))
			$custid = $emailArr['customer_id'];
		else
			$custid = @$this->session->userdata('customer_id');
		
		if(@$this->input->post('YesBtn') == 'Yes' || @$custid)
		{
			//saved to ring sizer request
			$ringSizerRequestArr = array(
				'customer_id' => @$custid
			);
			$this->db->insert('ring_sizer_request',$ringSizerRequestArr);
		}
		else
		{
			$this->load->helper('string');
			$user_pass = random_string('alnum', 6);
			$salt = md5(random_string('alnum','15'));
			$grpid = getField('customer_group_id','customer_group','customer_group_type','U');
			
			$data['email_list_id'] = saveEmailList(@$data['customer_emailid'], 1, 'N', 'RING_SIZER_REGISTER',32); //save email_list table
			
			//saved to customer table
			$customerArr = array(
				'customer_firstname' => @$data['customer_firstname'],
				'customer_lastname' => @$data['customer_lastname'],
				'email_list_id' => @$data['email_list_id'],
				'customer_emailid' => @$data['customer_emailid'],
				'customer_gender' => @$data['customer_gender'],
				'customer_phoneno' => @$data['customer_phoneno'],
				'customer_group_id' => @$grpid,
				'customer_password' => md5(@$user_pass.$this->config->item('encryption_key')),
				'customer_salt' => @$salt,
				'customer_ip_address' => @$this->input->ip_address()
			);
			/*$this->db->insert('customer',$customerArr); 
			$customer_id = $this->db->insert_id();*/
			
			$customer_id = saveCustomer($data['customer_emailid'], $customerArr); //save to customer
			
			$dataPin['pincode'] =  @$data['customer_pincode'];
			$dataPin['customer_address_landmark_area'] =  '';
			$dataPin['address_city'] =  @$data['address_city'];
			$dataPin['state_id'] =  @$data['state_id'];
			
			//saved to customer address table
			$customerAddArr = array(
				'customer_id' => @$customer_id,
				'customer_address_firstname' => @$data['customer_firstname'],
				'customer_address_lastname' => @$data['customer_lastname'],
				'customer_address_address' => @$data['customer_address'],
				'country_id' => @$data['country_id'],
				'customer_address_state_id' => @$data['state_id'],
				'customer_address_city' => @$data['address_city'],
				'customer_address_zipcode' => getPincodeId($dataPin),
				'customer_address_phone_no' => @$data['customer_phoneno']
			);		
			$this->db->insert('customer_address',$customerAddArr);
			
			//saved to ring sizer request
			$ringSizerRequestArr = array(
				'customer_id' => @$customer_id
			);
			$this->db->insert('ring_sizer_request',$ringSizerRequestArr);
			
			$data['email_address'] = $data['customer_emailid'];
			$data['text_password'] = $user_pass;
			$data['activation_link'] = base_url('activateAccount?signature='.$salt);
			$adminEmail = getField('config_value','configuration','config_key','CONTACT_EMAIL'); //get admin email
			
			$subject = baseDomain().' - Update for your requested free ring sizer';
			$mail_body = $this->load->view('templates/ring-sizer-request',$data,TRUE);
			$mail_body .= $this->load->view('templates/footer-template',array( 'email_list_id'=>$data['email_list_id'],'email_id'=>$data['customer_emailid']),TRUE);
			sendMail($data['customer_emailid'], $subject, $mail_body);            // send to customer email
			sendMail($adminEmail, $subject, $mail_body);            // send notification to admin email
			
			//saved to send email history
			$sendEmailHistoryArr = array(
				'es_from_emails' => @$adminEmail,
				'es_to_emails' => @$data['customer_emailid'],
				'es_subject' => @$subject,
				'es_message' => @$mail_body,
				'es_ip_address' => @$this->input->ip_address()
			);
			$this->db->insert('email_send_history',$sendEmailHistoryArr);
		}
		
	}
/**
 * @abstract function will save request ask the expert
 */
	function saveAskTheExpert()
	{
		$data = $this->input->post();
		$email_id = $data['customer_emailid'];
		$grpid = getField('customer_group_id','customer_group','customer_group_type','U');
		$email_list_id = saveEmailList($email_id, 1, 'S', 'ASK_EXPERT_USER', 3); //save email_list table
		$customer_dob = $data['birthday_year'].'-'.$data['birthday_month'].'-'.$data['birthday_day'];
		$customer_anni_date = $data['anniversary_year'].'-'.$data['anniversary_month'].'-'.$data['anniversary_day'];
		
		unset($data['birthday_year']);unset($data['birthday_month']);unset($data['birthday_day']);
		unset($data['anniversary_year']);unset($data['anniversary_month']);unset($data['anniversary_day']);
		
		$customerArr = array(
			'customer_firstname' => (@$data['customer_firstname']) ? @$data['customer_firstname'] : 'Ask Expert User',
			'email_list_id' => @$email_list_id,
			'customer_emailid' => @$email_id,
			'customer_phoneno' => @$data['customer_phoneno'],
			'customer_group_id' => @$grpid,
			'customer_dob' => @$customer_dob,
			'customer_anni_date' => @$customer_anni_date,
			'budget' => @$data['budget'],
			'product_type' => @$data['product_type'],
			'occassion' => @$data['occassion'],
			'buy_gift' => @$data['buy_gift'],
			'customer_ip_address' => @$this->input->ip_address()
		);
		saveCustomer($email_id, $customerArr);
		
		$adminEmail = getField('config_value','configuration','config_key','CONTACT_EMAIL'); //get admin email
		$subject = baseDomain().' - Request for Ask The Expert User';
		$mail_body = "<table>
					  <tr><th>Name: </th><td>".@$data['customer_firstname']."</td></tr>
					  <tr><th>Phone No.: </th><td>".@$data['customer_phoneno']."</td></tr>
					  <tr><th>Email: </th><td>".@$email_id."</td></tr>
					  <tr><th>Ip Address: </th><td>".@$this->input->ip_address()."</td></tr>
					 </table>";
		sendMail($adminEmail, $subject, $mail_body);            //send notification to admin email
	}
	
	function review()
	{
		$data = $this->input->post();
		$data['customer_id'] = $this->session->userdata('customer_id');
		
		$reviewArr = array(
				'product_id' => @$data['product_id'],
				'customer_id' => @$data['customer_id'],
				'user_type' => 'C',
				'product_review_rating' => @$data['product_review_rating'],
				'product_review_description' => @$data['product_review_description'],
				'product_review_ipaddress' => @$this->input->ip_address(),
				'product_review_status' => '1'
		);
		
		/*$data['customer_id'] = saveCustomer($data['pm_email'], $reviewArr);
				
		$data['pm_ip_address']= $_SERVER['REMOTE_ADDR'];
		$data['manufacturer_id'] = MANUFACTURER_ID;*/
		$this->db->insert('product_review',$reviewArr);

		//sales@Stationery.com
		$adminEmail = getField('config_value','configuration','config_key','SALES_EMAIL'); //get admin sales email
		$customer = exeQuery("SELECT customer_emailid, customer_firstname, customer_phoneno FROM customer WHERE customer_id = ".$data['customer_id']);
		
		$subject = baseDomain().' - Request for Ask to Verify from New Review';
		$mail_body = "<b>Name: </b>".@$customer['customer_firstname']."<br>
					  <b>Phone No.: </b>".@$customer['customer_phoneno']."<br>
					  <b>Email: </b>".@$customer['customer_emailid']."<br>
					  <b>Ip Address: </b>".@$this->input->ip_address()."<br>
					 <br>";
		$mail_body .= "Please <a href=".site_url('admin/product_review').">Click here </a>to Verify Product Review";
		
		sendMail($adminEmail, $subject, $mail_body);            //send notification to admin email
	}
	
	/**
	 * write code 27-Apr-2015
	 */
	function inviteFriend()
	{
		$data = $this->input->post();
		$data['email_list_id'] = getField("email_list_id", "email_list", "email_id", $data['customer_partner_id']);//04-04-2016 used to email unsubcribe 
		
		$subject = 'Your friend has invited you to join '.baseDomain();
		$mail_body = $this->load->view('templates/invite-friends', $data, TRUE);
		$mail_body .= $this->load->view('templates/footer-template', array( 'email_list_id'=>$data['email_list_id'],'email_id'=>$data['customer_partner_id'] ), TRUE); 
		
		sendMail( $data['customer_partner_id'], $subject, $mail_body);
	}
}