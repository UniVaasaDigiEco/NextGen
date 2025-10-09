<?php
session_start();
require_once('../classes/tools.class.php');

if(!$_SESSION['user'])
{
    header('Location: ../index.php');
    die();
}
$t = new DateTime();
$date = $t->format('Y-m-d');
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
            setChart();
            let $dataFieldset = $('#dataFieldset');
            $('#btnSearch').on('click', function() {
                if($dataFieldset.is(':visible'))
                    $dataFieldset.slideToggle();
                $('#loadingCover').fadeIn(400, function(){
                    let predictionDate = $('#date').val();
                    predictionData = fetchPrediction(predictionDate);
                    setPredictionTable(predictionData);
                });

                setTimeout(function() {
                    $('#loadingCover').fadeOut();
                    $dataFieldset.slideToggle('400', function(){
                    });
                }, 5000);
            });

            $('#btnSearchCourse').on('click', function() {
                alert("Näytä tässä ruokalajit dialogina.");
            });
        });

        function setPredictionTable(data){
            let $body = $('#predictionTableBody');
            let $perPersonBody = $('#predictionPerPersonTableBody');
            $body.html("");
            $perPersonBody.html("");
            data.forEach((element) => {
                let id = element.id;
                let date = element.date.toLocaleDateString('fi-FI');
                let day = element.day;
                let menuId = "#day" + id;
                let dayText = element.date.toLocaleDateString('fi-FI', {weekday: 'long'}) +"<br>"+ date;
                $(menuId).html(dayText);
                let plate = Math.round(element.platePrediction * 100) / 100 + " kg";
                let kitchen = Math.round(element.kitchenPrediction * 100) / 100 + " kg";
                let total = Math.round(element.totalPrediction * 100) / 100 + " kg";
                let client = Math.round(element.clientPrediction * 100) / 100;
                let platePerPerson = Math.round(element.platePerPerson * 100) * 10 + " g";
                let kitchenPerPerson = Math.round(element.kitchenPerPerson * 100) * 10 + " g";
                let totalPerPerson = Math.round(element.totalPerPerson * 100) * 10 + " g";

                let color = "";
                if(element.date.getDay() === 0 || element.date.getDay() === 6)
                {
                    color = "class='weekend'";
                }
                let row = `<tr ${color}><td class='day text-capitalize'>${day}</td><td>${date}</td><td>${plate}</td><td>${kitchen}</td><td>${total}</td><td>${client}</td>`;
                let perPersonRow = `<tr ${color}><td class='day text-capitalize'>${day}</td><td>${date}</td><td>${platePerPerson}</td><td>${kitchenPerPerson}</td><td>${totalPerPerson}</td><td>${client}</td>`

                $body.append(row);
                $perPersonBody.append(perPersonRow);
            });
        }
        /** Fetch prediction data for a given date
         *
         * @returns {null | array}
         */
        function fetchPrediction(inputDate){
            let predictionData = [];

            for(let i = 0; i <= 7; i++)
            {
                let platePrediction = getRndInteger(15, 30);
                let kitchenPrediction = getRndInteger(10, 50);
                let totalPrediction = platePrediction + kitchenPrediction;
                let clientPrediction = getRndInteger(330, 900);
                let platePerPerson = platePrediction / clientPrediction;
                let kitchenPerPerson = kitchenPrediction / clientPrediction;
                let totalPerPerson = platePerPerson + kitchenPerPerson;

                let date = new Date(inputDate);

                date.setDate(date.getDate() + i);
                let day = date.toLocaleDateString('en-US', { weekday: "short" });

                let newEntry = {
                    "id": i,
                    "date": date,
                    "day": day,
                    "platePrediction": platePrediction,
                    "kitchenPrediction": kitchenPrediction,
                    "totalPrediction": totalPrediction,
                    "clientPrediction": clientPrediction,
                    "platePerPerson": platePerPerson,
                    "kitchenPerPerson": kitchenPerPerson,
                    "totalPerPerson": totalPerPerson
                };

                predictionData.push(newEntry);
            }
            return predictionData;
        }

        Date.prototype.addDays = function(days) {
            let date = new Date(this.valueOf());
            date.setDate(date.getDate() + days);
            return date;
        }

        function getRndInteger(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        const tempData = {
            labels: [
                "Monday",
                "Tuesday",
                "Wednesday",
                "Thursday",
                "Friday",
                "Saturday",
                "Sunday"
            ],
            datasets: [{
                label: 'Total waste',
                data: [5, 15, 12, 18, 25, 10, 15],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(162, 205, 86)',
                    'rgb(235, 205, 86)',
                    'rgb(54, 205, 86)',
                    'rgb(255, 99, 86)',
                    'rgb(255, 50, 150)'
                ],
                hoverOffset: 4
            }]
        }

        const config = {
            type: 'pie',
            data: tempData,
            options: {
                plugins: {
                    legend: {
                        position: "top"
                    },
                    title: {
                        display: true,
                        text: "Percentage of waste in a week per day"
                    }
                }
            }
        };

        const config2 = {
            type: 'bar',
            data: tempData,
            options: {
                plugins: {
                    legend: {
                        position: "top"
                    },
                    title: {
                        display: true,
                        text: "Total waste per day in a week (kg)"
                    }
                }
            }
        };

        function setChart(){
            let chart1 = new Chart(document.getElementById('chart1'), config);
            let chart2 = new Chart(document.getElementById('chart2'), config2);
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
        dl{
            text-transform: capitalize;
        }

        input[type=date]{
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="modal fade modal-xl" id="foodModal1" tabindex="-1" data-bs-backdrop="static" aria-labelledby="foodModal1Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="foodModal1Label">Fish soup</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    Below you'll see predictions for the days when on the menu there was: <span class="fw-bold">Fish soup</span>
                </p>
                <div class="row">
                    <h4>Predictions</h4>
                    <div class="col-12 mb-3">
                        <div class="overflow-x-auto">
                            <table id="predictionTable" class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th><br>Day</th><th>Date</th><th>Plate waste<br>Prediction / Realization</th><th>Kitchen waste<br>Prediction / Realization</th><th>Total waste<br>Prediction / Realization</th><th>Visitor prediction<br>Prediction / Realization</th>
                                </tr>
                                </thead>
                                <tbody id="predictionLunchTableBody">
                                <tr>
                                    <td>Mon</td><td>1.1.2024</td><td>24 kg / 25 kg</td><td>15 kg / 12 kg</td><td>39 kg / 37 kg</td><td>713 / 715</td>
                                </tr>
                                <tr>
                                    <td>Mon</td><td>15.1.2024</td><td>26 kg / 20 kg</td><td>16 kg / 13 kg</td><td>42 kg / 20kg</td><td>890 / 850</td>
                                </tr>
                                <tr>
                                    <td>Thu</td><td>25.1.2024</td><td>25 kg / 25 kg</td><td>37 kg / 40 kg</td><td>62 kg / 58 kg</td><td>351 / 355</td>
                                </tr>
                                <tr>
                                    <td>Sat</td><td>10.2.2024</td><td>30 kg / 29 kg</td><td>39 kg / 42 kg</td><td>69 kg / 40 kg</td><td>826 / 900</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <h4>Predictions per person</h4>
                    <div class="col-12">
                        <div class="overflow-x-auto">
                            <table id="predictionTable" class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th><br>Day</th><th>Date</th><th>Plate waste<br>Prediction / Realization</th><th>Kitchen waste<br>Prediction / Realization</th><th>Total waste<br>Prediction / Realization</th><th>Visitor prediction<br>Prediction / Realization</th>
                                </tr>
                                </thead>
                                <tbody id="predictionDinnerTableBody">
                                <tr>
                                    <td>Mon</td><td>1.1.2024</td><td>24 g / 25 g</td><td>15 g / 12 g</td><td>39 g / 37 g</td><td>713 / 715</td>
                                </tr>
                                <tr>
                                    <td>Mon</td><td>15.1.2024</td><td>26 g / 20 g</td><td>16 g / 13 g</td><td>42 g / 20 g</td><td>890 / 850</td>
                                </tr>
                                <tr>
                                    <td>Thu</td><td>25.1.2024</td><td>25 g / 25 g</td><td>37 g / 40 g</td><td>62 g / 58 g</td><td>351 / 355</td>
                                </tr>
                                <tr>
                                    <td>Sat</td><td>10.2.2024</td><td>30 g / 29 g</td><td>39 g / 42 g</td><td>69 g / 40 g</td><td>826 / 900</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="cover" id="loadingCover">
    <div class="spinner-border text-success spinner-margin">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
<nav class="navbar navbar-expand-lg bg-body" style="z-index: 100">
    <div class="container-fluid">
        <a class="navbar-brand" href="main.php">
            <img alt="NextGen" src="../images/logo_horizontal.svg" height="50">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a href="main.php" class="nav-link">Today's prediction</a>
                </li>
                <li class="nav-item">
                    <a href="weekly.php" class="nav-link">Week's prediction</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link active dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Predictions & Actuals
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">By date</a></li>
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
            <h4>History Data</h4>
        </div>
    </div>
    <div class="row my-3">
        <div class="row">
            <div class="col-6 mb-3">
                <fieldset class="p-3 rounded-3 border border-1 border-black">
                    <legend>Search predictions & realization by date</legend>
                    <div class="row">
                        <div class="col-12 col-lg-12 mb-3">
                            <?php
                            $date = new DateTime();
                            ?>
                            <div class="form-floating mb-3">
                                <input type="date" onfocus="this.showPicker()" class="form-control" id="date" name="email" aria-describedby="dateHelp" value="<?php echo $date->format('Y-m-d'); ?>">
                                <label for="date">Date</label>
                                <div class="form-text">
                                    <span>Please insert a date. You'll get the predictions & realizations of the next 7 days of the given date.</span>
                                </div>
                            </div>
                            <button id="btnSearch" type="button" class="btn btn-success"><i class="bi bi-search"></i> Search</button>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-6 mb-3" style="display: none;">
                <fieldset class="p-3 rounded-3 border border-1 border-black">
                    <legend>Search predictions & realizations by dish</legend>
                    <div class="row">
                        <div class="col-12 col-lg-12 mb-3">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="course" name="course" aria-describedby="courseHelp" value="Fish soup" readonly>
                                <label for="course">Dish</label>
                                <div class="form-text">
                                    <span>Please give the name of a dish (NOTE: For the demo, the dish is hard coded and cannot be changed)</span>
                                </div>
                            </div>
                            <a class="btn btn-success" href="#" id="showFood1" data-bs-toggle="modal" data-bs-target="#foodModal1"><i class="bi bi-search"></i> Search</a>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="col-12 mb-3">
            <fieldset id="dataFieldset" style="display: none;" class="p-3 rounded-3 border border-1 border-black">
                <legend>Predictions</legend>
                <!-- ENNUSTETAULUKOT -->
                <div class="row">
                    <div class="col-12 col-lg-6 mb-3">
                        <fieldset class="p-3 rounded-3 border border-1 border-black">
                            <legend>Waste predictions</legend>
                            <div class="overflow-x-auto">
                                <table id="predictionTable" class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th><br>Day</th><th>Date</th><th>Plate waste</th><th>Kitchen waste</th><th>Total waste</th><th>Visitor prediction</th>
                                    </tr>
                                    </thead>
                                    <tbody id="predictionTableBody">
                                    <?php
                                    $current_prediction = Tools::GetCurrentPrediction();
                                    if(empty($current_prediction))
                                        echo "<tr><td colspan='9'>Ei tämänhetkisiä ennusteita</td></tr>";
                                    /*else{
                                        $prediction_date = $current_prediction['prediction_date'];
                                        $prediction_kitchen = $current_prediction['prediction_kitchen'];
                                        $prediction_plate = $current_prediction['prediction_plate'];
                                        $prediction_total = $prediction_kitchen + $prediction_plate;
                                        $prediction_visitor = rand(100, 1000);
                                        echo "<tr>";
                                        echo "<td>$prediction_date</td><td>$prediction_plate</td><td>$prediction_kitchen</td><td>$prediction_total</td><td>$prediction_visitor</td>";
                                        echo "</tr>";
                                    }*/
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-12 col-lg-6 mb-3">
                        <fieldset class="p-3 rounded-3 border border-1 border-black">
                            <legend>Waste predictions per person</legend>
                            <div class="overflow-x-auto">
                                <table id="predictionPerPersonTable" class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th><br>Day</th><th>Date</th><th>Plate waste</th><th>Kitchen waste</th><th>Total waste</th><th>Visitor prediction</th>
                                    </tr>
                                    </thead>
                                    <tbody id="predictionPerPersonTableBody">
                                    <?php
                                    $current_prediction = Tools::GetCurrentPrediction();
                                    if(empty($current_prediction))
                                        echo "<tr><td colspan='9'>No current predictions</td></tr>";
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <!-- RUOKALISTAT JA VARAUKSET -->
                <!-- Modal -->

                <div class="row">
                    <div class="col-12 col-lg-6 mb-3">
                        <fieldset class="p-3 rounded-3 border border-1 border-black">
                            <legend>Menus</legend>
                            <div>
                                <dl class="row">
                                    <dt class="col-12 col-sm-3 col-lg-2"><span id="day0"></span></dt>
                                    <dd class="col-12 col-sm-9 col-lg-10">
                                        <a href="#" id="showFood1" data-bs-toggle="modal" data-bs-target="#foodModal1">Fish soup</a><br>
                                        Dish 2<br>
                                        Side dish 1<br>
                                        Side dish 2<br>
                                        Dessert<br>
                                    </dd>

                                    <dt class="col-12 col-sm-3 col-lg-2"><span id="day1"></span></dt>
                                    <dd class="col-12 col-sm-9 col-lg-10">
                                        Dish 1<br>
                                        Dish 2<br>
                                        Side dish 1<br>
                                        Side dish 2<br>
                                        Dessert<br>
                                    </dd>

                                    <dt class="col-12 col-sm-3 col-lg-2"><span id="day2"></span></dt>
                                    <dd class="col-12 col-sm-9 col-lg-10">
                                        Dish 1<br>
                                        Dish 2<br>
                                        Side dish 1<br>
                                        Side dish 2<br>
                                        Dessert<br>
                                    </dd>

                                    <dt class="col-12 col-sm-3 col-lg-2"><span id="day3"></span></dt>
                                    <dd class="col-12 col-sm-9 col-lg-10">
                                        Dish 1<br>
                                        Dish 2<br>
                                        Side dish 1<br>
                                        Side dish 2<br>
                                        Dessert<br>
                                    </dd>

                                    <dt class="col-12 col-sm-3 col-lg-2"><span id="day4"></span></dt>
                                    <dd class="col-12 col-sm-9 col-lg-10">
                                        Dish 1<br>
                                        Dish 2<br>
                                        Side dish 1<br>
                                        Side dish 2<br>
                                        Dessert<br>
                                    </dd>

                                    <dt class="col-12 col-sm-3 col-lg-2"><span id="day5"></span></dt>
                                    <dd class="col-12 col-sm-9 col-lg-10">
                                        Dish 1<br>
                                        Dish 2<br>
                                        Side dish 1<br>
                                        Side dish 2<br>
                                        Dessert<br>
                                    </dd>

                                    <dt class="col-12 col-sm-3 col-lg-2"><span id="day6"></span></dt>
                                    <dd class="col-12 col-sm-9 col-lg-10">
                                        Dish 1<br>
                                        Dish 2<br>
                                        Side dish 1<br>
                                        Side dish 2<br>
                                        Dessert<br>
                                    </dd>

                                    <dt class="col-12 col-sm-3 col-lg-2"><span id="day7"></span></dt>
                                    <dd class="col-12 col-sm-9 col-lg-10">
                                        Dish 1<br>
                                        Dish 2<br>
                                        Side dish 1<br>
                                        Side dish 2<br>
                                        Dessert<br>
                                    </dd>
                                </dl>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-12 col-lg-6 mb-3">
                        <fieldset class="p-3 rounded-3 border border-1 border-black">
                            <legend>Reservation data</legend>
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Date</th><th>Organization</th><th>Reservations</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>01.01.2024</td><td>Kouvolan palloketut</td><td>25</td>
                                </tr>
                                <tr>
                                    <td>02.01.2024</td><td>VPS, junioritytöt</td><td>20</td>
                                </tr>
                                <tr>
                                    <td>03.01.2024</td><td>VPS, aikuiset naiset</td><td>30</td>
                                </tr>
                                <tr>
                                    <td>04.01.2024</td><td>Oulun kärpät</td><td>100</td>
                                </tr>
                                </tbody>
                            </table>
                        </fieldset>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-lg-6 mb-3">
                        <fieldset class="p-3 rounded-3 border border-1 border-black">
                            <legend>Graph 1</legend>
                            <div>
                                <canvas id="chart1" class="w-100">
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-12 col-lg-6 mb-3">
                        <fieldset class="p-3 rounded-3 border border-1 border-black">
                            <legend>Graph 2</legend>
                            <div>
                                <canvas id="chart2" class="w-100">
                            </div>
                        </fieldset>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</main>
</body>
</html>

