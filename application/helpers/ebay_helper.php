<?php
//************************************** intialization ******************************************//
	loadLibrary( 'ebay-php-integration/eBayCommon' );

	//define currenct constant
	currencyConstant( 0, 'USD' );
	
	//Note: last ID before update in ebay_product table: 274
	
//************************************** intialization end **************************************//




/**
 * @abstract add item to eBay listing
 */
	function addeBayItem( $product_generated_code='', $product_sku='', $product_price_id=0, $type="", $mode="" ) 
	{
		$eBayCommon = new eBayCommon( $type );
		$eBayCommon->add_mode = $mode;
		
		$CI =& get_instance(); 
		
		if( !empty( $product_generated_code ) && empty( $product_price_id ) ) 
		{
			$product_price_id = getField( "product_price_id", "product_price", "product_generated_code", $product_generated_code ); 
		}
		
		//check if item exist on eBay server with same product_price_id: temp condition of > 274
		if($CI->session->userdata('ebay_country_id') !="")
		{
			if( checkIfRowExist( " SELECT 1 FROM ebay_product 
								 WHERE ep_status=0 AND product_price_id=".$product_price_id." 
								 AND ( ep_item_id<>'' AND ep_item_id<>0 AND ep_item_id IS NOT NULL ) 
								 AND ep_site_id = ".$CI->session->userdata('ebay_country_id')." LIMIT 1 " ) )
			{
				echo "Product already exist on eBay server with provided customization.<br><br>";
				return false; 	
			}
		}
		else
		{
			echo "Product is not available for this country. Please select country.<br><br>";
			return false;
		}
		
		if( !empty( $product_price_id ) ) 
		{
			$prodData = formatAddUpdateData( $product_price_id );
			
			if( !empty( $prodData ) ) 
			{
				$ebay_item_id = $eBayCommon->addItem( $prodData );
				if( !empty( $ebay_item_id ) )
				{
					$ebay_products_id = (int)$CI->input->get("ebay_products_id");
					if( empty($ebay_products_id) )
					{
						$CI->db->query( " INSERT INTO ebay_product( product_id, product_price_id, ep_item_id, ep_title, ep_listing_duration, ep_mode, ep_modified_date)
										  VALUES( ".$prodData['product_id'].", ".$product_price_id.", ".$ebay_item_id.", '".mysql_real_escape_string( $prodData['title'] )."',
										  ".listingDurationDays().", ".$mode.", NOW() ) " );
					}
					else
					{
						$CI->db->query( " UPDATE ebay_product SET ep_item_id=".$ebay_item_id.", ep_listing_duration=".listingDurationDays().", 
																  ep_mode=".$mode.", ep_status=0, ep_modified_date=NOW() 
															  WHERE ebay_products_id=".$ebay_products_id." " ); 
					}
					return true; 
				}
			}
		}
	}

/** 
 * @abstract update item to eBay listing
 * @param $product_generated_code
 */
	function updateeBayItem( $product_generated_code='', $product_sku='', $product_price_id=0, $type="", $add_mode="" ) 
	{
		$eBayCommon = new eBayCommon($type);
		$eBayCommon->add_mode = $add_mode;
		
		$CI =& get_instance(); 
		
		if( !empty( $product_generated_code ) && empty( $product_price_id ) ) 
		{
			$product_price_id = getField( "product_price_id", "product_price", "product_generated_code", $product_generated_code ); 
		}
		
		if( !empty( $product_price_id ) ) 
		{
			$prodData = formatAddUpdateData( $product_price_id );
			if( !empty( $prodData ) ) 
			{
				$ebay_products_id = $CI->input->get("ebay_products_id");
				$prodData['ebay_item_id'] = exeQuery( " SELECT ep_item_id FROM ebay_product WHERE ebay_products_id=".$ebay_products_id." ", true, "ep_item_id" ); 
				
				$ebay_item_id = $eBayCommon->updateItem( $prodData );
				if( !empty( $ebay_item_id ) )
				{
					//change: 5/6/2014 to consider date only when relisted on ebay 
					//$CI->db->query( " UPDATE ebay_product SET ep_modified_date=NOW() WHERE product_price_id=".$product_price_id." " );
					
					//change: 4/11/2014 item id udpated each time on update relist request
					$CI->db->query( " UPDATE ebay_product SET ep_item_id=".$ebay_item_id.", ep_modified_date=NOW() 
															  WHERE ebay_products_id=".$ebay_products_id." " ); 
					return true; 
				}

			}
		}
	}

/** 
 * @abstract update item to eBay listing
 */
	function relisteBayItem( $product_generated_code='', $product_sku='', $product_price_id=0, $type="", $ebay_products_id='', $add_mode="" ) 
	{
		$eBayCommon = new eBayCommon($type);
		$eBayCommon->add_mode = $add_mode;
		
		$CI =& get_instance(); 
		
		if( !empty( $product_generated_code ) && empty( $product_price_id ) ) 
		{
			$product_price_id = getField( "product_price_id", "product_price", "product_generated_code", $product_generated_code ); 
		}
		
		if( !empty( $product_price_id ) ) 
		{
			$prodData = formatAddUpdateData( $product_price_id );
			if( !empty( $prodData ) ) 
			{
				$ebay_get_products_id = $CI->input->get("ebay_products_id");
				if(!empty($ebay_get_products_id))
				{
					$ebay_products_id = $ebay_get_products_id;
				}
				
				$prodData['ebay_item_id'] = exeQuery( " SELECT ep_item_id FROM ebay_product WHERE ebay_products_id=".$ebay_products_id." ", true, "ep_item_id" ); 
				$ebay_item_id = $eBayCommon->relistItem( $prodData );
				if( !empty( $ebay_item_id ) )
				{
					$CI->db->query( " UPDATE ebay_product SET ep_listing_duration=".listingDurationDays().", ep_item_id=".$ebay_item_id.", ep_modified_date=NOW() WHERE ebay_products_id=".$ebay_products_id." " ); 
					return true; 
				}
				
			}
		}
	}

/**
 * @abstract delete item to eBay listing
 */
	function deleteeBayItem( $product_generated_code='', $product_sku='', $product_price_id=0, $type="", $add_mode="" ) 
	{
		$eBayCommon = new eBayCommon($type);
		$eBayCommon->add_mode = $add_mode;
		
		$CI =& get_instance(); 
		
		if( !empty( $product_generated_code ) && empty( $product_price_id ) ) 
		{
			$product_price_id = getField( "product_price_id", "product_price", "product_generated_code", $product_generated_code ); 
		}
		
		if( !empty( $product_price_id ) ) 
		{
			$ebay_products_id = $CI->input->get("ebay_products_id");
			$prodData['ebay_item_id'] = exeQuery( " SELECT ep_item_id FROM ebay_product WHERE ep_status=0 AND product_price_id=".$product_price_id." AND ebay_products_id = ".$ebay_products_id." ", true, "ep_item_id" ); 
			if( $eBayCommon->deleteItem( $prodData ) )
			{
				//$CI->db->query( " DELETE FROM ebay_images WHERE product_price_id=".$product_price_id." " );change: 5/6/2014 no need to delete image record in table since it can be reused
				$CI->db->query( " DELETE FROM ebay_product WHERE ebay_products_id=".$ebay_products_id." " ); 
				return true; 
			}
			else //if( $eBayCommon->errorCode == 1047 )
			{
				//item is ended on ebay so delete from client server 
				$CI->db->query( " DELETE FROM ebay_product WHERE ebay_products_id=".$ebay_products_id." " );
				return true;
			}
		}
	}

/** 
 * @abstract update item to eBay listing
 */
	function formatAddUpdateData( $product_price_id ) 
	{
		$CI =& get_instance();
		$ebay_products_id = $CI->input->get("ebay_products_id");
		
		$prodData = showProductsDetails( $product_price_id, false, true, false, '', '', '', 0); 
		
		if( !empty( $prodData ) ) 
		{
			$prodData["product_name"] = str_replace( "&", "and", $prodData["product_name"] );
				
			//product type
			$prodData['product_type'] = getProductType( $prodData['product_accessories'], $prodData['ring_size_region'] );

			
			//store category 
// 			$storeCateArr = array(); 
// 			foreach( $catArr as $k=>$ar )
// 			{
// 				//primary category should only derive from parent category
// 				$ebay_store_category_id = getField( "ebay_store_category_id", "product_categories", "category_id", $ar ); 
// 				if( !empty( $ebay_store_category_id ) )
// 				{
// 					$storeCateArr[] = $ebay_store_category_id;
// 				}
				
// 				if( count( $storeCateArr ) >= 2 )
// 				{
// 					break;	
// 				}
// 			}
			
// 			$prodData['StoreCategoryID'] = @$storeCateArr[0]; 
// 			if( !empty($storeCateArr[1]) )
// 			{
// 				$prodData['StoreCategory2ID'] = @$storeCateArr[1]; 
// 			}

			//eBay price
			$ep_product_price = 0;
			if( !empty($ebay_products_id) )
			{
				$ep_product_price = exeQuery( " SELECT ep_product_price FROM ebay_product WHERE ebay_products_id=".$ebay_products_id." ", true, "ep_product_price" );
			}
			
			//SEO page title			
			if( !empty($ebay_products_id) )
			{
				$ep_product = exeQuery( " SELECT ep_title, ep_qty, ep_listing_duration, ep_site_id FROM ebay_product WHERE ebay_products_id=".$ebay_products_id." " );
			}
			
			//currency information 
			//$prodData['currency_code'] = 'USD'; 
			$CI->session->set_userdata(array('ebay_country_id'=>$ep_product['ep_site_id']));
			$ebaySiteArr = getEbayCountryCode();
			
			//if(!empty($ebaySiteCurreArr))
				//$prodData['currency_code'] = $ebaySiteCurreArr;
			//else
			$prodData['currency_code'] = $ebaySiteArr['currency_code'];
			
			
			if( !empty( $ep_product_price ) )
			{
				$prodData['price'] = $ep_product_price;
			}
			else
			{
				$prodData['price'] = str_replace( ",", "", lp_base( $prodData['product_discounted_price'], 2, constant( 'CURRENCY_ID_'.$prodData['currency_code'] ), true ) );
			}
			
			//listing prices 
			//change: added extra 10 USD for extra ship cost occured for ebay orders
			$prodData["StartPrice"] = round( $prodData["price"] - ( $prodData["price"] * 0.35 ), 2 );
			$prodData["DiscountPriceInfo"] = $prodData["price"];
			$prodData["BuyItNowPrice"] = $prodData["price"];
			
			//convert price
			//$prodData['price_convert'] = str_replace( ",", "", lp_base( $prodData['product_discounted_price'], 2, constant( 'CURRENCY_ID_GBP' ), true ) );
			
			//echo 'aaa=';pr($prodData);die;
			
			
			$prodData['title'] = ( !empty( $ep_product['ep_title'] ) ? $ep_product['ep_title'] : $prodData['product_name'] ); 
			$prodData['quantity'] = ( !empty( $ep_product['ep_qty'] ) ? $ep_product['ep_qty'] : 1 ); 
			
			$prodData['duration'] = "";
			if(!empty($ep_product['ep_listing_duration']))
				$prodData['duration'] = 'Days_'.$ep_product['ep_listing_duration'];
			
			//primary category: listing category config 
			$catArr = explode( "|", $prodData['category_id'] ); 			
			foreach( $catArr as $k=>$ar )
			{
				//primary category should only derive from parent category
				$parent_id = getField( "parent_id", "product_categories", "category_id", $ar ); 
				if( empty( $parent_id ) )
				{
					$category_id = getField( "category_id", "product_categories", "category_id", $ar );
					$categoryEbayArr = exeQuery( " SELECT ebay_category_id FROM product_categories_ebay WHERE category_id=".$category_id." AND ebay_site_id=".@$ep_product['ep_site_id']." AND manufacturer_id=".MANUFACTURER_ID." " );
					$prodData['PrimaryCategoryID'] = $categoryEbayArr['ebay_category_id'];
					break;
				}
			}
			
			//product description: HTML code 
			$prodData['description'] = ebayHtmlPage( '', $product_price_id ); 
			
			//pr($prodData['product_images']); die; 
			//product images
			$prodData['product_images'] = uploadImage( $product_price_id, $prodData['product_images'], $prodData['product_name'], $prodData['product_angle_in']) ; 
			
			//stone total weight
			$prodData['stone_total_weight'] = getProductTotalStoneWeight( $prodData ); 
			
			//total diamond / gemstone weight
			$d_weight = $g_weight = "";
			if(!empty($prodData['diamond_type_key_cs']) && $prodData['diamond_type_key_cs'] == "DIAMOND"){
				$d_weight = $prodData['product_center_stone_weight'];
				$prodData['total_diamond_weight'] = ($d_weight) ? $d_weight : '-';
			}
			elseif(!empty($prodData['diamond_type_key_ss1']) && $prodData['diamond_type_key_ss1'] == "DIAMOND"){
				$d_weight = $prodData['product_side_stone1_weight'];
				$prodData['total_diamond_weight'] = ($d_weight) ? $d_weight : '-';
			}
			if(!empty($prodData['diamond_type_key_cs']) && $prodData['diamond_type_key_cs'] == "GEMSTONE"){
				$g_weight = $prodData['product_center_stone_weight'];
				$prodData['total_gemstone_weight'] = ($g_weight) ? $g_weight : '-';
			}
			elseif(!empty($prodData['diamond_type_key_ss1']) && $prodData['diamond_type_key_ss1'] == "GEMSTONE"){
				$g_weight = $prodData['product_side_stone1_weight'];
				$prodData['total_gemstone_weight'] = ($g_weight) ? $g_weight : '-';
			}
			
			return $prodData;
		}
		else
		{
			echo "Product seems unavailable.<br><br>";
			return false; 	
		}
	}

/**
 * @abstract upload image group of particular product's product_price_id to eBay server
 */
	function uploadImage( $product_price_id, Array $product_images, $picture_name='Perrian', $product_angle_in=0) 
	{
		$eBayCommon = new eBayCommon("Item");
		$CI =& get_instance(); 
		$product_imagesNew = array();
		
		//whether to upload require or there are available images which were previously uploaded: however should they have atleast 8 days lifetime before they are removed from ebay server
		$is_upload_images = true; 
		$cnt = (int)exeQuery( " SELECT 1 AS 'Cnt' FROM ebay_images WHERE product_price_id=".$product_price_id." AND ei_use_by_date > timestampadd(day, 8, NOW()) LIMIT 1 ", true, "Cnt" ); 
		if( !empty( $cnt ) ) 
		{
			$is_upload_images = false; 
		}
		
		if( $is_upload_images ) 
		{
			$CI->db->query( " DELETE FROM ebay_images WHERE product_price_id=".$product_price_id." " ); 
			
			foreach( $product_images as $k=>$ar )
			{
				$response = $eBayCommon->uploadImage( asset_url( $ar ), $picture_name );
				
				if( !empty( $response ) )
				{
					$product_imagesNew[$k] = $response['FullURL']; 	//assign ebay image URL value
					
					$CI->db->insert( "ebay_images", array( 'product_price_id'=>$product_price_id, 'ei_full_url'=>$product_imagesNew[$k], 'ei_use_by_date'=>$response['UseByDate'] ) ); 	
				}
			}
		}
		else
		{
			$response = executeQuery( " SELECT * FROM ebay_images WHERE product_price_id=".$product_price_id." " ); 	
			
			if( is_array( $response ) && sizeof( $response ) > 0 )
			{
				foreach( $response as $k=>$ar )
				{
					$product_imagesNew[$k] = $ar['ei_full_url']; 
				}
			}
			
		}

		if( is_array( $product_imagesNew ) && sizeof( $product_imagesNew ) > 0 )		
		{
			if( $product_angle_in != 0 && isset( $product_imagesNew[ $product_angle_in ] ) )
			{
				//swap intended angle to first place so that eBay will show only that angle in listings
				$temp_image = $product_imagesNew[ $product_angle_in ];
				$product_imagesNew[ $product_angle_in ] = $product_imagesNew[0];
				$product_imagesNew[0] = $temp_image;
			}
			return $product_imagesNew; 
		}
		else
		{
			return false;
		}
	}
	
	
	function getEbayCountryCode()
	{
		$CI =& get_instance();
		
		if( $CI->session->userdata('ebay_country_id') )
			$res['country_id'] = $CI->session->userdata('ebay_country_id');
		
		if( $CI->session->userdata('ebay_country_id') == 3 )
		{
			$res['country_id'] = 3;
			$res['currency_code'] = "GBP";
			$res['abbreviation'] = "UK";
			$res['ShippingService_LOCAL'] = "UK_StandardShippingFromOutside";
			$res['ShippingService'] = "UK_SellersStandardInternationalRate";
			currencyConstant( 16, 'GBP' );
		}
		else if( $CI->session->userdata('ebay_country_id') == 15 )
		{
			$res['country_id'] = 15;
			$res['currency_code'] = "AUD";
			$res['abbreviation'] = "Australia";
			$res['ShippingService_LOCAL'] = "AU_StandardDeliveryFromOutsideAU";
			$res['ShippingService'] = "AU_StandardInternational";
			currencyConstant( 5, 'AUD' );
		}
		else
		{
			$res['country_id'] = 0;
			$res['currency_code'] = "USD";
			$res['abbreviation'] = "US";
			$res['ShippingService_LOCAL'] = "StandardShippingFromOutsideUS";
			$res['ShippingService'] = "StandardInternational";
		}
		
		return $res;
		
	}
		
	

?>