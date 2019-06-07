<?php
//write a 'in-process queries' box? probably better to do in javascript? or write to a temp file with the job name and time?

$directory = '/var/www/html/emsi/output/';
$files = array_diff(scandir($directory),array('.','..'));
//ksort($files)

echo '<p><b>Finished Queries:</b><p>';

echo ('<div class="data_files">');
echo ('<div class="row"><div class="name_cell"><i>Name</div><div class="data_cell">Created</div><div class="data_cell">Size</i></div><div class="del_cell">Delete</div></div>');

foreach ($files as $value){
	echo ('<div class="row"><div class="name_cell"><a href=output/'.$value.'>'.$value.'</a></div><div class="data_cell">'.date("F d Y", filemtime($directory.$value)).'</div><div class="data_cell">'.number_format((filesize($directory.$value)/1024),2,".",",").' kb</div>'.'<div class="del_cell"><span class="delete" value='.$value.' onclick="del_file(\''.$value.'\')"><img src="images/x.svg"/></span></div></div>');
}
	
echo ('</div>');

if (file_exists('/var/www/html/emsi/logs/daemon.pid')){
	header("Refresh: $sec; url=$page");
}
?>