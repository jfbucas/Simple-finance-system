<?php
require("config.inc.php");
session_name($cfg["session"]);
session_start();
unset($username, $priv, $user_role, $fullname);

require("grab_globals.inc.php");
require("connection.inc.php");
require("mail.inc.php");
require("header.inc.php");

function user_form() {
	global $cfg;
?>
   <table class="default" border="0" align="center" cellpadding="10" width="80%"><tr><td>
      <table class="default" border="0" cellpadding="0" cellspacing="0" width="100%">
         <tr>
            <td align="center">
                  <h2><?php echo $cfg["title"]; ?></h2>
            <!--/td>
            <td align="center"-->
               <img src="images/seal.gif" alt="Logo" border="0"></br></br>
	       <?php
			if ( $cfg["testing"] == "dev" ) {
				//echo "<font color=red>Development website, do not use unless you know what you're doing!</font>";
			} else if ( $cfg["testing"] == "rc" ) {
				echo "<font color=red>Release Candidate website, please report any bug you find, any ideas you have</font>";
			}
		?>
            </td>
         </tr>
      </table>
      <table class="login" align="center" border="0" cellpadding="1" cellspacing="0">
         <form action="login.php" method="post" name="form1">
         <tr class="row_head">
            <td align="center" colspan="2"><b> Log In </b></td>
         </tr>
         <tr class="box_bg">
            <td align="center" colspan="2">&nbsp;</td>
         </tr>
         <tr class="box_bg">
            <td width="45%" align="right">Login:</td>
            <td width="55%" align="left"><input type="text" name="uid" size="30"></td>
         </tr>
         <tr class="box_bg">
            <td align="right">Password:
            <td align="left"><input type="password" name="pwd" size="30"></td>
         </tr>
         <tr class="box_bg">
            <td colspan="2" align="center"> <font size="-2"><a href="login.php?action=forgot_password">Forgot your password ?</a></font></td>
         </tr>
         <tr class="box_bg">
                  <td colspan="2" align="center">&nbsp;</td>
         </tr>
         <tr class="box_bg">
            <td colspan="2" align="center">
               <input type="submit" class="button_login" value="login">
            </td>
         </tr>
         <tr class="box_bg">
                  <td colspan="2" align="center">&nbsp;</td>
         </tr>
         <input type="hidden" name="action" value="check_user">
         </form>
      </table>
   </td></tr></table>
   <script language="JavaScript">
      document.form1.uid.focus();
   </script> <?php
}

function user_forgot_password_form() {
	global $cfg;
?>
   <table class="default" border="0" align="center" cellpadding="10" width="80%"><tr><td>
      <table class="default" border="0" cellpadding="0" cellspacing="0" width="100%">
         <tr>
            <td align="center">
                  <h2><? echo $cfg["title"]; ?></h2>
            <!--/td>
            <td align="center"-->
               <img src="images/seal.gif" alt="Logo" border="0"></br></br>
	       <?php
			if ( $cfg["testing"] == "dev" ) {
				echo "<font color=red>Development website, do not use unless you know what you're doing!</font>";
			} else if ( $cfg["testing"] == "rc" ) {
				echo "<font color=red>Release Candidate website, please report any bug you find, any ideas you have</font>";
			}
		?>
            </td>
         </tr>
      </table>
      <table class="login" align="center" border="0" cellpadding="1" cellspacing="0">
         <form action="login.php" method="post" name="form1">
         <tr class="row_head">
            <td align="center" colspan="2"><b> Forgotten password </b></td>
         </tr>
         <tr class="box_bg">
            <td align="center" colspan="2">&nbsp;</td>
         </tr>
         <tr class="box_bg">
            <td width="45%" align="right">Please send a new password to this address :</td>
            <td width="55%" align="left"><input type="text" name="uid" size="30"></td>
         </tr>
         <tr class="box_bg">
            <td colspan="2" align="center">&nbsp;</td>
         </tr>
         <tr class="box_bg">
            <td colspan="2" align="center">
               <input type="submit" class="button_send" value="Send">
            </td>
         </tr>
         <tr class="box_bg">
            <td colspan="2" align="center">&nbsp;</td>
         </tr>
         <input type="hidden" name="action" value="forgot_password_check">
         </form>
      </table>
   </td></tr></table>
   <script language="JavaScript">
      document.form1.uid.focus();
   </script> <?php
}

