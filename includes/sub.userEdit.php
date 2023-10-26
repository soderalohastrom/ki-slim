<?php
include_once("class.users.php");
$USER = new Users($DB);
$user_id = $PAGE_PARAMS['params'][0];

if($user_id != 0) {
	$sql = "SELECT * FROM Users WHERE user_id='".$user_id."'";
	//echo $sql;
	$dta = $DB->get_single_result($sql);
	$USER_PERMS = $USER->get_userPermissions($user_id);
} else {
	$USER_PERMS = array();
}

$MY_PERMS = $USER->get_userPermissions($_SESSION['system_user_id']);
?>
<script>
if (navigator.userAgent.toLowerCase().indexOf('chrome') >= 0) {
    setTimeout(function () {
        document.getElementById('userForm').autocomplete = 'off';
		document.getElementById('firstName').autocomplete = 'off';
    }, 1);
}

</script>
<form class="m-form m-form--fit m-form--label-align-right m-form--group-seperator-dashed m-form--state" id="userForm" action="javascript:updateUser();" autocomplete="off">
<input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id?>" />
<?php echo $SESSIONS->renderToken()?>
<div class="m-portlet m-portlet--mobile">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <div class="m-portlet__head-title">
                    <span class="m-portlet__head-icon">
                       <i class="flaticon-user-settings"></i> 
                    </span>
                    <h3 class="m-portlet__head-text">
                         1. Basic User Information
                    </h3>
                </div>
            </div>
        </div>
    </div>
    
     
    <div class="m-portlet__body">
        <div class="form-group m-form__group row">
            <div class="col-lg-4" id="form_firstName">
                <label>
                    First Name:
                </label>
                <input type="text" name="firstName"id="firstName" class="form-control m-input" value="<?php echo $dta['firstName']?>" autocomplete="off" />
                <div class="form-control-feedback" style="display:none;">
                    You must enter in a first name
                </div>
            </div>
            <div class="col-lg-4" id="form_lastName">
                <label class="">
                    Last Name:
                </label>
                <input type="text" name="lastName" id="lastName" class="form-control m-input" value="<?php echo $dta['lastName']?>" autocomplete="off" />
                <div class="form-control-feedback" style="display:none;">
                    You must enter in a last name
                </div>
            </div>
            <div class="col-lg-4" id="form_username">
                <label>
                    Username:
                </label>
                <div class="input-group m-input-group m-input-group--square">
                    <span class="input-group-addon">
                        <i class="la la-user"></i>
                    </span>
                    <input type="text" name="user_username" id="user_username" class="form-control m-input" value="<?php echo $dta['username']?>" autocomplete="off" />
                </div>
                <div class="form-control-feedback" style="display:none;">
                    You must enter in a valid username.
                </div>
                <span class="m-form__help">
                    Please enter username used to log-in
                </span>
            </div>
        </div>
        <div class="form-group m-form__group row">
            <div class="col-lg-4" id="form_email">
                <label>
                    Email:
                </label>
                <div class="m-input-icon m-input-icon--right">
                    <input type="email" name="email" id="email" class="form-control m-input" placeholder="user@host.com" value="<?php echo $dta['email']?>" autocomplete="off" />
                    <span class="m-input-icon__icon m-input-icon__icon--right">
                        <span>
                            <i class="la la-envelope-o"></i>
                        </span>
                    </span>
                </div>
                <div class="form-control-feedback" style="display:none;">
                    You must enter in a valid email address.
                </div>
                <span class="m-form__help">
                    Please enter your email address
                </span>
            </div>
            <div class="col-lg-4" id="form_userGender">
                <label class="">
                    Gender:
                </label>
                <div class="m-radio-inline">
                    <label class="m-radio m-radio--solid">
                        <input type="radio" name="userGender" class="userGender" value="M" <?php echo (($dta['userGender'] == 'M')? 'checked':'')?>>
                          <i class="fa fa-male"></i> Male
                        <span></span>
                    </label>
                    <label class="m-radio m-radio--solid">
                        <input type="radio" name="userGender" class="userGender" value="F" <?php echo (($dta['userGender'] == 'F')? 'checked':'')?>>
                        <i class="fa fa-female"></i> Female
                        <span></span>
                    </label>
                </div>
                <div class="form-control-feedback" style="display:none;">
                    You must select a gender
                </div>
            </div>
            <div class="col-lg-4" id="form_password">
                <label>
                    Password:
                </label>
                <div class="input-group m-input-group m-input-group--square">
                    <span class="input-group-addon">
                        <i class="la la-lock"></i>
                    </span>
                    <input type="password" name="user_password" id="user_password" class="form-control m-input"  value="" autocomplete="new-password" />
                </div>
                <div class="form-control-feedback" style="display:none;">
                    You must enter in a valid password.
                </div>
                <span class="m-form__help">
                    Leave blank to keep current password.
                </span>
            </div>
        </div>
            
        <div class="form-group m-form__group row">                
            <div class="col-lg-4" id="form_userStatus">
                <label class="">
                    Status:
                </label>
                <div class="m-radio-inline">
                    <label class="m-radio m-radio--solid">
                        <input type="radio" name="userStatus" class="userStatus" value="1" <?php echo (($dta['userStatus'] == '1')? 'checked':'')?>>
                        Active
                        <span></span>
                    </label>
                    <label class="m-radio m-radio--solid">
                        <input type="radio" name="userStatus" class="userStatus" value="0" <?php echo (($dta['userStatus'] == '0')? 'checked':'')?>>
                        Inactive
                        <span></span>
                    </label>
                    <label class="m-radio m-radio--solid">
                        <input type="radio" name="userStatus" class="userStatus" value="2" <?php echo (($dta['userStatus'] == '2')? 'checked':'')?>>
                        Archived
                        <span></span>
                    </label>
                    <!--
                    <label class="m-radio m-radio--solid">
                        <input type="radio" name="userStatus" class="userStatus" value="4" <?php echo (($dta['userStatus'] == '4')? 'checked':'')?>>
                        Locked
                        <span></span>
                    </label>
                    -->
                </div>
                <div class="form-control-feedback" style="display:none;">
                    You must select a user status
                </div>
            </div>
            <div class="col-lg-4 text-right">
            	<?php if((in_array(83, $MY_PERMS)) && ($user_id != 1)): ?>
            	<button type="button" class="btn btn-sm btn-warning" onclick="spoofUser('<?php echo $ENC->encrypt($user_id)?>')">Sign in as this User <i class="la la-user"></i></button>
                <?php endif; ?>
            </div>
            <div class="col-lg-4" id="form_userClass">
                <label>
                    User Class:
                </label>
                <div class="input-group m-input-group m-input-group--square">
                    <span class="input-group-addon">User Class</span>
                    <select name="userClass_id" id="userClass_id" class="form-control m-input" onchange="getUserClass()">
                        <option value=""></option>
						<?php echo $USER->select_getUserClasses($dta['userClass_id'])?>
                    </select>
                </div>
                <div class="form-control-feedback" style="display:none;">
                    You must select a user class.
                </div>
            </div>
        </div>          
    </div>
