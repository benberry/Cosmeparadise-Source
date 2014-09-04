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
<h2>Grab ebay price programme by name</h2>
<b>Please keep the format: sku, name(SKIP first row of header)</b>
<form action="ebaygrabprice.php" method="post" enctype="multipart/form-data">
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

////////////////////timer//////////////
//$execution_time = microtime(); # Start counting
 $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime; 

////////////////////////get into magento code/////////////////////
/*set_time_limit(0);
ignore_user_abort();
error_reporting(E_ALL^E_NOTICE);
$_SVR = array();

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

if (isset($_GET['store']) && ($_GET['store'] != "")) {
	$store = $_GET['store'];
}
else {
	$store = $default_store_code;
}

Mage::app($store);*/
////////////////////////////start////////////////////////////
$Enclosure = "\"";
$Separator = ",";
$Breakline = "\n";
//echo "start<br>";
$line = "sku".$Separator.
"name".$Separator.
"ebay name".$Separator.
"search URL".$Separator.
"store".$Separator.
"price".$Separator.
"shipping cost".$Separator.
"suggest price".$Separator.
"skip record".$Separator.$Breakline;

$cookieFile = '/tmp/ebaycookie.txt';
$search_url = "http://www.ebay.com/sch/i.html";
$have_result = '<span class="rcnt">';

