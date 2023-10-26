<?php
class recordBio extends Record {
	function get_bioConfig($personID) {
		$sql = "SELECT BioConfig FROM Persons WHERE Person_id='".$personID."'";	
		$snd = $this->db->get_single_result($sql);
		if($snd['BioConfig'] == ''):
			$sql2 = "SELECT * FROM PersonsImages WHERE Person_id='".$personID."' AND PersonsImages_status IN (2,1)";
			$snd2 = $this->db->get_multi_result($sql2);	
			if($snd2['empty_result'] != 1):
				foreach($snd2 as $dta2):
					$bimages[] = $dta2['PersonsImages_id'];
					$bsizes[$dta2['PersonsImages_id']] = "H";
				endforeach;
			else:				
				$bimages[] = array();
				$bsizes[] = array();
			endif;
			
			$config = array(
				'coreInfo'	=> array('Location','Birthplace','Education','Occupation','Height'),
				'BioImages'	=> $bimages,
				'BioSize'	=> $bsizes
			);
		else:
			//echo "PRECONFIGURE:";
			$config = unserialize($snd['BioConfig']);
			//print_r($config);		
		endif;
		return $config;
	}
	
	function render_bioConfigModal($personID) {		
		ob_start();
		$imgPath_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		$bioConfig = $this->get_bioConfig($personID);		
		$sql = "SELECT * FROM PersonsImages WHERE Person_id='".$personID."' AND PersonsImages_status='2'";
		$snd = $this->db->get_multi_result($sql);		
		if($snd['empty_result'] != 1) {
			foreach($snd as $dta):
			$filePath = '/client_media/'.$this->get_image_directory($personID).'/'.$personID.'/'.$dta['PersonsImages_path'];
			if($dta['PersonsImages_status'] == 0) {
				$filler = '/assets/app/media/img/users/filler-large-red.png';	
			} else {
				$filler = '/assets/app/media/img/users/filler-large.png';
			}
			?>
            <li class="list-group-item bio-img-sort-dragger">
            	<div class="m-form__group form-group row">
                	<div class="col-2"><img src="<?php echo $filePath?>" class="img-fluid" /></div>
                    <div class="col-8">
                    	<label class="m-checkbox"><input type="checkbox" name="BioImages[]" value="<?php echo $dta['PersonsImages_id']?>" <?php echo (in_array($dta['PersonsImages_id'], $bioConfig['BioImages'])? 'checked':'')?> />Include<span></span></label>
                        <div class="m-radio-inline">
                            <label class="m-radio">
                                <input type="radio" name="BioSize[<?php echo $dta['PersonsImages_id']?>]" value="F" <?php echo (($bioConfig['BioSize'][$dta['PersonsImages_id']] == 'F')? 'checked':'')?>>
                                Full Size
                                <span></span>
                            </label>
                            <label class="m-radio">
                                <input type="radio" name="BioSize[<?php echo $dta['PersonsImages_id']?>]" value="H" <?php echo (($bioConfig['BioSize'][$dta['PersonsImages_id']] == 'H')? 'checked':'')?>>
                                Half Size
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-2 text-right"><i class="fa fa-sort fa-2x"></i></div>
				</div>
			</li>                                    
            <?php
			endforeach;	
		}
		
		$sql = "SELECT * FROM PersonsImages WHERE Person_id='".$personID."' AND PersonsImages_status='1' ORDER BY PersonsImages_order ASC";
		$snd = $this->db->get_multi_result($sql);
		if($snd['empty_result'] != 1) {
			foreach($snd as $dta):
			$filePath = '/client_media/'.$this->get_image_directory($personID).'/'.$personID.'/'.$dta['PersonsImages_path'];
			if($dta['PersonsImages_status'] == 0) {
				$filler = '/assets/app/media/img/users/filler-large-red.png';	
			} else {
				$filler = '/assets/app/media/img/users/filler-large.png';
			}
			?>
            <li class="list-group-item bio-img-sort-dragger">
            	<div class="row">
                	<div class="col-2"><img src="<?php echo $filePath?>" class="img-fluid" /></div>
                    <div class="col-8">
                    	<label class="m-checkbox"><input type="checkbox" name="BioImages[]" value="<?php echo $dta['PersonsImages_id']?>" <?php echo (@in_array($dta['PersonsImages_id'], $bioConfig['BioImages'])? 'checked':'')?>/>Include<span></span></label>
                    	<div class="m-radio-inline">
                            <label class="m-radio">
                                <input type="radio" name="BioSize[<?php echo $dta['PersonsImages_id']?>]" value="F" <?php echo (($bioConfig['BioSize'][$dta['PersonsImages_id']] == 'F')? 'checked':'')?>>
                                Full Size
                                <span></span>
                            </label>
                            <label class="m-radio">
                                <input type="radio" name="BioSize[<?php echo $dta['PersonsImages_id']?>]" value="H" <?php echo (($bioConfig['BioSize'][$dta['PersonsImages_id']] == 'H')? 'checked':'')?>>
                                Half Size
                                <span></span>
                            </label>
                        </div>    
					</div>
                    <div class="col-2 text-right"><i class="fa fa-sort fa-2x"></i></div>
				</div>
			</li>                                    
            <?php
			endforeach;	
		}
		$imageList = ob_get_clean();
		?>
<div class="modal fade" id="bioConfigModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="bioConfigModalLabel" aria-hidden="true">
	<div class="modal-dialog  modal-lg" role="document">
		<div class="modal-content">
			<form action="javascript:previewBioModal();" class="m-form" id="bioConfigForm">
            <input type="hidden" name="PID" value="<?php echo $personID?>" />
            <div class="modal-header">
				<h5 class="modal-title" id="bioConfigModalLabel">Bio Config Screen</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">          

<div id="BioConfig_formArea">
    <div class="m-form__group form-group row">
        <label class="col-4 col-form-label">
            Core Data to Include:
        </label>
        <div class="col-8">
            <div class="m-checkbox-list">
                <label class="m-checkbox">
                    <input type="checkbox" name="coreInfo[]" value="Location" <?php echo (@in_array('Location', $bioConfig['coreInfo'])? 'checked':'')?>>
                    Location (City, State)
                    <span></span>
                </label>
                <label class="m-checkbox">
                    <input type="checkbox" name="coreInfo[]" value="Birthplace" <?php echo (@in_array('Birthplace', $bioConfig['coreInfo'])? 'checked':'')?>>
                    Place of Birth
                    <span></span>
                </label>
                <label class="m-checkbox">
                    <input type="checkbox" name="coreInfo[]" value="Education" <?php echo (@in_array('Education', $bioConfig['coreInfo'])? 'checked':'')?>>
                    Education
                    <span></span>
                </label>
                <label class="m-checkbox">
                    <input type="checkbox" name="coreInfo[]" value="Occupation" <?php echo (@in_array('Occupation', $bioConfig['coreInfo'])? 'checked':'')?>>
                    Occupation
                    <span></span>
                </label>
                <label class="m-checkbox">
                    <input type="checkbox" name="coreInfo[]" value="Height" <?php echo (@in_array('Height', $bioConfig['coreInfo'])? 'checked':'')?>>
                    Height
                    <span></span>
                </label>
            </div>
            <span class="m-form__help">
                This information will appear in the top right area of the Bio.<br />Check all those you want to display.
            </span>
        </div>
    </div>
    <div class="m-form__group form-group row">
        <label class="col-4 col-form-label">
            Image Configuration:
        </label>
        <div class="col-8">
            <ul class="list-group" id="bio-img-preview-area">
                <?php echo $imageList?>
            </ul>        
            <span class="m-form__help">
                Check images you want to include in bio.<br />Drag &amp; Drop the images to sort them for the bio.
            </span>
        </div>
    </div>
    <div class="m-form__group form-group row">
        <label class="col-4 col-form-label">
            Text Content:
        </label>
        <div class="col-8">
<?php
$p_sql = "
SELECT
	*
FROM
	Persons 
	INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id		
	LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
	LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
WHERE
	Persons.Person_id='".$personID."'
";
$p_dta = $this->db->get_single_result($p_sql);

$sql = "SELECT * FROM QuestionsCategories WHERE QuestionsCategories_id IN (17) ORDER BY QuestionsCategories_id ASC";
//echo $sql."<br>\n";
$snd = $this->db->get_multi_result($sql);
//print_r($snd);
foreach($snd as $dta):
    $catID = $dta['QuestionsCategories_id'];
    $q_sql = "SELECT * FROM Questions WHERE QuestionsCategories_id='".$catID."' AND Questions_text != '' AND Questions_active='1' ORDER BY Questions_order ASC";
    //echo $q_sql."<br>\n";
	$q_found = $this->db->get_multi_result($q_sql, true);
    if($q_found):
        $q_snd = $this->db->get_multi_result($q_sql);
        $colCount = 0;
        foreach($q_snd as $q_dta):
            $fieldName = $q_dta['MappedField'];
            $fieldValue = $p_dta[$fieldName];
            if($fieldValue != ''):
				if($q_dta['QuestionTypes_id'] == 2):
				?>
                <div class="form-group m-form__group">
                    <label><?php echo $q_dta['Questions_text']?></label>
                    <textarea name="<?php echo $fieldName?>" class="form-control m-input" rows="10"><?php echo str_replace("|", ", ", $fieldValue)?></textarea>
                </div>
                <?php
				else:
				?>
                <div class="form-group m-form__group">
                    <label><?php echo $q_dta['Questions_text']?></label>
                    <input type="text"  name="<?php echo $fieldName?>" class="form-control m-input" value="<?php echo str_replace("|", ", ", $fieldValue)?>" />
                </div>
                <?php
				endif;
            endif;
        endforeach;
    endif;
endforeach;
?>        
        
        </div>
	</div>        
</div>

<div id="BioConfig_previewArea"></div>            
            	
    
			</div>
			<div class="modal-footer">            
            	<button type="button" class="btn btn-success" id="btn-bio-config-save" onclick="confirmBioModal()" style="display:none;"> Save &amp; Approve Bio Config </button>
                <button type="button" class="btn btn-secondary" id="btn-bio-preview-cancel" onclick="cancelPreview()" style="display:none;">Cancel Preview</button>
                <button type="submit" class="btn btn-primary" id="btn-bio-config-preview"> Preview Configuration </button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>                
			</div>
            </form>
		</div>
	</div>
</div> 


<div class="modal fade" id="bioConfigConfirmModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="bioConfigConfirmModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
            <input type="hidden" name="PID" value="<?php echo $personID?>" />
            <div class="modal-header">
				<h5 class="modal-title" id="bioConfigConfirmModalLabel">Bio Config Confirm</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="alert alert-info" role="alert">
                    <strong>NOTE</strong> You are confirming that this bio layout is acceptable and can be used when a person is attempting to generate and send a bio to a client/record 
                </div>
			</div>
			<div class="modal-footer">            
                <button type="button" class="btn btn-primary" id="btn-bio-config-confirm" onclick="saveBio()">I Confirm Configuration </button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>                
			</div>
		</div>
	</div>
</div>
<script>
var bio_el_img = document.getElementById('bio-img-preview-area');
var bio_img_sortable = Sortable.create(bio_el_img, {
	draggable: '.bio-img-sort-dragger',
	animation: 0,
	// Changed sorting within list
	onUpdate: function (evt) {
		// same properties as onEnd
		//console.log(evt);
		//alert('Image Order changed');
		/*
		if($('#save-image-order-area').is(':visible')) {
			// do nothing //
		} else {
			$('#save-image-order-area').show();
		}
		*/
	}		
});
function quickBioPreview() {
	$('#previewPersonModal').modal('show');
	$.post('/ajax/bioSearch.php?action=display&id=<?php echo $personID?>', { key: <?php echo rand(1000,9999)?> }, function(data) {
		if(data.showBio) {
			$('#previewPersonModal .modal-body').html(data.bioView);
		} else {
			alert('This record does not have a properly configured Bio');	
		}
	}, "json");		
}
function previewBioModal() {
	var formData = $('#bioConfigForm').serializeArray();
	$('#BioConfig_formArea').hide();
	$('#BioConfig_previewArea').show();
	$.post('/ajax/bioSearch.php?action=preview', formData, function(data) {
		$('#BioConfig_previewArea').html(data);
		$('#btn-bio-preview-cancel').show();
		$('#btn-bio-config-save').show();
		$('#btn-bio-config-preview').hide();
	});
}
function cancelPreview() {
	$('#BioConfig_formArea').show();
	$('#BioConfig_previewArea').hide();
	$('#btn-bio-preview-cancel').hide();
	$('#btn-bio-config-save').hide();
	$('#btn-bio-config-preview').show();
}
function confirmBioModal() {
	$('#bioConfigConfirmModal').modal('show');		
}
function saveBio() {
	var formData = $('#bioConfigForm').serializeArray();
	$('#bioConfigModal').modal('hide');
	$('#BioConfig_formArea').show();
	$('#BioConfig_previewArea').hide();
	$('#btn-bio-config-confirm').prop('disabled', true);
	$('#btn-bio-config-confirm').html('Saving Bio Config <i class="fa fa-circle-o-notch fa-spin"></i>');
	$.post('/ajax/bioSearch.php?action=biosave', formData, function(data) {
		$('#btn-bio-config-confirm').prop('disabled', false);
		$('#btn-bio-config-confirm').html('I Confirm Configuration');
		document.location.reload(true);	
	});
}
</script>       
        
        
        <?php		
	}
	
