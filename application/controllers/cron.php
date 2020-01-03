<?php

/**
 * @abstract cron controller
 */
class cron extends CI_Controller
{

	var $log = "";
	
    public function __construct()
	{
        parent::__construct();
		
        setTimeLimit();
         
        //debug mode on for cronning
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
        //$this->db->db_debug = TRUE;

        /**
         * @abstract BASE_DIR
         */
        //define( 'BASE_DIR', $_SERVER['DOCUMENT_ROOT']."/" );
        
		//load cron helper
		$this->load->helper( 'cron' );
		
		//$this->lang->load('english', 'english');
		$this->load->library('form_validation');
		
		require_once APPPATH.'libraries/simple_html_dom.php';
		require_once APPPATH.'libraries/curl/curl_lib.php'; 
		
		/*$this->load->model('banner_model');
        $this->data["cr_page"] = "";
		$this->load->model('cars_model');
        $this->load->model('car_model_model');
        $this->load->model('dealer_model');
        $this->load->model('page_model');
        $this->load->model('youtube_video_model');
        $this->load->model('car_category_model');
        $this->load->model("slider_image_model");
        
        $this->load->model('car_brand_model');
        $this->load->model('country_model');
        //slider
		$this->load->model('mod_slider');
		
		$this->load->model('mod_vehiclewarranty');
		$this->load->model('mod_prepurchase');
		*/

    }
	
	function index($lang=false)
	{	
		redirect();		
	}

	/**
	 * @abstract will scrap for dealers from autotrader.com or other intended sites
	 */
	function cronQue($movie_mode="")
	{
		//function specific lib
		require_once APPPATH.'libraries/simple_html_dom.php'; 
		$data = $this->input->get();
		$res;
		
		$data['lc_name'] = $movie_mode;
		$this->db->insert('local_cron',$data);
	
		$time_elapsed = time();
		
		$start = $end = 0;
		$data['mode'] = ($movie_mode) ? $movie_mode : $data['mode'];

		//read process index
		if( $data['mode'] == "MOVIE_INPUT" )
			$tempRow = exeQuery( " SELECT * FROM temp WHERE t_name='MOVIE_SEARCH_INDEX' " );
		else if( $data['mode'] == "PTA_SCRAPE_PLAIN" )
			$tempRow = exeQuery( " SELECT * FROM temp WHERE t_name='PTA_SCRAPE_PLAIN_INDEX' " );
		else if( $data['mode'] == "PTA_SEARCH_INDEX" )
			$tempRow = exeQuery( " SELECT * FROM temp WHERE t_name='PTA_SEARCH_INDEX' " );
		else if( $data['mode'] == "MOVIE_JAGUAR" )
			$tempRow = exeQuery( " SELECT * FROM temp WHERE t_name='MOVIE_JAGUAR' " );
		
		$start = $tempRow['t_value'];
		if( $start == -1 ) 
		{
			//batch processes completed
			errorLog( 'MOVIE_CRON', 'MOVIE_CRON process: Mode=> '.$data['mode'].': DB batch processes already completed.' );
			exit;
		}

		//cron_que results
		if( $data['mode'] == "MOVIE_INPUT" )
		{
			$res = executeQuery( " SELECT * FROM cron_que WHERE cq_key='MOVIE_INPUT' AND cq_status=0 LIMIT ".$tempRow['t_no_of_records'] );
		}
		else if( $data['mode'] == "PTA_SCRAPE_PLAIN" )
		{
			$res = executeQuery( " SELECT * FROM cron_que WHERE cq_key='PTA_SEARCH_INDEX' AND cq_status=0 LIMIT ".$tempRow['t_no_of_records'] );
		}
		else if( $data['mode'] == "PTA_SEARCH_INDEX" ) 
		{
			$is_success = $this->googleSearchPTA( $data, $tempRow );
			//[temp]
			die;
		}
		else if( $data['mode'] == "MOVIE_JAGUAR" )
		{
			$res = executeQuery( " SELECT * FROM cron_que WHERE cq_key='MOVIE_JAGUARDC' AND cq_status=0 LIMIT ".$tempRow['t_no_of_records'] );
		}
				
		$end = $start + $tempRow['t_no_of_records'];
		if( !empty( $res ) )
		{
			foreach ( $res as $k=>$ar )
			{
				//scrap DEALERS 
				if( $data['mode'] == "MOVIE_INPUT" ) 
				{
					$is_success = $this->scrapMovies( validateOutput( $ar["cq_url"] ), $ar["cron_que_id"] ,$data, $tempRow );
				}
				else if( $data['mode'] == "PTA_SCRAPE_PLAIN" ) 
				{
					$is_success = $this->scrapPTA( validateOutput( $ar["cq_url"] ), $ar["cron_que_id"] ,$data, $tempRow );
				}
				else if( $data['mode'] == "MOVIE_JAGUAR" ) 
				{
					$is_success = $this->scrapJaguardcLink( validateOutput( $ar["cq_url"] ), $ar["cron_que_id"] ,$data, $tempRow );
				}
			}
		
			$time_elapsed = (time() - $time_elapsed);
			
			//log
			errorLog( 'MOVIE_CRON', ' MOVIE_CRON process: Mode=> '.$data['mode'].': DB batch Process '.( $is_success == true ? 'successful' : 'failed' ).'. <br> Time taken:'. $time_elapsed, true );
		}
		else
		{
			//reset process index
			if( !isset( $data["is_testing"] ) )
			{
				if( $data['mode'] == "MOVIE_INPUT" )
				{
					$this->db->query( " UPDATE temp SET t_value=-1 WHERE t_name='MOVIE_SEARCH_INDEX' " );
				}
				else if( $data['mode'] == "PTA_SCRAPE_PLAIN" )
				{
					$this->db->query( " UPDATE temp SET t_value=-1 WHERE t_name='PTA_SCRAPE_PLAIN_INDEX' " );
				}
				else if( $data['mode'] == "MOVIE_JAGUAR" )
				{
					$this->db->query( " UPDATE temp SET t_value=-1 WHERE t_name='MOVIE_JAGUAR' " );
				}
					
				//$this->db->query( " UPDATE temp SET t_value='".$this->modeInSequence( $data )."' WHERE t_name='AUTO_TRADER_CRON_CUR_MODE' " );
			}
			
			$time_elapsed = ( time() - $time_elapsed );
			
			//log
			errorLog( 'MOVIE_CRON', 'MOVIE_CRON process: Mode=> '.$data['mode'].': DB batch process fully completed. <br> Time taken:'. $time_elapsed, true );
		}
		
		echo 'Operation completed in '.$time_elapsed.' seconds.';
	}

	function testScrap()
	{
		//function specific lib
		require_once APPPATH.'libraries/simple_html_dom.php'; 
		
		//echo validateOutput( "" ); die; 
		
		$this->scrapMovies( "Forever Love|2005", 2 );
		
// 		$res = executeQuery( " SELECT * FROM cron_que WHERE cq_key='MOVIE_INPUT' AND cq_status=0 " );
		
// 		if( !empty( $res ) )
// 		{
// 			foreach ( $res as $k=>$ar )
// 			{
// 				$this->scrapMovies( $ar["cq_url"], $ar["cron_que_id"] );				
// 			}			
// 		}
		
	}
	
