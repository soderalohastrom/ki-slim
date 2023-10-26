<?php
include_once("class.dashboard.php");
include_once("class.record.php");
include_once("class.users.php");
include_once("class.encryption.php");
$RECORD = new Record($DB);
$ENC = new encryption();
$DASHBOARD = new Dashboard($DB, $RECORD, $ENC);
$USER = new Users($DB);
$USER_PERMS = $USER->get_userPermissions($_SESSION['system_user_id']);

function bw_check($value_1, $value_2, $textonly = false)
{
	if ($textonly) :
		return ($value_1 - $value_2);
	else :
		ob_start();
		if ($value_1 > $value_2) :
?><span class="m-badge m-badge--success m-badge--wide"><?php echo ($value_1 - $value_2) ?></span><?php
																										elseif ($value_1 == $value_2) :
																											?><span class="m-badge m-badge--default m-badge--wide"><?php echo ($value_1 - $value_2) ?></span><?php
																										else :
																											?><span class="m-badge m-badge--danger m-badge--wide"><?php echo ($value_1 - $value_2) ?></span><?php
																										endif;
																										return ob_get_clean();
																									endif;
																								}

																											?>
<script src="//maps.google.com/maps/api/js?key=AIzaSyBE3dc8SCYrTsKHAL2o7HwC9uhjoYIKeKE" type="text/javascript"></script>
<script src="/assets/vendors/custom/gmaps/gmaps.js" type="text/javascript"></script>
<style>
	.infoTitle {
		font-weight: boldest;
		font-size: 1.2em;
	}

	.info-block {
		width: 200px;
	}
</style>
<!-- BEGIN: Subheader -->
<?php echo $PAGE->render_PageSubHeader("Dashboard", "flaticon-line-graph", array(array('text' => 'All Offices', 'link' => '')), 'Please check out the KISS system guide '); ?>
<!-- END: Subheader -->
	
	<div class="m-content">
		<div class="row">	
			<div class="col-md-6 col-lg-2 col-xl-2 col-xxl-2 mb-md-5 mb-xl-10">
				<?php echo $DASHBOARD->widget_salesbyuser($_SESSION['system_user_id']); ?>
			</div>
			<?php if (in_array(86, $USER_PERMS)) : ?>
				<div class="col-xl-5">
					<?php echo $DASHBOARD->widget_LeadTrends(); ?>
				</div>
				<div class="col-xl-2">
					<?php echo $DASHBOARD->widget_quickNav(); ?>
				</div>
			<?php endif; ?>
		</div>

	<?php if (in_array(91, $USER_PERMS)) : ?>
		<?php $DASHBOARD->widget_DBoverview(); ?>
	<?php endif; ?>

	<?php //if(in_array(92, $USER_PERMS)): 
	?>
	<?php //$DASHBOARD->widget_GeoOverview(); 
	?>
	<?php //endif; 
	?>

	<?php if (in_array(6, $USER_PERMS)) : ?>
		<div class="row">
			<div class="col-xl-12 col-lg-12">
				<?php $DASHBOARD->datatable_NewestLeadsAlt(); ?>
				<?php $DASHBOARD->datatable_ReviviedLeadsAlt(); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if (in_array(81, $USER_PERMS)) : ?>
		<div class="row">
			<div class="col-xl-12 col-lg-12">
				<?php echo $DASHBOARD->panel_matchmakers($_SESSION['system_user_id']); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if (in_array(7, $USER_PERMS)) : ?>
		<div class="row">
			<div class="col-xl-5 col-lg-6">
				<?php $DASHBOARD->data_newestMatchers(); ?>
			</div>
			<div class="col-xl-7 col-lg-6">
				<?php $DASHBOARD->chart_RecordDistribution(); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="row">
		<div class="col-xl-12 col-lg-12">
			<?php $DASHBOARD->calendar_MyCalendar(); ?>
		</div>
	</div>

	<!--
    <div class="m-portlet__body  m-portlet__body--no-padding">
    	<div class="m-portlet">
            <div class="row m-row--no-padding m-row--col-separator-xl">
                <div class="col-xl-6">
                    <?php //$DASHBOARD->stats_RecentActivity(); 
					?>
                </div>
                <div class="col-xl-6">
                    <?php //$DASHBOARD->chart_LeadTrends(); 
					?>
                </div>
            </div>
		</div>
    </div>
-->
</div>
<?php
include_once("includes/ringcentral.php");
?>
<script>
	$(document).ready(function(e) {
		document.title = <?php echo json_encode("DASHBOARD - (KISS) Kelleher International Software System") ?>;
		$('.rolling-alert').each(function() {
			$(this).hide();
		});
		setTimeout(function() {
			rollToMessage(0);
		}, 1500);
	});

	function rollToMessage(value) {
		var myCount = 0;
		var value_2 = value + 1;
		var rollCount = 0;
		var totalCount = $('.rolling-alert').length;
		console.log('Total Found:' + totalCount + '|MyCount:' + value);
		$('.rolling-alert').each(function() {
			$(this).fadeOut('fast', function() {
				rollCount++;
				if (rollCount == totalCount) {
					$('.rolling-alert').each(function() {
						if (myCount == value) {
							$(this).fadeIn('fast');
						}
						myCount++;
					});
				}
			});
		});
		if (value_2 >= totalCount) {
			value_2 = 0;
		}
		setTimeout(function() {
			rollToMessage(value_2);
		}, 10000);
	}
</script>