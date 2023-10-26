<?php
//error_reporting(E_ERROR | E_PARSE);

/*! \class Page class.page.php "class.page.php"
 *  \brief This class is used to draw and render the actualy page.
 */
class Page {
	/*! \fn obj __constructor($DB)
		\brief Page class constructor.
		\param	$DB db class object
		\param	$USER user class object
		\return null
	*/
	function __construct($DB, $USER) {
		$this->db 	= 	$DB;
		$this->usr	=	$USER;
	}
	
	/* 	\fn array parseURLString()
		\brief parses the url string into an array of variables
		\return array
	*/
	function parseURLString() {
		$urlParts = explode("/", $_GET['path']);
      	for($i=0; $i<count($urlParts); $i++) {
			if($i == 0) {
				$return['page'] = $urlParts[$i];	
			} else {
				$return['params'][] = $urlParts[$i];
			}
		}
		return $return;
	}
	
	function renderHeader() {
		?>
    <meta charset="utf-8" />
    <title>(KISS) Kelleher International Software System</title>
    <meta name="description" content="Latest updates and statistic charts">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!--begin::Base Scripts -->
    <script src="/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/app.js" type="text/javascript"></script>
    <script src="/assets/demo/default/base/scripts.bundle.full.js" type="text/javascript"></script>
    <!--end::Base Scripts -->   
    <!--begin::Page Vendors -->
    <script src="/assets/vendors/custom/fullcalendar/fullcalendar.bundle.js" type="text/javascript"></script>
    <!--<script src="/assets/vendors/custom/bootstrap3-editable/js/bootstrap-editable.js"></script>-->
    <script type="text/javascript" src="/assets/vendors/custom/twbs-pagination/jquery.twbsPagination.min.js"></script>
    <!--end::Page Vendors -->  
    <!--begin::Page Snippets -->
    
    <!--end::Page Snippets -->   
    <!-- begin::Page Loader -->
    
    <!--begin::Web font -->
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>    
    
    <script>
      WebFont.load({
        google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
        active: function() {
            sessionStorage.fonts = true;
        }
      });
    </script>
    <!--end::Web font -->
    <!--begin::Base Styles -->  
    <!--begin::Page Vendors -->
    <link href="/assets/vendors/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Page Vendors -->
    <link href="/assets/vendors/base/vendors.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/demo/default/base/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/app/css/kelleher.css" rel="stylesheet" type="text/css" />
    <!--end::Base Styles -->
    <link rel="shortcut icon" href="/favicon.ico" />
		<?php
	}
	
	function render_PageLoader() {
		?>
        <div class="m-page-loader">
            <div class="m-blockui">
                <span>
                    Please wait...
                </span>
                <span>
                    <div class="m-loader m-loader--brand"></div>
                </span>
            </div>
        </div>	
		<?php
	}
	
	function renderViewableHeader($user_id) {
		global $SESSIONS;
		$userPerms = $this->usr->get_userPermissions($user_id);
		?>
		<!-- BEGIN: Header -->
		<header class="m-grid__item m-header "  data-minimize-offset="200" data-minimize-mobile-offset="200" >
			<div class="m-container m-container--fluid m-container--full-height">
				<div class="m-stack m-stack--ver m-stack--desktop">
					<!-- BEGIN: Brand -->
					<div class="m-stack__item m-brand  m-brand--skin-dark ">
						<div class="m-stack m-stack--ver m-stack--general">
							<div class="m-stack__item m-stack__item--middle m-brand__logo">
                            	<a href="/" class="m-brand__logo-wrapper"><img alt="" src="/assets/app/media/img/logos/logo-kelleher.png"/></a>
							</div>
							<div class="m-stack__item m-stack__item--middle m-brand__tools">
                                <!-- BEGIN: Left Aside Minimize Toggle -->
                                <a href="javascript:;" id="m_aside_left_minimize_toggle" class="m-brand__icon m-brand__toggler m-brand__toggler--left m--visible-desktop-inline-block <?php echo (($_SESSION['navSize'] == 'collapsed')?'m-brand__toggler--active':'')?>"><span></span></a>
                                <!-- END -->
                                
                                <!-- BEGIN: Responsive Aside Left Menu Toggler -->
                                <a href="javascript:;" id="m_aside_left_offcanvas_toggle" class="m-brand__icon m-brand__toggler m-brand__toggler--left m--visible-tablet-and-mobile-inline-block"><span></span></a>
                                <!-- END -->
                                
                                <!-- BEGIN: Responsive Header Menu Toggler -->
                                <a id="m_aside_header_menu_mobile_toggle" href="javascript:;" class="m-brand__icon m-brand__toggler m--visible-tablet-and-mobile-inline-block"><span></span></a>
                                <!-- END -->
                                
                                <!-- BEGIN: Topbar Toggler -->
                                <a id="m_aside_header_topbar_mobile_toggle" href="javascript:;" class="m-brand__icon m--visible-tablet-and-mobile-inline-block"><i class="flaticon-more"></i></a>
                                <!-- BEGIN: Topbar Toggler -->
							</div>
						</div>
					</div>
					<!-- END: Brand -->
                    <script type="module">
                        import Chatbot from "https://cdn.jsdelivr.net/npm/flowise-embed/dist/web.js"
                        Chatbot.init({
                            chatflowid: "3b7dec44-35ff-4d65-9037-210b450f66e3",
                            apiHost: "https://kiss-flow.onrender.com",
                        })
                    </script>
					<div class="m-stack__item m-stack__item--fluid m-header-head" id="m_header_nav">
						<!-- BEGIN: Horizontal Menu -->
						<button class="m-aside-header-menu-mobile-close  m-aside-header-menu-mobile-close--skin-dark " id="m_aside_header_menu_mobile_close_btn"><i class="la la-close"></i></button>
							<div id="m_header_menu" class="m-header-menu m-aside-header-menu-mobile m-aside-header-menu-mobile--offcanvas  m-header-menu--skin-light m-header-menu--submenu-skin-light m-aside-header-menu-mobile--skin-dark m-aside-header-menu-mobile--submenu-skin-dark "  >
								<ul class="m-menu__nav  m-menu__nav--submenu-arrow ">
									<li class="m-menu__item  m-menu__item--submenu m-menu__item--rel"  data-menu-submenu-toggle="click" data-redirect="true" aria-haspopup="true">
										<a  href="#" class="m-menu__link m-menu__toggle">
											<i class="m-menu__link-icon flaticon-add"></i>
											<span class="m-menu__link-text">Actions</span>
											<i class="m-menu__hor-arrow la la-angle-down"></i>
											<i class="m-menu__ver-arrow la la-angle-right"></i>
										</a>
										<div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left">
											<span class="m-menu__arrow m-menu__arrow--adjust"></span>
											<ul class="m-menu__subnav">
												<li class="m-menu__item "  aria-haspopup="true">
													<a href="/new" class="m-menu__link ">
														<i class="m-menu__link-icon la la-user-plus"></i>
														<span class="m-menu__link-text">Create New Person</span>
													</a>
												</li>
												<li class="m-menu__item "  data-redirect="true" aria-haspopup="true">
													<a href="/myleads" class="m-menu__link ">
														<i class="m-menu__link-icon flaticon-users"></i>
														<span class="m-menu__link-title">
															<span class="m-menu__link-wrap">
																<span class="m-menu__link-text">View My Leads</span>
																<span class="m-menu__link-badge">
																	<span class="m-badge m-badge--info">
																		<?php echo $this->get_AssignedLeadsCount($_SESSION['system_user_id'])?>
																	</span>
																</span>
															</span>
														</span>
													</a>
												</li>
                                                <li class="m-menu__item "  data-redirect="true" aria-haspopup="true">
													<a href="/myclients" class="m-menu__link ">
														<i class="m-menu__link-icon flaticon-user-ok"></i>
														<span class="m-menu__link-title">
															<span class="m-menu__link-wrap">
																<span class="m-menu__link-text">View My Clients</span>
																<span class="m-menu__link-badge">
																	<span class="m-badge m-badge--success">
																		<?php echo $this->get_AssignedClientsCount($_SESSION['system_user_id'])?>
																	</span>
																</span>
															</span>
														</span>
													</a>
												</li>
                                                <li class="m-menu__item "  data-redirect="true" aria-haspopup="true">
													<a href="/myintros" class="m-menu__link ">
														<i class="m-menu__link-icon la la-heart-o"></i>
														<span class="m-menu__link-title">
															<span class="m-menu__link-wrap">
																<span class="m-menu__link-text">View My Intros</span>
																<span class="m-menu__link-badge">
																	<span class="m-badge m-badge--brand">
																		<?php echo $this->get_IntroRecordCount($_SESSION['system_user_id'])?>
																	</span>
																</span>
															</span>
														</span>
													</a>
												</li>
											</ul>
										</div>
									</li>
									<li class="m-menu__item  m-menu__item--submenu m-menu__item--rel"  data-menu-submenu-toggle="click" data-redirect="true" aria-haspopup="true">
										<a  href="#" class="m-menu__link m-menu__toggle">
											<i class="m-menu__link-icon flaticon-line-graph"></i>
											<span class="m-menu__link-text">
												Reports
											</span>
											<i class="m-menu__hor-arrow la la-angle-down"></i>
											<i class="m-menu__ver-arrow la la-angle-right"></i>
										</a>
										<div class="m-menu__submenu  m-menu__submenu--fixed m-menu__submenu--left" style="width:650px;">
											<span class="m-menu__arrow m-menu__arrow--adjust"></span>
											<div class="m-menu__subnav">
												<ul class="m-menu__content">
													<li class="m-menu__item">
														<h3 class="m-menu__heading m-menu__toggle">
															<span class="m-menu__link-text">
																My Reports
															</span>
															<i class="m-menu__ver-arrow la la-angle-right"></i>
														</h3>
														<div class="m-scrollable" data-scrollable="true" data-max-height="500" style="margin-right:10px;">
                                                        <ul class="m-menu__inner">
															<?php
															$rpt_sql = "SELECT * FROM Reports INNER JOIN ReportsAccess ON Reports.Report_id=ReportsAccess.Report_id WHERE user_id='".$_SESSION['system_user_id']."' ORDER BY Report_createdDate DESC";
															//echo $rpt_sql;
															$rpt_snd = $this->db->get_multi_result($rpt_sql);
															if(isset($rpt_snd['empty-result'])):
															?>
                                                            <li class="m-menu__item "  data-redirect="true" aria-haspopup="true">
																<a  href="#" class="m-menu__link ">
																	<i class="m-menu__link-icon flaticon-graphic-1"></i>
																	<span class="m-menu__link-text">
																		No Reports Found
																	</span>
																</a>
															</li>
                                                            <?php
															else:
																foreach($rpt_snd as $rpt_dta):																
																?>                                                            
                                                                <li class="m-menu__item "  data-redirect="true" aria-haspopup="true">
                                                                    <a  href="/page.php?path=viewreport/<?php echo $rpt_dta['Report_id']?>" class="m-menu__link ">
                                                                        <i class="m-menu__link-icon flaticon-graphic-1"></i>
                                                                        <span class="m-menu__link-text">
                                                                        	<?php echo $rpt_dta['Report_name']?>
                                                                        </span>
                                                                    </a>
                                                                </li>
                                                                <?php
																endforeach;
															endif;
															?>
														</ul>
                                                        </div>
													</li>												
													<li class="m-menu__item">
														<h3 class="m-menu__heading m-menu__toggle">
															<span class="m-menu__link-text">
																Report Management
															</span>
															<i class="m-menu__ver-arrow la la-angle-right"></i>
														</h3>
														<ul class="m-menu__inner">
															<?php if(in_array(82, $userPerms)): ?>
                                                            <li class="m-menu__item "  data-redirect="true" aria-haspopup="true">
																<a  href="/reports" class="m-menu__link ">
																	<span class="m-menu__link-text">
																		Create New Report <i class="fa fa-plus"></i>
																	</span>
																</a>
															</li>
                                                            <?php endif; ?>
                                                            <?php if(in_array(85, $userPerms)): ?>
                                                            <li class="m-menu__item "  data-redirect="true" aria-haspopup="true">
																<a  href="/allreports" class="m-menu__link ">
																	<span class="m-menu__link-text">
																		View All Reports <i class="flaticon-file-1"></i>
																	</span>
																</a>
															</li>
                                                            <?php endif; ?>
                                                            <li class="m-menu__item "  data-redirect="true" aria-haspopup="true">
                                                                <a  href="/page.php?path=viewreport/97" class="m-menu__link ">
                                                                    <i class="m-menu__link-icon flaticon-graphic-1"></i>
                                                                    <span class="m-menu__link-text">
                                                                        My Participating Members
                                                                    </span>
                                                                </a>
                                                            </li>
                                                            															
														</ul>
													</li>
												</ul>
											</div>
										</div>
									</li>
                                    
                                    <li class="m-menu__item  m-menu__item--submenu m-menu__item--rel" id="historyMenu" data-menu-submenu-toggle="click" data-redirect="true" aria-haspopup="true">
										<a href="#" class="m-menu__link m-menu__toggle">
											<i class="m-menu__link-icon flaticon-paper-plane"></i>
											<span class="m-menu__link-title">
												<span class="m-menu__link-wrap">
													<span class="m-menu__link-text">
														My History
													</span>
                                                    <!--
													<span class="m-menu__link-badge">
														<span class="m-badge m-badge--danger m-badge--wide">
															new
														</span>
													</span>
                                                    -->
												</span>
											</span>
											<i class="m-menu__hor-arrow la la-angle-down"></i>
											<i class="m-menu__ver-arrow la la-angle-right"></i>
										</a>
										<div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left" id="historyMenuList">
											<span class="m-menu__arrow m-menu__arrow--adjust"></span>
											<ul class="m-menu__subnav">
												
											</ul>
										</div>
									</li>
                                    									
								</ul>
							</div>
							<!-- END: Horizontal Menu -->								<!-- BEGIN: Topbar -->
							<div id="m_header_topbar" class="m-topbar  m-stack m-stack--ver m-stack--general">
								<div class="m-stack__item m-topbar__nav-wrapper">
									<ul class="m-topbar__nav m-nav m-nav--inline">
										<?php if($_SESSION && $_SESSION['spoofed_user'] == 1): ?>
                                        <li class="m-nav__item">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="clearSession()" data-container="body" data-toggle="m-tooltip" data-placement="bottom" title="" data-original-title="You are currently impersonating a user." style="margin-top:16px;">
											<i class="flaticon-users"></i>
											</button>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php
										$up_sql = "SELECT userMeetingURL FROM Users WHERE user_id='".$_SESSION['system_user_id']."'";
										$up_snd = $this->db->get_single_result($up_sql);
										if($up_snd['userMeetingURL'] != ''):
										?>
                                        <li class="m-nav__item m-dropdown m-dropdown--large">
											<a href="<?php echo $up_snd['userMeetingURL']?>" class="m-nav__link" target="_blank" data-skin="dark" data-toggle="m-tooltip" data-placement="bottom" title="Meeting Room URL Link">
												<span class="m-nav__link-icon">
													<i class="flaticon-imac"></i>
												</span>
											</a>
										</li>
										<?php
										endif;
										?>                                            
                                        
                                        <li class="m-nav__item m-dropdown m-dropdown--large m-dropdown--arrow m-dropdown--align-center m-dropdown--mobile-full-width m-dropdown--skin-light	m-list-search m-list-search--skin-light" data-dropdown-toggle="click" data-dropdown-persistent="true" id="m_quicksearch" data-search-type="dropdown">
											<a href="#" class="m-nav__link m-dropdown__toggle">
												<span class="m-nav__link-icon">
													<i class="flaticon-search-1"></i>
												</span>
											</a>
											<div class="m-dropdown__wrapper">
												<span class="m-dropdown__arrow m-dropdown__arrow--center"></span>
												<div class="m-dropdown__inner ">
													<div class="m-dropdown__header">
														<form class="m-list-search__form" onsubmit="return false;">
                                                        	<?php echo $SESSIONS->renderToken()?>
															<div class="m-list-search__form-wrapper">
																<span class="m-list-search__form-input-wrapper">
																	<input id="m_quicksearch_input_custom" autocomplete="off" type="text" name="q" class="m-list-search__form-input" value="" placeholder="Quick Search...">
																</span>
                                                                <span class="m-list-search__form-icon-close" id="m_quicksearch_clear" title="clear text">
																	<i class="la la-undo"></i>
																</span>
																<span class="m-list-search__form-icon-close" id="m_quicksearch_close">
																	<i class="la la-remove"></i>
																</span>
															</div>                                                            
														</form> 
                                                        <small>First and Last Name, Email, ID &amp; Phone Number search</small>                                                       
													</div>
													<div class="m-dropdown__body" id="m-qsearch-display">                                                    	
														<div class="m-dropdown__scrollable m-scrollable" data-scrollable="true" data-max-height="300" data-mobile-max-height="200">
															<div class="m-dropdown__content" id="m-qsearch-result"></div>
														</div>
													</div>
												</div>
											</div>
										</li>
										<li class="m-nav__item m-topbar__notifications m-topbar__notifications--img m-dropdown m-dropdown--large m-dropdown--header-bg-fill m-dropdown--arrow m-dropdown--align-center 	m-dropdown--mobile-full-width" data-dropdown-toggle="click" data-dropdown-persistent="true">
											<a href="#" class="m-nav__link m-dropdown__toggle" id="m_topbar_notification_icon">
												<span class="m-nav__link-badge m-badge m-badge--dot m-badge--dot-small m-badge--danger"></span>
												<span class="m-nav__link-icon">
													<i class="flaticon-music-2"></i>
												</span>
											</a>
											<div class="m-dropdown__wrapper">
												<span class="m-dropdown__arrow m-dropdown__arrow--center"></span>
												<div class="m-dropdown__inner">
													<div class="m-dropdown__header m--align-center" style="background: url(/assets/app/media/img/misc/notification_bg-red.jpg); background-size: cover;">
														<span class="m-dropdown__header-title">
															# New
														</span>
														<span class="m-dropdown__header-subtitle">
															User Notifications
														</span>
													</div>
													<div class="m-dropdown__body">
														<div class="m-dropdown__content">
															<ul class="nav nav-tabs m-tabs m-tabs-line m-tabs-line--brand" role="tablist">
																<li class="nav-item m-tabs__item">
																	<a class="nav-link m-tabs__link active" data-toggle="tab" href="#topbar_notifications_notifications" role="tab">
																		Alerts
																	</a>
																</li>
																<li class="nav-item m-tabs__item">
																	<a class="nav-link m-tabs__link" data-toggle="tab" href="#topbar_notifications_logs" role="tab">
																		Logs
																	</a>
																</li>
															</ul>
															<div class="tab-content">
																<div class="tab-pane active" id="topbar_notifications_notifications" role="tabpanel">
																	<div class="m-stack m-stack--ver m-stack--general" style="min-height: 180px;">
																		<div class="m-stack__item m-stack__item--center m-stack__item--middle">
																			<span class="">No Alerts or Notifications</span>
																		</div>
																	</div> 
																</div>																
																<div class="tab-pane" id="topbar_notifications_logs" role="tabpanel">
																	<?php echo $this->usr->render_userLogs($_SESSION['system_user_id']); ?>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</li>
										<li class="m-nav__item m-topbar__quick-actions m-topbar__quick-actions--img m-dropdown m-dropdown--large m-dropdown--header-bg-fill m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push m-dropdown--mobile-full-width m-dropdown--skin-light"  data-dropdown-toggle="click">
											<a href="#" class="m-nav__link m-dropdown__toggle">
												<span class="m-nav__link-badge m-badge m-badge--dot m-badge--info m--hide"></span>
												<span class="m-nav__link-icon">
													<i class="flaticon-share"></i>
												</span>
											</a>
											<div class="m-dropdown__wrapper">
												<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
												<div class="m-dropdown__inner">
													<div class="m-dropdown__header m--align-center" style="background: url(/assets/app/media/img/misc/quick_actions_bg-red.jpg); background-size: cover;">
														<span class="m-dropdown__header-title">
															Quick Actions
														</span>
														<span class="m-dropdown__header-subtitle">
															Shortcuts
														</span>
													</div>
													<div class="m-dropdown__body m-dropdown__body--paddingless">
														<div class="m-dropdown__content">
															<div class="m-scrollable" data-scrollable="false" data-max-height="380" data-mobile-max-height="200">
																<div class="m-nav-grid m-nav-grid--skin-light">
																	<div class="m-nav-grid__row">
																		<a href="/new" class="m-nav-grid__item">
																			<i class="m-nav-grid__icon flaticon-user-add"></i>
																			<span class="m-nav-grid__text">
																				Add New Person
																			</span>
																		</a>
                                                                        <a href="/newintro" class="m-nav-grid__item">
																			<i class="m-nav-grid__icon flaticon-folder-2"></i>
																			<span class="m-nav-grid__text">
																				Create New Intro
																			</span>
																		</a>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</li>
										<li class="m-nav__item m-topbar__user-profile m-topbar__user-profile--img  m-dropdown m-dropdown--medium m-dropdown--arrow m-dropdown--header-bg-fill m-dropdown--align-right m-dropdown--mobile-full-width m-dropdown--skin-light" data-dropdown-toggle="click">
											<a href="#" class="m-nav__link m-dropdown__toggle">
												<span class="m-topbar__userpic">
													<img src="<?php echo $this->usr->get_userImage($_SESSION['system_user_id'])?>" class="m--img-rounded m--marginless m--img-centered" alt=""/>
												</span>
												<span class="m-topbar__username m--hide">
                                                    <?php echo $this->usr->get_userName($_SESSION['system_user_id'])?>
												</span>
											</a>
											<div class="m-dropdown__wrapper">
												<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
												<div class="m-dropdown__inner">
													<div class="m-dropdown__header m--align-center" style="background: url(/assets/app/media/img/misc/user_profile_bg-red.jpg); background-size: cover;">
														<div class="m-card-user m-card-user--skin-dark">
															<div class="m-card-user__pic">
																<img src="<?php echo $this->usr->get_userImage($_SESSION['system_user_id'])?>" class="m--img-rounded m--marginless" alt=""/>
															</div>
															<div class="m-card-user__details">
																<span class="m-card-user__name m--font-weight-500">
																	<?php echo $this->usr->get_userFullName($_SESSION['system_user_id'])?>
																</span>
																<a href="/myconfig" class="m-card-user__email m--font-weight-300 m-link">
																	<?php echo $this->usr->get_userEmail($_SESSION['system_user_id'])?>
																</a>
															</div>
														</div>
													</div>
													<div class="m-dropdown__body">
														<div class="m-dropdown__content">
															<ul class="m-nav m-nav--skin-light">
																<li class="m-nav__section m--hide">
																	<span class="m-nav__section-text">
																		Section
																	</span>
																</li>
																<li class="m-nav__item">
																	<a href="/myconfig" class="m-nav__link">
																		<i class="m-nav__link-icon flaticon-profile-1"></i>
																		<span class="m-nav__link-title">
																			<span class="m-nav__link-wrap">
																				<span class="m-nav__link-text">
																					My Profile
																				</span>
																			</span>
																		</span>
																	</a>
																</li>
                                                                <li class="m-nav__item">
																	<a href="/mkg-templates" class="m-nav__link">
																		<i class="m-nav__link-icon flaticon-file-1"></i>
																		<span class="m-nav__link-text">
																			My eMail Templates
																		</span>
																	</a>
																</li>
																<li class="m-nav__item">
																	<a href="/api-connections" class="m-nav__link">
																		<i class="m-nav__link-icon flaticon-map"></i>
																		<span class="m-nav__link-text">
																			API Connections
																		</span>
																	</a>
																</li>    
                                                                <!--  Add link to KISS task list  -->
                                                                <li class="m-nav__item">
                                                                    <a href="https://ki-match.link/kiss-list" class="m-nav__link" target=”_blank”>
                                                                         <i class="m-nav__link-icon flaticon-clipboard"></i>
																		<span class="m-nav__link-text">
																			KISS Tasks
																		</span>
                                                                    </a>
                                                                </li>
																<li class="m-nav__separator m-nav__separator--fit"></li>
																<!--
                                                                <li class="m-nav__item">
																	<a href="header/profile.html" class="m-nav__link">
																		<i class="m-nav__link-icon flaticon-info"></i>
																		<span class="m-nav__link-text">
																			FAQ
																		</span>
																	</a>
																</li>
                                                              	-->
																
																<li class="m-nav__separator m-nav__separator--fit"></li>
																<li class="m-nav__item">
																	<a href="/signout.php" class="btn m-btn--pill    btn-secondary m-btn m-btn--custom m-btn--label-brand m-btn--bolder">
																		Logout
																	</a>
																</li>
                                                                <li class="m-nav__item">
																	<a href="/securelogout.php" class="btn m-btn--pill    btn-secondary m-btn m-btn--custom m-btn--label-brand m-btn--bolder">
																		New Secure Logout
																	</a>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</li>
										<li id="m_quick_sidebar_toggle" class="m-nav__item">
											<a href="#" class="m-nav__link m-dropdown__toggle">
												<span class="m-nav__link-icon">
													<i class="flaticon-grid-menu"></i>
												</span>
											</a>
										</li>
									</ul>
								</div>
							</div>
							<!-- END: Topbar -->
						</div>
					</div>
				</div>
			</header>
		<!-- END: Header -->
            <?php
	} 
	