	function render_CustomBioView($personID, $overrideConfig=array(), $printing=false) {
		$imgPath_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		
		$p_sql = "
		SELECT
			*
		FROM
			Persons 
			INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id		
			LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
			LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
		WHERE
			Persons.Person_id='".$personID."'
		";
		$p_dta = $this->db->get_single_result($p_sql);
		
		$MemberNumber = $p_dta['Person_id'];
		$FirstName = $p_dta['FirstName'];
		$Age = $this->get_personAge($p_dta['DateOfBirth']);
		$City = $p_dta['City'];
		$State = $p_dta['State'];
		
		ob_start();
		$sql = "SELECT * FROM QuestionsCategories WHERE QuestionsCategories_id IN (17) ORDER BY QuestionsCategories_id ASC";
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta):
			$catID = $dta['QuestionsCategories_id'];
			$q_sql = "SELECT * FROM Questions WHERE QuestionsCategories_id='".$catID."' AND Questions_text != '' AND Questions_active='1' ORDER BY Questions_order ASC";
			$q_found = $this->db->get_multi_result($q_sql, true);
			if($q_found):
				$q_snd = $this->db->get_multi_result($q_sql);
				$colCount = 0;
				foreach($q_snd as $q_dta):
					$fieldName = $q_dta['MappedField'];
					$fieldValue = $p_dta[$fieldName];
					if($fieldValue != ''):				
						?><div class="" style="margin-bottom:5px;"><strong><?php echo $q_dta['Questions_text']?></strong>:<br /><?php echo str_replace("|", ", ", $fieldValue)?></div><?php						
					endif;
				endforeach;
			endif;
		endforeach;
		$profileList = ob_get_clean();
		
