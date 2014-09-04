<?php
if ( isset($_SERVER["REMOTE_ADDR"]) )    {
    $ip=$_SERVER["REMOTE_ADDR"];
} else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
    $ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
    $ip=$_SERVER["HTTP_CLIENT_IP"];
} 
$accesslist=false;
if ($ip == "103.1.217.72" ) $accesslist=true;	//www.cosmeparadise.com server
if ($ip == "59.148.228.226" ) $accesslist=true;	//company IP

if ($accesslist==false) 
	{
	echo $ip;
	exit;
	}
echo "start<br>";
$csvfile = "/home/cosmepar/cosmeparadise.com/html/customprogram/grabprice/price1.csv";
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
"SBN price".$Separator.
"ebeauty price".$Separator.
"ozcosmetics price".$Separator.
"brandshopping price".$Separator.
"Hot-Cos price".$Separator.
"Cosmetics Now Price".$Separator.
"TopBuy Price".$Separator.
"YESSTYLE Price".$Separator.
"Cosme-de Price".$Separator.
"Suggest Price(just in comparison site)".$Separator.
"self price".$Separator.$Breakline;

$ozcoslength = strlen("<span class='bl-price' style='font-size: 15px;'>");
$brandshoplength = strlen('<span class="totalPrice">');
$ebeautylength = strlen('<td style="font-size:24px;color:#FFAD5B;font-weight:bold">');
$SBNlength = strlen('<span class="fs33 intPrice">');
$SBNdeclength = strlen('<span class="fs20 decPrice">');
$hotcoslength = strlen('<span class="productSpecialPrice">');
$tbspeciallength = strlen("<h2>Today's Price: <span>");
$tbnormallength = strlen("<h2>Cash Price:<span>");
$cnowspeciallength = strlen("<span style='font-size: 22px; font-weight: bold;'>");
$cnownormallength = strlen('<span style="color: #480072;">Price:</span>');
$yesstylelength = strlen('finalprice">');
$cdlength = strlen('<td width="140" valign="top" class="price_txt">');

