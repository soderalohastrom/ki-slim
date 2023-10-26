<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.reports.php");
include_once("class.encryption.php");
include_once("class.sessions.php");

$DB = new database();
$DB->connect();
$REPORT_ID = 21;
$RECORD = new Record($DB);
$REPORTS = new Reports($DB, $RECORD);
$ENC = new encryption();
$SESSION = new Session($DB, $ENC);

function get_salesDolarTotal($startEpoch, $enderEpoch, $source) {
	global $DB;
	if($startEpoch == 0) {
		$sql = "
		SELECT 
			SUM(PersonsSales_payment) as total 
		FROM  
			PersonsSales
			INNER JOIN Persons ON Persons.Person_id=PersonsSales.Persons_Person_id
		WHERE
			Persons.HearAboutUs='".$DB->mysqli->escape_string($source)."'
		";
	} else {
		$sql = "
		SELECT 
			SUM(PersonsSales_payment) as total 
		FROM  
			PersonsSales
			INNER JOIN Persons ON Persons.Person_id=PersonsSales.Persons_Person_id
		WHERE
			Persons.HearAboutUs='".$DB->mysqli->escape_string($source)."'
		AND
			(PersonsSales.PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales.PersonsSales_dateCreated <= '".$enderEpoch."')
		";
	}
	//echo $sql;
	$snd = $DB->get_single_result($sql);
	if($snd['total'] == NULL) {
		return number_format(0.00, 2);
	} else {
		return number_format($snd['total'], 0);
	}
}

function get_AllsalesDolarTotal($startEpoch, $enderEpoch, $source, $LeftColumn) {
	global $DB;
	if($startEpoch == 0) {
		$sql = "
		SELECT 
			SUM(PersonsSales_payment) as total 
		FROM  
			PersonsSales
			INNER JOIN Persons ON Persons.Person_id=PersonsSales.Persons_Person_id
			INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id 
			LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
		WHERE
			".$LeftColumn."='".$DB->mysqli->escape_string($source)."' 
		";
	} else {
		$sql = "
		SELECT 
			SUM(PersonsSales_payment) as total 
		FROM  
			PersonsSales
			INNER JOIN Persons ON Persons.Person_id=PersonsSales.Persons_Person_id
			INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id 
			LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
		WHERE
			".$LeftColumn."='".$DB->mysqli->escape_string($source)."' 
		AND
			(PersonsSales.PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales.PersonsSales_dateCreated <= '".$enderEpoch."')
		";
	}
	//echo $sql."<br>\n";
	$snd = $DB->get_single_result($sql);
	if($snd['total'] == NULL) {
		return 0.00;
	} else {
		return $snd['total'];
	}
}

if (!isset($_POST['filterDates'])) {
	$dateNow = date("m/d/Y");
	$thenEpoch = mktime(0,0,0, date("m"), (date("d") - 7), date("Y"));	
	//echo $thenEpoch;
	$dateThen = date("m/d/Y", $thenEpoch);
	$_POST['filterDates'] = $dateThen.' - '.$dateNow;
	$_POST['exclude_zero'] = 1;
}
//print_r($_POST);

$dateParts = explode(" - ", $_POST['filterDates']);
$startEpoch = strtotime($dateParts[0]);
$enderEpoch = strtotime($dateParts[1]) + 86399;
//echo "Custom:".$startEpoch."|".$enderEpoch;
$dateParameters = array($startEpoch, $enderEpoch);

$CORE_FIELDS = $REPORTS->getCoreFields();
$SummarizeBy = "Gender";
$SummarizeByInside_ID = 631;
$SummarizeByInside = "prQuestion_".$SummarizeByInside_ID;

//print_r($CORE_FIELDS);
foreach($CORE_FIELDS as $CFIELD) {
	if($CFIELD['title'] == $SummarizeBy) {
		$topOptions = $CFIELD['opt'];
	}
}
$sumOptions = $REPORTS->get_customOptions($SummarizeByInside_ID);
//print_r($topOptions);

if(isset($_POST['LeftColumn'])) {
	$LeftColumn = $_POST['LeftColumn'];	
} else {
	$LeftColumn = 'HearAboutUs';
}

if(isset($_POST['Level_1'])) {
	$Level_1 = $_POST['Level_1'];
} else {
	$Level_1 = 'Persons.Gender';
}

if(isset($_POST['Level_1'])) {
	$Level_2 = $_POST['Level_2'];
} else {
	$Level_2 = 'PersonsProfile.prQuestion_631';
}

$LeftColumQ = $REPORTS->find_qSelect($LeftColumn);
$Level_1_ColumnQ = $REPORTS->find_qSelect($Level_1);
$Level_2_ColumnQ = $REPORTS->find_qSelect($Level_2);
//print_r($LeftColumQ);

