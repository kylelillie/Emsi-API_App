<?php

/*get mah vars */
$country = $_POST['country'];
$geography = $_POST['geography'];
$dataset = $_POST['dataset'];

/* execute this after form is submitted */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$client_id = "alberta-edt";
$client_secret = "fdd6f495";
$token_url = "https://auth.emsicloud.com/connect/token";
$scope = "emsiauth";
$content_type = "application/x-www-form-urlencoded";

$data = array("grant_type"=>"client_credentials",
			"client_id"=>$client_id,
			"client_secret"=>$client_secret,
			"scope"=>$scope,
			"content-type"=>$content_type);

$options = array(
	"http"=> array(
		"header"=>"Content-type: application/x-www-form-urlencoded\r\n",
		"method"=>"POST",
		"content"=>http_build_query($data))
	);

$context = stream_context_create($options);
$result = file_get_contents($token_url,false,$context);

if ($result === FALSE) 
{ 
	echo "no response"; 
}

$token = json_decode($result)->access_token;
$expiry = json_decode($result)->expires_in;

$meta_url = "http://agnitio.emsicloud.com/meta/dataset/emsi.".$country.".".$dataset."/";

$get = array(
	"http"=> array(
		"method"=>"GET",
		"header"=>"Authorization: Bearer ".$token."\r\n"."Content-Type: application/json"));

$header = stream_context_create($get);

/*find the most recent year available first*/
$meta = json_decode(file_get_contents($meta_url,false,$header));
$recent_year_data = end($meta);

$values = array($token,$expiry,$recent_year_data);

echo $recent_year_data;

/*now apply it*/
$meta = json_decode(file_get_contents($meta_url.$recent_year_data,false,$header));

?>