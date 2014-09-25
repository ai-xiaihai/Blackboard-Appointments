<?php
include("connection.php");
// page displays a comment associated with an appointment sign-up identified by student's username and appointment id
?>
<html>
<style type="text/css">
<!--
.style1 {
	font-family: Arial;
	font-size: 12px;
}
-->
</style>
<body class="style1" onBlur="self.focus" onFocus="self.focus">
<div align="justify">
<?php
$sql = "SELECT * FROM signups WHERE id='".$_GET['id']."' AND student='".substr($_GET['uid'],0,8)."'";
$result = mysql_query($sql, $connection);
if($com=mysql_fetch_assoc($result)){
	echo $com['comment'];
}
?>
</div>
</body>
</html>
