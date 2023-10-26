<?php
include_once("class.db.php");
include_once("class.record.php");
include_once("class.forms.php");

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$FORMS = new Forms($DB);

//Number to help
$number = date('H,i,s');

if(isset($_GET['p'])):
	$psql = "SELECT * FROM Persons WHERE PersonID='".$_GET['p']."'";
	$psnd = $DB->get_single_result($psql);
	if(isset($psnd['empty_result'])):
		//echo "INVALID ID";
		$PERSON_ID = 0;
	else:
		$PERSON_ID = $_GET['p'];
	endif;
else:
	$PERSON_ID = 0;
endif;


$sql = "SELECT * FROM CompanyForms WHERE FormCallString='".$_GET['id']."'";
$snd = $DB->get_single_result($sql);	
?>
<!DOCTYPE html>
<html lang="en" >
<head>
	    <meta charset="utf-8" />
    <title>(KISS) FORM</title>
    <meta name="description" content="Latest updates and statistic charts">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php if($snd['Form_excludeIndex'] == 1): ?>
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <?php endif; ?>
    <!--begin::Base Scripts -->
    <script src="/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
    <script src="/assets/demo/default/base/scripts.bundle.full.js" type="text/javascript"></script>
    <!--end::Base Scripts -->   
    <!--begin::Page Vendors -->
    <script src="/assets/vendors/custom/fullcalendar/fullcalendar.bundle.js" type="text/javascript"></script>
    <!--<script src="/assets/vendors/custom/bootstrap3-editable/js/bootstrap-editable.js"></script>-->
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
.m-form .m-form__group {
    margin-bottom: 0;
    padding-top: 7px;
    padding-bottom: 7px;
}
form-group {
	margin-bottom: .2rem;
}
.m-form. m-form--fit .m-form__group .m-form_heading {
	padding-left: 10px;
	paddin-right: 10px
}	
	
	</style>
    

<!-- BEGIN CUSTOM HEADER CODE -->
<?php echo $snd['FormHeader']?>
<!-- END CUSTOM HEADER CODE -->
</head>
<body>
<div class="container-fluid">
<!-- BEGIN CUSTOM BODY CODE -->
<?php echo $snd['FormBody']?>
<!-- END CUSTOM BODY CODE -->
<?php
if(isset($snd['empty_result'])) {
	?><div class="alert alert-danger">Invalid Form ID</div><?php
} else {
	$fdta = $snd;
	$FORMS->updateFormViews($fdta['FormID']);
	$FORMS->updateFullFormViews($fdta['FormID']);
	
	// CUSTOM FORM ONLY CODE //
	if($fdta['FormID'] == 5):
	?><div style="display:inline;"><img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/963023089/?label=ToGFCJafil4Q8aGaywM&amp;guid=ON&amp;script=0"/></div><?php
	?>
    <div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
        <div class="m-alert__icon">
            <i class="fa fa-thumbs-o-up"></i>
            <span></span>
        </div>
        <div class="m-alert__text">
            <strong>
                You're almost done!
            </strong><br>
            In order to ensure we find your perfect match please fill out the additional information below.
        </div>
    </div>
    <?php
	elseif (strip_tags($fdta['FormNextFormMessage']) != ''):
	?>
    <div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-brand alert-dismissible fade show" role="alert" style="margin-top:5px;">
        <div class="m-alert__icon">
            <i class="fa fa-thumbs-o-up"></i>
            <span></span>
        </div>
        <div class="m-alert__text">
        	<?php echo $fdta['FormNextFormMessage']?>    
        </div>
    </div>    
	<?php  
	endif;	
	?>	
<div>&nbsp;</div>	
<!--begin::Form-->
<form class="m-form m-form--fit m-form--label-align-right" id="<?php echo $_GET['id']?>" action="submit-form.php" method="post">
    <input type="hidden" name="formID" value="<?php echo $_GET['id']?>">
    <input type="hidden" name="PersonID" value="<?php echo $PERSON_ID?>">
    <input type="hidden" name="FormHeight" id="FormHeight" value="">
    <input type="hidden" name="number" value="<?php echo $number; ?>">
    <div class="m-portlet__body">
    	<div class="text-center"><small><span class="m--font-danger">*</span> indicates required field.</small></div>
        <?php $FORMS->render_fullForm($fdta['FormID'], $PERSON_ID)?>
    </div>
    <div class="m-portlet__foot m-portlet__foot--fit">
        <div class="m-form__actions" align="center">
            <button type="submit" class="btn btn-metal">
                Submit
            </button>
            <button type="reset" class="btn btn-secondary">
                Cancel
            </button>
        </div>
    </div>
    <input name="webaddress" id="WebAddress" value="">
    
</form>
<!--end::Form-->
    <?php
}
?>
</div>
<script>
$(document).ready(function(e) {
	var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
	$('#FormHeight').val(h);
	//alert(w+'/'+h);
    $('#Country').on('change', function() {
		var country = $('#Country').val();
		$.post('/ajax/select.states.php', {
			country:	country	
		}, function(data) {
			$('#State').html(data);
		});	
	});
	
});
</script>
<script>
    try {
      var postObject = JSON.stringify({
        event: 'iframeFormView', 
        'kiss_formID': '<?php echo $_GET['id']?>',
	    'person_UID': '<?php echo $PERSON_ID?>',
	    'campaign_id': '<?php echo $CAMPAIGN_ID?>',
	    'keyword': '<?php echo $KEYWORD?>',
      });
      parent.postMessage(postObject, 'https://kelleher-international.com/');
      parent.postMessage(postObject, 'https://pages.kelleher-international.com/');
    } catch(e) {
    window.console && window.console.log(e);
    }
</script>

</body>
</html>
