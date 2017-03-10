<%@page import="java.util.*,
				blackboard.base.*,
				blackboard.data.*,
                blackboard.data.user.*,
				blackboard.data.course.*,
                blackboard.persist.*,
                blackboard.persist.user.*,
				blackboard.persist.course.*,
                blackboard.platform.*,
                blackboard.platform.persistence.*"
        errorPage="/error.jsp"                
%>
<%@include file = "config.jsp" %>
<%@ taglib uri="/bbData" prefix="bbData"%>                
<%@ taglib uri="/bbUI" prefix="bbUI"%>
<body onLoad="document.form.submit()">
<bbData:context id="ctx">
<%

/*
 * This page is meant to pull all of the information needed for a student to be able to sign up for an appointment in blackboard.
 * The page which allows them to sign up or view the appointments is view.php
 * Information pulled from Blackboard includes certain attributes of the current user
 * and current course as well as attributes of the instructor of the course.
 */
//get the current user logged into blackboard
User thisUser = ctx.getUser();
//get this user's username
String strUsername = thisUser.getUserName();

//create a persistence manager - needed if we want to load anything with a DbLoader
BbPersistenceManager bbPm = BbServiceManager.getPersistenceService().getDbPersistenceManager();

// creates a DbLoader for users
UserDbLoader loader = (UserDbLoader) bbPm.getLoader( UserDbLoader.TYPE );

// creates a DbLoader for courses
CourseDbLoader cLoader = (CourseDbLoader) bbPm.getLoader( CourseDbLoader.TYPE );

// use the loader to load the full user object for this user
blackboard.data.user.User userBb = loader.loadByUserName(strUsername);

// get certain user attributes
String email = userBb.getEmailAddress();
String fname = userBb.getGivenName();
String lname = userBb.getFamilyName();
String name = fname+" "+lname;

// default value for courseName and cId
String courseName = request.getParameter("uid") + " \'s appointments";
String cId = "OCTET";
// if-else block to catch empty course_id to prevent Exceptions
if ( request.getParameter("course_id") != null ) {
        // get the course Id for the current course that we are in - this is the internal id e.g. "_2345_1"
        Id courseId = bbPm.generateId(Course.DATA_TYPE, request.getParameter("course_id"));

        // load the course object
        Course thisCourse = (Course)courseId.load(); 
        // get the course name and course Id - this is the external Id e.g. "200702-CSCI-151-01"
        courseName = thisCourse.getTitle();
        cId = thisCourse.getCourseId();
} 

//create a form with all of thise relevant informatino and pass it along to the php page
%>
<form action="<%=phpurl%>view.php" method="post" name="form">
<input type="hidden" name="course_id" value="<%=request.getParameter("course_id")%>">
<input type="hidden" name="course_name" value="<%=courseName%>">
<input type="hidden" name="course_cid" value="<%=cId%>">
<input type="hidden" name="username" value="<%=strUsername%>">
<input type="hidden" name="name" value="<%=name%>">
<input type="hidden" name="email" value="<%=email%>">
<input type="hidden" name="instructor" value="<%=request.getParameter("uid")%>">
<!--<input type="submit">-->
</form>
</bbData:context>