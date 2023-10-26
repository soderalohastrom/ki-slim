<?php
include_once("class.record.php");
include_once("class.recordTracking.php");
include_once("class.matching.php");
include_once("class.geocode.php");
include_once("class.encryption.php");
include_once("class.sessions.php");
include_once("./assets/vendors/modules/htmlpurifier-4.10.0/library/HTMLPurifier.auto.php");

$RECORD = new Record($DB);
$MATCHING = new Matching($DB, $RECORD);
$GEOCODE = new GeoCode($DB);
$RTRACK = new recordTracking($DB);
$ENC = new encryption();
$SESSION = new Session($DB, $ENC);

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);


//print_r($_GET);
//print_r($pageParamaters);

function listScore($score) {
	$starList = '';
	for($l=1; $l<11; $l++):
		if($l <= $score):
			$starList .= '&starf;';
		else:
			$starList .= '&star;';
		endif;
	endfor;
	return $starList;
}

function cleanString($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}
//print_r($PARENT_USER_PERMISSIONS);

$INTRO_ID = $pageParamaters['params'][0];
//$TAB = $pageParamaters['params'][1];
$d_sql = "SELECT * FROM PersonsDates WHERE PersonsDates_id='".$INTRO_ID."'";
$d_snd = $DB->get_single_result($d_sql);
$D_DATA = $d_snd;

$thumbSize = 100;

$P1_SQL = "
SELECT 
*
FROM
	Persons
	LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
WHERE 
	Persons.Person_id='".$D_DATA['PersonsDates_participant_1']."'
";
$P1_DTA = $DB->get_single_result($P1_SQL);

$P2_SQL = "
SELECT 
*
FROM
	Persons
	LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
WHERE 
	Persons.Person_id='".$D_DATA['PersonsDates_participant_2']."'
";
$P2_DTA = $DB->get_single_result($P2_SQL);

