<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);

$PAID_PER_MATCH = 50.00;

function introResultShow($score) {
	if($score < 7) {
		$icon_class = 'la-thumbs-down';
	} else {
		$icon_class = 'la-thumbs-up';
	}
	
	if($score < 3) {
		$color_class = 'm-badge--danger';
	} elseif($score < 5) {
		$color_class = 'm-badge--warning';
	} elseif($score < 7) {
		$color_class = 'm-badge--primary';
	} else {
		$color_class = 'm-badge--success';
	}
	
	ob_start();
	?><span class="m-badge <?php echo $color_class?> m-badge--wide"><?php echo $score?> <i class="la <?php echo $icon_class?>"></i></span><?php
	return ob_get_clean();
}
//print_r($_POST);

if (isset($_POST['filterDates'])) {
	$dateParts = explode(" - ", $_POST['filterDates']);
	$startEpoch = strtotime($dateParts[0]);
	$enderEpoch = strtotime($dateParts[1]) + 86399;
	//echo "Custom:".$startEpoch."|".$enderEpoch;
	$dateParameters = array($startEpoch, $enderEpoch);	
	$dayDiff = (($enderEpoch - $startEpoch) / 86400) - 1;
	$dateDaysPreload = round($dayDiff);	
	$offices = $_POST['Offices_id'];		
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
SELECT
	*
FROM
	PersonsDates
WHERE
	(PersonsDates_dateExecuted >= '".$startEpoch."' AND PersonsDates_dateExecuted <= '".$enderEpoch."')
AND
	PersonsDates_isComplete='1'
";


$SQL .= "ORDER BY
	PersonsDates_dateExecuted
ASC
";
//echo $SQL;
$SND = $DB->get_multi_result($SQL);

ob_start();
foreach($SND as $DTA):
$score = round(($DTA['PersonsDates_participant_1_rank'] + $DTA['PersonsDates_participant_2_rank']) / 2, 1);
if($score >= 7) {
	$COMMISSIONS[$DTA['PersonsDates_assignedTo']][] = $PAID_PER_MATCH;
	$TOTAL[] = $PAID_PER_MATCH;
}
?>
<tr>	
    <td>
    	<a href="/intro/<?php echo $DTA['PersonsDates_id']?>" class="btn btn-sm btn-secondary"><i class="fa fa-heart"></i></a>
        <a href="/intro/<?php echo $DTA['PersonsDates_id']?>" target="_blank" class="btn btn-sm btn-secondary"><i class="la la-external-link-square"></i></a>
	</td>
    <td><?php echo $RECORD->get_personName($DTA['PersonsDates_participant_1'])?></td>
    <td><?php echo $RECORD->get_personName($DTA['PersonsDates_participant_2'])?></td>
    <td><?php echo $RECORD->get_FulluserName($DTA['PersonsDates_assignedTo'])?></td>
    <td><?php echo introResultShow($score)?></td>
    <td><?php echo (($score >= 7)? number_format($PAID_PER_MATCH, 2):'&nbsp;')?></td>
</tr>
<?php
endforeach;
$TBODY = ob_get_clean();

//print_r($COMMISSIONS);
?>
<script src="/assets/vendors/custom/tablesorter/dist/js/jquery.tablesorter.min.js" type="text/javascript"></script>
<link href="/assets/vendors/custom/tablesorter/dist/css/theme.bootstrap_4.min.css" rel="stylesheet" type="text/css" />
<?php

if(isset($COMMISSIONS)):
$mmListArray = array_keys($COMMISSIONS);
?>
<div class="m-portlet">
    <div class="m-portlet__body" style="padding: 1.2rem 1.2rem;">
    	<h5>Matchmaking Commissioned Earned <small><?php echo $_POST['filterDates']?></small></h5>
		<div class="row">
<?php
for($key = 0; $key < count($mmListArray); $key++):
	$uID = $mmListArray[$key];
	?>
    <div class="col-4">
    	<div class="row">
        	<div class="col-8"><?php echo $RECORD->get_userName($uID)?></div>
            <div class="col-4 text-right"><strong>$<?php echo number_format(array_sum($COMMISSIONS[$uID]))?></strong></div>
        </div>
    </div>
	<?php
endfor;
?>            
    	</div>
    </div>
</div>
<?php
endif;


?>
<table class="table tablesorter" id="statsTable">
<thead>
	<tr>
    	<th width="10%">&nbsp;</th>
        <th>Participant #1</th>
        <th>Participant #2</th>
        <th>Matchmkaker</th>
        <th>Intro Score</th>
        <th>Result <small>(Min Score 7)</small></th>
	</tr>
</thead>
<tbody>
	<?php echo $TBODY?>
</tbody>
<tfoot>
	<tr>
    	<th colspan="5" class="text-right">TOTAL:</th>
        <th><?php echo @number_format(array_sum($TOTAL), 2)?></th>
	</tr>        
</table>
<script>
$(document).ready(function(e) {
    $("#statsTable").tablesorter();
});	
</script>                






