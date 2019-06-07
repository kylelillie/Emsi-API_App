<!--
To do:

1. check if data query has already been completed
	if yes, don't do it again.
-->
<?php
$page = $_SERVER['PHP_SELF'];
$sec = "10";
?>
<!DOCTYPE html>
<html>
<head>
<title>Emsi Extractor</title>
<meta charset="utf-8"/>
<link rel="stylesheet" type="text/css" href="./css/css.css">
<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="./css/semantic.css">
<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.3.0/dist/semantic.js"></script>
</head>
<body>
<header>
	<div class="title">
		<img src='images/emsi.png'>
		Emsi Query Application
	</div>
</header>
<div class='main'>
<?php

if (file_exists('/var/www/html/emsi/logs/daemon.pid')){
	
	echo '<div class="in_progress show">';
	echo '<script>
		var height = $(".main").outerHeight();
		var width = $(".main").outerWidth();
		var position = $(".main").position();

		$(".in_progress").css("height",height-height/3);
		$(".in_progress").css("width",width);
		$(".in_progress").css("top",position.top);
		$(".in_progress").css("left",position.left);
		$(".in_progress").css("padding-top",height/3);
	</script>';
	header('refresh: 10;');
}

else {
	echo '<div class="in_progress hide">';
	header('refresh:');
	}

echo '<div><p>Query in Progress<br><img src="images/loader.svg"></div></div>';
?>


<form method="post" action="index.php" id="data-selection">
	<div class="pick-country">
		<b>1. Country</b>
		<br>
		<div class="radio" name="country" data-value="ca">Canada</div>
		<div class="radio" name="country" data-value="us">United States</div>
	</div>
	<div class="pick-geography"></div>
	<div class="pick-dataset"></div>
	<div class="pick-metric"></div>
	<div class="pick-years"></div>
</form>
</div>
</div>
<footer>
</footer>
</body>
<script>
$('footer').load('scripts/footer.php');

function del_file(file_to_delete){
	if (confirm('Do you want to delete this file? \n\''+file_to_delete+'\'')){
		$.ajax({
			url: 'scripts/delete.php',
			type: 'POST',
			data: {
				file_to_delete:file_to_delete
			},
			success: function(result){
				$('footer').delay(300).load('scripts/footer.php');
				console.log(result)
			}
		})
	}
}

//parse the URL hash
set_values();
 
//cascade dooowwwwwn
//if (typeof country !="undefined")
//{
$("div").find("[data-value='" + country + "']").addClass('selected');
pick_geography(country);
//}

$('.pick-country .radio').click(function(){
    $(this).parent().find('.radio').removeClass('selected');
    $(this).addClass('selected');
    country = $(this).attr('data-value');
	
	pick_geography(country);
	
	setHash('country='+country);

});

/*works with pick_geography to save custom selections*/	
function save_custom(save){
	
	$('.checkmark').remove();
	
	if (save) {
		
		//find a way to steal the values from the pop up box before it closes
		var custom_region = $('a.label').map(function(){return $(this).attr('data-value');}).get().join();
		custom_region = custom_region.replace(/[^,\d]{1,}/g,'');
		console.log(custom_region);
		//if province/state picked, and no sub-region, use all subregions
		$('[data-value=customize]').append('<img class="checkmark" src="images/check.png"></img>').addClass('options_yes');
		
		setHash('custom='+custom_region);
	}

	else {
		$('.options').removeClass('options_yes');
	}
	
	$('.options_box').remove();
}

