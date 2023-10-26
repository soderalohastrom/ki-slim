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
	PersonsPrefs.*,
	PersonsProfile.*,
	PersonsColors.Color_hex,
	PersonsColors.Color_title,
	(SELECT CONCAT(PersonsNotes_type,' &gt; ',PersonsNotes_header) FROM PersonsNotes WHERE PersonsNotes_personID=Persons.Person_id ORDER BY PersonsNotes_dateCreated DESC LIMIT 1) as LastNoteAction
FROM
	Persons
	LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons. Person_id
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
	LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
	LEFT JOIN PersonsColors ON PersonsColors.Color_id=Persons.Color_id
WHERE
	PersonsTypes_id IN (4, 6)
AND
	PersonsProfile.prQuestion_1711 != ''
GROUP BY
	Persons.Person_id
ORDER BY
	Persons.LastName ASC
";
//echo $sql;

$snd = $DB->get_multi_result($sql);
foreach($snd as $dta):
	$LAST_ACTION = json_decode($dta['LastNoteAction'], true);
	$SinceLastAction = round(((time() - $LAST_ACTION['hDate']) / 86400), 0);
	$SinceLastIntro = round(((time() - $dta['LastIntroDate']) / 86400), 0);
	
	$successVisibleString = substr($dta['prQuestion_1711'], 0, 35);
	?>
	<tr>
        <td><a href="/profile/<?php echo $dta['Person_id']?>" class="m-link"><?php echo $RECORD->get_personName($dta['Person_id'])?></a>&nbsp;&nbsp;<a href="/profile/<?php echo $dta['Person_id']?>" class="m-link" target="_blank"><i class="la la-external-link-square"></i></a></td>
        <td><?php echo $dta['Gender']?></td>
        <td><?php echo $RECORD->get_personAge($dta['DateOfBirth'])?></td>
        <td><?php echo $dta['City']?></td>
        <td><?php echo $dta['State']?></td>
        <td><?php echo $dta['prQuestion_631']?></td>
        <td><div class="truncate" data-container="body" data-toggle="m-popover" data-placement="top" data-skin="dark" data-content="<?php echo $dta['prQuestion_1711']?>"><?php echo $successVisibleString ?></div></td>
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
        <td><?php echo $RECORD->get_userName($dta['Matchmaker_id'])?><?php echo (($dta['Matchmaker2_id'] != 0)? '<br>'.$RECORD->get_userName($dta['Matchmaker2_id']):'')?></td>
        <td><?php echo $RECORD->get_userName($dta['Assigned_userID'])?></td>
        <td><?php echo $RECORD->get_personType($dta['Person_id'])?></td>
    	<!-- <td><?php echo $dta['prQuestion_657']?></td> -->
        
        <!-- <td>
        	<?php if(($dta['Color_id'] != 0) && ($dta['Color_hex'] != NULL)): ?>
            <span class="m-badge m-badge--metal m-badge--wide" style="background-color:<?php echo $dta['Color_hex']?>;"><?php echo $dta['Color_title']?></span>
            <?php else: ?>
            &nbsp;
            <?php endif; ?>
		</td> -->
        
    </tr>
    <?php
endforeach;
$tbody = ob_get_clean();

?>
<script src="/assets/vendors/custom/tablesorter/dist/js/jquery.tablesorter.min.js" type="text/javascript"></script>
<link href="/assets/vendors/custom/tablesorter/dist/css/theme.bootstrap_4.min.css" rel="stylesheet" type="text/css" />
<style>
.truncate {
	width: 150px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
</style>
<form id="filterSearhForm" class="m-form m-form--fit m-form--label-align-right" action="" method="post" style="margin-bottom:10px;">
<div class="row">
    <div class="col-4">
    	<h2 style="padding-top:20px;">Total Records Found: <?php echo count($snd)?></h2>
	</div>
    <div class="col-8">
        <div class="alert alert-info" role="alert">
        	This list is all records of Active or Frozen members who have a value in the "Success Fee" textarea field. Haver of the success fee value to see the whole text for the success fee field.
        </div>
	</div>           
</div>
</form>
<table class="table table-striped m-table" id="leadTimelineTable">
<thead>
	<tr>
        <th>Record</th>
        <th>Gen</th>
        <th>Age</th>  
        <th>City</th>
        <th>State</th>
        <th>Income</th>
        <th width="100" style="width:100px;">Success Fee</th> 
        <th width="100" style="width:100px;">Start Date</th>
        <th width="100" style="width:100px;">End Date</th> 
        <th>Matchmaker(s)</th>
        <th>Salesperson</th>
        <th>Record Type</th>
        <!-- <th>Member Type</th> -->
        <!-- <th>Flag</th> -->
        
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