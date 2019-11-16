<?php
try {
	$access = new DOMDocument();
	$access->load("../../access.xml");
	$host = $access->getElementsByTagName("host")->item(0)->nodeValue;
	$user = $access->getElementsByTagName("user")->item(0)->nodeValue;
	$pass = $access->getElementsByTagName("password")->item(0)->nodeValue;
	$db = $access->getElementsByTagName("schema")->item(0)->nodeValue;
	$auth = $access->getElementsByTagName("passkey")->item(0)->nodeValue;
	$table = "rssi";
	
	$mysqli = new mysqli($host, $user, $pass, $db);
	if ($mysqli->connect_error) {
		die ("output.add(\"Database connection failed: " .$mysqli->connect_error. "\");" 
				."output.timestamp();"
				."$(\"#rssSubmit\").addClass(\"btn-warning\").removeClass(\"btn-secondary\").html(\"<b>SQL Error :(</b>\");"
				."testmenu.resetRss();");
	}
		
	if(isset($_POST["device"]) && isset($_POST["rssi"]) && isset($_POST["date"]) && isset($_POST["time"])) {
		if (!isset($_POST["auth"]) || ($_POST["auth"] != $auth)) {
			die ("output.add(\"Passkey missing or invalid\");" 
				."output.timestamp();"
				."$(\"#rssSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>No Auth >:(</b>\");"
				."testmenu.resetRss();");
		}
		$device = $mysqli->real_escape_string($_POST["device"]);
		$rssi = $mysqli->real_escape_string($_POST["rssi"]);
		$date = $mysqli->real_escape_string($_POST["date"]);
		$time = $mysqli->real_escape_string($_POST["time"]);
		if(!preg_match("/^[0-9]+$/", $device)) {
			die("output.add(\"Device ID invalid format: (" .$device. ")\");" 
				."output.timestamp();"
				."$(\"#rssSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad Value!</b>\");"
				."testmenu.resetRss();");
		}
		if(!preg_match("/^\-?[0-9]+$/", $rssi)) {
			die("output.add(\"RSSI value invalid format: (" .$rssi. ")\");" 
				."output.timestamp();"
				."$(\"#rssSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad Value!</b>\");"
				."testmenu.resetRss();");
		}
		if(!preg_match("/^[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/", $time)) {
			die("output.add(\"Time value invalid format: (" .$time. ")\");" 
				."output.timestamp();"
				."$(\"#rssSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad Value!</b>\");"
				."testmenu.resetRss();");
		}
		if($timestamp = strtotime(str_replace('-', '/', $date))) {
			$date = date('Y-m-d', $timestamp);
			$datetime = $date. " " .$time;
		} else {
			die("output.add(\"Date value invalid format: (" .$date. ")\");" 
				."output.timestamp();"
				."$(\"#rssSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad Value!</b>\");"
				."testmenu.resetRss();");
		}
		
		if(!$mysqli->query("INSERT INTO " .$table. "(device, scan_update, rssi) VALUES ("
			.$device. ",\"" .$datetime. "\",\"" .$rssi. "\")")) {
			echo "output.add(\"MySQL Error: " .$mysqli->error. "\");" 
				."output.timestamp();";
			echo "$(\"#rssSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>SQL Error :(</b>\");"
				."testmenu.resetRss();";
		} else {
			echo "output.add(\"VALUES (" .$device. ", " .$datetime. ", " .$rssi. ") added to TABLE: " .$table. ".\");"
				."output.timestamp();";
			echo "$(\"#rssSubmit\").addClass(\"btn-success\").removeClass(\"btn-secondary\").html(\"<b>Success :D</b>\");"
				."testmenu.resetRss();";
		}
	} elseif(isset($_POST["id"])) {
		$id = $mysqli->real_escape_string($_POST["id"]);
		if(!preg_match("/^[0-9]+$/", $id)) {
			die("output.add(\"Device ID invalid format: (" .$id. ")\");" 
				."output.timestamp();");
		}
		$qstr = "SELECT * FROM " .$db. "." .$table. " WHERE device = \"" .$id. "\"" ;
		if(!($result = $mysqli->query($qstr))) {
			echo "<script>output.add(\"MySQL Error: " .$mysqli->error. "\");" 
				."output.timestamp() ;</script>" ;
		} else {
			$i = 0;
			echo "<ul class=\"list-group\">";
			while ($row = $result->fetch_assoc()) {
				echo "<li class=\"list-group-item list-group-flush list-group-item-action\" value=\"" .$row["id"]. "\">" .$row["scan_update"]. " " .$row["rssi"]. " dBm</li>";
				$i++ ;
			}
			echo "</ul>";
			echo "<script>output.add(\"" .$i. " values loaded.\");" 
				."output.timestamp();</script>" ;
			echo "<script>$(\"#collapseRss div ul li\").hover( function() { $(this).addClass(\"list-group-item-danger\").removeClass(\"\"); },"
					."function() { $(this).addClass(\"\").removeClass(\"list-group-item-danger\"); })"
					.".click( function() {"
					."let item = $(this);"
					."item.addClass(\"disabled\");"
					."setTimeout(function(){item.removeClass(\"disabled\");}, 500);"
					."if(!item.hasClass(\"list-group-item-success\")) {"
						."item.removeClass(\"list-group-item-danger\").off(\"mouseenter mouseleave\").addClass(\"list-group-item-success\"); "
					."} else {"
						."item.removeClass(\"list-group-item-success\").addClass(\"list-group-item-danger\")"
						.".hover( function() { $(this).addClass(\"list-group-item-danger\").removeClass(\"\"); },"
						."function() { $(this).addClass(\"\").removeClass(\"list-group-item-danger\"); });"
					."}});</script>";
			echo "<script>$(\"#showRss\").html(\"<span>RSSI</span><span class=\\\"badge badge-pill badge-light float-right\\\" style=\\\"margin-top: 0.25rem\\\">" .$i. "</span>\")"
				.".addClass(\"btn-danger\").removeClass(\"btn-secondary btn-outline-secondary font-weight-bold\").off(\"mouseenter mouseleave\");</script>";
			echo "<script>$(\"#collapseRss\").addClass(\"show\");</script>";
		}
		$result->free() ;
	}
	$mysqli->close();
}
catch(Exception $e) {
	die ("<script>output.add(\"[PHP Error] " .$e->getMessage(). "\");</script>");
}
?>