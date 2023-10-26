<?php

include_once("class.settings.php");
include_once("class.marketing.php");
include_once("class.templates.php");
$SETTINGS = new Settings();
$TEMPLATES = new Templates();
$MKG = new Marketing();
date_default_timezone_set($SETTINGS->setting['DEFAULT_TIMEZONE']);
if(isset($_POST['deploy_id'])) {
	$deploy_id = $_POST['deploy_id'];
} elseif(array_key_exists('params', $pageParamaters) || count($pageParamaters['params']) > 0) {
	$deploy_id = $pageParamaters['params'][0];
} else {
	$deploy_id = 0;
}
if(!is_numeric($deploy_id) || $deploy_id < 0) {
	$deploy_id = 0;
}
if($deploy_id == 0) {
	$edit_mode = 'New';
} else {
	$edit_mode = 'Edit';
}
$loading_span = '<span class="m--font-bolder m--font-warning">Loading...</span>';
$empty_span = '<span class="m--font-bolder m--font-danger">Please fill out this field</span>';
$value_span = '<span class="m--font-bolder">';
$back_btn = '<div style="margin-top:5px;"><button type="button" class="btn btn-sm btn-default" onclick="document.location=\'/mkg-deployments\'"><i class="la la-arrow-left"></i>&nbsp;Back to Deployments</button></div>';
?>

<script src="/assets/app/js/marketing.js" type="text/javascript"></script>
<script type="text/javascript">
	//set variables used in marketing.js
	toolsetDir = '';
	loadingSpan = '<?php echo $loading_span?>';
	emptySpan = '<?php echo $empty_span?>';
	valueSpan = '<?php echo $value_span?>';
</script>
<style type="text/css">
.list-group a {
	cursor:pointer;
}
.list-group a:hover {
	background-color:#ececec;
}
.info_label {
	padding-top:.65rem;
}
#msg_body_plain {
	/*width:100%;
	height:300px;*/
}
ul.dropdown-menu .dropdown-header {
	background:#e6e6e6;
	color:#333;
}
.inactive_list {
    font-style: italic;
    color: #5E5E5E;
}
#prev_msg_body_html, #prev_msg_body_plain {
	min-height:350px;
	/*font-family: sans-serif, Arial, Verdana, "Trebuchet MS";
	font-size: 12px;
	line-height: 16px;
	color: #333;*/
}
#prev_msg_body_html a,
#prev_msg_body_html a:focus,
#prev_msg_body_html a:hover,
#prev_msg_body_html a:active,
#prev_msg_body_html a:visited {
	color: #0782C1;
	text-decoration:underline;
}
#prev_msg_body_html hr {
	border: 0px;
	border-top: 1px solid #ccc;
}
</style>
<?php
$form_errors  = array();
$dlists       = array();
$recip_count  = 0;
$sent_count   = '---';
$alert_mode   = '';
$invalid      = false;

if(array_key_exists('params', $pageParamaters) && count($pageParamaters['params']) > 1) {
	$alert_mode = $pageParamaters['params'][1];
}

require_once("includes/deployment.submit.php");

if($deploy_id != 0) {
	$data = $MKG->get_deployment($deploy_id);
	if(empty($data)) {
		$invalid = true;
	}
}

if(!$invalid) {
$dlists = ($_POST['submitted'] == 1) ? ($_POST['deploy_lists'] != '' ? explode('|', $_POST['deploy_lists']) : array()) : ( $deploy_status == 'Sent' ? $MKG->get_deployment_lists($deploy_id, true) : $MKG->get_deployment_lists($deploy_id) ); 
$assoc_lists = array();

foreach($dlists as $l) {
    $sql   = "SELECT MarketingLists_name, MarketingLists_active FROM MarketingLists WHERE MarketingLists_id = '".$DB->mysqli->escape_string($l)."' LIMIT 1";
    $query = $DB->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$DB->mysqli->error);
    
    if($query->num_rows > 0) {
        $row = $query->fetch_assoc();
        $assoc_lists[$l] = array('name' => $row['MarketingLists_name'], 'active' => $row['MarketingLists_active']);
    }
}

foreach($dlists as $list) {
    $groups = $MKG->get_list_groups($list);
    $counts = $MKG->get_group_counts($groups);
    
    $recip_count += $counts['emails'];
}

$deploy_status = $data['MarketingDeployments_status'];
$deploy_status = (strlen($deploy_status) < 1) ? 'Pending' : $deploy_status;
$esp           = (!empty($data)) ? $data['MarketingDeployments_ESP'] : $MKG->esp;
$deploy_date   = ($data['MarketingDeployments_dateSent'] > 0 && $deploy_status == 'Sent') ? date('n/j/Y g:ia', (($deploy_status != 'Sent' && $data['MarketingDeployments_dateSched'] > 0) ? $data['MarketingDeployments_dateSched'] : $data['MarketingDeployments_dateSent'])) : 'n/a';
$deploy_sentby = '';
switch($deploy_status) {
	case 'Pending':
		$deploy_status_badge = 'm-badge--brand';
		break;
	case 'Sent':
		$deploy_status_badge = 'm-badge--success';
		break;
	case 'Scheduled':
		$deploy_status_badge = 'm-badge--warning';
		break;
	case 'In Progress':
		$deploy_status_badge = 'm-badge--danger';
		break;
	default:
		$deploy_status_badge = 'm-badge--brand';
}
if($data['MarketingDeployments_sentBy'] > 0) {
	$user_sql = "SELECT firstName, lastName FROM Users WHERE user_id = '".$data['MarketingDeployments_sentBy']."'";
	$user_data = $DB->get_single_result($user_sql);
	if(array_key_exists('firstName', $user_data)) {
		$deploy_sentby = $user_data['firstName'].' '.$user_data['lastName'];
	}
}
$is_copied	   = ($alert_mode == 'copied') ? true : false;
$is_processing = ($deploy_status == 'In Progress') ? true : false;
$is_finished   = ($deploy_status == 'Sent') ? true : false;
$is_scheduled  = ($deploy_status == 'Scheduled') ? true : false;
$readonly      = ($is_processing ||  $is_scheduled || $is_finished) ? true : false;
if($readonly) {
	$edit_mode = 'View';
}
$list_sql = "SELECT MarketingLists.*,
ifNull(MarketingListCategories_name, 'Uncategorized') AS MarketingListCategories_name,
ifNull(MarketingListCategories_order, '9999') AS MarketingListCategories_order
FROM MarketingLists 
LEFT JOIN MarketingListCategories ON MarketingLists_category = MarketingListCategories_id
WHERE MarketingLists_active = '1' 
ORDER BY MarketingListCategories_order, MarketingLists_name";
$list_query = $DB->mysqli->query($list_sql) or die('Line: '.__LINE__ .' - '.$DB->mysqli->error);
$merge_fields = $MKG->get_merge_fields();
}
?>
<div class="m-subheader">
<h4><i class="flaticon-interface-2"></i> <?php echo $edit_mode?> Deployment</h4>
</div>
<div class="m-content">
<?php
if($invalid) {
	?><div class="alert alert-danger">ERROR: Invalid Deployment ID<?php echo $back_btn?></div><?php
} else {
	if($readonly) {
		include('deployment.view.php');
	} else {
		include('deployment.edit.php');
	}
}
?>
</div>