function pick_geography(country){
	$('.pick-dataset').empty();
	$('.pick-geography').empty();
	$('.pick-geography').append('<b>2. Geography&nbsp&nbsp</b>');

	if (country == 'ca')
	{
		$('.pick-geography').append('<li><div class="radio" data-value="state">Province</div>');
		$('.pick-geography').append('<li><div class="radio" data-value="msa">CMA</div>');
		$('.pick-geography').append('<li><div class="radio" data-value="county">CSD</div>');
		$('.pick-geography').append('<li><div class="plain"><hr/></div>');
	}
	
	else
	{
		$('.pick-geography').append('<li><div class="radio" data-value="state">State</div>');
		$('.pick-geography').append('<li><div class="radio" data-value="msa">MSA</div>');
		$('.pick-geography').append('<li><div class="radio" data-value="county">County</div>');
		$('.pick-geography').append('<li><div class="plain"><hr/></div>');
	}
	
	if (custom > 0) {
		$('.pick-geography').append('<li><div class="options options_yes" data-value="customize">Customize</div>');
		$('[data-value=customize]').append('<img class="checkmark" src="images/check.png"></img>').addClass('options_yes');
		}

	else
		$('.pick-geography').append('<li><i>(optional)</i><br><div class="options" data-value="customize">Customize</div>');

	
//	if (typeof geography != "undefined")
//	{
	$("div").find("[data-value='" + geography + "']").addClass('selected');
	pick_datasets(country,geography);
//	}
	
	function subregion(){
			
			var selections = $('a.label').map(function(){return $(this).attr('data-value');}).get().join();

			$.ajax({
			type: 'POST',
			url: 'scripts/custom_subregion.php',
			data:{
				subregions: selections,
				country: country,
				geography: geography
			},
			success: function(result){
				$('.subregion').remove();
				$('.in_box').remove();
				$('.options_box').append(result);
				$('#select_b').dropdown({fullTextSearch:'exact',saveRemoteData:true});
			}
		});
	};
	
	$('.options').click(function(){
		$('.main').append('<div class="options_box"><h4>Customize Geography Selection</h4><div></div>');
		$.ajax({
			type: 'POST',
			url: 'scripts/custom_region.php',
			data: {
				geography: geography,
				country: country
				},
			success: function(result){
				$('.options_box').append(result);
				$('#select').dropdown();
				subregion();
				
				$('.region').click(function(){
					subregion();
				});
			}
		});
	});
	
	$('.pick-geography .radio').click(function(){
		$(this).parent().parent().find('.radio').removeClass('selected');
		$(this).addClass('selected');

		geography = $(this).attr('data-value');

		setHash('geography='+geography);
		
		pick_datasets(country,geography);

	});

};

function pick_datasets(country,geography){
	$('.pick-dataset').empty();
	$('.pick-dataset').append('<b>3. Dataset</b>');

	if (country == 'ca')
	{
		$('.pick-dataset').append('<li><div class="radio" data-value="completers">Completers</div>');
		$('.pick-dataset').append('<li><div class="radio" data-value="demographics">Demographics</div>');
		$('.pick-dataset').append('<li><div class="radio" data-value="industry">Industry</div>');
		$('.pick-dataset').append('<li><div class="radio" data-value="locations">Locations</div>');
		$('.pick-dataset').append('<li><div class="radio" data-value="occupation">Occupation</div>');
		$('.pick-dataset').append('<li><div class="radio" data-value="staffing">Staffing</div>');
	}
	
	else
	{
		$('.pick-dataset').append('<li><div class="radio" data-value="completers">Completers</div>');
		$('.pick-dataset').append('<li><div class="radio" data-value="demographics">Demographics</div>');
		$('.pick-dataset').append('<li><div class="radio" data-value="industry">Industry</div>');
		$('.pick-dataset').append('<li><div class="radio" data-value="occupation">Occupation</div>');
		$('.pick-dataset').append('<li><div class="radio" data-value="staffing">Staffing</div>');
	}

	$('.pick-dataset').append('</div>');

	if (typeof dataset != "undefined")
	{
		$("div").find("[data-value='" + dataset + "']").addClass('selected');
		pick_metric();
	}
	
	$('.pick-dataset .radio').click(function(){
		
		$(this).parent().parent().find('.radio').removeClass('selected');
		$(this).addClass('selected');
		dataset = $(this).attr('data-value');

		pick_metric();
		
		setHash('dataset='+dataset);
		
	});

};

