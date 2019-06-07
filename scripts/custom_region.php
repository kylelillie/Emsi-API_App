<?php

$geography = $_POST['geography'];
$country = $_POST['country'];
$geog = '';
$region_type = 'Province';

if ($country == 'ca'){
	
	$states = 'ca_prov.csv';
	
	if ($geography == 'county'){
		$geog = 'ca_csd.csv';
		}
	if ($geography == 'state'){
		$geog = 'ca_prov.csv';
		}
	if ($geography == 'msa'){
		$geog = 'ca_cma.csv';
		}
	}

if ($country == 'us'){
	
	$states = 'us_states.csv';
	$region_type = 'State';
	if ($geography == 'county'){
		$geog = 'ca_csd.csv';
		}
	if ($geography == 'state'){
		$geog = 'ca_prov.csv';
		}
	if ($geography == 'msa'){
		$geog = 'ca_cma.csv';
		}
	}
	
$file = '/var/www/html/emsi/data/'.$states;
$csv = array();

$fileHandle = fopen($file, 'r');
$first = true;
//Loop through the CSV rows.
echo $region_type.' Selection<br>';
echo '<div class="inline field"><select class="label ui search fluid dropdown region" multiple="" id="select">';
echo '<option value="">All</option>';
while (($row = fgetcsv($fileHandle, 0, ",")) !== FALSE) {
	if ($first){$first = false; continue;}
	
    echo '<option value="'.$row[0].'">'.$row[1].'</option>';
}
fclose($handle);
echo '</select></div><p>';
?>