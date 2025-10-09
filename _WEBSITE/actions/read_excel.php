<?php
require_once ('../vendor/autoload.php');
require_once ('../classes/tools.class.php');
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;

$d = new DateTime();
$currentDate = $d->format('YmdHi');

$file = $_FILES['toteuma'];

$basePath = "../files/$currentDate/";
if(!is_dir($basePath)){
    mkdir($basePath);
}
$fileName = $file['name'];
$filePath = $basePath . $fileName;

$data = false;

if(move_uploaded_file($file['tmp_name'], $filePath)){
    $reader = new Reader();
    $reader->open($filePath);

    $data = [];
    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
            if($rowIndex > 2)
            {
                $cells = $row->getCells();

                $day = $cells[0]->getValue();
                $date = $cells[1]->getValue();
                $dateStr = $date->format('j.n.Y');
                $lunchTisma = $cells[2]->getValue();
                $lunchRealization = $cells[3]->getValue();
                $dinnerTisma = $cells[4]->getValue();
                $dinnerRealization = $cells[5]->getValue();

                $newData = [
                    "day" => $day,
                    "date" => $dateStr,
                    "lunchTisma" => $lunchTisma,
                    "lunchRealization" => $lunchRealization,
                    "dinnerTisma" => $dinnerTisma,
                    "dinnerRealization" => $dinnerRealization,
                ];
                $data[] = $newData;
            }
        }
    }
}
$xlsxFilePath = $basePath . "KSM_varaukset_toteuma.xlsx";

if($data)
{
    $columnTitles = [
        "Date",
        "Restaurant",
        "Reservation",
        "Realization",
        "LunchType"
    ];
    $writer = new \OpenSpout\Writer\XLSX\Writer();

    $writer->openToFile($xlsxFilePath);

    $rowFromValues = Row::fromValues($columnTitles);
    $writer->addRow($rowFromValues);

    foreach($data as $entry)
    {
        $lunchData = [
            $entry["date"],
            "Eppula",
            $entry["lunchTisma"],
            $entry["lunchRealization"],
            "Lounas"
        ];
        $dinnerData = [
            $entry["date"],
            "Eppula",
            $entry["dinnerTisma"],
            $entry["dinnerRealization"],
            "Päivällinen"
        ];
        $lunchRow = Row::fromValues($lunchData);
        $dinnerRow = Row::fromValues($dinnerData);
        $writer->addRow($lunchRow);
        $writer->addRow($dinnerRow);
    }
    $writer->close();
}

if(file_exists($xlsxFilePath))
{
    echo "<a href='$xlsxFilePath' download>Lataa tiedosto</a><br>";
}

