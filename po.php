<?php require("common.inc.php"); ?>
<?php
function edit_line($db, $draft_number, $id, $approved) {
   global $username, $user_role, $cfg;

   if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
   $section_id = $po->fields["section"];
   $section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
   $line_item = $db->Execute("SELECT * FROM line_items WHERE id=$id"); 
   $cr_user_id = $po->fields["created_by"];
   if (($username != $cr_user_id) && ($username != $section->fields["superapprover"]) && ($username != $section->fields["headof"]) && ($username != $section->fields["delegate"]) && ($user_role != $cfg["admin"]) && ($user_role != $cfg["finofficer"]) && ($user_role != $cfg["finmember"])) {
      //if ($username != $cr_user_id){
      finance_log( $db, 'po', $draft_number, 'warn', "Insufficient privilege. You are " . $username .". Only " . $po->fields["created_by"]. " can edit the line of this Requisition " );
      //po_form($db);
      return FALSE;
     }

   ?>
  <table class="small" width="100%" border="0" cellspacing="0" cellpadding="1">
  <form action="po.php" method="post" name="form_edit_line_<?echo  $line_item->fields["id"];?>">
    <tr class="box_head"> 
      <td colspan="8"><b>Edit Item</b></td>
    </tr>
    <tr class="box_bg"> 
      <td align="right" valign="top">Description:</td>
      <td colspan="3">
        <input type="text" name="descrip" size="128"
           value="<?php echo $line_item->fields["descrip"]; ?>">
      </td>
    </tr>
    <tr class="box_bg"> 
      <!--td align="right">Shortname: </td>
      <td> 
        <input type="text" name="unit" size="10"
           value="<?php echo $line_item->fields["unit"]; ?>">
      </td-->
      <td align="right">Quantity:</td>
      <td> 
        <input type="text" name="qty" size="5"
         value="<?php echo $line_item->fields["qty"]; ?>">
      </td>
      <td align="right">Unit Price (euros):</td>
      <td> 
        <input type="text" name="unit_price" size="16"
           value="<?php echo $line_item->fields["unit_price"]; ?>">
      </td>
      <!--td align="right">Section Alloc:</td>
      <td> 
        <input type="text" name="alloc" size="16"
           value="<?php echo $line_item->fields["alloc"]; ?>">
      </td-->
    </tr>
    <tr class="box_bg"> 
      <td colspan="8">
	 <button type="button" class="button_update" onClick="if (valid_po_line_form(document.form_edit_line_<?echo  $line_item->fields["id"];?>)) { 
	    document.form_edit_line_<?echo  $line_item->fields["id"];?>.submit(); }">Update</button> 
         <button type="button" class="button_delete" 
            onClick="if (isConfirmed('Are you sure you want to DELETE this Travel Request Line Item ?')) { window.location='po.php?action=delete_line&id=<?php echo $id; ?>&draft_number=<?php echo $draft_number; ?>'; }">Delete</button>
         <button type="button" class="button_cancel" onClick="window.location='po.php?action=cancel&draft_number=<?php echo $draft_number; ?>'">Cancel</button>
      </td>
    </tr>
  <input type="hidden" name="action" value="update_line">
  <input type="hidden" name="id" value="<?php echo $id; ?>">
  <input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
  </form>
  </table>
  <script language="JavaScript">
     document.edit_line.unit.focus();
  </script> <?php
//edit_line_item END
} ?>

