<?php
require_once 'DBconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// جلب الكاتيجوري
$categories = $pdo->query("SELECT * FROM recipecategory")->fetchAll();
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

    <form action="AddRecipeProcess.php" method="POST" enctype="multipart/form-data">

        <!-- Name -->
        <div class="form-group">
            <label>Name <span class="required">*</span></label>
            <input type="text" name="name" required>
        </div>

        <!-- Category -->
        <div class="form-group">
            <label>Category <span class="required">*</span></label>
            <select name="category" required>
                <option value="">Select</option>

                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>">
                        <?= $cat['categoryName'] ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label>Description <span class="required">*</span></label>
            <textarea name="description" required></textarea>
        </div>

        <!-- Photo -->
        <div class="form-group">
            <label>Upload Recipe Photo <span class="required">*</span></label>
            <input type="file" name="photo" accept="image/*" required>
        </div>

        <!-- Ingredients -->
        <h2 style="color:#9C0300;">Ingredients *</h2>

        <div id="ingredients">
            <div class="ingredient-row">
                <input type="text" name="ingredient_name[]" placeholder="Ingredient Name" required>
                <input type="text" name="ingredient_qty[]" placeholder="Quantity" required>
            </div>
        </div>

        <button type="button" class="action-btn" onclick="addIngredient()">+ Add another ingredient</button>

        <!-- Instructions -->
        <h2 style="color:#9C0300;">Instructions *</h2>

        <div id="instructions">
            <div class="instruction-row">
                <input type="text" name="steps[]" placeholder="Step 1" required>
            </div>
        </div>

        <button type="button" class="action-btn" onclick="addStep()">+ Add another step</button>

        <!-- Video -->
        <div class="form-group" style="margin-top:20px;">
            <label>Video URL (Optional)</label>
            <input type="text" name="video">
        </div>

        <!-- Submit -->
        <button type="submit">Add Recipe</button>

    </form>
</div>

<script>
// نفس كودك بدون تغيير
function addIngredient() {
    const div = document.createElement("div");
    div.className = "ingredient-row";

    div.innerHTML = `
        <input type="text" name="ingredient_name[]" placeholder="Ingredient Name" required>
        <input type="text" name="ingredient_qty[]" placeholder="Quantity" required>
        <button type="button" class="mini-delete" onclick="this.parentElement.remove()">✕</button>
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
        <button type="button" class="mini-delete" onclick="this.parentElement.remove()">✕</button>
    `;

    document.getElementById("instructions").appendChild(div);
}
</script>

</body>
</html>
