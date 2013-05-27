<?php
$website = 'localhost/~gaoshine/neuronII';
$mysql_db = "neuron";
$mysql_user = "root";
$mysql_pass = "kingstar";
$mysql_link = mysql_connect("localhost", $mysql_user, $mysql_pass);
mysql_query("set names utf8"); 
mysql_select_db($mysql_db, $mysql_link);
?>
