<?php require("common.inc.php"); ?>

<?php
function section_form($db) {
   if (!$section = $db->Execute("SELECT name, id FROM section ORDER BY name")) {
      echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
      return FALSE;
   } ?>
   <table class="default" border="0" cellspacing="0" cellpadding="1" align="center">
   <form action="search_section.php" method="post" name="form1">
    <tr class="row_head">
      <td align="center" colspan="2" nowrap><b>View Section Information</b></td>
    </tr>
    <tr class="box_bg">
      <td align="right">Choose Section:</td>
      <td>
         <?php echo $section->GetMenu("id", "", FALSE, FALSE, 0,
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
         window.location="search_asset.php?action=view_section&section_id=<?php echo $id; ?>";
      </script> <?php
      break;
   default:
      section_form($db);
}
require("footer.inc.php");
?>
