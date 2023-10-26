<?php

include_once("class.settings.php");
$SETTINGS = new Settings();
include_once("class.templates.php");
$template_obj = new Templates();
$_SESSION['ckfinder_url'] = $SETTINGS->setting['BASE_URL'].'assets/vendors/modules/ckfinder/userfiles/';
$user_perms = $PAGE->usr->get_userPermissions($_SESSION['system_user_id']);

if(in_array(35, $user_perms)) {
	$global_access = true;
} else {
	$global_access = false;
}
?>
<style type="text/css">
.iframe_thumbnail {
	background-color:#FFF;
	border:none;
}

.thumbnail_container {
	 border: solid #d3d3d3 1px;
	 width:250px;	/*125px;*/
	 height:300px;	/*150px;*/
	 overflow:auto;
}
/*
.button_container {
	position:absolute;
	top:50px;
	left:170px;
}

.template_button {
	float:left; 
	margin-top:5px;
}

.template_button button {
	width:80px;
}

.template_button a {
	width:75px; 
	text-align:center;
	display:block;
	font-size:12px;
	padding:2px 0;
	word-wrap:break-word;
}

.panel-heading h3 {
	height:17px;
	overflow:auto;
}
*/
.editMode {
	display:none;
}

.savingMsg, .savedMsg {
	display:none;
}
</style>
<link rel="stylesheet" href="/vendor/telerik/styles/kendo.common.min.css" />
<link rel="stylesheet" href="/vendor/telerik/styles/kendo.default.min.css" />
<link rel="stylesheet" href="/vendor/telerik/styles/kendo.default.mobile.min.css" />
<script src="/vendor/telerik/js/kendo.all.min.js"></script>
<script type="text/javascript">

//jQuery function which inserts data at the cursor position in the selected textarea
$.fn.insertAtCaret = function (myValue) {
    return this.each(function(){
        //IE support
        if (document.selection) {
            this.focus();
            sel = document.selection.createRange();
            sel.text = myValue;
            this.focus();
        }
        //MOZILLA/NETSCAPE support
        else if (this.selectionStart || this.selectionStart == '0') {
            var startPos = this.selectionStart;
            var endPos = this.selectionEnd;
            var scrollTop = this.scrollTop;
            this.value = this.value.substring(0, startPos)
                          + myValue
                  + this.value.substring(endPos,this.value.length);
            this.focus();
            this.selectionStart = startPos + myValue.length;
            this.selectionEnd = startPos + myValue.length;
            this.scrollTop = scrollTop;
        } else {
            this.value += myValue;
            this.focus();
        }
    });
};

function edit_template(templateID) {
	document.location='./mkg-templates/'+templateID;
}

function copy_template(templateID, templateName) {
	var promptTXT = 'Please enter a name for the new template.';
	var copiedTXT = 'Template copy has been created.';
	var templateNameDefault = templateName + ' (copy)';
	var templateNameInput = prompt(promptTXT, templateNameDefault);
	if(templateNameInput != null) {
		$.get('ajax/ajax.templates.php', { 'action': 'copy_template', 'template_id': templateID, 'template_name': templateNameInput },
		function(data){
			if(data.error == '') {
				//alert(copiedTXT);
				//window.location.reload(true);
				document.location = '/mkg-templates/'+data.template_id;
			} else {
				alert(data.error);
			}
		}, 'json');
	}
}

function delete_template(templateID) {
	var promptTXT = 'Are you sure you want to remove this email template?';
	var removedTXT = 'Template has been removed.';
	var answer = confirm(promptTXT);
	if(answer) {
		$.get('ajax/ajax.templates.php', { 'action': 'delete_template', 'template_id': templateID },
		function(data){
			if(data.error == '') {
				alert(removedTXT);
				window.location.reload(true);
			} else {
				alert(data.error);
			}
		}, 'json');
	}
}

function add_category() {
	var promptTXT = 'Please enter a name for the new template category.';
	var copiedTXT = 'Template category has been created.';
	var templateCategoryName = prompt(promptTXT, '');
	if(templateCategoryName != null) {
		$.get("ajax/ajax.templates.php", { 'action': 'add_category', 'category_name': templateCategoryName },
		function(data){
			if(data.error == '') {
				alert(copiedTXT);
				window.location.reload(true);
			} else {
				alert(data.error);
			}
		}, 'json');
	}
}

