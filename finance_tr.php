<?php require("common.inc.php"); 

function paint_table(&$summary) { 
   global $action, $status, $section_id, $vendor_id,
          $from_date, $to_date, $order_by, $order, $db; 
   
	  ?>
   <table class="default" border="0" align="center" cellpadding="10" width="80%"><tr><td>
  	 <h2 align="center"> TR List - Finance Department </h2> 
   </td></tr></table>
   <table class="small" border="0" cellpadding="1" cellspacing="0" >
      <tr class="row_head">
      	<?php
		get_paint_tr_table_header( $db, "finance_tr.php" );
	?>
      </tr> <?php
      $i = 1;
      while (!$summary->EOF) {

         if ($i % 2 == 0) {
            echo "<tr class=\"row_even\"> ";
         } else {
            echo "<tr class=\"row_odd\"> ";
         } 
	 //$td_onclick="onclick=\"location.href='finance_tr.php?action=edit_tr_info&draft_number=" . $summary->fields["draft_number"] . "'\" ";
	 $td_onclick="onclick=\"location.href='tr.php?action=view_from_search&draft_number=" . $summary->fields["draft_number"] . "'\" ";

         if (!$user_details = $db->Execute("SELECT * FROM users WHERE username='".$summary->fields['created_by']."'")) {
	         echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		 break;
	}

	  echo "<td $td_onclick align=\"center\">";
          echo $summary->fields["draft_number"]; 
	       ?>
         </td>
         <td <? echo $td_onclick; ?>><?php echo $user_details->fields["fullname"]; ?></td>
         <td <? echo $td_onclick; ?> align="center"><?php echo display_date($summary->fields["date"]); ?></td>
         <td <? echo $td_onclick; ?>><?php echo $summary->fields["name"]; ?></td>
         <td <? echo $td_onclick; ?>><?php echo $summary->fields["destination"] . " ( " . display_date($summary->fields["depart_date"])  . " - " . display_date($summary->fields["return_date"]) . " )"; ?> </td>
         <td <? echo $td_onclick; ?> align="center"><?php
		switch ($summary->fields["status"]) {
			case "Open"		: echo "<div class=tr_open>Open</div>";  break;
			case "Requested"	: echo "<div class=tr_requested>Requested</div>"; break;
			case "Approved"		: echo "<div class=tr_approved>Approved</div>"; break;
			case "Closed"		: echo "<div class=tr_closed>Closed</div>"; break;
			case "Canceled"		: echo "<div class=tr_canceled>Canceled</div>"; break;
			default			: echo "<div>Unknown</div>"; break;
		}
         ?></td><?php
	/*
               		<td align="center"></td> <?php break;
	       		<td align="center"><?php echo $summary->fields['approved_number']?></td> <?php break;
	*/?>

	 <td <? echo $td_onclick; ?> align="right"><?php echo get_tr_total($summary->fields["draft_number"]) . " " . get_tr_currency_sign($summary->fields["draft_number"]) ."&nbsp;"; ?></td>
	 <td <? echo $td_onclick; ?> align="right"><?php echo sprintf("%01.2f",$summary->fields["advance_requested"]) . " " . get_tr_currency_sign($summary->fields["draft_number"]) ."&nbsp;"; ?></td>
	 <td <? echo $td_onclick; ?> align="right"><?php echo sprintf("%01.2f",$summary->fields["advance_transfered"]) . " " . get_tr_currency_sign($summary->fields["draft_number"]) ."&nbsp;"; ?></td>
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
               <a href="finance_tr.php?action=paged_query&page=<?php echo $summary->AbsolutePage() - 1; echo "&" . $paged_query_url; ?>">
                  <img src="images/previous.png" border="0" alt="Previous"></a> <?php
            }
            echo "&nbsp;";
            if (!$summary->AtLastPage() && $summary->AbsolutePage() != -1) { ?>
               <a href="finance_tr.php?action=paged_query&page=<?php echo $summary->AbsolutePage() + 1; echo "&" . $paged_query_url; ?>">
                  <img src="images/next.png" border="0" alt="Next"></a> <?php
            } ?>
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
            <!--button type="button" class="button_print" onClick="window.location='finance_tr.php?action=print_result'">Print</button>
            <button type="button" class="button_export" onClick="window.location='finance_tr.php?action=csv_result'">Export</button-->
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
/*      case "edit_tr_info":
         tr_info_form($db, $draft_number);
         break;
   case "csv_result": 
   	$_SESSION["context"] = "finance_tr.php";
   	?>
	<script language="JavaScript">
         window.open("csv_search_tr.php");
      </script> <?php
      finance_log( $db, 'tr', $draft_number, 'info', "Search Results opened in a new browser window." );
      break;*/
   case "paged_query":
      $summary = paged_query($db, $page);
      paint_table($summary, $db);
      break;
/*   case "print_result": ?>
      <script language="JavaScript">
         window.open("print_html_search_tr.php");
      </script> <?php
      finance_log( $db, 'tr', $draft_number, 'info', "Search Results opened in a new browser window." );
      break;*/
  case "search_none":
      finance_log( $db, 'tr', $draft_number, 'warn', "You must select a Search Type from the drop menu." );
      search_form($db);
      break;
/*   case "search_single":
      if (empty($draft_number)) {
         finance_log( $db, 'tr', $draft_number, 'warn', "You must enter a valid purchase order number." );
         search_form($db);
         break;
      }
      if (!$tr = $db->Execute("SELECT * FROM tr WHERE draft_number=$draft_number")) {
         echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
         break;
      }
      if ($tr->RecordCount() == 0) {
         finance_log( $db, 'tr', $draft_number, 'warn', "Requisition No. $draft_number not found." );
         search_form($db);
         break;
      } ?>
      <script language="JavaScript">
         window.location="ed_tr.php?action=view_from_search&draft_number=<?php echo $draft_number; ?>";
      </script> <?php
      break;*/
   case "search_all":
      show_tr($db, "finance_tr.php");
      break;
/*   case "search_section":
      show_tr($db, "finance_tr.php");
      break;
   case "search_vendor":
      show_tr($db, "finance_tr.php");
      break;*/
/*   case "insert_po_approved_number":
      edit_tr($db);
      break;
   case "update_tr_info":
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
            finance_log( $db, 'tr', $draft_number, 'info', "PO update OK" );
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
		finance_log( $db, 'tr', $draft_number, 'info', "An email has been sent to $cr_user and $ap_user to notify the approved_number $po_approved_number." );
	 }

	 //po_info_form($db, $draft_number);
	 $action="search_all";
	 $order_by="draft_number";
	 show_po($db, "finance_po.php");
         break;*/
/*   case "update_po_tiny_info":
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
         break;*/
      default:
	//show_tr($db);
   }
} else {
   finance_log( $db, 'tr', $draft_number, 'warn', "Insufficient privilege." );
}
?>
<img class="money" src="images/emblem-money.png">
<?
// Switch end
require("footer.inc.php");
?>
