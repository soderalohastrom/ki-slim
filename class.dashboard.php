<?php
/*! \class Dashboard class.dashbaord.php "class.dashbaord.php"
 *  \brief This class is used to render the dashboard elements.
 */
class Dashboard
{
	/*! \fn obj __constructor($DB)
		\brief Dashboard class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB, $RECORD, $ENC = '-1')
	{
		$this->db 		= $DB;
		$this->record	= $RECORD;
		$this->enc		= $ENC;
	}

	function panel_matchmakers($user_id)
	{
		$startepoch 	= time();
		$_POST['StartDate'] = date("m/d/Y", mktime(0, 0, 0, date("m"), 1, date("Y")));
		$_POST['EndDate'] = date("m/d/Y", mktime(0, 0, 0, date("m"), date("t"), date("Y")));
		$startepoch 	= strtotime($_POST['StartDate']);

		$month_floor	= mktime(0, 0, 0, date("m", $startepoch), 1, date("Y", $startepoch));
		$month_peak 	= mktime(23, 59, 59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch));

		$ytd_floor		= mktime(0, 0, 0, 1, 1, date("Y", $startepoch));
		$ytd_peak		= mktime(23, 59, 59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch));

		$mm_as_sql = "SELECT count(*) as count FROM Persons WHERE PersonsTypes_id='4' AND Matchmaker_id='" . $user_id . "'";
		$mm_as_snd = $this->db->get_single_result($mm_as_sql);

		$mm_as2_sql = "SELECT count(*) as count FROM Persons WHERE PersonsTypes_id='4' AND Matchmaker2_id='" . $user_id . "'";
		$mm_as2_snd = $this->db->get_single_result($mm_as2_sql);

		$mm_fas_sql = "SELECT count(*) as count FROM Persons WHERE PersonsTypes_id='6' AND Matchmaker_id='" . $user_id . "'";
		$mm_fas_snd = $this->db->get_single_result($mm_fas_sql);

		$mm_fas2_sql = "SELECT count(*) as count FROM Persons WHERE PersonsTypes_id='6' AND Matchmaker2_id='" . $user_id . "'";
		$mm_fas2_snd = $this->db->get_single_result($mm_fas2_sql);

		$mm_cdm_sql = "SELECT count(*) as count FROM PersonsDates WHERE PersonsDates_assignedTo='" . $user_id . "' AND PersonsDates_isComplete='1' AND (PersonsDates_dateExecuted >= '" . $month_floor . "' AND PersonsDates_dateExecuted <= '" . $month_peak . "')";
		$mm_cdm_snd = $this->db->get_single_result($mm_cdm_sql);

		$mm_cdy_sql = "SELECT count(*) as count FROM PersonsDates WHERE PersonsDates_assignedTo='" . $user_id . "' AND PersonsDates_isComplete='1' AND (PersonsDates_dateExecuted >= '" . $ytd_floor . "' AND PersonsDates_dateExecuted <= '" . $ytd_peak . "')";
		$mm_cdy_snd = $this->db->get_single_result($mm_cdy_sql);

		 $mm_dist_stat_sql = "SELECT DISTINCT(B.Color_id) as clr_id FROM Persons A JOIN PersonsFlags B ON A.Person_Id = B.Person_Id WHERE PersonsTypes_id='4' AND Matchmaker_id='" . $user_id . "' AND B.Color_id NOT IN (21, 42, 43, 44, 45)";
		 $mm_dist_stat_snd = $this->db->get_multi_result($mm_dist_stat_sql);

		 ob_start();
		 foreach ($mm_dist_stat_snd as $mm_dist_dta) :
		 	$mm_stat_sql = "SELECT * FROM PersonsColors WHERE Color_id='" . $mm_dist_dta['clr_id'] . "'";
		 	$mm_stat_dta = $this->db->get_single_result($mm_stat_sql);

		 	$mm_stat_count_sql = "SELECT COUNT(*) as count FROM Persons A JOIN PersonsFlags B ON A.Person_Id = B.Person_Id WHERE PersonsTypes_id='4' AND Matchmaker_id='" . $user_id . "' AND B.Color_id='" . $mm_dist_dta['clr_id'] . "'";
		 	$mm_stat_count_snd = $this->db->get_single_result($mm_stat_count_sql);
		 	?>
		<div class="row">
		 		<div class="col-sm-8">
		 			<span class="m-badge m-badge--metal m-badge--wide" style="background-color:<?php echo $mm_stat_dta['Color_hex'] ?>; cursor:pointer;" onclick="getMM_table('<?php echo $user_id ?>', '4', '<?php echo $mm_dist_dta['clr_id'] ?>', 1, 0)"><?php echo $mm_stat_dta['Color_title'] ?></span>
		 		</div>
		 		<div class="col-sm-4"><?php echo $mm_stat_count_snd['count'] ?></div>
		 	</div> 
			<?php
		endforeach;
	    $statusStats = ob_get_clean();

		ob_start();
		$int_s_sql = "SELECT * FROM DropDown_DateStatus WHERE Date_status NOT IN (101, 102, 5, 99)";
		$int_s_snd = $this->db->get_multi_result($int_s_sql);
		foreach ($int_s_snd as $status_dta) :
			$mm_s_sql = "SELECT count(*) as count FROM PersonsDates WHERE PersonsDates_assignedTo='" . $user_id . "' AND PersonsDates_status='" . $status_dta['Date_status'] . "' AND PersonsDates_isComplete='0'";
			$mm_s_snd = $this->db->get_single_result($mm_s_sql);
			if ($mm_s_snd['count'] > 0) :
			?>
				<div class="row">
					<div class="col-sm-8">
						<span class="m-badge m-badge--<?php echo $status_dta['kimsClass'] ?> m-badge--wide" style="cursor:pointer;" onclick="getMMINTRO_table('<?php echo $user_id ?>', '<?php echo $status_dta['Date_status'] ?>')"><?php echo $status_dta['Date_statusText'] ?></span>
					</div>
					<div class="col-sm-4"><?php echo $mm_s_snd['count'] ?></div>
				</div>
		<?php
			endif;
		endforeach;
		$introBlocks = ob_get_clean();


		?>
		<script src="/assets/vendors/custom/tablesorter/dist/js/jquery.tablesorter.min.js" type="text/javascript"></script>
		<link href="/assets/vendors/custom/tablesorter/dist/css/theme.bootstrap_4.min.css" rel="stylesheet" type="text/css" />
		<style>
			#mm_report_table .m-blockui {
				margin-top: 0%;
				margin-left: 30%;
			}
		</style>
		<div class="row">
			<div class="col-12">

				<div class="m-portlet m-portlet--head-sm" data-portlet="true" id="m_portlet_tools_1">
					<div class="m-portlet__head">
						<div class="m-portlet__head-caption">
							<div class="m-portlet__head-title">
								<span class="m-portlet__head-icon">
									<i class="la la-heart-o"></i>
								</span>
								<h3 class="m-portlet__head-text">
									MM Overview
									<small>
										<?php echo $this->record->get_FulluserName($user_id) ?>
									</small>
								</h3>
							</div>
						</div>
						<div class="m-portlet__head-tools">
							<ul class="m-portlet__nav">
								<li class="m-portlet__nav-item">
									<input type="search" class="light-table-filter form-control form-control-sm m-input" id="generalSearch" data-table="statsTable" placeholder="Filter" disabled>
								</li>
								<li class="m-portlet__nav-item">
									<a href="javascript:clearMMTableData(<?php echo $user_id ?>);" class="m-portlet__nav-link m-portlet__nav-link--icon" title="clear table data">
										<i class="la la-ban"></i>
									</a>
								</li>
								<li class="m-portlet__nav-item">
									<a href="" data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Collapse">
										<i class="la la-angle-down"></i>
									</a>
								</li>
								<!--
                                <li class="m-portlet__nav-item">
                                    <a href="" data-portlet-tool="reload" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Reload">
                                        <i class="la la-refresh"></i>
                                    </a>
                                </li>
                                <li class="m-portlet__nav-item">
                                    <a href="#" data-portlet-tool="fullscreen" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Fullscreen">
                                        <i class="la la-expand"></i>
                                    </a>
                                </li>
                                <li class="m-portlet__nav-item">
                                    <a href="#" data-portlet-tool="remove" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Remove">
                                        <i class="la la-close"></i>
                                    </a>
                                </li>
                                -->
							</ul>
						</div>
					</div>
					<div class="m-portlet__body">
						<div class="row">
							<div class="col-3">
								<dl class="row">
									<dt class="col-sm-8"><a href="javascript:;" onclick="getMM_dates_table('<?php echo $user_id ?>', '4', '', 1, 0)" class="m-link" data-toggle="m-tooltip" title="" data-original-title="list of records with completed dates for the month">Completed Dates (month)</a></dt>
									<dd class="col-sm-4"><?php echo $mm_cdm_snd['count'] ?></dd>
									<dt class="col-sm-8"><a href="javascript:;" onclick="getMM_dates_table('<?php echo $user_id ?>', '4', '', 1, 0)" class="m-link" data-toggle="m-tooltip" title="" data-original-title="list of records with completed dates year to date">Completed Dates (YTD)</a></dt>
									<dd class="col-sm-4"><?php echo $mm_cdy_snd['count'] ?></dd>
								</dl>
							</div>
							<div class="col-3">
								<dl class="row">
									<dt class="col-sm-8"><a href="javascript:;" onclick="getMM_table('<?php echo $user_id ?>', '4', '', 1, 0)" class="m-link" data-toggle="m-tooltip" title="" data-original-title="list of records where you are the relationship manager">Active (Relationship Manager)</a></dt>
									<dd class="col-sm-4"><?php echo $mm_as_snd['count'] ?></dd>

									<dt class="col-sm-8"><a href="javascript:;" onclick="getMM_table('<?php echo $user_id ?>', '4', '', 0, 1)" class="m-link" data-toggle="m-tooltip" title="" data-original-title="list of records where you are the network developer">Active (Network Developer)</a></dt>
									<dd class="col-sm-4"><?php echo $mm_as2_snd['count'] ?></dd>

									<dt class="col-sm-8"><a href="javascript:;" onclick="getMM_table('<?php echo $user_id ?>', '6', '', 1, 0)" class="m-link" data-toggle="m-tooltip" title="" data-original-title="list of frozen records where you are the relationship manager">Frozen (Relationship Manager)</a></dt>
									<dd class="col-sm-4"><?php echo $mm_fas_snd['count'] ?></dd>

									<dt class="col-sm-8"><a href="javascript:;" onclick="getMM_table('<?php echo $user_id ?>', '6', '', 0, 1)" class="m-link" data-toggle="m-tooltip" title="" data-original-title="list of frozen records where you are the network developer">Frozen (Network Developer)</a></dt>
									<dd class="col-sm-4"><?php echo $mm_fas2_snd['count'] ?></dd>
								</dl>
							</div>
							<div class="col-3">
									<?php echo $statusStats ?>
							</div>
							<div class="col-3">
									<?php echo $introBlocks ?>
							</div>
						</div>
						<div id="mm_report_table"></div>
					</div>
				</div>

			</div>
		</div>
		<script>
			(function(document) {
				'use strict';

				var LightTableFilter = (function(Arr) {

					var _input;

					function _onInputEvent(e) {
						_input = e.target;
						var tables = document.getElementsByClassName(_input.getAttribute('data-table'));
						Arr.forEach.call(tables, function(table) {
							Arr.forEach.call(table.tBodies, function(tbody) {
								Arr.forEach.call(tbody.rows, _filter);
							});
						});
					}

					function _filter(row) {
						var text = row.textContent.toLowerCase(),
							val = _input.value.toLowerCase();
						row.style.display = text.indexOf(val) === -1 ? 'none' : 'table-row';
					}

					return {
						init: function() {
							var inputs = document.getElementsByClassName('light-table-filter');
							Arr.forEach.call(inputs, function(input) {
								input.oninput = _onInputEvent;
							});
						}
					};
				})(Array.prototype);

				document.addEventListener('readystatechange', function() {
					if (document.readyState === 'complete') {
						LightTableFilter.init();
					}
				});
				clearMMTableData(<?php echo $user_id ?>);

			})(document);

			function getMMINTRO_table(UserID, IntroStatus) {
				$('#mm_report_table').html('<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>');
				mApp.block("#mm_report_table", {
					overlayColor: "#000000",
					type: "loader",
					state: "success",
					message: "Loading Custom MM Table..."
				});
				$.post('/ajax/ajax.dashboard.php?action=intro_dashboard_table', {
					User_id: UserID,
					PersonsDates_status: IntroStatus
				}, function(data) {
					//console.log(data.sql);
					//console.log(data.sql);
					$('#mm_report_table').html(data.table);
					$(".statsTable").tablesorter();
					mApp.unblock("#mm_report_table");
					$('.light-table-filter').attr('disabled', false);
				}, "json");
			}

			function getMM_table(UserID, PersonsTypes_id, Color_id, PrimaryMM, SecondaryMM) {
				$('#mm_report_table').html('<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>');
				mApp.block("#mm_report_table", {
					overlayColor: "#000000",
					type: "loader",
					state: "success",
					message: "Loading Custom MM Table..."
				});
				$.post('/ajax/ajax.dashboard.php?action=mm_dashboard_table', {
					User_id: UserID,
					PersonsTypes_id: PersonsTypes_id,
					Color_id: Color_id,
					PrimaryMM: PrimaryMM,
					SecondaryMM: SecondaryMM
				}, function(data) {
					console.log(data.sql);
					$('#mm_report_table').html(data.table);
					$(".statsTable").tablesorter();
					mApp.unblock("#mm_report_table");
					$('.light-table-filter').attr('disabled', false);
				}, "json");
			}

			function getMM_dates_table(UserID, PersonsTypes_id, Color_id, PrimaryMM, SecondaryMM) {
				$('#mm_report_table').html('<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>');
				mApp.block("#mm_report_table", {
					overlayColor: "#000000",
					type: "loader",
					state: "success",
					message: "Loading Custom MM Table..."
				});
				$.post('/ajax/ajax.dashboard.php?action=mm_dashboard_table', {
					User_id: UserID,
					PersonsTypes_id: PersonsTypes_id,
					Color_id: Color_id,
					PrimaryMM: PrimaryMM,
					SecondaryMM: SecondaryMM
				}, function(data) {
					console.log(data.sql);
					$('#mm_report_table').html(data.table);
					$(".statsTable").tablesorter();
					mApp.unblock("#mm_report_table");
					$('.light-table-filter').attr('disabled', false);
				}, "json");
			}

			function clearMMTableData(UserID) {
				$('.light-table-filter').attr('disabled', true);
				$('#mm_report_table').html('<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>');
				mApp.block("#mm_report_table", {
					overlayColor: "#000000",
					type: "loader",
					state: "success",
					message: "Loading Custom MM Table..."
				});
				$.post('/ajax/ajax.dashboard.php?action=intro_dashboard_last_intros', {
					User_id: UserID
				}, function(data) {
					//console.log(data.sql);
					$('#mm_report_table').html(data.table);
					//$(".statsTable").tablesorter();	
					mApp.unblock("#mm_report_table");
					$('.light-table-filter').attr('disabled', false);

					$("#LastIntroRecords").mDatatable({
						search: {
							input: $("#generalSearch")
						},
						columns: [{
							field: "Matches",
							type: "number"
						}, {
							field: "#",
							width: 30
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
				}, "json");
			}
		</script>
	<?php
	}

	/*! \fn obj chart_RecordDistribution()
		\brief Generates a table (pie) of the record distribution.
		\return HTML
	*/
	function chart_RecordDistribution()
	{
		$sql = "SELECT * FROM PersonTypes WHERE PersonsTypes_id IN (4, 6, 7, 8, 10, 12)";
		$snd = $this->db->get_multi_result($sql);
		foreach ($snd as $dta) :
			$count_sql = "SELECT COUNT(*) as count FROM Persons WHERE PersonsTypes_id='" . $dta['PersonsTypes_id'] . "'";
			$count_snd = $this->db->get_single_result($count_sql);
			$chartArray[] = array(
				'label'	=>	$dta['PersonsTypes_text'],
				'value'	=>	$count_snd['count']
			);
			$chartColors[] = 'mUtil.getColor("' . $dta['PersonsTypes_color'] . '")';
			$chartArrayList[] = array(
				'label'	=>	$dta['PersonsTypes_text'],
				'value'	=>	$count_snd['count'],
				'color'	=>	$dta['PersonsTypes_color']
			);
		endforeach;
		$chartData = json_encode($chartArray);
	?>
		<div class="m-portlet ">
			<div class="m-portlet__body">

				<!--begin:: Widgets/Revenue Change-->
				<div class="m-widget14">
					<div class="m-widget14__header">
						<h3 class="m-widget14__title">
							Record Type Distribution
						</h3>
						<span class="m-widget14__desc">
							Breakdown of Clients based on Type
						</span>
					</div>
					<div class="row  align-items-center">
						<div class="col">
							<div id="m_chart_revenue_change" class="m-widget14__chart1" style="height: 300px"></div>
						</div>
						<div class="col">
							<div class="m-widget14__legends">
								<?php foreach ($chartArrayList as $chartVal) : ?>
									<div class="m-widget14__legend">
										<span class="m-widget14__legend-bullet m--bg-<?php echo $chartVal['color'] ?>"></span>
										<span class="m-widget14__legend-text">
											<?php echo number_format($chartVal['value'], 0) ?> <?php echo $chartVal['label'] ?>
										</span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
				<!--end:: Widgets/Revenue Change-->

			</div>
		</div>
		<script>
			var recordTypeChart = function() {
				0 != $("#m_chart_revenue_change").length && Morris.Donut({
					element: "m_chart_revenue_change",
					data: <?php echo $chartData ?>,
					colors: [<?php echo implode(",", $chartColors) ?>]
				})
			};
			$(document).ready(function(e) {
				recordTypeChart();
			});
		</script>
	<?php
	}

