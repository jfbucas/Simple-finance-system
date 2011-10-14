<?php

// Define a wrapping function to prevent mails going spamming for testing purposes 
function do_mail( $to, $subject, $message, $headers = "" ) {
	global $cfg;

	if ( $cfg[ "testing" ] == "dev" ) {
		$subject = "TEST : " . $to . " - " . $subject;
		$to = $cfg["sysadmin_email"];

		$headers_array = explode( "\r\n", $headers);
		$to_header = preg_grep( "/^To:/", $headers_array );
		$not_to_header = preg_grep( "/^To:/", $headers_array, PREG_GREP_INVERT );

		foreach ($to_header as $t => $value) {
			$value = preg_replace( "/^To: /", "", $value );
			$value = preg_replace( "/\r/", "", $value );
			$to .= ',' . $value;
		}

		$headers = implode( "\r\n", $not_to_header );
		$headers = preg_replace( "/[a-zA-Z\.]*@[a-z]*.". $cfg["email_zone"] . "/", $cfg["sysadmin_email"], $headers );
		$headers = preg_replace( "/[a-zA-Z\.]*@[A-Z]*.". $cfg["EMAIL_ZONE"] . "/", $cfg["sysadmin_email"], $headers );

		$to = preg_replace( "/[a-zA-Z\.]*@[a-z]*.". $cfg["email_zone"] . "/", $cfg["sysadmin_email"], $to );
		$to = preg_replace( "/[a-zA-Z\.]*@[A-Z]*.". $cfg["EMAIL_ZONE"] . "/", $cfg["sysadmin_email"], $to );
		
		//echo "<pre>Send mail : \n[ $to ] \n[ $subject ] \n[ $message ] \n[ $headers ]</pre>\n";
		mail( $to, $subject, $message, $headers);
	}else if ( $cfg[ "testing" ] == "rc" ) {
		$headers_array = explode( "\r\n", $headers);
		$to_header = preg_grep( "/^To:/", $headers_array );
		$not_to_header = preg_grep( "/^To:/", $headers_array, PREG_GREP_INVERT );

		foreach ($to_header as $t => $value) {
			$value = preg_replace( "/^To: /", "", $value );
			$value = preg_replace( "/\r/", "", $value );
			$to .= ',' . $value;
		}

		$headers = implode( "\r\n", $not_to_header );

		mail( $to, $subject, $message, $headers);
	}else{
		$headers_array = explode( "\r\n", $headers);
		$to_header = preg_grep( "/^To:/", $headers_array );
		$not_to_header = preg_grep( "/^To:/", $headers_array, PREG_GREP_INVERT );

		foreach ($to_header as $t => $value) {
			$value = preg_replace( "/^To: /", "", $value );
			$value = preg_replace( "/\r/", "", $value );
			$to .= ',' . $value;
		}

		$headers = implode( "\r\n", $not_to_header );

		mail( $to, $subject, $message, $headers);
	}
}

?>
