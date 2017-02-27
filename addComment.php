<?php
session_start();
include("connection.php");
if(strpos($_SERVER["HTTP_REFERER"], "http://octet1.csr.oberlin.edu") == 0){ // make sure the request comes from this server
/*
 * Processes a request to add a comment to a sign up for an appointment. 
 * This page processes the form provided in signup.php
 */
?>
<html>
<head>
<title>View Appointments</title>
<style type="text/css">
<!--
.style1 {
	font-size: 10px;
	font-family: Arial;
}
.style6 {font-size: 12px; font-family: Arial; }
.style8 {font-family: Arial}
.style9 {font-size: 12px; font-family: Arial; font-weight: bold; }
-->
</style>
</head>
<body>
<!-- simulate breadcrumbBar -->
<span class="style1">&nbsp;&nbsp;<?php include("bread.php"); ?> > add comment</span><br>
<span class="style6">
<br>
<?php
//add the comment to the entry in the database
if($_POST["comment"]!=""){
$sql = "UPDATE signups SET comment='".$_POST['comment']."' WHERE id='".$_POST['id']."' AND student='".$_POST['username']."'";
$result = mysql_query($sql, $connection);
	if($result){
		echo "Your comment was added to the appointment sign up. The instructor will be able to see it when managing their appointments.<br>";
	}
	else{
		echo "Unable to add comment. There has been an error in the database. Please contact octet@oberlin.edu<br>";
	}
}
else{
echo "The comment field is empty...<br>";
}
?>
</span>
<br/><br/><br/><br/><br/><br/>
 <table width="900"  border="0" class="style6"><tr><td>
 <div align="left">
 <img src="images/ok_off.gif" border="0" usemap="#Map">
 <map name="Map">
   <area shape="rect" coords="2,-2,66,23" href="<?php 
	if ( $_POST["course_id"] != "null" ) {
		echo $url;
		echo "/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=";
		echo $_POST["course_id"];
	}else{
		echo "https://blackboard.oberlin.edu/";
	}
	?>">
 </map>
 </div>
 </td></tr></table>
</body>
</html>
<?php
}
else
{
 echo "Request coming from ".$_SERVER['HTTP_REFERER'];
}
?>