$exportFileName = 'Lead Source Report Export - '.$_POST['filterDates'].'-'.$Level_1.'-'.$Level_2.'.csv';
?>
<style>
.value-found {
	background-color:#6F9;
}
.value-found-total {
	background-color:#716aca3b;
}
.report-table thead {
	background-color:#000;
	color:#FFF;
}
</style>
<?php
//print_r($_POST);
//print_r($LeftColumQ);

$LeftSideBarArray = $LeftColumQ['opt'];
$Level_1_ColArray = $Level_1_ColumnQ['opt'];
$Level_2_ColArray = $Level_2_ColumnQ['opt'];

$topOptions = $Level_1_ColArray;
$sumOptions = $Level_2_ColArray;

ob_start();
?>
<div style="width:100%; overflow:auto;">
<table width="100%" border="1" cellspacing="0" cellpadding="0" class="table table-condensed table-bordered table-responsive js-table report-table" id="stats-table"> 
<thead>
    <tr>
    <th>&nbsp;</th>
    <?php $topRow[] = ''; ?>
    <?php for($i=0; $i<count($topOptions); $i++): ?>
	<th colspan="<?php echo count($sumOptions)?>"><small><?php echo $topOptions[$i]['text']?></small></th>
    <?php $topRow[] = $topOptions[$i]['text']; ?>
    <?php for($l=0; $l<count($sumOptions); $l++): ?>
    <?php $topRow[] = ''; ?>
	<?php endfor; ?>
	<?php endfor; ?>
    <th colspan="3">&nbsp;</th>
    </tr>

	<tr>
    	<th>&nbsp;</th>
        <?php $secondRow[] = ''; ?>
        <?php for($lp=0; $lp<count($topOptions); $lp++): ?>
        	<?php for($i=0; $i<count($sumOptions); $i++): ?>
			<th><small><?php echo $sumOptions[$i]['text']?></small></th>
            <?php $secondRow[] = $sumOptions[$i]['text']; ?>
			<?php endfor; ?>
		<?php endfor; ?>
        <th><small>TOTAL</small></th>

        <th><small>SCOPE DOLLARS</small></th>
        <th><small>LIFE DOLLARS</small></th>
        <?php
		$secondRow[] = 'TOTAL';
		$secondRow[] = 'SCOPE DOLLARS';
		$secondRow[] = 'LIFE DOLLARS';
		?>

	</tr>
</thead> 
<tbody>
<?php
$THEADER = ob_get_clean();

$exportRows[] = $topRow;
$exportRows[] = $secondRow;

