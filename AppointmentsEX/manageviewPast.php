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
<title>View Past Appointments</title>
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
	font-size: 10px;
	font-family: Arial;
}
.style6 {font-size: 12px; font-family: Arial; }
.style8 {font-family: Arial}    
.style9 {color: #FFFFFF}
-->
</style>
</head>
<body>
<span class="style1"><br>
&nbsp;&nbsp;</span>
<table width="900"  border="0" class="style6"><tr>
  <td> This has opened a new window. You can only VIEW past appointments in this window.<br>
</td></tr></table>
<?php
// pull all appointments from the database for this instructor
  $today = time();
  $sql = "SELECT * FROM appointment as t1, appointmentowner as t2 WHERE t2.username='".substr($_GET["uid"], 0, 9)."' AND t1.id=t2.id ORDER BY date DESC, starttime";
$result = mysql_query($sql, $connection);
 $connection;
$date = "";
$closetable = false;

$num_appts = 0;
?>
<table width="900"  border="0">
			  <tr valign="middle">
			    <td width="60" align="middle" class="style6">Day</td>
				<td width="60" align="middle" class="style6">Time</td>
				<td width="120" align="middle" class="style6">Duration (minutes)</td>
				<td width="175" align="middle" class="style6">Status</td>
				<td width="280" align="middle" class="style6">Course</td>
			  </tr>
<?php			  
while($appts = mysql_fetch_assoc($result)){// for every appointment in the database for this user
	// make sure the appointment is in the future, we do not want to display appointments in the past.
	$num_comments = 0;
	//if($today < strtotime($appts['date']." ".$appts['starttime'])){
		$num_appts++;
		// find details about the owner (name and email)
		$q = "SELECT * FROM users where username='".$appts['username']."'";
		$res = mysql_query($q, $connection);
		$professor = mysql_fetch_assoc($res);
		// display appointment
		//if(strcmp($date, $appts['date'])!=0){ //display new date header if it's different from the previous one
		//	if($closetable){
		//	echo "</table>";
		//	}
			?>		
			<?php $greybg = true; ?>
			
			<?php
		//	$closetable = true;	
		//	$date = $appts['date'];
		//}
		?>
		<tr valign="middle" align="middle"  <?php if($greybg){echo "bgcolor=\"#CCCCCC\""; }?>>
		  <td align="middle" class="style6"><span class="style8"><?php echo date("F j, Y",strtotime($appts['date'])); ?></span></td>
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
				(<a href="#" onClick="MM_openBrWindow('comment.php?uid=<?php echo $st['student']; ?>&amp;id=<?php echo $st['id']; ?>','comment','scrollbars=yes,resizable=yes,width=200,height=300')">More info</a>)<br>
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
		if($appts["course"]!="" )
			echo $appts["course"];
		?>
		  </span></td>
		</tr>
	<?php
		$greybg = !$greybg;
	}
//}
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
   <area shape="rect" coords="2,-2,66,23" href="<?php echo $url;?>">
 </map>
 </div>
 </td></tr></table>
<p>
</body>
</html>
