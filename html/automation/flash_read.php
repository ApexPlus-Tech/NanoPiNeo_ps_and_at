<?php
//remove the folder
ini_set('max_execution_time', 0);
$fp         = fopen("/var/www/automation/session.txt","r") or die("Could not open session file");
$line       = fgets($fp);
fclose($fp);
$folderName = str_replace(array("\r","\n"),"",$line);
if($folderName == "" || $folderName == "\r\n" || $folderName == "\n" ){
	die("Invalid folder name");
}
exec("rm -r --interactive=never /var/www/automation/boards/$folderName/ReadFiles");
exec("mkdir /var/www/automation/boards/$folderName/ReadFiles");
//read frequency range
$fp=fopen("/var/www/automation/freq_range.txt","r") or die("Could not open freq_range file");
$line       = fgets($fp);
$line       = str_replace(array("\r","\n"),"",$line);
$lineArray  = explode(" ",$line);
$start      = $lineArray[0];
$stop       = $lineArray[1];
$filepath   = "/var/www/automation/boards/$folderName/";
exec("python /var/www/automation/spiFlashRead.py $start $stop $filepath");
?>
