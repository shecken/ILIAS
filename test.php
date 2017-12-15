<?php

function hoursToMinutes($decimal) {
	// $hours = floor($decimal);
	// $decimal = $decimal - $hours;
	// $decimal = $decimal * 60;
	// $minutes = floor($decimal);
	// $seconds = $decimal - $minutes;

	return $decimal * 60;
}

function minuteToHoursString($minutes) {
	return $minutes / 60;
}

$minutes = hoursToMinutes(2.88);
echo $minutes;
echo "<br />";
echo minuteToHoursString($minutes);