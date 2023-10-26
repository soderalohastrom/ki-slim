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
	IF(DateUpdated = '0', '', FROM_UNIXTIME(DateUpdated, '%Y-%m-%d %h:%i%p')) as DateUpdatedDisplay
FROM
	Persons 
WHERE 
	HCS='1'
";
//echo $SQL;
$SND = $DB->get_multi_result($SQL);
if(!isset($SND['empty_result'])):	
?>
<table class="table m-datatable">
	<thead>
    	<tr>
        	<th>ID</th>
            <th>Name</th>
            <th>Type</th>
			<th>Gender</th>
            <th>Age</th>
            <th>Last Update</th>
            <th>Sales Rep</th>
            <th>Matchmaker (Primary)</th>
            <th>Matchmaker (Secondary)</th>
		</tr>
	</thead>                                    
    <tbody>
        <?php foreach($SND as $DTA): ?>
		<tr>
        	<td><?php echo $DTA['Person_id']?></td>
            <td><a href="/profile/<?php echo $DTA['Person_id']?>" class="m-link"><?php echo $DTA['FirstName']?> <?php echo $DTA['LastName']?></a></td>
            <td><?php echo $RECORD->get_personType($DTA['Person_id'])?></td>
            <td><?php echo $DTA['Gender']?></td>
            <td><?php echo $RECORD->get_personAge($DTA['DateOfBirth'])?></td>
            <td><?php echo $RECORD->get_date_diff(date("m/d/Y", $DTA['DateUpdated']), date("m/d/Y"))?></td>
            <td><?php echo $RECORD->get_userName($DTA['Assigned_userID'])?></td>
            <td><?php echo $RECORD->get_userName($DTA['Matchmaker_id'])?></td>
            <td><?php echo $RECORD->get_userName($DTA['Matchmaker2_id'])?></td>
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
       
                    