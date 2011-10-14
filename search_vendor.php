<?php require("common.inc.php"); ?>

<?php
function vendor_form($db) {
   if (!$vendors = $db->Execute("SELECT DISTINCT name, id FROM vendor WHERE enabled > 0 ORDER BY name")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   } ?>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
   <form action="search_vendor.php" method="post" name="form1">
    <tr class="row_head"> 
      <td align="center" colspan="2" nowrap><b>View Supplier Information</b></td>
    </tr>
    <tr class="box_bg">
      <td align="right">Choose Supplier:</td>
      <td>
         <?php echo $vendors->GetMenu("id", "", FALSE, FALSE, 0,
                              "onChange='document.form1.submit();'"); ?>
      </td>
    </tr>
   <input type="hidden" name="action" value="view">
   </form>
   </table> <?php
} ?>

<?php
$action = strtolower($action);
switch ($action) {
   case "view": ?>
      <script language="JavaScript">
         window.location="search_asset.php?action=view_vendor&vendor_id=<?php echo $id; ?>";
      </script> <?php
      break;
   default:
      vendor_form($db);
}
require("footer.inc.php");
?>
