<?php
if(isset($_POST['clearlog'])){
	exec('sudo cp /var/log/apache2/error.log');
	echo "<script>alert('Log cleared successfully');</script>";
	echo "<script>window.history.go(-1);</script>"
}
elseif(isset($_POST['showlog'])){
	exec('sudo cat /var/log/apache2/error.log',$output,$retVal);
	echo  '<pre>';
	echo $output;
	echo '</pre>';
}
elseif(isset($_POST['restart'])){
 exec("sudo reboot");
}
?>
