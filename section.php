<?php require("common.inc.php"); ?>

<?php
function new_section_form($db) {
  global $refer; ?>
<br>
  <table class="default" width="100%" border="0" cellspacing="0" cellpadding="1">
   <form action="section.php" method="post" name="form1">
    <tr class="row_head"> 
      <td colspan="4"><b>New Section Information</b></td>
    </tr>
         <tr class="box_bg"> <td colspan="4" align=center nowrap> &nbsp;   </td>  </tr>
    <tr class="box_bg">
      <td align="right">Name:</td>
      <td> 
        <input type="text" name="name" size="50">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Super Approver:</td>
      <td> 
        <?php 
          $userslist = $db->Execute("SELECT fullname, username FROM users ORDER BY fullname"); 
          echo $userslist->GetMenu("superapprover", "", FALSE);      		  

//         <input type="text" name="headof" size="50">
       ?>
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Head of Section:</td>
      <td> 
        <?php 
          $userslist = $db->Execute("SELECT fullname, username FROM users ORDER BY fullname"); 
          echo $userslist->GetMenu("headof", "", FALSE);      		  

//         <input type="text" name="headof" size="50">
       ?>
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Delegate of Section:</td>
      <td> 
        <?php 
          $userslist = $db->Execute("SELECT fullname, username FROM users ORDER BY fullname"); 
          echo $userslist->GetMenu("delegate", "", FALSE);      		  

//         <input type="text" name="headof" size="50">
       ?>
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Coordinator of Section:</td>
      <td> 
        <?php 
          $userslist = $db->Execute("SELECT fullname, username FROM users ORDER BY fullname"); 
          echo $userslist->GetMenu("receptionist", "", FALSE);      		  

//         <input type="text" name="headof" size="50">
       ?>
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Address1:</td>
      <td> 
        <input type="text" name="address1" size="100">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Address2:</td>
      <td> 
        <input type="text" name="address2" size="100">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">City:</td>
      <td> 
        <input type="text" name="city" size="50">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Province:</td>
      <td> 
        <input type="text" name="province" size="50">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Post Code:</td>
      <td> 
        <input type="text" name="p_code" size="16">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Country:</td>
      <td> 
        <input type="text" name="country" size="50">
      </td>
    </tr>        
         <tr class="box_bg"> <td colspan="4" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td colspan="4" align=center nowrap> 
	 	<button type="button" class="button_enter"
            		onClick="if (valid_section_form(document.form1)) { document.form1.submit(); }">Enter</button>
	 	<button type="button" class="button_reset" onClick="document.form1.reset();">Reset</button>
            </td>
         </tr>
   <?php
   if (isset($refer)) { ?>
      <input type="hidden" name="refer" value="<?php echo $refer; ?>"> <?php
   } ?>
  <input type="hidden" name="action" value="insert">
  </form>
  </table>
  <script language="JavaScript">
     document.form1.name.focus();
  </script> <?php
} 

