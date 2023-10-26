<?php
session_start();
include_once("../class.db.php");
include_once("../class.record.php");

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);


//print_r($_POST);

if (isset($_GET['start'])) {
	$startEpoch = $dateParts[0];
	$enderEpoch = $dateParts[1];
	$dayDiff = (($enderEpoch - $startEpoch) / 86400) - 1;
	$dateDaysPreload = round($dayDiff);		
} else {
	$dateParameters = array((time() - (30 * 86400)), time());
	$startEpoch = time() - (30 * 86400);
	$enderEpoch = time();
	//echo "Default:".$startEpoch."|".$enderEpoch;
	$sql = "SELECT * FROM Offices ORDER BY Offices_id";
	$snd = $DB->get_multi_result($sql);
	foreach($snd as $dta):
		$offices[] = $dta['Offices_id'];
	endforeach;
	$dateDaysPreload = 30;
	$_POST['filterDates'] = date("m/d/Y", $startEpoch).' - '.date("m/d/Y", $enderEpoch);
}


$SQL = "
SELECT `report_matchmaker_dates`.`PersonsDates_id`,
    `report_matchmaker_dates`.`Participant1`,
    `report_matchmaker_dates`.`Participant1_Type`,
    `report_matchmaker_dates`.`Participant2`,
    `report_matchmaker_dates`.`Participant2_Type`,
    `report_matchmaker_dates`.`Date_Location`,
    `report_matchmaker_dates`.`Date_Status`,
    `report_matchmaker_dates`.`RelationshipManager`,
    `report_matchmaker_dates`.`NetworkDeveloper`,
    `report_matchmaker_dates`.`Participant1_Disposition`,
    `report_matchmaker_dates`.`Participant2_Disposition`,
    `report_matchmaker_dates`.`Date_Created`,
    `report_matchmaker_dates`.`Date_Completed`,
    `report_matchmaker_dates`.`PersonsDates_isComplete`,
    `report_matchmaker_dates`.`Next Action On` AS `Next_Action_On`
FROM `report_matchmaker_dates`

WHERE 1=1
";

if ($_GET['start']) {
	$SQL .= "AND `report_matchmaker_dates`.`Next Action On` BETWEEN '".$_GET['start'] .
	"' and '" . $_GET['end'] . "' ";
}
else {
	$SQL .= "AND `report_matchmaker_dates`.`Next Action On` > NOW()-30 ";
}

if (isset($_GET['statuses']) && $_GET['statuses'] != 'All') {

	$SQL .= "AND `report_matchmaker_dates`.`Date_Status` = '".$_GET['statuses'] ."' ";
}

if (isset($_GET['hideincomplete'])  && $_GET['hideincomplete'] == 'true' ) {
	$SQL .= "AND `report_matchmaker_dates`.`PersonsDates_isComplete` = 1 ";
}

$SQL .= "ORDER BY
`Next Action On`
DESC
";

$SND = $DB->get_multi_result($SQL);

foreach($SND as $row) {
    $myArray[] = $row;
}
echo json_encode($myArray, JSON_PRETTY_PRINT);


