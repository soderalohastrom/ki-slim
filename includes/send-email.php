<?php
include_once("class.settings.php");
include_once("class.marketing.php");
include_once("class.templates.php");
include_once("class.record.php");
$marketing = new Marketing();
$SETTINGS = new Settings();
$template_obj = new Templates();
$_SESSION['ckfinder_url'] = $SETTINGS->setting['BASE_URL'].'assets/vendors/modules/ckfinder/userfiles/';
$invalid = false;
$bad_email = false;
$RECORD = new Record($DB);
include_once("assets/vendors/modules/htmlpurifier-4.10.0/library/HTMLPurifier.auto.php");
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);

require_once  __DIR__ . '/../vendor/telerik/wrappers/php/lib/Kendo/Autoload.php';

    $editor = new \Kendo\UI\Editor('EmailBodyContent');

   
	
// declare snippets
// add snippets to insertHtml tool
$insertHtml = new \Kendo\UI\EditorTool();
$insertHtml->name("insertHtml");
foreach($SETTINGS->setting['MERGE_FIELDS'] as $merge_field_id=>$merge_field) : 
	$signature = new \Kendo\UI\EditorToolItem();
	$signature->text("Merge Field: " . $merge_field['display'])->value($merge_field_id);
	$insertHtml->addItem($signature);
endforeach;


// fILE BROWSER SETUP

 // enable all tools
 $editor->addTool(
	$insertHtml,"bold", "italic", "underline", "strikethrough",
	"justifyLeft", "justifyCenter", "justifyRight", "justifyFull",
	"insertUnorderedList", "insertOrderedList", "insertUpperRomanList", "insertLowerRomanList", "indent", "outdent",
	"subscript", "superscript",
	"tableWizard", "createTable", "addRowAbove", "addRowBelow", "addColumnLeft", "addColumnRight", "deleteRow", "deleteColumn", "tableAlignLeft", "tableAlignCenter", "tableAlignRight",
	"mergeCellsHorizontally", "mergeCellsVertically", "splitCellHorizontally", "splitCellVertically",
	"fontName",
	"fontSize",
	"foreColor",
	"backColor",
	"print",
	"insertImage","viewHtml"
);
// configure image browser
$imageBrowser = new \Kendo\UI\EditorImageBrowser();

$imageBrowser_transport = new \Kendo\UI\EditorImageBrowserTransport();
$imageBrowser_transport->thumbnailUrl('/assets/ImageBrowser.php?action=thumbnail');
$imageBrowser_transport->uploadUrl('/assets/ImageBrowser.php?action=upload');
$imageBrowser_transport->imageUrl('/assets/ImageBrowser.php?action=image&path={0}');

$imageBrowser_transport->read('/assets/ImageBrowser.php?action=read');
$imageBrowser_destroy = new \Kendo\UI\EditorImageBrowserTransportDestroy();
$imageBrowser_destroy
	->url('/assets/ImageBrowser.php?action=destroy')
	->type('POST');
$imageBrowser_transport->destroy($imageBrowser_destroy);
$imageBrowser_create = new \Kendo\UI\EditorImageBrowserTransportCreate();
$imageBrowser_create
	->url('/assets/ImageBrowser.php?action=create')
	->type('POST');
$imageBrowser_transport->create($imageBrowser_create);
$imageBrowser->transport($imageBrowser_transport);

$editor->imageBrowser($imageBrowser);

$pasteCleanup = new \Kendo\UI\EditorPasteCleanup();
    $pasteCleanup->all(false)
                 ->css(false)
                 ->keepNewLines(false)
                 ->msAllFormatting(false)
                 ->msConvertLists(true)
                 ->msTags(true)
                 ->none(false)
                 ->span(false);


    $editor
        ->attr('style', 'width:100%;height:400px')
		->encoded(false)
		->content($_POST['EmailBodyContent']);
    

unset($SETTINGS->setting['MERGE_FIELDS']['#ViewOnlineLink']);	//unset the "view online link" because it only applies to deployments

function get_person_info($id) {
	global $DB;
	$sql = "SELECT Email, FirstName, LastName, EmailStatus FROM Persons WHERE Person_id = '".$id."'";
	$result = $DB->get_single_result($sql);
	return $result;
}

function get_user_info($id) {
	global $DB;
	$sql = "SELECT email as Email, firstName as FirstName, lastName as LastName FROM Users WHERE user_id = '".$id."'";
	$result = $DB->get_single_result($sql);
	return $result;
}
$MY_INFO = get_user_info($_SESSION['system_user_id']);
$MY_EMAIL = explode("@", $MY_INFO['Email']);
$MY_EMAIL_USERNAME = $MY_EMAIL[0];

