<?php
include_once("class.datatables.php");
include_once("class.record.php");
include_once("class.reports.php");
include_once("class.encryption.php");
$RECORD = new Record($DB);
$REPORTS = new Reports($DB, $RECORD);
$ENC = new encryption();
$DATATABLE = new Datatable($DB, $RECORD, -1, $ENC);

$customSelect = $DATATABLE->getCustomTableConfig_select($_SESSION['system_user_id'], 'allleadsTable');
if(!$customSelect) {
	$customSelect = "Gender,
City,
State,
Country,
HearAboutUs,
".$DATATABLE->customProfileFieldSelect(664).",
".$DATATABLE->customProfileFieldSelect(1522).",
".$DATATABLE->customProfileFieldSelect(660).",
".$DATATABLE->customProfileFieldSelect(621).",
".$DATATABLE->customProfileFieldSelect(622).",
".$DATATABLE->customProfileFieldSelect(631).",
".$DATATABLE->customProfileFieldSelect(1713).",
".$DATATABLE->customProfileFieldSelect(1719)." ";
	$tableFields = $DATATABLE->getCustomLeadFields();
	//$tableFields = $DATATABLE->getLeadAssignFields();	
} else {
	$tableFields = $DATATABLE->makeCustomLeadFields($_SESSION['system_user_id'], 'allleadsTable');	
}
//print_r($tableFields);


$tableSQL = "
SELECT 
	Persons.Person_id, 
	Persons.Person_id as PID,
	FirstName, 
	LastName, 
	IF(Persons.HCS = 1, CONCAT(Persons.Person_id,' (HPC)'), CONCAT(FirstName,' ',LastName)) as FullName,
	Persons.OpenRecord,
	IF(Persons.OpenRecord = 1, 'm--show', 'm--hide') as isOpenRecord,
	Email, 
	FROM_UNIXTIME(DateCreated, '%Y-%m-%d') as DateCreatedDisplay, 
	IF(DateUpdated = '0', '', FROM_UNIXTIME(DateUpdated, '%Y-%m-%d %h:%i%p')) as DateUpdatedDisplay,	
	office_Name,
	PersonsImages_path,
	DateOfBirth as DateOfBirth,
	DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), DateOfBirth)), '%Y')+0 AS RecordAge,
	Persons.LeadStages_id,
	LeadStages_name,
	LeadStage_hex,
	(SELECT Phone_number FROM Phones WHERE Phones.Person_id=Persons.Person_id AND isPrimary='1' LIMIT 1) as PhoneNumber,
	(SELECT CONCAT(firstName,' ',lastName) FROM Users WHERE Users.user_id=Persons.Assigned_userID) as Salesperson,
	(SELECT CONCAT(firstName,' ',lastName) FROM Users WHERE Users.user_id=Persons.Matchmaker_id) as Matchmaker,
	Addresses.City,
	Addresses.State,
	PersonTypes.PersonsTypes_text,
	PersonsProfile.prQuestion_1719 as PrimeNoteBody,
	".$customSelect."
FROM 
	Persons 
	INNER JOIN Offices ON Offices.Offices_id=Persons.Offices_id
	INNER JOIN LeadStages ON LeadStages.LeadStages_id=Persons.LeadStages_id
	INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id
	LEFT JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
	LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
WHERE 
	Persons.PersonsTypes_id IN (3)
AND 
	Persons.PersonsStatus_id='1' 
AND
	Persons.LeadStages_id != '8'
";
//$tableFields = $DATATABLE->getLeadAssignFields();
//echo nl2br($tableSQL);
$methodSQL = trim(preg_replace('/\s+/', ' ', $tableSQL));	
?>
<script src="/assets/vendors/custom/sortable-master/Sortable.min.js" type="text/javascript"></script>
<div class="m-content">
	<?php echo $DATATABLE->render_datatable("allleadsTable", '<i class="flaticon-paper-plane"></i> All Leads - <small>every record marked as an active lead.</small>', "/ajax/getTableData.php", $methodSQL, $tableFields, 'Person_id', 'DateCreated', 'desc', 10, false, false, 'false', 'false')?>
</div>
<script>
$(document).ready(function(e) {
    if( $('#currentTableFields').length ) {
		var sortable = Sortable.create(document.getElementById('currentTableFields'), {
			draggable: ".dragable-item"
		});
	}
});
</script>