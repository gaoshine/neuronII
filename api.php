<?php
require('db.php');
require('utils.php');
 
$action = $_REQUEST['action'];
$id = $_REQUEST['id'];
$value = $_REQUEST['value'];
$type = $_REQUEST['type'];
switch ($action) {
	case 'datapointAdd':
		datapointAdd($_REQUEST['value']); 
		break;
	case 'datapointnow':
		echo datapointnow($id,$type); 
		break;		
	default:
		home();
}
?>
