<?php
	/**
	 * cache configs
	 */
	function cacheConfig()
	{
		return array( 'adapter' => 'apc', 'backup' => 'file'); 
	}

	/*
	 * Function cahce key specific to department/store/ccTLD
	*/
	function cacheKey( $key )
	{
		return $key."_".MANUFACTURER_ID.getFirstSubDomain();
	}

	function get_cache( $key ) 
	{
		$CI =& get_instance(); 
		return $CI->cache->get( $key );
	}

	function save_cache( $key, $cache_content, $cache_time )
	{
		$CI =& get_instance();
		$CI->cache->save( $key, $cache_content, $cache_time );
	}
		
	/**
	 * function will save cached key in tempo table for reference when it needs be removed due to updation opf contents
	 */
	function saveCacheKey( $c_key, $c_key_group='' )
	{
		$CI =& get_instance();
	
		$CI->db->insert( "pr_cache", array( 'c_key'=>$c_key, 'c_key_group'=>$c_key_group));
	}
	
	/**
	 * function will remove cached key through referring tempo table: such action only invoked when there is updation of contents for particular group
	 */
	function removeCacheKey( $c_key='', $c_key_group='' )
	{
		$CI =& get_instance();
	
		$res = array();
		if( $c_key_group != '' )
		{
			$res = $CI->db->query( " SELECT c_key FROM pr_cache WHERE c_key_group='".$c_key_group."' " )->result_array();
			$CI->db->query( " DELETE FROM pr_cache WHERE c_key_group='".$c_key_group."' " );
		}
		else if( $c_key != '' )
		{
			$res = array( 'c_key'=>$c_key );
			$CI->db->query( " DELETE FROM pr_cache WHERE c_key='".$c_key."' " );
		}
	
		//cache driver
		//$CI->load->driver( 'cache', array( 'adapter' => 'apc', 'backup' => 'file'));	//moved to CI_Controller no need to load here
		if( is_array($res) && sizeof($res) > 0 )
		{
			foreach( $res as $k=>$ar )
			{
				$CI->cache->delete( $ar['c_key'] );
			}
		}
	
	}
	
	/*
	 * Function get filter query id to be used to identify in cache
	* @param $type f-> filter , c-> subCategory page , d-> diamonds filter
	*/
	function queryId( $query, $type='f' )
	{
		return $type."_".MANUFACTURER_ID."_".crc32 ( $query );
	}
	
?>