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
if(isset($_POST['mode'])){
	if($_POST['mode']=="txmode"){
		sendSocketCommand("SENS:SWE:PULS:MODE STD");//turn ON the pulse
		sendSocketCommand("SENS2:SWE:PULS:MODE STD");//turn ON the pulse
		sendSocketCommand("SENS1:PULS1 1");
		sendSocketCommand("SENS2:PULS1 1");
		//sendSocketCommand("OUTP ON");
		$pulsewidth=$_POST['pulsewidth'];
		$dutycycle=$_POST['dutycycle'];
		//echo $dutycycle;
		sendSocketCommand("SENS1:PULS:WIDT ".$pulsewidth);
		$pulsePeriod=floatval($pulsewidth)/floatval($dutycycle);
		//echo ($pulsewidth);
		sendSocketCommand("SENS1:PULS:PER ".$pulsePeriod);
		 sendSocketCommand("SENS2:PULS:WIDT ".$pulsewidth);
                //$pulsePeriod=floatval($pulsewidth)/floatval($dutycycle);
                //echo ($pulsewidth);
                sendSocketCommand("SENS2:PULS:PER ".$pulsePeriod);

	}
	else if($_POST['mode']=="rxmode"){
		sendSocketCommand("SENS:SWE:PULS:MODE OFF");
		sendSocketCommand("SENS2:SWE:PULS:MODE OFF");
		//sendSocketCommand("OUTP ON");
		//sendSocketCommand("SOUR2:POW:MODE ON");
	}
}
//set up the parameters 
//set pulse commands
//sendSocketCommand("SENS:SWE:PULS:MODE STD");//turn ON the pulse
//sendSocketCommand("SENS1:PULS1 1");
//sendSocketCommand("SENS1:PULS:WIDT ".$pulseWidth);
//$pulsePeriod=floatval($pulseWidth)/floatval($dutycycle);
//sendSocketCommand("SENS1:PULS:PER ".$pulsePeriod);
//sleep(1);

socket_close($socket);

?>
