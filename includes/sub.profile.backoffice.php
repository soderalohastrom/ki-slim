<?php
include_once("class.sales.php");
include_once("class.geocode.php");
//include_once("class.cupidMigration.php");
include_once("class.sessions.php");
include_once("class.encryption.php");
include_once("class.recordMMExpectations.php");
$ENC = new encryption();
$SALES = new Sales($DB, $ENC);
$GEOCODE = new GeoCode($DB);
$SESSION = new Session($DB, $ENC);
$EXPECT = new recordMMExpect($DB);
?>


<div class="row">
	<div class="col-6">
		<h4>Background Check Status</h4>
		<?php echo $BG->render_BG_check($PERSON_ID)?>
		<div>&nbsp;</div> 
	</div>
    
    <div class="col-6">
<div class="text-right">
	<button type="button" class="btn m-btn--pill btn-secondary btn-sm" title="this will update the total numbers for this record." onclick="updateNumbers()" id="btn-updateNumbers">Update Numbers <i class="fa fa-code-fork"></i></button>
</div>    
<div class="m-widget1" style="padding:0.2em;">
    <div class="m-widget1__item">
        <div class="row m-row--no-padding align-items-center">
            <div class="col">
                <h3 class="m-widget1__title">Paid to Date</h3>
                <span class="m-widget1__desc"><small id="TotalPaid_displayDate">updated <?php echo date("m/d/y h:ia", $PDATA['TotalPaidLastUpdate'])?></small></span>
            </div>
            <div class="col m--align-right">
                <span class="m-widget1__number m--font-brand" id="TotalPaid_display"><i class="fa fa-usd"></i><?php echo number_format($PDATA['TotalPaid'], 0)?></span>
            </div>
        </div>
    </div>
    <div class="m-widget1__item">
        <div class="row m-row--no-padding align-items-center">
            <div class="col">
                <h3 class="m-widget1__title">Sales Commissions</h3>
                <span class="m-widget1__desc"><small id="TotalSCom_displayDate">updated <?php echo date("m/d/y h:ia", $PDATA['SaleCommissionPaidLastUpdate'])?></small></span>
            </div>
            <div class="col m--align-right">
                <span class="m-widget1__number m--font-danger" id="TotalSCom_display"><i class="fa fa-usd"></i><?php echo number_format($PDATA['SalesCommissionsPaid'], 0)?></span>
            </div>
        </div>
    </div>
    <!--
    <div class="m-widget1__item">
        <div class="row m-row--no-padding align-items-center">
            <div class="col">
                <h3 class="m-widget1__title">Intro Commissions Paid</h3>
                <span class="m-widget1__desc">client lifetime</span>
            </div>
            <div class="col m--align-right">
                <span class="m-widget1__number m--font-danger">{VAR}</span>
            </div>
        </div>
    </div>
    -->
</div>    
    
    </div>
</div>            

<?php if(($PDATA['PersonsTypes_id'] != 3) || ($PDATA['PersonsTypes_id'] != 13)):?>
<h4>Backoffice</h4>



