<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include 'secret.php';

//restart session or redirect if user is not logged in 
session_start();
$strMessage = '';
if(isset($_SESSION['username']) || !empty($_SESSION['username'])){
	$username = $_SESSION['username'];
	echo '<p class="loginStatus">You are logged in as: ' . $_SESSION['username'] . '</p>';
}else{
	$filePath = explode('/', $_SERVER['PHP_SELF'], -1);
	$filePath = implode('/', $filePath);
	$redirect = "//" . $_SERVER['HTTP_HOST'] . $filePath;
	header("location: {$redirect}/RecipeMainPage.php", true);
}

//make database connection 
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", $password, "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	$newName = '';
	$newDesc = '';
	$newTime = '';
	$newServ = '';
	$newCategory = '';
	$newInst = '';
	$newIng = '';
	$newCreator = '';
	$newSource = '';
	$addIng = '';
	$addQ = '';
	$addU = '';
	
	$catList = $mysqli->query("SELECT catId, catName FROM r_Category ORDER BY catName");
	if($catList){
		while($row = mysqli_fetch_assoc($catList)){
			$arrCat['catId'][] = $row['catId'];
			$arrCat['catName'][] = $row['catName'];
		}
	}
	
	if(isset($_POST['updateRecipe']) && !empty($_POST['updateRecipe'])){
		$updateId = $_POST['updateRecipe'];
		//check if user entered data for recipe name, description, time, servings, instructions 
		if(isset($_POST['updatedName']) && !empty($_POST['updatedName']))
			$newName = $_POST['updatedName'];
		if(isset($_POST['updatedDesc']) && !empty($_POST['updatedDesc']))
			$newDesc = $_POST['updatedDesc'];
		if(isset($_POST['updatedTime']) && !empty($_POST['updatedTime']))
			$newTime = $_POST['updatedTime'];
		if(isset($_POST['updatedServings']) && !empty($_POST['updatedServings']))
			$newServ = $_POST['updatedServings'];
		if(isset($_POST['updatedCategory']) && !empty($_POST['updatedCategory']))
			$newCategory = $_POST['updatedCategory'];
		if(isset($_POST['updatedInstructions']) && !empty($_POST['updatedInstructions']))
			$newInst = $_POST['updatedInstructions'];
		if(isset($_POST['updatedIngredients']) && !empty($_POST['updatedIngredients']))
			$newIng = $_POST['updatedIngredients'];
		if(isset($_POST['updatedQuantities']) && !empty($_POST['updatedQuantities']))
			$newQuan = $_POST['updatedQuantities'];
		if(isset($_POST['updatedUoms']) && !empty($_POST['updatedUoms']))
			$newUom = $_POST['updatedUoms'];
		if(isset($_POST['updatedCreator']) && !empty($_POST['updatedCreator']))
			$newCreator = $_POST['updatedCreator'];
		if(isset($_POST['updatedSource']) && !empty($_POST['updatedSource']))
			$newSource = $_POST['updatedSource'];
		if(isset($_POST['addIngredients']) && !empty($_POST['addIngredients']))
			$addIng = $_POST['addIngredients'];
		if(!empty($_POST['addQuantities']))
			$addQ = $_POST['addQuantities'];
		if(!empty($_POST['addUoms']))
			$addU = $_POST['addUoms'];
		
		if($newName !== ''){
			$updateName = $mysqli->prepare("UPDATE r_Recipes SET rName = ? WHERE rId = ?");
			$updateName->bind_param("si", $newName, $updateId);
			$updateName->execute();
			$updateName->close();
		}
		if($newDesc != ''){
			$updateDesc = $mysqli->prepare("UPDATE r_Recipes SET rDesc = ? WHERE rId = ?");
			$updateDesc->bind_param("si", $newDesc, $updateId);
			$updateDesc->execute();
			$updateDesc->close();
		}
		if($newTime != ''){
			$updateTime = $mysqli->prepare("UPDATE r_Recipes SET rTime = ? WHERE rId = ?");
			$updateTime->bind_param("ii", $newTime, $updateId);
			$updateTime->execute();
			$updateTime->close();
		}
		if($newServ != ''){
			$updateServ = $mysqli->prepare("UPDATE r_Recipes SET rServings = ? WHERE rId = ?");
			$updateServ->bind_param("ii", $newServ, $updateId);
			$updateServ->execute();
			$updateServ->close();
		}
		
		if($newCategory != '' && $newCategory != '0'){
			$updateCategory = $mysqli->prepare("UPDATE r_Recipes SET rCatId = ? WHERE rId = ?");
			$updateCategory->bind_param("ii", $newCategory, $updateId);
			$updateCategory->execute();
			$updateCategory->close();
		}
		
		if($newInst != ''){
			$updateInst = $mysqli->prepare("UPDATE r_Recipes SET rInstructions = ? WHERE rId = ?");
			$updateInst->bind_param("si", $newInst, $updateId);
			$updateInst->execute();
			$updateInst->close();
		}
		
		if($newCreator != '' || $newSource != ''){
			$crId = $mysqli->prepare("SELECT rCreator FROM r_Recipes INNER JOIN r_Creator ON r_Recipes.rCreator = r_Creator.cId WHERE r_Recipes.rId = ?");
			$crId->bind_param("i", $updateId);
			$crId->execute();
			$crId->bind_result($creatorId);
			$crId->fetch();
			$crId->close();
			if($newSource != '' && $newCreator == ''){
				$updateCr = $mysqli->prepare("UPDATE r_Creator INNER JOIN r_Recipes ON r_Creator.cId = r_Recipes.rCreator SET cSource = ? WHERE cId = ?");
				$updateCr->bind_param("si", $newSource, $creatorId);
			}else if($newSource == '' && $newCreator != ''){
				$updateCr = $mysqli->prepare("UPDATE r_Creator INNER JOIN r_Recipes ON r_Creator.cId = r_Recipes.rCreator SET cName = ? WHERE cId = ?");
				$updateCr->bind_param("si", $newCreator, $creatorId);
			}else{
				$updateCr = $mysqli->prepare("UPDATE r_Creator INNER JOIN r_Recipes ON r_Creator.cId = r_Recipes.rCreator SET cName = ?, cSource = ? WHERE cId = ?");
				$updateCr->bind_param("ssi", $newCreator, $newSource, $creatorId);
			}
			$updateCr->execute();
			$updateCr->close();
		}
		
		//get recipe's ingredients
		$getIngList = $mysqli->prepare("SELECT iName FROM r_Ingredients INNER JOIN r_RecipeIngredients ON iId = ri_iId INNER JOIN r_Recipes ON ri_rId=rId WHERE rId = ?");
		$getIngList->bind_param("i", $updateId);
		$getIngList->execute();
		$getIngList->bind_result($ingredient);
		$arIng = array();
		while($getIngList->fetch()){
			$arIng[] = $ingredient;
		}
		$getIngList->close();
		
		for($i = 0; $i < count($newQuan); $i++){
			$getIng = $mysqli->prepare("SELECT iId FROM r_Ingredients WHERE iName = ?");
			$getIng->bind_param("s", $arIng[$i]);
			$getIng->execute();
			$getIng->bind_result($ii);
			$getIng->fetch();
			$getIng->close();
			if(!empty($newQuan[$i]) && !empty($newUom[$i])){	
				if(!empty($newQuan[$i]) && empty($newUom[$i])){
					$RecIng = $mysqli->prepare("UPDATE r_RecipeIngredients SET ri_quantity = ? WHERE ri_rId = ? AND ri_iId = ?");
					$RecIng->bind_param("dii", $newQuan[$i], $updateId, $ii);
				} else if(empty($newQuan[$i]) && !empty($newUom[$i])){
					$RecIng = $mysqli->prepare("UPDATE r_RecipeIngredients SET ri_uom = ? WHERE ri_rId = ? AND ri_iId = ?");
					$RecIng->bind_param("sii", $newUom[$i], $updateId, $ii);
				}else{
					$RecIng = $mysqli->prepare("UPDATE r_RecipeIngredients SET ri_quantity = ?, ri_uom = ? WHERE ri_rId = ? AND ri_iId = ?");
					$RecIng->bind_param("dsii", $newQuan[$i], $newUom[$i], $updateId, $ii);
				}
				$RecIng->execute();
				$RecIng->close();
			}else{
				$delIng = $mysqli->prepare("DELETE FROM r_RecipeIngredients WHERE ri_rId = ? AND ri_iId = ?");
				$delIng->bind_param("ii", $updateId, $ii);
				$delIng->execute();
				$delIng->close();
			}
		}
		
		//add new ingredients to ingredients table; NEXT, add ingredient id, recipe id, quantity and uom to r_RecipeIngredients
		if(!empty($newIng)){
			foreach($newIng as $ing){
				if(!empty($ing)){
					$nIng = $mysqli->prepare("INSERT IGNORE INTO r_Ingredients (iName) VALUES (?)");
					$nIng->bind_param("s", $ing);
					$nIng->execute();
					$nIng->close();
				}
			}
		}
		
		if(!empty($addIng)){
			foreach($addIng as $i){
				if(!empty($i)){
					$aIng = $mysqli->prepare("INSERT IGNORE INTO r_Ingredients (iName) VALUES (?)");
					$aIng->bind_param("s", $i);
					$aIng->execute();
					$aIng->close();
				}
			}
		}
		
		for ($i = 0; $i < count($addIng); $i++){	
			if(!empty($addIng[$i])){
				$ingId = $mysqli->prepare("SELECT iId FROM r_Ingredients WHERE iName = ?");
				$ingId->bind_param("s", $addIng[$i]);
				$ingId->execute();
				$ingId->bind_result($iii);
				$ingId->fetch();
				$ingId->close();
		
				$newRecIng = $mysqli->prepare("INSERT INTO r_RecipeIngredients (ri_rId, ri_iId, ri_quantity, ri_uom) VALUES (?, ?, ?, ?)");
				$newRecIng->bind_param("iids", $updateId, $iii, $addQ[$i], $addU[$i]);
				$newRecIng->execute();
				$newRecIng->close();
			}
		}
		echo 'Recipe <b>' . $newName . '</b> has been updated';
		$recipeIdToEdit = $updateId;
	}else if(isset($_POST['editRecipe']) && $_POST['editRecipe'] != 'All'){
		$recipeIdToEdit = $_POST['editRecipe'];
	}else{
		$strMessage = "You did not select a recipe to edit.";
	}	
	//get recipe's ingredients
	$editIngredients = $mysqli->prepare("SELECT iName FROM r_Ingredients INNER JOIN r_RecipeIngredients ON iId = ri_iId INNER JOIN r_Recipes ON ri_rId=rId WHERE rId = ?");
	$editIngredients->bind_param("i", $recipeIdToEdit);
	$editIngredients->execute();
	$editIngredients->bind_result($ingredient);
	$arrIng = array();
	while($editIngredients->fetch()){
		$arrIng[] = $ingredient;
	}
	$editIngredients->close();
	
	//get creator name and source
	$creator = $mysqli->prepare("SELECT cName, cSource FROM r_Creator INNER JOIN r_Recipes ON rCreator = cId WHERE rId = ?");
	$creator->bind_param("i", $recipeIdToEdit);
	$creator->execute();
	$creator->bind_result($creatorName, $creatorSource);
	$creator->fetch();
	$creator->close();
		
	$viewCat = $mysqli->prepare("SELECT catName FROM r_Category INNER JOIN r_Recipes ON catId = rCatId WHERE rId = ?");
	$viewCat->bind_param("i", $recipeIdToEdit);
	$viewCat->execute();
	$viewCat->bind_result($catName); 
	$viewCat->fetch();
	$viewCat->close();
	
	//get recipe id, ingredient id, quantity, uom
	$ingDetails = $mysqli->prepare("SELECT ri_rId, ri_iId, ri_quantity, ri_uom FROM r_RecipeIngredients INNER JOIN r_Ingredients ON ri_iId = iId INNER JOIN r_Recipes ON ri_rId=rId WHERE rId = ?");
	$ingDetails->bind_param("i", $recipeIdToEdit);
	$ingDetails->execute();
	$ingDetails->bind_result($recId, $ingId, $quan, $uom);
	$arrQuan = array();
	$arrUom = array();
	while($ingDetails->fetch()){
		$arrQuan[] = $quan;
		$arrUom[] = $uom;
	}
	$ingDetails->close();
	
	//get recipe id, name, description, servings, time, instructions, creator id, user
	$editRecipe = $mysqli->prepare("SELECT * FROM r_Recipes WHERE rId = ?");
	$editRecipe->bind_param("i", $recipeIdToEdit);
	$editRecipe->execute();
	$editRecipe->bind_result($rId, $rName, $rDesc, $rServings, $rTime, $rInstructions, $rCreator, $rCatId, $rUsername);	
