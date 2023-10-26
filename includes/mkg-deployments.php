<?php
include_once("class.datatables.php");
include_once("class.record.php");
include_once("class.encryption.php");
$RECORD = new Record($DB);
$ENC = new encryption();
$DATATABLE = new Datatable($DB, $RECORD, -1, $ENC);

$tableSQL = "
SELECT
	MarketingDeployments_id,
	MarketingDeployments_name,
	MarketingDeployments_status,
	FROM_UNIXTIME(MarketingDeployments_dateCreated, '%Y-%m-%d') as MarketingDeployments_dateCreated,
	CASE MarketingDeployments_dateSent
		WHEN '0' THEN '<span class=\"m-badge m-badge--metal m-badge--wide\">NOT SENT</span>'
		ELSE FROM_UNIXTIME(MarketingDeployments_dateSent, '%Y-%m-%d')
	END as MarketingDeployments_dateSent,
	CASE MarketingDeployments_status
		WHEN 'Pending' THEN 'm-badge--brand'
		WHEN 'Sent' THEN 'm-badge--success'
		WHEN 'Scheduled' THEN 'm-badge--warning'
		WHEN 'In Progress' THEN 'm-badge--danger'
		ELSE NULL
	END as statusClass,
	CASE MarketingDeployments_status
		WHEN 'Pending' THEN 'edit'
		WHEN 'Scheduled' THEN 'edit'
		WHEN 'In Progress' THEN 'pie-chart'
		WHEN 'Sent' THEN 'pie-chart'
		ELSE NULL
	END as viewClass
FROM 
	MarketingDeployments 
WHERE 
	1
";
$tableFields = array(
	array(
		'field'	=>	'MarketingDeployments_dateCreated',
		'label'	=>	'Created',
		'width'	=>	100
	),
	array(
		'field'	=>	'MarketingDeployments_name',
		'label'	=>	'Deployment Name',
		'width'	=>	250
	),
	array(
		'field'	=>	'MarketingDeployments_status',
		'label'	=>	'Status',
		'width'	=>	100,
		'template'	=>	'<span class="m-badge {{statusClass}} m-badge--wide">{{MarketingDeployments_status}}</span>'
	),
	array(
		'field'	=>	'MarketingDeployments_dateSent',
		'label'	=>	'Date Sent',
		'width'	=>	100
	),
	array(
		'field'	=>	'Actions',
		'label'	=>	'Actions',
		'width'	=>	75,
		'template'	=>	'<span class="deploy_actions" data-deployid="{{MarketingDeployments_id}}"><a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill btn_deploy_view" title="View Deployment"><i class="la la-{{viewClass}}"></i></a><a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-warning m-btn--icon m-btn--icon-only m-btn--pill btn_deploy_copy" title="Copy Deployment"><i class="la la-copy"></i></a></span>'
	)
);
$methodSQL = str_replace('"', '\"', (trim(preg_replace('/\s+/', ' ', $tableSQL))));
$portletNav = '<ul class="m-portlet__nav">
					<li class="m-portlet__nav-item">
						<a href="/mkg-deployment/0" class="m-portlet__nav-link btn btn-accent m-btn m-btn--pill">
							<i class="flaticon-add"></i>
							<span>
								Create Deployment
							</span>
						</a>
					</li>
				</ul>';
?>
<script src="/assets/app/js/marketing.js" type="text/javascript"></script>
<script type="text/javascript">
	//set variables used in marketing.js
	toolsetDir = '';
</script>
<div class="m-content">
	<input type="hidden" name="page_id" id="page_id" value="deployments_list" />
	<?php echo $DATATABLE->render_General_datatable("deplymentTable", '<i class="flaticon-interface-2"></i> Marketing Deployments', "/ajax/getMkgDeploymentData.php", $methodSQL, $tableFields, 'MarketingDeployments_id', 'MarketingDeployments_dateCreated', 'desc', 10, "$('#loadingTableBlock').hide();", true, $portletNav)?>
</div>