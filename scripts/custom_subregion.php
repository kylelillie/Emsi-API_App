<?php

$country = $_POST['country'];
$geography = $_POST['geography'];
$subregions = explode(',',$_POST['subregions']);

/*only show if they've selcted MSA or CSD??*/

if ($geography != 'state'){

	echo '<div class="subregion">';

	if ($country == 'ca'){
		if ($geography == 'msa'){
			$subtext = 'CMA/CA';
			$subregion = 'ca_cma.csv';
			}
				
		else {
			$subtext = 'CSD';
			$subregion = 'ca_csd.csv';
			}
		
		echo $subtext.' Selection';
	}

	else {
		if ($geography == 'msa'){ 
			$subtext = 'MSA';
			$subregion = 'us_msa.csv';
			}
			
		else {
			$subtext = 'County';
			$subregion = 'us_counties.csv';
			}
		
		echo $subtext.' Selection';
	}

	echo '<select class="label ui search fluid dropdown" multiple="" id="select_b">';
	echo '<option value="">All</option>';
	
	/*open file; read it; filter it*/
	$file = fopen('/var/www/html/emsi/data/'.$subregion,'r');
	$filter = array_map('preg_quote',$subregions);
	$regex = '/'.implode('|',$filter).'/i';
	$first = true;
	
	while (($row = fgetcsv($file, 0, ",")) !== False){
		
		if ($first){$first = false; continue;}
		/*Counties*/
		if ($geography == 'county'){
			
			list($csduid,$csdname,$region) = $row;
			
			if (preg_match($regex,substr($csduid,0,2))){
				echo '<option>'.$csdname.' ('.$csduid.')</option>';
			}
		}
		/*MSAs*/
		else {
			
			list($csduid,$csdname,$region,$cma_id,$cma_name) = $row;
			
			if ($row[4] != $last_read){
				if (preg_match($regex,substr($csduid,0,2))){
					echo '<option>'.$cma_name.' ('.$cma_id.')</option>';
				}
				
				$last_read = $row[4];
			}	
		}
		
	}
	fclose($file);
	
	echo '</select>';
}

/*pass the variables into save_custom before removing the options box*/
echo "<p><div class='confirm options in_box' onClick=\"save_custom(true);\">Confirm</div><div class='cancel options in_box' onClick=\"save_custom(false);\">Cancel</div></div>";
?>