$action = strtolower($action);
switch ($action) {
   case "forgot_password":
   	user_forgot_password_form();
   	break;
   case "forgot_password_check":
      if (!$user = $db->Execute("SELECT * FROM users WHERE username='$uid'")) {
         echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
         break;
      }

      if ($user->RecordCount() == 0) {
      	echo "<table class=\"warn\" width=\"100%\"><tr><td>Unknown Email.</td></tr></table>";
   	user_forgot_password_form();
	break;
      }

      // Generate a password
      $pwd = '';
      for($i=0; $i< rand(8, 10); $i++) {
         if ( rand(0,10) >= 5 ) {
            $pwd .= chr(rand(0, 25) + ord('a'));
         } else {
	     $pwd .= chr(rand(0, 25) + ord('A'));
	 }
       }

      // Reset passwd
      $query1 = "UPDATE users SET password='" . md5($pwd) . "'" . " WHERE username='$uid'";
      if (!$db->Execute($query1)) {
           echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
           break;
      }

      // Send the password
      $mail_headers = 'From: ' . $cfg["sysadmin_email"] . "\r\n" .
            'Reply-To: ' . $cfg["sysadmin_email"] . "\r\n" .
            'To: ' . $uid . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
      $email_body =
       "=== " . $cfg["title"] . " Account ===" ."\r\n" .
       "\r\n" .
       "This mail has been sent by the " . $cfg["title"] . " to provide your login and password. Once logged it is *strongly* recommended to change it."."\r\n" .
       "\r\n" .
       "Access to the website : " . $cfg["baseurl"] . "\r\n" .
       "\r\n" .
       "Go there and log in using the following details : " ."\r\n" .
       " - Email    : $uid" ."\r\n" .
       " - Password : $pwd" ."\r\n" .
       "\r\n" .
       "\r\n" .
       "The Finance Department Sysadmin," ."\r\n" .
       $cfg["sysadmin_email"]. "\r\n";

      do_mail($uid,
              $cfg["title"] . " Account",
              $email_body,
              $mail_headers);

 
      echo "<table class=\"info\" width=\"100%\"><tr><td>A new password has been sent to $uid.</td></tr></table>";
      user_form();
      break;
   case "check_user":
	$ldapok = false;

	if ( $uid === "" ) {
		user_form();
		echo "<table class=\"warn\" width=\"100%\"><tr><td>Please provide a login.</td></tr></table>";
		break;
	}

	if ( $pwd === "" ) {
		user_form();
		echo "<table class=\"warn\" width=\"100%\"><tr><td>Please provide a password.</td></tr></table>";
		break;
	}

	# Try to identify with LDAP
	$ldap = ldap_connect($ldap_url);
	ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

	# Bind
	if ( isset($ldap_binddn) && isset($ldap_bindpw) ) {
		$bind = ldap_bind($ldap, $ldap_binddn, $ldap_bindpw);
	} else {
		$bind = ldap_bind($ldap);
	}

	$errno = ldap_errno($ldap);
	if ( $errno ) {
		error_log("LDAP - Bind error $errno  (".ldap_error($ldap).")");
	} else {

		# Search for user
		$ldap_filter = str_replace("{login}", $uid, $ldap_filter);
		$search = ldap_search($ldap, $ldap_base, $ldap_filter);

		$errno = ldap_errno($ldap);
		if ( $errno ) {
			error_log("LDAP - Search error $errno  (".ldap_error($ldap).")");
		} else {

			# Get user DN
			$entry = ldap_first_entry($ldap, $search);
			$userdn = ldap_get_dn($ldap, $entry);

			if( !$userdn ) {
				error_log("LDAP - User $uid not found");
			} else {

				# Bind with password
				$bind = ldap_bind($ldap, $userdn, $pwd);
				$errno = ldap_errno($ldap);
				if ( $errno ) {
					error_log("LDAP - Bind user error $errno  (".ldap_error($ldap).")");
					user_form();
					echo "<table class=\"warn\" width=\"100%\"><tr><td>LDAP ". ldap_error($ldap) .".</td></tr></table>";
					break;
				} else {
					$ldapok = true;

					$usermails = ldap_get_values($ldap, $entry, 'mail');

					for ($i = 0; $i < $usermails["count"] ; $i ++) {
						$uid = $usermails[ $i ];

						$user = $db->Execute("SELECT * FROM users WHERE username='$uid'");
						if ($user->RecordCount() > 0) break;
					}

					if ( $i == $usermails["count"] ) {
						user_form();
						echo "<table class=\"warn\" width=\"100%\"><tr><td>It seems your user is not known by the Finance System, contact the Finance Department.</td></tr></table>";
						break;
					}

					echo "Using email address : $uid";

				}
			}
		}
	}
	


	# Get the user details in the database
	if (!$user = $db->Execute("SELECT * FROM users WHERE username='$uid'")) {
		echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		break;
	}

	if ($user->RecordCount() == 0) {
		user_form();
		echo "<table class=\"warn\" width=\"100%\"><tr><td>Invalid Username.</td></tr></table>";
		break;
	}

	if (!$array_role = $db->Execute("SELECT * FROM `users-roles` WHERE username='$uid'")) {
		echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		break;
	}

	if ( ! $ldapok ) {
		if (md5($pwd) != $user->fields["password"]) {
			user_form();
			echo "<table class=\"warn\" width=\"100%\"><tr><td>Invalid Password.</td></tr></table>";
			break;
		}
	}

	# Redirect to the main page
	$_SESSION["username"]	= $user->fields["username"];
	$_SESSION["priv"]	= $user->fields["priv"];
	$_SESSION["user_role"]	= $array_role->fields["role"]; 
	$_SESSION["fullname"]	= $user->fields["fullname"]; ?>
	<script language="JavaScript">
	window.location="index.php";
	</script> <?php
	
	break;
   default:
      user_form();
      break;
}

require("footer.inc.php"); ?>
