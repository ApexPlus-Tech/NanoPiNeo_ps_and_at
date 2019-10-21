<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$fp = fopen("interprocess","r+");
$line = fgets($fp);
echo $line;
flush();
?>