$colCount = 0;
foreach($LeftSideBarArray as $source_DTA):
	if ($source_DTA['text'] != ''):
	
	ob_start();
	?>
	<tr>
		<td nowrap="nowrap">&nbsp;&nbsp;<small><?php echo $source_DTA['text']?></small></td>
		<?php
		$singleRow[] = $source_DTA['text'];
		for($i=0; $i<count($topOptions); $i++):		 
			for($l=0; $l<count($sumOptions); $l++):
				if($LeftColumn == 'AGE') {
					$ageParts= explode("-", $source_DTA['value']);
					//print_r($ageParts);
					$low_epoch = mktime(0,0,0, date("m"), date("d"), (date("Y") - $ageParts[1]));
					$high_epoch = mktime(23,59,59, date("m"), date("d"), (date("Y") - $ageParts[0]));
					
					$sql = "SELECT 
						count(Persons.Person_id) as recordCount 
					FROM 
						Persons 
						INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id 
						LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
					WHERE 1
					AND ".$Level_1."='".$DB->mysqli->escape_string($topOptions[$i]['value'])."' 
					AND DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), Persons.DateOfBirth)), '%Y')+0 
					BETWEEN " . $ageParts[1] . " AND " . $ageParts[0] . " 
					AND ".$Level_2."='".$DB->mysqli->escape_string($sumOptions[$l]['value'])."' 
					AND (Persons.DateCreated >= '".$startEpoch."' AND Persons.DateCreated <= '".$enderEpoch."')
					";
				} elseif($Level_1 == 'AGE') {
					$ageParts= explode("-", $topOptions[$i]['value']);
					//print_r($ageParts);
					$low_epoch = mktime(0,0,0, date("m"), date("d"), (date("Y") - $ageParts[1]));
					$high_epoch = mktime(23,59,59, date("m"), date("d"), (date("Y") - $ageParts[0]));
					
					$sql = "SELECT 
						count(Persons.Person_id) as recordCount 
					FROM 
						Persons 
						INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id 
						LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
					WHERE 1
					AND DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), Persons.DateOfBirth)), '%Y')+0 
					BETWEEN " . $ageParts[1] . " AND " . $ageParts[0] . " 
					AND ".$LeftColumn."='".$DB->mysqli->escape_string($source_DTA['value'])."' 
					AND ".$Level_2."='".$DB->mysqli->escape_string($sumOptions[$l]['value'])."' 
					AND (Persons.DateCreated >= '".$startEpoch."' AND Persons.DateCreated <= '".$enderEpoch."')
					";
				} elseif($Level_2 == 'AGE') {
					$ageParts= explode("-", $sumOptions[$l]['value']);
					//print_r($ageParts);
					$low_epoch = mktime(0,0,0, date("m"), date("d"), (date("Y") - $ageParts[1]));
					$high_epoch = mktime(23,59,59, date("m"), date("d"), (date("Y") - $ageParts[0]));
					
					$sql = "SELECT 
						count(Persons.Person_id) as recordCount 
					FROM 
						Persons 
						INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id 
						LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
					WHERE 1
					AND ".$Level_1."='".$DB->mysqli->escape_string($topOptions[$i]['value'])."' 
					AND ".$LeftColumn."='".$DB->mysqli->escape_string($source_DTA['value'])."' 
					AND DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), Persons.DateOfBirth)), '%Y')+0 
					BETWEEN " . $ageParts[1] . " AND " . $ageParts[0] . " 
					AND (Persons.DateCreated >= '".$startEpoch."' AND Persons.DateCreated <= '".$enderEpoch."')
					";						
				} else {
					$sql = "SELECT 
						count(Persons.Person_id) as recordCount 
					FROM 
						Persons 
						INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id 
						LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
					WHERE 1
					AND ".$Level_1."='".$DB->mysqli->escape_string($topOptions[$i]['value'])."' 
					AND ".$LeftColumn."='".$DB->mysqli->escape_string($source_DTA['value'])."' 
					AND ".$Level_2."='".$DB->mysqli->escape_string($sumOptions[$l]['value'])."' 
					AND (Persons.DateCreated >= '".$startEpoch."' AND Persons.DateCreated <= '".$enderEpoch."')
					";
				}
				//echo $sql."<br>";
				$snd = $DB->get_single_result($sql);
				
				?>
                <td class="text-center <?php echo (($snd['recordCount'] > 0)? 'value-found':'')?>">
					<?php
					if($snd['recordCount'] > 0):
						$encSQL = $ENC->encrypt($sql);
						?><a href="#" onclick="openModalPreview('<?php echo $encSQL?>')"><?php echo $snd['recordCount']?></a><?php
					else:
						echo $snd['recordCount'];
					endif;
					?>
                </td>                
				<?php
				$singleRow[] = $snd['recordCount'];
				$rowCount[] = $snd['recordCount'];
				$colTotal[$colCount][] = $snd['recordCount'];
				$colCount++;
			endfor;
		endfor;
		$rowTotal = array_sum($rowCount);
		$singleRow[] = $rowTotal;
		$colTotal[$colCount][] = $rowTotal;
		//$colCount++;	
		
		$scopeTotal = get_AllsalesDolarTotal($startEpoch, $enderEpoch, $source_DTA['value'], $LeftColumn);
		$lifeTotal = get_AllsalesDolarTotal(0, 0, $source_DTA['value'], $LeftColumn);
		$singleRow[] = $scopeTotal;
		$singleRow[] = $lifeTotal;
		$scopeRunningTotal[] = $scopeTotal;
		$lifeRunningTotal[] = $lifeTotal;
						
		?>
        <td class="text-center <?php echo (($rowTotal > 0)? 'value-found-total':'')?>"><strong><?php echo $rowTotal?></strong></td>
		<td class="text-right"><?php echo number_format($scopeTotal, 0)?></td>
        <td class="text-right"><?php echo number_format($lifeTotal, 0)?></td>
	</tr>
	<?php
	$temp_row = ob_get_clean();
	
	if($_POST['exclude_zero'] == 1) {
		if($rowTotal > 0) {
			$TBODY .= $temp_row;
			$exportRows[] = $singleRow;
		}
	} else {
		$TBODY .= $temp_row;
		$exportRows[] = $singleRow;	
		//$exportRows[] = $scopeTotal;
		//$exportRows[] = $lifeTotal;
	}
	unset($singleRow);
	unset($rowCount);
	endif;
	$colCount = 0;	
endforeach;

ob_start();
?>
</tbody>
<tfoot>
<tr>
<td>&nbsp;</td>
<?php
$lastRow[] = '';
foreach($colTotal as $columnNumber):
	?>
    <td class="text-center <?php echo ((@array_sum($columnNumber) > 0)? 'value-found-total':'')?>"><strong><?php echo @array_sum($columnNumber)?></strong></td>
    <?php
	$lastRow[] = @array_sum($columnNumber);	
