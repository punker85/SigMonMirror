<?php
try {
	$access = new DOMDocument();
	$access->load("../../access.xml");
	$host = $access->getElementsByTagName("host")->item(0)->nodeValue;
	$user = $access->getElementsByTagName("user")->item(0)->nodeValue;
	$pass = $access->getElementsByTagName("password")->item(0)->nodeValue;
	$db = $access->getElementsByTagName("schema")->item(0)->nodeValue;
	
	$mysqli = new mysqli($host, $user, $pass, $db);
	if ($mysqli->connect_error)
		die("<script>output.add(\"[Connect Error (" .$mysqli->connect_errno. ")] " .$mysqli->connect_error. "\");</script>");
		
	if(isset($_POST["table"])) {
		$table = $mysqli->real_escape_string($_POST["table"]);
		$qstr = "SELECT * FROM " .$db. "." .$table;
		if(!preg_match("/^[a-z0-9]+$/i", $table)) {
			die ("<script>output.add(\"Table name format error: " .$table. "\");</script>");
		}
		
		if(isset($_POST["id"])) {
			$id = $mysqli->real_escape_string($_POST["id"]);
			if(!preg_match("/^[0-9]+$/", $id)) {
				die ("<script>output.add(\"Experiment ID format error: " .$id. "\");</script>");
			}
			if($table == "experiment") {
				$qstr .= " WHERE id = \"" .$id. "\"";
				if(!($result = $mysqli->query($qstr))) {
					echo "<script>output.add(\"[MySQL Error] " .$mysqli->error. "\");</script>";
				} else {
					while ($row = $result->fetch_assoc()) {
						$datetime = explode(" ", $row["time_start"]);
						$date = str_replace("-", "/", date("m-d-Y", strtotime($datetime[0])));
						echo "<script>$(\"#devDate\").val(\"" .$date. "\");</script>";
						echo "<script>$(\"#devTime\").val(\"" .$datetime[1]. "\");</script>";
						echo "<script>output.add(\"" .$row["location"]. " experiment picked.\");"
							."output.timestamp();</script>" ;
					}
				}
			} elseif($table == "device") {
				if(!isset($_POST["pick"])) {
					$qstr .= " WHERE experiment = \"" .$id. "\"";
					if(!($result = $mysqli->query($qstr))) {
						echo "<script>output.add(\"[MySQL Error] " .$mysqli->error. "\");</script>";
					} else {
						$i = 0;
						echo "<select class=\"form-control\" name=\"device\" required>" ;
						echo "<option selected disabled>Pick device...</option>" ;
						while ($row = $result->fetch_assoc()) {
							echo "<option value=" .$row["id"]. ">" .$row["mac"]. " (" .$row["id"]. ")</option>" ;
							$i++ ;
						}
						echo "</select><script>output.add(\"" .$i. " devices returned.\");" 
							."output.timestamp();</script>" ;
					}
				} else {
					$qstr .= " WHERE id = \"" .$id. "\"";
					if(!($result = $mysqli->query($qstr))) {
						echo "<script>output.add(\"[MySQL Error] " .$mysqli->error. "\");</script>";
					} else {
						while ($row = $result->fetch_assoc()) {
							$datetime = explode(" ", $row["discovery"]);
							$date = str_replace("-", "/", date("m-d-Y", strtotime($datetime[0])));
							echo "<script>$(\"#rssDate\").val(\"" .$date. "\");</script>";
							echo "<script>$(\"#rssTime\").val(\"" .$datetime[1]. "\");</script>";
							echo "<script>output.add(\"" .$row["mac"]. " device picked.\");"
								."output.timestamp();</script>" ;
						}
					}
				}
			}
		} else {
			if(!($result = $mysqli->query($qstr))) {
				echo "<div class=\"text-danger\"><b>MySQL Error!</b></div>"
				."<script>output.add(\"[MySQL Error] " .$mysqli->error. "\");</script>" ;
			} elseif($table == "scanner") {
				$i = 0;
				echo "<select class=\"form-control\" name=\"scanner\" required>" ;
				echo "<option selected disabled>Pick scanner...</option>" ;
				while ($row = $result->fetch_assoc()) {
					echo "<option value=" .$row["id"]. ">" .$row["owner"]. " (" .$row["id"]. ")</option>" ;
					$i++ ;
				}
				echo "</select><script>output.add(\"" .$i. " scanners returned.\");" 
					."output.timestamp();</script>" ;
			} elseif($table == "experiment") {
				$i = 0;
				echo "<select class=\"form-control\" name=\"experiment\" required>" ;
				echo "<option selected disabled>Pick experiment...</option>" ;
				while ($row = $result->fetch_assoc()) {
					echo "<option value=" .$row["id"]. ">" .$row["location"]. " (" .$row["id"]. ")</option>" ;
					$i++ ;
				}
				echo "</select><script>output.add(\"" .$i. " experiments returned.\");" 
					."output.timestamp();</script>" ;
			}
			$result->free() ;
		}
		$mysqli->close() ;
	}
}
catch(Exception $e) {
	die ("<script>output.add(\"[PHP Error] " .$e->getMessage(). "\");</script>");
}
?>