<?php
include_once("class.record.php");
include_once("class.users.php");
$RECORD = new Record($DB);
$USER = new Users($DB);
$USER_PERMS = $USER->get_userPermissions($_SESSION['system_user_id']);

$DAYS_SCOPE = 7;
?>		
<!-- BEGIN: Subheader -->
<?php echo $PAGE->render_PageSubHeader("My History", "flaticon-paper-plane", array(array('text' => 'Recently Visited Records', 'link' => ''))); ?>
<!-- END: Subheader -->
<div class="m-content" id="history-display">
<?php 
for($i=0; $i<1; $i++):
	$dayStart = mktime(0, 0, 0, date("m"), (date("d") - $i), date("Y"));
	$dayEnd = mktime(23, 59, 59,  date("m"), (date("d") - $i), date("Y"));
	$p_sql = "
	(
		SELECT 
			Person_id as RecordID,
			ViewDate as vdate,
			'P' as vtype			
		FROM 
			PersonsViewLog 
		WHERE 
			User_id='".$_SESSION['system_user_id']."' 
		AND 
			(ViewDate >= '".$dayStart."' AND  ViewDate <= '".$dayEnd."') 
		ORDER BY 
			ViewDate DESC
	) UNION (
		SELECT
			PersonsDates_id as RecordID,
			ViewDate as vdate,
			'I' as vtype			
		FROM 
			PersonsDatesViewLog 
		WHERE 
			User_id='".$_SESSION['system_user_id']."' 
		AND 
			(ViewDate >= '".$dayStart."' AND  ViewDate <= '".$dayEnd."') 
		ORDER BY 
			ViewDate DESC
	)
	ORDER BY
		vdate DESC
	";
	//echo $p_sql."<br>\n";
	$p_snd = $DB->get_multi_result($p_sql);
	if(isset($p_snd['empty_result'])):
		?>
        <div class="row btn-success">
        	<div class="col-12">
            	<strong><?php echo date("l M jS Y", $dayStart)?></strong>
            </div>
		</div>
        <div class="row">
        	<div class="col-12">
            	<em>No Logs for this date</em>            
            </div>
        </div>            
        <?php
	else:
		?>
        <div class="row  btn-success">
        	<div class="col-12">
            	<strong><?php echo date("l M jS Y", $dayStart)?></strong>
            </div>
		</div>            
        <?php
		foreach($p_snd as $p_dta):
		if($p_dta['vtype'] == 'P'):
			$url = 'https://'.$_SERVER['SERVER_NAME'].'/profile/'.$p_dta['RecordID'];
			ob_start();
			?><i class="la la-user"></i> <?php echo $RECORD->get_personName($p_dta['RecordID'])?><?php
			$label = ob_get_clean();
		elseif($p_dta['vtype'] == 'I'):
			$url = 'https://'.$_SERVER['SERVER_NAME'].'/intro/'.$p_dta['RecordID'];
			$int_sql = "
			SELECT
				PersonsDates_id,
				PersonsDates_participant_1,
				PersonsDates_participant_2
			FROM
				PersonsDates
			WHERE
				PersonsDates_id='".$p_dta['RecordID']."'			
			";
			$int_snd = $DB->get_single_result($int_sql);
			$p1_name = $RECORD->get_personName($int_snd['PersonsDates_participant_1']);
			$p2_name = $RECORD->get_personName($int_snd['PersonsDates_participant_2']);			
			$label = 'Intro between '.$p1_name.' &amp; '.$p2_name;
			ob_start();
			?><i class="fa fa-heart-o"></i> <?php echo $label?> <?php
			$label = ob_get_clean();
		endif;
		?>
      	<div class="row">
        	<div class="col-2">
            	<?php echo date("h:ia", $p_dta['vdate'])?>
			</div>
            <div class="col-4">
            	<?php echo $label?>
            </div>
            <div class="col-6">
            	<a href="<?php echo $url?>" class="m-link"><?php echo $url?></a>
			</div>
        </div>
        <?php
		endforeach;
	endif;
endfor;
?>
<div class="row" id="more-history-block">
	<div class="col-12">
    	<button type="button" class="btn btn-secondary btn-block" onclick="loadMoreHistory('<?php echo date("m/d/Y", $dayStart)?>', '<?php echo $DAYS_SCOPE?>')">Load More History</button>
	</div>
</div>
</div>


<script>
function loadMoreHistory(date, days) {
	mApp.block("#more-history-block", {
		overlayColor: "#000000",
		type: "loader",
		state: "success",
		message: "Loading History..."
	});
	$.post('/ajax/ajax.myRecordHistory.php', {
		date: date,
		days: days
	}, function(data) {
		mApp.unblock("#more-history-block");
		$('#more-history-block').remove();
		$('#history-display').append(data);
	});
}

</script>


