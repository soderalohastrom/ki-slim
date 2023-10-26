<?php
include_once("class.users.php");
$USER = new Users($DB);

function generate_timezone_list()
{
    static $regions = array(
        DateTimeZone::AFRICA,
        DateTimeZone::AMERICA,
        DateTimeZone::ANTARCTICA,
        DateTimeZone::ASIA,
        DateTimeZone::ATLANTIC,
        DateTimeZone::AUSTRALIA,
        DateTimeZone::EUROPE,
        DateTimeZone::INDIAN,
        DateTimeZone::PACIFIC,
    );

    $timezones = array();
    foreach( $regions as $region )
    {
        $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
    }

    $timezone_offsets = array();
    foreach( $timezones as $timezone )
    {
        $tz = new DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
    }

    // sort timezone by offset
    asort($timezone_offsets);

    $timezone_list = array();
    foreach( $timezone_offsets as $timezone => $offset )
    {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate( 'H:i', abs($offset) );

        $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

        $timezone_list[$timezone] = "(${pretty_offset}) $timezone";
    }

    return $timezone_list;
}

$sql = "SELECT * FROM Users WHERE user_id='".$_SESSION['system_user_id']."'";
$user_dta = $DB->get_single_result($sql);

if($user_dta['userTimezone'] == '') {
	$user_dta['userTimezone'] = 'America/Los_Angeles';
}

$tzlist = generate_timezone_list();
//print_r($tzlist);
$tzKeys = array_keys($tzlist);
ob_start();
foreach($tzKeys as $key):
	?><option value="<?php echo $key?>" <?php echo (($user_dta['userTimezone'] == $key)? 'selected':'')?>><?php echo $tzlist[$key]?></option><?php
endforeach;
$tzSelect = ob_get_clean();

?>
<script type="text/javascript" src="/assets/vendors/custom/pStrength-master/pStrength.jquery.js"></script>
<div class="m-content">

<div class="m-portlet">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <span class="m-portlet__head-icon">
                    <i class="flaticon-profile-1"></i>
                </span>
                <h3 class="m-portlet__head-text">
                    My User Profile
                </h3>
            </div>
        </div>
    </div>
    <!--begin::Form-->
    <form class="m-form">
    	<input type="hidden" id="current_user_id" value="<?php echo $_SESSION['system_user_id']?>" />
        <?php echo $SESSIONS->renderToken()?>
        <div class="m-portlet__body">
            <div class="m-form__section m-form__section--first">
                <div class="row">
                	<div class="col-6">
                        <div class="form-group m-form__group">
                            <label for="example_input_full_name">
                                Full Name:
                            </label>
                            <input type="text" class="form-control m-input m-input--solid" value="<?php echo $user_dta['firstName']?> <?php echo $user_dta['lastName']?>" readonly="readonly">
                        </div>
                        <div class="form-group m-form__group">
                            <label>
                                Email address:
                            </label>
                            <input type="email" class="form-control m-input m-input--solid" value="<?php echo $user_dta['email']?>" readonly="readonly">
                        </div>
                        
                        <div class="form-group m-form__group">
                            <label>
                                Password:
                            </label>
                            <div class="input-group m-input-group">
                                <span class="input-group-addon" id="basic-addon1">
                                    <i class="la la-lock"></i>
                                </span>
                                <input type="password" class="form-control m-input" id="passwordField" name="password" value="" data-display="myDisplayElement1">
                                <!--
                                <span class="input-group-btn">
                                	<button type="button" class="btn m-btn btn-secondary" id="button-view-password">View</button>
                                </span>
                                -->
                                <span class="input-group-btn">
                                	<button type="button" class="btn m-btn btn-primary" id="button-save-password">Save</button>
                                </span>                                
                            </div>
                            <span class="m-form__help">
                            	<div class="left" id="myDisplayElement1"></div>
                                <div class="left" id="myDisplayElement2">Leave password blank to keep existing password.</div>
                            </span>                               
                        </div>
                        <div class="form-group m-form__group">
                            <label>
                                Personal Meeting Room URL:
                            </label>
                            <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-globe"></i></span>
                            <input type="text" class="form-control m-input" name="userMeetingURL" id="userMeetingURL" value="<?php echo $user_dta['userMeetingURL']?>" placeholder="Please enter Meeting room URL" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                            <span class="input-group-btn">
                                <button type="button" class="btn m-btn btn-primary" id="button-save-roomurl">Save</button>
                            </span>                             
                            </div>
                        </div>
                        <div class="form-group m-form__group">
                            <label>
                                Timezone:
                            </label>
                            <select name="userTimezone" id="userTimezone" class="form-control m-input">
                            	<?php echo $tzSelect?>
                            </select>                          
                        </div>                        
					</div>
                    <div class="col-6">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group m-form__group">
                                    <label>Default Avatar:</label>
                                    <div><img id="previewImage" src="<?php echo $USER->get_userImage($_SESSION['system_user_id'])?>" class="m--marginless img-responsive" alt=""/></div>
                                </div>
                            </div>
                            <div class="col-md-5">                                
                                <div class="m-dropzone dropzone" action="/ajax/upload.avatar.php?uid=<?php echo $_SESSION['system_user_id']?>" id="m-dropzone-one">
                                    <div class="m-dropzone__msg dz-message">
                                        <h3 class="m-dropzone__msg-title">Drop image here or click to upload.</h3>
                                        <span class="m-dropzone__msg-desc">This image will be resized to optimize its use in the system.</span>
                                    </div>
                                </div>
                            
                            </div>                
                        </div>                                                
					</div>
				</div>                                            
                
                
                
            </div>
        </div>
        <div class="m-portlet__foot m-portlet__foot--fit m--hide">
            <div class="m-form__actions m-form__actions">
                <button type="reset" class="btn btn-primary">
                    Submit
                </button>
                <button type="reset" class="btn btn-secondary">
                    Cancel
                </button>
            </div>
        </div>
    </form>
    <!--end::Form-->
