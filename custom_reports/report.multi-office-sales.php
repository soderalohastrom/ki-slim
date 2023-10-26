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

$REPORT_ID = 41;

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

ob_start();
$o_sql = "SELECT * FROM Offices ORDER BY office_Name ASC";
$o_snd = $DB->get_multi_result($o_sql);
foreach($o_snd as $o_dta):
	?><option value="<?php echo $o_dta['Offices_id']?>" <?php echo ((in_array($o_dta['Offices_id'], $offices)? 'selected':''))?>><?php echo $o_dta['office_Name']?></option><?php
endforeach;
$officeSelect = ob_get_clean();

?>
<script src="/assets/vendors/custom/tablesorter/dist/js/jquery.tablesorter.min.js" type="text/javascript"></script>
<script src="/assets/vendors/custom/floatThead/dist/jquery.floatThead.min.js" type="text/javascript"></script>
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
<div class="row">
	<div class="col-8">
		<div class="form-group m-form__group">         
            <select class="form-control m-select2" id="Offices_id" name="Offices_id[]" multiple="multiple">
            	<?php echo $officeSelect?>
            </select>
        </div> 
    </div>
    <div class="col-4">
    	<div class="input-group m-input-group">					
            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-calendar"></i></span>
            <input type="text" class="form-control m-input input-sm" id="filterDates" name="filterDates" placeholder="dates" autocomplete="off" value="<?php echo $_POST['filterDates']?>">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-default">Apply Filters</button>
            </span>
        </div>
        
        <div class="m-form__group form-group" style="margin-top:10px;">
            <div class="m-checkbox-inline">
                <label class="m-checkbox">
                    <input type="checkbox" name="filterInclude" value="1" <?php echo (($_POST['filterInclude'] == 1)? 'checked':'')?>>
                    Include payments
                    <span></span>
                </label>               
            </div>
            <span class="m-form__help">
                <small>This will include payments that occur within the timeframe.</small>
            </span>
        </div>
    </div>
</div>
</form>

<table class="table table-bordered m-table mors-table">
<thead class="thead-inverse">
	<tr>
    	<th colspan="8">&nbsp;</th>
        <th width="100" class="text-center">Total<br />Base</th>
        <!--<th width="8%" class="text-center">Total<br />Tax</th>-->
        <th width="100" class="text-center">Total<br />Dollars</th>
        <th width="100" class="text-center">Total<br />Processed</th>
        <th width="100" class="text-center">Total<br />Paid</th>
        <th width="8%" class="text-center">Total<br />Refund</th>
        <!-- <th width="8%" class="text-center">Refund<br />Perc</th> -->
        <th width="100" class="text-center">Total<br />Balance</th>
	</tr>        
