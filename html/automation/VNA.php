<?php
session_start();
$host=$_POST['ipAddr'];
$_SESSION['ipAddr']=$_POST['ipAddr'];
//function definition
function sendSocketCommand($cmdString){
	$socket=$GLOBALS['socket'];
	$command="$cmdString"."\n"; 
	//it has sent the command to SCPI server 
	socket_write($socket, $command, strlen($command)) or die("Could not send data to server\n");
	//$result="";
	//socket_recv($socket, $result,1024,MSG_WAITALL);// or header('Location:guiSocket.php');
	//socket_read($socket,1024);
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
$startFreq=$_POST['startFreq'];
$stopFreq=$_POST['stopFreq'];
$pulseWidth=$_POST['pulseWidth'];
$dutyCycle=$_POST['dutyCycle'];
$points=$_POST['points'];

//start frequency 
sendSocketCommand("SYSTem:FPRESET");
//sleep(1);
sendSocketCommand("CALCulate1:PARameter:DEFine 'Meas1',S21");
//sleep(1);
sendSocketCommand("DISPlay:WINDow1:STATe ON");
//sleep(1);
sendSocketCommand("DISPlay:WINDow1:TRACe1:FEED 'Meas1'");
//sleep(1);
sendSocketCommand("INITiate1:CONTinuous OFF;*OPC?");

//sendSocketCommand("INITiate1:CONTinous OFF;*OPC?");
//sleep(1);
sendSocketCommand("SENSe1:SWEep:TRIGger:POINt OFF");
//sleep(1);
sendSocketCommand("SENSe1:SWEep:POINts ".$points);
//sleep(1);
sendSocketCommand("SENSe1:FREQuency:STARt " .$startFreq);
//sleep(1);
sendSocketCommand("SENSe1:FREQuency:STOP ".$stopFreq);
//sleep(1);
sendSocketCommand("INITiate1;*OPC?");
//sleep(1);
sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1'");
//sleep(1);
sendSocketCommand("FORMat ASCII");
sendSocketCommand("CALC1:FORM MLOG");//set the Y axis to dB .
//sleep(1);
//$sweepTime=sendSocketCommand("SENSe1:SWEep:TIME?");
//sleep(1);
//$_SESSION['sweepTime']=$sweepTime;
socket_close($socket);
echo "Configuration done."
?>