//add in a select 'metric' option
function pick_metric(){

	var metrics = {
			'ca':{
				'completers':['AwardLevel','Institution','Program'],
				'demographics':['Population'],
				'industry':['Earnings','EPW','Jobs'],
				'occupation':['Earnings','EPW','Jobs'],
				'staffing':['Jobs','Percentage'],
				'locations':['LT','LI','L500','L200','L100','L50','L10','L5','L1']
			},
			'us':{
				'completers':['AwardLevel','Institution','Program'],
				'demographics':['Population'],
				'industry':['Earnings','Estab.','Jobs','Supplements','Wages','EPW','WPW','SPW'],
				'occupation':['Earnings','Jobs'],
				'staffing':['Jobs','Percent']
			}
	}
	
	$('.pick-metric').empty();
	$('.pick-metric').append('<b>4. Data Metric&nbsp&nbsp</b>');
	
	try {
		for (i=0; i < Object.keys(metrics[country][dataset]).length; i++)
		{
			$('.pick-metric').append('<li><div class="radio" data-value="'+metrics[country][dataset][i].toLowerCase()+'">'+metrics[country][dataset][i]+'</div>');
		}
	}
	
	catch(err) {
		$('.pick-metric').append('<li><div data-value="null"></div>');
	}
	
	if (typeof metric != "undefined")
	{
		$("div").find("[data-value='" + metric + "']").addClass('selected');

		$.ajax({
			type: 'POST',
			url: 'scripts/dates.php',
			data: {
				country: country,
				geography: geography,
				dataset: dataset
				},
		success: function(response){
			final_year = ~~response;
			revision_year = response;
			pick_years(country,geography,dataset,final_year,revision_year);
			}
		});
	}
	
	$('.pick-metric .radio').click(function(){
		
		$(this).parent().parent().find('.radio').removeClass('selected');
		$(this).addClass('selected');
		
		metric = $(this).attr('data-value');
		
		setHash('metric='+metric);
		
		$.ajax({
			type: 'POST',
			url: 'scripts/dates.php',
			data: {
				'country': country,
				'geography': geography,
				'dataset': dataset
				},
		success: function(response){
			final_year = ~~response;
			revision_year = response;
			pick_years(country,geography,dataset,final_year,revision_year);
			}
		});
		
	});
}

function pick_years(country,geography,dataset,final_year,revision_year){

	var years = '';
	
	if (typeof start_year == "undefined") { start_year = 2001;setHash('start='+2001);}
	if (typeof end_year == "undefined") { end_year = final_year;setHash('end='+final_year);}
	
	//generate the required amount of years as a string value
	//this should be moved, so it initializes even if 'dataset' isn't clicked
	//turn this into a drop menu like found under the customize button, so a whole range of years can be selected.
	for (i=2001; i<=final_year;i++)
	{
		years +='<option value="'+i+'"/>';
	}
	
	$('.pick-years').empty();
	$('.pick-years').append('<b>5. Years</b>');
	
	$('.pick-years').append('<li><div class="box">Start Year</div>'+
		'<input value="'+start_year+'" id="s_year" class="years" list="startyear" name="startyear" onclick="this.value=20;" oninput="setHash(\'start=\'+this.value);validate();">'+
		'<datalist id="startyear">'+years+'</datalist></li>');

	$('.pick-years').append('<li><div class="box">End Year&nbsp</div>'+
		'<input value="'+end_year+'" id="e_year" class="years" list="endyear" name="endyear" onclick="this.value=20;" oninput="setHash(\'end=\'+this.value);validate();">'+
		'<datalist id="endyear">'+years+'</datalist></li>');
	
	window.revision_year = revision_year;
	
	validate();
};
 
