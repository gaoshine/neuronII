<?php

function datapointAdd($value)
{
	global $mysql_link;

	$ip = $_SERVER["REMOTE_ADDR"];
	$temp = getallheaders();
	$apikey = $temp['U-ApiKey'];
	$json=json_decode($value);
	//$sql = "INSERT INTO datapoint (timestamp, value, API,ip) VALUES (now(), '$value', '$apikey','$ip')";
	//echo var_dump($json) . "\n";
	$type =  $json->{'type'};
	if ($type == 'TH') {
		$temperature = $json->{'temperature'};
		$humidity = $json->{'humidity'};
		$sql = "INSERT INTO datapoint (timestamp, value,type, API,ip) VALUES (now(), '$temperature','temperature', '$apikey','$ip')";
		$sql = $sql . "," . "(now(), '$humidity','humidity', '$apikey','$ip')";
		echo $sql;
	}
	
	$result = mysql_query($sql,$mysql_link);
	if ($result == 1) {
		return "SUCCESS";
	} else {
		return "FAILED";
	}
}

function datapointshow($id)
{
	global $mysql_link;

	$sql ="select * from datapoint  where 1 order by timestamp desc limit 0,30 ";
	$sql ="SELECT * FROM `datapoint` WHERE  timestamp  >  DATE_SUB(NOW(),INTERVAL 3000 SECOND)   order by timestamp ASC  limit 500  ";
	
	$result = mysql_query($sql,$mysql_link);

	if(mysql_num_rows($result))
	{
		$row = mysql_fetch_row($result);
		while($row = mysql_fetch_row($result)) {
			$ret = "{\"timestamp\":\"".$row[0]."\",\"value\":\"".$row[1]."\",\"apikey\":\"".$row[2]."\",\"ip\":\"".$row[3]."\"}";
			echo $ret;
		}
	}
}

function dashboard($id,$type) {
	global $mysql_link;
	$sql ="SELECT * FROM `datapoint` WHERE type='$type' and  timestamp  >  DATE_SUB(NOW(),INTERVAL 3000 SECOND)   order by timestamp ASC  limit 500  ";
	$result = mysql_query($sql,$mysql_link);

	$myData = "";
	if(mysql_num_rows($result))
	{
		$row = mysql_fetch_row($result);
		while($row = mysql_fetch_row($result)) {
			$ret = "{\"timestamp\":\"".$row[0]."\",\"value\":\"".$row[1]."\",\"apikey\":\"".$row[2]."\",\"ip\":\"".$row[3]."\"}";
			$myData =$myData.$row[1].",";
			$myTime = $myTime.'"'.$row[0].' - '.$row[1].'`C",';
		}
	}

	print('<script src="lib/libraries/RGraph.common.core.js"></script>' . "\n");
	print('<script src="lib/libraries/RGraph.common.tooltips.js"></script>' . "\n");
	print('<script src="lib/libraries/RGraph.common.effects.js"></script>' . "\n");
	print('<script src="lib/libraries/RGraph.common.dynamic.js"></script>' . "\n");
	print('<script src="lib/libraries/RGraph.line.js"></script>' . "\n\n");

	print('<canvas id="'.$type.'" width="600" height="200">[No canvas support]</canvas>' . "\n\n");
	print('<script>' . "\n");
	print('    var data = [' . $myData . '];' . "\n\n");
	print('    var time = [' . $myTime . '];' . "\n\n");
	print('    var line = new RGraph.Line("'.$type.'", data);' . "\n");
	print('    line.Set("chart.tooltips", time);' . "\n");
	print('    line.Set("chart.shadow", true)' . "\n" );
	print('    line.Draw();' . "\n");
	print('</script>');

	print "<div  class=\"ui-bar\">";
	print "<a href=\"index.php\" data-role=\"button\" data-icon=\"home\">10m</a>";
	print "<a href=\"?action=update\" data-role=\"button\" data-icon=\"clock\">1h</a>";
	print "<a href=\"index.html\" data-role=\"button\" data-icon=\"clock\">2h</a>";
	print "<a href=\"index.html\" data-role=\"button\" data-icon=\"grid\">1d</a>";
	print "<a href=\"index.html\" data-role=\"button\" data-icon=\"grid\">1w</a>";
	print "<a href=\"index.html\" data-role=\"button\" data-icon=\"grid\">1m</a>";
	print "<a href=\"index.html\" data-role=\"button\" data-icon=\"grid\">1y</a></div>";

	return "SUCCESS";
}


