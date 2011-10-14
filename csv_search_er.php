<?php

require("config.inc.php");
require("currency.inc.php");

session_name($cfg["session"]);
session_start();
if (!empty($_SESSION) && isset($_SESSION["username"])) {
   $username = $_SESSION["username"];
   $priv = $_SESSION["priv"];
   $fullname = $_SESSION["fullname"];
} else {
   header("Status: 302 Found");
   header("Location: login.php");
}

function display_date($date) {
// Converts an ISO format date selected from database to local format for display.
   global $cfg;
   list($year, $month, $day) = split("-", $date);
   $date = date($cfg["date_arg"], mktime(0, 0, 0, $month, $day, $year));
   return $date;
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=Export_ER.csv' );
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

require("grab_globals.inc.php");
require("connection.inc.php"); 
require("er_common.inc.php"); 

ob_clean();
flush();

if (!$summary = $db->Execute($_SESSION["search_query"])) {
   echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
   die();
}
  //echo $_SESSION[ "context" ]. "\n";
   echo 'Requisition,"Created By",Date,Section,Description,Status,Requested,Prepaid,Balance' . "\n";
   $i = 1;
   while (!$summary->EOF) {
      echo	'"'. $summary->fields["draft_number"] . '"'. "," . '"'. $summary->fields["created_by"] . '"'. "," . '"'. display_date($summary->fields["date"]) . '"'. "," . 
		'"'. $summary->fields["name"] . '"'. "," . '"'. $summary->fields["description"] . '"'. "," . '"'. $summary->fields["status"] . '"'. ",";
	$total_requested = get_er_total($summary->fields["draft_number"]);
	$total_prepaid = get_er_advance($summary->fields["draft_number"]) + get_er_prepaid($summary->fields["draft_number"]);

      echo '"'. $total_requested . '"'. ",";
      echo '"'. $total_prepaid . '"'. ",";
      echo '"'. sprintf("%01.2f",$total_requested - $total_prepaid) . '"'. "\n";
      $summary->MoveNext();
   }

$notHelp="yes" ;
