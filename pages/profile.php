<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/photo.css">
</head>
<body>
<?php
require_once 'components/header.php';
?>
<div class="profile">
    <div class="container">
        <div class="flex-center-wrapper img">
            <img src="https://picsum.photos/250/250?random=10.jpg" alt="symbol image" title="symbol image">
        </div>
        <div class="flex-center-wrapper name">
            <h1>Duc Truong</h1>
        </div>
        <div class="flex-center-wrapper description">
            <p>Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum
                Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum
                Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum</p>
        </div>
    </div>
</div>
<?php
require_once 'components/gallery.php';
require_once 'components/footer.php';
?>
</body>
</html>