function validate(){
	
	$('div').remove('.go');
	
	var result = window.location.hash.split('&').reduce(function (result, item) {
		var parts = item.split('=');
		result[parts[0]] = parts[1];
		return result;
	}, {});

	//initialize variables
	window.country = result['#country'];
	window.geography = result['geography'];
	window.dataset = result['dataset'];
	window.metric = result['metric'];
	window.start_year = result['start'];
	window.end_year = result['end'];
	window.custom = result['custom'];
	
	var year_error = true;
	var dataset_error = true;
	var metric_error = true;
	
	//perform validation
	//start year can't be after end year
	if (typeof start_year != "undefined" && typeof end_year != "undefined")
	{
		if (start_year > end_year)
		{
			$('.error').remove(':contains("Invalid")');
			$('.pick-years').append('<li><div class="error">Invalid Start Year</div>');
			year_error = true;
		}
		else if (start_year <= end_year)
		{
			$('.error').remove(':contains("Invalid")');
			year_error = false;
		}
	}
	
	//data metric should be reset if dataset changes
	if ($('.pick-metric').find('div.selected').length == 0)
	{
		$('.error').remove(':contains("Select Metric")');
		$('.pick-metric').append('<li><div class="error"><b>!</b> Select Metric</div>');
		metric_error = true;
	}
	
	else if ($('.pick-metric').find('div.selected').length == 1)
	{
		$('.error').remove(':contains("Select Metric")');
		metric_error = false;
	}
	
	//us can't have 'locations'
	if (country == 'us' && dataset == 'locations')
	{
		$('.pick-dataset').append('<li><div class="error"><b>!</b> Select Dataset</div>');
		dataset_error = true;
	}
	
	else if (country == 'us' && dataset != 'locations' || country == 'ca')
	{
		$('.error').remove(':contains("Select Dataset")');
		dataset_error = false;
	}
	
	if (year_error == false && dataset_error == false && metric_error == false && !$('.go').length)
	{
		
		$('#data-selection').append('<div class="go"><b>&#187&#187&nbspReady!'
									+'<li class="text"><br><b>Save Data as:</b><br><input class="email" name="filename" type="text" maxlength="47" placeholder="'+save_file+'"></input>'
									+'<br>&nbsp<span style="color:red;font-size:14pt;">*</span><sup style="color:#555;">will be compressed as a .ZIP archive</sup>'
									+'<li class="text"><br><b>Send query completion note to:</b><br><input class="email" name="email" type="email" maxlength="47" placeholder="first.last@gov.ab.ca"></input><p>'
									+'<li class="run" onclick="run_query()">Submit Query'
									+'</div>');
	}
	else if (year_error == true || dataset_error == true)
	{
		$('div').remove('.go');
	}
	
};

function set_values(){
	
	var result = window.location.hash.split('&').reduce(function (result, item) {
		var parts = item.split('=');
		result[parts[0]] = parts[1];
		return result;
	}, {});

	//initialize variables
	window.country = result['#country'];
	window.geography = result['geography'];
	window.dataset = result['dataset'];
	window.metric = result['metric']
	window.start_year = result['start'];
	window.end_year = result['end'];
	window.custom = result['custom'];
	
	var geotmp;
	
	if (country == 'ca' && geography == 'state') { geotmp = 'province' }
	
	else if (country == 'ca' && geography == 'msa') { geotmp = 'cma' }
	
	else if (country == 'ca' && geography == 'county') { geotmp = 'csd' }
	
	else { geotmp = geography }
	
	if (custom == 'true'){
		window.save_file = 'custom.'+country+'.'+geotmp+'.'+dataset+'.'+metric+'.'+start_year+'-'+end_year+'.csv';
	}
	else
		window.save_file = country+'.'+geotmp+'.'+dataset+'.'+metric+'.'+start_year+'-'+end_year+'.csv';
};

function setHash(hash_var){
	
	var nameRx = new RegExp("(.*)=");
	var valueRx = new RegExp("(\=(.*))");
	
	var name = nameRx.exec(hash_var)[1];
	var value = valueRx.exec(hash_var)[2];
	
	hash = window.location.hash;

	var hashRx = new RegExp("("+name+"=(.*?)&)");
	hash = hash.replace(hashRx,hash_var+'&');
	
	if (!hash.includes(hash_var))
	{
		window.location.hash += hash_var+'&';
	}
	else
		window.location.hash = hash;
	
	set_values();
};

function run_query()
{
	$('li').remove('.run');
	$('.go').append('<li class="processing">Processing');
	
	var height = $('.main').outerHeight();
	var width = $('.main').outerWidth();
	var position = $('.main').position();

	$('.in_progress').css('height',height-height/3);
	$('.in_progress').css('width',width);
	$('.in_progress').css('top',position.top);
	$('.in_progress').css('left',position.left);
	$('.in_progress').css('padding-top',height/3);
	
	$('.in_progress').removeClass('hide');
	$('.in_progress').addClass('show');
	
	
	if ($('[name="filename"]').val() != '')
	{
		save_file = $('[name="filename"]').val()
	}

	
	$.ajax({
		type: 'POST',
		url: 'scripts/data.php',
		data: {
			country: country,
			geography: geography,
			dataset: dataset,
			metric: metric,
			start: start_year,
			end: end_year,
			revision: revision_year,
			filename: save_file,
			email: $('[name="email"]').val(),
			custom: custom
			},
		success: function(result){
			console.log('Success');
			console.log(custom);
			setTimeout(function(){ location.reload(true); }, 5000)
			
		}
	});
}
</script>
</html>