<?php require("common.inc.php"); 

function po_info_form($db, $draft_number) {
	global $cfg;
   if ($draft_number == "") {
      finance_log( $db, 'po', $draft_number, 'warn', "You must enter a valid PO number." );
      //po_form();
      return FALSE;
   }
   if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
   // Added to generate a new approved number - 6digits, starting with a 0
   if (!$po_numbers = $db->Execute("SELECT * FROM po WHERE po_approved_number LIKE '______' AND po_approved_number LIKE '0%'")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
   if ($po->RecordCount() == 0) {
      finance_log( $db, 'po', $draft_number, 'warn', "PO number $draft_number not found." );
      //po_form();
      return FALSE;
   }
	$new_po_number = $po_numbers->RecordCount();
	do {
		$new_po_number += 1;
		$new_po_number_str = "$new_po_number";
		while ( strlen( $new_po_number_str ) < 6 ) 
			$new_po_number_str = "0" . $new_po_number_str;
		
   		$po_numbers = $db->Execute("SELECT * FROM po WHERE po_approved_number LIKE '$new_po_number_str'");

	} while ( $po_numbers->RecordCount() );

//   $super_users = $db->Execute("SELECT fullname, username FROM users WHERE priv>2 ORDER BY fullname");
   $all_users = $db->Execute("SELECT fullname, username FROM users ORDER BY fullname");
   $all_users2 = $db->Execute("SELECT fullname, username FROM users ORDER BY fullname");
   $vendors = $db->Execute("SELECT DISTINCT name, id FROM vendor WHERE enabled > 0 ORDER BY name");
   $sections = $db->Execute("SELECT name, id FROM section WHERE enabled='Y' ORDER BY name"); ?>
   <table class="small" align="center" border="0" cellspacing="0" cellpadding="1">
      <form action="finance_po.php" method="post" name="fin_po_info_form">
         <tr class="row_head">
            <td colspan="2" nowrap><b>Edit Purchase Order Information</b></td>
         </tr>
         <tr class="box_bg">        <td colspan=2 align="center"> &nbsp;   </td>      </tr>
         <tr class="box_bg">
            <td colspan=2 align="center">
               PO Number: <?php echo $draft_number; ?>
            </td>
         </tr>
         <tr class="box_bg">        <td colspan=2 align="center">
	<table align=center><!-- First Table begin -->
         <tr class="box_bg">
            <td width="45%" align="right">
               Section:
            </td>
            <td>
               <?php echo $sections->GetMenu2("section", $po->fields["section"], FALSE, FALSE, 0,
                                 "") ?>
            </td>
         </tr>
         <tr class="box_bg">
            <td align="right">
               Supplier:
            </td>
            <td>
               <?php echo $vendors->GetMenu2("vendor", $po->fields["vendor"], FALSE, FALSE, 0,
                                    "") ?>
            </td>
          </tr>
         <tr class="box_bg">
            <td align="right">
               Status:
            </td>
            <td><?php
                if ($po->fields["open"] == "Y" && $po->fields["approved"] == "N") $selected_open = "selected";
                if ($po->fields["open"] == "Y" && $po->fields["approved"] == "Y") $selected_approved = "selected";
                if ($po->fields["open"] == "N" ) $selected_close = "selected";
                if ($po->fields["open"] == "C" ) $selected_cancel = "selected";
		$status_filter = "<select name=\"status\">";
                $status_filter .= "<option value=\"1\" $selected_open class=\"po_open\">Open</option>";
                $status_filter .= "<option value=\"2\" $selected_approved class=\"po_approved\">Approved</option>";
              	$status_filter .= "<option value=\"3\" $selected_close class=\"po_closed\">Closed</option>";
              	$status_filter .= "<option value=\"4\" $selected_cancel class=\"po_canceled\">Canceled</option>";
                $status_filter .= '</select>';
		echo "$status_filter";
		
               /*<select name="status" > <?php
	                  <option value="1" selected>Open, Not Approved</option>
	                  <option value="2">Open, Approved</option>
	                  <option value="3">Closed, Approved</option> <?php
                  } else if ($po->fields["open"] == "Y" && $po->fields["approved"] == "Y") { ?>
	                  <option value="1">Open, Not Approved</option>
	                  <option value="2" selected>Open, Approved</option>
	                  <option value="3">Closed, Approved</option> <?php
                  } else { ?>
	                  <option value="1">Open, Not Approved</option>
	                  <option value="2">Open, Approved</option>
	                  <option value="3" selected>Closed, Approved</option> <?php
                  } ?>
               </select>*/
	       ?>
            </td>
         </tr>
         <tr class="box_bg">
            <td align="right">
               Requested By:
            </td>
            <td>
               <?php 	echo $all_users->GetMenu2("cr_user", $po->fields["created_by"], FALSE, FALSE, 0,
                                      "") ?>
            </td>
         </tr> <?php
         if ($po->fields["approved"] == "Y") { ?>
         <tr class="box_bg">
            <td align="right">
               Approved By:
            </td>
	    <td>
	    <?php
                	echo $all_users2->GetMenu2("ap_user", $po->fields["approved_by"], FALSE, FALSE, 0,
                                      "")    ?>

		 <?php 
/* Not all users should be displayed here. Only possible approvers: head_of_section; finance_officer & registrar
//  $registrar_user = $db->Execute("SELECT username FROM 'users-roles' WHERE role=$cfg["registrar"]");
		 
		  <select name="ap_user" > 
	                  <option value="1" selected><?php $po->fields["approved_by"]; ?></option>
	                  <option value="2">Open, Approved</option>
	                  <option value="3">Closed, Approved</option> 
               </select>
*/  
	?>
            <td align="right">
         </tr>
        <tr class="box_bg">
            <td nowrap align="right">Approved PO number:</td>
            <td>
	    <? 	// Added by JFBucas - generates an approved number
	    	if ( $po->fields["po_approved_number"] == "" ) {
			$po->fields["po_approved_number"] = $new_po_number_str;
		}

		?>
               <input type="text" name="po_approved_number" size="30"
                  value="<?php echo $po->fields["po_approved_number"]; ?>">
	    </td>
	</tr>
	<tr>    <td class="box_bg" colspan=2> </td></tr>

<?php
         }// END po_info_form()
 ?>
         <tr class="box_bg">        <td colspan=2 align="center"> &nbsp;   </td>      </tr>
	<tr>
	    <td class="box_bg" colspan=2 align=center>
              <button type="button" class="button_update" onClick='document.fin_po_info_form.submit();' align="right" >Update</button>
              <button type="button" class="button_cancel" onClick='history.go(-1);' align="right" >Cancel</button>
            </td>
         </tr>
	<tr>    <td class="box_bg" colspan=2> &nbsp;</td></tr>
	</table> <!-- First Table end -->

	  </td>      </tr>
         <input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
         <input type="hidden" name="action" value="update_po_info">
      </form>
         <tr class="box_bg">        <td colspan=2 align="center">

		<table width=80% align=center> <!-- Second table begin -->
          <tr class="box_bg">
            <td colspan=2 align="center"><b>Send a reminder to the requestor</b></td>
         </tr>
      <form action="finance_po.php" method="post" name="reminder">
         <tr class="box_bg" width=80%>
	   <td colspan=2 align="center">Message to be sent to <?php echo $po->fields['created_by'];?> </br>
		<textarea class="PO_reminder" rows="10"  name="reminder_text" wrap="virtual"><?
		echo 'The Purchase order #' . $po->fields["draft_number"] . " (see below), is still showing as open on the online order system." . "\n";
		echo 'Can you please update and inform the Finance Department if the goods/services have been received and are satisfactory.' . "\n";
		echo 'Can you please let us know when this has been done, or if there is a problem please contact us.' . "\n";
		echo "\n";
		echo 'Thank you,';
		echo "\n";
		echo $cfg["signature-reminder"];
		echo "\n";
		echo "\n";
		echo "\n";
		echo po_description($po->fields["draft_number"]);
		?>
		</textarea>
 	    </td>
	 </tr>
         <tr class="box_bg">        <td colspan=2 align="center"> &nbsp;   </td>      </tr>
       	<tr>
	    <td class="box_bg" colspan=2 align=center>
              <input type="button" class="button_send" value="Send" onClick='document.reminder.submit();' align="right" >
            </td>
         </tr>
         <tr class="box_bg">        <td colspan=2 align="center"> &nbsp;   </td>      </tr>
	 <input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
         <input type="hidden" name="action" value="send_reminder">
      </form>
	</table> <!-- Second table end -->
	  </td>      </tr>

         <tr class="box_bg">        <td colspan=2 align="center">

		<table width=80% align=center> <!-- Third table begin -->
          <tr class="box_bg">
            <td colspan=2 align="center"><b>Print PO batch</b></td>
         </tr>
      <form action="print_po.php" method="post" name="po_batch">
         <tr class="box_bg" width=80%>
	   <td colspan=2 align="center">
	               From <input type="text" name="po_batch_begin" size="10" value="<?php echo $po->fields["po_approved_number"]; ?>"> to 
	               <input type="text" name="po_batch_end" size="10" value="<?php echo $new_po_number_str; ?>">
	    </td>
	 </tr>
         <tr class="box_bg">        <td colspan=2 align="center"> &nbsp;   </td>      </tr>
       	<tr>
	    <td class="box_bg" colspan=2 align=center>
              <input type="button" class="button_print" value="Print" onClick='document.po_batch.submit();' align="right" >
            </td>
         </tr>
         <tr class="box_bg">        <td colspan=2 align="center"> &nbsp;   </td>      </tr>
	 <input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
         <input type="hidden" name="printer" value="printer5">
         <input type="hidden" name="action" value="print_gd_po_group">
      </form>
	</table> <!-- Third table end -->
	  </td>      </tr>




           <!--tr class="box_bg">
            <td colspan=2 align="center"><b>Additional Information</b></td>
         </tr>
      <form action="finance_po.php" method="post" name="fin_po_tiny_info_form">
         <tr class="box_bg">
	   <td align="right">Invoice received: </td>
	   <td>         
	      <select name="sent_info" onChange='document.fin_po_tiny_info_form.submit();'> <?php
                  if ($po->fields["sent_to_supplier"] == "Y") { ?>
	                  <option value="1" selected>Yes</option>
	                  <option value="2">No</option> <?php
                  } else { ?>
	                  <option value="1">Yes</option>
	                  <option value="2" selected>No</option> <?php
                  } ?>
               </select>
 	    </td>
	 </tr>
         <tr class="box_bg">
	   <td align="right">Paid to Supplier: </td>
	   <td>
              <select name="paid_info" onChange='document.fin_po_tiny_info_form.submit();'> <?php
                  if ($po->fields["paid"] == "Y") { ?>
	                  <option value="1" selected>Paid Already</option>
	                  <option value="2">Not Paid Yet</option> <?php
                  } else { ?>
	                  <option value="1">Paid Already</option>
	                  <option value="2" selected>Not Paid Yet</option> <?php
                  } ?>
              </select>
 	    </td>
	 </tr>
         <tr class="box_bg">        <td colspan=2 align="center"> &nbsp;   </td>      </tr>
         <input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
         <input type="hidden" name="action" value="update_po_tiny_info">
      </form-->
   </table>

 <?php
} ?>

<?php
function paint_table(&$summary) { 
   global $action, $status, $section_id, $vendor_id,
          $from_date, $to_date, $order_by, $order, $db; 
   
	  ?>
   <table class="default" border="0" align="center" cellpadding="10" width="80%"><tr><td>
  	 <h2 align="center"> PO List - Finance Department </h2> 
   </td></tr></table>
   <table class="small" border="0" cellpadding="1" cellspacing="0" >
      <tr class="row_head">
      	<?php
		get_paint_table_header( $db, "finance_po.php" );
	?>
      </tr> <?php
      $i = 1;
      while (!$summary->EOF) {
             if (!$po_comment = $db->Execute("SELECT comment FROM po_comments WHERE draft_number='". $summary->fields["draft_number"] . "'" )) { 
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
	       break;
	     }

         if ($i % 2 == 0) {
            echo "<tr class=\"row_even\"> ";
         } else {
            echo "<tr class=\"row_odd\"> ";
         } 
	 $td_onclick="onclick=\"location.href='po.php?action=view_from_search&draft_number=" . $summary->fields["draft_number"] . "'\" ";

         if (!$user_details = $db->Execute("SELECT * FROM users WHERE username='".$summary->fields['created_by']."'")) {
	         echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		 break;
	}

	 echo "<td align=center><a href='finance_po.php?action=edit_po_info&draft_number=" . $summary->fields["draft_number"] . "'><img src=\"images/tools.png\" border=0></a></td>";
	     echo "<td $td_onclick align=\"center\">";
       	     if ( $po_comment->fields[ "comment" ] != "" ) {
	     	echo "<span>". ereg_replace("\n", "<br>", $po_comment->fields[ "comment" ]) . "</span>";
	       	echo "**<b>"; 
	     }

               echo $summary->fields["draft_number"]; 

	       if ( $po_comment->fields[ "comment" ] != "" ) echo "</b>**";
	       ?>
         </td>
         <td <? echo $td_onclick; ?> ><?php echo $user_details->fields["fullname"]; ?></td>
         <td <? echo $td_onclick; ?> align="center"> <?php echo display_date($summary->fields["date"]); ?></td>
         <td <? echo $td_onclick; ?> > <?php echo $summary->fields[4]; ?></td>
         <td <? echo $td_onclick; ?> > <?php echo $summary->fields[5]; ?></td>
         <td <? echo $td_onclick; ?> align="center"> <?php 
            if ($summary->fields["open"] == "Y" && $summary->fields["approved"] == "N") { ?>
	    	<div class=po_open>Open</div>
	       <td <? echo $td_onclick; ?> align="center">N/A</td> <?php
            } else if ($summary->fields["open"] == "Y" && $summary->fields["approved"] == "Y") { ?>
	    	<div class=po_approved>Approved</div>
	       <td <? echo $td_onclick; ?> align="center"><?php echo $summary->fields[6]?></td> <?php
            } else if ($summary->fields["open"] == "N" ) { ?>
	    	<div class=po_closed>Closed</div>
	       <td <? echo $td_onclick; ?> align="center"><?php echo $summary->fields[6]?></td> <?php
            } else { ?>
	    	<div class=po_canceled>Canceled</div>
	       <td <? echo $td_onclick; ?> align="center"><?php echo $summary->fields[6]?></td> <?php
            } ?>
         </td><?php
            if ($summary->fields["sent_to_supplier"] == "Y") { 
              echo "<td title=\"Click on the Y to mark the invoice as not received\" align=\"center\" onclick='"
                   ."location.href=\"finance_po.php?action=unsent_to_supplier&draft_number=". $summary->fields["draft_number"] . "\"'>"
                   . "<img src=\"images/yes.png\" border=\"0\" alt=\"Received\"></a></td>";
           } else {
                   //."<a href=\"po.php?action=confirm_recv_line&draft_number=$draft_number&id="
              echo "<td title=\"Click on the N to mark the invoice as received\" align=\"center\" class=\"\" onclick='"
	           ."location.href=\"finance_po.php?action=sent_to_supplier&draft_number=" . $summary->fields["draft_number"] . "\"'>"
                   . "<img src=\"images/no.png\" border=\"0\" alt=\"Not Received\"></a></td>";
           } 
	   /* <td <? echo $td_onclick; ?> align="center"> <?php
              if ($summary->fields["sent_to_supplier"] == "Y") { ?>
               <img src="images/yes.png" border="0" alt="Invoice received"> <?php
            } else { ?>
               <img src="images/no.png" border="0" alt="Invoice not received"><?php
            } ?></td>*/
	 ?><!--td <? echo $td_onclick; ?> align="center"> <?php 
            if ($summary->fields["paid"] == "Y") { ?>
               <img src="images/yes.png" border="0" alt="Already paid to supplier"> <?php
            } else { ?>
               <img src="images/no.png" border="0" alt="Not paid to supplier yet"><?php
            } ?></td-->
         <td <? echo $td_onclick; ?> align="right"><?php echo get_po_total($summary->fields["draft_number"]) . " " . get_po_currency_sign($summary->fields["draft_number"]) ."&nbsp;"; ?></td>
	 <!--td align="center"><a href="po.php?action=view_from_search&draft_number=<?php echo $summary->fields["draft_number"]; ?>" target="_blank"><img src="images/oeil.png" border="0"></a></td-->
      </tr> <?php
         $i++;
         $summary->MoveNext();
      } ?>
   </table>
   <table class="small" border="0" cellpadding="1" cellspacing="0" width="100%">
      <tr class="row_head">
         <td align="center"> <?php
	    $paged_query_url = ereg_replace('action=[^&]*[&]*', '', $_SERVER['QUERY_STRING'] );
	    $paged_query_url = ereg_replace('page=[^&]*[&]*', '', $paged_query_url );
            if (!$summary->AtFirstPage() && $summary->AbsolutePage() != -1) { ?>
               <a href="finance_po.php?action=paged_query&page=<?php echo $summary->AbsolutePage() - 1; echo "&" . $paged_query_url; ?>">
                  <img src="images/previous.png" border="0" alt="Previous"></a> <?php
            }
            echo "&nbsp;";
            if (!$summary->AtLastPage() && $summary->AbsolutePage() != -1) { ?>
               <a href="finance_po.php?action=paged_query&page=<?php echo $summary->AbsolutePage() + 1; echo "&" . $paged_query_url; ?>">
                  <img src="images/next.png" border="0" alt="Next"></a> <?php
            } /*?>
            if (!$summary->AtFirstPage() && $summary->AbsolutePage() != -1) { ?>
               <a href="finance_po.php?action=paged_query&page=<?php echo $summary->AbsolutePage() - 1; ?>">
                  <img src="images/previous.png" border="0" alt="Previous"></a> <?php
            }
            echo "&nbsp;";
            if (!$summary->AtLastPage() && $summary->AbsolutePage() != -1) { ?>
               <a href="finance_po.php?action=paged_query&page=<?php echo $summary->AbsolutePage() + 1; ?>">
                  <img src="images/next.png" border="0" alt="Next"></a> <?php
            } */?>
         </td>
      </tr>
   </table>
   <table class="small" border="0" cellpadding="1" cellspacing="0" width="100%">
      <tr class="row_head"> <?php
         if ($summary->AbsolutePage() == -1) {
            echo "<td>&nbsp;</td>";
         } else {
            echo "<td>Page: " . $summary->AbsolutePage() . "</td>";
         } ?>
         <td align="right">
            <button type="button" class="button_print" onClick="window.location='finance_po.php?action=print_result'">Print</button>
            <button type="button" class="button_export" onClick="window.location='finance_po.php?action=csv_result'">Export</button>
         </td>
      </tr>
   </table>
   <?php 
} ?>



<?php
// Switch begin
if (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) {
   finance_buttons(false);
   $action = strtolower($action);
   switch ($action) {
   case "edit_po_info":
         po_info_form($db, $draft_number);
         break;
   case "paged_query":
      $summary = paged_query($db, $page);
      paint_table($summary, $db);
      break;
   case "csv_result": 
   	$_SESSION["context"] = "finance_po.php";
   	?>
	<script language="JavaScript">
         window.open("csv_search_po.php");
      </script> <?php
      finance_log( $db, 'po', $draft_number, 'info', "Search Results opened in a new browser window." );
      break;
   case "print_result": ?>
      <script language="JavaScript">
         window.open("print_html_search_po.php");
      </script> <?php
      finance_log( $db, 'po', $draft_number, 'info', "Search Results opened in a new browser window." );
      break;
  case "po_batch": 
	
	break;
  case "search_none":
      finance_log( $db, 'po', $draft_number, 'warn', "You must select a Search Type from the drop menu." );
      search_form($db);
      break;
   case "search_single":
      if (empty($draft_number)) {
         finance_log( $db, 'po', $draft_number, 'warn', "You must enter a valid purchase order number." );
         search_form($db);
         break;
      }
      if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
         echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
         break;
      }
      if ($po->RecordCount() == 0) {
         finance_log( $db, 'po', $draft_number, 'warn', "Requisition No. $draft_number not found." );
         search_form($db);
         break;
      } ?>
      <script language="JavaScript">
         window.location="ed_po.php?action=view_from_search&draft_number=<?php echo $draft_number; ?>";
      </script> <?php
      break;
   case "search_all":
      show_po($db, "finance_po.php");
      break;
   case "search_section":
      show_po($db, "finance_po.php");
      break;
   case "search_vendor":
      show_po($db, "finance_po.php");
      break;
   case "insert_po_approved_number":
      edit_po($db);
      break;
   case "send_reminder":
      if (! $po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
	  echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
	  break;
       }
       	  $mail_headers = 'From: ' . $cfg["finance_email"] . "\r\n" .
       	     'Reply-To: ' . $cfg["finance_email"] . "\r\n" .
       	     'To: ' . $po->fields["created_by"] . "\r\n" .
       	     'CC: ' . $cfg["finance_email"] . "\r\n" .
       	     'X-Mailer: PHP/' . phpversion();
	  do_mail($po->fields["created_by"],
               "Requisition $draft_number : Reminder",
       	       $reminder_text,
	       $mail_headers);
		finance_log( $db, 'po', $draft_number, 'info', "A reminder has been sent to " . $po->fields["created_by"] . "" );

	 //po_info_form($db, $draft_number);
	 $action="search_all";
	 $order_by="draft_number";
	 show_po($db, "finance_po.php");
	
	break;
   case "update_po_info":
         if (!isset($ap_user)) {
            $ap_user = "";
         }
         switch ($status) {
            case 1:
               $open = "Y";
               $approved = "N";
               break;
            case 2:
               $open = "Y";
               $approved = "Y";
               break;
            case 3:
               $open = "N";
               $approved = "Y";
               break;
            case 4:
               $open = "C";
               $approved = "N";
               break;
         }

      	 $previous_po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number");

         $query = "UPDATE po SET"
                . " vendor='$vendor', section='$section',"
                . " open='$open', created_by='$cr_user',"
                . " po_approved_number='$po_approved_number',"
                . " approved='$approved', approved_by='$ap_user'"
                . " WHERE draft_number=$draft_number";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         } else {
            finance_log( $db, 'po', $draft_number, 'info', "PO update OK" );
	 }
   
	// Added by JFBucas -- Send an email when a Requisition has been assigned a new approved_number
	if ( $previous_po->fields["po_approved_number"] != $po_approved_number ) {
	  $mail_headers = 'From: ' . $cfg["finance_email"] . "\r\n" .
       	     'Reply-To: ' . $cfg["finance_email"] . "\r\n" .
       	     'To: ' . $cr_user . "\r\n" .
       	     'X-Mailer: PHP/' . phpversion();
       	  $email_body="Your requisition " .$draft_number . " has been assigned an approved number : ". $po_approved_number . ".\r\n\r\n" . po_description($draft_number) ;
       	  do_mail($ap_user,
               "Requisition $draft_number : Approved number " . $po_approved_number,
       	       $email_body,
	       $mail_headers);
		finance_log( $db, 'po', $draft_number, 'info', "An email has been sent to $cr_user and $ap_user to notify the approved_number $po_approved_number." );
	 }

	 //po_info_form($db, $draft_number);
	 $action="search_all";
	 $order_by="draft_number";
	 show_po($db, "finance_po.php");
         break;
   case "update_po_tiny_info":
         if (!isset($ap_user)) {
            $ap_user = "";
         }
         switch ($sent_info) {
            case 1:
               $sent = "Y";
               break;
            case 2:
               $sent = "N";
               break;
         }
         switch ($paid_info) {
            case 1:
               $paid = "Y";
               break;
            case 2:
               $paid = "N";
               break;
         }

         $query1 = "UPDATE po SET"
                . " sent_to_supplier='$sent', paid='$paid'"
                . " WHERE draft_number=$draft_number";
         if (!$db->Execute($query1)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         po_info_form($db, $draft_number);
         break;
  case "sent_to_supplier":
	      if (!$po = $db->Execute("SELECT sent_to_supplier FROM po WHERE draft_number=$draft_number")) {
		  echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		  break;
	       }
	 if (!$db->Execute("UPDATE po SET sent_to_supplier='Y' WHERE draft_number=$draft_number")) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
         }
	 $action="search_all";
	 $order_by="draft_number";
	 show_po($db, "finance_po.php");
         break;
  case "unsent_to_supplier":
	      if (!$po = $db->Execute("SELECT sent_to_supplier FROM po WHERE draft_number=$draft_number")) {
		  echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		  break;
	       }
	 if (!$db->Execute("UPDATE po SET sent_to_supplier='N' WHERE draft_number=$draft_number")) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
         }
	 $action="search_all";
	 $order_by="draft_number";
	 show_po($db, "finance_po.php");
         break;
      default:
//      show_po($db);
   }
} else {
   finance_log( $db, 'po', $draft_number, 'warn', "Insufficient privilege." );
}
?>
<img class="money" src="images/emblem-money.png">
<?
// Switch end
require("footer.inc.php");
?>