function edit_section_form($db, $section_id) {
   $section = $db->Execute("SELECT * FROM section WHERE id='$section_id'"); ?>
	<br>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
      <form name="form_edit_section" method="post" action="section.php">
         <tr class="row_head"> 
            <td colspan="2" nowrap><b>Edit Section Information</b></td>
         </tr>
         <tr class="box_bg"> <td colspan="4" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
           <td align="right">Name:</td>
           <td> <input type="text" name="name" size="50" value="<?php echo $section->fields["name"]; ?>"></td>
         </tr>
         <tr class="box_bg">
           <td align="right">Super Approver:</td>
           <td> <?php
             $all_users = $db->Execute("SELECT fullname, username FROM users ORDER BY fullname");
             echo $all_users->GetMenu2("superapprover", $section->fields["superapprover"], FALSE, FALSE, 0);
             ?></div>
         </td>
       </tr>
         <tr class="box_bg">
           <td align="right">Head of Section:</td>
           <td> <?php
             $all_users = $db->Execute("SELECT fullname, username FROM users ORDER BY fullname");
             echo $all_users->GetMenu2("headof", $section->fields["headof"], FALSE, FALSE, 0);
             ?></div>
         </td>
       </tr>
         <tr class="box_bg">
           <td align="right">Delegate of Section:</td>
           <td> <?php
             $all_users = $db->Execute("SELECT fullname, username FROM users ORDER BY fullname");
             echo $all_users->GetMenu2("delegate", $section->fields["delegate"], FALSE, FALSE, 0);
             ?></div>
         </td>
       </tr>
         <tr class="box_bg">
           <td align="right">Coordinator of Section:</td>
           <td> <?php
             $all_users = $db->Execute("SELECT fullname, username FROM users ORDER BY fullname");
             echo $all_users->GetMenu2("receptionist", $section->fields["receptionist"], FALSE, FALSE, 0);
             ?></div>
         </td>
       </tr>
    <tr class="box_bg">
      <td align="right">Address1:</td>
      <td>
        <input type="text" name="address1" size="100" value="<?php echo $section->fields["address1"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Address2:</td>
      <td>
        <input type="text" name="address2" size="100" value="<?php echo $section->fields["address2"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">City:</td>
      <td>
        <input type="text" name="city" size="50" value="<?php echo $section->fields["city"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Province:</td>
      <td>
        <input type="text" name="province" size="50" value="<?php echo $section->fields["province"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Post Code:</td>
      <td>
        <input type="text" name="p_code" size="16" value="<?php echo $section->fields["p_code"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Country:</td>
      <td>
        <input type="text" name="country" size="50" value="<?php echo $section->fields["country"]; ?>">
      </td>
    </tr>

         <tr class="box_bg"> <td colspan="4" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td colspan="4" align=center nowrap> 
	 	<button type="button" class="button_update"
            		onClick="if (valid_section_form(document.form_edit_section)) { document.form_edit_section.submit(); }">Update</button>
	 	<button type="button" class="button_delete"
            		onClick="if (isConfirmed('Are you sure you want to DELETE this Section ?')) { window.location='section.php?action=delete_section&section_id=<?php echo $section_id; ?>'; }">Delete</button>
	 	<button type="button" class="button_cancel" onClick="window.location='section.php?action=cancel'">Cancel</button>
            </td>
         </tr>
     </table>
   <input type="hidden" name="action" value="update_section">
   <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
   </form>
   <script language="JavaScript">
      document.form_edit_section.name.focus();
   </script> <?php

} ?>

<?php
function paint_section_table($db) {
   $section_summary = $db->Execute("SELECT * FROM section LIMIT 1,1000");
?>
	</br></br>
   <table class="small" width="100%" border="0" cellspacing="0" cellpadding="1">
      <tr class="row_head"> 
        <td><b>Enabled</b></td>
        <td><b>Section Name</b></td>
        <td align=center><b>Super-Approver</b></td>
        <td align=center><b>Head</b></td>
        <td align=center><b>Delegate</b></td>
        <td align=center><b>Coordinator</b></td>
        <td><b>Address</b></td>
      </tr> <?php
      $i = 1;
      while (!$section_summary->EOF) {
         if ($i % 2 == 0) {
            echo "<tr class=\"row_even\" >";
         } else {
            echo "<tr class=\"row_odd\" >";
         } 
         $onclick="onclick=\"location.href='section.php?action=edit_section&section_id=" . $section_summary->fields["id"] . "'\"";
	 
           if ($section_summary->fields["enabled"] == "Y") {
		      echo "<td title=\"Click on the Y to mark it as disabled\" align=\"center\" onclick='"
			   ."location.href=\"section.php?action=disable_section&section_id=" . $section_summary->fields["id"] . "\"'>"
			   . "<img src=\"images/yes.png\" border=\"0\" alt=\"Enabled\"></a></td>";
		}else{
		      echo "<td title=\"Click on the N to mark it as enabled\" align=\"center\" class=\"\" onclick='"
			   ."location.href=\"section.php?action=enable_section&section_id=" . $section_summary->fields["id"] . "\"'>"
			   . "<img src=\"images/no.png\" border=\"0\" alt=\"Disabled\"></a></td>";
		}
	 ?> 
            <td <?php echo "$onclick"; ?>>
               <!--a href="section.php?action=edit_section&section_id=<?php echo $section_summary->fields["id"]; ?>"-->
                  <?php echo $section_summary->fields["name"]; ?><!--/a-->
            </td>
            <td align=center <?php echo "$onclick"; ?>>
               <?php echo $section_summary->fields["superapprover"]; ?>
            </td>
            <td align=center <?php echo "$onclick"; ?>>
               <?php echo $section_summary->fields["headof"]; ?>
            </td>
            <td align=center <?php echo "$onclick"; ?>>
               <?php echo $section_summary->fields["delegate"]; ?>
            </td>
            <td align=center <?php echo "$onclick"; ?>>
               <?php echo $section_summary->fields["receptionist"]; ?>
            </td>
            <td <?php echo "$onclick"; ?>>
               <?php echo $section_summary->fields["address1"] . " " . $section_summary->fields["address2"] . " " . $section_summary->fields["city"] . " " .
			 $section_summary->fields["province"] . " " . $section_summary->fields["country"] . " " . $section_summary->fields["p_code"]; ?>
            </td>
          </tr> <?php
         $i++;
         $section_summary->MoveNext();
      } ?>
   </table></br> <?php
}


