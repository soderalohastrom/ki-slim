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


for($y=2016; $y<(date("Y") + 1); $y++):
	$YEAR_SCOPE[] = $y;
endfor;


function countLeadRecords($year, $income=array(), $gender) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	
	$sql = "SELECT count(*) as count FROM Persons INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id  WHERE (Persons.DateCreated >= '".$startEpoch."' AND Persons.DateCreated <= '".$enderEpoch."') AND Persons.Gender='".$gender."' AND PersonsProfile.prQuestion_631 IN ('".implode("','", $income)."')";
	$snd = $DB->get_single_result($sql);
	return $snd['count'];	
}

function totalInDB($gender) {
	global $DB;
	$sql = "SELECT count(*) as count FROM Persons WHERE Gender='".$gender."'";
	$snd = $DB->get_single_result($sql);
	return $snd['count'];
}

function countRecordsByAge($year, $gender, $ageStart, $ageEnd) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	
	$ageStart = mktime(0,0,0, date("m"), date("d"), (date("Y") - $ageStart));
	$ageEnder = mktime(23,59,59, date("m"), date("d"), (date("Y") - $ageEnd));
	
	$sql = "SELECT count(*) as count FROM Persons INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id 
    WHERE (PersonsSales.PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales.PersonsSales_dateCreated <= '".$enderEpoch."') AND Persons.Gender='".$gender."' 
    AND DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), Persons.DateOfBirth)), '%Y')+0 
    BETWEEN " . $ageStart . " AND " . $ageEnd . " ";
	$snd = $DB->get_single_result($sql);
	return $snd['count'];	
}

function formsFilledOut($year) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	
	$sql = "SELECT count(*) as count FROM CompanyFormsViews WHERE (CompanyFormsViews.ViewDate >= '".$startEpoch."' AND CompanyFormsViews.ViewDate <= '".$enderEpoch."')";
	$snd = $DB->get_single_result($sql);
	$TOTAL_VIEWS = $snd['count'];
	
	$sql2 = "SELECT count(*) as count FROM PersonForms WHERE (FormSubmitted >= '".date("Y-m-d H:i:s", $startEpoch)."' AND FormSubmitted <= '".date("Y-m-d H:i:s", $enderEpoch)."')";
	//echo $sql2."<br>\n";
	$snd2 = $DB->get_single_result($sql2);
	$TOTAL_SUBS = $snd2['count'];
	
	if($TOTAL_SUBS != 0) {
		$PERC = round((($TOTAL_SUBS / $TOTAL_VIEWS) * 100), 1);
	} else {
		$PERC = 0.0;
	}
	return $PERC;
}

function countContacted($year) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	
	$sql = "SELECT count(DISTINCT(Person_id)) as count FROM PersonsCommHistory WHERE (MessageSentDate >= '".$startEpoch."' AND MessageSentDate <= '".$enderEpoch."')";
	$snd = $DB->get_single_result($sql);
	return number_format($snd['count'], 0);
}

function countSales($year) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	
	$sql = "SELECT count(*) as count FROM PersonsSales WHERE (PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales_dateCreated <= '".$enderEpoch."')"; 
	$snd = $DB->get_single_result($sql);
	return $snd['count'];
}
function countSalesFiltered($year, $peak_amt, $floor_amt, $types=NULL) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	
	$sql = "SELECT count(*) as count FROM PersonsSales WHERE (PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales_dateCreated <= '".$enderEpoch."') AND (PersonsSales_payment >= '".$floor_amt."' AND PersonsSales_payment <= '".$peak_amt."')";
	if($types != NULL) {
		$sql .= " AND PersonsSales_packageID IN (".implode(",", $types).")";	
	}
	$snd = $DB->get_single_result($sql);
	return $snd['count'];
}
function countLeads($year) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	$sql = "SELECT count(*) as count FROM Persons WHERE (Persons.DateCreated >= '".$startEpoch."' AND Persons.DateCreated <= '".$enderEpoch."')";
	$snd = $DB->get_single_result($sql);
	return $snd['count'];
	
}
function avgSalesPerMonth($year) {
	$SALES = countSales($year);
	if($year != date("Y")) {
		$PERC = $SALES / 12;
	} else {
		$PERC = $SALES / date("m");	
	}
	return $PERC;
}
function conversionRate($year) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	
	$SALES = countSales($year);
	$LEADS = countLeads($year);
	
	if($SALES != 0) {
		$PERC = round(($LEADS / $SALES), 1);
	} else {
		$PERC = 0.0;
	}
	return $PERC;
}
function averageSale($year) {
	global $DB;
	$startEpoch = mktime(0, 0, 0, 1, 1, $year);
	$enderEpoch = mktime(23, 59, 59, 12, 31, $year);
	
	$sql = "SELECT AVG(PersonsSales_payment ) as average FROM PersonsSales WHERE (PersonsSales_dateCreated >= '".$startEpoch."' AND PersonsSales_dateCreated <= '".$enderEpoch."')"; 
	$snd = $DB->get_single_result($sql);
	return number_format($snd['average'], 0);
	
}

?>
<div class="row">
	<div class="col-md-6">
    
