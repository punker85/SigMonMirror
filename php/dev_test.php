<?php
try
{
	$host = "localhost" ;
	$user = "root" ;
	$pass = "" ;
	$db = "sigmon" ;
	$table = "device" ;
	
	$mysqli = new mysqli($host, $user, $pass, $db) ;
		
	if(isset($_POST["experiment"]) && isset($_POST["mac"]) && isset($_POST["date"]) && isset($_POST["time"]))
	{
		$experiment = $mysqli->real_escape_string($_POST["experiment"]);
		$mac = $mysqli->real_escape_string($_POST["mac"]);
		$date = $mysqli->real_escape_string($_POST["date"]);
		$time = $mysqli->real_escape_string($_POST["time"]);
		
		$date = date('Y-m-d', strtotime(str_replace('-', '/', $date)));
		$datetime = $date. " " .$time ;
		
		if(!$mysqli->query("INSERT INTO " .$table. "(experiment, discovery, mac) VALUES ("
			.$experiment. ",\"" .$datetime. "\",\"" .$mac. "\")")) 
		{
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
	} /*elseif($_SERVER["REQUEST_METHOD"] == "GET") {
		$qstr = "SELECT * FROM " . $db . "." . $table ;
		if(!($result = $mysqli->query($qstr)))
		{
			echo "output.add(\"MySQL Error: " .$mysqli->error. "\");" 
				."output.timestamp() ;" ;
		} else {
			$i = 0;
			while ($row = $result->fetch_assoc()) {
				echo "nodes.add(new Node(\"" .$row["location"]. "\",\"" .$row["lat"]. "\",\"" .$row["lng"]. "\")) ;" ;
				$i++ ;
			}
			echo "output.add(\"" .$i. " experiments located.\");" 
				."output.timestamp();" ;
		}
		$result->free() ;
	}*/
	$mysqli->close() ;
}
catch(Exception $e)
{
	echo "alert(\"" .$e->getMessage(). "\") ;";
}
?>