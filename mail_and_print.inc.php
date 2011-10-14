<?php
class mshell_mail {
  var $errstr;
  var $headers;
  var $textbody;
  var $htmlbody;
  var $attachments;
  var $boundary;

  // Default constructor, sets up default header and boundary.
  function mshell_mail() {
    $this->attachments = array();
    $this->boundary = '_mshell_mail_boundary_';
    $this->headers = array(
         'From' => 'Metalshell Mail Class <default@mshell_mail.com>',
         'MIME-Version' => '1.0',
         'Content-Type' => "multipart/mixed; boundary=\"$this->boundary\""
    );
    $this->bodytext("Default Mail Message.");
  }

  // For debugging purposes you can display the body you are about
  // to send.
  function get_body() {
    $retval = $textbody;
    $retval .= $htmlbody;
    foreach($this->attachments as $tblck)
      $retval .= $tblck;
    return $retval;
  }

  // Convert the values in the header array into the correct format.
  function get_header() {
    $retval = "";
    foreach($this->headers as $key => $value)
      $retval .= "$key: $value\n";
    return $retval;
  }

  // Add your own header entry or modify a header.
  function set_header($name, $value) {
    $this->headers[$name] = $value;
  }

  // Attach a file to the message.
  function attachfile($file, $type = "application/octetstream", $alt_fname = FALSE)  {
    if(!($fd = fopen($file, "r"))) {
      $this->errstr = "Error opening $file for reading.";
      return 0;
    }
    $_buf = fread($fd, filesize($file));
    fclose($fd);
    if ($alt_fname === FALSE) {
      $fname = $file;
      for($x = strlen($file); $x > 0; $x--)
        if($file[$x] == "/")
          $fname = substr($file, $x + 1, strlen($file) - $x);
    } else {
      $fname = $alt_fname;
    }

    // Convert to base64 because mail attachments are not binary safe.
    $_buf = chunk_split(base64_encode($_buf));
    $this->attachments[$file] = "\n--" . $this->boundary . "\n";
    $this->attachments[$file] .= "Content-Type: $type; name=\"$fname\"\n";
    $this->attachments[$file] .= "Content-Transfer-Encoding: base64\n";
    $this->attachments[$file] .= "Content-Disposition: attachment; filename=\"$fname\"\n\n";
    $this->attachments[$file] .= $_buf;
    return 1;
  }

  function bodytext($text) {
    // Set the content type to text/plain for the text message.
    // 7bit encoding is simple ASCII characters, this is default.
    $this->textbody = "\n--" . $this->boundary . "\n";
    $this->textbody .= "Content-Type: text/plain\n";
    $this->textbody .= "Content-Transfer-Encoding: 7bit\n\n";
    $this->textbody .= $text;
  }

  function htmltext($text) {
    // Set the content type to text/html for the html message.
    // Also uses 7bit encoding.
    $this->htmlbody = "\n--" . $this->boundary . "\n";
    $this->htmlbody .= "Content-Type: text/html\n";
    $this->htmlbody .= "Content-Transfer-Encoding: 7bit\n\n";
    $this->htmlbody .= $text;
  }

  function clear_bodytext() { $this->textbody = ""; }
  function clear_htmltext() { $this->htmlbody = ""; }
  function get_error() { return $this->errstr; }

  // Send the headers and body using php's built in mail.
  function sendmail($to = "root@localhost", $subject = "Default Subject") {
    if(isset($this->textbody)) $_body .= $this->textbody;
    if(isset($this->htmlbody)) $_body .= $this->htmlbody;
    foreach($this->attachments as $tblck)
      $_body .= $tblck;
    $_body .= "\n--$this->boundary--";
    do_mail($to, $subject, $_body, $this->get_header());
  }
} // End class mshell_mail

function send_pdf($file, $document_name, $description) {
   global $db, $cfg;
   // Get the email address of the currently logged in user.
   //$email = $db->Execute("SELECT email FROM users WHERE username='" . $_SESSION["username"] . "'");
   $email = $_SESSION["username"];
   // Create a new mail object.
   $mail = new mshell_mail;
//   $mail->set_header("From", "AssetMan@" . $_SERVER["SERVER_NAME"]);
   $mail->set_header("From", $cfg["finance_email"]);
   $mail->set_header("Reply-To",$cfg["finance_email"]);
   $body = "Please find attached the PDF copy of " . $description . " you requested from " . $cfg["title"] . ".";
   $mail->bodytext($body);
   // Attach the PDF document.
   if (!$mail->attachfile($file, "application/pdf", $document_name)) {
      print $mail->get_error();
      return FALSE;
   }
   // Now send the email to the user.
   $subject = $cfg["title"] . "PDF copy of " . $description;
   //$mail->sendmail($email->fields["email"], $subject);
   $mail->sendmail($email, $subject);
   unlink($file);
   return TRUE;
}

