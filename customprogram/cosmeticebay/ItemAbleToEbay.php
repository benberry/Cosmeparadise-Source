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
<script type="text/javascript"> 
function submitForm(sku) 
{ 
  if(sku != null)
	document.getElementById('active_sku').value = sku;
  document.getElementById("ShowActive").submit(); 
} 
</script> 
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
	$sql = "SELECT distinct(category_name) FROM `item_able_to_ebay`;"; 					
	$result = mysqli_query($con, $sql);	
	while($row = mysqli_fetch_array($result))
	{	$count++;
		array_push($category_name_array,$row['category_name']);		
	}
	

  ?>


<body>
<h2>Item able to eBay Control programme</h2>
<form action="ItemAbleToEbay.php" method="post" enctype="multipart/form-data">
Search key word:<input type="text" name="search_name" value="<?php echo isset($_POST["search_name"]) ? $_POST["search_name"] : "" ?>"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
SKU:<input type="text" name="SKU" value="<?php echo isset($_POST["SKU"]) ? $_POST["SKU"] : "" ?>"/><br>
<a href="#" onclick="submitForm()">Check Current Listing Page</a>&nbsp;&nbsp;
<a href="http://www.cosmeparadise.com/customprogram/ProductToEbay.php" target="_self">Upload Invoice stock</a>

