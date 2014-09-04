<?php
error_reporting(E_ALL);  // turn on all errors, warnings and notices for easier debugging

$query = 'ipod';  // A query

$SafeQuery = urlencode($query);
$MyAppID = "Uniondut-7c56-4493-9553-a0f23992268d";
$endpoint = 'http://open.api.ebay.com/shopping';  // URL to call
$responseEncoding = 'XML';   // Format of the response

// Construct the FindItems call
$apicall = "$endpoint?callname=FindItems&version=517&siteid=0&appid=$MyAppID&QueryKeywords=$SafeQuery&responseencoding=$responseEncoding";

//"http://open.api.ebay.com/shopping?appid=Uniondut-7c56-4493-9553-a0f23992268d&version=517&siteid=0&callname=FindItems&QueryKeywords=ipod&responseencoding=XML"


// Load the call and capture the document returned by the Shopping API
$resp = simplexml_load_file($apicall);

// Check to see if the response was loaded, else print an error
if ($resp) {
	$results = '';

    // If the response was loaded, parse it and build links
    foreach($resp->Item as $item) {
        $link  = $item->ViewItemURLForNaturalSearch;
        $title = $item->Title;

		// For each result node, build a link and append it to $results
		$results .= "<a href=\"$link\">$title</a><br/>";
	}
}
// If there was no response, print an error
else {
	$results = "Oops! Must not have gotten the response!";
}

?>
<html>
<head>
<title>
eBay Search Results for <?php echo $query; ?>
</title>
</head>
<body>
<h1>eBay Search Results</h1>
<?php echo $results;?>
</body>
</html>