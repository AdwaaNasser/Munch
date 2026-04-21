<?php
require_once 'DBconfig.php';

session_start();
$userID = $_SESSION['user_id'] ?? 1;

// =======================
// ADD RECIPE
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $video = $_POST['video'];

    $ingredient_names = $_POST['ingredient_name'];
    $ingredient_qtys = $_POST['ingredient_qty'];
    $steps = $_POST['steps'];

    // رفع الصورة
    $photoName = $_FILES['photo']['name'];
    $tmp = $_FILES['photo']['tmp_name'];
    move_uploaded_file($tmp, "images/" . $photoName);

    // =======================
    // إدخال recipe
    // =======================
    $sql = "INSERT INTO recipe (userID, categoryID, name, description, photoFileName, videoFilePath)
            VALUES (?,?,?,?,?,?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userID, $category, $name, $description, $photoName, $video]);

    $recipeID = $pdo->lastInsertId();

    // =======================
    // ingredients
    // =======================
    for($i=0; $i<count($ingredient_names); $i++){
        $stmt = $pdo->prepare("INSERT INTO ingredients (recipeID, IngredientName, IngredientQuantity) VALUES (?,?,?)");
        $stmt->execute([$recipeID, $ingredient_names[$i], $ingredient_qtys[$i]]);
    }

    // =======================
    // instructions
    // =======================
    for($i=0; $i<count($steps); $i++){
        $stmt = $pdo->prepare("INSERT INTO instructions (recipeID, step, stepOrder) VALUES (?,?,?)");
        $stmt->execute([$recipeID, $steps[$i], $i+1]);
    }

    // رجوع
    header("Location: My-recipes.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Recipe</title>
<link rel="stylesheet" href="style.css">
</head>

<body class="edit-page">

<div class="form-card">
<h1>Add New Recipe</h1>

<form method="POST" enctype="multipart/form-data">

<!-- Name -->
<div class="form-group">
<label>Name *</label>
<input type="text" name="name" required>
</div>

<!-- Category -->
<div class="form-group">
<label>Category *</label>
<select name="category" required>
<option value="">Select</option>
<option value="3">Healthy Bakery</option>
<option value="2">Gluten Free</option>
<option value="1">Sugar Free</option>
</select>
</div>

<!-- Description -->
<div class="form-group">
<label>Description *</label>
<textarea name="description" required></textarea>
</div>

<!-- Photo -->
<div class="form-group">
<label>Upload Recipe Photo *</label>
<input type="file" name="photo" required>
</div>

<!-- Ingredients -->
<h2 style="color:#9C0300;">Ingredients *</h2>

<div id="ingredients">
<div class="ingredient-row">
<input type="text" name="ingredient_name[]" placeholder="Ingredient Name" required>
<input type="text" name="ingredient_qty[]" placeholder="Quantity" required>
</div>
</div>

<button type="button" class="action-btn" onclick="addIngredient()">
+ Add another ingredient
</button>

<!-- Instructions -->
<h2 style="color:#9C0300;">Instructions *</h2>

<div id="instructions">
<div class="instruction-row">
<input type="text" name="steps[]" placeholder="Step 1" required>
</div>
</div>

<button type="button" class="action-btn" onclick="addStep()">
+ Add another step
</button>

<!-- Video -->
<div class="form-group">
<label>Video URL</label>
<input type="text" name="video">
</div>

<button type="submit">Add Recipe</button>

</form>
</div>

<script>
function addIngredient() {
    const div = document.createElement("div");
    div.className = "ingredient-row";

    div.innerHTML = `
        <input type="text" name="ingredient_name[]" placeholder="Ingredient Name" required>
        <input type="text" name="ingredient_qty[]" placeholder="Quantity" required>
        <button type="button" onclick="this.parentElement.remove()">✕</button>
    `;

    document.getElementById("ingredients").appendChild(div);
}

let stepCount = 1;

function addStep() {
    stepCount++;

    const div = document.createElement("div");
    div.className = "instruction-row";

    div.innerHTML = `
        <input type="text" name="steps[]" placeholder="Step ${stepCount}" required>
        <button type="button" onclick="this.parentElement.remove()">✕</button>
    `;

    document.getElementById("instructions").appendChild(div);
}
</script>

</body>
</html>