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

$REPORT_ID = 47;

if (isset($_POST['filterDates'])) {
	$dateParts = explode(" - ", $_POST['filterDates']);
	$startEpoch = strtotime($dateParts[0]);
	$enderEpoch = strtotime($dateParts[1]) + 86399;
	//echo "Custom:".$startEpoch."|".$enderEpoch;
	$dateParameters = array($startEpoch, $enderEpoch);	
	$dayDiff = (($enderEpoch - $startEpoch) / 86400) - 1;
	$dateDaysPreload = round($dayDiff);	
	$offices = $_POST['Offices_id'];
	$preSelect_users = $_POST['system_user_id'];	
} else {
	$dateParameters = array((time() - (7 * 86400)), time());
	$startEpoch = time() - (7 * 86400);
	$enderEpoch = time();
	$dateDaysPreload = 7;
	$preSelect_users = array($_SESSION['system_user_id']);
	//echo "Default:".$startEpoch."|".$enderEpoch;
	$sql = "SELECT * FROM Offices ORDER BY Offices_id";
	$snd = $DB->get_multi_result($sql);
	foreach($snd as $dta):
		$offices[] = $dta['Offices_id'];
	endforeach;
	$_POST['system_user_id'] = $preSelect_users;
}

ob_start();
$o_sql = "SELECT * FROM Offices ORDER BY office_Name ASC";
$o_snd = $DB->get_multi_result($o_sql);
foreach($o_snd as $o_dta):
	?><option value="<?php echo $o_dta['Offices_id']?>" <?php echo ((in_array($o_dta['Offices_id'], $offices)? 'selected':''))?>><?php echo $o_dta['office_Name']?></option><?php
endforeach;
$officeSelect = ob_get_clean();

$uc_sql = "SELECT* FROM UserClasses ORDER BY userClass_name ASC";
$uc_snd = $DB->get_multi_result($uc_sql);
foreach($uc_snd as $uc_dta):
	$groupID = $uc_dta['userClass_id'];
	$u_sql = "SELECT * FROM Users WHERE userClass_id='".$groupID."' AND userStatus='1'";
	$u_fnd = $this->db->get_multi_result($u_sql, true);
	if($u_fnd > 0):
		$u_snd = $this->db->get_multi_result($u_sql);
		foreach($u_snd as $u_dta):
			settype($u_dta['user_id'], 'integer');
			$idArray[] = $u_dta['user_id'];
		endforeach;
		$linkValue = json_encode($idArray);
		$quickClick[] = '<a href="javascript:;" onclick="addUsersToReport('.addslashes($linkValue).');">'.$uc_dta['userClass_name'].'</a>';
	endif;
	unset($idArray);	
endforeach;

?>
<form action="/viewreport/<?php echo $REPORT_ID?>" method="post">
<div class="row">
	<div class="col-6">
		<div class="form-group m-form__group">
        	<label>User(s)&nbsp;&nbsp; <?php echo implode(" | ", $quickClick)?></label>         
            <select class="form-control m-select2" id="system_user_id" name="system_user_id[]" multiple="multiple">
            	<?php echo $RECORD->options_userSelect($preSelect_users)?>
            </select>
            <span class="m-form__help">select the users you want to include in this report.</span>
        </div> 
    </div>
    <div class="col-6">
    	<div class="form-group m-form__group">
	        <label>Date Filter</label>
    	    <div class="input-group m-input-group">            
        	    <span class="input-group-addon" id="basic-addon1"><i class="fa fa-calendar"></i></span>
            	<input type="text" class="form-control m-input input-sm" id="filterDates" name="filterDates" placeholder="dates">
	            <span class="input-group-btn">
    	            <button type="submit" class="btn btn-default">Apply</button>
        	    </span>
        	</div>
		</div>
    </div>
</div>
</form>
<?php
//print_r($_POST);
foreach($_POST['system_user_id'] as $user):
$SQL = "
SELECT
	*
FROM
	UserLog
WHERE
	UserLog_userId='".$user."'
AND
	(UserLog_date >= '".$startEpoch."' AND UserLog_date <= '".$enderEpoch."')
ORDER BY
	UserLog_date ASC
";
//echo $SQL;
$SND = $DB->get_multi_result($SQL);
$SND = $DB->get_multi_result($SQL);
if(isset($SND['empty_result'])) {
	$found = 0;
} else {
	$found = count($SND);		
}
?>
<table class="table table-bordered m-table m-table--border-brand m-table--head-bg-brand table-sm">
<thead>
	<tr>
    	<th colspan="4">
        	<?php echo $RECORD->get_FulluserName($user)?> | <?php echo $found?> 
        </th>
    <tr>
    	<th>Date</th>
        <th>Action</th>
        <th>Type</th>
        <th>IP Address</th>
	</tr>
</thead>
<tbody>
<?php
//echo $SQL;
if($found != 0) {
	foreach($SND as $DTA):
	?>
    <tr>
    	<td width="10%"><?php echo date("m/d/y h:ia", $DTA['UserLog_date'])?></td>
        <td width="70%"><?php echo stripslashes($DTA['UserLog_desc'])?></td>
        <td width="10%"><?php echo $DTA['UserLog_recordType']?></td>
        <td width="10%"><?php echo $DTA['UserLog_ipAddress']?></td>    
    </tr>
    <?php
	endforeach;
	
}
?>
</tbody>
</table>
<?php
endforeach;
?>


<script>
$(document).ready(function(e) {
	$("#system_user_id").select2({
		placeholder: "Select users(s)",
		allowClear: !0
	});
	
	var start = moment().subtract(<?php echo $dateDaysPreload?>, 'days');
	var end = moment();	
	$('#filterDates').daterangepicker({
		buttonClasses: 'm-btn btn',
		applyClass: 'btn-primary',
		cancelClass: 'btn-secondary',
		startDate: start,
		endDate: end,
		ranges: {
		   'Last 24 Hours': [moment().subtract(24, 'hours'), moment()],
		   'Last 72 Hours': [moment().subtract(72, 'hours'), moment()],
		   'Last 7 Days': [moment().subtract(7, 'days'), moment()],
		   'Last 30 Days': [moment().subtract(30, 'days'), moment()],
		   'Last 60 Days': [moment().subtract(60, 'days'), moment()],
		   'Last 90 Days': [moment().subtract(90, 'days'), moment()],
		   //'Last 6 Months': [moment().subtract(6, 'months'), moment()],
		   //'Last 12 Months': [moment().subtract(12, 'months'), moment()],
		   'This Week': [moment().startOf('week'), moment().endOf('week')],
		   'Last Week': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
		   'This Month': [moment().startOf('month'), moment().endOf('month')],
		   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		}
	});
});

function addUsersToReport(userObject) {
	console.log(userObject);
	//$('#system_user_id').select2('val', userObject);
	var Values = new Array();
	for(i=0; i<userObject.length; i++) {
		console.log(userObject[i]);
		Values.push(userObject[i]);		
	}
	$("#system_user_id").val(Values).trigger('change');
}
</script>