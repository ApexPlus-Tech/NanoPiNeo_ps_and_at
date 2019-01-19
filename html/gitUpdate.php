<?php
//echo "Looking for the update ......<br/>";
#echo "Firmware update complete<br/>";
// echo "Please press back and refresh <br/>"
echo("<script>alert('Looking for the update .....')</script>");
//exec("python /home/pi/auxilary.py");
exec("git clone https://github.com/ApexPlus-Tech/NanoPiNeo_ps_and_at.git /var/www/updated",$output,$result);
echo($result);
exec("cp -TRv /var/www/updated/html /var/www");
exec("rm -r --interactive=never /var/www/updated");
echo("<script>window.location = 'index.html';</script>"); 
?>
