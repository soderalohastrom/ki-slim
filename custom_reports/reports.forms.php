<?php
include_once("class.forms.php");
include_once("class.record.php");
include_once("class.encryption.php");
include_once("class.sessions.php");

$DB = new database();
$DB->connect();

$FORMS = new Forms($DB);
$RECORD = new Record($DB);
$ENC = new encryption();
$SESSION = new Session($DB, $ENC);
$FORM_ID = 0;
//$TAB = $pageParamaters['params'][1];

$AGES_ARRAY = array(
	array(
		'text'	=>	'Under 25',
		'value'	=>	'18-25'
	),
	array(
		'text'	=>	'26-35',
		'value'	=>	'26-35'
	),
	array(
		'text'	=>	'36-45',
		'value'	=>	'36-45'
	),
	array(
		'text'	=>	'46-55',
		'value'	=>	'46-55'
	),
	array(
		'text'	=>	'56-65',
		'value'	=>	'56-65'
	),
	array(
		'text'	=>	'66-75',
		'value'	=>	'66-75'
	),
	array(
		'text'	=>	'Over 75',
		'value'	=>	'76-99'
	)
);
$GENDERS_ARRAY = array(
	array(
		'text'	=>	'Male',
		'value'	=>	'M'
	),
	array(
		'text'	=>	'Female',
		'value'	=>	'F'
	)
);


function getIncomeColor($income) {
	switch($income):
		case 'Less Than $100k':
		$class = 'metal'; //   ';
		break;
		
		case '$100K - $150K':
		$class = 'info';
		break;
		
		case '$150K - $250K':
		$class = 'accent';
		break;
		
		case '$250K - $500K':
		$class = 'primary';
		break;
		
		case '$500K - $1M':
		$class = 'warning';
		break;
		
		case '$1M - $5M':
		$class = 'danger';
		break;
		
		case 'More than $5M':
		$class = 'success';
		break;
		
		case 'Other':
		$class = 'brand';
		break;
	endswitch;
	return $class;
}

$SCORES_ARRAY = array(
	array(
		'text'	=>	'Less than 25',
		'value'	=>	'0-25'
	),
	array(
		'text'	=>	'26 to 35',
		'value'	=>	'26-35'
	),
	array(
		'text'	=>	'36 to 45',
		'value'	=>	'36-45'
	),
	array(
		'text'	=>	'46 to 50',
		'value'	=>	'46-50'
	),
	array(
		'text'	=>	'51 to 100',
		'value'	=>	'51-100'
	),
	array(
		'text'	=>	'101 to 250',
		'value'	=>	'101-250'
	),
	array(
		'text'	=>	'Over 250',
		'value'	=>	'251-999'
	)
);
include("include.amchart_head.php");
?>
<style>
.m-widget14 .m-widget14__legend {
	margin-bottom:.1rem;
}
</style>
<?php
//print_r($pageParamaters);
//print_r($_POST);
if (isset($_POST['reportDateRange'])) {
	$dateParts = explode(" - ", $_POST['reportDateRange']);
	$startEpoch = strtotime($dateParts[0]);
	$enderEpoch = strtotime($dateParts[1]) + 86399;
	//echo "Custom:".$startEpoch."|".$enderEpoch;
	$dateParameters = array($startEpoch, $enderEpoch);	
	$dayDiff = (($enderEpoch - $startEpoch) / 86400) - 1;
	$dateDaysPreload = round($dayDiff);		
} else {
	$reportEpoch = time();
	$startEpoch = mktime(0, 0, 0, date("m", $reportEpoch), 1, date("Y", $reportEpoch));
	$enderEpoch = mktime(0, 0, 0, date("m", $reportEpoch), date("t", $reportEpoch), date("Y", $reportEpoch));	
	$_POST['reportDateRange'] = date("m/d/Y", $startEpoch).' - '.date("m/d/Y", $enderEpoch);
	$dateDaysPreload = 30;
	$_POST['mapView'] = 'heat';
}

$fm_sql = "SELECT * FROM CompanyForms WHERE 1 AND FormActive='1' ORDER BY FormName ASC";
//echo $sql."<br>\n";
$fm_snd = $this->db->get_multi_result($fm_sql);
ob_start();
foreach($fm_snd as $fm_dta):
	$AllForms[] = $fm_dta['FormID'];
endforeach;
$forms_select;  

//print_r($_POST);
if(isset($_POST['reportsToInclude'])) {
	$chosenForms = is_array($_POST['reportsToInclude']) ? $_POST['reportsToInclude'] : array($_POST['reportsToInclude']);
	$myForms = array($chosenForms);
} else {
	if($FORM_ID != 0) {
		$chosenForms = array($FORM_ID);
		$myForms = $chosenForms;
	} else {
		$chosenForms = $AllForms;	
		$myForms = array();
	}
}
//echo '<h1>my forms = '. print_r($_POST,true) .'</h1>';
ob_start();
foreach($fm_snd as $fm_dta):
	?><option value="<?php echo $fm_dta['FormID']?>" <?php echo ((in_array($fm_dta['FormID'], $myForms))? 'selected':'')?>><?php echo $fm_dta['FormName']?></option><?php
endforeach;
$forms_select = ob_get_clean(); 

?>
	<div class="m-portlet">
        <div class="m-portlet__head m--hide">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <span class="m-portlet__head-icon m--hide">
                        <i class="la la-gear"></i>
                    </span>
                    <h3 class="m-portlet__head-text">&nbsp;</h3>
                </div>
            </div>
        </div>
        <!--begin::Form-->
        <form class="m-form m-form--fit m-form--label-align-right m-form--group-seperator-dashed" action="/viewreport/169" method="post">
            <div class="m-portlet__body">
                <div class="form-group m-form__group row">
                    <label class="col-lg-1 col-form-label">
                        Range:
                    </label>
                    <div class="col-lg-3">
                        <div class="input-group m-input-group m-input-group--square">
                            <span class="input-group-addon">
                                <i class="la la-calendar-o"></i>
                            </span>
                            <input type="text" name="reportDateRange" id="reportDateRange" class="form-control m-input" value="<?php echo $_POST['reportDateRange']?>" placeholder="" autocomplete="off">
                        </div>
                        <span class="m-form__help">
                            Please enter the date range this report displays.
                        </span>
                    </div>
                    
                    <label class="col-lg-1 col-form-label">
                        Forms:
                    </label>
                    <div class="col-lg-3">
                        <div class="input-group ">
                            <span class="input-group-addon">
                                <i class="la la-calendar-o"></i>
                            </span>
                            <select name="reportsToInclude[]" id="reportsToInclude" class="form-control" multiple="multiple">
								<?php echo $forms_select?>           
                            </select>
                        </div>
                        <span class="m-form__help">
                            Please select forms to include on report.
                        </span>
                    </div>
                    
                    <div class="col-lg-2">
                        <div class="m-radio-list">
                            <label class="m-radio m-radio--solid">
                                <input type="radio" name="mapView" value="heat" <?php echo (($_POST['mapView'] == 'heat')? 'checked':'')?>>
                                Heatmap
                                <span></span>
                            </label>
                            <label class="m-radio m-radio--solid">
                                <input type="radio" name="mapView" value="points" <?php echo (($_POST['mapView'] == 'points')? 'checked':'')?>>
                                Individual Points
                                <span></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-lg-2">
                    	<button type="submit" class="btn btn-outline-primary m-btn m-btn--icon m-btn--pill">
                        	<span>
                            <i class="flaticon-line-graph"></i>
                            <span>Update View</span>
                            </span>
                        </button>
                    </div>

                </div>                
            </div>
            <div class="m-portlet__foot m-portlet__no-border m-portlet__foot--fit m--hide">
                <div class="m-form__actions m-form__actions--solid">
                    <div class="row">
                        <div class="col-lg-5"></div>
                        <div class="col-lg-7">                            
                            <button type="reset" class="btn btn-secondary">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!--end::Form-->
    </div>
    <!-- END SEARCH FORM PORTLET -->
    
