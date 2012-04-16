<?php
$server = "<mysql server>";
$username = "<mysql username>";
$password = "<mysql password>";
$db = "<mysql database>";
$cxn = mysql_connect($server,$username,$password);
mysql_select_db($db,$cxn);

$apiurl = "<RefugeesUnited API URL>";
$apikey = "<RefugeesUnited API Key>";
?>