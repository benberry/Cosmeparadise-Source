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
if ($ip == "27.121.64.109" ) $accesslist=true;	//analystsupporter.com server
if ($ip == "192.240.170.73" ) $accesslist=true;	//us.cosmeparadise.com server
if ($ip == "178.17.36.69" ) $accesslist=true;	//www.cosmeparadise.co.uk server
if ($ip == "61.93.89.10" ) $accesslist=true;	//company IP

if ($accesslist==false) 
	{
	echo $ip;
	exit;
	}
	
if(isset($_FILES["file"])) { 
	if (($_FILES["file"]["type"] == "text/csv")
	|| ($_FILES["file"]["type"] == "application/vnd.ms-excel")
	|| ($_FILES["file"]["type"] == "application/vnd.msexcel")
	|| ($_FILES["file"]["type"] == "application/excel")
	|| ($_FILES["file"]["type"] == "application/x-excel")
	|| ($_FILES["file"]["type"] == "application/x-msexcel")
	|| ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document")
	|| ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
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
<h2>Product To eBay</h2>
<form action="ProductToEbay.php" method="post" enctype="multipart/form-data">
<label for="file">Upload ebay file:</label>
<input type="file" name="file" id="file" /> <br /><br />
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

/* Connect to a custom MySQL server */
	$con = mysqli_connect(
	'localhost', /* The host to connect to */
	'cosmepar_program', /* The user to connect as */
	'RecapsBoronWhirlGrands45', /* The password to use */
	'cosmepar_custom'); /* The default database to query */
	
	if (!$con) {
	printf("Can't connect to custom MySQL Server. Errorcode: %s\n", mysqli_connect_error());
	exit;
	}
	
	
/////////////////include Magento file/////////////////////
$path_include = "../app/Mage.php";
// Include configuration file
if(!file_exists($path_include)) {
	exit('<HTML><HEAD><TITLE>404 Not Found</TITLE></HEAD><BODY><H1>Not Found</H1>Please ensure that this file is in the root directory, or make sure the path to the directory where the configure.php file is located is defined corectly above in $path_include variable</BODY></HTML>');
}
else {
	require_once $path_include;
}

$CAT = getCategories();

Mage::app();
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//// call SQL read
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL write
/////////////////////////////////////////////////////////

/** PHPExcel_IOFactory */
include './PHPExcel_1.7.9_doc/Classes/PHPExcel/IOFactory.php';

		
$line="";

class TextValueBinder implements PHPExcel_Cell_IValueBinder
{
    public function bindValue(PHPExcel_Cell $cell, $value = null) {
        $cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_STRING);
        return true;
    }
}

////////////////////timer//////////////
//$execution_time = microtime(); # Start counting
 $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime; 
   
   $CSVFileType = 'CSV';
   $XLSFileType = 'Excel5';
   
   $count_row = 0;
   $perfume_array = array("4", "38", "39", "40", "42", "190", "243");
   $gift_array = array("0192AS","0193AS","0091IN","0085IN","947573","963245","0089IN","0090IN","616713P","PLB01","MT-BOX
");
try {
	$sql = 'TRUNCATE TABLE item_able_to_ebay;';
	mysqli_query($con, $sql);
   ///////////////////////////////Load Invoice System File/////////////////////////////////////
   $savedValueBinder = PHPExcel_Cell::getValueBinder();	///////make CSV all cell to string
   PHPExcel_Cell::setValueBinder(new TextValueBinder()); ///////make CSV all cell to string
   $InvoiceReader = PHPExcel_IOFactory::createReader($XLSFileType);
   $objPHPExcel = $InvoiceReader->load($csvfile);   
   PHPExcel_Cell::setValueBinder($savedValueBinder); ///////make CSV all cell to string
   
   $sheet = $objPHPExcel->getSheet(0); // 讀取第一個工作表(編號從 0 開始)
   $highestRow = $sheet->getHighestRow(); // get total rows

	///////////////////////////////////excel writer///////////////////////////
		// Create new PHPExcel object
			//echo date('H:i:s') . " Create new PHPExcel object\n";			
			$New_objPHPExcel = new PHPExcel();
			// Set properties
			//echo date('H:i:s') . " Set properties\n";
			$New_objPHPExcel->getProperties()->setCreator("Berry Lai");
			$New_objPHPExcel->getProperties()->setLastModifiedBy("Berry Lai");
			$New_objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Document");
			$New_objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Document");
			$New_objPHPExcel->getProperties()->setDescription("");
			
			
			// Add some data
			//echo date('H:i:s') . " Add some data\n";
			$New_objPHPExcel->setActiveSheetIndex(0);
			$New_objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Brand');
			$New_objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Name');			
			$New_objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Web Category');
			$New_objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Barcode');
			$New_objPHPExcel->getActiveSheet()->SetCellValue('E1', 'SKU');
			$New_objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Qty');
			$New_objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Cost(HKD)');
			$New_objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Estimate postage(HKD)');
			$New_objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Web price(AUD)');
			$New_objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Image URL');
			
   for ($row = 5; $row <= $highestRow; $row++) {
		$brand = $sheet->getCellByColumnAndRow(0, $row)->getValue();	//columnA Brand
		$sku = $sheet->getCellByColumnAndRow(14, $row)->getValue();	//columnO SKU
		$barcode = $sheet->getCellByColumnAndRow(15, $row)->getValue();	//columnP Barcode
		$cost = $sheet->getCellByColumnAndRow(9, $row)->getValue();	//columnJ  COST
		$inventory = $sheet->getCellByColumnAndRow(8, $row)->getValue();	//columnI Available
		
		///////////////check if free gift///////////////
		//if( count(array_intersect($gift_array, $sku)) > 0)
		if (in_array($sku, $gift_array))
			continue;
		//////////////check if stock available/////////////
		if($inventory <= 0)
			continue;
		
		//////////////check if eBay prohibited brand/////////////
		if($brand == "GIORGIO ARMANI" || $brand == "BIOTHERM" || $brand == "CACHAREL" || $brand == "DIESEL" || $brand == "GUY LAROCHE" || $brand == "VIKTOR & ROLF" || $brand == "LANCOME" || $brand == "HELENA RUBINSTEIN" || $brand == "KIEHL'S" || $brand == "LA ROCHE POSAY" || $brand == "RALPH LAUREN" || $brand == "SHU UEMURA" || $brand == "VIKTOR & ROLF")
			continue;
		
		if( substr(trim(strtoupper($sku)),-1) == 'P')
		{	$sku_with_P = $sku;
			$sku_without_P = substr($sku_with_P, 0, -1);
		}
		else
		{	$sku_with_P = $sku."P";
			$sku_without_P = $sku;
		}
		
		if(_checkIfSkuExists($sku_without_P, $connection_read))
			$sku = $sku_without_P;
		else if(_checkIfSkuExists($sku_with_P, $connection_read))
			$sku = $sku_with_P;
		else
		{	//echo "sku:".$sku." not exist <Br>";
			continue;
		}
		
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
		////////////////////////////////////////get manufacture///////////////////////////////////
		if ($product->getResource()->getAttribute('manufacturer')) {
			$manufacturer = $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
			if ($manufacturer == "No") {
				$manufacturer = "";
			}
		}
		else {
			$manufacturer = "";
		}
				
		$prod_name = $product->getName();
		$prod_image = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
		$final_price = $product->getFinalPrice();
		////////////////////////////////////////get category_name///////////////////////////////////
		$cat_ids = $product->getCategoryIds();
		$cat_level = 0;
		$cat_final_id = 0;
		$category_name = "";
		
		foreach ($cat_ids as $cat_id) {
			if ($CAT[$cat_id]['level'] > $cat_level) {
				$cat_level = $CAT[$cat_id]['level'];
				$cat_final_id = $cat_id;
			}
		}
		if ($cat_final_id > 0) {
			$category_name = $CAT[$cat_final_id]['name'];
		}
		else {
			$category_name = "Home";
		}	
		//$category_name = ereg_replace("Root Catalog", "Home", $category_name);
		$category_name = ereg_replace("Root Catalog", "", $category_name);
		
		//////////////////////////get postage///////////////////////
		$postage = 0;
		if ($product->getResource()->getAttribute('postage') && $product->getResource()->getAttribute('postage')->getFrontend()->getValue($product) != "") {
			$postage = $product->getResource()->getAttribute('postage')->getFrontend()->getValue($product);		
		}
		else {
			
			//////////////////////find postage/////////////////////////
			if( count(array_intersect($perfume_array, $cat_ids)) > 0)
			{	$DG_type = "Yes";
				if($cosme_cost<50)
					$postage = 70;
				else if( $cosme_cost>=50 && $cosme_cost<200)
					$postage = 100;
				else if( $cosme_cost>=200 && $cosme_cost<300)
					$postage = 105;
				else if( $cosme_cost>=300 && $cosme_cost<400)
					$postage = 110;
				else if( $cosme_cost>=400 && $cosme_cost<500)
					$postage = 105;
				else if( $cosme_cost>=500 && $cosme_cost<600)
					$postage = 120;
				else
					$postage = 125;
			}
			else
			{	$DG_type = "No";
				if($cosme_cost<10)
					$postage = 30;
				else if($cosme_cost>=10 && $cosme_cost<50)
					$postage = 58;
				else if( $cosme_cost>=50 && $cosme_cost<200)
					$postage = 88;
				else if( $cosme_cost>=200 && $cosme_cost<300)
					$postage = 93;
				else if( $cosme_cost>=300 && $cosme_cost<400)
					$postage = 98;
				else if( $cosme_cost>=400 && $cosme_cost<500)
					$postage = 103;
				else if( $cosme_cost>=500 && $cosme_cost<600)
					$postage = 108;
				else
					$postage = 113;
			}
		}
		
		$count_row++;
		//echo "Record:".$count_row."--manufacturer:".$manufacturer."--sku:".$sku."--cost:".$cost."--inventory:".$inventory."--prod_name:".$prod_name."--category_name:".$category_name."<br>";
		$i = $count_row+1;
		////Brand	Name	Category Barcode	SKU	Qty Cost	Estimate postage(HKD)
			$New_objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $manufacturer);
			$New_objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $prod_name);				
			$New_objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $category_name);
			$New_objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$i, $barcode,PHPExcel_Cell_DataType::TYPE_STRING);
			$New_objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$i, $sku,PHPExcel_Cell_DataType::TYPE_STRING);
			$New_objPHPExcel->getActiveSheet()->SetCellValue('F'.$i, $inventory);
			$New_objPHPExcel->getActiveSheet()->SetCellValue('G'.$i, $cost);
			$New_objPHPExcel->getActiveSheet()->SetCellValue('H'.$i, $postage);
			$New_objPHPExcel->getActiveSheet()->SetCellValue('I'.$i, $final_price);
			$New_objPHPExcel->getActiveSheet()->SetCellValue('J'.$i, $prod_image);
			
		$sql = "INSERT INTO item_able_to_ebay VALUES (".$count_row.", '".$prod_name."', '".$prod_name."', '".$sku."', '".$category_name."', '".$manufacturer."', ".$inventory.", ".$cost.", ".$postage.", '".$prod_image."');";
		mysqli_query($con, $sql);
	}
	
	// Rename sheet			
	$New_objPHPExcel->getActiveSheet()->setTitle('To eBay Product');
	// Save Excel 2007 file			
	$objWriter = new PHPExcel_Writer_Excel2007($New_objPHPExcel);
	$objWriter->save('ProductToEbay.xlsx');
			
	//clear memory
	unset($InvoiceReader);
	unset($objPHPExcel);

	//////////////////////////////////////////////////////////////////////		
} catch(PHPExcel_Reader_Exception $e) {
	die('Error: '.$e->getMessage());
}

	mysqli_close($con);  
   	/////////////////get execution time//////////////
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   //echo "This page was created in ".$totaltime." seconds <br>"; 
   
   //echo "<br>Download <a href='./ProductToEbay.xlsx'>Here</a><br>Page Redirect in 3 seconds";
   // This is for the buffer achieve the minimum size in order to flush data
	//echo str_repeat(' ',1024*64);
	//flush();
   //sleep(3);
   header("Location: http://www.cosmeparadise.com/customprogram/cosmeticebay/ItemAbleToEbay.php"); /* Redirect browser */
   