$p1_name = $RECORD->get_personName($D_DATA['PersonsDates_participant_1']);
$p2_name = $RECORD->get_personName($D_DATA['PersonsDates_participant_2']);			
$label = 'INTRO - '.$p1_name.'|'.$p2_name;
$PAGE_TITLE = json_encode($label." (KISS) Kelleher International Software System");
$RTRACK->updateIntroRecordViewLog($INTRO_ID, $_SESSION['system_user_id']);
?>
<div class="m-content">
<div class="row">
	<div class="col-6">
    
    	<div class="m-portlet m-portlet--tabs">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                    	<span class="m-portlet__head-icon">
                           <i class="fa fa-heart-o"></i>
                        </span>
                        <h3 class="m-portlet__head-text">
                            Date/Meeting Information <small>(<?php echo $INTRO_ID?>)</small>
                        </h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <ul class="nav nav-tabs m-tabs m-tabs-line   m-tabs-line--right m-tabs-line-danger" role="tablist">
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_portlet_tab_1_1" role="tab" aria-expanded="true">Intro Info</a>
                        </li>
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_portlet_tab_1_2" role="tab" aria-expanded="false">
                                Log
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body">
                <div class="tab-content">
                    <div class="tab-pane active" id="m_portlet_tab_1_1" aria-expanded="true">
						
                        <div class="row">
                        	<div class="col-9">                        
                                <div class="row" style="margin-bottom:4px;">
                                    <div class="col-4 text-right"><a href="javascript:;" class="m-link" data-toggle="modal" data-target="#dateStatusModal">Intro Status:</a></div>
                                    <div class="col-8" id="display_PersonsDates_status"><?php echo $MATCHING->get_dateStatusText($D_DATA['PersonsDates_status'])?></div>
                                </div>
                                <div class="row" style="margin-bottom:4px;">
                                    <div class="col-4 text-right"><a href="javascript:;" class="m-link" data-toggle="modal" data-target="#dateMatchmakerModal">Relationship Manager:</a></div>
                                    <div class="col-8"><strong id="display_PersonsDates_assignedTo"><?php echo $RECORD->get_FulluserName($D_DATA['PersonsDates_assignedTo'])?></strong></div>
                                </div>
                                <div class="row" style="margin-bottom:4px;">
                                    <div class="col-4 text-right"><a href="javascript:;" class="m-link" data-toggle="modal" data-target="#dateNDModal">Network Developer:</a></div>
                                    <div class="col-8"><strong id="display_PersonsDates_assignedTo_ND"><?php echo $RECORD->get_FulluserName($D_DATA['PersonsDates_assignedTo_ND'])?></strong></div>

                                </div>
                                <div class="row" style="margin-bottom:4px;">
                                    <div class="col-4 text-right"><a href="javascript:;" class="m-link" data-toggle="modal" data-target="#dateExecutedModal">Next Action On:</a></div>
                                    <div class="col-8"><strong id="display_PersonsDates_dateExecuted"><?php echo (($D_DATA['PersonsDates_dateExecuted'] != 0)? date("m/d/y", $D_DATA['PersonsDates_dateExecuted']):'')?></strong></div>
                                </div>
                                <div class="row" style="margin-bottom:4px;">
                                    <div class="col-4 text-right"><a href="javascript:;" class="m-link" data-toggle="modal" data-target="#dateLocationModal">Intro Location:</a></div>
                                    <div class="col-8"><strong id="display_PersonsDates_locationID"><?php echo $MATCHING->get_dateLocationText($D_DATA['PersonsDates_locationID'])?></strong></div>
                                </div>
                                <div>
                                	<button type="button" class="btn m-btn--pill btn-secondary btn-sm" data-toggle="modal" data-target="#dateNotesModal">Add Date Note <i class="fa fa-plus"></i></button>
    	                            <button type="button" class="btn m-btn--pill btn-outline-danger btn-sm" onclick="deleteIntro()">Delete Intro <i class="fa fa-ban"></i></button>
								</div>
                            </div>
                            <div class="col-3">
                                <div style="margin-bottom:10px;">
                                	<input data-switch="true" data-size="small" type="checkbox" data-id="<?php echo $INTRO_ID?>" data-on-text="Completed" data-off-text="Active" data-on-color="danger" <?php echo (($D_DATA['PersonsDates_isComplete'] == 1)? 'checked':'')?>>
									<?php if($D_DATA['PersonsDates_isComplete'] == 1):?>
                                    <div>
                                    	<?php if($D_DATA['PersonsDates_dateCompleted'] != 0): ?>
                                        <small>Completed on <?php echo date("m/d/y", $D_DATA['PersonsDates_dateCompleted'])?></small>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="m-stack m-stack--ver m-stack--general m-stack--demo">
                                    <div class="m-stack__item m-stack__item--center m-stack__item--middle">
                                        <div style="font-size:2.8em;"><?php echo $MATCHING->get_introScore($INTRO_ID)?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row" style="margin:10px 0px;">
                        	<div class="col-4">
                            	<h4>NOTES</h4>
							</div>
                            <div class="col-8 text-right">&nbsp;</div>
						</div>                                                            
                        <div  id="dateNotesScollArea">    
                            <div class="m-scrollable"data-scrollbar-shown="true" data-scrollable="true" data-max-height="375" style="overflow:auto; height:375px">
                                <?php echo $MATCHING->render_DateNotes($INTRO_ID)?>
                            </div>
                        </div>
                        
                        <hr />
                        <div class="row" style="margin-bottom:4px;">
                            <div class="col-3 text-right">Date Created:</div>
                            <div class="col-9"><strong><?php echo date("m/d/y h:ia", $D_DATA['PersonsDates_dateCreated'])?></strong></div>
                        </div>
                        <div class="row" style="margin-bottom:4px;">
                            <div class="col-3 text-right">Created By:</div>
                            <div class="col-9"><strong><?php echo $RECORD->get_userName($D_DATA['PersonsDates_createdBy'])?></strong></div>
                        </div>
                        
                    </div>
                    
                    <div class="tab-pane" id="m_portlet_tab_1_2" aria-expanded="false">
                    	<div class="m-scrollable"data-scrollbar-shown="true" data-scrollable="true" data-max-height="650" style="overflow:auto; height:650px">
						<?php echo $MATCHING->showDateLogs($INTRO_ID)?> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    </div>
    <div class="col-6">
    	
        <div class="m-portlet">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon">
                            <i class="flaticon-user-ok"></i>
                        </span>
                        <h3 class="m-portlet__head-text">
							<a href="/profile/<?php echo $D_DATA['PersonsDates_participant_1']?>" class="m-link m-link--state m-link--info" target="_new"><?php echo $RECORD->get_personName($D_DATA['PersonsDates_participant_1'])?></a>
							<small><?php echo $RECORD->get_personOffice($D_DATA['PersonsDates_participant_1'])?> | <?php echo $RECORD->get_personType($D_DATA['PersonsDates_participant_1'])?></small>
						</h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <ul class="nav nav-tabs m-tabs m-tabs-line  m-tabs-line--right m-tabs-line-danger" role="tablist">
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_portlet_tab_person_1_1" role="tab" aria-expanded="true">Info</a>
                        </li>
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_portlet_tab_person_1_2" role="tab" aria-expanded="false">Debriefing</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body">
            
            	<div class="tab-content">
                    <div class="tab-pane active" id="m_portlet_tab_person_1_1" aria-expanded="true">
            
                        <div class="row">
                            <div class="col-2">
                                <div style="float:left; margin-right:5px; width:<?php echo $thumbSize?>px; height:<?php echo $thumbSize?>px; background-image:url('<?php echo $RECORD->get_PrimaryImage($D_DATA['PersonsDates_participant_1'])?>'); background-size:cover;">
                                    <img src="/assets/app/media/img/users/filler.png" alt="" style="width:<?php echo $thumbSize?>px; height:<?php echo $thumbSize?>px;">
                                </div>
                            </div>
                            <div class="col-10">
                                
                                <div class="row">
                                    <div class="col-6">
                                
                                        <div class="row" style="margin-bottom:4px;">
                                            <div class="col-5 text-right">Age:</div>
                                            <div class="col-7"><strong><?php echo $RECORD->get_personAge($P1_DTA['DateOfBirth'])?></strong></div>
                                        </div>
                                        <div class="row" style="margin-bottom:4px;">
                                            <div class="col-5 text-right">Gender:</div>
                                            <div class="col-7"><strong><?php echo $P1_DTA['Gender']?></strong></div>
                                        </div>
                                        <div class="row" style="margin-bottom:4px;">
                                            <div class="col-5 text-right">Occupation:</div>
                                            <div class="col-7"><strong><?php echo $P1_DTA['Occupation']?></strong></div>
                                        </div>
                                        <div class="row" style="margin-bottom:4px;">
                                            <div class="col-5 text-right">From:</div>
                                            <div class="col-7"><strong><?php echo $P1_DTA['City']?> <?php echo $P1_DTA['State']?> <?php echo $P1_DTA['Country']?></strong></div>
                                        </div>
                                        
                                    </div>
                                    <div class="col-6">                                
                                        
                                        <div class="row" style="margin-bottom:4px;">
                                            <div class="col-6 text-right"><a href="javascript:openMyDateStatus('1','<?php echo $D_DATA['PersonsDates_participant_1']?>');" class="m-link">Person Status:</a></div>
                                            <div class="col-6"><strong id="display_participantStatus_1"><?php echo $MATCHING->get_dateStatusText($D_DATA['PersonsDates_participant_1_status'])?></strong></div>
                                            <input type="hidden" id="participant_1_status_id" value="<?php echo $D_DATA['PersonsDates_participant_1_status']?>" />
                                        </div>
                                       	<?php if($D_DATA['PersonsDates_participant_1_rank'] != ''):?>
                                        <div class="row" style="margin-top:10px;">
                                        	<div class="col-6">&nbsp;</div>
                                            <div class="col-6 text-right">
                                            	<div style="font-size:1.8em;"><small>Score</small> <strong class="m--font-brand"><?php echo $D_DATA['PersonsDates_participant_1_rank']?></strong></div>
											</div>
										</div>
                                        <?php endif; ?>                                                                                   
                                    </div>
                                </div>                                                                 
                                
                                <div class="btn-group m-btn-group m-btn-group--pill" role="group" aria-label="...">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="openPersonNotes('<?php echo addslashes($RECORD->get_personName($D_DATA['PersonsDates_participant_1']))?>','1','<?php echo $D_DATA['PersonsDates_participant_1']?>')">
                                        <i class="fa fa-edit"></i> Add Person Note
                                    </button>
                                    <a href="/send-email/<?php echo $D_DATA['PersonsDates_participant_1']?>/<?php echo $INTRO_ID?>" class="btn btn-secondary btn-sm">
                                        <i class="fa fa-envelope"></i> Notify
                                    </a>                                    
                                    <a href="/send-email/<?php echo $D_DATA['PersonsDates_participant_1']?>/<?php echo $INTRO_ID?>/<?php echo $D_DATA['PersonsDates_participant_2']?>" class="btn btn-secondary btn-sm">
                                        <i class="fa fa-envelope-o"></i> Send Bio
                                    </a>
                                    <a href="javascript:;" class="btn btn-secondary btn-sm" id="send-debrief-button-1" data-id="participant_1_status_id" data-link="/send-email/<?php echo $D_DATA['PersonsDates_participant_1']?>/<?php echo $INTRO_ID?>/debrief">
                                        <i class="fa fa-heart"></i> Send Debriefing
                                    </a>
                                    <?php if(in_array(59, $PARENT_USER_PERMISSIONS)): ?>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="openDebriefing('<?php echo cleanString($RECORD->get_personName($D_DATA['PersonsDates_participant_1']))?>','1','<?php echo $D_DATA['PersonsDates_participant_1']?>')">
                                        <i class="fa fa-heart-o"></i> Edit Debriefing
                                    </button>
                                    <?php endif; ?>
                                </div>                               
                            </div>
                        </div>
                        <div class="row" style="margin-bottom:4px; margin-top:8px;">
                            <div class="col-3 text-right"><a href="javascript:openMyDateDisposition('1','<?php echo $D_DATA['PersonsDates_participant_1']?>');" class="m-link">Disposition:</a></div>
                            <div class="col-9"><strong id="display_participantDisposition_1"><?php echo $MATCHING->get_dateDisposition($D_DATA['PersonsDates_participant_1_Disposition_id'])?></strong></div>
                            <input type="hidden" id="PersonsDates_participant_1_Disposition_id" value="<?php echo strip_tags($D_DATA['PersonsDates_participant_1_Disposition_id'])?>" />
                        </div>
                        <div class="row" style="margin-bottom:4px; margin-top:8px;">
                            <div class="col-3 text-right"><a href="javascript:openMyDateMMDebriefing('1','<?php echo $INTRO_ID?>','<?php echo $D_DATA['PersonsDates_participant_1']?>');" class="m-link">MM Comments:</a></div>
                            <div class="col-9"><strong id="display_participantMMDebrief_1"><?php echo $D_DATA['PersonsDates_participant_1_MM_debriefing']?></strong></div>
                            <input type="hidden" id="PersonsDates_participant_1_MM_debriefing" value="<?php echo strip_tags($D_DATA['PersonsDates_participant_1_MM_debriefing'])?>" />
                        </div> 
                        
					</div>
                    <div class="tab-pane" id="m_portlet_tab_person_1_2" aria-expanded="true">
                    	<div id="debriefing_1_display">
                        	<?php echo $D_DATA['PersonsDates_participant_1_debrief']?>
                        </div>
                        <input type="hidden" id="PersonsDates_participant_1_rank" value="<?php echo $D_DATA['PersonsDates_participant_1_rank']?>" />
                    </div>
				</div>                    
                                                           
            </div>
        </div>
        
        <div class="m-portlet">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon">
                            <i class="flaticon-user-ok"></i>
                        </span>
                        <h3 class="m-portlet__head-text">
							<a href="/profile/<?php echo $D_DATA['PersonsDates_participant_2']?>" class="m-link m-link--state m-link--info" target="_new"><?php echo $RECORD->get_personName($D_DATA['PersonsDates_participant_2'])?></a>
                            <small><?php echo $RECORD->get_personOffice($D_DATA['PersonsDates_participant_2'])?> | <?php echo $RECORD->get_personType($D_DATA['PersonsDates_participant_2'])?></small>
						</h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <ul class="nav nav-tabs m-tabs m-tabs-line  m-tabs-line--right m-tabs-line-danger" role="tablist">
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_portlet_tab_person_2_1" role="tab" aria-expanded="true">Info</a>
                        </li>
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_portlet_tab_person_2_2" role="tab" aria-expanded="false">Debriefing</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body">
            	<div class="tab-content">
                    <div class="tab-pane active" id="m_portlet_tab_person_2_1" aria-expanded="true">
                        <div class="row">
                            <div class="col-2">
                                <div style="float:left; margin-right:5px; width:<?php echo $thumbSize?>px; height:<?php echo $thumbSize?>px; background-image:url('<?php echo $RECORD->get_PrimaryImage($D_DATA['PersonsDates_participant_2'])?>'); background-size:cover;">
                                    <img src="/assets/app/media/img/users/filler.png" alt="" style="width:<?php echo $thumbSize?>px; height:<?php echo $thumbSize?>px;">
                                </div>
                            </div>
                            <div class="col-10">
                                <div class="row">
                                    <div class="col-6">
                                
                                        <div class="row" style="margin-bottom:4px;">
                                            <div class="col-5 text-right">Age:</div>
                                            <div class="col-7"><strong><?php echo $RECORD->get_personAge($P2_DTA['DateOfBirth'])?></strong></div>
                                        </div>
                                        <div class="row" style="margin-bottom:4px;">
                                            <div class="col-5 text-right">Gender:</div>
                                            <div class="col-7"><strong><?php echo $P2_DTA['Gender']?></strong></div>
                                        </div>
                                        <div class="row" style="margin-bottom:4px;">
                                            <div class="col-5 text-right">Occupation:</div>
                                            <div class="col-7"><strong><?php echo $P2_DTA['Occupation']?></strong></div>
                                        </div>
                                        <div class="row" style="margin-bottom:4px;">
                                            <div class="col-5 text-right">From:</div>
                                            <div class="col-7"><strong><?php echo $P2_DTA['City']?> <?php echo $P2_DTA['State']?> <?php echo $P2_DTA['Country']?></strong></div>
                                        </div>
                                        
                                    </div>
                                    <div class="col-6">                                
                                        <div class="row" style="margin-bottom:4px;">
                                            <div class="col-6 text-right">
                                                <a href="javascript:openMyDateStatus('2','<?php echo $D_DATA['PersonsDates_participant_2']?>');" class="m-link">Person Status:</a>
                                            </div>
                                            <div class="col-6"><strong id="display_participantStatus_2"><?php echo $MATCHING->get_dateStatusText($D_DATA['PersonsDates_participant_2_status'])?></strong></div>
                                            <input type="hidden" id="participant_2_status_id" value="<?php echo $D_DATA['PersonsDates_participant_1_status']?>" />
                                        </div>
                                        <?php if($D_DATA['PersonsDates_participant_2_rank'] != ''):?>
                                        <div class="row" style="margin-top:10px;">
                                        	<div class="col-6">&nbsp;</div>
                                            <div class="col-6 text-right">
                                            	<div style="font-size:1.8em;"><small>Score</small> <strong class="m--font-brand"><?php echo $D_DATA['PersonsDates_participant_2_rank']?></strong></div>
											</div>
										</div>
                                        <?php endif; ?>                                        
                                    </div>
                                </div>
                                <div class="btn-group m-btn-group m-btn-group--pill" role="group" aria-label="...">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="openPersonNotes('<?php echo addslashes($RECORD->get_personName($D_DATA['PersonsDates_participant_2']))?>','2','<?php echo $D_DATA['PersonsDates_participant_2']?>')">
                                        <i class="fa fa-edit"></i> Add Person Note
                                    </button>                                    
                                    <a href="/send-email/<?php echo $D_DATA['PersonsDates_participant_2']?>/<?php echo $INTRO_ID?>" class="btn btn-secondary btn-sm">
                                        <i class="fa fa-envelope"></i> Notify
                                    </a>
                                    <a href="/send-email/<?php echo $D_DATA['PersonsDates_participant_2']?>/<?php echo $INTRO_ID?>/<?php echo $D_DATA['PersonsDates_participant_1']?>" class="btn btn-secondary btn-sm">
                                        <i class="fa fa-envelope-o"></i> Send Bio
                                    </a>
                                    <a href="/send-email/<?php echo $D_DATA['PersonsDates_participant_2']?>/<?php echo $INTRO_ID?>/debrief" class="btn btn-secondary btn-sm">
                                        <i class="fa fa-heart"></i> Send Debriefing
                                    </a>
                                    <?php if(in_array(59, $PARENT_USER_PERMISSIONS)): ?>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="openDebriefing('<?php echo cleanString($RECORD->get_personName($D_DATA['PersonsDates_participant_2']))?>','2','<?php echo $D_DATA['PersonsDates_participant_1']?>')">
                                        <i class="fa fa-heart-o"></i> Edit Debriefing
                                    </button>
                                    <?php endif; ?>
                                </div>                              
                            </div>
                        </div>
                        <div class="row" style="margin-bottom:4px; margin-top:10px;">
                            <div class="col-3 text-right"><a href="javascript:openMyDateDisposition('2','<?php echo $D_DATA['PersonsDates_participant_2']?>');" class="m-link">Disposition:</a></div>
                            <div class="col-9"><strong id="display_participantDisposition_2"><?php echo $MATCHING->get_dateDisposition($D_DATA['PersonsDates_participant_2_Disposition_id'])?></strong></div>
                            <input type="hidden" id="PersonsDates_participant_2_Disposition_id" value="<?php echo strip_tags($D_DATA['PersonsDates_participant_2_Disposition_id'])?>" />
                        </div>
                        <div class="row" style="margin-bottom:4px; margin-top:8px;">
                            <div class="col-3 text-right"><a href="javascript:openMyDateMMDebriefing('2','<?php echo $INTRO_ID?>','<?php echo $D_DATA['PersonsDates_participant_2']?>');" class="m-link">MM Comments:</a></div>
                            <div class="col-9"><strong id="display_participantMMDebrief_2"><?php echo $D_DATA['PersonsDates_participant_2_MM_debriefing']?></strong></div>
                            <input type="hidden" id="PersonsDates_participant_2_MM_debriefing" value="<?php echo strip_tags($D_DATA['PersonsDates_participant_2_MM_debriefing'])?>" />
                        </div>
					</div>
                    <div class="tab-pane" id="m_portlet_tab_person_2_2" aria-expanded="true">
                    	<div id="debriefing_2_display">
                        	<?php echo $D_DATA['PersonsDates_participant_2_debrief']?>
                        </div>
                        <input type="hidden" id="PersonsDates_participant_2_rank" value="<?php echo $D_DATA['PersonsDates_participant_2_rank']?>" />
                    </div>
				</div>                    
                                            
				
                            
            </div>
        </div>
        <div id="CUPID_RECORD_LINK"></div>
    </div>
