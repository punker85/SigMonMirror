<?php
try
{
	$host = "localhost";
	$user = "root";
	$pass = "";
	$db = "sigmon";
	$table = "rssi";
	
	$mysqli = new mysqli($host, $user, $pass, $db);
		
	if(isset($_POST["device"]) && isset($_POST["rssi"]) && isset($_POST["date"]) && isset($_POST["time"]))
	{
		$device = $mysqli->real_escape_string($_POST["device"]);
		$rssi = $mysqli->real_escape_string($_POST["rssi"]);
		$date = $mysqli->real_escape_string($_POST["date"]);
		$time = $mysqli->real_escape_string($_POST["time"]);
		
		$date = date('Y-m-d', strtotime(str_replace('-', '/', $date)));
		$datetime = $date. " " .$time;
		
		if(!$mysqli->query("INSERT INTO " .$table. "(device, scan_update, rssi) VALUES ("
			.$device. ",\"" .$datetime. "\",\"" .$rssi. "\")")) 
		{
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
	}
	$mysqli->close();
}
catch(Exception $e)
{
	echo "alert(\"" .$e->getMessage(). "\");";
}
?>