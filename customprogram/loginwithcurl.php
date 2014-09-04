<?php
//https://signin.ebay.com/ws/eBayISAPI.dll?userid=cosme-paradise&pass=uriuriorkewnfiuiu3oi93

$login_url = 'https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&ru=http%3A%2F%2Fwww.ebay.com%2F';
//Test to login cosmeparadise
//$login_url = 'https://www.cosmeparadise.com/customer/account/loginPost/';
 
//These are the post data username and password
//$post_data = 'co_partnerId=2&siteid=0&UsingSSL=1&userid=cosme-paradise&pass=uriuriorkewnfiuiu3oi93&keepMeSignInOption=1';

 $data = array(
    'MfcISAPICommand' => 'SignInWelcome',
    'bhid' => 'a1%3Dna~a2%3Dna~a3%3Dna~a4%3DMozilla~a5%3DNetscape~a6%3D5.0%20(Windows%20NT%206.1%3B%20WOW64)%20AppleWebKit%2F537.36%20(KHTML%2C%20like%20Gecko)%20Chrome%2F31.0.1650.63%20Safari%2F537.36~a7%3D20030107~a8%3Dna~a9%3Dtrue~a10%3D~a11%3Dtrue~a12%3DWin32~a13%3Dna~a14%3DMozilla%2F5.0%20(Windows%20NT%206.1%3B%20WOW64)%20AppleWebKit%2F537.36%20(KHTML%2C%20like%20Gecko)%20Chrome%2F31.0.1650.63%20Safari%2F537.36~a15%3Dtrue~a16%3Den-US~a17%3DISO-8859-1~a18%3Dsignin.ebay.com~a19%3Dna~a20%3Dna~a21%3Dna~a22%3Dna~a23%3D1920~a24%3D1080~a25%3D32~a26%3D1040~a27%3Dna~a28%3DFri%20Jan%2010%202014%2017%3A09%3A34%20GMT%2B0800%20(China%20Standard%20Time)~a29%3D8~a30%3Drpl%7C~a31%3Dyes~a32%3Dna~a33%3Dna~a34%3Dno~a35%3Dno~a36%3Dyes~a37%3Dyes~a38%3Donline~a39%3Dno~a40%3DWin32~a41%3Dyes~a42%3Dno~',
    'co_partnerId' => '2',
    'siteid' => '0',
    'UsingSSL' => '1',
    'lse' => 'false',
    'lsv' => '',
    'mid' => 'AQAAAUN5crm4AAUxNDM3YjY0OTE1OS5hNjBjZTNmLjFjMjUxLmZmZjk4OTg3c+HIS1wPJELHUz7lnLpwlR0w3XA*',
    'kgver' => '1',
    'kgupg' => '1',
    'kgstate' => '',
    'omid' => '',
    'hmid' => '',
    'rhr' => 'f',
    'ru' => 'http://www.ebay.com',
    'pp' => '',
    'pa1' => '',
    'pa2' => '',
    'pa3' => '',
    'i1' => '-1',
    'pageType' => '-1',
    'rtmData' => '',
    'usid' => '2',
    'inputversion' => '7b6491591430a60ce3f1c251fff98986',
    'bUrlPrfx' => '6491591437bwwt6r',
    'rqid' => '7b6491591430a60ce3f03c80ffef2b52',
    'kgct' => '',
    'inputversion' => '2',
    'userid' => 'cosme-paradise',
    'pass' => 'uriuriorkewnfiuiu3oi93',
    'keepMeSignInOption' => '1'
);

/* Test to login cosmeparadise
$data = array(
'login[username]' => 'union.programmer@gmail.com',
'login[password]' => 'BL201107b'
);*/
foreach($data as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
    $fields_string = rtrim($fields_string,'&');
	
$cookieFile = '/home/cosmepar/cosmeparadise.com/html/customprogram/cookie.txt';
// /home/cosmepar/cosmeparadise.com/html/customprogram/cookie.txt


if (!file_exists($cookieFile) || !is_writable($cookieFile)){
            echo 'Cookie file missing or not writable.';
            die;
    }
	
//Create a curl object
$ch = curl_init();
 
//Set the useragent
//$agent = $_SERVER["HTTP_USER_AGENT"];
//curl_setopt($ch, CURLOPT_USERAGENT, $agent);
 //curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
 curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36");
 
//Set the URL
curl_setopt($ch, CURLOPT_URL, $login_url );
 
/////Set refer URL
curl_setopt ($ch, CURLOPT_REFERER, $url);  
 
/////Set header
curl_setopt($ch, CURLOPT_HEADER, 1);    

//This is a POST query
curl_setopt($ch, CURLOPT_POST, true );
 
//Set the post data
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
 
//We want the content after the query
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
//Follow Location redirects
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
 
/*
Set the cookie storing files
Cookie files are necessary since we are logging and session data needs to be saved
*/
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);

//Execute the action to login
$postResult = curl_exec($ch);
$info = curl_getinfo($ch);

if ($postResult === FALSE) {
    print("CURL failed: " . curl_error($ch) . "\n");
}

echo $postResult;
echo "<br>Info:";
print_r($info);
?>