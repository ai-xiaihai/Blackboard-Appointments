<%@page import="java.util.*,
				blackboard.base.*,
				blackboard.data.*,
                blackboard.data.user.*,
				blackboard.data.course.*,
                blackboard.persist.*,
                blackboard.persist.user.*,
				blackboard.persist.course.*,
                blackboard.platform.*,
                blackboard.platform.persistence.*,
                blackboard.platform.plugin.PlugInUtil"
        errorPage="/error.jsp"                
%>
<%@include file = "config.jsp" %>
<%@ taglib uri="/bbData" prefix="bbData"%>                
<%@ taglib uri="/bbUI" prefix="bbUI"%>
<%
/* This page contains a form which allows instructors to create appointments.
 * Date and time form can be replaced by date picker from the bbUI tag library
 * in a future version.
 */
String bburl = PlugInUtil.getUri("octt","octetsign","links/");
//create a persistence manager - needed if we want to load anything with a DbLoader
BbPersistenceManager bbPm = BbServiceManager.getPersistenceService().getDbPersistenceManager();

// creates a course loader
CourseDbLoader courseLoader = (CourseDbLoader)bbPm.getLoader(CourseDbLoader.TYPE);

// get the course Id for the current course that we are in - this is the internal id e.g. "_2345_1"
Id courseId = bbPm.generateId(Course.DATA_TYPE, request.getParameter("course_id")); 
%>
<style type="text/css">
<!--
.style1 {
	color: #CC0000;
	font-weight: bold;
}
.style2 {color: #333333}
.style3 {
	font-family: Arial;
	font-size: 11px;
}
-->
</style>

<bbUI:docTemplate title="Create Appointments">
<bbData:context id="ctx">
<bbUI:coursePage courseId="<%=courseId%>">
<bbUI:breadcrumbBar handle="course_tools_area" isContent="true">
 <bbUI:breadcrumb>Create Appointments</bbUI:breadcrumb>
</bbUI:breadcrumbBar>
<div align="right" class="style3">
<a href="<%=bburl%>manage.jsp?course_id=<%=request.getParameter("course_id")%>">
MANAGE APPOINTMENTS</a>
</div>
<bbUI:titleBar iconUrl="/images/ci/icons/calendar_u.gif">Create Appointments</bbUI:titleBar>

<%
//get the current user logged into blackboard
User thisUser = ctx.getUser(); // professor. To get userID, thisUser.getID

// gets the list of courses that have this instructor
BbList<Course> courses = courseLoader.loadByUserIdAndCourseMembershipRole(thisUser.getId(), CourseMembership.Role.INSTRUCTOR);

//get this user's username
String strUsername = thisUser.getUserName();

// creates a DbLoader for users
UserDbLoader loader = (UserDbLoader) bbPm.getLoader( UserDbLoader.TYPE );
// use the loader to load the full user object for this user
blackboard.data.user.User userBb = loader.loadByUserName(strUsername);
//get certain user attributes
String email = userBb.getEmailAddress();
String fname = userBb.getGivenName();
String lname = userBb.getFamilyName();
String name = fname+" "+lname;

//load the course object
Course thisCourse = (Course)courseId.load();
// get the course name and course Id - this is the external Id e.g. "200702-CSCI-151-01"
String courseName = thisCourse.getTitle();
String cId = thisCourse.getCourseId();

//standard Java API stuff
Calendar c = Calendar.getInstance();
int currMonth = c.get(c.MONTH);
int currYear = c.get(c.YEAR);
int currDay = c.get(c.DATE);
int currHour = c.get(c.HOUR);
int currMin = c.get(c.MINUTE);
int ampm = c.get(c.AM_PM);
%>

<form action="<%=phpurl%>create.php" method="post" enctype="multipart/form-data" name="form">
<input type="hidden" name="course_id" value="<%=request.getParameter("course_id")%>">
<input type="hidden" name="course_name" value="<%=courseName%>">
<input type="hidden" name="course_cid" value="<%=cId%>">
<input type="hidden" name="uid" value="<%=strUsername%>">
<input type="hidden" name="name" value="<%=name%>">
<input type="hidden" name="email" value="<%=email%>">
<br>
<bbUI:step title="Appointments Date" number="1">
<bbUI:instructions><span class="style1">Note:</span> <b>For students to see these appointments you must make the Appointment Menu available to them:</b><br>
<span class="style2">+ at the top of the menu >>  Select Tool Link >> Appointments (make available to users)</span><br></bbUI:instructions>
<bbUI:stepContent>
You have the ability to create multiple appointments at once. Select the date as well as the start time and end time of the whole block of appointments you wish to create.<br><br>
<table width="279">
  <tr>
    <td width="69">Date</td>
	<td width="194">
	<select name="month" id="month">
	  <option value="1" <% if(currMonth==c.JANUARY)out.print("selected"); %>>Jan</option>
	  <option value="2" <% if(currMonth==c.FEBRUARY)out.print("selected"); %>>Feb</option>
	  <option value="3" <% if(currMonth==c.MARCH)out.print("selected"); %>>Mar</option>
	  <option value="4" <% if(currMonth==c.APRIL)out.print("selected"); %>>Apr</option>
	  <option value="5" <% if(currMonth==c.MAY)out.print("selected"); %>>May</option>
	  <option value="6" <% if(currMonth==c.JUNE)out.print("selected"); %>>Jun</option>
	  <option value="7" <% if(currMonth==c.JULY)out.print("selected"); %>>Jul</option>
	  <option value="8" <% if(currMonth==c.AUGUST)out.print("selected"); %>>Aug</option>
	  <option value="9" <% if(currMonth==c.SEPTEMBER)out.print("selected"); %>>Sep</option>
	  <option value="10" <% if(currMonth==c.OCTOBER)out.print("selected"); %>>Oct</option>
	  <option value="11" <% if(currMonth==c.NOVEMBER)out.print("selected"); %>>Nov</option>
	  <option value="12" <% if(currMonth==c.DECEMBER)out.print("selected"); %>>Dec</option>
	</select>
	<select name="day" id="day">
	  <option value="1" <% if(currDay==1)out.print("selected");%>>1</option>
	  <option value="2" <% if(currDay==2)out.print("selected");%>>2</option>
	  <option value="3" <% if(currDay==3)out.print("selected");%>>3</option>
	  <option value="4" <% if(currDay==4)out.print("selected");%>>4</option>
	  <option value="5" <% if(currDay==5)out.print("selected");%>>5</option>
	  <option value="6" <% if(currDay==6)out.print("selected");%>>6</option>
	  <option value="7" <% if(currDay==7)out.print("selected");%>>7</option>
	  <option value="8" <% if(currDay==8)out.print("selected");%>>8</option>
	  <option value="9" <% if(currDay==9)out.print("selected");%>>9</option>
	  <option value="10" <% if(currDay==10)out.print("selected");%>>10</option>
	  <option value="11" <% if(currDay==11)out.print("selected");%>>11</option>
	  <option value="12" <% if(currDay==12)out.print("selected");%>>12</option>
	  <option value="13" <% if(currDay==13)out.print("selected");%>>13</option>
	  <option value="14" <% if(currDay==14)out.print("selected");%>>14</option>
	  <option value="15" <% if(currDay==15)out.print("selected");%>>15</option>
	  <option value="16" <% if(currDay==16)out.print("selected");%>>16</option>
	  <option value="17" <% if(currDay==17)out.print("selected");%>>17</option>
	  <option value="18" <% if(currDay==18)out.print("selected");%>>18</option>
	  <option value="19" <% if(currDay==19)out.print("selected");%>>19</option>
	  <option value="20" <% if(currDay==20)out.print("selected");%>>20</option>
	  <option value="21" <% if(currDay==21)out.print("selected");%>>21</option>
	  <option value="22" <% if(currDay==22)out.print("selected");%>>22</option>
	  <option value="23" <% if(currDay==23)out.print("selected");%>>23</option>
	  <option value="24" <% if(currDay==24)out.print("selected");%>>24</option>
	  <option value="25" <% if(currDay==25)out.print("selected");%>>25</option>
	  <option value="26" <% if(currDay==26)out.print("selected");%>>26</option>
	  <option value="27" <% if(currDay==27)out.print("selected");%>>27</option>
	  <option value="28" <% if(currDay==28)out.print("selected");%>>28</option>
	  <option value="29" <% if(currDay==29)out.print("selected");%>>29</option>
	  <option value="30" <% if(currDay==30)out.print("selected");%>>30</option>
	  <option value="31" <% if(currDay==31)out.print("selected");%>>31</option>
	</select>
	<select name="year" id="year">
	<option value="<%=currYear%>" selected><%=currYear%></option>
	<option value="<%=(currYear+1)%>"><%=(currYear+1)%></option>
	</select>
	</td>
</tr>
<tr>
    <td>Start Time</td>
    <td>
	<select name="shour" id="shour">
	  <option value="1" <% if(currHour==1)out.print("selected");%>>01</option>
	  <option value="2" <% if(currHour==2)out.print("selected");%>>02</option>
	  <option value="3" <% if(currHour==3)out.print("selected");%>>03</option>
	  <option value="4" <% if(currHour==4)out.print("selected");%>>04</option>
	  <option value="5" <% if(currHour==5)out.print("selected");%>>05</option>
	  <option value="6" <% if(currHour==6)out.print("selected");%>>06</option>
	  <option value="7" <% if(currHour==7)out.print("selected");%>>07</option>
	  <option value="8" <% if(currHour==8)out.print("selected");%>>08</option>
	  <option value="9" <% if(currHour==9)out.print("selected");%>>09</option>
	  <option value="10" <% if(currHour==10)out.print("selected");%>>10</option>
	  <option value="11" <% if(currHour==11)out.print("selected");%>>11</option>
	  <option value="12" <% if(currHour==12)out.print("selected");%>>12</option>
	</select>
	<select name="sminute" id="sminute">
	  <option value="0" <% if(currMin>=0 && currMin<5)out.print("selected");%>>00</option>
	  <option value="5" <% if(currMin>=5 && currMin<10)out.print("selected");%>>05</option>
	  <option value="10" <% if(currMin>=10 && currMin<15)out.print("selected");%>>10</option>
	  <option value="15" <% if(currMin>=15 && currMin<20)out.print("selected");%>>15</option>
	  <option value="20" <% if(currMin>=20 && currMin<25)out.print("selected");%>>20</option>
	  <option value="25" <% if(currMin>=25 && currMin<30)out.print("selected");%>>25</option>
	  <option value="30" <% if(currMin>=30 && currMin<35)out.print("selected");%>>30</option>
	  <option value="35" <% if(currMin>=35 && currMin<40)out.print("selected");%>>35</option>
	  <option value="40" <% if(currMin>=40 && currMin<45)out.print("selected");%>>40</option>
	  <option value="45" <% if(currMin>=45 && currMin<50)out.print("selected");%>>45</option>
	  <option value="50" <% if(currMin>=50 && currMin<55)out.print("selected");%>>50</option>
	  <option value="55" <% if(currMin>=55 && currMin<60)out.print("selected");%>>55</option>
	</select>
	<select name="sampm" id="sampm">
	  <option value="0" <% if(ampm==c.AM)out.print("selected"); %>>AM</option>
	  <option value="1" <% if(ampm==c.PM)out.print("selected"); %>>PM</option>
	</select>
	</td>
  </tr>
   <tr>
    <td>End Time</td>
    <td>   
	<select name="ehour" id="ehour">
	  <option value="1" <% if(currHour==1)out.print("selected");%>>01</option>
	  <option value="2" <% if(currHour==2)out.print("selected");%>>02</option>
	  <option value="3" <% if(currHour==3)out.print("selected");%>>03</option>
	  <option value="4" <% if(currHour==4)out.print("selected");%>>04</option>
	  <option value="5" <% if(currHour==5)out.print("selected");%>>05</option>
	  <option value="6" <% if(currHour==6)out.print("selected");%>>06</option>
	  <option value="7" <% if(currHour==7)out.print("selected");%>>07</option>
	  <option value="8" <% if(currHour==8)out.print("selected");%>>08</option>
	  <option value="9" <% if(currHour==9)out.print("selected");%>>09</option>
	  <option value="10" <% if(currHour==10)out.print("selected");%>>10</option>
	  <option value="11" <% if(currHour==11)out.print("selected");%>>11</option>
	  <option value="12" <% if(currHour==12)out.print("selected");%>>12</option>
	</select>
	<select name="eminute" id="eminute">
	  <option value="0" <% if(currMin>=0 && currMin<5)out.print("selected");%>>00</option>
	  <option value="5" <% if(currMin>=5 && currMin<10)out.print("selected");%>>05</option>
	  <option value="10" <% if(currMin>=10 && currMin<15)out.print("selected");%>>10</option>
	  <option value="15" <% if(currMin>=15 && currMin<20)out.print("selected");%>>15</option>
	  <option value="20" <% if(currMin>=20 && currMin<25)out.print("selected");%>>20</option>
	  <option value="25" <% if(currMin>=25 && currMin<30)out.print("selected");%>>25</option>
	  <option value="30" <% if(currMin>=30 && currMin<35)out.print("selected");%>>30</option>
	  <option value="35" <% if(currMin>=35 && currMin<40)out.print("selected");%>>35</option>
	  <option value="40" <% if(currMin>=40 && currMin<45)out.print("selected");%>>40</option>
	  <option value="45" <% if(currMin>=45 && currMin<50)out.print("selected");%>>45</option>
	  <option value="50" <% if(currMin>=50 && currMin<55)out.print("selected");%>>50</option>
	  <option value="55" <% if(currMin>=55 && currMin<60)out.print("selected");%>>55</option>
	</select>
	<select name="eampm" id="eampm">
	  <option value="0" <% if(ampm==c.AM)out.print("selected"); %>>AM</option>
	  <option value="1" <% if(ampm==c.PM)out.print("selected"); %>>PM</option>
	</select>
	</td>
  </tr>
</table></bbUI:stepContent>
</bbUI:step>

<bbUI:step title="Time Interval" number="2">
<bbUI:stepContent>How long should each appointment be?<br>
Make each appointment last 
 <select name="duration" id="duration">
	<option value="5">5</option>
	<option value="10">10</option>
	<option value="15" selected>15</option>
	<option value="20">20</option>
	<option value="25">25</option>
	<option value="30">30</option>
	<option value="45">45</option>
	<option value="60">60</option>
 </select>  minutes.
 </bbUI:stepContent>
</bbUI:step>

<bbUI:step title="Availability" number="3">
<bbUI:stepContent>
Make the appointments available<br>
  <label>
  	<input name="courseIDs[]" type="checkbox" value="DIRECTORY">
  	Show in directory tool</label><br>
  <label>
  	<input name="courseIDs[]" type="radio" value="ALL" onchange="checkAll()"> <%--default = checked --%>
  	Available for all courses/organizations</label>.<br>
  <label>
  	<input name="courseIDs[]" type="radio" id="selectCourse" checked onchange="checkAll()">
  	Available in selected courses/organizations</label>.<br>
<%--Will list out the available courses you are an instructor for.--%>
<%--Personal note: using < % and % > gets into javascript coding mode. --%>
<%-- course.getCourseId() gets the id of the course within courses. getTitle() then print the name of the course --%>
<%-- we don't use cId or any of the variables above because they refer to only one course that we're in, not what we have in courses --%>

<%--list all courses if the previous box was checked--%>
<%--part will only appear if above is checked--%>

<div id="courseList" style="visibility: visible; position: relative; left: 25px;">
<% for (Course course: courses){ %>
	<input type="checkbox" name="courseIDs[]" value=<%=course.getCourseId()%>> <%=course.getTitle()%><br>
<% } %>
</div>

<script>
function checkAll() {
	if (document.getElementById("selectCourse").checked){
		document.getElementById("courseList").style.visibility = "visible";
	}
	else {
		document.getElementById("courseList").style.visibility = "hidden";
	}
}
</script>
</bbUI:stepContent>
</bbUI:step>

<bbUI:step title="Restrictions" number="4">
<bbUI:stepContent>
Indicate whether or not you'd like for students to sign up for only one, or multiple time slots.<br>
	<label>
		<input name="timeSlot" type="radio" value="1" checked="checked">
		Students can only sign-up for one time slot.</label><br>
	<label>
		<input name="timeSlot" type="radio" value="0">
		Students can sign up for multiple time slots.</label><br>
	
</bbUI:stepContent>
</bbUI:step>

<bbUI:stepSubmit title="Submit" number="5" />
</form>
</bbUI:coursePage>
</bbData:context>
</bbUI:docTemplate>