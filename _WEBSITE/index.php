<?php
session_start();
require_once ('classes/tools.class.php');
$db = Tools::GetDB();
?>
<!doctype html>
<html lang="fi" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NextGen - Ruokahävikkiennuste</title>
    <link rel="icon" href="images/logo_icon.svg">
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.min.css">
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        html, body{
            height: 100%;
        }
        .form-signin{
            min-width: 450px;
            max-width: 60vw;
            padding: 1rem;
        }
    </style>
</head>
<body class="d-lg-flex align-items-center py-4 bg-body-tertiary">
<main class="form-signin m-auto">
    <img src="images/logo_big.svg" alt="">
    <?php
    if(!empty($_SESSION['error'])) {
        Tools::ShowError($_SESSION['error']);
        unset($_SESSION['error']);
    }

    if(!empty($_SESSION['message'])) {
        Tools::ShowMessage($_SESSION['message']);
        unset($_SESSION['message']);
    }


    ?>
    <form action="actions/login.php" method="post">
        <div class="form-floating mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="nimi@esimerkki.fi">
            <label for="email">Sähköpostiosoite</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="password" name="password" placeholder="Salasana">
            <label for="password">Salasana</label>
        </div>
        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-box-arrow-in-right"></i> Kirjaudu sisään</button>
    </form>
</main>
</body>
</html>
