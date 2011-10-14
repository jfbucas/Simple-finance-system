<?php
// Include the ADODB library.
require("adodb/adodb.inc.php");
// Connect to the database.
$db =& ADONewConnection($cfg["db_type"]);
if ($cfg["db_persist"] == TRUE) { // Persistent DB connection.
   switch ($cfg["db_type"]) {
      case "sqlitepo":
         $db->PConnect($cfg["db_host"]);
         break;
      case "odbc":
         $db->PConnect($cfg["db_host"], $cfg["uid"], $cfg["pwd"]);
         break;
      default:
         $db->PConnect($cfg["db_host"], $cfg["uid"], $cfg["pwd"], $cfg["db"]);
   }
} else { // Non-persistent DB connection.
   switch ($cfg["db_type"]) {
      case "sqlitepo":
         $db->Connect($cfg["db_host"]);
         break;
      case "odbc":
         $db->Connect($cfg["db_host"], $cfg["uid"], $cfg["pwd"]);
         break;
      default:
         $db->Connect($cfg["db_host"], $cfg["uid"], $cfg["pwd"], $cfg["db"]);
   }
}
$db->SetFetchMode(ADODB_FETCH_BOTH);
set_magic_quotes_runtime(0);
// For debugging
//require("adodb/tohtml.inc.php");
//$db->debug = TRUE;
?>
