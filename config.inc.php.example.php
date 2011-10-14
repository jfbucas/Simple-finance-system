<?php

if ( $_SERVER["SERVER_PORT"] == "446" ) {
	$cfg["testing"] = "dev"; // dev | rc | prod
} else {
	$cfg["testing"] = "prod"; // dev | rc | prod
}


$cfg["version"] = "0.2";
$cfg["signature"] = "Organisation Finance System " . $cfg["version"];
$cfg["title"] = "Organisation Finance System ";
$cfg["signature-reminder"] = "Organisation - Finance Department";

$cfg["finance_contact1"] = "Organisation Name";
$cfg["finance_contact2"] = "Address";
$cfg["finance_contact3"] = "City, Country";
$cfg["finance_contact4"] = "+353 00000000";
$cfg["finance_officer"] = "Thomas A. Anderson";

// Database connection parameters.
// This much I know is true. Organisation Assets WILL work with MySQL >= 3.23.xx, >= 4.0.xx,
// PostgreSQL >= 7.2.x and SQLite (PECL PHP extension version >= 1.0.2).
// Specify mysql, postgres or sqlitepo (not sqlite) for db_type.
// I THINK Organisation Assets will work with MOST of the other database drivers found
// in the adodb/drivers directory but I have no way of knowing for sure.
// Organisation Assets will NOT work with MS Access and I have no intention of trying
// to make it work.
$cfg["db_type"] = "mysql";      // DB server type eg: mysql, postgres, sqlitepo.
$cfg["db_host"] = "mysql";  // DB server hostname. DSN if odbc OR /path/filename if sqlite.

if ( $cfg["testing"] == "dev" ) {
	$cfg["db"] = "finance_test";           // Organisation Assets database. Not used if odbc or sqlite.
} else if ( $cfg["testing"] == "rc" ) {
	$cfg["db"] = "finance";           // Organisation Assets database. Not used if odbc or sqlite.
} else {
	$cfg["db"] = "finance";           // Organisation Assets database. Not used if odbc or sqlite.
}
$cfg["uid"] = "root";           // DB server user id.
$cfg["pwd"] = "";               // DB server password.
$cfg["db_persist"] = FALSE;     // Use persistent database connection. TRUE or FALSE.

// Session cookie name.
// If you want to configure two or more separate installations of Organisation Assets
// on the same host, you must ensure that each installation uses a unique session
// cookie name. Some users do this in order to access two or more databases which
// they want to keep separate.
// Most users configure just one installation of Organisation Assets and can safely
// ignore this parameter.


$cfg["oldbaseurl"] = "old.url.org";

if ( $cfg["testing"] == "dev" ) {
	$cfg["session"] = "Organisation-Finance-dev";
	$cfg["baseurl"] = "https://new.url.org:446/";
	$cfg["session_cache_expire"] = 8 * 60; // 8 hours
	$cfg["session_expire"] = 8 * 60 * 60;
} else if ( $cfg["testing"] == "rc" ) {
	$cfg["session"] = "Organisation-Finance-rc";
	$cfg["baseurl"] = "https://new.url.org:446/";
	$cfg["session_cache_expire"] = 8 * 60; // 8 hours
	$cfg["session_expire"] = 8 * 60 * 60;
} else {
	$cfg["session"] = "Organisation-Finance";
	$cfg["baseurl"] = "https://new.url.org/";
	$cfg["session_cache_expire"] = 8 * 60; // 8 hours
	$cfg["session_expire"] = 8 * 60 * 60;
}

# LDAP
$ldap_url = "ldap://127.0.0.1:7777";
$ldap_binddn = "";
$ldap_bindpw = "";
$ldap_base = "dc=company,dc=org";
$ldap_filter = "(&(objectClass=posixAccount)(uid={login}))";
#$ldap_filter = "(uid={login})";


# Email filtering in Testing mode - see mail.inc.php
$cfg["email_zone"] = "company.org";
$cfg["EMAIL_ZONE"] = "COMPANY.ORG";

// Allow receiving items capabillities  1 = yes  anything_else = no
$cfg["receive"] = 1;

// Roles & Privileges
// There are a few built-in roles: 
// *System Administrator (shortname: admin id:1) FULL access
// *Registrar (shortname: registrar id:2) FULL access EXCEPT to user/roles/section management
// *Finance Officer (shortname: finofficer id:3) Registrar access except approval of orders > 5000 EURO
// *Finance Office Member (shortname: finmember id:4) Able to see any order and assign PO numbers to approved orders
// *Users (shortname:user id:5) People able to order and approve them if the user is head of section
$cfg["admin"] = 1;
$cfg["registrar"] = 2;
$cfg["finofficer"] = 3;
$cfg["finmember"] = 4;

$cfg["sysadmin_email"] = "sysadmin@company.org" ;
$cfg["finance_email"] ="new.url.org" ;
$cfg["finance_email_notify_closure"] ="po-closed@company.org" ;
$cfg["support_email"] = "support@company.org" ;

$expenses_types_array = array( "Transport", "Accomodation", "AccomodationBreakfast", "Subsistence-5h", "Subsistence-10h", "Conference-Fee", "Abstract-Fee", "Other" );

$currencies_types_array = array( "e", "d", "l", "c", "o" );

$cfg["subsistence_rates_links"] = "<a href=\"http://some.file.address/rate.xls\">Ireland</a> | 	<a href=\"http://another.file.address/rates.xls\">Overseas</a>";