	function get_AssignedLeadsCount($personID) {
		$sql = "SELECT COUNT(*) as count FROM Persons INNER JOIN Offices ON Offices.Offices_id=Persons.Offices_id INNER JOIN LeadStages ON LeadStages.LeadStages_id=Persons.LeadStages_id INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id WHERE Persons.PersonsTypes_id IN (3, 13) AND Persons.PersonsStatus_id='1' AND Persons.LeadStages_id != '8' AND PersonsProfile.prQuestion_1713 NOT IN ('Dead Lead','5 - Cold','Cold') AND Persons.Assigned_userID='".$_SESSION['system_user_id']."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['count'];
	}
	
	function get_MyTasksCount($personID) {
		$sql = "SELECT COUNT(*) as count FROM PersonActions INNER JOIN PersonActionTypes ON PersonActionTypes.ActionTypeID=PersonActions.ActionTypeID INNER JOIN Persons ON Persons.Person_id=PersonActions.ActionPersonID INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id WHERE ActionAssignedTo='".$_SESSION['system_user_id']."' AND ActionCompleted='0'";
		$snd = $this->db->get_single_result($sql);
		return $snd['count'];
	}
	
	function get_AssignedClientsCount($personID) {
		$sql = "SELECT COUNT(*) as count FROM Persons WHERE PersonsTypes_id NOT IN (1, 2, 3, 9, 5, 11, 13) AND PersonsStatus_id='1' AND Persons.Matchmaker_id='".$_SESSION['system_user_id']."'";
		//Persons.PersonsTypes_id NOT IN (1, 2, 3, 9, 5, 11, 13)
		$snd = $this->db->get_single_result($sql);
		return $snd['count'];
	}
	
	function get_ClientRecordTypeCount($type_id) {
		$sql = "SELECT COUNT(*) as count FROM Persons WHERE PersonsTypes_id='".$type_id."' AND PersonsStatus_id='1'";
		$snd = $this->db->get_single_result($sql);
		return $snd['count'];
	}
	
	function get_IntroRecordCount($personID) {
		$sql = "SELECT COUNT(*) as count FROM PersonsDates WHERE PersonsDates_assignedTo='".$personID."' AND PersonsDates_status IN (0, 2, 3, 6)";
		$snd = $this->db->get_single_result($sql);
		return $snd['count'];
	}
	
