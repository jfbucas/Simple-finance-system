<?php require("common.inc.php"); ?>

<?php
function new_user_form($db) { ?>
	</br>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
      <form name="form1" method="post" action="admin.php">
         <tr class="row_head"> 
            <td colspan="2" nowrap><b>New User Information</b></td>
         </tr>
         <tr class="box_bg"> <td colspan="2" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td nowrap align="right">Email Address:</td>
            <td> 
               <input type="text" name="uid" size="48">
            </td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">Full Name:</td>
            <td> 
               <input type="text" name="given" size="30">
            </td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">Password:</td>
            <td> 
               <input type="password" name="pwd1" size="16">
            </td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">Retype Password:</td>
            <td> 
               <input type="password" name="pwd2" size="16" value="">
            </td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">Phone:</td>
            <td> 
               <input type="text" name="phone" size="30">
            </td>
         </tr>

          <tr class="box_bg">
            <td nowrap align="right">Role:</td>
		<?php
   		  $roles = $db->Execute("SELECT fullname, id FROM roles ORDER BY fullname"); 
		?>
            <td> 
            <?php echo $roles->GetMenu("role", "", FALSE); ?>
            </td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">Section:</td>
		<?php
   		  $sections = $db->Execute("SELECT name, id FROM section WHERE enabled='Y' ORDER BY name"); 
		?>
            <td> 
            <?php echo $sections->GetMenu("section", "", FALSE); ?>
            </td>
         </tr>
         <tr class="box_bg"> <td colspan="2" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td colspan="2" align=center nowrap> 
	 	<button type="button" class="button_enter" onClick="document.form1.submit();">Enter</button>
	 	<button type="button" class="button_reset" onClick="document.form1.reset();">Reset</button>
	 	<button type="button" class="button_cancel" onClick="window.location='admin.php?action=cancel'">Cancel</button>
            </td>
         </tr>
      <input type="hidden" name="action" value="insert_user">
      </form>
   </table>
   <script language="JavaScript">
      document.form1.uid.focus();
   </script> <?php
} ?>
<?php
function edit_user_form($db, $uid) {
   $user = $db->Execute("SELECT username, fullname, email, priv, phone FROM users WHERE username='$uid'"); ?>
	<br>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
      <form name="form2" method="post" action="admin.php">
         <tr class="row_head"> 
            <td colspan="2" nowrap><b>Edit User Information</b></td>
         </tr>
         <tr class="box_bg"> <td colspan="2" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td nowrap align="right">Email Address:</td>
            <td><?php echo $uid; ?></td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">Full Name:</td>
            <td> 
               <input type="text" name="given" size="30"
                  value="<?php echo $user->fields["fullname"]; ?>">
            </td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">Password:</td>
            <td> 
               <input type="password" name="pwd1" size="16">
            </td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">Retype Password:</td>
            <td> 
               <input type="password" name="pwd2" size="16" value="">
            </td>
         </tr>
         <!--
         <tr class="box_bg">
            <td nowrap align="right">E-Mail Address:</td>
            <td> 
               <input type="text" name="email_addr" size="30"
                  value="<?php echo $user->fields["email"]; ?>">
            </td>
         </tr>
          -->
         <tr class="box_bg">
            <td nowrap align="right">Phone Number:</td>
            <td> 
               <input type="text" name="phone" size="30"
                  value="<?php echo $user->fields["phone"]; ?>">
            </td>
         </tr>

         <tr class="box_bg">
            <td nowrap align="right">Role:</td>
		<?php
   		  $roles = $db->Execute("SELECT fullname, id FROM roles ORDER BY fullname"); 
   		  $user_role = $db->Execute("SELECT roles.fullname FROM roles,users,`users-roles` WHERE roles.id=`users-roles`.role AND users.username=`users-roles`.username AND users.username=\"". $user->fields["username"]. "\""); 
		?>
            <td> 
            <?php echo $roles->GetMenu("role", $user_role->fields["fullname"], FALSE); ?>
            </td>
         </tr>
        <tr class="box_bg">
            <td nowrap align="right">Section:</td>
		<?php
   		  $sections = $db->Execute("SELECT name, id FROM section WHERE enabled='Y' ORDER BY name"); 
   		  $user_section = $db->Execute("SELECT name FROM section,users,`users-sections` WHERE section.id=`users-sections`.section_id AND users.username=`users-sections`.username AND users.username=\"". $user->fields["username"]. "\""); 
		?>
            <td> 
            <?php echo $sections->GetMenu("section", $user_section->fields["name"], FALSE); ?>
            <?php //echo $sections->GetMenu2("section", $user_section->fields["name"], FALSE, FALSE,0,""); ?>
            </td>
         </tr>
         <tr class="box_bg"> <td colspan="2" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td colspan="2" nowrap align="center"> 
	 	<button type="button" class="button_update" onClick="document.form2.submit();">Update</button>
	 	<button type="button" class="button_delete"
                  onClick="if (isConfirmed('Are you sure you want to DELETE this User ?')) { window.location='admin.php?action=delete_user&uid=<?php echo $uid; ?>'; }">Delete</button>
	 	<button type="button" class="button_cancel" onClick="window.location='admin.php?action=cancel'">Cancel</button>
            </td>
         </tr>
      <input type="hidden" name="uid" value="<?php echo $uid; ?>">
      <input type="hidden" name="action" value="update_user">
      </form>
   </table>
   <script language="JavaScript">
      document.form2.given.focus();
   </script> <?php
} ?>