	function scrapMovies( $cq_url, $cron_que_id, $data='', $tempRow='' )
	{
		$tempArr = explode( "|", $cq_url );
		$movie_name = $tempArr[0]; 
		$movie_year = $tempArr[1]; 
		
		$sql = "";
		
		//fetch all links from pagination
		$url = "http://www.imdb.com/find?q=".str_replace(' ','+',$movie_name)."&s=all";
		
		$html = getContents( $url );
		
		$ret = $html->find( 'table[class=findList]' );
		$movie_link = "";
		foreach($ret as $tbody)
		{
			//echo $tbody->plaintext.'<br>';
			$trArr = $tbody->find( 'tr' );
			foreach ( $trArr as $tr )
			{
				$td = $tr->find( 'td[class=result_text]', 0 );
				$a = $td->find( 'a', 0 );
				//echo $a->plaintext.'<br>';
				//echo fetchSubStr( $td->plaintext, "(", ")" ).'<br>';

				if( strcasecmp( $movie_name, $a->plaintext ) == 0  && strpos( $td->plaintext, "(".$movie_year.")" ) !== FALSE )
				{
					$movie_link = $a->href;
				}
				
				if( !empty( $movie_link ) )
				{
					break;	
				}
			}
			
			if( !empty( $movie_link ) )
			{
				break;	
			}
		}
		
		$movieData["m_site_key"] = "IMDB.COM";
		$movieData["m_name"] = $movie_name;
		$movieData["m_year"] = $movie_year;
		$movieData["cron_que_id"] = $cron_que_id;		
		
		if(empty($movie_link))
		{
			$movieData["m_desc"] = 'Not Found';
			$this->storeMovies( $movieData );
			updateCronQueStatus($cron_que_id);	
			//$this->scrapJaguardcLink($cq_url, $cron_que_id, $data='', $tempRow=''); //scrap another url
			return true;
		}
		//$movie_link = "title/tt1335975/?ref_=fn_al_tt_1";
		$url = "http://www.imdb.com/".$movie_link;
		$html = getContents( $url );
		
		$table1 = $html->find( 'table[id=title-overview-widget-layout]', 0 );
		
		//Image source
		$td1 = $table1->find( 'td[id=img_primary]', 0 );
		$td1_div = $td1->find( 'div[class=image]', 0 );
		$img_src = $td1_div->find( 'img[itemprop=image]', 0 )->src;
		
		$td2 = $table1->find( 'td[id=overview-top]', 0 );
		$td2_div = $td2->find( 'div[class=infobar]', 0 );
		
		//certificate
		$certi_tag = $td2_div->find( 'span[itemprop=contentRating]', 0 );
		$certificate = "";
		if(is_object($certi_tag))
			$certificate = $certi_tag->title;
		
		//star rating
		$star_div = $td2->find( 'div[class=star-box-details]', 0 );
		$star_tag = $star_div->find( 'span[itemprop=ratingValue]', 0 );
		$rating="";
		if(is_object($star_tag))
			$rating = $star_tag->plaintext;
		
		//duration
		$duration_tag = $td2_div->find( 'time[itemprop=duration]', 0 );
		$duration = "";
		if(is_object($duration_tag))
		{
			$duration = explode(' ',removeSpace($duration_tag->plaintext));
			$duration = $duration[0];
		}
		//genre	
		$genreArr = $td2_div->find( 'span[itemprop=genre]' );
		$genre = "";
		foreach($genreArr as $genVal)
		{
			$genre .= removeSpace($genVal->innertext)." | ";
		}
		$genre = substr($genre, 0, -2);
		
		//release date
		$date_span = $td2_div->find( 'span[class=nobr] a', 0 );
		$release_date_full = "";
		if(is_object($date_span))
		{
			$release_date_full = removeSpace($date_span->plaintext);
		
			//release full date
			$dateArr = explode(' ',$release_date_full);
			$date = $dateArr[0]." ".$dateArr[1]." ".$dateArr[2];
			$release_date = date('Y-m-d',strtotime($date));
		}
		
		//synopsis
		$syn_tag = $td2->find( 'p[itemprop=description]', 0 );
		$synopsis="";
		if(is_object($syn_tag))
			$synopsis = $syn_tag->plaintext;
		
		//director
		$dir_tag = $td2->find( 'div[itemprop=director] a', 0 );
		$director="";
		if(is_object($dir_tag))
			$director = $dir_tag->plaintext;
		
		//stars
		$stars_tag = $td2->find( 'div[itemprop=actors]', 0 );
		$stars="";
		if(is_object($stars_tag))
		{
			$stars = $stars_tag->plaintext;
			$stars = str_replace('Stars: ','',$stars);
			$stars = str_replace(',','|',$stars);
			$stars = removeSpace($stars);
		}
		
		//trailer link
		$td3 = $table1->find( 'td[id=overview-bottom]', 0 );
		$trailer_tag = $td3->find( 'a[class=title-trailer]',0 );
		$trailer_link="";
		if(is_object($trailer_tag))
			$trailer_link = $trailer_tag->href;
		
		//echo $html; die;
		//$ret = $html->find( 'span[itemprop=name]' );
		
		$movieData["m_image"] = saveImageFromUrl($img_src, 'movies', 'scrapper'); //image path, folder name, subdomain
		$movieData["m_desc"] = "";
		$movieData["m_certificate"] = $certificate;
		$movieData["m_rating"] = $rating;
		$movieData["m_duration"] = $duration;
		$movieData["m_genre"] = $genre;
		$movieData["m_us_release_date"] = $release_date;
		$movieData["m_us_release_date_full"] = $release_date_full;
		$movieData["m_synopsis"] = $synopsis;
		$movieData["m_director"] = $director;
		$movieData["m_stars"] = $stars;
		$movieData["m_trailer_link"] = $trailer_link;
		
		//pr($movieData);
		//die; 
		 
		$this->storeMovies( $movieData );
		updateCronQueStatus($cron_que_id);	
		
		//$this->scrapJaguardcLink($cq_url, $cron_que_id, $data='', $tempRow=''); //scrap another url
				
		return true;
	}
	
	/**
	 * @abstract will add/update dealer in nextauto db
	 */
	function storeMovies( $data )
	{
		$cron_que_id = $data['cron_que_id'];
		unset($data['cron_que_id']);
		
		//movies
		$movies_id = exeQuery( " SELECT movies_id FROM sc_movies WHERE m_name='".$data['m_name']."' AND m_year='".$data['m_year']."' AND m_site_key='".$data['m_site_key']."'  ", TRUE, "movies_id" );
		if( empty($movies_id) )
		{
			$this->db->insert( "sc_movies", $data );
		}
		else
		{
			$this->db->where( "movies_id", $movies_id )->update( "sc_movies", $data );
		}
		
	}
	
	
	
