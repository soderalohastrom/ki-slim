<?php
include_once("class.datatables.php");
include_once("class.record.php");
include_once("class.reports.php");
include_once("class.encryption.php");
$RECORD = new Record($DB);
$REPORTS = new Reports($DB, $RECORD);
$ENC = new encryption();
$DATATABLE = new Datatable($DB, $RECORD, $REPORTS, $ENC);

$tableSQL = "
SELECT 
	Persons.Person_id,
	Persons.Person_id as PID, 
	FirstName, 
	LastName, 
	Email, 
	DateCreated, FROM_UNIXTIME(DateCreated, '%Y-%m-%d') as DateCreated, 
	office_Name,
	PersonsImages_path,
	Gender,
	City,
	State,
	Postal,
	Country,
	HearAboutUs,
	datediff(NOW(), FROM_UNIXTIME(DateCreated)) as LeadAge,
	DateOfBirth as DateOfBirth,
	DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), DateOfBirth)), '%Y')+0 AS RecordAge,
	Persons.LeadStages_id,
	LeadStages_name,
	LeadStage_hex,
	Assigned_userID,
	".$DATATABLE->customProfileFieldSelect(664).",
	".$DATATABLE->customProfileFieldSelect(1522).",
	".$DATATABLE->customProfileFieldSelect(660).",
	".$DATATABLE->customProfileFieldSelect(631).",
	".$DATATABLE->customProfileFieldSelect(621).",
	".$DATATABLE->customProfileFieldSelect(622).",
	(SELECT CONCAT(SUBSTRING(firstName, 1, 1),' ',lastName) FROM Users WHERE Users.user_id=Persons.Assigned_userID) as Marketer,
	(SELECT CONCAT(SUBSTRING(firstName, 1, 1),' ',lastName) FROM Users WHERE Users.user_id=Persons.Matchmaker_id) as Matchmaker,
	(SELECT Phone_number FROM Phones WHERE Phones.Person_id=Persons.Person_id AND isPrimary='1' LIMIT 1) as PhoneNumber
FROM 
	Persons 
	INNER JOIN Offices ON Offices.Offices_id=Persons.Offices_id
	INNER JOIN LeadStages ON LeadStages.LeadStages_id=Persons.LeadStages_id
	LEFT JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
	LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
WHERE 
	PersonsTypes_id='3' 
AND 
	PersonsStatus_id='1' 
AND
	Persons.LeadStages_id != '8'
";
$tableFields = $DATATABLE->getLeadAssignFields();
//echo nl2br($tableSQL);
$methodSQL = trim(preg_replace('/\s+/', ' ', $tableSQL));	
?>
<div class="m-content">
	<?php echo $DATATABLE->render_Assign_datatable("allleadsTable", '<i class="flaticon-paper-plane"></i> Leads Assignment - <small>where you can segment and assign your leads.</small>', "/ajax/getAssignedTableData.php", $methodSQL, $tableFields, 'Person_id', 'DateCreated', 'desc', 10, "$('#loadingTableBlock').hide();", true)?>
</div>