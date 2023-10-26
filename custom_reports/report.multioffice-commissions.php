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

$REPORT_ID = 119;

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
	//$dateParameters = array((time() - (30 * 86400)), time());
	//$startEpoch = time() - (30 * 86400);
	//$enderEpoch = time();
	
	$reportEpoch = time();
	$startEpoch = mktime(0, 0, 0, date("m", $reportEpoch), 1, date("Y", $reportEpoch));
	$enderEpoch = mktime(0, 0, 0, date("m", $reportEpoch), date("t", $reportEpoch), date("Y", $reportEpoch));
	
	$_POST['filterDates'] = date("m/d/Y", $startEpoch).' - '.date("m/d/Y", $enderEpoch);
	//echo "Default:".$startEpoch."|".$enderEpoch;
	$sql = "SELECT * FROM Offices ORDER BY Offices_id";
	$snd = $DB->get_multi_result($sql);
	foreach($snd as $dta):
		$offices[] = $dta['Offices_id'];
	endforeach;
	$dateDaysPreload = 30;
}
?>
<script src="/assets/vendors/custom/tablesorter/dist/js/jquery.tablesorter.min.js" type="text/javascript"></script>
<link href="/assets/vendors/custom/tablesorter/dist/css/theme.bootstrap_4.min.css" rel="stylesheet" type="text/css" />
<style>
.sale-data-field {
	font-weight:bold;
}
.paymentBlock {
	padding-bottom:10px;
	margin-bottom:10px;
	border-bottom:#EAEAEA solid 1px;
}

</style>
<form action="/viewreport/<?php echo $REPORT_ID?>" method="post">
<div class="row" style="margin-bottom:10px;">
	<div class="col-8">
    	<small>This report includes all PAYMENTS that have been PAID within the time scope of the report.</small>
    </div>
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

<table class="table table-bordered m-table">
<thead class="thead-inverse">
	<tr>
    	<th>Salesperson</th>
        <th width="100" class="text-center">Total<br />Sales</th>
        <th width="100" class="text-center">Total<br />Commissions</th>
        <th width="100" class="text-center">Percent<br />Collected</th> 
        <th width="100" class="text-center">Total<br />Commissions %</th>       
        <!-- <th width="100" class="text-center">Total<br />Unprocessed</th> -->
        <!-- <th width="100" class="text-center">Total<br />Processed</th> -->
        <th width="100" class="text-center">Total<br />Paid</th>
        
	</tr>        
</thead>
<tbody>

