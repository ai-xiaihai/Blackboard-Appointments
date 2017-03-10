<?php
/* 
Allows instructors of courses and leaders of organizations to create 
appointments for which students in the course can sign up online.
This page is part of  the OCTET Appointments/Sign-up Blackboard building block structure.
The Building Block's main use is to allow advisors to easily set up
appointments with their advisees during registration period.
*/
session_start();
include("connection.php");?>
<html>
<head>
<title>Create Appointments</title>
<style type="text/css">
<!--
.style2 {
	font-size: 10px;
	font-family: Arial;
}
.style6 {font-size: 12px; font-family: Arial; }
.style7 {
	color: #CC0000;
	font-weight: bold;
}
.style8 {color: #333333}
-->
</style>
</head>

<body>
<?php if(@strpos($_SERVER["HTTP_REFERER"], $url) == 0){ //request coming from blackboard
?>
<span class="style2"><br>&nbsp;&nbsp;<a href="<?php echo $url;?>/webapps/blackboard/execute/courseMain?course_id=<?php echo $_POST["course_id"]; ?>" target="_self">
<?php echo strtoupper($_POST["course_name"]." (".$_POST["course_cid"].")"); ?></a> > 
<a href="<?php echo $url;?>/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=<?php echo $_POST["course_id"]; ?>">APPOINTMENTS</a> > Create Appointments</span><br>
<br>
<span class="style6"><span class="style8"><br>
</span><br>
<?php
	if($_POST['uid']!=""){//process the form
		
		// make sure faculty information is in the database, add it if not
		$query = "SELECT * FROM users WHERE username='".substr($_POST['uid'],0,8)."'";
		$res = mysql_query($query, $connection);
		if(!mysql_fetch_assoc($res)){
			$query = "INSERT INTO users (username, name, email) VALUES ('".$_POST['uid']."','".addslashes($_POST['name'])."','".$_POST['email']."')";
				if(mysql_query($query, $connection)){
					echo "Instructor information added into the database.<br>";
				}
				else{
					die("Cannot add faculty profile into the database.");
				}
		}
		else{
			$query = "UPDATE users SET name='".addslashes($_POST['name'])."', email='".$_POST['email']."' WHERE username='".substr($_POST['uid'], 0, 8)."'";
				if(mysql_query($query, $connection)){
					echo "Instructor information updated into the database.<br>";
				}
				else{
					echo "Cannot update faculty profile into the database.";
				}
		}
		
		//process the appointments
		$duration;
		
		// find out what the start and end hour of the appointment slot is
		// convert to military time
		if($_POST['ehour']=='12' && $_POST['eampm']=='1'){//12 pm
			//do nothing
		}
		else if($_POST['ehour']=='12' && $_POST['eampm']=='0'){
		 	$_POST['ehour']='0';
		}
		else{
			$_POST['ehour'] = $_POST['ehour'] + 12*$_POST['eampm'];
		}
		if($_POST['shour']=='12' && $_POST['sampm']=='1'){//12 pm
			//do nothing
		}
		else if($_POST['shour']=='12' && $_POST['sampm']=='0'){
		 	$_POST['shour']='0';
		}
		else{
			$_POST['shour'] = $_POST['shour'] + 12*$_POST['sampm'];
		}
		
		if($_POST['ehour']<$_POST['shour'])//if end time is earlier than start time
		{
			die("Incompatible start and end times, make sure you enter the correct times.");
		}
		else if($_POST['ehour']==$_POST['shour'] && $_POST['eminute']<=$_POST['sminute']) //if end time is earlier than start time
		{
			die("Incompatible start and end times, make sure you enter the correct times.");
		}
		else if($_POST['eminute']>=$_POST['sminute']) // find out the duration of the appointment slot in minutes
		{
			$duration = ($_POST['eminute'] - $_POST['sminute']) + 60*($_POST['ehour'] - $_POST['shour']);
		}
		else if($_POST['eminute']<$_POST['sminute']) // find out the duration of the appointment slot in minutes
		{
			$_POST['eminute'] = $_POST['eminute'] + 60;
			$_POST['ehour'] = $_POST['ehour'] -1;
			$duration = ($_POST['eminute'] - $_POST['sminute']) + 60*($_POST['ehour'] - $_POST['shour']);
		}
		
		if($duration < $_POST['duration']) 
		// check if the duration of the whole appointment slot is 
		// at least as big as the duration for each individual appointment to be created
		{
			die("Insufficient time slot specified: $duration minutes.");
		}
		$num_appts = $duration/$_POST['duration'];
		echo "The specified time interval can be divided into $num_appts separate appointments.<br>";
		//creating each appointment
		$appt_date = date("Y-m-d", mktime(0,0,0,$_POST['month'], $_POST['day'], $_POST['year']));
		while($num_appts >=1)
		{		
			$appt_time = date("H:i:s", mktime($_POST['shour'], $_POST["sminute"], 0));
			if($_POST['available']==0)
				$_POST['course_id']="";
			
			$conflict = false;//if an appointment already exists, make sure not to add a double one
			$endmin = $_POST['sminute']+$_POST['duration'];
			$endhour = $_POST['shour'];
			//compute end hour and minute of this appointments
			if($endmin>=60)
			{
				$endmin = $endmin%60;
				$endhour++;
			}
			$q = "SELECT t1.date,t1.starttime,t1.duration FROM appointment AS t1,appointmentowner AS t2 WHERE t1.date='".$appt_date."' 
				AND t1.id=t2.id AND t2.username='".substr($_POST['uid'],0,8)."'";
			$result = mysql_query($q, $connection);
			while(($appt = mysql_fetch_array($result)) && !$conflict){			
				$new_appt_time = strtotime($appt_time);
				
				$new_appt_end = strtotime(date("H:i:s", mktime($endhour, $endmin, 0)));
				$db_appt_start = strtotime($appt[1]);
				if($new_appt_end > $db_appt_start && $new_appt_time <= $db_appt_start){
					echo "Time conflict: The new appointment you're trying to add will not end before another one of your existing appointments starts.<br>";
					$conflict = true;
					continue;
				}
				// find the end time of the appointment in the database
				$db_hour = date("H", $db_appt_start);
				$db_min = date("i", $db_appt_start);
				$db_min = $db_min + $appt[2];
				if($db_min >= 60)
				{
					$db_min = $db_min%60;
					$db_hour++;
				}
				$db_appt_end = strtotime(date("H:i:s", mktime($db_hour,$db_min, 0)));
				if($db_appt_start <= $new_appt_time && $db_appt_end > $new_appt_time)     
				{
					echo "Time conflict: The new appointment you're trying to add starts in the middle of another existing appointment.<br>";
					$conflict = true;
					continue;
				}
			}// end while no conflict
			if(!$conflict){
				//insert the new appointment into the database
				$sql= "INSERT INTO appointment (starttime,duration,date,course,courseName,courseId,appointmentOwner) VALUES ('".$appt_time."','".$_POST['duration']."','".$appt_date."','".$_POST['course_id']."','".$_POST['course_name']."','".$_POST['course_cid']."','".$_POST['uid']."')";

				if(mysql_query($sql, $connection)){
					echo "Appointment saved in the database - date: $appt_date time: $appt_time<br>";
				}
				else
				{
					echo "Unable to enter appointment in the database. Date: $appt_date Time: $appt_time<br>";
				}
				//associate appointment with its owner
				//select all appointments like the one we just entered
				$query = "SELECT id FROM appointment WHERE date='".$appt_date."' AND starttime='".$appt_time."' AND duration='".$_POST['duration']."' AND course='".$_POST['course_id']."'";
				$r = mysql_query($query, $connection);
				//for each appointment
				while($ap = mysql_fetch_assoc($r)){
				//find if it is already woned by a professor
					$subq = "SELECT * FROM appointmentowner WHERE id='".$ap['id']."'";
					$res = mysql_query($subq, $connection);
					if(!mysql_fetch_assoc($res)){//if not owned by a professor
						// associate the appointment with the current user
						if(mysql_query("INSERT INTO appointmentowner (id, username) VALUES ('".$ap['id']."', '".$_POST['uid']."')",$connection))
						{
							echo "Appointment with id ".$ap['id']." was associated with instructor with username ".$_POST['uid'].".<br>";
						}
						else
						{
							echo "Unable to associate appointment with id ".$ap['id']." with username ".$_POST['uid'].".<br>";
						}
					}
				}
				//update the start time of next appointment
				$_POST['shour'] = $endhour;
				$_POST['sminute'] = $endmin;
			}
			$num_appts--;
		}
	}
}?>
</span>
<!-- <table width="900"  border="0" class="style6"><tr><td>
 <div align="right">
 <img src="images/ok_off.gif" border="0" usemap="#Map">
 <map name="Map">
   <area shape="rect" coords="2,-2,66,23" href="<?php echo $url; ?>/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=<?php echo $_POST["course_id"]; ?>">
 </map>
 </div>
 </td></tr></table>
 -->
<p> Use the Appointments breadcrumb link at the top of the page to navigate back to the Appointment section. <br>
Or, go back to your <a href="https://blackboard.oberlin.edu">home page</a>. </p>
</body>
</html>