</thead>
<tbody>
<?php
sort($offices);
foreach($offices as $office):
	ob_start();
	
	if ($_POST['filterInclude'] == 1):
	$s_sql = "
	SELECT 
		* 
	FROM 
		PersonsSales 
		LEFT JOIN PersonsContract ON PersonsContract.Contract_id=PersonsSales.PersonsSales_ContractID
		LEFT JOIN PersonsPaymentInfo ON PersonsPaymentInfo.Contract_id=PersonsSales.PersonsSales_ContractID
	WHERE
	 	Offices_Offices_id='".$office."' 
	AND
		(
			(PersonsPaymentInfo.PaymentInfo_Execute >= '".$startEpoch."' AND PersonsPaymentInfo.PaymentInfo_Execute <= '".$enderEpoch."')
			OR
			(PersonsSales.PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales.PersonsSales_dateCreated <= '".$enderEpoch."')
		)
	AND
		PersonsPaymentInfo.Person_id != 0
	GROUP BY 
		PersonsSales_id 
	ORDER BY 
		PersonsSales_dateCreated ASC
	";
	else:
	$s_sql = "
	SELECT 
		* 
	FROM 
		PersonsSales 
		LEFT JOIN PersonsContract ON PersonsContract.Contract_id=PersonsSales.PersonsSales_ContractID
		LEFT JOIN PersonsPaymentInfo ON PersonsPaymentInfo.Contract_id=PersonsSales.PersonsSales_ContractID
	WHERE 
		(
		(PersonsSales.PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales.PersonsSales_dateCreated <= '".$enderEpoch."')
		)
	AND 
		Offices_Offices_id='".$office."' 
	AND
		PersonsPaymentInfo.Person_id != 0
	GROUP BY 
		PersonsSales_id 
	ORDER BY 
		PersonsSales_dateCreated ASC
	";	
	endif;
	//echo $s_sql."<br>\n";
	$s_snd = $DB->get_multi_result($s_sql);	
	if(isset($s_snd['empty_result'])):
		$skipLocation = true;
	else:
		$skipLocation = false;
		$rowClass = "officeSales_".$office;
		?>
        <tr style="background-color:#EAEAEA;" class="<?php echo $rowClass?>">
            <td width="75">Date</td>
            <td width="325">Client</td>
            <td width="2">&nbsp;</td>
            <td width="30">ID</td>
            <td width="50">Contract</td>
            <td width="50">Payments</td>
            <td width="250">Salesperson</td>
            <td width="150">Status</td>
            <td width="100">Base</td>
            <!--<th>Taxes</th>-->
            <td width="100">Total</td>
            <td width="100">Processed</td>
            <td width="100">Paid</td>
            <td width="100">Refund</td>
            <td width="100">Balance</td>
        </tr>       
        <?php
		foreach($s_snd as $s_dta):
			$com_sql = "SELECT * FROM PersonsSalesCommissions WHERE PersonsSales_PersonsSales_id='".$s_dta['PersonsSales_id']."'";
			$com_data = $DB->get_multi_result($com_sql);
			if(!isset($com_data['empty_result'])):
				foreach($com_data as $commissions):
				$sp_array[] = $RECORD->get_userName($commissions['Users_user_id']);
				$psc_array[] = $commissions['CommissionAMT'];
				endforeach;		
			endif;
			
			$paid = $SALES->get_salePayments($s_dta['PersonsSales_id'], $s_dta['Persons_Person_id'], $ENC);
			//print_r($paid);
			$balance = $s_dta['PersonsSales_payment'] - $paid['total'];
			//echo "BALANCE: ".$balance;
			
			if ($s_dta['PersonsSales_ContractID'] == 0) {
				
				
				$op_sql = "SELECT count(*) as count FROM PersonsSalesPayments WHERE PersonsSales_PersonsSales_id='".$s_dta['PersonsSales_id']."'";
				$op_snd = $this->db->get_single_result($op_sql);
				if($op_snd['count'] == 0) {
					$paymentCount = '<i class="flaticon-warning-2" data-toggle="m-tooltip" title="No payment records connected to this contract/sale"></i>';
					$contractSTATUS = '<i class="flaticon-warning-2" data-toggle="m-tooltip" title="No contract attached to this sale"></i>';
				} else {
					//print_r($op_snd);
					//$paymentCount = '<i class="flaticon-exclamation-square" data-toggle="m-tooltip" title="This is an old record using the old payment engine from CUPID."></i>';
					$paymentCount = '<span class="m--font-danger" data-toggle="m-tooltip" title="This is an old record using the old payment engine from CUPID.">'.$op_snd['count'].'</span>';
					$contractSTATUS = '<i class="flaticon-warning-2" data-toggle="m-tooltip" title="No contract attached to this sale"></i>';	
					
					$op_sql = "SELECT * FROM PersonsSalesPayments WHERE PersonsSales_PersonsSales_id='".$s_dta['PersonsSales_id']."'";
					$op_snd = $this->db->get_multi_result($op_sql);
					foreach($op_snd as $op_dta):
						//print_r($op_dta);
						$baseTotal[] = $op_dta['PaymentAmount'];
						$runningTotal[] = $op_dta['PaymentAmount'];
						$processedTotal[] = '0.00';
						$runningRefund[] = '0.00';					
						if($op_dta['PayStatus'] == 1) {
							$collectedTotal[] = $op_dta['PaymentAmount'];
						} else {
							$collectedTotal[] = '0.00';
						}
					endforeach;
					
					$totalFound = @array_sum($runningTotal);
					$totalProcessed = @array_sum($processedTotal);
					$collected = @array_sum($collectedTotal);
					$display = number_format($collected, 2);
					$totalRefund = @array_sum($runningRefund);			
					$refundDisplay = number_format($totalRefund, 2);
					$baseTotal = @array_sum($baseTotal);
					
					
					$paid = array(
						'base'		=>	$baseTotal,			
						'total'		=>	$totalFound,
						'display'	=>	$display,
						'refund'	=>	$totalRefund,
						'rdisplay'	=>	$refundDisplay,
						'iSrefund'	=>	$isRefund,
						'processed'	=>	$totalProcessed,
						'paid'		=>	$collected
					);
					//echo "<hr>";
					unset($baseTotal);
					unset($runningTotal);
					unset($processedTotal);
					unset($runningRefund);
					unset($collectedTotal);	
					$balance = $s_dta['PersonsSales_payment'] - $paid['paid'];			
				}				
			} else {
				$cp_sql = "SELECT COUNT(*) as count FROM PersonsPaymentInfo WHERE Contract_id='".$s_dta['PersonsSales_ContractID']."'";
				//echo $cp_sql."<br>";
				$cp_snd = $this->db->get_single_result($cp_sql);
				$paymentCount = $cp_snd['count'];
				
				$con_sql = "SELECT * FROM PersonsContract WHERE Contract_id='".$s_dta['PersonsSales_ContractID']."'";
				$con_dta = $DB->get_single_result($con_sql);
				$contractSTATUS = $con_dta['Contract_status'];	
			}
			
			if($s_dta['PersonsSales_dateCreated'] < $startEpoch):
				$rowColor = '#CCCFFF';
			else:
				$rowColor = '#FFFFFF';
			endif;
		?>
        <tr id="row_<?php echo $s_dta['PersonsSales_id']?>" class="<?php echo $rowClass?>" style="background-color:<?php echo $rowColor?>;">
        	<td width="75"><?php echo date("m/d/y", $s_dta['PersonsSales_dateCreated'])?></td>
            <td width="325">
            	<a href="/profile/<?php echo $s_dta['Persons_Person_id']?>" class="m-link" style="color:#000;" target="_blank"><?php echo $RECORD->get_personName($s_dta['Persons_Person_id'])?> <i class="la la-user"></i></a>				
            </td>
            <td width="2">
            	<a href="javascript:openSaleInfo('<?php echo $s_dta['PersonsSales_id']?>')" class="m-link" style="color:#000;"><i class="la la-puzzle-piece"></i></a>
            </td>
            <td width="30" class="text-center"><?php echo $s_dta['Persons_Person_id']?></td>
            <td width="50" class="text-center"><?php echo (($s_dta['PersonsSales_ContractID'] == 0)? '<i class="flaticon-warning-2" data-toggle="m-tooltip" title="No contract is associated with this sale"></i>':$s_dta['PersonsSales_ContractID'])?></td>
            <td width="50" class="text-center"><?php echo $paymentCount?></td>
            <td width="250"><?php echo @implode(", ", $sp_array)?></td>
            <td width="150" class="text-center" id="contractStatus_<?php echo $s_dta['PersonsSales_ContractID']?>"><?php echo $SALES->getContractStatus($contractSTATUS)?></td>           
            <td width="100" class="text-right"><?php echo number_format($s_dta['PersonsSales_basePrice'], 2)?> <?php echo (($paid['iSrefund'])? '[REFUND]':'')?></td>
            <!--<td width="10%"><?php echo $s_dta['PersonsSales_taxes']?></td>-->            
            <td width="100" class="text-right"><?php echo number_format($s_dta['PersonsSales_payment'], 2)?></td>
            <td width="100" class="text-right"><?php echo number_format($paid['processed'], 2)?></td>
            <td width="100" class="text-right"><a href="javascript:reviewSalePayments('<?php echo $s_dta['PersonsSales_id']?>','<?php echo $s_dta['Persons_Person_id']?>')" class="m-link" style="color:#000;"><?php echo $paid['display']?></a></td>
            <td width="100" class="text-right"><?php echo number_format($paid['refund'], 2)?></td>
            <td width="100" class="text-right"><?php echo number_format($balance, 2)?></td>  
       	</tr> 
        <?php
		$total_base[] = $s_dta['PersonsSales_basePrice'];
		$total_tax[] = $s_dta['PersonsSales_taxes'];
		$total_dollars[] = $s_dta['PersonsSales_payment'];
		$total_paid[] = $paid['total'];
		$total_balance[] = $balance;
		$total_refund[] = $paid['refund'];
		$total_process[] = $paid['processed'];
		
		unset($sp_array);
		endforeach;
		//print_r($total_refund);
	endif;
	$officeSalesTable = ob_get_clean();
	
	if(!$skipLocation):
	?>
    <tr id="prow_<?php echo $office?>">
    	<td colspan="8" class="office-name-block"><span style="font-size:16px; font-weight:bold;"><?php echo $RECORD->getOfficeName($office)?></span> <i class="toggle-section la la-angle-up" data-id="<?php echo $office?>"></i></td>
        <td class="text-right"><?php echo @number_format(array_sum($total_base), 2)?></td>
        <!--<td class="text-right"><?php echo @number_format(array_sum($total_tax), 2)?></td>-->
        <td class="text-right"><?php echo @number_format(array_sum($total_dollars), 2)?></td>
        <td class="text-right"><?php echo @number_format(array_sum($total_process), 2)?></td>
        <td class="text-right"><?php echo @number_format(array_sum($total_paid), 2)?></td>                
		<td class="text-right"><?php echo @number_format(array_sum($total_refund), 2)?></td>
        <!-- <td class="text-right">%<?php echo @round(((array_sum($total_refund) / array_sum($total_base)) * 100))?></td> -->
        <td class="text-right"><?php echo @number_format(array_sum($total_balance), 2)?></td>
	</tr>
    <?php echo $officeSalesTable?>           
    <?php
	endif;
	$running_total_base[] = @array_sum($total_base);
	$running_total_tax[] = @array_sum($total_tax);
	$running_total_dollars[] = @array_sum($total_dollars);
	$running_total_paid[] = @array_sum($total_paid);
	$running_total_balance[] = @array_sum($total_balance);
	$running_total_refund[] = @array_sum($total_refund);
	$running_total_processed[] = @array_sum($total_process);
	
	unset($total_base);
	unset($total_tax);
	unset($total_dollars);
	unset($total_paid);
	unset($total_balance);
	unset($total_refund);
	unset($total_process);