if(!array_key_exists('params', $pageParamaters) || count($pageParamaters['params']) == 0) {
	$invalid = true;
} else {
	$person_id = $pageParamaters['params'][0];
	$person_data = get_person_info($person_id);
	if(array_key_exists('empty_result', $person_data) || array_key_exists('error', $person_data)) {
		$invalid = true;
	} elseif(empty($person_data['Email']) || !$marketing->valid_email($person_data['Email'], true) || $person_data['EmailStatus'] == 2) {
		$invalid = true;
		$bad_email = true;
	}
	
	if(isset($pageParamaters['params'][1])) {
		$intro_id = $pageParamaters['params'][1];
	}
	
	if(isset($pageParamaters['params'][2])) {
		$bio_id = $pageParamaters['params'][2];
	}
}

if(!$invalid) {
	$user_data = get_user_info($_SESSION['system_user_id']);
	$whitelist = explode(',', $SETTINGS->setting['DOMAIN_WHITELIST']);
	
	if(isset($_POST['FromAddr'])) {
		$fromaddr_left = $_POST['FromAddr'];
		$fromaddr_right = $_POST['FromDomain'];
	} elseif($user_data['Email'] != '') {
		$at_pos = strpos($user_data['Email'], '@');
		$fromaddr_left = substr($user_data['Email'], 0, $at_pos);
		$fromaddr_right = substr($user_data['Email'], ($at_pos+1));
	}
	
	if(isset($_POST['FromAddr'])) {	//validate form fields
		$errors = array();
		$cc_arr = array();
		if(strlen($_POST['ToBCC']) > 0) {
			$bcc_arr = explode(',', $_POST['ToBCC']);
			foreach($bcc_arr as $bcc) {
				if(!$marketing->valid_email($bcc, true)) {
					$errors[] = "BCC: $bcc is not a valid email address";
				}
			}
		} else {
			$bcc_arr = array();
		}
		if(strlen($_POST['FromName']) == 0) {
			$errors[] = 'From Name cannot be empty';
		}
		if(strlen($_POST['FromAddr']) == 0) {
			$errors[] = 'From Address cannot be empty';
		} else {
			$from_addr = $purifier->purify($_POST['FromAddr']).'@'.$_POST['FromDomain'];
			if(!$marketing->valid_email($from_addr, true)) {
				$errors[] = "From Address is not a valid email address";
			}
		}
		if(strlen($_POST['Subject']) == 0) {
			$errors[] = 'Subject cannot be empty';
		}
		if(strlen($_POST['EmailBodyContent']) == 0) {
			$errors[] = 'Body cannot be empty';
		}
		if(count($errors) == 0) {	//send the email
			include('assets/vendors/modules/html2text/html2text.php');
			$email_body = $_POST['EmailBodyContent'];
			$merge_data = $marketing->get_merge_data(0, $person_id, 0, 0);
			foreach($SETTINGS->setting['MERGE_FIELDS'] as $merge_field=>$merge_val) {
				$email_body = str_replace($merge_field, $merge_data[$merge_field], $email_body);
			}
			$email_body = $purifier->purify($email_body);
			$email_body_text = convert_html_to_text($email_body);
			$send_time = time();
			$email_sent = $marketing->send_oneoff_email($purifier->purify($_POST['Subject']), array($person_data['Email']), $cc_arr, $bcc_arr, $purifier->purify($_POST['FromName']), $from_addr, $from_addr, $email_body, $email_body_text, $person_id, 0, '', false, false, false, false, array(), array('sendgrid_id' => $person_id.'-'.$send_time), $send_time);
			if($email_sent === false) {
				$email_failed = true;
			}
			$DB->log_user_action('Email sent to <a href="/profile/'.$person_id.'">'.$RECORD->get_personName($person_id).'</a>: '.$_POST['Subject'], $person_id, 'PERSON', $_SESSION['system_user_id']);
		}
	}
}
?>
<link rel="stylesheet" href="/vendor/telerik/styles/kendo.common.min.css" />
<link rel="stylesheet" href="/vendor/telerik/styles/kendo.default.min.css" />
<link rel="stylesheet" href="/vendor/telerik/styles/kendo.default.mobile.min.css" />
<script src="/vendor/telerik/js/kendo.all.min.js"></script>

