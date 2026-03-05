<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Log In</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="login-page">

  <div class="auth-card">
    <h1>Log In</h1>
 
     <form action="loginProcess.php" method="post">
      
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password"  name="password" required>
      </div>

      <div class="button-group">
       <button type="submit">Log In</button>
      </div>

    </form>
    <?php
    session_start();
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    if (isset($_SESSION['success_message'])) {
        echo '<div class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
        unset($_SESSION['success_message']);
    }
    ?>
  
</body>
</html>