<?php
require_once 'DBconfig.php';
requireLogin();
 
$recipeID = $_GET['id'] ?? 0;
 
if (!$recipeID) {
    header("Location: My-recipes.php");
    exit();
}
 
// Fetch recipe to get file paths and verify ownership
$stmt = $pdo->prepare("SELECT * FROM recipe WHERE id = ?");
$stmt->execute([$recipeID]);
$recipe = $stmt->fetch();
 
// If recipe doesn't exist, go back
if (!$recipe) {
    header("Location: My-recipes.php");
    exit();
}
 
// Only the recipe owner can delete it
if ($recipe['userID'] != $_SESSION['user_id']) {
    header("Location: My-recipes.php");
    exit();
}
 
// Delete photo file from server
if (!empty($recipe['photoFileName'])) {
    $photoPath = "images/" . $recipe['photoFileName'];
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }
}
 
// Delete video file from server
if (!empty($recipe['videoFilePath'])) {
    $videoPath = "videos/" . $recipe['videoFilePath'];
    if (file_exists($videoPath)) {
        unlink($videoPath);
    }
}
 
// Delete the recipe using CASCADE handles ingredients, instructions,
// comments, likes, favourites, and reports automatically
$pdo->prepare("DELETE FROM recipe WHERE id = ?")->execute([$recipeID]);
 
header("Location: My-recipes.php");
exit();
?>