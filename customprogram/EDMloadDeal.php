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
	///////!!!!!!!!EDM will send by Takkie at AU time 1 AM////////////////
$directinput = false;
if( isset($_POST["DealDate"]) && ($_POST["DealDate"]) != ""){
		$DealDate = $_POST["DealDate"];
		
}
else
{
?>

<html>
<head> 
  <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.9.1.js"></script>
  <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>  
  <script>
  $(function() {
    $(".datepicker").datepicker();
	$(".datepicker").datepicker( "option", "dateFormat", "yy-mm-dd" );
  });
  </script>
</head>
<body>
<h2>Product sku To HTML programme</h2>
<b>Select Deal Date before convert</b>
<form id="usrform" action="EDMloadDeal.php" method="post" enctype="multipart/form-data">
<input class="datepicker" type="text" name="DealDate" id="DealDate" /> (YYYY-mm-dd)
<br><br>
<input type="submit" name="submit" value="Convert To HTML" />
</form>
</body>
</html>

<?php	
  exit;
  }
/////////////open file and write into it///////////////
	$today =  date("YmdHis");
//echo $today." ".date("Y/m/d H:i:s")
	$filename = "./EDM/EDMtemplate".$today.".html";
	$html_link = "http://www.cosmeparadise.com/customprogram/EDM/EDMtemplate".$today.".html";
	
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


///////////////get Daily deal product ID///////////////
$sql = 'SELECT product_id FROM multipledeals WHERE datetime_from LIKE "'.$DealDate.'%";';
$deal_product_id = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////
///////////////get Daily deal product price///////////////
$sql = 'SELECT deal_price FROM multipledeals WHERE datetime_from LIKE "'.$DealDate.'%";';
$deal_product_price = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////
$new_DealDate = date("F j, Y", strtotime($DealDate));

$product_html = '<html>
<head><title>Cosmeparadise Promotion</title></head>';
//$skulist = array();
 $product_html .= '<body style="background: #fbf9f4;12px/1.55 Arial,Helvetica,sans-serif">';
 $product_html .=  '<table frame="box" cellspacing="0" cellpadding="0" align="center" style="background-color:white;border-width: 1px; border-color:#cccccc;width: 560px;">
<tr><td colspan="5"><p align="center"><a href="http://www.cosmeparadise.com/?utm_source=icontact&utm_medium=email&utm_campaign=EDM"><span style="text-decoration:none;text-underline:none"><img border="0" src="http://www.cosmeparadise.com/media/banner/DailyDealbot/logo.gif" alt="Cosme Paradise"></span></a></p></td></tr>
<tr><td align="center" colspan="5">       
<p><img usemap="#navigationbar" border="0" width="545px" height="25" src="http://www.cosmeparadise.com/media/banner/DailyDealbot/EDM-Bar-2.gif" alt="top menu"></p>
<map name="navigationbar">
<area target="_blank" shape="rect" coords="0, 2, 70, 25" href="http://www.cosmeparadise.com/skincare.html?utm_source=icontact&utm_medium=email&utm_campaign=EDM">
<area target="_blank" shape="rect" coords="71, 2, 155, 25" href="http://www.cosmeparadise.com/fragrance.html?utm_source=icontact&utm_medium=email&utm_campaign=EDM">
<area target="_blank" shape="rect" coords="156, 2, 224, 25" href="http://www.cosmeparadise.com/makeup.html?utm_source=icontact&utm_medium=email&utm_campaign=EDM">
<area target="_blank" shape="rect" coords="227, 2, 308, 25" href="http://www.cosmeparadise.com/bodycare.html?utm_source=icontact&utm_medium=email&utm_campaign=EDM">
<area target="_blank" shape="rect" coords="311, 2, 389, 25" href="http://www.cosmeparadise.com/haircare.html?utm_source=icontact&utm_medium=email&utm_campaign=EDM">
<area target="_blank" shape="rect" coords="393, 2, 467, 25" href="http://www.cosmeparadise.com/suncare.html?utm_source=icontact&utm_medium=email&utm_campaign=EDM">
<area target="_blank" shape="rect" coords="470, 2, 542, 25" href="http://www.cosmeparadise.com/men.html?utm_source=icontact&utm_medium=email&utm_campaign=EDM">
</map>	   
</td></tr>';
	   
