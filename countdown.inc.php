<?php
include('./_launchDate.php');

$bypass = @$_GET['bypass'];
$countdown = @$_GET['countdown'];


if( 
	$delta > 0 
	&& $_SERVER['SERVER_NAME'] != 'seepex.test.cat06.de'
	//&& $_SERVER['SERVER_NAME'] != '192.168.2.52'
	//&& $_SERVER['SERVER_NAME'] != 'localhost'
	&& $bypass != 1
	&& $countdown !== '0'
	&& $countdown !== 'no'
	){
    
    header('Location: ./countdown.php');
}


?>