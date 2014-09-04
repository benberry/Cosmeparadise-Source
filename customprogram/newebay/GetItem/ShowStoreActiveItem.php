<?PHP 
/////////////////////////start session//////////////////////////////////
session_start(); 

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
	//////////////set default time zone/////////////
	date_default_timezone_set('Asia/Hong_Kong');
	$date1 = new DateTime("now");
	$time_now = $date1->format('Y-m-d H:i:s');
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
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script type="text/javascript">
    function beforeSubmit(k){
        if (1 == 1){
            //your before submit logic
			//var item1 = "";
			var title = "";
			var qty = "";
			var price = "";
			var Curr = "";
			for(i=1;i<=k;i++)
			{	title = $("#titleEd"+i).val();
				qty = $("#qtyEd"+i).val();
				price = $("#priceEd"+i).val();
				Curr = $("#currEd"+i).val();
				//item1 += qty+"--";
				/////Update Bulk update form value///////
				 $("#BQTYFORM #btitleid"+i).val(title);
				 $("#BQTYFORM #bqtyid"+i).val(qty);
				 $("#BQTYFORM #bpriceid"+i).val(price);
				 $("#BQTYFORM #bcurrid"+i).val(Curr);
			}
			//alert("Hello world:"+k+".  Qty of item:"+item1);
			
        }        
        $("#BQTYFORM").submit();   
    }
	
	function Update_Input_Qty(qty)
	{	$('[name="Qty"]').val(qty);
	}
	
	function submitForm(sku) 
	{ 	if(sku != "")
			$('[name="SKU"]').val(sku);
		$("#ItemAbleToEbay").submit(); 
	} 
	
	function Switch_Page(page) 
	{ 	$('[name="Page_Number"]').val(page);
		$("#page_switch").submit(); 
	} 
</script>
</head>
<?php


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
	///////////Get total active listing
	$sql = 'SELECT SKU FROM `cosme_ebay_listing_2`';
	$result = mysqli_query($con, $sql);
	$Total_Active_Listing = mysqli_num_rows($result);
	
	//////////////////load all category name////////////////////
	$category_count=0;
	$category_name_array = array();	
	$sql = "SELECT distinct(CategoryName) FROM `cosme_ebay_listing_2` ORDER BY CategoryName;";
	$result = mysqli_query($con, $sql);	
	while($row = mysqli_fetch_array($result))
	{	$category_count++;
		array_push($category_name_array,$row['CategoryName']);		
	}
	
	//////////////////load all category name////////////////////
	$site_count=0;
	$Site_name_array = array();	
	$sql = "SELECT distinct(Site) FROM `cosme_ebay_listing_2`;"; 					
	$result = mysqli_query($con, $sql);	
	while($row = mysqli_fetch_array($result))
	{	$site_count++;
		array_push($Site_name_array,$row['Site']);		
	}
	
	////////////Define Page Array///////////////
	$page_item_arrray = array();
	$page_item_arrray['20'] = "20";
	$page_item_arrray['50'] = "50";
	$page_item_arrray['100'] = "100";
	$page_item_arrray['200'] = "200";
	
	////////////Define Sort By Array///////////////
	$Sort_By_Array = array();
	$Sort_By_Array['SKU ASC'] = "SKU ASC";
	$Sort_By_Array['SKU DESC'] = "SKU DESC";
	$Sort_By_Array['Qty ASC'] = "QuantityAvailable ASC";
	$Sort_By_Array['Qty DESC'] = "QuantityAvailable DESC";
	$Sort_By_Array['Price ASC'] = "CurrentPrice DESC";
	$Sort_By_Array['Price DESC'] = "CurrentPrice DESC";	
	$Sort_By_Array['No Record Stock only'] = "No Record";	
  ?>
