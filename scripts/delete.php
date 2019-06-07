<?php

$directory = '/var/www/html/emsi/output/';
$file = $_POST['file_to_delete'];

// Check file exist or not
if( file_exists($directory.$file) ){
	unlink($directory.$file);
	
	echo $directory.$file;
}
?>