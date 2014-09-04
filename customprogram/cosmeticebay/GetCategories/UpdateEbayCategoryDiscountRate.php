<?php

if(isset($_FILES["file"])) { 
	if (($_FILES["file"]["type"] == "text/csv")
	|| ($_FILES["file"]["type"] == "application/vnd.ms-excel")
	|| ($_FILES["file"]["type"] == "application/vnd.msexcel")
	|| ($_FILES["file"]["type"] == "application/excel")
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
<h2>Update Discount Rate for eBay Categories</h2>
<b>Please keep the format: CategoryID(SKIP first row of header)</b>
<form action="UpdateEbayCategoryDiscountRate.php" method="post" enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> 
<br /><br />
<input type="submit" name="submit" value="Submit" />
</form>

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


$con = mysqli_connect(
'localhost', /* The host to connect to */
'cosmepar_program', /* The user to connect as */
'RecapsBoronWhirlGrands45', /* The password to use */
'cosmepar_custom'); /* The default database to query */

if (!$con) {
printf("Can't connect to cosmepar_custom MySQL Server. Errorcode: %s\n", mysqli_connect_error());
exit;
}



if (($handle = fopen($csvfile, "r")) !== FALSE) {	//OPEN CSV
	// FORMAT:  Sku, NAME, EB URL, OZ URL, BS URL, SBN URL
	$row = 1;
	while (($data = fgetcsv($handle)) !== FALSE) {	//go through data
		if($row>1){
			$CategoryID = trim($data[0]);
			$DiscountRate = trim($data[1]);
			firstcategory($CategoryID, $con, $DiscountRate);
		}
		$row++;
	}
	echo "Total row:".$row." updated";
}	
	
	
function firstcategory($F_CategoryID, $con, $DiscountRate)
{	echo "F_CategoryID: $F_CategoryID <br>";
	$sql = "SELECT CategoryID, CategoryName, LeafCategory FROM ebay_categories where CategoryID =".$F_CategoryID;	
	$getcats = mysqli_query($con, $sql);
	while (list($CategoryID, $CategoryName, $LeafCategory) = mysqli_fetch_row($getcats)) { 
					if($LeafCategory == 0)
						getleafcategory($CategoryID, $con, $DiscountRate);
					else
					{	$sql =  "UPDATE ebay_categories SET DiscountRate = '$DiscountRate' WHERE CategoryID =".$CategoryID;
						mysqli_query($con, $sql);
					}
				}
}
			
function getleafcategory($P_CategoryID, $con, $DiscountRate)
{
	$sql = "SELECT CategoryID, CategoryName, LeafCategory FROM ebay_categories where CategoryParentID != CategoryID AND CategoryParentID =".$P_CategoryID;
	$getcats = mysqli_query($con, $sql);
	while (list($CategoryID, $CategoryName, $LeafCategory) = mysqli_fetch_row($getcats)) { 
				if($LeafCategory == 0)
					getleafcategory($CategoryID, $con, $DiscountRate);
				else
				{	$sql =  "UPDATE ebay_categories SET DiscountRate = '$DiscountRate' WHERE CategoryID =".$CategoryID;
					mysqli_query($con, $sql);
				}
			}
}		
	
?>
</body>
</html>