</div>

    
<div class="m-portlet m-portlet--mobile">        
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <span class="m-portlet__head-icon">
                    <i class="flaticon-settings"></i>
                </span>
                <h3 class="m-portlet__head-text">
                     2. Permissions
                     <span class="pull-right"><small>SUPER USER PERMISSION required request to System Tech Admin</small></span>
                </h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">&nbsp;</div>
    </div>
    
    <div class="m-portlet__body">
        <div class="row" id="permErrorAlert" style="margin-top:10px; display:none;">
            <div class="col-2">&nbsp;</div>
            <div class="col-8">               
                <div class="alert alert-danger" role="alert">
                    <strong>WARNING:</strong>
                    No permissions were selected.
                </div>
            </div>
            <div class="col-2">&nbsp;</div>
        </div>                
        <div class="form-group m-form__group row">
            <div class="col-lg-3">
                <label>
                    <strong>System</strong>
                </label>
                <?php echo $USER->render_userPermissionForm('System_1', $USER_PERMS)?>
            </div>
            <div class="col-lg-3">
                <label>
                    <strong>System (Cont)</strong>
                </label>
                <?php echo $USER->render_userPermissionForm('System_2', $USER_PERMS)?>
            </div>
            <div class="col-lg-3">
                <label>
                    <strong>Access</strong>
                </label>
                <?php echo $USER->render_userPermissionForm('Access', $USER_PERMS)?>
                <p>&nbsp;</p>
                <label>
                    <strong>Make Record Types</strong>
                </label>
                <?php echo $USER->render_userPermissionForm('Records', $USER_PERMS)?>
            </div>
            <div class="col-lg-3">
                <label>
                    <strong>Actions</strong>
                </label>
                <?php echo $USER->render_userPermissionForm('Actions', $USER_PERMS)?>
                <p>&nbsp;</p>
                <label>
                    <strong>Dashboard</strong>
                </label>
                <?php echo $USER->render_userPermissionForm('Dashboard', $USER_PERMS)?>
            </div>                
        </div>
    
    </div>
    
    <div class="m-portlet__foot m-portlet__no-border m-portlet__foot--fit">
        <div class="m-form__actions m-form__actions--solid">
            <div class="row">
                <div class="col-12">
					<a href="/users" class="btn btn-secondary" style="margin-right:5px;"><i class="la la-arrow-left"></i>&nbsp;Back to User List</a>
                    <button type="submit" class="btn btn-success">
						<i class="la la-save"></i>&nbsp;
                        Save User Information
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</form>

