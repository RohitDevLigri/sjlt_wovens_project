<?php
include_once 'dbconnect.php';

// Create a new connection instance
$db = new Connection();
$dbo = $db->getConnection(); // Use getConnection() instead of openConnection()

$now = date('Y-m-d');
$month = date("m", strtotime($now));
$MONTH = date('M');
$year = date("Y", strtotime($now));

$d = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$first = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
$last = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

$thisTime = strtotime($first);
$endTime = strtotime($last);
$thisDate = [];

while ($thisTime <= $endTime) {
    $thisDate[] = date('Y-m-d', $thisTime);
    $thisTime = strtotime('+1 day', $thisTime); // increment for loop
}

$total = [];
for ($i = 0; $i < $d; $i++) {
    $ldate = $thisDate[$i];
    $totalqueryA = "SELECT SUM(`Production_report`.`Picks`) AS TotalPicks FROM Production_report WHERE Date = '$ldate'";
    $totalstmtA = $dbo->prepare($totalqueryA);
    $totalstmtA->execute();
    $totalstmtA->setfetchmode(PDO::FETCH_ASSOC);
    $totalresultA = $totalstmtA->fetchAll();
    $totalA = 0;
    foreach ($totalresultA as $row) {
        $totalA = isset($row['TotalPicks']) ? $row['TotalPicks'] : 0; // Handle null values
    }
    $total[] = $totalA / 1000000;
}
?>