<?php
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
include_once("class.matching.php");
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
$MATCHING = new Matching($DB, $RECORD);

$USER_PERMS = $USER->get_userPermissions($_SESSION['system_user_id']);

$PERSON_ID = $pageParamaters['params'][0];
$ADDRESS_ID = $pageParamaters['params'][1];
	

$P1_SQL = "
SELECT 
*
FROM
	Persons
	LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
WHERE 
	Persons.Person_id='".$PERSON_ID."'
";
$P1_DTA = $DB->get_single_result($P1_SQL);

if($ADDRESS_ID != ''):
$GEO_LOCATION = $RECORD->get_primaryAddressGeoLocation($ADDRESS_ID);
else:
$GEO_LOCATION = $RECORD->get_primaryGeoLocation($PERSON_ID);
endif;
//print_r($GEO_LOCATION);
?>


<script src="//maps.google.com/maps/api/js?key=AIzaSyBE3dc8SCYrTsKHAL2o7HwC9uhjoYIKeKE" type="text/javascript"></script>
<script src="/assets/vendors/custom/gmaps/gmaps.js" type="text/javascript"></script> 
<div class="m-content">
    <div class="row">
        <div class="col-xl-6 col-lg-12">
        	
            <div class="m-portlet m-portlet--head-sm m-portlet--tabs">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <span class="m-portlet__head-icon">
                               <i class="fa fa-heart-o"></i>
                            </span>
                            <h3 class="m-portlet__head-text">
                                Searching for <?php echo $RECORD->get_personName($PERSON_ID)?> 
                                <small><?php echo $RECORD->get_personOffice($PERSON_ID)?> | <?php echo $RECORD->get_personType($PERSON_ID)?></small>
                            </h3>
                        </div>
                    </div>
                    <div class="m-portlet__head-tools">
                        <ul class="nav nav-tabs m-tabs m-tabs-line   m-tabs-line--right m-tabs-line-danger" role="tablist">
                            <li class="nav-item m-tabs__item">
                                <a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_portlet_tab_1" role="tab" aria-expanded="true">Info</a>
                            </li>
                            <li class="nav-item m-tabs__item">
                                <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_portlet_tab_2" role="tab" aria-expanded="false">Prefs</a>
                            </li>
                            <li class="nav-item m-tabs__item">
                                <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_portlet_tab_3" role="tab" aria-expanded="false">Profile</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="m_portlet_tab_1" aria-expanded="true">
                        	<?php echo $MATCHING->searching_render_personCard($PERSON_ID); ?>
							<?php if(($GEO_LOCATION['lat'] == '34.0617109') && ($GEO_LOCATION['lng'] == '-118.4017053')): ?>
							<div class="alert alert-danger" style="margin-top:20px;">WARNING: This record's primary address is not currently geo-located. This will prevent you from properly locating records within a certain distance. To correct this please geo-locate the address from the person record view.</div>
							<?php endif; ?>
                        </div>                        
                        <div class="tab-pane" id="m_portlet_tab_2" aria-expanded="false">
                            <h4>Prefs</h4>
                            <?php echo $PROFILE->render_FullPrefs($PERSON_ID, false)?>
                        </div>
                        <div class="tab-pane" id="m_portlet_tab_3" aria-expanded="false">
                            <h4>Profile</h4>
                            <?php echo $PROFILE->render_FullProfile($PERSON_ID, false)?>
                        </div>
                    </div>
                </div>
            </div>
			
			
			<div class="m-portlet " id="quickResultsPortlet">
				<div class="m-portlet__body  m-portlet__body--no-padding">
					<div class="row m-row--no-padding m-row--col-separator-xl">
						
						<div class="col-6">
							<div style="padding:20px;">
								<h6 class="text-center">LAST 3 COMPLTED INTROS</h6>
								<?php echo $SEARCH->render_getlastDates($PERSON_ID)?>
							</div>
						</div>
						
						<div class="col-6">
							<!--begin::Total Profit-->
							<div class="m-widget24">
								<div class="m-widget24__item">
									<h4 class="m-widget24__title">
										Matching Records
									</h4>
									<br>
									<span class="m-widget24__desc">
										Total records that match your query
									</span>
									<span class="m-widget24__stats m--font-brand" id="display_allRecords">
										0
									</span>
									<!-- <div class="m--space-10"></div> -->
									<div class="progress m-progress--sm">
										<div class="progress-bar m--bg-brand" role="progressbar" style="width:100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
									<span class="m-widget24__change">
										&nbsp;
									</span>
									<span class="m-widget24__number">										
										<button type="button" onclick="sendFullSearch()" class="btn m-btn--pill btn-outline-brand btn-block" id="button-view-results" disabled>
											View Potential Matches <i class="fa fa-heart"></i>
										</button>
									</span>
								</div>
							</div>
							<!--end::Total Profit-->
						</div>
						
						
						
					</div>
				</div>
			</div>
			
			<div class="m-portlet m-portlet--head-sm m-portlet--collapsed m-portlet--collapse" data-portlet="true" id="portlet-geo-mapping">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h3 class="m-portlet__head-text">
                                Geo Location
                            </h3>
                        </div>
                    </div>
                    <div class="m-portlet__head-tools">
                    	<ul class="m-portlet__nav">
                            <li class="m-portlet__nav-item">
                                <a href=""  data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon" >
                                    <i class="la la-plus"></i>
                                </a>
                            </li>
                            <li class="m-portlet__nav-item">
                                <a href="#"  data-portlet-tool="fullscreen" class="m-portlet__nav-link m-portlet__nav-link--icon">
                                    <i class="la la-expand"></i>
                                </a>
                            </li>
                        </ul>                        
                    </div>
                </div>
                <div class="m-portlet__body">
                    <div class="m-widget15">
                        <div class="m-widget15__map m-portlet__pull-sides">
                            <div id="m_chart_latest_trends_map" style="height:440px;"></div>
                        </div>                     
                    </div>
                </div>
            </div>

		</div>
        
        <!-- SECONDA COLUMNS -->
        <div class="col-6">
			
			<form id="searchForForm">
			
			<div class="m-portlet m-portlet--head-sm" data-portlet="true" id="m_portlet_tools_3">
				<div class="m-portlet__foot">
					<div class="m-form__actions m-form__actions--right">
						<div class="row">
							<div class="col m--align-left">                                
								<!--
								<button type="reset" class="btn btn-success" disabled="disabled">
									Save <i class="fa fa-save"></i>
								</button>
								<button type="reset" class="btn btn-metal" disabled="disabled">
									Load <i class="fa fa-folder-open-o"></i>
								</button>
								-->
								<button type="button" class="btn btn-secondary" onclick="addCustomField()">
									Custom Field <i class="fa fa-plus"></i>
								</button>							
							</div>
							
							<div class="col m--align-right">
								<button type="button" onclick="clearFormElements()" class="btn btn-secondary">
									Clear <i class="fa fa-remove"></i>
								</button>
								<button type="button" onclick="sendQuickSearch()" class="btn btn-brand m-btn button-submitSearch ">
									Search <i class="fa fa-search"></i>
								</button>
							</div>                            
						</div>
					</div>
				</div>
			</div>
            
			<!-- BESIC SEARCH FORM AREA -->
			<div class="m-portlet m-portlet--head-sm" data-portlet="true" id="m_portlet_tools_2">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <span class="m-portlet__head-icon">
                                <i class="la la-filter"></i>
                            </span>
                            <h3 class="m-portlet__head-text">
                                Basic Search Tools
                            </h3>
                        </div>
                    </div>
                    <div class="m-portlet__head-tools">
                        <ul class="m-portlet__nav">
                            <li class="m-portlet__nav-item">
                                <a href=""  data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon" >
                                    <i class="la la-plus"></i>
                                </a>
                            </li>
                            <li class="m-portlet__nav-item">
                                <a href="#"  data-portlet-tool="fullscreen" class="m-portlet__nav-link m-portlet__nav-link--icon">
                                    <i class="la la-expand"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <input type="hidden" name="searchForID" value="<?php echo $PERSON_ID?>" />
                <div class="m-portlet__body">
				
					<div class="form-group m-form__group row">
                        <?php $ageChoices = explode("|", $P1_DTA['prefQuestion_age_floor']); ?>
                        <label class="col-lg-3 col-form-label">
                            Seeking Age:
                        </label>
                        <div class="col-lg-9">
							<div class="input-group m-input-group">
                                <span class="input-group-addon">From:</span>
                                <select name="age_seek_floor" id="age_seek_floor" class="form-control m-input">
                                <?php for($i=18; $i<=99; $i++) {?>                                        
                                    <option value="<?php echo $i?>" <?php echo (($ageChoices[0] == $i)?'selected':'')?>><?php echo $i?></option>                        
                                <?php } ?>                    
                                </select>
                                <span class="input-group-addon">&nbsp;&nbsp;To:</span>
                                <select name="age_seek_peak" id="age_seek_peak" class="form-control m-input">
                                <?php for($i=18; $i<=99; $i++) {?>                                        
                                    <option value="<?php echo $i?>" <?php echo (($ageChoices[1] == $i)?'selected':'')?>><?php echo $i?></option>
                                <?php } ?> 
                                </select>
                            </div>
							<span class="m-form__help">
								<small>
								<a href="javascript:;" onclick="addAgeToSearch('21','35');">21 to 35</a> | 
								<a href="javascript:;" onclick="addAgeToSearch('35','45');">35 to 45</a> | 
								<a href="javascript:;" onclick="addAgeToSearch('45','55');">45 to 55</a> | 
								<a href="javascript:;" onclick="addAgeToSearch('55','65');">55 to 65</a> | 
								<a href="javascript:;" onclick="addAgeToSearch('65','99');">Over 65</a> | 
								</small>
							</span>
                        </div>
                    </div>                                       
					
					<div class="m-form__group form-group row">
                        <label class="col-lg-3 col-form-label">Seeking Gender:</label>
                        <?php
                        $preSelected = array($P1_DTA['prefQuestion_Gender']);	
                        ?>
                        <div class="col-lg-9">
                            <div class="m-checkbox-list">
                                <label class="m-checkbox">
                                    <input type="checkbox" name="Gender[]" class="genderRadio" value="M" <?php echo ((in_array('M', $preSelected))? 'checked':'')?>>
                                    <i class="fa fa-male"></i> Male            	
                                    <span></span>
                                </label>
                                <label class="m-checkbox">
                                    <input type="checkbox" name="Gender[]" class="genderRadio" value="F" <?php echo ((in_array('F', $preSelected))? 'checked':'')?>>
                                    <i class="fa fa-female"></i> Female
                                    <span></span>
                                </label>
                            </div>
						</div>                            
                    </div>
                    
                    <div class="form-group m-form__group row">
                        <label class="col-lg-3 col-form-label">
                            Record Types:
                        </label>
                        <div class="col-lg-9">
                        	<?php $preSelected = explode("|", $P1_DTA['prefQuestion_Pref_MemberTypes']); ?>
							<select class="form-control m-select2" id="PersonsTypes_id" name="PersonsTypes_id[]" multiple="multiple">
							<?php                         
                            $sql = "SELECT * FROM PersonTypes WHERE PersonsTypes_id NOT IN (1,2,9) ORDER BY PersonsTypes_order";
                            $snd = $DB->get_multi_result($sql);
                            foreach($snd as $dta):
                                ?><option value="<?php echo $dta['PersonsTypes_id']?>" <?php echo ((in_array($dta['PersonsTypes_id'], $preSelected))? 'selected':'')?>><?php echo $dta['PersonsTypes_text']?></option><?php
                            endforeach;
                            ?>
                            </select>
                        </div>
                    </div>

					<div class="m-form__group form-group row">
						<div class="col-3">
							&nbsp;
						</div>
						<label class="col-4 col-form-label">
							Include Archive Records <i class="fa fa-info-circle" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Will include records that are are assigned to Archived Acounts (Legacy Leads)" data-original-title="Include Archived Records"></i>
						</label>
						<div class="col-3">
							<span class="m-switch m-switch--icon">
								<label>
									<input type="checkbox" name="showArchived" value="1">
									<span></span>
								</label>
							</span>							
						</div>
					</div>
                    
                    <div class="form-group m-form__group row">
                        <label class="col-lg-3 col-form-label">
                            KI Locations:
                        </label>
                        <div class="col-lg-9">
                            <select class="form-control m-select2" id="Offices_id" name="Offices_id[]" multiple="multiple">
							<?php
                            $preSelected = explode("|", $P1_DTA['prefQuestion_Pref_Offices']);	
                            $sql = "SELECT * FROM Offices ORDER BY Offices_id";
                            $snd = $DB->get_multi_result($sql);
                            foreach($snd as $dta):
                                ?><option value="<?php echo $dta['Offices_id']?>" <?php echo ((in_array($dta['Offices_id'], $preSelected))? 'selected':'')?>><?php echo $dta['office_Name']?></option><?php
                            endforeach;
                            ?>
                            </select>
						</div>
					</div>
					
					<div class="m-form__group form-group row">
						<div class="col-3">
							&nbsp;
						</div>
						<label class="col-4 col-form-label">
							Show records on map<br>
							<small>may slow down results display</small>
						</label>
						<div class="col-3">
							<span class="m-switch m-switch--icon">
								<label>
									<input type="checkbox" name="showMap" value="1">
									<span></span>
								</label>
							</span>							
						</div>
					</div>

                    <div class="form-group m-form__group row">
                        <label class="col-lg-3 col-form-label">
                            Member Types:
                        </label>
                        <div class="col-lg-9">
                            <select class="form-control m-select2" id="prQuestion_657" name="prQuestion_657[]" multiple="multiple">
                                <?php
                                if(isset($preLoad['prQuestion_657'])) {
                                    $preSelected = $preLoad['prQuestion_657'];
                                } else {
                                    $preSelected = array();	
                                }						
                                $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='657' ORDER BY QuestionsAnswers_order ASC";
                                $qa_snd = $DB->get_multi_result($qa_sql);
                                //print_r($qa_snd);
                                foreach($qa_snd as $qa_dta):
                                    ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                                endforeach;
                                ?>
							</select>
						</div>
					</div>

                    <div class="form-group m-form__group row">
                        <label class="col-lg-3 col-form-label">Ranking:</label>        
                        <div class="col-lg-9">
                            <select class="form-control m-select2" id="prQuestion_664" name="prQuestion_664[]" multiple="multiple">
                            <?php
                            if(isset($preLoad['prQuestion_664'])) {
                                $preSelected = $preLoad['prQuestion_664'];
                            } else {
                                $preSelected = array();	
                            }
                            $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='664' ORDER BY QuestionsAnswers_order ASC";
                            $qa_snd = $DB->get_multi_result($qa_sql);
                            //print_r($qa_snd);
                            foreach($qa_snd as $qa_dta):
                                ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                            endforeach;
                            ?>
                            </select>
							<span class="m-form__help">
								<small>
								<a href="javascript:;" onclick="addRankToSearch('topRanked');"> Top Ranked 8 to 12</a> |
								</small>
							</span>
						</div>
					</div>
                    
                    <div class="form-group m-form__group row">                        
                        <label class="col-lg-3 col-form-label">Income:</label>
                        <div class="col-lg-9">
                            <select class="form-control m-select2" id="prQuestion_631" name="prQuestion_631[]" multiple="multiple">
                            <?php
                            $preSelected = explode("|", $P1_DTA['prefQuestion_631']);	
                            $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='631' ORDER BY QuestionsAnswers_order ASC";
                            $qa_snd = $DB->get_multi_result($qa_sql);
                            //print_r($qa_snd);
                            foreach($qa_snd as $qa_dta):
                                ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                            endforeach;
                            ?>
                            </select>
							<span class="m-form__help">
								<small>
								<a href="javascript:;" onclick="addIncomeToSearch('over1M');">Over 1 M</a> | 
								<a href="javascript:;" onclick="addIncomeToSearch('over500');">Over 500K</a> | 
								<a href="javascript:;" onclick="addIncomeToSearch('over250');">Over 250K</a> | 
								<a href="javascript:;" onclick="addIncomeToSearch('below250');">Under 250K</a> | 
								<a href="javascript:;" onclick="addIncomeToSearch('below150');">Under 150K</a> | 
								</small>
							</span>
							
							
                        </div>                        
                    </div> 
                    
                    <div class="form-group m-form__group row">
                        <label class="col-lg-3 col-form-label">Height:</label>        
                        <div class="col-lg-9">
                            <select class="form-control m-select2" id="prQuestion_621" name="prQuestion_621[]" multiple="multiple">
                            <?php
                            $preSelected = explode("|", $P1_DTA['prefQuestion_621']);
                            $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='621' ORDER BY QuestionsAnswers_order ASC";
                            $qa_snd = $DB->get_multi_result($qa_sql);
                            //print_r($qa_snd);
                            foreach($qa_snd as $qa_dta):
                                ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                            endforeach;
                            ?>
                            </select>
							<span class="m-form__help">
								<small>
								<a href="javascript:;" onclick="addHeightToSearch('under5_4');">Under 5'4</a> | 
								<a href="javascript:;" onclick="addHeightToSearch('over5_4');">Over 5'4</a> | 
								<a href="javascript:;" onclick="addHeightToSearch('over6_0');">Over 6'0</a> | 
								<a href="javascript:;" onclick="addHeightToSearch('under6_0');">Under 6'0</a> | 
								<a href="javascript:;" onclick="addHeightToSearch('54_510');">5'4 to 5'10</a> | 
								<a href="javascript:;" onclick="addHeightToSearch('510_6');">5'10 to 6'0</a> | 
								<a href="javascript:;" onclick="addHeightToSearch('6_66');">6'0 to 6'6</a> | 
								</small>
							</span>
						</div>
					</div>
                    
                    <div class="form-group m-form__group row">    
                        <label class="col-lg-3 col-form-label">Weight:</label>
                        <div class="col-lg-9">
                            <select class="form-control m-select2" id="prQuestion_622" name="prQuestion_622[]" multiple="multiple">
                            <?php
                            $preSelected = explode("|", $P1_DTA['prefQuestion_622']);
                            $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='622' ORDER BY QuestionsAnswers_order ASC";
                            $qa_snd = $DB->get_multi_result($qa_sql);
                            //print_r($qa_snd);
                            foreach($qa_snd as $qa_dta):
                                ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                            endforeach;
                            ?>
                            </select>
							<span class="m-form__help">
								<small>
								<a href="javascript:;" onclick="addWeightToSearch('under_150');">Under 150lbs</a> |
								<a href="javascript:;" onclick="addWeightToSearch('under_200');">Under 200lbs</a> | 
								<a href="javascript:;" onclick="addWeightToSearch('under_250');">Under 250lbs</a> | 								
								</small>
							</span>
                        </div>                        
                    </div>
                    
                    <div class="form-group m-form__group row">    
                        <label class="col-lg-3 col-form-label">Religion:</label>
                        <div class="col-lg-9">
                            <select class="form-control m-select2" id="prQuestion_637" name="prQuestion_637[]" multiple="multiple">
                            <?php
                            $preSelected = explode("|", $P1_DTA['prefQuestion_637']);
                            $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='637' ORDER BY QuestionsAnswers_order ASC";
                            $qa_snd = $DB->get_multi_result($qa_sql);
                            //print_r($qa_snd);
                            foreach($qa_snd as $qa_dta):
                                ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                            endforeach;
                            ?>
                            </select>
                        </div>                        
                    </div>
                    
                    <div class="form-group m-form__group row">    
                        <label class="col-lg-3 col-form-label">Race:</label>
                        <div class="col-lg-9">
                            <select class="form-control m-select2" id="prQuestion_624" name="prQuestion_624[]" multiple="multiple">
                            <?php
                            $preSelected = explode("|", $P1_DTA['prefQuestion_624']);
                            $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='624' ORDER BY QuestionsAnswers_order ASC";
                            $qa_snd = $DB->get_multi_result($qa_sql);
                            //print_r($qa_snd);
                            foreach($qa_snd as $qa_dta):
                                ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                            endforeach;
                            ?>
                            </select>
                        </div>                        
                    </div>
					
					<div class="form-group m-form__group row">    
                        <label class="col-lg-3 col-form-label">Profile:</label>
                        <div class="col-lg-9">
                            <div class="input-group">                            	
	                            <input type="text" name="ProfileText" id="ProfileText" class="form-control m-input" placeholder="search text..." value="">
                                <span class="input-group-addon"><i class="flaticon-info"  data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="This will search all of the text responses from records trying to find a string match." data-original-title="Profile Text Search"></i></span>
							</div> 
                        </div>                        
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-5">
                        	
                            <div class="m-form__group form-group row">
		                        <label class="col-lg-6 col-form-label">Have Children:</label>        
        		                <div class="col-lg-6">
                                    <div class="m-checkbox-list">
                                    <?php
									$preSelected = explode("|", $P1_DTA['prefQuestion_632']);
                                    $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='632' ORDER BY QuestionsAnswers_order ASC";
                                    $qa_snd = $DB->get_multi_result($qa_sql);
                                    //print_r($qa_snd);
                                    foreach($qa_snd as $qa_dta):
                                        ?>
                                        <label class="m-checkbox">
                                            <input type="checkbox" name="prQuestion_632[]" class="prQuestion_632" value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'checked':'')?>>
                                            <?php echo $qa_dta['QuestionsAnswers_value']?>           	
                                            <span></span>
                                        </label>
                                        <?php
                                    endforeach;
                                    ?>
                                    </div>
                                </div>
							</div>
                            
                            <div class="m-form__group form-group row">
	                            <label class="col-lg-6 col-form-label">Will<br />Travel:</label>
                                <div class="col-lg-6">
                                    <div class="m-checkbox-list">
                                    <?php
									$preSelected = explode("|", $P1_DTA['prefQuestion_652']);
                                    $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='652' ORDER BY QuestionsAnswers_order ASC";
                                    $qa_snd = $DB->get_multi_result($qa_sql);
                                    //print_r($qa_snd);
                                    foreach($qa_snd as $qa_dta):
                                        ?>
                                        <label class="m-checkbox">
                                            <input type="checkbox" name="prQuestion_652[]" class="prQuestion_652" value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'checked':'')?>>
                                            <?php echo $qa_dta['QuestionsAnswers_value']?>           	
                                            <span></span>
                                        </label>
                                        <?php
                                    endforeach;
                                    ?>
                                    </div>
                                </div>
							</div>
                            
						</div>                                                            
                        
                        <div class="col-lg-7">                        
                            <div class="m-form__group form-group row">
                                <label class="col-lg-4 col-form-label">Want<br />Children:</label>
                                <div class="col-lg-8">
                                    <div class="m-checkbox-list">
                                    <?php
									//$preSelected = explode("|", $P1_DTA['prefQuestion_634']);
									$preSelected = array();
                                    $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='634' ORDER BY QuestionsAnswers_order ASC";
                                    $qa_snd = $DB->get_multi_result($qa_sql);
                                    //print_r($qa_snd);
                                    foreach($qa_snd as $qa_dta):
                                        ?>
                                        <label class="m-checkbox">
                                            <input type="checkbox" name="prQuestion_634[]" class="prQuestion_634" value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'checked':'')?>>
                                            <?php echo $qa_dta['QuestionsAnswers_value']?>           	
                                            <span></span>
                                        </label>
                                        <?php
                                    endforeach;
                                    ?>
                                    </div>
                                </div>
                            </div>                        
						</div>                        
						<div id="customSearchFieldsArea" style="width:100%;"></div>
						<input type="hidden" name="addressID" value="<?php echo $ADDRESS_ID?>">
                    </div>				
                </div>
            </div>
			<!-- END BASIC SEARCH OPTIONS -->
			
			<!-- GEO GRAPHIC OPTIONS -->
			<div class="m-portlet m-portlet--head-sm"  data-portlet="true" id="m_portlet_tools_3">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <span class="m-portlet__head-icon">
                                <i class="flaticon-placeholder-1"></i>
                            </span>
                            <h3 class="m-portlet__head-text">
                                Geographic Search Tools
                            </h3>
                        </div>
                    </div>
                    <div class="m-portlet__head-tools">
                        <ul class="m-portlet__nav">
                            <li class="m-portlet__nav-item">
                                <a href=""  data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon" >
                                    <i class="la la-plus"></i>
                                </a>
                            </li>
                            <li class="m-portlet__nav-item">
                                <a href="#"  data-portlet-tool="fullscreen" class="m-portlet__nav-link m-portlet__nav-link--icon">
                                    <i class="la la-expand"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
				<div class="m-portlet__body">
					
					<div class="form-group m-form__group row">
                        <label class="col-lg-3 col-form-label text-left">
                            <label class="m-radio">
                                <input type="radio" name="GeoSearch" id="validRadio" value="0">
                                 Ignore Geo Data:          	
                                <span></span>
                            </label>
                        </label>
                        <div class="col-lg-9">&nbsp;</div>
					</div>
                    
                    
                    <div class="form-group m-form__group row">
                        <label class="col-lg-3 col-form-label text-left">
                            <label class="m-radio">
                                <input type="radio" name="GeoSearch" id="validRadio" value="distance" checked>
                                 Distance from:          	
                                <span></span>
                            </label>
                        </label>
                        <div class="col-lg-5">
                        	<input id="m_touchspin_1" type="text" class="form-control" value="<?php echo (($P1_DTA['prefQuestion_distance'] != '')? $P1_DTA['prefQuestion_distance']:50)?>" name="DistanceInMiles" placeholder="Select distance" type="text">                        
                        </div>
                        <div class="col-lg-3">
                        	<small id="default_addressDisplayArea">
							<?php
							if($ADDRESS_ID != ''):
								echo $RECORD->get_Address($ADDRESS_ID);
							else:							
								echo $RECORD->get_primaryAddress($PERSON_ID);
							endif;
							?>
							</small>
							<small id="custom_addressDisplayArea" style="display:none;">
							
							</small>
						</div>
						<div class="col-lg-1">
							<button type="button" class="btn btn-sm btn-default" id="customCenterEdit" data-toggle="modal" data-target="#customCenterModal">Edit</button>
							<button type="button" class="btn btn-sm btn-default" id="customCenterCancel" style="display:none;" onclick="cancelCustomCenter()"><i class="la la-ban"></i></button>
						</div>
					</div>

					<input type="hidden" name="geo_overwrite" id="geo_overwrite" value="0">
					<input type="hidden" name="geo_lat" id="geo_lat" value="0">
					<input type="hidden" name="geo_lng" id="geo_lng" value="0">
                    
                    <div class="form-group m-form__group row">
                        <label class="col-lg-3 col-form-label text-left">
                            <label class="m-radio">
                                <input type="radio" name="GeoSearch" id="validRadio" value="states">
                                 State/Province:          	
                                <span></span>
                            </label>
                        </label>
                        <div class="col-lg-9">
                        	<?php
							//echo $P1_DTA['prefQuestion_Pref_States']."<br>\n";
							//echo $P1_DTA['prefQuestion_Pref_States'];
							if($P1_DTA['prefQuestion_Pref_States'] != '') {
								$preSelected = explode("|", $P1_DTA['prefQuestion_Pref_States']);
							} else {
								$preSelected = array();	
							}
							//print_r($preSelected);
							?>       
							<select class="form-control m-select2" id="State" name="States[]" multiple="multiple">							
							<?php
							$c_sql = "SELECT * FROM SOURCE_Contries";
							$c_snd = $DB->get_multi_result($c_sql);
							foreach($c_snd as $c_dta):
								$countryID = $c_dta['CountryCode'];
								$s_sql = "SELECT * FROM SOURCE_States WHERE CountryCode='".$countryID."'";
								$s_fnd = $DB->get_multi_result($s_sql, true);
								if($s_fnd > 0):
									$s_snd = $DB->get_multi_result($s_sql);
									?><optgroup label="<?php echo $c_dta['Country']?>"><?php
									foreach($s_snd as $s_dta):
										?><option value="<?php echo $c_dta['CountryCode']?>|<?php echo $s_dta['StateCode']?>" <?php echo ((in_array($s_dta['State'], $preSelected))? 'selected':'')?>><?php echo $s_dta['State']?></option><?php
									endforeach;
									?></optgroup><?php
								endif;	
							endforeach;
							?>
							</select>                        
                        </div>
					</div>
					
					<div class="form-group m-form__group row">
                        <label class="col-lg-3 col-form-label text-left">
                            <label class="m-radio">
                                <input type="radio" name="GeoSearch" id="validRadio" value="country">
                                 Country:          	
                                <span></span>
                            </label>
                        </label>
                        <div class="col-lg-9">
                        	<?php
							//echo $P1_DTA['prefQuestion_Pref_States']."<br>\n";
							//echo $P1_DTA['prefQuestion_Pref_States'];
							if($P1_DTA['prefQuestion_Pref_States'] != '') {
								$preSelected = array("US");
							} else {
								$preSelected = array("US");	
							}
							?>       
							<select class="form-control m-select2" id="Country" name="Country[]" multiple="multiple">							
							<?php
							$c_sql = "SELECT * FROM SOURCE_Contries";
							$c_snd = $DB->get_multi_result($c_sql);
							foreach($c_snd as $c_dta):
								$countryID = $c_dta['CountryCode'];								
								?><option value="<?php echo $c_dta['CountryCode']?>" <?php echo ((in_array($c_dta['CountryCode'], $preSelected))? 'selected':'')?>><?php echo $c_dta['Country']?></option><?php
							endforeach;
							?>
							</select>                        
                        </div>
					</div>
				
				</div>
			</div>
			<!-- END GEO GRAPHIC OPTIONS -->

			<div class="m-portlet m-portlet--primary m-portlet--head-sm" data-portlet="true" id="m_portlet_tools_3">
				<div class="m-portlet__foot">
					<div class="m-form__actions m-form__actions--right">
						<div class="row">
							<div class="col m--align-left">                                
								<!--
								<button type="reset" class="btn btn-success" disabled="disabled">
									Save <i class="fa fa-save"></i>
								</button>
								<button type="reset" class="btn btn-metal" disabled="disabled">
									Load <i class="fa fa-folder-open-o"></i>
								</button>
								-->
								<button type="button" class="btn btn-secondary" onclick="addCustomField()">
									Custom Field <i class="fa fa-plus"></i>
								</button>
							</div>

							<div class="col m--align-right">
								<button type="button" onclick="clearFormElements()" class="btn btn-secondary">
									Clear <i class="fa fa-remove"></i>
								</button>
								<button type="button" onclick="sendQuickSearch()" class="btn btn-brand m-btn button-submitSearch ">
									Search <i class="fa fa-search"></i>
								</button>                                
							</div>                            
						</div>
					</div>
				</div>
			</div>
			
			</form>

        </div>
	</div>
