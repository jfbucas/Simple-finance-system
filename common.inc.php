<?php
require("config.inc.php");
require("log.inc.php");
require("mail.inc.php");
require("currency.inc.php");
require("po_common.inc.php");
require("tr_common.inc.php");
require("er_common.inc.php");
require("attached_document.inc.php");


ini_set("session.gc_maxlifetime", $cfg["session_expire"]); 
session_name($cfg["session"]);

/* set the cache limiter to 'private' */
session_cache_limiter('private');
$cache_limiter = session_cache_limiter();

/* set the cache expire to x minutes */
session_cache_expire($cfg["session_cache_expire"]);
$cache_expire = session_cache_expire();

//echo "The cache limiter is now set to $cache_limiter<br />";
//echo "The cached session pages expire after $cache_expire minutes";

/* start the session */
session_start();

if (!empty($_SESSION) && isset($_SESSION["username"])) {
   $username = $_SESSION["username"];
// AJ
   $user_role = $_SESSION["user_role"];
   $priv = $_SESSION["priv"];
   $fullname = $_SESSION["fullname"];
} else {
   header("Status: 302 Found");
   header("Location: login.php");
}

// Tell browsers not to cache this page
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");

// Include required files
require("grab_globals.inc.php");
require("connection.inc.php");
require("header.inc.php");

// Some functions needed by several pages. Try to move functions required often
// to this file.

function paged_query($db, $page = 1) {
// Used by search_*.php to generate paged recordsets.
   global $cfg;
   // First, check for an empty result.
   if (!$rstest = $db->SelectLimit($_SESSION["search_query"], 1)) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
   //if ($rstest->RecordCount() == 0) {
   //   echo "<table class=\"info\" width=\"100%\"><tr><td>Your search returned 0 results.</td></tr></table>";
   //   return FALSE;
   //}
   $rstest->Close();
   // Recordset is not empty so go ahead and page it.
   if (!$summary = $db->PageExecute($_SESSION["search_query"], $cfg["lpp"], $page)) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
   return $summary;
}

function valid_date($date) {
// Validates and converts local format date to ISO format suitable for
// insertion into a database -OR- calculates a date relative to today given a
// number of days in the future or in the past.
   global $cfg;
   if (ereg("^([\+\-]{1})([0-9]+)$", $date, $shortcut)) {
   // $date is a shortcut so calculate and return a date in the future or the
   // in the past relative to today.
      $date = date("Y-m-d");
      list($year, $month, $day) = split("-", $date);
      switch ($shortcut[1]) {
         case "+":
            $date = date("Y-m-d", mktime(0, 0, 0, $month, $day + $shortcut[2], $year));
            break;
         case "-":
            $date = date("Y-m-d", mktime(0, 0, 0, $month, $day - $shortcut[2], $year));
            break;
      }
   return $date;
   }
   switch ($cfg["date_fmt"]) {
   // $date is a date in one format or another. Convert to and return an
   // ISO format date if required or FALSE on error.
      case "usa":
         if (!ereg("^[0-9]{2}\/{1}[0-9]{2}\/{1}[0-9]{4}$", $date)) return FALSE;
         list($month, $day, $year) = split("/", $date);
         $date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
         break;
      case "int":
         if (!ereg("^[0-9]{2}\/{1}[0-9]{2}\/{1}[0-9]{4}$", $date)) return FALSE;
         list($day, $month, $year) = split("/", $date);
         $date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
         break;
      default:
         if (!ereg("^[0-9]{4}\-{1}[0-9]{2}\-{1}[0-9]{2}$", $date)) return FALSE;
   }
   return $date;
}

function display_date($date) {
// Converts an ISO format date selected from database to local format for display.
   global $cfg;
   list($year, $month, $day) = split("-", $date);
   $date = date($cfg["date_arg"], mktime(0, 0, 0, $month, $day, $year));
   return $date;
}


function valid_warranty($rcv_date, $period) {
// Calculates a valid warranty expiration date given a valid receive date and
// a warranty period in years. OR, validates date input format. Returns a valid
// date in ISO format in the future or the unmodified receive date by default.
   if (ereg("^([\+]{1})([0-9]+)$", $period, $shortcut)) {
   // $period is a shortcut so calculate the expiry date.
      if ($shortcut[2] > 99) {
         $shortcut[2] = 99;
      }
      list($year, $month, $day) = split("-", $rcv_date);
      $exp_date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year + $shortcut[2]));
   } else if (!$exp_date = valid_date($period)) { 
   // $period is an absolute date so validate it. Return the receive date if that fails.
      $exp_date = $rcv_date;
   }
   return $exp_date;
}