		if(!isset($overrideConfig['coreInfo'])):
			$bconfig = $this->get_bioConfig($personID);
		else:
			$bconfig = $overrideConfig;
		endif;
		
		for($idx=0; $idx<count($bconfig['BioImages']); $idx++):
			$picID = $bconfig['BioImages'][$idx];
			$picSZ = $bconfig['BioSize'][$picID];
			
			$sql2 = "SELECT * FROM PersonsImages WHERE PersonsImages_id='".$picID."'";
			$dta2 = $this->db->get_single_result($sql2);
			
			$filePath = '/client_media/'.$this->get_image_directory($personID).'/'.$personID.'/'.$dta2['PersonsImages_path'];
			$picLayout[] = array(
				'id'	=>	$picID,
				'sz'	=>	$picSZ,
				'path'	=>	$filePath,
				'html'	=>	'<img src="'.$filePath.'" class="img-fluid" />',
				'src'	=>	$_SERVER['DOCUMENT_ROOT'].$filePath
			);
		
		endfor;
		
		if(($picLayout[0]['sz'] == 'H') && ($picLayout[1]['sz'] == 'H')):
			// HALF SIZE TOP 2 //
			ob_start();
			?><img style="width:150px; height:200px; margin-left:1%; margin-right:1%; margin-top:5%; border:#666 solid 1px;" src="<?php echo $this->makeImageURL($picLayout[0]['src'], 150, 200)?>" /><?php
			?><img style="width:150px; height:200px; margin-left:1%; margin-right:1%; margin-top:5%; border:#666 solid 1px;" src="<?php echo $this->makeImageURL($picLayout[1]['src'], 150, 200)?>" /><?php
			$PICS['primary'] = ob_get_clean();	
			