<?php
function new_role_form($db) { ?>
	<br>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
      <form name="form_new_role" method="post" action="admin.php">
         <tr class="row_head"> 
            <td colspan="2" nowrap><b>New Role Information</b></td>
         </tr>
         <tr class="box_bg"> <td colspan="2" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td nowrap align="right">Role short name:</td>
            <td> 
               <input type="text" name="role_shortname" size="50">
            </td>
         </tr>
        <tr class="box_bg">
            <td nowrap align="right">Role Name:</td>
            <td> 
               <input type="text" name="role_fullname" size="50">
            </td>
         </tr>
         <tr class="box_bg"> <td colspan="2" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td colspan="2" align=center nowrap> 
	 	<button type="button" class="button_enter" onClick="document.form_new_role.submit();">Enter</button>
	 	<button type="button" class="button_reset" onClick="document.form_new_role.reset();">Reset</button>
	 	<button type="button" class="button_cancel" onClick="window.location='admin.php?action=cancel'">Cancel</button>
            </td>
         </tr>
      <input type="hidden" name="action" value="insert_role">
      </form>
   </table>
   <script language="JavaScript">
      document.form_new_role.role_name.focus();
   </script> <?php
} ?>

<?php
function edit_role_form($db, $role_id) {
   $role = $db->Execute("SELECT id, shortname, fullname FROM roles WHERE id='$role_id'"); ?>
	<br>
   <table class="default" align="center" border="0" cellspacing="0" cellpadding="1">
      <form name="form_edit_role" method="post" action="admin.php">
         <tr class="row_head"> 
            <td colspan="2" nowrap><b>Edit Role Information</b></td>

         </tr>
         <tr class="box_bg"> <td colspan="2" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td nowrap align="right">Rol Name:</td>
            <td>
	       <input type="text" name="role_shortname" size="50"
                  value="<?php echo $role->fields["shortname"]; ?>">
	    </td>
         </tr>
         <tr class="box_bg">
            <td nowrap align="right">Rol Name:</td>
            <td>
	       <input type="text" name="role_fullname" size="50"
                  value="<?php echo $role->fields["fullname"]; ?>">
	    </td>
         </tr>
         <tr class="box_bg"> <td colspan="2" align=center nowrap> &nbsp;   </td>  </tr>
         <tr class="box_bg">
            <td colspan="2" nowrap align="center"> 
	 	<button type="button" class="button_update" onClick="document.form_edit_role.submit();">Update</button>
	 	<button type="button" class="button_delete"
                  onClick="if (isConfirmed('Are you sure you want to DELETE this Role ?')) { window.location='admin.php?action=delete_role&role_id=<?php echo $role_id; ?>'; }">Delete</button>
	 	<button type="button" class="button_cancel" onClick="window.location='admin.php?action=cancel'">Cancel</button>
            </td>
         </tr>
      <input type="hidden" name="role_id" value="<?php echo $role->fields["id"]; ?>">
      <input type="hidden" name="action" value="update_role">
      </form>
   </table>
   <script language="JavaScript">
      document.form_edit_role.given.focus();
   </script> <?php
} ?>


