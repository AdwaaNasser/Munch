<?php
require_once 'DBconfig.php';

// لازم يكون فيه ID
if (!isset($_GET['id'])) {
    die("Invalid Recipe ID");
}

$recipeID = $_GET['id'];
$userID = $_SESSION['user_id'] ?? null;
$userType = $_SESSION['user_type'] ?? 'user';

// =========================
// 1. جلب بيانات الوصفة + صاحبها
// =========================
$sql = "SELECT r.*, u.firstName, u.lastName, u.photoFileName,
               c.categoryName
        FROM recipe r
        JOIN user u ON r.userID = u.id
        JOIN recipecategory c ON r.categoryID = c.id
        WHERE r.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$recipeID]);
$recipe = $stmt->fetch();

if (!$recipe) {
    die("Recipe not found");
}

// =========================
// 2. Ingredients
// =========================
$stmt = $pdo->prepare("SELECT * FROM ingredients WHERE recipeID=?");
$stmt->execute([$recipeID]);
$ingredients = $stmt->fetchAll();

// =========================
// 3. Instructions
// =========================
$stmt = $pdo->prepare("SELECT * FROM instructions WHERE recipeID=? ORDER BY stepOrder");
$stmt->execute([$recipeID]);
$steps = $stmt->fetchAll();

// =========================
// 4. Comments
// =========================
$stmt = $pdo->prepare("
    SELECT c.*, u.firstName 
    FROM comment c 
    JOIN user u ON c.userID = u.id 
    WHERE recipeID=? ORDER BY date DESC
");
$stmt->execute([$recipeID]);
$comments = $stmt->fetchAll();

// =========================
// 5. Checks (favorites / like / report)
// =========================
$isOwner = ($userID == $recipe['userID']);
$isAdmin = ($userType == 'admin');

// favourite
$stmt = $pdo->prepare("SELECT * FROM favourites WHERE userID=? AND recipeID=?");
$stmt->execute([$userID, $recipeID]);
$isFav = $stmt->rowCount() > 0;

// like
$stmt = $pdo->prepare("SELECT * FROM likes WHERE userID=? AND recipeID=?");
$stmt->execute([$userID, $recipeID]);
$isLiked = $stmt->rowCount() > 0;

// report
$stmt = $pdo->prepare("SELECT * FROM report WHERE userID=? AND recipeID=?");
$stmt->execute([$userID, $recipeID]);
$isReported = $stmt->rowCount() > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $recipe['name'] ?></title>
<link rel="stylesheet" href="style.css">
</head>

<body class="view-page">

<div class="recipe-card">

<h1><?= $recipe['name'] ?></h1>

<img src="images/<?= $recipe['photoFileName'] ?>" class="recipe-img">

<!-- Creator -->
<div class="creator-actions">

  <div class="creator">
    <img src="images/profile/<?= $recipe['photoFileName'] ?>">
    <div>
      <strong><?= $recipe['firstName'] . " " . $recipe['lastName'] ?></strong>
      <small><?= $recipe['categoryName'] ?></small>
    </div>
  </div>

  <!-- ===================== -->
  <!-- Buttons (شرط مهم) -->
  <!-- ===================== -->
  <?php if (!$isOwner && !$isAdmin): ?>
  <div class="recipe-actions">

    <!-- Favourite -->
    <form action="favProcess.php" method="POST">
      <input type="hidden" name="recipeID" value="<?= $recipeID ?>">
      <button class="action-btn" <?= $isFav ? 'disabled' : '' ?>>
        ♡ <?= $isFav ? 'Added' : 'Add to favorites' ?>
      </button>
    </form>

    <!-- Like -->
    <form action="likeProcess.php" method="POST">
      <input type="hidden" name="recipeID" value="<?= $recipeID ?>">
      <button class="action-btn like" <?= $isLiked ? 'disabled' : '' ?>>
        👍 <?= $isLiked ? 'Liked' : 'Like' ?>
      </button>
    </form>

    <!-- Report -->
    <form action="reportProcess.php" method="POST">
      <input type="hidden" name="recipeID" value="<?= $recipeID ?>">
      <button class="action-btn danger" <?= $isReported ? 'disabled' : '' ?>>
        ⚠ <?= $isReported ? 'Reported' : 'Report' ?>
      </button>
    </form>

  </div>
  <?php endif; ?>

</div>

<!-- Info -->
<div class="recipe-info">
  <p><strong>Category:</strong> <?= $recipe['categoryName'] ?></p>
  <p><?= $recipe['description'] ?></p>
</div>

<!-- ===================== -->
<!-- Ingredients + Steps -->
<!-- ===================== -->
<div class="recipe-sections">

  <div class="recipe-box">
    <h2>Ingredients</h2>
    <ul>
      <?php foreach($ingredients as $ing): ?>
        <li><?= $ing['IngredientQuantity'] . " - " . $ing['IngredientName'] ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="recipe-box">
    <h2>Instructions</h2>
    <ol>
      <?php foreach($steps as $step): ?>
        <li><?= $step['step'] ?></li>
      <?php endforeach; ?>
    </ol>
  </div>

</div>

<!-- ===================== -->
<!-- Video -->
<!-- ===================== -->
<?php if (!empty($recipe['videoFilePath'])): ?>
<a href="<?= $recipe['videoFilePath'] ?>" class="video-box exciting-video">
  <span class="play-icon">▶</span>
  <div>
    <strong>Watch the Baking Process</strong>
    <small>See step by step</small>
  </div>
</a>
<?php endif; ?>

<!-- ===================== -->
<!-- Comments -->
<!-- ===================== -->
<div class="comments">
<h2>Comments</h2>

<?php foreach($comments as $c): ?>
<div class="comment">
  <strong><?= $c['firstName'] ?>:</strong> <?= $c['comment'] ?>
</div>
<?php endforeach; ?>

<!-- Add Comment -->
<form action="addComment.php" method="POST">
  <input type="hidden" name="recipeID" value="<?= $recipeID ?>">
  <textarea name="comment" required placeholder="Add your comment"></textarea>
  <button type="submit">Post</button>
</form>

</div>

</div>
</body>
</html>