</div>


<div class="modal fade" id="customCenterModal" role="dialog" aria-labelledby="customCenterModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="customSearchFieldModalLabel">Custom Search Center </h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form class="m-form m-form--fit m-form--label-align-right" id="newCenterForm" onsubmit="return setNewCenter();">
				<div class="form-group m-form__group">
					<label for="newCenter">
						Search Center Address
					</label>
					<input type="text" class="form-control m-input" id="newCenter" id="newCenter" autocomplete="off">
					<span class="m-form__help">
						Enter a city & state to set the center of your search
					</span>
				</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="setNewCenter()">Set Search Center</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="searchResultsModal" role="dialog" aria-labelledby="searchResultsModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="prospectMeetModalLabel"><i class="flaticon-speech-bubble-1"></i> Potential Matches <small><span id="display_FoundNumber"></span></small></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
			
			<div class="row">
				
				<div class="col-6">					
					<div class="form-group m-form__group row">
						<label class="col-form-label col-lg-4 col-sm-12 text-right">
							Min Match %:
						</label>
						<div class="col-lg-8 col-md-8 col-sm-12">
							<select id="matchAbove" class="form-control m-bootstrap-select m_selectpicker" style="width:100%;">
								<option value="0">0%</option>
								<option value="10">10%</option>
								<option value="20">20%</option>
								<option value="30">30%</option>
								<option value="40">40%</option>
								<option value="50">50%</option>
								<option value="60">60%</option>
								<option value="70">70%</option>
								<option value="80">80%</option>
								<option value="90">90%</option>
								<option value="100">100%</option>								
							</select>
						</div>
					</div>					
				</div>
				<div class="col-6">
					<div class="form-group m-form__group row">
						<label class="col-form-label col-lg-4 col-sm-12 text-right">
							Sort By:
						</label>
						<div class="col-lg-8 col-md-8 col-sm-12">
							<select id="sortResultsBy" class="form-control m-bootstrap-select m_selectpicker" style="width:100%;">
								<option value="DateCreated">Date Created</option>
								<option value="distance">Distance</option>
								<option value="DateUpdated">Date Updated</option>
								<option value="score">Compatibility</option>
							</select>
						</div>
					</div>
				</div>
				<div class="col-12">				
					<div class="m-checkbox-list" style="margin-top:6px;">						
						<label class="m-checkbox m-checkbox--solid m-checkbox--state-success">
							<input type="checkbox" id="includePrevious" value="1" checked>
							Include Previous Matches
							<span></span>
						</label>						
					</div>				
				</div>
			</div>
			
            <div id="searchListDisplay"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="updateProspectMeet()">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="customSearchFieldModal" role="dialog" aria-labelledby="customSearchFieldModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="customSearchFieldModalLabel"><i class="flaticon-speech-bubble-1"></i> Add Custom Field <small><span id="display_FoundNumber"></span></small></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
			
			<div class="form-group m-form__group">
				<label for="customQuestionSelect">
					Select Question to Add to Search Form
				</label>
				<select class="form-control m-input m-input--square" id="customQuestionSelect">
					<option value=""></option>
