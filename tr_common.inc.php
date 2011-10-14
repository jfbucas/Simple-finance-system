<?php


function show_tr($db, $context = "search_tr.php" ) {
   global $action, $username, $user_role, $status, $section_id, $vendor_id,
          $from_date, $to_date, $order_by, $order, $created_by, $cfg;
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
   if ($order != "ASC") {
      $order = "DESC";
   }


   $_SESSION["search_query"] =
      "SELECT tr.draft_number, tr.date, tr.status, section.name, tr.created_by, tr.section, tr.destination, tr.depart_date, tr.return_date, tr.advance_requested, tr.advance_transfered"
      . " FROM tr, section"
      . " WHERE tr.section=section.id"
      . " AND tr.date>='$from_date'"
      . " AND tr.date<='$to_date'";
   $section_priviledge="( section.superapprover LIKE '$username' OR section.headof LIKE '$username' OR section.delegate LIKE '$username' OR section.receptionist LIKE '$username' )";
   switch ( $context ) {
   	case "search_tr.php":
	   if ($created_by == "anybody") 
	   	$created_by = $cfg[ "sysadmin_email" ];

	   if ($created_by == "anybody") {
	      $created_by_query = "%";
	      //if (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) {
	      if (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"])) {
	         $_SESSION["search_query"] .= " AND ( tr.created_by LIKE '$created_by_query' )";
	      }else{
	         $_SESSION["search_query"] .= " AND ( tr.created_by LIKE '$created_by_query' AND $section_priviledge )";
	      }
	   } else if ( ( $created_by == "") || ( $created_by == $cfg[ "sysadmin_email" ] ) ) {
	      $created_by_query= $username;
	      $_SESSION["search_query"] .= " AND ( tr.created_by LIKE '$created_by_query' OR  $section_priviledge )";
	   } else if ( $username == $created_by ) {
	      $created_by_query= $username;
	      $_SESSION["search_query"] .= " AND ( tr.created_by LIKE '$created_by_query' )";
	   } else {
	      $created_by_query= $created_by;
	      $_SESSION["search_query"] .= " AND ( tr.created_by LIKE '$created_by_query' AND $section_priviledge )";
	   }
	      break;
	case "finance_tr.php":
   	   if ( ($created_by == "anybody") || ( $created_by == "") || ( $created_by == $cfg[ "sysadmin_email" ] ) ) {
	      $created_by_query = "%";
	   } else {
	      $created_by_query= $created_by;
	   }
	   $_SESSION["search_query"] .= " AND tr.created_by LIKE '$created_by_query'";
	   break;
    }

/*    $_SESSION["search_query"] .=
        " AND ( tr.tr_approved_number LIKE '0_____'"
      . " 	OR tr.tr_approved_number LIKE ''"
      . " 	OR tr.tr_approved_number IS NULL )";*/

   if ( ($action == "search_section") || ( isset($section_id ) ) ) {
   	if ( $section_id != "1" )
	      $_SESSION["search_query"] .= " AND tr.section=$section_id";
   }
   if ( isset($status) && ( $status != "none" ) && ( $status != "" ) && ( $status != "any" )) {
	      $_SESSION["search_query"] .= " AND tr.status='$status'";
   }
   $_SESSION["search_query"] .= " ORDER BY $order_by $order";

  //echo $_SESSION["search_query"];

   if (!$summary = paged_query($db)) {
      search_form($db);
   } else {
      paint_table($summary, $db);
   }
}

