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

if(!isset($_POST['PersonsTypes_id'])):
	$_POST['PersonsTypes_id'] = 4;
endif;

if(!isset($_POST['DaysSince'])):
	$_POST['DaysSince'] = 60;
endif;



ob_start();
$daysEpoch = time() - (86400 * $_POST['DaysSince']);

$sql = "
SELECT
	Persons.*,
	Addresses.City,
	Addresses.State,
	PersonsPrefs.prefQuestion_age_floor,
	PersonsPrefs.prefQuestion_Gender,
	PersonsProfile.prQuestion_657,
	PersonsProfile.prQuestion_676,
	PersonsProfile.prQuestion_677,
	

FROM
	Persons
	LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons. Person_id
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
	LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
WHERE
	PersonsTypes_id='".$_POST['PersonsTypes_id']."'
AND
	LastIntroDate <= '".$daysEpoch."'
GROUP BY
	Persons.Person_id
ORDER BY
	LastIntroDate ASC
";
//echo $sql;

$snd = $DB->get_multi_result($sql);
foreach($snd as $dta):
	$LAST_ACTION = json_decode($dta['LastNoteAction'], true);
	$SinceLastAction = round(((time() - $LAST_ACTION['hDate']) / 86400), 0);
	$SinceLastIntro = round(((time() - $dta['LastIntroDate']) / 86400), 0);
	?>
	<tr>
        <td><a href="/profile/<?php echo $dta['Person_id']?>" class="m-link"><?php echo $RECORD->get_personName($dta['Person_id'])?></a>&nbsp;&nbsp;<a href="/profile/<?php echo $dta['Person_id']?>" class="m-link" target="_blank"><i class="la la-external-link-square"></i></a></td>
        <td><?php echo $dta['Gender']?></td>
        <td><?php echo $RECORD->get_personAge($dta['DateOfBirth'])?></td>
        <td><?php echo $RECORD->get_userName($dta['Matchmaker_id'])?><?php echo (($dta['Matchmaker2_id'] != 0)? '<br>'.$RECORD->get_userName($dta['Matchmaker2_id']):'')?></td>
        <?php if($dta['LastIntroDate'] == 0): ?>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        <?php else: ?>
            <td><a href="/intro/<?php echo $dta['LastIntroID']?>" class="m-link" target="_blank"><?php echo date("Y-m-d", $dta['LastIntroDate'])?></a></td>
        	<td><?php echo $SinceLastIntro.' <sup>days</sup>'?></td>    
        <?php endif; ?>
        <td><?php echo (($LAST_ACTION['hDate'] != 0)? $SinceLastAction.' <sup>days</sup>':'')?></td>
        <td><?php echo $dta['City']?></td>
        <td><?php echo $dta['State']?></td>
        <td><?php echo str_replace("|", "-", $dta['prefQuestion_age_floor'])?></td>
        <!--<td><?php echo $RECORD->get_personType($dta['Person_id'])?></td>-->
    	<td><?php echo $dta['prQuestion_657']?></td>
        <td>
        	<?php 
			echo $RECORD->get_personsColorSpan($dta['Person_id']);
			?>
		</td>
        <td>
			<?php if($dta['prQuestion_676'] != 0): ?>
            	<?php echo date("Y-m-d", $dta['prQuestion_676'])?>
            <?php else: ?>
            	&nbsp;
            <?php endif; ?>
		</td>
        <td>
			<?php if($dta['prQuestion_677'] != 0): ?>
            	<?php echo date("Y-m-d", $dta['prQuestion_677'])?>
            <?php else: ?>
            	&nbsp;
            <?php endif; ?>
		</td>
    </tr>
    <?php
endforeach;
$tbody = ob_get_clean();

//print_r($_POST);
?>
<script src="/assets/vendors/custom/tablesorter/dist/js/jquery.tablesorter.min.js" type="text/javascript"></script>
<link href="/assets/vendors/custom/tablesorter/dist/css/theme.bootstrap_4.min.css" rel="stylesheet" type="text/css" />
<form id="filterSearhForm" class="m-form m-form--fit m-form--label-align-right" action="/viewreport/174" method="post" style="margin-bottom:10px;">
<div class="row">
    <div class="col-4">
    	<h2 style="padding-top:20px;">Total Records Found: <?php echo count($snd)?></h2>
	</div>
    <div class="col-3">
        <div class="form-group m-form__group">
            <label>Record Type</label>
            <?php
            if(isset($_POST['PersonsTypes_id'])) {
                $preSelected = array($_POST['PersonsTypes_id']);
            } else {
                $preSelected = array();	
            }
            ?>        
            <select class="form-control m-select2" id="PersonsTypes_id" name="PersonsTypes_id" >
<?php
$sql = "SELECT * FROM PersonTypes WHERE PersonsTypes_id NOT IN (1, 2, 3, 13, 11, 9) ORDER BY PersonsTypes_order";
$snd = $DB->get_multi_result($sql);
foreach($snd as $dta):
	?><option value="<?php echo $dta['PersonsTypes_id']?>" <?php echo ((in_array($dta['PersonsTypes_id'], $preSelected))? 'selected':'')?>><?php echo $dta['PersonsTypes_text']?></option><?php
endforeach;
?>				
            </select>
        </div>
	</div>
    <div class="col-3">
    	<div class="form-group m-form__group">
	        <label>Since Last Intro</label>
    	    <div class="input-group m-input-group">            
        	    <span class="input-group-addon" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                <input id="m_touchspin_1" type="text" class="form-control" value="<?php echo $_POST['DaysSince']?>" name="DaysSince">
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
<table class="table table-sm table-responsive" id="leadTimelineTable">
<thead>
	<tr>
        <th>Record</th>
        <th>Gen</th>
        <th>Age</th>    
        <th>Matchmaker</th>
        <th width="90" style="width:90px;">Last Intro</th>
        <th>Since Intro</th>
        <th>Last Action</th>
        <th>City</th>
        <th>State</th>
        <th>Seeking</th>
        <!--<th>Record Type</th>-->
        <th>Member Type</th>
        <th>Flag</th>
        <th width="90" style="width:90px;">Start Date</th>
        <th width="90" style="width:90px;">End Date</th>
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
	$("#m_touchspin_1").TouchSpin({
		buttondown_class: "btn btn-secondary",
		buttonup_class: "btn btn-secondary",
		min: 1,
		max: 365,
		step: 10,
		decimals: 0,
		boostat: 10,
		maxboostedstep: 10,
		postfix: "days"
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
	
	$('#PersonsTypes_id').select2({
		placeholder: "Select Record Type(s)",
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