<?php
//foreach($offices as $office):
	ob_start();
	$s_sql = "
	SELECT 
		DISTINCT(Users_user_id) 
	FROM 
		PersonsSalesCommissions
		INNER JOIN PersonsSales ON PersonsSales.PersonsSales_id=PersonsSalesCommissions.PersonsSales_PersonsSales_id		 
		LEFT JOIN PersonsContract ON PersonsContract.Contract_id=PersonsSales.PersonsSales_ContractID
		LEFT JOIN PersonsPaymentInfo ON PersonsPaymentInfo.Contract_id=PersonsSales.PersonsSales_ContractID
	WHERE 
		(
		(PersonsPaymentInfo.PaymentInfo_Execute >= '".$startEpoch."' AND PersonsPaymentInfo.PaymentInfo_Execute <= '".$enderEpoch."')
		)
	GROUP BY 
		PersonsSales_id 
	ORDER BY 
		PersonsSales_dateCreated ASC
	";
	//echo $s_sql."<br>\n";
	$s_snd = $DB->get_multi_result($s_sql);	
	if(isset($s_snd['empty_result'])):
		$skipLocation = true;
	else:
		$skipLocation = false;
		foreach($s_snd as $s_dta):
			$com_sql = "
			SELECT 
				PersonsSalesCommissions.*,
				PersonsPaymentInfo.*,
				PersonsSales.PersonsSales_payment,
				PersonsSales.PersonsSales_id,
				PersonsSales.Persons_Person_id
			FROM 
				PersonsSalesCommissions
				INNER JOIN PersonsSales ON PersonsSales.PersonsSales_id=PersonsSalesCommissions.PersonsSales_PersonsSales_id		 
				LEFT JOIN PersonsContract ON PersonsContract.Contract_id=PersonsSales.PersonsSales_ContractID
				LEFT JOIN PersonsPaymentInfo ON PersonsPaymentInfo.Contract_id=PersonsSales.PersonsSales_ContractID
			WHERE 
				(
				(PersonsPaymentInfo.PaymentInfo_Execute >= '".$startEpoch."' AND PersonsPaymentInfo.PaymentInfo_Execute <= '".$enderEpoch."')
				)
			AND
				PersonsSalesCommissions.Users_user_id='".$s_dta['Users_user_id']."'
			GROUP BY 
				PersonsSalesCommissions_id 
			ORDER BY 
				PersonsSales_dateCreated ASC
			";
			//$com_sql = "SELECT * FROM PersonsSalesCommissions WHERE PersonsSales_PersonsSales_id='".$s_dta['PersonsSales_id']."'";
			$com_data = $DB->get_multi_result($com_sql);
			if(!isset($com_data['empty_result'])):
				foreach($com_data as $commissions):
					$SALES_DATA = $SALES->get_salePayments($commissions['PersonsSales_id'], $commissions['Persons_Person_id'], $ENC);

					$amt_percentage = @($SALES_DATA['paid'] / $SALES_DATA['base']);

					$col_base_array[] = $commissions['PersonsSales_payment'];
					$col_base_dollars[] = $commissions['CommissionAMT'];
					$col_comm_array[] = round(($commissions['CommissionAMT'] * $amt_percentage), 2);
					
					if($commissions['PaymentInfo_Status'] == 3):
						if($s_dta['Users_user_id'] == 181158):
						//echo "PROCESSED |".$amt_percentage."|<br>";
						endif;						
						$col_proc_array[] = round(($commissions['CommissionAMT'] * $amt_percentage), 2);
					elseif($commissions['PaymentInfo_Status'] == 4):						
						if($s_dta['Users_user_id'] == 181158):
						//echo "PAID |".$amt_percentage."|<br>";
						endif;
						$col_paid_array[] = round(($commissions['CommissionAMT'] * $amt_percentage), 2);
					else:
						if($s_dta['Users_user_id'] == 181158):
						//echo "NOT COLLECTED |".$amt_percentage."|<br>";
						endif;
						$col_ball_array[] = round(($commissions['CommissionAMT'] * $amt_percentage), 2);
					endif;
					$amt_perc_array[] = $amt_percentage;
					endforeach;		
			endif;
			
			if($s_dta['Users_user_id'] == 181158):					
			//print_r($SALES_DATA);
			//print_r($commissions);
			//print_r($col_proc_array);
			endif;
			
			if($s_dta['PersonsSales_dateCreated'] < $startEpoch):
				$rowColor = '#FFFFFF';
			else:
				$rowColor = '#FFFFFF';
			endif;
			//echo "COUNT:".count($com_data);
			$percAverage = (@array_sum($amt_perc_array) / count($com_data));
		?>
        <tr id="row_<?php echo $s_dta['PersonsSales_id']?>" class="<?php echo $rowClass?>" style="background-color:<?php echo $rowColor?>;">
            <td width="325">
            	<a href="javascript:openSaleInfo('<?php echo $s_dta['Users_user_id']?>', '<?php echo $startEpoch?>', '<?php echo $enderEpoch?>')" class="m-link" style="color:#000;"><i class="flaticon-user-ok"></i>&nbsp;<?php echo $RECORD->get_FulluserName($s_dta['Users_user_id'])?> </a>				
            </td>        
            <td width="100" class="text-right"><?php echo @number_format(array_sum($col_base_array), 2)?></td>            
            <td width="100" class="text-right"><?php echo @number_format(array_sum($col_base_dollars), 2)?></td>
            <td width="100" class="text-center"><?php echo round(($percAverage * 100), 1)?>%</td>
            <td width="100" class="text-right"><?php echo @number_format(array_sum($col_comm_array), 2)?></td>
            <!-- <td width="100" class="text-right"><?php echo @number_format(array_sum($col_ball_array), 2)?></td> -->
            <!-- <td width="100" class="text-right"><?php echo @number_format(array_sum($col_proc_array), 2)?></td> -->
            <td width="100" class="text-right"><?php echo @number_format(array_sum($col_paid_array), 2)?></td>              
       	</tr> 
        <?php
		$total_base[] = @array_sum($col_comm_array);
		$total_dollars[] = @array_sum($col_base_dollars);
		$total_balance[] = @array_sum($col_ball_array);
		$total_process[] = @array_sum($col_proc_array);		
		$total_paid[] = @array_sum($col_paid_array);

		//unset($col_base_array);
		unset($col_comm_array);
		unset($col_ball_array);
		unset($col_proc_array);
		unset($col_paid_array);	
		unset($col_base_dollars);	
		unset($col_base_array);
		unset($amt_perc_array);
		
		
		
		endforeach;
		//print_r($total_refund);
	endif;
	$officeSalesTable = ob_get_clean();
	

	?>
    <?php echo $officeSalesTable?>           
    <?php
	
	$running_total_base[] = @array_sum($total_base);
	$running_total_processed[] = @array_sum($total_process);	
	$running_total_dollars[] = @array_sum($total_dollars);
	$running_total_paid[] = @array_sum($total_paid);
	$running_total_balance[] = @array_sum($total_balance);
	$running_total_comdue[] = @array_sum($col_comm_array);
	
	
	unset($total_base);
	unset($total_tax);
	unset($total_dollars);
	unset($total_paid);
	unset($total_balance);
	unset($total_refund);
	unset($total_process);
