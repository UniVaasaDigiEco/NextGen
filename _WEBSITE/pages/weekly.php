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
if(!empty($_GET['date'])) {
    $date = $_GET['date'];
    $t = new DateTime($date);
}

$nextWeek = $t->add(new DateInterval('P7D'))->format('Y-m-d');
$lastWeek = $t->sub(new DateInterval('P14D'))->format('Y-m-d');

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
    <link rel="stylesheet" href="../js/datatables.min.css">
    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script src="../js/datatables.min.js"></script>
    <script>

        Date.prototype.addDays = function(days){
            let date = new Date(this.valueOf());
            date.setDate(date.getDate() + days);
            return date;
        }
        let $dataTable = null;
        let predictionData = null;
        let predictionChart = null;
        let clientPredictionChart = null;
        let chartColor = "#000000";
        let fontSize = 14;
        //Document ready
        $(function(){
            let date = "<?php echo $date; ?>";
            predictionData = fetchPrediction(date);

            window.addEventListener('beforeprint', function(){
                predictionChart.destroy();
                clientPredictionChart.destroy();
                setPredictionChart(predictionData, "#000000", fontSize);
            });
            window.addEventListener('afterprint', function(){
                predictionChart.destroy();
                clientPredictionChart.destroy();
                setPredictionChart(predictionData, chartColor, fontSize);
            });

            $('#btn_chartTogglePrediction').on('click', function(){
                if(predictionChart)
                {
                    let type = $(this).data('type');
                    if(type === "bar") {
                        predictionChart.config.type = "line";
                        $(this).data('type', "line");
                        $(this).removeClass('bi-graph-up');
                        $(this).addClass('bi-bar-chart-fill');
                        $(this).html(" Show bargraph");
                    }
                    else {
                        predictionChart.config.type = "bar";
                        $(this).data('type', 'bar');
                        $(this).removeClass('bi-bar-chart-fill');
                        $(this).addClass('bi-graph-up');
                        $(this).html(" Show linegraph");
                    }

                    predictionChart.update();
                }
            });

            $('#btn_chartToggleClient').on('click', function(){
                if(clientPredictionChart)
                {
                    let type = $(this).data('type');
                    if(type === "bar") {
                        clientPredictionChart.config.type = "line";
                        $(this).data('type', "line");
                        $(this).removeClass('bi-graph-up');
                        $(this).addClass('bi-bar-chart-fill');
                        $(this).html(" Show bargraph");
                    }
                    else {
                        clientPredictionChart.config.type = "bar";
                        $(this).data('type', 'bar');
                        $(this).removeClass('bi-bar-chart-fill');
                        $(this).addClass('bi-graph-up');
                        $(this).html(" Show linegraph");
                    }

                    clientPredictionChart.update();
                }
            });

            /*
            let $table = ('#predictionTable');
            $dataTable = new DataTable('#predictionTable', {
                searchable: true,
                sortable: true,
                paging: true,
                perPage: 10,
                perPageSelect: [5, 10, 20, 50, 100],
                labels: {
                    placeholder: "Hae...",
                    perPage: "{select} riviä per sivu",
                    noRows: "Ei rivejä",
                    info: "Näytetään {start} - {end} / {rows} riviä",
                },
                layout: {
                    topStart: {
                        searchPanes: {
                            // config options here
                        }
                    }
                }
            });

            $dataTable.searchPanes.rebuildPane();
            */
        });

        //Functions
        /**
         * Create charts for predictions
         * @param data Data for predictions
         * @param fontColor Color for the font (legend & axis)
         * @param fontSize Size for the font (legend & axis)
         */
        function setPredictionChart(data, fontColor, fontSize){
            Chart.defaults.color = fontColor;
            Chart.defaults.font.size = fontSize;
            predictionChart = new Chart(document.getElementById('predictionsChart'), {
                type: 'bar',
                data: {
                    labels: data.map(row => (row.date.toLocaleDateString('fi-FI', { weekday: "short" }) + ", " + row.date.getDate() + "." + (row.date.getMonth() + 1))),
                    datasets: [
                        {
                            label: "Kitchen waste prediction",
                            data: data.map(row => row.kitchenPrediction)
                        },
                        {
                            label: "Plate waste prediction",
                            data: data.map(row => row.platePrediction)
                        }
                    ]
                }
            });

            clientPredictionChart = new Chart(document.getElementById('clientPredictionsChart'), {
                type: 'bar',
                data: {
                    labels: data.map(row => (row.date.toLocaleDateString('fi-FI', { weekday: "short" }) + ", " + row.date.getDate() + "." + (row.date.getMonth() + 1))),
                    datasets: [
                        {
                            label: "Visitor prediction",
                            data: data.map(row => row.clientPrediction)
                        }
                    ]
                }
            });
        }

        let dinnerRows = {};
        /**
         * Set the table data for the predictions
         * @param data Data for predictions
         */
        function setPredictionTable(data){
            let $body = $('#predictionTableBody');
            $body.html("");
            for(let key in data){
                if(data.hasOwnProperty(key)) {
                    let element = data[key];

                    let day = element.day;
                    let dateStr = element.date.getDate() + "." + (element.date.getMonth() + 1);

                    let clientsLunch = Math.ceil(element.lunch.clientPrediction);
                    let clientsDinner = Math.ceil(element.dinner.clientPrediction);
                    let kitchenLunch = Math.round(element.lunch.kitchen * 100) / 100;
                    //let kitchenPerPersonLunch = element.lunch.kitchenPerPerson;
                    let kitchenDinner = Math.round(element.dinner.kitchen * 100) / 100;
                    //let kitchenPerPersonDinner = element.dinner.kitchenPerPerson;
                    let plateLunch = Math.round(element.lunch.plate * 100) / 100;
                    //let platePerPersonLunch = element.lunch.platePerPerson;
                    let plateDinner = Math.round(element.dinner.plate * 100) / 100;
                    //let platePerPersonDinner = element.dinner.platePerPerson;
                    let totalLunch = Math.round(element.lunch.total * 100) / 100;
                    //let totalPerPersonLunch = element.lunch.totalPerPerson;
                    let totalDinner = Math.round(element.dinner.total * 100) / 100;
                    //let totalPerPersonDinner = element.dinner.totalPerPerson;

                    let totalClients = clientsLunch + clientsDinner;
                    let totalKitchen = Math.round((kitchenLunch + kitchenDinner) * 100) / 100;
                    let totalKitchenPerPerson = Math.round(((kitchenLunch + kitchenDinner) / totalClients) * 100) / 100;
                    let totalPlate = Math.round((plateLunch + plateDinner) * 100) / 100;
                    let totalPlatePerPerson = Math.round(((plateLunch + plateDinner) / totalClients) * 100) / 100;
                    let total = Math.round((totalLunch + totalDinner) * 100) / 100;
                    let totalPerPerson = Math.round(((totalLunch + totalDinner) / totalClients) * 100) / 100;

                    let color = "";
                    if(element.date.getDay() === 0 || element.date.getDay() === 6)
                    {
                        color = "weekend";
                    }
                    let lunchDinnerRowId = "lunchDinner" + element.lunch.id;
                    let row = `<tr class='dialogRow ${color}' data-id='${lunchDinnerRowId}' ${color}><td class='day text-capitalize'>${day}</td><td>${dateStr}</td><td>${totalPlate}</td><td>${totalPlatePerPerson}</td><td>${totalKitchen}</td><td>${totalKitchenPerPerson}</td><td>${total}</td><td>${totalPerPerson}</td><td>${totalClients}</td>`;
                    dinnerRows[lunchDinnerRowId] = `<tr id='${lunchDinnerRowId}'><td class='day text-capitalize'>${day}</td><td>${dateStr}</td><td>${plateLunch}</td><td>${plateDinner}</td><td>${kitchenLunch}</td><td>${kitchenDinner}</td><td>${totalLunch}</td><td>${totalDinner}</td><td>${clientsLunch}</td><td>${clientsDinner}</td></tr>`;
                    $body.append(row);
                }
            }
            /*data.forEach((element) => {
                let id = element.id;

                let plate = Math.round(element.platePrediction * 100) / 100 + " kg";
                let kitchen = Math.round(element.kitchenPrediction * 100) / 100 + " kg";
                let total = Math.round(element.totalPrediction * 100) / 100 + " kg";
                let client = Math.round(element.clientPrediction * 100) / 100;
                let platePerPerson = Math.round((element.platePerPerson * 1000) * 100) / 100 + " g";
                let kitchenPerPerson = Math.round((element.kitchenPerPerson * 1000) * 100) / 10 + " g";
                let totalPerPerson = Math.round((element.totalPerPerson * 1000) * 100) / 10 + " g";

                let randFactor = getRandomInt(5);
                let plateLunch = (Math.round(element.platePrediction * 100) / 100) / 2;
                let plateDinner = Math.round((plateLunch - randFactor) * 100) / 100;
                plateLunch += randFactor;
                plateLunch = Math.round(plateLunch * 100) / 100;

                let plateLunchPerPerson = plateLunch / client;
                let plateDinnerPerPerson = plateDinner / client;

                let kitchenLunch = (Math.round(element.kitchenPrediction * 100) / 100) / 2;
                let kitchenDinner = Math.round((kitchenLunch - randFactor) * 100) / 100;
                kitchenLunch += randFactor;
                kitchenLunch = Math.round(kitchenLunch * 100) / 100;

                let kitchenLunchPerPerson = Math.round((kitchenLunch / client) * 100) / 100;
                let kitchenDinnerPerPerson = Math.round((kitchenDinner / client) * 100) / 100;

                let totalLunch = Math.round((kitchenLunch + plateLunch) * 100) / 100;
                let totalLunchPerPerson = Math.round((totalLunch / client) * 100) / 100;
                let totalDinner = Math.round((kitchenDinner + plateDinner) * 100) / 100;
                let totalDinnerPerPerson = Math.round((totalDinner / client) * 100) / 100;

                let clientLunch = (client / 2) - randFactor;
                let clientDinner = (client / 2) + randFactor;

                let color = "";
                if(element.date.getDay() === 0 || element.date.getDay() === 6)
                {
                    color = "weekend";
                }
                let lunchDinnerRowId = "lunchDinner" + id;
                let row = `<tr class='dialogRow ${color}' data-id='${lunchDinnerRowId}' ${color}><td class='day text-capitalize'>${day}</td><td>${dateStr}</td><td>${plate}</td><td>${platePerPerson}</td><td>${kitchen}</td><td>${kitchenPerPerson}</td><td>${total}</td><td>${totalPerPerson}</td><td>${client}</td>`;
                dinnerRows[lunchDinnerRowId] = `<tr id='${lunchDinnerRowId}'><td class='day text-capitalize'>${day}</td><td>${dateStr}</td><td>${plateLunch}</td><td>${plateDinner}</td><td>${kitchenLunch}</td><td>${kitchenDinner}</td><td>${totalLunch}</td><td>${totalDinner}</td><td>${clientLunch}</td><td>${clientDinner}</td></tr>`;
                $body.append(row);
            });*/

            $('.dialogRow').each(function(){
                $(this).off('click');
                $(this).on('click', function(){
                    let id = $(this).data('id');
                    let row = dinnerRows[id];
                    let $lunchDinnerTable = $('#lunchDinnerTable');
                    $lunchDinnerTable.html("");
                    $lunchDinnerTable.append(row);

                    $('#dialogCover').fadeIn();
                });
            });
        }

        /** Fetch prediction data for a given date
         *
         * @returns {null | array}
         */
        function fetchPrediction(fetchDate){
            let predictionData = {};
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
                    $('#loadingCover').fadeOut();
                    let lines = data.split(/\r?\n|\r|\n/g);
                    lines.forEach(function(currentValue, index) {
                        if(index > 1) {
                            let split = currentValue.split(/\s+/);
                            let dateStr = split[0];
                            let date = new Date(split[0]);
                            let lunchType = parseInt(split[1]);
                            let platePrediction = split[2];
                            let kitchenPrediction = split[3];
                            let clientPrediction = split[4];

                            let predictionEntry = predictionData[dateStr];
                            if(!predictionEntry) {
                                predictionEntry = {
                                    "date": date,
                                    "day": date.toLocaleDateString('fi-FI', {weekday: "long"}),
                                    "lunch": {},
                                    "dinner": {}
                                }
                            }

                            if(lunchType === 1){
                                predictionEntry.lunch = {
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
                                predictionEntry.dinner = {
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
                            predictionData[dateStr] = predictionEntry;
                        }
                    });

                    setPredictionTable(predictionData);
                    //setPredictionChart(predictionData, chartColor, fontSize);
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

        .dialogRow{
            cursor: pointer;
        }
        .weekend td{
            background: rgba(31, 166, 75, 0.3) !important;
        }
        .weekend .day {
            color: red;
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

        #dialogCover{
            padding-top: 150px;
            width: 100%;
            height: 100%;
            position: fixed;
            left: 0;
            top: 50px;
            background: rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
<div id="dialogCover" class="p-5" style="display: none;">
    <div class="container-fluid p-5 bg-white">
        <h3 class="text-center">Lunch / Dinner division</h3>
        <div>
            <table class="table table-striped table-hover display">
                <thead>
                <tr>
                    <th colspan="2"></th><th colspan="2">Plate waste</th><th colspan="2">Kitchen waste</th><th colspan="2">Total waste</th><th colspan="2">Visitor prediction</th>
                </tr>
                <tr>
                    <th>Day</th><th>Date</th><th>Lunch</th><th>Dinner</th><th>Lunch</th><th>Dinner</th><th>Lunch</th><th>Dinner</th><th>Lunch</th><th>Dinner</th>
                </tr>
                </thead>
                <tbody id="lunchDinnerTable">

                </tbody>
            </table>
        </div>
    </div>
    <div class="container-fluid px-5 pb-3 bg-white text-end">
        <button class="btn btn-danger" type="button" onclick="$('#dialogCover').fadeOut()">Close</button>
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
                    <a href="#" class="nav-link active">Week's prediction</a>
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
            <h4>Weekly prediction for week <?php echo $t->format('W'); ?></h4>
        </div>
        <div class="col-12">
            <a class="btn btn-primary" href="weekly.php?date=<?php echo $lastWeek; ?>"><i class="bi bi-arrow-left"></i> Previous week</a>
            <a class="btn btn-primary" href="weekly.php?date=<?php echo $nextWeek; ?>"><i class="bi bi-arrow-right"></i> Next week</a>
        </div>
    </div>
    <div class="row my-3">
        <div class="col-12 col-xl-12 mb-3">
            <fieldset class="p-3 rounded-3 border border-1 border-black">
                <legend>Waste predictions</legend>
                <div class="overflow-x-auto">
                    <small>You can click on a row to see that days Lunch/Dinner -division.</small>
                    <table id="predictionTable" class="table table-striped table-hover" style="font-size: 0.85rem;">
                        <thead>
                        <tr>
                            <th></th><th></th><th colspan="2">Plate waste</th><th colspan="2">Kitchen waste</th><th colspan="2">Total waste</th><th></th>
                        </tr>
                        <tr>
                            <th>Day</th><th>Date</th><th>Total</th><th>Per person</th><th>Total</th><th>Per person</th><th>Total</th><th>Per person</th><th>Visitor prediction</th>
                        </tr>
                        </thead>
                        <tbody id="predictionTableBody">
                        <?php
                        $current_prediction = Tools::GetCurrentPrediction();
                        if(empty($current_prediction))
                            echo "<tr><td colspan='9'>Ei tämänhetkisiä ennusteita</td></tr>";
                        ?>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
    <div class="row mb-3 pagebreak">
        <div class="col-12 col-lg-6 p-3 pagebreak">
            <fieldset class="p-3 rounded-3 border border-1 border-black">
                <legend>Waste prediction chart <button type="button" data-type="bar" class="btn btn-primary bi bi-graph-up" id="btn_chartTogglePrediction"> Show linegraph</button></legend>
                <canvas id="predictionsChart" class="w-100"></canvas>
            </fieldset>
        </div>
        <div class="col-12 col-lg-6 p-3 pagebreak">
            <fieldset class="p-3 rounded-3 border border-1 border-black">
                <legend>Visitor prediction chart <button type="button" data-type="bar" class="btn btn-primary bi bi-graph-up" id="btn_chartToggleClient"> Show linegraph</button></legend>
                <canvas id="clientPredictionsChart" class="w-100"></canvas>
            </fieldset>
        </div>
    </div>
</main>
</body>
</html>

