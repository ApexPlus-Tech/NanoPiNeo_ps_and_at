<?php
session_start();
//$host=$_POST['ipAddr'];
$host=$_SESSION['ipAddr'];
$startFreq=$_SESSION['startFreq'];
$stopFreq=$_SESSION['stopFreq'];
$points=$_SESSION['points'];
$value=$_SESSION['value'];
if (empty($host) ){ //if the string is empty i.e. session has expired
	$fileArray=file("/var/www/automation/basic_session_ecal.txt") or die("Could not open basic session file.");
	$host=str_replace(array("\n","\r"),'', $fileArray[0]);
	$startFreq=str_replace(array("\n","\r"),'', $fileArray[1]);
	$stopFreq=str_replace(array("\n","\r"),'', $fileArray[2]);
	$points=str_replace(array("\n","\r"),'', $fileArray[3]);
}
session_write_close();
echo "Host:".$host.";startFreq:".$startFreq.";stopFreq:".$stopFreq.".points:".$points;
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

sendSocketCommand("SENS:CORR:COLL:METH SPARSOLT");
sendSocketCommand("SENS:CORR:PREF:ECAL:ORI ON");
sendSocketCommand("SENS:CORR:COLL:ACQ ECAL1,CHAR0;*OPC?");
sendSocketCommand("sense1:correction:cset:delete 'Rx_Ecal_Pulse'");
sendSocketCommand("sense1:correction:cset:name 'Rx_Ecal_Pulse'");
//sendSocketCommand("SOUR1:POW ".$value);
//sendSocketCommand("SOUR2:POW ".$value);

//echo "<script>alert('ECAL calibration1 done . Connect DUT')</script>";



//sendSocketCommand("CALC2:PAR:DEF 'Meas1_Phase',S21");
//sendSocketCommand("DISP:WIND2:STAT ON");
//sendSocketCommand("DISP:WIND2:TRAC2:FEED 'Meas1_Phase'");
//sendSocketCommand("SOUR2:POW ".$value);
//sendSocketCommand("SENS2:SWE:PULS:MODE STD");//turn ON the pulse

//echo $startFreq;
//echo $stopFreq;
//sendSocketCommand("SENS2:FREQuency:STARt ".$startFreq);
//sleep(1);
//sendSocketCommand("SENS2:FREQuency:STOP ".$stopFreq);
//sendSocketCommand("CALC2:PAR:SEL 'Meas1_Phase'");
//sendSocketCommand("SENS2:CORR:COLL:CKIT:INF? ECAL1,CHAR0");
//sendSocketCommand("SOUR2:POW ".$value);
//sendSocketCommand("SENS2:CORR:COLL:CKIT:INF? ECAL1,CHAR0");
//sendSocketCommand("SENS2:CORR:COLL:CKIT:INF? ECAL1,CHAR0");

socket_close($socket);
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");  //it creates a tcp socket
socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));  //    if it doesn,t get reply from server it will close its connection after 5 sec
$result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");   //it has connected to server and stores its connection link in result
//sendSocketCommand("SENS2:CORR:COLL:METH SPARSOLT");
//sendSocketCommand("SENS2:CORR:PREF:ECAL:ORI ON");
//sendSocketCommand("SENS2:CORR:COLL:ACQ ECAL1,CHAR0;*OPC?");




//sendSocketCommand("SENSe2:FREQ:STOP ".$stopFreq);
echo "<script>alert('ECAL calibration2 done . Connect DUT')</script>";
echo "<script>window.history.go(-1)</script>";
socket_close($socket);
//echo "<script>window.location='ecal.html'</script>";
//echo "Configuration done."
?>