?>
</tbody>
<tfoot>
	<tr>
    	<th>&nbsp;</th>
        <th>&nbsp;</th>
        <th width="100" class="text-right"><?php echo @number_format(array_sum($running_total_dollars), 2)?></th>
        <th>&nbsp;</th>
        <th width="100" class="text-right"><?php echo @number_format(array_sum($running_total_base), 2)?></th>
        <!-- <th width="100" class="text-right"><?php echo @number_format(array_sum($running_total_balance), 2)?></th> -->
        <!-- <th width="100" class="text-right"><?php echo @number_format(array_sum($running_total_processed), 2)?></th> -->
        <th width="100" class="text-right"><?php echo @number_format(array_sum($running_total_paid), 2)?></th>        
	</tr>        
</tfoot>
</table>

<div class="modal fade" id="commissionReviewModal" tabindex="-1" role="dialog" aria-labelledby="commissionReviewModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="commissionReviewModalLabel">Commissions Information</h5>
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

function reloadSaleInfo(userID, epochSTART, epochEND) {
	mApp.block("#commissionReviewModal .modal-body", {
		overlayColor: "#000000",
		type: "loader",
		state: "primary",
		message: "Loading Commissions Information..."
	});
	$.post('/ajax/mocs.php?action=getComms', {
		uid: userID,
		sta: epochSTART,
		end: epochEND,
		kiss_token: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$("#commissionReviewModal .modal-body").html(data);
		mApp.unblock("#commissionReviewModal .modal-body");	
	});
}
function openSaleInfo(userID, epochSTART, epochEND) {
	$('#commissionReviewModal').modal('show');
	reloadSaleInfo(userID, epochSTART, epochEND);
}

function saveFieldValue(saleID) {
	var formID = 'form_'+saleID;
	var formData = $('#'+formID).serializeArray();
	$.post('/ajax/mors.php?action=saveValue', formData, function(data) {
		reloadSaleInfo(saleID);		
	});	
}
</script>