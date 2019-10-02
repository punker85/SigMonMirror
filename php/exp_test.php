<?php
/*
if (!function_exists('mysqli_init') && !extension_loaded('mysqli')) {
    echo 'We don\'t have mysqli!!!';
} else {
    echo 'Phew we have it!';
}*/
try
{
	$host = "localhost" ;
	$user = "root" ;
	$pass = "Ilovekim0" ;
	$name = "sigmon" ;
	$table = "experiment" ;
	
	$mysqli = new mysqli($host, $user, $pass, $name) ;
	if ($mysqli->connect_error)
		die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
		
	echo "output.push() ;" . "output.add(\"DB Connect: " .$mysqli->host_info. ".  \") ;";
	if(isset($_POST["place"]) && isset($_POST["date"]) && isset($_POST["time"]) && isset($_POST["lat"]) && isset($_POST["lng"]))
	{
		$date = date('Y-m-d', strtotime(str_replace('-', '/', $_POST["date"])));
		$datetime = $date . " " . $_POST["time"] ;
		
		if(!$mysqli->query("INSERT INTO experiment(scanner, time_start, location, lat, lng) VALUES (0,"
			. "\"" . $datetime . "\",\"" . $mysqli->real_escape_string($_POST["place"]) . "\"," . $_POST["lat"] . "," . $_POST["lng"] . ")"))
		{
			echo "output.add(\"MySQL Error: " .$mysqli->error. "\");" ;
		} else {
			echo "output.add(\"VALUES (" . $mysqli->real_escape_string($_POST["place"]) . ", " . $datetime . ") added to TABLE: " . $table . ".\") ;"
				."output.timestamp() ;" ;
		}
	} elseif($_SERVER["REQUEST_METHOD"] == "GET") {
		//if(!$mysqli->query("SELECT * FROM sigmon.experiment;
	}
	
	/*if(!isset($_POST["size"]))
	{
		$query = "SELECT mac, COUNT(DISTINCT AP), SUM(duration) AS manhours
			FROM traces 
			GROUP BY mac
			HAVING COUNT(DISTINCT AP) > 5
			ORDER BY 3 DESC";
		$result = mysql_query($query, $conn);
		$num = mysql_num_rows($result);
		
		$macs = array();
		for($i = 0; $i < $num; $i++)
		{
			$macs[$i] = mysql_result($result,$i,"mac");
		}
		
		for($i = 0; $i < count($macs); $i++)
		{			
			echo "<script>"
					."macs.push(\"" .$macs[$i]. "\") ;"
				."</script>" ;
		} 
		
		echo "<script>"
				."openDialog('Clustering User Devices...') ;"
				."queryCluster(10, 0, macs.length) ;"
			."</script>" ;
	}
	else
	{
		$doc = new DOMDocument();
		$doc->load("data/default.xml");
		if(!$doc)
			die("cannot read file");
		$nodes = $doc->getElementsByTagName('node');
		$aps = array() ;
		for($i = 0; $i < $nodes->length; $i++)
			$aps[$i] = $nodes->item($i)->getElementsByTagName("name")->item(0)->nodeValue ;
			
		for($i = 0; $i < $_POST["size"]; $i++)
		{				
			$query = "SELECT ap, SUM(x.mtime) FROM (
							SELECT CASE
								WHEN ap = '172.16.8.245_21034' THEN '172.16.8.245_31027'
								WHEN ap = '172.16.8.245_21019' THEN '172.16.8.245_31023'
								ELSE ap
							END AS ap, 
							SUM(duration) AS mtime
							FROM traces 
							WHERE mac LIKE \"" .$_POST["mac".$i]. "\" 
							AND ap IN ('" .implode("','", $aps). "') 
							GROUP BY ap) AS x 
						GROUP BY x.ap 
						ORDER BY 2 desc ";
			$result = mysql_query($query, $conn);
			$num = mysql_num_rows($result);
			
			if($num > 2)
			{
				echo "<script>"
						."clusters.insert(map, \"" .$_POST["mac".$i]. "\",\"" .mysql_result($result, 0, "ap"). "\",\"" .mysql_result($result, 1, "ap"). "\",\"" .mysql_result($result, 2, "ap"). "\");"
					."</script>" ;
			}
		}
		
		$num = 10 ;
		if(($_POST["total"] - $_POST["position"]) < 10)
			$num = $_POST["total"] - $_POST["position"] ;
		
		echo "<script>"
				."queryCluster(" .$num. ", " .$_POST["position"]. " + " .$num. ", " .$_POST["total"]. ") ;"
			."</script>" ;
	}*/
	$mysqli->close() ;
}
catch(Exception $e)
{
	echo "alert(\"" .$e->getMessage(). "\") ;";
}
?>