if (($handle = fopen($csvfile, "r")) !== FALSE) {	//OPEN CSV
	// FORMAT:  Sku, NAME, EB URL, OZ URL, BS URL, SBN URL
	$row = 1;
	while (($data = fgetcsv($handle)) !== FALSE) {	//go through data
	$Sku = trim($data[0]);
	$Product_Name = trim($data[1]);
	$SBNURL = trim($data[2]);
	$EBURL = trim($data[3]);
	$OZURL = trim($data[4]);
	$BSURL = trim($data[5]);
	$HCURL = trim($data[6]);
	$CNURL = trim($data[7]);
	$TBURL = trim($data[8]);
	$YESURL = trim($data[9]);
	$CDURL = trim($data[10]);
	
	if($row>1){
	
	$NEWline="";
	$selfprice = "can't find product";
	$sbnprice = 0;
	$ebprice = 0;
	$ozprice = 0;
	$bsprice = 0;	
	$hotcprice = 0;
	$cnowprice = 0;
	$tbprice = 0;
	$yesprice = 0;
	$cdprice = 0;
	/////////////////////////get self website price////////////
	$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$Sku); 
	if($_product != null)
	{	$_specialprice = $_product->getFinalPrice();	
		$selfprice = $_specialprice >0?$_specialprice:$_product->getPrice();
	}
	/////////////////////////End get self website price////////////
	/////////////////////////Get ebeauty data///////////////////
	if(strlen($EBURL)> 5)
	{ $ch = curl_init();
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_URL, $EBURL);
     $string = curl_exec($ch);
     curl_close($ch);	
	if (preg_match('/<td style="font-size:24px;color:#FFAD5B;font-weight:bold">/', $string)) {
		$pricepos = strpos($string,'<td style="font-size:24px;color:#FFAD5B;font-weight:bold">');
		$price = substr($string, $pricepos+$ebeautylength, 29);
		$price = str_replace("$", "", $price);
		$price = str_replace(" ", "", $price);
		$price = str_replace("\n", "", $price);
		$price = str_replace("\r", "", $price);
		$ebprice = (double)$price;
		}
	}
	/////////////////////////End Get ebeauty data///////////////////	
	/////////////////////////Get ozcosmetics data///////////////////
	if(strlen($OZURL)> 5)
	{ $ch = curl_init();
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_URL, $OZURL);
     $string = curl_exec($ch);
     curl_close($ch);	
	 if (preg_match("/<span class='bl-price' style='font-size: 15px;'>/", $string)) {
		$pricestart = strpos($string,"<span class='bl-price' style='font-size: 15px;'>")+$ozcoslength;
		$priceend = strpos($string,"</span>",$pricestart);
		//echo $pricestart." and ".$priceend. "and ".$priceend-$pricestart."<br>";
		$price = substr($string, $pricestart, $priceend-$pricestart);
		$price = str_replace("$", "", $price);
		$price = str_replace(" ", "", $price);		
		$price = str_replace("\n", "", $price);
		$price = str_replace("\r", "", $price);
		$ozprice = (double)$price;
		} 
	}
	/////////////////////////End Get ozcosmetics data///////////////////
	/////////////////////////Get brandshopping data///////////////////
	if(strlen($BSURL)> 5)
	{ $ch = curl_init();
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_URL, $BSURL);
     $string = curl_exec($ch);
     curl_close($ch);	
	if (preg_match('/<span class="totalPrice">/', $string)) {
		$pricestart = strpos($string,'<span class="totalPrice">')+$brandshoplength;
		$priceend = strpos($string,"</span>",$pricestart);
		//echo $pricestart." and ".$priceend. "and ".$priceend-$pricestart."<br>";
		$price = substr($string, $pricestart, $priceend-$pricestart);
		$price = str_replace("$", "", $price);
		$price = str_replace("AUD", "", $price);		
		$price = str_replace(" ", "", $price);		
		$price = str_replace("\n", "", $price);
		$price = str_replace("\r", "", $price);
		$bsprice = (double)$price;
		} 
	}
	/////////////////////////End Get brandshopping data///////////////////
	/////////////////////////Get SBN data///////////////////
	 //?CurrId=AUD
	if(strlen($SBNURL)> 5)
	{ $SBNURL = str_replace("#DETAIL", "" , $SBNURL);
	 //echo "<br>".$SBNURL."<br>";
	 $datatopost = array("CurrId" => "AUD");
	 $ch = curl_init ($SBNURL);
     curl_setopt ($ch, CURLOPT_POST, true);
	 curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
	 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
     $string = curl_exec($ch);
     curl_close($ch);
	if (preg_match('/<span class="fs33 intPrice">/', $string)) {
		$pricestart = strpos($string,'<span class="fs33 intPrice">')+$SBNlength;
		$priceend = strpos($string,"</span>",$pricestart);
		//echo $pricestart." and ".$priceend. "and ".$priceend-$pricestart."<br>";
		$price = substr($string, $pricestart, $priceend-$pricestart);
		$decPricestart = strpos($string,'<span class="fs20 decPrice">',$priceend)+$SBNdeclength;
		$decPriceend = strpos($string,"</span>",$decPricestart);
		$decPrice =substr($string, $decPricestart, $decPriceend-$decPricestart);
		$sbnprice = (double)($price.$decPrice);
		} 
	}
	/////////////////////////End Get SBN data///////////////////
	/////////////////////////Get Hot_Cosmetics data///////////////////
	if(strlen($HCURL)> 5)
	{ $ch = curl_init();
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_URL, $HCURL);
     $string = curl_exec($ch);
     curl_close($ch);	
	if (preg_match('/<span class="productSpecialPrice">/', $string)) {
		$pricestart = strpos($string,'<span class="productSpecialPrice">')+$hotcoslength;
		$priceend = strpos($string,"</span>",$pricestart);
		//echo $pricestart." and ".$priceend. "and ".$priceend-$pricestart."<br>";
		$price = substr($string, $pricestart, $priceend-$pricestart);
		$price = str_replace("$", "", $price);
		$price = str_replace("AUD", "", $price);		
		$price = str_replace(" ", "", $price);		
		$price = str_replace("\n", "", $price);
		$price = str_replace("\r", "", $price);
		$hotcprice = (double)$price;
		} 
	}
	/////////////////////////End Get Hot_Cosmetics data///////////////////
	/////////////////////////Get TopBuy data///////////////////
	if(strlen($TBURL)> 5)
	{ $ch = curl_init();
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_URL, $TBURL);
     $string = curl_exec($ch);
     curl_close($ch);	
	if (preg_match("/<h2>Today's Price: <span>/", $string)) {
		$pricestart = strpos($string,"<h2>Today's Price: <span>")+$tbspeciallength;
		$priceend = strpos($string,"</span>",$pricestart);
		//echo $pricestart." and ".$priceend. "and ".$priceend-$pricestart."<br>";
		$price = substr($string, $pricestart, $priceend-$pricestart);
		$price = str_replace("$", "", $price);
		$price = str_replace("AUD", "", $price);		
		$price = str_replace(" ", "", $price);		
		$price = str_replace("\n", "", $price);
		$price = str_replace("\r", "", $price);
		$tbprice = (double)$price;
		}
	else if (preg_match("/<h2>Cash Price:<span>/", $string)) {
		$pricestart = strpos($string,"<h2>Cash Price:<span>")+$tbnormallength;
		$priceend = strpos($string,"</span>",$pricestart);
		//echo $pricestart." and ".$priceend. "and ".$priceend-$pricestart."<br>";
		$price = substr($string, $pricestart, $priceend-$pricestart);
		$price = str_replace("$", "", $price);
		$price = str_replace("AUD", "", $price);		
		$price = str_replace(" ", "", $price);		
		$price = str_replace("\n", "", $price);
		$price = str_replace("\r", "", $price);
		$tbprice = (double)$price;
		}
	}
	/////////////////////////End Get TopBuy data///////////////////
	/////////////////////////Get Cosmetics Now data///////////////////
	if(strlen($CNURL)> 5)
	{ $ch = curl_init();
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_URL, $CNURL);
     $string = curl_exec($ch);	 
     curl_close($ch);	
	 //echo $string;
	 if(strpos( $string , "Special:") !== false)
	{	if (preg_match("/<span style='font-size: 22px; font-weight: bold;'>/", $string)) {
			$pricestart = strpos($string,"<span style='font-size: 22px; font-weight: bold;'>")+$cnowspeciallength;
			$priceend = strpos($string,"</span>",$pricestart);
			//echo $pricestart." and ".$priceend. "and ".$priceend-$pricestart."<br>";
			$price = substr($string, $pricestart, $priceend-$pricestart);
			$price = str_replace("$", "", $price);
			$price = str_replace("AUD", "", $price);		
			$price = str_replace(" ", "", $price);		
			$price = str_replace("\n", "", $price);
			$price = str_replace("\r", "", $price);
			$cnowprice = (double)$price;
			} 
			//echo "<br>exist special<br>";
	} 
	 else
	{	if (preg_match('/<span style="color: #480072;">Price:/', $string)) {
			$pricestart = strpos($string,'<span style="color: #480072;">Price:</span>')+$cnownormallength;
			$priceend = strpos($string,"<br />",$pricestart);
			//echo $pricestart." and ".$priceend. "and ".$priceend-$pricestart."<br>";
			$price = substr($string, $pricestart, $priceend-$pricestart);
			$price = str_replace("$", "", $price);
			$price = str_replace("AUD", "", $price);		
			$price = str_replace(" ", "", $price);		
			$price = str_replace("\n", "", $price);
			$price = str_replace("\r", "", $price);
			$cnowprice = (double)$price;
			} 
			//echo "<Br>normal price<br>";
	}
	}
	/////////////////////////End Cosmetics Now data///////////////////
	/////////////////////////Get YESSTYLE data///////////////////
	if(strlen($YESURL)> 5)
	{ $ch = curl_init();
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_URL, $YESURL);
     $string = curl_exec($ch);
     curl_close($ch);	
	if (preg_match('/finalprice">/', $string)) {
		$pricestart = strpos($string,'finalprice">')+$yesstylelength;
		$priceend = strpos($string,"</b>",$pricestart);
		//echo $pricestart." and ".$priceend. "and ".$priceend-$pricestart."<br>";
		$price = substr($string, $pricestart, $priceend-$pricestart);
		$price = str_replace("$", "", $price);
		$price = str_replace("AU", "", $price);		
		$price = str_replace(" ", "", $price);		
		$price = str_replace("\n", "", $price);
		$price = str_replace("\r", "", $price);
		$yesprice = (double)$price;
		} 
	}
	/////////////////////////End Get YESSTYLE data///////////////////
	/////////////////////////Get COSME-DE data///////////////////
	if(strlen($CDURL)> 5)
	{ $ch = curl_init();
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_URL, $CDURL);
     $string = curl_exec($ch);
     curl_close($ch);	
	if (preg_match('/<td width="140" valign="top" class="price_txt">/', $string)) {
		$pricestart = strpos($string,'<td width="140" valign="top" class="price_txt">')+$cdlength;
		$priceend = strpos($string,"</b>",$pricestart);
		//echo $pricestart." and ".$priceend. "and ".$priceend-$pricestart."<br>";
		$price = substr($string, $pricestart, $priceend-$pricestart);
		$price = str_replace("<b>", "", $price);
		$price = str_replace("$", "", $price);
		$price = str_replace("AUD", "", $price);		
		$price = str_replace(" ", "", $price);		
		$price = str_replace("\n", "", $price);
		$price = str_replace("\r", "", $price);
		$cdprice = (double)$price;
		} 
	}
	/////////////////////////End Get COSME-DE data///////////////////
	
	
	if($ozprice == 0 || $bsprice == 0 || $ebprice == 0 || $sbnprice == 0 || $hotcprice == 0 || $tbprice == 0 || $cnowprice == 0 || $yesprice == 0 || $cdprice == 0)
		{	$newozprice = "no price";
			$newbsprice = "no price";
			$newebprice = "no price";
			$newsbnprice = "no price";
			$newhotcprice = "no price";
			$newcnowpriceprice = "no price";
			$newtbprice = "no price";
			$newyesprice = "no price";
			$newcdprice = "no price";
		
			if($ozprice != 0)
				$newozprice = $ozprice;
			if($bsprice != 0)
				$newbsprice = $bsprice+8;
			if($ebprice != 0)
				$newebprice = $ebprice;
			if($sbnprice != 0)
				$newsbnprice = $sbnprice;
			if($hotcprice != 0)
				$newhotcprice = $hotcprice+9.5;
			if($tbprice != 0)
				$newtbprice = $tbprice+8.95;
			if($cnowprice != 0)
				$newcnowpriceprice = $cnowprice;
			if($yesprice != 0)
				$newyesprice = $yesprice;
			if($cdprice != 0)
				$newcdprice = $cdprice;
			
			$findprice = array($newozprice, $newbsprice, $newebprice, $newsbnprice, $newhotcprice, $newcnowpriceprice,  $newtbprice, $newyesprice, $newcdprice );
			$remove = array("no price");
			$result = array_diff($findprice, $remove); 
			//print_r($result);
			$NEWline .=	$Enclosure.$Sku.$Enclosure.$Separator.
						$Enclosure.$Product_Name.$Enclosure.$Separator.
						$newsbnprice.$Separator.//SBN price
						$newebprice.$Separator.	//ebeauty price
						$newozprice.$Separator.	//ozcosmetics price
						$newbsprice.$Separator.	//brandshopping price
						$newhotcprice.$Separator.//hot cosmetics price
						$newcnowpriceprice.$Separator.//Cos Now price
						$newtbprice.$Separator.//Topbuy price
						$newyesprice.$Separator.//YESSTYLE price
						$newcdprice.$Separator.//Cosme-De price
						(min($result)-0.01).$Separator.	//suggest price
						$selfprice.$Breakline;		//Ourselves price
		}
	else
		{
			$NEWline .= $Enclosure.$Sku.$Enclosure.$Separator.
			$Enclosure.$Product_Name.$Enclosure.$Separator.
			$sbnprice.$Separator.		//SBN price
			$ebprice.$Separator.		//ebeauty price
			$ozprice.$Separator.		//ozcosmetics price
			($bsprice+8).$Separator.	//brandshopping price
			($hotcprice+9.5).$Separator.		//hot cosmetics price
			$cnowprice.$Separator.		//cos now price
			($tbprice+8.95).$Separator.		//TopBuy price
			$yesprice.$Separator.		//YESSTYLE price
			$cdprice.$Separator.		//Cosme-De price
			(min($ozprice, ($bsprice+8), $ebprice, $sbnprice, ($hotcprice+9.5), $cnowprice,($tbprice+8.95), $yesprice,$cdprice)-0.01).$Separator.		//suggest price
			$selfprice.$Breakline;		//Ourselves price
		}
	$line .= $NEWline; ///// Add to new line
	//echo $NEWline."<br>";
	}	
	$row++;
	//ob_flush();
    //flush();
	}
}
else 
echo "can't open file <br>";
fclose($handle);



