<?php
echo "GOGOGO<br>";
$url = 'http://dynupdate.no-ip.com/ip.php';
//$url = 'http://www.cosmeticsnow.com/iteminfo/biotherm-aquasource-deep-hydration-replenishing-gel-normalcombination-skin-50ml';
//$proxy = '202.165.89.18:80';
//$proxy = '203.20.55.55:80';
//$proxy = '203.20.238.21:80';
//$proxy = '58.96.138.12:8080';
//$proxy = '180.95.19.77:80';
//$proxy = '116.228.55.184:80';
//$proxyauth = 'user:password';
$proxy = "54.252.106.242:80";
//$proxy = "122.56.106.46:3128";
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
curl_setopt($ch, CURLOPT_PROXY, $proxy);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'GET');
curl_setopt ($ch, CURLOPT_HEADER, 1);
$curl_scraped_page = curl_exec($ch);
curl_close($ch);

echo $curl_scraped_page;

?>