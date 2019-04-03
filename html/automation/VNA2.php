<?php
session_start();
$host=$_SESSION['ipAddr'];
//echo $ipAddr;
$startFreq=$_SESSION['startFreq'];
$stopFreq=$_SESSION['stopFreq'];
$points=$_POST['points'];
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
	
$port=5025;   //here the port no is 5025.it is not random
	//SCPI is a protocol built over top of TCP which listens on specific port ,which is 5025 or 5024 by default
	//So ,I have created a tcp socket for iv4
	//SOCK_STREAM is for TCP and SOCK_DGRAM is for UDP
	//
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");  //it creates a tcp socket
socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));  //    if it doesn,t get reply from server it will close its connection after 5 sec
$result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");   //it has connected to server and stores its connection link in result
//set up the parameters 
//COMMANDS FOR WINDOW1
//sendSocketCommand("SYSTem:FPRESET");
//sendSocketCommand("SYST:CHAN:DEL 1");
//sendSocketCommand("CALCulate1:PARameter:DEFine 'Meas1_Amp',S21");
//sleep(1);
//sendSocketCommand("DISPlay:WINDow1:STATe ON");
//sleep(1);
//sendSocketCommand("DISPlay:WINDow1:TRACe1:FEED 'Meas1_Amp'");
//sleep(1);
sendSocketCommand("INITiate1:CONTinuous OFF;*OPC?");
//sleep(1);
sendSocketCommand("SENSe1:SWEep:TRIGger:POINt OFF");
//sleep(1);
sendSocketCommand("SENSe1:SWEep:POINts ".$points);
//sleep(1);
//sendSocketCommand("SENSe1:FREQuency:STARt " .$startFreq);
//sleep(1);
//sendSocketCommand("SENSe1:FREQuency:STOP ".$stopFreq);
//set pulse commands
//sendSocketCommand("SENS:SWE:PULS:MODE STD");//turn ON the pulse
//sendSocketCommand("SENS1:PULS1 1");
//sendSocketCommand("SENS1:PULS:WIDT ".$pulseWidth);
//$pulsePeriod=floatval($pulseWidth)/floatval($dutycycle);
//sendSocketCommand("SENS1:PULS:PER ".$pulsePeriod);
//sleep(1);
sendSocketCommand("INITiate1;*OPC?");
//sleep(1);
sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
//sleep(1);
sendSocketCommand("FORMat ASCII");
sendSocketCommand("CALC1:FORM MLOG");//set the Y axis to dB .

//UNCOMMENT HERE
// $delta=($stopFreq-$startFreq)/($points-1);
// //$freqString="";
// for($z=0;$z<$points;$z++){
// 	$tempFreq=$startFreq+$z*$delta;
// 	sendSocketCommand("CALC1:MARK".$z+1.":STAT ON");
// 	sendSocketCommand("CALC1:MARK".$z+1.":X ".$tempFreq);
// }
//UNCOMMENT HERE

sendSocketCommand("CALC1:MARK1:STAT ON");
//sendSocketCommand("CALC1:MARK1:TYPE FIXED");
sendSocketCommand("CALC1:MARK1:X 3.1e9");

sendSocketCommand("CALC1:MARK2:STAT ON");
//sendSocketCommand("CALC1:MARK2:TYPE FIXED");
sendSocketCommand("CALC1:MARK2:X 3.2e9");


sendSocketCommand("CALC1:MARK3:STAT ON");
//sendSocketCommand("CALC1:MARK3:TYPE FIXED");
sendSocketCommand("CALC1:MARK3:X 3.3e9");


sendSocketCommand("CALC1:MARK4:STAT ON");
//sendSocketCommand("CALC1:MARK4:TYPE FIXED");
sendSocketCommand("CALC1:MARK4:X 3.4e9");


sendSocketCommand("CALC1:MARK5:STAT ON");
//sendSocketCommand("CALC1:MARK5:TYPE FIXED");
sendSocketCommand("CALC1:MARK5:X 3.5e9");


//COMMANDS FOR WINDOW2
sendSocketCommand("INITiate2:CONTinuous OFF;*OPC?");
sendSocketCommand("SENSe2:SWEep:TRIGger:POINt OFF");

//sendSocketCommand("SENSe2:SWEep:POINts ".$points);
sendSocketCommand("INITiate2;*OPC?");
sendSocketCommand("CALCulate2:PARameter:SELect 'Meas1_Phase'");
sendSocketCommand("CALC2:FORM PHASe");
//set the markers
//UNCOMMENT HERE
// $delta=($stopFreq-$startFreq)/($points-1);
// //$freqString="";
// for($z=0;$z<$points;$z++){
// 	$tempFreq=$startFreq+$z*$delta;
// 	sendSocketCommand("CALC2:MARK".$z+6.":STAT ON");
// 	sendSocketCommand("CALC2:MARK".$z+6.":X ".$tempFreq);
// }
//UNCOMMENT HERE
sendSocketCommand("CALC2:MARK6:STAT ON");
sendSocketCommand("CALC2:MARK6:X 3.1e9");


sendSocketCommand("CALC2:MARK7:STAT ON");
sendSocketCommand("CALC2:MARK7:X 3.2e9");


sendSocketCommand("CALC2:MARK8:STAT ON");
sendSocketCommand("CALC2:MARK8:X 3.3e9");

sendSocketCommand("CALC2:MARK9:STAT ON");
sendSocketCommand("CALC2:MARK9:X 3.4e9");

sendSocketCommand("CALC2:MARK10:STAT ON");
sendSocketCommand("CALC2:MARK10:X 3.5e9");
sendSocketCommand("DISP:ENAB ON");
socket_close($socket);
echo "<script>window.history.go(-1)</script>";

?>