<style>
.ck-editor__editable_inline {
    min-height: 400px;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
	$('#TemplateSelect').select2({
  theme: "classic"
});
	$('#MergeFieldSelect').select2({ theme: "classic" });
	
	$('#TemplateSelect').on('select2:select', function (e) {
		var data = e.params.data;
		if(data.id.length > 0) {
			$.ajax(
			{
				type:     'POST',
				url:      '/ajax/ajax.deployments.php',
				dataType: 'json',
				data:    
				{
					action      : 'load_template',
					template_id : data.id,
					fill_merge  : '1',
					person_id : '<?php echo $person_id?>'
				},
				success:  function(data)
				{
					if(data.subject.length > 0)
					{
						$('#Subject').val(data.subject);
					}
					
					if(data.from.length > 0)
					{
						$('#FromAddr').val(data.fromacct);
						$('#FromDomain').val(data.fromdomain); 
					}
					
					if(data.fromname.length > 0)
					{
						$('#FromName').val(data.fromname);
					}
					console.log(editor);
					var editor = $("#EmailBodyContent").data("kendoEditor");          
					editor.value(data.html);
            
	
					
					$('#TemplateSelect').val('');
					$('#TemplateSelect').trigger('change.select2');					
					checkAccount();
				}
			});
		}
	});
	
});
</script>
<input type="hidden" id="my-email-account" value="<?php echo $MY_EMAIL_USERNAME?>" />
<div class="m-content">
<div class="row">
<div class="col-lg-12">
<?php if($bad_email) { ?>
	<div class="alert alert-danger">
		ERROR: Emails cannot be sent to this person because they do not have a valid email address.<br /><br />
		<button type="button" class="btn btn-sm btn-default" onclick="document.location='/profile/<?php echo $person_id?>'"><i class="la la-arrow-left"></i>&nbsp;Back to Person Record</button> 
	</div>
<?php } elseif($invalid) { ?>
	<div class="alert alert-danger">
		ERROR: Missing or Invalid Person ID
	</div>
<?php } else { ?>
	<?php if(is_array($errors) && count($errors) > 0) { ?>
		<div class="alert alert-danger">
			<h4>Please correct the following:</h4>
			<?php echo implode('<br />', $errors)?>
		</div>
	<?php } ?>
	<?php if($email_sent) { ?>
		<div class="alert alert-success">
			<span class="la la-check"></span> Email has been sent successfully.
			<div style="margin-top:5px;"><button type="button" class="btn btn-sm btn-default" onclick="document.location='/profile/<?php echo $person_id?>'"><i class="la la-arrow-left"></i>&nbsp;Back to Person Record</button></div>
		</div>
	<?php } else { ?>
		<?php if($email_failed) { ?>
			<div class="alert alert-danger">
				Sorry, an error occurred when attempting to send this email. Please try re-sending in a few minutes. If the problem persists, please contact support for assistance.
			</div>
		<?php } ?>
<form action="/send-email/<?php echo $person_id?>" method="post" enctype="multipart/form-data" name="SendEmailForm" id="SendEmailForm" class="m-form m-form--fit m-form--label-align-right">
<div class="m-portlet">
	<div class="m-portlet__head">
		<div class="m-portlet__head-caption">
			<div class="m-portlet__head-title">
				<h3 class="m-portlet__head-text">
					<i class="la la-envelope"></i> Send Email
				</h3>
			</div>
		</div>
	</div>
	<div class="m-portlet__body">
		<div class="form-group m-form__group row">
			<label for="" class="col-sm-2 col-form-label">To:</label>
			<div class="col-sm-10">
				<input type="text" class="form-control m-input" readonly="readonly" value="<?php echo $person_data['FirstName']?>&nbsp;<?php echo $person_data['LastName']?>&nbsp;&lt;<?php echo $person_data['Email']?>&gt;" />
			</div>
		</div>
		<div class="form-group m-form__group row">
			<label for="" class="col-sm-2 col-form-label">BCC:</label>
			<div class="col-sm-10">
				<input type="text" class="form-control m-input" name="ToBCC" id="ToBCC" value="<?php echo $_POST['ToBCC']?>" placeholder="separate multiple addresses with a comma" />
			</div>
		</div>
		<div class="form-group m-form__group row">
			<label for="" class="col-sm-2 col-form-label">From Name:</label>
			<div class="col-sm-10">
				<input type="text" class="form-control m-input" name="FromName" id="FromName" value="<?php echo ( isset($_POST['FromName']) ? $_POST['FromName'] : $user_data['FirstName'].' '.$user_data['LastName'] )?>" required />
			</div>
		</div>
		<div class="form-group m-form__group row " id="fromAddressBlock">
			<label for="" class="col-sm-2 col-form-label">From Address:</label>
			<div class="col-sm-10">
				<div class="input-group">
					<input type="text" class="form-control m-input " name="FromAddr" id="FromAddr" value="<?php echo $fromaddr_left ?>" onblur="checkAccount();" required />
					<span class="input-group-addon" id="sizing-addon1">@</span>
					<?php if(is_array($whitelist) && count($whitelist) > 0) { ?>
					<select name="FromDomain" class="form-control m-input" id="FromDomain">
						<?php foreach($whitelist as $domain) { ?>
						<option value="<?php echo $domain?>"<?php echo ( $fromaddr_right == $domain ? ' selected' : '' ) ?>><?php echo $domain?></option>
						<?php } ?>
					</select>
					<?php } else {?>
					<input type="text" class="form-control m-input" name="FromDomain" id="FromDomain" value="<?php echo $fromaddr_right?>" required />
					<?php }?>
				</div>
                <div class="form-control-feedback" id="address-error-block" style="display:none;">
                	WARNING: The email you are about to send is not addressed from your account.<br />If the recipient clicks "reply" it will not be sent directly to you.<br />Please change the email address to match yours or <a href="javascript:fixAccountName();">click here</a>
            	</div>
			</div>
		</div>
        
		<div class="form-group m-form__group row">
			<label for="" class="col-sm-2 col-form-label">Subject:</label>
			<div class="col-sm-10">
            	<div class="input-group">
					<input name="Subject" type="text" class="form-control m-input" id="Subject" value="<?php echo $_POST['Subject']?>" required />
                    <div class="dropdown">
                        <button id="button-emoji" class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-content="Place emoji at the front of your subject to give your emails that special flare" title="Subject Line Emoji">
                            Emoji ☺
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" >
                        	<?php
							$em_sql = "SELECT * FROM MarketingEmojis ORDER BY Emoji_title ASC";
							$em_snd = $DB->get_multi_result($em_sql);
							foreach($em_snd as $em_dta):
							?><a class="dropdown-item" href="javascript:addEmojiToSubject('<?php echo $em_dta['Emoji_code'] ?>')"><?php echo $em_dta['Emoji_code']?> | <?php echo $em_dta['Emoji_title']?></a><?php
							endforeach;
							?>                                                    
                        </div>
                    </div>
				</div>                    
			</div>
		</div>
		<div class="form-group m-form__group row">
			<label for="" class="col-md-2 col-form-label">&nbsp;</label>
			<div class="col-md-4">
				<select class="form-control m-select2" id="TemplateSelect" name="TemplateSelect">
					<option value="">Insert Template</option>
					<optgroup label="My Templates"></optgroup>
					<?php
					$t_data = $template_obj->get_templates(0, $_SESSION['system_user_id']);
					if(!array_key_exists('empty_result', $t_data) && !array_key_exists('error', $t_data)) {
						foreach($t_data as $t_row) {
							?><option value="<?php echo $t_row['EmailTemplates_id'] ?>">
								<?php echo $t_row['EmailTemplates_title'] ?>
							</option><?php
						}
					}
					?>
					<optgroup label="Global Templates"></optgroup>
					<?php
					$t_data = $template_obj->get_templates(0);
					if(!array_key_exists('empty_result', $t_data) && !array_key_exists('error', $t_data)) {
						foreach($t_data as $t_row) {
							?><option value="<?php echo $t_row['EmailTemplates_id'] ?>">
								<?php echo $t_row['EmailTemplates_title'] ?>
							</option><?php
						}
					}
					?>
				</select>
			</div>													
																
            <div class="col-md-1">
            	<button type="button" class="btn btn-secondary  btn-sm btn-block" data-toggle="modal" data-target="#bioLoadModal">
                    <i class="flaticon-profile-1"></i> Load Bio
                </button>
            </div>
		</div>
		
		<div class="form-group m-form__group row">
			<label for="" class="col-sm-2 col-form-label">Body:</label>
			<div class="col-sm-10">
			<?php

			echo $editor->render();
			?>		
	</div>
		</div>
	</div>
	<div class="m-portlet__foot m-form__actions m-form__actions--solid">
		<div class="row">
			<div class="col-12">
				<button type="submit" class="btn btn-success">
					<i class="la la-envelope"></i> Send Email
				</button>
				<button type="button" class="btn btn-secondary" onclick="javascript:document.location='/profile/<?php echo $person_id ?>'">
					<i class="la la-close"></i> Cancel
				</button>
			</div>
		</div>
	</div>
</div>
</form>
<?php
 }
} 
?>