///////////////function//////////////////////
function _checkIfSkuExists($sku, $connection_read){   
    $sql   = "SELECT COUNT(*) AS count_no FROM catalog_product_entity WHERE sku = ?";
    $count = $connection_read->fetchOne($sql, array($sku));
    if($count > 0){
        return true;
    }else{
        return false;
    }
}   

// Get all categories whith breadcrumbs
function getCategories(){
	$storeId = Mage::app()->getStore()->getId(); 

	$collection = Mage::getModel('catalog/category')->getCollection()
		->setStoreId($storeId)
		->addAttributeToSelect("name");
	$catIds = $collection->getAllIds();

	$cat = Mage::getModel('catalog/category');

	$max_level = 0;

	foreach ($catIds as $catId) {
		$cat_single = $cat->load($catId);
		$level = $cat_single->getLevel();
		if ($level > $max_level) {
			$max_level = $level;
		}

		$CAT_TMP[$level][$catId]['name'] = $cat_single->getName();
		$CAT_TMP[$level][$catId]['childrens'] = $cat_single->getChildren();
	}

	$CAT = array();
	
	for ($k = 0; $k <= $max_level; $k++) {
		if (is_array($CAT_TMP[$k])) {
			foreach ($CAT_TMP[$k] as $i=>$v) {
				if (isset($CAT[$i]['name']) && ($CAT[$i]['name'] != "")) {					
					/////Berry add/////
					if($k == 2)
						$CAT[$i]['name'] .= $v['name'];
					else
						$CAT[$i]['name'] .= "";
						
					//$CAT[$i]['name'] .= " > " . $v['name'];
					$CAT[$i]['level'] = $k;
				}
				else {
					$CAT[$i]['name'] = $v['name'];
					$CAT[$i]['level'] = $k;
				}

				if (($v['name'] != "") && ($v['childrens'] != "")) {
					if (strpos($v['childrens'], ",")) {
						$children_ids = explode(",", $v['childrens']);
						foreach ($children_ids as $children) {
							if (isset($CAT[$children]['name']) && ($CAT[$children]['name'] != "")) {
								$CAT[$children]['name'] = $CAT[$i]['name'];
							}
							else {
								$CAT[$children]['name'] = $CAT[$i]['name'];
							}
						}
					}
					else {
						if (isset($CAT[$v['childrens']]['name']) && ($CAT[$v['childrens']]['name'] != "")) {
							$CAT[$v['childrens']]['name'] = $CAT[$i]['name'];
						}
						else {
							$CAT[$v['childrens']]['name'] = $CAT[$i]['name'];
						}
					}
				}
			}
		}
	}
	unset($collection);
	unset($CAT_TMP);
	return $CAT;
}

?>   
   