function print_image($printer, $document_name, $description) {
// Converts a forms overlay PNG image file to Postscript or HP PCL-XL (PCL6) for
// printing or PDF for email and download.
   global $cfg, $printer1, $printer2, $printer3, $printer4, $printer5;
    $printcap = ${$printer}["printcap"];
   switch ($cfg["os"]) {
      case "unix":

         switch (${$printer}["language"]) {
            case "ps":
               system("unixbin/pnmtops -rle -scale .5 var/" . session_id() . ".pnm > var/" . session_id() . ".ps", $return);
               system("lpr -P $printcap var/" . session_id() . ".ps", $return);
               break;
            case "pcl":
               system("unixbin/pnmtopclxl -center -colorok -dpi 150 -format letter var/" . session_id() . ".pnm > var/" . session_id() . ".pcl", $return);
               system("lpr -P $printcap var/" . session_id() . ".pcl", $return);
               break;
            case "email":
               system("convert -page a4 -border 90x90 -bordercolor '#FFFFFF' var/" . session_id() . ".png var/" . session_id() . ".pdf", $return);
               send_pdf("var/" . session_id() . ".pdf", $document_name, $description);
               break;
            case "download":
               system("convert -page a4 -border 90x90 -bordercolor '#FFFFFF' var/" . session_id() . ".png var/" . session_id() . ".pdf", $return); 
	       if ( $description == "" ) $description = "NoDescription";
		sleep(1);
		?>
               <script language="JavaScript">
                  window.open("download_pdf.php?description=<?php echo $description; ?>&pdf_file=<?php echo "var/" . session_id() . ".pdf"; ?>");
               </script> 
		<?php
               break;
            case "download_group":
               system("convert -page a4 -border 90x90 -bordercolor '#FFFFFF' var/" . session_id() . "*.png var/" . session_id() . ".pdf", $return); 
	       if ( $description == "" ) $description = "NoDescription";
		sleep(1);
		?>
               <script language="JavaScript">
                  window.open("download_pdf.php?description=<?php echo $description; ?>&pdf_file=<?php echo "var/" . session_id() . ".pdf"; ?>");
               </script> 
		<?php
               break;
            default:
               echo "<table class=\"warn\" width=\"100%\"><tr><td>Invalid forms overlay configuration. Check printer language parameter.</td></tr></table>";
               return FALSE;
         }
         break;
      case "win":
         switch (${$printer}["language"]) {
            case "ps":
               system("winbin\\pnmtops -rle -scale .5 var\\" . session_id() . ".pnm > var\\" . session_id() . ".ps", $return);
               system("copy /b var\\" . session_id() . ".ps $printcap", $return);
               break;
            case "pcl":
               system("winbin\\pnmtopclxl -center -colorok -dpi 150 -format letter var\\" . session_id() . ".pnm > var\\" . session_id() . ".pcl", $return);
               system("copy /b var\\" . session_id() . ".pcl $printcap", $return);
               break;
            case "email":
               system("winbin\\pnmtops -scale .5 var\\" . session_id() . ".pnm > var\\" . session_id() . ".ps", $return);
               system("ps2pdf var\\" . session_id() . ".ps var\\" . session_id() . ".pdf", $return);
               send_pdf("var/" . session_id() . ".pdf", $document_name, $description);
               break;
            case "download":
	       if ( $description == "" ) $description = "NoDescription";
               system("winbin\\pnmtops -scale .5 var\\" . session_id() . ".pnm > var\\" . session_id() . ".ps", $return);
               system("ps2pdf var\\" . session_id() . ".ps var\\" . session_id() . ".pdf", $return); ?>
               <script language="JavaScript">
                  window.open("download_pdf.php?description=<?php echo $description; ?>&pdf_file=<?php echo "var/" . session_id() . ".pdf"; ?>");
               </script> <?php
               break;
            default:
               echo "<table class=\"warn\" width=\"100%\"><tr><td>Invalid forms overlay configuration. Check printer language parameter.</td></tr></table>";
               return FALSE;
         }
         break;
   }
   // Clean up after yourself.
   @unlink("var/" . session_id() . ".png");
   @unlink("var/TEMP-" . session_id() . ".pdf");
//   @unlink("var/" . session_id() . ".ps");
//   @unlink("var/" . session_id() . ".pcl");
   return TRUE;
}
?>
