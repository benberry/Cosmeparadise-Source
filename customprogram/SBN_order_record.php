<?php

if (  $_POST["orderfrom"] == "" && $_POST["orderto"] == "")
  {
?>

<html>
<body>
<h2>Cosmeparadis SBN Order (by date range)</h2>
<form action="SBN_order_record.php" method="post" enctype="multipart/form-data">
<label for="Order">Order Date Range:</label>
<input type="text" name="orderfrom" id="orderfrom" /> (YYYY-mm-dd)  -  
<input type="text" name="orderto" id="orderto" /> (YYYY-mm-dd)
<br />
<SELECT name="product_type">
<OPTION value="SBN_product_ID">SBN product ID</OPTION>
<OPTION value="Order_product_sku" SELECTED>Our sku</OPTION>
</SELECT>
<br /><br />
<input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>

<?php
exit;
  }
///////////////////////////get magento require file and field/////////////////
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

if (isset($_GET['show_stores']) && ($_GET['show_stores'] == 'on')) {
	$stores = Mage::app()->getStores();
	
	foreach ($stores as $i) {
		print $i->getCode() . "<br />";
	}
	exit;
}
if (isset($_GET['store']) && ($_GET['store'] != "")) {
	$store = $_GET['store'];
}
else {
	$store = $default_store_code;
}

Mage::app($store);
///////////////////////////////////////////////////////////////////////

/////////////connect database///////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//////////// Make connection to call SQL read
//$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//////////// Make connection to call SQL write

header('Content-Disposition: attachment; filename="SBN_Order_Record.csv"');

$delimiter = "\""   ;
$fieldbreak = ","    ;
$linebreak = "\n"      ;
$flag = false; 
$line = "";

$product_type = $_POST['product_type'];
	if($product_type == "SBN_product_ID")
	{
	$line = $line.	
					"Order Number".$fieldbreak.				
					"SBN product IDs".$fieldbreak.
					"SBN response status".$fieldbreak.
					"Send Time(HK time)".$linebreak;		
	
					
	$GetItemSQL = "SELECT * 
	FROM  SBN_order 
	WHERE DATE(make_time_hk) >= DATE('".$_POST["orderfrom"] ."') 
	AND DATE(make_time_hk) <= DATE('".$_POST["orderto"] ."') ";
	
				foreach ($connection_read->fetchAll($GetItemSQL) as $SBN_order) {			
		//if($lastupdatedate == date("0000-00-00 00:00:00"))	
				$line=$line.
				$delimiter.$SBN_order['order_no'].$delimiter.$fieldbreak.
				$delimiter.$SBN_order['product_ids'].$delimiter.$fieldbreak.
				$delimiter.$SBN_order['status'].$delimiter.$fieldbreak.
				$delimiter.$SBN_order['make_time_hk'].$delimiter.$linebreak;				
				}
	}
	else
	{
		$line = $line.	
					"Order Number".$fieldbreak.				
					"Our sku".$fieldbreak.
					"Quantity".$fieldbreak.
					"SBN response status".$fieldbreak.
					"Send Time(HK time)".$linebreak;		
	
					
	$GetItemSQL = "SELECT SBN_order . * , GROUP_CONCAT( items.sku SEPARATOR  ',' ) AS our_sku, GROUP_CONCAT( items.qty_ordered SEPARATOR  ',' ) AS qty
	FROM  SBN_order 
	LEFT JOIN sales_flat_order ON sales_flat_order.increment_id = SBN_order.order_no
	LEFT JOIN sales_flat_order_item items ON items.order_id = sales_flat_order.entity_id
	WHERE DATE(make_time_hk) >= DATE('".$_POST["orderfrom"] ."') 
	AND DATE(make_time_hk) <= DATE('".$_POST["orderto"] ."') 
	GROUP BY SBN_order.order_no";
	
				foreach ($connection_read->fetchAll($GetItemSQL) as $SBN_order) {			
		//if($lastupdatedate == date("0000-00-00 00:00:00"))	
				$line=$line.
				$delimiter.$SBN_order['order_no'].$delimiter.$fieldbreak.
				$delimiter.$SBN_order['our_sku'].$delimiter.$fieldbreak.
				$delimiter.$SBN_order['qty'].$delimiter.$fieldbreak.
				$delimiter.$SBN_order['status'].$delimiter.$fieldbreak.
				$delimiter.$SBN_order['make_time_hk'].$delimiter.$linebreak;				
				}
	
	
	}
///////////////////////////////////get total record count/////////////////////////
$GetItemSQL = "SELECT COUNT(*) AS record_count
FROM SBN_order 
WHERE DATE(make_time_hk) >= DATE('".$_POST["orderfrom"] ."') 
AND DATE(make_time_hk) <= DATE('".$_POST["orderto"] ."') ";

			foreach ($connection_read->fetchAll($GetItemSQL) as $SBN_order) {	
			$record_count = $SBN_order['record_count'];
			$line=$line.
			$delimiter."Total record from this date range:".$delimiter.$fieldbreak.
            $delimiter.$record_count.$delimiter.$linebreak;
			}
			
 echo $line;
  
?>