<div class="modal fade" id="bioLoadModal" role="dialog" aria-labelledby="bioLoadModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="printProfileModalLabel">Load Bio</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form class="m-form" id="NotesForm">               
                <div class="form-group m-form__group">
                    <div>First &amp; Last Name, Email or ID</div>
                    <div class="m-typeahead"> 
                    <input type="text" class="form-control m-input" id="bioSearch" name="bioSearch" aria-describedby="emailHelp" placeholder="Search..."  style="width:100%;">
                    </div>
                </div>
				<input type="hidden" name="bio_id" id="bio_id" value="" />

    			</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            	<button type="button" class="btn btn-primary" onclick="injectBioBody()" data-container="#bioLoadModal" data-toggle="m-popover" data-placement="top" data-content="clicking this will place a bio of the selected person into the body of the email." data-original-title="" title="" data-skin="dark"><i class="la la-envelope"></i> Place into Email Body</button>
            </div>
		</div>
	</div>
</div>
<script>

var bioSearchObject = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: '../data/films/post_1960.json',
  remote: {
    url: '/ajax/bioSearch.php?action=query&q=%QUERY',
    wildcard: '%QUERY'
  }
});

$(document).ready(function(e) {
    $('#bioSearch').typeahead(null, {
		hint: true,
		highlight: true,
		minLength: 4,
		name: 'bio-search',
		display: 'name',
		source: bioSearchObject
	});
	$('#bioSearch').bind('typeahead:select', function(ev, suggestion) {
		console.log(ev)
		console.log(suggestion);
		$('#bio_id').val(suggestion.id);
	});
	$('#bioLoadModal').on('hidden.bs.modal', function (e) {
		$('#bio_id').val('');
		$('#bioSearch').val('');
	});
	
	$('#button-emoji').popover({
		trigger: 'hover'	
	});
	
	<?php if((isset($bio_id)) && is_numeric($bio_id)): ?>
	setTimeout(function() {
		quickInjectBio('<?php echo $bio_id ?>');
	}, 1500);
	<?php endif; ?>
	
	<?php if($bio_id == 'debrief'): ?>
	quickInjectDebriefingTemplate('111','<?php echo $person_id ?>','<?php echo $intro_id ?>');
	<?php endif; ?>
});
function injectBioBody() {
	var id = $('#bio_id').val();
	var error = 0;
	if(id == '') {	
		error = 1;
	}
	if(error == 1) {
		alert('You must select a record before you can place the bio into the body of the email');
	} else {
		$.get('/ajax/bioSearch.php', {
			action: 'display',
			id:	id
		}, function(data) {
			if(data.showBio) {
				var editor = $("#EmailBodyContent").data("kendoEditor");          
				editor.value(data.bioView);
            
				$('#bioLoadModal').modal('hide');	
			} else {
				alert('This record does not have an approved bio to inject into the email. Please have the person in charge of this record configure its Bio so that it can be shared.');	
			}
		}, "json");	
	}
}