if (($handle = fopen($csvfile, "r")) !== FALSE) {	//OPEN CSV
	// FORMAT:  Sku, NAME
	$row = 1;
	Loginebay($cookieFile);
	while (($data = fgetcsv($handle)) !== FALSE) {	//go through data
	$Sku = trim($data[0]);
	$Product_Name = trim($data[1]);
	
	//$GrapURL = trim($search_url.$Product_Name_convert.$sorting_var);
	
	if($row>1){
			$NEWline="";
			$selfprice = "can't find product";
			$newlink = 0;
			$product_data = array('_nkw' => $Product_Name, '_sop' => '15', '_fcid' => '1',  'LH_BIN' => '1' );			
			$string = curlUsingPost($search_url, $product_data, $cookieFile);
			//echo $string;
			
			///////////get search URL///////////
			$fields_string = '';
			foreach($product_data as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
			$fields_string = rtrim($fields_string,'&');
			//$fields_string = substr($fields_string, 0, -1);
			$GrapURL = $search_url."?".$fields_string;
			////////////////////////////////////
			$startpos = 0; 
			$stoppos = 0;
			$imagestart = 0;
			$counting = 0;
			$name_start = 0; $name_stop = 0;
			$store_pos = 0;
			$shipping = "";
			//echo $string."<br>";
			if(strpos( $string , $have_result) !== false)	/////if have result
			{	//echo "have record<br>";
				///////////////GET RECORD total///////////////
				$record_count_pos = strpos( $string , $have_result);
				$record_count_start = $record_count_pos+strlen($have_result);		
				$record_count_stop = strpos($string, '</span>', $record_count_start);
				$record_count = substr($string, $record_count_start, $record_count_stop-$record_count_start);		
				//echo $record_count."<br>";
				if(strpos( $string , "Store:") !== false)	/////if have store
				{	
					//echo "have store<br>";
					$store_pos = strpos( $string , "Store:");
					//echo "store_pos:".$store_pos."<br>";
					while($counting < 10 && $counting < (int)$record_count)
					{	$startpos = strpos($string, 'itemprop="name">', $stoppos);	
						$stoppos = strpos($string, '<span class="watch">', $startpos);						
						if($store_pos > $startpos && $store_pos < $stoppos)
						{	
							//echo "start:".$startpos.", stoppos:".$stoppos."<br>";
							///////////////find name show in ebay///////////////
							$name_start = $startpos+strlen('itemprop="name">');		
							$name_stop = strpos($string, '</a>', $name_start);
							$ebay_name = substr($string, $name_start, $name_stop-$name_start);							
							//////////filter name out////////////
								$lower_name = strtolower($ebay_name);
								//echo $lower_name."<br>";
								if(strpos($lower_name, 'no box')  !== false || strpos($lower_name, 'tester')  !== false || strpos($lower_name, 'unbox')  !== false || strpos($lower_name, 'unboxed')  !== false)
								{	$store_pos = strpos( $string , "Store:", $stoppos);
									//echo "exist filter name<br>";
									$counting++;
									continue;								
								}
								////////////////for specify product check name/////////////
								if($Product_Name == "Elizabeth Arden 5th Avenue Eau De Parfum Spray 125ml")
								{	if(strpos($lower_name, 'nyc')  !== false)
									{	$store_pos = strpos( $string , "Store:", $stoppos);
										//echo "exist filter nyv<br>";
										$counting++;
										continue;								
									}
								}
							//echo "ebay_name:".$ebay_name."<br>";
							///////////////find store name/////////////
							$store_start = strpos($string, '<span class="v">', $name_stop)+strlen('<span class="v">');
							$store_stop = strpos($string, '</a>', $store_start);
							$store_name = strip_tags(substr($string, $store_start, $store_stop-$store_start));//remove tag
							//echo "store_name:".$store_name."<br>";
							//////////////find price///////////////////
							$price_start = strpos($string, '<div  class="g-b" itemprop="price">', $store_stop)+strlen('<div  class="g-b" itemprop="price">');
							$price_stop = strpos($string, '</div>', $price_start);
							$price = trim(substr($string, $price_start, $price_stop-$price_start));
							$price = str_replace("$", "", $price);
							//echo "price:".$price."<br>";
							if(strpos( $string , '<span class="fee">', $startpos) !== false && strpos( $string , '<span class="fee">', $startpos) > $startpos && strpos( $string , '<span class="fee">', $startpos) < $stoppos)	//////if have shipping cost
							{
								$shipping_start = strpos($string, '<span class="fee">', $price_stop)+strlen('<span class="fee">');
								$shipping_stop = strpos($string, '</span>', $shipping_start);
								$shipping = trim(substr($string, $shipping_start, $shipping_stop-$shipping_start));
								$shipping = str_replace(" shipping", "", $shipping);
								$shipping = str_replace("+$", "", $shipping);
							}
							else if(strpos( $string , "<span class='gfsp'>", $startpos) !== false && strpos( $string , "<span class='gfsp'>", $startpos) > $startpos && strpos( $string , "<span class='gfsp'>", $startpos) < $stoppos)	//////if show free shipping
								$shipping = "Free shipping";
							else
								$shipping = "Shipping not specified";
							//echo "shipping:".$shipping."<br>";	
							
							if($store_name != "Cosme Paradise") 
							{	if($shipping != "Free shipping" && $shipping != "Shipping not specified")
									$suggestprice = (double)$price + (double)$shipping - 0.01;
								else
									$suggestprice = (double)$price - 0.01;
							}
							else
								$suggestprice = $price;
							/////////////////////////Put data in Line///////////////////					
							$NEWline = $Enclosure.$Sku.$Enclosure.$Separator.	
							$Enclosure.$Product_Name.$Enclosure.$Separator.								
							$Enclosure.$ebay_name.$Enclosure.$Separator.	
							$Enclosure.$GrapURL.$Enclosure.$Separator.	
							$Enclosure.$store_name.$Enclosure.$Separator.	
							$Enclosure.$price.$Enclosure.$Separator.	
							$Enclosure.$shipping.$Enclosure.$Separator.								
							$Enclosure.$suggestprice.$Enclosure.$Separator.								
							$Enclosure.$counting.$Enclosure.$Breakline;		// end
							
							$line .= $NEWline; ///// Add to new line
							//echo $NEWline."<br>";
							echo "sku:".$Sku." finished<br>";
							flush();
							break;
						}
						
						/*$dch = curl_init($productURL);
						curl_setopt($dch, CURLOPT_RETURNTRANSFER, TRUE);
						curl_setopt($dch, CURLOPT_HEADER, TRUE); // We'll parse redirect url from header.
						curl_setopt($dch, CURLOPT_FOLLOWLOCATION, FALSE); // We want to just get redirect url but not to follow it.
						$response = curl_exec($dch);
						preg_match_all('/^Location:(.*)$/mi', $response, $matches);
						curl_close($dch);
						//echo !empty($matches[1]) ? trim($matches[1][0]) : 'No redirect found';
						$productURL = !empty($matches[1]) ? trim($matches[1][0]) : 'No redirect found';*/
						$counting++;
					}							
				}
			}
			else
			{	//echo "no record<br>";
				/////////////////////////Put data in Line///////////////////					
				$NEWline = $Enclosure.$Sku.$Enclosure.$Separator.	
				$Enclosure.$Product_Name.$Enclosure.$Separator.	
				$Enclosure."no record".$Enclosure.$Separator.	
				$Enclosure."no record".$Enclosure.$Separator.	
				$Enclosure."no record".$Enclosure.$Separator.	
				$Enclosure."no record".$Enclosure.$Separator.	
				$Enclosure."no record".$Enclosure.$Separator.								
				$Enclosure."no record".$Enclosure.$Separator.								
				$Enclosure."no record".$Enclosure.$Breakline;		// end		
				$line .= $NEWline; ///// Add to new line				
			}
			
		}
		$row++;
	}
}

/////////////open file and write into it///////////////
	$filename = "./ebaygrabprice.csv";
	$ourFileHandle = fopen($filename, 'w') or die("can't open file");
	fputs($ourFileHandle, $line);
	//close file after write the content into it
	fclose($ourFileHandle);
	
	echo "<br><br>success. check file link <a href=\"./ebaygrabprice.csv\">Here</a><br>";
	
	/////////////////get execution time//////////////
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   echo "This page was created in ".$totaltime." seconds </body></html>"; 
   
////////////////function////////////////
function curlUsingPost($url, $data, $cookieFile)
{

    if(empty($url) OR empty($data))
    {
        return 'Error: invalid Url or Data';
    }

    
    //url-ify the data for the POST
    $fields_string = '';
    foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
    $fields_string = rtrim($fields_string,'&');


    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,count($data));
    curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
	
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
	//curl_setopt($ch, CURLOPT_REFERER, $URL2);

    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10); # timeout after 10 seconds, you can increase it
   //curl_setopt($ch,CURLOPT_HEADER,false);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);  # Set curl to return the data instead of printing it to the browser.
   curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36"); # Some server may refuse your request if you dont pass user agent

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    //execute post
    $result = curl_exec($ch);

    //close connection
    curl_close($ch);
    return $result;
}

