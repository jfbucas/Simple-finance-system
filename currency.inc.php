<?php
	// Found here: http://www.ecb.int/stats/exchange/eurofxref/html/index.en.html

    //This is a PHP(4/5) script example on how eurofxref-daily.xml can be parsed
    //Read eurofxref-daily.xml file in memory 
    //For this command you will need the config option allow_url_fopen=On (default)
    $XMLContent=file("/var/www/currency/eurofxref-daily.xml");
    //the file is updated daily between 2.15 p.m. and 3.00 p.m. CET
            
    foreach($XMLContent as $line){
        if(preg_match("/currency='([[:alpha:]]+)'/",$line,$currencyCode)){
            if(preg_match("/rate='([[:graph:]]+)'/",$line,$rate)){
                //Output the value of 1EUR for a currency code
                //echo'1&euro;='.$rate[1].' '.$currencyCode[1].'<br/>';

		$exchange_rates[ $currencyCode[1] ] = 1/$rate[1];

            }
        }
    }
    $exchange_rates[ "EUR" ] = 1;






function get_currency_sign( $currency_code, $symboletype = "html" ) {

  if ( $symboletype == "html" ) {
	  switch ( $currency_code ) {
	     case "e": $currsign="&#8364;"; break; // "euro;"; break; // http://ie2.php.net/manual/en/function.imagettftext.php#75493
	     case "d": $currsign="$"; break;
	     case "l": $currsign="&pound;"; break;
	     case "c": $currsign="$ (CAN)"; break;
	     case "o": $currsign="#"; break;
	   }
  } else if ( $symboletype == "text" ) {
	  switch ( $currency_code ) {
	     case "e": $currsign="euro"; break; // "euro;"; break; // http://ie2.php.net/manual/en/function.imagettftext.php#75493
	     case "d": $currsign="dollar"; break;
	     case "l": $currsign="pound"; break;
	     case "c": $currsign="Canadian dollar"; break;
	     case "o": $currsign="Other"; break;
	   }
  } else if ( $symboletype == "eurofxref" ) {
	  switch ( $currency_code ) {
	     case "e": $currsign="EUR"; break; // "euro;"; break; // http://ie2.php.net/manual/en/function.imagettftext.php#75493
	     case "d": $currsign="USD"; break;
	     case "l": $currsign="GBP"; break;
	     case "c": $currsign="CAD"; break;
	     case "o": $currsign="###"; break;
	   }
  } else if ( $symboletype == "char" ) {
	  switch ( $currency_code ) {
	     case "e": $currsign=chr(128); break; // "euro;"; break; // http://ie2.php.net/manual/en/function.imagettftext.php#75493
	     case "d": $currsign=chr(36); break;
	     case "l": $currsign=chr(163); break;
	     case "c": $currsign=chr(36) . "(CAN)"; break;
	     case "o": $currsign='#'; break;
	   }
   }
   return $currsign;
}

	
?>
