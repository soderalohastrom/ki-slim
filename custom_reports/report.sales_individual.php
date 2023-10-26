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

$REPORT_ID = 182;

function getAppointmentsCount($user_id, $peak, $floor) {
	global $DB;
	$sql = "
	SELECT
		count(*) as count
	FROM
		Appointments
		INNER JOIN Persons ON Persons.Person_id=Appointments.Appointments_personId
	WHERE
		1
	AND
		(Appointments.Appointments_time >= '".$floor."' AND Appointments.Appointments_time <= '".$peak."')
	AND
		Appointments.Appointments_userId='".$user_id."'	
	";
	$snd = $DB->get_single_result($sql);
	return $snd['count'];	
}

function getSalesCount($user_id, $peak, $floor) {
	global $DB;
	$sql = "
	SELECT
		*
	FROM
		PersonsSales
		INNER JOIN PersonsSalesCommissions ON PersonsSalesCommissions.PersonsSales_PersonsSales_id=PersonsSales.PersonsSales_id
	WHERE
		1
	AND
		(PersonsSales.PersonsSales_dateCreated <= '".$peak."' AND PersonsSales.PersonsSales_dateCreated >= '".$floor."')
	AND
		PersonsSalesCommissions.Users_user_id='".$user_id."'
	GROUP BY
		PersonsSales.PersonsSales_id	
	";
	//echo $s_sql;
	$snd = $DB->get_multi_result($sql);	
	if(!isset($snd['empty_result'])):
		foreach($snd as $dta):
			$sale_amount[] = $dta['PersonsSales_payment'];		
		endforeach;
	endif;
	$return['count'] = count($snd);
	$return['dollars'] = array_sum($sale_amount);
	$return['per_sale'] = round(($return['dollars'] / $return['count']), 2);
	return $return;
}

function get_all_sale_dollars($peak, $floor) {
	global $DB;
	$sql = "
	SELECT
		*
	FROM
		PersonsSales
		INNER JOIN PersonsSalesCommissions ON PersonsSalesCommissions.PersonsSales_PersonsSales_id=PersonsSales.PersonsSales_id
	WHERE
		1
	AND
		(PersonsSales.PersonsSales_dateCreated <= '".$peak."' AND PersonsSales.PersonsSales_dateCreated >= '".$floor."')
	GROUP BY
		PersonsSales.PersonsSales_id	
	";
	//echo $s_sql;
	$snd = $DB->get_multi_result($sql);	
	if(!isset($snd['empty_result'])):
		foreach($snd as $dta):
			$sale_amount[] = $dta['PersonsSales_payment'];		
		endforeach;
	endif;
	$return['count'] = count($snd);
	$return['dollars'] = array_sum($sale_amount);
	$return['per_sale'] = round(($return['dollars'] / $return['count']), 2);
	return $return;
	
}

if (isset($_POST['filterDates'])) {
	$dateParts = explode(" - ", $_POST['filterDates']);
	$startEpoch = strtotime($dateParts[0]);
	$enderEpoch = strtotime($dateParts[1]) + 86399;
	//echo "Custom:".$startEpoch."|".$enderEpoch;
	$dateParameters = array($startEpoch, $enderEpoch);	
	$dayDiff = (($enderEpoch - $startEpoch) / 86400) - 1;
	$dateDaysPreload = round($dayDiff);		
} else {
	$reportEpoch = time();
	$startEpoch = mktime(0, 0, 0, date("m", $reportEpoch), 1, date("Y", $reportEpoch));
	$enderEpoch = mktime(0, 0, 0, date("m", $reportEpoch), date("t", $reportEpoch), date("Y", $reportEpoch));	
	$_POST['filterDates'] = date("m/d/Y", $startEpoch).' - '.date("m/d/Y", $enderEpoch);
	$dateDaysPreload = 30;
}

$scu_sql = "
SELECT
	DISTINCT(Users_user_id)
FROM
	PersonsSales
	INNER JOIN PersonsSalesCommissions ON PersonsSalesCommissions.PersonsSales_PersonsSales_id=PersonsSales.PersonsSales_id
WHERE
	1
AND
	(PersonsSales.PersonsSales_dateCreated <= '".$enderEpoch."' AND PersonsSales.PersonsSales_dateCreated >= '".$startEpoch."')