<?php
$exclude_ids = array(657,664,631,621,622,637,624,632,634,653);
$sql = "SELECT * FROM QuestionsCategories WHERE QuestionsCategories_id NOT IN (15) ORDER BY QuestionsCategories_id ASC";
$snd = $DB->get_multi_result($sql);
foreach($snd as $dta):
	$catID = $dta['QuestionsCategories_id'];
	$q_sql = "SELECT * FROM Questions WHERE QuestionsCategories_id='".$catID."' AND Questions_text != '' AND Questions_active='1' AND QuestionTypes_id IN (3,4,5,6) AND Questions_id NOT IN (".implode(",", $exclude_ids).") ORDER BY Questions_order ASC";
	$q_found = $DB->get_multi_result($q_sql, true);
	if($q_found):
		?><optgroup title="<?php echo $dta['QuestionsCategories_name']?>" label="<?php echo $dta['QuestionsCategories_name']?>"><?php
		$q_snd = $DB->get_multi_result($q_sql);
		foreach($q_snd as $q_dta):
			if(!in_array($q_dta['Questions_id'], $exeptions)) {
				$fieldName = 'prQuestion_'.$q_dta['Questions_id'];
				$fieldValue = $p_snd[$fieldName];
				?><option value="<?php echo $q_dta['Questions_id']?>"><?php echo $q_dta['Questions_text']?></option><?php
			}
		endforeach;
		?></optgroup><?php
	endif;				