<div>&nbsp;</div>
<div class="row">
    <div class="col-md-6" id="quickbackoffice-toggle-area">
    	<div style="margin-bottom:10px;">
            <button type="button" class="btn m-btn--pill btn-outline-info btn-block" data-toggle="modal" data-target="#bioConfigModal">
                Configure Bio Display <i class="fa fa-gears"></i>
            </button>
         </div> 
         <?php if($PDATA['BioConfig'] != ''):?>  
        <div class="alert alert-info hide" role="alert">
            This bio was reviewed and approved by<br /><?php echo $RECORD->get_userName($PDATA['BioApproveBy'])?> on <?php echo date("m/d/Y", $PDATA['BioApprovedDate'])?>
        </div>
        <?php endif; ?>
          
        <div class="form-group m-form__group row">
            <label class="col-form-label col-lg-6 col-sm-12">TOS Agreed</label>
            <div class="col-lg-6 col-md-6 col-sm-12 text-right">
                <input name="RequirePM" type="checkbox" id="RequirePM" <?php echo (($PDATA['RequirePM'] == 1)? 'checked':'')?> />
            </div>
        </div>
        <div class="form-group m-form__group row">
            <label class="col-form-label col-lg-6 col-sm-12">Monitor this Record</label>
            <div class="col-lg-6 col-md-6 col-sm-12 text-right">
                <input name="Monitored" id="Monitored" type="checkbox" <?php echo (($PDATA['Monitored'] == 1)? 'checked':'')?> />
            </div>
        </div>
        <div class="form-group m-form__group row">
            <label class="col-form-label col-lg-6 col-sm-12">
            	VIP Client
			</label>
            <div class="col-lg-6 col-md-6 col-sm-12 text-right">
                <input name="VIP" id="VIP" type="checkbox" <?php echo (($PDATA['VIP'] == 1)? 'checked':'')?> />
            </div>
        </div>
        <div class="form-group m-form__group row">
            <label class="col-form-label col-lg-6 col-sm-12">
            	High Priority Client
			</label>
            <div class="col-lg-6 col-md-6 col-sm-12 text-right">
                <input name="HCS" id="HCS" type="checkbox" <?php echo (($PDATA['HCS'] == 1)? 'checked':'')?> />
            </div>
        </div>                
    </div> 
    <div class="col-md-6">    	 
    	<div class="form-group m-form__group row">
            <label class="col-form-label col-lg-6 col-sm-12">OPEN RECORD</label>
            <div class="col-lg-6 col-md-6 col-sm-12 text-right">
                <span class="m-switch m-switch--outline m-switch--icon m-switch--warning">
                    <label>
                        <input type="checkbox" name="OpenRecord" id="OpenRecord" <?php echo (($PDATA['OpenRecord'] == 1)? 'checked':'')?> />
                        <span></span>
                    </label>
                </span>
			</div>
            
            <div id="open_record_notice" style="display:<?php echo (($PDATA['OpenRecord'] == 1)? 'default':'none')?>;">
                <div class="alert alert-warning" role="alert">
                    This record has been marked as an open record and is available for any user to view.
                </div>
            </div>
		</div>           	
        <?php echo $RECORD->render_FreezeHisotry($PERSON_ID)?>
    </div>              
</div> 
<div>&nbsp;</div>


<?php endif; ?>

<h4>
<a href="/expectadd/<?php echo $PERSON_ID?>/0" class="btn btn-warning btn-sm pull-right">Add Expectation Addendum <i class="la la-plus"></i></a>
<a href="/expectgen/<?php echo $PERSON_ID?>/0" class="btn btn-secondary btn-sm pull-right">Add Expectation Agreement <i class="la la-plus"></i></a>
    Expectations
</h4>
<?php echo $EXPECT->render_MMExpect_table($PERSON_ID)?>
<div>&nbsp;</div>
<h4>
	<a href="/contractgen/<?php echo $PERSON_ID?>/" class="btn btn-secondary btn-sm pull-right">Add Agreement <i class="la la-plus"></i></a>
    Agreements
</h4>
<?php echo $RECORD->render_ContractsTable($PERSON_ID)?>
<div>&nbsp;</div>

<h4>
	<button type="button" class="btn btn-secondary btn-sm pull-right" onclick="openPayment(0)">Add Sale Form <i class="la la-plus"></i></button>
    Sale Forms
</h4>
<div id="paymentFormTable">
<?php echo $RECORD->render_ccFormTable($PERSON_ID)?>
</div>
<div>&nbsp;</div>

<h4>
	<a href="/newsale/<?php echo $PERSON_ID?>/0" class="btn btn-secondary btn-sm pull-right">Add Sale <i class="la la-plus"></i></a>
	Sales