?>

	
<!DOCTYPE html>
<html>
	<head>
	<title>Edit Recipe</title>
	<link rel="stylesheet" type="text/css" href="Style.css">
	<script>
		//this function adds a row to the ingredients html table
		var count = 0;
		function addRow(in_tbl_name)
		{
			var tbody = document.getElementById(in_tbl_name).getElementsByTagName("TBODY")[0];
			// create row
			var row = document.createElement("tr");
			// create table cell 1
			var td1 = document.createElement("td");
			var temp1 = "addIngredients[" + count + "]";
			td1.innerHTML = "<input type='text' name='" + temp1 + "' size='30' maxlength='30'>";
			// create table cell 2
			var td2 = document.createElement("td");
			var temp2 = "addQuantities[" + count + "]"; 
			td2.innerHTML = "<input type='number' name='" + temp2 + "' step='0.01'>";
			// create table cell 3
			var td3 = document.createElement("td");
			var temp3 = "addUoms[" + count + "]";
			td3.innerHTML = "<input type='text' name='" + temp3 + "' size='15' maxlength='15'>";
			// append data to row
			row.appendChild(td1);
			row.appendChild(td2);
			row.appendChild(td3);
			// add to count variable
			count = parseInt(count) + 1;
			// append row to table
			tbody.appendChild(row);
		}
	</script>
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

	<h1>Edit Recipe</h1>
	<hr>
	<?php 
	if($strMessage !='')
		echo '<p align="center">' . $strMessage . '</p>';
	while ($editRecipe->fetch()){
		echo '<form method="POST" action="editRecipe.php"><table name="tblRecipe" class="display" width="70%">';
		echo '<tr><td><b>Name:</b></td><td><input type="text" name="updatedName" value="' . $rName . '" size="40" maxlength="40" required></td><td>&nbsp;</td></tr>';
		echo '<tr><td><b>Description:</b></td><td><input type="text" name="updatedDesc" value="' . $rDesc . '" size="70" maxlength="90"></td><td></td></tr>';
		echo '<tr><td><b>Time (minutes):</b></td><td><input type="number" name="updatedTime" value="' . $rTime . '" min="1" step="0.01"></td><td></td></tr>';
		echo '<tr><td><b>Servings:</b></td><td><input type="number" name="updatedServings" value="' . $rServings . '" min="1" step="0.01"></td><td></td></tr>';
		echo '<tr><td><b>Creator:</b></td><td><input type="text" name="updatedCreator" value="' . $creatorName . '" size="30" maxlength="30"></td><td></td></tr>';
		echo '<tr><td><b>Source:</b></td><td><input type="text" name="updatedSource" value="' . $creatorSource . '" size="50" maxlength="90"></td><td></td></tr>';
		echo '<tr><td><b>Category:</b></td><td><select name="updatedCategory">';
			for($i=0; $i<sizeof($arrCat['catId']); $i++){
				if($arrCat['catId'][$i] == $rCatId){
					echo '<option value="'.$arrCat['catId'][$i].'" selected="selected">'.$arrCat['catName'][$i].'</option>';
				}else{
					echo '<option value="'.$arrCat['catId'][$i].'">'.$arrCat['catName'][$i].'</option>';
				}
			}
		echo '</select></td><td></td></tr>';
		echo '<tr><td valign="top"><b>Instructions:</b></td><td><textarea name="updatedInstructions" value="' . $rInstructions . '" style="width:500px; height:150px;">'.$rInstructions.'</textarea></td><td></td></tr>';	
		echo '<table width="70%" id="editIngredients" class="display" width="70%">';
		echo '<tr><td><b>Ingredients:</b></td><td><b>Quantities:</b></td><td><b>UOM:</b></td></tr>';
			for($a=0; $a <count($arrIng); $a++){
				echo '<tr><td>' . $arrIng[$a] . '</td>
							<td><input type="number" name="updatedQuantities[]" value="' . $arrQuan[$a] . '" step="0.01"></td>
							<td><input type="text" name="updatedUoms[]" value="' . $arrUom[$a] . '" size="15" maxlength="15"></td>
							</tr>';
			} 
		echo '</table>';	
		?>
		<tr><td colspan="2"><input type="button" onClick="addRow('editIngredients')" value="+"></td></tr>
		<?php 
		echo '<br>';
		echo '</table>';
		echo '<p align="center"><button type="submit" name="updateRecipe" value="' . $rId . '">Update Recipe</button></p>'; 
		echo '</form>';
	}
	$editRecipe->close();
}
?>

</body>
</html>