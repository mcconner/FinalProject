<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');

session_start();

//end the session and log the user out 
$_SESSION = array();
session_destroy();
$filePath = explode('/', $_SERVER['PHP_SELF'], -1);
$filePath = implode('/', $filePath);
$redirect = "//" . $_SERVER['HTTP_HOST'] . $filePath;
header("location: {$redirect}/RecipeMainPage.php", true);
die();
?>