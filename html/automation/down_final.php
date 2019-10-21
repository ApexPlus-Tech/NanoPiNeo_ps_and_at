<?php
  $file_name   = "/var/www/automation/session.txt";
  $fp          = fopen($file_name,"r");
  $string_name = fgets($fp);
  $new_string  = str_replace(array("\r","\n"),"",$string_name);
  fclose($fp);
  $file_name   = "/var/www/automation/download.txt";
  $fp          = fopen($file_name,"w");
  $new_string  = "$new_string"."/ReadFiles";
  echo $new_string;
  fwrite($fp,$new_string);
  fclose($fp);
  header("Location:/automation/download.php");
?>