function valid_po($db, $po) {
// Checks for valid purchase order that exists in the database.
// If user left the field blank, return TRUE.
// If user entered something else, check for a valid PO.
//   if (empty($po)) {
//      return TRUE;
//   }
//   $record = $db->Execute("SELECT * FROM po WHERE draft_number=$po");
//   if ($record->RecordCount() == 0) {
//      return FALSE;
//   }
   return TRUE;
}

function valid_tag($tag) {
// Checks for valid tag input format. Returns TRUE or FALSE.
//   if (!ereg("^[1-9]{1}[0-9]{4}$", $tag)) {
//      return FALSE;
//   }
   return TRUE;
}

function valid_char_1($value) {
// Checks to ensure character 1 of string value is a valid alphanumeric character
// and not a space or other character.
   if (!ereg("^[a-zA-Z0-9]{1}.*$", $value)) {
      return FALSE;
   }
   return TRUE;
}




function po_buttons($title = true) {
global $cfg;
?>

	<?php if ( ! $title ) { ?>
		<br>
	<?php } ?>

    <table class="menu" border="0" align="center" cellpadding="0" width="40%">
      <?php if ( $title ) { ?>
            <tr><td colspan=4 align="center" valign="center">
       		    <h2 align="center"> <img src="images/emblem-purchase.png" alt="euros" border="0"> Purchase Order
			<a href="help/PO_procedure_small.png" rel="lightbox" title="Purchase Order Procedure"><img src="images/lightbulb.png" border=0></a>
		</h2>
            </td></tr>
	<?php } ?>

      <tr>
      <?php if ( ! $title ) { ?>
		<td><img src="images/emblem-purchase.png" alt="euros" border="0"></td>
	<?php } ?>
	<td align="center" width="25%">
            <a href="po.php"><img src="images/stock_new-tab.png" border="0"></br>New Purchase</a>
         </td>
         <td align="center" width="25%">
            <a href="search_po.php?action=search_all&order_by=draft_number&order=DESC"><img src="images/stock_index.png" border="0"></br>List</a>
         </td>
	 <td align="center" width="25%">
            <a href="search_po_items.php?action=search_all&order_by=draft_number&order=DESC"><img src="images/stock_index.png" border="0"></br>List Items</a>
         </td>
         <td align="center" width="25%">
            <a href="search_po.php"><img src="images/stock_find.png" border="0"></br>Search</a>
      </td></tr>
   </table>
	<?php if ( ! $title ) { ?>
		<br>
	<?php } ?>

<?php }

function tr_buttons($title = true) {
global $cfg;
?>
	<?php if ( ! $title ) { ?>
		<br>
	<?php } ?>

    <table class="menu" border="0" align="center" cellpadding="0" width="40%">
      <?php if ( $title ) { ?>
            <tr><td colspan=3 align="center" valign="center">
       		    <h2 align="center"> <img src="images/emblem-plane.png" alt="euros" border="0"> Travel Request
			<a href="help/TR_procedure_small.png" rel="lightbox" title="Travel Request Procedure"><img src="images/lightbulb.png" border=0></a>
		</h2>
            </td></tr>
	<?php } ?>

      <tr>
      <?php if ( ! $title ) { ?>
		<td><img src="images/emblem-plane.png" alt="euros" border="0"></td>
	<?php } ?>
         <td align="center" width="33%">
            <a href="tr.php"><img src="images/stock_new-tab.png" border="0"></br>New Travel</a>
         </td>
         <td align="center" width="33%">
            <a href="search_tr.php?action=search_all&order_by=draft_number&order=DESC"><img src="images/stock_index.png" border="0"></br>List</a>
         </td>
         <td align="center" width="33%">
            <a href="search_tr.php"><img src="images/stock_find.png" border="0"></br>Search</a>
      </td></tr>
   </table>
	<?php if ( ! $title ) { ?>
		<br>
	<?php } ?>

<?php }

