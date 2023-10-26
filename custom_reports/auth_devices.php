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

$REPORT_ID = 113;

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
$SQL = "SELECT * FROM UsersDevices WHERE (UsersDevices_approveDate >='".$startEpoch."' AND UsersDevices_approveDate <= '".$enderEpoch."') ORDER BY UsersDevices_approveDate DESC";
$SND = $DB->get_multi_result($SQL);
if(!isset($SND['empty_result'])):
	foreach($SND as $DTA):
	$decodeInfo = json_decode($DTA['UsersDevices_device'], true);
	
	?>
    <tr>
    	<td><?php echo date("m/d/y h:ia", $DTA['UsersDevices_approveDate'])?></td>
        <td><?php echo $RECORD->get_FulluserName($DTA['UsersDevices_userId'])?></td>
        <td><?php echo $decodeInfo['DEVICE']?></td>
        <td><?php echo $decodeInfo['BROWSER_TYPE']?> <?php echo $decodeInfo['BROWSER_VERSION']?></td>
        <td><?php echo $decodeInfo['OS']?></td>        
        <td><?php echo $decodeInfo['IP_ADDRESS']?></td>
        <td><?php echo $decodeInfo['REVERSE_IP_LOOKUP']?></td>
    </tr>
    <?php
	endforeach;
endif;
$tbody = ob_get_clean();
?>


<table class="table table-bordered m-table m-datatable">
<thead class="thead-inverse">
	<tr>
    	<th>Approve Date</th>
        <th>User</th>
        <th>Device</th>
        <th>Browser</th>
        <th>OS</th>
        <th>IP</th>
        <th>Provider</th>
	</tr>
</thead>
<tbody>
<?php echo $tbody?>
</tbody>
</table>
<script>
var DatatableHtmlTable = function() {
    var e = function() {
        $(".m-datatable").mDatatable({
            search: {
                input: $("#generalSearch")
            },
            columns: [{
				field: "Device",
				width: 75
			},{
				field: "Browser",
				width: 100
			},{
				field: "OS",
				width: 125
			},{
				field: "IP",
				width: 125
			},{
                field: "Provider",
                width: 300
            }]
        })
    };
    return {
        init: function() {
            e()
        }
    }
}();
jQuery(document).ready(function() {
	DatatableHtmlTable.init();		
});
</script>            





