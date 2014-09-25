<?php
$connection=mysql_connect("www.oberlin.edu","database name here","password for database goes here");
$url = "https://blackboard.oberlin.edu";
if (!$connection) {
 echo "Could not connect to MySQL server!";
 exit;
}
$db=mysql_select_db("OnCampus",$connection);
if (!$db) {
 echo "Could not access the database";
 exit;
}
?>