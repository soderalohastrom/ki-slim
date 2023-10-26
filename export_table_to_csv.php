<?php
$fromExport = urldecode($_POST['export-data']);
//echo $fromExport;
$exportData = unserialize($fromExport);
//print_r($exportData);

//$filename = 'test_export.csv';
$filename = $_POST['export-filename'];

header( 'Content-Type: text/csv' );
header( 'Content-Disposition: attachment;filename='.$filename);
$fp = fopen('php://output', 'w');
//$fp = fopen('file.csv', 'w');
foreach ($exportData as $row) {
    fputcsv($fp, $row);
}
fclose($fp);


?>