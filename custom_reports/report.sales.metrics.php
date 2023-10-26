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

function bw_check($value_1, $value_2, $textonly=false) {	
	if($textonly): 
		return ($value_1 - $value_2);	
	else:
		ob_start();
		if ($value_1 > $value_2):
			?><span class="m-badge m-badge--success m-badge--wide"><?php echo ($value_1 - $value_2)?></span><?php
		elseif($value_1 == $value_2):
			?><span class="m-badge m-badge--default m-badge--wide"><?php echo ($value_1 - $value_2)?></span><?php
		else:
			?><span class="m-badge m-badge--danger m-badge--wide"><?php echo ($value_1 - $value_2)?></span><?php
		endif;
		return ob_get_clean();	
	endif;
}

if(!isset($_POST['state'])) {
	$_POST['state'] = '';
}

if(isset($_POST['month_set'])) {
	$startepoch 	= mktime(0,0,0, $_POST['month_set'], 1, $_POST['year_set']);
	$_POST['StartDate'] = date("m/d/Y", mktime(0,0,0,date("m", $startepoch), 1, date("Y", $startepoch)));
	$_POST['EndDate'] = date("m/d/Y", mktime(23,59,59,date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch)));	
} else {
	if(!isset($_POST['StartDate'])) {
		$startepoch 	= time();
		$_POST['StartDate'] = date("m/d/Y", mktime(0,0,0,date("m"), 1, date("Y")));
		$_POST['EndDate'] = date("m/d/Y", mktime(23,59,59,date("m"), date("t"), date("Y")));
	} else {
		$startepoch 	= strtotime($_POST['StartDate']);
	}	
}

$month_floor	= mktime(0,0,0, date("m", $startepoch), 1, date("Y", $startepoch));
$month_peak 	= mktime(23,59,59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch));
$lmonth_floor	= mktime(0,0,0, date("m", $startepoch)-1, 1, date("Y", $startepoch));
$lmonth_peak 	= mktime(23,59,59, date("m", $startepoch)-1, date("t", $lmonth_floor), date("Y", $startepoch));
$ytd_floor		= mktime(0,0,0,1,1,date("Y", $startepoch));
$ytd_peak		= mktime(23,59,59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch));
$lytd_floor		= mktime(0,0,0,1,1,date("Y", $startepoch)-1);
$lytd_peak		= mktime(23,59,59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch)-1);

$current_month_floor	= mktime(0,0,0, date("m"), 1, date("Y"));

$s_query = "SELECT * FROM Offices ORDER BY office_Name ASC";
ob_start();
?><option value="" <?php echo (($_POST['state'] == '')? 'selected':'')?>>All Locations</option><?php
//$s_send = mysql_query($s_query, $db_link);
//while ($s_data = mysql_fetch_assoc($s_send)) {
$s_send = $DB->get_multi_result($s_query);
foreach($s_send as $s_data) {
	?><option value="<?php echo $s_data['Offices_id']?>" <?php echo (($_POST['state'] == $s_data['Offices_id'])? 'selected':'')?>><?php echo $s_data['office_Name']?></option><?php
}
$stateSelect = ob_get_clean();
$userDropdown = $RECORD->options_userSelect(array($_POST['telemarketer']), true);


//echo "Month Check:".$current_month_floor."|".$month_floor;
//if($current_month_floor == $month_floor):

// OVERVIEW NUMBERS //
$tc_sql = "
SELECT 
	count(*) as count 
FROM 
	Persons	
WHERE 
	PersonsTypes_id IN (4,7) 
";
if($_POST['telemarketer'] != ''):
$tc_sql .= "AND Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$tc_send = mysql_query($tc_sql, $db_link);
//$tc_data = mysql_fetch_assoc($tc_send);
$tc_send = $DB->get_single_result($tc_sql);
$TOTAL_CLIENTS = $tc_send['count'];

$trr_sql = "
SELECT 
	count(*) as count 
FROM 
	Persons
WHERE 
	PersonsTypes_id IN (10,12,8) 
";
if($_POST['telemarketer'] != ''):
$trr_sql .= "AND Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$trr_send = mysql_query($trr_sql, $db_link);
//$trr_data = mysql_fetch_assoc($trr_send);
$trr_send = $DB->get_single_result($trr_sql);
$TOTAL_RESOURCES = $trr_send['count'];

$tl_sql = "
SELECT 
	count(*) as count 
FROM 
	Persons
WHERE 
	PersonsTypes_id IN (3) 
";
if($_POST['telemarketer'] != ''):
$tl_sql .= "AND Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$tl_send = mysql_query($tl_sql, $db_link);
//$tl_data = mysql_fetch_assoc($tl_send);
$tl_send = $DB->get_single_result($tl_sql);
$TOTAL_LEADS = $tl_send['count'];
/*
else:
	$mm_sql = "SELECT * FROM CompanyMetricsHistory WHERE MetricMonth='".date("Y-m-d", $month_floor)."'";
	//$mm_send = mysql_query($mm_sql, $db_link);
	//$mm_data = mysql_fetch_assoc($mm_send);
	$mm_data = $DB->get_multi_result($mm_sql);
	$TOTAL_LEADS = $mm_data['LeadRecords'];
	$TOTAL_RESOURCES = $mm_data['ResourceRecords']; 
	$TOTAL_CLIENTS = $mm_data['ClientRecords'];
endif;
*/
$TOTAL_CONTACTS = $TOTAL_LEADS + $TOTAL_RESOURCES + $TOTAL_CLIENTS;



// LEAD SITE SOURCES //
$site_cupid_month_sql = "
SELECT
	count(*) as count
FROM
	Persons
WHERE
	1
AND
	(Persons.DateCreated  >= '".$month_floor."' AND Persons.DateCreated  <= '".$month_peak."')
";
//$site_cupid_month_send = mysql_query($site_cupid_month_sql, $db_link);
//$site_cupid_month_data = mysql_fetch_assoc($site_cupid_month_send);
$site_cupid_month_data = $DB->get_single_result($site_cupid_month_sql);
$TOTAL_CUPID_LEADS = $site_cupid_month_data['count'];



// LEADS //
$l_month_sql = "
SELECT
	count(*) as count
FROM
	Persons
WHERE
	1
AND
	(Persons.DateCreated >= '".$month_floor."' AND Persons.DateCreated <= '".$month_peak."')
";
if($_POST['state'] != ''):
$l_month_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$l_month_sql .= "
AND
	Assigned_userID = '".$_POST['telemarketer']."'
";
endif;

//$l_month_send = mysql_query($l_month_sql, $db_link);
//$l_month_data = mysql_fetch_assoc($l_month_send);
//print_r($tc_month_data);
$l_month_data = $DB->get_single_result($l_month_sql);
$l_month = $l_month_data['count'];

$l_lmonth_sql = "
SELECT
	count(*) as count
FROM
	Persons
WHERE
	1
AND
	(Persons.DateCreated >= '".$lmonth_floor."' AND Persons.DateCreated <= '".$lmonth_peak."')
";
if($_POST['state'] != ''):
$l_lmonth_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$l_lmonth_sql .= "
AND
	Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$l_lmonth_send = mysql_query($l_lmonth_sql, $db_link);