<table class="table table-sm m-table">
    <thead class="thead-inverse">
        <tr>
            <th>Leads Summary Report</th>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <th class="text-right"><?php echo $YEAR?></th>            
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td scope="row">Male Leads 250k & Above</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countLeadRecords($YEAR, array('More than $5M','$1M - $5M','$500K - $1M','$250K - $500K'), 'M')?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Male Leads 500k & Above</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countLeadRecords($YEAR, array('More than $5M','$1M - $5M','$500K - $1M'), 'M')?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Male Leads 1 Mil & Above</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countLeadRecords($YEAR, array('More than $5M','$1M - $5M'), 'M')?></td>
            <?php endforeach; ?>            
        </tr>        
        <tr>
            <td scope="row">Female Leads 250k & Above</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countLeadRecords($YEAR, array('More than $5M','$1M - $5M','$500K - $1M','$250K - $500K'), 'F')?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Female Leads 500k & Above</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countLeadRecords($YEAR, array('More than $5M','$1M - $5M','$500K - $1M'), 'F')?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Female Leads 1 Mil & Above</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countLeadRecords($YEAR, array('More than $5M','$1M - $5M'), 'F')?></td>
            <?php endforeach; ?>            
        </tr>        
        <tr>
            <td scope="row">Percentage of Forms Filled Out</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo formsFilledOut($YEAR)?>%</td>
            <?php endforeach; ?>            
        </tr>        
        <tr>
            <td scope="row">Records Contacted</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countContacted($YEAR)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Avg Leads Sold Per Month</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo round(avgSalesPerMonth($YEAR), 1)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">New Leads</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo number_format(countLeads($YEAR), 0)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Total Sales</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo number_format(countSales($YEAR), 0)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Converstion Rate</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo conversionRate($YEAR)?>%</td>
            <?php endforeach; ?>            
        </tr>        
    </tbody>
</table>
<div id="m_amcharts_4" style="height:250px;"></div>
    
    </div>
    <div class="col-md-6">
    
<table class="table table-sm m-table">
    <thead class="thead-inverse">
        <tr>
            <th>Sales Summary Report</th>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <th class="text-right"><?php echo $YEAR?></th>            
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td scope="row">Men Under 25</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'M', 18, 25)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Men 26 - 35</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'M', 26, 35)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Men 36 - 45</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'M', 36, 45)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Men 46 - 55</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'M', 46, 55)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Men 56 - 65</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'M', 56, 65)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Men Over 65</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'M', 66, 99)?></td>
            <?php endforeach; ?>            
        </tr>        
        <tr>
            <td scope="row">Total Men in Database</td>
            <td colspan="<?php echo (count($YEAR_SCOPE))?>" class="text-right"><?php echo number_format(totalInDB('M'), 0)?></td>           
        </tr>
        
        <tr>
            <td scope="row">Women Under 25</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'F', 18, 25)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Women 26 - 35</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'F', 26, 35)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Women 36 - 45</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'F', 36, 45)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Women 46 - 55</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'F', 46, 55)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Women 56 - 65</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'F', 56, 65)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Women Over 65</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countRecordsByAge($YEAR, 'F', 66, 99)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row" style="border-bottom:#333 solid 1px;">Total Women in Database</td>
            <td colspan="<?php echo (count($YEAR_SCOPE))?>" class="text-right" style="border-bottom:#333 solid 1px;"><?php echo number_format(totalInDB('F'), 0)?></td>           
        </tr>
        <tr>
            <td scope="row">Average Sale</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right">$<?php echo averageSale($YEAR)?></td>
            <?php endforeach; ?>            
        </tr>
        
        <tr>
            <td scope="row">Sales over 100K</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countSalesFiltered($YEAR, 1000000000, 100000)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Sales between 45K &amp; 100K</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countSalesFiltered($YEAR, 100000, 45000)?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Sales Less than 45K</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countSalesFiltered($YEAR, 45000, 0)?></td>
            <?php endforeach; ?>            
        </tr>
        
        <tr>
            <td scope="row">National Sales under 45K</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countSalesFiltered($YEAR, 45000, 0, array(127))?></td>
            <?php endforeach; ?>            
        </tr>
        <tr>
            <td scope="row">Local Sales under 25K</td>
            <?php foreach($YEAR_SCOPE as $YEAR): ?>
            <td class="text-right"><?php echo countSalesFiltered($YEAR, 25000, 0, array(122,123))?></td>
            <?php endforeach; ?>            
        </tr>
    </tbody>
</table>
    
    </div>
</div>
<script>
$(document).ready(function(e) {
	AmCharts.makeChart("m_amcharts_4", {
		theme: "light",
		type: "serial",
		dataProvider: [{
			country: "2016",
			year2016: <?php echo countSales(2016)?>
		}, {
			country: "2017",
			year2016: <?php echo countSales(2017)?>
		}, {
			country: "2018",
			year2016: <?php echo countSales(2018)?>
		}, {
			country: "2019",
			year2016: <?php echo countSales(2019)?>
		}],
		valueAxes: [{
			position: "left",
			title: "Sales per Year"
		}],
		startDuration: 1,
		graphs: [{
			id: "g1",
			balloon: {
				drop: !0,
				adjustBorderColor: !1,
				color: "#ffffff"
			},
			balloonText: "Sales in [[category]]: <b>[[value]]</b>",
			bullet: "round",
			bulletBorderAlpha: 1,
			bulletColor: "#FFFFFF",
			bulletSize: 5,
			hideBulletsCount: 50,
			lineThickness: 2,
			title: "red line",
			useLineColorForBulletBorder: !0,
			valueField: "year2016",
			balloonText: "<span style='font-size:18px;'>[[value]]</span>"
		}],
		plotAreaFillAlphas: .1,
		categoryField: "country",
		categoryAxis: {
			gridPosition: "start"
		},
		export: {
			enabled: !0
		}
	});
	
});
</script>
