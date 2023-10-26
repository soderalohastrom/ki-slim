<?php
include_once("class.record.php");
$RECORD = new Record($DB);

function options_IncomeGroups($myChoice=array()) {
	global $DB;
	if($myChoice == ''):
		$myChoice = array();
	endif;
	$ic_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id = 631 ORDER BY QuestionsAnswers.QuestionsAnswers_order ASC";
	$ic_snd = $DB->get_multi_result($ic_sql);
	ob_start();
	foreach($ic_snd as $ic_dta):
		?><option value="<?php echo $ic_dta['QuestionsAnswers_value']?>" <?php echo (in_array($ic_dta['QuestionsAnswers_value'], $myChoice)? 'selected':'')?>><?php echo $ic_dta['QuestionsAnswers_value']?></option><?php
	endforeach;
	$ic_options = ob_get_clean();
	return $ic_options;
}
?>
<div class="m-content">
	<div class="m-portlet m-portlet--head-sm">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
                        Lead Delivery
                        <small>who gets assigned to whom</small>
                    </h3>
                </div>
            </div>
        </div>
        <form class="m-form m-form--fit m-form--label-align-right" action="javascript:saveDeliverySchedule();" method="post" id="deliveryForm">
        <div class="m-portlet__body">               
<?php
$sql = "SELECT * FROM Offices ORDER BY office_Name ASC";
$snd = $DB->get_multi_result($sql);
foreach($snd as $dta):
	?>
    <div class="form-group m-form__group row">
        <div class="col-lg-2">
            <label>Location:</label>
            <p class="form-control-static"><strong><?php echo $dta['office_Name']?></strong></p>            
        </div>                
        <?php if(($dta['office_lat'] != '') && ($dta['office_lng'] != '')): ?> 
        <input type="hidden" name="OfficeIDs[]" value="<?php echo $dta['Offices_id']?>" />       
        <div class="col-lg-3">
            <label>Assigned To:</label>
            <div class="input-group m-input-group m-input-group--square">
                <span class="input-group-addon">
                    <i class="la la-user"></i>
                </span>
                <select class="form-control m-select2 Assigned_userID" id="Assigned_userID" name="DefaultAssignedUser[<?php echo $dta['Offices_id']?>]">
					<?php echo $RECORD->options_userSelectAll(array($dta['DefaultAssignedUser']))?>
                </select>
            </div>
            <span class="m-form__help"><small>select user the lead matching these paramaters will be assigned to</small></span>
        </div>
        <div class="col-lg-3">
            <label>Income:</label>
            <div class="input-group m-input-group m-input-group--square">
                <span class="input-group-addon">
                    <i class="la la-usd"></i>
                </span>
                <select class="form-control m-select2 prQuestion_631" id="prQuestion_631" name="prQuestion_631[<?php echo $dta['Offices_id']?>][]" multiple="multiple">
					<?php echo options_IncomeGroups(json_decode($dta['DefaultIncomeRanges'], true))?>
                </select>
            </div>
            <span class="m-form__help"><small>those mathing selected values will be delivered to Assigned Market Director. Leave blank for all</small></span>
        </div>
        <div class="col-lg-3">
            <label class="">Distance</label>
            <input type="number" name="DefaultAssignedMinDistance[<?php echo $dta['Offices_id']?>]" class="form-control m-input" style="max-width:100px;" min="0" max="2000" step="10" value="<?php echo $dta['DefaultAssignedMinDistance']?>">
            <span class="m-form__help"><small>Records within this distance will be assigned<br />(leave 0 for assignment if closest regardless of distance)</small></span>
        </div>
         
        <?php else: ?>
        <div class="col-lg-10">
        	<div class="alert alert-warning" role="alert">
                <strong>
                    Warning!
                </strong>
                This office lacks the necessary information (Lat/Lng) to properly be included in geo-locating the nearest office.
            </div>        
        </div>
        <?php endif; ?>        
    </div>
    <?php	
endforeach;



?>        
        
        </div>        
        <div class="m-portlet__foot m-portlet__foot--fit">
            <div class="m-form__actions">
                <button type="submit" class="btn btn-primary">
                    Save
                </button>
            </div>
        </div>
        </form>
    </div>

</div>
<script>
$(document).ready(function(e) {
    $('.Assigned_userID').select2({
        theme: "classic",
		placeholder: "Select Saleperson(s)",
		allowClear: !0,
	});
	$('.prQuestion_631').select2({
        theme: "classic",
		placeholder: "Select Saleperson(s)",
		allowClear: !0,
	});
});
function saveDeliverySchedule() {
	var formData = $('#deliveryForm').serializeArray();
	$.post('/ajax/leaddelivery.php?action=submit', formData, function(data) {
		alert('Lead Delivery Schedule Updated');		
	});
	
}


</script>