//$l_lmonth_data = mysql_fetch_assoc($l_lmonth_send);
//print_r($tc_month_data);
$l_lmonth_data = $DB->get_single_result($l_lmonth_sql);
$l_lmonth = $l_lmonth_data['count'];

$l_ytd_sql = "
SELECT
	count(*) as count
FROM
	Persons
WHERE
	1
AND
	(Persons.DateCreated >= '".$ytd_floor."' AND Persons.DateCreated <= '".$ytd_peak."')
";
if($_POST['state'] != ''):
$l_ytd_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$l_ytd_sql .= "
AND
	Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$l_ytd_send = mysql_query($l_ytd_sql, $db_link);
//$l_ytd_data = mysql_fetch_assoc($l_ytd_send);
//print_r($tc_month_data);
$l_ytd_data = $DB->get_single_result($l_ytd_sql);
$l_ytd = $l_ytd_data['count'];

$l_lytd_sql = "
SELECT
	count(*) as count
FROM
	Persons
WHERE
	1
AND
	(Persons.DateCreated >= '".$lytd_floor."' AND Persons.DateCreated <= '".$lytd_peak."')
";
if($_POST['state'] != ''):
$l_lytd_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$l_lytd_sql .= "
AND
	Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$l_lytd_send = mysql_query($l_lytd_sql, $db_link);
//$l_lytd_data = mysql_fetch_assoc($l_lytd_send);
//print_r($tc_month_data);
$l_lytd_data = $DB->get_single_result($l_lytd_sql);
$l_lytd = $l_lytd_data['count'];


// CALLS //
$cl_month_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN PersonsNotes ON PersonsNotes.PersonsNotes_personID=Persons.Person_id
WHERE
	1
AND
	(PersonsNotes.PersonsNotes_dateCreated >= '".$month_floor."' AND PersonsNotes.PersonsNotes_dateCreated <= '".$month_peak."')
AND
	PersonsNotes.PersonsNotes_type='Call Note'
";
if($_POST['state'] != ''):
$cl_month_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$cl_month_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$cl_month_send = mysql_query($cl_month_sql, $db_link);
//$cl_month_data = mysql_fetch_assoc($cl_month_send);
//print_r($tc_month_data);
$cl_month_data = $DB->get_single_result($cl_month_sql);
$cl_month = $cl_month_data['count'];

$cl_lmonth_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN PersonsNotes ON PersonsNotes.PersonsNotes_personID=Persons.Person_id
WHERE
	1
AND
	(PersonsNotes.PersonsNotes_dateCreated >= '".$lmonth_floor."' AND PersonsNotes.PersonsNotes_dateCreated <= '".$lmonth_peak."')
AND
	PersonsNotes.PersonsNotes_type='Call Note'
";
if($_POST['state'] != ''):
$cl_lmonth_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$cl_lmonth_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$cl_lmonth_send = mysql_query($cl_lmonth_sql, $db_link);
//$cl_lmonth_data = mysql_fetch_assoc($cl_lmonth_send);
//print_r($tc_month_data);
$cl_lmonth_data = $DB->get_single_result($cl_lmonth_sql);
$cl_lmonth = $cl_lmonth_data['count'];

$cl_ytd_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN PersonsNotes ON PersonsNotes.PersonsNotes_personID=Persons.Person_id
WHERE
	1
AND
	(PersonsNotes.PersonsNotes_dateCreated >= '".$ytd_floor."' AND PersonsNotes.PersonsNotes_dateCreated <= '".$ytd_peak."')
AND
	PersonsNotes.PersonsNotes_type='Call Note'
";
if($_POST['state'] != ''):
$cl_ytd_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$cl_ytd_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$cl_ytd_send = mysql_query($cl_ytd_sql, $db_link);
//$cl_ytd_data = mysql_fetch_assoc($cl_ytd_send);
//print_r($tc_month_data);
$cl_ytd_data = $DB->get_single_result($cl_ytd_sql);
$cl_ytd = $cl_ytd_data['count'];

$cl_lytd_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN PersonsNotes ON PersonsNotes.PersonsNotes_personID=Persons.Person_id
WHERE
	1
AND
	(PersonsNotes.PersonsNotes_dateCreated >= '".$lytd_floor."' AND PersonsNotes.PersonsNotes_dateCreated <= '".$lytd_peak."')
AND
	PersonsNotes.PersonsNotes_type='Call Note'
";
if($_POST['state'] != ''):
$cl_lytd_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$cl_lytd_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$cl_lytd_send = mysql_query($cl_lytd_sql, $db_link);
//$cl_lytd_data = mysql_fetch_assoc($cl_lytd_send);
//print_r($tc_month_data);
$cl_lytd_data = $DB->get_single_result($cl_lytd_sql);
$cl_lytd = $cl_lytd_data['count'];


// EMAILS //
$mail_month_sql = "
SELECT
	count(*) as count
FROM
	Persons		
	INNER JOIN PersonsCommHistory ON PersonsCommHistory.Person_id=Persons.Person_id
WHERE
	1
AND
	(PersonsCommHistory.MessageSentDate >= '".$month_floor."' AND PersonsCommHistory.MessageSentDate <= '".$month_peak."')
AND
	PersonsCommHistory.Deployment_id='0'
";
if($_POST['state'] != ''):
$mail_month_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$mail_month_sql .= "
AND
	PersonsCommHistory.MessageSender = '".$_POST['telemarketer']."'
";
endif;
//echo $mail_month_sql;
//$mail_month_send = mysql_query($mail_month_sql, $db_link);
//$mail_month_data = mysql_fetch_assoc($mail_month_send);
//print_r($tc_month_data);
$mail_month_data = $DB->get_single_result($mail_month_sql);
$mail_month = $mail_month_data['count'];

$mail_lmonth_sql = "
SELECT
	count(*) as count
FROM
	Persons		
	INNER JOIN PersonsCommHistory ON PersonsCommHistory.Person_id=Persons.Person_id
WHERE
	1
AND
	(PersonsCommHistory.MessageSentDate >= '".$lmonth_floor."' AND PersonsCommHistory.MessageSentDate <= '".$lmonth_peak."')
AND
	PersonsCommHistory.Deployment_id='0'
";
if($_POST['state'] != ''):
$mail_lmonth_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$mail_lmonth_sql .= "
AND
	PersonsCommHistory.MessageSender = '".$_POST['telemarketer']."'
";
endif;
//$mail_lmonth_send = mysql_query($mail_lmonth_sql, $db_link);
//$mail_lmonth_data = mysql_fetch_assoc($mail_lmonth_send);
//print_r($tc_month_data);
$mail_lmonth_data = $DB->get_single_result($mail_lmonth_sql);
$mail_lmonth = $mail_lmonth_data['count'];

$mail_ytd_sql = "
SELECT
	count(*) as count
FROM
	Persons		
	INNER JOIN PersonsCommHistory ON PersonsCommHistory.Person_id=Persons.Person_id
WHERE
	1
AND
	(PersonsCommHistory.MessageSentDate >= '".$ytd_floor."' AND PersonsCommHistory.MessageSentDate <= '".$ytd_peak."')
AND
	PersonsCommHistory.Deployment_id='0'