endforeach;
?>
</tbody>
<tfoot>
	<tr>
    	<th colspan="8">&nbsp;</th>
        <th width="100" class="text-right"><?php echo @number_format(array_sum($running_total_base), 2)?></th>
        <!--<th class="text-right"><?php echo @number_format(array_sum($running_total_tax), 2)?></th>-->
        <th width="100" class="text-right"><?php echo @number_format(array_sum($running_total_dollars), 2)?></th>
        <th width="100" class="text-right"><?php echo @number_format(array_sum($running_total_processed), 2)?></th>
        <th width="100" class="text-right"><?php echo @number_format(array_sum($running_total_paid), 2)?></th>
        <th class="text-right"><?php echo @number_format(array_sum($running_total_refund), 2)?></th>
        <th width="100" class="text-right"><?php echo @number_format(array_sum($running_total_balance), 2)?></th>
	</tr>        
</tfoot>
</table>

<div class="modal fade" id="paymentReviewModal" tabindex="-1" role="dialog" aria-labelledby="paymentReviewModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="paymentReviewModalLabel">Contract and Sale Payment Information</h5>
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

<div class="modal fade" id="contractReviewModal" tabindex="-1" role="dialog" aria-labelledby="contractReviewModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="contractReviewModalLabel">Contract/Payments/Sale Information</h5>
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
$(document).ready(function(e) {
	
	$('#contractReviewModal').on('hidden.bs.modal', function () {
  		$("#contractReviewModal .modal-body").html('');
	});
	
	<?php if ($_POST['filterInclude'] == 1): ?>
	$('.m-portlet__head-text').html('Multi-Office Revenue Report');
	<?php else: ?>
	$('.m-portlet__head-text').html('Multi-Office Sales Report');
	<?php endif; ?>
	
	$('.stats_table').tablesorter();
    $(document).on('click', '.toggle-section', function() {
		var rowID = 'row_'+$(this).attr('data-id');
		var prowID = 'prow_'+$(this).attr('data-id');
		var ofRows = 'officeSales_'+$(this).attr('data-id');
		if($('.'+ofRows).is(':visible')) {
			$('.'+ofRows).each(function() {
				$(this).hide();
			});
			//$('#'+rowID).hide();
			$('#'+prowID+' .toggle-section').removeClass('la-angle-up');
			$('#'+prowID+' .toggle-section').addClass('la-angle-down');
		} else {
			$('.'+ofRows).each(function() {
				$(this).show();
			});
			$('#'+prowID+' .toggle-section').removeClass('la-angle-down');
			$('#'+prowID+' .toggle-section').addClass('la-angle-up');
		}
	});
	
	$(document).on('click', '.conStatus', function() {
		if($(this).is(':checked')) {
			var conID = $(this).attr('data-id');
			$.post('/ajax/payments.php?action=setCStatus', {
				stat:$(this).val(),
				payid:conID 	
			}, function(data) {					
				toastr.success('Contract Status Updated', '', {timeOut: 5000});
				//$('#cpayCellDisplay_'+payIDnum).html(data.statusBlock);
				$('#contractStatus_'+conID).html(data.statusBlock);				
			}, "json");
		}
	});
	
	$(document).on('click', '.payStatus', function() {
		$('.payStatus').each(function() {
			if($(this).is(':checked')) {
				var payIDnum = $(this).attr('data-id');				
				$.post('/ajax/payments.php?action=setStatus', {
					stat:$(this).val(),
					payid:payIDnum 	
				}, function(data) {					
					toastr.success('Payment Status Updated', '', {timeOut: 5000});
					$('#cpayCellDisplay_'+payIDnum).html(data.statusBlock);					
				}, "json");					
			}
		});		
	});
		
	$("#Offices_id").select2({
		placeholder: "Select location(s)",
		allowClear: !0
	});
	
	//var start = moment().subtract(<?php echo $dateDaysPreload?>, 'days');
	//var end = moment();	
	$('#filterDates').daterangepicker({
		buttonClasses: 'm-btn btn',
		applyClass: 'btn-primary',
		cancelClass: 'btn-secondary',
		//startDate: start,
		//endDate: end,
		ranges: {
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
			'Next Month': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')],
		   	'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
			'Last Year to Date': [moment('<?php echo date("c", mktime(0,0,0,1,1,(date("Y") - 1)))?>'), moment('<?php echo date("c", mktime(0,0,0,12,31,(date("Y") - 1)))?>')],
		}
	});
	
	/*
	$(document).on('click', '.fieldEdit', function() {
		var sid 	= $(this).attr('data-id');
		var field 	= $(this).attr('data-field');
		var ttype 	= $(this).attr('data-type');
		var table 	= $(this).attr('data-table');
		var tkey	= $(this).attr('data-key');
		$.post('/ajax/mors.php?action=editfield', {
			sid			: sid,
			field		: field,
			ttype		: ttype,
			table		: table,
			tkey		: tkey,
			kiss_token	: '<?php echo $SESSION->createToken()?>'
		}, function(data) {
			//$('#'+field).html(data);
		});	
	});
	*/
	
	var $table = $('table.mors-table');
	$table.floatThead();
});
function editContractElement(pid, field, ttype, table, tkey, symbol='') {
	$.post('/ajax/mors.php?action=editfield', {
		sid			: pid,
		field		: field,
		ttype		: ttype,
		table		: table,
		tkey		: tkey,
		symbol		: symbol,
		kiss_token	: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$('#'+field).html(data);
		if((ttype == 'date') || (ttype == 'pdate')) {
			$('#value').datepicker({
				todayHighlight: !0,
				orientation: "bottom left",
				templates: {
					leftArrow: '<i class="la la-angle-left"></i>',
					rightArrow: '<i class="la la-angle-right"></i>'
				}
			});
		}
	});
}
function editOldPayElement(pid, field, ttype, table, tkey, symbol='') {
	$.post('/ajax/mors.php?action=editfield', {
		sid			: pid,
		field		: field,
		ttype		: ttype,
		table		: table,
		tkey		: tkey,
		symbol		: symbol,
		kiss_token	: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$('#payment-'+pid+'-'+field).html(data);
		if(ttype == 'date') {
			$('#value').datepicker({
				todayHighlight: !0,
				orientation: "bottom left",
				templates: {
					leftArrow: '<i class="la la-angle-left"></i>',
					rightArrow: '<i class="la la-angle-right"></i>'
				}
			});
		}
	});
}
function editPayElement(pid, field, ttype, table, tkey, symbol='') {
	console.log('Flag:'+symbol);
	$.post('/ajax/mors.php?action=editfield', {
		sid			: pid,
		field		: field,
		ttype		: ttype,
		table		: table,
		tkey		: tkey,
		symbol		: symbol,
		kiss_token	: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$('#payment-'+pid+'-'+field).html(data);
		if(ttype == 'date') {
			$('#value').datepicker({
				todayHighlight: !0,
				orientation: "bottom left",
				templates: {
					leftArrow: '<i class="la la-angle-left"></i>',
					rightArrow: '<i class="la la-angle-right"></i>'
				}
			});
		}
	});
}
function editSaleElement(sid, field, ttype, table, tkey) {
	$.post('/ajax/mors.php?action=editfield', {
		sid			: sid,
		field		: field,
		ttype		: ttype,
		table		: table,
		tkey		: tkey,
		kiss_token	: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$('#'+field).html(data);
		if(ttype == 'date') {
			$('#value').datepicker({
				todayHighlight: !0,
				orientation: "bottom left",
				templates: {
					leftArrow: '<i class="la la-angle-left"></i>',
					rightArrow: '<i class="la la-angle-right"></i>'
				}
			});
		}
	});	
}

