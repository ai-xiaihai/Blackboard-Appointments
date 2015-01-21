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
 * This page is meant to pull all of the information needed for a professor to be able to manage their appointments in blackboard.
 * The page which allows them to manage the appointments is manage.php
 * Information pulled from Blackboard includes certain attributes of the current user and a list of the courses and organizationns
 * where he/she is enrolled in as an instructor or teaching_assitant.
 */
 
//get the current user logged into blackboard
User thisUser = ctx.getUser();

//create a persistence manager - needed if we want to load anything with a DbLoader
BbPersistenceManager bbPm = BbServiceManager.getPersistenceService().getDbPersistenceManager();	

// creates a DbLoader for courses
CourseDbLoader cLoader = (CourseDbLoader) bbPm.getLoader( CourseDbLoader.TYPE );

// load all courses where the current user is an instructor or a teaching assitant
// these correspond to leaders and assitants in organizations
BbList courselist = cLoader.loadByUserIdAndCourseMembershipRole(thisUser.getId(), CourseMembership.Role.INSTRUCTOR);
courselist.addAll(cLoader.loadByUserIdAndCourseMembershipRole(thisUser.getId(), CourseMembership.Role.TEACHING_ASSISTANT));

// remove unavailable courses from the list
courselist = courselist.getFilteredSubList(new AvailabilityFilter(AvailabilityFilter.AVAILABLE_ONLY));

//sort the courses by their id	
GenericFieldComparator comparator = new GenericFieldComparator(BaseComparator.ASCENDING,"getCourseId",Course.class);
Collections.sort(courselist,comparator);

// get the course Id for the current course that we are in - this is the internal id e.g. "_2345_1"
Id courseId = bbPm.generateId(Course.DATA_TYPE, request.getParameter("course_id"));

//load the course object
Course thisCourse = (Course)courseId.load();

// get the course name and course Id - this is the external Id e.g. "200702-CSCI-151-01"
String courseName = thisCourse.getTitle();
String cId = thisCourse.getCourseId();

//create a form with the relevant information to pass to the php page
%>
<form action="<%=phpurl%>manage.php" method="post" enctype="multipart/form-data" name="form">
<input type="hidden" name="uid" value="<%=thisUser.getUserName()%>">
<input type="hidden" name="course_id" value="<%=request.getParameter("course_id")%>">
<input type="hidden" name="course_name" value="<%=courseName%>">
<input type="hidden" name="course_cid" value="<%=cId%>">
<%
//iterate through all the  courses that we pulled for this user
BbList.Iterator courseIter = courselist.getFilteringIterator();
	while (courseIter.hasNext())
	{
		Course course = (Course)courseIter.next();
		//get course name and internal course Id represented as string e.g."_2345_1"
		%>
		<input name="<%=course.getId().toExternalString()%>" type="hidden" value="<%=course.getTitle()%>">
		<%
	}
%>
</form>
</bbData:context>