			ob_start();
			for($i=2; $i<count($picLayout); $i++):				
				if($picLayout[$i]['sz'] == 'F'):
					?><img style="width:200px; height:250px; margin-left:1%; margin-right:1%; margin-top:2%; margin-bottom:2%; border:#666 solid 1px;" src="<?php echo $this->makeImageURL($picLayout[$i]['src'], 200, 250)?>" /><?php
				else:
					?><img style="width:100px; height:150px; margin-left:1%; margin-right:1%; margin-top:2%; margin-bottom:2%; border:#666 solid 1px;" src="<?php echo $this->makeImageURL($picLayout[$i]['src'], 100, 150)?>" /><?php						
				endif;			
			endfor;
			$PICS['secondary'] = ob_get_clean();
		else:
			// FALBACK - HALF SIZED SINGLE AT TOP //
			ob_start();	
			?><img style="width:300px; height:300px; margin-left:1%; margin-right:1%; margin-top:5%; margin-bottom:5%; border:#666 solid 1px;" src="<?php echo $this->makeImageURL($picLayout[0]['src'], 300, 300)?>" /><?php
			$PICS['primary'] = ob_get_clean();
			
			ob_start();
			for($i=1; $i<count($picLayout); $i++):				
				if($picLayout[$i]['sz'] == 'F'):
					?><img style="width:200px; height:250px; margin-left:1%; margin-right:1%; margin-top:2%; margin-bottom:2%; border:#666 solid 1px;" src="<?php echo $this->makeImageURL($picLayout[$i]['src'], 200, 250)?>" /><?php
				else:
					?><img style="width:100px; height:150px; margin-left:1%; margin-right:1%; margin-top:2%; margin-bottom:2%; border:#666 solid 1px;" src="<?php echo $this->makeImageURL($picLayout[$i]['src'], 100, 150)?>" /><?php						
				endif;
			endfor;
			$PICS['secondary'] = ob_get_clean();						
		endif;
		//print_r($bconfig);
		?>
        <style>
		@media print {
  			* {-webkit-print-color-adjust:exact;}
		}
		</style>
        <table width="<?php echo (($printing)? '100%':'700')?>" border="0" cellpadding="2" cellspacing="0" align="center" style="border-top:#333 solid 1px; border-bottom:#333 solid 1px; border-left:#333 solid 1px; border-right:#333 solid 1px; margin-bottom:10px;">
			<tr>
				<td width="60%" align="center" valign="top" style="width:60%; text-align:center;">
    				<div style="text-align:center;">
    					<?php echo $PICS['primary']?>
        			</div>                    
                    <?php if($printing): ?>
                    <div class="truncate no-print">Last Update: <?php echo (($p_dta['DateUpdated'] == 0)? 'None':date("m/d/y h:ia", $p_dta['DateUpdated']))?></div>
                    <div class="no-print" style="margin-left:10px; margin-right:10px; text-align:left;">         
                    <hr />
                        <div class="truncate"><?php echo $this->get_personAge($p_dta['DateOfBirth'], true)?> yrs (<?php echo date("m/d/y", $p_dta['DateOfBirth'])?>)</div>
                        <div class="nodetail-print">EMAIL: <?php echo $this->get_personEmail($p_dta['Person_id'])?></div>
                        <div class="nodetail-print"><?php echo $this->get_primaryPhone($p_dta['Person_id'], false, true)?></div>
                        <div class="nodetail-print"><?php echo $this->get_otherPhone($p_dta['Person_id'], $false, true)?></div>
                        <hr />
                        <div class="nodetail-print"><?php echo $this->get_primaryAddress($p_dta['Person_id'])?></div>            
                    </div>
                    <?php endif; ?>
    			</td>
    			<td width="40%" align="center" valign="top" style="width:40%; text-align:center; background-color:#EAEAEA;">
			    	<img align="center" src="<?php echo $imgPath_link?>/assets/vendors/modules/ckfinder/userfiles/images/ka-logo-400.png" class="img-fluid" style="max-width:300px;"/>
			        <div style="font-family:'Times New Roman', Times, serif; font-size:28px;"><?php echo strtoupper($FirstName)?></div>
                    <div style="margin:0px 20px; background-color:#fff; height:4px;">&nbsp;</div>
                    