//
//  Print the header of the tables for  TR  and for  Finance TR
//
function get_paint_tr_table_header($db, $context = "search_tr.php" ) {
  global $username, $user_role, $cfg, $section_id, $vendor_id, $status, $created_by;

		$server_query = $_SERVER['QUERY_STRING'];
		// set default action
	    if ( ereg_replace('action=[^&]*', '', $server_query) == $server_query )
	    	$server_query .= "&action=search_all";

	    $notpaged_query_url = ereg_replace('action=[^&]*', 'action=search_all', $server_query );
	    $notpaged_query_url = ereg_replace('page=[^&]*[&]*', '', $notpaged_query_url );

   	    $order_by_query = ereg_replace('&order_by=[^&]*', '', $notpaged_query_url );
	    $order_by_query = ereg_replace('&order=[^&]*', '', $order_by_query );
            $SortLinkDescBegin = "<a href=\"$context?$order_by_query&order_by=";
            $SortLinkDescEnd   = "&order=DESC\"><img src=\"images/s_desc.png\" border=\"0\"></a>";
            $SortLinkAscBegin  = "<a href=\"$context?$order_by_query&order_by=";
            $SortLinkAscEnd    = "&order=ASC\"><img src=\"images/s_asc.png\" border=\"0\"></a>";


   		$sections = $db->Execute("SELECT id, name FROM section WHERE enabled='Y' ORDER BY name");
		$section_filter_query = ereg_replace('&section_id=[0-9]*', '', $notpaged_query_url );
		$section_filter  = $sections->GetMenu4("filter_section", $section_id, "$context?$section_filter_query&section_id=" );
   		
		$status_filter_query = ereg_replace('&status=[^&]*', '', $notpaged_query_url );
		$status_filter = "<select name=\"status\" onchange=\"javascript:if (this.value){window.location='$context?$status_filter_query&status='+this.value+'';}\">";
                if ( ! isset($status) ) $selected_none = "selected";
                if ( $status == "Open" ) $selected_open = "selected";
                if ( $status == "Requested" ) $selected_requested = "selected";
                if ( $status == "Approved" ) $selected_approved = "selected";
                if ( $status == "Closed" ) $selected_close = "selected";
                if ( $status == "Canceled" ) $selected_cancel = "selected";
            	$status_filter .= "<option value=\"none\" $selected_none>-- Status --</option>";
                $status_filter .= "<option value=\"Open\" $selected_open class=\"tr_open\">Open</option>";
                $status_filter .= "<option value=\"Requested\" $selected_requested class=\"tr_requested\">Requested</option>";
                $status_filter .= "<option value=\"Approved\" $selected_approved class=\"tr_approved\">Approved</option>";
              	$status_filter .= "<option value=\"Closed\" $selected_close class=\"tr_closed\">Closed</option>";
              	$status_filter .= "<option value=\"Canceled\" $selected_cancel class=\"tr_canceled\">Canceled</option>";
                $status_filter .= '</select>';

		
		if ( $created_by == $cfg[ "sysadmin_email" ] ) $created_by = "";
		$creators = $db->Execute("SELECT username, fullname FROM users  WHERE username LIKE \"%@%\" ORDER BY fullname"); 
		$creator_filter_query = ereg_replace('&created_by=[^&]*', '', $notpaged_query_url );
		$creator_filter = $creators->GetMenu4("filter_creator", $created_by, "$context?$creator_filter_query&created_by='+this.value+'';}\">" );


	switch ($context) {
		case "search_tr.php" :
	 echo "<td align=\"center\">". $SortLinkDescBegin . "tr.draft_number" . $SortLinkDescEnd . " <b>Req.</b> "            . $SortLinkAscBegin . "tr.draft_number" . $SortLinkAscEnd . "</td>";
         //echo "<td>". $SortLinkDescBegin . "po.created_by"   . $SortLinkDescEnd . " <b>$creator_filter</b> "         . $SortLinkAscBegin . "po.created_by"   . $SortLinkAscEnd . "</td>";
         echo "<td>"."<b>$creator_filter</b>"."</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "tr.date"         . $SortLinkDescEnd . " <b>Date</b> "            . $SortLinkAscBegin . "tr.date"         . $SortLinkAscEnd . "</td>";
         //echo "<td>". $SortLinkDescBegin . "po.section"      . $SortLinkDescEnd . " <b>$section_filter</b> "         . $SortLinkAscBegin . "po.section"      . $SortLinkAscEnd . "</td>";
         echo "<td>". "<b>$section_filter</b>" . "</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "tr.destination"    . $SortLinkDescEnd . " <b>Destination</b> "         . $SortLinkAscBegin . "tr.destination"    . $SortLinkAscEnd . "</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "tr.status"         . $SortLinkDescEnd . " <b>$status_filter</b> "      . $SortLinkAscBegin . "tr.status"         . $SortLinkAscEnd . "</td>";
         //echo "<td align=\"center\">". $SortLinkDescBegin . "tr.tr_approved_number"         . $SortLinkDescEnd . " <b>TR</b> "            . $SortLinkAscBegin . "tr.tr_approved_number"         . $SortLinkAscEnd . "</td>";
         echo "<td align=\"right\">". "<b>Total</b>" ."</td>";
         echo "<td align=\"right\">". "<b>Requested</b>" ."</td>";
         echo "<td align=\"right\">". "<b>Transfered</b>" ."</td>";
	  	break;
	  	case "finance_tr.php" :
	 echo "<td align=\"center\">". $SortLinkDescBegin . "tr.draft_number" . $SortLinkDescEnd . " <b>Req.</b> "            . $SortLinkAscBegin . "tr.draft_number" . $SortLinkAscEnd . "</td>";
         //echo "<td>". $SortLinkDescBegin . "po.created_by"   . $SortLinkDescEnd . " <b>$creator_filter</b> "         . $SortLinkAscBegin . "po.created_by"   . $SortLinkAscEnd . "</td>";
         echo "<td>"."<b>$creator_filter</b>"."</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "tr.date"         . $SortLinkDescEnd . " <b>Date</b> "            . $SortLinkAscBegin . "tr.date"         . $SortLinkAscEnd . "</td>";
         //echo "<td>". $SortLinkDescBegin . "po.section"      . $SortLinkDescEnd . " <b>$section_filter</b> "         . $SortLinkAscBegin . "po.section"      . $SortLinkAscEnd . "</td>";
         echo "<td>". "<b>$section_filter</b>" . "</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "tr.destination"    . $SortLinkDescEnd . " <b>Destination</b> "         . $SortLinkAscBegin . "tr.destination"    . $SortLinkAscEnd . "</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "tr.status"         . $SortLinkDescEnd . " <b>$status_filter</b> "      . $SortLinkAscBegin . "tr.status"         . $SortLinkAscEnd . "</td>";
         //echo "<td align=\"center\">". $SortLinkDescBegin . "tr.tr_approved_number"         . $SortLinkDescEnd . " <b>TR</b> "            . $SortLinkAscBegin . "tr.tr_approved_number"         . $SortLinkAscEnd . "</td>";
         echo "<td align=\"right\">". "<b>Total</b>" ."</td>";
         echo "<td align=\"right\">". "<b>Requested</b>" ."</td>";
         echo "<td align=\"right\">". "<b>Transfered</b>" ."</td>";
         //echo "<td align=\"center\"><b>Show</b></td>";
	 	break;
	}

}



