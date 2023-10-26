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

$SQL = "
SELECT 
	Persons.*,
	IF(DateUpdated = '0', '', FROM_UNIXTIME(DateUpdated, '%Y-%m-%d %h:%i%p')) as DateUpdatedDisplay,
	PersonsProfile.prQuestion_631,
	(SELECT CONCAT(City,', ',State) FROM Addresses WHERE Addresses.Person_id=Persons.Person_id ORDER BY isPrimary DESC LIMIT 1) as location
FROM
	Persons 
	INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
WHERE 
	Persons.isHighIncomeLead='1'
";
//echo $SQL;
$SND = $DB->get_multi_result($SQL);
if(!isset($SND['empty_result'])):	
?>
<table class="table m-datatable">
	<thead>
    	<tr>
            <th>Date Created</th>
            <th>Name</th>
            <th>Location</th>
            <th>Type</th>
			<th>Gender</th>
            <th>Age</th>
            <th>Income</th>
            <th>Last Update</th>
            <th>Sales Rep</th>
		</tr>
	</thead>                                    
    <tbody>
        <?php foreach($SND as $DTA): ?>
		<tr>
            <td><?php echo date("Y-m-d", $DTA['DateCreated'])?></td>
            <td><a href="/profile/<?php echo $DTA['Person_id']?>" class="m-link" target="_blank"><?php echo $DTA['FirstName']?> <?php echo $DTA['LastName']?></a></td>
            <td><?php echo $DTA['location']?></td>
            <td><?php echo $RECORD->get_personType($DTA['Person_id'])?></td>
            <td><?php echo $DTA['Gender']?></td>                        
            <td><?php echo $RECORD->get_personAge($DTA['DateOfBirth'])?></td>
            <td><?php echo $DTA['prQuestion_631']?></td>
            <td><?php echo $DTA['DateUpdatedDisplay']?></td>
            <td><?php echo $RECORD->get_userName($DTA['Assigned_userID'])?></td>
		</tr>
        <?php endforeach; ?>
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
endif; 
?>