<?php
class recordMMExpect extends Record {
	
	function render_MMExpect_table($PID) {
		$e_sql = "SELECT * FROM PersonsExpectations WHERE PersonID='".$PID."' ORDER BY SubmitDate DESC";
		echo $c_sql."<br>";
		$e_snd = $this->db->get_multi_result($e_sql);
		ob_start();
		?>
        <table class="table m-table m-table--head-no-border">
            <thead>
                <tr>
                    <th>Date Created</th>
                    <th>Rep</th>
                    <th>Status</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
        	<?php		
			if(!isset($e_snd['empty_result'])):
				foreach($e_snd as $e_dta):
					switch($e_dta['SubmitStatus']) {
						case 1:
						$statusBlock = '<span class="m-badge m-badge--info m-badge--wide">PENDING</span>';
						break;
						
						case 2:
						$statusBlock = '<span class="m-badge m-badge--warning m-badge--wide">SIGNED</span>';
						break;
						
						case 3:
						$statusBlock = '<span class="m-badge m-badge--success m-badge--wide">PROCESSED</span>';
						break;
					}
				?>
                <tr>
                	<td><?php echo date("m/d/y h:ia", $e_dta['CreateDate'])?></td>
                    <td><?php echo $this->get_userName($e_dta['CreatedBy'])?></td>
                    <td><?php echo $statusBlock?></td>
					<?php if ($e_dta['Is_Addendum']) : ?>
                    	<td><a href="/expectadd/<?php echo $PID?>/<?php echo $e_dta['ExpectID']?>" class="btn btn-secondary btn-sm">View Addendum</a></td>
					<?php else: ?>
						<td><a href="/expectgen/<?php echo $PID?>/<?php echo $e_dta['ExpectID']?>" class="btn btn-secondary btn-sm">View Agreeement</a></td>
					<?php endif; ?>
					</tr>
                <?php
				endforeach;                    			
			else:
				?>
                <tr>
                	<td colspan="4">&nbsp;</td>
                </tr>
                <?php
			endif;
			?>
		</tbody>
		</table>
        <?php
		return ob_get_clean();
	}
	
	
	
}
?>