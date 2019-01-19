<?php
//echo "Looking for the update ......<br/>";
//echo "Firmware update complete<br/>";
// echo "Please press back and refresh <br/>"
//echo("<script>alert('Looking for the update .....')</script>");
//exec("python /home/pi/auxilary.py");
exec("ping -c 4 www.google.com",$output,$result);
if($result==0){//ping is successful
	exec("git clone hs://github.com/ApexPlus-Tech/NanoPiNeo_ps_and_at.git /var/www/updated",$output,$result);
	//echo($result);
	//git cloning is successful
	if($result==0){
		exec("cp -TRv /var/www/updated/html /var/www");
		exec("rm -r --interactive=never /var/www/updated");
		echo("Firmware update completed");
	}
	else{
		echo("Server is down");
	}
}
else{
	echo("Internet is down");
}
?>