</h4>
<?php echo $SALES->render_clientSales($PERSON_ID)?>
<div>&nbsp;</div>
<h4>
	<button type="button" data-toggle="modal" data-target="#myFilesModal" class="btn btn-secondary btn-sm pull-right">Add File <i class="la la-plus"></i></button>
	Files &amp; Documents
</h4>
<div id="filesTableDisplay">  
<table class="table table-condensed" id="files_table" width="100%">
	<thead>
    	<tr>
        	<th>Date</th>
            <th>Type</th>
            <th>File</th>
            <th>Action</th>
		</tr>
	</thead>
    <tbody>
	</tbody>
</table>
</div>
<?php if(in_array(79, $USER_PERMS)): ?>
<div>&nbsp;</div>
<h4>CUPID Data Migration</h4>
<div id="CUPID_RECORD_LINK"></div>
<?php endif; ?>



<div class="modal fade" id="mySaleModal" tabindex="-1" role="dialog" aria-labelledby="mySaleModalLabel" aria-hidden="true">
	<div class="modal-dialog  modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="mySaleModalLabel">Sale Information</h5>
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

<div class="modal fade" id="myFilesModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="myFilesModalLabel" aria-hidden="true">
	<div class="modal-dialog  modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="myFilesModalLabel">Upload File</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            
<form class="m-form" id="NewFiles" action="">
    <input type="hidden" name="pid" value="<?php echo $personID?>" />      
    <div class="m-dropzone m-dropzone--success" action="/ajax/uploadFile.php?pid=<?php echo $personID?>" id="m-dropzone-four">
        <div class="m-dropzone__msg dz-message needsclick">
            <h3 class="m-dropzone__msg-title">
                Drop files here or click to upload.
            </h3>
            <span class="m-dropzone__msg-desc">
                35MB max file size
            </span>
        </div>
    </div>
</form>
    
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php echo $RBIO->render_bioConfigModal($PERSON_ID)?>