if (
	($user_role == $cfg["admin"]) || 
	($user_role == $cfg["registrar"]) || 
	($user_role == $cfg["finofficer"]) ||
	($user_role == $cfg["finmember"])
     ) {
     finance_buttons(false);

   $action = strtolower($action);
   switch ($action) {
      case "cancel":
         echo "<table class=\"warn\" width=\"100%\"><tr><td>Section Dictionary update canceled.</td></tr></table>";
         new_section_form($db);
	 paint_section_table($db);
         break;
      case "insert":
         if (!valid_char_1($name)) {
            echo "<table class=\"warn\" width=\"100%\"><tr><td>You must enter a valid section name.</td></tr></table>";
            new_section_form();
            break;
         }
         $id = $db->GenID("section_seq");
         $query = "INSERT INTO section (id, name, superapprover, headof, delegate, receptionist, address1,address2,city,province,p_code,country)"
                 ."VALUES ('$id', " . $db->QMagic($name) . ", " . $db->QMagic($superapprover) .", ".$db->QMagic($headof) .", ". $db->QMagic($delegate) .", ". $db->QMagic($receptionist) .", "
		 		. $db->QMagic($address1) . ", " . $db->QMagic($address2) . ", " . $db->QMagic($city) . ", " . $db->QMagic($province) . ", " . $db->QMagic($p_code) . ", " . $db->QMagic($country) .")";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         if (isset($refer)) { ?>
            <script language="JavaScript">
               window.location="<?php echo $refer; ?>";
            </script> <?php
         }
         echo "<table class=\"info\" width=\"100%\"><tr><td>Section Dictionary updated OK.</td></tr></table>";
         new_section_form($db);
	 paint_section_table($db);
         break;
      case "edit_section":
         edit_section_form($db, $section_id);
	 paint_section_table($db);
         break;
      case "update_section":
	// Filter the "--Select User--" Option
	if ($superapprover	== $cfg["sysadmin_email"] ) $superapprover="";
	if ($headof		== $cfg["sysadmin_email"] ) $headof="";
	if ($delegate		== $cfg["sysadmin_email"] ) $delegate="";
	if ($receptionist	== $cfg["sysadmin_email"] ) $receptionist="";

         $query = "UPDATE section SET name=" . $db->QMagic($name) . ", superapprover=" . $db->QMagic($superapprover) . ", headof=" . $db->QMagic($headof) . ", delegate=" . $db->QMagic($delegate) . ", receptionist=" . $db->QMagic($receptionist) . ", address1=" . $db->QMagic($address1) . ", address2=" . $db->QMagic($address2) .  ", city=" . $db->QMagic($city) . ", province=" . $db->QMagic($province) .  ", country=" . $db->QMagic($country) .  ", p_code=" . $db->QMagic($p_code) ." WHERE id='$section_id'";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         edit_section_form($db, $section_id);
         paint_section_table($db);
         break;
      case "enable_section":
         $query = "UPDATE section SET enabled='Y' WHERE id='$section_id'";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         edit_section_form($db, $section_id);
         paint_section_table($db);
         break;
      case "disable_section":
         $query = "UPDATE section SET enabled='N' WHERE id='$section_id'";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         edit_section_form($db, $section_id);
         paint_section_table($db);
         break;
      case "delete_section":
         if (!$db->Execute("DELETE FROM section WHERE id='$section_id'")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         echo "<table class=\"info\" width=\"100%\"><tr><td>Section $section_id was deleted OK.</td></tr></table>";
         new_section_form($db);
         paint_section_table($db);
         break;
      default:
         new_section_form($db);
	 paint_section_table($db);
   }
} else {
   echo "<table class=\"warn\" width=\"100%\"><tr><td>Insufficient privilege. See AssetMan administrator.</td></tr></table>";
}
require("footer.inc.php");
?>
