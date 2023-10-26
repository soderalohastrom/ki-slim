<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.sales.php");
include_once("class.matching.php");
$DB = new database();
$DB->connect();

$RECORD = new Record($DB);
$SALES = new Sales($DB);
$MATCHING = new Matching($DB, $RECORD);


$SQL = "
SELECT 
	Person_id, 
	FirstName, 
	LastName, 
	LastNoteAction
FROM 
	Persons 
WHERE 
	PersonsTypes_id='12' 
AND 
	Assigned_userID='".$_SESSION['system_user_id']."' 
ORDER BY 
	DateCreated DESC";
//echo $SQL;
$SND = $DB->get_multi_result($SQL);
//print_r($SND);

ob_start();
if(isset($SND['empty_result'])) {
	?>
    <tr>
    	<td colspan="4" class="text-center"><em>NO PM Records Found</em></td>
	</tr>
    <?php        
} else {
	foreach($SND as $DTA):
	$LAST = json_decode($DTA['LastNoteAction'], true);
	//print_r($LAST);
	?>
	<tr>
    	<td><a href="/profile/<?php echo $DTA['Person_id']?>" class="m-link" target="_blank"><?php echo $DTA['Person_id']?></a></td>
        <td><a href="/profile/<?php echo $DTA['Person_id']?>" class="m-link" target="_blank"><?php echo $DTA['FirstName']?></a></td> 
		<td><a href="/profile/<?php echo $DTA['Person_id']?>" class="m-link" target="_blank"><?php echo $DTA['LastName']?></a></td>
        <td><?php echo $LAST['hType']?></td>
        <td><?php echo date("Y-m-d", $LAST['hDate'])?></td>
    </tr>
    <?php
	endforeach;	
}
$tbody = ob_get_clean();
?>
<div class="row" style="margin-bottom:10px;">
	<div class="col-8">&nbsp;</div>
    <div class="col-4">
        <div class="input-group">
            <span class="input-group-addon" data-toggle="m-tooltip" title="First Name and Last Name"><i class="flaticon-search-1"></i>&nbsp;Quick Search</span>
            <input type="text" class="form-control m-input" id="generalSearch" />                                                                        
        </div>
	</div>
</div>
<table class="table" id="myPMs">
<thead>
	<tr>
    	<th>ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Last Action</th>
        <th>Last Action Date</th>
	</tr>
</thead>
<tbody>
<?php echo $tbody?>
</tbody> 
</table>
<script>
$("#myPMs").mDatatable(
	{
		search: {
		input: $("#generalSearch")
	},
	columns: [{
		field: "Matches",
		type: "number"
	},{
		field: "ID",
		width: 60
	}],
	footer: true,
	pagination: true,
	data: {
		saveState: {
			cookie: false,
			webstorage: false
		}
	}
}); 
</script>       
