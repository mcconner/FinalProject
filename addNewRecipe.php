<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include 'secret.php';

//start or restart connection 
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

$IngredList = '';

//connect to database
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", $password, "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

//get list of ingredients for auto complete 
$getIngredList = $mysqli->query("SELECT iName FROM r_Ingredients ORDER BY iName");
if($getIngredList){
	while($row = mysqli_fetch_assoc($getIngredList)){
		if(empty($IngredList)){
			$IngredList .= "\"" . $row['iName'] . "\"";
		}else{
			$IngredList .= ", \"" . $row['iName'] . "\"";
		}
	}
}

	//get categories
	$catList = $mysqli->query("SELECT catId, catName FROM r_Category ORDER BY catName");
	if($catList){
		while($row = mysqli_fetch_assoc($catList)){
			$arrCat['catId'][] = $row['catId'];
			$arrCat['catName'][] = $row['catName'];
		}
	}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	
	$myIngredients = $_POST["myIngredients"];
	$myQuantities = $_POST["myQuantities"];
	$myUOM = $_POST["myUOM"];
	$recipeUsername = $username;
	
	//count of ingredients added via the '+' button 
	$count = $_REQUEST['count1'];	
	
	//add ingredients to r_Ingredients table
	foreach($myIngredients as $ingredient){
		if(!empty($ingredient)){
			$newIng = $mysqli->prepare("INSERT IGNORE INTO r_Ingredients (iName) VALUES (?)");
			$newIng->bind_param("s", $ingredient);
			$newIng->execute();
			$newIng->close();
		}
	}
	
	if(isset($_POST['addRecipe'])){
		//check if user entered data for recipe name, description, time, servings, instructions , category, creator, & source
		if(isset($_POST['RecipeName']) && !empty($_POST['RecipeName']))
			$recipeName = $_POST['RecipeName'];
		if(isset($_POST['Description']) && !empty($_POST['Description']))
			$recipeDescription = $_POST['Description'];
		if(isset($_POST['Time']) && !empty($_POST['Time']))
			$recipeTime = $_POST['Time'];
		if(isset($_POST['Servings']) && !empty($_POST['Servings']))
			$recipeServings = $_POST['Servings'];
		if(isset($_POST['Category']) && !empty($_POST['Category']))
			$recipeCategory = $_POST['Category'];
		if(isset($_POST['Instructions']) && !empty($_POST['Instructions']))
			$recipeInstructions = $_POST['Instructions'];
		if(isset($_POST['Creator']) && !empty($_POST['Creator']))
			$recipeCreator = $_POST['Creator'];
		if(isset($_POST['Source']) && !empty($_POST['Source']))
			$recipeSource = $_POST['Source'];
		if(isset($_POST['RecipeInstructions']) && !empty($_POST['RecipeInstructions']))
			$recipeInstructions = $_POST['RecipeInstructions'];
		
		//insert into r_Recipes table 
		$newRecipe = $mysqli->prepare("INSERT INTO r_Recipes (rName, rDesc, rServings, rTime, rInstructions, rCatId, rUsername) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$newRecipe->bind_param("ssiisis", $recipeName, $recipeDescription, $recipeServings, $recipeTime, $recipeInstructions, $recipeCategory, $recipeUsername);
		$newRecipe->execute();
		$newRecipe->close();
		
		//find recipe id 
		$recId = $mysqli->prepare("SELECT rId FROM r_Recipes WHERE rName = ?");
		$recId->bind_param("s", $recipeName);
		$recId->execute();
		$recId->bind_result($rrr);
		$recId->fetch();
		$recId->close();
		
		//insert into r_RecipeIngredients table 
		for ($i = 0; $i < $count; $i++){	
			if(!empty($myIngredients[$i])){
				$ingId = $mysqli->prepare("SELECT iId FROM r_Ingredients WHERE iName = ?");
				$ingId->bind_param("s", $myIngredients[$i]);
				$ingId->execute();
				$ingId->bind_result($iii);
				$ingId->fetch();
				$ingId->close();
				
				$newRecIng = $mysqli->prepare("INSERT INTO r_RecipeIngredients (ri_rId, ri_iId, ri_quantity, ri_uom) VALUES (?, ?, ?, ?)");
				$newRecIng->bind_param("iids", $rrr, $iii, $myQuantities[$i], $myUOM[$i]);
				$newRecIng->execute();
				$newRecIng->close();
			}
		}
		
		//add creator name and source 
		$newRecCreator = $mysqli->prepare("INSERT INTO r_Creator (cName, cSource) VALUES (?, ?)");
		$newRecCreator->bind_param("ss", $recipeCreator, $recipeSource);
		$newRecCreator->execute();
		$newRecCreator->close();
		
		$getCreatorId = $mysqli->prepare("SELECT cId FROM r_Creator WHERE cName= ? AND cSource=?");
		$getCreatorId->bind_param("ss", $recipeCreator, $recipeSource);
		$getCreatorId->execute();
		$getCreatorId->bind_result($crId);
		$getCreatorId->fetch();
		$getCreatorId->close();
		
		$getRecipeId = $mysqli->prepare("SELECT rId FROM r_Recipes WHERE rName = ? AND rDesc = ?");
		$getRecipeId->bind_param("ss", $recipeName, $recipeDescription);
		$getRecipeId->execute();
		$getRecipeId->bind_result($reId);
		$getRecipeId->fetch();
		$getRecipeId->close();
		
		$addCreatorId = $mysqli->prepare("UPDATE r_Recipes SET rCreator = ? WHERE rId = ?");
		$addCreatorId->bind_param("ii", $crId, $reId);
		$addCreatorId->execute();
		$addCreatorId->close();
		
		echo "Recipe " . $recipeName . " has been successfully added!";
	
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Add New Recipe</title>
	<link href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="Style.css">
	<script src="jquery-1.11.3.min.js" type="text/javascript"></script>
	<!--http://www.joegarrepy.com/tableaddrow_jscript.htm
	http://www.randomsnippets.com/2008/02/21/how-to-dynamically-add-form-elements-via-javascript/ -->
	<script>
		var count = 3;
		 function addRow(in_tbl_name)
		 {
			var tbody = document.getElementById(in_tbl_name).getElementsByTagName("TBODY")[0];
			// create row
			var row = document.createElement("tr");
			// create table cell 1
			var td1 = document.createElement("td");
			var temp1 = "myIngredients[" + count + "]";
			td1.innerHTML = "<input type='text' name='" + temp1 + "' class='cIngred' id='searchIngr'>";
			// create table cell 2
			var td2 = document.createElement("td");
			var temp2 = "myQuantities[" + count + "]"; 
			td2.innerHTML = "<input type='number' name='" + temp2 + "' step='0.01'>";
			// create table cell 3
			var td3 = document.createElement("td");
			var temp3 = "myUOM[" + count + "]";
			td3.innerHTML = "<input type='text' name='" + temp3 + "'>";
			// append data to row
			row.appendChild(td1);
			row.appendChild(td2);
			row.appendChild(td3);
			// add to count variable & store
			count = parseInt(count) + 1;
			document.getElementById("count1").value = count;
			// append row to table
			tbody.appendChild(row);
		}
	</script>
</head>
<body>
<!--http://html.cita.illinois.edu/nav/menu/menu-example-hrzt.php -->
	<script src="jquery-ui.min-1.11.1.js" type="text/javascript"></script>

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


<h1>Add a New Recipe</h1>
<hr>
<div>
	<form method="POST" action="addNewRecipe.php">
	
	<table class="display" width="70%">
		<tr>
			<td>Recipe Name: </td>
			<td><input type="text" name="RecipeName" id="RecipeName" size="40" maxlength="40" required></td>
			<td><div id="status"></div></td>
		</tr>
		<tr>
			<td>Description: </td>
			<td><input type="text" name="Description" size="70" maxlength="90" required></td>
			<td></td>
		</tr>
		<tr>
			<td>Time (minutes): </td>
			<td><input type="number" name="Time" min="1"></td>
			<td></td>
		</tr>
		<tr>
			<td>Servings: </td>
			<td><input type="number" name="Servings" min="1"></td>
			<td></td>
		</tr>
		<tr>
			<td>Category: </td>
			<td><select name="Category" required>
			<?php 
			for($i=0; $i<sizeof($arrCat['catId']); $i++){
				echo '<option value="'.$arrCat['catId'][$i].'">'.$arrCat['catName'][$i].'</option>';
			}
			?>
			</select></td>
			<td></td>
		</tr>
		<tr>
			<td>Creator: </td>
			<td><input type="text" name="Creator" size="30" maxlength="30"></td>
			<td></td>
		</tr>
		<tr>
			<td>Source: </td>
			<td><input type="text" name="Source" size="50" maxlength="90"></td>
			<td></td>
		</tr>		
		<tr>
			<td valign="top">Instructions: </td>
			<td><textarea name="RecipeInstructions" style="width:500px; height:150px;" required></textarea></td>
			<td></td>
		</tr>
				<table id="tblIngredients" class="display" width="70%"><tr>
					<td>Ingredient Name</td>
					<td>Quantity</td>
					<td>Unit of Measurement</td>
				</tr>
				<tr>
					<td><input type="text" name="myIngredients[]" id="txtIngred" class="cIngred"></td>	
					<div id="results"></div>
					<td><input type="number" name="myQuantities[]" step="0.01"></td>
					<td><input type="text" name="myUOM[]"></td>
				</tr>
				<tr>
					<td><input type="text" name="myIngredients[]" id="txtIngred" class="cIngred"></td>
					<div id="results"></div>
					<td><input type="number" name="myQuantities[]" step="0.01"></td>
					<td><input type="text" name="myUOM[]"></td>
				</tr>
				<tr>
					<td><input type="text" name="myIngredients[]" id="txtIngred" class="cIngred"></td>
					<div id="results"></div>
					<td><input type="number" name="myQuantities[]" step="0.01"></td>
					<td><input type="text" name="myUOM[]"></td>
				</tr>
				</table>
				<input type="hidden" name="count1" id="count1" value="0">
		<tr>
			<td>&nbsp;</td><td><input type="button" onClick="addRow('tblIngredients')" value="+"></td>
		</tr>
	</table>

	<br>
	<p align= "center"><input type="submit" name="addRecipe" value="Add Recipe"></p>

	</form>
</div>
    <script>
	//this function implements autocomplete on the ingredient text boxes
		$(document).ready(function () {
			 $("input.cIngred").each(function() {
				var ingredTags = [<?php echo $IngredList; ?>];
				$(this).autocomplete({
					source: ingredTags
				});
			 });
		});
    </script>
	   
	      <!-- <script>
         $(document).ready(function () {
             var ingredTags = [<?php echo $IngredList ?>];
             $("#txtIngred").autocomplete({
                 source: ingredTags
             });
         });
    </script>-->


</body>
</html>