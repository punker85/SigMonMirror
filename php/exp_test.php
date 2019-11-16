<?php
try {
	$access = new DOMDocument();
	$access->load("../../access.xml");
	$host = $access->getElementsByTagName("host")->item(0)->nodeValue;
	$user = $access->getElementsByTagName("user")->item(0)->nodeValue;
	$pass = $access->getElementsByTagName("password")->item(0)->nodeValue;
	$db = $access->getElementsByTagName("schema")->item(0)->nodeValue;
	$auth = $access->getElementsByTagName("passkey")->item(0)->nodeValue;
	$table = "experiment" ;
	
	$mysqli = new mysqli($host, $user, $pass, $db) ;
	if ($mysqli->connect_error) {
		die ("output.add(\"Database connection failed: " .$mysqli->connect_error. "\");" 
				."output.timestamp();"
				."$(\"#expSubmit\").addClass(\"btn-warning\").removeClass(\"btn-secondary\").html(\"<b>SQL Error :(</b>\");"
				."testmenu.resetExp();");
	}
	
	if(isset($_POST["scanner"]) && isset($_POST["place"]) && isset($_POST["date"]) && isset($_POST["time"]) && isset($_POST["lat"]) && isset($_POST["lng"])) {
		if (!isset($_POST["auth"]) || ($_POST["auth"] != $auth)) {
			die ("output.add(\"Passkey missing or invalid\");" 
				."output.timestamp();"
				."$(\"#expSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>No Auth >:(</b>\");"
				."testmenu.resetExp();");
		}
		$scanner = $mysqli->real_escape_string($_POST["scanner"]);
		$place = $mysqli->real_escape_string($_POST["place"]);
		$date = $mysqli->real_escape_string($_POST["date"]);
		$time = $mysqli->real_escape_string($_POST["time"]);
		$lat = $mysqli->real_escape_string($_POST["lat"]);
		$lng = $mysqli->real_escape_string($_POST["lng"]);
		if(!preg_match("/^[0-9]+$/", $scanner)) {
			die("output.add(\"Scanner ID invalid format: (" .$scanner. ")\");" 
				."output.timestamp();"
				."$(\"#expSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad Value!</b>\");"
				."testmenu.resetExp();");
		}
		if(!preg_match("/^[0-9a-z\040]+$/i", $place)) {
			die("output.add(\"Location name invalid format: (" .$place. ")\");" 
				."output.timestamp();"
				."$(\"#expSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad Value!</b>\");"
				."testmenu.resetExp();");
		}
		if(!preg_match("/^[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/", $time)) {
			die("output.add(\"Time value invalid format: (" .$time. ")\");" 
				."output.timestamp();"
				."$(\"#expSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad Value!</b>\");"
				."testmenu.resetExp();");
		}
		if($timestamp = strtotime(str_replace('-', '/', $date))) {
			$date = date('Y-m-d', $timestamp);
			$datetime = $date. " " .$time;
		} else {
			die("output.add(\"Date value invalid format: (" .$date. ")\");" 
				."output.timestamp();"
				."$(\"#expSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad Value!</b>\");"
				."testmenu.resetExp();");
		}
		if(!preg_match("/^\-?1?[0-9]?[0-9]\.[0-9]{2,12}$/", $lat)) {
			die("output.add(\"Lat value invalid format: (" .$lat. ")\");" 
				."output.timestamp();"
				."$(\"#expSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad Value!</b>\");"
				."testmenu.resetExp();");
		}
		if(!preg_match("/^\-?1?[0-9]?[0-9]\.[0-9]{2,12}$/", $lng)) {
			die("output.add(\"Lng value invalid format: (" .$lng. ")\");" 
				."output.timestamp();"
				."$(\"#expSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>Bad Value!</b>\");"
				."testmenu.resetExp();");
		}
		
		if(!$mysqli->query("INSERT INTO " .$table. "(scanner, time_start, location, lat, lng) VALUES ("
			.$scanner. ",\"" . $datetime . "\",\"" .$place. "\"," .$lat. "," .$lng. ")")) {
			echo "output.add(\"MySQL Error: " .$mysqli->error. "\");" 
				."output.timestamp() ;" ;
			echo "$(\"#expSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>SQL Error :(</b>\");"
				."testmenu.resetExp();" ;
		} else {
			echo "output.add(\"VALUES (" .$place. ", " .$datetime. ") added to TABLE: " .$table. ".\") ;"
				."output.timestamp() ;" ;
			echo "$(\"#expSubmit\").addClass(\"btn-success\").removeClass(\"btn-secondary\").html(\"<b>Success :D</b>\");"
				."testmenu.resetExp();" ;
		}
	} elseif(isset($_POST["all"])) {
		$qstr = "SELECT * FROM " .$db. "." .$table;
		if(!($result = $mysqli->query($qstr))) {
			echo "<script>output.add(\"MySQL Error: " .$mysqli->error. "\");" 
				."output.timestamp() ;</script>";
		} else {
			$i = 0;
			echo "<ul class=\"list-group\">";
			while ($row = $result->fetch_assoc()) {
				echo "<li class=\"list-group-item list-group-flush list-group-item-action\" value=\"" .$i. "\">" .$row["location"]. "</li>";
				echo "<script>nodes.add(new Node(map," .$row["id"]. ",\"" .$row["location"]. "\",\"" .$row["lat"]. "\",\"" .$row["lng"]. "\"));</script>" ;
				$i++ ;
			}
			echo "</ul>";
			echo "<script>output.add(\"" .$i. " experiments loaded.\");" 
				."output.timestamp();</script>" ;
			echo "<script>$(\"#collapseExp div ul li\").hover( function() { $(this).addClass(\"list-group-item-danger\").removeClass(\"\"); },"
					."function() { $(this).addClass(\"\").removeClass(\"list-group-item-danger\"); })"
					.".click( function() {"
					."let item = $(this);"
					."item.addClass(\"disabled\");"
					."let node = nodes.get(item.val());"
					."node.draw();"
					."node.marker.addListener(\"click\", function(e) {"
						."output.push();output.add(\"Loading Devices:\");"
						."var formData = {\"id\" : node.id};"
						."$.ajax({url : \"./php/dev_test.php\","
							."type: \"POST\","
							."data: formData,"
							."dataType: \"html\","
							."success: function (data) {"
								."$(\"#collapseDev div\").html(data);},"
							."error: function (jXHR, textStatus, errorThrown) {"
								."alert(errorThrown);}"
					."});});"
					."setTimeout(function(){item.removeClass(\"disabled\");}, 1300);"
					."if(!$(this).hasClass(\"list-group-item-success\")) {"
						."$(this).removeClass(\"list-group-item-danger\").off(\"mouseenter mouseleave\").addClass(\"list-group-item-success\"); "
					."} else {"
						."$(this).removeClass(\"list-group-item-success\").addClass(\"list-group-item-danger\")"
						.".hover( function() { $(this).addClass(\"list-group-item-danger\").removeClass(\"\"); },"
						."function() { $(this).addClass(\"\").removeClass(\"list-group-item-danger\"); });"
					."}});</script>";
			echo "<script>$(\"#showExp\").html(\"<span>Experiments</span><span class=\\\"badge badge-pill badge-light float-right\\\" style=\\\"margin-top: 0.25rem\\\">" .$i. "</span>\");</script>";
		}
		$result->free() ;
	}
	$mysqli->close() ;
}
catch(Exception $e) {
	die ("<script>output.add(\"[PHP Error] " .$e->getMessage(). "\");</script>");
}
?>