// Returns a text description of the PO
// Status
// Items
// Comment
function tr_description($draft_number) {
	global $db;

	if (!$tr = $db->Execute("SELECT * FROM tr WHERE draft_number=$draft_number")) {
		echo "<table class=\"warn_db\" width=\"100%\"><tr><td>Cannot get description PO | DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		exit;
	}
	$section_id	= $tr->fields["section"];
	$cr_user_id	= $tr->fields["created_by"];
	$section	= $db->Execute("SELECT * FROM section WHERE id=$section_id");
	$cr_user	= $db->Execute("SELECT fullname FROM users WHERE username='$cr_user_id'");
	$line_items	= $db->Execute("SELECT * FROM tr_items WHERE draft_number=$draft_number ORDER BY id");

	// === Details ===
	$txt = str_pad("=[ Details ]", 120, "=") . "\r\n";
	$txt .= str_pad("Date", 15) . ": " . $tr->fields["date"] . "\r\n";
	$txt .= str_pad("Funding", 15) . ": " . $section->fields["name"] ."\r\n";
	$txt .= str_pad("Destination", 15). ": " . $tr->fields["destination"] . "\r\n";
	$txt .= str_pad("Purpose", 15). ": " . $tr->fields["purpose"] . "\r\n";
	$txt .= str_pad("Depart", 15). ": " . display_date($tr->fields["depart_date"]) . "\r\n";
	$txt .= str_pad("Return", 15). ": " . display_date($tr->fields["return_date"]) . "\r\n";
	$txt .= str_pad("Status", 15). ": " . $tr->fields["status"] . "\r\n";
	$txt .= str_pad("Requested By", 15). ": " . $cr_user->fields["fullname"] . "\r\n";

	if (($tr->fields["status"] == "Approved") || ($tr->fields["status"] == "Closed")) {
		$app_user	= $db->Execute("SELECT fullname FROM users WHERE username='" . $tr->fields["approved_by"] . "'");
		$txt .= str_pad("Approved by", 15) . ": " . $app_user->fields["fullname"] . "\r\n";
		/*if ( $tr->fields["tr_approved_number"] != "" ) {
			$txt .= str_pad("TR Number", 15) . ": " . $tr->fields["tr_approved_number"] . "\r\n";
		}*/
	}

	// === Details ===
	$txt .= str_pad("", 120, " ") . "\r\n";
	$txt .= str_pad("=[ Items ]", 120, "=") . "\r\n";
	$txt .= str_pad("", 120, " ") . "\r\n";
	$txt .= str_pad("Type", 20) . str_pad("Description", 60) . str_pad("Price", 10, " ", STR_PAD_LEFT) . str_pad("Prepaid", 9, " ", STR_PAD_BOTH) . str_pad("Comment", 40) .  "\r\n";
	$txt .= str_pad("", 120, "-") . "\r\n";

	while (!$line_items->EOF) {
	        $txt .= str_pad($line_items->fields["type"], 20);
	        $txt .= str_pad(substr($line_items->fields["description"], 0, 59), 60);
		$txt .= str_pad(sprintf("%01.2f", $line_items->fields["price"] * $line_items->fields["quantity"] * $line_items->fields["exchangerate"]), 10, " ", STR_PAD_LEFT);
		$txt .= str_pad($line_items->fields["prepaid"], 9, " ", STR_PAD_BOTH);
		$txt .= str_pad($line_items->fields["comment"], 40);
		$txt .= "\r\n";
		$line_items->MoveNext();
	}
	$txt .= str_pad("", 120, "-") . "\r\n";
	$txt .= str_pad("Total", 80) . str_pad(get_tr_total($draft_number), 10,  " " , STR_PAD_LEFT) ." ". get_currency_sign( 'e', "text" ).  "\r\n";

	return $txt;
}

	
function get_tr_total( $draft_number ) {

   global $db;

   if (!$tr = $db->Execute("SELECT * FROM tr WHERE draft_number=$draft_number")) {
	echo "<table class=\"warn_db\" width=\"100%\"><tr><td>Cannot get PO total | DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
	exit;
   }

   $line_items	= $db->Execute("SELECT * FROM tr_items WHERE draft_number=$draft_number ORDER BY id");

   $tr_total = 0;
   while (!$line_items->EOF) {
	$tr_total += $line_items->fields["price"] * $line_items->fields["quantity"] * $line_items->fields["exchangerate"];
	$line_items->MoveNext();
   }

   return sprintf("%01.2f", $tr_total);
}


function get_tr_maximum_budget( $draft_number ) {

   global $db;

   if (!$tr = $db->Execute("SELECT * FROM tr WHERE draft_number=$draft_number")) {
	echo "<table class=\"warn_db\" width=\"100%\"><tr><td>Cannot get PO total | DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
	exit;
   }

   return $tr->fields["maximum_budget"];
}




function get_tr_currency_sign( $draft_number, $symboletype = "html" ) {
   global $db;

   if (!$tr = $db->Execute("SELECT * FROM tr WHERE draft_number=$draft_number")) {
	echo "<table class=\"warn_db\" width=\"100%\"><tr><td>Cannot get TR total | DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
	exit;
  }
  //return get_currency_sign( $tr->fields["currency"], $symboletype);
  return get_currency_sign( 'e', $symboletype);
}

?>
