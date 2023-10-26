<?php
include_once("class.datatables.php");
include_once("class.record.php");
include_once("class.reports.php");
include_once("class.encryption.php");
$RECORD = new Record($DB);
$REPORTS = new Reports($DB, $RECORD);
$ENC = new encryption();
$DATATABLE = new Datatable($DB, $RECORD, -1, $ENC);


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
	DateCreated, FROM_UNIXTIME(DateCreated, '%Y-%m-%d') as DateCreated, 
	office_Name,
	PersonsImages_path,
	Gender,
	City,
	State,
	Country,
	DateOfBirth,
	PersonsTypes_color,
	PersonsTypes_text,
	(SELECT CONCAT(SUBSTRING(firstName, 1, 1),' ',lastName) FROM Users WHERE Users.user_id=Persons.Matchmaker_id) as Matchmaker,
	".$DATATABLE->customProfileFieldSelect(657).",
	".$DATATABLE->customProfileFieldSelect(664).",
	".$DATATABLE->customProfileFieldSelect(631).",	
	".$DATATABLE->customProfileFieldSelect(676).",	
	".$DATATABLE->customProfileFieldSelect(677).",
	".$DATATABLE->customProfileFieldSelect(1062).",
	".$DATATABLE->customProfileFieldSelect(1522).",
	IFNULL(PersonsColors.Color_title,'&nbsp;') as Color_title,
	IFNULL(PersonsColors.Color_hex,'#FFFFFF') as Color_hex
FROM 
	Persons 
	INNER JOIN Offices ON Offices.Offices_id=Persons.Offices_id
	INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id
	LEFT JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
	LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	LEFT JOIN PersonsColors ON PersonsColors.Color_id=Persons.Color_id
WHERE 
	Persons.PersonsTypes_id IN (12)
AND 
	PersonsStatus_id='1'
";
$tableFields = $DATATABLE->getCustomClientFields();
//echo nl2br($tableSQL);
//$methodSQL = str_replace("\n", "", $tableSQL);	(4,7,8,10,12)
$methodSQL = trim(preg_replace('/\s+/', ' ', $tableSQL));
?>
<div class="m-content">
	<?php echo $DATATABLE->render_datatable("myclientsTable", '<i class="flaticon-user-ok"></i> Participating - <small>all active particpating records</small>', "/ajax/getTableData.php", $methodSQL, $tableFields, 'Person_id', 'LastName', 'asc', 10, false, false, 'false', 'false')?>
</div>
