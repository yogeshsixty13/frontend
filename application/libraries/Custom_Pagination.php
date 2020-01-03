<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------
/**
 * Pagination Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Pagination
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/pagination.html
 */
class Custom_Pagination extends CI_Pagination{
	/**
	 * Generate the pagination links
	 *
	 * @access	public
	 * @return	string
	 */
	var $xtra;
	 
	function create_links()
	{
		$CI =& get_instance();
		
		//if mobile view
		$mob_view = ( $CI->session->userdata('lType')=='M') ? "data-ajax='false'" : '';
		 //echo preg_replace('/(&|\?)'.$this->query_string_segment.'=(\d+)/', '', '?per_page=5&sam=4');
		//die;
		// If our item count or per-page total is zero there is no need to continue.
		if ($this->total_rows == 0 OR $this->per_page == 0)
		{
			return '';
		}
		// Calculate the total number of pages
		$num_pages = ceil($this->total_rows / $this->per_page);
		// Is there only one page? Hm... nothing more to do here then.
		if ($num_pages == 1)
		{
			return '';
		}
		// Determine the current page number.
		
		if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
		{
			if ($CI->input->get($this->query_string_segment) != 0)
			{
				$this->cur_page = $CI->input->get($this->query_string_segment);
				// Prep the current page - no funny business!
				$this->cur_page = (int) $this->cur_page;
			}
		}
		else
		{
			if ($CI->uri->segment($this->uri_segment) != 0)
			{
				$this->cur_page = $CI->uri->segment($this->uri_segment);
				// Prep the current page - no funny business!
				$this->cur_page = (int) $this->cur_page;
			}
		}
		$this->num_links = (int)$this->num_links;
		if ($this->num_links < 1)
		{
			show_error('Your number of links must be a positive number.');
		}
		if ( ! is_numeric($this->cur_page))
		{
			$this->cur_page = 0;
		}
		// Is the page number beyond the result range?
		// If so we show the last page
		if ($this->cur_page > $this->total_rows)
		{
			$this->cur_page = ($num_pages - 1) * $this->per_page;
		}
		$uri_page_number = $this->cur_page;
		$this->cur_page = floor(($this->cur_page/$this->per_page) + 1);
		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
		$end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;
		// Is pagination being used over GET or POST?  If get, add a per_page query
		// string. If post, add a trailing slash to the base URL if needed
		if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
		{
			$q = $_SERVER['QUERY_STRING'];
			$query = '?';
			if($q != '')
			{
				parse_str($q,$qa);
				if(isset($qa['per_page']))
					unset($qa['per_page']);
				
				$query.= http_build_query($qa);			
			}
					
			$this->base_url = current_url().$query.(($query != '?') ? '&' : '').$this->query_string_segment.'=';	
		}
		else
		{
			$this->base_url = rtrim($this->base_url, '/') .'/';
		}
		// And here we go...
		$output = '';
		// Render the "First" link
		if  ($this->first_link !== FALSE AND $this->cur_page > ($this->num_links + 1))
		{
			$first_url = ($this->first_url == '') ? $this->base_url : $this->first_url;
			$output .= $this->first_tag_open.'<a '.$this->anchor_class.'href="'.$first_url.$this->qs().'" class="first" title="First Page" '.$mob_view.'>'.$this->first_link.'</a>'.$this->first_tag_close;
		}
		// Render the "previous" link
		if  ($this->prev_link !== FALSE AND $this->cur_page != 1)
		{
			$i = $uri_page_number - $this->per_page;
			if ($i == 0 && $this->first_url != '')
			{
				$output .= $this->prev_tag_open.'<a '.$this->anchor_class.'href="'.$this->first_url.$this->qs().'" class="prev" title="Prev Page" '.$mob_view.'>'.$this->prev_link.'</a>'.$this->prev_tag_close;
			}
			else
			{
				$i = ($i == 0) ? '' : $this->prefix.$i.$this->suffix;
				$output .= $this->prev_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$i.$this->qs().'" class="prev" title="Prev Page" '.$mob_view.'>'.$this->prev_link.'</a>'.$this->prev_tag_close;
			}
		}
		// Render the pages
		if ($this->display_pages !== FALSE)
		{
			// Write the digit links
			for ($loop = $start -1; $loop <= $end; $loop++)
			{
				$i = ($loop * $this->per_page) - $this->per_page;
				if ($i >= 0)
				{
					$n = ($i == 0) ? '' : $i;
					
					if ($this->cur_page == $loop)
					{
						$output .= "<b>".$this->cur_tag_open.$loop.$this->cur_tag_close."</b>"; 	// Current page
					}
					else
					{
						if ($n == '' && $this->first_url != '')
						{
							$output .= $this->num_tag_open.'<a '.$this->anchor_class.'href="'.$this->first_url.$this->qs().'" title="Page '.$loop.'" '.$mob_view.'>'.$loop.'</a>'.$this->num_tag_close;
						}
						else
						{
							$n = ($n == '') ? '' : $this->prefix.$n.$this->suffix;
							$output .= $this->num_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$n.$this->qs().'" title="Page '.$loop.'" '.$mob_view.'>'.$loop.'</a>'.$this->num_tag_close;
						}
					}
				}
			}
		}
		// Render the "next" link
		if ($this->next_link !== FALSE AND $this->cur_page < $num_pages)
		{
			$output .= $this->next_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$this->prefix.($this->cur_page * $this->per_page).$this->suffix.$this->qs().'" class="next" title="Next Page" '.$mob_view.'>'.$this->next_link.'</a>'.$this->next_tag_close;
		}
		// Render the "Last" link
		if ($this->last_link !== FALSE AND ($this->cur_page + $this->num_links) < $num_pages)
		{
			$i = (($num_pages * $this->per_page) - $this->per_page);
			$output .= $this->last_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$this->prefix.$i.$this->suffix.$this->qs().'" class="last" title="Last Page" '.$mob_view.'>'.$this->last_link.'</a>'.$this->last_tag_close;
		}
		// Kill double slashes.  Note: Sometimes we can end up with a double slash
		// in the penultimate link so we'll kill all double slashes.
		$output = preg_replace("#([^:])//+#", "\\1/", $output);
		// Add the wrapper HTML if exists
		$output = $this->full_tag_open.$output.$this->full_tag_close;
		return $output;
	}
	function qs()
	{
		$CI =& get_instance();
		if($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
			return '';
		
		$qs = $_SERVER['QUERY_STRING'];
			
		if($qs != '')
			parse_str($qs,$qa);
				
		if(substr_count($this->base_url,'?') == 0 && $qs != '')
			return '?'.$qs;
		else if($qs != '')
			return '&'.$qs;
		else
			return '';
	}
}
// END Pagination Class
/* End of file Pagination.php */
/* Location: ./system/libraries/Pagination.php */