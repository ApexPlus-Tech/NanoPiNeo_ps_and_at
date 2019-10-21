<?php
	ini_set('max_execution_time', 0);
	$rawData  = file_get_contents("php://input");
	$jsonData = json_decode($rawData,true);
	if(1){
		$fp = fopen("/var/www/automation/freq_range.txt","w") or die('Could not open text file freq_range');
		$start = $jsonData['start'];
		$stop  = $jsonData['stop'];
		fwrite($fp,"$start $stop");
		fclose($fp);
	}
	else{
		echo "Error in inputs";
	}	
?>
