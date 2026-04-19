<?php
require_once 'DBconfig.php';

$id = $_GET['id'] ?? $_POST['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'];
    $description = $_POST['description'];
    $video = $_POST['video'];

    $ingredient_names = $_POST['ingredient_name'];
    $ingredient_qtys = $_POST['ingredient_qty'];
    $steps = $_POST['steps'];

    $sql = "UPDATE recipe SET name=?, description=?, videoFilePath=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $description, $video, $id]);

    $pdo->prepare("DELETE FROM ingredients WHERE recipeID=?")->execute([$id]);

    for($i=0; $i<count($ingredient_names); $i++){
        $stmt = $pdo->prepare("INSERT INTO ingredients (recipeID, IngredientName, IngredientQuantity) VALUES (?,?,?)");
        $stmt->execute([$id, $ingredient_names[$i], $ingredient_qtys[$i]]);
    }

    $pdo->prepare("DELETE FROM instructions WHERE recipeID=?")->execute([$id]);

    for($i=0; $i<count($steps); $i++){
        $stmt = $pdo->prepare("INSERT INTO instructions (recipeID, step, stepOrder) VALUES (?,?,?)");
        $stmt->execute([$id, $steps[$i], $i+1]);
    }

    // back
    header("Location: My-recipes.php");
    exit();
}

// recipe
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

<form method="POST">

<input type="hidden" name="id" value="<?= $recipe['id'] ?>">

<!-- Name -->
<label>Recipe Name</label>
<input type="text" name="name" value="<?= $recipe['name'] ?>" required>

<!-- Description -->
<label>Description</label>
<textarea name="description" required><?= $recipe['description'] ?></textarea>

<!-- Ingredients -->
<h3>Ingredients</h3>

<div id="ingredients">

<?php foreach($ingredients as $index => $ing): ?>
<div class="ingredient-row">
  <input type="text" name="ingredient_name[]" value="<?= $ing['IngredientName'] ?>" required>
  <input type="text" name="ingredient_qty[]" value="<?= $ing['IngredientQuantity'] ?>" required>

  <?php if($index != 0): ?>
    <button type="button" class="mini-delete" onclick="removeIngredient(this)">✕</button>
  <?php endif; ?>
</div>
<?php endforeach; ?>

</div>

<button type="button" class="action-btn" onclick="addIngredient()">
  + Add Ingredient
</button>

<!-- Instructions -->
<h3>Instructions</h3>

<div id="instructions">

<?php foreach($steps as $index => $step): ?>
<div class="instruction-row">
  <input type="text" name="steps[]" value="<?= $step['step'] ?>" required>

  <?php if($index != 0): ?>
    <button type="button" class="mini-delete" onclick="removeStep(this)">✕</button>
  <?php endif; ?>
</div>
<?php endforeach; ?>

</div>

<button type="button" class="action-btn" onclick="addStep()">
  + Add Step
</button>
<!-- Video -->
<label>Video Path</label>
<input type="text" name="video" value="<?= $recipe['videoFilePath'] ?>">

<button type="submit">Save Changes</button>

</form>

</div>

<script>
function addIngredient() {

    const div = document.createElement("div");
    div.className = "ingredient-row";

    div.innerHTML = `
        <input type="text" name="ingredient_name[]" placeholder="Ingredient Name" required>
        <input type="text" name="ingredient_qty[]" placeholder="Quantity" required>
        <button type="button" class="mini-delete" onclick="removeIngredient(this)">✕</button>
    `;

    document.getElementById("ingredients").appendChild(div);
}

function removeIngredient(button) {
    button.parentElement.remove();
}


// ===== Instructions =====

let stepCount = <?= count($steps) ?>;

function addStep() {
    stepCount++;

    const div = document.createElement("div");
    div.className = "instruction-row";

    div.innerHTML = `
        <input type="text" name="steps[]" placeholder="Step ${stepCount}" required>
        <button type="button" class="mini-delete" onclick="removeStep(this)">✕</button>
    `;

    document.getElementById("instructions").appendChild(div);
}

function removeStep(button) {
    button.parentElement.remove();
}
</script>


</body>
</html>