<?php
		
	function getLangMsg( $type, $val='', $is_all=false ) 
	{
		$labelA = array ();

		if( $type === 'hm' )
		{
			return 'Home'; 
		}
		else if( $type === 'lgn' )
		{
			return 'Login';
		}
		else if( $type === 'haq' )
		{
			return 'have a question?';
		}
		else if( $type === 'ma' )
		{
			return 'My Account';
		}
		else if( $type === 'reg' )
		{
			return 'Register';
		}
		else if( $type === 'r_product' )
		{
			return 'Related products';
		}
		else if( $type === 'lo' )
		{
			return 'Logout';
		}
		else if( $type === 'lc' )
		{
			return 'Live Chat';
		}
		else if( $type === 'sb' )
		{
			return 'Cart';
		}
		else if( $type === 'll' )
		{
			return 'Love List';
		}
		else if( $type === 'r_l' )
		{
			return 'Refferal Page';
		}
		else if( $type === 'contact' )
		{
			return 'Contact';
		}
		else if( $type === 'pdtail' )
		{
			return 'Product Details';
		}
		else if( $type === 'prd' )
		{
			return 'Products';
		}
		else if( $type === 'pcode' )
		{
			return 'Product Code';
		}
		else if( $type === 'pname' )
		{
			return 'Product Name';
		}
		else if( $type === 'width' )
		{
			return 'Width';
		}
		else if( $type === 'height' )
		{
			return 'Height';
		}
		else if( $type === 'price' )
		{
			return 'Price';
		}
		else if( $type === 'dics' )
		{
			return 'Discount';
		}
		else if( $type === 'Stationery' )
		{
			return 'Stationery';
		}
		else if( $type === 'au' )
		{
			return 'About US';
		}
		else if( $type === 'dro' )
		{
			return 'About Draw';
		}
		else if( $type === 'pp' )
		{
			return 'Privacy Policy';
		}
		else if( $type === 'tc' )
		{
			return 'Terms & Conditions';
		}
		else if( $type === 'itc' )
		{
			return 'I agree to the Terms & Condition';
		}
		else if( $type === 'rp' )
		{
			return 'Return Policy';
		}
		else if( $type === 'faqs' )
		{
			return 'FAQs';
		}
		else if( $type === 'cu' )
		{
			return 'CONTACT US';
		}
		else if( $type === 'en_us' )
		{
			return 'English';
		}
		else if( $type === 'hi' )
		{
			return 'Hindi';
		}
		else if( $type === 'gu' )
		{
			return 'Gujrati';
		}
		else if( $type === 'pps' )
		{
			return 'POPULAR POSTS';
		}
		else if( $type === 'lid' )
		{
			return 'Login ID';
		}
		else if( $type === 'lidmsg' )
		{
			return '(Login ID Should be your Email ID.)';
		}
		else if( $type === 'fyp' )
		{
			return 'Forgot Your Password?';
		}
		else if( $type === 'info' )
		{
			return 'Information';
		}
		else if( $type === 'u_info' )
		{
			return 'User Information';
		}
		else if( $type === 'as')
		{
			return 'About Shop';
		}
		else if( $type === 'add' )
		{
			return '233, Royal Plaza,<br> Ved Road, Surat - 395004 <br> Gujarat, India.';
		}
		else if( $type === 'address' )
		{
			return 'Address';
		}
		else if( $type === 'action' )
		{
			return 'Action';
		}
		else if( $type === 'nl' )
		{
			return 'Newsletter';
		}
		else if( $type === 'sm' )
		{
			return 'Sidebar Menu';
		}
		else if( $type === 'veg' )
		{
			return 'Vegetables';
		}
		else if( $type === 'frt' )
		{
			return 'Fruits';
		}
		else if( $type === 'phtl' )
		{
			return 'Price: High to Low';
		}
		else if( $type === 'plth' )
		{
			return 'Price: Low to High';
		}
		else if( $type === 'p_id' )
		{
			return 'Product ID';
		}
		else if( $type === 'na' )
		{
			return 'New Arrivals';
		}
		else if( $type === 'spoff' )
		{
			return 'Offers';
		}
		else if( $type === 'back' )
		{
			return 'Back';
		}
		else if( $type === 'fgmsg' )
		{
			return 'Please enter your email id registered on Stationery.com. Password email link will be sent on this email id.';
		}
		else if( $type === 'ea' )
		{
			return 'Email Address:';
		}
		else if( $type === 'ear' )
		{
			return 'Email Address *';
		}
		else if( $type === 'eea' )
		{
			return 'Enter Email Address';
		}
		else if( $type === 'wrsn' )
		{
			return 'we are in social networks';
		}
		else if( $type === 'qtyw' )
		{
			return 'Please select quantity.';
		}
		else if( $type === 'form_err' )
		{
			return 'Please check form there is some error.'; 
		}
		else if( $type === 'fs' )
		{
			return 'Free Shipping';
		}
		else if( $type === 'ship' )
		{
			return 'SHIPPING';
		}
		else if( $type === 'free' )
		{
			return 'Free';
		}
		else if( $type === 'ttl' )
		{
			return 'Total';
		}
		else if( $type === 't_id' )
		{
			return 'Transaction ID';
		}
		else if( $type === 'dt' )
		{
			return 'Date';
		}
		else if( $type === 'status' )
		{
			return 'Status';
		}
		else if( $type === 'dlv' )
		{
			return 'Delivered';
		}
		else if( $type === 'hac' )
		{
			return 'HAVE A COUPON?';
		}
		else if( $type === 'lex' )
		{
			return 'Lifetime Exchange';
		}
		else if( $type === 'dr' )
		{
			return 'Easy Returns';
		}
		else if( $type === 'dff' )
		{
			return 'Different payment methods';
		}
		else if( $type === 'pay_meth' )
		{
			return 'Payment Methods';
		}
		else if( $type === 'c_on_del' )
		{
			return 'Cash On Delivery';
		}
		else if( $type === 'payu' )
		{
			return 'payU';
		}
		else if( $type === 'pr' )
		{
			return 'Price';
		}
		else if( $type === 'ship_meth' )
		{
			return 'SHIPPING METHOD';
		}
		else if( $type === 'mr_pr' )
		{
			return 'Market Price';
		}
		else if( $type === 'or_pr' )
		{
			return 'Our Price';
		}
		else if( $type === 'tt' )
		{
			return 'Total';
		}
		else if( $type === 'login_as' )
		{
			return 'Logged in as '.$val;
		}
		else if( $type === 'lo_as' )
		{
			return 'Logout and log in as different user.';
		}
		else if( $type === 'wemp' )
		{
			return 'Wishlist is Empty';
		}
		else if( $type === 'cemp' )
		{
			return 'Cart is Empty';
		}
		else if( $type === 'rvs' )
		{
			return 'Your Review has been submitted. Thank you!';
		}
		else if( $type === 'pin' )
		{
			return 'Pincode';
		}
		else if( $type === 'pinr' )
		{
			return 'Pincode *';
		}
		else if( $type === 'code' )
		{
			return 'Code';
		}
		else if( $type === 'atb' )
		{
			return 'Add to bag';
		}
		else if( $type === 'siwf' )
		{
			return 'Share item with friends';
		}
		else if( $type === 'item' )
		{
			return 'Item';
		}
		else if( $type === 'qty' )
		{
			return 'Quantity';
		}
		else if( $type === 'dtail' )
		{
			return 'Details';
		}
		else if( $type === 'rvu' )
		{
			return 'Reviews';
		}
		else if( $type === 'm_ordr' )
		{
			return 'My Orders';
		}
		else if( $type === 'vmohis' )
		{
			return 'View my order history';
		}
		else if( $type === 'm_t' )
		{
			return 'My transactions';
		}
		else if( $type === 'trean' )
		{
			return 'Transactions';
		}
		else if( $type === 'l_out' )
		{
			return 'Logout';
		}
		else if( $type === 'm_a' )
		{
			return 'My Account';
		}
		else if( $type === 'o_ret' )
		{
			return 'Order Returns';
		}
		else if( $type === 'o_info' )
		{
			return 'Order Information';
		}
		else if( $type === 'o_id' )
		{
			return 'Order Id';
		}
		else if( $type === 'disc' )
		{
			return 'Discription';
		}
		else if( $type === 'amt' )
		{
			return 'Amount';
		}
		else if( $type === 'bal' )
		{
			return 'Balance';
		}
		else if( $type === 'disc_amt' )
		{
			return 'Discount Amount';
		}
		else if( $type === 'trans' )
		{
			return 'Transactions';
		}
		else if( $type === 'mybal' )
		{
			return 'My Balance';
		}
		else if( $type === 'lan' )
		{
			return 'Language';
		}
		else if( $type === 'n_tran' )
		{
			return 'No transactions yet!';
		}
		else if( $type === 'f_c' )
		{
			return 'Friend Code';
		}
		else if( $type === 'n_wish' )
		{
			return "You haven't inserted any Wishlist yet.";
		}
		else if( $type === 'cur_bal' )
		{
			return 'Your current balance is: ';
		}
		else if( $type === 'emainfo' )
		{
			return 'Edit my account information';
		}
		else if( $type === 'city' )
		{
			return 'City';
		}
		else if( $type === 'cityr' )
		{
			return 'City *';
		}
		else if( $type === 'fs' )
		{
			return 'Free shipping, same day delivery.';
		}
		else if( $type === 'selshipadd' )
		{
			return 'Select this as Shipping Address';
		}
		else if( $type === 'sifb' )
		{
			return 'Sign in with your facebook account';
		}
		else if( $type === 'save' )
		{
			return 'Save';
		}
		else if( $type === 'tyo' )
		{
			return 'Thank you for your order!';
		}
		else if( $type === 'o_ordr' )
		{
			return 'Your Order';
		}
		else if( $type === 'p_ordr' )
		{
			return 'Place Order';
		}
		else if( $type === 'previous_page' )
		{
			return 'Go to previous step';
		}
		else if( $type === 'prv_add' )
		{
			return 'Previous Addresses';
		}
		else if( $type === 'sel_bill_add' )
		{
			return 'Select this as Billing Address';
		}
		else if( $type === 'sam_ship_add' )
		{
			return 'Same as the Shipping Address';
		}
		else if( $type === 'war' )
		{
			return 'WRITE A REVIEW';
		}
		else if( $type === 'sr' )
		{
			return 'Submit Review';
		}
		else if( $type === 'war_desc' )
		{
			return 'Now please write a (short) review....(min. 10, max. 300 characters)';
		}
		else if( $type === 'yr' )
		{
			return 'Your Rate';
		}
		else if( $type === 'cart_item' )
		{
			return "You haven't inserted any address yet.";
		}
		else if( $type === 'cont' )
		{
			return 'Continue';
		}
		else if( $type === 'shipadd' )
		{
			return 'Shipping Address';
		}
		if( $type === 'and' )
		{
			return 'and';
		}
		else if( $type === 'payment' )
		{
			return 'Payment';
		}
		else if( $type === 'c_ordr' )
		{
			return 'Confirm Ordered';
		}
		else if( $type === 'country' )
		{
			return 'Country';
		}
		else if( $type === 'state' )
		{
			return 'State';
		}
		else if( $type === 'l_area' )
		{
			return 'Landmark Area';
		}
		else if( $type === 'l_arear' )
		{
			return 'Landmark Area *';
		}
		else if( $type === 'p_t' )
		{
			return 'Province / Territory';
		}
		else if( $type === 's_add' )
		{
			return 'Street Address';
		}
		else if( $type === 's_addr' )
		{
			return 'Street Address *';
		}
		else if( $type === 'bill_info' )
		{
			return 'Billing Information';
		}
		else if( $type === 'phone' )
		{
			return 'Phone';
		}
		else if( $type === 'phoner' )
		{
			return 'Phone *';
		}
		else if( $type === 'instock' )
		{
			return 'In Stock';
		}
		else if( $type === 'soldout' )
		{
			return 'Sold Out';
		}
		else if( $type === 'available' )
		{
			return 'Availability';
		}
		else if( $type === 'available' )
		{
			return 'Maximum purchase quantity';
		}
		else if( $type === 'p_s' )
		{
			return 'Product status';
		}
		else if( $type === 'palias' )
		{
			return 'Product Alias';
		}
		else if( $type === 'psku' )
		{
			return 'Product SKU';
		}
		else if( $type === 'general' )
		{
			return 'General';
		}
		else if( $type === 's_s' )
		{
			return 'Side Stone';
		}
		else if( $type === 'metal' )
		{
			return 'Metal';
		}
		else if( $type === 'g_i' )
		{
			return 'General Information';
		}
		else if( $type === 'c_s' )
		{
			return 'Center Stone';
		}
		else if( $type === 'a_fill' )
		{
			return 'All fields marked with ';
		}
		else if( $type === 'stloc' )
		{
			return 'Store locations';
		}
		else if( $type === 'msg' )
		{
			return 'Message';
		}
		else if( $type === 'fp' )
		{
			return 'Featured products';
		}
		else if( $type === 'iar' )
		{
			return "Registered Customers";
		}
		else if( $type === 'iarl' )
		{
			return "If you have an account with us, please log in.";
		}
		else if( $type === 'email' )
		{
			return 'E-Mail';
		}
		else if( $type === 'newc' )
		{
			return 'NEW CUSTOMERS';
		}
		else if( $type === 'new_add' )
		{
			return 'Create New Address';
		}
		else if( $type === 'sup' )
		{
			return 'Support';
		}
		else if( $type === 'pn' )
		{
			return 'Partnership';
		}
		else if( $type === 'rnr' )
		{
			return 'Returns and Refunds';
		}
		else if( $type === 'cform' )
		{
			return 'Contact Form';
		}
		else if( $type === 'nm' )
		{
			return 'Name';
		}
		else if( $type === 'nmr' )
		{
			return 'Name *';
		}
		else if( $type === 'bts' )
		{
			return 'Back to shop';
		}
		else if( $type === 'c_pass' )
		{
			return 'Current Password';
		}
		else if( $type === 'cng_pass' )
		{
			return 'Change password';
		}
		else if( $type === 'm_a_b' )
		{
			return 'Modify my address book';
		}
		else if( $type === 'obl_pay' )
		{
			return ' Dear customer you are obliged to pay';
		}
		else if( $type === 'm_w_l' )
		{
			return 'Modify my wishlist';
		}
		else if( $type === 'o_h' )
		{
			return 'Order History';
		}
		else if( $type === 'regulation' )
		{
			return ' at the time of delivery, as per the tax and regulations of ';
		}
		else if( $type === 'a_book' )
		{
			return 'Address Books';
		}
		else if( $type === 'a_bok' )
		{
			return 'Address Book';
		}
		else if( $type === 'e_acc' )
		{
			return 'Edit Account';
		}
		else if( $type === 'w_l' )
		{
			return 'Wish List';
		}
		else if( $type === 'edit' )
		{
			return 'Edit';
		}
		else if( $type === 's_u_n' )
		{
			return 'Subscribe/Unsubscribe to newsletter';
		}
		else if( $type === 'n_pass' )
		{
			return 'New Password';
		}
		else if( $type === 'cf_pass' )
		{
			return 'Confirm Password';
		}
		else if( $type === 'f_name' )
		{
			return 'First Name';
		}
		else if( $type === 'l_name' )
		{
			return 'Last Name';
		}
		else if( $type === 'l_namer' )
		{
			return 'Last Name *';
		}
		else if( $type === 's_g' )
		{
			return 'Select Gender';
		}
		else if( $type === 'm' )
		{
			return 'Male';
		}
		else if( $type === 'f' )
		{
			return 'Female';
		}
		else if( $type === 'type' )
		{
			return 'Type';
		}
		else if( $type === 'shape' )
		{
			return 'Shape';
		}
		else if( $type === 'purity' )
		{
			return 'Purity';
		}
		else if( $type === 'no' )
		{
			return 'NO';
		}
		else if( $type === 'clr' )
		{
			return 'Color';
		}
		else if( $type === 'weight' )
		{
			return 'Weight';
		}
		else if( $type === 'thanks' )
		{
			return "Thank you for registering with our Stationery online jewellery! <br /> Notification to activate your account has been send to your email. If you have any question about the operation of this online diamond jewellery, please";
		}
		else if( $type === 'bttl' )
		{
			return 'BAG TOTALS';
		}
		else if( $type === 'sttl' )
		{
			return 'SUB TOTAL';
		}
		else if( $type === 'yahbc' )
		{
			return 'Your Account Has Been Created!';
		}
		else if( $type === 'checkemail' )
		{
			return 'Please check your email and click the link to activate!';
		}
		else if( $type === 'billadd' )
		{
			return 'Billing Address';
		}
		else if( $type === 'gsst' )
		{
			return 'Stationery Fashion';
		}
		else if( $type === 'nry' )
		{
			return 'No reviews yet!';
		}
		else if( $type === 'pass' )
		{
			return 'Password';
		}
		else if( $type === 'psr' )
		{
			return 'Password *';
		}
		else if( $type === 'cwp' )
		{
			return 'Continue without password (You can checkout as guest)';
		}
		else if( $type === 'or' )
		{
			return 'OR';
		}
		else if( $type === 'sort' )
		{
			return 'SORT BY';
		}
		else if( $type === 'select' )
		{
			return 'SELECT';
		}
		else if( $type === 'pop' )
		{
			return 'Popular';
		}
		else if( $type === 'new' )
		{
			return 'New';
		}
		else if( $type === 'req' )
		{
			return ' are required';
		}
		else if( $type === 'back_top' )
		{
			return ' Back to Top';
		}
		else if( $type === 'order_success' )
		{
			return 'Your order has been successfully processed!';
		}
		else if( $type === 'order_success' )
		{
			return 'Your Order Number is  ';
		}
		else if( $type === 's_n' )
		{
			return "Address inserted successfully.";
		}
		else if( $type === 'order_history' )
		{
			return 'You can view your order history by going to the ';
		}
		else if( $type === 'show_page' )
		{
			return ' page and by clicking on ';
		}
		else if( $type === 'history' )
		{
			return ' history ';
		}
		else if( $type === 'd_question' )
		{
			return ' Please direct any questions you have to the ';
		}
		else if( $type === 'thnks' )
		{
			return ' Thanks for shopping with us online! ';
		}
		else if( $type === 'order_no' )
		{
			return ' Order Number ';
		}
		else if( $type === 'order_place' )
		{
			return 'Your Order Has Been Placed!';
		}
		else if( $type === 'order_place_failed' )
		{
			return 'Your Order Has Been Failed!';
		}
		else if( $type === 'order_failed' )
		{
			return 'Your order has been failed!';
		}
		else if( $type === 'o_sumery' )
		{
			return 'Order Summary';
		}
		else if( $type === 't_detail' )
		{
			return 'Tracking Details';
		}
		else if( $type === 'ihap' )
		{
			return 'I have an account and password (Login to checkout faster)';
		}
		else if( $type === 'reset_pass' )
		{
			return 'Kindly reset your password are as follow:';
		}
		else if( $type === 'c_h' )
		{
			return 'Click Here';
		}
		else if( $type == "ttl_amt")
		{
			return "Total Amt";
		}
		else if( $type === 'enjoy' )
		{
			return 'We hope you will enjoy shopping at ';
		}
		else if( $type === 'fb' )
		{
			return 'Follow Us on Facebook';
		}
		else if( $type === 'gplus' )
		{
			return 'Follow Us on Google+';
		}
		else if( $type === 'invfr' )
		{
			return 'Invite Friends';
		}
		else if( $type === 'tyf' )
		{
			return 'Write your friend a message';
		}
		else if( $type === 'wfmsg' )
		{
			return 'Insert your friends email address';
		}
		else if( $type === "flt" )
		{
			return "  FILTER"; 
		}
		else if( $type === "wm" )
		{
			return "Women";
		}
		else if( $type === "mn" )
		{
			return "Men";
		}
		else if( $type === "crdt" )
		{
			return "Credit";
		}
		else if( $type === "dbt" )
		{
			return "Debit";
		}
		else if( $type === "prd" )
		{
			return "Price Detail";
		}
		else if( $type == "s_msg" )
		{
			return "Message Send Successful";
		}
		else if( $type == "l_suc" )
		{
			return "Login Success";
		}
		else if( $type == "e_ph")
		{
			return "Enter Phone";
		}
		else if( $type == "s_reg")
		{
			return "Register Successfull.";
		}
		else if( $type == "s_f_msg")
		{
			return "Password Sent Successfull.";
		}
		else if( $type == "iin")
		{
			return "Invalid input.";
		}
		else if( $type == "c_p_del")
		{
			return "Cart Product Delete Successful";
		}
		else if( $type == "l_area")
		{
			return "Landmark Area";
		}
		else if( $type == "outstokw")
		{
			return "This product is sold out.";
		}
		else if( $type == "outstokc")
		{
			return "This product is sold out, product will be excluded from checkout.";
		}
		else if( $type == "atw")
		{
			return "Add to wishlist";
		}
		else if( $type == "conts")
		{
			return "Continue Shopping";
		}
		else if( $type == "acnt")
		{
			return "Account";
		}
		else if( $type == "rest_res_err")
		{
			return "Internet response error.";
		}
		else if( $type == "is_req_err")
		{
			return " is required.";
		}
		else if( $type == "pl")
		{
			return "Product List";
		}
		else if( $type == "pd")
		{
			return "Product Details";
		}
		else if( $type == "cl")
		{
			return "Cart List";
		}
		else if( $type == "co")
		{
			return "Checkout";
		}
		else if( $type == "ty")
		{
			return "Thank You";
		}
		else if( $type == "o_f")
		{
			return "Order Failed";
		}
		else if( $type == "usr")
		{
			return "User";
		}
		else if( $type == "shipp")
		{
			return "Shipping";
		}
		else if( $type == "conf")
		{
			return "Confirmation";
		}
		else if( $type == "cond")
		{
			return "Are you sure to delete?";
		}
		else if( $type == "in_comp_cab")
		{
			return "Incompatible action with current selection.";
		}
		else if( $type == "o_t")
		{
			return "Order Tracking";
		}
		else if( $type == "c_a")
		{
			return "Create Address";
		}
		else if( $type == "trk")
		{
			return "Track";
		}
		else if( $type == "n_reg")
		{
			return "Create Account";
		}
		else if( $type == "f_pass")
		{
			return "Forgot Password";
		}
		else if( $type == "s/g/i" )
		{
			return "Surat, Gujarat,<br>India.";
		}
		else if( $type == "ino" )
		{
			return "Invoice No";
		}
		else if( $type == "mop" )
		{
			return "Mode Of Payment";
		}
		else if( $type == "anau" )
		{
			return "Address not available, may be deleted by user.";
		}
		else if( $type == "desc" )
		{
			return "Description";
		}
		else if( $type == "up" )
		{
			return "Unit Price";
		}
		else if( $type == "q" )
		{
			return "Qty";
		}
		else if( $type == "ic" )
		{
			return "Item Code";
		}
		else if( $type == "pw" )
		{
			return "Product Weight";
		}
		else if( $type == "nia" )
		{
			return "No products available";
		}
		else if( $type == "que" )
		{
			return "If you have any questions, call us on ".getField('config_value','configuration','config_key','TOLL_FREE_NO')." or mail us at ".getField('config_value','configuration','config_key','CONTACT_EMAIL');
		}
		else if( $type == "vat" )
		{
			return "(".$val."% VAT included)";
		}
		else if( $type == "thnk" )
		{
			return "THANK YOU FOR SHOPPING AT Stationery.COM";
		}
		return ""; 
	}
	 
?>