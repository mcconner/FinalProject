<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include 'secret.php';

session_start();
if(isset($_SESSION['username']) || !empty($_SESSION['username'])){
	$username = $_SESSION['username'];
	echo '<p class="loginStatus">You are logged in as: ' . $_SESSION['username'] . '</p>';
}else{
	$filePath = explode('/', $_SERVER['PHP_SELF'], -1);
	$filePath = implode('/', $filePath);
	$redirect = "//" . $_SERVER['HTTP_HOST'] . $filePath;
	header("location: {$redirect}/RecipeMainPage.php", true);
}

$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", $password, "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
?>
	
	
<!DOCTYPE html>
<html>
<head>
	<title>Edit Recipes</title>
	<link rel="stylesheet" type="text/css" href="Style.css">
</head>
<body>

<ul class="navbar">
	<li><a href="RecipeMainPage.php" title="Home">Home</a></li>
	<li><a href="viewMyRecipes.php" title="View My Recipes">View My Recipes</a></li>
	<li><a href="viewAllRecipes.php" title="View All Recipes">View All Recipes</a></li>
	<li><a href="addNewRecipe.php" title="Add New Recipe">Add New Recipe</a></li>
	<li><a href="addCategory.php" title="Add Category">Add Category</a></li>
	<li><a href="editRecipes.php" title="Edit Recipes">Edit Recipes</a></li>
	<li><a href="Recipelogout.php" title="Logout">Logout</a></li>
</ul>

	<h1>Edit Recipes</h1>
	<hr>
	<h3 align="center">Choose a Recipe to Edit:</h3>
		<?php 
		$cArr = array();

		$ddRecipes = $mysqli->prepare("SELECT rId, rName FROM r_Recipes WHERE rUsername = ? ORDER BY rName ");
		$ddRecipes->bind_param("s", $username);
		$ddRecipes->execute();
		$ddRecipes->store_result();
		if($ddRecipes->num_rows > 0){
			$ddRecipes->bind_result($id, $rList);
			echo '<form style="text-align: center" method="POST" action="editRecipe.php" name="editRecipe" value="-SELECT-">';
			echo '<select name="editRecipe">';
			echo '<option disabled="disabled" selected="selected" style="display:none" value="All">-Select a Recipe-</option>';
			while($ddRecipes->fetch()){
				array_push($cArr, $rList);
				echo "<option value='" . $id . "'>" . $rList . "</option>";
			}
			$ddRecipes->close();
			echo '&nbsp;</select>';
			echo '<p align="center"><input type="submit" value="Edit"></p>';
			echo '</form>';
			echo '<br>';
			echo '</div>';
		}else{
			echo '<p class="errorMsg" align="center">You do not have any recipes to edit.</p>';
		}
		?>
</body>
</html>

