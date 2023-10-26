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
	*
FROM
	Persons
WHERE
	BioApprovedDate >= '".$startEpoch."' AND BioApprovedDate <= '".$enderEpoch."'
ORDER BY
	BioApprovedDate DESC
";
//echo $SQL."<br>\n";
$SND = $DB->get_multi_result($SQL);
if(!isset($SND['empty_result'])) {
	?>
    <table class="table table-striped m-table" id="RecordList">
    <thead>
        <tr>
            <th>Lead/Client</th>
            <th>Date Approved</th>
            <th>Approved By</th>
            <th>&nbsp;</th>
        </tr>
	</thead>
    <tbody>
    <?php
	foreach($SND as $DTA) {
		?>
        <tr>
        	<td><a href="/profile/<?php echo $DTA['Person_id']?>" class="m-link" style="color:#333;" target="_blank"><?php echo $DTA['FirstName']?> <?php echo $DTA['LastName']?> <i class="fa fa-share"></i></a></td>
            <td><?php echo date("m/d/y h:ia", $DTA['BioApprovedDate'])?></td>
            <td><?php echo $RECORD->get_userName($DTA['BioApproveBy'])?></td>
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