<?php
// Disconnect from the database
$db->Close(); 

if ( ! isset($notHelp) ) {

	echo "<br><br>";
	echo "<div class='footer' align='center' onclick=\"window.open('help/index.html')\"><a href=\"\">Need Help ?</a> or if anything is not working as expected please email the <a href=\"mailto:" . $cfg["support_email"] . "\">support</a>.</div>";

}

if ( $cfg["testing"] != "" ) {

	$time_end = microtime(true);
	$time = $time_end - $time_start;

 	echo "<div class='footer' align='center'>Generated in $time seconds</div>";
} 

?>
</body>
</html>
