<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include 'secret.php';

session_start();
$strMessage = '';

//create database connection 
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", $password, "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
echo '<p class="loginStatus">You are not logged in.</p>';

//if form has been submitted, validate new username and password 
if($_SERVER['REQUEST_METHOD'] === 'POST'){
	$newUsername = '';
	$newPassword = '';
	$newPassword1 = '';

	if(isset($_POST['newAccount']) && !empty($_POST['newAccount'])){
		if(isset($_POST['username']) && !empty($_POST['username']))
			$newUsername = $_POST['username'];
		if(isset($_POST['password']) && !empty($_POST['password']))
			$newPassword = $_POST['password'];
		if(isset($_POST['password1']) && !empty($_POST['password1']))
			$newPassword1 = $_POST['password1'];
	}
	
	//check if username already exists
	$exists = $mysqli->query("SELECT username FROM r_User");
	if($exists){
		while($row = mysqli_fetch_assoc($exists)){
			$arrUsername['username'][] = $row['username'];
		}
	}
		
	for($i=0; $i < sizeof($arrUsername['username']); $i++){
		if(strtolower($arrUsername['username'][$i]) == strtolower($newUsername)){
			$strMessage = "This username is already in use.";
		}
	}
	
	if(($newPassword == $newPassword1) && ($strMessage == '')){
		if($newUsername !== '' && $newPassword !== ''){
			$newPword = base64_encode(hash('sha256', $newPassword));
			$updateName = $mysqli->prepare("INSERT INTO r_User SET username = ?, password = ?");
			$updateName->bind_param("ss", $newUsername, $newPword);
			$updateName->execute();
			$updateName->close();
			echo "New username is: " . $newUsername . "<br>";
		}
	}else if($newPassword != $newPassword1){
		$strMessage = "Your passwords don't match. Please try again.";
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Create New Account</title>
	<script type="text/javascript" src="jquery-1.2.6.min.js"></script>
	<link rel="stylesheet" type="text/css" href="Style.css">
	<script type="text/javascript">
/*http://www.bitrepository.com/web-programming/ajax/username-checker.html*/
 
 //this function checks if the username is available 
$(document).ready(function(){
	$("#username").change(function() { 
	var user = $("#username").val();
	 
	if(user.length >= 4){
		$("#status").html('&nbsp;Checking availability...');
		$.ajax({  
		type: "POST",  
		url: "check.php",
		data: "username="+ user,
		success: function(msg){  
			$("#status").ajaxComplete(function(event, request, settings){ 
				if(msg == 'OK'){ 
					$("#username").removeClass('object_error'); 
					$("#username").addClass("object_ok");
					$(this).html('&nbsp;Available!');
					$('input[name=newAccount]').attr('disabled', false);
				}  
				else{  
					$("#username").removeClass('object_ok'); 
					$("#username").addClass("object_error");
					$(this).html(msg);
					$('input[name=newAccount]').attr('disabled', true);  //disable submit button if name is taken
				}  
		   });
		} 
		
	  }); 
	}
	else{
		$("#status").html('<font color="red">' +
	'The username should have at least 4 characters.</font>');
		$("#username").removeClass('object_ok'); 
		$("#username").addClass("object_error");
		}
	});
});

 
</script>
</head>
<body>

<h1>Create a New Account</h1>
<hr>
<ul class="navbar">
	<li><a href="RecipeMainPage.php" title="Home">Home</a></li>
	<li><a href="viewAllRecipes.php" title="View All Recipes">View All Recipes</a></li>
</ul>

<h3 align="center"> New Account Form</h3>
<?php 
if($strMessage != ''){
	echo '<p class="errorMsg">' . $strMessage . '</p>';
}
?>

<form name="newAccount" id="formId" action="newAccount.php" method="POST">
<table align="center" class="login">
	<tr>
      <td width="300"><div align="right">Please enter a username:&nbsp;</div></td>
      <td width="100"><input id="username" size="20" type="text" name="username" required></td>
      <td width="450" align="left"><div id="status"></div></td>
	</tr>
	<tr>
      <td width="300"><div align="right">Please enter a password:&nbsp;</div></td>
      <td width="100"><input id="username" size="20" type="password" name="password" required></td>
      <td width="450" align="left"><div id="status"></div></td>
	</tr>
	<tr>
      <td width="300"><div align="right">Please re-enter password:&nbsp;</div></td>
      <td width="100"><input id="username" size="20" type="password" name="password1" required></td>
      <td width="450" align="left"><div id="status"></div></td>
	</tr>
	<tr></tr>
	<tr><td>&nbsp;</td><td align="center"><input type="submit" name="newAccount" value="Create Account"></td><td>&nbsp;</td></tr>
</table>
</form>   

</body>
</html>