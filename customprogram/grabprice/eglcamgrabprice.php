<?php

if(isset($_FILES["file"])) { 
	if (($_FILES["file"]["type"] == "text/csv")
	|| ($_FILES["file"]["type"] == "application/vnd.ms-excel")
	|| ($_FILES["file"]["type"] == "application/vnd.msexcel")
	|| ($_FILES["file"]["type"] == "application/excel")
	|| ($_FILES["file"]["type"] == "text/comma-separated-values"))
	{
	if ($_FILES["file"]["error"] > 0)
		{   echo "Return Code: " . $_FILES["file"]["error"] . "<br />"; exit;    }
	}
  }
else
  {
?>

<html>
<body>
<h2>Grab EGL price programme by link</h2>
<b>Please keep the format: sku/id, Name, URL(SKIP first row of header)</b>
<form action="eglcamgrabprice.php" method="post" enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> 
<br /><br />
<input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>

<?php	
  exit;
  }

$csvfile = $_FILES["file"]["tmp_name"];

if(!file_exists($csvfile)) {
	echo "File not found.";
	exit;
}

$size = filesize($csvfile);
if(!$size) {
	echo "File is empty.\n";
	exit;
}

////////////////////timer//////////////
//$execution_time = microtime(); # Start counting
 $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime; 

$fieldbreak = ",";
$linebreak = "\n";
$line = 	"sku/id".$fieldbreak.				
			"Name".$fieldbreak.
			"Subtotal".$fieldbreak.
			"Shipping fee".$fieldbreak.
			"Total".$linebreak;	

if (($handle = fopen($csvfile, "r")) !== FALSE) {	//OPEN CSV
	// FORMAT:  Sku/id, NAME, URL
	$row = 1;
	while (($data = fgetcsv($handle)) !== FALSE) {	//go through data
	$Sku = trim($data[0]);
	$Name = trim($data[1]);
	$URL = trim($data[2]);				
	//$Name = "Nikon D7000 Body Only Digital SLR Cameras";
	//$URL = 'http://www.eglobaldigitalcameras.com.au/nikon-d7000-body-only-digital-slr-cameras.html';
		if($row>1){
			//echo "row:$row<br>";
			if(strlen($URL) > 10 )
				$line = grapeglprice($Sku, $Name, $URL, $line);
		}
	$row++;
	}
}

/////////////open file and write into it///////////////
	$filename = "./eglgrabprice.csv";
	$ourFileHandle = fopen($filename, 'w') or die("can't open file");
	fputs($ourFileHandle, $line);
	//close file after write the content into it
	fclose($ourFileHandle);
	
	echo "<br><br>success. check file link <a href=\"./eglgrabprice.csv\">Here</a><br>";
	
	/////////////////get execution time//////////////
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   echo "This page was created in ".$totaltime." seconds"; 

   
///////////////////////////////////////function///////////////////////////////////////////////
function grapeglprice($Sku, $Name, $URL, $line)
{
	$cookieFile = '/tmp/eglcookie.txt';
	$fp = fopen($cookieFile, "r+");
	// clear content to 0 bits
	ftruncate($fp, 0);
	//close file
	fclose($fp);
	
	$delimiter = "\"";
	$fieldbreak = ",";
	$linebreak = "\n";

	$URL_add_to_cart = 'http://www.eglobaldigitalcameras.com.au/';
	$URL_cart = 'https://www.eglobaldigitalcameras.com.au/index.php?dispatch=checkout.cart';
	
	$ch  = curl_init();
	// initial login page which redirects to correct sign in page, sets some cookies
	if (!file_exists($cookieFile) || !is_writable($cookieFile)){
				echo 'Cookie file missing or not writable.';
				die;
		}
	curl_setopt($ch, CURLOPT_URL, $URL);
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);        
	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	$page = curl_exec($ch);
	//curl_close($ch);
	
	// try to find the actual login form
	if (!preg_match('/<form class="cm-ajax" action="\/" method="post" name="product_form_.*?<\/form>/is', $page, $form)) {
		die('Failed to find log in product form!');
	}
	
	$form = $form[0];
	//echo $form;
	// find all hidden fields which we need to send with our login, this includes security tokens 
	$count = preg_match_all('/<input type="hidden"\s*name="([^"]*)"\s*value="([^"]*)"/i', $form, $hiddenFields);
	
	$postFields = array();
	$product_id = "";
	// turn the hidden fields into an array
	for ($i = 0; $i < $count; ++$i) {
		$postFields[$hiddenFields[1][$i]] = $hiddenFields[2][$i];
		if(strpos($hiddenFields[1][$i], "product_id") !== false)
			$product_id = $hiddenFields[2][$i];
	}
	
	////Manually add fields
	$postFields["dispatch[checkout.add..".$product_id."]"] = "Add to Cart";
	//$postFields["is_ajax"] = "2";
	
	$post = '';
	
	// convert to string, this won't work as an array, form will not accept multipart/form-data, only application/x-www-form-urlencoded
	foreach($postFields as $key => $value) {
		//echo $key . '=' . $value . '<br>';
		$post .= $key . '=' . urlencode($value) . '&';
	}
	
	$post = substr($post, 0, -1);
	//echo $post;
	// set additional curl options using our previous options
	$ch  = curl_init();
	if (!file_exists($cookieFile) || !is_writable($cookieFile)){
				echo 'Cookie file missing or not writable.';
				die;
		}
	
	//curl_setopt($ch, CURLOPT_COOKIE, $cookie); 	
	curl_setopt($ch, CURLOPT_URL, $URL_add_to_cart);
	curl_setopt($ch, CURLOPT_REFERER, $URL);
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36"); # Some server may refuse your request if you dont pass user agent
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	$page1 = curl_exec($ch); // make request
	
	$ch  = curl_init();
	curl_setopt($ch, CURLOPT_URL, $URL_cart);
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);        
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	$page2 = curl_exec($ch);	
	/////////////////////////////////
	// try to find the actual login form
	if (!preg_match('/class="shipping-name">Economy Express Shipping.*?class="shipping-icon-bottom"/is', $page2, $shipping)) {
		die('Failed to find shipping-chooser!');
	}
	$shipping_cost_value = 0;
	$shipping = $shipping[0];
	//if (!preg_match('/<div class="shipping-fee">AU$([^"]*)<\/div>"/i', $shipping, $shipping_cost))
		//die('Failed to find shipping-chooser!');
	preg_match('/<div class="shipping-fee">AU\$(.*)<\/div>/i', $shipping, $shipping_cost);
	//echo "Shipping cost:".str_replace(",", "", $shipping_cost[1])."<br>";	
	$shipping_fee = str_replace(",", "", $shipping_cost[1]);
	preg_match('/Subtotal:&nbsp;<strong>AU\$(.*)<\/strong>/i', $page2, $subtotal_cost);
	//echo "Subtotal cost:".str_replace(",", "", $subtotal_cost[1])."<br>";	
	$subtotal = str_replace(",", "", $subtotal_cost[1]);	
	/////////////////////////////////
	//close connection
	curl_close($ch);	
	//echo $page2;
	$line = $line.
	$delimiter.$Sku.$delimiter.$fieldbreak.
	$delimiter.$Name.$delimiter.$fieldbreak.
	$delimiter.$subtotal.$delimiter.$fieldbreak.
	$delimiter.$shipping_fee.$delimiter.$fieldbreak.
	$delimiter.($shipping_fee+$subtotal).$delimiter.$linebreak;
	
	return $line;
}
?>