	/*! \fn obj chart_LeadTrends()
		\brief Generates a table (line) of the lead trends.
		\return HTML
	*/
	function chart_LeadTrends()
	{
	?>
		<div class="m-widget14">
			<div class="m-widget14__header m--margin-bottom-30">
				<h3 class="m-widget14__title">Inbound Lead Trends</h3>
				<span class="m-widget14__desc">Check out each column for more details</span>
			</div>
			<div class="m-widget14__chart" style="height:250px;">
				<div class="chartjs-size-monitor" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;">
					<div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:auto;pointer-events:none;visibility:hidden;z-index:-1;">
						<div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div>
					</div>
					<div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:auto;pointer-events:none;visibility:hidden;z-index:-1;">
						<div style="position:absolute;width:200%;height:200%;left:0; top:0"></div>
					</div>
				</div>
				<canvas id="m_chart_daily_sales"></canvas>
			</div>
		</div>

		<script>
			var leadTrendChart = $("#m_chart_daily_sales");
			if (0 != leadTrendChart.length) new Chart(leadTrendChart, {
				type: "bar",
				data: {
					labels: ["Jan", "Feb", "Mar", "Apr", "May", "June", "July", "Aug", "Sept", "Oct", "Nov", "Dec"],
					datasets: [{
						label: "New Leads",
						data: [12, 15, 20, 12, 17, 5, 9, 10, 11, 12, 18, 22],
						backgroundColor: mUtil.getColor("success")
					}, {
						label: "Sales",
						data: [5, 2, 12, 8, 2, 1, 2, 4, 6, 5, 2, 12],
						backgroundColor: '#f3f3fb'
					}],
				},
				options: {
					title: {
						display: !1
					},
					tooltips: {
						intersect: !1,
						mode: "nearest",
						xPadding: 10,
						yPadding: 10,
						caretPadding: 10
					},
					legend: {
						display: !1
					},
					responsive: !0,
					maintainAspectRatio: !1,
					barRadius: 4,
					scales: {
						xAxes: [{
							display: !1,
							gridLines: !1,
							stacked: !0
						}],
						yAxes: [{
							display: !1,
							stacked: !0,
							gridLines: !1
						}]
					},
					layout: {
						padding: {
							left: 0,
							right: 0,
							top: 0,
							bottom: 0
						}
					}
				}
			});
		</script>
	<?php
	}

	/*! \fn obj stats_RecentActivity()
		\brief Generates a stats portlet of the system records overview
		\return HTML
	*/
	function stats_RecentActivity()
	{
	?>
		<!--begin:: Widgets/Stats2-1 -->
		<div class="m-widget1">
			<div class="m-widget1__item">
				<div class="row m-row--no-padding align-items-center">
					<div class="col">
						<h3 class="m-widget1__title">New Clients</h3>
						<span class="m-widget1__desc">Clients added this month</span>
					</div>
					<div class="col m--align-right">
						<span class="m-widget1__number m--font-primary">4</span>
					</div>
				</div>
			</div>
			<div class="m-widget1__item">
				<div class="row m-row--no-padding align-items-center">
					<div class="col">
						<h3 class="m-widget1__title">New Participants</h3>
						<span class="m-widget1__desc">Participants added this month</span>
					</div>
					<div class="col m--align-right">
						<span class="m-widget1__number m--font-warning">8</span>
					</div>
				</div>
			</div>
			<div class="m-widget1__item">
				<div class="row m-row--no-padding align-items-center">
					<div class="col">
						<h3 class="m-widget1__title">New Resources</h3>
						<span class="m-widget1__desc">Resources added this week</span>
					</div>
					<div class="col m--align-right">
						<span class="m-widget1__number m--font-brand">15</span>
					</div>
				</div>
			</div>
			<div class="m-widget1__item">
				<div class="row m-row--no-padding align-items-center">
					<div class="col">
						<h3 class="m-widget1__title">New Free Members</h3>
						<span class="m-widget1__desc">Free Members added this week</span>
					</div>
					<div class="col m--align-right">
						<span class="m-widget1__number m--font-metal">14</span>
					</div>
				</div>
			</div>
			<div class="m-widget1__item">
				<div class="row m-row--no-padding align-items-center">
					<div class="col">
						<h3 class="m-widget1__title">New Introductions</h3>
						<span class="m-widget1__desc">Introductions created this week</span>
					</div>
					<div class="col m--align-right">
						<span class="m-widget1__number m--font-danger">35</span>
					</div>
				</div>
			</div>
		</div>
		<!--end:: Widgets/Stats2-1 -->
	<?php
	}


	/*! \fn obj datatable_NewestLeads()
		\brief Generates a table for the 40 most recent Leads into table
		\return HTML
	*/
	function datatable_NewestLeads()
	{
	?>
		<div class="m-portlet m-portlet--mobile ">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<h3 class="m-portlet__head-text"><i class="flaticon-users"></i> Newest Leads</h3>
					</div>
				</div>
				<div class="m-portlet__head-tools">
					<ul class="m-portlet__nav">
						<li class="m-portlet__nav-item"></li>
					</ul>
				</div>
			</div>
			<div class="m-portlet__body">
				<!--begin: Datatable -->
				<div class="m_datatable" id="m_datatable_latest_leads"></div>
				<!--end: Datatable -->
			</div>
		</div>
		<script>
			var datatable;
			var table_options = {
				data: {
					type: 'remote',
					source: {
						read: {
							url: '/ajax/datatable.newestleads.php',
							method: 'POST',
							params: {
								// custom query params
								query: {
									MAX: 40,
									EmployeeID: <?php echo $_SESSION['system_user_id'] ?>
								}
							},
							map: function(raw) {
								// sample data mapping
								var dataSet = raw;
								if (typeof raw.data !== 'undefined') {
									dataSet = raw.data;
								}
								return dataSet;
							},
						}
					},
					order: [
						[0, 'desc']
					],
					pageSize: 10,
					saveState: {
						cookie: false,
						webstorage: false
					},
					serverPaging: true,
					serverFiltering: true,
					serverSorting: true
				},
				layout: {
					theme: 'default',
					class: '',
					scroll: !0,
					height: 380,
					footer: true,
				},
				filterable: true,
				pagination: true,
				sortable: false,

				// columns definition
				columns: [{
					field: "FormatedDateCreated",
					title: "Date Created",
					width: 100,
					textAlign: "center"
				}, {
					field: "FirstName",
					title: "First Name",
					filterable: true,
					width: 150,
					overflow: 'visible'
				}, {
					field: "LastName",
					title: "Last Name",
					width: 150,
					overflow: 'visible'
				}, {
					field: "Email",
					title: "Email",
					width: 150,
					overflow: 'visible'
				}, {
					field: "City",
					title: "Location",
					width: 150,
					overflow: 'hidden',
					template: function(row) {
						return row.City + ', ' + row.State
					}
				}, {
					field: "Actions",
					width: 110,
					title: "Actions",
					overflow: "visible",
					template: function(e) {
						var row_actions = '<a href="/profile/' + e.Person_id + '" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View Record"><i class="la la-edit"></i></a>';
						//row_actions += '<a href="#" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete Record"><i class="la la-trash"></i></a>';					
						return row_actions;
					}
				}],
				toolbar: {
					layout: ['pagination', 'info'],
					items: {
						pagination: {
							pageSizeSelect: [10, 20]
						},
						info: true
					}
				}
			};
			datatable = $('#m_datatable_latest_leads').mDatatable(table_options);
		</script>
	<?php
	}

