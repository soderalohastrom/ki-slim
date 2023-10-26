<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.reports.php");
include_once("class.matching.php");
$DB = new database();
$DB->connect();

$RECORD = new Record($DB);
$REPORTS = new Reports($DB, $RECORD);
$MATCHING = new Matching($DB, $RECORD);

$DayMark = 30;
$dateDaysPreload = 30;

if(!isset($_POST['Assigned_userID'])):
	$_POST['Assigned_userID'] = '-1';
endif;

if(!isset($_POST['filterDates'])):
	$date2 = date("m/d/Y");
	$date1 = date("m/d/Y", time() - (((60 * 60) * 24) * $dateDaysPreload));
	$_POST['filterDates'] = $date1.' - '.$date2;
endif;


if (isset($_POST['filterDates'])) {
	$dateParts = explode(" - ", $_POST['filterDates']);
	$startEpoch = strtotime($dateParts[0]);
	$enderEpoch = strtotime($dateParts[1]) + 86399;
}


ob_start();
$sql = "
SELECT 
* 
FROM 
Persons 
WHERE 
PersonsTypes_id='3' 
AND AssignedDate != '0'
";
if($_POST['Assigned_userID'] != '-1'):
$sql .= "AND Assigned_userID='".$_POST['Assigned_userID']."' ";
endif;
$sql .= "AND (AssignedDate >= '".$startEpoch."' AND AssignedDate <= '".$enderEpoch."') ";

$sql .= "ORDER BY 
	AssignedDate DESC
";
//echo $sql."<br>\n";
$snd = $DB->get_multi_result($sql);
foreach($snd as $dta):
	$assinged_since = $dta['AssignedDate'];
	$now = time();
	$diffSeconds = $now - $assinged_since; 
	$numberDays = round((($diffSeconds / 60) / 60) / 24);
	$remain = $DayMark - $numberDays;
	$LAST_ACTION = json_decode($dta['LastNoteAction'], true);
	$SinceLastAction = round(((time() - $LAST_ACTION['hDate']) / 86400), 0);
	?>
	<tr>
    	<td><?php echo $dta['Person_id']?></td>
        <td><a href="/profile/<?php echo $dta['Person_id']?>" class="m-link"><?php echo $RECORD->get_personName($dta['Person_id'])?></a>&nbsp;&nbsp;<a href="/profile/<?php echo $dta['Person_id']?>" class="m-link" target="_blank"><i class="la la-external-link-square"></i></a></td>
        <td><?php echo $RECORD->get_userName($dta['Assigned_userID'])?></td>
        <td><?php echo date("Y-m-d", $dta['AssignedDate'])?></td>
        <td><?php echo $RECORD->get_date_diff(date("m/d/Y H:i:s"), date("m/d/Y H:i:s", $dta['AssignedDate']))?></td>
    	<td><?php echo (($LAST_ACTION['hDate'] != 0)? date("Y-m-d", $LAST_ACTION['hDate']):'')?></td>
        <td><?php echo (($LAST_ACTION['hDate'] != 0)? $SinceLastAction.' days':'')?></td>
        <td><?php echo $LAST_ACTION['hType']?></td>
        <td><span class="<?php echo (($remain > 0)? 'm--font-success':'m--font-danger')?>"><?php echo $remain?></span></td>
    </tr>
    <?php
endforeach;
$tbody = ob_get_clean();

//print_r($_POST);
?>
<script src="/assets/vendors/custom/tablesorter/dist/js/jquery.tablesorter.min.js" type="text/javascript"></script>
<link href="/assets/vendors/custom/tablesorter/dist/css/theme.bootstrap_4.min.css" rel="stylesheet" type="text/css" />
<form id="filterSearhForm" class="m-form m-form--fit m-form--label-align-right" action="/viewreport/61" method="post" style="margin-bottom:10px;">
<div class="row">
    <div class="col-4">&nbsp;</div>
    <div class="col-3">
        <div class="form-group m-form__group">
            <label>Sales Rep</label>
            <?php
            if(isset($_POST['Assigned_userID'])) {
                $preSelected = array($_POST['Assigned_userID']);
            } else {
                $preSelected = array();	
            }
            ?>        
            <select class="form-control m-select2" id="Assigned_userID" name="Assigned_userID" >
                <option value="-1" <?php echo ((in_array('-1', $preSelected))? 'selected':'')?>>ALL</option>
				<?php echo $RECORD->options_userSelect($preSelected)?>
            </select>
        </div>
	</div>
    <div class="col-3">
    	<div class="form-group m-form__group">
	        <label>Assigned</label>
    	    <div class="input-group m-input-group">            
        	    <span class="input-group-addon" id="basic-addon1"><i class="fa fa-calendar"></i></span>
            	<input type="text" class="form-control m-input input-sm" id="filterDates" name="filterDates" placeholder="Date Range" value="<?php echo $_POST['filterDates']?>">
        	</div>
		</div>
	</div>
    <div class="col-2">
    	<div class="form-group m-form__group">
	        <label>&nbsp;</label>
    	    <div class="input-group m-input-group">
    			<button type="submit" class="btn btn-default">Apply Filters</button>
			</div>
		</div>
	</div>           
</div>
</form>
<table class="table table-condensed" id="leadTimelineTable">
<thead>
	<tr>
    	<th>ID</th>
        <th>Lead/Person</th>
        <th>Salesperson</th>
        <th>Assigned</th>
        <th>Since Assigned</th>
        <th>Last Action Date</th>
        <th>Since Last Action</th>
        <th>Last Action</th>
        <th>Remain</th>
	</tr>
</thead>
<tbody>
<?php echo $tbody?>
</tbody>
</table>
<script>
var dtable;
$(document).ready(function(e) {
	$("#leadTimelineTable").tablesorter({
		sortList: [[5,0]] 	
	});
	/*	
	dtable = $("#leadTimelineTable").mDatatable(
		{
			search: {
			input: $("#generalSearch")
		},
		columns: [{
			field: "Remain",
			type: "number"
		},{
			field: "ID",
			width: 45,
			sortable: false
		},{
			field: "Lead/Person",
			width: 200
		},{
			field: "Last Action Date",
			sortable: true
		},{
			field: "Since Assigned",
			sortable: false
		},{
			field: "Since Last Action",
			sortable: false
		}],
		footer: true,
		pagination: true,
		data: {
			saveState: {
            	cookie: false,
            	webstorage: false
        	}
		}
	});
	
	// Sort by columns 1 and 2 and redraw
	dtable.sort( [[ "Last Action Date", 'asc' ]] );
	*/
	
	$('#Assigned_userID').select2({
		placeholder: "Select Salesperson(s)",
		allowClear: !0
	});
	<?php if($_POST['Assigned_userID'] == ''):?>
	$("#Assigned_userID").select2("val", "");
	<?php endif; ?>
	
	//var start = moment().subtract(<?php echo $dateDaysPreload?>, 'days');
	//var end = moment();	
	$('#filterDates').daterangepicker({
		buttonClasses: 'm-btn btn',
		applyClass: 'btn-primary',
		cancelClass: 'btn-secondary',
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
</script>	            