<?php
function mailing_list($db) { 
	$summary = $db->Execute("SELECT username FROM users ORDER BY username");
	$dest = "";
	while (!$summary->EOF) {
		$dest .= $summary->fields["username"] . ", ";
         	$summary->MoveNext();
	}

	return $dest;
} ?>
 
<?php
function paint_table($db) { 
   $summary = $db->Execute("SELECT username, fullname, email, phone, priv FROM users ORDER BY username");
   //$section_summary = $db->Execute("SELECT * FROM section LIMIT 1,1000");
   $role_summary = $db->Execute("SELECT id, shortname,fullname FROM roles");?>

    <table border="0" width="100%" cellspacing="0" cellpadding="1" class="default">
    <tr><td align="center">  <hr>  </td></tr>
    <tr><td align="center">
      <img src="images/flechebas.png" border="0">
      <a href="#Users">Users</a> | 
      <a href="section.php">Sections</a> | 
      <a href="#Roles">Roles</a> |
      <a href="#Mailing">Send Message</a>
      <img src="images/flechebas.png" border="0">
    </td></tr>
    <tr><td align="center">  <hr>  </td></tr>
    </table></br>
<?php
//Users table
?>
   <a name="Users">
   <table border="0" width="100%" cellspacing="0" cellpadding="1" class="default"><tr><td align="center">
	<button type="button" class="button_add" onClick="window.location='admin.php?action=new_user'">Add User</button>
   </td></tr></table></br>
   <table class="small" width="100%" border="0" cellspacing="0" cellpadding="1">
      <tr class="row_head"> 
         <td><b>E-Mail Address</b></td>
         <td><b>Full Name</b></td>
         <td><b>Privilege</b></td>
         <td><b>Phone</b></td>
         <td><b>Role</b></td>
         <td><b>Section</b></td>
      </tr> <?php
      $i = 1;
      while (!$summary->EOF) {
         if ($i % 2 == 0) {
            echo "<tr class=\"row_even\" ";
         } else {
            echo "<tr class=\"row_odd\" ";
         } 
         echo "onclick=\"location.href='admin.php?action=edit_user&uid=" . $summary->fields["username"] . "'\">";
	 
	 ?> 
            <td>
		<?php
		$uid = $summary->fields["username"];
		?>
               <!--a href="admin.php?action=edit_user&uid=<?php echo $summary->fields["username"]; ?>"-->
                  <?php echo $summary->fields["username"]; ?><!--/a-->
            </td>
            <td><?php echo $summary->fields["fullname"]; ?></td>
            <td> <?php
               $x = $summary->fields["priv"];
               switch ($x) {
                  case "1":
                     echo "Read Only";
                     break;
                  case "2":
                     echo "Read / Write";
                     break;
                  case "3":
                     echo "Supervisor";
                     break;
                  case "4":
                     echo "Administrator";
                     break;
               } ?>
            </td>
            <td><?php echo $summary->fields["phone"]; ?></td>
            <td>
               <?php 
		 $userrole = $db->Execute("SELECT role FROM `users-roles` WHERE username='$uid'");
		 $userroleid = $userrole->fields["role"];
		 $userrolefuna=$db->Execute("SELECT fullname FROM `roles` WHERE id='$userroleid'");
		 $userrolefullname = $userrolefuna->fields["fullname"];
		 echo $userrolefullname;
	       ?>
            </td>
	    <td>
	       <?php 
    		 $usersection_summary = $db->Execute("SELECT section_id FROM `users-sections` WHERE username='$uid'");
      		 while (!$usersection_summary->EOF) {
		   $usersectionid = $usersection_summary->fields["section_id"];
		   $usersectionna = $db->Execute("SELECT name FROM `section` WHERE id='$usersectionid'");
		   $usersectionname = $usersectionna->fields["name"];
		   echo $usersectionname . "  ";
         	   $usersection_summary->MoveNext();}
	       ?>
	    </td>
         </tr> <?php
         $i++;
         $summary->MoveNext();
      } ?>
   </table></br>
<?php

//Roles table
?>
   <a name="Roles">
   <table border="0" width="100%" cellspacing="0" cellpadding="1" class="default"><tr><td align="center">
	<button type="button" class="button_add" onClick="window.location='admin.php?action=new_role'">Add Role</button>
   </td></tr></table></br>
   <table class="small" width="100%" border="0" cellspacing="0" cellpadding="1">
      <tr class="row_head"> 
        <td><b>Role Short Name</b></td>
        <td><b>Role Name</b></td>
      </tr> <?php
      $i = 1;
      while (!$role_summary->EOF) {
         if ($i % 2 == 0) {
            echo "<tr class=\"row_even\" ";
         } else {
            echo "<tr class=\"row_odd\" ";
         } 
         echo "onclick=\"location.href='admin.php?action=edit_role&role_id=" . $role_summary->fields["id"] . "'\">";
	 
	 ?> 
            <td>
               <!--a href="admin.php?action=edit_role&role_id=<?php echo $role_summary->fields["id"]; ?>"-->
                  <?php echo $role_summary->fields["shortname"]; ?><!--/a-->
            </td>
            <td>
               <?php echo $role_summary->fields["fullname"]; ?><!--/a-->
            </td>
          </tr> <?php
         $i++;
         $role_summary->MoveNext();
      } ?>
   </table></br>
   


	<a name="Mailing">
	<table border="0" width="100%" cellspacing="0" cellpadding="1" class="default">
	<tr><td align="center">
	<b>Send a message</b>
	</td></tr></table></br>

	<form action="admin.php" method="post" name="form_mailing_list">
	<table class="small" width="100%" border="0" cellspacing="0" cellpadding="1">
	<tr class="box_head">
	<td align="left" width=100%><b>Send a message to all the users :</b></td>
	</tr>
	<tr class="box_bg">
	         <td width=100%>
			<textarea cols=100% rows="20" name="mailtext" wrap="virtual" class="PO_comment"  ></textarea>
 		</td>
	</tr>
	<tr class="box_bg">
	         <td align=center>
	 		<button type="button" class="button_enter" onClick="document.form_mailing_list.submit();" nowrap>Send to all users</button>
			<input type="hidden" name="action" value="send_mailing_list">
 		</td>
	</tr>
	<tr><td align="left">
	<?php
	echo mailing_list($db);
	?>
         </td>
         </tr>
	</table></br>
	</form>
 <?php
} ?>

