<?php
if($data['MarketingDeployments_status'] != 'Pending' && $data['MarketingDeployments_status'] != 'Scheduled') { 
$stats = $MKG->get_deployment_stats($deploy_id);
$unopened = $stats['total'] - $stats['opens_unique'] - $stats['bounces'];
?>
<script src="//www.amcharts.com/lib/3/amcharts.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/serial.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/radar.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/pie.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/plugins/tools/polarScatter/polarScatter.min.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/plugins/animate/animate.min.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/plugins/export/export.min.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/themes/light.js" type="text/javascript"></script>
<div class="row" style="padding:0 2.2rem;">
<div class="col-12">
	<!--begin::Portlet-->
	<div class="m-portlet m-portlet--brand m-portlet--head-solid-bg">
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<span class="m-portlet__head-icon">
						<i class="la la-bar-chart"></i>
					</span>
					<h3 class="m-portlet__head-text">Deployment Statistics <small><?php echo $data['MarketingDeployments_name']?></small></h3>
				</div>
			</div>
		</div>
		<div class="m-portlet__body">
			<div id="m_amcharts_1" style="height: 500px;"></div>
		</div>
	</div>
	<!--end::Portlet-->
</div>
</div>
<div class="row" style="padding:0 2.2rem;">
<div class="col-12">
	<!--begin::Portlet-->
	<div class="m-portlet m-portlet--brand m-portlet--head-solid-bg">
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<span class="m-portlet__head-icon">
						<i class="la la-pie-chart"></i>
					</span>
					<h3 class="m-portlet__head-text">Delivery Breakdown <small><?php echo $data['MarketingDeployments_name']?></small></h3>
				</div>
			</div>
		</div>
		<div class="m-portlet__body">
			<div id="m_amcharts_2" style="height: 500px;"></div>
		</div>
	</div>
	<!--end::Portlet-->
</div>
</div>
<script>
function loadDeploymentCharts_Delay() {
	setTimeout(function() {
		loadDeploymentCharts();	
	}, 100);
}
function loadDeploymentCharts() {
	var chart = AmCharts.makeChart("m_amcharts_1", {
		"theme": "light",
		"type": "serial",
		"dataProvider": [{
			"category": "Total Recipients",
			"stat": <?php echo $stats['total']?>
		}, {
			"category": "Total Opens",
			"stat": <?php echo $stats['opens']?>
		}, {
			"category": "Unique Opens",
			"stat": <?php echo $stats['opens_unique']?>
		}, {
			"category": "Clicks",
			"stat": <?php echo $stats['clicks']?>
		}, {
			"category": "Bounces",
			"stat": <?php echo $stats['bounces']?>
		}],
		"graphs": [{
			"balloonText": "[[category]]: <b>[[value]]</b>",
			"fillAlphas": 1,
			"lineAlpha": 0.2,
			"title": "Number",
			"type": "column",
			"valueField": "stat"
		}],
		"depth3D": 20,
		"angle": 30,
		"rotate": true,
		"categoryField": "category",
		"categoryAxis": {
			"gridPosition": "start",
			"fillAlpha": 0.05,
			"position": "left"
		},
		"export": {
			"enabled": false
		 }
	});

	var chart2 = AmCharts.makeChart( "m_amcharts_2", {
	  "type": "pie",
	  "theme": "light",
	  "dataProvider": [{
		"category": "Delivered, Unopened",
		"stat": <?php echo $unopened?>
	  }, {
		"category": "Not Delivered",
		"stat": <?php echo $stats['bounces']?>
	  }, {
		"category": "Delivered, Opened",
		"stat": <?php echo $stats['opens_unique']?>
	  } ],
	  "valueField": "stat",
	  "titleField": "category",
	  "outlineAlpha": 0.4,
	  "depth3D": 15,
	  "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
	  "angle": 30,
	  "export": {
		"enabled": false
	  }
	} );
}
</script>
<?php
} else {
	?><div class="alert alert-info" style="margin:0 2.2rem;">Charts are unavailable because this deployment has not run yet.</div><?php
}
?>