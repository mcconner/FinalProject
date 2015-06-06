<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include 'secret.php';

//start or restart session
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

//make database connection 
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", $password, "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$bCat = 0;
$catList = $mysqli->query("SELECT catId, catName FROM r_Category ORDER BY catName");
if($catList){
	while($row = mysqli_fetch_assoc($catList)){
		$arrCat['catId'][] = $row['catId'];
		$arrCat['catName'][] = $row['catName'];
	}
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	if(isset($_POST['addCategory']) && !empty($_POST['addCategory'])){
		if(isset($_POST['newCategory']) && !empty($_POST['newCategory'])){
			$newCat = $_POST['newCategory'];		
		
			for($j=0; $j<sizeof($arrCat['catName']); $j++){
				if(strtolower($arrCat['catName'][$j]) == strtolower($newCat)){
					$bCat = 1;
					echo '<p class="errorMsg">This is already a category.</p>';
				}
			}
		}else{
			$bCat = 1;
			echo '<p class="errorMsg">You did not enter a category.</p>';
		}
		
		if($bCat != 1){
			//add to category table
			$addNewCat = $mysqli->prepare("INSERT INTO r_Category SET catName = ?");
			$addNewCat->bind_param("s", $newCat);
			$addNewCat->execute();
			$addNewCat->close();
			echo "Category <b>" . $newCat . "</b> has been successfully added!";
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Add Category</title>
	<script type="text/javascript" src="jquery-1.2.6.min.js"></script>
	<link rel="stylesheet" type="text/css" href="Style.css">
	<script type="text/javascript">
	//this function checks the database to see if the category exists
		$(document).ready(function(){
		 
			$("#newCategory").change(function() { 
			 
			var newCat = $("#newCategory").val();
			if(newCat.length >= 1){
				$("#status").html('&nbsp;Checking availability...');
			 
				$.ajax({  
				type: "POST",  
				url: "check.php",  
				data: "newCategory="+ newCat,  
				success: function(msg){  
					$("#status").ajaxComplete(function(event, request, settings){ 
						if(msg == 'OK'){ 
							$("#newCategory").removeClass('object_error');
							$("#newCategory").addClass("object_ok");
							$(this).html('&nbsp;This is a new category!');
							$('input[name=addCategory]').attr('disabled', false);
						}  
						else{  
							$("#newCategory").removeClass('object_ok');
							$("#newCategory").addClass("object_error");
							$(this).html(msg);
							$('input[name=addCategory]').attr('disabled', true);  //disable submit button if name is taken
						}  
				   });
				}     
			  }); 
			  	}
				else{
					$("#status").html('<font color="red">' +
				'The new category cannot be blank.</font>');
					$("#newCategory").removeClass('object_ok');
					$("#newCategory").addClass("object_error");
					}
			});
		});	
</script>
</head>
<body>
<!--http://html.cita.illinois.edu/nav/menu/menu-example-hrzt.php -->
<!--show navigation bar menu -->
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


<h1>Add a Category</h1>
<hr>
<div>
	<form method="POST" action="addCategory.php">
	<table class="display" width="70%">
		<tr>
			<td align="center">Your Current Categories:<br>
			
			<?php 
			echo '<form style="text-align: center" method="POST" action="addCategory.php" name="addCategory" value="-SELECT-">';
			echo '<select name="addCategory">';
			for($i=0; $i<sizeof($arrCat['catName']); $i++){
				echo "<option>" . $arrCat['catName'][$i] . "</option>";
			}
			echo '&nbsp;</select>';
			echo '</form>';
			echo '<br>';
			?>
			</td>
			<td align="center">New Category: <br><input type="text" id="newCategory" name="newCategory"></td>
			<td width="30%"><div id="status"></div></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input type="submit" name="addCategory" value="Add Category"></td>
			<td width="30%"><div id="status"></div></td>
		</tr>
	</table>
	</form>
</div>

</body>
</html>