<?
if ( $cfg["testing"] != "" )
	$time_start = microtime(true);
?>

<html>
<head>
<?php
   require("includes/validation.inc.php");
   require("includes/hideshow.inc.php");
	if ( isset($need_calendar) ) {
	   require_once('calendar/classes/tc_calendar.php');
	}
   
?>

<script language="javascript" src="calendar/calendar.js"></script>

<?php if ( isset($need_slimbox) ) { ?>
	<script type="text/javascript" src="help/slimbox/js/mootools.js"></script>
	<script type="text/javascript" src="help/slimbox/js/slimbox.js"></script>
	<link rel="stylesheet" href="help/slimbox/css/slimbox.css" type="text/css" media="screen" />
<?php } ?>


<link href="style.css" rel="stylesheet" type="text/css">
	<?php if ( isset($need_calendar) ) { ?>
		<link href="calendar/calendar.css" rel="stylesheet" type="text/css">
	<?php } ?>

<title><?php echo $cfg["title"] ?></title>
</head>
<body class="myBG">
<?php 
?>

<div id="Logo" class="Logo" onclick="location.href='index.php'" align="right">
   	<a href="index.php"><img Alt="Logo" border="0" src="images/seal.gif"></br>Home</a>
</div>

<table border="0" cellpadding="0" cellspacing="0" width="100%" class="box_head">
  <tr>
   <td><a href="index.php">Home</a></td>
<?php
if (isset($username)) { ?>
         <td width="5%"  align="right">&nbsp;</td>
         <td width="45%"  align="left">
	 	<?php
			if ( $cfg["testing"] == "dev" ) {
				//echo "<font color=red>Development website, do not use unless you know what you're doing!</font>";
			} else if ( $cfg["testing"] == "rc" ) {
				echo "<font color=red>Release Candidate website, please report any bug you find, any ideas you have</font>";
			}
		?>
		<script language="JavaScript">
			if (navigator.appName != "Netscape") {
				document.write("<font color=red>", navigator.appName, " is not fully supported. Please use Firefox. </font>");
			}
		</script>
	 
	 </td>
         <td width="50%"  align="right">&nbsp;&nbsp;Welcome <?php echo $fullname; ?><!--/td>
         <td class="row_even" align="right"--> <?php
            if ($user_role == $cfg["admin"]) { ?>
         	<button type="button" class="button_admin" onClick="window.location='admin.php'">Admin</button>
	 <?php } ?> <?php
            //if (($user_role == $cfg["registrar"]) || ($user_role == $cfg["finofficer"]) || ($user_role == $cfg["finmember"]) || ($user_role == $cfg["admin"])) { ? >
            //   <a href="finance.php"><img src="images/finance_btn.gif" alt="Finance" border="0"></a></a> <?php
            //} ?>
         	<button type="button" class="button_password" onClick="window.location='passwd.php'">Password</button>
         	<button type="button" class="button_logout" onClick="window.location='logout.php'">Logout</button>
         </td>
<?php
} else {

       echo  '<td width="80%" align="right">Please Login</td>';
}
	 
?>
  </tr>
</table>