</div>    


<div class="modal fade" id="dateStatusModal" tabindex="-1" role="dialog" aria-labelledby="dateStatusModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dateStatusModalLabel">Intro Status</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<form id="PersonsDates_status_form" onsubmit="javascript:;">
                <input type="hidden" name="PersonsDates_id" value="<?php echo $INTRO_ID?>" />
				<?php echo $MATCHING->render_dateStatus_radios(array($D_DATA['PersonsDates_status']))?>
                </form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="submitDateStatus()">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="dateMatchmakerModal" role="dialog" aria-labelledby="dateMatchmakerModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dateStatusModalLabel">Assigned Relationship Manager</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<form id="PersonsDates_assignedTo_form" onsubmit="javascript:;">
                <input type="hidden" name="PersonsDates_id" value="<?php echo $INTRO_ID?>" />
					<?php echo $RECORD->render_userSelect($D_DATA['PersonsDates_assignedTo'], 'PersonsDates_assignedTo')?>
                    <span class="m-form__help">
                        Select the user assigned to this introduction.
                    </span>
                </form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="submitDateMatchmaker()">Save</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="dateNDModal" role="dialog" aria-labelledby="dateNDLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dateStatusModalLabel">Assigned Network Developer</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<form id="PersonsDates_assignedTo_ND_form" onsubmit="javascript:;">
                <input type="hidden" name="PersonsDates_id" value="<?php echo $INTRO_ID?>" />
					<?php echo $RECORD->render_userSelect($D_DATA['PersonsDates_assignedTo_ND'], 'PersonsDates_assignedTo_ND')?>
                    <span class="m-form__help">
                        Select the network developer assigned to this introduction.
                    </span>
                </form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="submitDateND()">Save</button>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="dateExecutedModal" role="dialog" aria-labelledby="dateExecutedModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dateExecutedModallLabel">Date of Next Action on the Intro</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<form id="PersonsDates_dateExecuted_form" onsubmit="javascript:;">
                <input type="hidden" name="PersonsDates_id" value="<?php echo $INTRO_ID?>" />
				<div class="form-group">
                    <div class="input-group date" id="m_datetimepicker_3">
                        <input type="text" name="PersonsDates_dateExecuted" class="form-control m-input" readonly value="<?php echo (($D_DATA['PersonsDates_dateExecuted'] != 0)? date("m/d/Y", $D_DATA['PersonsDates_dateExecuted']):'')?>"/>
                        <span class="input-group-addon">
                            <i class="la la-calendar glyphicon-th"></i>
                        </span>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default" onclick="clearNextActionDate()" data-toggle="m-tooltip" title="" data-original-title="Clear the date from the database"><i class="fa fa-times"></i></button>
                        </span>
                    </div>
                    <span class="m-form__help">
                        <small>If you do not know the exact time, estimate as this will be used to calulate when to send clients their debriefing</small>
                    </span>
                </div>
                </form>
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitDateExecutionDateTime();">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="dateLocationModal" role="dialog" aria-labelledby="dateLocationModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dateLocationModalLabel">Intro Location</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<form id="PersonsDates_locationID_form" onsubmit="javascript:;">
                <input type="hidden" name="PersonsDates_id" value="<?php echo $INTRO_ID?>" />
				<div class="form-group m-form__group row">
                    <label class="col-form-label col-lg-3 col-sm-12">
                        Location:
                    </label>
                    <div class="col-lg-9 col-md-9 col-sm-12">
                        <select class="form-control m-select2" id="m_select2_6" name="param" style="width:100%;">
                            <option></option>
                        </select>
                    </div>
                </div>	
                </form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="submitDateLocation();">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="datePersonStatusModal" tabindex="-1" role="dialog" aria-labelledby="dateStatusModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dateStatusModalLabel">Person Intro Status</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<form id="PersonsDates_participant_status_form" onsubmit="javascript:;">
                <input type="hidden" name="PersonsDates_id" value="<?php echo $INTRO_ID?>" />
                <input type="hidden" name="ParticpantNumber" id="ParticpantNumber" value="" />
				<?php echo $MATCHING->render_dateStatus_radios(array())?>
                </form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="submitMyDateStatus()">Save</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="datePersonDispoModal" tabindex="-1" role="dialog" aria-labelledby="datePersonDispoModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="datePersonDispoModalLabel">Person Intro Disposition</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<form id="PersonsDates_participant_dispo_form" onsubmit="javascript:;">
                <input type="hidden" name="PersonsDates_id" value="<?php echo $INTRO_ID?>" />
                <input type="hidden" name="ParticpantNumber" id="DispoParticpantNumber" value="" />
				<?php echo $MATCHING->render_dateDispo_radios(array())?>
                </form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="submitMyDateDispo()">Save</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="datePersonMMDebriefModal" tabindex="-1" role="dialog" aria-labelledby="datePersonMMDebriefModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="datePersonMMDebriefModalLabel">Person Intro MM Comments</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<form id="PersonsDates_participant_mmdebrief_form" onsubmit="javascript:;">
                <input type="hidden" name="PersonsDates_id" value="<?php echo $INTRO_ID?>" />
                <input type="hidden" name="ParticpantNumber" id="MMDebriefParticpantNumber" value="" />
                <?php echo $SESSION->renderToken()?>
				<div class="mmdebrief-summernote"></div>
                </form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="submitMyDateDebrief()">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="dateNotesModal" tabindex="-1" role="dialog" aria-labelledby="dateNotesModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dateNotesModalLabel">Internal Intro Note</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<form id="Dates_notes_form" onsubmit="javascript:;">
                <input type="hidden" name="PersonsDates_id" value="<?php echo $INTRO_ID?>" />
                <input type="hidden" name="PersonsDatesNotes_createdBy" value="<?php echo $_SESSION['system_user_id']?>" />
                <?php echo $SESSION->renderToken()?>
                <div class="datenotes-summernote"></div>
                </form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn m-btn btn-primary " id="button_dateNoteSave" onclick="saveDateNote()">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="datePersonNotesModal" tabindex="-1" role="dialog" aria-labelledby="datePersonNotesModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="datePersonNotesModalLabel">Intro Note for <span id="PersonNoteNameDisplay">{PERSON_NAME}</span></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<form id="DatesPersons_notes_form" onsubmit="javascript:;">
                <input type="hidden" name="PersonsDates_id" value="<?php echo $INTRO_ID?>" />
                <input type="hidden" name="PersonsDatesNotes_createdBy" value="<?php echo $_SESSION['system_user_id']?>" />
                <input type="hidden" name="PersonsDatesNotes_number" id="PersonsDatesNotes_number" value="" />
                <input type="hidden" name="PersonsDatesNotes_personID" id="PersonsDatesNotes_personID" value="" />
                <?php echo $SESSION->renderToken()?>
                
                <div class="form-group m-form__group row">
                    <label class="col-lg-3 col-form-label">
                        Note Type:
                    </label>
                    <div class="col-lg-6">
                    	<select name="PersonDatesNoteType" id="PersonDatesNoteType" class="form-control m-input">                      
                            <option value="">--- select note type ---</option>
                            <option value="Left Message">Left Message</option>
                            <option value="No Answer - No Message">No Answer - No Message</option>
                            <option value="Spoke to Member/Client/Rep">Spoke to Member/Client/Rep</option>
                            <option value="Client Member Declined">Client Member Declined</option>
                            <option value="Client/Member Accepted">Client/Member Accepted</option>
                        </select>
                    </div>
                </div>
                
                <div class="datepersonnotes-summernote"></div>
                </form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn m-btn btn-primary " id="button_dateNoteSave" onclick="savePersonNotes()">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="datePersonDebriefingModal" tabindex="-1" role="dialog" aria-labelledby="datePersonDebriefingModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="datePersonDebriefingModalLabel">Intro Debriefing for <span id="PersonDebriefingNameDisplay">{PERSON_NAME}</span></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            	<form id="DatesPersons_dbriefing_form" onsubmit="javascript:;">
                <input type="hidden" name="PersonsDates_id" value="<?php echo $INTRO_ID?>" />
                <input type="hidden" name="Dates_dbriefing_number" id="Dates_dbriefing_number" value="" />
                <input type="hidden" name="Dates_dbriefing_personID" id="Dates_dbriefing_personID" value="" />
                <?php echo $SESSION->renderToken()?>
                
                
                <div class="debriefing-summernote"></div>
                
                <div class="form-group m-form__group row" style="margin-top:10px;">
                    <label class="col-lg-3 col-form-label">
                        Intro Rank:
                    </label>
                    <div class="col-lg-6">
                    	<select name="PersonsDates_participant_rank" id="PersonsDates_participant_rank" class="form-control m-input">
                        	<?php for($i=1; $i<11; $i++): ?>
                            <option value="<?php echo $i?>">(<?php echo $i?>)&nbsp;<?php echo listScore($i)?> </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                </form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn m-btn btn-primary " id="button_dateNoteSave" onclick="saveDebriefing()">Save</button>
			</div>
		</div>
	</div>
