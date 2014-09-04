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
<form id="usrform" action="ProductGridToHTML.php" method="post" enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> <br />
Top Banner image-URL<input type="text" name="banner1" size="100"/><br />
Top Banner destination-URL<input type="text" name="banner1D" size="100"/><br />

Promotion Banner 1 image-URL<input type="text" name="banner2" size="100" value="https://a792ded0d2-custmedia.vresp.com/library/1380093068/e075f72324/Fashion-edm550x100.gif" /><br />
Promotion Banner 1 destination-URL<input type="text" name="banner2D" size="100" value="http://www.cosmeparadise.com/appeal-accessories/womans-apparel.html" /><br />

Promotion Banner 2 image-URL<input type="text" name="banner3" size="100" value="https://a792ded0d2-custmedia.vresp.com/library/1380093104/c7a9c854d7/makeupbrushes-edm(2).gif" /><br />
Promotion Banner 2 destination-URL<input type="text" name="banner3D" size="100" value="http://www.cosmeparadise.com/tools-accessories.html?cat=88" /><br />

Promotion Banner 3 image-URL<input type="text" name="banner4" size="100" value="https://a792ded0d2-custmedia.vresp.com/library/1380093124/b0174afd04/cosmeticbag-edm.gif" /><br />
Promotion Banner 3 destination-URL<input type="text" name="banner4D" size="100" value="http://www.cosmeparadise.com/tools-accessories/bags-cases.html" /><br />

Promotion Banner 4 image-URL<input type="text" name="banner5" size="100" value="https://a792ded0d2-custmedia.vresp.com/library/1380093163/0673ce8c91/blendingsponge-edm.gif" /><br />
Promotion Banner 4 destination-URL<input type="text" name="banner5D" size="100" value="http://www.cosmeparadise.com/teardrop-blender-sponge.html" /><br />

Promotion Banner 5 image-URL<input type="text" name="banner6" size="100" value="https://a792ded0d2-custmedia.vresp.com/library/1380093185/dbc0a5c712/Newcustomer-skinfood-gift-edm.gif" /><br />
Promotion Banner 5 destination-URL<input type="text" name="banner6D" size="100" value="http://www.cosmeparadise.com/free-max-factor-eyeshadow-on-all-your-purchases" /><br />

Promotion Banner 6 image-URL<input type="text" name="banner7" size="100" value="https://a792ded0d2-custmedia.vresp.com/library/1380093202/f0a16bc848/MF-EDM-BANNER.gif" /><br />
Promotion Banner 6 destination-URL<input type="text" name="banner7D" size="100" value="http://www.cosmeparadise.com/free-max-factor-eyeshadow-on-all-your-purchases" /><br />

Promotion Banner 7 image-URL<input type="text" name="banner8" size="100" value="https://a792ded0d2-custmedia.vresp.com/library/1380093257/c6309dd83e/CS-feedback550x100.gif" /><br />
Promotion Banner 7 destination-URL<input type="text" name="banner8D" size="100" value="http://au.shopping.com/xMWR-Cosme%20Paradise~MRD-510986?sb=1" /><br />

Promotion Banner 8 image-URL<input type="text" name="banner9" size="100" value="https://a792ded0d2-custmedia.vresp.com/library/1380093257/c6309dd83e/CS-feedback550x100.gif" /><br />
Promotion Banner 8 destination-URL<input type="text" name="banner9D" size="100" value="http://au.shopping.com/xMWR-Cosme%20Paradise~MRD-510986?sb=1" /><br />

Promotion Banner 9 image-URL<input type="text" name="banner10" size="100" value="https://a792ded0d2-custmedia.vresp.com/library/1380093257/c6309dd83e/CS-feedback550x100.gif" /><br />
Promotion Banner 9 destination-URL<input type="text" name="banner10D" size="100" value="http://au.shopping.com/xMWR-Cosme%20Paradise~MRD-510986?sb=1" /><br />

