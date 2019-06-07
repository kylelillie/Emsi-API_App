<?php

/*get mah vars */
$country = $_POST['country'];
$geography = $_POST['geography'];
$dataset = $_POST['dataset'];
$metric = $_POST['metric'];
$start = $_POST['start'];
$end = $_POST['end'];
$revision = $_POST['revision'];
$filename = $_POST['filename'];
$email = $_POST['email'];
$custom_region = $_POST['custom'];

/* execute this after form is submitted */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* execute the already-made python script */

try {
	$command = ('/var/www/html/emsi/scripts/agnitio.py '.$country.' '.$geography.' '.$dataset.' '.$metric.' '.' '.$start.' '.$end.' '.$revision.' '.$filename.' '.$email.' '.$custom_region);
	echo $filename;
	echo $custom_region;
	
	shell_exec($command);
}

catch(Exception $e) {
	echo '<li class="server_error">Server Error';
}

?>