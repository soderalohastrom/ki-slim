<?php
include_once("class.settings.php");
$SETTINGS = new Settings();
$_SESSION['ckfinder_url'] = $SETTINGS->setting['BASE_URL'].'assets/vendors/modules/ckfinder/userfiles/';
?>
<p>&nbsp;</p>
<div class="container-fluid">
<iframe frameborder="0" style="width:100%; height:700px;" src="/assets/vendors/modules/ckfinder/ckfinder.html"></iframe>
</div>