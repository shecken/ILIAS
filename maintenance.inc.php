<?php

// switch maintenance off here...
if (true) {
	$bypass = @$_GET['bypass_maintenance_page'];
	$cookie_bypass = @$_COOKIE["bypass_maintenance_page"];
	if ($bypass != 1 && $cookie_bypass != 1) {
		header("Location: ./maintenance.php");
		exit;
	}
	setcookie("bypass_maintenance_page", 1, time() + 24 * 60 * 60);
}