</div>


</div>

<script>
$(document).ready(function(e) {
	document.title = <?php echo $PAGE_TITLE?>;
    $("[data-switch=true]").bootstrapSwitch({
		onSwitchChange: function(event, state) {
			var dateID = $(this).attr('data-id');
			if(state) {
				var dateState = 1;
			} else {
				var dateState = 0;
			}
			console.log(dateID);
			$.post('/ajax/intros.php?action=setDateCompleted', {
				intro:		dateID,
				dateState:	dateState
			}, function(data) {				
				toastr.success('Intro Updated', '', {timeOut: 5000});
				//$('blanaceFor_'+data.pay_id).html(data.balance);
			}, "json");
		}	
	});
	$("#PersonsDates_assignedTo").select2({
		placeholder: "Select a user"	
	});
	
	$(".datenotes-summernote").summernote({
    	height: 250
    });
	$(".datepersonnotes-summernote").summernote({
		height: 250
	});
	$(".debriefing-summernote").summernote({
		height: 350
	});
	$(".mmdebrief-summernote").summernote({
		height: 350
    });
	
	$("#m_datetimepicker_3").datepicker({
		format: "mm/dd/yyyy",
		showMeridian: !0,
		todayHighlight: !0,
		autoclose: !0,
		pickerPosition: "bottom-left"
	});
	
	$("#m_select2_6").select2({
		theme: "classic",
		placeholder: "Search for locations",
		allowClear: !0,
		ajax: {
			url: "/ajax/intros.php?action=searchLocations",
			dataType: "json",
			delay: 250,
			data: function(e) {
				return {
					q: e.term,
					page: e.page
				}
			},
			processResults: function(e, t) {
				return t.page = t.page || 1, {
					results: e.items,
					pagination: {
						more: 30 * t.page < e.total_count
					}
				}
			},
			cache: !0
		},
		escapeMarkup: function(e) {
			return e
		},
		minimumInputLength: 1,
		templateResult: function(e) {
			if (e.loading) return e.text;
			var t = "<div class='select2-result-repository clearfix'><div class='select2-result-repository__meta'><div class='select2-result-repository__title'>" + e.name + "</div>";
			//return (t += "<div class='select2-result-repository__description'>"+e.city+" "+e.state+" "+e.nat+"</div>"), t += "<div class='select2-result-repository__statistics'><div class='select2-result-repository__forks'><i class='fa fa-phone'></i> " + e.ph + " Forks</div><div class='select2-result-repository__stargazers'><i class='fa fa-star'></i> " + e.stargazers_count + " Stars</div><div class='select2-result-repository__watchers'><i class='fa fa-eye'></i> " + e.watchers_count + " Watchers</div></div></div></div>"
			return (t += "<div class='select2-result-repository__description'>"+e.city+" "+e.state+" "+e.nat+"</div>");
		},
		templateSelection: function(e) {
			return e.name || e.text
		}
	});
	
	$(document).on('click', '#send-debrief-button-1', function() {
		var field = $(this).attr('data-id');
		var statusID = $('#'+field).val();	
		//alert(statusID);
		if((statusID != 4) && (statusID != 6)) {
			alert('WARNING: You are attempting to send an debriefing email to a person who is not in the Match Connected or Met/Need Feedback status and will not be able to view the debriefing form.');
		} else {
			var url = $(this).attr('data-link');	
			document.location.href=url;
		}
	});
});
function clearNextActionDate() {
	$.post('/ajax/intros.php?action=clearNextActionDate', {
		did: '<?php echo $INTRO_ID?>'
	}, function(data) {
		//document.location.href='/profile/'+data.return_to_id	
		document.location.reload(true);		
	}, "json");	
}
function deleteIntro() {
	var choice = confirm('Are you sure you want to delete this introduction from the system? WARNING: this action cannot be undone.');
	if (choice) {
		$.post('/ajax/intros.php?action=removeIntro', {
			did: '<?php echo $INTRO_ID?>'
		}, function(data) {
			document.location.href='/profile/'+data.return_to_id			
		}, "json");		
	}	
}
function openDebriefing(fullName, number, pid) {
	$('#datePersonDebriefingModal').modal('show');	
	$('#PersonDebriefingNameDisplay').html(fullName);
	$('#Dates_dbriefing_number').val(number);
	$('#Dates_dbriefing_personID').val(pid);
	var editorBody = $('#debriefing_'+number+'_display').html();
	$('.debriefing-summernote').summernote('code', editorBody);
	var dateRank = $('#PersonsDates_participant_'+number+'_rank').val();
	$('#PersonsDates_participant_rank').val(dateRank);	
}
function saveDebriefing() {
	var formData = $('#DatesPersons_dbriefing_form').serializeArray();	
	var textareaValue = $('.debriefing-summernote').summernote('code');
	//var textAreaString = $($('.debriefing-summernote').summernote('code')).text();
	formData.push({name: 'noteTextArea', value: textareaValue});
	//if (textAreaString != '') {
	//formData.push({name: 'noteTextArea', value: textareaValue});
	console.log(formData);
	$.post('/ajax/intros.php?action=dateSaveDebriefing', formData, function(data) {
		//console.log(data);
		document.location.reload(true);
	},"json");
}
function openPersonNotes(fullName, number, pid) {
	$('#datePersonNotesModal').modal('show');	
	$('#PersonNoteNameDisplay').html(fullName);
	$('#PersonsDatesNotes_number').val(number);
	$('#PersonsDatesNotes_personID').val(pid);
}
function savePersonNotes() {
	var formData = $('#DatesPersons_notes_form').serializeArray();	
	var textareaValue = $('.datepersonnotes-summernote').summernote('code');
	var textAreaString = $($('.datepersonnotes-summernote').summernote('code')).text();
	formData.push({name: 'noteTextArea', value: textareaValue});
	//if (textAreaString != '') {
		formData.push({name: 'noteTextArea', value: textareaValue});
		$.post('/ajax/intros.php?action=dateSavePersonNote', formData, function(data) {
			console.log(data);
			document.location.reload(true);
		},"json");
	//} else {
		//alert('You must enter a note');
	//}
	
	
}
function loadNotes() {
	$.post('/ajax/intros.php?action=getDateNotes', {
		PersonsDates_id: '<?php echo $INTRO_ID?>'
	}, function(data) {
		//console.log(data);
		$('#dateNotesScollArea').html(data.html);		
	},"json");
}
function saveDateNote() {
	var textareaValue = $('.datenotes-summernote').summernote('code');
	var formData = $('#Dates_notes_form').serializeArray();
	var textAreaString = $($('.datenotes-summernote').summernote('code')).text();
	$('#button_dateNoteSave').addClass('m-loader m-loader--light m-loader--left');
	$('#button_dateNoteSave').prop('disabled', true);
	console.log(textAreaString);
	if (textAreaString != '') {
		formData.push({name: 'noteTextArea', value: textareaValue});
		$.post('/ajax/intros.php?action=dateSaveNote', formData, function(data) {
			//console.log(data);
			document.location.reload(true);
		},"json");
	} else {
		alert('You must enter a note');
	}
}

