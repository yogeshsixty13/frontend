<?php
/**
 * @package he_: front_end_hlp 
 * @author Cloudwebs Tech Dev Team
 * @version 1.9
 * @abstract front end features helper except cart which features are separate in cart helper
 * @copyright Cloudwebs Tech
 */

/**
 * sets in session inventory currently being navigated in front end in product catlog.
 * It will be helpful mainly for search filter rendering and help search cater the result specific to inventory
 */
function he_front_end_hlp_loadCatalogNavigationInventory( $inventory_type_id=0 )
{
	if( !empty( $inventory_type_id ) )
	{
		setInventorySession( inventory_typeKeyForId( $inventory_type_id ) );
	}
}



/*
 * @author   Cloudwebs
 * @abstract function will return current balance of customer
 */
	function getCustBalance($customerId)
	{
		$CI =& get_instance();
		if((int)$customerId!=0)
		{
			$res = $CI->db->query("SELECT customer_bucks FROM customer WHERE customer_id=".$customerId." LIMIT 1")->row_array();
			
			unset($CI);						
			if(!empty($res))
			{
				return $res['customer_bucks'];	
			}
			else
			{
				return 0;	
			}
		}
		else
		{
			return 0;	
		}
	}

/*
+------------------------------------------------------------------+
	@author Cloudwebs
	@return : array of row or null empty array if not specified
+------------------------------------------------------------------+
*/
function FetchLayout($css_class,$where)
{
	$resSlider = executeQuery("SELECT module_manager_serialize_menu,module_manager_table_name,module_manager_field_name,
							  module_manager_primary_id FROM module_manager m INNER JOIN banner_position b 
						ON b.banner_position_id=m.position_id WHERE banner_position_alias='".$css_class."' AND module_manager_status=0");
	if(!empty($resSlider))
	{
		$resMenu = executeQuery("SELECT front_menu_id,front_menu_type_id FROM front_menu ".$where);
		$module_manager_serialize_menu = unserialize($resSlider[0]['module_manager_serialize_menu']);
		if(array_key_exists($resMenu[0]['front_menu_type_id'],$module_manager_serialize_menu))
		{
			if(in_array($resMenu[0]['front_menu_id'],$module_manager_serialize_menu[$resMenu[0]['front_menu_type_id']]))
			{
				return $resSlider;
			}
		}
	}
	return array();
}

/*
+------------------------------------------------------------------+
	Get Country name from country id
	@params-> $country_id :  Country id from country table
+------------------------------------------------------------------+
*/
function gc($country_id)
{
	if($country_id)
		return getField('country_name','country','country_id',$country_id);
	else
		return 'Unknown';
}

/*
+------------------------------------------------------------------+
	Get state name from state id
	@params-> $state_id :  state id from state table
+------------------------------------------------------------------+
*/
function gs($state_id)
{
	if($state_id)
		return getField('state_name','state','state_id',$state_id);
	else
		return 'Unknown';
}

/*
+------------------------------------------------------------------+
	Get Menu icon from menu id
	@params-> $className :  classname from menu table
+------------------------------------------------------------------+
*/
function getMenuIcon($className)
{
	if($className)
		return asset_url().getField('am_icon','admin_menu','am_class_name',$className);
	else
		return asset_url()."images/no-image.jpg";
}

/*
+------------------------------------------------------------------+
	Function will return Form_dropdown for ring_size
	@params-> $metal_price_id : $metal_price_id for which to generate form drop down
+------------------------------------------------------------------+
*/
function getDropDownAryRingSize($metal_price_id, $product_gender, $encode=false, $dropDown=array('' => '-- Select --'))
{
	$CI =& get_instance();
	
	$keyField = 'ring_size_id';
	$valueField = 'ring_size_name';
	$genderDef = 0;
	$diffSign = '-';

	$sql = "select ring_size_id, ring_size_name, ring_size_weight_diff FROM ring_size where region_type='".$CI->session->userdata('country_code')."' AND ring_size_status='0' 
			AND ring_size_gender='".$product_gender."' order by ring_size_sort_order ";
			
	$result = executeQuery( $sql );
	
	if( empty($result) )
	{
		$result = executeQuery( "select ring_size_id, ring_size_name, ring_size_weight_diff FROM ring_size where region_type='IN' AND ring_size_status='0' 
								AND ring_size_gender='".$product_gender."' order by ring_size_sort_order " );
	}
	
	$resMetPrice = $CI->db->query('SELECT mp_price_difference FROM metal_price WHERE metal_price_id='.$metal_price_id.'')->row_array();
	foreach( $result as $res )  
	{
		if($res['ring_size_weight_diff'] < 0)
			$diffSign = '-';
		else if($res['ring_size_weight_diff'] == 0)
			$diffSign = '';
		else if($res['ring_size_weight_diff'] > 0)
			$diffSign = '+';
			
		$key = ($encode) ? _en($res[$keyField]) : $res[$keyField];
		$dropDown[$key] = $res[$valueField] . " " . $diffSign . ( ($diffSign!='') ? lp( abs( round($resMetPrice['mp_price_difference'] * $res['ring_size_weight_diff']) ) ):'');
	}

	return (array)$dropDown;
}


/*
+--------------------------------------------------+
	Function fetch data for pipe string IDs from table name specified
	
+--------------------------------------------------+
*/	
function getGiftData($table_name,$id_field_name,$fetch_field_name,$id_string)
{
	$CI =& get_instance();
	$id_string = explode("|",$id_string);
	if(!is_array($id_string) || sizeof($id_string)==0)
	{
		return '';
	}
	
	$where = " WHERE gift_status=0 AND (";
	foreach($id_string as $k=>$ar)
	{
		$where .= " (".$id_field_name." like ".$ar." OR ".$id_field_name." like '".$ar."|%'  OR ".
				$id_field_name." like '%|".$ar."|%' OR ".$id_field_name." like '%|".$ar."') OR " ;
	}
	$where = substr($where,0,-3).")";
		
	$res = $CI->db->query("SELECT ".$fetch_field_name." FROM ".$table_name.$where)->result_array();
	unset($CI);
	return $res;
}

// country dropdown for admin panel
function loadCountryDropdown($sel='', $extra='onchange="getStateFromCountry(this.value)" ',$name='country')
{
	$CI =& get_instance();
	$res = $CI->db->where('country_status','0')->order_by('country_name')->get('country')->result_array();
	
	$arr = array(''=>'--- Please select country ---');
	foreach($res as $r)
		$arr[$r['country_id']] = $r['country_name']; 
		
	return form_dropdown($name,$arr,$sel,$extra);
}

/**
* LOAD ALL state FROM DB TABLE
* @param $countryid of which load state
* @param $name name of select box
* @param $selid default selected id
* @param $extra extra property of select
*/
function loadStateDropdown($name, $countryid, $selid=0, $extra='')
{
	$CI =& get_instance();
	
	$arr = array(''=>'--- Please select state ---');
	
	$res = $CI->db->where('country_id',$countryid)->order_by('state_name')->get('state')->result_array();
	foreach($res as $r)
		$arr[$r['state_id']] = $r['state_name']; 

	unset($CI);
	return form_dropdown($name,$arr,$selid,$extra);
}	

/*
 * @author   Cloudwebs
 * @abstract function will load city as per state selected
 */
function loadCity($state_id,$sel='')
{
	if(!empty($state_id))
	{
		$CI =& get_instance();

		$res = $CI->db->query('SELECT DISTINCT(cityname) as cityname FROM pincode WHERE state_id='.$state_id.' ORDER BY cityname')->result_array();
		$html ='<option value="">- Select City -</option>';
		if(!empty($res))
		{
			foreach($res as $k=>$ar)
			{
				if($ar['cityname']==$sel)
					$html .= '<option value="'.$ar['cityname'].'" selected="selected">'.$ar['cityname'].'</option>';			
				else
					$html .= '<option value="'.$ar['cityname'].'">'.$ar['cityname'].'</option>';			
			}
		}
		else
			return '<option value="">- No City -</option>';

		unset($CI);
		return $html;
	}
	else
	{
		return '<option value="">-- Select state first --</option>';	
	}
}

/*
 * @author   Cloudwebs
 * @abstract function will load area as per city selected
 */
function loadArea($city_name,$state_id,$sel='')
{
	if($city_name!='')
	{
		$CI =& get_instance();

		$res = $CI->db->query('SELECT areaname FROM pincode WHERE state_id='.$state_id.' AND cityname=\''.$city_name.'\' ORDER BY areaname')->result_array();
		$html ='<option value="" >- Select Area -</option>';
		if(!empty($res))
		{
			foreach($res as $k=>$ar)
			{
				if($ar['areaname']==$sel)
					$html .= '<option value="'.$ar['areaname'].'" selected="selected" >'.$ar['areaname'].'</option>';			
				else
					$html .= '<option value="'.$ar['areaname'].'">'.$ar['areaname'].'</option>';			
			}
		}
		else
			return '<option value="">- No Area -</option>';

		unset($CI);
		return $html;
	}
	else
	{
		return '<option value="">-- Select city first --</option>';	
	}
}
/*
 * @author   Cloudwebs
 * @abstract function will load pincode as per area selected
 */
function loadPincode($area_name,$city_name,$state_id)
{
	if($area_name!='')
	{
		$CI =& get_instance();

		$res = $CI->db->query('SELECT pincode_id,pincode FROM pincode WHERE state_id='.$state_id.' AND cityname=\''.$city_name.'\' AND areaname=\''.$area_name.'\' AND pincode_status=0 ')->row_array();
		if(!empty($res))
		{
			unset($CI);
			return array('pincode_id'=>$res['pincode_id'],'pincode'=>$res['pincode']);
		}
	}
	else
	{
		return array('pincode_id'=>'','pincode'=>'');	
	}
}

/*
+------------------------------------------------------------------+
	Function will fetch category from database.
	$parent = Start parent id from where you want to make menu tree.
	$para = Default first option value.
	$k = Level of category which will convert in ul li
+------------------------------------------------------------------+
*/
function getMultiLevelCategory($parent=0,$str = '',$selected = '',$k=-1)
{
	$CI =& get_instance();
	
	$res = $CI->db->select('category_id,category_name,category_alias, parent_category')->where('parent_category',$parent)->
	where('del_in','0')->order_by('sort_order')->get('product_category')->result_array();
				
	if(count($res) > 0):
		$str.='<ul>';
		$k++;
		foreach($res as $r):
			$ac_class = '';
			$menu_title = $r['category_name'];
			$ac_class = (in_array($r['category_alias'],$selected))?'active':'';
			$str.='<li>
				<a href="'.getMenuLink($r['category_id']).'" class="'.$ac_class.'" >'.str_repeat('- ',$k).$menu_title.'</a>';
			$str = getMultiLevelCategory($r['category_id'],$str,$selected,$k);
			$str.='</li>';
		endforeach;
		
		$str.='</ul>';
		return $str;
	else:
		return $str;
	endif;
}

/*
+--------------------------------------------------+
	Function fetch data for pipe string IDs from table name specified
+--------------------------------------------------+
*/	
function getPipeStringData($table_name,$id_field_name,$fetch_field_name,$id_string)
{
	
	$CI =& get_instance();
	$id_string = str_replace("|",",",$id_string);
	$where = "";
	if($id_string != "")
		$where = " WHERE ".$id_field_name." IN(".$id_string.")";
		
	$res = $CI->db->query("SELECT ".$fetch_field_name." FROM ".$table_name.$where)->result_array();
	unset($CI);
	return $res;
}
	
/**
 * @abstract function will fetch all of product details, this function will be used in jewellery cont,ajax call,cart cont etc.
 */	
function getProductsDetails( $product_price_id, $is_status_check=true, $cz_suffix='')
{
	$CI =& get_instance();
	$select=$join=$where='';
	$product_generated_code_info = null; 
	$is_do_separate_queries_instead_of_joins = true; 
	
	if( isIntranetIp() )
	{
	    echo "here 4, ";
	}
	
	if( MANUFACTURER_ID == 7 )
	{
		$product_generated_code_info = getField( "product_generated_code_info", "product_price", "product_price_id", $product_price_id);
	}
	else 
	{
		$product_generated_code_info = exeQuery(" SELECT product_generated_code_info FROM product_price_cctld 
												  WHERE product_price_id=".$product_price_id." AND manufacturer_id=".MANUFACTURER_ID." ", 
												true, "product_generated_code_info");
	}
	
	if( isIntranetIp() )
	{
	    echo "here 5, ";
	}
	
	if( empty($product_generated_code_info) ) 
	{	
		return '';
	}

	$codeArr = parseProductcodeInfo( $product_generated_code_info ); 
	
	if( isIntranetIp() )
	{
	    echo "here 6, ";
	}
	
	if( MANUFACTURER_ID == 7 ) 
	{
		$select = "SELECT p.product_id, p.inventory_type_id, p.category_id, p.product_name, p.product_alias, p.product_sku, p.product_offer_id, p.product_video, p.product_created_date, 
						  p.product_angle_in, p.product_gender, p.product_accessories, p.product_metal_priority_id, p.product_cs_priority_id, 
						  p.product_ss1_priority_id, p.product_ss2_priority_id, p.ring_size_region, p.product_short_description, p.product_description, 
						  p.product_related_category_id, p.product_related_products_id, 
						  p.custom_page_title, p.meta_description, p.meta_keyword, p.robots,p.author, p.content_rights, 
				   pv.product_value_quantity, pv.product_value_width, pv.product_value_height, 
				   pp.product_price_id, pp.product_generated_code, pp.product_generated_code_displayable, pp.product_generated_code_info, 
				   CONCAT(pp.product_price_weight,' gm (Approx)') as product_price_weight, 
				   pp.product_price_weight as product_price_weight_alias, pp.product_price_calculated_price".$cz_suffix." as product_price_calculated_price, 
				   pp.product_discount".$cz_suffix." as product_discount, pp.product_discounted_price".$cz_suffix." as product_discounted_price ";
	
		$join = "FROM product_price pp INNER JOIN product p ON p.product_id=pp.product_id 
				 INNER JOIN product_value pv ON pv.product_id=p.product_id ";						
				
		$where = "WHERE pp.product_price_id=".$product_price_id." "; 
		
		if( $is_status_check )
			$where .= "AND p.product_status=0 AND pp.product_price_status=0 ";
		
	}
	else 
	{
		/*$select = "SELECT p.product_id, p.inventory_type_id, p.category_id,prc.product_name, p.product_alias, p.product_sku, p.product_offer_id, p.product_angle_in, p.product_gender, p.product_accessories, p.product_metal_priority_id, p.product_cs_priority_id, p.product_ss1_priority_id, p.product_ss2_priority_id, p.ring_size_region, p.product_short_description, p.product_description, p.product_related_category_id, p.product_related_products_id, 
						  prc.custom_page_title, prc.meta_description, prc.meta_keyword, prc.robots,p.author, prc.content_rights, 
				   pv.product_value_quantity, pv.product_value_width, pv.product_value_height, 
				   ppc.product_price_id, ppc.product_generated_code, ppc.product_generated_code_displayable, ppc.product_generated_code_info, 
				   CONCAT(pp.product_price_weight,' gm (Approx)') as product_price_weight, 
				   pp.product_price_weight as product_price_weight_alias, ppc.product_price_calculated_price".$cz_suffix." as product_price_calculated_price, ppc.product_discount".$cz_suffix." as product_discount, ppc.product_discounted_price".$cz_suffix." as product_discounted_price ";
				   
		$join = "FROM product_price pp 
				 INNER JOIN product p ON p.product_id=pp.product_id 
				 INNER JOIN product_price_cctld ppc ON ( ppc.manufacturer_id=".MANUFACTURER_ID." AND ppc.product_price_id=pp.product_price_id ) 
				 INNER JOIN product_cctld prc ON ( prc.manufacturer_id=".MANUFACTURER_ID." AND prc.product_id=p.product_id ) 
				 INNER JOIN product_value pv ON pv.product_id=p.product_id ";
				 
		$where = "WHERE ppc.product_price_id=".$product_price_id." ";	
		if($is_status_check)
			$where .= "AND prc.product_status=0 AND ppc.product_price_status=0 ";			
		*/
				   
		$select = "SELECT p.product_id, p.inventory_type_id, p.category_id,prc.product_name, p.product_alias, p.product_sku, p.product_offer_id, p.product_angle_in, p.product_gender, p.product_accessories, p.product_metal_priority_id, p.product_cs_priority_id, p.product_ss1_priority_id, p.product_ss2_priority_id, p.ring_size_region, p.product_short_description, p.product_description, p.product_related_category_id, p.product_related_products_id, 
						  prc.custom_page_title, prc.meta_description, prc.meta_keyword, prc.robots,p.author, prc.content_rights, p.product_video, p.product_created_date,
				   pv.product_value_quantity, pv.product_value_width, pv.product_value_height, 
				   pp.product_price_id, pp.product_generated_code, pp.product_generated_code_displayable, pp.product_generated_code_info, 
				   CONCAT(pp.product_price_weight,' gm (Approx)') as product_price_weight, 
				   pp.product_price_weight as product_price_weight_alias, pp.product_price_calculated_price".$cz_suffix." as product_price_calculated_price, pp.product_discount".$cz_suffix." as product_discount, pp.product_discounted_price".$cz_suffix." as product_discounted_price ";
	
		$join = "FROM product_price pp 
				 INNER JOIN product p ON p.product_id=pp.product_id 
				 INNER JOIN product_cctld prc ON ( prc.manufacturer_id=".MANUFACTURER_ID." AND prc.product_id=p.product_id ) 
				 INNER JOIN product_value pv ON pv.product_id=p.product_id ";	
		
		
		$where = " WHERE pp.product_price_id=".$product_price_id." ";	
		
		if($is_status_check)
			$where .= "AND prc.product_status=0 AND pp.product_price_status=0 ";
		
	}
	
	if( isIntranetIp() )
	{
	    echo "here 7, ";//.$select.$join.$where;die;
	}
	
	/**
	 * 
	 */
	foreach ($codeArr as $k=>$ar)
	{
		if( $k >= 2 )
		{
			$tempA = explode(":", $ar); 
			
			/**
			 * here $k stands for product_stone_number, 
			 * minus it by 2 to reflect stone number in sequence.  
			 */
			$k -= 2; 
			if( $tempA[1] == "JW_CS" || $tempA[1] == "JW_SS1" || $tempA[1] == "JW_SS2" || $tempA[1] == "JW_SSS" ) 
			{
				if( $k == 0 )
				{
					if( !empty($tempA[3]) )
					{
						$selectTmp = $joinTmp = $whereTmp = ""; 
						$selectTmp = ", product_center_stone_weight as product_center_stone_weight_alias,
									CONCAT(cs.product_center_stone_weight,' carats (Approx)') as product_center_stone_weight,
									cs.product_center_stone_size, cs.product_center_stone_total,
									dpcs.diamond_price_id as diamond_price_id_cs, dpcs.diamond_price_name as diamond_price_name_cs,
									dpcs.diamond_price_key as diamond_price_key_cs, dpcs.dp_desc as dp_desc_cs,
									csds.diamond_shape_id as diamond_shape_id_cs, csds.diamond_shape_name as diamond_shape_name_cs,
									csdt.diamond_type_name as diamond_type_name_cs, csdt.diamond_type_key as diamond_type_key_cs,
								    csdp.diamond_purity_name as diamond_purity_name_cs,
									csdc.diamond_color_name as diamond_color_name_cs ";
						
						$joinTmp = "INNER JOIN product_center_stone cs ON cs.product_id=pp.product_id
								 INNER JOIN diamond_price dpcs ON dpcs.diamond_price_id=cs.category_id
								 INNER JOIN diamond_shape csds ON csds.diamond_shape_id=cs.pcs_diamond_shape_id
								
								 INNER JOIN diamond_type csdt ON csdt.diamond_type_id=dpcs.diamond_type_id
								 INNER JOIN diamond_purity csdp ON csdp.diamond_purity_id=dpcs.diamond_purity_id
								 INNER JOIN diamond_color csdc ON csdc.diamond_color_id=dpcs.diamond_color_id ";
						
						$whereTmp = "AND cs.category_id=".$tempA[3]." ";
						
						if( !$is_do_separate_queries_instead_of_joins )
						{
							$select .= $selectTmp;
							$join .= $joinTmp;
								
							$where .= $whereTmp;
						}
						else 
						{
							$selectTmp = substr($selectTmp, 2);
							$joinTmp = $join . $joinTmp; 
							$whereTmp = $where . $whereTmp; 
							
							$resTmp = $CI->db->query($selectTmp.$joinTmp.$whereTmp)->row_array();
							if(!empty($resTmp))
							{
								$select .= ", '".$resTmp["product_center_stone_weight_alias"]."' as product_center_stone_weight_alias, 
								'".$resTmp["product_center_stone_weight"]."' as product_center_stone_weight,
								'".$resTmp["product_center_stone_size"]."' as product_center_stone_size, 
								'".$resTmp["product_center_stone_total"]."' as product_center_stone_total, 
								'".$resTmp["diamond_price_id_cs"]."' as diamond_price_id_cs, 
								'".$resTmp["diamond_price_name_cs"]."' as diamond_price_name_cs,
								'".$resTmp["diamond_price_key_cs"]."' as diamond_price_key_cs, '".$resTmp["dp_desc_cs"]."' as dp_desc_cs,
								'".$resTmp["diamond_shape_id_cs"]."' as diamond_shape_id_cs, '".$resTmp["diamond_shape_name_cs"]."' as diamond_shape_name_cs,
								'".$resTmp["diamond_type_name_cs"]."' as diamond_type_name_cs, '".$resTmp["diamond_type_key_cs"]."' as diamond_type_key_cs,
							    '".$resTmp["diamond_purity_name_cs"]."' as diamond_purity_name_cs,
								'".$resTmp["diamond_color_name_cs"]."' as diamond_color_name_cs ";
							}
						}
					}
					else 
					{
						$select .= ", '0' as product_center_stone_weight_alias,
								'0' as product_center_stone_weight,
								'' as product_center_stone_size, 0 as product_center_stone_total,
								0 as diamond_price_id_cs, '' as diamond_price_name_cs,
								'' as diamond_price_key_cs, '' as dp_desc_cs,
								0 as diamond_shape_id_cs, '' as diamond_shape_name_cs,
								'' as diamond_type_name_cs, '' as diamond_type_key_cs,
							    '' as diamond_purity_name_cs,
								'' as diamond_color_name_cs ";
					}
				}
				else if( $k <= 2 )
				{
					if( !empty($tempA[3]) )
					{
						$select .= ", product_side_stone".$k."_weight as product_side_stone".$k."_weight_alias,
								CONCAT(ss".$k.".product_side_stone".$k."_weight,' carats (Approx)') as product_side_stone".$k."_weight,
								ss".$k.".product_side_stone".$k."_size, ss".$k.".product_side_stone".$k."_total,
								dsss".$k.".diamond_shape_name as diamond_shape_name_ss".$k.",
								ss".$k."dt.diamond_type_name as diamond_type_name_ss".$k.", ss".$k."dt.diamond_type_key as diamond_type_key_ss".$k.",
								dpss".$k.".diamond_price_id as diamond_price_id_ss".$k.", dpss".$k.".diamond_price_name as diamond_price_name_ss".$k.",
								dpss".$k.".diamond_price_key as diamond_price_key_ss".$k.", dpss".$k.".dp_desc as dp_desc_ss".$k.",
								ss".$k."dp.diamond_purity_name as diamond_purity_name_ss".$k.",
								ss".$k."dc.diamond_color_name as diamond_color_name_ss".$k." ";
						
						$join .= "INNER JOIN product_side_stone".$k." ss".$k." ON ss".$k.".product_id=pp.product_id
							  INNER JOIN diamond_price dpss".$k." ON dpss".$k.".diamond_price_id=ss".$k.".category_id
							  INNER JOIN diamond_shape dsss".$k." ON dsss".$k.".diamond_shape_id=ss".$k.".pss".$k."_diamond_shape_id
					
							  INNER JOIN diamond_type ss".$k."dt ON ss".$k."dt.diamond_type_id=dpss".$k.".diamond_type_id
							  INNER JOIN diamond_purity ss".$k."dp ON ss".$k."dp.diamond_purity_id=dpss".$k.".diamond_purity_id
							 INNER JOIN diamond_color ss".$k."dc ON ss".$k."dc.diamond_color_id=dpss".$k.".diamond_color_id ";
						$where .= "AND ss".$k.".category_id=".$tempA[3]." ";
					}
					else
					{
						$select .= ", '0' as product_side_stone".$k."_weight_alias,
								'0' as product_side_stone".$k."_weight,
								'' as product_side_stone".$k."_size, 0 as product_side_stone".$k."_total,
								'' as diamond_shape_name_ss".$k.",
								'' as diamond_type_name_ss".$k.", '' as diamond_type_key_ss".$k.",
								0 as diamond_price_id_ss".$k.", '' as diamond_price_name_ss".$k.",
								'' as diamond_price_key_ss".$k.", '' as dp_desc_ss".$k.",
								'' as diamond_purity_name_ss".$k.",
								'' as diamond_color_name_ss".$k." ";
					}
				}
				else 
				{
					if( !empty($tempA[3]) )
					{
						$select .= ", product_side_stones_weight as product_side_stone".$k."_weight_alias,
								CONCAT(ss".$k.".product_side_stones_weight,' carats (Approx)') as product_side_stone".$k."_weight,
								ss".$k.".product_side_stones_size as product_side_stone".$k."_size,
								ss".$k.".product_side_stones_total as product_side_stone".$k."_total,
								dsss".$k.".diamond_shape_name as diamond_shape_name_ss".$k.",
								dpss".$k.".diamond_price_id as diamond_price_id_ss".$k.", dpss".$k.".diamond_price_name as diamond_price_name_ss".$k.",
								dpss".$k.".diamond_price_key as diamond_price_key_ss".$k.", dpss".$k.".dp_desc as dp_desc_ss".$k.",
								ss".$k."dt.diamond_type_name as diamond_type_name_ss".$k.", ss".$k."dt.diamond_type_key as diamond_type_key_ss".$k.",
								ss".$k."dp.diamond_purity_name as diamond_purity_name_ss".$k.",
								ss".$k."dc.diamond_color_name as diamond_color_name_ss".$k." ";
						
						$join .= "INNER JOIN product_side_stones ss".$k." ON ( ss".$k.".product_id=pp.product_id AND ss".$k.".product_stone_number=".$k." )
							  INNER JOIN diamond_price dpss".$k." ON dpss".$k.".diamond_price_id=ss".$k.".category_id
							  INNER JOIN diamond_shape dsss".$k." ON dsss".$k.".diamond_shape_id=ss".$k.".psss_diamond_shape_id
					
							  INNER JOIN diamond_type ss".$k."dt ON ss".$k."dt.diamond_type_id=dpss".$k.".diamond_type_id
							  INNER JOIN diamond_purity ss".$k."dp ON ss".$k."dp.diamond_purity_id=dpss".$k.".diamond_purity_id
							  INNER JOIN diamond_color ss".$k."dc ON ss".$k."dc.diamond_color_id=dpss".$k.".diamond_color_id ";
						$where .= "AND ss".$k.".category_id=".$tempA[3]." ";
					}
					else 
					{
						$select .= ", '0' as product_side_stone".$k."_weight_alias,
								'0' as product_side_stone".$k."_weight,
								'' as product_side_stone".$k."_size,
								'0' as product_side_stone".$k."_total,
								'' as diamond_shape_name_ss".$k.",
								'0' as diamond_price_id_ss".$k.", '' as diamond_price_name_ss".$k.",
								'' as diamond_price_key_ss".$k.", '' as dp_desc_ss".$k.",
								'' as diamond_type_name_ss".$k.", '' as diamond_type_key_ss".$k.",
								'' as diamond_purity_name_ss".$k.",
								'' as diamond_color_name_ss".$k." ";
					}
				}
			}
			elseif( $tempA[1] == "SEL" || $tempA[1] == "CHK" || $tempA[1] == "RDO" )
			{
				if( $k == 0 )
				{
					if( !empty($tempA[3]) )
					{
						$selectTmp = $joinTmp = $whereTmp = "";
						
						if( MANUFACTURER_ID == 7 )
						{
							$selectTmp = ", cs.pcs_diamond_shape_id as diamond_shape_id_cs, pacs.pa_real_value as pa_real_value_cs,
									  pacs.pa_value as pa_value_cs ";
								
							$joinTmp = "INNER JOIN product_center_stone cs ON cs.product_id=pp.product_id
							  INNER JOIN product_attribute pacs ON pacs.product_attribute_id=cs.pcs_diamond_shape_id ";
								
							$whereTmp = "AND cs.pcs_diamond_shape_id=".$tempA[3]." ";
						}
						else
						{
							$selectTmp = ", cs.pcs_diamond_shape_id as diamond_shape_id_cs, pacs.pa_real_value as pa_real_value_cs,
									  pacsc.pa_value as pa_value_cs ";
								
							$joinTmp = "INNER JOIN product_center_stone cs ON cs.product_id=pp.product_id
							  	  INNER JOIN product_attribute pacs ON pacs.product_attribute_id=cs.pcs_diamond_shape_id
								  INNER JOIN product_attribute_cctld pacsc
								  ON ( pacsc.product_attribute_id=pacs.product_attribute_id AND pacsc.manufacturer_id=".MANUFACTURER_ID." ) ";
								
							$whereTmp = "AND cs.pcs_diamond_shape_id=".$tempA[3]." ";
						}
						
						
						if( !$is_do_separate_queries_instead_of_joins )
						{
							$select .= $selectTmp;
							$join .= $joinTmp;
							
							$where .= $whereTmp;
						}
						else
						{
							$selectTmp = "SELECT ".substr($selectTmp, 2);
							$joinTmp = $join . $joinTmp;
							$whereTmp = $where . $whereTmp;
							
							$resTmp = $CI->db->query($selectTmp.$joinTmp.$whereTmp)->row_array();
							if(!empty($resTmp))
							{
								$select .= ", '".$resTmp["diamond_shape_id_cs"]."' as diamond_shape_id_cs, 
												'".$resTmp["pa_real_value_cs"]."' as pa_real_value_cs,
									  			'".$resTmp["pa_value_cs"]."' as pa_value_cs ";
							}
							else 
							{
								$select .= ", '0' as diamond_shape_id_cs, '0' as pa_real_value_cs,
									  '' as pa_value_cs ";
							}
						}
					}
					else 
					{
						$select .= ", '0' as diamond_shape_id_cs, '0' as pa_real_value_cs,
									  '' as pa_value_cs ";
					}
				}
				else if( $k <= 2 )
				{
					if( !empty($tempA[3]) )
					{
						$selectTmp = $joinTmp = $whereTmp = "";
						
						if( MANUFACTURER_ID == 7 )
						{
							$selectTmp = ", ss".$k.".pss".$k."_diamond_shape_id as diamond_shape_id_ss".$k.",
									  pass".$k.".pa_real_value as pa_real_value_ss".$k.", pass".$k.".pa_value as pa_value_ss".$k." ";
								
							$joinTmp = "INNER JOIN product_side_stone".$k." ss".$k." ON ss".$k.".product_id=pp.product_id
							  	  INNER JOIN product_attribute pass".$k." ON pass".$k.".product_attribute_id=ss".$k.".pss".$k."_diamond_shape_id ";
								
							$whereTmp = "AND ss".$k.".pss".$k."_diamond_shape_id=".$tempA[3]." ";
						}
						else
						{
							$selectTmp = ", ss".$k.".pss".$k."_diamond_shape_id as diamond_shape_id_ss".$k.",
									  pass".$k.".pa_real_value as pa_real_value_ss".$k.", pass".$k."c.pa_value as pa_value_ss".$k." ";
								
							$joinTmp = "INNER JOIN product_side_stone".$k." ss".$k." ON ss".$k.".product_id=pp.product_id
							  	  INNER JOIN product_attribute pass".$k." ON pass".$k.".product_attribute_id=ss".$k.".pss".$k."_diamond_shape_id
								  INNER JOIN product_attribute_cctld pass".$k."c
								  ON ( pass".$k."c.product_attribute_id=pass".$k.".product_attribute_id AND
								  	   pass".$k."c.manufacturer_id=".MANUFACTURER_ID." ) ";
								
							$whereTmp = "AND ss".$k.".pss".$k."_diamond_shape_id=".$tempA[3]." ";
						}
						
						
						if( !$is_do_separate_queries_instead_of_joins )
						{
							$select .= $selectTmp;
							$join .= $joinTmp;
							
							$where .= $whereTmp;
						}
						else
						{
							$selectTmp = "SELECT ".substr($selectTmp, 2);
							$joinTmp = $join . $joinTmp;
							$whereTmp = $where . $whereTmp;
							
							$resTmp = $CI->db->query($selectTmp.$joinTmp.$whereTmp)->row_array();
							if(!empty($resTmp))
							{
								$select .= ", '".$resTmp["diamond_shape_id_ss".$k]."' as diamond_shape_id_ss".$k.",
									  	'".$resTmp["pa_real_value_ss".$k]."' as pa_real_value_ss".$k.", 
										'".$resTmp["pa_value_ss".$k]."' as pa_value_ss".$k." ";
							}
							else
							{
								$select .= ", '0' as diamond_shape_id_ss".$k.",
									  '0' as pa_real_value_ss".$k.", '' as pa_value_ss".$k." ";
							}
						}
					}
					else 
					{
						$select .= ", '0' as diamond_shape_id_ss".$k.",
									  '0' as pa_real_value_ss".$k.", '' as pa_value_ss".$k." ";
					}
				}
				else
				{
					if( !empty($tempA[3]) )
					{
						$selectTmp = $joinTmp = $whereTmp = "";
						
						if( MANUFACTURER_ID == 7 )
						{
							$selectTmp = ", ss".$k.".psss_diamond_shape_id as diamond_shape_id_ss".$k.",
									  pass".$k.".pa_real_value as pa_real_value_ss".$k.", pass".$k.".pa_value as pa_value_ss".$k." ";
								
							$joinTmp = "INNER JOIN product_side_stones ss".$k." ON ss".$k.".product_id=pp.product_id
							  	  INNER JOIN product_attribute pass".$k." ON pass".$k.".product_attribute_id=ss".$k.".psss_diamond_shape_id ";
								
							$whereTmp = "AND ss".$k.".psss_diamond_shape_id=".$tempA[3]." ";
						}
						else
						{
							$selectTmp = ", ss".$k.".psss_diamond_shape_id as diamond_shape_id_ss".$k.",
									  pass".$k.".pa_real_value as pa_real_value_ss".$k.", pass".$k."c.pa_value as pa_value_ss".$k." ";
								
							$joinTmp = "INNER JOIN product_side_stones ss".$k." ON ss".$k.".product_id=pp.product_id
							  	  INNER JOIN product_attribute pass".$k." ON pass".$k.".product_attribute_id=ss".$k.".psss_diamond_shape_id
								  INNER JOIN product_attribute_cctld pass".$k."c
								  ON ( pass".$k."c.product_attribute_id=pass".$k.".product_attribute_id AND
								  	   pass".$k."c.manufacturer_id=".MANUFACTURER_ID." ) ";
								
							$whereTmp = "AND ss".$k.".psss_diamond_shape_id=".$tempA[3]." ";
						}
						
						
						if( !$is_do_separate_queries_instead_of_joins )
						{
							$select .= $selectTmp;
							$join .= $joinTmp;
							
							$where .= $whereTmp;
						}
						else
						{
							$selectTmp = "SELECT ".substr($selectTmp, 2);
							$joinTmp = $join . $joinTmp;
							$whereTmp = $where . $whereTmp;
							
							$resTmp = $CI->db->query($selectTmp.$joinTmp.$whereTmp)->row_array();
							if(!empty($resTmp))
							{
								$select .= ", '".$resTmp["diamond_shape_id_ss".$k]."' as diamond_shape_id_ss".$k.",
									  '".$resTmp["pa_real_value_ss".$k]."' as pa_real_value_ss".$k.", '".$resTmp["pa_value_ss".$k]."' as pa_value_ss".$k." ";
							}
							else
							{
								$select .= ", '0' as diamond_shape_id_ss".$k.",
									  '0' as pa_real_value_ss".$k.", '' as pa_value_ss".$k." ";
							}
						}
					}
					else 
					{
						$select .= ", '0' as diamond_shape_id_ss".$k.",
									  '0' as pa_real_value_ss".$k.", '' as pa_value_ss".$k." ";
					}
				}
			}
			elseif( $tempA[1] == "JW_MTL" )
			{
				$select .= ", metal_type_name, metal_purity_name, metal_color_name, mc.metal_color_key, CONCAT(pm.product_metal_weight,' gm (Approx)') as product_metal_weight, 
						   pm.product_metal_weight  as product_metal_weight_alias, 
						   mp.metal_price_id, mp.mp_price_difference, mp_desc "; 
			
				$join .= "INNER JOIN product_metal pm ON pm.product_id=pp.product_id
						 INNER JOIN metal_price mp ON mp.metal_price_id=pm.category_id  
						 INNER JOIN metal_type mt ON mt.metal_type_id=mp.metal_type_id
						 INNER JOIN metal_color mc ON mc.metal_color_id=mp.metal_color_id 
						 INNER JOIN metal_purity mpp ON mpp.metal_purity_id=mp.metal_purity_id "; 
						
				$where .= "AND pm.category_id=".$tempA[3]." "; 
			}
			elseif( $tempA[1] == "TXT" )
			{
				if( $k == 0 )
				{
					if( !empty($tempA[3]) )
					{
						$selectTmp = $joinTmp = $whereTmp = "";
						
						if( MANUFACTURER_ID == 7 )
						{
							$selectTmp = ", cs.product_center_stone_size ";
								
							$joinTmp = "INNER JOIN product_center_stone cs ON cs.product_id=pp.product_id ";
						}
						else
						{
							$selectTmp = ", csc.product_center_stone_size ";
								
							$joinTmp = "INNER JOIN product_center_stone cs ON cs.product_id=pp.product_id
								  INNER JOIN product_center_stone_cctld csc
								  ON ( csc.product_center_stone_id=cs.product_center_stone_id AND csc.manufacturer_id=".MANUFACTURER_ID." ) ";
						}
						
						
						if( !$is_do_separate_queries_instead_of_joins )
						{
							$select .= $selectTmp;
							$join .= $joinTmp;
							
							$where .= $whereTmp;
						}
						else
						{
							$selectTmp = "SELECT ".substr($selectTmp, 2);
							$joinTmp = $join . $joinTmp;
							$whereTmp = $where . $whereTmp;
							
							$resTmp = $CI->db->query($selectTmp.$joinTmp.$whereTmp)->row_array();
							if(!empty($resTmp))
							{
								$select .= ", '".$resTmp["product_center_stone_size"]."' as product_center_stone_size ";
							}
							else
							{
								$select .= ", '' as product_center_stone_size ";
							}
						}
					}
					else 
					{
						$select .= ", '' as product_center_stone_size ";
					}
				}
				else if( $k <= 2 )
				{
					if( !empty($tempA[3]) )
					{
						$selectTmp = $joinTmp = $whereTmp = "";
						
						if( MANUFACTURER_ID == 7 )
						{
							$selectTmp = ", ss".$k.".product_side_stone".$k."_size ";
								
							$joinTmp = "INNER JOIN product_side_stone".$k." ss".$k." ON ss".$k.".product_id=pp.product_id ";
						}
						else
						{
							$selectTmp = ", ss".$k."c.product_side_stone".$k."_size ";
								
							$joinTmp = "INNER JOIN product_side_stone".$k." ss".$k." ON ss".$k.".product_id=pp.product_id
								  INNER JOIN product_side_stone".$k."_cctld ss".$k."c
								  ON ( ss".$k."c.product_side_stone".$k."_id=ss".$k.".product_side_stone".$k."_id AND
								  	   ss".$k."c.manufacturer_id=".MANUFACTURER_ID." ) ";
						}
						
						
						if( !$is_do_separate_queries_instead_of_joins )
						{
							$select .= $selectTmp;
							$join .= $joinTmp;
							
							$where .= $whereTmp;
						}
						else
						{
							$selectTmp = "SELECT ".substr($selectTmp, 2);
							$joinTmp = $join . $joinTmp;
							$whereTmp = $where . $whereTmp;
							
							$resTmp = $CI->db->query($selectTmp.$joinTmp.$whereTmp)->row_array();
							if(!empty($resTmp))
							{
								$select .= ", '".$resTmp["product_side_stone".$k."_size"]."' as product_side_stone".$k."_size ";
							}
							else
							{
								$select .= ", '' as product_side_stone".$k."_size ";
							}
						}
					}
					else 
					{
						$select .= ", '' as product_side_stone".$k."_size ";
					}
				}
				else 
				{
					if( !empty($tempA[3]) )
					{
						$selectTmp = $joinTmp = $whereTmp = "";
						
						if( MANUFACTURER_ID == 7 )
						{
							$selectTmp = ", ss".$k.".product_side_stones_size as product_side_stone".$k."_size ";
								
							$joinTmp = "INNER JOIN product_side_stones ss".$k." ON ss".$k.".product_id=pp.product_id ";
						}
						else
						{
							$selectTmp = ", ss".$k."c.product_side_stones_size as product_side_stone".$k."_size ";
								
							$joinTmp = "INNER JOIN product_side_stones ss".$k." ON ss".$k.".product_id=pp.product_id
								  INNER JOIN product_side_stones_cctld ss".$k."c
								  ON ( ss".$k."c.product_side_stones_id=ss".$k.".product_side_stones_id AND
								  	   ss".$k."c.manufacturer_id=".MANUFACTURER_ID." ) ";
						}
						
						
						if( !$is_do_separate_queries_instead_of_joins )
						{
							$select .= $selectTmp;
							$join .= $joinTmp;
							
							$where .= $whereTmp;
						}
						else
						{
							$selectTmp = "SELECT ".substr($selectTmp, 2);
							$joinTmp = $join . $joinTmp;
							$whereTmp = $where . $whereTmp;
							
							$resTmp = $CI->db->query($selectTmp.$joinTmp.$whereTmp)->row_array();
							if(!empty($resTmp))
							{
								$select .= ", '".$resTmp["product_side_stone".$k."_size"]."' as product_side_stone".$k."_size ";
							}
							else
							{
								$select .= ", '' as product_side_stone".$k."_size ";
							}
						}
						
					}
					else 
					{
						$select .= ", '' as product_side_stone".$k."_size ";
					}
				}
			}
				
		}
	}
		
	
	$res = $CI->db->query($select.$join.$where);
	//echo $select.$join.$where; 
	if( isIntranetIp() )
	{
	    echo "here 8: ".$CI->db->last_query();die;
	}
// 	
	unset($CI);
	return array('res'=>$res,'codeArr'=>$codeArr);
}

/**
 * 
 * @param string $product_generated_code
 * @param number $product_price_id
 * @param unknown $product_sku
 * @return string
 */
function getProdImageFolder($product_generated_code='',$product_price_id=0, $product_sku="", $product_generated_code_info="", $inventory_type_id=0)
{
	$CI =& get_instance();
	$imagefolder='';
	
	/**
	 * @deprecated
	 */
// 	if( empty($product_generated_code) )
// 	{
// 		if( $product_price_id!=0 )
// 		{
// 			$product_generated_code= getField("product_generated_code","product_price","product_price_id",$product_price_id);
// 		}
// 		else 
// 		{
// 			return FALSE; 
// 		}
// 	}

	if( hewr_isComponentBasedCheckWithId($inventory_type_id) )
	{
		/**
		 * componenet based inventory folder structure
		 */
		if( empty($product_generated_code_info) )
		{
			if( $product_price_id!=0 )
			{
				$product_generated_code_info= getField("product_generated_code","product_price","product_price_id",$product_price_id);
			}
			else if(!empty($product_generated_code) )
			{
				$product_generated_code_info= getField("product_generated_code","product_price","product_price_id",$product_price_id);
			}
			else
			{
				return FALSE;
			}
		}
		
		$codeArr = parseProductcodeInfo( $product_generated_code_info );
		
		$select = "SELECT  p.product_sku,metal_color_key ";
		
		$join = "FROM product_price pp INNER JOIN product p ON p.product_id=pp.product_id
			 INNER JOIN product_metal pm ON pm.product_id=pp.product_id
			 INNER JOIN metal_price mp ON mp.metal_price_id=pm.category_id
			 INNER JOIN metal_color mc ON mc.metal_color_id=mp.metal_color_id ";
			
		$where = " WHERE pp.product_price_id=".$product_price_id." AND pp.product_price_status=0 AND pm.category_id=".(int)$codeArr[2]."";
		
		if(isset($codeArr[3]))
		{
			$select .= ",dpcs.diamond_price_key as diamond_price_key_cs,csdt.diamond_type_key as diamond_type_key_cs ";
		
			$join .= "INNER JOIN product_center_stone cs ON cs.product_id=pp.product_id
				 INNER JOIN diamond_price dpcs ON dpcs.diamond_price_id=cs.category_id
				 INNER JOIN diamond_type csdt ON csdt.diamond_type_id=dpcs.diamond_type_id ";
			$where .= " AND cs.category_id=".$codeArr[3]."";
		}
		
		if(isset($codeArr[4]))
		{
			$select .= ",ss1dt.diamond_type_key as diamond_type_key_ss1,dpss1.diamond_price_key as diamond_price_key_ss1 ";
			$join .= "INNER JOIN product_side_stone1 ss1 ON ss1.product_id=pp.product_id
				  INNER JOIN diamond_price dpss1 ON dpss1.diamond_price_id=ss1.category_id
				  INNER JOIN diamond_type ss1dt ON ss1dt.diamond_type_id=dpss1.diamond_type_id ";
			$where .= " AND ss1.category_id=".$codeArr[4]."";
		}
		
		if(isset($codeArr[5]))
		{
			$select .= ",ss2dt.diamond_type_key as diamond_type_key_ss2,dpss2.diamond_price_key as diamond_price_key_ss2 ";
			$join .= "INNER JOIN product_side_stone2 ss2 ON ss2.product_id=pp.product_id
				  INNER JOIN diamond_price dpss2 ON dpss2.diamond_price_id=ss2.category_id
				  INNER JOIN diamond_type ss2dt ON ss2dt.diamond_type_id=dpss2.diamond_type_id ";
			$where .= " AND ss2.category_id=".$codeArr[5]."";
		
			//query for additional stones
			$is_stone = true;
			$product_stone_number = 3;
			while( $is_stone )
			{
				if(isset($codeArr[ $product_stone_number+3 ]))
				{
					$select .= ",ss".$product_stone_number."dt.diamond_type_key as diamond_type_key_ss".$product_stone_number.", dpss".$product_stone_number.".diamond_price_key as diamond_price_key_ss".$product_stone_number." ";
					$join .= "INNER JOIN product_side_stones ss".$product_stone_number." ON ( ss".$product_stone_number.".product_id=pp.product_id AND ss".$product_stone_number.".product_stone_number=".$product_stone_number." )
						  INNER JOIN diamond_price dpss".$product_stone_number." ON dpss".$product_stone_number.".diamond_price_id=ss".$product_stone_number.".category_id
						  INNER JOIN diamond_type ss".$product_stone_number."dt ON ss".$product_stone_number."dt.diamond_type_id=dpss".$product_stone_number.".diamond_type_id ";
					$where .= " AND ss".$product_stone_number.".category_id=".$codeArr[ $product_stone_number+3 ]."";
				}
				else
				{
					$is_stone = false;
				}
				$product_stone_number++;
			}
		}
		
		
		$data = $CI->db->query($select.$join.$where)->row_array();
		//echo $CI->db->last_query();die;
		
		if(!empty($data))
		{
			//generate image folder path and fetch particular folder images
			$imagefolder = 'assets/product/'.$data['product_sku'].'/';
			if(isset($codeArr[3]))		//center stone if exist
			{
				$imagefolder .= $data['diamond_type_key_cs'].'/'.$data['diamond_price_key_cs'].'/';
			}
			if(isset($codeArr[4]))		//side stone1 if exist
			{
				$imagefolder .= 'SIDESTONE1/'.$data['diamond_type_key_ss1'].'/'.$data['diamond_price_key_ss1'].'/';
			}
			if(isset($codeArr[5]))		//side stone2 if exist
			{
				$imagefolder .= 'SIDESTONE2/'.$data['diamond_type_key_ss2'].'/'.$data['diamond_price_key_ss2'].'/';
		
				//query for additional stones
				$is_stone = true;
				$product_stone_number = 3;
				while( $is_stone )
				{
					if(isset($codeArr[ $product_stone_number+3 ]))
					{
						$imagefolder .= 'SIDESTONE'.$product_stone_number.'/'.$data['diamond_type_key_ss'.$product_stone_number.''].'/'.$data['diamond_price_key_ss'.$product_stone_number.''].'/';
					}
					else
					{
						$is_stone = false;
					}
					$product_stone_number++;
				}
			}
		
			$imagefolder .= $data['metal_color_key'];
		}
	}
	else 
	{
		/**
		 * generate image folder path for basic inventory
		 */
		//$imagefolder = 'assets/product/'.$product_sku;
		
		/**
		 * @author Cloudwebs
		 * Added on 27-06-2015
		 * A semi dynamic(only single level deep ) folder structure for attribute based inventory,
		 * applicable when attributes like color or colour is found.
		 */
		/**
		 * componenet based inventory folder structure
		 */
		if( empty($product_generated_code_info) )
		{
			if( $product_price_id!=0 )
			{
				$product_generated_code_info = getField("product_generated_code_info","product_price","product_price_id",$product_price_id);
			}
			else if(!empty($product_generated_code) )
			{
				$product_generated_code_info = getField("product_generated_code_info","product_price","product_price_id",$product_price_id);
			}
			else
			{
				return FALSE;
			}
		}
		
		$codeArr = parseProductcodeInfo( $product_generated_code_info );
		$imagefolder = front_end_hlp_attrBasedProductImageFolder( "assets/product/".$product_sku, $codeArr);
		
	}
	
	unset($CI);
	return $imagefolder;
}

/**
 * @return string
 */
function getProdQtyOptionsIndex( $product_generated_code_info, $inventory_type_id )
{
	$CI =& get_instance();

	if( $CI->session->userdata("IMS_QTY_OPT_IND_".$inventory_type_id) !== FALSE )
	{
		return $CI->session->userdata("IMS_QTY_OPT_IND_".$inventory_type_id); 
	}
	else 
	{
		$codeArr = parseProductcodeInfo( $product_generated_code_info );
		
		$index = "";
		foreach ($codeArr as $k=>$ar)
		{
			if( $k >= 2 )
			{
				$tempA = explode(":", $ar);
				if( in_array( $tempA[0], hewr_qtyAttributeIDs() ) )
				{
					$k -= 2;
					if( $k == 0 )
					{
						$index = "cs";
					}
					else 
					{
						$index = "ss".$k;
					}
				}
			}
		}
		
		$CI->session->set_userdata( array( "IMS_QTY_OPT_IND_".$inventory_type_id=>$index ) );
		return $index;
	}
}

/**
 * 
 * @param string $product_generated_code
 * @param number $product_price_id
 * @param unknown $product_sku
 * @return string
 */
function getProdQtyOptions( $product_id="",$product_generated_code_info="", $dropdown=array( ''=>"Quantity" ) )
{
	$CI =& get_instance();
	$codeArr = parseProductcodeInfo( $product_generated_code_info );

	foreach ($codeArr as $k=>$ar)
	{
		if( $k >= 2 )
		{
			$tempA = explode(":", $ar);
			if( in_array( $tempA[0], hewr_qtyAttributeIDs() ) )
			{
				/**
				 * get qty options from side stones table
				 */
				$k -= 2;
				if( $k == 0 )
				{
					if( MANUFACTURER_ID == 7 )
					{
						return getDropDownAry( "SELECT pa.product_attribute_id, pa.pa_real_value, pa.pa_value FROM product_center_stone pcs
												INNER JOIN product_attribute pa
												ON pa.product_attribute_id=pcs.pcs_diamond_shape_id
												WHERE pcs.product_id=".$product_id." AND 
													  pcs.inventory_master_specifier_id=".$tempA[0]." AND 
													  pcs.product_center_stone_status=0 
												ORDER BY pa.pa_sort_order",
												"pa_real_value", "pa_value", $dropdown, false);
					}
					else 
					{
						return getDropDownAry( "SELECT pac.product_attribute_id, pa.pa_real_value, pac.pa_value FROM product_center_stone pcs
												INNER JOIN product_attribute pa
												ON pa.product_attribute_id=pcs.pcs_diamond_shape_id
												INNER JOIN product_attribute_cctld pac
												ON pac.product_attribute_id=pcs.pcs_diamond_shape_id
												WHERE pcs.product_id=".$product_id." AND 
													  pcs.inventory_master_specifier_id=".$tempA[0]." AND 
													  pcs.product_center_stone_status=0 
												ORDER BY pa.pa_sort_order",
												"pa_real_value", "pa_value", $dropdown, false);
					}
				} 
				else if( $k <= 2 ) 
				{
					if( MANUFACTURER_ID == 7 )
					{
						return getDropDownAry( "SELECT pa.product_attribute_id, pa.pa_real_value, pa.pa_value FROM product_side_stone".$k." pss".$k."
												INNER JOIN product_attribute pa
												ON pa.product_attribute_id=pss".$k.".pss".$k."_diamond_shape_id
												WHERE pss".$k.".product_id=".$product_id." AND 
													  pss".$k.".inventory_master_specifier_id=".$tempA[0]." AND 
													  pss".$k.".product_side_stone".$k."_status=0 
												ORDER BY pa.pa_sort_order",
												"pa_real_value", "pa_value", $dropdown, false);
					}
					else
					{
						return getDropDownAry( "SELECT pac.product_attribute_id, pa.pa_real_value, pac.pa_value 
												FROM product_side_stone".$k." pss".$k."
												INNER JOIN product_attribute pa
												ON pa.product_attribute_id=pss".$k.".pss".$k."_diamond_shape_id
												INNER JOIN product_attribute_cctld pac
												ON pac.product_attribute_id=pss".$k.".pss".$k."_diamond_shape_id
												WHERE pss".$k.".product_id=".$product_id." AND 
													  pss".$k.".inventory_master_specifier_id=".$tempA[0]." AND 
													  pss".$k.".product_side_stone".$k."_status=0 
												ORDER BY pa.pa_sort_order",
												"pa_real_value", "pa_value", $dropdown, false);
					}
				}
				else 
				{
					if( MANUFACTURER_ID == 7 )
					{
						return getDropDownAry( "SELECT pa.product_attribute_id, pa.pa_real_value, pa.pa_value FROM product_side_stones pss".$k."
												INNER JOIN product_attribute pa
												ON pa.product_attribute_id=pss".$k.".psss_diamond_shape_id
												WHERE pss".$k.".product_id=".$product_id." AND 
													  pss".$k.".inventory_master_specifier_id=".$tempA[0]." AND 
													  pss".$k.".product_side_stones_status=0 
												ORDER BY pa.pa_sort_order",
												"pa_real_value", "pa_value", $dropdown, false);
					}
					else
					{
						return getDropDownAry( "SELECT pac.product_attribute_id, pa.pa_real_value, pac.pa_value FROM product_side_stones pss".$k."
												INNER JOIN product_attribute pa
												ON pa.product_attribute_id=pss".$k.".psss_diamond_shape_id
												INNER JOIN product_attribute_cctld pac
												ON pac.product_attribute_id=pss".$k.".psss_diamond_shape_id
												WHERE pss".$k.".product_id=".$product_id." AND 
													  pss".$k.".inventory_master_specifier_id=".$tempA[0]." AND 
													  pss".$k.".product_side_stones_status=0 
												ORDER BY pa.pa_sort_order",
												"pa_real_value", "pa_value", $dropdown, false);
					}
				}
			}
		}
	}
	
	return $dropdown;
}


/*
+--------------------------------------------------+
	@author Cloudwebs
	Function generate and return price tag for seo freindly search code
	@abstract whenever function changes it's behaviour it's mandatory to do regression testing for home page search filter
+--------------------------------------------------+
*/	
function generatePriceTag($price_filArr=array(),$min_term=-1,$max_term=-1)
{
	$price_tag = "";
	$url_tag = "";
	$min = 0.0;		// min price
	$max = 0.0;		// max price
	$is_searched = false;

	//if min_term and max_term is supplied and if not searched then add it to search term array else exclude it to generate relative price tag
	if($min_term != -1 && $max_term != -1)
	{
		if( is_searched( $min_term."-".$max_term, $price_filArr ) )
		{
			$is_searched = true;
			$price_filArr = array_diff($price_filArr, array($min_term."-".$max_term));	//exclude from array
		}
		else
		{
			$price_filArr[] = $min_term."-".$max_term;									//add to array
		}
	}

	$cnt = 0;
	foreach($price_filArr  as $key=>$val)
	{
		$valArr = explode("-",$val);
		if(sizeof($valArr)>=2)
		{
			if($cnt == 0)
			{
				$min = $valArr[0];
			}
			if($min > (float)$valArr[0])
			{
				$min = (float)$valArr[0];
			}
	
			if((float)$valArr[1] == 0 )
			{
				$max = 0;
				//break;	
			}
			else if($max < (float)$valArr[1])
			{
				$max = (float)$valArr[1];
			}
			
			if( $valArr[0] == 0 )
			{
				$tMax = str_replace( array( " ", "&nbsp;" ), "-", lp($valArr[1], 0));
				$price_tag .= "Below-".$tMax."-";
				$url_tag .= "below-".$tMax."+"; 
			}
			else if( $valArr[1] == 0 )
			{
				$tMin = str_replace( array( " ", "&nbsp;" ), "-", lp($valArr[0], 0));
				$price_tag .= "Above-".$tMin."-";
				$url_tag .= "above-".$tMin."+";
			}
			else 
			{
				$tMin = str_replace( array( " ", "&nbsp;" ), "-", lp($valArr[0], 0));
				$tMax = str_replace( array( " ", "&nbsp;" ), "-", lp($valArr[1], 0));

				$price_tag .= "Between-".$tMin."-and-".$tMax."-";
				$url_tag .= $tMin."-to-".$tMax."+";
			}
   			
			$cnt++;
		}
	}

//	//$tMin = str_replace( array( " ", "&nbsp;" ), "-", lp($min, 0));
//	//$tMax = str_replace( array( " ", "&nbsp;" ), "-", lp($max, 0));
//	if( $min == 0 && $max == 0 )
//		$price_tag = "";
//	else if($min == 0)
//	{
//		//$price_tag = "Below-".$tMax."-";
//		//$url_tag = "below-".$tMax."+";
//	}
//	else if($max == 0)
//	{
//		//$price_tag = "Above-".$tMin."-";
//		//$url_tag = "above-".$tMin."+";
//	}
//	else 
//	{
//		//$price_tag = "Between-".$tMin."-and-".$tMax."-";
//		//$url_tag = $tMin."-to-".$tMax."+";
//	}

	return array('price_tag'=> str_replace( " ", "-", $price_tag), 'url_tag'=>str_replace( " ", "-", $url_tag), 'min'=>$min,'max'=>$max,'is_searched'=>$is_searched);
}

/*
+--------------------------------------------------+
	@author Cloudwebs
	Function generate and return metal tag for seo freindly search code
+--------------------------------------------------+
*/	
function generateMetalTag($metal_filArr,$color_term='',$purity_term='',$color_term_name='',$purity_term_name='')
{
	$metal_tag = ""; $url_tag = '';
	$is_searched = false;
	//if color_term and purity_term is supplied and  if not searched then add it to search term array else exclude it to generate relative price tag
	if($color_term != '' && $purity_term != '')
	{
		if(is_searched($color_term."-".$purity_term,$metal_filArr))
		{
			$is_searched = true;
			$metal_filArr = array_diff($metal_filArr, array($color_term."-".$purity_term));	//exclude from array
		}
		else
		{
			$metal_filArr[] = $color_term."-".$purity_term;									 //add to array
		}
	}

	//generate id pipe string
	$metal_color_id="";
	$metal_purity_id="";
	
	foreach($metal_filArr as $key=>$val)
	{
		$valArr = explode("-",$val);
		if(sizeof($valArr)>=2)
		{
			$metal_color_id .= $valArr[0]."|";
			$metal_purity_id .= $valArr[1]."|";
		}
	}
	
	if(!empty($metal_filArr))		//if not searched then apply default selection
	{
		$resC = getPipeStringData("metal_purity","metal_purity_id","metal_purity_name,metal_purity_id",substr($metal_purity_id,0,-1));
		foreach($resC as $k1=>$ar1)
		{
			$metal_tag .= $ar1['metal_purity_name']."+";							
		}
		
		$resP = getPipeStringData("metal_color","metal_color_id","metal_color_name,metal_color_id",substr($metal_color_id,0,-1));
		foreach($resP as $k1=>$ar1)
		{
			$metal_tag .= $ar1['metal_color_name']."+";							
		}
	}
	
	//at a time only gold is used so metal type name is static and append gold if metal tag is not empty
	if($metal_tag != "")
	{
		$metal_tag .= "gold";
	}

	return array( 'metal_tag'=>$metal_tag."+", 'url_tag'=>$url_tag, 'is_searched'=>$is_searched);
}

/**
 * @author Cloudwebs
 * @abstract function will return true if $item_id was searched used in search filter
 * @param $item_id the item to look for if it was searched
 * @param $array to search in
 * $return return true if item match else false
*/
function is_searched($item_id,$array)
{
	if(isset($array) && is_array($array))
	{
		if(in_array($item_id,$array))
			return true;
		else
			return false;
	}
}

/*
+------------------------------------------------------------------+
	Function will prepate query and append limit options. 
	return query result options.
	@params : $str -> pagination base url
			  $num -> Total number of rows table contain.
			  $start -> start segment, position 
			  $segment -> From which segment you want to consider pagination record count ?.
+------------------------------------------------------------------+
*/
function pagiationData($str,$num,$start,$segment, $config_arr = array() )
{
	$CI =& get_instance();
	if($CI->input->post('perPage') != '')
		$pp = (int)$CI->input->post('perPage');
		
	else if($CI->input->get('perPage') != '')
		$pp = (int)$CI->input->get('perPage');	
			
	else if($CI->session->userdata('perPage') != '')
		$pp = (int)$CI->session->userdata('perPage');
		
		
	elseif($CI->router->directory == 'admin/')
		$pp = 20;

	if($CI->router->directory != 'admin/' && !$CI->input->get('perPage'))
		$pp = PER_PAGE_FRONT;


/*	if( is_restClient() )
	{
		//if( $CI->input->get('perPage') )
			$pp = PER_PAGE_FRONT;
	}
	else 
	{
		if($CI->router->directory == 'admin/')
		$pp = 20;
		
		if($CI->router->directory != 'admin/' && !$CI->input->get('perPage'))
			$pp = PER_PAGE_FRONT;
	}
*/	
	$CI->session->set_userdata('perPage',$pp);
	//echo $pp.'=sess='.$CI->session->userdata('perPage');die;
	if( !is_restClient() )
	{
		$config['base_url'] = base_url().$str;
		$config['uri_segment'] = $segment;
	}
// 	else
// 	{
// 		$config['uri_segment'] = 1;
// 	}
	$config['total_rows'] = $num;
	$config['per_page'] = $pp ;			//variable defined by Cloudwebs
	//$config['full_tag_open'] = '<div class="pagination">';
	//$config['full_tag_close'] = '</div>';
	$config['cur_page'] = $start;
	
	if(!empty($config_arr))
		$config = array_merge($config, $config_arr);
		
	
	$CI->pagination->initialize($config); 
	
	$query = $CI->db->last_query()." LIMIT ".$start." , ".$config['per_page'];
	
	$res = $CI->db->query($query);
	
	$data['perpage'] = $pp;
	$data['listArr'] = $res->result_array();
	$data['num'] = $res->num_rows();
	
	if( !is_restClient() )
	{
		$data['links'] =  $CI->pagination->create_links();
	}
	$data['total_rows'] = $num;

	return $data;
}

/*******************************************************************************/

/**
 * @author Cloudwebs
 * @abstract function will fetch all of product details from function getProductsDetails and optimize return for display, this function will be used in jewellery cont,ajax call,cart cont etc.
 * @param if $is_cart_or_checkout is true then it indicates that call is from cart or checkout processes in that case never redirect
 * @param $depth: important to unload cpu usage so that unneccessary information proccessing will be ignored 0 => full, 1 => minimal inforamtion
 */	
function showProductsDetails($product_price_id, $is_ajax=false, $is_cart_or_checkout=false,  $is_status_check=true, $pageToken='', $ring_size_id='', $cz_suffix='', $depth=0)
{
	$CI =& get_instance();
	$is_ready_to_ship = false;

	if( isIntranetIp() )
	{
	    echo "Here 3, ";
	}
	
	$resArr =  getProductsDetails( $product_price_id, $is_status_check, $cz_suffix );
	
	if(empty($resArr) || $resArr['res']->num_rows() == 0)
	{
		if(!$is_cart_or_checkout)
		{
			setFlashMessage( 'error',"Sorry! Product is not available.");
			redirect(site_url('search'));	
		}
		else
			return false;
	}
	
	$data['view_var'] = $data = $resArr['res']->row_array();
	
	if( isIntranetIp() )
	{
	    pr( $data );die;
	}
	//set success flag
	$data['type'] = 'success';
	$data['msg'] = '';


	//ready to ship check
	if( $data['product_offer_id']!='')
	{
		$resOffer = getPipeStringData("product_offer", "product_offer_id", "product_offer_key", $data['product_offer_id']);
		if(!empty($resOffer))
			$is_rts = associative_array_search($resOffer, "product_offer_key", "RTS");
			
		if($is_rts !== FALSE)			
			$is_ready_to_ship = true;
	}

	/**
	 * ready to ship check
	 * un-comment below code if cross check with metal priority is required
	 */
// 	if($is_ready_to_ship==true && $data['product_metal_priority_id']==$data['metal_price_id'])
// 		$is_ready_to_ship = true;
// 	else
// 		$is_ready_to_ship = false;

	
	/**
	 * generate image folder path
	 */
	$imagefolder = "";
	if( FALSE && hewr_isComponentBasedCheckWithId( $data["inventory_type_id"] ) ) 
	{
		$imagefolder = 'assets/product/'.$data['product_sku'].'/';
		if(isset($resArr['codeArr'][3]))		//center stone if exist
		{
			$imagefolder .= $data['diamond_type_key_cs'].'/'.$data['diamond_price_key_cs'].'/';
		
			if($data['diamond_type_key_cs'] == 'DIAMOND')
			{
				if( $cz_suffix == '_cz' ) 	//if CZ then change label and types
				{
					$data['diamond_type_name_cs'] = $data['view_var']['diamond_type_name_cs'] = 'Stone';
					$data['diamond_type_name_cs_alias'] = $data['view_var']['diamond_type_name_cs_alias'] = 'Cubic Zirconia';
					$data['diamond_purity_name_cs'] = $data['diamond_color_name_cs'] = $data['view_var']['diamond_purity_name_cs'] = $data['view_var']['diamond_color_name_cs'] = '-';
				}
				else if( $data['product_accessories'] == 'SOL' )
				{
					$data['diamond_type_name_cs_alias'] = $data['view_var']['diamond_type_name_cs_alias'] = $data['diamond_type_name_cs'];
					$data['view_var']['diamond_type_name_cs'] = $data['diamond_type_name_cs'] = 'Solitaire';
				}
				else
				{ $data['diamond_type_name_cs_alias'] = $data['view_var']['diamond_type_name_cs_alias'] = $data['diamond_type_name_cs']; }
			}
			else
			{
				if( $cz_suffix == '_cz' ) 	//if CZ then change label and types
				{
					$data['diamond_type_name_cs']= $data['view_var']['diamond_type_name_cs'] = 'Semi Precious Gemstone';
					$data['diamond_type_name_cs_alias'] = $data['view_var']['diamond_type_name_cs_alias'] = $data['diamond_price_name_cs']." (Semi Precious)";
				}
				else
				{ $data['diamond_type_name_cs_alias'] = $data['view_var']['diamond_type_name_cs_alias'] = $data['diamond_price_name_cs']; }
			}
		
			//ready to ship check
			if($is_ready_to_ship===true &&  $data['product_cs_priority_id']==$data['diamond_price_id_cs'])
				$is_ready_to_ship = true;
			else
				$is_ready_to_ship = false;
		}
		
		if(isset($resArr['codeArr'][4]))		//side stone1 if exist
		{
			$imagefolder .= 'SIDESTONE1/'.$data['diamond_type_key_ss1'].'/'.$data['diamond_price_key_ss1'].'/';
		
			if($data['diamond_type_key_ss1'] == 'DIAMOND')
			{
				if( $cz_suffix == '_cz' ) 	//if CZ then change label and types
				{
					$data['diamond_type_name_ss1'] = $data['view_var']['diamond_type_name_ss1'] = 'Stone';
					$data['diamond_type_name_ss1_alias'] = $data['view_var']['diamond_type_name_ss1_alias'] = 'Cubic Zirconia';
					$data['diamond_purity_name_ss1'] = $data['diamond_color_name_ss1'] = $data['view_var']['diamond_purity_name_ss1'] = $data['view_var']['diamond_color_name_ss1'] = '-';
				}
				else
				{ $data['diamond_type_name_ss1_alias'] = $data['view_var']['diamond_type_name_ss1_alias'] = $data['diamond_type_name_ss1']; }
			}
			else
			{
				if( $cz_suffix == '_cz' ) 	//if CZ then change label and types
				{
					$data['diamond_type_name_ss1']= $data['view_var']['diamond_type_name_ss1'] = 'Semi Precious Gemstone';
					$data['diamond_type_name_ss1_alias'] = $data['view_var']['diamond_type_name_ss1_alias'] = $data['diamond_price_name_ss1']." (Semi Precious)";
				}
				else
				{ $data['diamond_type_name_ss1_alias'] = $data['view_var']['diamond_type_name_ss1_alias'] = $data['diamond_price_name_ss1']; }
			}
		
			//ready to ship check
			if($is_ready_to_ship===true &&  $data['product_ss1_priority_id']==$data['diamond_price_id_ss1'])
				$is_ready_to_ship = true;
			else
				$is_ready_to_ship = false;
		}
		
		if(isset($resArr['codeArr'][5]))		//side stone2 if exist
		{
			$imagefolder .= 'SIDESTONE2/'.$data['diamond_type_key_ss2'].'/'.$data['diamond_price_key_ss2'].'/';
		
			if($data['diamond_type_key_ss2'] == 'DIAMOND')
			{
				if( $cz_suffix == '_cz' ) 	//if CZ then change label and types
				{
					$data['diamond_type_name_ss2'] = $data['view_var']['diamond_type_name_ss2'] = 'Stone';
					$data['diamond_type_name_ss2_alias'] = $data['view_var']['diamond_type_name_ss2_alias'] = 'Cubic Zirconia';
					$data['diamond_purity_name_ss2'] = $data['diamond_color_name_ss2'] = $data['view_var']['diamond_purity_name_ss2'] = $data['view_var']['diamond_color_name_ss2'] = '-';
				}
				else
				{ $data['diamond_type_name_ss2_alias'] = $data['view_var']['diamond_type_name_ss2_alias'] = $data['diamond_type_name_ss2']; }
			}
			else
			{
				if( $cz_suffix == '_cz' ) 	//if CZ then change label and types
				{
					$data['diamond_type_name_ss2']= $data['view_var']['diamond_type_name_ss2'] = 'Semi Precious Gemstone';
					$data['diamond_type_name_ss2_alias'] = $data['view_var']['diamond_type_name_ss2_alias'] = $data['diamond_price_name_ss2']." (Semi Precious)";
				}
				else
				{ $data['diamond_type_name_ss2_alias'] = $data['view_var']['diamond_type_name_ss2_alias'] = $data['diamond_price_name_ss2']; }
			}
		
			//ready to ship check
			if($is_ready_to_ship===true &&  $data['product_ss2_priority_id']==$data['diamond_price_id_ss2'])
				$is_ready_to_ship = true;
			else
				$is_ready_to_ship = false;
				
			//check for additional stones
			$is_stone = true;
			$product_stone_number = 3;
			while( $is_stone )
			{
				if(isset($resArr['codeArr'][$product_stone_number+3]))
				{
					$imagefolder .= 'SIDESTONE'.$product_stone_number.'/'.$data['diamond_type_key_ss'.$product_stone_number].'/'.$data['diamond_price_key_ss'.$product_stone_number].'/';
						
					if($data['diamond_type_key_ss'.$product_stone_number] == 'DIAMOND')
					{
						$data['diamond_type_name_ss'.$product_stone_number.'_alias'] = $data['diamond_type_name_ss'.$product_stone_number];
		
						if( $cz_suffix == '_cz' ) 	//if CZ then change label and types
						{
							$data['diamond_type_name_ss'.$product_stone_number] = $data['view_var']['diamond_type_name_ss'.$product_stone_number] = 'Stone';
							$data['diamond_type_name_ss'.$product_stone_number.'_alias'] = $data['view_var']['diamond_type_name_ss'.$product_stone_number.'_alias'] = 'Cubic Zirconia';
							$data['diamond_purity_name_ss'.$product_stone_number] = $data['diamond_color_name_ss'.$product_stone_number] = $data['view_var']['diamond_purity_name_ss'.$product_stone_number] = $data['view_var']['diamond_color_name_ss'.$product_stone_number] = '-';
						}
						else
						{
							$data['diamond_type_name_ss'.$product_stone_number.'_alias'] = $data['view_var']['diamond_type_name_ss'.$product_stone_number.'_alias'] =
							$data['diamond_type_name_ss'.$product_stone_number];
						}
					}
					else
					{
						if( $cz_suffix == '_cz' ) 	//if CZ then change label and types
						{
							$data['diamond_type_name_ss'.$product_stone_number]= $data['view_var']['diamond_type_name_ss'.$product_stone_number] = 'Semi Precious Gemstone';
							$data['diamond_type_name_ss'.$product_stone_number.'_alias'] = $data['view_var']['diamond_type_name_ss'.$product_stone_number.'_alias'] = $data['diamond_price_name_ss'.$product_stone_number]." (Semi Precious)";
						}
						else
						{
							$data['diamond_type_name_ss'.$product_stone_number.'_alias'] = $data['view_var']['diamond_type_name_ss'.$product_stone_number.'_alias'] = $data['diamond_price_name_ss'.$product_stone_number];
						}
					}
				}
				else
				{ $is_stone = false; }
				$product_stone_number++;
			}
		}
	}
	else 
	{
		//$imagefolder = "assets/product/".$data['product_sku'];
		/**
		 * @author Cloudwebs
		 * Added on 27-06-2015
		 * A semi dynamic(only single level deep ) folder structure for attribute based inventory,
		 * applicable when attributes like color or colour is found.
		 */
		$imagefolder = front_end_hlp_attrBasedProductImageFolder( "assets/product/".$data['product_sku'], $resArr['codeArr'], $data);
	}
	
	/**
	 * expected delivery date calculaton
	 */
	$Date = date('d-m-Y', time());
	if($is_ready_to_ship===true)
	{
		$data['order_details_expected_delivery_date'] = date('d/m/Y', strtotime($Date. ' + 5 days'));
		$data['order_details_expected_delivery_date_org'] = strtotime($Date. ' + 5 days');
	}
	else
	{
		$data['order_details_expected_delivery_date'] = date('d/m/Y', strtotime($Date. ' + 14 days'));
		$data['order_details_expected_delivery_date_org'] = strtotime($Date. ' + 14 days');
	}
	
	/**
	 * Ring size applicable to jewellery INVENTORY only 
	 */
	if( hewr_isJewelryInventoryCheckWithId( $data["inventory_type_id"] ) )
	{
		//array used only in couple products like couple bands etc.
		$ring_sizeArr = array();
		if($is_ajax)	//data in array so that easily accessed in jquery
		{
			//$data['view_var'] = $resArr['res']->row_array();	change: On 30/12/2013 initialization of $data['view_var'] is done right at where row_array function called for $data initialization --> so disvantage and performance overhead is occured of unneccesary variable initilization in case of ajax is not required but implemented to render CZ telecasting
		
			//ring_size drop down
			if( isRing( $data['view_var']['product_accessories'], $data['view_var']['ring_size_region'] ) )
			{
				$sizeArr = getDropDownAryRingSize($data['view_var']['metal_price_id'], $data['view_var']['product_gender']);
				$data['view_var']['ring_size_drop_down'] = form_dropdown('ring_size_id', $sizeArr, $ring_size_id,'style="width:100px;padding:5px; font-size:10px;" title="Select Ring Size" id="ring_size_id"  type-="ring_size" onchange="ajaxCustomize(this)"');
			}
			else if( isCoupleBand( $data['view_var']['product_accessories'], $data['view_var']['ring_size_region'] ) )
			{
				$ring_sizeArr = explode( '|', $ring_size_id);
					
				//women ring size drop down for
				if( $CI->session->userdata('lType') == 'PC' )
				{
					$sizeArr = getDropDownAryRingSize($data['view_var']['metal_price_id'], 'F', false, array('' => '- Women -'));
					$data['view_var']['ring_size_id_f'] = form_dropdown('ring_size_id_f', $sizeArr, @$ring_sizeArr[0],'style="width:86px;padding:4px; font-size:12px; " title="Select Ring Size" type-="ring_size" onchange="ajaxCustomize(this)" class="ring_size" id="ring_size_id_f"');
				}
				else
				{
					$sizeArr = getDropDownAryRingSize($data['view_var']['metal_price_id'], 'F', false, array('' => '- W -'));
					$data['view_var']['ring_size_id_f'] = form_dropdown('ring_size_id_f', $sizeArr, @$ring_sizeArr[0],'style="width:45px;padding:4px; font-size:12px; " title="Select Ring Size" type-="ring_size" onchange="ajaxCustomize(this)" class="ring_size" id="ring_size_id_f"');
				}
					
		
				if( $CI->session->userdata('lType') == 'PC' )
				{
					$sizeArr = getDropDownAryRingSize($data['view_var']['metal_price_id'], 'M', false, array('' => '- Men -'));
					$data['view_var']['ring_size_id_m'] = form_dropdown('ring_size_id_m', $sizeArr, @$ring_sizeArr[1],'style="width:80px;padding:4px; font-size:12px; float:right;" title="Select Ring Size" type-="ring_size" onchange="ajaxCustomize(this)" class="ring_size" id="ring_size_id_m"');
				}
				else
				{
					$sizeArr = getDropDownAryRingSize($data['view_var']['metal_price_id'], 'M', false, array('' => '- M -'));
					$data['view_var']['ring_size_id_m'] = form_dropdown('ring_size_id_m', $sizeArr, @$ring_sizeArr[1],'style="width:45px;padding:4px; font-size:12px; float:right;" title="Select Ring Size" type-="ring_size" onchange="ajaxCustomize(this)" class="ring_size margin_l_10" id="ring_size_id_m"');
				}
		
			}
		
			if( !empty($ring_size_id) )
			{
				if( isRing( $data['view_var']['product_accessories'], $data['view_var']['ring_size_region'] ) )
				{
					$resRingSize = $CI->db->query( " SELECT ring_size_weight_diff FROM ring_size WHERE ring_size_id=".$ring_size_id." ")->row_array();
		
					//change weight as per ring size
					$data['view_var']['product_metal_weight'] = round($data['view_var']['product_metal_weight_alias'] + $resRingSize['ring_size_weight_diff'], 2)." gm (Approx)";
					$data['view_var']['product_price_weight'] = round($data['view_var']['product_price_weight'] + $resRingSize['ring_size_weight_diff'], 2)." gm (Approx)";
		
					//change price as per ring size
					$data['product_discounted_price'] = round( $data['view_var']['product_discounted_price'] + ( $data['view_var']['mp_price_difference'] *
							$resRingSize['ring_size_weight_diff'] ) , 0);
					$data['view_var']['product_discounted_price'] = lp(round( $data['view_var']['product_discounted_price'] + ( $data['view_var']['mp_price_difference'] *
							$resRingSize['ring_size_weight_diff'] ) , 0));
				}
				else if( isCoupleBand( $data['view_var']['product_accessories'], $data['view_var']['ring_size_region'] ) )
				{
					foreach($ring_sizeArr as $k=>$ar)
					{
						if( !empty($ar) )
						{
							$resRingSize = $CI->db->query("SELECT ring_size_weight_diff FROM ring_size WHERE ring_size_id=".$ar." ")->row_array();
		
							$data['view_var']['product_metal_weight_alias'] += $resRingSize['ring_size_weight_diff'];
							$data['view_var']['product_price_weight'] += $resRingSize['ring_size_weight_diff'];
		
							$data['product_discounted_price'] = round( $data['product_discounted_price'] + ( $data['view_var']['mp_price_difference'] *
									$resRingSize['ring_size_weight_diff'] ) , 0);
		
							$data['view_var']['product_discounted_price'] = round( $data['view_var']['product_discounted_price'] + ( $data['view_var']['mp_price_difference'] *
									$resRingSize['ring_size_weight_diff'] ) , 0);
						}
					}
		
					//change weight as per ring size
					$data['view_var']['product_metal_weight'] = round($data['view_var']['product_metal_weight_alias'], 2)." gm (Approx)";
					$data['view_var']['product_price_weight'] = round($data['view_var']['product_price_weight'], 2)." gm (Approx)";
		
					//change price as per ring size
					$data['view_var']['product_discounted_price'] = lp( round( $data['view_var']['product_discounted_price'], 0 ) );
				}
			}
			else
			{
				//product final price is sensitive to ring size
				$data['view_var']['product_discounted_price'] = lp(($data['view_var']['product_discounted_price']));
			}
		
			$data['view_var']['product_price_calculated_price'] = lp(($data['view_var']['product_price_calculated_price']));
			$data['view_var']['product_discount'] = $data['view_var']['product_discount'].' %';
			$data['view_var']['order_details_expected_delivery_date'] = $data['order_details_expected_delivery_date'];
			//$data['order_details_expected_delivery_date_org'] = $data['order_details_expected_delivery_date_org'];
		}
		else
		{
			//ring_size drop down
			if(  isRing( $data['product_accessories'] ) )
			{
				$sizeArr = getDropDownAryRingSize($data['metal_price_id'], $data['product_gender']);
				$data['ring_size_drop_down'] = form_dropdown('ring_size_id', $sizeArr, $ring_size_id,'style="width:100px;padding:5px; font-size:10px;" title="Select Ring Size" id="ring_size_id" type-="ring_size" onchange="ajaxCustomize(this)"');
			}
			else if( isCoupleBand( $data['product_accessories'] ) )
			{
				$ring_sizeArr = explode('|', $ring_size_id);
					
				//women ring size drop down for
				if( $CI->session->userdata('lType') == 'PC' )
				{
					$sizeArr = getDropDownAryRingSize($data['metal_price_id'], 'F', false, array('' => '- Women -'));
					$data['ring_size_id_f'] = form_dropdown('ring_size_id_f', $sizeArr, $ring_sizeArr[0],'style="width:86px;padding:4px; font-size:12px; " title="Select Ring Size" type-="ring_size" onchange="ajaxCustomize(this)" class="ring_size" id="ring_size_id_f"');
				}
				else
				{
					$sizeArr = getDropDownAryRingSize($data['metal_price_id'], 'F', false, array('' => '- W -'));
					$data['ring_size_id_f'] = form_dropdown('ring_size_id_f', $sizeArr, $ring_sizeArr[0],'style="width:50px;padding:4px; font-size:12px; " title="Select Ring Size" type-="ring_size" onchange="ajaxCustomize(this)" class="ring_size" id="ring_size_id_f"');
				}
					
		
				if( $CI->session->userdata('lType') == 'PC' )
				{
					$sizeArr = getDropDownAryRingSize($data['metal_price_id'], 'M', false, array('' => '- Men -'));
					$data['ring_size_id_m'] = form_dropdown('ring_size_id_m', $sizeArr, @$ring_sizeArr[1],'style="width:80px;padding:4px; font-size:12px; float:right;" title="Select Ring Size" type-="ring_size" onchange="ajaxCustomize(this)" class="ring_size" id="ring_size_id_m"');
				}
				else
				{
					$sizeArr = getDropDownAryRingSize($data['metal_price_id'], 'M', false, array('' => '- M -'));
					$data['ring_size_id_m'] = form_dropdown('ring_size_id_m', $sizeArr, @$ring_sizeArr[1],'style="width:50px;padding:4px; font-size:12px; float:right;" title="Select Ring Size" type-="ring_size" onchange="ajaxCustomize(this)" class="ring_size margin_l_10" id="ring_size_id_m"');
				}
		
			}
		
			if( !empty($ring_size_id) )
			{
				if( !isCoupleBand( $data['product_accessories'] ) )
				{
					$resRingSize = $CI->db->query("SELECT ring_size_weight_diff FROM ring_size WHERE ring_size_id=".$ring_size_id." ")->row_array();
		
					//change weight as per ring size
					$data['product_metal_weight'] = round($data['product_metal_weight_alias'] + $resRingSize['ring_size_weight_diff'], 2)." gm (Approx)";
					$data['product_price_weight'] = round($data['product_price_weight'] + $resRingSize['ring_size_weight_diff'], 2)." gm (Approx)";
		
					//change price as per ring size
					$data['product_discounted_price'] = round( $data['product_discounted_price'] + ( $data['mp_price_difference'] * $resRingSize['ring_size_weight_diff'] ) , 0);
				}
				else
				{
					foreach($ring_sizeArr as $k=>$ar)
					{
						if( !empty($ar) )
						{
							$resRingSize = $CI->db->query("SELECT ring_size_weight_diff FROM ring_size WHERE ring_size_id=".$ar." ")->row_array();
							$data['product_metal_weight_alias'] += $resRingSize['ring_size_weight_diff'];
							$data['product_price_weight'] += $resRingSize['ring_size_weight_diff'];
		
							$data['product_discounted_price'] = round( $data['product_discounted_price'] + ( $data['mp_price_difference'] *
									$resRingSize['ring_size_weight_diff'] ) , 0);
						}
					}
		
					//change weight as per ring size
					$data['product_metal_weight'] = round($data['product_metal_weight_alias'], 2)." gm (Approx)";
					$data['product_price_weight'] = round($data['product_price_weight'], 2)." gm (Approx)";
				}
			}
		}
	}
	else 
	{
		if($is_ajax)
		{
			$data['view_var']['product_discounted_price'] = lp(($data['view_var']['product_discounted_price']));
			$data['view_var']['product_price_calculated_price'] = lp(($data['view_var']['product_price_calculated_price']));
			$data['view_var']['product_discount'] = $data['view_var']['product_discount'].' %';
			$data['view_var']['order_details_expected_delivery_date'] = $data['order_details_expected_delivery_date'];
		}
	}

	/**
	 * custom client wise 
	 */
	$data["qty"] = $data["view_var"]["qty"] = 1;
	$data['product_discounted_price_tot'] = $data['product_discounted_price'];
	$data['view_var']['product_discounted_price_tot'] = lp(($data['product_discounted_price_tot']));
	if( hewr_isQtyInAttributeInventoryCheckWithId( $data["inventory_type_id"] ) )
	{
		$index = getProdQtyOptionsIndex( $data["product_generated_code_info"], $data["inventory_type_id"] );
		$data["qty"] = $data["view_var"]["qty"] = $data["pa_real_value_".$index];

		$data['product_discounted_price_tot'] = $data['product_discounted_price'] * $data["qty"];
		//$data['product_price_calculated_price_tot'] = $data['product_price_calculated_price'] * $data["qty"];
		if($is_ajax)
		{
			$data['view_var']['product_discounted_price_tot'] = lp(($data['product_discounted_price_tot']));
			//$data['view_var']['product_price_calculated_price_tot'] = lp(($data['product_discounted_price_tot']));
		}
	}
	
	/**
	 * generate image folder path
	 */
	if( hewr_isComponentBasedCheckWithId( $data["inventory_type_id"] ) )
	{
		//images for particular selection
		$imagefolder .= $data['metal_color_key'];
	}

	
	/**
	 * added on 27-06-2015 to support attribute based semi dynamic folder structure
	 */
	$data["is_dynamic_images"] = 0;
	
	/**
	 * fetch particular folder images
	 */
	$product_images = fetchProductImages($imagefolder);			
	//echo $imagefolder."<br><br>";

	if( 
		hewr_isComponentBasedCheckWithId( $data["inventory_type_id"] ) 
		|| 
		( $imagefolder != 'assets/product/'.$data['product_sku'] && $imagefolder != 'assets/product/'.$data['product_sku'].'/' ) 
	  )
	{
		$data["is_dynamic_images"] = 1;
		
		//images at root of product folder of models if exist
		$product_model_images = fetchProductImages('assets/product/'.$data['product_sku']);
		
		//both folder merge	  
		if((is_array($product_images) && sizeof($product_images)>0) && (is_array($product_model_images) && sizeof($product_model_images)>0))
		{
			$data['product_images'] = array_merge($product_images,$product_model_images);	
		}
		else if(is_array($product_images) && sizeof($product_images)>0)
		{
			$data['product_images'] = $product_images;
		}
		else if(is_array($product_model_images) && sizeof($product_model_images)>0)
		{
			$data['product_images'] = $product_model_images;
		}
		else
			$data['product_images'][0] = '';	//initialize empty array
	}
	else 
	{
		$data['product_images'] = $product_images;
	}
	

	if(!$is_ajax && !$is_cart_or_checkout)
	{
		//related category
		if(@$data['product_related_category_id'] != '')
		{
			$data['related_links'] = getPipeStringData('product_categories', 'category_id', 'category_id, category_name, custom_page_title,category_alias', @$data['product_related_category_id']);
		}
		
		//related products
		if(@$data['product_related_products_id'] != '')
		{
			$data['related_productsArr'] = getPipeStringData('product', 'product_id', 'product_id,product_name,product_angle_in ', @$data['product_related_products_id']);
		}
		
		//gift listing
		if(@$data['category_id'] != '')
		{
			$data['gift_image'] = getGiftData('gift', 'category_id', 'gift_id,gift_image,gift_name', @$data['category_id']);
		}
	}

	//
	if( hewr_isComponentBasedCheckWithId( $data["inventory_type_id"] ) && !$is_cart_or_checkout ) 
	{
		$data["resMakingPrices"] = getMakingPrices( $data["product_price_id"], $cz_suffix );	
		
		if( !empty( $data["resMakingPrices"] ) && $is_ajax )
		{
			foreach( $data["resMakingPrices"] as $k=>$ar )
			{
				$data[ "view_var" ][ $ar["pmp_key"] ] = lp( $ar["pmp_value"] );	 
			}
		}
	}

	if( !empty($pageToken) )
	{
		/**
		 * save codeArr in session
		 * from 28-03-2015 parsed array from "product_generated_code" will be saved in session while product_generated_code_info is returned in view, 
		 * also instead of saving parsed array in session now only code string is saved
		 */
		$CI->session->set_userdata( array( 'codeArr_'.$pageToken=> $data["product_generated_code"], 'product_price_id_'.$pageToken=>$product_price_id, 'cz_suffix_'.$pageToken=>$cz_suffix) );
	}

	$data['codeArr'] = $resArr['codeArr'];
	$data["cz_suffix"] = $cz_suffix;
	unset($CI);
	return  $data;
}

/**
 * @abstract will return products' making prices applicable for particular product_price_id 
 */
function getMakingPrices( $product_price_id, $cz_suffix )
{
	if( empty( $cz_suffix ) )
	{
		$cz_suffix = "GENERAL";	
	}
	else if( $cz_suffix == "_mount" )
	{
		$cz_suffix = "SOL";	
	}
	
	if( MANUFACTURER_ID == 7 ) 
	{
		return executeQuery( " SELECT * FROM product_making_prices WHERE product_price_id=".$product_price_id." AND pmp_key LIKE '".$cz_suffix."%' " );
	}
	else
	{
		return executeQuery( " SELECT * FROM product_making_prices_cctld WHERE manufacturer_id=".MANUFACTURER_ID." 
							   AND product_price_id=".$product_price_id." AND pmp_key LIKE '".$cz_suffix."%' " );
	}
}

/**
 * @deprecated
 * @abstract update product quantity
 */
function updateProductQuantity($product_id,$product_value_quantity)
{
	/**
	 * on 25-04-2015
	 * Commented since Actual warehouse module was added
	 */
	return false;
// 	$CI =& get_instance();

// 	$temp = getField("product_value_quantity","product_value","product_id",$product_id);
// 	$product_value_quantity = (int)$temp - (int)$product_value_quantity;
// 	$CI->db->where("product_id",$product_id)->update("product_value",array('product_value_quantity'=>$product_value_quantity));
//	unset($CI);
	
}

/*
+------------------------------------------------------------------+
	@author Cloudwebs
	Function will FETCH layout for page specified in where condition for apecified css class
	Function will LOAD layout for result passed if not empty
Input ->
	@param $css_class : class name of layout position.
	@param $where : where condition to be appended in sql query.
	@param $css_class : class name of layout position.
	@param $extraStart : html/css to be appended at start of particular layout
	@param $extraEnd : html/css to be appended at end of particular layout
	@return : array of row or null empty array if not specified
+------------------------------------------------------------------+
*/
function LoadLayout($css_class,$where,$extraStart='',$extraEnd='')
{
		$resLayout = executeQuery("SELECT module_manager_serialize_menu,module_manager_table_name,module_manager_field_name,
								  module_manager_primary_id,module_manager_css FROM module_manager m INNER JOIN banner_position b 
							ON b.banner_position_id=m.position_id WHERE banner_position_alias='".$css_class."' AND module_manager_status=0  ORDER BY module_manager_sort_order");
		if(!empty($resLayout))
		{
			$resMenu = executeQuery("SELECT front_menu_id,front_menu_type_id FROM front_menu ".$where);

			if(is_array($resLayout) && sizeof($resLayout)>0)
			{
				echo $extraStart;
				$cnt = 0;
				$style = "";
				foreach($resLayout as $k=>$ar)
				{
					$module_manager_serialize_menu = unserialize($resLayout[$k]['module_manager_serialize_menu']);
	
					if(array_key_exists($resMenu[0]['front_menu_type_id'],$module_manager_serialize_menu))
					{
						if(in_array($resMenu[0]['front_menu_id'],$module_manager_serialize_menu[$resMenu[0]['front_menu_type_id']]))
						{
							$cnt++;
							
							if($resLayout[$k]['module_manager_table_name'] == "front_hook")
							{
								$type = getField("front_hook_type",$resLayout[$k]['module_manager_table_name'],$resLayout[$k]['module_manager_field_name'],$resLayout[$k]['module_manager_primary_id']);
								if($type == "E")
								{
									$CI =& get_instance();
									$CI->load->view('elements/'.$resLayout[$k]['module_manager_primary_id']);	
									unset($CI);
								}
							}
							else if($resLayout[$k]['module_manager_table_name'] == "article")
							{
								$html = getField("article_description",$resLayout[$k]['module_manager_table_name'],$resLayout[$k]['module_manager_field_name'],$resLayout[$k]['module_manager_primary_id']);
								echo _pwu($html);
							}
							else if($resLayout[$k]['module_manager_table_name'] == "banner")
							{
								$resbanner = executeQuery("SELECT banner_image,banner_image_alt_text,banner_link FROM banner WHERE banner_id=".$resLayout[$k]['module_manager_primary_id']." ");
								if(!empty($resbanner))
								{
									echo '<div class="banner_div">
										  <a href="'.$resbanner[0]['banner_link'].'">	
										  <img src="'.asset_url($resbanner[0]['banner_image']).'" alt="'.$resbanner[0]['banner_image_alt_text'].'" width="1002" />
										  </a>
										  </div>';
								}
							}
/*							else if($resLayout[$k]['module_manager_table_name'] == "product_categories" && $css_class == "middle")
							{
								$resCat = executeQuery("SELECT category_image,category_name,front_menu_id,category_id FROM product_categories c INNER JOIN front_menu m
														ON m.front_menu_primary_id=c.category_id  WHERE category_id=".$resLayout[$k]['module_manager_primary_id']." ");
	
								
								if(!empty($resCat))
								{
									$cat_url = getCatUrl($resCat[0]['category_id'],$resCat[0]['front_menu_id']);
									
										echo '<div '.$resLayout[$k]['module_manager_css'].'>';
										echo '<a href="'.$cat_url.'"><img src="'.load_image($resCat[0]['category_image']).'"></a>
											  <div class="pro-text">'.$resCat[0]['category_name'].'</div>
											  </div>';
								}
	
	
							}
*/						}
					}
				}
				echo $extraEnd;
			}
		}
	}


/**
 * @author Cloudwebs
 * @abstract function will return pincode_id and if pincode not exist then insert pincode if pincode and return it's pincode_id
 */
	function getPincodeId( $data )
	{
		$CI =& get_instance();
		$pincode_id = exeQuery( "SELECT pincode_id FROM pincode 
								WHERE pincode='".$data['pincode']."' AND state_id='".$data['state_id']."' 
								AND cityname='".$data['address_city']."' AND areaname='".$data['customer_address_landmark_area']."' ", true, "pincode_id" ); 

		if( empty( $pincode_id ) )
		{
			$CI->db->query("INSERT INTO pincode(pincode, areaname, cityname, state_id) 
							values('".$data['pincode']."', '".$data['customer_address_landmark_area']."', '".$data['address_city']."', 
							'".$data['state_id']."' )");
			return $CI->db->insert_id();	
		}
		else 
		{
			return $pincode_id;
		}
	}

/**
 * @author Cloudwebs
 * @abstract function will fetch address from cutomer_address and state and pincode tables
 */
	function getAddress($customer_address_id, $suffix='')
	{
		$CI =& get_instance();
	
		return $CI->db->query("SELECT c.customer_address_id, c.customer_address_firstname as customer_address_firstname".$suffix.", c.customer_address_lastname as customer_address_lastname".$suffix.", 
						  CONCAT(c.customer_address_firstname, ' ', c.customer_address_lastname) as customer_name".$suffix.", 
						  c.customer_address_address as customer_address_address".$suffix.", c.customer_address_landmark_area as customer_address_landmark_area".$suffix.", 
						  c.customer_address_company as customer_address_company".$suffix.", c.customer_address_phone_no as customer_address_phone_no".$suffix.", 
						  c.customer_address_city as customer_address_city".$suffix.", p.cityname as address_city".$suffix.", 
						  c.customer_address_zipcode as customer_address_zipcode".$suffix.", p.pincode as pincode".$suffix.", p.state_id as state_id".$suffix.", 
						  s.state_name as state_name".$suffix.", p.cityname as cityname".$suffix.", p.areaname as areaname".$suffix.", 
						  co.country_id as country_id".$suffix.", co.country_code as country_code".$suffix.", co.country_name as country_name".$suffix."  
						  FROM customer_address c 
						  INNER JOIN pincode p ON p.pincode_id=c.customer_address_zipcode 
						  INNER JOIN state s ON s.state_id=p.state_id  
						  INNER JOIN country co ON co.country_id=s.country_id
						  WHERE customer_address_id=".$customer_address_id." ")->row_array();
						  
	}
	
/* 
 * @author Cloudwebs
 * @abstract function will fetch email_list_id for email_id if it is available then return unsubscribe link with email and email_list_id as get parameter
*/
	function getUnsubscribeLink( $email_id )
	{
		$CI =& get_instance();
		
		$resEmail = $CI->db->query("SELECT email_list_id FROM email_list WHERE email_id='".$email_id."' ")->row_array();
		
		if( !empty( $resEmail ) )		
		{
			return site_url('unsubscribe?em='.$email_id.'&eli='.$resEmail['email_list_id']);	
		}
		else
		{
			return site_url('account/unsubscribe');	
		}
	}
	
/**
 * @author Cloudwebs
 * @abstract Function will randomize sort order of product inventory
 */	
	function randomSortOrder()
	{
		$CI =& get_instance();
		
		
		$resCnt = $CI->db->query("SELECT count(product_id) as 'Count' FROM product")->row_array();
		$CI->db->query('UPDATE product SET product_sort_order = FLOOR('.$resCnt['Count'].' * RAND()) + 1;');
		
	}	
	
/**
 * @author Cloudwebs
 * @abstract Function will return ring size name funxtion created to check if couple product then repipe the id and return two names
 */	
	function getRingSizeName( $ring_size_id )
	{
		$CI =& get_instance();
		
		if( strpos($ring_size_id, '|') !== FALSE)
		{
			$ring_sizeArr = explode('|', $ring_size_id);
			$resCnt = $CI->db->query("SELECT CONCAT((SELECT ring_size_name FROM ring_size WHERE ring_size_id=".(int)$ring_sizeArr[0]."),'|',
									(SELECT ring_size_name FROM ring_size WHERE ring_size_id=".(int)$ring_sizeArr[1].")) as ring_size_name
									FROM ring_size LIMIT 1 ")->row_array();

			if( !isEmptyArr($resCnt) )
			{
				return $resCnt['ring_size_name'];
			}
			else 
			{
				return "N/A"; 
			}
		}
		else
		{
			$resCnt = $CI->db->query("SELECT ring_size_name FROM ring_size WHERE ring_size_id=".$ring_size_id." ")->row_array();
			
			if( !isEmptyArr($resCnt) )
			{
				return $resCnt['ring_size_name'];
			}
			else
			{
				return "N/A";
			}
		}
		
	}	
	
/**
 * @author Cloudwebs
 * @abstract Function will return price filter array
 */	
	function getPriceFilter()
	{
		$resFilter = executeQuery("SELECT filters_table_name, filters_table_id FROM filters WHERE filters_table_name='Price_Filter' ");
		
		if( !empty($resFilter) )
		{
			$resArr = array();
		
			$filter_price_range = $resFilter[0]['filters_table_id'];
			$filter_price_rangeArr = explode("|",$filter_price_range);
	
			//price filter start range
			$resArr[0][0] = 0; $resArr[0][1] = $filter_price_rangeArr[0]; $resArr[0][2] = "Below ".lp( $filter_price_rangeArr[0], 0 );		
			
			$toRange =0; $i =1;
			for( $range=$filter_price_rangeArr[0]; $range<$filter_price_rangeArr[2]; $range +=$filter_price_rangeArr[1])
			{
				$toRange = (($range+$filter_price_rangeArr[1])>$filter_price_rangeArr[2])?$filter_price_rangeArr[2]:($range+$filter_price_rangeArr[1]);
				
				$resArr[$i][0] = $range; $resArr[$i][1] = $toRange; $resArr[$i][2] = lp( $range, 0 )." - ".lp( $toRange, 0 );		
				$i++;
			}
	
			//price filter start range
			$resArr[$i][0] = $filter_price_rangeArr[2]; $resArr[$i][1] = 0; $resArr[$i][2] = "Above ".lp( $filter_price_rangeArr[2], 0 );		

			return array ( 'filterArr'=>$resArr, 'filters_table_name'=> strtolower( $resFilter[0]['filters_table_name'] ) );
		}
		
		return false;
	}

/**
 * @author Cloudwebs
 * @abstract Function will return minimum three product variant product_price_id with alternate metal clarity and color 
 */	
	function getProductVariant( $codeArr )
	{
		$CI =& get_instance();
		
		$product_generated_code = genProdcodeFromArr( $codeArr );
		
		$resArr = array();
		$purity_10kArr = array(0=>" (mpp.metal_purity_key='10K' AND mc.metal_color_key='YELLOW') ", 1=>" (mpp.metal_purity_key='10K' AND mc.metal_color_key='WHITE') ", 
								2=>" (mpp.metal_purity_key='10K' AND mc.metal_color_key='ROSE') ");

		$purity_14kArr = array(0=>" (mpp.metal_purity_key='14K' AND mc.metal_color_key='YELLOW') ", 1=>" (mpp.metal_purity_key='14K' AND mc.metal_color_key='WHITE') ", 
								2=>" (mpp.metal_purity_key='14K' AND mc.metal_color_key='ROSE') ");

		$purity_18kArr = array(0=>" (mpp.metal_purity_key='18K' AND mc.metal_color_key='YELLOW') ", 1=>" (mpp.metal_purity_key='18K' AND mc.metal_color_key='WHITE') ", 
								2=>" (mpp.metal_purity_key='18K' AND mc.metal_color_key='ROSE') ");
		
		$is_res_three = false;		
		while( !$is_res_three )
		{
			$resArr = $CI->db->query("SELECT product_price_id FROM product_price pp INNER JOIN product p 
							ON (p.product_id=pp.product_id AND p.product_cs_priority_id=pp.cs_diamond_price_id AND 
							p.product_ss1_priority_id=pp.ss1_diamond_price_id AND p.product_ss2_priority_id=pp.ss2_diamond_price_id) 
							INNER JOIN metal_price mp ON mp.metal_price_id=pp.metal_price_id 
							INNER JOIN metal_purity mpp ON mpp.metal_purity_id=mp.metal_purity_id 
							INNER JOIN metal_color mc ON mc.metal_color_id=mp.metal_color_id 
							WHERE pp.product_id=".$codeArr[1]." AND pp.product_generated_code<>'".$product_generated_code."' 
							AND ( ".$purity_10kArr[ rand(0,2) ]." OR ".$purity_14kArr[ rand(0,2) ]." OR ".$purity_18kArr[ rand(0,2) ]." ) ")->result_array();
							
			if( sizeof($resArr) >= 3)
			{
				$is_res_three = true;	
			}
		}
		
		return $resArr;
	}
	
/**
 * @author Cloudwebs
 * @abstract Function will fetch diamond details from database for diamond_price_id specified
 */	
	function fetchDiamondDetail( $diamond_price_id=0, $dp_rapnet_lot_no='', $dp_stock_no='')
	{
		$CI =& get_instance();
		
		//query parts
		$res = diaDetailSelJoinPart();
		$where = $res['where'];	
		if( $diamond_price_id != 0 ) { $where .= " AND d.diamond_price_id=".$diamond_price_id." "; } 
		else if( $dp_rapnet_lot_no != '' ) { $where .= " AND d.dp_rapnet_lot_no=".$dp_rapnet_lot_no." "; } 
		else if( $dp_stock_no != '' ) { $where .= " AND d.dp_stock_no='".$dp_stock_no."' "; } 
		else { return false; }
		return $CI->db->query($res['select'].$res['join'].$where)->row_array();
	}

/**
 * @author Cloudwebs
 * @abstract Function will return canonical url for product details page
 */	
	function getCanonicalUrl( $prodUrl )
	{
		$rpos = strrpos($prodUrl, "-");
		return substr($prodUrl, 0, $rpos);
	}
		
/**
 * @author Cloudwebs
 * @abstract Function will return average of solitaire diamond price from RapNet inventory for the specified clarity, color, weight and shape
 * Note*: it will calc average from the rapnet inventory at a time as and when diamond price was calculated however diamond price for solitaire category updated each hour if specified...
 * connection to the database here will be from perrian.com because only there price are updatted each hour
 */	
	function avgSolitaireDiamondPrice( $diamond_price_id, $diamond_shape_id, $weight, $diamond_color_key="'I'", $diamond_purity_key="'SI1'", $callNo=0, $endWeight=0 )
	{
		$CI =& get_instance();
		if( $endWeight == 0 ) { $endWeight = $weight; }
		//$conn = mysql_connect('www.perrian.com', 'localcon', '1,)8KLc]ll~$');
		//var_dump($conn);
		
		//$diaDetail = fetchDiamondDetail( $diamond_price_id ); : for now static for IJ_SI
		$diamond_color_idArr = fetchIdArrForKey( $diamond_color_key, 'diamond_color_id', 'diamond_color_name', 'diamond_color_key', 'diamond_color' );
		$diamond_purity_idArr = fetchIdArrForKey( $diamond_purity_key, 'diamond_purity_id', 'diamond_purity_name', 'diamond_purity_key', 'diamond_purity' );
			
		if( MANUFACTURER_ID == 7 ) 
		{
			$query = " SELECT AVG(dp_final_price) as 'AVG_PRICE' FROM diamond_price WHERE dp_status=0 
						AND dp_final_price>0 
						AND diamond_shape_id=".$diamond_shape_id." 
						AND diamond_color_id IN(".implode(",", $diamond_color_idArr ).") 
						AND diamond_purity_id IN(".implode(",", $diamond_purity_idArr ).") ";
		}
		else 
		{
			$query = " SELECT AVG(dctl.dp_final_price) as 'AVG_PRICE' 
						FROM diamond_price d INNER JOIN diamond_price_cctld dctl 
						ON ( dctl.manufacturer_id=".MANUFACTURER_ID." AND dctl.diamond_price_id=d.diamond_price_id ) 
					 	WHERE dctl.dp_status=0 
						AND dctl.dp_final_price>0 
						AND diamond_shape_id=".$diamond_shape_id." 
						AND diamond_color_id IN(".implode(",", $diamond_color_idArr ).") 
						AND diamond_purity_id IN(".implode(",", $diamond_purity_idArr ).") ";
		}
		$query .= " AND dp_weight BETWEEN ".$weight." AND ".$endWeight." ";
		$query .= "AND dp_rapnet_lot_no<>0 ";	
		
								
		$res = $CI->db->query( $query )->row_array();
		
		//echo $CI->db->last_query()." <br><br>";
		
		
		if( (int)$res['AVG_PRICE'] == 0) 
		{  
			if( $callNo == 0 )
				return avgSolitaireDiamondPrice( $diamond_price_id, $diamond_shape_id, $weight, "'J'", "'SI1'", ++$callNo );
			else if( $callNo == 1 )
				return avgSolitaireDiamondPrice( $diamond_price_id, $diamond_shape_id, $weight, "'I','J'", "'SI1'", ++$callNo );	
			else if( $callNo == 2 )
				return avgSolitaireDiamondPrice( $diamond_price_id, $diamond_shape_id, $weight, "'I','J'", "'SI1','SI2'", ++$callNo );	
			else if( $callNo == 3 )
				return avgSolitaireDiamondPrice( $diamond_price_id, $diamond_shape_id, $weight, "'I','J'", "'SI1','SI2'", ++$callNo );	
			else if( $callNo == 4 )
				return avgSolitaireDiamondPrice( $diamond_price_id, $diamond_shape_id, $weight, "'I','I+','J'", "'SI1','SI2','SI1+'", ++$callNo );	
			else if( $callNo == 5 )
				return avgSolitaireDiamondPrice( $diamond_price_id, $diamond_shape_id, $weight, "'I','J','I+','J+'", "'SI1','SI2','SI1+','SI2+'", ++$callNo );	
			else if( $callNo == 6 )
				return avgSolitaireDiamondPrice( $diamond_price_id, $diamond_shape_id, $weight, "'I','J','I+','J+','I-'", "'SI1','SI2','SI1+','SI2+','SI1-'", ++$callNo );	
			else if( $callNo == 7 )
				return avgSolitaireDiamondPrice( $diamond_price_id, $diamond_shape_id, $weight, "'I','J','I+','J+','I-','J-'", "'SI1','SI2','SI1+','SI2+','SI1-','SI2-'", ++$callNo );	
			else if( $callNo == 8 )
				return avgSolitaireDiamondPrice( $diamond_price_id, $diamond_shape_id, $weight, "'I','J','I+','J+','I-','J-'", "'SI1','SI2','SI1+','SI2+','SI1-','SI2-'", ++$callNo, ($weight+0.01) );	
			else if( $callNo == 9 )
				return avgSolitaireDiamondPrice( $diamond_price_id, $diamond_shape_id, $weight, "'I','J','I+','J+','I-','J-'", "'SI1','SI2','SI1+','SI2+','SI1-','SI2-'", ++$callNo, ($weight+0.02) );	
		}
		
		return $res['AVG_PRICE'];
	}
	
	/**
 * @author Cloudwebs
 * @abstract function will return parent category type like Ring, Earring, pendants etc...
 */
	function fetchParentType( $category_id='', $product_price_id=0 )
	{
		if(  $category_id == '' ) 
		{
			if( $product_price_id != 0 )
			{
				$category_id = exeQuery(" SELECT category_id FROM product_price pp INNER JOIN product p ON p.product_id=pp.product_id WHERE pp.product_price_id=".$product_price_id." ", 
										true, 'category_id');
			}
			else { return ''; } 
		}
		//category mapping
		$parent_category = '';
		if( strpos( " ".$category_id, RING_PCID.'|' ) == 1 )	
			return 'Ring';
		else if( strpos( " ".$category_id, PENDANT_PCID.'|' ) == 1 )	
			return 'Pendant';			
		else if( strpos( " ".$category_id, EARRING_PCID.'|' ) == 1 )	
			return 'Earring';
	}

/**
 * @author Cloudwebs
 * @abstract function record any page accessed by user
 */
	function recordPageAccess()
	{
		$CI =& get_instance();
		$req = getCurrPageUrl( ); 
		
		$ref = "";
		if(isset($_SERVER["HTTP_REFERER"]))
			$ref = $_SERVER["HTTP_REFERER"];
			
		$CI->db->insert( "page_accesses", array( 'sessions_id'=> $CI->session->userdata('sessions_id'), 'session_id'=> session_id(), 'customer_id'=>(int)$CI->session->userdata('customer_id'), 'pa_url'=>$req, 'pa_referell_url'=>$ref));
	}

/**
 * @author Cloudwebs
 * @abstract function will return chat module config applicable for current page
 */
	function getChatConfig()
	{
		$CI =& get_instance();
		$curr_page = getCurrPageUrl( );
		$CI->session->set_userdata( array('curr_page'=>$curr_page) );
		$res = executeQuery(" SELECT p.*, coalesce(l.l_key, 'EN_US') as l_key FROM ch_position p 
							  LEFT JOIN languages l 
							  ON l.languages_id=p.languages_id 
							  WHERE p_status=0 AND p.`p_allowed_url` LIKE '".$curr_page."' 
							  ORDER BY p_sort_order ");

		if( !empty($res) )	//page specific chat pop up
		{
			return $res[0];
		}
		else				//if not available then global chat pop up
		{
			$res = executeQuery( "SELECT p.*, coalesce(l.l_key, 'EN_US') as l_key FROM ch_position p 
								LEFT JOIN languages l 
								ON l.languages_id=p.languages_id 
								WHERE p_status=0 
								ORDER BY p_sort_order ");
			if( !empty($res) )	//page specific chat pop up
			{
				return $res[0];
			}
			else
			{
				return array();
			}
		}
	}	

/**
 * @author Cloudwebs
 * @abstract function will decide on ajax call if pro active chat is required and start pro active chat if respective OR any admin operator is online...
 * If multiple pro active chat messages is defined then sort_order and after that time_on_site is given priority
 */
	function getProActiveChatConfig( $position_id, $am_id=0, $ct_id=0)
	{
		$CI =& get_instance();
		//$CI->session->set_userdata( array( 'chat_id'=> FALSE, 'customer_id'=>FALSE, 'customer_group_type'=>FALSE ) );

		//handle multiple chat session on front side
		if( !empty($ct_id) )
		{
			$CI->session->set_userdata( array( 'chat_id' => $ct_id ) );	
		}

		if( isAdmin() )
		{
			return '';	
		}
		else if( $CI->session->userdata('chat_id') !== FALSE )
		{
			$chat_id = (int)$CI->session->userdata('chat_id');
			//set chat as active and initialize admin agent chat session on front side
			if( !empty($am_id) )
			{
				//set chat as active
				$CI->db->where( "chat_id", $chat_id)->update("ch_chat", array('c_status'=>2, 'c_modified_date'=>'NOW()') );

				//record msg
				chatMsg( $chat_id, $am_id, 'A', '');	
			}
			
			$chatHistory = getChatHistory( $chat_id );
			if( !empty($chatHistory) )
			{
				return $chatHistory;	
			}
		}
		else if( isNoProChat() )
		{
			return '';	
		}
		
		//current user's time on site till now
		$sessTime = time() - $CI->session->userdata('sess_strt_time');
		
		$resArr = array();
		$apciRes =executeQuery(" SELECT apci.*, au.admin_user_firstname, au.admin_profile_image, au.admin_user_phone_no, l.sessions_id, l.session_id, l.l_user_type, l.l_session_status 
								FROM ch_abstract_proactive_chat_invitation apci 
								LEFT JOIN admin_user au 
								ON au.admin_user_id=apci.admin_user_id 
								LEFT JOIN logins l 
								ON ( l.cust_admin_user_id=au.admin_user_id AND l.l_user_type='A' AND l.l_session_status=1 ) 
								WHERE apci_status=0 AND ( position_id=0 OR position_id=".$position_id." ) 
								ORDER BY apci_sort_order ");
		
		if( !empty($apciRes) )
		{
			$pageViews = $CI->db->query(" SELECT pa.customer_id, coalesce( c.customer_firstname, 'Guest') as customer_firstname, 
										  ( SELECT COUNT(1) FROM page_accesses WHERE session_id=pa.session_id ) as 'Count' FROM page_accesses pa 
										  LEFT JOIN customer c 
										  ON c.customer_id=pa.customer_id 
										  WHERE session_id='".session_id()."' 
										  ORDER BY page_accesses_id DESC LIMIT 1 ")->row_array();
			
			$min = $apciRes[0]['apci_time_on_site']; 
			$tempK = 0; 
			$resAdmin;
			foreach( $apciRes as $k=>$ar )
			{
				if( $ar['apci_pageviews'] <= $pageViews['Count'] || $ar['apci_time_on_site'] <= $sessTime )			
				{
					if( $ar['admin_user_id'] != 0 )
					{
						if( $ar['l_user_type'] == 'A' && $ar['l_session_status'] == 1 )
						{
							$resAdmin['admin_user_id'] = $ar['admin_user_id']; 
							$resAdmin['sessions_id'] = $ar['sessions_id'];
							$resAdmin['admin_user_firstname'] = $ar['admin_user_firstname']; 
							$resAdmin['admin_profile_image'] = $ar['admin_profile_image']; 
							$resAdmin['admin_user_phone_no'] = $ar['admin_user_phone_no']; 
							$resAdmin['session_id'] = $ar['session_id'];
						}
						else
						{
							continue;	
						}
					}
					else if(  $ar['apci_show_random_operator'] == 0  )
					{
						$resTemp = getOnlineAdmin( true );
						if( !empty($resTemp) )
						{
							$resAdmin = $resTemp[0];
						}
						else
						{
							continue;	
						}
					}
					else
					{
						$resTemp = getOnlineAdmin( false );
						if( !empty($resTemp) )
						{
							$resAdmin = $resTemp[0];
						}
						else
						{
							continue;	
						}
					}
					
					if( isset($resAdmin) && !empty($resAdmin) )
					{
						if( isActive( 'A', $resAdmin['admin_user_id'], $resAdmin['sessions_id'], 10) )
						{
							$resArr['is_start'] = 1;
							$resArr['status'] = 0;	//status pro chat
							$resArr['is_pro'] = 1;
							$resArr['chat_his'] = '';
							$resArr['admin_user_id'] = $resAdmin['admin_user_id']; 
							$resArr['admin_sessions_id'] = $resAdmin['sessions_id'];
							$resArr['admin_session_id'] = $resAdmin['session_id'];
							$resArr['admin_user_firstname'] = $resAdmin['admin_user_firstname']; 
							$resArr['admin_profile_image'] = $resAdmin['admin_profile_image']; 
							$resArr['admin_user_phone_no'] = $resAdmin['admin_user_phone_no']; 
							$resArr['apci_message'] = str_replace( "\r\n", "<br>", $ar['apci_message']);
							
							if( !empty( $pageViews['customer_id'] ) )
							{
								$resArr['apci_message'] = " Hi ". $pageViews['customer_firstname']."<br>".$resArr['apci_message'] ;	
							}
							
							$resArr['apci_wait_message'] = $ar['apci_wait_message'];		 
							$resArr['apci_wait_timeout'] = $ar['apci_wait_timeout'];
							$resArr['apci_timeout_message'] = $ar['apci_timeout_message'];	
							$resArr['customer_id'] = $pageViews['customer_id'];	
							$resArr['customer_firstname'] = $pageViews['customer_firstname'];
							$resArr['sessions_id'] = $CI->session->userdata('sessions_id');
							
							$chat_id = startChat( $resArr['customer_id'], $CI->session->userdata('sessions_id'), $CI->session->userdata('curr_page'), 
												$ar['abstract_proactive_chat_invitation_id']);
							$CI->session->set_userdata( array( 'chat_id'=>$chat_id ) );
							chatMsg( $CI->session->userdata('chat_id'), $resArr['admin_user_id'], 'A', $resArr['apci_message']);	
							
							$resArr['chat_id'] = $chat_id;
							$resArr['c_created_date'] = formatDate();
							return $resArr;
						}
					}
				}
				else if( $ar['apci_time_on_site'] < $min )
				{
					$min = $ar['apci_time_on_site'];	
					$tempK = $k;
				}
			}
			
			$resTemp = getOnlineAdmin(); 
			if( !empty($resTemp) )
			{
				$resArr['is_start'] = 0;
				$resArr['min'] = ( $min + 1 - $sessTime ) * 1000;
				return $resArr;
			}
			else
			{
				return '';	
			}
		}
		else
		{
			return '';	
		}
	}

/**
 * @author Cloudwebs
 * @abstract function will check and if avaialable return online admin operators to start chat with
 */
	function getOnlineAdmin( $is_random=false, $limit = ' LIMIT 1 ', $admin_user_id=0, $is_validate_time=true )
	{
		$where = '';
		if( !empty($admin_user_id) )
		{
			$where = " AND au.admin_user_id<>".$admin_user_id." ";
		}
		
		$sql = " SELECT au.admin_user_id, au.admin_user_firstname, au.admin_profile_image, au.admin_user_phone_no, l.sessions_id, l.session_id  
				FROM admin_user au 
				INNER JOIN logins l 
				ON l.cust_admin_user_id=au.admin_user_id 
				WHERE au.admin_user_status=0 ".$where."
				AND au.admin_can_chat=1 
				AND l.l_user_type='A' 
				AND l.l_session_status=1 ";
		$order_by = '';
			
		if( $is_random )		
		{
			$order_by = " ORDER BY RAND()  ";
		}
		else
		{
			$order_by = " ORDER BY admin_chat_priority ";	
		}

		$res = executeQuery( $sql.$order_by.$limit );
		if( $is_validate_time )
		{
			if( !empty($res) )
			{
				$resArr = array();
				foreach($res as $k=>$ar)
				{
					if( isActive( 'A', $ar['admin_user_id'], $ar['sessions_id'], 480 ) )
					{
						$resArr[] = $ar;
					}
				}
				return $resArr;
			}
			else
			{
				return '';
			}
		}
		else
		{
			return $res;			
		}
	}

/**
 * @author Cloudwebs
 * @abstract function will return auto responder for current chat started by user
 */
	function getAutoResponder( $position_id )
	{
		$CI =& get_instance();
		return executeQuery(" SELECT aar.aar_wait_message, aar.aar_wait_timeout, aar.aar_timeout_message  
							  FROM ch_abstract_auto_responder aar 
							  WHERE aar.aar_status=0 AND ( position_id=0 OR position_id=".$position_id.")
							  ORDER BY aar_sort_order ");
	}

/**
 * @author Cloudwebs
 * @abstract function will check if current session is of admin
 */
	function isAdmin( $session_id=0 )
	{
		if( $session_id == 0 ) 
		{
			$session_id = session_id();	
		}
		
		$sql = " SELECT cust_admin_user_id FROM logins l 
				WHERE l.l_user_type='A' AND l.l_session_status=1 AND l.session_id='".$session_id."' ";
		$res = executeQuery( $sql );
		if( !empty( $res ) ) 
		{
			return true;
		}
		else
		{
			return false;	
		}
	}

/**
 * @author Cloudwebs
 * @abstract function will check if customer has requested for no pro chat
 */
	function isNoProChat()
	{
		$CI =& get_instance();
		$res  = exeQuery( "SELECT customer_interaction_id FROM customer_interaction 
						   WHERE ci_interaction_type='NO_P_CHAT' AND 
						   ( ( customer_id<>0 AND customer_id=".(int)$CI->session->userdata('customer_id')." ) OR 
						   sessions_id=".(int)$CI->session->userdata('sessions_id')." )  ", true, 'customer_interaction_id');	

		if( empty($res) )				   
		{
			return false;			
		}
		else
		{
			return true;
		}
	}
	
/**
 * @author Cloudwebs
 * @abstract function will initiate chat 
 */
	function startChat( $user_id, $sessions_id, $c_page_url, $abstract_proactive_chat_invitation_id=0, $c_status=0)
	{
		$CI =& get_instance();
		
		$CI->db->insert("ch_chat", array( 'user_id'=>$user_id, 'sessions_id'=>$sessions_id, 'c_page_url'=>$c_page_url, 
										'abstract_proactive_chat_invitation_id'=>$abstract_proactive_chat_invitation_id, 'c_status'=>$c_status));
		$chat_id = $CI->db->insert_id();			
		
		$chatArr = array();
		if( $CI->session->userdata('chatArr') !== FALSE )
		{
			$chatArr = $CI->session->userdata('chatArr');
		}
		
		$chatArr[$chat_id]['id'] = $chat_id;
		$CI->session->set_userdata( array ( 'chatArr'=>$chatArr, 'chat_id'=>$chat_id ) );
		return $chat_id;
	}
	
/**
 * @author Cloudwebs
 * @abstract function will send msg to admin about new chat
 */
	function sendChatSMSToAdmin( $user_id, $chat_id)
	{
		$resAdm = getOnlineAdmin( false, '', 0, true);
		if( empty($resAdm) )
		{
			$resAdm = executeQuery(" SELECT admin_user_id, admin_user_firstname, admin_user_phone_no FROM admin_user 
									WHERE admin_user_status=0 AND admin_can_chat=1 
									ORDER BY admin_chat_priority ");
			if( !empty($resAdm) )
			{
				foreach( $resAdm as $k=>$ar )
				{
					if( !empty($ar['admin_user_phone_no']) )
					{
						sendChatStartMsg( $ar['admin_user_id'], $ar['admin_user_firstname'], $ar['admin_user_phone_no'], $user_id, $chat_id);
					}
				}
			}
		}
	}

/**
 * @author Cloudwebs
 * @abstract function will store msg conversation for current chat
 */
	function chatMsg( $chat_id, $cust_admin_user_id, $cm_user_type, $cm_msg)
	{
		$CI =& get_instance();
		
		$CI->db->insert( "ch_chat_msg", array( 'chat_id'=>$chat_id, 'cust_admin_user_id'=>$cust_admin_user_id, 'cm_user_type'=>$cm_user_type, 'cm_msg'=>$cm_msg));
	}

/**
 * @author Cloudwebs
 * @abstract function will return detaileD chat history for the current user chat if chat is running for current session
 */
	function getChatHistory( $chat_id, $admin_user_id=0, $is_admin=false)
	{
		$CI =& get_instance();
		$resArr = array();

		$resArr['chat_his'] = executeQuery(" SELECT c.abstract_proactive_chat_invitation_id, c.c_status, c.c_created_date, cm.cust_admin_user_id, cm.cm_user_type, cm.cm_msg, cm.cm_created_date, 
											   coalesce(au.admin_user_firstname, '".CH_DEF_AGE."') as admin_user_firstname 
											   FROM ch_chat c 
											   INNER JOIN  ch_chat_msg cm
											   ON cm.chat_id=c.chat_id 
											   LEFT JOIN admin_user au 
											   ON ( cm.cm_user_type='A' AND au.admin_user_id=cm.cust_admin_user_id ) 
											   WHERE cm.chat_id=".$chat_id." 
											   ORDER BY chat_msg_id DESC ");
		
		if( empty($resArr['chat_his']) )					
		{
			return '';	
		}
							
		$resArr['onAdm'] = getOnlineAdmin( false, '');
		$resArr['chat_id'] = $chat_id;
		$resArr['abstract_proactive_chat_invitation_id'] = $resArr['chat_his'][0]['abstract_proactive_chat_invitation_id'];
		$resArr['c_status'] = $resArr['chat_his'][0]['c_status'];
		$resArr['c_created_date'] = formatDate( $resArr['chat_his'][0]['c_created_date'] );
		$resArr['admin_user_id'] = 0; 
		$resArr['customer_id'] = 0;
		foreach($resArr['chat_his'] as $k=>$ar)									   
		{
			if( $ar['cm_user_type'] == 'A' )	
			{
				$resArr['admin_user_id'] = $ar['cust_admin_user_id'];
				break;	
			}
		}
		
		if( $is_admin  && !empty($resArr['admin_user_id']) )
		{
			if( $resArr['admin_user_id'] != $admin_user_id )
			{
				$resArr['is_taken'] = true;
				return $resArr;	
			}
		}

		foreach($resArr['chat_his'] as $k=>$ar)									   
		{
			if( $ar['cm_user_type'] == 'U' )	
			{
				$resArr['customer_id'] = $ar['cust_admin_user_id'];
				break;	
			}
		}

		$resArr['is_start'] = 1;	//since this is chat history chat is assumed to be started
		$resArr['status'] = $resArr['c_status'];	
		$resArr['is_pro'] = ( $resArr['c_status'] == 0 ? 1 : 0 );	//specifies that chat is still in pro mode and not taken by admin
		$chatArr = $CI->session->userdata('chatArr');
		if( isset( $chatArr[ $resArr['chat_id'] ] ) )
		{
			$resArr['chat_conf'] = $chatArr[ $resArr['chat_id'] ];
		}
		else
		{
			$chatArr[ $resArr['chat_id'] ]['id'] = $resArr['chat_id'];
			$resArr['chat_conf'] = $chatArr[ $resArr['chat_id'] ];
			
			//set session chatArr
			$CI->session->set_userdata( array ( 'chatArr'=>$chatArr ) );
		}

		//admin info
		if( (int)$resArr['admin_user_id'] != 0 )
		{
			$resAdmin = $CI->db->query(" SELECT au.admin_user_id, au.admin_user_firstname, au.admin_profile_image, au.admin_user_phone_no, l.sessions_id, l.session_id 
										 FROM admin_user au 
										 INNER JOIN logins l 
										 ON ( l.cust_admin_user_id=au.admin_user_id AND l.l_user_type='A' AND l.l_session_status=1 ) 
										 WHERE au.admin_user_id=".$resArr['admin_user_id']." 
										 ORDER BY logins_id DESC LIMIT 1")->row_array();
			
			$resArr['admin_user_id'] = $resAdmin['admin_user_id']; 
			$resArr['admin_sessions_id'] = $resAdmin['sessions_id'];
			$resArr['admin_session_id'] = $resAdmin['session_id']; 
			$resArr['admin_user_firstname'] = $resAdmin['admin_user_firstname']; 
			$resArr['admin_profile_image'] = $resAdmin['admin_profile_image']; 
			$resArr['admin_user_phone_no'] = $resAdmin['admin_user_phone_no']; 
		}
		else
		{
			$resArr['admin_user_id'] = 0; 
			$resArr['admin_sessions_id'] = 0;
			$resArr['admin_session_id'] = 0;
			$resArr['admin_user_firstname'] = CH_DEF_AGE; 
			$resArr['admin_profile_image'] = ''; 
			$resArr['admin_user_phone_no'] = ''; 
		}

		//customer info
		if( (int)$resArr['customer_id'] != 0 )
		{
			$resCust = $CI->db->query(" SELECT customer_firstname FROM customer c WHERE c.customer_id=".$resArr['customer_id']." ")->row_array();
			
			$resArr['customer_firstname'] = @$resCust['customer_firstname'];
			//$resArr['sessions_id'] = $CI->session->userdata('sessions_id');
		}
		else
		{
			$resArr['customer_id'] = 0;	
			$resArr['customer_firstname'] = 'Guest';
			//$resArr['sessions_id'] = $CI->session->userdata('sessions_id');
		}
		return $resArr;
	}

/**
 * @author Cloudwebs
 * @abstract function will close chat whether it is closed by user or admin
 */
	function closeChats( $chat_id, $c_status=3 )
	{
		$CI =& get_instance();
		$CI->db->where("chat_id",  $chat_id)->update("ch_chat", array('c_status'=>$c_status, 'c_modified_date'=>'NOW()') );
	}

/**
 * @author Cloudwebs
 * @abstract function will return chat for admin operator: as per status parameter ask the type of chats
 */
	function getAdminChats( $admin_user_id=0, $c_status=2 )
	{
		$sql = '';
		if( $c_status == 0 ) //pro chat
		{
			$sql = "SELECT c.chat_id
							 FROM 
							 ( SELECT MAX(chat_msg_id) as chat_msg_id FROM ch_chat_msg WHERE cm_user_type='A' GROUP BY chat_id ) as max
							 INNER JOIN  ch_chat_msg cm
							 ON ( cm.chat_msg_id=max.chat_msg_id ) 
							 INNER JOIN  
							 ch_chat c 
							 ON ( c.c_status=".$c_status." AND c.chat_id=cm.chat_id )
							 WHERE cm.cust_admin_user_id=".$admin_user_id." 
							 ORDER BY chat_id  ";			
		}
		else if( $c_status == 1 ) //pending
		{
			$sql = " SELECT c.chat_id, c.sessions_id FROM ch_chat c WHERE c.c_status=".$c_status." ORDER BY chat_id ";			
		} 
		else if( $c_status == 2 ) //active
		{
			$sql = "SELECT c.chat_id
							 FROM 
							 ( SELECT MAX(chat_msg_id) as chat_msg_id FROM ch_chat_msg WHERE cm_user_type='A' GROUP BY chat_id ) as max
							 INNER JOIN  ch_chat_msg cm
							 ON ( cm.chat_msg_id=max.chat_msg_id ) 
							 INNER JOIN  
							 ch_chat c 
							 ON ( c.c_status=".$c_status." AND c.chat_id=cm.chat_id )
							 WHERE cm.cust_admin_user_id=".$admin_user_id." 
							 ORDER BY chat_id ";			
		}
		else if( $c_status == 4 ) //unread
		{
			$sql = " SELECT c.chat_id, c.sessions_id FROM ch_chat c WHERE c.c_status=".$c_status." ORDER BY chat_id ";			
		} 
		else if( $c_status == 5 ) //unread
		{
			$sql = " SELECT c.chat_id, c.sessions_id, c.user_id FROM ch_chat c WHERE c.c_status=".$c_status." ORDER BY chat_id ";			
		} 
		
		return executeQuery( $sql );
	}

/**
 * @author Cloudwebs
 * @abstract function will check if user/admin is active by taking in concern cust_admin_id AND session_id and validating against min minute require to claim itself as active
 */
	function isActive( $type, $cust_admin_id, $sessions_id, $validMinute )
	{
		$res = executeQuery( "SELECT pa_created_time FROM page_accesses WHERE sessions_id=".$sessions_id." ORDER BY page_accesses_id DESC LIMIT 1 " );
		if( !empty($res) )
		{
			$time = time();
			$pa_created_time = strtotime( $res[0]['pa_created_time'] );
			
			if( ( $pa_created_time + ( $validMinute * 60 ) ) > $time )
			{
				return true;	
			}
			else
			{
				return false;				
			}
		}
		else
		{
			return false;
		}
	}
	
/**
 * @author Cloudwebs
 * @abstract function will save email ID in email list and then register user as type specified mainly used for Chat registration 
 */
	function emailListAndReg( $customer_group_type, $customer_firstname, $customer_emailid, $customer_phoneno, $el_priority_level=31 )
	{
		$CI =& get_instance();
		
		$email_list_id = saveEmailList($customer_emailid, 1, 'N', 'CHAT_REG', $el_priority_level);
		
		$customer_group_id = exeQuery( " SELECT customer_group_id FROM customer_group WHERE customer_group_type='".$customer_group_type."' ", true, 'customer_group_id');
		
		return saveCustomer($customer_emailid, array( 'manufacturer_id'=> MANUFACTURER_ID, 'customer_firstname'=>$customer_firstname, 'customer_group_id'=>$customer_group_id, 
						'email_list_id'=>$email_list_id, 'customer_emailid'=>$customer_emailid, 'customer_phoneno'=>$customer_phoneno, 'customer_ip_address'=>$_SERVER['REMOTE_ADDR']));
	}
	
/**
 * @author Cloudwebs
 * @abstract function will count no of pages visited for sessions_id provided
 */
	function pageCount( $sessions_id )
	{
		if( $sessions_id == 0 )
		{
			return 0;			
		}
		else
		{
			return exeQuery( "SELECT COUNT(1) as 'Count' FROM page_accesses WHERE sessions_id=".$sessions_id." ", true, 'Count');			
		}
	}
	
/**
 * @author Cloudwebs
 * @abstract function is chat offline
 */
	function isChatOffline()
	{
		$resultArr = array();
		$resultArr['isChatOffline'] = false;
		$resultArr['chatConf'] = fetchKeyIdArr( " SELECT c_key, c_value FROM ch_config WHERE c_status=0 ", 'c_value', 'c_key');
		
		$workDayArr = explode( "|", $resultArr['chatConf']['WORK_DAYS']);
		$workHourArr = explode( "|", $resultArr['chatConf']['WORK_HOURS']);
		$currentHour = date('H.i');
		
		if( !in_array( date('N', time()),  $workDayArr) )
		{
			$resultArr['isChatOffline'] = true;
		}
		else if( $currentHour < $workHourArr[0] || $currentHour > $workHourArr[1] )
		{
			$resultArr['isChatOffline'] = true;
		}
		
		return $resultArr;
	}

	/**
	 * Function will return product and solitaire info 
	 * @param $cnt is the variable used in admin panel so that admin can able to add more then opone product for order 
	 */
	function prodSolInfo( $product_price_id, $is_ajax=false, $is_cart_or_checkout=false,  $is_status_check=true, $pageToken='', $ring_size_id='', $cz_suffix='', 
						$diamond_price_id='', $cnt=0)
	{
		$res = array();					
		$res = showProductsDetails( $product_price_id, $is_ajax, $is_cart_or_checkout, $is_status_check, $pageToken, $ring_size_id, $cz_suffix);
		if($res)
		{
			//when accessories couple
			if($res['view_var']['product_accessories'] == 'COU' || ( $res['view_var']['ring_size_region'] == 'Y' && $res['view_var']['product_accessories'] == 'SOL_COU' ) ) 
			{
			}
			
			//when accessories ring
			if($res['view_var']['product_accessories'] == 'RIN' || ( $res['view_var']['ring_size_region'] == 'Y' && $res['view_var']['product_accessories'] == 'SOL' ) ) 
			{
				$sizeArr = getDropDownAryRingSize( $res['metal_price_id'], $res['product_gender']);
				$res['view_var']['ring_size_drop_down'] = $res['ring_size_drop_down'] = form_dropdown('ring_size_id_'.$cnt, $sizeArr, $ring_size_id, 'style="width:100px;padding:4px; font-size:12px; float:none;" title="Select Ring Size" id="ring_size_id_'.$cnt.'" type-="ring_size" onchange="calcProdPrice($(\'#solitaire_code_'.$cnt.'\'), \''.$cnt.'\')" class="ring_size"');
			}
			
			if( !empty($diamond_price_id) )
			{
				$didArr = explode( "|", $diamond_price_id);
				$res['d_detail']['diamond_price_id'] = '';
				//fetch diamond detail for this mount						
				foreach( $didArr as $no=>$did)
				{
					$res['d_detail'][$did] = fetchDiamondDetail( $did );

					$res['product_discounted_price'] = $res['product_discounted_price'] + $res['d_detail'][$did]['dp_price'];
					$res['d_detail']['diamond_price_id'] .= $res['d_detail'][$did]['diamond_price_id'] ."|";
				}
				
				$res['view_var']['product_discounted_price'] = lp( $res['product_discounted_price'] );
				$res['d_detail']['diamond_price_id'] = substr( $res['d_detail']['diamond_price_id'], 0, -1);
			}
		}
		return $res;
	}
	
	/**
	 * function fetch main category
	 */

	function menuCategoryMobile( $front_menu_type_id=10 )
	{
		if( MANUFACTURER_ID == 7 )			
		{
			return executeQuery( "SELECT m.front_menu_id, m.front_menu_name, m.front_hook_alias, m.front_menu_primary_id, m.fm_icon, t.fm_icon_is_display, c.category_image, 
								c.category_banner, c.m_category_image  
								FROM front_menu_type t INNER JOIN front_menu m ON m.front_menu_type_id=t.front_menu_type_id 
								INNER JOIN product_categories c ON m.front_menu_primary_id=c.category_id 
								WHERE t.front_menu_type_id=".$front_menu_type_id." 
								AND m.fm_parent_id=0 
								AND m.fm_status=0 
								ORDER BY front_menu_primary_id" );
		}
		else
		{
			return executeQuery( "SELECT m.front_menu_id, m.front_menu_name, m.front_hook_alias, m.front_menu_primary_id, m.fm_icon, t.fm_icon_is_display, c.category_image, 
								c.category_banner, c.m_category_image 
								FROM front_menu_type t 
								INNER JOIN front_menu m ON m.front_menu_type_id=t.front_menu_type_id 
								INNER JOIN front_menu_cctld mc ON ( mc.manufacturer_id = ".MANUFACTURER_ID." AND mc.front_menu_id=m.front_menu_id ) 
								INNER JOIN product_categories_cctld c ON m.front_menu_primary_id=c.category_id 
								WHERE t.front_menu_type_id=".$front_menu_type_id." 
								AND m.fm_parent_id=0 
								AND mc.fm_status=0 
								ORDER BY front_menu_primary_id" );
		}
	}

	/**
	 * function fetch main category
	 */
	function menuCategoryDesk( $front_menu_type_id )
	{
		$CI =& get_instance(); 
		
		if( is_restClient() )
		{
			$is_display = "is_display = 2";
		}
		else
		{
			$is_display = "is_display = 1";
		}
		
		if( MANUFACTURER_ID == 7 )			
		{
			
			return executeQuery( "SELECT m.front_menu_id,m.front_menu_name,m.front_hook_alias,m.front_menu_primary_id,m.fm_icon,m.is_display,m.fm_static_url_restapp,
								t.fm_icon_is_display 
								FROM front_menu_type t 
								INNER JOIN front_menu m 
								ON m.front_menu_type_id=t.front_menu_type_id  
								WHERE t.front_menu_type_id=".$front_menu_type_id." 
								AND m.fm_parent_id=0 
								AND m.fm_status=0
								AND (m.".$is_display." or m.is_display = 0)
								ORDER BY fm_sort_order" );
		}
		else
		{
			return executeQuery( "SELECT mc.front_menu_id,mc.front_menu_name,m.front_hook_alias,m.front_menu_primary_id,mc.fm_icon,t.fm_icon_is_display,m.fm_static_url_restapp
								FROM front_menu_type t 
								INNER JOIN front_menu m 
								ON m.front_menu_type_id=t.front_menu_type_id 
								INNER JOIN front_menu_cctld mc 
								ON ( mc.manufacturer_id = ".MANUFACTURER_ID." 
								AND mc.front_menu_id=m.front_menu_id ) 
								WHERE t.front_menu_type_id=".$front_menu_type_id." 
								AND m.fm_parent_id=0 
								AND mc.fm_status=0 
								AND (m.".$is_display." or m.is_display = 0)
								ORDER BY mc.fm_sort_order" );
		}
	}

	/**
	 */
	function detailDiamondType( $product_stone_number )
	{
		if( $product_stone_number == 0 )
		{
			return "cs";
		}
		else if( $product_stone_number <= 2 )
		{
			return "ss".$product_stone_number;
		}
		else
		{
			return "dyn";
		}
	}
	
	/**
	 * function serves maintainability for detail page diamond choices to be used for both desk and mobile views
	 * @param $attributeType like C=> Component, A=> attribute
	 */
	function detailDiamonds( $product_id, &$type, $product_stone_number=0, $attributeType="" ) 
	{
		
		if( $attributeType == "C" )
		{
			if( $type == 'cs' )
			{
				return executeQuery( "SELECT diamond_price_id,diamond_price_name,diamond_price_key,dp_icon,dt.diamond_type_id,diamond_type_name,
									  		 diamond_type_key
									FROM product_center_stone pcs
									INNER JOIN diamond_price dp ON dp.diamond_price_id=pcs.category_id
									INNER JOIN diamond_type dt ON dt.diamond_type_id=dp.diamond_type_id
									WHERE pcs.product_id=".$product_id." AND product_center_stone_status=0
									ORDER BY dt.diamond_type_id,dp_sort_order" );
			}
			else if( $type == 'ss1' )
			{
				return executeQuery( "SELECT diamond_price_id,diamond_price_name,diamond_price_key,dp_icon,dt.diamond_type_id,diamond_type_name,
											 diamond_type_key
									FROM product_side_stone1 ss1
									INNER JOIN diamond_price dp ON dp.diamond_price_id=ss1.category_id
									INNER JOIN diamond_type dt ON dt.diamond_type_id=dp.diamond_type_id
									WHERE ss1.product_id=".$product_id." AND product_side_stone1_status=0
									ORDER BY dt.diamond_type_id,dp_sort_order" );
			}
			else if( $type == 'ss2' )
			{
				return executeQuery( "SELECT diamond_price_id,diamond_price_name,diamond_price_key,dp_icon,dt.diamond_type_id,diamond_type_name,
											 diamond_type_key
									FROM product_side_stone2 ss2
									INNER JOIN diamond_price dp ON dp.diamond_price_id=ss2.category_id
									INNER JOIN diamond_type dt ON dt.diamond_type_id=dp.diamond_type_id
									WHERE ss2.product_id=".$product_id." AND product_side_stone2_status=0
									ORDER BY dt.diamond_type_id,dp_sort_order" );
			}
			else if( $type == 'dyn' )	//dynamic stones
			{
				$type = "ss".$product_stone_number; 
				return executeQuery( "SELECT diamond_price_id,diamond_price_name,diamond_price_key,dp_icon,dt.diamond_type_id,diamond_type_name,
											 diamond_type_key
									FROM product_side_stones ss".$product_stone_number."
									INNER JOIN diamond_price dp ON ( dp.diamond_price_id=ss".$product_stone_number.".category_id AND 
																	 ss".$product_stone_number.".product_stone_number=".$product_stone_number." )
									INNER JOIN diamond_type dt ON dt.diamond_type_id=dp.diamond_type_id
									WHERE ss".$product_stone_number.".product_id=".$product_id." AND 
										  ss".$product_stone_number.".product_stone_number=".$product_stone_number." AND 
										  product_side_stones_status=0
									ORDER BY dt.diamond_type_id, dp_sort_order " );
			}
		}
		else 
		{
			if( $type == 'cs' )
			{
				if( MANUFACTURER_ID == 7 )
				{
					return executeQuery( "SELECT pcs.pcs_diamond_shape_id, pa.pa_value, pa.pa_icon
									FROM product_center_stone pcs
									INNER JOIN product_attribute pa ON pa.product_attribute_id=pcs.pcs_diamond_shape_id
									WHERE pcs.product_id=".$product_id." AND product_center_stone_status=0
									ORDER BY pa.pa_sort_order " );
				}
				else 
				{
					return executeQuery( "SELECT pcs.pcs_diamond_shape_id, pac.pa_value, pac.pa_icon
									FROM product_center_stone pcs
									INNER JOIN product_attribute pa ON pa.product_attribute_id=pcs.pcs_diamond_shape_id
									INNER JOIN product_attribute_cctld pac 
									ON ( pac.product_attribute_id=pa.product_attribute_id AND manufacturer_id=".MANUFACTURER_ID." ) 
									WHERE pcs.product_id=".$product_id." AND product_center_stone_status=0
									ORDER BY pa.pa_sort_order " );
				}
			}
			else if( $type == 'ss1' || $type == 'ss2' )
			{
				if( MANUFACTURER_ID == 7 )
				{
					return executeQuery( "SELECT p".$type.".p".$type."_diamond_shape_id, pa.pa_value, pa.pa_icon
									FROM product_side_stone".$product_stone_number." p".$type."
									INNER JOIN product_attribute pa ON pa.product_attribute_id=p".$type.".p".$type."_diamond_shape_id
									WHERE p".$type.".product_id=".$product_id." AND product_side_stone".$product_stone_number."_status=0
									ORDER BY pa.pa_sort_order " );
				}
				else 
				{
					return executeQuery( "SELECT p".$type.".p".$type."_diamond_shape_id, pac.pa_value, pac.pa_icon
									FROM product_side_stone".$product_stone_number." p".$type."
									INNER JOIN product_attribute pa ON pa.product_attribute_id=p".$type.".p".$type."_diamond_shape_id
									INNER JOIN product_attribute_cctld pac 
									ON ( pac.product_attribute_id=pa.product_attribute_id AND manufacturer_id=".MANUFACTURER_ID." ) 
									WHERE p".$type.".product_id=".$product_id." AND product_side_stone".$product_stone_number."_status=0
									ORDER BY pa.pa_sort_order " );
				}
			}
			else if( $type == 'dyn' )	//dynamic stones
			{
				$type = "ss".$product_stone_number;
				if( MANUFACTURER_ID == 7 )
				{
					return executeQuery( "SELECT p".$type.".psss_diamond_shape_id as p".$type."_diamond_shape_id, pa.pa_value, pa.pa_icon
									FROM product_side_stones p".$type."
									INNER JOIN product_attribute pa ON pa.product_attribute_id=p".$type.".psss_diamond_shape_id
									WHERE p".$type.".product_id=".$product_id." AND 
										  p".$type.".product_stone_number=".$product_stone_number." AND 
										  product_side_stones_status=0 
									ORDER BY pa.pa_sort_order " );
				}
				else 
				{
					return executeQuery( "SELECT p".$type.".psss_diamond_shape_id as p".$type."_diamond_shape_id, pac.pa_value, pac.pa_icon
									FROM product_side_stones p".$type."
									INNER JOIN product_attribute pa ON pa.product_attribute_id=p".$type.".psss_diamond_shape_id
									INNER JOIN product_attribute_cctld pac 
									ON ( pac.product_attribute_id=pa.product_attribute_id AND manufacturer_id=".MANUFACTURER_ID." ) 
									WHERE p".$type.".product_id=".$product_id." AND 
										  p".$type.".product_stone_number=".$product_stone_number." AND 
										  product_side_stones_status=0 
									ORDER BY pa.pa_sort_order " );
				}
			}
		}
	}

	/**
	 * function serves maintainability for detail page metal choices to be used for both desk and mobile views
	 */
	function detailMetals( $product_id )
	{
		return executeQuery( "SELECT metal_price_id,mp.mp_desc,mc.metal_color_id,mpp.metal_purity_id,metal_purity_name,metal_color_key,mp_icon,mp_desc 
				FROM product_metal pm 
				INNER JOIN metal_price mp ON mp.metal_price_id=pm.category_id
				INNER JOIN metal_purity mpp ON mpp.metal_purity_id=mp.metal_purity_id
				INNER JOIN metal_color mc ON mc.metal_color_id=mp.metal_color_id
				WHERE pm.product_id=".$product_id." AND product_metal_status=0
				ORDER BY mc.metal_color_id,mpp.metal_purity_sort_order,mp_sort_order" );
	}

/**
 * @author Cloudwebs
 * @abstract Function will return priority product price ID for product
 */	
	function getPriorityPrPrID( $product_alias='', $product_id=0 )
	{
		$sql=$where = '';	$product_price_id = 0; 			
		if( MANUFACTURER_ID == 7 )
		{
			$sql = " SELECT pp.product_price_id 
					 FROM product_price pp 
					 INNER JOIN product p 
					 ON (p.product_id=pp.product_id AND p.product_metal_priority_id=pp.metal_price_id AND p.product_cs_priority_id=pp.cs_diamond_price_id AND 
	 				 p.product_ss1_priority_id=pp.ss1_diamond_price_id AND p.product_ss2_priority_id=pp.ss2_diamond_price_id) ";

			if( !empty($product_alias) )				
			{
				$where = " WHERE p.product_alias='".$product_alias."' AND p.product_status=0 AND pp.product_price_status=0 ";	
			}
			else if( !empty($product_id) )
			{
				$where = " WHERE p.product_id=".$product_id." AND p.product_status=0 AND pp.product_price_status=0 ";	
			}
			
			$product_price_id = exeQuery( $sql . $where . " LIMIT 1 ", true, "product_price_id" );
			
			
			//condition added on 24-04-2015 to exclude priority if not found
			if( empty( $product_price_id ) )
			{
				$sql = " SELECT pp.product_price_id
						 FROM product_price pp
						 INNER JOIN product p
						 ON (p.product_id=pp.product_id) ";
				
				$product_price_id = exeQuery( $sql . $where . " LIMIT 1 ", true, "product_price_id" );
			}
		}
		else 
		{
			$sql = " SELECT pp.product_price_id 
					 FROM product_price pp 
					 INNER JOIN product p 
					 ON p.product_id=pp.product_id 
					 INNER JOIN product_price_cctld ppc ON ( ppc.manufacturer_id=".MANUFACTURER_ID." AND ppc.product_price_id=pp.product_price_id ) 
					 INNER JOIN product_cctld prc ON ( prc.manufacturer_id=".MANUFACTURER_ID." AND prc.product_id=p.product_id 
					 AND prc.product_metal_priority_id=pp.metal_price_id AND prc.product_cs_priority_id=pp.cs_diamond_price_id 
					 AND prc.product_ss1_priority_id=pp.ss1_diamond_price_id AND prc.product_ss2_priority_id=pp.ss2_diamond_price_id ) ";
					 
			if( !empty($product_alias) )				
			{
				$where = " WHERE p.product_alias='".$product_alias."' AND prc.product_status=0 AND ppc.product_price_status=0 ";	
			}
			else if( !empty($product_id) )	
			{
				$where = " WHERE p.product_id=".$product_id." AND prc.product_status=0 AND ppc.product_price_status=0 ";	
			}
			
			$product_price_id = exeQuery( $sql . $where . " LIMIT 1 ", true, "product_price_id" );
			
			
			//condition added on 24-04-2015 to exclude priority if not found
			if( empty( $product_price_id ) )
			{
				$sql = " SELECT pp.product_price_id 
						 FROM product_price pp 
						 INNER JOIN product p 
						 ON p.product_id=pp.product_id 
						 INNER JOIN product_price_cctld ppc ON ( ppc.manufacturer_id=".MANUFACTURER_ID." AND ppc.product_price_id=pp.product_price_id ) 
						 INNER JOIN product_cctld prc ON ( prc.manufacturer_id=".MANUFACTURER_ID." AND prc.product_id=p.product_id ) ";
							
				$product_price_id = exeQuery( $sql . $where . " LIMIT 1 ", true, "product_price_id" );
			}
		}
						
		return $product_price_id; 
	}


	/**
	 * function serves maintainability for detail page related products to be used for both desk and mobile views
	 */
	function relatedProducts( $product_id, $category_id, $product_discounted_price, $range=1000 )
	{
		$sql='';				
		if( MANUFACTURER_ID == 7 )
		{
			$sql = "SELECT pp.product_price_id 
					FROM product p 
					INNER JOIN product_category_map pcm 
					ON pcm.product_id=p.product_id 
					INNER JOIN product_price pp 
					ON (p.product_id=pp.product_id AND p.product_metal_priority_id=pp.metal_price_id AND p.product_cs_priority_id=pp.cs_diamond_price_id AND 
					p.product_ss1_priority_id=pp.ss1_diamond_price_id AND p.product_ss2_priority_id=pp.ss2_diamond_price_id) 
					WHERE p.product_id<>".$product_id." AND pcm.category_id=".(int)$category_id." 
					AND p.product_status=0 AND pp.product_price_status=0 AND pp.product_discounted_price>=".($product_discounted_price - $range)." 
					AND pp.product_discounted_price<=".($product_discounted_price + $range)." 
					GROUP BY p.product_id 
					ORDER BY pp.product_discounted_price ";
		}
		else
		{
			$sql = " SELECT pp.product_price_id 
					 FROM product p 
					 INNER JOIN product_category_map pcm 
					 ON pcm.product_id=p.product_id 
					 INNER JOIN product_price pp 
					 ON (pp.product_id=p.product_id ) 
					 INNER JOIN product_price_cctld ppc ON ( ppc.manufacturer_id=".MANUFACTURER_ID." AND ppc.product_price_id=pp.product_price_id ) 
					 INNER JOIN product_cctld prc ON ( prc.manufacturer_id=".MANUFACTURER_ID." AND prc.product_id=p.product_id 
					 AND prc.product_metal_priority_id=pp.metal_price_id AND prc.product_cs_priority_id=pp.cs_diamond_price_id 
					 AND prc.product_ss1_priority_id=pp.ss1_diamond_price_id AND prc.product_ss2_priority_id=pp.ss2_diamond_price_id )
					 WHERE p.product_id<>".$product_id." AND pcm.category_id=".(int)$category_id." 
					 AND prc.product_status=0 AND ppc.product_price_status=0 AND 
					 ppc.product_discounted_price>=".($product_discounted_price - $range)." AND ppc.product_discounted_price<=".($product_discounted_price + $range)." 
					 GROUP BY p.product_id 
					 ORDER BY ppc.product_discounted_price ";
		}
		
		$res = executeQuery( $sql );
						
		if( !empty($res) )				
		{
			foreach( $res as $k=>$ar ) 			
			{
				$res[$k] = showProductsDetails( $ar['product_price_id'], false, false, true, '', '', '', 1);
			}
		}
		else
		{
			if( $range < 3000 )
			{
				return relatedProducts( $product_id, $category_id, $product_discounted_price, ($range+1000) );	
			}
		}
		
		return $res;
	}
	
	/**
	 * function will inject dir name in a product image path to get another image version like thumbnail etc.
	 */
	function injectDirInImagePath( $path, $dir, $after )
	{
		$pos = strpos($path, $after);
		return substr_replace($path, $dir, $pos+strlen($after), 0);
	}

	/**
	 * function return shipping address available for user for checkout process
	 */
	function getShippAddress( $customer_id )
	{
		return executeQuery("SELECT c.customer_address_id, c.customer_address_firstname, c.customer_address_lastname, c.customer_address_address, 
						c.customer_address_phone_no, c.customer_address_zipcode,
						p.pincode,p.state_id,p.cityname,p.areaname,s.country_id,s.state_name,cn.country_name   
						FROM customer_address c 
						INNER JOIN pincode p ON p.pincode_id=c.customer_address_zipcode 
						INNER JOIN state s ON s.state_id=p.state_id
						INNER JOIN country cn ON cn.country_id=s.country_id
						WHERE customer_id=".$customer_id." ");
	}

	/**
	 * function will return dia filter price and weight min and max 
	 */
	function diaMinMax()
	{
		$resPrice = executeQuery("SELECT coalesce( MIN(`dp_final_price`), 0) as Min, coalesce( Max(`dp_final_price`), 0) as Max 
									FROM `diamond_price` WHERE `dp_status`=0 AND `dp_final_price`> 0 AND `dp_rapnet_lot_no`<>0");
	
		$resDiamond = executeQuery( "SELECT coalesce( MIN(`dp_weight`), 0) as Min, coalesce(Max(`dp_weight`), 0) as Max 
										FROM `diamond_price` WHERE `dp_status`=0 AND `dp_weight`> 0 AND `dp_rapnet_lot_no`<>0" );
										
		$min = lp_base(@$resPrice[0]['Min']);	$max = lp_base(@$resPrice[0]['Max']);
		
		
		$minArr[0] = CURRENCY_SYMBOL;
		$minArr[1] = $min;	
		
		$maxArr[0] = CURRENCY_SYMBOL;
		$maxArr[1] = $max;	

		//echo 'test';pr($minArr);die;
		return array( 'resPrice'=>$resPrice, 'resDiamond'=>$resDiamond, 'min'=>$min, 'max'=>$max, 'minArr'=>$minArr, 'maxArr'=>$maxArr );
	}
	
	/**
	 * function will return all department/store/ccTLD that perrian working with
	 */
	function getManufacturers()
	{
		return executeQuery( " SELECT manufacturer_id FROM manufacturer " );
	}
	
/*
+++++++++++++++++++++++++++++++++++++++++
This function fetch images from folders
para1 :- product_id for fetch image folder path
+++++++++++++++++++++++++++++++++++++++++
*/
	function FetchImageFromFolder($product_id,$is_random=false)
	{
		 $res = executeQuery("select  product_image  from product where product_id='".$product_id."' ");
		 if(empty($res))
		 {
			return '';	 
		 }
		 $dir = $res[0]['product_image'];
		 if(!empty($dir))
		 {
			  if (is_dir($dir)) 
			  {
				if ($dh = opendir($dir)) 
				{
					$image = array();       			 		
					$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),RecursiveIteratorIterator::SELF_FIRST);
					foreach($iterator as $entry=>$file)
					{
						$filepath = str_replace("\\","/",$entry);
						if(!$file->isDir() && substr($filepath,-3)!=".db")
						{
							if($is_random)
							{
								$image[] = $filepath;
							}
							else
							{
								return $filepath;
							}
						}
					}
					$rand = 0;
					if($is_random)
					{
						$rand = rand(0,sizeof($image)-1);	 
					}
					return $image[$rand];
				}
			 }
			 
		 }
		
	}

/**
 * @abstract function will return images located at specified folder but not from inner dir if exist
 * @author	Cloudwebs
 * @param folderpath specifies folder from which all images will be fetched
 * @return array of images path
*/
	function fetchProductImages($folderpath)
	{
		if(!empty($folderpath))
		{		 
			if (is_dir($folderpath)) 
			{
				if ($dh = opendir($folderpath)) 
				{
					$images = array();
					while (($file = readdir($dh)) !== false)
					{
						if (!is_dir($folderpath.'/'.$file) && substr($file,-3)!=".db") 
						{
							$images[] = $folderpath.'/'.$file;
						}
					}
					closedir($dh);
					sort($images);
					return $images;
				}
			}
		}
		return false;
	}

/*
 *  function will generate sql query to display filter selections. 
 *  
 *  BUG 421: filter display generateFilterDisQuery function needs to be cctldiesed if client require within it cctld content, 
 *  but URL should never support cctld content.  
 */
	function generateFilterDisQuery($filters_table_name,$filters_table_field_name,$filters_table_id,$extra_field='',$order_by='')
	{
		$where ="";
		$name_field = $filters_table_name."_name";
		if($filters_table_name == "product_categories")
		{
			$name_field = "category_name ";
		}
		else if(strpos($filters_table_name,"diamond_price") !== FALSE)
		{
			$filters_table_name = "diamond_price";
			$name_field = "diamond_price_name";
		}
		else if(strpos($filters_table_name,"product_attribute") !== FALSE)
		{
			$filters_table_name = "product_attribute";
			$name_field = "pa_value";
		}
		
		
		if($filters_table_id != "")
		{
			$filters_table_id = str_replace("|",",",$filters_table_id);
			$where = " WHERE ".$filters_table_field_name." IN(".$filters_table_id.")";	
		}
		
		return "SELECT ".$filters_table_field_name.",".$name_field.$extra_field." FROM ".$filters_table_name.$where.$order_by;
	}
	
/*
 * @abstract this function regenerate product code from parse array
 * $author Horen Donda
 * @param $codeArr product code array
 * @return product generate code as per format
*/
	function genProdcodeFromArr($codeArr)
	{
// 		if(isset($codeArr[3]))
// 			$codeArr[3] = 'C'.$codeArr[3]; 	
	
// 		if(isset($codeArr[4]))
// 			$codeArr[4] = 'S'.$codeArr[4]; 	
	
// 		if(isset($codeArr[5]))
// 		{
// 			$codeArr[5] = 'S'.$codeArr[5]; 	
			
// 			$size = sizeof( $codeArr );
// 			if( $size > 6 )
// 			{
// 				for( $i=6; $i<$size; $i++ )
// 				{
// 					if( isset( $codeArr[$i] ) )
// 					{
// 						$codeArr[$i] = 'S'.$codeArr[$i];	
// 					}
// 				}
// 			}
// 		}
	
		return implode( "-", $codeArr );
	}
	
/*
+--------------------------------------------------+
	@author Cloudwebs
	Function generate and return seo freindly search code based on $search_tags array of search tags 
+--------------------------------------------------+
*/	
	function generateSearchCode($search_tagArr,$index='',$value='')
	{
		$search_tag = '';	
		//only append Products tag if no Products category is there
		if(!empty($search_tagArr['product_categories_tag']))
		{
			$search_tagArr['product_categories_tag'] = parseProductCatTag( $search_tagArr['product_categories_tag'] );
			
			$search_tag = rtrim($search_tagArr['metal_purity_tag'].$search_tagArr['metal_tag'].$search_tagArr['metal_color_tag'].$search_tagArr['metal_type_tag'].$search_tagArr['diamond_purity_tag'].$search_tagArr['diamond_color_tag'].$search_tagArr['diamond_shape_tag'].$search_tagArr['diamond_type_tag'].$search_tagArr['cz_tag'].$search_tagArr['diamond_price_tag'].$search_tagArr['sort_by_tag'].$search_tagArr['product_offer_tag'].$search_tagArr['product_categories_tag'].$search_tagArr['gender_filter_tag'].$search_tagArr['price_tag'].$search_tagArr['keyword_search_tag'].$search_tagArr['product_attribute_tag'],"+");
		}
		else if( stripos( " ".$search_tag,  'Products' ) === FALSE )
		{
			if( empty($search_tagArr['keyword_search_tag']) )
			{
				$search_tag = rtrim($search_tagArr['metal_purity_tag'].$search_tagArr['metal_tag'].$search_tagArr['metal_color_tag'].$search_tagArr['metal_type_tag'].$search_tagArr['diamond_purity_tag'].$search_tagArr['diamond_color_tag'].$search_tagArr['diamond_shape_tag'].$search_tagArr['diamond_type_tag'].$search_tagArr['cz_tag'].$search_tagArr['diamond_price_tag'].$search_tagArr['sort_by_tag'].$search_tagArr['product_offer_tag']." Products ".$search_tagArr['gender_filter_tag'].$search_tagArr['price_tag'].$search_tagArr['keyword_search_tag'].$search_tagArr['product_attribute_tag'],"+");
			}
			else if( stripos( $search_tagArr['keyword_search_tag'],  'Rings' ) !== FALSE || stripos( $search_tagArr['keyword_search_tag'],  'Earrings' ) !== FALSE || 
					 stripos( $search_tagArr['keyword_search_tag'],  'Pendants' ) !== FALSE || stripos( $search_tagArr['keyword_search_tag'],  'Tanmanya' ) !== FALSE || 
					 stripos( $search_tagArr['keyword_search_tag'],  'Bangles & Bracelets' ) !== FALSE || stripos( $search_tagArr['keyword_search_tag'],  'Mangalsutra' ) !== FALSE || 
					 stripos( $search_tagArr['keyword_search_tag'],  'Bangles' ) !== FALSE || stripos( $search_tagArr['keyword_search_tag'],  'Bracelets' ) !== FALSE ||
					 stripos( $search_tagArr['keyword_search_tag'],  'Band' ) !== FALSE || stripos( $search_tagArr['keyword_search_tag'],  'Ring' ) !== FALSE 
					 || stripos( $search_tagArr['keyword_search_tag'],  'Earring' ) !== FALSE || 
					 stripos( $search_tagArr['keyword_search_tag'],  'Pendant' ) !== FALSE || stripos( $search_tagArr['keyword_search_tag'],  'Tanmanya' ) !== FALSE || 
					 stripos( $search_tagArr['keyword_search_tag'],  'Bangle & Bracelet' ) !== FALSE || stripos( $search_tagArr['keyword_search_tag'],  'Mangalsutras' ) !== FALSE || 
					 stripos( $search_tagArr['keyword_search_tag'],  'Bangle' ) !== FALSE || stripos( $search_tagArr['keyword_search_tag'],  'Bracelet' ) !== FALSE ||
					 stripos( $search_tagArr['keyword_search_tag'],  'Bands' ) !== FALSE || stripos( $search_tagArr['keyword_search_tag'],  'jewellery' ) !== FALSE )
			{
				$search_tag = rtrim($search_tagArr['metal_purity_tag'].$search_tagArr['metal_tag'].$search_tagArr['metal_color_tag'].$search_tagArr['metal_type_tag'].$search_tagArr['diamond_purity_tag'].$search_tagArr['diamond_color_tag'].$search_tagArr['diamond_shape_tag'].$search_tagArr['diamond_type_tag'].$search_tagArr['cz_tag'].$search_tagArr['diamond_price_tag'].$search_tagArr['sort_by_tag'].$search_tagArr['product_offer_tag'].$search_tagArr['gender_filter_tag'].$search_tagArr['price_tag'].$search_tagArr['keyword_search_tag'].$search_tagArr['product_attribute_tag'],"+");
			}
			else
			{
				$search_tag = rtrim($search_tagArr['metal_purity_tag'].$search_tagArr['metal_tag'].$search_tagArr['metal_color_tag'].$search_tagArr['metal_type_tag'].$search_tagArr['diamond_purity_tag'].$search_tagArr['diamond_color_tag'].$search_tagArr['diamond_shape_tag'].$search_tagArr['diamond_type_tag'].$search_tagArr['cz_tag'].$search_tagArr['diamond_price_tag'].$search_tagArr['sort_by_tag'].$search_tagArr['product_offer_tag'].$search_tagArr['gender_filter_tag'].$search_tagArr['price_tag'].$search_tagArr['keyword_search_tag'].$search_tagArr['product_attribute_tag'],"+");	//." Products" append at last removed on 15-04-2015
			}
		}
			
		return htmlspecialchars_decode( str_replace( array(0=>'@',1=>'£',2=>'^'),'', $search_tag) );
	}

/*
+--------------------------------------------------+
	@author Cloudwebs
	Function generate and return seo freindly search code based on $search_tags array of search tags 
+--------------------------------------------------+
*/	
	function generateSeoUrl($search_url_tagArr,$index='',$value='')
	{
		if($index!='')		//this condition is for filter
			$search_url_tagArr[$index] = $value;
			
		return htmlspecialchars_decode( str_replace( "++", "+", str_replace( array(0=>'@',1=>'£',2=>'^'),'', trim($search_url_tagArr['metal_purity_url_tag'].$search_url_tagArr['metal_url_tag'].$search_url_tagArr['metal_color_url_tag'].$search_url_tagArr['metal_type_url_tag'].$search_url_tagArr['diamond_purity_url_tag'].$search_url_tagArr['diamond_color_url_tag'].$search_url_tagArr['diamond_shape_url_tag'].$search_url_tagArr['diamond_type_url_tag'].$search_url_tagArr['cz_url_tag'].$search_url_tagArr['diamond_price_url_tag'].$search_url_tagArr['product_offer_url_tag'].$search_url_tagArr['product_categories_url_tag'].$search_url_tagArr['gender_filter_url_tag'].$search_url_tagArr['price_url_tag'].$search_url_tagArr['product_attribute_url_tag'],"+" ) ) ) );
	}

/**
 * @author Cloudwebs
 * @abstract Function will return diamond detail page link as per diamond_price_id provided
 */	
	function diamondUrl( $diamond_price_id )
	{
		return site_url('solitaires/diamondDetail?did='.$diamond_price_id);
	}

/**
 * @author Cloudwebs
 * @abstract Function will return solitaire detail page url as per product price id
 */	
	function solitaireUrl( $product_price_id )
	{
		return site_url('solitaires/pickDesign?pid='.$product_price_id);
	}

/**
 * @author Cloudwebs
 * @abstract Function will return return next solitaire event applicable like: e1, e2, e9.... etc.
 */	
	function newSolitaireEvent()
	{
		$CI =& get_instance();
		$eventno = (int)$CI->session->userdata('maxEvent') + 1;
		$CI->session->set_userdata( array('maxEvent'=>$eventno) );
		
		return  "e".$eventno."_";
	}

/**
 * @author Cloudwebs
 * @abstract Function will return url by following which particular mount is selected for particular event
 */	
	function selectStyleUrl( $product_price_id, $currentEvent )
	{
		return site_url('solitaires/solitairesDiamond?evsst='.$product_price_id.'&ev='.$currentEvent);
	}

/**
 * @author Cloudwebs
 * @abstract Function will return url by following which particular diamond is added to particular mount of particular event
 */	
	function addDiamondToDesignUrl( $diamond_price_id, $currentEvent )
	{
		return site_url('solitaires/pickDesign?adddid='.$diamond_price_id.'&ev='.$currentEvent);
	}
	
/** 
 * @author Cloudwebs
 * @abstract function will fetch diamond detail sel query part
 */
	function diaDetailSelJoinPart()
	{
		if( MANUFACTURER_ID == 7 ) 
		{
			$res['select'] = "SELECT SQL_CALC_FOUND_ROWS d.diamond_price_id, d.dp_rapnet_lot_no, d.dp_weight, d.dp_pair_stock, d.dp_measurements, d.dp_calculated_cost, d.dp_discount, d.dp_final_price, d.dp_final_price as dp_price, CONCAT(d.dp_depth, '%') as dp_depth, CONCAT(d.dp_girdle, '%') as dp_girdle, CONCAT(d.dp_table, '%') as dp_table, d.dp_member_comments, 
						ds.diamond_shape_id, ds.diamond_shape_name, ds.diamond_shape_icon, dc.diamond_color_name, dc.diamond_color_key, dp.diamond_purity_name, dp.diamond_purity_key, COALESCE(rpc.c_name, '-') as c_name, rpp.p_name, rps.s_name, rpf.fc_name, COALESCE(rpl.cl_name, '-') as cl_name, rpl.cl_key, rpcr.c_certificate_url, rpcr.c_certificate_no ";
						
			$res['join'] = "FROM diamond_price d LEFT JOIN diamond_shape ds 
					 ON ds.diamond_shape_id=d.diamond_shape_id 
					 LEFT JOIN diamond_color dc 
					 ON dc.diamond_color_id=d.diamond_color_id 
					 LEFT JOIN diamond_purity dp 
					 ON dp.diamond_purity_id=d.diamond_purity_id 
					 LEFT JOIN rp_cut rpc 
					 ON rpc.cut_id=d.cut_id 
					 LEFT JOIN rp_polish rpp 
					 ON rpp.polish_id=d.polish_id 
					 LEFT JOIN rp_symmetry rps 
					 ON rps.symmetry_id=d.symmetry_id 
					 LEFT JOIN rp_fluorescence_color rpf 
					 ON rpf.fluorescence_color_id=d.fluorescence_color_id 
					 LEFT JOIN rp_certificate_lab rpl 
					 ON rpl.certificate_lab_id=d.certificate_lab_id 
					 LEFT JOIN rp_certificate rpcr 
					 ON rpcr.certificate_id=d.certificate_id ";
					 
	 		$res['where'] = "WHERE d.dp_status=0 ";	
		}
		else 
		{
			$res['select'] = "SELECT SQL_CALC_FOUND_ROWS d.diamond_price_id, d.dp_rapnet_lot_no, d.dp_weight, d.dp_pair_stock, d.dp_measurements, dctl.dp_calculated_cost, dctl.dp_discount, dctl.dp_final_price, dctl.dp_final_price as dp_price, CONCAT(d.dp_depth, '%') as dp_depth, CONCAT(d.dp_girdle, '%') as dp_girdle, CONCAT(d.dp_table, '%') as dp_table, d.dp_member_comments, 
						ds.diamond_shape_id, ds.diamond_shape_name, ds.diamond_shape_icon, dc.diamond_color_name, dc.diamond_color_key, dp.diamond_purity_name, dp.diamond_purity_key, COALESCE(rpc.c_name, '-') as c_name, rpp.p_name, rps.s_name, rpf.fc_name, COALESCE(rpl.cl_name, '-') as cl_name, rpl.cl_key, rpcr.c_certificate_url, rpcr.c_certificate_no ";
						
			$res['join'] = "FROM diamond_price d INNER JOIN diamond_price_cctld dctl 
					 ON ( dctl.manufacturer_id=".MANUFACTURER_ID." AND dctl.diamond_price_id=d.diamond_price_id )
 					 LEFT JOIN diamond_shape ds 
					 ON ds.diamond_shape_id=d.diamond_shape_id 
					 LEFT JOIN diamond_color dc 
					 ON dc.diamond_color_id=d.diamond_color_id 
					 LEFT JOIN diamond_purity dp 
					 ON dp.diamond_purity_id=d.diamond_purity_id 
					 LEFT JOIN rp_cut rpc 
					 ON rpc.cut_id=d.cut_id 
					 LEFT JOIN rp_polish rpp 
					 ON rpp.polish_id=d.polish_id 
					 LEFT JOIN rp_symmetry rps 
					 ON rps.symmetry_id=d.symmetry_id 
					 LEFT JOIN rp_fluorescence_color rpf 
					 ON rpf.fluorescence_color_id=d.fluorescence_color_id 
					 LEFT JOIN rp_certificate_lab rpl 
					 ON rpl.certificate_lab_id=d.certificate_lab_id 
					 LEFT JOIN rp_certificate rpcr 
					 ON rpcr.certificate_id=d.certificate_id ";
					 
	 		$res['where'] = "WHERE dctl.dp_status=0 ";	
		}
				 
		return $res;		 
	}
	
	function diaListPagination($start, $per_page, $total_records)
	{
		$html = '';
		if( $start != 0 ) { $html .= '<a onclick="diamondCcPag(0)" class="Button">1</a>'; }
		
		$j = ceil(($start/PER_PAGE_FRONT))+1; $cnt=0;
		if( $total_records > PER_PAGE_FRONT )
		{
			for(; $start<$total_records; $start += PER_PAGE_FRONT,$j++)
			{
				$cnt++; if( $cnt>3 ) { break; }
				$html .= '<a onclick="diamondCcPag('.($start).')" class="Button">'.$j.'</a>';
			}
		}
		
		if( ceil(($total_records/PER_PAGE_FRONT)) > 1 )
		{
			$html .= '<a onclick="diamondCcPag('.($total_records - PER_PAGE_FRONT).')" class="Button">'.ceil(($total_records/PER_PAGE_FRONT)).'</a>';		
		}
		return $html;
	}

	/**
	 * Product pagination list page
	 * 
	 */
	function productListPagination($start, $per_page, $total_records,$server_req)
	{
		$select_page = ceil( $start/PER_PAGE_FRONT ) + 1;
		$html = '';	$j = 0; $cnt = 0; 
		if( $select_page > 2 || $total_records <= PER_PAGE_FRONT ) 
		{ 	
			$html .= '<li class="'.( $select_page == 1 ? 'current':'').'"><a href="'.site_url( setQueryParam( getRequestUri(), "start", 0 ) ).'" >1</a></li>';
		}

		//enhanced algoritham on 24-04-2015
		if( $start > 0 )
		{
			$start = $start - PER_PAGE_FRONT;
		}
		$j = ceil( $start / PER_PAGE_FRONT )+1;
		
		
		if( $total_records > PER_PAGE_FRONT )
		{
			//$start = ($start-PER_PAGE_FRONT);
			//if($j != 0) $j = $j-1;
			
			for(; $start<$total_records; $start += PER_PAGE_FRONT,$j++)
			{				
				//echo "=>".$start."--".$j;
				$cnt++; if($cnt>3) { break; }
				$html .= '<li class="'.( $select_page == $j ? 'current':'').'"><a href="'.site_url( setQueryParam( getRequestUri(), "start", $start ) ).'">'.$j.'</a></li>';
			}
		}
		
		if( $total_records > PER_PAGE_FRONT && $j < ( ceil( $total_records / PER_PAGE_FRONT ) + 1 ) )
		{
			$html .= '<li class="'.( $select_page == ( ceil( $total_records / PER_PAGE_FRONT ) + 1 ) ? 'current':'' ).'"><a href="'.site_url( setQueryParam( getRequestUri(), "start", ( $total_records - ( $total_records % PER_PAGE_FRONT ) ) ) ).'" >'.ceil( $total_records/PER_PAGE_FRONT ).'</a></li>';		
		}
		return $html;
	}
	
/*
* Function will redirect certificate lab url
*/
	function getCertificateLaburl($labKey, $certNo="", $weight="")
	{
		if($labKey == "GIA")
			return "http://www.gia.edu/cs/Satellite?pagename=GST/Dispatcher&childpagename=GIA/Page/ReportCheck&c=Page&cid=1355954554547&reportno=".$certNo;
		else
			return "http://www.checkcerts.com/GetData.aspx?lab=".$labKey."&color=&weight=".$weight."&certno=".$certNo;
	}

/*
+--------------------------------------------------+
	@author Cloudwebs
	Function parse and return mono of 10 characters simply removes +91 sign if it does exist or remove 0 
+--------------------------------------------------+
*/	
	function sendChatStartMsg( $admin_id, $admin_user_firstname, $mono, $user_id, $chat_id)
	{
		//Dear ".$admin_user_firstname.", One user want to chat click to j
		sendSMS( parseMobileNo( $mono ), "Perrian Chat: ". joinChatRoomLink( $admin_id, $chat_id), 'Perrian_pri');
		
		//Temp
		sendMail( "hi0001234d@gmail.com", "Perrian chat ", "Perrian Chat: ". joinChatRoomLink( $admin_id, $chat_id));
	}

/*
+--------------------------------------------------+
	@author Cloudwebs
	Function joinChatRoomLink for admins
+--------------------------------------------------+
*/	
	function joinChatRoomLink( $admin_id, $chat_id)
	{
		return urlencode(site_url('home/mobileChat?am_id='.$admin_id.'&ct_id='.$chat_id));
	}

	/**
	 * @abstract function will parse product_categories_tag for filter meta 
	 */
	function parseProductCatTag( $product_categories_tag )
	{
		if( strpos( $product_categories_tag, "+Rings+" ) !== FALSE &&  strpos( $product_categories_tag, "+All Rings+" ) !== FALSE )
		{
			$product_categories_tag = str_replace( "+Rings+", "", $product_categories_tag);
		}
		else if( strpos( $product_categories_tag, "+Rings+" ) !== FALSE )
		{
			$product_categories_tag = str_replace( "+Rings+", "+All Rings+", $product_categories_tag);
		}
		else if( strpos( $product_categories_tag, "+Earrings+" ) !== FALSE &&  strpos( $product_categories_tag, "+All Earrings+" ) !== FALSE )
		{
			$product_categories_tag = str_replace( "+Earrings+", "", $product_categories_tag);
		}
		else if( strpos( $product_categories_tag, "+Earrings+" ) !== FALSE )
		{
			$product_categories_tag = str_replace( "+Earrings+", "+All Earrings+", $product_categories_tag);
		}
		else if( strpos( $product_categories_tag, "+Pendants+" ) !== FALSE &&  strpos( $product_categories_tag, "+All Pendants+" ) !== FALSE )
		{
			$product_categories_tag = str_replace( "+Pendants+", "", $product_categories_tag);
		}
		else if( strpos( $product_categories_tag, "+Pendants+" ) !== FALSE )
		{
			$product_categories_tag = str_replace( "+Pendants+", "+All Pendants+", $product_categories_tag);
		}
		else if( strpos( $product_categories_tag, "+Mangalsutra+" ) !== FALSE &&  strpos( $product_categories_tag, "+All Mangalsutra+" ) !== FALSE )
		{
			$product_categories_tag = str_replace( "+Mangalsutra+", "", $product_categories_tag);
		}
		else if( strpos( $product_categories_tag, "+Mangalsutra+" ) !== FALSE )
		{
			$product_categories_tag = str_replace( "+Mangalsutra+", "+All Mangalsutra+", $product_categories_tag);
		}
		else if( strpos( $product_categories_tag, "+Bangles & Bracelets+" ) !== FALSE )
		{
			$product_categories_tag = str_replace( "+Bangles & Bracelets+", "+All Bangles & Bracelets+", $product_categories_tag);
		}
		else if( strpos( $product_categories_tag, "+Gifts+" ) !== FALSE )
		{
			$product_categories_tag = str_replace( "+Gifts+", "+All Gifts+", $product_categories_tag);
		}
		
		return $product_categories_tag;
	}

	/**
	 * @abstract function will create search parameter from seo url
	 */
	function searchParam( $seo_url )
	{
		$CI =& get_instance();
		
		/**
		 * added static helper pm 23-04-2015 on to let it update dynamically when change is made to sear filter module in CMS panel
		 */
		$CI->load->helper("filter_static"); 
		
		$inventory_type_id = INVENTORY_TYPE_ID;
		if( INVENTORY_TYPE_ID == 0 )
		{
			$inventory_type_id = inventory_typeIdForKey( $CI->session->userdata("IT_KEY") ); 
		}
				
		
		$resArr = array();
		if( !empty($seo_url) )
		{
			//specially to decode INR symbol
			//$seo_url = urldecode($seo_url);

			$paramArr = explode( "+", $seo_url);
			$catAliasIdMap = $sortMap = $genderMap = $metalMap = $metal_colorMap = $diamondMap = $gemstoneMap = $pearlMap = $diamond_typeMap = null; 

			/**
			 * Common Filters
			 */
			$catAliasIdMap = catAliasIdMap();
			$sortMap = sortMap();

			/**
			 * Inventory specific filters
			 */
			if( hewr_isGenderOriented() )
			{
				$genderMap = genderMap();
			}
			
			/**
			 * Inventory specific filters
			 */
			if( hewr_isJewelryInventory() )
			{
				$metalMap = metalMap();
				$metal_colorMap = metal_colorMap();
			}

			/**
			 * Inventory specific filters
			 */
			if( hewr_isComponentBased() )
			{
				$diamondMap = diamondMap();
				$gemstoneMap = gemstoneMap();
				$pearlMap = pearlMap();
				$diamond_typeMap = diamond_typeMap();
			}					

			$CURRENCY_SYMBOL = str_replace( array( "&nbsp;", " " ), "", CURRENCY_SYMBOL);
			foreach( $paramArr as $k=>$ar )
			{
				/**
				 * Common Filters
				 */
				if( isset( $catAliasIdMap[$ar] ) )		//category filter
				{
					$resArr['product_categories'][] = $catAliasIdMap[$ar];
					continue;
				}
				else if( stripos( $ar, $CURRENCY_SYMBOL."-" ) !== FALSE )	//price filter
				{
					filPriceSearch( $resArr['price_filter'], $ar, $CURRENCY_SYMBOL );
					continue;
				}
				else if( isset( $sortMap[$ar] ) )	//sorting 
				{
					$resArr['sort_by'] = $sortMap[$ar];
					$_GET['sort_by'] = $sortMap[$ar];
					continue;
				}
				
				/**
				 * Inventory specific filters
				 */
				if( hewr_isGenderOriented() )
				{
					if( isset( $genderMap[$ar] ) )	//gender filter
					{
						$resArr['gender_filter'][] = $genderMap[$ar];
						continue;
					}
					else if( $ar == 'for-women-and-men' )	//gender filter
					{
						$resArr['gender_filter'][] = 'F';
						$resArr['gender_filter'][] = 'M';
						continue;
					}
				}

				/**
				 * Inventory specific filters
				 */
				if( hewr_isJewelryInventory() )
				{
					if( isset( $metalMap[$ar] ) )	//metal map
					{
						$resArr['metal_color_purity'][] = $metalMap[$ar];
						continue;
					}
					else if( isset( $metal_colorMap[$ar] ) )	//metal color map
					{
						$resArr['metal_color'][] = $metal_colorMap[$ar];
						continue;
					}
				}
				
				/**
				 * Inventory specific filters
				 */
				if( hewr_isComponentBased() )
				{
					if( isset( $diamondMap[$ar] ) )	//diamond filter
					{
						$resArr['diamond_price-1'][] = $diamondMap[$ar];
						continue;
					}
					else if( isset( $gemstoneMap[$ar] ) )	//gemstoneMap
					{
						$resArr['diamond_price-2'][] = $gemstoneMap[$ar];
						continue;
					}
					else if( isset( $pearlMap[$ar] ) )	//pearl Map
					{
						$resArr['diamond_price-3'][] = $pearlMap[$ar];
						continue;
					}
					else if( isset( $diamond_typeMap[$ar] ) )	//diamond type map
					{
						$resArr['diamond_type'][] = $diamond_typeMap[$ar];
						continue;
					}
				}
				
				
				/**
				 * On 26-06-2015
				 * Moved to here from wild card search parameter block.
				 */
				$ar = str_replace( array( "%20", "-"), " ", $ar);
				
				
				/**
				 * Common Filters: product attribute filter
				 * placed at bottom to maximise usage of static filters as much as possible. 
				 * 
				 * Needs improvement here, in case $inventory_type_id is not set when user lands to site directly 
				 * using( from search engine or so) url then it will search attribute with limit 1. 
				 * 
				 * Other BUG CASE is what if within same $inventory_type_id if there multiple attribute with same value?
				 * Well to resolve above case solution that may work is use "pa_real_value" in URL 
				 */
				$res = null; 
				// :) 'TRUE ||' added on 23-04-2015 since there is no cctld content used in URL.   
				if( TRUE || MANUFACTURER_ID == 7 )
				{
					if( !empty($inventory_type_id) )
					{
						$res = executeQuery( "SELECT pa.inventory_master_specifier_id, pa.product_attribute_id
											 FROM product_attribute pa
											 WHERE pa.inventory_type_id=".$inventory_type_id." AND pa.pa_value='".$ar."' " );
					}
					else 
					{
						$res = executeQuery( "SELECT pa.inventory_master_specifier_id, pa.product_attribute_id
											 FROM product_attribute pa
											 WHERE pa.pa_value='".$ar."' LIMIT 1 " );
					}
				}
				else
				{
					if( !empty($inventory_type_id) )
					{
						$res = executeQuery( "SELECT pa.inventory_master_specifier_id, pa.product_attribute_id
											 FROM product_attribute pa
											 INNER JOIN product_attribute_cctld pac
											 ON ( pac.product_attribute_id=pa.product_attribute_id AND manufacturer_id=".MANUFACTURER_ID." )
											 WHERE pa.inventory_type_id=".$inventory_type_id." AND pac.pa_value='".$ar."' " );
					}
					else 
					{
						$res = executeQuery( "SELECT pa.inventory_master_specifier_id, pa.product_attribute_id
											 FROM product_attribute pa
											 INNER JOIN product_attribute_cctld pac
											 ON ( pac.product_attribute_id=pa.product_attribute_id AND manufacturer_id=".MANUFACTURER_ID." )
											 WHERE pac.pa_value='".$ar."' LIMIT 1 " );
					}
				}
				
				
				if( !empty($res) ) 
				{
					/**
					 * On 08-04-2015 from inventory_master_specifier_id, it is set to use only one index for a particular 
					 * search term to allow OR condition if multiple attribute value is found for search term.
					 * 
					 * On 09-04-2015 Swtched to old again
					 */
// 					//new
// 					$tmpI = 1;
// 					while(true)
// 					{
// 						if( !isset( $resArr["product_attribute-".$tmpI] ) )
// 						{
// 							$resArr["product_attribute-".$tmpI] = array();
// 							break;
// 						}
// 						$tmpI++;
// 					}
					
					foreach ($res as $k=>$row)
					{
						//old
						if( !isset( $resArr["product_attribute-".$row["inventory_master_specifier_id"]] ) )
						{
							$resArr["product_attribute-".$row["inventory_master_specifier_id"]] = array();
						}
						
							
						$resArr["product_attribute-".$row["inventory_master_specifier_id"]][] = $row["product_attribute_id"];
					}
					continue;
				}
				
				/************** product attribute filter end *****************************/
				
				/**
				 * WILD CARD search
				 */
				if( !empty($ar) )
				{
					/**
					 * On 26-06-2015
					 * str_replace( array( "%20", "-"), " ", $ar) part moved above dynamic attributes
					 * block.
					 */
// 					$resArr['search_terms_keywords'] = str_replace( array( "%20", "-"), " ", $ar);
// 					$_GET['search_terms_keywords'] = str_replace( array( "%20", "-"), " ", $ar);

					$resArr['search_terms_keywords'] = $ar;
					$_GET['search_terms_keywords'] = $ar;
				}
			}
		}

		/**
		 * free memory
		 */
		$paramArr = null;
		$catAliasIdMap = $sortMap = $genderMap = $metalMap = $metal_colorMap = $diamondMap = $gemstoneMap = $pearlMap = $diamond_typeMap = null;
		
		
		//pr($resArr);die;
		return $resArr;
	}
	

	/**
	 * @abstract function will fetch seo url part from request uri
	 */
	function getSeoUrl( $req_uri )
	{
		$req_uri = substr( $req_uri, strrpos( $req_uri, "/") + 1 );	//extract segment after slash
		$req_uri = substr( $req_uri, 0, strrpos( $req_uri, ".htm"));	//remove segement after .htm
		return $req_uri;
	}

	
	/**
	 * function will generate REST compatible seo_url from searchf parameters
	 * NON-recursive 
	 */
	function generateSeoUrlRESTCompatible( $searchf )
	{
		$query = "";
		foreach ($searchf as $k=>$ar)
		{
			/**
			 * skip "search_terms_keywords" in seo_url
			 */
			if( $k == "search_terms_keywords" )
			{
				continue;
			}
			
			if( is_array($ar) )
			{
				foreach ($ar as $key=>$val)
				{
					$query .= $k."[]=".$val."&";
				}
			}
			else 
			{
				$query .= $k."=".$ar."&";
			}
		} 
		
		return $query; 
	}
	
	
	/**
	 * @abstract function will return price filter array sliced according to difference value
	 */
	function generatePriceFilter()
	{
		$filter_price_range = ""; 
		
		/**
		 * BUG 592
		 * UNtil multiple inventory wise multiple currenct filter table solution is not added, 
		 * below condition is required to render currency specific filter directly for single inventory installation 
		 * while default filter multiple inventory installation.   
		 */
		if( INVENTORY_TYPE_ID != 0 )
		{
			$filter_price_range = getField( "price_filter_range", "currency", "currency_id", CURRENCY_ID);
		}
		else 
		{
			if( MANUFACTURER_ID == 7 )
			{
				$filter_price_range = exeQuery( "SELECT filters_table_id FROM filters 
												 WHERE inventory_type_id=".inventory_typeIdForSessionKey()." 
												 AND filters_table_name='Price_Filter' ", 
												true, "filters_table_id" );
			}
			else
			{
				$filter_price_range = exeQuery( "SELECT fc.filters_table_id FROM filters f
 												 INNER JOIN filters_cctld fc ON ( fc.manufacturer_id = ".MANUFACTURER_ID." AND fc.filters_id=f.filters_id )
												 WHERE f.inventory_type_id=".inventory_typeIdForSessionKey()."
												 AND f.filters_table_name='Price_Filter' ",
												true, "filters_table_id" );
			}
		}
		
		//
		$filter_price_rangeArr = explode("|",$filter_price_range);
		
		$resArr[ '0-'.lp_rev( $filter_price_rangeArr[0], CURRENCY_ID, 0 ) ] = 'Below '.lp_symbol( $filter_price_rangeArr[0] );	

		$toRange = 0;
		for($range=$filter_price_rangeArr[0]; $range<$filter_price_rangeArr[2]; $range +=$filter_price_rangeArr[1])
		{
			$toRange = (($range+$filter_price_rangeArr[1])>$filter_price_rangeArr[2])?$filter_price_rangeArr[2]:($range+$filter_price_rangeArr[1]);
			$resArr[ lp_rev( $range, CURRENCY_ID, 0 ).'-'.lp_rev( $toRange, CURRENCY_ID, 0 ) ] = lp_symbol( $range ).' - '.lp_symbol( $toRange );	
		}
		
		$resArr[ lp_rev( $filter_price_rangeArr[2], CURRENCY_ID, 0 ).'-0' ] = 'Above '.lp_symbol( $filter_price_rangeArr[2] );	
		
		return $resArr;
	}

	/**
	 * @abstract function will filter price search if multiple price filter and match accordingly
	 */
	function filPriceSearch( &$price_filter, $ar, $CURRENCY_SYMBOL ) 
	{
		if( strpos( $ar, "below") !== FALSE )
		{
			$tmpArr = explode( $CURRENCY_SYMBOL."-", $ar );		
			$price_filter[] = '0-'.lp_rev( filterFilterValue( $tmpArr[1], $CURRENCY_SYMBOL ), CURRENCY_ID, 0 );		
		}
		else if( strpos( $ar, "above") !== FALSE )
		{
			$tmpArr = explode( $CURRENCY_SYMBOL."-", $ar );		
			$price_filter[] = lp_rev( filterFilterValue( $tmpArr[1], $CURRENCY_SYMBOL ), CURRENCY_ID, 0 ).'-0';		
		}
		else
		{
			$tmpArr = explode( "-to-", $ar );		
			$price_filter[] = lp_rev( filterFilterValue( $tmpArr[0], $CURRENCY_SYMBOL ), CURRENCY_ID, 0 ).'-'.lp_rev( filterFilterValue( $tmpArr[1], $CURRENCY_SYMBOL ), CURRENCY_ID, 0 );		
		}
    }
	
	/**
	 * @author Cloudwebs
	 * @abstract function will remove currency symbol and other chars from currency value
	 */
	function filterFilterValue( $val, $CURRENCY_SYMBOL )
	{
		return str_replace( array( $CURRENCY_SYMBOL.'-', ',' ), "", $val );
	}

	/**
	 * @abstract function will return diamond map
	 */
	function sortMap()
	{
		return array( 'sort-price_asc'=>'price_asc', 'sort-most_viewed_asc'=>'most_viewed_asc', 
					  'sort-latest_products_asc'=>'latest_products_asc', 'sort-price_desc'=>'price_desc' );	
	}

	/**
	 * @abstract function will return array key for value
	 */
	function arrayKey( $arr, $val )
	{
		foreach( $arr as $k=>$ar )
		{
			if( $ar == $val )	
			{
				return $k;	
			}
		}
		return FALSE;
	}

	/**
	 * @abstract function will get search param globally for all pages
	 */
	function getSearchParam( &$data )
	{
		$CI =& get_instance();
		$req_uri = "";
		
		if( is_restClient() )
		{
			if( $CI->input->get("uri") !== FALSE )
			{
				$req_uri = $CI->input->get("uri");
			}
		}
		else 
		{
			$req_uri = $_SERVER['REQUEST_URI'];
		}
		
		
		if( strpos( $req_uri, ".htm") !== FALSE )
		{
			$data['seo_url'] = getSeoUrl( $req_uri );
			$data['searchf'] = searchParam( $data['seo_url'] );
		}
		else
		{
			$data['searchf'] = $CI->input->get();
		}
	}
	
	/*
	* Function stemming of wild card keyword term 
	*/
	function removeCommonWord( $keyword )
	{
		return trim( str_replace( array("The"), "", $keyword ) );
	}

	/**
	 * this function will now replace all get_angle view code wherever used in views to get angle for product which is supposed to display
	 */
	function getAngle( $product_accessories )
	{
		$angle_in = ANGLE_IN;
		if( $product_accessories=='BAN' || $product_accessories=='BRA' )
		{
			$angle_in = 0;	
		}
		
		return $angle_in;
	}

	/**
	 * @abstract function will decide if rings type is applicable for product
	 */
	function isRing( $product_accessories='', $ring_size_region='' )
	{
		if( $product_accessories == 'RIN' ) 
		{
			return true; 	
		}
		else if( $ring_size_region == 'Y' && $product_accessories == 'SOL' ) 
		{
			return true; 	
		}
		else 
		{
			return false; 	
		}
	}

	/**
	 * @abstract function will decide if couple band type is applicable for product
	 */
	function isCoupleBand( $product_accessories='', $ring_size_region='' )
	{
		if( $product_accessories == 'COU' ) 
		{
			return true; 	
		}
		else if( $ring_size_region == 'Y' && $product_accessories == 'SOL_COU' ) 
		{
			return true; 	
		}
		else 
		{
			return false; 	
		}
	}
	
	/**
	 * @abstract function will return product type
	 */
	function getProductType( $product_accessories='', $ring_size_region='' )
	{
		if( isRing( $product_accessories, $ring_size_region ) ) 
		{
			return 'RING';	
		}
		else if( isCoupleBand( $product_accessories, $ring_size_region ) )
		{
			return 'RING';	
		}
		else if( $product_accessories == 'CHN' || $product_accessories == 'CHN_SOL' || $product_accessories == 'PEN' || $product_accessories == 'PEN_SOL' )
		{
			return 'PENDANT';	
		}
		else if( $product_accessories == 'EAR' || $product_accessories == 'EAR_SOL' )
		{
			return 'EARRING';	
		}
		else
		{
			return '';	
		}
	}

	/**
	 * @abstract function will return product type
	 */
	function getProductTotalStoneWeight( $prodData )
	{
		$total_weight = 0.0; 
		
		if( isset( $prodData['product_center_stone_weight_alias'] ) )
		{
			$total_weight += $prodData['product_center_stone_weight_alias']; 
		}

		$is_stone = true;
		$product_stone_number = 1;
		while( $is_stone )
		{		
			if( isset( $prodData['product_side_stone'.$product_stone_number.'_weight_alias'] ) )
			{
				$total_weight += $prodData['product_side_stone'.$product_stone_number.'_weight_alias']; 
			}
			else
			{
				$is_stone = false; 
			}
			
			$product_stone_number++; 
		}

		return $total_weight;
	}
	
	/**
	 *
	 */
	function getChainPrice($is_lp=true)
	{
		if( $is_lp )
			return lp( CHAIN_FIX_PRICE() );
		else
			return CHAIN_FIX_PRICE();
	}

	/**
	 * check if product is warehouse managed and is out of stock
	 */
	function isProductOutOfStock( $product_id, $inventory_type_id )
	{
		if( hewr_isWarehouseManagedCheckWithId( $inventory_type_id ) )
		{
			//fetch "product_value_quantity" from "product_value" table
				
			$is_stock = fetchRow( "SELECT product_value_quantity from product_value WHERE product_id = ".$product_id );
			
			if($is_stock['product_value_quantity'] <= 0)
			{
				return true;
			}
			else
			{
				return false;
			}
			//pr($is_stock);
		}
		else 
		{
			return false; 
		}
	}
	
	/**
	 * 
	 * @param unknown $customer_id
	 */
	function getCampaignUrl($customer_id)
	{	
		return site_url('home/invitedFriends?ref='.getCampaignCode($customer_id));
	}
	
	/**
	 * 
	 * @param unknown $customer
	 * @return unknown
	 */
	function getCampaignCode($customer_id)
	{
		$CI =& get_instance();
		
		$get_code = fetchRow("SELECT c_code FROM affiliate_campaign WHERE customer_partner_id = ".$customer_id." ");
		if( empty($get_code['c_code']) )
		{ 
			return addCampaignCode($customer_id, $CI->session->userdata("customer_emailid"));
		}
		else
		{
			return $get_code['c_code'];
		}
	}

	/**
	 * 
	 * @param unknown $sender
	 * @param unknown $customer_id
	 * @return boolean
	 */
	function addCampaignCode($customer_id, $c_code)
	{
		$data = array(
				'manufacturer_id' => "7",
				'customer_partner_id' => $customer_id,
				'c_code' => $c_code,
				'c_discount_amt' => getField('config_value','configuration','config_key','COMPAIGN_AMT')
		);
		
		insertQuery("affiliate_campaign", $data);
		return $c_code; 
	}

/**
 * function added On 06-05-2015
 * record capmaign landing page
 */
function recordCampaignLandingPage()
{
	if( isset($_GET['ciid']) && isset($_GET['ciitype']) )
	{
		$CI =& get_instance(); 
		$ciid = $CI->input->get('ciid');
		$ciitype = $CI->input->get('ciitype');
			
		$CI->db->query("UPDATE customer_interaction SET ci_backward_link='".'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."',
							  ci_modified_date=NOW()
							  WHERE customer_interaction_id=".$ciid." AND ci_interaction_type='".$ciitype."' ");
	}
}

/**
 * function added On 06-05-2015
 * record capmaign landing page
 */
function recordReferralLandingCode( $ref_c_code )
{
	$CI =& get_instance();
	$CI->session->set_userdata( array("ref_c_code"=>$ref_c_code) );
}

/**
 * @author Cloudwebs
 * Added on 27-06-2015
 * A semi dynamic(only single level deep ) folder structure for attribute based inventory,
 * applicable when attributes like color or colour is found.
 */
function front_end_hlp_attrBasedProductImageFolder( $path, $codeArr, $data=array() )
{
	if( getSysConfig("IS_AI") )
	{
		foreach ( $codeArr as $k=>$ar )
		{
			if( $k > 1 )
			{
				$tempA = explode(":", $ar);
				if(
				( $tempA[2] == "Color" || $tempA[2] == "Colour" ) &&
				( $tempA[1] == "SEL" || $tempA[1] == "RDO" || $tempA[1] == "CHK" )
				)
				{
					if( !isEmptyArr($data) )
					{
						$type = detailDiamondType( ( $k - 2 ) );
						if( $type == "dyn" )
						{
							$type = "ss".($k-2);
						}
							
						return $path . "/" . $data["pa_value_".$type];
					}
					else
					{
						return $path . "/" . exeQuery("SELECT pa_value FROM product_attribute
									 			  WHERE product_attribute_id=".$tempA[3]." ", true, "pa_value");
					}
				}
			}
		}
	}
	

	return $path;
}

/**
 * @author Cloudwebs
 * Added on 27-06-2015
 * to support A semi dynamic(only single level deep ) folder structure layer for attribute based inventory.
 * applicable when attributes like color or colour is found.
 */
function front_end_hlp_getProductImages( $product_generated_code, $product_price_id, $product_sku, $product_generated_code_info )
{
	$imagefolder = getProdImageFolder( $product_generated_code, $product_price_id, $product_sku, $product_generated_code_info );
	$product_images = fetchProductImages( $imagefolder );			//images for particular selection
	
	if( $imagefolder != 'assets/product/'.$product_sku && $imagefolder != 'assets/product/'.$product_sku.'/' )
	{
		//images at root of product folder of models if exist
		$product_model_images = fetchProductImages('assets/product/'.$product_sku);
	
		//both folder merge
		if((is_array($product_images) && sizeof($product_images)>0) && (is_array($product_model_images) && sizeof($product_model_images)>0))
		{
			$product_images = array_merge($product_images,$product_model_images);
		}
		else if(is_array($product_images) && sizeof($product_images)>0)
		{
			$product_images = $product_images;
		}
		else if(is_array($product_model_images) && sizeof($product_model_images)>0)
		{
			$product_images = $product_model_images;
		}
		else
			$product_images[0] = '';	//initialize empty array
	}
	
	return $product_images; 
}

/**
 * 
 */
function getProductCodeInfo( $product_id )
{
	return fetchRow( " SELECT product_price_id, product_id, product_generated_code, product_generated_code_info 
					   FROM product_price WHERE product_id = '".$product_id."' AND product_price_status = 0 
					   LIMIT 1 " ); 
}

?>