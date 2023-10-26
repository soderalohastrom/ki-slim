<?php
include_once("class.record.php");
include_once("class.profile.php");
$RECORD = new Record($DB);
$PROFILE = new Profile($DB, $IEDIT);

$g_sql = "SELECT * FROM UserClasses ORDER BY userClass_id DESC";
$g_snd = $DB->get_multi_result($g_sql);
ob_start();
?><option value=""></option><?php
foreach($g_snd as $g_dta):
	$groupID = $g_dta['userClass_id'];
	$u_sql = "SELECT * FROM Users WHERE userClass_id='".$groupID."'";
	$u_fnd = $DB->get_multi_result($u_sql, true);
	if($u_fnd > 0):
		$u_snd = $DB->get_multi_result($u_sql);
		?><optgroup label="<?php echo $g_dta['userClass_name']?>"><?php
		foreach($u_snd as $u_dta):
?><option value="<?php echo $u_dta['user_id']?>" <?php echo (($u_dta['user_id'] == $_SESSION['system_user_id'])? 'selected':'')?>><?php echo $u_dta['firstName']?> <?php echo $u_dta['lastName']?></option>
<?php
		endforeach;
		?></optgroup><?php
	endif;	
endforeach;
$userSelect = ob_get_clean();

$TwentyOne = mktime(0,0,0,1,1,(date("Y") - 25));


$c_code = 'US';
$c_sql = "SELECT * FROM SOURCE_Contries";
$c_snd = $DB->get_multi_result($c_sql);
ob_start();
foreach($c_snd as $c_dta):
	$countryCode 	= $c_dta['CountryCode'];
	$countryName	= $c_dta['Country'];
	?><option value="<?php echo $countryCode?>" <?php echo (($countryCode == $c_code)? 'selected':'')?>><?php echo $countryName?></option><?php
endforeach;
$country_select_options = ob_get_clean();

$s_sql = "SELECT * FROM SOURCE_States WHERE CountryCode='".$c_code."' ORDER BY State";
$s_snd = $DB->get_multi_result($s_sql);
ob_start();
?><option value=""></option><?php
foreach($s_snd as $s_dta):
	?><option value="<?php echo $s_dta['StateCode']?>" <?php echo (($s_dta['StateCode'] == $PDATA['State'])? 'selected':'')?>><?php echo $s_dta['State']?></option><?php
