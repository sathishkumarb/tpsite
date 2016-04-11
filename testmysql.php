<?php 
$link = mysql_connect('localhost','taitsuser','0gAoc$67'); 
if (!$link) { 
	die('Could not connect to MySQL: ' . mysql_error()); 
} 
echo 'Connection OK'; mysql_close($link); 
?> 