<?php
session_start();
$host=$_SESSION['ipAddr'];
class GoodZipArchive extends ZipArchive 
{
	//@author Nicolas Heimann
	public function __construct($a=false, $b=false) { $this->create_func($a, $b);  }
	
	public function create_func($input_folder=false, $output_zip_file=false)
	{
		if($input_folder && $output_zip_file)
		{
			$res = $this->open($output_zip_file, ZipArchive::CREATE);
			if($res === TRUE) 	{ $this->addDir($input_folder, basename($input_folder)); $this->close(); }
			else  				{ echo 'Could not create a zip archive. Contact Admin.'; }
		}
	}
	
    // Add a Dir with Files and Subdirs to the archive
    public function addDir($location, $name) {
        $this->addEmptyDir($name);
        $this->addDirDo($location, $name);
    }

    // Add Files & Dirs to archive 
    private function addDirDo($location, $name) {
        $name .= '/';         $location .= '/';
      // Read all Files in Dir
        $dir = opendir ($location);
        while ($file = readdir($dir))    {
            if ($file == '.' || $file == '..') continue;
          // Rekursiv, If dir: GoodZipArchive::addDir(), else ::File();
            $do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
            $this->$do($location . $file, $name . $file);
        }
    } 
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
$dirName="/var/www/html/boards/".$directory;
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
	for($i=0;$i<64;$i++){
		//set attenuator value 
		$val=$i;
		$val=($val-31.5)*-2;
		$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 1B\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
        $temp='attenuator'.$i;
		for($j=0;$j<64;$j++){
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
	for($i=0;$i<64;$i++){
		//set attenuator value 
		$val=$i;
		$val=($val-31.5)*-2;
		$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 24\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
        $temp='attenuator'.$i;
		for($j=0;$j<64;$j++){
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
	for($i=0;$i<64;$i++){
		//set attenuator value 
		$val=$i;
		$val=($val-31.5)*-2;
		$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 1D\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
        $temp='attenuator'.$i;
		for($j=0;$j<64;$j++){
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
	for($i=0;$i<64;$i++){
		//set attenuator value 
		$val=$i;
		$val=($val-31.5)*-2;
		$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 26\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
        $temp='attenuator'.$i;
		for($j=0;$j<64;$j++){
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
			//get sweep time and sleep for that time .
			sleep(1);
		}   
	}
}
$folderName=$_POST['folder'];
$zipFileName='/var/www/html/boards/'.$folderName.'.zip';
$zipFile=new GoodZipArchive('/var/www/html/boards/'. $folderName,$zipFileName)or die("Could not create zip file");
header('Content-Type: application/zip');
header("Content-Disposition: attachment; filename = $zipFileName");
header('Content-Length: ' . filesize($zipFileName));
//header("Location:"."/boards/".basename($zipFileName)) or die("Could not open zip file");
readfile($zipFileName);
unlink($zipFileName);
rmdir('var/www/html/boards/'.$folderName);
}
else{
	die("incorrect response .Instrument not a PNA");
}
?>