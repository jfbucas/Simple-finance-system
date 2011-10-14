<?php require("common.inc.php"); ?>

<?php
function show_po_items($db, $context = "search_po.php" ) {
   global $action, $username, $user_role, $status, $section_id,
          $order, $created_by, $cfg;

   if ($order != "ASC") {
      $order = "DESC";
   }


   $_SESSION["search_query"] =
       "SELECT line_items.draft_number, line_items.descrip"
     . " FROM po, line_items"
     . " WHERE ( po.draft_number = line_items.draft_number )"
     . " AND ( po.created_by LIKE '$username' )"
     . " ORDER BY po.date $order";

   //echo $_SESSION["search_query"];

   if (!$summary = paged_query($db)) {
   } else {
      paint_table($summary, $db);
   }
}?>

<?php
function paint_table(&$summary, $db) {
  global $username, $user_role, $cfg, $section_id, $vendor_id, $status, $created_by;
 
 	?><table class="default" border="0" align="center" cellpadding="10" width="80%"><tr><td>
	  <h2 align="center"> PO List </h2> 
	  </td></tr></table>

          <table class="small" border="0" cellpadding="1" cellspacing="0" width="100%">
          <tr class="row_head">
	<?php
		get_paint_table_header( $db, "search_po_items.php"); 
	  ?>
      </tr> <?php
      $i = 1;
      $summary->MoveFirst();
      while (!$summary->EOF) {
		if ( $previous_draft != $summary->fields["draft_number"] ) {
			$i++;
			$previous_draft = $summary->fields["draft_number"];
		}
			
             if ($i % 2 == 0) {
                echo "<tr class=\"row_even\" ";
              } else {
                 echo "<tr class=\"row_odd\" ";
              }

             echo "onclick=\"location.href='po.php?action=view_from_search&draft_number=". $summary->fields["draft_number"] ."'\">"
	        . '<td align="center">' . $summary->fields["draft_number"] . "</td>"
	        . '<td align="left">' . $summary->fields["descrip"] . "</td>"
		. "</tr>";

         $summary->MoveNext();
      } ?>      
   </table>
   <table class="small" border="0" cellpadding="1" cellspacing="0" width="100%">
      <tr class="row_head">
         <td align="center"> <?php

	    $paged_query_url = ereg_replace('action=[^&]*[&]*', '', $_SERVER['QUERY_STRING'] );
	    $paged_query_url = ereg_replace('page=[^&]*[&]*', '', $paged_query_url );
            if (!$summary->AtFirstPage() && $summary->AbsolutePage() != -1) { ?>
               <a href="search_po_items.php?action=paged_query&page=<?php echo $summary->AbsolutePage() - 1; echo "&" . $paged_query_url; ?>">
                  <img src="images/previous.png" border="0" alt="Previous"></a> <?php
            }
            echo "&nbsp;";
            if (!$summary->AtLastPage() && $summary->AbsolutePage() != -1) { ?>
               <a href="search_po_items.php?action=paged_query&page=<?php echo $summary->AbsolutePage() + 1; echo "&" . $paged_query_url; ?>">
                  <img src="images/next.png" border="0" alt="Next"></a> <?php
            } ?>
         </td>
      </tr>
   </table>
   <table class="small" border="0" cellpadding="1" cellspacing="0" width="100%">
      <tr class="row_head"> <?php
         if ($summary->AbsolutePage() == -1) {
            echo "<td width='10%' >&nbsp;</td>";
         } else {
            echo "<td width='10%' >Page: " . $summary->AbsolutePage() . "</td>";
         } ?>
         <td align="right">
         </td>
      </tr>
   </table> <?php
} ?>

<?php
   po_buttons(false); 
$action = strtolower($action);
switch ($action) {
   case "csv_result": 
   	$_SESSION["context"] = "search_po.php";
   	?>
      <script language="JavaScript">
         window.open("csv_search_po.php");
      </script> <?php
      echo "<table class=\"info\" width=\"100%\"><tr><td>Search Results opened in a new browser window.</td></tr></table>";
      break;
   case "print_result": ?>
      <script language="JavaScript">
         window.open("print_html_search_po.php");
      </script> <?php
      echo "<table class=\"info\" width=\"100%\"><tr><td>Search Results opened in a new browser window.</td></tr></table>";
      break;
   case "search_none":
      echo "<table class=\"warn\" width=\"100%\"><tr><td>You must select a Search Type from the drop menu.</td></tr></table>";
      search_form($db);
      break;
   case "search_single":
      if (empty($draft_number) && empty($po_approved_number) ) {
         echo "<table class=\"warn\" width=\"100%\"><tr><td>You must enter a valid purchase order number or a valid approved number.</td></tr></table>";
         search_form($db);
         break;
      }
      $SingleQuery = "SELECT * FROM po WHERE ";
      if (!empty($draft_number)) {
	$SingleQuery .= "draft_number=$draft_number";
      	if (!empty($po_approved_number)) {
		$SingleQuery .= " AND ";
	}
      }
      if (!empty($po_approved_number))
        $SingleQuery .= "po_approved_number=$po_approved_number";

      if (!$po = $db->Execute($SingleQuery)) {
         echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
         break;
      }
      if ($po->RecordCount() == 0) {
         echo "<table class=\"warn\" width=\"100%\"><tr><td>Requisition No. $draft_number or Approved Number $po_approved_number  not found.</td></tr></table>";
         search_form($db);
         break;
      } ?>
      <script language="JavaScript">
         window.location="po.php?action=view_from_search&draft_number=<?php echo $po->fields["draft_number"]; ?>";
      </script> <?php
      break;
   case "search_all":
      show_po_items($db);
      break;
   case "search_section":
      show_po($db);
      break;
   case "search_vendor":
      show_po($db);
      break;
   case "paged_query":
      $summary = paged_query($db, $page);
      paint_table($summary, $db);
      break;
   default:
      search_form($db);
}
require("footer.inc.php");
?>
