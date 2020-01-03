<?php
/**
 * @package pr_: cmndb_hlp 
 * @author Cloudwebs Tech Dev Team
 * @version 1.9
 * @abstract common db helper
 * @copyright Perrian Tech
 */


/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++
	This function will check column exist in database 
	or not? if column exist then return true otherwise
	return false.
	
	@params : $table_name -> name of the table
			  $column_name -> Column name you want to 
			  					check in table.
	@return : TRUE OR FALSE
+++++++++++++++++++++++++++++++++++++++++++++++++++++
*/
function check_db_column($table_name,$column_name)
{
	$CI =& get_instance();
	$sql = "SELECT * 
	FROM information_schema.COLUMNS 
	WHERE 
		TABLE_SCHEMA = '".$CI->db->database."' 
	AND TABLE_NAME = '".$CI->db->dbprefix.$table_name."' 
	AND COLUMN_NAME = '".$column_name."'";
	
	$rows = $CI->db->query($sql)->num_rows();
	if($rows > 0)
		return true;
	else
		return false;
}

/*
+------------------------------------------------------------------+
	@author Cloudwebs
	@return : single row from database
+------------------------------------------------------------------+
*/
function fetchRow( $sql )
{
	$CI =& get_instance();
	return $CI->db->query( $sql )->row_array();
}

/*
+------------------------------------------------------------------+
	@author Cloudwebs
	@return : single row from database
+------------------------------------------------------------------+
*/
function checkIfRowExist( $sql )
{
	$CI =& get_instance();
	$res = $CI->db->query($sql);	
	if( is_object($res) && $res->num_rows() > 0)
	{
		return true;
	}
	else
	{ 
		return false; 
	}
}

/*
+------------------------------------------------------------------+
	@author Cloudwebs
	Function will be use for fetch data from table based on query. 
	@params-> $sql : sql query to be executed.
	@return : array of rows
+------------------------------------------------------------------+
*/
function executeQuery($sql)
{
	$CI = & get_instance();
	$res = $CI->db->query($sql);	
	if( is_object($res) && $res->num_rows() > 0)
	{
		$result =$res->result_array();
		return $result;
	}
	else
	{ return ''; }
}

/*
+------------------------------------------------------------------+
	Function will be use for single field value. 
Input ->
	@params-> $field : Name of field you want to fetch.
			  $table : Name of table
			  $wh : Where condition field name 
			  $cond : condition operator value.
+------------------------------------------------------------------+
*/
function getField($field,$table,$wh,$cond,$is_use_cctld=true)
{
	$CI = & get_instance();
	
	$CI->db->select($field,FALSE);
	if($is_use_cctld && $table == 'configuration' && MANUFACTURER_ID != 7)
	{
		$table = 'configuration_cctld';
		$CI->db->where('manufacturer_id',MANUFACTURER_ID);
	}
	
	if($wh || $cond)
		$CI->db->where($wh,$cond);		
		
	//checking is there del_in field exist
	//if(check_db_column($table,'del_in'))
		//$CI->db->where('del_in','0');
		
	$res = $CI->db->get($table);	
	//if we want some aggreagration then we pass field name in $wh
	if( is_object( $res ) && $res->num_rows() > 0 ) 
	{ 
		$result = $res->row_array(); 
	}
	else
	{ 
		return '';
	}	
	
	unset($CI);
	//echo $result['new_order'];die;
	if(count($result) > 0)
		return $result[$field];
	else
		return '';
}

/*
+------------------------------------------------------------------+
	Function will return array which is usable for codeigniter 
	Form_dropdown function
	@params-> $sql : Query to database
			  $keyField : Key field you want to get as value
			  $valueField : Option you want to make appear at dropdown
+------------------------------------------------------------------+
*/
function getDropDownAry($sql,$keyField,$valueField,$dropDown = array(),$encode = false)
{
	$CI =& get_instance();
	
	$result = $CI->db->query($sql)->result_array();
	foreach($result as $res)
	{
		$key = ($encode) ? _en($res[$keyField]) : $res[$keyField];
		$dropDown[$key] = $res[$valueField];
	}

	return (array)$dropDown;
}

/*
+----------------------------------------------------------------------+
	Function will get Get temaplate key as parameter and find apprprtiate
	temaplte for it, then parse it using php variable and prepare a string
	of HTML, that we can send using sendMail function.
	$params => $template_key = Static key that we defined for template.
				$data = for parse keyword that we have in template.
+----------------------------------------------------------------------+
*/	
function getTemplateDetailAndSendMail($template_key,$data = array())
{
	$CI =& get_instance();
	
	//getting template detail from key
	$template_detail = $CI->db->where('template_key',$template_key)->limit(1)->get('mail_templates')->row_array();	
	
	if(!empty($template_detail))
	{	
	  $CI->load->library('parser');
			  
	  //parse template and returns a string
	  $message = $CI->parser->parse_string($template_detail['template_content'],$data,TRUE);
	  $message = _pwu($message); // parsing with some predefined configuration
	  
	  sendMail($data['email_address'],$template_detail['template_subject'],$message); // send mail
	}
	else
	  show_error('Template is not configured, please configure it first.','500');
}

