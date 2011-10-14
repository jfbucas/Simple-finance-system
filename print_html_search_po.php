<?php
function display_date($date) {
// Converts an ISO format date selected from database to local format for display.
   global $cfg;
   list($year, $month, $day) = split("-", $date);
   $date = date($cfg["date_arg"], mktime(0, 0, 0, $month, $day, $year));
   return $date;
}

require("config.inc.php");
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

require("grab_globals.inc.php");
require("connection.inc.php"); ?>
<html>
<head>
	<title>Print HTML Search Result</title>
   <link rel="STYLESHEET" type="text/css" href="style.css">
</head>
<body> <?php
if (!$summary = $db->Execute($_SESSION["search_query"])) {
   echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
   die();
} ?>
<table class="small" width="100%" border="0" cellspacing="0" cellpadding="1">
   <tr class="row_head">
      <td><b>Req.</b></td>
      <td><b>Created By</b></td>
      <td><b>Date</b></td>
      <td><b>Organization</b></td>
      <td><b>Supplier</b></td>
      <td><b>Status</b></td>
   </tr> <?php
   $i = 1;
   while (!$summary->EOF) {
      if ($i % 2 == 0) {
         echo "<tr class=\"report_even\">";
      } else {
         echo "<tr class=\"report_odd\">";
      }
      echo "<td nowrap>" . $summary->fields["draft_number"] . "</td>";
      echo "<td nowrap>" . $summary->fields["created_by"] . "</td>";
      echo "<td nowrap>" . display_date($summary->fields["date"]) . "</td>";
      echo "<td nowrap>" . $summary->fields[4] . "</td>";
      echo "<td nowrap>" . $summary->fields[5] . "&nbsp;&nbsp;</td>";
      if ($summary->fields["open"] == "Y" && $summary->fields["approved"] == "N") {
         echo "<td nowrap>Open</td></tr>";
      } else if ($summary->fields["open"] == "Y" && $summary->fields["approved"] == "Y") {
         echo "<td nowrap>Approved</td></tr>";
      } else if ($summary->fields["open"] == "N" ) {
         echo "<td nowrap>Closed</td></tr>";
      } else {
         echo "<td nowrap>Canceled</td></tr>";
      }
      $i++;
      $summary->MoveNext();
   } ?>
</table>

<?php $notHelp="yes" ; require("footer.inc.php"); ?>
