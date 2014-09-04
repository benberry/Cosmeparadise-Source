<?php

if (($_FILES["file"]["type"] == "text/csv")
 || ($_FILES["file"]["type"] == "application/vnd.ms-excel")
 || ($_FILES["file"]["type"] == "application/vnd.msexcel")
 || ($_FILES["file"]["type"] == "application/excel")
 || ($_FILES["file"]["type"] == "text/comma-separated-values"))
 {
  if ($_FILES["file"]["error"] > 0)
    {   echo "Return Code: " . $_FILES["file"]["error"] . "<br />"; exit;    }
 }
else
  {
?>

<html>
<body>
<h2>Get images width and height programme</h2>
<b>Please keep the format: sku, image URL</b>
<form action="getImageSize.php" method="post" enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> 
<br /><br />
<input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>

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

header('Content-Disposition: attachment; filename="ImageWidthHeight.csv"');
////////////////////////////start////////////////////////////
$delimiter = "\"";
$fieldbreak = ",";
$linebreak = "\n";
$flag = false; 
$line = "";

if (($handle = fopen($csvfile, "r")) !== FALSE) {	//OPEN CSV
	$line = $line.	
			$delimiter."sku".$delimiter.$fieldbreak.				
			$delimiter."image URL".$delimiter.$fieldbreak.
			$delimiter."Width".$delimiter.$fieldbreak.
			$delimiter."Height".$delimiter.$fieldbreak.
			$delimiter."One size >= 500".$delimiter.$linebreak;		
	// FORMAT:  Sku, URL
	$row = 1;
	while (($data = fgetcsv($handle)) !== FALSE) {	//go through data
	$Sku = trim($data[0]);
	$URL = trim($data[1]);
	//echo "gogogo"
	if($row>1){
		$exists = checkRemoteFile($URL);
		if ($exists)
		{
			list($width, $height, $type, $attr) = getimagesize($URL);
			if($width >= 500 || $height >= 500)
				$onesite = "Yes";
			else
				$onesite = "No";
				
			$line = $line.	
				$delimiter.$Sku.$delimiter.$fieldbreak.				
				$delimiter.$URL.$delimiter.$fieldbreak.
				$delimiter.$width.$delimiter.$fieldbreak.
				$delimiter.$height.$delimiter.$fieldbreak.		
				$delimiter.$onesite.$delimiter.$linebreak;	
			
			if($onesite == "No"){
				$ch = curl_init($URL);
				$fp = fopen($Sku.'.jpg', 'wb');
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in sconds
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);
				//echo "$Sku, $URL, image download success<br>";
			}
		}else{
			$line = $line.	
				$delimiter.$Sku.$delimiter.$fieldbreak.				
				$delimiter.$URL.$delimiter.$fieldbreak.
				$delimiter."Image NOT exist".$delimiter.$fieldbreak.
				$delimiter."Image NOT exist".$delimiter.$fieldbreak.
				$delimiter."Image NOT exist".$delimiter.$linebreak;
		}
	}
	$row++;	
	}
}
fclose($handle);


echo $line;

function checkRemoteFile($url)
{
    $cch = curl_init();
    curl_setopt($cch, CURLOPT_URL,$url);
	curl_setopt($cch, CURLOPT_TIMEOUT, 400); //timeout in sconds
    // don't download content
    curl_setopt($cch, CURLOPT_NOBODY, 1);
    curl_setopt($cch, CURLOPT_FAILONERROR, 1);
    curl_setopt($cch, CURLOPT_RETURNTRANSFER, 1);
    if(curl_exec($cch)!==FALSE)
    {	curl_close($cch);
        return true;
    }
    else
    {	curl_close($cch);
        return false;
    }
}

?>