///////////////////////Daily Deal control///////////////////
if($deal_product_id > 0)
{	$_product = Mage::getModel('catalog/product')->load($deal_product_id); 
	$prod_name = $_product->getName();	
	$prod_url = $_product->getProductUrl();
	////////////show save price from MSRP//////////////	
	$_price = $deal_product_price;	
	////////////////////////////////////////////////////
	$product_html .=  '<tr><td align="left" colspan="5" style="padding-left:10px;padding-top:10px;"><div style="font-weight:bold;font-size:25px;color:#ff00ae;text-align:left;font-family:arial;">Daily Deal '.$new_DealDate.'</div></td></tr>';
	$product_html .=  '<tr><td align="left" colspan="5" style="padding-left:10px;padding-top:10px;">*Deal will end before 6AM of next day</td></tr>';;
	$product_html .=  '<tr><td align="left" colspan="3">';
	$product_html .=  '<table style="padding-left:10px;">';
	$product_html .=  '<tr><td colspan="2"><a target="_blank" style="font-weight:bold;font-size:15px;text-decoration: none;" href="'.$prod_url.'?utm_source=icontact&utm_medium=email&utm_campaign=EDM">'.$prod_name.'</a></td></tr>';	
	/////////price control////////////
	if($_product->getMsrp()>$_price)
		$product_html .=  '<tr><td><div style="font-family:arial;padding-right:10px;">RRP:<span style="font-size:14px;color:#000;"><s>'.(Mage::helper('core')->currency($_product->getMsrp(),true,false)).'</s></span></div></td>';	
	else
		$product_html .=  '<tr>';
	if($_product->getPrice() < $_product->getMsrp())
		$product_html .=  '<td><div style="font-family:arial;">Original Price: <span style="font-size:14px;color:#000;"><s>'.(Mage::helper('core')->currency($_product->getPrice(),true,false)).'</s></span></div></td></tr>';
	else
		$product_html .=  '</tr>';
	$product_html .= '<tr><td colspan="2" align="left"><span style="color:#F660AB;font-weight:bold;font-size:18px;">SALE PRICE: A$'.number_format($_price,2,'.','').'</span></td><tr>';
	$product_html .= '<tr><td colspan="2" align="left"><div style="font-family:arial;"><span style="font-size:14px;font-weight:bold;color:#FF0000;">~'.(round(($_product->getMsrp()-$_price)/$_product->getMsrp(),2)*100).'%OFF</span></div></td></tr>';	
	//////////////////////////////////
	///////////////add to cart button///////////////
	$product_html .=  '<tr><td colspan="2" align="left" style="padding-right:10px;"><b><a href="'.$_product->getProductUrl().'?utm_source=icontact&utm_medium=email&utm_campaign=EDM" target="_blank">Buy Now</a></b></td></tr>';
	
	$product_html .=  '</table>';	
	$product_html .=  '<td colspan="2" valign="middle"><table><tr><td><div style="padding-left:10px;height:150;"><a target="_blank" style="word-wrap:break-word;" href="'.$_product->getProductUrl().'?utm_source=icontact&utm_medium=email&utm_campaign=EDM" ><img style="border:0;" src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(150).'" alt="Product Detail" /></a></td></tr></table></td>';		
	$product_html .=  '</tr>';	
	$product_html .=  '<tr><td colspan="5"><hr width="90%" /></td></tr>';
	$product_html .=  '<tr><td colspan="5" style="padding-left:25px;">If you cannot view this email, please click <a target="blank" href="'.$html_link.'?utm_source=icontact&utm_medium=email&utm_campaign=EDM">here</a></td></tr>';
	$product_html .=  '<tr><td colspan="5"><hr width="90%" /></td></tr>';
}else{
	echo "no deal can found<br><a href='".$_SERVER['REQUEST_URI']."'>back</a>";
	exit;
}

