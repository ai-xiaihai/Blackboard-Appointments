<?php
include("connection.php"); 

if(strpos($_SERVER["HTTP_REFERER"], "http://octet1.csr.oberlin.edu/octet/Bb/Appointments/manage.php") == 0){
//process only if referer is correct

/*
 * This page processes a request by the student to cancel an appointment.
 * It collects the relevant information and sends an email to the professor informing them of the cancellation.
 * Email is only sent if the cancelled appointment is within two days.
 * The appointment will only be cancelled if an email was sucessfully sent, or email was not necessary.
 * In other cases the script should advise the student to contact OCTET (if there is an error in the database, for example)
 */
?>
<html>
<head>
<title>Cancel Appointment</title>
<style type="text/css">
<!--
.style1 {
	font-size: 12px;
	font-family: Arial;
}
.style6 {font-size: 12px; font-family: Arial; }
.style8 {font-family: Arial}    
-->
</style>
</head>
<body>
<span class="style1"><br>&nbsp;&nbsp;<a href="<?php echo $url;?>/webapps/blackboard/execute/courseMain?course_id=<?php echo $_POST["course_id"]; ?>" target="_self">
<?php echo strtoupper($_POST["course_name"]." (".$_POST["course_cid"].")"); ?></a> > 
<a href="<?php echo $url;?>/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=<?php echo $_POST["course_id"]; ?>">APPOINTMENTS</a> > CANCEL</span><br>
<span class="style6">
<?php 
//code here
//variables to hold respective emails
$to = "";
$from = "";
echo "<br>";
//find details about the appointment
$sql = "SELECT * FROM appointment WHERE id='".$_POST['id']."'";
if($appt = mysql_fetch_assoc(mysql_query($sql, $connection)) ){
	//display information about the appointment
	/* echo "Cancelling appointment:<br>
		  Date: ".date("F j, Y",strtotime($appt['date']))."<br>
		  Time: ".date("g:i a", strtotime($appt['starttime']))."<br>
		  Duration: ".$appt['duration']." minutes<br>
		  Status: ";*/
	if($appt['status'] == 'o'){
		//echo "available";
	}
	else if($appt['status'] == 't'){
		//echo "taken";
	}
	echo "<br><br>";
	
	//find if anyone signed up for the appointment
	$sql = "SELECT * FROM signups WHERE id='".$_POST['id']."'";
	$result = mysql_query($sql, $connection);
	if($student = mysql_fetch_assoc($result)){
		if($student['student'] != substr($_POST['username'], 0,8)){
			echo "Your username does not match the one of the person who has signed up for this appointment.<br>
				  You cannot cancel this appointment.<br>";
		}
		else
		{
			//find out if the appointment is within 1 days of today
			 $endtime = strtotime("+1 days");
			//echo date("F j, Y g:i a", $endtime)."<br>";
			$appttime = strtotime($appt['date']);
			if($appttime > $endtime){ //if appointment is within 1 days
				//find details about the student
				$sql = "SELECT * FROM users WHERE username='".$student['student']."'";
				if ($stu = mysql_fetch_assoc(mysql_query($sql, $connection))){ //$stu contains student's information
					//echo "Student information correctly pulled from the database.<br>";
					$from = $stu['email'];				
					// find out the email of the professor				
					$sql = "SELECT * FROM appointmentowner WHERE id='".$_POST['id']."'";				
					if( $prof = mysql_fetch_assoc(mysql_query($sql, $connection)) ){
						//find the owner's email and name
						$sql = "SELECT * FROM users WHERE username='".$prof['username']."'";
						if( $professor = mysql_fetch_assoc(mysql_query($sql, $connection)) ){ //$professor contains instructor's information			
							$to = $professor['email'];
							//echo "To: ".$to."<br>";
							// prepare an email to be sent to the professor
							$subject = "Appointment with ".stripslashes($stu['name'])." CANCELLED";
							$headers = "From: ".$from."\r\nReturn-Path: <".$from.">";
							$contents = stripslashes($stu['name'])." has cancelled their appointment with you for ".date("F j, Y",strtotime($appt['date']))." ".date("g:i a", strtotime($appt['starttime'])).".
								\nReason: ".$_POST['reason']."
								\nThis time slot will now be available for other people to take.
								\n".stripslashes($stu['name'])." will be able to sign up for another appointment.
								\nThis is an automatically generated email. To reply please email ".stripslashes($stu['name'])." at ".$from;
							if(mail($to, $subject, $contents, $headers)){
								echo " An email has been sent to the instructor notifying them of the cancellation.<br>";
								//cancel the appointment
								$sql = "UPDATE appointment SET status='o', nameSignedUp='(NULL)' WHERE id='".$_POST['id']."'";							
								if(mysql_query($sql, $connection)){
									//echo "Appointment made available.<br>";
									$sql = "DELETE FROM signups WHERE id='".$_POST['id']."'";
									if(mysql_query($sql, $connection))
									{
										//echo "Deleted sign-up for this appointment.<br>";
									}
									else
									{
										echo "Error: could not delete sign-up for appointment. Contact OCTET - octet@oberlin.edu<br>";
									}
								}
								else{
									echo "Error: could not cancel appointment. Please contact OCTET - octet@oberlin.edu.<br>";
								}
								}
								else{
									echo "Could not send email to the instructor notifying them of the change.<br> 
									The appointment will not be cancelled.<br>
									If you wish to cancel the appointment please contact your instructor or OCTET (octet@oberlin.edu)<br>";
								}
						}
						else{
							echo "Unable to find instructor's information in the database. Appointment will not be cancelled.<br>";
						}
					}
					else
					{
					 echo "There has been an error in the database. This appointment is not associated with any instructor.
					 		Appointment will not be cancelled.<br>";
					}
				}
				else
				{
					echo "Could not find your details in the database. Appointment will not be cancelled.<br>";
				}				
			}
			else{
				echo "<b> FAILED </b>Your appointment is not within 24 hours. An email will not be send to the instructor notifying them of the change. CONTACT THE PROFESSOR DIRECTLY VIA EMAIL.<br>";
				//cancel the appointment
				$sql = "UPDATE appointment SET status='o' SET comments='' SET nameSignedUp='' WHERE id='".$_POST['id']."'";							
				if(mysql_query($sql, $connection)){
					//echo "Appointment made available.<br>";
					$sql = "DELETE FROM signups WHERE id='".$_POST['id']."'";
					if(mysql_query($sql, $connection))
					{
						//echo "Deleted sign-up for this appointment.<br>";
					}
					else
					{
						echo "Error: could not delete sign-up for appointment. Contact OCTET - octet@oberlin.edu<br>";
					}
				}
				else{
					echo "Error: could not cancel appointment.<br>";
				}
			}
			
			//cancel the appointment
		}
	}
}
?>
</span>
<br/><br/><br/><br/><br/><br/>
<table width="900"  border="0" class="style6"><tr><td>
 
 <tr><td>
 <div align="left">
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