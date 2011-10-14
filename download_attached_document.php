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

$the_file = "attached_document/" . $_GET["attached_document_file"];

if (file_exists($the_file)) {
   $len = filesize($the_file);
   //header("Content-Type: application/pdf");
   //header("Content-Length: $len");
   //header("Content-Disposition: inline");

   header('Content-Description: File Transfer');
   header('Content-Type: application/octet-stream');
   header('Content-Disposition: attachment; filename='. $_GET["attached_document_name"] );
   header('Content-Transfer-Encoding: binary');
   header('Expires: 0');
   header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
   header('Pragma: public');
   header('Content-Length: ' . filesize($the_file));
   ob_clean();
   flush();
   readfile($the_file);
}
?>