";
if($_POST['state'] != ''):
$mail_ytd_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$mail_ytd_sql .= "
AND
	PersonsCommHistory.MessageSender = '".$_POST['telemarketer']."'
";
endif;
//$mail_ytd_send = mysql_query($mail_ytd_sql, $db_link);
//$mail_ytd_data = mysql_fetch_assoc($mail_ytd_send);
//print_r($tc_month_data);
$mail_ytd_data = $DB->get_single_result($mail_ytd_sql);
$mail_ytd = $mail_ytd_data['count'];

$mail_lytd_sql = "
SELECT
	count(*) as count
FROM
	Persons		
	INNER JOIN PersonsCommHistory ON PersonsCommHistory.Person_id=Persons.Person_id
WHERE
	1
AND
	(PersonsCommHistory.MessageSentDate  >= '".$lytd_floor."' AND PersonsCommHistory.MessageSentDate  <= '".$lytd_peak."')
AND
	PersonsCommHistory.Deployment_id='0'
";
if($_POST['state'] != ''):
$mail_lytd_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$mail_lytd_sql .= "
AND
	PersonsCommHistory.MessageSender = '".$_POST['telemarketer']."'
";
endif;
//$mail_lytd_send = mysql_query($mail_lytd_sql, $db_link);
//$mail_lytd_data = mysql_fetch_assoc($mail_lytd_send);
//print_r($tc_month_data);
$mail_lytd_data = $DB->get_single_result($mail_lytd_sql);
$mail_lytd = $mail_lytd_data['count'];


// NON-CALL NOTES //
$ncl_month_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN PersonsNotes ON PersonsNotes.PersonsNotes_personID=Persons.Person_id
WHERE
	1
AND
	(PersonsNotes.PersonsNotes_dateCreated >= '".$month_floor."' AND PersonsNotes.PersonsNotes_dateCreated <= '".$month_peak."')
AND
	PersonsNotes.PersonsNotes_type != 'Call Note'
";
if($_POST['state'] != ''):
$ncl_month_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$ncl_month_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$cl_month_send = mysql_query($cl_month_sql, $db_link);
//$cl_month_data = mysql_fetch_assoc($cl_month_send);
//print_r($tc_month_data);
$ncl_month_data = $DB->get_single_result($ncl_month_sql);
$ncl_month = $ncl_month_data['count'];

$ncl_lmonth_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN PersonsNotes ON PersonsNotes.PersonsNotes_personID=Persons.Person_id
WHERE
	1
AND
	(PersonsNotes.PersonsNotes_dateCreated >= '".$lmonth_floor."' AND PersonsNotes.PersonsNotes_dateCreated <= '".$lmonth_peak."')
AND
	PersonsNotes.PersonsNotes_type != 'Call Note'
";
if($_POST['state'] != ''):
$ncl_lmonth_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$ncl_lmonth_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$cl_lmonth_send = mysql_query($cl_lmonth_sql, $db_link);
//$cl_lmonth_data = mysql_fetch_assoc($cl_lmonth_send);
//print_r($tc_month_data);
$ncl_lmonth_data = $DB->get_single_result($ncl_lmonth_sql);
$ncl_lmonth = $ncl_lmonth_data['count'];

$ncl_ytd_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN PersonsNotes ON PersonsNotes.PersonsNotes_personID=Persons.Person_id
WHERE
	1
AND
	(PersonsNotes.PersonsNotes_dateCreated >= '".$ytd_floor."' AND PersonsNotes.PersonsNotes_dateCreated <= '".$ytd_peak."')
AND
	PersonsNotes.PersonsNotes_type='Call Note'
";
if($_POST['state'] != ''):
$ncl_ytd_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$ncl_ytd_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$cl_ytd_send = mysql_query($cl_ytd_sql, $db_link);
//$cl_ytd_data = mysql_fetch_assoc($cl_ytd_send);
//print_r($tc_month_data);
$ncl_ytd_data = $DB->get_single_result($ncl_ytd_sql);
$ncl_ytd = $ncl_ytd_data['count'];

$ncl_lytd_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN PersonsNotes ON PersonsNotes.PersonsNotes_personID=Persons.Person_id
WHERE
	1
AND
	(PersonsNotes.PersonsNotes_dateCreated >= '".$lytd_floor."' AND PersonsNotes.PersonsNotes_dateCreated <= '".$lytd_peak."')
AND
	PersonsNotes.PersonsNotes_type='Call Note'
";
if($_POST['state'] != ''):
$cl_lytd_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$cl_lytd_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$cl_lytd_send = mysql_query($cl_lytd_sql, $db_link);
//$cl_lytd_data = mysql_fetch_assoc($cl_lytd_send);
//print_r($tc_month_data);
$ncl_lytd_data = $DB->get_single_result($ncl_lytd_sql);
$ncl_lytd = $ncl_lytd_data['count'];

/*
//MEETINGS //
$ap_month_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN Appointments ON Appointments.Appointments_personId
WHERE
	1
AND
	(Appointments.Appointments_time >= '".$month_floor."' AND Appointments.Appointments_time <= '".$month_peak."')
AND
	Appointments.Appointments_type='Sales'
";
if($_POST['state'] != ''):
$ap_month_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$ap_month_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$ap_month_send = mysql_query($ap_month_sql, $db_link);
//$ap_month_data = mysql_fetch_assoc($ap_month_send);
//print_r($tc_month_data);
$ap_month_data = $DB->get_single_result($ap_month_sql);
$ap_month = $ap_month_data['count'];


$ap_lmonth_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN Appointments ON Appointments.Appointments_personId
WHERE
	1
AND
	(Appointments.Appointments_time >= '".$lmonth_floor."' AND Appointments.Appointments_time <= '".$lmonth_peak."')
AND
	Appointments.Appointments_type='Sales'
";
if($_POST['state'] != ''):
$ap_lmonth_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$ap_lmonth_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$ap_lmonth_send = mysql_query($ap_lmonth_sql, $db_link);
//$ap_lmonth_data = mysql_fetch_assoc($ap_lmonth_send);
//print_r($tc_month_data);
$ap_lmonth_data = $DB->get_single_result($ap_lmonth_sql);
$ap_lmonth = $ap_lmonth_data['count'];

$ap_ytd_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN Appointments ON Appointments.Appointments_personId
WHERE
	1
AND
	(Appointments.Appointments_time >= '".$ytd_floor."' AND Appointments.Appointments_time <= '".$ytd_peak."')
AND
	Appointments.Appointments_type='Sales'
";
if($_POST['state'] != ''):
$ap_ytd_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$ap_ytd_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$ap_ytd_send = mysql_query($ap_ytd_sql, $db_link);
//$ap_ytd_data = mysql_fetch_assoc($ap_ytd_send);
//print_r($tc_month_data);
$ap_ytd_data = $DB->get_single_result($ap_ytd_sql);
$ap_ytd = $ap_ytd_data['count'];

$ap_lytd_sql = "
SELECT
	count(*) as count
FROM
	Persons
	INNER JOIN Appointments ON Appointments.Appointments_personId
WHERE
	1
AND
	(Appointments.Appointments_time >= '".$lytd_floor."' AND Appointments.Appointments_time <= '".$lytd_peak."')
AND
	Appointments.Appointments_type='Sales'
";
if($_POST['state'] != ''):
$ap_lytd_sql .= "
AND
	Persons.Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$ap_lytd_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//$ap_lytd_send = mysql_query($ap_lytd_sql, $db_link);
//$ap_lytd_data = mysql_fetch_assoc($ap_lytd_send);
//print_r($tc_month_data);
$ap_lytd_data = $DB->get_single_result($ap_lytd_sql);
$ap_lytd = $ap_lytd_data['count'];
*/