";
//echo $scu_sql."<br>\n";
$scu_snd = $DB->get_multi_result($scu_sql);
ob_start();
$all_sales = get_all_sale_dollars($enderEpoch, $startEpoch);
if(!isset($scu_snd['empty_result'])):
	foreach($scu_snd as $scu_dta):		
		$appt	=	getAppointmentsCount($scu_dta['Users_user_id'], $enderEpoch, $startEpoch);
		$sales	=	getSalesCount($scu_dta['Users_user_id'], $enderEpoch, $startEpoch);		
		if($appt == 0) {
			$dolper = 0;
		} else {
			$dolper	=	round(($sales['dollars']/$appt), 2);
		}
		$dolperc	= round((($sales['dollars'] / $all_sales['dollars']) * 100), 2);
		$newsql = "SELECT *, CONCAT(FirstName,' ',LastName) as Name FROM PersonsSales INNER JOIN Persons ON Persons.Person_id=PersonsSales.Persons_Person_id INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id INNER JOIN PersonsSalesCommissions ON PersonsSalesCommissions.PersonsSales_PersonsSales_id=PersonsSales.PersonsSales_id WHERE (PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales_dateCreated <= '".$enderEpoch."') AND PersonsSalesCommissions.Users_user_id='".$scu_dta['Users_user_id']."'";
		?>
		<tr>
			<td><a href="javascript:;" onclick="previewRecords('Sales for <?php echo $RECORD->get_FulluserName($scu_dta['Users_user_id'])?> between <?php echo $_POST['filterDates']?>','<?php echo $ENC->encrypt($newsql)?>')"><?php echo $RECORD->get_FulluserName($scu_dta['Users_user_id'])?></a></td>
			<td class="text-center"><?php echo $appt?></td>
			<td class="text-center"><?php echo $sales['count']?></td>
			<td class="text-right"><?php echo number_format($sales['dollars'], 2)?></td>
			<td class="text-right"><?php echo number_format($sales['per_sale'], 2)?></td>
			<td class="text-right"><?php echo number_format($dolper, 2)?></td>
			<td class="text-right"><?php echo $dolperc?>%</td>
		</tr>    
		<?php
	endforeach;
endif;
$tbody = ob_get_clean();


?>
<form action="/viewreport/<?php echo $REPORT_ID?>" method="post">
<div class="row" style="margin-bottom:10px;">
	<div class="col-8">&nbsp;</div>
    <div class="col-4">
    	<div class="input-group m-input-group">					
            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-calendar"></i></span>
            <input type="text" class="form-control m-input input-sm" id="filterDates" name="filterDates" placeholder="dates" autocomplete="off" value="<?php echo $_POST['filterDates']?>">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-default">Apply Filters</button>
            </span>
        </div>
    </div>
</div>
</form>
<table class="table table-striped m-table table-sm">
<thead>
	<tr>
    	<th>User</th>
        <th class="text-center"># of Apt.</th>
        <th class="text-center"># of Sales</th>
        <th class="text-right">$ Dollars</th>
        <th class="text-right">Dollars/Sale</th>
        <th class="text-right">Dollars/Apt</th>
        <th class="text-right">Dollar %</th>
	</tr>
</thead>
<tbody>
<?php echo $tbody?>
</tbody>
<tfoot>
	<tr>
    	<td colspan="2">&nbsp;</td>
        <td class="text-center m--font-boldest"><?php echo $all_sales['count']?></td>
        <td class="text-right m--font-boldest"><?php echo number_format($all_sales['dollars'], 2)?></td>        
        <td colspan="2">&nbsp;</td>
        <td class="text-right">100%</td>
	</tr>        
</tfoot>
</table>

<div class="modal fade" id="metricsDetailsModal" tabindex="-1" role="dialog" aria-labelledby="metricsDetailsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="metricsDetailsModalLabel"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div> 

<script>
//var start = moment().subtract(<?php echo $dateDaysPreload?>, 'days');
//var end = moment();	
$('#filterDates').daterangepicker({
	buttonClasses: 'm-btn btn',
	applyClass: 'btn-primary',
	cancelClass: 'btn-secondary',
	//startDate: start,
	//endDate: end,
	ranges: {
		'Today'			: [moment().subtract(1, 'days'), moment()],
		'This Week'		: [moment().startOf('week'), moment().endOf('week')],
		'This Month'	: [moment().startOf('month'), moment().endOf('month')],
		'Last 7 Days': [moment().subtract(7, 'days'), moment()],
		'Last 30 Days': [moment().subtract(30, 'days'), moment()],
		'Last 60 Days': [moment().subtract(60, 'days'), moment()],
		'Last 90 Days': [moment().subtract(90, 'days'), moment()],
		'Last 6 Months': [moment().subtract(6, 'months'), moment()],
		'Last 12 Months': [moment().subtract(12, 'months'), moment()],
		'Year to Date': [moment('<?php echo date("c", mktime(0,0,0,1,1,date("Y")))?>'), moment()],
		'This Month': [moment().startOf('month'), moment().endOf('month')],
		'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
		'Last Year to Date': [moment('<?php echo date("c", mktime(0,0,0,1,1,(date("Y") - 1)))?>'), moment('<?php echo date("c", mktime(0,0,0,12,31,(date("Y") - 1)))?>')],
	}
});
function previewRecords(modalTitle, modalSQL) {
	$('#metricsDetailsModal').modal('show');
	$('#metricsDetailsModalLabel').html(modalTitle);
	mApp.block("#metricsDetailsModal .modal-body", {
		overlayColor: "#000000",
		type: "loader",
		state: "primary",
		message: "Loading..."
	});
	$.post('/ajax/smr.php?action=getDetails', {
		sql: modalSQL,
		kiss_token: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$("#metricsDetailsModal .modal-body").html(data);
		mApp.unblock("#metricsDetailsModal .modal-body");
		mApp.init();	
	});
}
</script>            














