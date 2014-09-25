<?php
session_start();
include("connection.php");
/*
 * shows appointments by instructor, all appointments both taken and available are shown
 * students allowed to sign up for available appointment unless they already have 
 * signed up for an appointment. If signed up, students are allowed to cancel the appointment
 */
 ?>
<html>
<head>
<title>View Appointments</title>
<style type="text/css">
<!--
.style1 {
	font-size: 12px;
	font-family: Arial;
	background-color:#E0ECF8;
}
.style6 {font-size: 12px; font-family: Arial; vertical-align:middle }
.style8 {font-family: Arial}
table
{
table-layout:fixed;
}
-->
</style>
</head>
<body>
<!-- simulate breadcrumbBar -->
<span class="style1">&nbsp;&nbsp;<a href="<?php echo $url;?>/webapps/blackboard/execute/courseMain?course_id=<?php echo $_POST["course_id"]; ?>" target="_self">
<?php echo strtoupper($_POST["course_name"]." (".$_POST["course_cid"].")"); ?></a> > 
<a href="<?php echo $url;?>/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=<?php echo $_POST["course_id"]; ?>">APPOINTMENTS</a> > View & Sign up &nbsp;&nbsp;</span><br>
<br>
<div width="900"  border="0" class="style6">
To sign up for an appointment click the "Sign up" button at the end of the appropriate row. <br/>
</div>
<?php
//pull up a list of appointment entries from the database

$today = time(); //holds current time to compare with appointment time
$signup = true; //indicates whether student is allowed to sign up for an appointment

//find out if the student has signed up for any appointment
$query = "SELECT t1.date, t1.starttime, t1.id  FROM appointment as t1, signups as t2, appointmentowner as t3 
										WHERE t1.id=t3.id AND
										t1.id=t2.id AND
										t3.username='".substr($_POST['instructor'], 0, 8)."' AND
										t2.student='".substr($_POST['username'], 0, 8)."'";
// echo $query;
$res = mysql_query($query, $connection);
// for every appointment the student has in the database with that professor
while($ap = mysql_fetch_array($res)){
	// figure out if the appointment is in the past or pending
	if($today < strtotime($ap[0]." ".$ap[1])){ //if appointment is pending
		$signup = true; //student cannot sign up for another appointment
		//Get name of instructor
		$q1 = "SELECT * FROM users where username='".substr($_POST['username'], 0, 8)."'";
		$res1 = mysql_query($q1, $connection);
		$professor1 = mysql_fetch_assoc($res1);
/*		//Give info about any appoint that user is already signed up for with that instructor
       //still in beta. decided not to use it. Seemed confusing. AB 10/31/13
		echo "<p><b>You scheduled to meet with ". $professor1['name']." at ". substr($ap['starttime'],0,5)." on ". $ap['date'] ." </b></p>";
*/
	}
}
// pull up all appointments for that professor into the database									
$sql = "SELECT t1.date, t1.starttime, t1.status, t1.duration, t1.id, t1.course FROM appointment AS t1, appointmentowner AS t2 
										WHERE (t1.course='".$_POST['course_id']."' OR t1.course='') AND
										t1.id=t2.id AND
										t2.username='".substr($_POST['instructor'], 0, 8)."'
										ORDER BY t1.date, t1.starttime";
