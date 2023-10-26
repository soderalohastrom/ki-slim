<div class="m-content">
<?php
//print_r($_GET);
$PAGE_PARAMS = $PAGE->parseURLString();
//print_r($PAGE_PARAMS);
if(isset($PAGE_PARAMS['params'][0])):
	include("sub.userEdit.php");
else:
	include("sub.usersList.php");
endif;
?>
</div>