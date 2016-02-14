<?php


	function datapointAdd($value)
	{
		global $mysql_link;
		 
		$ip = $_SERVER["REMOTE_ADDR"];
		$temp = getallheaders();
		$apikey = $temp['U-ApiKey'];
		$json=json_decode($value);
		//$sql = "INSERT INTO datapoint (timestamp, value, API,ip) VALUES (now(), '$value', '$apikey','$ip')";
		echo var_dump($json) . "\n";
		$type =  $json->{'type'};
		if ($type == 'TH') {
			$temperature = $json->{'temperature'};
			$humidity = $json->{'humidity'};
			$sql = "INSERT INTO datapoint (timestamp, value,type, API,ip) VALUES (now(), '$temperature','count', '$apikey','$ip')";
			$sql = $sql . "," . "(now(), '$humidity','inout', '$apikey','$ip')";
			echo $sql;
		}
		if ($type == 'PO') {
			$temperature = $json->{'temperature'};
			$humidity = $json->{'humidity'};
			$sql = "INSERT INTO datapoint (timestamp, value,type, API,ip) VALUES (now(), '$temperature','point1', '$apikey','$ip')";
			//$sql = $sql . "," . "(now(), '$humidity','humidity', '$apikey','$ip')";
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
		$sql ="SELECT * FROM `datapoint` WHERE  timestamp  >  DATE_SUB(NOW(),INTERVAL 100 SECOND)   order by timestamp ASC  limit 500  ";

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
		$sql ="SELECT HOUR(timestamp), count(*) FROM `datapoint` WHERE type='inout' GROUP BY HOUR(timestamp)";
		$result = mysql_query($sql,$mysql_link);
		$myData = array(0,0,0,0,0,0,0,0,0,0);

		// $myData = "";
		if(mysql_num_rows($result))
		{
			while($row = mysql_fetch_row($result)) {
				$i = (int)($row[0] - 8);
				if ($i >= 0 && $i < 10){
					$myData[$i] = (int)$row[1];
				}
			}
		}
		print('<script src="lib/libraries/RGraph.common.core.js"></script>' . "\n");
		print('<script src="lib/libraries/RGraph.common.tooltips.js"></script>' . "\n");
		print('<script src="lib/libraries/RGraph.common.effects.js"></script>' . "\n");
		print('<script src="lib/libraries/RGraph.common.dynamic.js"></script>' . "\n");
		print('<script src="lib/libraries/RGraph.line.js"></script>' . "\n\n");

		print('<canvas id="'.$type.'" width="300" height="250">[No canvas support]</canvas>' . "\n\n");
		print('<script>' . "\n");
		print('    var data = ' . json_encode($myData) . ';' . "\n\n");
		print('    var line = new RGraph.Line("'.$type.'", data);' . "\n");
		print('    line.Set("labels", ["8H","9H","10H","11H","12H","13H","14H","15H","16H","17H"])' . "\n" );
		print('    line.Set("chart.shadow", true)' . "\n" );
		print('    line.Draw();' . "\n");
		print('</script>');

		print "<div  class=\"ui-bar\">";
		print "<a href=\"index.html\" data-role=\"button\" data-icon=\"grid\">1d</a>";
		print "<a href=\"index.html\" data-role=\"button\" data-icon=\"grid\">1w</a>";
		print "<a href=\"index.html\" data-role=\"button\" data-icon=\"grid\">1m</a>";
		print "<a href=\"index.html\" data-role=\"button\" data-icon=\"grid\">1y</a></div>";

		return "SUCCESS";
	}


	function dashboardnow($id,$type) {
		global $mysql_link;
		global $website;
		$sql = "SELECT case when value between 0 AND 2 then '1:<3' when value between 3 AND 30 then '2:3-30'  when value > 30 then '3:>30' end name, count(*) FROM `datapoint` WHERE type='count' GROUP BY case when value between 0 AND 2 then '1:<3' when value between 3 AND 30 then '2:3-30'  when value > 30 then '3:>30' end";
		//$sql ="SELECT * FROM `datapoint` WHERE type='$type' and   timestamp >  DATE_SUB(NOW(),INTERVAL 30  SECOND)   order by timestamp ASC  limit 100  ";
		$result = mysql_query($sql,$mysql_link);

		$myData = array();
		$myLabels = array();
		if(mysql_num_rows($result))
		{
			while($row = mysql_fetch_row($result)) {
				//$ret = "{\"timestamp\":\"".$row[0]."\",\"value\":\"".$row[1]."\",\"apikey\":\"".$row[2]."\",\"ip\":\"".$row[3]."\"}";
					
				array_push($myData, $row[1]);
				array_push($myLabels, $row[0]);

			}
		}
		$sum = 0;
		for($i = 0 ,$size = sizeof($myData); $i < $size; ++$i){
	    	$sum = $sum + (int)$myData[$i];
		}
		$chartData = array();
		for($i = 0 ,$size = sizeof($myData); $i < $size; ++$i){
			array_push($chartData, $myData[$i] * 100 / $sum);
			$myLabels[$i] = $myLabels[$i]."(".($myData[$i] * 100 / $sum).")";
		}
		//	echo $myData;
		//	echo $myTime;
		print('<script src="lib/libraries/RGraph.common.core.js"></script>' . "\n");
		print('<script src="lib/libraries/RGraph.common.tooltips.js"></script>' . "\n");
		print('<script src="lib/libraries/RGraph.common.effects.js"></script>' . "\n");
		print('<script src="lib/libraries/RGraph.common.dynamic.js"></script>' . "\n");
		print('<script>ISOLD=RGraph.isOld();</script>' . "\n");
		print('<script src="lib/libraries/RGraph.pie.js"></script>' . "\n\n");

		print('<canvas id="myCanvasTag" width="300" height="300">[No canvas support]</canvas>' . "\n\n");
		print('<script>' . "\n");
		print ('   RGraph.Clear(document.getElementById("myCanvasTag"));'."\n");
		print('function draw3DPie(data, labels) {' . "\n");
		print('	var pie1 = new RGraph.Pie("myCanvasTag", data);' . "\n");
		print('        pie1.Set("tooltips", labels);' . "\n");
	    print('        pie1.Set("labels", labels);' . "\n");
	    print('        pie1.Set("strokestyle", "white");' . "\n");
	    print('        pie1.Set("linewidth", 5);' . "\n");
	    print('        pie1.Set("shadow", true);' . "\n");
	    print('        pie1.Set("shadow.blur", 10);' . "\n");
	    print('        pie1.Set("shadow.offsetx", 0);' . "\n");
	    print('        pie1.Set("shadow.offsety", 0);' . "\n");
	    print('        pie1.Set("shadow.color", "#000");' . "\n");
	    print('        pie1.Set("text.color", "#999");' . "\n");
	    print('	pie1.Draw();' . "\n");
		print('}' . "\n");
		print('    var data = ' . json_encode($chartData) . ';' . "\n\n");
		print('    var labels = ' . json_encode($myLabels) . ';' . "\n\n");
		print('    draw3DPie(data, labels);'. "\n\n");
		print('</script>');


		return "SUCCESS";
	}


	function datapointnow($id,$type) {
		global $mysql_link;
		$sql = "SELECT case when value between 0 AND 2 then '<3' when value between 3 AND 30 then '3-30'  when value > 30 then '>30' end name, count(*) FROM `datapoint` WHERE type='$type' GROUP BY case when value between 0 AND 3 then '<3' when value between 3 AND 30 then '3-30'  when value > 30 then '>30' end";
		//$sql ="SELECT * FROM `datapoint` WHERE type='$type' and   timestamp >  DATE_SUB(NOW(),INTERVAL 30  SECOND)   order by timestamp ASC  limit 100  ";
		$result = mysql_query($sql,$mysql_link);

		$myData = array();
		if(mysql_num_rows($result))
		{
			$myLabels = array();
			$row = mysql_fetch_row($result);
			while($row = mysql_fetch_row($result)) {
				//$ret = "{\"timestamp\":\"".$row[0]."\",\"value\":\"".$row[1]."\",\"apikey\":\"".$row[2]."\",\"ip\":\"".$row[3]."\"}";
					
				array_push($myData, $row[1]);
				array_push($myLabels, $row[0]);

			}
		}

		if ($myLabels!=NULL){
			$ret = "{\n\"status\":\""."SUCCESS"."\",\n\"value\":\"".json_encode($myData)."\",\n\"lables\":\"".json_encode($myLabels)."\"}";

		}
		else{
			$ret = "{\"status\":\""."FAIL"."\",\"value\":\""."0"."\",\"labels\":\""."0"."\"}";
		}

		return $ret;
	}


	function getCount() {
		global $mysql_link;
		
		$sql ="SELECT value  FROM  datapoint  where type='count'   order by timestamp desc  limit 1  ";
		$result = mysql_query($sql,$mysql_link);
		
		if(mysql_num_rows($result))
		{
			while($row = mysql_fetch_row($result)) {
				$myTime=$row[0];
		      
			}
		}

		$sql ="SELECT sum(value)  FROM  datapoint  where type='inout'   order by timestamp desc   ";
		$result = mysql_query($sql,$mysql_link);
		
		if(mysql_num_rows($result))
		{
			while($row = mysql_fetch_row($result)) {
				$myAll=$row[0];
				 
			}
		}
		
		$sql ="SELECT sum(value)  FROM  datapoint  where type='point1'   order by timestamp desc    ";
		$result = mysql_query($sql,$mysql_link);
		
		if(mysql_num_rows($result))
		{
			while($row = mysql_fetch_row($result)) {
				$myPOI=$row[0];
		
			}
		}
		
		$sql ="SELECT sum(value)  FROM  datapoint  where type='point1' and value>2  order by timestamp desc    ";
		$result = mysql_query($sql,$mysql_link);
		
		if(mysql_num_rows($result))
		{
			while($row = mysql_fetch_row($result)) {
				$myPOI1=$row[0];
		
			}
		}	
		
		$sql ="select count(*) as mcount from opportunities   ";
		$result = mysql_query($sql,$mysql_link);
		
		if(mysql_num_rows($result))
		{
			while($row = mysql_fetch_row($result)) {
				$mycount=$row[0];
		
			}
		}
			

		if ($myTime!=NULL){
			$ret = "{\n\"status\":\""."SUCCESS"."\",\n\"value\":\"".$myTime."\",\n\"timestamp\":\"".$myAll."\",\n\"myPOI\":\"".$myPOI."\",\n\"myPOI1\":\"".$myPOI1."\",\n\"mycount\":\"".$mycount."\"}";

		}
		else{
			$ret = "{\"status\":\""."FAIL"."\",\"value\":\""."0"."\",\"timestamp\":\""."0"."\"}";
		}

		return $ret;
	}




	function home()
	{
		global $mysql_link;
		global $website;

		print("<ul data-role=\"listview\" >");
		print("<li data-role=\"list-divider\"><a id=\"mnue01\" rel=\"external\"   data-ajax=\"false\"  href=\"?action=dashboard&type=temperature\" >");
		print("<img src=\"img/iphone/Graph.png\"    height=\"72\" width=\"72\" />");
		print("<span>客流量</span>");
		print("<span id='countSpan' class=\"ui-li-count\"></span>");
		
		
		print("</a></li>\n");


		print("<li data-role=\"list-divider\"><a id=\"mnue02\" rel=\"external\"   data-ajax=\"false\"  href=\"?action=dashboardnow&type=temperature\" >");
		print("<img src=\"img/iphone/Trash_Empty.png\"    height=\"72\" width=\"72\" />");
		print("<span>POI(001)</span>");
		print("<span id='countSpan1' class=\"ui-li-count\"></span>");
		print("</a></li>\n");

		print("<li data-role=\"list-divider\"><a id=\"mnue03\"  rel=\"external\"   data-ajax=\"false\"  href=\"?action=userlist\" >");
		print("<img src=\"img/iphone/Finder.png\"    height=\"72\" width=\"72\" />");
		print("<span>潜在客户</span>");
		print("<span id='countSpan2' class=\"ui-li-count\"></span>");
		print("</a></li>\n");
		print("</ul>");
		print('<script>' . "\n");
		print('function updateCount() {'. "\n");
		print('$.post("http://');
		print($website);
		print('/api.php?action=getCount", {}, function (data) {'. "\n");
		print('var obj = $.parseJSON(data);'. "\n");
		print('	$("#countSpan").text("人数" + obj["value"] + "人  累计" + obj["timestamp"] );'. "\n");
		print('	$("#countSpan1").text("经过" + obj["myPOI"] + "人  感兴趣" + obj["myPOI1"] );'. "\n");
		print('	$("#countSpan2").text("新增" + obj["mycount"] );'. "\n");
		print('});'. "\n");
		print('};'. "\n");
		print('setInterval("updateCount()", 1000);'. "\n");
		print('</script>');
	}



	function showOneOpp($id)
	{
		global $mysql_link;

		$COL_OPPID= 0;
		$COL_PERSON= 1;
		$COL_CONTACT= 2;
		$COL_DESCRIPTION= 3;

		$person = "";
		$contact = "";
		$description = "";

		if ($id != -1) {
			$sql ="select * from opportunities where opp_id = " . $id;
			$result = mysql_query($sql,$mysql_link);
			 
			if(mysql_num_rows($result)) {
				$row = mysql_fetch_row($result);
				$person = $row[$COL_PERSON];
				$contact = $row[$COL_CONTACT];
				$description = $row[$COL_DESCRIPTION];
			}
			print("<a rel=\"external\" href=\"javascript:deleteEntry($id)\">删除记录</a>");
		}

		print("<form method=\"post\" rel=\"external\" action=\"index.php?action=adduser\" >");
		print("<input type=\"hidden\" name=\"action\" value=\"adduser\"/>");
		print("<input type=\"hidden\" name=\"id\" value=\"$id\"/>");
		print("<fieldset>");

		print("<div data-role=\"fieldcontain\">");
		print("<label for=\"person\">人员</label>");
		print("<input type=\"text\" name=\"person\" maxlength=\"100\" id=\"person\" value=\"$person\" />");
		print("</div>");

		print("<div data-role=\"fieldcontain\">");
		print("<label for=\"contact\">联系信息</label>");
		print("<input type=\"text\" name=\"contact\" maxlength=\"100\" id=\"contact\" value=\"$contact\" />");
		print("</div>");

		print("<div data-role=\"fieldcontain\">");
		print("<label for=\"description\">详细描述</label>");
		print("<input type=\"text\" name=\"description\" maxlength=\"100\" id=\"description\" value=\"$description\" />");
		print("</div>");

		print("<fieldset>");
		print("<button type=\"submit\" value=\"Save\">保存记录</button>");

		print("</form>\n");

	}


	function addOpp($person,$contact,$description)
	{
		global $mysql_link;

		$sql = "insert opportunities(opp_id,opp_person,opp_contact,opp_description) values (NULL,'$person','$contact','$description')";
		$result = mysql_query($sql,$mysql_link);
		if ($result == 1) {
			return "SUCCESS";
		} else {
			return "FAILED";
		}

	}


	function showOpps()
	{
		global $mysql_link;

		$COL_OPPID= 0;
		$COL_PERSON= 1;
		$COL_CONTACT= 2;
		$COL_DESCRIPTION= 3;
		$sql ="select * from opportunities";
		$result = mysql_query($sql,$mysql_link);
		 
		if(mysql_num_rows($result))
		{
			//print("<a data-rel=\"dialog\" data-transition=\"pop\" href=\"index.php?action=addnew\">新增记录</a><br/><br/>");
			print("<ul data-role=\"listview\" data-filter=\"true\">");
			while($row = mysql_fetch_row($result)) {
				print("<li data-ibm-jquery-contact=\"".$row[$COL_CONTACT]."\">");
				print("<a data-rel=\"dialog\" data-transition=\"pop\" href=\"index.php?action=details&id=".$row[$COL_OPPID]."\">");
				print("人员:&nbsp;".$row[$COL_PERSON]."<br/>");
				print("联系方式:&nbsp;".$row[$COL_CONTACT]."<br/>");
				print("具体信息:&nbsp;".$row[$COL_DESCRIPTION]);
				print("</a>");

				print("</li>\n");
			}
			print("</ul>");
		}
	}

/*
JSONP PHP实现跨域的ajax数据处理
*/   
	function uploaddata()

	{
		$data = '{ "status":"SUCCESS!", "value":"3", "timestamp":"2408", "myPOI":"", "myPOI1":"", "mycount":"3"}';
		$callback = $_GET['callback'];
		$mjson = $_GET['mjson'];
		$data = $_GET['myjson'];
		print  $callback.'('.json_encode($data).')';
	}
?>