function edit_category(CategoryID) {
	$('#viewMode_'+CategoryID).hide();
	$('#editMode_'+CategoryID).show();
}

function remove_category(CategoryID) {
	var promptTXT = 'Are you sure you want to remove this template category?';
	var removedTXT = 'Template category has been removed.';
	var errorTXT = 'An unexpected error occurred. Please try your request again later.';
	var error2TXT = 'We are unable to remove this category because it contains one or more templates. Please move all templates out of this category, then re-submit your removal request.';
	var answer = confirm(promptTXT);
	if(answer) {
		$.get("ajax/ajax.templates.php", { 'action': 'delete_category', 'category_id': CategoryID },
		function(data){
			if(data.success == '1') {
				$('#categoryUpdated').val('1');
				$('#category_li_'+CategoryID).remove();
				$('#categoryRemovedMsg').fadeIn().delay(3000).fadeOut();
			} else if(data.success == '2') {
				alert(error2TXT);
			} else {
				alert(errorTXT);
			}
		}, "json");
	}
}

function save_category(CategoryID) {
	var errorTXT = 'Template category name cannot be blank.';
	var newName = $.trim($('#inputCategoryName_'+CategoryID).val());
	if(newName == '') {
		alert(errorTXT);
	} else {
		$.get("ajax/ajax.templates.php", { 'action': 'edit_category', 'category_id': CategoryID, 'category_name': newName },
		function(data){
			if(data.error == '') {
				$('#categoryUpdated').val('1');
				$('#categoryName_'+CategoryID).html(newName);
				$('#categorySavedMsg').fadeIn().delay(3000).fadeOut();
				cancel_category(CategoryID);
			} else {
				alert(data.error);
			}
		}, 'json');
	}
}

function cancel_category(CategoryID) {
	$('#editMode_'+CategoryID).hide();
	$('#viewMode_'+CategoryID).show();
}

function set_field(ver) {
	var FieldValue = $('#MergeField'+ver).val();
	$('#MergeField'+ver).val('');
	if(ver == 'HTML') {	
		console.log(FieldValue);
		var myEditor = $("#EmailTemplates_bodyHtml").kendoEditor();
		console.log(myEditor);
		myEditor.insertAtCaret(FieldValue)
	} else {
		$('#EmailTemplates_bodyText').insertAtCaret(FieldValue);
	}
}
</script>

