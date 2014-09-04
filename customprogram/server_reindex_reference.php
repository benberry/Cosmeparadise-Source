<?php
//header('Location: ../index.php');

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
?>
<html>
<head><title>Reindex control</title></head>
<body>
<p>
For future reference, you should have SSH access to the host, so you can run these commands yourself via command line, instead of the web interface (which crashes sometimes). Here are some examples:        <br>
                                                                                                                                                                                                              <br>
List the indexes:                                                                                                                                                                                             <br>
php -f /home/cosmepar/dev.cosmeparadise.com/html/shell/indexer.php info                                                                                                                                       <br>
catalog_product_attribute     Product Attributes                                                                                                                                                              <br>
catalog_product_price         Product Prices                                                                                                                                                                  <br>
catalog_url                   Catalog URL Rewrites                                                                                                                                                            <br>
catalog_product_flat          Product Flat Data                                                                                                                                                               <br>
catalog_category_flat         Category Flat Data                                                                                                                                                              <br>
catalog_category_product      Category Products                                                                                                                                                               <br>
catalogsearch_fulltext        Catalog Search Index                                                                                                                                                            <br>
cataloginventory_stock        Stock Status                                                                                                                                                                    <br>
tag_summary                   Tag Aggregation Data                                                                                                                                                            <br>
product                       Brand Category Index                                                                                                                                                            <br>
url                           Brand Url Index                                                                                                                                                                 <br>
                                                                                                                                                                                                              <br>
To reindex one index(example: Catalog URL Rewrites):                                                                                                                                                          <br>
php -f /home/cosmepar/dev.cosmeparadise.com/html/shell/indexer.php -- --reindex catalog_url                                                                                                                   <br>
                                                                                                                                                                                                              <br>
To reindex everything:                                                                                                                                                                                        <br>
php -f /home/cosmepar/dev.cosmeparadise.com/html/shell/indexer.php -- --reindexall                                                                                                                            <br>

</p>
</body>
</html>