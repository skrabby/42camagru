<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Camagru</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<?php
require_once 'components/header.php';
?>
<div class="login-block">
    <div class="login-form">
        <form>
            <input class="input" type="text" placeholder="Login"/>
            <input class="input" type="password" placeholder="Password"/>
            <input class="submit-btn" type="submit" value="Log In">
        </form>
        <a href="#">Forgot your password?</a>
        <a href="#">Not registered yet? Sign up!</a>
    </div>
</div>
<?php
require_once 'components/footer.php';
?>
</body>
</html>