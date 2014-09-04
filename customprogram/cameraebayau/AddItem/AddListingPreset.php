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
if ($ip == "192.240.170.73" ) $accesslist=true;	//us.cosmeparadise.com server
if ($ip == "178.17.36.69" ) $accesslist=true;	//www.cosmeparadise.co.uk server
if ($ip == "61.93.89.10" ) $accesslist=true;	//company IP

if ($accesslist==false) 
	{
	echo $ip;
	exit;
	}

/////////////////////////start session//////////////////////////////////
session_start(); 
$product_array = array();
		

	/* Connect to a cosmepar_custom MySQL server */
	$con = mysqli_connect(
	'localhost', /* The host to connect to */
	'cosmepar_program', /* The user to connect as */
	'RecapsBoronWhirlGrands45', /* The password to use */
	'cosmepar_custom'); /* The default database to query */
	
	if (!$con) {
	printf("Can't connect to cosmepar_custom MySQL Server. Errorcode: %s\n", mysqli_connect_error());
	exit;
	}
	
	
require_once '../xajax-0.6-beta1/xajax_core/xajax.inc.php'; 	
$xajax = new xajax();
$xajax->register(XAJAX_FUNCTION, "nextCategory");
$xajax->register(XAJAX_FUNCTION, "FVF");
$xajax->processRequest();
$xajax->configure('javascript URI','../xajax-0.6-beta1/');
///////////////Start the XAJAX CATEGORY code lines to prepare the requests.///////////		
	// This function is called by the JavaScript to gather the necessary information.
	function FVF($ecatid, $price, $CurrencyRate, $TotalCost, $TempShipping) {
		/* Connect to a cosmepar_custom MySQL server */
		$con = mysqli_connect(
		'localhost', /* The host to connect to */
		'cosmepar_program', /* The user to connect as */
		'RecapsBoronWhirlGrands45', /* The password to use */
		'cosmepar_custom'); /* The default database to query */
		
		if (!$con) {
		printf("Can't connect to cosmepar_custom MySQL Server. Errorcode: %s\n", mysqli_connect_error());
		exit;
		}
		
		if($ecatid != "")	//////////check if category has selected
		{
			$sql = "select DiscountRate from ebay_categories where CategoryID = $ecatid";
			list($DiscountRate) = mysqli_fetch_row(mysqli_query($con, $sql));
			$newcost = round(($TotalCost+$TempShipping)/$CurrencyRate,2);
			$normal_fee_rate = 6;
			$new_fee_rate = 0;
			
			if($DiscountRate == -2)
			{	if($newcost >= 200)
					$new_fee_rate = ($normal_fee_rate+$DiscountRate)/100;
				else
					$new_fee_rate = $normal_fee_rate/100;
			}
			else
				$new_fee_rate = ($normal_fee_rate+$DiscountRate)/100;				
			
			$BreakEvenPrice = round($newcost/(1-$new_fee_rate),2);
			$Final_Value_Fee = round($BreakEvenPrice*$new_fee_rate,2);
			if($Final_Value_Fee> 250)
			{	$Final_Value_Fee = 250;
				$BreakEvenPrice = $Final_Value_Fee + $newcost;
			}
			
			if($price > 0)
			{	$Final_Value_Fee = $price * $new_fee_rate;
				if($Final_Value_Fee> 250)
					$Final_Value_Fee = 250;
			}
			
			
			$objResponse = new xajaxResponse();
			$objResponse->assign("FVF","value", $Final_Value_Fee);		
			$objResponse->assign("BreakEvenPrice","value", $BreakEvenPrice);		
			return $objResponse;//->getXML();
			
		}else{
			$objResponse = new xajaxResponse();
			$objResponse->assign("FVF","value", "Select Category first");		
			return $objResponse;//->getXML();
		
		}
		
	}
	
	function nextCategory($ecatid, $box) {
		/* Connect to a cosmepar_custom MySQL server */
		$con = mysqli_connect(
		'localhost', /* The host to connect to */
		'cosmepar_program', /* The user to connect as */
		'RecapsBoronWhirlGrands45', /* The password to use */
		'cosmepar_custom'); /* The default database to query */
		
		if (!$con) {
		printf("Can't connect to cosmepar_custom MySQL Server. Errorcode: %s\n", mysqli_connect_error());
		exit;
		}
		//ecatid: category ID selected in the box.
		//box: this is the box that the category was selected in.
		global $shipconn;
		list($j, $boxnum) = explode("_", $box);
		$boxnum++; //increment the box to the next box.
		$newContent = "";
		
		// If this is a leaf category, popuplate the category field instead of a new box.
		$sql = "select LeafCategory from ebay_categories where CategoryID = $ecatid";
		list($leaf) = mysqli_fetch_row(mysqli_query($con, $sql));
		
		if ($leaf) {
			$objResponse = new xajaxResponse();
			$objResponse->assign("FinalCat","value", $ecatid);
			for ($i = $boxnum; $i < 7; $i++) {  //This resets all other boxes up to the 6th one in case one clicks on a previous box.
			$objResponse->assign("catbox_".$i,"innerHTML", "");
			$objResponse->assign("boxnum_".$i,"innerHTML", "");
			}    
			return $objResponse;//->getXML();
		}
		
		// selecting the necessary information based on the Category that is being sent to the function.
		$sql = "select CategoryID, CategoryName, LeafCategory
				from ebay_categories where Expired = 0 and Virtual = 0 and CategoryParentID = $ecatid
				and CategoryParentID != CategoryID order by CategoryName ASC";
		$getcats = mysqli_query($con, $sql);
		$getcats_count = mysqli_num_rows($getcats);
		if ($getcats_count) {  //if there are categories in here, create the selection box to be populated in the table field.
			$newContent .= "<select name=\"catbox_".$boxnum."\" "
						. "onChange=\"xajax_nextCategory(this[this.selectedIndex].value, 'catbox_".$boxnum."')\" "
						. "size=\"8\" style=\"width: 240px; height: 250px;\">\n";
			while (list($CategoryID, $CategoryName, $LeafCategory) = mysqli_fetch_row($getcats)) {  //loop through and make options
				$newContent .= "<option value=\"$CategoryID\">$CategoryName".(($LeafCategory)?"":"-->")."</option>\n";
			}
			$newContent .= "</select>\n";
		}
		
		//$newContent = htmlspecialchars($newContent);
		$objResponse = new xajaxResponse();
		//$objResponse->alert( "getcats_count:".$getcats_count );
		// add a command to the response to assign the innerHTML attribute of
		// the element with id="SomeElementId" to whatever the new content is
		$objResponse->assign("catbox_".$boxnum,"innerHTML", $newContent); //assign the selection box.
		$objResponse->assign("boxnum_".$boxnum,"innerHTML", "<b>".$boxnum.".</b>"); //assign the box number.
		for ($i = ($boxnum +1); $i < 7; $i++) {  //This resets all other boxes up to the 6th one in case one clicks on a previous box.
			$objResponse->assign("catbox_".$i,"innerHTML", "");
			$objResponse->assign("boxnum_".$i,"innerHTML", "");
		}    
		
		//return the XML response generated by the xajaxResponse object
		return $objResponse;//->getXML();
	
		/*$objResponse = new xajaxResponse();
		$objResponse->addEvent('catbox_1', "onclick", 'alert(\''.$ecatid.'\');');
		return $objResponse;*/	
	}
	
	//show categories for the first box.
	$sql = "SELECT CategoryID, CategoryName, LeafCategory FROM ebay_categories where Expired = 0 and Virtual = 0 and CategoryParentID = CategoryID ORDER BY CategoryName ASC";
	$getcats = mysqli_query($con, $sql);
	///////////////End the XAJAX CATEGORY code lines to prepare the requests.///////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>eBay Listing Preset</title>
