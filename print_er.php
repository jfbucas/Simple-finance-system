<?php
require("common.inc.php");
require("mail_and_print.inc.php");

function select_printer($draft_number) {
   global $printer1, $printer2, $printer3, $printer4, $printer5; ?>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
   <form action="print_po.php" method="post" name="form1">
    <tr class="row_head"> 
      <td align="center" colspan="3" nowrap><b>Print Expense Report <?php echo $draft_number; ?></b></td>
    </tr>
    <tr class="box_bg">
      <td align="right">Select Printer:</td>
      <td> 
         <select name="printer">
             <option value="printer3"><?php echo $printer3["name"]; ?></option>
             <option value="printer4"><?php echo $printer4["name"]; ?></option>
         </select>
      </td>
      <td>
         <img src="images/print_xp.gif" alt="Print Expense Report" border="0"
            onClick="document.form1.submit();">
         <a href="print_po.php?action=cancel">
            <img src="images/cancel_xp.gif" alt="Cancel" border="0"></a>
      </td>
    </tr>
    <input type="hidden" name="action" value="print_gd_er">
    <input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
   </form>
   </table> <?php
} ?>

<?php
function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
{
    /* this way it works well only for orthogonal lines
        imagesetthickness($image, $thick);
	    return imageline($image, $x1, $y1, $x2, $y2, $color);
	        */
		    if ($thick == 1) {
		            return imageline($image, $x1, $y1, $x2, $y2, $color);
			        }
				    $t = $thick / 2 - 0.5;
				        if ($x1 == $x2 || $y1 == $y2) {
					        return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
						    }
						        $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
							    $a = $t / sqrt(1 + pow($k, 2));
							        $points = array(
								        round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
									        round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
										        round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
											        round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
												    );
												        imagefilledpolygon($image, $points, 4, $color);
													    return imagepolygon($image, $points, 4, $color);
													    } ?>


