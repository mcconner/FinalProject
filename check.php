<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include 'secret.php';

//make database connection 
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", $password, "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

//http://www.bitrepository.com/a-simple-ajax-username-availability-checker.html
//http://www.2my4edge.com/2013/08/autocomplete-search-using-php-mysql-and.html

if(isset($_POST['newCategory'])){
	$newCategory = $_POST['newCategory'];
	
	$sql_check = $mysqli->prepare("SELECT catName FROM r_Category WHERE catName = ?");
	$sql_check->bind_param("s", $newCategory);
	$sql_check->execute();
	$sql_check->store_result();
	$numRows = $sql_check->num_rows;
	$sql_check->close();
 
	if($numRows > 0) {
		echo '<font color="red">This category is already in the list.</font>';
	} else {
		echo 'OK';
	}
	
}else if(isset($_POST['username'])) {
	$username = $_POST['username'];
 
 	$sql_check = $mysqli->prepare("SELECT username FROM r_User WHERE username = ?");
	$sql_check->bind_param("s", $username);
	$sql_check->execute();
	$sql_check->store_result();
	$numRows = $sql_check->num_rows;
	$sql_check->close();
 
	if($numRows > 0) {
		echo '<font color="red">The username '.$username.' is already in use.</font>';
	} else {
		echo 'OK';
	}
}
?>