					<?php if(@in_array('Location', $bconfig['coreInfo'])):?>
                    <div style="font-size:16px; margin-top:5px; margin-left:30px; text-align:center;"><span style="font-family:'Times New Roman', Times, serif;"><?php echo strtoupper($City)?> <?php echo strtoupper($State)?></span></div>
                    <?php endif;?>
                    
                    <?php if(@in_array('Birthplace', $bconfig['coreInfo'])):?>
                    <div style="font-size:16px; margin-top:20px; margin-left:30px; text-align:left;"><span style="font-family:'Times New Roman', Times, serif;">BORN:</span>&nbsp;<?php echo $p_dta['prQuestion_625']?></div>
                    <?php endif; ?>
                    
                    <?php if(@in_array('Education', $bconfig['coreInfo'])):?>
                    <div style="font-size:16px; margin-top:20px; margin-left:30px; text-align:left;"><span style="font-family:'Times New Roman', Times, serif;">EDUCATION:</span>&nbsp;<?php echo $p_dta['prQuestion_663']?></div>
                    <?php endif; ?>
                    
                    <?php if(@in_array('Occupation', $bconfig['coreInfo'])):?>
                    <div style="font-size:16px; margin-top:20px; margin-left:30px; text-align:left;"><span style="font-family:'Times New Roman', Times, serif;">OCCUPATION:</span>&nbsp;<?php echo $p_dta['Occupation']?></div>
                    <?php endif; ?>
                    