<div class="modal fade" id="userModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
	<div class="modal-dialog  modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">&nbsp;</div>
			<div class="modal-body"> 
                <div class="m-alert m-alert--icon m-alert--air m-alert--square alert alert-success alert-dismissible fade show" role="alert">
                    <div class="m-alert__icon">
                        <i class="la la-check"></i>
                    </div>
                    <div class="m-alert__text">
                        <strong>
                            SUCCESS
                        </strong>
                        <span id="alert-text-area">User record created</span>
                    </div>
                </div>               
                <div class="row">
                	<div class="col-6">
                    	<a href="/users" class="btn btn-secondary btn-lg btn-block"><i class="la la-long-arrow-left"></i> Return to User List</a>
					</div>
					<div class="col-6">
                    	<a href="/users/ID" id="newRecordLink" class="btn btn-primary btn-lg btn-block"><i class="la la-user"></i> Go to User Record</a>
					</div>                                                
                </div>
			</div>
			<div class="modal-footer">&nbsp;</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function(e) {
    $('#user_password').val('');
});
function getUserClass() {
	classID = $('#userClass_id').val();
	$.post('/ajax/userMgt.php?action=getuserClass', {
		id: classID
	}, function(data) {
		console.log(data);	
		$('.permissionChoice').each(function() {
			$(this).prop('checked', false);
			for(l=0; l<data.Permissions.length; l++) {
				console.log(data.Permissions[l]);
				if($(this).val() == data.Permissions[l]) {
					$(this).prop('checked', true);
				}
			}
		});
	}, "json");
}
function spoofUser(user_id) {
	$.post('/ajax/userMgt.php?action=spoofUser', {
		user		: 	user_id,
		kiss_token	:	'<?php echo $SESSIONS->createToken()?>',
		puser		:	'<?php echo $_SESSION['system_user_id']?>'	
	}, function(data) {
		document.location.href='/home';		
	});
}
function updateUser() {
	var error = 0;
	var errorTxt = '';
	// VALIDATE FORM //
	var fName = $('#firstName').val();
	if(fName == ''){
		error = 1;
		$('#form_firstName').addClass('has-danger');
		$('#form_firstName .form-control-feedback').show();
	} else {
		$('#form_firstName').removeClass('has-danger');
		$('#form_firstName .form-control-feedback').hide();
	}
	
	var lName = $('#lastName').val();
	if(lName == ''){
		error = 1;
		$('#form_lastName').addClass('has-danger');
		$('#form_lastName .form-control-feedback').show();
	} else {
		$('#form_lastName').removeClass('has-danger');
		$('#form_lastName .form-control-feedback').hide();
	}
	
	var uName = $('#user_username').val();
	if(uName == ''){
		error = 1;
		$('#form_username').addClass('has-danger');
		$('#form_username .form-control-feedback').show();
	} else {
		$('#form_username').removeClass('has-danger');
		$('#form_username .form-control-feedback').hide();
	}
	/*
	var passW = $('#user_password').val();
	if(passW == ''){
		error = 1;
		$('#form_password').addClass('has-danger');
		$('#form_password .form-control-feedback').show();
	} else {
		$('#form_password').removeClass('has-danger');
		$('#form_password .form-control-feedback').hide();
	}
	*/
	
	/*
	var email = $('#email').val();
	if(email == '') {
		error = 1;
		$('#form_email').addClass('has-danger');
		$('#form_email .form-control-feedback').show();
	} else {
		if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
			$('#form_email').removeClass('has-danger');
			$('#form_email .form-control-feedback').hide();
			//error = 0;
		} else {
			$('#form_email').addClass('has-danger');
			$('#form_email .form-control-feedback').show();
			error = 1;
		}
	}
	*/
	
	var gChoice = 0;
	$('.userGender').each(function() {
		if($(this).is(':checked')) {
			gChoice = 1;
		}
	});
	if(gChoice == 0) {
		$('#form_userGender').addClass('has-danger');
		$('#form_userGender .form-control-feedback').show();
		error = 1;
	} else {
		$('#form_userGender').removeClass('has-danger');
		$('#form_userGender .form-control-feedback').hide();
	}
	
	var pChoice = 0;
	$('.permissionChoice').each(function() {
		if($(this).is(':checked')) {
			pChoice = 1;
		}
	});
	if(pChoice == 0) {
		$('#permErrorAlert').show();
		error = 1;
	} else {
		$('#permErrorAlert').hide();
	}
	
	var sChoice = 0;
	$('.userStatus').each(function() {
		if($(this).is(':checked')) {
			sChoice = 1;
		}
	});
	if(sChoice == 0) {
		$('#form_userStatus').addClass('has-danger');
		$('#form_userStatus .form-control-feedback').show();
		error = 1;
	} else {
		$('#form_userStatus').removeClass('has-danger');
		$('#form_userStatus .form-control-feedback').hide();
	}

	var uClass = $('#userClass_id').val();
	if(uClass == ''){
		error = 1;
		$('#form_userClass').addClass('has-danger');
		$('#form_userClass .form-control-feedback').show();
	} else {
		$('#form_userClass').removeClass('has-danger');
		$('#form_userClass .form-control-feedback').hide();
	}
	if(error == 1) {
		alert('Error');
	} else {
		var formData = $('#userForm').serializeArray();
		$.post('/ajax/userMgt.php?action=update', formData, function(data) {
			console.log(data);
			if (data.type == 'new') {
				//toastr.success('New user record created', '', {timeOut: 8000});
				var message = 'New user record created';
				clearForm();
			} else {
				var message = 'User record updated';
			}
			$('#alert-text-area').html(message);
			$('#newRecordLink').prop('href', '/users/'+data.uid);
			$('#userModal').modal('show');
		}, "json");
	}
}
function clearForm() {
	var fName = $('#firstName').val('');
	var lName = $('#lastName').val('');
	var uName = $('#username').val('');
	var passW = $('#password').val('');
	var email = $('#email').val('');
	$('.userGender').each(function() {
		$(this).prop('checked', false);
	});
	$('.permissionChoice').each(function() {
		$(this).prop('checked', false);
	});
	var sChoice = 0;
	$('.userStatus').each(function() {
		$(this).prop('checked', false);
	});
	var uClass = $('#userClass_id').val('');	
}
function togglePassword() {
	var type = $('#password').prop('type');
	if(type == 'password') {
		$('#password').prop('type', 'text');
	} else {
		$('#password').prop('type', 'password');
	}
}


</script>