</div>



</div>


<script>
var DropzoneDemo = function() {
    var e = function() {
        Dropzone.options.mDropzoneOne = {
            paramName: "file",
            maxFiles: 1,
            maxFilesize: 5,
			acceptedFiles: "image/*",
            accept: function(e, o) {
				console.log(e);						
                "justinbieber.jpg" == e.name ? o("Naha, you don't.") : o()
            },
			init: function() {
				this.on("success", function(file, response) {
					//alert('image uploaded');
					var obj = jQuery.parseJSON(response)
					console.log(obj);
					//$('#previewImage').attr('src', obj.imgStuff);
					document.location.reload(true);
				})
    			//this.on("addedfile", function(file) { alert("Added file."); });
			}
        }
    };
    return {
        init: function() {
            e()
        }
    }
}();
DropzoneDemo.init();

$(document).ready(function(e) {    	
	$('#passwordField').pStrength({
        'changeBackground'          : false,
		'passwordValidFrom'			: 60, // 60%
        'onPasswordStrengthChanged' : function(passwordStrength, strengthPercentage) {
            if ($(this).val()) {
                $.fn.pStrength('changeBackground', $(this), passwordStrength);
            } else {
                $.fn.pStrength('resetStyle', $(this));
            }
            $('#' + $(this).data('display'))
                .html('Your password strength is ' + strengthPercentage + '%');
			//$('#passwordProgress').css('width', strengthPercentage+'%');
			//$('#passwordProgress').addClass('m--bg-danger');
			//$('#passwordProgress').removeClass('m--bg-success');
			$('#button-save-password').attr('disabled', true);			
        },
        'onValidatePassword': function(strengthPercentage) {
            $('#' + $(this).data('display')).html(
                $('#' + $(this).data('display')).html() + ' Great, now you can continue to reset your password!'
            );
			//alert('resetting Password');
			//$('#passwordProgress').removeClass('m--bg-danger');
			//$('#passwordProgress').addClass('m--bg-success');
			$('#button-save-password').attr('disabled', false);
        }
    });

	$(document).on('change','#userTimezone', function() {
		$.post('/ajax/mystuff.php?action=setUserTimezone', {
			pid: $('#current_user_id').val(),
			tz:	$(this).val(),
			kiss_token: $('#kiss_token').val()
		}, function(data) {
			toastr.success('Timezone Set', '', {timeOut: 5000});			
		});
	});
	
	$(document).on('click', '#button-save-roomurl', function() {
		$('#button-save-roolurl').addClass('m-loader m-loader--light m-loader--right');
		$.post('/ajax/mystuff.php?action=setroom', {
			pid: $('#current_user_id').val(),
			room:	$('#userMeetingURL').val(),
			kiss_token: $('#kiss_token').val()
		}, function(data) {
			$('#button-save-roomurl').removeClass('m-loader m-loader--light m-loader--right');
			toastr.success('Meeting Room URL Set', '', {timeOut: 5000});			
		});
		
		
	});
	
	$(document).on('click', '#button-view-password', function() {
		if($('#passwordField').prop('type') == 'password') {
			$('#passwordField').prop('type', 'text');
			$('#button-view-password').html('Hide');
		} else {
			$('#passwordField').prop('type', 'password');
			$('#button-view-password').html('Show');
		}
	});
	
	$(document).on('click', '#button-save-password', function() {
		$('#button-save-password').addClass('m-loader m-loader--light m-loader--right');
		$.post('/ajax/mystuff.php?action=setPassword', {
			pid: $('#current_user_id').val(),
			pswd:	$('#passwordField').val(),
			kiss_token: $('#kiss_token').val()
		}, function(data) {
			$('#button-save-password').removeClass('m-loader m-loader--light m-loader--right');
			toastr.success('Password Set', '', {timeOut: 5000});			
		});
	});
});

</script>
