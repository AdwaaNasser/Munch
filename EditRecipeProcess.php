<?php
require_once 'DBconfig.php';

$id = $_POST['id'];

$name = $_POST['name'];
$category = $_POST['category'];
$description = $_POST['description'];
$video = $_POST['video'];

$ingredient_names = $_POST['ingredient_name'];
$ingredient_qty = $_POST['ingredient_qty'];
$steps = $_POST['steps'];

// جلب القديم
$stmt = $pdo->prepare("SELECT photoFileName FROM recipe WHERE id=?");
$stmt->execute([$id]);
$old = $stmt->fetch();

$photo = $old['photoFileName'];

// رفع صورة جديدة
if (!empty($_FILES['photo']['name'])) {

    if (!empty($photo) && file_exists("images/".$photo)) {
        unlink("images/".$photo);
    }

    $photo = time() . "_" . $_FILES['photo']['name'];
    move_uploaded_file($_FILES['photo']['tmp_name'], "images/".$photo);
}

// تحديث recipe
$stmt = $pdo->prepare("
UPDATE recipe 
SET name=?, categoryID=?, description=?, photoFileName=?, videoFilePath=?
WHERE id=?
");
$stmt->execute([$name,$category,$description,$photo,$video,$id]);

// حذف القديم
$pdo->prepare("DELETE FROM ingredients WHERE recipeID=?")->execute([$id]);
$pdo->prepare("DELETE FROM instructions WHERE recipeID=?")->execute([$id]);

// إعادة الإدخال
for($i=0;$i<count($ingredient_names);$i++){
    if(!empty($ingredient_names[$i])){
        $stmt=$pdo->prepare("INSERT INTO ingredients VALUES(NULL,?,?,?)");
        $stmt->execute([$id,$ingredient_names[$i],$ingredient_qty[$i]]);
    }
}

for($i=0;$i<count($steps);$i++){
    if(!empty($steps[$i])){
        $stmt=$pdo->prepare("INSERT INTO instructions VALUES(NULL,?,?,?)");
        $stmt->execute([$id,$steps[$i],$i+1]);
    }
}

// رجوع
header("Location: My-recipes.php");
exit();