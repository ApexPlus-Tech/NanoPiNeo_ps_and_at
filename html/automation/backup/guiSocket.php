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
		<style>
			body{
				background-color: #41d9f4;
				opacity:0.9;
				z-index: 0;
			}
			table,th,td{
				border:1px solid black;
			}
			#fill-commands{
				position:relative;
				width :500px;
				height:300px;
				background-color:#42f4e5;
				float:left;
				z-index: 1;
			}
			#pic{
				position:relative;
				width:1000px;
				height:500px;
				margin:10px;
				float:right;
				z-index: 2;
			}
			#clear{
				position:absolute;
				top:600px;
				left:0px;
			}


		</style>
	</head>
	<body>
		<img id="pic" src="pic.jpeg">
		<div id="blank"><table id='tbl'></table></div>
		<div id="fill-commands">
			<h3> Give commands</h3>
			<form method="post" action="socket.php">
				<p>IP:<input type="text" name='IP' placeholder="192.168.0.xxx"></p>
				<p>Commands:<input type="text" name='command' placeholder="eg.*IST?"></p>
				<p><button>send</button></p>
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