endforeach;
$state_select_options = ob_get_clean(); 
?>
<div class="m-content">
<div class="m-portlet">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <span class="m-portlet__head-icon m--hide">
                    <i class="la la-gear"></i>
                </span>
                <h3 class="m-portlet__head-text">
                    <i class="flaticon-users"></i> Enter New Lead
                </h3>
            </div>
        </div>
    </div>
    <!--begin::Form-->
    <form id="newLead" class="m-form m-form--fit m-form--label-align-right m-form--group-seperator-dashed m-form--state" action="javascript:enterNewLead()">
        <div class="m-portlet__body">
            <div class="form-group m-form__group row">
                <label class="col-lg-2 col-form-label">
                    Name:
                </label>
                <div class="col-lg-3" id="fullName">
                	<div class="input-group m-input-group">
	                    <input type="text" name="FirstName" id="FirstName" class="form-control m-input" placeholder="First" >
                        <span class="input-group-addon">&nbsp;</span>
                        <input type="text" name="LastName" id="LastName" class="form-control m-input" placeholder="Last">
					</div>
                    <div class="form-control-feedback" style="display:none;">
                        You must enter in a first and last name
                    </div>                        
                    <span class="m-form__help">
                        Please enter your full name
                    </span>
                </div>
                <label class="col-lg-2 col-form-label">
                    Email Address:
                </label>
                <div class="col-lg-3" id="fullEmail">
                	<div class="m-input-icon m-input-icon--right">
                        <input type="email" name="Email" id="Email" class="form-control m-input" placeholder="user@domain.com">
                        <span class="m-input-icon__icon m-input-icon__icon--right">                    
                            <span><i class="la 	la-envelope-o"></i></span>
                        </span>
                    </div>
                    <div class="form-control-feedback" style="display:none;">
                        You must enter in a valid email address
                    </div>                       
                </div>
            </div>
            
            <div class="form-group m-form__group row">
                <label class="col-lg-2 col-form-label">
                    Gender:
                </label>
                <div class="col-lg-3" id="fullGender">
                    <div class="m-radio-list">
                    	<label class="m-radio m-radio--bold">
                        	<input type="radio" name="Gender" class="genderRadio" value="M">
                            <i class="fa fa-male"></i> Male            	
                            <span></span>
        				</label>
                        <label class="m-radio m-radio--bold">
                        	<input type="radio" name="Gender" class="genderRadio" value="F">
                            <i class="fa fa-female"></i> Female
                            <span></span>
        				</label>
                    </div>
                    <div class="form-control-feedback" style="display:none;">
                        You must select a gender
                    </div>
                </div>
                <label class="col-lg-2 col-form-label">
                    Assigned to:
                </label>
                <div class="col-lg-3">
                    <select class="form-control m-select2" id="Assigned_userID" name="Assigned_userID" style="width:100%;">
                 		<?php echo $userSelect?>   
                	</select>
    				<span class="m-form__help">
        				Select the user assigned to this lead.
    				</span>
                </div>
            </div>
            
            <div class="form-group m-form__group row">
                <label class="col-lg-2 col-form-label">
                    Date of Birth:
                </label>
                <div class="col-lg-3">
                    <div class="input-group m-input-group">
                    	<select name="DateOfBirth_m" id="DateOfBirth_m" class="form-control m-input form-control-sm">
                            <option value="01" <?php echo ((date("m", $TwentyOne) == '01')? 'selected':'')?>>Jan</option>
                            <option value="02" <?php echo ((date("m", $TwentyOne) == '02')? 'selected':'')?>>Feb</option>
                            <option value="03" <?php echo ((date("m", $TwentyOne) == '03')? 'selected':'')?>>Mar</option>
                            <option value="04" <?php echo ((date("m", $TwentyOne) == '04')? 'selected':'')?>>Apr</option>
                            <option value="05" <?php echo ((date("m", $TwentyOne) == '05')? 'selected':'')?>>May</option>
                            <option value="06" <?php echo ((date("m", $TwentyOne) == '06')? 'selected':'')?>>Jun</option>
                            <option value="07" <?php echo ((date("m", $TwentyOne) == '07')? 'selected':'')?>>Jul</option>
                            <option value="08" <?php echo ((date("m", $TwentyOne) == '08')? 'selected':'')?>>Aug</option>
                            <option value="09" <?php echo ((date("m", $TwentyOne) == '09')? 'selected':'')?>>Sep</option>
                            <option value="10" <?php echo ((date("m", $TwentyOne) == '10')? 'selected':'')?>>Oct</option>
                            <option value="11" <?php echo ((date("m", $TwentyOne) == '11')? 'selected':'')?>>Nov</option>
                            <option value="12" <?php echo ((date("m", $TwentyOne) == '12')? 'selected':'')?>>Dec</option>
                        </select>
                        <select name="DateOfBirth_d" id="DateOfBirth_d" class="form-control m-input form-control-sm">
                            <?php for($i=1; $i < 32; $i++): ?>
                            <option value="<?php echo $i?>" <?php echo (($i == date("d", $TwentyOne))? 'selected':'')?>><?php echo $i?></option>
                            <?php endfor; ?>
                        </select>
                        <select name="DateOfBirth_y" id="DateOfBirth_y" class="form-control m-input form-control-sm">
                            <?php for($i=date("Y"); $i > 1900; $i--): ?>
                            <option value="<?php echo $i?>" <?php echo (($i == date("Y", $TwentyOne))? 'selected':'')?>><?php echo $i?></option>
                            <?php endfor; ?>
                        </select>
                        <span class="input-group-addon"><i class="flaticon-calendar-2"></i></span>
					</div>
                    <span class="m-form__help">
                        enter date of birth or <a href="javascript:$('#age_enter_form').toggle();">click here</a> to select by age | default 25
                    </span>
                    
                    <div id="age_enter_form" style="display:none;">
                        <div class="input-group m-input-group">
                            <span class="input-group-addon"><i class="flaticon-time-3"></i></span>
                            <input type="number" class="form-control m-input form-control-sm" id="value_age" name="value_age" value="25">                
                            <span class="input-group-addon">AGE</span>
                            <span class="input-group-btn">
                                <button class="btn btn-brand btn-sm" onclick="getDOBfromAGE()" type="button"><i class="fa fa-gear"></i></button>
                            </span>
                        </div>
                        <span class="m-form__help">
                            enter age and click the button to calculate Date of birth
                        </span>
                    </div>
                </div>
                <label class="col-lg-2 col-form-label">
                    Phone Number:
                </label>
                <div class="col-lg-3">
                    <div class="m-input-icon m-input-icon--right">
                        <input type="text" name="Phone_number" id="Phone_number" class="form-control m-input" placeholder="555-555-5555">
                        <span class="m-input-icon__icon m-input-icon__icon--right">
                            <span>
                                <i class="la la-phone"></i>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="form-group m-form__group row">
                <label class="col-lg-2 col-form-label">
                    Location:
                </label>
                <div class="col-lg-3" id="fullOffice">
                    <div class="m-radio-list">
						<?php echo $RECORD->render_officeRadio($PDATA['Offices_id'], 'Offices_id', 'officeNum')?>
                    </div>
                    <div class="form-control-feedback" style="display:none;">
						You must select a home location for this record
					</div>
                    <span class="m-form__help">
                        Select the home office of this record.
                    </span>
                </div>
                <label class="col-lg-2 col-form-label">
                    Address:
                </label>
                <div class="col-lg-3">
                    <div class="m-input-icon m-input-icon--right">
                        <input type="text" name="Street_1" id="Street_1" class="form-control m-input" placeholder="Street Address">
                        <span class="m-input-icon__icon m-input-icon__icon--right">
                            <span>
                                <i class="la la-map-marker"></i>
                            </span>
                        </span>
                    </div>
                    <div class="input-group m-input-group">
                    	<input type="text" name="City" id="City" class="form-control m-input" placeholder="City">
                        <span class="input-group-addon">,</span>
                        <select name="State" id="State" class="form-control m-input">
                        	<?php echo $state_select_options?>
						</select>
                        <input type="text" name="Postal" id="Postal" class="form-control m-input" placeholder="Postal">	
                    </div>
                    <select name="Country" id="Country" class="form-control m-input" onchange="setCountryStates()">
                        <?php echo $country_select_options?>
                    </select>
                </div>
            </div>
            
            <div class="form-group m-form__group row">
                <label class="col-lg-2 col-form-label">&nbsp;</label>
                <div class="col-lg-10">
                    <button type="submit" class="btn btn-success">
                        Create New Record
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
        <?php echo $SESSIONS->renderToken()?>
    </form>
    <!--end::Form-->    
