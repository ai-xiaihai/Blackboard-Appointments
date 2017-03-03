<!-- Using php, check if course_id is null,
	 if yes, do the breadcrumb links
	 if no, do some plain text
	 author: Frank Cheng
-->
<?php 
if ( $_POST["course_id"] != "null" ) {
	echo "<a href=";
	echo $url;
	echo "/webapps/blackboard/execute/courseMain?course_id=";
	echo $_POST["course_id"];
	echo " target=\"_self\">";
	echo strtoupper($_POST["course_name"]." (".$_POST["course_cid"].")");
	echo "</a> > ";

	echo "<a href=";
	echo $url;
	echo "/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=";
	echo $_POST["course_id"];
	echo ">APPOINTMENTS</a>";
}else{
	echo strtoupper($_POST["course_name"]." (".$_POST["course_cid"].")");
}
?>