function openMyDateMMDebriefing(number, intro, person) {
	$('#datePersonMMDebriefModal').modal('show');
	$('#MMDebriefParticpantNumber').val(number);
	$.post('/ajax/intros.php?action=dateGetMMDebrief', {
		ParticpantNumber: number,
		PersonsDates_id: intro
	}, function(data) {	
		//var mmbody = $('#PersonsDates_participant_'+number+'_MM_debriefing').val();
		var mmbody = data.mmbody;
		console.log(mmbody);
		$('.mmdebrief-summernote').summernote('code', mmbody);
	}, "json");
	
}
function submitMyDateDebrief() {
	var textareaValue = $('.mmdebrief-summernote').summernote('code');
	var formData = $('#PersonsDates_participant_mmdebrief_form').serializeArray();
	//var textAreaString = $($('.mmdebrief-summernote').summernote('code')).text();
	//$('#button_dateNoteSave').addClass('m-loader m-loader--light m-loader--left');
	//$('#button_dateNoteSave').prop('disabled', true);
	//console.log(textareaValue);
	if (textareaValue != '') {
		formData.push({name: 'noteTextArea', value: textareaValue});
		$.post('/ajax/intros.php?action=dateSaveMMDebrief', formData, function(data) {
			//console.log(data);
			//document.location.reload(true);
			$('#display_participantMMDebrief_'+data.number).html(data.noteBody);
			$('#PersonsDates_participant_'+data.number+'_MM_debriefing').val(data.noteBody);
			$('#datePersonMMDebriefModal').modal('hide');
			$('.mmdebrief-summernote').summernote('code', '');
		},"json");
	} else {
		alert('You must enter a comment/debriefing');
	}	
}

