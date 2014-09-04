<?php

$str = '<div class="shipping-fee">AU$1,298.00</div>';
preg_match('/<div class="shipping-fee">AU\$(.*)<\/div>/i', $str, $shipping_cost);
echo "Shipping cost:".$shipping_cost[1]."--77<br>";

//echo $arr[0]."---".$arr[1]."---".$arr[2];

?>