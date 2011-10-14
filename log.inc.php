<?

#$mysqldate = date( 'Y-m-d H:i:s', $phpdate );
#$phpdate = strtotime( $mysqldate );


// This function records and show every interaction messages with the PO/TR/ER in the log table

function finance_log( $db, $category, $draft_number, $level, $message, $action = 'show_and_log' ) {
	

	if (($draft_number == '') || ($draft_number == 0)) {
		$action = "show";
	}

	switch ($action) {
		case "show":
		case "show_and_log":
			echo "<table class=\"$level\" width=\"100%\"><tr><td>$message</td></tr></table>";
		break;
		default:
	}

	switch ($action) {
		case "show_and_log":
		case "log":
			switch ( $level ) {
				case "info" :
				case "warn" :
				case "crit" :
					$date = date('Y-m-d H:i:s');
					$query = "INSERT INTO log (category, draft_number, date, level, message)"
						. " VALUES ('$category', '$draft_number', '$date', '$level', " . $db->QMagic($message) . ")";
					if (!$db->Execute($query)) {
						echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
					} 			
				break;
				default:
					echo "<table class=\"warn\" width=\"100%\"><tr><td>Unknown LOG level </td></tr></table>";
			}
		break;
		default:
	}

}

function finance_log_show( $db, $category, $draft_number ) {


	if (!$log_entries = $db->Execute("SELECT * FROM log WHERE category='$category' and draft_number='$draft_number' ORDER BY date DESC")) {
		echo "<table class=\"warn_db\" width=\"100%\"><tr><td>DB ERROR: " . $db->ErrorMsg() . "</td></tr></table>";
		return FALSE;
	}

	if ($log_entries->RecordCount() == 0) {
		return;
	}

	?>	
		<table class="small" cellspacing="0" cellpadding="1" width="100%">
		<tr class="box_head">	<td colspan=2 align="left" ><b>Log :</b></td>      </tr>
	<?php

	while (!$log_entries->EOF) {
		
		echo "<tr><td class=date_log><pre>". $log_entries->fields["date"] . "</pre></td><td class=\"". $log_entries->fields["level"] . "_log\"><pre>". $log_entries->fields["message"] . "</pre></td></tr>";
		$log_entries->MoveNext();
	}


	?>
	</table>
	<?php
}

?>
