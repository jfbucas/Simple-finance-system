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
	<title>Print HTML Purchase Order</title>
   <link rel="STYLESHEET" type="text/css" href="style.css">
</head>
<body> <?php
if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
   echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
   die();
}
if ($po->RecordCount() == 0) {
   echo "<table class=\"warn\" width=\"100%\"><tr><td>PO number $draft_number not found.</td></tr></table>";
   die();
}
$vendor_id = $po->fields["vendor"];
$org_id = $po->fields["organization"];
$cr_username = $po->fields["created_by"];
$appr_username = $po->fields["approved_by"];
$cr_fullname = $db->Execute("SELECT fullname FROM users WHERE username='$cr_username'");
$appr_fullname = $db->Execute("SELECT fullname FROM users WHERE username='$appr_username'");
$vendor = $db->Execute("SELECT * FROM vendor WHERE id=$vendor_id");
$org = $db->Execute("SELECT * FROM organization WHERE id=$org_id");
$line_items = $db->Execute("SELECT * FROM line_items WHERE draft_number=$draft_number ORDER BY id");
require("form_header.inc.htm"); ?>
<hr>
<table class="default" width="100%" border="0" cellspacing="0" cellpadding="1">
   <tr> 
      <td><b>Date of Order: </b><?php echo display_date($po->fields["date"]); ?></td>
      <td align="right"><b>Purchase Order No. </b><?php echo $draft_number; ?></td>
   </tr>
</table>
<hr>
<table class="default" width="100%" border="0" cellspacing="0" cellpadding="1"
   <tr> 
      <td colspan="2"><b>Supplier:</b></td>
      <td colspan="2"><b>Sold To:</b></td>
   </tr>
   <tr> 
      <td>&nbsp;</td>
      <td nowrap>
         <?php echo $vendor->fields["name"]; ?><br>
         <?php echo $vendor->fields["address1"]; ?><br>
         <?php echo $vendor->fields["city"] . ", " . $vendor->fields["province"] . ", " . $vendor->fields["p_code"]; ?><br>
         <?php echo "Attn: " . $vendor->fields["attn"]; ?>
         </td>
      <td>&nbsp;</td>
      <td nowrap>
         <?php echo $org->fields["name"]; ?><br>
         <?php echo $org->fields["address1"]; ?><br>
         <?php echo $org->fields["city"] . ", " . $org->fields["province"] . ", " . $org->fields["p_code"]; ?><br>
         <?php echo "Attn: " . $org->fields["contact"]; ?>
      </td>
   </tr>
</table>
<table class="default" width="100%" border="0"><tr><td>&nbsp;</td></tr></table>
<table class="small" width="100%" border="1" cellspacing="1" cellpadding="1">
   <tr> 
      <td colspan="6" align="center">PLEASE FURNISH THE MATERIALS SPECIFIED BELOW</td>
   </tr>
   <tr> 
      <td align="center"><b>Item</b></td>
      <td align="center"><b>Qty</b></td>
      <td align="center"><b>Unit</b></td>
      <td><b>Description</b></td>
      <td align="right"><b>Unit Price</b></td>
      <td align="right"><b>Amount</b></td>
   </tr> <?php
   $i = 1;
   $po_total = 0;
   while (!$line_items->EOF) {
      echo "<tr>";
      echo "<td align=\"center\">$i</td>";
      echo "<td align=\"center\">" . $line_items->fields["qty"] . "</td>";
      echo "<td align=\"center\">" . $line_items->fields["unit"] . "</td>";
      echo "<td>" . $line_items->fields["descrip"] . "</td>";
      if ($line_items->fields["unit_price"] != 0) {
         echo "<td align=\"right\">" . $line_items->fields["unit_price"] . "</td>";
         echo "<td align=\"right\">" . $line_items->fields["amount"] . "</td>";
      } else {
         echo "<td>&nbsp;</td>";
         echo "<td>&nbsp;</td>";
      }
      echo "</tr>";
      $po_total += $line_items->fields["amount"];
      $i++;
      $line_items->MoveNext();
   } ?>
   <tr> 
      <td colspan="5" align="right"><b>Totals:</b></td>
      <td align="right"> <?php
         if ($po_total != 0) {
            printf("<b>%s%01.2f</b>", $cfg["curr"], $po_total);
         } else {
            echo "&nbsp;";
         } ?>
      </td>
   </tr>
</table>
<table class="default" width="100%" border="0"><tr><td>&nbsp;</td></tr></table>
<table class="default" width="100%" border="1" cellspacing="1" cellpadding="1">
   <tr> 
      <td width="33%">Requisitioner:</td>
      <td width="33%">Dept. Manager:</td>
      <td width="33%">Manager of Finance:</td>
   </tr>
   <tr>
      <td width="33%"><?php echo $cr_fullname->fields["fullname"]; ?></td>
      <td width="33%"><?php echo $appr_fullname->fields["fullname"]; ?></td>
      <td width="33%">&nbsp;</td>
   </tr>
</table>
<?php $notHelp="yes" ; require("footer.inc.php"); ?>
