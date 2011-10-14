<?php


function show_po($db, $context = "search_po.php" ) {
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
      //"SELECT po.draft_number, po.date, po.open, po.approved, section.name, vendor.name, po.po_approved_number, po.sent_to_supplier, po.paid"
      "SELECT po.draft_number, po.date, po.open, po.approved, section.name, vendor.name, po.po_approved_number, po.created_by, po.section, po.sent_to_supplier"
      . " FROM po, section, vendor"
      . " WHERE po.vendor=vendor.id"
      . " AND po.section=section.id"
      . " AND po.date>='$from_date'"
      . " AND po.date<='$to_date'";
   $section_priviledge="( section.superapprover LIKE '$username' OR section.headof LIKE '$username' OR section.delegate LIKE '$username' OR section.receptionist LIKE '$username' )";
   switch ( $context ) {
   	case "search_po.php":
	   if ($created_by == "anybody") 
	   	$created_by = $cfg[ "sysadmin_email" ];

	   if ($created_by == "anybody") {
	      $created_by_query = "%";
	      //if (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) {
	      if (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"])) {
	         $_SESSION["search_query"] .= " AND ( po.created_by LIKE '$created_by_query' )";
	      }else{
	         $_SESSION["search_query"] .= " AND ( po.created_by LIKE '$created_by_query' AND $section_priviledge )";
	      }
	   } else if ( ( $created_by == "") || ( $created_by == $cfg[ "sysadmin_email" ] ) ) {
	      $created_by_query= $username;
	      $_SESSION["search_query"] .= " AND ( po.created_by LIKE '$created_by_query' OR  $section_priviledge )";
	   } else if ( $username == $created_by ) {
	      $created_by_query= $username;
	      $_SESSION["search_query"] .= " AND ( po.created_by LIKE '$created_by_query' )";
	   } else {
	      $created_by_query= $created_by;
	      $_SESSION["search_query"] .= " AND ( po.created_by LIKE '$created_by_query' AND $section_priviledge )";
	   }
	      break;
	case "finance_po.php":
   	   if ( ($created_by == "anybody") || ( $created_by == "") || ( $created_by == $cfg[ "sysadmin_email" ] ) ) {
	      $created_by_query = "%";
	   } else {
	      $created_by_query= $created_by;
	   }
	   $_SESSION["search_query"] .= " AND po.created_by LIKE '$created_by_query'";
	   break;
    }

    $_SESSION["search_query"] .=
        " AND ( po.po_approved_number LIKE '0_____'"
      . " 	OR po.po_approved_number LIKE ''"
      . " 	OR po.po_approved_number IS NULL )";

   if ( ($action == "search_section") || ( isset($section_id ) ) ) {
   	if ( $section_id != "1" )
	      $_SESSION["search_query"] .= " AND po.section=$section_id";
   }
   if ( ($action == "search_vendor") || ( isset($vendor_id ) ) ) {
   	if ( $vendor_id != "1" )
   	  $_SESSION["search_query"] .= " AND po.vendor=$vendor_id";
   }
   if ( isset($status) && ( $status != "none" )) {
	  if ($status == "open") {
	      $_SESSION["search_query"] .= " AND po.open='Y' AND po.approved='N'";
	   }
	   if ($status == "approved") {
	      $_SESSION["search_query"] .= " AND po.open='Y' AND po.approved='Y'";
	   }
	   if ($status == "closed") {
	      $_SESSION["search_query"] .= " AND po.open='N'";
	   }
	   if ($status == "canceled") {
	      $_SESSION["search_query"] .= " AND po.open='C'";
	   }
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
//  Print the header of the tables for  PO  and for  Finance
//
function get_paint_table_header($db, $context = "search_po.php" ) {
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
   		
		$supplier = $db->Execute("SELECT id, name FROM vendor WHERE enabled > 0 ORDER BY name "); 
		$supplier_filter_query = ereg_replace('&vendor_id=[0-9]*', '', $notpaged_query_url );
		$supplier_filter  = $supplier->GetMenu4("filter_vendor", $vendor_id, "$context?$supplier_filter_query&vendor_id=", 25 );


		$status_filter_query = ereg_replace('&status=[^&]*', '', $notpaged_query_url );
		$status_filter = "<select name=\"status\" onchange=\"javascript:if (this.value){window.location='$context?$status_filter_query&status='+this.value+'';}\">";
                if ( ! isset($status) ) $selected_none = "selected";
            	$status_filter .= "<option value=\"none\" $selected_none>-- Status --</option>";
                if ( $status == "open" ) $selected_open = "selected";
                $status_filter .= "<option value=\"open\" $selected_open class=\"po_open\">Open</option>";
                if ( $status == "approved" ) $selected_approved = "selected";
                $status_filter .= "<option value=\"approved\" $selected_approved class=\"po_approved\">Approved</option>";
                if ( $status == "closed" ) $selected_close = "selected";
              	$status_filter .= "<option value=\"closed\" $selected_close class=\"po_closed\">Closed</option>";
                if ( $status == "canceled" ) $selected_cancel = "selected";
              	$status_filter .= "<option value=\"canceled\" $selected_cancel class=\"po_canceled\">Canceled</option>";
                $status_filter .= '</select>';

		
		if ( $created_by == $cfg[ "sysadmin_email" ] ) $created_by = "";
		$creators = $db->Execute("SELECT username, fullname FROM users  WHERE username LIKE \"%@%\" ORDER BY fullname"); 
		$creator_filter_query = ereg_replace('&created_by=[^&]*', '', $notpaged_query_url );
		$creator_filter = $creators->GetMenu4("filter_creator", $created_by, "$context?$creator_filter_query&created_by='+this.value+'';}\">" );


	switch ($context) {
		case "search_po.php" :
	 echo "<td align=\"center\">". $SortLinkDescBegin . "po.draft_number" . $SortLinkDescEnd . " <b>Req.</b> "            . $SortLinkAscBegin . "po.draft_number" . $SortLinkAscEnd . "</td>";
         //echo "<td>". $SortLinkDescBegin . "po.created_by"   . $SortLinkDescEnd . " <b>$creator_filter</b> "         . $SortLinkAscBegin . "po.created_by"   . $SortLinkAscEnd . "</td>";
         echo "<td>"."<b>$creator_filter</b>"."</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "po.date"         . $SortLinkDescEnd . " <b>Date</b> "            . $SortLinkAscBegin . "po.date"         . $SortLinkAscEnd . "</td>";
         //echo "<td>". $SortLinkDescBegin . "po.section"      . $SortLinkDescEnd . " <b>$section_filter</b> "         . $SortLinkAscBegin . "po.section"      . $SortLinkAscEnd . "</td>";
         echo "<td>". "<b>$section_filter</b>" . "</td>";
         //echo "<td>". $SortLinkDescBegin . "po.vendor"       . $SortLinkDescEnd . " <b>$supplier_filter</b> "          . $SortLinkAscBegin . "po.vendor"       . $SortLinkAscEnd . "</td>";
         echo "<td>". "<b>$supplier_filter</b>" ."</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "po.open"         . $SortLinkDescEnd . " <b>$status_filter</b> "            . $SortLinkAscBegin . "po.open"         . $SortLinkAscEnd . "</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "po.po_approved_number"         . $SortLinkDescEnd . " <b>PO</b> "            . $SortLinkAscBegin . "po.po_approved_number"         . $SortLinkAscEnd . "</td>";
         echo "<td align=\"right\">". "<b>Total</b>" ."</td>";
	  	break;
		case "search_po_items.php" :
	 echo "<td align=\"center\" width=5%>". $SortLinkDescBegin . "draft_number" . $SortLinkDescEnd . " <b>Req.</b> " . $SortLinkAscBegin . "draft_number" . $SortLinkAscEnd . "</td>";
	 echo "<td align=\"left\">". $SortLinkDescBegin . "po.descrip" . $SortLinkDescEnd . " <b>Description.</b> "            . $SortLinkAscBegin . "po.descrip" . $SortLinkAscEnd . "</td>";
		break;
	  	case "finance_po.php" :
         echo "<td align=\"center\"><b>Edit</b></td>";
	 echo "<td align=\"center\">". $SortLinkDescBegin . "draft_number" . $SortLinkDescEnd . " <b>Req.</b> " . $SortLinkAscBegin . "draft_number" . $SortLinkAscEnd . "</td>";
         //echo "<td>". $SortLinkDescBegin . "po.created_by"   . $SortLinkDescEnd . " <b>$creator_filter</b> "         . $SortLinkAscBegin . "po.created_by"   . $SortLinkAscEnd . "</td>";
         echo "<td>". "<b>$creator_filter</b>" . "</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "po.date"      . $SortLinkDescEnd . " <b>Date</b> "            . $SortLinkAscBegin . "po.date"      . $SortLinkAscEnd  . "</td>";
         //echo "<td>". $SortLinkDescBegin . "po.section"   . $SortLinkDescEnd . " <b>$section_filter</b> "         . $SortLinkAscBegin . "po.section"   . $SortLinkAscEnd ."</td>";
         echo "<td>". "<b>$section_filter</b>" ."</td>";
         //echo "<td>". $SortLinkDescBegin . "po.vendor"    . $SortLinkDescEnd . " <b>$supplier_filter</b> "        . $SortLinkAscBegin . "po.vendor"    . $SortLinkAscEnd ."</td>";
         echo "<td>"."<b>$supplier_filter</b>"."</td>";
         //echo "<td align=\"center\">". $SortLinkDescBegin . "po.open"            . $SortLinkDescEnd . " <b>$status_filter</b> "          . $SortLinkAscBegin . "po.open"    . $SortLinkAscEnd ."</td>";
         echo "<td align=\"center\">". "<b>$status_filter</b>" ."</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "po_approved_number" . $SortLinkDescEnd . " <b>PO</b> " . $SortLinkAscBegin . "po_approved_number" . $SortLinkAscEnd ."</td>";
         echo "<td align=\"center\">". $SortLinkDescBegin . "sent_to_supplier"   . $SortLinkDescEnd . " <b>Inv</b> " . $SortLinkAscBegin . "sent_to_supplier" . $SortLinkAscEnd ."</td>";
         echo "<td align=\"right\">". "<b>Total</b>" ."</td>";
         //echo "<td align=\"center\">". $SortLinkDescBegin . "paid"               . $SortLinkDescEnd . " <b>Paid</b> " . $SortLinkAscBegin . "paid" . $SortLinkAscEnd ."</td>";
	 	break;
	}

}



// Returns a text description of the PO
// Status
// Items
// Comment
function po_description($draft_number) {
	global $db;

	if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
		echo "<table class=\"warn_db\" width=\"100%\"><tr><td>Cannot get description PO | DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		exit;
	}
	$vendor_id	= $po->fields["vendor"];
	$section_id	= $po->fields["section"];
	$cr_user_id	= $po->fields["created_by"];
	$vendor		= $db->Execute("SELECT DISTINCT name FROM vendor WHERE id=$vendor_id");
	$section	= $db->Execute("SELECT * FROM section WHERE id=$section_id");
	$cr_user	= $db->Execute("SELECT fullname FROM users WHERE username='$cr_user_id'");
	$line_items	= $db->Execute("SELECT * FROM line_items WHERE draft_number=$draft_number ORDER BY id");
	$comment	= $db->Execute("SELECT comment FROM po_comments WHERE draft_number=$draft_number");

	// === Details ===
	$txt = str_pad("=[ Details ]", 100, "=") . "\r\n";
	$txt .= str_pad("Date", 15) . ": " . $po->fields["date"] . "\r\n";
	$txt .= str_pad("Section", 15) . ": " . $section->fields["name"] ."\r\n";

	$txt .= str_pad("Status", 15). ": ";
	if ($po->fields["open"] == "Y" && $po->fields["approved"] == "N") { 
	   	$txt .= "Open";
        } else if ($po->fields["open"] == "Y" && $po->fields["approved"] == "Y") {
	   	$txt .= "Approved";
        } else if ($po->fields["open"] == "N" ) {
	  	$txt .= "Closed";
        } else {
	   	$txt .= "Canceled";
        }
	$txt .= "\r\n";

	$txt .= str_pad("Requested By", 15). ": " . $cr_user->fields["fullname"] . "\r\n";
	$txt .= str_pad("Supplier", 15) . ": " . $vendor->fields["name"] . "\r\n";

	if ($po->fields["approved"] == "Y"){
		$app_user	= $db->Execute("SELECT fullname FROM users WHERE username='" . $po->fields["approved_by"] . "'");
		$txt .= str_pad("Approved by", 15) . ": " . $app_user->fields["fullname"] . "\r\n";
		if ( $po->fields["po_approved_number"] != "" ) {
			$txt .= str_pad("PO Number", 15) . ": " . $po->fields["po_approved_number"] . "\r\n";
		}
	}

	// === Details ===
	$txt .= str_pad("", 100, " ") . "\r\n";
	$txt .= str_pad("=[ Items ]", 100, "=") . "\r\n";
	$txt .= str_pad("", 100, " ") . "\r\n";
	$txt .= str_pad("Description", 60) . str_pad("Rcv", 4) . str_pad("Qty", 4) . str_pad("Price", 10) . str_pad("Amount", 10) . "\r\n";
	$txt .= str_pad("", 100, "-") . "\r\n";

	$po_total = 0;
	while (!$line_items->EOF) {
	        $txt .= str_pad(substr($line_items->fields["descrip"], 0, 59), 60);
		if ($line_items->fields["received"] == "Y") {
			$txt .= str_pad(" Y", 4);
		} else {
			$txt .= str_pad(" N", 4);
		}
		$txt .= str_pad($line_items->fields["qty"], 4);
		$txt .= str_pad($line_items->fields["unit_price"], 10);
		$txt .= str_pad($line_items->fields["amount"], 10) . "\r\n";
		$po_total += $line_items->fields["amount"];
		$line_items->MoveNext();
	}
	$txt .= str_pad("", 100, "-") . "\r\n";
	if (($po_total != 0) && ($po->fields["vat"] != "a")) {
		switch ( $po->fields["vat"] ) {
			case "e" : $po_total_vat = $po_total * 0.215; $vat_str="21%"; break;
			case "b" : $po_total_vat = $po_total * 0.215; $vat_str="21.5%"; break;
			case "c" : $po_total_vat = $po_total * 0.135; $vat_str="13.5%"; break;
			case "d" : $po_total_vat = $po_total * 0.048; $vat_str="4.8%"; break;
		}
		$txt .= str_pad("VAT", 80) . $po_total_vat . "\r\n";
		$po_total += $po_total_vat;
	}
	$txt .= str_pad("Total", 80) . $po_total . "\r\n";

	$txt .= str_pad("", 100, " ") . "\r\n";
	$txt .= str_pad("=[ Comment ]", 100, "=") . "\r\n";
	$txt .= $comment->fields["comment"];

	return $txt;
}

	
function get_po_total( $draft_number ) {

   global $db;

   if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
	echo "<table class=\"warn_db\" width=\"100%\"><tr><td>Cannot get PO total | DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
	exit;
   }

   $line_items	= $db->Execute("SELECT * FROM line_items WHERE draft_number=$draft_number ORDER BY id");

   $po_total = 0;
   while (!$line_items->EOF) {
	$po_total += $line_items->fields["amount"];
	$line_items->MoveNext();
   }

   if (($po_total != 0) && ($po->fields["vat"] != "a")) {
	switch ( $po->fields["vat"] ) {
		case "e" : $po_total_vat = $po_total * 0.21; $vat_str="21%"; break;
		case "b" : $po_total_vat = $po_total * 0.215; $vat_str="21.5%"; break;
		case "c" : $po_total_vat = $po_total * 0.135; $vat_str="13.5%"; break;
		case "d" : $po_total_vat = $po_total * 0.048; $vat_str="4.8%"; break;
	}
      $po_total += $po_total_vat;
   }

   if ($po->fields["delivery"] != 0) {
      $po_total += $po->fields["delivery"];
   }

   return sprintf("%01.2f", $po_total);
}
function get_po_currency_sign( $draft_number, $symboletype = "html" ) {
   global $db;

   if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
	echo "<table class=\"warn_db\" width=\"100%\"><tr><td>Cannot get PO total | DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
	exit;
  }
  return get_currency_sign( $po->fields["currency"], $symboletype);
}

function get_po_status( $draft_number ) {
   global $db;

   if (!$po = $db->Execute("SELECT * FROM po WHERE draft_number=$draft_number")) {
	echo "<table class=\"warn_db\" width=\"100%\"><tr><td>Cannot get PO total | DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
	exit;
  }

         if ($po->fields["open"] == "Y" && $po->fields["approved"] == "N") { 
		$status="Open";
         } else if ($po->fields["open"] == "Y" && $po->fields["approved"] == "Y") {
		$status="Approved";
         } else if ($po->fields["open"] == "N" ) {
		$status="Closed";
         } else {
		$status="Canceled";
         }

  return $status;
} ?>
