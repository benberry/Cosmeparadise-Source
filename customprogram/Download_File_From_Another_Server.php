<?php

$productURL = "http://uniondropship.com/cosmetic/datafeed/cosmedropship.csv";
$dch = curl_init($productURL);
curl_setopt($dch, CURLOPT_RETURNTRANSFER, TRUE);
//curl_setopt($dch, CURLOPT_HEADER, TRUE); // We'll parse redirect url from header.
curl_setopt($dch, CURLOPT_FOLLOWLOCATION, FALSE); // We want to just get redirect url but not to follow it.
$response = curl_exec($dch);

curl_close($dch);

$name = "berrytest";

header('Content-type: text/csv');
header("Content-disposition: attachment;filename=$name.csv");
echo $response;


?>