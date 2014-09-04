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
<h2>eBay Stock Level checking programme</h2>
<b>Please keep the name: ebay.csv, PARADISE.xls, wholesale.xls</b>
<form action="eBayStockCheck.php" method="post" enctype="multipart/form-data">
<label for="file">Upload ebay file:</label>
<input type="file" name="file[]" multiple /><br /><br />
<input type="hidden" name="cool" value="cool" /><br /><br />
<input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>

<?php	
  exit;
  }

  for( $i=0; $i<3; $i++)
{	echo $_FILES['file']["name"][$i]." with temp path:".$_FILES['file']["tmp_name"][$i]."<br>";
	if($_FILES['file']["name"][$i] == "ebay.csv")
		$csv_ebay = $_FILES['file']["tmp_name"][$i];
	if($_FILES['file']["name"][$i] == "PARADISE.xls")
		$Cosmeparadise = $_FILES['file']["tmp_name"][$i];
	if($_FILES['file']["name"][$i] == "wholesale.xls")
		$Wholesale = $_FILES['file']["tmp_name"][$i];
}


if(!file_exists($csv_ebay) && !file_exists($Cosmeparadise) && !file_exists($Wholesale)) {
	echo "One of the file not found.";
	exit;
}

$size_ebay = filesize($csv_ebay);
$size_cosme = filesize($Cosmeparadise);
$size_whole = filesize($Wholesale);
if(!$size && !$size_cosme && !$size_whole) {
	echo "One of the file is empty.\n";
	exit;
}

/** PHPExcel_IOFactory */
include './PHPExcel_1.7.9_doc/Classes/PHPExcel/IOFactory.php';

/* Connect to a MySQL server */
$con = mysqli_connect(
'localhost', /* The host to connect to */
'cosmepar_program', /* The user to connect as */
'RecapsBoronWhirlGrands45', /* The password to use */
'cosmepar_custom'); /* The default database to query */

if (!$con) {
printf("Can't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
exit;
}
/////////clear table first/////////////
try{
$result = mysqli_query($con, 'DELETE FROM ebay_stock;');
}catch(Exception $e)
{	echo 'Caught exception: ', $e->getMessage(), "\n";
}
		
$line="";
////////////////////timer//////////////

class TextValueBinder implements PHPExcel_Cell_IValueBinder
{
    public function bindValue(PHPExcel_Cell $cell, $value = null) {
        $cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_STRING);
        return true;
    }
}
			
			
//$execution_time = microtime(); # Start counting
 $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime; 
   
   $CSVFileType = 'CSV';
   $XLSFileType = 'Excel5';
   