endforeach;
?>
	<td><?php echo @number_format(array_sum($scopeRunningTotal), 0);?></td>
    <td><?php echo @number_format(array_sum($lifeRunningTotal), 0);?></td>
</tr>
</tfoot>
</table>
<?php
$lastRow[] = @number_format(array_sum($scopeRunningTotal), 0);
$lastRow[] = @number_format(array_sum($lifeRunningTotal), 0);
$exportRows[] = $lastRow;   
//print_r($scopeRunningTotal);
?>
</div>
<?php
$TFOOTER = ob_get_clean();
//$finalReportTable = ob_get_clean();
?>
<script src="/assets/vendors/custom/floatThead/dist/jquery.floatThead.min.js" type="text/javascript"></script>
<script src="/assets/vendors/custom/tablesorter/dist/js/jquery.tablesorter.min.js" type="text/javascript"></script>
<script src="/assets/vendors/custom/table-live-search/js/search.js" type="text/javascript"></script>
<link href="/assets/vendors/custom/tablesorter/dist/css/theme.bootstrap_4.min.css" rel="stylesheet" type="text/css" />

<form action="/viewreport/<?php echo $REPORT_ID?>" method="post">
<div class="row">
	<div class="col-3">
		<div class="form-group m-form__group">
        	<label>Left Side <small>Select field for left side of report</small></label>         
            <select class="form-control" id="LeftColumn" name="LeftColumn">
            	<option value="AGE">Age Ranges</option>
            	<?php echo $REPORTS->render_qSelect(array(), $LeftColumn, array('SELECT', 'RADIO', 'CHECKBOX'))?>
            </select>
            <span class="m-form__help"><small>
                <label class="m-checkbox" style="margin-top:5px; margin-left:10px;">
                    <input type="checkbox" name="exclude_zero" value="1" <?php echo ((isset($_POST['exclude_zero'])? 'checked':''))?>>
                    Exclude "Zero" sum entries
                    <span></span>
                </label>
            </small></span>
        </div>
    </div>
    <div class="col-3">
		<div class="form-group m-form__group">
        	<label>Top Filter (Level 1) <small>Select field for top of report</small></label>         
            <select class="form-control" id="Level_1" name="Level_1">            	
            	<option value="AGE">Age Ranges</option>
				<?php echo $REPORTS->render_qSelect(array(), $Level_1, array('SELECT', 'RADIO', 'CHECKBOX'))?>
            </select>
        </div>          
    </div>
    <div class="col-3">
    	<div class="form-group m-form__group">
        	<label>Top Filter (Level 2) <small>Select field for top of report</small></label>         
            <select class="form-control" id="Level_2" name="Level_2">
            	<option value="AGE">Age Ranges</option>
            	<?php echo $REPORTS->render_qSelect(array(), $Level_2, array('SELECT', 'RADIO', 'CHECKBOX'))?>
            </select>
        </div>    
    </div>
    <div class="col-3">
    	<div class="form-group m-form__group">
            <label>Date Created</label>    
            <div class="input-group m-input-group">					
                <span class="input-group-addon" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                <input type="text" class="form-control m-input input-sm" id="filterDates" name="filterDates" placeholder="dates" autocomplete="off" value="<?php echo $_POST['filterDates']?>">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-default">Apply</button>
                </span>
            </div>
		</div>            
    </div>
</div>
</form>
<?php
echo $THEADER;
echo $TBODY;
echo $TFOOTER;
?>
<div class="modal fade" id="resultsReviewModal" tabindex="-1" role="dialog" aria-labelledby="resultsReviewModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="resultsReviewModalLabel">Report Drill Down Results</h5>
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
    $("#stats-table").tablesorter();

	
	$("#search").on("keyup", function() {
		var value = $(this).val();	
		$("table tbody tr").each(function(index) {
			//if (index !== 0) {
				$row = $(this);
				var id = $row.find("td:first").text();
				console.log(id);
				if (id.match(value)) {
					$row.show();
				} else {
					$row.hide();
				}
			//}
		});
	});
	
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
	
	var $table = $('table.report-table');
	$table.floatThead();
});		

function openModalPreview(enc) {
	$('#resultsReviewModal').modal('show');
	mApp.block("#resultsReviewModal .modal-body", {
		overlayColor: "#000000",
		type: "loader",
		state: "primary",
		message: "Loading..."
	});
	$.post('/ajax/report.lsr.php?action=load_details', {
		enc: enc,
		kiss_token: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$("#resultsReviewModal .modal-body").html(data);
		mApp.unblock("#resultsReviewModal .modal-body");
		mApp.init();
	});
		
	
}

</script>