<?php
function edit_po_form($db, $draft_number) {
   global $username, $user_role, $cfg;
   if ($draft_number == "") {
      finance_log( $db, 'po', $draft_number, 'warn', "You must enter a valid Requisition No.." );
      go_to_po_form();
      return FALSE;
   }
   if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
   if ($po->RecordCount() == 0) {
      finance_log( $db, 'po', $draft_number, 'warn', "Requisition No. $draft_number not found." );
      //po_form($db);
      return FALSE;
   }

   $currsign=get_po_currency_sign($draft_number);

   $section_id = $po->fields["section"];
   $section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
   $cr_user_id = $po->fields["created_by"];
   $appr_user_id = $po->fields["approved_by"];

   if (($username == $cr_user_id) || ($username == $section->fields["superapprover"]) || ($username == $section->fields["headof"]) || ($username == $section->fields["delegate"])
	|| ($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) {


     if ($po->fields["open"] == "N") {
        view_po($db, $draft_number);
        return TRUE;
     }
     $vendor_id = $po->fields["vendor"];
     $section_id = $po->fields["section"];
     $vendor = $db->Execute("SELECT name FROM vendor WHERE id=$vendor_id");
     $section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
     $cr_user = $db->Execute("SELECT fullname FROM users WHERE username='$cr_user_id'");
     $appr_user = $db->Execute("SELECT fullname FROM users WHERE username='$appr_user_id'");
     $line_items = $db->Execute("SELECT * FROM line_items WHERE draft_number=$draft_number ORDER BY id");
     $po_status = get_po_status( $draft_number ); ?>

   <table class="small" align="center" border="0" cellspacing="0" cellpadding="1" width="100%">
       <tr class="row_head"> 
         <td nowrap><b>Edit Requisition </b></td>
         <td align="right">Requisition : <?php echo $draft_number; ?></td>
       </tr>
       <tr class="box_bg">
         <td> <table><tr class="box_bg"><td>Status:</td><td><?php
		echo "<div class=po_". strtolower($po_status) . ">". $po_status . "</div>";
		echo "</td><td>";

	    ?><button type="button" class="button_save" onClick="window.location='po.php?action=view_from_search&draft_number=<?php echo $draft_number; ?>'">Ok</button><?php
            if ($po->fields["open"] == "Y") { ?>
 	   		<button type="button" class="button_cancel_request" onClick="if (isConfirmed('Are you sure you want to CANCEL this requisition ?')) { window.location='po.php?action=cancel_po&draft_number=<?php echo $draft_number; ?>'; }">Cancel Purchase Order</button> <?php
            }
	 ?></td></tr></table>
         </td>
         <td align="right">Date:&nbsp;<?php echo display_date($po->fields["date"]); ?></td>
       </tr>
       <tr class="box_bg"> 
         <td>&nbsp;&nbsp;Section:&nbsp;<?php echo $section->fields["name"]; ?></td>
         <td align="right">Requested By:&nbsp;<?php echo $cr_user->fields["fullname"]; ?></td>
       </tr>
       <tr class="box_bg">
         <td>&nbsp;&nbsp;Supplier:&nbsp;<?php echo $vendor->fields["name"]; ?></td>
        <?php 
        if ($po->fields["approved"] == "Y"){
          ?>
            <td align="right">&nbsp;&nbsp;Approved by :&nbsp;<?php echo $appr_user->fields["fullname"]; ?></td>
         <?php } else { ?>
            <td align="right">&nbsp;</td>
         <?php   }  ?>
       </tr>    

        <?php
         if ( $po->fields["po_approved_number"] != ""){
           ?>
           <tr class="box_bg">
           <td>&nbsp;</td>
           <td align="right">&nbsp;&nbsp;PO Number:&nbsp;<?php echo $po->fields["po_approved_number"]; ?></td>
         </tr>
       <?php
         }
     ?>
       <tr class="box_bg">
         <td colspan="1"> 

	 <?php
	    if (  ( $po->fields["open"] == "Y") && ($po->fields["approved"] == "N")) { 
		    if ((($username != $section->fields["headof"]) && ($username != $section->fields["delegate"])) ||
	   	        (($username == $section->fields["headof"]) && ($cr_user_id == $section->fields["headof"])) ||
			(($username == $section->fields["delegate"]) && ($cr_user_id == $section->fields["delegate"]))) { ?>
			Get approval from :  <?php
			if (($section->fields["delegate"] != "" ) && ($section->fields["delegate"] != $cfg["sysadmin_email"] )) { ?>
		    		<button type="button" class="button_approval" onClick="window.location='po.php?action=get_po_approval&draft_number=<?php echo $draft_number; ?>'">All</button>
				( <button type="button" class="button_approval" onClick="window.location='po.php?action=get_po_approval&draft_number=<?php echo $draft_number; ?>&head_of_section_only=1'"><?php echo $section->fields["headof"];?></button> |
				<button type="button" class="button_approval" onClick="window.location='po.php?action=get_po_approval&draft_number=<?php echo $draft_number; ?>&delegate_only=1'"><?php echo $section->fields["delegate"]; ?></button> )
			  <?php } else { ?>
		    		<button type="button" class="button_approval" onClick="window.location='po.php?action=get_po_approval&draft_number=<?php echo $draft_number; ?>'"><? echo $section->fields["headof"]; ?></button>
			  <?php } 
	
            	}
            }
	//If user is head of section but request is not from himself
	   if ( ( $po->fields["open"] == "Y") &&( $po->fields["approved"] == "N")) { 
			if (($username == $section->fields["superapprover"]) ||
	   		   (($username == $section->fields["headof"]) && ($cr_user_id != $section->fields["headof"])) ||
			   (($username == $section->fields["delegate"]) && ($cr_user_id != $section->fields["delegate"]))) {  ?>
	    			<button type="button" class="button_approve" onClick="window.location='po.php?action=approve_po&draft_number=<?php echo $draft_number; ?>'">Approve</button>
	    			<!--button type="button" class="button_reject" onClick="window.location='po.php?action=reject_po&draft_number=<?php echo $draft_number; ?>'">Reject</button-->
            		<?php } 
	   } ?>
      		<?	//Added by JFBucas - the Close PO button is only shown when items are received
           		if ( $po->fields["open"] == "Y") { 
				$can_be_closed = 0;
				$line_items->Move(0);
				while (!$line_items->EOF) {
					if ( $line_items->fields["received"] != "Y" )
						$can_be_closed ++;
					$line_items->MoveNext();
				}

				if ( ( ( $can_be_closed == 0 ) && ( $line_items->RecordCount() > 0 ) ) ||
					 ( $line_items->RecordCount() == 0 ) )  { ?>
					<button type="button" class="button_close" onClick="window.location='po.php?action=close_po&draft_number=<?php echo $draft_number; ?>'">Close</button><?php
				} else { 
	   				if ( ( $po->fields["open"] == "Y") &&( $po->fields["approved"] == "Y")) { 
						?><span class="tip"><button type="button" class="button_close" disabled >Close</button><span>Make sure to mark all goods as received before closing</span></span><?php
					}
				}
			}

?>
         </td>
	 <td>
                <table class="small" align="center" border="0" cellspacing="0" cellpadding="1" width="100%">
	 	<form action="po.php" method="post" name="form_edit_po_details">
		<tr class="box_bg"><td>
	 	Currency :
	        <select name="currency">
                <option value="e" 
		<?php if ( $po->fields["currency"] == "e" ) echo "SELECTED"; ?>
		>Euro &euro;</option>
                <option value="d"
		<?php if ( $po->fields["currency"] == "d" ) echo "SELECTED"; ?>
		>Dollar $</option>
                <option value="l"
		<?php if ( $po->fields["currency"] == "l" ) echo "SELECTED"; ?>
		>Pound &pound;</option>
                <option value="c"
		<?php if ( $po->fields["currency"] == "c" ) echo "SELECTED"; ?>
		>Canadian Dollar $</option>
                </select>
		</td><td>
		Delivery : 
	        <input type="text" name="delivery" size="8" value="<?php echo $po->fields["delivery"]; ?>">
		<?php echo " $currsign"; ?>
		</td><td>
		VAT :
	        <select name="vat">
                <option value="a" 
		<?php if ( $po->fields["vat"] == "a" ) echo "SELECTED"; ?>
		>+0.0%</option>
                <option value="e"
		<?php if ( $po->fields["vat"] == "e" ) echo "SELECTED"; ?>
		>+21%</option>
                <option value="b"
		<?php if ( $po->fields["vat"] == "b" ) echo "SELECTED"; ?>
		>+21.5%</option>
                <option value="c"
		<?php if ( $po->fields["vat"] == "c" ) echo "SELECTED"; ?>
		>+13.5%</option>
                <option value="d"
		<?php if ( $po->fields["vat"] == "d" ) echo "SELECTED"; ?>
		>+4.8%</option>
                </select>
		</td><td>
		<button type="button" class="button_update" onClick="document.form_edit_po_details.submit();">Update</button>
		<input type="hidden" name="action" value="update_po_details">
		<input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
		</form>
		</td></tr></table>
	 </td>
       </tr>
     </table></br>
   <table class="small" cellspacing="0" cellpadding="1" width="100%">
    <tr class="row_head">
      <!--td align="center"><b>Item</b></td-->
      <!--td><b>Shortname</b></td-->
      <td><b>Description</b></td>
      <!--td><b>Alloc</b></td-->
      <td align="center"><b>Qty</b></td>
      <td align="right"><b>Price</b></td>
      <td align="right"><b>Amount</b></td>
      <td align="center"><b>Rcv</b></td> 
   </tr>
    <?php
      $po_total = 0;
      $i = 1;
      $line_items->Move(0);
      while (!$line_items->EOF) {
         if ($i % 2 == 0) {
            echo "<tr class=\"row_even\" ";
         } else {
            echo "<tr class=\"row_odd\" ";
         }
         //echo "onclick=\"showtr('" . $line_items->fields["id"] . "');\">";
         echo ">";
         //if ($po->fields["approved"] == "N") {
            /*echo " <td align=\"center\"><a href=\"javascript:showdiv('"
                 . $line_items->fields["id"]
		 . "');\"> <img src=\"images/edit.gif\" border=\"0\" alt=\"Edit\"></a></td>";*/
         //} else {
	 //  echo ">";  // close the TR opening
            //echo "<td align=\"center\">$i</td>";
         //}
         //echo "<td>" . $line_items->fields["unit"] . "</td>";
         echo "<td onclick=\"showtr('" . $line_items->fields["id"] . "');\">" . substr($line_items->fields["descrip"], 0, 128);
	 if ( strlen($line_items->fields["descrip"]) > 128 ) { echo " ..."; }
	 echo "</td>";
         //echo "<td>" . $line_items->fields["alloc"] . "</td>";
         echo "<td onclick=\"showtr('" . $line_items->fields["id"] . "');\" align=\"center\">" . $line_items->fields["qty"] . "</td>";
         echo "<td onclick=\"showtr('" . $line_items->fields["id"] . "');\" align=\"right\">" . $line_items->fields["unit_price"] . "</td>";
         echo "<td onclick=\"showtr('" . $line_items->fields["id"] . "');\" align=\"right\">" . $line_items->fields["amount"] . "</td>";
           if ($line_items->fields["received"] == "Y") {
              echo "<td title=\"Click on the Y to mark the good as not received\" align=\"center\" onclick='"
                   ."location.href=\"po.php?action=unrecv_line&draft_number=$draft_number&id=" . $line_items->fields["id"] . "\"'>"
                   . "<img src=\"images/yes.png\" border=\"0\" alt=\"Received\"></a></td>";
           } else {
                   //."<a href=\"po.php?action=confirm_recv_line&draft_number=$draft_number&id="
              echo "<td title=\"Click on the N to mark the good as received\" align=\"center\" class=\"\" onclick='"
	           ."location.href=\"po.php?action=recv_line&draft_number=$draft_number&id=" . $line_items->fields["id"] . "\"'>"
                   . "<img src=\"images/no.png\" border=\"0\" alt=\"Not Received\"></a></td>";
           }
         echo "</tr>";

	 if ( ( ($po->fields["open"] == "Y") && ( $po->fields["approved"] != "Y" ) ) 
    		|| ($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) {
		echo "<tr id='" . $line_items->fields["id"] . "' style=\"display:none;\">";
	        //echo "</tr> <tr id='" . $line_items->fields["id"] . "'>";
		echo "<td colspan=8>";
		echo "<div>";
		edit_line($db, $draft_number, $line_items->fields["id"], $po->fields["approved"]);
		echo "</div>";
	 	echo "</td></tr>";
	 }
         $po_total += $line_items->fields["amount"];
         $i++;
         $line_items->MoveNext();
      }
	switch ( $po->fields["vat"] ) {
		case "a" : $po_total_vat = $po_total * 0.0; break;
		case "e" : $po_total_vat = $po_total * 0.21; break;
		case "b" : $po_total_vat = $po_total * 0.215; break;
		case "c" : $po_total_vat = $po_total * 0.135; break;
		case "d" : $po_total_vat = $po_total * 0.048; break;
	}
	?>

       <tr class="row_head">
         <?php printf("<td align=\"right\" colspan=\"3\"><b>Total + %01.2f(vat) + %01.2f(delivery) = </b></td><td align=\"right\"><b>%01.2f %s</b></td>", $po_total_vat, $po->fields["delivery"], $po_total + $po_total_vat + $po->fields["delivery"], $currsign); ?>
         <td colspan="1">&nbsp;</td>
      </tr>
  </table>
  </br> 
  </br> 
<?php
//AJ
         if ($po->fields["approved"] == "N") {
	   new_line_item($draft_number); 
  		echo "</br>   </br> ";
	 }
	if ( $po_total >= $cfg["quotes_required"] ) {
		attached_document_form( "po", $draft_number );
		echo "<br/>";
		attached_document_list( $db, 'po', $draft_number );
		echo "<br/>";
	} 

	if (!$po_comment = $db->Execute("SELECT comment FROM po_comments WHERE draft_number='$draft_number'" )) { 
        	echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		return FALSE;
	}
	$po_comment->fields["comment"] = preg_replace( "/\\'/", "'" , $po_comment->fields["comment"] );
	
	?>

	<table class="small" cellspacing="0" cellpadding="1" width="100%">
  	<form action="po.php" method="post" name="form_edit_comment">
	      <tr class="box_head">
        	 <td align="left" ><b>Comment :</b></td>
	      </tr>
	      <tr class="box_bg">
	         <td>
	         <?php
			echo "<textarea rows=\"10\"  name=\"po_comment\" wrap=\"virtual\"";
         		if ($po->fields["approved"] == "Y") {
				//echo " readonly class=\"PO_comment_readonly\"";
				echo " class=\"PO_comment\"";
			}else{
				echo " class=\"PO_comment\"";
			}
			echo ">\n"; 
			echo $po_comment->fields["comment"];
			echo "</textarea>";
		 
		 if ($po_comment->fields["comment"] == "") { ?>
			<button type="button" class="button_enter" onClick="document.form_edit_comment.submit();">Enter</button><?php
		 }else{ ?>
			<button type="button" class="button_update" onClick="document.form_edit_comment.submit();">Update</button><?php
		 } ?>
 		 </td>
	      </tr>
		<input type="hidden" name="action" value="update_comment">
		<input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
	</form>
	</table></br>
	<?php


	if ( ($user_role == $cfg["admin"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"])) {
		finance_log_show( $db, 'po', $draft_number );
	}

	} else {
      finance_log( $db, 'po', $draft_number, 'warn', "Insufficient privilege. You are " . $username .". Only " . $po->fields["created_by"]. " can modify this Requisition " );
      //po_form($db);
      return FALSE;
     }
//edit_po END
} ?>

<?php
function go_to_po_form() { ?>
   <table class="default" border="0" cellspacing="0" cellpadding="1" align="center">
   <form action="po.php" method="post" name="go_to_po">
      <tr class="row_head"> 
         <td align="center" colspan="2" nowrap><b>Update Requisition No.</b></td>
      </tr>
      <tr class="box_bg"> 
         <td align="right">Requisition No.:</td>
         <td> 
            <input type="text" name="draft_number" size="12">
            <input type="submit" value="Enter">
         </td>
      </tr>
   <input type="hidden" name="action" value="edit_po">
   </form>
   </table>
   <script language="JavaScript">
      document.go_to_po.draft_number.focus();
   </script> <?php
//go_to_po END
} ?>


<?php
function new_line_item($draft_number) { ?>
  <table class="small" width="100%" border="0" cellspacing="0" cellpadding="1">
  <form action="po.php" method="post" name="new_line_item_form">
    <tr class="row_head"> 
      <td colspan="8"><b>Add New Item</b></td>
    </tr>
    <tr class="box_bg"> 
      <td align="right" valign="top">Description</td>
      <td colspan="3"> 
        <input type="text" name="descrip" size="128"
           value="<?php echo $line_item->fields["descrip"]; ?>">
      </td>
      </td>
    </tr>
    <tr class="box_bg"> 
      <!--td align="right">Shortname </td>
      <td> 
        <input type="text" name="unit" size="10" value="">
      </td-->
      <td align="right">Quantity</td>
      <td> 
        <input type="text" name="qty" size="5" value="1">
      </td>
      <td align="right">Unit Price</td>
      <td> 
        <input type="text" name="unit_price" size="16">
      </td>
      <!--td align="right">Section Alloc</td>
      <td> 
        <input type="text" name="alloc" size="16">
      </td-->
    </tr>
    <tr class="box_bg"> 
      <td colspan="8">
   	<button type="button" class="button_add" onClick="if (valid_po_line_form(document.new_line_item_form)) { document.new_line_item_form.submit(); }">Add</button>
	<button type="button" class="button_cancel" onClick="window.location='po.php?action=cancel&draft_number=<?php echo $draft_number; ?>'">Cancel</button>
      </td>
    </tr>
  <input type="hidden" name="action" value="enter_new_line">
  <input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
  </form>
  </table>
  <script language="JavaScript">
     document.new_line_item.unit.focus();
  </script> <?php
//new_line_item END
} ?>



<?php
function new_po_form($db) {
   global $cfg, $username; //, $vendor_category_id;
     if (! $usersection_ids = $db->Execute("SELECT s.name FROM section as s, `users-sections` as us WHERE us.username='$username' and us.section_id = s.id") ) {
          echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";         break;      }
     $usersection_id = $usersection_ids->fields[ "name" ];
     //$vendor_category = $db->Execute("SELECT * FROM vendor_categories WHERE id='$vendor_category_id' ORDER BY name");
     //$vendors = $db->Execute("SELECT DISTINCT name, id FROM vendor WHERE enabled > 0 and account_number='$vendor_category_id' ORDER BY name");
     $vendors = $db->Execute("SELECT DISTINCT name, id FROM vendor WHERE enabled > 0 ORDER BY name");
     $sections = $db->Execute("SELECT name, id FROM section WHERE enabled='Y' ORDER BY name"); ?>
   <table class="default" border="0" align="center" cellpadding="10" width="80%"><tr><td>
  	 <h2 align="center"> New PO </h2> 
   </td></tr></table>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
   <form action="po.php" method="post" name="new_po">
       <tr class="box_head"> 
         <td>Supplier and Section   </td>

       </tr>
   <tr class="box_bg">     <td> &nbsp; </td>      </tr>
       <tr class="box_bg"> 
         <td align="center">Date: <input type="hidden" name="date" value="<?php echo date($cfg["date_arg"]); ?>"> <?php echo date($cfg["date_arg"]); ?>
         </td>
       </tr>
   <tr class="box_bg">     <td> &nbsp; </td>      </tr>
   <tr class="box_bg">
         <td align="center">Supplier: 
            <?php echo $vendors->GetMenu("vendor", "", FALSE); ?>
         </td>
       </tr>
       <tr class="box_bg">
         <td align="center">Section: 
	 	<?php echo $sections->GetMenu("section", "$usersection_id", FALSE, FALSE, 0); ?>
         </td>
       </tr>
   	<tr class="box_bg">     <td> &nbsp; </td>      </tr>
       <tr class="box_bg"> 
         <td align="center" > 
 		<button type="button" class="button_enter" onClick="document.new_po.submit();">Enter</button>
 		<button type="button" class="button_cancel" onClick="window.location='po.php?action=cancel'">Cancel</button>
         </td>
       </tr>
   	<tr class="box_bg">     <td> &nbsp; </td>      </tr>
     <input type="hidden" name="action" value="create_po">
   </form>
   </table>
   <script language="JavaScript">
      document.new_po.date.focus();
   </script> <?php
//new_po
} ?>
<?php
function po_table(&$summary, $db) {
  global $username, $user_role, $cfg;

	    $order_by_query = ereg_replace('&order_by=[^&]*', '', $_SERVER['QUERY_STRING'] );
	    $order_by_query = ereg_replace('&order=[^&]*', '', $order_by_query );
            $SortLinkDescBegin = "<a href=\"po.php?$order_by_query&order_by=";
            $SortLinkDescEnd   = "&order=DESC\"><img src=\"images/s_desc.png\" border=\"0\"></a>";
            $SortLinkAscBegin  = "<a href=\"po.php?$order_by_query&order_by=";
            $SortLinkAscEnd    = "&order=\"><img src=\"images/s_asc.png\" border=\"0\"></a>";


   		$sections = $db->Execute("SELECT id, name FROM section WHERE enabled='Y' ORDER BY name");
		$section_filter_query = ereg_replace('&section_id=[0-9]*', '', $_SERVER['QUERY_STRING'] );
		$section_filter  = $sections->GetMenu4("filter_section", $section_id, "po.php?$section_filter_query&section_id=" );
   		
		$supplier = $db->Execute("SELECT id, name FROM vendor WHERE enabled > 0 ORDER BY name"); 
		$supplier_filter_query = ereg_replace('&vendor_id=[0-9]*', '', $_SERVER['QUERY_STRING'] );
		$supplier_filter  = $supplier->GetMenu4("filter_vendor", $vendor_id, "po.php?$supplier_filter_query&vendor_id=" );


		$status_filter_query = ereg_replace('&status=[^&]*', '', $_SERVER['QUERY_STRING'] );
		$status_filter = "<select name=\"status\" onchange=\"javascript:if (this.value){window.location='po.php?$status_filter_query&status='+this.value+'';}\">";
                if ( ! isset($status) ) $selected_none = "selected";
            	$status_filter .= "<option value=\"\" $selected_none>-- Status --</option>";
                if ( $status == "open" ) $selected_open = "selected";
                $status_filter .= "<option value=\"open\" $selected_open>Open</option>";
                if ( $status == "approved" ) $selected_approved = "selected";
                $status_filter .= "<option value=\"approved\" $selected_approved>Approved</option>";
                if ( $status == "closed" ) $selected_close = "selected";
              	$status_filter .= "<option value=\"closed\" $selected_close>Closed</option>";
                $status_filter .= '</select>';

 ?>
   <table class="small" border="0" cellpadding="1" cellspacing="0" width="100%">
      <tr class="row_head">
         <?
	  echo "<td>". $SortLinkDescBegin . "draft_number" . $SortLinkDescEnd . " <b>Req.</b> "            . $SortLinkAscBegin . "draft_number" . $SortLinkAscEnd . "</td>";
          echo "<td>". $SortLinkDescBegin . "created_by"   . $SortLinkDescEnd . " <b>Creator</b> "         . $SortLinkAscBegin . "created_by"   . $SortLinkAscEnd . "</td>";
          echo "<td align=\"center\">". $SortLinkDescBegin . "date"         . $SortLinkDescEnd . " <b>Date</b> "            . $SortLinkAscBegin . "date"         . $SortLinkAscEnd . "</td>";
          echo "<td>". $SortLinkDescBegin . "section"      . $SortLinkDescEnd . " <b>$section_filter</b> " . $SortLinkAscBegin . "section"      . $SortLinkAscEnd . "</td>";
          echo "<td>". $SortLinkDescBegin . "vendor"       . $SortLinkDescEnd . " <b>$supplier_filter</b> ". $SortLinkAscBegin . "vendor"       . $SortLinkAscEnd . "</td>";
          echo "<td align=\"center\">". $SortLinkDescBegin . "status"         . $SortLinkDescEnd . " <b>$status_filter</b> "            . $SortLinkAscBegin . "status"         . $SortLinkAscEnd . "</td>";
          echo "<td align=\"center\">". $SortLinkDescBegin . "po_approved_number"         . $SortLinkDescEnd . " <b>Approved Number</b> "            . $SortLinkAscBegin . "po_approved_number"         . $SortLinkAscEnd . "</td>";
	  ?>
      </tr> <?php
      $i = 1;
      while (!$summary->EOF) {
	  //Extra DB queries
          $section_id = $summary->fields["section"];
          if (!$section_details = $db->Execute("SELECT * FROM section WHERE id=$section_id")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
           }
	  $superapprover=$section_details->fields["superapprover"];
	  $headof=$section_details->fields["headof"];
	  $delegate=$section_details->fields["delegate"];
	  $po_creator=$summary->fields["created_by"];          
          if (($user_role == $cfg["admin"]) || ($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($username == $po_creator) || ($username == $superapprover) || ($username == $headof) || ($username == $delegate)){
             if ($i % 2 == 0) {
                echo "<tr class=\"row_even\">";
              } else {
                 echo "<tr class=\"row_odd\">";
              }
              if (!$user_details = $db->Execute("SELECT * FROM users WHERE username='$po_creator'")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
              }
             ?>
         <td>
            <a href="po.php?action=view_from_search&draft_number=<?php echo $summary->fields["draft_number"]; ?>">
               <?php echo $summary->fields["draft_number"]; ?></a>
         </td>
         <td><?php echo $user_details->fields["fullname"]; ?></td>
         <td><?php echo display_date($summary->fields["date"]); ?></td>
         <td><?php echo $summary->fields[4]; ?></td>
         <td><?php echo $summary->fields[5]; ?></td>
         <td align="center"> <?php 
            if ($summary->fields["open"] == "Y" && $summary->fields["approved"] == "N") { ?>
	    	<div class=po_open>Open</div></td><td align="center">N/A</td> <?php
            } else if ($summary->fields["open"] == "Y" && $summary->fields["approved"] == "Y") { ?>
	    	<div class=po_approved>Approved</div></td><td align="center"><?php echo $summary->fields[6]?></td> <?php
            } else { ?>
	    	<div class=po_closed>Closed</div></td><td align="center"><?php echo $summary->fields[6]?></td> <?php
            } ?>
         </td>
      </tr> <?php
           $i++;
	 //End if user_role
         }
         $summary->MoveNext();
      } ?>         <td>
   </table>
   <table class="small" border="0" cellpadding="1" cellspacing="0" width="100%">
      <tr class="row_head">
         <td align="center"> <?php
            if (!$summary->AtFirstPage() && $summary->AbsolutePage() != -1) { ?>
               <a href="search_po.php?action=paged_query&page=<?php echo $summary->AbsolutePage() - 1; ?>">
                  <img src="images/previous.png" border="0" alt="Previous"></a> <?php
            }
            echo "&nbsp;";
            if (!$summary->AtLastPage() && $summary->AbsolutePage() != -1) { ?>
               <a href="search_po.php?action=paged_query&page=<?php echo $summary->AbsolutePage() + 1; ?>">
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
            <!--a href="search_po.php?action=print_result"><img src="images/print_btn.gif" alt="Print Results" border="0"></a>
            <a href="search_po.php"><img src="images/search_btn.gif" border="0" alt="Search"></a-->
         </td>
      </tr>
   </table> <?php
//po_table
} ?>

<?php
function search_po_form($db) {
   global $cfg;
   $vendors = $db->Execute("SELECT DISTINCT name, id FROM vendor WHERE enabled > 0 ORDER BY name");
   $sections = $db->Execute("SELECT name, id FROM section WHERE enabled='Y' ORDER BY name"); ?>
   <table class="default" width="100%" border="0" cellspacing="0" cellpadding="1">
   <form action="po.php" method="get" name="search_po">
      <tr class="row_head">
         <td colspan="5"><b>Search the Purchase Order Dictionary</b></td>
      </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <td colspan="4">
            <select name="action">
               <option value="search_none" SELECTED>--- Select Search Type ---</option>
               <option value="search_single">Show a Single Purchase Order</option>
               <option value="search_all">Show All Purchase Orders</option>
               <option value="search_section">Show Purchase Orders by Section</option>
               <option value="search_vendor">Show Purchase Orders by Supplier</option>
            </select>
         </td>
      </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <td align="right">Requisition No.:</td>
         <td colspan="3">
            <input type="text" name="draft_number" size="12">
         </td>
      </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <td align="right">From Date:</td>
         <td>
            <input type="text" name="from_date" size="12"
               onchange="return BisDate(this,'N')"><?php echo $cfg["date_exp"]; ?>
         </td>
         <td align="right">To Date:</td>
         <td>
            <input type="text" name="to_date" size="12"
               onchange="return BisDate(this,'N')"><?php echo $cfg["date_exp"]; ?>
         </td>
      </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <td align="right">Section:</td>
         <td colspan="3">
            <?php echo $sections->GetMenu("section_id", "", FALSE); ?>
         </td>
      </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <td align="right">Supplier:</td>
         <td colspan="3">
            <?php echo $vendors->GetMenu("vendor_id", "", FALSE); ?>
         </td>
      </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <td align="right">PO Status:</td>
         <td colspan="3">
            <input type="radio" name="status" value="any" checked>Any &nbsp;&nbsp;
            <input type="radio" name="status" value="open">Open &nbsp;&nbsp;
            <input type="radio" name="status" value="closed">Closed
         </td>
      </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <td align="right">Order By:</td>
         <td colspan="3">
            <input type="radio" name="order_by" value="draft_number" checked>Requisition No. &nbsp;&nbsp;
            <input type="radio" name="order_by" value="po.date">Date &nbsp;&nbsp;
            <input type="checkbox" name="order" value="DESC">Reverse
         </td>
      </tr>
      <tr class="row_head">
         <td colspan="4">
            Select search type, fill in parameters and click Search...
         </td>
         <td align="right">
	 	<button type="button" class="button_search" onClick="document.search_po.submit();">
         </td>
      </tr>
   </form>
   </table> <?php
//search END
} ?>




<?php
function show_po_list($db) {
   global $action, $status, $section_id, $vendor_id,
          $from_date, $to_date, $order_by, $order;
   if ($from_date == "") {
      $from_date = "2006-01-01";
   } else {
      $from_date = valid_date($from_date);
   }
   if ($to_date == "") {
      $to_date = date("Y-m-d");
   } else {
      $to_date = valid_date($to_date);
   }
   if ($order != "DESC") {
      $order = "ASC";
   }

   $_SESSION["search_query"] =
      "SELECT po.draft_number, po.date, po.open, po.approved, section.name, vendor.name, po.po_approved_number, po.created_by, po.section"
      . " FROM po, section, vendor"
      . " WHERE po.vendor=vendor.id"
      . " AND po.section=section.id"
      . " AND po.date>='$from_date'"
      . " AND po.date<='$to_date'";

   if ( ($action == "search_section") || ( isset($section_id ) ) ) {
      $_SESSION["search_query"] .= " AND po.section=$section_id";
   }
   if ($action == "search_vendor") {
      $_SESSION["search_query"] .= " AND po.vendor=$vendor_id";
   }
   if ($status == "open") {
      $_SESSION["search_query"] .= " AND po.open='Y' AND po.approved='N'";
   }
   if ($status == "approved") {
      $_SESSION["search_query"] .= " AND po.open='Y' AND po.approved='Y'";
   }
   if ($status == "closed") {
      $_SESSION["search_query"] .= " AND po.open='N'";
   }
   $_SESSION["search_query"] .= " ORDER BY $order_by $order";
   echo $_SESSION["search_query"];
   if (!$summary = paged_query($db)) {
      search_po_form($db);
   } else {
      po_table($summary, $db);
   }
//show_po_list END
} ?>

<?php
function view_po($db, $draft_number, $from_search = FALSE) {
   global $username, $cfg;
   if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
   if ($po->RecordCount() == 0) {
      finance_log( $db, 'po', $draft_number, 'warn', "Requisition No. $draft_number not found." );
      //po_form($db);
      return;
   }

   $currsign=get_po_currency_sign($draft_number);

   $vendor_id = $po->fields["vendor"];
   $section_id = $po->fields["section"];
   $cr_user_id = $po->fields["created_by"];
   $appr_user_id = $po->fields["approved_by"];
   $vendor = $db->Execute("SELECT DISTINCT name FROM vendor WHERE id=$vendor_id");
   $section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
   $cr_user = $db->Execute("SELECT fullname FROM users WHERE username='$cr_user_id'");
   $appr_user = $db->Execute("SELECT fullname FROM users WHERE username='$appr_user_id'");
   $line_items = $db->Execute("SELECT * FROM line_items WHERE draft_number=$draft_number ORDER BY id"); ?>

   <table class="small" align="center" border="0" cellspacing="0" cellpadding="1" width="100%">
    <tr class="box_head"> 
      <td nowrap><b>View Requisition </b></td>
      <td align="right">Requisition : <?php echo $draft_number; ?></td>
    </tr>
    <tr class="box_bg">
      <td> <table><tr class="box_bg"><td>Status:</td><td><?php
 		$po_status = get_po_status( $draft_number ); 
		echo "<div class=po_". strtolower($po_status) . ">". $po_status . "</div>";

	 echo "</td><td>";

         if ($po->fields["open"] == "Y") { ?>
	    <button type="button" class="button_edit" onClick="window.location='po.php?action=edit_po&draft_number=<?php echo $draft_number; ?>'">Edit</button> <?php
         }
         if (($po->fields["approved"] == "Y") && ($po->fields["po_approved_number"] != "")){ ?>
	    <button type="button" class="button_pdf" onClick="window.location='print_po.php?action=print_gd_po&printer=printer3&draft_number=<?php echo $draft_number; ?>'"></button> 
	    <button type="button" class="button_email" onClick="window.location='print_po.php?action=print_gd_po&printer=printer4&draft_number=<?php echo $draft_number; ?>'"></button> <?php
         } ?>
	 </td></tr></table>
      </td>
      <td align="right">Date:&nbsp;<?php echo display_date($po->fields["date"]); ?></td>
    </tr>
    <tr class="box_bg"> 
      <td>&nbsp;&nbsp;Section:&nbsp;<?php echo $section->fields["name"]; ?></td>
      <td align="right">Requested By:&nbsp;<?php echo $cr_user->fields["fullname"]; ?></td>
    </tr>
    <tr class="box_bg">
      <td>&nbsp;&nbsp;Supplier:&nbsp;<?php echo $vendor->fields["name"]; ?></td>
     <?php 
     if ($po->fields["approved"] == "Y"){
       ?>
         <td align="right">&nbsp;&nbsp;Approved by :&nbsp;<?php echo $appr_user->fields["fullname"]; ?></td>
      <?php } else { ?>
         <td align="right">&nbsp;</td>
      <?php   }  ?>
     </tr>    

      <?php
       if ( $po->fields["po_approved_number"] != ""){
         ?>
         <tr class="box_bg">
         <td>&nbsp;</td>
         <td align="right">&nbsp;&nbsp;PO Number:&nbsp;<?php echo $po->fields["po_approved_number"]; ?></td>
         </tr>
     <?php
       }
      ?>
         <tr class="box_bg">
         <td>&nbsp;</td>
         <td align="center">
	 <table class="small" ><tr>
	         <td align="left">
		 	Currency : <?php echo $currsign; ?>
		</td><td align="center">
			Delivery : <?php echo $po->fields["delivery"] . " $currsign"; ?>
		</td><td align="right">
			VAT :
		        <?php
				switch ( $po->fields["vat"] ) {
					case "a" : echo "+0.0%"; break;
					case "b" : echo "+21.5%"; break;
					case "c" : echo "+13.5%"; break;
					case "d" : echo "+4.8%"; break;
					case "e" : echo "+21%"; break;
				}
			?>
	        </td></tr>
	  </table>
       	  </td></tr>
      </table></br>
  <table class="small" cellspacing="0" cellpadding="0" width="100%">
    <tr class="row_head">
      <td align="center"><b>Item</b></td>
      <!--td><b>Shortname</b></td-->
      <td><b>Description</b></td>
      <!--td><b>Alloc</b></td-->
      <td align="center"><b>Qty</b></td>
      <td align="right"><b>Price</b></td>
      <td align="right"><b>Amount</b></td>
      <td align="center"><b>Rcv</b></td>
    </tr> <?php
      $po_total = 0;
      $i = 1;
      while (!$line_items->EOF) {
         if ($i % 2 == 0) {
            echo "<tr class=\"row_even\">";
         } else {
            echo "<tr class=\"row_odd\">";
         }
         echo "<td align=\"center\">$i</td>";
         //echo "<td>" . $line_items->fields["unit"] . "</td>";
         echo "<td>" . substr($line_items->fields["descrip"], 0, 128) . "</td>";
         //echo "<td>" . $line_items->fields["alloc"] . "</td>";
         echo "<td align=\"center\">" . $line_items->fields["qty"] . "</td>";
         echo "<td align=\"right\">" . $line_items->fields["unit_price"] . "</td>";
         echo "<td align=\"right\">" . $line_items->fields["amount"] . "</td>";
           if ($line_items->fields["received"] == "Y") {
              echo "<td title=\"Edit and click on the Y to mark the good as not received\" align=\"center\"><img src=\"images/yes.png\" border=\"0\" alt=\"Received\"></td>";
           } else {
              echo "<td title=\"Edit and click on the N to mark the good as received\" align=\"center\"><img src=\"images/no.png\" border=\"0\" alt=\"Not Received\"></td>";
         }
         echo "</tr>";
         $po_total += $line_items->fields["amount"];
         $i++;
         $line_items->MoveNext();
      }

	switch ( $po->fields["vat"] ) {
		case "a" : $po_total_vat = $po_total * 0.0; break;
		case "e" : $po_total_vat = $po_total * 0.21; break;
		case "b" : $po_total_vat = $po_total * 0.215; break;
		case "c" : $po_total_vat = $po_total * 0.135; break;
		case "d" : $po_total_vat = $po_total * 0.048; break;
	}
	$po_total += $po->fields["delivery"];

	?>
        <tr class="row_head">
         <?php printf("<td align=\"right\" colspan=\"4\"><b>Total + %01.2f(vat) + %01.2f(delivery) = </b></td><td align=\"right\"><b>%01.2f %s</b></td>", $po_total_vat, $po->fields["delivery"], $po_total + $po_total_vat + $po->fields["delivery"], $currsign); ?>
         <td colspan="1">&nbsp;</td>
      </tr>
   </table></br>

   	<?php
		attached_document_list( $db, 'po', $draft_number );
		echo "<br/>";

	if (!$po_comment = $db->Execute("SELECT comment FROM po_comments WHERE draft_number='$draft_number'" )) { 
        	echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		return FALSE;
	}
	$po_comment->fields["comment"] = preg_replace( "/\\'/", "'" , $po_comment->fields["comment"] );
	
	?>

	<table class="small" cellspacing="0" cellpadding="1" width="100%">
	      <tr class="box_head">
        	 <td align="left" ><b>Comment :</b></td>
	      </tr>
	      <tr class="box_bg">
	         <td>
	         <?php
			echo "<textarea rows=\"10\" class=\"PO_comment_readonly\" name=\"po_comment\" wrap=\"virtual\" readonly >";
			echo $po_comment->fields["comment"];
			echo "</textarea>";
		 ?>
 		 </td>
	      </tr>
	</table>
	</br></br>

	<!--table class="default">
      <tr>
         <td align="right" colspan="9">
            <?php if ($from_search == TRUE) { ?>
               <a href="search_po.php"><img src="images/ search_btn.gif" border="0" alt="Search"></a>
            <?php } else { ?>
               <a href="po.php"><img src="images/edit_btn.gif" border="0" alt="Edit"></a>another Requisition.
            <?php } ?>
         </td>
      </tr>
   </table--> <?php

	if ( ($user_role == $cfg["admin"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"])) {
		finance_log_show( $db, 'po', $draft_number );
	}
} ?>


<?php

// ACTION SECTION

   po_buttons(false); 


   $action = strtolower($action);
   switch ($action) {
      case "approve_po":
        $po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number");
        $section_id = $po->fields["section"];
        $section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
	if (($username == $section->fields["superapprover"]) ||
	   (($username == $section->fields["headof"]) && ($cr_user_id != $section->fields["headof"])) ||
	   (($username == $section->fields["delegate"]) && ($cr_user_id != $section->fields["delegate"]))) { 
            if (!$db->Execute("UPDATE po SET approved='Y', approved_by='$username' WHERE draft_number='$draft_number'")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }
// Unnecessary extra query
//           $cr_username = $db->Execute("SELECT created_by FROM po WHERE draft_number='$draft_number'");
//            $cr_username_id = $cr_username->fields["created_by"];
	    $cr_username_id= $po->fields["created_by"];
            $approver_data = $db->Execute("SELECT * FROM users WHERE username='$username'");
            $approver_fullname = $approver_data->fields["fullname"];
	 // Email to Finance and the cr_user
	 $mail_headers = 'From: ' . $cfg["finance_email"] . "\r\n" .
            'Reply-To: ' . $cfg["finance_email"] . "\r\n" .
            'To: ' . $cfg["finance_email"] . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
         $email_body="Requisition " . $draft_number . " has been Approved by " . $approver_fullname . ". \r\n\r\n" . po_description($draft_number);
            do_mail($cr_username_id,
              "Requisition $draft_number : Approved notification",
              $email_body,
              $mail_headers);
//               do_mail($cr_email->fields["email"],
//                    "Requisition  approval",
//                    "Requisition  $draft_number has been approved by $fullname",
//                    "From: AssetMan@" . $_SERVER["SERVER_NAME"] . "\n");
//               echo "<table class=\"info\" width=\"100%\"><tr><td>A Requisition approval notification has been E-Mailed to "
//                    . $cr_email->fields["email"] . "</td></tr></table>";
            finance_log( $db, 'po', $draft_number, 'info', "Approved by $username", "log" );
            finance_log( $db, 'po', $draft_number, 'info', "Notification has been sent to $cr_username_id." );
         } else {
            finance_log( $db, 'po', $draft_number, 'warn', "You are not allowed to approve this order." );
         }
         edit_po_form($db, $draft_number);
         break;
//approve END
   case "reject_po":
        $po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number");
        $section_id = $po->fields["section"];
        $section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
         if (($username == $section->fields["headof"]) || ($username == $section->fields["delegate"])) {
            if (!$db->Execute("UPDATE po SET status='Open', approved_by='' WHERE draft_number='$draft_number'")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }
	    $cr_username_id= $po->fields["created_by"];
            $approver_data = $db->Execute("SELECT * FROM users WHERE username='$username'");
            $approver_fullname = $approver_data->fields["fullname"];
	 // Email to Finance and the cr_user
	 $mail_headers = 'From: ' . $cfg["finance_email"] . "\r\n" .
            'Reply-To: ' . $cfg["finance_email"] . "\r\n" .
            'To: ' . $cfg["finance_email"] . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
         $email_body="Requisition " . $draft_number . " has been REJECTED by " . $approver_fullname . ". \r\n\r\n" . po_description($draft_number);
            do_mail($cr_username_id,
              "Requisition $draft_number : Rejected notification",
              $email_body,
              $mail_headers);
            finance_log( $db, 'po', $draft_number, 'warn', "Rejected by $username", "log" );
            finance_log( $db, 'po', $draft_number, 'info', "Notification has been sent to $cr_username_id." );
         } else {
            finance_log( $db, 'po', $draft_number, 'warn', "You are not allowed to reject this request." );
         }
         edit_po_form($db, $draft_number);
         break;

      case "cancel":
         finance_log( $db, 'po', $draft_number, 'warn', "Operation canceled by $username", "log" );
         finance_log( $db, 'po', $draft_number, 'warn', "Purchase Order operation canceled." );
         edit_po_form($db, $draft_number);
         break;
//cancel END
      case "cancel_po":
         if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            } 
  	 if (($username == $po->fields["created_by"]) || ($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) {
           if (!$po = $db->Execute("SELECT open, approved FROM po WHERE draft_number=$draft_number")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }
            if ($po->fields["open"] == "Y") {
               if (!$db->Execute("UPDATE po SET open='C' WHERE draft_number=$draft_number")) {
                  echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
                  break;
               }
                 finance_log( $db, 'po', $draft_number, 'info', "PO canceled by $username", "log" );
                 finance_log( $db, 'po', $draft_number, 'info', "PO has been canceled." );
            }
            edit_po_form($db, $draft_number);
	  }else{
            finance_log( $db, 'po', $draft_number, 'warn', "Insufficient privilege. Only " . $po->fields["created_by"]. " can cancel this Requisition." );
	  }

         break;
//close_po END
      case "close_po":
         if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            } 
  	 if (($username == $po->fields["created_by"]) || ($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) {
            if (!$line_items = $db->Execute("SELECT received FROM line_items WHERE draft_number=$draft_number")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }
              $not_recvd = FALSE;
              while (!$line_items->EOF) {
                 if ($line_items->fields["received"] == "N") {
                  $not_recvd = TRUE;
                 }
                 $line_items->MoveNext();
              }
              if ($not_recvd == TRUE) {
                 echo "<table class=\"warn\" width=\"100%\"><tr><td>One or more line items associated with this purchase order"
                 . " have NOT been received. Either receive or delete the offending"
                 . " line items. You may then close this purchase order.</td></tr></table>";
                 edit_po_form($db, $draft_number);
                 break;
              }
            if (!$po = $db->Execute("SELECT open, approved FROM po WHERE draft_number=$draft_number")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }
            /*if ($po->fields["approved"] == "N") {
               finance_log( $db, 'po', $draft_number, 'warn', "Requisition $draft_number has not yet been approved!" );
               edit_po_form($db, $draft_number);
               break;
            }*/
            if ($po->fields["open"] == "Y") {
               if (!$db->Execute("UPDATE po SET open='N' WHERE draft_number=$draft_number")) {
                  echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
                  break;
               }
		 $mail_headers = 'From: ' . $cfg["finance_email"] . "\r\n" .
       	          'Reply-To: ' . $cfg["finance_email"] . "\r\n" .
                  'X-Mailer: PHP/' . phpversion();
                 $email_body="Requisition " .$draft_number . " has been closed.\r\n" ;
                 $email_body= $email_body . "Please visit: " . $cfg["baseurl"] . "\r\n\r\n" . po_description($draft_number);
                 //do_mail($cfg["finance_email_notify_closure"], "Requisition $draft_number : Closed", $email_body,  $mail_headers);
                 //finance_log( $db, 'po', $draft_number, 'info', "PO is closed." );
                 finance_log( $db, 'po', $draft_number, 'info', "PO has been closed by $username", "log" );
            }
            edit_po_form($db, $draft_number);
	  }else{
            finance_log( $db, 'po', $draft_number, 'warn', "Insufficient privilege. Only " . $po->fields["created_by"]. " can close this Requisition " );
	  }

         break;
//close_po END
     /* case "confirm_recv_line":
            if (!$line_item = $db->Execute("SELECT * FROM line_items WHERE id=$id")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }
            finance_log( $db, 'po', $draft_number, 'info', "Please confirm that you received the following line item..." ); ?>
            <table class="small" align="center" border="0" cellpadding="1" cellspacing="0" width="100%">
               <tr class="row_head">
                  <td><b>Requisition</b></td>
                  <td><b>Description</b></td>
                  <td align="center"><b>Qty</b></td>
                  <td align="right"><b>Price</b></td>
                  <td align="right"><b>Amount</b></td>
               </tr>
               <tr class="box_bg">
                  <td><?php echo $line_item->fields["draft_number"]; ?></td>
                  <td><?php echo $line_item->fields["descrip"]; ?></td>
                  <td align="center"><?php echo $line_item->fields["qty"]; ?></td>
                  <td align="right"><?php echo $line_item->fields["unit_price"]; ?></td>
                  <td align="right"><?php echo $line_item->fields["amount"]; ?></td>
               </tr>
            </table></br> <?php
            echo "<table class=\"small\" width=\"100%\"><tr class=\"box_bg\"><td><a href=\"new_asset.php?action=from_po&line_id=$id\"><img src=\"images/yes_btn.gif\" border=\"0\" alt=\"Yes\">This is a capital asset. Add to the Asset Dictionary.</a><br>"
              . "<a href=\"new_item.php?action=from_po&line_id=$id\"><img src=\"images/yes_btn.gif\" border=\"0\" alt=\"Yes\">This is not a capital asset but I want to track it. Add to the Item Master Dictionary.</a><br>"
              . "<a href=\"po.php?action=recv_line&draft_number=$draft_number&id=$id\"><img src=\"images/yes_btn.gif\" border=\"0\" alt=\"Yes\">I don't want to track it or I'll deal with it later. Just mark this line item received.</a><br>"
              . "<a href=\"po.php?action=cancel&draft_number=$draft_number\"><img src=\"images/no_btn.gif\" border=\"0\" alt=\"No\">I was jes' foolin'. This line item has not been received.</a></td></tr></table>";
            break;*/
      case "create_po":
         if (!$date = valid_date($date)) {
            finance_log( $db, 'po', $draft_number, 'warn', "You must enter a valid date format." );
            new_po_form($db);
            break;
         }
         $draft_number = $db->GenID("po_seq");
         $query = "INSERT INTO po (draft_number, date, vendor, section, open, created_by, approved)"
                . " VALUES ('$draft_number', '$date', '$vendor', '$section', 'Y', '$username', 'N')";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         finance_log( $db, 'po', $draft_number, 'info', "PO created by $username.", "log" ); ?>
         <script language="JavaScript">
            window.location="po.php?action=edit_po&draft_number=<?php echo $draft_number; ?>";
         </script> <?php
         break;
//create_po END

      case "delete_line":
            if (!$db->Execute("DELETE FROM line_items WHERE id=$id")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }
      		finance_log( $db, 'po', $draft_number, 'warn', "Line removed by $username.", "log" );
            edit_po_form($db, $draft_number);
         break;
//delete_line END
      case "edit_po":
      		finance_log( $db, 'po', $draft_number, 'info', "PO edited by $username.", "log" );
              edit_po_form($db, $draft_number);
         break;
//edit_po END

      case "enter_new_line":  	
            $unit_price = sprintf("%01.2f", $unit_price);
            $amount = sprintf("%01.2f", $qty * $unit_price);
            $id = $db->GenID("line_items_seq");
            $query = "INSERT INTO line_items (id, draft_number, qty, inv_qty, unit, descrip, alloc, unit_price, amount, received)"
                . " VALUES ('$id', '$draft_number', '$qty', '$qty', '$unit', "
                . $db->QMagic($descrip) . ", " . $db->QMagic($alloc) . ", '$unit_price', '$amount', 'N')";
            if (!$db->Execute($query)) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
             }
      		finance_log( $db, 'po', $draft_number, 'info', "New line entered by $username VALUES ( '$qty', '$unit', " . $db->QMagic($descrip) . ", " . $db->QMagic($alloc) . ", '$unit_price', '$amount')", "log" );
            edit_po_form($db, $draft_number);
            break;
//enter_new_line END
          
      case "get_po_approval":
         if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           return FALSE;
         }
        $cr_user_id = $po->fields["created_by"];
        $section_id = $po->fields["section"];
        $section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
	if (($cr_user_id == $section->fields["headof"]) || ($cr_user_id == $section->fields["delegate"])) { 
	        $approver = $section->fields["superapprover"];
        	unset( $delegate );
	} else {
	        $approver = $section->fields["headof"];
        	$delegate = $section->fields["delegate"];
	}
        if (!$requisitioner_data = $db->Execute("SELECT * FROM users WHERE username='$username'")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }
	 $mail_headers = 'From: ' . $cfg["finance_email"] . "\r\n" .
            'Reply-To: ' . $cfg["finance_email"] . "\r\n" .
            'To: ' . $username . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
         $email_body="Requisition " .$draft_number . " has been submitted by ".  $requisitioner_data->fields["fullname"] . " for your approval." . "\r\n" ;
         $email_body= $email_body . "Please visit: " . $cfg["baseurl"] . "\r\n\r\n" . po_description($draft_number);
	 if (! isset($delegate_only) ) {
              do_mail($approver,
              "Requisition $draft_number : Approval request sent to Head of Section",
              $email_body,
              $mail_headers);
              if (!$approver_data = $db->Execute("SELECT * FROM users WHERE username='$approver'")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
              }
              finance_log( $db, 'po', $draft_number, 'info', "Your request has been forwarded to " . $approver_data->fields["fullname"] . " (" . $approver .  ")." );
	 }
	 if (( $delegate != "" ) && ( $delegate != $cfg["sysadmin_email"] ) && ( !isset($head_of_section_only))) {
	       do_mail($delegate,
       	       "Requisition $draft_number : Approval request sent to Delegate",
       	       $email_body,
       	       $mail_headers);
              if (!$delegate_data = $db->Execute("SELECT * FROM users WHERE username='$delegate'")) {
                echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
                break;
              }
              finance_log( $db, 'po', $draft_number, 'info', "Your request has been forwarded to " . $delegate_data->fields["fullname"] . " (" . $delegate .  ")." );
	 }
              finance_log( $db, 'po', $draft_number, 'info', "Email was : " . $email_body, "log" );
              finance_log( $db, 'po', $draft_number, 'info', "Request for approval by $username.", "log" );
              finance_log( $db, 'po', $draft_number, 'info', "Requester will be notifed by E-Mail when this Requisition has been approved.", "show" );
         edit_po_form($db, $draft_number);
         break;
//get_po_approval END
     case "new_po":
//         if(isset($vendor_category_id) and strlen($vendor_category_id) > 0){
            new_po_form($db);
 //        }else{
//	    select_vendor_category_form($db);
  //       } 

            break;
//new_po END
   case "paged_query":
      $summary = paged_query($db, $page);
      po_table($summary, $db);
      break;
  case "recv_line":
      if (!$line_item = $db->Execute("SELECT received FROM line_items WHERE id=$id")) {
          echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
          break;
       }
         if (!$db->Execute("UPDATE line_items SET received='Y' WHERE id=$id")) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
          }
       edit_po_form($db, $draft_number);
         break;
  case "unrecv_line":
      if (!$line_item = $db->Execute("SELECT received FROM line_items WHERE id=$id")) {
          echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
          break;
       }
         if (!$db->Execute("UPDATE line_items SET received='N' WHERE id=$id")) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
          }
       edit_po_form($db, $draft_number);
         break;
  case "search_po":
      search_po_form($db);
      break;
   case "search_single":
      if (empty($draft_number)) {
         finance_log( $db, 'po', $draft_number, 'warn', "You must enter a valid purchase order number." );
         search_po_form($db);
         break;
      }
      if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
         echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
         break;
      }
      if ($po->RecordCount() == 0) {
         finance_log( $db, 'po', $draft_number, 'warn', "Requisition No. $draft_number not found." );
         search_po_form($db);
         break;
      } ?>
      <script language="JavaScript">
         window.location="po.php?action=view_from_search&draft_number=<?php echo $draft_number; ?>";
      </script> <?php
      break;
   case "search_all":
      show_po_list($db);
      break;
   case "search_section":
      show_po_list($db);
      break;
   case "search_vendor":
      show_po_list($db);
      break;

   case "search_none":
      finance_log( $db, 'po', $draft_number, 'warn', "You must select a Search Type from the drop menu.", "show" );
      search_po_form($db);
      break;
// case search_none END

   case "update_line":
      $unit_price = sprintf("%01.2f", $unit_price);
      $amount = sprintf("%01.2f", $qty * $unit_price);
      $query = "UPDATE line_items SET"
         . " qty='$qty', inv_qty='$qty', unit='$unit', descrip=" . $db->QMagic($descrip) . ","
         . " alloc=" . $db->QMagic($alloc) . ", unit_price='$unit_price', amount='$amount'"
         . " WHERE id=$id";
      if (!$db->Execute($query)) {
         echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
         }
      finance_log( $db, 'po', $draft_number, 'info', "Line updated by $username. VALUES( $qty, $unit, " . $db->QMagic($descrip) . ", " . $db->QMagic($alloc) . ", $unit_price, $amount )", "log" );
      edit_po_form($db, $draft_number);
      break;
//update_line END
// case update PO commment
      case "update_comment":  	
	$po_comment = preg_replace( "/'/", "\'" , $po_comment );
        if (!$already_exists = $db->Execute("SELECT * FROM po_comments WHERE draft_number='$draft_number'" )) { 
        	echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		break;
	}
   	if ($already_exists->RecordCount() == 0) {
		if (!$db->Execute("INSERT INTO po_comments (draft_number, comment) VALUES ('$draft_number', '$po_comment')")) {
			echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
			break;
		}
	} else {
		if (!$db->Execute("UPDATE po_comments SET comment='$po_comment' WHERE draft_number='$draft_number'")) {
			echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
			break;
		}
	}
        finance_log( $db, 'po', $draft_number, 'info', "Comment Updated." );
        finance_log( $db, 'po', $draft_number, 'info', "New comment is " . $po_comment, "log" );
	edit_po_form($db, $draft_number);
	break;
// END update PO commment
      case "upload_attached_document":  	
	attached_document_upload($db, "po", $draft_number );
	edit_po_form($db, $draft_number);
	break;
      case "delete_attached_document":  	
	attached_document_delete($db, "po", $draft_number, $code );
	edit_po_form($db, $draft_number);
	break;
// case update PO details
      case "update_po_details":  	
	if (!$db->Execute("UPDATE po SET currency='$currency', delivery='$delivery', vat='$vat'  WHERE draft_number='$draft_number'")) {
		echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		break;
	}
        finance_log( $db, 'po', $draft_number, 'info', "PO Details Updated. VALUES( $currency, $delivery, $vat)" );
	edit_po_form($db, $draft_number);
	break;
// END update PO commment


      case "view_from_search":
	 view_po($db, $draft_number, TRUE);
	 break;
      case "view": 
         view_po($db, $draft_number);
         break;
//view END

      default:
	if (( $cfg["testing"] == "dev" ) && isset($action) && ( $action != "null" )) { echo "$action"; }
	new_po_form($db);
	break;
    }
require("footer.inc.php");
?>
