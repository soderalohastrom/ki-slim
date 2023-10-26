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

$REPORT_ID = 181;

if (isset($_POST['filterDates'])) {
	$dateParts = explode(" - ", $_POST['filterDates']);
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
	$_POST['filterDates'] = date("m/d/Y", $startEpoch).' - '.date("m/d/Y", $enderEpoch);
	$dateDaysPreload = 30;
}
//print_r($_POST);

$scu_sql = "
SELECT
	DISTINCT(Users_user_id)
FROM
	PersonsSales
	INNER JOIN PersonsSalesCommissions ON PersonsSalesCommissions.PersonsSales_PersonsSales_id=PersonsSales.PersonsSales_id
WHERE
	1
AND
	(PersonsSales.PersonsSales_dateCreated <= '".$enderEpoch."' AND PersonsSales.PersonsSales_dateCreated >= '".$startEpoch."')

";
//echo $scu_sql."<br>\n";
$scu_snd = $DB->get_multi_result($scu_sql);
ob_start();
if(!isset($scu_snd['empty_result'])):
	foreach($scu_snd as $scu_dta):
		
		$s_sql = "
		SELECT
			*
		FROM
			PersonsSales
			INNER JOIN PersonsSalesCommissions ON PersonsSalesCommissions.PersonsSales_PersonsSales_id=PersonsSales.PersonsSales_id
		WHERE
			1
		AND
			(PersonsSales.PersonsSales_dateCreated <= '".$enderEpoch."' AND PersonsSales.PersonsSales_dateCreated >= '".$startEpoch."')
		AND
			PersonsSalesCommissions.Users_user_id='".$scu_dta['Users_user_id']."'
		GROUP BY
			PersonsSales.PersonsSales_id	
		";
		//echo $s_sql;
		$s_snd = $DB->get_multi_result($s_sql);
		if(!isset($scu_snd['empty_result'])):
			ob_start();
			?><div id="details_<?php echo $scu_dta['Users_user_id']?>"  style="margin-bottom:50px; display:none;"><?php
			?><table class="table table-sm"><?php
			?><thead><tr><th>Date</th><th>Client</th><th>Amount</th><th>Package</th><th class="text-right">Commission</th></tr></thead><?php
			?><tbody><?php
			foreach($s_snd as $s_dta):
				?>
                <tr>
                	<td><?php echo date("m/d/y", $s_dta['PersonsSales_dateCreated'])?></td>
                    <td><?php echo $RECORD->get_personName($s_dta['Persons_Person_id'])?></td>
                    <td>$<?php echo number_format($s_dta['PersonsSales_payment'], 0)?></td>
                    <td><?php echo $SALES->get_packageName($s_dta['PersonsSales_packageID'])?></td>
                    <td class="text-right">$<?php echo number_format($s_dta['CommissionAMT'], 2)?></td>
                </tr>
                <?php
				$sale_dollars[] = $s_dta['PersonsSales_payment'];
				$sale_commission[] = $s_dta['CommissionAMT'];
			endforeach;
			?></tbody><?php
			?></table><?php
			?></div><?php
			$innerTable = ob_get_clean();
		endif;
	
	
	
		?>
        <div class="m-widget5__item" style="margin-bottom:.0rem; padding-bottom:.0rem;">
			<div class="m-widget5__pic">
                <a href="javascript:;" onclick="$('#details_<?php echo $scu_dta['Users_user_id']?>').toggle()" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--custom m-btn--pill">
                    <i class="la la-angle-down"></i>
                </a>
            </div>
            <div class="m-widget5__content">
				<h4 class="m-widget5__title"><?php echo $RECORD->get_FulluserName($scu_dta['Users_user_id'])?></h4>
				<span class="m-widget5__desc"><?php echo $RECORD->get_userEmail($scu_dta['Users_user_id'])?></span>				
			</div>
			<div class="m-widget5__stats1">
				<span class="m-widget5__number">
					$<?php echo number_format(array_sum($sale_commission), 0)?>
				</span>
				<br>
				<span class="m-widget5__sales">
					commissions
				</span>
			</div>
			<div class="m-widget5__stats2">
				<span class="m-widget5__number">
					<?php echo count($sale_dollars)?>
				</span>
				<br>
				<span class="m-widget5__votes">
					sales
				</span>
			</div>
            <div class="m-widget5__stats1">
				<span class="m-widget5__number">
					<?php echo number_format(array_sum($sale_dollars), 0)?>
				</span>
				<br>
				<span class="m-widget5__sales">
					dollars
				</span>
			</div>
		</div>
        <?php
		echo $innerTable;
		unset($sale_dollars);
		unset($sale_commission);
	endforeach;
endif;
$reportBody = ob_get_clean();







?>
<form action="/viewreport/<?php echo $REPORT_ID?>" method="post">
<div class="row" style="margin-bottom:10px;">
	<div class="col-8">
    	<div class="alert alert-danger">
    	<small>This report includes all sales that have occured within the time scope of the report regardless of payment processed and recieved. To view a report that accounts for payments recieved <a href="/page.php?path=viewreport/119">click here</a></small>
        </div>
    </div>
    <div class="col-4">
    	<div class="input-group m-input-group">					
            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-calendar"></i></span>
            <input type="text" class="form-control m-input input-sm" id="filterDates" name="filterDates" placeholder="dates" autocomplete="off" value="<?php echo $_POST['filterDates']?>">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-default">Apply Filters</button>
            </span>
        </div>
    </div>
</div>
</form>
<div class="m-widget5">
<?php echo $reportBody?>        
</div>
<script>
//var start = moment().subtract(<?php echo $dateDaysPreload?>, 'days');
//var end = moment();	
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
</script>