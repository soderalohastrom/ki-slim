<?php
/* Profile Page */
include_once("class.record.php");
include_once("class.inline.edit.php");
include_once("class.profile.php");
include_once("class.searching.php");
include_once("class.images.php");
include_once("class.notes.php");
include_once("class.backgroundcheck.php");
include_once("class.tasks.php");
include_once("class.encryption.php");
include_once("class.users.php");
include_once("class.recordShare.php");
include_once("class.recordAddress.php");
include_once("class.recordTracking.php");
include_once("class.recordAlerts.php");
include_once("class.recordMMExpectations.php");
include_once("class.recordLinks.php");
include_once("class.recordBio.php");
include_once("class.matching.php");
include_once("class.sessions.php");
$RECORD = new Record($DB);
$IEDIT = new inlineEdit($DB);

$SEARCH = new Searching($DB, $RECORD);
$PROFILE = new Profile($DB, $IEDIT);
$IMAGES = new Images($DB, $RECORD);
$NOTES = new Notes($DB, $RECORD);
$BG = new BackgroundCheck($DB);
$TASKS = new Tasks($DB, $RECORD);
$ENC = new encryption();
$USER = new Users($DB);
$RSHARE = new recordShare($DB);
$RADDRESS = new recordAddress($DB);
$RTRACK = new recordTracking($DB);
$RALERT = new recordAlerts($DB);
$RLINKS = new recordLinks($DB);
$RBIO = new recordBio($DB);
$MATCHING = new Matching($DB, $RECORD);
$SESSION = new Session($DB, $ENC);

$USER_PERMS = $USER->get_userPermissions($_SESSION['system_user_id']);

//print_r($_GET);
//print_r($pageParamaters);

$PERSON_ID = $pageParamaters['params'][0];
$TAB = $pageParamaters['params'][1];

$p_sql = "
SELECT
	Persons.*,
	PersonsImages.*,
	PersonsProfile.*,
	Offices.office_Name,
	Pods.pod_Name,
	(SELECT PersonsTypes_text FROM PersonTypes WHERE PersonsTypes_id=Persons.PersonsTypes_id) as PersonTypeText,
	DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), DateOfBirth)), '%Y')+0 AS RecordAge,
	Addresses.*,
	PersonsPrefs.*
FROM
	Persons
	LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	LEFT JOIN Offices ON Offices.Offices_id=Persons.Offices_id
	LEFT JOIN Pods ON Pods.Pod_id=Persons.Pod_id
	LEFT JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
	LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
WHERE
	Persons.Person_id='" . $PERSON_ID . "'
";
//echo $p_sql;
$PDATA = $DB->get_single_result($p_sql);
//print_r($PDATA);
$PRIMARY_ARRESS_ID = $PDATA['Address_id'];
$bg_quick_check = $BG->get_checkStatus($PERSON_ID);

//echo "PASSWORD:".$ENC->decrypt($PDATA['Persons_password'])."<br>\n";

if ($PDATA['PersonsImages_path'] == '') {
	$PrimaryImage = $RECORD->get_defaultImage($PERSON_ID);
} else {
	$PrimaryImage = "/client_media/" . $RECORD->get_image_directory($PERSON_ID) . "/" . $PERSON_ID . "/" . $PDATA['PersonsImages_path'];
}
$pendingPayments = $RECORD->get_pendingPayments($PERSON_ID);

