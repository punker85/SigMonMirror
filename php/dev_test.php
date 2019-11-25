<?php
function tab_content_dev(mysqli &$conn, string $database, int $device) {
	$params = "{";
	$samples = 0;
	$query = "SELECT mac FROM " .$database. ".device WHERE id = " .$device;
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$conn->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$params .= "mac: \"" .$rslt->fetch_assoc()["mac"]. "\",";
	}
	$rslt->free();
	$query = "select count(*) as sum from " .$database. ".device a inner join rssi b on a.id = b.device where a.id = " .$device;
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$mysqli->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$samples = $rslt->fetch_assoc()["sum"];
		$params .= "samps: \"" .$samples. "\",";
	}
	$rslt->free();
	$query = "select TIMEDIFF(MAX(b.scan_update), MIN(b.scan_update)) AS diff from " .$database. ".device a inner join rssi b on a.id = b.device where a.id = " .$device;
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$mysqli->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$params .= "time: \"" .$rslt->fetch_assoc()["diff"]. "\",";
	}
	$rslt->free();
	$query = "select TIME_TO_SEC(TIMEDIFF(MAX(b.scan_update), MIN(b.scan_update))) AS secs from " .$database. ".device a inner join rssi b on a.id = b.device where a.id = " .$device;
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$mysqli->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$secs = $rslt->fetch_assoc()["secs"];
		if($secs == 0)
			$secs = 1;
		$params .= "spm: \"" .round($samples/$secs*60, 3). "\",";
	}
	$rslt->free();
	$query = "select MIN(b.rssi) AS low, MAX(b.rssi) AS high from " .$database. ".device a inner join rssi b on a.id = b.device where a.id = " .$device. " and b.rssi < 0";
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$mysqli->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$temp = $rslt->fetch_assoc();
		$params .= "low: \"" .$temp["low"]. "\",";
		$params .= "high: \"" .$temp["high"]. "\",";
	}
	$rslt->free();
	$query = "select AVG(b.rssi) AS mean from " .$database. ".device a inner join rssi b on a.id = b.device where a.id = " .$device. " and b.rssi < 0";
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$mysqli->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$params .= "mean: \"" .round($rslt->fetch_assoc()["mean"], 2). "\",";
	}
	$rslt->free();
	
	$rank = 0;
	$query = "select floor(count(*)/2) from " .$database. ".device a inner join rssi b on a.id = b.device "
		."where a.id = " .$device. " and b.rssi < 0";
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$conn->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$rank = $rslt->fetch_row()[0];
		$rslt->free();
		$query = "select b.rssi from " .$database. ".device a inner join rssi b on a.id = b.device "
		."where a.id = " .$device. " and b.rssi < 0 order by b.rssi asc limit " .$rank. ",1";
		if(!($rslt = $conn->query($query))) {
			echo "<script>output.add(\"MySQL Error: " .$conn->error. "\");" 
				."output.timestamp() ;</script>";
		} else {
			$median = $rslt->fetch_row()[0];
			$params .= "median: \"" .$median. "\"";
		}
	}
	$rslt->free();
	
	$params .= "}";
    return $params;
}

