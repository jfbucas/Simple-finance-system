<?php require("common.inc.php"); ?>

<?php
function search_form($db) {
   global $cfg;
   $sections = $db->Execute("SELECT name, id FROM section WHERE enabled='Y' ORDER BY name"); ?>
   <table class="default" width="100%" border="0" cellspacing="0" cellpadding="1">
   <form action="search_er.php" method="get" name="form1">
      <tr class="row_head">
         <td colspan="5"><b>Search the Expense Report and Claim</b></td>
      </tr>
      <tr class="box_bg">
         <td colspan="2" width="5%">&nbsp;</td>
         <td colspan="3">
	    </br>
            <select name="action">
               <!--option value="search_none" SELECTED>--- Select Search Type ---</option-->
               <option value="search_single" selected>Show a Single Expense Report</option>
               <option value="search_all">Show All Expense Report</option>
               <option value="search_section">Show Expense Reports by Section</option>
            </select>
	    </br>
	    </br>
         </td>
      </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <td align="right">Expense Report No.:</td>
         <td>       <input type="text" name="draft_number" size="20">  </td>
         <td align="right">From Date:</td>
         <td> <input type="text" name="from_date" size="12"
               onchange="return BisDate(this,'N')"><?php echo " ". $cfg["date_exp"]; ?>   </td>
      </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <!--td align="right">Approved number:</td>
         <td>   <input type="text" name="tr_approved_number" size="20">  </td-->
	 <td></td>
	 <td></td>
         <td align="right">To Date:</td>
         <td> <input type="text" name="to_date" size="12"
               onchange="return BisDate(this,'N')"><?php echo " ". $cfg["date_exp"]; ?>   </td>
      </tr>
      <tr class="box_bg">     <td colspan="5"> &nbsp; </td>     </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <td align="right">Section:</td>
         <td>   <?php echo $sections->GetMenu("section_id", "", FALSE); ?> </td>
         <td align="right">Created by:</td>
         <td> <input type="text" name="created_by" size="12" value="anybody"></td>
      </tr>
      <tr class="box_bg">     <td colspan="5"> &nbsp; </td>     </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <td align="right">Request Status:</td>
         <td colspan="3">
            <input type="radio" name="status" value="any" checked>Any &nbsp;&nbsp;
            <input type="radio" name="status" value="Open">Open &nbsp;&nbsp;
            <input type="radio" name="status" value="Requested">Requested &nbsp;&nbsp;
            <input type="radio" name="status" value="Approved">Approved &nbsp;&nbsp;
            <input type="radio" name="status" value="Closed">Closed
            <input type="radio" name="status" value="Canceled">Canceled
         </td>
      </tr>
      <tr class="box_bg">
         <td width="5%">&nbsp;</td>
         <td align="right">Order By:</td>
         <td colspan="3">
            <input type="radio" name="order_by" value="draft_number" checked>Travel Request No. &nbsp;&nbsp;
            <input type="radio" name="order_by" value="er.date">Date &nbsp;&nbsp;
            <!--input type="radio" name="order_by" value="er.tr_approved_number">Approved Number &nbsp;&nbsp;-->
            <input type="checkbox" name="order" value="ASC">Reverse
         </td>
      </tr>
      <tr class="box_bg">
         <td colspan="5">&nbsp;</td>
      </tr>
      <tr class="row_head">
         <td colspan="2">
         </td>
         <td colspan="3" align="left">
		<button type="button" class="button_search" onClick="document.form1.submit();">Search</button>
         </td>
      </tr>
   </form>
   </table> <?php
} ?>