echo "<br/>";
//echo $sql;
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
	<table width="900"  border="0">
	<tr><td  style="width:100px"></td><td  style="width:100px"></td><td  style="width:175px"></td><td></td></tr><!--used to set column widths for table-->
	<tr><td colspan="4">	
			<br/><hr/><h3 class="style8"><?php echo date("l", strtotime($appts[0]))."&nbsp";echo date("F j, Y",strtotime($appts[0])); ?></h3>
			</td><tr>
			<?php $greybg = true; ?>
			
			  <tr class="style6">
				<td align="middle">Time</td>
				<td align="middle">Duration (minutes) </td>
				 <td align="middle">Status </td> 
				<td>Action</td>
			  </tr>
			<?php
			$closetable = true;	
			$date = $appts[0];
		}?>
		<tr valign="middle" class="style6"  <?php if($greybg){echo "bgcolor=\"#CCCCCC\""; }?>>
		<td height="40" align="center"><?php echo date("g:i a", strtotime($appts[1])); ?></td>
		<td height="40" width="170px" align="center"><?php echo $appts[3]; ?></td>
		<!-- <td height="30" align="center"><?php echo $professor['name']; ?></td> -->
		<td height="40" valign="middle" >
		   <?php 
		  if($appts[2]=='t'){ //appointment status is set to "taken"
			// find who has signed up for that appointment
			$q = "SELECT * FROM signups where id='".$appts[4]."'";
			$res = mysql_query($q, $connection);
			$st = mysql_fetch_assoc($res);
			
			//find details about that person
			$q = "SELECT * FROM users where username='".$st['student']."'";
			$res = mysql_query($q, $connection);
			$student = mysql_fetch_assoc($res);
			//display the results
			echo " <img src='https://octet1.csr.oberlin.edu/octet/Bb/Photos/expo/".$st['student']."/profileImage' align='left' width='25' height='25'border='1'>Taken by:<br/> ".$student['name']."</td>
			<td>";
			//If the student who has signed up is the one that is logged in, allow them to cancel their appointment.
			if($st['student']==substr($_POST['username'], 0, 8))
			{ ?>
				<form name="form" method="post" action="stcancel.php">
				<input name="id" type="hidden" value="<?php echo $appts[4]; ?>">
				<input name="username" type="hidden" value="<?php echo $_POST['username']; ?>">
				<input type="hidden" name="course_id" value="<?php echo $_POST['course_id']; ?>">
				<input type="hidden" name="course_name" value="<?php echo $_POST['course_name']; ?>">
				<input type="hidden" name="course_cid" value="<?php echo $_POST['course_cid']; ?>">
				<i><span style="font-size:smaller">Reason for cancelation <br/></span></i><input name="reason" type="text" size="30" required>
		  		<input type="submit" name="Submit" value="Cancel Appointment">
		  		</form>
				</td>
			<?php 
			} 
		}
			//if no one has taken that slot i.e. status field in database is 'o' then let them sign up
			else if($appts[2]=='o' && $signup){
				?>
				Available</td><td >
				<form name="form" method="post" action="signup.php">
				<input name="id" type="hidden" value="<?php echo $appts[4]; ?>">
				<input name="username" type="hidden" value="<?php echo $_POST['username']; ?>">
				<input name="name" type="hidden" value="<?php echo $_POST['name']; ?>">
				<input name="email" type="hidden" value="<?php echo $_POST['email']; ?>">
				<input type="hidden" name="course_id" value="<?php echo $_POST['course_id']; ?>">
				<input type="hidden" name="course_name" value="<?php echo $_POST['course_name']; ?>">
				<input type="hidden" name="course_cid" value="<?php echo $_POST['course_cid']; ?>">
			  <input type="submit" name=" " value="Sign Up">
			  </form>
		 	 </td>
			<?php //these are probably unused
			}
		else if($appts[2]=='o' && !$signup){echo "Available <td>&nbsp;</td>";}
		else if($appts[2]=='c'){
			echo "Status: The professor has cancelled this appointment.";
		}
		else if($appts[2]=='p'){
			echo "Status: A cancel request by the student is pending for this appointment. Check back later and it might be available.";
		}?></td></tr>
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
<table width="900"  border="0" class="style6"><tr><td>
 <div align="right">
 <img src="images/ok_off.gif" border="0" usemap="#Map">
 <map name="Map">
   <area shape="rect" coords="2,-2,66,23" href="<?php echo $url;?>/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=<?php echo $_POST["course_id"]; ?>">
 </map>
 </div>
 </td></tr></table>
</em></p>
</body>
</html>