// SALES //
$tc_month_sql = "
SELECT
	CONCAT(Persons.FirstName,' ',Persons.LastName) as Name,
	Persons.Person_id,
	PersonsSales.PersonsSales_basePrice,
	PersonsSales.PersonsSales_taxes,
	PersonsSales.PersonsSales_dateCreated,
	Persons.DateCreated,
	Persons.Assigned_userID,
	Persons.DateOfBirth
FROM
	Persons
	INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id
WHERE
	1
AND
	(PersonsSales.PersonsSales_dateCreated >= '".$month_floor."' AND PersonsSales.PersonsSales_dateCreated <= '".$month_peak."')
";
if($_POST['state'] != ''):
$tc_month_sql .= "
AND
	PersonsSales.Offices_Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$tc_month_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//echo $tc_month_sql;
//$tc_month_send = mysql_query($tc_month_sql, $db_link);
//while($tc_month_data = mysql_fetch_assoc($tc_month_send)) {
$tc_month_send = $DB->get_multi_result($tc_month_sql);
if(!isset($tc_month_send['empty_result'])) {
	foreach($tc_month_send as $tc_month_data) {
		$tc_month_array[] = $tc_month_data['PersonsSales_basePrice'] + $tc_month_data['PersonsSales_taxes'];
		$time_month_array[] = $tc_month_data['PersonsSales_dateCreated'] - $tc_month_data['DateCreated'];
	}
}
$tc_month = @count($tc_month_array);
$tc_month_sum = @array_sum($tc_month_array);
$tc_month_avg = @round($tc_month_sum / $tc_month);
$time_month_avg = @round(((array_sum($time_month_array) / count($time_month_array))/2592000), 1);

$tc_lmonth_sql = "
SELECT
	CONCAT(Persons.FirstName,' ',Persons.LastName) as Name,
	Persons.Person_id,
	PersonsSales.PersonsSales_basePrice,
	PersonsSales.PersonsSales_taxes,
	PersonsSales.PersonsSales_dateCreated,
	Persons.DateCreated,
	Persons.Assigned_userID,
	Persons.DateOfBirth
FROM
	Persons
	INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id
WHERE
	1
AND
	(PersonsSales.PersonsSales_dateCreated >= '".$lmonth_floor."' AND PersonsSales.PersonsSales_dateCreated <= '".$lmonth_peak."')
";
if($_POST['state'] != ''):
$tc_lmonth_sql .= "
AND
	PersonsSales.Offices_Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$tc_lmonth_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//echo $tc_lmonth_sql;
//$tc_lmonth_send = mysql_query($tc_lmonth_sql, $db_link);
//while($tc_lmonth_data = mysql_fetch_assoc($tc_lmonth_send)) {
$tc_lmonth_send = $DB->get_multi_result($tc_lmonth_sql);
if(!isset($tc_lmonth_send['empty_result'])) {
	foreach($tc_lmonth_send as $tc_lmonth_data) {	
		$tc_lmonth_array[] = $tc_lmonth_data['PersonsSales_basePrice'] + $tc_lmonth_data['PersonsSales_taxes'];
		$time_lmonth_array[] = $tc_lmonth_data['PersonsSales_dateCreated'] - $tc_lmonth_data['Persons_dateCreated'];
	}
}
$tc_lmonth = @count($tc_lmonth_array);
$tc_lmonth_sum = @array_sum($tc_lmonth_array);
$tc_lmonth_avg = @round($tc_lmonth_sum / $tc_lmonth);
$time_lmonth_avg = @round(((array_sum($time_lmonth_array) / count($time_lmonth_array))/2592000), 1);

$tc_ytd_sql = "
SELECT
	CONCAT(Persons.FirstName,' ',Persons.LastName) as Name,
	Persons.Person_id,
	PersonsSales.PersonsSales_basePrice,
	PersonsSales.PersonsSales_taxes,
	PersonsSales.PersonsSales_dateCreated,
	Persons.DateCreated,
	Persons.Assigned_userID,
	Persons.DateOfBirth
FROM
	Persons
	INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id
WHERE
	1
AND
	(PersonsSales.PersonsSales_dateCreated >= '".$ytd_floor."' AND PersonsSales.PersonsSales_dateCreated <= '".$ytd_peak."')
";
if($_POST['state'] != ''):
$tc_ytd_sql .= "
AND
	PersonsSales.Offices_Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$tc_ytd_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//echo $tc_lmonth_sql;
//$tc_ytd_send = mysql_query($tc_ytd_sql, $db_link);
//while($tc_ytd_data = mysql_fetch_assoc($tc_ytd_send)) {
$tc_ytd_send = $DB->get_multi_result($tc_ytd_sql);
if(!isset($tc_ytd_send['empty_result'])) {
	foreach($tc_ytd_send as $tc_ytd_data) {
		$tc_ytd_array[] = $tc_ytd_data['PersonsSales_basePrice'] + $tc_ytd_data['PersonsSales_taxes'];
		$time_ytd_array[] = $tc_ytd_data['PersonsSales_dateCreated'] - $tc_ytd_data['Persons_dateCreated'];
	}
}
$tc_ytd = @count($tc_ytd_array);
$tc_ytd_sum = @array_sum($tc_ytd_array);
$tc_ytd_avg = @round($tc_ytd_sum / $tc_ytd);
$time_ytd_avg = @round(((array_sum($time_ytd_array) / count($time_ytd_array))/2592000), 1);

$tc_lytd_sql = "
SELECT
	CONCAT(Persons.FirstName,' ',Persons.LastName) as Name,
	Persons.Person_id,
	PersonsSales.PersonsSales_basePrice,
	PersonsSales.PersonsSales_taxes,
	PersonsSales.PersonsSales_dateCreated,
	Persons.DateCreated,
	Persons.Assigned_userID,
	Persons.DateOfBirth
FROM
	Persons
	INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id
WHERE
	1
AND
	(PersonsSales.PersonsSales_dateCreated >= '".$lytd_floor."' AND PersonsSales.PersonsSales_dateCreated <= '".$lytd_peak."')
";
if($_POST['state'] != ''):
$tc_lytd_sql .= "
AND
	PersonsSales.Offices_Offices_id = '".$_POST['state']."'
";
endif;
if($_POST['telemarketer'] != ''):
$tc_lytd_sql .= "
AND
	Persons.Assigned_userID = '".$_POST['telemarketer']."'
