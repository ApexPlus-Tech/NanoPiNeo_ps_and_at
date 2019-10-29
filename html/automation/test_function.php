<?php
function test(){
	$a = $GLOBALS['a'];
	echo "Inside the function $a";
}

$a = "abcde";
echo "Outside the function :$a\n";
for($i="5";$i<10;$i++){
	echo "hello\n";
}
test();
?>
