<?php

if (  $_POST["orderfrom"] == "" && $_POST["orderto"] == "")
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
<h2>Cosmeparadis Order (by date range)</h2>
<font color="red">Just in case for missing '0' problem, better open with Open office.</font><br />
<form action="web_order_saled.php" method="post" enctype="multipart/form-data">
<label for="Order">Order Date Range:</label>
<input class="datepicker" type="text" name="orderfrom" id="orderfrom" /> (YYYY-mm-dd)  -  
<input class="datepicker" type="text" name="orderto" id="orderto" /> (YYYY-mm-dd)
<br />
Report Type:<select name="report_type">
  <option value="item" SELECTED >Item Sales Report</option>
  <option value="brand">Brand Sales Report</option>
</select>
<br />
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

header('Content-Disposition: attachment; filename=Order_Saled_Record.csv');

$delimiter = "\""   ;
$fieldbreak = ","    ;
$linebreak = "\n"      ;
$flag = false; 
$line = $delimiter."Date From:".$_POST["orderfrom"]." To:".$_POST["orderto"].$delimiter.$linebreak;

if($_POST['report_type'] == "item")
{
	$line = $line.	
				"sku".$fieldbreak.				
				"Name".$fieldbreak.
				"Brand".$fieldbreak.
				"Quantity".$fieldbreak.
				"Total Price".$fieldbreak.
				"Average Price per Item".$linebreak;		
	
	
				
	$GetItemSQL = 'SELECT item.sku, item.name, eaov.value as brand, item.qty, item.total_price, item.avg_price FROM
	(SELECT items.product_id, items.sku, items.name, SUM(items.qty_ordered) AS qty, SUM(items.base_row_total) AS total_price, (SUM(items.base_row_total)/SUM(items.qty_ordered)) AS avg_price
	FROM sales_flat_order SFO 
	LEFT JOIN sales_flat_order_item items ON items.order_id = SFO.entity_id AND items.name NOT LIKE "%FREE GIFT%"
	WHERE SFO.created_at >= "'.$_POST["orderfrom"].'" AND SFO.created_at <= "'.$_POST["orderto"].'" AND (SFO.status = "complete" OR SFO.status = "processing_shipped" OR SFO.status = "sh") AND items.sku IS NOT NULL
	GROUP BY items.sku ORDER by qty DESC) item
	LEFT JOIN catalog_product_entity_int cpei ON cpei.attribute_id = 81 AND cpei.entity_id = item.product_id
	LEFT JOIN eav_attribute_option_value eaov on eaov.option_id = cpei.value AND eaov.store_id = 0
	';
		
	foreach ($connection_read->fetchAll($GetItemSQL) as $web_order) {			
	//if($lastupdatedate == date("0000-00-00 00:00:00"))	
			$line=$line.
			$delimiter.$web_order['sku'].$delimiter.$fieldbreak.
			$delimiter.$web_order['name'].$delimiter.$fieldbreak.
			$delimiter.$web_order['brand'].$delimiter.$fieldbreak.
			$delimiter.$web_order['qty'].$delimiter.$fieldbreak.
			$delimiter.$web_order['total_price'].$delimiter.$fieldbreak.
			$delimiter.$web_order['avg_price'].$delimiter.$linebreak;				
			}
}
else
{
	$line = $line.	
				"Brand".$fieldbreak.
				"Count".$linebreak;		
	
	
				
	$GetItemSQL = 'SELECT eaov.value as brand, sum(item.qty) brand_count FROM
	(SELECT items.product_id, items.sku, items.name, SUM(items.qty_ordered) AS qty, SUM(items.base_row_total) AS total_price, (SUM(items.base_row_total)/SUM(items.qty_ordered)) AS avg_price
	FROM sales_flat_order SFO 
	LEFT JOIN sales_flat_order_item items ON items.order_id = SFO.entity_id AND items.name NOT LIKE "%FREE GIFT%"
	WHERE SFO.created_at >= "'.$_POST["orderfrom"].'" AND SFO.created_at <= "'.$_POST["orderto"].'" AND (SFO.status = "complete" OR SFO.status = "processing_shipped" OR SFO.status = "sh") AND items.sku IS NOT NULL
	GROUP BY items.sku ORDER by qty DESC) item
	LEFT JOIN catalog_product_entity_int cpei ON cpei.attribute_id = 81 AND cpei.entity_id = item.product_id
	LEFT JOIN eav_attribute_option_value eaov on eaov.option_id = cpei.value AND eaov.store_id = 0
	GROUP BY eaov.value
	ORDER BY brand_count DESC
	';		
	
	foreach ($connection_read->fetchAll($GetItemSQL) as $web_order) {			
	//if($lastupdatedate == date("0000-00-00 00:00:00"))	
			$line=$line.
			$delimiter.$web_order['brand'].$delimiter.$fieldbreak.
			$delimiter.$web_order['brand_count'].$delimiter.$linebreak;				
			}	
}	
	
			
 echo $line;
  
?>