";
endif;
//echo $tc_lmonth_sql;
//$tc_lytd_send = mysql_query($tc_lytd_sql, $db_link);
//while($tc_lytd_data = mysql_fetch_assoc($tc_lytd_send)) {
$tc_lytd_send = $DB->get_multi_result($tc_lytd_sql);
if(!isset($tc_lytd_send['empty_result'])) {
	foreach($tc_lytd_send as $tc_lytd_data) {	
		$tc_lytd_array[] = $tc_lytd_data['PersonsSales_basePrice'] + $tc_lytd_data['PersonsSales_taxes'];
		$time_lytd_array[] = $tc_lytd_data['PersonsSales_dateCreated'] - $tc_lytd_data['Persons_dateCreated'];
	}
}
$tc_lytd = @count($tc_lytd_array);
$tc_lytd_sum = @array_sum($tc_lytd_array);
$tc_lytd_avg = @round($tc_lytd_sum / $tc_lytd);
$time_lytd_avg = @round(((array_sum($time_lytd_array) / count($time_lytd_array))/2592000), 1);


$close_month = @round(($tc_month / $ap_month) * 100, 1);
$close_lmonth = @round(($tc_lmonth / $ap_lmonth) * 100, 1);
$close_ytd = @round(($tc_ytd / $ap_ytd) * 100, 1);
$close_lytd = @round(($tc_lytd / $ap_lytd) * 100, 1);

$REPORT_TITLE = 'Sales Metrics Reports: '.date("M Y", $startepoch);
$REPORT_FILENAME = 'SalesMetricsReport_'.date("Y-m", $startepoch).'.csv';
//print_r($_POST);

$exportRows[] = array('Total # of Leads', $TOTAL_LEADS);
$exportRows[] = array('Total # of Clients', $TOTAL_CLIENTS);
$exportRows[] = array('Total # of Resources', $TOTAL_RESOURCES);
$exportRows[] = array((($_POST['state'] != '')? $RECORD->getOfficeName($_POST['state']):''), $RECORD->get_userName($_POST['telemarketer']), date("M", $startepoch), date("Y", $startepoch));
$exportRows[] = array('','MONTH','LAST MONTH','COMPARED','YTD','LYTD','COMPARED YTD');
$exportRows[] = array('New Leads', $l_month, $l_lmonth, bw_check($l_month, $l_lmonth, true), $l_ytd, $l_lytd, bw_check($l_ytd, $l_lytd, true));
$exportRows[] = array('# of Calls', $cl_month, $cl_lmonth, bw_check($cl_month, $cl_lmonth, true), $cl_ytd, $cl_lytd, bw_check($cl_ytd, $cl_lytd, true));
$exportRows[] = array('# of Emails', $mail_month, $mail_lmonth, bw_check($mail_month, $mail_lmonth, true), $mail_ytd, $mail_lytd, bw_check($mail_ytd, $mail_lytd, true));
$exportRows[] = array('# of Notes', $ncl_month, $ncl_lmonth, bw_check($ncl_month, $ncl_lmonth, true), $ncl_ytd, $ncl_lytd, bw_check($ncl_ytd, $ncl_lytd, true));
$exportRows[] = array('Sales Closed #', $tc_month, $tc_lmonth, bw_check($tc_month, $tc_lmonth, true), $tc_ytd, $tc_lytd, bw_check($tc_ytd, $tc_lytd, true));
$exportRows[] = array('Sales Closed $', number_format($tc_month_sum, 2), number_format($tc_lmonth_sum, 2), bw_check($tc_month_sum, $tc_lmonth_sum, true), number_format($tc_ytd_sum, 2), number_format($tc_lytd_sum, 2), bw_check($tc_ytd_sum, $tc_lytd_sum, true));
$exportRows[] = array('Average Sale $', number_format($tc_month_avg, 2), number_format($tc_lmonth_avg, 2), bw_check($tc_month_avg, $tc_lmonth_avg, true), number_format($tc_ytd_avg, 2), number_format($tc_lytd_avg, 2), bw_check($tc_ytd_avg, $tc_lytd_avg, true));
$exportRows[] = array('Avg Sales Cycle (months)', $time_month_avg, $time_lmonth_avg, bw_check($time_month_avg, $time_lmonth_avg, true), $time_ytd_avg, $time_lytd_avg, bw_check($time_ytd_avg, $time_lytd_avg, true));

