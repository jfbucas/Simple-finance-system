<?php
require("config.inc.php");
session_name($cfg["session"]);
session_start();
if (!empty($_SESSION) && isset($_SESSION["username"])) {
   $username = $_SESSION["username"];
   $priv = $_SESSION["priv"];
   $fullname = $_SESSION["fullname"];
} else {
   header("Status: 302 Found");
   header("Location: login.php");
}

if (file_exists($_GET["pdf_file"])) {
   $len = filesize($_GET["pdf_file"]);
   //header("Content-Type: application/pdf");
   //header("Content-Length: $len");
   //header("Content-Disposition: inline");

   header('Content-Description: File Transfer');
   header('Content-Type: application/octet-stream');
   header('Content-Disposition: attachment; filename='. $_GET["description"].".pdf" );
   header('Content-Transfer-Encoding: binary');
   header('Expires: 0');
   header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
   header('Pragma: public');
   header('Content-Length: ' . filesize($_GET["pdf_file"]));
   ob_clean();
   flush();
   readfile($_GET["pdf_file"]);
}
?>