<?php
function paint_table(&$summary, $db) {
  global $username, $user_role, $cfg, $section_id, $status, $created_by;
 
 	?><table class="default" border="0" align="center" cellpadding="10" width="80%"><tr><td>
	  <h2 align="center"> Expense Reports and Claim List </h2> 
	  </td></tr></table>

          <table class="small" border="0" cellpadding="1" cellspacing="0" width="100%">
          <tr class="row_head">
	<?php
		get_paint_er_table_header( $db, "search_er.php"); 
	  ?>
      </tr> <?php
      $i = 1;
      $summary->MoveFirst();
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
	  $receptionist=$section_details->fields["receptionist"];
	  $er_creator=$summary->fields["created_by"];          
			
          if (($user_role == $cfg["admin"]) || 
	      ($user_role == $cfg["registrar"]) || 
	      ($user_role == $cfg["finofficer"]) || 
	      ($user_role == $cfg["finmember"]) || 
	      ($username == $er_creator) || 
	      ($username == $superapprover) ||
	      ($username == $headof) ||
	      ($username == $delegate) ||
	      ($username == $receptionist) ){

             if ($i % 2 == 0) {
                echo "<tr class=\"row_even\" ";
              } else {
                 echo "<tr class=\"row_odd\" ";
              }

             echo "onclick=\"location.href='er.php?action=view_from_search&draft_number=". $summary->fields["draft_number"] ."'\">";

             if (!$user_details = $db->Execute("SELECT * FROM users WHERE username='$er_creator'")) {
               echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
               break;
             }

            
	     echo '<td align="center">';
             echo $summary->fields["draft_number"]; 

	       ?>
         </td>
         <td><?php echo $user_details->fields["fullname"]; ?></td>
         <td align="center"><?php echo display_date($summary->fields["date"]); ?></td>
         <td><?php echo $summary->fields["name"]; ?></td>
         <td><?php echo $summary->fields["description"]; ?> </td>
         <td align="center"><?php echo "<div class=er_" . strtolower($summary->fields["status"]) . ">" . $summary->fields["status"] . "</div>";  ?></td><?php
	/*
               		<td align="center"></td> <?php break;
	       		<td align="center"><?php echo $summary->fields['approved_number']?></td> <?php break;
	*/
	$total_requested = get_er_total($summary->fields["draft_number"]);
	$total_prepaid = get_er_advance($summary->fields["draft_number"]) + get_er_prepaid($summary->fields["draft_number"]);
	?>

	 <td align="right"><?php echo $total_requested . " " . get_er_currency_sign($summary->fields["draft_number"]) ."&nbsp;"; ?></td>
	 <td align="right"><?php echo $total_prepaid . " " . get_er_currency_sign($summary->fields["draft_number"]) ."&nbsp;"; ?></td>
	 <td align="right"><?php echo sprintf("%01.2f",$total_requested - $total_prepaid) . " " . get_er_currency_sign($summary->fields["draft_number"]) ."&nbsp;"; ?></td>
      </tr> <?php
	 //End if user_role
           $i++;
         }
         $summary->MoveNext();
      } ?>      
   </table>
   <table class="small" border="0" cellpadding="1" cellspacing="0" width="100%">
      <tr class="row_head">
         <td align="center"> <?php

	    $paged_query_url = ereg_replace('action=[^&]*[&]*', '', $_SERVER['QUERY_STRING'] );
	    $paged_query_url = ereg_replace('page=[^&]*[&]*', '', $paged_query_url );
            if (!$summary->AtFirstPage() && $summary->AbsolutePage() != -1) { ?>
               <a href="search_er.php?action=paged_query&page=<?php echo $summary->AbsolutePage() - 1; echo "&" . $paged_query_url; ?>">
                  <img src="images/previous.png" border="0" alt="Previous"></a> <?php
            }
            echo "&nbsp;";
            if (!$summary->AtLastPage() && $summary->AbsolutePage() != -1) { ?>
               <a href="search_er.php?action=paged_query&page=<?php echo $summary->AbsolutePage() + 1; echo "&" . $paged_query_url; ?>">
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
		<!--button type="button" class="button_print" onClick="window.location='search_er.php?action=print_result';">Print</button-->
		<button type="button" class="button_export" onClick="window.location='search_er.php?action=csv_result';">Export</button>
         </td>
      </tr>
   </table> <?php
} ?>

<?php
	if ( ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) ) {
		finance_buttons(false);
	} else {
		er_buttons(false); 
	}

$action = strtolower($action);
switch ($action) {
   case "csv_result": 
   	$_SESSION["context"] = "search_er.php";
   	?>
      <script language="JavaScript">
         window.open("csv_search_er.php");
      </script> <?php
      echo "<table class=\"info\" width=\"100%\"><tr><td>Search Results opened in a new browser window.</td></tr></table>";
      break;
/*   case "print_result": ?>
      <script language="JavaScript">
         window.open("print_html_search_er.php");
      </script> <?php
      echo "<table class=\"info\" width=\"100%\"><tr><td>Search Results opened in a new browser window.</td></tr></table>";
      break;*/
   case "search_none":
      echo "<table class=\"warn\" width=\"100%\"><tr><td>You must select a Search Type from the drop menu.</td></tr></table>";
      search_form($db);
      break;
   case "search_single":
      if (empty($draft_number)) { // && empty($er_approved_number) ) {
         echo "<table class=\"warn\" width=\"100%\"><tr><td>You must enter a valid Expense Report number.</td></tr></table>";
         search_form($db);
         break;
      }
      $SingleQuery = "SELECT * FROM er WHERE ";
      if (!empty($draft_number)) {
	$SingleQuery .= "draft_number=$draft_number";
      	if (!empty($er_approved_number)) {
		$SingleQuery .= " AND ";
	}
      }
      if (!$er = $db->Execute($SingleQuery)) {
         echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
         break;
      }
      if ($er->RecordCount() == 0) {
         echo "<table class=\"warn\" width=\"100%\"><tr><td>Requisition No. $draft_number not found.</td></tr></table>";
         search_form($db);
         break;
      } ?>
      <script language="JavaScript">
         window.location="er.php?action=view_from_search&draft_number=<?php echo $er->fields["draft_number"]; ?>";
      </script> <?php
      break;
   case "search_all":
      show_er($db);
      break;
   case "search_section":
      show_er($db);
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
