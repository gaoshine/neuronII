<?php
require('db.php');
require('utils.php');
require('header.php');
?>
<div  data-role="page">
<div  data-role="header">
	<h1>神经元网络平台</h1>
</div>
<div data-role="content">
<?php 

$action = $_REQUEST['action'];
$id = $_REQUEST['id'];
$type= $_REQUEST['type'];

switch ($action) {
	case 'test':
		echo datapointnow();
		break;
	case 'dashboard':
		dashboard($id,$type);
		//dashboard($id,'humidity');
		
		break;
	case 'dashboardnow':
		dashboardnow($id,$type);
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