function reloadSaleInfo(saleID) {
	if ($('#PARENT_SALE_ID').length) {
		var SID = $('#PARENT_SALE_ID').val();
	} else {
		var SID = saleID;
	}
	mApp.block("#contractReviewModal .modal-body", {
		overlayColor: "#000000",
		type: "loader",
		state: "primary",
		message: "Loading Sale Information..."
	});
	$.post('/ajax/mors.php?action=getSale', {
		sid: SID,
		kiss_token: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$("#contractReviewModal .modal-body").html(data);
		mApp.unblock("#contractReviewModal .modal-body");
		mApp.init();	
	});
}
function saveFieldValue(saleID) {
	var formID = 'form_'+saleID;
	var formData = $('#'+formID).serializeArray();
	$.post('/ajax/mors.php?action=saveValue', formData, function(data) {
		reloadSaleInfo(saleID);		
	});	
}
function makeGhostPayment(saleID, contractID) {
	$.post('/ajax/mors.php?action=ghostPayment', {
		sale: saleID,
		contract: contractID,
		kiss_token: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		reloadSaleInfo(saleID);
	});	
}
function removePaymentBlock(saleID, payID) {
	var choice = confirm('Are you sure you want to remove this payment. This information cannot be restored once deleted.');
	if(choice) {
		var confirmText = prompt('Please enter to the word "DELETE" (all caps) to confirm your choice');
		if(confirmText == 'DELETE') {
			$.post('/ajax/mors.php?action=removePayment', {
				sale: saleID, 
				payment: payID,
				kiss_token: '<?php echo $SESSION->createToken()?>'
			}, function(data) {
				reloadSaleInfo(saleID);
			});
		}
	}
}

function clearPaymentBlock(saleID, payID) {
	var choice = confirm('Are you sure you want to clear this payment info. This information cannot be restored.');
	if(choice) {
			$.post('/ajax/mors.php?action=clearPayment', {
				sale: saleID, 
				payment: payID,
				kiss_token: '<?php echo $SESSION->createToken()?>'
			}, function(data) {
				reloadSaleInfo(saleID);
			});
		}
}


function openSaleInfo(saleID) {
	$('#contractReviewModal').modal('show');
	reloadSaleInfo(saleID);
}
function reviewSalePayments(saleID, personID) {
	$('#paymentReviewModal').modal('show');	
	mApp.block("#paymentReviewModal .modal-content", {
		overlayColor: "#000000",
		type: "loader",
		state: "primary",
		message: "Loading..."
	});
	$.post('/ajax/payments.php?action=fullContractSaleReview', {
		sid: saleID,
		pid: personID
	}, function(data) {
		$("#paymentReviewModal .modal-body").html(data);
		mApp.unblock("#paymentReviewModal .modal-content");	
	});	
		
	
}

</script>