function openMyDateDisposition(number, person) {
	$('#datePersonDispoModal').modal('show');
	$('#DispoParticpantNumber').val(number);

	$('#PersonsDates_participant_dispo_form input[type="radio"]').each(function() {
		var radioVal = $(this).val();
		var myValue = $('#PersonsDates_participant_'+number+'_Disposition_id').val();
		console.log($(this).val()+'|'+myValue);
		if($(this).val() == eval(myValue)) {
			console.log('Setting Check');
			$(this).prop('checked', true);
		}
	});
}
function submitMyDateDispo() {
	var formData = $('#PersonsDates_participant_dispo_form').serializeArray();
	$.post('/ajax/intros.php?action=setDatePersonDispo', formData, function(data) {
		console.log(data);
		$('#datePersonDispoModal').modal('hide');
		$('#display_participantDisposition_'+data.number).html(data.html);
		$('#PersonsDates_participant_'+data.number+'_Disposition_id').val(data.data);
		toastr.success('Person Intro Disposition Updated', '', {timeOut: 5000});		
	}, "json");	
	
}
function openMyDateStatus(number, person) {
	$('#datePersonStatusModal').modal('show');
	$('#ParticpantNumber').val(number);
	$('#PersonsDates_participant_status_form input[type="radio"]').each(function() {
		
		var radioVal = $(this).val();
		var myValue = $('#participant_'+number+'_status_id').val();
		console.log($(this).val()+'|'+myValue);
		if($(this).val() == eval(myValue)) {
			console.log('Setting Check');
			$(this).prop('checked', true);
		}
	});
}
function submitMyDateStatus() {
	var formData = $('#PersonsDates_participant_status_form').serializeArray();
	$.post('/ajax/intros.php?action=setDatePersonStatus', formData, function(data) {
		console.log(data);
		$('#datePersonStatusModal').modal('hide');
		$('#display_participantStatus_'+data.number).html(data.html);
		$('#participant_'+data.number+'_status_id').val(data.data);
		toastr.success('Person Intro Status Updated', '', {timeOut: 5000});		
	}, "json");	
}
function submitDateStatus() {
	var formData = $('#PersonsDates_status_form').serializeArray();
	$.post('/ajax/intros.php?action=setDateStatus', formData, function(data) {
		//console.log(data);
		$('#dateStatusModal').modal('hide');
		$('#display_PersonsDates_status').html(data.html);
		toastr.success('Intro Status Updated', '', {timeOut: 5000});
		
	}, "json");
}
function submitDateMatchmaker() {
	var formData = $('#PersonsDates_assignedTo_form').serializeArray();
	$.post('/ajax/intros.php?action=setDateMMaker', formData, function(data) {
		//console.log(data);
		$('#dateMatchmakerModal').modal('hide');
		$('#display_PersonsDates_assignedTo').html(data.html);
		toastr.success('Relationship Manager Assigned', '', {timeOut: 5000});
		
	}, "json");
}
function submitDateND() {
	var formData = $('#PersonsDates_assignedTo_ND_form').serializeArray();
	$.post('/ajax/intros.php?action=setDateND', formData, function(data) {
		//console.log(data);
		$('#dateMatchmakerModal').modal('hide');
		$('#display_PersonsDates_assignedTo_ND').html(data.html);
		toastr.success('Network Developer Assigned', '', {timeOut: 5000});
		
	}, "json");
}
function submitDateExecutionDateTime() {
	var formData = $('#PersonsDates_dateExecuted_form').serializeArray();
	$.post('/ajax/intros.php?action=setDateExecute', formData, function(data) {
		//console.log(data);
		$('#dateExecutedModal').modal('hide');
		$('#display_PersonsDates_dateExecuted').html(data.html);
		toastr.success('Next Action Date Set', '', {timeOut: 5000});
		
	}, "json");
}
function submitDateLocation() {
	var formData = $('#PersonsDates_locationID_form').serializeArray();
	var textValue = $("#m_select2_6").select2('data');
	console.log(textValue);
	formData.push({name: 'nameLocation', value: textValue[0].name});
	$.post('/ajax/intros.php?action=setDateLocation', formData, function(data) {
		//console.log(data);
		$('#dateLocationModal').modal('hide');
		$('#display_PersonsDates_locationID').html(data.html);
		toastr.success('Intro Location Set', '', {timeOut: 5000});		
	}, "json");
}
function openCUPID_Daterecord(dateID) {
	var cupidWindow = window.open('https://www.kelleher-international.com/admin/pop.date_engine3.php?DateID='+dateID, 'cupidDateWindow', 'width=900,height=800');			
}

function stripHTML(dirtyString) {
  var container = document.createElement('div');
  var text = document.createTextNode(dirtyString);
  container.appendChild(text);
  return container.innerHTML; // innerHTML will be a xss safe string
}
</script>


