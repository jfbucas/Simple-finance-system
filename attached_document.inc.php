<?php

$attached_document_path = $cfg["basedir"] . "attached_document/";

function attached_document_form($category, $draft_number) {

	echo '<table class="small" cellspacing="0" cellpadding="1" width="100%">';
	echo '<tr class="box_head"><td colspan=2 align="left" ><b>Upload a document</b></td></tr>';

	echo '<form enctype="multipart/form-data" action="' . $category . '.php" method="post" name="form_attached_document">';

		echo '<tr class="box_bg"><td>Please select file to upload <input name="attached_document" size="40" type="file"></td></tr>';
		echo '<tr class="box_bg"><td><button type="button" class="button_upload" onClick="document.form_attached_document.submit();">Upload</button></td></tr>';
		echo '<input type="hidden" name="action" value="upload_attached_document">';
		echo '<input type="hidden" name="draft_number" value="' . $draft_number . '">';
		echo '<input type="hidden" name="MAX_FILE_SIZE" value="20000000" />';

	echo '</form>';
	echo "</table>";

}

function attached_document_upload( $db, $category, $draft_number ) {
	global $attached_document_path;
	$name = $_FILES['attached_document']['name'];
	$code = md5(time().rand());

	if (! move_uploaded_file( $_FILES['attached_document']['tmp_name'], $attached_document_path . $code ) ) {
		finance_log( $db, $category, $draft_number, "warn", "The file $name couldn't be attached to requisition ". $draft_number );
		return;
	} else {
		finance_log( $db, $category, $draft_number, "info", "The file $name has been attached." );
	}

	$nono_chars = array("&", " ", "\"", "'", "?", "!", "<", ">", "(", ")");
	$newname = str_replace($nono_chars, "_", $name);

	$query = "INSERT INTO attached_document (category, draft_number, name, code) "
		."VALUES ('$category', '$draft_number', " . $db->QMagic($newname) . ", '$code')";
	if (!$db->Execute($query)) {
			echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
	} 			

}

function attached_document_delete( $db, $category, $draft_number, $code ) {
	global $attached_document_path;

	if (! unlink( $attached_document_path . $code ) ) {
		finance_log( $db, $category, $draft_number, "warn", "The file $name couldn't be deleted in requisition ". $draft_number );
		return;
	} else {
		finance_log( $db, $category, $draft_number, "info", "The file $name has been deleted." );
	}

	$query = "DELETE FROM attached_document WHERE category = '$category' AND draft_number = '$draft_number' AND code = '$code' LIMIT 1";

	if (!$db->Execute($query)) {
			echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
	} 			

}

function attached_document_list( $db, $category, $draft_number ) {
	global $attached_document_path;

	if (!$attached_document = $db->Execute("SELECT * FROM attached_document WHERE category='$category' and draft_number='$draft_number'")) {
		echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		return FALSE;
	}

	if ($attached_document->RecordCount() == 0) {
		return;
	}

	echo '<table class="small" cellspacing="0" cellpadding="1" width="100%">';
	echo '<tr class="box_head">';
	if ($attached_document->RecordCount() == 1) {
		echo '<td colspan=2 align="left" ><b>Attached document:</b></td>';
	}else{
		echo '<td colspan=2 align="left" ><b>Attached documents:</b></td>';
	}
	echo '</tr>';

	while (!$attached_document->EOF) {
		
		echo "<tr class=box_bg>";
		echo "<td title=\"Click to delete the attached document\" align=\"center\" class=\"\" onclick='if (isConfirmed(\"Are you sure you want to DELETE this attachment ?\")) { "
			."location.href=\"".$category.".php?action=delete_attached_document&draft_number=$draft_number&code=" . $attached_document->fields["code"] . "\"}'>"
			. "<img src=\"images/no.png\" border=\"0\" alt=\"Delete document\"></a></td>";
		echo "<td><a href='download_attached_document.php?attached_document_file=" . $attached_document->fields["code"] .
			 "&attached_document_name=" . $attached_document->fields["name"]. "'>". $attached_document->fields["name"] . "</a></td></tr>";
		$attached_document->MoveNext();
	}


	?>
	</table>
	<?php
	
}

?>