<?php
//echo "FORM: ".$FORM_ID."<br>\n";
//echo "START: ".$startEpoch."<br>\n";
//echo "END: ".$enderEpoch."<br>\n";
//echo "RANGE: ".$_POST['reportDateRange']."<br>\n";
//echo '<h1>chosenforms = '.print_r($chosenForms,true).'</h1>';
if(count($chosenForms) == 1) {
	if($chosenForms[0] != 0):
		$form_sql 	= "SELECT * FROM CompanyForms WHERE FormID='".$chosenForms[0]."'";
		$form_data 	= $this->db->get_single_result($form_sql);
		$FORM_KEY 	= $form_data['FormCallString'];
		$FORM_NAME	= $form_data['FormName'];
		$FORM_KEYS	= array($form_data['FormCallString']);
	else:
		$FORM_KEY	= 'N/A';
		$FORM_NAME	= 'ALL FORMS COMBINED';
	endif;
} else {
	$FORM_KEY	= 'N/A';
	if(count($myForms) == 0):
		$FORM_NAME	= 'ALL FORMS COMBINED';
	else:
		$FORM_NAME	= 'MULTIPLE FORMS COMBINED';
	endif;
	foreach($chosenForms as $form):
		$form_sql 		= "SELECT * FROM CompanyForms WHERE FormID='".$form."'";
		$form_data 		= $this->db->get_single_result($form_sql);
		$FORM_KEYS[]	= $form_data['FormCallString'];	
	endforeach;
}
	

$nc_sql = "
SELECT
	Persons.Person_id,
	Persons.FirstName,
	Persons.LastName,
	Persons.Gender,
	Persons.DateOfBirth,
	Persons.LeadScore,
	Addresses.City,
	Addresses.State,
	Addresses.Postal,
	Addresses.Lattitude,
	Addresses.Longitude,
	PersonForms.FormSubmitted,
	PersonsProfile.prQuestion_631
FROM
	Persons
	INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	INNER JOIN Addresses ON Addresses.Person_id=Persons.Person_id
	INNER JOIN PersonForms ON PersonForms.Person_id=Persons.Person_id
WHERE
	1
AND
	(PersonForms.FormSubmitted >= '".date("Y-m-d", $startEpoch)." 00:00:00' AND PersonForms.FormSubmitted <= '".date("Y-m-d", $enderEpoch)." 23:59:59')
AND 
	PersonForms.Form_id IN ('".implode("','", $FORM_KEYS)."')
GROUP BY
	Persons.Person_id
ORDER BY
	PersonForms.FormSubmitted ASC
";
//echo $nc_sql;
$nc_snd = $this->db->get_multi_result($nc_sql);
if(!isset($nc_snd['empty_result'])):
	$STATS['new_records'] = count($nc_snd);	
	ob_start();
	?>
    <table class="table table-sm m-table m-table--head-bg-brand">
    <thead>
    	<tr>
        <th>Submitted</th>
        <th>Campaign ID</th>
        <th>Keyword</th>
        <th>Record</th>
        <th>Gen</th>
        <th>Age</th>
        <th>Location</th>
        <th>Income</th>
        <th>Score</th>
		</tr>
    </thead>
    <tbody>    
    <?php
	$incomes = array();
	foreach($nc_snd as $nc_dta):
	?>
    <tr>
		<td><?php echo $nc_dta['FormSubmitted']?></td>
        <td><a href="/profile/<?php echo $nc_dta['Person_id']?>" target="_blank" class="m-link"><?php echo $RECORD->get_personName($nc_dta['Person_id'])?></a></td>
        <td><?php echo $nc_dta['Gender']?></td>
        <td><?php echo $RECORD->get_personAge($nc_dta['DateOfBirth'], true)?></td>
        <td><?php echo $nc_dta['City']?> <?php echo $nc_dta['State']?> <?php echo $nc_dta['Postal']?></td>
        <td><?php echo $nc_dta['prQuestion_631']?></td>
        <td><?php echo $nc_dta['LeadScore']?></td>
    </tr>
    <?php
	$incomes[] = $nc_dta['prQuestion_631'];
	endforeach;
	?>
    </tbody>
    </table>    
    <?php
	$modal_table['newleads'] = ob_get_clean();
else:
	$STATS['new_records'] = 0;	
endif;

//print_r($incomes);
$income_counts = @array_count_values($incomes);
//print_r($income_counts);
$income_total = isset($incomes) && is_countable($incomes) ? count($incomes) : 1;

$income_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='631' ORDER BY QuestionsAnswers_order";
$income_snd = $this->db->get_multi_result($income_sql);
$i=1;
foreach($income_snd as $income_dta):
	$value = $income_dta['QuestionsAnswers_value'];
	//echo $value."<br>\n";
	if(isset($income_counts[$value])):
		$perc = round(($income_counts[$value]/$income_total) * 100);
		$chart['incomes'][] = array(
			'label'		=>	$value,
			'value'		=>	$perc,
			'class'		=>	getIncomeColor($value)
		);
		/*
		$chart['incomeChart'][] = array(
			'value'		=>	$perc,
			'className'	=>	"custom",
			'meta'		=>	array('color'	=> 'mUtil.getColor("'.getIncomeColor($value).'")')							
		);
		*/
		$chart['incomeChart'][] = "{ 
	value: ".$perc.", 
	className: \"custom\", 
	meta: { 
		color: mUtil.getColor(\"".getIncomeColor($value)."\") 
	}
}";
		$chart['incomeChartLabels'][] = $i;
		$i++;
	else:
		$chart['incomes'][] = array(
			'label'		=>	$value,
			'value'		=>	0,
			'class'		=>	getIncomeColor($value)
		);
	endif;
	
	
endforeach;


// NEW RECORD TRENDS //
for($i=11; $i>-1; $i--):
	$s_epoch = mktime(0,0,0, (date("m") - $i), 1, date("Y"));
	$e_epoch = mktime(23, 59, 59, (date("m") - $i), date("t", $s_epoch), date("Y"));
	$chart['trends']['labels'][] = date("M-Y", $s_epoch);
	$nc_sql = "
	SELECT
		Persons.Person_id
	FROM
		Persons
		INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
		INNER JOIN Addresses ON Addresses.Person_id=Persons.Person_id
		INNER JOIN PersonForms ON PersonForms.Person_id=Persons.Person_id
	WHERE
		1
	AND
		(PersonForms.FormSubmitted >= '".date("Y-m-d", $s_epoch)." 00:00:00' AND PersonForms.FormSubmitted <= '".date("Y-m-d", $e_epoch)." 23:59:59')
	AND 
		PersonForms.Form_id IN ('".implode("','", $FORM_KEYS)."')
	GROUP BY
		Persons.Person_id	
	";
	//echo $nc_sql."<br>\n";
	$nc_snd = $this->db->get_multi_result($nc_sql);
	if(isset($nc_snd['empty_result'])) {
		$chart['trends']['data'][] = 0;
	} else {
		$chart['trends']['data'][] = count($nc_snd);
	}
