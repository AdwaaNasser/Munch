<?php

require_once 'DBconfig.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please log in to access this page.";
    header("Location: login.php");
    exit();
}

if ($_SESSION['user_type'] !== 'user') {
    $_SESSION['error_message'] = "Access denied. This page is for regular users only.";
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];

// Retrieve user information 
$stmt = executeQuery($pdo, "SELECT * FROM User WHERE id = ?", [$userID]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

//  Count user's total recipes and total likes 
$stmtRecipeCount = executeQuery(
    $pdo,
    "SELECT COUNT(*) AS totalRecipes FROM Recipe WHERE userID = ?",
    [$userID]
);
$recipeCount = $stmtRecipeCount->fetch()['totalRecipes'];

$stmtLikesCount = executeQuery(
    $pdo,
    "SELECT COUNT(*) AS totalLikes 
     FROM Likes l 
     JOIN Recipe r ON l.recipeID = r.id 
     WHERE r.userID = ?",
    [$userID]
);
$likesCount = $stmtLikesCount->fetch()['totalLikes'];

//6d Retrieve categories for filter dropdown 
$stmtCategories = executeQuery($pdo, "SELECT * FROM RecipeCategory");
$categories = $stmtCategories->fetchAll();

//6e Retrieve recipes (all or filtered by category) 
$recipes = [];
$noRecipesMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST: filtered by category
    $selectedCategoryID = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

    if ($selectedCategoryID > 0) {
        $stmtRecipes = executeQuery(
            $pdo,
            "SELECT r.*, u.firstName, u.lastName, u.photoFileName AS userPhoto,
                    rc.categoryName,
                    (SELECT COUNT(*) FROM Likes l WHERE l.recipeID = r.id) AS likesCount
             FROM Recipe r
             JOIN User u ON r.userID = u.id
             JOIN RecipeCategory rc ON r.categoryID = rc.id
             WHERE r.categoryID = ?",
            [$selectedCategoryID]
        );
    } else {
        // "All Categories" selected
        $stmtRecipes = executeQuery(
            $pdo,
            "SELECT r.*, u.firstName, u.lastName, u.photoFileName AS userPhoto,
                    rc.categoryName,
                    (SELECT COUNT(*) FROM Likes l WHERE l.recipeID = r.id) AS likesCount
             FROM Recipe r
             JOIN User u ON r.userID = u.id
             JOIN RecipeCategory rc ON r.categoryID = rc.id"
        );
    }

    $recipes = $stmtRecipes->fetchAll();
    if (empty($recipes)) {
        $noRecipesMessage = "No recipes found in this category.";
    }

} else {
    // GET: all recipes
    $stmtRecipes = executeQuery(
        $pdo,
        "SELECT r.*, u.firstName, u.lastName, u.photoFileName AS userPhoto,
                rc.categoryName,
                (SELECT COUNT(*) FROM Likes l WHERE l.recipeID = r.id) AS likesCount
         FROM Recipe r
         JOIN User u ON r.userID = u.id
         JOIN RecipeCategory rc ON r.categoryID = rc.id"
    );
    $recipes = $stmtRecipes->fetchAll();
}

//6f Retrieve user's favourite recipes 
$stmtFavourites = executeQuery(
    $pdo,
    "SELECT r.id, r.name, r.photoFileName
     FROM Favourites f
     JOIN Recipe r ON f.recipeID = r.id
     WHERE f.userID = ?",
    [$userID]
);
$favourites = $stmtFavourites->fetchAll();