?>
<style>
.empty-cell {
	background-color:#333;
}
#chartdiv {
	width	: 100%;
	height	: 500px;
}	
</style>
<div class="container">
	<div class="row">
    	<div class="col-sm-8">
			<h3>SALES METRICS REPORT</h3>
		</div>
        <div class="col-sm-4">
        	<h3 class="text-right">
            	<!--
                <form action="/viewreport/6" method="post" id="report-export-1" target="_blank">
                    <input type="hidden" name="report_file" value="<?php echo $_GET['report_file']?>" />
                    <input type="hidden" name="StartDate" value="<?php echo $_GET['StartDate']?>" />
                    <input type="hidden" name="EndDate" value="<?php echo $_GET['EndDate']?>" />
                    <input type="hidden" name="inFrame" value="<?php echo $_GET['inFrame']?>" />
                    
                    <input type="hidden" name="state" id="export_state" value="<?php echo $_POST['state']?>" />
                    <input type="hidden" name="telemarketer" id="export_telemarketer" value="<?php echo $_POST['telemarketer']?>" />
                    					
                    <button type="button" class="btn btn-sm btn-default" onclick="window.print();">Print <i class="glyphicon glyphicon-print"></i></button>
                    <button type="button" class="btn btn-sm btn-default" onclick="$('#report-export-1').submit();">Export <i class="glyphicon glyphicon-save"></i></button>
                    <button type="button" class="btn btn-sm btn-default" onclick="parent.getReport('report.cc.metrics.php')">Refresh <i class="glyphicon glyphicon-repeat"></i></button>            
				</form>
                -->       	
            </h3>
        </div>
	</div>                    
	<div class="row">
	<div class="col-md-12">
	
	<table width="100%" id="table" border="1" cellspacing="0" cellpadding="0" class="table table-condensed table-bordered">    
		<tr>
            <td><span data-toggle="m-tooltip" title="" data-original-title="Current number of LEADS that are assigned to this record as telemarketer.">Total # of Leads</span></td>
            <td class="text-center"><?php echo $TOTAL_LEADS?></td>
            <td colspan="6" class="empty-cell">&nbsp;</td>
        </tr>
        <tr>
            <td><span data-toggle="m-tooltip" title="" data-original-title="Current number of PENDING or ACTIVE MEMBERS that are assigned to this record as telemarketer">Total # of Clients</span></td>
            <td class="text-center"><?php echo $TOTAL_CLIENTS?></td>
            <td colspan="6" class="empty-cell">&nbsp;</td>
        </tr>
        <tr>
        	<td><span data-toggle="m-tooltip" title="" data-original-title="Current number of PARTICIPATING, RESOURCE or FREE MEMBERS that are assigned to this record as telemarketer">Total # of Resources</span></td>
            <td class="text-center"><?php echo $TOTAL_RESOURCES?></td>
            <td colspan="6" class="empty-cell text-right">
            	<form id="report-export-matrix" action="/export_table_to_csv.php" method="post" target="_blank">
                <input type="hidden" name="export-data" value="<?php echo urlencode(serialize($exportRows))?>" />
                <input type="hidden" name="export-filename" value="<?php echo $REPORT_FILENAME?>" />
                <button type="submit" class="btn btn-default btn-sm">Export to CSV <i class="fa fa-bar-chart"></i></button>
	            </form>             
            </td>
        </tr>
        <tr>
        	<td colspan="8" class="empty-cell">
        	<?php
			$c_startEpoch = strtotime($_POST['StartDate']);
			$monthSet = date("n", $c_startEpoch); 
			$yearSet = date("Y", $c_startEpoch);
			
			?>
            <form action="/viewreport/6" method="post" id="report-form-1">
            <input type="hidden" name="StartDate" value="<?php echo $_POST['StartDate']?>">
            <input type="hidden" name="EndDate" value="<?php echo $_POST['EndDate']?>">           
            <div class="input-group input-group-sm">
	            <select name="state" id="state" class="form-control input-sm">
    		        <?php echo $stateSelect?>
            	</select>
            	<span class="input-group-addon">&nbsp;</span>
                <select name="telemarketer" id="telemarketer" class="form-control input-sm">
					<option value="">All Users</option>
					<?php echo $userDropdown?>
            	</select>
                <span class="input-group-addon">&nbsp;</span>
                <select name="month_set" id="month_set" class="form-control input-sm">
					<option value="1" <?php echo (($monthSet == 1)? 'selected':'')?>>Jan</option>
                    <option value="2" <?php echo (($monthSet == 2)? 'selected':'')?>>Feb</option>
                    <option value="3" <?php echo (($monthSet == 3)? 'selected':'')?>>Mar</option>
                    <option value="4" <?php echo (($monthSet == 4)? 'selected':'')?>>Apr</option>
                    <option value="5" <?php echo (($monthSet == 5)? 'selected':'')?>>May</option>
                    <option value="6" <?php echo (($monthSet == 6)? 'selected':'')?>>Jun</option>                    
                    <option value="7" <?php echo (($monthSet == 7)? 'selected':'')?>>Jul</option>
                    <option value="8" <?php echo (($monthSet == 8)? 'selected':'')?>>Aug</option>
                    <option value="9" <?php echo (($monthSet == 9)? 'selected':'')?>>Sep</option>
                    <option value="10" <?php echo (($monthSet == 10)? 'selected':'')?>>Oct</option>
                    <option value="11" <?php echo (($monthSet == 11)? 'selected':'')?>>Nov</option>
                    <option value="12" <?php echo (($monthSet == 12)? 'selected':'')?>>Dec</option>
            	</select>
                <span class="input-group-addon">&nbsp;</span>
                <select name="year_set" id="year_set" class="form-control input-sm">
                	<?php for($i=0; $i<5; $i++): ?>
                    <option value="<?php echo (date("Y") - $i)?>" <?php echo (($yearSet == (date("Y") - $i))? 'selected':'')?>><?php echo (date("Y") - $i)?></option>
                    <?php endfor; ?>                    
            	</select>
                <span class="input-group-btn">
                	<button type="button" class="btn btn-sm btn-primary" onclick="$('#report-form-1').submit();">Update</button>
                </span>                
			</div>                
            </form>            
            </td>
        </tr>
  		<tr>
            <td colspan="8" class="empty-cell text-center">
            	<?php
				//print_r($_POST['StartDate']);
				$c_startEpoch = strtotime($_POST['StartDate']);
				//$startepoch;
				
				$prevMonthEpoch_s = mktime(0, 0, 0, date("m", $c_startEpoch) - 1, date("d", $c_startEpoch),date("Y", $c_startEpoch));
				$prevMonth_s = date("m/d/Y", $prevMonthEpoch_s);				
				$prevMonthEpoch_e = mktime(23, 59, 59, date("m",$prevMonthEpoch_s), date("t",$prevMonthEpoch_s), date("Y",$prevMonthEpoch_s));
				$prevMonth_e = date("m/d/Y", $prevMonthEpoch_e);
				
				$nextMonthEpoch_s = mktime(0, 0, 0, date("m", $c_startEpoch) + 1, date("d", $c_startEpoch),date("Y", $c_startEpoch));
				$nextMonth_s = date("m/d/Y", $nextMonthEpoch_s);
				$nextMonthEpoch_e = mktime(23, 59, 59, date("m",$nextMonthEpoch_s),date("t",$nextMonthEpoch_s),date("Y",$nextMonthEpoch_s));
				$nextMonth_e = date("m/d/Y", $nextMonthEpoch_e);
				//echo "PREV:".$prevMonth_s."|".$prevMonth_e."<br>\n"; 
				//echo "NEXT:".$nextMonth_s."|".$nextMonth_e."<br>\n";
				/*
				?>
            	<div class="pull-left">
                	<form action="/viewreport/6" method="post">
                        <input type="hidden" name="StartDate" value="$prevMonth_s?>">
                        <input type="hidden" name="EndDate" value="$prevMonth_e?>">
                        <input type="hidden" name="state" value="$_POST['state']?>" />
                        <input type="hidden" name="telemarketer" value="$_POST['telemarketer']?>" />
                        <button type="submit" class="btn btn-xs btn-default"><i class="glyphicon glyphicon-arrow-left"></i> Prev Month </button>
                    </form>
				</div>
                <div class="pull-right">
                	<form action="/viewreport/6" method="post">
                        <input type="hidden" name="StartDate" value="$nextMonth_s?>">
                        <input type="hidden" name="EndDate" value="$nextMonth_e?>">
                        <input type="hidden" name="state" value="$_POST['state']?>" />
                        <input type="hidden" name="telemarketer" value="$_POST['telemarketer']?>" />
                        <button type="submit" class="btn btn-xs btn-default"> Next Month <i class="glyphicon glyphicon-arrow-right"></i> </button>
                    </form>
                </div>
				<?
				*/
				?>                
                <span style="font-size:1.5em; color:#FFFFFF;"><?php echo $REPORT_TITLE?></span>
            </td>
        </tr>
		<tr>
        <th width="35%">&nbsp;</th>
        <td width="10%" class="text-center"><strong>Month</strong></td>
        <td width="10%" class="text-center"><strong>Last Month</strong></td>
        <td width="10%" class="text-center"><strong>B/W</strong></td>
        <td width="5%">&nbsp;</td>
        <td width="10%" class="text-center"><strong>YTD</strong></td>
        <td width="10%" class="text-center"><strong>LYTD</strong></td>
        <td width="10%" class="text-center"><strong>B/W</strong></td>
    </tr>
  	<tr>
    	<td>
    		<span data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-original-title="" data-content="Records created within the time frame and filters applied.">New Leads</span>
            <a href="javascript:openChartsModal('newLeads', '<?php echo $c_startEpoch?>', '<?php echo $_POST['telemarketer']?>');" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-original-title="" data-content="this month vs last month vs this month last year" class="btn btn-outline-metal m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill pull-right"><i class="la la-signal"></i></a>
        </td>
	    <td class="text-center"><?php echo $l_month?></td>
	    <td class="text-center"><?php echo $l_lmonth?></td>
    	<td class="text-center"><?php echo bw_check($l_month, $l_lmonth)?></td>
    <td>&nbsp;</td>
    	<td class="text-center"><?php echo $l_ytd?></td>
	    <td class="text-center"><?php echo $l_lytd?></td>
    	<td class="text-center"><?php echo bw_check($l_ytd, $l_lytd)?></td>    
	</tr>
    <tr>
        <td><span data-toggle="m-tooltip" title="" data-original-title="Notes marked as CALL NOTE within the time frame and filters applied."># of Calls</span></td>
        <td class="text-center"><?php echo $cl_month?></td>
        <td class="text-center"><?php echo $cl_lmonth?></td>
        <td class="text-center"><?php echo bw_check($cl_month, $cl_lmonth)?></td>
        <td>&nbsp;</td>
        <td class="text-center"><?php echo $cl_ytd?></td>
        <td class="text-center"><?php echo $cl_lytd?></td>
        <td class="text-center"><?php echo bw_check($cl_ytd, $cl_lytd)?></td>
    </tr>
    <tr>
        <td><span data-toggle="m-tooltip" title="" data-original-title="Notes marked as EMAIL within the time frame and filters applied."># of Emails</span></td>
        <td class="text-center"><?php echo $mail_month?></td>
        <td class="text-center"><?php echo $mail_lmonth?></td>
        <td class="text-center"><?php echo bw_check($mail_month, $mail_lmonth)?></td>
        <td>&nbsp;</td>
        <td class="text-center"><?php echo $mail_ytd?></td>
        <td class="text-center"><?php echo $mail_lytd?></td>
        <td class="text-center"><?php echo bw_check($mail_ytd, $mail_lytd)?></td>
    </tr>
    <tr>
        <td><span data-toggle="m-tooltip" title="" data-original-title="Notes marked as NOTE within the time frame and filters applied."># of Notes</span></td>
        <td class="text-center"><?php echo $ncl_month?></td>
        <td class="text-center"><?php echo $ncl_lmonth?></td>
        <td class="text-center"><?php echo bw_check($ncl_month, $ncl_lmonth)?></td>
        <td>&nbsp;</td>
        <td class="text-center"><?php echo $ncl_ytd?></td>
        <td class="text-center"><?php echo $ncl_lytd?></td>
        <td class="text-center"><?php echo bw_check($ncl_ytd, $ncl_lytd)?></td>
    </tr>
    <tr>
	    <td colspan="8">&nbsp;</td>
    </tr>
    <tr>
        <td>
        	<span data-toggle="m-tooltip" title="" data-original-title="Number of SALES executed within the time frame and assigned to the salesperson listed.">Sales Closed #</span>
			<a href="javascript:openChartsModal('newSales', '<?php echo $c_startEpoch?>', '<?php echo $_POST['telemarketer']?>');" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-original-title="" data-content="this month vs last month vs this month last year" class="btn btn-outline-metal m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill pull-right"><i class="la la-signal"></i></a>
        </td>
        <td class="text-center"><a href="javascript:openDetailsModal('Sales Closed this Month', '<?php echo $ENC->encrypt($tc_month_sql)?>')"><?php echo $tc_month?></a></td>
        <td class="text-center"><a href="javascript:openDetailsModal('Sales Closed last Month', '<?php echo $ENC->encrypt($tc_lmonth_sql)?>')"><?php echo $tc_lmonth?></a></td>
        <td class="text-center"><?php echo bw_check($tc_month, $tc_lmonth)?></td>
        <td>&nbsp;</td>
        <td class="text-center"><a href="javascript:openDetailsModal('Sales Closed Year to Date', '<?php echo $ENC->encrypt($tc_ytd_sql)?>')"><?php echo $tc_ytd?></a></td>
        <td class="text-center"><a href="javascript:openDetailsModal('Sales Closed Last Year to Date', '<?php echo $ENC->encrypt($tc_lytd_sql)?>')"><?php echo $tc_lytd?></a></td>
        <td class="text-center"><?php echo bw_check($tc_ytd, $tc_lytd)?></td>
    </tr>
    <tr>
	    <td>
        	<span data-toggle="m-tooltip" title="" data-original-title="Total dollars from SALES executed within the time frame and assigned to the salesperson listed.">Sales Closed $</span>
            <a href="javascript:openChartsModal('newSalesDollars', '<?php echo $c_startEpoch?>', '<?php echo $_POST['telemarketer']?>');" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-original-title="" data-content="this month vs last month vs this month last year" class="btn btn-outline-metal m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill pull-right"><i class="la la-signal"></i></a>
		</td>
	    <td class="text-right"><?php echo number_format($tc_month_sum, 0)?></td>
    	<td class="text-right"><?php echo number_format($tc_lmonth_sum, 0)?></td>
    	<td class="text-center"><?php echo bw_check($tc_month_sum, $tc_lmonth_sum)?></td>
        <td>&nbsp;</td>
	    <td class="text-right"><?php echo number_format($tc_ytd_sum, 0)?></td>
    	<td class="text-right"><?php echo number_format($tc_lytd_sum, 0)?></td>
	    <td class="text-center"><?php echo bw_check($tc_ytd_sum, $tc_lytd_sum)?></td>    	
    </tr>
	<tr>
		<td>
        	<span data-toggle="m-tooltip" title="" data-original-title="Average amount of all closed SALES executed within the time frame and assigned to the salesperson listed.">Average Sale $</span>
        	<a href="javascript:openChartsModal('avgSalesDollars', '<?php echo $c_startEpoch?>', '<?php echo $_POST['telemarketer']?>');" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-original-title="" data-content="this month vs last month vs this month last year" class="btn btn-outline-metal m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill pull-right"><i class="la la-signal"></i></a>    
		</td>
		<td class="text-right"><?php echo number_format($tc_month_avg, 0)?></td>
		<td class="text-right"><?php echo number_format($tc_lmonth_avg, 0)?></td>
		<td class="text-center"><?php echo bw_check($tc_month_avg, $tc_lmonth_avg)?></td>
		<td>&nbsp;</td>
		<td class="text-right"><?php echo number_format($tc_ytd_avg, 0)?></td>
		<td class="text-right"><?php echo number_format($tc_lytd_avg, 0)?></td>
		<td class="text-center"><?php echo bw_check($tc_ytd_avg, $tc_lytd_avg)?></td>
	</tr>
    <!--
    <tr>
        <td>% Closed</td>
        <td class="text-center"><?php echo $close_month?>%</td>        
        <td class="text-center"><?php echo $close_lmonth?>%</td>
        <td class="text-center"><?php echo bw_check($close_month, $close_lmonth)?></td>
        <td>&nbsp;</td>
        <td class="text-center"><?php echo $close_ytd?>%</td>
        <td class="text-center"><?php echo $close_lytd?>%</td>
        <td class="text-center"><?php echo bw_check($close_ytd, $close_lytd)?></td>
    </tr>
    -->
    <tr>
	    <td colspan="8">&nbsp;</td>
    </tr>  
    <tr>
        <td>
        	<span data-toggle="m-tooltip" title="" data-original-title="Average time (in months) it takes to go from record created to sale using the sales within the time frame and assigned to the salesperson listed.">Avg Sales Cycle (months)</span>			
        </td>
        <td class="text-center"><?php echo $time_month_avg?></td>
        <td class="text-center"><?php echo $time_lmonth_avg?></td>
        <td class="text-center"><?php echo bw_check($time_month_avg, $time_lmonth_avg)?></td>
        <td>&nbsp;</td>
        <td class="text-center"><?php echo $time_ytd_avg?></td>
        <td class="text-center"><?php echo $time_lytd_avg?></td>
        <td class="text-center"><?php echo bw_check($time_ytd_avg, $time_lytd_avg)?></td>
    </tr>   
