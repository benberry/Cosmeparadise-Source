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
if ($ip == "61.93.89.10" ) $accesslist=true;	//company IP

if ($accesslist==false) 
	{
	echo $ip;
	exit;
	}

if (  $_POST["orderfrom"] == "" && $_POST["orderto"] == "")
  {
?>

<html>
<body>
<h2>Cosmeparadis Abandon cart email records (by date range)</h2>
<form action="abandon_cart_email_open_record.php" method="post" enctype="multipart/form-data">
<label for="Order">Order Date Range:</label>
<input type="text" name="orderfrom" id="orderfrom" /> (YYYY-mm-dd)  -  
<input type="text" name="orderto" id="orderto" /> (YYYY-mm-dd)
<br />
Get opened email to check:<input type="checkbox" name="only_open" value="yes" /> 
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

header('Content-Disposition: attachment; filename="Abandon_Email_Record.csv"');

$delimiter = "\""   ;
$fieldbreak = ","    ;
$linebreak = "\n"      ;
$flag = false; 
$line = "";
$line = $line.	
				"Email".$fieldbreak.				
				"Email Type".$fieldbreak.
				"Send Date(HK time)".$fieldbreak.
				"Open Status".$fieldbreak.
				"Open Date(HK time)".$linebreak;		

if(isset($_POST['only_open']) && $_POST['only_open'] == 'yes')
	$only_open="AND Open='TRUE'";
else
	$only_open="";

 				
$GetItemSQL = "SELECT Abandon_Email_Record.user_email, Abandon_Email_Record.Type,Abandon_Email_Record.cart_id, Abandon_Email_Record.Send_Date, Abandon_Email_Record.Open, Abandon_Email_Record.Open_Date
FROM Abandon_Email_Record 
WHERE DATE(Send_Date) >= DATE('".$_POST["orderfrom"] ."') 
AND DATE(Send_Date) <= DATE('".$_POST["orderto"] ."') ".$only_open;

			foreach ($connection_read->fetchAll($GetItemSQL) as $Abandon_Email_Record) {			
	//if($lastupdatedate == date("0000-00-00 00:00:00"))	
	$line=$line.
			$delimiter.$Abandon_Email_Record['user_email'].$delimiter.$fieldbreak.
            $delimiter.$Abandon_Email_Record['Type'].$delimiter.$fieldbreak.
            $delimiter.$Abandon_Email_Record['Send_Date'].$delimiter.$fieldbreak.
            $delimiter.$Abandon_Email_Record['Open'].$delimiter.$fieldbreak.
            $delimiter.$Abandon_Email_Record['Open_Date'].$delimiter.$linebreak;
			
}

///////////////////////////////////get total record count/////////////////////////
$GetItemSQL = "SELECT COUNT(*) AS record_count
FROM Abandon_Email_Record 
WHERE DATE(Send_Date) >= DATE('".$_POST["orderfrom"] ."') 
AND DATE(Send_Date) <= DATE('".$_POST["orderto"] ."') ".$only_open;

			foreach ($connection_read->fetchAll($GetItemSQL) as $Abandon_Email_Record) {	
			$record_count = $Abandon_Email_Record['record_count'];
			$line=$line.
			$delimiter."Total record from this date range:".$delimiter.$fieldbreak.
            $delimiter.$record_count.$delimiter.$linebreak;
			}
			
 echo $line;
  
?>


