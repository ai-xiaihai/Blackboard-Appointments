<?php
session_start();
include("connection.php");?>
<html>
<head>
<title>View Appointments</title>
<style type="text/css">
<!--
.style1 {
	font-size: 8px;
	font-family: Arial;
}
.style6 {font-size: 12px; font-family: Arial; }
.style8 {font-family: Arial}
-->
</style>
</head>
<body>
<?php 
$_POST["course_name"] = 'vocal studies';
$_POST["course_id"] = '_2819_1';
$_POST["course_cid"] = '_2819_1';
$_POST["email"] = '';
$_POST["username"] = '';
$_POST["instructor"] = 'vstudies';
$_POST["name"] = '1';
?>
<!-- simulate breadcrumbBar -->
<table width="900"  border="0" class="style6"><tr><td>
To sign up for an appointment click the "Sign up" button corresponding to it. You can only sign up for one appointment at a time. If you already have an appointment, you will need to cancel it before you can sign up for another one. To cancel an appointment provide a reason for the cancellation and click the Cancel button.<br>
</td></tr></table>
<?php
//pull up a list of appointment entries from the database

$today = time();
$signup = true;
/*
//find out if the student has signed up for any appointment
$query = "SELECT t1.date, t1.starttime, t1.id FROM appointment as t1, signups as t2, appointmentowner as t3 
										WHERE t1.id=t3.id AND
										t1.id=t2.id AND
										t3.username='".substr($_POST['instructor'], 0, 8)."' AND
										t2.student='".substr($_POST['username'], 0, 8)."'";
$res = mysql_query($query, $connection);
// for every appointment the student has in the database with that professor
while($ap = mysql_fetch_array($res)){
	// figure out if the appointment is in the past or pending
	if($today < strtotime($ap[0]." ".$ap[1])){
		$signup = false;
	}
}
*/
// pull up all appointments for that professor into the database									
$sql = "SELECT t1.date, t1.starttime, t1.status, t1.duration, t1.id, t1.course FROM appointment AS t1, appointmentowner AS t2 
										WHERE (t1.course='".$_POST['course_id']."' OR t1.course='') AND
										t1.id=t2.id AND
										t2.username='".substr($_POST['instructor'], 0, 8)."'
										ORDER BY t1.date, t1.starttime";
//echo substr($_POST['instructor'], 0,8);
$result = mysql_query($sql, $connection);
$date = "";
$closetable = false;
$num_appts = 0;
while($appts = mysql_fetch_array($result)){// for every appointment in the database for this course
	// if the appointment is pending
	if($today < strtotime($appts[0]." ".$appts[1])){
		$num_appts++;
		// find the owner of the appointment
		$query = "SELECT * FROM appointmentowner where id='".$appts[4]."'";
		$res = mysql_query($query, $connection);
		$owner = mysql_fetch_assoc($res);
		// find details about the owner (name and email)
		$q = "SELECT * FROM users where username='".$owner['username']."'";
		$res = mysql_query($q, $connection);
		$professor = mysql_fetch_assoc($res);
		// display appointment
		if(strcmp($date, $appts[0])!=0){ //display new date header if it's different from the previous one
			if($closetable){
			echo "</table>";
			}
			?>		
			<h3 class="style8">Day: <?php echo date("F j, Y",strtotime($appts[0])); ?></h3>
			<?php $greybg = true; ?>
			<table width="900"  border="0" cellpadding="0" cellspacing="3" bordercolor="#010000">
			  <tr class="style6">
				<td width="100" align="middle">Time</td>
				<td width="100" align="middle">Duration (minutes) </td>
				<td width="200">Taken by </td>
			    <td width="378">Scheduled pieces </td>
			  </tr>
			<?php
			$closetable = true;	
			$date = $appts[0];
		}?>
		    <tr valign="middle" class="style6"  <?php if($greybg){echo "bgcolor=\"#CCCCCC\""; }?>>
		      <td height="1" colspan="4" align="center" bgcolor="#333333"></td>
		      </tr>
		    <tr valign="middle" class="style6"  <?php if($greybg){echo "bgcolor=\"#CCCCCC\""; }?>>
		<td height="30" align="center"><?php echo date("g:i a", strtotime($appts[1])); ?></td>
		<td width="100" height="30" align="center"><?php echo $appts[3]; ?></td>
		<td width="200" height="30" valign="top"><?php if($appts[2]=='t'){ //appointment status is set to "taken"
			// find who has signed up for that appointment
			$q = "SELECT * FROM signups where id='".$appts[4]."'";
			$res = mysql_query($q, $connection);
			$st = mysql_fetch_assoc($res);
			
			//find details about that person
			$q = "SELECT * FROM users where username='".$st['student']."'";
			$res = mysql_query($q, $connection);
			$student = mysql_fetch_assoc($res);
			$comment = $st['comment'];
			
			echo "Taken by: ".$student['name']; //display the results
			
			if($st['student']==substr($_POST['username'], 0, 8))
			{ ?>			<?php }
		}
		else if($appts[2]=='o' && $signup){
			?>
		        <?php //these are probably unused
		}
		else if($appts[2]=='c'){
			echo "Status: The professor has cancelled this appointment.";
		}
		else if($appts[2]=='p'){
			echo "Status: A cancel request by the student is pending for this appointment. Check back later and it might be available.";
		}?></td>
		<td valign="top"><?php if (isset($comment)) {echo '<pre>'.  $comment .'</pre>';} 
		  ?></td>
		    </tr>
	<?php
		$greybg = !$greybg;
	}
}
?>
</table>
<?php
if($num_appts < 1)
{
?>
<span class="style6"><br>
 <br>
 &nbsp;&nbsp;This instructor does not have any available appointments in the database.<br>
</span> <?php
}
?>

</body>
</html>