endforeach;	
?>
				</select>
			</div>
			
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="AddToSearchForm()">Add to Form</button>
			</div>
		</div>
	</div>
</div> 

<script>
var gmap;
var marker = new Array();
var infowindow;
var searchCircle;

function setNewCenter() {
	mApp.block("#customCenterModal .modal-content", {
		overlayColor: "#CCCCCC",
		type: "loader",
		state: "success",
		size: "lg"
	});
	//var formData = $('#newCenterForm').serializeArray();
	$.post('/ajax/searchForMatch.php?action=getNewCenter', {
		location: $('#newCenter').val()		
	}, function(data) {
		$('#customCenterModal').modal('hide');
		$('#newCenter').val('');
		if(data.code == '401') {
			alert('Unable to Geo-Locate address provided. Please make sure it is spelled correctly and if needed you can add a zip code that can greatly assist in geo-location.');
		} else {
			$('#geo_lat').val(data.lat);
			$('#geo_lng').val(data.lng);
			$('#geo_overwrite').val(1);	
			var customLocation = '<span class="m-badge m-badge--warning m-badge--wide">Custom Location</span><br>'+data.city+', '+data.state+' '+data.country;
			$('#default_addressDisplayArea').hide();
			$('#custom_addressDisplayArea').html(customLocation);
			$('#custom_addressDisplayArea').show();
			$('#customCenterEdit').hide();
			$('#customCenterCancel').show();
		}
		mApp.unblock('#customCenterModal .modal-content');
	}, "json");
	return false;
}
function cancelCustomCenter() {
	$('#custom_addressDisplayArea').hide();
	$('#default_addressDisplayArea').show();
	$('#geo_overwrite').val(0);	
	$('#customCenterCancel').hide();
	$('#customCenterEdit').show();
}
function addCustomField() {
	$('#customSearchFieldModal').modal('show');	
}
function AddToSearchForm() {
	var qid = $('#customQuestionSelect').val();
	$.post('/ajax/searchForMatch.php?action=loadCustomQuestion', {
		qid: qid
	}, function(data) {
		$('#customSearchFieldsArea').append(data);
	});	
}
function removeFormBlock(qid) {
	$('.customFormItemWrapper').each(function() {
		tqid = $(this).attr("data-id");
		if(tqid == qid) {
			$(this).remove();
		}
	});	
}

