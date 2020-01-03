<?php
/**
 * @package ieh_ 
 * @author hrtech Dev Team
 * @version 2.1.1
 * @ 3Rockets Srl - all right reserved
 * import export helper function
 */

/**
 * @ 3Rockets Srl - all right reserved
 */
	function ieh_successMsg($msg)
	{
		echo '<p><img src="'.load_image("images/admin/enabled.gif").'">&nbsp;&nbsp; '.$msg.'</p><br>'; 
	}

	/**
	 * @ 3Rockets Srl - all right reserved
	 */
	function ieh_warningMsg($msg)
	{
		echo '<p><img src="'.load_image("images/admin/attention.png").'">&nbsp;&nbsp; '.$msg.'</p><br>';
	}
	
/**
 * @ 3Rockets Srl - all right reserved
 */
	function ieh_errorMsg($msg)
	{
		echo '<p><img src="'.load_image("images/admin/disabled.gif").'">&nbsp;&nbsp; '.$msg.'</p><br>';
	}

	function ieh_doneButton( $controller )
	{
		echo '<br><br><a class="button" id="done" href="'.site_url('admin/'.$controller).'" >Done</a><br><br>';
	}
	
/**
 * @ 3Rockets Srl - all right reserved
 */
	function ieh_hideLoader( $controller )
	{
		ieh_doneButton( $controller );
		echo '<script type="text/javascript">
				jQuery("#import_process_loader").hide();
			  </script>';
	}

	/**
	 * @ 3Rockets Srl - all right reserved
	 */
	function ieh_product_mag_exp_ext_xml_ToArr( $rowArr, $j )
	{
		$tempArr = array();
		
		$tempArr[0] = 0;
			
		//seller
		$tempArr[1] = "__SKIP";
		
		//name
		$tempArr[2] = (string)$rowArr[$j]->Cell[6]->Data;
		
		//alias
		$tempArr[3] = "__SKIP";	//(string)$rowArr[$j]->Cell[12]->Data;
			
		//sku
		$tempArr[4] = (string)$rowArr[$j]->Cell[4]->Data;
		if( $tempArr[4] == "01" || $tempArr[4] == "02" || $tempArr[4] == "03" )
		{
			$tempArr[4] = "CW".$tempArr[4];
		}
		
		//category
		$tempArr[5] = "__SKIP";	//(string)$rowArr[$j]->Cell[88]->Data;
		$tempArr[6] = "__SKIP";
		
		/**
		 * product_short_description
		 */
		$tempArr[7] = "__SKIP"; 	//(string)$rowArr[$j]->Cell[48]->Data;
			
		/**
		 * status
		 */
		$tempArr[8] = "__SKIP";
		
		
		/**
		 * price
		 */
		$tempArr[9] = "__KEEP";
		$tempArr[10] = "__KEEP";
		$tempArr[11] = "__KEEP";
			
		
		/**
		 * qty and weight
		 */
		$tempArr[12] = "__SKIP";
		$tempStr = (string) $rowArr[$j]->Cell[104]->Data;
		$tempArr[13] = (float) trim($tempStr);
		
		/**
		 * p_seller_publish_status
		 */
		$tempArr[14] = "__SKIP";
		
		//product_description
		$tempArr[15] = "__SKIP";	//(string)$rowArr[$j]->Cell[47]->Data;
			
		//min qty sales: direct set in POST
		$tempStr = (string) $rowArr[$j]->Cell[63]->Data;
		$tempArr[16] = (float) trim($tempStr);

		//qty_increments: direct set in POST
		$tempStr = (string) $rowArr[$j]->Cell[75]->Data;
		$tempArr[17] = (float) trim($tempStr);
		
		/**
		 * attributes
		 * 
		 */
		//Tipologia Vino = wine_type
		//Regione = manufacture_region
		//Varieta = varieta_uva
		//Anno = year
		//Grado = alcool3 
		//Denominazione = denominazione2
		//Volume = volume
		//Temperatura di servizio = temperatura_di_servizio
		//Abbinamento = abbinamenti
		//Premi e riconoscimenti = premi
		//Winery Tag = NOT FOUND 
		//Cantina = cantina
		//Cantina note = cantinanote SKIP use in abbinamenti2 , Abbinamento2 = abbinamenti2
		//Note degustazione = notedegustazione
		//Cosa ha di interessante = cosahadiinteressante
		//Giudizio Winezon = giudiziowinezon
		//Classificazione = denominazione
		//Denominazione Seo = denominazione2
		
		
		//Tipologia Vino-Attr-0-58
		$attrIndexStart = 18;
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[43]->Data;
		
		//Regione-Attr-1-59
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[42]->Data;

		//Varietà Uva-Attr-2-60
		$tempStr = (string)$rowArr[$j]->Cell[24]->Data;
		$tempArr[ $attrIndexStart++ ] = str_replace(",", "|", $tempStr);
		
		//Anno-Attr-3-61
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[44]->Data;
		
		//Grado Alcolico-Attr-4-62
		$tempStr = "";
		$tempStr = (string)$rowArr[$j]->Cell[109]->Data;
		if( !empty($tempStr) ) { 	$tempStr = $tempStr."%"; 	}
		$tempArr[ $attrIndexStart++ ] = $tempStr;
		
		//Denominazione-Attr-5-63
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[30]->Data;
		
		//Volume-Attr-6-64
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[45]->Data;
		
		//Temperatura di servizio-Attr-7-65
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[46]->Data;
		
		//Abbinamento-Attr-8-66
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[26]->Data;
		
		//Premi e riconoscimenti-Attr-9-67
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[25]->Data;
		
		//Winery Tag-Attr-10-68
		$tempArr[ $attrIndexStart++ ] = "__KEEP";
		
		//Cantina-Attr-11-69
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[28]->Data;
		
		//Abbinamento2-Attr-12-70
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[33]->Data;
		
		//Note degustazione-Attr-13-71
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[32]->Data;
		
		//Cosa ha di interessante-Attr-14-72
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[105]->Data;
		
		//Giudizio Winezon-Attr-15-73
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[29]->Data;
		
		//Classificazione-Attr-16-74
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[108]->Data;

		//Denominazione Seo-Attr-17-82
		$tempArr[ $attrIndexStart++ ] = (string)$rowArr[$j]->Cell[30]->Data;
		
		return $tempArr;
	}
	
	
?>