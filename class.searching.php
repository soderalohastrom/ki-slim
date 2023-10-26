<?php
/*! \class Searching class.searching.php "class.search.php"
 *  \brief used to render all of the inline editable form elements.
 */
class Searching {
	/*! \fn obj __constructor($DB)
		\brief searching class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB, $RECORD) {
		$this->db 			= $DB;
		$this->record		= $RECORD;
		$this->skippedUsers = array(186799);
		$this->expire		= 5;				// Pre-Fetch Potential Matches Expirese after X days //
		$this->display		= 20;				// Number of records to display in an automated search result //
	}
	
	function getCompletionStatus($personID) {
		$sql = "SELECT * FROM PersonsPrefs WHERE Person_id='".$personID."'";	
		$snd = $this->db->get_single_result($sql);
		//print_r($snd);
		$q1 = $snd['prefQuestion_age_floor'];
		$q2 = $snd['prefQuestion_Gender'];
		$q3 = $snd['prefQuestion_Pref_MemberTypes'];
		//$q4 = $snd['prefQuestion_Pref_Offices'];
		
		$aSQL = "SELECT * FROM Addresses WHERE Person_id='".$personID."' ORDER BY isPrimary DESC LIMIT 1";
		$aSND = $this->db->get_single_result($aSQL);
		$t = 0;
		if($aSND['GeoLocationStatus'] != 200) {
			$temp = -1;
			$t = $temp;
		}
		if($q1 != '') {
			$temp = $t + 34;
			$t = $temp;
		}
		if($q2 != '') {
			$temp = $t + 33;
			$t = $temp;
		}
		if($q3 != '') {
			$temp = $t + 33;
			$t = $temp;
		}
		/*
		if($q4 != '') {
			$temp = $t + 25;
			$t = $temp;
		}
		*/
		return $t;		
	}
	
	function preSearch($personID) {
		$ps_sql = "SELECT * FROM PersonsPreFetchPotential WHERE Person_id='".$personID."'";
		$ps_snd = $this->db->get_single_result($ps_sql);
		if(isset($ps_snd['empty_result'])) {
			return false;
		} else {
			$now 	=	time();
			$then 	= 	$ps_snd['FetchDate'];
			if (($now - $then) > (86400 * $this->expire)) {
				return false;
			} else {
				//echo "SETTING LOAD TIME:".date("m/d/y h:ia", $ps_snd['FetchDate'])."<br>";
				$this->resultGenerated = $ps_snd['FetchDate'];
				return json_decode($ps_snd['FetchData'], true);				
			}
		}
		
	}
	
	function basicPreSearch($personID) {
		if($_POST['skp'] == 1) {
			$sResults = false;	
		} else {
			$sResults = $this->preSearch($personID);
		}
		if($sResults) {
			//echo "SAVED SEARCH";
			$send = $sResults;
			$results = $send;	
		} else {
			$sql = "SELECT * FROM PersonsPrefs WHERE Person_id='".$personID."'";	
			$snd = $this->db->get_single_result($sql);
			//print_r($snd);
			$q1 = explode("|", $snd['prefQuestion_age_floor']);
			$q2 = $snd['prefQuestion_Gender'];
			$q3 = $snd['prefQuestion_Pref_MemberTypes'];
			//$q4 = $snd['prefQuestion_Pref_Offices'];
			
			$ad_sql = "SELECT * FROM Addresses WHERE Person_id='".$personID."' AND Addresses.isPrimary='1'";
			$ad_dta = $this->db->get_single_result($ad_sql);
			$lat = $ad_dta['Lattitude'];		
			$lng = $ad_dta['Longitude'];
			
			$select[] = 'Persons.Person_id';
			$select[] = 'Persons.FirstName';
			$select[] = 'Persons.LastName';
			$select[] = 'Persons.DateOfBirth';
			$select[] = 'Persons.DateUpdated';
			$select[] = 'Persons.PersonsTypes_id';
			$select[] = 'Persons.Assigned_userID';
			$select[] = 'Persons.Matchmaker_id';
			$select[] = 'Persons.Matchmaker2_id';
			$select[] = 'PersonsImages.PersonsImages_path';
			$select[] = 'PersonsTypes_color';
			$select[] = 'Addresses.City';
			$select[] = 'Addresses.State';
			$select[] = 'Addresses.Lattitude';
			$select[] = 'Addresses.Longitude';
			$select[] = 'Addresses.GeoLocationStatus';
			if($ad_dta['GeoLocationStatus'] == 200) {
				$select[] = "(((acos(sin((".$lat."*pi()/180)) * sin((Addresses.Lattitude*pi()/180)) + cos((".$lat."*pi()/180)) * cos((Addresses.Lattitude*pi()/180)) * cos(((".$lng." - Addresses.Longitude)*pi()/180))))*180/pi())*60*1.1515) as distance";
				$order[] = 'distance ASC';
			}
			$order[] = 'Persons.DateUpdated DESC'; 				//LAST UPDATE
			$order[] = 'PersonsProfile.prQuestion_664 DESC'; 	//RANKING
	
			$SQL = "
	SELECT 
		".implode(",", $select)."	
	FROM 
		Persons 
		INNER JOIN Offices ON Offices.Offices_id=Persons.Offices_id
		LEFT JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'
		INNER JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
		INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
		INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id
	WHERE
		1
	AND 
		Persons.PersonsStatus_id='1'
	AND
		Persons.PersonsTypes_id IN (".str_replace("|",",", $q3).")
	AND
		Persons.Gender='".$q2."'
	AND
		DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), Persons.DateOfBirth)), '%Y')+0 BETWEEN " . $q1[1] . " AND " . $q1[0] ."
	AND
		PersonsImages.PersonsImages_path != ''
	AND
		Addresses.Postal != ''
	AND
		Persons.Assigned_userID NOT IN (".implode(",", $this->skippedUsers).")
	";
	$SQL .= "
	GROUP BY
		Persons.Person_id
	HAVING
		distance != 0
	ORDER BY
		".implode(", ", $order)."
	LIMIT 500
	";
			//echo $SQL;
			$send = $this->db->get_multi_result($SQL);	
			//print_r($send);
			//echo "PARSING RESULTS:";
			$results = $this->parseSearchResults($personID, $send);
			$this->resultGenerated = time();		
		}		
		$this->displayResultsList($personID, $results);		
	}
	
	
	
	function parseSearchResults($searchForID, $searchData, $skipLog=false) {
		$sql = "SELECT * FROM PersonsPrefs WHERE Person_id='".$searchForID."'";	
		$snd = $this->db->get_single_result($sql);
		
		$pq_sql = "SELECT * FROM PrefQuestions WHERE PrefQuestions_cat='Profile'";
		$pq_snd = $this->db->get_multi_result($pq_sql);
		$pq_total = count($pq_snd);
		$filledCount = 0;
		foreach($pq_snd as $pq_dta):
			$prefField = $pq_dta['PrefQuestion_mappedField'];
			if($snd[$prefField] != '') {
				$filledCount++;
			}
		endforeach;
			
		
		foreach($searchData as $data):
			$pid = $data['Person_id'];
			if($data['PersonsImages_path'] == '') {
				$imgPath = $this->record->get_defaultImage($pid);
			} else {
				$imgPath = "/client_media/".$this->record->get_image_directory($pid)."/".$pid."/".$data['PersonsImages_path'];
			}
			$score = $this->scoreMatch($searchForID, $pid);
			$results[$pid] = array(
				'pid'		=>	$pid,
				'img'		=>	$imgPath,
				'loc'		=>	$data['City'].', '.$data['State'],
				'nme'		=>	$data['FirstName'].' '.$data['LastName'],
				'age'		=>	$this->record->get_personAge($data['DateOfBirth'], true),
				'scr'		=>	$score,
				'dis'		=>	$data['distance'],
				'last'		=>	(($data['DateUpdated'] == 0)? 'None':date("m/d/y h:ia", $data['DateUpdated'])),	
				'color'		=>	$data['PersonsTypes_color'],							
				'type'		=>	$data['PersonsTypes_id'],
				'ttext'		=>	$this->record->get_personType($pid),
				'owner'		=>	(($data['PersonsTypes_id'] == 3)? $this->record->get_userName($data['Assigned_userID']):$this->record->get_userName($data['Matchmaker_id']))				
			);
			/*
			if($filledCount > 2) {
				$sorting[$pid] = $score;
				$stortType = 'down';
			} else {
				$sorting[$pid] = $data['distance'];
				$stortType = 'up';
			}
			*/
			$sorting[$pid] = $data['distance'];
			$stortType = 'up';
		endforeach;
		if($stortType == 'down') {
			arsort($sorting, SORT_NUMERIC);
		} else {
			asort($sorting, SORT_NUMERIC);
		}
		//print_r($sorting);
		$keys = array_keys($sorting);
		//print_r($keys);
		$index = 0;
		foreach($keys as $key):
			$finalData[] = $results[$key];
			if($index <= $this->display) {
				$savedData[] = $results[$key];
			}
			$index++;
		endforeach;		
		//print_r($finalData);
		
		if(!$skipLog) {
			$ps_sql = "SELECT * FROM PersonsPreFetchPotential WHERE Person_id='".$searchForID."'";
			$ps_snd = $this->db->get_single_result($ps_sql);
			if(isset($ps_snd['empty_result'])) {
				$ins_sql = "INSERT INTO PersonsPreFetchPotential (Person_id) VALUES('".$searchForID."')";
				$ins_snd = $this->db->mysqli->query($ins_sql);
			}
			
			$upd_sql = "UPDATE PersonsPreFetchPotential SET FetchDate='".time()."', FetchData='".$this->db->mysqli->escape_string(json_encode($savedData))."' WHERE Person_id='".$searchForID."'";
			//echo $upd_sql."<br>\n";
			$upd_snd = $this->db->mysqli->query($upd_sql);
		}
				
		return $finalData;
	}
	
	function displayResultsList($searchForID, $searchData, $totalDisplay=20) {
		$now = time();
		$then = $this->resultGenerated;
		//$interval = date_diff($then, $now);
		//$daysOld = $interval;
		//$daysOld = $interval->format('%h Hours %i Minutes ago');
		//$daysOld = date_format($interval, '%h Hours %i Minutes ago');
		//$daysOld = $this->time_diff($now, $then);
		if($totalDisplay == 20):
		?><div class="text-right" style="font-size:.75em; padding:5px; background-color:#f9f9f9; margin-bottom:4px;">Generated <?php echo date("m/d/y h:ia", $then)?></div><?php
		endif;
		?><ul class="list-group"><?php
		$count = 0;
		foreach($searchData as $data):
			if($count < $totalDisplay):
			$pid = $data['pid'];
			$matched = $this->alreadyMatched($searchForID, $pid)
			?>
            <li class="list-group-item" style="background-color:<?php echo (($matched != -1)? '#ffe8e8':'#fff')?>;">
            	<div class="row">
                	<div class="col-sm-3">
                		<div style="background-image:url('<?php echo $data['img']?>'); background-size:cover;">
                        <img src="/assets/app/media/img/users/filler.png" style="height:50px; width:50px;" class="kiss-profile-preview-link" data-id="<?php echo $pid?>">
                        </div>
                    </div>
                    <div class="col-sm-6" style="padding-left:0px;">
                    	<div style="float:right;"><a href="/profile/<?php echo $pid?>" target="_blank" class="m-link" style="color:#7b7e8a;"><i class="la la-external-link-square"></i></a></div>						
						<?php if($data['type'] == 3): ?>
                        	<span class="truncate" style="width:95%;"><a href="/profile/<?php echo $pid?>" data-container="body" data-toggle="m-popover" data-placement="top" data-content="<?php echo $data['ttext']?> | <?php echo round($data['dis'], 1)?> miles | Owner:<?php echo $data['owner']?>"><span class="m--font-danger"><?php echo $data['pid']?></span> | <span class="m--font-metal"><?php echo $data['owner']?></span> </a></span>
                        <?php else: ?>
                    		<?php if($data['vip'] == 1): ?>
                            	<span class="truncate" style="width:95%;"><a href="/profile/<?php echo $pid?>" data-container="body" data-toggle="m-popover" data-placement="top" data-content="<?php echo $data['ttext']?> | <?php echo round($data['dis'], 1)?> miles | Owner:<?php echo $data['owner']?> | HIGH PRIORITY CLIENT"><span class="m--font-primary"><?php echo $data['pid']?> (HPC)</span> | <span class="m--font-metal"><?php echo $data['owner']?></span> </a></span>
                            <?php else: ?>
		                        <span class="truncate" style="width:95%;"><a href="/profile/<?php echo $pid?>" data-container="body" data-toggle="m-popover" data-placement="top" data-content="<?php echo $data['ttext']?> | <?php echo round($data['dis'], 1)?> miles<?php echo ((trim($data['owner']) != '')? ' | Owner:'.$data['owner']:'')?>"><span class="m--font-<?php echo $data['color']?>"><?php echo $data['nme']?></span> </a></span>
							<?php endif; ?>                                
                        <?php endif; ?>
						<div class="truncate" style="margin-top:-5px;">
							<small><span class="m--font-danger"><?php echo $data['scr']?>%</span> | <?php echo $data['loc']?></small>                                
						</div>
                        <div class="truncate" style="margin-top:-5px;"><small><?php echo $this->record->get_date_diff($data['last'], time())?></small></div>
                    </div>
                    <div class="col-sm-3">
                    	<div><?php echo $data['age']?>yrs</div>
                        <?php if($matched != -1):?>
                        <div class="text-right m--font-danger"><i class="fa fa-heart" data-toggle="m-popover" data-placement="top" data-content="This person has already been matched with this record."></i></div>
                        <?php endif; ?>
                    </div>
                </div>
			</li>                   
			<?php
			$count ++;
			endif;						
		endforeach;	               
		?></ul>
		<script>
		$(document).ready(function(e) {
            $(document).on('click', '.kiss-profile-preview-link', function() {
				var pid = $(this).attr('data-id');
				//alert(pid);
				$('#previewPersonModalLabel').html('');
				$('#previewPersonModalLabel .modal-body').html('');
                mApp.block("#previewPersonModal .modal-content", {
                    overlayColor: "#000000",
                    type: "loader",
                    state: "success",
                    message: "Loading Profile Preview...",
					blockMsgClass: "InfoLoadingProfile"
				});
				$('.blockMsg').css('top', '40%');
				$('.blockMsg').css('left', '40%');
				$('#previewPersonModal').modal('show');
				$.post('/ajax/profilePreview.php', {
					pid: pid,
					fromPID: <?php echo $searchForID?>
				}, function(data) {
					$('#previewPersonModal .modal-body').html(data.html);
					mApp.unblock("#previewPersonModal .modal-content");
					mApp.init();
				}, "json");
			});
        });		
		</script>
		<?php
	}
	
	function display_SearchForResult($results, $searchForID) {
		global $MATCHING;
		$imgWidth = 90;
		?><ul class="list-group"><?php	
		foreach($results as $result):
		?>
		<li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    <div style="background-image:url('<?php echo $result['img']?>'); background-size:cover; height:<?php echo $imgWidth?>px; width:<?php echo $imgWidth?>px;">
                    <img src="/assets/app/media/img/users/filler.png" style="height:<?php echo $imgWidth?>px; width:<?php echo $imgWidth?>px;">
                    </div>
                    <?php echo $this->record->get_personMMcolor($result['id'])?>
                </div>
                <div class="col-sm-10">
                	
                    <div class="row">
                        <div class="col-sm-6" style="padding-left:0px;">
                            <?php if($result['type_id'] == 3): ?>
                            <div class="truncate"><a href="/profile/<?php echo $result['id']?>" data-container="body" data-toggle="m-popover" data-placement="top" data-content="<?php echo $result['type']?> | <?php echo round($result['distance'], 1)?> miles"><span class="m--font-danger"><?php echo $result['id']?></span> </a></div>
                            <?php else: ?>
                            	<?php if($result['vip'] == 1): ?>
								<div class="truncate"><a href="/profile/<?php echo $result['id']?>" data-container="body" data-toggle="m-popover" data-placement="top" data-content="<?php echo $result['type']?> | <?php echo round($result['distance'], 1)?> miles | HIGH PRIORITY CLIENT"><span class="m--font-primary"><?php echo $result['id']?> (HPC)</span> </a></div>                                
                                <?php else: ?>
                            	<div class="truncate"><a href="/profile/<?php echo $result['id']?>" data-container="body" data-toggle="m-popover" data-placement="top" data-content="<?php echo $result['type']?> | <?php echo round($result['distance'], 1)?> miles"><span class="m--font-<?php echo $result['color']?>"><?php echo $result['name']?></span> </a></div>
                                <?php endif; ?>
                            <?php endif; ?>                           
                            <div class="truncate" style="margin-top:-5px;"><?php echo $result['age']?> yrs | <?php echo $result['loc']?></div>
                            <?php if($result['job'] != ''): ?>
                            <div class="truncate" style="margin-top:-5px;">
                                <small><?php echo $result['job']?></small>
                            </div>
                            <?php endif; ?>                    
                            <div class="truncate" style="margin-top:-5px;">
                                <small><?php echo $result['height']?> | <?php echo $result['weight']?> <?php echo (($result['rank'] != '')? '| Rating: '.$result['rank']:'')?></small>                                
                            </div>
                            <?php if(trim($result['seeking']) != ''): ?>                   
                            <div class="truncate" style="margin-top:-5px;">
                                <small>Seeking: <?php echo $result['seeking']?></small>                                
                            </div>
                            <?php endif; ?>
                            <?php if($result['matched'] != -1): ?>
                            <div class="truncate" style="margin-top:0px;">
                                <?php echo $MATCHING->get_dateStatusText($result['matched'])?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-sm-6">                        	                       	                   
                            <div class="truncate" style="margin-top:-5px;">
                            	<?php if($result['geoloc'] != 200): ?> 
                                <div class="pull-right">
                            		<span class="m--font-danger" data-container="body" data-toggle="m-popover" data-placement="top" data-content="This record is not geo-located and will not be included in any geo searches"><i class="flaticon-warning-sign"></i></span>
                            	</div>
                                <?php endif; ?> 
                                <span class="m--font-danger"><?php echo $result['score']?>% Match</span>                                
                            </div> 
                            <div class="truncate" style="margin-top:-5px;">
                                <?php echo round($result['distance'], 1)?> Miles                                
                            </div>
                            <div class="truncate" style="margin-top:-5px;">
                                <small>Have Children: <?php echo $result['haveKids']?></small>                                
                            </div>
                            <div class="truncate" style="margin-top:-5px;">
                                <small>Wants Children: <?php echo $result['wantKids']?></small>                                
                            </div>
                            <div class="truncate" style="margin-top:-5px;">
                                <small>Will Travel: <?php echo $result['travel']?></small>                                
                            </div>
                            <div class="truncate" style="margin-top:-5px;"><small>Last Update: <?php echo (($PSND['DateUpdated'] == 0)? 'None':$this->record->get_date_diff($data['last'], time()))?></small></div>                      
                        </div>
					</div>
                    
                    <div class="btn-group m-btn-group m-btn-group--pill" role="group" aria-label="...">
                    	<?php if($result['matched'] != -1): ?>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="createDate('<?php echo $searchForID?>','<?php echo $result['id']?>','101')" disabled>
                            <i class="fa fa-heart-o"></i> Create Introduction
                        </button>                        
                        <button type="button" class="btn btn-secondary btn-sm" onclick="createDate('<?php echo $searchForID?>','<?php echo $result['id']?>','102')" disabled>
                            <i class="fa fa-thumbs-o-down"></i> Quick Reject
                        </button> 
                        <?php else: ?>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="createDate('<?php echo $searchForID?>','<?php echo $result['id']?>','101')" >
                            <i class="fa fa-heart-o"></i> Create Introduction
                        </button>                        
                        <button type="button" class="btn btn-secondary btn-sm" onclick="createDate('<?php echo $searchForID?>','<?php echo $result['id']?>','102')">
                            <i class="fa fa-thumbs-o-down"></i> Quick Reject
                        </button>                         
                        <?php endif; ?>                       
                        <button type="button" class="btn btn-secondary btn-sm kiss-profile-preview-link" data-id="<?php echo $result['id']?>">
                            <i class="fa fa-search"></i> Quick View
                        </button>                        
                        <a href="/profile/<?php echo $result['id']?>" class="btn btn-secondary btn-sm" target="_blank">
                            <i class="fa fa-user"></i> Full Profile
                        </a>
                    </div>

				</div>                    
            </div>
            <div id="preview_<?php echo $result['id']?>_area"></div>
        </li>        
        <?php
		endforeach;
		?></ul><?php
	}
	
	function alreadyMatched($p1, $p2) {
		$sql = "SELECT pd.PersonsDates_status FROM PersonsDates pd WHERE (pd.PersonsDates_participant_1='".$p1."' AND pd.PersonsDates_participant_2='".$p2."') OR (pd.PersonsDates_participant_1='".$p2."' AND pd.PersonsDates_participant_2='".$p1."')";
		//echo $sql."\n";
		$snd = $this->db->get_single_result($sql);
		if(isset($snd['empty_result'])) {
			return -1;
		} else {
			return $snd['PersonsDates_status'];
		}
	}

	function displayResults($searchForID, $searchData, $displayCount=20) {
		?>
		<div class="m-list-search">
            <div class="m-list-search__results">
                <?php
				$count = 0;
				foreach($searchData as $data):
					if($count < $displayCount):
					$pid = $data['pid'];					
					//$alreadyMatched = $this->alreadyMatched($searchForID, $pid);					
					if($data['type'] == 3):
					?>
                    <a href="/profile/<?php echo $pid?>" class="m-list-search__result-item">                    	
                        <span class="m-list-search__result-item-pic" style="background-image:url('<?php echo $data['img']?>'); background-size:cover;">
                        	<img class="m--img-rounded" src="/assets/app/media/img/users/filler.png" title="">
                    	</span>
                    	<span class="m-list-search__result-item-text" style="padding-left:5px;">
                            <div class="truncate"><span class="m--font-info"><?php echo $pid?></span> <?php echo $data['age']?></div>
                            <div class="truncate">
                            	<small><span class="m--font-danger"><?php echo $data['scr']?>%</span> | <?php echo $data['loc']?></small>                                
							</div>                            
                    	</span>
                	</a>                    
                    <?php
					else:
					?>
                    <a href="/profile/<?php echo $pid?>" class="m-list-search__result-item">                    	
                        <span class="m-list-search__result-item-pic" style="background-image:url('<?php echo $data['img']?>'); background-size:cover;">
                        	<img class="m--img-rounded" src="/assets/app/media/img/users/filler.png" title="">
                    	</span>
                    	<span class="m-list-search__result-item-text" style="padding-left:5px;">
                        	<div class="truncate"><span class="m--font-success"><?php echo $data['nme']?></span> <?php echo $data['age']?></div>
                            <div class="truncate">
                            	<small><span class="m--font-danger"><?php echo $data['scr']?>%</span> | <?php echo $data['loc']?></small>                                
							</div>                            
                    	</span>
                	</a>                    
                    <?php
					endif;
					$count ++;
					endif;
				endforeach;	               
                ?>             
            </div>
        </div>        
        <?php
	}
	
	function display_personPreviewBlocks($personID, $ProfileObject, $imgWidth=150) {		
		global $MATCHING, $DB;
		$PSQL = "
SELECT 
	Persons.Person_id,	
	Persons.FirstName,
	Persons.LastName,
	Persons.PersonsTypes_id,
	Persons.DateOfBirth,
	Persons.DateUpdated,
	Persons.Assigned_userID,
	Persons.Matchmaker_id,
	Persons.Occupation,
	Persons.VIP,
	PersonTypes.PersonsTypes_text,
	PersonTypes.PersonsTypes_color,
	Addresses.City,
	Addresses.State,
	Addresses.Lattitude,
	Addresses.Longitude,
	PersonsProfile.prQuestion_621,
	PersonsProfile.prQuestion_622,	
	PersonsProfile.prQuestion_632,
	PersonsProfile.prQuestion_634,
	PersonsProfile.prQuestion_653,
	PersonsProfile.prQuestion_664,
	PersonsPrefs.prefQuestion_age_floor,
	PersonsPrefs.prefQuestion_Gender
FROM
	Persons
	INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id
	LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id	
WHERE 
	Persons.Person_id='".$personID."'
		";
		$PSND = $this->db->get_single_result($PSQL);
		
		$matched = $MATCHING->alreadyMatched($personID, $_POST['fromPID']);
		//echo "MATCHED:".$matched."<br>\n";
		?>
        
        <div style="padding:10px; border:#900 solid 1px; margin:20px 0px;" class="quickview-block">
        <div class="row">
            <div class="col-6">
                <h5>Profile Preview</h5>
            </div>
            <div class="col-6 text-right">                
            <button type="button" class="close" aria-label="Close" onclick="$('#preview_<?php echo $personID?>_area').html('')">
                <span aria-hidden="true">×</span>
            </button>
            </div>
        </div>            
        <div class="row">
            <div class="col-sm-3">
                <div style="background-image:url('<?php echo $this->record->get_PrimaryImage($PSND['Person_id'])?>'); background-size:cover; height:<?php echo $imgWidth?>px; width:<?php echo $imgWidth?>px;">
                <img src="/assets/app/media/img/users/filler.png" style="height:<?php echo $imgWidth?>px; width:<?php echo $imgWidth?>px;">
                </div>
            </div>
            <div class="col-sm-9">                	
                <div class="row">
                    <div class="col-sm-6" style="padding-left:0px;">
                        <?php if($PSND['PersonsTypes_id'] == 3): ?>
                        <div class="truncate"><a href="/profile/<?php echo $PSND['Person_id']?>" target="_blank" data-container="body" data-toggle="m-popover" data-placement="top" data-content="<?php echo $PSND['PersonsTypes_text']?>"><span class="m--font-danger"><?php echo $PSND['Person_id']?></span> |  <?php echo $this->record->get_userName($PSND['Assigned_userID'])?></a></div>
                        <?php else: ?>
                        	<?php if($PSND['VIP'] == 1): ?>
                        	<div class="truncate"><a href="/profile/<?php echo $PSND['Person_id']?>" target="_blank" data-container="body" data-toggle="m-popover" data-placement="top" data-content="<?php echo $PSND['PersonsTypes_text']?>"><span class="m--font-primary"><?php echo $PSND['Person_id']?> (HPC)</span> |  <?php echo $this->record->get_userName($PSND['Matchmaker_id'])?></a></div>
                        	<?php else: ?>
                        	<div class="truncate"><a href="/profile/<?php echo $PSND['Person_id']?>" target="_blank" data-container="body" data-toggle="m-popover" data-placement="top" data-content="<?php echo $PSND['PersonsTypes_text']?>"><span class="m--font-<?php echo $PSND['PersonsTypes_color']?>"><?php echo $this->record->get_personName($PSND['Person_id'])?></span> </a></div>
                        	<?php endif; ?>
                        <?php endif; ?>
                        <div class="truncate"><?php echo $this->record->get_personAge($PSND['DateOfBirth'], true)?> yrs | <?php echo $PSND['City']?> <?php echo $PSND['State']?></div>
                        <?php if($PSND['job'] != ''): ?>
                        <div class="truncate"><?php echo $PSND['Occupation']?></div>
                        <?php endif; ?>                    
                        <div class="truncate">
                            <?php echo $PSND['prQuestion_621']?> | <?php echo $PSND['prQuestion_622']?> <?php echo (($PSND['prQuestion_664'] != '')? '| Rating: '.$PSND['prQuestion_664']:'')?>                              
                        </div>
                        <?php if(trim($PSND['prefQuestion_Gender']) != ''): ?>                   
                        <div class="truncate">Seeking: <?php echo $PSND['prefQuestion_Gender']?></div>
                        <?php endif; ?>                          
                    </div>
                    <div class="col-sm-6">
                        <div class="truncate">
                            Have Children: <?php echo $PSND['prQuestion_632']?>                              
                        </div>
                        <div class="truncate">
                            Wants Children: <?php echo $PSND['prQuestion_634']?>                               
                        </div>
                        <div class="truncate">
                            Will Travel: <?php echo $PSND['prQuestion_653']?>                                
                        </div>
                        <div class="truncate" style="margin-top:-5px;"><small>Last Update: <?php echo date("m/d/Y", $PSND['DateUpdated'])?></small></div>                      
                    </div>
                </div>
                
                <!-- BUTTONS GO  HERE -->
                <div class="btn-group m-btn-group m-btn-group--pill" role="group" aria-label="..." style="margin-top:10px;">
					<?php if($matched != -1): ?>
                    <button type="button" class="btn btn-danger btn-sm" onclick="createDate('<?php echo $_POST['fromPID']?>','<?php echo $PSND['Person_id']?>','101')" disabled>
                        <i class="fa fa-heart-o"></i> Already Matched
                    </button>                        
                    <button type="button" class="btn btn-secondary btn-sm" onclick="createDate('<?php echo $_POST['fromPID']?>','<?php echo $PSND['Person_id']?>','102')" disabled>
                        <i class="fa fa-thumbs-o-down"></i> Quick Reject
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="createDate('<?php echo $_POST['fromPID']?>','<?php echo $PSND['Person_id']?>','101')" >
                        <i class="fa fa-heart-o"></i> Create Introduction
                    </button>                        
                    <button type="button" class="btn btn-secondary btn-sm" onclick="createDate('<?php echo $_POST['fromPID']?>','<?php echo $PSND['Person_id']?>','102')">
                        <i class="fa fa-thumbs-o-down"></i> Quick Reject
                    </button>                         
                    <?php endif; ?>                    
                </div>
                
            </div>                    
        </div>
        
        <ul class="nav nav-tabs  m-tabs-line" role="tablist">
            <li class="nav-item m-tabs__item">
                <a class="nav-link m-tabs__link active" data-toggle="tab" href="#preview_tab_1" role="tab" aria-expanded="true">Media</a>
            </li>
            <li class="nav-item m-tabs__item">
                <a class="nav-link m-tabs__link " data-toggle="tab" href="#preview_tab_3" role="tab" aria-expanded="true">Profile</a>
            </li>
            <li class="nav-item m-tabs__item">
                <a class="nav-link m-tabs__link" data-toggle="tab" href="#preview_tab_2" role="tab" aria-expanded="true">Prefs</a>
            </li>
        </ul>
        
        <div class="tab-content">
            <div class="tab-pane active" id="preview_tab_1" role="tabpanel" aria-expanded="true">
				<div class="row">
					<?php echo $this->render_imageLibrary($personID)?>
                </div>	                
            </div>
            <div class="tab-pane" id="preview_tab_2" role="tabpanel">
				<h4>Prefs</h4>
                 <?php echo $ProfileObject->render_FullPrefs($personID, false)?>
            </div>
            <div class="tab-pane" id="preview_tab_3" role="tabpanel" aria-expanded="false">
            	<h4>Profile</h4>
                <?php echo $ProfileObject->render_FullProfile($personID, false)?>
            </div>
        </div>
        
        </div>
        <?php
			
		
	}
	
	function render_imageLibrary($personID, $imgWidth=150, $preview=false) {
		$isql = "SELECT * FROM PersonsImages WHERE Person_id='".$personID."' AND PersonsImages_status > 0 ORDER BY PersonsImages_status DESC";
		$isnd = $this->db->get_multi_result($isql);
		if(!isset($isnd['empty_result'])) {
			foreach($isnd as $idta):
			if ($preview) {
				?><img src="/client_media/<?php echo $this->record->get_image_directory($personID)?>/<?php echo $personID?>/<?php echo $idta['PersonsImages_path']?>" style="width:<?php echo $imgWidth?>px;"><?php
			} else {
				?>
				<div class="col-md-3">
					<div style="background-image:url('/client_media/<?php echo $this->record->get_image_directory($personID)?>/<?php echo $personID?>/<?php echo $idta['PersonsImages_path']?>'); background-size:cover; height:<?php echo $imgWidth?>px; width:<?php echo $imgWidth?>px; margin-bottom:10px;">
						<img src="/assets/app/media/img/users/filler.png" style="height:<?php echo $imgWidth?>px; width:<?php echo $imgWidth?>px;">
					</div>
				</div>
				<?php
			}
			endforeach;	
		}
	}
	
	function scoreMatch($personID, $matchID) {
		$sql = "SELECT * FROM PersonsPrefs WHERE Person_id='".$personID."'";
		$prefs = $this->db->get_single_result($sql);
		//print_r($prefs);
		
		$psql = "SELECT * FROM PersonsProfile WHERE Person_id='".$matchID."'";
		$psnd = $this->db->get_single_result($psql);
		//print_r($psnd);
		
		$pq_sql = "SELECT * FROM PrefQuestions WHERE PrefQuestion_active='1' AND PrefQuestions_cat='Profile'";
		$pq_snd = $this->db->get_multi_result($pq_sql);
		$qCount = 0;
		$mCount = 0;
		foreach($pq_snd as $pq_dta):
			$matchField = $pq_dta['PrefQuestions_mappedMatchField'];
			$matchResponse = $psnd[$matchField];
			
			$mappedField = $pq_dta['PrefQuestion_mappedField'];
			$wantArray = explode("|", $prefs[$mappedField]);
			
			if ($prefs[$mappedField] != '') {
				//echo "CHECKING FOR: (".$matchField.") ".$matchResponse." in (".$mappedField.")";
				//print_r($wantArray);			
				$qCount++;
			
				if(in_array($matchResponse, $wantArray)) {
					$mCount++;
				}
			}
		endforeach;
		
		//$score = $qCount."|".$mCount;
		$score = @round((($mCount / $qCount) * 100), 0);
		//echo $mCount."|".$qCount."|".$score;
		return $score;
	}
	
	function quickMatches($person_id, $address_id, $longVersion=false) {
		$include_array = array('prefQuestion_621','prefQuestion_622','prefQuestion_624','prefQuestion_631');
		$question_array = array('Prefered Heights','Prefered Weights', 'Prefered Ethnicity', 'Prefered Income');
		$completed_so_far = $this->getCompletionStatus($person_id);
		
		$p_sql = "
		SELECT
			*
		FROM
			Persons
			INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
			INNER JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
		WHERE
			Persons.Person_id='".$person_id."'		
		";
		//echo $p_sql;
		$p_dta = $this->db->get_single_result($p_sql);
		$ageParts = explode("|", $p_dta['prefQuestion_age_floor']);
		$ageFloor = $ageParts[0];
		$agePeak = $ageParts[1];
		$prefGender = explode("|", $p_dta['prefQuestion_Gender']);
		//echo "AGE:".$ageFloor."|".$agePeak;
		//echo "GENDER:".$prefGender;
		$prefTypes = explode("|", $p_dta['prefQuestion_Pref_MemberTypes']);
		if ($p_dta['prefQuestion_Pref_Offices'] != '') {
			$prefOffices = explode("|", $p_dta['prefQuestion_Pref_Offices']);
		}
		if($p_dta['prefQuestion_distance'] != '') {
			$prefDistance = $p_dta['prefQuestion_distance'];
		} else {
			$prefDistance = 50;
		}
		//print_r($p_dta);
		$customFound = 0;
		$missingFields = array();
		for($i=0; $i<count($include_array); $i++) {
			if ($p_dta[$include_array[$i]] != '') {
				$customFound++;				
				$foundFields[] = $question_array[$i];
			} else {
				$missingFields[] = $question_array[$i];
			}
		}
			
		if(($completed_so_far == 100) && ($customFound != 0)) {
			$myLocation = $this->record->get_primaryAddressGeoLocation($address_id);
			$SQL = "
			SELECT 
				Persons.Person_id,	
				Persons.FirstName,
				Persons.LastName,
				Persons.PersonsTypes_id,
				Persons.DateOfBirth,
				Persons.DateUpdated,
				Persons.Assigned_userID,
				Persons.Matchmaker_id,
				(((acos(sin((".$myLocation['lat']."*pi()/180)) * sin((Addresses.Lattitude*pi()/180)) + cos((".$myLocation['lat']."*pi()/180)) * cos((Addresses.Lattitude*pi()/180)) * cos(((".$myLocation['lng']." - Addresses.Longitude)*pi()/180))))*180/pi())*60*1.1515) as distance,
				Addresses.*,				
				PersonsProfile.*,
				PersonsImages.PersonsImages_path,
				PersonTypes.PersonsTypes_color
			FROM
				Persons
				INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
				INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id
				INNER JOIN Offices ON Offices.Offices_id=Persons.Offices_id
				INNER JOIN Addresses ON Addresses.Person_id=Persons.Person_id AND Addresses.isPrimary='1'
				LEFT JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'				
				LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id	
			WHERE 
				1
			AND
				Persons.Assigned_userID NOT IN (".implode(",", $this->skippedUsers).")
			";
			
			if(($ageFloor != '') && ($agePeak != '')) {
				$whereClause[] = "AND DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), Persons.DateOfBirth)), '%Y')+0 BETWEEN " . $ageFloor . " AND " . $agePeak. "";	
			}	
			if(isset($prefGender)) {
				$whereClause[] = "AND Persons.Gender IN ('".implode("','", $prefGender)."')";
			}
			if(isset($prefTypes)) {
				$whereClause[] = "AND Persons.PersonsTypes_id IN (".implode(",", $prefTypes).")";
			} else {
				$whereClause[] = "AND Persons.PersonsTypes_id NOT IN (".implode(",", array(1,2,9)).")";
			}
			if(isset($prefOffices)) {
				$whereClause[] = "AND Persons.Offices_id IN (".implode(",", $prefOffices).")";
			}
			$postKeys = array_keys($p_dta);
			//$ommit_array = array('prefQuestion_Pref_States','prefQuestion_Pref_Zips','prefQuestion_Pref_Countries','prefQuestion_Pref_Cities','prefQuestion_distance','prefQuestion_Pref_MemberTypes','prefQuestion_age_floor','prefQuestion_Gender');			
			for($loop=0; $loop<count($postKeys); $loop++) {
				// CUSTOM CASES //
				$check = substr($postKeys[$loop], 0, 13);
				//echo $check."\n";
				if($check == 'prefQuestion_') {
					$qid = substr($postKeys[$loop], 13);
					$theirField = 'prQuestion_'.$qid;
					if (($p_dta[$postKeys[$loop]] != '') && (in_array($postKeys[$loop], $include_array))) {
						$newSetArrayTemp = explode("|", $p_dta[$postKeys[$loop]]);
						for($i=0; $i<count($newSetArrayTemp); $i++) {
							$newSetArray[] = "'".$this->db->mysqli->escape_string($newSetArrayTemp[$i])."'";
						}
						//print_r($newSetArray);
						$whereClause[] = "AND PersonsProfile.".$theirField." IN (".implode(",", $newSetArray).")";
						unset($newSetArray);
					}
				}
			}
			//print_r($whereClause);
			foreach($whereClause as $where) {
				$SQL .= $where." ";
			}
			$SQL .= "GROUP BY Persons.Person_id ";
			$SQL .= "HAVING distance <= '".$prefDistance."' ";
			$SQL .= "ORDER BY distance ASC";
			
			$validResults = true;
		} else {
			$validResults = false;
		}
		//echo "SQL:".$SQL."<br>\n";
		
		if($longVersion) {
			if($validResults) {
				$results = $this->db->get_multi_result($SQL);
				if(isset($results['empty_result'])):
					?>NO RESULTS FOR THIS ADDRESS<?php
				else:
					//echo "FOUND RESULTS - PARSING<br>\n"; 
					$parsedResults = $this->parseSearchResults($person_id, $results, true);			
					$this->displayResultsList($person_id, $parsedResults, 5);
				endif;
			} else {
				?>
                <em>Insufficent prefs to find ideal matches</em>
                <div class="alert alert-danger" style="margin-top:10px;">
                	<small>
                	Record must have at least one of these fields filled with preference information:
                    <ul>
                    <li><?php echo implode("</li><li>", $missingFields)?></li>
                    </ul>
                    </small>
				</div>                    
				<?php
				//print_r($missingFields);	
			}
		} else {
			if($validResults):
				$found = $this->db->get_multi_result($SQL, true);
				?><span class="m--font-metal" style="font-size:0.8em;"><a href="/findfor/<?php echo $person_id?>/<?php echo $address_id?>" class="m-link"><?php echo $found?> Matches</a> within <?php echo $prefDistance?> miles</span><?php
			else:
				?><small><em>insufficent prefs to quick match</em></small><?php
			endif;
		}		
	}
	
	function render_IdealMatches($person_id) {
		$p_sql = "
		SELECT
			PersonsPrefs.*
		FROM
			Persons
			INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
			INNER JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
		WHERE
			Persons.Person_id='".$person_id."'		
		";
		//echo $p_sql;
		$p_dta = $this->db->get_single_result($p_sql);
		if ($p_dta['prefQuestion_distance'] == '') {
			$prefDistance = 50;
		} else {
			$prefDistance = $p_dta['prefQuestion_distance'];
		}
		$sql = "SELECT * FROM Addresses WHERE Person_id='".$person_id."' ORDER BY isPrimary DESC";
		//echo $sql;
		$snd = $this->db->get_multi_result($sql);
		if(!isset($snd['empty_result'])):
			foreach($snd as $dta):
			?><div style="font-size:14px; font-weight:bold; margin-top:10px; margin-bottom:5px;">within <?php echo $prefDistance?> miles of <?php echo $dta['City']?> <?php echo $dta['State']?></div><?php
			$this->quickMatches($person_id, $dta['Address_id'], true);
			endforeach;
		endif;
	}
	
	function render_getlastDates($person_id, $dateCount=3) {
		$imgWidth = 50;
		$d_sql = "SELECT * FROM PersonsDates WHERE PersonsDates_isComplete='1' AND (PersonsDates_participant_1='".$person_id."' OR PersonsDates_participant_2='".$person_id."') ORDER BY PersonsDates_dateExecuted DESC LIMIT ".$dateCount;
		$d_snd = $this->db->get_multi_result($d_sql);
		if(isset($d_snd['empty_result'])) {
			?><div class="text-center"><em>No completed matches</em></div><?php				
		} else {
			?><div class="m-stack m-stack--hor m-stack--general" style="height:165px"><?php
			foreach($d_snd as $d_dta):
				if($d_dta['PersonsDates_participant_1'] == $person_id) {
					$daterID = $d_dta['PersonsDates_participant_2'];
				} else {
					$daterID = $d_dta['PersonsDates_participant_1'];
				}
				?>
                <div class="m-stack__item">
                    <div class="row">
                    	<div class="col-2">
                        	<div style="background-image:url('<?php echo $this->record->get_PrimaryImage($daterID)?>'); background-size:cover; height:<?php echo $imgWidth?>px; width:<?php echo $imgWidth?>px;">
                				<img src="/assets/app/media/img/users/filler.png" style="height:<?php echo $imgWidth?>px; width:<?php echo $imgWidth?>px;">
                			</div>	
                        </div>
                        <div class="col-8">
                            <div><strong><?php echo $this->record->get_personName($daterID)?></strong></div>                         
                            <div>
							<?php if($d_dta['PersonsDates_dateExecuted'] != 0): ?>
                            	<?php echo $this->record->get_date_diff(date("m/d/Y", $d_dta['PersonsDates_dateExecuted']), date("m/d/Y"))?>
                            <?php else: ?>
                            	N/A
                            <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-2" style="background-color:#EAEAEA;">
                        	<div style="font-size:2.0em;">
							<?php echo round((($d_dta['PersonsDates_participant_1_rank'] + $d_dta['PersonsDates_participant_2_rank']) /2))?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
			endforeach;
			?></div><?php
		}					
	}
	
	function time_diff($ts1, $ts2) {
        # Find The Bigger Number
        if ($ts1 == $ts2) {
            return '0 Seconds';
        } else if ($ts1 > $ts2) {
            $large = $ts1;
            $small = $ts2;
        } else {
            $small = $ts1;
            $large = $ts2;
        }
        # Get the Diffrence
        $diff = $large - $small;
        # Setup The Scope of Time
        $s = 1;         $ss = 0;
        $m = $s * 60;   $ms = 0;
        $h = $m * 60;   $hs = 0;
        $d = $h * 24;   $ds = 0;
        $n = $d * 31;   $ns = 0;
        $y = $n * 365;  $ys = 0;
        # Find the Scope
        while (($diff - $y) > 0) { $ys++; $diff -= $y; }
        while (($diff - $n) > 0) { $ms++; $diff -= $n; }
        while (($diff - $d) > 0) { $ds++; $diff -= $d; }
        while (($diff - $h) > 0) { $hs++; $diff -= $h; }
        while (($diff - $m) > 0) { $ms++; $diff -= $m; }
        while (($diff - $s) > 0) { $ss++; $diff -= $s; }
        # Print the Results
        return "$hs Hours and $ms Minutes ago";
    }
	
	
	
	// RICH, YOU CAN PLACE YOUR METHODS HERE //
	
	
	
}