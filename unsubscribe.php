<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.marketing.php");

$DB = new database();
$DB->connect();

$marketing = new Marketing();
$msg = '';
$show_form = true;

if($_POST['submitted'] == 1) {
    if(strlen($_POST['email']) > 0 && $marketing->valid_email($_POST['email'], true)) {
        $result = $marketing->process_unsubscribe($_POST['email']);
        $msg = '<h2>Unsubscribe Successful</h2><p>The e-mail address \''.$_POST['email'].'\' has been successfully unsubscribed.</p>';
		$show_form = false;
    } else {
        $msg = '<div class="alert alert-danger" role="alert"><strong>ERROR:</strong> The e-mail address you entered is invalid. Please correct any typos, and then re-submit.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en" >
<head>
	<meta charset="utf-8" />
    <title>Kelleher International Matchmaking Unsubscribe</title>
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
	</style>	    
</head>
<body>
	<div class="container">
		<div class="row">
		<div class="col-12" style="margin-top:30px;">
		<?php echo $msg?>
		<?php if($show_form) { ?>
		<h2>Unsubscribe</h2>
		<form id="unsub_form" name="unsub_form" method="post" action="/unsubscribe.php" class="form-horizontal">
			<input name="submitted" value="1" type="hidden" />
			Please enter the e-mail address that you wish to opt-out from future marketing e-mails below:
			<div class="form-group m-form__group" style="margin-top:20px;">
				<label for="email" class="col-4 control-label">E-Mail Address</label>
				<div class="col-8">
					<input type="text" value="<?php echo $_POST['email']?>" name="email" id="email" class="form-control" required />
				</div>
			</div>
			<div class="form-group m-form__group">
				<div class="col-12">
					<button type="submit" class="btn btn-primary m-btn m-btn--pill m-btn--custom m-btn--air">Submit</button>
				</div>
			</div>
		</form>
		<?php } ?>
		</div>
		</div>
	</div>
</body>
</html>