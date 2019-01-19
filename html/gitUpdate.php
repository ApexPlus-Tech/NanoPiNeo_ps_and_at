<?php
function showInstall(){
	echo '
	<br/>
	<div>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
    <br/>
	<input type="file" name="fileToUpload" required/>
	<br/>
    <input type="submit" name="update" value="INSTALL" class="btn btn-primary"/>
    </form>
	</div>'
}

exec("ping -c 4 www.google.com",$output,$result);
if($result==0){//ping is successful
	exec("git clone https://github.com/ApexPlus-Tech/NanoPiNeo_ps_and_at.git /var/www/updated",$output,$result);
	//echo($result);
	if($result==0){//git cloning is successful
		exec("cp -TRv /var/www/updated/html /var/www");
		exec("rm -r --interactive=never /var/www/updated");
		echo("Firmware update completed");
	}
	else{
		echo("Server is down");
		showInstall();
	}
}
else{
	echo("Internet is down");
	showInstall();
}
?>