	function googleSearchPTA($data='', $tempRow='')
	{
		//function specific lib
		require_once APPPATH.'libraries/simple_html_dom.php'; 
		
		$kword = "PTO board members"; //"PTA board members";
		$url = "https://www.google.co.in/search?q=".str_replace(" ", "+", $kword)."&start=".$tempRow['t_value'];
		
		$html = getContents( $url );
		
		$linkObjs = $html->find('h3.r a');
		foreach ($linkObjs as $linkObj) 
		{
			$link  = trim($linkObj->href);
			
			// if it is not a direct link but url reference found inside it, then extract
			if (!preg_match('/^https?/', $link) && preg_match('/q=(.+)&amp;sa=/U', $link, $matches) && preg_match('/^https?/', $matches[1])) {
				$link = $matches[1];
			} else if (!preg_match('/^https?/', $link)) { // skip if it is not a valid link
				continue;    
			}
			
			//echo $link; 
			$ptaData['cq_url'] = validateInput($link);
			$ptaData['cq_key'] = $data['mode'];
			$ptaData['cq_status'] = 0;
			$this->storeQueLink($ptaData);
		}		
		
		$temp_value = ($tempRow['t_value'] + $tempRow['t_no_of_records']);
		$this->db->query( " UPDATE temp SET t_value='".$temp_value."', t_modified_date='NOW()' WHERE t_name='PTA_SEARCH_INDEX' " );
		
		echo 'Operation successfull total '.$tempRow['t_no_of_records'].' saved.';
		
		return true;		
	}
	
	/**
	 * @abstract will add/update in db
	 */
	function storeQueLink( $data )
	{
		$cq_id = exeQuery( " SELECT cron_que_id FROM cron_que WHERE cq_key='".$data['cq_key']."' AND cq_url='".$data['cq_url']."' ", TRUE, "cron_que_id" );
		if( empty($cq_id) )
		{
			$this->db->insert( "cron_que", $data );
			$last_id = $this->db->insert_id();
		}
		else
		{
			$this->db->where( "cron_que_id", $cq_id )->update( "cron_que", $data );
		}		
	
	}
	
	
	function testScrapPTA()
	{
		//function specific lib
		require_once APPPATH.'libraries/simple_html_dom.php'; 
		
		//echo validateOutput( "" ); die; 
		
 		//$res = executeQuery( " SELECT * FROM cron_que WHERE cq_key='PTA_SEARCH_INDEX' AND cq_status=0 " );
		/*$res = array(
					'http://www.abileneisd.org/Page/1354',
					'http://www.wayzata.k12.mn.us/Page/10758',
					/*'http://lakeharriet.mpls.k12.mn.us/pta_board_and_key_contacts',*/
					/*'http://www.jbes.srvusd.k12.ca.us/cms/page_view?d=x&piid=&vpid=1255068491728',
					'http://tmlink.org/about/2013-14-pta-board/',
					'http://www.floridapta.org/about-us/board-of-directors',
					'http://www.powayusd.com/pusdsces/pta/board.shtml',
					'http://lincoln.tusd.org/PTA/PTABoardMembersandChairpersons/tabid/3703/Default.aspx',*/
					/*'http://www.ptaatope.org/pta-board-members'
					);
 		*/
		$this->scrapPTAPlaintext( "http://www.pta.org/about/content.cfm?ItemNumber=954", 0 );
 		
	}
	