                    <?php if(@in_array('Height', $bconfig['coreInfo'])):?>
                    <div style="font-size:16px; margin-top:20px; margin-left:30px; text-align:left;"><span style="font-family:'Times New Roman', Times, serif;">HEIGHT:</span>&nbsp;<?php echo $p_dta['prQuestion_621']?></div>
                    <?php endif; ?>
                    <div style="margin:20px 20px 30px 20px; background-color:#fff; height:4px;">&nbsp;</div>
			    </td>
			</tr>
		</table>
        <table width="<?php echo (($printing)? '100%':'700')?>" border="0" cellpadding="2" cellspacing="0" align="center" style="border-top:#333 solid 1px; border-bottom:#333 solid 1px; border-left:#333 solid 1px; border-right:#333 solid 1px;">
        <tr>
            <td width="60%" align="center" valign="top" style="width:60%;">
                <div style="padding:20px; text-align:left;">
                    <h3>ABOUT <?php echo strtoupper($FirstName)?></h3> 
                    <?php echo $profileList?>    	
                </div>
            </td>
            <td width="40%" align="center" valign="top" style="width:40%; text-align:center; background-color:#EAEAEA;" class="backed-cell">
                <div style="text-align:center;">
                <?php echo $PICS['secondary']?>		
                </div>        
            </td>
        </table>
        <?php
	}
	
	function get_croppedImageBinary($image_path, $destW, $destH) {
		$x=$destW; 
		$y=$destH;
		$ratio_thumb=$x/$y; // ratio thumb		
		$thumb = imagecreatetruecolor($x, $y);
		$source = imagecreatefromjpeg($image_path);
		//list($owidth, $oheight) = getimagesize($image_path);
		
		list($xx, $yy) = getimagesize($image_path); // original size
		$ratio_original=$xx/$yy; // ratio original

		if ($ratio_original>=$ratio_thumb) {
		    $yo=$yy; 
		    $xo=ceil(($yo*$x)/$y);
		    $xo_ini=ceil(($xx-$xo)/2);
		    $xy_ini=0;
		} else {
		    $xo=$xx; 
		    $yo=ceil(($xo*$y)/$x);
		    $xy_ini=ceil(($yy-$yo)/2);
		    $xo_ini=0;
		}
		imagecopyresampled($thumb, $source, 0, 0, $xo_ini, $xy_ini, $x, $y, $xo, $yo);	
		//imagecopyresampled($thumb, $source, 0, 0, 0, 0, $x, $y, $xo, $yo);
		
		ob_start(); // Let's start output buffering.
    	imagejpeg($thumb); //This will normally output the image, but because of ob_start(), it won't.
    	$contents = ob_get_contents(); //Instead, output above is saved to $contents
		ob_end_clean(); //End the output buffer.
		$dataUri = "data:image/jpeg;base64," . base64_encode($contents); 
		return $dataUri;
		
	}
	
	function makeImageURL($file, $width, $height) {
		include_once("class.encryption.php");
		$ENC = new encryption(); 
		$imgPath_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		$params = $file.'|'.$width.'|'.$height;
		$url = $imgPath_link.'/view-image.php?img='.urlencode($ENC->encrypt($params));
		return $url;		
	}
}