Promotion Banner 10 image-URL<input type="text" name="banner11" size="100" value="https://a792ded0d2-custmedia.vresp.com/library/1380093257/c6309dd83e/CS-feedback550x100.gif" /><br />
Promotion Banner 10 destination-URL<input type="text" name="banner11D" size="100" value="http://au.shopping.com/xMWR-Cosme%20Paradise~MRD-510986?sb=1" /><br />
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

$product_html = '';
$skulist = array();
 $product_html .= '<body style="background: #fbf9f4;12px/1.55 Arial,Helvetica,sans-serif">';
 $product_html .=  '<table frame="box" cellspacing="0" cellpadding="0" align="center" style="background-color:white;border-width: 1px; border-color:#cccccc;table-layout: fixed;width: 560px;">
<tr><td colspan="3"><p align="center"><a href="http://www.cosmeparadise.com/"><span style="text-decoration:none;text-underline:none"><img border="0" src="https://a792ded0d2-custmedia.vresp.com/library/1364200909/6c81b2ad0a/logo.gif" alt="Cosme Paradise"></span></a></p></td></tr>
<tr><td align="center" colspan="3">       
<p><img usemap="#navigationbar" border="0" width="545" height="25" src="https://a792ded0d2-custmedia.vresp.com/library/1364200750/9be25e48b0/EDM-Bar-2.gif" alt="top menu"></p>
<map name="navigationbar">
<area target="_blank" shape="rect" coords="0, 2, 70, 25" href="http://www.cosmeparadise.com/skincare.html">
<area target="_blank" shape="rect" coords="71, 2, 155, 25" href="http://www.cosmeparadise.com/fragrance.html">
<area target="_blank" shape="rect" coords="156, 2, 224, 25" href="http://www.cosmeparadise.com/makeup.html">
<area target="_blank" shape="rect" coords="227, 2, 308, 25" href="http://www.cosmeparadise.com/bodycare.html">
<area target="_blank" shape="rect" coords="311, 2, 389, 25" href="http://www.cosmeparadise.com/haircare.html">
<area target="_blank" shape="rect" coords="393, 2, 467, 25" href="http://www.cosmeparadise.com/suncare.html">
<area target="_blank" shape="rect" coords="470, 2, 542, 25" href="http://www.cosmeparadise.com/men.html">
</map>	   
</td></tr>';
	   