$cfg["quotes_required"] = 0; // Above this total, the PO requestors have to provide 3 quotes

// Miscellaneous configuration parameters.
$cfg["lpp"] = 20;          // Lines per page on search results.
$cfg["markup"] = 0.10;     // Markup to apply automatically when invoice line is
                           // added from a purchase order.
$cfg["tax1"] = 0.21;       // tax1 is a sales tax calculated from the total of
                           // invoice line items marked taxable. Line items
                           // not marked taxable are excluded from the calculation.
$cfg["tax2"] = 0.07;       // tax2 is a sales tax calculated from the total of
                           // ALL invoice line items regardless of whether or
                           // not the line item is marked taxable.
$cfg["tax1_name"] = "VAT21%"; // Local name for tax1. eg: PST, TAX, VAT etc.
$cfg["tax2_name"] = "GST"; // Local name for tax2. eg: GST, TAX, VAT etc.
$cfg["curr"] = "EURO  ";        // Local currency symbol. eg: $, £, € etc.
$cfg["date_fmt"] = "int";  // Local date format preference. You may choose;
                           //    iso - YYYY-MM-DD, ISO 8601
                           //    usa - MM/DD/YYYY, United States
                           //    int - DD/MM/YYYY, International
$cfg["phone_fmt"] = "int"; // Local phone number format preference. You may choose;
                           //    nam - 555-555-5555, Strict North American format
                           //    int - Allows 0-9, +, -, space, Relaxed check for International

// Forms overlay configuration parameters.
// If you want to use the forms overlay feature for printing PO's and invoices
// then set the following to TRUE. If you are not going to use forms overlay
// then set this to FALSE and ignore the rest.
$cfg["gd"] = TRUE;

// OS and Printer definitions, maximum of 4 printers for now.
// Forms overlay processing and printing is platform dependant.
// Set $cfg["os"] to one of the following:
//    $cfg["os"] = "unix" for Unix style operating systems (Unix/Linux/BSD etc.)
//    $cfg["os"] = "win" for Windows 9X/ME/NT/2000/XP operating systems.
// Set $cfg["basedir"] to the full path of the Organisation Assets directory. eg:
if ( $cfg["testing"] == "dev" ) {
	$cfg["basedir"] = "/var/www/new-test.url.org/";
} else if ( $cfg["testing"] == "rc" ) {
	$cfg["basedir"] = "/var/www/new-test.url.org/";
} else {
	$cfg["basedir"] = "/var/www/new.url.org/";
}

//    Be sure to include the trailing slash.
// Set $cfg["font_r"] to the full path to the regular weight font file and
//    set $cfg["font_b"] to the full path to the bold font file. eg:
//    $cfg["font_r"] = $cfg["basedir"] . "fonts/arial.ttf";
//    $cfg["font_b"] = $cfg["basedir"] . "fonts/arialbd.ttf";
// Set $printerN["name"] to the name of the printer as it will appear in Organisation Assets.
// Set $printerN["printcap"] to the system printer name for Unix or the printer
//    port name for Windows. eg:
//    $printerN["printcap"] = "is_laser";
//    $printerN["printcap"] = "\\\\printerserver\\printername";
//    This parameter is ignored for a PDF document via email or download.
// Set $printerN["language"] to one of the following:
//    $printerN["language"] = "ps" for Postscript capable printers.
//    $printerN["language"] = "pcl" for Hewlett Packard PCL XL (PCL 6) printers.
//    $printerN["language"] = "email" for a PDF document via email to current user.
//    $printerN["language"] = "download" for a PDF document via download to browser.
//    You MUST have Ghostscript installed for PDF via email or download to work.
// Set $printerN["colour"] to TRUE if the printer supports colour printing AND
//    you want colour output or FALSE for greyscale output.
if ($cfg["gd"]) {
   $cfg["os"] = "unix";
   $cfg["font_r"] = $cfg["basedir"] . "fonts/arial.ttf";
   $cfg["font_b"] = $cfg["basedir"] . "fonts/arialbd.ttf";
   $cfg["font_currency"] = $cfg["basedir"] . "fonts/Vera.ttf";
   
//   $printer1["name"] = "Example Windows Printer";
//   $printer1["printcap"] = "\\\\printserver\\printername";
//   $printer1["language"] = "pcl";
//   $printer1["colour"] = FALSE;
   
//   $printer2["name"] = "Example Unix Printer";
//   $printer2["printcap"] = "lp";
//   $printer2["language"] = "ps";
//   $printer2["colour"] = TRUE;
   
   $printer3["name"] = "PDF via Download";
   $printer3["printcap"] = "";
   $printer3["language"] = "download";
   $printer3["colour"] = TRUE;
   
   $printer4["name"] = "PDF via Email";
   $printer4["printcap"] = "";
   $printer4["language"] = "email";
   $printer4["colour"] = TRUE;

   $printer5["name"] = "Group PDF via Download";
   $printer5["printcap"] = "";
   $printer5["language"] = "download_group";
   $printer5["colour"] = TRUE;
}

// Do not edit anything below this line.
switch ($cfg["date_fmt"]) {
   case "usa":
      $cfg["date_arg"] = "m/d/Y";
      $cfg["date_exp"] = "MM/DD/YYYY";
      break;
   case "int":
      $cfg["date_arg"] = "d/m/Y";
      $cfg["date_exp"] = "DD/MM/YYYY";
      break;
   default:
      $cfg["date_arg"] = "Y-m-d";
      $cfg["date_exp"] = "YYYY-MM-DD";
}
?>