</div>
</div>
<p>&nbsp;</p>

<div class="modal fade" id="newConfirmModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="phonesModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="phonesModalLabel">&nbsp;</h5>
				<button type="button" class="close" onclick="document.location.reload(true)">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">

<div class="m-alert m-alert--icon m-alert--outline alert alert-primary" role="alert">
    <div class="m-alert__icon">
        <i class="la la-warning"></i>
    </div>
    <div class="m-alert__text">
        <strong>
            New Record Created!
        </strong>
    </div>
    <div class="m-alert__actions" style="width:300px;">
        <a id="newRecord_link" href="/profile/" class="btn btn-brand btn-sm m-btn m-btn--pill m-btn--wide">
            View Record
        </a>
        <button type="button" class="btn btn-danger btn-sm m-btn m-btn--pill m-btn--wide" onclick="document.location.reload(true)">
            Dismiss
        </button>
    </div>
</div>
    
			</div>
			<div class="modal-footer">
				&nbsp;
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function(e) {
    $("#Assigned_userID").select2({
		placeholder: "Select a user"	
	});
});
var enterNewLead = function() {
	var error = 0;
	var errorTxt = '';
	// VALIDATE FORM //
	var fName = $('#FirstName').val();
	var lName = $('#LastName').val();
	if((fName == '') || (lName == '')){
		error = 1;
		$('#fullName').addClass('has-danger');
		$('#fullName .form-control-feedback').show();
	} else {
		$('#fullName').removeClass('has-danger');
		$('#fullName .form-control-feedback').hide();
	}
	
	var email = $('#Email').val();
	if(email == '') {
		error = 1;
		$('#fullEmail').addClass('has-danger');
		$('#fullEmail .form-control-feedback').show();
	} else {
		if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
			$('#fullEmail').removeClass('has-danger');
			$('#fullEmail .form-control-feedback').hide();
			//error = 0;
		} else {
			$('#fullEmail').addClass('has-danger');
			$('#fullEmail .form-control-feedback').show();
			error = 1;
		}
	}
	var gChoice = 0;
	$('.genderRadio').each(function() {
		console.log($(this).val());
		if($(this).is(':checked')) {
			gChoice = 1;
		}
	});
	if(gChoice == 0) {
		$('#fullGender').addClass('has-danger');
		$('#fullGender .form-control-feedback').show();
		error = 1;
	} else {
		$('#fullGender').removeClass('has-danger');
		$('#fullGender .form-control-feedback').hide();
		//error = 0;
	}
	
	var oChoice = 0;
	$('.officeNum').each(function() {
		console.log($(this).val());
		if($(this).is(':checked')) {
			oChoice = 1;
		}
	});
	if(oChoice == 0) {
		$('#fullOffice').addClass('has-danger');
		$('#fullOffice .form-control-feedback').show();
		error = 1;
	} else {
		$('#fullOffice').removeClass('has-danger');
		$('#fullOffice .form-control-feedback').hide();
		//error = 0;
	}
	
	console.log(error);
	console.log(oChoice);
	console.log(gChoice);
	
	if(error == 0) {
		var formData = $('#newLead').serializeArray();
		$.post('/ajax/newRecord.php', formData, function(data) {
			$('#newConfirmModal').modal('show');	
			$('#newRecord_link').prop('href', '/profile/'+data.person_id);
		}, "json");
	}
}

var getDOBfromAGE = function() {
	var age = $('#value_age').val();
	var now = moment();
	var dob = moment().subtract(age, 'years');
	console.log(dob.format('MM'));
	console.log(dob.format('D'));
	console.log(dob.format('YYYY'));
	$('#DateOfBirth_m').val('01');
	$('#DateOfBirth_d').val('1');
	$('#DateOfBirth_y').val(dob.format('YYYY'));	
}
var setCountryStates = function() {
	var country = $('#Country').val();
	$.post('/ajax/select.states.php', {
		country:	country	
	}, function(data) {
		$('#State').html(data);
	});
}






</script>