	function scrapPTA( $url, $cron_que_id, $data='', $tempRow='' )
	{
		//function specific lib
		require_once APPPATH.'libraries/simple_html_dom.php'; 
		require_once APPPATH.'libraries/curl/curl_lib.php'; 
		
		$ext = pathinfo($url, PATHINFO_EXTENSION);
		$extArr = getAllFileExtension(); 
		if(in_array($ext, $extArr))
		{
			//update cron que status
			updateCronQueStatus($cron_que_id);	
			return true;
		}
		
		$resArr = fetchUrlHtml( validateOutput($url) );
		
		$html = str_get_html($resArr['answer']);
		
		if(empty($resArr['answer']))
		{
			echo "Error: ".$resArr['error']."<br>";
			echo "URL: ".$url."<br>";
			pr($resArr);
			
			//update cron que status
			updateCronQueStatus($cron_que_id);
		
			return true;
		}
		
		$pp_plaintext = "";
		if(is_object($html))
			$pp_plaintext = $html->plaintext;
			
		$ptaPlainData['pp_plaintext'] = $pp_plaintext;
		
		$ptaPlainData["pp_site_key"] = "PTA_KEYWORD";
		$ptaPlainData["cron_que_id"] = $cron_que_id;
		 
		$this->storeScrapPTA( $ptaPlainData );
		
		return true;
	}
	function storeScrapPTA( $data )
	{
		$pta_plaintext_id = exeQuery( " SELECT pta_plaintext_id FROM sc_pta_plaintext WHERE cron_que_id = '".$data['cron_que_id']."'  ", TRUE, "pta_plaintext_id" );
		if( empty($pta_plaintext_id) )
		{
			$this->db->insert( "sc_pta_plaintext", $data );
		}
		else
		{
			$this->db->where( "pta_plaintext_id", $pta_plaintext_id )->update( "sc_pta_plaintext", $data );
		}
		
		//update cron que status
		updateCronQueStatus($data['cron_que_id']);
			
	}
	
	
	function scrapPTAPlaintext()
	{
		$ptaTextArr = executeQuery( " SELECT pp.pp_plaintext, cq.cq_url FROM sc_pta_plaintext pp INNER JOIN 
									  cron_que cq ON cq.cron_que_id=pp.cron_que_id 
									  LIMIT 25 " );
		if( !empty( $ptaTextArr ) )
		{
			foreach($ptaTextArr as $ptaText)
			{
				echo $ptaText['cq_url']."<br><br>";
				$plainText = getPTA_OListTable( $ptaText['pp_plaintext'] );
				pr( explode( "\n", $plainText ) )."<br><br><br><br>";
			}
		}
		
	}
	
	function testJaguardcLink()
	{
		$url = "http://www.jaguardc.com/category/library/classics";
		
		$resArr = fetchUrlHtml( validateOutput($url) );
		$html = str_get_html($resArr['answer']);
		
		$ret = $html->find( 'ul[class=results] li' );
		
		$cnt=0;
		foreach($ret as $aTag)
		{
			$link_url = $aTag->find('a',0)->href;
			
			$jaguarLink['cq_url'] = validateInput($link_url);
			$jaguarLink['cq_key'] = "MOVIE_JAGUARDC";
			$jaguarLink['cq_status'] = 0;
						
			$this->storeQueLink($jaguarLink);
			$cnt++;			
		}
		
		echo 'Operation successfull total '.$cnt.' saved.';
		
	}
	
	/* www.jaguardc.com */
	function scrapJaguardcLink($cq_url, $cron_que_id, $data='', $tempRow='')
	{
		/*$tempArr = explode( "|", $cq_url );
		$movie_name = str_replace(" ","-",$tempArr[0]); 
		$movie_year = $tempArr[1]; */
		
		//fetch all links from pagination
		/*$url = "http://www.jaguardc.com/livesearch.php?q=".$movie_name;*/
		//$url = "http://www.jaguardc.com/".$movie_name.".html";
		
		$resArr = fetchUrlHtml( validateOutput(urldecode($cq_url)) );
		$html = str_get_html($resArr['answer']);
		//$html = getContents( $url );
		
		if(empty($resArr['answer']))
		{
			echo "Error: ".$resArr['error']."<br>";
			echo "URL: ".$cq_url."<br>";
			pr($resArr);
			
			//update cron que status
			updateCronQueStatus($cron_que_id);
		
			return true;
		}
		
		$movieData["m_site_key"] = "JAGUARDC.COM";
		$movieData["m_year"] = '';
		$movieData["cron_que_id"] = $cron_que_id;
		$ret = $html->find( 'div[id=movie]', 0 );
		
		if(!is_object($ret))
		{
			$movieData["m_desc"] = 'Not Found';
			$this->storeMovies( $movieData );
			
			//update cron que status
			updateCronQueStatus($cron_que_id);
		
			return true;
		}
		
		
		$movie_name = $ret->find('div[class=post] h2',0)->plaintext; 
		
		$img_src = "";
		$img_tag = $html->find( 'li[class=poster] img', 0 );
		if(is_object($img_tag))
			$img_src = str_replace(" ","%20",$img_tag->src);
		
				
		$spec = $ret->find( 'div[class=specs]', 0);
		
		$label = $spec->innertext;
		//echo $label;
		
		$remove_tag = strip_tags_content($label);		
		//echo $remove_tag;
		//$specArr = strip_tags($specArr,"<br />");
		
		$specArr = explode("<br />", removeSpace($remove_tag));
		
		
		$release_date = $release_date_full = "";
		if(@$specArr[5])
			$release_date = date("Y-m-d",strtotime($specArr[5]));
		
		if(!empty($specArr[5]) && !empty($specArr[6]))
			$release_date_full = date('j F Y',strtotime($release_date))." (".removeSpace($specArr[6]).")";
		else if(!empty($specArr[6]))
			$release_date_full = $specArr[6];
			
		$trailer_link = "";
		$trailer_link_tag = $ret->find( 'div[class=entry] embed', 0);
		if(is_object($trailer_link_tag))
			$trailer_link = $trailer_link_tag->src;
		
		//echo $html;die;
		
		$movieData["m_name"] = $movie_name;
		$movieData["m_image"] = saveImageFromUrl($img_src, 'movies', 'scrapper'); //image path, folder name, subdomain
		$movieData["m_desc"] = (@$specArr[17]) ? $specArr[17] : '';;
		$movieData["m_genre"] = (@$specArr[0]) ? $specArr[0] : '';
		$movieData["m_certificate"] = (@$specArr[1]) ? $specArr[1] : '';
		$movieData["m_us_release_date"] = $release_date;
		$movieData["m_us_release_date_full"] = $release_date_full;
		$movieData["m_stars"] = (@$specArr[8]) ? $specArr[8] : '';
		$movieData["m_director"] = (@$specArr[9]) ? $specArr[9] : '';
		$movieData["m_synopsis"] = (@$specArr[15]) ? $specArr[15] : '';
		
		$movieData["theatrical_runtime"] = (@$specArr[2]) ? $specArr[2] : '';
		$movieData["edited_runtime"] = (@$specArr[3]) ? $specArr[3] : '';		
		$movieData["aspect_ratio"] = (@$specArr[4]) ? $specArr[4] : '';
		$movieData["lab"] = (@$specArr[7]) ? $specArr[7] : '';
		$movieData["original_language"] = (@$specArr[10]) ? $specArr[10] : '';
		$movieData["available_language"] = (@$specArr[11]) ? $specArr[11] : '';
		$movieData["subtitles"] = (@$specArr[12]) ? $specArr[12] : '';
		$movieData["rights"] = (@$specArr[13]) ? $specArr[13] : '';
		$movieData["excluded_rights"] = (@$specArr[14]) ? $specArr[14] : '';
		
		$movieData["m_trailer_link"] = $trailer_link;
		
		//pr($movieData);
		//die; 
		 
		$this->storeMovies( $movieData );
		
		updateCronQueStatus($cron_que_id); //update cron que status
		
		//return true;
		
	}
	
	
	
	
		
/**
 * @abstract return mode that comes next
 */	
	function modeInSequence( $data ) 
	{
		if( $data['mode'] == "CITY" )
		{
			return "DEALERS";
		}
		else if( $data['mode'] == "DEALERS" )
		{
			return "LISTING";
		}
		else if( $data['mode'] == "LISTING" )
		{
			return "DETAILS";
		}
		else if( $data['mode'] == "DETAILS" )
		{
			return "CITY";
		}
	}

	/**
	 * @abstract return mode that comes prev
	 */
	function modeInRevSequence( $data )
	{
		if( $data['mode'] == "CITY" )
		{
			return "DETAILS";
		}
		else if( $data['mode'] == "DEALERS" )
		{
			return "CITY";
		}
		else if( $data['mode'] == "LISTING" )
		{
			return "DEALERS";
		}
		else if( $data['mode'] == "DETAILS" )
		{
			return "LISTING";
		}
	}
		
	/**
	 * @abstract will scrap for dealers from autotrader.com or other intended sites
	 */
	function scrapCity( $data ) 
	{
		$html = getContents( "http://www.autotrader.com/car-dealers/" );
		
		$ret = $html->find('a[data-birf-cmp=slc_city]');
		
		$sql = " INSERT INTO cron_que( cq_url, cq_key ) VALUES";
		foreach ( $ret as $element)
		{
			$sql .= " ( 'http://www.autotrader.com/".$element->href."', 'AUTO_TRADER_".$data["mode"]."' ),";
		}
		
		//insert in cron que
		$this->db->query( substr( $sql, 0, -1 ) );
		
		return true;
	}
		
	/**
	 * @abstract will scrap for dealers from autotrader.com or other intended sites
	 */
	function scrapDealersListing( $url, $data, $tempRow ) 
	{
		$pages = 0; $zip = ""; $sql = "";
		$arr = explode( "/", $url );
		$zip = end( $arr );
		$zip = substr( $zip, strpos( $zip, "-" ) + 1 );
				
		if( $tempRow["record_2"] == 0 ) 
		{
			$this->log .= "Checking pagination: ".$url."<br><br>";
			
			$html = getContents( $url );
			$ret = $html->find( 'span[class=pageof]' );
			foreach ( $ret as $element ) 
			{
				$arr = explode( " ", $element->innertext );
				$pages = end( $arr );
			}
				
			//log
			$this->log .= "Zip: ". $zip . " Pages: " . $pages . "<br><br>";
			
			$tempRow["record_2"] = $pages;
		}

		if( $tempRow["record_1"] < $tempRow["record_2"] )
		{
			
			//fetch all links from pagination
			$url = "http://www.autotrader.com/car-dealers/dealersearchresults.xhtml?zip=".$zip."&sortBy=distanceASC&firstRecord=".( ( 10 * ( $tempRow["record_1"] ) ) + 1 )."&numRecords=10&searchRadius=10";
			$html = getContents( $url );
							
			$ret = $html->find( 'div[class=dealer-listing]' );
			foreach ( $ret as $element )
			{
				$div = $element->find( 'div', 2 );
				$a = $div->find( 'a', 0 );
				$sql .= " ( 'http://www.autotrader.com/".$a->href."', 'AUTO_TRADER_".$data["mode"]."' ),";
				$this->log .= $a->href." <br><br>";
			}
		
			//insert in cron que
			if( !empty( $sql ) ) 
			{
				$sql = " INSERT INTO cron_que( cq_url, cq_key ) VALUES" . substr( $sql, 0, -1 );
					

				$this->log .= $sql;
				//echo $this->log; die;
					
				$this->db->query( $sql );
			}
			
			$tempRow["record_1"] = $tempRow["record_1"] + 1;
		}
		else 
		{
			$tempRow["record_1"] = 0;
			$tempRow["record_2"] = 0;
		}
		
		//update pagination being scrapped
		$temp_id = $tempRow["temp_id"];
		unset( $tempRow["temp_id"] );
		$this->db->where( "temp_id", $temp_id )->update( "temp", $tempRow );

		if( $tempRow["record_2"] == 0 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * @abstract will scrap for dealers from autotrader.com or other intended sites
	 */
	function scrapDealerAndVehicleListing( $url='', $data='', $tempRow='' )
	{
		$pages = 0; $zip = ""; $sql = "";
		
		//extract zip from url
		$tempStr = str_replace( "//", "/", $url );
		$arr = explode( "/", $tempStr );
		$zip = $arr[3];
		$zip = substr( $zip, strpos( $zip, "-" ) + 1 );
	
		$dealerLocationAutotrader = $arr[3];
		$dealerIdAutotrader = $arr[4];

		if( strpos( $dealerLocationAutotrader, "?" ) !== FALSE )
		{
			$tempArr = exploe( "?", $dealerLocationAutotrader );	
			$dealerLocationAutotrader = $tempArr[0];
		}
	
		//temp
		//$tempRow["record_2"] = 2; $tempRow["record_1"] = 1;
		if( $tempRow["record_2"] == 0 )
		{
			$this->log .= "Checking pagination: ".$url."<br><br>";

			$html = getContents( $url );
			if( !empty( $html ) ) 
			{
				$ret = $html->find( 'span[class=pageof]' );
				foreach ( $ret as $element )
				{
					$arr = explode( " ", $element->innertext );
					$pages = end( $arr );
				}
				
				//log
				$this->log .= "Zip: ". $zip . " Pages: " . $pages . "<br><br>";
				
				$tempRow["record_2"] = $pages;
					
				//dealer detail
				
				//user_image
				$dataDealer["user_image"] = "";
				$ret = $html->find( 'img[class=thumbnailImage]', 0 );
				if( is_object( $ret ) ) 
				{
					$dataDealer["user_image"] = $ret->src;
				}
				
				//user_name
				$dataDealer["user_name"] = "";
				$ret = $html->find( 'h1[class=atcui-page-title]', 0 );
				if( is_object( $ret ) ) 
				{
					$dataDealer["user_name"] = $ret->innertext;
				}
				
				//user_address
				$dataDealer["user_address_1"] = "";
				$ret = $html->find( 'span[class=address1]' );
				foreach ( $ret as $ele )
				{
					$dataDealer["user_address_1"] = $ele->innertext;
				}
					
				$ret = $html->find( 'span[class=address2]' );
				$dataDealer["user_address_2"] = "";
				foreach ( $ret as $ele )
				{
					$dataDealer["user_address_2"] = $ele->innertext;
				}
				
				//
				$dataDealer["user_address_city_state_zip"] = $dataDealer["user_state"] = $dataDealer["user_city"] = "";
				$ret = $html->find( 'span[class=cityStateZip]' );
				foreach ( $ret as $ele )
				{
					$dataDealer["user_address_city_state_zip"] = $ele->innertext;
					$pos = strpos(  $dataDealer["user_address_city_state_zip"], "," );
					if( $pos !== FALSE )
					{
						$dataDealer["user_city"] = substr( $dataDealer["user_address_city_state_zip"], 0, $pos );
						
						$tempStr =  trim( substr( $dataDealer["user_address_city_state_zip"], $pos + 1 ) );
						$pos = strpos( $tempStr, " " );
						if( $pos !== FALSE )
						{
							$dataDealer["user_state"] = trim( substr( $tempStr, 0, $pos ) ); 
						}
					}
				}
					
				//user_phone
				$dataDealer["user_phone"] = "";
				$ret = $html->find( 'span[class=dealer-phone]', 0 );
				if( is_object( $ret ) )
				{
					$dataDealer["user_phone"] = $ret->innertext;
				}
				
				//user_website
				$dataDealer["user_website"] = "";
				$dataDealer["user_logo"] = "";
				$ret = $html->find( 'span[class=dealer-tile]', 0 );
				if( is_object( $ret ) )
				{
					$ret = $ret->find( 'a', 0 );
					if( is_object( $ret ) )
					{
						$dataDealer["user_website"] = $ret->href;
						$ret = $ret->find( 'img', 0 );
						if( is_object( $ret ) ) 
						{
							$dataDealer["user_logo"] = $ret->src;
						}
					}
				}

				//user latitude
				$dataDealer["gps_latitude"] = "";
				$dataDealer["gps_longitude"] = "";
				
				//http://www.autotrader.com//car-dealers/Albuquerque+NM-87109/68839
				$url = "http://www.autotrader.com/cardealers/partial/dealerdetails-about.xhtml?dealerId=".$dealerIdAutotrader."&location=".$dealerLocationAutotrader."";
				$html = getContents( $url );
				if( !empty( $html ) ) 
				{
					$dataDealer["gps_latitude"] = fetchSubStr( $html, "latitude=", "," );
					$dataDealer["gps_longitude"] = fetchSubStr( $html, "longitude=", "," );
				}


				//zip
				$dataDealer["zip"] = trim( $zip );
				
				//storeDealers
				$dealer_id = $this->storeDealers( $dataDealer );
				
				$tempRow["record_3"] = $dealer_id;
			}
			else 
			{
				//echo "Empty";
			}
			
		}
	
		if( $tempRow["record_1"] < $tempRow["record_2"] )
		{
				
			//fetch all links from pagination
			$url = $url."?endYear=2015&firstRecord=".( ( 25 * ( $tempRow["record_1"] ) ) + 1 )."&searchRadius=25&startYear=1981&zip=".$zip;

			$this->log .= "Fetching listing: ".$url."<br><br>";
			$html = getContents( $url );
			if( !empty( $html ) )
			{
				$ret = $html->find( 'a[class=vehicle-title]' );
				foreach ( $ret as $a )
				{
					$sql .= " ( 'http://www.autotrader.com/".$a->href."', 'AUTO_TRADER_".$data["mode"]."_".$tempRow["record_3"]."' ),";
					$this->log .= $a->href." <br><br>";
				}
				
				//insert in cron que
				if( !empty( $sql ) )
				{
					$sql = " INSERT INTO cron_que( cq_url, cq_key ) VALUES" . substr( $sql, 0, -1 );
						
					$this->log .= $sql;
					//echo $this->log; die;
						
					$this->db->query( $sql );
				}
			}
				
				
			$tempRow["record_1"] = $tempRow["record_1"] + 1;
		}
		else
		{
			$tempRow["record_1"] = 0;
			$tempRow["record_2"] = 0;
			$tempRow["record_3"] = 0;
		}

		//update pagination being scrapped
		$temp_id = $tempRow["temp_id"];
		unset( $tempRow["temp_id"] ); 
		$this->db->where( "temp_id", $temp_id )->update( "temp", $tempRow );
	
		if( $tempRow["record_2"] == 0 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
		
	/**
	 * @abstract will add/update dealer in nextauto db
	 */
	function storeDealers( $data ) 
	{
		$stateRow = getStateNameFrAtName( $data["user_state"] );
		
		//user
		$dataUser[ "user_first_name" ] = $data["user_name"];
		$dataUser[ "user_user_name" ] =  str_safe( trim( $data["user_name"] ) );
		$dataUser[ "user_password" ] = "123456";
		$dataUser[ "user_email" ] = "info@".( !empty( $data["user_website"] ) ? $data["user_website"] : $dataUser["user_user_name"].".com" );
		$dataUser[ "user_country_id" ] = "10";
		$dataUser[ "user_state" ] = $stateRow["county_name"];
		$dataUser[ "user_city" ] = $data["user_city"];
		$dataUser[ "user_phone_number" ] = $data["user_phone"];
		$dataUser[ "user_image" ] = saveImageFromUrl( $data["user_image"] );
		$dataUser[ "user_type" ] = 3;
		$dataUser[ "is_active" ] = 0;
				
		//user_id
		$user_id = getField( "user_id", "search_tbl_users", "user_user_name", $dataUser["user_user_name"] );
		if( empty( $user_id ) ) 
		{
			$this->db->insert( "search_tbl_users", $dataUser );
			$user_id = $this->db->insert_id();
		}
		else
		{
			$this->db->where( "user_id", $user_id )->update( "search_tbl_users", $dataUser );
		}
		
		//dealer
		$dataDealer["user_id"] = $user_id;
		$dataDealer["dealer_image"] = $dataUser[ "user_image" ];
		$dataDealer["dealer_name"] = $data[ "user_name" ];

		$resBrand = getFranchiseName( $dataDealer[ "dealer_name" ] );
		$dataDealer["franchise_name"] = $resBrand[ "brand_name" ];

		$dataDealer["dealer_url"] = $data[ "user_website" ];
		$dataDealer["dealer_address"] = $data[ "user_address_1" ]." ".$data["user_address_2"];
		$dataDealer["dealer_country"] = $dataUser[ "user_country_id" ];
		$dataDealer["dealer_postal_code"] = $data[ "zip" ];
		$dataAd[ "dealer_state_id" ] = $stateRow[ "county_id" ];
		$dataAd[ "dealer_city_id" ] = fetchFieldIfExist( $data[ "user_city" ], "city_id", "city_name", "search_tbl_cities", array( "country_id"=>10, "county_id"=>$stateRow[ "county_id" ] ) );
		$dataDealer["dealer_state"] = $stateRow["county_name"];
		$dataDealer["dealer_city"] = $data[ "user_city" ];
		$dataDealer["gps_latitude"] = $data["gps_latitude"];
		$dataDealer["gps_longitude"] = $data["gps_longitude"];
		$dataDealer["dealer_phone"] = $data[ "user_phone" ];
		$dataDealer["is_active"] = 1;
		

		//dealer_id
		$dealer_id = getField( "dealer_id", "search_tbl_dealers", "user_id", $user_id );
		if( empty( $dealer_id ) )
		{
			$this->db->insert( "search_tbl_dealers", $dataDealer );
			$dealer_id = $this->db->insert_id();
		}
		else
		{
			$this->db->where( "dealer_id", $dealer_id )->update( "search_tbl_dealers", $dataDealer );
		}
		
		return $dealer_id;
				
	}

	/**
	 * @abstract will scrap vehicles as per dealers id passed
	 */
	function scrapVehicles( $url='', $data='', $tempRow='', $cq_key='', $cq='' ) 
	{
		//dealer_id
		$tempArr = explode( "_", $cq_key );
		$dealer_id = end( $tempArr );
		
		$dealer = fetchRow( " SELECT * FROM search_tbl_dealers WHERE dealer_id=".(int)$dealer_id." " ); 
		
		//zip
		$zip = str_replace( "&", "", fetchSubStr( $url, "zip=", "&" ) );
				
		//listingId
		$listingId = str_replace( "&", "", fetchSubStr( $url, "listingId=", "&" ) );
		
		$this->log .= "Fetching details for LitingId: ".$listingId." From URL: ".$url."<br><br>";
		$html = getContents( $url ); 
		if( !empty( $html ) ) 
		{
			//vehicle detail
				
			//ad_title
			$dataDealer["ad_title"] = "";
			$ret = $html->find( 'title', 0 );
			if( is_object( $ret ) )
			{
				$dataDealer["ad_title"] = $ret->plaintext;
				
				if( !empty( $dataDealer["ad_title"] ) )
				{
					$dataDealer["ad_title"] = str_ireplace( "CARS FOR SALE:", "", $dataDealer["ad_title"] );
					$pos = stripos( $dataDealer["ad_title"], "Details -" );
					if( $pos !== FALSE ) 
					{
						$dataDealer["ad_title"] = substr( $dataDealer["ad_title"], 0, $pos ); 
					}
				}
			}
							
			//ad_mileage
			$dataDealer["ad_mileage"] = "";
			$ret = $html->find( 'span[class=mileage]', 0 );
			if( is_object( $ret ) )
			{
				$dataDealer["ad_mileage"] = $ret->innertext; 
			}
			if( !empty( $dataDealer["ad_mileage"] ) )
			{
				$dataDealer["ad_mileage"] = str_replace( "Mileage:", "", $dataDealer["ad_mileage"]);	
			}
			
			
			//user_image
			$dataDealer["vehicle_image"] = array();
			$offsetI = 0;
			$carmedia = fetchSubStr( $html, '"carmedia": [', ']', $offsetI );
			$dataDealer["vehicle_image"] = json_decode( "[".$carmedia."]" );
			
			//latitude
			$dataDealer["gps_latitude"] = "";
			$dataDealer["gps_longitude"] = "";
			if( is_array( $dataDealer["vehicle_image"] ) && sizeof( $dataDealer["vehicle_image"] ) > 0 )
			{
				foreach( $dataDealer["vehicle_image"] as $k=>$ar )				
				{
					if( $ar->mediaType == "MAP" )	
					{
						$dataDealer["gps_latitude"] = $ar->lat;
						$dataDealer["gps_longitude"] = $ar->lng;
					}
				}
			}
			
			//vehicle_price
			$dataDealer["ad_price"] = 0;
			$ret = $html->find( 'h4[class=primary-price]', 0 );
			if( is_object( $ret ) ) 
			{
				$span = $ret->find( "span", 0 );
				if( is_object( $span ) )
				{
					$dataDealer["ad_price"] = str_replace( ",", "",  substr( $span->innertext, 1 ) );
				}
			}

			//overview
			$url = "http://www.autotrader.com/cars-for-sale/vehicledetails/overview-tab.xhtml?listingId=".$listingId."&Log=0";
			$this->log .= "Fetching overview: ".$url."<br><br>";
			$html = getContents( $url );
							
			//ad_uniq_code
			$dataDealer["ad_uniq_code"] = getAdUniqueCode( 'MNA' );

			//ad_vehicle_title
			$dataDealer["ad_vehicle_title"] = "";
			$ret = $html->find( 'div[class=tab-heading]', 0 );
			if( is_object( $ret ) )
			{
				$span = $ret->find( "span" );
				foreach ( $span as $ele )
				{
					$dataDealer["ad_vehicle_title"] .= $ele->innertext;
				}
			}

			//ad_category
			$dataDealer["ad_category"] = "";
			$ret = $html->find( 'div[class=overview-infobar-subsection]', 0 );
			if( is_object( $ret ) )
			{
				$h2 = $ret->find( "h2", 0 );
				$dataDealer["ad_category"] = trim( $h2->innertext );
			}

			//user_ext_colorcode
			$dataDealer["user_ext_colorcode"] = "";
			$ret = $html->find( 'div[class=swatch]', 0 );
			if( is_object( $ret ) )
			{
				$dataDealer["user_ext_colorcode"] = $ret->style;
			}
							
			//user_ext_colorname
			$dataDealer["user_ext_colorname"] = "";
			$ret = $html->find( 'span[class=colorName]', 0 );
			if( is_object( $ret ) )
			{
				$span = $ret->find( "span", 0 );
				$dataDealer["user_ext_colorname"] = trim( $span->innertext );
			}
							
			//user_int_colorcode
			$dataDealer["user_int_colorcode"] = "";
			$ret = $html->find( 'div[class=swatch]', 1 );
			if( is_object( $ret ) )
			{
				$dataDealer["user_int_colorcode"] = $ret->style;
			}
				
			//user_int_colorname
			$dataDealer["user_int_colorname"] = "";
			$ret = $html->find( 'span[class=colorName]', 1 );
			if( is_object( $ret ) )
			{
				$span = $ret->find( "span", 0 );
				$dataDealer["user_int_colorname"] = trim( $span->innertext );
			}

			//ad_transmission
			$dataDealer["ad_transmission"] = "";
			$ret = $html->find( 'span[class=colorName]', 2 );
			if( is_object( $ret ) )
			{
				$span = $ret->find( "span", 0 );
				$dataDealer["ad_transmission"] = trim( $span->innertext );
			}

			//ad_condition
			$dataDealer["ad_condition"] = "";

			//ad_driver
			$dataDealer["ad_driver"] = "";
			$ret = $html->find( 'span[class=colorName]', 3 );
			if( is_object( $ret ) )
			{
				$span = $ret->find( "span", 0 );
				$dataDealer["ad_driver"] = trim( $span->innertext );
			}
							
			//ad_features
			$dataDealer["ad_features"] = "";
			$ret = $html->find( 'div[class=overview-optionList]', 0 );
			if( is_object( $ret ) ) 
			{
				$div = $ret->find( "div", 1 );
				$div = $ret->find( "div" );
				foreach ( $div as $ele ) 
				{
					$li = $ret->find( "li" );
					foreach ( $li as $e ) 
					{
						$d = $e->find( "div", 0 );
						$dataDealer["ad_features"] .= trim( $d->innertext )."|";
					}
				}
			}
							
			//ad_seller_comments
			$dataDealer["ad_seller_comments"] = "";
			$ret = $html->find( 'div[class=overview-comments]', 0 );
			if( is_object( $ret ) ) 
			{
				$dataDealer["ad_seller_comments"] = $ret->find( "p", 0 )->innertext;
			}

			//ad_vin
			$dataDealer["ad_vin"] = "";
							
			//details
			$url = "http://www.autotrader.com/cars-for-sale/vehicledetails/modelinfo-tab.xhtml?listingId=".$listingId."&Log=0";
			$this->log .= "Fetching details: ".$url."<br><br>";
			$html = getContents( $url );

			//ad_description
			$dataDealer["ad_description"] = "";
			$ret = $html->find( 'span[class=modelSummary]', 0 ); 
			if( is_object( $ret ) ) 
			{
				$dataDealer["ad_description"] = $ret->innertext;
			}

			//ad_specification
			$dataDealer["ad_specification"] = array();
			$ret = $html->find( 'div[class=features-list]' );
			if( is_object( $ret ) || is_array( $ret ) ) 
			{
				foreach ( $ret as $ele ) 
				{
					$divs = $ele->find( "div" );
					foreach ( $divs as $div )
					{
						$li = $div->find( "li" );
						foreach ( $li as $e )
						{
							$span1 = $e->find( "span", 0 );
							$span2 = $e->find( "span", 1 );
							$dataDealer["ad_specification"][ str_safe( trim( $span1->innertext ) ) ] = trim( $span2->innertext );
						}
					}
				}
			}
							
			//warranty
			$url = "http://www.autotrader.com/cars-for-sale/vehicledetails/warranty-tab.xhtml?listingId=".$listingId."&Log=0";
			$this->log .= "Fetching warranty: ".$url."<br><br>";
			$html = getContents( $url ); 
							
			//ad_description
			$dataDealer["vehiclewarranty_content"] = "";
			$ret = $html->find( 'h2[class=warranty-name]', 0 );
			if( is_object( $ret ) )
			{
				$dataDealer["vehiclewarranty_content"] = $ret->innertext;
			}
							
			//dealer_id
			$dataDealer["dealer_id"] = $dealer_id;
			
			//dealer info
			$dataDealer["dealer"] = $dealer;
							
			//listingId
			$dataDealer["autotrader_listing_id"] = $listingId;
							
			//zip
			$dataDealer["zip"] = trim( $zip );

			//storeDealers
			$ad_id = $this->storeVehicles( $dataDealer );
		}

		//update cron que: put at top since 10 threads are running
		if( !empty( $ad_id ) )
		{
			$this->db->query( " UPDATE cron_que SET cq_status=1 WHERE cron_que_id=".$cq["cron_que_id"]."" );
		}
			
		//echo $this->log;	
		return true;
	}
		
	/**
	 * @abstract will add/update vehicles in nextauto db as vehicles listed by dealer_id
	 */
	function storeVehicles( $data )
	{
		$stateRow = getStateNameFrAtName( $data["dealer"]["dealer_state"] );
		
		//vehicle ad 
		$dataAd[ "autotrader_listing_id" ] = $data["autotrader_listing_id"];
		$dataAd[ "ad_uniq_code" ] = $data["ad_uniq_code"];
		$dataAd[ "ad_title" ] =  filterTitle( $data["ad_title"] );
		$dataAd[ "ad_description" ] = $data["ad_description"];
		$dataAd[ "ad_features" ] = $data["ad_features"];
		$dataAd[ "ad_seller_comments" ] = $data["ad_seller_comments"];
		$dataAd[ "ad_vehicle_title" ] = $data["ad_vehicle_title"];
		$dataAd[ "ad_engine" ] = isset( $data["ad_specification"]["engine_type"] ) ? $data["ad_specification"]["engine_type"] : "";
		
		//newUsedArr text
		$newUsedArr = explode( " ", $data["ad_vehicle_title"]);
		$dataAd[ "ad_new_or_used" ] = $newUsedArr[0];

		$dataAd[ "ad_power" ] = isset( $data["ad_specification"]["horsepower"] ) ? $data["ad_specification"]["horsepower"] : "";
		$dataAd[ "ad_location" ] = "";
		$dataAd[ "ad_country" ] = 10;
		$dataAd[ "ad_state" ] = $stateRow[ "county_id" ];
		$dataAd[ "ad_city" ] = fetchFieldIfExist( $data["dealer"]["dealer_city"], "city_id", "city_name", "search_tbl_cities", array( "country_id"=>10, "county_id"=>$stateRow[ "county_id" ] ) );
		$dataAd[ "ad_price" ] = $data["ad_price"];
		$dataAd[ "ad_year" ] = getYearFromTitle( $dataAd[ "ad_title" ] );
		$dataAd[ "ad_doors" ] = "";
		$dataAd[ "ad_passengers" ] = "";
		$dataAd[ "ad_mileage" ] = $data["ad_mileage"];
		$dataAd[ "is_featured" ] = "";
		$dataAd[ "ad_vin" ] = $data["ad_vin"];
		$dataAd[ "is_active" ] = 1;
		$dataAd[ "ad_user_id" ] = $data["dealer"]["user_id"];
		$dataAd[ "ad_category" ] = $data["ad_category"];
		$dataAd[ "ad_category_id" ] = fetchFieldIfExist( $data["ad_category"], "category_id", "category_name", "search_tbl_category", array( "category_keyword"=>$data["ad_category"] ) );
		
		//newUsedArr
		$dataAd[ "ad_is_used_car" ] = ( strcasecmp( trim( $newUsedArr[0] ), "Used" ) ? 1 : 0 );
		$dataAd[ "ad_is_new_car" ] = ( strcasecmp( trim( $newUsedArr[0] ), "New" ) ? 1 : 0 );
		
		$resBrand = getFranchiseName( $dataAd[ "ad_title" ] );
		$dataAd[ "ad_brand_id" ] = $resBrand[ "brand_id" ];
		$dataAd[ "ad_model_id" ] = getModelId( $data["ad_vehicle_title"] );
		$dataAd[ "ad_is_viewed" ] = 0;
		$dataAd[ "ad_dealer_id" ] = $data["dealer_id"];
		$dataAd[ "ad_miles" ] = $data["ad_mileage"];
		$dataAd[ "ad_fuel" ] = isset( $data["ad_specification"]["fuel_capacity"] ) ? $data["ad_specification"]["fuel_capacity"] : "";
		$dataAd[ "ad_exterior" ] = $data["user_ext_colorname"];
		$dataAd[ "ad_interior" ] = $data["user_int_colorname"];
		$dataAd[ "user_int_colorcode" ] = $data["user_int_colorcode"];
		$dataAd[ "user_ext_colorcode" ] = $data["user_ext_colorcode"];
		$dataAd[ "user_ext_colorname" ] = $data["user_ext_colorname"];
		$dataAd[ "user_int_colorname" ] = $data["user_int_colorname"];
		$dataAd[ "ad_transmission" ] = $data["ad_transmission"];
		$dataAd[ "ad_condition" ] = $dataAd[ "ad_new_or_used" ];
		$dataAd[ "ad_driver" ] = $data["ad_driver"];
		$dataAd[ "ad_state_txt" ] = $stateRow[ "county_name" ];
		$dataAd[ "ad_city_txt" ] = $data["dealer"]["dealer_city"];
		$dataAd[ "gps_latitude" ] = $data["gps_latitude"];
		$dataAd[ "gps_longitude" ] = $data["gps_longitude"];
				
		//ad_id
		$ad_id = getField( "ad_id", "search_tbl_user_ads", "autotrader_listing_id", $dataAd["autotrader_listing_id"] );
		if( empty( $ad_id ) )
		{
			$this->db->insert( "search_tbl_user_ads", $dataAd );
			$ad_id = $this->db->insert_id();
			echo "Vehicle inserted.<br>";
			
			//user_ad_specification
			$this->db->insert( "search_tbl_user_ad_specification", array( "ad_id"=>$ad_id, "uas_specification_serialized"=>serialize( $data["ad_specification"] ) ) );
		}
		else
		{
			$this->db->set('modified_date', 'NOW()', FALSE);
			$this->db->where( "ad_id", $ad_id )->update( "search_tbl_user_ads", $dataAd );
			echo "Vehicle updated.<br>";

			//user_ad_specification
			$this->db->set('uas_modified_date', 'NOW()', FALSE);
			$this->db->where( "ad_id", $ad_id )->update( "search_tbl_user_ad_specification", array( "uas_specification_serialized"=>serialize( $data["ad_specification"] ) ) );
		}
		
		//ad properties
		saveAdProperties( $ad_id, $data["ad_specification"], $dataAd ) ;
		
		//images: only store in add mode not in update mode
		$ad_image_id = getField( "ad_image_id", "search_tbl_ad_images", "ad_image_ad_id", $ad_id );
		if( is_array( $data["vehicle_image"] ) && sizeof( $data["vehicle_image"] ) > 0 )
		{
			$dataImage["ad_image_ad_id"] = $ad_id;
			foreach( $data["vehicle_image"] as $k=>$ar )
			{
				if( $ar->mediaType == "PHOTO" )	
				{
					$dataImage["ad_image_name"] = saveImageFromUrl( $ar->url );
					$this->db->insert( "search_tbl_ad_images", $dataImage );
				}
			}
		}
		
		//warranty
		$dataWarranty["ad_id"] = $ad_id;
		$dataWarranty["vehiclewarranty_content"] = $data["vehiclewarranty_content"];
		
		//vehiclewarranty_id
		$vehiclewarranty_id = getField( "vehiclewarranty_id", "search_tbl_vehiclewarranty", "ad_id", $ad_id );
		if( empty( $vehiclewarranty_id ) )
		{
			$this->db->insert( "search_tbl_vehiclewarranty", $dataWarranty );
			$vehiclewarranty_id = $this->db->insert_id();
		}
		else
		{
			$this->db->where( "vehiclewarranty_id", $vehiclewarranty_id )->update( "search_tbl_vehiclewarranty", $dataWarranty );
		}
		
		return $ad_id;
	}
	
	
	function getCountryData()
	{
// 	    $string = fopen( asset_url( 'assets/tmp/Country.txt' ), 'r' );
// 	    echo $string;
// 	    die;
	    
	    $fh = fopen( asset_url( 'assets/tmp/Country.txt' ), 'r' );
	    
        while (!feof($fh)) 
        {
            $line = fgets($fh);
            
            $dialCode = fetchSubStr( $line , 'dial-code="', '"');
            $code = fetchSubStr( $line , 'country-code="', '"');
            $countryName = fetchSubStr( $line , '"country-name">', '<');
            
            echo $dialCode." ".$code." ".$countryName."<br>";
        }
        
        fclose($fh);
	}

}

?>