///////////////////////Show Image///////////////////
if(isset($_POST["banner1"]) && $_POST["banner1"] != "")
	$product_html .=  '<tr><td align="center" colspan="3"><a target="_blank" href="'.$_POST["banner1D"].'"><img style="display:block;border:0;" src="'.$_POST["banner1"].'" alt="top image" /></a></td></tr>';
		
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
	
	$product_html .= '<td width="170px" height="380px" align="center">';	
	//////////////product image///////////////
	$product_html .=  '<div style="padding-left:5px;height:330px;"><a target="_blank" style="word-wrap:break-word;" href="'.$_product->getProductUrl().'" ><img width="135px" style="border:0;" src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135).'" alt="Product Detail" /></a>';
	
	
	////////////show save price from MSRP////////////
	$_specialprice = $_product->getFinalPrice();
	
	$_price = $_specialprice >0?$_specialprice:$_product->getPrice();	
			
	$product_html .= '<h2 style="font-weight:bold;font-size:11px;color:#000;text-align:left;text-transform:uppercase;font-family:arial;"><a target="_blank" style="color:#000;font-weight:bold;text-decoration: none;" href="'.$_product->getProductUrl().'" title="'.$_product->getName().'">'.$_product->getName().'</a></h2>';
	
	if($_product->getMsrp()>$_price){
		$product_html .=  '<div style="font-family:arial;"> <span style="font-size:14px;color:#000;">RRP:</span><span style="font-size:14px;color:#000;"><s>'.(Mage::helper('core')->currency($_product->getMsrp(),true,false)).'</s></span></div>';		
	}
	
    if($_specialprice >0 && $_product->getPrice() > $_specialprice)
	{	$product_html .= '<div style="font-family:arial;"><span style="color:#F660AB;font-weight:bold;font-size:24px;text-decoration: line-through;">A$'.number_format($_product->getPrice(),2,'.','').'</span><br />';
		$product_html .= '<span style="font-size:20px;font-weight:bold;color:#FF0000;">SAVE '.(round(($_product->getMsrp()-$_price)/$_product->getMsrp(),2)*100).'%</span><br />';
		$product_html .= '<span style="font-size:20px;font-weight:bold;color:#FF0000;">A$'.number_format($_price,2,'.','').'</span></div>';
	}	
	else
	{	$product_html .= '<div style="font-family:arial;"><span style="font-size:24px;font-weight:bold;color:#FF0000;">SAVE '.(round(($_product->getMsrp()-$_price)/$_product->getMsrp(),2)*100).'%</span>';
		$product_html .= '<span style="color:#F660AB;font-weight:bold;font-size:24px">A$'.number_format($_price,2,'.','').'</span></div>';
	}
	///////////////add to cart button///////////////
	 $product_html .=  '</div><a target="_blank" style="word-wrap:break-word;" href="'.$_product->getProductUrl().'" ><img style="border:0;" src="https://a792ded0d2-custmedia.vresp.com/library/1380093601/9c48e2b20b/BUYNOW.gif" alt="AddToCart" /></a>';
	$product_html .=  '</td>';
	
	/*if( $counting % 3 == 0 && $counting+3 != $skucount)
		$product_html .=  '</tr><tr><td colspan="3"><hr /></td></tr><tr>';
	else if($counting % 3 == 0 && $counting+3 == $skucount)
		$product_html .=  '</tr><tr>';*/
		
	if( $counting % 3 == 0)	 
		$product_html .=  '</tr><tr>';
	 $counting++;
}
$product_html .=  '</tr><tr><td><p>&nbsp;</p></td></tr>';	///close products table
$product_html .=  '<tr><td align="center" colspan="3"><img style="border:0;" src="https://a792ded0d2-custmedia.vresp.com/library/1380092964/6a5a8541bc/promo-bar.gif" alt="promotion bar" /></td></tr>';
/////////////////////////////////Here for Promotion Banner/////////////////////////	
if(isset($_POST["banner2"]) && $_POST["banner2"] != "")
	$product_html .=  '<tr><td align="center" colspan="3"><a target="_blank" href="'.$_POST["banner2D"].'"><img style="display:block;border:0;" src="'.$_POST["banner2"].'" alt="promotion oimage" /></a></td></tr>';
if(isset($_POST["banner3"]) && $_POST["banner3"] != "")
	$product_html .=  '<tr><td align="center" colspan="3"><a target="_blank" href="'.$_POST["banner3D"].'"><img style="display:block;border:0;" src="'.$_POST["banner3"].'" alt="promotion oimage" /></a></td></tr>';
if(isset($_POST["banner4"]) && $_POST["banner4"] != "")
	$product_html .=  '<tr><td align="center" colspan="3"><a target="_blank" href="'.$_POST["banner4D"].'"><img style="display:block;border:0;" src="'.$_POST["banner4"].'" alt="promotion oimage" /></a></td></tr>';
if(isset($_POST["banner5"]) && $_POST["banner5"] != "")
	$product_html .=  '<tr><td align="center" colspan="3"><a target="_blank" href="'.$_POST["banner5D"].'"><img style="display:block;border:0;" src="'.$_POST["banner5"].'" alt="promotion oimage" /></a></td></tr>';
if(isset($_POST["banner6"]) && $_POST["banner6"] != "")
	$product_html .=  '<tr><td align="center" colspan="3"><a target="_blank" href="'.$_POST["banner6D"].'"><img style="display:block;border:0;" src="'.$_POST["banner6"].'" alt="promotion oimage" /></a></td></tr>';
if(isset($_POST["banner7"]) && $_POST["banner7"] != "")
	$product_html .=  '<tr><td align="center" colspan="3"><a target="_blank" href="'.$_POST["banner7D"].'"><img style="display:block;border:0;" src="'.$_POST["banner7"].'" alt="promotion oimage" /></a></td></tr>';
