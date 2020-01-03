<?php
/**
 * @package pr_: site_map_hlp 
 * @author Cloudwebs Tech Dev Team
 * @version 1.9
 * @abstract contains function that is used to create sitemap which is submitted to variuos search engines 
 * @copyright Perrian Tech
 */


	function CallSitemapFunctions( $CI, $root_path, $NoOfRecordsPerSitemapI, $idTemp, $SiteMapForI, &$SitemapFileNoI, $tagArr)
	{
		$IsProcessCompletedB = false;
		$BodyS = "";
	
		if( $SiteMapForI == 1 ) 	  //product_categories
		{
			if( MANUFACTURER_ID == 7 )
			{
				$res = executeQuery( " SELECT pc.category_id, pc.category_alias, DATE_FORMAT(category_modified_date, '%Y-%m-%d') as 'ModifiedOn' , 
										DATE_FORMAT( category_created_date, '%Y-%m-%d') as 'CreatedOn'  
										FROM product_categories pc INNER JOIN product_category_map pcm 
										ON pcm.category_id=pc.category_id 
										INNER JOIN product p 
										ON p.product_id=pcm.product_id 
										WHERE p.product_status =0 AND category_status = 0
										GROUP BY pc.category_id 
										ORDER BY category_id LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			}
			else
			{
				$res = executeQuery( " SELECT prcc.category_id, prcc.category_alias, DATE_FORMAT(category_cctld_modified_date, '%Y-%m-%d') as 'ModifiedOn' , 
										DATE_FORMAT( category_cctld_created_date, '%Y-%m-%d') as 'CreatedOn' 
										FROM product_categories_cctld prcc INNER JOIN product_category_map pcm 
										ON pcm.category_id=prcc.category_id 
										INNER JOIN product_cctld prc 
										ON ( prc.manufacturer_id=".MANUFACTURER_ID." AND prc.product_id=pcm.product_id ) 
										WHERE prcc.manufacturer_id=".MANUFACTURER_ID." AND prc.product_status =0 AND prcc.category_status = 0 
										GROUP BY prcc.category_id 
										ORDER BY prcc.category_id LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			}
			
									
			if( !empty( $res ) )
			{
				CreateSiteMap( $root_path, $SitemapFileNoI, $res, $SiteMapForI );
				$SitemapFileNoI++;
				$idTemp += $NoOfRecordsPerSitemapI;
				
				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
			else
			{
				$idTemp = 0; 
				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}

		}
		else if( $SiteMapForI == 2 ) //product
		{
			if( MANUFACTURER_ID == 7 )
			{
				$res = executeQuery( " SELECT product_price_id, product_id, DATE_FORMAT( product_price_modified_date, '%Y-%m-%d') as 'ModifiedOn', 
										DATE_FORMAT( product_price_created_date, '%Y-%m-%d') as 'CreatedOn' 
										FROM product_price pp
										WHERE product_price_status = 0 
										ORDER BY product_price_id 
										LIMIT " . $idTemp . ", " . $NoOfRecordsPerSitemapI . " " );
			}
			else
			{
				$res = executeQuery( " SELECT ppc.product_price_id, pp.product_id, DATE_FORMAT( product_price_cctld_modified_date, '%Y-%m-%d') as 'ModifiedOn', 
										DATE_FORMAT( product_price_cctld_created_date, '%Y-%m-%d') as 'CreatedOn' 
										FROM product_price pp 
										INNER JOIN product_price_cctld ppc 
										ON ( ppc.manufacturer_id=".MANUFACTURER_ID." AND ppc.product_price_id=pp.product_price_id )
										WHERE ppc.product_price_status = 0 
										ORDER BY pp.product_price_id 
										LIMIT " . $idTemp . ", " . $NoOfRecordsPerSitemapI . " " );
			}
			
			if( !empty( $res ) )
			{
				CreateSiteMap( $root_path, $SitemapFileNoI, $res, $SiteMapForI );
				$SitemapFileNoI++;
				$idTemp += $NoOfRecordsPerSitemapI;
				
				return array( 'SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp );
			}
			else
			{
				$idTemp = 0; 
				return array( 'SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp );
			}
		
		}
		else if( $SiteMapForI == 3 ) //article
		{
			if( MANUFACTURER_ID == 7 )
			{
				$res = executeQuery( " SELECT article_id, DATE_FORMAT(article_modified_date, '%Y-%m-%d') as 'ModifiedOn' , 
										DATE_FORMAT( article_created_date, '%Y-%m-%d') as 'CreatedOn' 
										FROM article_category c INNER JOIN article a 
										ON a.article_category_id=c.article_category_id 
										WHERE article_status=0 
										AND ( article_category_alias='common' OR article_category_parent_id IN (SELECT article_category_id FROM article_category WHERE article_category_alias='common' ) ) 
										GROUP BY a.article_id 
										ORDER BY article_id LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			}
			else 
			{
				$res = executeQuery( " SELECT ac.article_id, DATE_FORMAT( article_cctld_modified_date, '%Y-%m-%d') as 'ModifiedOn' , 
										DATE_FORMAT( article_cctld_created_date, '%Y-%m-%d') as 'CreatedOn' 
										FROM article_category c INNER JOIN article a 
										ON a.article_category_id=c.article_category_id 
										INNER JOIN article_cctld ac
										ON ( ac.manufacturer_id=".MANUFACTURER_ID." AND ac.article_id=a.article_id )
										WHERE ac.article_status=0 AND (article_category_alias='common' OR article_category_parent_id IN (SELECT article_category_id FROM article_category WHERE article_category_alias='common')) 
										GROUP BY a.article_id 
										ORDER BY ac.article_id LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			}

		
			if( !empty( $res ) )
			{
				CreateSiteMap( $root_path, $SitemapFileNoI, $res, $SiteMapForI );
				$SitemapFileNoI++;
				$idTemp += $NoOfRecordsPerSitemapI;
				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
			else
			{
				$idTemp = 0; 
				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
		
		}
		else if( $SiteMapForI == 4 ) //product tags
		{
			if( MANUFACTURER_ID == 7 )
			{
				$res = executeQuery( " SELECT CONCAT( meta_keyword, ',', product_tags, ',', product_related_keywords) as product_tags, 
										DATE_FORMAT(product_modified_date, '%Y-%m-%d') as 'ModifiedOn', 
										DATE_FORMAT( product_created_date, '%Y-%m-%d') as 'CreatedOn'  
										FROM product p 
										WHERE product_status=0 
										LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			}
			else
			{
				$res = executeQuery( " SELECT CONCAT( p.meta_keyword, ',', p.product_tags, ',', p.product_related_keywords) as product_tags, 
										DATE_FORMAT( product_cctld_modified_date 	, '%Y-%m-%d') as 'ModifiedOn', 
										DATE_FORMAT( product_cctld_created_date, '%Y-%m-%d') as 'CreatedOn'  
										FROM product p 
										INNER JOIN product_cctld prc 
										ON ( prc.manufacturer_id=".MANUFACTURER_ID." AND prc.product_id=p.product_id )
										WHERE prc.product_status=0 
										LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			}
									
			
			if( !empty( $res ) )
			{
				CreateSiteMap( $root_path, $SitemapFileNoI, $res, $SiteMapForI, $tagArr);
				$SitemapFileNoI++;
				$idTemp += $NoOfRecordsPerSitemapI;
				
				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
			else
			{
				$idTemp = 0; 
				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
		}
		else if( $SiteMapForI == 5 ) //static links
		{
			$res = array( 0 =>  array( 'url'=>site_url('jewellery/ready-to-ship'),
										 	'ModifiedOn'=> date('Y-m-d')	
										  ),
							1 =>  array( 'url'=>site_url('solitaires'),
										'ModifiedOn'=> date('Y-m-d')	
									  ),
							2 =>  array( 'url'=>site_url('solitaires/solitairesJewellery?pty=rin'),
										'ModifiedOn'=> date('Y-m-d')	
									  ),
							3 =>  array( 'url'=>site_url('solitaires/solitairesJewellery?pty=ear'),
										'ModifiedOn'=> date('Y-m-d')	
									  ),
							4 =>  array( 'url'=>site_url('solitaires/solitairesJewellery?pty=pen'),
										'ModifiedOn'=> date('Y-m-d')	
									  ),
							5 =>  array( 'url'=>site_url('solitaires/solitairesDiamond'),
										'ModifiedOn'=> date('Y-m-d')	
									  )
							); 
									
			CreateSiteMap( $root_path, $SitemapFileNoI, $res, $SiteMapForI );
			$SitemapFileNoI++;
			$idTemp = 0;
			
			return array( 'SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);

		}
		else if( $SiteMapForI == 6 ) //product category tags
		{
			if( MANUFACTURER_ID == 7 )
			{
				$res = executeQuery( " SELECT meta_keyword as product_category_tags, 
										DATE_FORMAT(category_modified_date, '%Y-%m-%d') as 'ModifiedOn', 
										DATE_FORMAT( category_created_date, '%Y-%m-%d') as 'CreatedOn'  
										FROM product_categories pc 
										WHERE category_status=0 
										LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			}
			else
			{
				$res = executeQuery( " SELECT prcc.meta_keyword as product_category_tags, 
										DATE_FORMAT(category_cctld_modified_date, '%Y-%m-%d') as 'ModifiedOn', 
										DATE_FORMAT( category_cctld_created_date, '%Y-%m-%d') as 'CreatedOn'  
										FROM product_categories pc INNER JOIN product_categories_cctld prcc 
										ON ( prcc.manufacturer_id=".MANUFACTURER_ID." AND prcc.category_id=pc.category_id ) 
										WHERE prcc.category_status=0 
										LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			}
			
			if( !empty( $res ) )
			{
				CreateSiteMap( $root_path, $SitemapFileNoI, $res, $SiteMapForI, $tagArr);
				$SitemapFileNoI++;
				$idTemp += $NoOfRecordsPerSitemapI;
				
				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
			else
			{
				$idTemp = 0; 
				$SiteMapForI++; 

				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
		
		}
		else if( $SiteMapForI == 7 ) //product related_category link 
		{
			if( MANUFACTURER_ID == 7 )
			{
				$res = executeQuery( " SELECT product_related_category_id , 
										DATE_FORMAT(product_modified_date, '%Y-%m-%d') as 'ModifiedOn', 
										DATE_FORMAT( product_created_date, '%Y-%m-%d') as 'CreatedOn'  
										FROM product p 
										WHERE product_status=0 
										LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			}
			else
			{
				$res = executeQuery( " SELECT product_related_category_id , 
										DATE_FORMAT( product_cctld_modified_date, '%Y-%m-%d') as 'ModifiedOn', 
										DATE_FORMAT( product_cctld_created_date, '%Y-%m-%d') as 'CreatedOn'  
										FROM product p INNER JOIN product_cctld prc 
										ON ( prc.manufacturer_id=".MANUFACTURER_ID." AND prc.product_id=p.product_id )
  										WHERE prc.product_status=0 
										LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			}
			
			if( !empty( $res ) )
			{
				CreateSiteMap( $root_path, $SitemapFileNoI, $res, $SiteMapForI, $tagArr);
				$SitemapFileNoI++;
				$idTemp += $NoOfRecordsPerSitemapI;
				
				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
			else
			{
				$idTemp = 0; 
				$SiteMapForI++; 

				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
		
		}
		else if( $SiteMapForI == 8 ) //product filter 
		{
			
			$res = executeQuery( " SELECT article_id, DATE_FORMAT(article_modified_date, '%Y-%m-%d') as 'ModifiedOn' 
									FROM article_category c INNER JOIN article a 
									ON a.article_category_id=c.article_category_id 
									WHERE article_status=0 AND (article_category_alias='common' OR article_category_parent_id IN (SELECT article_category_id FROM article_category WHERE article_category_alias='common')) 
									GROUP BY a.article_id 
									ORDER BY article_id LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			
			if( !empty( $res ) )
			{
				CreateSiteMap( $root_path, $SitemapFileNoI, $res, $SiteMapForI );
				$SitemapFileNoI++;
				$idTemp += $NoOfRecordsPerSitemapI;
				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
			else
			{
				$idTemp = 0; 
				$SiteMapForI++; 
				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
		
		}
		else if( $SiteMapForI == 9 ) //diamond filter
		{
			
			$res = executeQuery( " SELECT article_id, DATE_FORMAT(article_modified_date, '%Y-%m-%d') as 'ModifiedOn' 
									FROM article_category c INNER JOIN article a 
									ON a.article_category_id=c.article_category_id 
									WHERE article_status=0 AND (article_category_alias='common' OR article_category_parent_id IN (SELECT article_category_id FROM article_category WHERE article_category_alias='common')) 
									GROUP BY a.article_id 
									ORDER BY article_id LIMIT " . $idTemp . "," . $NoOfRecordsPerSitemapI . " " );
			
			if( !empty( $res ) )
			{
				CreateSiteMap( $root_path, $SitemapFileNoI, $res, $SiteMapForI );
				$SitemapFileNoI++;
				$idTemp += $NoOfRecordsPerSitemapI;
				
				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
			else
			{
				$idTemp = 0; 
				$SiteMapForI++; 

				return array('SitemapFileNoI'=>$SitemapFileNoI, 'SiteMapForI'=>$SiteMapForI, 'idTemp'=>$idTemp);
			}
		
		}
	}

	function CreateSiteMap( $root_path, &$SitemapFileNoI, $resultQ, $SiteMapForI, $tagArr=array())
	{
		$cnt = 0;
		$fh = fopen( $root_path.'sitemap'.$SitemapFileNoI.'.xml', 'w+' );

		//start xml file tags
		$FileContentS =  '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		foreach($resultQ as $row)
		{
			
			if($SiteMapForI == 1)
			{
				$cnt++;
				$FileContentS .= GenerateProductCategoryMapTag($row);
			}
			else if($SiteMapForI == 2)
			{
				$cnt++;
				$FileContentS .= GenerateProductMapTag($row);
			}
			else if($SiteMapForI == 3)
			{
				$cnt++;
				$FileContentS .= GenerateArticleMapTag($row);
			}
			else if($SiteMapForI == 4)
			{
				$keywordArr = explode(',', $row['product_tags']);
		
				foreach($keywordArr as $k=>$val)
				{
					$val = isKeyword( $val );
					if( !$val ) { continue; }
					else if( !in_array( $val, $tagArr ) )
					{
						$cnt++;
						$tagArr[] = $val;
						$row['product_tags'] = $val;
						$FileContentS .= GenerateProductTagMapTag($row);
					}
				}
			}
			else if($SiteMapForI == 5)	
			{
				$cnt++;
				$FileContentS .= GenerateStaticLinkMapTag($row);
			}
			else if($SiteMapForI == 6)
			{
				$cnt++;
				$keywordArr = explode(',', $row['product_category_tags']);
		
				foreach($keywordArr as $k=>$val)
				{
					$val = isKeyword( $val );
					if( !$val ) { continue; }
					else if( !in_array( $val, $tagArr ) )
					{
						$cnt++;
						$tagArr[] = $val;
						$row['product_category_tags'] = $val;
						$FileContentS .= GenerateCategoryTagMapTag($row);
					}
				}
			}
			else if($SiteMapForI == 7)
			{
				$res = getPipeStringData( "product_categories", "category_id", " category_id, category_name ", $row['product_related_category_id']);
		
				foreach($res as $k=>$val)
				{
					$val['category_name'] = isKeyword( $val['category_name'] );
					if( !$val['category_name'] ) { continue; }
					else if( !in_array( $val['category_name'], $tagArr ) )
					{
						$cnt++;
						$tagArr[] = $row['category_name'] = $val['category_name'];
						$FileContentS .= GenerateProductRelatedLinkMapTag( $row );
					}
				}
			}
		}
		
		if( $cnt == 0 )
		{
			if( file_exists( $root_path.'sitemap'.$SitemapFileNoI.'.xml' ) )
			{
				unlink($root_path.'sitemap'.$SitemapFileNoI.'.xml');	
				$SitemapFileNoI--;
			}
		}	
		else
		{
			//end urlset tag
			$FileContentS .= '</urlset>';
			fwrite( $fh, $FileContentS );
			fclose( $fh );
		}
	}

	function GenerateStaticLinkMapTag($row)
	{
		if( $row['ModifiedOn'] == '0000-00-00')
		{
			$row['ModifiedOn'] = $row['CreatedOn'];
		}
			
		return '<url>
			    <loc>'.$row['url'].'</loc>
			    <lastmod>'.$row['ModifiedOn'].'</lastmod>
			    <changefreq>weekly</changefreq>
				</url>';

	}

	function GenerateProductCategoryMapTag($row)
	{
		if( $row['ModifiedOn'] == '0000-00-00')
		{
			$row['ModifiedOn'] = $row['CreatedOn'];
		}

		return '<url>
			    <loc>'.CategoryLink($row).'</loc>
			    <lastmod>'.$row['ModifiedOn'].'</lastmod>
			    <changefreq>weekly</changefreq>
				</url>';
	}

	function CategoryLink($row)
	{
		if( isset( $row['category_alias'] ) )
		{
			return searchByKeywordUrl( $row['category_alias'] );	
		}
		else
		{
			return searchCategoryUrl( $row['category_id'] ); 
		}
	}

	function GenerateProductMapTag($row)
	{
		if( $row['ModifiedOn'] == '0000-00-00')
		{
			$row['ModifiedOn'] = $row['CreatedOn'];
		}

		return '<url>
			    <loc>'.getProductUrl($row['product_id'], $row['product_price_id']).'</loc>
			    <lastmod>'.$row['ModifiedOn'].'</lastmod>
			    <changefreq>weekly</changefreq>
				</url>';
	}

	function GenerateProductTagMapTag($row)
	{
		if( $row['ModifiedOn'] == '0000-00-00')
		{
			$row['ModifiedOn'] = $row['CreatedOn'];
		}

		return '<url>
				<loc>'.searchByKeywordUrl($row['product_tags']).'</loc>
				<lastmod>'.$row['ModifiedOn'].'</lastmod>
				<changefreq>weekly</changefreq>
				</url>';
	}

	function GenerateCategoryTagMapTag($row)
	{
		if( $row['ModifiedOn'] == '0000-00-00')
		{
			$row['ModifiedOn'] = $row['CreatedOn'];
		}

		return '<url>
				<loc>'.searchByKeywordUrl($row['product_category_tags']).'</loc>
				<lastmod>'.$row['ModifiedOn'].'</lastmod>
				<changefreq>weekly</changefreq>
				</url>';
	}

	function GenerateProductRelatedLinkMapTag($row)
	{
		if( $row['ModifiedOn'] == '0000-00-00')
		{
			$row['ModifiedOn'] = $row['CreatedOn'];
		}

		return '<url>
				<loc>'.searchByKeywordUrl($row['category_name']).'</loc>
				<lastmod>'.$row['ModifiedOn'].'</lastmod>
				<changefreq>weekly</changefreq>
				</url>';
	}

	function GenerateArticleMapTag($row)
	{
		if( $row['ModifiedOn'] == '0000-00-00')
		{
			$row['ModifiedOn'] = $row['CreatedOn'];
		}

		return '<url>
			    <loc>'.getArtUrl( $row['article_id'] ).'</loc>
			    <lastmod>'.$row['ModifiedOn'].'</lastmod>
			    <changefreq>weekly</changefreq>
				</url>';
	}

	function CreateSiteMapIndex( $root_path, $FileA )
	{
	
		$fh = fopen( $root_path.'sitemap.xml', 'w' );
	
		//start xml file tags
		$FileContentS =  '<?xml version="1.0" encoding="UTF-8"?>
		<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
	
		foreach($FileA as $FileAEle)
		{
			$FileContentS .= GenerateSiteMapIndexTag( $FileAEle );
		}
	
		//end sitemapindex tag
		$FileContentS .= '</sitemapindex>';
		fwrite($fh,$FileContentS);
		fclose($fh);
	
	}
	
	function GenerateSiteMapIndexTag($FileAEle)
	{
		return '<sitemap>
				<loc>'.site_url( $FileAEle ).'</loc>
				</sitemap>';
	}
	
?>