function dashboardnow($id,$type) {
	global $mysql_link;
	global $website;
	$sql ="SELECT * FROM `datapoint` WHERE type='$type' and  timestamp  >  DATE_SUB(NOW(),INTERVAL 300 SECOND)   order by timestamp ASC  limit 100  ";
	$result = mysql_query($sql,$mysql_link);

	$myData = "";
	if(mysql_num_rows($result))
	{
		$row = mysql_fetch_row($result);
		while($row = mysql_fetch_row($result)) {
			$ret = "{\"timestamp\":\"".$row[0]."\",\"value\":\"".$row[1]."\",\"apikey\":\"".$row[2]."\",\"ip\":\"".$row[3]."\"}";
			//echo $ret;
			$myData =$myData.$row[1].",";
			$myTime = $myTime.'"'.$row[0].' - '.$row[1].'`C",';
		}
	}
//	echo $myData;
//	echo $myTime;
	print('<script src="lib/libraries/RGraph.common.core.js"></script>' . "\n");
	print('<script src="lib/libraries/RGraph.common.tooltips.js"></script>' . "\n");
	print('<script src="lib/libraries/RGraph.common.effects.js"></script>' . "\n");
	print('<script src="lib/libraries/RGraph.common.dynamic.js"></script>' . "\n");
	print('<script src="lib/libraries/RGraph.line.js"></script>' . "\n\n");

	print('<canvas id="myCanvasTag" width="600" height="200">[No canvas support]</canvas>' . "\n\n");
	print('<script>' . "\n");
	print('    var data = [' . $myData . '];' . "\n\n");
	print('    var time = [' . $myTime . '];' . "\n\n");
	print('function draw() ' . "\n" . "{" . "\n");
	print('    var obj = eval("(" + this.responseText + ")");  '."\n");
	print ('   RGraph.Clear(document.getElementById("myCanvasTag"));'."\n");
	print('    var line = new RGraph.Line("myCanvasTag", data);' . "\n");
//	print('    line.Set("chart.tooltips", time);' . "\n");
	print('    line.Set("chart.shadow", true)' . "\n" );
	print('    line.Draw();' . "\n");
//    print('    var value=RGraph.random(1, 100);' . "\n");
	print('    data.push(obj.value);'."\n");
	print('    }                   ' ."\n\n");
	print('function update()    {'."\n");
	print('	RGraph.AJAX("http://');
	print( $website);
	print ('/api.php?action=datapointnow&type=temperature", draw); '."\n");
	print('	setTimeout(update, 5000);' . "\n");
	print(' }' . "\n\n");
	print('setTimeout(update, 5000);' . "\n");
	print('draw();' . "\n");
	print('</script>');


	return "SUCCESS";
}


function datapointnow($id,$type) {
	global $mysql_link;

	$sql ="SELECT * FROM `datapoint` WHERE type='$type' and   timestamp >  DATE_SUB(NOW(),INTERVAL 30  SECOND)   order by timestamp ASC  limit 100  ";
	$result = mysql_query($sql,$mysql_link);

	$myData = "";
	if(mysql_num_rows($result))
	{
		$row = mysql_fetch_row($result);
		while($row = mysql_fetch_row($result)) {
			//$ret = "{\"timestamp\":\"".$row[0]."\",\"value\":\"".$row[1]."\",\"apikey\":\"".$row[2]."\",\"ip\":\"".$row[3]."\"}";
			
			$myData = $row[1];
			$myTime = $row[0];

		}
	}
	if ($myTime!=NULL){
		$ret = "{\n\"status\":\""."SUCCESS"."\",\n\"value\":\"".$myData."\",\n\"timestamp\":\"".$myTime."\"}";
		
	}
	else{
		$ret = "{\"status\":\""."FAIL"."\",\"value\":\""."0"."\",\"timestamp\":\""."0"."\"}";
	} 
	
	return $ret;
}



function home()
{
	global $mysql_link;

	$sql ="select * from billboard ";
	$result = mysql_query($sql,$mysql_link);

	print("<ul data-role=\"listview\" >");
	print("<li data-role=\"list-divider\"><a id=\"mnue01\" rel=\"external\"   data-ajax=\"false\"  href=\"?action=dashboard&type=temperature\" >");
	print("<img src=\"img/iphone/Graph.png\"    height=\"72\" width=\"72\" />");
	print("<span>我的仪表盘</span>");
	print("<span class=\"ui-li-count\">".mysql_num_rows($result)."</span>");
	print("</a></li>\n");

	if(mysql_num_rows($result))
	{
		while($row = mysql_fetch_row($result)) {
			print("<li><a  href=\"?action=read&id=".$row[0]."\" ><h3>".$row[1]."</h3></a>");
			print("<p>".$row[2]."</p>");
			print("<p class=\"ui-li-aside\"><strong>".$row[7]."  </strong>".$row[8]."</p></li>\n");
		}
	}

	print("<li data-role=\"list-divider\"><a id=\"mnue02\" rel=\"external\"   data-ajax=\"false\"  href=\"?action=dashboardnow&type=temperature\" >");
	print("<img src=\"img/iphone/Trash_Empty.png\"    height=\"72\" width=\"72\" />");
	print("<span>实时监控</span>");
	print("</a></li>\n");

	print("<li data-role=\"list-divider\"><a id=\"mnue03\"  rel=\"external\"   data-ajax=\"false\"  href= >");
	print("<img src=\"img/iphone/Finder.png\"    height=\"72\" width=\"72\" />");
	print("<span>我的设置</span>");
	print("</a></li>\n");
	print("</ul>");
}

?>
