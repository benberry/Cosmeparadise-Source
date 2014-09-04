<?php
////////////////////////get into magento /////////////////////
set_time_limit(0);
ignore_user_abort();
error_reporting(E_ALL^E_NOTICE);
$_SVR = array();

$path_include = "../app/Mage.php";
require_once $path_include;
Mage::app();

///////////////////////////////////////////////////////////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//// call SQL read
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL write


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
<h2>SBN email filter</h2>
<b>Please keep the format: email(SKIP first row of header)</b>
<form action="SBNemailfilter.php" method="post" enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> 
<br /><br />
<input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>

<?php	
echo "Below will show the current list:<br>";
$SQL = "SELECT * FROM SBN_filter_email";
$counter = 1;	
foreach ($connection_read->fetchAll($SQL) as $SBN_filter_email) {	
	echo $counter." -> ".$SBN_filter_email['email']."<br>";
	$counter++;
}

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

if (($handle = fopen($csvfile, "r")) !== FALSE) {	//OPEN CSV
	/////////////////CLEAR ALL FROM SBN_products////////////////////////
$sql = "DELETE FROM SBN_filter_email";
$connection_write->query($sql);
	// FORMAT:  email
	$row = 1;
	while (($data = fgetcsv($handle)) !== FALSE) {	//go through data
	
	if($row > 1)
	{
	$email = trim($data[0]);
	/////////////////////////import into our database/////////////////
	$Insert_sql = "INSERT INTO SBN_filter_email (email) VALUES (?)";
	
	try{
		$connection_write->query($Insert_sql, array($email));
	 }catch(Exception $e)
		{	echo 'Caught exception: ', $e->getMessage(), "\n";
		}
		
	}
	$row++;
	}
	
}

echo "Below will show the current list:<br>";
$SQL = "SELECT * FROM SBN_filter_email";
$counter = 1;	
foreach ($connection_read->fetchAll($SQL) as $SBN_filter_email) {	
	echo $counter." -> ".$SBN_filter_email['email']."<br>";
	$counter++;
}

$URL = $_SERVER['REQUEST_URI'];
echo '<br> <a href="'.$URL.'">Back</a>';
	
?>