</table>
	</div>
    <div class="col-md-12">
    	<div id="chartdiv"></div>
    </div>
</div>

<div class="modal fade" id="metricsDetailsModal" tabindex="-1" role="dialog" aria-labelledby="metricsDetailsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="metricsDetailsModalLabel"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="metricsGraphsModal" tabindex="-1" role="dialog" aria-labelledby="metricsGraphsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="metricsGraphsModalLabel"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="detailChartsModalArea">
            

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php
//print_r($exportRows);
for($i=1; $i<13; $i++) {
	$month_floor	= mktime(0,0,0, $i, 1, date("Y", $startepoch));
	$month_peak 	= mktime(23,59,59, $i, date("t", $month_floor), date("Y", $startepoch));
	
	$monthLabel[] = date("M y", $month_floor);
	
	$l_month_sql = "
	SELECT
		count(*) as count
	FROM
		Persons
	WHERE
		1
	AND
		(Persons.DateCreated >= '".$month_floor."' AND Persons.DateCreated <= '".$month_peak."')
	";
	if($_POST['state'] != ''):
	$l_month_sql .= "
	AND
		Persons.Offices_id = '".$_POST['state']."'
	";
	endif;
	if($_POST['telemarketer'] != ''):
	$l_month_sql .= "
	AND
		Assigned_userID = '".$_POST['telemarketer']."'
	";
	endif;
	//$l_month_send = mysql_query($l_month_sql, $db_link);
	//$l_month_data = mysql_fetch_assoc($l_month_send);
	//print_r($tc_month_data);
	$l_month_data = $DB->get_single_result($l_month_sql);
	$leads_graph_count[] = $l_month_data['count'];
	
	$ap_month_sql = "
	SELECT
		count(*) as count
	FROM
		Persons
		INNER JOIN Appointments ON Appointments.Appointments_personId
	WHERE
		1
	AND
		(Appointments.Appointments_time >= '".$month_floor."' AND Appointments.Appointments_time <= '".$month_peak."')
	AND
		Appointments.Appointments_type='Sales'
	";
	if($_POST['state'] != ''):
	$ap_month_sql .= "
	AND
		Persons.Offices_id = '".$_POST['state']."'
	";
	endif;
	if($_POST['telemarketer'] != ''):
	$ap_month_sql .= "
	AND
		Assigned_userID = '".$_POST['telemarketer']."'
	";
	endif;
	//$ap_month_send = mysql_query($ap_month_sql, $db_link);
	//$ap_month_data = mysql_fetch_assoc($ap_month_send);
	//print_r($tc_month_data);
	$ap_month_data = $DB->get_single_result($ap_month_sql);
	$app_graph_count[] = $ap_month_data['count'];
	
	
	$tc_month_sql = "
	SELECT
		COUNT(*) as count
	FROM
		Persons
		INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id
	WHERE
		1
	AND
		(PersonsSales.PersonsSales_dateCreated >= '".$month_floor."' AND PersonsSales.PersonsSales_dateCreated <= '".$month_peak."')
	";
	if($_POST['state'] != ''):
	$tc_month_sql .= "
	AND
		Persons.Offices_id = '".$_POST['state']."'
	";
	endif;
	if($_POST['telemarketer'] != ''):
	$tc_month_sql .= "
	AND
		Assigned_userID = '".$_POST['telemarketer']."'
	";
	endif;
	//echo $tc_month_sql;
	//$tc_month_send = mysql_query($tc_month_sql, $db_link);
	//$tc_month_data = mysql_fetch_assoc($tc_month_send);
	$tc_month_data = $DB->get_single_result($tc_month_sql);
	$client_graph_count[] = $tc_month_data['count'];
	
}
$lead_records_graph = implode(",", $leads_graph_count);
$app_records_graph = implode(",", $app_graph_count);
$client_records_graph = implode(",", $client_graph_count);

