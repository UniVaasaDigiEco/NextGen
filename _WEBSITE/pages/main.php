<?php
session_start();
require_once('../classes/tools.class.php');

if(!$_SESSION['user'])
{
    header('Location: ../index.php');
    die();
}

$date = false;
if(!empty($_GET['date']))
    $date = $_GET['date'];
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
    <link rel="stylesheet" href="../css/main.css">
    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script>

        let predictionData = null;
        let predictionChart = null;
        let clientPredictionChart = null;
        let chartColor = "#000000";
        let fontSize = 14;
        //Document ready
        $(function(){
            let date = '<?php echo $date; ?>';
            fetchPrediction(date);
        });

        function setDailyPredictions(data){
            let date = data.date.toLocaleDateString('fi-FI');
            let day = data.day;

            $('#dateField').text(`${day}, ${date}`);
            let clientsLunch = Math.ceil(data.lunch.clientPrediction);
            let clientsDinner = Math.ceil(data.dinner.clientPrediction);
            let kitchenLunch = data.lunch.kitchen;
            let kitchenPerPersonLunch = data.lunch.kitchenPerPerson;
            let kitchenDinner = data.dinner.kitchen;
            let kitchenPerPersonDinner = data.dinner.kitchenPerPerson;
            let plateLunch = data.lunch.plate;
            let platePerPersonLunch = data.lunch.platePerPerson;
            let plateDinner = data.dinner.plate;
            let platePerPersonDinner = data.dinner.platePerPerson;
            let totalLunch = data.lunch.total;
            let totalPerPersonLunch = data.lunch.totalPerPerson;
            let totalDinner = data.dinner.total;
            let totalPerPersonDinner = data.dinner.totalPerPerson;

            let totalClients = clientsLunch + clientsDinner;
            let totalKitchen = kitchenLunch + kitchenDinner;
            let totalKitchenPerPerson = Math.round(((kitchenLunch + kitchenDinner) / totalClients) * 100) / 100;
            let totalPlate = plateLunch + plateDinner;
            let totalPlatePerPerson = Math.round(((plateLunch + plateDinner) / totalClients) * 100) / 100;
            let total = totalLunch + totalDinner;
            let totalPerPerson = Math.round(((totalLunch + totalDinner) / totalClients) * 100) / 100;

            $('#clientPredictionFieldLunch').text(`${clientsLunch}`);
            $('#kitchenPredictionFieldLunch').text(kitchenLunch + " kg");
            $('#kitchenPredictionPerPersonFieldLunch').text(kitchenPerPersonLunch * 1000 + " g");
            $('#platePredictionFieldLunch').text(plateLunch + " kg");
            $('#platePredictionPerPersonFieldLunch').text(platePerPersonLunch * 1000 + " g");
            $('#totalPredictionFieldLunch').text(totalLunch + " kg");
            $('#totalPredictionPerPersonFieldLunch').text(totalPerPersonLunch * 1000 + " g");

            $('#clientPredictionFieldDinner').text(`${clientsDinner}`);
            $('#kitchenPredictionFieldDinner').text(kitchenDinner + " kg");
            $('#kitchenPredictionPerPersonFieldDinner').text(kitchenPerPersonDinner * 1000 + " g");
            $('#platePredictionFieldDinner').text(plateDinner + " kg");
            $('#platePredictionPerPersonFieldDinner').text(platePerPersonDinner * 1000 + " g");
            $('#totalPredictionFieldDinner').text(totalDinner + " kg");
            $('#totalPredictionPerPersonFieldDinner').text(totalPerPersonDinner * 1000 + " g");

            $('#clientPredictionFieldTotal').text(totalClients);
            $('#kitchenPredictionFieldTotal').text(totalKitchen + " kg");
            $('#kitchenPredictionPerPersonFieldTotal').text(totalKitchenPerPerson * 1000 + " g");
            $('#platePredictionFieldTotal').text(totalPlate + " kg");
            $('#platePredictionPerPersonFieldTotal').text(totalPlatePerPerson * 1000 + " g");
            $('#totalPredictionFieldTotal').text(total + " kg");
            $('#totalPredictionPerPersonFieldTotal').text(totalPerPerson * 1000 + " g");

        }

        function fetchPrediction(fetchDate){
            console.log('Fetching prediction for date: ' + fetchDate);
            let number_of_days = 7;
            let url = "https://nextgen-foodwaste-prediction.azurewebsites.net/api/predict_food_waste";
            if(fetchDate)
            {
                url = `https://nextgen-foodwaste-prediction.azurewebsites.net/api/predict_food_waste?prediction_start_date=${fetchDate}&num_days=${number_of_days}`;
            }
            console.log(url);

            $('#loadingCover').show();
            $.ajax({
                headers: {
                },
                type: "GET",
                url: url,
                success: function(data){
                    console.log("FETCHING DONE");
                    console.log(data);
                    $('#loadingCover').fadeOut();
                    let lines = data.split(/\r?\n|\r|\n/g);
                    lines.forEach(function(currentValue, index) {
                        if(index > 1) {
                            let split = currentValue.split(/\s+/);
                            let date = new Date(split[0]);
                            let lunchType = parseInt(split[1]);
                            let platePrediction = split[2];
                            let kitchenPrediction = split[3];
                            let clientPrediction = split[4];
                            let currentDate = new Date();
                            if(fetchDate)
                                currentDate = new Date(fetchDate);
                            const isSameDay =
                                date.getFullYear() === currentDate.getFullYear() &&
                                date.getMonth() === currentDate.getMonth() &&
                                date.getDate() === currentDate.getDate();
                            if(isSameDay) {
                                if(predictionData === null)
                                {
                                    predictionData = {
                                        "date": date,
                                        "day": date.toLocaleDateString('fi-FI', { weekday: 'long' }),
                                        "lunch": {},
                                        "dinner": {},
                                    };
                                }

                                if(lunchType === 1){
                                    predictionData.lunch = {
                                        "id": index,
                                        "plate": parseFloat(platePrediction),
                                        "kitchen": parseFloat(kitchenPrediction),
                                        "total": Math.round((parseFloat(platePrediction) + parseFloat(kitchenPrediction)) * 100) / 100,
                                        "platePerPerson": Math.round((parseFloat(platePrediction) / parseFloat(clientPrediction)) * 100) / 100,
                                        "kitchenPerPerson": Math.round((parseFloat(kitchenPrediction) / parseFloat(clientPrediction)) * 100) / 100,
                                        "totalPerPerson": Math.round(((parseFloat(platePrediction) + parseFloat(kitchenPrediction)) / parseFloat(clientPrediction)) * 100) / 100,
                                        "clientPrediction": parseFloat(clientPrediction)
                                    }
                                }
                                else if(lunchType === 2){
                                    predictionData.dinner = {
                                        "id": index,
                                        "plate": parseFloat(platePrediction),
                                        "kitchen": parseFloat(kitchenPrediction),
                                        "total": Math.round((parseFloat(platePrediction) + parseFloat(kitchenPrediction)) * 100) / 100,
                                        "platePerPerson": Math.round((parseFloat(platePrediction) / parseFloat(clientPrediction)) * 100) / 100,
                                        "kitchenPerPerson": Math.round((parseFloat(kitchenPrediction) / parseFloat(clientPrediction)) * 100) / 100,
                                        "totalPerPerson": Math.round(((parseFloat(platePrediction) + parseFloat(kitchenPrediction)) / parseFloat(clientPrediction)) * 100) / 100,
                                        "clientPrediction": parseFloat(clientPrediction)
                                    }
                                }
                            }
                        }
                    });
                    setDailyPredictions(predictionData);
                }
            });
        }
    </script>
    <style>
        .cover{
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.3);
            z-index: 99;
            position: fixed;
            top: 0;
            left: 0;
            text-align: center;
            display:none;
        }
        .spinner-margin{
            height: 50px;
            width: 50px;
            margin-top: calc(50vh - 25px);
        }

        .bg-satSun td{
            background: rgb(31, 166, 75) !important;
            color: white;
        }

        @media print{
            .pagebreak {
                page-break-before: always;
            }

            #predictionsChart{
                width: 100% !important;
                height: auto !important;
            }

            #clientPredictionsChart{
                width: 100% !important;
                height: auto !important;
            }
        }
    </style>