$PAGE_TITLE = json_encode('PROFILE: ' . $RECORD->get_personName($PERSON_ID) . " (KISS) Kelleher International Software System");
$clearedToView = $RSHARE->check_recordAccess($PERSON_ID, $_SESSION['system_user_id']);
if ($clearedToView) :
?>
	<script src="/assets/vendors/custom/sortable-master/Sortable.min.js" type="text/javascript"></script>
	<script src="https://cdn.jsdelivr.net/npm/clipboard@1/dist/clipboard.min.js" type="text/javascript"></script>
	<script src="/assets/vendors/custom/jQuery.print-master/jQuery.print.js" type="text/javascript"></script>
	<script src="/assets/vendors/custom/tablesorter/dist/js/jquery.tablesorter.min.js" type="text/javascript"></script>
	<link href="/assets/vendors/custom/tablesorter/dist/css/theme.bootstrap_4.min.css" rel="stylesheet" type="text/css" />
	<style>
		a.profile-image-edit {
			top: 10px;
			right: 0;
			margin: 0;
			color: #FFF;
			opacity: .5;
			border-top: #000 solid 0px;
			border-bottom: #000 solid 0px;
			border-right: #000 solid 0px;
			border-left: 0;
			padding: 3px 12px;
			font-size: 12px;
			background: #34bfa3;
			position: relative;
			filter: alpha(opacity=50);
			z-index: 4;
			width: 35px;
			text-align: center;
		}

		a.profile-image-edit2 {
			top: 13px;
			right: 0;
			margin: 0;
			color: #000;
			opacity: .5;
			border: none;
			padding: 3px 10px;
			font-size: 12px;
			background: #06F;
			position: relative;
			filter: alpha(opacity=50);
			z-index: 5;
			width: 35px;
			text-align: center;
		}

		a.profile-image-edit3 {
			top: 16px;
			right: 0;
			margin: 0;
			color: #000;
			opacity: .5;
			border: none;
			padding: 3px 9px;
			font-size: 12px;
			background: #FF0;
			position: relative;
			filter: alpha(opacity=50);
			z-index: 5;
			width: 35px;
			text-align: center;
		}

		.primary-image-wrapper {
			height: 300px;
			width: 250px;
			margin-left: 10%;
		}
	</style>
	<div class="m-content">
		<div class="row">
			<div class="col-xl-3 col-lg-3">
				<div class="m-portlet m-portlet--full-height  ">
					<div class="m-portlet__body">
						<div class="m-card-profile">
							<div class="m-card-profile__title m--hide">Profile: <?php echo $PDATA['FirstName'] ?> <?php echo substr($PDATA['LastName'], 0, 1) ?> | <?php echo $PDATA['PersonTypeText'] ?></div>
							<div>
								<div class="primary-image-wrapper" style="background-image:url(<?php echo $PrimaryImage ?>); background-size:cover;">
									<?php
									if ($bg_quick_check['result'] == 1) {
									?><a href="javascript:;" class="profile-image-edit" data-skin="dark" data-toggle="m-tooltip" data-placement="right" title="Background PASSED"> <i class="fa fa-shield"></i> </a><?php
																																																				?><div style="height:5px;">&nbsp;</div><?php
																	}

																	if ($PDATA['BioConfig'] != '') :
									?><a href="javascript:quickBioPreview();" class="profile-image-edit2" data-skin="dark" data-toggle="m-tooltip" data-placement="right" title="Bio Configured"> <i class="fa fa-clipboard"></i> </a><?php
																																																									?><div style="height:5px;">&nbsp;</div><?php
																	endif;
																	if ($PDATA['OpenRecord'] == 1) :
																		?><a href="javascript:;" class="profile-image-edit3" data-skin="dark" data-toggle="m-tooltip" data-placement="right" title="Open Record"> <i class="fa fa-folder-open"></i> </a><?php
																																																			endif;
																																																				?>
								</div>
								<!--<img src="<?php echo $PrimaryImage ?>" class="img-fluid rounded img-thumbnail" />-->
							</div>
							<div class="m-card-profile__details">
								<span class="m-card-profile__email">
									<?php if ($PDATA['HCS'] == 1) : ?>
										<?php if (in_array(10, $USER_PERMS)) : ?>
											<strong><?php echo $PDATA['FirstName'] ?> <?php echo $PDATA['LastName'] ?></strong>
										<?php else : ?>
											<span class="m--font-danger"><?php echo $PERSON_ID ?></span>
										<?php endif; ?>
									<?php else : ?>
										<strong><?php echo $PDATA['FirstName'] ?> <?php echo $PDATA['LastName'] ?></strong>
									<?php endif; ?>
								</span>
							</div>
						</div>
						<div>&nbsp;</div>
						<div class="row" id="img-preview-area">
							<?php echo $RECORD->render_allImages($PERSON_ID) ?>
						</div>
						<div class="row">
							<div class="col-6">
								<small><a href="javascript:;" onclick="$('#ImagesModal').modal('show');" class="m-link">View All Images</a></small>
							</div>
							<div class="col-6">
								<div id="save-image-order-area" class="text-right" style="display:none;">
									<button class="btn btn-outline-success btn-sm" type="button" onclick="saveImageOrder()">Save Image Order <i class="fa fa-image"></i></button>
								</div>
							</div>
						</div>


						<ul class="m-nav m-nav--hover-bg m-portlet-fit--sides">
							<li class="m-nav__item">
								<a href="#" data-toggle="modal" data-target="#recordModal" class="m-nav__link">
									<i class="m-nav__link-icon flaticon-profile-1"></i>
									<span class="m-nav__link-title">
										<span class="m-nav__link-wrap">
											<span class="m-nav__link-text"><strong><?php echo $PDATA['PersonTypeText'] ?></strong></span>
										</span>
									</span>
								</a>
							</li>
							<?php if ($PDATA['PersonsTypes_id'] == 6) : ?>
								<li class="m-nav__item">
									<a href="javascript:;" class="m-nav__link">
										<i class="m-nav__link-icon fa fa-thermometer"></i>
										<span class="m-nav__link-text">
											Current Freeze Length: <strong><?php echo $RECORD->get_currentFreezeLength($PERSON_ID) ?> Days</strong>
										</span>
									</a>
								</li>
							<?php endif; ?>
							<li class="m-nav__item">
								<a href="#" title="PODS" data-toggle="modal" data-target="#podModal" class="m-nav__link">
									<i class="m-nav__link-icon flaticon-network"></i>
									<span class="m-nav__link-text">
										<strong id="display_POD_name"><?php echo $PDATA['pod_Name'] ?></strong>
									</span>
								</a>
							</li>
							<li class="m-nav__item">
								<a href="#" data-toggle="modal" data-target="#officeModal" class="m-nav__link">
									<i class="m-nav__link-icon flaticon-map-location"></i>
									<span class="m-nav__link-text">
										<strong id="display_Office_name"><?php echo $PDATA['office_Name'] ?></strong>
									</span>
								</a>
							</li>
							<?php if (($PDATA['PersonsTypes_id'] == 3) || ($PDATA['PersonsTypes_id'] == 13)) : ?>
								<li class="m-nav__item">
									<a href="#" data-toggle="modal" data-target="#stageModal" class="m-nav__link">
										<i class="m-nav__link-icon flaticon-users"></i>
										<span class="m-nav__link-text">
											Status: <strong id="display_Stages_name"><?php echo $RECORD->get_leadStage($PDATA['LeadStages_id']) ?></strong>
										</span>
									</a>
								</li>
								<?php echo $PROFILE->renderProfileSidebarSelect(1713, $PDATA['prQuestion_1713'], 'flaticon-folder-2', 'border-top:solid 1px #d6d6d6;') ?>
								<?php echo $PROFILE->renderProfileSidebarSelect(1719, $PDATA['prQuestion_1719'], 'flaticon-list-2', 'border-top:solid 1px #d6d6d6; border-bottom:solid 1px #d6d6d6; ') ?>
								<?php if (in_array(56, $USER_PERMS)) : ?>
									<li class="m-nav__item">
										<a href="#" data-toggle="modal" data-target="#assignedModal" class="m-nav__link">
											<i class="m-nav__link-icon flaticon-user"></i>
											<span class="m-nav__link-text">
												Assigned: <strong id="display_Assigned_userID"><?php echo $RECORD->get_userName($PDATA['Assigned_userID']) ?></strong>
												<?php if ($PDATA['AssignedDate'] != 0) : ?>
													<p><small><?php echo date("m/d/Y", $PDATA['AssignedDate']) ?>&nbsp;&nbsp;<?php echo $RECORD->get_date_diff(date("m/d/Y H:i:s"), date("m/d/Y H:i:s", $PDATA['AssignedDate'])) ?></small></p>
												<?php endif; ?>
											</span>
										</a>
									</li>
								<?php else : ?>
									<li class="m-nav__item">
										<a href="javascript:;" class="m-nav__link">
											<i class="m-nav__link-icon flaticon-user"></i>
											<span class="m-nav__link-text">
												Assigned: <strong id="display_Assigned_userID"><?php echo $RECORD->get_userName($PDATA['Assigned_userID']) ?></strong>
												<?php if ($PDATA['AssignedDate'] != 0) : ?>
													<p><small><?php echo date("m/d/Y", $PDATA['AssignedDate']) ?>&nbsp;&nbsp;<?php echo $RECORD->get_date_diff(date("m/d/Y H:i:s"), date("m/d/Y H:i:s", $PDATA['AssignedDate'])) ?></small></p>
												<?php endif; ?>
											</span>
										</a>
									</li>
								<?php endif; ?>
								<?php echo $RSHARE->render_leftShareLink($PERSON_ID) ?>
								<li class="m-nav__item">
									<a href="#" data-toggle="modal" data-target="#sourceModal" class="m-nav__link" title="Record Source">
										<i class="m-nav__link-icon flaticon-interface-2"></i>
										<span class="m-nav__link-text">
											Source: <strong id="display_Source_name"><?php echo $PDATA['HearAboutUs']; ?></strong>
										</span>
									</a>
								</li>

								<?php
								$contract_sql = "SELECT * FROM PersonsContract WHERE Person_id='" . $PERSON_ID . "' AND Contract_status='1'";
								$contract_snd = $DB->get_single_result($contract_sql);
								if (!isset($contract_snd['empty_result'])) :
								?>
									<li class="m-nav__item list-group-item-success" id="ContractIsActive">
										<a href="/contractgen/<?php echo $PERSON_ID ?>/<?php echo $contract_snd['Contract_id'] ?>" class="m-nav__link ">
											<i class="m-nav__link-icon flaticon-exclamation m--font-success"></i>
											<span class="m-nav__link-text">
												<strong class="m--font-success">Active Contract</strong>
											</span>
										</a>
									</li>
								<?php
								endif;
								?>

								<?php if ($PDATA['OpenRecord'] == 1) : ?>
									<li class="m-nav__item list-group-item-warning" id="RecordIsOpen">
										<a href="javascript:;" class="m-nav__link " onclick="quickTab_backoffice();">
											<i class="m-nav__link-icon la la-clipboard m--font-default"></i>
											<span class="m-nav__link-text">
												<strong class="m--font-default">OPEN RECORD</strong>
											</span>
										</a>
									</li>
								<?php endif; ?>

								<?php if (in_array(58, $USER_PERMS)) : ?>
									<li class="m-nav__item">
										<a href="#" data-toggle="modal" data-target="#prospectMeetModal" class="m-nav__link" title="Met with Prospect">
											<i class="m-nav__link-icon flaticon-speech-bubble-1"></i>
											<span class="m-nav__link-text">
												Met Prospect: <strong><?php echo $PDATA['prQuestion_1727'] ?></strong>
												<?php if ($PDATA['prQuestion_1728'] != '') : ?>
													<p><small><?php echo $PDATA['prQuestion_1728'] ?></small></p>
												<?php endif; ?>
											</span>
										</a>
									</li>
								<?php else : ?>
									<li class="m-nav__item">
										<a href="javascript:;" class="m-nav__link">
											<i class="m-nav__link-icon 	flaticon-speech-bubble-1"></i>
											<span class="m-nav__link-text">
												Met Prospect: <strong><?php echo $PDATA['prQuestion_1727'] ?></strong>
												<?php if ($PDATA['prQuestion_1728'] != '') : ?>
													<p><small><?php echo $PDATA['prQuestion_1728'] ?></small></p>
												<?php endif; ?>
											</span>
										</a>
									</li>
								<?php endif; ?>

								<!-- CLIENT VERSION -->
							<?php else : ?>
								<?php echo $PROFILE->renderProfileSidebarSelect(1719, $PDATA['prQuestion_1719'], 'flaticon-list-2', 'border-top:solid 1px #d6d6d6; border-bottom:solid 1px #d6d6d6; ') ?>
								<li class="m-nav__item">
									<a href="#" data-toggle="modal" data-target="#colorModal" class="m-nav__link">
										<i class="m-nav__link-icon flaticon-interface-9"></i>
										<span class="m-nav__link-title">
											<span class="m-nav__link-wrap">
												<span class="m-nav__link-text" id="display_PersonColor">
													<?php 
													foreach (  explode('|',$PDATA['Color_id']) as $color ){
														$mm_stat_sql = "SELECT * FROM PersonsColors WHERE Color_id='" . $color . "'";
														$mm_stat_dta = $DB->get_single_result($mm_stat_sql);
														echo '<span class="m-badge m-badge--metal m-badge--wide" style="background-color:'.$mm_stat_dta['Color_hex'].';">'.$mm_stat_dta['Color_title'].'</span>';
													} ?>
													
												</span>
											</span>
										</span>
									</a>
								</li>
								<?php if (in_array(56, $USER_PERMS)) : ?>
									<li class="m-nav__item">
										<a href="#" data-toggle="modal" data-target="#assignedModal" class="m-nav__link">
											<i class="m-nav__link-icon flaticon-user"></i>
											<span class="m-nav__link-text">
												Market Director: <strong id="display_Assigned_userID"><?php echo $RECORD->get_userName($PDATA['Assigned_userID']) ?></strong>
											</span>
										</a>
									</li>
								<?php else : ?>
									<li class="m-nav__item">
										<a href="javascript:;" class="m-nav__link">
											<i class="m-nav__link-icon flaticon-user"></i>
											<span class="m-nav__link-text">
												Market Director: <strong id="display_Assigned_userID"><?php echo $RECORD->get_userName($PDATA['Assigned_userID']) ?></strong>
											</span>
										</a>
									</li>
								<?php endif; ?>
								<li class="m-nav__item">
									<a href="#" data-toggle="modal" data-target="#matchmakerModal" class="m-nav__link">
										<i class="m-nav__link-icon la la-heart-o"></i>
										<span class="m-nav__link-text">
											Relationship Manager: <strong id="display_MM_userID"><?php echo $RECORD->get_userName($PDATA['Matchmaker_id']) ?>&nbsp;</strong>
										</span>
									</a>
								</li>
								<li class="m-nav__item">
									<a href="#" data-toggle="modal" data-target="#matchmakerModal2" class="m-nav__link">
										<i class="m-nav__link-icon la la-heart"></i>
										<span class="m-nav__link-text">
											Network Developer: <strong id="display_MM2_userID"><?php echo $RECORD->get_userName($PDATA['Matchmaker2_id']) ?>&nbsp;</strong>
										</span>
									</a>
								</li>
								<li class="m-nav__item">
									<a href="#" data-toggle="modal" data-target="#memTypeModal" class="m-nav__link">
										<i class="m-nav__link-icon flaticon-users"></i>
										<span class="m-nav__link-text">
											Membership Type: <br /><strong id="display_memType"><?php echo str_replace("|", "<br>", (($PDATA['prQuestion_657'] == '') ? 'NONE' : $PDATA['prQuestion_657'])) ?></strong>
										</span>
									</a>
								</li>
								<li class="m-nav__item">
									<a href="#" data-toggle="modal" data-target="#validModal" class="m-nav__link">
										<i class="m-nav__link-icon flaticon-user-ok"></i>
										<span class="m-nav__link-text">
											<?php echo $RECORD->render_clientStatus($PERSON_ID) ?>
										</span>
									</a>
								</li>
								<li class="m-nav__item">
									<a href="#" class="m-nav__link">
										<i class="m-nav__link-icon flaticon-network"></i>
										<span class="m-nav__link-text">
											Completed Dates: <strong><?php echo $MATCHING->count_myCompletedDates($PERSON_ID) ?></strong>
										</span>
									</a>
								</li>
								<?php echo $RSHARE->render_leftShareLink($PERSON_ID) ?>
								<li class="m-nav__item">
									<a href="#" data-toggle="modal" data-target="#sourceModal" class="m-nav__link" title="Record Source">
										<i class="m-nav__link-icon flaticon-interface-2"></i>
										<span class="m-nav__link-text">
											Source: <strong id="display_Source_name"><?php echo $PDATA['HearAboutUs']; ?></strong>
										</span>
									</a>
								</li>

								<?php if (in_array(58, $USER_PERMS)) : ?>
									<li class="m-nav__item">
										<a href="#" data-toggle="modal" data-target="#prospectMeetModal" class="m-nav__link" title="Met with Prospect">
											<i class="m-nav__link-icon flaticon-speech-bubble-1"></i>
											<span class="m-nav__link-text">
												Met Prospect: <strong><?php echo $PDATA['prQuestion_1727'] ?></strong>
												<?php if ($PDATA['prQuestion_1728'] != '') : ?>
													<p><small><?php echo $PDATA['prQuestion_1728'] ?></small></p>
												<?php endif; ?>
											</span>
										</a>
									</li>
								<?php else : ?>
									<li class="m-nav__item">
										<a href="javascript:;" class="m-nav__link">
											<i class="m-nav__link-icon 	flaticon-speech-bubble-1"></i>
											<span class="m-nav__link-text">
												Met Prospect: <strong><?php echo $PDATA['prQuestion_1727'] ?></strong>
												<?php if ($PDATA['prQuestion_1728'] != '') : ?>
													<p><small><?php echo $PDATA['prQuestion_1728'] ?></small></p>
												<?php endif; ?>
											</span>
										</a>
									</li>
								<?php endif; ?>

								<?php
								$contract_sql = "SELECT * FROM PersonsContract WHERE Person_id='" . $PERSON_ID . "' AND Contract_status='1'";
								$contract_snd = $DB->get_single_result($contract_sql);
								if (!isset($contract_snd['empty_result'])) :
								?>
									<li class="m-nav__item list-group-item-success" id="ContractIsActive">
										<a href="/contractgen/<?php echo $PERSON_ID ?>/<?php echo $contract_snd['Contract_id'] ?>" class="m-nav__link ">
											<i class="m-nav__link-icon flaticon-exclamation m--font-success"></i>
											<span class="m-nav__link-text">
												<strong class="m--font-success">Active Contract</strong>
											</span>
										</a>
									</li>

									<?php if ($PDATA['OpenRecord'] == 1) : ?>
										<li class="m-nav__item list-group-item-warning" id="RecordIsOpen">
											<a href="javascript:;" class="m-nav__link " onclick="quickTab_backoffice();">
												<i class="m-nav__link-icon flaticon-folder-2 m--font-default"></i>
												<span class="m-nav__link-text">
													<strong class="m--font-default">OPEN RECORD</strong>
												</span>
											</a>
										</li>
									<?php endif; ?>
								<?php
								endif;
								?>
							<?php endif; ?>
							<li class="m-nav__item <?php echo (($PDATA['VIP'] == 1) ? '' : 'm--hide') ?>" id="VIP_record">
								<a href="javascript:;" class="m-nav__link">
									<i class="m-nav__link-icon flaticon-exclamation-1 m--font-danger"></i>
									<span class="m-nav__link-text">
										<strong class="m--font-danger">VIP CLIENT</strong>
									</span>
								</a>
							</li>
							<li class="m-nav__item <?php echo (($PDATA['Monitored'] == 1) ? '' : 'm--hide') ?>" id="Monitored_record">
								<a href="javascript:;" class="m-nav__link">
									<i class="m-nav__link-icon flaticon-clock m--font-success"></i>
									<span class="m-nav__link-text">
										<strong class="m--font-success">MONITORED RECORD</strong>
									</span>
								</a>
							</li>
							<li class="m-nav__item <?php echo (($PDATA['RequirePM'] == 1) ? '' : 'm--hide') ?>" id="TOSREQ_record">
								<a href="javascript:;" class="m-nav__link">
									<i class="m-nav__link-icon flaticon-signs-1 m--font-brand"></i>
									<span class="m-nav__link-text">
										<strong class="m--font-brand">TOS AGREED</strong>
									</span>
								</a>
							</li>
							<li class="m-nav__item <?php echo (($PDATA['HCS'] == 1) ? '' : 'm--hide') ?>" id="HCS_record">
								<a href="javascript:;" class="m-nav__link">
									<i class="m-nav__link-icon flaticon-alarm m--font-danger"></i>
									<span class="m-nav__link-text">
										<strong class="m--font-danger">HIGH PRIORITY CLIENT</strong>
									</span>
								</a>
							</li>
							<?php echo $RLINKS->render_sideBarNav($PERSON_ID) ?>
						</ul>

					</div>
				</div>
			</div>
			<?php echo $PROFILE->renderProfileSidebarModal() ?>
			<div class="col-xl-9 col-lg-9">
				<div class="m-portlet m-portlet--full-height m-portlet--tabs  ">
					<div class="m-portlet__head">
						<div class="m-portlet__head-tools">
							<ul class="nav nav-tabs m-tabs m-tabs-line   m-tabs-line--left m-tabs-line--primary" role="tablist">
								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link active" data-toggle="tab" href="#user_profile_tab_main" role="tab" title="Main"><i class="flaticon-profile-1"></i></a>
								</li>
								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link" data-toggle="tab" href="#user_profile_tab_profile" role="tab" title="Profile"><i class="flaticon-profile"></i></a>
								</li>
								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link" data-toggle="tab" href="#user_profile_tab_schedule" role="tab" title="Schedule Appointment"><i class="flaticon-time-3"></i></a>
								</li>
								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link" data-toggle="tab" href="#user_profile_tab_office" role="tab">
										<?php if ($pendingPayments != 0) : ?>
											<div style="font-size:10px; float:right; margin-top:-1px; margin-left:-10px;"><span class="m-badge m-badge--info" style="min-width:5px; border-radius:10px; line-height:12px; min-height:13px;" title="This record has pending payment(s)"><?php echo $pendingPayments ?></span></div>
										<?php endif; ?>
										<i class="flaticon-user-settings"></i>
									</a>
								</li>
								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link" data-toggle="tab" href="#user_profile_tab_intros" role="tab"><i class="la la-heart-o"></i></a>
								</li>
								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link" data-toggle="tab" href="#user_profile_tab_history" role="tab" title="History">
										<i class="flaticon-layers"></i>
									</a>
								</li>
							</ul>
						</div>
						<div class="m-portlet__head-tools">
							<ul class="m-portlet__nav">
								<li class="m-portlet__nav-item m-portlet__nav-item--last">
									<a href="javascript:document.location.reload(true)" class="m-portlet__nav-link btn btn-lg btn-secondary  m-btn m-btn--icon m-btn--icon-only m-btn--pill"><i class="flaticon-refresh"></i></a>
									<div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="click" aria-expanded="true" data-dropdown-persistent="true">
										<a href="#" class="m-portlet__nav-link btn btn-lg btn-secondary  m-btn m-btn--icon m-btn--icon-only m-btn--pill  m-dropdown__toggle">
											<i class="la la-gear"></i>
										</a>
										<div class="m-dropdown__wrapper">
											<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
											<div class="m-dropdown__inner">
												<div class="m-dropdown__body">
													<div class="m-dropdown__content">
														<ul class="m-nav">
															<li class="m-nav__section m-nav__section--first">
																<span class="m-nav__section-text">
																	Actions
																</span>
															</li>
															<li class="m-nav__item">
																<a href="javascript:openAction(0)" class="m-nav__link">
																	<i class="m-nav__link-icon flaticon-add"></i>
																	<span class="m-nav__link-text">
																		Set Task
																	</span>
																</a>
															</li>

															<li class="m-nav__item">
																<a href="javascript:;" class="m-nav__link loadNoteForm" data-type="Lead Action">
																	<i class="m-nav__link-icon flaticon-tool-1"></i>
																	<span class="m-nav__link-text">
																		Set Action
																	</span>
																</a>
															</li>
															<li class="m-nav__item">
																<a href="javascript:;" class="m-nav__link loadNoteForm" data-type="Call Note">
																	<i class="m-nav__link-icon flaticon-music-2"></i>
																	<span class="m-nav__link-text">
																		Call
																	</span>
																</a>
															</li>
															<li class="m-nav__item">
																<a href="javascript:;" class="m-nav__link loadNoteForm" data-type="Client Note">
																	<i class="m-nav__link-icon flaticon-file"></i>
																	<span class="m-nav__link-text">
																		New Note
																	</span>
																</a>
															</li>
															<li class="m-nav__item">
																<a href="/send-email/<?php echo $PERSON_ID ?>" class="m-nav__link">
																	<i class="m-nav__link-icon la la-envelope"></i>
																	<span class="m-nav__link-text">
																		Email
																	</span>
																</a>
															</li>

															<li class="m-nav__item">
																<a href="#" data-toggle="modal" data-target="#imgUploadModal" class="m-nav__link">
																	<i class="m-nav__link-icon flaticon-multimedia"></i>
																	<span class="m-nav__link-text">
																		Add Pic
																	</span>
																</a>
															</li>
															<li class="m-nav__section m-nav__section--first">
																<span class="m-nav__section-text">
																	Sales
																</span>
															</li>
															<li class="m-nav__item">
																<a href="/contractgen/<?php echo $PERSON_ID ?>/" class="m-nav__link">
																	<i class="m-nav__link-icon flaticon-file-1"></i>
																	<span class="m-nav__link-text">
																		Add Contract
																	</span>
																</a>
															</li>
															<li class="m-nav__item">
																<a href="javascript:;" onclick="openPayment(0)" class="m-nav__link">
																	<i class="m-nav__link-icon flaticon-coins"></i>
																	<span class="m-nav__link-text">
																		Add Payment
																	</span>
																</a>
															</li>
															<li class="m-nav__item">
																<a href="/newsale/<?php echo $PERSON_ID ?>/0" class="m-nav__link">
																	<i class="m-nav__link-icon flaticon-music-1"></i>
																	<span class="m-nav__link-text">
																		Add Sale
																	</span>
																</a>
															</li>

															<li class="m-nav__section">
																<span class="m-nav__section-text">
																	Views
																</span>
															</li>

															<li class="m-nav__item">
																<a href="#" data-toggle="modal" data-target="#linksModal" class="m-nav__link">
																	<i class="m-nav__link-icon la la-link"></i>
																	<span class="m-nav__link-text">
																		Links
																	</span>
																</a>
															</li>

															<li class="m-nav__item">
																<a href="javascript:openPrintView();" class="m-nav__link">
																	<i class="m-nav__link-icon la la-print"></i>
																	<span class="m-nav__link-text">
																		Print View
																	</span>
																</a>
															</li>

															<li class="m-nav__section">
																<span class="m-nav__section-text">
																	Quick Emails
																</span>
															</li>
															<!--
                                                        <li class="m-nav__item">
                                                            <a href="javascript:sendPortalPasswordReset('<?php echo $PDATA['Email'] ?>')" class="m-nav__link">
                                                                <i class="m-nav__link-icon la la-envelope"></i>
                                                                <span class="m-nav__link-text">
                                                                    Password Reset
                                                                </span>
                                                            </a>
                                                        </li>
                                                        -->
															<li class="m-nav__item">
																<a href="javascript:sendQuickEmail('<?php echo $PERSON_ID ?>', '199', 'Are you sure you want to send this record a email with their Client Portal Access information?', 'Client Portal Access')" class="m-nav__link">
																	<i class="m-nav__link-icon la la-envelope"></i>
																	<span class="m-nav__link-text">
																		Client Portal Access
																	</span>
																</a>
															</li>

														</ul>
													</div>
												</div>
											</div>
										</div>
									</div>

								</li>
							</ul>
						</div>
					</div>
					<div class="m-portlet__body">
						<div class="tab-content">
							<?php $RECORD->checkForDuplicateEmail($PDATA['Email'], $PERSON_ID); ?>

							<div class="tab-pane active" id="user_profile_tab_main">
								<div class="pull-right" style="margin-top: -10px;">

									<div class="pull-right"><?php echo $RALERT->check_leadAge($PERSON_ID) ?></div>
									<small class="m--font-secondary">Created<br /><?php echo date("m/d/y h:ia", $PDATA['DateCreated']) ?></small>
									<?php echo (($PDATA['CreatedBy'] != 0) ? '<div style="margin-top:-7px;"><small><sup>by</sup>&nbsp;<font class="m--font-danger">' . $RECORD->get_userName($PDATA['CreatedBy']) . '</font></small></div>' : '') ?>

								</div>
								<div id="name-badge" class="row">
									<?php
									//$bg_quick_check['result'] = 0;                                
									if ($bg_quick_check['check']) :
										?>
										<h3> 
										<?php 
										if ($bg_quick_check['result'] == 1) :
											?><i class="fa fa-shield m--font-success" style="font-size:1.0em;" data-skin="dark" data-toggle="m-tooltip" data-placement="top" title="Background PASSED">&nbsp;</i>
											<?php
											elseif ($bg_quick_check['result'] == 0) :
												?><i class="fa fa-exclamation-triangle m--font-danger" style="font-size:1.0em;" data-skin="dark" data-toggle="m-tooltip" data-placement="top" title="Background FAILED">&nbsp;</i>
												<?php
											endif; ?>
										</h3>
									<?php else: ?>
										<a href="#" data-toggle="modal" data-target="#BackgroundCheckModal" style="padding-top:5px; padding-left:15px" class="btn btn-info m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air">
													<span>
														<i class="flaticon-alert-2"></i>
														<span>NOT RUN</span>
													</span>
												</a>
												<div class="modal fade" id="BackgroundCheckModal" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="backgroundCheckModalLabel" aria-hidden="true">
													<div class="modal-dialog modal-lg" role="document">
														<div class="modal-content">
															<div class="modal-header">
																<h5 class="modal-title" id="backgroundCheckModalLabel">Background Check</h5>
																<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																	<span aria-hidden="true">&times;</span>
																</button>
															</div>
															<div class="modal-body">
		
																<span id="bg_check_disclaimer">
																	<p>The information in this report is derived from records that may or may not be in accordance with the Fair Credit Report Act (FCRA, Public Law 91-508, Title VI). This information may not be used for insurance, or credit evaluation and if used for employment purposes or in connection with other legitimate needs it is agreed that the information is for legitimate informational purposes only. The depth of information available varies. Final verification of an individual's identity and proper use of report contents are the user's responsibility.</p>
																	<p>The information in this report such as the public records and criminal records, is compiled from and processed by various third-party sources. IntegraScan does not guarantee, warrant or assume any responsibility for the accuracy of the information obtained from other sources and shall not be liable for any losses or injuries now or in the future resulting from or relating to the information provided herein.</p>
																	<p class="text-center">
																		<button style="background-color:red" type="button" id="btnRunBGCheck" class="btn btn-primary btn-lg" onclick='location.href="/check/bgcheck.php?personid=<?php echo ($PERSON_ID); ?>"'>Run Background Check</button>
																	</p>
		
																</span>
		
		
															</div>
															<div class="modal-footer">
																<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
															</div>
														</div>
													</div>
												</div>
									<?php endif; ?>
									
								<h3 >
									<?php if ($PDATA['HCS'] == 1) : ?>
										<?php if (in_array(10, $USER_PERMS)) : ?>
											<?php echo $IEDIT->render_basicField('text', 'FirstName', $PERSON_ID, $PDATA['FirstName'], $PDATA['FirstName'], '#575962', '/ajax/inline.basic.php') ?>
											<?php echo $IEDIT->render_basicField('text', 'LastName', $PERSON_ID, $PDATA['LastName'], $PDATA['LastName'], '#575962', '/ajax/inline.basic.php') ?>
										<?php else : ?>
											<span class="m--font-danger"><?php echo $PERSON_ID ?></span>
										<?php endif; ?>
									<?php else : ?>
										<?php echo $IEDIT->render_basicField('text', 'FirstName', $PERSON_ID, $PDATA['FirstName'], $PDATA['FirstName'], '#575962', '/ajax/inline.basic.php') ?>
										<?php echo $IEDIT->render_basicField('text', 'LastName', $PERSON_ID, $PDATA['LastName'], $PDATA['LastName'], '#575962', '/ajax/inline.basic.php') ?>
									<?php endif; ?>
								</h3>
									</div>
								<div class="row">
									<div class="col-sm-6">
										<div class="profileValueBlock">
											Record ID: <span class="m--font-primary"><?php echo $PERSON_ID ?></span>
										</div>
										<div class="profileValueBlock">
											<a href="#" id="AddressBlock" data-toggle="modal" data-target="#addressModal"><?php echo ((($PDATA['City'] == '') && ($PDATA['City'] == '')) ? '<span class="m-badge m-badge--warning m-badge--wide">LOCATION UNKNOWN</span>' : $PDATA['City'] . ', ' . $PDATA['State'] . ' ' . $PDATA['Country']) ?></a>
											&nbsp;<?php echo (($PDATA['GeoLocationStatus'] == 200) ? '<i class="fa fa-map-marker" title="This address has been geo-located"></i>' : '<i class="fa fa-info-circle" title="This address has been NOT been geo-located and will not be able to be included in any distance searches."></i>') ?>
										</div>
										<?php if ($PDATA['HCS'] == 1) : ?>
											<?php if (in_array(10, $USER_PERMS)) : ?>
												<div class="profileValueBlock"><?php echo $IEDIT->render_basicField('text', 'Email', $PERSON_ID, $PDATA['Email'], $PDATA['Email'], '/ajax/inline.basic.php') ?></div>
											<?php else : ?>
												<div class="profileValueBlock"><span class="m--font-danger">[REDACTED]</span></div>
											<?php endif; ?>
										<?php else : ?>
											<div class="profileValueBlock"><?php echo $IEDIT->render_basicField('text', 'Email', $PERSON_ID, $PDATA['Email'], $PDATA['Email'], '/ajax/inline.basic.php') ?></div>
										<?php endif; ?>

										<?php if ($PDATA['HCS'] == 1) : ?>
											<?php if (in_array(10, $USER_PERMS)) : ?>
												<div class="profileValueBlock">
													<a href="javascript:void($('#otherPhones').toggle());" class="btn btn-secondary m-btn m-btn--icon btn-sm m-btn--icon-only pull-right" style="margin-left:4px;"><i class="fa fa-chevron-down"></i></a>
													<a href="#" data-toggle="modal" data-target="#phonesModal" class="btn btn-secondary m-btn m-btn--icon btn-sm m-btn--icon-only pull-right"><i class="fa fa-plus"></i></a>
													<?php echo $RECORD->get_primaryPhone($PERSON_ID, true) ?>
													<div id="otherPhones" style="display:none;">
														<?php echo $RECORD->get_otherPhone($PERSON_ID, true) ?>
													</div>
												</div>
											<?php endif; ?>
										<?php else : ?>
											<div class="profileValueBlock">
												<a href="javascript:void($('#otherPhones').toggle());" class="btn btn-secondary m-btn m-btn--icon btn-sm m-btn--icon-only pull-right" style="margin-left:4px;"><i class="fa fa-chevron-down"></i></a>
												<a href="#" data-toggle="modal" data-target="#phonesModal" class="btn btn-secondary m-btn m-btn--icon btn-sm m-btn--icon-only pull-right"><i class="fa fa-plus"></i></a>
												<?php echo $RECORD->get_primaryPhone($PERSON_ID, true) ?>
												<div id="otherPhones" style="display:none;">
													<?php echo $RECORD->get_otherPhone($PERSON_ID, true) ?>
												</div>
											</div>
										<?php endif; ?>

										<div class="profileValueBlock">Gender: <?php echo $IEDIT->render_basicField('select', 'Gender', $PERSON_ID, $PDATA['Gender'], $PDATA['Gender'], '', '/ajax/inline.basic.php') ?></div>
										<div class="profileValueBlock">
											Age: <?php echo $IEDIT->render_basicField('text', 'DateOfBirth', $PERSON_ID, $PDATA['DateOfBirth'], $RECORD->get_personAge($PDATA['DateOfBirth']), '', '/ajax/inline.basic.php') ?>
											
										</div>
										<div class="profileValueBlock">Height: <?php echo $IEDIT->render_customField(621, $PDATA['prQuestion_621'], $PDATA['prQuestion_621'], $PERSON_ID) ?></div>
										<div class="profileValueBlock">Weight: <?php echo $IEDIT->render_customField(622, $PDATA['prQuestion_622'], $PDATA['prQuestion_622'], $PERSON_ID) ?></div>
										<div class="profileValueBlock">Occupation: <?php echo $IEDIT->render_basicField('text', 'Occupation', $PERSON_ID, $PDATA['Occupation'], $PDATA['Occupation'], '/ajax/inline.basic.php') ?></div>
										<div class="profileValueBlock">Income: <?php echo $IEDIT->render_customField(631, $PDATA['prQuestion_631'], $PDATA['prQuestion_631'], $PERSON_ID) ?></div>
										<div class="profileValueBlock">Have Children: <?php echo $IEDIT->render_customField(632, $PDATA['prQuestion_632'], $PDATA['prQuestion_632'], $PERSON_ID) ?></div>
										<div class="profileValueBlock">Religion: <?php echo $IEDIT->render_customField(637, $PDATA['prQuestion_637'], $PDATA['prQuestion_637'], $PERSON_ID) ?></div>
									</div>
									<div class="col-sm-6">
										<h6>ACCESS INFO</h6>
										<?php if ($PDATA['HCS'] == 1) : ?>
											<?php if (in_array(10, $USER_PERMS)) : ?>
												<div class="profileValueBlock">U: <strong><?php echo $PDATA['Email'] ?></strong></div>
											<?php else : ?>
												<div class="profileValueBlock"><span class="m--font-danger">[REDACTED]</span></div>
											<?php endif; ?>
										<?php else : ?>
											<div class="profileValueBlock">U: <strong><?php echo $PDATA['Email'] ?></strong></div>
										<?php endif; ?>
										<div class="profileValueBlock">P: <strong><?php echo $IEDIT->render_basicField('text', 'Persons_password', $PERSON_ID, '', '*************', '/ajax/inline.basic.php') ?></strong></div>
										<hr style="margin-top:.25rem; margin-bottom:.25rem;" />
										<div class="profileValueBlock row">
											<div class="col-4">Last Login:</div>
											<div class="col-8"><?php echo (($PDATA['LastLogin'] == 0) ? '&nbsp;' : date("m/d/Y h:ia", $PDATA['LastLogin'])) ?></div>
										</div>
										<div class="profileValueBlock row">
											<div class="col-4">Last View:</div>
											<div class="col-8"><?php echo $RTRACK->get_lastRecordView($PERSON_ID) ?></div>
										</div>
										<hr style="margin-top:.25rem; margin-bottom:.25rem;" />
										<?php if ($PDATA['PersonsTypes_id'] == 3) : ?><div class="profileValueBlock">Potential: <?php echo $IEDIT->render_customField(1729, $PDATA['prQuestion_1729'], $PDATA['prQuestion_1729'], $PERSON_ID) ?></div> <?php endif; ?>
										<div class="profileValueBlock">Rating: <?php echo $IEDIT->render_customField(664, $PDATA['prQuestion_664'], $PDATA['prQuestion_664'], $PERSON_ID) ?></div>
										<div class="profileValueBlock">Seeking: <?php echo $PDATA['prefQuestion_Gender'] ?> <?php echo str_replace("|", " to ", $PDATA['prefQuestion_age_floor']) ?></div>
										<div class="profileValueBlock">Will Travel: <?php echo $IEDIT->render_customField(653, $PDATA['prQuestion_653'], $PDATA['prQuestion_653'], $PERSON_ID) ?></div>
										<div class="profileValueBlock">Want Children: <?php echo $IEDIT->render_customField(634, $PDATA['prQuestion_634'], $PDATA['prQuestion_634'], $PERSON_ID) ?></div>
										<div class="profileValueBlock">Location Pref: <?php echo $IEDIT->render_customField(678, $PDATA['prQuestion_678'], $PDATA['prQuestion_678'], $PERSON_ID) ?></div>
										<?php if (in_array($PDATA['prQuestion_631'], $RECORD->highIncomes)) : ?>
											<div class="profileValueBlock m--font-warning">High Income Lead: <?php echo $IEDIT->render_basicField('select', 'isHighIncomeLead', $PERSON_ID, $PDATA['isHighIncomeLead'], (($PDATA['isHighIncomeLead'] == 0) ? 'No' : 'Yes'), '') ?></div>
										<?php endif; ?>
									</div>
								</div>
								<div class="row" style="margin-top:10px;">
									<div class="col-sm-6">
										<a href="/send-email/<?php echo $PERSON_ID ?>" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x" data-toggle="m-popover" data-placement="top" data-content="Send this record an email">
											<i class="la la-envelope"></i>
										</a>
										&nbsp;
										<a href="javascript:openAction(0)" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x" data-toggle="m-popover" data-placement="top" data-content="Add a task to this record">
											<i class="la la-calendar-plus-o"></i>
										</a>
										&nbsp;
										<a href="javascript:;" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x loadNoteForm" data-type="Call Note" data-toggle="m-popover" data-placement="top" data-content="Add call note to record">
											<i class="la la-phone-square"></i>
										</a>
										&nbsp;
										<a href="javascript:;" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x loadNoteForm" data-type="Client Note" data-toggle="m-popover" data-placement="top" data-content="Add client note to record">
											<i class="la la-sticky-note-o"></i>
										</a>
										&nbsp;
										<a href="javascript:;" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x loadNoteForm" data-type="Lead Action" data-toggle="m-popover" data-placement="top" data-content="Add lead action to record">
											<i class="la la-rocket"></i>
										</a>
										&nbsp;
										<a href="javascript:;" id="quick-action-photo" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x" data-toggle="modal" data-target="#imgUploadModal" data-trigger="hover" data-placement="top" data-content="Add photo to this record">
											<i class="la la-cloud-upload"></i>
										</a>
									</div>
									<div class="col-sm-6 text-right">
										<?php if (in_array(84, $USER_PERMS)) : ?>
											<form id="jumpToForm" action="https://clients.kelleher-international.com/jumpToFromKISS.php" method="post" target="_blank">
												<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
												<input type="hidden" name="uid" value="<?php echo $_SESSION['system_user_id'] ?>" />
												<input type="hidden" name="kiss_token" id="client_kiss_token" value="" />
												<button type="submit" class="btn btn-outline-success m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x" data-toggle="m-popover" data-placement="top" data-content="Sign into Client Portal as this record.">
													<i class="flaticon-user-ok"></i>
												</button>
												&nbsp;
												<a href="javascript:openBioView();" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x" data-toggle="m-popover" data-placement="top" data-content="View Bio">
													<i class="la la-eye"></i>
												</a>
												&nbsp;
												<a href="javascript:openPrintView();" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x" data-toggle="m-popover" data-placement="top" data-content="View Print Profile">
													<i class="la la-user"></i>
												</a>
												&nbsp;
												<a href="/findfor/<?php echo $PERSON_ID ?>" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x" data-toggle="m-popover" data-placement="top" data-content="Search for a Match">
													<i class="la la-heart-o"></i>
												</a>
												&nbsp;
											</form>
										<?php else : ?>
											<a href="javascript:openBioView();" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x" data-toggle="m-popover" data-placement="top" data-content="View Bio">
												<i class="la la-eye"></i>
											</a>
											&nbsp;
											<a href="javascript:openPrintView();" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x" data-toggle="m-popover" data-placement="top" data-content="View Print Profile">
												<i class="la la-user"></i>
											</a>
											&nbsp;
											<a href="/findfor/<?php echo $PERSON_ID ?>" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only m-btn--outline-2x" data-toggle="m-popover" data-placement="top" data-content="Search for a Match">
												<i class="la la-heart-o"></i>
											</a>
											&nbsp;
										<?php endif; ?>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12" style="padding-top:10px;">
										<?php echo $RALERT->checkforRecordAlerts($PERSON_ID) ?>
									</div>
								</div>
								<div class="m-portlet__body-separator"></div>
								<p id="action-list-display">&nbsp;</p>
								<h5>
									<div class="pull-right" id="display_lastNote_date">&nbsp;</div>
									LAST NOTE/ACTION
								</h5>

								<div id="display_lastNote_body">
									<?php if ($PDATA['LastNoteAction'] != '') : ?>
										<?php $lastNoteAction = json_decode($PDATA['LastNoteAction'], true); ?>

										<?php
										ob_start();
										if ($lastNoteAction['node'] == 'EMAIL_LINK') {
											$msg_sql = "SELECT Messages_opened FROM Messages WHERE Messages_commId='" . $lastNoteAction['hID'] . "' LIMIT 1 ";
											//echo $msg_sql."<br>\n";
											$msg_dta = $DB->get_single_result($msg_sql);
											$msg_status = $msg_dta['Messages_opened'];
											if ($msg_status == 0) {
										?><i class="fa fa-envelope-o m--font-danger" data-container="body" data-toggle="m-popover" data-placement="top" data-content="This email has not been opened"></i><?php
																																																		} else {
																																																			?><i class="fa fa-envelope-open-o m--font-success" data-container="body" data-toggle="m-popover" data-placement="top" data-content="This email has been opened"></i><?php
																																																		}
																																																	} else {
																																																			?>&nbsp;<?php
																																																	}
																																																	$emailStatus = ob_get_clean();
																																																	$LNA_from = '';
																																																	if (array_key_exists('hFrom', $lastNoteAction)) {
																																																		$LNA_from = trim($lastNoteAction['hFrom']);
																																																	}
												?>
												<div data-id="<?php echo $lastNoteAction['hID'] ?>" data-node="<?php echo $lastNoteAction['node'] ?>" class="historyLink list-group-item list-group-item-action flex-column align-items-start">
													<div class="d-flex w-100 justify-content-between">
														<h6 class="mb-1"><?php echo $lastNoteAction['hType'] ?> &gt; <?php if ($lastNoteAction['hType'] == 'SMS') { ?><span id="LNA_header_SMS"></span><?php } ?><span id="LNA_header"><?php echo $lastNoteAction['hHeader'] ?></span> <?php echo $emailStatus ?></h6>
														<small style="text-align:right;"><?php echo date("m/d/y h:ia", $lastNoteAction['hDate']) ?><?php echo ($LNA_from != '' ? '<br />' . $LNA_from : '') ?></small>
													</div>
													<?php if ($lastNoteAction['node'] != 'EMAIL_LINK') : ?>
														<p class="mb-1"><?php echo ($lastNoteAction['node'] == 'EMAIL_LINK' ? $RECORD->cleanHtml($PDATA['LastNoteActionBody']) : nl2br($PDATA['LastNoteActionBody'])) ?></p>
													<?php else : ?>
														<p class="mb-1 text-center"><em>click to view email body</em></p>
													<?php endif; ?>
												</div>
											<?php else : ?>
												<div class="list-group-item list-group-item-action flex-column align-items-start">
													<p class="mb-1 text-center"><em>No Last Action/Note Found</em></p>
												</div>
											<?php endif; ?>
								</div>

								<div class="m-portlet__body-separator"></div>
								<h5>
									<?php if ($SEARCH->getCompletionStatus($PERSON_ID) == 100) : ?>
										<div class="pull-right m--font-success"><i class="fa fa-check"></i> COMPLETED</div>
									<?php else : ?>
										<div class="pull-right m--font-danger"><i class="fa fa-exclamation-circle"></i> INCOMPLETE</div>
									<?php endif; ?>
									PREFERENCES
								</h5>
								<?php echo $PROFILE->render_FullPrefs($PERSON_ID) ?>
							</div>

							<div class="tab-pane " id="user_profile_tab_profile">
								<h3>Profile</h3>
								<div class="row">
									<div class="col-sm-6">
										<div class="profileValueBlock">Employer: <?php echo $IEDIT->render_basicField('text', 'Employer', $PERSON_ID, $PDATA['Employer'], $PDATA['Employer'], '/ajax/inline.basic.php') ?></div>
										<div class="profileValueBlock">Education: <?php echo $IEDIT->render_basicField('select', 'Education', $PERSON_ID, $PDATA['Education'], $PDATA['Education'], '/ajax/inline.basic.php') ?></div>
									</div>
									<div class="col-sm-6">
										<div class="profileValueBlock">Marital Status: <?php echo $IEDIT->render_basicField('select', 'MaritalStatus', $PERSON_ID, $PDATA['MaritalStatus'], $PDATA['MaritalStatus'], '/ajax/inline.basic.php') ?></div>
										<div class="profileValueBlock">Dating: <?php echo $IEDIT->render_basicField('select', 'DatingStatus', $PERSON_ID, $PDATA['DatingStatus'], $PDATA['DatingStatus'], '/ajax/inline.basic.php') ?></div>
									</div>
								</div>
								<?php echo $PROFILE->render_FullProfile($PERSON_ID) ?>
							</div>

							<div class="tab-pane " id="user_profile_tab_intros">
								<div class="row" style="margin-bottom:10px;">
									<div class="col-8">
										<h3>Introductions</h3>
									</div>
									<div class="col-2">
										<a href="/findfor/<?php echo $PERSON_ID ?>" class="btn m-btn--pill btn-outline-danger btn-sm">
											<i class="fa fa-heart-o"></i> Find a Match
										</a>
									</div>
									<div class="col-2">
										<button type="button" class="btn m-btn--pill btn-outline-success btn-sm" data-toggle="modal" data-target="#IntroMakeModal"><i class="fa fa-heart-o"></i>&nbsp;<i class="fa fa-plus"></i></button>
									</div>
								</div>
								<?php include_once("./includes/sub.profile.intros.php"); ?>
							</div>

							<div class="tab-pane " id="user_profile_tab_schedule">
								<?php include_once("./includes/profile.appointments.php"); ?>
							</div>
							<div class="tab-pane " id="user_profile_tab_office">
								<?php include_once("./includes/sub.profile.backoffice.php"); ?>
							</div>

							<div class="tab-pane " id="user_profile_tab_history">
								<div class="row" style="margin-bottom:10px;">
									<div class="col-5">
										<h3>Record History</h3>
									</div>
									<div class="col-7 text-right">
										<div class="m-demo__preview m-demo__preview--btn">
											<button onclick="openDataHistory()" class="btn btn-secondary btn-sm m-btn 	m-btn m-btn--icon">
												<span>
													<i class="flaticon-folder-1"></i>
													<span>View Data History</span>
												</span>
											</button>
											&nbsp;
											<button onclick="openFormHistory()" class="btn btn-secondary btn-sm m-btn 	m-btn m-btn--icon">
												<span>
													<i class="flaticon-file-1"></i>
													<span>View Form History</span>
												</span>
											</button>
										</div>
									</div>
								</div>
								<?php include_once("./includes/profile.history.php"); ?>
							</div>



						</div>
					</div>
				</div>
			</div>

		</div>
	</div>


	<?php
	if ($PDATA['Country'] != '') {
		$c_code = $PDATA['Country'];
	} else {
		$c_code = 'US';
	}

	$c_sql = "SELECT * FROM SOURCE_Contries";
	$c_snd = $DB->get_multi_result($c_sql);
	ob_start();
	foreach ($c_snd as $c_dta) :
		$countryCode 	= $c_dta['CountryCode'];
		$countryName	= $c_dta['Country'];
	?><option value="<?php echo $countryCode ?>" <?php echo (($countryCode == $c_code) ? 'selected' : '') ?>><?php echo $countryName ?></option><?php
																																		endforeach;
																																		$country_select_options = ob_get_clean();

																																		$s_sql = "SELECT * FROM SOURCE_States WHERE CountryCode='" . $c_code . "' ORDER BY State";
																																		$s_snd = $DB->get_multi_result($s_sql);
																																		ob_start();
																																		foreach ($s_snd as $s_dta) :
																																			?><option value="<?php echo $s_dta['StateCode'] ?>" <?php echo (($s_dta['StateCode'] == $PDATA['State']) ? 'selected' : '') ?>><?php echo $s_dta['State'] ?></option><?php
																																								endforeach;
																																								$state_select_options = ob_get_clean();

																																								$currentArray = explode("|", $PDATA['prQuestion_657']);
																																								$qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='657' ORDER BY QuestionsAnswers_order ASC";
																																								$qa_snd = $DB->get_multi_result($qa_sql);
																																								//print_r($qa_snd);
																																								ob_start();
																																								foreach ($qa_snd as $qa_dta) :
																																									?><label class="m-checkbox"><input type="checkbox" name="value[]" value="<?php echo $qa_dta['QuestionsAnswers_value'] ?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $currentArray)) ? 'checked' : '') ?>><?php echo $qa_dta['QuestionsAnswers_value'] ?><span></span></label><?php
																																																																						endforeach;
																																																																						$qa_select_options = ob_get_clean();
																																																																							?>
	<!-- PAGE MODALS -->
	<?php echo $IMAGES->render_ImagesUploadModal($PERSON_ID) ?>
	<?php echo $IMAGES->render_ImagesPreviewModal($PERSON_ID) ?>
	<?php echo $NOTES->render_notesModal() ?>
	<div class="modal fade" id="prospectMeetModal" role="dialog" aria-labelledby="prospectMeetModalLabel" aria-hidden="true">
		<form id="prospectMeetForm" action="javascript:;">
			<div class="modal-dialog " role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="prospectMeetModalLabel"><i class="flaticon-speech-bubble-1"></i> Met with Prospect</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
						<input type="hidden" name="qid" value="1727|1728" />
						<?php echo $SESSION->renderToken() ?>
						<div class="m-form__group form-group row">
							<label class="col-md-4 col-form-label">Met Prospect</label>
							<div class="col-md-8 align-self-md-center">
								<div class="">
									<input type="radio" name="prQuestion_1727" value="In Person" <?php echo (($PDATA['prQuestion_1727'] == 'In Person' || $PDATA['prQuestion_1727'] == 'Yes' ) ? 'checked' : '') ?>>
									In Person <br />
									<input type="radio" name="prQuestion_1727" value="Video" <?php echo (($PDATA['prQuestion_1727'] == 'Video') ? 'checked' : '') ?>>
										Video
								</div>
							</div>
						</div>
						<div class="m-form__group form-group row">
							<label class="col-md-4 col-form-label">Meet Notes</label>
							<div class="col-md-8">
								<textarea name="prQuestion_1728" id="prQuestion_1728" class="form-control m-input" style="height:100px;"><?php echo $PDATA['prQuestion_1728'] ?></textarea>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary" onclick="updateProspectMeet()">Save</button>
					</div>
				</div>
			</div>
		</form>
	</div>

	<!-- MODAL: SOURCE -->
	<div class="modal fade" id="sourceModal" role="dialog" aria-labelledby="sourceModalLabel" aria-hidden="true">
		<div class="modal-dialog " role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="sourceModalLabel"><i class="flaticon-interface-9"></i> Record Source</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form class="m-form" id="sourceForm" action="">
						<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
						<input type="hidden" name="qid" value="HearAboutUs" />
						<?php echo $SESSION->renderToken() ?>
						<div class="form-group m-form__group row">
							<label class="col-form-label col-3">
								Source:
							</label>
							<div class="col-9">
								<select class="form-control m-select2" id="sourceSelect" name="value" style="width:100%;">
									<?php echo $RECORD->options_leadSources($values = array($PDATA['HearAboutUs']), true); ?>
								</select>
								<span class="m-form__help">
									Select the source of this record.
								</span>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="updateSource()">Save</button>
				</div>
			</div>
		</div>
	</div>

	<!-- MODAL: COLOR -->
	<div class="modal fade" id="colorModal" tabindex="-1" role="dialog" aria-labelledby="colorModalLabel" aria-hidden="true">
		<div class="modal-dialog " role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="stageModalLabel"><i class="flaticon-interface-9"></i> Record Flag</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form class="m-form" id="colorForm" action="">
						<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
						<input type="hidden" name="qid" value="Color_id" />
						<?php echo $SESSION->renderToken() ?>
						<div class="m-form__group form-group">
							<div class="m-radio-list">
							<?php echo $RECORD->render_colorRadio($PERSON_ID) ?>
							</div>
						</div>
						<span class="m-form__help">
							Select the flag of this record.
						</span>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="updateColor()">Save</button>
				</div>
			</div>
		</div>
	</div>
	<!-- MODAL: SHARE -->
	<?php echo $RSHARE->render_leftShareModal($PERSON_ID) ?>

	<!-- MODAL: MEMBER TYPE -->
	<div class="modal fade" id="memTypeModal" role="dialog" aria-labelledby="memTypeModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="memTypeModalLabel">Member/Client Type</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<form class="m-form" id="memTypeForm" action="">
						<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
						<input type="hidden" name="qid" value="657" />
						<input type="hidden" name="url" value="/ajax/inline.profile.php" />
						<?php echo $SESSION->renderToken() ?>
						<div class="m-form__group form-group">
							<div class="m-radio-list">
								<?php echo $qa_select_options ?>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="saveMemType()">Save</button>
				</div>
			</div>
		</div>
	</div>

	<!-- MODAL: CONTRACT TERMS -->
	<div class="modal fade" id="validModal" role="dialog" tabindex="-1" role="dialog" aria-labelledby="validModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="validModalLabel">Membership Contract Term/Span</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form class="m-form" id="validForm" action="javascript:saveVadidDates()" class="m-form m-form--fit m-form--label-align-right m-form--group-seperator-dashed m-form--state">
						<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
						<input type="hidden" name="qid" value="657" />
						<input type="hidden" name="url" value="/ajax/inline.profile.php" />
						<?php echo $SESSION->renderToken() ?>
						<div class="form-group m-form__group row">
							<label class="col-form-label col-lg-5 col-sm-12">
								Contract Start Date
							</label>
							<div class="col-lg-7 col-sm-12">
								<div class="input-group date" id="prQuestion_676_special">
									<input type="text" id="prQuestion_676" name="prQuestion_676" value="<?php echo (($PDATA['prQuestion_676'] != 0) ? date("m/d/Y", $PDATA['prQuestion_676']) : '') ?>" class="form-control m-input" readonly placeholder="mm/dd/yyyy" />
									<span class="input-group-addon">
										<i class="la la-calendar-check-o"></i>
									</span>
								</div>
							</div>
						</div>
						<div class="form-group m-form__group row">
							<label class="col-form-label col-lg-5 col-sm-12">
								Contract End Date
							</label>
							<div class="col-lg-7 col-sm-12">
								<div class="input-group date" id="prQuestion_677_special">
									<input type="text" name="prQuestion_677" id="prQuestion_677" value="<?php echo (($PDATA['prQuestion_677'] != 0) ? date("m/d/Y", $PDATA['prQuestion_677']) : '') ?>" class="form-control m-input" readonly placeholder="mm/dd/yyyy">
									<span class="input-group-addon">
										<i class="la la-calendar-check-o"></i>
									</span>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="saveVadidDates()">Save</button>
				</div>
			</div>
		</div>
	</div>

	<!-- MODAL: RECORD TYPE -->
	<?php include_once("sub.profile.rtypeModal.php"); ?>

	<!-- MODAL: MATCHMAKER -->
	<div class="modal fade" id="matchmakerModal" role="dialog" aria-labelledby="matchmakerModalLabel" aria-hidden="true">
		<div class="modal-dialog " role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="matchmakerModalLabel">Assigned Relationship Manager</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<form class="m-form" id="matchmakerForm" action="">
						<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
						<input type="hidden" name="qid" value="Matchmaker_id" />
						<?php echo $SESSION->renderToken() ?>
						<?php echo $RECORD->render_userSelect($PDATA['Matchmaker_id'], 'matchmakerSelect') ?>
						<span class="m-form__help">
							Select the relationship manager assigned to this record.
						</span>
					</form>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="updateMachmaker()">Save</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="matchmakerModal2" role="dialog" aria-labelledby="matchmakerModal2Label" aria-hidden="true">
		<div class="modal-dialog " role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="matchmakerModal2Label">Assigned Network Developer</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<form class="m-form" id="matchmaker2Form" action="">
						<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
						<input type="hidden" name="qid" value="Matchmaker2_id" />
						<?php echo $SESSION->renderToken() ?>
						<?php echo $RECORD->render_userSelect($PDATA['Matchmaker2_id'], 'matchmakers2Select') ?>
						<span class="m-form__help">
							Select the network assigned to this record.
						</span>
					</form>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="updateMachmaker2()">Save</button>
				</div>
			</div>
		</div>
	</div>

	<!-- MODAL: Assigned Market Director -->
	<div class="modal fade" id="assignedModal" role="dialog" aria-labelledby="assignedModalLabel" aria-hidden="true">
		<div class="modal-dialog " role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="assignedModalLabel">Assigned Market Director</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<form class="m-form" id="assignedForm" action="">
						<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
						<input type="hidden" name="qid" value="Assigned_userID" />
						<?php echo $SESSION->renderToken() ?>
						<?php echo $RECORD->render_userSelect($PDATA['Assigned_userID'], 'assignedSelect') ?>
						<span class="m-form__help">
							Select the market director assigned to this lead.
						</span>
					</form>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="updateAssigned()">Save</button>
				</div>
			</div>
		</div>
	</div>

	<!-- MODAL: STAGES -->
	<div class="modal fade" id="stageModal" tabindex="-1" role="dialog" aria-labelledby="stageModalLabel" aria-hidden="true">
		<div class="modal-dialog " role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="stageModalLabel">Lead Stage/Status</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<form class="m-form" id="stagesForm" action="">
						<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
						<input type="hidden" name="qid" value="LeadStages_id" />
						<?php echo $SESSION->renderToken() ?>
						<div class="m-radio-list">
							<?php echo $RECORD->render_stagesRadio($PDATA['LeadStages_id']) ?>
						</div>
						<span class="m-form__help">
							Select the lead stage of this record.
						</span>
					</form>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="updateStage()">Save</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="officeModal" tabindex="-1" role="dialog" aria-labelledby="officeModalLabel" aria-hidden="true">
		<div class="modal-dialog " role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="officeModalLabel">Record Office/Location</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<form class="m-form" id="officeForm" action="">
						<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
						<input type="hidden" name="Offices_id" id="Offices_id" value="<?php echo $PDATA['Offices_id'] ?>" />
						<input type="hidden" name="qid" value="Offices_id" />
						<?php echo $SESSION->renderToken() ?>
						<div class="m-radio-list">
							<?php echo $RECORD->render_officeRadio($PDATA['Offices_id']) ?>
						</div>
						<span class="m-form__help">
							Select the home office of this record or <a href="javascript:;" class="m-link quick-office-locate" data-id="<?php echo $PERSON_ID ?>">click here</a> to do it automaticly.
						</span>
					</form>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="updateOffice()">Save</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="podModal" tabindex="-1" role="dialog" aria-labelledby="podModalLabel" aria-hidden="true">
		<div class="modal-dialog " role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="podModalLabel">Record POD</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<form class="m-form" id="podForm" action="">
						<input type="hidden" name="pid" value="<?php echo $PERSON_ID ?>" />
						<input type="hidden" name="Pod_id" id="Pod_id" value="<?php echo $PDATA['Pod_id'] ?>" />
						<input type="hidden" name="qid" value="Pod_id" />
						<?php echo $SESSION->renderToken() ?>
						<div class="m-radio-list">
							<?php echo $RECORD->render_podRadio($PDATA['Pod_id']) ?>
						</div>
						<span class="m-form__help">
							Select the POD of this record or <a href="javascript:;" class="m-link quick-office-locate" data-id="<?php echo $PERSON_ID ?>">click here</a> to do it automaticly.
						</span>
					</form>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="updatePOD()">Save</button>
				</div>
			</div>
		</div>
	</div>



	<div class="modal fade" id="phonesModal" tabindex="-1" role="dialog" aria-labelledby="phonesModalLabel" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="phonesModalLabel">Phone Information</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<form class="m-form" id="phoneForm" action="javascript:saveAddress();">
						<input type="hidden" name="personID" value="<?php echo $PERSON_ID ?>" />
						<?php echo $SESSION->renderToken() ?>

						<div id="m_repeater_1">
							<div class="form-group  m-form__group row" id="m_repeater_1">
								<div id="phones-form-list" class="col-lg-12">
									<?php echo $RECORD->render_PhonesForm($PERSON_ID) ?>
								</div>
							</div>
							<div class="m-form__group form-group row">
								<label class="col-lg-2 col-form-label"></label>
								<div class="col-lg-4">
									<a href="javascript:loadNewPhone()" class="btn btn btn-sm btn-brand m-btn m-btn--icon m-btn--pill m-btn--wide">
										<span>
											<i class="la la-plus"></i>
											<span>Add</span>
										</span>
									</a>
								</div>
							</div>
						</div>
					</form>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="savePhones();">Save</button>
				</div>
			</div>
		</div>
	</div>

	<!-- MODAL: ADDRESS -->
	<div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addressModalLabel">Address Information</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<?php echo $RADDRESS->render_addressStack($PERSON_ID) ?>
					<form class="m-form" id="addressForm" action="javascript:saveAddress();">
						<input type="hidden" name="addressID" id="addressID" value="<?php echo $PDATA['Address_id'] ?>" />
						<input type="hidden" name="personID" value="<?php echo $PERSON_ID ?>" />
						<?php echo $SESSION->renderToken() ?>
						<div class="m-form__section m-form__section--first">
							<div class="form-group m-form__group row">
								<label class="col-lg-3 col-form-label">Street:</label>
								<div class="col-lg-6">
									<input type="text" name="Street_1" id="Street_1" class="form-control m-input" placeholder="Street Address" value="<?php echo $PDATA['Street_1'] ?>">
								</div>
							</div>
							<div class="form-group m-form__group row">
								<label class="col-lg-3 col-form-label">City:</label>
								<div class="col-lg-6">
									<input type="text" name="City" id="City" class="form-control m-input" placeholder="City" value="<?php echo $PDATA['City'] ?>">
								</div>
							</div>
							<div class="form-group m-form__group row">
								<label class="col-lg-3 col-form-label">State</label>
								<div class="col-lg-6">
									<select name="State" id="State" class="form-control m-input">
										<option value=""></option>
										<?php echo $state_select_options ?>
									</select>
								</div>
							</div>
							<div class="form-group m-form__group row">
								<label class="col-lg-3 col-form-label">Zip/Postal Code</label>
								<div class="col-lg-6">
									<input type="text" name="Postal" id="Postal" class="form-control m-input" placeholder="Zip Code" value="<?php echo $PDATA['Postal'] ?>" />
								</div>
							</div>
							<div class="form-group m-form__group row">
								<label class="col-lg-3 col-form-label">Country</label>
								<div class="col-lg-6">
									<select name="Country" id="Country" class="form-control m-input" onchange="setCountryStates()"><?php echo $country_select_options ?></select>
								</div>
							</div>
							<div class="m-form__group form-group row">
								<label class="col-lg-3 col-form-label">&nbsp;</label>
								<div class="col-lg-6">
									<div class="m-checkbox-inline">
										<label class="m-checkbox">
											<input type="checkbox" name="isPrimary" id="address_isPrimary" value="1" <?php echo (($PDATA['isPrimary'] == 1) ? 'checked' : '') ?>>
											Address is Primary <a class="fa fa-alert"></a>
											<span></span>
										</label>
									</div>
								</div>
							</div>
							<div class="m-form__group form-group row m-hide">
								<label class="col-lg-3 col-form-label">Geolocation Status</label>
								<div class="col-lg-6">
									<button type="button" class="btn btn-secondary btn-sm pull-right" id="button-geolocate"><i class="fa fa-map-marker"></i> GeoLocate</button>
									<span class="m-badge m-badge--success m-badge--wide m-badge--rounded <?php echo (($PDATA['GeoLocationStatus'] == 200) ? '' : 'm--hide') ?>" id="geolocate-success">this record has been geo-located</span>
									<span class="m-badge m-badge--danger m-badge--wide m-badge--rounded <?php echo (($PDATA['GeoLocationStatus'] != 200) ? '' : 'm--hide') ?>" id="geolocate-failed">this record has NOT been geo-located</span>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" id="button-address-delete" onclick="rmAddress()" <?php echo (($PDATA['isPrimary'] == 1) ? 'disabled' : '') ?>>Delete Address <i class="fa fa-remove"></i></button>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<button type="button" class="btn btn-success" id="button-address-new" onclick="newAddress()">Addd New Address <i class="fa fa-plus"></i></button>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" id="button-address-save" onclick="saveAddress();">Save</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="linksModal" tabindex="-1" role="dialog" aria-labelledby="linksModalLabel" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="linksModalLabel">Personalized Links to Forms</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<ul class="list-group">
						<?php
						$fsql = "SELECT * FROM CompanyForms WHERE FormActive='1' ORDER BY FormName";
						$fsnd = $DB->get_multi_result($fsql);
						foreach ($fsnd as $fdta) :
						?>
							<li class="list-group-item">
								<!--<button type="button" class="btn btn-primary btn-sm pull-right link-copy-button" data-clipboard-action="copy" data-clipboard-target="#formLink_<?php echo $fdta['FormID'] ?>">Copy to Clipboard</button>-->
								<?php echo $fdta['FormName'] ?><br /><a href="https://<?php echo $_SERVER['SERVER_NAME'] ?>/view-form.php?id=<?php echo $fdta['FormCallString'] ?>&p=<?php echo $PERSON_ID ?>" target="_blank">https://<?php echo $_SERVER['SERVER_NAME'] ?>/view-form.php?id=<?php echo $fdta['FormCallString'] ?>&p=<?php echo $PERSON_ID ?></a>
								<!--<input type="hidden" id="formLink_<?php echo $fdta['FormID'] ?>" value="https://<?php echo $_SERVER['SERVER_NAME'] ?>/view-form.php?id=<?php echo $fdta['FormCallString'] ?>&p=<?php echo $PERSON_ID ?>" />-->
							</li>
						<?php
						endforeach;
						?>
					</ul>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<?php $TASKS->render_taskModal($PERSON_ID); ?>
	<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="historyModalLabel">Record History</h5>
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

	<div class="modal fade" id="printProfileModal" tabindex="-1" role="dialog" aria-labelledby="printProfileModalLabel" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="printProfileModalLabel">Profile Preview</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">



				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="printRecordInfo()" data-toggle="m-tooltip" title="This version will remove the DoB, Address, and contact information from the printed version."><i class="la la-print"></i> Print Profile (Safe Version)</button>
					<button type="button" class="btn btn-warning" onclick="printAdminRecordInfo()" data-toggle="m-tooltip" title="This version will keep private information like DoB, Address and contact information on the printed version."><i class="la la-print"></i> Print Profile (Admin Version)</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="bioProfileModal" tabindex="-1" role="dialog" aria-labelledby="bioProfileModalLabel" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="bioProfileModalLabel">Profile Preview</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">



				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="printRecordBio()" data-toggle="m-tooltip" title="This version will remove the DoB, Address, and contact information from the printed version."><i class="la la-print"></i> Print Bio (Safe Version)</button>
					<button type="button" class="btn btn-warning" onclick="printAdminRecordBio()" data-toggle="m-tooltip" title="This version will keep private information like DoB, Address and contact information on the printed version."><i class="la la-print"></i> Print Bio (Admin Version)</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="myPaymentsModal" tabindex="-1" role="dialog" aria-labelledby="myPaymentsModalLabel" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<form class="m-form m-form--fit m-form--label-align-right" action="javascript:;" id="paymentForm">
				<input type="hidden" name="PaymentInfo_ID" id="PaymentInfo_ID" value="" />
				<input type="hidden" name="PaymentInfo_PID" id="PaymentInfo_PID" value="<?php echo $PERSON_ID ?>" />
				<?php echo $SESSION->renderToken() ?>
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="mySaleModalLabel">Add Sale Form</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-6">
								<div class="form-group m-form__group">
									<label for="Contract_id">Agreement</label>
									<select class="form-control m-input" id="PaymentContract_id" name="PaymentContract_id">
										<option value="0"><em>None</em></option>
										<?php
										$c_sql = "SELECT * FROM PersonsContract WHERE Person_id='" . $PERSON_ID . "' ORDER BY Contract_dateEntered DESC";
										$c_snd = $DB->get_multi_result($c_sql);
										if (!isset($c_snd['empty_result'])) :
											foreach ($c_snd as $c_dta) :
										?><option value="<?php echo $c_dta['Contract_id'] ?>">issued <?php echo date("m/d/y", $c_dta['Contract_dateEntered']) ?> for $<?php echo $c_dta['Contract_RetainerFee'] ?></option><?php
																																																					endforeach;
																																																				endif;
																																																						?>
									</select>
									<span class="m-form__help">Select the agreement this payment will be connected to</span>
								</div>
							</div>
							<div class="col-6">
								<div class="form-group m-form__group">
									<label for="PaymentInfo_paymentType">Payment Type</label>
									<select class="form-control m-input" id="PaymentInfo_paymentType" name="PaymentInfo_paymentType">
										<option value="1">Credit Card Payment</option>
										<option value="2">Electronic Check</option>
										<option value="3">Wire Transfer</option>
									</select>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-6">
								<div class="form-group m-form__group">
									<label for="PaymentInfo_Amount">Amount</label>
									<div class="input-group m-input-group">
										<span class="input-group-addon" id="basic-addon1">
											<i class="fa fa-usd"></i>
										</span>
										<input type="text" class="form-control m-input" id="PaymentInfo_Amount" name="PaymentInfo_Amount" />
									</div>
								</div>
							</div>
							<div class="col-6">
								<div class="form-group m-form__group">
									<label for="PaymentInfo_Amount">Payment Date</label>
									<div class="input-group m-input-group">
										<span class="input-group-addon" id="basic-addon1">
											<i class="fa fa-calendar"></i>
										</span>
										<input type="text" class="form-control m-input" name="PaymentInfo_Execute" id="PaymentInfo_Execute" readonly />
									</div>
								</div>
							</div>
						</div>

						<div class="row" style="margin-top:10px;">
							<div class="col-6">
								<?php if (in_array(64, $USER_PERMS)) : ?>
									<div class="form-group m-form__group">
										<div class="m-checkbox-inline">
											<label class="m-checkbox">
												<input type="checkbox" name="isRefund" id="isRefund" value="1">
												This payment is a refund.
												<span></span>
											</label>
										</div>
									</div>
								<?php endif; ?>
							</div>
							<div class="col-6">
								<?php if (in_array(63, $USER_PERMS)) : ?>
									<div class="form-group m-form__group">
										<div class="m-radio-list">
											<label class="m-radio">
												<input type="radio" name="PaymentInfo_Status" id="payStatus_1" class="payStatusMarker" data-id="<?php echo $_POST['pid'] ?>" value="1" <?php echo (($snd['PaymentInfo_Status'] == 1) ? 'checked' : '') ?>>
												PENDING
												<span></span>
											</label>
											<label class="m-radio">
												<input type="radio" name="PaymentInfo_Status" id="payStatus_2" class="payStatusMarker" data-id="<?php echo $_POST['cid'] ?>" value="2" <?php echo (($c_snd['Contract_status'] == 2) ? 'checked' : '') ?>>
												SIGNED
												<span></span>
											</label>
											<label class="m-radio">
												<input type="radio" name="PaymentInfo_Status" id="payStatus_3" class="payStatusMarker" data-id="<?php echo $_POST['cid'] ?>" value="3" <?php echo (($c_snd['Contract_status'] == 3) ? 'checked' : '') ?>>
												PROCESSED
												<span></span>
											</label>
										</div>
									</div>
								<?php else : ?>
									<input type="hidden" name="PaymentInfo_Status" id="payStatus_1" class="payStatusMarker" data-id="<?php echo $snd['PaymentInfo_Status'] ?>" value="1">
								<?php endif; ?>

							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group m-form__group">
									<label for="PaymentInfo_Amount">Sale Form Note</label>
									<div class="input-group m-input-group">
										<textarea class="form-control m-input" name="PaymentInfo_notes" id="PaymentInfo_notes"></textarea>
									</div>
								</div>
							</div>
						</div>

						<hr />

						<div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-danger alert-dismissible fade show" role="alert" id="paymentAlertArea">
							<div class="m-alert__icon">
								<i class="flaticon-exclamation-2"></i>
								<span></span>
							</div>
							<div class="m-alert__text">
								<?php
								$contractURL = 'https://' . $_SERVER['SERVER_NAME'] . '/payment.php?id=' . $PDATA['PaymentInfo_Hash'];
								//$contractTinyURL = $ENC->get_tiny_url($contractURL); 
								$contractTinyURL = $contractURL;
								?>
								<strong>This payment can be accessed via <a href="<?php echo $contractTinyURL ?>" id="link-payment-showcase" target="_blank">the following URL</a>:</strong><br />
								<textarea class="form-control m-input" id="payment_embedCode"><?php echo $contractTinyURL ?></textarea>
							</div>
						</div>


					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-metal" id="button-copy-paylink" onclick="copyPaymentLink()">Copy to Clipboard</button>
						<button type="button" class="btn btn-danger" id="button-payment-delete" onclick="removePayment()">Delete Payment Form</button>
						<button type="button" class="btn btn-info" id="button-payment-clear" onclick="clearPayment()">Clear Payment Information</button>
						<button type="button" class="btn btn-primary" onclick="savePayment()">Save Payment Form</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="modal fade" id="myPaymentReviewModal" tabindex="-1" role="dialog" aria-labelledby="myPaymentReviewModalLabel" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="myPaymentReviewModalLabel">Sale Information</h5>
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

	<div class="modal fade" id="ImagesModal" tabindex="-1" role="dialog" aria-labelledby="ImagesModalLabel" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="ImagesModalLabel">Profile Images</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<?php echo $RECORD->render_ImageLibrary($PERSON_ID) ?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<?php echo $RLINKS->render_personLinksModal($PERSON_ID) ?>

	<script type="text/javascript" src="/assets/vendors/custom/twbs-pagination/jquery.twbsPagination.min.js"></script>
	<script>
		function quickTab_backoffice() {
			$('.m-tabs a[href="#user_profile_tab_office"]').tab('show');
		}
		//$.fn.editable.defaults.mode = 'inline';
		$(document).ready(function(e) {
			document.title = <?php echo $PAGE_TITLE ?>;
			$(document).on('click', '.quick-office-locate', function() {
				var pid = $(this).attr('data-id');
				$.post('/ajax/otherStuff.php?action=getNearestOffice', {
					pid: pid
				}, function(data) {
					//console.log(data);
					var choice = confirm(data.distance + ' miles from ' + data.text + ' office');
					if (choice) {
						$('#officeForm input[type=radio]').each(function() {
							if ($(this).val() == data.id) {
								$(this).attr('checked', true);
							} else {
								$(this).attr('checked', false);
							}
						});
						updateOffice();
					}
				}, "json");
			});

			$('#quick-action-photo').popover({
				template: '            <div class="m-popover m-popover--skin-light popover" role="tooltip">                <div class="arrow"></div>                <h3 class="popover-header"></h3>                <div class="popover-body"></div>            </div>'
			});

			var el_img = document.getElementById('img-preview-area');
			var img_sortable = Sortable.create(el_img, {
				draggable: '.img-sort-dragger',
				animation: 0,
				// Changed sorting within list
				onUpdate: function(evt) {
					// same properties as onEnd
					//console.log(evt);
					//alert('Image Order changed');
					if ($('#save-image-order-area').is(':visible')) {
						// do nothing //
					} else {
						$('#save-image-order-area').show();
					}
				}
			});
			//$('#img-preview-area').

			$(document).on('click', '.address-select-block', function() {
				var aid = $(this).attr('data-id');
				mApp.block("#addressModal .modal-content", {
					overlayColor: "#000000",
					type: "loader",
					state: "success",
					message: "Loading Address...",
					blockMsgClass: "InfoLoadingProfile"
				});
				$.post('/ajax/addresses.php?action=getAddress', {
					aid: aid
				}, function(data) {
					$('#Country').val(data.Country);
					setCountryStates();
					$('#addressID').val(data.Address_id);
					$('#Street_1').val(data.Street_1);
					$('#City').val(data.City);
					$('#Postal').val(data.Postal);
					if (data.isPrimary == 1) {
						$('#address_isPrimary').attr('checked', true);
						$('#button-address-delete').attr('disabled', true);
					} else {
						$('#address_isPrimary').attr('checked', false);
						$('#button-address-delete').attr('disabled', false);
					}
					setTimeout(function() {
						$('#State').val(data.State);
					}, 750);
					if (data.GeoLocationStatus == 200) {
						$('#geolocate-failed').addClass('m--hide');
						$('#geolocate-success').removeClass('m--hide');
					} else {
						$('#geolocate-failed').removeClass('m--hide');
						$('#geolocate-success').addClass('m--hide');
					}
					$('#button-geolocate').attr('disabled', false);
					mApp.unblock("#addressModal .modal-content");
				}, "json");
			});

			$(document).on('click', '.inline-edit-text', function() {
				var pid = $(this).attr('data-key');
				var field = $(this).attr('data-field');
				var value = $(this).attr('data-value');
				console.log(pid + '|' + field + '|' + value);
			});
			var clipboard = new Clipboard('.link-copy-button');

			$(document).on('click', '.inline-edit-select', function() {
				var pid = $(this).attr('data-key');
				var field = $(this).attr('data-field');
				var value = $(this).attr('data-value');
				var url = $(this).attr('data-url');
				var unique = 'prQuestion_' + field;
				//console.log(pid+'|'+field+'|'+value+'|'+unique);
				$('#' + unique + ' .inline-edit-display').hide();
				$('#' + unique + ' .inline-edit-form').html('<div class="m-loader m-loader--brand m-loader--sm" style="width: 30px; display: inline-block;"></div>');
				$.post('/ajax/inline.form.php', {
					pid: pid,
					field: field,
					value: value,
					url: url,
					type: 'select'
				}, function(data) {
					$('#' + unique + ' .inline-edit-form').html(data);
				});
			});
			$(document).on('click', '.inline-edit-text', function() {
				var pid = $(this).attr('data-key');
				var field = $(this).attr('data-field');
				var value = $(this).attr('data-value');
				var url = $(this).attr('data-url');
				var unique = 'prQuestion_' + field;
				//console.log(pid+'|'+field+'|'+value+'|'+unique);
				$('#' + unique + ' .inline-edit-display').hide();
				$('#' + unique + ' .inline-edit-form').html('<div class="m-loader m-loader--brand m-loader--sm" style="width: 30px; display: inline-block;"></div>');
				$.post('/ajax/inline.form.php', {
					pid: pid,
					field: field,
					value: value,
					url: url,
					type: 'text'
				}, function(data) {
					$('#' + unique + ' .inline-edit-form').html(data);
				});
			});
			$(document).on('click', '.inline-edit-radio', function() {
				var pid = $(this).attr('data-key');
				var field = $(this).attr('data-field');
				var value = $(this).attr('data-value');
				var url = $(this).attr('data-url');
				var unique = 'prQuestion_' + field;
				//console.log(pid+'|'+field+'|'+value+'|'+unique);
				$('#' + unique + ' .inline-edit-display').hide();
				$('#' + unique + ' .inline-edit-form').html('<div class="m-loader m-loader--brand m-loader--sm" style="width: 30px; display: inline-block;"></div>');
				$.post('/ajax/inline.form.php', {
					pid: pid,
					field: field,
					value: value,
					url: url,
					type: 'radio'
				}, function(data) {
					$('#' + unique + ' .inline-edit-form').html(data);
				});
			});
			$(document).on('click', '.inline-edit-checkbox', function() {
				var pid = $(this).attr('data-key');
				var field = $(this).attr('data-field');
				var value = $(this).attr('data-value');
				var url = $(this).attr('data-url');
				var unique = 'prQuestion_' + field;
				//console.log(pid+'|'+field+'|'+value+'|'+unique);
				$('#' + unique + ' .inline-edit-display').hide();
				$('#' + unique + ' .inline-edit-form').html('<div class="m-loader m-loader--brand m-loader--sm" style="width: 30px; display: inline-block;"></div>');
				$.post('/ajax/inline.form.php', {
					pid: pid,
					field: field,
					value: value,
					url: url,
					type: 'checkbox'
				}, function(data) {
					$('#' + unique + ' .inline-edit-form').html(data);
				});
			});
			$(document).on('click', '.inline-edit-date', function() {
				var pid = $(this).attr('data-key');
				var field = $(this).attr('data-field');
				var value = $(this).attr('data-value');
				var url = $(this).attr('data-url');
				var unique = 'prQuestion_' + field;
				//console.log(pid+'|'+field+'|'+value+'|'+unique);
				$('#' + unique + ' .inline-edit-display').hide();
				$('#' + unique + ' .inline-edit-form').html('<div class="m-loader m-loader--brand m-loader--sm" style="width: 30px; display: inline-block;"></div>');
				$.post('/ajax/inline.form.php', {
					pid: pid,
					field: field,
					value: value,
					url: url,
					type: 'date'
				}, function(data) {
					$('#' + unique + ' .inline-edit-form').html(data);
					$("#m_datepicker_6").datepicker({
						todayHighlight: !0,
						templates: {
							leftArrow: '<i class="la la-angle-left"></i>',
							rightArrow: '<i class="la la-angle-right"></i>'
						}
					}).on('changeDate', function(e) {
						$('#' + unique + ' .inline-edit-form form input[name="value"]').val(e.format('mm/dd/yyyy'))
					});
					if (value != '12/31/1969') {
						$('#m_datepicker_6').datepicker('update', value);
					}
				});
			});
			$(document).on('click', '.inline-edit-textarea', function() {
				var pid = $(this).attr('data-key');
				var field = $(this).attr('data-field');
				var value = $(this).attr('data-value');
				var url = $(this).attr('data-url');
				var unique = 'prQuestion_' + field;
				//console.log(pid+'|'+field+'|'+value+'|'+unique);
				$('#' + unique + ' .inline-edit-display').hide();
				$('#' + unique + ' .inline-edit-form').html('<div class="m-loader m-loader--brand m-loader--sm" style="width: 30px; display: inline-block;"></div>');
				$.post('/ajax/inline.form.php', {
					pid: pid,
					field: field,
					value: value,
					url: url,
					type: 'textarea'
				}, function(data) {
					$('#' + unique + ' .inline-edit-form').html(data);
					t = $('#' + unique + ' .inline-edit-form form textarea');
					autosize(t);
				});
			});

			$(document).on('click', '.inline-edit-prefs', function() {
				var pid = $(this).attr('data-key');
				var field = $(this).attr('data-field');
				var value = $(this).attr('data-value');
				var match = $(this).attr('data-match');
				var url = $(this).attr('data-url');
				var unique = 'prefQuestion_' + field;
				//console.log(pid+'|'+field+'|'+value+'|'+unique);
				$('#' + unique + ' .inline-edit-display').hide();
				$('#' + unique + ' .inline-edit-form').html('<div class="m-loader m-loader--brand m-loader--sm" style="width: 30px; display: inline-block;"></div>');
				$.post('/ajax/inline.prefsForm.php', {
					pid: pid,
					field: field,
					match: match,
					value: value,
					url: url,
					type: 'checkbox'
				}, function(data) {
					$('#' + unique + ' .inline-edit-form').html(data);
					if (field == 31) {
						$("#m_select2_3").select2({
							placeholder: "Select a state"
						});
					}
					if (field == 28) {
						$("#m_select2_3").select2({
							placeholder: "Select a country"
						});
					}
				});
			});

			$(document).on('click', '.basic-inline-edit', function() {
				var pid = $(this).attr('data-key');
				var field = $(this).attr('data-field');
				var value = $(this).attr('data-value');
				var url = $(this).attr('data-url');
				var type = $(this).attr('data-type');
				$('#' + field + ' .inline-edit-display').hide();
				$('#' + field + ' .inline-edit-form').html('<div class="m-loader m-loader--brand m-loader--sm" style="width: 30px; display: inline-block;"></div>');
				$.post('/ajax/inline.basicForm.php', {
					pid: pid,
					field: field,
					value: value,
					url: url,
					type: type
				}, function(data) {
					$('#' + field + ' .inline-edit-form').html(data);
					if (field == 'DateOfBirth') {
						$("#value_dob").datepicker({
							todayHighlight: !0,
							templates: {
								leftArrow: '<i class="la la-angle-left"></i>',
								rightArrow: '<i class="la la-angle-right"></i>'
							}
						}).on('changeDate', function(e) {
							$('#value_dob').val(e.format('mm/dd/yyyy'))
						});
					}
				});
			});

			$(document).on('click', '#button-geolocate', function() {
				var formData = $('#addressForm').serializeArray();
				$('#button-geolocate').addClass('m-loader m-loader--success m-loader--right');
				$.post('/ajax/addresses.php?action=geolocate', formData, function(data) {
					$('#button-geolocate').removeClass('m-loader m-loader--success m-loader--right');
					if (data.success) {
						$('#geolocate-success').removeClass('m--hide');
						$('#geolocate-failed').addClass('m--hide');
						if (data.city != null) {
							$('#City').val(data.city);
						}
						if (data.state != null) {
							$('#State').val(data.state);
						}
					} else {
						alert('Error: ' + data.msg);
					}
				}, "json");
			});

			$(document).on('click', '.save_attach', function() {
				var name = $(this).data('name');
				var filename = $(this).data('filename');
				var filetype = $(this).data('filetype');
				var pid = '<?php echo $PERSON_ID ?>';
				$.post('/ajax/saveFile.php', {
					'name': name,
					'filename': filename,
					'filetype': filetype,
					'pid': pid
				}, function(data) {
					if (data.success == 1) {
						toastr.success('File Saved to Record', '', {
							timeOut: 5000
						});
					} else {
						alert(data.message);
					}
				}, 'json');
			});

			$("#assignedSelect").select2({
				placeholder: "Select a user"
			});
			$("#matchmakerSelect").select2({
				placeholder: "Select a user"
			});
			$('#matchmakers2Select').select2({
				placeholder: "Select a user"
			});
			$('#sourceSelect').select2({
				theme: "classic"
			});


			$(document).on('click', '#clear-dropzone', function() {
				ImageDropZone.removeAllFiles(true);
			});

			$(document).on('click', '.loadNoteForm', function() {
				var type = $(this).attr('data-type');
				$('#PersonsNotes_type').val(type);
				$('#displayNoteRecipient').html('<?php echo str_replace("'", "\'", $PDATA['FirstName']) ?> <?php echo str_replace("'", "\'", $PDATA['LastName']) ?>');
				var now = moment();
				console.log(now.format('M/D/YY h:ma'));
				$('#displayNoteDate').html(now.format('M/D/YY h:ma'));
				$('#NotesForm input[name="pid"]').val('<?php echo $PERSON_ID ?>');
				$('#NotesForm input[name="epoch"]').val(now.format('X'));
				if (type == 'Lead Action') {
					$('#subchoice_Actions').show();
					$('#subchoice_CallNotes').hide();
					$('#subchoice_ClientNotes').hide();
					$('#addNotesModalLabel').html('Add Lead Action');
				}
				if (type == 'Call Note') {
					$('#subchoice_Actions').hide();
					$('#subchoice_CallNotes').show();
					$('#subchoice_ClientNotes').hide();
					$('#addNotesModalLabel').html('Add Call Note');
				}
				if (type == 'Client Note') {
					$('#subchoice_Actions').hide();
					$('#subchoice_CallNotes').hide();
					$('#subchoice_ClientNotes').show();
					$('#addNotesModalLabel').html('Add Client Note');
				}
				$('#PersonsNotes_header').val('');
				$('#addNotesModal').modal('show');
			});
			var noteBody = $('#PersonsNotes_body');
			autosize(noteBody);
			$(".notes-summernote").summernote({
				height: 250
			});

			//getNoteHistory('<?php echo $PERSON_ID ?>', 0);
			$(document).on('click', '.historyLink', function() {
				var id = $(this).attr('data-id');
				var node = $(this).attr('data-node');
				//console.log(node+'|'+id);		
				if (node == 'TASK_LINK') {
					openAction(id);
				} else {
					$('#historyModal .modal-body').html('<div class="m-loader m-loader--brand m-loader--lg" style="width:30px; display: inline-block;"></div> Loading Note/Email...');
					$('#historyModal').modal('show');
					if (node == 'NOTE_LINK') {
						var url = '/ajax/notes.php?action=getNote';
					} else {
						var url = '/ajax/notes.php?action=getMail';
					}
					$.post(url, {
						id: id,
					}, function(data) {
						$('#historyModal .modal-body').html(data);
						//$('#addNotesModal').modal('hide');	
						//loadLastNote();
						$('.noteedit-summernote').summernote({
							height: 350
						});
					});
				}
			});

			var start = moment().subtract(5, 'months');
			var end = moment();
			$('#filterDates').daterangepicker({
				buttonClasses: 'm-btn btn',
				applyClass: 'btn-primary',
				cancelClass: 'btn-secondary',
				startDate: start,
				endDate: end,
				ranges: {
					'Last 7 Days': [moment().subtract(6, 'days'), moment()],
					'Last 30 Days': [moment().subtract(29, 'days'), moment()],
					'Last 60 Days': [moment().subtract(59, 'days'), moment()],
					'Last 90 Days': [moment().subtract(89, 'days'), moment()],
					'Last 6 Months': [moment().subtract(5, 'months'), moment()],
					'Last 12 Months': [moment().subtract(11, 'months'), moment()],
					'Last 2 years': [moment().subtract(23, 'months'), moment()],
					'Last 3 years': [moment().subtract(35, 'months'), moment()],
					'Last 4 years': [moment().subtract(47, 'months'), moment()],
					'Last 5 years': [moment().subtract(59, 'months'), moment()],
					//'This Month': [moment().startOf('month'), moment().endOf('month')],
					//'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				}
			});

			$('#PaymentInfo_Execute').datepicker({
				todayHighlight: !0,
				orientation: "bottom left",
				templates: {
					leftArrow: '<i class="la la-angle-left"></i>',
					rightArrow: '<i class="la la-angle-right"></i>'
				}
			});

			$('#prQuestion_676_special').datepicker({
				todayHighlight: !0,
				orientation: "bottom left",
				templates: {
					leftArrow: '<i class="la la-angle-left"></i>',
					rightArrow: '<i class="la la-angle-right"></i>'
				}
			});
			$('#prQuestion_677_special').datepicker({
				todayHighlight: !0,
				orientation: "bottom left",
				templates: {
					leftArrow: '<i class="la la-angle-left"></i>',
					rightArrow: '<i class="la la-angle-right"></i>'
				}
			});
			//loadLastNote();
			filterNoteHistory();
			// setTimeout(function() {
			// 	getPotentialMatches('<?php echo $PERSON_ID ?>');
			// }, 500);
			<?php if (in_array(84, $USER_PERMS)) : ?>
				$.ajax({
					url: "https://clients.kelleher-international.com/ajax.php?action=get_session",
					dataType: 'jsonp', // Notice! JSONP <-- P (lowercase)
					success: function(json) {
						// do stuff with json (in this case an array)
						//alert("Success");
						console.log(json);
						$('#client_kiss_token').val(json.session);
					},
					error: function() {
						console.log('error in ajax');
					}
				});
			<?php endif; ?>

			var LNA_header_SMS = $('#LNA_header_SMS');
			var LNA_header = $('#LNA_header');
			var RAW_phone = $('.raw_phone');
			var phone_match = false;
			if (LNA_header_SMS.length > 0) {
				if (RAW_phone.length > 0) {
					RAW_phone.each(function() {
						if ($(this).data('raw') == LNA_header.html()) {
							phone_match = true;
						}
					});
				}
				if (phone_match) {
					$('#LNA_header_SMS').html('<span class="m--font-info"><?php echo str_replace("'", "\'", ($PDATA['FirstName'] . ' ' . $PDATA['LastName'])) ?></span> &gt; ');
				} else {
					$('#LNA_header_SMS').html('<span class="m--font-danger">KISS</span> &gt; ');
				}
			}

		});

		function openClientPortalView() {
			$.post('https://clients.kelleher-international.com/jumpToFromKISS.php', {
				pid: '<?php echo $PERSON_ID ?>',
				kiss_token: '<?php echo $SESSION->createToken() ?>',
				uid: '<?php echo $_SESSION['system_user_id'] ?>'
			}, function(data) {
				window.open('https://clients.kelleher-international.com/', '_new');
			});
		}

		function launchRC(action, number) {
			if (action == 'ringout') {
				var type = 'Call Note';
				$('#PersonsNotes_type').val(type);
				$('#displayNoteRecipient').html('<?php echo str_replace("'", "\'", $PDATA['FirstName']) ?> <?php echo str_replace("'", "\'", $PDATA['LastName']) ?>');
				var now = moment();
				$('#displayNoteDate').html(now.format('M/D/YY h:ma'));
				$('#NotesForm input[name="pid"]').val('<?php echo $PERSON_ID ?>');
				$('#NotesForm input[name="epoch"]').val(now.format('X'));
				$('#subchoice_Actions').hide();
				$('#subchoice_CallNotes').show();
				$('#subchoice_ClientNotes').hide();
				$('#addNotesModalLabel').html('Add Call Note');
				$('#PersonsNotes_header').val('');
				$('#addNotesModal').modal('show');
			}
			window.open('/apis/rc.php?action=' + action + '&number=' + number, '', 'width=300,height=536,location=0,menubar=0,status=0,titlebar=0,toolbar=0');
		}

		function sendQuickEmail(pid, tid, alertBody, slug) {
			var choice = confirm(alertBody);
			if (choice) {
				$.post('/ajax/quickmail.php', {
					p: pid,
					t: tid
				}, function(data) {
					console.log(data);
					toastr.success(slug + ' Email Sent', '', {
						timeOut: 5000
					});
				});
			}
		}

		function saveImageOrder() {
			var imgSort = new Array();
			var index = 0;
			$('.img-sort-dragger').each(function() {
				imgSort[index] = $(this).attr('data-id');
				index++;
			});
			console.log(imgSort);
			$.post('/ajax/images.php?action=resort_images', {
				imgs: imgSort
			}, function(data) {
				$('#save-image-order-area').hide();
				toastr.success('Image Order Updated', '', {
					timeOut: 5000
				});
			});
		}

		function createDate(person1, person2, status) {
			$.post('/ajax/intros.php?action=createDateRecord', {
				p1: person1,
				p2: person2,
				st: status
			}, function(data) {
				console.log(data);
				var e = {};
				if (status == 101) {
					e.message = "Go to Intro Record", e.title = "Intro Record Created", e.url = "/intro/" + data.id, e.target = "_blank";
					var timer = 0;
					var ntype = 'danger';
				} else {
					e.message = "Quick Reject Recorded", e.title = "Intro Record Update";
					var timer = 5000;
					var ntype = 'success';
				}
				var t = $.notify(e, {
					type: ntype,
					allow_dismiss: true,
					newest_on_top: true,
					mouse_over: true,
					showProgressbar: false,
					spacing: 10,
					timer: timer,
					placement: {
						from: 'top',
						align: 'right'
					},
					offset: {
						x: 30,
						y: 30
					},
					delay: 1000,
					z_index: 2000,
					animate: {
						enter: "animated fadeIn",
						exit: "animated fadeOut"
					}
				});
			}, "json");
		}

		function sendPortalPasswordReset(email) {
			var choice = confirm('Are you sure you want to send this person a password reset email?');
			if (choice) {
				$.get('/portal/ajax.php', {
					action: 'passwordReset',
					email: email
				}, function(data) {
					console.log(data);
				});
			}
		}

		function openBioView() {
			$('#bioProfileModal').modal('show');
			mApp.block("#bioProfileModal .modal-content", {
				overlayColor: "#000000",
				type: "loader",
				state: "success",
				message: "Loading Printable Bio...",
				blockMsgClass: "InfoLoadingProfile"
			});
			$.post('/ajax/ajax.bioView.php', {
				pid: '<?php echo $PERSON_ID ?>'
			}, function(data) {
				if (data.showBio) {
					$('#bioProfileModal .modal-body').html(data.html);
					mApp.unblock("#bioProfileModal .modal-content");
				} else {
					alert('This record does not have an approved bio to inject into the email. Please have the person in charge of this record configure its Bio so that it can be shared.');
					mApp.unblock("#bioProfileModal .modal-content");
					setTimeout(function() {
						$('#bioProfileModal').modal('hide');
					}, 1000);
				}
			}, "json");
		}

		function printRecordBio() {
			$('#bioProfileModal .modal-body').print({
				globalStyles: true,
				mediaPrint: false,
				stylesheet: null,
				noPrintSelector: ".no-print",
				iframe: true,
				append: null,
				prepend: null,
				manuallyCopyFormValues: true,
				deferred: $.Deferred(),
				timeout: 750,
				title: 'Printed KISS Bio',
				doctype: '<!doctype html>'

			});
		}

		function printAdminRecordBio() {
			$('#bioProfileModal .modal-body').print({
				globalStyles: true,
				mediaPrint: false,
				stylesheet: null,
				noPrintSelector: ".no-print-admin",
				iframe: true,
				append: null,
				prepend: null,
				manuallyCopyFormValues: true,
				deferred: $.Deferred(),
				timeout: 750,
				title: 'Printed KISS Bio',
				doctype: '<!doctype html>'

			});
		}



		function openPrintView() {
			$('#printProfileModal').modal('show');
			mApp.block("#printProfileModal .modal-content", {
				overlayColor: "#000000",
				type: "loader",
				state: "success",
				message: "Loading Printable Profile...",
				blockMsgClass: "InfoLoadingProfile"
			});
			$.post('/ajax/ajax.printView.php', {
				pid: '<?php echo $PERSON_ID ?>'
			}, function(data) {
				if (data.showBio) {
					$('#printProfileModal .modal-body').html(data.html);
					mApp.unblock("#printProfileModal .modal-content");
				} else {
					alert('This record does not have an approved bio to inject into the email. Please have the person in charge of this record configure its Bio so that it can be shared.');
					mApp.unblock("#printProfileModal .modal-content");
					setTimeout(function() {
						$('#printProfileModal').modal('hide');
					}, 1000);
				}
			}, "json");
		}

		function printRecordInfo() {
			$('#printProfileModal .modal-body').print({
				globalStyles: true,
				mediaPrint: false,
				stylesheet: null,
				noPrintSelector: ".no-print",
				iframe: true,
				append: null,
				prepend: null,
				manuallyCopyFormValues: true,
				deferred: $.Deferred(),
				timeout: 750,
				title: 'Printed KISS Profile',
				doctype: '<!doctype html>'

			});
		}

		function printAdminRecordInfo() {
			$('#printProfileModal .modal-body').print({
				globalStyles: true,
				mediaPrint: false,
				stylesheet: null,
				noPrintSelector: ".no-print-admin",
				iframe: true,
				append: null,
				prepend: null,
				manuallyCopyFormValues: true,
				deferred: $.Deferred(),
				timeout: 750,
				title: 'Printed KISS Profile',
				doctype: '<!doctype html>'

			});
		}

		function copyToClipboard(divID) {
			$('#' + divID).select();
			document.execCommand('copy');
		}
		var updateProspectMeet = function() {
			var formData = $('#prospectMeetForm').serializeArray();
			$.post('/ajax/otherStuff.php?action=loadMetNote', formData, function(data) {
				console.log(data);
				document.location.reload(true);
			});

		}
		var genPassword = function() {
			$.get('/ajax/password.php', {
				action: 'gen'
			}, function(data) {
				$('#passwordField').val(data.newPass);
			}, "json");
		}
		var saveVadidDates = function() {
			var formData = $('#validForm').serializeArray();
			$.post('/ajax/inline.validdates.php', formData, function(data) {
				document.location.reload(true);
			});
		}
		var saveMemType = function() {
			var formData = $('#memTypeForm').serializeArray();
			$.post('/ajax/inline.profile.php', formData, function(data) {
				var str = data.dp_val;
				$('#display_memType').html(str.replace(", ", "<br>"));
				$('#memTypeModal').modal('hide');
			}, "json");
		}

		var customSidebarQuestion = function(qid) {
			$('#customQuestionModal .modal-body').html('<div class="text-center"><div class="m-loader m-loader--brand m-loader--lg" style="width:30px; display: inline-block;"></div><br><br> Loading Form...</div>');
			$('#customQuestionModal').modal('show');
			var currentValue = $('#displayVal_' + qid).text();
			$.post('/ajax/modal_question.php?action=showForm', {
				qid: qid,
				val: currentValue,
				pid: '<?php echo $PERSON_ID ?>'
			}, function(data) {
				$('#customQuestionModal .modal-body').html(data);
			});
		}
		var submitCustomQuestionModal = function() {
			var formData = $('#customQuestionModalForm').serializeArray();
			$.post('/ajax/modal_question.php?action=submitForm', formData, function(data) {
				$('#displayVal_' + data.qid).html(data.newVal);
				$('#customQuestionModal').modal('hide');
				toastr.success(data.qlabel + ' Updated', '', {
					timeOut: 5000
				});
			}, "json");
		}

		// NOTES HANDLING //
		var filterNoteHistory = function() {
			var formData = $('#notes-filter-form').serializeArray();
			$('#notes-pagination').twbsPagination('destroy');
			$.post('/ajax/notes.php?action=getList', formData, function(data) {
				$('#note-items-list').html(data.html);
				$('#found-notes').html('<span class="m-badge m-badge--info m-badge--wide m-badge--rounded">' + data.found + ' total notes</span>');
				$('#notes-pagination').html('');
				if (data.found != 0) {
					$('#notes-pagination').twbsPagination({
						totalPages: data.pages,
						visiblePages: 5,
						onPageClick: function(event, page) {
							//$('#page-content').text('Page ' + page);
							//console.log('Offset:'+((eval(page) - 1) * 20));
							var formDataF = $('#notes-filter-form').serializeArray();
							formDataF.forEach(function(item) {
								if (item.name === 'offset') {
									item.value = ((eval(page) - 1) * 20);
								}
							});
							$('#note-items-list').html('<div class="m-loader m-loader--brand m-loader--lg" style="width:30px; display: inline-block;"></div> Loading...');
							$.post('/ajax/notes.php?action=getList', formDataF, function(dataF) {
								$('#note-items-list').html(dataF.html);
								mApp.init();
							}, "json");
						}
					});
				}
			}, "json");
		}
		var clearNoteFilters = function() {
			$('#filterTasks').prop('checked', true);
			$('#filterActions').prop('checked', true);
			$('#filterNotes').prop('checked', true);
			$('#filterCalls').prop('checked', true);
			$('#filterEmails').prop('checked', true);
			$('#filterSMS').prop('checked', true);
			$('#filterString').val('');
			$('#filterDates').val(moment().subtract(11, 'months').format("M/D/YYYY") + ' - ' + moment().format("M/D/YYYY"));
			filterNoteHistory();
		}
		var saveNoteEdit = function(nid, pid) {
			var textareaValue = $('.noteedit-summernote').summernote('code');
			$.post('/ajax/notes.php?action=saveEdit', {
				id: nid,
				body: textareaValue,
				pid: pid
			}, function(data) {
				//$('#note-items-list').html(data.html);
				$('#body-display-area').html(data.body);
				cancelNoteEdit();
				toastr.success('Note Updated', '', {
					timeOut: 5000
				});
			}, "json");
		}
		var deleteNoteEdit = function(id, pid) {
			var choice = confirm('Are you sure you want to remvoe this note? THIS ACTION CANNOT BE UNDONE!!!');
			if (choice) {
				$.post('/ajax/notes.php?action=removeNote', {
					id: id,
					pid: pid
				}, function(data) {
					$('#historyModal').modal('hide');
					toastr.warning('Note Removed', '', {
						timeOut: 5000
					});
					getNoteHistory('<?php echo $PERSON_ID ?>');
				});
			}
		}
		var editNote = function() {
			$('#note-display-area').fadeOut(250, function() {
				$('#note-edit-area').fadeIn(250);
			});
		}
		var cancelNoteEdit = function() {
			$('#note-edit-area').fadeOut(250, function() {
				$('#note-display-area').fadeIn(250);
			});
		}

		var saveNote = function() {
			var textareaValue = $('.notes-summernote').summernote('code');
			//console.log(textareaValue);
			$('#PersonsNotes_body').val(textareaValue);
			var formData = $('#NotesForm').serializeArray();
			$.post('/ajax/notes.php?action=add', formData, function(data) {
				$('#addNotesModal').modal('hide');
				loadLastNote();
				toastr.success('Note Saved', '', {
					timeOut: 5000
				});
			});
		}
		var loadLastNote = function() {
			$('#display_lastNote_body').html('<div class="m-loader m-loader--brand m-loader--lg" style="width:30px; display: inline-block;"></div> Loading Last Note...');
			$.post('/ajax/notes.php?action=getLast', {
				pid: '<?php echo $PERSON_ID ?>'
			}, function(data) {
				//$('#display_lastNote_date').html(data.date);
				$('#display_lastNote_body').html(data.body);
			}, "json");
		}

		// IMAGE HANDLING //
		var ImageDropZone;
		$('#imgUploadModal').on('show.bs.modal', function(e) {
			console.log('open modal');
			$('#m-dropzone-three').addClass('dropzone');
			ImageDropZone = new Dropzone('#m-dropzone-three', {
				url: '/ajax/upload.php?pid=<?php echo $PERSON_ID ?>',
				paramName: "file",
				maxFiles: 10,
				maxFilesize: 36,
				acceptedFiles: "image/*",
				accept: function(e, o) {
					"justinbieber.jpg" == e.name ? o("Naha, you don't.") : o()
				}
			});

		});
		$('#imgUploadModal').on('hidden.bs.modal', function(e) {
			console.log('closed modal');
			$('#m-dropzone-three').removeClass('dropzone');
			ImageDropZone.destroy();
			imgThumbsRefresh('<?php echo $PERSON_ID ?>');
		});
		var imgThumbsRefresh = function(pid) {
			$('#img-preview-area').html('<div class="m-loader m-loader--brand m-loader--sm" style="width:30px; display: inline-block;"></div> Loading Images...');
			$.post('/ajax/images.php?action=thumbs', {
				pid: pid
			}, function(data) {
				$('#img-preview-area').html(data);
			});
		}
		var previewImage = function(iid, file) {
			$('#imgPreviewModal').modal('show');
			$('#img-preview-wrapper').html('<div class="m-loader m-loader--brand m-loader--lg" style="width:30px; display: inline-block;"></div> Loading Image...');
			$.post('/ajax/images.php?action=preview', {
				image: iid,
				path: file
			}, function(data) {
				var img_html = '<img src="' + data.file + '" class="img-fluid">';
				$('#img-preview-wrapper').html(img_html + '<br>Uploaded: ' + data.upped + ' by ' + data.uppedby);
				$('#imageUpdateImageID').val(iid);
				//$('#imageUpdateForm input[value="1"]').prop('checked', true);
				$('#imageUpdateForm input[value="' + eval(data.result.PersonsImages_status) + '"]').prop('checked', true);
			}, "json");
		}
		var saveImages = function() {
			var formData = $('#imageUpdateForm').serializeArray();
			console.log(formData);
			$.post('/ajax/images.php?action=save', formData, function(data) {
				$('#imgPreviewModal').modal('hide');
			});
		}
		var removeImage = function() {
			var image_id = $('#imageUpdateImageID').val();
			var choice = confirm('Are you sure you want to remove this image from the system? THIS ACTION CANNOT BE UNDONE!');
			if (choice) {
				$.post('/ajax/images.php?action=remove', {
					id: image_id,
					kiss_token: '<?php echo $SESSION->createToken() ?>'
				}, function(data) {
					$('#imgPreviewModal').modal('hide');
					imgThumbsRefresh('<?php echo $PERSON_ID ?>');
				});
			}
		}



		var getPotentialMatches = function(pid, skip = 0) {
			$('#preSearchResults').html('<div class="m-loader m-loader--brand m-loader--lg" style="width: 30px; display: inline-block;"></div><br><br> Loading Potential Matches...');
			$.post('/ajax/potentialMatches.php', {
				pid: pid,
				skp: skip,
				kiss_token: '<?php echo $SESSION->createToken() ?>'
			}, function(data) {
				$('#preSearchResults').html(data);
				mApp.init();
			});
		}
		var updateSource = function() {
			var formData = $('#sourceForm').serializeArray();
			$.post('/ajax/inline.basic.php', formData, function(data) {
				$('#display_Source_name').html(data.dp_val);
				$('#sourceModal').modal('hide');
			}, "json");

		}
		var updateColor = function() {
			var formData = $('#colorForm').serializeArray();
			$.post('/ajax/inline.basic.php', formData, function(data) {
				$('#display_PersonColor').html(data.dp_val);
				$('#colorModal').modal('hide');
			}, "json");

		}
		var updateAssigned = function() {
			var formData = $('#assignedForm').serializeArray();
			$.post('/ajax/inline.basic.php', formData, function(data) {
				$('#display_Assigned_userID').html(data.dp_val);
				$('#assignedModal').modal('hide');
			}, "json");
		}

		var updateMachmaker = function() {
			var formData = $('#matchmakerForm').serializeArray();
			$.post('/ajax/inline.basic.php', formData, function(data) {
				$('#display_MM_userID').html(data.dp_val);
				$('#matchmakerModal').modal('hide');
			}, "json");
		}
		var updateMachmaker2 = function() {
			var formData = $('#matchmaker2Form').serializeArray();
			$.post('/ajax/inline.basic.php', formData, function(data) {
				$('#display_MM2_userID').html(data.dp_val);
				$('#matchmakerModal2').modal('hide');
			}, "json");
		}
		var updateStage = function() {
			var formData = $('#stagesForm').serializeArray();
			$.post('/ajax/inline.basic.php', formData, function(data) {
				$('#display_Stages_name').html(data.dp_val);
				$('#stageModal').modal('hide');
			}, "json");

		}
		var updateOffice = function() {
			var formData = $('#officeForm').serializeArray();
			$.post('/ajax/inline.basic.php', formData, function(data) {
				$('#display_Office_name').html(data.dp_val);
				$('#Offices_id').val(data.db_val);
				$('#officeModal').modal('hide');
			}, "json");

		}
		var updatePOD = function() {
			var formData = $('#podForm').serializeArray();
			$.post('/ajax/inline.basic.php', formData, function(data) {
				$('#display_POD_name').html(data.dp_val);
				$('#Pod_id').val(data.db_val);
				$('#podModal').modal('hide');
			}, "json");

		}
		var removeOldPhone = function(divID, pid) {
			var choice = confirm('Are you sure you want to remove this phone number?');
			if (choice) {
				$('#' + divID).remove();
				$.post('/ajax/phones.php?action=remove', {
					phone_id: pid
				}, function(data) {
					// do nothing //
				});
			}
		}
		var removeNewPhone = function(divID) {
			$('#' + divID).remove();
		}
		var loadNewPhone = function() {
			$.post('/ajax/phones.php?action=new', {
				action: 'new'
			}, function(data) {
				//document.location.reload(true);
				$('#phones-form-list').append(data);
			});
		}
		var savePhones = function() {
			var formData = $('#phoneForm').serializeArray();
			$.post('/ajax/phones.php?action=save', formData, function(data) {
				document.location.reload(true);
			});
		}
		var newAddress = function() {
			$('#Street_1').val('');
			$('#City').val('');
			$('#State').val('');
			$('#Postal').val('');
			$('#address_isPrimary').attr('checked', false);
			$('#addressID').val('');
			$('#geolocate-failed').removeClass('m--hide');
			$('#geolocate-success').addClass('m--hide');
			$('#button-geolocate').attr('disabled', true);
		}
		var saveAddress = function() {
			var formData = $('#addressForm').serializeArray();
			$.post('/ajax/addresses.php?action=save', formData, function(data) {
				document.location.reload(true);
			});
		}
		var rmAddress = function() {
			var aid = $('#addressID').val();
			var choice = confirm('Are you sure you want to delete this address?');
			if (choice) {
				$.post('/ajax/addresses.php?action=delete', {
					aid: aid
				}, function(data) {
					document.location.reload(true);
				});
			}
		}
		var setCountryStates = function() {
			var country = $('#Country').val();
			$.post('/ajax/select.states.php', {
				country: country
			}, function(data) {
				$('#State').html(data);
			});
		}
		var inlineSubmit = function(divID) {
			var formURL = $('#' + divID + ' .inline-edit-form form input[name="url"]').val();
			var formData = $('#' + divID + ' .inline-edit-form form').serializeArray();
			console.log(formURL);
			console.log(formData);
			$('#' + divID + ' .inline-edit-form').html('<div class="m-loader m-loader--brand m-loader--sm" style="width:30px; display: inline-block;"></div> updating...');
			$.post(formURL, formData, function(data) {
				$('#' + divID + ' .inline-edit-display').html(data.dp_val);
				if (divID != 'Persons_password') {
					$('#' + divID + ' .inline-edit-display').attr('data-value', data.db_val);
				} else {
					$('#' + divID + ' .inline-edit-display').attr('data-value', '');
				}
				$('#' + divID + ' .inline-edit-display').show();
				$('#' + divID + ' .inline-edit-form').html('');
			}, "json");
		}
		var cancelEdit = function(divID) {
			$('#' + divID + ' .inline-edit-display').show();
			$('#' + divID + ' .inline-edit-form').html('');
		}
		var getDOBfromAGE = function() {
			var age = $('#value_age').val();
			var now = moment();
			var dob = moment().subtract(age, 'years');
			console.log(dob.format('MM'));
			console.log(dob.format('D'));
			console.log(dob.format('YYYY'));
			$('#value_m').val('01');
			$('#value_d').val('1');
			$('#value_y').val(dob.format('YYYY'));
		}

		function openFormHistory() {
			$('#historyModal .modal-title').html('Record Form History');
			$('#historyModal .modal-body').html('<div class="m-loader m-loader--brand m-loader--lg" style="width:30px; display: inline-block;"></div> Loading Data History...');
			$('#historyModal').modal('show');
			$.post('/ajax/notes.php?action=formhistory', {
				pid: '<?php echo $PERSON_ID ?>'
			}, function(data) {
				$('#historyModal .modal-body').html(data);
				mApp.init();
			});
		}
	</script>
<?php
	$RTRACK->updateRecordViewLog($PERSON_ID, $_SESSION['system_user_id']);
else :
?>
	<style>
		.nodetail-print {
			display: none;
		}
	</style>
	<div class="m-content">
		<div class="m-alert m-alert--icon alert alert-danger" role="alert">
			<div class="m-alert__icon">
				<i class="flaticon-danger"></i>
			</div>
			<div class="m-alert__text">
				<?php if ($PDATA['PersonsTypes_id'] == 3) : ?>
					<div class="pull-right">
						<button type="button" class="btn btn-default" id="button-awaken-record" onclick="sendRequestToOwner('<?php echo $PERSON_ID ?>', '<?php echo $PDATA['Assigned_userID'] ?>', '<?php echo $_SESSION['system_user_id'] ?>')"><i class="flaticon-users"></i> Send Request to Awaken this Record to its Owner <i class="fa fa-arrow-right"></i></button>
					</div>
				<?php endif; ?>
				<strong>
					WARNING
				</strong>
				<p>Your account does not grant you permissions to view this record.<br />
					If you would like to gain access to this record please request access from:</p>
				<ul>
					<?php if ($PDATA['Assigned_userID'] != 0) { ?><li><?php echo $RECORD->get_userName($PDATA['Assigned_userID']) ?></li><?php } ?>
					<?php if ($PDATA['Matchmaker_id'] != 0) { ?><li><?php echo $RECORD->get_userName($PDATA['Matchmaker_id']) ?></li><?php } ?>
				</ul>
				or record supervisor.
			</div>
		</div>
		<?php
		$_POST['pid'] = $PERSON_ID;
		$PSQL = "
SELECT 
	*
FROM
	Persons
	INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id
	LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id	
WHERE 
	Persons.Person_id='" . $_POST['pid'] . "'
		";
		$PSND = $DB->get_single_result($PSQL);
		$hideStyle = true;
		ob_start();
		include_once("./ajax/template.quickPrint.php");
		$printOutput = ob_get_clean();
		echo $printOutput;
		?>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
	</div>
	<script>
		function sendRequestToOwner(p, s, u) {
			$('#button-awaken-record').prop('disabled', true);
			$('#button-awaken-record').html('<i class="fa fa-circle-o-notch fa-spin"></i> Sending Request to Record Owner...');
			$.post('/ajax/quick-tick.php?action=awkenRequest', {
				p: p,
				s: s,
				u: u
			}, function(data) {
				if (data.success) {
					toastr.success('Request Sent', '', {
						timeOut: 5000
					});
				} else {
					//alert(data.error);
					toastr.error(data.error, '', {
						timeOut: 0,
						closeButton: true,
						tapToDismiss: false
					});
				}
				$('#button-awaken-record').prop('disabled', false);
				$('#button-awaken-record').html('<i class="flaticon-users"></i> Send Request to Awaken this Record to its Owner <i class="fa fa-arrow-right"></i>');
			}, "json");
		}
	</script>
<?php
	$RTRACK->updateRecordViewLog($PERSON_ID, $_SESSION['system_user_id']);
endif;
?>