<?php
/**
 * @package pr_: adm_hlp 
 * @author hrtech Dev Team
 * @version 1.9
 * @abstract admin features helper
 * @copyright Perrian Tech
 */

/**
 * @author Cloudwebs
 * function check for restricted modules to be accessed by only devlopers
 */
function checkDevPermission()
{
	$CI =& get_instance();
	if( $CI->session->userdata("admin_id") != 5 && $CI->session->userdata("admin_id") != 30 && $CI->session->userdata("admin_id") != 33)
	{
		setFlashMessage('error', "Sorry! the module can be accessed by developers only.");
		adminRedirect('admin/dashboard');
	}
}

/**
 * @author Cloudwebs
 * @abstract function will check for permission of user for page where user going to be redirected  
 * if permission not available for specific page then user redirected to page where logged in admin has permission 
 * if no page available which particular user can access then user redirected to home page with message for asking to seek permission from super admin first.
 *	
 */
	function adminRedirect( $class='', $isredirect=false ) 
	{
		$CI =& get_instance();
		$class = str_replace(array(0=>'admin/',1=>'admin'),"",$class);
		if(!$isredirect)
		{
			$admin_user_id = $CI->session->userdata('admin_id');
			if($class!='')
			{
				$res = $CI->db->query("SELECT COUNT(permission_id) as Count FROM permission p 
										INNER JOIN admin_menu m ON m.admin_menu_id=p.admin_menu_id
										WHERE m.am_class_name='".$class."' AND p.admin_user_id=".$admin_user_id." AND permission_view=0 ")->row_array();
				
				if(!empty($res) && $res['Count']>=1)
				{
					$isredirect=true; 
				}
			}
		}

		if(!$isredirect)
		{
			$res = $CI->db->query("SELECT am_class_name FROM permission p 
									INNER JOIN admin_menu m ON m.admin_menu_id=p.admin_menu_id
									WHERE p.admin_user_id=".$admin_user_id." AND permission_view=0 LIMIT 1")->row_array();
									
			if(!empty($res))
			{
				$isredirect=true;					
				$class = $res['am_class_name'];
			}
		}
		
		unset($CI);
		if($isredirect)
		{
			redirect('admin/'.$class);
		}
		else
		{
			setFlashMessage('error',getErrorMessageFromCode('01021'));
			showPermissionDenied();
		}
	}

/*
 * @author   Cloudwebs
 * @abstract function will check current admin user 
 * @return true if yes else false
 */
function checkIsSuperAdmin( $is_power_admin=false )
{
	$CI = & get_instance();
	$admin_user_id = $CI->session->userdata('admin_id');
	$res;
	
	if( !$is_power_admin )
	{
		$res = $CI->db->query("SELECT COUNT(g.admin_user_group_id) as Count FROM admin_user_group g 
								INNER JOIN admin_user a ON a.admin_user_group_id=g.admin_user_group_id 
								WHERE ( admin_user_group_key='SUPER_ADMIN' OR admin_user_group_key='POWER_ADMIN' ) AND a.admin_user_id=".$admin_user_id."")->row_array();
	}
	else
	{
		$res = $CI->db->query("SELECT COUNT(g.admin_user_group_id) as Count FROM admin_user_group g 
								INNER JOIN admin_user a ON a.admin_user_group_id=g.admin_user_group_id 
								WHERE ( admin_user_group_key='POWER_ADMIN' ) AND a.admin_user_id=".$admin_user_id."")->row_array();
	}
	
	unset($CI);
	if(!empty($res) && $res['Count']>=1)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

/**
+++++++++++++++++++++++++++++++++++++++++++++++++++++
	@params : $controller  name of controller
			  $per_type name of permission type to check
			  
	@return : array
+++++++++++++++++++++++++++++++++++++++++++++++++++++
*/
	function fetchPermission($controller)
	{
		$CI = & get_instance();
		$admin_id = $CI->session->userdata('admin_id');
		if($admin_id == '' || $admin_id == 0)
		{
			redirect('./admin');
		}
		
		$sql = "SELECT p.permission_add,p.permission_edit,p.permission_delete,p.permission_view FROM permission p INNER JOIN admin_menu m ON m.admin_menu_id=p.admin_menu_id WHERE p.admin_user_id=".$admin_id." AND m.am_class_name='".$controller."'";
		$res = $CI->db->query($sql);	
		if($res->num_rows() > 0)
		{
			$result =$res->row_array();
			unset($CI);
			return $result;
		}
		else
		{
			unset($CI);
			return '';
		}
	}
	
/**
+++++++++++++++++++++++++++++++++++++++++++++++++++++
	@params : $is_firstcall true if first time called in a recursive way
			  $name of select box
			  $optionArr option array
			  $setVal selected alue array
			  $extra property for input element
			  $i depth specifier in a multidimensional array
			  
	@return : string
+++++++++++++++++++++++++++++++++++++++++++++++++++++
*/
	function form_dropdownMultiDimensional($is_firstcall,$name,$optionArr,$setVal='',$extra='',$i=-1)
	{
		$html = '';
		if($is_firstcall)
			$html = '<select name="'.$name.'" '.$extra.'>';
			
		$i++;
		foreach($optionArr as $k=>$ar)
		{
			if(is_array($ar))
			{
				$html .= '<optgroup label="'.str_repeat("-",$i)." ".$k.'" >';	
				$html .=  form_dropdownMultiDimensional(false,$name,$ar,$setVal,$extra='',$i);
				$html .= '</optgroup>';
			}
			else
			{
				if(is_array($setVal) && sizeof($setVal)>0)
				{
					if(in_array($k,$setVal))
						$html .= '<option value="'.$k.'" selected="selected">'.$ar.'</option>';
					else
						$html .= '<option value="'.$k.'">'.$ar.'</option>';
				}
				else
					$html .= '<option value="'.$k.'">'.$ar.'</option>';
			}
		}
		
		$html = str_replace("</select>","",$html);
		return $html."</select>";
	}

/*
+------------------------------------------------------------------+
	Function will fetch menus from database and prepare combox 
	accroding to it's level.
	$parent = Start parent id from where you want to make menu tree.
	$menuArr = Default first option value.
	$i = Level of category which will convert in (-) dash
	$encode = TRUE OR FALSE. if true then id will be base 64 encode form
+------------------------------------------------------------------+
*/
function getMultiLevelMenuDropdown($parent = 0,$menuArr = array('0'=>'---- Select Parent Category ----'), $i = -1,$encode = false,$parentName='')
{
	$CI =& get_instance();
	
	$res = $CI->db->select('category_id,category_name')->where('parent_id',$parent)->
	order_by('category_sort_order')->get('product_categories')->result_array();
				
	if(count($res) > 0 )
	{
		$i++;
		foreach($res as $r):
			if($encode == true)
				$menuArr[_en($r['category_id'])] = str_repeat(' - ',$i).$r['category_name'].$parentName;	
			else
				$menuArr[$r['category_id']] = str_repeat(' - ',$i).$r['category_name'].$parentName;	
			$menuArr = getMultiLevelMenuDropdown($r['category_id'], $menuArr, $i, $encode, $parentName." - ".$r['category_name']);
		endforeach;
		return $menuArr;
	}
	else 
		return $menuArr;
}

/* article category
+------------------------------------------------------------------+
	Function will fetch menus from database and prepare combox 
	accroding to it's level.
	$parent = Start parent id from where you want to make menu tree.
	$menuArr = Default first option value.
	$i = Level of category which will convert in (-) dash
	$encode = TRUE OR FALSE. if true then id will be base 64 encode form
+------------------------------------------------------------------+
*/
function getMultiLevelMenuDropdownArticle($parent = 0,$menuArr = array('0'=>'---- Select Parent Article Category ----'), $i = -1,$encode = false)
{
	$CI =& get_instance();
	
	$res = $CI->db->select('article_category_id,article_category_name')->where('article_category_parent_id',$parent)->
	order_by('article_category_sort_order')->get('article_category')->result_array();
	//pr($res);
	if(count($res) > 0 )
	{
		$i++;
		foreach($res as $r):
			if($encode == true)
				$menuArr[_en($r['article_category_id'])] = str_repeat(' - ',$i).$r['article_category_name'];	
			else
				$menuArr[$r['article_category_id']] = str_repeat(' - ',$i).$r['article_category_name'];	
			$menuArr = getMultiLevelMenuDropdownArticle($r['article_category_id'],$menuArr,$i,$encode);
		endforeach;
		return $menuArr;
	}
	else 
		return $menuArr;
}

function getMultiLevelAdminMenuDropdown($parent = 0,$menuArr = array('0'=>'-- Select Parent Menu --'), $i = -1,$encode = false)
{
	$CI =& get_instance();
		
	$res = $CI->db->select('admin_menu_id,am_name')->where('am_parent_id',$parent)->
	order_by('am_sort_order')->get('admin_menu')->result_array();
	
	if(count($res) > 0 )
	{
		$i++;
		foreach($res as $r):
			if($encode == true)
				$menuArr[_en($r['admin_menu_id'])] = str_repeat(' - ',$i).$r['am_name'];	
			else
				$menuArr[$r['admin_menu_id']] = str_repeat(' - ',$i).$r['am_name'];	
			$menuArr = getMultiLevelAdminMenuDropdown($r['admin_menu_id'],$menuArr,$i,$encode);
		endforeach;
		return $menuArr;
	}
	else 
		return $menuArr;
}

function getMultiLevelFrontMenuDropdown($menu_type_id=0,$parent = 0,$menuArr = array('0'=>'-- Select Parent Menu --'), $i = -1,$encode = false)
{
	$CI =& get_instance();
		
	$res = $CI->db->select('front_menu_id,front_menu_name')->where('fm_parent_id',$parent)->where('front_menu_type_id',$menu_type_id)->
	order_by('fm_sort_order')->get('front_menu')->result_array();
	
	if(count($res) > 0 )
	{
		$i++;
		foreach($res as $r):
			if($encode == true)
				$menuArr[_en($r['front_menu_id'])] = str_repeat(' - ',$i).$r['front_menu_name'];	
			else
				$menuArr[$r['front_menu_id']] = str_repeat(' - ',$i).$r['front_menu_name'];	
			$menuArr = getMultiLevelFrontMenuDropdown($menu_type_id,$r['front_menu_id'],$menuArr,$i,$encode);
		endforeach;
		return $menuArr;
	}
	else 
		return $menuArr;
}
/*

+------------------------------------------------------+
	Function will load seller dropdown array. which will 
	useful for filtering process and also for product 
	assigning
	$default - > default option value you want to put in array.
	$Ecnode - > Data will encode in base 64 or not
+------------------------------------------------------+
*/
function getSellerDropdownArr($default = array(''=>'Please select Seller'),$encode = false)
{
	$CI =& get_instance();
	
	if(!empty($default))
		$arr = $default;
	
	$CI->db->where('del_in','0');	 		
	$res = $CI->db->order_by('first_name')->get('sellers')->result_array();
	if($encode)
		foreach($res as $r)
			$arr[_en($r['seller_id'])] = $r['first_name']." ".$r['last_name'] ; 
	else
		foreach($res as $r)
			$arr[$r['seller_id']] = $r['first_name']." ".$r['last_name'] ; 
	
	return $arr;
}

/*
+------------------------------------------------------------------+
	Function will be help system to find next sort order. 
Input =>
	@params-> $inst : Object of model
			  $fieldName : Name of the sorting field
+------------------------------------------------------------------+
*/
function getSortOrder(&$inst,$field)
{
	$CI =& get_instance();
	
	if(check_db_column($inst->cTable, 'del_in'))
		$maxArr = $CI->db->select_max($field)->where('del_in','0')->get($inst->cTable)->row_array();
	else
		$maxArr = $CI->db->select_max($field)->get($inst->cTable)->row_array();
		
	$mx = ($maxArr[$field] != '') ? $maxArr[$field]+1:0;
	return $mx;
}

// Order status dropdown for admin panel
function getOrderStatusDropdown($sel='',$extra='')
{
	$CI =& get_instance();
	$res = $CI->db->where('order_status_status','0')->order_by('order_status_name')->get('order_status')->result_array();
	
	$arr = array(''=>'');
	foreach($res as $r)
		$arr[$r['order_status_id']] = $r['order_status_name']; 
		
	return form_dropdown('order_status_id',$arr,$sel,$extra);
}

// image size dropdown for admin panel
function getImageSizeDropdown($sel='')
{
	$CI =& get_instance();
	$res = $CI->db->where('image_size_status','0')->order_by('image_size_sort_order')->get('image_size')->result_array();

	$arr = array(0=>'-- Select image size --');
	foreach($res as $r)
		$arr[$r['image_size_id']] = $r['image_size_width'].' x '.$r['image_size_height'].' px'; 
		
	echo form_dropdown('image_size_id',$arr,$sel,'');
}

// image size dropdown for admin panel
function getBannerSizeDropdown($sel='')
{
	$CI =& get_instance();
	$res = $CI->db->where('image_size_status','0')->order_by('image_size_sort_order')->get('image_size')->result_array();

	$arr = array(0=>'-- Select image size --');
	foreach($res as $r)
		$arr[$r['image_size_id']] = $r['image_size_width'].' x '.$r['image_size_height'].' px'; 
		
	echo form_dropdown('banner_size_id',$arr,$sel,'');
}

/*
 *  @abstract function will generate unique code for category  
 */
	function generateBrandCode($category_id,$prefix='')
	{
		$CI =& get_instance();
	
		$res = $CI->db->select("category_id,parent_id")->where("category_id",$category_id)->get("product_categories")->row_array();
							
		if($res['parent_id'] != 0)
		{
			return $prefix = generateBrandCode($res['parent_id'],"-".$res['category_id'].$prefix);
		}
		else	
		{			
			return $res['category_id'].$prefix;
		}
		unset($CI);
	}

/*
+------------------------------------------------------------------+
	Function will fetch menus from database and prepare combox 
	accroding to it's level.
	$parent = Start parent id from where you want to make menu tree.
	$menuArr = Default first option value.
	$i = Level of category which will convert in (-) dash
	$encode = TRUE OR FALSE. if true then id will be base 64 encode form
+------------------------------------------------------------------+
*/
function getMultiLevelWithOptGroup($select,$sort,$table_name,$parent = 0,$parent_field='',$menuArr = array('0'=>'---- Select Parent Category ----'), $encode = false)
{
	
	$CI =& get_instance();

	$res = $CI->db->select($select)->where($parent_field,$parent)->
	order_by($sort)->get($table_name)->result_array();
						
	if(count($res) > 0 )
	{
		foreach($res as $r):
			$res_child = $CI->db->select($select)->where('parent_id',$r['category_id'])->order_by($sort)->get($table_name)->result_array();
			if($encode == true)
			{
				if(count($res_child) > 0)
				{
					$menuArr[$r['category_name']] = getMultiLevelWithOptGroup($select,$sort,$table_name,$r['category_id'],$parent_field,'',$encode);	
				}
				else	
				{			
					$menuArr[_en($r['category_id'])] = $r['category_name'];	
				}
			}
			else
			{
				if(count($res_child) > 0)
				{
					$menuArr[$r['category_name']] = getMultiLevelWithOptGroup($select,$sort,$table_name,$r['category_id'],$parent_field,'',$encode);	
				}
				else	
				{			
					$menuArr[$r['category_id']] = $r['category_name'];	
				}
			}
		endforeach;
		return $menuArr;
	}
	else 
		return $menuArr;
}

/**
 *
 * @author Cloudwebs
 * @access public
 * @abstract gerates dynamically checkbox wise diamond and category display
 * @param string @sql 
 * @param array  @catfieldArr 
 * @param array @typefieldArr 
 * @param string @typeArr 
 * @param string @checkedArr 
 * @param string @checkboxProperty
 * @param string @extra
 * @return string
 *
*/
function renderCategorywithCheckbox($name,$sql, $catfieldArr, $typefieldArr, $typeArr = array(), $checkedArr = array(), $checkboxProperty = '', $extra= '',$product_id=0,$is_post=false, $product_stone_number=0)
{
	$CI =& get_instance();
	$html = "";
	$weight = ""; //product metal weight
	
	foreach($typeArr as $typekey => $type)
	{
		$typelower = strtolower($type);

		$html .= '<div id="tab-' . $typelower . '" ' . $extra . ' >';
		
		$html .= '<fieldset>';
		
		$html .= '<legend>' . $type . '</legend>';	
		
		$html .= '<table class="form" >
	              <tbody>';	
				  
		$tempsql =  $sql . $typekey . " ";
		
		$result = $CI->db->query($tempsql)->result_array();
		if(count($result))
		{
			foreach($result as $k=>$res)
			{
				$checked="";
				if(sizeof($checkedArr)>0)
				{
					if((in_array($res[$catfieldArr[0]], $checkedArr)))
					{
						$checked = ' checked';
						
						if($name=='mt_p[]' && !$is_post)
						{
							$resWgt = $CI->db->query("SELECT product_metal_weight FROM product_metal WHERE product_id=".$product_id." AND category_id=".$res[$catfieldArr[0]]."")->row_array();
							if(!empty($resWgt))
								$weight = $resWgt['product_metal_weight'];	
							else 	
								$weight='';
						}
					}
					else
					{
						$weight='';
					}
				}

				if($name=='mt_p[]' && $is_post)
				{
					$weight = @$_POST['product_metal_weight_'.$res[$catfieldArr[0]]];	
				}

				$type_name = str_replace("[]","",$name);
				$html .= '<tr>
						  <td width="60%"><label><input type="checkbox" onchange="return calcProdPrice(this,\''.substr($name,0,-2).'\',true,false, '.$product_stone_number.')" name="' . $name . '" value="' . $res[$catfieldArr[0]] . '" ' . $checkboxProperty . '  ' . $checked . ' id="chk_'.$type_name.'_' . $res[$catfieldArr[0]] . '" />' . $res[$catfieldArr[1]] . '</label></td>';
				
				if($name=='mt_p[]')
				{
					$html .= '<td><input onkeyup="return calcProdPrice(this,\'mt_p\',true,true, '.$product_stone_number.');" type="text" value="'.$weight.'" placeholder="Metal weight" name="product_metal_weight_' . $res[$catfieldArr[0]] . '" id="pmw_' . $res[$catfieldArr[0]] . '" data-="' . $res[$catfieldArr[0]] . '"/></td>';	
				}

				$html .= '<td width="40%"><span id="span_'.substr($name,0,-2).$res[$catfieldArr[0]].'">-</span></td></tr>';
			}
		}
		else
		{
			$html .= '<tr style="text-align:center"><td>No results!</td></tr>';
		}
		$html .= '</tbody>
            	  </table>
		          </fieldset>
           		  </div>';
	}
	return $html;
	
}

/*
+------------------------------------------------------------------+
	Function is save admin log. 
	@params : $className -> controller name
			  $itemName -> controller item name
			  $dbTableName -> name of db table
			  $dbTableField -> name of table field
			  $primaryId -> table primary id
			  $logType -> type of add/edit/delete
+------------------------------------------------------------------+
*/
function saveAdminLog($className, $itemName, $dbTableName, $dbTableField, $primaryId, $logType)
{
	$CI =& get_instance();
	$data = array(
			'admin_user_id' => $CI->session->userdata('admin_id'),
			'admin_class_name' => @$className,
			'module_item_name' => @$itemName,
			'module_table_name' => @$dbTableName,
			'module_table_field' => @$dbTableField,
			'module_primary_id' => @$primaryId,
			'admin_log_type' => @$logType,
			'admin_log_ip' => @$CI->input->ip_address()
			);
	
	$CI->db->insert('admin_log', $data);

}

/*
 * @author Cloudwebs
 * @abstract function will set all sessions related to login and perform other login related activity
*/
function setLoginSessionsAdmin($sessArr)
{	
	
	$CI =& get_instance();
	$CI->session->set_userdata( $sessArr ); //set session 
	saveLogins( $sessArr['admin_id'], 'A');
}

/*
author :Cloudwebs kahar
select image size  id in all tables where image field located
its fetch single fields from one table
*/	
function isImageIdExist($tableArr,$field_nameArr,$cur_id)
{
	$CI =& get_instance();

	foreach($tableArr as $k=>$ar)
	{
		
			$sql = $CI->db->query("SELECT ".$field_nameArr[$k]." FROM ".$ar." where ".$field_nameArr[$k]." = ".$cur_id."");
			$get_data = $sql->num_rows();
			if($get_data > 0)
			{
				unset($CI);			
				return array('type'=>'error','msg'=>'This '.$field_nameArr[$k].' cannot be deleted as it is currently assigned to  '.$get_data.'&nbsp;'.ucwords(str_replace("_"," ",$ar)));
			}
	}
	unset($CI);			
	return array();
}
/*
author :Cloudwebs kahar
select field in all tables where category field located
its fetch two fields from one table
*/	
function isFieldIdExist($tableArr,$field_nameArr,$cur_id,$is_like=false)
{
	$CI =& get_instance();

	foreach($tableArr as $k=>$ar)
	{
		//$sql = $CI->db->query("SELECT ".$field_nameArr[$k]." FROM ".$ar." where image_size_id = ".$cur_id."");
		if($is_like)
		{
			$sql = $CI->db->query("SELECT   ".$field_nameArr[$k]." FROM  ".$ar." where ".$field_nameArr[$k]." like '".$cur_id."' OR ".$field_nameArr[$k]." like '".$cur_id."|%' OR ".
			$field_nameArr[$k]." like '%|".$cur_id."|%' OR ".$field_nameArr[$k]." like '%|".$cur_id."'");
		}
		else
		{
			$sql = $CI->db->query("SELECT   ".$field_nameArr[$k]." FROM  ".$ar." where ".$field_nameArr[$k]."=".$cur_id."");
		}
			$get_data = $sql->num_rows();
		if($get_data > 0)
		{
			unset($CI);
			return array('type'=>'error','msg'=>'This category cannot be deleted as it is currently assigned to  '.$get_data.'&nbsp;'.ucwords(str_replace("_"," ",$ar)));
		}
	}
	unset($CI);			
	return array();
}

/*
author :Cloudwebs kahar
select field in all tables where one field located(used in product_category model)
its fetch three  fields from one table
*/	
function isFieldIdExistMul($tableArr,$field_nameArr,$valArr)
{
	$CI =& get_instance();

	foreach($tableArr as $k=>$ar)
	{
			
		$where = "";
		foreach($field_nameArr[$k] as $key=>$val)
		{
			$where .= $val. " = '" .$valArr[$k][$key]. "' AND ";	
		}
		
		$where = substr($where,0,-4);
		//$sql = $CI->db->query("SELECT   ".$field_nameArr[$k][1]." FROM  ".$ar." ".($where!="")?" WHERE ".$where:" ");
		$sql = $CI->db->query("SELECT   ".$field_nameArr[$k][0]." FROM  ".$ar." ".(($where!="")?' WHERE '.$where:'')."");
		//echo $CI->db->last_query();
		$get_data = $sql->num_rows();
		if($get_data > 0)
		{
			unset($CI);			
			return array('type'=>'error','msg'=>'This category cannot be deleted as it is currently assigned to  '.$get_data.'&nbsp;'.ucwords(str_replace("_"," ",$ar)));
		}
	}
	unset($CI);			
	return array();
}

/**
 * @author Cloudwebs
 * @abstract function record any page accessed by user: department_id only applicable when there is department other then perrian
 */
	function getOnlineVisiotrs( $pa_created_time, $department_id)
	{
		$CI =& get_instance();
		$res = $CI->db->query(" SELECT s.sessions_id as 's_sessions_id', s.s_ip as pa_ip, s.s_user_agent, s.s_user_device, pa.customer_id, pa.pa_url, pa.pa_referell_url, pa.pa_created_time, c.customer_firstname, l.sessions_id, ch.chat_id, s.s_created_time  
							  FROM 
                              (
                              		SELECT MAX(page_accesses_id) as page_accesses_id FROM page_accesses GROUP BY sessions_id
                              ) m_pa 
                              INNER JOIN page_accesses pa 
                              ON pa.page_accesses_id=m_pa.page_accesses_id 
                              INNER JOIN sessions s
                              ON s.sessions_id=pa.sessions_id 
                              Left JOIN customer c 
						      ON c.customer_id=pa.customer_id
                              LEFT JOIN logins l 
                              ON ( l.sessions_id=pa.sessions_id AND l.l_session_status=1 AND l.l_user_type='A' )
						      LEFT JOIN ch_chat ch
                              ON ( ch.sessions_id=pa.sessions_id )
						      WHERE pa.pa_created_time > date_sub(now(), interval ".($pa_created_time/60)." minute) 
						      AND ( l.sessions_id IS NULL ) 
                              GROUP BY pa.sessions_id
                              ORDER BY pa.page_accesses_id DESC ");

		if( $res->num_rows() > 0 )
			return $res->result_array(); 
		else 
			return array();	
	}

/**
 * @abstract Redirects user to default permission denied page if permission is not given
 */
	function showPermissionDenied()
	{
		$msg = getFlashMessage('error');
		if(empty($msg))
		{
			setFlashMessage('error',getErrorMessageFromCode('01022'));
		}
		else
		{
			setFlashMessage('error',$msg);
		}
		
		redirect('admin/lgs');
	}

/**
 * @abstract return html code for ebay listing product description 
 */
	function ebayHtmlPage( $product_code, $product_price_id=0, $is_testing=false ) 
	{
		$CI =& get_instance(); 
		if( !empty( $product_code ) )
		{
			$product_price_id = getField('product_price_id','product_price','product_generated_code',$product_code);
		}
		
		if( !empty( $product_price_id ) )
		{
			$data = showProductsDetails( $product_price_id, true, false, false, '', 0); 
			//echo 'test==';pr($data);die;
			
			if( $is_testing ) 
			{
				//define currenct constant
				currencyConstant( 0, 'USD' );
				echo "SKU: ".$data["product_sku"]." Price: ". lp_base( $data['product_discounted_price'], 2, constant( 'CURRENCY_ID_USD' ), true )."<br><br>";
				/*****************************************************************/
			}
			$pageName="";
			if( MANUFACTURER_ID == 2 )
				$pageName = "ebay_html_page";
			else if(MANUFACTURER_ID != 2)
				$pageName = "ebay_html_page_".MANUFACTURER_ID;
			
			$htmlContent = $CI->load->view('admin/product/'.$pageName, $data, TRUE);

			
			$search = array(
				'/\>[^\S ]+/s',  // strip whitespaces after tags, except space
				'/[^\S ]+\</s',  // strip whitespaces before tags, except space
				'/(\s)+/s'       // shorten multiple whitespace sequences
				
			);
		
			$replace = array(
				'>',
				'<',
				'\\1'
			);
		
			$htmlContent = preg_replace( $search, $replace, $htmlContent);
			$htmlContent = str_replace( "Â", "", $htmlContent);
			
			return $htmlContent;
		}
		else
		{
			return false;
		}
	
	}
	
	/**
	 * 
	 */
	function inventroyAttributeQuery()
	{
		return "SELECT inventory_master_specifier_id, CONCAT( ims_tab_label, ' - ', it_name ) AS ims_tab_label 
								FROM  inventory_master_specifier ims INNER JOIN inventory_type it
								ON it.inventory_type_id=ims.inventory_type_id 
								WHERE ims_status=0 AND ims_input_type IN ( ".inventroyAttributeMasterInputTypes()." ) ";
	}
	
	/**
	 *
	 */
	function getAttributeDataOfimsID( $sideStoneCnt, $inventory_master_specifier_id, $resP, $product_side_stonesData, 
									  $center_stone_idArr, $side_stone1_idArr, $side_stone2_idArr, $metal_price_idArr, 
									  $compAttrVal ) 
	{
		/**
		 * first check if match to any inventory component 
		 */
		if( $inventory_master_specifier_id == $resP["cs_inventory_master_specifier_id"] )
		{
			$res = array();
			if( $compAttrVal["ims_input_type"] == "JW_CS" || $compAttrVal["ims_input_type"] == "JW_SS1" ||
				$compAttrVal["ims_input_type"] == "JW_SS2" || $compAttrVal["ims_input_type"] == "JW_SSS" )
			{
				$res['product_side_stone'.$inventory_master_specifier_id.'_weight'] = $resP["product_center_stone_weight"];
				$res['pss'.$inventory_master_specifier_id.'_diamond_shape_id'] = $resP["pcs_diamond_shape_id"];
				$res['product_side_stone'.$inventory_master_specifier_id.'_size'] = $resP["product_center_stone_size"];
				$res['product_side_stone'.$inventory_master_specifier_id.'_total'] = $resP["product_center_stone_total"];
				$res['side_stone'.$inventory_master_specifier_id.'_idArr'] = $center_stone_idArr;
			}
			elseif( $compAttrVal["ims_input_type"] == "JW_MTL" )
			{
				$res['side_stone'.$inventory_master_specifier_id.'_idArr'] = $metal_price_idArr;
			}
			elseif( $compAttrVal["ims_input_type"] == "TXT" )
			{
				$res['product_side_stone'.$inventory_master_specifier_id.'_size'] = $resP["product_center_stone_size"];
			}
			elseif( $compAttrVal["ims_input_type"] == "SEL" || $compAttrVal["ims_input_type"] == "CHK" || $compAttrVal["ims_input_type"] == "RDO" )
			{
				$sql = "SELECT pcs_diamond_shape_id FROM product_center_stone WHERE product_id=".$resP["product_id"]." AND
						inventory_master_specifier_id=".$inventory_master_specifier_id." AND product_center_stone_status=0";
			
				$res['pss'.$inventory_master_specifier_id.'_diamond_shape_id'] = getDropDownAry( $sql, "pcs_diamond_shape_id", "pcs_diamond_shape_id", '', false);
			}
				
			return $res;	
		}
		else if( $inventory_master_specifier_id == $resP["ss1_inventory_master_specifier_id"] )
		{
			$res = array();
			if( $compAttrVal["ims_input_type"] == "JW_CS" || $compAttrVal["ims_input_type"] == "JW_SS1" ||
				$compAttrVal["ims_input_type"] == "JW_SS2" || $compAttrVal["ims_input_type"] == "JW_SSS" )
			{
				$res['product_side_stone'.$inventory_master_specifier_id.'_weight'] = $resP["product_side_stone1_weight"];
				$res['pss'.$inventory_master_specifier_id.'_diamond_shape_id'] = $resP["pss1_diamond_shape_id"];
				$res['product_side_stone'.$inventory_master_specifier_id.'_size'] = $resP["product_side_stone1_size"];
				$res['product_side_stone'.$inventory_master_specifier_id.'_total'] = $resP["product_side_stone1_total"];
				$res['side_stone'.$inventory_master_specifier_id.'_idArr'] = $side_stone1_idArr;
			}
			elseif( $compAttrVal["ims_input_type"] == "JW_MTL" )
			{
				$res['side_stone'.$inventory_master_specifier_id.'_idArr'] = $metal_price_idArr;
			}
			elseif( $compAttrVal["ims_input_type"] == "TXT" )
			{
				$res['product_side_stone'.$inventory_master_specifier_id.'_size'] = $resP["product_side_stone1_size"];
			}
			elseif( $compAttrVal["ims_input_type"] == "SEL" || $compAttrVal["ims_input_type"] == "CHK" || $compAttrVal["ims_input_type"] == "RDO" )
			{
				$sql = "SELECT pss1_diamond_shape_id FROM product_side_stone1 WHERE product_id=".$resP["product_id"]." AND
							inventory_master_specifier_id=".$inventory_master_specifier_id." AND product_side_stone1_status=0";
					
				$res['pss'.$inventory_master_specifier_id.'_diamond_shape_id'] = getDropDownAry( $sql, "pss1_diamond_shape_id", "pss1_diamond_shape_id", '', false);
			}
				
			return $res;	
		}
		else if( $inventory_master_specifier_id == $resP["ss2_inventory_master_specifier_id"] )
		{
			$res = array();
			if( $compAttrVal["ims_input_type"] == "JW_CS" || $compAttrVal["ims_input_type"] == "JW_SS1" ||
				$compAttrVal["ims_input_type"] == "JW_SS2" || $compAttrVal["ims_input_type"] == "JW_SSS" )
			{
				$res['product_side_stone'.$inventory_master_specifier_id.'_weight'] = $resP["product_side_stone2_weight"];
				$res['pss'.$inventory_master_specifier_id.'_diamond_shape_id'] = $resP["pss2_diamond_shape_id"];
				$res['product_side_stone'.$inventory_master_specifier_id.'_size'] = $resP["product_side_stone2_size"];
				$res['product_side_stone'.$inventory_master_specifier_id.'_total'] = $resP["product_side_stone2_total"];
				$res['side_stone'.$inventory_master_specifier_id.'_idArr'] = $side_stone2_idArr;
			}
			elseif( $compAttrVal["ims_input_type"] == "JW_MTL" )
			{
				$res['side_stone'.$inventory_master_specifier_id.'_idArr'] = $metal_price_idArr;
			}
			elseif( $compAttrVal["ims_input_type"] == "TXT" )
			{
				$res['product_side_stone'.$inventory_master_specifier_id.'_size'] = $resP["product_side_stone2_size"];
			}
			elseif( $compAttrVal["ims_input_type"] == "SEL" || $compAttrVal["ims_input_type"] == "CHK" || $compAttrVal["ims_input_type"] == "RDO" )
			{
				$sql = "SELECT pss2_diamond_shape_id FROM product_side_stone2 WHERE product_id=".$resP["product_id"]." AND
							inventory_master_specifier_id=".$inventory_master_specifier_id." AND product_side_stone2_status=0";
					
				$res['pss'.$inventory_master_specifier_id.'_diamond_shape_id'] = getDropDownAry( $sql, "pss2_diamond_shape_id", "pss2_diamond_shape_id", '', false);
			}
				
			return $res;
		}
		else if( isset($resP["mt_inventory_master_specifier_id"]) && $inventory_master_specifier_id == $resP["mt_inventory_master_specifier_id"] )
		{
			$res = array();
			$res['side_stone'.$inventory_master_specifier_id.'_idArr'] = $metal_price_idArr;
				
			return $res;
		}
		else 
		{
			$k = associative_array_search($product_side_stonesData, "inventory_master_specifier_id", $inventory_master_specifier_id);
			if( $k !== FALSE )
			{
				$tempData = $product_side_stonesData[$k];
				
				$res = array();
				
				if( $compAttrVal["ims_input_type"] == "JW_CS" || $compAttrVal["ims_input_type"] == "JW_SS1" ||
					$compAttrVal["ims_input_type"] == "JW_SS2" || $compAttrVal["ims_input_type"] == "JW_SSS" )
				{
					$sql = "SELECT category_id, category_id FROM product_side_stones WHERE product_id=".$resP["product_id"]." AND
							inventory_master_specifier_id=".$inventory_master_specifier_id." AND product_side_stones_status=0";
						
					$res['product_side_stone'.$inventory_master_specifier_id.'_weight'] = $tempData["product_side_stones_weight"];
					$res['pss'.$inventory_master_specifier_id.'_diamond_shape_id'] = $tempData["psss_diamond_shape_id"];
					$res['product_side_stone'.$inventory_master_specifier_id.'_size'] = $tempData["product_side_stones_size"];
					$res['product_side_stone'.$inventory_master_specifier_id.'_total'] = $tempData["product_side_stones_total"];
					$res['side_stone'.$inventory_master_specifier_id.'_idArr'] = getDropDownAry( $sql, "category_id", "category_id", '', false);
				}
				elseif( $compAttrVal["ims_input_type"] == "JW_MTL" )
				{
					return FALSE;
				}
				elseif( $compAttrVal["ims_input_type"] == "TXT" )
				{
					$res['product_side_stone'.$inventory_master_specifier_id.'_size'] = $tempData["product_side_stones_size"];
				}
				elseif( $compAttrVal["ims_input_type"] == "SEL" || $compAttrVal["ims_input_type"] == "CHK" || $compAttrVal["ims_input_type"] == "RDO" )
				{
					$sql = "SELECT psss_diamond_shape_id FROM product_side_stones WHERE product_id=".$resP["product_id"]." AND
							inventory_master_specifier_id=".$inventory_master_specifier_id." AND product_side_stones_status=0";
						
					$res['pss'.$inventory_master_specifier_id.'_diamond_shape_id'] = getDropDownAry( $sql, "psss_diamond_shape_id", "psss_diamond_shape_id", '', false);
				}
				
				
				return $res;
				
			}
			else 
			{
				return FALSE; 
			}
		}
	}
	
/**
 * Checkbox Field rendering from master attributes array
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	bool
 * @param	string
 * @return	string
 */
	function form_checkboxArry($name, $product_attributeArr, $checked = FALSE, $extra = '')
	{
		$html = "";
		
		if( !isEmptyArr($product_attributeArr) )
		{
			foreach ($product_attributeArr as $k=>$ar)
			{
				$html .= form_checkbox( $name, $k, $checked, ' id="'.$name.'_'.$k.'" ').' <label for="'.$name.'_'.$k.'">'.$ar.'</label> <br>';
			}
		}
		
		return $html;
	}

	/**
	 * Radio Field rendering from master attributes array
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	bool
	 * @param	string
	 * @return	string
	 */
	function form_radioArry($name, $product_attributeArr, $checked = FALSE, $extra = '')
	{
		$html = "";
		
		if( !isEmptyArr($product_attributeArr) ) 
		{
			foreach ($product_attributeArr as $k=>$ar)
			{
				$html .= form_radio( $name, $k, $checked, ' id="'.$name.'_'.$k.'" ').' <label for="'.$name.'_'.$k.'">'.$ar.'</label> <br>';
			}
		}
		
		return $html;
	}
	
	/**
	 * function will return component and attribute array 
	 */
	function getcompAttrArr( $is_status_bound=true, $inventory_type_id=0 )
	{
		$CI =& get_instance();
		$sql = '';
		if( empty($inventory_type_id) )
		{
			$inventory_type_id = inventory_typeIdForKey( $CI->session->userdata("IT_KEY") );	
		}
		$where = " ims.inventory_type_id=".$inventory_type_id." ";
		
		if( MANUFACTURER_ID == 7 )
		{
			if( $is_status_bound )
			{
				$where .= " AND ims.ims_status=0 "; 
			}
			
			$sql = " SELECT ims.inventory_master_specifier_id, ims.ims_input_type, ims.ims_tab_label, ims.ims_fieldset_label, ims.ims_input_label,
						 ims.ims_default_value
						 FROM  inventory_master_specifier ims
						 WHERE ".$where." 
	   					 ORDER BY ims.inventory_master_specifier_id ";
		}
		else
		{
			if( $is_status_bound )
			{
				$where .= " AND imsc.ims_status=0 ";
			}
				
			$sql = " SELECT ims.inventory_master_specifier_id, ims.ims_input_type, ims.ims_tab_label, ims.ims_fieldset_label, imsc.ims_input_label,
						 imsc.ims_default_value
						 FROM  inventory_master_specifier ims
						 INNER JOIN  inventory_master_specifier_cctld imsc
						 ON ( imsc.manufacturer_id=".MANUFACTURER_ID." AND imsc.inventory_master_specifier_id=ims.inventory_master_specifier_id )
						 WHERE ".$where." 
	   					 ORDER BY ims.inventory_master_specifier_id ";
		}
		
		return executeQuery( $sql );
	}

	/**
	 * function will delete atributes from side stones table. <br>
	 * it actually does not delete them instead just sets the status to 1 for all, it is kind of hack not standard approach.
	 * 
	 * On 13-04-2015 changed to delete all attribute at once
	 */
	function deleteCompAttrArr( $product_id, $product_stone_number, $compAttrVal )
	{
		$CI =& get_instance(); 
		
// 		if( $product_stone_number == 0 )
// 		{
// 			$CI->db->where("product_id",$product_id)
// 			->where('inventory_master_specifier_id', $compAttrVal["inventory_master_specifier_id"])
// 			->update("product_center_stone", array("product_center_stone_status"=>1) );
// 		}
// 		else if( $product_stone_number <= 2 )
// 		{
// 			$CI->db->where("product_id",$product_id)
// 			->where('inventory_master_specifier_id', $compAttrVal["inventory_master_specifier_id"])
// 			->update("product_side_stone".$product_stone_number, array("product_side_stone".$product_stone_number."_status"=>1) );
// 		}
// 		else 
// 		{
// 			/**
// 			 * here there is chance of same inventory_master_specifier_id is saved in more then one side_stone numbers, <br>
// 			 * but it will not be possible since deletion inventory_master_specifier is disabled in <br> 
// 			 * inventory_master_specifier module.  
// 			 */
// 			$CI->db->where("product_id",$product_id)
// 				   ->where('inventory_master_specifier_id', $compAttrVal["inventory_master_specifier_id"])
// 				   ->update("product_side_stones", array("product_side_stones_status"=>1) );
// 		}

		$CI->db->where("product_id",$product_id)
		->update("product_center_stone", array("product_center_stone_status"=>1) );

		$CI->db->where("product_id",$product_id)
		->update("product_side_stone1", array("product_side_stone1_status"=>1) );

		$CI->db->where("product_id",$product_id)
		->update("product_side_stone2", array("product_side_stone2_status"=>1) );
		
		/**
		 * here there is chance of same inventory_master_specifier_id is saved in more then one side_stone numbers, <br>
		 * but it will not be possible since deletion inventory_master_specifier is disabled in <br>
		 * inventory_master_specifier module.
		 */
		$CI->db->where("product_id",$product_id)
		->update("product_side_stones", array("product_side_stones_status"=>1) );
	}
	

	/**
	 * function will delete atributes from side stones table
	 */
	function getCompAttrCategoryField( $compAttrVal, $product_stoneChar )
	{
		if( $compAttrVal["ims_input_type"] == "JW_CS" || $compAttrVal["ims_input_type"] == "JW_SS1" || 
			$compAttrVal["ims_input_type"] == "JW_SS2" || $compAttrVal["ims_input_type"] == "JW_SSS" )
		{
			return "category_id"; 
		}
		elseif( $compAttrVal["ims_input_type"] == "SEL" || $compAttrVal["ims_input_type"] == "CHK" || $compAttrVal["ims_input_type"] == "RDO" )
		{
			return "p".$product_stoneChar."_diamond_shape_id"; 
		}
	}

	/**
	 * @deprecated
	 * function get existing stone number if exists. 
	 */
	function getExistingStoneNum( $product_id, $compAttrVal )
	{
		return exeQuery( " SELECT product_stone_number FROM product_side_stones WHERE product_id=".$product_id." AND
						   inventory_master_specifier_id=".$compAttrVal["inventory_master_specifier_id"]." ", 
						   true, "product_stone_number" ); 
	} 
	
	/**
	 * 
	 */
	function deleteProductFromProductPrice($product_id)
	{
		query("DELETE FROM pp_pss_index_map WHERE product_id=".$product_id." ");
		query("DELETE FROM product_price_cctld WHERE product_price_id IN 
				(SELECT product_price_id FROM product_price WHERE product_id=".$product_id." ) ");
		query("DELETE FROM product_price WHERE product_id=".$product_id." ");
	}

	/**
	 *
	 */
	function deleteProduct($product_id)
	{
		query("DELETE FROM product_cctld WHERE product_id=".$product_id." ");
		query("DELETE FROM product WHERE product_id=".$product_id." ");
	}
	
	/***************************************** Warehouse functions **********************************************/
	/**
	 * @namespace he: wr => hewr_ Cloudwebs warehouse module
	 */
	
	
	/**
	 * adds warehouse transaction
	 */
	function hewr_addWarehouseTransactions($product_id, $qty, $netRate, $reflectiveRate, $data)
	{
		$CI =& get_instance(); 
		
		$CI->db->insert("warehouse_transactions",$data);
				
		$warehouse_transactions_id = $CI->db->insert_id();
		
		hewr_processWarehouseTransactions($product_id, $qty, $netRate, $reflectiveRate); 
		
		return $warehouse_transactions_id; 
	}

	/**
	 * edits warehouse transaction
	 */
	function hewr_editWarehouseTransactions($product_id, $warehouse_transactions_id, $qty, $netRate, $reflectiveRate, $data, $is_order_edit=false)
	{
		$CI =& get_instance();
		/**
		 * fetchOld Transaction of wt_id
		 */
		$wt = fetchRow("SELECT wt_qty, wt_rate, wt_rateReflective FROM warehouse_transactions 
										   WHERE warehouse_transactions_id=".$warehouse_transactions_id." "); 

		/**
		 * do negative of old qty to reset old transaction
		 */
		hewr_processWarehouseTransactions($product_id, -$wt["wt_qty"], $wt["wt_rate"], $wt["wt_rateReflective"]); 
		
		/**
		 * netRateLcl = netRateLclOld if new is 0
		 * reflectiveRateLcl = reflectiveRateLclOld if new is 0
		 */
		if( $is_order_edit )
		{
			if( empty($netRate) )
			{
				$netRate = $wt["wt_rate"]; 
			}
			
			if( empty($reflectiveRate) )
			{
				$reflectiveRate = $wt["wt_rateReflective"];
			}
		}
		
		//
		hewr_processWarehouseTransactions($product_id, $qty, $netRate, $reflectiveRate);
		
		$CI->db->set('wt_modified_date', 'NOW()', FALSE);
		$CI->db->where("warehouse_transactions_id", $warehouse_transactions_id)->update("warehouse_transactions",$data);
	}

	/**
	 * processes warehouse transaction
	 */
	function hewr_processWarehouseTransactions($product_id, $qtyLcl, $netRateLcl, $reflectiveRateLcl)
	{
		$product = fetchRow("SELECT p.product_price, pv.product_value_quantity, pv.pv_reflective_price 
							 FROM product p INNER JOIN product_value pv
							 ON pv.product_id=p.product_id
							 WHERE p.product_id=".$product_id." "); 
		$oldQty = $product["product_value_quantity"];
		$qty = $oldQty + $qtyLcl;

		
		/**
		 * net rate
		 */
		$netRate = $product["product_price"];
		
		/**
		 * when qty reaches 0, just update the effective rate and not devide by qty to avoid devide by Zero.
		 */
		if( !empty($qty) )
		{
			$netRate = (
						($netRate * $oldQty)
						+
						($netRateLcl * $qtyLcl)
					   )
					   /
					   $qty;
		}
		else 
		{
			$netRate = (
						($netRate * $oldQty)
						+
						($netRateLcl * $qtyLcl)
					   );
		}
		$netRate = round($netRate, 3);

		
		/**
		 * reflective rate
		 */
		$reflectiveRate = $product["pv_reflective_price"];
		
		/**
		 * when qty reaches 0, just update the effective rate and not devide by qty to avoid devide by Zero.
		 */
		if( !empty($qty) )
		{
			$reflectiveRate = (
								($reflectiveRate * $oldQty)
								+
								($reflectiveRateLcl * $qtyLcl)
							  )
							  /
							  $qty;
		}
		else 
		{
			$reflectiveRate = (
								($reflectiveRate * $oldQty)
								+
								($reflectiveRateLcl * $qtyLcl)
							  );
		}
		$reflectiveRate = round($reflectiveRate, 3);
		
		/**
		 * update qty , prices in DB
		 */
		query("UPDATE product p SET p.product_price=".$netRate." WHERE p.product_id=".$product_id." "); 
		query("UPDATE product_value pv SET pv.product_value_quantity=".$qty.", pv.pv_reflective_price=".$reflectiveRate." WHERE pv.product_id=".$product_id." "); 
	}
	
	/***************************************** Warehouse functions end **********************************************/

	
	/***************************************** Customer account BUCKS functions **********************************************/
	/**
	 * @namespace he: cam => hecam_ customer account management of BUCKS module 
	 */
	

	/**
	 * @author   Cloudwebs
	 * save or update BUCKS transaction. 
	 * Both 1. affiliate sale AND 2. Purchase using BUCKS managed at order level using customer_id and order_id reference. 
	 * In which 1 is credited when applicable, while 2 is debited
	 */
	function hecam_bucksTransaction( $is_front_end, $customer_account_manage_id, $customer_id, $order_id, $order_details_id, $customer_account_manage_credit, $customer_account_manage_debit, $customer_account_manage_entry_type )
	{
		$data = array();

		$data["customer_id"] = $customer_id;
		$data["order_id"] = $order_id;
		$data["order_details_id"] = $order_details_id;
		$data["customer_account_manage_credit"] = $customer_account_manage_credit;
		$data["customer_account_manage_debit"] = $customer_account_manage_debit;
		$data["customer_account_manage_entry_type"] = $customer_account_manage_entry_type;

		/**
		 * 
		 */
		if( empty( $customer_account_manage_id ) )
		{
			if( !empty( $order_details_id ) )
			{
				$customer_account_manage_id = exeQuery( "SELECT customer_account_manage_id FROM customer_account_manage
												     WHERE customer_id=".$customer_id." AND order_id=".$order_id."
													 AND order_details_id=".$order_details_id."
													 AND customer_account_manage_entry_type=".$customer_account_manage_entry_type." ",
						true, "customer_account_manage_id" );
			}
			else
			{
				$customer_account_manage_id = exeQuery( "SELECT customer_account_manage_id FROM customer_account_manage
												 	 WHERE customer_id=".$customer_id." AND order_id=".$order_id."
													 AND customer_account_manage_entry_type=".$customer_account_manage_entry_type." ", true, "customer_account_manage_id" );
			}
		}
		
		/**
		 * 
		 */
		if( !empty( $customer_account_manage_id ) )
		{
			hecam_editBucksTransactions($customer_account_manage_id,$customer_account_manage_credit, $customer_account_manage_debit, $data);
			return $customer_account_manage_id;  
		}
		else
		{
			return hecam_addBucksTransactions($customer_account_manage_credit, $customer_account_manage_debit, $data);
		}
	}
	
	/**
	 * adds warehouse transaction
	 */
	function hecam_addBucksTransactions($crAmtLcl, $drAmtLcl, $data)
	{
		$CI =& get_instance();
	
		$CI->db->insert("customer_account_manage",$data);
	
		$customer_account_manage_id = $CI->db->insert_id();

		if( isExplicitCreatedDate() )
		{
			//update created date
			$CI->db->where('customer_account_manage_id', $customer_account_manage_id);
			$CI->db->update('customer_account_manage', array( "customer_account_manage_created_date" => mysqlTimestamp() ) );
		}
		
		
		//process transaction
		hecam_processBucksTransactions( $crAmtLcl, $drAmtLcl, $data["customer_id"]); 
	
		return $customer_account_manage_id;
	}
	
	/**
	 * edits warehouse transaction
	 */
	function hecam_editBucksTransactions($customer_account_manage_id, $crAmtLcl, $drAmtLcl, $data)
	{
		$CI =& get_instance();
	
		/**
		 * fetchOld Transaction of $customer_account_manage_id
		*/
		$cam = fetchRow("SELECT customer_account_manage_credit, customer_account_manage_debit FROM customer_account_manage WHERE customer_account_manage_id=".$customer_account_manage_id." ");
		
		/**
		 * do negative of old cr and dr Amts to reset old transaction
		*/
		hecam_processBucksTransactions( -$cam["customer_account_manage_credit"], -$cam["customer_account_manage_debit"], $data["customer_id"]);
	
		//
		hecam_processBucksTransactions( $crAmtLcl, $drAmtLcl, $data["customer_id"]);
	
		//update table
		if( isExplicitCreatedDate() )
		{
			$data["customer_account_manage_created_date"] = mysqlTimestamp(); 
		}
		$CI->db->where("customer_account_manage_id", $customer_account_manage_id)->update("customer_account_manage",$data);
	}
	
	/**
	 * processes BUCKS transaction
	 */
	function hecam_processBucksTransactions( $crAmtLcl, $drAmtLcl, $customer_id)
	{
		$customer_bucks = getCustBalance( $customer_id );
		$customer_bucks = $customer_bucks + ( $crAmtLcl );
		$customer_bucks = $customer_bucks - ( $drAmtLcl );
		
		/**
		 * update customer_bucks
		*/
		query("UPDATE customer c SET c.customer_bucks=".$customer_bucks." WHERE c.customer_id=".$customer_id." ");
	}
	
	
	/***************************************** Customer account BUCKS functions end **********************************************/
	
	
	
	/***************************************** language functions ***************************************************/

	/**
	 * 
	 */
	function getCurrencyForCountryCode( $country_code )
	{
		$resCurr = executeQuery( " SELECT c.currency_id, c.currency_code, c.currency_symbol, c.currency_value
					   FROM currency c INNER JOIN country co
					   ON co.country_id=c.country_id
					   WHERE co.country_code='".COUNTRY_CODE."' AND c.currency_status=0 ");
				
		if( !empty( $resCurr ) && !empty( $resCurr[0]['currency_value'] ) )
		{
			return $resCurr[0];
		}
		else
		{
			return getDefaultCurrency();
		}
	}
	
	/***************************************** language functions end ***********************************************/
	
?>