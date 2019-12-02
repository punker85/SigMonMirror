<?php
function tab_content_exp(mysqli &$conn, string $database, int $experiment) {
	$params = "{";
	$samples = 0;
	$query = "SELECT location FROM " .$database. ".experiment WHERE id = " .$experiment;
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$conn->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$params .= "name: \"" .$rslt->fetch_assoc()["location"]. "\",";
	}
	$rslt->free();
	$query = "SELECT COUNT(*) as num FROM " .$database. ".device WHERE experiment = " .$experiment;
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$conn->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$params .= "devs: \"" .$rslt->fetch_assoc()["num"]. "\",";
	}
	$rslt->free();
	$query = "select count(*) as sum from " .$database. ".experiment a inner join device b on a.id = b.experiment inner join rssi c on b.id = c.device where a.id = " .$experiment;
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$conn->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$samples = $rslt->fetch_assoc()["sum"];
		$params .= "samps: \"" .$samples. "\",";
	}
	$rslt->free();
	$query = "select TIMEDIFF(MAX(c.scan_update), MIN(c.scan_update)) AS diff from " .$database. ".experiment a inner join device b on a.id = b.experiment inner join rssi c on b.id = c.device where a.id = " .$experiment;
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$conn->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$params .= "time: \"" .$rslt->fetch_assoc()["diff"]. "\",";
	}
	$rslt->free();
	$query = "select TIME_TO_SEC(TIMEDIFF(MAX(c.scan_update), MIN(c.scan_update))) AS secs from " .$database. ".experiment a inner join device b on a.id = b.experiment inner join rssi c on b.id = c.device where a.id = " .$experiment;
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$conn->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$secs = $rslt->fetch_assoc()["secs"];
		if($secs == 0)
			$secs = 1;
		$params .= "sps: \"" .round($samples/$secs, 3). "\",";
	}
	$rslt->free();
	$query = "select MIN(c.rssi) AS low, MAX(c.rssi) AS high from " .$database. ".experiment a inner join device b on a.id = b.experiment inner join rssi c on b.id = c.device where a.id = " .$experiment. " and c.rssi < 0";
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$conn->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$temp = $rslt->fetch_assoc();
		$params .= "low: \"" .$temp["low"]. "\",";
		$params .= "high: \"" .$temp["high"]. "\",";
	}
	$rslt->free();
	$query = "select AVG(c.rssi) AS mean from " .$database. ".experiment a inner join device b on a.id = b.experiment inner join rssi c on b.id = c.device where a.id = " .$experiment. " and c.rssi < 0";
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$conn->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$params .= "mean: \"" .round($rslt->fetch_assoc()["mean"], 2). "\",";
	}
	$rslt->free();
	
	$rank = 0;
	$query = "select floor(count(*)/2) from " .$database. ".experiment a inner join device b on a.id = b.experiment inner join rssi c on b.id = c.device "
		."where a.id = " .$experiment. " and c.rssi < 0";
	if(!($rslt = $conn->query($query))) {
		echo "<script>output.add(\"MySQL Error: " .$conn->error. "\");" 
			."output.timestamp() ;</script>";
	} else {
		$rank = $rslt->fetch_row()[0];
		$rslt->free();
		$query = "select c.rssi from " .$database. ".experiment a inner join device b on a.id = b.experiment inner join rssi c on b.id = c.device "
		."where a.id = " .$experiment. " and c.rssi < 0 order by c.rssi asc limit " .$rank. ",1";
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
		echo "<ul class=\"list-group\">"; 
		$tri_exps = array();
		$qstr = "SELECT triplet.id as id, exp1, exp2, exp3, a.location as aloc, a.lat as alat, a.lng as alng, b.location as bloc, b.lat as blat, b.lng as blng, c.location as cloc, c.lat as clat, c.lng as clng FROM " .$db. ".triplet"
			." inner join " .$table. " a on triplet.exp1 = a.id"
			." inner join " .$table. " b on triplet.exp2 = b.id"
			." inner join " .$table. " c on triplet.exp3 = c.id";
		if(!($result = $mysqli->query($qstr))) {
			echo "<script>output.add(\"MySQL Error: " .$mysqli->error. "\");" 
				."output.timestamp() ;</script>";
		} else {
			$j = 0;
			while ($row = $result->fetch_assoc()) {
				echo "<li class=\"list-group-item list-group-flush list-group-item-action\" value=\"" .$row["id"]. "\"><ul style=\"list-style-type: none; padding: 0\">" 
					."<li value=\"" .$row["exp1"]. "\" disabled>" .$row["aloc"]. "</li>"
					."<li value=\"" .$row["exp2"]. "\" disabled>" .$row["bloc"]. "</li>"
					."<li value=\"" .$row["exp3"]. "\" disabled>" .$row["cloc"]. "</li>"				
				."</ul></li>";
				echo "<script>nodes[" .$row["exp1"]. "] = new Node(map," .$row["exp1"]. ",\"" .$row["aloc"]. "\",\"" .$row["alat"]. "\",\"" .$row["alng"]. "\"," .tab_content_exp($mysqli, $db, $row["exp1"]). ");</script>" ;
				echo "<script>nodes[" .$row["exp2"]. "] = new Node(map," .$row["exp2"]. ",\"" .$row["bloc"]. "\",\"" .$row["blat"]. "\",\"" .$row["blng"]. "\"," .tab_content_exp($mysqli, $db, $row["exp2"]). ");</script>" ;
				echo "<script>nodes[" .$row["exp3"]. "] = new Node(map," .$row["exp3"]. ",\"" .$row["cloc"]. "\",\"" .$row["clat"]. "\",\"" .$row["clng"]. "\"," .tab_content_exp($mysqli, $db, $row["exp3"]). ");</script>" ;
				array_push($tri_exps, $row["exp1"]);
				array_push($tri_exps, $row["exp2"]);
				array_push($tri_exps, $row["exp3"]);
				
				$qstr = "SELECT id, triple, mac, lat, lng FROM " .$db. ".trilat WHERE trilat.triple = " .$row["id"];
				if(!($subresult = $mysqli->query($qstr))) {
					echo "<script>output.add(\"MySQL Error: " .$mysqli->error. "\");" 
						."output.timestamp() ;</script>";
				} else {
					while ($subrow = $subresult->fetch_assoc()) {
						echo "<script>trilats[" .$subrow["id"]. "] = new Trilat(map," .$row["id"]. ",\"" .$subrow["mac"]. "\",\"" .$subrow["lat"]. "\",\"" .$subrow["lng"]. "\");</script>";
					}
					$subresult->free();
				}
				$j++ ;
			}
			$result->free();
			
			echo "<script>output.add(\"" .$j. " Tri-Nodes loaded.\");</script>" ;
			echo "<script>$(\"#collapseExp div ul li.list-group-item:has(ul)\").hover( function() { $(this).addClass(\"list-group-item-danger\").removeClass(\"\"); },"
					."function() { $(this).addClass(\"\").removeClass(\"list-group-item-danger\"); })"
					.".click( function() {"
					."let item = $(this);"
					."item.addClass(\"disabled\");"
					."let triple = item.find(\"li\");"
					."let node1 = nodes[triple.eq(0).val()];"
					."let node2 = nodes[triple.eq(1).val()];"
					."let node3 = nodes[triple.eq(2).val()];"
					."node1.draw(false);"
					."node2.draw(false);"
					."node3.draw(false);"
					."let lat = (parseFloat(node1.latitude) + parseFloat(node2.latitude) + parseFloat(node3.latitude)) / 3;"
					."let lng = (parseFloat(node1.longitude) + parseFloat(node2.longitude) + parseFloat(node3.longitude)) / 3;"
					."setTimeout(function(){map.panTo(new google.maps.LatLng(lat, lng));}, 500);"
					."setTimeout(function(){map.setZoom(20);}, 800);"
					."setTimeout(function(){item.removeClass(\"disabled\");}, 1300);"
					."let i = 0;"
					."for(var key in trilats) { if(trilats[key].triple == item.val()) { let tri = trilats[key]; setTimeout(function() { tri.draw(); }, (1500 + i*100)); i++;} }"
					."if(!$(this).hasClass(\"list-group-item-success\")) {"
						."$(this).removeClass(\"list-group-item-danger\").off(\"mouseenter mouseleave\").addClass(\"list-group-item-success\");"
						."node1.listener = node1.marker.addListener(\"click\", function(e) {"
							."output.push();output.add(\"Loading Devices:\");"
							."setTimeout(function(){map.panTo(node1.marker.position);}, 100);"
							."setTimeout(function(){map.setZoom(20);}, 400);"
							."cpanel.add(\"experiment\", node1.params);"
							."cpanel.select(\"experiment\");"
							."var formData = {\"id\" : node1.id};"
							."$.ajax({url : \"./php/dev_test.php\","
								."type: \"POST\","
								."data: formData,"
								."dataType: \"html\","
								."success: function (data) {"
									."$(\"#collapseDev div\").html(data);},"
								."error: function (jXHR, textStatus, errorThrown) {"
									."alert(errorThrown);}"
						."});});"
						."node2.listener = node2.marker.addListener(\"click\", function(e) {"
							."output.push();output.add(\"Loading Devices:\");"
							."setTimeout(function(){map.panTo(node2.marker.position);}, 100);"
							."setTimeout(function(){map.setZoom(20);}, 400);"
							."cpanel.add(\"experiment\", node2.params);"
							."cpanel.select(\"experiment\");"
							."var formData = {\"id\" : node2.id};"
							."$.ajax({url : \"./php/dev_test.php\","
								."type: \"POST\","
								."data: formData,"
								."dataType: \"html\","
								."success: function (data) {"
									."$(\"#collapseDev div\").html(data);},"
								."error: function (jXHR, textStatus, errorThrown) {"
									."alert(errorThrown);}"
						."});});"
						."node3.listener = node3.marker.addListener(\"click\", function(e) {"
							."output.push();output.add(\"Loading Devices:\");"
							."setTimeout(function(){map.panTo(node3.marker.position);}, 100);"
							."setTimeout(function(){map.setZoom(20);}, 400);"
							."cpanel.add(\"experiment\", node3.params);"
							."cpanel.select(\"experiment\");"
							."var formData = {\"id\" : node3.id};"
							."$.ajax({url : \"./php/dev_test.php\","
								."type: \"POST\","
								."data: formData,"
								."dataType: \"html\","
								."success: function (data) {"
									."$(\"#collapseDev div\").html(data);},"
								."error: function (jXHR, textStatus, errorThrown) {"
									."alert(errorThrown);}"
						."});});"
					."} else {"
						."$(this).removeClass(\"list-group-item-success\").addClass(\"list-group-item-danger\")"
						.".hover( function() { $(this).addClass(\"list-group-item-danger\").removeClass(\"\"); },"
						."function() { $(this).addClass(\"\").removeClass(\"list-group-item-danger\"); });"
						."node1.listener.remove();"
						."node2.listener.remove();"
						."node3.listener.remove();"
					."}});</script>";
		}
		
		$qstr = "SELECT * FROM " .$db. "." .$table;
		if(!($result = $mysqli->query($qstr))) {
			echo "<script>output.add(\"MySQL Error: " .$mysqli->error. "\");" 
				."output.timestamp() ;</script>";
		} else {
			$i = 0;
			while ($row = $result->fetch_assoc()) {
				if(!in_array($row["id"], $tri_exps)) {
					echo "<li class=\"list-group-item list-group-flush list-group-item-action\" value=\"" .$row["id"]. "\">" .$row["location"]. "</li>";
					echo "<script>nodes[" .$row["id"]. "] = new Node(map," .$row["id"]. ",\"" .$row["location"]. "\",\"" .$row["lat"]. "\",\"" .$row["lng"]. "\"," .tab_content_exp($mysqli, $db, $row["id"]). ");</script>" ;
					$i++ ;
				}
			}
			echo "</ul>";
			echo "<script>output.add(\"" .$i. " single experiments loaded.\");" 
				."output.timestamp();</script>" ;
			echo "<script>$(\"#collapseExp div ul li.list-group-item:not(:has(ul))\").hover( function() { $(this).addClass(\"list-group-item-danger\").removeClass(\"\"); },"
					."function() { $(this).addClass(\"\").removeClass(\"list-group-item-danger\"); })"
					.".click( function() {"
					."let item = $(this);"
					."item.addClass(\"disabled\");"
					."let node = nodes[item.val()];"
					."node.draw();"
					."setTimeout(function(){item.removeClass(\"disabled\");}, 1300);"
					."if(!$(this).hasClass(\"list-group-item-success\")) {"
						."$(this).removeClass(\"list-group-item-danger\").off(\"mouseenter mouseleave\").addClass(\"list-group-item-success\");"
						."node.listener = node.marker.addListener(\"click\", function(e) {"
							."output.push();output.add(\"Loading Devices:\");"
							."setTimeout(function(){map.panTo(node.marker.position);}, 100);"
							."setTimeout(function(){map.setZoom(20);}, 400);"
							."cpanel.add(\"experiment\", node.params);"
							."cpanel.select(\"experiment\");"
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
					."} else {"
						."$(this).removeClass(\"list-group-item-success\").addClass(\"list-group-item-danger\")"
						.".hover( function() { $(this).addClass(\"list-group-item-danger\").removeClass(\"\"); },"
						."function() { $(this).addClass(\"\").removeClass(\"list-group-item-danger\"); });"
						."node.listener.remove();"
					."}});</script>";
			echo "<script>$(\"#showExp\").html(\"<span>Experiments</span><span class=\\\"badge badge-pill badge-light float-right\\\" style=\\\"margin-top: 0.25rem\\\">" .($i+$j). "</span>\");</script>";
		}
		$result->free() ;
	}
	$mysqli->close() ;
}
catch(Exception $e) {
	die ("<script>output.add(\"[PHP Error] " .$e->getMessage(). "\");</script>");
}
?>