<? $xajax->printJavascript(); //this is required for the AJAX system to work. ?>
<style>
table {border-color:black;}
.chead {background-color:grey; text-align: right;}
.chead td{padding-left:30px;}
.cgrey {background-color:#D8D8D8;padding-left:20px;padding-right:20px;}
.clightgrey {background-color:#F0F0F0;padding-left:20px;padding-right:20px;}

.dataform{width:1330px;background-color:#ffffff;}
.borderline{height:300px; border:0px solid #555;}
.dsheet-tabs{background-color:#fff;border-bottom:1px solid #555;}
.dsheet-tabs a{display: inline-block; line-height:28px;padding:0 10px;font-size:16px; color:#555;}
.dsheet-tabs a:hover{background-color:#ddd;}
.dsheet-tabs a.selected{background-color:#ddd;}
.dsheet-tabs-fix{position:fixed;top:55px;margin:0 auto;width:950px;}
star.Mandatory{color:red;}
div.dataformSS > table td{ padding: 5px 20px 5px 0;}
div.dataformSS > table td table td{ padding: 0 0 0 0;}
.selectinput{width: 178px;}
.disabled{background:#dddddd;}
</style>
<script type="text/javascript">
function limitText(limitField, limitCount, limitNum) {
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	} else {
		limitCount.value = limitNum - limitField.value.length;
	}
}

function goBack() {
    window.history.back()
}

function tabswitch(id)
{
	
	document.getElementById("section_item").style.display ="none";
	document.getElementById("section_description").style.display ="none";
	document.getElementById("section_category").style.display ="none";
	document.getElementById("section_shipping").style.display ="none";
	document.getElementById("section_submit").style.display ="none";
	document.getElementById(id).style.display ="";	  
	
	document.getElementById("section_items").className = "";
	document.getElementById("section_descriptions").className = "";
	document.getElementById("section_categorys").className = "";
	document.getElementById("section_shippings").className = "";
	document.getElementById("section_submits").className = "";
	document.getElementById(id+"s").className = "selected";
}
</script>
<!-- Make sure the path to CKEditor is correct. -->
<script src="../ckeditor_4.4.1_standard/ckeditor/ckeditor.js"></script>
</head>
<body>
<?php
	//////////////////check session/////////////////////
			if(isset($_SESSION['product_id']))
		{
			$product_array = $_SESSION['product_id'];
			echo "have session";
			//print_r($product_array);
		}else{	
			echo '<a href="../GetItem/ShowStoreActiveItem.php" target="_self">Check Current Listing</a>&nbsp;&nbsp;';
			echo '<a href="../ebaycontrol.php" target="_self">Add Combo Listing</a>';
			echo '<h3>Add Normal Listing</h3>';
			echo 'Total cost(HKD): <input type="text" value="0" id="TotalCost" name="TotalCost" />';			
		}
	//////////////////////Require eBay files/////////////////////
	require('../get-common/keys.php');
	require('../get-common/eBaySession.php');
	
	//////////////////load all items For Combination////////////////////
	$product_ids = "";
	$new_sku = "";
	if(count($product_array) > 0)
	{	
		$counting=0;
		$combination_cost = 0;
		echo '<p>Product(s) For Combination:</p>';
		
		echo '<table><tr>';		
		echo '<td><table class="chead" align="right">';		
		echo '<tr><td>No.:</td></tr>';
		echo '<tr><td height="100px">Image:</td></tr>';
		echo '<tr><td>Product ID:</td></tr>';
		echo '<tr><td height="100px">Title:</td></tr>';
		echo '<tr><td>Category:</td></tr>';
		echo '<tr><td>Brand:</td></tr>';
		echo '<tr><td>Weight:</td></tr>';
		echo '<tr><td>Cost:</td></tr>';		
		echo '</table></td>';
		$product_ids = implode(",",$product_array);
		$sql = 'SELECT * FROM `ebay_test_data` WHERE id IN ('.$product_ids.') ORDER BY id ASC;';		
		$result = mysqli_query($con, $sql);	
		while($row = mysqli_fetch_array($result))
		{	$counting++;
			$combination_cost += $row['unit_cost'];
			$new_sku .= $row['id']."+";
			if($counting%2 == 0)
				$classname = "cgrey";
			else
				$classname = "clightgrey";
			echo '<td class="'.$classname.'"><table>';
			echo '<tr><td>'.$counting.'</td></tr>';			
			echo '<tr><td><a target="_blank" href="'.$row['img'].'"><img height="100px" src="'.$row['img'].'" /></a></td></tr>';			
			echo '<tr><td>'.$row['id'].'</td></tr>';
			echo '<tr><td height="100px" width="200px" valign="top">'.$row['display_name'].'</td></tr>';
			echo '<tr><td>'.$row['category_name'].'</td></tr>';
			echo '<tr><td>'.$row['brand_name'].'</td></tr>';
			echo '<tr><td>'.$row['weight'].'</td></tr>';
			echo '<tr><td>'.$row['unit_cost'].'</td></tr>';			
			echo '</table></td>';
		}
		echo '</tr></table>';
		echo 'Total cost(HKD): <input type="text" value="'.$combination_cost.'" id="TotalCost" name="TotalCost" class="disabled" readonly />';
	
		$new_sku = substr($new_sku, 0, -1);
		if(checkSKU($new_sku) > 0)
		{	echo "Combination Exist!";
			exit;
		}else
			echo '<br>New Combination &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="../ebaycontrol.php">Back To Combo Select</a><br><br>';
	
		}
	
	?>


<div class="dataform">
<div class="dsheet-tabs">    
<a id="section_items" class="selected" onClick="tabswitch('section_item');" >Item</a>
<a id="section_descriptions" onClick="tabswitch('section_description');" >Description</a>
<a id="section_categorys" onClick="tabswitch('section_category');" >Category</a>
<a id="section_shippings" onClick="tabswitch('section_shipping');" >Shipping & Return Policy</a>
<a id="section_submits" onClick="tabswitch('section_submit');" >Submit</a>
</div>

<form action="AddComboFixedPriceItem.php" method="post" id="ListingForm" enctype="multipart/form-data">
<div class="borderline" >

<!-- Listing -->
<div class="dataformSS" id="section_item">
<table>
<tr>
<td>Title<star class="Mandatory">*</star></td><td colspan="7"><input type="text" name="Title" size="140" onKeyDown="limitText(this.form.Title,this.form.countdown,80);" onKeyUp="limitText(this.form.Title,this.form.countdown,80);"maxlength="80" required />Characters Left:<input readonly type="text" name="countdown" size="2" value="80"></td>
</tr><tr>
<td>SKU<star class="Mandatory">*</star></td><td><input type="text" name="SKU" value="<?php echo $new_sku ?>" /></td>
<td>Start Price<star class="Mandatory">*</star></td><td><input type="text" id="price" name="StartPrice" required /></td>
<td>Buy it now<star class="Mandatory">*</star></td><td><input type="text" name="now_price" value="Unavailable" class="disabled" readonly /></td>
<td>Currency<star class="Mandatory">*</star></td>
<td><select class="selectinput" name="Currency"><option value="AUD" SELECTED>Australia Dollar</option><!-- <option value="GBP">United Kingdom Pound</option><option value="USD">United States Dollar</option><option value="EUR">Euro Member Countries</option>--></td>
</tr><tr>
<td>Quantity<star class="Mandatory">*</star></td><td><input type="text" name="Quantity" required /></td>
<td>OutOfStockControl<star class="Mandatory">*</star></td><td><SELECT name="OutOfStockControl"><option value="true" selected>True</option><option value="false">False</option></td>
<td>PaymentMethods</td><td><input type="text" name="PaymentMethods" value="PayPal" class="disabled" readonly /></td>
<td>PayPalEmailAddress</td><td><input type="text" name="PayPalEmailAddress" size="24" value="payau@cameraparadise.com" class="disabled" readonly /></td>
</tr><tr>
<td>Item Country</td><td><select class="selectinput" name="Country"><option value="HK" SELECTED>Hong Kong</option></select></td>
<td>Item Location</td><td><input type="text" name="Location" value="HK" class="disabled" readonly /></td>
<td>Item Condition<star class="Mandatory">*</star></td><td>
<select class="selectinput" name="ConditionID"><option value="-1" SELECTED>-</option><option value="1000">New</option><option value="1500">New: Never Used</option><option value="1750">New with defects</option><option value="2000">Manufacturer refurbished</option><option value="2500">Seller refurbished</option><option value="3000">Used</option><option value="4000">Very Good</option><option value="5000">Good</option><option value="6000">Acceptable</option><option value="7000">For parts or not working</option></select></td>
<td>Weight(KG)</td><td><input type="text" name="Weight" value="" /></td>
</tr><tr>
<td>ListingType</td><td><select class="selectinput" name="ListingType"><!-- <option value="AUC">Auction</option>--><option value="FixedPriceItem">FixedPriceItem</option></select></td>
<td>ListingDuration</td><td><select class="selectinput" name="ListingDuration"><option value="GTC">GTC</option><option value="Days_3" SELECTED>3 Days</option><option value="Days_5">5 Days</option><option value="Days_7">7 Days</option><option value="Days_10">10 Days</option><option value="Days_14">14 Days</option><option value="Days_21">21 Days</option><option value="Days_30">30 Days</option></select></td>
<td>Brand</td><td><input type="text" name="Brand" value="" /></td>
<td>Size</td><td><input type="text" name="Volume_Size" value="" /></td>
</tr><tr>
<td>PictureURL<star class="Mandatory">*</star></td><td colspan="7"><input type="text" name="PictureURL" size="140" required /></td>
</tr><tr>
<td>Shipping(HKD)</td><td><input type="text" value="0" id="TempShipping" name="TempShipping" /></td><td><input type="button" value="Final Value Fee" onClick="xajax_FVF(document.getElementById('FinalCat').value, document.getElementById('price').value, document.getElementById('CurrencyRate').value, document.getElementById('TotalCost').value, document.getElementById('TempShipping').value)"> </a></td><td><input type="text" name="FVF" id="FVF" /></td><td>CurrencyRate</td><td><input type="text" name="CurrencyRate" id="CurrencyRate" value="7" /></td><td>BreakEvenPrice</td><td><input type="text" name="BreakEvenPrice" value="" id="BreakEvenPrice" class="disabled" readonly /></td>
</tr>
</table>
<input type="submit" name="submit" value="Check if Missing" />
</div>
<!-- END Listing-->

<!--Description-->
<div class="tab" id="section_description" style="display:none;">
<textarea rows="25" cols="163" name="Description" id="desc_editor" form="ListingForm">
<div class="paneTemplate">
<script type="text/javascript">
(function(){
      document.write('<scr' + 'ipt id="startFile" src="//static.come2list.com/require.js" data-main="//static.come2list.com/maintest.js?noblock" data-host="come2list.com" data-itemHash="86b50a11987262ee248afa51af1616e9275d814d" data-packageHash="3c331613a26f366446dd2bb9297a8b4104e340d5" data-file=["cosme_paradise/cosme_paradise.js"]></scr' + 'ipt>');
      document.write('<scr' + 'ipt src="http://www.professorparts.com/TA_Photo/cosme_paradise/SpryAssets/SpryTabbedPanels.js" ></scr' + 'ipt>');
      document.close();
})();
</script><script id="startFile" src="//static.come2list.com/require.js" data-main="//static.come2list.com/maintest.js?noblock" data-host="come2list.com" data-itemhash="86b50a11987262ee248afa51af1616e9275d814d" data-packagehash="3c331613a26f366446dd2bb9297a8b4104e340d5" data-file="[&quot;cosme_paradise/cosme_paradise.js&quot;]"></script><script src="http://www.professorparts.com/TA_Photo/cosme_paradise/SpryAssets/SpryTabbedPanels.js"></script>
<link href="http://www.professorparts.com/TA_Photo/cosme_paradise/style1n.css" rel="stylesheet" type="text/css">



<div align="center">
	<div id="pagewapper">
    	<div id="background">
        	<div id="header">
            	<div class="hl1">                	
                    <div class="logo" data-bind="with:templateSection().temp_logo">
                      <a data-bind="attr:{'href':url}" href="http://stores.ebay.com/Cosme-Paradise"><img data-bind="attr:{'src':image,'width':imageWidth,'height':imageHeight}" src="http://come2list.com/services/files/00000000000000000279/php2d9Qrd" width="201" height="33"></a>
                    </div>                    
                     <div class="topnav" data-bind="with:templateSection().temp_menu">
                          <ul data-bind="foreach:URLs">
                              <li><a data-bind="text:$data.name,attr:{'href':$data.url}" href="http://stores.ebay.com/Cosme-Paradise">Home</a></li>
                          
                              <li><a data-bind="text:$data.name,attr:{'href':$data.url}" href="http://myworld.ebay.com/cosme-paradise">About Us</a></li>
                          
                              <li><a data-bind="text:$data.name,attr:{'href':$data.url}" href="http://myworld.ebay.com/cosme-paradise">Payment</a></li>
                          
                              <li><a data-bind="text:$data.name,attr:{'href':$data.url}" href="http://myworld.ebay.com/cosme-paradise">Shipping</a></li>
                          </ul>
                      </div>
                    <!-- /ko -->
                    
                    <div class="search">
                    	<div id="searchbox">
                            <div class="search_sec">
                                            <form action="http://stores.ebay.com/Cosme-Paradise/" method="get" name="Search" id="Search" style="display: inline;">
                                                        <span class="text"><input type="text" value="" onblur="if(this.value=='')this.value='';" onclick="if(this.value=='')this.value='';" maxlength="300" size="13" name="_nkw">
                                                        </span>
                                            </form>
                            </div>
            			</div>
                    </div>
                    
                    <div style="clear:both"></div>
                </div>
                
                <div class="banner" data-bind="foreach:banner">
              	<a data-bind="attr:{'href':$data.url}" href="http://stores.ebay.com/Cosme-Paradise"><img data-bind="attr:{'src':$data.image}" src="http://come2list.com/services/files/00000000000000000279/phpjAHUBP"></a>
              	</div>
            </div>
            
            <div id="mainbody">
            	<div class="title" data-bind="text:title">Sisley Botanical Creme Moisturizer With Cucumber (Jar) 50ml</div>
                
           	  <div class="ml1">
                	<div class="photoarea">
                    	<div class="mainphoto">
                        	<img data-bind="attr:{src: currentImage}" width="450" height="450" src="http://i.ebayimg.com/00/s/NTAwWDUwMA==/z/sbkAAOxygj5SfKyA/$_1.JPG?set_id=8800005007">
                        </div>
                        
                        <div class="thumphoto">
                        	<ul data-bind="visible: prevImage.length > 1,foreach:prevImage" style="display: none;">
                            	<li><img data-bind="attr:{src: $data},event: {mouseover: $root.changeCurrent}" width="90" height="90" src="http://i.ebayimg.com/00/s/NTAwWDUwMA==/z/sbkAAOxygj5SfKyA/$_1.JPG?set_id=8800005007"></li>
                        	</ul>
                        </div>
                    </div>
                    
                    <div class="descarea">
                    	<div class="desc">
                        	<div class="dtitle">
                            	Description
                            </div>
                            
                            <div data-bind="html: htmlBox" class="htmlboxstyle"></div>
                        </div>
                        
                        <div class="spec">
                        	<div class="dtitle">
                            	Item Specification
                            </div>
                            
                            <div class="ddesc">
                            	<ul data-bind="foreach: customAttributes"></ul>
                            </div>
                        </div>
                    </div>
                    
                    <div style="clear:both"></div>
                </div>
                
                <div class="ml2">
                	<div class="promo">
                   	  <div class="news">
                        	<div class="newsin">
                        	Add my store to your favorites and receive my email newsletters about new items and special promotions!
                            </div>
                            
                            <div class="newsbtn">
                                <a href="http://my.ebay.com.au/ws/eBayISAPI.dll?AcceptSavedSeller&amp;sellerid=cosme-paradise&amp;ssPageName=STRK:MEFS:ADDSTR&amp;rt=nc">
                                    SUBMIT
                                </a>
                            </div>
                            
                        </div>
                        
                        <div class="subterm"></div>
                    </div>
                    
                    <div class="terms">
               			<div id="TabbedPanels1" class="TabbedPanels">
                    		<ul class="TabbedPanelsTabGroup">
                    				<li class="TabbedPanelsTab" tabindex="0">Shipping /</li>
                                    <li class="TabbedPanelsTab" tabindex="0">Payment /</li>
                                	<li class="TabbedPanelsTab" tabindex="0">Return /</li>
                                    <li class="TabbedPanelsTab TabbedPanelsTabSelected" tabindex="0" data-bind="text: (templateSection().temp_about || {}).selectedName">About Us / </li>
                                    <li class="TabbedPanelsTab" tabindex="0" data-bind="text: (templateSection().temp_cancel || {}).selectedName"></li>
                                    <li class="TabbedPanelsTab" tabindex="0" data-bind="text: (templateSection().temp_handling || {}).selectedName"></li>
                  		     </ul>
                    		 <div class="TabbedPanelsContentGroup">
                    		 		<div class="TabbedPanelsContent" data-bind="html: shippingTerm" style="display: none;"></div>
                                    <div class="TabbedPanelsContent" data-bind="html: paymentTerm" style="display: none;">-	We only accept credit card payment via verified Paypal.
-	Please note that paying by eCheck via PayPal can delay your shipment by up to two weeks while payment clears through the PayPal system (Check with PayPal on the expected clearance date).Once your eCheck payment clears, your item will be dispatched to you within 1 working day.</div>
                                    <div class="TabbedPanelsContent" data-bind="html: returnTerm" style="display: none;">Returns Accepted<br>
After receiving the item, your buyer should contact you within: 14 Days<br>
Refund will be given as: Money back or exchange (buyer's choice)<br>
Return shipping will be paid by: Buyer<br></div>
                                    <div class="TabbedPanelsContent TabbedPanelsContentVisible" data-bind="html: (templateSection().temp_about || {}).selectedTerm" style="display: block;">Cosme Paradise is holding by Union Group, a wholesale company since 1989, we sell mainly beauty products including skincare, fragrance, perfume, cosmetic products.</div>
                                    <div class="TabbedPanelsContent" data-bind="html: (templateSection().temp_cancel || {}).selectedTerm" style="display: none;"></div>
                                    <div class="TabbedPanelsContent" data-bind="html: (templateSection().temp_handling || {}).selectedTerm" style="display: none;"></div>
                  			 </div>
           		    	</div>
                    </div>
                    
                    <div style="clear:both"></div>
                </div>
            </div>
            
            <div id="footer">
            	<!-- ko if:templateSection -->
              	<div class="footnav" data-bind="with:templateSection().temp_menu2">
                	<ul data-bind="foreach:URLs">
                    	<li><a data-bind="text:$data.name,attr:{'href':$data.url}" href="http://stores.ebay.com/Cosme-Paradise/">Home</a></li>
                	
                    	<li><a data-bind="text:$data.name,attr:{'href':$data.url}" href="http://myworld.ebay.com/cosme-paradise">About Us</a></li>
                	
                    	<li><a data-bind="text:$data.name,attr:{'href':$data.url}" href="http://myworld.ebay.com/cosme-paradise">Payment</a></li>
                	
                    	<li><a data-bind="text:$data.name,attr:{'href':$data.url}" href="http://myworld.ebay.com/cosme-paradise">Shipping</a></li>
                	</ul>
              	</div>
              	<!-- /ko -->
                
                <div class="copyright">
                	Copyright © cosme-paradise. All right reserved. Designed by BBQ
                </div>
                
                <div style="clear:both"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
</script>
</div>
</textarea>
<script>
// Replace the <textarea id="editor1"> with a CKEditor
// instance, using default configuration.
CKEDITOR.replace('desc_editor');
</script>
</div>
<!--End Description-->

<!-- Category Selecting-->
<div class="tab" id="section_category" style="display:none;">
<table cellpadding=3 cellspacing=0>
<tr valign="top">
    <td><b>1.</b></td>
    <td id="catbox_1"><select name="catbox_1" onClick="xajax_nextCategory(this[this.selectedIndex].value, 'catbox_1')" size="8" style="width: 240px; height: 250px;"><?
        while (list($CategoryID, $CategoryName, $LeafCategory) = mysqli_fetch_row($getcats)) {
            echo "<option value=\"$CategoryID\">$CategoryName".(($LeafCategory)?"":"-->")."</option>\n";
        }
    ?></select></td>
    <td id="boxnum_2"></td>
    <td id="catbox_2"></td>
	<td id="boxnum_3"></td>
    <td id="catbox_3"></td>
    <td id="boxnum_4"></td>
    <td id="catbox_4"></td>
	<td id="boxnum_5"></td>
    <td id="catbox_5"></td>
    <td id="boxnum_6"></td>
    <td id="catbox_6"></td>
</tr>
</table>
Category: #<input type="text" id="FinalCat" name="ecatid" size="6" required /> 
</div>
<!--End Category Selecting-->

<!--Shipping & Return-->
<div class="dataformSS" id="section_shipping" style="display:none;">
<table>
<tbody>
<tr><td colspan="5">
<table><tr>
<td>Domestic Shipping</td>
	<td><select name="ShippingService"><option value="AUP_BX1_SMALL">AUP_BX1_SMALL(Australia Post Flat Rate BX1 Small Box)</option><option value="AUP_BX2_MEDIUM">AUP_BX2_MEDIUM(Australia Post Flat Rate BX2 Medium Box)</option><option value="AUP_BX4_LARGE">AUP_BX4_LARGE(Australia Post Flat Rate BX4 Large Box)</option><option value="AU_AustralianAirExpressFlatRate1kg">AU_AustralianAirExpressFlatRate1kg(Australian air Express Flat Rate 1kg)</option><option value="AU_AustralianAirExpressFlatRate3kg">AU_AustralianAirExpressFlatRate3kg(Australian air Express Flat Rate 3kg)</option><option value="AU_AustralianAirExpressFlatRate5kg">AU_AustralianAirExpressFlatRate5kg(Australian air Express Flat Rate 5kg)</option><option value="AU_AustralianAirExpressMetro15kg">AU_AustralianAirExpressMetro15kg(Australian air Express Metro 15kg)</option><option value="AU_Courier">AU_Courier(Courier)</option><option value="AU_DHL">AU_DHL(DHL)</option><option value="AU_eBayAusPost3kgFlatRateSatchel">AU_eBayAusPost3kgFlatRateSatchel(eBay AusPost 3kg Flat Rate Satchel)</option><option value="AU_eBayAusPost500gFlatRateSatchel">AU_eBayAusPost500gFlatRateSatchel(eBay AusPost 500g Flat Rate Satchel)</option><option value="AU_EconomyDeliveryFromOutsideAU">AU_EconomyDeliveryFromOutsideAU(Economy delivery from outside AU)</option><option value="AU_ExpeditedDeliveryFromOutsideAU">AU_ExpeditedDeliveryFromOutsideAU(Expedited delivery from outside AU)</option><option value="AU_expeditedShipping">AU_expeditedShipping(Expedited Shipping)</option><option value="AU_Express">AU_Express(AusPost Express Post Parcel)</option><option value="AU_ExpressDelivery">AU_ExpressDelivery(Express delivery)</option><option value="AU_ExpressPostSatchel3kg">AU_ExpressPostSatchel3kg(AusPost Express Post Satchel 3kg)</option><option value="AU_ExpressPostSatchel500g">AU_ExpressPostSatchel500g(AusPost Express Post Satchel 500g)</option><option value="AU_ExpresswithInsurance">AU_ExpresswithInsurance(Express with Insurance)</option><option value="AU_FastwayCouriers">AU_FastwayCouriers(Fastway Couriers)</option><option value="AU_Freight">AU_Freight(Freight)</option><option value="AU_Other">AU_Other(Other)</option><option value="AU_Pickup">AU_Pickup(Local Pickup)</option><option value="AU_PrePaidExpressPostPlatinum3kg">AU_PrePaidExpressPostPlatinum3kg(AusPost PrePaid Express Post Platinum 3kg)</option><option value="AU_PrePaidExpressPostPlatinum500g">AU_PrePaidExpressPostPlatinum500g(AusPost PrePaid Express Post Platinum 500g)</option><option value="AU_PrePaidExpressPostSatchel3kg">AU_PrePaidExpressPostSatchel3kg(AusPost PrePaid Express Post Satchel 3kg)</option><option value="AU_PrePaidExpressPostSatchel500g">AU_PrePaidExpressPostSatchel500g(AusPost PrePaid Express Post Satchel 500g)</option><option value="AU_PrePaidExpressPostSatchel5kg">AU_PrePaidExpressPostSatchel5kg(AusPost PrePaid Express Post Satchel 5kg)</option><option value="AU_PrePaidParcelPostSatchels3kg">AU_PrePaidParcelPostSatchels3kg(AusPost PrePaid Parcel Post Satchels 3kg)</option><option value="AU_PrePaidParcelPostSatchels500g">AU_PrePaidParcelPostSatchels500g(AusPost PrePaid Parcel Post Satchels 500g)</option><option value="AU_PrePaidParcelPostSatchels5kg">AU_PrePaidParcelPostSatchels5kg(AusPost PrePaid Parcel Post Satchels 5kg)</option><option value="AU_Registered">AU_Registered(AusPost Registered)</option><option value="AU_RegisteredParcelPost">AU_RegisteredParcelPost(AusPost Registered Parcel Post)</option><option value="AU_RegisteredParcelPostPrepaidSatchel3kg">AU_RegisteredParcelPostPrepaidSatchel3kg(AusPost Registered Parcel Post Prepaid 3kg Satchel)</option><option value="AU_RegisteredParcelPostPrepaidSatchel500g">AU_RegisteredParcelPostPrepaidSatchel500g(AusPost Registered Parcel Post Prepaid 500g Satchel)</option><option value="AU_RegisteredParcelPostPrepaidSatchel5kg">AU_RegisteredParcelPostPrepaidSatchel5kg(AusPost Registered Parcel Post Prepaid 5kg Satchel)</option><option value="AU_RegisteredSmallParcel">AU_RegisteredSmallParcel(Registered Small Parcel)</option><option value="AU_Regular">AU_Regular(AusPost Regular Parcel)</option><option value="AU_RegularParcelWithTracking">AU_RegularParcelWithTracking(AusPost Parcel with tracking)</option><option value="AU_RegularParcelWithTrackingAndSignature">AU_RegularParcelWithTrackingAndSignature(AusPost Parcel with tracking and signature)</option><option value="AU_RegularwithInsurance">AU_RegularwithInsurance(Regular with Insurance)</option><option value="AU_SmallParcels">AU_SmallParcels(Small Parcels)</option><option value="AU_SmallParcelWithTracking">AU_SmallParcelWithTracking(Small Parcel With Tracking)</option><option value="AU_SmallParcelWithTrackingAndSignature">AU_SmallParcelWithTrackingAndSignature(Small Parcel With Tracking And Signature)</option><option value="AU_StandardDelivery">AU_StandardDelivery(Standard delivery)</option><option value="AU_StandardDeliveryFromOutsideAU" SELECTED>AU_StandardDeliveryFromOutsideAU(Standard delivery from outside AU)</option><option value="AU_standardShipping">AU_standardShipping(Standard Shipping)</option><option value="AU_StarTrackExpress">AU_StarTrackExpress(Star Track Express)</option><option value="AU_TNT">AU_TNT(TNT)</option><option value="AU_TntIntlExp">AU_TntIntlExp(TNT International Express)</option><option value="AU_Toll">AU_Toll(Toll Consumer Delivery)</option></select></td>
	<td>Cost:</td>
	<td><input type="text" name="standard_shipping_cost" value="0.0" size="10" /></td>	
	<td>Each additional:</td>
	<td><input type="text" name="standard_additional_cost" value="0.0" size="10" /></td>
</tr>
<tr>
	<td>Internetional Shipping</td>
	<td><select name="InternationalShippingService"><option value="AU_AirMailInternational">AU_AirMailInternational(AusPost Air Mail Parcel)</option><option value="AU_AusPostRegisteredPostInternationalPaddedBag1kg">AU_AusPostRegisteredPostInternationalPaddedBag1kg(AusPost Registered Post International Padded Bag 1kg)</option><option value="AU_AusPostRegisteredPostInternationalPaddedBag500g">AU_AusPostRegisteredPostInternationalPaddedBag500g(AusPost Registered Post International Padded Bag 500g)</option><option value="AU_AusPostRegisteredPostInternationalParcel">AU_AusPostRegisteredPostInternationalParcel(AusPost Registered Post International Parcel)</option><option value="AU_EconomyAirInternational">AU_EconomyAirInternational(Economy Air)</option><option value="AU_EMSInternationalCourierDocuments">AU_EMSInternationalCourierDocuments(EMS International Courier - Documents)</option><option value="AU_EMSInternationalCourierParcels">AU_EMSInternationalCourierParcels(EMS International Courier - Parcels)</option><option value="AU_ExpeditedInternational">AU_ExpeditedInternational(Express International Flat Rate Postage)</option><option value="AU_ExpressCourierInternational">AU_ExpressCourierInternational(AusPost Express Courier International)</option><option value="AU_ExpressPostInternational">AU_ExpressPostInternational(AusPost Express Post International Parcel)</option><option value="AU_ExpressPostInternationalDocuments">AU_ExpressPostInternationalDocuments(Express Post International - Documents)</option><option value="AU_OtherInternational">AU_OtherInternational(Other Int'l Postage (see description))</option><option value="AU_PrePaidExpressPostInternationalBox10kg">AU_PrePaidExpressPostInternationalBox10kg(AusPost PrePaid Express Post International Box 10Kg)</option><option value="AU_PrePaidExpressPostInternationalBox20kg">AU_PrePaidExpressPostInternationalBox20kg(AusPost PrePaid Express Post International Box 20Kg)</option><option value="AU_PrePaidExpressPostInternationalBox5kg">AU_PrePaidExpressPostInternationalBox5kg(AusPost PrePaid Express Post International Box 5Kg)</option><option value="AU_PrePaidExpressPostInternationalEnvelopeB4">AU_PrePaidExpressPostInternationalEnvelopeB4(AusPost PrePaid Express Post International Envelope B4)</option><option value="AU_PrePaidExpressPostInternationalEnvelopeC5">AU_PrePaidExpressPostInternationalEnvelopeC5(AusPost PrePaid Express Post International Envelope C5)</option><option value="AU_PrePaidExpressPostInternationalSatchels2kg">AU_PrePaidExpressPostInternationalSatchels2kg(AusPost PrePaid Express Post International Satchel 2Kg)</option><option value="AU_PrePaidExpressPostInternationalSatchels3kg">AU_PrePaidExpressPostInternationalSatchels3kg(AusPost PrePaid Express Post International Satchel 3Kg)</option><option value="AU_SeaMailInternational">AU_SeaMailInternational(AusPost Sea Mail Parcel)</option><option value="AU_StandardInternational" SELECTED>AU_StandardInternational(Standard International Flat Rate Postage)</option><option value="PromotionalShippingMethod">PromotionalShippingMethod(Promotional Postage Service)</option></select></td>
	<td>Cost:</td>
	<td><input type="text" name="international_shipping_cost" value="0.0" size="10" /></td>
	<td>Each additional:</td>
	<td><input type="text" name="international_additional_cost" value="0.0" size="10" /></td>
</tr>
</table></td></tr>
<tr><td colspan="2"><b>Shipping Details:</b></td></tr>
<tr><td>Ship-To Location(Region/Country)</td>
	<td><input type="text" name="Int_ShipToLocation"  value="Worldwide" class="disabled" readonly /></td>
	<td>Excluded Ship-To Location</td>
	<td><input type="text" name="ExcludeShipToLocation" value=""></td>
	<td>(AU,UK)separate by comma</td>
</tr>
<tr><td>Handling Time</td>
	<td><select class="selectinput" name="DispatchTimeMax"><option value="0">0 Day</option><option value="1">1 Day</option><option value="2" SELECTED>2 Days</option><option value="3">3 Days</option><option value="4">4 Days</option><option value="5">5 Days</option><option value="10">10 Days</option><option value="15">15 Days</option><option value="20">20 Days</option><option value="30">30 Days</option></select></td>
	<td>May Use</td>
	<td><input name="Something" value="" class="disabled" readonly /></td>
</tr>
<tr><td colspan="2"><b>Return Policy:</b></td></tr>
<tr><td>Returns Accepted</td>
	<td><select class="selectinput" name="ReturnsAcceptedOption"><option value="ReturnsAccepted" SELECTED>Accepted</option><option value="ReturnsNotAccepted">Not Accepted</option></select></td>
	<td>Return Refund &amp; Within</td>
	<td><select class="selectinput" name="RefundOption"><option value="MoneyBack" SELECTED>MoneyBack</option><option value="MoneyBackOrExchange">MoneyBackOrExchange</option><option value="Exchange">Exchange</option></select></td>
	<td><select class="selectinput" name="ReturnsWithinOption" id="ReturnsWithinOption"><option value=""></option><option value="Days_3">3 Days</option><option value="Days_7" SELECTED>7 Days</option><option value="Days_14">14 Days</option><option value="Days_30">30 Days</option><option value="Days_60">60 Days</option><option value="Months_1">1 Month</option></select></td>
</tr>
<tr><td>Return shipping will be paid by</td>
	<td><select class="selectinput" name="ShippingCostPaidByOption" id="ShippingCostPaidByOption"><option value="Buyer" SELECTED>Buyer</option><option value="Seller">Seller</option></select></td>
	<td>Additional return policy details</td>
	<td><input type="text" name="return_policy_desc" id="return_policy_desc" value="" /></td>
</tr>
</tbody>
</table>
</div>
<!--End Shipping & Return-->

<div class="tab" id="section_submit" style="display:none;">
Confirm Submit to eBay Listing # <input type="checkbox" name="confirm_submit" value="GO" required/><br><br>
<input type="submit" name="submit" value="Submit to eBay" />
</div>

</div>
</form>
    
</div>
</body>
</html>
<?php
	
	mysqli_close($con);
	
	function checkSKU($SKU)
	{	//////////////////////Require eBay files/////////////////////
		require('../get-common/keys.php');
		//require('../get-common/eBaySession.php');
		//SiteID must also be set in the Request's XML
		//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
		//SiteID Indicates the eBay site to associate the call with
		$siteID = 15;
		//the call being made:
		$verb = 'GetSellerList';
		
		$compatabilityLevel = 549;
		$today =  date("Y-m-d");
		$onedaybefore = date( "Y-m-d", strtotime ("-1 day", strtotime($today))); 
		$fiftydayafter = date( "Y-m-d", strtotime ("+50 day", strtotime($today))); 
		//echo $onedaybefore." and ".$fiftydayafter."<br>";
		
		///Build the request Xml string
		$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestXmlBody .= '<GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
		$requestXmlBody .= '<EndTimeFrom>'.$today.'</EndTimeFrom>';
		$requestXmlBody .= '<EndTimeTo>'.$fiftydayafter.'</EndTimeTo>';
		//$requestXmlBody .= '<StartTimeTo>2014-02-04</StartTimeTo>';
		$requestXmlBody .= '<WarningLevel>High</WarningLevel>';
		$requestXmlBody .= '<ErrorLanguage>en_US</ErrorLanguage>';
		$requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
		$requestXmlBody .= '<Pagination>';
		$requestXmlBody .= '<EntriesPerPage>5</EntriesPerPage>';
		$requestXmlBody .= '<PageNumber>1</PageNumber>';
		$requestXmlBody .= '</Pagination>';
		$requestXmlBody .= '<SKUArray>';
		$requestXmlBody .= '<SKU>'.$SKU.'</SKU>';	//321404
		$requestXmlBody .= '</SKUArray>';
		$requestXmlBody .= '<OutputSelector>ItemArray.Item.Site,ItemArray.Item.SKU,ItemArray.Item.ItemID,ItemArray.Item.Title,ItemArray.Item.Quantity,ItemArray.Item.QuantityAvailableHint,ItemArray.Item.SellingStatus.QuantitySold,ItemArray.Item.Currency,ItemArray.Item.SellingStatus.CurrentPrice,ItemArray.Item.ListingDetails.EndTime</OutputSelector>';
		$requestXmlBody .= '</GetSellerListRequest>';
		
        //Create a new eBay session with all details pulled in from included keys.php
        $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
		
		//send the request and get response
		$responseXml = $session->sendHttpRequest($requestXmlBody);
		if(stristr($responseXml, 'HTTP 404') || $responseXml == '')
			die('<P>Error sending request');
		
		
		//echo $responseXml;
		$ItemID=0;
		//Xml string is parsed and creates a DOM Document object
		$responseDoc = new DomDocument();
		$responseDoc->loadXML($responseXml);
		//get any error nodes
		$errors = $responseDoc->getElementsByTagName('Errors');
		
		//if there are error nodes
		if($errors->length > 0)
		{
			echo '<P><B>eBay returned the following error(s):</B>';
			//display each error
			//Get error code, ShortMesaage and LongMessage
			$code     = $errors->item(0)->getElementsByTagName('ErrorCode');
			$shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');
			$longMsg  = $errors->item(0)->getElementsByTagName('LongMessage');
			//Display code and shortmessage
			echo '<P>', $code->item(0)->nodeValue, ' : ', str_replace(">", "&gt;", str_replace("<", "&lt;", $shortMsg->item(0)->nodeValue));
			//if there is a long message (ie ErrorLevel=1), display it
			if(count($longMsg) > 0)
				echo '<BR>', str_replace(">", "&gt;", str_replace("<", "&lt;", $longMsg->item(0)->nodeValue));
	
		} else { //no errors
			$responses = $responseDoc->getElementsByTagName("GetSellerListResponse");
			foreach ($responses as $response) {
				$ItemArray = $response->getElementsByTagName("ItemArray");
				$Items = $ItemArray->item(0)->getElementsByTagName("Item");
				
				foreach($Items as $Item) {                
					$ItemID	= $Item->getElementsByTagName("ItemID")->item(0)->nodeValue;
				}
			}
		}
		return $ItemID;
	}
?>
