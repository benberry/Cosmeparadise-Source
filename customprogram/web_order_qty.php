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
<h2>Find Order QTY(by date range)</h2>
<form action="web_order_qty.php" method="post" enctype="multipart/form-data">
<label for="Order">Order Date Range:</label>
<input class="datepicker" type="text" name="orderfrom" id="orderfrom" /> (YYYY-mm-dd)  -  
<input class="datepicker" type="text" name="orderto" id="orderto" /> (YYYY-mm-dd)
<br />
Report Type:<select name="report_type">
  <option value="hasfreegift" SELECTED >With Free gift</option>
  <option value="nofreegift">Without Free gift</option>
</select>
<br />
<input type="submit" name="submit" value="Submit" />
</form>


<?php
if ( $_POST["orderfrom"] == "" || $_POST["orderto"] == "")
{	echo "Date haven't selected yet";
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

if($_POST['report_type'] == "hasfreegift")
{	$checkFreeGift = '';
	echo "Order With free gift <br>";
}
else
{	$checkFreeGift = 'AND items.name NOT LIKE "%FREE GIFT%"';
	echo "Order Without free gift <br>";
}
	
	$order_qty = array();
	$GetItemSQL = 'SELECT SFO.increment_id, COUNT(SFO.increment_id) saled_qty
	FROM sales_flat_order SFO 
	LEFT JOIN sales_flat_order_item items ON items.order_id = SFO.entity_id 
	WHERE SFO.created_at >= "'.$_POST["orderfrom"].'" AND SFO.created_at <= "'.$_POST["orderto"].'" AND (SFO.status = "complete" OR SFO.status = "processing_shipped" OR SFO.status = "sh") AND items.sku IS NOT NULL '.$checkFreeGift.'
    GROUP BY SFO.increment_id';		
	
	foreach ($connection_read->fetchAll($GetItemSQL) as $web_order) {			
	//if($lastupdatedate == date("0000-00-00 00:00:00"))	
			$order_qty[$web_order['increment_id']] = $web_order['saled_qty'];
					
			}
	//print_r($order_qty);
	$total_count = count($order_qty);
	for($i=1; $i <= max($order_qty); $i++)
	{		
		$DV = findDuplicates($order_qty,$i);
		echo "Qty".$i.": have ".$DV." order(s) with percentage ".(ROUND($DV/$total_count*100,2))."% <br>";
	}
	
?>
</body>
</html>
<?php	
	
///////////////////function/////////////////////
function findDuplicates($data,$dupval) { 
$nb= 0; 
foreach($data as $key => $val) 
if ($val==$dupval) $nb++; 
return $nb; 
} 	
?>


