<?php
include_once("class.record.php");
include_once("class.reports.php");
$RECORD = new Record($DB);
$REPORT = new Reports($DB, $RECORD);
?>
<div class="m-content">
    <div class="m-portlet m-portlet--mobile">		
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<span class="m-portlet__head-icon">
						<i class="flaticon-graphic-1"></i>
					</span>
					<h3 class="m-portlet__head-text">
        	            All KISS Reports
                    </h3>
				</div>
			</div>
			<div class="m-portlet__head-tools">
				&nbsp;
			</div>
		</div>
        <div class="m-portlet__body">
<table class="table table-condensed table-striped">
<thead>
	<tr>
    	<th>Report</th>
        <th width="150">Date Created</th>
        <th width="150">Created By</th>
	</tr>
</thead>
<tbody>                    
<?php
$r_sql = "SELECT * FROM Reports ORDER BY Report_name ASC";
$r_snd = $DB->get_multi_result($r_sql);
foreach($r_snd as $report):
	?>
    <tr>
    	<td>
        	<a href="/page.php?path=viewreport/<?php echo $report['Report_id']?>" class="m-link"><?php echo $report['Report_name']?></a> 
			<?php echo (($report['Report_type'] == 2)? '<i class="flaticon-graph" title="This is acustom report"></i>':'')?>
		</td>
        <td><?php echo date("m/d/y h:ia", $report['Report_createdDate'])?></td>
        <td><?php echo $RECORD->get_userName($report['Report_createdBy'])?></td>
    </tr>
    <?php
endforeach;
?> 
</tbody>
</table>                    
        </div>
    </div>
</div>          