<script>
var FileDropZone;
var filesTable;
jQuery(document).ready(function() {
	/*
	$("#PaymentInfo_Execute").datepicker({
		todayHighlight: !0,
		templates: {
			leftArrow: '<i class="la la-angle-left"></i>',
			rightArrow: '<i class="la la-angle-right"></i>'
		}
	});
	*/
	$(document).on('change', '#OpenRecord', function() {
		if($('#OpenRecord').is(':checked')) {
			//alert('Checked');
			var checkRst = 1;
			var NoticeText = 'Success Making Open Record';
			$('#open_record_notice').show();		
		} else {
			//alert('Unchecked');
			var checkRst = 0;
			var NoticeText = 'Removed Open Record Status';
			$('#open_record_notice').hide();
		}
		$.post('/ajax/inline.basic.php', { 
			qid		: 'OpenRecord',
			value	: checkRst,			
			pid: <?php echo $PERSON_ID?>,
			kiss_token: '<?php echo $SESSION->createToken()?>'
		}, function(data) {
			toastr.warning(NoticeText, '', {timeOut: 5000});			
		});
		
	});
	
	$(document).on('click', '.payStatus', function() {
		$('.payStatus').each(function() {
			if($(this).is(':checked')) {
				//alert('Set new status:'+$(this).val());
				$.post('/ajax/payments.php?action=setStatus', {
					stat:$(this).val(),
					payid: $(this).attr('data-id')	
				}, function(data) {					
					toastr.success('Payment Status Updated', '', {timeOut: 5000});
					refreshPaymentTable();					
				});					
			}
		});		
	});
	$(document).on('click', '#button-add-payment-note', function() {
		var note = $('#PaymentInfo_notes').val();
		var payIDnum = $(this).attr('data-id');
		$.post('/ajax/payments.php?action=addNoteToPayment', {
			note: note,
			payid: payIDnum,
			kiss_token: '<?php echo $SESSION->createToken()?>'
		}, function(data) {					
			toastr.success('Payment Note Updated', '', {timeOut: 5000});			
			$('#cpayCellNoteDisplay_'+payIDnum).html(note);			
		});		
	});
	
	$('#myFilesModal').on('show.bs.modal', function (e) {
		console.log('open modal');
		$('#m-dropzone-four').addClass('dropzone');
		FileDropZone = new Dropzone('#m-dropzone-four', {
			url: '/ajax/uploadFile.php?pid=<?php echo $PERSON_ID?>',
			paramName: "file",
			maxFiles: 10,
			maxFilesize: 36,
			init: function() {
    			this.on("complete", function(file) { 
					if(file.status == 'success') {
						toastr.success('File Added to Record', '', {timeOut: 5000});
						loadFiles();						
					} else {
						toastr.error('Upload Error', '', {timeOut: 5000});
					}
				});
  			}
		});
		
	});
	$('#myFilesModal').on('hidden.bs.modal', function (e) {
		console.log('closed modal');
		$('#m-dropzone-four').removeClass('dropzone');
		FileDropZone.destroy();
	});
	
	$('#RequirePM').bootstrapSwitch({
		onText: 	'YES',
		offText:	'NO',
		onSwitchChange: function(event, state) {
			//console.log(state);
			mApp.block("#quickbackoffice-toggle-area", {
				overlayColor: "#FFFFFF",
				type: "loader",
				state: "success",
				size: "lg"
            });
			$.post('/ajax/ajax.backoffice.php?action=dataUpdate', {
				pid:	'<?php echo $PERSON_ID?>',
				state:	state,
				field:	'RequirePM'
			}, function(data) {
				mApp.unblock("#quickbackoffice-toggle-area");
				if (state) {
					$('#TOSREQ_record').removeClass('m--hide');
				} else {
					$('#TOSREQ_record').addClass('m--hide');
				}
				toastr.success('TOS Agreed Updated', '', {timeOut: 5000});
			});
		}
	});
	
	$('#Monitored').bootstrapSwitch({
		onText: 	'YES',
		offText:	'NO',
		onSwitchChange: function(event, state) {
			//console.log(state);
			mApp.block("#quickbackoffice-toggle-area", {
				overlayColor: "#FFFFFF",
				type: "loader",
				state: "success",
				size: "lg"
            });
			$.post('/ajax/ajax.backoffice.php?action=dataUpdate', {
				pid:	'<?php echo $PERSON_ID?>',
				state:	state,
				field:	'Monitored '
			}, function(data) {
				mApp.unblock("#quickbackoffice-toggle-area");
				if(state) {
					$('#Monitored_record').removeClass('m--hide');
				} else {
					$('#Monitored_record').addClass('m--hide');
				}
				toastr.success('Monitored Record Updated', '', {timeOut: 5000});
			});
		}
	});
	
	$('#VIP').bootstrapSwitch({
		onText: 	'YES',
		offText:	'NO',
		onSwitchChange: function(event, state) {
			//console.log(state);
			mApp.block("#quickbackoffice-toggle-area", {
				overlayColor: "#FFFFFF",
				type: "loader",
				state: "success",
				size: "lg"
            });
			$.post('/ajax/ajax.backoffice.php?action=dataUpdate', {
				pid:	'<?php echo $PERSON_ID?>',
				state:	state,
				field:	'VIP'
			}, function(data) {
				mApp.unblock("#quickbackoffice-toggle-area");
				if(state) {
					$('#VIP_record').removeClass('m--hide');
				} else {
					$('#VIP_record').addClass('m--hide');
				}
				toastr.success('High Priority Client Status Updated', '', {timeOut: 5000});
			});
		}
	});
	$('#HCS').bootstrapSwitch({
		onText: 	'YES',
		offText:	'NO',
		onSwitchChange: function(event, state) {
			//console.log(state);
			mApp.block("#quickbackoffice-toggle-area", {
				overlayColor: "#FFFFFF",
				type: "loader",
				state: "success",
				size: "lg"
            });
			$.post('/ajax/ajax.backoffice.php?action=dataUpdate', {
				pid:	'<?php echo $PERSON_ID?>',
				state:	state,
				field:	'HCS'
			}, function(data) {
				mApp.unblock("#quickbackoffice-toggle-area");
				if(state) {
					$('#HCS_record').removeClass('m--hide');
				} else {
					$('#HCS_record').addClass('m--hide');
				}
				toastr.success('High Priority Client Status Updated', '', {timeOut: 5000});
			});
		}
	});
	loadFiles();
});
function updateNumbers() {
	$('#btn-updateNumbers').prop('disabled', true);
	$('#btn-updateNumbers').html('<i class="fa fa-circle-o-notch fa-spin"></i> Updating...');
	$.get('/cron/cron.totalcollected.php', {
		uid: <?php echo $PERSON_ID?>
	}, function(data) {
		$('#TotalPaid_display').html('<i class="fa fa-usd"></i>'+data.amt);
		$('#TotalPaid_displayDate').html('updated '+data.stamp);
		$.get('/cron/cron.totalpaid.php', {
			uid: <?php echo $PERSON_ID?>
		}, function(data2) {
			$('#TotalSCom_display').html('<i class="fa fa-usd"></i>'+data2.amt);
			$('#TotalSCom_displayDate').html('updated '+data2.stamp);
			$('#btn-updateNumbers').prop('disabled', false);
			$('#btn-updateNumbers').html('Update Numbers <i class="fa fa-code-fork"></i>');
		}, "json");
	}, "json");
	
}
function removeSale(saleID) {
	var choice = confirm('Are you sure you want to remove this sale?');
	if(choice) {
		var confirm2 = prompt('In order to delete you must type "DELETE" (all caps) to complete this action');
		if(confirm2 == 'DELETE') {
			$.post('/ajax/ajax.backoffice.php?action=removeSale', {
				sid:	saleID,
				kiss_token: '<?php echo $SESSION->createToken()?>'
			}, function(data) {
				//filesTable.destroy();
				document.location.reload(true);
			});
		}
	}	
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
function removeHistory(historyID) {
	var choice = confirm('Are you sure you want to remove this history?');
	if(choice) {
			$.post('/ajax/history.php?action=remove', {
				historyID:	historyID,
				kiss_token: '<?php echo $SESSION->createToken()?>'
			}, function(data) {
				refreshPaymentTable();
				$('#myPaymentsModal').modal('hide');
			});	
	}
}
function removePayment() {
	var choice = confirm('Are you sure you want to remove this payment?');
	if(choice) {
		var choice2 = confirm('Are you absolutely sure? This action cannot be undone.');
		if(choice2) {
			$.post('/ajax/payments.php?action=remove', {
				payid:	$('#PaymentInfo_ID').val(),
				kiss_token: '<?php echo $SESSION->createToken()?>'
			}, function(data) {
				refreshPaymentTable();
				$('#myPaymentsModal').modal('hide');
			});					
		}
	}
}

function clearPayment() {
	var choice = confirm('Are you sure you want to clear this payment information?');
	if(choice) {
		var choice2 = confirm('Are you absolutely sure? This action cannot be undone.');
		if(choice2) {
			$.post('/ajax/payments.php?action=clearPayment', {
				payid:	$('#PaymentInfo_ID').val(),
				kiss_token: '<?php echo $SESSION->createToken()?>'
			}, function(data) {
				refreshPaymentTable();
				$('#myPaymentsModal').modal('hide');
			});					
		}
	}
}

function refreshPaymentTable() {
	$.post('/ajax/payments.php?action=refresh', {
		pid:	'<?php echo $PERSON_ID?>'
	}, function(data) {
		//filesTable.destroy();
		$('#paymentFormTable').html(data);
		mApp.init();
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
		if(data.isRefund == 1) {
			$('#isRefund').attr('checked', true);
		} else {
			$('#isRefund').attr('checked', false);
		}
		$('#payStatus_1').attr('checked', true);
		$('#payStatus_2').attr('checked', false);
		$('#payStatus_3').attr('checked', false);
		
		$('.payStatusMarker').each(function() {
			console.log($(this).val()+'|'+data.status);
			if($(this).val() == data.status) {
				$(this).prop('checked', true);
			} else {
				$(this).prop('checked', false);
			}
		});
		//$("#PaymentInfo_Execute").datepicker("setDate", data.pdate);
		$('#PaymentInfo_notes').val(data.notes);
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
function savePayment() {
	// VALIDATE FORM //
	var tst_amount = $('#PaymentInfo_Amount').val();
	var regex  = /^\d+(?:\.\d{0,2})$/;
	var error = 0;
	var errorText = '';
	if(tst_amount == 0.00) {
		error = 1;
		errorText += 'You must enter a valid dollar amount\n';
	}
	if (regex.test(tst_amount)) {
		// do nothing //
	} else {
		error = 1;
		errorText += 'You must enter a valid dollar amount\n';
	}
	
	if ($('#PaymentContract_id').val() == 0) {
		error = 1;
		errorText += 'You must select a contract to attach the payment\n';
	}
	
	if (error == 1) {
		alert(errorText);
	} else {
		var formData = $('#paymentForm').serializeArray();
		$.post('/ajax/payments.php?action=save', formData, function(data) {
			console.log(data);
			if (data.statusChange) {
				$('#myPaymentsModal').modal('hide');
				openPayment(data.paymentID);
				toastr.success('Payment Created', '', {timeOut: 5000});	
			} else {
				toastr.success('Payment Updated', '', {timeOut: 5000});	
			}
			refreshPaymentTable();
		}, "json");
	}
}
function refreshPayments() {
		
	
}
function loadFiles() {
	$.post('/ajax/ajax.backoffice.php?action=getFilesTable', {
		pid:	'<?php echo $PERSON_ID?>'
	}, function(data) {
		//filesTable.destroy();
		$('#filesTableDisplay').html(data);
		mApp.init();
	});
}
function removeFile(fid) {
	var choice = confirm('Are you sure you want to remove this file? This action cannot be undone.');
	if(choice) {
		$.post('/ajax/ajax.backoffice.php?action=rmFile', {
			fid: fid
		}, function(data) {
			toastr.success('File Removed', '', {timeOut: 5000});
			loadFiles();
		});		
	}
}
function copyPaymentLink() {
	$("#payment_embedCode").select();
    document.execCommand('copy');
}
function viewSale(saleID) {
	$('#mySaleModal').modal('show');
	$('#mySaleModal .modal-body').html('<div class="m-loader" style="width: 30px; display: inline-block;"></div>&nbsp;Loading Sale Information...');
	$.post('/ajax/ajax.backoffice.php?action=getSale', {
		saleID: saleID
	}, function(data) {
		$('#mySaleModal .modal-body').html(data);
		$('.PayStatusCheck').bootstrapSwitch({
			labelText:	'Received',
			onText: 	'YES',
			offText:	'NO',
			labelWidth:	50,
			onSwitchChange: function(event, state) {
				var payID = $(this).attr('data-id');
				var saleID = $(this).attr('data-sale');
				console.log(payID+'|'+state);
				if(state) {
					var payState = 1;
				} else {
					var payState = 0;
				}
				$.post('/ajax/ajax.backoffice.php?action=payUpdate', {
					pid:		'<?php echo $PDATA['Person_id']?>',
					payment:	payID,
					paystatus:	payState,
					sale:		saleID
				}, function(data) {				
					toastr.success('Sale Updated', '', {timeOut: 5000});
					$('blanaceFor_'+data.pay_id).html(data.balance);
				}, "json");
			}
		});
	});
}
</script>