/**
 * @author Cloudwebs
 * @abstract Function will execute query and return row or column as desired
 */	
	function exeQuery( $sql, $isCol=false, $colName='' )
	{
		$res = executeQuery( $sql );
		if( isset( $res[0] ))
		{
			if( $isCol )
			{
				return $res[0][$colName];				
			}
			else
			{
				return $res[0];
			}
		}
		else
		{
			return FALSE;	
		}
	}


/**
 * @author Cloudwebs
 * @abstract function will fetch ID for key only for used for RapNet diamond features to map similar keys to relevant ID's
 * @return array of ID's
 */
	function fetchIdArrForKey( $key, $idField, $nameField, $keyField, $tableName )
	{
		$CI =& get_instance();
		$returnArr = array();
		$key = strtoupper( $key );
		$res = $CI->db->query(" SELECT ".$idField." FROM ".$tableName." WHERE ".$keyField." IN (".$key.") ")->result_array();
		foreach( $res as $k=>$ar )
		{
			$returnArr[] = $ar[ $idField ];
		}
		return $returnArr;
	}
		
/**
 * @author Cloudwebs
 * @abstract function will fetch ID for key and insert if not exist
 */
	function fetchKeyId( $key, $idField, $nameField, $keyField, $tableName, &$idArr )
	{
		$key = strtoupper( $key );
		if( !isset($idArr[ $key ]) )
		{
			$CI =& get_instance();
			$CI->db->insert( $tableName, array($nameField=>$key, $keyField=>$key));
			$idArr[$key] = $CI->db->insert_id();
			//echo $idField.' = '.$key.' inserted in '.$tableName.' <br>';
			return $idArr[$key];
		}
		else
		{
			//echo ' Key '.$key.' not inserted in '.$tableName.' <br>';
			return $idArr[$key];
		}
	}
		
/**
 * @author Cloudwebs
 * @abstract function will execute query and return array of key and ID mapping 
 */
	function fetchKeyIdArr( $sql, $idColName='', $keyColName='' )
	{
		$res = executeQuery( $sql );
		if( isset($res[0]) )
		{
			$resArr = array();
			foreach ( $res as $k=>$ar )
			{
				$resArr[ strtoupper( $ar[ $keyColName ] ) ] = $ar[ $idColName ];
			}
			return $resArr;
		}
	}

/**
 * @author Cloudwebs
 * @abstract function will execute query and return array of key and corresponding row for key
 */
	function fetchKeyArr( $sql, $keyColName='' )
	{
		$res = executeQuery( $sql );
		if( isset($res[0]) )
		{
			$resArr = array();
			foreach ( $res as $k=>$ar )
			{
				$resArr[ $ar[ $keyColName ] ] = $ar;
			}
			return $resArr;
		}
	}

	/**
	 * Function will find and replace for " 's and append string"
	 */
	function characterReplace($table, $fld, $id)
	{
		$CI =& get_instance();
		$res = executeQuery("select ".$id.",".$fld." from ".$table." ");
		
		$data = array();
		foreach($res as $k=>$v)
		{
			if(strpos($v[$fld],"'s") != FALSE)
			{
				$oldstr = $v[$fld];
				$newstr = str_replace("'s","",$v[$fld]);
				$data = array(
					$fld => $oldstr.','.$newstr
				);			
				
				$CI->db->where($id,$v[$id]);
				$CI->db->update($table,$data);
				
				echo $v[$id]."<br>";
			}			
		}
		echo "Successfully updated.";
	}
	
	/**
	 * Function will find and replace for " India to australia"
	 */
	function stringReplaceCommon($table, $fld, $id, $strMatch, $strReplace="")
	{
		$CI =& get_instance();
		$res = executeQuery("select ".$id.",".$fld." from ".$table." WHERE ". $fld. " LIKE '%".$strMatch."%' ");
		
		$data = array();
		if(!empty($res))
		{
			$cnt=0;
			foreach($res as $k=>$v)
			{
				if(strpos($v[$fld],$strMatch) !== FALSE)
				{
					$oldstr = $v[$fld];
					$newstr = str_replace($strMatch,@$strReplace,$v[$fld]);
					
					$data = array(
						$fld => $newstr
					);			
					
					$CI->db->where($id,$v[$id]);
					$CI->db->update($table,$data);
					
					$cnt++;
					echo $v[$id]."<br>";
				}			
			}		
		echo "Successfully ".$cnt." updated.";
		}
		else
			echo "No Results Found.";
	}


