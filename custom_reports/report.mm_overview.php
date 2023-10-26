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

if (!isset($_POST['PersonsTypes_id']) || ($_POST['PersonsTypes_id'] == '')) {
	$_POST['PersonsTypes_id'] = array(4);
}

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


$startepoch 	= time();
$_POST['StartDate'] = date("m/d/Y", mktime(0,0,0,date("m"), 1, date("Y")));
$_POST['EndDate'] = date("m/d/Y", mktime(0,0,0,date("m"), date("t"), date("Y")));
$startepoch 	= strtotime($_POST['StartDate']);

$month_floor	= mktime(0,0,0, date("m", $startepoch), 1, date("Y", $startepoch));
$month_peak 	= mktime(23,59,59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch));

$ytd_floor		= mktime(0,0,0,1,1,date("Y", $startepoch));
$ytd_peak		= mktime(23,59,59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch));

if($_POST['IncludeOverview'] == '') {
	$PAGE = '';
	if ((isset($_POST['Matchmaker_id'])) && ($_POST['Matchmaker_id'] != '')) {
		$mm_array = $_POST['Matchmaker_id'];	
	} else {
		$mm_sql = "SELECT DISTINCT(Matchmaker_id) as mm_id FROM Persons WHERE PersonsTypes_id IN (4,6)";
		$mm_snd = $DB->get_multi_result($mm_sql);
		foreach($mm_snd as $mm_dta):
			$mm_array[] = $mm_dta['mm_id'];
		endforeach;
	}
	for($i=0; $i<count($mm_array); $i++) {
		$SQL = "
		SELECT
			Persons.Person_id,
			(Select Office_Name from Offices where Offices_Id = Persons.Offices_id) as Location,
        (Select Pod_Name from Pods where Pods.Pod_id = Persons.Pod_id) as Pod,
		
			Persons.FirstName,
			Persons.LastName,
			Persons.LastNoteAction,
			PersonsTypes_color,
			PersonsTypes_text,
			(SELECT CONCAT(SUBSTRING(firstName, 1, 1),' ',lastName) FROM Users WHERE Users.user_id=Persons.Matchmaker_id) as Matchmaker,
			(SELECT CONCAT(SUBSTRING(firstName, 1, 1),' ',lastName) FROM Users WHERE Users.user_id=Persons.Matchmaker2_id) as NetworkDeveloper,
			(SELECT CONCAT(SUBSTRING(firstName, 1, 1),' ',lastName) FROM Users WHERE Users.user_id=Persons.Assigned_userID) as Salesperson,
			PersonTypes.PersonsTypes_text,
			IF(prQuestion_676  = '0', '', FROM_UNIXTIME(prQuestion_676 , '%Y-%m-%d')) as prQuestion_676,
			IF(prQuestion_677  = '0', '', FROM_UNIXTIME(prQuestion_677 , '%Y-%m-%d')) as prQuestion_677,
			(SELECT SUM(PersonsSales_basePrice) FROM PersonsSales WHERE Persons_Person_id=Persons.Person_id AND PersonsSales_active = '1') as PaidToDate
		FROM
			Persons
			INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id
			LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
		WHERE
			Persons.PersonsTypes_id IN (".implode(",", $_POST['PersonsTypes_id']).")
		AND 
			Persons.Matchmaker_id='".$mm_array[$i]."'
		";
		if ((isset($_POST['Color_id'])) && ($_POST['Color_id'] != '')) {
			$SQL .= " AND Persons.Person_ID In (Select Person_Id from PersonsFlags where Color_Id in (" . implode(",", $_POST['Color_id']) ."))";
		}
		if ((isset($_POST['Assigned_userID'])) && ($_POST['Assigned_userID'] != '')) {
			$SQL .= " AND Persons.Assigned_userID IN (".implode(",", $_POST['Assigned_userID']).")";
		}
		$SQL .= "
		ORDER BY
			Persons.PersonsTypes_id ASC,
			Persons.LastName ASC
		";
		//echo $SQL."<hr>\n";
		
		$SND = $DB->get_multi_result($SQL);
		$FOUND = count($SND);
		
		$detailsClass = 'mmDetails_'.$mm_array[$i];
		
		$mm_as_sql = "SELECT count(*) as count FROM Persons WHERE PersonsTypes_id='4' AND Matchmaker_id='".$mm_array[$i]."'";
		$mm_as_snd = $DB->get_single_result($mm_as_sql);
		
		$mm_as2_sql = "SELECT count(*) as count FROM Persons WHERE PersonsTypes_id='4' AND Matchmaker2_id='".$mm_array[$i]."'";
		$mm_as2_snd = $DB->get_single_result($mm_as2_sql);
		
		$mm_fas_sql = "SELECT count(*) as count FROM Persons WHERE PersonsTypes_id='6' AND Matchmaker_id='".$mm_array[$i]."'";
		$mm_fas_snd = $DB->get_single_result($mm_fas_sql);
		
		$mm_fas2_sql = "SELECT count(*) as count FROM Persons WHERE PersonsTypes_id='6' AND Matchmaker2_id='".$mm_array[$i]."'";
		$mm_fas2_snd = $DB->get_single_result($mm_fas2_sql);
		
		$mm_cdm_sql = "SELECT count(*) as count FROM PersonsDates WHERE PersonsDates_assignedTo='".$mm_array[$i]."' AND PersonsDates_isComplete='1' AND (PersonsDates_dateExecuted >= '".$month_floor."' AND PersonsDates_dateExecuted <= '".$month_peak."')";
		$mm_cdm_snd = $DB->get_single_result($mm_cdm_sql);
		
		$mm_cdy_sql = "SELECT count(*) as count FROM PersonsDates WHERE PersonsDates_assignedTo='".$mm_array[$i]."' AND PersonsDates_isComplete='1' AND (PersonsDates_dateExecuted >= '".$ytd_floor."' AND PersonsDates_dateExecuted <= '".$ytd_peak."')";
		$mm_cdy_snd = $DB->get_single_result($mm_cdy_sql);
	
		$mm_dist_stat_sql = "SELECT DISTINCT(A.Color_id) as clr_id FROM PersonsFlags A JOIN Persons B ON A.Person_Id=B.Person_Id WHERE PersonsTypes_id='4' AND Matchmaker_id='".$mm_array[$i]."' AND A.Color_id NOT IN (21, 42, 43, 44, 45)";
		$mm_dist_stat_snd = $DB->get_multi_result($mm_dist_stat_sql);
		
		ob_start();
		foreach($mm_dist_stat_snd as $mm_dist_dta):
			$mm_stat_sql = "SELECT * FROM PersonsColors WHERE Color_id='".$mm_dist_dta['clr_id']."'";
			$mm_stat_dta = $DB->get_single_result($mm_stat_sql);
			
			$mm_stat_count_sql = "SELECT COUNT(*) as count FROM PersonsFlags A JOIN Persons B ON A.Person_Id=B.Person_Id WHERE PersonsTypes_id='4' AND Matchmaker_id='".$mm_array[$i]."' AND A.Color_id='".$mm_dist_dta['clr_id']."'";
			$mm_stat_count_snd = $DB->get_single_result($mm_stat_count_sql);
			?>
            <div class="row">
                <div class="col-sm-8">
                    <span class="m-badge m-badge--metal m-badge--wide" style="background-color:<?php echo $mm_stat_dta['Color_hex']?>;"><?php echo $mm_stat_dta['Color_title']?></span>
                </div>
                <div class="col-sm-4"><?php echo $mm_stat_count_snd['count']?></div>
            </div>
			<?php
		endforeach;
		$statusStats = ob_get_clean();
		
		ob_start();
		?>
        <div class="row">
            <div class="col-4">
                <div class="pull-right">
                    <small>Showing: <?php echo number_format($FOUND)?></small>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleDetails('<?php echo $detailsClass?>')" /><i class="la la-angle-double-down"></i></button>
                </div>
                <h5 class="m--font-primary"><?php echo $RECORD->get_FulluserName($mm_array[$i])?></h5>
                <dl class="row">
                    <dt class="col-sm-8">Completed Dates <small>(this month)</small></dt>
                    <dd class="col-sm-4"><?php echo $mm_cdm_snd['count']?></dd>
                    
                    <dt class="col-sm-8">Completed Dates <small>(YTD)</small></dt>
                    <dd class="col-sm-4"><?php echo $mm_cdy_snd['count']?></dd>
                </dl>
            </div>
            <div class="col-4">
                <dl class="row">                                                    
                    <dt class="col-sm-8">Active (Relationship Manager)</dt>
                    <dd class="col-sm-4"><?php echo $mm_as_snd['count']?></dd>
                    
                    <dt class="col-sm-8">Active (Network Developer)</dt>
                    <dd class="col-sm-4"><?php echo $mm_as2_snd['count']?></dd>
                    
                    <dt class="col-sm-8">Frozen (Relationship Manager)</dt>
                    <dd class="col-sm-4"><?php echo $mm_fas_snd['count']?></dd>
                    
                    <dt class="col-sm-8">Frozen (Network Developer)</dt>
                    <dd class="col-sm-4"><?php echo $mm_fas2_snd['count']?></dd>
                </dl>
            </div>
            <div class="col-4">
                <?php echo $statusStats?>
            </div>
        </div>
        <hr />                                                   
        <?php
		
		?>
		<table class="table statsTable" id="<?php echo $detailsClass?>" style="display:none;">
        <thead class="thead-inverse">
            <tr>
                <th>Client</th>
			<th>Type</th>
			<th>Status</th>
			<th>Relationship Manager</th>
			<th>Network Developer</th>
			<th>Last Contact</th>
			<th>Matches</th>
			<th>Last Intro</th>
			<th>Paid to Date</th>
			<th>Market Director</th>
			<th>Contract Start</th>
			<th>Contract End</th>
            </tr>
        </thead>
        <tbody>
        <?php
		foreach($SND as $DTA):
			$LAST_DATA = json_decode($DTA['LastNoteAction'], true);
		$Last_Intro = $MATCHING->get_myLastDate($DTA['Person_id']);
		$LAST_INTRO_DATE = ($Last_Intro['PersonsDates_dateExecuted'] == 0) ? '': '<a href="/intro/' . $Last_Intro['PersonsDates_id'] . '">'. date("Y-m-d", $Last_Intro['PersonsDates_dateExecuted']) . '</a>';
		
		//echo render_row($DTA, $LAST_DATA, $LAST_INTRO_DATE);	
		?>
		<tr>
	        <td><a href="/profile/<?php echo $DTA['Person_id']?>"  target="_blank"><?php echo $DTA['FirstName']?>
			<?php echo $DTA['LastName']?></a> <br /> <?php echo $DTA['Location']?>
			<br /><?php echo ($DTA['Pod'] ?? "No Pod");?></td>
			<td><div class="m-badge m-badge--<?php echo $DTA['PersonsTypes_color']?> m-badge--wide"><?php echo $DTA['PersonsTypes_text']?></div></td>
			<td><?php echo $RECORD->get_personsColorSpan($DTA['Person_id'])?></td>
			<td><?php echo $DTA['Matchmaker']?></td>
			<td><?php echo $DTA['NetworkDeveloper']?></td>
			<td class="text-right">
			<a href="/profile/<?php echo $DTA['Person_id']?>#user_profile_tab_history"  target="_blank">
				<?php echo (($LAST_DATA['hDate'] != 0)? date("Y-m-d", $LAST_DATA['hDate']). ' ('.$LAST_DATA['hType'] . '}':'')?>
			</a></td>
			<td class="text-center">
				<a href="/profile/<?php echo $DTA['Person_id']?>#user_profile_tab_intros"  target="_blank">
				<?php echo $MATCHING->count_myCompletedDates($DTA['Person_id'])?>
				</a>
			</td>
			<td class="text-right"><?php echo $LAST_INTRO_DATE?></td>
			<td class="text-right"><?php echo number_format($DTA['PaidToDate'], 0)?></td>
			<td><?php echo $DTA['Salesperson']?></td>    
			<td class="text-right"><?php echo $DTA['prQuestion_676']?></td>
			<td class="text-right"><?php echo $DTA['prQuestion_677']?></td>
			    
		</tr>
		<?php		
		endforeach;
		?>
        </tbody>
        </table>
        <script>
$(document).ready(function(e) {
    $(".statsTable").tablesorter();
});		
		
		</script>
        <?php
		//$REPORT .= ob_get_clean();
		$PAGE .= ob_get_clean();
	}
} else {
	$SQL = "
	SELECT
		Persons.Person_id,
		(Select Office_Name from Offices where Offices_Id = Persons.Offices_id) as Location,
        (Select Pod_Name from Pods where Pods.Pod_id = Persons.Pod_id) as Pod,
		Persons.FirstName,
		Persons.LastName,
		Persons.LastNoteAction,
		PersonsTypes_color,
		PersonsTypes_text,
		(SELECT CONCAT(SUBSTRING(firstName, 1, 1),' ',lastName) FROM Users WHERE Users.user_id=Persons.Matchmaker_id) as Matchmaker,
		(SELECT CONCAT(SUBSTRING(firstName, 1, 1),' ',lastName) FROM Users WHERE Users.user_id=Persons.Assigned_userID) as Salesperson,
		PersonTypes.PersonsTypes_text,
		IF(prQuestion_676  = '0', '', FROM_UNIXTIME(prQuestion_676 , '%Y-%m-%d')) as prQuestion_676,
		IF(prQuestion_677  = '0', '', FROM_UNIXTIME(prQuestion_677 , '%Y-%m-%d')) as prQuestion_677,
		(SELECT SUM(PersonsSales_basePrice) FROM PersonsSales WHERE Persons_Person_id=Persons.Person_id AND PersonsSales_active = '1') as PaidToDate
	FROM
		Persons
		INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id
		LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	WHERE
		Persons.PersonsTypes_id IN (".implode(",", $_POST['PersonsTypes_id']).")";
		
	if ((isset($_POST['Matchmaker_id'])) && ($_POST['Matchmaker_id'] != '')) {
		$SQL .= " AND Persons.Matchmaker_id IN (".implode(",", $_POST['Matchmaker_id']).")";
	}
	if ((isset($_POST['Color_id'])) && ($_POST['Color_id'] != '')) {
		$SQL .= " AND Persons.Person_ID In (Select Person_Id from PersonsFlags where Color_Id in (" . implode(",", $_POST['Color_id']) ."))";
	}
	if ((isset($_POST['Assigned_userID'])) && ($_POST['Assigned_userID'] != '')) {
		$SQL .= " AND Persons.Assigned_userID IN (".implode(",", $_POST['Assigned_userID']).")";
	}
	
	$SQL .= "
	ORDER BY
		Persons.PersonsTypes_id ASC,
		Persons.LastName ASC
	";
	
	$SND = $DB->get_multi_result($SQL);
	die;
	
	ob_start();
	foreach($SND as $DTA):
		$LAST_DATA = json_decode($DTA['LastNoteAction'], true);
		$Last_Intro = $MATCHING->get_myLastDate($DTA['Person_id']);
		$LAST_INTRO_DATE = ($Last_Intro['PersonsDates_dateExecuted'] == 0) ? '': '<a href="/intro/' . $Last_Intro['PersonsDates_id'] . '">'. date("Y-m-d", $Last_Intro['PersonsDates_dateExecuted']) . '</a>';
		
		if (isset($_POST['LastIntroDays']) || ($_POST['LastIntroDays'] != '0')) {
			$lastIntro_epoch = $Last_Intro['PersonsDates_dateExecuted'];
			$currentDay_epoch = time();
			$maxDateDiff = $_POST['LastIntroDays'] * ((60 * 60) * 24);
			$dateDiff = ($currentDay_epoch - $lastIntro_epoch);
			if($dateDiff > $maxDateDiff) {
				$showRow = true;
			} else {
				$showRow = false;
			}
		} else {
			$showRow = true;
		}
		
		if($showRow):
		?>
		<tr>
		<td><a href="/profile/<?php echo $DTA['Person_id']?>"  target="_blank"><?php echo $DTA['FirstName']?>
			<?php echo $DTA['LastName']?></a> <br /> <?php echo $DTA['Location']?>
			<br /><?php echo ($DTA['Pod'] ?? "No Pod");?></td>
			<td><div class="m-badge m-badge--<?php echo $DTA['PersonsTypes_color']?> m-badge--wide"><?php echo $DTA['PersonsTypes_text']?></div></td>
			<td><?php echo $RECORD->get_personsColorSpan($DTA['Person_id'])?></td>
			<td><?php echo $DTA['Matchmaker']?></td>
			<td><?php echo $DTA['NetworkDeveloper']?></td>
			<td class="text-right">
			<a href="/profile/<?php echo $DTA['Person_id']?>#user_profile_tab_history"  target="_blank">
				<?php echo (($LAST_DATA['hDate'] != 0)? date("Y-m-d", $LAST_DATA['hDate']). ' ('.$LAST_DATA['hType'] . '}':'')?>
			</a></td>
			<td class="text-center">
				<a href="/profile/<?php echo $DTA['Person_id']?>#user_profile_tab_intros"  target="_blank">
				<?php echo $MATCHING->count_myCompletedDates($DTA['Person_id'])?>
				</a>
			</td>
			<td class="text-right"><?php echo $LAST_INTRO_DATE?></td>
			<td class="text-right"><?php echo number_format($DTA['PaidToDate'], 0)?></td>
			<td><?php echo $DTA['Salesperson']?></td>    
			<td class="text-right"><?php echo $DTA['prQuestion_676']?></td>
			<td class="text-right"><?php echo $DTA['prQuestion_677']?></td>
			
		</tr>
		<?php
		endif;
		
	endforeach;
	$TABLE = ob_get_clean();
	
	ob_start();
	?>
    <table class="m-datatable">
    <thead>
        <tr>
            <th>Client</th>
            <th>Type</th>
            <th>Status</th>
            <th>Relationship Manager</th>
            <th>Network Developer</th>
            <th>Last Intro</th>
            <th>Contract Start</th>
            <th>Contract End</th>
            <th>Matches</th>
            <th>Last Contact</th>
            <th>Paid to Date</th>
            <th>Market Director</th>
        </tr>
    </thead>
    <tbody>
    <?php echo $TABLE?>
    </tbody>
    </table>
    <script>
var DatatableHtmlTable = function() {
    var e = function() {
        $(".m-datatable").mDatatable({
            search: {
                input: $("#generalSearch")
            },
            columns: [{
                field: "Matches",
                type: "number"
            }]
        })
    };
    return {
        init: function() {
            e()
        }
    }
}();
jQuery(document).ready(function() {
	DatatableHtmlTable.init();		
});
	
	
    </script>
    <?php
	$PAGE = ob_get_clean();
	 
}
?>
<script src="/assets/vendors/custom/tablesorter/dist/js/jquery.tablesorter.min.js" type="text/javascript"></script>
<link href="/assets/vendors/custom/tablesorter/dist/css/theme.bootstrap_4.min.css" rel="stylesheet" type="text/css" />
<div class="m-portlet">
    <div class="m-portlet__body" style="padding: 1.2rem 1.2rem;">
    	<form id="filterSearhForm" class="m-form m-form--fit m-form--label-align-right" action="/viewreport/56" method="post">
		<h5>Filters</h5>
        <?php //print_r($_POST); ?>
        <div class="row">
        	<div class="col-4">
            	<div class="form-group m-form__group">
                    <label>Record Types</label>
                    
                    <select class="form-control m-select2" id="PersonsTypes_id" name="PersonsTypes_id[]" multiple="multiple">
                    <?php
                    if(isset($_POST['PersonsTypes_id'])) {
                        $preSelected = $_POST['PersonsTypes_id'];
                    } else {
                        $preSelected = array();	
                    }
                    $sql = "SELECT * FROM PersonTypes WHERE PersonsTypes_id IN (4,6) ORDER BY PersonsTypes_order";
                    $snd = $DB->get_multi_result($sql);
                    foreach($snd as $dta):
                        ?><option value="<?php echo $dta['PersonsTypes_id']?>" <?php echo ((in_array($dta['PersonsTypes_id'], $preSelected))? 'selected':'')?>><?php echo $dta['PersonsTypes_text']?></option><?php
                    endforeach;
                    ?>
                    </select>
                </div>                
                <div class="form-group m-form__group">
                    <label>Status</label>        
                    <select class="form-control m-select2" id="Color_id" name="Color_id[]" multiple="multiple">
                    <?php
                    if(isset($_POST['Color_id'])) {
                        $preSelected = $_POST['Color_id'];
                    } else {
                        $preSelected = array();	
                    }						
                    $qa_sql = "SELECT * FROM PersonsColors WHERE 1 ORDER BY Color_order ASC";
                    $qa_snd = $DB->get_multi_result($qa_sql);
                    //print_r($qa_snd);
                    ob_start();
                    foreach($qa_snd as $qa_dta):
                        ?><option value="<?php echo $qa_dta['Color_id']?>" <?php echo ((in_array($qa_dta['Color_id'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['Color_title']?></option><?php
                    endforeach;
                    ?>
                    </select>
                </div>
			</div>
        	<div class="col-4">
            	<div class="form-group m-form__group">
                    <label>Assigned Matchmaker</label>
                    <?php
                    if(isset($_POST['Matchmaker_id'])) {
                        $preSelected = $_POST['Matchmaker_id'];
                    } else {
                        $preSelected = array();	
                    }
                    ?>        
                    <select class="form-control m-select2" id="Matchmaker_id" name="Matchmaker_id[]" multiple="multiple">
                        <?php echo $RECORD->options_userSelect($preSelected)?>
                    </select>
                    <span class="m-form__help"><?php echo implode(" | ", $quickClick)?></span>
                    
                </div>
                <div class="form-group m-form__group">
                    <label>Sales Rep</label>
                    <?php
                    if(isset($_POST['Assigned_userID'])) {
                        $preSelected = $_POST['Assigned_userID'];
                    } else {
                        $preSelected = array();	
                    }
                    ?>        
                    <select class="form-control m-select2" id="Assigned_userID" name="Assigned_userID[]" multiple="multiple">
                        <?php echo $RECORD->options_userSelect($preSelected)?>
                    </select>
                </div>                	
            </div>
            <div class="col-4">
            	<div class="form-group m-form__group">
                    <label>No Intro in</label>
                    <?php
                    if(isset($_POST['LastIntroDays'])) {
                        $preSelected = $_POST['LastIntroDays'];
                    } else {
                        $preSelected = array();	
                    }
                    ?>        
                    <select class="form-control m-select2" id="LastIntroDays" name="LastIntroDays">
                    	<option value="">none</option>
					<?php for($i=0; $i<10; $i++): ?>
                    	<option value="<?php echo ($i * 10)?>" <?php echo (($_POST['LastIntroDays'] == ($i * 10))? 'selected':'')?>><?php echo ($i * 10)?> days</option>                    
                    <?php endfor; ?>
                    </select>
                </div>
                <div class="m-form__group form-group">
                    <div class="m-checkbox-list">
                    <label class="m-checkbox">
                    	<input type="checkbox" name="IncludeOverview" id="IncludeOverview" value="1" <?php echo (($_POST['IncludeOverview'] == 1)? 'checked':'')?>>
                        Render as single table <i class="la la-info-circle" data-toggle="m-tooltip" title="" data-original-title="This option will remove the summary of each salesperson and show just the matching records."></i>           	
                         <span></span>
                        </label>
                    </div>
                	<button type="submit" class="btn btn-secondary">Apply Filters <i class="fa fa-search"></i></button>
                </div>                           	
            </div>
        </div>
        </form>


        
    </div>
</div>

<?php echo $PAGE?>           
        
<script>
jQuery(document).ready(function() {
<?php if($_POST['IncludeOverview'] == 1): ?>

<?php else: ?>

<?php endif; ?>    

	$('#Matchmaker_id').select2({
		placeholder: "Select Matchkmater(s)",
		allowClear: !0
	});
	$('#Assigned_userID').select2({
		placeholder: "Select Salesperson(s)",
		allowClear: !0
	});
	$('#Color_id').select2({
		placeholder: "Select Status(s)",
		allowClear: !0
	});	
	$('#PersonsTypes_id').select2({
		placeholder: "Select Type(s)",
		allowClear: !0
	});
	$('#LastIntroDays').select2({
		placeholder: "Select",
		allowClear: !0
	});
});
function toggleDetails(rowClass) {
	$('#'+rowClass).each(function() {
		console.log($(this));
		if($(this).is(':visible')) {
			$(this).css('display', 'none');		
		} else {
			$(this).css('display', 'block');	
		}
	});
}
function addUsersToReport(userObject) {
	console.log(userObject);
	//$('#system_user_id').select2('val', userObject);
	var Values = new Array();
	for(i=0; i<userObject.length; i++) {
		console.log(userObject[i]);
		Values.push(userObject[i]);		
	}
	$("#Matchmaker_id").val(Values).trigger('change');
}
</script>        




