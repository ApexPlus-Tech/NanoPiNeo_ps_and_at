<?php
	$output=shell_exec("/usr/bin/python /var/www/testSerial.py");
	header("Content-type: application/json");
	echo json_encode($output)
?>
