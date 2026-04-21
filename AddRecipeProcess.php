<?php
require_once 'DBconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];

$name = $_POST['name'];
$category = $_POST['category'];
$description = $_POST['description'];
$video = $_POST['video'];

$ingredient_names = $_POST['ingredient_name'];
$ingredient_qty = $_POST['ingredient_qty'];
$steps = $_POST['steps'];

// رفع صورة
$fileName = "";
if (!empty($_FILES['photo']['name'])) {
    $fileName = time() . "_" . $_FILES['photo']['name'];
    move_uploaded_file($_FILES['photo']['tmp_name'], "images/" . $fileName);
}

// إدخال recipe
$stmt = $pdo->prepare("
INSERT INTO recipe (userID, categoryID, name, description, photoFileName, videoFilePath)
VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$userID, $category, $name, $description, $fileName, $video]);

$recipeID = $pdo->lastInsertId();

// ingredients
for ($i=0; $i<count($ingredient_names); $i++) {
    if (!empty($ingredient_names[$i])) {
        $stmt = $pdo->prepare("INSERT INTO ingredients VALUES(NULL,?,?,?)");
        $stmt->execute([$recipeID,$ingredient_names[$i],$ingredient_qty[$i]]);
    }
}

// steps
for ($i=0; $i<count($steps); $i++) {
    if (!empty($steps[$i])) {
        $stmt = $pdo->prepare("INSERT INTO instructions VALUES(NULL,?,?,?)");
        $stmt->execute([$recipeID,$steps[$i],$i+1]);
    }
}

header("Location: My-recipes.php");
exit();