<?php
//echo "something";
//check if  a button is pressed
if(isset($_POST['TRP'])){
	if($_POST['choice1']=='TRP_BNC'){
	$data="W 54 22\r";
	exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	}
	elseif($_POST['choice1']=='TRP_GUI'){
	$data="W 45 22\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	}
}
elseif(isset($_POST['MODE'])){
	if($_POST['choice2']=='RX_MODE'){
	$data="W CD 21\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	}
	elseif($_POST['choice2']=='TX_MODE'){
	 $data="W AB 21\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	}
}
elseif(isset($_POST['channelSelect'])){
	if($_POST['channel']=='channel1'){
	$data="W 01 1E\r";
	exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	}
	elseif($_POST['channel']=='channel2'){
	$data="W 02 1E\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	}
	elseif($_POST['channel']=='both'){
	$data="W 03 1E\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
	}
}
elseif(isset($_POST['channel']) && ($_POST['channel']=='channel1' || $_POST['channel']=='both') &&
	(isset($_POST['PhaseShifter_TX1_1'])) ){
	$val=floatval($_POST['TX1_1']);
	$val=ceil($val/5.625);
	$data=dechex($val);
	$data="W ".$data." 1A\r";
	exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
}
elseif(isset($_POST['channel']) && ($_POST['channel']=='channel1' || $_POST['channel']=='both') &&
	(isset($_POST['Attenuator_TX2_1'])) ){
	$val=floatval($_POST['TX2_1']);
	$val=($val-31.5)*-2;
	$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 1B\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
}

elseif(isset($_POST['channel']) && ($_POST['channel']=='channel1' || $_POST['channel']=='both') &&
	(isset($_POST['PhaseShifter_RX1_1'])) ){
        $val=floatval($_POST['RX1_1']);
		$val=ceil($val/5.625);
        $data=dechex($val);
        $data="W ".$data." 23\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
}
elseif(isset($_POST['channel']) && ($_POST['channel']=='channel1' || $_POST['channel']=='both') &&
	(isset($_POST['Attenuator_RX2_1'])) ) {
        $val=floatval($_POST['RX2_1']);
	$val=($val-31.5)*-2;
	$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 24\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
}
elseif(isset($_POST['channel']) && ($_POST['channel']=='channel2' || $_POST['channel']=='both') &&
	(isset($_POST['PhaseShifter_TX1_2'])) ){
	$val=floatval($_POST['TX1_2']);
	$val=ceil($val/5.625);
	$data=dechex($val);
	$data="W ".$data." 1C\r";
	exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
}
elseif(isset($_POST['channel']) && ($_POST['channel']=='channel2' || $_POST['channel']=='both') &&
	(isset($_POST['Attenuator_TX2_2'])) ){
	$val=floatval($_POST['TX2_2']);
	$val=($val-31.5)*-2;
	$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 1D\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
}

elseif(isset($_POST['channel']) && ($_POST['channel']=='channel2' || $_POST['channel']=='both') &&
	(isset($_POST['PhaseShifter_RX1_2'])) ){
        $val=floatval($_POST['RX1_2']);
	$val=ceil($val/5.625);
        $data=dechex($val);
        $data="W ".$data." 25\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
}
elseif(isset($_POST['channel']) && ($_POST['channel']=='channel2' || $_POST['channel']=='both') &&
	(isset($_POST['Attenuator_RX2_2'])) ){
        $val=floatval($_POST['RX2_2']);
	$val=($val-31.5)*-2;
	$val=ceil($val);
        $data=dechex($val);
        $data="W ".$data." 26\r";
        exec('/usr/bin/python /home/pi/sendSerialData.py "'.$data.'"');
}
?>