for($l=0; $l<count($leads_graph_count); $l++) {
	$dataProvider[] = array(
		"month"	=>	$monthLabel[$l],
		"leads"	=>	$leads_graph_count[$l],
		"appt"	=>	$app_graph_count[$l],
		"sales"	=>	$client_graph_count[$l]	
	);	
}
//print_r($dataProvider);
?>
  
<script>
var chart = AmCharts.makeChart( "chartdiv", {
	"type": "serial",
  	"addClassNames": true,
  	"theme": "light",
  	"autoMargins": false,
  	"marginLeft": 30,
  	"marginRight": 8,
  	"marginTop": 10,
  	"marginBottom": 26,
  	"balloon": {
    	"adjustBorderColor": false,
    	"horizontalPadding": 10,
    	"verticalPadding": 8,
    	"color": "#ffffff"
  	},
  	"dataProvider": <?php echo json_encode($dataProvider)?>,
  	"valueAxes": [{
    	"axisAlpha": 0,
    	"position": "left"
  	}],
	 "startDuration": 1,
  	"graphs": [ {
		"alphaField": "alpha",
		"balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:20px;'>[[value]]</span> [[additional]]</span>",
		"fillAlphas": 1,
		"title": "Sales",
		"type": "column",
		"valueField": "sales",
		"dashLengthField": "dashLengthColumn"
  	}, {
		"id": "graph2",
		"balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:20px;'>[[value]]</span> [[additional]]</span>",
		"bullet": "round",
		"lineThickness": 3,
		"bulletSize": 7,
		"bulletBorderAlpha": 1,
		"bulletColor": "#FFFFFF",
		"useLineColorForBulletBorder": true,
		"bulletBorderThickness": 3,
		"fillAlphas": 0,
		"lineAlpha": 1,
		"title": "Leads",
		"valueField": "leads",
		"dashLengthField": "dashLengthLine"
  	}],
	"categoryField": "month",
	"categoryAxis": {
		"gridPosition": "start",
		"axisAlpha": 0,
		"tickLength": 0
  	},
  	"export": {
    	"enabled": true
  	}
});
function openDetailsModal(modalTitle, modalSQL) {
	$('#metricsDetailsModal').modal('show');
	$('#metricsDetailsModalLabel').html(modalTitle);
	mApp.block("#metricsDetailsModal .modal-body", {
		overlayColor: "#000000",
		type: "loader",
		state: "primary",
		message: "Loading..."
	});
	$.post('/ajax/smr.php?action=getDetails', {
		sql: modalSQL,
		kiss_token: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$("#metricsDetailsModal .modal-body").html(data);
		mApp.unblock("#metricsDetailsModal .modal-body");
		mApp.init();	
	});
}
function openChartsModal(chartKey, start, tm) {
	$("#metricsGraphsModal").modal('show');
	$('#detailChartsModalArea').html('LOADING INFORMATION...');	
	$.post('/ajax/smr.php?action=chartsModal', {
		chart: chartKey,
		start: start,
		tm: tm,
		kiss_token: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$('#detailChartsModalArea').html(data);		
	});
}
</script>
