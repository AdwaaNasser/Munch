<?php
require_once 'DBconfig.php';

session_start();
$userID = $_SESSION['user_id'] ?? 1; // مؤقت

$id = $_GET['id'] ?? 0;

  // Add comment
if (isset($_POST['add_comment'])) {
    $comment = $_POST['comment'];

    if (!empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO comment (recipeID, userID, comment, date) VALUES (?,?,?,NOW())");
        $stmt->execute([$id, $userID, $comment]);
    }
}

//Like 

if (isset($_POST['like'])) {
    $stmt = $pdo->prepare("SELECT * FROM likes WHERE userID=? AND recipeID=?");
    $stmt->execute([$userID, $id]);

    if ($stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO likes (userID, recipeID) VALUES (?,?)");
        $stmt->execute([$userID, $id]);
    }
}

// Favourit
if (isset($_POST['fav'])) {
    $stmt = $pdo->prepare("SELECT * FROM favourites WHERE userID=? AND recipeID=?");
    $stmt->execute([$userID, $id]);

    if ($stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO favourites (userID, recipeID) VALUES (?,?)");
        $stmt->execute([$userID, $id]);
    }
}

// recipe + user + category
$sql = "SELECT r.*, c.categoryName, u.firstName, u.lastName, u.photoFileName AS userPhoto
        FROM recipe r
        JOIN recipecategory c ON r.categoryID = c.id
        JOIN user u ON r.userID = u.id
        WHERE r.id = ?";

$stmt = $pdo->prepare($sql);
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

// comments
$stmt = $pdo->prepare("SELECT c.comment, u.firstName 
                       FROM comment c 
                       JOIN user u ON c.userID = u.id
                       WHERE c.recipeID=?");
$stmt->execute([$id]);
$comments = $stmt->fetchAll();

// likes count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE recipeID=?");
$stmt->execute([$id]);
$likesCount = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Recipe</title>
<link rel="stylesheet" href="style.css">
</head>

<body class="view-page">

<div class="recipe-card">

<!-- Title -->
<h1><?= $recipe['name'] ?></h1>

<!-- Image -->
<img src="<?= $recipe['photoFileName'] ?>" class="recipe-img">

<!-- Creator + Actions -->
<div class="creator-actions">

  <div class="creator">
    <img src="<?= $recipe['userPhoto'] ?>">
    <div>
      <strong><?= $recipe['firstName'] . " " . $recipe['lastName'] ?></strong>
      <small><?= $recipe['categoryName'] ?></small>
    </div>
  </div>

  <form method="POST" class="recipe-actions">

    <button name="fav" class="action-btn">
      ♡ Add to favorites
    </button>

    <button name="like" class="action-btn like">
      👍 Like (<?= $likesCount ?>)
    </button>

    <button class="action-btn danger">
      ⚠ Report
    </button>

  </form>

</div>

<!-- Info -->
<div class="recipe-info">
  <p><strong>Category:</strong> <?= $recipe['categoryName'] ?></p>
  <p><?= $recipe['description'] ?></p>
</div>

<!-- Ingredients + Instructions -->
<div class="recipe-sections">

  <!-- Ingredients -->
  <div class="recipe-box">
    <h2>Ingredients</h2>
    <ul>
      <?php foreach($ingredients as $ing): ?>
        <li><?= $ing['IngredientQuantity'] ?> - <?= $ing['IngredientName'] ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Instructions -->
  <div class="recipe-box">
    <h2>Instructions</h2>
    <ol>
      <?php foreach($steps as $step): ?>
        <li><?= $step['step'] ?></li>
      <?php endforeach; ?>
    </ol>
  </div>

</div>

<!-- Video -->
<?php if(!empty($recipe['videoFilePath'])): ?>
<div class="video-box exciting-video">
  <span class="play-icon">▶</span>
  <div>
    <strong>Watch the Baking Process</strong>
    <small><?= $recipe['videoFilePath'] ?></small>
  </div>
</div>
<?php endif; ?>

<!-- Comments -->
<div class="comments">
  <h2>Comments</h2>

  <?php foreach($comments as $com): ?>
    <div class="comment">
      <strong><?= $com['firstName'] ?>:</strong> <?= $com['comment'] ?>
    </div>
  <?php endforeach; ?>

  <form method="POST">
    <textarea name="comment" placeholder="Add your comment"></textarea>
    <button name="add_comment">Post</button>
  </form>

</div>

</div>

</body>
</html>