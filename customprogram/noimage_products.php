<?php
echo "start <br>";
error_reporting(E_ALL);
ini_set('display_errors', '1');
# Path to your Magento installation
define('MAGENTO', realpath('/home/cosmepar/cosmeparadise.com/html/'));
require_once(MAGENTO . '/app/Mage.php');
$app = Mage::app();
//this builds a collection that's analagous to 
//select * from products where image = 'no_selection'
$products = Mage::getModel('catalog/product')
->getCollection()
->addAttributeToSelect('*')
->addAttributeToFilter('image', 'no_selection');

foreach($products as $product)
{
    echo  $product->getSku() . " has no image \n<br />\n";
    //var_dump($product->getData()); //uncomment to see all product attributes
                                     //remove ->addAttributeToFilter('image', 'no_selection');
                                     //from above to see all images and get an idea of
                                     //the things you may query for
}   

echo "finish <br>";
?>