endfor;
//print_r($chart);

// NEW SALES STUFF //
$ns_sql = "
SELECT
	Persons.Person_id,
	Persons.FirstName,
	Persons.LastName,
	Persons.Gender,
	Persons.DateOfBirth,
	Persons.LeadScore,
	PersonForms.FormSubmitted,
	PersonsProfile.prQuestion_631,
	PersonsSales.PersonsSales_basePrice
FROM
	Persons
	INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	INNER JOIN PersonForms ON PersonForms.Person_id=Persons.Person_id
	INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id
WHERE
	1
AND
	(PersonsSales.PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales.PersonsSales_dateCreated <= '".$enderEpoch."')
AND 
	PersonForms.Form_id IN ('".implode("','", $FORM_KEYS)."')
GROUP BY
	Persons.Person_id
ORDER BY
	PersonForms.FormSubmitted ASC
";
//echo $ns_sql;
$ns_snd = $this->db->get_multi_result($ns_sql);
if(!isset($ns_snd['empty_result'])):
	$STATS['new_sales'] = count($ns_snd);
	ob_start();
	?>
    <table class="table table-sm m-table m-table--head-bg-primary">
    <thead>
    	<tr>
        <th>Submitted</th>
        <th>Campaign Id</th>
        <th>Keyword</th>
        <th>Record</th>
        <th>Gen</th>
        <th>Age</th>
        <th>Location</th>
        <th>Income</th>
        <th>Score</th>
        <th>Dollars</th>
		</tr>
    </thead>
    <tbody>    
    <?php
	foreach($ns_snd as $ns_dta):
	?>
    <tr>
		<td><?php echo $ns_dta['FormSubmitted']?></td>
        <td><?php echo $RECORD->get_personName($ns_dta['Person_id'])?></td>
        <td><?php echo $ns_dta['Gender']?></td>
        <td><?php echo $RECORD->get_personAge($ns_dta['DateOfBirth'], true)?></td>
        <td><?php echo $ns_dta['City']?> <?php echo $ns_dta['State']?> <?php echo $ns_dta['Postal']?></td>
        <td><?php echo $ns_dta['prQuestion_631']?></td>
        <td><?php echo $ns_dta['LeadScore']?></td>
        <td><?php echo $ns_dta['PersonsSales_basePrice']?></td>
    </tr>
    <?php
	$saleDollarsArray[] = $ns_dta['PersonsSales_basePrice'];
	endforeach;
	?>
    </tbody>
    </table>    
    <?php
	$modal_table['newsales'] = ob_get_clean();
else:
	$STATS['new_sales'] = 0;
endif;	


// GRAPH: FORM VIEW vs SUBMIT TRENDS //
$viewEpoch = $startEpoch;
while ($viewEpoch <= $enderEpoch) {
	$currentDate = date("Y-m-d", $viewEpoch);
	$nextEpoch = mktime(0,0,0, date("m", $viewEpoch), (date("d", $viewEpoch) + 1), date("Y", $viewEpoch));
	$viewSepoch = mktime(0,0,0, date("m", $viewEpoch), (date("d", $viewEpoch)), date("Y", $viewEpoch));
	$viewEepoch = mktime(23,59,59, date("m", $viewEpoch), (date("d", $viewEpoch)), date("Y", $viewEpoch));
	//echo $currentDate."<br>\n";
	$viewEpoch = $nextEpoch;
	$fv_sql = "SELECT count(*) as count FROM CompanyFormsViews WHERE Form_id IN ('".implode("','", $chosenForms)."') AND (ViewDate >= '".$viewSepoch."' AND ViewDate <= '".($viewEepoch - 1)."')";
	//echo $fv_sql."<br>\n";
	$fv_snd = $this->db->get_single_result($fv_sql);
	$form_views = $fv_snd['count'];
	
	$fs_sql = "SELECT Persons.Person_id FROM Persons INNER JOIN PersonForms ON PersonForms.Person_id=Persons.Person_id AND (PersonForms.FormSubmitted >= '".date("Y-m-d", $viewSepoch)." 00:00:00' AND PersonForms.FormSubmitted <= '".date("Y-m-d", ($viewEepoch - 1))." 23:59:59') AND PersonForms.Form_id IN ('".implode("','", $FORM_KEYS)."') GROUP BY Persons.Person_id";
	//echo $fs_sql."<br>\n";
	$fs_snd = $this->db->get_multi_result($fs_sql, true);
	$form_submits = $fs_snd;
	
	$conversion_rate = @round((($form_submits / $form_views) * 100), 2);
	
	settype($form_views, "integer");
	settype($form_submits, "integer");
	$LineChart[] = array(
		'date'		=>	$currentDate,
		'views'		=>	$form_views,
		'submits'	=>	$form_submits,
		'conversion'=>	$conversion_rate
	);
	$views_tally_array[] = $form_views;
	$submit_tally_array[] = $form_submits;	
}
//echo array_sum($views_tally_array)." / ".array_sum($submit_tally_array)." * 100";
$TOTAL_CONVERSION_RATE = @round(((array_sum($submit_tally_array) / array_sum($views_tally_array)) * 100), 2);
//error_log('linechart'.print_r($LineChart,true));
$LineChart_JS = json_encode($LineChart, JSON_PARTIAL_OUTPUT_ON_ERROR);