function quickInjectBio(id) {	
	//alert('Injecting Bio');
	var choice = confirm('Are you sure you want to load this bio into the email body?');
	if(choice) {
		$.get('/ajax/bioSearch.php', {
			action: 'display',
			id:	id
		}, function(data) {
			if(data.showBio) {
				var editor = $("#EmailBodyContent").data("kendoEditor");          
				editor.value(data.bioView);
            	$('#Subject').val('💕 We have a potential Match for you');
			} else {
				alert('This record does not have an approved bio to inject into the email. Please have the person in charge of this record configure its Bio so that it can be shared.');
			}
		}, "json");
	}
}

function addEmojiToSubject(emoji) {
	var subject = $('#Subject').val();
	var newSubject = emoji+' '+subject;
	$('#Subject').val(newSubject);	
}

function quickInjectDebriefingTemplate(template_id, pid, did) {
	var choice = confirm('Are you sure you want to load this template (Debriefing) into the email body?');
	if(choice) {
		$.get('/ajax/email-qucksend.php', {
			action: 'debriefing',
			tid: template_id,
			pid: pid,
			did: did
		}, function(data) {
			var editor = $("#EmailBodyContent").data("kendoEditor");          
				editor.value(data);
            
			//$('#Subject').val('We have a potential Match for you');
		});
	}
}

function checkAccount() {
	var myaccount = $('#my-email-account').val();
	var fromaccount = $('#FromAddr').val();
	if(myaccount != fromaccount) {
		$('#fromAddressBlock').addClass('has-danger');
		$('#address-error-block').show();							
	} else {
		$('#fromAddressBlock').removeClass('has-danger');
		$('#address-error-block').hide();
	}	
}
function fixAccountName() {
	var myaccount = $('#my-email-account').val();
	$('#FromAddr').val(myaccount);
	checkAccount();
}
</script>


</div>
</div>
</div>
