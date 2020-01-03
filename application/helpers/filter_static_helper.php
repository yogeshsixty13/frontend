<?php
/**
 * @package he_: flt_st_
 * @author Cloudwebs Tech Dev Team
 * @version 1.0
 * @abstract common helper
 * @copyright Cloudwebs Tech
 */

/**
 * @abstract function will return category alias to id map array
 */
function catAliasIdMap()
{
// 	return array( 'vegetables'=>284, 'fresh-vegetables'=>285, 'imported-vegetables'=>286, 'leafy-vegetables'=>287,
// 			'fruits'=>288, 'fresh-fruits'=>289, 'imported-fruits'=>290, 'special-offers'=>296, 'synthetic-diamonds'=>304, 'fashion'=>301, 'home-appliances'=>300, 
// 			'sarees'=>315 );

	$result = array();
	$res = executeQuery("SELECT category_id, category_alias FROM product_categories ");
	
	/**
	 * not checked empty condition since it is always expected to have results.
	 */ 
	foreach ($res as $k=>$ar)
	{
		$result[$ar["category_alias"]] = $ar["category_id"]; 
	}
	
	return $result;
}

/**
 * HELD FOR REMOVAL
 * @deprecated
 * @abstract function will return price filter map
 */
function priceMap()

{
	//@deprecated: JEWELLERY PTRICE FILTER
	return array( 'below-'.str_replace( array( " ", "&nbsp;" ), "-", lp(10000))=>'0-10000',
			'below-'.str_replace( array( " ", "&nbsp;" ), "-", lp(20000))=>'0-20000',
			'below-'.str_replace( array( " ", "&nbsp;" ), "-", lp(30000))=>'0-30000',
			'below-'.str_replace( array( " ", "&nbsp;" ), "-", lp(40000))=>'0-40000',
			'below-'.str_replace( array( " ", "&nbsp;" ), "-", lp(50000))=>'0-50000',
			str_replace( array( " ", "&nbsp;" ), "-", lp(10000)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(20000))=>'10000-20000',
			str_replace( array( " ", "&nbsp;" ), "-", lp(10000)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(30000))=>'10000-30000',
			str_replace( array( " ", "&nbsp;" ), "-", lp(10000)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(40000))=>'10000-40000',
			str_replace( array( " ", "&nbsp;" ), "-", lp(10000)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(50000))=>'10000-50000',
			str_replace( array( " ", "&nbsp;" ), "-", lp(20000)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(30000))=>'20000-30000',
			str_replace( array( " ", "&nbsp;" ), "-", lp(20000)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(40000))=>'20000-40000',
			str_replace( array( " ", "&nbsp;" ), "-", lp(20000)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(50000))=>'20000-50000',
			str_replace( array( " ", "&nbsp;" ), "-", lp(30000)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(40000))=>'30000-40000',
			str_replace( array( " ", "&nbsp;" ), "-", lp(30000)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(50000))=>'30000-50000',
			str_replace( array( " ", "&nbsp;" ), "-", lp(40000)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(50000))=>'40000-50000',
			'above-'.str_replace( array( " ", "&nbsp;" ), "-", lp(10000))=>'10000-0',
			'above-'.str_replace( array( " ", "&nbsp;" ), "-", lp(20000))=>'20000-0',
			'above-'.str_replace( array( " ", "&nbsp;" ), "-", lp(30000))=>'30000-0',
			'above-'.str_replace( array( " ", "&nbsp;" ), "-", lp(40000))=>'40000-0',
			'above-'.str_replace( array( " ", "&nbsp;" ), "-", lp(50000))=>'50000-0' );

	//@deprecated: GROCERY PTRICE FILTER
	return array( 'below-'.str_replace( array( " ", "&nbsp;" ), "-", lp(30))=>'0-30',
			'below-'.str_replace( array( " ", "&nbsp;" ), "-", lp(20000))=>'0-50',
			'below-'.str_replace( array( " ", "&nbsp;" ), "-", lp(30000))=>'0-70',
			'below-'.str_replace( array( " ", "&nbsp;" ), "-", lp(40000))=>'0-90',
			'below-'.str_replace( array( " ", "&nbsp;" ), "-", lp(50000))=>'0-100',
			str_replace( array( " ", "&nbsp;" ), "-", lp(30)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(50))=>'30-50',
			str_replace( array( " ", "&nbsp;" ), "-", lp(30)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(70))=>'30-70',
			str_replace( array( " ", "&nbsp;" ), "-", lp(30)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(90))=>'30-90',
			str_replace( array( " ", "&nbsp;" ), "-", lp(30)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(100))=>'30-100',
			str_replace( array( " ", "&nbsp;" ), "-", lp(50)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(70))=>'50-70',
			str_replace( array( " ", "&nbsp;" ), "-", lp(50)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(90))=>'50-90',
			str_replace( array( " ", "&nbsp;" ), "-", lp(50)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(100))=>'50-100',
			str_replace( array( " ", "&nbsp;" ), "-", lp(70)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(90))=>'70-90',
			str_replace( array( " ", "&nbsp;" ), "-", lp(70)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(100))=>'70-100',
			str_replace( array( " ", "&nbsp;" ), "-", lp(90)).'-to-'.str_replace( array( " ", "&nbsp;" ), "-", lp(100))=>'90-100',
			'above-'.str_replace( array( " ", "&nbsp;" ), "-", lp(30))=>'30-0',
			'above-'.str_replace( array( " ", "&nbsp;" ), "-", lp(50))=>'50-0',
			'above-'.str_replace( array( " ", "&nbsp;" ), "-", lp(70))=>'70-0',
			'above-'.str_replace( array( " ", "&nbsp;" ), "-", lp(90))=>'90-0',
			'above-'.str_replace( array( " ", "&nbsp;" ), "-", lp(100))=>'100-0' );
}

/**
 * @abstract function will return gender map
 */
function genderMap( )
{
	return array( 'for-women'=>'F', 'for-men'=>'M');
}

/**
 * @abstract function will return metal map: right now ony map for gold type
 */
function metalMap( )
{
	return array( '18k-yellow-gold'=>'1-4', '14k-yellow-gold'=>'1-3', '10k-yellow-gold'=>'1-2',
			'18k-rose-gold'=>'2-4', '14k-rose-gold'=>'2-3', '10k-rose-gold'=>'2-2',
			'18k-white-gold'=>'3-4', '14k-white-gold'=>'3-3', '10k-white-gold'=>'3-2' );
}

function metal_colorMap()
{
	return array( 'Yellow'=>1, 'Rose'=>2, 'White'=>3 );
}

/**
 * @abstract function will return diamond map
 */
function diamondMap()
{
	return array( 'IJ-SI'=>1, 'GH-SI'=>2, 'GH-VS'=>3 );
}

/**
 * @abstract function will return diamond map
 */
function gemstoneMap()
{
	return array( 'Ruby'=>7, 'Gemstone-U'=>55, 'Blue-Topaz'=>56, 'Emerald'=>57, 'Tourmaline'=>58, 'Sapphire'=>59, 'Citrine'=>60,
			'Amethyst'=>68 );
}

/**
 * @abstract function will return diamond map
 */
function pearlMap()
{
	return array( 'Chinese-Freshwater-Pearl'=>61 );
}

/**
 * @abstract function will return diamond map
 */
function diamond_typeMap()
{
	return array( 'Diamond'=>1, 'Gemstone'=>2, 'Pearl'=>3 );
}
