<?php
//if ( isset($_SERVER["REMOTE_ADDR"]) )    {
//    $ip=$_SERVER["REMOTE_ADDR"];
//} else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
//    $ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
//} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
//    $ip=$_SERVER["HTTP_CLIENT_IP"];
//} 
//$accesslist=false;
//if ($ip == "103.1.217.72" ) $accesslist=true;	//www.cosmeparadise.com server
//if ($ip == "59.148.228.226" ) $accesslist=true;	//company IP

//if ($accesslist==false) 
//	{
//	echo $ip;
//	exit;
//	}
	
$directinput = false;
if(($_POST["txtArea"]) != ""){
		$tempskulist = array_map('trim',explode(",",$_POST["txtArea"]));
		//print_r($tempskulist);
		$directinput = true;
}

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
<h2>Product sku To HTML programme</h2>
<b>Please keep the format: sku</b>
<form id="usrform" action="ProductGridToHTML-test.php" method="post" enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> <br />
Top Banner image-URL<input type="text" name="banner1" size="100" value="http://a792ded0d2-custmedia.vresp.com/library/1386642718/63406fe71b/UnionDropShip/unionlogo.gif"/><br />
Top Banner destination-URL<input type="text" name="banner1D" size="100"/><br />

Full Page Destination Text<input type="text" name="FULLLIST_TEXT" value="Click here for full specials list" size="100"/><br />
Full Page Destination URL<input type="text" name="FULLLIST_LINK" size="100"/><br />
Head Content<br />
<textarea name="headcontent" rows="10" cols="70"></textarea>
<br /><br />
foot Content<br />
<textarea name="footcontent" rows="10" cols="70"></textarea>
<br />
<input type="submit" name="submit" value="Convert To HTML" />
</form>
<br />
Input sku separate with comma "," (If there is any text input, it will skip the uploaded file!)
<br />
<textarea form="usrform" name="txtArea" rows="10" cols="70"></textarea>
</body>
</html>

<?php	
  exit;
  }
if($directinput == false)
{	$csvfile = $_FILES["file"]["tmp_name"];
	
	if(!file_exists($csvfile)) {
		echo "File not found.";
		exit;
	}
	
	$size = filesize($csvfile);
	if(!$size) {
		echo "File is empty.\n";
		exit;
	}
}
?>

<?php
////////////////////////////start////////////////////////////
$Enclosure = "\"";
$Separator = ",";
$Breakline = "\n";
////////////////////////get into magento /////////////////////
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
/////////////// call SQL read////////////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		
/////////////// call SQL write///////////////
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	

$product_html = '<html>
<head><title>Cosmeparadise Promotion</title></head>';
$skulist = array();
 $product_html .= '<body style="background: #fbf9f4;12px/1.55 Arial,Helvetica,sans-serif">';
 $product_html .=  '<table frame="box" cellspacing="0" cellpadding="0" align="center" style="background-color:white;border-width: 1px; border-color:#cccccc;width: 560px;">
<tr><td align="center" colspan="5">       	   
</td></tr>';
	 
///////////////////////Show Image///////////////////
if(isset($_POST["banner1"]) && $_POST["banner1"] != "")
	$product_html .=  '<tr><td align="center" colspan="5"><a target="_blank" href="'.$_POST["FULLLIST_LINK"].'"><img style="display:block;border:0;" src="'.$_POST["banner1"].'" alt="top image" /></a></td></tr>';
		
$product_html .=  '<tr><td colspan="5"><p style="padding:10">'.str_replace("\n", "<br />", $_POST["headcontent"]).'</p></td></tr>';

 $product_html .= '<tr>';
 if (($handle = fopen($csvfile, "r")) !== FALSE || $directinput == true) {	//OPEN CSV or read textarea
	// FORMAT:  Sku, 
	$row = 1;
	if($directinput == true)
	{	$skulist = $tempskulist;
		//echo "TEXT";
	}
	else
	{	//echo "CSV";
		while (($data = fgetcsv($handle)) !== FALSE) {	//go through data	
			array_push($skulist, trim($data[0]));
		}
	}
  $skucount = count($skulist);
  $counting = 1;
 foreach($skulist as $sku)
{
	$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku); 
	if($_product == null)
		continue;
		
	$prod_name = $_product->getName();	
	$prod_id = $_product->getId();
	
	////////////show save price from MSRP////////////
	$_specialprice = $_product->getFinalPrice();
	
	$_price = $_specialprice >0?$_specialprice:$_product->getPrice();	
	/////////////////////////fist cell, Manufacturer and Name///////////////////	
	$product_html .= '<td colspan="4" align="left" style="padding:8px;" width="350">
	<b>'.$_product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($_product).'</b><br />
	<div style="font-weight:bold;font-size:12px;color:#000;text-align:left;text-transform:uppercase;font-family:arial;"><a target="_blank" style="color:#000;font-weight:bold;text-decoration: none;" href="'.$_product->getProductUrl().'" title="'.$_product->getName().'">'.$_product->getName().'</a></div></td>';
	
  
	$product_html .= '<td align="right" style="padding-right:10px;"><span style="color:#F660AB;font-weight:bold;font-size:18px;">USD$'.number_format($_price,2,'.','').'</span></td>';
	
	 $product_html .=  '</tr><tr>';
	 $counting++;
}
$product_html .=  '</tr><tr><td><p>&nbsp;</p></td></tr>';	///close products table

///////////////////////Show Click Link///////////////////
if(isset($_POST["banner1"]) && $_POST["banner1"] != "")
	$product_html .=  '<tr><td colspan="5"><a target="_blank" href="'.$_POST["banner1D"].'">'.$_POST["FULLLIST_TEXT"].'</a></td></tr>';
	
///////////////show foot content//////////////
$product_html .=  '<tr><td colspan="5"><p style="padding:10">'.str_replace("\n", "<br />", $_POST["footcontent"]).'</p></td></tr>';

$product_html .=  '</table>';



$product_html .=  '</body></html>';

echo $product_html;

sendEmail($product_html);

}
else 
echo "can't open file <br />";
fclose($handle);


function sendEmail($product_html)
	 {		
		//////////////////////////////////////////////////////////////////////		
		$path_include = "./phpMailer/class.phpmailer.php";
		require_once $path_include;		
		$mail = new PHPMailer;		
		$mail->IsSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'sipau2-01.nexcess.net';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'order2@cosmeparadise.com';                            // SMTP username
		$mail->Password = 'AppealVicesCrashJoyous77';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted		 
		$mail->From = 'order2@cosmeparadise.com';
		$mail->FromName = 'SBN order '.$subject_status;
		$mail->AddAddress('union.programmer@gmail.com', 'Berry Lai');  // Add a recipient
		//$mail->AddAddress('marketing@cosmeparadise.com', 'Kitty');  // Add a recipient
		//$mail->AddAddress('union.b2c@gmail.com', 'Clara');  // Add a recipient
		//$mail->AddAddress('union.emarketing@gmail.com', 'Kitty');  // Add a recipient
		//$mail->AddAddress('order2@cosmeparadise.com', 'cosme order2'); // Name is optional	
		$mail->AddBCC('support@cosmeparadise.com', 'Berry Lai');		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters		
		$mail->IsHTML(true);                                  // Set email format to HTML		
		$mail->Subject = 'EDM email';
		$mail->Body    = $product_html;	
		
		if(!$mail->Send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;		
		}
		//else
		//	echo 'Message has been sent <br />';		 
	 } 
?>