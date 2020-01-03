<?php
/**
 * @package he_: aff_hlp 
 * @author Cloudwebs Tech Dev Team 
 * @version 1.9 
 * @abstract affiliate features helper 
 * @copyright Cloudwebs Tech 
 */

/**
 * affiliate signup campaign credit processing
 * [temp]: HELD FOR REMOVAL
 */
function __cart_hlp_signupAffiliateCreditProcessing($order_id)
{
	$CI =& get_instance();

	if( $CI->session->userdata("ref_c_code") !== FALSE )
	{
		if( isSignupAffiliateCreditAuto() )
		{
			$ref_c_code = $CI->session->userdata("ref_c_code");

			$partner_id = fetchRow("SELECT affiliate_campaign_id, customer_partner_id,c_code,c_type,c_discount_amt FROM affiliate_campaign WHERE c_code = '".$ref_c_code."' " );
			if( !isEmptyArr( $partner_id ) )
			{
				/**
				 * To resolve BUG 246 CASE POINT 12
				 * dont allow referral bonus to same user
				 */
				if( $partner_id['customer_partner_id'] == $CI->session->userdata("customer_id") )
				{
					//SAME user in session detected
					return false;
				}


				//check if page is refreshed then don't allow disount again
				if( !checkIfRowExist(" SELECT 1 FROM customer_account_manage WHERE customer_id=".$partner_id['customer_partner_id']."
									  AND order_id=".$order_id."
									  AND customer_account_manage_entry_type=1 ") )
				{
					//check if affiliate partner had once got the balance from this user order then dont allow balance again
					if( !checkIfRowExist( "SELECT 1 FROM orders WHERE affiliate_campaign_id=".$partner_id["affiliate_campaign_id"]."
										  AND customer_id IN ( SELECT customer_id FROM orders WHERE order_id=".$order_id." ) " ) )
					{
						query( " UPDATE orders SET affiliate_campaign_id=".$partner_id["affiliate_campaign_id"]." WHERE order_id=".$order_id." " );
							
						hecam_bucksTransaction(true, 0, $partner_id['customer_partner_id'], $order_id, 0, $partner_id['c_discount_amt'], 0, 1);
					}
				}
			}
				
		}
	}
}

/**
 * affiliate signup campaign credit processing
 */
function cart_hlp_signupAffiliateCreditProcessing($order_id)
{
	$CI =& get_instance();

	if( $CI->session->userdata("ref_c_code") !== FALSE )
	{
		$ref_c_code = $CI->session->userdata("ref_c_code");

		$partner_id = fetchRow("SELECT affiliate_campaign_id, customer_partner_id,c_code,c_type,c_discount_amt FROM affiliate_campaign WHERE c_code = '".$ref_c_code."' " );
		if( !isEmptyArr( $partner_id ) )
		{
			/**
			 * To resolve BUG 246 CASE POINT 12
			 * dont allow referral bonus to same user
			 */
			if( $partner_id['customer_partner_id'] == $CI->session->userdata("customer_id") )
			{
				//SAME user in session detected
				return FALSE;
			}

			/**
			 * check if affiliate partner had once got the balance from this USER's order then dont allow balance again
			 */
			if( !checkIfRowExist( "SELECT 1 FROM orders WHERE affiliate_campaign_id=".$partner_id["affiliate_campaign_id"]."
									  AND customer_id IN ( SELECT customer_id FROM orders WHERE order_id=".$order_id." ) " ) )
			{
				query( " UPDATE orders SET affiliate_campaign_id=".$partner_id["affiliate_campaign_id"]." WHERE order_id=".$order_id." " );
			}
			else
			{
				return FALSE;
			}

			if( isSignupAffiliateCreditAuto() == 1 )
			{
				/**
				 * will check if signup affilicate is credited for this order
				 * OR
				 * check in case if page is refreshed then don't allow discount again.
				 */
				if( !aff_hlp_isSignupAffiliateCredited($order_id, $partner_id['customer_partner_id']) )
				{
					hecam_bucksTransaction(true, 0, $partner_id['customer_partner_id'], $order_id, 0, $partner_id['c_discount_amt'], 0, 1);
				}
			}
				
		}
	}

}

/**
 * affiliate signup campaign credit processing
 */
function aff_hlp_isSignupAffiliateCreditPostOrderProcessingApplicable($order_id, $isSignupAffiliateCreditAutoType)
{
	$CI =& get_instance();

	if( isSignupAffiliateCreditAuto() == $isSignupAffiliateCreditAutoType )
	{
		$row = exeQuery("SELECT ac.affiliate_campaign_id, ac.customer_partner_id  
						 FROM orders o 
						 INNER JOIN 
						 affiliate_campaign ac 
						 ON ac.affiliate_campaign_id=o.affiliate_campaign_id	
						 WHERE order_id=".$order_id." " );
		if( !isEmptyArr($row) && !aff_hlp_isSignupAffiliateCredited($order_id, $row['customer_partner_id']) )
		{
			return TRUE;
		}
	}

	return FALSE; 
} 

/**
 * affiliate signup campaign credit processing post order 
 * either called in auto mode after completion of order 
 * OR
 * explicitly from Admin panel by Admin
 */
function aff_hlp_signupAffiliateCreditPostOrder($order_id)
{
	$CI =& get_instance();
	$affiliate_campaign_id = exeQuery( " SELECT affiliate_campaign_id FROM orders WHERE order_id=".$order_id." ", true, "affiliate_campaign_id" );

	if( !empty($affiliate_campaign_id) )
	{
		$partner_id = fetchRow("SELECT affiliate_campaign_id, customer_partner_id,c_code,c_type,c_discount_amt 
								FROM affiliate_campaign WHERE affiliate_campaign_id = ".$affiliate_campaign_id." " );
		if( !isEmptyArr( $partner_id ) )
		{
			/**
			 * function will check if signup affilicate is credited for this order
			 * than don't allow discount again.
			 */
			if( !aff_hlp_isSignupAffiliateCredited($order_id, $partner_id['customer_partner_id']) )
			{
				hecam_bucksTransaction(true, 0, $partner_id['customer_partner_id'], $order_id, 0, $partner_id['c_discount_amt'], 0, 1);
			}
				
		}
	}

	return FALSE; 
	
}


/**
 * function will check if signup affilicate is credited for this order
 */
function aff_hlp_isSignupAffiliateCredited($order_id, $customer_partner_id)
{
	return checkIfRowExist(" SELECT 1 FROM customer_account_manage WHERE customer_id=".$customer_partner_id."
								  AND order_id=".$order_id."
								  AND customer_account_manage_entry_type=1 ");
}

?>