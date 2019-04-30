<?php
session_start();
set_time_limit(0); 
ignore_user_abort(true);
ini_set('max_execution_time', 0);
ini_set('session.gc_maxlifetime', 14400);
session_start();
$host=$_POST['ipAddr'];
$_SESSION['ipAddr']=$_POST['ipAddr'];
$_SESSION['startFreq']=$_POST['startFreq'];
$_SESSION['stopFreq']=$_POST['stopFreq'];
$_SESSION['value']=$_POST['powerlevel'];
$_SESSION['txpowerlevel']=$_POST['txpowerlevel'];
$_SESSION['rxpowerlevel']=$_POST['rxpowerlevel'];
$_SESSION['pulsewidth']=$_POST['pulsewidth'];
$_SESSION['dutycycle']=$_POST['dutycycle'];
$_SESSION['points']=$_POST['points'];

$startFreq=$_POST['startFreq'];
$stopFreq=$_POST['stopFreq'];

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
//ECAL commands

//FPRESET
sendSocketCommand("SYSTem:FPRESET");

//Trace Initializations
sendSocketCommand("CALC1:PAR:DEF 'Meas1_Amp',S21");
sendSocketCommand("DISP:WIND1:STAT ON");
sendSocketCommand("DISP:WIND1:TRAC1:FEED 'Meas1_Amp'");
sendSocketCommand("CALC1:PAR:SEL 'Meas1_Amp'");
sendSocketCommand("CALC1:FORM MLOG");
sendSocketCommand("CALC1:PAR:DEF 'Meas1_Phase',S21");
sendSocketCommand("DISP:WIND2:STAT ON");
sendSocketCommand("DISP:WIND2:TRAC1:FEED 'Meas1_Phase'");
sendSocketCommand("CALC1:PAR:SEL 'Meas1_Phase'");
sendSocketCommand("CALC1:FORM PHASe");
sendSocketCommand("SENSe1:FREQuency:STARt " .$startFreq);
//sleep(1);
sendSocketCommand("SENSe1:FREQuency:STOP ".$stopFreq);
$points=$_SESSION['points'];
sendSocketCommand("SENSe1:SWEep:POINts ".$points);

//Enable Pulse Mode
sendSocketCommand("SENS:SWE:PULS:MODE OFF");
$pulsewidth=$_SESSION['pulsewidth'];
$dutycycle=$_SESSION['dutycycle'];
		//echo $dutycycle;
//sendSocketCommand("SENS1:PULS:WIDT ".$pulsewidth);
$pulsePeriod=floatval($pulsewidth)/floatval($dutycycle);
		//echo ($pulsewidth);
//sendSocketCommand("SENS1:PULS:PER ".$pulsePeriod);

$value=$_SESSION['rxpowerlevel'];
sendSocketCommand("SOUR1:POW ".$value);


sendSocketCommand("SENS:CORR:COLL:CKIT:INF? ECAL1,CHAR0");
socket_close($socket);
// //alert ,how to do it in php ?
echo "<script>alert('Please connect ECAL kit without Attenuator')</script>";
echo "<script>window.location='/automation/rxbypass.php'</script>";
// sendSocketCommand("SENS:CORR:COLL:METH SPARSOLT");
// sendSocketCommand("SENS:CORR:PREF:ECAL:ORI ON");
// sendSocketCommand("SENS:CORR:COLL:ACQ ECAL1,CHAR0;*OPC?")
// echo "<script>alert("ECAL calibration done . Connect DUT")</script>"

//echo "Configuration done."
?>