function clearFormElements() {
	$("#prQuestion_631").val('');
	$('#prQuestion_631').trigger('change'); 
	
	$('#prQuestion_621').val('');
	$('#prQuestion_621').trigger('change');

	$('#prQuestion_622').val('');
	$('#prQuestion_622').trigger('change');
	
	$('#prQuestion_637').val('');
	$('#prQuestion_637').trigger('change');
	
	$('#prQuestion_624').val('');
	$('#prQuestion_624').trigger('change');	
	$('#ProfileText').val('');
	
	alert('Form Cleared');
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
			e.message = "Go to Intro Record", e.title = "Intro Record Created", e.url = "/intro/"+data.id, e.target = "_blank";
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
		sendSearch();
	}, "json");	
}

function sendQuickSearch() {
	clearOverlays();
	searchCircle.setMap(null);
	
	var formData = $('#searchForForm').serializeArray();
	$('.button-submitSearch').attr('disabled', true);
	$('.button-submitSearch').addClass('m-loader m-loader--light m-loader--left');
	//mApp.block("#portlet-search-results", {});
	mApp.block('#portlet-geo-mapping', {
		overlayColor: "#CCCCCC",
		type: "loader",
		state: "success",
		size: "lg"
	});
	mApp.block('#quickResultsPortlet', {
		overlayColor: "#FFFFFF",
		type: "loader",
		state: "success",
		message: "Searching for Matches...",
		blockMsgClass: "InfoLoadingProfile"
	});
	
	$.post('/ajax/searchForMatch.php?action=searchForMatchQuick', formData, function(data) {
		$('#searchListDisplay').html(data.html);
		$('.button-submitSearch').removeClass('m-loader m-loader--light m-loader--left');
		$('.button-submitSearch').attr('disabled', false);
		$('#display_foundClients').html(data.found_clients);
		$('#display_foundPartic').html(data.found_participating);
		$('#display_foundResc').html(data.found_resources);
		$('#display_foundLeads').html(data.found_leads);
		$('#display_FoundNumber').html('Found '+data.found+' matches');
		$('#display_allRecords').html(data.found);
		//mApp.unblock("#portlet-search-results");
		mApp.unblock("#portlet-geo-mapping");
		mApp.unblock('#quickResultsPortlet');		
		$('#button-view-results').attr('disabled', false);
		console.log(data.SQL);		
		//console.log(data);
		//console.log(typeof data.cords.length);
		
		if(data.showMap == true) {
			if(data.cords.length) {	
				for(i=0; i<data.cords.length; i++) {
					cord_lat = data.cords[i].lat;
					cord_lng = data.cords[i].lng;
					title = data.cords[i].name;
					contentString = '/profile/'+data.cords[i].id;
					//console.log(cord_lat+'|'+cord_lng+'|'+title);
					//icons = data.icon[i];
					//mtype = data.type[i];
					//console.log(types);	
					//console.log(title);

					marker[i] = new google.maps.Marker({
						position: new google.maps.LatLng(cord_lat, cord_lng),
						map: gmap,
						title:title,
						animation: google.maps.Animation.DROP,
						//icon: './images/map_icons/'+icons,
						winContent:contentString,
						//winType: mtype
					});
						
					google.maps.event.addListener(marker[i], 'click', function() {	
						//jumpToCompanyPerson(this.pid, this.cid);
						infowindow.setContent('<b class="infoTitle">'+this.title+'</b><br><div class="info-block"><a href="'+this.winContent+'" target="_blank">Profile Link</a></div>');
						infowindow.setPosition(this.position);
						//locationInfoWindow[rope].open(map);
						infowindow.open(gmap,marker[i]);	
					});
				}
				
				if(data.draw_circle) {
					var circleRadius = ($('#m_touchspin_1').val() * 1609.344);
				} else {
					var circleRadius = 20;
				}
				console.log('Radius'+circleRadius);
				searchCircle = new google.maps.Circle({
					strokeColor: '#68a2ff',
					strokeOpacity: 0.5,
					strokeWeight: 1,
					fillColor: '#68a2ff',
					fillOpacity: 0.2,
					map: gmap,
					center: new google.maps.LatLng(data.center_lat, data.center_lng),
					radius: circleRadius
				});
				var newCenterCords = new google.maps.LatLng(data.center_lat, data.center_lng);
				gmap.setCenter(newCenterCords);
				gmap.setZoom(data.zoom);				
				$('#portlet-geo-mapping .portlet-body').css('display', 'default');
			} else {
				//if($('#portlet-geo-mapping').hasClass('m-portlet--collapse')) {
					//portlet_1 = $('#portlet-geo-mapping').mPortlet();		
					//var e = $("#m_portlet_tools_1")
					//portlet_1.expand();
					//portlet_1.collapse();
					$('#portlet-geo-mapping .portlet-body').css('display', 'none');
				//}
				
			}
		} else {
			//mApp.unblock("#portlet-geo-mapping");
			//mApp.unblock('#quickResultsPortlet');		
			//$('#button-view-results').attr('disabled', false);
		}
		
		if(data.showMap) {
			if($('#portlet-geo-mapping').hasClass('m-portlet--collapse')) {
				portlet_1 = $('#portlet-geo-mapping').mPortlet();		
				//var e = $("#m_portlet_tools_1")
				portlet_1.expand();
			}
		}
		
		if(data.found >= 500) {
			$('#button-view-results').attr('disabled', true);
			toastr.warning('Results Exceed 500 - You must reduce the results to under 500 before you can view results', '', {timeOut: 5000});
		} else {
			$('#button-view-results').attr('disabled', false);
		}
		mApp.init();	
	}, "json");	
	
	
	
}