/////////////open file and write into it///////////////
	$filename = "./grabprice/grabprice_p1.csv";
	$ourFileHandle = fopen($filename, 'w') or die("can't open file");
	fputs($ourFileHandle, $line);
	//close file after write the content into it
	fclose($ourFileHandle);
	
	
	/////////////////get execution time//////////////
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   sendEmail($totaltime); 
   exit;
   
   function sendEmail($totaltime)
	 {		
		//////////////////////////////////////////////////////////////////////		
		$path_include = "./phpMailer/class.phpmailer.php";

		// Include configuration file
		if(!file_exists($path_include)) {
			exit('<HTML><HEAD><TITLE>404 Not Found</TITLE></HEAD><BODY><H1>Not Found</H1>Please ensure that this file is in the root directory, or make sure the path to the directory where the configure.php file is located is defined corectly above in $path_include variable</BODY></HTML>');
		}
		else {
			//echo "get require!<br>";
			require_once $path_include;
		}

		$mail = new PHPMailer;
		
		$mail->IsSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'cosmepar.nextmp.net';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'marketing@cosmeparadise.com';                            // SMTP username
		$mail->Password = 'CrustySunCotesTenses17';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
		
		$mail->From = 'marketing@cosmeparadise.com';
		$mail->FromName = 'Grap Price Auto';
		$mail->AddAddress('support@cosmeparadise.com', 'Berry Lai');  // Add a recipient
		$mail->AddAddress('info@cosmeparadise.com', 'Clara');  // Add a recipient
		$mail->AddAddress('cs1@cosmeparadise.com', 'Sec');  // Add a recipient
		//$mail->AddAddress('union.programmer@gmail.com');               // Name is optional
		//$mail->AddReplyTo('support@cosmeparadise.com', 'Information');
		//$mail->AddCC('cc@example.com');
		//$mail->AddBCC('bcc@example.com');
		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		$mail->AddAttachment('/home/cosmepar/cosmeparadise.com/html/customprogram/grabprice/grabprice_p1.csv');         // Add attachments
		//$mail->AddAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->IsHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = 'Grab Price part 1 finished';
		$mail->Body    = "It take ".$totaltime." sec to finish.<br><br>success. check file link <a href=\"http://www.cosmeparadise.com/customprogram/grabprice/grabprice_p1.csv\">Here</a><br> Or download from attachment.";
		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		
		if(!$mail->Send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
		exit;
		}
		
		echo 'Message has been sent';
	
	 }
?>