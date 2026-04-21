<?php
require_once 'DBconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: My-recipes.php");
    exit();
}

$userID = $_SESSION['user_id'];
$recipeID = $_POST['recipeID'];

// check existing
$stmt = $pdo->prepare("SELECT * FROM favourites WHERE userID=? AND recipeID=?");
$stmt->execute([$userID, $recipeID]);

if ($stmt->rowCount() == 0) {
    $stmt = $pdo->prepare("INSERT INTO favourites (userID, recipeID) VALUES (?,?)");
    $stmt->execute([$userID, $recipeID]);
}

header("Location: view-recipe.php?id=" . $recipeID);
exit();
?>