if(isset($_POST["banner8"]) && $_POST["banner8"] != "")
	$product_html .=  '<tr><td align="center" colspan="3"><a target="_blank" href="'.$_POST["banner8D"].'"><img style="display:block;border:0;" src="'.$_POST["banner8"].'" alt="promotion oimage" /></a></td></tr>';
if(isset($_POST["banner9"]) && $_POST["banner9"] != "")
	$product_html .=  '<tr><td align="center" colspan="3"><a target="_blank" href="'.$_POST["banner9D"].'"><img style="display:block;border:0;" src="'.$_POST["banner9"].'" alt="promotion oimage" /></a></td></tr>';
if(isset($_POST["banner10"]) && $_POST["banner10"] != "")
	$product_html .=  '<tr><td align="center" colspan="3"><a target="_blank" href="'.$_POST["banner10D"].'"><img style="display:block;border:0;" src="'.$_POST["banner10"].'" alt="promotion oimage" /></a></td></tr>';
if(isset($_POST["banner11"]) && $_POST["banner11"] != "")
	$product_html .=  '<tr><td align="center" colspan="3"><a target="_blank" href="'.$_POST["banner11D"].'"><img style="display:block;border:0;" src="'.$_POST["banner11"].'" alt="promotion oimage" /></a></td></tr>';
/////////////Social Media/////////////
$product_html .=  '<tr><td align="center" colspan="3">
    <h2 align="center" style="text-align:center"><span style="mso-fareast-font-family:Times New Roman;">Get Social With Us!</span></h2>
    <p class="MsoNormal" align="center" style="text-align:center"><a href="http://cts.vresp.com/c/?CosmeParadise/70959a554d/TEST/96aab2635d"><span style="text-decoration:none;text-underline:none"><img border="0" src="https://a792ded0d2-custmedia.vresp.com/library/1373430272/36231dc730/twitter-edm.gif" alt="Follow @CosmeParadise"></span></a><a href="http://cts.vresp.com/c/?CosmeParadise/70959a554d/TEST/3d1d6c04a1"><span style="text-decoration:none;text-underline:none"><img border="0" id="_x0000_i1042" src="https://a792ded0d2-custmedia.vresp.com/library/1373430369/8a5472338c/fb-edm.gif" alt="Like Us Today! @CosmeParadise"></span></a></p>
    </td></tr>';	
$product_html .=  '<tr><td align="center" colspan="3"><img style="border:0;" src="https://a792ded0d2-custmedia.vresp.com/library/1380093483/2b362e0393/EDM-Bar-freeship.gif" alt="free shipping" /></td></tr>';	
$product_html .=  '</table>';



$product_html .=  '</body>';

echo $product_html;

sendEmail($product_html);
?>


<?php

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
		$mail->Host = 'cosmepar.nextmp.net';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'order2@cosmeparadise.com';                            // SMTP username
		$mail->Password = 'AppealVicesCrashJoyous77';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted		 
		$mail->From = 'order2@cosmeparadise.com';
		$mail->FromName = 'SBN order '.$subject_status;
		//$mail->AddAddress('union.programmer@gmail.com', 'Berry Lai');  // Add a recipient
		$mail->AddAddress('marketing@cosmeparadise.com', 'Kitty');  // Add a recipient
		$mail->AddAddress('union.b2c@gmail.com', 'Clara');  // Add a recipient
		$mail->AddAddress('union.emarketing@gmail.com', 'Kitty');  // Add a recipient
		//$mail->AddAddress('order2@cosmeparadise.com', 'cosme order2'); // Name is optional	
		//$mail->AddBCC('support@cosmeparadise.com', 'Berry Lai');		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters		
		$mail->IsHTML(true);                                  // Set email format to HTML		
		$mail->Subject = 'EDM email';
		$mail->Body    = $product_html;	
		
		if(!$mail->Send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;		
		}
		else
			echo 'Message has been sent <br>';		 
	 } 
?>