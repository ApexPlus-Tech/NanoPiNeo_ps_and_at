<?php
echo "Looking for the update ......<br/>";
exec("python /home/pi/auxilary.py");
#echo "Firmware update complete<br/>";
// echo "Please press back and refresh <br/>"
sleep(3);
header('Location: index.html');
?>
