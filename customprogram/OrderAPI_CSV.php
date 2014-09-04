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
<h2>OrderAPI_CSV</h2>
<b>Please keep the format: "Title", "Quantity", "SKU", "Order ID", "Shipping Addr 1", "Shipping Addr 2", "Shipping City", "Shipping Region", "Shipping Postal Code", "Shipping Country", "Buyer First Name", "Buyer Last Name", "Buyer Company", "Buyer Day Phone", "Shipping Region Name", "Brand"
</b>
<form action="OrderAPI_CSV.php" method="post" enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /><br />
Dropshiper email<input type="text" name="email" id="email" size="50"/><br />
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

/////////////echo as XML///////////
//header('Content-Type: text/xml'); 

if(($_POST["email"]) == "")
{	echo "missing Dropshiper email.";
	exit;		
}else
	$uid = $_POST["email"];

echo "start<br>";	
if (($handle = fopen($csvfile, "r")) !== FALSE ) {	//OPEN CSV
echo "open CSV<Br>";
require_once '../app/Mage.php';
Mage::app();
$row=1;
while (($data = fgetcsv($handle)) !== FALSE) {	//go through data	
echo trim($data[0])."<br>";
if($row > 1)
{
$firstname = trim($data[10]);
$middlename = "";
$lastname = trim($data[11]);
$companyname = trim($data[12]);
$address = trim($data[4])." ".trim($data[5]);
$countrycode = trim($data[9]);
$state = trim($data[14]);
$city = trim($data[6]);
$postalcode = trim($data[8]);
$tel = trim($data[13]);
$fax = "";
$sku = trim($data[2]);
$quantity = trim($data[1]);
$comment = "";
$affiliateino = trim($data[3]);

echo $address."--".$countrycode."--state:".$state."--city:".$city."--sku:".$sku."<br>";

if($address == "" || $countrycode == "" || $state == "" || $city == "")
{
	echo "order:".$affiliateino.' Fail! Address not complete.<br>';
	continue;
}

if($postalcode == "")
{
	echo "order:".$affiliateino.' Fail! Postal code missing.<br>';
	continue;
}

if($sku == "")
{
	echo "order:".$affiliateino.' Fail! No product selected.<br>';
	continue;
}


$default_shipping = 0;
if($default_shipping == "1")
{	$shipping_method = "flatrate_flatrate";
	$shipping_description = "Express Shipping";
	$shippingprice = 30;
}else
{	$shipping_method = "freeshipping_freeshipping";
	$shipping_description = "Free Shipping";	
	$shippingprice = 0;
}


//$id=829; // get Customer Id
//$customer = Mage::getModel('customer/customer')->load($id);
$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($uid);
//echo $customer->getStoreId();
if($customer->getEmail() == "" && $customer->getEmail() == null)
{
	echo "order:".$affiliateino.' Fail! Invalid UID.<br>';
	continue;
}

$transaction = Mage::getModel('core/resource_transaction');
//$storeId = $customer->getStoreId();
/////////////set Store ID to 1 get get normal incrementId///////////
$storeId = 1;
$reservedOrderId = Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($storeId);

$order = Mage::getModel('sales/order')
->setIncrementId($reservedOrderId)
->setStoreId($storeId)
->setQuoteId(0)
->setGlobal_currency_code('USD')
->setBase_currency_code('USD')
->setStore_currency_code('USD')
->setOrder_currency_code('USD');

// set discount
$GroupDiscount = 1;
if($customer->getGroupId() == 4)
	$GroupDiscount = 1;//0.97;
	
// set Customer data
$order->setCustomer_email($customer->getEmail())
->setCustomerFirstname($customer->getFirstname())
->setCustomerLastname($customer->getLastname())
->setCustomerGroupId($customer->getGroupId())
->setCustomer_is_guest(0)
->setCustomer($customer);

// set Billing Address
$billing = $customer->getDefaultBillingAddress();
$billingAddress = Mage::getModel('sales/order_address')
->setStoreId($storeId)
->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
->setCustomerId($customer->getId())
->setFirstname($firstname)
->setMiddlename($middlename)
->setLastname($lastname)
->setCompany($companyname)
->setStreet($address)
->setCity($city)
->setCountry_id($countrycode)
->setRegion($state)
->setPostcode($postalcode)
->setTelephone($tel)
->setFax($fax);
$order->setBillingAddress($billingAddress);

$shipping = $customer->getDefaultShippingAddress();
$shippingAddress = Mage::getModel('sales/order_address')
->setStoreId($storeId)
->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
->setCustomerId($customer->getId())
->setFirstname($firstname)
->setMiddlename($middlename)
->setLastname($lastname)
->setCompany($companyname)
->setStreet($address)
->setCity($city)
->setCountry_id($countrycode)
->setRegion($state)
->setPostcode($postalcode)
->setTelephone($tel)
->setFax($fax);

$order->setShippingAddress($shippingAddress)
->setShipping_method($shipping_method)
->setShippingDescription($shipping_description);

$orderPayment = Mage::getModel('sales/order_payment')
->setStoreId($storeId)
->setCustomerPaymentId(0)
//->setMethod('purchaseorder')
//->setMethod('checkmo')
->setMethod('banktransfer');
//->setPo_number(' - ');
//->setPo_number('103283');
$order->setPayment($orderPayment);

// let say, we have 2 products
$subTotal = 0;
$itmes_status = '';
$pass_order = true;

$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku); 
$stockstatus = "";
	if($_product)
	{	$stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();
		if($stocklevel == 0)
		{$stockstatus = 'But may not have enough stock';		
		}
		$rowTotal = $_product->getPrice() * $quantity; //$product['qty'];
		$orderItem = Mage::getModel('sales/order_item')
		->setStoreId($storeId)
		->setQuoteItemId(0)
		->setQuoteParentItemId(NULL)
		->setProductId($productId)
		->setProductType($_product->getTypeId())
		->setQtyBackordered(NULL)		
		->setTotalQtyOrdered("")
		->setQtyOrdered($quantity)
		->setName($_product->getName())
		->setSku($_product->getSku())
		->setPrice($_product->getPrice())
		->setDiscountAmount($rowTotal*(1-$GroupDiscount))
		->setBasePrice($_product->getPrice())
		->setOriginalPrice($_product->getPrice())
		->setRowTotal($rowTotal)
		->setBaseRowTotal($rowTotal);
		
		$subTotal += $rowTotal;
		$order->addItem($orderItem);		
		echo "order:".$affiliateino.' Success. '.$sku.'<br>';
		
	}else
	{	$pass_order = false;		
		echo "order:".$affiliateino.' Fail, Order will not be processed. As '.$sku.' not exist<br>';
		continue;		
	}


$GrandTotal = $subTotal*$GroupDiscount + $shippingprice;
$order->setSubtotal($subTotal)
->setBaseSubtotal($subTotal*$GroupDiscount)
->setBaseDiscountAmount($subTotal*(1-$GroupDiscount))
->setGrandTotal($GrandTotal)
->setBaseGrandTotal($GrandTotal)
->setShippingAmount($shippingprice)
->setBaseShippingAmount($shippingprice)
->setDiscountAmount($subTotal*(1-$GroupDiscount)) //Set discount for order
->setDiscountDescription('Dropship Group Discount'); //Set discount for order
//$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $comment);

if($pass_order)
{
$transaction->addObject($order);
$transaction->addCommitCallback(array($order, 'place'));
$transaction->addCommitCallback(array($order, 'save'));
$transaction->save();
///////////////////////////create invoice//////////////////////
$Invoice_Order = Mage::getModel('sales/order')->loadByIncrementId($reservedOrderId);
try {
if(!$Invoice_Order->canInvoice())
{
Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
}
  
$invoice = Mage::getModel('sales/service_order', $Invoice_Order)->prepareInvoice();
  
if (!$invoice->getTotalQty()) {
Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
}
  
$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
$invoice->addComment("Invoice Number:".$affiliateino, false);
//Or you can use
//$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
$invoice->register();
$transactionSave = Mage::getModel('core/resource_transaction')
->addObject($invoice)
->addObject($invoice->getOrder());
  
$transactionSave->save();
}
catch (Mage_Core_Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "<br>";
}
///////////////////////////End create invoice//////////////////////
////////////////Add record into database////////////////
try{
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL 
$Insert_API_Record = "INSERT INTO Dropship_API_Record VALUES (?, ?, ?, ADDDATE(NOW(), INTERVAL 12 HOUR ))";
$connection_write->query($Insert_API_Record, array($uid, $reservedOrderId, $affiliateino));
}catch(Exception $e)
{	echo 'Caught exception: ', $e->getMessage(), "<br>";}

$order->setCustomerComment("Invoice Number:".$affiliateino);
$order->setCustomerNoteNotify(true);
$order->setCustomerNote("Invoice Number:".$affiliateino);
$order->sendNewOrderEmail();
}else
	{echo "order:".$affiliateino.' Fail<br>';
	 continue;
	}
}
$row++;
}

}else
echo "can't open file <br>";
fclose($handle)
?>