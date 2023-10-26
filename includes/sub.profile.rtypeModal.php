<?php
$typeSQL = "SELECT * FROM PersonTypes WHERE PersonsTypes_id NOT IN(1,2) ORDER BY PersonsTypes_order ASC";
$typeSND = $DB->get_multi_result($typeSQL);
ob_start();
foreach($typeSND as $typeDTA):
	if(in_array($typeDTA['PersonsTypes_permission'], $USER_PERMS)):
		?>
		<button type="button" onclick="updateRecordType(<?php echo $typeDTA['PersonsTypes_id']?>)" class="btn btn-<?php echo $typeDTA['PersonsTypes_color']?> btn-block" data-container="body" data-toggle="m-popover" data-placement="top" data-content="<?php echo $typeDTA['PersonsTypes_desc']?>" <?php echo (($PDATA['PersonsTypes_id'] == $typeDTA['PersonsTypes_id'])? 'disabled':'')?>>
		<span>
			<i class="flaticon-users"></i>
			<span>
			<?php echo $typeDTA['PersonsTypes_text']?>
			</span>
		</span>        
		</button>
		<?php
		if(($typeDTA['PersonsTypes_id'] == 13) || ($typeDTA['PersonsTypes_id'] == 8) || ($typeDTA['PersonsTypes_id'] == 11) || ($typeDTA['PersonsTypes_id'] == 6)):
		?><hr /><?php 
		endif;	
	endif;
endforeach;
$buttonList = ob_get_clean();	
	


?>
<div class="modal fade" id="recordModal" role="dialog" aria-labelledby="recordModallLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="recordModalLabel">Record Type</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">   
			<?php echo $buttonList?>
            
            <?php if(in_array(8, $USER_PERMS)): ?>
                <hr />
                <button type="button" onclick="removeRecord(<?php echo $PERSON_ID?>)" class="btn btn-danger btn-block" data-container="body" data-toggle="m-popover" data-placement="top" data-content="This will remove the record and all of it's date/intro conenctions and communication history. ">
                <span>
                    <i class="flaticon-users"></i>
                    <span>
                    Remove Record
                    </span>
                </span>        
                </button>
            <?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
var updateRecordType = function(typeID) {
	var choice = confirm('Are you sure you want to change this record type?');
	if(choice) {
		$.post('/ajax/inline.basic.php', {
			qid		: 'PersonsTypes_id',
			value	: typeID,
			pid		: '<?php echo $PERSON_ID?>',
			kiss_token: '<?php echo $SESSION->createToken()?>'	
		}, function(data) {
			document.location.reload(true);	
		}, "json");	
	}
}
var removeRecord = function(pid) {
	var choice = confirm('Are you sure you want to delete this person record? WARNING: This action cannot be undone.');
	if(choice) {
		var confirmString = prompt("please type \"DELETE\" to confirm", "");
		if(confirmString == 'DELETE') {
			//alert('Remove Record:'+pid);
			$.post('/ajax/otherStuff.php?action=removeRecord', {
				pid: pid	
			}, function(data) {
				alert('Person Deleted from Database');
				document.location.href = '/home';
			});
		}
	}	
}
</script>