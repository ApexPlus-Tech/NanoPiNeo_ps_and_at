<?php
ini_set('max_execution_time', 0);
$host=$_SESSION['ipAddr'];
$startFreq=$_SESSION['startFreq'];
$stopFreq=$_SESSION['stopFreq'];
$points=$_SESSION['points'];
$_SESSION['folder']=$_POST['folder'];
session_write_close();
if (empty($host) ){ //if the string is empty i.e. session has expired
	$fileArray=file("/var/www/automation/basic_session.txt") or die("Could not open basic session file.");
	$host=str_replace(array("\n","\r"),'', $fileArray[0]);
	$startFreq=str_replace(array("\n","\r"),'', $fileArray[1]);
	$stopFreq=str_replace(array("\n","\r"),'', $fileArray[2]);
	$points=str_replace(array("\n","\r"),'', $fileArray[3]);
}



/**
 * Output span with progress.
 *
 * @param $current integer Current progress out of total
 * @param $total   integer Total steps required to complete
 */
//
function outputProgress($current, $total,$attenuator,$phaseShifter) {
    if(isset($_POST['resume'])){
	$string="Resuming for measurement <font color='blue'>".basename($GLOBALS['dirName'])."</font> for channel <font color='blue'>".$GLOBALS['channelFunction']."</font> <br>";	
    }
    echo "<span style='position: absolute;z-index:$current;background:#FFF;'>$string PhaseShifter:".$phaseShifter." Progress:".round($current / $total * 100) . "% </span>";
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

function sendandReceiveSocket($cmdString,&$result){
	$socket=$GLOBALS['socket'];
	$command=$cmdString;   //This command  variable has got value from $_POST variable which has been passed from gui page by user
	//$directory=$_POST['folder'];
	//$command="*IDN?";	
    $command="$command"."\n";      //concatenating the command with newline character
	//
	//Edit these lines  
	//it has sent the command to SCPI server 
	socket_write($socket, $command, strlen($command))  or die("Could not send data to server\n");
	//$result="";
	//$result=socket_read($socket,1024);
	socket_recv($socket, $result,16384,MSG_WAITALL);
	//socket_recv($socket,$result,1024,MSG_DONTWAIT);
	//socket_recv($socket,$result,1024,MSG_OOB);
	//socket_recv($socket,$result,1024,MSG_WAITALL|MSG_DONTWAIT);
	//$result=socket_read($socket,1024);
			
}



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

//get the value of channel radio button 
//echo $host;
$port=5025;   //here the port no is 5025.it is not random
	//SCPI is a protocol built over top of TCP which listens on specific port ,which is 5025 or 5024 by default
	//So ,I have created a tcp socket for iv4
	//SOCK_STREAM is for TCP and SOCK_DGRAM is for UDP
$socket = socket_create(AF_INET, SOCK_STREAM, 0)  or die("Could not create socket\n");  //it creates a tcp socket


socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));  //    if it doesn,t get reply from server it will close its connection after 5 sec
$result = socket_connect($socket, $host, $port)  or die("Could not connect to server\n");   //it has connected to server and stores its connection link in result
$channelFunction=$_POST['channel'];
$resumeFlag = 0;
if(isset($_POST['resume'])){
	$resumeFlag =1;
	//get the folder name
	$f=file("/var/www/automation/session.txt") or die("Could not open session text file");
	$dirName = str_replace(array("\r","\n"),"",$f[0]);
	$dirName="/var/www/automation/boards/".$dirName;
	//read resume progress
        $fp_resume=file("/var/www/automation/resume.txt") or die("Could not open resume text file");
        $resume_progress = str_replace(array("\r","\n"),"",$fp_resume[0]);
	$fp_channel=file("/var/www/automation/channel.txt") or die("Could not open channel text file");
        $channelFunction = str_replace(array("\r","\n"),"",$fp_channel[0]);
	//delete the last file
	exec("rm $dirName/Phase".$resume_progress.".txt");
	//die("Resume progress:".$resume_progress);
	$warmUp = 1;
}
else{
	//create a folder 
	$directory=$_POST['folder'];
	//write the name 
	$file_pointer=fopen("/var/www/automation/session.txt",'w') or die("Could not open session text file ");
	fwrite($file_pointer,$directory);
	fclose($file_pointer);
	$dirName="/var/www/automation/boards/".$directory;
	if(!is_dir($dirName)){
		mkdir($dirName,0777,true);
	}
	else{
 		die("<font color='red'>Board name </font><font color='blue'>".basename($dirName)."</font> <font color='red'>  exists.Please choose another name</font>");	
    }
	$f=fopen("/var/www/automation/channel.txt",'w') or die("Could not open channel text file ");
        fwrite($f,$channelFunction);
        fclose($f);
	$resume_progress=0;
	$warmUp=$_POST["warmUp"];
	$warmUp=intval($warmUp);
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
//sendSocketCommand("*IDN?",$result);
//check if the result contains a PNA in the output
$result=strtolower($result);
$scpiServerCheckFlag=strpos($result,"pna");
//Note use === instead of ==
if(1 || $scpiServerCheckFlag===true  ){
  if(isset($channelFunction)){
	if ($channelFunction=="CH1_TX"){
		//UNCOMMENT HERE
		//set TX mode 
		$data="W AB 21\r";
		exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		 //only channel1 select
		$data="W 01 1E\r";
		exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		//set pulsed mode on PNA
		//sendSocketCommand("SENS:SWE:PULS:MODE STD");//turn ON the pulse
		//sendSocketCommand("SENS1:PULS1 1");
		sendSocketCommand("sense1:correction:cset:activate 'Tx_Ecal_Pulse',1");
		sleep($warmUp);
		for($i=$resume_progress;$i<1;$i++){
			//write the name
			//write the name
        		$resume_file=fopen("/var/www/automation/resume.txt",'w') or die("Could not open text file ");
        		fwrite($resume_file,$i);
        		fclose($resume_file);
			$filename="Phase".$i.".txt";
			$fp=fopen($dirName."/".$filename,'a');
			$delta=($stopFreq-$startFreq)/($points-1);
			$freqString="";
			for($z=0;$z<$points;$z++){
				$tempFreq=$startFreq+$z*$delta;
				$freqString=$freqString.$tempFreq."\t";
			}
			$firstLine="\t$freqString";
			fwrite($fp,$firstLine."\n"); 
			//send phase shifter value
			$val=$i;
			$data=dechex($val);
			$data="W ".$data." 1A\r";
			exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			for($j=0;$j<=31.5;$j=$j+0.5){
			//set attenuator value 
				$val=$j;
				$val=($val-31.5)*-2;
				$val=ceil($val);
		       	$data=dechex($val);
		        $data="W ".$data." 1B\r";
		        
		        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		        sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
		        usleep(500);
				sendSocketCommand("CALC1:FORM PHASe");
				usleep(500);
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				usleep(1000);
				$result=str_replace("\n","",$result);
				fwrite($fp,$j."\t".$result."\n");
				//sendSocketCommand("INITiate1;*OPC?") ;

				sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
				usleep(500);
				sendSocketCommand("CALC1:FORM MLOG");
				usleep(500);
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				usleep(1000);
				//strip /n 
				$result=str_replace("\n","",$result);
				fwrite($fp,"\t".$result."\n");


				outputProgress((($i+1)),4,2*$j,$i);
				}
				fclose($fp);
			}//Phase shifter for loop ends here 
			
		}		        	
	elseif ($channelFunction=="CH1_RX"){
		//set TX mode 
		$data="W CD 21\r";
		exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		 //only channel2 select
		$data="W 01 1E\r";
		exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		//sendSocketCommand("OUTP ON");
		sendSocketCommand("sense1:correction:cset:activate 'Rx_Ecal_Pulse',1");
		sleep($warmUp);
		for($i=$resume_progress;$i<4;$i++){
			$resume_file=fopen("/var/www/automation/resume.txt",'w') or die("Could not open text file ");
                        fwrite($resume_file,$i);
                        fclose($resume_file);
			$filename="Phase".$i.".txt";
			$fp=fopen($dirName."/".$filename,'a');
			$delta=($stopFreq-$startFreq)/($points-1);
			$freqString="";
			for($z=0;$z<$points;$z++){
				$tempFreq=$startFreq+$z*$delta;
				$freqString=$freqString.$tempFreq."\t";
			}
			$firstLine="\t$freqString";
			fwrite($fp,$firstLine."\n"); 
			//send phase shifter value
			$val=$i;
			$data=dechex($val);
			$data="W ".$data." 23\r";
			exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			for($j=0;$j<=1.5;$j=$j+0.5){
			//set attenuator value 
				$val=$j;
				$val=($val-31.5)*-2;
				$val=ceil($val);
		       	$data=dechex($val);
		        $data="W ".$data." 24\r";
		        
		        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		        sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
		       // usleep(500);
				sendSocketCommand("CALC1:FORM PHASe");
				//usleep(500);
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				//usleep(1000);
				$result=str_replace("\n","",$result);
				$result=str_replace(",","\t",$result);
				fwrite($fp,$j."\t".$result."\n");
				//sendSocketCommand("INITiate1;*OPC?") ;

				sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
				//usleep(500);
				sendSocketCommand("CALC1:FORM MLOG");
				//usleep(500);
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				//usleep(1000);
				//strip /n 
				$result=str_replace("\n","",$result);
				$result=str_replace(",","\t",$result);
				fwrite($fp,"\t".$result."\n");


				outputProgress((($i+1)),4,2*$j,$i);
				}
				fclose($fp);
			}//Phase shifter for loop ends here 
	}
	elseif ($channelFunction=="CH2_TX"){
		//set TX mode 
		$data="W AB 21\r";
		exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		 //only channel1 select
		$data="W 02 1E\r";
		exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		//set pulsed mode on PNA
		//sendSocketCommand("SENS:SWE:PULS:MODE STD");//turn ON the pulse
		//sendSocketCommand("SENS1:PULS1 1");
		sendSocketCommand("sense1:correction:cset:activate 'Tx_Ecal_Pulse',1");
		sleep($warmUp);
		
		for($i=$resume_progress;$i<1;$i++){
			$resume_file=fopen("/var/www/automation/resume.txt",'w') or die("Could not open text file ");
                        fwrite($resume_file,$i);
                        fclose($resume_file);
			$filename="Phase".$i.".txt";
			$fp=fopen($dirName."/".$filename,'a');
			$delta=($stopFreq-$startFreq)/($points-1);
			$freqString="";
			for($z=0;$z<$points;$z++){
				$tempFreq=$startFreq+$z*$delta;
				$freqString=$freqString.$tempFreq."\t";
			}
			$firstLine="\t$freqString";
			fwrite($fp,$firstLine."\n"); 
			//send phase shifter value
			$val=$i;
			$data=dechex($val);
			$data="W ".$data." 1C\r";
			exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			for($j=0;$j<=31.5;$j=$j+0.5){
			//set attenuator value 
				$val=$j;
				$val=($val-31.5)*-2;
				$val=ceil($val);
		       	$data=dechex($val);
		        $data="W ".$data." 1D\r";
		        
		        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		        sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
		        usleep(500);
				sendSocketCommand("CALC1:FORM PHASe");
				usleep(500);
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				usleep(1000);
				$result=str_replace("\n","",$result);
				fwrite($fp,$j."\t".$result."\n");
				//sendSocketCommand("INITiate1;*OPC?") ;

				sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
				usleep(500);
				sendSocketCommand("CALC1:FORM MLOG");
				usleep(500);
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				usleep(1000);
				//strip /n 
				$result=str_replace("\n","",$result);
				fwrite($fp,"\t".$result."\n");


				outputProgress((($i+1)),4,2*$j,$i);
				}
				fclose($fp);
			}//Phase shifter for loop ends here 
	}
	elseif ($channelFunction=="CH2_RX"){
		//set TX mode 
		$data="W CD 21\r";
		exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		 //only channel2 select
		$data="W 02 1E\r";
		exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		sendSocketCommand("sense1:correction:cset:activate 'Rx_Ecal_Pulse',1");
                //sendSocketCommand("SENS1:SWE:POINts 5");
		//sendSocketCommand("OUTP ON");
		sleep($warmUp);
		for($i=$resume_progress;$i<4;$i++){
			$resume_file=fopen("/var/www/automation/resume.txt",'w') or die("Could not open text file ");
                        fwrite($resume_file,$i);
                        fclose($resume_file);
			$filename="Phase".$i.".txt";
			$fp=fopen($dirName."/".$filename,'a');
			$delta=($stopFreq-$startFreq)/($points-1);
			$freqString="";
			for($z=0;$z<$points;$z++){
				$tempFreq=$startFreq+$z*$delta;
				$freqString=$freqString.$tempFreq."\t";
			}
			$firstLine="\t$freqString";
			fwrite($fp,$firstLine."\n"); 
			//send phase shifter value
			$val=$i;
			$data=dechex($val);
			$data="W ".$data." 25\r";
			exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			for($j=0;$j<=1.5;$j=$j+0.5){
			//set attenuator value 
				$val=$j;
				$val=($val-31.5)*-2;
				$val=ceil($val);
		       	$data=dechex($val);
		        $data="W ".$data." 26\r";
		        
		        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		        sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
				sendSocketCommand("CALC1:FORM PHASe");
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				$result=str_replace("\n","",$result);
				$result=str_replace(",","\t",$result);
				fwrite($fp,$j."\t".$result."\n");
				//sendSocketCommand("INITiate1;*OPC?") ;

				sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
				sendSocketCommand("CALC1:FORM MLOG");
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				//strip /n 
				$result=str_replace("\n","",$result);
				$result=str_replace(",","\t",$result);
				fwrite($fp,"\t".$result."\n");
				
				outputProgress((($i+1)),4,2*$j,$i);
				}
				fclose($fp);
			}//Phase shifter for loop ends here 
	}
	socket_close($socket);
	echo("<script>window.location='/automation/zip2.php'</script>");
 }
}
else{
	die("incorrect response .Instrument not a PNA");
}
?>
