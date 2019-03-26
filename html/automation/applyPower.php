<?php
session_start();
$host=$_SESSION['ipAddr'];
//function definition
function sendSocketCommand($cmdString){
	$socket=$GLOBALS['socket'];
	$command="$cmdString"."\n"; 
	//it has sent the command to SCPI server 
	socket_write($socket, $command, strlen($command)) or die("Could not send data to server\n");
	//$result="";
	//socket_recv($socket, $result,1024,MSG_WAITALL);// or header('Location:guiSocket.php');
	socket_read($socket,1);
	//echo $result;

	//return $result;
}

$host=$GLOBALS['host'];
	
$port=5025;   //here the port no is 5025.it is not random
	//SCPI is a protocol built over top of TCP which listens on specific port ,which is 5025 or 5024 by default
	//So ,I have created a tcp socket for iv4
	//SOCK_STREAM is for TCP and SOCK_DGRAM is for UDP
	//
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");  //it creates a tcp socket
socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));  //    if it doesn,t get reply from server it will close its connection after 5 sec
$result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");   //it has connected to server and stores its connection link in result
//set up the parameters 
if(isset($_POST['mode'])){
	$txvalue=$_POST['txmodevalue'];
	$rxvalue=$_POST['rxmodevalue'];
	if($_POST['mode']=="txmode"){
		sendSocketCommand("SENS:SWE:PULS:MODE STD");
		sendSocketCommand("SOUR1:POW ".$txvalue);
		sendSocketCommand("OUTP ON");
	}
	else if($_POST['mode']=="rxmode"){
		sendSocketCommand("SENS:SWE:PULS:MODE OFF");
		sendSocketCommand("SOUR1:POW ".$rxvalue);
		sendSocketCommand("OUTP ON");
	}
}
sendSocketCommand("")
socket_close($socket);
//echo "Configuration done."
?>