// GRAPH: INDIIVIDUAL FORM SUBMITS vs VIEWS OVER REPORT SCOPE //
foreach($chosenForms as $form):
	$form_sql 		= "SELECT * FROM CompanyForms WHERE FormID='".$form."'";
	$form_data 		= $DB->get_single_result($form_sql);
	//$FORM_KEYS[]	= $form_data['FormCallString'];
	
	$fv_sql = "SELECT count(*) as count FROM CompanyFormsViews WHERE Form_id='".$form."' AND (ViewDate >= '".$startEpoch."' AND ViewDate <= '".$enderEpoch."')";
	//echo $fv_sql."<br>\n";
	$fv_snd = $this->db->get_single_result($fv_sql);
	$form_views = $fv_snd['count'];
	
	$fs_sql = "SELECT Persons.Person_id FROM Persons INNER JOIN PersonForms ON PersonForms.Person_id=Persons.Person_id AND (PersonForms.FormSubmitted >= '".date("Y-m-d", $startEpoch)." 00:00:00' AND PersonForms.FormSubmitted <= '".date("Y-m-d", $enderEpoch)." 23:59:59') AND PersonForms.Form_id='".$form_data['FormCallString']."' GROUP BY Persons.Person_id";
	//echo $fs_sql."<br>\n";
	$fs_snd = $this->db->get_multi_result($fs_sql, true);
	$form_submits = $fs_snd;
	
	$conversion_rate = @round((($form_submits / $form_views) * 100), 2);
	
	if(($form_views != 0) && ($form_submits != 0)):
	$indFormVvS[] = array(
		"form"		=>	$form_data['FormName'],
		"views"		=>	$form_views,
		"submits"	=>	$form_submits,
		"rate"		=>	$conversion_rate	
	);
	endif;
	
	$dataPoint['form'] = $form_data['FormName'];
	// AGE CHART QUERIES //
	$showForm = false;
	foreach($AGES_ARRAY as $source_DTA):
		$ageParts= explode("-", $source_DTA['value']);
		$low_epoch = mktime(0,0,0, date("m"), date("d"), (date("Y") - $ageParts[1]));
		$high_epoch = mktime(23,59,59, date("m"), date("d"), (date("Y") - $ageParts[0]));
		
		$fs_sql = "SELECT Persons.Person_id FROM Persons INNER JOIN PersonForms ON PersonForms.Person_id=Persons.Person_id 
		AND (PersonForms.FormSubmitted >= '".date("Y-m-d", $startEpoch)." 00:00:00' 
		AND PersonForms.FormSubmitted <= '".date("Y-m-d", $enderEpoch)." 23:59:59') 
		AND PersonForms.Form_id='".$form_data['FormCallString']."'
		WHERE 
		 DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), Persons.DateOfBirth)), '%Y')+0 
    	 BETWEEN " . $ageParts[1] . " AND " . $ageParts[0] . " 
		 GROUP BY Persons.Person_id";
		//echo $fs_sql."<br>\n";
		$fs_snd = $this->db->get_multi_result($fs_sql, true);
		$form_submits = $fs_snd;		
		$dataPoint[$source_DTA['value']] = $form_submits;
		if($form_submits > 0) {
			$showForm = true;
		}
		$temp_tally_age[$source_DTA['value']][] = $form_submits;
	endforeach;	
	if($showForm) {
		$dataPoints_age[] = $dataPoint;
	}
	unset($dataPoint);
	$showForm = false;
	
	// GENDER CHART QUERIES //
	$showForm = false;
	$dataPoint_G['form'] = $form_data['FormName'];
	foreach($GENDERS_ARRAY as $gender):		
		$fs_sql = "SELECT Persons.Person_id FROM Persons INNER JOIN PersonForms ON PersonForms.Person_id=Persons.Person_id 
		AND (PersonForms.FormSubmitted >= '".date("Y-m-d", $startEpoch)." 00:00:00' 
		AND PersonForms.FormSubmitted <= '".date("Y-m-d", $enderEpoch)." 23:59:59') 
		AND PersonForms.Form_id='".$form_data['FormCallString']."'
		WHERE Persons.Gender='".$gender['value']."'
		GROUP BY Persons.Person_id";
		//echo $fs_sql."<br>\n";
		$fs_snd = $this->db->get_multi_result($fs_sql, true);
		$form_submits = $fs_snd;		
		$dataPoint_G[$gender['text']] = $form_submits;
		if($form_submits > 0) {
			$showForm = true;
		}
		$temp_tally_gender[$gender['text']][] = $form_submits;
	endforeach;	
	if($showForm) {
		$dataPoints_gender[] = $dataPoint_G;
	}
	unset($dataPoint_G);
	$showForm = false;	
	
	// INCOME CHART QUERIES //
	$income_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='631' ORDER BY QuestionsAnswers_order";
	$income_snd = $this->db->get_multi_result($income_sql);
	$dataPoint_I['form'] = $form_data['FormName'];
	$showForm = false;
	foreach($income_snd as $income_dta):
		$value = $income_dta['QuestionsAnswers_value'];
		$fs_sql = "SELECT Persons.Person_id 
		FROM Persons 
		INNER JOIN PersonForms ON PersonForms.Person_id=Persons.Person_id
		INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id 
		WHERE (PersonForms.FormSubmitted >= '".date("Y-m-d", $startEpoch)." 00:00:00' 
		AND PersonForms.FormSubmitted <= '".date("Y-m-d", $enderEpoch)." 23:59:59') 
		AND PersonForms.Form_id='".$form_data['FormCallString']."'
		AND PersonsProfile.prQuestion_631='".$value."'
		GROUP BY Persons.Person_id";
		//echo $fs_sql."<br>\n";
		$fs_snd = $this->db->get_multi_result($fs_sql, true);
		$form_submits = $fs_snd;		
		$dataPoint_I[$value] = $form_submits;
		if($form_submits > 0) {
			$showForm = true;
		}
		$temp_tally_income[$value][] = $form_submits;
	endforeach;
	if($showForm) {
		$dataPoints_income[] = $dataPoint_I;
	}
	unset($dataPoint_I);
	$showForm = false;
	
	$dataPoint_F['form'] = $form_data['FormName'];
	// AGE CHART QUERIES //
	$showForm = false;
	foreach($SCORES_ARRAY as $source_DTA):
		$scoreParts= explode("-", $source_DTA['value']);
		//$low_epoch = mktime(0,0,0, date("m"), date("d"), (date("Y") - $ageParts[1]));
		//$high_epoch = mktime(23,59,59, date("m"), date("d"), (date("Y") - $ageParts[0]));
		
		$fs_sql = "SELECT Persons.Person_id FROM Persons INNER JOIN PersonForms ON PersonForms.Person_id=Persons.Person_id 
		AND (PersonForms.FormSubmitted >= '".date("Y-m-d", $startEpoch)." 00:00:00' 
		AND PersonForms.FormSubmitted <= '".date("Y-m-d", $enderEpoch)." 23:59:59') 
		AND PersonForms.Form_id='".$form_data['FormCallString']."'
		WHERE Persons.LeadScore BETWEEN '".$scoreParts[0]."' AND '".$scoreParts[1]."'
		GROUP BY Persons.Person_id";
		//echo $fs_sql."<br>\n";
		$fs_snd = $this->db->get_multi_result($fs_sql, true);
		$form_submits = $fs_snd;		
		$dataPoint_F[$source_DTA['value']] = $form_submits;
		if($form_submits > 0) {
			$showForm = true;
		}
		$temp_tally_scores[$source_DTA['value']][] = $form_submits;
	endforeach;	
	if($showForm) {
		$dataPoints_scores[] = $dataPoint_F;
	}
	unset($dataPoint_F);
	$showForm = false;
	
endforeach;


$formsVvS_JS = isset($indFormVvS) ? json_encode($indFormVvS) : json_encode(null);
$count_indFormVvS = isset($indFormVvS) && is_countable($indFormVvS) ? count($indFormVvS) : 1;
$chartHeight = (($count_indFormVvS * 50) + 100);
if($chartHeight < 640) {
	$chartHeight = 640;
}

// AGE //
$temp_age_keys = array_keys($temp_tally_age);
for($i=0; $i<count($temp_age_keys); $i++) {
	$keyName = $temp_age_keys[$i];
	$tempSum = @array_sum($temp_tally_age[$keyName]);
	$agePieChart[] = array(
		'label'	=>	$keyName,
		'value'	=>	$tempSum	
	);
}
$dataPoints_age_JS = isset($dataPoints_age) ? json_encode($dataPoints_age) : json_encode(null);
$agePieChart_JS = json_encode($agePieChart);

// GENDER //
$temp_gender_keys = array_keys($temp_tally_gender);
for($i=0; $i<count($temp_gender_keys); $i++) {
	$keyName = $temp_gender_keys[$i];
	$tempSum = @array_sum($temp_tally_gender[$keyName]);
	$genderPieChart[] = array(
		'label'	=>	$keyName,
		'value'	=>	$tempSum	
	);
}
$genderPieChart_JS = json_encode($genderPieChart);
$dataPoints_gender_JS = isset($dataPoints_gender) ? json_encode($dataPoints_gender) : json_encode(null);

