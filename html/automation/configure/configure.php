<?php
$ipAddr=$_POST['ipAddr'];
$gateway=$_POST['gateway'];
$subnet=$_POST['subnet'];

exec("sudo ifconfig eth0:0 ".$ipAddr." netmask ".$subnet." up");
exec("sudo route add default gw ".$gateway);
?>