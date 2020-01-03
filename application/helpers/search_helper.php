<?php
/**
 * @package he_: s_ 
 * @author hrtech Dev Team
 * @version 1.9
 * @abstract product search helper
 * 
 * Helper added on 04-05-2015 to modularize product search module requirement arised from for REST API
 */

/**
 * added on On 04-05-2015
 */	
function he_s_productListing($is_search, $is_wild_search=false, $call_no=0)
{
	$CI = & get_instance(); 
	$is_no_result = $CI->input->get('nos'); 
	
	$categoryList = array();
	$data['search_tagArr'] = array('metal_tag'=>"",'metal_purity_tag'=>"",'metal_color_tag'=>"",'metal_type_tag'=>"",'diamond_purity_tag'=>"",'diamond_color_tag'=>"",'diamond_shape_tag'=>"",'diamond_type_tag'=>"",'cz_tag'=>"",'diamond_price_tag'=>"",'gender_filter_tag'=>"",'product_offer_tag'=>"",'product_categories_tag'=>"",'price_tag'=>"",'sort_by_tag'=>"",'keyword_search_tag'=>"",'product_attribute_tag'=>"");
	$data['search_url_tagArr'] = array('metal_url_tag'=>"",'metal_purity_url_tag'=>"",'metal_color_url_tag'=>"",'metal_type_url_tag'=>"",'diamond_purity_url_tag'=>"",'diamond_color_url_tag'=>"",'diamond_shape_url_tag'=>"",'diamond_type_url_tag'=>"",'cz_url_tag'=>"",'diamond_price_url_tag'=>"",'gender_filter_url_tag'=>"",'product_offer_url_tag'=>"",'product_categories_url_tag'=>"",'price_url_tag'=>"",'product_attribute_url_tag'=>"");
	
	$data['searchf'] = '';
	$categoryList['category_id'] = '';
	$data['filter_data'] = $CI->jew->getFilterData();
	
	$data['seo_url'] = '';
	if($is_search)
	{
		getSearchParam( $data );
		
		/**
		 * @deprecated
		*/
		// 			if( isset($data['searchf']['cz']) )
			// 			{
			// 				redirect('products/s');
			// 				exit;
			// 				$CI->cz = 'cz';
			// 			}
	
		unset($data['searchf']['start']);
		$data['where'] = "WHERE front_hook_alias='search'";
		if(isset($data['searchf']['search_terms_keywords']) && $data['searchf']['search_terms_keywords']!='')
		{
			//save search term in database...
			keyword_search();
		}
	}
	else
	{
		$segArr = $CI->uri->segment_array();
		$CI->menu_id = end($segArr);
		$CI->cat_alias = $segArr;
		
		$categoryList = $CI->jew->getCategory( $CI->cat_alias[1] );
			
		//temp impementation Note(Filter_TEMP): on TEMP at perry UML: for more info
		//$CI->session->set_userdata( array( 'sub_category_id' => $categoryList['category_id'] ) );
			
		$data['category_id'] = $categoryList['category_id'];
		$data['category_image'] = $categoryList['category_banner'];
		$data['category_name'] = $categoryList['category_name'];
		$data['custom_page_title'] = $categoryList['custom_page_title'];
		$data['meta_description'] = $categoryList['meta_description'];
		$data['meta_keyword'] = $categoryList['meta_keyword']; //Change: keywords are closed from 30/10/2013 , started from 22/1/2014
		$data['robots'] = $categoryList['robots'];
		$data['author'] = $categoryList['author'];
		$data['where'] = ' WHERE front_menu_id='.$CI->menu_id.'';
	}
	
	
	//parse keyword
	if( isset($data['searchf']['search_terms_keywords']) )
	{
		$data['searchf']['search_terms_keywords'] = isKeyword( $data['searchf']['search_terms_keywords'] );
		if( !$data['searchf']['search_terms_keywords'] )
		{
			//do nothing
		}
		else
		{
			$data['searchf']['search_terms_keywords'] = mysql_real_escape_string( $data['searchf']['search_terms_keywords'] );
			$resArr = filterKeyword( $data['searchf']['search_terms_keywords'] );
	
			if( $resArr['is_filtered'] )
			{
				$data['searchf']['search_terms_keywords'] = $resArr['keyword'];
					
				foreach( $resArr['id'] as $key1=>$val1 )
				{
					if($key1 == "metal_color")
					{
						if( isset( $data['searchf']['metal_color'] ) )
						{
							$data['searchf']['metal_color'] = array_merge( $data['searchf']['metal_color'], $val1 );
						}
						else
						{
							$data['searchf']['metal_color'] = $val1;
						}
					}
					else if($key1 == "metal_purity")
					{
						if( isset( $data['searchf']['metal_purity'] ) )
						{
							$data['searchf']['metal_purity'] = array_merge( $data['searchf']['metal_purity'], $val1 );
						}
						else
						{
							$data['searchf']['metal_purity'] = $val1;
						}
					}
				}
			}
		}
	}
	
	//Note(Filter_TEMP): on TEMP at perry UML:
	//feature implemented temporarily till search filter not updated to get from post
	//start
	$tmp = array();
	if(!empty($data['searchf']))
	{
		//remove empty array from search input: this is not temp implementation
		foreach($data['searchf'] as $k=>$ar)
		{
			if( isEmptyArr( $ar ) && $k != "search_terms_keywords" )
			{
				//"sort_by" is also unset here, but it will be handled from $_GET
				unset($data['searchf'][$k]);
			}
		}
	
			
		/**
		 * Inventory specific filters
		 */
		if( hewr_isJewelryInventory() )
		{
			foreach($data['searchf'] as $k=>$ar)
			{
				if(is_array($ar))
				{
					if( isset($data['searchf']['metal_color']) && isset($data['searchf']['metal_purity']) )
					{
						$data['searchf']['metal_color_purity'][] = $data['searchf']['metal_color'][0]."-".$data['searchf']['metal_purity'][0];
						unset($data['searchf']['metal_color']);
						unset($data['searchf']['metal_purity']);
					}
					else
					{
						$is_exist = false;
	
						if($k=='metal_color')
						{
							if(isset($data['searchf']['metal_color_purity']) && sizeof($data['searchf']['metal_color_purity'])>0)
							{
								foreach($data['searchf']['metal_color_purity'] as $key=>$val)
								{
									if(substr($val,0,1)==$ar[0])
									{
										$is_exist=true;
										unset($data['searchf'][$k]);
										break;
									}
								}
							}
								
							if(!$is_exist)
							{
								$metal_purity_id = getField('metal_purity_id','metal_purity','metal_purity_key','18K');
								$data['searchf']['metal_color_purity'][] = $ar[0]."-".$metal_purity_id;
							}
								
							unset($data['searchf']['metal_color']);
						}
						else if($k=='metal_purity')
						{
							if(isset($data['searchf']['metal_color_purity']) && sizeof($data['searchf']['metal_color_purity'])>0)
							{
								foreach($data['searchf']['metal_color_purity'] as $key=>$val)
								{
									if(substr($val,2,1)==$ar[0])
									{
										$is_exist=true;
										unset($data['searchf'][$k]);
										break;
									}
								}
							}
	
							if(!$is_exist)
							{
								$metal_color_id = getField('metal_color_id','metal_color','metal_color_key','YELLOW');
								$data['searchf']['metal_color_purity'][] = $metal_color_id."-".$ar[0];
							}
								
							unset($data['searchf']['metal_purity']);
						}
					}
				}
			}
		}
	}
	//end
	
	$res = $CI->jew->getProducts($categoryList['category_id'], $data['searchf'], '', false, false, false, $call_no);
	$num = $res['data'];
	
	$data['start'] = $res["start"];
	
	//fetch result array
	$data['listArr'] = $num['result_array'];
	
	//randomize the result
	if( @$data['searchf']['sort_by'] == '' )
	{
		/**
		 * @deprecated
		 */
		// 			if( MANUFACTURER_ID == 7 )
			// 				shuffle( $data['listArr'] );
			
		//set session if sorting is used: sort_by
		$CI->session->set_userdata( array('sort_by'=>'' ) );
	}
	else
	{
		//set session if sorting is used: sort_by
		$CI->session->set_userdata( array('sort_by'=>$data['searchf']['sort_by'] ) );
	}
	
	$data['total_records'] = $num['Count'];
	if( $data['total_records'] == 0 )
	{
		/**
		 * added on 25-03-2015
		 */
		if( $CI->input->get('call_no') !== FALSE && empty($call_no) )
		{
			$call_no = $CI->input->get('call_no');
		}
	
		/**
		 * BUG_CASE:
		 * in below redirection there is a infinite loop error case possible if inventory contains no products at all.
		 */
		if( isset($data['searchf']['search_terms_keywords']) && strpos( $data['searchf']['search_terms_keywords'], " ") !== FALSE && $call_no == 0 )
		{
			/**
			 * redirect search so that wild card terms is searched separately with each search term word
			 */
			return he_s_productListing( $is_search, $is_wild_search, 1 );
			//redirect('search');
		}
		else
		{
			if( isEmptyArr( $data['searchf'] ) || ( isset($data['searchf']['search_terms_keywords']) && strcasecmp ( $data['searchf']['search_terms_keywords'], 'products') == 0 ) )
			{
				/**
				 * this condition is added on 25-03-2015 to prevent loop if inventory is entirely empty
				 */
				if( $call_no == 0 )
				{
					redirect('search');
				}
				else
				{
					redirect();
				}
			}
			else
			{
				/**
				 * On 23-04-2015
				 * Commented TO resolve BUG CASE: of redirecting user to all records when none matching thier searched criteria.
				 */
				//redirect( 'search?nos=1&call_no='.( $call_no + 1 ) );
			}
		}
	}
	
	$data['perpage'] = PER_PAGE_FRONT;
	
	return array( "data"=>$data, "res"=>$res, "is_no_result"=>$is_no_result ); 
}

/*
 * @author Cloudwebs Kahar
* function will save search terms/keywords searched by users
*/
function keyword_search()
{
	$CI =& get_instance(); 
	//Change --> currently validation ignored direct save of function is called : Cloudwebs
	$CI->jew->saveSearchTerm();
	return;

	/*		
	 * $data = array();
		$CI->form_validation->set_rules('search_terms_keywords','Search Keyword','trim|required');
	if($CI->form_validation->run() == FALSE )
	{
	$returnArr['error'] = $CI->form_validation->get_errors();
	}
	else // saving data to database
	{
	$CI->jew->saveSearchTerm();
	redirect($CI->controller);
	}
	echo json_encode($returnArr);
	*/	
}



?>