<body>
<h2>New account eBay Active Item</h2>
<form action="ShowStoreActiveItem.php" method="post" enctype="multipart/form-data">
<table>
<tr>
<td>Title</td><td><input type="text" name="Title" value="<?php echo isset($_POST["Title"]) ? $_POST["Title"] : "" ?>"/></td>
<td>Item No.</td><td><input type="text" name="ItemID" value="<?php echo isset($_POST["ItemID"]) ? $_POST["ItemID"] : "" ?>"/></td>
<td>SKU</td><td><input type="text" name="SKU" value="<?php echo isset($_POST["SKU"]) ? $_POST["SKU"] : "" ?>"/></td>
<td>Show Items</td><td><SELECT name="page_item">
<option value="">All</option>
<?php
	foreach($page_item_arrray as $key => $key_value)
	{ 	if($_POST["page_item"] == $key_value)
			echo '<option value="'.$key_value.'" SELECTED>'.$key.'</option>';	
		else
			echo '<option value="'.$key_value.'">'.$key.'</option>';
	
	}

?>
</SELECT></td>
</tr>
<tr>
<td>Category</td><td colspan="3"><SELECT name="CategoryName">
<option value="">ALL</option>
<?php
	if($category_count>0)
	{	for($i=0;$i<$category_count;$i++)
			if($_POST["CategoryName"] == $category_name_array[$i])
				echo '<option value="'.$category_name_array[$i].'" SELECTED>'.$category_name_array[$i].'</option>';	
			else
				echo '<option value="'.$category_name_array[$i].'">'.$category_name_array[$i].'</option>';	
	}
?>
</SELECT></td>
<td>Site</td><td><SELECT name="Site">
<option value="">ALL</option>
<?php
	if($site_count>0)
	{	for($i=0;$i<$site_count;$i++)
			if($_POST["Site"] == $Site_name_array[$i])
				echo '<option value="'.$Site_name_array[$i].'" SELECTED>'.$Site_name_array[$i].'</option>';	
			else
				echo '<option value="'.$Site_name_array[$i].'">'.$Site_name_array[$i].'</option>';	
					
	}
?>
</SELECT></td>
<td>Sort By</td><td><SELECT name="SortBy">
<option value="">--</option>
<?php
	foreach($Sort_By_Array as $key => $key_value)
	{ 	if($_POST["SortBy"] == $key_value)
			echo '<option value="'.$key_value.'" SELECTED>'.$key.'</option>';	
		else
			echo '<option value="'.$key_value.'">'.$key.'</option>';
	
	}

?>
</SELECT></td>
</tr><tr>
<td colspan="2"><input type="submit" name="submit" value="Submit" /></td>
<td colspan="2"><a href="../AddItem/AddListingPreset.php" target="_self">Add Listing Page</a></td>
<!--<td colspan="2"><a href="#" onclick="submitForm()">Item Able To Ebay Page</a></td>-->
<td colspan="2">Total current Listing:<?php echo $Total_Active_Listing ?></td>
</table>
</form>


<form action="../ItemAbleToEbay.php" method="post" id="ItemAbleToEbay">
<input type="hidden" name="search_name" id="search_name" value="" />
<input type="hidden" name="SKU" id="SKU" value="" />
</form>

<!-- Progress bar holder -->
<div id="progress" style="width:500px;border:1px solid #ccc;display:none;"></div>
<!-- Progress information -->
<div id="information" style="width"></div>