try {
   ///////////////////////////////eBay/////////////////////////////////////
   $savedValueBinder = PHPExcel_Cell::getValueBinder();	///////make CSV all cell to string
   PHPExcel_Cell::setValueBinder(new TextValueBinder()); ///////make CSV all cell to string
   $ebayReader = PHPExcel_IOFactory::createReader($CSVFileType);
   $objPHPExcel = $ebayReader->load($csv_ebay);   
   PHPExcel_Cell::setValueBinder($savedValueBinder); ///////make CSV all cell to string
   
   $sheet = $objPHPExcel->getSheet(0); // 讀取第一個工作表(編號從 0 開始)
   ///$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
   $highestRow = $sheet->getHighestRow(); // get total rows
   
   for ($row = 2; $row <= $highestRow; $row++) {   
		$site_listed = trim($sheet->getCellByColumnAndRow(17, $row)->getValue());
		$Item_ID = trim($sheet->getCellByColumnAndRow(0, $row)->getValue());		
		$Custom_Label = trim($sheet->getCellByColumnAndRow(1, $row)->getValue());	
		$Quantity_Available = trim($sheet->getCellByColumnAndRow(5, $row)->getValue());
		$Item_Title = trim($sheet->getCellByColumnAndRow(13, $row)->getValue());
		$sql = 'INSERT INTO ebay_stock (site_id, item_id, sku, ebay_inv, name) VALUES ('.$site_listed.', '.$Item_ID.', "'.$Custom_Label.'", '.$Quantity_Available.', "'.$Item_Title.'")';
		// Send a query to the server
		try{
		$result = mysqli_query($con, $sql);
		}catch(Exception $e)
		{	echo 'Caught exception: ', $e->getMessage(), "\n";
		}
		//echo $site_listed."--".$Item_ID."--".$Custom_Label."--".$Quantity_Available."--".$Item_Title."<br>";
	} 
	//clear memory
	unset($ebayReader);
	unset($objPHPExcel);
	///////////////////////////////Cosmeparadis/////////////////////////////////////
   $cosmeReader = PHPExcel_IOFactory::createReader($XLSFileType);
   $objPHPExcel = $cosmeReader->load($Cosmeparadise);
   $sheet = $objPHPExcel->getSheet(0); // 讀取第一個工作表(編號從 0 開始)
   $highestRow = $sheet->getHighestRow(); // get total rows

   for ($row = 4; $row <= $highestRow; $row++) {
		$sku = $sheet->getCellByColumnAndRow(14, $row)->getValue();	//columnO 
		$inventory = $sheet->getCellByColumnAndRow(6, $row)->getValue();	//columnG
		if( substr(trim(strtoupper($sku)),-1) == 'P')
		{	$sql = 'UPDATE ebay_stock SET cosme_p = '.$inventory.' WHERE sku="'.$sku.'"';
			$AD_P_sku = 'UPDATE ebay_stock SET cosme_p = '.$inventory.' WHERE sku="'.substr($sku, 0, strlen($sku)-1).'"';
		}
		else
		{	$sql = 'UPDATE ebay_stock SET cosme_out_p = '.$inventory.' WHERE sku="'.$sku.'"';
			$AD_P_sku = 'UPDATE ebay_stock SET cosme_out_p = '.$inventory.' WHERE sku="'.$sku.'P"';
		}
		// Send a query to the server
		try{
		mysqli_query($con, $sql);
		mysqli_query($con, $AD_P_sku);
		}catch(Exception $e)
		{	echo 'Caught exception: ', $e->getMessage(), "\n";
		}
		   //echo $sku . '---' . $inventory . "<br>";
	} 
	//clear memory
	unset($cosmeReader);
	unset($objPHPExcel);
	///////////////////////////////Wholesale/////////////////////////////////////
   $wholeReader = PHPExcel_IOFactory::createReader($XLSFileType);
   $objPHPExcel = $wholeReader->load($Wholesale);
   $sheet = $objPHPExcel->getSheet(0); // 讀取第一個工作表(編號從 0 開始)
   $highestRow = $sheet->getHighestRow(); // get total rows

   for ($row = 4; $row <= $highestRow; $row++) {
		$sku = $sheet->getCellByColumnAndRow(14, $row)->getValue();	//columnO 
		$inventory = $sheet->getCellByColumnAndRow(6, $row)->getValue();	//columnG
		if( substr(trim(strtoupper($sku)),-1) == 'P')
		{	$sql = 'UPDATE ebay_stock SET whole_p = '.$inventory.' WHERE sku="'.$sku.'"';
			$AD_P_sku = 'UPDATE ebay_stock SET whole_p = '.$inventory.' WHERE sku="'.substr($sku, 0, strlen($sku)-1).'"';			
		}
		else
		{	$sql = 'UPDATE ebay_stock SET whole_out_p = '.$inventory.' WHERE sku="'.$sku.'"';
			$AD_P_sku = 'UPDATE ebay_stock SET whole_out_p = '.$inventory.' WHERE sku="'.$sku.'P"';			
		}
		// Send a query to the server
		try{
		mysqli_query($con, $sql);
		mysqli_query($con, $AD_P_sku);
		}catch(Exception $e)
		{	echo 'Caught exception: ', $e->getMessage(), "\n";
		}
		   //echo $sku . '---' . $inventory . "<br>";
	}
	//clear memory
	unset($wholeReader);
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
			$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'site listed');
			$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Item ID');
			$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Custom Label');
			$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Quantity Available');
			$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Item Title');
			$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'cosme with P stock');
			$objPHPExcel->getActiveSheet()->SetCellValue('G1', 'wholesale with P stock');
			$objPHPExcel->getActiveSheet()->SetCellValue('H1', 'cosme without P stock');
			$objPHPExcel->getActiveSheet()->SetCellValue('I1', 'wholesale without P stock');
			$objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Instock');
			$objPHPExcel->getActiveSheet()->SetCellValue('K1', 'need DO Non OS');
			$objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Need DO OS');
			$objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Cosme In Stock');
			$objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Wholesale In Stock');
			$objPHPExcel->getActiveSheet()->SetCellValue('O1', 'cosme total Stock');
			$objPHPExcel->getActiveSheet()->SetCellValue('P1', 'Wholesale total Stock');
			
			////////Extra data from database///////////
			try{
				$sql = "SELECT * FROM ebay_stock";			
				$result = mysqli_query($con, $sql);	
				$i=2;
				while($row = mysqli_fetch_array($result))
				{
				$non_os = "No";
				$os = "No";
				$instock = "No";
				$cosme_in_stock = "No";
				$wholesale_in_stock = "No";			
				
				$cosme_stock = $row['cosme_p'] + $row['cosme_out_p'];
				$whole_stock = $row['whole_p'] + $row['whole_out_p'];
				
				//////////////check stocks////////////
				if($cosme_stock > 0)
					$cosme_in_stock = "Yes";
				else
					$cosme_in_stock = "No";
				
				if($whole_stock > 0)
					$wholesale_in_stock = "Yes";
				else
					$wholesale_in_stock = "No";		
					
				//////////////compare cosme and wholesale get if instock/////////
				if($cosme_stock > 0)
					$instock = "Yes";
				else
				{	if(($whole_stock+$cosme_stock) > 0)
						$instock = "Yes";
					else
						$instock = "No";
				
				
				}
				/////////////////check if need to do non OS/////////////
				if($row['ebay_inv'] > 0)
					$non_os = "No";
				else
				{	if($instock == "Yes")
						$non_os = "Yes";
					else
						$non_os = "No";
				}
				
				/////////////////check if need to do OS/////////////
				if($row['ebay_inv'] <= 0)
					$os = "No";
				else
				{	if($instock == "Yes")
						$os = "No";
					else
						$os = "Yes";
				}
				
				//echo $row['sku']."-- cosme stock:".$cosme_stock." and level:".$cosme_in_stock." wholesale stock:".$whole_stock." and level:".$wholesale_in_stock."<br>";				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $row['site_id']);
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $row['item_id']);
				//$objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $row['sku']);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$i, $row['sku'],PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, $row['ebay_inv']);
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, $row['name']);
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$i, $row['cosme_p']);
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$i, $row['whole_p']);
				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$i, $row['cosme_out_p']);
				$objPHPExcel->getActiveSheet()->SetCellValue('I'.$i, $row['whole_out_p']);
				$objPHPExcel->getActiveSheet()->SetCellValue('J'.$i, $instock);
				$objPHPExcel->getActiveSheet()->SetCellValue('K'.$i, $non_os);
				$objPHPExcel->getActiveSheet()->SetCellValue('L'.$i, $os);
				$objPHPExcel->getActiveSheet()->SetCellValue('M'.$i, $cosme_in_stock);
				$objPHPExcel->getActiveSheet()->SetCellValue('N'.$i, $wholesale_in_stock);
				$objPHPExcel->getActiveSheet()->SetCellValue('O'.$i, $cosme_stock);
				$objPHPExcel->getActiveSheet()->SetCellValue('P'.$i, $whole_stock);
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
	//var_dump($sheetData);
 /*if (($handle = fopen($csvfile, "r")) !== FALSE) {	//OPEN CSV
	// FORMAT: 
	$row = 1;
	while (($data = fgetcsv($handle)) !== FALSE) {	//go through data
		if($row>4){
		$site_listed = trim($data[17]);
		$Item_ID = trim($data[0]);
		$Custom_Label = trim($data[1]);
		$Quantity_Available = trim($data[5]);
		echo $site_listed."--".$Item_ID."--".$Custom_Label."--".$Quantity_Available."<br>";
		
		$sql = "SELECT * FROM  Abandon_Email_Record";
		// Send a query to the server
		$result = mysqli_query($con, $sql);
		
		while($row = mysqli_fetch_array($result))
		{
		echo $row['user_email'] . " " . $row['cart_id']." ".$row['Send_Date'];
		echo "<br>";
		}
  
		}
		
		//$line .= $NEWline; ///// Add to new line
		//echo $NEWline."<br>";
		$row++;
	}
}
else 
	"can't open file <br>";
fclose($handle);*/
   
   
mysqli_close($con);
   
   	/////////////////get execution time//////////////
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   echo "This page was created in ".$totaltime." seconds <br>"; 
   
   echo "<br>Download <a href='./eBayStockCheck.xlsx'>Here</a><br>";
?>   
   
   
   
   
   
   