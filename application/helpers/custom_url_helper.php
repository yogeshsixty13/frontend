<?php
/**
 * CUSTOM URL helper: this helper will be project requirement specific, so it will not be tracked in "he_" GIT respository.  
 * @package he_: url_hlp
 * @author Cloudwebs Tech Dev Team
 * @version 1.9
 */

/*
 +------------------------------------------------------------------+
Function will generate the breadcrumb for product
+------------------------------------------------------------------+
*/
function generateProductBreadcrumb($parent,$selected,$str = '')
{
	$CI =& get_instance();
	if($parent == 0)
		return $str;
	else
	{
		$r = $CI->db->where('category_id',$parent)->where('del_in','0')->get('product_category')->row_array();

		$str = generateProductBreadcrumb($r['parent_category'],$str);
		if($CI->router->method == 'product_details')
			$lk = '<a href="'.getMenuLink($r['category_id']).'">'.$r['category_name'].'</a>';
		else
			$lk = ($selected != $r['category_id'])? '<a href="'.getMenuLink($r['category_id']).'">'.$r['category_name'].'</a>':$r['category_name'];
		return $str.'&nbsp;&nbsp;<b>/</b>&nbsp;&nbsp;'.$lk;
	}
}

/*
 +------------------------------------------------------------------+
Function will generate menu link.
$cat = id from category
+------------------------------------------------------------------+
*/
function getMenuLink($cat,$str='')
{
	$CI =& get_instance();

	if($str == '') // if initialise then we have append base url to link
		$str .= base_url()."products/";

	$r = $CI->db->select('category_alias,parent_category')->where('category_id',$cat)->where('del_in','0')->get('product_category')->row_array();

	if($r['parent_category'] == 0)
		return $str.$r['category_alias'];
	else
	{
		$str = getMenuLink($r['parent_category'],$str);
		return $str."/".$r['category_alias'];
	}
}


/**
 * function will return listing URL
 */
function getListingUrl( $main_cat, $item )
{
	if( is_restClient() )
	{
		return array( "href"=>"products_list", "param"=>"uri=".$main_cat.$item.".html" ); 
	}
	else 
	{
		return site_url( $main_cat.$item.".html" ); 
	}
}

