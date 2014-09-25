<?php
session_start();
include("connection.php");

if(strpos($_SERVER["HTTP_REFERER"], "http://octet1.csr.oberlin.edu") == 0){ // make sure the request comes from this server
/*
 * This page allows students to sign up for an appointment
 * The request for sign up happens via a form in view.php page.
 * This page only processes a request made in the other page.
 */
 $add_comment = false;
?>
<html>
<head>
<title>View & SignUp for Appointments</title>
<style type="text/css">
<!--
.style1 {
	font-size: 10px;
	font-family: Arial;
	background-color:#E0ECF8;
}
.style6 {font-size: 12px; font-family: Arial; }
.style8 {font-family: Arial}
.style9 {font-size: 12px; font-family: Arial; font-weight: bold; }
-->
</style>
</head>
<body>
<span class="style1">&nbsp;&nbsp;<a href="<?php echo $url;?>/webapps/blackboard/execute/courseMain?course_id=<?php echo $_POST["course_id"]; ?>" target="_self">
<?php echo strtoupper($_POST["course_name"]." (".$_POST["course_cid"].")"); ?></a> > 
<a href="<?php echo $url;?>/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=<?php echo $_POST["course_id"]; ?>">APPOINTMENTS</a> > SIGN UP</span><br>
<span class="style6">
<br>
<?php
	if($_POST['id']!=""){//if ID is set
		//echo "Id is ".$_POST['id']."<br>";
		$sql = "SELECT * FROM appointment WHERE id='".$_POST['id']."'";//find the appointment in the database
		$result = mysql_query($sql, $connection);
		//echo mysql_num_rows($result)." appointments selected.<br>";
		$appt = mysql_fetch_assoc($result);
		if($appt['status']!='o') // if the appointment is not open
			die("There has been an error in the database. The appointment you have selected is already taken. Please go back and select another appointment slot.");
		$taken = "UPDATE appointment SET nameSignedUp='".$_POST['username']."' WHERE id='".$_POST['id']."'";	
		mysql_query($taken,$connection);
		$query = "UPDATE appointment SET status='t' WHERE id='".$_POST['id']."'";
		if(mysql_query($query, $connection))
		{
			// echo "Appointment status changed to taken.<br>";
		}
		// update the database with student's sign-up information
		$query = "INSERT INTO signups (id, student) VALUES ('".$_POST['id']."', '".$_POST['username']."')";
		if($res = mysql_query($query, $connection)){
			//echo "User ".$_POST['username']." signed up for appointment.<br>";
			$q = "SELECT * FROM users WHERE username='".substr($_POST['username'],0,8)."'";
			$u_res = mysql_query($q, $connection);
			if(mysql_fetch_assoc($u_res)){
				//echo "User already in the database.<br>";
				//update user's information
				$q = "UPDATE users SET name='".addslashes($_POST['name'])."', email='".$_POST['email']."' WHERE username='".substr($_POST['username'],0,8)."'";
				if(mysql_query($q, $connection))
				{
					echo "You have successfully signed up for an appointment with ";
					$q = "SELECT * FROM appointmentowner WHERE id='".$_POST['id']."'";
						$res = mysql_query($q, $connection);
						$owner = mysql_fetch_assoc($res);
						$q = "SELECT * FROM users WHERE username='".$owner['username']."'";
						if($prof = mysql_fetch_assoc(mysql_query($q, $connection))){
							echo stripslashes($prof['name'])."<br>";
					$add_comment = true;
					$q = "SELECT * FROM appointment WHERE id='".$_POST['id']."'";
					$res = mysql_query($q, $connection);
					if($appt = mysql_fetch_assoc($res)){
						//echo $appt['appointmentOwner']."<br/>";
						echo "Day: ".date("F j, Y",strtotime($appt['date']))."<br>";
						echo "Time: ".date("g:i a", strtotime($appt['starttime']))."<br>";
						echo "Duration: ".$appt['duration']." minutes<br>";
						
						}
					}
				}
				else{
					//remove the appointment sign up
					$q = "DELETE FROM signups WHERE id='".$_POST['id']."' AND student='".substr($_POST['username'],0,8)."'";
					mysql_query($q, $connection) or die("There has been an error in the database. Please contact OCTET withe the time of the appointment you were trying to sign-up for.");
					//change appointment status back to 'o'
					$q = "UPDATE appointment SET status='o' WHERE id='".$_POST['id']."'";
					mysql_query($q, $connection) or die("There has been an error in the database. Please contact OCTET withe the time of the appointment you were trying to sign-up for.");
				}
			}
			else // need to add the user
			{
				$q = "INSERT INTO users (username, name, email) VALUES ('".$_POST['username']."', '".addslashes($_POST['name'])."', '".$_POST['email']."')";
				if(mysql_query($q, $connection))
				{
					echo "You have successfully signed up for your selected appointment!<br>";
					$add_comment = true;
					$q = "SELECT * FROM appointment WHERE id='".$_POST['id']."'";
					$res = mysql_query($q, $connection);
					if($appt = mysql_fetch_assoc($res)){
						echo "Day: ".date("F j, Y",strtotime($appt['date']))."<br>";
						echo "Time: ".date("g:i a", strtotime($appt['starttime']))."<br>";
						echo "Duration: ".$appt['duration']." minutes<br>";
						$q = "SELECT * FROM appointmentowner WHERE id='".$_POST['id']."'";
						$res = mysql_query($q, $connection);
						$owner = mysql_fetch_assoc($res);
						$q = "SELECT * FROM users WHERE username='".$owner['username']."'";
						if($prof = mysql_fetch_assoc(mysql_query($q, $connection))){
							echo "Instructor: ".stripslashes($prof['name'])."<br>";
						}
					}
				}
				else{
					//remove the appointment sign up
					$q = "DELETE FROM signups WHERE id='".$_POST['id']."' AND student='".substr($_POST['username'],0,8)."'";
					mysql_query($q, $connection) or die("There has been an error in the database. Please contact OCTET with the time of the appointment you were trying to sign-up for.");
					//change appointment status back to 'o'
					$q = "UPDATE appointment SET status='o' WHERE id='".$_POST['id']."'";
					mysql_query($q, $connection) or die("There has been an error in the database. Please contact OCTET with the time of the appointment you were trying to sign-up for.");
				}
			}
		}
		else{
			//change appointment status back to 'o'
			$q = "UPDATE appointment SET status='o' WHERE id='".$_POST['id']."'";
					mysql_query($q, $connection) or die("There has been an error in the database. Please contact OCTET with the time of the appointment you were trying to sign-up for.");
		}
	}
	else{
		echo "ID not set. Cannot proceed with sign up.";
	}
	if($add_comment){
	// add a form for adding comment;
	?>
</span>
<form action="addComment.php" method="post" name="comment" id="comment">
  <p class="style9">If you would like to add a comment to the instructor do so here and click on 'Add Comment'(optional). Otherwise, select OK to return to course. </p>
  <p class="style6">Comment:<br>
  <textarea name="comment" cols="40" rows="3" id="comment"></textarea>
  <input type="hidden" name="course_name" value="<?php echo $_POST["course_name"]; ?>">
  <input type="hidden" name="course_id" value="<?php echo $_POST["course_id"]; ?>">
  <input type="hidden" name="course_cid" value="<?php echo $_POST["course_cid"]; ?>">
  <input type="hidden" name="id" value="<?php echo $_POST["id"]; ?>">
  <input type="hidden" name="username" value="<?php echo substr($_POST["username"],0,8); ?>">
  </p>
  <p class="style6">
    &nbsp;<input name="Submit" type="submit" class="style6" value="Add Comment"> 
  </p>
</form>
<?php
	}
	?>
<!-- <table width="900"  border="0" class="style6"><tr><td>
 <div align="right">
 <img src="images/ok_off.gif" border="0" usemap="#Map">
 <map name="Map">
   <area shape="rect" coords="2,-2,66,23" href="<?php echo $url;?>/webapps/blackboard/execute/courseMain?course_id=<?php echo $_POST["course_id"]; ?> ">
 </map>
 </div>
 </td></tr></table>
 -->
</body>
</html>
<?php
}
else
{
 echo "Request coming from ".$_SERVER['HTTP_REFERER'];
}
?>