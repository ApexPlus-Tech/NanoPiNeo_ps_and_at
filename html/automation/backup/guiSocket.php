<?php
	
	//it starts the session .so that session variable gets live across ther pages
	//
	session_start();

	$reply=$_SESSION['reply'];
	echo $reply;
	session_unset();
	session_destroy();
	

	





?>


<html>
	<head>
		<title>SCPI REMOTE ACCESS</title>
	</head>
	<body>
		<div id="fill-commands">
			<h3> Give commands</h3>
			<form method="post" action="socket.php">
				<p>IP:<input type="text" name='IP' placeholder="192.168.0.xxx"></p>
				<p>Commands:<input type="text" name='command' placeholder="eg.*IST?"></p>
				<p><input type="submit"></input></p>
			</form>

		</div>
		<div id="clear">
			<p> click to clear the result</p>
			<button  onclick="myFunction()">clear</button>
		</div>	
		<script>
		function myFunction() {
		    location.reload();
		}
		</script>


		



	</body>
</html>

