<?php
include_once("class.db.php");
include_once("class.record.php");
include_once("class.forms.php");
include_once("class.matching.php");

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$FORMS = new Forms($DB);
$MATCHING = new Matching($DB, $RECORD);


$ERROR = 1;
$ck_sql = "SELECT * FROM DateLinks WHERE LinkHash='".$_GET['id']."'";
$ck_send = $DB->get_single_result($ck_sql);
if(isset($ck_send['empty_result'])) {
	$ERROR = 1;
} else {
	$timeout = $ck_send['Expiration'];
	//if($timeout < time()) {
		//$ERROR = 1;	
	//} else {
		$ERROR = 0;
		$DateID = $ck_send['DateID'];
		$ViewerID = $ck_send['PersonID'];
		$d_query = "SELECT * FROM PersonsDates WHERE PersonsDates_id='".$DateID."'";
		$d_data = $DB->get_single_result($d_query);
		
		$DisplayDate = date("l M jS", $d_data['PersonsDates_dateExecuted']);
		$ShortDate = date("m/d/y", $d_data['PersonsDates_dateExecuted']);
		$DisplayTime = date("g:i a", $d_data['PersonsDates_dateExecuted']);
		$DateStatusText = $MATCHING->get_dateStatusText($d_data['PersonsDates_status']);
		$DateIsComplete = $d_data['PersonsDates_isComplete'];
		
		if($d_data['PersonsDates_participant_1'] == $ViewerID) {
			$introWith = $d_data['PersonsDates_participant_2'];
			$myStatus = $d_data['PersonsDates_participant_1_status'];
		} else {
			$introWith = $d_data['PersonsDates_participant_1'];
			$myStatus = $d_data['PersonsDates_participant_2_status'];
		}
		
		$primaryImage = $RECORD->get_PrimaryImage($introWith);
		$imgWidth = 100;
		ob_start();
		?>
        <div style="background-image:url('<?php echo $primaryImage?>'); background-size:cover; height:<?php echo $imgWidth?>px; width:<?php echo $imgWidth?>px; margin-bottom:10px;">
            <img src="/assets/app/media/img/users/filler.png" style="height:<?php echo $imgWidth?>px; width:<?php echo $imgWidth?>px;">
        </div>
        <?php
		$LeftImage = ob_get_clean();		
		$LeftMnum = $RECORD->get_personFirstName($introWith).' ('.$introWith.')';
		
		ob_start();		
		$deb_sql = "SELECT * FROM Questions WHERE QuestionsCategories_id='15' AND Questions_active='1' ORDER BY Questions_order ASC";
		//echo $deb_sql;
		$deb_snd = $DB->get_multi_result($deb_sql);
		foreach($deb_snd as $deb_dta):
			switch($deb_dta['QuestionTypes_id']):
				case '4':
				$FORMS->form_radioField('debrief_'.$deb_dta['Questions_id'], $deb_dta['Questions_text'], '', true, $FORMS->get_fieldOptions($deb_dta['Questions_id']), array(5,7));
				break;
				
				case '3':
				$FORMS->form_selectField('debrief_'.$deb_dta['Questions_id'], $deb_dta['Questions_text'], '', true, $FORMS->get_fieldOptions($deb_dta['Questions_id']), array(5,7));
				break;
				
				case '2':
				$FORMS->form_textAreaField('debrief_'.$deb_dta['Questions_id'], $deb_dta['Questions_text'], '', true, array(5,7));
				break;
				
				case '1':
				$FORMS->form_textField('debrief_'.$deb_dta['Questions_id'], $deb_dta['Questions_text'], '', true, false, array(5, 7));
				break;
				
				case '5':
				$FORMS->form_checkboxField('debrief_'.$deb_dta['Questions_id'], $deb_dta['Questions_text'], '', true, $FORMS->get_fieldOptions($deb_dta['Questions_id']), array(5,7));
				break;
				
				
			endswitch;		
		endforeach;
		$debriefForm = ob_get_clean();
		
		// check state of the date overall //
		if (($d_data['PersonsDates_status'] != 3) && ($d_data['PersonsDates_status'] != 4) && ($d_data['PersonsDates_status'] != 6)) {
			$ERROR = 2;
		}
		
		if (($myStatus == 3)) {
			$leftShowForm = true;
		} else {
			$leftShowForm = false;
		}
		
		if (($myStatus == 4) || ($myStatus == 6)):
			$ERROR = 0;
		else:
			$ERROR = 3;
		endif;		
		$count++;
	//}
}

?>
<!DOCTYPE html>
<html lang="en" >
<head>
	<meta charset="utf-8" />
    <title>(KISS) DEBRIEFING</title>
    <meta name="description" content="Latest updates and statistic charts">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
		    
</head>
<body>
<div class="container">
<p><img src="//www.kelleher-international.com/inventory/images/header_logo10_14.jpg" class="img-fluid"/></p>
<?php
if($ERROR == 1):
	?><div class="alert alert-danger"><h3>ERROR</h3>The introduction link has either expired or is no longer in existance.</div><?php
elseif($ERROR == 2):
	?><div class="alert alert-warning"><h3>WARNING</h3>This date is not currently in the debriefing stage.</div><?php
else:
	?>
<h3>Welcome <?php echo $RECORD->get_personName($ViewerID)?></h3>
<p>Please take a few moments to fill out our debriefing form in regards to your recent introduction to: <strong><?php echo $LeftMnum?></strong></p>
<div class="row">
    <div class="col-md-12">
<form class="m-form m-form--fit m-form--label-align-right" id="<?php echo $_GET['id']?>" action="submit-debriefing.php" method="post">
    <input type="hidden" name="formID" value="<?php echo $_GET['id']?>">
    <input type="hidden" name="PersonID" value="<?php echo $ViewerID?>">
    <input type="hidden" name="DateID" value="<?php echo $DateID?>">       	
	<?php if($ERROR == 0): ?>
	<?php echo $debriefForm?>
    <div class="m-form__actions">
    	<div class="row">
        	<div class="col-md-12 text-right">
		        <button type="submit" class="btn btn-primary">
        		    Submit Debriefing
        		</button>
			</div>
		</div>
    </div>
    <?php else: ?>
    <div class="alert alert-success">Your Debriefing has been received</div>
    <?php endif; ?>
</form>    
	</div>
</div>    
<?php
endif;
?>
</div>
</body>
</html>
	