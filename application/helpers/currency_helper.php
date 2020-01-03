<?php 
/*
++++++++++++++++++++++++++++++++++++++++++++++
       Function will append symbol only in price 
++++++++++++++++++++++++++++++++++++++++++++++
*/
function lp_symbol( $price, $is_schema=false, $currency_id=0)
{
	$span_st = $span_cl = '';
	if($is_schema)
	{
		$span_st = '<span itemprop="price">';
		$span_cl = '</span>';
	}
	
	if( $currency_id == 0 ) 
	{
		$currency_sign = $span_st.CURRENCY_SYMBOL; 
	}
	else
	{
		if( !defined( 'CURRENCY_SYMBOL_'.$currency_id ) )
		{
			currencyConstant( $currency_id );	
		}
		
		$currency_sign = $span_st.constant( 'CURRENCY_SYMBOL_'.$currency_id );
	}

	return $currency_sign . $price . $span_cl;	//return trim(trim(number_format($price,2,'.',','),'0'),'.');
}

/*
++++++++++++++++++++++++++++++++++++++++++++++
       Function will return money symbol with price. 
++++++++++++++++++++++++++++++++++++++++++++++
*/
function lp( $price, $rounding=2, $is_schema=false, $currency_id=0)
{
	$span_st = $span_cl = $currency_code = '';
	if($is_schema)
	{
		$span_st = '<span itemprop="price">';
		$span_cl = '</span>';
	}
	
	if( $currency_id == 0 )
	{
		$currency_sign = $span_st.CURRENCY_SYMBOL;
		$price = round( $price * CURRENCY_VALUE, $rounding);	
		
		//to get currency specific lp_presentable
		$currency_code = CURRENCY_CODE;
	}
	else
	{
		if( !defined( 'CURRENCY_SYMBOL_'.$currency_id ) )
		{
			currencyConstant( $currency_id );	
		}
		
		$currency_sign = $span_st.constant( 'CURRENCY_SYMBOL_'.$currency_id );
		$price = round( $price * constant( 'CURRENCY_VALUE_'.$currency_id ), $rounding);	

		//to get currency specific lp_presentable
		$currency_code = constant( 'CURRENCY_CODE_'.$currency_id );
	}

	return $currency_sign . lp_presentable( $price, $rounding, $currency_code ) . $span_cl;	//return trim(trim(number_format($price,2,'.',','),'0'),'.');
}

/**
 * @author Cloudwebs
 * @abstract Function formats price in presentable format
 * @param $sub_part is figure used to subtract from rounding amount to achive presentable figure
 */
function lp_presentable( $price, $rounding, $currency_code )
{
	if( $currency_code == 'INR' )
		return number_format( $price, 0 );
	else if( $price == 0 )
		return 0;
	else	
		return number_format( round( $price, 0 ) - 0.01, $rounding );	
}

/*
++++++++++++++++++++++++++++++++++++++++++++++
       Function will return base price for applicable currency against INR without currency symbol appended
++++++++++++++++++++++++++++++++++++++++++++++
*/
function lp_base($price, $rounding=2, $currency_id=0, $is_presentable=false)
{
	$currency_code = '';
	if( $currency_id == 0 )
	{
		$price = round( $price * CURRENCY_VALUE, $rounding);	
		
		//to get currency specific lp_presentable
		$currency_code = CURRENCY_CODE;
	}
	else 
	{
		if( !defined( 'CURRENCY_SYMBOL_'.$currency_id ) )
		{
			currencyConstant( $currency_id );
		}
		
		$price = round( $price * constant( 'CURRENCY_VALUE_'.$currency_id ), $rounding);
		
		//to get currency specific lp_presentable
		$currency_code = constant( 'CURRENCY_CODE_'.$currency_id );
	}
	
	if( !$is_presentable )
		return $price;	
	else 
		return lp_presentable( $price, $rounding, $currency_code );	
	
}

/*
++++++++++++++++++++++++++++++++++++++++++++++
       Function will return price in INR from given currency
++++++++++++++++++++++++++++++++++++++++++++++
*/
function lp_rev($price, $currency_id, $rounding=2)
{
	if( !defined( 'CURRENCY_SYMBOL_'.$currency_id ) )
	{
		currencyConstant( $currency_id );	
	}
	
	return  round( $price * ( 1/constant( 'CURRENCY_VALUE_'.$currency_id ) ), $rounding );	//find INR value for applicable currency
}

/**
 * @author Cloudwebs
 * @abstract changes default currency for session
 */
function changeDefaultCurrency( $currency_id=0, $currency_code='' )
{
	$CI =& get_instance();
	$res = null;
	
	if( !empty( $currency_id ) )	
		$res = $CI->db->query('SELECT currency_id, currency_code, currency_symbol, currency_value FROM currency WHERE currency_id='.$currency_id.' ')->row_array(); 
	else if( !empty( $currency_code ) )	
		$res = $CI->db->query(" SELECT currency_id, currency_code, currency_symbol, currency_value FROM currency WHERE currency_code='".$currency_code."' " )->row_array();

	if(!empty($res))	
	{
		setCurrencySession($res);	
		return json_encode( array('type'=>'success', 'msg'=>'') );	
	}	
	else	
		return json_encode( array('type'=>'error', 'msg'=>'Currency not found.') );	
}

/**
 * @author Cloudwebs
 * @abstract sets currency session
 */
function setCurrencySession($res)
{
	$CI =& get_instance();
	$CI->session->set_userdata( $res );
}

/**
 * @author Cloudwebs
 * @abstract function serve as a gate to get currency_id from system database based on currency_code: all library should call this function to get currency_id 
 */
function getCurrenyID( $currency_code )
{
	return getField( "currency_id", "currency", "currency_code", $currency_code );
}

/**
 * function define ccurrency spesific constants
 */
function currencyConstant( $currency_id=0, $currency_code='' ) 
{
	$res = null;
	
	if( !empty($currency_id) )
	{
		$res = exeQuery( " SELECT * FROM currency WHERE currency_id=".$currency_id." " );
		$currency_code = $res['currency_code'];
	}
	else if( !empty($currency_code) )	
	{
		$res = exeQuery( " SELECT * FROM currency WHERE currency_code='".$currency_code."' " );
		$currency_id = $res['currency_id'];
	}
	
	if( !empty( $res ) )
	{
		if(!defined( 'CURRENCY_ID_'.$currency_code ))
			define( 'CURRENCY_ID_'.$currency_code , $res['currency_id'] );
				
		if(!defined( 'CURRENCY_CODE_'.$currency_id ))	
			define( 'CURRENCY_CODE_'.$currency_id , $res['currency_code'] );	
			
		if(!defined( 'CURRENCY_VALUE_'.$currency_id ))
			define( 'CURRENCY_VALUE_'.$currency_id , $res['currency_value'] );	
			
		if(!defined( 'CURRENCY_VALUE_'.$currency_code ))
			define( 'CURRENCY_VALUE_'.$currency_code , $res['currency_value'] );	
			
		if(!defined( 'CURRENCY_SYMBOL_'.$currency_id ))
			define( 'CURRENCY_SYMBOL_'.$currency_id , $res['currency_symbol'] );	
	}
}

?>