try {
	$access = new DOMDocument();
	$access->load("../../access.xml");
	$host = $access->getElementsByTagName("host")->item(0)->nodeValue;
	$user = $access->getElementsByTagName("user")->item(0)->nodeValue;
	$pass = $access->getElementsByTagName("password")->item(0)->nodeValue;
	$db = $access->getElementsByTagName("schema")->item(0)->nodeValue;
	$auth = $access->getElementsByTagName("passkey")->item(0)->nodeValue;
	$table = "device" ;
	
	$mysqli = new mysqli($host, $user, $pass, $db) ;
	if ($mysqli->connect_error) {
		die ("output.add(\"Database connection failed: " .$mysqli->connect_error. "\");" 
				."output.timestamp();"
				."$(\"#devSubmit\").addClass(\"btn-warning\").removeClass(\"btn-secondary\").html(\"<b>SQL Error :(</b>\");"
				."testmenu.resetDev();");
	}
		
	if(isset($_POST["experiment"]) && isset($_POST["mac"]) && isset($_POST["date"]) && isset($_POST["time"])) {
		if (!isset($_POST["auth"]) || ($_POST["auth"] != $auth)) {
			die ("output.add(\"Passkey missing or invalid\");" 
				."output.timestamp();"
				."$(\"#devSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>No Auth >:(</b>\");"
				."testmenu.resetDev();");
		}
		$experiment = $mysqli->real_escape_string($_POST["experiment"]);
		$mac = $mysqli->real_escape_string($_POST["mac"]);
		$date = $mysqli->real_escape_string($_POST["date"]);
		$time = $mysqli->real_escape_string($_POST["time"]);
		if(!preg_match("/^[0-9]+$/", $experiment)) {
			die("output.add(\"Experiment ID invalid format: (" .$experiment. ")\");" 
				."output.timestamp();"
				."$(\"#devSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad value!</b>\");"
				."testmenu.resetDev();");
		}
		if(!preg_match("/^[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}$/i", $mac)) {
			die("output.add(\"MAC address invalid format: (" .$mac. ")\");" 
				."output.timestamp();"
				."$(\"#devSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad value!</b>\");"
				."testmenu.resetDev();");
		}
		if(!preg_match("/^[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/", $time)) {
			die("output.add(\"Time value invalid format: (" .$time. ")\");" 
				."output.timestamp();"
				."$(\"#devSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad value!</b>\");"
				."testmenu.resetDev();");
		}
		if($timestamp = strtotime(str_replace('-', '/', $date))) {
			$date = date('Y-m-d', $timestamp);
			$datetime = $date. " " .$time;
		} else {
			die("output.add(\"Date value invalid format: (" .$date. ")\");" 
				."output.timestamp();"
				."$(\"#devSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad value!</b>\");"
				."testmenu.resetDev();");
		}		
		
		if(!$mysqli->query("INSERT INTO " .$table. "(experiment, discovery, mac) VALUES ("
			.$experiment. ",\"" .$datetime. "\",\"" .$mac. "\")")) {
			echo "output.add(\"MySQL Error: " .$mysqli->error. "\");" 
				."output.timestamp();";
			echo "$(\"#devSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>SQL Error :(</b>\");"
				."testmenu.resetDev();" ;
		} else {
			echo "output.add(\"VALUES (" .$experiment. ", " .$datetime. ", " .$mac. ") added to TABLE: " .$table. ".\") ;"
				."output.timestamp();" ;
			echo "$(\"#devSubmit\").addClass(\"btn-success\").removeClass(\"btn-secondary\").html(\"<b>Success :D</b>\");"
				."testmenu.resetDev();" ;
		}
	} elseif(isset($_POST["id"])) {
		$id = $mysqli->real_escape_string($_POST["id"]);
		if(!preg_match("/^[0-9]+$/", $id)) {
			die("output.add(\"Experiment ID invalid format: (" .$id. ")\");" 
				."output.timestamp();");
		}
		$qstr = "SELECT * FROM " .$db. "." .$table. " WHERE experiment = \"" .$id. "\"" ;
		if(!($result = $mysqli->query($qstr))) {
			echo "<script>output.add(\"MySQL Error: " .$mysqli->error. "\");" 
				."output.timestamp() ;</script>" ;
		} else {
			$i = 0;
			echo "<ul class=\"list-group\">";
			echo "<script>devices = {};</script>";
			while ($row = $result->fetch_assoc()) {
				echo "<li class=\"list-group-item list-group-flush list-group-item-action\" value=\"" .$row["id"]. "\">" .$row["mac"]. "</li>";
				echo "<script>devices[" .$row["id"]. "] = " .tab_content_dev($mysqli, $db, $row["id"]). "</script>";
				$i++ ;
			}
			echo "</ul>";
			echo "<script>output.add(\"" .$i. " devices loaded.\");" 
				."output.timestamp();</script>" ;
			echo "<script>$(\"#collapseDev div ul li\").hover( function() { $(this).addClass(\"list-group-item-danger\").removeClass(\"\"); },"
					."function() { $(this).addClass(\"\").removeClass(\"list-group-item-danger\"); })"
					.".click( function() {"
					."let item = $(this);"
					."item.addClass(\"disabled\");"
					."output.push();output.add(\"Loading RSSI Values:\");"
					."var formData = {\"id\" : $(this).val()};"
					."cpanel.add(\"device\", devices[formData.id]);"
					."cpanel.select(\"device\");"
					."$.ajax({url : \"./php/rss_test.php\","
						."type: \"POST\","
						."data: formData,"
						."dataType: \"html\","
						."success: function (data) {"
							."$(\"#collapseRss div\").html(data);},"
						."error: function (jXHR, textStatus, errorThrown) {"
							."alert(errorThrown);}"
					."});"
					."setTimeout(function(){item.removeClass(\"disabled\");}, 500);"
					."if(!item.hasClass(\"list-group-item-success\")) {"
						."item.removeClass(\"list-group-item-danger\").off(\"mouseenter mouseleave\").addClass(\"list-group-item-success\"); "
					."} else {"
						."item.removeClass(\"list-group-item-success\").addClass(\"list-group-item-danger\")"
						.".hover( function() { $(this).addClass(\"list-group-item-danger\").removeClass(\"\"); },"
						."function() { $(this).addClass(\"\").removeClass(\"list-group-item-danger\"); });"
					."}});</script>";
			echo "<script>$(\"#showDev\").html(\"<span>Devices</span><span class=\\\"badge badge-pill badge-light float-right\\\" style=\\\"margin-top: 0.25rem\\\">" .$i. "</span>\")"
				.".addClass(\"btn-danger\").removeClass(\"btn-secondary btn-outline-secondary font-weight-bold\").off(\"mouseenter mouseleave\");</script>";
			echo "<script>$(\"#collapseDev\").addClass(\"show\");</script>";
		}
		$result->free() ;
	}
	$mysqli->close() ;
}
catch(Exception $e) {
	die ("<script>output.add(\"[PHP Error] " .$e->getMessage(). "\");</script>");
}
?>