<?php	
if($_POST["Title"] != "" || $_POST["SKU"] != "" || $_POST["ItemID"] != "" || ISSET($_POST["CategoryName"])) {
	/////////////Do bulk Update//////////////
	if($_POST["BulkUpdate"] == "Confirm")
		{	$total_items = count($_POST['ItemIDs']);
		
			echo '<script language="javascript">document.getElementById("progress").style.display = "";</script>';
			// This is for the buffer achieve the minimum size in order to flush data
			echo str_repeat(' ',1024*64);
			flush();
			for($i=0; $i< $total_items; $i++)
			{	// Calculate the percentation
				$percent = intval($i/$total_items * 100)."%";
				// Javascript for updating the progress bar and information
				echo '<script language="javascript">document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-image:url(http://www.cosmeparadise.com/customprogram/cosmeticebay/pbar-ani.gif);\">&nbsp;</div>";document.getElementById("information").innerHTML="'.$i.' Item(s) processed.";</script>';		
				// This is for the buffer achieve the minimum size in order to flush data
				echo str_repeat(' ',1024*64);
				// Send output to browser immediately
				flush();
				
				$ItemID = $_POST['ItemIDs'][$i];
				$Titles = $_POST['Titles'][$i];
				$Qty = $_POST['Qtys'][$i];
				$Price = $_POST['Prices'][$i];
				$Curr = $_POST['Currs'][$i];
				revise($ItemID,$Titles,$Qty,$Price,$Curr,$con);			
			}
			// Javascript for updating the progress bar and information
			echo '<script language="javascript">document.getElementById("progress").innerHTML="<div style=\"width:100%;background-image:url(http://www.cosmeparadise.com/customprogram/cosmeticebay/pbar-ani.gif);\">&nbsp;</div>";    document.getElementById("information").innerHTML="Process completed";</script>';
			// This is for the buffer achieve the minimum size in order to flush data
			echo str_repeat(' ',1024*64);
			// Send output to browser immediately
			flush();
		}
  
	//SELECT * FROM `ebay_test_data` WHERE display_name LIKE "%Canon ACK-DC40%"
	try {  
		$counting=1;
		$First_SQL = 1;
		$Title="";
		$ItemID="";
		$SKU="";
		$CategoryName="";
		$Site="";
		echo '<table>';
		echo '<thead><tr><th>Ref</th><th>Item ID</th><th>Site</th><th>Title</th><th>Dur.</th><th>SKU</th><th>Price</th><th>Curr</th><th>Qty</th><th>Sold</th><th>Ship To.</th><th>End Time(HK)</th><th>Image</th><th>End Listing</th><th>Real Stock</th></tr></thead>';
		echo '<tbody>';
		
		if($_POST["Title"] != "") {
			$Title = $_POST["Title"];
			$Title = str_replace("'", "''", $Title);	
			if($First_SQL == 1)
			{	$Title = ' WHERE Title LIKE "%'.$_POST["Title"].'%"';
				$First_SQL++;
			}
			else
				$Title = ' AND Title LIKE "%'.$_POST["Title"].'%"';
		}
		
		if($_POST["ItemID"] != "") {
			if($First_SQL == 1)
			{	$ItemID = ' WHERE ItemID = "'.$_POST["ItemID"].'"';
				$First_SQL++;
			}
			else
				$ItemID = ' AND ItemID = "'.$_POST["ItemID"].'"';			
		}
		
		if($_POST["SKU"] != "") {
			if($First_SQL == 1)
			{	$SKU = ' WHERE SKU = "'.$_POST["SKU"].'"';
				$First_SQL++;
			}
			else
				$SKU = ' AND SKU = "'.$_POST["SKU"].'"';			
		}
		
		if($_POST["CategoryName"] != "")
		{	if($First_SQL == 1)
			{	$CategoryName = ' WHERE CategoryName = "'.$_POST["CategoryName"].'"';
				$First_SQL++;
			}
			else
				$CategoryName = ' AND CategoryName = "'.$_POST["CategoryName"].'"';	
		}
		
		if($_POST["Site"] != "")
		{	if($First_SQL == 1)
			{	$Site = ' WHERE Site = "'.$_POST["Site"].'"';
				$First_SQL++;
			}
			else
				$Site = ' AND Site = "'.$_POST["Site"].'"';		
		}		
		
		if($_POST["SortBy"] != "" && $_POST["SortBy"] != "No Record")
			$SortBy = ' ORDER BY '.$_POST["SortBy"];		
		else
			$SortBy = ' ORDER BY SKU ASC';
			
		if($_POST["page_item"] != "")
		{	if($_POST["Page_Number"] != "")
				$page_item = ' LIMIT '.(($_POST["Page_Number"]-1)*$_POST["page_item"]).' , '.$_POST["page_item"];
			else
				$page_item = ' LIMIT '.$_POST["page_item"];
		}else
			$page_item = '';
		
		$sql = 'SELECT * FROM `cosme_ebay_listing_2` CBL'.$Title.$ItemID.$SKU.$CategoryName.$Site.' GROUP BY ItemID'.$SortBy.$page_item.';';
		//echo $sql;
		$BulkUpdateItems = "";
		$result = mysqli_query($con, $sql);	
		while($row = mysqli_fetch_array($result))
		{	if($counting%2 == 0)
				$classname = "cgrey";
			else
				$classname = "clightgrey";
				
			$date = new DateTime($row['EndTime']);
			$date->add(new DateInterval('PT8H'));
			$new_Date = $date->format('Y-m-d H:i:s');
			if($time_now > $new_Date)
				continue;
			
			$real_stock = _checkIfSkuExists($row['SKU'], $con);
			if($_POST["SortBy"] == "No Record")
			{	if( $real_stock != "No Record!")
					continue;
			}
			
			echo '<tr class="'.$classname.'">';
			echo "<td>".$counting."</td>";
			echo '<td><a target="_blank" href="http://www.ebay.com/itm/'.$row['ItemID'].'">'.$row['ItemID'].'</a></td>';
			echo '<td>'.$row['Site'].'</td>';
			echo '<td width="200px"><textarea name="item_title" id="titleEd'.$counting.'" rows="6" cols="25">'.$row['Title'].'</textarea></td>';
			echo '<td>'.$row['ListingDuration'].'</td>';
			echo '<td>'.$row['SKU'].'</td>';
			echo '<td><input type="text" id="priceEd'.$counting.'" name="price" value="'.$row['CurrentPrice'].'" /></td>';
			echo '<td>'.$row['Currency'].'<input type="hidden" id="currEd'.$counting.'" name="Curr" value="'.$row['Currency'].'" /></td>';			
			echo '<td><input type="text" id="qtyEd'.$counting.'" name="Qty" value="'.$row['QuantityAvailable'].'" size="6" /></td>';
			echo '<td>'.$row['QuantitySold'].'</td>';			
			echo '<td>'.$row['ShipToLocations'].'</td>';
			echo '<td>'.$new_Date.'</td>';
			echo '<td><a target="_blank" href="'.$row['PictureURL'].'"><img height="100px" src="'.$row['PictureURL'].'" /></a></td>';
			echo '<td><a target="_self" href="../EndItem/EndFixedPriceItem.php?ID='.$row['ItemID'].'">End Listing Page</a></td>';
			
			
			if( $real_stock != "No Record!")
				echo '<td>'.$real_stock.'</td>';
			else
				echo '<td>No Record!</td>';
			
			echo '</tr>';
			
			$BulkUpdateItems .= '<input type="hidden" name="ItemIDs[]" value="'.$row['ItemID'].'" />';
			$BulkUpdateItems .= '<input type="hidden" name="Titles[]" value="0" id="btitleid'.$counting.'" />';
			$BulkUpdateItems .= '<input type="hidden" name="Qtys[]" value="0" id="bqtyid'.$counting.'" />';
			$BulkUpdateItems .= '<input type="hidden" name="Prices[]" value="0" id="bpriceid'.$counting.'" />';
			$BulkUpdateItems .= '<input type="hidden" name="Currs[]" value="0" id="bcurrid'.$counting.'" />';
		
		$counting++;
		}
		//////////////for bulk QTY update/////////////////
			if($BulkUpdateItems != "")
			{	echo '<tr><td colspan="8"><form id="BQTYFORM" action="ShowStoreActiveItem.php" method="post" enctype="multipart/form-data">';
				echo '<input type="hidden" name="Title" value="'.$_POST["Title"].'" />';
				echo '<input type="hidden" name="ItemID" value="'.$_POST["ItemID"].'" />';
				echo '<input type="hidden" name="SKU" value="'.$_POST["SKU"].'" />';
				echo '<input type="hidden" name="CategoryName" value="'.$_POST["CategoryName"].'" />';
				echo '<input type="hidden" name="Site" value="'.$_POST["Site"].'" />';
				echo '<input type="hidden" name="page_item" value="'.$_POST["page_item"].'" />';
				echo '<input type="hidden" name="SortBy" value="'.$_POST["SortBy"].'" />';
				echo '<input type="hidden" name="Page_Number" value="'.$_POST["Page_Number"].'" />';
				echo '<input type="hidden" name="BulkUpdate" value="Confirm" />';
				echo $BulkUpdateItems;
				echo '<input type="submit" value="Bulk Update Items" onclick="return beforeSubmit('.($counting-1).');" /></form></td>';
				echo '<td colspan="4"><input type="button" value="Set Bulk Qty" onclick="Update_Input_Qty(document.getElementById(\'bulk_qty\').value)" /><input type="text" value="" id="bulk_qty" name="bulk_qty" /></td>';
				echo "</tr>";
			}
			
		//////////////Page update/////////////////
				echo '<tr><td colspan="8"><form id="page_switch" action="ShowStoreActiveItem.php" method="post" enctype="multipart/form-data">';
				echo '<input type="hidden" name="Title" value="'.$_POST["Title"].'" />';
				echo '<input type="hidden" name="ItemID" value="'.$_POST["ItemID"].'" />';
				echo '<input type="hidden" name="SKU" value="'.$_POST["SKU"].'" />';
				echo '<input type="hidden" name="CategoryName" value="'.$_POST["CategoryName"].'" />';
				echo '<input type="hidden" name="Site" value="'.$_POST["Site"].'" />';
				echo '<input type="hidden" name="page_item" value="'.$_POST["page_item"].'" />';
				echo '<input type="hidden" name="SortBy" value="'.$_POST["SortBy"].'" />';	
				echo '<input type="hidden" name="Page_Number" value="'.$_POST["Page_Number"].'" />';	
				
				
				if($_POST["page_item"] == "")
					echo 'Page 1 of 1';
				else
				{	$Total_Page = round($Total_Active_Listing/$_POST["page_item"],0);
					if($_POST["Page_Number"] != "")
					{	if($_POST["Page_Number"] == "1")
							echo 'Page 1 of '.$Total_Page.' <input type="submit" value="Next" onclick="Switch_Page(2);" />';					
						else if($_POST["Page_Number"] == $Total_Page)
							echo '<input type="submit" value="Back" onclick="Switch_Page('.($_POST["Page_Number"]-1).');" />Page '.$_POST["Page_Number"].' of '.$Total_Page;
						else
							echo '<input type="submit" value="Back" onclick="Switch_Page('.($_POST["Page_Number"]-1).')" /> Page '.$_POST["Page_Number"].' of '.$Total_Page.' <input type="submit" value="Next" onclick="Switch_Page('.($_POST["Page_Number"]+1).');" />';
					}
					else
					{	if($Total_Active_Listing > $_POST["page_item"])
							echo 'Page 1 of '.$Total_Page.' <input type="submit" value="Next" onclick="Switch_Page(2);" />';
						else
							echo 'Page 1 of 1';
					
					}
				}
				echo "</form></td></tr>";
			
			
		echo '</tbody></table>';
		// This is for the buffer achieve the minimum size in order to flush data
		echo str_repeat(' ',1024*64);
		flush();
	}catch(Exception $e)
	{	echo 'Caught exception: ', $e->getMessage(), "\n";
	}		
	
	mysqli_close($con); 
	
	if($_POST["BulkUpdate"] == "Confirm")
	{
		//////Sleep then set display to none
		sleep(2);
		echo '<script language="javascript">document.getElementById("progress").style.display = "none";document.getElementById("information").style.display = "none";</script>';
		// This is for the buffer achieve the minimum size in order to flush data
		echo str_repeat(' ',1024*64);
		flush();
	}
}
else
  {
	echo "<br>Please search by Title or ItemID or SKU<br>";
	mysqli_close($con);   
	
	exit;
  }
  
