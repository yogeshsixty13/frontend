<?php
/**
 * @package he_: cmn_vw
 * @version 1.0
 * @author Cloudwebs Tech Dev Team
 * common view helper<br> 
 * layer functions which responds to both Web and REST clients  
 */

/**
 * common function to get main menu data for both Web and REST clients
 */
function cmn_vw_mainMenu()
{
	$CI =& get_instance(); 
 	$cmn_vw = array();
 	
 	if( is_restClient() )
 	{
 		$res = executeQuery("SELECT module_manager_primary_id
 							 FROM banner_position b INNER JOIN module_manager m
							 ON m.position_id=b.banner_position_id
 							 WHERE banner_position_alias='top-menu' AND m.module_manager_status=0");
 		if(!empty($res))
 		{
 			$front_menu_id = $res[0]['module_manager_primary_id'];
 			$cmn_vw["res"] = menuCategoryDesk( $front_menu_id );
 			
 			if(!empty($cmn_vw["res"]))
 			{
 				$cmn_vw["mn"]["label"] = $cmn_vw["mn"]["desc"] = $cmn_vw["mn"]["image"] = array();
 				$cmn_vw["mn"]["href"] = $cmn_vw["mn"]["param"] = array();
 					
 				$cmn_vw["fm_icon_is_display"] = ((int)$cmn_vw["res"][0]['fm_icon_is_display'] == 0) ? true:false;
 		
 				$cmn_vw["mn"]["label"][] = getLangMsg( "hm" );
 				$cmn_vw["mn"]["desc"][] = "";
 				$cmn_vw["mn"]["image"][] = 'hm';
 				$cmn_vw["mn"]["href"][] = "home";
 				$cmn_vw["mn"]["param"][] = "is_parent=1";
 				
 				/**
 				 * since now MANUFACTURER_ID is language identifier, it is not needed to change link as per alias.
 				 */
 				//  				$wh = "";
 				//  				if( MANUFACTURER_ID != 7 )
 					//  				{
 					//  					$wh = " AND manufacturer_id=".MANUFACTURER_ID." ";
 					//  				}
 					
 				foreach($cmn_vw["res"] as $k=>$ar)
 				{
 					//if( $ar['front_menu_id'] == 65 ) { $giftAr = $ar; continue; }
 					$item = ""; $wh='';
 					
 					if($ar['front_hook_alias'] == "products")
 					{
 						/**
 						 * since now MANUFACTURER_ID is language identifier, it is not needed to change link as per alias.
 						 */
 						//$catTableName = ( MANUFACTURER_ID !=7 ) ? 'product_categories_cctld': 'product_categories';
 						$catTableName = 'product_categories';
 							
 						$item = exeQuery( " SELECT category_alias FROM ".$catTableName."
												   WHERE category_id=".$ar['front_menu_primary_id'] , true, "category_alias" );
 					}
 					else if($ar['front_hook_alias'] == "article")
 					{
 						$artTableName = ( MANUFACTURER_ID !=7 ) ? 'article_cctld': 'article';
 						/**
 						 * since now MANUFACTURER_ID is language identifier, it is not needed to change link as per alias.
 						 */
 						//$artTableName = ( MANUFACTURER_ID !=7 ) ? 'article_cctld': 'article';
 						$artTableName = 'article';
 		
 						$item = exeQuery( " SELECT article_alias FROM ".$artTableName."
												   WHERE article_id=".$ar['front_menu_primary_id'] , true, "article_alias" );
 					}

 					$cmn_vw["res"][$k]["item"] = ($item != "")?'/'.$item:'';
 					$cmn_vw["res"][$k]["cnt"] = getField("front_menu_id","front_menu","fm_parent_id",$ar['front_menu_id']);
 					
 		
 					/**
 					 * @deprecated
 					 * old category URL format now used only ListingUrl format
 					*/
 					/**
 					 * old
 					 * 
 					 * 
 					//$cmn_vw["res"][$k]["pURL"] = site_url('/'.$ar['front_hook_alias'].'/main'.$cmn_vw["res"][$k]["item"].'/'.$ar['front_menu_id']);
 					$cmn_vw["res"][$k]["pURL"] = "#"; //getListingUrl("", $cmn_vw["res"][$k]["item"]);
 		
 					$cmn_vw["mn"]["label"][] = $ar["front_menu_name"];
 					$cmn_vw["mn"]["desc"][] = "";
 					$cmn_vw["mn"]["image"][] = ($cmn_vw["fm_icon_is_display"]) ? load_image($ar['fm_icon']) : '';
 					$cmn_vw["mn"]["href"][] = "";
 					$cmn_vw["mn"]["param"][] = "is_parent=1";
 					*/
 					
 					/**
 					 * New 23-03-2016
 					 */
 					if( !empty($ar["fm_static_url_restapp"]) )
 					{
 						$tempA = explode("|", $ar["fm_static_url_restapp"]);
 						$cmn_vw["res"][$k]["pURL"]["href"] = (string) @$tempA[0];
 						$cmn_vw["res"][$k]["pURL"]["param"] = (string) @$tempA[1]."&is_parent=1";
 					}
 					else
 					{
 						$cmn_vw["res"][$k]["pURL"]["href"] = "";
 						$cmn_vw["res"][$k]["pURL"]["param"] = "";
 					}
 						
 					$cmn_vw["mn"]["label"][] = $ar["front_menu_name"];
 					$cmn_vw["mn"]["desc"][] = "";
 					$cmn_vw["mn"]["image"][] = ($cmn_vw["fm_icon_is_display"]) ? load_image($ar['fm_icon']) : '';
 					$cmn_vw["mn"]["href"][] = $cmn_vw["res"][$k]["pURL"]["href"];
 					$tempStr = $cmn_vw["res"][$k]["pURL"]["param"]."&is_parent=1";
 					$cmn_vw["mn"]["param"][] = $tempStr;
 					
 					if( $cmn_vw["res"][$k]["cnt"] > 0 )
 					{
 						if( MANUFACTURER_ID != 7 )
 						{
 							$cmn_vw["res"][$k]["res"] = executeQuery("SELECT mc.front_menu_id,mc.front_menu_name,m.front_hook_alias,m.front_menu_primary_id,m.is_display,m.fm_static_url_restapp,mc.fm_icon
														 			  FROM front_menu m
																	  INNER JOIN front_menu_cctld mc 
 																	  ON ( mc.manufacturer_id = ".MANUFACTURER_ID." 
 																	  AND mc.front_menu_id=m.front_menu_id )
																	  WHERE m.fm_parent_id=".$ar['front_menu_id']." 
 																	  AND mc.fm_status=0 
 																	  AND (m.is_display = 2 or m.is_display = 0)
 																	  ORDER BY mc.fm_sort_order");
 						}
 						else
 						{
 							$cmn_vw["res"][$k]["res"] = executeQuery("SELECT m.front_menu_id,m.front_menu_name,m.front_hook_alias,m.front_menu_primary_id,m.is_display,m.fm_static_url_restapp,m.fm_icon
																	  FROM front_menu m
																	  WHERE m.fm_parent_id=".$ar['front_menu_id']." 
 																	  AND m.fm_status=0 
 																	  AND (m.is_display = 2 or m.is_display = 0)
 																	  ORDER BY fm_sort_order");
 						}
 							
 						//
 						if( !isEmptyArr( $cmn_vw["res"][$k]["res"] ) )
 						{
 							foreach ( $cmn_vw["res"][$k]["res"] as $key=>$val )
 							{
 								if($val['front_hook_alias'] == "products")
 								{
 									$item = getField("category_alias","product_categories","category_id",$val['front_menu_primary_id']);
 								}
 								else if($val['front_hook_alias'] == "article")
 								{
 									$item = getField("article_alias","article","article_id",$val['front_menu_primary_id']);
 								}
 								
 								$item = (($item != "")?'/'.$item:'');

 								/**
 								 * New 02-04-2016
 								 */
 								$href = $hrefParam = "";
 								if( !empty($val["fm_static_url_restapp"]) )
 								{
 									$tempA = explode("|", $val["fm_static_url_restapp"]);
 									$href = (string) @$tempA[0];
 									$hrefParam = (string) @$tempA[1];
 								}
 								
 								$cmn_vw["mn"]["label"][] = $val["front_menu_name"];
 								$cmn_vw["mn"]["desc"][] = "";
 								$cmn_vw["mn"]["image"][] = ($cmn_vw["fm_icon_is_display"]) ? load_image($val['fm_icon']) : '';
 								
 								$tempArr = array();
 								if( !empty($href) )
 								{
 									$tempArr["href"] = $href;
 									$tempArr["param"] = $hrefParam;
 								}
 								else
 								{
 									$tempArr = getListingUrl( $cmn_vw["res"][$k]["item"], $item );
 								}

 								$cmn_vw["mn"]["href"][] = $tempArr["href"]; 
 								$cmn_vw["mn"]["param"][] = $tempArr["param"]."&is_parent=0";
 							}
 						}
 					}
 				}
 				
 				//special offer
 				$cmn_vw["mn"]["label"][] = getLangMsg( "spoff" );
 				$cmn_vw["mn"]["desc"][] = "";
 				$cmn_vw["mn"]["image"][] = 'h';	//heart
 				$tempArr = getListingUrl( "special-offer", "" );
 				$cmn_vw["mn"]["href"][] = $tempArr["href"]; 
 				$cmn_vw["mn"]["param"][] = $tempArr["param"]."&is_parent=1";

 				//Override Url
//  				$cmn_vw["mn"]["label"][] = "Override";
//  				$cmn_vw["mn"]["desc"][] = "";
//  				$cmn_vw["mn"]["image"][] = '';
//  				$tempArr = getListingUrl( "special-offer", "" );
//  				$cmn_vw["mn"]["href"][] = $tempArr["href"];
//  				$cmn_vw["mn"]["param"][] = $tempArr["param"]."&is_parent=1";
 				
 				//Language
 				$cmn_vw["mn"]["label"][] = "Languages";
 				$cmn_vw["mn"]["desc"][] = "";
 				$cmn_vw["mn"]["image"][] = '';
 				$cmn_vw["mn"]["href"][] = "";
 				$cmn_vw["mn"]["param"][] = "is_parent=1";
 					
 				//EN_US
 				$cmn_vw["mn"]["label"][] = "lang=".$CI->session->userdata("LANG");
 				$cmn_vw["mn"]["desc"][] = "en_us=".getLangMsg( "en_us" )."&hi=".getLangMsg( "hi" )."&gu=".getLangMsg( "gu" );
 				$cmn_vw["mn"]["image"][] = "";
 				$cmn_vw["mn"]["href"][] = "setLangSession";
 				$cmn_vw["mn"]["param"][] = "is_lang=1&is_parent=0";

//  				//hi
//  				$cmn_vw["mn"]["label"][] = getLangMsg( "hi" );
//  				$cmn_vw["mn"]["desc"][] = "";
//  				$cmn_vw["mn"]["image"][] = "";
//  				$cmn_vw["mn"]["href"][] = "setLangSession";
//  				$cmn_vw["mn"]["param"][] = "lang=HI&is_parent=0";
 				
//  				//GU
//  				$cmn_vw["mn"]["label"][] = getLangMsg( "gu" );
//  				$cmn_vw["mn"]["desc"][] = "";
//  				$cmn_vw["mn"]["image"][] = "";
//  				$cmn_vw["mn"]["href"][] = "setLangSession";
//  				$cmn_vw["mn"]["param"][] = "lang=GU&is_parent=0";

 				//Currency Label
 				$cmn_vw["mn"]["label"][] = "Currencies";
 				$cmn_vw["mn"]["desc"][] = "";
 				$cmn_vw["mn"]["image"][] = '';	//heart
 				$cmn_vw["mn"]["href"][] = "";
 				$cmn_vw["mn"]["param"][] = "is_parent=1";
 				
 				$sql = executeQuery(" SELECT currency_id,currency_code FROM currency WHERE currency_status=0 GROUP BY currency_code ORDER BY currency_code ");
 				
 				//
 				if( !isEmptyArr( $sql ) )
 				{
 					foreach ($sql as $cid)
 					{
 						$cur = "";
 						if( $cid['currency_id'] == CURRENCY_ID )
 						{
 							$cur = "&cur=1";
 						}
 						//Currency
 						$cmn_vw["mn"]["label"][] = $cid['currency_code'];
 						$cmn_vw["mn"]["desc"][] = "";
 						$cmn_vw["mn"]["image"][] = "";
 						$cmn_vw["mn"]["href"][] = "setCurrencySession";
 						$cmn_vw["mn"]["param"][] = "currency_id=".$cid['currency_id']."&is_parent=0".$cur;
 					}
 				}
 			}
 			else 
 			{
 				
 			}
 		}
 		
 		return $cmn_vw["mn"];
 	}
 	else 
 	{
 		$res = executeQuery("SELECT module_manager_primary_id
 							 FROM banner_position b INNER JOIN module_manager m
							 ON m.position_id=b.banner_position_id
 							 WHERE banner_position_alias='top-menu' AND m.module_manager_status=0");
 		
 		if(!empty($res))
 		{
 			$front_menu_id = $res[0]['module_manager_primary_id']; 
 			$cmn_vw["res"] = menuCategoryDesk( $front_menu_id );
 			if(!empty($cmn_vw["res"]))
 			{
 				$cmn_vw["fm_icon_is_display"] = ((int)$cmn_vw["res"][0]['fm_icon_is_display'] == 0)?true:false;

 				/**
 				 * since now MANUFACTURER_ID is language identifier, it is not needed to change link as per alias.
 				 */
//  				$wh = ""; 
//  				if( MANUFACTURER_ID != 7 )
//  				{
//  					$wh = " AND manufacturer_id=".MANUFACTURER_ID." ";
//  				}
 				
 				foreach($cmn_vw["res"] as $k=>$ar)
 				{
 					//if( $ar['front_menu_id'] == 65 ) { $giftAr = $ar; continue; }
 					$cmn_vw["res"][$k]["icon"] = ""; $item = ""; $wh='';
 					$cmn_vw["res"][$k]["icon"] = ($cmn_vw["fm_icon_is_display"])?'<img alt="'.$ar['front_menu_name'].'" title="'.$ar['front_menu_name'].'" src="'.load_image($ar['fm_icon']).'">':'';
 				
 						
 					if($ar['front_hook_alias'] == "products")
 					{
 						/**
						 * since now MANUFACTURER_ID is language identifier, it is not needed to change link as per alias.  
 						 */
 						//$catTableName = ( MANUFACTURER_ID !=7 ) ? 'product_categories_cctld': 'product_categories';
 						$catTableName = 'product_categories';
 				
 						$item = exeQuery( " SELECT category_alias FROM ".$catTableName."
												   WHERE category_id=".$ar['front_menu_primary_id'] , true, "category_alias" );
 					}
 					else if($ar['front_hook_alias'] == "articles")
 					{
 						$artTableName = ( MANUFACTURER_ID !=7 ) ? 'article_cctld': 'article';
 						/**
 						 * since now MANUFACTURER_ID is language identifier, it is not needed to change link as per alias.
 						 */
 						//$artTableName = ( MANUFACTURER_ID !=7 ) ? 'article_cctld': 'article';
 						$artTableName = 'article';
 							
 						$item = exeQuery( " SELECT article_alias FROM ".$artTableName."
												   WHERE article_id=".$ar['front_menu_primary_id'] , true, "article_alias" );
 					}
 				
 					$cmn_vw["res"][$k]["item"] = ($item != "")?'/'.$item:'';
 					$cmn_vw["res"][$k]["cnt"] = getField("front_menu_id","front_menu","fm_parent_id",$ar['front_menu_id']);
 					
 					/**
 					 * @deprecated
 					 * old category URL format now used only ListingUrl format
 					 */
 					//$cmn_vw["res"][$k]["pURL"] = site_url('/'.$ar['front_hook_alias'].'/main'.$cmn_vw["res"][$k]["item"].'/'.$ar['front_menu_id']);
 					$cmn_vw["res"][$k]["pURL"] = "#"; //getListingUrl("", $cmn_vw["res"][$k]["item"]);
 					
 					
 					if( $cmn_vw["res"][$k]["cnt"] > 0 )
 					{
 						if( MANUFACTURER_ID != 7 )
 						{
 							$cmn_vw["res"][$k]["res"] = executeQuery(" SELECT mc.front_menu_id,mc.front_menu_name,m.front_hook_alias,m.front_menu_primary_id,mc.fm_icon,m.is_display,m.fm_static_url_restapp
														 			   FROM front_menu m
																	   INNER JOIN front_menu_cctld mc 
 																	   ON ( mc.manufacturer_id = ".MANUFACTURER_ID." 
 																	   AND mc.front_menu_id=m.front_menu_id )
																	   WHERE m.fm_parent_id=".$ar['front_menu_id']." 
 									 								   AND mc.fm_status=0 
 																	   AND (m.is_display = 1 or m.is_display = 0)
 																	   ORDER BY mc.fm_sort_order");
 						}
 						else
 						{
 							$cmn_vw["res"][$k]["res"] = executeQuery("SELECT m.front_menu_id,m.front_menu_name,m.front_hook_alias,m.front_menu_primary_id,m.fm_icon,m.is_display,m.fm_static_url_restapp
																	  FROM front_menu m
																	  WHERE m.fm_parent_id=".$ar['front_menu_id']." 
 																	  AND m.fm_status=0 
 																	  AND (m.is_display = 1 or m.is_display = 0)
 																	  ORDER BY fm_sort_order");
 						}
 						
 					}
 				
 				}
 			}
 			 			
 		}
 	}
 	
	return $cmn_vw; 
 }
 
/**
 * common function to get filter data for both Web and REST clients
 */
function cmn_vw_prepareFilterData( $filter_data, $searchf, &$cmn_vw )
{
	$CI =& get_instance();

	/**
	 * seo_url REST compatible
	 */
	$cmn_vw["seo_url"] = generateSeoUrlRESTCompatible( $searchf );
	
	$cmn_vw["fl"]["label"] = $cmn_vw["fl"]["desc"] = $cmn_vw["fl"]["image"] = array();
	$cmn_vw["fl"]["href"] = $cmn_vw["fl"]["param"] = array();
	if( !isEmptyArr($filter_data) )
	{
		$is_metal_dis = false;
				
		foreach($filter_data as $k=>$ar):
		$table = "";
		$resArr = array();
								
			// if price filter then display price options
	    	if( $ar['filters_table_name'] == "price_filter" ):
				$table = $ar['filters_table_name'];
				$prcFil = generatePriceFilter();
				$cmn_vw["fl"]["label"][] = $ar['filters_name'];
				$cmn_vw["fl"]["desc"][] = "";
				$cmn_vw["fl"]["image"][] = "";
				$cmn_vw["fl"]["href"][] = "";
				$cmn_vw["fl"]["param"][] = "is_title=1";
								
								
								
				$toRange = $range = 0;
				$i =1;
          		foreach( $prcFil as $key=>$val ):
					$tmpArr = explode( "-", $key );
					$range = trim( $tmpArr[0] ); $toRange = trim( $tmpArr[1] ); 
					
					$i++;

					//seo URL 
					$resArr = generatePriceTag( (array)@$searchf[$table], $range,  $toRange);
					//$seo_url = generateSeoUrl( $search_url_tagArr, 'price_url_tag', $resArr['url_tag']);

					$cmn_vw["fl"]["label"][] = $val;
					$cmn_vw["fl"]["desc"][] = "";
					$cmn_vw["fl"]["image"][] = "";
					$cmn_vw["fl"]["href"][] = "";
					
					//check checkbox if it was checked by user
					$tempStr = "name=".$table."[]&val=".$key.""; 
					if( $resArr['is_searched'] )
					{
						$tempStr = "is_searched=1&".$tempStr; 
					}
					$cmn_vw["fl"]["param"][] = $tempStr; 
				endforeach;	
			/**
			 * if metal filter then display metal options right now there is different design for metal filter therefor separate condition required
			 */
			elseif(!$is_metal_dis && ($ar['filters_table_name'] == "metal_color" || $ar['filters_table_name'] == "metal_purity")):  
				$is_metal_dis = true; 
				//Metal filter not supported yet!</span>
			elseif( $ar['filters_table_name'] != "metal_color" && $ar['filters_table_name'] != "metal_purity" && 
					$ar['filters_table_name'] != "gender_filter" && $ar['filters_table_name'] != "price_filter" && 
					$ar['filters_table_name'] != "cz" && $ar['filters_table_name'] != "product_categories" ):
					 
				$res = executeQuery(generateFilterDisQuery($ar['filters_table_name'],$ar['filters_table_field_name'],$ar['filters_table_id']));	                 
				if(!empty($res)):
					$cmn_vw["fl"]["label"][] = $ar['filters_name'];
					$cmn_vw["fl"]["desc"][] = "";
					$cmn_vw["fl"]["image"][] = "";
					$cmn_vw["fl"]["href"][] = "";
					$cmn_vw["fl"]["param"][] = "is_title=1";
				
					$table = $ar['filters_table_name'];
	
					$i = 1;
					$original_table = $table;
					$pos = strpos($table,"-");
					if($pos != false)
					{
						$original_table = substr($table,0,$pos);					
					}
									
					foreach($res as $keyR=>$valR):
						$valR = array_values($valR);
						$i++;
						
						$cmn_vw["fl"]["label"][] = $valR[1];
						$cmn_vw["fl"]["desc"][] = "";
						$cmn_vw["fl"]["image"][] = "";
						$cmn_vw["fl"]["href"][] = "";
							
						//check checkbox if it was checked by user
						$tempStr = "name=".$table."[]&val=".$valR[0].""; 
						if( is_searched($valR[0],(array)@$searchf[$table]) )
						{
							$tempStr = "is_searched=1&".$tempStr; 
						}

						$cmn_vw["fl"]["param"][] = $tempStr;
						
					endforeach;

				endif;	
								
			/**
			 * product_categories filter
			 */
			elseif( $ar['filters_table_name'] == "product_categories" ): 
								
				$res = executeQuery( generateFilterDisQuery( $ar['filters_table_name'], $ar['filters_table_field_name'], $ar['filters_table_id'], ", category_alias " ) );	
				$product_categoriesArr = @$searchf['product_categories']; $parentRes;
				$sub_categoryKey = FALSE;
	
				if( isset($product_categoriesArr) && !isEmptyArr($product_categoriesArr) )
				{
					$parentRes = executeQuery( "SELECT category_id, parent_id FROM product_categories WHERE category_id IN(".implode(",",$product_categoriesArr).") " );
				}
				
				if(!empty($res)):

					$cmn_vw["fl"]["label"][] = $ar['filters_name'];
			        $cmn_vw["fl"]["desc"][] = "";
			        $cmn_vw["fl"]["image"][] = "";
			        $cmn_vw["fl"]["href"][] = "";
			        $cmn_vw["fl"]["param"][] = "is_title=1";
					         
					        
					$table = $ar['filters_table_name'];
					
					$i = 1;
					$original_table = $table;
					$pos = strpos($table,"-");
					if($pos != false)
					{
						$original_table = substr($table,0,$pos);					
					}
									
					$checked = ""; 
					foreach($res as $keyR=>$valR):
						$valR = array_values($valR);
						$i++;
						
						$cmn_vw["fl"]["label"][] = $valR[1];
						$cmn_vw["fl"]["desc"][] = "";
						$cmn_vw["fl"]["image"][] = "";
						$cmn_vw["fl"]["href"][] = "";
							
						//check checkbox if it was checked by user
						$tempStr = "name=".$table."[]&val=".$valR[0]."";
						if( is_searched($valR[0],(array)@$searchf[$table]) )
						{
							$checked = "Y"; 
							$tempStr = "is_searched=1&".$tempStr;
						}
						else 
						{
							$checked = ""; 
						}
						
						$cmn_vw["fl"]["param"][] = $tempStr;
						

						if( isset($parentRes) || $checked != '' ):
						
						/**
						 * On 29-04-2015 Needs improvement in logic here, the error on special-offers category was due to it was being searched as category while
						 * it does not exist in search filter of category
						 */ 
							if( $checked == '' && !isEmptyArr($parentRes) )	
							{
								$sub_categoryKey = associative_array_search( $parentRes, 'parent_id', $valR[0]);
							}
							
							
							$wh="";
							if( MANUFACTURER_ID != 7 )
							{
								$cctldTable = $ar['filters_table_name'].'_cctld';
								$wh .= ' AND pc.manufacturer_id='.MANUFACTURER_ID;
							}
							else
								$cctldTable = $ar['filters_table_name'];
							
							if( $sub_categoryKey !== FALSE || $checked != '' ):
								$resSubCat = executeQuery( "SELECT DISTINCT ".$ar['filters_table_field_name'].", category_name, category_alias 
															FROM ".$cctldTable." pc 
															INNER JOIN front_menu fm 
															ON (fm.front_menu_primary_id=pc.category_id AND fm.front_menu_table_name='".$ar['filters_table_name']."' ".$wh." AND pc.category_status=0 ) 
															WHERE parent_id=".$valR[0]." AND fm_status=0 ORDER BY pc.category_sort_order ASC "); 
		
								if(!empty($resSubCat)):
									foreach($resSubCat as $key1=>$val1):
										$valR = array_values($val1);
										$i++;
										
										$cmn_vw["fl"]["label"][] = $valR[1];
										$cmn_vw["fl"]["desc"][] = "";
										$cmn_vw["fl"]["image"][] = "";
										$cmn_vw["fl"]["href"][] = "";
											
										//check checkbox if it was checked by user
										$tempStr = "name=".$table."[]&val=".$valR[0]."&is_sub=2";
										if( is_searched($valR[0], $product_categoriesArr) )
										{
											$tempStr = "is_searched=1&".$tempStr;
										}
										
										$cmn_vw["fl"]["param"][] = $tempStr;
									endforeach;
								endif;
								
							endif;
							
						endif;
					endforeach;	

				endif;
								
			/**
			 * gender filters
			 * gender filters are not yet cctld-multi language supported, so it is required to implement that feature.
			 */ 
			elseif($ar['filters_table_name'] == "gender_filter"): 
					
				$table = $ar['filters_table_name'];

				$cmn_vw["fl"]["label"][] = $ar['filters_name'];
				$cmn_vw["fl"]["desc"][] = "";
				$cmn_vw["fl"]["image"][] = "";
				$cmn_vw["fl"]["href"][] = "";
				$cmn_vw["fl"]["param"][] = "is_title=1";
					                     
				//women											
				$cmn_vw["fl"]["label"][] = getLangMsg("wm");
				$cmn_vw["fl"]["desc"][] = "";
				$cmn_vw["fl"]["image"][] = "";
				$cmn_vw["fl"]["href"][] = "";
					
				//check checkbox if it was checked by user
				$tempStr = "name=".$table."[]&val=F";
				if( is_searched('F',(array)@$searchf[$table]) )
				{
					$tempStr = "is_searched=1&".$tempStr;
				}
				
				$cmn_vw["fl"]["param"][] = $tempStr;
					
				
				//men
				$cmn_vw["fl"]["label"][] = getLangMsg("mn");
				$cmn_vw["fl"]["desc"][] = "";
				$cmn_vw["fl"]["image"][] = "";
				$cmn_vw["fl"]["href"][] = "";
				
				//check checkbox if it was checked by user
				$tempStr = "name=".$table."[]&val=M";
				if( is_searched('M',(array)@$searchf[$table]) )
				{
					$tempStr = "is_searched=1&".$tempStr;
				}
					
				$cmn_vw["fl"]["param"][] = $tempStr;
												
			// For CZ filters
			elseif($ar['filters_table_name'] == "cz"):
				//not supported yet! 
			endif;
			
		endforeach;
				
	}		
	
	/***************************** sort_by filter **************************************/
	$cmn_vw["sf"]["label"] = $cmn_vw["sf"]["desc"] = $cmn_vw["sf"]["image"] = array();
	$cmn_vw["sf"]["href"] = $cmn_vw["sf"]["param"] = array();
	
	//title
	$cmn_vw["sf"]["label"][] = getLangMsg("sort");
	$cmn_vw["sf"]["desc"][] = "";
	$cmn_vw["sf"]["image"][] = "";
	$cmn_vw["sf"]["href"][] = "";
	$cmn_vw["sf"]["param"][] = "is_title=1";
	
	//popular 
	$cmn_vw["sf"]["label"][] = getLangMsg("pop");
	$cmn_vw["sf"]["desc"][] = "";
	$cmn_vw["sf"]["image"][] = "";
	$cmn_vw["sf"]["href"][] = "";
		
	//check checkbox if it was checked by user
	$tempStr = "name=sort_by&val=most_viewed_asc";
	if( @$_GET['sort_by']=='most_viewed_asc' )
	{
		$tempStr = "is_searched=1&".$tempStr;
	}
	$cmn_vw["sf"]["param"][] = $tempStr;

	//price_asc
	$cmn_vw["sf"]["label"][] = getLangMsg("plth");
	$cmn_vw["sf"]["desc"][] = "";
	$cmn_vw["sf"]["image"][] = "";
	$cmn_vw["sf"]["href"][] = "";
	
	//check checkbox if it was checked by user
	$tempStr = "name=sort_by&val=price_asc";
	if( @$_GET['sort_by']=='price_asc' )
	{
		$tempStr = "is_searched=1&".$tempStr;
	}
	$cmn_vw["sf"]["param"][] = $tempStr;
	
	//price_desc
	$cmn_vw["sf"]["label"][] = getLangMsg("phtl");
	$cmn_vw["sf"]["desc"][] = "";
	$cmn_vw["sf"]["image"][] = "";
	$cmn_vw["sf"]["href"][] = "";
	
	//check checkbox if it was checked by user
	$tempStr = "name=sort_by&val=price_desc";
	if( @$_GET['sort_by']=='price_desc' )
	{
		$tempStr = "is_searched=1&".$tempStr;
	}
	$cmn_vw["sf"]["param"][] = $tempStr;
	
	
	/***************************** sort_by filter end **************************************/
	
 	return $cmn_vw;
} 

/**
 *
 */
function cmn_vw_productListJSONObjRow( &$cmn_vw, $val )
{
	//product image folder
// 	$imagefolder = getProdImageFolder( $val['product_generated_code'], $val['product_price_id'], $val["product_sku"] );
// 	$product_images = fetchProductImages( $imagefolder );			//images for particular selection
	$product_images = front_end_hlp_getProductImages($val['product_generated_code'], $val['product_price_id'], 
													 $val["product_sku"], $val['product_generated_code_info']);
	
	
	$cmn_vw["pr"]["label"][] = $val["product_name"];
		
	/**
	 * description
	 */
	$tempStr = "";
	
	//
	$tempStr = "pid=".$val["product_price_id"]."&";
		
	//price
	$tempStr .= "product_discount=".$val['product_discount']."&"."product_price_calculated_price=".lp( $val['product_price_calculated_price'] )."&".
			"product_discounted_price=".lp( $val['product_discounted_price'] )."&";
	
	//stock
	if( isProductOutOfStock( $val["product_id"], $val["inventory_type_id"] ) )
	{
		$tempStr .= "is_out_of_stock=1&";
	}
	else
	{
		$tempStr .= "is_out_of_stock=0&";
	
		//qty
		if( hewr_isGroceryInventoryCheckWithId( $val["inventory_type_id"] ) )
		{
			$tempStr .= "isQty=1&opts=";
	
			$qtyOpts = getProdQtyOptions( $val["product_id"], $val["product_generated_code_info"], array() );
			foreach ($qtyOpts as $k1=>$ar1)
			{
				$tempStr .= $k1."=".$ar1."|";
			}
	
			$tempStr .= "&";
		}
		else
		{
			$tempStr .= "isQty=0&";
		}
	}
	
	//product types
	$tempStr .= "cz=&";	//$ this -> cz yet to make dynamic
	
	$cmn_vw["pr"]["desc"][] = $tempStr;
		
	/********************* desc end *************************/
		
		
	$cmn_vw["pr"]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
	$tempArr = getProductUrl($val['product_id'],$val['product_price_id'],$val['product_alias'],$val['category_id']);
	$cmn_vw["pr"]["href"][] = $tempArr["href"];
	$cmn_vw["pr"]["param"][] = $tempArr["param"];
}

/**
 *
 */
function cmn_vw_productListJSONObj( $listArr, &$cmn_vw, $limit=0, $cnt=0, $cz="", $filter_page="" )
{
	$cmn_vw["pr"]["label"] = $cmn_vw["pr"]["desc"] = $cmn_vw["pr"]["image"] = array();
	$cmn_vw["pr"]["href"] = $cmn_vw["pr"]["param"] = array();

	//total records
	$cmn_vw["pr"]["cnt"] = $cnt;
	
	//PER_PAGE_FRONT
	$cmn_vw["pr"]["PER_PAGE_FRONT"] = PER_PAGE_FRONT;
	
	//is_records
	if( empty($limit) && $cmn_vw["pr"]["cnt"] > PER_PAGE_FRONT )
	{
		$cmn_vw["pr"]["is_records"] = 1;
		
		/**
		 * more records callback controller, href and hrefParams
		 */
		
		$cmn_vw["pr"]["moreC"] = "rest/rest_products";
		$cmn_vw["pr"]["moreH"] = "scrollPagination";
		$cmn_vw["pr"]["moreP"] = "cz=".$cz."&page=".$filter_page;
	}
	else 
	{
		$cmn_vw["pr"]["is_records"] = 0;
		$cmn_vw["pr"]["moreC"] = "";
		$cmn_vw["pr"]["moreH"] = "";
		$cmn_vw["pr"]["moreP"] = "";
	}
	
	//cz
	$cmn_vw["pr"]["cz"] = $cz;
	
	//filter_page
	$cmn_vw["pr"]["filter_page"] = $filter_page;
	
	
	
	if( !isEmptyArr( $listArr ) )
	{
		foreach($listArr as $key=>$val)
		{
			/**
			 * only allow 3 products in featured products for RESTApps
			 */
			if( !empty($limit) && $key >= $limit )
			{
				break;
			}
			cmn_vw_productListJSONObjRow($cmn_vw, $val); 
		}
		
// 		/**
// 		 * Tell REST Apps to ask for more records on specified controller function on scroll event.
// 		 */
// 		if( $cmn_vw["pr"]["is_records"] == 1 )
// 		{
			
// 			$cmn_vw["pr"]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
// 			$tempArr = getProductUrl($val['product_id'],$val['product_price_id'],$val['product_alias'],$val['category_id']);
// 			$cmn_vw["pr"]["href"][] = $tempArr["href"];
// 			$cmn_vw["pr"]["param"][] = $tempArr["param"];
// 		}
	}
}

/**
 *
 */
function cmn_vw_scroll_pagination_listJSONObj( $listArr, &$cmn_vw )
{
	$cmn_vw["pr"]["label"] = $cmn_vw["pr"]["desc"] = $cmn_vw["pr"]["image"] = array();
	$cmn_vw["pr"]["href"] = $cmn_vw["pr"]["param"] = array();

	if( !isEmptyArr( $listArr ) )
	{
		foreach($listArr as $key=>$val)
		{
			cmn_vw_productListJSONObjRow($cmn_vw, $val);
		}
	}
	
	return $cmn_vw; 
}

/**
 * function will prepare filter data for REST clients
 */
function cmn_vw_getFilterData()
{
	$CI =& get_instance();
	$cmn_vw = array();
		
		//same for both so far
		if( MANUFACTURER_ID == 7 )
		{
			return executeQuery("SELECT filters_name,LOWER(filters_table_name) as filters_table_name,filters_table_field_name,filters_table_id FROM filters
									 WHERE inventory_type_id=".inventory_typeIdForKey($CI->session->userdata("IT_KEY"))." AND
									 filters_status=0 ORDER BY filters_sort_order");
		}
		else
		{
			return executeQuery("SELECT fc.filters_name,LOWER(f.filters_table_name) as filters_table_name, f.filters_table_field_name,
 									fc.filters_table_id as filters_table_id
									FROM filters f
 									INNER JOIN filters_cctld fc ON ( fc.manufacturer_id = ".MANUFACTURER_ID." AND fc.filters_id=f.filters_id )
									WHERE f.inventory_type_id=".inventory_typeIdForKey($CI->session->userdata("IT_KEY"))." AND
									fc.filters_status=0 ORDER BY fc.filters_sort_order");
		}

	return $cmn_vw;
}

/**
 * common function to get home data for both Web and REST clients
 */
function cmn_vw_home()
{
	$CI =& get_instance(); 
	$cmn_vw = array(); 
	
	/**
	 * Gautam: Featured products added on 11-04-2015
	 */
	$CI->load->model('mdl_products','jew'); 

	if( is_restClient() )
	{
		//main/drawer menu
		$cmn_vw["mn"] = cmn_vw_mainMenu();
		
		//banners
		$cmn_vw['bn']['title'] = "Banners";
		$cmn_vw["bn"]["label"] = $cmn_vw["bn"]["image"] = $cmn_vw["bn"]["href"] = $cmn_vw["bn"]["param"] = array();
		if( MANUFACTURER_ID == 7 )
		{
			$table = "slider";
			$wh = "";
		}
		else
		{
			$table = "slider_cctld";
			$wh = "manufacturer_id='" . MANUFACTURER_ID . "' AND ";
		}
		
		$sliderImg = executeQuery( "SELECT slider_image,slider_layout FROM $table WHERE ".$wh." slider_layout = 'M' AND slider_status=0 ORDER BY slider_sort_order" );
		if( !empty($sliderImg) )
		{
			foreach ($sliderImg as $ar)
			{
				$cmn_vw["bn"]["image"][] = asset_url($ar['slider_image']);
				$cmn_vw["bn"]["href"][] = "products_list";
				$cmn_vw["bn"]["param"][] = "";
			}
		}
		
		//new arrivals
		$cmn_vw['na']['title'] = "New Arrivals";
		$cmn_vw["na"]["label"] = $cmn_vw["na"]["desc"] = $cmn_vw["na"]["image"] = array(); 
		$cmn_vw["na"]["href"] = $cmn_vw["na"]["param"] = array();
		
		$naArr = $CI->jew->getProducts( staticCategoryIDs( "new-arrivals" ) );
		
		if( !isEmptyArr( $naArr["data"]["result_array"] ) )
		{
			foreach($naArr["data"]["result_array"] as $key=>$val)
			{
				/**
				 * only allow Products for set rest_dis Config file
				 */
				if( $key > getSysConfig("rest_dis") )
				{
					break;
				}
				
				//product image folder
// 				$imagefolder = getProdImageFolder( $val['product_generated_code'], $val['product_price_id'], $val["product_sku"] );
// 				$product_images = fetchProductImages( $imagefolder );			//images for particular selection
				$product_images = front_end_hlp_getProductImages($val['product_generated_code'], $val['product_price_id'],
														 		 $val["product_sku"], $val['product_generated_code_info']);
				
				
				$tempStr =  "product_discount=".$val['product_discount']."&".
							"product_price_calculated_price=".lp( $val['product_price_calculated_price'] )."&".
							"product_discounted_price=".lp( $val['product_discounted_price'] )."&";
				
				$cmn_vw["na"]["label"][] = char_limit($val['product_name'],MANUFACTURER_ID ==7 ? 12 : 30); 
				$cmn_vw["na"]["desc"][] = $tempStr;
				$cmn_vw["na"]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
				
				$tempArr = getProductUrl($val['product_id'],$val['product_price_id'],$val['product_alias'],$val['category_id']);
				$cmn_vw["na"]["href"][] = $tempArr["href"];
				$cmn_vw["na"]["param"][] = $tempArr["param"];
			}
		}
		
		//fashion and lifestyle
		$cmn_vw['fl']['title'] = "Fashion and Lifestyle";
		$cmn_vw["fl"]["label"] = $cmn_vw["fl"]["desc"] = $cmn_vw["fl"]["image"] = array();
		$cmn_vw["fl"]["href"] = $cmn_vw["fl"]["param"] = array();
		
		$naArr = $CI->jew->getProducts( staticCategoryIDs( "fashion" ) );
		
		if( !isEmptyArr( $naArr["data"]["result_array"] ) )
		{
			foreach($naArr["data"]["result_array"] as $key=>$val)
			{
				/**
				 * only allow Products for set rest_dis Config file
				 */
				if( $key > getSysConfig("rest_dis") )
				{
					break;
				}
		
				//product image folder
				// 				$imagefolder = getProdImageFolder( $val['product_generated_code'], $val['product_price_id'], $val["product_sku"] );
				// 				$product_images = fetchProductImages( $imagefolder );			//images for particular selection
				$product_images = front_end_hlp_getProductImages($val['product_generated_code'], $val['product_price_id'],
						$val["product_sku"], $val['product_generated_code_info']);
		
		
				$tempStr =  "product_discount=".$val['product_discount']."&".
						"product_price_calculated_price=".lp( $val['product_price_calculated_price'] )."&".
						"product_discounted_price=".lp( $val['product_discounted_price'] )."&";
		
				$cmn_vw["fl"]["label"][] = char_limit($val['product_name'],MANUFACTURER_ID ==7 ? 12 : 30);
				$cmn_vw["fl"]["desc"][] = $tempStr;
				$cmn_vw["fl"]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
		
				$tempArr = getProductUrl($val['product_id'],$val['product_price_id'],$val['product_alias'],$val['category_id']);
				$cmn_vw["fl"]["href"][] = $tempArr["href"];
				$cmn_vw["fl"]["param"][] = $tempArr["param"];
			}
		}
		
		/**
		 * Commented on 01-03-2016
		 */
		// deal of the day
// 		$cmn_vw['dd']['title'] = "Deal of the Day";
// 		$cmn_vw["dd"]["label"] = $cmn_vw["dd"]["desc"] = $cmn_vw["dd"]["image"] = array();
// 		$cmn_vw["dd"]["href"] = $cmn_vw["dd"]["param"] = array();
		
// 		$naArr = $CI->jew->getProducts( staticCategoryIDs( "deal-of-the-day" ) );
		
// 		if( !isEmptyArr( $naArr["data"]["result_array"] ) )
// 		{
// 			foreach($naArr["data"]["result_array"] as $key=>$val)
// 			{
// 				/**
// 				 * only allow Products for set rest_dis Config file
// 				 */
// 				if( $key > getSysConfig("rest_dis") )
// 				{
// 					break;
// 				}
		
// 				//product image folder
// 				// 				$imagefolder = getProdImageFolder( $val['product_generated_code'], $val['product_price_id'], $val["product_sku"] );
// 				// 				$product_images = fetchProductImages( $imagefolder );			//images for particular selection
// 				$product_images = front_end_hlp_getProductImages($val['product_generated_code'], $val['product_price_id'],
// 						$val["product_sku"], $val['product_generated_code_info']);
		
		
// 				$tempStr =  "product_discount=".$val['product_discount']."&".
// 						"product_price_calculated_price=".lp( $val['product_price_calculated_price'] )."&".
// 						"product_discounted_price=".lp( $val['product_discounted_price'] )."&";
		
// 				$cmn_vw["dd"]["label"][] = char_limit($val['product_name'],MANUFACTURER_ID ==7 ? 12 : 30);
// 				$cmn_vw["dd"]["desc"][] = $tempStr;
// 				$cmn_vw["dd"]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
		
// 				$tempArr = getProductUrl($val['product_id'],$val['product_price_id'],$val['product_alias'],$val['category_id']);
// 				$cmn_vw["dd"]["href"][] = $tempArr["href"];
// 				$cmn_vw["dd"]["param"][] = $tempArr["param"];
// 			}
// 		}
		
		/**
		 * Load Dynamic API Categories
		 */
		
		// configuration table to getarray for product category id. 
		$getConfigValue = getField("config_value", "configuration", "config_key", "HOME_PRODUCT_CATEGORY", false);
		
		// convert string to array get configuration table value.
		$getPCID = explode("|",$getConfigValue);
		
		$value = "";
		
		foreach ( $getPCID as $cid=>$product_id )
		{
			if( $cid <= 2 )
			{
				$getProductCategory = exeQuery( "SELECT category_name, category_alias FROM product_categories 
												 WHERE category_id = ".$product_id." 
												 AND category_status = 0" );

				// home and decor
				if( $cid == 0 ) 
				{
					$value = "hd"; 
				}
				
				// Electronics
				if( $cid == 1 ) 
				{
					$value = "ec"; 
				}
				
				// Deal of the day
				if( $cid == 2 ) 
				{ 
					$value = "dd"; 
				}
				
				if( !empty( $getProductCategory['category_name'] ) )
				{
					$cmn_vw[$value]['title'] = $getProductCategory['category_name'];
					$cmn_vw[$value]["label"] = $cmn_vw[$value]["desc"] = $cmn_vw[$value]["image"] = array();
					$cmn_vw[$value]["href"] = $cmn_vw[$value]["param"] = array();
					
					$naArr = $CI->jew->getProducts( $product_id );//staticCategoryIDs( $getProductCategory["category_alias"] )
					
					if( !isEmptyArr( $naArr["data"]["result_array"] ) )
					{
						foreach($naArr["data"]["result_array"] as $key=>$val)
						{
							/**
							 * only allow Products for set rest_dis (rest item display) Config file
							 */
							if( $key > getSysConfig("rest_dis") )
							{
								break;
							}
					
							//product image folder
							// 				$imagefolder = getProdImageFolder( $val['product_generated_code'], $val['product_price_id'], $val["product_sku"] );
							// 				$product_images = fetchProductImages( $imagefolder );			//images for particular selection
							$product_images = front_end_hlp_getProductImages($val['product_generated_code'], $val['product_price_id'],
									$val["product_sku"], $val['product_generated_code_info']);
					
					
							$tempStr =  "product_discount=".$val['product_discount']."&".
									"product_price_calculated_price=".lp( $val['product_price_calculated_price'] )."&".
									"product_discounted_price=".lp( $val['product_discounted_price'] )."&";
					
							$cmn_vw[$value]["label"][] = char_limit($val['product_name'],MANUFACTURER_ID ==7 ? 12 : 30);
							$cmn_vw[$value]["desc"][] = $tempStr;
							$cmn_vw[$value]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
					
							$tempArr = getProductUrl($val['product_id'],$val['product_price_id'],$val['product_alias'],$val['category_id']);
							$cmn_vw[$value]["href"][] = $tempArr["href"];
							$cmn_vw[$value]["param"][] = $tempArr["param"];
						}
					}
				}
				else 
				{
					$cmn_vw[$value]['title'] = "";
					$cmn_vw[$value]["label"] = $cmn_vw[$value]["desc"] = $cmn_vw[$value]["image"] = array();
					$cmn_vw[$value]["href"] = $cmn_vw[$value]["param"] = array();
				}
			}
		}
		
		/**
		 * Commented on 01-03-2016
		 */
		// home and decor -> Sarees
// 		$cmn_vw['hd']['title'] = "Sarees";
// 		$cmn_vw["hd"]["label"] = $cmn_vw["hd"]["desc"] = $cmn_vw["hd"]["image"] = array();
// 		$cmn_vw["hd"]["href"] = $cmn_vw["hd"]["param"] = array();
		
// 		$naArr = $CI->jew->getProducts( staticCategoryIDs( "sarees" ) );
		
// 		if( !isEmptyArr( $naArr["data"]["result_array"] ) )
// 		{
// 			foreach($naArr["data"]["result_array"] as $key=>$val)
// 			{
// 				/**
// 				 * only allow Products for set rest_dis Config file
// 				 */
// 				if( $key > getSysConfig("rest_dis") )
// 				{
// 					break;
// 				}
		
// 				//product image folder
// 				// 				$imagefolder = getProdImageFolder( $val['product_generated_code'], $val['product_price_id'], $val["product_sku"] );
// 				// 				$product_images = fetchProductImages( $imagefolder );			//images for particular selection
// 				$product_images = front_end_hlp_getProductImages($val['product_generated_code'], $val['product_price_id'],
// 						$val["product_sku"], $val['product_generated_code_info']);
		
		
// 				$tempStr =  "product_discount=".$val['product_discount']."&".
// 						"product_price_calculated_price=".lp( $val['product_price_calculated_price'] )."&".
// 						"product_discounted_price=".lp( $val['product_discounted_price'] )."&";
		
// 				$cmn_vw["hd"]["label"][] = char_limit($val['product_name'],MANUFACTURER_ID ==7 ? 12 : 30);
// 				$cmn_vw["hd"]["desc"][] = $tempStr;
// 				$cmn_vw["hd"]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
		
// 				$tempArr = getProductUrl($val['product_id'],$val['product_price_id'],$val['product_alias'],$val['category_id']);
// 				$cmn_vw["hd"]["href"][] = $tempArr["href"];
// 				$cmn_vw["hd"]["param"][] = $tempArr["param"];
// 			}
// 		}
		
		// Electronics -> Leggings
// 		$cmn_vw['ec']['title'] = "Leggings";
// 		$cmn_vw["ec"]["label"] = $cmn_vw["ec"]["desc"] = $cmn_vw["ec"]["image"] = array();
// 		$cmn_vw["ec"]["href"] = $cmn_vw["ec"]["param"] = array();
		
// 		$naArr = $CI->jew->getProducts( staticCategoryIDs( "leggings" ) );
		
// 		if( !isEmptyArr( $naArr["data"]["result_array"] ) )
// 		{
// 			foreach($naArr["data"]["result_array"] as $key=>$val)
// 			{
// 				/**
// 				 * only allow Products for set rest_dis Config file
// 				 */
// 				if( $key > getSysConfig("rest_dis") )
// 				{
// 					break;
// 				}
		
// 				//product image folder
// 				// 				$imagefolder = getProdImageFolder( $val['product_generated_code'], $val['product_price_id'], $val["product_sku"] );
// 				// 				$product_images = fetchProductImages( $imagefolder );			//images for particular selection
// 				$product_images = front_end_hlp_getProductImages($val['product_generated_code'], $val['product_price_id'],
// 						$val["product_sku"], $val['product_generated_code_info']);
		
		
// 				$tempStr =  "product_discount=".$val['product_discount']."&".
// 						"product_price_calculated_price=".lp( $val['product_price_calculated_price'] )."&".
// 						"product_discounted_price=".lp( $val['product_discounted_price'] )."&";
		
// 				$cmn_vw["ec"]["label"][] = char_limit($val['product_name'],MANUFACTURER_ID ==7 ? 12 : 30);
// 				$cmn_vw["ec"]["desc"][] = $tempStr;
// 				$cmn_vw["ec"]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
		
// 				$tempArr = getProductUrl($val['product_id'],$val['product_price_id'],$val['product_alias'],$val['category_id']);
// 				$cmn_vw["ec"]["href"][] = $tempArr["href"];
// 				$cmn_vw["ec"]["param"][] = $tempArr["param"];
// 			}
// 		}
		
		//featured products
		$fpArr = $CI->jew->getProducts( staticCategoryIDs( "featured-products" ) ); 
		cmn_vw_productListJSONObj( $fpArr["data"]["result_array"], $cmn_vw, 3, $fpArr["data"]["Count"] ); 
	}
	else 
	{
		//not in use so far, should be used soon
	}
	
	return $cmn_vw;
}

/**
 * 
 */
function cmn_vw_productListing( $is_search, $is_wild_search=false, $call_no=0, $cz="", $filter_page="filter" )
{
	$CI =& get_instance();
	$CI->load->helper("search");
	$cmn_vw = array();
	
	
	if( is_restClient() )
	{
		$CI->load->model('mdl_products','jew');
		
		$tmpArr = he_s_productListing($is_search, $is_wild_search, $call_no);
		$data = $tmpArr["data"];
		$tmpArr = null;
		
		//filter
		cmn_vw_prepareFilterData($data["filter_data"], $data["searchf"], $cmn_vw); 
		
		//product list
		cmn_vw_productListJSONObj( $data["listArr"], $cmn_vw, 0, $data["total_records"], $cz, $filter_page );
		
		return $cmn_vw; 
	}
	else 
	{
		$tmpArr = he_s_productListing($is_search, $is_wild_search, $call_no);
		$data = $tmpArr["data"];
		$res = $tmpArr["res"];
		$is_no_result = $tmpArr["is_no_result"];
		$tmpArr = null;
		
		
		$data['pageName'] = 'products_list';
		
		//if serach mode then apply meta tag as per search criteria generated SEO freindly search code
		if( $is_search )
		{
			if( $res['search_code'] == "" || $res['search_code'] == " Products " )
			{
				if( !$is_no_result )
				{
					$res['search_code'] = " Products...";
				}
				else
				{
					$res['search_code'] = " No Products... matching your criteria.";
				}
			}
		
			$search_tag = str_replace( array( "+", "-")," ",$res['search_code']);
		
			/**
			 * Old category specific meta creation had been deprecated
			 * now generate only search specific meta tags
			*/
			$resMeta =  createMeta( $search_tag );
			$data['category_name'] = $resMeta['category_name']; //h tag
			$data['custom_page_title'] = $resMeta['custom_page_title'];
			$data['meta_description'] = $resMeta['meta_description'];
			$data['meta_keyword'] = $resMeta['meta_keyword'];
		
				
			$data['search_code'] = $res['search_code'];
			$data['search_tagArr'] = $res['search_tagArr'];
			$data['search_url_tagArr'] = $res['search_url_tagArr'];
		}
		
		/**
		 * only for backward compatibility for get filter
		 */
		if( empty($data['searchf']) )
		{
			$data['searchf'] = $CI->input->get();
		}
		
		$CI->load->view( 'site-layout', $data);
	}
	
}

/**
 *
 */
function cmn_vw_scrollPagination( &$__this )
{
	$CI =& get_instance();
	$CI->load->helper("search");
	$cmn_vw = array();

	$page = $__this->input->get('page');
	$__this->cz = $__this->input->get('cz');
	$data = $__this->jew->scrollPagination($page);

	if( !empty($data) )
	{
		//fetch result array
		$data['listArr'] = $data['data']['result_array'];
			
			
		/**
		 * randomize the result if sort by is not used
		 * @since 26-05-2015 turned off 
		 */
// 		if($page == 'filter')
// 			$is_sort_by_used = $__this->session->userdata('sort_by');
// 		else if($page == 'ready_to_ship')
// 			$is_sort_by_used = $__this->session->userdata('sort_by_ready_to_ship');
// 		else if($page == 'valentine_gifts')
// 			$is_sort_by_used = $__this->session->userdata('sort_by_valentine_gifts');
		
// 		if( empty( $is_sort_by_used ) )
// 		{
// 			shuffle( $data['listArr'] );
// 		}
			
		if( is_restClient() )
		{
			cmn_vw_scroll_pagination_listJSONObj($data['listArr'], $cmn_vw);
			return $cmn_vw;
		}
		else 
		{
			if($page == 'filter')
			{
				//echo $__this->load->view('scroll_pagination_list', $data);
				foreach ($data as $k=>$val)
				{
					${$k} = $val;
				}
				require BASE_DIR . 'application/views/scroll_pagination_list.php';
			}
			else if($page == 'ready_to_ship')
			{	echo $__this->load->view('scroll_pagination_list_ready_to_ship', $data);	}
			else if($page == 'solitaire')
			{	echo $__this->load->view('scroll_pagination_list_solitaire', $data);	}
			else if($page == 'valentine_gifts')
			{	echo $__this->load->view('scroll_pagination_list_valentine_gifts', $data);	}
			exit(1);
		}	
	}
	else
	{
		if( is_restClient() )
		{
			cmn_vw_scroll_pagination_listJSONObj( "", $cmn_vw);
			return $cmn_vw; 
		}
		else 
		{
			echo '';
			exit(1);
		}
	}

}


/*****************************************************************************************************/
/**
 * product detail page functions
 */

/**
 *
 */
function cmn_vw_showProductsDetails( $menu_id=0 ) 
{
    if( isIntranetIp() )
    {
        echo "Here 1, ";
    }
    
	$CI =& get_instance();
	$cmn_vw = array();

	if( is_restClient() )
	{
		if( $CI->input->get('event') == 'solitaire' )
		{
			//un-comment it if solitaire support is required
			//redirect('solitaires/pickDesign?pid='.$menu_id);
		}
		
		$cz_suffix = ''; $ring_size_id='';	//currently on page load no ring_size is selected and by default diamond price is displayed not cz price
		$pageToken = pageToken();
		
		/**
		 *
		 */
		$product_price_id = (int) $CI->input->get("id");
		
		$data = showProductsDetails($product_price_id, false, false, true, $pageToken, $ring_size_id, $cz_suffix);
		
		if( isIntranetIp() )
		{
		    pr( $data );die;
		}
		//update view count on each page load
		$CI->db->query("UPDATE product SET product_view_buy=product_view_buy+1 WHERE product_id=".$data['product_id']."");
		
		//record if page load from any proposed campaign
		recordCampaignLandingPage();
		
		//
		unset( $data["view_var"] );
		$cmn_vw = $data; 
		unset( $data ); 
		
		//
		if( isEmptyArr( $cmn_vw["product_images"] ) )
		{
			$cmn_vw["product_images"] = array( load_image("") ); 
		}
		else 
		{
			foreach ($cmn_vw["product_images"] as $k=>$ar)
			{
				$cmn_vw["product_images"][$k] = load_image($ar);
			}
		}
		
		//
		$cmn_vw["isGroceryInventory"] = hewr_isGroceryInventoryCheckWithId( $cmn_vw["inventory_type_id"] ); 

		//price detail
		$cmn_vw["product_price_calculated_price"] = lp( $cmn_vw["product_price_calculated_price"] );//Market Price
		$cmn_vw["product_discounted_price"] = lp( $cmn_vw["product_discounted_price"] );//Our Price
		$cmn_vw["product_discount"] = $cmn_vw["product_discount"];
		$cmn_vw["product_discounted_price_tot"] = lp( $cmn_vw["product_discounted_price_tot"] );//Total
		
		/**
		 *
		 */
		$cmn_vw["aTitleA"] = array(); 
		$cmn_vw["aCntA"] = array();
		$cmn_vw["attributesO"] = array();
		$cmn_vw["attributesO"]["isJO"] = "";	//tell it is JSON Object to prevent empty JSONObject error at client side
		
		$cnt = 0; 
		foreach ($cmn_vw["codeArr"] as $k=>$ar):
			if( $k >= 2 ):
				$tempA = explode(":", $ar);
				
				/**
				 * here $k stands for product_stone_number,
				 * minus it by 2 to reflect stone number in sequence.
				 */
				$k -= 2;
				
				if( $tempA[1] === "JW_CS" || $tempA[1] === "JW_SS1" || $tempA[1] === "JW_SS2" || $tempA[1] === "JW_SSS" ):
					$type = detailDiamondType( $k );
					$res = detailDiamonds( $cmn_vw["product_id"], $type, $k, "C" );
					if( !isEmptyArr($res) && sizeof($res) > 1 ):
						//
						$cmn_vw["aTitleA"][] = $tempA[2]; 
						$cmn_vw["aCntA"][] = $cnt; 
						$cmn_vw["attributesO"][$cnt]["label"] = array(); 
						$cmn_vw["attributesO"][$cnt]["param"] = array(); 
						
						foreach($res as $k=>$ar):
							$tempStr = "";
							if( $ar['diamond_price_id'] == $tempA[3] )
							{
								$tempStr = "is_active=1&";
							}

							//
							$tempStr = $tempStr . "type=".$type."&pid=".$ar['diamond_price_id'];

							$cmn_vw["attributesO"][$cnt]["label"][] = $ar['diamond_price_name'];
							$cmn_vw["attributesO"][$cnt]["param"][] = $tempStr;
						endforeach;
					endif; 
				elseif( $tempA[1] === "SEL" || $tempA[1] === "CHK" || $tempA[1] === "RDO" ):
					$type = detailDiamondType( $k );
					$res = detailDiamonds( $cmn_vw["product_id"], $type, $k, "A" );
					if( !isEmptyArr($res) && sizeof($res) > 1 ):
						//
						$cmn_vw["aTitleA"][] = $tempA[2];
						$cmn_vw["aCntA"][] = $cnt;
						$cmn_vw["attributesO"][$cnt]["label"] = array();
						$cmn_vw["attributesO"][$cnt]["param"] = array();
														
														
						foreach($res as $k=>$ar):
							$tempStr = "";
							if( $ar["p".$type."_diamond_shape_id"] == $tempA[3] )
							{
								$tempStr = "is_active=1&";
							}
							
							//
							$tempStr = $tempStr . "type=".$type."&pid=".$ar["p".$type."_diamond_shape_id"];
							
							$cmn_vw["attributesO"][$cnt]["label"][] = $ar['pa_value'];
							$cmn_vw["attributesO"][$cnt]["param"][] = $tempStr;
						endforeach;
					endif;
				elseif( $tempA[1] === "JW_MTL" ):
					$type = detailDiamondType( $k );
					if( $type === "dyn" )
					{
						$type = "ss".$k;
					}
					
					$res = detailMetals( $cmn_vw["product_id"] );
					/**
					 * JW_MTL: metal Component yet need to be developed, as per design selected by client  
					 */
				//elseif( $tempA[1] === "TXT" ):
					//nothing to do
				endif;
				
				$cnt++;
			endif;
		endforeach; 
		

			
		//
		$cmn_vw["isProductOutOfStock"] = ( isProductOutOfStock( $cmn_vw["product_id"], $cmn_vw["inventory_type_id"] ) ? 1 : 0 );
		if( $cmn_vw["isProductOutOfStock"] )
		{
			$cmn_vw["stockMsg"] = getLangMsg("soldout");
		} 
		else 
		{
			$cmn_vw["stockMsg"] = getLangMsg("instock");
		}
		
		/**
		 * product specifications
		 */
		$cmn_vw["sIdA"] = array();		//specification attribute id
		$cmn_vw["sTitleA"] = array();	//specification attribute titles
		$cmn_vw["sDescA"] = array();	//specification attribute detail/description  
		foreach ($cmn_vw["codeArr"] as $k=>$ar):
			if( $k >= 2 ):
				$tempA = explode(":", $ar);
					
				/**
				 * here $k stands for product_stone_number,
				 * minus it by 2 to reflect stone number in sequence.
				 */
				$k -= 2;
					
				$type = detailDiamondType( $k );
				if( $type === "dyn" )
				{
					$type = "ss".$k;
				}
			
				if( $tempA[1] === "JW_CS" || $tempA[1] === "JW_SS1" || $tempA[1] === "JW_SS2" || $tempA[1] === "JW_SSS" ):
			  		$cmn_vw["sIdA"][] = "diamond_type_name_cs_alias"; 
					$cmn_vw["sTitleA"][] = $tempA[2]." ".getLangMsg("type"); 
					$cmn_vw["sDescA"][] = $cmn_vw["diamond_type_name_".$type."_alias"]; 
		
					$cmn_vw["sIdA"][] = "diamond_shape_name_cs";
					$cmn_vw["sTitleA"][] = $tempA[2]." ".getLangMsg("shape");
					$cmn_vw["sDescA"][] = $cmn_vw["diamond_shape_name_".$type];
		
					if($diamond_type_key_cs=='DIAMOND'):
						$cmn_vw["sIdA"][] = "diamond_purity_name_cs";
						$cmn_vw["sTitleA"][] = $tempA[2]." ".getLangMsg("purity");
						$cmn_vw["sDescA"][] =  $cmn_vw["diamond_purity_name_".$type];
		
						$cmn_vw["sIdA"][] = "diamond_color_name_cs";
						$cmn_vw["sTitleA"][] = $tempA[2]." ".getLangMsg("clr");
						$cmn_vw["sDescA"][] =  $cmn_vw["diamond_color_name_".$type];
					endif;
		
					$no_of_pcs = ( $type === "cs" ? "product_center_stone_total" : "product_side_stone".$k."_total" );
					$tot_weight = ( $type === "cs" ? "product_center_stone_weight" : "product_side_stone".$k."_weight" );
						
					$cmn_vw["sIdA"][] = $no_of_pcs;
					$cmn_vw["sTitleA"][] = $tempA[2]." No of pcs";
					$cmn_vw["sDescA"][] =  $cmn_vw[$no_of_pcs];
						
					$cmn_vw["sIdA"][] = $tot_weight;
					$cmn_vw["sTitleA"][] = $tempA[2]." Total Weight";
					$cmn_vw["sDescA"][] =  $cmn_vw[$tot_weight];
																	
				elseif( $tempA[1] === "SEL" || $tempA[1] === "CHK" || $tempA[1] === "RDO" ):
					$cmn_vw["sIdA"][] = "pa_value_".$type;
					$cmn_vw["sTitleA"][] = $tempA[2];
					$cmn_vw["sDescA"][] = ( !empty( $cmn_vw["pa_value_".$type] ) ? $cmn_vw["pa_value_".$type] : "-" );
														  	
				elseif( $tempA[1] === "JW_MTL" ):
					$cmn_vw["sIdA"][] = "metal_name"; 
					$cmn_vw["sTitleA"][] = $tempA[2]." ".getLangMsg("type"); 
					$cmn_vw["sDescA"][] = $cmn_vw[metal_type_name]; 
					  	
					$cmn_vw["sIdA"][] = "metal_purity_name";
					$cmn_vw["sTitleA"][] = $tempA[2]." ".getLangMsg("purity");
					$cmn_vw["sDescA"][] = $cmn_vw[metal_purity_name];
					  	
					$cmn_vw["sIdA"][] = "metal_color_name";
					$cmn_vw["sTitleA"][] = $tempA[2]." ".getLangMsg("clr");
					$cmn_vw["sDescA"][] = $cmn_vw[metal_color_name];
					  	
					$cmn_vw["sIdA"][] = "product_metal_weight";
					$cmn_vw["sTitleA"][] = $tempA[2]." ".getLangMsg("weight");
					$cmn_vw["sDescA"][] = $cmn_vw[product_metal_weight];
	
				elseif( $tempA[1] === "TXT" ):
					$txt = ( $type === "cs" ? "product_center_stone_size" : "product_side_stone".$k."_size" );
					  
					$cmn_vw["sIdA"][] = $txt;
					$cmn_vw["sTitleA"][] = $tempA[2];
					$cmn_vw["sDescA"][] = ( !empty( $cmn_vw[$txt] ) ? $cmn_vw[$txt] : "-" );
																  	
				endif;
			endif;
		endforeach; 
				
		
		//unset not necessary variables to off load REST response
		unset($cmn_vw["codeArr"]);		                                            
		                                            
		//product review
		$cmn_vw["pr"] = array(); 
		$cmn_vw["pr"]["label"] = $cmn_vw["pr"]["desc"] = $cmn_vw["pr"]["image"] = array();
		$cmn_vw["pr"]["href"] = $cmn_vw["pr"]["param"] = array();
		
		$res = executeQuery( " SELECT * FROM product_review where product_id=".$cmn_vw["product_id"]." AND product_review_status = 0 " ); 
		$cmn_vw["pr"]["nr_msg"] = ""; 
		
		if(!isEmptyArr($res)):
			$cmn_vw["pr"]["cnt"] = count($res);
			foreach ($res as $k=>$ar):
				if($ar['user_type'] == "A"):
					$cmn_vw["pr"]["label"][] = exeQuery( " SELECT admin_user_firstname FROM admin_user where admin_user_id=".$ar['customer_id']." LIMIT 1 ", true, "admin_user_firstname" ); 
				else:
				 	$cmn_vw["pr"]["label"][] = exeQuery( " SELECT customer_firstname FROM customer where customer_id=".$ar['customer_id']." LIMIT 1 ", true, "customer_firstname" ); 
				endif;
	
				$cmn_vw["pr"]["image"][] = "";
				$cmn_vw["pr"]["param"][] = $ar['product_review_created_date'];
				$cmn_vw["pr"]["href"][] = $ar['product_review_rating']; 
				$cmn_vw["pr"]["desc"][] = $ar['product_review_description'];
			endforeach;	
		else:
			$cmn_vw["pr"]["cnt"] = 0;
			$cmn_vw["pr"]["nr_msg"] = getLangMsg("nry"); 
		endif;
		
		//
		$cmn_vw["is_component_besed_inv"] = ( hewr_isComponentBasedCheckWithId( $cmn_vw["inventory_type_id"] ) ? 1 : 0 );
		$cmn_vw["selected_index"] = $cmn_vw["product_angle_in"]; //specifies image index currently active
		$cmn_vw["pid"] = $pageToken;		 		  //page token
		return $cmn_vw;
	}
	else 
	{
	    if( isIntranetIp() )
	    {
	        echo "Here 2, ";
	    }
		/**
		 * Allow admins to view products even if they are disabled
		 * Added on 28-03-2015
		 */
		$is_status_check = true;
		if( $CI->input->get("is_preview") == 1 )
		{
			$is_status_check = false;
		}

		if( $CI->input->get('event') == 'solitaire')
		{
			//un-comment it if solitaire support is required
			//redirect('solitaires/pickDesign?pid='.$menu_id);
		}
		
		$cz_suffix = ''; $ring_size_id='';	//currently on page load no ring_size is selected and by default diamond price is displayed not cz price
		
		$pageToken = pageToken();
		
		/**
		 * detect if canonicle URL is called
		*/
		if( (int)$menu_id == 0 )
		{
			/**
			 * get priority or otherwise first product price if from product table for product
			 */
			$CI->product_price_id = getPriorityPrPrID( $menu_id );
		}
		else
		{
			$CI->product_price_id = $menu_id;
		}
		
		$data = showProductsDetails($CI->product_price_id, false, false, $is_status_check, $pageToken, $ring_size_id, $cz_suffix);
		
		//update view count on each page load
		$CI->db->query("UPDATE product SET product_view_buy=product_view_buy+1 WHERE product_id=".$data['product_id']."");
		
		//record if page load from any proposed campaign
		recordCampaignLandingPage();
		
		//create page title
		$tabelName = ( MANUFACTURER_ID != 7 ) ? "product_categories_cctld": "product_categories";
		$resTitle = $CI->db->query("SELECT custom_page_title
									FROM product_category_map pcm INNER JOIN ".$tabelName." pc
									ON pc.category_id=pcm.category_id
									WHERE pcm.product_id=".$data['product_id']." LIMIT 2,1")->row_array();
		
		/**
		 * SEO meta pattern
		*/
		//$data['the_prod_name'] = str_replace( "The ", "", $data['product_name']);
		// 		if( isset($data['diamond_color_name_cs']) && !empty($data['diamond_color_name_cs']) && isset($data['diamond_purity_name_cs']) && !empty($data['diamond_purity_name_cs']) )
			// 		{
			// 			//$data['custom_page_title'] = "Buy ".$data['product_name']." in ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name'].
			// 										" With ".$data['diamond_color_name_cs']."-".$data['diamond_purity_name_cs']." Diamonds".
			// 										" | ".@$resTitle['custom_page_title'] ;
		
		// 			//$data['meta_description'] = ".com - Indian Online Diamond Products Shopping Store with Designer Certified Diamond Engagement Rings & Diamond Products with Cheapest - Budget Price for Men & Women - Latest Diamond Rings, Diamond Pendants, Diamond Ear Rings & Gold Products Designs.";
		// 										$data['the_prod_name']." in ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name'].
		// 										" With ".$data['diamond_color_name_cs']."-".$data['diamond_purity_name_cs']." Diamonds";
			
		// 			//$data['meta_keyword'] = $data['the_prod_name'].( !empty($data['meta_keyword']) ? ", ":"" ).$data['meta_keyword'].", ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name']." ".$data['the_prod_name'].", Buy ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name']." ".$data['the_prod_name'].", ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name']." ".$data['the_prod_name']." price in india".", ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name']." ".$data['the_prod_name'].", ".$data['diamond_color_name_cs']."-".$data['diamond_purity_name_cs']." ".$data['the_prod_name'].", Buy ".$data['diamond_color_name_cs']."-".$data['diamond_purity_name_cs']." ".$data['the_prod_name'].", ".$data['diamond_color_name_cs']."-".$data['diamond_purity_name_cs']." ".$data['the_prod_name']." price in india";
		// 		}
		// 		else
			// 		{
			// 			//$data['custom_page_title'] = "Buy ".$data['product_name']." in ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name'].
			// 										" | ".@$resTitle['custom_page_title'] ;
		
		// 			//$data['meta_description'] = $data['the_prod_name']." in ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name'];
			
		// 			//$data['meta_keyword'] = $data['the_prod_name'].( !empty($data['meta_keyword']) ? ", ":"" ).$data['meta_keyword'].", ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name']." ".$data['the_prod_name'].", Buy ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name']." ".$data['the_prod_name'].", ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name']." ".$data['the_prod_name']." price in india".", ".$data['metal_purity_name']."  ".$data['metal_color_name']." ".$data['metal_type_name']." ".$data['the_prod_name'];
		// 		}
		
		// 		if(MANUFACTURER_ID != 7)
			// 		{
			// 			//$data['custom_page_title'] = strReplaceIndToAus(@$data['custom_page_title']);
			// 			//$data['meta_description'] = strReplaceIndToAus(@$data['meta_description']);
			// 			$data['custom_page_title'] = $data['product_name']." ".$data['product_generated_code']." - "; //change on seo meeting 07-02-2015
			// 			$data['meta_description'] = " an online Products store of Australia offers beautiful ".$data['product_name']; //change on seo meeting 07-02-2015
			// 			$data['meta_keyword'] = strReplaceIndToAus(@$data['meta_keyword']);
			// 		}
			// 		else
				// 		{
				// 			$dash = (!empty($resTitle['custom_page_title'])) ? " | ".$resTitle['custom_page_title'] : '';
				// 			$data['custom_page_title'] = "Buy ".$data['product_name'].$dash ;
				// 		}
		/************************* SEO meta pattern end **************************************************************/
		
		
		//view
		
		$data['pageName'] = 'products-details';
		$data['pageToken'] = $pageToken;
		
		$catidArr = explode("|",$data['category_id']);
		$data['prodUrl'] = getProductUrl($data['product_id'], $data['product_price_id'], $data['product_alias'], $catidArr[0]);
		
		//set canonical url
		$data['canonical'] = getCanonicalUrl( $data['prodUrl'] );
		
		
// 		if( isset($_GET["is_test"]) )
// 		{
// 			print_r($data); die;
// 		}
		
		$CI->load->view('site-layout',$data);
	}
}

/**
 * 
 */
function cmn_vw_fetchProductDetailsAjax()
{
	$CI =& get_instance();
	$cmn_vw = array();
	
	if( is_restClient() )
	{
		$data = $CI->input->post();
		
		//change Note: pageToken is added in functionality to tackel uniqueness issue of same session pages
		$pid = $data['pid'];		//pageToken
		$ring_size_id = $data['ring_size_id'];
		
		$codeArr = parseProductcode( $CI->session->userdata('codeArr_'.$pid) );
		$cz_suffix = $CI->session->userdata('cz_suffix_'.$pid);	//initialize cz_suffix from session
		
		/**
		 *
		*/
		if($data['type'] == "cs")
		{
			//transform product to diamond category
			$cz_suffix = '';
		
			$codeArr[2] = $data['id'];
		}
		else if($data['type'] == "ss1")
		{
			//transform product to diamond category
			$cz_suffix = '';
		
			$codeArr[3] = $data['id'];
		}
		else if($data['type'] == "ss2")
		{
			//transform product to diamond category
			$cz_suffix = '';
		
			$codeArr[4] = $data['id'];
		}
		else if($data['type'] == "cz")
		{
			//transform product to cz category
			$cz_suffix = '_cz';
		}
		else	//change: 2/12/2013 now check for more stones
		{
			$size = sizeof( $codeArr );
			if( $size > 5 )
			{
				//transform product to diamond category
				$cz_suffix = '';
		
				for( $i=5; $i<$size; $i++ )
				{
					if( $data['type'] == "ss".($i-2) )
					{
						$codeArr[$i] = $data['id'];
					}
				}
			}
		}
		
		/**
		 * Change made On 09 May 2015
		 */
		//old
		//$res = $CI->db->query("SELECT product_price_id FROM product_price WHERE product_price_status=0 AND product_generated_code='".genProdcodeFromArr($codeArr)."'")->row_array();
		//new
		$res = null;
		if( MANUFACTURER_ID == 7 )
		{
			$res = $CI->db->query("SELECT product_price_id FROM product_price WHERE product_price_status=0 AND product_generated_code='".genProdcodeFromArr($codeArr)."'")->row_array();
		}
		else 
		{
			$res = $CI->db->query("SELECT product_price_id FROM product_price_cctld WHERE manufacturer_id=".MANUFACTURER_ID." AND product_price_status=0 AND product_generated_code='".genProdcodeFromArr($codeArr)."'")->row_array();
		}


		if( !isEmptyArr($res) )
		{
			$CI->product_price_id = $res['product_price_id'];
		}
		else
		{
			$cmn_vw["type"] = "warning"; 
			$cmn_vw["msg"] = "Sorry! Session is expired please refresh the page."; 
			return $cmn_vw;
		}
		
		
		$data = showProductsDetails( $CI->product_price_id, true, false, true, $pid, $ring_size_id, $cz_suffix );

		/**
		 * return only required response to off load network and resource use on REST Apps
		 */
		$cmn_vw["vv"] = $data["view_var"];
		
		/**
		 * due to folder structure is made dynamic based on color attrubute applicable now it is required always return images in response. 
		 */
// 		if( hewr_isComponentBasedCheckWithId( $data["inventory_type_id"] ) )
// 		{			 
// 			$cmn_vw["product_images"] = $data["product_images"];
// 		}
		$cmn_vw["product_images"] = $data["product_images"];
		if( isEmptyArr( $cmn_vw["product_images"] ) )
		{
			$cmn_vw["product_images"] = array( load_image("") );
		}
		else
		{
			foreach ($cmn_vw["product_images"] as $k=>$ar)
			{
				$cmn_vw["product_images"][$k] = load_image($ar);
			}
		}
		
		
		return $cmn_vw;
	}
	else 
	{

		$data = $CI->input->post();
		
		//change Note: pageToken is added in functionality to tackel uniqueness issue of same session pages
		$pid = $data['pid'];		//pageToken
		$ring_size_id = $data['ring_size_id'];
		
		$codeArr = parseProductcode( $CI->session->userdata('codeArr_'.$pid) );
		$cz_suffix = $CI->session->userdata('cz_suffix_'.$pid);	//initialize cz_suffix from session
		
		/**
		 *
		*/
		if($data['type'] == "cs")
		{
			//transform product to diamond category
			$cz_suffix = '';
		
			$codeArr[2] = $data['id'];
		}
		else if($data['type'] == "ss1")
		{
			//transform product to diamond category
			$cz_suffix = '';
		
			$codeArr[3] = $data['id'];
		}
		else if($data['type'] == "ss2")
		{
			//transform product to diamond category
			$cz_suffix = '';
		
			$codeArr[4] = $data['id'];
		}
		else if($data['type'] == "cz")
		{
			//transform product to cz category
			$cz_suffix = '_cz';
		}
		else	//change: 2/12/2013 now check for more stones
		{
			$size = sizeof( $codeArr );
			if( $size > 5 )
			{
				//transform product to diamond category
				$cz_suffix = '';
		
				for( $i=5; $i<$size; $i++ )
				{
					if( $data['type'] == "ss".($i-2) )
					{
						$codeArr[$i] = $data['id'];
					}
				}
			}
		}
		
		/**
		 * Change made On 09 May 2015
		 */
		//old 
		//$res = $CI->db->query("SELECT product_price_id FROM product_price WHERE product_price_status=0 AND product_generated_code='".genProdcodeFromArr($codeArr)."'")->row_array();
		//new
		$res = null;
		if( MANUFACTURER_ID == 7 )
		{
			$res = $CI->db->query("SELECT product_price_id FROM product_price WHERE product_price_status=0 AND product_generated_code='".genProdcodeFromArr($codeArr)."'")->row_array();
		}
		else
		{
			$res = $CI->db->query("SELECT product_price_id FROM product_price_cctld WHERE manufacturer_id=".MANUFACTURER_ID." AND product_price_status=0 AND product_generated_code='".genProdcodeFromArr($codeArr)."'")->row_array();
		}
		
		if(!empty($res))
			$CI->product_price_id = $res['product_price_id'];
		else
		{
			echo json_encode( array('type'=>'warning','msg'=>'Sorry! Session is expired please refresh the page.') );
			return;
		}
		
		echo json_encode( showProductsDetails( $CI->product_price_id, true, false, true, $pid, $ring_size_id, $cz_suffix ) );
	}
}

/******************************* product detail page functions end ***********************************/

/*************************************** cart wishlist functions *************************************/
/**
 * cart wishlist functions
 */

/**
 * 
 */
function cmn_vw_cartAdd( $product_price_id, $is_ajax, $cartArr, $customer_id )
{
	$CI =& get_instance(); 
	$cmn_vw = array(); 
	if( is_restClient() )
	{
		/**
		 * to tell REST client that cart wish requires update
		 */
		$CI->session->set_userdata( array( "is_CW" => 1 ) );
	}
	
	if( is_restClient() )
	{

		$pid = $CI->input->post('pid');
		$token = $CI->input->post('token');	//page token : token will identify session and will usefull for getting product_price_id
		$ring_size = $CI->input->post('ring_size');	//ring size applicable to only ring products
			
		//change25/13/2013*
		$type = $CI->input->post('type');
			
		/**
		 * qty added on 31-03-2105
		*/
		$qty = 1;
		if( $CI->input->post('qty') !== FALSE )
		{
			$qty = $CI->input->post('qty');
		}
			
		//apply product price id directly from listing page if not zero if function called form details page then it will be always zero
		if($pid!=0)
		{
			$product_price_id = $pid;
		}
		else if(!empty($token))
		{
			if( $type == 'sol')	//for solitaires category pageToken will contain solitaire event session prefix
			{
				$product_price_id = $CI->session->userdata($token.'pick_design_id')."=".$CI->session->userdata($token.'choose_diamond_id');
			}
			else
			{
				$product_price_id = $CI->session->userdata('product_price_id_'.$token);
				$type = $CI->session->userdata('cz_suffix_'.$token);
				if( strlen($type) > 0) { $type = substr($type, 1); }
			}
		}
		
		if($product_price_id!=0)
		{
			$res = array();
			$is_add = true;
			if( empty( $type ) || $type == 'prod' )
			{
				$res = executeQuery( "SELECT product_name, p.product_id, p.inventory_type_id FROM product_price pp
										  INNER JOIN product p ON p.product_id=pp.product_id
										  WHERE pp.product_price_id=".$product_price_id." LIMIT 1" );
				if( CLIENT == "Stationery" )
				{
					if( hewr_isGroceryInventoryCheckWithId( $res[0]["inventory_type_id"] ) )
					{
						$is_add = false;
					}
				}
			}
		
			updCartDatabase($product_price_id, $qty, $is_add, false, $cartArr, $customer_id, $ring_size, '', $type);
		
			if( empty( $type ) || $type == 'prod' )
			{
				/**
				 * On 15-04-2015 moved out of if and put above, so that code can read it before hand and apply another logic also
				 */
				if(!empty($res))
				{
					//$link= getProductUrl($res[0]['product_id'],$product_price_id);
					$cmn_vw["type"] = "success"; 
					$cmn_vw["msg"] = "Success: You have added ".pgTitle($res[0]['product_name'])." to your shopping cart!"; 
				} 
			}
			else if( $type == 'cz' )
			{
				$res = executeQuery( "SELECT product_name,p.product_id FROM product_price pp INNER JOIN product p ON p.product_id=pp.product_id
										WHERE pp.product_price_id=".$product_price_id." LIMIT 1" );
				if(!empty($res))
				{
					//$link= getProductUrl($res[0]['product_id'],$product_price_id);
					$cmn_vw["type"] = "success";
					$cmn_vw["msg"] = "Success: You have added ".pgTitle($res[0]['product_name'])." to your shopping cart!";
				}
			}
			else if( $type == 'sol' )
			{
				$tempArr = explode('=', $product_price_id);
				$product_price_id = (int)$tempArr[0];
				$res = executeQuery( "SELECT product_name,p.product_id FROM product_price pp INNER JOIN product p ON p.product_id=pp.product_id
										WHERE pp.product_price_id=".$product_price_id." LIMIT 1" );
				if(!empty($res))
				{
					//$link= getProductUrl($res[0]['product_id'],$product_price_id);
					$cmn_vw["type"] = "success";
					$cmn_vw["msg"] = "Success: You have added ".pgTitle($res[0]['product_name'])." to your shopping cart!";
				}
			}
			else if( $type == 'dia' )
			{
				$cmn_vw["type"] = "success";
				$cmn_vw["msg"] = "Success: You have added one Diamond to your shopping cart!";
			}
		}
		else 
		{
			$cmn_vw["type"] = "error";
			$cmn_vw["msg"] = "Sorry! Something wrong happen, please try later or contact support.";
		}
		
		return $cmn_vw; 
	}
	else
	{
		if( $is_ajax )
		{
			$pid = $CI->input->post('pid');
			$token = $CI->input->post('token');	//page token : token will identify session and will usefull for getting product_price_id
			$ring_size = $CI->input->post('ring_size');	//ring size applicable to only ring products
			
			//change25/13/2013*
			$type = $CI->input->post('type');
			
			/**
			 * qty added on 31-03-2105
			 */
			$qty = 1;
			if( $CI->input->post('qty') !== FALSE ) 
			{
				$qty = $CI->input->post('qty'); 
			}
			
			//apply product price id directly from listing page if not zero if function called form details page then it will be always zero
			if($pid!=0)						
			{
				$product_price_id = $pid;
			}
			else if(!empty($token))
			{
				if( $type == 'sol')	//for solitaires category pageToken will contain solitaire event session prefix
				{
					$product_price_id = $CI->session->userdata($token.'pick_design_id')."=".$CI->session->userdata($token.'choose_diamond_id');				
				}
				else
				{
					$product_price_id = $CI->session->userdata('product_price_id_'.$token);
					$type = $CI->session->userdata('cz_suffix_'.$token); 
					if( strlen($type) > 0) { $type = substr($type, 1); }
				}
			}
				
			if($product_price_id!=0)
			{
				$res = array();
				$is_add = true; 
				if( empty( $type ) || $type == 'prod' )
				{
					$res = executeQuery( "SELECT product_name, p.product_id, p.inventory_type_id FROM product_price pp 
										  INNER JOIN product p ON p.product_id=pp.product_id
										  WHERE pp.product_price_id=".$product_price_id." LIMIT 1" ); 
					if( CLIENT == "Stationery" )
					{
						if( hewr_isGroceryInventoryCheckWithId( $res[0]["inventory_type_id"] ) )
						{
							$is_add = false; 
						}
					}
				}
				
				updCartDatabase($product_price_id, $qty, $is_add, false, $cartArr, $customer_id, $ring_size, '', $type);

				if( empty( $type ) || $type == 'prod' )
				{
					/**
					 * On 15-04-2015 moved out of if and put above, so that code can read it before hand and apply another logic also  
					 */
// 					$res = executeQuery( "SELECT product_name,p.product_id FROM product_price pp INNER JOIN product p ON p.product_id=pp.product_id 
// 										WHERE pp.product_price_id=".$product_price_id." LIMIT 1" );
					if(!empty($res))
					{
						$link= getProductUrl($res[0]['product_id'],$product_price_id);
						echo json_encode(array('type'=>'success','msg'=>'Success: You have added <a data-ajax="false" href="'.$link.'">'.pgTitle($res[0]['product_name']).'</a> to your <a data-ajax="false" href="'.site_url('cart').'">shopping cart</a>!'));
					}
				}
				else if( $type == 'cz' )
				{
					$res = executeQuery( "SELECT product_name,p.product_id FROM product_price pp INNER JOIN product p ON p.product_id=pp.product_id 
										WHERE pp.product_price_id=".$product_price_id." LIMIT 1" );
					if(!empty($res))
					{
						$link= getProductUrl($res[0]['product_id'],$product_price_id);
						echo json_encode(array('type'=>'success','msg'=>'Success: You have added <a data-ajax="false" href="'.$link.'">'.pgTitle($res[0]['product_name']).'</a> to your <a data-ajax="false" href="'.site_url('cart').'">shopping cart</a>!'));
					}
				}
				else if( $type == 'sol' )
				{
					$tempArr = explode('=', $product_price_id);
					$product_price_id = (int)$tempArr[0];
					$res = executeQuery( "SELECT product_name,p.product_id FROM product_price pp INNER JOIN product p ON p.product_id=pp.product_id 
										WHERE pp.product_price_id=".$product_price_id." LIMIT 1" );
					if(!empty($res))
					{
						$link= getProductUrl($res[0]['product_id'],$product_price_id);
						echo json_encode(array('type'=>'success','msg'=>'Success: You have added <a data-ajax="false" href="'.$link.'">'.pgTitle($res[0]['product_name']).'</a> to your <a  data-ajax="false" href="'.site_url('cart').'">shopping cart</a>!'));
					}
				}
				else if( $type == 'dia' )
				{
					echo json_encode(array('type'=>'success','msg'=>'Success: You have added one <a data-ajax="false" href="'.diamondUrl( $product_price_id ).'">Diamond</a> to your <a  data-ajax="false" href="'.site_url('cart').'">shopping cart</a>!'));
				}
			}

		}
		else
		{
			redirect(site_url('cart'));	
		}
	}
	
}

/**
 *
 */
function cmn_vw_wishAdd( $product_price_id, $is_ajax, $wishArr, $customer_id )
{
	$CI =& get_instance();
	$cmn_vw = array();
	if( is_restClient() )
	{
		/**
		 * to tell REST client that cart wish requires update
		 */
		$CI->session->set_userdata( array( "is_CW" => 1 ) );
	}
	
	if( is_restClient() )
	{
		$pid = (int)$CI->input->post('pid');
		$token = $CI->input->post('token');	//page token : token will identify session and will usefull for getting product_price_id
		if($pid!=0)						//apply product price id directly from listing page if not zero if function called form details page then it will be always zero
		{
			$link = "";
			$product_price_id = $pid;
		}
		else if(!empty($token))
		{
			$product_price_id = $CI->session->userdata('product_price_id_'.$token);
		}
	
		if($product_price_id!=0)
		{
			$res = executeQuery("SELECT product_name,p.product_id FROM product_price pp INNER JOIN product p ON p.product_id=pp.product_id
								 WHERE pp.product_price_id=".$product_price_id." LIMIT 1");
			if(!empty($res))
			{
				//$link= getProductUrl($res[0]['product_id'],$product_price_id);
			}
	
			if(updWishDatabase($product_price_id,false,$wishArr,$customer_id))
			{
				$cmn_vw["type"] = "success";
				$cmn_vw["msg"] = "Success: You have added ".pgTitle($res[0]['product_name'])." to your wish list!";
			}
			else 
			{
				$cmn_vw["type"] = "warning";
				$cmn_vw["msg"] = "Warning: Product ".pgTitle($res[0]['product_name'])." is already in wish list!";
			}
		}
		
		return $cmn_vw; 
	}
	else 
	{
		if($is_ajax)
		{
			$pid = (int)$CI->input->post('pid');
			$token = $CI->input->post('token');	//page token : token will identify session and will usefull for getting product_price_id
			if($pid!=0)						//apply product price id directly from listing page if not zero if function called form details page then it will be always zero
			{
				$link = "";
				$product_price_id = $pid;
			}
			else if(!empty($token))
			{
				$product_price_id = $CI->session->userdata('product_price_id_'.$token);
			}
		
			if($product_price_id!=0)
			{
				$res = executeQuery("SELECT product_name,p.product_id FROM product_price pp INNER JOIN product p ON p.product_id=pp.product_id 
									 WHERE pp.product_price_id=".$product_price_id." LIMIT 1");
				if(!empty($res))
				{
					$link= getProductUrl($res[0]['product_id'],$product_price_id);
				}
		
				if(updWishDatabase($product_price_id,false,$wishArr,$customer_id))
				{
					echo json_encode(array('type'=>'success','msg'=>'Success: You have added <a href="'.$link.'">'.pgTitle($res[0]['product_name']).'</a> to your <a href="'.site_url('wishlist').'">wish list</a>!'));
				}
				else
				{
					echo json_encode(array('type'=>'warning','msg'=>'Warning: Product <a href="'.$link.'">'.pgTitle($res[0]['product_name']).'</a> is already in <a href="'.site_url('wishlist').'">wish list</a>!'));
				}
			}
		}
		else
		{
			redirect(site_url('wishlist'));
		}
	}

}

/**
 * cart List oblect
 */
function cmn_vw_cartListJSONObj( $data, &$cmn_vw, $limit=0 )
{
	$cmn_vw["pr"]["label"] = $cmn_vw["pr"]["desc"] = $cmn_vw["pr"]["image"] = array();
	$cmn_vw["pr"]["href"] = $cmn_vw["pr"]["param"] = array();

	$grand_total = 0.0;
	if( isset($data["cart_prod"]) && !isEmptyArr( $data["cart_prod"] ) )
	{
		foreach($data["cart_prod"] as $key=>$val)
		{
			/**
			 * only allow 3 products in featured products for RESTApps
			 */
			if( !empty($limit) && $key >= $limit )
			{
				break;
			}

			if(isset($val['not_available']))
			{
				/**
				 * [temp]: on 10-07-2015 some product with product_price_id not set is noticed, 
				 * not sure from where it is coming for now it is stoped from continuing further in loop.
				 */
				if( !isset($val["product_price_id"]) )
				{
					continue;
				}
				
				$cmn_vw["pr"]["label"][] = $val['not_available'];
				
				/**
				 * description
				 */
				$tempStr = "";
				
				//
				$tempStr = "pid=".$val["product_price_id"]."&not_available=".$val['not_available']."&";
				$cmn_vw["pr"]["desc"][] = $tempStr;
 				
				//product image folder
// 				$imagefolder = getProdImageFolder( $val['product_generated_code'], $val['product_price_id'], $val["product_sku"] );
// 				$product_images = fetchProductImages( $imagefolder );			//images for particular selection
				
				$product_images = front_end_hlp_getProductImages($val['product_generated_code'], $val['product_price_id'],
						  										 $val["product_sku"], $val['product_generated_code_info']);
				
				
				$cmn_vw["pr"]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
				$cmn_vw["pr"]["href"][] = "";
				$cmn_vw["pr"]["param"][] = "";
			}
			else 
			{
				//product image folder
// 				$imagefolder = getProdImageFolder( $val['product_generated_code'], $val['product_price_id'], $val["product_sku"] );
// 				$product_images = fetchProductImages( $imagefolder );			//images for particular selection
				
				$product_images = front_end_hlp_getProductImages($val['product_generated_code'], $val['product_price_id'],
																 $val["product_sku"], $val['product_generated_code_info']);
				
				
				$cmn_vw["pr"]["label"][] = $val["product_name"];
				
				/**
				 * description
				 */
				$tempStr = "";
				
				//
				$tempStr = "pid=".$val["product_price_id"]."&";
				
				//price
				$tempStr .= "product_discount=".$val['product_discount']."&".
						"product_price_calculated_price=".lp( $val['product_price_calculated_price'] )."&".
						"product_discounted_price=".lp( $val['product_discounted_price'] )."&".
						"code=".$val['product_generated_code_displayable']."&";
				
				if(empty($val['pa_value_cs']))
				{
					$tempStr .="color=-&";
				}
				else
				{
					$tempStr .="color=".$val['pa_value_cs']."&";
				}
				
				
				//stock
				if( isProductOutOfStock( $val["product_id"], $val["inventory_type_id"] ) )
				{
					$tempStr .= "is_out_of_stock=1&";
				}
				else
				{
					$tempStr .= "is_out_of_stock=0&";
				
					//qty
					if( hewr_isGroceryInventoryCheckWithId( $val["inventory_type_id"] ) )
					{
						$tempStr .= "isQty=1&opts=";
				
						$qtyOpts = getProdQtyOptions( $val["product_id"], $val["product_generated_code_info"], array() );
						foreach ($qtyOpts as $k1=>$ar1)
						{
							$tempStr .= $k1."=".$ar1."|";
						}
				
						$tempStr .= "&";
					}
					else
					{
						$tempStr .= "isQty=1&opts=";
						
						for($i=1;$i<=10;$i++)
						{
							$tempStr .= $i."=".$i."|";
						}
						
						$tempStr .= "&";
					}
				}
					
				//qty
				$tempStr .= "qty=".$data["cartArr"][ $data["customer_id"] ][ $key ]['qty']."&";
				
				//product types
				$tempStr .= "cz=&";	//$ this -> cz yet to make dynamic
				
				$cmn_vw["pr"]["desc"][] = $tempStr;
				
				/********************* desc end *************************/
				
				//cart total amount
				$grand_total += round( $val['product_discounted_price'] * $data["cartArr"][ $data["customer_id"] ][ $key ]['qty'], 0);
				
				$cmn_vw["pr"]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
				$tempArr = getProductUrl($val['product_id'],$val['product_price_id'],$val['product_alias'],$val['category_id']);
				$cmn_vw["pr"]["href"][] = $tempArr["href"];
				$cmn_vw["pr"]["param"][] = $tempArr["param"];
				
			}
				
		}
	}
	
	//grand_total
	$cmn_vw['grand_total'] = lp( $grand_total );
	return $cmn_vw;
}

/**
 * Deletes Cart item
 */
function cmn_vw_removeProduct( &$__this )//$product_price_id, $is_ajax, $cartArr, $customer_id
{
	$cmn_vw = array();
	if( is_restClient() )
	{
		/**
		 * to tell REST client that cart wish requires update
		 */
		$__this->session->set_userdata( array( "is_CW" => 1 ) );
	}

	
	$__this->product_price_id = $__this->input->post('id');
	//$cid  = $__this->input->post('cid');	deprecated : Only session cust_id is used if session for cust time out then entry goes to generel(0) session

	unset($__this->cartArr[$__this->customer_id][$__this->product_price_id]);
	if($__this->customer_id!=0)
	{
		if( IS_CS )
		{
			$__this->db->query("DELETE FROM customer_cartwish WHERE manufacturer_id=".MANUFACTURER_ID." AND
							    customer_id=".$__this->customer_id." AND product_price_id=".$__this->product_price_id." AND customer_cartwish_type='C'");
		}
		else
		{
			//removed on 01-06-2015:  manufacturer_id=7 AND
			$__this->db->query("DELETE FROM customer_cartwish WHERE
							    customer_id=".$__this->customer_id." AND product_price_id=".$__this->product_price_id." AND customer_cartwish_type='C'");
		}
	}

	$__this->session->set_userdata( array('cartArr'=>$__this->cartArr) );

	if( is_restClient() )
	{
		$cmn_vw["type"] = "success";
		$cmn_vw["msg"] = "";
		return $cmn_vw;
	}
	else
	{
		echo json_encode( array('type'=>'success') );
	}
}

/**
 * deletes wish list item
 * @param unknown $__this
 * @return multitype:string
 */
function cmn_vw_removeWishlist( &$__this )
{
	$cmn_vw = array();
	if( is_restClient() )
	{
		/**
		 * to tell REST client that cart wish requires update
		 */
		$__this->session->set_userdata( array( "is_CW" => 1 ) );
	}
	
	$__this-> product_price_id = $__this-> input->post('id');
	//$cid  = $__this-> input->post('cid');	deprecated : Only session cust_id is used if session fopr cust time out then entry goes to generel(0) session

	unset($__this-> wishArr[$__this-> customer_id][$__this-> product_price_id]);
	if($__this-> customer_id!=0)
	{
		if( IS_CS )
		{
			$__this-> db->query("DELETE FROM customer_cartwish WHERE manufacturer_id=".MANUFACTURER_ID." AND
							  customer_id=".$__this-> customer_id." AND product_price_id=".$__this-> product_price_id." AND customer_cartwish_type='W'");
		}
		else
		{
			$__this-> db->query("DELETE FROM customer_cartwish WHERE
								  customer_id=".$__this-> customer_id." AND product_price_id=".$__this-> product_price_id." AND customer_cartwish_type='W'");
		}
			
	}

	$__this-> session->set_userdata(array('wishArr'=>$__this-> wishArr));

	if(is_restClient())
	{
		$cmn_vw["type"] = "success";
		$cmn_vw["msg"] = "Product removed from wish list";
		return $cmn_vw;
	}
	else
	{
		echo json_encode(array('type'=>'success','msg'=>'Success: Product removed from wish list.'));
	}
}


/**
 * Get cart Data...
 */
function cmn_vw_getCartData( $customer_id, $cartArr )
{
	$CI =& get_instance();
	$cmn_vw = array();
	$data = array();

	/**
	 * From 09-04-2015 now it will always read from database if user is logged in
	*/
	if( isLoggedIn() )
	{
		$data = getCartData( "", $customer_id, true, false, true, true);

	}
	else
	{
		$data = getCartData( $cartArr, $customer_id, false, false, true, true);

	}

	if( is_restClient() )
	{
		//product list
		cmn_vw_cartListJSONObj( $data, $cmn_vw );

		return $cmn_vw;
	}
	else
	{}
}


/**
 * cart List oblect
 */
function cmn_vw_wishListJSONObj( $data, &$cmn_vw, $limit=0 )
{
	$cmn_vw["pr"]["label"] = $cmn_vw["pr"]["desc"] = $cmn_vw["pr"]["image"] = array();
	$cmn_vw["pr"]["href"] = $cmn_vw["pr"]["param"] = array();

	if( isset($data["wish_prod"]) && !isEmptyArr( $data["wish_prod"] ) )
	{
		foreach($data["wish_prod"] as $key=>$val)
		{
			/**
			 * only allow 3 products in featured products for RESTApps
			 */
			if( !empty($limit) && $key >= $limit )
			{
				break;
			}

			if(isset($val['not_available']))
			{
				$cmn_vw["pr"]["label"][] = $val['not_available'];
			
				/**
				 * description
				 */
				$tempStr = "";			
				$tempStr = "pid=".$val["product_price_id"]."&not_available=".$val['not_available']."&";
				$cmn_vw["pr"]["desc"][] = $tempStr;
			
				//product image folder
				$imagefolder = getProdImageFolder( $val['product_generated_code'], $val['product_price_id'], $val["product_sku"] );
				$product_images = fetchProductImages( $imagefolder );			//images for particular selection
				$cmn_vw["pr"]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
				$cmn_vw["pr"]["href"][] = "";
				$cmn_vw["pr"]["param"][] = "";
			}
			else 
			{
				//product image folder
// 				$imagefolder = getProdImageFolder( $val['product_generated_code'], $val['product_price_id'], $val["product_sku"] );
// 				$product_images = fetchProductImages( $imagefolder );			//images for particular selection
				
				$product_images = front_end_hlp_getProductImages($val['product_generated_code'], $val['product_price_id'],
																 $val["product_sku"], $val['product_generated_code_info']);
				
	
				$cmn_vw["pr"]["label"][] = $val["product_name"];
	
				/**
				 * description
				 */
				$tempStr = "";
	
				//
				$tempStr = "pid=".$val["product_price_id"]."&";
	
				//price
				$tempStr .= "product_discount=".$val['product_discount']."&product_price_calculated_price=".lp( $val['product_price_calculated_price'] )."&".
						"product_discounted_price=".lp( $val['product_discounted_price'] )."&".
						"code=".$val['product_generated_code_displayable']."&";

				if(empty($val['pa_value_cs']))
				{
					$tempStr .="color=-&";
				}
				else
				{
					$tempStr .="color=".$val['pa_value_cs']."&";
				}
				
				//stock
				if( isProductOutOfStock( $val["product_id"], $val["inventory_type_id"] ) )
				{
					$tempStr .= "is_out_of_stock=1&";
				}
				else
				{
					$tempStr .= "is_out_of_stock=0&";
	
					//qty
					if( hewr_isGroceryInventoryCheckWithId( $val["inventory_type_id"] ) )
					{
						$tempStr .= "isQty=1&opts=";
	
						$qtyOpts = getProdQtyOptions( $val["product_id"], $val["product_generated_code_info"], array() );
						foreach ($qtyOpts as $k1=>$ar1)
						{
							$tempStr .= $k1."=".$ar1."|";
						}
	
						$tempStr .= "&";
					}
					else
					{
						$tempStr .= "isQty=0&";
					}
				}
	
				//product types
				$tempStr .= "cz=&";	//$ this -> cz yet to make dynamic
	
				$cmn_vw["pr"]["desc"][] = $tempStr;
	
				/********************* desc end *************************/
	
	
				$cmn_vw["pr"]["image"][] = load_image( $product_images[ $val['product_angle_in'] ] );
				$tempArr = getProductUrl($val['product_id'],$val['product_price_id'],$val['product_alias'],$val['category_id']);
				$cmn_vw["pr"]["href"][] = $tempArr["href"];
				$cmn_vw["pr"]["param"][] = $tempArr["param"];
// 							$cmn_vw['pr']['color'][] = $val['pa_value_cs'];
// 							$cmn_vw['pr']['code'][] = $val['product_generated_code_displayable'];
			}
		
		}
	}
	else
	{
		$cmn_vw["type"] = "error";
		$cmn_vw["msg"] = "Sorry you have not any wish product!";
	}
	return $cmn_vw;
}

/**
 * Get Wish Data...
 */
function cmn_vw_getWishData( $customer_id, $cartArr )
{
	$CI =& get_instance();
	$cmn_vw = array();
	$data = array();

	/**
	 * From 09-04-2015 now it will always read from database if user is logged in
	*/
	if( isLoggedIn() )
	{
		$data = getWishData( "", $customer_id, true, false, true, true);

	}
	else
	{
		$data = getWishData( $cartArr, $customer_id, false, false, true, true);

	}

	if( is_restClient() )
	{
		//product list
		cmn_vw_wishListJSONObj( $data, $cmn_vw );

		return $cmn_vw;
	}
	else
	{}
}

/**
 * update qty of product in cart
 */
function cmn_vw_updateQty( &$__this )
{
	$cmn_vw = array();  
	$qty = $__this->input->post('qty');
	$__this->product_price_id = $__this->input->post('id');
	//$cid  = $__this->input->post('cid');	deprecated : Only session cust_id is used if session for cust time out then entry goes to generel(0) session
	$ring_size = $__this->input->post('ring_size');

		
	updCartDatabase($__this->product_price_id, $qty, false, false, $__this->cartArr, $__this->customer_id, $ring_size);
	
	if( is_restClient() )
	{
		$cmn_vw["type"] = "success";
		$cmn_vw["msg"] = "";
		
		return $cmn_vw; 
	}
	else 
	{
		echo json_encode(array('type'=>'success'));
	}
}

function cmn_vw_applyCoupon()
{
	$CI =& get_instance();
	$cmn_vw = array();

	$CI->form_validation->set_rules('coupon','Coupon Code','trim|required');
	$data = array();

	if( is_restClient() )
	{
		if($CI->form_validation->run() == FALSE)
		{
			$data["type"] = "error";
			$data['msg'] = getErrorMessageFromCode('01005');
			$data["error"] = $CI->form_validation->get_errors();
		}
		else
		{
			$resArr = array();
			//unset coupon session first
			$CI->session->unset_userdata('coupon_id');

			$couponCode = $CI->input->post('coupon');

			$resArr = getCartData($CI->cartArr, $CI->customer_id, false, true, true, true);

			if($resArr['type']=='success')
			{
				$resArr = applyCouponCode($resArr['order_subtotal_amt'], $resArr['cartArr'], $CI->customer_id, 0, $couponCode);
				if($resArr['type']=='success')
				{
					$data = $resArr; 
					$data['grand_total'] = lp($resArr['order_total_amt']);	//append prefix of currency
				}
				else
				{
					$data = $resArr;
					//pr($resArr); die;
				}
				
			}
			else if($resArr['type']=='error')
			{
				$data['type'] = "error";
				$data['msg'] = $resArr['msg'];
			}
			else 
			{
				$data = $resArr;
			}

		}
			
	}
	else
	{
		$resArr = array();
		//unset coupon session first
		$CI->session->unset_userdata('coupon_id');

		$couponCode = $CI->input->post('coupon');

		$resArr = getCartData($CI->cartArr, $CI->customer_id, false, true, true, true);

		if($resArr['type']=='success')
		{
			$resArr = applyCouponCode($resArr['order_subtotal_amt'], $resArr['cartArr'], $CI->customer_id, 0, $couponCode);
			if($resArr['type']=='success')
			{
				$resArr['grand_total'] = lp($resArr['order_total_amt']);	//append prefix of currency
			}
		}
		echo json_encode($resArr);
	}

	/**
	 *
	 */
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();

			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
		$cmn_vw["data"] = $data;
		return $cmn_vw;
	}
}


/******************************* cart wishlist functions end *****************************************/


/*************************************** checkout functions *************************************/

/**
 * checkout functions
 */


/**
 *
 */
function cmn_vw_getCheckOutDataStep2( $customer_id )
{
	$CI =& get_instance();
	$data = array();
	$cmn_vw = array();

	if( is_restClient() )
	{
		$data = cart_hlp_getCheckOutData( true ); 
		
		if( !isset($data["type"]) || $data["type"] == "success" )
		{
			/**
			 * previous addresses
			 */
			$cmn_vw["pa"] = array();
			if( !empty($customer_id) )
			{
				$res = getShippAddress( $customer_id );
					
				if( !isEmptyArr($res) )
				{
					foreach ($res as $k=>$ar)
					{
						$tempStr = "add=".$ar['customer_address_firstname'] . " " . $ar["customer_address_lastname"] . "|";
						$tempStr .= $ar['customer_address_address'] . "|";
						$tempStr .= $ar['cityname'] . "," . $ar['pincode'] . " - " . $ar['customer_address_phone_no'] . "&";
						$tempStr .= "customer_address_id=".$ar["customer_address_id"]."&";
						$cmn_vw["pa"][] = $tempStr;
					}
				}
			}
			
			//
			$cmn_vw["customer_shipping_address_id"] = $data["customer_shipping_address_id"];
			$cmn_vw["customer_billing_address_id"] = $data["customer_billing_address_id"];
			
			//shipping address
			$cmn_vw["sa"] = array();
			cmn_vw_customerAddressJSONOBj($cmn_vw["customer_shipping_address_id"], "shipp", ($cmn_vw["customer_shipping_address_id"]!=0),
			$cmn_vw["sa"]);
			
			//billing address
			$cmn_vw["ba"] = array();
			cmn_vw_customerAddressJSONOBj($cmn_vw["customer_billing_address_id"], "bill", ($cmn_vw["customer_billing_address_id"]!=0),
			$cmn_vw["ba"]);
				
		}
		else 
		{
			if( $data["type"] == "_redirect" )
			{
				//DO Nothing... redirect by upper libraries so REST client will just need to redirect to specified page  
			}
		}
		
		
		return $cmn_vw; 
	}
	else 
	{}
}

/**
 * 
 */
function cmn_vw_getCheckOutDataStep3( $customer_id ) 
{
	$CI =& get_instance();
	$data = array();
	$cmn_vw = array();

	if( is_restClient() )
	{
		if( isImportDuty() )
		{
			//call common import duty functions as it is in WebApp	
		}
		
		
		$data = cart_hlp_getCheckOutData( true );
		if( !isset($data["type"]) || $data["type"] == "success" )
		{
			//
			$cmn_vw["default_payment_method_id"] = 4;
				
			//
			$cmn_vw["isImportDuty"] = ( isImportDuty() ? 1 : 0 ); 
			
			//
			if( $data["resArr"]["order_total_amt"] <=  getCustBalance( $data["resArr"]["customer_id"] ) )
			{
				$cmn_vw["balance"] = getLangMsg("bal") . ":" . lp($data["customer_account_manage_balance"]);
				$cmn_vw["isBucksDisabled"] = 0;
			}
			else
			{
				$cmn_vw["balance"] = getLangMsg("bal") . ":" . lp($data["customer_account_manage_balance"]) .  " (Insufficient Balance)";
				$cmn_vw["isBucksDisabled"] = 1;
			} 
				
			//
			$cmn_vw["other_charges"] = array();
			if( isset( $data["resArr"]["other_charges"] ) )
			{ 
				$cmn_vw["other_charges"] = $data["resArr"]["other_charges"]; 
			}
			
			// change condition true->false on 17-02-2016
			$cmn_vw["coupon_id"] = 0;
			if( FALSE || !empty($data["resArr"]['coupon_id']) )
			{
				$cmn_vw["coupon_id"] = $data["resArr"]['coupon_id'];
				
				$cmn_vw["order_subtotal_amt"] = lp( $data["resArr"]['order_subtotal_amt'] );
				$cmn_vw["order_discount_amount"] = lp( $data["resArr"]['order_discount_amount'] );
			}
				
			$cmn_vw["grand_total"] = lp( $data['grand_total'] );
			
			// check Coupon
			if(!empty($resArr['coupon_id']))
			{
				$cmn_vw["order_subtotal_amt"] = lp($resArr['order_subtotal_amt']);
				$cmn_vw["order_discount_amount"] = lp( $data["resArr"]['order_discount_amount'] );
			}
			
			//check other charges
			if( !isEmptyArr( $cmn_vw["other_charges"] ) )
			{
				$cmn_vw["grand_total_1"] = lp( $data['grand_total'] );
				
				$cnt = 0;
				foreach ( $cmn_vw["other_charges"] as $k=>$ar )
				{
					$cnt++;
					$data['grand_total'] += $ar["value"];
					$cmn_vw["other_charges_".$cnt."_lbl"] = $ar["name"];
					$cmn_vw["other_charges_".$cnt."_val"] = lp($ar["value"]);
				}
				
				$cmn_vw["grand_total"] = lp( $data['grand_total'] );
			}
			unset($cmn_vw["other_charges"]);
		}
		else
		{
			if( $data["type"] == "_redirect" )
			{
				//DO Nothing... redirected by upper libraries so REST client will just need to redirect to specified page
			}
		}


		return $cmn_vw;
	}
	else
	{}
}

/************************************** Address info ****************************************************/

/**
 *
 */
function cmn_vw_customerAddressJSONOBj( $customer_address_id, $class, $is_read_only, &$cmn_vw )
{
	$CI =& get_instance();
	$data = array();
	$cmn_vw["is_read_only"] = ($is_read_only==true) ? 1 : 0;
	$cmn_vw["is_add_available"] = 0; 
	$cmn_vw["customer_address_id"] = $customer_address_id; 
	$cmn_vw["edit"] = ($is_read_only==true) ? 0 : 1;
	
	$res;
	if(isset($customer_address_id) && (int)$customer_address_id!=0)
	{
		$res = getAddress($customer_address_id, ""); 
		
		if( !isEmptyArr($res) )
			$cmn_vw["is_add_available"] = 1;
	}
	
	if( is_restClient() )
	{
		if( $cmn_vw["is_add_available"] == 1 )
		{
			$cmn_vw["customer_address_firstname"] = $res["customer_address_firstname"];
			$cmn_vw["customer_address_lastname"] = $res["customer_address_lastname"];
			$cmn_vw["customer_address_address"] = $res["customer_address_address"];
			$cmn_vw["country"] = getDefaultCountryID();
			$cmn_vw["state_id"] = getDefaultStateID();
			$cmn_vw["address_city"] = getDefaultCity();
			$cmn_vw["customer_address_landmark_area"] = $res["customer_address_landmark_area"];
			$cmn_vw["pincode"] = $res["pincode"];
			$cmn_vw["customer_address_phone_no"] = $res["customer_address_phone_no"];
		}
		else 
		{
			$cmn_vw["customer_address_firstname"] = "";
			$cmn_vw["customer_address_lastname"] = "";
			$cmn_vw["customer_address_address"] = "";
			$cmn_vw["country"] = getDefaultCountryID();
			$cmn_vw["state_id"] = getDefaultStateID();
			$cmn_vw["address_city"] = getDefaultCity();
			$cmn_vw["customer_address_landmark_area"] = "";
			$cmn_vw["pincode"] = "";
			$cmn_vw["customer_address_phone_no"] = "";
		}

		return true; 
	}
	else
	{}
}


/**
 *
 */
function cmn_vw_applyAddress()
{
	$CI =& get_instance();
	$data = array();
	$cmn_vw = array();

	if( is_restClient() )
	{
    	$data['customer_address_id'] = $CI->input->post('id');
    	$data['class'] = $CI->input->post('type');
    	$data['is_read_only'] = $CI->input->post('read');
		
    	//
    	$cmn_vw["ca"] = array(); 
    	cmn_vw_customerAddressJSONOBj($data['customer_address_id'], $data['class'], $data['is_read_only'], $cmn_vw["ca"]);
    	
		return $cmn_vw;
	}
	else
	{}
}

/**
 * 
 */
function cmn_vw_editAddress( $customer_id )
{
	$CI =& get_instance();
	$CI->load->model('mdl_checkout','che');
	$cmn_vw = array();
	
	//    
	if( $customer_id == 0 )
    {
    	rest_redirect("checkout", ""); 
		return $cmn_vw;
    }
	    
	$CI->form_validation->set_rules('customer_address_firstname_shipp','First Name','trim|required');
	$CI->form_validation->set_rules('customer_address_address_shipp','Address','trim|required|min_length[10]');
	$CI->form_validation->set_rules('country_shipp','country','trim|required');
	$CI->form_validation->set_rules('state_id_shipp','State','trim|required');
	//$CI->form_validation->set_rules('address_city_shipp','City','trim|required');
	$CI->form_validation->set_rules('customer_address_landmark_area_shipp','Area','trim|required');
	$CI->form_validation->set_rules('pincode_shipp','Pincode','trim|required');
	$CI->form_validation->set_rules('customer_address_phone_no_shipp','Mobile No','trim|required');
    	
	//
	$data =array();
	if($CI->form_validation->run() == FALSE)
	{
		if( is_restClient() )
		{
    		$data["type"] = "error";
    		$data["msg"] = getErrorMessageFromCode('01005');
    		$data["error"] = $CI->form_validation->get_errors();
		}
		else
		{}
	}
	else
	{
		if( is_restClient() )
		{
			$data = $CI->che->editAddress();
		}
		else
		{}
	}
	
	/**
	 *
	 */
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();
			
			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
		
		$cmn_vw["data"] = $data;
		return $cmn_vw;
	}
}

/**
 *
 */
function cmn_vw_applyShipInfo( $customer_id )
{
	$CI =& get_instance();
	$CI->load->model('mdl_checkout','che');
	$cmn_vw = array();
	
	//
	if( $customer_id == 0 )
	{
		rest_redirect("checkout", "");
		return $cmn_vw;
	}
	 
	$is_validation_req = false;
	$customer_address_id_shipp = $CI->input->post('customer_address_id_shipp');
	$edit_shipp = $CI->input->post('edit_shipp');
	$customer_address_id_bill = $CI->input->post('customer_address_id_bill');
	$edit_bill = $CI->input->post('edit_bill');
	$same_as_billing_address = $CI->input->post('same_as_billing_address');
    
	$order_is_gift_wrap = $CI->input->post('order_is_gift_wrap');
	if((int)$order_is_gift_wrap == 1)
    {
		//set session for gift wrapping
		$CI->session->set_userdata('order_is_gift_wrap',true);
	}
	else
	{
		//unset session for gift wrapping
		$CI->session->unset_userdata('order_is_gift_wrap');
	}
    
	if($customer_address_id_shipp==0 || $edit_shipp==1) //validate if save mode or edit mode is on
	{
		$is_validation_req=true;
		$CI->form_validation->set_rules('customer_address_firstname_shipp','First Name','trim|required');
		$CI->form_validation->set_rules('customer_address_address_shipp','Address','trim|required|min_length[10]');
		$CI->form_validation->set_rules('country_shipp','country','trim|required');
		//$CI->form_validation->set_rules('state_id_shipp','State','trim|required');
		$CI->form_validation->set_rules('address_city_shipp','City','trim|required');
		$CI->form_validation->set_rules('customer_address_landmark_area_shipp','Area','trim|required');
		$CI->form_validation->set_rules('pincode_shipp','Pincode','trim|required');
		$CI->form_validation->set_rules('customer_address_phone_no_shipp','Mobile No','trim|required');
	}
    
	if((int)$same_as_billing_address!=1 && $customer_address_id_bill==0) //validate if diff address and save mode is on
	{
		$is_validation_req=true;
		$CI->form_validation->set_rules('customer_address_firstname_bill','First Name','trim|required');
		$CI->form_validation->set_rules('customer_address_address_bill','Address','trim|required|min_length[10]');
		$CI->form_validation->set_rules('country_bill','country','trim|required');
		//$CI->form_validation->set_rules('state_id_bill','State','trim|required');
		$CI->form_validation->set_rules('address_city_bill','City','trim|required');
		$CI->form_validation->set_rules('customer_address_landmark_area_bill','Area','trim|required');
		$CI->form_validation->set_rules('pincode_bill','Pincode','trim|required');
		$CI->form_validation->set_rules('customer_address_phone_no_bill','Mobile No','trim|required');
	}
    
    
	//
	$data =array();
	if($is_validation_req && $CI->form_validation->run() == FALSE)
	{
		if( is_restClient() )
		{
			$data["type"] = "error";
			$data["msg"] = getErrorMessageFromCode('01005');
			$data["error"] = $CI->form_validation->get_errors();
		}
		else
		{}
	}
	else
	{
		if( is_restClient() )
		{
			$data = $CI->che->applyShipInfo();
    
			if($data['type']=='success')
			{
				$data = $CI->checkShipAvail();
			}
	    
			//set type to warning if it is error because error type used only for validation errors
			if($data['type'] == 'error')
	    			$data['type'] = 'warning';
	    
    		//set session that shipping is okay
    		if($data['type']=='success')
    		{
    			$CI->session->set_userdata(array('is_shipping_valid'=>true));
    		}
    		else
    		{
    			$CI->session->set_userdata(array('is_shipping_valid'=>false));
    		}
		}
		else
		{}
	}

	/**
	*
	*/
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();
			
			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
		else if( $data["type"] == "success" )
		{
			$data["shippS"] = array(); 
			$data["shippS"]["customer_shipping_address_id"] = $CI->session->userdata("customer_shipping_address_id");
			$data["shippS"]["customer_billing_address_id"] = $CI->session->userdata("customer_billing_address_id");
			$data["shippS"]["is_shipping_valid"] = ( $CI->session->userdata("is_shipping_valid") ? 1 : 0 );
		}
		
		$cmn_vw["data"] = $data;
		return $cmn_vw;
	}
}


/************************************** Address info end *********************************************/

/************************************** Payment ****************************************************/

/**
 *
 */
function cmn_vw_payment()
{
	$CI =& get_instance();
	$cmn_vw = array();

	$data = cart_hlp_payment( true );
	
	if( $data["type"] == "_redirect" )
	{
		//DO Nothing... redirect by upper libraries so REST client will just need to redirect to specified page. 
		//and it is most expected response for BUCKs and COD methods
		return $cmn_vw;
	}
	
	/**
	 *
	 */
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();
			
			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
			
			$cmn_vw["data"]["eKA"] = $data["eKA"];
			$cmn_vw["data"]["eVA"] = $data["eVA"];
		}

		//only Send required response 
		$cmn_vw["data"]["type"] = $data["type"];
		$cmn_vw["data"]["msg"] = $data["msg"];
		
		return $cmn_vw;
	}
}

/**
 * @author	Hitesh Khunt
 * Thank you msg after checkout msg complete
 */
function cmn_vw_thankyou()
{
	$CI =& get_instance();
	$cmn_vw = array();
	
	$data = cart_hlp_thankyou( true );
	
	if( $data["type"] == "_redirect" )
	{
		//DO Nothing... redirect by upper libraries so REST client will just need to redirect to specified page.
		//and it is most expected response for BUCKs and COD methods
		return $cmn_vw;
	}
	
	/**
	 *
	 */
	if( is_restClient() )
	{
		$cmn_vw["data"] = $data; 
		return $cmn_vw;
	}
}

/**
 * @author	Hitesh Khunt
 * Failure msg in case checkout process has raised some errors
 */
function cmn_vw_failure()
{
	$CI =& get_instance();
	$cmn_vw = array();
	
	$data = cart_hlp_failure( true );
	
	
	if( $data["type"] == "_redirect" )
	{
		//DO Nothing... redirect by upper libraries so REST client will just need to redirect to specified page.
		//and it is most expected response for BUCKs and COD methods
		return $cmn_vw;
	}
	
	/**
	 *
	 */
	if( is_restClient() )
	{
		$cmn_vw["data"] = $data; 
		return $cmn_vw;
	}
}

/************************************** Payment end ****************************************************/

/*************************************** checkout functions end *********************************/


/**
 *
 */
function cmn_vw_review()
{
	$CI =& get_instance();
	$CI->load->model('mdl_home','hom');
	$cmn_vw = array();

	$CI->form_validation->set_rules('product_review_description','Review','trim|required|min_length[10]|max_length[300]');
	$CI->form_validation->set_rules('product_review_rating','Rating','trim|required|numeric');

	//
	$data = array(); 
	if($CI->form_validation->run() == FALSE)
	{
		$data['type'] = "error";
		$data['msg'] = getErrorMessageFromCode('01005'); 
		$data["error"] = $CI->form_validation->get_errors();
	}
	else
	{
		//[temp]: REST remove when login session is dynamic on REST App
		if( is_restClient() )
		{
			$CI->session->set_userdata( array( "customer_id"=>43 ) ); 
		}
		
		$CI->hom->review();
		$data['type'] = "success";
		$data['msg'] = getLangMsg("rvs");
	}
	
	/**
	 * 
	 */
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();

			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k; 
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			} 			
		}
		
		$cmn_vw["data"] = $data;
		return $cmn_vw; 
	}
	else 
	{
		echo json_encode($data);
		die;
	}
}

/**
 * contact us form
 */
function cmn_vw_contact()
{
	$CI =& get_instance();
	$CI->load->model('mdl_home','hom');
	$cmn_vw = array();

	$CI->form_validation->set_rules('pm_name','Full Name','trim|required');
	$CI->form_validation->set_rules('pm_email','Email Id','trim|required|valid_email');
	$CI->form_validation->set_rules('pm_phone','Phone Number','trim|required|numeric');
	$CI->form_validation->set_rules('pm_message','Message','trim|required');

	$data = array();
	if($CI->form_validation->run() == FALSE)
	{
		if( is_restClient() )
		{
			$data["type"] = "error";
			$data['msg'] = getErrorMessageFromCode('01005');
			$data["error"] = $CI->form_validation->get_errors();
		}
		else
		{
			$data = $CI->form_validation->get_errors();
			echo json_encode($data);
		}
	}
	else
	{
		if( is_restClient() )
		{
			$CI->hom->feedback();
			$data["type"] = "success";
			$data['msg'] = getLangMsg("s_msg");
		}
		else
		{
			$CI->hom->feedback();
			$data['success'] = 1;
			echo json_encode($data);
		}
	}

	/**
	 *
	 */
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();
			
			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
			
		$cmn_vw["data"] = $data;
		return $cmn_vw;
	}
	
}	

/******************************* login signup functions ***********************************/

/**
 * 
 * @return multitype:NULL Ambigous <multitype:, string, number, NULL, multitype:string , unknown>
 */
function cmn_vw_guestSignup()
{
	$CI =& get_instance();
	$CI->load->model('mdl_checkout','che');
	$cmn_vw = array();
	$returnArr = array();

	$CI->form_validation->set_rules('login_email','Email','trim|required|valid_email|callback_checkMailDuplication');
	
	//
	$data = array();
	if($CI->form_validation->run() == FALSE)
	{
		if( is_restClient() )
		{
			$data["type"] = "error";
			$data['msg'] = getErrorMessageFromCode('01005');
			$data["error"] = $CI->form_validation->get_errors();
		}
		else
		{
    		//$customer_emailid = $CI->input->post('login_email');
    			
    		$returnArr['type'] = 'error';
    		$returnArr['error'] = $CI->form_validation->get_errors();
		}

	}
	else
	{
		if( is_restClient() )
		{
			$data = $CI->che->guestSignup();
			$data["type"] = "success";
			$data['msg'] = "";
		}
		else
		{
			$returnArr = $CI->che->guestSignup();
		}
	}

	/**
	 *
	 */
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();
			
			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
		else if( $data['type']=='success' )
		{
			//set all login sessions and upd cart/wish database
			$cmn_vw["lgnS"] = setLoginSessions($data['customer_id'], $data['customer_group_type'], $data['customer_emailid']);
			 
			unset($data['customer_id']);
			unset($data['customer_group_type']);
			unset($data['customer_emailid']);
		}
		
		
		$cmn_vw["data"] = $data;
		return $cmn_vw;
	}
	else 
	{
		if($returnArr['type']=='success')
		{
			//set all login sessions and upd cart/wish database
			setLoginSessions($returnArr['customer_id'], $returnArr['customer_group_type'], $returnArr['customer_emailid']);
			 
			unset($returnArr['customer_id']);
			unset($returnArr['customer_group_type']);
			unset($returnArr['customer_emailid']);
		}
		
		echo json_encode($returnArr);
	}
}


/**
 * Create Date 08-05-2015 For Use restAPI
 * Login / Signin Form
 */
function cmn_vw_login()
{
	$CI =& get_instance();
	$CI->load->model('mdl_login','lgn');
	$CI->lgn->cTable = "customer";
	$CI->lgn->cAutoId = "customer_id";

	$data = array();
	$cmn_vw = array();
	if( is_restClient() )
	{
		/**
		 * to tell REST client that cart wish requires update
		 */
		$CI->session->set_userdata( array( "is_CW" => 1 ) );
	}
	

	$CI->form_validation->set_rules('login_email','Email Address','trim|required|valid_email');
	$CI->form_validation->set_rules('login_password','Password','trim|required');
	
	if( is_restClient() )
	{
		if($CI->input->post() != '')
		{
		
			if($CI->form_validation->run() == FALSE)
			{
				$data["type"] = "error";
				$data['msg'] = getErrorMessageFromCode('01005');
				$data["error"] = $CI->form_validation->get_errors();
				//$returnArr['error'] = $CI->form_validation->get_errors();
			}
			else
			{
				$email = trim($CI->input->post('login_email'));
				$password   = md5($CI->input->post('login_password').$CI->config->item('encryption_key'));
		
				$response = $CI->lgn->getCustomerData($email,$password);
		
				if($response)
				{
					//On 01-05-2015 allowed login to guest and set thier type as G
					if($response['customer_group_type'] == 'U' || $response['customer_group_type'] == 'G')
					{
						if(($response['customer_emailid'] == $email) && ($response['customer_password'] == $password))
						{
							if($response['customer_status'] == '0')
							{
								//update customer group if G then to U
								checkAndUpdateGuestCustomerGroup( $response['customer_id'], $response['customer_group_type'] );
		
								//set all login sessions and upd cart/wish database
								$cmn_vw["lgnS"] = setLoginSessions($response['customer_id'], 'U', $response['customer_emailid']);
		
								$data["type"] = "success";
								$data['msg'] = getLangMsg("l_suc");
								//$returnArr['success'] = 'true';
							}
							else
							{
								//$CI->lgn->isCustomerDisabled($response);
								$data["type"] = "error";
								$data['msg'] = getErrorMessageFromCode('01002');
								$data['error'] = getErrorMessageFromCode('01002');
								//$returnArr['warning'] = getErrorMessageFromCode('01002');
							}
						}
						else
						{
							$data['type'] = "error";
							$data['msg'] = "Invalid email or password combination";
							$data['error'] = array('login_email'=>getErrorMessageFromCode('01013'));
							//$returnArr['error'] = array('login_not_match'=>getErrorMessageFromCode('01013'));
						}
					}
					elseif($response['customer_group_type'] == 'G')
					{
						$data["type"] = "error";
						$data['msg'] = getErrorMessageFromCode('01020');
						$data['error'] = getErrorMessageFromCode('01020');
						//$returnArr['warning'] = getErrorMessageFromCode('01020');
					}
					else
					{
						$data["type"] = "error";
						$data['msg'] = getErrorMessageFromCode('01015');
						$data['error'] = getErrorMessageFromCode('01015');
						//$returnArr['warning'] = getErrorMessageFromCode('01015');
					}
				}
				else
				{
					$data['type'] = "error";
					$data['msg'] = "Invalid email or password combination";
					$data['error'] = array('login_not_match'=>getErrorMessageFromCode('01013'));
					//$returnArr['error'] = array('login_not_match'=>getErrorMessageFromCode('01013'));
				}
			}
		
// 			$msg = getFlashMessage('customer_referrer');
		
// 			if( $CI->session->userdata('transaction_id') !== FALSE )	//complete pending order left due to session time out
// 			{
// 				$email = $CI->session->userdata('email');
// 				if($email == $CI->session->userdata('customer_emailid'))
// 				{
// 					$msg ='checkout/completeOrdOnTimeOut';
// 				}
// 				else
// 				{
// 					setFlashMessage('error', 'One order pending with CI session. Login with same account to complete the pending order.');
// 					$msg='account';
// 				}
// 			}
// 			else if($msg=='')
// 			{
// 				$msg='account';
// 			}
// 			else
// 			{
// 				$msg = customizeRedUrl($msg);
// 			}
		
			//$returnArr['ref_url'] = site_url($msg);
			//echo json_encode($returnArr);
		}
		else
		{
			$tempI = $CI->session->userdata('customer_id'); 
			if( empty( $tempI ) || $CI->session->userdata('customer_group_type') == "C" )
			{
				$data['type'] = "error";
				$data['msg'] = getLangMsg("iin");
			}
			else
			{
				$data['type'] = "success";
				$data['msg'] = "Already logged in!";
				
				//set login sessions only for sake of REST lgnS response
				$cmn_vw["lgnS"] = setLoginSessions( $CI->session->userdata('customer_id'), $CI->session->userdata('customer_group_type'), $CI->session->userdata('customer_emailid'));
			}
		}
	}
	else 
	{
		
	}
	
	/**
	 *
	 */
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();
			
			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
		else if( $data["type"] == "success" )
		{
			//set activity or contoller redirect on REST Apps
			if( !isset($data['redirect']) )
			{
				$cmn_vw['redirect'] = "invitefriend";
			}
		}
		
		$cmn_vw["data"] = $data;
		return $cmn_vw;
	}
}

/**
 * created Date 09-05-2015
 * New Register / signup form
 */
function cmn_vw_signup()
{
	$CI =& get_instance();
	$CI->load->model('mdl_login','lgn');
	$cmn_vw = array();

	$CI->form_validation->set_rules('customer_firstname','Name','trim|required');
	$CI->form_validation->set_rules('customer_lastname','Name','trim|required');
	$CI->form_validation->set_rules('customer_emailid','Email Address','trim|required|valid_email|callback_checkMailDuplication');
	$CI->form_validation->set_rules('customer_phoneno','Phone','trim|required|numeric');
	$CI->form_validation->set_rules('customer_password','Password','trim|required|min_length[6]');
	//$CI->form_validation->set_rules('confirm_password','Confirm Password','trim|required|matches[customer_password]|min_length[3]');
	//$CI->form_validation->set_rules('agree','Agree terms','trim|required');

	//
	$data = array();
	if($CI->form_validation->run() == FALSE)
	{
		if( is_restClient() )
		{
			$data["type"] = "error";
			$data['msg'] = getErrorMessageFromCode('01005');
			$data["error"] = $CI->form_validation->get_errors();
		}
		else
		{
			$data["error"] = $CI->form_validation->get_errors();
			echo json_encode($data);
			die;
		}
	}
	else
	{
		if( is_restClient() )
		{
			$cmn_vw["lgnS"] = $CI->lgn->saveNewAccount();
			$data["type"] = "success";
			$data['msg'] = getLangMsg("s_reg");
		}
		else
		{
			$CI->lgn->saveNewAccount();
			$data['success'] = 1;
			echo json_encode($data);
			die;
		}
	}
	
	/**
	 *
	 */
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();
			
			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{	
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
		$cmn_vw["data"] = $data;
		return $cmn_vw;
	}
}

/**
 * created Date 11-05-2015
 * forgot password form
 */
function cmn_vw_forgot()
{
	$CI =& get_instance();
	$CI->load->model('mdl_login','lgn');
	$CI->lgn->cTable = "customer";
	$CI->lgn->cAutoId = "customer_id";

	$cmn_vw = array();

	$CI->form_validation->set_rules('forgot_email','Email Address','trim|required|valid_email');

	$data = array();

	if(is_restClient())
	{
		if($CI->form_validation->run() == FALSE)
		{
			$data["type"] = "error";
			$data['msg'] = getErrorMessageFromCode('01005');
			$data["error"] = $CI->form_validation->get_errors();
		}
		else
		{
			$email 	  = trim($CI->input->post('forgot_email'));
			$response = $CI->lgn->getCustomerData($email);
				
			if($response && ($response['customer_emailid'] == $email))
			{
				$CI->load->helper('string');
					
				$user_pass = random_string('alnum', 6); //random generate string
				$data['customer_password'] = md5($user_pass.$CI->config->item('encryption_key'));

				$CI->db->where($CI->lgn->cAutoId,$response['customer_id'])->update($CI->lgn->cTable,$data);
					
				$data['email_list_id'] = getField("email_list_id", "email_list", "email_id", $response['customer_emailid']);
				$data['first_name'] = $response['customer_firstname'];
				$data['last_name'] = $response['customer_lastname'];
				$data['email_address'] = $response['customer_emailid'];
				$data['text_password'] = $user_pass;
				$data['login_link'] = base_url('login');
				//getTemplateDetailAndSendMail('RESET_PASSWORD_EMAIL',$data);
					
				$subject = 'Reset Your Password at Stationery';
				$mail_body = $CI->load->view('templates/forgot-password',$data,TRUE);
				$mail_body .= $CI->load->view('templates/footer-template',array( 'email_list_id'=>$data['email_list_id'],'email_id'=>$data['email_address']),TRUE);

				sendMail($data['email_address'], $subject, $mail_body);
					
				$data["type"] = "success";
				$data['msg'] = getLangMsg("s_f_msg");
			}
			else
			{
				$data["type"] = "error";
				$data['msg'] = getErrorMessageFromCode('01015');
				$data["error"] = array('forgot_email'=>getErrorMessageFromCode('01015'));
			}
		}
	}

	/**
	 *
	 */
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();
			
			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
			
		$cmn_vw["data"] = $data;

		return $cmn_vw;
	}
}

/**
 * 
 * @return multitype:Ambigous <multitype:, string, unknown, multitype:string , number, NULL>
 */
function cmn_vw_logout()
{
	$CI =& get_instance();
	$cmn_vw = array();
	$is_checkout = (int) $CI->input->get("is_checkout");
	
	$customer_id = (int)$CI->session->userdata('customer_id');
	if($customer_id!=0)
	{
		if( $is_checkout != 1 )
		{
			unsetLoginSessions();
			setFlashMessage('success','You are successfully logged out.');
		}
		else 
		{
			$cartArr = $CI->session->userdata('cartArr');
			$wishArr = $CI->session->userdata('wishArr');
			
			$data = logout('', false, false, $customer_id, $cartArr, $wishArr);
			setFlashMessage( $data["type"], $data["msg"] ); 
		}
		
		
		if( is_restClient() )
		{
			if( $is_checkout != 1 )
			{
				$cmn_vw["redirect"] = "home";
			}
			else 
			{
				$cmn_vw["redirect"] = "checkout";
			}
		}
		else 
		{}
	}
	else
	{
		if( is_restClient() )
		{
			if( $is_checkout != 1 )
			{
				$cmn_vw["redirect"] = "home";
			}
			else
			{
				$cmn_vw["redirect"] = "checkout";
			}
		}
		else 
		{}
	}
	
	return $cmn_vw;
}


/******************************* login signup functions end ***********************************/


/**
 * Create date : 15-05-2015
 * change password to restAPI / Desktop
 */
function cmn_vw_changePassword()
{
	$CI =& get_instance();
	$CI->load->model('mdl_account','ma');
	$CI->ma->cTable = 'customer';

	$cmn_vw = array();
	$CI->form_validation->set_rules('current_password','Current password','trim|required|callback_checkForOldPassword');
	$CI->form_validation->set_rules('new_password','New password','trim|required|min_length[6]');
	$CI->form_validation->set_rules('confirm_password','Confirm password','trim|required|matches[new_password]|min_length[6]');

	if($CI->form_validation->run() == FALSE)
	{
		if ( is_restClient() )
		{
			$data["type"] = "error";
			$data['msg'] = getErrorMessageFromCode('01005');
			$data["error"] = $CI->form_validation->get_errors();
		}
		else
		{
			if($_POST)
			{
				$error_msg=$CI->form_validation->get_errors();
				foreach($error_msg as $key=>$val)
				{
					setFlashMessage('error',$val);
					break;
				}
			}
			$data['custom_page_title'] = 'Change Password';
			$data['pageName'] = 'account/change-password';
			$CI->load->view('site-layout',$data);
		}
	}
	else
	{
		if ( is_restClient() )
		{
			$CI->ma->saveChangedPassword();
			$data["type"] = "success";
			$data['msg'] = getLangMsg("cng_pass");
		}
		else
		{
			$CI->ma->saveChangedPassword();
			setFlashMessage('success','Your password has been changed successfully.');
			redirect('account');
		}
	}

	/**
	 *
	 */
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();
			
			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
		$cmn_vw["data"] = $data;
		return $cmn_vw;
	}
}

/**
 * Create date : 15-05-2015
 * InviteFriends to restAPI / Desktop
 */
function cmn_vw_invitefriend()
{
	$CI =& get_instance();
	$CI->load->model('mdl_home','hom');

	$cmn_vw = array();
	$CI->form_validation->set_rules('customer_partner_id','Email ID','trim|required|valid_email');
	$CI->form_validation->set_rules('customer_note','Tell Massage','trim|required');

	$data = array();
	if($CI->form_validation->run() == FALSE)
	{
		if ( is_restClient() )
		{
			$data["type"] = "error";
			$data['msg'] = getErrorMessageFromCode('01005');
			$data["error"] = $CI->form_validation->get_errors();
		}
		else
		{
			$data['type'] = "error";
			$data['msg'] = getErrorMessageFromCode('01005');
			$data["error"] = $CI->form_validation->get_errors();
			echo json_encode($data);
		}
	}
	else
	{
		if ( is_restClient() )
		{
			$CI->hom->inviteFriend();
			$data['type'] = "success";
			$data['msg'] = getLangMsg("invfr");
		}
		else
		{
			$CI->hom->inviteFriend();
			$data['type'] = "success";
			$data['msg'] = getLangMsg("invfr");
			echo json_encode($data);
		}
	}

	/**
	 *
	 */
	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();
			
			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
		$cmn_vw["data"] = $data;
		return $cmn_vw;
	}
}

/**
 *
 * @param unknown $__this
 * @return multitype:
 */
function cmn_vw_socialmediashare_get( &$__this )
{
	$CI =& get_instance();

	$cmn_vw = array();
	$data = array();

	if( is_restClient() )
	{
		$res = fetchRow("SELECT article_name,article_image,article_description FROM article WHERE article_key='INVITE_FRIEND_MAIL' ");
		$c_name = getField('customer_firstname','customer','customer_id',$CI->session->userdata("customer_id"));
		
		$cmn_vw['aff_msg'] =  "For every friend that register and purchase first time you will get discount of Rs. ".getField("config_value", "configuration", "config_key", "COMPAIGN_AMT").".";
		
		if( !isEmptyArr( $res ) )
		{
			$cmn_vw['from'] = "From: ".$CI->session->userdata("customer_emailid");
			$cmn_vw['paragraph'] = " <div id='display_cnote'></div><br>". $res['article_description'];
			$cmn_vw['paragraph'].= " <br/> http://www.Stationery.com/home/invitedFriends?ref=".$CI->session->userdata("customer_emailid");
			$cmn_vw['paragraph'].= " <br/><br/> Regards, <br/> ".$c_name;
			
			$cmn_vw['paragraph_str1'] = str_replace( "&nbsp;", '', strip_tags($res['article_description']) );
			$cmn_vw['paragraph_str2']= "http://www.Stationery.com/home/invitedFriends?ref=".$CI->session->userdata("customer_emailid");
			$cmn_vw['paragraph_str3']= "Regards,";
			$cmn_vw['paragraph_str4']= $c_name;

			$data['type'] = "success";
		}
		else
		{
			$data['type'] = "error";
			$data['msg'] = "Record Not Found";
		}
	}

	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();

			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
		$cmn_vw["data"] = $data;
		return $cmn_vw;
	}
}

/******************************* account panel functions ***********************************/

/**
 *
 */
function cmn_vw_account( &$__this )
{
	$cmn_vw = array();
	$data = array();
	
	if( is_restClient() )
	{
		$cmn_vw['customer_account_manage_balance'] = lp( $__this->ma->currentBalance() );
		
		//
		$cmn_vw["pr"]["label"] = $cmn_vw["pr"]["desc"] = $cmn_vw["pr"]["image"] = array();
		$cmn_vw["pr"]["href"] = $cmn_vw["pr"]["param"] = array();
		
		//Invite Friends
		$cmn_vw["pr"]["label"][] = getLangMsg("invfr"); 
		$cmn_vw["pr"]["desc"][] = "";
		$cmn_vw["pr"]["image"][] = "";
		$cmn_vw["pr"]["href"][] = "invitefriend";
		$cmn_vw["pr"]["param"][] = "";

		//Order history
		$cmn_vw["pr"]["label"][] = getLangMsg("o_h");
		$cmn_vw["pr"]["desc"][] = "";
		$cmn_vw["pr"]["image"][] = "";
		$cmn_vw["pr"]["href"][] = "order_history";
		$cmn_vw["pr"]["param"][] = "";
		
		//Address Books
		$cmn_vw["pr"]["label"][] = getLangMsg("a_bok");
		$cmn_vw["pr"]["desc"][] = "";
		$cmn_vw["pr"]["image"][] = "";
		$cmn_vw["pr"]["href"][] = "address_book";
		$cmn_vw["pr"]["param"][] = "";
		
		//refferals Landing Page
		$cmn_vw["pr"]["label"][] = getLangMsg("f_c");
		$cmn_vw["pr"]["desc"][] = "";
		$cmn_vw["pr"]["image"][] = "";
		$cmn_vw["pr"]["href"][] = "ref_code";
		$cmn_vw["pr"]["param"][] = "";
		
		//Edit Account
		$cmn_vw["pr"]["label"][] = getLangMsg("e_acc");
		$cmn_vw["pr"]["desc"][] = "";
		$cmn_vw["pr"]["image"][] = "";
		$cmn_vw["pr"]["href"][] = "edit_account";
		$cmn_vw["pr"]["param"][] = "";
		
		//Change Password
		$cmn_vw["pr"]["label"][] = getLangMsg("cng_pass");
		$cmn_vw["pr"]["desc"][] = "";
		$cmn_vw["pr"]["image"][] = "";
		$cmn_vw["pr"]["href"][] = "change_pass";
		$cmn_vw["pr"]["param"][] = "";
		
		//Wishlist
		$cmn_vw["pr"]["label"][] = getLangMsg("w_l");
		$cmn_vw["pr"]["desc"][] = "";
		$cmn_vw["pr"]["image"][] = "";
		$cmn_vw["pr"]["href"][] = "wish";
		$cmn_vw["pr"]["param"][] = "";
		
		//Transactions
		$cmn_vw["pr"]["label"][] = getLangMsg("mybal");
		$cmn_vw["pr"]["desc"][] = "";
		$cmn_vw["pr"]["image"][] = "";
		$cmn_vw["pr"]["href"][] = "transaction";
		$cmn_vw["pr"]["param"][] = "";
		
		//Newsletter
		$cmn_vw["pr"]["label"][] = getLangMsg("nl");
		$cmn_vw["pr"]["desc"][] = "";
		$cmn_vw["pr"]["image"][] = "";
		$cmn_vw["pr"]["href"][] = "newsletter";
		$cmn_vw["pr"]["param"][] = "";
		
		//Log-out / Sign-out
		$cmn_vw["pr"]["label"][] = getLangMsg("l_out");
		$cmn_vw["pr"]["desc"][] = "";
		$cmn_vw["pr"]["image"][] = "";
		$cmn_vw["pr"]["href"][] = "logout";
		$cmn_vw["pr"]["param"][] = "";
		
		return $cmn_vw;
	}
	else
	{
		$data['customer_account_manage_balance'] = $__this->ma->currentBalance();
		$data['custom_page_title'] = 'My Account';
		$data['pageName'] = 'account/index';
		$__this->load->view('site-layout',$data);
	}
}

/**
 * 
 * @param unknown $listArr
 * @param unknown $cmn_vw
 * @param number $limit
 * @param unknown $cnt
 * @param string $cz
 * @param string $filter_page
 */
function cmn_vw_order_historyJSONObj( $listArr, &$cmn_vw, $cnt )
{
	$cmn_vw["pr"]["label"] = $cmn_vw["pr"]["desc"] = $cmn_vw["pr"]["image"] = array();
	$cmn_vw["pr"]["href"] = $cmn_vw["pr"]["param"] = array();

	//total records
	$cmn_vw["pr"]["cnt"] = $cnt;

	//PER_PAGE_FRONT
	$cmn_vw["pr"]["PER_PAGE_FRONT"] = PER_PAGE_FRONT;

	//is_records
	if( $cmn_vw["pr"]["cnt"] > PER_PAGE_FRONT )
	{
		$cmn_vw["pr"]["is_records"] = 1;

		/**
		 * more records callback controller, href and hrefParams
		 */
		$cmn_vw["pr"]["moreC"] = "rest/rest_account";
		$cmn_vw["pr"]["moreH"] = "scrollPagination_order_history";
		$cmn_vw["pr"]["moreP"] = "";
	}
	else
	{
		$cmn_vw["pr"]["is_records"] = 0;
		$cmn_vw["pr"]["moreC"] = "";
		$cmn_vw["pr"]["moreH"] = "";
		$cmn_vw["pr"]["moreP"] = "";
	}

	if( !isEmptyArr( $listArr ) )
	{
		foreach($listArr as $key=>$ar)
		{
			cmn_vw_order_historyJSONObjRow($cmn_vw, $ar);
		}
	}
}

/**
 * 
 * @param unknown $cmn_vw
 * @param unknown $ar
 */
function cmn_vw_order_historyJSONObjRow(&$cmn_vw, $ar)
{
	$res = executeQuery("SELECT order_status_name
    					 FROM order_tracking t
						 INNER JOIN order_status s
						 ON s.order_status_id=t.order_status_id
						 WHERE t.order_id=".$ar['order_id']."
						 ORDER BY order_tracking_id
						 DESC LIMIT 1");
	
	/**
	 * [temp]:
	 * Needs to fix: instead of just not displaying order for which status is not found(webapp is not displaying empty status orders), 
	 * it should go in detail and display whatever is the issue with order.  
	 */
	if( !empty($res) )
	{
		/**
		 * @since
		 * can't fetch order history order_status_name to change direct pass the label in rest API.
		 */
		$tempStr =  "order_total_amt=".lp($ar['order_total_amt'])."&".
					"order_created_date=".$ar['order_created_date']."&".
					"order_id=".$ar['order_id']."&";
		$cmn_vw["pr"]["desc"][] = $tempStr;
		$cmn_vw["pr"]["label"][] = $res[0]["order_status_name"];
		$cmn_vw["pr"]["image"][] = "";
		$cmn_vw["pr"]["href"][] = "order_track";
		$cmn_vw['pr']['param'][] = "oid="._en($ar["order_id"]);
	}
	
}

/**
 * 
 * @param unknown $cTable
 * @param unknown $cAutoId
 * @param number $customerId
 * @param number $start
 * @return string
 */
function cmn_vw_order_history($cTable, $cAutoId, $customerId=0, $start = 0)
{
	$CI =& get_instance();
	$cmn_vw = array(); 
	$data = array(); 

	$CI->load->model('mdl_account','ma');
	$CI->ma->cTable = $cTable;
	$CI->ma->cAutoId = $cAutoId;
	$CI->ma->customerId = $customerId;

	/**
	 * set scroll state: limit that is being reached now
	 */
	$CI->session->set_userdata( "order_history_limit", 0 );
	
	if( is_restClient() )
	{
		$num = $CI->ma->getOrderDetails( 0 );
		$resCnt = $CI->db->query("SELECT FOUND_ROWS( ) as 'Count'")->row_array();
		
		//history list
		cmn_vw_order_historyJSONObj( $num->result_array(), $cmn_vw, $resCnt['Count'] );
		
		/**
		 * free memory
		 */
		$num = null; 
		
		return $cmn_vw;
	}
	else
	{
		$num = $CI->ma->getOrderDetails();
		$data = pagiationData( $CI->router->class."/orderHistory", $num->num_rows(), $start, 4 );
		
		foreach($data['listArr'] as $k=>$ar)
		{
			$data['order_details_'.$ar['order_id']] = $CI->disOrderDetails($ar['order_id']);
		}
		$data['custom_page_title'] = 'My Order History';
		$data['pageName'] = 'account/order-history';

		$CI->load->view('site-layout',$data);
	}

}

/**
 *
 */
function cmn_vw_scroll_pagination_order_history_listJSONObj( $listArr, &$cmn_vw )
{
	$cmn_vw["pr"]["label"] = $cmn_vw["pr"]["desc"] = $cmn_vw["pr"]["image"] = array();
	$cmn_vw["pr"]["href"] = $cmn_vw["pr"]["param"] = array();

	if( !isEmptyArr( $listArr ) )
	{
		foreach($listArr as $key=>$val)
		{
			cmn_vw_order_historyJSONObjRow($cmn_vw, $val);
		}
	}
}


/**
 * 
 * @param unknown $__this
 * @return multitype:
 */
function cmn_vw_scrollPagination_order_history( &$__this )
{
	$cmn_vw = array();

	/**
	 * get previous scroll limit
	 */
	$limit = (int)$__this->session->userdata("order_history_limit") + PER_PAGE_FRONT; 
	
	/**
	 * set scroll state: limit that is being reached now
	 */
	$__this->session->set_userdata("order_history_limit", $limit);
	
	/**
	 *
	 */
	$num = $__this->ma->getOrderDetails( $limit );
	
	if( is_restClient() )
	{
		//history list
		cmn_vw_scroll_pagination_order_history_listJSONObj( $num->result_array(), $cmn_vw );
	
		/**
		 * free memory
		*/
		$num = null;
	
		return $cmn_vw;
	}
}


/**
 * Order Tracking form
 * @param number $start
 * @return Ambigous <multitype:, string>
 */
function cmn_vw_order_tracking( &$__this )
{
// 	$CI =& get_instance();
//  	$CI->load->model('mdl_account','ma');
	$cmn_vw = array();
	$data = array();
	
	$order_id = _de($__this->input->get('oid'));
	if( !empty($order_id) )
	{
		$num = $__this->ma->getOrderTracking($order_id);
		if($num)
		{
			$data['listArr'] = $num->result_array();
		}
			
		if( isEmptyArr($data['listArr']) )
		{
			setFlashMessage('error','Order not found.');
			
			if( is_restClient() )
			{
				rest_redirect("", ""); 
				
				return $cmn_vw; 
			}
			else 
			{
				redirect('');
			}
		}
	
		foreach($data['listArr'] as $k=>$ar)
		{
			$data['order_details'] = $__this->ma->disOrderDetails($ar['order_id']);
		}
			
		foreach($data['order_details']['data']['data_order'] as $k=>$ar)
		{
			$data['order_details']['data']['order_tracking'][$ar['order_details_id']] = $__this->db->query('SELECT order_tracking_comment, order_tracking_modified_date, 
																	order_tracking_created_date, order_status_name, order_status_key,os.order_status_icon
																	FROM order_tracking ot LEFT JOIN order_status os
																	ON os.order_status_id=ot.order_status_id
																	WHERE ot.order_details_id='.(int)$ar['order_details_id'].' AND ot.order_tracking_status=0
																	ORDER BY order_tracking_id ASC')->result_array();
		}
			
		$data['customer_shipping_address'] = $__this->db->query(" SELECT c.customer_address_firstname, c.customer_address_lastname, c.customer_address_address,
												c.customer_address_phone_no, c.customer_address_zipcode,
												 p.pincode,s.state_name,p.cityname,p.areaname,co.country_name
												 FROM customer_address c
												 INNER JOIN pincode p ON p.pincode_id=c.customer_address_zipcode
												 INNER JOIN state s ON s.state_id=p.state_id
												 INNER JOIN country co ON co.country_id=s.country_id
												 WHERE customer_address_id=".$data['listArr'][0]['customer_shipping_address_id']." ")->row_array();
			
			
	}
	else
	{
		setFlashMessage('error','Invalid input.');

		if( is_restClient() )
		{
			rest_redirect("", ""); 
			
			return $cmn_vw; 
		}
		else 
		{
			redirect('');
		}
	}
	

	if( is_restClient() )
	{
		
		//order info
		$cmn_vw["order_id"] = $data['listArr'][0]["order_id"];
		$cmn_vw["order_created_date"] = formatDate("d-m-Y h:i:s A", $data['listArr'][0]['order_created_date']); 
		$cmn_vw["order_total_amt"] = lp( $data['listArr'][0]['order_total_amt']);
		
		//shipping address
		$cmn_vw["customer_firstname"] = $data["customer_shipping_address"]['customer_address_firstname']." ".$data["customer_shipping_address"]['customer_address_lastname'];
		$cmn_vw["customer_address_address"] = $data["customer_shipping_address"]['customer_address_address'];
		$cmn_vw["cityname"] = $data["customer_shipping_address"]['cityname'].", ".$data["customer_shipping_address"]['state_name'].", ".$data["customer_shipping_address"]['country_name'].".";
		$cmn_vw["customer_address_phone_no"] = $data["customer_shipping_address"]['customer_address_phone_no'];
		
		//products
		$cmn_vw["pr"] = array(); 
		$cnt = 0; 
		foreach ($data['order_details']['data']['data_order'] as $j=>$val):
			$cmn_vw["pr"][$cnt] = array();
		
			//product info
			$prodUrl = getProductUrl($val['product_id'], $val['product_price_id']);
			$cmn_vw["pr"][$cnt]["proHref"] = $prodUrl["href"]; 
			$cmn_vw["pr"][$cnt]["proParam"] = $prodUrl["param"];
			$cmn_vw["pr"][$cnt]["product_name"] = $val["product_name"];
			$cmn_vw["pr"][$cnt]["qty"] = $val["qty"];
				
			//tracking table list
			$cmn_vw["pr"][$cnt]["label"] = $cmn_vw["pr"][$cnt]["desc"] = $cmn_vw["pr"][$cnt]["image"] = array();
			$cmn_vw["pr"][$cnt]["href"] = $cmn_vw["pr"][$cnt]["param"] = array();
			foreach ($data['order_details']['data']['order_tracking'][$val['order_details_id']] as $l=>$value):
				
				$cmn_vw["pr"][$cnt]["label"][] = $value["order_status_name"];
				$cmn_vw["pr"][$cnt]["desc"][] = formatDate("d-m-Y h:i:s A",$value['order_tracking_created_date']);
				$cmn_vw["pr"][$cnt]["image"][] = load_image($value["order_status_icon"]);
				$cmn_vw["pr"][$cnt]["href"][] = "";
				$cmn_vw["pr"][$cnt]["param"][] = "";
			
			endforeach;
			
			$cnt++;
		endforeach;
		
		//cnt of products
		$cmn_vw["pr"]["cnt"] = $cnt; 
		
		return $cmn_vw;
	}
	else
	{
		$data['custom_page_title'] = 'My Order Tracking';
		$data['pageName'] = 'account/order-tracking';
	
		$__this->load->view('site-layout',$data);
	}
	
}

/**
 * Create date : 15-05-2015
 * Save New Address Book to restAPI / Desktop
 */
function cmn_vw_saveAddress( &$__this )
{
	$data = array();

	$cmn_vw = array();

	$__this->form_validation->set_rules('customer_address_firstname','First Name','trim|required');
	$__this->form_validation->set_rules('customer_address_address','Address','trim|required|min_length[10]');
	$__this->form_validation->set_rules('country_id','Country','trim|required');
	$__this->form_validation->set_rules('state_id','State','trim');
	$__this->form_validation->set_rules('address_city','City','trim|required');
	$__this->form_validation->set_rules('customer_address_landmark_area','Area','trim|required');
	$__this->form_validation->set_rules('pincode','Pincode','trim|required|numeric');
	$__this->form_validation->set_rules('customer_address_phone_no','Mobile No','trim|required');


	if ( is_restClient() )
	{
		if($__this->form_validation->run() == FALSE)
		{
			$data["type"] = "error";
			$data['msg'] = getErrorMessageFromCode('01005');
			$data["error"] = $__this->form_validation->get_errors();
		}
		else
		{
			$data = $__this->ma->saveAddress();
			if( $data["type"] == "_redirect" )
			{
				$cmn_vw = $data; 
				return $cmn_vw; 
			}
		}
	}
	else
	{
		if($__this->form_validation->run() == FALSE)
		{
			$data['error'] = $__this->form_validation->get_errors();
			if($data['error'])
				setFlashMessage('error',getErrorMessageFromCode('01005'));
			
			$data['mode'] = 'validation';
			$data['custom_page_title'] = 'Edit Address';
			$data['pageName'] = 'account/edit-address';
			$__this->load->view('site-layout',$data);
		}
		else
		{
			$res = $__this->ma->saveAddress();
			redirect('account/address-books');
		}
	}

	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();

			if( isset($data["error"]) && !isEmptyArr($data["error"]) )
			{
				foreach ($data["error"] as $k=>$ar)
				{
					$data["eKA"][] = $k;
					$data["eVA"][] = $ar;
				}
				unset($data["error"]);
			}
		}
		$cmn_vw["data"] = $data;
		return $cmn_vw;
	}
}

/**
 * Create date : 04-07-2015
 * get wishlist total data
 * get cartlist total data
 */
function cmn_vw_getrefreshWishCart( )
{
	$cmn_vw["type"] = "success";
	$cmn_vw["msg"] = "";
	$cmn_vw["data"] = getCartWishCount();
	return $cmn_vw;
}

/**
 * Create on 19-03-2016
 * common function call from API for drower link
 * About US, Return Policy, Term & Condition, Export.
 * @return unknown
 */

function cmn_vw_common_page($article_key)
{
	$cmn_vw["pr"]["label"] = $cmn_vw["pr"]["desc"] = $cmn_vw["pr"]["image"] = array();
	$cmn_vw["pr"]["href"] = $cmn_vw["pr"]["param"] = array();

	if( is_restClient() )
	{

		$artRow = "";
		if( MANUFACTURER_ID == 7 )
		{
			$artRow = fetchRow( "SELECT article_name,article_description, article_image FROM article WHERE article_key = '".$article_key."'" );
		}
		else
		{
			$artRow = fetchRow( "SELECT article_name,article_description, article_image FROM article_cctld WHERE article_key = '".$article_key."' AND manufacturer_id = ".MANUFACTURER_ID." " );
		}

		$cmn_vw['image'] = ASSET_URL($artRow['article_image']);
		$cmn_vw['desc'] = $artRow['article_description'];

		//$cmn_vw['data'] = $data;
		return $cmn_vw;
	}

	if( is_restClient() )
	{
		/**
		 * check if validation error then format error array for REST
		 */
		if( $data["type"] == "error" )
		{
			$data["eKA"] = array(); $data["eVA"] = array();
			foreach ($data["error"] as $k=>$arr)
			{
				$data["eKA"][] = $k;
				$data["eVA"][] = $arr;
			}
			unset($data["error"]);
		}
	}
}
/******************************* account panel functions end ********************************/