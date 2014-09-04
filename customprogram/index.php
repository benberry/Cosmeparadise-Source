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
if ($ip == "192.240.170.73" ) $accesslist=true;	//us.cosmeparadise.com server
if ($ip == "178.17.36.69" ) $accesslist=true;	//www.cosmeparadise.co.uk server
if ($ip == "61.93.89.10" ) $accesslist=true;	//company IP

if ($accesslist==false) 
	{
	echo $ip;
	exit;
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Cosmeparadise intranet, IP logged: <?php echo $ip?></title>
<style type="text/css">
.Title{
font-family: Arial, Helvetica, sans-serif;
font-size: 30px;
font-weight:bold;
color:#FFFFFF;
}
.tdTable{
border-collapse:collapse;
border:1px solid #cccccc;
margin:1em 1em;
}
a{
text-align:left;
text-decoration:none;
color:#000;
}
a:hover{
color:#0033CC;
text-decoration:underline;
}
.td{
text-align:left;
padding-left:10px;
border-collapse:collapse;
border:1px solid #CCCCCC;
}
.tdTitle{
padding-left:10px;
text-align:left;
background-color:pink;
font-size:18px;
font-family:Arial, Helvetica, sans-serif;
font-weight:bold;
color:#FFFFFF;
}
<!--
body {
	background-color: #464646;
	font-family:Arial, Helvetica, sans-serif;
	font-size:14px;
	color:#666666;
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
.list{
list-style-type:disc;
}
-->
</style></head>

<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center"><table width="800" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><table width="900"  height="1000" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td height="50" bgcolor="#212121" width="44">&nbsp;</td>
            <td  width="214"bgcolor="#212121">&nbsp;</td>
            <td  width="18"bgcolor="#212121" >&nbsp;</td>
            <td width="624" align="left" bgcolor="#212121" class="Title">Internal  Page</td>
          </tr>
          <tr>
            <td   colspan="4" align="center" valign="top" bgcolor="#FFFFFF"><table width="80%" border="0" cellspacing="0" cellpadding="0">
              <tr>
               
                <td align="center" valign="top" ><table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td><table width="400" border="0" cellspacing="0" cellpadding="0"  class=" tdTable">
                      <tr>
                        <td class="tdTitle">Grab Price and link</td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./grabprice_auto_p1.php">Part 1</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./grabprice_auto_p2.php">Part 2</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./grablink.php">myshopping link</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="http://www.analystsupporter.com/grabpriceprogram/ebaygrabprice_au.php">eBay AU Grab Price</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="http://www.analystsupporter.com/grabpriceprogram/ebaygrabprice.php">eBay US Grab Price</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="http://www.analystsupporter.com/grabpriceprogram/ebaygrabprice_uk.php">eBay UK Grab Price</a></td>
                      </tr>
                    </table></td>
                  
                    <td width="400"><table width="400" border="0" cellspacing="0" cellpadding="0"  class=" tdTable">
                      <tr>
                        <td class="tdTitle">Order & Shipping Tools</td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./Import_Tracking_Direct.php">CSV Import Tracking Number </a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./abandon_cart_email_open_record.php">Show abandon cart record</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./abandon_cart.php">Abandon cart program(Don't run manually)</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./customer_review.php">Customer Review(Don't run manually)</a></td>
                      </tr>
                    </table></td>
                  </tr>
                 
                  <tr>
                    <td><table width="400" border="0" cellspacing="0" cellpadding="0"  class=" tdTable">
                      <tr>
                        <td class="tdTitle">SBN</td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./SBN_order_API.php">security check order to API</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./SBN_order_API.php?process=check">check API order(For Berry)</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./SBN_order_record.php">SBN order record</a></td>
					  </tr>
                      <tr>
                        <td class="td"><a href="./SBN_order_shipment_update.php">Update shipping(Don't run manually)</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./SBNproducts.php">Update products price & status(Don't run manually)</a></td>
                      </tr>
					  <tr>
                        <td class="td"><a href="./SBN_order_API_direct.php?process=GOAPI&order_number=&SBN_productID=">Create order to SBN through link directly</a></td>
					  </tr>
					  <tr>
                        <td class="td"><a href="./SBNemailfilter.php">Email Black list record</a></td>
					  </tr>
                    </table></td>
                   
                    <td><table width="400" border="0" cellspacing="0" cellpadding="0"  class=" tdTable">
                      <tr>
                        <td class="tdTitle">Product Tools</td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./updateMSRP.php">Update MSRP to 1.2 with conditions(Ask before run)</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./ProductGridToHTML.php">EDM generator</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./EDMloadDeal.php">EDM load From daily deal</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./add_upsell_to_product.php">Upsell(Don't run manually)</a></td>
                      </tr>  
                      <tr>
                        <td class="td"><a href="./add_crossselll_to_product.php">Crosssell(Don't run manually)</a></td>
                      </tr>     
                      <tr>
                        <td class="td"><a href="./product_sku_switcher.php">Product sku/type switcher</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./sku_from_category.php">Get sku from category</a></td>
                      </tr> 
                      <tr>
                        <td class="td"><a href="http://www.cosmeparadise.com/update_prices.php">Update price (Delete after run)</a></td>
                      </tr>  
                      <tr>
                        <td class="td"><a href="http://www.cosmeparadise.com/update_stocks.php">Update Manage Stock (Delete after run)</a></td>
                      </tr>  
                      <tr>
                        <td class="td"><a href="http://www.cosmeparadise.com/update_noncheck_stocks.php">Update non-check Stock (Delete after run)</a></td>
                      </tr>        
                    </table></td>
                  </tr>
                  <tr>
                    <td><table width="400" border="0" cellspacing="0" cellpadding="0"  class=" tdTable">
                      <tr>
                        <td class="tdTitle">Reindex & ClearCache</td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./Price_Auto_Reindex.php">Price Reindex</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./Auto_Reindex_For_All.php">Reindex ALL(Don't run manually)</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./cleanAllCache.php">Clear All Cache in Website</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./server_reindex_reference.php">Server Reindex Reference</a></td>
                      </tr>                     
                    </table></td>
                    <td><table width="400" border="0" cellspacing="0" cellpadding="0"  class=" tdTable">
                      <tr>
                        <td class="tdTitle">DataFeed</td>
                      </tr>   
                      <tr>
                        <td class="td"><a href="../datafeed/cosmeparadisedatafeed.php">Cosme Datafeed Generate programme (Need 10mins) <br>Will be auto updated at 8PM</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="../datafeed/cosmeparadise.txt">Cosme Datafeed txt</a></td>
                      </tr>
                     
                    </table></td>
                  </tr>
                  <tr>
                    <td><table width="400" border="0" cellspacing="0" cellpadding="0"  class=" tdTable">
                      <tr>
                        <td class="tdTitle">Product Format change</td>
                      </tr>
                      <tr>
                        <td class="td"><a href="../datafeed/Product_To_Magento.php">Products To Union Dropship Magento</a></td>
                      </tr>                          
                    </table></td>
                    <td><table width="400" border="0" cellspacing="0" cellpadding="0"  class=" tdTable">
                      <tr>
                        <td class="tdTitle">eBay Tools</td>
                      </tr>
                      <tr>
                        <td class="td"><a href="http://us.cosmeparadise.com/customprogram/eBayStockCheck.php">eBayStockCheck</a></td>
                      </tr>
                      <tr>
                        <td class="td"><a href="http://www.cosmeparadise.com/customprogram/stockncost.php">Stock and Cost</a></td>
                      </tr>   
                      <tr>
                        <td class="td"><a href="http://www.cosmeparadise.com/customprogram/cosmeticebay/GetItem/ShowStoreActiveItem.php">eBay Stock & Price control</a></td>
                      </tr>                     
                    </table></td>
                  </tr>  
                  <tr>
                    <td><table width="400" border="0" cellspacing="0" cellpadding="0"  class=" tdTable">
                      <tr>
                        <td class="tdTitle">Web order Report</td>
                      </tr>
                      <tr>
                        <td class="td"><a href="./web_order_saled.php">Order Items Saled By date range</a></td>
                      </tr>                          
                    </table></td>
                    <td><table width="400" border="0" cellspacing="0" cellpadding="0"  class=" tdTable">
                      <tr>
                        <td class="tdTitle">empty</td>
                      </tr>
                      <tr>
                        <td class="td"><a href="#"></a></td>
                      </tr>                                          
                    </table></td>
                  </tr>              
                </table></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
</table>
</body>
</html>

