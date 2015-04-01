<?php
	//hh,mm,ss,month, day, year
    
    $targetDate =mktime(10, 0, 0, 5, 4, 2015); //10:00:00 04.05.2015
    //$targetDate =mktime(11, 46, 0, 4, 1, 2015); 
    

    $now  = time();
    $delta = $targetDate - $now;
?>
