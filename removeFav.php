<?php
require_once 'DBconfig.php';

// لازم المستخدم يكون مسجل دخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];

// التحقق من وجود recipeID
if (!isset($_GET['recipeID'])) {
    header("Location: user.php");
    exit();
}

$recipeID = (int)$_GET['recipeID'];

// حذف من المفضلة
$stmt = $pdo->prepare("DELETE FROM favourites WHERE userID=? AND recipeID=?");
$stmt->execute([$userID, $recipeID]);

// رجوع لنفس الصفحة
header("Location: user.php");
exit();