<?php if(!array_key_exists('params', $pageParamaters) || count($pageParamaters['params']) == 0) { //page mode = view all templates ?>
<div class="m-subheader">
<div class="btn-group pull-right" style="margin-bottom:10px;">
	<button type="button" class="btn btn-accent" onclick="edit_template('0')"><i class="flaticon-add" aria-hidden="true"></i> New Template</button>
	<!--<button type="button" class="btn btn-accent" onclick="add_category()"><i class="flaticon-add" aria-hidden="true"></i> New Category</button>
	<button type="button" class="btn btn-brand" data-toggle="modal" data-target="#modalEditCategories" data-backdrop="static"><i class="flaticon-edit-1" aria-hidden="true"></i> Edit Categories</button>-->
</div>
<h4><i class="flaticon-interface-1"></i> <?php echo ( $global_access ? '' : 'My ' )?>eMail Templates</h4>
</div>
<?php 
if($global_access) {
	$CATEGORY_IDS = array($_SESSION['system_user_id'], 0);
} else {
	$CATEGORY_IDS = array($_SESSION['system_user_id']);
}
?>
<div class="m-content m-form">
<?php if($global_access) { ?>
<ul class="nav nav-tabs m-tabs m-tabs-line m-tabs-line--primary" role="tablist">
	<?php foreach($CATEGORY_IDS as $CATEGORY_ID) { ?>
	<li class="nav-item m-tabs__item"><a class="nav-link m-tabs__link<?php echo ( $CATEGORY_ID != 0 ? ' active' : '' )?>" data-toggle="tab" role="tab" href="#category_<?php echo $CATEGORY_ID?>" style="background-color:transparent;"><?php echo ( $CATEGORY_ID != 0 ? 'My' : 'Global' )?> Templates</a></li>
	<?php } ?>
</ul>
<div class="tab-content">
<?php } ?>
<?php foreach($CATEGORY_IDS as $CATEGORY_ID) { ?>
<?php if($global_access) {
?><div id="category_<?php echo $CATEGORY_ID?>" class="tab-pane<?php echo ($CATEGORY_ID != 0 ? ' active' : '')?>" role="tabpanel"><?php 
}
$all_templates = $template_obj->get_templates(0, $CATEGORY_ID);
if(empty($all_templates['error']) && empty($all_templates['empty_result'])) {
foreach($all_templates as $idx=>$TP_DATA) {
	if($idx % 2 == 0) {
		if($idx != 0) {
			?></div><?php
		}
		?><div class="row"><?php
	}
?>
	<div class="col-lg-6">
		<div class="m-portlet">
			<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
			<div class="m-portlet__head-title">
			  <h3 class="m-portlet__head-text" title="<?php echo $TP_DATA['EmailTemplates_title']?>"><?php echo $TP_DATA['EmailTemplates_title']?></h3>
			</div>
			</div>
			</div>
			<div class="m-portlet__body">
			  <div class="thumbnail_container">
					<iframe id="iframe_thumbnail_<?php echo $TP_DATA['EmailTemplates_id']?>" name="iframe_thumbnail_<?php echo $TP_DATA['EmailTemplates_id']?>" class="iframe_thumbnail" src="./template.preview.php?TemplateID=<?php echo $TP_DATA['EmailTemplates_id']?>&thumbnail=1" width="800" height="300" scrolling="no"></iframe>
				</div>
			</div>
			<div class="m-portlet__foot m-form__actions m-form__actions--solid">
			
			<div class="row">
				<div class="col-12">
				<div class="btn-group">
					<button type="button" class="btn btn-accent" onclick="edit_template('<?php echo $TP_DATA['EmailTemplates_id']?>');"><i class="la la-edit"></i>Edit</button>
					<button type="button" class="btn btn-brand" onclick="copy_template('<?php echo $TP_DATA['EmailTemplates_id']?>', '<?php echo $TP_DATA['EmailTemplates_title']?>');return false;"><i class="la la-copy"></i>Copy</button>
					<button type="button" class="btn btn-danger" onclick="delete_template('<?php echo $TP_DATA['EmailTemplates_id']?>');"><i class="la la-trash"></i>Remove</button>
				</div>
				</div>
			</div>
			
			</div>
	  </div>
	</div>
<?php } ?>
</div>
<?php }  else {
	?><div class="alert alert-info">No templates found.</div><?php
}?>
<div style="clear:both;"></div>
<?php if($global_access) { ?>
</div>
<?php } }?>
<div style="clear:both;"></div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	/*$('#modalEditCategories').on('hidden.bs.modal', function(e) {
		if($('#categoryUpdated').val() == '1') {
			window.location.reload(true);
		}
	});*/
});
</script>
</div>
<?php } else { //page mode = edit template
$tmpl_updated = false;
$errors = array();
$template_id = $pageParamaters['params'][0];
if(empty($template_id)) {
	$template_id = 0;
}
$invalid = false;
$edit_mode = 'Edit';
if(is_numeric($template_id)) {
	if($template_id != 0) {
		$tp_data = $template_obj->get_template_data($template_id);
		if(array_key_exists('empty_result', $tp_data) || array_key_exists('error', $tp_data)) {
			$invalid = true;
		}
	} else {
		$edit_mode = 'New';
	}
} else {
	$invalid = true;
}
if(!$invalid && isset($_POST['EmailTemplates_title'])) {
	include('assets/vendors/modules/html2text/html2text.php');
	include("assets/vendors/modules/htmlpurifier-4.10.0/library/HTMLPurifier.auto.php");
	
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	
	$ET_title = $purifier->purify($_POST['EmailTemplates_title']);
	$ET_subject = $purifier->purify($_POST['EmailTemplates_subject']);
	$ET_fromName = $purifier->purify($_POST['EmailTemplates_fromName']);
	$ET_fromEmail = $purifier->purify($_POST['TemplateFromAddr']).'@'.$_POST['TemplateFromDomain'];
	$ET_category = $_POST['EmailTemplates_category'];
	$ET_user = $_POST['EmailTemplates_user'];
	$ET_bodyHTML = $purifier->purify($_POST['EmailTemplates_bodyHTML']);
	$ET_bodyText = ( strlen($_POST['EmailTemplates_bodyText']) > 0 ? $purifier->purify($_POST['EmailTemplates_bodyText']) : convert_html_to_text($ET_bodyHTML) );
	
	$tp_data = array(
		'EmailTemplates_title' => $ET_title,
		'EmailTemplates_subject' => $ET_subject,
		'EmailTemplates_fromName' => $ET_fromName,
		'EmailTemplates_fromEmail' => $ET_fromEmail,
		'EmailTemplates_category' => $ET_category,
		'EmailTemplates_user' => $ET_user,
		'EmailTemplates_bodyHTML' => $ET_bodyHTML,
		'EmailTemplates_bodyText' => $ET_bodyText
		);
	if($tp_data['EmailTemplates_title'] == '') {
		$errors[] = 'The Template Name cannot be empty.';
	}
	if($tp_data['EmailTemplates_subject'] == '') {
		$errors[] = 'The Email Subject cannot be empty.';
	}
	if($tp_data['EmailTemplates_fromName'] == '') {
		$errors[] = 'The From Name cannot be empty.';
	}
	if($_POST['TemplateFromAddr'] == '') {
		$errors[] = 'The From Address cannot be empty.';
	}
	if($_POST['TemplateFromDomain'] == '') {
		$errors[] = 'The From Address Domain cannot be empty.';
	}
	if($tp_data['EmailTemplates_bodyHTML'] == '') {
		$errors[] = 'The Template Content cannot be empty.';
	}
	if(count($errors) == 0) {
		$current_time = time();
		$fields = array('EmailTemplates_title', 'EmailTemplates_subject', 'EmailTemplates_fromEmail', 'EmailTemplates_fromName', 'EmailTemplates_bodyHTML', 'EmailTemplates_bodyText', 'EmailTemplates_dateCreated', 'EmailTemplates_createdBy', 'EmailTemplates_status', 'EmailTemplates_category', 'EmailTemplates_user');
		$values = array(
			"'".$DB->mysqli->escape_string($tp_data['EmailTemplates_title'])."'",
			"'".$DB->mysqli->escape_string($tp_data['EmailTemplates_subject'])."'",
			"'".$DB->mysqli->escape_string($tp_data['EmailTemplates_fromEmail'])."'",
			"'".$DB->mysqli->escape_string($tp_data['EmailTemplates_fromName'])."'",
			"'".$DB->mysqli->escape_string($tp_data['EmailTemplates_bodyHTML'])."'",
			"'".$DB->mysqli->escape_string($tp_data['EmailTemplates_bodyText'])."'",
			"'".$current_time."'",
			"'".$_SESSION['system_user_id']."'",
			"'1'",
			"'".$DB->mysqli->escape_string($tp_data['EmailTemplates_category'])."'",
			"'".$DB->mysqli->escape_string($tp_data['EmailTemplates_user'])."'"
		);
		if($_POST['EmailTemplates_id'] == 0) {
			$tmpl_sql = 'INSERT INTO EmailTemplates ( '.implode(', ', $fields).' ) VALUES ( '.implode(', ', $values).' )';
			//echo "$tmpl_sql<br><br><br>";
			$tmpl_send = $DB->mysqli->query($tmpl_sql);
			$template_id = $DB->mysqli->insert_id;
			if($template_id) {
				$edit_mode = 'Edit';
				$tmpl_updated = true;
			} else {
				$template_id = 0;
			}
		} else {
			$tmpl_sql = 'UPDATE EmailTemplates SET ';
			$tmpl_fields = '';
			foreach($fields as $idx=>$field_name) {
				$tmpl_fields .= ', '.$field_name.' = '.$values[$idx];
			}
			$tmpl_sql .= substr($tmpl_fields, 2);
			$tmpl_sql .= " WHERE EmailTemplates_id = '".$_POST['EmailTemplates_id']."'";
			//echo "$tmpl_sql<br><br><br>";
			$tmpl_send = $DB->mysqli->query($tmpl_sql);
			if($tmpl_send) {
				$template_id = $_POST['EmailTemplates_id'];
				$tmpl_updated = true;
			}
		}
	}
}
?>
<div class="m-subheader">
<h4><i class="flaticon-interface-1"></i> <?php echo $edit_mode?> Email Template</h4>
</div>
<div class="m-content">
<?php if($invalid) { ?>
	<div class="alert alert-danger"><strong>Error:</strong> The requested template ID is invalid.</div>
<?php } else { ?>
<?php if(count($errors) > 0) { ?>
	<div class="alert alert-danger alert-dismissible fade show">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
		<h4>Please correct the following:</h4>
		<?php echo implode('<br />', $errors)?>
	</div>
<?php } ?>
<?php if($tmpl_updated) { ?>
	<div class="alert alert-success alert-dismissible fade show">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
		<span class="la la-check"></span> Template has been <?php echo ( $_POST['EmailTemplates_id'] == 0 ? 'created' : 'updated' )?>.
	</div>
<?php } ?>
<form action="/mkg-templates/<?php echo $template_id?>" method="post" enctype="multipart/form-data" name="mainForm" id="templateForm" class="m-form m-form--fit m-form--label-align-right">
<input type="hidden" name="EmailTemplates_id" id="EmailTemplates_id" value="<?php echo $template_id?>" />
<input type="hidden" name="EmailTemplates_category" id="EmailTemplates_category" value="0" />
<div class="nice-tabs">	
<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item"><a class="nav-link active" data-toggle="tab" role="tab" href="#template_basic">Basic Information</a></li>
	<li class="nav-item"><a class="nav-link" data-toggle="tab" role="tab" href="#template_html">HTML Version</a></li>
	<li class="nav-item"><a class="nav-link" data-toggle="tab" role="tab" href="#template_text">Text Version</a></li>
</ul>
<div class="tab-content">
<div id="template_basic" class="tab-pane active" role="tabpanel">
    <div class="form-group m-form__group row">
        <label for="EmailTemplates_title" class="col-sm-4 col-form-label">Template Name:</label>
        <div class="col-sm-8"><input name="EmailTemplates_title" type="text" class="form-control m-input" id="EmailTemplates_title" value="<?php echo $tp_data['EmailTemplates_title']?>" required></div>
    </div>
    <div class="form-group m-form__group row">
    	<label for="EmailTemplates_subject" class="col-sm-4 col-form-label">Email Subject:</label>
		<div class="col-sm-8">
        	<div class="input-group">
                <input name="EmailTemplates_subject" type="text" class="form-control m-input" id="EmailTemplates_subject" value="<?php echo $tp_data['EmailTemplates_subject']?>" required>
                <div class="dropdown">
                    <button id="button-emoji" class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-content="Place emoji at the front of your subject to give your emails that special flare" title="Subject Line Emoji">
                        Emoji ☺
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" >
                        <?php
                        $em_sql = "SELECT * FROM MarketingEmojis ORDER BY Emoji_title ASC";
                        $em_snd = $DB->get_multi_result($em_sql);
                        foreach($em_snd as $em_dta):
                        ?><a class="dropdown-item" href="javascript:addEmojiToSubject('<?php echo $em_dta['Emoji_code']?>')"><?php echo $em_dta['Emoji_code']?> | <?php echo $em_dta['Emoji_title']?></a><?php
                        endforeach;
                        ?>                                                    
                    </div>
                </div>
            </div>
		</div>
	</div>
    <div class="form-group m-form__group row">
    	<label for="EmailTemplates_fromName" class="col-sm-4 col-form-label">From Name:</label>
		<div class="col-sm-8"><input name="EmailTemplates_fromName" type="text" class="form-control m-input" id="EmailTemplates_fromName" value="<?php echo $tp_data['EmailTemplates_fromName']?>" required></div> 
	</div>
	<?php
	$whitelist = explode(',', $SETTINGS->setting['DOMAIN_WHITELIST']);
	if($tp_data['EmailTemplates_fromEmail'] != '') {
		$at_pos = strpos($tp_data['EmailTemplates_fromEmail'], '@');
		$fromaddr_left = substr($tp_data['EmailTemplates_fromEmail'], 0, $at_pos);
		$fromaddr_right = substr($tp_data['EmailTemplates_fromEmail'], ($at_pos+1));
	} else {
		$fromaddr_left = '';
		$fromaddr_right = '';
	}
	?>
    <div class="form-group m-form__group row">
    	<label for="TemplateFromAddr" class="col-sm-4 col-form-label">From Address:</label>
		<div class="col-sm-8">
			<div class="input-group">
				<input name="TemplateFromAddr" type="text" class="form-control m-input" id="TemplateFromAddr" value="<?php echo $fromaddr_left?>" required>
				<span class="input-group-addon" id="sizing-addon1">@</span>
				<?php if(is_array($whitelist) && count($whitelist) > 0) { ?>
				<select name="TemplateFromDomain" class="form-control m-input" id="TemplateFromDomain">
					<?php foreach($whitelist as $domain) { ?>
					<option value="<?php echo $domain?>"<?php echo ( $fromaddr_right == $domain ? ' selected' : '' )?>><?php echo $domain?></option>
					<?php } ?>
				</select>
				<?php }  else {?>
				<input name="TemplateFromDomain" type="text" class="form-control m-input" id="TemplateFromDomain" value="<?php echo $fromaddr_right?>" required>
				<?php } ?>
			</div>
		</div>
	</div>
	<?php if($global_access) { ?>
    <div class="form-group m-form__group row" style="padding-bottom:15px;">
    	<label for="EmailTemplates_category" class="col-sm-4 col-form-label">Template Scope:</label>
        <div class="col-sm-8">
        <select name="EmailTemplates_user" id="EmailTemplates_user" class="form-control m-input">
			<option value="<?php echo $_SESSION['system_user_id']?>"<?php echo ( !is_array($tp_data) || $tp_data['EmailTemplates_user'] == $_SESSION['system_user_id'] ? ' selected' : '' )?>>My Templates</option>
			<option value="0"<?php echo ( is_array($tp_data) && $tp_data['EmailTemplates_user'] == 0 ? ' selected' : '' )?>>Global Templates</option>
        </select>
        </div> 
	</div>
	<?php } else { ?>
		<input type="hidden" name="EmailTemplates_user" id="EmailTemplates_user" value="<?php echo $_SESSION['system_user_id']?>" />
	<?php } ?>
</div>
<div id="template_html" class="tab-pane" role="tabpanel">
	<div class="form-group m-form__group row">
		<div class="col-12">
		
	<?php

	require_once  __DIR__ . '/../vendor/telerik/wrappers/php/lib/Kendo/Autoload.php';

    $editor = new \Kendo\UI\Editor('EmailTemplates_bodyHTML');

   
	
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
		->content($tp_data['EmailTemplates_bodyHTML']);
    
	echo $editor->render();
?>
</div>
	</div>




</div>
<div id="template_text" class="tab-pane" role="tabpanel">
	<div class="row">
	<div class="col-lg-6">
	<div class="form-group m-form__group row">
    	<label for="MergeFieldText" class="col-sm-4 col-form-label">Merge Fields:</label>
        <div class="col-sm-8">
        <select name="MergeFieldText" id="MergeFieldText" class="form-control m-input" onchange="javascript:set_field('Text');">
        	<option value="">-- select --</option>
			<?php foreach($SETTINGS->setting['MERGE_FIELDS'] as $merge_field_id=>$merge_field) {?>
            <option value="<?php echo $merge_field_id?>"><?php echo $merge_field['display']?></option>
			<?php } ?>
        </select>
        </div> 
	</div>
	</div>
	<div class="col-lg-6"></div>
	</div>
	<div class="form-group m-form__group row">
		<div class="col-12">
			<textarea id="EmailTemplates_bodyText" name="EmailTemplates_bodyText" class="form-control m-input" rows="25"><?php echo $tp_data['EmailTemplates_bodyText']?></textarea>
		</div>
	</div>
</div>
<div class="tab-footer m-form__actions m-form__actions--solid">
	<button type="button" class="btn btn-default" style="margin-right:5px;" onclick="document.location='/mkg-templates'"><i class="la la-arrow-left"></i>&nbsp;Back to Templates</button>    
	<button type="submit" class="btn btn-success"><i class="la la-save"></i>&nbsp;Save Template</button>
</div>
</div>
</div>
</form>
<?php } ?>
</div>
<script type="text/javascript">
function addEmojiToSubject(emoji) {
	var subject = $('#EmailTemplates_subject').val();
	var newSubject = emoji+' '+subject;
	$('#EmailTemplates_subject').val(newSubject);	
}
</script>
<?php } ?>