///////////////////////////////////////////////////////////////
//<b><a target="_blank" href="'.$_POST["FULLLIST_LINK"].'">'.$_POST["FULLLIST_TEXT"].'</a></b>
 $product_html .= '<tr>
 <td width="200" style="padding:8px;">&nbsp;</td>
 <td width="60" ><b>R.R.P.</b></td>
 <td width="50" align="center"><b>SAVE</b></td>
 <td width="80" align="right" style="padding-right:10px;"><b>Our Price</b></td>
 <td width="70" >&nbsp;</td>
 </tr>';
 $product_html .= '<tr>';

	$sql = "SELECT cpei.entity_id FROM catalog_product_entity_int cpei
	INNER JOIN cataloginventory_stock_item csi ON csi.product_id = cpei.entity_id AND csi.qty > 0
	INNER JOIN catalog_product_entity_int item_status ON item_status.entity_id = cpei.entity_id AND item_status.value = 1 AND item_status.store_id = 0 AND item_status.attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'status' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product'))
	WHERE cpei.attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'current_product_type' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')) 
	AND cpei.value = (SELECT option_id FROM eav_attribute_option_value WHERE value = 'Cosmeparadise' AND store_id = 0)
	ORDER BY RAND()
	LIMIT 5";

 //$skulist = array("0033BY","0033BY","0033BY","0033BY","0033BY","0033BY","0033BY","0033BY");
 //foreach($skulist as $sku)
 //$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku); 
 foreach ($connection_read->fetchAll($sql) as $PID_for_UNION)
{
	$_product = Mage::getModel('catalog/product')->load($PID_for_UNION); 
	if($_product == null)
		continue;
		
	$prod_name = $_product->getName();	
	$prod_id = $_product->getId();
	
	//$product_html .= '<td width="170px" height="380px" align="center">';	
	//////////////product image///////////////
	//$product_html .=  '<div style="padding-left:10px;height:330px;"><a target="_blank" style="word-wrap:break-word;" href="'.$_product->getProductUrl().'" ><img width="135px" style="border:0;" src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135).'" alt="Product Detail" /></a>';
	
	
	////////////show save price from MSRP////////////
	$_specialprice = $_product->getFinalPrice();
	
	$_price = $_specialprice >0?$_specialprice:$_product->getPrice();	
	/////////////////////////fist cell, Manufacturer and Name///////////////////	
	$product_html .= '<td align="left" style="padding:8px;">
	<b>'.$_product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($_product).'</b><br />
	<div style="font-weight:bold;font-size:12px;color:#000;text-align:left;text-transform:uppercase;font-family:arial;"><a target="_blank" style="color:#000;font-weight:bold;text-decoration: none;" href="'.$_product->getProductUrl().'?utm_source=icontact&utm_medium=email&utm_campaign=EDM" title="'.$_product->getName().'">'.$_product->getName().'</a></div></td>';
	
	if($_product->getMsrp()>$_price){
		$product_html .=  '<td><div style="font-family:arial;"><span style="font-size:14px;color:#000;"><s>'.(Mage::helper('core')->currency($_product->getMsrp(),true,false)).'</s></span></div></td>';		
	}
	else
		$product_html .=  '<td>&nbsp;</td>';
	
	$product_html .= '<td align="center"><div style="font-family:arial;"><span style="font-size:14px;font-weight:bold;color:#FF0000;">SAVE<br />'.(round(($_product->getMsrp()-$_price)/$_product->getMsrp(),2)*100).'%</span></div></td>';
	$product_html .= '<td align="right" style="padding-right:10px;"><span style="color:#F660AB;font-weight:bold;font-size:18px;">A$'.number_format($_price,2,'.','').'</span></td>';
	///////////////add to cart button///////////////
	$product_html .=  '<td align="right" style="padding-right:10px;"><b><a href="'.$_product->getProductUrl().'?utm_source=icontact&utm_medium=email&utm_campaign=EDM" target="_blank">Buy Now</a></b></td>';
	
	 $product_html .=  '</tr><tr>';
	 $counting++;
}
$product_html .=  '</tr><tr><td><p>&nbsp;</p></td></tr>';	///close products table
$product_html .=  '<tr><td align="center" colspan="5"><img style="border:0;" src="http://www.cosmeparadise.com/media/banner/DailyDealbot/promo-bar.gif" alt="promotion bar" /></td></tr>';
/////////////////////////////////Here for Promotion Banner/////////////////////////	
$product_html .=  '<tr><td align="center" colspan="5"><a target="_blank" href="http://www.cosmeparadise.com/sisley-gel-express-aux-fleurs-express-flower-gel-60ml.html?utm_source=icontact&utm_medium=email&utm_campaign=EDM"><img style="display:block;border:0;" src="http://www.cosmeparadise.com/media/banner/cosmetic_banner_550-x-100_AU.gif" alt="promotion" /></a></td></tr>';
//$product_html .=  '<tr><td align="center" colspan="5"><a target="_blank" href="http://www.cosmeparadise.com/selected-products.html?utm_source=icontact&utm_medium=email&utm_campaign=EDM"><img style="display:block;border:0;" src="http://www.cosmeparadise.com/media/banner/SP550x110.gif" alt="promotion" /></a></td></tr>';
$product_html .=  '<tr><td align="center" colspan="5"><a target="_blank" href="http://www.cosmeparadise.com/free-max-factor-eyeshadow-on-all-your-purchases?utm_source=icontact&utm_medium=email&utm_campaign=EDM"><img style="display:block;border:0;" src="http://www.cosmeparadise.com/media/banner/DailyDealbot/AnnaSuiRockMeVial550x100.gif" alt="promotion" /></a></td></tr>';
$product_html .=  '<tr><td align="center" colspan="5"><a target="_blank" href="http://www.cosmeparadise.com/free-max-factor-eyeshadow-on-all-your-purchases?utm_source=icontact&utm_medium=email&utm_campaign=EDM"><img style="display:block;border:0;" src="http://www.cosmeparadise.com/media/banner/DailyDealbot/newcustomer-edm.gif" alt="promotion" /></a></td></tr>';
/////////////Social Media/////////////
$product_html .=  '<tr><td align="center" colspan="5">
    <h2 align="center" style="text-align:center"><span style="mso-fareast-font-family:Times New Roman;">Get Social With Us!</span></h2>
    <p class="MsoNormal" align="center" style="text-align:center"><a href="https://twitter.com/CosmeParadise"><span style="text-decoration:none;text-underline:none"><img border="0" src="http://www.cosmeparadise.com/media/banner/DailyDealbot/twitter-edm.gif" alt="Follow @CosmeParadise"></span></a><a href="https://www.facebook.com/c0smeparadise"><span style="text-decoration:none;text-underline:none"><img border="0" id="_x0000_i1042" src="http://www.cosmeparadise.com/media/banner/DailyDealbot/fb-edm.gif" alt="Like Us Today! @CosmeParadise"></span></a></p>
    </td></tr>';	
$product_html .=  '<tr><td align="center" colspan="5"><img style="border:0;" src="http://www.cosmeparadise.com/media/banner/DailyDealbot/EDM-Bar-freeship.gif" alt="free shipping" /></td></tr>';	
$product_html .=  '</table>';
$product_html .=  '</body></html>';

//echo $product_html;
//sendEmail($product_html);


	$ourFileHandle = fopen($filename, 'w') or die("can't open file");
	fputs($ourFileHandle, $product_html);
	//close file after write the content into it
	fclose($ourFileHandle);
	
	echo '<br><br>Generate success. check open page <a target="_blank" href="'.$filename.'">Here</a><br><a href="'.$_SERVER['REQUEST_URI'].'">back</a>';
	
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
		//$mail->AddBCC('support@cosmeparadise.com', 'Berry Lai');		
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