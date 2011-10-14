<?php require("common.inc.php"); ?>

<?php
function passwd_form($db) {
   global $username, $fullname; ?>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
      <form name="form1" method="post" action="passwd.php">
         <tr class="row_head"> 
            <td colspan="2" nowrap><b>Change Password for <?php echo $fullname; ?></b></td>
         </tr>
         <tr class="box_bg">
            <td colspan="2">&nbsp;</td>
         </tr>
         <tr class="box_bg">
            <td width="45%" nowrap align="right">Email address:</td>
            <td width="55%"><?php echo $username; ?></td>
         </tr>
         <tr class="box_bg">
            <td colspan="2" nowrap align="center"> &nbsp;  </td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">Current Password:</td>
            <td> 
               <input type="password" name="in_cur_pwd" size="30">
            </td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">New Password:</td>
            <td> 
               <input type="password" name="pwd1" size="30">
            </td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">Retype New Password:</td>
            <td> 
               <input type="password" name="pwd2" size="30">
            </td>
         </tr>
         <tr class="box_bg">
            <td colspan="2" nowrap align="center"> &nbsp;  </td>
         </tr>
         <tr class="box_bg">
            <td colspan="2" nowrap align="center"> 
		<button type="button" class="button_update" onClick="document.form1.submit();">Update</button>
		<button type="button" class="button_cancel" onClick="window.location='passwd.php?action=cancel'">Cancel</button>
            </td>
         </tr>
         <tr class="box_bg">
            <td colspan="2" nowrap align="center"> &nbsp;  </td>
         </tr>
      <input type="hidden" name="action" value="change">
      </form>
   </table>
   <script language="JavaScript">
      document.form1.in_cur_pwd.focus();
   </script> <?php
} ?>

<?php
$action = strtolower($action);
switch ($action) {
   case "cancel":
      echo "<table class=\"warn\" width=\"100%\"><tr><td>Change password canceled.</td></tr></table>";
      break;
   case "change":
      $db_cur_pwd = $db->Execute("SELECT password FROM users WHERE username='$username'");
      if (md5($in_cur_pwd) != $db_cur_pwd->fields["password"]) {
         echo "<table class=\"warn\" width=\"100%\"><tr><td>The current password was invalid.</td></tr></table>";
         passwd_form($db);
         break;
      }
      if ((strlen($pwd1) < 4) || ($pwd1 != $pwd2)) {
         echo "<table class=\"warn\" width=\"100%\"><tr><td>The new password is less than 4 characters or the passwords don't match.</td></tr></table>";
         passwd_form($db);
         break;
      }
      $query = "UPDATE users SET"
             . " password='" . md5($pwd1) . "'"
             . " WHERE username='$username'";
      if (!$db->Execute($query)) {
         echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
         break;
      }
      echo "<table class=\"info\" width=\"100%\"><tr><td>Success! Your password has been changed.</td></tr></table>";
      break;
   default:
   passwd_form($db); 
}
require("footer.inc.php");
?>