/*
 +--------------------------------------------------+
Function will create and return front side url for particular article as per controllers used
+--------------------------------------------------+
*/
function getArtUrl($article_id,$is_first_call=true,$prefix='')
{
	$CI =& get_instance();
	$hook_alias = getField("front_hook_alias","front_hook","front_hook_name","Articles");
	if($is_first_call)
	{
		if( MANUFACTURER_ID == 7 )
		{
			$res = $CI->db->select("article_alias,article_category_alias,article_category_parent_id")->join('article_category c','c.article_category_id=a.article_category_id','INNER')->where("article_id",$article_id)->get("article a")->row_array();
		}
		else
		{
			$res = fetchRow( " SELECT article_alias,article_category_alias,article_category_parent_id FROM article_cctld ac
							   INNER JOIN article_category c
							   ON c.article_category_id=ac.article_category_id
							   WHERE ac.manufacturer_id=".MANUFACTURER_ID." AND ac.article_id=".$article_id." " );
		}
	}
	else
	{
		$res = $CI->db->select("article_category_alias,article_category_parent_id")->where("article_category_id",$article_id)->get("article_category")->row_array();
	}

	$temp = ($is_first_call)? $res['article_category_alias']."/".$res['article_alias']:$res['article_category_alias']."/";
	if($res['article_category_parent_id'] != 0)
	{
		return $prefix = getArtUrl($res['article_category_parent_id'],false,$temp).$prefix;
	}
	else
	{
		return base_url($hook_alias.'/'.$temp.$prefix);
	}
}

/*
 +--------------------------------------------------+
Function will create and return front side url for particular category_id as per controllers used
+--------------------------------------------------+
*/
function getCatUrl($category_id,$front_menu_id=0,$prefix='')
{
	$CI =& get_instance();
	if($front_menu_id == 0)
	{
		$res = $CI->db->query("SELECT front_menu_id FROM front_menu WHERE front_menu_table_name='product_categories' AND front_menu_primary_id=".(int)$category_id."")->row_array();
		if(!empty($res))
			$front_menu_id = $res['front_menu_id'];
	}

	if( MANUFACTURER_ID == 7 )
	{
		$res = $CI->db->select("category_alias,parent_id")->where("category_id",(int)$category_id)->get("product_categories")->row_array();
	}
	else
	{
		$res = $CI->db->select("category_alias,parent_id")->where( "manufacturer_id", MANUFACTURER_ID)->where("category_id",(int)$category_id)->get("product_categories_cctld")->row_array();
	}

	if(!empty($res))
	{
		if($res['parent_id'] != 0)
		{
			return getCatUrl($res['parent_id'],$front_menu_id,$res['category_alias']."/".$prefix);
		}
		else
		{
			return site_url('jewellery/main/'.$res['category_alias']."/".$prefix.$front_menu_id);
		}
	}
	else
	{
		return site_url();
	}
}

/**
 +--------------------------------------------------+
 Function will create and return front side url for particular product_id as per controllers used
 +--------------------------------------------------+
 */
function getProductUrl($product_id,$product_price_id,$prod_alias='',$category_id=0,$prefix='')
{
	$CI =& get_instance();
	
	/**
	 * REST url
	 */
	if( is_restClient() )
	{
		return array( "href"=>"product_detail", "param"=>"id=".$product_price_id );
	}
	
	
	if((int)$category_id == 0)
	{
		$res = $CI->db->query("SELECT category_id,product_alias FROM product WHERE product_id=".$product_id."")->row_array();
		if(!empty($res))
		{
			$catidArr = explode("|",$res['category_id']);
			$category_id = $catidArr[0];
			$prod_alias = $res['product_alias'];
		}
	}

	$res = $CI->db->select("category_alias,parent_id")->where("category_id",(int)$category_id)->get("product_categories")->row_array();
	
	if(!empty($res))
	{
		if($res['parent_id'] != 0)
		{
			return getProductUrl($product_id,$product_price_id,$prod_alias,$res['parent_id'],$res['category_alias']."/".$prefix);
		}
		else
		{
			return site_url($res['category_alias']."/".$prefix.$prod_alias."-".$product_price_id);
		}
	}
	else
	{
		return site_url($prod_alias."-".$product_price_id);
	}
}

/**
 * Cloudwebs added On 15-05-2015
 */
function rest_redirect( $viewHref, $hrefParams )
{
	$CI =& get_instance(); 
	
	$sess = array();
	$sess["_redirect"] = $viewHref;
	$sess["_rparam"] = $hrefParams;
	$CI->session->set_userdata( $sess );
}
 
/**
 +--------------------------------------------------+
 Function will create and return front side url for particular product_id as per controllers used
 +--------------------------------------------------+
 */
function getProductPriviewUrl( $product_id, $product_url, $sites_id=0 )
{
	$CI =& get_instance();

	if( empty($sites_id) )
	{
		$sites_id = getField("sites_id", "product", "product_id", $product_id);
	}

	/**
	 *
	 */

	if( $sites_id == SITES_ID )
	{
		return $product_url;
	}
	else
	{
		return str_replace( site_url(), getField("s_domain", "ms_sites", "sites_id", $sites_id), $product_url);
	}
}

/**
 * is valid URL
 */
function isValidUrl( $url )
{
	if (filter_var($url, FILTER_VALIDATE_URL) === FALSE)
	{
		return false;
	}
	else
	{
		return true;
	}
}

/**
 * function will return full request URI
 */
function he_url_hlp_reqUri()
{
	if( isLocalHost() )
	{
		return str_replace(LOCALHOST_PART, "", $_SERVER["REQUEST_URI"]);
	}
	else
	{
		return $_SERVER["REQUEST_URI"];
	}
}

?>