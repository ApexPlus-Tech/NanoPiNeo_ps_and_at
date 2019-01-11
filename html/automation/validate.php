<?php
	//it starts  the session so that some variables can be live across the pages
	
	session_start();
	//host is the ip addres of instrument on which SCPI server is running
	//However, it can change .
	
	
	$host=$_POST["ipAddr"];

	$command="*IDN?";   //This command  variable has got value from $_POST variable which has been passed from gui page by user

    $command="$command"."\n";      //concatenating the command with newline character

	$port=5025;   //here the port no is 5025.it is not random
	//SCPI is a protocol built over top of TCP which listens on specific port ,which is 5025 or 5024 by default
	//So ,I have created a tcp socket for iv4
	//SOCK_STREAM is for TCP and SOCK_DGRAM is for UDP
	//
	
	$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");  //it creates a tcp socket


	socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>5, "usec"=>0));  //    if it doesn,t get reply from server it will close its connection after 5 sec
	$result = socket_connect($socket, $host, $port) or die("Could not connect toserver\n");   //it has connected to server and stores its connection link in result

	//it has sent the command to SCPI server 
	socket_write($socket, $command, strlen($command)) or die("Could not send data to server\n");

	$result = socket_read ($socket, 1024);//it receives the reply from the server even if it fails it will redircted to our gui page for another querry
	echo "Reply From Server  :".$result;
	socket_close($socket);    //after this close the connection
	$_SESSION['reply']=$result;    //store the result in session variable so that it can be accessible across the pages
	#header('Location:guiSocket.php'); //redirect the back to gui page for user interaction


	//CONTROL FLOW#########################################################################################################################################################
	/* guiSocket.php is my local gui page.First of all This gui page interacts with user and takes the commands and sends commands to its own server which is scripted in this file(socket.php) .This server talks to the the SCPI server and sends command to the scpi server running in some instrument.The scpi server in return sends response to our own local server.Then our server forwards the response back to the user interacting page(guiSocket.php)
	*/
?>
