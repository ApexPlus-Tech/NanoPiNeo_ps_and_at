<?php
$ipAddr=$_POST['ipAddr'];
$gateway=$_POST['gateway'];
$subnet=$_POST['subnet'];
//$nameserver=$_POST['nameserver'];
exec("sudo ifconfig eth0 ".$ipAddr." netmask ".$subnet." up");
exec("sudo route add default gw ".$gateway);
//exec("echo 'nameserver ".$nameserver."'| sudo tee /etc/resolv.conf > /dev/null");
?>