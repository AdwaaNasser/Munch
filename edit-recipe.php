<?php
require_once 'DBconfig.php';

$id = $_GET['id'] ?? 0;

// جلب البيانات
$stmt = $pdo->prepare("SELECT * FROM recipe WHERE id=?");
$stmt->execute([$id]);
$recipe = $stmt->fetch();

// ingredients
$stmt = $pdo->prepare("SELECT * FROM ingredients WHERE recipeID=?");
$stmt->execute([$id]);
$ingredients = $stmt->fetchAll();

// instructions
$stmt = $pdo->prepare("SELECT * FROM instructions WHERE recipeID=? ORDER BY stepOrder");
$stmt->execute([$id]);
$steps = $stmt->fetchAll();

// categories
$categories = $pdo->query("SELECT * FROM recipecategory")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Recipe</title>
<link rel="stylesheet" href="style.css">
</head>

<body class="edit-page">

<div class="form-card">
<h1>Edit Recipe</h1>

<form action="EditRecipeProcess.php" method="POST" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?= $recipe['id'] ?>">

<!-- Name -->
<div class="form-group">
<label>Name *</label>
<input type="text" name="name" value="<?= $recipe['name'] ?>" required>
</div>

<!-- Category -->
<div class="form-group">
<label>Category *</label>
<select name="category" required>
<?php foreach($categories as $cat): ?>
<option value="<?= $cat['id'] ?>" 
<?= $cat['id']==$recipe['categoryID'] ? 'selected' : '' ?>>
<?= $cat['categoryName'] ?>
</option>
<?php endforeach; ?>
</select>
</div>

<!-- Description -->
<div class="form-group">
<label>Description *</label>
<textarea name="description" required><?= $recipe['description'] ?></textarea>
</div>

<!-- Current Image -->
<div class="form-group">
<label>Current Photo</label><br>
<img src="images/<?= $recipe['photoFileName'] ?>" width="100">
</div>

<!-- Upload new -->
<div class="form-group">
<label>Upload New Photo (optional)</label>
<input type="file" name="photo">
</div>

<!-- INGREDIENTS -->
<h2 style="color:#9C0300;">Ingredients *</h2>

<div id="ingredients">

<?php foreach($ingredients as $ing): ?>
<div class="ingredient-row">
<input type="text" name="ingredient_name[]" value="<?= $ing['IngredientName'] ?>" required>
<input type="text" name="ingredient_qty[]" value="<?= $ing['IngredientQuantity'] ?>" required>
<button type="button" class="mini-delete" onclick="removeRow(this)">✕</button>
</div>
<?php endforeach; ?>

</div>

<button type="button" class="action-btn" onclick="addIngredient()">+ Add another ingredient</button>


<!-- INSTRUCTIONS -->
<h2 style="color:#9C0300;">Instructions *</h2>

<div id="instructions">

<?php foreach($steps as $step): ?>
<div class="instruction-row">
<input type="text" name="steps[]" value="<?= $step['step'] ?>" required>
<button type="button" class="mini-delete" onclick="removeRow(this)">✕</button>
</div>
<?php endforeach; ?>

</div>

<button type="button" class="action-btn" onclick="addStep()">+ Add another step</button>

<!-- Video -->
<div class="form-group">
<label>Video URL</label>
<input type="text" name="video" value="<?= $recipe['videoFilePath'] ?>">
</div>

<button type="submit">Save Changes</button>

</form>
</div>

<script>
// حذف صف
function removeRow(btn){
    btn.parentElement.remove();
}

// إضافة مكون
function addIngredient() {
    const div = document.createElement("div");
    div.className = "ingredient-row";

    div.innerHTML = `
        <input type="text" name="ingredient_name[]" placeholder="Ingredient Name" required>
        <input type="text" name="ingredient_qty[]" placeholder="Quantity" required>
        <button type="button" class="mini-delete" onclick="removeRow(this)">✕</button>
    `;

    document.getElementById("ingredients").appendChild(div);
}

// إضافة خطوة
let stepCount = <?= count($steps) ?>;

function addStep() {
    stepCount++;

    const div = document.createElement("div");
    div.className = "instruction-row";

    div.innerHTML = `
        <input type="text" name="steps[]" placeholder="Step ${stepCount}" required>
        <button type="button" class="mini-delete" onclick="removeRow(this)">✕</button>
    `;

    document.getElementById("instructions").appendChild(div);
}
</script>

</body>
</html>
