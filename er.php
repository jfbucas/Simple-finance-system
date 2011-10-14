<?php require("common.inc.php"); ?>

<?php
// ------------------------------------------------------------------------------------------------------------------------
// EDIT Expense Report Line
function edit_line($db, $draft_number, $id, $view_only = true) {
   global $username, $user_role, $cfg, $expenses_types_array, $currencies_types_array, $exchange_rates;

   if (!$er = $db->Execute("SELECT * FROM er WHERE draft_number=$draft_number")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
   $section_id = $er->fields["section"];
   $section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
   $line_item = $db->Execute("SELECT * FROM er_items WHERE id=$id"); 
   $cr_user_id = $er->fields["created_by"];
   if (($username != $cr_user_id) && 
	($username != $section->fields["superapprover"]) && 
	($username != $section->fields["headof"]) && 
	($username != $section->fields["delegate"]) && 
	($username != $section->fields["receptionist"]) && 
	($user_role != $cfg["admin"]) && 
	($user_role != $cfg["finofficer"]) && 
	($user_role != $cfg["finmember"])) {
      finance_log( $db, 'er', $draft_number, 'warn', "Insufficient privilege. You are " . $username .". Only " . $er->fields["created_by"]. " can edit the line of this Expense Report" );
      return FALSE;
     }

	if ($view_only) {
		$disabled = "disabled";
		$action_item = "View";
	}else{
		$disabled = "";
		$action_item = "Edit";
	}
   ?>
  <table class="small" width="100%" border="0" cellspacing="0" cellpadding="1">
    <form action="er.php" method="post" name="form_edit_line_<?echo  $line_item->fields["id"];?>">
    <tr class="box_head">
    <td colspan="8"><b><?php echo $action_item; ?> Item</b></td>
    </tr>
    <tr class="box_bg"> 
      <td align="right" valign="top">Type</td>
      <td colspan="3"><?php 
		echo "<select $disabled name=\"type\">";
		foreach ($expenses_types_array as &$t ) { 
			$select = ( $t == $line_item->fields["type"] ) ? "selected" : "";
			echo "<option value='$t' $select>" . expenses_type2name($t) . "</option>";
		}
                echo "</select>";
		?>
      </td>
    </tr>

    <tr class="box_bg"> 
      <td align="right">Receipt</td>
	<td><input <?php echo $disabled; ?> type="text" name="receipt" size="16"
           value="<?php echo $line_item->fields["receipt"]; ?>">
	</td>
    </tr>
    <tr class="box_bg"> 
      <td align="right" valign="top">Description</td>
      <td colspan="1"> 
        <input <?php echo $disabled; ?> type="text" name="description" size="128"
           value="<?php echo $line_item->fields["description"]; ?>">
      </td>
    </tr>
    <tr class="box_bg"> 
      <td align="right" valign="top">Comment</td>
      <td colspan="1"> 
        <input <?php echo $disabled; ?> type="text" name="comment" size="128"
           value="<?php echo $line_item->fields["comment"]; ?>">
      </td>
    </tr>
    <tr class="box_bg"> 
      <td align="right">Price</td>
	<td><input <?php echo $disabled; ?> type="text" name="price" size="16"
           value="<?php echo $line_item->fields["price"]; ?>">
		Quantity = <input <?php echo $disabled; ?> type="text" name="quantity" size="16" value="<?php echo $line_item->fields["quantity"]; ?>">
	</td>
    </tr>
    <tr class="box_bg"> 
      <td align="right">Currency</td>
	<td><?php
		echo "<select $disabled name=\"currency\">";
		foreach ($currencies_types_array as &$c ) { 
			$select = ( $c == $line_item->fields["currency"] ) ? "selected" : "";
			echo "<option value='$c' $select " .
			"onClick=\"javascript:document.form_edit_line_". $line_item->fields["id"] . ".exchangerate.value=". $exchange_rates[ get_currency_sign( $c, "eurofxref" ) ] . "\";"
			. ">" . get_currency_sign( $c, "text" ) . "</option>";
			//$exchange_rate[ $currencyCode[1] ] = $rate[1];
		}
                echo "</select>";
		?>
		exchange rate = <input <?php echo $disabled; ?> type="text" name="exchangerate" size="16" value="<?php echo $line_item->fields["exchangerate"]; ?>">
	</td>
    </tr>


	<?php if (!$view_only) {?>
		    <tr class="box_bg"> 
		      <td colspan="8">
			 <button type="button" class="button_update" onClick="if (valid_er_line_form(document.form_edit_line_<?echo  $line_item->fields["id"];?>)) { 
			    document.form_edit_line_<?echo  $line_item->fields["id"];?>.submit(); }">Update</button> 
			 <button type="button" class="button_delete" 
			    onClick="if (isConfirmed('Are you sure you want to DELETE this Expense Report Line Item ?')) { window.location='er.php?action=delete_line&id=<?php echo $id; ?>&draft_number=<?php echo $draft_number; ?>'; }">Delete</button>
			 <button type="button" class="button_cancel" onClick="window.location='er.php?action=cancel&draft_number=<?php echo $draft_number; ?>'">Cancel</button>
		      </td>
		    </tr>
		  <input type="hidden" name="action" value="update_line">
		  <input type="hidden" name="id" value="<?php echo $id; ?>">
		  <input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
		  </form>
	<?php } ?>
  </table>
  <script language="JavaScript">
     document.edit_line.unit.focus();
  </script> <?php
//edit_line_item END
} ?>




