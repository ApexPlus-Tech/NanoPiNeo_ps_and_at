<?php
ini_set('session.gc_maxlifetime', 7200);
session_start();
$host=$_SESSION['ipAddr'];
$_SESSION['folder']=$_POST['folder'];




/**
 * Output span with progress.
 *
 * @param $current integer Current progress out of total
 * @param $total   integer Total steps required to complete
 */
//
function outputProgress($current, $total,$attenuator,$phaseShifter) {
    echo "<span style='position: absolute;z-index:$current;background:#FFF;'>" ."Attenuator:".$attenuator." PhaseShifter:".$phaseShifter." Progress:".round($current / $total * 100) . "% </span>";
    myFlush();
    //sleep(1);
}

/**
 * Flush output buffer
 */
function myFlush() {
    echo(str_repeat(' ', 256));
    if (@ob_get_contents()) {
        @ob_end_flush();
    }
    flush();
}

function sendSocketCommand($cmdString,&$result){
	$host=$GLOBALS['host'];
	$command=$cmdString;   //This command  variable has got value from $_POST variable which has been passed from gui page by user
	$directory=$_POST['folder'];
	//$command="*IDN?";	
    $command="$command"."\n";      //concatenating the command with newline character

	$port=5025;   //here the port no is 5025.it is not random
	//SCPI is a protocol built over top of TCP which listens on specific port ,which is 5025 or 5024 by default
	//So ,I have created a tcp socket for iv4
	//SOCK_STREAM is for TCP and SOCK_DGRAM is for UDP
	//
	//Edit these lines  
	$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");  //it creates a tcp socket


	socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>5, "usec"=>0));  //    if it doesn,t get reply from server it will close its connection after 5 sec
	$result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");   //it has connected to server and stores its connection link in result

	//it has sent the command to SCPI server 
	socket_write($socket, $command, strlen($command)) or die("Could not send data to server\n");
	//$result="";
	socket_recv($socket, $result,12438,MSG_WAITALL);
	socket_close($socket);		
}

//get the value of channel radio button 
//echo $host;
$channelFunction=$_POST['channel'];
//create a folder 
$directory=$_POST['folder'];
//write the name 
$f=fopen("/var/www/automation/session.txt",'w') or die("Could not open text file ");
fwrite($f,$directory);
fclose($f);
$dirName="/var/www/automation/boards/".$directory;
if(!is_dir($dirName)){
		mkdir($dirName,0777,true);
}
//echo $channelFunction;
//set TRP GUI mode 
$data="W 45 22\r";
exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
//set TRP GUI mode 
$data="W 45 22\r";
exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
//echo "Something<br/>";
$result="";
//send *idn command to the instrument 
sendSocketCommand("*IDN?",$result);
//check if the result contains a PNA in the output
$result=strtolower($result);
$scpiServerCheckFlag=strpos($result,"pna");

//Note use === instead of ==
if($scpiServerCheckFlag===true  ){
if ($channelFunction=="CH1_TX"){
	//set TX mode 
	$data="W AB 21\r";
	exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	 //only channel1 select
	$data="W 01 1E\r";
	exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	for($i=0;$i<1;$i++){
		//set attenuator value 
		$val=$i;
		$val=($val-31.5)*-2;
		$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 1B\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
        $temp='attenuator'.$i;
		for($j=0;$j<1;$j++){
			//send phase shifter value
			$val=$j;
			$data=dechex($val);
			$data="W ".$data." 1A\r";
			exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			$filename=$temp."_phaseShifter".$j;
			//echo $filename;
			$result="";
			sendSocketCommand("INITiate1;*OPC?",$result) ;
			sendSocketCommand("CALCulate1:DATA?FDATA",$result);
			//store the result in a file 
			$fp=fopen($dirName."/".$filename,'w');
			fwrite($fp,$result);
			fclose($fp);
			outputProgress(($i+1)*($j+1),1*1,$i,$j);
			//get sweep time and sleep for that time .
			sleep(1);
		}   
	}
}
elseif ($channelFunction=="CH1_RX"){
	//set RX mode 
	$data="W CD 21\r";
	exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	 //only channel1 select
	$data="W 01 1E\r";
		exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	for($i=0;$i<1;$i++){
		//set attenuator value 
		$val=$i;
		$val=($val-31.5)*-2;
		$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 24\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
        $temp='attenuator'.$i;
		for($j=0;$j<1;$j++){
			//send phase shifter value
			$val=$j;
			$data=dechex($val);
			$data="W ".$data." 23\r";
			exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			$filename=$temp."_phaseShifter".$j;
			//echo $filename;
			$result="";
			sendSocketCommand("INITiate1;*OPC?",$result) ;
			sendSocketCommand("CALCulate1:DATA?FDATA",$result);
			//store the result in a file 
			$fp=fopen($dirName."/".$filename,'w');
			fwrite($fp,$result);
			fclose($fp);
			outputProgress(($i+1)*($j+1),1*1,$i,$j);	
			//get sweep time and sleep for that time .
			sleep(1);
		}   
	}
}
elseif ($channelFunction=="CH2_TX"){
	//set TX mode 
	$data="W AB 21\r";
	exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	 //only channel1 select
	$data="W 02 1E\r";
	exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	for($i=0;$i<1;$i++){
		//set attenuator value 
		$val=$i;
		$val=($val-31.5)*-2;
		$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 1D\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
        $temp='attenuator'.$i;
		for($j=0;$j<1;$j++){
			//send phase shifter value
			$val=$j;
			$data=dechex($val);
			$data="W ".$data." 1C\r";
			exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			$filename=$temp."_phaseShifter".$j;
			//echo $filename;
			$result="";
			sendSocketCommand("INITiate1;*OPC?",$result) ;
			sendSocketCommand("CALCulate1:DATA?FDATA",$result);
			//store the result in a file 
			$fp=fopen($dirName."/".$filename,'w');
			fwrite($fp,$result);
			fclose($fp);
			outputProgress(($i+1)*($j+1),1*1,$i,$j);	
			//get sweep time and sleep for that time .
			sleep(1);
		}   
	}
}
elseif ($channelFunction=="CH2_RX"){
	//set TX mode 
	$data="W CD 21\r";
	exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	 //only channel2 select
	$data="W 02 1E\r";
	exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	for($i=0;$i<1;$i++){
		//set attenuator value 
		$val=$i;
		$val=($val-31.5)*-2;
		$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 26\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
        $temp='attenuator'.$i;
		for($j=0;$j<1;$j++){
			//send phase shifter value
			$val=$j;
			$data=dechex($val);
			$data="W ".$data." 25\r";
			exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			$filename=$temp."_phaseShifter".$j;
			//echo $filename;
			$result="";
			sendSocketCommand("INITiate1;*OPC?",$result) ;
			sendSocketCommand("CALCulate1:DATA?FDATA",$result);
			//store the result in a file 
			$fp=fopen($dirName."/".$filename,'w');
			fwrite($fp,$result);
			fclose($fp);
			outputProgress(($i+1)*($j+1),1*1,$i,$j);	
			//get sweep time and sleep for that time .
			sleep(1);
		}   
	}
}
echo("<script>window.location='/automation/zip.php'</script>");
}
else{
	die("incorrect response .Instrument not a PNA");
}
?>