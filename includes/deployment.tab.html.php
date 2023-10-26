

<link rel="stylesheet" href="/vendor/telerik/styles/kendo.common.min.css" />
<link rel="stylesheet" href="/vendor/telerik/styles/kendo.default.min.css" />
<link rel="stylesheet" href="/vendor/telerik/styles/kendo.default.mobile.min.css" />
<script src="/vendor/telerik/js/kendo.all.min.js"></script>
<div class="form-group m-form__group row">
	<div class="col-12">
		

<?php

	require_once  __DIR__ . '/../vendor/telerik/wrappers/php/lib/Kendo/Autoload.php';

    $editor = new \Kendo\UI\Editor('msg_body_html');

   
	
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


    $editor
        ->attr('style', 'width:100%;height:400px')
		->encoded(false)
		->pasteCleanup($pasteCleanup)
		->content(((isset($_POST['msg_body_html']))?$_POST['msg_body_html']:$data['MarketingDeployments_bodyHTML']));
    
	echo $editor->render();
?>

	</div>
</div>