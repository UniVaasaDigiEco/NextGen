<?php
$path_to_config = dirname(__FILE__) ."/../config/_config.php";
require_once($path_to_config);

class Tools{
    static function GetDB($host = DB_HOST, $user = DB_USER, $pass = DB_PASS, $db_name = DB_NAME):mysqli
    {
        return new mysqli($host, $user, $pass, $db_name);
    }

    static function ShowError($error): void
    {
        echo "<div class='alert alert-danger alert-dismissible fade show mx-3' role='alert'>";
        echo "<i class='bi bi-exclamation-circle-fill'></i><strong>$error</strong>";
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo "</div>";
    }

    static function ShowMessage($message): void
    {
        echo "<div class='alert alert-success alert-dismissible fade show mx-3' role='alert'>";
        echo "<i class='bi bi-exclamation-circle-fill'></i><strong>$message</strong>";
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo "</div>";
    }

    static function GetCurrentPrediction(): array
    {
        $db = self::GetDB();

        $data = [];

        $sql = "SELECT id, prediction_id, prediction_date, prediction_kitchen, prediction_plate, prediction_client FROM prediction ORDER BY prediction_date";
        $stmt = $db->prepare($sql);
        $stmt->bind_result($id, $pred_id, $pred_date, $pred_kitch, $pred_plate, $pred_client);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0)
        {
            while($stmt->fetch()){
                $el = [
                    "row_id" => $id,
                    "prediction_id" => $pred_id,
                    "prediction_date" => $pred_date,
                    "prediction_kitchen" => $pred_kitch,
                    "prediction_plate" => $pred_plate,
                    "prediction_client" => $pred_client
                ];
                $data[] = $el;
            }
        }

        return $data;
    }
}