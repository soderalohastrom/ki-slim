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


$sql = "
SELECT
	Persons.Person_id,
	Persons.FirstName,
	Persons.LastName,
	Persons.Assigned_userID,
	PersonsSales.*
FROM
	PersonsContract
	LEFT JOIN PersonsPaymentInfo ON PersonsPaymentInfo.Contract_id=PersonsContract.Contract_id
	INNER JOIN PersonsSales ON PersonsSales.PersonsSales_ContractID=PersonsContract.Contract_id
	INNER JOIN Persons ON Persons.Person_id=PersonsSales.Persons_Person_id
WHERE
	PersonsPaymentInfo.PaymentInfo_ID IS NULL
ORDER BY
	PersonsSales_dateCreated DESC
";
$snd = $DB->get_multi_result($sql);

if(!isset($snd['empty_result'])):
	ob_start();
	foreach($snd as $dta):
	?>
    <tr>
    	<td><a href="/profile/<?php echo $dta['Person_id']?>" target="_blank"><?php echo $RECORD->get_personName($dta['Person_id'])?></a></td>
        <td><?php echo $RECORD->get_userName($dta['Assigned_userID'])?></td>
        <td><?php echo date("m/d/Y", $dta['PersonsSales_dateCreated'])?></td>
        <td><?php echo $dta['PersonsSales_payment']?></td>
	</tr>        
	<?php
	endforeach;
	$tbody = ob_get_clean();
	?>
    <div class="alert alert-info" role="alert">
		This is a compilation of all sales that currently lack a payment record. As a result, these sales WILL NOT show up in the Multi-Office Sales Report. Please correct or remove invalid sales.
	</div>
    <table class="table table-sm">
        <thead class="thead-inverse">
	        <tr>
    		    <th>Record</th>
		        <th>Salesperson</th>
                <th>Sale Date</th>
                <th>Sale Amount</th>
			</tr>                
        </thead>
        <tbody>
       		<?php echo $tbody?>
        </tbody>
	</table>    
    <?php
else:
?>
<div class="alert alert-info" role="alert">
	<strong>CONGRATULATIONS!!!</strong>
	There are currently no sales without payments attached.
</div>
<?php

endif;