</head>
<body>
<div class="cover" id="loadingCover">
    <div class="spinner-border text-success spinner-margin">
        <span class="visually-hidden">Ladataan...</span>
    </div>
</div>
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
                    <a href="#" class="nav-link active">Today's prediction</a>
                </li>
                <li class="nav-item">
                    <a href="weekly.php" class="nav-link">Week's prediction</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Predictions & Actuals
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="history.php">By date</a></li>
                        <!--<li><a class="dropdown-item" href="dish.php">By dish</a></li>-->
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container-fluid">
    <div class="row mt-3">
        <div class="col-12">
            <h4>Today's waste prediction - <span class="text-capitalize" id="dateField"></span></h4>
        </div>
        <div class="col-12">
            <a class="btn btn-primary" href="main.php?date=<?php echo date('Y-m-d', strtotime($date . ' -1 day')) ?>"><i class="bi bi-arrow-left"></i> Previous day</a>
            <a class="btn btn-primary" href="main.php?date=<?php echo date('Y-m-d', strtotime($date . ' +1 day')); ?>">Next day <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="row my-3">
        <div class="col-12 col-lg-4 mb-3">
            <fieldset class="p-3 rounded-3 border border-1 border-black">
                <legend>Prediction for the day</legend>
                <div class="overflow-auto">
                    <table class="table table-striped table-hover">
                        <tbody>
                        <tr>
                            <th>Visitors</th><td><span id="clientPredictionFieldLunch"></span></td>
                        </tr>
                        <tr>
                            <th>Kitchen waste</th><td><span id="kitchenPredictionFieldLunch"></span></td>
                        </tr>
                        <tr>
                            <th>Kitchen waste per person</th><td><span id="kitchenPredictionPerPersonFieldLunch"></span></td>
                        </tr>
                        <tr>
                            <th>Plate waste</th><td><span id="platePredictionFieldLunch"></span></td>
                        </tr>
                        <tr>
                            <th>Plate waste per person</th><td><span id="platePredictionPerPersonFieldLunch"></span></td>
                        </tr>
                        <tr>
                            <th>Total waste</th><td><span id="totalPredictionFieldLunch"></span></td>
                        </tr>
                        <tr>
                            <th>Total waste per person</th><td><span id="totalPredictionPerPersonFieldLunch"></span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
        <div class="col-12 col-lg-4 mb-3">
            <fieldset class="p-3 rounded-3 border border-1 border-black">
                <legend>Prediction for dinner</legend>
                <div class="overflow-auto">
                    <table class="table table-striped table-hover">
                        <tbody>
                        <tr>
                            <th>Visitors</th><td><span id="clientPredictionFieldDinner"></span></td>
                        </tr>
                        <tr>
                            <th>Kitchen waste</th><td><span id="kitchenPredictionFieldDinner"></span></td>
                        </tr>
                        <tr>
                            <th>Kitchen waste per person</th><td><span id="kitchenPredictionPerPersonFieldDinner"></span></td>
                        </tr>
                        <tr>
                            <th>Plate waste</th><td><span id="platePredictionFieldDinner"></span></td>
                        </tr>
                        <tr>
                            <th>Plate waste per person</th><td><span id="platePredictionPerPersonFieldDinner"></span></td>
                        </tr>
                        <tr>
                            <th>Total waste</th><td><span id="totalPredictionFieldDinner"></span></td>
                        </tr>
                        <tr>
                            <th>Total waste per person</th><td><span id="totalPredictionPerPersonFieldDinner"></span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
        <div class="col-12 col-lg-4 mb-3">
            <fieldset class="p-3 rounded-3 border border-1 border-black">
                <legend>Prediction for total</legend>
                <div class="overflow-auto">
                    <table class="table table-striped table-hover">
                        <tbody>
                        <tr>
                            <th>Visitors</th><td><span id="clientPredictionFieldTotal"></span></td>
                        </tr>
                        <tr>
                            <th>Kitchen waste</th><td><span id="kitchenPredictionFieldTotal"></span></td>
                        </tr>
                        <tr>
                            <th>Kitchen waste per person</th><td><span id="kitchenPredictionPerPersonFieldTotal"></span></td>
                        </tr>
                        <tr>
                            <th>Plate waste</th><td><span id="platePredictionFieldTotal"></span></td>
                        </tr>
                        <tr>
                            <th>Plate waste per person</th><td><span id="platePredictionPerPersonFieldTotal"></span></td>
                        </tr>
                        <tr>
                            <th>Total waste</th><td><span id="totalPredictionFieldTotal"></span></td>
                        </tr>
                        <tr>
                            <th>Total waste per person</th><td><span id="totalPredictionPerPersonFieldTotal"></span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!--<legend>Reservations for the day</legend>
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Organization</th><th>Reservations</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="2" class="text-center">Ei saatavilla</td>
                    </tr>
                    </tbody>
                </table>-->
            </fieldset>
        </div>
    </div>
</main>
</body>
</html>