	function datatable_NewestLeadsAlt()
	{
	?>
		<div class="m-portlet m-portlet--mobile ">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<h3 class="m-portlet__head-text"><i class="flaticon-users"></i> Newest Leads</h3>
					</div>
				</div>
				<div class="m-portlet__head-tools">
					<ul class="m-portlet__nav">
						<li class="m-portlet__nav-item"></li>
					</ul>
				</div>
			</div>
			<div class="m-portlet__body">
				<!--begin: Datatable -->
				<div class="m_datatable" id="m_datatable_latest_leads"></div>
				<!--end: Datatable -->
			</div>
		</div>
		<script>
			var datatable;
			var table_options = {
				data: {
					type: 'remote',
					source: {
						read: {
							url: '/ajax/datatable.newestleads.php',
							method: 'POST',
							params: {
								// custom query params
								query: {
									MAX: 40,
									EmployeeID: <?php echo $_SESSION['system_user_id'] ?>
								}
							},
							map: function(raw) {
								// sample data mapping
								var dataSet = raw;
								if (typeof raw.data !== 'undefined') {
									dataSet = raw.data;
								}
								return dataSet;
							},
						}
					},
					order: [
						[0, 'desc']
					],
					pageSize: 10,
					saveState: {
						cookie: false,
						webstorage: false
					},
					serverPaging: true,
					serverFiltering: true,
					serverSorting: true
				},
				layout: {
					theme: 'default',
					class: '',
					scroll: !0,
					height: 380,
					footer: true,
				},
				filterable: true,
				pagination: true,
				sortable: false,

				// columns definition
				columns: [{
					field: "Actions",
					width: 50,
					title: "Action",
					overflow: "visible",
					template: function(e) {
						var row_actions = '<a href="/profile/' + e.Person_id + '" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View Record"><i class="la la-edit"></i></a>';
						//row_actions += '<a href="#" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete Record"><i class="la la-trash"></i></a>';					
						return row_actions;
					}
				}, {
					field: "FullName",
					title: "Name",
					filterable: true,
					width: 125,
					overflow: 'visible',
					template: function(e) {
						var row_name = e.FirstName + ' ' + e.LastName;
						return row_name;
					}
				}, {
					field: "DateOfBirth",
					title: "Age",
					width: 25,
					overflow: 'visible'
				}, {
					field: "Gender",
					title: "Gender",
					width: 25,
					overflow: 'visible'
				}, {
					field: "location",
					title: "Location",
					width: 125,
					overflow: 'hidden'
				}, {
					field: "prQuestion_621",
					title: "Height",
					width: 50,
					overflow: 'visible'
				}, {
					field: "prQuestion_622",
					title: "Weight",
					width: 75,
					overflow: 'visible'
				}, {
					field: "prQuestion_631",
					title: "Income",
					width: 100,
					overflow: 'visible'
				}, {
					field: "prQuestion_1713",
					title: "Lead State",
					width: 100,
					overflow: 'visible'
				}, {
					field: "PhoneNumber",
					title: "Phone",
					width: 100,
					overflow: 'visible'
				}, {
					field: "HearAboutUs",
					title: "Source",
					width: 100,
					overflow: 'visible'
				}, {
					field: "FormatedDateCreated",
					title: "Date Created",
					width: 100,
					textAlign: "center"
				}, {
					field: "Salesperson",
					title: "Assigned",
					width: 100,
					textAlign: "center"
				}, {
					field: "DateUpdated",
					title: "Last Edit",
					width: 100,
					textAlign: "center"
				}],
				toolbar: {
					layout: ['pagination', 'info'],
					items: {
						pagination: {
							pageSizeSelect: [10, 20]
						},
						info: true
					}
				}
			};
			datatable = $('#m_datatable_latest_leads').mDatatable(table_options);
		</script>
	<?php
	}

	function datatable_ReviviedLeadsAlt()
	{
	?>
		<div class="m-portlet m-portlet--mobile ">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<h3 class="m-portlet__head-text"><i class="flaticon-users"></i> Revived Leads</h3>
					</div>
				</div>
				<div class="m-portlet__head-tools">
					<ul class="m-portlet__nav">
						<li class="m-portlet__nav-item"></li>
					</ul>
				</div>
			</div>
			<div class="m-portlet__body">
				<!--begin: Datatable -->
				<div class="m_datatable" id="m_datatable_revived_leads"></div>
				<!--end: Datatable -->
			</div>
		</div>
		<script>
			var rdatatable;
			var rtable_options = {
				data: {
					type: 'remote',
					source: {
						read: {
							url: '/ajax/datatable.reviviedleads.php',
							method: 'POST',
							params: {
								// custom query params
								query: {
									MAX: 40,
									EmployeeID: <?php echo $_SESSION['system_user_id'] ?>
								}
							},
							map: function(raw) {
								// sample data mapping
								var dataSet = raw;
								if (typeof raw.data !== 'undefined') {
									dataSet = raw.data;
								}
								return dataSet;
							},
						}
					},
					order: [
						[0, 'desc']
					],
					pageSize: 10,
					saveState: {
						cookie: false,
						webstorage: false
					},
					serverPaging: true,
					serverFiltering: true,
					serverSorting: true
				},
				layout: {
					theme: 'default',
					class: '',
					scroll: !0,
					height: 380,
					footer: true,
				},
				filterable: true,
				pagination: true,
				sortable: false,

				// columns definition
				columns: [{
					field: "Actions",
					width: 50,
					title: "Action",
					overflow: "visible",
					template: function(e) {
						var row_actions = '<a href="/profile/' + e.Person_id + '" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View Record"><i class="la la-edit"></i></a>';
						//row_actions += '<a href="#" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete Record"><i class="la la-trash"></i></a>';					
						return row_actions;
					}
				}, {
					field: "FullName",
					title: "Name",
					filterable: true,
					width: 125,
					overflow: 'visible',
					template: function(e) {
						var row_name = e.FirstName + ' ' + e.LastName;
						return row_name;
					}
				}, {
					field: "DateOfBirth",
					title: "Age",
					width: 25,
					overflow: 'visible'
				}, {
					field: "Gender",
					title: "Gender",
					width: 25,
					overflow: 'visible'
				}, {
					field: "location",
					title: "Location",
					width: 125,
					overflow: 'hidden'
				}, {
					field: "prQuestion_621",
					title: "Height",
					width: 50,
					overflow: 'visible'
				}, {
					field: "prQuestion_622",
					title: "Weight",
					width: 75,
					overflow: 'visible'
				}, {
					field: "prQuestion_631",
					title: "Income",
					width: 100,
					overflow: 'visible'
				}, {
					field: "HearAboutUs",
					title: "Source",
					width: 100,
					overflow: 'visible'
				}, {
					field: "FormatedDateCreated",
					title: "Date Created",
					width: 100,
					textAlign: "center"
				}, {
					field: "FormSubmitted",
					title: "Date Updated",
					width: 100,
					textAlign: "center"
				}, {
					field: "Salesperson",
					title: "Assigned",
					width: 100,
					textAlign: "center"
				}, {
					field: "DateUpdated",
					title: "Last Edit",
					width: 100,
					textAlign: "center"
				}, {
					field: "PersonsTypes_text",
					title: "Type",
					width: 100
				}],
				toolbar: {
					layout: ['info'],
					items: {
						info: true
					}
				}

			};
			rdatatable = $('#m_datatable_revived_leads').mDatatable(rtable_options);
		</script>
		<?php
	}

	/*! \fn obj data_newestMatchers()
		\brief Generates a table of the newerst clients and participating records
		\return HTML
	*/
	function data_newestMatchers()
	{
		$sql = "
		SELECT 
			Persons.*,
			Addresses.City,
			Addresses.State,
			Addresses.Country,
			PersonsImages.PersonsImages_path 
		FROM 
			Persons 
			INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id 
			LEFT JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'
			LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
		WHERE 
			PersonsProfile.prQuestion_676 != '' 
		AND 
			Persons.PersonsTypes_id IN (4, 7)
		GROUP BY
			Persons.Person_id
		ORDER BY 
			prQuestion_676 DESC 
		LIMIT 5";
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		foreach ($snd as $dta) :
			if ($dta['PersonsImages_path'] == '') {
				$imgPath = $this->record->get_defaultImage($dta['Person_id']);
			} else {
				$imgPath = "/client_media/" . $this->record->get_image_directory($dta['Person_id']) . "/" . $dta['Person_id'] . "/" . $dta['PersonsImages_path'];
			}
		?>
			<div class="m-widget4__item">
				<div class="m-widget4__img m-widget4__img--pic" style="background-image:url('<?php echo $imgPath ?>'); background-size:cover;">
					<img src="/assets/app/media/img/users/filler-large.png" alt="">
				</div>
				<div class="m-widget4__info">
					<span class="m-widget4__title"><?php echo $dta['FirstName'] ?> <?php echo $dta['LastName'] ?> <?php echo $this->record->get_personAge($dta['DateOfBirth']) ?></span><br>
					<span class="m-widget4__sub"><?php echo ((($dta['City'] == '') && ($dta['City'] == '')) ? '<span class="m-badge m-badge--warning m-badge--wide">LOCATION UNKNOWN</span>' : $dta['City'] . ', ' . $dta['State'] . ' ' . $dta['Country']) ?></span>
				</div>
				<div class="m-widget4__ext">
					<a href="/profile/<?php echo $dta['Person_id'] ?>" class="m-btn m-btn--pill m-btn--hover-brand btn btn-sm btn-secondary"><i class="flaticon-profile-1"></i> View</a>
				</div>
			</div>
		<?php
		endforeach;
		$newMembers = ob_get_clean();


		$sql = "
		SELECT 
			Persons.*,
			Addresses.City,
			Addresses.State,
			Addresses.Country,
			PersonsImages.PersonsImages_path 
		FROM 
			Persons 
			INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id 
			LEFT JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'
			LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
		WHERE 
			PersonsProfile.prQuestion_676 != '' 
		AND 
			Persons.PersonsTypes_id IN (12)
		GROUP BY
			Persons.Person_id 
		ORDER BY 
			prQuestion_676 DESC 
		LIMIT 5";
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		foreach ($snd as $dta) :
			if ($dta['PersonsImages_path'] == '') {
				$imgPath = $this->record->get_defaultImage($dta['Person_id']);
			} else {
				$imgPath = "/client_media/" . $this->record->get_image_directory($dta['Person_id']) . "/" . $dta['Person_id'] . "/" . $dta['PersonsImages_path'];
			}
		?>
			<div class="m-widget4__item">
				<div class="m-widget4__img m-widget4__img--pic" style="background-image:url('<?php echo $imgPath ?>'); background-size:cover;">
					<img src="/assets/app/media/img/users/filler-large.png" alt="">
				</div>
				<div class="m-widget4__info">
					<span class="m-widget4__title"><?php echo $dta['FirstName'] ?> <?php echo $dta['LastName'] ?> <?php echo $this->record->get_personAge($dta['DateOfBirth']) ?></span><br>
					<span class="m-widget4__sub"><?php echo ((($dta['City'] == '') && ($dta['City'] == '')) ? '<span class="m-badge m-badge--warning m-badge--wide">LOCATION UNKNOWN</span>' : $dta['City'] . ', ' . $dta['State'] . ' ' . $dta['Country']) ?></span>
				</div>
				<div class="m-widget4__ext">
					<a href="/profile/<?php echo $dta['Person_id'] ?>" class="m-btn m-btn--pill m-btn--hover-brand btn btn-sm btn-secondary"><i class="flaticon-profile-1"></i> View</a>
				</div>
			</div>
		<?php
		endforeach;
		$newParticipating = ob_get_clean();
		?>
		<!--begin:: Widgets/New Users-->
		<div class="m-portlet m-portlet--full-height ">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<h3 class="m-portlet__head-text">
							Newest
						</h3>
					</div>
				</div>
				<div class="m-portlet__head-tools">
					<ul class="nav nav-pills nav-pills--brand m-nav-pills--align-right m-nav-pills--btn-pill m-nav-pills--btn-sm" role="tablist">
						<li class="nav-item m-tabs__item">
							<a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_widget4_tab1_content" role="tab">Clients</a>
						</li>
						<li class="nav-item m-tabs__item">
							<a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget4_tab2_content" role="tab">Participating</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="m-portlet__body">
				<div class="tab-content">
					<div class="tab-pane active" id="m_widget4_tab1_content">
						<div class="m-widget4">
							<?php echo $newMembers ?>
						</div>
					</div>
					<div class="tab-pane" id="m_widget4_tab2_content">
						<div class="m-widget4">
							<?php echo $newParticipating ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--end:: Widgets/New Users-->
	<?php
	}

	function calendar_MyCalendar()
	{
	?>
		<!--begin:: Widgets/My Calendar-->
		<div class="m-portlet" id="m_portlet">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<span class="m-portlet__head-icon">
							<i class="flaticon-time-3"></i>
						</span>
						<h3 class="m-portlet__head-text">
							My Calendar
						</h3>
					</div>
				</div>
			</div>
			<div class="m-portlet__body">
				<div id="m_calendar"></div>
			</div>
		</div>
		<!--end:: Widgets/My Calendar-->
		<script>
			var MyCalendar = {
				init: function() {
					var e = moment().startOf("day"),
						t = e.format("YYYY-MM"),
						i = e.clone().subtract(1, "day").format("YYYY-MM-DD"),
						n = e.format("YYYY-MM-DD"),
						r = e.clone().add(1, "day").format("YYYY-MM-DD");
					$("#m_calendar").fullCalendar({
						header: {
							left: "prev,next today",
							center: "title",
							right: "month,agendaWeek,agendaDay,listWeek"
						},
						editable: !0,
						eventLimit: !0,
						navLinks: !0,
						eventSources: [{
							url: '/ajax/cal_feed.php'
						}, {
							url: '/ajax/cal_matchmakers.php'
						}],
						eventRender: function(e, t) {
							t.hasClass("fc-day-grid-event") ? (t.data("content", e.description), t.data("placement", "top"), mApp.initPopover(t)) : t.hasClass("fc-time-grid-event") ? t.find(".fc-title").append('<div class="fc-description">' + e.description + "</div>") : 0 !== t.find(".fc-list-item-title").lenght && t.find(".fc-list-item-title").append('<div class="fc-description">' + e.description + "</div>")
						}
					})
				}
			};
			$(document).ready(function() {
				MyCalendar.init();
			});
		</script>
	<?php
	}

