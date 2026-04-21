<?php
require_once 'DBconfig.php';

// تحقق تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$recipeID = $_POST['recipeID'] ?? 0;
$comment = trim($_POST['comment'] ?? '');

// تحقق من البيانات
if ($recipeID == 0 || empty($comment)) {
    header("Location: ViewRecipe.php?id=" . $recipeID);
    exit();
}

// إدخال الكومنت
$stmt = $pdo->prepare("
    INSERT INTO comment (recipeID, userID, comment, date)
    VALUES (?, ?, ?, NOW())
");

$stmt->execute([$recipeID, $userID, $comment]);

// رجوع لنفس الصفحة
header("Location: view-recipe.php?id=" . $recipeID);
exit();
?>