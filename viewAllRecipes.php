<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include 'secret.php';

//start or restart session, redirect if user is not logged in
session_start();
if(isset($_SESSION['username']) || !empty($_SESSION['username'])){
	$username = $_SESSION['username'];
	echo '<p class="loginStatus">You are logged in as: ' . $_SESSION['username'] . '</p>';
}else{
	echo '<p class="loginStatus">You are not logged in.</p>';
}

//make database connection 
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", $password, "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

//if user clicked delete 
if($_SERVER['REQUEST_METHOD'] === 'POST'){
	if(isset($_POST['deleteRecipe']) && !empty($_POST['deleteRecipe'])){
		$deleteId = $_POST['deleteRecipe'];
		$deleteRow = $mysqli->prepare("DELETE FROM r_Recipes WHERE rId = ?");
		$deleteRow->bind_param("i", $deleteId);
		$deleteRow->execute();
		$deleteRow->close();
	}	
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>View All Recipes</title>
	<link rel="stylesheet" type="text/css" href="Style.css">
</head>
<body>

	<?php 
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
		<?php 
	} else{
		?>
		<ul class="navbar">
		<li><a href="RecipeMainPage.php" title="Home">Home</a></li>
		<li><a href="viewAllRecipes.php" title="View All Recipes">View All Recipes</a></li>
		</ul>
		<?php 
	}
	?>

	<table class="display" width="70%">
		<h1>All Recipes</h1>
		<hr>
		<tr>
			<th align="left" width="20%">Name
			<th align="left" width="50%">Description
			<th width="10%">
			<th width="10%">
			<th width="10%">
		</tr>
		<?php 

		//display all recipes
		$displayAll = $mysqli->prepare("SELECT * FROM r_Recipes ORDER BY rName");
		$displayAll->execute();
		$displayAll->bind_result($recipeId, $recipeName, $recipeDesc, $recipeServings, $recipeTime, $recipeInstructions, $rCreator, $rCatId, $rUsername);
		while($displayAll->fetch()){
			echo '<tr>';
			echo '<td>' . $recipeName . '</td>';
			echo '<td>' . $recipeDesc . '</td>';
		?>
			<!--http://stackoverflow.com/questions/3317730/putting-a-php-variable-in-a-html-form-value-->
			<form method="POST" action="viewRecipe.php"><td style="white-space: nowrap;"><button type="submit" value="<?php echo htmlspecialchars($recipeId);?>" name="viewRecipe"><img src="btn_view.jpg" alt="View"></button></td></form>
			<?php
			if(!empty($username) && ($username == $rUsername)){
				?>
				<form method="POST" action="editRecipe.php"><td style="white-space: nowrap;"><button type="submit" value="<?php echo htmlspecialchars($recipeId);?>" name="editRecipe"><img src="btn_edit.gif" alt="Edit"></button></td></form>
				<form action="viewAllRecipes.php" method="POST"><td style="white-space: nowrap;"><button type="submit" value="<?php echo htmlspecialchars($recipeId);?>" name="deleteRecipe"><img src="btn_delete.png" alt="Delete"></button></td></form>
				<?php 
			}
		echo '</tr>';
		}
		$displayAll->close();
	?>	
	</table>
</body>
</html>
