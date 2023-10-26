<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.sales.php");
include_once("class.encryption.php");
include_once("class.sessions.php");
$DB = new database();
$DB->connect();

$RECORD = new Record($DB);
$SALES = new Sales($DB);
$ENC = new encryption();
$SESSION = new Session($DB, $ENC);

function countLeadRecords($year, $income=array(), $gender=false) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	
	$sql = "SELECT count(*) as count FROM Persons INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id  WHERE (Persons.DateCreated >= '".$startEpoch."' AND Persons.DateCreated <= '".$enderEpoch."')";
	
	if(count($income) != 0):
		$sql .= "AND PersonsProfile.prQuestion_631 IN ('".implode("','", $income)."')";
	endif;
	
	if($gender):
		$sql .= " AND Persons.Gender='".$gender."'";
	endif;
	
	$snd = $DB->get_single_result($sql);
	return $snd['count'];	
}

function getPercLeadsByGender($YEAR, $gender) {
	$m_leads = countLeadRecords($YEAR, array(), 'M');
	$f_leads = countLeadRecords($YEAR, array(), 'F');
	$t_leads = $m_leads + $f_leads;
	
	if($gender == 'M'):
		return round((($m_leads / $t_leads) * 100), 2);
	else:
		return round((($f_leads / $t_leads) * 100), 2);
	endif;
}

function countSalesFiltered($year, $peak_amt, $floor_amt, $gender, $types=NULL) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	
	$sql = "SELECT count(*) as count FROM PersonsSales INNER JOIN Persons ON Persons.Person_id=PersonsSales.Persons_Person_id WHERE Persons.Gender='".$gender."' AND (PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales_dateCreated <= '".$enderEpoch."') AND (PersonsSales_payment >= '".$floor_amt."' AND PersonsSales_payment <= '".$peak_amt."')";
	//echo $sql;
	if($types != NULL) {
		$sql .= " AND PersonsSales_packageID IN (".implode(",", $types).")";	
	}
	//echo $sql;
	$snd = $DB->get_single_result($sql);
	return $snd['count'];
}
function countLeadsByIncome($year, $incomes=array(), $gender=false) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	$sql = "SELECT count(*) as count FROM Persons INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id ";
	$sql .= "WHERE (Persons.DateCreated >= '".$startEpoch."' AND Persons.DateCreated <= '".$enderEpoch."') ";
	$sql .= "AND PersonsProfile.prQuestion_631 IN ('".implode("','", $incomes)."') ";
	if($gender) {
		$sql .= "AND Persons.Gender='".$gender."' ";
	}
	//echo $sql;
	$snd = $DB->get_single_result($sql);
	return $snd['count'];
}
function countSalesByIncome($year, $incomes=array(), $gender=false) {
	global $DB, $ENC;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	$sql = "
	SELECT count(*) as count 
	FROM PersonsSales 
	INNER JOIN Persons ON Persons.Person_id=PersonsSales.Persons_Person_id 
	INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	WHERE
		(PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales_dateCreated <= '".$enderEpoch."')
	AND
		PersonsProfile.prQuestion_631 IN ('".implode("','", $incomes)."') 
	";
	if($gender) {
		$sql .= "AND Persons.Gender='".$gender."' ";
	}
	//echo $sql;
	$snd = $DB->get_single_result($sql);
	$newsql = str_replace("count(*) as count", "*, CONCAT(FirstName,' ',LastName) as Name", $sql);
	return '<a href="javascript:previewRecords(\'Details for '.$year.'-'.implode(",", $incomes).'\', \''.$ENC->encrypt($newsql).'\');">'.$snd['count'].'</a>';
}
	
	

for($y=2016; $y<(date("Y") + 1); $y++):
	$YEAR_SCOPE[] = $y;
endfor;


?>
<div class="row">
	<div class="col-md-6">
    
