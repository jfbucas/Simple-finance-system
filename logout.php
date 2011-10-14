<?php
require("config.inc.php");
session_name($cfg["session"]);
session_start();
session_destroy();
header("Status: 302 Found");
header("Location: login.php");
?>
