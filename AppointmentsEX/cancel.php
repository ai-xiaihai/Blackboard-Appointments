<?php
include("connection.php"); 

if(strpos($_SERVER["HTTP_REFERER"], "http://octet1.csr.oberlin.edu/octet/Bb/Appointments/manage.php") == 0){
/*
 * This page allows a professor to cancel an appointment.
 * Cancel button appears in manage page next to each appointment, which allows the professor to request a cancellation
 * The cancellation request is processed and executed in this page (cancel.php).
 * When an appointmenti s cancelled, if it was taken by anybody, an email will be sent to the affected person
 * to notify them of the cancellation. If email cannot be sent, appointment will stil lbe cancelled and
 * the professor will be informed that they need to notify the student of the change.
 */
?>
<html>
<head>
<title>Cancel Appointment</title>
<style type="text/css">
<!--
.style1 {
	font-size: 10px;
	font-family: Arial;
}
.style6 {font-size: 12px; font-family: Arial; }
.style8 {font-family: Arial}    
-->
</style>
</head>
<body>
<span class="style1"><br>
&nbsp;&nbsp;<a href="<?php echo $url;?>/webapps/blackboard/execute/courseMain?course_id=<?php echo $_POST["course_id"]; ?>" target="_self">
  <?php echo strtoupper($_POST["course_name"]." (".$_POST["course_cid"].")"); ?></a> > 
  <a href="<?php echo $url;?>/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=<?php echo $_POST["course_id"]; ?>">APPOINTMENTS</a> > CANCEL</span><br>
  <span class="style6">
  <?php
//process only if referer is correct
$to = "";
$from = "";
echo $to."<br>";
//find details about the appointment
$sql = "SELECT * FROM appointment WHERE id='".$_POST['id']."'";
if($appt = mysql_fetch_assoc(mysql_query($sql, $connection)) ){
	//display information about the appointment
	echo "Cancelling appointment:<br>
		  Date: ".date("F j, Y",strtotime($appt['date']))."<br>
		  Time: ".date("g:i a", strtotime($appt['starttime']))."<br>
		  Duration: ".$appt['duration']." minutes<br>
		  Status: ";
	if($appt['status'] == 'o'){
		echo "available";
	}
	else if($appt['status'] == 't'){
		echo "taken";
	}
	echo "<br><br>";
	//find if anyone signed up for the appointment
	$sql = "SELECT * FROM signups WHERE id='".$_POST['id']."'";
	$result = mysql_query($sql, $connection);
	if($student = mysql_fetch_assoc($result)){
		//find out who signed up for the appointment - their email
		$sql = "SELECT * FROM users WHERE username='".$student['student']."'";
		if ($stu = mysql_fetch_assoc(mysql_query($sql, $connection))){ //$stu contains student's information
			$to = $stu['email'];
			$sql = "SELECT * FROM appointmentowner WHERE id='".$_POST['id']."'";
			
			if( $prof = mysql_fetch_assoc(mysql_query($sql, $connection)) ){
				//find the owner's email and name
				$sql = "SELECT * FROM users WHERE username='".$prof['username']."'";
				if( $professor = mysql_fetch_assoc(mysql_query($sql, $connection)) ){ //$professor contains instructor's information
					// prepare an email		
					$from = $professor['email'];
					$subject = "Appointment with ".stripslashes($professor['name'])." CANCELLED";
					$headers = "From: ".$from."\r\nReturn-Path: <".$from.">";
					$contents = stripslashes($professor['name'])." has cancelled your appointment with them for ".date("F j, Y",strtotime($appt['date']))." ".date("g:i a", strtotime($appt['starttime'])).".
						\nIf you would still like to meet with ".stripslashes($professor['name']).", please go back into the course 
						\nthey are teaching and select an alternative time from the available appointments.
						\nThis is an automatically generated email. To reply please email ".stripslashes($professor['name'])." at ".$from;
					if(mail($to, $subject, $contents, $headers)){
						echo "An email has been sent to the student notifying them of the cancellation.<br>";
					}
					else{
						echo "Could not send an email to the student. Please notify the student of the cancellation.<br>";
					}
				}
				else{
					echo "Unable to find instructor's information in the database.<br>";
				}
			}
			else
			{
			 echo "There has been an error in the database. This appointment is not associated with any instructor.<br>";
			}
		}
		else{
		 echo "Unable to find the student, who signed up for this appointment, in the database. 
			   An email will not be sent automatically. Please, notify the student of the cancellation.<br>";
		}
	}
	//delete student sin-up for the appointment
	$sql = "DELETE FROM signups WHERE id='".$_POST['id']."'";
	if(mysql_query($sql, $connection)){
		echo "Deleted sign-up for the appointment.<br>";
	}
	//delete owner of the appointment
	$sql = "DELETE FROM appointmentowner WHERE id='".$_POST['id']."'";
	if(mysql_query($sql, $connection)){
		echo "Deleted owner for the appointment.<br>";
	}
	// delete appointment
	//delete owner of the appointment
	$sql = "DELETE FROM appointment WHERE id='".$_POST['id']."'";
	if(mysql_query($sql, $connection)){
		echo "Deleted appointment.<br>";
	}
}
else{
	echo "Error: could not find the appointment in the database.<br>";
} ?>
  </span>
 <table width="900"  border="0" class="style6"><tr><td>
 <div align="right">
 <img src="images/ok_off.gif" border="0" usemap="#Map">
 <map name="Map">
   <area shape="rect" coords="2,-2,66,23" href="<?php echo $url;?>/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=<?php echo $_POST["course_id"]; ?>">
 </map>
 </div>
 </td></tr></table>
</body>
</html>
<?php
}
?>