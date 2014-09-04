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
<h2>Grab Link programme</h2>
<b>Please keep the format: sku, name, URL(SKIP first row of header)</b>
<form action="grablink.php" method="post" enctype="multipart/form-data">
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

////////////////////////get into magento code/////////////////////
set_time_limit(0);
ignore_user_abort();
error_reporting(E_ALL^E_NOTICE);
$_SVR = array();

$path_include = "../app/Mage.php";

// Include configuration file
if(!file_exists($path_include)) {
	exit('<HTML><HEAD><TITLE>404 Not Found</TITLE></HEAD><BODY><H1>Not Found</H1>Please ensure that this file is in the root directory, or make sure the path to the directory where the configure.php file is located is defined corectly above in $path_include variable</BODY></HTML>');
}
else {
	require_once $path_include;
}

// Get default store code
$default_store = Mage::app()->getStore();
$default_store_code = $default_store->getCode();

if (isset($_GET['store']) && ($_GET['store'] != "")) {
	$store = $_GET['store'];
}
else {
	$store = $default_store_code;
}

Mage::app($store);

////////////////////////////start////////////////////////////
$Enclosure = "\"";
$Separator = ",";
$Breakline = "\n";
echo "start<br>";
$line = "sku".$Separator.
"name".$Separator.
"SBN LINK".$Separator.
"ebeauty LINK".$Separator.
"ozcosmetics LINK".$Separator.
"brandshopping LINK".$Separator.
"Hot-Cos LINK".$Separator.
"Cosmetics Now LINK".$Separator.
"TopBuy LINK".$Separator.
"YESSTYLE LINK".$Separator.
"Cosme-de LINK".$Separator.$Breakline;

$ozcoslength = strlen("<span class='bl-price' style='font-size: 15px;'>");
$brandshoplength = strlen('<span class="totalPrice">');
$imagelink = "http://img.myshopping.com.au/ClientLogo/";
$productlink = '<a class="plistBtnA" target="_blank" rel="nofollow" href="';

if (($handle = fopen($csvfile, "r")) !== FALSE) {	//OPEN CSV
	// FORMAT:  Sku, NAME, EB URL, OZ URL, BS URL, SBN URL
	$row = 1;
	while (($data = fgetcsv($handle)) !== FALSE) {	//go through data
	$Sku = trim($data[0]);
	$Product_Name = trim($data[1]);
	$GrapURL = trim($data[2]);
	
	if($row>1){
	$NEWline="";
	$selfprice = "can't find product";
	$newlink = 0;
	
	/////////////////////////get self website price////////////
	//$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$Sku); 	
	/////////////////////////Get Cosmetics Now data///////////////////
	if(strlen($GrapURL)> 5)
	{ $ch = curl_init();
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_URL, $GrapURL);
     $string = curl_exec($ch);	 
     curl_close($ch);	
	 //echo $string;
	 $startpos = 0;
	 $imagestart = 0;
	 $count = 0;
	 $NEWline = "";
	 $StrawberryNET_com="/";
	 $eGlobaL_Beauty="/";
	 $OZ_Cosmetics="/";
	 $Brand_Shopping="/";
	 $Cosmetics_Now="/";
	 $HotCosmetics="/";
	 $TopBuy="/";
	 $YesStyle="/";
	 $COSME_DE="/";
	 
	 if(strpos( $string , $imagelink) !== false)
	{	if(strpos($string, "You may also be interested in the following results.") !== false)
			$stoppos = strpos($string, "You may also be interested in the following results.");
		else
			$stoppos = strlen($string);
		echo "stoppos:".$stoppos."<br>";
		
		while($stoppos > $imagestart || $count < 20)
		{$imagestart = strpos($string,$imagelink, $imagestart+strlen($imagelink));		
		if($stoppos < $imagestart || $count >= 20)
		{ 	//echo "position or count stop!!!!!";
			break;
		}
		$imagestop = strpos($string, '">', $imagestart);	
		$imageURL = substr($string, $imagestart, $imagestop-$imagestart);
		
		$productstart = strpos($string,$productlink, $imagestop+2) + +strlen($productlink);
		$productstop = strpos($string, '">', $productstart);
		$productURL = substr($string, $productstart, $productstop-$productstart);
		
		
		$dch = curl_init($productURL);
		curl_setopt($dch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($dch, CURLOPT_HEADER, TRUE); // We'll parse redirect url from header.
		curl_setopt($dch, CURLOPT_FOLLOWLOCATION, FALSE); // We want to just get redirect url but not to follow it.
		$response = curl_exec($dch);
		preg_match_all('/^Location:(.*)$/mi', $response, $matches);
		curl_close($dch);
		//echo !empty($matches[1]) ? trim($matches[1][0]) : 'No redirect found';
		$productURL = !empty($matches[1]) ? trim($matches[1][0]) : 'No redirect found';
		
		
		if(strpos( $imageURL , "StrawberryNET_com"))	
		{	$SBNURL = explode("?", $productURL);
			$StrawberryNET_com = $SBNURL[0];		
		}
		else if(strpos( $imageURL , "eGlobaL_Beauty"))
			$eGlobaL_Beauty = $productURL;
		else if(strpos( $imageURL , "OZ_Cosmetics"))
			$OZ_Cosmetics = $productURL;
		else if(strpos( $imageURL , "Brand_Shopping"))
			$Brand_Shopping = $productURL;
		else if(strpos( $imageURL , "HotCosmetics"))
			$HotCosmetics = $productURL;
		else if(strpos( $imageURL , "Cosmetics_Now"))
			$Cosmetics_Now = $productURL;
		else if(strpos( $imageURL , "TopBuy"))
			$TopBuy = $productURL;
		else if(strpos( $imageURL , "YesStyle"))
			$YesStyle = $productURL;
		else if(strpos( $imageURL , "COSME_DE"))
			$COSME_DE = $productURL;
	
		//echo "Image URL:".$imageURL." and product URL:".$productURL."<br>";
		$count++;
		}
		/////////////////////////Grab data for link///////////////////
	
		$NEWline .= $Enclosure.$Sku.$Enclosure.$Separator.	
		$Enclosure.$Product_Name.$Enclosure.$Separator.	
		$Enclosure.$StrawberryNET_com.$Enclosure.$Separator.	
		$Enclosure.$eGlobaL_Beauty.$Enclosure.$Separator.	
		$Enclosure.$OZ_Cosmetics.$Enclosure.$Separator.	
		$Enclosure.$Brand_Shopping.$Enclosure.$Separator.	
		$Enclosure.$HotCosmetics.$Enclosure.$Separator.	
		$Enclosure.$Cosmetics_Now.$Enclosure.$Separator.	
		$Enclosure.$TopBuy.$Enclosure.$Separator.	
		$Enclosure.$YesStyle.$Enclosure.$Separator.	
		$Enclosure.$COSME_DE.$Enclosure.$Breakline;		// end
		
		$line .= $NEWline; ///// Add to new line
		//echo $NEWline."<br>";
	} 	 
	}
	
	}	
	$row++;
	//ob_flush();
    flush();
	}
}
else 
"can't open file <br>";
fclose($handle);



/////////////open file and write into it///////////////
	$filename = "./grablink.csv";
	$ourFileHandle = fopen($filename, 'w') or die("can't open file");
	fputs($ourFileHandle, $line);
	//close file after write the content into it
	fclose($ourFileHandle);
	
	echo "<br><br>success. check file link <a href=\"./grablink.csv\">Here</a><br>";
	
	/////////////////get execution time//////////////
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   echo "This page was created in ".$totaltime." seconds </body></html>"; 
   
   
////////////////function////////////////
//function 
?>