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


?><div class="row"><?php
$pt_sql = "SELECT * FROM PersonTypes WHERE PersonsTypes_id NOT IN (1,2,9) ORDER BY PersonsTypes_order ASC";
$pt_snd = $DB->get_multi_result($pt_sql);
foreach($pt_snd as $pt_dta):
	$SQL = "SELECT * FROM Persons WHERE VIP='1' AND PersonsTypes_id='".$pt_dta['PersonsTypes_id']."'";
	$SND = $DB->get_multi_result($SQL);
	if(!isset($SND['empty_result'])):	
	?>
    <div class="col-3">
    <table class="table">
    <tbody>
    	<tr>
        	<td colspan="3"><strong><?php echo $pt_dta['PersonsTypes_text']?> - <?php echo count($SND)?></strong></td>
		</tr>
        <?php		
		foreach($SND as $DTA):
		?>
		<tr>
        	<td><?php echo $DTA['Person_id']?></td>
            <td><a href="/profile/<?php echo $DTA['Person_id']?>" class="m-link"><?php echo $DTA['FirstName']?> <?php echo $DTA['LastName']?></a></td>
            <td></td>
		</td>
        <?php
		endforeach;
		?>
	</tbody>
    </table>
    </div>
    <?php
	endif;
endforeach;
?></div><?php        
                    