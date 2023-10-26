<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.sales.php");
include_once("class.encryption.php");
$DB = new database();
$DB->connect();

$RECORD = new Record($DB);
$SALES = new Sales($DB);
$ENC = new encryption();

if (isset($_POST['filterDates'])) {
	$dateParts = explode(" - ", $_POST['filterDates']);
	$startEpoch = strtotime($dateParts[0]);
	$enderEpoch = strtotime($dateParts[1]) + 86399;
	//echo "Custom:".$startEpoch."|".$enderEpoch;
	$dateParameters = array($startEpoch, $enderEpoch);
	
	$dayDiff = (($enderEpoch - $startEpoch) / 86400) - 1;
	$dateDaysPreload = round($dayDiff);			
} else {
	$dateParameters = array((time() - (90 * 86400)), time());
	$startEpoch = time() - (90 * 86400);
	$enderEpoch = time();
	//echo "Default:".$startEpoch."|".$enderEpoch;
}

$MAIN_SQL = "
SELECT 
	Persons.Person_id,
	Persons.FirstName,
	Persons.LastName,
	PersonsContract.*,
	(SELECT ContractHistory_date FROM PersonsContractsHistory WHERE PersonsContractsHistory.Contract_id=PersonsContract.Contract_id ORDER BY ContractHistory_date DESC LIMIT 1) as LastView
FROM 
	PersonsContract 
	INNER JOIN Persons ON Persons.Person_id=PersonsContract.Person_id
WHERE
	PersonsContract.Contract_status != '1'
HAVING
	(LastView >= '".$startEpoch."' AND LastView <= '".$enderEpoch."')
ORDER BY
	LastView DESC
