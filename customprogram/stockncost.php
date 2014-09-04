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
<h2>Stock and Cost checking programme</h2>
<b>Please keep the name: PARADISE.xls, wholesale.xls</b>
<form action="stockncost.php" method="post" enctype="multipart/form-data">
<label for="file">Upload ebay file:</label>
<input type="file" name="file[]" multiple /><br /><br />
Currency Rate AU TO HDK<input type="text" name="currency_rate" value="7" /><br /><br />
<input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>

<?php	
  exit;
  }

  for( $i=0; $i<3; $i++)
{	echo $_FILES['file']["name"][$i]." with temp path:".$_FILES['file']["tmp_name"][$i]."<br>";	
	if($_FILES['file']["name"][$i] == "PARADISE.xls")
		$Cosmeparadise = $_FILES['file']["tmp_name"][$i];
	if($_FILES['file']["name"][$i] == "wholesale.xls")
		$Wholesale = $_FILES['file']["tmp_name"][$i];
}


if(!file_exists($Cosmeparadise) && !file_exists($Wholesale)) {
	echo "One of the file not found.";
	exit;
}

$size_cosme = filesize($Cosmeparadise);
$size_whole = filesize($Wholesale);
if(!$size_cosme && !$size_whole) {
	echo "One of the file is empty.\n";
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

Mage::app();
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//// call SQL read
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL write
/////////////////////////////////////////////////////////

/** PHPExcel_IOFactory */
include './PHPExcel_1.7.9_doc/Classes/PHPExcel/IOFactory.php';

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

/* Connect to a Magento MySQL server */
$conMagento = mysqli_connect(
'localhost', /* The host to connect to */
'cosmepar_magento', /* The user to connect as */
'cM4v8yRj8b1q', /* The password to use */
'cosmepar_magento'); /* The default database to query */

if (!$conMagento) {
printf("Can't connect to Magento MySQL Server. Errorcode: %s\n", mysqli_connect_error());
exit;
}
/////////clear table first/////////////
try{
$result = mysqli_query($con, 'DELETE FROM stock_n_cost;');
}catch(Exception $e)
{	echo 'Caught exception: ', $e->getMessage(), "\n";
}
		
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
   
try {
   ///////////////////////////////Wholesale/////////////////////////////////////
   $savedValueBinder = PHPExcel_Cell::getValueBinder();	///////make CSV all cell to string
   PHPExcel_Cell::setValueBinder(new TextValueBinder()); ///////make CSV all cell to string
   $wholeReader = PHPExcel_IOFactory::createReader($XLSFileType);
   $objPHPExcel = $wholeReader->load($Wholesale);
   PHPExcel_Cell::setValueBinder($savedValueBinder); ///////make CSV all cell to string
   
   $sheet = $objPHPExcel->getSheet(0); // 讀取第一個工作表(編號從 0 開始)
   $highestRow = $sheet->getHighestRow(); // get total rows

   for ($row = 5; $row <= $highestRow; $row++) {
		$sku = $sheet->getCellByColumnAndRow(14, $row)->getValue();	//columnO 
		$cost = $sheet->getCellByColumnAndRow(9, $row)->getValue();	//columnJ 
		$inventory = $sheet->getCellByColumnAndRow(6, $row)->getValue();	//columnG
		$sql = 'INSERT INTO stock_n_cost (sku, whole_stock, whole_cost) VALUES ("'.$sku.'", '.$inventory.','.(double)$cost.')';		
		// Send a query to the server
		try{
		mysqli_query($con, $sql);
		}catch(Exception $e)
		{	echo 'Caught exception: ', $e->getMessage(), "\n";
		}
		   //echo $sku . '---' . $inventory . "<br>";
	}
	//clear memory
	unset($wholeReader);
	unset($objPHPExcel);
	///////////////////////////////Cosmeparadis/////////////////////////////////////
   $savedValueBinder = PHPExcel_Cell::getValueBinder();	///////make CSV all cell to string
   PHPExcel_Cell::setValueBinder(new TextValueBinder()); ///////make CSV all cell to string
   $cosmeReader = PHPExcel_IOFactory::createReader($XLSFileType);
   $objPHPExcel = $cosmeReader->load($Cosmeparadise);
   PHPExcel_Cell::setValueBinder($savedValueBinder); ///////make CSV all cell to string
   
   $sheet = $objPHPExcel->getSheet(0); // 讀取第一個工作表(編號從 0 開始)
   $highestRow = $sheet->getHighestRow(); // get total rows
	
   for ($row = 5; $row <= $highestRow; $row++) {
		$sku = $sheet->getCellByColumnAndRow(14, $row)->getValue();	//columnO 
		$cost = $sheet->getCellByColumnAndRow(9, $row)->getValue();	//columnJ 
		$inventory = $sheet->getCellByColumnAndRow(6, $row)->getValue();	//columnG
		//echo "sku:".$sku."<br>";
		/////////////////////check if sku exist/////////////////////
		try{
			
			$sql = "SELECT COUNT(sku) AS count_exist FROM stock_n_cost WHERE sku ='".$sku."'";			
			$result = mysqli_query($con, $sql);	
			$row_value = mysqli_fetch_array($result);
			if($row_value['count_exist'] > 0)
				$sql = 'UPDATE stock_n_cost SET cosme_stock = '.$inventory.', cosme_cost = '.(double)$cost.' WHERE sku="'.$sku.'";';		
			else
				$sql = 'INSERT INTO stock_n_cost (sku, cosme_stock, cosme_cost) VALUES ("'.$sku.'", '.$inventory.','.(double)$cost.');';		
			//echo "---count_exist:".$row_value['count_exist']."---sql:".$sql."<br>";	
			
			/// Send a query to the server
			mysqli_query($con, $sql);
		}catch(Exception $e)
		{	echo 'Caught exception: ', $e->getMessage(), "\n";
		}		
	} 
	//clear memory
	unset($cosmeReader);
	unset($objPHPExcel);
	
	
	///////////////////////////////////excel writer///////////////////////////
	// Create new PHPExcel object
			//echo date('H:i:s') . " Create new PHPExcel object\n";
			$objPHPExcel = new PHPExcel();
			
			// Set properties
			//echo date('H:i:s') . " Set properties\n";
			$objPHPExcel->getProperties()->setCreator("Berry Lai");
			$objPHPExcel->getProperties()->setLastModifiedBy("Berry Lai");
			$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Document");
			$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Document");
			$objPHPExcel->getProperties()->setDescription("");
			
			
			// Add some data
			//echo date('H:i:s') . " Add some data\n";
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'sku');
			$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'suggest stock');			
			$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'current stock');
			$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'suggest cost(HKD)');
			$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'current price');
			$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'current price(HKD)');
			$objPHPExcel->getActiveSheet()->SetCellValue('G1', 'DG');
			$objPHPExcel->getActiveSheet()->SetCellValue('H1', 'postage(HKD)');
			$objPHPExcel->getActiveSheet()->SetCellValue('I1', 'paypal fee(HKD)');
			$objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Net Cost(HKD)');
			$objPHPExcel->getActiveSheet()->SetCellValue('K1', 'GP(HKD)');
			$objPHPExcel->getActiveSheet()->SetCellValue('L1', 'GP %');
			
			
			////////Extra data from database///////////			
			try{
				$i=2;
				$perfume_array = array("4", "38", "39", "40", "42", "190", "243");
				$sql_magento = "SELECT sku FROM catalog_product_entity_int cpei LEFT JOIN catalog_product_entity cpe ON cpe.entity_id = cpei.entity_id WHERE cpei.attribute_id = 150 AND cpei.value = 1233;"; //AU					
				$result_magento = mysqli_query($conMagento, $sql_magento);	
				while($row_magento = mysqli_fetch_array($result_magento))	////////////load cosmeparadise product SKU//////////
				{	
					$website_sku = $row_magento['sku'];					
					if( substr(trim(strtoupper($website_sku)),-1) == 'P')
					{	$sku_with_P = $website_sku;
						$sku_without_P = substr($sku_with_P, 0, -1);
					}
					else
					{	$sku_with_P = $website_sku."P";
						$sku_without_P = $website_sku;
					}
					$sql = "SELECT * FROM stock_n_cost WHERE sku IN ('".$sku_with_P."', '".$sku_without_P."') ORDER BY  whole_stock, cosme_stock";			
					$result = mysqli_query($con, $sql);	
					$suggest_stock = 0;$suggest_stock_1 = 0;$suggest_stock_2 = 0;
					$suggest_cost = 0;$suggest_cost_1 = 0;$suggest_cost_2 = 0;
					$counter_ss = 0;					
					while($row = mysqli_fetch_array($result))
					{	$counter_ss++;
						if($row['cosme_stock'] > 0)
						{	$suggest_stock = $row['cosme_stock'];
						}else if($row['whole_stock'] > 0){
							$suggest_stock = $row['whole_stock'];				
						}else
							$suggest_stock = 0;
						
						if($row['cosme_stock'] > 0)
						{	$cosme_cost = $row['cosme_cost'];
						}else if($row['whole_stock'] > 0){
							$cosme_cost = $row['whole_cost'];				
						}else
							$cosme_cost = $row['whole_cost'];
						
						if($counter_ss == 1)
						{	$suggest_stock_1 = $suggest_stock;
							$suggest_cost_1 = $cosme_cost;
						}else{
							$suggest_stock_2 = $suggest_stock;
							$suggest_cost_2 = $cosme_cost;
						}
						
						$counter_ss++;
					}
						
					if($counter_ss == 0)
						continue;
					/////////////////find bigger stock///////////////
					if($suggest_stock_1 > $suggest_stock_2)
						$suggest_stock = $suggest_stock_1;
					else
						$suggest_stock = $suggest_stock_2;	
					/////////////////find bigger cost///////////////	
					if($suggest_cost_1 > $suggest_cost_2)
						$cosme_cost = $suggest_cost_1;
					else
						$cosme_cost = $suggest_cost_2;
					//////////////////////find magento price///////////////////////	
					//if(_checkIfSkuExists($website_sku, $connection_read))
					//{	
						$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$website_sku);
						$final_price = $product->getFinalPrice();
						$current_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
						$MSRP = $product->getMsrp();
						$final_price_HKD = '=E'.$i.'*'.$_POST["currency_rate"];
						$cats_array = $product->getCategoryIds();
						//$cats_string = implode(",", $cats_array);		
						//////////////////////////get postage///////////////////////
						$postage = 0;
						if ($product->getResource()->getAttribute('postage') && $product->getResource()->getAttribute('postage')->getFrontend()->getValue($product) != "") {
							$postage = $product->getResource()->getAttribute('postage')->getFrontend()->getValue($product);		
						}
						else {
							
							//////////////////////find postage/////////////////////////
							if( count(array_intersect($perfume_array, $cats_array)) > 0)
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
	
						
						///////////////Paypal Fee///////////////
						$paypal_fee_HKD = '=(E'.$i.'*0.02+0.3)*'.$_POST["currency_rate"]; //($final_price*0.02+0.3)*$_POST["currency_rate"]
						///////////////Net Cost///////////////
						$net_cost = '=(E'.$i.'*0.02+0.3)*'.$_POST["currency_rate"].'+H'.$i.'+D'.$i; //($final_price*0.02+0.3)*$_POST["currency_rate"]+$postage+$cosme_cost;
						///////////////GP///////////////
						//$GP = $final_price_HKD - $net_cost;
						///////////////GP %///////////////
						//$GP_percentage = round(($GP/$final_price_HKD),4);
				
					/*}else{
						$final_price = "sku not in website";
						$current_stock = "sku not in website";
						$MSRP = "sku not in website";
						$final_price_HKD = "sku not in website";
						$cats_string = "sku not in website";
						$DG_type = "sku not in website";
						$postage = "sku not in website";
						$paypal_fee_HKD = "sku not in website";
						$GP = "sku not in website";					
						$GP_percentage = "sku not in website";	}*/
					
				
					//$objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $row['sku']);
					$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i, $website_sku,PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $suggest_stock);				
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $current_stock);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, $cosme_cost);
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, $final_price);
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$i, $final_price_HKD);
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$i, $DG_type);
					$objPHPExcel->getActiveSheet()->SetCellValue('H'.$i, $postage);
					$objPHPExcel->getActiveSheet()->SetCellValue('I'.$i, $paypal_fee_HKD);
					$objPHPExcel->getActiveSheet()->SetCellValue('J'.$i, $net_cost);
					$objPHPExcel->getActiveSheet()->SetCellValue('K'.$i, '=F'.$i.'-J'.$i);///$GP
					$objPHPExcel->getActiveSheet()->SetCellValue('L'.$i, '=K'.$i.'/F'.$i);///GP_percentage
							
					$i++;
				
				}
			}catch(Exception $e)
			{	echo 'Caught exception: ', $e->getMessage(), "\n";
			}
			//////////////////////////////////////////
			
			// Rename sheet			
			$objPHPExcel->getActiveSheet()->setTitle('eBay Stock');
			
					
			// Save Excel 2007 file			
			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
			$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
	//////////////////////////////////////////////////////////////////////		
} catch(PHPExcel_Reader_Exception $e) {
	die('Error: '.$e->getMessage());
}

   
   
mysqli_close($conMagento);
mysqli_close($con);
   
   	/////////////////get execution time//////////////
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   echo "This page was created in ".$totaltime." seconds <br>"; 
   
   echo "<br>Download <a href='./stockncost.xlsx'>Here</a><br>";
   
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
?>   
   