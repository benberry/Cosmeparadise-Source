<?php
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
<input type="file" name="file" id="file" /> <br>
Banner URL<input type="text" name="banner" id="banner" size="100"/> 
<br /><br />
<input type="submit" name="submit" value="Submit" />
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
<body style="background: #fbf9f4;12px/1.55 Arial,Helvetica,sans-serif">
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

/////////////////////main-container col1-layout div////////////////////
 $product_html .=  '<div style="position: relative;">';
/////////////////////main div////////////////////
 $product_html .=  '<div style="z-index: 1;background:url(http://www.cosmeparadise.com/skin/frontend/galaeverbuy/default/images/bkg-1col-top.png) center top no-repeat;padding-top:10px;width:960px;margin:10px auto 0;min-height:303px;text-align:left">';
/////////////////////col-main div////////////////////
 $product_html .=  '<div style="float:none;width:auto;padding:0
15px 20px;background:url(http://www.cosmeparadise.com/skin/frontend/galaeverbuy/default/images/bkg-1col-bottom.png) center bottom no-repeat;margin-bottom:10px">';
///////////////////////Show Image///////////////////
 $product_html .=  '<p><img style="display: block; margin-left: auto; margin-right: auto;" src="'.$_POST["banner"].'" alt="Fragrance Special"></p><p></p>';
///////////////////////class="category-products"///////////////////
 $product_html .=  '<div>';
 $product_html .=  '<ul style="position:relative;padding:15px 0;/*border-bottom:1px solid #ecebeb;*/clear:both;list-style: none;">';
 
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
	
	///echo $prod_id."--".$prod_name."--".$MSRP."--".$price."<br>";
	if( $counting % 5 == 0)
		$product_html .= '<li style="float:left;width:170px;position: relative">';
	else
		$product_html .= '<li style="border-right:1px solid #ecebeb;float:left;margin-right:14px;width:170px;position: relative">';
	$product_html .=  '<a style="margin-bottom:10px;word-wrap:break-word;" href="'.$_product->getProductUrl().'" ><img src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$_product->getImage().'" width="135" height="135"  /></a>';
	
	
	////////////show save price from MSRP////////////
	$_specialprice = $_product->getFinalPrice();
	
	$_price = $_specialprice >0?$_specialprice:$_product->getPrice();	
	if($_product->getMsrp()> $_price){
	$product_html .= '<div style="text-align:center;clear:both;height:53px;width:53px;color:#ff6b01;background:url(\'http://www.cosmeparadise.com/media/PINK53x53.gif\') no-repeat;float:right;margin-top:-70px;padding-top:5px;margin-left: 80px;position: absolute;z-index:5"><span style="font-size:13px;color:#fff;font-weight:normal;">SAVE</span><br><span style="font-size:15px;color:#fff;font-weight:bold;">'.(round(($_product->getMsrp()-$_price)/$_product->getMsrp(),2)*100).'%</span></div>';
	 }
		
	$product_html .= '<h2 style="margin:0;font-weight:bold;font-size:11px;color:#000;text-align:left;text-transform:uppercase"><a style="color:#000;font-weight:bold;text-decoration: none;" href="'.$_product->getProductUrl().'" title="'.$_product->getName().'">'.$_product->getName().'</a></h2>';
	
	if($_product->getMsrp()>$_price){
		$product_html .=  '<div style="margin-top:7px"><span style="font-size:14px;color:#000;">RRP:</span><span style="font-size:14px;color:#000;"><s>'.(Mage::helper('core')->currency($_product->getMsrp(),true,false)).'</s></span></div>';		
	}
	
    if($_specialprice >0 && $_product->getPrice() > $_specialprice)
		$product_html .= '<div style="margin:5px 0 10px;text-align:left"><p><span style="color:#F660AB;font-weight:bold;font-size:24px;text-decoration: line-through;">A$'.number_format($_product->getPrice(),2,'.','').'</span></p><p><span style="font-size:24px;font-weight:bold;color:#FF0000!important">A$'.number_format($_price,2,'.','').'</span></p></div>';
	else
		$product_html .= '<div style="margin:5px 0 10px;text-align:left"><span style="color:#F660AB;font-weight:bold;font-size:24px">A$'.number_format($_price,2,'.','').'</span></span></div>';
	 
	$product_html .=  '</li>';
	
	if( $counting % 5 == 0 && $counting+5 != $skucount)
		$product_html .=  '</ul><ul style="position:relative;padding:15px 0;clear:both;list-style: none;"><li><hr></li></ul><ul style="position:relative;padding:15px 0;clear:both;list-style: none;">';
	else if($counting % 5 == 0 && $counting+5 == $skucount)
		$product_html .=  '</ul><ul style="position:relative;padding:15px 0;clear:both;list-style: none;">';
	/*else if( $counting % 5 == 0 && $counting < $skucount)
		$product_html .=  '</ul><ul style="position: relative;padding:15px 0;clear:both;list-style: none;"><li><hr></li></ul><ul style="position:relative;padding:15px 0;border-bottom:1px solid #ecebeb;clear:both;list-style: none;">';*/
	 
	 $counting++;
}
$product_html .=  '</ul>';
$product_html .=  '</div>';	///category-products
$product_html .=  '<p>&nbsp</p><p>&nbsp</p><p>&nbsp</p><p>&nbsp</p><p>&nbsp</p><p>&nbsp</p><p>&nbsp</p>';
$product_html .=  '</div>';	///col-main 
$product_html .=  '</div>';	///main 
$product_html .=  '</div>';	///main-container col1-layout 

echo $product_html;
?>
</body>

<?php

}
else 
echo"can't open file <br>";
fclose($handle);

?>