// INCOME //
$temp_income_keys = array_keys($temp_tally_income);
for($i=0; $i<count($temp_income_keys); $i++) {
	$keyName = $temp_income_keys[$i];
	$tempSum = @array_sum($temp_tally_income[$keyName]);
	$incomePieChart[] = array(
		'label'	=>	$keyName,
		'value'	=>	$tempSum	
	);
}
$dataPoints_income_JS = isset($dataPoints_income) ? json_encode($dataPoints_income) : json_encode(null);
$incomePieChart_JS = json_encode($incomePieChart);

// SCORE //
$ts_sql = "SELECT LeadScore FROM Persons ORDER BY LeadScore DESC LIMIT 1";
$ts_snd = $this->db->get_single_result($ts_sql);
$topScore = $ts_snd['LeadScore'];
//echo "SCORE BETWEEN: 0-".$topScore."<br>\n";

$temp_score_keys = array_keys($temp_tally_scores);
for($i=0; $i<count($temp_score_keys); $i++) {
	$keyName = $temp_score_keys[$i];
	$tempSum = @array_sum($temp_tally_scores[$keyName]);
	$scoresPieChart[] = array(
		'label'	=>	$keyName,
		'value'	=>	$tempSum	
	);
}

$dataPoints_scores_JS =  isset($dataPoints_scores) ? json_encode($dataPoints_scores) : json_encode(null);
$scorePieChart_JS = json_encode($scoresPieChart);
//print_r($dataPoints_scores_JS);
//print_r($scorePieChart_JS);
?> 
<div class="m-portlet">
    <div class="m-portlet__body  m-portlet__body--no-padding">
        <div class="row m-row--no-padding m-row--col-separator-xl">
            <div class="col-xl-4">
                <!--begin:: Widgets/Daily Sales-->
                <div class="m-widget14">
                    <div class="m-widget14__header m--margin-bottom-30">
                        <h3 class="m-widget14__title">
                            <?php echo $FORM_NAME?>
                        </h3>
                        <span class="m-widget14__desc">
                            ID: <?php echo $FORM_KEY?>
                        </span>
                    </div>
                    <div class="m-widget14__chart" style="height:120px;">
                        <canvas id="m_chart_daily_sales"></canvas>
                    </div>
                    <div class="text-center m--font-success"><small>New Leads Past 12 Months from this Form</small></div>                   
                </div>
                <!--end:: Widgets/Daily Sales-->
            </div>
            
            <div class="col-xl-4">
                <!--begin:: Widgets/Stats2-1 -->
                <div class="m-widget1">
                    <div class="m-widget1__item">
                        <div class="row m-row--no-padding align-items-center">
                            <div class="col">
                                <h3 class="m-widget1__title">
                                    New Leads <a href="javascript:viewNewLeadDetails();" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-original-title="" data-content="view list of leads from this form" class="btn btn-outline-metal m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill pull-right"><i class="la la-signal"></i></a>  
                                </h3>
                                <span class="m-widget1__desc">
									created within the time frame
                                </span>
                            </div>
                            <div class="col m--align-right">
                                <span class="m-widget1__number m--font-brand">
                                    <?php echo $STATS['new_records']?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="m-widget1__item">
                        <div class="row m-row--no-padding align-items-center">
                            <div class="col">
                                <h3 class="m-widget1__title">
                                    Sales <a href="javascript:viewNewSaleDetails();" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-original-title="" data-content="view list of sales from this form" class="btn btn-outline-metal m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill pull-right"><i class="la la-signal"></i></a>
                                </h3>
                                <span class="m-widget1__desc">
                                    sales created from this form
                                </span>
                            </div>
                            <div class="col m--align-right">
                                <span class="m-widget1__number m--font-danger">
                                    <?php echo $STATS['new_sales']?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="m-widget1__item">
                        <div class="row m-row--no-padding align-items-center">
                            <div class="col">
                                <h3 class="m-widget1__title">
                                    Sale Dollars 
                                </h3>
                                <span class="m-widget1__desc">
                                    dollars in sales from this form.
                                </span>
                            </div>
                            <div class="col m--align-right">
                                <span class="m-widget1__number m--font-success">
                                    $<?php echo @number_format(array_sum($saleDollarsArray), 0)?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end:: Widgets/Stats2-1 -->
            </div>
            
            <div class="col-xl-4">
                <!--begin:: Widgets/Profit Share-->
                <div class="m-widget14">
                    <div class="m-widget14__header">
                        <h3 class="m-widget14__title">
                            Income Range
                        </h3>
                        <span class="m-widget14__desc">
                            distribution of income ranges among new records.
                        </span>
                    </div>
                    <div class="row  align-items-center">
                        <div class="col">
                            <div id="m_chart_profit_share" class="m-widget14__chart" style="height: 160px">
                                <div class="m-widget14__stat">
                                    &nbsp;
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="m-widget14__legends">
                                <?php foreach($chart['incomes'] as $cincomes): ?>
                                <div class="m-widget14__legend" style="opacity:<?php echo (($cincomes['value'] == 0)? '0.4':'1')?>;">
                                    <span class="m-widget14__legend-bullet m--bg-<?php echo $cincomes['class']?>"></span>
                                    <span class="m-widget14__legend-text">
                                        <?php echo $cincomes['value']?>% <?php echo $cincomes['label']?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end:: Widgets/Profit Share-->
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <!--begin:: Widgets/Tasks -->
        <div class="m-portlet m-portlet--full-height ">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <h3 class="m-portlet__head-text">&nbsp;</h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <ul class="nav nav-pills nav-pills--brand m-nav-pills--align-right m-nav-pills--btn-pill m-nav-pills--btn-sm" role="tablist">
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_widget2_tab1_content" role="tab">
                                Combined Forms
                            </a>
                        </li>
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget2_tab2_content" role="tab">
                                Individual Forms
                            </a>
                        </li>
                       <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget2_tab3_content" role="tab">
                                Age
                            </a>
                        </li>
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget2_tab4_content" role="tab">
                                Gender
                            </a>
                        </li>
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget2_tab5_content" role="tab">
                                Income
                            </a>
                        </li>
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget2_tab6_content" role="tab">
                                Scoring
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body">
                <div class="tab-content">
                    <div class="tab-pane active" id="m_widget2_tab1_content">
                    	<h5>Combined Views vs Submits</h5>
                        <div id="chartdiv2" style="height:640px;"></div>
                    </div>
                    <div class="tab-pane" id="m_widget2_tab2_content">
                    	<h5>Forms Views &amp; Submits</h5>
                    	<div id="chartdiv" style="height:<?php echo $chartHeight?>px;"></div>
                    </div>
                    <div class="tab-pane" id="m_widget2_tab3_content">                    	
                        <div class="row">
                        	<div class="col-8">
                            	<h5>Submits by Age and Form</h5>
		                        <div id="chartdiv3" style="height:640px;"></div>
							</div>
                            <div class="col-4">
                            	<h5>Submits by Age</h5>
                                <div id="chartdiv4" style="height:420px;"></div>
                            </div>
						</div>                                                            
					</div> 
                    <div class="tab-pane" id="m_widget2_tab4_content">                    	
                        <div class="row">
                        	<div class="col-8">
                            	<h5>Submits by Gender and Form</h5>
		                        <div id="chartdiv_genderForms" style="height:640px;"></div>
							</div>
                            <div class="col-4">
                            	<h5>Submits by Gender</h5>
                                <div id="chartdiv_genderPie" style="height:420px;"></div>
                            </div>
						</div>                                                            
					</div>
                    <div class="tab-pane" id="m_widget2_tab5_content">                    	
                        <div class="row">
                        	<div class="col-8">
                            	<h5>Submits by Income and Form</h5>
		                        <div id="chartdiv_IncomeForms" style="height:640px;"></div>
							</div>
                            <div class="col-4">
                            	<h5>Submits by Income</h5>
                                <div id="chartdiv_IncomePie" style="height:420px;"></div>
                            </div>
						</div>                                                            
					</div> 
                    <div class="tab-pane" id="m_widget2_tab6_content">                    	
                        <div class="row">
                        	<div class="col-8">
                            	<h5>Submits by Lead Score and Form</h5>
		                        <div id="chartdiv_ScoreForms" style="height:640px;"></div>
							</div>
                            <div class="col-4">
                            	<h5>Submits by Score</h5>
                                <div id="chartdiv_ScorePie" style="height:420px;"></div>
                            </div>
						</div>                                                            
					</div>                      
                </div>
            </div>
        </div>
        <!--end:: Widgets/Tasks -->
    </div>
