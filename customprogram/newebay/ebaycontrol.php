<?php 
session_start(); 
$product_array = array();
if(isset($_SESSION['product_id']))
{
	$product_array = $_SESSION['product_id'];
	if(isset($_POST["product_id"]) && $_POST["product_id"] != "")
	{
		array_push($product_array, $_POST["product_id"]);
		$_SESSION['product_id'] = $product_array;
	}
	
	if(isset($_POST["remove_product_id"]) && $_POST["remove_product_id"] != "")
	{
		// Search
		$pos = array_search($_POST["remove_product_id"], $product_array);
		// Remove from array
		unset($product_array[$pos]);
		if(count($product_array) > 0)
			$_SESSION['product_id'] = $product_array;
		else
			unset($_SESSION['product_id']);
	}
	
}else{
	if(isset($_POST["product_id"]) && $_POST["product_id"] != "")
	{	array_push($product_array, $_POST["product_id"]);
		$_SESSION['product_id'] = $product_array;
	}
}

?>
<html>
<head>
<style>
table {border-color:gray;border-spacing:2px;}
th {background-color:grey;}
td {padding-left:20px;padding-right:20px;}
.cgrey {background-color:#D8D8D8;}
.clightgrey {background-color:#F0F0F0;}
</style>
</head>
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
	//////////////////load all category name////////////////////
	$count=0;
	$category_name_array = array();	
	$sql = "SELECT distinct(category_name) FROM `ebay_test_data`;"; 					
	$result = mysqli_query($con, $sql);	
	while($row = mysqli_fetch_array($result))
	{	$count++;
		array_push($category_name_array,$row['category_name']);		
	}
	

  ?>


<body>
<h2>eBay Control programme</h2>
<form action="ebaycontrol.php" method="post" enctype="multipart/form-data">
Search key word:<input type="text" name="search_name" value="<?php echo isset($_POST["search_name"]) ? $_POST["search_name"] : "" ?>"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="./GetItem/ShowStoreActiveItem.php" target="_self">Check Current Listing Page</a>
<br /><br />
<?php
	if($count>0)
	{	echo 'Category <SELECT name="category_name">';
		//echo '<option value="ALL">ALL</option>';
		for($i=0;$i<$count;$i++)
		{
			if(isset($_POST["category_name"]) && $category_name_array[$i] == $_POST["category_name"])
				echo '<option value="'.$category_name_array[$i].'" SELECTED>'.$category_name_array[$i].'</option>';
			else
				echo '<option value="'.$category_name_array[$i].'">'.$category_name_array[$i].'</option>';
		}
		echo '</SELECT>';
	}
?>
<input type="submit" name="submit" value="Submit" />
</form>

<?php	
$product_ids = "";
if(count($product_array) > 0)
{	$combination_cost = 0;
	$new_sku = "";
	$counting=0;
	echo '<p>Product(s) For Combination:</p>';
	echo '<table>';
	echo '<thead><tr><th>ID</th><th>Name</th><th>MPN</th><th>Category</th><th>Brand</th><th>Cost</th><th>Weight</th><th>Image</th><th>Remove Form Combination</th></tr></thead>';
	echo '<tbody>';
	$product_ids = implode(",",$product_array);
	$sql = 'SELECT * FROM `ebay_test_data` WHERE id IN ('.$product_ids.') ORDER BY id ASC;';		
	$result = mysqli_query($con, $sql);	
	while($row = mysqli_fetch_array($result))
	{	$combination_cost += $row['unit_cost'];
		$new_sku .= $row['id']."+";
		if($counting%2 == 0)
			$classname = "cgrey";
		else
			$classname = "clightgrey";
		echo '<tr class="'.$classname.'">';
		echo '<td>'.$row['id'].'</td>';
		echo '<td width="200px">'.$row['display_name'].'</td>';
		echo '<td>'.$row['mpn'].'</td>';
		echo '<td>'.$row['category_name'].'</td>';
		echo '<td>'.$row['brand_name'].'</td>';
		echo '<td>'.$row['unit_cost'].'</td>';
		echo '<td>'.$row['weight'].'</td>';
		echo '<td><a target="_blank" href="'.$row['img'].'"><img height="100px" src="'.$row['img'].'" /></a></td>';
		////////////////Remove form Multi add session/////////////////
		echo '<td><form action="ebaycontrol.php" method="post" enctype="multipart/form-data">';
		echo '<input type="hidden" name="search_name" value="'.(isset($_POST["search_name"]) ? $_POST["search_name"] : "").'" />';
		echo '<input type="hidden" name="remove_product_id" value="'.$row['id'].'" />';
		echo '<input type="hidden" name="category_name" value="'.$_POST["category_name"].'" />';
		echo '<input type="submit" value="Remove From Combination"/>';
		echo '</form></td>';
		
		echo '</tr>';
		$counting++;
	}
	echo '</tbody></table>';
	
	///////////////show suggestion table//////////////	
	$new_sku = substr($new_sku,0,-1);
	echo '<table><thead><tr><th>Total cost(HKD)</th><th>SKU will be created</th><th>To listing page</th></tr></thead>';
	echo '<tbody><tr>';
	echo '<td>'.$combination_cost.'</td>';
	echo '<td>'.$new_sku.'</td>';	
	echo '<td><a href="./AddItem/AddListingPreset.php">Confirm</a></td>';
	echo '</tr></tbody></table>';
}

if(isset($_POST["search_name"]) && $_POST["search_name"] != "") {
	$search_name = $_POST["search_name"];
	$search_name = str_replace("'", "''", $search_name);	
  
	//SELECT * FROM `ebay_test_data` WHERE display_name LIKE "%Canon ACK-DC40%"
	try {  
		$counting=0;
		$exclude_pid = "";
		if($product_ids != "")
			$exclude_pid = "AND id NOT IN (".$product_ids.") ";
		echo '<p>Searching Result:</p>';	
		echo '<table>';
		//echo '<thead><tr><th>ID</th><th>Name</th><th>MPN</th><th>Category</th><th>Brand</th><th>Cost</th><th>Weight</th><th>Image</th><th>AU eBay</th><th>US eBay</th><th>UK eBay</th><th>To Combination</th></tr></thead>';
		echo '<thead><tr><th>ID</th><th>Name</th><th>MPN</th><th>Category</th><th>Brand</th><th>Cost</th><th>Weight</th><th>Image</th><th>To Combination</th></tr></thead>';
		echo '<tbody>';
		$sql = 'SELECT * FROM `ebay_test_data` WHERE display_name LIKE "%'.$search_name.'%" AND category_name = "'.$_POST["category_name"].'" '.$exclude_pid.' ORDER BY id ASC;';		
		$result = mysqli_query($con, $sql);	
		while($row = mysqli_fetch_array($result))
		{	if($counting%2 == 0)
				$classname = "cgrey";
			else
				$classname = "clightgrey";
			echo '<tr class="'.$classname.'">';
			echo '<td>'.$row['id'].'</td>';
			echo '<td width="200px">'.$row['display_name'].'</td>';
			echo '<td>'.$row['mpn'].'</td>';
			echo '<td>'.$row['category_name'].'</td>';
			echo '<td>'.$row['brand_name'].'</td>';
			echo '<td>'.$row['unit_cost'].'</td>';
			echo '<td>'.$row['weight'].'</td>';
			echo '<td><a target="_blank" href="'.$row['img'].'"><img height="100px" src="'.$row['img'].'" /></a></td>';
			/*if($row['au_ebay'] != null)
				echo '<td><a href="#">'.$row['au_ebay'].'</a></td>';
			else
				echo '<td><a href="./AddItem/AddFixedPriceItem.php?id='.$row['id'].'&title='.urlencode($row['display_name']).'&mpn='.$row['mpn'].'&category_name='.$row['category_name'].'&brand_name='.urlencode($row['brand_name']).'&weight='.$row['weight'].'&sale_price='.$row['unit_cost'].'&img_url='.$row['img'].'&country=AU">Add to eBay</a></td>';
			if($row['us_ebay'] != null)
				echo '<td><a href="#">'.$row['us_ebay'].'</a></td>';
			else
				echo '<td><a href="./AddItem/AddFixedPriceItem.php?id='.$row['id'].'&title='.urlencode($row['display_name']).'&mpn='.$row['mpn'].'&category_name='.$row['category_name'].'&brand_name='.urlencode($row['brand_name']).'&weight='.$row['weight'].'&sale_price='.$row['unit_cost'].'&img_url='.$row['img'].'&country=US">Add to eBay</a></td>';
			if($row['uk_ebay'] != null)
				echo '<td><a href="#">'.$row['uk_ebay'].'</a></td>';
			else
				echo '<td><a href="./AddItem/AddFixedPriceItem.php?id='.$row['id'].'&title='.urlencode($row['display_name']).'&mpn='.$row['mpn'].'&category_name='.$row['category_name'].'&brand_name='.urlencode($row['brand_name']).'&weight='.$row['weight'].'&sale_price='.$row['unit_cost'].'&img_url='.$row['img'].'&country=UK">Add to eBay</a></td>';
			*/	
			////////////////Multi add session/////////////////
			echo '<td><form action="ebaycontrol.php" method="post" enctype="multipart/form-data">';
			echo '<input type="hidden" name="search_name" value="'.(isset($_POST["search_name"]) ? $_POST["search_name"] : "").'" />';
			echo '<input type="hidden" name="product_id" value="'.$row['id'].'" />';
			echo '<input type="hidden" name="category_name" value="'.$_POST["category_name"].'" />';
			echo '<input type="submit" value="Add To Combination"/>';
			echo '</form></td>';
			
			echo '</tr>';		
		

		$counting++;
		}
		echo '</tbody></table>';
		/*$result = mysqli_query($con, $sql);	
		$row_value = mysqli_fetch_array($result);
		if($row_value['count_exist'] > 0)
			$sql = 'UPDATE stock_n_cost SET cosme_stock = '.$inventory.', cosme_cost = '.(double)$cost.' WHERE sku="'.$sku.'";';		
		else
			$sql = 'INSERT INTO stock_n_cost (sku, cosme_stock, cosme_cost) VALUES ("'.$sku.'", '.$inventory.','.(double)$cost.');';		
		
		/// Send a query to the server
		mysqli_query($con, $sql);*/
	}catch(Exception $e)
	{	echo 'Caught exception: ', $e->getMessage(), "\n";
	}		
	
	mysqli_close($con);   
}
else
  {
	mysqli_close($con);   
	exit;
  }
?>   
</body>
</html>   