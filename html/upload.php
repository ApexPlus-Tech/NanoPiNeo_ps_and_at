<?php
$target_dir="uploads/";
$target_file=$_FILES["fileToUpload"]["name"];
if($target_file=="html.zip"){
	$flag=1;
	if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"],$target_dir.$target_file)){
		echo "The file " . basename($_FILES["fileToUpload"]["name"]), " has been uploaded.";
	}
	else{
		echo "Could not upload file";
	}
	exec("unzip -P "apexplus" -o /var/www/uploads/html.zip -d /var/www/uploads/");
	exec("cp -TRv /var/www/uploads/html/ /var/www/");
	exec("rm --interactive=never /var/www/uploads/html.zip");
	exec("rm --interactive=never -r /var/www/uploads/html");
	echo "Software has been installed";
}
else{
	echo("Invalid file name");
}
?>