////////////////////////////////////////Revise Item Function/////////////////////////////////////  
  function revise($ItemID,$Item_Title,$Qty,$Price,$Curr,$con)
	{	require('../get-common/keys.php');
		require_once('../get-common/eBaySession.php');
        ini_set('magic_quotes_gpc', false);    // magic quotes will only confuse things like escaping apostrophe
		
		//SiteID must also be set in the Request's XML
		//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
		//SiteID Indicates the eBay site to associate the call with
		$siteID = 0;
		//the call being made:
		$verb = 'ReviseItem';
		
		$compatabilityLevel = "653";
		//$itemTitle = "M.A.C Studio Fix Fluid SPF 15 Foundation(berry test revise!)";
		///Build the request Xml string
		$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestXmlBody .= '<ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
		$requestXmlBody .= '<WarningLevel>High</WarningLevel>';
		$requestXmlBody .= '<ErrorLanguage>en_US</ErrorLanguage>';
		$requestXmlBody .= '<Item>';
		$requestXmlBody .= '<ItemID>'.$ItemID.'</ItemID>';	
		$requestXmlBody .= '<Title><![CDATA['.$Item_Title.']]></Title>';	
		$requestXmlBody .= "<Quantity>$Qty</Quantity>";		
		$requestXmlBody .= '<StartPrice currencyID="'.$Curr.'">'.$Price.'</StartPrice>';		
		//$requestXmlBody .= "<Title><![CDATA[$itemTitle]]></Title>";		
		$requestXmlBody .= '</Item>';
		$requestXmlBody .= '</ReviseItemRequest>';
		
		//echo $requestXmlBody."<br>";
		
        //Create a new eBay session with all details pulled in from included keys.php
        $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
		
		//send the request and get response
		$responseXml = $session->sendHttpRequest($requestXmlBody);
		if(stristr($responseXml, 'HTTP 404') || $responseXml == '')
			die('<P>Error sending request');
		
		//echo "<p><b>$responseXml</b></p><br><Br>";
		
		//Xml string is parsed and creates a DOM Document object
		$responseDoc = new DomDocument();
		$responseDoc->loadXML($responseXml);
			
		//get any error nodes
		$errors = $responseDoc->getElementsByTagName('Errors');
		$ack = $responseDoc->getElementsByTagName('Ack')->item(0)->nodeValue;
		echo "ItemID:".$ItemID." update $ack<br>";
		//if there are error nodes
		if($ack == "Failure")
		{			
			$code     = $errors->item(0)->getElementsByTagName('ErrorCode');
			$shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');
			$longMsg  = $errors->item(0)->getElementsByTagName('LongMessage');
			//Display code and shortmessage
			echo '<P>', $code->item(0)->nodeValue, ' : ', str_replace(">", "&gt;", str_replace("<", "&lt;", $shortMsg->item(0)->nodeValue));
			//if there is a long message (ie ErrorLevel=1), display it
			if(count($longMsg) > 0)
				echo '<BR>ItemID:'.$ItemID."--".str_replace(">", "&gt;", str_replace("<", "&lt;", $longMsg->item(0)->nodeValue));
	
		} else { //no errors
            
			 $sql = 'UPDATE cosme_ebay_listing_2 SET Title = "'.$Item_Title.'", CurrentPrice = '.(double)$Price.', QuantityAvailable = '.$Qty.' WHERE ItemID='.$ItemID.';';	
			/// Send a query to the server
			mysqli_query($con, $sql);	
		} // if $errors->length > 0		
	}
	
	function _checkIfSkuExists($sku, $con){  
	if( substr(trim(strtoupper($sku)),-1) == 'P')
	{	$sku_with_P = $sku;
		$sku_without_P = substr($sku_with_P, 0, -1);
	}
	else
	{	$sku_with_P = $sku."P";
		$sku_without_P = $sku;
	}
		
    $sql   = "SELECT qty FROM item_able_to_ebay WHERE mpn = '".$sku_with_P."';";
	$result = mysqli_query($con, $sql);
	if(mysqli_num_rows($result))
	{	$return_msg = "";
		while (list($qty) = mysqli_fetch_row($result)) {
			$return_msg .= '<a href="#" onclick="submitForm(\''.$sku_with_P.'\')">'.$qty.'</a> ';
		}
		return trim($return_msg);
	}
	
    $sql   = "SELECT qty FROM item_able_to_ebay WHERE mpn = '".$sku_without_P."';";
	$result = mysqli_query($con, $sql);
	if(mysqli_num_rows($result))
	{	$return_msg = "";
		while (list($qty) = mysqli_fetch_row($result)) {
			$return_msg .= '<a href="#" onclick="submitForm(\''.$sku_without_P.'\')">'.$qty.'</a> ';
		}
		return trim($return_msg);
	}
	
    return "No Record!";	
	}
?>   
</body>
</html>   