<br /><br />
<?php
	if($count>0)
	{	echo 'Category <SELECT name="category_name">';
		echo '<option value="">ALL</option>';
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

<form action="./GetItem/ShowStoreActiveItem.php" method="post" id="ShowActive">
<input type="hidden" name="SKU" id="active_sku" value="" />
<input type="hidden" name="CategoryName" value="" />
</form>
<?php	
$product_ids = "";
if(count($product_array) > 0)
{	$combination_cost = 0;
	$new_sku = "";
	$counting=0;
	echo '<p>Product(s) For Combination:</p>';
	echo '<table>';
	echo '<thead><tr><th>Ref</th><th>Name</th><th>SKU</th><th>Category</th><th>Brand</th><th>Qty</th><th>Cost</th><th>Estimate Postage</th><th>Image</th><th>Remove Form Combination</th></tr></thead>';
	echo '<tbody>';
	$product_ids = implode(",",$product_array);
	$sql = 'SELECT * FROM `item_able_to_ebay` WHERE id IN ('.$product_ids.') ORDER BY id ASC;';		
	$result = mysqli_query($con, $sql);	
	while($row = mysqli_fetch_array($result))
	{	$counting++;
		$combination_cost += $row['unit_cost'];
		$new_sku .= $row['mpn']."+";
		if($counting%2 == 0)
			$classname = "cgrey";
		else
			$classname = "clightgrey";
		echo '<tr class="'.$classname.'">';
		echo '<td>'.$counting.'</td>';
		echo '<td width="200px">'.$row['display_name'].'</td>';
		echo '<td>'.$row['mpn'].'</td>';
		echo '<td>'.$row['category_name'].'</td>';
		echo '<td>'.$row['brand_name'].'</td>';
		echo '<td>'.$row['qty'].'</td>';
		echo '<td>'.$row['unit_cost'].'</td>';
		echo '<td>'.$row['postage'].'</td>';
		echo '<td><a target="_blank" href="'.$row['img'].'"><img height="100px" src="'.$row['img'].'" /></a></td>';
		////////////////Remove form Multi add session/////////////////
		echo '<td><form action="ItemAbleToEbay.php" method="post" enctype="multipart/form-data">';
		echo '<input type="hidden" name="search_name" value="'.(isset($_POST["search_name"]) ? $_POST["search_name"] : "").'" />';
		echo '<input type="hidden" name="SKU" value="'.(isset($_POST["SKU"]) ? $_POST["SKU"] : "").'" />';
		echo '<input type="hidden" name="remove_product_id" value="'.$row['id'].'" />';
		echo '<input type="hidden" name="category_name" value="'.$_POST["category_name"].'" />';
		echo '<input type="submit" value="Remove From Combination"/>';
		echo '</form></td>';
		
		echo '</tr>';		
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

if(isset($_POST["search_name"]) || isset($_POST["SKU"])) {
	$search_name = $_POST["search_name"];
	$search_name = str_replace("'", "''", $search_name);	
  
	//SELECT * FROM `item_able_to_ebay` WHERE display_name LIKE "%Canon ACK-DC40%"
	try {  
		$counting=0;
		$First_SQL = 1;
		if($_POST["search_name"] != "")
		{	$search_name = ' WHERE display_name LIKE "%'.$search_name.'%"';
			$First_SQL++;			
		}
		
		$SKU = "";
		if($_POST["SKU"] != "")
		{	if($First_SQL == 1)
			{$SKU = ' WHERE mpn = "'.$_POST["SKU"].'"';
			 $First_SQL++;
			}
			else
			 $SKU = ' AND mpn = "'.$_POST["SKU"].'"';
			
		}
		
		$category_name = "";
		if($_POST["category_name"] != "")
		{	if($First_SQL == 1)
			{$category_name = ' WHERE category_name = "'.$_POST["category_name"].'"';
			 $First_SQL++;
			}
			else
			 $category_name = ' AND category_name = "'.$_POST["category_name"].'"';
			
		}
		
		$exclude_pid = "";
		if($product_ids != "")
		{	if($First_SQL == 1)
			{$exclude_pid = ' WHERE id NOT IN ('.$product_ids.')';
			 $First_SQL++;
			}
			else
			 $exclude_pid = ' AND id NOT IN ('.$product_ids.')';
			
		}
		echo '<p>Searching Result:</p>';	
		echo '<table>';
		//echo '<thead><tr><th>ID</th><th>Name</th><th>MPN</th><th>Category</th><th>Brand</th><th>Cost</th><th>Weight</th><th>Image</th><th>AU eBay</th><th>US eBay</th><th>UK eBay</th><th>To Combination</th></tr></thead>';
		echo '<thead><tr><th>Ref</th><th>Name</th><th>SKU</th><th>Category</th><th>Brand</th><th>Qty</th><th>Cost</th><th>Estimate Postage</th><th>Image</th><th>To Combination</th></tr></thead>';
		echo '<tbody>';
		$sql = 'SELECT * FROM `item_able_to_ebay`'.$search_name.$SKU.$category_name.$exclude_pid.' ORDER BY id ASC;';		
		$result = mysqli_query($con, $sql);	
		while($row = mysqli_fetch_array($result))
		{	$counting++;
			if($counting%2 == 0)
				$classname = "cgrey";
			else
				$classname = "clightgrey";
			echo '<tr class="'.$classname.'">';
			echo '<td>'.$counting.'</td>';
			echo '<td width="200px">'.$row['display_name'].'</td>';
			echo '<td>'.$row['mpn'].'</td>';
			echo '<td>'.$row['category_name'].'</td>';
			echo '<td>'.$row['brand_name'].'</td>';
			echo '<td>'.$row['qty'].'</td>';
			echo '<td>'.$row['unit_cost'].'</td>';
			echo '<td>'.$row['postage'].'</td>';
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
			$SKU_CHECK = _checkIfSkuExists($row['mpn'], $con);
			if( $SKU_CHECK == "Not Exist")
			{////////////////Multi add session/////////////////
			echo '<td><form action="ItemAbleToEbay.php" method="post" enctype="multipart/form-data">';
			echo '<input type="hidden" name="search_name" value="'.(isset($_POST["search_name"]) ? $_POST["search_name"] : "").'" />';
			echo '<input type="hidden" name="product_id" value="'.$row['id'].'" />';
			echo '<input type="hidden" name="category_name" value="'.$_POST["category_name"].'" />';
			echo '<input type="submit" value="Add To Combination"/>';
			echo '</form></td>';
			}else{
				echo '<td>Exist!'.$SKU_CHECK.'</td>';
			
			}
			
			echo '</tr>';		
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
  
  ///////////////function//////////////////////
function _checkIfSkuExists($sku, $con){  
	if( substr(trim(strtoupper($sku)),-1) == 'P')
	{	$sku_with_P = $sku;
		$sku_without_P = substr($sku_with_P, 0, -1);
	}
	else
	{	$sku_with_P = $sku."P";
		$sku_without_P = $sku;
	}
		
    $sql   = "SELECT Site FROM cosme_ebay_listing WHERE SKU = '".$sku_with_P."' ORDER BY Site ASC;";
	$result = mysqli_query($con, $sql);
	if(mysqli_num_rows($result))
	{	$return_msg = "In";
		while (list($Site) = mysqli_fetch_row($result)) {
			$return_msg .= " ".$Site;
		}
		return $return_msg.' with SKU:<a href="#" onclick="submitForm(\''.$sku_with_P.'\')">'.$sku_with_P.'</a>';
	}
	
    $sql   = "SELECT Site FROM cosme_ebay_listing WHERE SKU = '".$sku_without_P."' ORDER BY Site ASC;";
	$result = mysqli_query($con, $sql);
	if(mysqli_num_rows($result))
	{	$return_msg = "In";
		while (list($Site) = mysqli_fetch_row($result)) {
			$return_msg .= " ".$Site;
		}
		return $return_msg.' with SKU:<a href="#" onclick="submitForm(\''.$sku_without_P.'\')">'.$sku_without_P.'</a>';
	}
	
    return "Not Exist";	
} 

?>   
</body>
</html>   