<?php
try
{
	$host = "localhost" ;
	$user = "root" ;
	$pass = "" ;
	$db = "sigmon" ;
	$table = "experiment" ;
	
	$mysqli = new mysqli($host, $user, $pass, $db) ;
		
	if(isset($_POST["scanner"]) && isset($_POST["place"]) && isset($_POST["date"]) && isset($_POST["time"]) && isset($_POST["lat"]) && isset($_POST["lng"]))
	{
		$date = date('Y-m-d', strtotime(str_replace('-', '/', $_POST["date"])));
		$datetime = $date . " " . $_POST["time"] ;
		
		if(!$mysqli->query("INSERT INTO " .$table. "(scanner, time_start, location, lat, lng) VALUES ("
			. $_POST["scanner"] . ",\"" . $datetime . "\",\"" . $mysqli->real_escape_string($_POST["place"]) . "\"," . $_POST["lat"] . "," . $_POST["lng"] . ")"))
		{
			echo "output.add(\"MySQL Error: " .$mysqli->error. "\");" 
				."output.timestamp() ;" ;
			echo "$(\"#expSubmit\").addClass(\"btn-danger\").removeClass(\"btn-secondary\").html(\"<b>SQL Error :(</b>\");"
				."testmenu.resetExp();" ;
		} else {
			echo "output.add(\"VALUES (" . $mysqli->real_escape_string($_POST["place"]) . ", " . $datetime . ") added to TABLE: " . $table . ".\") ;"
				."output.timestamp() ;" ;
			echo "$(\"#expSubmit\").addClass(\"btn-success\").removeClass(\"btn-secondary\").html(\"<b>Success :D</b>\");"
				."testmenu.resetExp();" ;
		}
	} elseif($_SERVER["REQUEST_METHOD"] == "GET") {
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
	}
	$mysqli->close() ;
}
catch(Exception $e)
{
	echo "alert(\"" .$e->getMessage(). "\") ;";
}
?>