/*
++++++++++++++++++++++++++++++++++++++++++++++
	This function return error message according
	to error code.
	@params : $error_code : 5-6 digit code. if 
	code is not found then return unknown error..
++++++++++++++++++++++++++++++++++++++++++++++
*/
	function getErrorMessageFromCode($error_code)
	{
		$message = getField('error_message','error_codes','error_code',$error_code);
		
		if($message == '')
			return 'Unknown error.';
		else
			return $message;
	}
	
	/**
	 * @abstract executes query
	 */
	function query( $sql )
	{
		$CI =& get_instance();
		return $CI->db->query( $sql );
	}
	
	/**
	 */
	function z( $table, $data )
	{
		$CI = & get_instance();
		$CI->db->insert( $table, $data);
		return $CI->db->insert_id();
	}
	
	/**
	 */
	function updateQuery( $table, $data, $id_key, $id_val )
	{
		$CI = & get_instance();
		$CI->db->where( $id_key, $id_val )->update( $table, $data);
	}
	
	/**
	 * @author Cloudwebs
	 * @abstract function will fetch field and insert if not exist
	 */
	function fetchFieldIfExist( $key, $idField, $keyField, $tableName, $data=array() )
	{
	
		$id = exeQuery( " SELECT ".$idField." FROM ".$tableName." WHERE ".$keyField."='".$key."' ", true, $idField );
		if( empty( $id ) )
		{
			$CI =& get_instance();
			$data[$keyField] = $key;
			$CI->db->insert( $tableName, $data);
			$id = $CI->db->insert_id();
		}
	
		return $id;
	}
	
	/**
	 * @abstract return project feed data
	 */
	function getProjectFeedData( $user_id )
	{
		$res['invitation_result'] = executeQuery( "SELECT u.username, u.logo, p.project_name, pi.invite_date as date FROM project_invitation pi
													INNER JOIN users u ON ( u.id=pi.sender_id )
													INNER JOIN projects p ON ( p.id=pi.project_id )
													WHERE pi.receiver_id=".$user_id." " );
	
		$res['comments_result'] = executeQuery( "SELECT u.username, u.logo, p.project_name, pd.subject, pd.comment, pd.date FROM project_discussion pd
													INNER JOIN projects p ON ( p.id=pd.project_id )
													INNER JOIN users u ON ( u.id=p.creator_id )
													WHERE p.creator_id=".$user_id." " );
	
		return $res;
	}
	
	/**
	 * @abstract return id  for key value from particular db table separated by pipes
	 */
	function keyStrToIds( $keys, $table, $keyfield, $idfield, $del="|" )
	{
		$resStr = "";
		if( !empty($keys) )
		{
			$where = "";
			$keyArr = explode( $del, $keys );
			foreach( $keyArr as $k=>$ar )
			{
				$where .= "'".$ar."', ";
			}
			$where = substr( $where, 0, -2);
				
			$res = executeQuery( " SELECT ".$idfield." FROM ".$table." WHERE ".$keyfield." IN ( ".$where." ) " );
			if( !empty( $res ) )
			{
				foreach($res as $k=>$ar)
				{
					$resStr .= $ar[$idfield]."|";
				}
	
				return substr( $resStr, 0, -1);
			}
		}
	}
	
	/**
	 * function will check if foreign key exist in specified tables
	 */
	function checkIfForeignKeyExist( $tables, $fKey, $fKeyValue )
	{
		foreach($tables as $key=>$val)
		{
			if( checkIfRowExist( "SELECT 1 FROM ".$val." WHERE ".$fKey."=".$fKeyValue." " ) )
			{
				$returnArr['type'] ='error';
				$returnArr['msg'] = "One of your specified item could not be deleted, it is used in module ".getModuleNameForDBTable($val).".<br>
									 Delete item from that module first if you wish to delete this item.";
				return $returnArr;
			}
		}
		return array();
	}

	/**
	 * function will check if foreign key(multiple keys) exist in specified tables
	 */
	function checkIfForeignKeyExistMultiple( $tables, $fKeyArr )
	{
		$where = "";
		foreach ($fKeyArr as $k=>$ar)
		{ 
			$where .= $k."=".$ar." AND "; 	
		}
		$where = substr($where, 0, -4);
		
		foreach($tables as $key=>$val)
		{
			if( checkIfRowExist( "SELECT 1 FROM ".$val." WHERE ".$where ) )
			{
				$returnArr['type'] ='error';
				$returnArr['msg'] = "One of your specified item could not be deleted, it is used in module ".getModuleNameForDBTable($val).".<br>
									 Delete item from that module first if you wish to delete this item.";
				return $returnArr;
			}
		}
		return array();
	}

	/**
	 */
	function insertQuery( $table, $data )
		{
		$CI = & get_instance();
		$CI->db->insert( $table, $data);
		return $CI->db->insert_id();
	}
		
	/**
	 *
	 */
	function getLanguagesForItemListing( $sel_query )
	{
		$CI =& get_instance();
		$res = $CI->db->query("SELECT ".( !empty($sel_query) ? $sel_query.", " : "" )." manufacturer_id, manufacturer_name, manufacturer_key
							   FROM manufacturer WHERE manufacturer_status=0 ");
		
		return $res->result_array();
	}
	
	/**
	 *
	 */
	function getInventoryListing()
	{
		$CI =& get_instance();
		$res = $CI->db->query("SELECT inventory_type_id, it_name, it_key FROM inventory_type WHERE it_status=0 ");
		return $res->result_array();
	}
?>