";
$SND = $DB->get_multi_result($MAIN_SQL);
if(!isset($SND['empty_result'])) {	
	?>
    <div class="m-checkbox-inline text-right">
        <label class="m-checkbox">
            <input type="checkbox" id="includeProcessed" value="1" checked>
           	Include Processed Contracts
            <span></span>
        </label>
    </div>
    <table class="table table-sm table-bordered table-hover">	
    <thead>
        <tr>
            <th width="80">&nbsp;</th>
            <th>Lead/Client</th>
            <th>Rep</th>
            <th>Retainer</th>            
            <th>Status</th>
            <th>Membership Type</th>
            <th>Note</th>
        </tr>
	</thead>
    <tbody>
    <?php
	foreach($SND as $DTA) {
		switch($DTA['Contract_status']) {
			case 1:
			$statusBlock = '<span class="m-badge m-badge--info m-badge--wide">PENDING</span>';
			$statusClass = 'pending-c';
			break;
			
			case 2:
			$statusBlock = '<span class="m-badge m-badge--warning m-badge--wide">SIGNED</span>';
			$statusClass = 'signed-c';
			break;
			
			case 3:
			$statusBlock = '<span class="m-badge m-badge--success m-badge--wide">PROCESSED</span>';
			$statusClass = 'processed-c';
			break;
		}
		?>
        <tr id="cRow_<?php echo $DTA['Contract_id']?>" class="<?php echo $statusClass?>">
        	<td>
            	<?php if($DTA['Contract_fileID'] != '0'): ?>
                <a href="/getFile.php?DID=<?php echo $DTA['Contract_fileID']?>" title="View Internal Contract" class="btn btn-success m-btn m-btn--icon btn-sm m-btn--icon-only">
					<i class="la la-file-text-o"></i>
				</a>
                <?php endif; ?>
                <a href="/profile/<?php echo $DTA['Person_id']?>" title="View Profile Record" class="btn btn-info m-btn m-btn--icon btn-sm m-btn--icon-only">
					<i class="la la-user"></i>
				</a>
            </td>
            <td colspan="2"><a href="javascript:reviewContractPayment(<?php echo $DTA['Contract_id']?>);" class="m-link" style="color:#333;"><?php echo $DTA['FirstName']?> <?php echo $DTA['LastName']?>&nbsp;(<?php echo $DTA['Person_id']?>)</td>
            <td><?php echo $RECORD->get_userName($DTA['Contract_rep'])?></td>
            <td class="text-right"><?php echo number_format($DTA['Contract_RetainerFee'], 2)?></td>
            <td id="statusBlock_<?php echo $DTA['Contract_id']?>" class="text-center"><?php echo $statusBlock?></td>
            <td><?php echo $DTA['Contract_MembershipType']?></td>
            <td id="noteBlock_<?php echo $DTA['Contract_id']?>"><?php echo $DTA['Contract_notes']?></td>
		</tr>
        <?php
		$c_sql = "SELECT * FROM PersonsPaymentInfo WHERE Contract_id='".$DTA['Contract_id']."' ORDER BY PaymentInfo_dateCreated DESC";	
		//echo $c_sql."<br>";
		$c_snd = $this->db->get_multi_result($c_sql);
		if(!isset($c_snd['empty_result'])):
		foreach($c_snd as $c_dta):
			switch($c_dta['PaymentInfo_Status']) {
				case 1:
				$statusCode = '<span class="m-badge m-badge--info m-badge--wide">PENDING</span>';
				break;
				
				case 2:
				$statusCode = '<span class="m-badge m-badge--warning m-badge--wide">SUBMITTED</span>';
				break;
				
				case 3:
				$statusCode = '<span class="m-badge m-badge--success m-badge--wide">PAID</span>';
				break;
				
			}
			?>
            <tr class="cRow_<?php echo $DTA['Contract_id']?> <?php echo $statusClass?>">
                <td>&nbsp;</td>
                <td><?php echo date("m/d/y", $c_dta['PaymentInfo_Execute'])?></td>
                <?php if($c_dta['PaymentInfo_paymentType'] == 1): ?>
                <td><a href="javascript:reviewPayment(<?php echo $c_dta['PaymentInfo_ID']?>);" class="m-link" style="color:#333;"><i class="la la-usd"></i> <?php echo $ENC->decrypt($c_dta['PaymentInfo_NameFirst'])?> <?php echo $ENC->decrypt($c_dta['PaymentInfo_NameLast'])?></a></td>
                <?php else: ?>
                <td><a href="javascript:reviewPayment(<?php echo $c_dta['PaymentInfo_ID']?>);" class="m-link" style="color:#333;"><i class="la la-usd"></i> <?php echo $ENC->decrypt($c_dta['PaymentInfo_accountName'])?></a></td> 
                <?php endif; ?>               
                <td><?php echo (($c_dta['PaymentInfo_paymentType'] == 1)? 'CC':'DIRECT')?></td>
                <td><?php echo $ENC->decrypt($c_dta['PaymentInfo_Amount'])?></td>
                <td id="cpayCellDisplay_<?php echo $c_dta['PaymentInfo_ID']?>"><?php echo $statusCode?></td>                                                       
                <td id="cpayCellNoteDisplay_<?php echo $c_dta['PaymentInfo_ID']?>"><?php echo $c_dta['PaymentInfo_notes']?></td>
            </tr>
                <?php
				endforeach;                    			
			else:
				?>
                <tr>
                	<td colspan="5" class="text-center"><em>no payments found</em></td>
                </tr>
                <?php
			endif;	
	}
	?>
    </tbody>
    </table>
    <?php
} else {
		
}
?>
<div class="modal fade" id="myPaymentReviewModal" tabindex="-1" role="dialog" aria-labelledby="myPaymentReviewModalLabel" aria-hidden="true">
	<div class="modal-dialog  modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="myPaymentReviewModalLabel">Payment Information</h5>
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

