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
<h2>download images programme</h2>
<b>Please keep the format: sku, image URL</b>
<form action="bulkdownloadimage.php" method="post" enctype="multipart/form-data">
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

////////////////////////////start////////////////////////////
echo "start\n";

if (($handle = fopen($csvfile, "r")) !== FALSE) {	//OPEN CSV
	// FORMAT:  Sku, URL
	$row = 1;
	while (($data = fgetcsv($handle)) !== FALSE) {	//go through data
	$Sku = trim($data[0]);
	$URL = trim($data[1]);
	
	if($row>1){
	$exists = checkRemoteFile($URL);
	if ($exists)
	{
	$ch = curl_init($URL);
	$fp = fopen($Sku.'.jpg', 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in sconds
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	echo "$Sku, $URL, image download success<br>";
	}
	else
	{	
		$ch = curl_init('http://www.mnit.ac.in/new/PortalProfile/images/faculty/noimage.jpg');
		$fp = fopen($Sku.'.jpg', 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		echo "$Sku, $URL, image not exist<br>";
	}
	}	
	$row++;
	ob_flush();
    flush();
	}
}
fclose($handle);

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

echo "end";
/*
function save_image($inPath,$outPath)
{ //Download images from remote server
    $in=    fopen($inPath, "rb");
    $out=   fopen($outPath, "wb");
    while ($chunk = fread($in,8192))
    {
        fwrite($out, $chunk, 8192);
		echo "write file<br>";
    }
    fclose($in);
    fclose($out);
	
}

echo "start<br>";
save_image('http://www.bonjourhk.com/public_photo/T/TSH188514.JPG','TSH188514.jpg');
echo "end";
*/

?>