function Loginebay($cookieFile)
{

$userid    = 'cosme-paradise';
$pass = 'uriuriorkewnfiuiu3oi93';

// initial login page which redirects to correct sign in page, sets some cookies
$URL = 'https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&ru=http%3A%2F%2Fwww.ebay.com%2F';
//$URL = 'https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&ru=http%3A%2F%2Fwww.ebay.com%2Fsch%2Fi.html%3F_nkw%3DHermes%2BUn%2BJardin%2BSur%2BLe%2BNil%2BEau%2BDe%2BToilette%2BSpray%2B100ml%26_sop%3D15%26_fcid%3D1%26LH_BIN%3D1';

$ch  = curl_init();

if (!file_exists($cookieFile) || !is_writable($cookieFile)){
            echo 'Cookie file missing or not writable.';
            die;
    }
	
curl_setopt($ch, CURLOPT_URL, $URL);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);        
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

$page = curl_exec($ch);

// try to find the actual login form
if (!preg_match('/<form name="SignInForm".*?<\/form>/is', $page, $form)) {
    die('Failed to find log in form!');
}

$form = $form[0];

// find the action of the login form
if (!preg_match('/action="([^"]+)"/i', $form, $action)) {
    die('Failed to find login form url');
}

$URL2 = $action[1]; // this is our new post url

// find all hidden fields which we need to send with our login, this includes security tokens 
$count = preg_match_all('/<input type="hidden"\s*name="([^"]*)"\s*id="([^"]*)"\s*value="([^"]*)"/i', $form, $hiddenFields);

// find all hidden fields which we need to send with our login, this includes security tokens 
$count_a = preg_match_all('/<input type="hidden"\s*name="([^"]*)"\s*id="([^"]*)"\s*value="([^"]*)"/i', $form, $hiddenFields_a);

$postFields = array();

// turn the hidden fields into an array
for ($i = 0; $i < $count; ++$i) {
    $postFields[$hiddenFields[1][$i]] = $hiddenFields[2][$i];
}

// turn the hidden fields into an array
for ($i = 0; $i < $count_a; ++$i) {
    $postFields[$hiddenFields_a[2][$i]] = $hiddenFields_a[3][$i];
}

// add our login values
$postFields['userid'] = $userid;
$postFields['keepMeSignInOption'] = 1;
$postFields['pass'] = $pass;

$post = '';

// convert to string, this won't work as an array, form will not accept multipart/form-data, only application/x-www-form-urlencoded
foreach($postFields as $key => $value) {
    $post .= $key . '=' . urlencode($value) . '&';
}

$post = substr($post, 0, -1);

// set additional curl options using our previous options
curl_setopt($ch, CURLOPT_URL, $URL2);
curl_setopt($ch, CURLOPT_REFERER, $URL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

$page = curl_exec($ch); // make request

}
?>