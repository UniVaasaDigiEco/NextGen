<?php
session_start();
require_once('../classes/tools.class.php');

if(!$_SESSION['user'])
{
    header('Location: ../index.php');
    die();
}
?>
<!doctype html>
<html lang="fi" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NextGen - Ruokahävikkiennuste</title>
    <link rel="icon" href="../images/logo_icon.svg">
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../node_modules/bootstrap-icons/font/bootstrap-icons.min.css">
    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script>

    </script>
    <style>
    </style>
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-lg bg-body">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img alt="NextGen" src="../images/logo_horizontal.svg" height="50">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a href="#" class="nav-link">Päivän ennuste</a>
                </li>
                <li class="nav-item">
                    <a href="weekly.php" class="nav-link">Viikkoennuste</a>
                </li>
                <li class="nav-item">
                    <a href="history.php" class="nav-link">Historia (MALLI)</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container-fluid">

</main>
</body>
</html>

