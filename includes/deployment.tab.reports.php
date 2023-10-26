<?php
if($data['MarketingDeployments_status'] != 'Pending' && $data['MarketingDeployments_status'] != 'Scheduled') { 
?>
<div style="margin:0 2.2rem 2.2rem 2.2rem;">
<ul class="nav nav-tabs m-tabs m-tabs-line m-tabs-line--primary" role="tablist">
	<li class="nav-item m-tabs__item"><a class="nav-link m-tabs__link active" data-toggle="tab" role="tab" href="#tab_Recipients" style="background-color:transparent;">Recipients</a></li>
	<li class="nav-item m-tabs__item"><a class="nav-link m-tabs__link" data-toggle="tab" role="tab" href="#tab_Opens" style="background-color:transparent;">Opens</a></li>
	<li class="nav-item m-tabs__item"><a class="nav-link m-tabs__link" data-toggle="tab" role="tab" href="#tab_Opens_Unique" style="background-color:transparent;">Unique Opens</a></li>
	<li class="nav-item m-tabs__item"><a class="nav-link m-tabs__link" data-toggle="tab" role="tab" href="#tab_Clicks" style="background-color:transparent;">Clicks</a></li>
	<li class="nav-item m-tabs__item"><a class="nav-link m-tabs__link" data-toggle="tab" role="tab" href="#tab_Bounces" style="background-color:transparent;">Bounces</a></li>
</ul>
<div class="tab-content">
	<div id="tab_Recipients" class="tab-pane active" role="tabpanel">
	<?php
		$recipients_data = $MKG->get_reporting_data($deploy_id, 'recipients');
		echo $MKG->render_reporting_grid('recipients', $recipients_data);
	?>
	</div>
	<div id="tab_Opens" class="tab-pane" role="tabpanel">
	<?php
		$opens_data = $MKG->get_reporting_data($deploy_id, 'opens');
		echo $MKG->render_reporting_grid('opens', $opens_data);
	?>
	</div>
	<div id="tab_Opens_Unique" class="tab-pane" role="tabpanel">
	<?php
		$opens_unique_data = $MKG->get_reporting_data($deploy_id, 'opens_unique');
		echo $MKG->render_reporting_grid('opens_unique', $opens_unique_data);
	?>
	</div>
	<div id="tab_Clicks" class="tab-pane" role="tabpanel">
	<?php
		$clicks_data = $MKG->get_reporting_data($deploy_id, 'clicks');
		echo $MKG->render_reporting_grid('clicks', $clicks_data);
	?>
	</div>
	<div id="tab_Bounces" class="tab-pane" role="tabpanel">
	<?php
		$bounces_data = $MKG->get_reporting_data($deploy_id, 'bounces');
		echo $MKG->render_reporting_grid('bounces', $bounces_data);
	?>
	</div>
</div>
</div>
<?php
} else {
	?><div class="alert alert-info" style="margin:0 2.2rem;">Reports are unavailable because this deployment has not run yet.</div><?php
}
?>