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
<%@ taglib uri="/bbData" prefix="bbData"%>                
<%@ taglib uri="/bbUI" prefix="bbUI"%>
<bbData:context id="ctx">
<%
/* This page allows users in a course to navigate in the Appoitnments building block.
 * This is the entry page of the building block and it allows users to select an instructor
 * with whom they wish to set up an appointment.
 * Instructors get additional optiona which allow them to create appointments or manage their existing appointments.
 */
String bburl = PlugInUtil.getUri("octt","octetsign","links/");
//create a persistence manager - needed if we want to load anything with a DbLoader
BbPersistenceManager bbPm = BbServiceManager.getPersistenceService().getDbPersistenceManager();

// get the course Id for the current course that we are in - this is the internal id e.g. "_2345_1"
Id courseId = bbPm.generateId(Course.DATA_TYPE, request.getParameter("course_id")); 
%>
<style type="text/css">
<!--
.style1 {
	font-size: 16px;
	font-weight: bold;
} -->
</style>
<bbUI:docTemplate title="Sign-up">
<bbUI:coursePage courseId="<%=courseId%>">
<bbUI:breadcrumbBar handle="course_tools_area" isContent="true">
 <bbUI:breadcrumb>Appointments</bbUI:breadcrumb>
</bbUI:breadcrumbBar>
<bbUI:titleBar>Appointments</bbUI:titleBar>
<%
//get the current user logged into blackboard
String currentUser = ctx.getUser().getUserName();

//indicates whether the instructor options should be displayed or not
boolean manageEnabled = false;

// creates a DbLoader for users
UserDbLoader loader = (UserDbLoader) bbPm.getLoader( UserDbLoader.TYPE );

// creates a list of all the users "enrolled" in the current course
blackboard.base.BbList userlist = null;
userlist = loader.loadByCourseId(courseId);

// creates a DBLoader for course membership objects
CourseMembershipDbLoader cmLoader = (CourseMembershipDbLoader)bbPm.getLoader( CourseMembershipDbLoader.TYPE );
// create a new list to hold instructors for the course
BbList instructors = new BbList();

//iterate through all of the users in this course
BbList.Iterator userIter = userlist.getFilteringIterator();
while(userIter.hasNext())
{
	//get the next user
	User thisUser = (User)userIter.next();
	// now use the CourseMembershipDBLoader to load the CourseMembership data for this user in this course.
	CourseMembership cmData = cmLoader.loadByCourseAndUserId(courseId, thisUser.getId());
	//if the current user's course membership indicates that he/she is an instructor or an assistant
	if (cmData.getRole() == cmData.getRole().INSTRUCTOR || cmData.getRole() == cmData.getRole().TEACHING_ASSISTANT)
	{
		// add the user to the list of instructors
		 instructors.add(thisUser);
		 
		 // if the user is also the current user enable manage option
		 if(thisUser.getUserName().equals(currentUser))
		{
			manageEnabled = true;
		}   
		 
	}
} 

	// sort by last name, first name
	GenericFieldComparator comparator = new GenericFieldComparator(BaseComparator.ASCENDING,"getFamilyName",User.class);
    comparator.appendSecondaryComparator(new GenericFieldComparator(BaseComparator.ASCENDING,"getGivenName",User.class));
    Collections.sort(instructors,comparator);
%>
Choose the instructor with whom you wish to set up an appointment:<br>
<%
//list the instructors for the course
BbList.Iterator instIter = instructors.getFilteringIterator();
int i = 0;
while(instIter.hasNext())
{ 
	User thisUser = (User)instIter.next();
	i++;
	%>
     <a href="<%=bburl%>appointments.jsp?uid=<%=thisUser.getUserName() %>&course_id=<%=request.getParameter("course_id")%>"><%=thisUser.getGivenName() %>&nbsp <%=thisUser.getFamilyName() %></a>
	 <br>
	<%
}
//display manage options if appropriate
if(manageEnabled){
	%>
	<br><br><br>
	<a href="<%=bburl%>manage.jsp?course_id=<%=request.getParameter("course_id")%>">Manage existing appointments</a> or 
	<a href="<%=bburl%>create.jsp?course_id=<%=request.getParameter("course_id")%>">Create new appointments</a>
	<%
}
%>
</bbUI:coursePage>
</bbUI:docTemplate>
</bbData:context>
 