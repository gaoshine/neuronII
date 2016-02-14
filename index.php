<?php
require('db.php');
require('utils.php');
require('header.php');
?>
<div  data-role="page">
<div  data-role="header">
	<h1> POI APP</h1>
</div>
<div data-role="content">
<?php 

$action = $_REQUEST['action'];
$id = $_REQUEST['id'];
$type= $_REQUEST['type'];

switch ($action) {
	case 'test1':
		echo datapointnow();
		break;
	case 'dashboard':
		dashboard($id,$type);
		//dashboard($id,'humidity');
		break;
	case 'dashboardnow':
		dashboardnow($id,$type);
		break;
	case 'adduser':
		addOpp($_REQUEST['person'],$_REQUEST['contact'],$_REQUEST['description']);
		break;
	case 'useradd':	
 		showOneOpp(-1);
		break;
	case 'userlist':
		showOpps();
		break;
	
	default:
		home();
}

?>
</div>
<?php
if ($action!='read') {
	require('footer.php');
}
?>
</div>
</div>

</body>
</html>