function er_buttons($title = true) {
global $cfg;
?>
	<?php if ( ! $title ) { ?>
		<br>
	<?php } ?>

    <table class="menu" border="0" align="center" cellpadding="0" width="40%">
      <?php if ( $title ) { ?>
            <tr><td colspan=3 align="center" valign="center">
       		    <h2 align="center"> <img src="images/emblem-money3.png" alt="euros" border="0"> Expense Report
			<a href="help/ER_procedure_small.png" rel="lightbox" title="Expense Report Procedure"><img src="images/lightbulb.png" border=0></a>
			</h2>
            </td></tr>
	<?php } ?>

      <tr>
      <?php if ( ! $title ) { ?>
		<td><img src="images/emblem-money3.png" alt="euros" border="0"></td>
	<?php } ?>
         <td align="center" width="33%">
            <a href="er.php"><img src="images/stock_new-tab.png" border="0"></br>New Expense</a>
         </td>
         <td align="center" width="33%">
            <a href="search_er.php?action=search_all&order_by=draft_number&order=DESC"><img src="images/stock_index.png" border="0"></br>List</a>
         </td>
         <td align="center" width="33%">
            <a href="search_er.php"><img src="images/stock_find.png" border="0"></br>Search</a>
      </td></tr>
   </table>
	<?php if ( ! $title ) { ?>
		<br>
	<?php } ?>

<?php }

function finance_buttons($title = true) {
global $cfg;
?>
	<?php if ( ! $title ) { ?>
		<br>
	<?php } ?>

    <table class="menu" border="0" align="center" cellpadding="0" width="40%">
      <?php if ( $title ) { ?>
            <tr><td colspan=5 align="center" valign="center">
       		    <h2 align="center"> <!--div class=".Money2"--><img src="images/emblem-money.png" alt="euros" border="0"><!--/div--> Finance Department </h2>
            </td></tr>
	<?php } ?>

      <tr><td align="center" width="20%">
            <a href="finance_po.php?action=search_all&order_by=draft_number&order=DESC"><img src="images/emblem-purchase.png" border="0"></br>Manage</a>
         </td>
         <td align="center" width="20%">
            <a href="finance_tr.php?action=search_all&order_by=draft_number&order=DESC"><img src="images/emblem-plane.png" border="0"></br>Manage</a>
         </td>
         <td align="center" width="20%">
            <a href="finance_er.php?action=search_all&order_by=draft_number&order=DESC"><img src="images/emblem-money3.png" border="0"></br>Manage</a>
         </td>
         <td align="center" width="20%">
            <a href="section.php"><img src="images/stock_book-open.png" border="0"></br>Sections</a>
         </td>
         <td align="center" width="25%">
            <a href="vendor.php"><img src="images/gnome-package.png" border="0"></br>Suppliers</a>
      </td></tr>
   </table>
	<?php if ( ! $title ) { ?>
		<br>
	<?php } ?>

<?php }

function general_menu($title = true) {
global $cfg, $user_role;
?>
	<table class="default" border="0" align="center" cellpadding="10" width="80%"><tr><td>
      <?php if ( $title ) { ?>
      <table class="default" border="0" cellpadding="0" cellspacing="0" width="100%">
         <tr>
         <td align="center">
         <h2><?php echo $cfg["title"]; ?></h2>
               </br>


		<?php
		 if ( $_SERVER['SERVER_NAME'] == $cfg["oldbaseurl"] ) { ?>
			<a href="<?php echo $cfg["baseurl"]; ?>">New address <?php echo $cfg["baseurl"];?></a><br>
			<font color=red>  Please update your bookmarks ! </font>
			</br>
			</br>
			</br>
		<?php } ?>
         </td>
       	 </tr>
	 </table>
	<?php }

	if (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) {
		finance_buttons(true); 
		echo '<br/>';
	}

	tr_buttons($title);
	echo '<br/>';
	er_buttons($title);
	echo '<br/>';
	po_buttons($title);
	echo '<br/>';

	 ?>
	
	</td></tr>
 </table>

<?php } ?>

<?php
// ------------------------------------------------------------------------------------------------------------------------
// 
function expenses_type2name( $type ) {
	switch ( $type ) {
		case "AccomodationBreakfast" : return "Accomodation with Breakfast"; break;
		case "Subsistence-5h" : return "Subsistence (5h rate)"; break;
		case "Subsistence-10h" : return "Subsistence (10h rate)"; break;
	}
	return $type;
} ?>

