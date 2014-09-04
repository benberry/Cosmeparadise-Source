<?php
date_default_timezone_set('Asia/Hong_Kong');
$time_string = "2014-06-20T03:32:11.000Z";
$date = new DateTime($time_string);
$date->add(new DateInterval('PT8H'));
$new_Date = $date->format('Y-m-d H:i:s');
echo $new_Date . "<br>";

//echo date($time_string);
//echo "<br>";
//echo date(DateTime::ISO8601);
//echo "<br>";

$date1 = new DateTime("now");
//$new_Date1 = $date1->format('c');
$new_Date1 = $date1->format('Y-m-d H:i:s');
echo $new_Date1;
echo "<br>";

if($new_Date1 > $new_Date)
	echo "date1 bigger date ";
else
	echo "date1 smaller date ";

if(strtotime($new_Date) < strtotime($new_Date1))
	echo "date1 bigger date ";
else
	echo "date1 smaller date ";

$interval = date_diff($date, $date1);
echo $interval->format('%R%a days');
?>