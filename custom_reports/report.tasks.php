<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.sales.php");
include_once("class.encryption.php");
$DB = new database();
$DB->connect();

$RECORD = new Record($DB);
$SALES = new Sales($DB);
$ENC = new encryption();


$sql = "
SELECT 
	DISTINCT(ActionAssignedTo) 
FROM 
	PersonActions
	INNER JOIN Users ON Users.user_id=PersonActions.ActionAssignedTo 
WHERE 
	ActionCompleted='0'
AND
	userClass_id='9'
ORDER BY
	Users.lastName ASC
";
$snd = $DB->get_multi_result($sql);
ob_start();
foreach($snd as $dta):
	//echo $dta['ActionAssignedTo']."<br>\n";
	
	$otc_sql = "SELECT count(*) as count FROM PersonActions WHERE ActionAssignedTo='".$dta['ActionAssignedTo']."' AND ActionCompleted='0'";
	$otc = $DB->get_single_result($otc_sql);
	
	$pdtc_sql = "SELECT count(*) as count FROM PersonActions WHERE ActionAssignedTo='".$dta['ActionAssignedTo']."' AND ActionCompleted='0' AND ActionDateTime < UNIX_TIMESTAMP()";
	$pdtc = $DB->get_single_result($pdtc_sql);
	
	$e7_start = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	$e7_end = mktime(23, 59, 59, date("m"), (date("d") - 7), date("Y"));
	$ct7_sql = "SELECT count(*) as count FROM PersonActions WHERE ActionAssignedTo='".$dta['ActionAssignedTo']."' AND ActionCompleted='1' AND (ActionCompletedDate <= '".$e7_start."' AND ActionCompletedDate >= '".$e7_end."')";
	//echo $ct7_sql."<br>\n";
	$ct7 = $DB->get_single_result($ct7_sql);
	
	$e30_start = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	$e30_end = mktime(23, 59, 59, date("m"), (date("d") - 7), date("Y"));
	$ct30_sql = "SELECT count(*) as count FROM PersonActions WHERE ActionAssignedTo='".$dta['ActionAssignedTo']."' AND ActionCompleted='1' AND (ActionCompletedDate <= '".$e30_start."' AND ActionCompletedDate >= '".$e30_end."')";
	$ct30 = $DB->get_single_result($ct30_sql);
	
	$e90_start = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	$e90_end = mktime(23, 59, 59, date("m"), (date("d") - 7), date("Y"));
	$ct90_sql = "SELECT count(*) as count FROM PersonActions WHERE ActionAssignedTo='".$dta['ActionAssignedTo']."' AND ActionCompleted='1' AND (ActionCompletedDate <= '".$e90_start."' AND ActionCompletedDate >= '".$e90_end."')";
	$ct90 = $DB->get_single_result($ct90_sql);
	?>
    <tr>
    	<td><?php echo $RECORD->get_FulluserName($dta['ActionAssignedTo'])?></td>
        <td class="text-center"><?php echo $otc['count']?></td>
        <td class="text-center"><?php echo $pdtc['count']?></td>
        <td class="text-center"><?php echo $ct7['count']?></td>
        <td class="text-center"><?php echo $ct30['count']?></td>
        <td class="text-center"><?php echo $ct90['count']?></td>    
    </tr>
    <?php
endforeach;
$tbody = ob_get_clean();
?>
<table class="table">
    <thead>
        <tr>
            <th>User</th>
            <th class="text-center">Open Tasks</th>
            <th class="text-center">Past Due Tasks</th>
            <th class="text-center">Completed<br />past 7 Days</th>
            <th class="text-center">Completed<br />past 30 Days</th>
            <th class="text-center">Completed<br />past 90 Days</th>
        </tr>
    </thead>
    <tbody>
        <?php echo $tbody?>
    </tbody>
</table>