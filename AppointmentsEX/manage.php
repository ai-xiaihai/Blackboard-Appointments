<?php
session_start();
include("connection.php");
/*
 * This page allows faculty to manage their appointments
 * It will display all appointments the professor has made available, that are still pending.
 * The page also displays the name of the person who has signed up for an appointment,
 * and allows the professor to cancel appointments (both taken and available)
 */?>
<html>
<head>
<title>Manage Appointments</title>
<script language="JavaScript" type="text/JavaScript">
function MM_openBrWindow(theURL,winName,features) { //v2.0
  var w = window.open(theURL,winName,features);
  w.focus();
}
//-->
</script>
<style type="text/css">
<!--
.style1 {
	font-size: 12px;
	font-family: Arial;
}
.style6 {font-size: 12px; font-family: Arial; }
.style8 {font-family: Arial;}    
.style9 {color:brown;}
-->
</style>
</head>
<body>
<span class="style1"><br>&nbsp;&nbsp;<a href="<?php echo $url;?>/webapps/blackboard/execute/courseMain?course_id=<?php echo $_POST["course_id"]; ?>" target="_self">
<?php echo strtoupper($_POST["course_name"]." (".$_POST["course_cid"].")"); ?></a> > 
<a href="<?php echo $url;?>/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=<?php echo $_POST["course_id"]; ?>">APPOINTMENTS</a> > MANAGE</span><br>
<br>
<table width="900"  border="0" class="style6"><tr><td>
You can use this page to cancel existing appointments. Cancelling an appointment deletes it permanently. It will no longer be available for students to sign up for it. If you wish to cancel an appointment which is already taken, an email will be sent to the student to notify them of the cancellation and they will be able to sign up for another appointment. To cancel an appointment, click the Cancel button corresponding to it.<br>
</td></tr>
<tr>
  <td>
  
</table>
<?php
// pull all appointments from the database for this instructor
$today = time();
$sql = "SELECT * FROM appointment as t1, appointmentowner as t2 WHERE t2.username='".substr($_POST["uid"], 0, 8)."' AND t1.id=t2.id ORDER BY date, starttime";
$result = mysql_query($sql, $connection);
$date = "";
$closetable = false;
$num_appts = 0;
while($appts = mysql_fetch_assoc($result)){// for every appointment in the database for this user
	// make sure the appointment is in the future, we do not want to display appointments in the past.
	$num_comments = 0;
	if($today < strtotime($appts['date']." ".$appts['starttime'])){
		$num_appts++;
		// find details about the owner (name and email)
		$q = "SELECT * FROM users where username='".$appts['username']."'";
		$res = mysql_query($q, $connection);
		$professor = mysql_fetch_assoc($res);
		// display appointment
		if(strcmp($date, $appts['date'])!=0){ //display new date header if it's different from the previous one
			if($closetable){
			echo "</table>";
			}
			?>		
			<h3 class="style8">Day: <?php echo date("F j, Y",strtotime($appts['date'])); ?></h3>
			<?php $greybg = true; ?>
			<table width="900"  border="0">
			  <tr valign="middle">
				<td width="60" align="middle" class="style6">Time <br/></td>
				<td width="120" align="middle" class="style6">Duration <br/> (minutes)</td>
				<td width="175" align="middle" class="style6">Status <br/></td>
				<td width="280" align="middle" class="style6">Course <br/></td>
				<td align="right" class="style6"></td>
			  </tr>
			<?php
			$closetable = true;	
			$date = $appts['date'];
		}?>
		<tr valign="middle" align="middle"  <?php if($greybg){echo "bgcolor=\"#CCCCCC\""; }?>>
		<td height="30" align="middle" class="style6"><?php echo date("g:i a", strtotime($appts['starttime'])); ?></span></td>
		<td height="30" align="middle" class="style6"><?php echo $appts['duration']; ?></span></td>
		<td height="30" align="middle" class="style6">
		  <?php if($appts['status']=='t'){
			// find who has signed up for that appointment
		
			$q = "SELECT * FROM signups where id='".$appts['id']."'";
			$res = mysql_query($q, $connection);
			$st = mysql_fetch_assoc($res); 
			
			
			//find details about that person
			$q = "SELECT * FROM users where username='".$st['student']."'";
			$res = mysql_query($q, $connection);
			$student = mysql_fetch_assoc($res);
			
			echo "Taken by: ".stripslashes($student['name']);
			if(strlen($st["comment"])> 0)
			{
				echo "<br>Comments: ".substr($st["comment"], 0, 20); ?>
				(<a href="#" onClick="MM_openBrWindow('comment.php?uid=<?php echo $st['student']; ?>&amp;id=<?php echo $st['id']; ?>','comment','scrollbars=yes,resizable=yes,top=20, left=20, width=200,height=300')">More info</a>)<br>
				<?php
				$num_comments = $num_comments + 1;;
			}
		}
		else if($appts['status']=='o'){
			echo "Available";
		}
		else if($appts['status']=='c'){
			echo "You have cancelled this appointment.";
		}
		else if($appts['status']=='p'){
			echo "A cancel request by the student is pending for this appointment.";
		}?>
		</span></td>
		<td height="30" align="middle" class="style6">
		  <span class="style7">
		  <?php 
		if($appts["course"]!="" && isset($_POST[$appts['course']])){
			echo $_POST[$appts["course"]];}
			else {echo 'This appointment is available<br/> in all courses and orgs';}
		?>
		  </span></td>
		<td height="30" align="left" class="style6"><?php if($appts["status"]!="c"){?>
		<form action="cancel.php" method="post" name="<?php echo $appts["id"]; ?>" target="_self">
			<input name="id" type="hidden" value="<?php echo $appts["id"]; ?>">
			<input type="hidden" name="course_id" value="<?php echo $_POST['course_id']; ?>">
			<input type="hidden" name="course_name" value="<?php echo $_POST['course_name']; ?>">
			<input type="hidden" name="course_cid" value="<?php echo $_POST['course_cid']; ?>">
			<input name="Cancel" type="submit" value="Cancel">
		</form><?php } ?></td></tr>
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
   <area shape="rect" coords="2,-2,66,23" href="<?php 
	if ( $_POST["course_id"] != "null" ) {
		echo $url;
		echo "/webapps/octt-octetsign-bb_bb60/links/welcome.jsp?course_id=";
		echo $_POST["course_id"];
	}else{
		echo "https://blackboard.oberlin.edu/";
	}?>">
 </map>
 </div>
 </td></tr></table>

<p><?php
if ( $_POST["course_id"] != "null" ){
	// if no course_id, then no past appiontments
	echo "<a href="."manageviewPast.php?uid=".$_POST['uid'];
	echo "&course_id=".$_POST['course_id'];
	echo "&course_cid=".$_POST['course_cid'];
	echo "&course_id=".$_POST['course_id']; 
	echo '&course_name='.$_POST['course_name'];
	echo ' target="list">view past appointments </a><br>';
	// if no course_id, then cannot create new appiontments
	echo "<a href=".$url;
	echo "/webapps/octt-octetsign-bb_bb60/links/create.jsp?course_id=";
	echo $_POST["course_id"];
	echo ' target="list">create new appointments </a><br>';
}?>
  <span class="style9"><em>will open in a new window</span></p>
</body>
</html>
