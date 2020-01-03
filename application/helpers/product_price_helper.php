<?php
/**
 * @package he_: prp 
 * @author hrtech Dev Team
 * @version 1.9
 * @abstract product price helper
 */


/**
 * @author Cloudwebs
 * @abstract function will update whole product pricing table when there are changes in metal or diamond prices
 * if change in metal prices made manually then operation is triggered by users manually else if changes are dynamically updated from
 * any pricing API then operation triggered automatically after updation of metal prices
 * @param $product_id if product_id is supplied then operation is done for that particular product only means after product is inserted or updated
 * @return true if operation complete successfully else false
 * $product_price_calculated_price added to support dynamic product_price_calculated_price from RATE SET[Warehouse] module
 * $product_discounted_price added to support dynamic product_discounted_price from RATE SET[Warehouse] module
 * $margin_in_percent added to support dynamic margin from RATE SET[Warehouse] module
 */
function update_insertProductPrice( $product_id=0, $quantity=1, $is_return_costing=false, $is_update_all=false, $is_solitaire_update=false,
$product_price_calculated_price=0, $product_discounted_price=0, $margin_in_percent=0 )
{
	/**
	 * added on 08-04-2015 to allow calculation run for long time in case product has multiple customized variations
	 */
	setTimeLimit();

	$CI =& get_instance();
	$currency_id = 0;
	//$start_time= microtime(true);

	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	//log
	$query= $CI->db->query("SELECT MAX(product_price_id) as max FROM product_price");
	$product_price_id = $query->row('max')+1;

	if( IS_LOG )
	{
		$datetime = date('d-m-Y-H:i:s');
		$admin_user_id = $CI->session->userdata('admin_id');
		$admin_user = getField('admin_user_firstname','admin_user','admin_user_id',$admin_user_id);
		$fp = fopen(BASE_DIR."assets/update_product_price/log/price-update-log-".$product_id."-".$admin_user_id."-".time().".xml","w");
		fwrite($fp,'<?xml version="1.0" encoding="ISO-8859-1"?>
						<note>
							<Datetime>'.$datetime.'</Datetime>
							<updateby>'.$admin_user.'</updateby>
							<starttime>'.$datetime.'</starttime>
							<BeforeProdPriceID>'.$product_price_id.'</BeforeProdPriceID>
							<body>');
	}

	if( $is_update_all )
	{
		if( MANUFACTURER_ID == 7 )
		{
			$CI->db->query( "UPDATE product_price SET product_price_status_temp=1" );
		}
		else
		{
			$CI->db->query("UPDATE product_price_cctld SET product_price_status_temp=1 WHERE manufacturer_id=".MANUFACTURER_ID." ");
		}
	}
	else if( $is_solitaire_update )
	{
		if( MANUFACTURER_ID == 7 )
		{
			$CI->db->query("UPDATE product_price SET product_price_status_temp=1 WHERE product_id IN( SELECT DISTINCT product_id FROM product_category_map WHERE category_id IN( ".SOL_RING_PCID.", ".SOL_EARR_PCID.", ".SOL_PEND_PCID.") )");
		}
		else
		{
			$CI->db->query("UPDATE product_price_cctld SET product_price_status_temp=1 WHERE manufacturer_id=".MANUFACTURER_ID." AND product_price_id
								IN( SELECT product_price_id FROM product_price pc
								INNER JOIN product_category_map pcm
								ON pcm.product_id=pc.product_id
								WHERE category_id IN( ".SOL_RING_PCID.", ".SOL_EARR_PCID.", ".SOL_PEND_PCID." ) )");
		}
	}

	/**
	 * make dynamic constant defination if more ccTLD specific price calculatoin require
	 * flows are changed now, not too much only change is that manufacturer will support regions also over countries. <br>
	 * so is regional languages like country specific languages. <br><br>
	 *
	 * MANUFACTURER_ID 8 is for HI=> hindi regional language, but for it currency will be same INR. <br>
	 * MANUFACTURER_ID 9 is for GU=> gujrati regional language, but for it currency will be same INR. <br>
	 */
	if( MANUFACTURER_ID == 8 )
	{
		$currency_id = (int)getField( "currency_id", "currency", "currency_code", "INR" );
	}
	else if( MANUFACTURER_ID == 9 )
	{
		$currency_id = (int)getField( "currency_id", "currency", "currency_code", "INR" );
	}


	$sql = "SELECT config_key,config_value FROM configuration WHERE config_key IN ( 'LABOUR_CHARGE', 'COMPANY_PROFIT' )";
	$lab_comCharge = getDropDownAry($sql,"config_key","config_value",'',false);

	//common taxRate in case Product Wise tax not specified
	$taxRate = getField("config_value","configuration","config_key","TAX_RATE");

	//get array of diamond_type_id and diamond_type_key from diamond_type table: this will reduce fetching diamond type pn each call
	$diaTypeArr = array();
	$resTemp = $CI->db->query("SELECT diamond_type_id, diamond_type_key FROM diamond_type ")->result_array();
	foreach($resTemp as $k=>$ar)
	{	$diaTypeArr[$ar['diamond_type_id']] = $ar['diamond_type_key'];	}

	$sql = "SELECT product_id, inventory_type_id FROM product ";
	if( $product_id != 0 )
	{
		$sql .= " WHERE product_id=".$product_id;
	}
	else if( $is_solitaire_update )
	{
		$sql .= " WHERE product_id IN( SELECT DISTINCT product_id FROM product_category_map
					  WHERE category_id IN( ".SOL_RING_PCID.", ".SOL_EARR_PCID.", ".SOL_PEND_PCID.") ) ";
	}

	$resPro =$CI->db->query( $sql )->result_array(); //where("product_status",0) Change : status condition removed prices updated for disabled products also


	$cnttot = 0;
	if( !isEmptyArr($resPro) )
	{
		foreach($resPro as $kP=>$arP)
		{
			/**
			 *
			 */
			$compAttrArr = getcompAttrArr(false, $arP["inventory_type_id"]);

			/**
			 * @deprecated
			 * variable indicates if previous check for stone is true so that code should check for more diamonds. <br>
			 * no more necessary after dynamic inventory had been added
			*/
			$is_more_stone = true;

			//variable specifies if CZ category is applicable for more info on CZ see: CZ jewellery in perry UML
			$is_has_diamonds = false;

			if( MANUFACTURER_ID == 7 )
			{
				$select = "SELECT p.product_id,p.category_id,p.product_accessories, p.product_price, p.product_discount,p.product_shipping_cost,
									  p.product_cod_cost, p.product_tax_id,
								      mt.category_id as 'metal_category_id',mt.product_metal_weight,mp.mp_price_difference as 'mp_price_difference',
									  mp.metal_type_id,
									  mp.metal_color_id,mp.metal_purity_id ";

				$join = " FROM product p LEFT JOIN product_metal mt
							 ON mt.product_id=p.product_id LEFT JOIN metal_price mp
							 ON mp.metal_price_id=mt.category_id ";
			}
			else
			{
				$select = "SELECT p.product_id,p.category_id, p.product_accessories, p.product_price, prc.product_discount,p.product_shipping_cost,
									  p.product_cod_cost, p.product_tax_id,
									  mt.category_id as 'metal_category_id',mt.product_metal_weight,mp.mp_price_difference as 'mp_price_difference',
									  mp.metal_type_id,
									  mp.metal_color_id,mp.metal_purity_id ";

				$join = " FROM product p INNER JOIN product_cctld prc
							 ON ( prc.manufacturer_id=".MANUFACTURER_ID." AND prc.product_id=p.product_id )
							 LEFT JOIN product_metal mt
							 ON mt.product_id=p.product_id LEFT JOIN metal_price mp
							 ON mp.metal_price_id=mt.category_id ";
			}

			$where = "WHERE p.product_id=".$arP['product_id']." ";
			if( $CI->session->userdata("IT_KEY") == "JW" )
			{
				$where .= "AND mt.product_metal_status<>1 ";
			}

			/**
			 * dynamic inventory added on 07-03-2015
			 */
			$CI->db->query("DELETE FROM pp_pss_index_map WHERE product_id=".$product_id." ");
			//					if( MANUFACTURER_ID == 7 )
			//					{
			//						$CI->db->query("DELETE FROM pp_pss_index_map WHERE product_id=".$product_id." ");
			//					}
			//					else
			//					{
			//						$CI->db->query("DELETE FROM pp_pss_index_map_cctld WHERE manufacturer_id=".MANUFACTURER_ID." AND product_id=".$product_id." ");
			//					}

			$product_stone_number = 0;
			foreach ($compAttrArr as $compAttrKey=>$compAttrVal)
			{
				if( $product_stone_number == 0 )
				{
					//check if center stone exist for product
					$resC = executeQuery("SELECT 1 FROM product_center_stone WHERE product_id=".$arP['product_id']." AND inventory_master_specifier_id=".$compAttrVal["inventory_master_specifier_id"]." AND product_center_stone_status=0");
					if(!empty($resC))
					{
						$is_has_diamonds = true;

						$select .= ",'1' as 'is_cs',
										cs.category_id as 'cs_category_id', cs.product_center_stone_weight, cs.product_center_stone_size,
										cs.product_center_stone_total,
										dp_cs.dp_calculated_cost as 'cs_dp_calculated_cost', cs.pcs_diamond_shape_id,
										dp_cs.diamond_type_id as 'diamond_type_id_cs', dp_cs.diamond_color_id as 'diamond_color_id_cs',
										dp_cs.diamond_purity_id as 'diamond_purity_id_cs' ";
							
						$join .= "LEFT JOIN product_center_stone cs
		 					  		  ON cs.product_id=p.product_id LEFT JOIN diamond_price dp_cs
							  		  ON dp_cs.diamond_price_id=cs.category_id ";
							
						$where .= "AND cs.inventory_master_specifier_id=".$compAttrVal["inventory_master_specifier_id"]." AND cs.product_center_stone_status=0 ";
					}
					else
					{
						$select .= ",'0' as 'is_cs',
										'0' as 'cs_category_id','0' as 'product_center_stone_weight','0' as 'product_center_stone_size',
										'0' as 'product_center_stone_total','0' as 'cs_dp_calculated_cost',
										'0' as 'pcs_diamond_shape_id','0' as 'diamond_type_id_cs','0' as 'diamond_color_id_cs','0' as 'diamond_purity_id_cs' ";
					}
				}
				else if( $product_stone_number <= 2 )
				{
					//check if side stone 1 exist for product
					$resC = executeQuery("SELECT 1 FROM product_side_stone".$product_stone_number."
											  WHERE product_id=".$arP['product_id']." AND
											  inventory_master_specifier_id=".$compAttrVal["inventory_master_specifier_id"]." AND
											  product_side_stone".$product_stone_number."_status=0");
					if(!empty($resC))
					{
						$select .= ",'1' as 'is_ss".$product_stone_number."',
										ss".$product_stone_number.".category_id as 'ss".$product_stone_number."_category_id',
										ss".$product_stone_number.".product_side_stone".$product_stone_number."_weight,
										ss".$product_stone_number.".product_side_stone".$product_stone_number."_size,
										ss".$product_stone_number.".product_side_stone".$product_stone_number."_total,
										dp_ss".$product_stone_number.".dp_calculated_cost as 'ss".$product_stone_number."_dp_calculated_cost',
										ss".$product_stone_number.".pss".$product_stone_number."_diamond_shape_id,
										dp_ss".$product_stone_number.".diamond_type_id as 'diamond_type_id_ss".$product_stone_number."',
										dp_ss".$product_stone_number.".diamond_color_id as 'diamond_color_id_ss".$product_stone_number."',
										dp_ss".$product_stone_number.".diamond_purity_id as 'diamond_purity_id_ss".$product_stone_number."' ";
							
						$join .= "LEFT JOIN product_side_stone".$product_stone_number." ss".$product_stone_number."
									  ON ss".$product_stone_number.".product_id=p.product_id LEFT JOIN diamond_price dp_ss".$product_stone_number."
									  ON dp_ss".$product_stone_number.".diamond_price_id=ss".$product_stone_number.".category_id ";
							
						$where .= "AND ss".$product_stone_number.".inventory_master_specifier_id=".$compAttrVal["inventory_master_specifier_id"]." AND ss".$product_stone_number.".product_side_stone".$product_stone_number."_status=0 ";
					}
					else
					{
						$select .= ",'0' as 'is_ss".$product_stone_number."',
										'0' as 'ss".$product_stone_number."_category_id','0' as 'product_side_stone".$product_stone_number."_weight',
										'0' as 'product_side_stone".$product_stone_number."_size',
										'0' as 'product_side_stone".$product_stone_number."_total', '0' as 'ss".$product_stone_number."_dp_calculated_cost',
										'0' as 'pss".$product_stone_number."_diamond_shape_id','0' as 'diamond_type_id_ss".$product_stone_number."',
										'0' as 'diamond_color_id_ss".$product_stone_number."','0' as 'diamond_purity_id_ss".$product_stone_number."' ";
					}
				}
				else
				{

					$resC = executeQuery("SELECT 1 FROM product_side_stones WHERE product_id=".$arP['product_id']." AND
											  product_stone_number=".$product_stone_number." AND
											  inventory_master_specifier_id=".$compAttrVal["inventory_master_specifier_id"]." AND
											  product_side_stones_status=0 ");
					if(!empty($resC))
					{
						$select .= ", '1' as 'is_ss".$product_stone_number."',
										  ss".$product_stone_number.".category_id as 'ss".$product_stone_number."_category_id',
										  ss".$product_stone_number.".product_side_stones_weight as product_side_stone".$product_stone_number."_weight,
										  ss".$product_stone_number.".product_side_stones_size as product_side_stone".$product_stone_number."_size,
										  ss".$product_stone_number.".product_side_stones_total as product_side_stone".$product_stone_number."_total,
										  dp_ss".$product_stone_number.".dp_calculated_cost as 'ss".$product_stone_number."_dp_calculated_cost',
										  ss".$product_stone_number.".psss_diamond_shape_id as pss".$product_stone_number."_diamond_shape_id,
										  dp_ss".$product_stone_number.".diamond_type_id as 'diamond_type_id_ss".$product_stone_number."',
										  dp_ss".$product_stone_number.".diamond_color_id as 'diamond_color_id_ss".$product_stone_number."',
										  dp_ss".$product_stone_number.".diamond_purity_id as 'diamond_purity_id_ss".$product_stone_number."' ";
							
						$join .= "LEFT JOIN product_side_stones ss".$product_stone_number."
							  ON ( ss".$product_stone_number.".product_id=p.product_id AND ss".$product_stone_number.".product_stone_number=".$product_stone_number." )
							  LEFT JOIN diamond_price dp_ss".$product_stone_number."
							  ON dp_ss".$product_stone_number.".diamond_price_id=ss".$product_stone_number.".category_id ";
							
						$where .= "AND ss".$product_stone_number.".inventory_master_specifier_id=".$compAttrVal["inventory_master_specifier_id"]." AND ss".$product_stone_number.".product_side_stones_status=0 ";
					}
					else
					{
						$select .= ", '0' as 'is_ss".$product_stone_number."' ";
					}
				}
					
				$product_stone_number++;
			}

			//minus current stone by one to get to current value
			$product_stone_number--;

			$cntprod=0;
			$res = $CI->db->query($select.$join.$where)->result_array();
			//pr($res);
			//echo $select.$join.$where; die;
			if(!empty($res))
			{
				foreach($res as $k=>$ar)
				{
					$cnttot++;
					$cntprod++;

					$resArr = calcProductPrice($CI,$lab_comCharge,$taxRate,
							$ar['product_id'],$ar['category_id'],(int)$ar['product_discount'],(float)$ar['product_shipping_cost'],(float)$ar['product_cod_cost'],$ar['product_tax_id'],
							$ar['metal_category_id'],(float)$ar['product_metal_weight'],$ar['mp_price_difference'],
							(int)$ar['cs_category_id'],(float)$ar['product_center_stone_weight'],$ar['product_center_stone_total'],$ar['cs_dp_calculated_cost'],
							(int)$ar['ss1_category_id'],(float)$ar['product_side_stone1_weight'],$ar['product_side_stone1_total'],$ar['ss1_dp_calculated_cost'],
							(int)$ar['ss2_category_id'],(float)$ar['product_side_stone2_weight'],$ar['product_side_stone2_total'],$ar['ss2_dp_calculated_cost'],
							$quantity,$is_return_costing,
							$ar['metal_type_id'],$ar['metal_color_id'],$ar['metal_purity_id'],
							$ar['pcs_diamond_shape_id'],$ar['diamond_type_id_cs'],$ar['diamond_color_id_cs'],$ar['diamond_purity_id_cs'],
							$ar['pss1_diamond_shape_id'],$ar['diamond_type_id_ss1'],$ar['diamond_color_id_ss1'],$ar['diamond_purity_id_ss1'],
							$ar['pss2_diamond_shape_id'],$ar['diamond_type_id_ss2'],$ar['diamond_color_id_ss2'],$ar['diamond_purity_id_ss2'], $ar, $product_stone_number,
							$is_has_diamonds, $diaTypeArr, $ar['product_accessories'], $currency_id, $compAttrArr,
							$product_price_calculated_price, $product_discounted_price, $margin_in_percent);
					if( is_string($resArr) )
					{
						//log
						fwrite($fp,'<product>'.$resArr.'</product>');
					}

				}
			}


		}
	}
	$query= $CI->db->query("SELECT MAX(product_price_id) as max FROM product_price");
	$product_price_id = $query->row('max')+1;

	$CI->db->query("ALTER TABLE product_price AUTO_INCREMENT =".$product_price_id."");

	if($is_update_all)
	{
		if( MANUFACTURER_ID == 7 )
		{
			$CI->db->query("UPDATE product_price SET product_price_status=product_price_status_temp ");
		}
		else
		{
			$CI->db->query("UPDATE product_price_cctld SET product_price_status=product_price_status_temp WHERE manufacturer_id=".MANUFACTURER_ID." ");
		}
	}

	if( IS_LOG )
	{
		$datetime = date('d-m-Y-H:i:s');
		fwrite($fp,'</body><AfterProdPriceID>'.$product_price_id.'</AfterProdPriceID><total>Total '.$cnttot.' unique combinations updated-inserted.</total><endtime>'.$datetime.'</endtime></note>');
		fclose($fp);
			
		//echo "Time consumed to process the request : " .(microtime(true) - $start_time);
	}

	//removeCacheKey( '', 'filter' );

	if($is_return_costing)
	{ $resArr; }
	else
		return true;

	unset($CI);
}

/*
 * @author Cloudwebs
* @abstract function will calculate product costing as per metal,stones used
* @param $CI Code Igniter class object so that no need to create and close object on each call
* @param $lab_comCharge Company Labour Charge and Company Profit
* @param $metal_category_id Metal category id
* @param $product_metal_weight Metal weight
* @param $cs_category_id Center Stone category id
* @param $product_center_stone_weight Center Stone weight
* @param $product_center_stone_total Center Stone total
* @param $ss1_category_id Side Stone 1 category id
* @param $product_side_stone1_weight Side Stone 1 weight
* @param $product_side_stone1_total Side Stone 1 total
* @param $ss2_category_id Side Stone 2 category id
* @param $product_side_stone2_weight Side Stone 2 weight
* @param $product_side_stone2_total Side Stone 2 total
* @param $product_shipping_cost Product shipping cost
* @param $product_cod_cost product_cod_cost
* @param $product_tax_id product_tax_id
* @return double price
*/
function calcProductPrice($CI,$lab_comCharge,$taxRate,$product_id,$category_id,$product_discount,$product_shipping_cost,$product_cod_cost,$product_tax_id,
		$metal_category_id,$product_metal_weight,$mp_price_difference,
		$cs_category_id=0,$product_center_stone_weight=0,$product_center_stone_total=0,$cs_dp_calculated_cost=0,
		$ss1_category_id=0,$product_side_stone1_weight=0,$product_side_stone1_total=0,$ss1_dp_calculated_cost=0,
		$ss2_category_id=0,$product_side_stone2_weight=0,$product_side_stone2_total=0,$ss2_dp_calculated_cost=0,
		$quantity=1,$is_return_costing=false,
		$metal_type_id=0,$metal_color_id=0,$metal_purity_id=0,
		$pcs_diamond_shape_id=0,$diamond_type_id_cs=0,$diamond_color_id_cs=0,$diamond_purity_id_cs=0,
		$pss1_diamond_shape_id=0,$diamond_type_id_ss1=0,$diamond_color_id_ss1=0,$diamond_purity_id_ss1=0,
		$pss2_diamond_shape_id=0,$diamond_type_id_ss2=0,$diamond_color_id_ss2=0,$diamond_purity_id_ss2=0, $ar, $current_stone,
		$is_has_diamonds, $diaTypeArr, $product_accessories, $currency_id, $compAttrArr,
		$product_price_calculated_price, $product_discounted_price, $margin_in_percent )
{

	/**
	 * On 13-03-2015 it will now intialize product net price from product_price field instead of 0 to support dynamic inventory.
	 */
	//$price = 0.0;
	$price = $ar["product_price"];

	$product_price_weight = 0;	//saved in gm
	$resArr = array();
	$side_stones_idA = array();
	$product_price_cz = 0.0;
	$product_price_mount = 0.0;
	$avgPrice = 0.0;
	$tempCalculationPrice = 0.0;

	//making charges
	$data_product_making_price["gold_price"] = 0.0;
	$data_product_making_price["diamond_price"] = 0.0;
	$data_product_making_price["making_charge"] = 0.0;
	$data_product_making_price["vat_gst_charge"] = 0.0;

	$data_product_making_price["mount_gold_price"] = 0.0;
	$data_product_making_price["mount_diamond_price"] = 0.0;
	$data_product_making_price["mount_making_charge"] = 0.0;
	$data_product_making_price["mount_vat_gst_charge"] = 0.0;

	$data_product_making_price["cz_gold_price"] = 0.0;
	$data_product_making_price["cz_diamond_price"] = 0.0;
	$data_product_making_price["cz_making_charge"] = 0.0;
	$data_product_making_price["cz_vat_gst_charge"] = 0.0;

	//calculate metal cost
	if( $metal_category_id != 0 )
	{
		$price += $data_product_making_price["gold_price"] = $data_product_making_price["cz_gold_price"] = round($product_metal_weight * $mp_price_difference,2);
		$product_price_weight = $product_metal_weight;		//product_metal_weight is already in gm so need to convert
			
		$product_price_mount += $data_product_making_price["mount_gold_price"] = round($product_metal_weight * $mp_price_difference,2);
	}

	/**
	 * dynamic inventory added on 07-03-2015
	 */
	$product_stone_number = 0;
	foreach ($compAttrArr as $compAttrKey=>$compAttrVal)
	{
		if( $product_stone_number == 0 )
		{
			//calculate center stone cost
			if( $compAttrVal["ims_input_type"] == "JW_CS" || $compAttrVal["ims_input_type"] == "JW_SS1" ||
			$compAttrVal["ims_input_type"] == "JW_SS2" || $compAttrVal["ims_input_type"] == "JW_SSS" )
			{
				if( $ar["is_cs"] != 0 )
				{
					if( $product_accessories != 'SOL' )
					{
						//convert product_center_stone_weight into gm : Formula==> carat/5=gm , currently rounding with of 3 decimal selected
						$tempCalculationPrice = round($product_center_stone_weight * $cs_dp_calculated_cost,2);
						$price += $tempCalculationPrice;
						$data_product_making_price["diamond_price"] += $tempCalculationPrice;
							
						$product_price_weight += round($product_center_stone_weight/5,3);
					}
					else if( $product_accessories == 'SOL' ) //currently solitaire diamonds are only stored in center stone and so considered only for center stone
					{
						$avgPrice = avgSolitaireDiamondPrice( $cs_category_id, $pcs_diamond_shape_id, $product_center_stone_weight );
							
						//round( $avgPrice * $product_center_stone_total , 2); for solitaire assume that only one stone is there because stone prices are provided with sum
						if( (int)$avgPrice != 0 )
						{
							$price += $avgPrice;
							$data_product_making_price["diamond_price"] += $avgPrice;

							$product_price_weight += round( $product_center_stone_weight/5, 3);
						}
						else
						{
							//save log in file and abort execution
							return 'Product with ID:'.$product_id.' has Avg price not found, so price calculation is aborted.';
						}
					}

					if( $is_has_diamonds )
					{
						if( $diaTypeArr[$diamond_type_id_cs] == 'DIAMOND' )
						{
							//formula number of stone * 50 INR  (50 INR per stone)
							$tempCalculationPrice = round($product_center_stone_total * CZ_STONE_PR,2);
							$product_price_cz += $tempCalculationPrice;
							$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
						}
						else if( $diaTypeArr[$diamond_type_id_cs] == 'GEMSTONE' )
						{
							//formula gemstone_weight * 500 INR (500 INR per ct)
							$tempCalculationPrice = round($product_center_stone_weight * CZ_GEM_SEM_PR,2);
							$product_price_cz += $tempCalculationPrice;
							$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
						}
						else if( $diaTypeArr[$diamond_type_id_cs] == 'PEARL' )
						{
							//PEARL cost will be as it is
							$tempCalculationPrice = round($product_center_stone_weight * $cs_dp_calculated_cost,2);
							$product_price_cz += $tempCalculationPrice;
							$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
						}
					}
				}
			}
		}
		else if( $product_stone_number <= 2 )
		{
			//calculate side stone1 cost
			if( $compAttrVal["ims_input_type"] == "JW_CS" || $compAttrVal["ims_input_type"] == "JW_SS1" ||
			$compAttrVal["ims_input_type"] == "JW_SS2" || $compAttrVal["ims_input_type"] == "JW_SSS" )
			{
				if( $ar["is_ss".$product_stone_number] != 0 )
				{
					//convert product_side_stone1_weight into gm : Formula==> carat/5=gm , currently rounding with of 3 decimal selected
					$tempCalculationPrice = round( ${"product_side_stone".$product_stone_number."_weight"} *
					${"ss".$product_stone_number."_dp_calculated_cost"}, 2 );
					$price += $tempCalculationPrice;
					$data_product_making_price["diamond_price"] += $tempCalculationPrice;
					$product_price_weight += round(${"product_side_stone".$product_stone_number."_weight"}/5,3);

					if( $product_accessories == 'SOL' )
					{
						$tempCalculationPrice = round( ${"product_side_stone".$product_stone_number."_weight"} *
						${"ss".$product_stone_number."_dp_calculated_cost"}, 2 );
						$product_price_mount += $tempCalculationPrice;
						$data_product_making_price["mount_diamond_price"] += $tempCalculationPrice;
					}

					if( $is_has_diamonds )
					{
						if( $diaTypeArr[${"diamond_type_id_ss".$product_stone_number.""}] == 'DIAMOND' )
						{
							//formula number of stone * 50 INR  (50 INR per stone)
							$tempCalculationPrice = round(${"product_side_stone".$product_stone_number."_total"} * CZ_STONE_PR,2);
							$product_price_cz += $tempCalculationPrice;
							$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
						}
						else if( $diaTypeArr[${"diamond_type_id_ss".$product_stone_number.""}] == 'GEMSTONE' )
						{
							//formula gemstone_weight * 500 INR (500 INR per ct)
							$tempCalculationPrice = round(${"product_side_stone".$product_stone_number."_weight"} * CZ_GEM_SEM_PR,2);
							$product_price_cz += $tempCalculationPrice;
							$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
						}
						else if( $diaTypeArr[${"diamond_type_id_ss".$product_stone_number.""}] == 'PEARL' )
						{
							//PEARL cost will be as it is
							$tempCalculationPrice = round( ${"product_side_stone".$product_stone_number."_weight"} *
							${"ss".$product_stone_number."_dp_calculated_cost"},2);
							$product_price_cz += $tempCalculationPrice;
							$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
						}
					}
				}
			}
		}
		else
		{
			if( $compAttrVal["ims_input_type"] == "JW_CS" || $compAttrVal["ims_input_type"] == "JW_SS1" ||
			$compAttrVal["ims_input_type"] == "JW_SS2" || $compAttrVal["ims_input_type"] == "JW_SSS" )
			{
				if( $ar["is_ss".$product_stone_number] != 0 )
				{
					//convert product_side_stone2_weight into gm : Formula==> carat/5=gm , currently rounding with of 3 decimal selected
					$tempCalculationPrice = round( $ar['product_side_stone'.$product_stone_number.'_weight'] * $ar['ss'.$product_stone_number.'_dp_calculated_cost'], 2 );
					$price += $tempCalculationPrice;
					$data_product_making_price["diamond_price"] += $tempCalculationPrice;
					$product_price_weight += round( $ar['product_side_stone'.$product_stone_number.'_weight']/5, 3 );

					if( $product_accessories == 'SOL' )
					{
						$tempCalculationPrice = round( $ar['product_side_stone'.$product_stone_number.'_weight'] * $ar['ss'.$product_stone_number.'_dp_calculated_cost'], 2 );
						$product_price_mount += $tempCalculationPrice;
						$data_product_making_price["mount_diamond_price"] += $tempCalculationPrice;
					}

					$side_stones_idA[] = $ar['ss'.$product_stone_number.'_category_id'];

					if( $is_has_diamonds )
					{
						if( $diaTypeArr[ $ar['diamond_type_id_ss'.$product_stone_number] ] == 'DIAMOND' )
						{
							//formula number of stone * 50 INR  (50 INR per stone)
							$tempCalculationPrice = round( $ar['product_side_stone'.$product_stone_number.'_total'] * CZ_STONE_PR, 2);
							$product_price_cz += $tempCalculationPrice;
							$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
						}
						else if( $diaTypeArr[ $ar['diamond_type_id_ss'.$product_stone_number] ] == 'GEMSTONE' )
						{
							//formula gemstone_weight * 500 INR (500 INR per ct)
							$tempCalculationPrice = round( $ar['product_side_stone'.$product_stone_number.'_weight'] * CZ_GEM_SEM_PR, 2);
							$product_price_cz += $tempCalculationPrice;
							$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
						}
						else if( $diaTypeArr[ $ar['diamond_type_id_ss'.$product_stone_number] ] == 'PEARL' )
						{
							//PEARL cost will be as it is
							$tempCalculationPrice = round( $ar['product_side_stone'.$product_stone_number.'_weight'] * $ar['ss'.$product_stone_number.'_dp_calculated_cost'], 2);
							$product_price_cz += $tempCalculationPrice;
							$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
						}
					}
				}
			}
				
		}
			
		$product_stone_number++;
	}


	// 	//calculate side stone2 cost
	// 	if( $ss2_category_id != 0 )
		// 	{
		// 		//convert product_side_stone2_weight into gm : Formula==> carat/5=gm , currently rounding with of 3 decimal selected
		// 		$tempCalculationPrice = round( $product_side_stone2_weight * $ss2_dp_calculated_cost, 2 );
		// 		$price += $tempCalculationPrice;
		// 		$data_product_making_price["diamond_price"] += $tempCalculationPrice;
		// 		$product_price_weight += round($product_side_stone2_weight/5,3);

		// 		if( $product_accessories == 'SOL' )
			// 		{
			// 			$tempCalculationPrice = round($product_side_stone2_weight * $ss2_dp_calculated_cost,2);
			// 			$product_price_mount += $tempCalculationPrice;
			// 			$data_product_making_price["mount_diamond_price"] += $tempCalculationPrice;
			// 		}
		
			// 		if( $is_has_diamonds )
				// 		{
				// 			if( $diaTypeArr[$diamond_type_id_ss2] == 'DIAMOND' )
					// 			{
					// 				//formula number of stone * 50 INR  (50 INR per stone)
					// 				$tempCalculationPrice = round($product_side_stone2_total * CZ_STONE_PR,2);
					// 				$product_price_cz += $tempCalculationPrice;
					// 				$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
					// 			}
					// 			else if( $diaTypeArr[$diamond_type_id_ss2] == 'GEMSTONE' )
						// 			{
						// 				//formula gemstone_weight * 500 INR (500 INR per ct)
						// 				$tempCalculationPrice = round($product_side_stone2_weight * CZ_GEM_SEM_PR,2);
						// 				$product_price_cz += $tempCalculationPrice;
						// 				$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
						// 			}
						// 			else if( $diaTypeArr[$diamond_type_id_ss2] == 'PEARL' )
							// 			{
							// 				//PEARL cost will be as it is
							// 				$tempCalculationPrice = round($product_side_stone2_weight * $ss2_dp_calculated_cost,2);
							// 				$product_price_cz += $tempCalculationPrice;
							// 				$data_product_making_price["cz_diamond_price"] += $tempCalculationPrice;
							// 			}
							// 		}
							// 	}


	//product_weight is rounded to two decimal as specified by "Kevin"
	$product_price_weight = round($product_price_weight,2);

	//subtotal obtained from metal and stone costing
	$subTotal = $price;

	//apply LABOUR_CHARGE to product costing
	//$subTotal += round($subTotal*((float)$lab_comCharge['LABOUR_CHARGE']/100),2);	 //Note: currently not applieds
	$tempCalculationPrice = round($product_price_weight * LAB_STA_CHARGE,2);	//currently labour charge specified statucally : fomula ==> $product_price_weight * 750 i.e. 750/gm
	$subTotal += $tempCalculationPrice;
	$data_product_making_price["making_charge"] = $tempCalculationPrice;

	$tempCalculationPrice = round($product_price_weight * LAB_STA_CHARGE,2);
	$product_price_cz += $tempCalculationPrice;
	$data_product_making_price["cz_making_charge"] = $tempCalculationPrice;

	$tempCalculationPrice = round($product_price_weight * LAB_STA_CHARGE,2);
	$product_price_mount += $tempCalculationPrice;
	$data_product_making_price["mount_making_charge"] = $tempCalculationPrice;

	//add shipping and COD cost to prod_price
	$subTotal+=$product_shipping_cost+$product_cod_cost;
	$product_price_cz+=$product_shipping_cost+$product_cod_cost;
	$product_price_mount+=$product_shipping_cost+$product_cod_cost;



	// 	if( MANUFACTURER_ID == 7 )
		// 	{
		// 		//currently shipping charge specified statically : fomula ==> 1750/product
		// 		$subTotal += SHIPP_CHARGE;
		// 		$product_price_cz += SHIPP_CHARGE;
		// 		$product_price_mount += SHIPP_CHARGE;
		// 	}
		// 	else if( MANUFACTURER_ID == 8 )
			// 	{}
	/**
	 * make this part dynamic like above if required.
	 */
	$subTotal += SHIPP_CHARGE;
	$product_price_cz += SHIPP_CHARGE;
	$product_price_mount += SHIPP_CHARGE;


	//currently packaging charge specified statically : fomula ==>  150/product
	$subTotal += PACK_CHARGE;
	$product_price_cz += PACK_CHARGE;
	$product_price_mount += PACK_CHARGE;

	//calculate costing as per quantity specified if not specified then default 1
	$subTotal = round($subTotal * $quantity,2);
	$product_price_cz = round($product_price_cz * $quantity,2);
	$product_price_mount = round($product_price_mount * $quantity,2);


	// apply tax rates to product costing Note : uncomment below code block if tax applied product wise
	// 	$taxAmt = 0;
	// 	$resArr ['product_tax'] = "";
	// 	$resArr ['product_tax_amt'] = 0;
	// 	if( ! empty ( $product_tax_id ) )
		// 	{
		// 		$taxidArr = explode ( "|", $product_tax_id );
		// 		foreach( $taxidArr as $key => $val )
			// 		{
			// 			$resTax = executeQuery ( "SELECT tax_rate_rate,tax_rate_type FROM tax_rate WHERE tax_rate_id=" . ( int ) $val . "" );
			// 			if( ! empty ( $resTax ) )
				// 			{
				// 				$resArr ['product_tax'] .= $resTax [0] ['tax_rate_type'] . "," . $resTax [0] ['tax_rate_rate'] . "|";
				// 				if( $resTax [0] ['tax_rate_type'] == "Fix" )
					// 					$taxAmt += round ( ( float ) $resTax [0] ['tax_rate_rate'] * $quantity, 2 ); // if fix rate then apply direct but as per quantity if quantity 2 then multiply by 2
					// 				else
						// 					$taxAmt += round ( $subTotal * (( float ) $resTax [0] ['tax_rate_rate'] / 100), 2 );
						// 			}
						// 		}
						// 		$resArr ['product_tax'] .= substr ( $resArr ['product_tax'], 0, - 1 );
						// 		$subTotal += $resArr ['product_tax_amt'] = $taxAmt;
						// 	}
						// 	else // if product tax not specified then apply common tax
						// 	{
						// 		$resArr ['product_tax'] .= "General," . $taxRate;
						// 		$taxAmt += round ( $subTotal * (( float ) $taxRate / 100), 2 );

	// 		$subTotal += $resArr ['product_tax_amt'] = $taxAmt;
	// 	}


	//apply extra charges --> To be included


	//apply COMPANY_PROFIT to costing
	//$subTotal += round($subTotal*((float)$lab_comCharge['COMPANY_PROFIT']/100),2); //Note: currently not applied

	//apply VAT to costing : currently 1% applied as specified
	$tempCalculationPrice = $subTotal;
	$subTotal += $subTotal * ( VAT_CHARGE / 100 );
	$data_product_making_price["vat_gst_charge"] = $subTotal - $tempCalculationPrice;

	$tempCalculationPrice = $product_price_cz;
	$product_price_cz += $product_price_cz * ( VAT_CHARGE / 100 );
	$data_product_making_price["cz_vat_gst_charge"] = $product_price_cz - $tempCalculationPrice;

	$tempCalculationPrice = $product_price_mount;
	$product_price_mount += $product_price_mount * ( VAT_CHARGE / 100 );
	$data_product_making_price["mount_vat_gst_charge"] = $product_price_mount - $tempCalculationPrice;


	//payment gateway charges: payU and PayPal both have same charges apply in product prices
	//apply Payment Gateway charge to costing : currently 4% applied as specified
	$payGateWayCharge = $subTotal;
	$subTotal += $subTotal * ( PAY_GATE_CHARGE / 100 );
	$payGateWayCharge = $subTotal - $payGateWayCharge;

	$subTotal += $payGateWayCharge * ( PAY_YOU_SER_TAX / 100 );	//pay gateways service tax

	//CZ payGateway commision
	$payGateWayCharge = $product_price_cz;
	$product_price_cz += $product_price_cz * ( PAY_GATE_CHARGE / 100 );
	$payGateWayCharge = $product_price_cz - $payGateWayCharge;

	$product_price_cz += $payGateWayCharge * ( PAY_YOU_SER_TAX / 100 );	//pay gateways service tax

	//CZ payGateway commision
	$payGateWayCharge = $product_price_mount;
	$product_price_mount += $product_price_mount * ( PAY_GATE_CHARGE / 100 );
	$payGateWayCharge = $product_price_mount - $payGateWayCharge;

	$product_price_mount += $payGateWayCharge * ( PAY_YOU_SER_TAX / 100 );	//pay gateways service tax

	//since this charge is addition for non-indian order that's why != 7 condition
	//quote rankit: it has to be like if a product mrp less than $ 200 then add anoter 10 on it and for more than $200 it will be same
	// 	$int_limit = 12000;
	// 	if( MANUFACTURER_ID != 7 )
		// 	{
		// 		if( $subTotal <= $int_limit )
			// 		{
			// 			$subTotal += $subTotal * 0.10;
			// 		}

	// 		if( $product_price_cz <= $int_limit )
		// 		{
		// 			$product_price_cz += $product_price_cz * 0.10;
		// 		}

	// 		if( $product_price_mount <= $int_limit )
		// 		{
		// 			$product_price_mount += $product_price_mount * 0.10;
		// 		}
		// 	}

	// 	10% GST charges only appicable to .au ccTLD: change GST excluded on 08-11-2014
	// 	if( MANUFACTURER_ID == 8 )
		// 	{
		// 		if( $avgPrice == 0 )
			// 		{
			// 			$tempCalculationPrice = $subTotal * AU_GST;
			// 		}
			// 		else
				// 		{
				// 			$tempCalculationPrice = ( $subTotal - $avgPrice ) * AU_GST;
				// 		}

	// 		//
	// 		$subTotal += $tempCalculationPrice;
	// 		$data_product_making_price["vat_gst_charge"] = $tempCalculationPrice;

	// 		//cz
	// 		$tempCalculationPrice = $product_price_cz * AU_GST;
	// 		$product_price_cz += $tempCalculationPrice;
	// 		$data_product_making_price["cz_vat_gst_charge"] = $tempCalculationPrice;

	// 		//mount
	// 		$tempCalculationPrice = $product_price_mount * AU_GST;
	// 		$product_price_mount += $tempCalculationPrice;
	// 		$data_product_making_price["mount_vat_gst_charge"] = $tempCalculationPrice;
	// 	}

	// 	Change 08-11-2014: is diamond condition omitted
	// 	//add 10% margin in price then offer 15% discount only in diamond and gemstone category
	// 	if( $product_accessories != 'SOL' )
		// 	{
		// 		$subTotal = round($subTotal / ( (100 - 10) /100),2);
		// 	}
		// 	else if( $product_accessories == 'SOL' )	//if solitaire category then add 15% and give 15% discount
		// 	{
		// 		$subTotal = round($subTotal / ( (100 - 15) /100),2);
		// 	}

	// 	$product_price_cz = round($product_price_cz / ( (100 - 10) /100),2);

	//add 7% margin if price is less then INR 15000 or otherwise 15% margin: this is provision to give 10% and 15% discount respectively: INR 14000 is the rule used to balance condition after discount is applied
	//gen
	//Change 08-11-2014=> Compnay_PRofit_Margin: if MANUFACTURER_ID==7 then 50% else if MANUFACTURER_ID=8 then 70%
	$compnay_profit_margin = $margin_in_percent;
	// 	if( MANUFACTURER_ID == 7 )
		// 	{
		// 		$compnay_profit_margin = 50;
		// 	}
		// 	else if( MANUFACTURER_ID == 8 )
			// 	{
			// 		$compnay_profit_margin = 70;
			// 	}

	//compnay_profit_margin
	$subTotal += $subTotal * ( $compnay_profit_margin / 100 );

	//sol
	if( $product_accessories == 'SOL' )
	{
		$product_price_mount += $product_price_mount * ( $compnay_profit_margin / 100 );
	}

	//cz
	if( $is_has_diamonds )
	{
		$product_price_cz += $product_price_cz * ( $compnay_profit_margin / 100 );
	}


	//apply product discount to costing
	$resArr['product_discount_amt'] = round($subTotal * ((int)$product_discount/100),2);
	$discountedTotal = round($subTotal - $resArr['product_discount_amt']);

	//product_price table data
	$data_prod_price['product_price_weight'] = $product_price_weight;
	$data_prod_price['product_price_calculated_price'] = round($subTotal);
	$data_prod_price['product_discount'] = $product_discount;
	$data_prod_price['product_discounted_price'] = $discountedTotal;
	$data_prod_price['product_price_status_temp'] = 0;

	/**
	 * if from RATE SET module static price has been provided then set prices to that particular, <br>
	 * and neglect all algoritham based price. <br>
	 * It is applicable to non-component based inventory only :)
	 */
	if( !empty($product_price_calculated_price) && !empty($product_discounted_price) )
	{
		$data_prod_price['product_discount'] = round( ( 1 - ( $product_discounted_price / $product_price_calculated_price ) ) * 100 );
		$data_prod_price['product_price_calculated_price'] = $product_price_calculated_price;
		$data_prod_price['product_discounted_price'] = $product_discounted_price;
	}

	//making charges information
	$data_product_making_price["product_price_calculated_price"] = $data_prod_price['product_price_calculated_price'];
	$data_product_making_price["product_discounted_price"] = $data_prod_price['product_discounted_price'];

	if( $is_has_diamonds )
	{
		//apply product discount to costing of CZ
		$resArr['product_discount_amt_cz'] = round($product_price_cz * ((int)$product_discount/100),2);
		$discountedTotalCz = round($product_price_cz - $resArr['product_discount_amt_cz']);
			
		//product_price table  data
		$data_prod_price['product_price_calculated_price_cz'] = round( $product_price_cz );
		$data_prod_price['product_discount_cz'] = $product_discount;	//currently product_discount is also discount for cz
		$data_prod_price['product_discounted_price_cz'] = $discountedTotalCz;
			
		//making charges information
		$data_product_making_price["product_price_calculated_price_cz"] = $data_prod_price['product_price_calculated_price_cz'];
		$data_product_making_price["product_discounted_price_cz"] = $data_prod_price['product_discounted_price_cz'];
	}


	if( $product_accessories == 'SOL' )
	{
		//discount nnot applicable to Mount
		$resArr['product_discount_amt_mount'] = 0; //round($product_price_mount * ((int)$product_discount/100),2);
		$discountedTotalMount = round($product_price_mount - $resArr['product_discount_amt_mount']);
			
		//product_price table  data
		$data_prod_price['product_price_calculated_price_mount'] = round( $product_price_mount );
		$data_prod_price['product_discount_mount'] = 0; //$product_discount;
		$data_prod_price['product_discounted_price_mount'] = $discountedTotalMount;
			
		//making charges information
		$data_product_making_price["product_price_calculated_price_mount"] = $data_prod_price['product_price_calculated_price_mount'];
		$data_product_making_price["product_discounted_price_mount"] = $data_prod_price['product_discounted_price_mount'];
	}

	//table fields for searching, add this field at last because these are not needed in update query
	$data_prod_price['metal_price_id'] = (int)$metal_category_id;
	$data_prod_price['metal_type_id'] = (int)$metal_type_id;
	$data_prod_price['metal_color_id'] = (int)$metal_color_id;
	$data_prod_price['metal_purity_id'] = (int)$metal_purity_id;
	$data_prod_price['cs_diamond_price_id'] = (int)$cs_category_id;
	$data_prod_price['pcs_diamond_shape_id'] = (int)$pcs_diamond_shape_id;
	$data_prod_price['diamond_type_id_cs'] = (int)$diamond_type_id_cs;
	$data_prod_price['diamond_color_id_cs'] = (int)$diamond_color_id_cs;
	$data_prod_price['diamond_purity_id_cs'] = (int)$diamond_purity_id_cs;
	$data_prod_price['ss1_diamond_price_id'] = (int)$ss1_category_id;
	$data_prod_price['pss1_diamond_shape_id'] = (int)$pss1_diamond_shape_id;
	$data_prod_price['diamond_type_id_ss1'] = (int)$diamond_type_id_ss1;
	$data_prod_price['diamond_color_id_ss1'] = (int)$diamond_color_id_ss1;
	$data_prod_price['diamond_purity_id_ss1'] = (int)$diamond_purity_id_ss1;
	$data_prod_price['ss2_diamond_price_id'] = (int)$ss2_category_id;
	$data_prod_price['pss2_diamond_shape_id'] = (int)$pss2_diamond_shape_id;
	$data_prod_price['diamond_type_id_ss2'] = (int)$diamond_type_id_ss2;
	$data_prod_price['diamond_color_id_ss2'] = (int)$diamond_color_id_ss2;
	$data_prod_price['diamond_purity_id_ss2'] = (int)$diamond_purity_id_ss2;

	/**
	 * Cloudwebs: dead code commented On 08-04-2015
	 */
	// 	$update = "";
	// 	foreach( $data_prod_price as $key=>$val )
		// 	{
		// 		$update .= $key."=".$val.", ";
		// 	}
		// 	$update .= "product_price_modified_date=NOW()";

	//product_price table data add this field at last because these are not needed in update query
	$data_prod_price['product_id'] = $product_id;
	$data_prod_price['product_generated_code_displayable'] = $data_prod_price['product_generated_code_info'] = "";
	$data_prod_price['product_generated_code'] = generateProductCode($product_id, $metal_category_id, $cs_category_id, $ss1_category_id, $ss2_category_id, $category_id, $side_stones_idA, $ar, $compAttrArr, $data_prod_price['product_generated_code_info'], $data_prod_price['product_generated_code_displayable']);

	// 	echo $data_prod_price['product_generated_code']."<br><br>";
	// 	echo $data_prod_price['product_generated_code_info']."<br><br>";

	//save-update in product_price table
	$product_price_id = product_priceCcTld( $CI, $data_prod_price['product_generated_code'], $data_prod_price );

	/**
	 * making charges information. <br>
	 * turn it on when making price CcTld feature is required.
	*/
	//product_making_priceCcTld( $CI, $product_price_id, $product_accessories, $data_product_making_price );


	//data for insert in pp_pss_index_map table
	$pp_pss_indexData['product_id'] = $product_id;
	$pp_pss_indexData['product_price_id'] = $product_price_id;

	/**
	 * dynamic inventory added on 07-03-2015
	 */
	$product_stone_number = 0;
	foreach ($compAttrArr as $compAttrKey=>$compAttrVal)
	{
		//Important NOTE*: Only if there is side stone 2 then it will check for more then 3 stone in product_side_stones table
		if( $product_stone_number >= 3 && $ar["is_ss".$product_stone_number] != 0 )
		{
			$pp_pss_indexData['product_stone_number'] = $product_stone_number;

			if( $compAttrVal["ims_input_type"] == "JW_CS" || $compAttrVal["ims_input_type"] == "JW_SS1" ||
			$compAttrVal["ims_input_type"] == "JW_SS2" || $compAttrVal["ims_input_type"] == "JW_SSS" )
			{
				$pp_pss_indexData['diamond_price_id'] = $ar['ss'.$product_stone_number.'_category_id'];
				$pp_pss_indexData['diamond_shape_id'] = $ar['pss'.$product_stone_number.'_diamond_shape_id'];
				$pp_pss_indexData['diamond_type_id'] = $ar['diamond_type_id_ss'.$product_stone_number.''];
				$pp_pss_indexData['diamond_color_id'] = $ar['diamond_color_id_ss'.$product_stone_number.''];
				$pp_pss_indexData['diamond_purity_id'] = $ar['diamond_purity_id_ss'.$product_stone_number.''];
			}
			elseif( $compAttrVal["ims_input_type"] == "JW_MTL" )
			{
				continue;
			}
			elseif( $compAttrVal["ims_input_type"] == "TXT" )
			{
				continue;
			}
			elseif( $compAttrVal["ims_input_type"] == "SEL" || $compAttrVal["ims_input_type"] == "CHK" || $compAttrVal["ims_input_type"] == "RDO" )
			{
				$pp_pss_indexData['diamond_shape_id'] = $ar['pss'.$product_stone_number.'_diamond_shape_id'];
			}

			$CI->db->insert( "pp_pss_index_map", $pp_pss_indexData);
			//pp_pss_index_mapCcTld( $CI, $pp_pss_indexData );
		}


		$product_stone_number++;
	}

	if($is_return_costing)
	{
		$resArr['product_generate_code'] = $data_prod_price['product_generated_code'];
		$resArr['product_shipping_cost'] = $product_shipping_cost;
		$resArr['product_cod_cost'] = $product_cod_cost;
		$resArr['product_value_quantity'] = $quantity;
		$resArr['product_price_calculated_price'] = $subTotal;
		$resArr['product_discount'] = $product_discount;
		$resArr['product_discounted_price'] = $discountedTotal;
		return $resArr;
	}
	else
		return true;

}

/**
 * function will set product_price ccTld
 */
function product_priceCcTld( $CI, $product_generated_code, $data_prod_price )
{
	$statusTemp = $data_prod_price['product_price_status_temp'];
	$data_prod_price["inventory_type_id"] = inventory_typeIdForKey($CI->session->userdata("IT_KEY"));

	$data_prod_price_cctld["inventory_type_id"] = $data_prod_price["inventory_type_id"];
	$data_prod_price_cctld['product_generated_code'] = $product_generated_code;
	$data_prod_price_cctld['product_generated_code_info'] = $data_prod_price['product_generated_code_info'];
	$data_prod_price_cctld['product_price_calculated_price'] = $data_prod_price['product_price_calculated_price'];
	$data_prod_price_cctld['product_discount'] = $data_prod_price['product_discount'];
	$data_prod_price_cctld['product_discounted_price'] = $data_prod_price['product_discounted_price'];

	if( isset($data_prod_price['product_price_calculated_price_cz']) )
	{
		$data_prod_price_cctld['product_price_calculated_price_cz'] = $data_prod_price['product_price_calculated_price_cz'];
		$data_prod_price_cctld['product_discount_cz'] = $data_prod_price['product_discount_cz'];
		$data_prod_price_cctld['product_discounted_price_cz'] = $data_prod_price['product_discounted_price_cz'];
	}

	if( isset($data_prod_price['product_price_calculated_price_mount']) )
	{
		$data_prod_price_cctld['product_price_calculated_price_mount'] = $data_prod_price['product_price_calculated_price_mount'];
		$data_prod_price_cctld['product_discount_mount'] = $data_prod_price['product_discount_mount'];
		$data_prod_price_cctld['product_discounted_price_mount'] = $data_prod_price['product_discounted_price_mount'];
	}

	$product_price_id = exeQuery( " SELECT product_price_id FROM product_price WHERE product_generated_code='".$product_generated_code."' ", true, "product_price_id" );
	$is_update = ( empty( $product_price_id ) ? false : true );

	if( $is_update )
	{
		unset($data_prod_price["product_generated_code_displayable"]);
		if(  MANUFACTURER_ID == 7 )
		{
			$CI->db->where( "product_price_id", $product_price_id)->update( "product_price", $data_prod_price );
		}
		else
		{
			$data_prod_price_cctld['product_price_status_temp'] = $statusTemp;
			$CI->db->where( "product_price_id", $product_price_id)
			->where( "manufacturer_id", MANUFACTURER_ID)
			->update( "product_price_cctld", $data_prod_price_cctld );
		}
	}
	else
	{
		$resManuf = getManufacturers();
		foreach( $resManuf as $k=>$ar )
		{
			if( $ar['manufacturer_id'] == 7 )	//primary EN_US
			{
				if( MANUFACTURER_ID != 7 )
				{
					/**
					 * On 04-05-2015
					 * to resolve bug 374 by default enable, if IS not CS(country wise store)
					 */
					if( IS_CS )
					{
						$data_prd_price['product_price_status_temp'] = 1;
					}
					else 
					{
						$data_prd_price['product_price_status_temp'] = $statusTemp;	//as per user's intentional status for all applicable ccTld
					}
				}
				$CI->db->insert( "product_price", $data_prod_price);

				//assumed that primary domain will come first in loop otherwise chance of code break/bug
				$product_price_id = $data_prod_price_cctld['product_price_id'] = $CI->db->insert_id();
					
				//update product_generated_code_displayable
				$data_prod_price["product_generated_code_displayable"] .= "-".$product_price_id;
				$data_prod_price_cctld["product_generated_code_displayable"] = $data_prod_price["product_generated_code_displayable"];
				$CI->db->where( "product_price_id", $product_price_id)
				->update( "product_price", array("product_generated_code_displayable"=>$data_prod_price["product_generated_code_displayable"]) );
			}
			else
			{
				if(  $ar['manufacturer_id'] == MANUFACTURER_ID )
				{
					$data_prod_price_cctld['product_price_status_temp'] = $statusTemp;
				}
				else
				{
					/**
					 * On 04-05-2015
					 * to resolve bug 374 by default enable, if IS not CS(country wise store)
					 */
					if( IS_CS )
					{
						$data_prod_price_cctld['product_price_status_temp'] = 1;
					}
					else
					{
						$data_prod_price_cctld['product_price_status_temp'] = $statusTemp;	//as per user's intentional status for all applicable ccTld
					}
				}
					
				$data_prod_price_cctld['manufacturer_id'] = $ar['manufacturer_id'];
				$CI->db->insert( "product_price_cctld", $data_prod_price_cctld);

				//no need to update here
				//update product_generated_code_displayable
				// 					$CI->db->where( "product_price_id", $product_price_id)
				// 						   ->where( "manufacturer_id", $ar['manufacturer_id'])
				// 						   ->update( "product_price_cctld", array("product_generated_code_displayable"=>$data_prod_price["product_generated_code_displayable"]) );

			}
		}
	}

	return $product_price_id;
}

/**
 * function will set product_price ccTld
 */
function product_making_priceCcTld( $CI, $product_price_id, $product_accessories, $data_product_making_price )
{
	if( $data_product_making_price['product_price_calculated_price'] != $data_product_making_price["product_discounted_price"] )
	{
		$data_product_making_price['product_discount'] = $data_product_making_price['product_price_calculated_price'] - $data_product_making_price['product_discounted_price'];
		$data_product_making_price['product_discount_mount'] = $data_product_making_price['product_price_calculated_price_mount'] - $data_product_making_price['product_discounted_price_mount'];
	}
	else
	{
		$data_product_making_price['product_discount'] = 0;
		$data_product_making_price['product_discount_mount'] = 0;
	}

	$data["product_price_id"] = $product_price_id;
	$vat_lbl = "";
	$mak_lbl = "";
	$table = "";
	if( MANUFACTURER_ID == 7 )
	{
		query( " DELETE FROM product_making_prices WHERE product_price_id=".$product_price_id." " );
			
		$vat_lbl = "VAT(1%)";
		$mak_lbl = "Making Charges";
		$table = "product_making_prices";
	}
	else if( MANUFACTURER_ID == 8 )
	{
		query( " DELETE FROM product_making_prices_cctld WHERE manufacturer_id=".MANUFACTURER_ID." AND product_price_id=".$product_price_id." " );
			
		$data["manufacturer_id"] = MANUFACTURER_ID;
		$vat_lbl = "GST(10%)";
		$mak_lbl = "Manufacturing";
		$table = "product_making_prices_cctld";
	}

	//gold
	$data["pmp_name"] = "Gold";
	$data["pmp_key"] = "GENERAL_GOLD";
	$data["pmp_value"] = $data_product_making_price["gold_price"];
	$CI->db->insert( $table, $data );

	$dia_vatArr = getProductMakingDiamondPrice( "GENERAL", $data_product_making_price );
	//Diamond
	$data["pmp_name"] = "Diamond";
	$data["pmp_key"] = "GENERAL_DIAMOND";
	$data["pmp_value"] = $dia_vatArr["diamond_price"];
	$CI->db->insert( $table, $data );

	//Making
	$data["pmp_name"] = $mak_lbl;
	$data["pmp_key"] = "GENERAL_MAKING";
	$data["pmp_value"] = $data_product_making_price["making_charge"];
	$CI->db->insert( $table, $data );

	//VAT
	$data["pmp_name"] = $vat_lbl;
	$data["pmp_key"] = "GENERAL_VAT";
	$data["pmp_value"] = $dia_vatArr["vat_gst_charge"];
	$CI->db->insert( $table, $data );

	//Discount
	if( !empty( $data_product_making_price['product_discount'] ) )
	{
		$data["pmp_name"] = "Discount";
		$data["pmp_key"] = "GENERAL_DISCOUNT";
		$data["pmp_value"] = $data_product_making_price["product_discount"];
		$CI->db->insert( $table, $data );
	}

	if( $product_accessories == "SOL" )
	{
		$dia_vatArr = getProductMakingDiamondPrice( "SOL", $data_product_making_price );
			
		//gold
		$data["pmp_name"] = "Gold";
		$data["pmp_key"] = "SOL_GOLD";
		$data["pmp_value"] = $data_product_making_price["mount_gold_price"];
		$CI->db->insert( $table, $data );

		//Diamond
		$data["pmp_name"] = "Diamond";
		$data["pmp_key"] = "SOL_DIAMOND";
		$data["pmp_value"] = $dia_vatArr["mount_diamond_price"];
		$CI->db->insert( $table, $data );

		//Making
		$data["pmp_name"] = $mak_lbl;
		$data["pmp_key"] = "SOL_MAKING";
		$data["pmp_value"] = $data_product_making_price["mount_making_charge"];
		$CI->db->insert( $table, $data );

		//VAT
		$data["pmp_name"] = "VAT(1%)";
		$data["pmp_key"] = "SOL_VAT";
		$data["pmp_value"] = $dia_vatArr["mount_vat_gst_charge"];
		$CI->db->insert( $table, $data );
			
		//Discount
		if( !empty( $data_product_making_price['product_discount_mount'] ) )
		{
			$data["pmp_name"] = "Discount";
			$data["pmp_key"] = "SOL_DISCOUNT";
			$data["pmp_value"] = $data_product_making_price["product_discount_mount"];
			$CI->db->insert( $table, $data );
		}
	}

	return true;
}

/**
 * @abstract
 */
function getProductMakingDiamondPrice( $type, $data_product_making_price )
{
	$res = array();
	//1%
	$vat_percent = 1;

	if( MANUFACTURER_ID == 8 )
	{
		//AU: 10%
		$vat_percent = 10;
	}


	if( $type == "GENERAL" )
	{
		$res["vat_gst_charge"] = round( ( $data_product_making_price["product_price_calculated_price"] -
				( ( $data_product_making_price["product_price_calculated_price"] / 100 ) * $vat_percent ) ) * ( $vat_percent / 100 ) ) ;
		$res["diamond_price"] = round( $data_product_making_price["product_price_calculated_price"] - ( $data_product_making_price["gold_price"] + $data_product_making_price["making_charge"] + $res["vat_gst_charge"] + $data_product_making_price["product_discount"] ) );
	}
	else if( $type == "SOL" )
	{
		$res["mount_vat_gst_charge"] = round( ( $data_product_making_price["product_price_calculated_price_mount"] -
				( ( $data_product_making_price["product_price_calculated_price_mount"] / 100 ) * $vat_percent ) ) * ( $vat_percent / 100 ) ) ;
		$res["mount_diamond_price"] = round( $data_product_making_price["product_price_calculated_price_mount"] - ( $data_product_making_price["mount_gold_price"] + $data_product_making_price["mount_making_charge"] + $res["mount_vat_gst_charge"] + $data_product_making_price["product_discount_mount"] ) );
	}

	return $res;
}

/**
 * function will set product_price ccTld: deprecated
 */
function pp_pss_index_mapCcTld( $CI, $pp_pss_indexData )
{
	$resManuf = getManufacturers();
	foreach( $resManuf as $k=>$ar )
	{
		if( $ar['manufacturer_id'] == 7 )	//primary
		{
			$CI->db->insert( "pp_pss_index_map", $pp_pss_indexData);
		}
		else
		{
			$CI->db->insert( "pp_pss_index_map_cctld", array_merge( $pp_pss_indexData, array( 'manufacturer_id' => $ar['manufacturer_id'] ) ) );
		}
	}
}


/*
 *  @abstract function will generate category prefix for product code
*/
function generateCategoryPrefix($category_id,$prefix='')
{
	$CI =& get_instance();

	$res = $CI->db->select("category_name,parent_id")->where("category_id",$category_id)->get("product_categories")->row_array();
		
	if($res['parent_id'] != 0)
	{
		return $prefix = generateCategoryPrefix($res['parent_id'],substr($res['category_name'],0,1)).$prefix;
	}
	else
	{
		return substr($res['category_name'],0,1).$prefix;
	}
	unset($CI);
}

/**
 * function will generate product code
 * Category prefix used in product code generation will be of first category specified for product out of multiplle category
 * product_generate_code format: PRS-101-2-C1-S23-S32	==> {CATEGORY PREFIX}-{product_id}-{product_side_stones_id a attribute information holder....N}
 * product_generated_code_displayable format: PRS-101-3421 ==> {CATEGORY PREFIX}-{product_id}-{product_price_id product childs table product runtime information holder, to speed up execution}
 * product_generated_code_info format: PRS|101|8:SEL:Color:76|...N 
 * 		==> {CATEGORY PREFIX}|{product_id}|{inventory_master_specifier_id attribute base information holder:ims_input_type attribute input type:
 * 			ims_input_label attribute input label:product_side_stones_id a attribute information holder....N}
 */
function generateProductCode($product_id,$metal_id,$center_stone_id=0,$side_stone1_id=0,$side_stone2_id=0,$category_id='',
							 $side_stones_idA=array(), $ar=array(), $compAttrArr=array(), &$product_generated_code_info,
							 &$product_generated_code_displayable)
{
	if($category_id == '')
		$category_id = getField("category_id","product","product_id",$product_id);

	$catArr = explode("|",$category_id);


	//$product_generate_code = "P";
	//$manufacturer_name = getField('manufacturer_name','manufacturer','manufacturer_id', MANUFACTURER_ID);
	/**
	 * seller first letter, for market place make it dynamic
	*/
	$manufacturer_name = "K";

	/**
	 * RAM or CPU ? :-)
	 */
	//code
	$product_generate_code = substr(strtoupper($manufacturer_name), 0, 1);
	$product_generate_code .= strtoupper(generateCategoryPrefix($catArr[0]));
	$product_generate_code .= '-'.$product_id;

	//displayable
	$product_generated_code_displayable = substr(strtoupper($manufacturer_name), 0, 1);
	$product_generated_code_displayable .= strtoupper(generateCategoryPrefix($catArr[0]));
	$product_generated_code_displayable .= '-'.$product_id;


	//info
	$product_generated_code_info = substr(strtoupper($manufacturer_name), 0, 1);
	$product_generated_code_info .= strtoupper(generateCategoryPrefix($catArr[0]));
	$product_generated_code_info .= '|'.$product_id;

	/**
	 *
	 */
	$product_stone_number = 0;
	foreach ($compAttrArr as $compAttrKey=>$compAttrVal)
	{
		$side_stone_primary_id = 0;
			
		if( $compAttrVal["ims_input_type"] == "JW_CS" || $compAttrVal["ims_input_type"] == "JW_SS1" ||
		$compAttrVal["ims_input_type"] == "JW_SS2" || $compAttrVal["ims_input_type"] == "JW_SSS" )
		{
			if( $product_stone_number == 0 )
			{
				//code
				$product_generate_code .= '-'.$center_stone_id;
					
				//info
				$product_generated_code_info .= "|".$compAttrVal["inventory_master_specifier_id"].":".$compAttrVal["ims_input_type"].":".$compAttrVal["ims_input_label"].":".$center_stone_id;
			}
			else if( $ar["is_ss".$product_stone_number] != 0 )
			{
				//code
				$product_generate_code .= '-'.$ar["ss".$product_stone_number."_category_id"];
					
				//info
				$product_generated_code_info .= "|".$compAttrVal["inventory_master_specifier_id"].":".$compAttrVal["ims_input_type"].":".$compAttrVal["ims_input_label"].":".$ar["ss".$product_stone_number."_category_id"];
			}
			else
			{
				//code
				$product_generate_code .= '-0';
					
				//info
				$product_generated_code_info .= "|".$compAttrVal["inventory_master_specifier_id"].":".$compAttrVal["ims_input_type"].":".$compAttrVal["ims_input_label"].":0";
			}
		}
		elseif( $compAttrVal["ims_input_type"] == "JW_MTL" )
		{
			//code
			$product_generate_code .= '-'.$metal_id;

			//info
			$product_generated_code_info .= "|".$compAttrVal["inventory_master_specifier_id"].":".$compAttrVal["ims_input_type"].":".$compAttrVal["ims_input_label"].":".$metal_id;
		}
		elseif( $compAttrVal["ims_input_type"] == "TXT" )
		{
			//code
			$product_generate_code .= '-0';

			//info
			$product_generated_code_info .= "|".$compAttrVal["inventory_master_specifier_id"].":".$compAttrVal["ims_input_type"].":".$compAttrVal["ims_input_label"].":0";
		}
		elseif( $compAttrVal["ims_input_type"] == "SEL" || $compAttrVal["ims_input_type"] == "CHK" || $compAttrVal["ims_input_type"] == "RDO" )
		{
			if( $product_stone_number == 0 )
			{
				//code
				$product_generate_code .= '-'.$ar["pcs_diamond_shape_id"];
					
				//info
				$product_generated_code_info .= "|".$compAttrVal["inventory_master_specifier_id"].":".$compAttrVal["ims_input_type"].":".$compAttrVal["ims_input_label"].":".$ar["pcs_diamond_shape_id"];
			}
			else if( $ar["is_ss".$product_stone_number] != 0 )
			{
				//code
				$product_generate_code .= '-'.$ar["pss".$product_stone_number."_diamond_shape_id"];
					
				//info
				$product_generated_code_info .= "|".$compAttrVal["inventory_master_specifier_id"].":".$compAttrVal["ims_input_type"].":".$compAttrVal["ims_input_label"].":".$ar["pss".$product_stone_number."_diamond_shape_id"];
			}
			else
			{
				//code
				$product_generate_code .= '-0';
					
				//info
				$product_generated_code_info .= "|".$compAttrVal["inventory_master_specifier_id"].":".$compAttrVal["ims_input_type"].":".$compAttrVal["ims_input_label"].":0";
			}
		}

		$product_stone_number++;
		// 			if( sizeof($side_stones_idA) > 0 )
			// 			{
			// 				foreach( $side_stones_idA as $k=>$ar )
				// 				{
				// 					$product_generate_code .= '-S'.$ar;
				// 				}
				// 			}
	}

	return $product_generate_code;
}
	
?>