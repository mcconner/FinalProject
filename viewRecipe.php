<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include 'secret.php';

session_start();
if(isset($_SESSION['username']) || !empty($_SESSION['username'])){
	$username = $_SESSION['username'];
	echo '<p class="loginStatus">You are logged in as: ' . $_SESSION['username'] . '</p>';
}else{
	$username = '';
	echo '<p class="loginStatus">You are not logged in.</p>';
}

$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", $password, "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	if(isset($_POST['viewRecipe']) && !empty($_POST['viewRecipe'])){
		$recipeIdToView = $_POST['viewRecipe'];
		$viewCat = $mysqli->prepare("SELECT catName FROM r_Category INNER JOIN r_Recipes ON catId = rCatId WHERE rId = ?");
		$viewCat->bind_param("i", $recipeIdToView);
		$viewCat->execute();
		$viewCat->bind_result($catName); 
		$viewCat->fetch();
		$viewCat->close();
		
		$viewIngredients = $mysqli->prepare("SELECT iName FROM r_Ingredients INNER JOIN r_RecipeIngredients ON iId = ri_iId INNER JOIN r_Recipes ON ri_rId=rId WHERE rId = ?");
		$viewIngredients->bind_param("i", $recipeIdToView);
		$viewIngredients->execute();
		$viewIngredients->bind_result($ingredient);
		$arrIng = array();
		while($viewIngredients->fetch()){
			$arrIng[] = $ingredient;
		}
		$viewIngredients->close();
		
		$ingDetails = $mysqli->prepare("SELECT ri_rId, ri_iId, ri_quantity, ri_uom FROM r_RecipeIngredients INNER JOIN r_Ingredients ON ri_iId = iId INNER JOIN r_Recipes ON ri_rId=rId WHERE rId = ?");
		$ingDetails->bind_param("i", $recipeIdToView);
		$ingDetails->execute();
		$ingDetails->bind_result($recId, $ingId, $quan, $uom);
		$arrQuan = array();
		$arrUom = array();
		while($ingDetails->fetch()){
			$arrQuan[] = $quan;
			$arrUom[] = $uom;
		}
		$ingDetails->close();
		
		$viewRecipe = $mysqli->prepare("SELECT * FROM r_Recipes LEFT JOIN r_Creator ON rCreator = cId WHERE rId = ?");
		$viewRecipe->bind_param("i", $recipeIdToView);
		$viewRecipe->execute();
		$viewRecipe->bind_result($rId, $rName, $rDesc, $rServings, $rTime, $rInstructions, $rCreator, $rUsername, $rCatId, $cId, $cName, $cSource);
		?>
		
<!DOCTYPE html>
<html>
<head>
	<title>View Recipe</title>
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
		} else {
			?>
			<ul class="navbar">
				<li><a href="RecipeMainPage.php" title="Home">Home</a></li>
				<li><a href="viewAllRecipes.php" title="View All Recipes">View All Recipes</a></li>
			</ul>
			<?php 
		}
		while ($viewRecipe->fetch()){
			echo '<h1>' . $rName . '</h1>';
			echo '<hr>';
			echo '<table>';
			echo '<tr><td>Description:</td><td>' . $rDesc . '</td></tr>';
			echo '<tr><td>Serves:</td><td>' . $rServings . '</td></tr>';
			echo '<tr><td>Time:</td><td>' . $rTime . '&nbsp;minutes</td></tr>';
			echo '<tr><td>Category:</td><td>' . $catName . '</td></tr>';
			echo '<tr><td>Creator:</td><td>' . $cName . '</td></tr>';
			echo '<tr><td>Source:</td><td>' . $cSource . '</td></tr>';
			echo '<tr><td>Ingredients:</td><td>&nbsp;</td></tr>';
			for($i=0; $i<count($arrIng); $i++){
				echo '<tr><td></td><td>' . $arrQuan[$i] . " " . $arrUom[$i] . " " . $arrIng[$i] . '</td></tr> ';
			}
			echo '<tr></tr>';
			echo '<tr><td>Instructions:</td><td>&nbsp;</td></tr>';
			echo '<tr><td></td><td>' . $rInstructions . '</td><td></td></tr>';
			echo '</table>';
		}
		$viewRecipe->close();
	}
}
?>
</div>
</body>
</html>