</div>

<!-- MODAL SECTION -->
<div class="modal fade" id="modal-newrecords" tabindex="-1" role="dialog" aria-labelledby="modal-newrecordsLabel" aria-hidden="true">
	<div class="modal-dialog  modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modal-newrecordsLabel">New Leads from this Form</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<?php echo $modal_table['newleads']?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-newsales" tabindex="-1" role="dialog" aria-labelledby="modal-newsalesLabel" aria-hidden="true">
	<div class="modal-dialog  modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modal-newsalesLabel">New Sales from this Form</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<?php echo $modal_table['newsales']?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<?php

?>
<script>
function viewNewLeadDetails() {
	$('#modal-newrecords').modal('show');
}
function viewNewSaleDetails() {
	$('#modal-newsales').modal('show');
}

<!-- FORM TRENDS PAST 12 MONTHS -->
var e = {
	labels: <?php echo json_encode($chart['trends']['labels'])?>,
	datasets: [{
		backgroundColor: mUtil.getColor("success"),
		data: <?php echo json_encode($chart['trends']['data'])?>
	}]
};
var t = $("#m_chart_daily_sales");
new Chart(t, {
	type: "bar",
	data: e,
	options: {
		title: {
			display: !1
		},
		tooltips: {
			intersect: !1,
			mode: "nearest",
			xPadding: 10,
			yPadding: 10,
			caretPadding: 10
		},
		legend: {
			display: !1
		},
		responsive: !0,
		maintainAspectRatio: !1,
		barRadius: 4,
		scales: {
			xAxes: [{
				display: !1,
				gridLines: !1,
				stacked: !0
			}],
			yAxes: [{
				display: !1,
				stacked: !0,
				gridLines: !1
			}]
		},
		layout: {
			padding: {
				left: 0,
				right: 0,
				top: 0,
				bottom: 0
			}
		}
	}
});

<!-- INCOME DOHNUT CHART -->
if (0 != $("#m_chart_profit_share").length) {
	var e = new Chartist.Pie("#m_chart_profit_share", {
		series: [<?php echo @implode(",", $chart['incomeChart'])?>],
		labels: [<?php echo @implode(",", $chart['incomeChartLabels'])?>]
	}, {
		donut: !0,
		donutWidth: 35,
		showLabel: !1
	});
	e.on("draw", function(e) {
		if ("slice" === e.type) {
			var t = e.element._node.getTotalLength();
			e.element.attr({
				"stroke-dasharray": t + "px " + t + "px"
			});
			var a = {
				"stroke-dashoffset": {
					id: "anim" + e.index,
					dur: 1e3,
					from: -t + "px",
					to: "0px",
					easing: Chartist.Svg.Easing.easeOutQuint,
					fill: "freeze",
					stroke: e.meta.color
				}
			};
			0 !== e.index && (a["stroke-dashoffset"].begin = "anim" + (e.index - 1) + ".end"), e.element.attr({
				"stroke-dashoffset": -t + "px",
				stroke: e.meta.color
			}), e.element.animate(a, !1)
		}
	}), e.on("created", function() {
		window.__anim21278907124 && (clearTimeout(window.__anim21278907124), window.__anim21278907124 = null), window.__anim21278907124 = setTimeout(e.update.bind(e), 15e3)
	});
}