<?php
// ------------------------------------------------------------------------------------------------------------------------
// EDIT Expense report
//
function edit_er_form($db, $draft_number, $view_only = false) {
   global $username, $user_role, $cfg;
	$forced_view_only = false;

   if ($draft_number == "") {
      finance_log( $db, 'er', $draft_number, 'warn', "You must enter a valid Requisition No.." );
      go_to_er_form();
      return FALSE;
   }
   if (!$er = $db->Execute("SELECT * FROM er WHERE draft_number=$draft_number")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
   if ($er->RecordCount() == 0) {
      finance_log( $db, 'er', $draft_number, 'warn', "Requisition No. $draft_number not found." );
      //er_form($db);
      return FALSE;
   }

   if ( $er->fields["tr_draft_number"] != "" ) {
	   if (!$tr = $db->Execute("SELECT * FROM tr WHERE draft_number=" . $er->fields["tr_draft_number"] )) {
	      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
	      return FALSE;
	   }
	   if ($tr->RecordCount() == 0) {
	      finance_log( $db, 'er', $draft_number, 'warn', "Requisition No. $draft_number not found." );
	      //tr_form($db);
	      return FALSE;
	   }
   }

   $section_id = $er->fields["section"];
   $section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
   $cr_user_id = $er->fields["created_by"];
   $check_user_id = $er->fields["checked_by"];
   $appr_user_id = $er->fields["approved_by"];
   $superapprover_user_id = $section->fields["superapprover"];
   $headof_user_id = $section->fields["headof"];
   $delegate_user_id = $section->fields["delegate"];
   $receptionist_user_id = $section->fields["receptionist"];

     if ($er->fields["status"] == "Closed") { 
	$view_only = true;
	$forced_view_only = true;
     }

   if (($username != $cr_user_id) &&
	($username != $section->fields["superapprover"]) &&
	($username != $section->fields["headof"]) &&
	($username != $section->fields["delegate"]) &&
	($username != $section->fields["receptionist"]) &&
	($user_role != $cfg["registrar"]) &&
	($user_role != $cfg["finofficer"]) &&
	($user_role != $cfg["finmember"]) &&
	($user_role != $cfg["admin"])) {
      finance_log( $db, 'er', $draft_number, 'warn', "Insufficient privilege. You are " . $username .". Only " . $er->fields["created_by"]. " can modify this Requisition " );
      //er_form($db);
      return FALSE;
     }

     $line_items = $db->Execute("SELECT * FROM er_items WHERE draft_number=$draft_number ORDER BY id"); 
     $cr_user = $db->Execute("SELECT fullname FROM users WHERE username='$cr_user_id'");
     $check_user = $db->Execute("SELECT fullname FROM users WHERE username='$check_user_id'");
     $appr_user = $db->Execute("SELECT fullname FROM users WHERE username='$appr_user_id'");
     $superapprover_user = $db->Execute("SELECT fullname FROM users WHERE username='$superapprover_user_id'");
     $headof_user = $db->Execute("SELECT fullname FROM users WHERE username='$headof_user_id'");
     $delegate_user = $db->Execute("SELECT fullname FROM users WHERE username='$delegate_user_id'");
     $receptionist_user = $db->Execute("SELECT fullname FROM users WHERE username='$receptionist_user_id'");

	?>

   <table class="small" align="center" border="0" cellspacing="0" cellpadding="1" width="100%">
       <tr class="row_head"> <td nowrap
	<?php if ($view_only) { ?>
	         View 
	<?php } else { ?>
	         Edit
	<?php } ?>
	Expense Request and Claim for </b> <?php echo $er->fields["description"]; ?> </td>
         <td align="right">Request : <?php echo $draft_number; ?></td>
       </tr>
       <tr class="box_bg">
         <td> <table><tr class="box_bg"><td>Status:</td><td><?php
	echo "<div class=er_" . strtolower($er->fields["status"]) . ">" . $er->fields["status"] . "</div>";
	 echo "</td><td>";

	if ($view_only) {
         if ((($er->fields["status"] == "Open") || ($er->fields["status"] == "Requested")) && (!$forced_view_only)) { ?>
	    <button type="button" class="button_edit" onClick="window.location='er.php?action=edit_er&draft_number=<?php echo $draft_number; ?>'">Edit</button> <?php
         }
         if ( ($er->fields["status"] == "Approved") || ($er->fields["status"] == "Closed")) { ?>
	    <button type="button" class="button_pdf" onClick="window.location='print_er.php?action=print_gd_er&printer=printer3&draft_number=<?php echo $draft_number; ?>'"></button>
	    <button type="button" class="button_email" onClick="window.location='print_er.php?action=print_gd_er&printer=printer4&draft_number=<?php echo $draft_number; ?>'"></button> <?php
	 }
	}else{ ?>
	    <button type="button" class="button_save" onClick="window.location='er.php?action=view_from_search&draft_number=<?php echo $draft_number; ?>'">Ok</button>
	<?php
	    if (($er->fields["status"] == "Open") || ($er->fields["status"] == "Requested" )) { ?>
    		<button type="button" class="button_cancel_request" onClick="if (isConfirmed('Are you sure you want to CANCEL this requisition ?')) { window.location='er.php?action=cancel_er&draft_number=<?php echo $draft_number; ?>'; }">Cancel Expense Report</button> <?php
	    } 
	}

	?> </td></tr></table>
         </td>
         <td align="right">Date: <?php echo display_date($er->fields["date"]); ?></td>
       </tr>
       <tr class="box_bg"> 
         <td><?php
		if ($view_only) {
			echo "Funding: " . $section->fields["name"];
		}else{
   			$sections = $db->Execute("SELECT  name, id  FROM section WHERE enabled='Y' ORDER BY name "); 

			echo '<form action="er.php" method="post" name="form_er_funding">';
			echo 'Funding: ';
			echo $sections->GetMenu("section", $section->fields["name"], FALSE, FALSE, 0, 'onChange="document.form_er_funding.submit();"');
			
			echo '<input type="hidden" name="action" value="update_funding">';
			echo '<input type="hidden" name="draft_number" value="' . $draft_number .'">';
			echo '</form>';
		} ?>
	</td>
         <td align="right">
		<?php 
		echo "Requested By: " . $cr_user->fields["fullname"];
		if ( $check_user->fields["fullname"] != "" )
			echo "<br/>" . "Checked By: " . $check_user->fields["fullname"];
		?>
	</td>
       </tr>
       <tr class="box_bg">
           <td align="left"> <?php
		if ( $er->fields["tr_draft_number"] != "" ) {
			?><a href="tr.php?action=view&draft_number=<?php echo $er->fields["tr_draft_number"]; ?>"><img src="images/emblem-plane.png" border=0>Linked to Travel Request #<?php echo $er->fields["tr_draft_number"]; ?></a><?php
		} else {
			echo "&nbsp;";
		}
	   ?></td>
        <?php 
        if ($er->fields["approved_by"] != ""){
          ?>
            <td align="right">Approved by: <?php echo $appr_user->fields["fullname"]; ?></td>
         <?php } else { ?>
            <td align="right">&nbsp;</td>
         <?php   }  ?>
       </tr>    

       <tr class="box_bg">
         <td colspan="1"> 
	<?php if ($view_only) { ?>

		 <?php
		if ( $er->fields["status"] == "Open") {
		    if (($receptionist_user_id != "" ) &&
		    	($receptionist_user_id != $cfg["sysadmin_email"] ) &&
			($username != $receptionist_user_id)) {  ?>
			Get it checked by coordinator : 
		    		<button type="button" class="button_check" onClick="window.location='er.php?action=get_er_checked&draft_number=<?php echo $draft_number; ?>'"><? echo $receptionist_user->fields["fullname"]; ?></button><br/><?php
			}
		    if ((($username != $headof_user_id) && ($username != $delegate_user_id)) ||
	   	        (($username == $headof_user_id) && ($cr_user_id == $headof_user_id)) ||
			(($username == $delegate_user_id) && ($cr_user_id == $delegate_user_id))) { ?>
			Get approval from :  <?php
			if (( ($cr_user_id == $headof_user_id)) ||
			    ( ($cr_user_id == $delegate_user_id))) { 
			//if ((($username == $headof_user_id) && ($cr_user_id == $headof_user_id)) ||
			//    (($username == $delegate_user_id) && ($cr_user_id == $delegate_user_id))) { 
		    		?><button type="button" class="button_approval" onClick="window.location='er.php?action=get_er_approval&draft_number=<?php echo $draft_number; ?>'"><? echo $superapprover_user->fields["fullname"] ?></button><?php
			}else{ 
				if (($delegate_user_id != "" ) && ($delegate_user_id != $cfg["sysadmin_email"] )) { ?>
					<button type="button" class="button_approval" onClick="window.location='er.php?action=get_er_approval&draft_number=<?php echo $draft_number; ?>'">All</button>
					( <button type="button" class="button_approval" onClick="window.location='er.php?action=get_er_approval&draft_number=<?php echo $draft_number; ?>&head_of_section_only=1'"><?php echo $headof_user->fields["fullname"]?></button> |
					<button type="button" class="button_approval" onClick="window.location='er.php?action=get_er_approval&draft_number=<?php echo $draft_number; ?>&delegate_only=1'"><?php echo $delegate_user->fields["fullname"] ?></button> )
				  <?php } else { ?>
					<button type="button" class="button_approval" onClick="window.location='er.php?action=get_er_approval&draft_number=<?php echo $draft_number; ?>'"><? echo $headof_user->fields["fullname"] ?></button>
				  <?php } 
			}
		    }
		}
		//If user is  super-approver or  head-of-section but request is not from himself
		if ( $er->fields["status"] == "Open" )  {
			if (($username == $superapprover_user_id) ||
	   		   (($username == $headof_user_id) && ($cr_user_id != $headof_user_id)) ||
			   (($username == $delegate_user_id) && ($cr_user_id != $delegate_user_id)) ||
			   (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"]))) { ?>
	    			<button type="button" class="button_approve"
				    onClick="if (isConfirmed('Are you sure you want to Approve this Open Expense Report ?')) { window.location='er.php?action=approve_er&draft_number=<?php echo $draft_number; ?>'; }">Approve</button>
			<?php }
		}
		if ( $er->fields["status"] == "Requested" ) {
			if (($username == $superapprover_user_id) ||
	   		   (($username == $headof_user_id) && ($cr_user_id != $headof_user_id)) ||
			   (($username == $delegate_user_id) && ($cr_user_id != $delegate_user_id)) || 
			   (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"]))) { ?>
	    			<button type="button" class="button_approve" onClick="window.location='er.php?action=approve_er&draft_number=<?php echo $draft_number; ?>'">Approve</button>
	    			<button type="button" class="button_reject" onClick="window.location='er.php?action=reject_er&draft_number=<?php echo $draft_number; ?>'">Reject</button>
			<?php }
		}
		if ( $er->fields["status"] == "Approved") { 
			if  (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) { ?>
	    			<button type="button" class="button_reject" onClick="window.location='er.php?action=reject_er&draft_number=<?php echo $draft_number; ?>'">Reject</button>
    				<button type="button" class="button_cancel_request" onClick="if (isConfirmed('Are you sure you want to CANCEL this requisition ?')) { window.location='er.php?action=cancel_er&draft_number=<?php echo $draft_number; ?>'; }">Cancel Expense Report</button>
				<button type="button" class="button_close" onClick="window.location='er.php?action=close_er&draft_number=<?php echo $draft_number; ?>'">Close</button><?php
			}
		}
		if ( $er->fields["status"] == "Closed") { 
		}
		?>

	  <?php  } else { // view only ?> 
		&nbsp;
	  <?php  } ?> 

         </td>
	 <td align=center> &nbsp; </td>
       </tr>
     </table></br>
   <table class="small" cellspacing="0" cellpadding="1" width="100%">
    <tr class="row_head">
      <td align="center" width=1%><b>Receipt</b></td>
      <td align="center"><b>Type</b></td>
      <td align="left"><b>Description</b></td>
      <td align="right"><b>Price</b></td>
      <td align="center"><b>Comment</b></td>
      <td align="center"><b>Prepaid</b></td> 
   </tr>
    <?php
      $total_prepaid = 0;
      $i = 1;
      $line_items->Move(0);
      while (!$line_items->EOF) {
         if ($i % 2 == 0) {
            echo "<tr class=\"row_even\" ";
         } else {
            echo "<tr class=\"row_odd\" ";
         }
         echo ">";
         echo "<td onclick=\"showtr('" . $line_items->fields["id"] . "');\" align=\"center\">" . $line_items->fields["receipt"] . "</td>";
         echo "<td onclick=\"showtr('" . $line_items->fields["id"] . "');\" align=\"center\">" . expenses_type2name( $line_items->fields["type"] ). "</td>";
         echo "<td onclick=\"showtr('" . $line_items->fields["id"] . "');\">" . substr($line_items->fields["description"], 0, 128);
	 if ( strlen($line_items->fields["description"]) > 128 ) { echo " ..."; }
	 echo "</td>";
         echo "<td onclick=\"showtr('" . $line_items->fields["id"] . "');\" align=\"right\">";
	 printf("%01.2f". "</td>", $line_items->fields["price"] * $line_items->fields["quantity"] * $line_items->fields["exchangerate"]);
         echo "<td onclick=\"showtr('" . $line_items->fields["id"] . "');\" align=\"center\">" . substr($line_items->fields["comment"], 0, 128);
	 if ( strlen($line_items->fields["comment"]) > 128 ) { echo " ..."; }
	 echo "</td>";
           if ($line_items->fields["prepaid"] == "Y") {
	 	$total_prepaid += $line_items->fields["price"] * $line_items->fields["quantity"] * $line_items->fields["exchangerate"];
		if (!$view_only) {
		      echo "<td title=\"Click on the Y to mark it as prepaid\" align=\"center\" onclick='"
			   ."location.href=\"er.php?action=unrecv_line&draft_number=$draft_number&id=" . $line_items->fields["id"] . "\"'>"
			   . "<img src=\"images/yes.png\" border=\"0\" alt=\"Prepaid\"></a></td>";
		} else {
			echo "<td align=center><img src=\"images/yes.png\" border=\"0\" alt=\"Prepaid\"></td>";
		}
           } else {
		if (!$view_only) {
                   //."<a href=\"er.php?action=confirm_recv_line&draft_number=$draft_number&id="
		      echo "<td title=\"Click on the N to mark it as not prepaid\" align=\"center\" class=\"\" onclick='"
			   ."location.href=\"er.php?action=recv_line&draft_number=$draft_number&id=" . $line_items->fields["id"] . "\"'>"
			   . "<img src=\"images/no.png\" border=\"0\" alt=\"Not Prepaid\"></a></td>";
       		} else {
			echo "<td align=center><img src=\"images/no.png\" border=\"0\" alt=\"Not Prepaid\"></td>";
		}
	  }
         echo "</tr>";

	$view_only_line = true;
	if (!$view_only) {
		 if ( ( $er->fields["status"] == "Open" ) 
			|| ($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) {
				$view_only_line = false;
		 }
	 }
	echo "<tr id='" . $line_items->fields["id"] . "' style=\"display:none;\">";
	//echo "</tr> <tr id='" . $line_items->fields["id"] . "'>";
	echo "<td colspan=8>";
	echo "<div>";
	edit_line($db, $draft_number, $line_items->fields["id"], $view_only_line);
	echo "</div>";
	echo "</td></tr>";

         $i++;
         $line_items->MoveNext();
      }
	?>

	<tr class="row_head"><?php
		$total_reported = get_er_total( $draft_number);
		printf("<td align=\"right\" colspan=\"3\"><b>Total Reported </b></td><td align=\"right\"><b>%01.2f %s</b></td>", $total_reported, get_currency_sign('e', 'html') ); ?>
		<td colspan="2">&nbsp;</td>
	</tr>
	<?php
	   $advance = 0;
	   if ( $er->fields["tr_draft_number"] != "" ) {
		$advance = $tr->fields["advance_transfered"];
		?><tr class="row_head">
 	        <?php printf("<td align=\"right\" colspan=\"3\"><b>Advance Transfered </b></td><td align=\"right\"><b>%01.2f %s</b></td>", $advance, get_currency_sign('e', 'html') ); ?>
                <td colspan="2">&nbsp;</td>
		</tr><?php
	   }
	?>
	<?php
	   if ( $total_prepaid > 0 ) {
		?><tr class="row_head">
 	        <?php printf("<td align=\"right\" colspan=\"3\"><b>Prepaid </b></td><td align=\"right\"><b>%01.2f %s</b></td>", $total_prepaid, get_currency_sign('e', 'html') ); ?>
                <td colspan="2">&nbsp;</td>
		</tr><?php
	   }
	?><tr class="row_head"><?php
	$balance = $total_reported - $advance - $total_prepaid;
	printf("<td align=\"right\" colspan=\"3\"><b>Balance </b></td><td align=\"right\"><b>%01.2f %s</b></td>", $balance, get_currency_sign('e', 'html') ); ?>
	<td colspan="2">&nbsp;</td>
	</tr>
  </table>
  </br> 
  </br> 
<?php

	//if (!$view_only) {
		if ( $er->fields["tr_draft_number"] != "" ) {
			$maximum_budget = get_tr_maximum_budget( $er->fields["tr_draft_number"] );
			$total = get_tr_total( $er->fields["tr_draft_number"] );

			if ( $maximum_budget > 0 ) {
				$max_allowed = $maximum_budget;
				$over_message = "The current claim is higher than the maximum budget allowed by Head of Section for the travel request #" . $er->fields["tr_draft_number"] . ".";
			} else {
				$max_allowed = $total;
				$over_message = "The current claim is higher than the previsionnal budget of the travel request #" . $er->fields["tr_draft_number"] . ".";
			}

			if ( $total_reported > $max_allowed ) {

				?><table class="small" cellspacing="0" cellpadding="1" width="100%">
				<form action="er.php" method="post" name="form_edit_overexpense">
				<tr class="box_head">
					<td align="left" ><b>Overspend Explanation:</b></td>
				</tr>
				<tr class="box_bg">
				<td>
					<?php
					echo "<textarea rows=\"5\"  name=\"overexpense\" wrap=\"virtual\"";
					if ($er->fields["status"] == "Open") {
						echo " class=\"ER_overexpense\"";
					}else{
						echo " readonly class=\"PO_comment_readonly\"";
					}
					echo ">\n"; 
					echo $er->fields["overexpense"];
					echo "</textarea>";
				 
					if ($er->fields["status"] == "Open") 
					if ($er->fields["overexpense"] == "") { ?>
						<button type="button" class="button_enter" onClick="document.form_edit_overexpense.submit();">Enter</button><?php
					} else { ?>
						<button type="button" class="button_update" onClick="document.form_edit_overexpense.submit();">Update</button><?php
					} ?>
				</td>
				</tr>
				<tr class="box_bg"><td class="overmessage"><?php echo $over_message; ?></td></tr>
				<input type="hidden" name="view_only" value="<? echo $view_only; ?>">
				<input type="hidden" name="action" value="update_overexpense">
				<input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
				</form>
				</table></br><?php
			}
		}
	//}

	if (!$view_only) 
         if ($er->fields["status"] == "Open")  {
	   new_line_item($draft_number); 
  		echo "</br>   </br> ";
	 }
	
	if ( ($user_role == $cfg["admin"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"])) {
		finance_log_show( $db, 'er', $draft_number );
	}
//edit_er END
} ?>

<?php
// ------------------------------------------------------------------------------------------------------------------------
// EDIT Select Expense Report
//
function go_to_er_form() { ?>
   <table class="default" border="0" cellspacing="0" cellpadding="1" align="center">
   <form action="er.php" method="post" name="go_to_to">
      <tr class="row_head"> 
         <td align="center" colspan="2" nowrap><b>Update Expense Report No.</b></td>
      </tr>
      <tr class="box_bg"> 
         <td align="right">Expense Report and Claim No.:</td>
         <td> 
            <input type="text" name="draft_number" size="12">
            <input type="submit" class="button_enter" value="Enter">
         </td>
      </tr>
   <input type="hidden" name="action" value="edit_er">
   </form>
   </table>
   <script language="JavaScript">
      document.go_to_er.draft_number.focus();
   </script> <?php
//go_to_er END
} ?>


<?php
// ------------------------------------------------------------------------------------------------------------------------
// EDIT Expense Report Form
//
function new_line_item($draft_number) { 
  global $expenses_types_array, $currencies_types_array, $exchange_rates, $cfg;
	?>
  <table class="small" width="100%" border="0" cellspacing="0" cellpadding="1">
  <form action="er.php" method="post" name="new_line_item_form">
    <tr class="row_head"> 
      <td colspan="8"><b>Add New Item to the Claim</b></td>
    </tr>
    <tr class="box_bg">       <td colspan=2 align="right">&nbsp;</td>    </tr>
    <tr class="box_bg">       <td colspan=1 align="right">Rates: </td><td align=left>
	<?php echo $cfg["subsistence_rates_links"]; ?>
	</td>    </tr>
    <tr class="box_bg"> 
      <td align="right" valign="top">Type</td>
      <td colspan="3"><?php 
		echo "<select name=\"type\">";
		$select="selected";
		foreach ($expenses_types_array as &$t ) { 
			echo "<option value='$t' $select>" . expenses_type2name($t) . "</option>";
			$select = "";
		}
                echo "</select>";
		?>
      </td>
    </tr>

    <tr class="box_bg"> 
      <td align="right">Receipt</td>
	<td><input type="text" name="receipt" size="16"> </td>
    </tr>
    <tr class="box_bg"> 
      <td align="right" valign="top">Description</td>
      <td colspan="1"> 
        <input type="text" name="description" size="128">
      </td>
    </tr>
    <tr class="box_bg"> 
      <td align="right" valign="top">Comment</td>
      <td colspan="1"> 
        <input type="text" name="comment" size="128">
      </td>
    </tr>
    <tr class="box_bg"> 
      <td align="right">Price</td>
	<td><input type="text" name="price" size="16">
		Quantity = <input type="text" name="quantity" size="16" value="1">
	 </td>
    </tr>
    <tr class="box_bg"> 
      <td align="right">Currency</td>
	<td><?php
		echo "<select name=\"currency\">";
		$select="selected";
		foreach ($currencies_types_array as &$c ) { 
			echo "<option value='$c' $select " .
			"onClick=\"javascript:document.new_line_item_form.exchangerate.value=". $exchange_rates[ get_currency_sign( $c, "eurofxref" ) ] . "\";"
			. ">" . get_currency_sign( $c, "text" ) . "</option>";
			$select = "";
		}
                echo "</select>";
		?>
		exchange rate = <input type="text" name="exchangerate" size="16" value="1">
	</td>
    </tr>
   <tr class="box_bg"> 
      <td align="right">Prepaid</td>
	<td> <select name="prepaid"><option value="N" selected>No</option><option value="Y">Yes</option></select> Has the Finance office already paid for this item ?</td>

   </tr>
    <tr class="box_bg">       <td colspan=2 align="right">&nbsp;</td>    </tr>
    <tr class="box_bg"> 
      <td align="right"></td>
      <td colspan="1">
   	<button type="button" class="button_add" onClick="if (valid_er_line_form(document.new_line_item_form)) { document.new_line_item_form.submit(); }">Add</button>
	<button type="button" class="button_cancel" onClick="window.location='er.php?action=cancel&draft_number=<?php echo $draft_number; ?>'">Cancel</button>
      </td>
    </tr>
  <input type="hidden" name="action" value="enter_new_line">
  <input type="hidden" name="draft_number" value="<?php echo $draft_number; ?>">
  </form>
  </table>
  <script language="JavaScript">
     document.new_line_item_form.description.focus();
  </script> <?php
//new_line_item END
} ?>



<?php
// ------------------------------------------------------------------------------------------------------------------------
// New Expense Report Form
//
function new_er_form($db) {
   global $cfg, $username, $destination, $purpose, $section;
   
    if ( isset($section) ) {
      if (! $usersection_ids = $db->Execute("SELECT s.name FROM section as s WHERE s.id = $section") ) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";         break;      }
    } else {

     if (! $usersection_ids = $db->Execute("SELECT s.name FROM section as s, `users-sections` as us WHERE us.username='$username' and us.section_id = s.id") ) {
          echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";         break;      }
   }
     $usersection_id = $usersection_ids->fields[ "name" ];

   $sections = $db->Execute("SELECT name, id FROM section WHERE enabled='Y' ORDER BY name"); ?>

   <table class="default" border="0" align="center" cellpadding="10" width="80%"><tr><td>
  	 <h2 align="center"> New Expense Report and Claim </h2> 
   </td></tr></table>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
   <form action="er.php" method="post" name="new_er">
       <tr class="box_head"> 
         <td colspan=2>Expense Report and Claim Details</td>

       </tr>
   <tr class="box_bg">     <td colspan=2> &nbsp; </td>      </tr>
    <tr class="box_bg"> 
      <td align="right" width="30%" valign="top">Description : </td>
      <td>
        <input type="text" name="description" size="100" value="<?php echo "$description"; ?>">
      </td>
    </tr>

       <tr class="box_bg">     <td colspan=2> &nbsp; </td>      </tr><?php
   	$is_receptionist = $db->Execute("SELECT receptionist FROM section WHERE receptionist='$username'");
	if ($is_receptionist->RecordCount() > 0) {
		$userslist = $db->Execute("SELECT fullname, username FROM users ORDER BY fullname"); 
		echo '<tr class="box_bg">     <td align="right">On the behalf of : </td><td>';
		echo $userslist->GetMenu("onbehalfof", "", FALSE);      		  
		echo '</td>      </tr>';
		echo '<tr class="box_bg">     <td colspan=2> &nbsp; </td>      </tr>';
	} ?>

       <tr class="box_bg">
         <td align="right">Source of funding : </td><td> 
	 	<?php echo $sections->GetMenu("section", "$usersection_id", FALSE, FALSE, 0); ?>
         </td>
       </tr>
	<tr class="box_bg">     <td colspan=2> &nbsp; </td>      </tr>
       <tr class="box_bg"> 
         <td align="center" colspan=2 > 
   	   <button type="button" class="button_next" onClick="document.new_er.submit();">Next</button>
         </td>
       </tr>
   	<tr class="box_bg">     <td colspan=2> &nbsp; </td>      </tr>
     <input type="hidden" name="date" value="<?php echo date($cfg["date_arg"]); ?>">
     <input type="hidden" name="action" value="create_er">
   </form>
   </table>
   <script language="JavaScript">
      document.new_er.date.focus();
   </script> <?php
} ?>



<?php
// ------------------------------------------------------------------------------------------------------------------------
// ACTION SECTION
//


   er_buttons(false); 

   $action = strtolower($action);
   switch ($action) {
      case "approve_er":
        $er = $db->Execute("SELECT * FROM er WHERE draft_number=$draft_number");

	if ( ( $er->fields["status"] == "Open" ) || ( $er->fields["status"] == "Requested" ) ) {
		$cr_user_id = $er->fields["created_by"];
		$section_id = $er->fields["section"];
		$section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
		if (($username == $section->fields["superapprover"]) ||
		   (($username == $section->fields["headof"]) && ($cr_user_id != $section->fields["headof"])) ||
		   (($username == $section->fields["delegate"]) && ($cr_user_id != $section->fields["delegate"])) ||
		   (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"]))) { 
		    if (!$db->Execute("UPDATE er SET status='Approved', approved_by='$username' WHERE draft_number='$draft_number'")) {
		       echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		       break;
		    }
		    $cr_username_id= $er->fields["created_by"];
		    $approver_data = $db->Execute("SELECT * FROM users WHERE username='$username'");
		    $approver_fullname = $approver_data->fields["fullname"];
		 // Email to Finance and the cr_user
		 $mail_headers = 'From: ' . $cfg["finance_email"] . "\r\n" .
		    'Reply-To: ' . $cfg["finance_email"] . "\r\n" .
		    'To: ' . $cfg["finance_email"] . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
		 $email_body="Expense Report and Claim " . $draft_number . " has been Approved by " . $approver_fullname . ". \r\n\r\n" . er_description($draft_number);
		    do_mail($cr_username_id,
		      "Expense Report and Claim $draft_number : Approved notification",
		      $email_body,
		      $mail_headers);
		    finance_log( $db, 'er', $draft_number, 'info', "Approved by $username", "log" );
		    finance_log( $db, 'er', $draft_number, 'info', "Notification has been sent to $cr_username_id." );
		 } else {
		    finance_log( $db, 'er', $draft_number, 'warn', "You are not allowed to approve this request." );
		 }
	 } else {
	    finance_log( $db, 'er', $draft_number, 'warn', "Cannot approve : status is " . $er->fields["status"] );
	 }
         edit_er_form($db, $draft_number, true);
         break;
//approve END

      case "cancel":
         finance_log( $db, 'er', $draft_number, 'warn', "Operation canceled by $username", "log" );
         finance_log( $db, 'er', $draft_number, 'warn', "Expense Report operation canceled." );
         edit_er_form($db, $draft_number);
         break;
//cancel END
      case "cancel_er":
         if (!$er = $db->Execute("SELECT * FROM er WHERE draft_number=$draft_number")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         } 
        $section_id = $er->fields["section"];
        $section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
  	 if (($username == $er->fields["created_by"]) || ($username == $section->fields["receptionist"]) || ($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) {
           if (!$er = $db->Execute("SELECT status FROM er WHERE draft_number=$draft_number")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }
            if ($er->fields["status"] != "Closed") {
               if (!$db->Execute("UPDATE er SET status='Canceled' WHERE draft_number=$draft_number")) {
                  echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
                  break;
               }
                 finance_log( $db, 'er', $draft_number, 'info', "Expense Report and Claim canceled by $username", "log" );
                 finance_log( $db, 'er', $draft_number, 'info', "Expense Report and Claim is now canceled." );
            }
            edit_er_form($db, $draft_number, true);
	  }else{
            finance_log( $db, 'er', $draft_number, 'warn', "Insufficient privilege. Only " . $er->fields["created_by"]. " can cancel this Requisition." );
	  }

         break;
//cancel_er END
      case "close_er":
         if (!$er = $db->Execute("SELECT * FROM er WHERE draft_number=$draft_number")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            } 
  	 if (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) {
           /*if (!$er = $db->Execute("SELECT status FROM er WHERE draft_number=$draft_number")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }*/
            if ($er->fields["status"] == "Approved") {
               if (!$db->Execute("UPDATE er SET status='Closed' WHERE draft_number=$draft_number")) {
                  echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
                  break;
               }
		 $mail_headers = 'From: ' . $cfg["finance_email"] . "\r\n" .
       	          'Reply-To: ' . $cfg["finance_email"] . "\r\n" .
                  'X-Mailer: PHP/' . phpversion();
                 $email_body="Expense Report and Claim " .$draft_number . " has been closed.\r\n" ;
                 $email_body= $email_body . "Please visit: " . $cfg["baseurl"] . "\r\n\r\n" . er_description($draft_number);
                 do_mail($er->fields["created_by"], "Expense Report and Claim $draft_number : Closed", $email_body,  $mail_headers);
                 finance_log( $db, 'er', $draft_number, 'info', "Notification has been sent to ". $er->fields["created_by"]. "." );
                 finance_log( $db, 'er', $draft_number, 'info', "ER has been closed by $username", "log" );
            }
            edit_er_form($db, $draft_number, true);
	  }else{
            finance_log( $db, 'er', $draft_number, 'warn', "Insufficient privilege. Only " . $er->fields["created_by"]. " can close this Requisition " );
	  }
         break;
//close_er END
      case "create_er":
         if (!$date = valid_date($date)) {
            finance_log( $db, 'er', $draft_number, 'warn', "$date : Invalid date format." );
            new_er_form($db);
            break;
         }
	 if ( isset($onbehalfof) && ($onbehalfof != "" ) && ( $onbehalfof != $cfg["sysadmin_email"] ) ) {
         	$cr_user_id = $onbehalfof;
	 }else{
         	$cr_user_id = $username;
	 }
         $draft_number = $db->GenID("er_seq");
         $query = "INSERT INTO er (draft_number, date, created_by, section, description)"
                . " VALUES ('$draft_number', '$date', '$cr_user_id', '$section', ". $db->QMagic($description) . " )";
         if (!$db->Execute($query)) {
		echo $query;
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         finance_log( $db, 'er', $draft_number, 'info', "ER created by $username.", "log" ); 
	 ?>
         <script language="JavaScript">
            window.location="er.php?action=edit_er&draft_number=<?php echo $draft_number; ?>";
         </script> <?php
         break;
      //create_er END

      case "delete_line":
            if (!$db->Execute("DELETE FROM er_items WHERE id=$id")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }
	    finance_log( $db, 'er', $draft_number, 'warn', "Line removed by $username", "log" );
            edit_er_form($db, $draft_number);
         break;
//delete_line END
      case "edit_er":
              edit_er_form($db, $draft_number);
         break;
//edit_po END

      case "enter_new_line":  	
            $price = sprintf("%01.2f", $price);
            $id = $db->GenID("er_items_seq");
            $query = "INSERT INTO er_items (id, draft_number, receipt, type, price, quantity, currency, exchangerate, description, comment, prepaid)"
                . " VALUES ('$id', '$draft_number', '$receipt', '$type', '$price', '$quantity', '$currency', '$exchangerate', " . $db->QMagic($description) . ", " . $db->QMagic($comment) . ", '$prepaid' )";
            if (!$db->Execute($query)) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
             }
	    finance_log( $db, 'er', $draft_number, 'info', "New line entered by $username VALUES ('$receipt', '$type', '$price', '$quantity', '$currency', '$exchangerate', " . $db->QMagic($description) . ", " . $db->QMagic($comment) . ", '$prepaid' )", "log" );
            edit_er_form($db, $draft_number);
            break;
           //enter_new_line END
          
      case "get_er_approval":
         if (!$er = $db->Execute("SELECT * FROM er WHERE draft_number=$draft_number")) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           return FALSE;
         }
	
	if ( $er->fields["status"] == "Open" ) {

		$cr_user_id = $er->fields["created_by"];
		$section_id = $er->fields["section"];
		$section = $db->Execute("SELECT * FROM section WHERE id=$section_id");

		 if (!$db->Execute("UPDATE er SET status='Requested' WHERE draft_number='$draft_number'")) {
		      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		     break;
		 }
		if ( $username == $section->fields["receptionist"] ) {

		 if (!$db->Execute("UPDATE er SET checked_by='$username' WHERE draft_number='$draft_number'")) {
		      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		     break;
		 }
		}
		
		if (($cr_user_id == $section->fields["headof"]) || ($cr_user_id == $section->fields["delegate"])) { 
			$approver = $section->fields["superapprover"];
			unset( $delegate );
		} else {
			$approver = $section->fields["headof"];
			$delegate = $section->fields["delegate"];
		}

		if (!$requisitioner_data = $db->Execute("SELECT * FROM users WHERE username='$cr_user_id'")) {
		    echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		       break;
		    }
		 $mail_headers = 'From: ' . $cfg["finance_email"] . "\r\n" .
		    'Reply-To: ' . $cfg["finance_email"] . "\r\n" .
		    'To: ' . $cr_user_id . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
		 $email_body="Expense Report and Claim " .$draft_number . " has been submitted by ".  $requisitioner_data->fields["fullname"] . " for your approval." . "\r\n" ;
		 $email_body= $email_body . "Please visit: " . $cfg["baseurl"] . "\r\n\r\n" . er_description($draft_number);
		 if (! isset($delegate_only) ) {
		      do_mail($approver,
		      "Expense Report and Claim $draft_number : Approval request sent to Head of Section",
		      $email_body,
		      $mail_headers);
		      if (!$approver_data = $db->Execute("SELECT * FROM users WHERE username='$approver'")) {
		       echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		       break;
		      }
		      finance_log( $db, 'er', $draft_number, 'info', "Your request has been forwarded to " . $approver_data->fields["fullname"] . " (" . $approver .  ")." );
		 }
		 if (( $delegate != "" ) && ( $delegate != $cfg["sysadmin_email"] ) && ( !isset($head_of_section_only))) {
		       do_mail($delegate,
		       "Expense Report and Claim $draft_number : Approval request sent to Delegate",
		       $email_body,
		       $mail_headers);
		      if (!$delegate_data = $db->Execute("SELECT * FROM users WHERE username='$delegate'")) {
			echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
			break;
		      }
		      finance_log( $db, 'er', $draft_number, 'info', "Your request has been forwarded to " . $delegate_data->fields["fullname"] . " (" . $delegate .  ")." );
		 }
		 finance_log( $db, 'er', $draft_number, 'info', "Email was : " . $email_body, "log" );
		 finance_log( $db, 'er', $draft_number, 'info', "You will be notifed by E-Mail when this Requisition has been approved.", "show" );
	} else {
	    finance_log( $db, 'er', $draft_number, 'warn', "Cannot get approval : status is " . $er->fields["status"] );
	}
		
         edit_er_form($db, $draft_number, true);
         break;
//get_er_approval END
      case "get_er_checked":
         if (!$er = $db->Execute("SELECT * FROM er WHERE draft_number=$draft_number")) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           return FALSE;
         }
         /*if (!$db->Execute("UPDATE er SET status='Requested' WHERE draft_number='$draft_number'")) {
              echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
             break;
         }*/
        $cr_user_id = $er->fields["created_by"];
        $section_id = $er->fields["section"];
        $section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
	$receptionist = $section->fields["receptionist"];
	
        if (!$requisitioner_data = $db->Execute("SELECT * FROM users WHERE username='$cr_user_id'")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
            }
	 $mail_headers = 'From: ' . $cfg["finance_email"] . "\r\n" .
            'Reply-To: ' . $cfg["finance_email"] . "\r\n" .
            'To: ' . $cr_user_id . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
         $email_body="Expense Report and Claim " .$draft_number . " is about to be submitted by ".  $requisitioner_data->fields["fullname"] . ". Please check if everything is in order." . "\r\n" ;
         $email_body= $email_body . "Please visit: " . $cfg["baseurl"] . "\r\n\r\n" . er_description($draft_number);
         do_mail($receptionist,
              "Expense Report and Claim $draft_number : Check request sent to Coordinator of Section",
              $email_body,
              $mail_headers);
              if (!$receptionist_data = $db->Execute("SELECT * FROM users WHERE username='$receptionist'")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
              }
              finance_log( $db, 'er', $draft_number, 'info', "Your request has been forwarded to " . $receptionist_data->fields["fullname"] . " (" . $receptionist .  ")." );
	 
         finance_log( $db, 'er', $draft_number, 'info', "You will be notifed by E-Mail when this Requisition has been checked.", "show" );
         edit_er_form($db, $draft_number, true);
         break;
//get_er_checked END
     case "new_er":
            new_er_form($db);
            break;
//new_er END
   case "paged_query":
      $summary = paged_query($db, $page);
      er_table($summary, $db);
      break;
//paged_query END
   case "reject_er":
        $er = $db->Execute("SELECT * FROM er WHERE draft_number=$draft_number");
	if ( ( $er->fields["status"] == "Requested" ) || ( $er->fields["status"] == "Approved" ) ) {
		$section_id = $er->fields["section"];
		$section = $db->Execute("SELECT * FROM section WHERE id=$section_id");
		 if (($username == $section->fields["superapprover"]) || ($username == $section->fields["headof"]) || ($username == $section->fields["delegate"]) ||
		     ($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) { 
		    if (!$db->Execute("UPDATE er SET status='Open', approved_by='' WHERE draft_number='$draft_number'")) {
		       echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		       break;
		    }
		    $cr_username_id= $er->fields["created_by"];
		    $approver_data = $db->Execute("SELECT * FROM users WHERE username='$username'");
		    $approver_fullname = $approver_data->fields["fullname"];
		 // Email to Finance and the cr_user
		 $mail_headers = 'From: ' . $cfg["finance_email"] . "\r\n" .
		    'Reply-To: ' . $cfg["finance_email"] . "\r\n" .
		    'To: ' . $cfg["finance_email"] . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
		 $email_body="Expense Report and Claim " . $draft_number . " has been REJECTED by " . $approver_fullname . ". \r\n\r\n" . er_description($draft_number);
		    do_mail($cr_username_id,
		      "Expense Report and Claim $draft_number : Rejected notification",
		      $email_body,
		      $mail_headers);
		    finance_log( $db, 'er', $draft_number, 'warn', "Rejected by $username", "log" );
		    finance_log( $db, 'er', $draft_number, 'info', "Notification has been sent to $cr_username_id." );
		 } else {
		    finance_log( $db, 'er', $draft_number, 'warn', "You are not allowed to reject this request." );
		}
	 } else {
	    finance_log( $db, 'tr', $draft_number, 'warn', "Cannot reject : status is " . $er->fields["status"] );
         }
         edit_er_form($db, $draft_number, true);
         break;
//approve END
  case "recv_line":
         if (!$db->Execute("UPDATE er_items SET prepaid='Y' WHERE id=$id")) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
          }
       edit_er_form($db, $draft_number);
         break;
  case "unrecv_line":
         if (!$db->Execute("UPDATE er_items SET prepaid='N' WHERE id=$id")) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
          }
       edit_er_form($db, $draft_number);
         break;

   case "update_line":
      $price = sprintf("%01.2f", $price);

      $query = "UPDATE er_items SET "
 	. "receipt='$receipt', type='$type', price='$price', quantity='$quantity', description=" . $db->QMagic($description) . ", comment=" .$db->QMagic($comment) . ", currency='$currency', exchangerate='$exchangerate'" 
        . " WHERE id=$id";
      if (!$db->Execute($query)) {
         echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
         }
      finance_log( $db, 'er', $draft_number, 'info', "Line updated by $username VALUES ('$receipt', '$type', '$price', '$quantity', '$currency', '$exchangerate', " . $db->QMagic($description) . ", " . $db->QMagic($comment) . ", '$prepaid' )", "log" );
      edit_er_form($db, $draft_number);
      break;
//update_line END
// case update ER overexpense
      case "update_overexpense":  	
	if (!$db->Execute("UPDATE er SET overexpense='$overexpense' WHERE draft_number='$draft_number'")) {
		echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		break;
	}
        finance_log( $db, 'er', $draft_number, 'info', "Over Expense explanation Updated.", "show");
        finance_log( $db, 'er', $draft_number, 'info', "Over Expense explanation Updated by $username.", "log");
	edit_er_form($db, $draft_number, $view_only);
	break;
// case update funding source ER
      case "update_funding":
	if ( isset($section) ) {
		if (!$db->Execute("UPDATE er SET  section='$section' WHERE draft_number='$draft_number'") ) {
			echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
			break;
		}
	}
        finance_log( $db, 'er', $draft_number, 'info', "Funding source Updated.", "show" );
        finance_log( $db, 'er', $draft_number, 'info', "Funding source Updated by $username to $section.", "log" );
	edit_er_form($db, $draft_number, false);
	break;


      case "view_from_search":
	 edit_er_form($db, $draft_number, TRUE);
	 break;
      case "view": 
         edit_er_form($db, $draft_number, TRUE);
         break;
//view END
      default:
	if (( $cfg["testing"] == "dev" ) && isset($action) && ( $action != "null" )) { echo "$action"; }
	new_er_form($db);
	break;
   }
require("footer.inc.php");
?>
