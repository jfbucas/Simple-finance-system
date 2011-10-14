<?php
/*
 * This file will set the GET and POST variables. This works for PHP >= 4.0
 * with register_globals = off OR on (off is recomended for PHP >= 4.1).
 */

if (!empty($_GET)) {
   extract($_GET, EXTR_OVERWRITE);
} else if (!empty($HTTP_GET_VARS)) {
   extract($HTTP_GET_VARS, EXTR_OVERWRITE);
}

if (!empty($_POST)) {
   extract($_POST, EXTR_OVERWRITE);
} else if (!empty($HTTP_POST_VARS)) {
   extract($HTTP_POST_VARS, EXTR_OVERWRITE);
}

if (!empty($_FILES)) {
   while (list($name, $value) = each($_FILES)) {
      $$name = $value["tmp_name"];
   }
} else if (!empty($HTTP_POST_FILES)) {
   while (list($name, $value) = each($HTTP_POST_FILES)) {
      $$name = $value["tmp_name"];
   }
}

// The rest of this file is specific to AssetMan. Cut it out if you
// use this file in other applications.

// Initialize the $action variable if it is not already set.
if (!isset($action)) $action = "null";
?>

