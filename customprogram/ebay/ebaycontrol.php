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
Search key word:<input type="text" name="display_name" value="<?php echo isset($_POST["display_name"]) ? $_POST["display_name"] : "" ?>"/><br /><br />
<?php
	if($count>0)
	{	echo 'Category <SELECT name="category_name">';
		echo '<option value="ALL">ALL</option>';
		for($i=0;$i<$count;$i++)
		{
			echo '<option value="'.$category_name_array[$i].'">'.$category_name_array[$i].'</option>';
		}
		echo '</SELECT>';
	}
?>
<input type="submit" name="submit" value="Submit" />
</form>


<?php	
if(isset($_POST["display_name"]) && $_POST["display_name"] != "") {
	$display_name = $_POST["display_name"];
	$display_name = str_replace("'", "''", $display_name);	
  
	//SELECT * FROM `ebay_test_data` WHERE display_name LIKE "%Canon ACK-DC40%"
	try {  
		$counting=0;
		echo '<table>';
		echo '<thead><tr><th>ID</th><th>Name</th><th>MPN</th><th>Category</th><th>Brand</th><th>Cost</th><th>Weight</th><th>Image</th><th>To eBay</th></tr></thead>';
		echo '<tbody>';
		$sql = 'SELECT * FROM `ebay_test_data` WHERE display_name LIKE "%'.$display_name.'%"';		
		$result = mysqli_query($con, $sql);	
		while($row = mysqli_fetch_array($result))
		{
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
			echo '<td>EBAY ITEM ID</td>';		
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