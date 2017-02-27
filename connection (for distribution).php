<?php
$connection=mysql_connect("server w/mysql database","database name here","password for database goes here");
//$url = "https://blackboard.oberlin.edu";
if (!$connection) {
 echo "Could not connect to MySQL server!";
 exit;
}
$db=mysql_select_db("database name",$connection);
if (!$db) {
 echo "Could not access the database";
 exit;
}
?>