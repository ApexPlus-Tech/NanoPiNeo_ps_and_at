<?php
$target_dir="uploads/";
$target_file=$_FILES["fileToUpload"]["name"];
if($target_file=="alma.zip"){
	$flag=1;
	if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"],$target_dir.$target_file)){
		echo "The file ". basename($_FILES["fileToUpload"]["name"])." has been uploaded.";
	}
	else{
		echo "Could not upload file";
	}
	$f=fopen('/home/pi/password.txt','r');
	$line=$fgets($f);
	fclose($f);
	$line=str_replace(array("\n","\r"),'',$line);
	exec("unzip -P ".$line." -o /var/www/uploads/alma.zip -d /var/www/uploads/",$output,$result);
	if($result==0){
	exec("cp -TRv /var/www/uploads/html/ /var/www/");
	exec("rm --interactive=never /var/www/uploads/alma.zip");
	exec("rm --interactive=never -r /var/www/uploads/html");
	echo("<script>alert('software has been installed')</script>");
	echo("<script>window.location='index.html'</script>");
	}
	else{
		echo("<script>alert('Error unzipping the file.')</script>");
		echo("<script>window.location='index.html'</script>");
	}
}
else{
	echo("<script>alert('Invalid file name.')</script>");
	echo("<script>window.location='index.html'</script>");
}
?>
