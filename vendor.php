<?php require("common.inc.php"); ?>

<?php
function delete_vendor($db, $id) {
   $equipment = $db->Execute("SELECT COUNT(tag) FROM equipment WHERE vendor=$id");
   $po = $db->Execute("SELECT COUNT(draft_number) FROM po WHERE vendor=$id");
   if ($equipment->fields[0] > 0 || $po->fields[0] > 0) {
      echo "<table class=\"warn\" width=\"100%\"><tr><td>There are references to this vendor in other dictionaries. You may not delete this vendor.</td></tr></table>";
      return FALSE;
   }
   if (!$db->Execute("DELETE FROM vendor WHERE id=$id")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
   return TRUE;
} ?>

<?php
function delete_vendor_category($db, $id) {
   $equipment = $db->Execute("SELECT COUNT(tag) FROM vendor WHERE account_number=$id");
   if ($equipment->fields[0] > 0) {
      echo "<table class=\"warn\" width=\"100%\"><tr><td>There are references to this category in other dictionaries. You may not delete this vendor.</td></tr></table>";
      return FALSE;
   }
   if (!$db->Execute("DELETE FROM vendor_categories WHERE id=$id")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }
   return TRUE;
} ?>



<?php
function edit_vendor_form($db, $id) {
   if (!$vendor = $db->Execute("SELECT * FROM vendor WHERE id=$id")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   } ?>
<br>
  <table class="default" width="100%" border="0" cellspacing="0" cellpadding="1">
  <form action="vendor.php" method="post" name="update_vendor">
    <tr class="row_head"> 
      <td colspan="4"><b>Update Supplier Information</b></td>
    </tr>
         <tr class="box_bg"> <td colspan="4" align=center nowrap> &nbsp;   </td>  </tr>
    <tr class="box_bg">
      <td align="right">Name:</td>
      <td colspan="2"> 
        <input type="text" name="name" size="40"
           value="<?php echo $vendor->fields["name"]; ?>">
      </td>
      <td colspan="1"><?php 
	if ($vendor->fields["enabled"] <> 0) {
	        echo 'Enabled : <input type="checkbox" checked name="enabled">';
	}else{
	        echo 'Enabled : <input type="checkbox" name="enabled">';
	}?>

      </td>

    <tr class="box_bg">
      <td align="right">Vendor Category:</td>
      <td colspan="3"> 
<?  if (!$vendor_categories = $db->Execute("SELECT DISTINCT name,id FROM vendor_categories ORDER BY name")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   } 
	 echo $vendor_categories->GetMenu2("account_number", $vendor->fields["account_number"], FALSE,FALSE,0); ?>
      </td>
    </tr>
<!--
    <tr class="box_bg">
      <td align="right">Account Number:</td>
      <td colspan="3">
        <input type="text" name="account_number" size="30"
	    value="<?php echo $vendor->fields["account_number"]; ?>">
      </td>
    </tr>
-->

    <tr class="box_bg">
      <td align="right">Address 1:</td>
      <td colspan="3"> 
        <input type="text" name="address1" size="40"
           value="<?php echo $vendor->fields["address1"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Address 2:</td>
      <td colspan="3"> 
        <input type="text" name="address2" size="40"
           value="<?php echo $vendor->fields["address2"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">City:</td>
      <td> 
        <input type="text" name="city" size="20"
           value="<?php echo $vendor->fields["city"]; ?>">
      </td>
      <td align="right">Province/State:</td>
      <td> 
        <input type="text" name="province" size="12"
           value="<?php echo $vendor->fields["province"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Country:</td>
      <td> 
        <input type="text" name="country" size="20"
           value="<?php echo $vendor->fields["country"]; ?>">
      </td>
      <td align="right">Postal/ZIP Code:</td>
      <td> 
        <input type="text" name="p_code" size="12"
           value="<?php echo $vendor->fields["p_code"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Attention:</td>
      <td colspan="3">
        <input type="text" name="attn" size="40"
           value="<?php echo $vendor->fields["attn"]; ?>">
      </td>
    </tr>
    <tr class="row_head"> 
      <td colspan="4"><b>Main Contact Information</b></td>
    </tr>
    <tr class="box_bg">
      <td align="right">Phone:</td>
      <td> 
        <input type="text" name="main_phone" size="20"
           value="<?php echo $vendor->fields["main_phone"]; ?>">
      </td>
      <td align="right">FAX:</td>
      <td> 
        <input type="text" name="main_fax" size="20"
           value="<?php echo $vendor->fields["main_fax"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">E-Mail:</td>
      <td> 
        <input type="text" name="main_email" size="30"
           value="<?php echo $vendor->fields["main_email"]; ?>">
      </td>
      <td align="right">Web:</td>
      <td> 
        <input type="text" name="main_www" size="30"
           value="<?php echo $vendor->fields["main_www"]; ?>">
      </td>
    </tr>
    <tr class="row_head"> 
      <td colspan="4"><b>Technical or Support Contact Information</b></td>
    </tr>
    <tr class="box_bg">
      <td align="right">Phone:</td>
      <td> 
        <input type="text" name="tech_phone" size="20"
           value="<?php echo $vendor->fields["tech_phone"]; ?>">
      </td>
      <td align="right">FAX:</td>
      <td> 
        <input type="text" name="tech_fax" size="20"
           value="<?php echo $vendor->fields["tech_fax"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">E-Mail:</td>
      <td> 
        <input type="text" name="tech_email" size="30"
           value="<?php echo $vendor->fields["tech_email"]; ?>">
      </td>
      <td align="right">Web:</td>
      <td> 
        <input type="text" name="tech_www" size="30"
           value="<?php echo $vendor->fields["tech_www"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td valign="top" align="right">Comments:</td>
      <td colspan="3"> 
        <textarea name="comments" cols="50" rows="5"
           wrap="VIRTUAL"><?php echo $vendor->fields["comments"]; ?></textarea>
      </td>
    </tr>
         <tr class="box_bg"> <td colspan="4" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td colspan="4" align=center nowrap> 
	 	<button type="button" class="button_update"
            		onClick="if (valid_vendor_form(document.update_vendor)) { document.update_vendor.submit(); }">Update</button>
	 	<button type="button" class="button_delete"
            		onClick="if (isConfirmed('Are you sure you want to DELETE this Supplier ?')) { window.location='vendor.php?action=delete_vendor&id=<?php echo $id; ?>'; }">Delete</button>
	 	<button type="button" class="button_cancel" onClick="window.location='vendor.php?action=cancel'">Cancel</button>
            </td>
         </tr>
  <input type="hidden" name="action" value="update_vendor">
  <input type="hidden" name="id" value="<?php echo $id; ?>">
  </form>
  </table>
  <script language="JavaScript">
     document.update_vendor.name.focus();
  </script> <?php
} ?>

<?php
function edit_vendor_category_form($db, $id) {
   if (!$vendor_categories = $db->Execute("SELECT * FROM vendor_categories WHERE id=$id")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   } ?>
  <table class="default" width="100%" border="0" cellspacing="0" cellpadding="1">
  <form action="vendor.php" method="post" name="update_vendor_category">
    <tr class="row_head"> 
      <td colspan="4"><b>Update Supplier Category Information</b></td>
    </tr>
    <tr class="box_bg">
      <td align="right">Name:</td>
      <td colspan="3"> 
        <input type="text" name="name" size="40"
           value="<?php echo $vendor_categories->fields["name"]; ?>">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Account Number:</td>
      <td colspan="3">
        <input type="text" name="id" size="30"
	    value="<?php echo $id; ?>">
      </td>
    </tr>
         <tr class="box_bg"> <td colspan="4" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td colspan="4" align=center nowrap> 
	 	<button type="button" class="button_update"
            		onClick="if (valid_vendor_category_form(document.update_vendor_category)) { document.update_vendor_category.submit(); }">Update</button>
	 	<button type="button" class="button_delete"
            		onClick="if (isConfirmed('Are you sure you want to DELETE this Supplier Category?')) { window.location='vendor.php?action=delete_vendor_category&id=<?php echo $id; ?>'; }">Delete</button>
	 	<button type="button" class="button_cancel" onClick="window.location='vendors.php?action=cancel'">Cancel</button>
            </td>
         </tr>
  <input type="hidden" name="action" value="update_vendor_category">
  </form>
  </table>
  <script language="JavaScript">
     document.update_vendor_category.name.focus();
  </script> <?php
} ?>

<?php
function vendor_usage($db) {
   //if (!$v_use = $db->Execute("SELECT v.id as vid, v.name as vname, count(distinct p.draft_number) as vcount FROM vendor as v, po as p WHERE v.id=p.vendor GROUP BY v.id ORDER BY vcount")) {
   if (!$v_use = $db->Execute("SELECT v.id as vid, v.name as vname, v.enabled as venabled, count(distinct p.draft_number) as vcount FROM vendor as v LEFT OUTER JOIN po as p ON v.id=p.vendor GROUP BY v.id ORDER BY vcount DESC")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   }

   echo "<table  align=center>";
   echo "<tr class=box_head><td align=right><b>ID - </b></td><td><b>Name</b></td><td><b>Usage #</b></td><td>Enabled</td></tr>";
   $v_use->MoveFirst();
   while (!$v_use->EOF) {
   
      if ( $v_use->fields["vcount"] > 0 ) {
         echo "<tr class=vendor_used>";
      } else {
         echo "<tr class=vendor_unused>";
      } 
      echo "<td align=right>" . $v_use->fields["vid"] . " - </td>";
      echo "<td align=left> <a href=\"vendor.php?action=edit_vendor&id=". $v_use->fields["vid"] . "\">" . $v_use->fields["vname"] . "</a></td>";
      echo "<td align=right>" . $v_use->fields["vcount"] . "</td>";
      echo "<td align=right>" . $v_use->fields["venabled"] . "</td>";
      echo "</tr>";

      /* Update Vendor's Enabled according to the usage */
      /*
      if ( $v_use->fields["vcount"] > 0 ) {        $enabled=1;      }else{        $enabled=0;      }
      $query = "UPDATE vendor SET enabled=$enabled WHERE id=" . $v_use->fields["vid"];
      if (!$db->Execute($query)) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
      }*/

      $v_use->MoveNext();
   }
   echo "</table>";
} ?>

<?php
function main_vendor_form() {
  global $refer; ?>
 </table>
   <table class="default" border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
         <td align="center">
	 	<h3>Manage Suppliers</h3>
         </td>
      </tr>
      <tr>
         <td align="center">
            <a href="javascript:showdiv('new_vendor');">New Supplier</a> | 
            <a href="javascript:showdiv('select_vendor');">Edit Supplier</a> 
         </td>
      </tr>
      <tr>
         <td align="center">
            <a href="javascript:showdiv('new_vendor_category');">New Supplier Category</a> | 
            <a href="javascript:showdiv('select_vendor_category');">Edit Supplier Category</a> 
         </td>
      </tr>
      <tr>
         <td colspan=2 align="center">
            <a href="vendor.php?action=vendor_usage">Show Supplier Usage</a> 
         </td>
      </tr>
   </table></br>
<div id="new_vendor" style="display:none;">
<?php
	new_vendor_form();
?>
</div>
<div id="select_vendor" style="display:none;">
<?php
	select_vendor_form();
?>
</div>
<div id="new_vendor_category" style="display:none;">
<?php
	new_vendor_category_form();
?>
</div>
<div id="select_vendor_category" style="display:none;">
<?php
	select_vendor_category_form();
?>
</div>
   <?php
} ?>
<?php
function new_vendor_form() {
  global $refer, $db; ?>
	<br>
  <table class="default" width="100%" border="0" cellspacing="0" cellpadding="1">
   <form action="vendor.php" method="post" name="new_vendor">
    <tr class="row_head"> 
      <td colspan="4"><b>New Supplier Information</b></td>
    </tr>
         <tr class="box_bg"> <td colspan="4" align=center nowrap> &nbsp;   </td>  </tr>
    <tr class="box_bg">
      <td align="right">Name:</td>
      <td colspan="2"> 
        <input type="text" name="name" size="40">
      </td>
      <td colspan="1"> 
        Enabled : <input type="checkbox" checked name="enabled">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Vendor Category:</td>
      <td colspan="3"> 
<?  if (!$vendor_categories = $db->Execute("SELECT DISTINCT name,id FROM vendor_categories ORDER BY name")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   } 
	 echo $vendor_categories->GetMenu("account_number", "", FALSE); ?>
      </td>
<!--
      <td align="right">Account Number:</td>
      <td colspan="3"> 
        <input type="text" name="account_number" size="40">
      </td>
-->
    </tr>
    <tr class="box_bg">
      <td align="right">Address 1:</td>
      <td colspan="3"> 
        <input type="text" name="address1" size="40">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Address 2:</td>
      <td colspan="3"> 
        <input type="text" name="address2" size="40">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">City:</td>
      <td> 
        <input type="text" name="city" size="20">
      </td>
      <td align="right">Province/State:</td>
      <td> 
        <input type="text" name="province" size="12">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Country:</td>
      <td> 
        <input type="text" name="country" size="20">
      </td>
      <td align="right">Postal/ZIP Code:</td>
      <td> 
        <input type="text" name="p_code" size="12">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Attention:</td>
      <td colspan="3">
        <input type="text" name="attn" size="40">
      </td>
    </tr>
    <tr class="row_head"> 
      <td colspan="4"><b>Main Contact Information</b></td>
    </tr>
    <tr class="box_bg">
      <td align="right">Phone:</td>
      <td> 
        <input type="text" name="main_phone" size="20">
      </td>
      <td align="right">FAX:</td>
      <td> 
        <input type="text" name="main_fax" size="20">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">E-Mail:</td>
      <td> 
        <input type="text" name="main_email" size="30">
      </td>
      <td align="right">Web:</td>
      <td> 
        <input type="text" name="main_www" size="30">
      </td>
    </tr>
    <tr class="row_head"> 
      <td colspan="4"><b>Technical or Support Contact Information</b></td>
    </tr>
    <tr class="box_bg">
      <td align="right">Phone:</td>
      <td> 
        <input type="text" name="tech_phone" size="20">
      </td>
      <td align="right">FAX:</td>
      <td> 
        <input type="text" name="tech_fax" size="20">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">E-Mail:</td>
      <td> 
        <input type="text" name="tech_email" size="30">
      </td>
      <td align="right">Web:</td>
      <td> 
        <input type="text" name="tech_www" size="30">
      </td>
    </tr>
    <tr class="box_bg">
      <td valign="top" align="right">Comments:</td>
      <td colspan="3"> 
        <textarea name="comments" cols="50" rows="5" wrap="VIRTUAL"></textarea>
      </td>
    </tr>
         <tr class="box_bg"> <td colspan="4" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td colspan="4" align=center nowrap> 
	 	<button type="button" class="button_enter"
            		onClick="if (valid_vendor_form(document.new_vendor)) { document.new_vendor.submit(); }">Enter</button>
	 	<button type="button" class="button_reset" onClick="document.new_vendor.reset();">Reset</button>
	 	<button type="button" class="button_cancel" onClick="window.location='vendor.php?action=cancel';">Cancel</button>
            </td>
         </tr>
   <?php
   if (isset($refer)) { ?>
      <input type="hidden" name="refer" value="<?php echo $refer; ?>"> <?php
   } ?>
   <input type="hidden" name="action" value="insert_vendor">
   </form>
   </table>
   <script language="JavaScript">
      document.new_vendor.name.focus();
   </script> <?php
} ?>

<?php
function new_vendor_category_form() {
  global $refer; ?>
  <br>
  <table class="default" width="100%" border="0" cellspacing="0" cellpadding="1">
   <form action="vendor.php" method="post" name="new_vendor_category">
         <tr class="box_bg"> <td colspan="4" align=center nowrap> &nbsp;   </td>  </tr>
    <tr class="row_head"> 
      <td colspan="4"><b>New Supplier Information</b></td>
    </tr>
    <tr class="box_bg">
      <td align="right">Name:</td>
      <td colspan="3"> 
        <input type="text" name="name" size="40">
      </td>
    </tr>
    <tr class="box_bg">
      <td align="right">Account Number:</td>
      <td colspan="3"> 
        <input type="text" name="id" size="11">
      </td>
    </tr>
         <tr class="box_bg"> <td colspan="4" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td colspan="4" align=center nowrap> 
	 	<button type="button" class="button_enter"
            		onClick="if (valid_vendor_category_form(document.new_vendor_category)) { document.new_vendor_category.submit(); }">Enter</button>
	 	<button type="button" class="button_reset" onClick="document.new_vendor_category.reset();">Reset</button>
	 	<button type="button" class="button_cancel" onClick="window.location='vendor.php?action=cancel';">Cancel</button>
            </td>
         </tr>
   <?php
   if (isset($refer)) { ?>
      <input type="hidden" name="refer" value="<?php echo $refer; ?>"> <?php
   } ?>
   <input type="hidden" name="action" value="insert_vendor_category">
   </form>
   </table>
   <script language="JavaScript">
      document.new_vendor_category.name.focus();
   </script> <?php
} ?>


<?php
function select_vendor_form() {
  global $db;
   if (!$vendors = $db->Execute("SELECT DISTINCT name, id FROM vendor ORDER BY name")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   } ?>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
   <form action="vendor.php" method="post" name="select_vendor">
    <tr class="row_head"> 
      <td align="center" colspan="2" nowrap><b>Update Supplier Information</b></td>
    </tr>
    <tr class="box_bg">
      <td align="center">Choose Supplier:
      
         <?php echo $vendors->GetMenu("id", "", FALSE, FALSE, 0,
                                   "onChange='document.select_vendor.submit();'"); ?>
      </td>
    </tr>
   <input type="hidden" name="action" value="edit_vendor">
   </form>
   </table> <?php
} ?>

<?php
function select_vendor_category_form() {
  global $db;
   if (!$vendor_categories = $db->Execute("SELECT DISTINCT name, id FROM vendor_categories ORDER BY name")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   } ?>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
   <form action="vendor.php" method="post" name="select_vendor_category">
    <tr class="row_head"> 
      <td align="center" colspan="2" nowrap><b>Update Supplier Category Information</b></td>
    </tr>
    <tr class="box_bg">
      <td align="center">Choose Supplier Category:
      
         <?php echo $vendor_categories->GetMenu("id", "", FALSE, FALSE, 0,
                                   "onChange='document.select_vendor_category.submit();'"); ?>
      </td>
    </tr>
   <input type="hidden" name="action" value="edit_vendor_category">
   </form>
   </table> <?php
} ?>




<?php
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
         echo "<table class=\"warn\" width=\"100%\"><tr><td>Supplier Dictionary update canceled.</td></tr></table>";
         main_vendor_form();
         break;
      case "delete_vendor":
         if (delete_vendor($db, $id)) {
            echo "<table class=\"info\" width=\"100%\"><tr><td>Supplier Dictionary updated OK.</td></tr></table>";
         main_vendor_form();
         }
         break;
      case "delete_vendor_category":
         if (delete_vendor_category($db, $id)) {
            echo "<table class=\"info\" width=\"100%\"><tr><td>Supplier Dictionary updated OK.</td></tr></table>";
         main_vendor_form();
         }
         break;
      case "vendor_usage";
         vendor_usage($db);
         break;
      case "edit_vendor";
         edit_vendor_form($db, $id);
         break;
      case "edit_vendor_category";
         edit_vendor_category_form($db, $id);
         break;
      case "insert_vendor":
         if (!valid_char_1($name)) {
            echo "<table class=\"warn\" width=\"100%\"><tr><td>You must enter a valid supplier name.</td></tr></table>";
            new_vendor_form();
            break;
         }
	 if ($enabled == "on") { $enabled=1; } else { $enabled=0; }
         $id = $db->GenID("vendor_seq");
         $query = "INSERT INTO vendor (id, name, account_number, attn, address1, address2, city, province, country, p_code, main_phone, main_fax, main_email, main_www, tech_phone, tech_fax, tech_email, tech_www, comments, enabled)"
                . " VALUES ('$id', " . $db->QMagic($name) . "," . $db->QMagic($account_number) . ", " . $db->QMagic($attn) . ", " . $db->QMagic($address1) . ", "
                . $db->QMagic($address2) . ", " . $db->QMagic($city) . ", " . $db->QMagic($province) . ", " . $db->QMagic($country) . ", "
                . $db->QMagic($p_code) . ", '$main_phone', '$main_fax', " . $db->QMagic($main_email) . ", " . $db->QMagic($main_www) . ", "
                . $db->QMagic($tech_phone) . ", '$tech_fax', " . $db->QMagic($tech_email) . ", " . $db->QMagic($tech_www) . ", "
                . $db->QMagic($comments) . "," . $db->QMagic($enabled) . ")";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         if (isset($refer)) { ?>
            <script language="JavaScript">
               window.location="<?php echo $refer; ?>";
            </script> <?php
         }
         echo "<table class=\"info\" width=\"100%\"><tr><td>Supplier Dictionary updated OK.</td></tr></table>";
         main_vendor_form();
         break;
      case "insert_vendor_category":
         if (!valid_char_1($name)) {
            echo "<table class=\"warn\" width=\"100%\"><tr><td>You must enter a valid supplier category name.</td></tr></table>";
            new_vendor_category_form();
            break;
         }
         $query = "INSERT INTO vendor_categories (id, name)"
                . " VALUES ('$id', " . $db->QMagic($name) . ")";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         if (isset($refer)) { ?>
            <script language="JavaScript">
               window.location="<?php echo $refer; ?>";
            </script> <?php
         }
         echo "<table class=\"info\" width=\"100%\"><tr><td>Supplier Dictionary updated OK.</td></tr></table>";
         main_vendor_form();
         break;
      case "new":
         new_vendor_form();
          break;
      case "update_vendor":
         if (!valid_char_1($name)) {
            echo "<table class=\"warn\" width=\"100%\"><tr><td>You must enter a valid vendor name.</td></tr></table>";
            edit_vendor_form($db, $id);
            break;
         }
	 if ($enabled == "on") { $enabled=1; } else { $enabled=0; }

         $query = "UPDATE vendor SET"
                . " name=" . $db->QMagic($name) . ", attn=" . $db->QMagic($attn) . ","
                . " account_number=" . $db->QMagic($account_number) . ","
                . " address1=" . $db->QMagic($address1) . ", address2=" . $db->QMagic($address2) . ","
                . " city=" . $db->QMagic($city) . ", province=" . $db->QMagic($province) . ","
                . " country=" . $db->QMagic($country) . ", p_code=" . $db->QMagic($p_code) . ","
                . " main_phone='$main_phone', main_fax='$main_fax',"
                . " main_email=" . $db->QMagic($main_email) . ", main_www=" . $db->QMagic($main_www) . ","
                . " tech_phone='$tech_phone', tech_fax='$tech_fax',"
                . " tech_email=" . $db->QMagic($tech_email) . ", tech_www=" . $db->QMagic($tech_www) . ","
   	          . " enabled=" . $db->QMagic($enabled) . ","
   	          . " comments=" . $db->QMagic($comments) . " WHERE id=$id";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         echo "<table class=\"info\" width=\"100%\"><tr><td>Supplier Dictionary updated OK.";
         main_vendor_form();
         break;
      case "update_vendor_category":
         if (!valid_char_1($name)) {
            echo "<table class=\"warn\" width=\"100%\"><tr><td>You must enter a valid vendor category name.</td></tr></table>";
            edit_vendor_category_form($db, $id);
            break;
         }
         $query = "UPDATE vendor_categories SET name=" . $db->QMagic($name) . " WHERE id=$id";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
         main_vendor_form();
            break;
         }
         echo "<table class=\"info\" width=\"100%\"><tr><td>Supplier Dictionary updated OK.</td></tr></table>";
         main_vendor_form();
         break;
      default:
         main_vendor_form();
   }
} else {
   echo "<table class=\"warn\" width=\"100%\"><tr><td>Insufficient privilege.</td></tr></table>";
}
require("footer.inc.php");
?>
