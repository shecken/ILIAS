<?php

// switch maintenance off here...
if (true) {
	$bypass = @$_GET['bypass_maintenance_page'];
	$session_bypass = @ilSession::get("bypass_maintenance_page");
	if ($bypass != 1 && $session_bypass != 1) {
		header("Location: ./maintenance.php");
		exit;
	}
	ilSession::set("bypass_maintenance_page", 1);
}
