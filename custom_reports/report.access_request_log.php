<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.sales.php");
include_once("class.encryption.php");
include_once("class.sessions.php");
$DB = new database();
$DB->connect();

$RECORD = new Record($DB);
$SALES = new Sales($DB);
$ENC = new encryption();
$SESSION = new Session($DB, $ENC);

$REPORT_ID = 192;

function checkIfGranted($user_id, $person_id) {
	global $DB;
	$sql = "SELECT * FROM PersonRecordShares WHERE Person_id='".$person_id."' AND user_id='".$user_id."'";
	$snd = $DB->get_single_result($sql);
	if($snd['empty_result'] == 1):
		return false;
	else:
		return true;
	endif;	
}

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
	
	$_POST['filterDates'] = date("m/d/Y", $startEpoch).' - '.date("m/d/Y", $enderEpoch);
	//echo "Default:".$startEpoch."|".$enderEpoch;
	$sql = "SELECT * FROM Offices ORDER BY Offices_id";
	$snd = $DB->get_multi_result($sql);
	foreach($snd as $dta):
		$offices[] = $dta['Offices_id'];
	endforeach;
	$dateDaysPreload = 30;
}

ob_start();
$SQL = "SELECT * FROM RecordAwakenRequests WHERE (Request_Date >='".$startEpoch."' AND Request_Date <= '".$enderEpoch."') ORDER BY Request_Date DESC";
$SND = $DB->get_multi_result($SQL);
if(!isset($SND['empty_result'])):
	foreach($SND as $DTA):
	?>
    <tr>
    	<td><?php echo date("m/d/y h:ia", $DTA['Request_Date'])?></td>
        <td><?php echo $RECORD->get_FulluserName($DTA['Request_From'])?></td>        
        <td>
        	<a href="/profile/<?php echo $DTA['Request_PersonID']?>" class="m-link"><?php echo $RECORD->get_personName($DTA['Request_PersonID'])?></a>
			<sup><a href="/profile/<?php echo $DTA['Request_PersonID']?>" target="_blank"><i class="fa fa-external-link"></i></a></sup>
        </td>        
        <td><?php echo $RECORD->get_FulluserName($DTA['Request_To'])?></td>
        <td><?php echo ((checkIfGranted($DTA['Request_From'], $DTA['Request_PersonID']))? '<span class="m-badge m-badge--success m-badge--wide">Granted <i class="fa fa-check"></i></span>':'<span class="m-badge m-badge--danger m-badge--wide">Pending <i class="fa fa-times"></i></span>')?></td>
    </tr>
    <?php
	endforeach;
endif;
$tbody = ob_get_clean();
?>


<table class="table table-bordered m-table table-hover">
<thead class="thead-inverse">
	<tr>
    	<th>Requst Date</th>
        <th>Request By</th>
        <th>To View Record</th>
        <th>Record Owner</th>
        <th>Status</th>
	</tr>
</thead>
<tbody>
<?php echo $tbody?>
</tbody>
</table>           





