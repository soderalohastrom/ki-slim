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

$exc_u_sql = "SELECT * FROM Users WHERE userStatus='2' ORDER BY user_id ASC";
$exc_u_snd = $DB->get_multi_result($exc_u_sql);
foreach($exc_u_snd as $exc_u_dta):
	$ExcludedUsers[] = $exc_u_dta['user_id'];
	$ExcludedUserList[] = ucfirst($exc_u_dta['firstName']).' '.$exc_u_dta['lastName'];
endforeach;
$ExcludedUsers[] = 0;
//print_r($ExcludedUsers);

if($_POST['IncludeOverview'] == '') {
	$PAGE = '';
	if ((isset($_POST['Assigned_userID'])) && ($_POST['Assigned_userID'] != '')) {
		$mm_array = $_POST['Assigned_userID'];	
	} else {
		$mm_sql = "SELECT DISTINCT(Assigned_userID) as mm_id FROM Persons WHERE PersonsTypes_id IN (4,6)";
		$mm_snd = $DB->get_multi_result($mm_sql);
		foreach($mm_snd as $mm_dta):
			$mm_array[] = $mm_dta['mm_id'];
		endforeach;
	}
	for($i=0; $i<count($mm_array); $i++) {
		//$ExcludedUsers = array(186799,144752,144753,144754,144755,144756,144757,144758,144759);
		if (!in_array($mm_array[$i], $ExcludedUsers)):
		$SQL = "
		SELECT
			Persons.Person_id,
			Persons.FirstName,
			Persons.LastName,
			Persons.Email,
			Persons.LastNoteAction,
			Persons.LeadStages_id,
			FROM_UNIXTIME(DateCreated, '%Y-%m-%d') as DateCreatedDisplay,
			Persons.Assigned_userID,
			PersonsProfile.prQuestion_1713,
			PersonsProfile.prQuestion_631,
			LeadStages_name,
			LeadStage_hex,
			(SELECT CONCAT(firstName,' ',lastName) FROM Users WHERE Users.user_id=Persons.Assigned_userID) as Salesperson,
			CONCAT(Addresses.City,' ',Addresses.State) as Location
		FROM
			Persons
			INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
			INNER JOIN LeadStages ON LeadStages.LeadStages_id=Persons.LeadStages_id
			LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
		WHERE
			Persons.PersonsTypes_id IN (3)
		AND
			Assigned_userID NOT IN (".implode(",", $ExcludedUsers).")
		AND
			PersonsStatus_id NOT IN (8)
		AND
			prQuestion_1713 NOT IN ('Dead Lead')
		AND
			Persons.Assigned_userID='".$mm_array[$i]."'
		";
		
		if ((isset($_POST['Assigned_userID'])) && ($_POST['Assigned_userID'] != '')) {
			$SQL .= " AND Persons.Assigned_userID IN (".implode(",", $_POST['Assigned_userID']).")";
		}
		
		if ((isset($_POST['prQuestion_1713'])) && ($_POST['prQuestion_1713'] != '')) {
			$SQL .= " AND PersonsProfile.prQuestion_1713 IN ('".implode("','", $_POST['prQuestion_1713'])."')";
		}
		
		if ((isset($_POST['LeadStages_id'])) && ($_POST['LeadStages_id'] != '')) {
			$SQL .= " AND Persons.LeadStages_id IN (".implode(",", $_POST['LeadStages_id']).")";
		}
		
		if ((isset($_POST['prQuestion_631'])) && ($_POST['prQuestion_631'] != '')) {
			$SQL .= " AND PersonsProfile.prQuestion_631 IN ('".implode("','", $_POST['prQuestion_631'])."')";
		}
		
		$SQL .= " ORDER BY
			Persons.LastName ASC
		";
		
		//echo $SQL."<hr>\n";
		$SND = $DB->get_multi_result($SQL);
		$FOUND = count($SND);
		$detailsClass = 'mmDetails_'.$mm_array[$i];

		// ASSIGNED //
		$sp_as_sql = "SELECT count(*) as count FROM Persons INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id WHERE PersonsTypes_id='3' AND Assigned_userID='".$mm_array[$i]."' AND (PersonsStatus_id NOT IN (8) AND PersonsProfile.prQuestion_1713 != 'Dead Lead')";
		$sp_as_snd = $DB->get_single_result($sp_as_sql);
		
		// ACTIVE //
		$sp_ac_sql = "SELECT count(*) as count FROM Persons INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id WHERE PersonsTypes_id='3' AND Assigned_userID='".$mm_array[$i]."' AND (PersonsStatus_id NOT IN (8, 9) AND PersonsProfile.prQuestion_1713 NOT IN ('Dead Lead', 'Sleeping'))";		
		$sp_ac_snd = $DB->get_single_result($sp_ac_sql);
		
		// SLEEPING //
		$sp_sl_sql = "SELECT count(*) as count FROM Persons INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id WHERE PersonsTypes_id='3' AND Assigned_userID='".$mm_array[$i]."' AND (PersonsStatus_id ='9' OR PersonsProfile.prQuestion_1713 ='Sleeping')";		
		$sp_sl_snd = $DB->get_single_result($sp_sl_sql);
		
		// SALES COUNT - MONTH //
		$sp_sm_sql = "SELECT count(*) as count FROM PersonsSales WHERE PersonsSales_createdBy='".$mm_array[$i]."' AND (PersonsSales_dateCreated >= '".$month_floor."' AND PersonsSales_dateCreated <= '".$month_peak."')";
		$sp_sm_snd = $DB->get_single_result($sp_sm_sql);
		// SALES DOLLARS - MONTH //
		$sp_smd_sql = "SELECT SUM(PersonsSales_basePrice) as total FROM PersonsSales WHERE PersonsSales_createdBy='".$mm_array[$i]."' AND (PersonsSales_dateCreated >= '".$month_floor."' AND PersonsSales_dateCreated <= '".$month_peak."')";
		$sp_smd_snd = $DB->get_single_result($sp_smd_sql);
		
		// SALES COUNT - YTD //
		$sp_ytd_sql = "SELECT count(*) as count FROM PersonsSales WHERE PersonsSales_createdBy='".$mm_array[$i]."' AND (PersonsSales_dateCreated >= '".$ytd_floor."' AND PersonsSales_dateCreated <= '".$ytd_peak."')";		
		$sp_ytd_snd = $DB->get_single_result($sp_ytd_sql);
		// SALES DOLLARS - YTL //
		$sp_ytdd_sql = "SELECT SUM(PersonsSales_basePrice) as total FROM PersonsSales WHERE PersonsSales_createdBy='".$mm_array[$i]."' AND (PersonsSales_dateCreated >= '".$ytd_floor."' AND PersonsSales_dateCreated <= '".$ytd_peak."')";
		//echo $sp_ytdd_sql."<br>";
		$sp_ytdd_snd = $DB->get_single_result($sp_ytdd_sql);

		//$qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='631' ORDER BY QuestionsAnswers_order ASC";
        //$qa_snd = $DB->get_multi_result($qa_sql);
		
		$qa_sql = "SELECT * FROM Offices ORDER BY office_Name ASC";
		$qa_snd = $DB->get_multi_result($qa_sql);
		ob_start();
		foreach($qa_snd as $mm_dist_dta):
			$mm_stat_count_sql = "SELECT COUNT(*) as count FROM Persons WHERE PersonsTypes_id='3' AND Assigned_userID='".$mm_array[$i]."' AND Persons.Offices_id='".$mm_dist_dta['Offices_id']."'";
			//echo $mm_stat_count_sql."<br>";
			$mm_stat_count_snd = $DB->get_single_result($mm_stat_count_sql);
			if($mm_stat_count_snd['count'] != 0):
			?>
            <div class="row" style="font-size:.8em;">
                <div class="col-sm-8"><?php echo $mm_dist_dta['office_Name']?></div>
                <div class="col-sm-4"><?php echo $mm_stat_count_snd['count']?></div>
            </div>
			<?php
			endif;
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
                    <dt class="col-sm-8">Total Assigned</dt>
                    <dd class="col-sm-4"><?php echo number_format($sp_as_snd['count'])?></dd>
                    
                    <dt class="col-sm-8">Active</dt>
                    <dd class="col-sm-4"><?php echo number_format($sp_ac_snd['count'])?></dd>
                    
                    <dt class="col-sm-8">Sleeping</dt>
                    <dd class="col-sm-4"><?php echo number_format($sp_sl_snd['count'])?></dd>
                </dl>
            </div>
            <div class="col-4">
                <dl class="row">                                                    
                    <dt class="col-sm-8">Sales <small>(this month)</small></dt>
                    <dd class="col-sm-4"><?php echo $sp_sm_snd['count']?></dd>
                    
                    <dt class="col-sm-8">Dollars <small>(this month)</small></dt>
                    <dd class="col-sm-4"><?php echo number_format($sp_smd_snd['total'], 0)?></dd>
                    
                    <dt class="col-sm-8">Sales <small>(YTD)</small></dt>
                    <dd class="col-sm-4"><?php echo $sp_ytd_snd['count']?></dd>
                    
                    <dt class="col-sm-8">Dollars <small>(YTD)</small></dt>
                    <dd class="col-sm-4"><?php echo number_format($sp_ytdd_snd['total'], 0)?></dd>
                </dl>
            </div>
            <div class="col-4">
                <?php echo $statusStats?>
            </div>
        </div>
        <hr />                                   
        <?php
		
		?>
		<table class="table statsTable" id="<?php echo $detailsClass?>" style="display:none; width:100%;">
        <thead class="thead-inverse">
            <tr>
                <th>Lead</th>
                <th>Created</th>
                <th>Market Director</th>
                <th>Status</th>
                <th>State</th>
                <th>Location</th>
                <th>Income</th>
                <th>Last Contact</th>
            </tr>
        </thead>
        <tbody>
        <?php
		foreach($SND as $DTA):
			$LAST_DATA = json_decode($DTA['LastNoteAction'], true);
			?>
		<tr>
			<td>
            	<a href="/profile/<?php echo $DTA['Person_id']?>" class="m-link" target="_blank"><?php echo $DTA['FirstName']?> <?php echo $DTA['LastName']?></a>
                &nbsp;&nbsp;
                <a href="/profile/<?php echo $DTA['Person_id']?>" target="_blank" class="m-link" style="color:#7b7e8a;"><i class="la la-external-link-square"></i></a>
			</td>
			<td><?php echo $DTA['DateCreatedDisplay']?></td>
			<td><?php echo $DTA['Salesperson']?></td>
            <td><?php echo $DTA['LeadStages_name']?></td>
            <td><?php echo $DTA['prQuestion_1713']?></td>
            <td><?php echo $DTA['Location']?></td>
            <td><?php echo $DTA['prQuestion_631']?></td>
            <td><?php echo (($LAST_DATA['hDate'] != 0)? date("Y-m-d", $LAST_DATA['hDate']). ' (' . $LAST_DATA['hType'] . ')':'')?></td>
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
		endif;
		//$REPORT .= ob_get_clean();
		$PAGE .= ob_get_clean();
	}
} else {	
	//print_r($_POST);
	//$ExcludedUsers = array(186799,144752,144753,144754,144755,144756,144757,144758,144759);
	
	$SQL = "
	SELECT
		Persons.Person_id,
		Persons.FirstName,
		Persons.LastName,
		Persons.Email,
		Persons.LastNoteAction,
		Persons.LeadStages_id,
		FROM_UNIXTIME(DateCreated, '%Y-%m-%d') as DateCreatedDisplay,
		Persons.Assigned_userID,
		PersonsProfile.prQuestion_1713,
		PersonsProfile.prQuestion_631,
		LeadStages_name,
		LeadStage_hex,
		(SELECT CONCAT(firstName,' ',lastName) FROM Users WHERE Users.user_id=Persons.Assigned_userID) as Salesperson	
	FROM
		Persons
		INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
		INNER JOIN LeadStages ON LeadStages.LeadStages_id=Persons.LeadStages_id
	WHERE
		Persons.PersonsTypes_id IN (3)
	AND
		Assigned_userID NOT IN (".implode(",", $ExcludedUsers).")
	AND
		PersonsStatus_id NOT IN (8)
	AND
		prQuestion_1713 NOT IN ('Dead Lead')
	";
	if ((isset($_POST['Assigned_userID'])) && ($_POST['Assigned_userID'] != '')) {
		$SQL .= " AND Persons.Assigned_userID IN (".implode(",", $_POST['Assigned_userID']).")";
	}
	
	if ((isset($_POST['prQuestion_1713'])) && ($_POST['prQuestion_1713'] != '')) {
		$SQL .= " AND PersonsProfile.prQuestion_1713 IN ('".implode("','", $_POST['prQuestion_1713'])."')";
	}
	
	if ((isset($_POST['LeadStages_id'])) && ($_POST['LeadStages_id'] != '')) {
		$SQL .= " AND Persons.LeadStages_id IN (".implode(",", $_POST['LeadStages_id']).")";
	}
	
	if ((isset($_POST['prQuestion_631'])) && ($_POST['prQuestion_631'] != '')) {
		$SQL .= " AND PersonsProfile.prQuestion_631 IN ('".implode("','", $_POST['prQuestion_631'])."')";
	}
	
	$SQL .= " ORDER BY
		Persons.LastName ASC
	";
	
	//$SQL .= "LIMIT 2000";
	//echo "<hr>".$SQL;
	$SND = $DB->get_multi_result($SQL);
	//print_r($SND);
	
	ob_start();
	foreach($SND as $DTA):
		$LAST_DATA = json_decode($DTA['LastNoteAction'], true);
		//print_r($LAST_DATA);
		?>
		<tr>
			<td>
            	<a href="/profile/<?php echo $DTA['Person_id']?>" class="m-link" target="_blank"><?php echo $DTA['FirstName']?> <?php echo $DTA['LastName']?></a>
                &nbsp;&nbsp;
                <a href="/profile/<?php echo $DTA['Person_id']?>" target="_blank" class="m-link" style="color:#7b7e8a;"><i class="la la-external-link-square"></i></a>
			</td>
			<td><?php echo $DTA['DateCreatedDisplay']?></td>
			<td><?php echo $DTA['Salesperson']?></td>
            <td><?php echo $DTA['LeadStages_name']?></td>
            <td><?php echo $DTA['prQuestion_1713']?></td>
            <td><?php echo $DTA['prQuestion_631']?></td>
            <td><?php echo (($LAST_DATA['hDate'] != 0)? date("Y-m-d", $LAST_DATA['hDate']):'')?></td>
		</tr>
		<?php
	endforeach;
	$TABLE = ob_get_clean();
	
	ob_start();
	?>
    <table class="m-datatable">
    <thead>
        <tr>
            <th>Lead</th>
            <th>Created</th>
            <th>Rep</th>
            <th>Status</th>
            <th>State</th>
            <th>Income</th>
            <th>Last Contact</th>
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
                field: "Last Contact",
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
    	<form id="filterSearhForm" class="m-form m-form--fit m-form--label-align-right" action="/viewreport/57" method="post">
		<h5>Filters</h5>
        <?php //print_r($_POST); ?>
        <div class="row">
        	<div class="col-4">
            	<div class="form-group m-form__group">
                	<label>Assigned Market Director</label>
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
                    <span class="m-form__help"><?php echo implode(" | ", $quickClick)?></span>
                    
                </div>                
                <div class="form-group m-form__group">
                	<label>Lead Status</label>       
                    <select class="form-control m-select2" id="LeadStages_id" name="LeadStages_id[]" multiple="multiple">
                        <?php
						if(isset($_POST['LeadStages_id'])) {
							$preSelected = $_POST['LeadStages_id'];
						} else {
							$preSelected = array();	
						}
						$sql = "SELECT * FROM LeadStages WHERE 1";
						$snd = $DB->get_multi_result($sql);
						foreach($snd as $dta):
							?><option value="<?php echo $dta['LeadStages_id']?>" <?php echo ((in_array($dta['LeadStages_id'], $preSelected))? 'selected':'')?>><?php echo $dta['LeadStages_name']?></option><?php
						endforeach;
						?>
                    </select>
                    
                </div>
			</div>
        	<div class="col-4">
            	<div class="form-group m-form__group">
                	<label>Lead State</label>        
                    <select class="form-control m-select2" id="prQuestion_1713" name="prQuestion_1713[]" multiple="multiple">
                    <?php
                    if(isset($_POST['prQuestion_1713'])) {
                        $preSelected = $_POST['prQuestion_1713'];
                    } else {
                        $preSelected = array();	
                    }						
                    $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='1713' ORDER BY QuestionsAnswers_order ASC";
                    $qa_snd = $DB->get_multi_result($qa_sql);
                    //print_r($qa_snd);
                    ob_start();
                    foreach($qa_snd as $qa_dta):
                        ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                    endforeach;
                    ?>
                    </select>                    
                </div>
                <div class="form-group m-form__group">
                 	<label>Excluded Users (Archived)</label>
                    <div style="line-height:11px;"><small><?php echo implode(", ", $ExcludedUserList)?></small></div>
                </div>                	
            </div>
            <div class="col-4">
            	<div class="form-group m-form__group">
                    <label>Income</label>        
                    <select class="form-control m-select2" id="prQuestion_631" name="prQuestion_631[]" multiple="multiple">
                    <?php
                    if(isset($_POST['prQuestion_631'])) {
                        $preSelected = $_POST['prQuestion_631'];
                    } else {
                        $preSelected = array();	
                    }						
                    $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='631' ORDER BY QuestionsAnswers_order ASC";
                    $qa_snd = $DB->get_multi_result($qa_sql);
                    //print_r($qa_snd);
                    ob_start();
                    foreach($qa_snd as $qa_dta):
                        ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                    endforeach;
                    ?>
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

	$('#Assigned_userID').select2({
		placeholder: "Select Salesperson(s)",
		allowClear: !0
	});
	$('#prQuestion_1713').select2({
		placeholder: "Select State(s)",
		allowClear: !0
	});	
	$('#prQuestion_631').select2({
		placeholder: "Select Income(s)",
		allowClear: !0
	});
	$('#LeadStages_id').select2({
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
	$("#Assigned_userID").val(Values).trigger('change');
}
</script>        




