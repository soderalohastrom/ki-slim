<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.encryption.php");

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$ENC = new encryption(); 
$DB->setTimeZone();
?>
<!DOCTYPE html>
<html lang="en" >
<head>
	<meta charset="utf-8" />
    <title>Kelleher International Matchmaking Contract View</title>
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!--begin::Base Scripts -->
    <script src="/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
    <script src="/assets/demo/default/base/scripts.bundle.full.js" type="text/javascript"></script>
    <!--end::Base Scripts -->   
    <!--begin::Page Vendors -->
    <script src="/assets/vendors/custom/fullcalendar/fullcalendar.bundle.js" type="text/javascript"></script>
    <!--<script src="/assets/vendors/custom/bootstrap3-editable/js/bootstrap-editable.js"></script>-->
    <script type="text/javascript" src="/assets/vendors/custom/twbs-pagination/jquery.twbsPagination.min.js"></script>
    <script type="text/javascript" src="/assets/vendors/custom/pStrength-master/pStrength.jquery.js"></script>
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
	
    <style>
	.entry-field {
		background-color:#deffe0;
	}	
	</style>	    
</head>
<body>

<?php
$login_sql = "SELECT * FROM Users WHERE Email='".$DB->mysqli->escape_string($ENC->decrypt($_GET['id']))."'";
$login_snd = $DB->get_single_result($login_sql);
if(isset($login_snd['empty_result'])):
	?><div class="alert alert-danger">Invalid Password Request</div><?php
else:
	$PID = $login_snd['user_id'];
	?>
<div class="container">
<form class="m-login__form m-form" action="" id="m-form-reset-pass">
    <input type="hidden" id="pid" value="<?php echo $PID?>">
    <div class="form-group m-form__group">
        <input class="form-control m-input" type="password" id="pass_1" placeholder="Password" name="password" data-display="myDisplayElement1"> 
    </div>
    <div class="row">
    	<div class="col-4">        
		    <div class="progress m-progress--sm">
        		<div class="progress-bar m--bg-danger" id="passwordProgress" role="progressbar" style="width:100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
    		</div>
		</div>
        <div class="col-8">            
		    <div class="left" id="myDisplayElement1"></div>
		</div>
	</div>                    
    <div class="form-group m-form__group">
        <input class="form-control m-input m-login__form-input--last" id="pass_2" type="password" placeholder="Confirm Password" name="rpassword" data-display="myDisplayElement2"> <div class="left" id="myDisplayElement2"></div>
    </div>
    <div class="m-login__form-action">
    	<div class="row">
        	<div class="col-4">
		        <button type="button" id="m_login_signup_submit" class="btn btn-danger m-btn m-btn--pill m-btn--custom m-btn--air" disabled>
        		    Reset Password
        		</button>
			</div>
            <div class="col-8">
            	<div class="alert alert-info" role="alert">
                    <strong>
                        NOTE
                    </strong>
                    Your password should include at least 2 numbers and 1 special character (@!$#% etc) and be at least 8 characters long
                </div>                
			</div>
		</div>                            
    </div>
</form>    
</div>
    <?php
endif;	
?>



<script>
$(document).ready(function(e) {
	$(document).on('click', '#m_login_signup_submit', function() {
		submitPassReset();		
	});
	
	$('#pass_1').pStrength({
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
			$('#passwordProgress').css('width', strengthPercentage+'%');
			$('#passwordProgress').addClass('m--bg-danger');
			$('#passwordProgress').removeClass('m--bg-success');
			$('#m_login_signup_submit').attr('disabled', true);			
        },
        'onValidatePassword': function(strengthPercentage) {
            $('#' + $(this).data('display')).html(
                $('#' + $(this).data('display')).html() + ' Great, now you can continue to reset your password!'
            );
			//alert('resetting Password');
			$('#passwordProgress').removeClass('m--bg-danger');
			$('#passwordProgress').addClass('m--bg-success');
			$('#m_login_signup_submit').attr('disabled', false);
			//submitPassReset();
           /*
		    $('#myForm').submit(function(){
                return true;
            });
			*/
        }
    });
});
function submitPassReset() {
	var pass_1 = $('#pass_1').val();
	var pass_2 = $('#pass_2').val();
	if(pass_1 == pass_2) {
		$.post('passsetNew.php', {
			pid: 	$('#pid').val(),
			pass:	pass_1
		}, function(data) {
			alert('Password Reset');
			document.location.href='/';
		});
	} else {
		alert('Passwords do not match');
	}
}
</script>
</body>
</html>