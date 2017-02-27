<?php
session_start();
include("connection.php");
/*
 * check if someone has appointments
 * print 'Y' or 'N' to the page
 */
 ?>
<html>
<body>
	<?php
	$exist = false;
	$username = $_GET["name"];
	$t_date = date('Y-m-d');
	$sql = "SELECT appointmentOwner FROM appointment WHERE date >= "."'".$t_date."'";
	$result = mysql_query($sql, $connection);
	if ($result){
		while ($row = mysql_fetch_array($result)) {
    		if ( $row[0] == $username ){
    			$exist = true;
    			break;
    		}
		}
	}
	echo $exist==true ? 'Y' : 'N';
	?>
</body>
</html>