<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.sales.php");
$DB = new database();
$DB->connect();

$RECORD = new Record($DB);
$SALES = new Sales($DB);

//print_r($_POST);

if (isset($_POST['filterDates'])) {
	$dateParts = explode(" - ", $_POST['filterDates']);
	$startEpoch = strtotime($dateParts[0]);
	$enderEpoch = strtotime($dateParts[1]) + 86399;
	//echo "Custom:".$startEpoch."|".$enderEpoch;
	$dateParameters = array($startEpoch, $enderEpoch);
	
	$dayDiff = (($enderEpoch - $startEpoch) / 86400) - 1;
	$dateDaysPreload = round($dayDiff);			
} else {
	$dateParameters = array((time() - (30 * 86400)), time());
	$startEpoch = time() - (30 * 86400);
	$enderEpoch = time();
	//echo "Default:".$startEpoch."|".$enderEpoch;
}

$SQL = "
SELECT 
	Persons.Person_id,
	Persons.FirstName,
	Persons.LastName,
	PersonsContract.Contract_id,
	PersonsContract.Contract_rep,
	PersonsContract.Contract_dateEntered,
	PersonsContract.Contract_Hash,
	PersonsContract.Contract_RetainerFee,
	PersonsContract.Contract_fileID,
	(SELECT ContractHistory_date FROM PersonsContractsHistory WHERE PersonsContractsHistory.Contract_id=PersonsContract.Contract_id ORDER BY ContractHistory_date DESC LIMIT 1) as LastView
FROM 
	PersonsContract 
	INNER JOIN Persons ON Persons.Person_id=PersonsContract.Person_id
WHERE
	PersonsContract.Contract_status = '2'
HAVING
	(LastView >= '".$startEpoch."' AND LastView <= '".$enderEpoch."')
ORDER BY
	LastView DESC
";
$SND = $DB->get_multi_result($SQL);
if(!isset($SND['empty_result'])) {
	?>
    <table class="table table-striped m-table" id="RecordList">
    <thead>
        <tr>
            <th>Lead/Client</th>
            <th>Retainer</th>
            <th>Date Created</th>
            <th>Rep</th>
            <th>Signed</th>
        </tr>
	</thead>
    <tbody>
    <?php
	foreach($SND as $DTA) {
		?>
        <tr>
        	<td>
            	<a href="/profile/<?php echo $DTA['Person_id']?>" class="m-link" style="color:#333;" target="_blank"><?php echo $DTA['FirstName']?> <?php echo $DTA['LastName']?>&nbsp;
                <?php if($DTA['Contract_fileID'] != '0'): ?>
                <a href="/getFile.php?DID=<?php echo $DTA['Contract_fileID']?>" title="View Internal Contract" target="_blank" style="color:#333;"><i class="flaticon-list-2"></i></a>
                <?php endif; ?>
                <!--<a href="https://<?php echo $_SERVER['SERVER_NAME']?>/view-contract.php?id=<?php echo $DTA['Contract_Hash']?>" title="View External Contract" class="m--font-warning" target="_blank"><i class="flaticon-interface-4"></i></a>&nbsp;-->
			</td>
            <td><?php echo number_format($DTA['Contract_RetainerFee'], 2)?></td>
            <td><?php echo date("m/d/y h:ia", $DTA['Contract_dateEntered'])?></td>
            <td><?php echo $RECORD->get_userName($DTA['Contract_rep'])?></td>
            <td><?php echo (($DTA['LastView'] == '')? '':date("m/d/y h:ia", $DTA['LastView']))?></td>
		</tr>
        <?php	
	}
	?>
    </tbody>
    </table>
    <?php
} else {
		
}
?>
<script>
$("#RecordList").mDatatable(
	{
		search: {
		input: $("#generalSearch")
	},
	columns: [{
		field: "Matches",
		type: "number"
	},{
		field: "#",
		width: 30
	}],
	footer: false,
	pagination: false,
	data: {
		saveState: {
			cookie: false,
			webstorage: false
		}
	}
});
</script>