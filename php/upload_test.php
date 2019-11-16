<?php
try {
	header("Content-Type: application/json; charset=UTF-8");
	$access = new DOMDocument();
	$access->load("../../access.xml");
	$host = $access->getElementsByTagName("host")->item(0)->nodeValue;
	$user = $access->getElementsByTagName("user")->item(0)->nodeValue;
	$pass = $access->getElementsByTagName("password")->item(0)->nodeValue;
	$db = $access->getElementsByTagName("schema")->item(0)->nodeValue;
	$auth = $access->getElementsByTagName("passkey")->item(0)->nodeValue;
	
	$mysqli = new mysqli($host, $user, $pass, $db);
	if ($mysqli->connect_error) {
		$error = [
			"success" => "fail",
			"error" => "Sql",
			"message" => "Database connection failed: " .$mysqli->connect_error
		];
		die(json_encode($error));
	}
	
	$json = file_get_contents("php://input");
	$data = json_decode($json);
	
	if (!isset($data->auth) || $data->auth != $auth) {
		$error = [
			"success" => "fail",
			"error" => "Auth",
			"message" => "Passkey does not match" 
		];
		die(json_encode($error));
	}
	
	if(isset($data->name)) {
		if(!preg_match("/^[a-z0-9\040]+$/i", $data->name)) {
			$error = [
				"success" => "fail",
				"error" => "Name",
				"message" => "Name field invalid format: (" .$data->name. ")"
			];
			die(json_encode($error));
		}
		$datetime = "0";
		if(isset($data->datetime)) {
			try {
				$datetime = explode(" ", $data->datetime);
				$date = str_replace("/", "-", date("Y-m-d", strtotime($datetime[0])));
				if(!array_key_exists(1, $datetime) || !preg_match("/^[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/", $datetime[1])) {
					$error = [
						"success" => "fail",
						"error" => "Datetime",
						"message" => "Time field invalid format: (" .$data->datetime. ")"
					];
					die(json_encode($error));
				} 
				$time = $datetime[1];
				$datetime = $date. " " .$time;
			} catch(Exception $e) {
				$error = [
					"success" => "fail",
					"error" => "Datetime",
					"message" => "Date field invalid format: (" .$data->datetime. ")"
				];
				die(json_encode($error));
			}
		} else {
			$error = [
				"success" => "fail",
				"error" => "Datetime",
				"message" => "Missing datetime parameter"
			];
			die(json_encode($error));
		}
		if(isset($data->mac)) {
			if(!preg_match("/^[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}$/i", $data->mac)) {
				$error = [
					"success" => "fail",
					"error" => "Mac",
					"message" => "Mac field invalid format: (" .$data->mac. ")"
				];
				die(json_encode($error));
			}
		} else {
			$error = [
				"success" => "fail",
				"error" => "Mac",
				"message" => "Missing mac parameter"
			];
			die(json_encode($error));
		}
		if(isset($data->lat)) {
			if(!preg_match("/^\-?1?[0-9]?[0-9]\.[0-9]{2,12}$/", $data->lat)) {
				$error = [
					"success" => "fail",
					"error" => "Lat",
					"message" => "Lat field invalid format: (" .$data->lat. ")"
				];
				die(json_encode($error));
			}
		} else {
			$error = [
				"success" => "fail",
				"error" => "Lat",
				"message" => "Missing lat parameter"
			];
			die(json_encode($error));
		}
		if(isset($data->lng)) {
			if(!preg_match("/^\-?1?[0-9]?[0-9]\.[0-9]{2,12}$/", $data->lng)) {
				$error = [
					"success" => "fail",
					"error" => "Lng",
					"message" => "Lng field invalid format: (" .$data->lng. ")"
				];
				die(json_encode($error));
			}
		} else {
			$error = [
				"success" => "fail",
				"error" => "Lng",
				"message" => "Missing lng parameter"
			];
			die(json_encode($error));
		}
		
		$qstr = "SELECT * FROM scanner WHERE mac = \"" .$data->mac. "\"";
		if(!($result = $mysqli->query($qstr))) {
			$error = [
				"success" => "fail",
				"error" => "Sql",
				"errno" => isset($mysqli->errno) ? $mysqli->errno : -1,
				"message" => "Missing device entry in scanner table: " .$data->mac
			];
			$mysqli->close();
			die(json_encode($error));
		}
		$scanid = $result->fetch_assoc()["id"];
		$result->free();
		
		$experiment = "-1";
		$qstr = "INSERT INTO experiment (scanner, time_start, location, lat, lng) VALUES ("
			.$scanid. ",\"" .$datetime. "\",\"" .$data->name. "\"," .$data->lat. "," .$data->lng. ")";
		if(!$mysqli->query($qstr)) {
			if($mysqli->errno != 1062) {
				$error = [
					"success" => "fail",
					"error" => "Sql",
					"errno" => isset($mysqli->errno) ? $mysqli->errno : -1,
					"message" => "Experiment table insertion error: " .$mysqli->error
				];
				$mysqli->close();
				die(json_encode($error));
			} else {
				$qstr = "SELECT * FROM experiment where scanner = " .$scanid. " AND time_start = \"" .$datetime. "\"";
				if(!($result = $mysqli->query($qstr))) {
					$error = [
						"success" => "fail",
						"error" => "Sql",
						"errno" => isset($mysqli->errno) ? $mysqli->errno : -1,
						"message" => "Experiment entry select error: " .$mysqli->error
					];
					$mysqli->close();
					die(json_encode($error));
				} else {
					$experiment = $result->fetch_assoc()["id"];
					$result->free();
				}
			}
		} else {
			$experiment = $mysqli->insert_id;
		}
		
		$mysqli->close();
		$response = [
			"success" => "ok",
			"experiment" => $experiment,
			"name" => $data->name,
			"datetime" => $datetime,
			"mac" => $data->mac,
			"lat" => $data->lat,
			"lng" => $data->lng
		];
		echo json_encode($response);
		
	} elseif(isset($data->experiment)) {
		$count = 0;
		$length = 0;
		if(!preg_match("/^[0-9]+$/", $data->experiment)) {
			$error = [
				"success" => "fail",
				"error" => "Experiment",
				"message" => "Experiment field invalid format: (" .$data->experiment. ")"
			];
			die(json_encode($error));
		}
		if(isset($data->entries)) {
			try {
				$length = count($data->entries);
				$mysqli->autocommit(FALSE);
				for($i = 0; $i < $length; $i++) {
					$entry = $data->entries[$i];
					$datetime = "0";
					if(isset($entry->datetime)) {
						try {
							$datetime = explode(" ", $entry->datetime);
							$date = str_replace("/", "-", date("Y-m-d", strtotime($datetime[0])));
							if(!array_key_exists(1, $datetime) || !preg_match("/^[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/", $datetime[1])) {
								$error = [
									"success" => "fail",
									"error" => "Datetime",
									"message" => "Time field invalid format: entry " .$i. " (" .$entry->datetime. ")"
								];
								die(json_encode($error));
							} 
							$time = $datetime[1];
							$datetime = $date. " " .$time;
						} catch(Exception $e) {
							$error = [
								"success" => "fail",
								"error" => "Datetime",
								"message" => "Date field invalid format: entry " .$i. " (" .$entry->datetime. ")"
							];
							die(json_encode($error));
						}
					} else {
						$error = [
							"success" => "fail",
							"error" => "Datetime",
							"message" => "Missing datetime parameter: entry " .$i
						];
						die(json_encode($error));
					}
					
					if(isset($entry->mac)) {
						if(!preg_match("/^[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}$/i", $entry->mac)) {
							$error = [
								"success" => "fail",
								"error" => "Mac",
								"message" => "Mac field invalid format: entry " .$i. " (" .$entry->mac. ")"
							];
							die(json_encode($error));
						}
					} else {
						$error = [
							"success" => "fail",
							"error" => "Mac",
							"message" => "Missing mac parameter: entry " .$i
						];
						die(json_encode($error));
					}
					
					$qstr = "INSERT INTO device (experiment, discovery, mac) VALUES ("
						.$data->experiment. ",\"" .$datetime. "\",\"" .$entry->mac. "\")";
					if(!$mysqli->query($qstr)) {
						if($mysqli->errno != 1062) {
							$error = [
								"success" => "fail",
								"error" => "Sql",
								"errno" => isset($mysqli->errno) ? $mysqli->errno : -1,
								"message" => "Device insertion error: (" .$mysqli->error. ")"
							];
							$mysqli->close();
							die(json_encode($error));
						}
					}
				}
				if(!$mysqli->commit()) {
					$error = [
						"success" => "fail",
						"error" => "Device",
						"errno" => isset($mysqli->errno) ? $mysqli->errno : -1,
						"message" => "Device transaction commit error: (" .$mysqli->error. ")"
					];
					$mysqli->close();
					die(json_encode($error));
				}
			} catch(Exception $e) {
				$error = [
					"success" => "fail",
					"error" => "Entries",
					"message" => "PHP error @ inserting devices: (" .get_class($e). ")"
				];
				$mysqli->close();
				die(json_encode($error));
			}
			
			try {
				$length = count($data->entries);
				$mysqli->autocommit(FALSE);
				$macs = array();
				for($i = 0; $i < $length; $i++) {
					$entry = $data->entries[$i];
					$datetime = explode(" ", $entry->datetime);
					$date = str_replace("/", "-", date("Y-m-d", strtotime($datetime[0])));
					$time = $datetime[1];

					$qstr = "SELECT id FROM device WHERE experiment = " .$data->experiment. " AND mac = \"" .$entry->mac. "\"";
					if(!($result = $mysqli->query($qstr)) || ($result->num_rows == 0)) {
						$error = [
							"success" => "fail",
							"error" => "Sql",
							"errno" => isset($mysqli->errno) ? $mysqli->errno : -1,
							"message" => "Device ID select error: " .$entry->mac
						];
						$mysqli->close();
						die(json_encode($error));
					}
					$devid = $result->fetch_assoc()["id"];
					$result->free();
					
					$qstr = "INSERT INTO rssi (device, scan_update, rssi) VALUES ("
						.$devid. ",\"" .$time. "\",\"" .$entry->rssi. "\")";
					if(!$mysqli->query($qstr)) {
						if($mysqli->errno != 1062) {
							$error = [
								"success" => "fail",
								"error" => "Sql",
								"errno" => isset($mysqli->errno) ? $mysqli->errno : -1,
								"message" => "Rssi table insertion error: (" .$mysqli->errno. ")"
							];
							$mysqli->close();
							die(json_encode($error));
						} else {
							array_push($macs, "duplicate");
						}
					} else {
						array_push($macs, $entry->mac);
					}
				}
				if(!$mysqli->commit()) {
					$error = [
						"success" => "fail",
						"error" => "Rssi",
						"message" => "Rssi transaction commit error: (" .$mysqli->error. ")"
					];
					$mysqli->close();
					die(json_encode($error));
				}
			} catch(Exception $e) {
				$error = [
					"success" => "fail",
					"error" => "Entries",
					"message" => "PHP error @ inserting RSSI: (" .get_class($e). ")"
				];
				$mysqli->close();
				die(json_encode($error));
			}
		} else {
			$error = [
				"success" => "fail",
				"error" => "Entries",
				"message" => "Missing entries parameter"
			];
			die(json_encode($error));
		}
		
		$mysqli->close();
		$response = [
			"success" => "ok",
			"length" => $length,
			"macs" => $macs
		];
		echo json_encode($response);
	} else {
		$error = [
			"success" => "fail",
			"error" => "Empty",
			"message" => "Missing name or experiment parameter"
		];
		die(json_encode($error));
	}
}
catch(Exception $e) {
	$error = [
		"success" => "fail",
		"error" => "Php",
		"message" => "Generic PHP error: " .get_class($e)
	];
	die(json_encode($error));
}
?>