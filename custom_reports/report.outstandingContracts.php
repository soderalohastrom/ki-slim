<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.sales.php");
$DB = new database();
$DB->connect();

$RECORD = new Record($DB);
$SALES = new Sales($DB);

$SQL = "
SELECT 
    Persons.Person_id,
    Persons.FirstName,
    Persons.LastName,
    PersonTypes.PersonsTypes_text,
    TotalPaid,
    PersonsContract.Contract_id,
    PersonsContract.Contract_rep,
    PersonsContract.Contract_dateEntered,
    PersonsContract.Contract_Hash,
    PersonsContract.Contract_RetainerFee,
    (SELECT 
            ContractHistory_date
        FROM
            PersonsContractsHistory
        WHERE
            PersonsContractsHistory.Contract_id = PersonsContract.Contract_id
        ORDER BY ContractHistory_date DESC
        LIMIT 1) AS LastView
FROM
    PersonsContract
        INNER JOIN
    Persons ON Persons.Person_id = PersonsContract.Person_id
        INNER JOIN
    PersonTypes ON Persons.PersonsTypes_ID = PersonTypes.PersonsTypes_Id
WHERE
    PersonsContract.Contract_status = '1'
ORDER BY PersonsContract.Contract_dateEntered DESC
";
$SND = $DB->get_multi_result($SQL);
if(!isset($SND['empty_result'])) {
	?><table class="table"><?php
	?>
    <thead>
        <tr>
		<th>Lead/Client</th>
		<th>Record Type</th>
		<th>Paid to Date</th>
            <th>Retainer</th>
            <th>Date Created</th>
            <th>Rep</th>
            <th>Last View</th>
        </tr>
	</thead>
    <tbody>
    <?php
	foreach($SND as $DTA) {
		?>
        <tr>
        	<td>
            	<a href="/profile/<?php echo $DTA['Person_id']?>" class="m-link" target="_blank"><?php echo $DTA['FirstName']?> <?php echo $DTA['LastName']?>&nbsp;
                <a href="/contractgen/<?php echo $DTA['Person_id']?>/<?php echo $DTA['Contract_id']?>" title="View Internal Contract" class="m--font-success" target="_blank"><i class="flaticon-list-2"></i></a>&nbsp;
                <a href="https://<?php echo $_SERVER['SERVER_NAME']?>/view-contract.php?id=<?php echo $DTA['Contract_Hash']?>" title="View External Contract" class="m--font-warning" target="_blank"><i class="flaticon-interface-4"></i></a>&nbsp;
			</td>
            <td><?php echo $DTA['PersonsTypes_text']?></td>
            <td><?php echo number_format($DTA['TotalPaid'], 2)?></td>
            <td><?php echo number_format($DTA['Contract_RetainerFee'], 2)?></td>
            <td><?php echo date("m/d/y h:ia", $DTA['Contract_dateEntered'])?></td>
            <td><?php echo $RECORD->get_userName($DTA['Contract_rep'])?></td>
            <td><?php echo (($DTA['LastView'] == '')? '':date("m/d/y h:ia", $DTA['LastView']))?></td>
		</tr>
        <?php	
	}
	?>
    </tbody>
    </table>
    <?php
} else {
		
}
