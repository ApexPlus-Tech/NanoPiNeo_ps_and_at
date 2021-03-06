<?php
set_time_limit(0); 
ignore_user_abort(true);
ini_set('max_execution_time', 0);
ini_set('session.gc_maxlifetime', 14400);
session_start();
$host=$_SESSION['ipAddr'];
$startFreq=$_SESSION['startFreq'];
$stopFreq=$_SESSION['stopFreq'];
$points=$_SESSION['points'];
$_SESSION['folder']=$_POST['folder'];
if (empty($host) ){ //if the string is empty i.e. session has expired
	$fileArray=file("/var/www/automation/basic_session_ecal.txt") or die("Could not open basic session file.");
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

function sendandReceiveSocket($cmdString,&$result){
	$socket=$GLOBALS['socket'];
	$command=$cmdString;   //This command  variable has got value from $_POST variable which has been passed from gui page by user
	//$directory=$_POST['folder'];
	//$command="*IDN?";	
    $command="$command"."\n";      //concatenating the command with newline character
	//
	//Edit these lines  
	//it has sent the command to SCPI server 
	socket_write($socket, $command, strlen($command)) or die("Could not send data to server\n");
	//$result="";
	//$result=socket_read($socket,1024);
	socket_recv($socket, $result,1024,MSG_WAITALL);
	//socket_recv($socket,$result,1024,MSG_DONTWAIT);
	//socket_recv($socket,$result,1024,MSG_OOB);
	//socket_recv($socket,$result,1024,MSG_WAITALL|MSG_DONTWAIT);
	//$result=socket_read($socket,1024);
			
}
function sendandReceiveSocket1($cmdString,&$result){
        $socket=$GLOBALS['socket'];
        $command=$cmdString;   //This command  variable has got value from $_POST variable which has been passed from gui page by user
        //$directory=$_POST['folder'];
        //$command="*IDN?";
    $command="$command"."\n";      //concatenating the command with newline character
        //
        //Edit these lines
        //it has sent the command to SCPI server
        socket_write($socket, $command, strlen($command)) or die("Could not send data to server\n");
        //$result="";
        socket_recv($socket, $result,1024,MSG_WAITALL);
       // socket_recv($socket,$result,1024,MSG_DONTWAIT);
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
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");  //it creates a tcp socket


socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));  //    if it doesn,t get reply from server it will close its connection after 5 sec
$result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");   //it has connected to server and stores its connection link in result
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
//sendSocketCommand("*IDN?",$result);
//check if the result contains a PNA in the output
$result=strtolower($result);
$scpiServerCheckFlag=strpos($result,"pna");
$warmUp=$_POST["warmUp"];
$warmUp=intval($warmUp);
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
		//set output format tao phse
		sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
		sendSocketCommand("CALC1:FORM PHASe");
		
		//UNCOMMENT HERE
		for($i=0;$i<0.5;$i=$i+0.5){
			$filename="Attenuator".$i."_PhaseShifter_xx.txt";
			$fp=fopen($dirName."/".$filename,'a');
			$delta=($stopFreq-$startFreq)/($points-1);
			$freqString="";
			for($z=0;$z<$points;$z++){
				$tempFreq=$startFreq+$z*$delta;
				$freqString=$freqString.$tempFreq."\t";
			}
			$firstLine="PhaseState\t$freqString";
			fwrite($fp,$firstLine."\n");
			//set attenuator value 
			$val=$i;
			$val=($val-31.5)*-2;
			$val=ceil($val);
	        $data=dechex($val);
	        $data="W ".$data." 1B\r";
	        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			for($j=0;$j<64;$j++){
				//send phase shifter value
				//UNCOMMENT HERE
				
				$val=$j;
				$data=dechex($val);
				$data="W ".$data." 1A\r";
				exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
				//$filename=$temp."_phaseShifter".$j;
				//echo $filename;
				$result="";
				//trigger a measurement
				//sendSocketCommand("INITiate2;*OPC?") ;
				sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				//store the result in a file 
				if($j==0){
					//store the first value as reference
					$ref=explode(",",$result);
					//phase unwrapping
					for($iter=0;$iter<$points;$iter++){
						if($ref[$iter]<0)
							$ref[$iter]+=360;
						else {
							$ref[$iter]=$ref[$iter];
						}
						if($ref[$iter]>360)
							$ref[$iter]-=360;
					}
				//	echo $ref;
					//$ref=array(50,50,50,50,50);
					$temp="0\t";
					for($z=0;$z<$points;$z++){
						$temp=$temp."0\t";
					}
					fwrite($fp,$temp."\n");
					outputProgress(((2*$i+1)*($j+1))/2,1*64,2*$i,$j);
				//	//print_r($ref);
				}
				else{
                                //store the result in a file
					$result=str_replace("\n","",$result); 
					$tempValue=explode(",",$result);
					for($iter=0;$iter<$points;$iter++){
						$tempValue[$iter]=floatval($ref[$iter])-floatval($tempValue[$iter]);
						if($tempValue[$iter]<0)
							$tempValue[$iter]+=360;
						else {
							$tempValue[$iter]=$tempValue[$iter];
						}
						if($tempValue[$iter]>360)
							$tempValue[$iter]-=360;
					}
					$result=implode("\t",$tempValue);
				//store the result in a file 
				$result=str_replace(",", "\t", $result);
				$result=str_replace("\n","",$result);
				fwrite($fp,($j*5.625)."\t".$result."\n");
				outputProgress(((2*$i+1)*($j+1))/2 ,1*64,2*$i,$j);
				}
				
				//UNCOMMENT HERE
				//get sweep time and sleep for that time .
				//sleep(1);
			}//phase shifter loop ends here
			fclose($fp);
			$fullFilePath=$dirName.'/'.$filename;
			exec('/usr/bin/python /var/www/automation/rms.py "'.$fullFilePath.'"');   
		}//attenuator loop ends here

		sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
		sendSocketCommand("CALC1:FORM MLOG");
		for($i=0;$i<1;$i++){
			$filename="PhaseShifter".$i."_Attenuator_xx.txt";
			$fp=fopen($dirName."/".$filename,'a');
			$firstLine="AttenuatorState\t$freqString";		
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
				//sendSocketCommand("INITiate1;*OPC?") ;
				sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				if($j==0){
					//store the first value as reference
					$ref=explode(",",$result);
				//	echo $ref;
					//$ref=array(50,50,50,50,50);
					$temp="0\t";
					for($z=0;$z<$points;$z++){
						$temp=$temp."0\t";
					}
					fwrite($fp,$temp."\n");
					outputProgress((($i+1)*(2*$j+1))/2 + 64/2,1*64,2*$j,$i);
				//	//print_r($ref);
				}
				else{
                                //store the result in a file
					$result=str_replace("\n","",$result); 
					$tempValue=explode(",",$result);
					for($iter=0;$iter<$points;$iter++){
						$tempValue[$iter]=floatval($ref[$iter])-floatval($tempValue[$iter]);
					}
					$result=implode("\t",$tempValue);
				//store the result in a file 
				$result=str_replace(",", "\t", $result);
				$result=str_replace("\n","",$result);
				fwrite($fp,($j)."\t".$result."\n");
				outputProgress((($i+1)*(2*$j+1))/2 + 64/2,1*64,2*$j,$i);
				}
			}//Attenuator for loop ends here 
			//echo $filename;
			fclose($fp);
			$fullFilePath=$dirName.'/'.$filename;
			exec('/usr/bin/python /var/www/automation/rms.py "'.$fullFilePath.'"');
			//exec('/usr/bin/python /var/www/automation/boards
		}
		//sendSocketCommand("SENS1:PULS1 0");
		//sendSocketCommand("SENS:SWE:PULS:MODE OFF");//turn OFF the pulse		        	
	}
	elseif ($channelFunction=="CH1_RX"){
		$fileLogger=fopen("logger.txt","w");
		//set TX mode 
		$data="W CD 21\r";
		exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		 //only channel2 select
		$data="W 01 1E\r";
		exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
		//sendSocketCommand("OUTP ON");
		sendSocketCommand("sense1:correction:cset:activate 'Rx_Ecal_Pulse',1");
		sleep($warmUp);
		sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
		sendSocketCommand("CALC1:FORM PHASe");
		//die("Killed");
		for($i=0;$i<0.5;$i=$i+0.5){
			$filename="Attenuator".$i."_PhaseShifter_xx.txt";
			$fp=fopen($dirName."/".$filename,'a');
			$delta=($stopFreq-$startFreq)/($points-1);
			$freqString="";
			for($z=0;$z<$points;$z++){
				$tempFreq=$startFreq+$z*$delta;
				$freqString=$freqString.$tempFreq."\t";
			}
			$firstLine="PhaseState\t$freqString";
			fwrite($fp,$firstLine."\n");
			//set attenuator value 
			$val=$i;
			$val=($val-31.5)*-2;
			$val=ceil($val);
	        $data=dechex($val);
	        $data="W ".$data." 24\r";
	        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			for($j=0;$j<64;$j++){
				//send phase shifter value
				$val=$j;
				$data=dechex($val);
				$data="W ".$data." 23\r";
				exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
				//$filename=$temp."_phaseShifter".$j;
				//echo $filename;
				$result="";
				//sendSocketCommand("INITiate2;*OPC?") ;
				if($j == 0){
					for($myIter=0;$myIter<3;$myIter++){
						sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
                                		sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
					}
				}
				sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				fwrite($fileLogger,$result);	
				//outputProgress(((2*$i+1)*($j+1))/2 ,1*64,2*$i,$j);
				//store the result in a file 
				if($j==0){
					//store the first value as reference
					$referenceString=$result;
					$referenceString=str_replace(",","\t",$referenceString);
					$referenceString=str_replace("\n","",$referenceString);
					$ref=explode(",",$result);
				//	echo $ref;
					//$ref=array(50,50,50,50,50);
					//phase unwrapping
					for($iter=0;$iter<$points;$iter++){
						if($ref[$iter]<0)
							$ref[$iter]+=360;
						else {
							$ref[$iter]=$ref[$iter];
						}
						if($ref[$iter]>360)
							$ref[$iter]-=360;
					}
					$temp="0\t";
					for($z=0;$z<$points;$z++){
						$temp=$temp."0\t";
					}
					//fwrite($fp,$temp."\n");
					fwrite($fp,"$temp\n");
					outputProgress(((2*$i+1)*($j+1))/2,1*64,2*$i,$j);
				//	//print_r($ref);
				}
				else{
                                //store the result in a file
					$result=str_replace("\n","",$result); 
					$tempValue=explode(",",$result);
					for($iter=0;$iter<$points;$iter++){
						$tempValue[$iter]=floatval($ref[$iter])-floatval($tempValue[$iter]);
						if($tempValue[$iter]<0)
							$tempValue[$iter]+=360;
						else {
							$tempValue[$iter]=$tempValue[$iter];
						}
						if($tempValue[$iter]>360)
							$tempValue[$iter]-=360;
					}
					$result=implode("\t",$tempValue);
				//store the result in a file 
				$result=str_replace(",", "\t", $result);
				$result=str_replace("\n","",$result);
				fwrite($fp,($j*5.625)."\t".$result."\n");
				outputProgress(((2*$i+1)*($j+1))/2 ,1*64,2*$i,$j);
				}
				//get sweep time and sleep for that time .
				//sleep(1);
			}//phase shifter loop ends here 
			fclose($fp);
			fclose($fileLogger);
			$fullFilePath=$dirName.'/'.$filename;
			exec('/usr/bin/python /var/www/automation/rms.py "'.$fullFilePath.'"');  
		}//attenuator loop ends here
		sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
		sendSocketCommand("CALC1:FORM MLOG");
		for($i=0;$i<1;$i++){
			$filename="PhaseShifter".$i."_Attenuator_xx.txt";
			$fp=fopen($dirName."/".$filename,'a');
			$firstLine="AttenuatorState\t$freqString";		
			fwrite($fp,$firstLine."\n"); 
			//send phase shifter value
			$val=$i;
			$data=dechex($val);
			$data="W ".$data." 23\r";
			exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			for($j=63;$j>=0;$j-=1){
				//set attenuator value 
				//$val=$j;
				///$val=($val-31.5)*-2;
				//$val=ceil($val);
			$val=$j;
		       	$data=dechex($val);
		        $data="W ".$data." 24\r";
		        
		        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
				//sendSocketCommand("INITiate1;*OPC?") ;
				sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				//store the result in a file 
				if($j==63){
					//store the first value as reference
					$ref=explode(",",$result);
				//	echo $ref;
					//$ref=array(50,50,50,50,50);
					$temp="0\t";
					for($z=0;$z<$points;$z++){
						$temp=$temp."0\t";
					}
					fwrite($fp,$temp."\n");
					outputProgress((($i+1)*(63-$j+1))/2 + 64/2,1*64,63-$j,$i);
				//	//print_r($ref);
				}
				else{
                                //store the result in a file
					$result=str_replace("\n","",$result); 
					$tempValue=explode(",",$result);
					for($iter=0;$iter<$points;$iter++){
						$tempValue[$iter]=floatval($ref[$iter])-floatval($tempValue[$iter]);
					}
					$result=implode("\t",$tempValue);
				//store the result in a file 
				$result=str_replace(",", "\t", $result);
				$result=str_replace("\n","",$result);
				fwrite($fp,(63-$j)*0.5."\t".$result."\n");
				outputProgress((($i+1)*(63-$j+1))/2 + 64/2,1*64,63-$j,$i);
				}				
			}//attenuator loop ends here
			fclose($fp);
			$fullFilePath=$dirName.'/'.$filename;
			exec('/usr/bin/python /var/www/automation/rms.py "'.$fullFilePath.'"');
		}
		//sendSocketCommand("OUTP OFF");
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
		sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
		sendSocketCommand("CALC1:FORM PHASe");
		for($i=0;$i<0.5;$i=$i+0.5){
			$filename="Attenuator".$i."_PhaseShifter_xx.txt";
			$fp=fopen($dirName."/".$filename,'a');
			$delta=($stopFreq-$startFreq)/($points-1);
			$freqString="";
			for($z=0;$z<$points;$z++){
				$tempFreq=$startFreq+$z*$delta;
				$freqString=$freqString.$tempFreq."\t";
			}
			$firstLine="PhaseState\t$freqString";
			fwrite($fp,$firstLine."\n");
			//set attenuator value 
			$val=$i;
			$val=($val-31.5)*-2;
			$val=ceil($val);
	        $data=dechex($val);
	        $data="W ".$data." 1D\r";
	        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			for($j=0;$j<64;$j++){
				//send phase shifter value
				$val=$j;
				$data=dechex($val);
				$data="W ".$data." 1C\r";
				exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
				//$filename=$temp."_phaseShifter".$j;
				//echo $filename;
				$result="";
				//sendSocketCommand("INITiate2;*OPC?") ;
				sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				//store the result in a file 
				if($j==0){
					//store the first value as reference
					$ref=explode(",",$result);
					//phase unwrapping
					for($iter=0;$iter<$points;$iter++){
						if($ref[$iter]<0)
							$ref[$iter]+=360;
						else {
							$ref[$iter]=$ref[$iter];
						}
						if($ref[$iter]>360)
							$ref[$iter]-=360;
					}
				//	echo $ref;
					//$ref=array(50,50,50,50,50);
					$temp="0\t";
					for($z=0;$z<$points;$z++){
						$temp=$temp."0\t";
					}
					fwrite($fp,$temp."\n");
					outputProgress(((2*$i+1)*($j+1))/2,1*64,2*$i,$j);
				//	//print_r($ref);
				}
				else{
                                //store the result in a file
					$result=str_replace("\n","",$result); 
					$tempValue=explode(",",$result);
					for($iter=0;$iter<$points;$iter++){
						$tempValue[$iter]=floatval($ref[$iter])-floatval($tempValue[$iter]);
						if($tempValue[$iter]<0)
							$tempValue[$iter]+=360;
						else {
							$tempValue[$iter]=$tempValue[$iter];
						}
						if($tempValue[$iter]>360)
							$tempValue[$iter]-=360;
					}
					$result=implode("\t",$tempValue);
				//store the result in a file 
				$result=str_replace(",", "\t", $result);
				$result=str_replace("\n","",$result);
				fwrite($fp,($j*5.625)."\t".$result."\n");
				outputProgress(((2*$i+1)*($j+1))/2 ,1*64,2*$i,$j);
				}
				//get sweep time and sleep for that time .
				//sleep(1);
			}//phase shifter loop ends here 
			fclose($fp);
			$fullFilePath=$dirName.'/'.$filename;
			exec('/usr/bin/python /var/www/automation/rms.py "'.$fullFilePath.'"');  
		}//attenuator loop ends here
		sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
		sendSocketCommand("CALC1:FORM MLOG");
		for($i=0;$i<1;$i++){
			$filename="PhaseShifter".$i."_Attenuator_xx.txt";
			$fp=fopen($dirName."/".$filename,'a');
			$firstLine="AttenuatorState\t$freqString";		
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
			//sendSocketCommand("INITiate1;*OPC?") ;
			sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
			sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
			//store the result in a file 
			if($j==0){
					//store the first value as reference
					$ref=explode(",",$result);
				//	echo $ref;
					//$ref=array(50,50,50,50,50);
					$temp="0\t";
					for($z=0;$z<$points;$z++){
						$temp=$temp."0\t";
					}
					fwrite($fp,$temp."\n");
					outputProgress((($i+1)*(2*$j+1))/2 + 64/2,1*64,2*$j,$i);
				//	//print_r($ref);
				}
				else{
                                //store the result in a file
					$result=str_replace("\n","",$result); 
					$tempValue=explode(",",$result);
					for($iter=0;$iter<$points;$iter++){
						$tempValue[$iter]=floatval($ref[$iter])-floatval($tempValue[$iter]);
					}
					$result=implode("\t",$tempValue);
				//store the result in a file 
				$result=str_replace(",", "\t", $result);
				$result=str_replace("\n","",$result);
				fwrite($fp,($j)."\t".$result."\n");
				outputProgress((($i+1)*(2*$j+1))/2 + 64/2,1*64,2*$j,$i);
				}			
			}//attenuator loop ends here
			fclose($fp);
			$fullFilePath=$dirName.'/'.$filename;
			exec('/usr/bin/python /var/www/automation/rms.py "'.$fullFilePath.'"');
		}
		//sendSocketCommand("SENS1:PULS1 0");
		//sendSocketCommand("SENS:SWE:PULS:MODE OFF");//turn ON the pulse
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
		

                sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
		sendSocketCommand("CALC1:FORM PHASe");
		for($i=0;$i<0.5;$i=$i+0.5){
			$filename="Attenuator".$i."_PhaseShifter_xx.txt";
			$fp=fopen($dirName."/".$filename,'a');
			$delta=($stopFreq-$startFreq)/($points-1);
			$freqString="";
			for($z=0;$z<$points;$z++){
				$tempFreq=$startFreq+$z*$delta;
				$freqString=$freqString.$tempFreq."\t";
			}
			$firstLine="PhaseState\t$freqString";
			fwrite($fp,$firstLine."\n");
			//set attenuator value 
			$val=$i;
			$val=($val-31.5)*-2;
			$val=ceil($val);
	        $data=dechex($val);
	        $data="W ".$data." 26\r";
	        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			for($j=0;$j<64;$j++){
				//send phase shifter value
				$val=$j;
				$data=dechex($val);
				$data="W ".$data." 25\r";
				exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
				//$filename=$temp."_phaseShifter".$j;
				//echo $filename;
				$result="";
				//sendSocketCommand("INITiate1;*OPC?") ;
                 //               sleep(0.5);
				if($j == 0){
                                        for($myIter=0;$myIter<3;$myIter++){
                                                sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
                                                sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
                                        }
                                }
				sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");
				//if($j==0){
                                  //  for($k=0;$k<1;$k++){
                                    //    sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Phase'");

                                //sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
                                  //       }
				//}
				//else{
				//sendandReceiveSocket1("CALCulate1:DATA? FDATA",$result);
				sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
				//}
				//store the result in a file 
				 if($j==0){
					//store the first value as reference
					$ref=explode(",",$result);
					//phase unwrapping
					for($iter=0;$iter<$points;$iter++){
						if($ref[$iter]<0)
							$ref[$iter]+=360;
						else {
							$ref[$iter]=$ref[$iter];
						}
						if($ref[$iter]>360)
							$ref[$iter]-=360;
					}
				//	echo $ref;
					//$ref=array(50,50,50,50,50);
					$temp="0\t";
					for($z=0;$z<$points;$z++){
						$temp=$temp."0\t";
					}
					fwrite($fp,$temp."\n");
					outputProgress(((2*$i+1)*($j+1))/2,1*64,2*$i,$j);
				//	//print_r($ref);
				}
				else{
                                //store the result in a file
					$result=str_replace("\n","",$result); 
					$tempValue=explode(",",$result);
					for($iter=0;$iter<$points;$iter++){
						$tempValue[$iter]=floatval($ref[$iter])-floatval($tempValue[$iter]);
						if($tempValue[$iter]<0)
							$tempValue[$iter]+=360;
						else {
							$tempValue[$iter]=$tempValue[$iter];
						}
						if($tempValue[$iter]>360)
							$tempValue[$iter]-=360;
					}
					$result=implode("\t",$tempValue);
				//store the result in a file 
				$result=str_replace(",", "\t", $result);
				$result=str_replace("\n","",$result);
				fwrite($fp,($j*5.625)."\t".$result."\n");
				outputProgress(((2*$i+1)*($j+1))/2 ,1*64,2*$i,$j);
				}
				//get sweep time and sleep for that time .
				//sleep(1);
			}//phase shifter loop ends here
		 fclose($fp);
                 $fullFilePath=$dirName.'/'.$filename;
                 exec('/usr/bin/python /var/www/automation/rms.py "'.$fullFilePath.'"');
   
		}//attenuator loop ends here
		sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
		sendSocketCommand("CALC1:FORM MLOG");
		for($i=0;$i<1;$i++){
			$filename="PhaseShifter".$i."_Attenuator_xx.txt";
			$fp=fopen($dirName."/".$filename,'a');
			$firstLine="AttenuatorState\t$freqString";		
			fwrite($fp,$firstLine."\n"); 
			//send phase shifter value
			$val=$i;
			$data=dechex($val);
			$data="W ".$data." 25\r";
			exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
			for($j=63;$j>=0;$j-=1){
				//set attenuator value 
				//$val=$j;
				///$val=($val-31.5)*-2;
				//$val=ceil($val);
			$val=$j;
		       	$data=dechex($val);
		        $data="W ".$data." 26\r";
		        
		        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
				//sendSocketCommand("INITiate1;*OPC?") ;
				sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");
				//sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
 				//if($j==0){
                                  //  for($k=0;$k<2;$k++){
                                    //    sendSocketCommand("CALCulate1:PARameter:SELect 'Meas1_Amp'");

                                //sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
                                  //       }
                                //}
                                //else{
                                //sendandReceiveSocket1("CALCulate1:DATA? FDATA",$result);
                                sendandReceiveSocket("CALCulate1:DATA? FDATA",$result);
                                //}
	
				//store the result in a file 
				if($j==63){
					//store the first value as reference
					$ref=explode(",",$result);
				//	echo $ref;
					//$ref=array(50,50,50,50,50);
					$temp="0\t";
					for($z=0;$z<$points;$z++){
						$temp=$temp."0\t";
					}
					fwrite($fp,$temp."\n");
					outputProgress((($i+1)*(63-$j+1))/2 + 64/2,1*64,63-$j,$i);
				//	//print_r($ref);
				}
				else{
                                //store the result in a file
					$result=str_replace("\n","",$result); 
					$tempValue=explode(",",$result);
					for($iter=0;$iter<$points;$iter++){
						$tempValue[$iter]=floatval($ref[$iter])-floatval($tempValue[$iter]);
					}
					$result=implode("\t",$tempValue);
				//store the result in a file 
				$result=str_replace(",", "\t", $result);
				$result=str_replace("\n","",$result);
				fwrite($fp,(63-$j)*0.5."\t".$result."\n");
				outputProgress((($i+1)*(63-$j+1))/2 + 64/2,1*64,63-$j,$i);
				}					
			}//attenuator loop ends here
		 fclose($fp);
                 $fullFilePath=$dirName.'/'.$filename;
                 exec('/usr/bin/python /var/www/automation/rms.py "'.$fullFilePath.'"');

		}
		//sendSocketCommand("OUTP OFF");
	}
	socket_close($socket);
	echo("<script>window.location='/automation/zip.php'</script>");
 }
}
else{
	die("incorrect response .Instrument not a PNA");
}
?>