	function render_LeftNav($user_id) {
		$urlElements = $this->parseURLString();
		//print_r($_SESSION['system_user_id']);
		$userPerms = $this->usr->get_userPermissions($user_id);
		
		?>
        <!-- BEGIN: Left Aside -->
        <button class="m-aside-left-close  m-aside-left-close--skin-dark " id="m_aside_left_close_btn">
            <i class="la la-close"></i>
        </button>
        <div id="m_aside_left" class="m-grid__item	m-aside-left  m-aside-left--skin-dark ">
            <!-- BEGIN: Aside Menu -->
            <div id="m_ver_menu" class="m-aside-menu  m-aside-menu--skin-dark m-aside-menu--submenu-skin-dark " data-menu-vertical="true" data-menu-scrollable="true" data-menu-dropdown-timeout="500">
                <ul class="m-menu__nav  m-menu__nav--dropdown-submenu-arrow ">
                    <li class="m-menu__item <?php echo ((($urlElements['page'] == '') || ($urlElements['page'] == 'home'))? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                        <a  href="/home" class="m-menu__link ">
                            <i class="m-menu__link-icon flaticon-line-graph"></i>
                            <span class="m-menu__link-title">
                                <span class="m-menu__link-wrap">
                                    <span class="m-menu__link-text">Dashboard</span>
                                </span>
                            </span>
                        </a>
                    </li>
                    <?php if(in_array(12, $userPerms)): ?>
                    <li class="m-menu__item <?php echo (($urlElements['page'] == 'search')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                        <a  href="/search" class="m-menu__link ">
                            <i class="m-menu__link-icon flaticon-search-1"></i>
                            <span class="m-menu__link-title">
                                <span class="m-menu__link-wrap">
                                    <span class="m-menu__link-text">Global Search</span>
                                </span>
                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php //if(in_array(87, $userPerms)): ?>
                    <li class="m-menu__item <?php echo (($urlElements['page'] == 'fullsearch')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                        <a  href="/fullsearch" class="m-menu__link ">
                            <i class="m-menu__link-icon flaticon-search-1"></i>
                            <span class="m-menu__link-title">
                                <span class="m-menu__link-wrap">
                                    <span class="m-menu__link-text">Master Search</span>
                                </span>
                            </span>
                        </a>
                    </li>
                    <?php //endif; ?>
                    <li class="m-menu__item <?php echo (($urlElements['page'] == 'mytasks')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                        <a  href="/mytasks" class="m-menu__link ">
                            <i class="m-menu__link-icon flaticon-attachment"></i>
                            <span class="m-menu__link-title">
                                <span class="m-menu__link-wrap">
                                    <span class="m-menu__link-text">My Tasks</span>
                                    <span class="m-menu__link-badge">
                                        <span class="m-badge m-badge--warning"><?php echo $this->get_MyTasksCount($_SESSION['system_user_id'])?></span>
                                    </span>
                                </span>
                            </span>
                        </a>
                    </li>
                    <?php if(in_array(13, $userPerms)): ?>
                    <li class="m-menu__item <?php echo (($urlElements['page'] == 'myleads')? 'm-menu__item--active':'')?>" aria-haspopup="true">
                        <a href="/myleads" class="m-menu__link ">
                            <i class="m-menu__link-icon flaticon-paper-plane"></i>
                            <span class="m-menu__link-title">
                                <span class="m-menu__link-wrap">
                                    <span class="m-menu__link-text">My Lead Sheet</span>
                                    <span class="m-menu__link-badge">
                                        <span class="m-badge m-badge--info"><?php echo $this->get_AssignedLeadsCount($_SESSION['system_user_id'])?></span>
                                    </span>
                                </span>
                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if(in_array(14, $userPerms)): ?>
                    <li class="m-menu__item <?php echo (($urlElements['page'] == 'myclients')? 'm-menu__item--active':'')?>" aria-haspopup="true">
                        <a href="/myclients" class="m-menu__link ">
                            <i class="m-menu__link-icon flaticon-user-ok"></i>
                            <span class="m-menu__link-title">
                                <span class="m-menu__link-wrap">
                                    <span class="m-menu__link-text">My Clients</span>
                                    <span class="m-menu__link-badge">
                                        <span class="m-badge m-badge--success"><?php echo $this->get_AssignedClientsCount($_SESSION['system_user_id'])?></span>
                                    </span>
                                </span>
                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if(in_array(15, $userPerms)): ?>
                    <li class="m-menu__item <?php echo (($urlElements['page'] == 'myintros')? 'm-menu__item--active':'')?>" aria-haspopup="true">
                        <a href="/myintros" class="m-menu__link ">
                            <i class="m-menu__link-icon la la-heart-o"></i>
                            <span class="m-menu__link-title">
                                <span class="m-menu__link-wrap">
                                    <span class="m-menu__link-text">My Intros</span>
                                    <span class="m-menu__link-badge">
                                        <span class="m-badge m-badge--brand"><?php echo $this->get_IntroRecordCount($_SESSION['system_user_id'])?></span>
                                    </span>
                                </span>
                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if(in_array(22, $userPerms)): ?>
                    <li class="m-menu__item <?php echo (($urlElements['page'] == 'mycalendar')? 'm-menu__item--active':'')?>" aria-haspopup="true">
                        <a href="/mycalendar" class="m-menu__link ">
                            <i class="m-menu__link-icon flaticon-time-3"></i>
                            <span class="m-menu__link-title">
                                <span class="m-menu__link-wrap">
                                    <span class="m-menu__link-text">My Calendar</span>
                                </span>
                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if(in_array(24, $userPerms)): ?>
                    <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('new','allleads','leadsassign','leadsources','leadactions','leadsconfig','leadtaskmgt','pendingpart'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                        <a  href="#" class="m-menu__link m-menu__toggle">
                            <i class="m-menu__link-icon flaticon-users"></i>
                            <span class="m-menu__link-text">Leads</span>
                            <i class="m-menu__ver-arrow la la-angle-right"></i>
                        </a>
                        <div class="m-menu__submenu">
                            <span class="m-menu__arrow"></span>
                            <ul class="m-menu__subnav">
                                <li class="m-menu__item  m-menu__item--parent" aria-haspopup="true" >
                                    <a  href="#" class="m-menu__link ">
                                        <span class="m-menu__link-text">Leads</span>
                                    </a>
                                </li>                                
                                <?php if(in_array(25, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'new')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/new" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">Enter New Lead</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(26, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'allleads')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/allleads" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">All Active Leads</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(60, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'pendingpart')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/pendingpart" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">Pending PM</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(27, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'leadsassign')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/leadsassign" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">Leads Assignment</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(16, $userPerms)): ?>
                                <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('leadsources','leadactions','leadsconfig','leadtaskmgt'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                                    <a  href="#" class="m-menu__link m-menu__toggle">
                                        <i class="m-menu__link-icon flaticon-settings"></i>
                                        <span class="m-menu__link-text">Leads Admin</span>
                                        <i class="m-menu__ver-arrow la la-angle-right"></i>
                                    </a>
                                    <div class="m-menu__submenu">
                                        <span class="m-menu__arrow"></span>
                                        <ul class="m-menu__subnav">
                                            <?php if(in_array(28, $userPerms)): ?>
                                            <li class="m-menu__item <?php echo (($urlElements['page'] == 'leadsources')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/leadsources" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Lead Source Mgt.</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <!--
                                            <li class="m-menu__item <?php echo (($urlElements['page'] == 'leadactions')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/leadactions" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Lead Actions Mgt.</span>
                                                </a>
                                            </li>
                                            -->
                                            <?php if(in_array(29, $userPerms)): ?>
                                            <li class="m-menu__item <?php echo (($urlElements['page'] == 'leadsconfig')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/leadsconfig" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Leads Config</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <?php if(in_array(30, $userPerms)): ?>
                                            <li class="m-menu__item <?php echo (($urlElements['page'] == 'leadtaskmgt')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/leadtaskmgt" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Lead Tasks</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>                                            
                                        </ul>
                                    </div>
                                </li>
                                <?php endif; ?>                                
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                    
                    <?php if(in_array(21, $userPerms)): ?>
                    <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('sales-admin'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                        <a  href="#" class="m-menu__link m-menu__toggle">
                            <i class="m-menu__link-icon flaticon-calendar-2"></i>
                            <span class="m-menu__link-text">Sales &amp; Shows</span>
                            <i class="m-menu__ver-arrow la la-angle-right"></i>
                        </a>
                        <div class="m-menu__submenu">
                            <span class="m-menu__arrow"></span>
                            <ul class="m-menu__subnav">
                                <!--
								<?php if(in_array(31, $userPerms)): ?>
                                <li class="m-menu__item " aria-haspopup="true" >
                                    <a  href="#" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">Enter New Sales</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                -->
                                <?php if(in_array(32, $userPerms)): ?>
                                <li class="m-menu__item " aria-haspopup="true" >
                                    <a  href="#" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">Appiontment Log</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(32, $userPerms)): ?>
                                <li class="m-menu__item " aria-haspopup="true" >
                                    <a  href="#" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">Sales Log</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(17, $userPerms)): ?>
                                <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('sales-admin'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                                    <a  href="#" class="m-menu__link m-menu__toggle">
                                        <i class="m-menu__link-icon flaticon-settings"></i>
                                        <span class="m-menu__link-text">Sales Admin</span>
                                        <i class="m-menu__ver-arrow la la-angle-right"></i>
                                    </a>
                                    <div class="m-menu__submenu">
                                        <span class="m-menu__arrow"></span>
                                        <ul class="m-menu__subnav">
                                            <?php if(in_array(33, $userPerms)): ?>
                                            <li class="m-menu__item <?php echo (($urlElements['page'] == 'sales-admin')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/sales-admin" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Package Mgt.</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                    
                    <?php if(in_array(42, $userPerms)): ?>
                    <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('activeclients','pendingclients','inactiveclients','frozenclients','activeresources','inactiveresources','activeparticipating','inactiveparticipating','freeclients','clienttaskmgt','trialmembership'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                        <a  href="#" class="m-menu__link m-menu__toggle">
                            <i class="m-menu__link-icon flaticon-profile-1"></i>
                            <span class="m-menu__link-text">Clients</span>
                            <i class="m-menu__ver-arrow la la-angle-right"></i>
                        </a>
                        <div class="m-menu__submenu">
                            <span class="m-menu__arrow"></span>
                            <ul class="m-menu__subnav">
                                <li class="m-menu__item  m-menu__item--parent" aria-haspopup="true" >
                                    <a href="#" class="m-menu__link">
                                        <span class="m-menu__link-text">Clients</span>                                        
                                    </a>
                                </li>
                                
                                <?php if(in_array(43, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'activeclients')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/activeclients" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-title">
                                            <span class="m-menu__link-wrap">
                                                <span class="m-menu__link-text">Active</span>
                                                <span class="m-menu__link-badge">
                                                    <span class="m-badge m-badge--primary"><?php echo $this->get_ClientRecordTypeCount(4)?></span>
                                                </span>
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(44, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'pendingclients')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/pendingclients" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-title">
                                            <span class="m-menu__link-wrap">
                                                <span class="m-menu__link-text">Pending</span>
                                                <span class="m-menu__link-badge">
                                                    <span class="m-badge m-badge--success"><?php echo $this->get_ClientRecordTypeCount(7)?></span>
                                                </span>
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(45, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'frozenclients')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="frozenclients" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-title">
                                            <span class="m-menu__link-wrap">
                                                <span class="m-menu__link-text">Frozen</span>
                                                <span class="m-menu__link-badge">
                                                    <span class="m-badge m-badge--danger"><?php echo $this->get_ClientRecordTypeCount(6)?></span>
                                                </span>
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                                               
                                <li class="m-menu__section">
                                    <h4 class="m-menu__section-text">Other</h4>
                                    <i class="m-menu__section-icon flaticon-more-v3"></i>
								</li>
                                <?php if(in_array(61, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'trialmembership')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/trialmembership" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-title">
                                            <span class="m-menu__link-wrap">
                                                <span class="m-menu__link-text">Trial Members</span>
                                                <span class="m-menu__link-badge">
                                                    <span class="m-badge m-badge--metal"><?php echo $this->get_ClientRecordTypeCount(14)?></span>
                                                </span>
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <?php endif; ?>
								<?php if(in_array(46, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'activeresources')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/activeresources" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-title">
                                            <span class="m-menu__link-wrap">
                                                <span class="m-menu__link-text">Resources</span>
                                                <span class="m-menu__link-badge">
                                                    <span class="m-badge m-badge--brand"><?php echo $this->get_ClientRecordTypeCount(10)?></span>
                                                </span>
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(47, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'activeparticipating')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/activeparticipating" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-title">
                                            <span class="m-menu__link-wrap">
                                                <span class="m-menu__link-text">Participating</span>
                                                <span class="m-menu__link-badge">
                                                    <span class="m-badge m-badge--warning"><?php echo $this->get_ClientRecordTypeCount(12)?></span>
                                                </span>
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(51, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'freeclients')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/freeclients" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-title">
                                            <span class="m-menu__link-wrap">
                                                <span class="m-menu__link-text">Free</span>
                                                <span class="m-menu__link-badge">
                                                    <span class="m-badge m-badge--metal"><?php echo $this->get_ClientRecordTypeCount(8)?></span>
                                                </span>
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                                              
                                <li class="m-menu__section">
                                    <h4 class="m-menu__section-text">Archive</h4>
                                    <i class="m-menu__section-icon flaticon-more-v3"></i>
								</li>
                                <?php if(in_array(49, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'inactiveclients')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/inactiveclients" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">Inactive Clients</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php if(in_array(50, $userPerms)): ?>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'inactiveresources')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/inactiveresources" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">Inactive Resources</span>
                                    </a>
                                </li>
                                <?php endif; ?> 
                                
                                
                                <?php if(in_array(23, $userPerms)): ?>
                                <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('clienttaskmgt'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                                    <a  href="#" class="m-menu__link m-menu__toggle">
                                        <i class="m-menu__link-icon flaticon-settings"></i>
                                        <span class="m-menu__link-text">Client Admin</span>
                                        <i class="m-menu__ver-arrow la la-angle-right"></i>
                                    </a>
                                    <div class="m-menu__submenu">
                                        <span class="m-menu__arrow"></span>
                                        <ul class="m-menu__subnav">
                                            <?php if(in_array(34, $userPerms)): ?>
                                            <li class="m-menu__item <?php echo (($urlElements['page'] == 'clienttaskmgt')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/clienttaskmgt" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Client Tasks</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>                                            
                                        </ul>
                                    </div>
                                </li>
                                <?php endif; ?>                          
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                    
                    <?php if(in_array(18, $userPerms)): ?>
                    <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('mkg-lists','mkg-list','mkg-templates','mkg-deployments','mkg-bounces','mkg-optouts','forms','leaddelivery','mkg-deployment'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                        <a  href="#" class="m-menu__link m-menu__toggle">
                            <i class="m-menu__link-icon flaticon-interface-7"></i>
                            <span class="m-menu__link-text">Marketing</span>
                            <i class="m-menu__ver-arrow la la-angle-right"></i>
                        </a>
                        <div class="m-menu__submenu">
                            <span class="m-menu__arrow"></span>
                            <ul class="m-menu__subnav">
                                <li class="m-menu__item  m-menu__item--parent" aria-haspopup="true" >
                                    <a  href="#" class="m-menu__link ">
                                        <span class="m-menu__link-text">Marketing</span>
                                    </a>
                                </li>
                                <?php if(in_array(35, $userPerms)): ?>
                                <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('mkg-lists','mkg-list','mkg-templates','mkg-deployments','mkg-bounces','mkg-optouts','mkg-deployment'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                                    <a  href="#" class="m-menu__link m-menu__toggle">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">eMarketing</span>
                                        <i class="m-menu__ver-arrow la la-angle-right"></i>
                                    </a>
                                    <div class="m-menu__submenu">
                                        <span class="m-menu__arrow"></span>
                                        <ul class="m-menu__subnav">
                                            <li class="m-menu__item <?php echo ((in_array($urlElements['page'], array('mkg-deployments','mkg-deployment')))? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/mkg-deployments" class="m-menu__link ">
                                                    <i class="m-menu__link-icon flaticon-interface-2"></i>
                                                    <span class="m-menu__link-text">Deployments</span>
                                                </a>
                                            </li>
											<li class="m-menu__item <?php echo (($urlElements['page'] == 'mkg-lists' || $urlElements['page'] == 'mkg-list')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/mkg-lists" class="m-menu__link ">
                                                    <i class="m-menu__link-icon flaticon-list-3"></i>
                                                    <span class="m-menu__link-text">Marketing Lists</span>
                                                </a>
                                            </li>
                                            <li class="m-menu__item <?php echo (($urlElements['page'] == 'mkg-optouts')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/mkg-optouts" class="m-menu__link ">
                                                    <i class="m-menu__link-icon la la-ban"></i>
                                                    <span class="m-menu__link-text">Opt-Outs</span>
                                                </a>
                                            </li>
                                            <li class="m-menu__item <?php echo (($urlElements['page'] == 'mkg-bounces')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/mkg-bounces" class="m-menu__link ">
                                                    <i class="m-menu__link-icon flaticon-danger"></i>
                                                    <span class="m-menu__link-text">Bounces/Blocks</span>
                                                </a>
                                            </li>
											<li class="m-menu__item <?php echo (($urlElements['page'] == 'mkg-templates')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a href="/mkg-templates" class="m-menu__link ">
                                                    <i class="m-menu__link-icon flaticon-interface-1"></i>
                                                    <span class="m-menu__link-text">eMail Templates</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(36, $userPerms)): ?>
                                <li class="m-menu__item  m-menu__item--submenu" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                                    <a  href="#" class="m-menu__link m-menu__toggle">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">
                                            Social media
                                        </span>
                                        <i class="m-menu__ver-arrow la la-angle-right"></i>
                                    </a>
                                    <div class="m-menu__submenu">
                                        <span class="m-menu__arrow"></span>
                                        <ul class="m-menu__subnav">
                                            <li class="m-menu__item " aria-haspopup="true" >
                                                <a  href="#" class="m-menu__link ">
                                                    <i class="m-menu__link-icon socicon-facebook"></i>
                                                    <span class="m-menu__link-text">Facebook</span>
                                                </a>
                                            </li>
                                            <li class="m-menu__item " aria-haspopup="true" >
                                                <a  href="#" class="m-menu__link ">
                                                    <i class="m-menu__link-icon socicon-twitter"></i>
                                                    <span class="m-menu__link-text">Twitter</span>
                                                </a>
                                            </li>
                                            <li class="m-menu__item " aria-haspopup="true" >
                                                <a  href="#" class="m-menu__link ">
                                                    <i class="m-menu__link-icon socicon-instagram"></i>
                                                    <span class="m-menu__link-text">Instagram</span>
                                                </a>
                                            </li>                                            
                                        </ul>
                                    </div>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(37, $userPerms)): ?> 
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'forms')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/page.php?path=forms" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">Forms Manager</span>
                                    </a>
                                </li>
                                <li class="m-menu__item <?php echo (($urlElements['page'] == 'leaddelivery')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                    <a  href="/leaddelivery" class="m-menu__link ">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">Lead Delivery</span>
                                    </a>
                                </li>
                                <?php endif; ?>                               
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                    <?php if(in_array(19, $userPerms)): ?>
                    <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('users','userclasses','api-connections','qmanager','pmanager','qmanage','pmanage'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                        <a  href="#" class="m-menu__link m-menu__toggle">
                            <i class="m-menu__link-icon flaticon-suitcase"></i>
                            <span class="m-menu__link-text">Management</span>
                            <i class="m-menu__ver-arrow la la-angle-right"></i>
                        </a>
                        <div class="m-menu__submenu">
                            <span class="m-menu__arrow"></span>
                            <ul class="m-menu__subnav">
                                <li class="m-menu__item  m-menu__item--parent" aria-haspopup="true" >
                                    <a  href="#" class="m-menu__link ">
                                        <span class="m-menu__link-text">Management</span>
                                    </a>
                                </li>
                                <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('users','userclasses'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                                    <a  href="#" class="m-menu__link m-menu__toggle">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">User Management</span>
                                        <i class="m-menu__ver-arrow la la-angle-right"></i>
                                    </a>
                                    <div class="m-menu__submenu">
                                        <span class="m-menu__arrow"></span>
                                        <ul class="m-menu__subnav">
                                            <?php if(in_array(38, $userPerms)): ?>
                                            <li class="m-menu__item <?php echo (($urlElements['page'] == 'users')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/users" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Users</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <?php if(in_array(39, $userPerms)): ?>
                                            <li class="m-menu__item <?php echo (($urlElements['page'] == 'userclasses')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a  href="/userclasses" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">User Classes</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>                                          
                                        </ul>
                                    </div>
                                </li>
                                <?php if(in_array(40, $userPerms)): ?>
                                <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('qmanager','pmanager','qmanage','pmanage'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                                    <a  href="#" class="m-menu__link m-menu__toggle">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">System Config</span>
                                        <i class="m-menu__ver-arrow la la-angle-right"></i>
                                    </a>
                                    <div class="m-menu__submenu">
                                        <span class="m-menu__arrow"></span>
                                        <ul class="m-menu__subnav">
                                            <?php if(in_array(52, $userPerms)): ?>
                                            <li class="m-menu__item <?php echo ((in_array($urlElements['page'], array('qmanager','qmanage'))? 'm-menu__item--active':''))?>" aria-haspopup="true" >
                                                <a  href="/qmanager" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Profile Manager</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <?php if(in_array(53, $userPerms)): ?>
                                            <li class="m-menu__item <?php echo ((in_array($urlElements['page'], array('pmanager','pmanage'))? 'm-menu__item--active':''))?>" aria-haspopup="true" >
                                                <a  href="/pmanager" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Pref Manager</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <?php if(in_array(66, $userPerms)): ?>
                                            <li class="m-menu__item <?php echo ((in_array($urlElements['page'], array('debrief-manager','debrief-qmanage'))? 'm-menu__item--active':''))?>" aria-haspopup="true" >
                                                <a  href="/debrief-manager" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Debrief Manager</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <li class="m-menu__item " aria-haspopup="true" >
                                                <a  href="#" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">KIMS Config</span>
                                                </a>
                                            </li>
                                            <li class="m-menu__item " aria-haspopup="true" >
                                                <a  href="#" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Tables Config</span>
                                                </a>
                                            </li>
                                            <li class="m-menu__item " aria-haspopup="true" >
                                                <a  href="#" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Intro Config</span>
                                                </a>
                                            </li>
                                            <li class="m-menu__item " aria-haspopup="true" >
                                                <a  href="#" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">Call/Note Config</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array(41, $userPerms)): ?>                                
                                <li class="m-menu__item  m-menu__item--submenu <?php echo ((in_array($urlElements['page'], array('api-connections'))? 'm-menu__item--open m-menu__item--expanded':''))?>" aria-haspopup="true"  data-menu-submenu-toggle="hover">
                                    <a  href="#" class="m-menu__link m-menu__toggle">
                                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                            <span></span>
                                        </i>
                                        <span class="m-menu__link-text">
                                            API
                                        </span>
                                        <i class="m-menu__ver-arrow la la-angle-right"></i>
                                    </a>
                                    <div class="m-menu__submenu">
                                        <span class="m-menu__arrow"></span>
                                        <ul class="m-menu__subnav">
                                            <li class="m-menu__item <?php echo (($urlElements['page'] == 'api-connections')? 'm-menu__item--active':'')?>" aria-haspopup="true" >
                                                <a href="/api-connections" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">API Connections</span>
                                                </a>
                                            </li>
                                            <li class="m-menu__item " aria-haspopup="true" >
                                                <a  href="#" class="m-menu__link ">
                                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                                        <span></span>
                                                    </i>
                                                    <span class="m-menu__link-text">API Call Log</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                    <li class="m-menu__item " aria-haspopup="true">
                        <a href="/files" class="m-menu__link ">
                            <i class="m-menu__link-icon flaticon-folder-1"></i>
                            <span class="m-menu__link-title">
                                <span class="m-menu__link-wrap">
                                    <span class="m-menu__link-text">File Browser</span>
                                </span>
                            </span>
                        </a>
                    </li>

                </ul>
                <?php //print_r($userPerms); ?>
            </div>
            <!-- END: Aside Menu -->
        </div>
        <!-- END: Left Aside -->
		<?php
	}
	

    // Scott - comment out code - passes through call
	function render_PageHeaderNotices() {
        /*
		$sql = "SELECT * FROM CompanySystemMessages WHERE Active='1' ORDER BY DateCreated DESC";
		$snd = $this->db->get_multi_result($sql);
		if($snd['empty_result'] != 1):
			foreach($snd as $news):
			?>
            <div class="m-alert m-alert--icon alert <?php echo $news['alertClass']?> rolling-alert" role="alert" style="margin-bottom:0rem;">
                <div class="m-alert__icon">
	                <i class="<?php echo $news['Icon']?>"></i>
                </div>
                <div class="m-alert__text" style="padding:0.45rem 0.25rem;">
       	        	<small><?php echo date("m/d/y h:ia", $news['DateCreated'])?></small><br />
					<?php echo $news['Message']?>
                </div>
                <div class="m-alert__close">
                	<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
                </div>
			</div>
            <?php
			endforeach;
		endif;
     */
	}
	
	function render_PageSubHeader($title, $icon=NULL, $subtitles=array(), $notice=false) {
		?>
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator"><?php echo $title?></h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item m-nav__item--home">
                            <a href="#" class="m-nav__link m-nav__link--icon">
                                <i class="m-nav__link-icon <?php echo $icon?>"></i>
                            </a>
                        </li>
                        <li class="m-nav__separator">
                            -
                        </li>
                        <?php foreach($subtitles as $sublink): ?>
                        <li class="m-nav__item">
                            <a href="<?php echo $sublink['link']?>" class="m-nav__link">
                                <span class="m-nav__link-text">
                                    <?php echo $sublink['text']?>
                                </span>
                            </a>
                        </li>
                        <li class="m-nav__separator">
                            -
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php if($notice): ?>
                	<div>
                	<?php echo $this->render_PageHeaderNotices()?>
                    </div>
                <?php endif; ?>
            </div>
        </div>   
        <?php	
	}
	
	function renderFooter() {
		?>
        <footer class="m-grid__item		m-footer ">
            <div class="m-container m-container--fluid m-container--full-height m-page__container">
                <div class="m-stack m-stack--flex-tablet-and-mobile m-stack--ver m-stack--desktop">
                    <div class="m-stack__item m-stack__item--left m-stack__item--middle m-stack__item--last">
                        <span class="m-footer__copyright"><?php echo date("Y")?> &copy; Kelleher International</span>
                    </div>
                    <div class="m-stack__item m-stack__item--right m-stack__item--middle m-stack__item--first">
                        <ul class="m-footer__nav m-nav m-nav--inline m--pull-right">
                            <li class="m-nav__item">
                                <a href="#" class="m-nav__link">
                                    <span class="m-nav__link-text"><i class="la la-envelope-o"></i> My Email</span>
                                </a>
                            </li>
                            <li class="m-nav__item">
                                <a href="/myclients"  class="m-nav__link">
                                    <span class="m-nav__link-text"><i class="la la-users"></i> My Assigned</span>
                                </a>
                            </li>
                            <li class="m-nav__item">
                                <a href="#" class="m-nav__link">
                                    <span class="m-nav__link-text"><i class="la la-heart-o"></i> My Intros</span>
                                </a>
                            </li>
                            <li class="m-nav__item m-nav__item">
                                <a href="#" class="m-nav__link" data-toggle="m-tooltip" title="Support Center" data-placement="left">
                                    <i class="m-nav__link-icon flaticon-info m--icon-font-size-lg3"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
        <?php		
	}
	
	function render_rightSidebar() {
		?>
        <div id="m_quick_sidebar" class="m-quick-sidebar m-quick-sidebar--tabbed m-quick-sidebar--skin-light">
            <div class="m-quick-sidebar__content m--hide">
                <span id="m_quick_sidebar_close" class="m-quick-sidebar__close">
                    <i class="la la-close"></i>
                </span>
                <ul id="m_quick_sidebar_tabs" class="nav nav-tabs m-tabs m-tabs-line m-tabs-line--brand" role="tablist">
                    <li class="nav-item m-tabs__item">
                        <a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_quick_sidebar_tabs_messenger" role="tab">
                            Messages
                        </a>
                    </li>
                    <li class="nav-item m-tabs__item">
                        <a class="nav-link m-tabs__link" 		data-toggle="tab" href="#m_quick_sidebar_tabs_settings" role="tab">
                            Settings
                        </a>
                    </li>
                    <li class="nav-item m-tabs__item">
                        <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_quick_sidebar_tabs_logs" role="tab">
                            Logs
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active m-scrollable" id="m_quick_sidebar_tabs_messenger" role="tabpanel">
                        <div class="m-messenger m-messenger--message-arrow m-messenger--skin-light">
                            <div class="m-messenger__messages">
                                <div class="m-messenger__message m-messenger__message--in">
                                    <div class="m-messenger__message-pic">
                                        <img src="assets/app/media/img//users/user3.jpg" alt=""/>
                                    </div>
                                    <div class="m-messenger__message-body">
                                        <div class="m-messenger__message-arrow"></div>
                                        <div class="m-messenger__message-content">
                                            <div class="m-messenger__message-username">
                                                Megan wrote
                                            </div>
                                            <div class="m-messenger__message-text">
                                                Hi Bob. What time will be the meeting ?
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-messenger__message m-messenger__message--out">
                                    <div class="m-messenger__message-body">
                                        <div class="m-messenger__message-arrow"></div>
                                        <div class="m-messenger__message-content">
                                            <div class="m-messenger__message-text">
                                                Hi Megan. It's at 2.30PM
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-messenger__message m-messenger__message--in">
                                    <div class="m-messenger__message-pic">
                                        <img src="assets/app/media/img//users/user3.jpg" alt=""/>
                                    </div>
                                    <div class="m-messenger__message-body">
                                        <div class="m-messenger__message-arrow"></div>
                                        <div class="m-messenger__message-content">
                                            <div class="m-messenger__message-username">
                                                Megan wrote
                                            </div>
                                            <div class="m-messenger__message-text">
                                                Will the development team be joining ?
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-messenger__message m-messenger__message--out">
                                    <div class="m-messenger__message-body">
                                        <div class="m-messenger__message-arrow"></div>
                                        <div class="m-messenger__message-content">
                                            <div class="m-messenger__message-text">
                                                Yes sure. I invited them as well
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-messenger__datetime">
                                    2:30PM
                                </div>
                                <div class="m-messenger__message m-messenger__message--in">
                                    <div class="m-messenger__message-pic">
                                        <img src="assets/app/media/img//users/user3.jpg"  alt=""/>
                                    </div>
                                    <div class="m-messenger__message-body">
                                        <div class="m-messenger__message-arrow"></div>
                                        <div class="m-messenger__message-content">
                                            <div class="m-messenger__message-username">
                                                Megan wrote
                                            </div>
                                            <div class="m-messenger__message-text">
                                                Noted. For the Coca-Cola Mobile App project as well ?
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-messenger__message m-messenger__message--out">
                                    <div class="m-messenger__message-body">
                                        <div class="m-messenger__message-arrow"></div>
                                        <div class="m-messenger__message-content">
                                            <div class="m-messenger__message-text">
                                                Yes, sure.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-messenger__message m-messenger__message--out">
                                    <div class="m-messenger__message-body">
                                        <div class="m-messenger__message-arrow"></div>
                                        <div class="m-messenger__message-content">
                                            <div class="m-messenger__message-text">
                                                Please also prepare the quotation for the Loop CRM project as well.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-messenger__datetime">
                                    3:15PM
                                </div>
                                <div class="m-messenger__message m-messenger__message--in">
                                    <div class="m-messenger__message-no-pic m--bg-fill-danger">
                                        <span>
                                            M
                                        </span>
                                    </div>
                                    <div class="m-messenger__message-body">
                                        <div class="m-messenger__message-arrow"></div>
                                        <div class="m-messenger__message-content">
                                            <div class="m-messenger__message-username">
                                                Megan wrote
                                            </div>
                                            <div class="m-messenger__message-text">
                                                Noted. I will prepare it.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-messenger__message m-messenger__message--out">
                                    <div class="m-messenger__message-body">
                                        <div class="m-messenger__message-arrow"></div>
                                        <div class="m-messenger__message-content">
                                            <div class="m-messenger__message-text">
                                                Thanks Megan. I will see you later.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-messenger__message m-messenger__message--in">
                                    <div class="m-messenger__message-pic">
                                        <img src="assets/app/media/img//users/user3.jpg"  alt=""/>
                                    </div>
                                    <div class="m-messenger__message-body">
                                        <div class="m-messenger__message-arrow"></div>
                                        <div class="m-messenger__message-content">
                                            <div class="m-messenger__message-username">
                                                Megan wrote
                                            </div>
                                            <div class="m-messenger__message-text">
                                                Sure. See you in the meeting soon.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="m-messenger__seperator"></div>
                            <div class="m-messenger__form">
                                <div class="m-messenger__form-controls">
                                    <input type="text" name="" placeholder="Type here..." class="m-messenger__form-input">
                                </div>
                                <div class="m-messenger__form-tools">
                                    <a href="" class="m-messenger__form-attachment">
                                        <i class="la la-paperclip"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane  m-scrollable" id="m_quick_sidebar_tabs_settings" role="tabpanel">
                        <div class="m-list-settings">
                            <div class="m-list-settings__group">
                                <div class="m-list-settings__heading">
                                    General Settings
                                </div>
                                <div class="m-list-settings__item">
                                    <span class="m-list-settings__item-label">
                                        Email Notifications
                                    </span>
                                    <span class="m-list-settings__item-control">
                                        <span class="m-switch m-switch--outline m-switch--icon-check m-switch--brand">
                                            <label>
                                                <input type="checkbox" checked="checked" name="">
                                                <span></span>
                                            </label>
                                        </span>
                                    </span>
                                </div>
                                <div class="m-list-settings__item">
                                    <span class="m-list-settings__item-label">
                                        Site Tracking
                                    </span>
                                    <span class="m-list-settings__item-control">
                                        <span class="m-switch m-switch--outline m-switch--icon-check m-switch--brand">
                                            <label>
                                                <input type="checkbox" name="">
                                                <span></span>
                                            </label>
                                        </span>
                                    </span>
                                </div>
                                <div class="m-list-settings__item">
                                    <span class="m-list-settings__item-label">
                                        SMS Alerts
                                    </span>
                                    <span class="m-list-settings__item-control">
                                        <span class="m-switch m-switch--outline m-switch--icon-check m-switch--brand">
                                            <label>
                                                <input type="checkbox" name="">
                                                <span></span>
                                            </label>
                                        </span>
                                    </span>
                                </div>
                                <div class="m-list-settings__item">
                                    <span class="m-list-settings__item-label">
                                        Backup Storage
                                    </span>
                                    <span class="m-list-settings__item-control">
                                        <span class="m-switch m-switch--outline m-switch--icon-check m-switch--brand">
                                            <label>
                                                <input type="checkbox" name="">
                                                <span></span>
                                            </label>
                                        </span>
                                    </span>
                                </div>
                                <div class="m-list-settings__item">
                                    <span class="m-list-settings__item-label">
                                        Audit Logs
                                    </span>
                                    <span class="m-list-settings__item-control">
                                        <span class="m-switch m-switch--outline m-switch--icon-check m-switch--brand">
                                            <label>
                                                <input type="checkbox" checked="checked" name="">
                                                <span></span>
                                            </label>
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <div class="m-list-settings__group">
                                <div class="m-list-settings__heading">
                                    System Settings
                                </div>
                                <div class="m-list-settings__item">
                                    <span class="m-list-settings__item-label">
                                        System Logs
                                    </span>
                                    <span class="m-list-settings__item-control">
                                        <span class="m-switch m-switch--outline m-switch--icon-check m-switch--brand">
                                            <label>
                                                <input type="checkbox" name="">
                                                <span></span>
                                            </label>
                                        </span>
                                    </span>
                                </div>
                                <div class="m-list-settings__item">
                                    <span class="m-list-settings__item-label">
                                        Error Reporting
                                    </span>
                                    <span class="m-list-settings__item-control">
                                        <span class="m-switch m-switch--outline m-switch--icon-check m-switch--brand">
                                            <label>
                                                <input type="checkbox" name="">
                                                <span></span>
                                            </label>
                                        </span>
                                    </span>
                                </div>
                                <div class="m-list-settings__item">
                                    <span class="m-list-settings__item-label">
                                        Applications Logs
                                    </span>
                                    <span class="m-list-settings__item-control">
                                        <span class="m-switch m-switch--outline m-switch--icon-check m-switch--brand">
                                            <label>
                                                <input type="checkbox" name="">
                                                <span></span>
                                            </label>
                                        </span>
                                    </span>
                                </div>
                                <div class="m-list-settings__item">
                                    <span class="m-list-settings__item-label">
                                        Backup Servers
                                    </span>
                                    <span class="m-list-settings__item-control">
                                        <span class="m-switch m-switch--outline m-switch--icon-check m-switch--brand">
                                            <label>
                                                <input type="checkbox" checked="checked" name="">
                                                <span></span>
                                            </label>
                                        </span>
                                    </span>
                                </div>
                                <div class="m-list-settings__item">
                                    <span class="m-list-settings__item-label">
                                        Audit Logs
                                    </span>
                                    <span class="m-list-settings__item-control">
                                        <span class="m-switch m-switch--outline m-switch--icon-check m-switch--brand">
                                            <label>
                                                <input type="checkbox" name="">
                                                <span></span>
                                            </label>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane  m-scrollable" id="m_quick_sidebar_tabs_logs" role="tabpanel">
                        <div class="m-list-timeline">
                            <div class="m-list-timeline__group">
                                <div class="m-list-timeline__heading">
                                    System Logs
                                </div>
                                <div class="m-list-timeline__items">
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-success"></span>
                                        <a href="" class="m-list-timeline__text">
                                            12 new users registered
                                            <span class="m-badge m-badge--warning m-badge--wide">
                                                important
                                            </span>
                                        </a>
                                        <span class="m-list-timeline__time">
                                            Just now
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-info"></span>
                                        <a href="" class="m-list-timeline__text">
                                            System shutdown
                                        </a>
                                        <span class="m-list-timeline__time">
                                            11 mins
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-danger"></span>
                                        <a href="" class="m-list-timeline__text">
                                            New invoice received
                                        </a>
                                        <span class="m-list-timeline__time">
                                            20 mins
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-warning"></span>
                                        <a href="" class="m-list-timeline__text">
                                            Database overloaded 89%
                                            <span class="m-badge m-badge--success m-badge--wide">
                                                resolved
                                            </span>
                                        </a>
                                        <span class="m-list-timeline__time">
                                            1 hr
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-success"></span>
                                        <a href="" class="m-list-timeline__text">
                                            System error
                                        </a>
                                        <span class="m-list-timeline__time">
                                            2 hrs
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-info"></span>
                                        <a href="" class="m-list-timeline__text">
                                            Production server down
                                            <span class="m-badge m-badge--danger m-badge--wide">
                                                pending
                                            </span>
                                        </a>
                                        <span class="m-list-timeline__time">
                                            3 hrs
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-success"></span>
                                        <a href="" class="m-list-timeline__text">
                                            Production server up
                                        </a>
                                        <span class="m-list-timeline__time">
                                            5 hrs
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="m-list-timeline__group">
                                <div class="m-list-timeline__heading">
                                    Applications Logs
                                </div>
                                <div class="m-list-timeline__items">
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-info"></span>
                                        <a href="" class="m-list-timeline__text">
                                            New order received
                                            <span class="m-badge m-badge--info m-badge--wide">
                                                urgent
                                            </span>
                                        </a>
                                        <span class="m-list-timeline__time">
                                            7 hrs
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-success"></span>
                                        <a href="" class="m-list-timeline__text">
                                            12 new users registered
                                        </a>
                                        <span class="m-list-timeline__time">
                                            Just now
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-info"></span>
                                        <a href="" class="m-list-timeline__text">
                                            System shutdown
                                        </a>
                                        <span class="m-list-timeline__time">
                                            11 mins
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-danger"></span>
                                        <a href="" class="m-list-timeline__text">
                                            New invoices received
                                        </a>
                                        <span class="m-list-timeline__time">
                                            20 mins
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-warning"></span>
                                        <a href="" class="m-list-timeline__text">
                                            Database overloaded 89%
                                        </a>
                                        <span class="m-list-timeline__time">
                                            1 hr
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-success"></span>
                                        <a href="" class="m-list-timeline__text">
                                            System error
                                            <span class="m-badge m-badge--info m-badge--wide">
                                                pending
                                            </span>
                                        </a>
                                        <span class="m-list-timeline__time">
                                            2 hrs
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-info"></span>
                                        <a href="" class="m-list-timeline__text">
                                            Production server down
                                        </a>
                                        <span class="m-list-timeline__time">
                                            3 hrs
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="m-list-timeline__group">
                                <div class="m-list-timeline__heading">
                                    Server Logs
                                </div>
                                <div class="m-list-timeline__items">
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-success"></span>
                                        <a href="" class="m-list-timeline__text">
                                            Production server up
                                        </a>
                                        <span class="m-list-timeline__time">
                                            5 hrs
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-info"></span>
                                        <a href="" class="m-list-timeline__text">
                                            New order received
                                        </a>
                                        <span class="m-list-timeline__time">
                                            7 hrs
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-success"></span>
                                        <a href="" class="m-list-timeline__text">
                                            12 new users registered
                                        </a>
                                        <span class="m-list-timeline__time">
                                            Just now
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-info"></span>
                                        <a href="" class="m-list-timeline__text">
                                            System shutdown
                                        </a>
                                        <span class="m-list-timeline__time">
                                            11 mins
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-danger"></span>
                                        <a href="" class="m-list-timeline__text">
                                            New invoice received
                                        </a>
                                        <span class="m-list-timeline__time">
                                            20 mins
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-warning"></span>
                                        <a href="" class="m-list-timeline__text">
                                            Database overloaded 89%
                                        </a>
                                        <span class="m-list-timeline__time">
                                            1 hr
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-success"></span>
                                        <a href="" class="m-list-timeline__text">
                                            System error
                                        </a>
                                        <span class="m-list-timeline__time">
                                            2 hrs
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-info"></span>
                                        <a href="" class="m-list-timeline__text">
                                            Production server down
                                        </a>
                                        <span class="m-list-timeline__time">
                                            3 hrs
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-success"></span>
                                        <a href="" class="m-list-timeline__text">
                                            Production server up
                                        </a>
                                        <span class="m-list-timeline__time">
                                            5 hrs
                                        </span>
                                    </div>
                                    <div class="m-list-timeline__item">
                                        <span class="m-list-timeline__badge m-list-timeline__badge--state-info"></span>
                                        <a href="" class="m-list-timeline__text">
                                            New order received
                                        </a>
                                        <span class="m-list-timeline__time">
                                            1117 hrs
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
	}
	
	function render_quickNav() {
		?>
        <ul class="m-nav-sticky" style="margin-top: 30px;">
            <li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Showcase" data-placement="left">
                <a href="">
                    <i class="la la-eye"></i>
                </a>
            </li>
            <li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Pre-sale Chat" data-placement="left">
                <a href="" >
                    <i class="la la-comments-o"></i>
                </a>
            </li>
            <li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Purchase" data-placement="left">
                <a href="https://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes" target="_blank">
                    <i class="la la-cart-arrow-down"></i>
                </a>
            </li>
            <li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Documentation" data-placement="left">
                <a href="http://keenthemes.com/metronic/documentation.html" target="_blank">
                    <i class="la la-code-fork"></i>
                </a>
            </li>
            <li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Support" data-placement="left">
                <a href="http://keenthemes.com/forums/forum/support/metronic5/" target="_blank">
                    <i class="la la-life-ring"></i>
                </a>
            </li>
        </ul>        
        <?php	
	}
	
	function render_sampleDashboard() {
		?>
        <div class="row">
            <div class="col-xl-4">
                <!--begin:: Widgets/Top Products-->
                <div class="m-portlet m-portlet--bordered-semi m-portlet--full-height ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Trends
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
                                    <a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand">
                                        All
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: auto; right: 36.5px;"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav">
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                <span class="m-nav__link-text">
                                                                    Activity
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                <span class="m-nav__link-text">
                                                                    Messages
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                <span class="m-nav__link-text">
                                                                    FAQ
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                <span class="m-nav__link-text">
                                                                    Support
                                                                </span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <!--begin::Widget5-->
                        <div class="m-widget4">
                            <div class="m-widget4__chart m-portlet-fit--sides m--margin-top-10 m--margin-top-20" style="height:260px;">
                                <canvas id="m_chart_trends_stats"></canvas>
                            </div>
                            <div class="m-widget4__item">
                                <div class="m-widget4__img m-widget4__img--logo">
                                    <img src="assets/app/media/img/client-logos/logo3.png" alt="">
                                </div>
                                <div class="m-widget4__info">
                                    <span class="m-widget4__title">
                                        Phyton
                                    </span>
                                    <br>
                                    <span class="m-widget4__sub">
                                        A Programming Language
                                    </span>
                                </div>
                                <span class="m-widget4__ext">
                                    <span class="m-widget4__number m--font-danger">
                                        +$17
                                    </span>
                                </span>
                            </div>
                            <div class="m-widget4__item">
                                <div class="m-widget4__img m-widget4__img--logo">
                                    <img src="assets/app/media/img/client-logos/logo1.png" alt="">
                                </div>
                                <div class="m-widget4__info">
                                    <span class="m-widget4__title">
                                        FlyThemes
                                    </span>
                                    <br>
                                    <span class="m-widget4__sub">
                                        A Let's Fly Fast Again Language
                                    </span>
                                </div>
                                <span class="m-widget4__ext">
                                    <span class="m-widget4__number m--font-danger">
                                        +$300
                                    </span>
                                </span>
                            </div>
                            <div class="m-widget4__item">
                                <div class="m-widget4__img m-widget4__img--logo">
                                    <img src="assets/app/media/img/client-logos/logo2.png" alt="">
                                </div>
                                <div class="m-widget4__info">
                                    <span class="m-widget4__title">
                                        AirApp
                                    </span>
                                    <br>
                                    <span class="m-widget4__sub">
                                        Awesome App For Project Management
                                    </span>
                                </div>
                                <span class="m-widget4__ext">
                                    <span class="m-widget4__number m--font-danger">
                                        +$6700
                                    </span>
                                </span>
                            </div>
                        </div>
                        <!--end::Widget 5-->
                    </div>
                </div>
                <!--end:: Widgets/Top Products-->
            </div>
            <div class="col-xl-4">
                <!--begin:: Widgets/Activity-->
                <div class="m-portlet m-portlet--bordered-semi m-portlet--widget-fit m-portlet--full-height m-portlet--skin-light ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text m--font-light">
                                    Activity
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover">
                                    <a href="#" class="m-portlet__nav-link m-portlet__nav-link--icon m-portlet__nav-link--icon-xl">
                                        <i class="fa fa-genderless m--font-light"></i>
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav">
                                                        <li class="m-nav__section m-nav__section--first">
                                                            <span class="m-nav__section-text">
                                                                Quick Actions
                                                            </span>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                <span class="m-nav__link-text">
                                                                    Activity
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                <span class="m-nav__link-text">
                                                                    Messages
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                <span class="m-nav__link-text">
                                                                    FAQ
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                <span class="m-nav__link-text">
                                                                    Support
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__separator m-nav__separator--fit"></li>
                                                        <li class="m-nav__item">
                                                            <a href="#" class="btn btn-outline-danger m-btn m-btn--pill m-btn--wide btn-sm">
                                                                Cancel
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <div class="m-widget17">
                            <div class="m-widget17__visual m-widget17__visual--chart m-portlet-fit--top m-portlet-fit--sides m--bg-danger">
                                <div class="m-widget17__chart" style="height:320px;">
                                    <canvas id="m_chart_activities"></canvas>
                                </div>
                            </div>
                            <div class="m-widget17__stats">
                                <div class="m-widget17__items m-widget17__items-col1">
                                    <div class="m-widget17__item">
                                        <span class="m-widget17__icon">
                                            <i class="flaticon-truck m--font-brand"></i>
                                        </span>
                                        <span class="m-widget17__subtitle">
                                            Delivered
                                        </span>
                                        <span class="m-widget17__desc">
                                            15 New Paskages
                                        </span>
                                    </div>
                                    <div class="m-widget17__item">
                                        <span class="m-widget17__icon">
                                            <i class="flaticon-paper-plane m--font-info"></i>
                                        </span>
                                        <span class="m-widget17__subtitle">
                                            Reporeted
                                        </span>
                                        <span class="m-widget17__desc">
                                            72 Support Cases
                                        </span>
                                    </div>
                                </div>
                                <div class="m-widget17__items m-widget17__items-col2">
                                    <div class="m-widget17__item">
                                        <span class="m-widget17__icon">
                                            <i class="flaticon-pie-chart m--font-success"></i>
                                        </span>
                                        <span class="m-widget17__subtitle">
                                            Ordered
                                        </span>
                                        <span class="m-widget17__desc">
                                            72 New Items
                                        </span>
                                    </div>
                                    <div class="m-widget17__item">
                                        <span class="m-widget17__icon">
                                            <i class="flaticon-time m--font-danger"></i>
                                        </span>
                                        <span class="m-widget17__subtitle">
                                            Arrived
                                        </span>
                                        <span class="m-widget17__desc">
                                            34 Upgraded Boxes
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end:: Widgets/Activity-->
            </div>
            <div class="col-xl-4">
                <!--begin:: Widgets/Blog-->
                <div class="m-portlet m-portlet--bordered-semi m-portlet--full-height ">
                    <div class="m-portlet__head m-portlet__head--fit">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-action">
                                <button type="button" class="btn btn-sm m-btn--pill  btn-brand">
                                    Blog
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <div class="m-widget19">
                            <div class="m-widget19__pic m-portlet-fit--top m-portlet-fit--sides" style="min-height-: 286px">
                                <img src="assets/app/media/img//blog/blog1.jpg" alt="">
                                <h3 class="m-widget19__title m--font-light">
                                    Introducing New Feature
                                </h3>
                                <div class="m-widget19__shadow"></div>
                            </div>
                            <div class="m-widget19__content">
                                <div class="m-widget19__header">
                                    <div class="m-widget19__user-img">
                                        <img class="m-widget19__img" src="assets/app/media/img//users/user1.jpg" alt="">
                                    </div>
                                    <div class="m-widget19__info">
                                        <span class="m-widget19__username">
                                            Anna Krox
                                        </span>
                                        <br>
                                        <span class="m-widget19__time">
                                            UX/UI Designer, Google
                                        </span>
                                    </div>
                                    <div class="m-widget19__stats">
                                        <span class="m-widget19__number m--font-brand">
                                            18
                                        </span>
                                        <span class="m-widget19__comment">
                                            Comments
                                        </span>
                                    </div>
                                </div>
                                <div class="m-widget19__body">
                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry scrambled it to make text of the printing and typesetting industry scrambled a type specimen book text of the dummy text of the printing printing and typesetting industry scrambled dummy text of the printing.
                                </div>
                            </div>
                            <div class="m-widget19__action">
                                <button type="button" class="btn m-btn--pill btn-secondary m-btn m-btn--hover-brand m-btn--custom">
                                    Read More
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end:: Widgets/Blog-->
            </div>
        </div>
                
        <div class="m-portlet">
            <div class="m-portlet__body  m-portlet__body--no-padding">
                <div class="row m-row--no-padding m-row--col-separator-xl">
                    <div class="col-xl-4">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="m-widget1">
                            <div class="m-widget1__item">
                                <div class="row m-row--no-padding align-items-center">
                                    <div class="col">
                                        <h3 class="m-widget1__title">
                                            Member Profit
                                        </h3>
                                        <span class="m-widget1__desc">
                                            Awerage Weekly Profit
                                        </span>
                                    </div>
                                    <div class="col m--align-right">
                                        <span class="m-widget1__number m--font-brand">
                                            +$17,800
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="m-widget1__item">
                                <div class="row m-row--no-padding align-items-center">
                                    <div class="col">
                                        <h3 class="m-widget1__title">
                                            Orders
                                        </h3>
                                        <span class="m-widget1__desc">
                                            Weekly Customer Orders
                                        </span>
                                    </div>
                                    <div class="col m--align-right">
                                        <span class="m-widget1__number m--font-danger">
                                            +1,800
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="m-widget1__item">
                                <div class="row m-row--no-padding align-items-center">
                                    <div class="col">
                                        <h3 class="m-widget1__title">
                                            Issue Reports
                                        </h3>
                                        <span class="m-widget1__desc">
                                            System bugs and issues
                                        </span>
                                    </div>
                                    <div class="col m--align-right">
                                        <span class="m-widget1__number m--font-success">
                                            -27,49%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                    <div class="col-xl-4">
                        <!--begin:: Widgets/Daily Sales-->
                        <div class="m-widget14">
                            <div class="m-widget14__header m--margin-bottom-30">
                                <h3 class="m-widget14__title">
                                    Daily Sales
                                </h3>
                                <span class="m-widget14__desc">
                                    Check out each collumn for more details
                                </span>
                            </div>
                            <div class="m-widget14__chart" style="height:120px;">
                                <canvas  id="m_chart_daily_sales"></canvas>
                            </div>
                        </div>
                        <!--end:: Widgets/Daily Sales-->
                    </div>
                    <div class="col-xl-4">
                        <!--begin:: Widgets/Profit Share-->
                        <div class="m-widget14">
                            <div class="m-widget14__header">
                                <h3 class="m-widget14__title">
                                    Profit Share
                                </h3>
                                <span class="m-widget14__desc">
                                    Profit Share between customers
                                </span>
                            </div>
                            <div class="row  align-items-center">
                                <div class="col">
                                    <div id="m_chart_profit_share" class="m-widget14__chart" style="height: 160px">
                                        <div class="m-widget14__stat">
                                            45
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="m-widget14__legends">
                                        <div class="m-widget14__legend">
                                            <span class="m-widget14__legend-bullet m--bg-accent"></span>
                                            <span class="m-widget14__legend-text">
                                                37% Sport Tickets
                                            </span>
                                        </div>
                                        <div class="m-widget14__legend">
                                            <span class="m-widget14__legend-bullet m--bg-warning"></span>
                                            <span class="m-widget14__legend-text">
                                                47% Business Events
                                            </span>
                                        </div>
                                        <div class="m-widget14__legend">
                                            <span class="m-widget14__legend-bullet m--bg-brand"></span>
                                            <span class="m-widget14__legend-text">
                                                19% Others
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end:: Widgets/Profit Share-->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-12">
                <!--begin::Portlet-->
                <div class="m-portlet" id="m_portlet">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <span class="m-portlet__head-icon">
                                    <i class="flaticon-map-location"></i>
                                </span>
                                <h3 class="m-portlet__head-text">
                                    Calendar
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item">
                                    <a href="#" class="btn btn-accent m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air">
                                        <span>
                                            <i class="la la-plus"></i>
                                            <span>
                                                Add Event
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <div id="m_calendar"></div>
                    </div>
                </div>
                <!--end::Portlet-->
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-6">
                <!--begin:: Widgets/Tasks -->
                <div class="m-portlet m-portlet--full-height ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Tasks
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="nav nav-pills nav-pills--brand m-nav-pills--align-right m-nav-pills--btn-pill m-nav-pills--btn-sm" role="tablist">
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_widget2_tab1_content" role="tab">
                                        Today
                                    </a>
                                </li>
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget2_tab2_content1" role="tab">
                                        Week
                                    </a>
                                </li>
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget2_tab3_content1" role="tab">
                                        Month
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="m_widget2_tab1_content">
                                <div class="m-widget2">
                                    <div class="m-widget2__item m-widget2__item--primary">
                                        <div class="m-widget2__checkbox">
                                            <label class="m-checkbox m-checkbox--solid m-checkbox--single m-checkbox--brand">
                                                <input type="checkbox">
                                                <span></span>
                                            </label>
                                        </div>
                                        <div class="m-widget2__desc">
                                            <span class="m-widget2__text">
                                                Make Metronic Great  Again.Lorem Ipsum Amet
                                            </span>
                                            <br>
                                            <span class="m-widget2__user-name">
                                                <a href="#" class="m-widget2__link">
                                                    By Bob
                                                </a>
                                            </span>
                                        </div>
                                        <div class="m-widget2__actions">
                                            <div class="m-widget2__actions-nav">
                                                <div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover">
                                                    <a href="#" class="m-dropdown__toggle">
                                                        <i class="la la-ellipsis-h"></i>
                                                    </a>
                                                    <div class="m-dropdown__wrapper">
                                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                                        <div class="m-dropdown__inner">
                                                            <div class="m-dropdown__body">
                                                                <div class="m-dropdown__content">
                                                                    <ul class="m-nav">
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Activity
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Messages
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    FAQ
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Support
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-widget2__item m-widget2__item--warning">
                                        <div class="m-widget2__checkbox">
                                            <label class="m-checkbox m-checkbox--solid m-checkbox--single m-checkbox--brand">
                                                <input type="checkbox">
                                                <span></span>
                                            </label>
                                        </div>
                                        <div class="m-widget2__desc">
                                            <span class="m-widget2__text">
                                                Prepare Docs For Metting On Monday
                                            </span>
                                            <br>
                                            <span class="m-widget2__user-name">
                                                <a href="#" class="m-widget2__link">
                                                    By Sean
                                                </a>
                                            </span>
                                        </div>
                                        <div class="m-widget2__actions">
                                            <div class="m-widget2__actions-nav">
                                                <div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover">
                                                    <a href="#" class="m-dropdown__toggle">
                                                        <i class="la la-ellipsis-h"></i>
                                                    </a>
                                                    <div class="m-dropdown__wrapper">
                                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                                        <div class="m-dropdown__inner">
                                                            <div class="m-dropdown__body">
                                                                <div class="m-dropdown__content">
                                                                    <ul class="m-nav">
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Activity
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Messages
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    FAQ
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Support
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-widget2__item m-widget2__item--brand">
                                        <div class="m-widget2__checkbox">
                                            <label class="m-checkbox m-checkbox--solid m-checkbox--single m-checkbox--brand">
                                                <input type="checkbox">
                                                <span></span>
                                            </label>
                                        </div>
                                        <div class="m-widget2__desc">
                                            <span class="m-widget2__text">
                                                Make Widgets Great Again.Estudiat Communy Elit
                                            </span>
                                            <br>
                                            <span class="m-widget2__user-name">
                                                <a href="#" class="m-widget2__link">
                                                    By Aziko
                                                </a>
                                            </span>
                                        </div>
                                        <div class="m-widget2__actions">
                                            <div class="m-widget2__actions-nav">
                                                <div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover">
                                                    <a href="#" class="m-dropdown__toggle">
                                                        <i class="la la-ellipsis-h"></i>
                                                    </a>
                                                    <div class="m-dropdown__wrapper">
                                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                                        <div class="m-dropdown__inner">
                                                            <div class="m-dropdown__body">
                                                                <div class="m-dropdown__content">
                                                                    <ul class="m-nav">
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Activity
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Messages
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    FAQ
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Support
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-widget2__item m-widget2__item--success">
                                        <div class="m-widget2__checkbox">
                                            <label class="m-checkbox m-checkbox--solid m-checkbox--single m-checkbox--brand">
                                                <input type="checkbox">
                                                <span></span>
                                            </label>
                                        </div>
                                        <div class="m-widget2__desc">
                                            <span class="m-widget2__text">
                                                Make Metronic Great Again.Lorem Ipsum
                                            </span>
                                            <br>
                                            <span class="m-widget2__user-name">
                                                <a href="#" class="m-widget2__link">
                                                    By James
                                                </a>
                                            </span>
                                        </div>
                                        <div class="m-widget2__actions">
                                            <div class="m-widget2__actions-nav">
                                                <div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover">
                                                    <a href="#" class="m-dropdown__toggle">
                                                        <i class="la la-ellipsis-h"></i>
                                                    </a>
                                                    <div class="m-dropdown__wrapper">
                                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                                        <div class="m-dropdown__inner">
                                                            <div class="m-dropdown__body">
                                                                <div class="m-dropdown__content">
                                                                    <ul class="m-nav">
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Activity
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Messages
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    FAQ
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Support
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-widget2__item m-widget2__item--danger">
                                        <div class="m-widget2__checkbox">
                                            <label class="m-checkbox m-checkbox--solid m-checkbox--single m-checkbox--brand">
                                                <input type="checkbox">
                                                <span></span>
                                            </label>
                                        </div>
                                        <div class="m-widget2__desc">
                                            <span class="m-widget2__text">
                                                Completa Financial Report For Emirates Airlines
                                            </span>
                                            <br>
                                            <span class="m-widget2__user-name">
                                                <a href="#" class="m-widget2__link">
                                                    By Bob
                                                </a>
                                            </span>
                                        </div>
                                        <div class="m-widget2__actions">
                                            <div class="m-widget2__actions-nav">
                                                <div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover">
                                                    <a href="#" class="m-dropdown__toggle">
                                                        <i class="la la-ellipsis-h"></i>
                                                    </a>
                                                    <div class="m-dropdown__wrapper">
                                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                                        <div class="m-dropdown__inner">
                                                            <div class="m-dropdown__body">
                                                                <div class="m-dropdown__content">
                                                                    <ul class="m-nav">
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Activity
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Messages
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    FAQ
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Support
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-widget2__item m-widget2__item--info">
                                        <div class="m-widget2__checkbox">
                                            <label class="m-checkbox m-checkbox--solid m-checkbox--single m-checkbox--brand">
                                                <input type="checkbox">
                                                <span></span>
                                            </label>
                                        </div>
                                        <div class="m-widget2__desc">
                                            <span class="m-widget2__text">
                                                Completa Financial Report For Emirates Airlines
                                            </span>
                                            <br>
                                            <span class="m-widget2__user-name">
                                                <a href="#" class="m-widget2__link">
                                                    By Sean
                                                </a>
                                            </span>
                                        </div>
                                        <div class="m-widget2__actions">
                                            <div class="m-widget2__actions-nav">
                                                <div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover">
                                                    <a href="#" class="m-dropdown__toggle">
                                                        <i class="la la-ellipsis-h"></i>
                                                    </a>
                                                    <div class="m-dropdown__wrapper">
                                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                                        <div class="m-dropdown__inner">
                                                            <div class="m-dropdown__body">
                                                                <div class="m-dropdown__content">
                                                                    <ul class="m-nav">
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Activity
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Messages
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    FAQ
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Support
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="m_widget2_tab2_content"></div>
                            <div class="tab-pane" id="m_widget2_tab3_content"></div>
                        </div>
                    </div>
                </div>
                <!--end:: Widgets/Tasks -->
            </div>
            <div class="col-xl-6">
                <!--begin:: Widgets/Support Tickets -->
                <div class="m-portlet m-portlet--full-height ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Support Tickets
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
                                    <a href="#" class="m-portlet__nav-link m-portlet__nav-link--icon m-portlet__nav-link--icon-xl m-dropdown__toggle">
                                        <i class="la la-ellipsis-h m--font-brand"></i>
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav">
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                <span class="m-nav__link-text">
                                                                    Activity
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                <span class="m-nav__link-text">
                                                                    Messages
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                <span class="m-nav__link-text">
                                                                    FAQ
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                <span class="m-nav__link-text">
                                                                    Support
                                                                </span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <div class="m-widget3">
                            <div class="m-widget3__item">
                                <div class="m-widget3__header">
                                    <div class="m-widget3__user-img">
                                        <img class="m-widget3__img" src="assets/app/media/img/users/user1.jpg" alt="">
                                    </div>
                                    <div class="m-widget3__info">
                                        <span class="m-widget3__username">
                                            Melania Trump
                                        </span>
                                        <br>
                                        <span class="m-widget3__time">
                                            2 day ago
                                        </span>
                                    </div>
                                    <span class="m-widget3__status m--font-info">
                                        Pending
                                    </span>
                                </div>
                                <div class="m-widget3__body">
                                    <p class="m-widget3__text">
                                        Lorem ipsum dolor sit amet,consectetuer edipiscing elit,sed diam nonummy nibh euismod tinciduntut laoreet doloremagna aliquam erat volutpat.
                                    </p>
                                </div>
                            </div>
                            <div class="m-widget3__item">
                                <div class="m-widget3__header">
                                    <div class="m-widget3__user-img">
                                        <img class="m-widget3__img" src="assets/app/media/img/users/user4.jpg" alt="">
                                    </div>
                                    <div class="m-widget3__info">
                                        <span class="m-widget3__username">
                                            Lebron King James
                                        </span>
                                        <br>
                                        <span class="m-widget3__time">
                                            1 day ago
                                        </span>
                                    </div>
                                    <span class="m-widget3__status m--font-brand">
                                        Open
                                    </span>
                                </div>
                                <div class="m-widget3__body">
                                    <p class="m-widget3__text">
                                        Lorem ipsum dolor sit amet,consectetuer edipiscing elit,sed diam nonummy nibh euismod tinciduntut laoreet doloremagna aliquam erat volutpat.Ut wisi enim ad minim veniam,quis nostrud exerci tation ullamcorper.
                                    </p>
                                </div>
                            </div>
                            <div class="m-widget3__item">
                                <div class="m-widget3__header">
                                    <div class="m-widget3__user-img">
                                        <img class="m-widget3__img" src="assets/app/media/img/users/user5.jpg" alt="">
                                    </div>
                                    <div class="m-widget3__info">
                                        <span class="m-widget3__username">
                                            Deb Gibson
                                        </span>
                                        <br>
                                        <span class="m-widget3__time">
                                            3 weeks ago
                                        </span>
                                    </div>
                                    <span class="m-widget3__status m--font-success">
                                        Closed
                                    </span>
                                </div>
                                <div class="m-widget3__body">
                                    <p class="m-widget3__text">
                                        Lorem ipsum dolor sit amet,consectetuer edipiscing elit,sed diam nonummy nibh euismod tinciduntut laoreet doloremagna aliquam erat volutpat.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end:: Widgets/Support Tickets -->
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-8">
                <div class="m-portlet m-portlet--mobile ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Exclusive Datatable Plugin
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item">
                                    <div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
                                        <a href="#" class="m-portlet__nav-link btn btn-lg btn-secondary  m-btn m-btn--icon m-btn--icon-only m-btn--pill  m-dropdown__toggle">
                                            <i class="la la-ellipsis-h m--font-brand"></i>
                                        </a>
                                        <div class="m-dropdown__wrapper">
                                            <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                            <div class="m-dropdown__inner">
                                                <div class="m-dropdown__body">
                                                    <div class="m-dropdown__content">
                                                        <ul class="m-nav">
                                                            <li class="m-nav__section m-nav__section--first">
                                                                <span class="m-nav__section-text">
                                                                    Quick Actions
                                                                </span>
                                                            </li>
                                                            <li class="m-nav__item">
                                                                <a href="" class="m-nav__link">
                                                                    <i class="m-nav__link-icon flaticon-share"></i>
                                                                    <span class="m-nav__link-text">
                                                                        Create Post
                                                                    </span>
                                                                </a>
                                                            </li>
                                                            <li class="m-nav__item">
                                                                <a href="" class="m-nav__link">
                                                                    <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                    <span class="m-nav__link-text">
                                                                        Send Messages
                                                                    </span>
                                                                </a>
                                                            </li>
                                                            <li class="m-nav__item">
                                                                <a href="" class="m-nav__link">
                                                                    <i class="m-nav__link-icon flaticon-multimedia-2"></i>
                                                                    <span class="m-nav__link-text">
                                                                        Upload File
                                                                    </span>
                                                                </a>
                                                            </li>
                                                            <li class="m-nav__section">
                                                                <span class="m-nav__section-text">
                                                                    Useful Links
                                                                </span>
                                                            </li>
                                                            <li class="m-nav__item">
                                                                <a href="" class="m-nav__link">
                                                                    <i class="m-nav__link-icon flaticon-info"></i>
                                                                    <span class="m-nav__link-text">
                                                                        FAQ
                                                                    </span>
                                                                </a>
                                                            </li>
                                                            <li class="m-nav__item">
                                                                <a href="" class="m-nav__link">
                                                                    <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                    <span class="m-nav__link-text">
                                                                        Support
                                                                    </span>
                                                                </a>
                                                            </li>
                                                            <li class="m-nav__separator m-nav__separator--fit m--hide"></li>
                                                            <li class="m-nav__item m--hide">
                                                                <a href="#" class="btn btn-outline-danger m-btn m-btn--pill m-btn--wide btn-sm">
                                                                    Submit
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
                        <!--begin: Datatable -->
                        <div class="m_datatable" id="m_datatable_latest_orders"></div>
                        <!--end: Datatable -->
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <!--begin:: Widgets/Audit Log-->
                <div class="m-portlet m-portlet--full-height ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Audit Log
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="nav nav-pills nav-pills--brand m-nav-pills--align-right m-nav-pills--btn-pill m-nav-pills--btn-sm" role="tablist">
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_widget4_tab1_content" role="tab">
                                        Today
                                    </a>
                                </li>
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget4_tab2_content" role="tab">
                                        Week
                                    </a>
                                </li>
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget4_tab3_content" role="tab">
                                        Month
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="m_widget4_tab1_content">
                                <div class="m-scrollable" data-scrollable="true" data-max-height="400" style="height: 400px; overflow: hidden;">
                                    <div class="m-list-timeline m-list-timeline--skin-light">
                                        <div class="m-list-timeline__items">
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--success"></span>
                                                <span class="m-list-timeline__text">
                                                    12 new users registered
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    Just now
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--info"></span>
                                                <span class="m-list-timeline__text">
                                                    System shutdown
                                                    <span class="m-badge m-badge--success m-badge--wide">
                                                        pending
                                                    </span>
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    14 mins
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--danger"></span>
                                                <span class="m-list-timeline__text">
                                                    New invoice received
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    20 mins
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--accent"></span>
                                                <span class="m-list-timeline__text">
                                                    DB overloaded 80%
                                                    <span class="m-badge m-badge--info m-badge--wide">
                                                        settled
                                                    </span>
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    1 hr
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--warning"></span>
                                                <span class="m-list-timeline__text">
                                                    System error -
                                                    <a href="#" class="m-link">
                                                        Check
                                                    </a>
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    2 hrs
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--brand"></span>
                                                <span class="m-list-timeline__text">
                                                    Production server down
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    3 hrs
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--info"></span>
                                                <span class="m-list-timeline__text">
                                                    Production server up
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    5 hrs
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--success"></span>
                                                <span href="" class="m-list-timeline__text">
                                                    New order received
                                                    <span class="m-badge m-badge--danger m-badge--wide">
                                                        urgent
                                                    </span>
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    7 hrs
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--success"></span>
                                                <span class="m-list-timeline__text">
                                                    12 new users registered
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    Just now
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--info"></span>
                                                <span class="m-list-timeline__text">
                                                    System shutdown
                                                    <span class="m-badge m-badge--success m-badge--wide">
                                                        pending
                                                    </span>
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    14 mins
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--danger"></span>
                                                <span class="m-list-timeline__text">
                                                    New invoice received
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    20 mins
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--accent"></span>
                                                <span class="m-list-timeline__text">
                                                    DB overloaded 80%
                                                    <span class="m-badge m-badge--info m-badge--wide">
                                                        settled
                                                    </span>
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    1 hr
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--danger"></span>
                                                <span class="m-list-timeline__text">
                                                    New invoice received
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    20 mins
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--accent"></span>
                                                <span class="m-list-timeline__text">
                                                    DB overloaded 80%
                                                    <span class="m-badge m-badge--info m-badge--wide">
                                                        settled
                                                    </span>
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    1 hr
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--warning"></span>
                                                <span class="m-list-timeline__text">
                                                    System error -
                                                    <a href="#" class="m-link">
                                                        Check
                                                    </a>
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    2 hrs
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--brand"></span>
                                                <span class="m-list-timeline__text">
                                                    Production server down
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    3 hrs
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--info"></span>
                                                <span class="m-list-timeline__text">
                                                    Production server up
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    5 hrs
                                                </span>
                                            </div>
                                            <div class="m-list-timeline__item">
                                                <span class="m-list-timeline__badge m-list-timeline__badge--success"></span>
                                                <span href="" class="m-list-timeline__text">
                                                    New order received
                                                    <span class="m-badge m-badge--danger m-badge--wide">
                                                        urgent
                                                    </span>
                                                </span>
                                                <span class="m-list-timeline__time">
                                                    7 hrs
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="m_widget4_tab2_content"></div>
                            <div class="tab-pane" id="m_widget4_tab3_content"></div>
                        </div>
                    </div>
                </div>
                <!--end:: Widgets/Audit Log-->
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-4">
                <!--begin:: Widgets/Sales Stats-->
                <div class="m-portlet m-portlet--bordered-semi m-portlet--full-height ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Sales Stats
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item m-portlet__nav-item--last m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover">
                                    <a href="#" class="m-portlet__nav-link m-portlet__nav-link--icon m-portlet__nav-link--icon-xl">
                                        <i class="fa fa-genderless m--font-brand"></i>
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav">
                                                        <li class="m-nav__section m-nav__section--first">
                                                            <span class="m-nav__section-text">
                                                                Quick Actions
                                                            </span>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                <span class="m-nav__link-text">
                                                                    Activity
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                <span class="m-nav__link-text">
                                                                    Messages
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                <span class="m-nav__link-text">
                                                                    FAQ
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                <span class="m-nav__link-text">
                                                                    Support
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__separator m-nav__separator--fit"></li>
                                                        <li class="m-nav__item">
                                                            <a href="#" class="btn btn-outline-danger m-btn m-btn--pill m-btn--wide btn-sm">
                                                                Cancel
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <!--begin::Widget 6-->
                        <div class="m-widget15">
                            <div class="m-widget15__chart" style="height:180px;">
                                <canvas  id="m_chart_sales_stats"></canvas>
                            </div>
                            <div class="m-widget15__items">
                                <div class="row">
                                    <div class="col">
                                        <div class="m-widget15__item">
                                            <span class="m-widget15__stats">
                                                63%
                                            </span>
                                            <span class="m-widget15__text">
                                                Sales Grow
                                            </span>
                                            <div class="m--space-10"></div>
                                            <div class="progress m-progress--sm">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="m-widget15__item">
                                            <span class="m-widget15__stats">
                                                54%
                                            </span>
                                            <span class="m-widget15__text">
                                                Orders Grow
                                            </span>
                                            <div class="m--space-10"></div>
                                            <div class="progress m-progress--sm">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: 40%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="m-widget15__item">
                                            <span class="m-widget15__stats">
                                                41%
                                            </span>
                                            <span class="m-widget15__text">
                                                Profit Grow
                                            </span>
                                            <div class="m--space-10"></div>
                                            <div class="progress m-progress--sm">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 55%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="m-widget15__item">
                                            <span class="m-widget15__stats">
                                                79%
                                            </span>
                                            <span class="m-widget15__text">
                                                Member Grow
                                            </span>
                                            <div class="m--space-10"></div>
                                            <div class="progress m-progress--sm">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 60%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="m-widget15__desc">
                                * lorem ipsum dolor sit amet consectetuer sediat elit
                            </div>
                        </div>
                        <!--end::Widget 6-->
                    </div>
                </div>
                <!--end:: Widgets/Sales Stats-->
            </div>
            <div class="col-xl-4">
                <!--begin:: Widgets/Inbound Bandwidth-->
                <div class="m-portlet m-portlet--bordered-semi m-portlet--half-height m-portlet--fit " style="min-height: 300px">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Inbound Bandwidth
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
                                    <a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand">
                                        Today
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: auto; right: 36.5px;"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav">
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                <span class="m-nav__link-text">
                                                                    Activity
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                <span class="m-nav__link-text">
                                                                    Messages
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                <span class="m-nav__link-text">
                                                                    FAQ
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                <span class="m-nav__link-text">
                                                                    Support
                                                                </span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <!--begin::Widget5-->
                        <div class="m-widget20">
                            <div class="m-widget20__number m--font-success">
                                670
                            </div>
                            <div class="m-widget20__chart" style="height:160px;">
                                <canvas id="m_chart_bandwidth1"></canvas>
                            </div>
                        </div>
                        <!--end::Widget 5-->
                    </div>
                </div>
                <!--end:: Widgets/Inbound Bandwidth-->
                <div class="m--space-30"></div>
                <!--begin:: Widgets/Outbound Bandwidth-->
                <div class="m-portlet m-portlet--bordered-semi m-portlet--half-height m-portlet--fit " style="min-height: 300px">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Outbound Bandwidth
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
                                    <a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand">
                                        Today
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: auto; right: 36.5px;"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav">
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                <span class="m-nav__link-text">
                                                                    Activity
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                <span class="m-nav__link-text">
                                                                    Messages
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                <span class="m-nav__link-text">
                                                                    FAQ
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                <span class="m-nav__link-text">
                                                                    Support
                                                                </span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <!--begin::Widget5-->
                        <div class="m-widget20">
                            <div class="m-widget20__number m--font-warning">
                                340
                            </div>
                            <div class="m-widget20__chart" style="height:160px;">
                                <canvas id="m_chart_bandwidth2"></canvas>
                            </div>
                        </div>
                        <!--end::Widget 5-->
                    </div>
                </div>
                <!--end:: Widgets/Outbound Bandwidth-->
            </div>
            <div class="col-xl-4">
                <!--begin:: Widgets/Top Products-->
                <div class="m-portlet m-portlet--full-height m-portlet--fit ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Top Products
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
                                    <a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand">
                                        All
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: auto; right: 36.5px;"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav">
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                <span class="m-nav__link-text">
                                                                    Activity
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                <span class="m-nav__link-text">
                                                                    Messages
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                <span class="m-nav__link-text">
                                                                    FAQ
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                <span class="m-nav__link-text">
                                                                    Support
                                                                </span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <!--begin::Widget5-->
                        <div class="m-widget4 m-widget4--chart-bottom" style="min-height: 480px">
                            <div class="m-widget4__item">
                                <div class="m-widget4__img m-widget4__img--logo">
                                    <img src="assets/app/media/img/client-logos/logo3.png" alt="">
                                </div>
                                <div class="m-widget4__info">
                                    <span class="m-widget4__title">
                                        Phyton
                                    </span>
                                    <br>
                                    <span class="m-widget4__sub">
                                        A Programming Language
                                    </span>
                                </div>
                                <span class="m-widget4__ext">
                                    <span class="m-widget4__number m--font-brand">
                                        +$17
                                    </span>
                                </span>
                            </div>
                            <div class="m-widget4__item">
                                <div class="m-widget4__img m-widget4__img--logo">
                                    <img src="assets/app/media/img/client-logos/logo1.png" alt="">
                                </div>
                                <div class="m-widget4__info">
                                    <span class="m-widget4__title">
                                        FlyThemes
                                    </span>
                                    <br>
                                    <span class="m-widget4__sub">
                                        A Let's Fly Fast Again Language
                                    </span>
                                </div>
                                <span class="m-widget4__ext">
                                    <span class="m-widget4__number m--font-brand">
                                        +$300
                                    </span>
                                </span>
                            </div>
                            <div class="m-widget4__item">
                                <div class="m-widget4__img m-widget4__img--logo">
                                    <img src="assets/app/media/img/client-logos/logo4.png" alt="">
                                </div>
                                <div class="m-widget4__info">
                                    <span class="m-widget4__title">
                                        Starbucks
                                    </span>
                                    <br>
                                    <span class="m-widget4__sub">
                                        Good Coffee & Snacks
                                    </span>
                                </div>
                                <span class="m-widget4__ext">
                                    <span class="m-widget4__number m--font-brand">
                                        +$300
                                    </span>
                                </span>
                            </div>
                            <div class="m-widget4__chart m-portlet-fit--sides m--margin-top-20" style="height:260px;">
                                <canvas id="m_chart_trends_stats_2"></canvas>
                            </div>
                        </div>
                        <!--end::Widget 5-->
                    </div>
                </div>
                <!--end:: Widgets/Top Products-->
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-8">
                <!--begin:: Widgets/Best Sellers-->
                <div class="m-portlet m-portlet--full-height ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Best Sellers
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="nav nav-pills nav-pills--brand m-nav-pills--align-right m-nav-pills--btn-pill m-nav-pills--btn-sm" role="tablist">
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_widget5_tab1_content" role="tab">
                                        Last Month
                                    </a>
                                </li>
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget5_tab2_content" role="tab">
                                        last Year
                                    </a>
                                </li>
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_widget5_tab3_content" role="tab">
                                        All time
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <!--begin::Content-->
                        <div class="tab-content">
                            <div class="tab-pane active" id="m_widget5_tab1_content" aria-expanded="true">
                                <!--begin::m-widget5-->
                                <div class="m-widget5">
                                    <div class="m-widget5__item">
                                        <div class="m-widget5__pic">
                                            <img class="m-widget7__img" src="assets/app/media/img//products/product6.jpg" alt="">
                                        </div>
                                        <div class="m-widget5__content">
                                            <h4 class="m-widget5__title">
                                                Great Logo Designn
                                            </h4>
                                            <span class="m-widget5__desc">
                                                Make Metronic Great  Again.Lorem Ipsum Amet
                                            </span>
                                            <div class="m-widget5__info">
                                                <span class="m-widget5__author">
                                                    Author:
                                                </span>
                                                <span class="m-widget5__info-label">
                                                    author:
                                                </span>
                                                <span class="m-widget5__info-author-name">
                                                    Fly themes
                                                </span>
                                                <span class="m-widget5__info-label">
                                                    Released:
                                                </span>
                                                <span class="m-widget5__info-date m--font-info">
                                                    23.08.17
                                                </span>
                                            </div>
                                        </div>
                                        <div class="m-widget5__stats1">
                                            <span class="m-widget5__number">
                                                19,200
                                            </span>
                                            <br>
                                            <span class="m-widget5__sales">
                                                sales
                                            </span>
                                        </div>
                                        <div class="m-widget5__stats2">
                                            <span class="m-widget5__number">
                                                1046
                                            </span>
                                            <br>
                                            <span class="m-widget5__votes">
                                                votes
                                            </span>
                                        </div>
                                    </div>
                                    <div class="m-widget5__item">
                                        <div class="m-widget5__pic">
                                            <img class="m-widget7__img" src="assets/app/media/img//products/product10.jpg" alt="">
                                        </div>
                                        <div class="m-widget5__content">
                                            <h4 class="m-widget5__title">
                                                Branding Mockup
                                            </h4>
                                            <span class="m-widget5__desc">
                                                Make Metronic Great  Again.Lorem Ipsum Amet
                                            </span>
                                            <div class="m-widget5__info">
                                                <span class="m-widget5__author">
                                                    Author:
                                                </span>
                                                <span class="m-widget5__info-author m--font-info">
                                                    Fly themes
                                                </span>
                                                <span class="m-widget5__info-label">
                                                    Released:
                                                </span>
                                                <span class="m-widget5__info-date m--font-info">
                                                    23.08.17
                                                </span>
                                            </div>
                                        </div>
                                        <div class="m-widget5__stats1">
                                            <span class="m-widget5__number">
                                                24,583
                                            </span>
                                            <br>
                                            <span class="m-widget5__sales">
                                                sales
                                            </span>
                                        </div>
                                        <div class="m-widget5__stats2">
                                            <span class="m-widget5__number">
                                                3809
                                            </span>
                                            <br>
                                            <span class="m-widget5__votes">
                                                votes
                                            </span>
                                        </div>
                                    </div>
                                    <div class="m-widget5__item">
                                        <div class="m-widget5__pic">
                                            <img class="m-widget7__img" src="assets/app/media/img//products/product11.jpg" alt="">
                                        </div>
                                        <div class="m-widget5__content">
                                            <h4 class="m-widget5__title">
                                                Awesome Mobile App
                                            </h4>
                                            <span class="m-widget5__desc">
                                                Make Metronic Great  Again.Lorem Ipsum Amet
                                            </span>
                                            <div class="m-widget5__info">
                                                <span class="m-widget5__author">
                                                    Author:
                                                </span>
                                                <span class="m-widget5__info-author m--font-info">
                                                    Fly themes
                                                </span>
                                                <span class="m-widget5__info-label">
                                                    Released:
                                                </span>
                                                <span class="m-widget5__info-date m--font-info">
                                                    23.08.17
                                                </span>
                                            </div>
                                        </div>
                                        <div class="m-widget5__stats1">
                                            <span class="m-widget5__number">
                                                10,054
                                            </span>
                                            <br>
                                            <span class="m-widget5__sales">
                                                sales
                                            </span>
                                        </div>
                                        <div class="m-widget5__stats2">
                                            <span class="m-widget5__number">
                                                1103
                                            </span>
                                            <br>
                                            <span class="m-widget5__votes">
                                                votes
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!--end::m-widget5-->
                            </div>
                            <div class="tab-pane" id="m_widget5_tab2_content" aria-expanded="false">
                                <!--begin::m-widget5-->
                                <div class="m-widget5">
                                    <div class="m-widget5__item">
                                        <div class="m-widget5__pic">
                                            <img class="m-widget7__img" src="assets/app/media/img//products/product11.jpg" alt="">
                                        </div>
                                        <div class="m-widget5__content">
                                            <h4 class="m-widget5__title">
                                                Branding Mockup
                                            </h4>
                                            <span class="m-widget5__desc">
                                                Make Metronic Great  Again.Lorem Ipsum Amet
                                            </span>
                                            <div class="m-widget5__info">
                                                <span class="m-widget5__author">
                                                    Author:
                                                </span>
                                                <span class="m-widget5__info-author m--font-info">
                                                    Fly themes
                                                </span>
                                                <span class="m-widget5__info-label">
                                                    Released:
                                                </span>
                                                <span class="m-widget5__info-date m--font-info">
                                                    23.08.17
                                                </span>
                                            </div>
                                        </div>
                                        <div class="m-widget5__stats1">
                                            <span class="m-widget5__number">
                                                24,583
                                            </span>
                                            <br>
                                            <span class="m-widget5__sales">
                                                sales
                                            </span>
                                        </div>
                                        <div class="m-widget5__stats2">
                                            <span class="m-widget5__number">
                                                3809
                                            </span>
                                            <br>
                                            <span class="m-widget5__votes">
                                                votes
                                            </span>
                                        </div>
                                    </div>
                                    <div class="m-widget5__item">
                                        <div class="m-widget5__pic">
                                            <img class="m-widget7__img" src="assets/app/media/img//products/product6.jpg" alt="">
                                        </div>
                                        <div class="m-widget5__content">
                                            <h4 class="m-widget5__title">
                                                Great Logo Designn
                                            </h4>
                                            <span class="m-widget5__desc">
                                                Make Metronic Great  Again.Lorem Ipsum Amet
                                            </span>
                                            <div class="m-widget5__info">
                                                <span class="m-widget5__author">
                                                    Author:
                                                </span>
                                                <span class="m-widget5__info-author m--font-info">
                                                    Fly themes
                                                </span>
                                                <span class="m-widget5__info-label">
                                                    Released:
                                                </span>
                                                <span class="m-widget5__info-date m--font-info">
                                                    23.08.17
                                                </span>
                                            </div>
                                        </div>
                                        <div class="m-widget5__stats1">
                                            <span class="m-widget5__number">
                                                19,200
                                            </span>
                                            <br>
                                            <span class="m-widget5__sales">
                                                sales
                                            </span>
                                        </div>
                                        <div class="m-widget5__stats2">
                                            <span class="m-widget5__number">
                                                1046
                                            </span>
                                            <br>
                                            <span class="m-widget5__votes">
                                                votes
                                            </span>
                                        </div>
                                    </div>
                                    <div class="m-widget5__item">
                                        <div class="m-widget5__pic">
                                            <img class="m-widget7__img" src="assets/app/media/img//products/product10.jpg" alt="">
                                        </div>
                                        <div class="m-widget5__content">
                                            <h4 class="m-widget5__title">
                                                Awesome Mobile App
                                            </h4>
                                            <span class="m-widget5__desc">
                                                Make Metronic Great  Again.Lorem Ipsum Amet
                                            </span>
                                            <div class="m-widget5__info">
                                                <span class="m-widget5__author">
                                                    Author:
                                                </span>
                                                <span class="m-widget5__info-author m--font-info">
                                                    Fly themes
                                                </span>
                                                <span class="m-widget5__info-label">
                                                    Released:
                                                </span>
                                                <span class="m-widget5__info-date m--font-info">
                                                    23.08.17
                                                </span>
                                            </div>
                                        </div>
                                        <div class="m-widget5__stats1">
                                            <span class="m-widget5__number">
                                                10,054
                                            </span>
                                            <br>
                                            <span class="m-widget5__sales">
                                                sales
                                            </span>
                                        </div>
                                        <div class="m-widget5__stats2">
                                            <span class="m-widget5__number">
                                                1103
                                            </span>
                                            <br>
                                            <span class="m-widget5__votes">
                                                votes
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!--end::m-widget5-->
                            </div>
                            <div class="tab-pane" id="m_widget5_tab3_content" aria-expanded="false">
                                <!--begin::m-widget5-->
                                <div class="m-widget5">
                                    <div class="m-widget5__item">
                                        <div class="m-widget5__pic">
                                            <img class="m-widget7__img" src="assets/app/media/img//products/product10.jpg" alt="">
                                        </div>
                                        <div class="m-widget5__content">
                                            <h4 class="m-widget5__title">
                                                Branding Mockup
                                            </h4>
                                            <span class="m-widget5__desc">
                                                Make Metronic Great  Again.Lorem Ipsum Amet
                                            </span>
                                            <div class="m-widget5__info">
                                                <span class="m-widget5__author">
                                                    Author:
                                                </span>
                                                <span class="m-widget5__info-author m--font-info">
                                                    Fly themes
                                                </span>
                                                <span class="m-widget5__info-label">
                                                    Released:
                                                </span>
                                                <span class="m-widget5__info-date m--font-info">
                                                    23.08.17
                                                </span>
                                            </div>
                                        </div>
                                        <div class="m-widget5__stats1">
                                            <span class="m-widget5__number">
                                                10.054
                                            </span>
                                            <br>
                                            <span class="m-widget5__sales">
                                                sales
                                            </span>
                                        </div>
                                        <div class="m-widget5__stats2">
                                            <span class="m-widget5__number">
                                                1103
                                            </span>
                                            <br>
                                            <span class="m-widget5__votes">
                                                votes
                                            </span>
                                        </div>
                                    </div>
                                    <div class="m-widget5__item">
                                        <div class="m-widget5__pic">
                                            <img class="m-widget7__img" src="assets/app/media/img//products/product11.jpg" alt="">
                                        </div>
                                        <div class="m-widget5__content">
                                            <h4 class="m-widget5__title">
                                                Great Logo Designn
                                            </h4>
                                            <span class="m-widget5__desc">
                                                Make Metronic Great  Again.Lorem Ipsum Amet
                                            </span>
                                            <div class="m-widget5__info">
                                                <span class="m-widget5__author">
                                                    Author:
                                                </span>
                                                <span class="m-widget5__info-author m--font-info">
                                                    Fly themes
                                                </span>
                                                <span class="m-widget5__info-label">
                                                    Released:
                                                </span>
                                                <span class="m-widget5__info-date m--font-info">
                                                    23.08.17
                                                </span>
                                            </div>
                                        </div>
                                        <div class="m-widget5__stats1">
                                            <span class="m-widget5__number">
                                                24,583
                                            </span>
                                            <br>
                                            <span class="m-widget5__sales">
                                                sales
                                            </span>
                                        </div>
                                        <div class="m-widget5__stats2">
                                            <span class="m-widget5__number">
                                                3809
                                            </span>
                                            <br>
                                            <span class="m-widget5__votes">
                                                votes
                                            </span>
                                        </div>
                                    </div>
                                    <div class="m-widget5__item">
                                        <div class="m-widget5__pic">
                                            <img class="m-widget7__img" src="assets/app/media/img//products/product6.jpg" alt="">
                                        </div>
                                        <div class="m-widget5__content">
                                            <h4 class="m-widget5__title">
                                                Awesome Mobile App
                                            </h4>
                                            <span class="m-widget5__desc">
                                                Make Metronic Great  Again.Lorem Ipsum Amet
                                            </span>
                                            <div class="m-widget5__info">
                                                <span class="m-widget5__author">
                                                    Author:
                                                </span>
                                                <span class="m-widget5__info-author m--font-info">
                                                    Fly themes
                                                </span>
                                                <span class="m-widget5__info-label">
                                                    Released:
                                                </span>
                                                <span class="m-widget5__info-date m--font-info">
                                                    23.08.17
                                                </span>
                                            </div>
                                        </div>
                                        <div class="m-widget5__stats1">
                                            <span class="m-widget5__number">
                                                19,200
                                            </span>
                                            <br>
                                            <span class="m-widget5__sales">
                                                1046
                                            </span>
                                        </div>
                                        <div class="m-widget5__stats2">
                                            <span class="m-widget5__number">
                                                1046
                                            </span>
                                            <br>
                                            <span class="m-widget5__votes">
                                                votes
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!--end::m-widget5-->
                            </div>
                        </div>
                        <!--end::Content-->
                    </div>
                </div>
                <!--end:: Widgets/Best Sellers-->
            </div>
            <div class="col-xl-4">
                <!--begin:: Widgets/Authors Profit-->
                <div class="m-portlet m-portlet--bordered-semi m-portlet--full-height ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Authors Profit
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover">
                                    <a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand">
                                        All
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav">
                                                        <li class="m-nav__section m-nav__section--first">
                                                            <span class="m-nav__section-text">
                                                                Quick Actions
                                                            </span>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                <span class="m-nav__link-text">
                                                                    Activity
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                <span class="m-nav__link-text">
                                                                    Messages
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                <span class="m-nav__link-text">
                                                                    FAQ
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item">
                                                            <a href="" class="m-nav__link">
                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                <span class="m-nav__link-text">
                                                                    Support
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__separator m-nav__separator--fit"></li>
                                                        <li class="m-nav__item">
                                                            <a href="#" class="btn btn-outline-danger m-btn m-btn--pill m-btn--wide btn-sm">
                                                                Cancel
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <div class="m-widget4">
                            <div class="m-widget4__item">
                                <div class="m-widget4__img m-widget4__img--logo">
                                    <img src="assets/app/media/img/client-logos/logo5.png" alt="">
                                </div>
                                <div class="m-widget4__info">
                                    <span class="m-widget4__title">
                                        Trump Themes
                                    </span>
                                    <br>
                                    <span class="m-widget4__sub">
                                        Make Metronic Great Again
                                    </span>
                                </div>
                                <span class="m-widget4__ext">
                                    <span class="m-widget4__number m--font-brand">
                                        +$2500
                                    </span>
                                </span>
                            </div>
                            <div class="m-widget4__item">
                                <div class="m-widget4__img m-widget4__img--logo">
                                    <img src="assets/app/media/img/client-logos/logo4.png" alt="">
                                </div>
                                <div class="m-widget4__info">
                                    <span class="m-widget4__title">
                                        StarBucks
                                    </span>
                                    <br>
                                    <span class="m-widget4__sub">
                                        Good Coffee & Snacks
                                    </span>
                                </div>
                                <span class="m-widget4__ext">
                                    <span class="m-widget4__number m--font-brand">
                                        -$290
                                    </span>
                                </span>
                            </div>
                            <div class="m-widget4__item">
                                <div class="m-widget4__img m-widget4__img--logo">
                                    <img src="assets/app/media/img/client-logos/logo3.png" alt="">
                                </div>
                                <div class="m-widget4__info">
                                    <span class="m-widget4__title">
                                        Phyton
                                    </span>
                                    <br>
                                    <span class="m-widget4__sub">
                                        A Programming Language
                                    </span>
                                </div>
                                <span class="m-widget4__ext">
                                    <span class="m-widget4__number m--font-brand">
                                        +$17
                                    </span>
                                </span>
                            </div>
                            <div class="m-widget4__item">
                                <div class="m-widget4__img m-widget4__img--logo">
                                    <img src="assets/app/media/img/client-logos/logo2.png" alt="">
                                </div>
                                <div class="m-widget4__info">
                                    <span class="m-widget4__title">
                                        GreenMakers
                                    </span>
                                    <br>
                                    <span class="m-widget4__sub">
                                        Make Green Great Again
                                    </span>
                                </div>
                                <span class="m-widget4__ext">
                                    <span class="m-widget4__number m--font-brand">
                                        -$2.50
                                    </span>
                                </span>
                            </div>
                            <div class="m-widget4__item">
                                <div class="m-widget4__img m-widget4__img--logo">
                                    <img src="assets/app/media/img/client-logos/logo1.png" alt="">
                                </div>
                                <div class="m-widget4__info">
                                    <span class="m-widget4__title">
                                        FlyThemes
                                    </span>
                                    <br>
                                    <span class="m-widget4__sub">
                                        A Let's Fly Fast Again Language
                                    </span>
                                </div>
                                <span class="m-widget4__ext">
                                    <span class="m-widget4__number m--font-brand">
                                        +$200
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end:: Widgets/Authors Profit-->
            </div>
        </div>
		<?php
	}
	
	function render_QuickSearchModal() {
		?>
        <div class="modal fade" id="qSearchModal" role="dialog" tabindex="-1" role="dialog" aria-labelledby="qSearchModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="validModalLabel">Quci Search Results</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
					<div class="modal-body">

     
            
            		</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary" onclick="saveVadidDates()">Save</button>
					</div>
				</div>
			</div>
		</div>
        <?php
	}
	
	
        
	
	
	
	
	
}

?>
