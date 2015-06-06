<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include 'secret.php';

//start or restart session
session_start();

//create database connection 
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", $password, "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$strAction = '';
$username = '';

//if username is already set (user is logged in)
if(isset($_SESSION['username'])){
	$username = $_SESSION['username'];
	$strAction = 'welcome';
}

if(isset($_GET['action']))
	$strAction = $_GET['action'];

//if POST, check if username and password are valid 
if($_SERVER['REQUEST_METHOD'] === 'POST'){
	if(isset($_POST['login'])){
		$uname = $_POST['username'];
		$pword = $_POST['password'];
		$checkPword = base64_encode(hash('sha256', $pword));
		
		$sql = $mysqli->prepare("SELECT * FROM r_User WHERE username = ? AND password = ?");
		$sql->bind_param("ss", $uname, $checkPword);
		$sql->execute();
		$sql->store_result();
		$numRows = $sql->num_rows;
		$sql->close();
		
		if(!$numRows == 1){
			$strAction = 'invalid';
		}else{
			$_SESSION['username'] = $uname;
			$strAction = 'welcome';
		}	
	}
	//if user did not enter a username 
	if($_POST['username'] == ''){
		$strAction = 'noname';
	}
}

//display welcome message if user is logged in, else display error message 
if(($strAction === 'welcome')){
	echo '<p class="loginStatus">You are logged in as: ' . $_SESSION['username'] . '</p>';
}else if($strAction === ''){
	echo '<p class="loginStatus">You are not logged in.</p>';
} else if($strAction == 'logout'){
	$_SESSION = array();
	session_destroy();
	$filePath = explode('/', $_SERVER['PHP_SELF'], -1);
	$filePath = implode('/', $filePath);
	$redirect = "//" . $_SERVER['HTTP_HOST'] . $filePath;
	header("location: {$redirect}/viewAllRecipes.php", true);
	die();
} else if($strAction == 'invalid'){
	echo '<p class="errorMsg">Please enter a valid username and password combination.</p>';
}else if($strAction == 'noname'){
	echo '<p class="errorMsg">Please enter a username.</p>';
}else{
	echo '<p class="errorMsg">An unknown error occurred. Please try again.</p>';
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Recipe Home Page</title>
	<link rel="stylesheet" type="text/css" href="Style.css">
</head>
<body>
	<h1>Recipe Database</h1>
	<hr>
	<?php 
	//user is logged in  
	if(isset($_SESSION['username']) && !empty($_SESSION['username'])){
		?>
		<ul class="navbar">
			<li><a href="RecipeMainPage.php" title="Home">Home</a></li>
			<li><a href="viewMyRecipes.php" title="View My Recipes">View My Recipes</a></li>
			<li><a href="viewAllRecipes.php" title="View All Recipes">View All Recipes</a></li>
			<li><a href="addNewRecipe.php" title="Add New Recipe">Add New Recipe</a></li>
			<li><a href="addCategory.php" title="Add Category">Add Category</a></li>
			<li><a href="editRecipes.php" title="Edit Recipes">Edit Recipes</a></li>
			<li><a href="Recipelogout.php" title="Logout">Logout</a></li>
		</ul>
		<h3 align="center">Welcome, <?php echo $_SESSION['username'] ?>!</h3> 

		<!--display pictures -->
		<div class="images" style="background-image:url('coconutShrimp.jpg'); margin: 0px 6px 6px 35px; "></div>
		<div class="images" style="background-image:url('splitPeaSoup.jpg'); margin: 0px 6px 6px 6px;"></div>
		<div class="images" style="background-image:url('pestoPasta.jpg'); margin: 0px 6px 6px 6px;"></div>
		<div class="images" style="background-image:url('bananaBread.jpg'); margin: 0px 6px 6px 6px;"></div>
		<div class="images" style="background-image:url('oatmealCookies.jpg'); margin: 0px 6px 6px 6px;"></div>
		<div class="images" style="background-image:url('omelette.jpg'); margin: 0px 6px 6px 35px; "></div>
		<div class="images" style="background-image:url('strawberrySmoothie.jpg'); margin: 0px 6px 6px 6px;"></div>
		<div class="images" style="background-image:url('mushroomRisotto.jpg'); margin: 0px 6px 6px 6px;"></div>
		<div class="images" style="background-image:url('berryCrumble.jpg'); margin: 0px 6px 6px 6px;"></div>
		<div class="images" style="background-image:url('pancakes.jpg'); margin: 0px 6px 6px 6px;"></div>

		<?php 	
	} else {
		//user is not logged in 
		?>
		<ul class="navbar">
			<li><a href="RecipeMainPage.php">Home</a></li>
			<li><a href="viewAllRecipes.php">View All Recipes</a></li>
		</ul>
		<h3 align="center">Welcome, please login </h3>
		<form name="login" action="RecipeMainPage.php" method="POST">
		<table align="center" class="login">
			<tr>
				<td>Username: </td>
				<td><input type="text" name="username" required></td>
			</tr>
			<tr>
				<td>Password: </td>
				<td><input type="password" name="password" required></td>
			</tr>
			<tr>
				<td colspan="2" align="center"><input type="submit" name="login" value="Log-in" onClick="updateProfile()"></td>
			</tr>
		</table>
		</form> 
		<p align="center"><a class="newacct" href="newAccount.php">Click here to create an account</a></p>
		<?php 
	}
	?>
</body>
</html>