<?php
if ($user_role == $cfg["admin"]) {
   $action = strtolower($action);
   switch ($action) {
      case "cancel":
         paint_table($db);
         break;
      case "delete_user":
         if (!$db->Execute("DELETE FROM users WHERE username='$uid'")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         if (!$db->Execute("DELETE FROM `users-roles` WHERE username='$uid'")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         if (!$db->Execute("DELETE FROM `users-sections` WHERE username='$uid'")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         echo "<table class=\"info\" width=\"100%\"><tr><td>User $uid was deleted OK.</td></tr></table>";
         paint_table($db);
         break;
      case "delete_role":
         if (!$db->Execute("DELETE FROM roles WHERE id='$role_id'")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         echo "<table class=\"info\" width=\"100%\"><tr><td>Role $id was deleted OK.</td></tr></table>";
         paint_table($db);
         break;
      /*case "delete_section":
         if (!$db->Execute("DELETE FROM section WHERE id='$section_id'")) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         echo "<table class=\"info\" width=\"100%\"><tr><td>Section $section_id was deleted OK.</td></tr></table>";
         paint_table($db);
         break;*/
      case "edit_user":
         edit_user_form($db, $uid);
         break;
     case "edit_role":
         edit_role_form($db, $role_id);
         break;
     /*case "edit_section":
         edit_section_form($db, $section_id);
         break;*/
      case "insert_user":
         if ($uid == "") {
            echo "<table class=\"warn\" width=\"100%\"><tr><td>The Username field is required.</td></tr></table>";
            new_user_form($db);
            break;
         }
         if ((strlen($pwd1) < 6) || ($pwd1 != $pwd2)) {
            echo "<table class=\"warn\" width=\"100%\"><tr><td>The password is less than 6 characters or the passwords don't match.</td></tr></table>";
            new_user_form($db);
            break;
         }
         $query1 = "INSERT INTO users (username, fullname, email, phone, priv, password)"
                . " VALUES (" . $db->QMagic($uid) . ", " . $db->QMagic($given) . ", "
                . $db->QMagic($uid) . ", " . $db->QMagic($phone) . ", '$privilege', '" . md5($pwd1) . "')";
         if (!$db->Execute($query1)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
	 $query2 = "INSERT INTO `users-roles` (username, role)"
	       ."VALUES (" . $db->QMagic($uid) . ",'$role' )";
         if (!$db->Execute($query2)) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
	 }
	 $query3 = "INSERT INTO `users-sections` (username, section_id)"
	       ."VALUES (" . $db->QMagic($uid) . ",'$section' )";
        if (!$db->Execute($query3)) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
	 }
         paint_table($db);
         break;
         
      case "insert_role":
         if ($role_shortname == "") {
            echo "<table class=\"warn\" width=\"100%\"><tr><td>The Role's short name field is required.</td></tr></table>";
            new_role_form($db);
            break;
         }
         $query = "INSERT INTO roles (shortname, fullname)"
                . " VALUES (" . $db->QMagic($role_shortname) . ", " . $db->QMagic($role_fullname) . ")";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         paint_table($db);
         break;
      case "new_user":
         new_user_form($db);
         break;
      case "new_role":
         new_role_form($db);
         break;
      case "update_user":
         if ($pwd1 == "") {
            $query1 = "UPDATE users SET"
                   . " fullname=" . $db->QMagic($given) . ", email=" . $db->QMagic($uid) . ", priv='$privilege'" . ", phone=" . $db->QMagic($phone)
                   . " WHERE username='$uid'";
         } else if ((strlen($pwd1) >= 4) && ($pwd1 == $pwd2)) {
            $query1 = "UPDATE users SET"
                   . " fullname=" . $db->QMagic($given) . ", email=" . $db->QMagic($uid) . ", priv='$privilege'" . ", phone=" . $db->QMagic($phone)
                   . ", password='" . md5($pwd1) . "'"
                   . " WHERE username='$uid'";
         } else {
            echo "<table class=\"warn\" width=\"100%\"><tr><td>The password is less than 4 characters or the passwords don't match.</td></tr></table>";
           edit_user_form($db, $uid);
            break;
         }
         if (!$db->Execute($query1)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
	 $query2 = "UPDATE `users-roles` SET role='$role' WHERE username='$uid'";
         if (!$db->Execute($query2)) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
	 }
	 $query3 = "UPDATE `users-sections` SET section_id=$section WHERE username=" . $db->QMagic($uid) . "";
        if (!$db->Execute($query3)) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
//	 }
}
         paint_table($db);
         break;
      case "update_role":
         $query = "UPDATE roles SET shortname=" . $db->QMagic($role_shortname) . ", fullname=" . $db->QMagic($role_fullname) . " WHERE id='$role_id'";
         if (!$db->Execute($query)) {
            echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
            break;
         }
         paint_table($db);
         break;
      case "send_mailing_list":
	 $mail_headers = 'From: ' . $cfg["sysadmin_email"] . "\r\n" .
            'Reply-To: ' . $cfg["sysadmin_email"] . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        echo "<table class=\"info\" width=\"100%\"><tr><td>";
	echo "Sending -->" . $mailtext . "<-- to " . mailing_list($db);
	echo "</td></tr></table>";
        do_mail(mailing_list($db),
              "Finance eSystems Information",
              $mailtext,
              $mail_headers);
         paint_table($db);
         break;
      default:
      paint_table($db); 
   }
} else {
   echo "<table class=\"warn\" width=\"100%\"><tr><td>Insufficient privilege.</td></tr></table>";
}
require("footer.inc.php");
?>
