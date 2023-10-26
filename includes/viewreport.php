<?php
include_once("class.record.php");
include_once("class.reports.php");
$RECORD = new Record($DB);
$REPORT = new Reports($DB, $RECORD);

$REPORT_ID = $pageParamaters['params'][0];

$r_sql = "SELECT * FROM Reports WHERE Report_id='".$REPORT_ID."'";
$r_snd = $DB->get_single_result($r_sql);
$R_DATA = $r_snd;
if($R_DATA['Report_type'] == 1) {
	
	$rCONFIG = json_decode($R_DATA['Report_config'], true);
	$groupBy = $rCONFIG['groupBy'];
	$sortBy = $rCONFIG['sortBy'];
	$sortDir = $rCONFIG['sortDir'];
	$customReport = false;
	$showDateFilter = true;
	$dateFieldFound = true;
	
	for($i=0; $i<count($rCONFIG['filters']['fields']); $i++) {
		if (substr($rCONFIG['filters']['option_values'][$i], 0, 5) == 'EPOCH') {
			$epochBreak_part = str_replace("EPOCH(", "", $rCONFIG['filters']['option_values'][$i]);
			$epochBreak = str_replace(")", "", $epochBreak_part);
			$epochTimestamp = time() - ($epochBreak * 86400);
			$dateFieldFound = true;
			$dateDaysPreload = $epochBreak;
			
			//echo "FOUND DATE FIELD:".$dateFieldFound."<br>\n";
			//echo "DAY BREAK: ".$dateDaysPreload."<br>\n";
			//print_r($_POST);
			if (isset($_POST['filterDates'])) {
				$dateParts = explode(" - ", $_POST['filterDates']);
				$startEpoch = strtotime($dateParts[0]);
				$enderEpoch = strtotime($dateParts[1]) + 86399;
				//echo "Custom:".$startEpoch."|".$enderEpoch;
				$dateParameters = array($startEpoch, $enderEpoch);
				
				$dayDiff = (($enderEpoch - $startEpoch) / 86400) - 1;
				$dateDaysPreload = round($dayDiff);			
			} else {
				$dateParameters = array((time() - ($epochBreak * 86400)), time());
				$startEpoch = time() - ($epochBreak * 86400);
				$enderEpoch = time();
				//echo "Default:".$startEpoch."|".$enderEpoch;
			}
		}		
	}
} else {
	$incluse_file = $R_DATA['Report_config'];
	$customReport = true;
	if($R_DATA['Report_timescope'] != 0) {
		$showDateFilter = true;
		$dateFieldFound = true;		
		if (isset($_POST['filterDates'])) {
			$dateParts = explode(" - ", $_POST['filterDates']);
			$startEpoch = strtotime($dateParts[0]);
			$enderEpoch = strtotime($dateParts[1]) + 86399;
			//echo "Custom:".$startEpoch."|".$enderEpoch;
			$dateParameters = array($startEpoch, $enderEpoch);			
			$dayDiff = (($enderEpoch - $startEpoch) / 86400) - 1;
			$dateDaysPreload = round($dayDiff);			
		} else {
			$dateParameters = array((time() - ($epochBreak * 86400)), time());
			$startEpoch = time() - ($epochBreak * 86400);
			$enderEpoch = time();
			//echo "Default:".$startEpoch."|".$enderEpoch;
			$dateDaysPreload = $R_DATA['Report_timescope'];
		}
	} else {
		$showDateFilter = false;
		$dateFieldFound = false;
	}
}
//print_r($dateParameters);
//$dateParam_1 = date("m/d/Y", $startEpoch);
//$dateParam_2 = date("m/d/Y", $enderEpoch);
if(true) : //$REPORT->can_see_report($_SESSION['system_user_id'], $REPORT_ID)):
?>
<link href="//www.amcharts.com/lib/3/plugins/export/export.css" rel="stylesheet" type="text/css" />
<script src="//www.amcharts.com/lib/3/amcharts.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/serial.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/radar.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/pie.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/plugins/tools/polarScatter/polarScatter.min.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/plugins/animate/animate.min.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/plugins/export/export.min.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/themes/light.js" type="text/javascript"></script>
<script src="/assets/vendors/custom/sortable-master/Sortable.min.js" type="text/javascript"></script>
<div class="m-content">
    <div class="m-portlet m-portlet--mobile">		
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<span class="m-portlet__head-icon">
						<i class="flaticon-graphic-1"></i>
					</span>
					<h3 class="m-portlet__head-text">
        	            <?php echo $R_DATA['Report_name']?>
                    </h3>
				</div>
			</div>
			<div class="m-portlet__head-tools">
				<?php if($showDateFilter): ?>
				<form action="/viewreport/<?php echo $REPORT_ID?>" method="post">
				<div class="input-group m-input-group">					
					<span class="input-group-addon" id="basic-addon1"><i class="fa fa-calendar"></i></span>
					<input type="text" class="form-control m-input input-sm" id="filterDates" name="filterDates" placeholder="dates">
					<span class="input-group-btn">
						<button type="submit" class="btn btn-default">Apply Filter</button>
					</span>
					<?php if(($R_DATA['Report_createdBy'] == $_SESSION['system_user_id']) || ($REPORT->is_report_superuser($_SESSION['system_user_id']))): ?>
					<span class="input-group-btn">						
						<a href="/reports/<?php echo $REPORT_ID?>" class="btn btn-default">Edit Report <i class="fa fa-edit"></i></a>
					</span>
					<?php endif; ?>
				</div>
				</form>
				<?php else: ?>
					<div class="text-right"><a href="/reports/<?php echo $REPORT_ID?>" class="btn btn-default">Edit Report <i class="fa fa-edit"></i></a></div>
				<?php endif; ?>
			</div>
		</div>
        <div class="m-portlet__body">
			
            <?php
			 echo $REPORT->genReport($REPORT_ID, $dateParameters)?>            
        </div>
    </div>
</div>
<script>
<?php if($dateFieldFound): ?>
var start = moment().subtract(<?php echo $dateDaysPreload?>, 'days');
var end = moment();	
$('#filterDates').daterangepicker({
	buttonClasses: 'm-btn btn',
	applyClass: 'btn-primary',
	cancelClass: 'btn-secondary',
	startDate: start,
	endDate: end,
	ranges: {
	   'Last 7 Days': [moment().subtract(7, 'days'), moment()],
	   'Last 30 Days': [moment().subtract(30, 'days'), moment()],
	   'Last 60 Days': [moment().subtract(60, 'days'), moment()],
	   'Last 90 Days': [moment().subtract(90, 'days'), moment()],
	   'Last 6 Months': [moment().subtract(6, 'months'), moment()],
	   'Last 12 Months': [moment().subtract(12, 'months'), moment()],
	   'This Month': [moment().startOf('month'), moment().endOf('month')],
	   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	}
});
<?php endif; ?>
</script>
<?php
else:
?><div class="alert alert-danger">You do not have access to this report</div><?php
endif;