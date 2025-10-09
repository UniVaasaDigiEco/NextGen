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
<html lang="fi" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NextGen - Ruokahävikkiennuste</title>
    <link rel="icon" href="../images/logo_icon.svg">
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../node_modules/bootstrap-icons/font/bootstrap-icons.min.css">
    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script>
    </script>
    <style>
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body" style="z-index: 100">
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
                    <a href="#" class="nav-link active">Päivän ennuste</a>
                </li>
                <li class="nav-item">
                    <a href="weekly.php" class="nav-link">Viikkoennuste</a>
                </li>
                <li class="nav-item">
                    <a href="history.php" class="nav-link">Historia (MALLI)</a>
                </li>
                <li class="nav-item">
                    <a href="upload.php" class="nav-link">Lataa varaukset/toteumat</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container-fluid">
    <div class="row mt-3">
        <div class="col-12">
            <h4>Lataa kävijöiden varaukset ja/tai toteumat</h4>
        </div>
    </div>
    <div class="row my-3">
        <div class="col-12 col-lg-12 mb-3">
            <fieldset class="p-3 rounded-3 border border-1 border-black">
                <legend>Lataa tiedosto palvelimelle</legend>
                <form action="../actions/read_excel.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="fileToUpload">Valitse tiedosto</label>
                        <input type="file" class="form-control" id="fileToUpload" name="toteuma" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="bi bi-file-arrow-up-fill"></i> Lataa</button>
                </form>
            </fieldset>
        </div>
    </div>
</main>
</body>
</html>