	function widget_LeadTrends()
	{
		$startepoch 	= time();
		$_POST['StartDate'] = date("m/d/Y", mktime(0, 0, 0, date("m"), 1, date("Y")));
		$_POST['EndDate'] = date("m/d/Y", mktime(0, 0, 0, date("m"), date("t"), date("Y")));

		$month_floor	= mktime(0, 0, 0, date("m", $startepoch), 1, date("Y", $startepoch));
		$month_peak 	= mktime(23, 59, 59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch));
		$lmonth_floor	= mktime(0, 0, 0, date("m", $startepoch) - 1, 1, date("Y", $startepoch));
		$lmonth_peak 	= mktime(23, 59, 59, date("m", $startepoch) - 1, date("t", $lmonth_floor), date("Y", $startepoch));
		$ytd_floor		= mktime(0, 0, 0, 1, 1, date("Y", $startepoch));
		$ytd_peak		= mktime(23, 59, 59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch));
		$lytd_floor		= mktime(0, 0, 0, 1, 1, date("Y", $startepoch) - 1);
		$lytd_peak		= mktime(23, 59, 59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch) - 1);
		$current_month_floor	= mktime(0, 0, 0, date("m"), 1, date("Y"));

		// LEADS //
		$l_month_sql = "
		SELECT
			count(*) as count
		FROM
			Persons
		WHERE
			1
		AND
			(Persons.DateCreated >= '" . $month_floor . "' AND Persons.DateCreated <= '" . $month_peak . "')
		";
		if ($_POST['state'] != '') :
			$l_month_sql .= "
		AND
			Persons.Offices_id = '" . $_POST['state'] . "'
		";
		endif;
		if ($_POST['telemarketer'] != '') :
			$l_month_sql .= "
		AND
			Assigned_userID = '" . $_POST['telemarketer'] . "'
		";
		endif;

		//$l_month_send = mysql_query($l_month_sql, $db_link);
		//$l_month_data = mysql_fetch_assoc($l_month_send);
		//print_r($tc_month_data);
		$l_month_data = $this->db->get_single_result($l_month_sql);
		$l_month = $l_month_data['count'];

		$l_lmonth_sql = "
		SELECT
			count(*) as count
		FROM
			Persons
		WHERE
			1
		AND
			(Persons.DateCreated >= '" . $lmonth_floor . "' AND Persons.DateCreated <= '" . $lmonth_peak . "')
		";
		if ($_POST['state'] != '') :
			$l_lmonth_sql .= "
		AND
			Persons.Offices_id = '" . $_POST['state'] . "'
		";
		endif;
		if ($_POST['telemarketer'] != '') :
			$l_lmonth_sql .= "
		AND
			Assigned_userID = '" . $_POST['telemarketer'] . "'
		";
		endif;
		//$l_lmonth_send = mysql_query($l_lmonth_sql, $db_link);
		//$l_lmonth_data = mysql_fetch_assoc($l_lmonth_send);
		//print_r($tc_month_data);
		$l_lmonth_data = $this->db->get_single_result($l_lmonth_sql);
		$l_lmonth = $l_lmonth_data['count'];

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
			(PersonsNotes.PersonsNotes_dateCreated >= '" . $month_floor . "' AND PersonsNotes.PersonsNotes_dateCreated <= '" . $month_peak . "')
		AND
			PersonsNotes.PersonsNotes_type='Call Note'
		";
		if ($_POST['state'] != '') :
			$cl_month_sql .= "
		AND
			Persons.Offices_id = '" . $_POST['state'] . "'
		";
		endif;
		if ($_POST['telemarketer'] != '') :
			$cl_month_sql .= "
		AND
			Persons.Assigned_userID = '" . $_POST['telemarketer'] . "'
		";
		endif;
		//$cl_month_send = mysql_query($cl_month_sql, $db_link);
		//$cl_month_data = mysql_fetch_assoc($cl_month_send);
		//print_r($tc_month_data);
		$cl_month_data = $this->db->get_single_result($cl_month_sql);
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
			(PersonsNotes.PersonsNotes_dateCreated >= '" . $lmonth_floor . "' AND PersonsNotes.PersonsNotes_dateCreated <= '" . $lmonth_peak . "')
		AND
			PersonsNotes.PersonsNotes_type='Call Note'
		";
		if ($_POST['state'] != '') :
			$cl_lmonth_sql .= "
		AND
			Persons.Offices_id = '" . $_POST['state'] . "'
		";
		endif;
		if ($_POST['telemarketer'] != '') :
			$cl_lmonth_sql .= "
		AND
			Persons.Assigned_userID = '" . $_POST['telemarketer'] . "'
		";
		endif;
		//$cl_lmonth_send = mysql_query($cl_lmonth_sql, $db_link);
		//$cl_lmonth_data = mysql_fetch_assoc($cl_lmonth_send);
		//print_r($tc_month_data);
		$cl_lmonth_data = $this->db->get_single_result($cl_lmonth_sql);
		$cl_lmonth = $cl_lmonth_data['count'];

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
			(PersonsNotes.PersonsNotes_dateCreated >= '" . $month_floor . "' AND PersonsNotes.PersonsNotes_dateCreated <= '" . $month_peak . "')
		AND
			PersonsNotes.PersonsNotes_type != 'Call Note'
		";
		if ($_POST['state'] != '') :
			$ncl_month_sql .= "
		AND
			Persons.Offices_id = '" . $_POST['state'] . "'
		";
		endif;
		if ($_POST['telemarketer'] != '') :
			$ncl_month_sql .= "
		AND
			Persons.Assigned_userID = '" . $_POST['telemarketer'] . "'
		";
		endif;
		//$cl_month_send = mysql_query($cl_month_sql, $db_link);
		//$cl_month_data = mysql_fetch_assoc($cl_month_send);
		//print_r($tc_month_data);
		$ncl_month_data = $this->db->get_single_result($ncl_month_sql);
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
			(PersonsNotes.PersonsNotes_dateCreated >= '" . $lmonth_floor . "' AND PersonsNotes.PersonsNotes_dateCreated <= '" . $lmonth_peak . "')
		AND
			PersonsNotes.PersonsNotes_type != 'Call Note'
		";
		if ($_POST['state'] != '') :
			$ncl_lmonth_sql .= "
		AND
			Persons.Offices_id = '" . $_POST['state'] . "'
		";
		endif;
		if ($_POST['telemarketer'] != '') :
			$ncl_lmonth_sql .= "
		AND
			Persons.Assigned_userID = '" . $_POST['telemarketer'] . "'
		";
		endif;
		//$cl_lmonth_send = mysql_query($cl_lmonth_sql, $db_link);
		//$cl_lmonth_data = mysql_fetch_assoc($cl_lmonth_send);
		//print_r($tc_month_data);
		$ncl_lmonth_data = $this->db->get_single_result($ncl_lmonth_sql);
		$ncl_lmonth = $ncl_lmonth_data['count'];

		for ($i = 12; $i >= 0; $i--) {
			//echo $i."<br>\n";
			$month_floor	= mktime(0, 0, 0, date("m") - $i, 1, date("Y", $startepoch));
			$month_peak 	= mktime(23, 59, 59, date("m") - $i, date("t", $month_floor), date("Y", $startepoch));

			$monthLabel[] = date("M Y", $month_floor);

			$l_month_sql = "
			SELECT
				count(*) as count
			FROM
				Persons
			WHERE
				1
			AND
				(Persons.DateCreated >= '" . $month_floor . "' AND Persons.DateCreated <= '" . $month_peak . "')
			";
			//echo $l_lmonth_sql."<br>\n";
			//$l_month_send = mysql_query($l_month_sql, $db_link);
			//$l_month_data = mysql_fetch_assoc($l_month_send);
			//print_r($tc_month_data);
			$l_month_data = $this->db->get_single_result($l_month_sql);
			$leads_graph_count[] = $l_month_data['count'];
		}
		$lead_records_graph = implode(",", $leads_graph_count);
		$mlabels_graph = '"' . implode('","', $monthLabel) . '"';
	?>
		<div class="m-portlet m-portlet--bordered-semi m-portlet--full-height ">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<h3 class="m-portlet__head-text">Lead Trends &amp; Activity</h3>
					</div>
				</div>
				<div class="m-portlet__head-tools">
					<ul class="m-portlet__nav">
						<li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
							<a href="viewreport/6" class="m-portlet__nav-link btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand" target="_blank">
								View Full Report
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="m-portlet__body">
				<!--begin::Widget5-->
				<div class="m-widget4">
					<div class="m-widget4__chart m-portlet-fit--sides m--margin-top-10 m--margin-top-20" style="height:260px;">
						<div class="chartjs-size-monitor" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;">
							<div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:auto;pointer-events:none;visibility:hidden;z-index:-1;">
								<div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div>
							</div>
							<div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:auto;pointer-events:none;visibility:hidden;z-index:-1;">
								<div style="position:absolute;width:200%;height:200%;left:0; top:0"></div>
							</div>
						</div>
						<canvas id="m_chart_trends_stats"></canvas>
					</div>
					<div class="m-widget4__item">
						<div class="m-widget4__img m-widget4__img--logo">
							<img src="assets/app/media/img/icons/newleads.png" alt="">
						</div>
						<div class="m-widget4__info">
							<span class="m-widget4__title">
								New Leads
							</span>
							<br>
							<span class="m-widget4__sub">
								New leads within the past 30 days
							</span>
						</div>
						<span class="m-widget4__ext">
							<span class="m-widget4__number m--font-brand">
								<?php echo $l_month ?>
							</span>
						</span>
					</div>

					<div class="m-widget4__item">
						<div class="m-widget4__img m-widget4__img--logo">
							<img src="assets/app/media/img/icons/calls.png" alt="">
						</div>
						<div class="m-widget4__info">
							<span class="m-widget4__title">
								# of Calls
							</span>
							<br>
							<span class="m-widget4__sub">
								Notes marked as Call Notes
							</span>
						</div>
						<span class="m-widget4__ext">
							<span class="m-widget4__number m--font-brand">
								<?php echo $cl_month ?>
							</span>
						</span>
					</div>

					<div class="m-widget4__item">
						<div class="m-widget4__img m-widget4__img--logo">
							<img src="assets/app/media/img/icons/notes.png" alt="">
						</div>
						<div class="m-widget4__info">
							<span class="m-widget4__title">
								# of Notes
							</span>
							<br>
							<span class="m-widget4__sub">
								Internal notes count
							</span>
						</div>
						<span class="m-widget4__ext">
							<span class="m-widget4__number m--font-brand">
								<?php echo $ncl_month ?>
							</span>
						</span>
					</div>
				</div>
				<!--end::Widget 5-->
			</div>
		</div>
		<script>
			var e = document.getElementById("m_chart_trends_stats").getContext("2d"),
				t = e.createLinearGradient(0, 0, 0, 240);
			t.addColorStop(0, Chart.helpers.color("#00c5dc").alpha(.7).rgbString()), t.addColorStop(1, Chart.helpers.color("#f2feff").alpha(0).rgbString());
			var a = {
				type: "line",
				data: {
					labels: [<?php echo $mlabels_graph ?>],
					datasets: [{
						label: "New Leads",
						backgroundColor: t,
						borderColor: "#0dc8de",
						pointBackgroundColor: Chart.helpers.color("#ffffff").alpha(0).rgbString(),
						pointBorderColor: Chart.helpers.color("#ffffff").alpha(0).rgbString(),
						pointHoverBackgroundColor: mUtil.getColor("danger"),
						pointHoverBorderColor: Chart.helpers.color("#000000").alpha(.2).rgbString(),
						data: [<?php echo $lead_records_graph ?>]
					}]
				},
				options: {
					title: {
						display: !1
					},
					tooltips: {
						intersect: !1,
						mode: "nearest",
						xPadding: 10,
						yPadding: 10,
						caretPadding: 10
					},
					legend: {
						display: !1
					},
					responsive: !0,
					maintainAspectRatio: !1,
					hover: {
						mode: "index"
					},
					scales: {
						xAxes: [{
							display: !1,
							gridLines: !1,
							scaleLabel: {
								display: !0,
								labelString: "Month"
							}
						}],
						yAxes: [{
							display: !1,
							gridLines: !1,
							scaleLabel: {
								display: !0,
								labelString: "Value"
							},
							ticks: {
								beginAtZero: !0
							}
						}]
					},
					elements: {
						line: {
							tension: .19
						},
						point: {
							radius: 4,
							borderWidth: 12
						}
					},
					layout: {
						padding: {
							left: 0,
							right: 0,
							top: 5,
							bottom: 0
						}
					}
				}
			};
			new Chart(e, a);
		</script>
	<?php
	}

	function widget_salesbyuser($person_id)
	{
		$startepoch 	= time();

		$month_floor	= date('Y-m-d H:i:s', mktime(0, 0, 0, date("m", $startepoch), 1, date("Y", $startepoch)));
		$month_peak 	= date('Y-m-d H:i:s', mktime(23, 59, 59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch)));

		// SALES //
		$tc_month_sql = "
		SELECT `sales_by_salesperson`.`Sale_Id`,
			`sales_by_salesperson`.`SalesPerson_Email`,
			`sales_by_salesperson`.`SalesPerson_FirstName`,
			`sales_by_salesperson`.`SalesPerson_LastName`,
			`sales_by_salesperson`.`Sale_Date`,
			`sales_by_salesperson`.`Sale_Payment`,
			`sales_by_salesperson`.`Sale_Profile_Id`,
			`sales_by_salesperson`.`Sale_Email`,
			`sales_by_salesperson`.`Sale_FirtName`,
			`sales_by_salesperson`.`Sale_LastName`
		FROM `application_kelleher`.`sales_by_salesperson`
		WHERE
			(sales_by_salesperson.Sale_Date >= '" . $month_floor . "' 
			AND sales_by_salesperson.Sale_Date <= '" . $month_peak . "')
			AND sales_by_salesperson.SalesPerson_Id = '" . $person_id . "' 
		";
		$tc_month_send = $this->db->get_multi_result($tc_month_sql);
		if (!isset($tc_month_send['empty_result'])) {
			foreach ($tc_month_send as $tc_month_data) {
				$tc_month_array[] = $tc_month_data['Sale_Payment'];
			}
			$tc_month = @count($tc_month_array);
			$tc_month_sum = @array_sum($tc_month_array);
			$tc_month_avg = @round($tc_month_sum / $tc_month);
		} else {
			$tc_month = 0;
			$tc_month_sum = 0;
			$tc_month_avg = 0;
		}


		//print_r($dataProvider);
	?>
		<!--begin:: Widgets/Activity-->
		
			<!--begin::List widget 25-->
			<div class="card card-flush h-lg-50">
				<!--begin::Header-->
				<div class="card-header pt-5">
					<!--begin::Title-->
					<h3 class="card-title text-gray-800"><?php echo date('F'); ?>
					</h3>
					<!--end::Title-->
					<!--begin::Toolbar-->
					<div class="card-toolbar">
						<a href="viewreport/41" target="_blank">
							View Your Sales Report
						</a>

					</div>
					<!--end::Toolbar-->
				</div>
				<!--end::Header-->
				<!--begin::Body-->
				<div class="card-body pt-5">
					<!--begin::Item-->
					<div class="d-flex flex-stack">
						<!--begin::Section-->
						<div class="text-gray-700 fw-semibold fs-6 me-2">Your Total Sales:</div>
						<!--end::Section-->
						<!--begin::Statistics-->
						<div class="d-flex align-items-center ml-auto">
							<!--begin::Svg Icon | path: icons/duotune/arrows/arr094.svg-->
							<!-- <span class="svg-icon svg-icon-2 svg-icon-success me-2">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<rect opacity="0.5" x="16.9497" y="8.46448" width="13" height="2" rx="1" transform="rotate(135 16.9497 8.46448)" fill="currentColor"></rect>
									<path d="M14.8284 9.97157L14.8284 15.8891C14.8284 16.4749 15.3033 16.9497 15.8891 16.9497C16.4749 16.9497 16.9497 16.4749 16.9497 15.8891L16.9497 8.05025C16.9497 7.49797 16.502 7.05025 15.9497 7.05025L8.11091 7.05025C7.52512 7.05025 7.05025 7.52513 7.05025 8.11091C7.05025 8.6967 7.52512 9.17157 8.11091 9.17157L14.0284 9.17157C14.4703 9.17157 14.8284 9.52975 14.8284 9.97157Z" fill="currentColor"></path>
								</svg>
							</span> -->
							<!--end::Svg Icon-->
							<!--begin::Number-->
							<span class="text-gray-900 fw-bolder fs-6">$<?php echo number_format($tc_month_sum, 2) ?></span>
							<!--end::Number-->
						</div>
						<!--end::Statistics-->
					</div>
					<!--end::Item-->
					<!--begin::Separator-->
					<div class="separator separator-dashed my-3"></div>
					<!--end::Separator-->
					<!--begin::Item-->
					<div class="d-flex flex-stack">
						<!--begin::Section-->
						<div class="text-gray-700 fw-semibold fs-6 me-2">Your Avg Sale:</div>
						<!--end::Section-->
						<!--begin::Statistics-->
						<div class="d-flex align-items-senter ml-auto">
							<!--begin::Svg Icon | path: icons/duotune/arrows/arr093.svg-->
							<!-- <span class="svg-icon svg-icon-2 svg-icon-danger me-2">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<rect opacity="0.5" x="7.05026" y="15.5355" width="13" height="2" rx="1" transform="rotate(-45 7.05026 15.5355)" fill="currentColor"></rect>
									<path d="M9.17158 14.0284L9.17158 8.11091C9.17158 7.52513 8.6967 7.05025 8.11092 7.05025C7.52513 7.05025 7.05026 7.52512 7.05026 8.11091L7.05026 15.9497C7.05026 16.502 7.49797 16.9497 8.05026 16.9497L15.8891 16.9497C16.4749 16.9497 16.9498 16.4749 16.9498 15.8891C16.9498 15.3033 16.4749 14.8284 15.8891 14.8284L9.97158 14.8284C9.52975 14.8284 9.17158 14.4703 9.17158 14.0284Z" fill="currentColor"></path>
								</svg>
							</span> -->
							<!--end::Svg Icon-->
							<!--begin::Number-->
							<span class="text-gray-900 fw-bolder fs-6"> $<?php echo number_format($tc_month_avg, 2) ?></span>
							<!--end::Number-->
						</div>
						<!--end::Statistics-->
					</div>
					<!--end::Item-->
					<!--begin::Separator-->
					<div class="separator separator-dashed my-3"></div>
					<!--end::Separator-->
					<!--begin::Item-->
					<div class="d-flex flex-stack">
						<!--begin::Section-->
						<div class="text-gray-700 fw-semibold fs-6 me-2">Your Number of Sales:</div>
						<!--end::Section-->
						<!--begin::Statistics-->
						<div class="d-flex align-items-senter ml-auto">
							<!--begin::Svg Icon | path: icons/duotune/arrows/arr094.svg-->
							<!-- <span class="svg-icon svg-icon-2 svg-icon-success me-2">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<rect opacity="0.5" x="16.9497" y="8.46448" width="13" height="2" rx="1" transform="rotate(135 16.9497 8.46448)" fill="currentColor"></rect>
									<path d="M14.8284 9.97157L14.8284 15.8891C14.8284 16.4749 15.3033 16.9497 15.8891 16.9497C16.4749 16.9497 16.9497 16.4749 16.9497 15.8891L16.9497 8.05025C16.9497 7.49797 16.502 7.05025 15.9497 7.05025L8.11091 7.05025C7.52512 7.05025 7.05025 7.52513 7.05025 8.11091C7.05025 8.6967 7.52512 9.17157 8.11091 9.17157L14.0284 9.17157C14.4703 9.17157 14.8284 9.52975 14.8284 9.97157Z" fill="currentColor"></path>
								</svg>
							</span>
							end::Svg Icon -->
							<!--begin::Number-->
							<span class="text-gray-900 fw-bolder fs-6"> <?php echo $tc_month ?> </span>
							<!--end::Number-->
						</div>
						<!--end::Statistics-->
					</div>
					<!--end::Item-->
				</div>
				<!--end::Body-->
			</div>
			<!--end::LIst widget 25-->
		<!--end:: Widgets/Activity-->

	<?php
	}

	function widget_salesadmin()
	{
		$startepoch 	= time();
		$_POST['StartDate'] = date("m/d/Y", mktime(0, 0, 0, date("m"), 1, date("Y")));
		$_POST['EndDate'] = date("m/d/Y", mktime(0, 0, 0, date("m"), date("t"), date("Y")));

		$month_floor	= mktime(0, 0, 0, date("m", $startepoch), 1, date("Y", $startepoch));
		$month_peak 	= mktime(23, 59, 59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch));
		$lmonth_floor	= mktime(0, 0, 0, date("m", $startepoch) - 1, 1, date("Y", $startepoch));
		$lmonth_peak 	= mktime(23, 59, 59, date("m", $startepoch) - 1, date("t", $lmonth_floor), date("Y", $startepoch));
		$ytd_floor		= mktime(0, 0, 0, 1, 1, date("Y", $startepoch));
		$ytd_peak		= mktime(23, 59, 59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch));
		$lytd_floor		= mktime(0, 0, 0, 1, 1, date("Y", $startepoch) - 1);
		$lytd_peak		= mktime(23, 59, 59, date("m", $startepoch), date("t", $startepoch), date("Y", $startepoch) - 1);
		$current_month_floor	= mktime(0, 0, 0, date("m"), 1, date("Y"));

		// SALES //
		$tc_month_sql = "
		SELECT
			CONCAT(Persons.FirstName,' ',Persons.LastName) as Name,
			Persons.Person_id,
			PersonsSales.PersonsSales_basePrice,
			PersonsSales.PersonsSales_taxes,
			PersonsSales.PersonsSales_dateCreated,
			Persons.DateCreated
		FROM
			Persons
			INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id
		WHERE
			1
		AND
			(PersonsSales.PersonsSales_dateCreated >= '" . $month_floor . "' AND PersonsSales.PersonsSales_dateCreated <= '" . $month_peak . "')
		";
		if ($_POST['state'] != '') :
			$tc_month_sql .= "
		AND
			PersonsSales.Offices_Offices_id = '" . $_POST['state'] . "'
		";
		endif;
		if ($_POST['telemarketer'] != '') :
			$tc_month_sql .= "
		AND
			Persons.Assigned_userID = '" . $_POST['telemarketer'] . "'
		";
		endif;
		//echo $tc_month_sql;
		//$tc_month_send = mysql_query($tc_month_sql, $db_link);
		//while($tc_month_data = mysql_fetch_assoc($tc_month_send)) {
		$tc_month_send = $this->db->get_multi_result($tc_month_sql);
		if (!isset($tc_month_send['empty_result'])) {
			foreach ($tc_month_send as $tc_month_data) {
				$tc_month_array[] = $tc_month_data['PersonsSales_basePrice'] + $tc_month_data['PersonsSales_taxes'];
				$time_month_array[] = $tc_month_data['PersonsSales_dateCreated'] - $tc_month_data['DateCreated'];
			}
		}
		$tc_month = @count($tc_month_array);
		$tc_month_sum = @array_sum($tc_month_array);
		$tc_month_avg = @round($tc_month_sum / $tc_month);
		$time_month_avg = @round(((array_sum($time_month_array) / count($time_month_array)) / 2592000), 1);

		$tc_lmonth_sql = "
		SELECT
			CONCAT(Persons.FirstName,' ',Persons.LastName) as Name,
			Persons.Person_id,
			PersonsSales.PersonsSales_basePrice,
			PersonsSales.PersonsSales_taxes,
			PersonsSales.PersonsSales_dateCreated,
			Persons.DateCreated
		FROM
			Persons
			INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id
		WHERE
			1
		AND
			(PersonsSales.PersonsSales_dateCreated >= '" . $lmonth_floor . "' AND PersonsSales.PersonsSales_dateCreated <= '" . $lmonth_peak . "')
		";
		if ($_POST['state'] != '') :
			$tc_lmonth_sql .= "
		AND
			PersonsSales.Offices_Offices_id = '" . $_POST['state'] . "'
		";
		endif;
		if ($_POST['telemarketer'] != '') :
			$tc_lmonth_sql .= "
		AND
			Persons.Assigned_userID = '" . $_POST['telemarketer'] . "'
		";
		endif;
		//echo $tc_lmonth_sql;
		//$tc_lmonth_send = mysql_query($tc_lmonth_sql, $db_link);
		//while($tc_lmonth_data = mysql_fetch_assoc($tc_lmonth_send)) {
		$tc_lmonth_send = $this->db->get_multi_result($tc_lmonth_sql);
		if (!isset($tc_lmonth_send['empty_result'])) {
			foreach ($tc_lmonth_send as $tc_lmonth_data) {
				$tc_lmonth_array[] = $tc_lmonth_data['PersonsSales_basePrice'] + $tc_lmonth_data['PersonsSales_taxes'];
				$time_lmonth_array[] = $tc_lmonth_data['PersonsSales_dateCreated'] - $tc_lmonth_data['Persons_dateCreated'];
			}
		}
		$tc_lmonth = @count($tc_lmonth_array);
		$tc_lmonth_sum = @array_sum($tc_lmonth_array);
		$tc_lmonth_avg = @round($tc_lmonth_sum / $tc_lmonth);
		$time_lmonth_avg = @round(((array_sum($time_lmonth_array) / count($time_lmonth_array)) / 2592000), 1);

		for ($i = 12; $i >= 0; $i--) {
			$month_floor	= mktime(0, 0, 0, date("m") - $i, 1, date("Y", $startepoch));
			$month_peak 	= mktime(23, 59, 59, date("m") - $i, date("t", $month_floor), date("Y", $startepoch));

			$monthLabel[] = date("M Y", $month_floor);
			$tc_month_sql = "
			SELECT
				COUNT(*) as count
			FROM
				Persons
				INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id
			WHERE
				1
			AND
				(PersonsSales.PersonsSales_dateCreated >= '" . $month_floor . "' AND PersonsSales.PersonsSales_dateCreated <= '" . $month_peak . "')
			";
			//echo $tc_month_sql;
			//$tc_month_send = mysql_query($tc_month_sql, $db_link);
			//$tc_month_data = mysql_fetch_assoc($tc_month_send);
			$tc_month_data = $this->db->get_single_result($tc_month_sql);
			$client_graph_count[] = $tc_month_data['count'];
			$mlabels_graph = '"' . implode('","', $monthLabel) . '"';
		}
		$client_records_graph = implode(",", $client_graph_count);
		//print_r($dataProvider);
	?>
		<!--begin:: Widgets/Activity-->
		<div class="m-portlet m-portlet--bordered-semi m-portlet--widget-fit m-portlet--full-height m-portlet--skin-light ">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<h3 class="m-portlet__head-text m--font-light">
							Sales &amp; Membership <small>this month</small>
						</h3>
					</div>
				</div>
				<div class="m-portlet__head-tools">
					<ul class="m-portlet__nav">
						<li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover">
							<a href="viewreport/41" class="m-portlet__nav-link btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand" target="_blank">
								View Full Report
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="m-portlet__body">
				<div class="m-widget17">
					<div class="m-widget17__visual m-widget17__visual--chart m-portlet-fit--top m-portlet-fit--sides m--bg-danger">
						<div class="m-widget17__chart" style="height:320px;">
							<div class="chartjs-size-monitor" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;">
								<div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:auto;pointer-events:none;visibility:hidden;z-index:-1;">
									<div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div>
								</div>
								<div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:auto;pointer-events:none;visibility:hidden;z-index:-1;">
									<div style="position:absolute;width:200%;height:200%;left:0; top:0"></div>
								</div>
							</div>
							<canvas id="m_chart_activities"></canvas>
						</div>
					</div>
					<div class="m-widget17__stats">
						<div class="m-widget17__items m-widget17__items-col1">
							<div class="m-widget17__item">
								<span class="m-widget17__icon">
									<i class="flaticon-profile-1 m--font-brand"></i>
								</span>
								<span class="m-widget17__subtitle">
									<?php echo $tc_month ?>
								</span>
								<span class="m-widget17__desc">
									New Sales
								</span>
							</div>
							<div class="m-widget17__item">
								<span class="m-widget17__icon">
									<i class="flaticon-calendar m--font-info"></i>
								</span>
								<span class="m-widget17__subtitle">
									<?php echo $time_month_avg ?> months
								</span>
								<span class="m-widget17__desc">
									Avg Sales Cycle
								</span>
							</div>
						</div>
						<div class="m-widget17__items m-widget17__items-col2">
							<div class="m-widget17__item">
								<span class="m-widget17__icon">
									<i class="flaticon-coins m--font-success"></i>
								</span>
								<span class="m-widget17__subtitle">
									$<?php echo number_format($tc_month_sum, 2) ?>
								</span>
								<span class="m-widget17__desc">
									Sales Dollars
								</span>
							</div>
							<div class="m-widget17__item">
								<span class="m-widget17__icon">
									<i class="flaticon-dashboard m--font-danger"></i>
								</span>
								<span class="m-widget17__subtitle">
									$<?php echo number_format($tc_month_avg, 2) ?>
								</span>
								<span class="m-widget17__desc">
									Avg Sale
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--end:: Widgets/Activity-->
		<script>
			var e = document.getElementById("m_chart_activities").getContext("2d"),
				t = e.createLinearGradient(0, 0, 0, 240);
			t.addColorStop(0, Chart.helpers.color("#e14c86").alpha(1).rgbString()), t.addColorStop(1, Chart.helpers.color("#e14c86").alpha(.3).rgbString());
			var a = {
				type: "line",
				data: {
					labels: [<?php echo $mlabels_graph ?>],
					datasets: [{
						label: "Sales Stats",
						backgroundColor: t,
						borderColor: "#e13a58",
						pointBackgroundColor: Chart.helpers.color("#000000").alpha(0).rgbString(),
						pointBorderColor: Chart.helpers.color("#000000").alpha(0).rgbString(),
						pointHoverBackgroundColor: mUtil.getColor("light"),
						pointHoverBorderColor: Chart.helpers.color("#ffffff").alpha(.1).rgbString(),
						data: [<?php echo $client_records_graph ?>]
					}]
				},
				options: {
					title: {
						display: !1
					},
					tooltips: {
						mode: "nearest",
						intersect: !1,
						position: "nearest",
						xPadding: 10,
						yPadding: 10,
						caretPadding: 10
					},
					legend: {
						display: !1
					},
					responsive: !0,
					maintainAspectRatio: !1,
					scales: {
						xAxes: [{
							display: !1,
							gridLines: !1,
							scaleLabel: {
								display: !0,
								labelString: "Month"
							}
						}],
						yAxes: [{
							display: !1,
							gridLines: !1,
							scaleLabel: {
								display: !0,
								labelString: "Value"
							},
							ticks: {
								beginAtZero: !0
							}
						}]
					},
					elements: {
						line: {
							tension: 1e-7
						},
						point: {
							radius: 4,
							borderWidth: 12
						}
					},
					layout: {
						padding: {
							left: 0,
							right: 0,
							top: 10,
							bottom: 0
						}
					}
				}
			};
			new Chart(e, a)
		</script>
	<?php
	}

	function widget_quickNav()
	{
	?>
		<a href="/page.php?path=viewreport/6" target="_blank" class="btn btn-lg m-btn--square btn-info btn-block">Lead/Sales Metrics</a>
		<a href="/page.php?path=viewreport/41" target="_blank" class="btn btn-lg m-btn--square btn-info btn-block">Sales Report</a>
		<a href="/page.php?path=viewreport/57" target="_blank" class="btn btn-lg m-btn--square btn-info btn-block">Sales Staff Overview</a>
		<a href="/page.php?path=viewreport/56" target="_blank" class="btn btn-lg m-btn--square btn-info btn-block">Matchmaker Overview</a>
	<?php
	}

	function widget_newestClient()
	{
		// NEWEST LEAD W/ PIC //
		$nl_sql = "
		SELECT
			Persons.Person_id,
			Persons.DateCreated,
			DateOfBirth,
			DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), DateOfBirth)), '%Y')+0 AS RecordAge,
			PersonsImages.PersonsImages_path,
			Persons.Assigned_userID,
			Addresses.City,
			Addresses.State,
			PersonsProfile.prQuestion_631
		FROM
			Persons
			INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
			INNER JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'
			LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'	
		WHERE
			PersonsImages_path != 'NULL'
		AND
			Persons.Person_id='58401'
		ORDER BY
			DateCreated DESC
		LIMIT 1
		";
		$nl_snd = $this->db->get_single_result($nl_sql);

		$from 	= new DateTime(date("Y-m-d", $nl_snd['DateOfBirth']));
		$to   	= new DateTime('today');
		$age 	= $from->diff($to)->y;
		//print_r($nl_snd);
		include_once("class.users.php");
		$USER = new Users($this->db);
	?>
		<div class="m-portlet m-portlet--bordered-semi m-portlet--full-height ">
			<div class="m-portlet__head m-portlet__head--fit">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-action">
						<button type="button" class="btn btn-sm m-btn--pill  btn-brand">
							Newest Member
						</button>
					</div>
				</div>
			</div>
			<div class="m-portlet__body">
				<div class="m-widget19">
					<div class="m-widget19__pic m-portlet-fit--top m-portlet-fit--sides" style="min-height-: 250px">
						<img src="<?php echo $this->record->get_PrimaryImage($nl_snd['Person_id']) ?>" alt="" style="height:350px;">
						<h3 class="m-widget19__title m--font-light">
							<?php echo $this->record->get_personName($nl_snd['Person_id']) ?>
						</h3>
						<div class="m-widget19__shadow"></div>
					</div>
					<div class="m-widget19__content">
						<div class="m-widget19__header">
							<div class="m-widget19__user-img">
								<img class="m-widget19__img" src="<?php echo $USER->get_userImage($nl_snd['Assigned_userID']) ?>" alt="">
							</div>
							<div class="m-widget19__info">
								<span class="m-widget19__username">
									<?php echo $this->record->get_userName($nl_snd['Assigned_userID']) ?>
								</span>
								<br>
								<span class="m-widget19__time">
									Assigned Market Director
								</span>
							</div>
							<div class="m-widget19__stats">
								<span class="m-widget19__number m--font-brand">
									<?php echo date("m/d", $nl_snd['DateCreated']) ?>
								</span>
								<span class="m-widget19__comment">
									Joined
								</span>
							</div>
						</div>
						<div class="m-widget19__body">
							<div><strong>Type:</strong> <?php echo $this->record->get_personType($nl_snd['Person_id']) ?></div>
							<div><strong>Age:</strong> <?php echo $age ?></div>
							<div><strong>Location:</strong> <?php echo $nl_snd['City'] ?> <?php echo $nl_snd['State'] ?></div>
							<div><strong>Income:</strong> <?php echo $nl_snd['prQuestion_631'] ?></div>
						</div>
					</div>
					<div class="m-widget19__action">
						<a href="/profile/<?php echo $nl_snd['Person_id'] ?>" class="btn m-btn--pill btn-secondary m-btn m-btn--hover-brand m-btn--custom" target="_blank">
							View Record
						</a>
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	function widget_Sales()
	{
		// SALES //
		$tc_month_sql = "
		SELECT
			CONCAT(Persons.FirstName,' ',Persons.LastName) as Name,
			Persons.Person_id,
			PersonsSales.PersonsSales_basePrice,
			PersonsSales.PersonsSales_taxes,
			PersonsSales.PersonsSales_dateCreated,
			Persons.DateCreated
		FROM
			Persons
			INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id
		WHERE
			1
		AND
			(PersonsSales.PersonsSales_dateCreated >= '" . $month_floor . "' AND PersonsSales.PersonsSales_dateCreated <= '" . $month_peak . "')
		";
		if ($_POST['state'] != '') :
			$tc_month_sql .= "
		AND
			PersonsSales.Offices_Offices_id = '" . $_POST['state'] . "'
		";
		endif;
		if ($_POST['telemarketer'] != '') :
			$tc_month_sql .= "
		AND
			Persons.Assigned_userID = '" . $_POST['telemarketer'] . "'
		";
		endif;
		//echo $tc_month_sql;
		//$tc_month_send = mysql_query($tc_month_sql, $db_link);
		//while($tc_month_data = mysql_fetch_assoc($tc_month_send)) {
		$tc_month_send = $this->db->get_multi_result($tc_month_sql);
		if (!isset($tc_month_send['empty_result'])) {
			foreach ($tc_month_send as $tc_month_data) {
				$tc_month_array[] = $tc_month_data['PersonsSales_basePrice'] + $tc_month_data['PersonsSales_taxes'];
				$time_month_array[] = $tc_month_data['PersonsSales_dateCreated'] - $tc_month_data['DateCreated'];
			}
		}
		$tc_month = @count($tc_month_array);
		$tc_month_sum = @array_sum($tc_month_array);
		$tc_month_avg = @round($tc_month_sum / $tc_month);
		$time_month_avg = @round(((array_sum($time_month_array) / count($time_month_array)) / 2592000), 1);

		$tc_lmonth_sql = "
		SELECT
			CONCAT(Persons.FirstName,' ',Persons.LastName) as Name,
			Persons.Person_id,
			PersonsSales.PersonsSales_basePrice,
			PersonsSales.PersonsSales_taxes,
			PersonsSales.PersonsSales_dateCreated,
			Persons.DateCreated
		FROM
			Persons
			INNER JOIN PersonsSales ON PersonsSales.Persons_Person_id=Persons.Person_id
		WHERE
			1
		AND
			(PersonsSales.PersonsSales_dateCreated >= '" . $lmonth_floor . "' AND PersonsSales.PersonsSales_dateCreated <= '" . $lmonth_peak . "')
		";
		if ($_POST['state'] != '') :
			$tc_lmonth_sql .= "
		AND
			PersonsSales.Offices_Offices_id = '" . $_POST['state'] . "'
		";
		endif;
		if ($_POST['telemarketer'] != '') :
			$tc_lmonth_sql .= "
		AND
			Persons.Assigned_userID = '" . $_POST['telemarketer'] . "'
		";
		endif;
		//echo $tc_lmonth_sql;
		//$tc_lmonth_send = mysql_query($tc_lmonth_sql, $db_link);
		//while($tc_lmonth_data = mysql_fetch_assoc($tc_lmonth_send)) {
		$tc_lmonth_send = $this->db->get_multi_result($tc_lmonth_sql);
		if (!isset($tc_lmonth_send['empty_result'])) {
			foreach ($tc_lmonth_send as $tc_lmonth_data) {
				$tc_lmonth_array[] = $tc_lmonth_data['PersonsSales_basePrice'] + $tc_lmonth_data['PersonsSales_taxes'];
				$time_lmonth_array[] = $tc_lmonth_data['PersonsSales_dateCreated'] - $tc_lmonth_data['Persons_dateCreated'];
			}
		}
		$tc_lmonth = @count($tc_lmonth_array);
		$tc_lmonth_sum = @array_sum($tc_lmonth_array);
		$tc_lmonth_avg = @round($tc_lmonth_sum / $tc_lmonth);
		$time_lmonth_avg = @round(((array_sum($time_lmonth_array) / count($time_lmonth_array)) / 2592000), 1);

	?>
		<div class="m-portlet m-portlet--bordered-semi m-portlet--full-height ">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<h3 class="m-portlet__head-text">
							Lead Activity &amp; Trends
						</h3>
					</div>
				</div>
				<div class="m-portlet__head-tools">
					<ul class="m-portlet__nav">
						<li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
							<a href="viewreport/6" class="m-portlet__nav-link btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand" target="_blank">
								View Full Report
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="m-portlet__body">
				<table width="100%" class="table m-table" id="table">
					<thead class="thead-inverse">
						<tr>
							<th width="35%">&nbsp;</th>
							<th width="10%" class="text-center"><strong>This Month</strong></th>
							<th width="10%" class="text-center"><strong>Last Month</strong></th>
							<th width="10%" class="text-center"><strong>VS</strong></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><span data-toggle="m-tooltip" title="" data-original-title="Number of SALES executed within the time frame and assigned to the salesperson listed.">Sales Closed #</span></td>
							<td class="text-center"><?php echo $tc_month ?></td>
							<td class="text-center"><?php echo $tc_lmonth ?></td>
							<td class="text-center"><?php echo bw_check($tc_month, $tc_lmonth) ?></td>
						</tr>
						<tr>
							<td><span data-toggle="m-tooltip" title="" data-original-title="Total dollars from SALES executed within the time frame and assigned to the salesperson listed.">Sales Closed $</span></td>
							<td class="text-right"><?php echo number_format($tc_month_sum, 2) ?></td>
							<td class="text-right"><?php echo number_format($tc_lmonth_sum, 2) ?></td>
							<td class="text-center"><?php echo bw_check($tc_month_sum, $tc_lmonth_sum) ?></td>
						</tr>
						<tr>
							<td><span data-toggle="m-tooltip" title="" data-original-title="Average amount of all closed SALES executed within the time frame and assigned to the salesperson listed.">Average Sale $</span></td>
							<td class="text-right"><?php echo number_format($tc_month_avg, 2) ?></td>
							<td class="text-right"><?php echo number_format($tc_lmonth_avg, 2) ?></td>
							<td class="text-center"><?php echo bw_check($tc_month_avg, $tc_lmonth_avg) ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	<?php
	}

	function get_RecordByGenderAge($gender, $ageStart, $ageEnd)
	{
		$sql = "SELECT count(*) as count FROM Persons WHERE Gender='" . $gender . "'  
		AND DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), Persons.DateOfBirth)), '%Y')+0 
		BETWEEN " . $ageStart . " AND " . $ageEnd . "";
		$snd = $this->db->get_single_result($sql);
		return $snd['count'];
	}

	function get_MembersByGenderAge($gender, $ageStart, $ageEnd)
	{
		$sql = "SELECT count(*) as count FROM Persons WHERE Gender='" . $gender . "'
		 AND PersonsTypes_id IN (4,7,8,10,12,14,6)
		 AND DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), Persons.DateOfBirth)), '%Y')+0 
		BETWEEN " . $ageStart . " AND " . $ageEnd . "";
		
		$snd = $this->db->get_single_result($sql);
		return $snd['count'];
	}

	function widget_DBoverview()
	{
		$records[0] = $this->get_RecordByGenderAge("M", 18, 25);
		$records[1] = $this->get_RecordByGenderAge("M", 26, 35);
		$records[2] = $this->get_RecordByGenderAge("M", 36, 45);
		$records[3] = $this->get_RecordByGenderAge("M", 46, 55);
		$records[4] = $this->get_RecordByGenderAge("M", 56, 65);
		$records[5] = $this->get_RecordByGenderAge("M", 66, 99);

		$members[0] = $this->get_MembersByGenderAge("M", 18, 25);
		$members[1] = $this->get_MembersByGenderAge("M", 26, 35);
		$members[2] = $this->get_MembersByGenderAge("M", 36, 45);
		$members[3] = $this->get_MembersByGenderAge("M", 46, 55);
		$members[4] = $this->get_MembersByGenderAge("M", 56, 65);
		$members[5] = $this->get_MembersByGenderAge("M", 66, 99);
	?>
		<div class="m-portlet m-portlet--full-height ">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<h3 class="m-portlet__head-text">
							<i class="flaticon-network"></i> KISS Database Overview
						</h3>
					</div>
				</div>
				<div class="m-portlet__head-tools">
				</div>
			</div>

			<div class="m-portlet__body">
				<div class="m-widget16">
					<div class="row">
						<div class="col-lg-3 col-md-6 col-sm-12">
							<div class="m-widget16__head">
								<div class="m-widget16__item">
									<span class="m-widget16__sceduled">
										Men
									</span>
									<span class="m-widget16__amount m--align-right">
										<a href="javascript:;" class="m-link m-link--state m-link--metal" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="Records" data-content="Includes all record types, active, archived, and leads">
											Records
										</a>
									</span>
									<span class="m-widget16__amount m--align-right">
										<a href="javascript:;" class="m-link m-link--state m-link--metal" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="Clients" data-content="Includes active members, active resources, active participating, pending members, trial members, and free members.">
											Clients
										</a>
									</span>
								</div>
							</div>
							<div class="m-widget16__body">

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-danger">
										Men under 25
									</span>
									<span class="m-widget16__price m--align-right m--font-danger">
										<?php echo number_format($records[0], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-danger">
										<?php echo number_format($members[0], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-accent">
										Men 26 - 35
									</span>
									<span class="m-widget16__price m--align-right m--font-accent">
										<?php echo number_format($records[1], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-accent">
										<?php echo number_format($members[1], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-info">
										Men 36 - 45
									</span>
									<span class="m-widget16__price m--align-right m--font-info">
										<?php echo number_format($records[2], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-info">
										<?php echo number_format($members[2], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-success">
										Men 46 - 55
									</span>
									<span class="m-widget16__price m--align-right m--font-success">
										<?php echo number_format($records[3], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-success">
										<?php echo number_format($members[3], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-primary">
										Men 55 - 65
									</span>
									<span class="m-widget16__price m--align-right m--font-primary">
										<?php echo number_format($records[4], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-primary">
										<?php echo number_format($members[4], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-warning">
										Men Over 65
									</span>
									<span class="m-widget16__price m--align-right m--font-warning">
										<?php echo number_format($records[5], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-warning">
										<?php echo number_format($members[5], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date" style="color:#000;">
										TOTAL
									</span>
									<span class="m-widget16__price m--align-right" style="color:#000;">
										<?php echo number_format(array_sum($records), 0) ?>
									</span>
									<span class="m-widget16__price m--align-right" style="color:#000;">
										<?php echo number_format(array_sum($members), 0) ?>
									</span>
								</div>

							</div>
						</div>

						<div class="col-lg-3 col-md-6 col-sm-12">
							<div class="m-widget16__stats">
								<div class="m-widget16__visual">
									<div id="m_chart_support_tickets2" class="m-widget16__chart" style="height:250px">
										<div class="m-widget16__chart-number">
											<div><?php echo number_format(array_sum($members), 0) ?></div>
											<div style="font-size:.5em;">Male Clients</div>
										</div>
									</div>
								</div>
							</div>
						</div>


						<?php
						$frecords[0] = $this->get_RecordByGenderAge("F", 18, 25);
						$frecords[1] = $this->get_RecordByGenderAge("F", 26, 35);
						$frecords[2] = $this->get_RecordByGenderAge("F", 36, 45);
						$frecords[3] = $this->get_RecordByGenderAge("F", 46, 55);
						$frecords[4] = $this->get_RecordByGenderAge("F", 56, 65);
						$frecords[5] = $this->get_RecordByGenderAge("F", 66, 99);

						$fmembers[0] = $this->get_MembersByGenderAge("F", 18, 25);
						$fmembers[1] = $this->get_MembersByGenderAge("F", 26, 35);
						$fmembers[2] = $this->get_MembersByGenderAge("F", 36, 45);
						$fmembers[3] = $this->get_MembersByGenderAge("F", 46, 55);
						$fmembers[4] = $this->get_MembersByGenderAge("F", 56, 65);
						$fmembers[5] = $this->get_MembersByGenderAge("F", 66, 99);
						?>

						<div class="col-lg-3 col-md-6 col-sm-12">
							<div class="m-widget16__head">
								<div class="m-widget16__item">
									<span class="m-widget16__sceduled">
										Women
									</span>
									<span class="m-widget16__amount m--align-right">
										<a href="javascript:;" class="m-link m-link--state m-link--metal" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="Records" data-content="Includes all record types, active, archived, and leads">
											Records
										</a>
									</span>
									<span class="m-widget16__amount m--align-right">
										<a href="javascript:;" class="m-link m-link--state m-link--metal" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="Clients" data-content="Includes active members, active resources, active participating, pending members, trial members, and free members.">
											Clients
										</a>
									</span>
								</div>
							</div>
							<div class="m-widget16__body">

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-danger">
										Women under 25
									</span>
									<span class="m-widget16__price m--align-right m--font-danger">
										<?php echo number_format($frecords[0], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-danger">
										<?php echo number_format($fmembers[0], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-accent">
										Women 26 - 35
									</span>
									<span class="m-widget16__price m--align-right m--font-accent">
										<?php echo number_format($frecords[1], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-accent">
										<?php echo number_format($fmembers[1], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-info">
										Women 36 - 45
									</span>
									<span class="m-widget16__price m--align-right m--font-info">
										<?php echo number_format($frecords[2], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-info">
										<?php echo number_format($fmembers[2], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-success">
										Women 46 - 55
									</span>
									<span class="m-widget16__price m--align-right m--font-success">
										<?php echo number_format($frecords[3], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-success">
										<?php echo number_format($fmembers[3], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-primary">
										Women 55 - 65
									</span>
									<span class="m-widget16__price m--align-right m--font-primary">
										<?php echo number_format($frecords[4], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-primary">
										<?php echo number_format($fmembers[4], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date m--font-warning">
										Women Over 65
									</span>
									<span class="m-widget16__price m--align-right m--font-warning">
										<?php echo number_format($frecords[5], 0) ?>
									</span>
									<span class="m-widget16__price m--align-right m--font-warning">
										<?php echo number_format($fmembers[5], 0) ?>
									</span>
								</div>

								<div class="m-widget16__item">
									<span class="m-widget16__date" style="color:#000;">
										TOTAL
									</span>
									<span class="m-widget16__price m--align-right" style="color:#000;">
										<?php echo number_format(array_sum($frecords), 0) ?>
									</span>
									<span class="m-widget16__price m--align-right" style="color:#000;">
										<?php echo number_format(array_sum($fmembers), 0) ?>
									</span>
								</div>

							</div>
						</div>

						<div class="col-lg-3 col-md-6 col-sm-12">
							<div class="m-widget16__stats">
								<div class="m-widget16__visual">
									<div id="m_chart_support_tickets3" class="m-widget16__chart" style="height:250px">
										<div class="m-widget16__chart-number">
											<div><?php echo number_format(array_sum($fmembers), 0) ?></div>
											<div style="font-size:.5em;">Female Clients</div>
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
		<script>
			$(document).ready(function(e) {
				$("#m_chart_support_tickets2").length && new Chartist.Pie("#m_chart_support_tickets2", {
					series: [{
						value: <?php echo $members[0] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("danger")
						}
					}, {
						value: <?php echo $members[1] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("accent")
						}
					}, {
						value: <?php echo $members[2] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("info")
						}
					}, {
						value: <?php echo $members[3] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("success")
						}
					}, {
						value: <?php echo $members[4] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("primary")
						}
					}, {
						value: <?php echo $members[5] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("warning")
						}
					}],
					labels: [1, 2, 3, 4, 5, 6]
				}, {
					donut: !0,
					donutWidth: 35,
					showLabel: !1
				}).on("draw", function(e) {
					if ("slice" === e.type) {
						var t = e.element._node.getTotalLength();
						e.element.attr({
							"stroke-dasharray": t + "px " + t + "px"
						});
						var a = {
							"stroke-dashoffset": {
								id: "anim" + e.index,
								dur: 1e3,
								from: -t + "px",
								to: "0px",
								easing: Chartist.Svg.Easing.easeOutQuint,
								fill: "freeze",
								stroke: e.meta.color
							}
						};
						0 !== e.index && (a["stroke-dashoffset"].begin = "anim" + (e.index - 1) + ".end"), e.element.attr({
							"stroke-dashoffset": -t + "px",
							stroke: e.meta.color
						}), e.element.animate(a, !1)
					}
				});
				$("#m_chart_support_tickets3").length && new Chartist.Pie("#m_chart_support_tickets3", {
					series: [{
						value: <?php echo $fmembers[0] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("danger")
						}
					}, {
						value: <?php echo $fmembers[1] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("accent")
						}
					}, {
						value: <?php echo $fmembers[2] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("info")
						}
					}, {
						value: <?php echo $fmembers[3] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("success")
						}
					}, {
						value: <?php echo $fmembers[4] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("primary")
						}
					}, {
						value: <?php echo $fmembers[5] ?>,
						className: "custom",
						meta: {
							color: mUtil.getColor("warning")
						}
					}],
					labels: [1, 2, 3, 4, 5, 6]
				}, {
					donut: !0,
					donutWidth: 35,
					showLabel: !1
				}).on("draw", function(e) {
					if ("slice" === e.type) {
						var t = e.element._node.getTotalLength();
						e.element.attr({
							"stroke-dasharray": t + "px " + t + "px"
						});
						var a = {
							"stroke-dashoffset": {
								id: "anim" + e.index,
								dur: 1e3,
								from: -t + "px",
								to: "0px",
								easing: Chartist.Svg.Easing.easeOutQuint,
								fill: "freeze",
								stroke: e.meta.color
							}
						};
						0 !== e.index && (a["stroke-dashoffset"].begin = "anim" + (e.index - 1) + ".end"), e.element.attr({
							"stroke-dashoffset": -t + "px",
							stroke: e.meta.color
						}), e.element.animate(a, !1)
					}
				});
			});
		</script>
		<?php
	}

	function countDashboardRecords($type = array(), $office = NULL, $gender = NULL, $age = NULL)
	{
		//echo "GENDER:".$gender."<br>\n";
		$sql = "SELECT count(*) as count FROM Persons WHERE 1 ";
		if (count($type) != 0) {
			$sql .= " AND PersonsTypes_id IN (" . implode(",", $type) . ")";
		}
		if ($office != NULL) {
			$sql .= " AND Offices_id='" . $office . "'";
		}
		if ($gender != NULL) {
			$sql .= " AND Gender='" . $gender . "'";
		}
		if ($age != NULL) {
			$sql .= "  AND DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), Persons.DateOfBirth)), '%Y')+0 
			BETWEEN " . $age[0] . " AND " . $age[1] . "";		
		}
		//echo $sql;
		$snd = $this->db->get_single_result($sql);
		return $snd['count'];
	}

	function widget_GeoOverview($showPortlet = true)
	{
		if (false) :
		?>
			<div class="m-portlet m-portlet--tab">
				<div class="m-portlet__head">
					<div class="m-portlet__head-caption">
						<div class="m-portlet__head-title">
							<span class="m-portlet__head-icon m--hide">
								<i class="la la-gear"></i>
							</span>
							<h3 class="m-portlet__head-text">
								<i class="flaticon-network"></i> KISS Database Geographic Overview
							</h3>
						</div>
					</div>
				</div>
				<div class="m-portlet__body">
				<?php endif; ?>

				<div class="row">
					<div class="col-lg-9">
						<div id="m_gmap_1" style="height:550px;"></div>
						<div id="office_stats_block"></div>
					</div>
					<div class="col-lg-3">
						<div class="m-widget6">
							<div class="m-widget6__body" id="stats_list_data">
								<?php
								$mt_sql = "SELECT * FROM PersonTypes WHERE PersonsTypes_id NOT IN (1,2,11,9) ORDER BY PersonsTypes_order ASC";
								$mt_snd = $this->db->get_multi_result($mt_sql);
								foreach ($mt_snd as $mt_dta) :
								?>
									<div class="m-widget6__item" style="padding-top:.7rem; padding-bottom:.07rem;">
										<span class="m-widget6__text m--font-<?php echo $mt_dta['PersonsTypes_color'] ?>">
											<?php echo $mt_dta['PersonsTypes_text'] ?>
										</span>
										<span class="m-widget6__text m--align-right m--font-boldest m--font-<?php echo $mt_dta['PersonsTypes_color'] ?>" id="statNumber_<?php echo $mt_dta['PersonsTypes_id'] ?>">

										</span>
									</div>

									<div class="m-widget6__item" style="padding-top:.07rem; padding-bottom:.07rem;">
										<span class="m-widget6__text m--font-<?php echo $mt_dta['PersonsTypes_color'] ?>">
											&nbsp;&nbsp;&nbsp;<small><?php echo $mt_dta['PersonsTypes_text'] ?> - Male</small>
										</span>
										<span class="m-widget6__text m--align-right m--font-boldest m--font-<?php echo $mt_dta['PersonsTypes_color'] ?>" id="statNumber_<?php echo $mt_dta['PersonsTypes_id'] ?>_M">
											<small></small>
										</span>
									</div>

									<div class="m-widget6__item" style="padding-top:.07rem; padding-bottom:.07rem; margin-bottom:.7em;">
										<span class="m-widget6__text m--font-<?php echo $mt_dta['PersonsTypes_color'] ?>">
											&nbsp;&nbsp;&nbsp;<small><?php echo $mt_dta['PersonsTypes_text'] ?> - Female</small>
										</span>
										<span class="m-widget6__text m--align-right m--font-boldest m--font-<?php echo $mt_dta['PersonsTypes_color'] ?>" id="statNumber_<?php echo $mt_dta['PersonsTypes_id'] ?>_F">
											<small></small>
										</span>
									</div>
								<?php
								endforeach;
								?>
							</div>
							<div class="m-widget6__foot">
								<div class="m-widget6__action m--align-right">
									<button type="button" class="btn btn-secondary m-btn m-btn--hover-brand" onclick="loadOfficeNumbers(0)">
										All Offices
									</button>
									<button type="button" class="btn btn-secondary m-btn m-btn--hover-brand" onclick="loadOfficeNumbers(33)">
										National
									</button>
									<button type="button" class="btn btn-secondary m-btn m-btn--hover-brand" onclick="loadOfficeNumbers(37)">
										Beverly Hills
									</button>
								</div>
							</div>
						</div>

					</div>
				</div>
				<?php if ($showPortlet) : ?>
				</div>
			</div>
		<?php endif; ?>
		<script>
			var mapOptions = {
				isPng: true,
				streetViewControl: false,
				scrollwheel: false,
				center: new google.maps.LatLng(39.828175, -98.5795),
				zoom: 4
			};
			var gmap = new google.maps.Map(document.getElementById("m_gmap_1"), mapOptions);
			var infowindow = new google.maps.InfoWindow({
				content: '',
				maxWidth: 350,
				Width: 250
			});
			var marker = new Array();
			var contentString;

			loadOfficeNumbers(0);
			<?php
			$sql = "SELECT * FROM Offices WHERE (office_lat != '' AND office_lng != '')";
			$snd = $this->db->get_multi_result($sql);
			foreach ($snd as $dta) :
			?>
				contentString = 'javascript:loadOfficeNumbers(<?php echo $dta['Offices_id'] ?>);';
				marker[<?php echo $dta['Offices_id'] ?>] = new google.maps.Marker({
					position: new google.maps.LatLng(<?php echo $dta['office_lat'] ?>, <?php echo $dta['office_lng'] ?>),
					map: gmap,
					title: '<?php echo $dta['office_Name'] ?>',
					animation: google.maps.Animation.DROP,
					icon: '/assets/app/media/img/logos/kelleher-logo-mapicon.png',
					winContent: contentString,
					//winType: mtype
				});
				google.maps.event.addListener(marker[<?php echo $dta['Offices_id'] ?>], 'click', function() {
					//jumpToCompanyPerson(this.pid, this.cid);
					var infoContent = '<img src="/assets/app/media/img/logos/kelleher-logo-mapicon.png" height="40" align="right">';
					infoContent += '<div><strong class="infoTitle">Location: ' + this.title + '</strong></div>';
					infoContent += '<div class="info-block">Active Clients: <?php echo number_format($this->countDashboardRecords(array(4), $dta['Offices_id']), 0) ?></div>';
					//infoContent += '<div class="info-block">Active Resources: <?php echo number_format($this->countDashboardRecords(array(10), $dta['Offices_id']), 0) ?></div>';
					//infoContent += '<div class="info-block">Active Participating: <?php echo number_format($this->countDashboardRecords(array(12), $dta['Offices_id']), 0) ?></div>';
					infoContent += '<div class="info-block">Active Leads: <?php echo number_format($this->countDashboardRecords(array(3), $dta['Offices_id']), 0) ?></div>';
					infoContent += '<div class="info-block text-center" style="margin-top:8px;"><a href="' + this.winContent + '" target="_blank">View Office Full Stats</a></div>';
					infowindow.setContent(infoContent);
					infowindow.setPosition(this.position);
					//locationInfoWindow[rope].open(map);
					infowindow.open(gmap, marker[<?php echo $dta['Offices_id'] ?>]);
				});
			<?php
			endforeach;
			?>

			function loadOfficeStats(office_id) {
				var loader = '<div style="margin-top:75px; margin-bottom:75px; text-align:center;"><div><i class="fa fa-circle-o-notch fa-spin"></i> Loading Age Calculations Data...</div><div><small><em>This process may take a few moments, please be patient</div>';
				$('#office_stats_block').html(loader);
				$.post('/ajax/ajax.dashboard.php?action=get-office-stats', {
					oid: office_id
				}, function(data) {
					$('#office_stats_block').html(data);
					mApp.init();
				});
			}

			function loadOfficeNumbers(office_id) {
				mApp.block("#stats_list_data", {
					overlayColor: "#000000",
					type: "loader",
					state: "success",
					message: "Loading Statistical Info..."
				});
				$.post('/ajax/ajax.dashboard.php?action=get-office-numbers', {
					oid: office_id
				}, function(data) {
					$('#statNumber_3').html(data.numbers.slot_3);
					$('#statNumber_3_M small').html(data.numbers.slot_3_M);
					$('#statNumber_3_F small').html(data.numbers.slot_3_F);
					$('#statNumber_4').html(data.numbers.slot_4);
					$('#statNumber_4_M small').html(data.numbers.slot_4_M);
					$('#statNumber_4_F small').html(data.numbers.slot_4_F);
					$('#statNumber_5').html(data.numbers.slot_5);
					$('#statNumber_5_M small').html(data.numbers.slot_5_M);
					$('#statNumber_5_F small').html(data.numbers.slot_5_F);
					$('#statNumber_6').html(data.numbers.slot_6);
					$('#statNumber_6_M small').html(data.numbers.slot_6_M);
					$('#statNumber_6_F small').html(data.numbers.slot_6_F);
					$('#statNumber_7').html(data.numbers.slot_7);
					$('#statNumber_7_M small').html(data.numbers.slot_7_M);
					$('#statNumber_7_F small').html(data.numbers.slot_7_F);
					$('#statNumber_8').html(data.numbers.slot_8);
					$('#statNumber_8_M small').html(data.numbers.slot_8_M);
					$('#statNumber_8_F small').html(data.numbers.slot_8_F);
					$('#statNumber_10').html(data.numbers.slot_10);
					$('#statNumber_10_M small').html(data.numbers.slot_10_M);
					$('#statNumber_10_F small').html(data.numbers.slot_10_F);
					$('#statNumber_12').html(data.numbers.slot_12);
					$('#statNumber_12_M small').html(data.numbers.slot_12_M);
					$('#statNumber_12_F small').html(data.numbers.slot_12_F);
					$('#statNumber_13').html(data.numbers.slot_13);
					$('#statNumber_13_M small').html(data.numbers.slot_13_M);
					$('#statNumber_13_F small').html(data.numbers.slot_13_F);
					$('#statNumber_14').html(data.numbers.slot_14);
					$('#statNumber_14_M small').html(data.numbers.slot_14_M);
					$('#statNumber_14_F small').html(data.numbers.slot_14_F);
					//$('#geo-filter-name').html(data.oname);
					mApp.unblock("#stats_list_data");
					loadOfficeStats(office_id);
					mApp.init();
				}, "json");
			}
		</script>
<?php

	}
}
?>