<div class="modal fade" id="myContractPaymentReviewModal" tabindex="-1" role="dialog" aria-labelledby="myContractPaymentReviewModalLabel" aria-hidden="true">
	<div class="modal-dialog  modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="myPaymentReviewModalLabel">Contract Payment Information</h5>
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
    $(document).on('click', '#includeProcessed', function() {
		if($(this).is(':checked')) {
			$('.processed-c').show();
		} else {
			$('.processed-c').hide();
		}
	});
	
	$(document).on('click', '.cpayStatus', function() {
		$('.cpayStatus').each(function() {
			if($(this).is(':checked')) {
				//alert('Set new status:'+$(this).val());
				var payIDnum = $(this).attr('data-id');
				$.post('/ajax/payments.php?action=setCStatus', {
					stat:$(this).val(),
					payid: payIDnum
				}, function(data) {					
					toastr.success('Payment Status Updated', '', {timeOut: 5000});
					//refreshPaymentTable();
					$('#statusBlock_'+payIDnum).html(data.statusBlock);
					$('#cRow_'+payIDnum).attr('class', data.statusClass);
					$('.cRow_'+payIDnum).attr('class', '.cRow_'+payIDnum+' '+data.statusClass);
				}, "json");					
			}
		});		
	});
	$(document).on('click', '#button-add-contract-note', function() {
		var note = $('#Contract_notes').val();
		var payIDnum = $(this).attr('data-id');
		$.post('/ajax/payments.php?action=addNoteToContract', {
			note: note,
			payid: payIDnum
		}, function(data) {					
			toastr.success('Contract Note Updated', '', {timeOut: 5000});			
			$('#noteBlock_'+payIDnum).html(note);			
		});		
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
	
	$(document).on('click', '#button-add-payment-note', function() {
		var note = $('#PaymentInfo_notes').val();
		var payIDnum = $(this).attr('data-id');
		$.post('/ajax/payments.php?action=addNoteToPayment', {
			note: note,
			payid: payIDnum
		}, function(data) {					
			toastr.success('Payment Note Updated', '', {timeOut: 5000});			
			$('#cpayCellNoteDisplay_'+payIDnum).html(note);			
		});		
	});
});
function reviewContractPayment(contractID) {
	$('#myContractPaymentReviewModal').modal('show');
	mApp.block("#myContractPaymentReviewModal .modal-content", {
		overlayColor: "#000000",
		type: "loader",
		state: "primary",
		message: "Loading..."
	});
	$.post('/ajax/payments.php?action=getContractReviewPayment', {
		cid: contractID	
	}, function(data) {
		$("#myContractPaymentReviewModal .modal-body").html(data);
		mApp.unblock("#myContractPaymentReviewModal .modal-content");	
	});	
}
function reviewPayment(payID) {
	$('#myPaymentReviewModal').modal('show');
	mApp.block("#myPaymentReviewModal .modal-content", {
		overlayColor: "#000000",
		type: "loader",
		state: "primary",
		message: "Loading..."
	});
	$.post('/ajax/payments.php?action=getReviewPayment', {
		pid: payID	
	}, function(data) {
		$("#myPaymentReviewModal .modal-body").html(data);
		mApp.unblock("#myPaymentReviewModal .modal-content");	
	});
}
function openPayment(payID) {
	$('#myPaymentsModal').modal('show');
    mApp.block("#myPaymentsModal .modal-content", {
		overlayColor: "#000000",
		type: "loader",
		state: "primary",
		message: "Loading..."
	});
	$.post('/ajax/payments.php?action=getPayment', {
		pid: payID	
	}, function(data) {
		$('#PaymentInfo_ID').val(payID);
		$('#PaymentContract_id').val(data.contract);
		$('#PaymentInfo_paymentType').val(data.type);
		$('#PaymentInfo_Amount').val(data.amt);
		$('#PaymentInfo_Execute').val(data.pdate);
		
		$("#PaymentInfo_Execute").datepicker("setDate", data.pdate);
		if(data.status == 0) {
			$('#paymentAlertArea').hide();
			$('#button-payment-delete').attr('disabled', true);
			$('#button-copy-paylink').attr('disabled', true);
		} else {
			$('#payment_embedCode').val(data.url);
			$('#paymentAlertArea').show();
			$('#button-payment-delete').attr('disabled', false);
			$('#link-payment-showcase').attr('href', data.url);
			$('#button-copy-paylink').attr('disabled', false);
		}
		mApp.unblock("#myPaymentsModal .modal-content");
	}, "json");
}
</script>