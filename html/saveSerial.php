<?php
$rawString=file_get_contents("php://input");
$decodedJSON=json_decode($rawString,true);
$flag = $decodedJSON['type'];
if($flag == 'clear'){
	$fp = fopen("log.txt","w") or die("Could not open file");
	fclose($fp);
	echo "File cleared";
}
elseif($flag == 'append'){
	$fp = fopen("log.txt","a") or die("Could not open file");
	$data = $decodedJSON['data'];
	fwrite($fp,$data);
	fclose($fp);
	echo "Data saved";
}
else{
	echo "Invalid file access";
}
?>
