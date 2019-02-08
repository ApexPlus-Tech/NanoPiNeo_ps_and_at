<?php
session_start();
$host=$_POST['ipAddr'];
$_SESSION['ipAddr']=$_POST['ipAddr'];
$_SESSION['startFreq']=$_POST['startFreq'];
$_SESSION['stopFreq']=$_POST['stopFreq'];
$_SESSION['points']=$_POST['points'];
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
$startFreq=$_POST['startFreq'];
$stopFreq=$_POST['stopFreq'];
$pulseWidth=$_POST['pulseWidth'];
$dutyCycle=$_POST['dutyCycle'];
$points=$_POST['points'];

//COMMANDS FOR WINDOW1
sendSocketCommand("SYSTem:FPRESET");
//sleep(1);
sendSocketCommand("CALCulate1:PARameter:DEFine 'Meas1_Amp',S21");
//sleep(1);
sendSocketCommand("DISPlay:WINDow1:STATe ON");
//sleep(1);
sendSocketCommand("DISPlay:WINDow1:TRACe1:FEED 'Meas1_Amp'");
//sleep(1);
sendSocketCommand("INITiate1:CONTinuous OFF;*OPC?");
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
sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
//sleep(1);
sendSocketCommand("FORMat ASCII");
sendSocketCommand("CALC1:FORM MLOG");//set the Y axis to dB .
sendSocketCommand("CALC1:MARK1:STAT ON");
sendSocketCommand("CALC1:MARK1:TYPE FIXED");
sendSocketCommand("CALC1:MARK1:X 3.1e9");

sendSocketCommand("CALC1:MARK2:STAT ON");
sendSocketCommand("CALC1:MARK2:TYPE FIXED");
sendSocketCommand("CALC1:MARK2:X 3.2e9");


sendSocketCommand("CALC1:MARK3:STAT ON");
sendSocketCommand("CALC1:MARK3:TYPE FIXED");
sendSocketCommand("CALC1:MARK3:X 3.3e9");


sendSocketCommand("CALC1:MARK4:STAT ON");
sendSocketCommand("CALC1:MARK4:TYPE FIXED");
sendSocketCommand("CALC1:MARK4:X 3.4e9");


sendSocketCommand("CALC1:MARK5:STAT ON");
sendSocketCommand("CALC1:MARK5:TYPE FIXED");
sendSocketCommand("CALC1:MARK5:X 3.5e9");
//COMMANDS FOR WINDOW2
sendSocketCommand("CALCulate2:PARameter:DEFine 'Meas1_Phase',S21");
sendSocketCommand("DISPlay:WINDow2:STATe ON");
sendSocketCommand("DISPLay:WINDow2:TRACe2:FEED 'Meas1_Phase'");
sendSocketCommand("INITiate2:CONTinuous OFF;*OPC?");
sendSocketCommand("SENSe2:SWEep:TRIGger:POINt OFF");

sendSocketCommand("SENSe2:SWEep:POINts ".$points);
sendSocketCommand("SENSe2:FREQuency:STARt " .$startFreq);
sendSocketCommand("SENSe2:FREQuency:STOP ".$stopFreq);
sendSocketCommand("INITiate2;*OPC?");
sendSocketCommand("CALCulate2:PARameter:SELect 'Meas1_Phase'");
sendSocketCommand("CALC2:FORM PHASe");
//set the markers

sendSocketCommand("CALC2:MARK1:STAT ON");
sendSocketCommand("CALC2:MARK1:TYPE FIXED");
sendSocketCommand("CALC2:MARK1:X 3.1e9");


sendSocketCommand("CALC2:MARK2:STAT ON");
sendSocketCommand("CALC2:MARK2:TYPE FIXED");
sendSocketCommand("CALC2:MARK2:X 3.2e9");


sendSocketCommand("CALC2:MARK3:STAT ON");
sendSocketCommand("CALC2:MARK3:TYPE FIXED");
sendSocketCommand("CALC2:MARK3:X 3.3e9");

sendSocketCommand("CALC2:MARK4:STAT ON");
sendSocketCommand("CALC2:MARK4:TYPE FIXED");
sendSocketCommand("CALC2:MARK4:X 3.4e9");


sendSocketCommand("CALC2:MARK5:STAT ON");
sendSocketCommand("CALC2:MARK5:TYPE FIXED");
sendSocketCommand("CALC2:MARK5:X 3.5e9");


sendSocketCommand("DISP:ENAB ON");
socket_close($socket);
echo "Configuration done."
?>
