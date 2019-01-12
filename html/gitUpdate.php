<?php
//echo "Looking for the update ......<br/>";
#echo "Firmware update complete<br/>";
// echo "Please press back and refresh <br/>"
echo("<script>alert('Looking for the update .....')</script>");
exec("python /home/pi/auxilary.py");
echo("<script>window.location = 'index.html';</script>");
?>