function sendFullSearch() {
	//clearOverlays();
	//searchCircle.setMap(null);
	$('#searchResultsModal').modal('show');
	$('#searchListDisplay').html('');
	executeModalSearch();
}
	
function executeModalSearch() {
	mApp.block("#searchResultsModal .modal-content", {
		overlayColor: "#000000",
		type: "loader",
		state: "success",
		message: "Loading Potential Matches...",
		blockMsgClass: "InfoLoadingProfile"
	});
	
	//var vals = $("#post").find('input,select').serializeArray();
	//vals.push({name: 'nameOfTextarea', value: CKEDITOR.instances.ta1.getData()});

	var formData = $('#searchForForm').serializeArray();
	if($('#includePrevious').is(':checked')) {
		var includePrevs = 1;
	} else {
		var includePrevs = 0;	
	}	
	formData.push({name:'includePrevious', value: includePrevs});
	formData.push({name:'matchAbove', value: $('#matchAbove').val() });
	formData.push({name:'sortResultsBy', value: $('#sortResultsBy').val() });
	$.post('/ajax/searchForMatch.php?action=searchForMatch', formData, function(data) {
		$('#searchListDisplay').html(data.html);	
		$('#display_FoundNumber').html('Found '+data.found+' matches');
		console.log(data);
		mApp.unblock("#searchResultsModal .modal-content");
		mApp.init();	
	}, "json");
}
var portlet_1;
$(document).ready(function(e) {
	$('#PersonsTypes_id').select2({
		theme: "classic",
		placeholder: "Select Record Type(s)",
		allowClear: !0
	});
	$('#Offices_id').select2({
		theme: "classic",
		placeholder: "Select Office(s)",
		allowClear: !0
	});	
	$('#prQuestion_657').select2({
		theme: "classic",
		placeholder: "Select Member Type(s)",
		allowClear: !0
	});
	$('#prQuestion_664').select2({
		theme: "classic",
		placeholder: "Ranking(s)",
		allowClear: !0
	});
	$('#prQuestion_631').select2({
		theme: "classic",
		placeholder: "Income(s)",
		allowClear: !0
	});
	$('#prQuestion_621').select2({
		theme: "classic",
		placeholder: "Height(s)",
		allowClear: !0
	});
	$('#prQuestion_622').select2({
		theme: "classic",
		placeholder: "Weight(s)",
		allowClear: !0
	});
	$('#prQuestion_637').select2({
		theme: "classic",
		placeholder: "Religion(s)",
		allowClear: !0
	});
	$('#prQuestion_624').select2({
		theme: "classic",
		placeholder: "Race(s)",
		allowClear: !0
	});
	$('#State').select2({
		theme: "classic",
		placeholder: "States/Provinces to include",
		allowClear: !0
	});
	$('#Country').select2({
		theme: "classic",
		placeholder: "Countries to include",
		allowClear: !0
	});
	$('#matchAbove').selectpicker();
	$('#sortResultsBy').selectpicker();
	$("#m_touchspin_1").TouchSpin({
		buttondown_class: "btn btn-secondary",
		buttonup_class: "btn btn-secondary",
		min: 25,
		max: 500,
		step: 5,
		decimals: 0,
		boostat: 5,
		maxboostedstep: 5,
		postfix: "miles"
	});
	$(document).on('change','#includePrevious', function() {
		$('#searchListDisplay').html('');
		executeModalSearch();		
	});
	$(document).on('change','#matchAbove', function() {
		$('#searchListDisplay').html('');
		executeModalSearch();
	});
	$(document).on('change','#sortResultsBy', function() {
		$('#searchListDisplay').html('');
		executeModalSearch();
	});

	var mapOptions = {
		isPng: true,
		mapTypeControl: false,
		streetViewControl: false,
		scrollwheel: false,
		center: new google.maps.LatLng(<?php echo $GEO_LOCATION['lat']?>, <?php echo $GEO_LOCATION['lng']?>),    
		zoom: 8
	};
	gmap = new google.maps.Map(document.getElementById("m_chart_latest_trends_map"),mapOptions);
	infowindow = new google.maps.InfoWindow({
		content: '',
		maxWidth: 350
	});
	
	//var circleRadius = ($('#m_touchspin_1').val() * 1609.344);
	searchCircle = new google.maps.Circle({
		strokeColor: '#68a2ff',
		strokeOpacity: 0.5,
		strokeWeight: 1,
		fillColor: '#68a2ff',
		fillOpacity: 0.2,
		map: gmap,
		center: new google.maps.LatLng(<?php echo $GEO_LOCATION['lat']?>, <?php echo $GEO_LOCATION['lng']?>),
		radius: 10
	});
	
	
	$(document).on('click', '.kiss-profile-preview-link', function() {
		var pid = $(this).attr('data-id');		
		var loader = '<div class="text-center" style="margin:20px 0px;"><i class="fa fa-circle-o-notch fa-spin"></i> Loading Profile Preview...</div>';
		
		$('.quickview-block').each(function() {
			$(this).remove();
		}).promise().done(function() {
			$("#preview_"+pid+"_area").html(loader);
			
			//$('#previewPersonModal').modal('show');
			$.post('/ajax/profilePreview.php', {
				pid: pid
			}, function(data) {
				$("#preview_"+pid+"_area").html(data.html);
			}, "json");
		});
	});
	
	<?php if($ADDRESS_ID != ''): ?>
	sendQuickSearch();
	<?php endif; ?>

});
function addAgeToSearch(low, high) {
	console.log(low);
	console.log(high);
	$('#age_seek_floor').val(low);
	$('#age_seek_peak').val(high);
}
function addRankToSearch(userObject) {
	var Values = new Array();
	if(userObject == 'topRanked') {
		Values.push('12');
		Values.push('10');
		Values.push('9');
		Values.push('8');
	}
	$("#prQuestion_664").val(Values).trigger('change');
	
}
function addHeightToSearch(userObject) {
	var Values = new Array();
	if(userObject == 'under5_4') {
		Values.push("Less than 5");
		Values.push("5'0");
		Values.push("5'1");
		Values.push("5'2");
		Values.push("5'3");
		Values.push("5'4");
	} else if(userObject == 'over5_4') {
		Values.push("5'5");
		Values.push("5'6");
		Values.push("5'7");
		Values.push("5'8");
		Values.push("5'9");
		Values.push("5'10");
		Values.push("5'11");
		Values.push("5'12");
		Values.push("6'0");
		Values.push("6'1");
		Values.push("6'2");
		Values.push("6'3");
		Values.push("6'4");
		Values.push("6'5");
		Values.push("6'6");
		Values.push("6'7");
		Values.push("6'8");
		Values.push("6'9");
		Values.push("6'10");
		Values.push("6'11");
		Values.push("6'12");
		Values.push("7'");
		Values.push("More than 7'");
	} else if(userObject == 'over6_0') {
		Values.push("6'0");
		Values.push("6'1");
		Values.push("6'2");
		Values.push("6'3");
		Values.push("6'4");
		Values.push("6'5");
		Values.push("6'6");
		Values.push("6'7");
		Values.push("6'8");
		Values.push("6'9");
		Values.push("6'10");
		Values.push("6'11");
		Values.push("6'12");
		Values.push("7'");
		Values.push("More than 7'");
	} else if(userObject == 'under6_0') {
		Values.push("Less than 5");
		Values.push("5'0");
		Values.push("5'1");
		Values.push("5'2");
		Values.push("5'3");
		Values.push("5'4");
		Values.push("5'5");
		Values.push("5'6");
		Values.push("5'7");
		Values.push("5'8");
		Values.push("5'9");
		Values.push("5'10");
		Values.push("5'11");
		Values.push("6'0");
	} else if(userObject == '54_510') {
		Values.push("5'4");
		Values.push("5'5");
		Values.push("5'6");
		Values.push("5'7");
		Values.push("5'8");
		Values.push("5'9");
		Values.push("5'10");		
	} else if(userObject == '510_6') {
		Values.push("5'10");
		Values.push("5'11");
		Values.push("6'0");
	} else if(userObject == '6_66') {
		Values.push("6'0");
		Values.push("6'1");
		Values.push("6'2");
		Values.push("6'3");
		Values.push("6'4");
		Values.push("6'5");
		Values.push("6'6");		
	}
	$("#prQuestion_621").val(Values).trigger('change');
}
function addWeightToSearch(userObject) {
	var Values = new Array();
	if(userObject == 'under_150') {
		Values.push("Less than 100lbs");
		Values.push("100 - 110");
		Values.push("110 - 120");		
		Values.push("120 - 130");
		Values.push("130 - 140");
		Values.push("140 - 150");		
	} else if(userObject == 'under_200') {
		Values.push("Less than 100lbs");
		Values.push("100 - 110");
		Values.push("110 - 120");		
		Values.push("120 - 130");
		Values.push("130 - 140");
		Values.push("140 - 150");
		Values.push("150 - 160");
		Values.push("160 - 175");
		Values.push("175 - 200");
	} else if(userObject == 'under_250') {
		Values.push("Less than 100lbs");
		Values.push("100 - 110");
		Values.push("110 - 120");		
		Values.push("120 - 130");
		Values.push("130 - 140");
		Values.push("140 - 150");
		Values.push("150 - 160");
		Values.push("160 - 175");
		Values.push("175 - 200");
		Values.push("200 - 225");
		Values.push("225 - 250");
		Values.push("More than 250lbs");
	}
	$("#prQuestion_622").val(Values).trigger('change');
}
function addIncomeToSearch(userObject) {
	console.log(userObject);
	//$('#system_user_id').select2('val', userObject);
	var Values = new Array();
	if(userObject == 'over1M') {
		Values.push('More than $5M');
		Values.push('$1M - $5M');
	} else if(userObject == 'over500') {
		Values.push('More than $5M');
		Values.push('$1M - $5M');
		Values.push('$500K - $1M');
	} else if(userObject == 'over250') {
		Values.push('More than $5M');
		Values.push('$1M - $5M');
		Values.push('$500K - $1M');		
		Values.push('$250K - $500K');		
	} else if(userObject == 'below250') {
		Values.push('$150K - $250K');
		Values.push('$100K - $150K');
		Values.push('Less Than $100k');
	} else if(userObject == 'below150') {
		Values.push('$100K - $150K');
		Values.push('Less Than $100k');
	}
	$("#prQuestion_631").val(Values).trigger('change');
}
function clearOverlays() {
	for (var i = 0; i < marker.length; i++ ) {
		marker[i].setMap(null);
	}
	marker.length = 0;
}
</script>
