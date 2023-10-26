<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.sales.php");
$DB = new database();
$DB->connect();

$RECORD = new Record($DB);
$SALES = new Sales($DB);

//print_r($_POST);

if (isset($_POST['filterDates'])) {
	$dateParts = explode(" - ", $_POST['filterDates']);
	$startEpoch = strtotime($dateParts[0]);
	$enderEpoch = strtotime($dateParts[1]) + 86399;
	//echo "Custom:".$startEpoch."|".$enderEpoch;
	$dateParameters = array($startEpoch, $enderEpoch);
	
	$dayDiff = (($enderEpoch - $startEpoch) / 86400) - 1;
	$dateDaysPreload = round($dayDiff);			
} else {
	$dateParameters = array((time() - (7 * 86400)), time());
	$startEpoch = time() - (7 * 86400);
	$enderEpoch = time();
	//echo "Default:".$startEpoch."|".$enderEpoch;
}

$SQL = "
SELECT 
	Persons.Person_id,	
	Persons.FirstName,
	Persons.LastName,
	Persons.PersonsTypes_id,
	Persons.DateOfBirth,
	Persons.DateCreated,
	Persons.Assigned_userID,
	Persons.Matchmaker_id,
	Persons.Occupation,
	Persons.HearAboutUs,
	Persons.Gender,
	PersonTypes.PersonsTypes_text,
	PersonTypes.PersonsTypes_color,
	Addresses.City,
	Addresses.State,
	PersonsProfile.prQuestion_621,	
	PersonsProfile.prQuestion_622,	
	PersonsProfile.prQuestion_631,
	PersonsPrefs.prefQuestion_age_floor,
	PersonsPrefs.prefQuestion_Gender,
	PersonForms.FormSubmitted
FROM
	PersonForms
	INNER JOIN Persons ON Persons.Person_id=PersonForms.Person_id
	INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id
	LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id	
WHERE 
	1
AND
	(PersonForms.FormSubmitted >= '".date("Y-m-d H:i", $startEpoch)."' AND PersonForms.FormSubmitted <= '".date("Y-m-d H:i", $enderEpoch)."')
GROUP BY
	Persons.Person_id
";
//echo $SQL;
//echo $SQL."<br>\n";
$SND = $DB->get_multi_result($SQL);
?>
<table class="table table-striped m-table" border="1" cellpadding="3" cellspacing="0"  id="RecordRList">
<thead>
	<tr>
        <th>Created</th>
        <th>Updated</th>
        <th>Name</th>
        <th width="50">Age</th>
        <th>Gender</th>
        <th>Location</th>
        <th>Income</th>
        <th>Height</th>
        <th>Weight</th>
        <th>Source</th>
        <th>Assigned</th>
        <th>Type</th>
	</tr>
</thead>
<tbody>
<?php
if(!isset($SND['empty_result'])) {	
	//$NEWLEADS_FOUND = count($SND);
	$NEWLEADS_FOUND = 0;
	foreach($SND as $DTA) {
		$epochMarker = strtotime($DTA['FormSubmitted']) - ((60*60)*24);
		//echo $epochMarker."|".$DTA['DateCreated']."<br>\n";
		if($epochMarker >= $DTA['DateCreated']):
		if(($DTA['FirstName'] != '') && ($DTA['LastName'] != '')):
		?>
        <tr>
            <td><?php echo date("m/d/y h:ia", $DTA['DateCreated'])?></td>
            <td><?php echo date("m/d/y h:ia", strtotime($DTA['FormSubmitted']))?></td>
            <td><a href="https://kiss.kelleher-international.com/profile/<?php echo $DTA['Person_id']?>" target="_blank"><?php echo $DTA['FirstName']?> <?php echo $DTA['LastName']?></a></td>
            <td align="center"><?php echo $RECORD->get_personAge($DTA['DateOfBirth'], true)?></td>
            <td align="center"><?php echo $DTA['Gender']?></td>
            <td><?php echo $DTA['City']?> <?php echo $DTA['State']?></td>
            <td><?php echo $DTA['prQuestion_631']?></td>
            <td><?php echo $DTA['prQuestion_621']?></td>
            <td><?php echo $DTA['prQuestion_622']?></td>
            <td><?php echo $DTA['HearAboutUs']?></td>
            <td><?php echo $RECORD->get_userName($DTA['Assigned_userID'])?></td>
            <td><?php echo $RECORD->get_personType($DTA['Person_id'])?></td>
        </tr>
        <?php
		$NEWLEADS_FOUND++;
		endif;
		endif;
	}
} else {
	$NEWLEADS_FOUND = 0;
	?>
    <tr>
    	<td colspan="11">
        	<div style="margin:30px; text-align:center;">NO REVIVED LEADS FOUND</div>
		</td>
	</tr>                        
    <?php
}
?>
</tbody>
</table>
<?php
?>
<script>
$("#RecordRList").mDatatable(
	{
		search: {
		input: $("#generalSearch")
	},
	columns: [{
		field: "Matches",
		type: "number"
	},{
		field: "Age",
		width: 40
	},{
		field: "Gender",
		width: 40
	}],
	footer: false,
	pagination: false,
	data: {
		saveState: {
			cookie: false,
			webstorage: false
		}
	}
});
</script>