// Profile photo path
$profilePhoto = !empty($user['userPhoto'])
    ? 'images/' . htmlspecialchars($user['userPhoto'])
    : 'profile.png';
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile - Bakery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="home-page">
<div class="page My-recipes-card" style="height: auto; padding: 40px; overflow-y: auto;">

  
    <div class="page-header">
        <h1>Welcome, <?= htmlspecialchars($user['firstName']) ?></h1>
        <a href="signout.php" class="text-link">Log-out</a>
    </div>

   
    <div class="info-box">
        <div class="info-layout">
            <div>
                <h3>My Information</h3>
                <p><strong>Name:</strong>
                    <?= htmlspecialchars($user['firstName']) ?>
                    <?= htmlspecialchars($user['lastName']) ?>
                </p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['emailAddress']) ?></p>
            </div>
            <div class="photo-frame">
                <img src="images/<?= $profilePhoto ?>" alt="Profile">
            </div>
        </div>
    </div>

    <!-- ── 6c. My Recipes Summary ── -->
    <div class="info-box">
        <h3>My Recipes</h3>
        <p><strong>Total Recipes:</strong> <?= (int)$recipeCount ?></p>
        <p><strong>Total Likes:</strong> <?= (int)$likesCount ?></p>
        <br>
        <a href="My-recipes.php" class="text-link">Go to My Recipes &rarr;</a>
    </div>

    <!-- ── 6d. Filter by Category ── -->
    <div class="page-header">
        <h2>All Available Recipes</h2>
        <div>
            <form method="POST" action="user.php" style="display:inline-flex; gap:8px; align-items:center;">
                <select class="filter-select" name="category_id">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"
                            <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['categoryName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="addRecipe-link filter-btn">Filter</button>
            </form>
        </div>
    </div>

    <!-- ── 6e. Recipes Table ── -->
    <?php if (!empty($noRecipesMessage)): ?>
        <p><?= htmlspecialchars($noRecipesMessage) ?></p>

    <?php elseif (!empty($recipes)): ?>
        <table class="recipes-table">
            <thead>
                <tr>
                    <th>Recipe Name</th>
                    <th>Recipe Photo</th>
                    <th>Recipe Creator</th>
                    <th>Number of Likes</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recipes as $recipe): ?>
                <tr>
                    <td class="recipe-cell">
                        <a href="view-recipe.php?id=<?= (int)$recipe['id'] ?>">
                            <?= htmlspecialchars($recipe['name']) ?>
                        </a>
                    </td>
                    <td class="recipe-cell">
                        <img src="images/<?= htmlspecialchars($recipe['photoFileName']) ?>"
                             alt="<?= htmlspecialchars($recipe['name']) ?>">
                    </td>
                    <td class="recipe-cell">
                        <?= htmlspecialchars($recipe['firstName']) ?>
                        <?= htmlspecialchars($recipe['lastName']) ?>
                        <br>
                        <img src="images/<?= htmlspecialchars($recipe['userPhoto']) ?>"
                             alt="Chef" width="40" class="creator-img">
                    </td>
                    <td><?= (int)$recipe['likesCount'] ?></td>
                    <td><?= htmlspecialchars($recipe['categoryName']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <p>No recipes available yet.</p>
    <?php endif; ?>

    <!-- ── 6f. Favourite Recipes ── -->
    <br>
    <h2 class="section-title">My Favourite Recipes &#10084;</h2>


    <?php if (!empty($favourites)): ?>
        <table class="recipes-table">
            <thead>
                <tr>
                    <th>Recipe Name</th>
                    <th>Recipe Photo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($favourites as $fav): ?>
                <tr>
                    <td class="recipe-cell">
                        <a href="view-recipe.php?id=<?= (int)$fav['id'] ?>">
                            <?= htmlspecialchars($fav['name']) ?>
                        </a>
                    </td>
                    <td class="recipe-cell">
                        <img src="images/<?= htmlspecialchars($fav['photoFileName']) ?>"
                             alt="<?= htmlspecialchars($fav['name']) ?>">
                    </td>
                    <td>
                        <a href="removeFav.php?recipeID=<?= (int)$fav['id'] ?>"
                           class="text-link">Remove</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <p>You have no favourite recipes yet.</p>
    <?php endif; ?>

</div><!-- end .page -->

<!-- ── Footer ── -->
<footer class="site-footer">
    <div class="container footer-box">
        <div class="footer-grid">
            <div class="footer-col">
                <h4>Find us</h4>
                <ul class="social">
                    <li><a href="#" aria-label="X">X</a></li>
                    <li><a href="#" aria-label="Facebook">f</a></li>
                    <li><a href="#" aria-label="LinkedIn">in</a></li>
                </ul>
            </div>
            <div class="footer-col center">
                <div class="brand">
                    <img src="images/Bakery1.png" alt="Bakery logo" style="width:120px;">
                </div>
                <small>©️2026 Munch. All rights reserved</small>
            </div>
            <div class="footer-col right">
                <h4>contact Info</h4>
                <p>+966444282741</p>
                <p><a href="mailto:Munch@gmail.com">Munch@gmail.com</a></p>
            </div>
        </div>
    </div>
</footer>

</body>
</html>