<table class="table table-sm m-table">
    <thead class="thead-inverse">
        <tr>
            <th><a href="javascript:;" style="color:#FFF;" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="Lead Stats" data-content="These break down leads by stated income">Leads Summary Report</a></th>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <th class="text-right"><?php echo $YEAR?></th>            
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
    	<tr>
            <td scope="row">Total Leads</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo number_format(countLeadRecords($YEAR, array()), 0)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">&nbsp;</td>
            <td colspan="<?php echo (count($YEAR_SCOPE))?>" class="text-right">&nbsp;</td>           
        </tr>
       
        <tr>
            <td scope="row">Male Leads</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo number_format(countLeadRecords($YEAR, array(), 'M'), 0)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Female Leads</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo number_format(countLeadRecords($YEAR, array(), 'F'), 0)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Lead Male Percent</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo getPercLeadsByGender($YEAR, 'M')?>%</td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Lead Female Percent</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo getPercLeadsByGender($YEAR, 'F')?>%</td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">&nbsp;</td>
            <td colspan="<?php echo (count($YEAR_SCOPE))?>" class="text-right">&nbsp;</td>           
        </tr>
        
        <tr>
            <td scope="row">High Earners Total</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countLeadsByIncome($YEAR, array('More than $5M','$1M - $5M','$500K - $1M','$250K - $500K'))?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">High Earners Percent of Total Leads</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo @round((countLeadsByIncome($YEAR, array('More than $5M','$1M - $5M','$500K - $1M','$250K - $500K')) / countLeadRecords($YEAR, array()) * 100), 2)?>%</td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">High Earners Male Percentage</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo @round(countLeadsByIncome($YEAR, array('More than $5M','$1M - $5M','$500K - $1M','$250K - $500K'), "M") / countLeadRecords($YEAR, array(), 'M') * 100, 2)?>%</td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">High Earners Female Percentage</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo @round(countLeadsByIncome($YEAR, array('More than $5M','$1M - $5M','$500K - $1M','$250K - $500K'), "F") / countLeadRecords($YEAR, array(), 'F') * 100, 2)?>%</td>
            <?php endforeach; ?>            
        </tr>
		<thead class="thead-inverse">
	        <tr>
        	<th colspan="<?php echo ((count($YEAR_SCOPE)) + 1)?>" class="text-center">Leads Income/Gender Breakdown</th>           
    		</tr>
        </thead>
        <?php
		$ic_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id=631 ORDER BY QuestionsAnswers_order ASC";
		$ic_snd = $DB->get_multi_result($ic_sql);
		foreach($ic_snd as $ic_dta):
		?> 
        <tr>
            <td scope="row">Males <?php echo $ic_dta['QuestionsAnswers_value']?> </td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countLeadsByIncome($YEAR, array($ic_dta['QuestionsAnswers_value']), "M")?></td>
            <?php endforeach; ?>            
        </tr>
        <?php
		endforeach;
		?>
        <tr>
        	<td colspan="<?php echo ((count($YEAR_SCOPE)) + 1)?>" class="text-center">&nbsp;</td>           
    	</tr>
        <?php
		$ic_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id=631 ORDER BY QuestionsAnswers_order ASC";
		$ic_snd = $DB->get_multi_result($ic_sql);
		foreach($ic_snd as $ic_dta):
		?> 
        <tr>
            <td scope="row">Females <?php echo $ic_dta['QuestionsAnswers_value']?> </td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countLeadsByIncome($YEAR, array($ic_dta['QuestionsAnswers_value']), "F")?></td>
            <?php endforeach; ?>            
        </tr>
        <?php
		endforeach;
		?>
    </tbody>
</table>
	
    </div>
    <div class="col-md-6">
    
<table class="table table-sm m-table">
    <thead class="thead-inverse">
        <tr>
            <th><a href="javascript:;" style="color:#FFF;" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="Sales Stats" data-content="These break down sales by stated income">Sales Summary Report</a></th>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <th class="text-right"><?php echo $YEAR?></th>            
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
    
    <?php
	$ic_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id=631 ORDER BY QuestionsAnswers_order ASC";
	$ic_snd = $DB->get_multi_result($ic_sql);
	foreach($ic_snd as $ic_dta):
	?> 
	<tr>
		<td scope="row">Males <?php echo $ic_dta['QuestionsAnswers_value']?> </td>
		<?php foreach($YEAR_SCOPE as $YEAR): ?>
		<td class="text-right"><?php echo countSalesByIncome($YEAR, array($ic_dta['QuestionsAnswers_value']), "M")?></td>
		<?php endforeach; ?>            
	</tr>
	<?php
	endforeach;
	?>
    <tr>
        <td scope="row">&nbsp;</td>
        <td colspan="<?php echo (count($YEAR_SCOPE))?>" class="text-right">&nbsp;</td>           
    </tr>
    <?php
	foreach($ic_snd as $ic_dta):
	?> 
	<tr>
		<td scope="row">Female <?php echo $ic_dta['QuestionsAnswers_value']?> </td>
		<?php foreach($YEAR_SCOPE as $YEAR): ?>
		<td class="text-right"><?php echo countSalesByIncome($YEAR, array($ic_dta['QuestionsAnswers_value']), "F")?></td>
		<?php endforeach; ?>            
	</tr>
	<?php
	endforeach;
	?>
    <tr>
        <td scope="row">&nbsp;</td>
        <td colspan="<?php echo (count($YEAR_SCOPE))?>" class="text-right">&nbsp;</td>           
    </tr>
	</tbody>
</table>            
    
    
    </div>
</div>

<div class="modal fade" id="metricsDetailsModal" tabindex="-1" role="dialog" aria-labelledby="metricsDetailsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="metricsDetailsModalLabel"></h5>
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
function previewRecords(modalTitle, modalSQL) {
	$('#metricsDetailsModal').modal('show');
	$('#metricsDetailsModalLabel').html(modalTitle);
	mApp.block("#metricsDetailsModal .modal-body", {
		overlayColor: "#000000",
		type: "loader",
		state: "primary",
		message: "Loading..."
	});
	$.post('/ajax/smr.php?action=getDetails', {
		sql: modalSQL,
		kiss_token: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$("#metricsDetailsModal .modal-body").html(data);
		mApp.unblock("#metricsDetailsModal .modal-body");
		mApp.init();	
	});
}
</script>       