<?php
function print_gd_er($db, $draft_number, $printer) {
   global $cfg, $printer1, $printer2, $printer3, $printer4, $printer5;
   if (${$printer}["name"] == "NA" || ${$printer}["name"] == "") {
      echo "<table class=\"warn\" width=\"100%\"><tr><td>Select a valid configured printer from the list.</td></tr></table>";
      select_printer($draft_number);
      return FALSE;
   }
   if (!$er = $db->Execute("SELECT * FROM er WHERE draft_number=$draft_number")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
  if ($er->RecordCount() == 0) {
      echo "<table class=\"warn\" width=\"100%\"><tr><td>Expense Report No. $draft_number not found.</td></tr></table>";
      return FALSE;
   }

   $cr_username = $er->fields["created_by"];
   $appr_username = $er->fields["approved_by"];
   $section_id = $er->fields["section"];
   $cr_userinfo = $db->Execute("SELECT fullname, phone FROM users WHERE username='$cr_username'");
   $appr_userinfo = $db->Execute("SELECT fullname FROM users WHERE username='$appr_username'");
   $section_array = $db->Execute("SELECT * FROM section WHERE id=$section_id");
   $line_items = $db->Execute("SELECT * FROM er_items WHERE draft_number=$draft_number ORDER BY id");
   $total = get_er_total($draft_number);
   $advance = get_er_advance($draft_number);
   $prepaid = get_er_prepaid($draft_number);
   $balance = $total - $prepaid - $advance;
   $currsign=get_er_currency_sign($draft_number);
   $bbox_currency = imagettfbbox(16, 0,$cfg["font_currency"], $currsign );

   echo "<table class=\"info\" width=\"100%\"><tr><td>Generating document image. Please be patient...</td></tr></table>";
   flush();

   // Open the blank form image.
   $im = imagecreatefrompng("forms/er_template.png");
   $black = imagecolorallocate($im, 0, 0, 0);
   $white = imagecolorallocate($im, 255, 255, 255);
   $green = imagecolorallocate($im, 0, 40, 20);

   // Fill in the form by drawing text on the image at appropriate coordinates.
// ORIGINAL
//   imagettftext($im, 20, 0, 900, 200, $black, $cfg["font_b"],
//      $po_approved_number);

   imagettftext($im, 26, 0, 330, 150, $black, $cfg["font_b"],
      "Expense Report and Claim");

//Shipping Address
   imagettftext($im, 14, 0, 20, 150, $black, $cfg["font_b"],
      "Contact address :");
   imagettftext($im, 14, 0, 20, 171, $black, $cfg["font_r"],
      $cfg["finance_contact1"]);
   imagettftext($im, 14, 0, 20, 192, $black, $cfg["font_r"],
      $section_array->fields["name"]);
   imagettftext($im, 14, 0, 20, 213, $black, $cfg["font_r"],
      $section_array->fields["address1"] . "     " . $section_array->fields["address2"] );
   imagettftext($im, 14, 0, 20, 234, $black, $cfg["font_r"],
      $section_array->fields["city"] . " (" . $section_array->fields["p_code"] . "), " . $section_array->fields["country"] );
   imagettftext($im, 14, 0, 20, 257, $black, $cfg["font_r"],
      "Telephone: " . $cr_userinfo->fields["phone"]);
   imagettftext($im, 14, 0, 20, 281, $black, $cfg["font_r"],
      "Email: ".$cr_username);

//Invoices address
   imagettftext($im, 14, 0, 815, 150, $black, $cfg["font_b"],
      "Claimed to :");
   imagettftext($im, 14, 0, 815, 171, $black, $cfg["font_b"],
      "Finance Office");
   imagettftext($im, 14, 0, 815, 192, $black, $cfg["font_r"],
      $cfg["finance_contact1"]);
   imagettftext($im, 14, 0, 815, 213, $black, $cfg["font_r"],
      $cfg["finance_contact2"]);
   imagettftext($im, 14, 0, 815, 234, $black, $cfg["font_r"],
      $cfg["finance_contact3"]);
   imagettftext($im, 14, 0, 815, 257, $black, $cfg["font_r"],
      "Telephone ". $cfg["finance_contact4"]);
   imagettftext($im, 14, 0, 815, 281, $black, $cfg["font_r"],
      "Email: " . $cfg["finance_email"]);

//DATE
   imagettftext($im, 20, 0,  20, 337, $green, $cfg["font_b"],
      "Date ");
   imagettftext($im, 20, 0, 140, 337, $black, $cfg["font_r"],
      display_date($er->fields["date"]));

//ER number
   imagettftext($im, 14, 0, 810, 337, $green, $cfg["font_b"],
      "Req number :");
   imagettftext($im, 20, 0, 950, 337, $black, $cfg["font_b"],
      $er->fields["draft_number"]);

//Supplier Details
/*   imagettftext($im, 20, 0, 125, 337, $black, $cfg["font_r"],
      $vendor->fields["name"]);
   imagettftext($im, 20, 0, 125, 374, $black, $cfg["font_r"],
      $vendor->fields["address1"] . "  " . $vendor->fields["address2"]);
   imagettftext($im, 20, 0, 125, 411, $black, $cfg["font_r"],
      $vendor->fields["city"] . ", " . $vendor->fields["province"]);*/
//      $vendor->fields["city"] . ", " . $vendor->fields["province"] . ", " . $vendor->fields["p_code"]);
//   imagettftext($im, 20, 0, 125, 376, $black, $cfg["font_r"],
//      "Attn: " . $vendor->fields["attn"]);
//   imagettftext($im, 20, 0, 820, 265, $black, $cfg["font_r"],
//      $section_array->fields["name"]);
//   imagettftext($im, 20, 0, 820, 302, $black, $cfg["font_r"],
//      $section_array->fields["address1"]);
//   imagettftext($im, 20, 0, 820, 339, $black, $cfg["font_r"],
//      $section_array->fields["city"] . ", " . $section_array->fields["province"] . ", " . $section_array->fields["p_code"]);
//   imagettftext($im, 20, 0, 820, 376, $black, $cfg["font_r"],
//      "Attn: " . $section_array->fields["contact"]);

//Please supply...
/*   imagettftext($im, 14, 0, 10, 480, $black, $cfg["font_b"],
      "Please supply (subject to condition on foot of order)");
*/

//Table headers

   imagefilledrectangle($im, 0, 490, 1200, 519, $green);

   imagettftext($im, 12, 0, 5, 514, $white, $cfg["font_b"], "Receipt");
   imagettftext($im, 12, 0, 182-20, 514, $white, $cfg["font_b"], "Type");
   imagettftext($im, 12, 0, 305, 514, $white, $cfg["font_b"], "Description");
   imagettftext($im, 16, 0, 1180-70, 514, $white, $cfg["font_b"], "Price");

   $i = 1;  // Current Line
   $item_number = 1;
   $ppl = 36; // Pixels per line
   $baseline = 519; // Starting Y coordinate. Pixels - $ppl
   while (!$line_items->EOF) {
      $y = $baseline + $ppl * $i - 4;
/*      imagettftext($im, 16, 0, 15, $y, $black, $cfg["font_r"], $item_number);
      imagettftext($im, 16, 0, 65, $y, $black, $cfg["font_r"], $line_items->fields["qty"]);*/
      //imagettftext($im, 16, 0, 145, $y, $black, $cfg["font_r"],
      //   $line_items->fields["unit"]);
      //$bbox = imagettfbbox(12, 0, $cfg["font_r"], $line_items->fields["alloc"]);
      //imagettftext($im, 12, 0, 880 - $bbox[2], $y, $black, $cfg["font_r"],  $line_items->fields["alloc"]);
      $bbox = imagettfbbox(16, 0, $cfg["font_r"], $line_items->fields["receipt"]);
      imagettftext($im, 16, 0, 32 - ($bbox[2])/2, $y, $black, $cfg["font_r"], $line_items->fields["receipt"]);
      $bbox = imagettfbbox(16, 0, $cfg["font_r"], $line_items->fields["type"]);
      imagettftext($im, 16, 0, 182 - ($bbox[2])/2, $y, $black, $cfg["font_r"], $line_items->fields["type"]);
      $j = 0;
      $s = 0;
	$dc = $line_items->fields["description"];
	if ( $line_items->fields["comment"] != "" ) {
		$dc .= " ( ". $line_items->fields["comment"] ." ) ";
	}
      $l = strlen($dc);
      while ( substr($dc , $s, $l) != "" ) {
              $bbox = imagettfbbox(16, 0, $cfg["font_r"], substr($dc , $s, $l));
              while ( $bbox[2] > 760 ) {
	         $l--;
	         $bbox = imagettfbbox(16, 0, $cfg["font_r"], substr($dc , $s, $l));
	      }

	      imagettftext($im, 16, 0, 305, $y + $j * $ppl, $black, $cfg["font_r"], substr($dc, $s, $l));
	      $s += $l;
              $l = strlen($dc) - $s;
	      $j ++;
      }
	if ( $j > 1 ) $i+=$j-1;


      $bbox = imagettfbbox(16, 0, $cfg["font_r"], sprintf("%01.2f", $line_items->fields["price"] * $line_items->fields["quantity"] * $line_items->fields["exchangerate"]));
      imagettftext($im, 16, 0, 1190 - $bbox[2] - $bbox_currency[2], $y, $black, $cfg["font_r"], sprintf("%01.2f", $line_items->fields["price"] * $line_items->fields["quantity"] * $line_items->fields["exchangerate"]));
      imagettftext($im, 16, 0, 1190 - $bbox_currency[2], $y, $black, $cfg["font_currency"], $currsign );
      
      $i ++;
      imagelinethick($im, 0, $baseline + $ppl * ( $i -1 ) , 1200, $baseline + $ppl * ($i -1 ), $green, 2);
      $line_items->MoveNext();
      $item_number ++;
   }

   // Vertical lines
   imagelinethick($im,   0, 490,   0, $baseline + $ppl * ($i-1), $green, 2);
   imagelinethick($im,  65, 490,  65, $baseline + $ppl * ($i-1), $green, 2);
   imagelinethick($im, 300, 490, 300, $baseline + $ppl * ($i-1), $green, 2);


   if ( $total > 0) {
      $y = $baseline + $ppl * $i - 4;
      $bbox = imagettfbbox(16, 0, $cfg["font_b"], sprintf("%01.2f", $total ));
      imagettftext($im, 24, 0,  940, $y, $green, $cfg["font_r"], "Total");
      imagettftext($im, 16, 0, 1190 - $bbox[2] - $bbox_currency[2], $y, $black, $cfg["font_r"], sprintf("%01.2f", $total));
      imagettftext($im, 16, 0, 1190 - $bbox_currency[2], $y, $black, $cfg["font_currency"], $currsign );
      $i++;
      imagelinethick($im, 1055, $baseline + $ppl * ($i -1 ) , 1200, $baseline + $ppl * ($i -1 ), $green, 2);
   }

   if ( $advance > 0) {
      $y = $baseline + $ppl * $i - 4;
      $bbox = imagettfbbox(16, 0, $cfg["font_b"], sprintf("%01.2f", $advance));
      imagettftext($im, 16, 0,  940, $y, $green, $cfg["font_r"], "Advance" );
      imagettftext($im, 16, 0, 1190 - $bbox[2] - $bbox_currency[2], $y, $black, $cfg["font_r"], sprintf("%01.2f", $advance));
      imagettftext($im, 16, 0, 1190 - $bbox_currency[2], $y, $black, $cfg["font_currency"], $currsign );
      $i++;
      imagelinethick($im, 1055, $baseline + $ppl * ($i -1 ) , 1200, $baseline + $ppl * ($i -1 ), $green, 2);
   }

   if ( $prepaid > 0) {
      $y = $baseline + $ppl * $i - 4;
      $bbox = imagettfbbox(16, 0, $cfg["font_b"], sprintf("%01.2f", $prepaid));
      imagettftext($im, 16, 0,  940, $y, $green, $cfg["font_r"], "Prepaid" );
      imagettftext($im, 16, 0, 1190 - $bbox[2] - $bbox_currency[2], $y, $black, $cfg["font_r"], sprintf("%01.2f", $prepaid));
      imagettftext($im, 16, 0, 1190 - $bbox_currency[2], $y, $black, $cfg["font_currency"], $currsign );
      $i++;
      imagelinethick($im, 1055, $baseline + $ppl * ($i -1 ) , 1200, $baseline + $ppl * ($i -1 ), $green, 2);
   }

   if ( isset($balance)) {
      $y = $baseline + $ppl * $i - 4;
      $bbox = imagettfbbox(16, 0, $cfg["font_b"], sprintf("%01.2f", $balance));
      imagettftext($im, 16, 0,  940, $y, $green, $cfg["font_b"], "Balance" );
      imagettftext($im, 16, 0, 1190 - $bbox[2] - $bbox_currency[2], $y, $black, $cfg["font_b"], sprintf("%01.2f", $balance));
      imagettftext($im, 16, 0, 1190 - $bbox_currency[2], $y, $black, $cfg["font_currency"], $currsign );
      $i++;
      imagelinethick($im, 1055, $baseline + $ppl * ($i -1 ) , 1200, $baseline + $ppl * ($i -1 ), $green, 2);
   }
   //imagelinethick($im, 905, 490, 905, $baseline + $ppl * ($i-1), $green, 2);
   imagelinethick($im, 1055, 490, 1055, $baseline + $ppl * ($i-1), $green, 2);
   imagelinethick($im, 1198, 490, 1198, $baseline + $ppl * ($i-1), $green, 2);

//REQUISITIONER
   imagettftext($im, 12, 0, 20, 1507, $green, $cfg["font_b"],  "Claimed by:");
   imagettftext($im, 20, 0, 30, 1535, $black, $cfg["font_r"],  $cr_userinfo->fields["fullname"]);
//Head of section
   imagettftext($im, 12, 0, 415, 1507, $green, $cfg["font_b"], "Head of Section:");
   imagettftext($im, 20, 0, 425, 1535, $black, $cfg["font_r"], $appr_userinfo->fields["fullname"]);
//Finance officer
   imagettftext($im, 12, 0, 810, 1507, $green, $cfg["font_b"], "Finance Officer:");
   imagettftext($im, 20, 0, 820, 1535, $black, $cfg["font_r"], $cfg["finance_officer"]);


//Final table headers
/*   imagettftext($im, 12, 0, 100, 1408, $green, $cfg["font_b"], "Invoice date");
   imagettftext($im, 12, 0, 450, 1408, $green, $cfg["font_b"], "Invoice No.");
   imagettftext($im, 12, 0, 730, 1408, $green, $cfg["font_b"], "Invoice Value");
   imagettftext($im, 12, 0, 930, 1408, $green, $cfg["font_b"], "Currency");
*/
//Final table title
   $ppll = 20;
   imagettftext($im, 10, 0, 90, 1570, $black, $cfg["font_r"],  "(For finance use only)");
   imagettftext($im, 12, 0, 90, 1600 + $ppll * 0, $black, $cfg["font_r"],  "Account");
   imagettftext($im, 12, 0, 90, 1600 + $ppll * 1, $black, $cfg["font_r"],  "Internal Invoice Reference");
   imagettftext($im, 12, 0, 90, 1600 + $ppll * 2, $black, $cfg["font_r"],  "Check By");
   imagettftext($im, 12, 0, 90, 1600 + $ppll * 3, $black, $cfg["font_r"],  "Account Code");
   imagettftext($im, 12, 0, 90, 1600 + $ppll * 4, $black, $cfg["font_r"],  "Euro Cost");
   imagelinethick($im, 80, 1602 + $ppll *-1, 600, 1602 + $ppll *-1, $green, 1);
   imagelinethick($im, 80, 1602 + $ppll * 0, 600, 1602 + $ppll * 0, $green, 1);
   imagelinethick($im, 80, 1602 + $ppll * 1, 600, 1602 + $ppll * 1, $green, 1);
   imagelinethick($im, 80, 1602 + $ppll * 2, 600, 1602 + $ppll * 2, $green, 1);
   imagelinethick($im, 80, 1602 + $ppll * 3, 600, 1602 + $ppll * 3, $green, 1);
   imagelinethick($im, 80, 1602 + $ppll * 4, 600, 1602 + $ppll * 4, $green, 1);

   imagelinethick($im,  80, 1602 + $ppll *-1,  80, 1602 + $ppll * 4, $green, 1);
   imagelinethick($im, 340, 1602 + $ppll *-1, 340, 1602 + $ppll * 4, $green, 1);
   imagelinethick($im, 600, 1602 + $ppll *-1, 600, 1602 + $ppll * 4, $green, 1);
//Final text
/*
   imagettftext($im, 12, 0, 15, 1620, $black, $cfg["font_b"],
      "This order is issued subject to you providing us with your Tax Clearance Certificate.");
   imagettftext($im, 12, 0, 15, 1640, $black, $cfg["font_b"],
      "NOTE: In case of contruction industry orders, tax will be deducted from the gross invoice value at the rate of 35% at time of payment,");
   imagettftext($im, 12, 0, 15, 1660, $black, $cfg["font_b"],
      "unless a C2 is produced and a payments card obtained from the relevant authority.");
   imagettftext($im, 12, 0, 15, 1680, $black, $cfg["font_b"],
      "Professional Service Withholding Tax will be applied where appropriate.");
   imagettftext($im, 12, 0, 15, 1700, $black, $cfg["font_b"],
      "VAT Registered No. IE 1016603E: to be used for intra EU transactions only, not valid in the Republic of Ireland.");

// Good received
   imagettftext($im, 12, 0, 1050, 1620, $black, $cfg["font_b"], "Good Received");
   imagerectangle($im, 1100, 1640, 1130, 1670, $green);
*/
// If there is a comment, Edmond from finance asked to have some kind of marker to be noticed
/*   if ( $po_comment->fields["comment"] != "" ) {
	imagettftext($im, 12, 0, 1040, 1700, $black, $cfg["font_b"], "Comment available");
   }
*/

   // Save the filled in form image...
   imagepng($im, "var/" . session_id() . ".png");
   // And print it.
   if (print_image($printer, "Expense_Report_" . $draft_number . ".pdf", "Expense_Report_" . $draft_number ))
      echo "<table class=\"info\" width=\"100%\"><tr><td>Expense Report and Claim $draft_number: PDF generated.</td></tr></table>";
} ?>

<?php
   $action = strtolower($action);
   switch ($action) {
      case "cancel":
         echo "<table class=\"warn\" width=\"100%\"><tr><td>Print Expense Report $draft_number job canceled.</td></tr></table>";
         break;
      case "print_gd_er":
         print_gd_er($db, $draft_number, $printer);
	echo "<script language='Javascript'>";
	echo "window.location='er.php?action=view_from_search&draft_number=" . $draft_number . "'";
	echo "</script>";
         break;
      default:
        /* if ($cfg["gd"] == TRUE) {
            select_printer($draft_number);
         } else { ?>
            <script language="JavaScript">
               window.open("print_html_po.php?draft_number=<?php echo $draft_number; ?>");
            </script> <?php
            echo "<table class=\"info\" width=\"100%\"><tr><td>Purchase Order $draft_number opened in a new browser window.</td></tr></table>";
         }*/
   }
require("footer.inc.php");
?>