var gmap;
var marker = new Array();
var infowindow;
var searchCircle;
$(document).ready(function(e) {
	//var start = moment().subtract(30, 'days');
	//var end = moment();
    $('#reportDateRange').daterangepicker({
		buttonClasses: 'm-btn btn',
		applyClass: 'btn-primary',
		cancelClass: 'btn-secondary',
		//startDate: start,
		//endDate: end,
		ranges: {
			//'Today'			: [moment().subtract(1, 'days'), moment()],
			//'This Week'		: [moment().startOf('week'), moment().endOf('week')],
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
	
	
	
	renderStackedChart();
	renderLineChart();
	
	renderAgeStackedChart();
	renderAgePieChart();
	
	renderGenderStackedChart();
	renderGenderPieChart();
	
	renderIncomeStackedChart();
	renderIncomePieChart();
	
	renderScoresStackedChart();
	renderScoresPieChart();
});
function getPoints() {
	return [0];
}
function getCords() {
	return 0;
}


var chart;
function renderLineChart() {
	chart = AmCharts.makeChart("chartdiv2", {
    	type: "serial",
    	theme: "light",
    	marginRight: 10,
    	marginLeft: 10,
    	autoMarginOffset: 10,
    	mouseWheelZoomEnabled:false,
    	dataDateFormat: "YYYY-MM-DD",
		legend: {
			equalWidths: !0,
			useGraphSettings: !0,
			valueAlign: "left",
			valueWidth: 150,
			markerLabelGap: 10
		},
    	valueAxes: [{
        	id: "v1",
        	axisAlpha: 0,
        	position: "left",
        	ignoreAxisWidth:true
    	}],
    	balloon: {
        	"borderThickness": 1,
        	"shadowAlpha": 0
    	},
    	graphs: [{
        	id: "g2",
        	bullet: "round",
        	bulletBorderAlpha: 1,
        	bulletColor: "#FFFFFF",
        	bulletSize: 5,
        	hideBulletsCount: 50,
        	lineThickness: 2,
        	title: "",
        	useLineColorForBulletBorder: true,
        	valueField: "views",
        	//balloonText: "<span style='font-size:18px;'>[[value]] Views</span>"
			legendPeriodValueText: "   Total Views: [[value.sum]]",
        	legendValueText: "   [[value]] Views",			
    	},{
        	id: "g1",
        	bullet: "round",
        	bulletBorderAlpha: 1,
        	bulletColor: "#FFFFFF",
        	bulletSize: 5,
        	hideBulletsCount: 50,
        	lineThickness: 2,
        	title: "",
        	useLineColorForBulletBorder: true,
        	valueField: "submits",
			descriptionField: "conversion",
        	//balloonText: "<span style='font-size:18px;'>[[value]] Submits</span>"
			legendPeriodValueText: "   Total Submits: [[value.sum]] / Total Conversion Rate %<?php echo $TOTAL_CONVERSION_RATE?>",
        	legendValueText: "   [[value]] Submits / Conversion Rate %[[description]]",
    	}],
    	chartScrollbar: {
        	graph: "g1",
        	oppositeAxis:false,
        	offset:30,
        	scrollbarHeight: 35,
        	backgroundAlpha: 0,
        	selectedBackgroundAlpha: 0.1,
        	selectedBackgroundColor: "#888888",
        	graphFillAlpha: 0,
        	graphLineAlpha: 0.5,
        	selectedGraphFillAlpha: 0,
        	selectedGraphLineAlpha: 1,
        	autoGridCount:true,
        	color:"#AAAAAA"
    	},
    	chartCursor: {
        	pan: true,
        	valueLineEnabled: true,
        	valueLineBalloonEnabled: true,
        	cursorAlpha:1,
        	cursorColor:"#258cbb",
        	limitToGraph:"g1",
        	valueLineAlpha:0.2,
        	valueZoomable:true
    	},
		/*
    	"valueScrollbar":{
      		"oppositeAxis":false,
      		"offset":50,
      		"scrollbarHeight":10
    	},
		*/
    	categoryField: "date",
    	categoryAxis: {
        	parseDates: true,
        	dashLength: 1,
        	minorGridEnabled: true
    	},
    	export: {
        	enabled: true
    	},
    	dataProvider: <?php echo $LineChart_JS?>
	});
	chart.addListener("rendered", zoomChart);
	zoomChart();	
}
function zoomChart() {
    chart.zoomToIndexes(chart.dataProvider.length - 30, chart.dataProvider.length - 1);
}
var chart2;
function renderStackedChart() {
	chart2 = AmCharts.makeChart("chartdiv", {
    	theme: "light",
    	type: "serial",
		rotate: true,
    	dataProvider: <?php echo $formsVvS_JS?>,
		legend: {
			//equalWidths: !0,
			useGraphSettings: !0,
			valueAlign: "left",
			//valueWidth: 150,
			markerLabelGap: 5
		},
		chartCursor: {
			/*        	
			//pan: true,
        	valueLineEnabled: false,
        	valueLineBalloonEnabled: false,
			valueBalloonsEnabled: false,
        	cursorAlpha:0.1,
        	cursorColor:"#258cbb",
        	limitToGraph:"form_views",
        	valueLineAlpha:0.2,
        	valueZoomable:false,
			fullWidth:true,
			zoomable: false
			*/
			"categoryBalloonDateFormat": "DD",
			"cursorAlpha": 0.1,
			"cursorColor":"#000000",
			 "fullWidth":true,
			"valueBalloonsEnabled": false,
			"zoomable": false
    	},
		valueAxes: [{
			id:	"axis1",
			position: "right",
			title: "# of Form Submits/Views",
		}, {
        	id: "axis2",
        	//axisAlpha: 0,
        	//gridAlpha: 0,
        	//labelsEnabled: !1,
        	position: "left",
			title: "Form Conversion Rate"
    	}],
		startDuration: 1,
		graphs: [{
			id: "form_views",
			//balloonText: "[[category]] form Views: <b>[[value]]</b>",
			fillAlphas: 0.9,
			//lineAlpha: 0.2,
			title: "Views",
			type: "column",
			legendPeriodValueText: "[[value.sum]]",
			valueField: "views",
			valueAxis: "axis1"
		}, {
			id: "form_submits",
			//balloonText: "[[category]] form Submits: <b>[[value]]</b>",
			fillAlphas: 0.9,
			//lineAlpha: 0.2,
			title: "Submits",
			type: "column",
			//clustered:false,
			//columnWidth:0.5,
			legendPeriodValueText: "[[value.sum]]",
			valueField: "submits",
			valueAxis: "axis1"
		},{
			id: "conversion",
			bullet: "round",
			//bulletColor: "#FFFFFF",
			bulletSizeField: "size",
			bulletBorderAlpha: 1,
			bulletBorderThickness: 1,
			dashLengthField: "dashLength",
			//descriptionField: "dollars",
			legendValueText: "Conversion Rate: %[[value]]",
			legendPeriodValueText: "Combined Conv. Rate: %<?php echo $TOTAL_CONVERSION_RATE?>",
			//balloonText: "Sales:[[value]] / Dollars: [[description]]",
			//labelPosition: "right",
			//labelText: "[[description]]",
			title: " ",
			labelPosition: "right",
			fillAlphas: 0,
			valueField: "rate",
			valueAxis: "axis2"
		}],
		plotAreaFillAlphas: 0.1,
		categoryField: "form",
		categoryAxis: {
			gridPosition: "start"
		},
		export: {
			enabled: true
		 }

	});	
}

<!-- BEGIN: AGE CHARTS -->
var chart3;
function renderAgeStackedChart() {
	chart3 = AmCharts.makeChart("chartdiv3", {
    	type: "serial",
  		theme: "light",
		
    	legend: {
        	horizontalGap: 10,
        	maxColumns: 1,
        	position: "right",
    		useGraphSettings: true,
    		markerSize: 10
    	},
		valueAxes: [{
        	stackType: "regular",
        	axisAlpha: 0.3,
        	gridAlpha: 0
		}],		
    	dataProvider: <?php echo $dataPoints_age_JS?>,
    	graphs: [{
        	balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
        	fillAlphas: 0.8,
        	labelText: "[[value]]",
        	lineAlpha: 0.3,
        	title: "Under 25",
        	type: "column",
    		color: "#000000",
        	valueField: "18-25"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "26 to 35",
			type: "column",
			color: "#000000",
			valueField: "26-35"
    	}, {
        	balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
        	fillAlphas: 0.8,
        	labelText: "[[value]]",
        	lineAlpha: 0.3,
        	title: "36 to 45",
        	type: "column",
    		color: "#000000",
        	valueField: "36-45"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "46 to 55",
			type: "column",
			color: "#000000",
			valueField: "46-55"
    	}, {
        	balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
        	fillAlphas: 0.8,
        	labelText: "[[value]]",
        	lineAlpha: 0.3,
        	title: "56 to 65",
        	type: "column",
    		color: "#000000",
        	valueField: "56-65"
    	}, {
        	balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
        	fillAlphas: 0.8,
        	labelText: "[[value]]",
        	lineAlpha: 0.3,
        	title: "66 to 75",
        	type: "column",
    		color: "#000000",
        	valueField: "66-75"
    	}, {
        	balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
        	fillAlphas: 0.8,
        	labelText: "[[value]]",
        	lineAlpha: 0.3,
        	title: "Over 75",
        	type: "column",
    		color: "#000000",
        	valueField: "76-99"
    	}],
		categoryField: "form",
		categoryAxis: {
			gridPosition: "start",
			axisAlpha: 0,
			gridAlpha: 0,
			position: "left",
			labelRotation: 70
		},
	    export: {
    		enabled: true
     	}

	});	
}
var chart4;
function renderAgePieChart() {
	chart4 = AmCharts.makeChart( "chartdiv4", {
		type: "pie",
  		theme: "light",
		//marginRight: 3,
    	//marginLeft: 3,
    	//autoMarginOffset:3,
		
		
		//type: "pie",
  		labelRadius: -35,
  		labelText: "[[percents]]%",
		autoMargins: false,
		radius: 200,
		
  		dataProvider: <?php echo $agePieChart_JS?>,
		//innerRadius: "30%",
		valueField: "value",
		titleField: "label",
		//outlineAlpha: 0.4,
		balloonText: "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
		export: {
			enabled: true
		}
	});
}
<!-- END: AGE CHARTS -->

<!-- BEGIN: GENDER CHARTS -->
var chart5;
function renderGenderStackedChart() {
	chart5 = AmCharts.makeChart("chartdiv_genderForms", {
    	type: "serial",
  		theme: "light",
		
    	legend: {
        	horizontalGap: 10,
        	maxColumns: 1,
        	position: "right",
    		useGraphSettings: true,
    		markerSize: 10
    	},
		valueAxes: [{
        	stackType: "regular",
        	axisAlpha: 0.3,
        	gridAlpha: 0
		}],		
    	dataProvider: <?php echo $dataPoints_gender_JS?>,
    	graphs: [{
        	balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
        	fillAlphas: 0.8,
        	labelText: "[[value]]",
        	lineAlpha: 0.3,
        	title: "Male",
        	type: "column",
    		color: "#000000",
        	valueField: "Male"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "Female",
			type: "column",
			color: "#000000",
			valueField: "Female"
    	}],
		categoryField: "form",
		categoryAxis: {
			gridPosition: "start",
			axisAlpha: 0,
			gridAlpha: 0,
			position: "left",
			labelRotation: 70
		},
	    export: {
    		enabled: true
     	}

	});
}
var genderPieChart;
function renderGenderPieChart() {
	genderPieChart = AmCharts.makeChart( "chartdiv_genderPie", {
		type: "pie",
  		theme: "light",
  		labelRadius: -35,
  		labelText: "[[percents]]%",
		autoMargins: false,
		radius: 200,
		
  		dataProvider: <?php echo $genderPieChart_JS?>,
		//innerRadius: "30%",
		valueField: "value",
		titleField: "label",
		//outlineAlpha: 0.4,
		balloonText: "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
		export: {
			enabled: false
		}
	});
}
<!-- END: GENDER CHARTS -->

<!-- BEGIN: INCOME CHARTS -->
var chart7;
function renderIncomeStackedChart() {
	chart7 = AmCharts.makeChart("chartdiv_IncomeForms", {
    	type: "serial",
  		theme: "light",
		
    	legend: {
        	horizontalGap: 10,
        	maxColumns: 1,
        	position: "right",
    		useGraphSettings: true,
    		markerSize: 10
    	},
		valueAxes: [{
        	stackType: "regular",
        	axisAlpha: 0.3,
        	gridAlpha: 0
		}],		
    	dataProvider: <?php echo $dataPoints_income_JS?>,
    	graphs: [{
        	balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
        	fillAlphas: 0.8,
        	labelText: "[[value]]",
        	lineAlpha: 0.3,
        	title: "More than $5M",
        	type: "column",
    		color: "#000000",
        	valueField: "More than $5M"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "$1M - $5M",
			type: "column",
			color: "#000000",
			valueField: "$1M - $5M"
    	}, {
			
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "$500K - $1M",
			type: "column",
			color: "#000000",
			valueField: "$500K - $1M"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "$250K - $500K",
			type: "column",
			color: "#000000",
			valueField: "$250K - $500K"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "$150K - $250K",
			type: "column",
			color: "#000000",
			valueField: "$150K - $250K"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "$100K - $150K",
			type: "column",
			color: "#000000",
			valueField: "$100K - $150K"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "Less Than $100k",
			type: "column",
			color: "#000000",
			valueField: "Less Than $100k"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "Other",
			type: "column",
			color: "#000000",
			valueField: "Other"
    	}],
		categoryField: "form",
		categoryAxis: {
			gridPosition: "start",
			axisAlpha: 0,
			gridAlpha: 0,
			position: "left",
			labelRotation: 70
		},
	    export: {
    		enabled: true
     	}

	});
}
var chart8;
function renderIncomePieChart() {
	chart8 = AmCharts.makeChart( "chartdiv_IncomePie", {
		type: "pie",
  		theme: "light",
		//marginRight: 3,
    	//marginLeft: 3,
    	//autoMarginOffset:3,
		
		
		//type: "pie",
  		labelRadius: -35,
  		labelText: "[[percents]]%",
		autoMargins: false,
		radius: 200,
		
  		dataProvider: <?php echo $incomePieChart_JS?>,
		//innerRadius: "30%",
		valueField: "value",
		titleField: "label",
		//outlineAlpha: 0.4,
		balloonText: "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
		export: {
			enabled: true
		}
	});
}
<!-- END: INCOME CHARTS -->

<!-- BEGIN SCOR CHARTS -->

var chart9;
function renderScoresStackedChart() {
	chart9 = AmCharts.makeChart("chartdiv_ScoreForms", {
    	type: "serial",
  		theme: "light",
		
    	legend: {
        	horizontalGap: 10,
        	maxColumns: 1,
        	position: "right",
    		useGraphSettings: true,
    		markerSize: 10
    	},
		valueAxes: [{
        	stackType: "regular",
        	axisAlpha: 0.3,
        	gridAlpha: 0
		}],		
    	dataProvider: <?php echo $dataPoints_scores_JS?>,
    	graphs: [{
        	balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
        	fillAlphas: 0.8,
        	labelText: "[[value]]",
        	lineAlpha: 0.3,
        	title: "0 to 25",
        	type: "column",
    		color: "#000000",
        	valueField: "0-25"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "26 to 35",
			type: "column",
			color: "#000000",
			valueField: "26-35"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "36 to 45",
			type: "column",
			color: "#000000",
			valueField: "36-45"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "46 to 50",
			type: "column",
			color: "#000000",
			valueField: "46-50"
    	}, {
			
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "51 to 100",
			type: "column",
			color: "#000000",
			valueField: "51-100"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "101 to 250",
			type: "column",
			color: "#000000",
			valueField: "101-250"
    	}, {
			balloonText: "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
			fillAlphas: 0.8,
			labelText: "[[value]]",
			lineAlpha: 0.3,
			title: "Over 250",
			type: "column",
			color: "#000000",
			valueField: "251-999"
    	}],
		categoryField: "form",
		categoryAxis: {
			gridPosition: "start",
			axisAlpha: 0,
			gridAlpha: 0,
			position: "left",
			labelRotation: 70
		},
	    export: {
    		enabled: true
     	}
	});
}
var chart10;
function renderScoresPieChart() {
	chart10 = AmCharts.makeChart( "chartdiv_ScorePie", {
		type: "pie",
  		theme: "light",
		//marginRight: 3,
    	//marginLeft: 3,
    	//autoMarginOffset:3,
		
		
		//type: "pie",
  		labelRadius: -35,
  		labelText: "[[percents]]%",
		autoMargins: false,
		radius: 200,
		
  		dataProvider: <?php echo $scorePieChart_JS?>,
		//innerRadius: "30%",
		valueField: "value",
		titleField: "label",
		//outlineAlpha: 0.4,
		balloonText: "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
		export: {
			enabled: true
		}
	});
}

    

















</script>
